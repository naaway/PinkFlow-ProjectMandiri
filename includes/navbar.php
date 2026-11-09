
<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$basePath = $basePath ?? "";

// Mendeteksi halaman yang sedang dibuka
$currentPage = basename($_SERVER["PHP_SELF"]);

// Menghitung jumlah produk di keranjang
$cartCount = 0;

if (!empty($_SESSION["cart"]) && is_array($_SESSION["cart"])) {
    foreach ($_SESSION["cart"] as $quantity) {
        $cartCount += (int) $quantity;
    }
}
?>

<header class="navbar">

    <div class="container navbar-inner">

        <!-- LOGO PINKFLOW -->
        <a href="<?= $basePath ?>index.php" class="brand">
            <span class="brand-name">PinkFlow</span>
            <small>FLOWER & GIFTS</small>
        </a>

        <!-- MENU NAVIGASI -->
        <nav class="nav-links">

            <a
                href="<?= $basePath ?>index.php"
                class="<?= $currentPage === 'index.php' ? 'active' : '' ?>"
                <?= $currentPage === 'index.php' ? 'aria-current="page"' : '' ?>
            >
                Beranda
            </a>

            <a
                href="<?= $basePath ?>pages/katalog.php"
                class="<?= $currentPage === 'katalog.php' ? 'active' : '' ?>"
                <?= $currentPage === 'katalog.php' ? 'aria-current="page"' : '' ?>
            >
                Katalog
            </a>

            <a
                href="<?= $basePath ?>pages/tentang.php"
                class="<?= $currentPage === 'tentang.php' ? 'active' : '' ?>"
                <?= $currentPage === 'tentang.php' ? 'aria-current="page"' : '' ?>
            >
                Tentang Kami
            </a>

        </nav>

        <!-- IKON KERANJANG -->
        <?php if (empty($hideCartIcon)): ?>

            <div class="navbar-actions">

                <a
                    href="<?= $basePath ?>pages/cart.php"
                    class="navbar-cart"
                    aria-label="Keranjang, <?= $cartCount ?> produk"
                >

                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        width="22"
                        height="22"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.8"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    >
                        <path d="M6 7h12l1 13H5L6 7z"/>
                        <path d="M9 9V6a3 3 0 0 1 6 0v3"/>
                    </svg>

                    <?php if ($cartCount > 0): ?>
                        <span class="cart-badge">
                            <?= $cartCount > 99 ? "99+" : $cartCount ?>
                        </span>
                    <?php endif; ?>

                </a>

            </div>

        <?php endif; ?>

    </div>

</header>
