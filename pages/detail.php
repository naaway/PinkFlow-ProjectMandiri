<?php
require_once "../config/database.php";


$id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

if (!$id || $id <= 0) {
    http_response_code(400);
    exit("ID produk tidak valid.");
}

// Ambil detail produk berdasarkan ID
$stmt = $conn->prepare(
    "SELECT id, name, category, price, stock, image, description
     FROM products
     WHERE id = ?
     LIMIT 1"
);

$stmt->bind_param("i", $id);
$stmt->execute();

$result = $stmt->get_result();
$produk = $result->fetch_assoc();

$stmt->close();

// Jika produk tidak ditemukan
if (!$produk) {
    http_response_code(404);
    exit("Produk tidak ditemukan.");
}

// Format harga
$harga = "Rp " . number_format(
    (float) $produk["price"],
    0,
    ",",
    "."
);

$nomorWhatsApp = "62855242771020";

$pesanWhatsApp = "Halo PinkFlow, saya tertarik dengan produk "
    . $produk["name"]
    . ". Apakah produk ini masih tersedia?";

$linkWhatsApp = "https://wa.me/" . $nomorWhatsApp
    . "?text=" . urlencode($pesanWhatsApp);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($produk["name"]) ?> | PinkFlow</title>
    <link rel="stylesheet" href="../assets/css/style.css">

<style>
    .product-detail-grid {
        display: grid !important;
        grid-template-columns: 340px minmax(0, 1fr) !important;
        gap: 36px !important;
        align-items: start !important;
    }

    .product-detail-grid .product-detail-image {
        width: 340px !important;
        max-width: 100% !important;
        height: 340px !important;
        max-height: 340px !important;
        overflow: hidden !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        background: #fff5f7;
        border-radius: 16px;
    }

    .product-detail-grid .product-detail-image img {
        width: 100% !important;
        height: 100% !important;
        max-width: 100% !important;
        max-height: 100% !important;
        object-fit: contain !important;
        object-position: center !important;
        display: block !important;
    }

    @media (max-width: 768px) {
        .product-detail-grid {
            grid-template-columns: 1fr !important;
        }

        .product-detail-grid .product-detail-image {
            width: 100% !important;
            max-width: 340px !important;
            height: 340px !important;
            margin: 0 auto;
        }
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

        <a href="../index.php#produk" class="btn btn-outline">
            Kembali ke Katalog
        </a>
    </div>
</header>

<main>
    <section>
        <div class="container">

            <div class="section-heading">
                <p class="eyebrow">PRODUCT DETAILS</p>
                <h2>Detail Produk</h2>
            </div>

            <div class="card">
                <div class="product-detail-grid">

                    <div class="product-detail-image">
                        <?php if (!empty($produk["image"])): ?>
                            <img
                                src="../assets/images/<?= htmlspecialchars($produk["image"]) ?>"
                                alt="<?= htmlspecialchars($produk["name"]) ?>"
                            >
                        <?php else: ?>
                            <div class="product-placeholder">
                                ♡
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="product-detail-content">

                        <p class="product-category">
                            <?= htmlspecialchars($produk["category"]) ?>
                        </p>

                        <h1>
                            <?= htmlspecialchars($produk["name"]) ?>
                        </h1>

                        <p class="product-detail-price">
                            <?= $harga ?>
                        </p>

                        <p class="product-stock">
                            Stok tersedia:
                            <?= (int) $produk["stock"] ?>
                        </p>

                        <div class="product-description">
                            <h3>Deskripsi Produk</h3>

                            <p>
                                <?php if (!empty($produk["description"])): ?>
                                    <?= nl2br(htmlspecialchars($produk["description"])) ?>
                                <?php else: ?>
                                    Belum ada deskripsi untuk produk ini.
                                <?php endif; ?>
                            </p>
                        </div>

                        
                        <?php if ((int) $produk["stock"] > 0): ?>

                            <a
                                href="cart.php?add=<?= (int) $produk['id'] ?>"
                                class="btn btn-primary"
                            >
                                Tambah ke Keranjang
                            </a>

                            <a
                                href="<?= htmlspecialchars($linkWhatsApp) ?>"
                                class="btn btn-outline"
                                target="_blank"
                                rel="noopener noreferrer"
                            >
                                Konsultasi WhatsApp
                            </a>

                        <?php else: ?>

                            <button class="btn btn-outline" disabled>
                                Stok Habis
                            </button>

                        <?php endif; ?>


                    </div>
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
