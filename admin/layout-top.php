<?php
$currentPage = basename($_SERVER['PHP_SELF']);
$adminName = $_SESSION['admin_name'] ?? 'Admin';
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin PinkFlow</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/lenis@1.1.20/dist/lenis.css">
    <script defer src="https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/gsap.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/lenis@1.1.20/dist/lenis.min.js"></script>
    <script defer src="../assets/js/admin.js"></script>
    <script type="module" src="../assets/js/motion-effects.js"></script>
</head>
<body class="admin-body">
    <aside class="sidebar">
        <a class="sidebar-brand" href="dashboard.php">
            <img src="../assets/images/Logo.jpg" alt="PinkFlow">
            <span>
                <strong>PinkFlow</strong>
                <small>ADMIN STUDIO</small>
            </span>
        </a>

        <div class="sidebar-label">WORKSPACE</div>
        <nav class="sidebar-nav" aria-label="Navigasi admin">
            <a class="<?= $currentPage === 'dashboard.php' ? 'active' : '' ?>" href="dashboard.php">
                <span class="nav-icon">⌂</span> Dashboard
            </a>
            <a class="<?= $currentPage === 'produk.php' ? 'active' : '' ?>" href="produk.php">
                <span class="nav-icon">✿</span> Produk
            </a>
            <a class="<?= in_array($currentPage, ['pesanan.php', 'detail-pesanan.php'], true) ? 'active' : '' ?>" href="pesanan.php">
                <span class="nav-icon">▤</span> Pesanan
            </a>
        </nav>

        <div class="sidebar-note">
            <span class="sidebar-note-icon">✦</span>
            <strong>Make it lovely</strong>
            <p>Small details make every moment special.</p>
        </div>

        <a href="../index.php" class="sidebar-site">↗ Lihat website</a>
        <a href="logout.php" class="sidebar-logout"><span>⇥</span> Keluar</a>
    </aside>

    <main class="admin-main">
        <header class="admin-topbar">
            <div class="topbar-mobile-brand">
                <img src="../assets/images/Logo.jpg" alt="PinkFlow">
                <strong>PinkFlow</strong>
            </div>
            <div class="topbar-spacer"></div>
            <span class="topbar-greeting">Welcome back, <?= e($adminName) ?> ✿</span>
            <span class="admin-avatar"><?= e(strtoupper(substr($adminName, 0, 1))) ?></span>
        </header>
        <div class="admin-content">
