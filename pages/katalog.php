
<?php
require_once "../config/database.php";

// Ambil seluruh produk katalog
$hasil = $conn->query(
    "SELECT * FROM products
    WHERE name != 'Buket Pink Rose'
    ORDER BY id DESC"
);

$produk = [];

if ($hasil) {
    while ($row = $hasil->fetch_assoc()) {
        $produk[] = $row;
    }
}

// Pengaturan navbar dan footer
$basePath = "../";
$isHome = false;
?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Katalog Produk | PinkFlow</title>

    <link
        rel="stylesheet"
        href="../assets/css/style.css"
    >

    <style>
        .catalog-page {
            min-height: 75vh;
            padding-block: 70px;
            background: #fff5f7;
        }

        .catalog-heading {
            margin-bottom: 42px;
            text-align: center;
        }

        .catalog-heading h1 {
            margin: 0 0 12px;
            font-family: Georgia, serif;
            font-size: clamp(2rem, 4vw, 3rem);
            font-weight: 500;
        }

        .catalog-heading p {
            color: #806d76;
        }

        .catalog-page .product-grid {
            row-gap: 28px;
        }

        .catalog-empty {
            grid-column: 1 / -1;
            padding: 50px 20px;
            text-align: center;
            background: #fff5f7;
            border-radius: 20px;
        }

        .catalog-empty h3 {
            font-family: Georgia, serif;
        }

        @media (max-width: 650px) {
            .catalog-page {
                padding-block: 45px;
            }
        }
    </style>

</head>

<body>

    <?php require_once "../includes/navbar.php"; ?>

    <main>

        <section class="catalog-page">

            <div class="container">

                <div class="catalog-heading">

                    <p class="eyebrow">
                        OUR COLLECTION
                    </p>

                    <h1>
                        Temukan Hadiah Spesialmu
                    </h1>

                    <p>
                        Pilihan buket dan hadiah manis dari PinkFlow
                        untuk setiap momen berharga.
                    </p>

                </div>


                <div class="product-grid">

                    <?php if (count($produk) > 0): ?>

                        <?php foreach ($produk as $item): ?>

                            <article class="card product-card">

                                <div class="product-image">

                                    <?php if (!empty($item["image"])): ?>

                                        <img
                                            src="../assets/images/<?= htmlspecialchars($item["image"]) ?>"
                                            alt="<?= htmlspecialchars($item["name"]) ?>"
                                        >

                                    <?php else: ?>

                                        <div class="product-placeholder">
                                            ♡
                                        </div>

                                    <?php endif; ?>

                                </div>


                                <div class="card-body">

                                    <p class="product-category">
                                        <?= htmlspecialchars($item["category"]) ?>
                                    </p>

                                    <h3>
                                        <?= htmlspecialchars($item["name"]) ?>
                                    </h3>

                                    <p class="product-price">

                                        Rp <?= number_format(
                                            (float) $item["price"],
                                            0,
                                            ",",
                                            "."
                                        ) ?>

                                    </p>

                                    <a
                                        class="btn btn-outline product-button"
                                        href="detail.php?id=<?= (int) $item["id"] ?>"
                                    >

                                        Lihat Detail

                                    </a>

                                </div>

                            </article>

                        <?php endforeach; ?>

                    <?php else: ?>

                        <div class="catalog-empty">

                            <h3>
                                Produk segera hadir
                            </h3>

                            <p>
                                Produk PinkFlow akan ditampilkan di sini.
                            </p>

                        </div>

                    <?php endif; ?>

                </div>

            </div>

        </section>

    </main>


    <?php require_once "../includes/footer.php"; ?>

</body>

</html>
