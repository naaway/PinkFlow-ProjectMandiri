
<?php
session_start();

require_once "../config/database.php";

$nomorWhatsApp = "6285242771020";

$error = "";
$linkWhatsApp = "";
$nomorPesanan = "";

$keranjang = [];
$total = 0;


// AMBIL DATA PRODUK DARI KERANJANG

if (!empty($_SESSION["cart"])) {

    $ids = array_keys($_SESSION["cart"]);

    $placeholders = implode(
        ",",
        array_fill(0, count($ids), "?")
    );

    $types = str_repeat("i", count($ids));

    $stmt = $conn->prepare(
        "SELECT id, name, price, stock
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


// PROSES CHECKOUT

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $nama = trim($_POST["nama"] ?? "");
    $telepon = trim($_POST["telepon"] ?? "");
    $alamat = trim($_POST["alamat"] ?? "");

    if (empty($keranjang)) {

        $error = "Keranjang belanja kamu kosong.";

    } elseif (
        $nama === "" ||
        $telepon === "" ||
        $alamat === ""
    ) {

        $error = "Mohon lengkapi semua data pelanggan.";

    } else {

        try {

            $conn->begin_transaction();

            // Simpan data pesanan utama

            $stmt = $conn->prepare(
                "INSERT INTO orders (
                    order_type,
                    customer_name,
                    phone,
                    address,
                    total,
                    status
                ) VALUES (
                    'catalog',
                    ?, ?, ?, ?,
                    'Konfirmasi Pesanan di WhatsApp'
                )"
            );

            $stmt->bind_param(
                "sssd",
                $nama,
                $telepon,
                $alamat,
                $total
            );

            $stmt->execute();

            $idPesanan = $conn->insert_id;

            $stmt->close();


            // Buat kode pesanan

            $nomorPesanan = "PF-" . str_pad(
                $idPesanan,
                5,
                "0",
                STR_PAD_LEFT
            );


            // Simpan kode pesanan

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


            // Simpan setiap produk ke order_items

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

            $conn->commit();


            // Kosongkan keranjang setelah berhasil

            $_SESSION["cart"] = [];


            // Buat pesan WhatsApp

            $pesanWhatsApp =
                "Halo PinkFlow, saya ingin mengonfirmasi pesanan.\n\n" .
                "Kode Pesanan: " . $nomorPesanan . "\n" .
                "Nama: " . $nama . "\n" .
                "Nomor WhatsApp: " . $telepon . "\n" .
                "Alamat: " . $alamat . "\n\n" .
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

            $error = "Pesanan gagal disimpan. Silakan coba kembali.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Checkout | PinkFlow</title>

    <link
        rel="stylesheet"
        href="../assets/css/style.css"
    >

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
        }

        main {
            flex: 1;
        }

        .checkout-form {
            margin-top: 24px;
        }

        .checkout-summary {
            padding: 20px;
            margin-bottom: 24px;
            background: #fff5f7;
            border-radius: 12px;
        }

        .checkout-summary p {
            margin: 10px 0;
        }

        .checkout-success {
            margin-top: 24px;
            padding: 20px;
            border-radius: 12px;
            background: #fff5f7;
        }

        .checkout-success h3 {
            margin-top: 0;
        }

        .checkout-success .btn {
            margin-top: 10px;
        }

        footer.footer {
            margin-top: auto;
        }

    </style>

</head>

<body>

<header class="navbar">

    <div class="container navbar-inner">

        <a href="../index.php" class="brand">
            Pink<span>Flow</span>
            <small>Flower & Gifts</small>
        </a>

        <nav class="nav-links">

            <a href="../index.php">Beranda</a>

            <a href="../index.php#produk">Katalog</a>

            <a href="../index.php#tentang">Tentang Kami</a>

        </nav>

        <a
            href="cart.php"
            class="btn btn-outline"
        >
            Kembali ke Keranjang
        </a>

    </div>

</header>


<main>

    <section>

        <div class="container">

            <div class="section-heading">

                <p class="eyebrow">
                    COMPLETE YOUR ORDER
                </p>

                <h2>Checkout Pesanan</h2>

                <p>
                    Lengkapi data pelanggan untuk melanjutkan pesanan.
                </p>

            </div>


            <div class="card">

                <div class="card-body">

                    <?php if ($error !== ""): ?>

                        <div class="error-message">

                            <?= htmlspecialchars($error) ?>

                        </div>

                    <?php endif; ?>


                    <?php if ($linkWhatsApp !== ""): ?>

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

                            <p>

                                Pesanan kamu sudah masuk ke database PinkFlow.
                                Silakan konfirmasi melalui WhatsApp.

                            </p>

                            <a
                                href="<?= htmlspecialchars($linkWhatsApp) ?>"
                                class="btn btn-success"
                                target="_blank"
                                rel="noopener noreferrer"
                            >

                                Konfirmasi Pesanan di WhatsApp

                            </a>

                        </div>


                    <?php elseif (empty($keranjang)): ?>

                        <p>
                            Keranjang belanja kamu masih kosong.
                        </p>

                        <a
                            href="../index.php#produk"
                            class="btn btn-primary"
                        >

                            Kembali ke Katalog

                        </a>


                    <?php else: ?>


                        <div class="checkout-summary">

                            <h3>
                                Ringkasan Pesanan
                            </h3>

                            <?php foreach ($keranjang as $item): ?>

                                <p>

                                    <?= htmlspecialchars($item["name"]) ?>

                                    × <?= (int) $item["quantity"] ?>

                                    <br>

                                    Subtotal:

                                    <strong>

                                        Rp <?= number_format(
                                            $item["subtotal"],
                                            0,
                                            ",",
                                            "."
                                        ) ?>

                                    </strong>

                                </p>

                            <?php endforeach; ?>


                            <hr>


                            <h3>

                                Total:

                                Rp <?= number_format(
                                    $total,
                                    0,
                                    ",",
                                    "."
                                ) ?>

                            </h3>

                        </div>


                        <form
                            method="POST"
                            action=""
                            class="checkout-form"
                        >

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
                                    value="<?= htmlspecialchars(
                                        $_POST["nama"] ?? ""
                                    ) ?>"
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
                                    value="<?= htmlspecialchars(
                                        $_POST["telepon"] ?? ""
                                    ) ?>"
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
                                ><?= htmlspecialchars(
                                    $_POST["alamat"] ?? ""
                                ) ?></textarea>

                            </div>


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

            Pink<span>Flow</span>

            <small>Flower & Gifts</small>

        </a>

        <p>
            Small gifts, meaningful moments. ♡
        </p>

        <p>
            © <?= date("Y") ?> PinkFlow
        </p>

    </div>

</footer>


</body>

</html>
