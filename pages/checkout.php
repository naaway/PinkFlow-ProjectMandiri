
<?php
session_start();

require_once "../config/database.php";

$nomorWhatsApp = "6285242771020";

$error = "";
$linkWhatsApp = "";
$nomorPesanan = "";

$keranjang = [];
$total = 0;


// ======================================
// MENENTUKAN MODE CHECKOUT
// ======================================

if (isset($_GET["buy"])) {

    $buyId = (int) $_GET["buy"];

    if ($buyId > 0) {
        $_SESSION["direct_checkout"] = $buyId;
    }
}

$modeDirect = !empty($_SESSION["direct_checkout"]);


// ======================================
// MENGAMBIL JUMLAH PEMBELIAN
// ======================================

$directQuantity = 1;

if ($modeDirect) {

    if ($_SERVER["REQUEST_METHOD"] === "POST") {

        $directQuantity = (int) ($_POST["quantity"] ?? 1);

    } else {

        $directQuantity = (int) ($_GET["quantity"] ?? 1);

    }

    if ($directQuantity < 1) {
        $directQuantity = 1;
    }
}


// ======================================
// MENGAMBIL DATA PRODUK
// ======================================

if ($modeDirect) {

    $directId = (int) $_SESSION["direct_checkout"];

    $stmt = $conn->prepare(
        "SELECT id, name, price, stock, image
         FROM products
         WHERE id = ?
         LIMIT 1"
    );

    $stmt->bind_param("i", $directId);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($produk = $result->fetch_assoc()) {

        $stok = (int) $produk["stock"];

        if ($stok > 0) {

            // Jangan biarkan jumlah melebihi stok
            if ($directQuantity > $stok) {
                $directQuantity = $stok;
            }

            $produk["quantity"] = $directQuantity;

            $produk["subtotal"] =
                (float) $produk["price"] * $directQuantity;

            $keranjang[] = $produk;

            $total = $produk["subtotal"];
        }
    }

    $stmt->close();

} elseif (!empty($_SESSION["cart"])) {

    $ids = array_keys($_SESSION["cart"]);

    $placeholders = implode(
        ",",
        array_fill(0, count($ids), "?")
    );

    $types = str_repeat("i", count($ids));

    $stmt = $conn->prepare(
        "SELECT id, name, price, stock, image
         FROM products
         WHERE id IN ($placeholders)"
    );

    $stmt->bind_param($types, ...$ids);
    $stmt->execute();

    $result = $stmt->get_result();

    while ($produk = $result->fetch_assoc()) {

        $id = (int) $produk["id"];
        $jumlah = (int) ($_SESSION["cart"][$id] ?? 0);
        $stok = (int) $produk["stock"];

        if ($jumlah <= 0 || $jumlah > $stok) {
            continue;
        }

        $subtotal = (float) $produk["price"] * $jumlah;

        $produk["quantity"] = $jumlah;
        $produk["subtotal"] = $subtotal;

        $keranjang[] = $produk;
        $total += $subtotal;
    }

    $stmt->close();
}


// ======================================
// NILAI AWAL FORM
// ======================================

$namaForm = $_POST["nama"] ?? "";
$teleponForm = $_POST["telepon"] ?? "";
$alamatForm = $_POST["alamat"] ?? "";

$tanggalForm = $_POST["pickup_date"] ?? "";
$waktuForm = $_POST["pickup_time"] ?? "";


