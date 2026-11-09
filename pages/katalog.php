
<?php
session_start();

require_once "../config/database.php";

// Ambil seluruh produk katalog
$hasil = $conn->query(
    "SELECT * FROM products ORDER BY id DESC"
);

$produk = [];

if ($hasil) {
    while ($row = $hasil->fetch_assoc()) {
        $produk[] = $row;
    }
}

// Hitung jumlah produk dalam keranjang
$cartCount = 0;

if (!empty($_SESSION["cart"])) {
    foreach ($_SESSION["cart"] as $quantity) {
        $cartCount += (int) $quantity;
    }
}

// Pengaturan navbar
$basePath = "../";
$isHome = false;
?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Katalog Produk | PinkFlow</title>

    <link rel="stylesheet" href="../assets/css/style.css">

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

        /* INFORMASI STOK */
        .product-stock {
            margin-top: 7px;
            margin-bottom: 0;
            font-size: 13px;
            color: #806d76;
        }

        .product-stock.available {
            color: #56815b;
        }

        .product-stock.empty {
            color: #c34f78;
            font-weight: 600;
        }

        /* TOMBOL PRODUK */
        .catalog-product-actions {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-top: 20px;
        }

        .catalog-cart-button {
            width: 48px;
            min-width: 48px;
            height: 45px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid #e7a0ba;
            border-radius: 12px;
            background: #fff0f5;
            color: #bd3f75;
            font-size: 20px;
            text-decoration: none;
            cursor: pointer;
            transition: 0.25s ease;
        }

        .catalog-cart-button:hover {
            background: #f8dce7;
            transform: translateY(-2px);
        }

        .catalog-cart-button:disabled {
            opacity: 0.45;
            cursor: not-allowed;
        }

        .catalog-checkout-button {
            flex: 1;
            min-height: 45px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid #b54470;
            border-radius: 12px;
            background: #b54470;
            color: #ffffff;
            text-decoration: none;
            font-weight: 600;
            transition: 0.25s ease;
        }

        .catalog-checkout-button:hover {
            background: #96365d;
        }

        .catalog-checkout-button.disabled {
            opacity: 0.5;
            pointer-events: none;
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

                            <?php
                            $stok = (int) $item["stock"];
                            $produkHabis = $stok <= 0;
                            ?>

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

                                    <!-- INFORMASI STOK -->
                                    <p class="product-stock <?= $produkHabis ? 'empty' : 'available' ?>">

                                        <?php if ($produkHabis): ?>

                                            Stok habis

                                        <?php else: ?>

                                            Stok tersedia: <?= $stok ?>

                                        <?php endif; ?>

                                    </p>

                                    <p class="product-price">

                                        Rp <?= number_format(
                                            (float) $item["price"],
                                            0,
                                            ",",
                                            "."
                                        ) ?>

                                    </p>


                                    <!-- TOMBOL KERANJANG DAN CHECKOUT -->
                                    <div class="catalog-product-actions">

                                        <button
                                            type="button"
                                            class="catalog-cart-button"
                                            title="Tambahkan ke keranjang"
                                            aria-label="Tambahkan <?= htmlspecialchars($item["name"]) ?> ke keranjang"
                                            data-product-id="<?= (int) $item["id"] ?>"
                                            <?= $produkHabis ? "disabled" : "" ?>
                                        >
                                            🛒
                                        </button>


                                        <?php if (!$produkHabis): ?>

                                            <a
                                                class="catalog-checkout-button"
                                                href="checkout.php?buy=<?= (int) $item["id"] ?>"
                                            >
                                                Checkout
                                            </a>

                                        <?php else: ?>

                                            <span class="catalog-checkout-button disabled">
                                                Stok Habis
                                            </span>

                                        <?php endif; ?>

                                    </div>

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


    <script>

        // Tombol keranjang menggunakan AJAX
        document.querySelectorAll(".catalog-cart-button").forEach(button => {

            button.addEventListener("click", async function () {

                const productId = this.dataset.productId;

                this.disabled = true;

                try {

                    const formData = new FormData();
                    formData.append("product_id", productId);

                    const response = await fetch("add_to_cart.php", {
                        method: "POST",
                        body: formData
                    });

                    const result = await response.json();

                    if (result.success) {

                        const badge = document.querySelector(".cart-badge");

                        if (badge) {
                            badge.textContent = result.cart_count;
                        }

                        const cartCountElement = document.querySelector("[data-cart-count]");

                        if (cartCountElement) {
                            cartCountElement.textContent = result.cart_count;
                        }

                        button.innerHTML = "✓";

                        setTimeout(() => {
                            button.innerHTML = "🛍";
                        }, 700);

                    } else {

                        alert(result.message);

                    }

                } catch (error) {

                    alert("Gagal menambahkan produk. Silakan coba kembali.");

                } finally {

                    this.disabled = false;

                }

            });

        });

    </script>

</body>
</html>
