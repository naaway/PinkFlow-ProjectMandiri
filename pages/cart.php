
<?php
session_start();

require_once "../config/database.php";

// Siapkan keranjang
if (!isset($_SESSION["cart"]) || !is_array($_SESSION["cart"])) {
    $_SESSION["cart"] = [];
}


// =====================================
// TAMBAHKAN PRODUK KE KERANJANG
// =====================================

if (isset($_GET["add"])) {

    $id = filter_input(INPUT_GET, "add", FILTER_VALIDATE_INT);

    if ($id && $id > 0) {

        $stmt = $conn->prepare(
            "SELECT id, stock
             FROM products
             WHERE id = ?
             LIMIT 1"
        );

        $stmt->bind_param("i", $id);
        $stmt->execute();

        $result = $stmt->get_result();
        $produk = $result->fetch_assoc();

        $stmt->close();

        if ($produk && (int) $produk["stock"] > 0) {

            $jumlahSaatIni = (int) ($_SESSION["cart"][$id] ?? 0);
            $stok = (int) $produk["stock"];

            if ($jumlahSaatIni < $stok) {
                $_SESSION["cart"][$id] = $jumlahSaatIni + 1;
            }
        }
    }

    header("Location: cart.php");
    exit;
}


// =====================================
// UBAH JUMLAH PRODUK
// =====================================

if (isset($_GET["update"])) {

    $id = filter_input(INPUT_GET, "update", FILTER_VALIDATE_INT);
    $jumlah = filter_input(INPUT_GET, "quantity", FILTER_VALIDATE_INT);

    if ($id && isset($_SESSION["cart"][$id])) {

        if ($jumlah !== false && $jumlah !== null && $jumlah > 0) {

            $stmt = $conn->prepare(
                "SELECT stock
                 FROM products
                 WHERE id = ?
                 LIMIT 1"
            );

            $stmt->bind_param("i", $id);
            $stmt->execute();

            $result = $stmt->get_result();
            $produk = $result->fetch_assoc();

            $stmt->close();

            if ($produk) {

                $stok = (int) $produk["stock"];

                if ($jumlah <= $stok) {
                    $_SESSION["cart"][$id] = $jumlah;
                } else {
                    $_SESSION["cart"][$id] = $stok;
                }

            } else {
                unset($_SESSION["cart"][$id]);
            }

        } else {
            unset($_SESSION["cart"][$id]);
        }
    }

    header("Location: cart.php");
    exit;
}


// =====================================
// HAPUS PRODUK DARI KERANJANG
// =====================================

if (isset($_GET["remove"])) {

    $id = filter_input(INPUT_GET, "remove", FILTER_VALIDATE_INT);

    if ($id && isset($_SESSION["cart"][$id])) {
        unset($_SESSION["cart"][$id]);
    }

    header("Location: cart.php");
    exit;
}


// =====================================
// AMBIL DATA PRODUK DALAM KERANJANG
// =====================================

$keranjang = [];
$total = 0;