// ======================================
// PROSES CHECKOUT
// ======================================

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $nama = trim($_POST["nama"] ?? "");
    $telepon = trim($_POST["telepon"] ?? "");
    $alamat = trim($_POST["alamat"] ?? "");

    $tanggal = trim($_POST["pickup_date"] ?? "");
    $waktu = trim($_POST["pickup_time"] ?? "");

    $tanggalObj = DateTime::createFromFormat("!Y-m-d", $tanggal);

    $tanggalValid = (
        $tanggalObj &&
        $tanggalObj->format("Y-m-d") === $tanggal &&
        $tanggal >= date("Y-m-d")
    );

    $waktuValid = (
        preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $waktu) === 1
    );

    if (empty($keranjang)) {

        $error = "Produk tidak tersedia atau stok sudah habis.";

    } elseif (
        $nama === "" ||
        $telepon === "" ||
        $alamat === ""
    ) {

        $error = "Mohon lengkapi semua data pelanggan.";

    } elseif (!$tanggalValid || !$waktuValid) {

        $error = "Mohon pilih tanggal dan jam pengambilan yang valid.";

    } elseif ($modeDirect && $directQuantity < 1) {

        $error = "Jumlah pembelian minimal 1 produk.";

    } else {

        try {

            $conn->begin_transaction();


            // PERIKSA DAN KURANGI STOK

            foreach ($keranjang as $item) {

                $productId = (int) $item["id"];
                $quantity = (int) $item["quantity"];

                $stmtStock = $conn->prepare(
                    "SELECT stock
                     FROM products
                     WHERE id = ?
                     FOR UPDATE"
                );

                $stmtStock->bind_param("i", $productId);
                $stmtStock->execute();

                $stockResult = $stmtStock->get_result();
                $stockData = $stockResult->fetch_assoc();

                $stmtStock->close();

                if (
                    !$stockData ||
                    (int) $stockData["stock"] < $quantity
                ) {
                    throw new Exception(
                        "Stok produk tidak mencukupi."
                    );
                }

                // Kurangi stok sesuai jumlah pembelian
                $stmtUpdateStock = $conn->prepare(
                    "UPDATE products
                     SET stock = stock - ?
                     WHERE id = ?
                     AND stock >= ?"
                );

                $stmtUpdateStock->bind_param(
                    "iii",
                    $quantity,
                    $productId,
                    $quantity
                );

                $stmtUpdateStock->execute();

                if ($stmtUpdateStock->affected_rows !== 1) {

                    $stmtUpdateStock->close();

                    throw new Exception(
                        "Stok produk gagal diperbarui."
                    );
                }

                $stmtUpdateStock->close();
            }


            // SIMPAN PESANAN UTAMA

            $stmt = $conn->prepare(
                "INSERT INTO orders (
                    order_type,
                    customer_name,
                    phone,
                    address,
                    total,
                    status,
                    pickup_date,
                    pickup_time
                ) VALUES (
                    'catalog',
                    ?, ?, ?, ?,
                    'Konfirmasi Pesanan di WhatsApp',
                    ?, ?
                )"
            );

            $stmt->bind_param(
                "sssdss",
                $nama,
                $telepon,
                $alamat,
                $total,
                $tanggal,
                $waktu
            );

            $stmt->execute();

            $idPesanan = $conn->insert_id;

            $stmt->close();


            // BUAT KODE PESANAN

            $nomorPesanan = "PF-" . str_pad(
                $idPesanan,
                5,
                "0",
                STR_PAD_LEFT
            );


            // SIMPAN KODE PESANAN

            $stmtKode = $conn->prepare(
                "UPDATE orders
                 SET order_code = ?
                 WHERE id = ?"
            );

            $stmtKode->bind_param(
                "si",
                $nomorPesanan,
                $idPesanan
            );

            $stmtKode->execute();
            $stmtKode->close();


            // SIMPAN DETAIL PRODUK

            $stmtItem = $conn->prepare(
                "INSERT INTO order_items (
                    order_id,
                    product_id,
                    product_name,
                    price,
                    quantity,
                    subtotal
                ) VALUES (?, ?, ?, ?, ?, ?)"
            );

            foreach ($keranjang as $item) {

                $productId = (int) $item["id"];
                $productName = $item["name"];
                $price = (float) $item["price"];
                $quantity = (int) $item["quantity"];
                $subtotal = (float) $item["subtotal"];

                $stmtItem->bind_param(
                    "iisdid",
                    $idPesanan,
                    $productId,
                    $productName,
                    $price,
                    $quantity,
                    $subtotal
                );

                $stmtItem->execute();
            }

            $stmtItem->close();


            // SIMPAN TRANSAKSI

            $conn->commit();



            // PERBARUI KERANJANG SETELAH PESANAN BERHASIL

            if ($modeDirect) {

                // Checkout langsung tidak mengubah keranjang utama
                unset($_SESSION["direct_checkout"]);

            } else {

                // Kurangi hanya jumlah produk yang berhasil dibeli
                foreach ($keranjang as $item) {

                    $productId = (int) $item["id"];
                    $quantityPurchased = (int) $item["quantity"];

                    $currentQuantity = (int) (
                        $_SESSION["cart"][$productId] ?? 0
                    );

                    $remainingQuantity = $currentQuantity - $quantityPurchased;

                    if ($remainingQuantity > 0) {

                        $_SESSION["cart"][$productId] = $remainingQuantity;

                    } else {

                        unset($_SESSION["cart"][$productId]);

                    }
                }

            }


            // BUAT PESAN WHATSAPP

            $tanggalWhatsApp = $tanggalObj->format("d-m-Y");

            $pesanWhatsApp =
                "Halo PinkFlow, saya ingin mengonfirmasi pesanan.\n\n" .
                "Kode Pesanan: " . $nomorPesanan . "\n" .
                "Nama: " . $nama . "\n" .
                "Nomor WhatsApp: " . $telepon . "\n" .
                "Alamat: " . $alamat . "\n" .
                "Tanggal Pengambilan: " . $tanggalWhatsApp . "\n" .
                "Jam Pengambilan: " . $waktu . "\n\n" .
                "Daftar Pesanan:\n";

            foreach ($keranjang as $item) {

                $pesanWhatsApp .=
                    "- " . $item["name"] .
                    " x" . $item["quantity"] .
                    " = Rp " .
                    number_format(
                        $item["subtotal"],
                        0,
                        ",",
                        "."
                    ) . "\n";
            }

            $pesanWhatsApp .=
                "\nTotal: Rp " .
                number_format($total, 0, ",", ".");

            $linkWhatsApp =
                "https://wa.me/" . $nomorWhatsApp .
                "?text=" . urlencode($pesanWhatsApp);


        } catch (Throwable $e) {

            $conn->rollback();

            $error = "Pesanan gagal disimpan. Silakan periksa stok dan coba kembali.";

        }
    }
}


