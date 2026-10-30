
<?php
require_once "config/database.php";
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>PinkFlow — Flower & Gifts</title>

    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>

    <?php
    $basePath = "";
    $isHome = true;

    require_once "includes/navbar.php";
    ?>

    <main>

        <!-- HERO SECTION -->
        <section class="hero" id="beranda">

            <div class="container hero-grid">

                <div class="hero-content">

                    <p class="eyebrow">
                        A LITTLE GIFT, A LOT OF LOVE
                    </p>

                    <h1>
                        Berikan hadiah manis untuk
                        <span>momen spesialmu.</span>
                    </h1>

                    <p class="hero-description">
                        Temukan buket bunga dan hadiah pilihan yang dirangkai
                        dengan penuh cinta untuk orang-orang tersayang.
                    </p>

                    <div class="hero-actions">

                        <a
                            href="pages/katalog.php"
                            class="btn btn-primary"
                        >
                            Jelajahi Katalog
                        </a>

                        <a
                            href="pages/custom.php"
                            class="btn btn-outline"
                        >
                            Pesan Custom
                        </a>

                    </div>

                    <p class="hero-note">
                        Dirangkai dengan hati, diberikan dengan cinta ♡
                    </p>

                </div>

                <div class="hero-visual">

                    <div class="hero-image-frame">

                        <img
                            src="assets/images/hero-bouquet.jpg"
                            alt="Buket bunga PinkFlow"
                            onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';"
                        >

                        <div class="image-placeholder">

                            <span>♡</span>

                            <p>PinkFlow Flower & Gifts</p>

                            <small>
                                Foto buket pilihanmu bisa ditambahkan di sini
                            </small>

                        </div>

                    </div>

                    <div class="hero-decoration">
                        made with love ♡
                    </div>

                </div>

            </div>

        </section>


        <!-- CUSTOM ORDER SECTION -->
        <section class="custom-section" id="pesan">

            <div class="container custom-box">

                <div>

                    <p class="eyebrow">
                        MADE JUST FOR YOU
                    </p>

                    <h2>
                        Ingin buket yang lebih personal?
                    </h2>

                    <p>
                        Ceritakan ide dan warna favoritmu. Yuk, buat hadiah
                        custom yang sesuai dengan momen spesialmu.
                    </p>

                </div>

                <a
                    href="pages/custom.php"
                    class="btn btn-primary"
                >
                    Buat Pesanan Custom
                </a>

            </div>

        </section>

    </main>


    <?php
    $basePath = "";
    require_once "includes/footer.php";
    ?>

</body>

</html>
