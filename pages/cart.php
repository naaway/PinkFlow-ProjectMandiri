<?php
session_start();

require_once "../config/database.php";

// Siapkan keranjang jika belum ada
if (!isset($_SESSION["cart"])) {
    $_SESSION["cart"] = [];
}


// TAMBAHKAN PRODUK KE KERANJANG

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

        // Jika stok produk berubah, sesuaikan jumlah keranjang
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
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Keranjang Belanja | PinkFlow</title>

    <link
        rel="stylesheet"
        href="../assets/css/style.css"
    >

    <style>
        .cart-item {
            padding: 20px 0;
            border-bottom: 1px solid #f0dce2;
        }

        .cart-item h3 {
            margin-top: 0;
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
        }

        .cart-actions {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            margin-top: 20px;
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
            href="../index.php#produk"
            class="btn btn-outline"
        >
            Kembali ke Katalog
        </a>

    </div>

</header>

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
                            href="../index.php#produk"
                            class="btn btn-primary"
                        >
                            Lihat Katalog
                        </a>

                    <?php else: ?>

                        <?php foreach ($keranjang as $item): ?>

                            <div class="cart-item">

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
                                href="../index.php#produk"
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
                            </button>

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

        <p>Small gifts, meaningful moments. ♡</p>

        <p>© <?= date("Y") ?> PinkFlow</p>

    </div>

</footer>

</body>
</html>
