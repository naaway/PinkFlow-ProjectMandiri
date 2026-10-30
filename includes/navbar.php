
<?php
$basePath = $basePath ?? "";
$isHome = $isHome ?? false;
?>

<header class="navbar">

    <div class="container navbar-inner">

        <a href="<?= $basePath ?>index.php" class="brand">

            Pink<span>Flow</span>

            <small>Flower & Gifts</small>

        </a>


        <nav class="nav-links">

            <a href="<?= $basePath ?>index.php">
                Beranda
            </a>

            <a href="<?= $basePath ?>pages/katalog.php">
                Katalog
            </a>

            <a href="<?= $basePath ?>pages/tentang.php">
                Tentang Kami
            </a>

        </nav>


        <a
            href="<?= $basePath ?>pages/custom.php"
            class="btn btn-primary"
        >

            Pesan Sekarang

        </a>

    </div>

</header>