if (!empty($_SESSION["cart"])) {

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

        if ($jumlah > $stok) {
            $jumlah = $stok;
            $_SESSION["cart"][$id] = $jumlah;
        }

        if ($jumlah <= 0) {
            unset($_SESSION["cart"][$id]);
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


// PENGATURAN NAVBAR

$basePath = "../";
$isHome = false;

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Keranjang Belanja | PinkFlow</title>

    <link rel="stylesheet" href="../assets/css/style.css">

    <style>

        .cart-item {
            display: flex;
            align-items: flex-start;
            gap: 20px;
            padding: 20px 0;
            border-bottom: 1px solid #f0dce2;
        }

        .cart-item-image {
            width: 125px;
            min-width: 125px;
            height: 125px;
            overflow: hidden;
            border-radius: 12px;
            background: #fff0f5;
        }

        .cart-item-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .cart-item-placeholder {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #c34f78;
            font-size: 30px;
        }

        .cart-item-info {
            flex: 1;
        }

        .cart-item-info h3 {
            margin-top: 5px;
            font-family: Georgia, serif;
        }

        .cart-quantity {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
            margin: 16px 0;
        }

        .cart-quantity input {
            width: 80px;
            padding: 10px;
            border: 1px solid #ead0d9;
            border-radius: 8px;
        }

        .cart-remove {
            color: #c34f78;
            text-decoration: underline;
            margin-left: 8px;
        }

        .cart-total {
            margin-top: 24px;
            padding-top: 18px;
            border-top: 1px solid #f0dce2;
        }

        .cart-actions {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            margin-top: 20px;
        }

        @media (max-width: 600px) {

            .cart-item {
                gap: 12px;
            }

            .cart-item-image {
                width: 90px;
                min-width: 90px;
                height: 90px;
            }

            .cart-quantity {
                gap: 8px;
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
                        YOUR SHOPPING BAG
                    </p>

                    <h2>Keranjang Belanja</h2>

                    <p>
                        Periksa produk pilihanmu sebelum melanjutkan pesanan.
                    </p>

                </div>


                <div class="card">

                    <div class="card-body">

                        <?php if (empty($keranjang)): ?>

                            <p>
                                Keranjang belanja kamu masih kosong.
                            </p>

                            <a
                                href="katalog.php"
                                class="btn btn-primary"
                            >
                                Lihat Katalog
                            </a>


                        <?php else: ?>


                            <?php foreach ($keranjang as $item): ?>

                                <div class="cart-item">

                                    <div class="cart-item-image">

                                        <?php if (!empty($item["image"])): ?>

                                            <img
                                                src="../assets/images/<?= htmlspecialchars($item["image"]) ?>"
                                                alt="<?= htmlspecialchars($item["name"]) ?>"
                                            >

                                        <?php else: ?>

                                            <div class="cart-item-placeholder">
                                                ♡
                                            </div>

                                        <?php endif; ?>

                                    </div>


                                    <div class="cart-item-info">

                                        <h3>
                                            <?= htmlspecialchars($item["name"]) ?>
                                        </h3>

                                        <p>
                                            Harga:
                                            Rp <?= number_format(
                                                $item["price"],
                                                0,
                                                ",",
                                                "."
                                            ) ?>
                                        </p>

                                        <p>
                                            Stok tersedia:
                                            <?= (int) $item["stock"] ?>
                                        </p>


                                        <form
                                            method="GET"
                                            action="cart.php"
                                            class="cart-quantity"
                                        >

                                            <input
                                                type="hidden"
                                                name="update"
                                                value="<?= (int) $item["id"] ?>"
                                            >

                                            <label>
                                                Jumlah:
                                            </label>

                                            <input
                                                type="number"
                                                name="quantity"
                                                min="1"
                                                max="<?= (int) $item["stock"] ?>"
                                                value="<?= (int) $item["quantity"] ?>"
                                                required
                                            >

                                            <button
                                                type="submit"
                                                class="btn btn-outline"
                                            >
                                                Ubah Jumlah
                                            </button>

                                            <a
                                                href="cart.php?remove=<?= (int) $item["id"] ?>"
                                                class="cart-remove"
                                                onclick="return confirm('Hapus produk ini dari keranjang?')"
                                            >
                                                Hapus
                                            </a>

                                        </form>


                                        <p>
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

                                    </div>

                                </div>

                            <?php endforeach; ?>


                            <div class="cart-total">

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


                            <div class="cart-actions">

                                <a
                                    href="katalog.php"
                                    class="btn btn-outline"
                                >
                                    Tambah Produk
                                </a>

                                <a
                                    href="checkout.php"
                                    class="btn btn-primary"
                                >
                                    Checkout
                                </a>

                            </div>


                            <p style="margin-top: 16px;">
                                Silakan periksa jumlah produk dan total
                                pesanan sebelum checkout.
                            </p>


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