// PENGATURAN NAVBAR

$basePath = "../";
$isHome = false;
$hideCartIcon = true;

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Checkout | PinkFlow</title>

    <link rel="stylesheet" href="../assets/css/style.css">

    <style>

        html,
        body {
            min-height: 100%;
            margin: 0;
        }

        body {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            background: #fff5f7;
        }

        main {
            flex: 1;
            padding-block: 45px 65px;
        }

        .checkout-form {
            margin-top: 25px;
        }

        .checkout-summary {
            padding: 24px;
            margin-bottom: 25px;
            background: #fff5f7;
            border-radius: 14px;
        }

        .checkout-summary h3 {
            margin-top: 0;
            font-family: Georgia, serif;
        }

        .checkout-product {
            display: flex;
            align-items: center;
            gap: 18px;
            padding: 17px 0;
            border-bottom: 1px solid #f0dce2;
        }

        .checkout-product-image {
            width: 105px;
            min-width: 105px;
            height: 105px;
            border-radius: 12px;
            overflow: hidden;
            background: #fce8ef;
        }

        .checkout-product-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .checkout-product-placeholder {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #c34e7b;
            font-size: 30px;
        }

        .checkout-product-info {
            flex: 1;
        }

        .checkout-product-info h4 {
            margin: 0 0 8px;
            font-family: Georgia, serif;
            color: #49313d;
        }

        .checkout-product-info p {
            margin: 5px 0;
            font-size: 14px;
        }

        .checkout-quantity {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-top: 12px;
            flex-wrap: wrap;
        }

        .checkout-quantity label {
            font-size: 14px;
            font-weight: 600;
        }

        .checkout-quantity input {
            width: 85px;
            padding: 10px 12px;
            border: 1px solid #e8c5d2;
            border-radius: 9px;
            background: #ffffff;
            color: #49313d;
            font-family: inherit;
        }

        .checkout-quantity input:focus {
            outline: none;
            border-color: #c34e7b;
            box-shadow: 0 0 0 3px rgba(195, 78, 123, 0.10);
        }

        .checkout-total {
            margin-top: 20px;
            padding-top: 15px;
            border-top: 1px solid #e9cbd7;
        }

        .checkout-pickup {
            margin-top: 25px;
            padding: 24px;
            background: #fff5f8;
            border: 1px solid #f1dce4;
            border-radius: 14px;
        }

        .checkout-pickup h3 {
            margin: 0 0 8px;
            font-family: Georgia, serif;
            color: #a83f6a;
        }

        .checkout-pickup p {
            color: #806d76;
            font-size: 14px;
            margin-bottom: 20px;
        }

        .checkout-pickup-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 18px;
        }

        .checkout-pickup-group {
            display: flex;
            flex-direction: column;
            gap: 9px;
        }

        .checkout-pickup-group label {
            font-weight: 600;
            color: #563b49;
            font-size: 14px;
        }

        .checkout-pickup-group input {
            width: 100%;
            box-sizing: border-box;
            padding: 13px 14px;
            border: 1px solid #e8c5d2;
            border-radius: 10px;
            background: #ffffff;
            font-family: inherit;
            color: #49313d;
        }

        .checkout-pickup-group input:focus {
            outline: none;
            border-color: #c34e7b;
            box-shadow: 0 0 0 3px rgba(195, 78, 123, 0.10);
        }

        .checkout-success {
            margin-top: 24px;
            padding: 24px;
            border-radius: 14px;
            background: #fff5f7;
        }

        .checkout-success h3 {
            margin-top: 0;
            font-family: Georgia, serif;
        }

        .checkout-success-details {
            padding: 15px;
            margin: 18px 0;
            background: #ffffff;
            border-radius: 10px;
        }

        .checkout-success-products {
            margin: 18px 0;
            padding: 18px;
            background: #ffffff;
            border-radius: 12px;
        }

        .checkout-success-product {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 12px 0;
            border-bottom: 1px solid #f0dce2;
        }

        .checkout-success-product:last-child {
            border-bottom: none;
        }

        .checkout-success-product img {
            width: 85px;
            min-width: 85px;
            height: 85px;
            object-fit: cover;
            border-radius: 10px;
        }

        .checkout-success-product-info {
            flex: 1;
        }

        .checkout-success-product-info h4 {
            margin: 0 0 7px;
            color: #49313d;
            font-family: Georgia, serif;
        }

        .checkout-success-product-info p {
            margin: 4px 0;
            font-size: 14px;
        }

        .checkout-success .btn-whatsapp {
            display: inline-block;
            margin-top: 12px;
            padding: 13px 22px;
            background: #b54470;
            color: #ffffff !important;
            border: 1px solid #b54470;
            border-radius: 30px;
            text-decoration: none;
            font-weight: 600;
            transition: 0.3s ease;
        }

        .checkout-success .btn-whatsapp:hover {
            background: #96365d;
            border-color: #96365d;
        }

        .checkout-error {
            padding: 13px 16px;
            margin-bottom: 18px;
            border-radius: 9px;
            background: #fff0f0;
            color: #b4234b;
        }

        footer.footer {
            margin-top: auto;
        }

        @media (max-width: 650px) {

            main {
                padding-block: 30px 45px;
            }

            .checkout-summary {
                padding: 17px;
            }

            .checkout-product-image {
                width: 85px;
                min-width: 85px;
                height: 85px;
            }

            .checkout-pickup {
                padding: 17px;
            }

            .checkout-pickup-grid {
                grid-template-columns: 1fr;
            }

            .checkout-success {
                padding: 17px;
            }

            .checkout-success-product img {
                width: 70px;
                min-width: 70px;
                height: 70px;
            }

        }

    </style>

</head>

<body>

    <?php require_once "../includes/navbar.php"; ?>


    <main>

        <section>

            <div class="container">

                <div class="section-heading">

                    <p class="eyebrow">
                        COMPLETE YOUR ORDER
                    </p>

                    <h2>Checkout Pesanan</h2>

                    <p>
                        Lengkapi data pelanggan dan jadwal pengambilan
                        untuk melanjutkan pesanan.
                    </p>

                </div>


                <div class="card">

                    <div class="card-body">


                        <?php if ($error !== ""): ?>

                            <div class="checkout-error">
                                <?= htmlspecialchars($error) ?>
                            </div>

                        <?php endif; ?>


                        <?php if ($linkWhatsApp !== ""): ?>


                            <!-- PESANAN BERHASIL -->

                            <div class="checkout-success">

                                <h3>
                                    Pesanan berhasil disimpan!
                                </h3>

                                <p>
                                    Kode pesanan:
                                    <strong>
                                        <?= htmlspecialchars($nomorPesanan) ?>
                                    </strong>
                                </p>


                                <div class="checkout-success-products">

                                    <h3>Produk yang Dipesan</h3>

                                    <?php foreach ($keranjang as $item): ?>

                                        <div class="checkout-success-product">

                                            <?php if (!empty($item["image"])): ?>

                                                <img
                                                    src="../assets/images/<?= htmlspecialchars($item["image"]) ?>"
                                                    alt="<?= htmlspecialchars($item["name"]) ?>"
                                                >

                                            <?php else: ?>

                                                <div class="checkout-product-placeholder">
                                                    ♡
                                                </div>

                                            <?php endif; ?>


                                            <div class="checkout-success-product-info">

                                                <h4>
                                                    <?= htmlspecialchars($item["name"]) ?>
                                                </h4>

                                                <p>
                                                    Jumlah:
                                                    <?= (int) $item["quantity"] ?>
                                                </p>

                                                <strong>
                                                    Rp <?= number_format(
                                                        $item["subtotal"],
                                                        0,
                                                        ",",
                                                        "."
                                                    ) ?>
                                                </strong>

                                            </div>

                                        </div>

                                    <?php endforeach; ?>

                                </div>


                                <div class="checkout-success-details">

                                    <p>
                                        <strong>Tanggal Pengambilan:</strong>
                                        <?= htmlspecialchars($tanggalWhatsApp) ?>
                                    </p>

                                    <p>
                                        <strong>Jam Pengambilan:</strong>
                                        <?= htmlspecialchars($waktu) ?>
                                    </p>

                                    <p>
                                        <strong>Total Pesanan:</strong>
                                        Rp <?= number_format($total, 0, ",", ".") ?>
                                    </p>

                                </div>


                                <p>
                                    Pesanan kamu sudah masuk ke database PinkFlow.
                                    Silakan konfirmasi melalui WhatsApp.
                                </p>


                                <a
                                    href="<?= htmlspecialchars($linkWhatsApp) ?>"
                                    class="btn-whatsapp"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                >
                                    Konfirmasi Pesanan di WhatsApp
                                </a>

                                <br><br>

                                <a
                                    href="katalog.php"
                                    class="btn btn-outline"
                                >
                                    Kembali ke Katalog
                                </a>

                            </div>


                        <?php elseif (empty($keranjang)): ?>


                            <p>
                                Produk tidak tersedia atau stok sudah habis.
                            </p>

                            <a href="katalog.php" class="btn btn-primary">
                                Kembali ke Katalog
                            </a>


                        <?php else: ?>


                            <!-- FORM CHECKOUT -->

                            <form
                                method="POST"
                                action=""
                                class="checkout-form"
                            >


                                <!-- RINGKASAN PRODUK -->

                                <div class="checkout-summary">

                                    <h3>
                                        Ringkasan Pesanan
                                    </h3>


                                    <?php foreach ($keranjang as $item): ?>

                                        <div
                                            class="checkout-product"
                                            data-price="<?= (float) $item["price"] ?>"
                                        >

                                            <div class="checkout-product-image">

                                                <?php if (!empty($item["image"])): ?>

                                                    <img
                                                        src="../assets/images/<?= htmlspecialchars($item["image"]) ?>"
                                                        alt="<?= htmlspecialchars($item["name"]) ?>"
                                                    >

                                                <?php else: ?>

                                                    <div class="checkout-product-placeholder">
                                                        ♡
                                                    </div>

                                                <?php endif; ?>

                                            </div>


                                            <div class="checkout-product-info">

                                                <h4>
                                                    <?= htmlspecialchars($item["name"]) ?>
                                                </h4>

                                                <p>
                                                    Harga satuan:
                                                    Rp <?= number_format(
                                                        $item["price"],
                                                        0,
                                                        ",",
                                                        "."
                                                    ) ?>
                                                </p>


                                                <?php if ($modeDirect): ?>

                                                    <!-- PILIH JUMLAH CHECKOUT LANGSUNG -->

                                                    <div class="checkout-quantity">

                                                        <label for="quantity">
                                                            Jumlah Pembelian:
                                                        </label>

                                                        <input
                                                            type="number"
                                                            name="quantity"
                                                            id="quantity"
                                                            min="1"
                                                            max="<?= (int) $item["stock"] ?>"
                                                            value="<?= (int) $item["quantity"] ?>"
                                                            required
                                                        >

                                                    </div>

                                                    <p>
                                                        Stok tersedia:
                                                        <?= (int) $item["stock"] ?>
                                                    </p>

                                                <?php else: ?>

                                                    <p>
                                                        Jumlah:
                                                        <?= (int) $item["quantity"] ?>
                                                    </p>

                                                <?php endif; ?>


                                                <strong class="product-subtotal">

                                                    Rp <?= number_format(
                                                        $item["subtotal"],
                                                        0,
                                                        ",",
                                                        "."
                                                    ) ?>

                                                </strong>

                                            </div>

                                        </div>

                                    <?php endforeach; ?>


                                    <div class="checkout-total">

                                        <h3>
                                            Total:
                                            <span id="checkout-total">
                                                Rp <?= number_format(
                                                    $total,
                                                    0,
                                                    ",",
                                                    "."
                                                ) ?>
                                            </span>
                                        </h3>

                                    </div>

                                </div>


                                <!-- DATA PELANGGAN -->

                                <div class="form-group">

                                    <label for="nama">
                                        Nama Lengkap
                                    </label>

                                    <input
                                        type="text"
                                        name="nama"
                                        id="nama"
                                        required
                                        placeholder="Masukkan nama lengkap"
                                        value="<?= htmlspecialchars($namaForm) ?>"
                                    >

                                </div>


                                <div class="form-group">

                                    <label for="telepon">
                                        Nomor HP / WhatsApp
                                    </label>

                                    <input
                                        type="tel"
                                        name="telepon"
                                        id="telepon"
                                        required
                                        placeholder="Contoh: 08xxxxxxxxxx"
                                        value="<?= htmlspecialchars($teleponForm) ?>"
                                    >

                                </div>


                                <div class="form-group">

                                    <label for="alamat">
                                        Alamat Lengkap
                                    </label>

                                    <textarea
                                        name="alamat"
                                        id="alamat"
                                        required
                                        placeholder="Masukkan alamat penerima"
                                    ><?= htmlspecialchars($alamatForm) ?></textarea>

                                </div>


                                <!-- JADWAL PENGAMBILAN -->

                                <div class="checkout-pickup">

                                    <h3>
                                        ♡ Jadwal Pengambilan
                                    </h3>

                                    <p>
                                        Tentukan tanggal dan jam ketika
                                        kamu ingin mengambil pesanan.
                                    </p>

                                    <div class="checkout-pickup-grid">

                                        <div class="checkout-pickup-group">

                                            <label for="pickup_date">
                                                Tanggal Pengambilan
                                            </label>

                                            <input
                                                type="date"
                                                name="pickup_date"
                                                id="pickup_date"
                                                min="<?= date("Y-m-d") ?>"
                                                required
                                                value="<?= htmlspecialchars($tanggalForm) ?>"
                                            >

                                        </div>


                                        <div class="checkout-pickup-group">

                                            <label for="pickup_time">
                                                Jam Pengambilan
                                            </label>

                                            <input
                                                type="time"
                                                name="pickup_time"
                                                id="pickup_time"
                                                required
                                                value="<?= htmlspecialchars($waktuForm) ?>"
                                            >

                                        </div>

                                    </div>

                                </div>


                                <br>


                                <button
                                    type="submit"
                                    class="btn btn-primary"
                                >
                                    Simpan Pesanan
                                </button>


                            </form>


                        <?php endif; ?>

                    </div>

                </div>

            </div>

        </section>

    </main>


    <footer class="footer">

        <div class="container footer-inner">

            <a href="../index.php" class="brand">

                <span class="brand-name">PinkFlow</span>

                <small>FLOWER & GIFTS</small>

            </a>

            <p>
                Small gifts, meaningful moments. ♡
            </p>

            <p>
                © <?= date("Y") ?> PinkFlow
            </p>

        </div>

    </footer>


    <!-- HITUNG TOTAL OTOMATIS UNTUK CHECKOUT LANGSUNG -->

    <script>

        const quantityInput = document.getElementById("quantity");

        if (quantityInput) {

            const productCard = quantityInput.closest(".checkout-product");

            const price = Number(productCard.dataset.price);

            const subtotalElement = productCard.querySelector(".product-subtotal");

            const totalElement = document.getElementById("checkout-total");

            const formatRupiah = (number) => {

                return "Rp " + new Intl.NumberFormat("id-ID").format(number);

            };


            function updateCheckoutTotal() {

                let quantity = parseInt(quantityInput.value, 10);

                const maxStock = parseInt(quantityInput.max, 10);

                if (!Number.isFinite(quantity) || quantity < 1) {
                    quantity = 1;
                    quantityInput.value = 1;
                }

                if (quantity > maxStock) {
                    quantity = maxStock;
                    quantityInput.value = maxStock;
                }

                const subtotal = price * quantity;

                subtotalElement.textContent = formatRupiah(subtotal);

                totalElement.textContent = formatRupiah(subtotal);

            }


            quantityInput.addEventListener(
                "input",
                updateCheckoutTotal
            );

            quantityInput.addEventListener(
                "change",
                updateCheckoutTotal
            );

        }

    </script>

</body>
</html>
