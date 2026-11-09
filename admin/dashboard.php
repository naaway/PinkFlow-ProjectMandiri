<?php
session_start();

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

require_once "../config/database.php";

// Menghitung seluruh pesanan
$total_pesanan = 0;
$result_total = $conn->query("SELECT COUNT(*) AS total FROM orders");

if ($result_total) {
    $row_total = $result_total->fetch_assoc();
    $total_pesanan = $row_total["total"];
}

// Menghitung pesanan katalog
$total_katalog = 0;
$result_katalog = $conn->query(
    "SELECT COUNT(*) AS total FROM orders WHERE order_type = 'catalog'"
);

if ($result_katalog) {
    $row_katalog = $result_katalog->fetch_assoc();
    $total_katalog = $row_katalog["total"];
}

// Menghitung pesanan custom
$total_custom = 0;
$result_custom = $conn->query(
    "SELECT COUNT(*) AS total FROM orders WHERE order_type = 'custom'"
);

if ($result_custom) {
    $row_custom = $result_custom->fetch_assoc();
    $total_custom = $row_custom["total"];
}

// Mengambil 5 pesanan terbaru
$pesanan = $conn->query(
    "SELECT id, order_code, customer_name, order_type, total, status, created_at
     FROM orders
     ORDER BY created_at DESC
     LIMIT 5"
);
?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Dashboard Admin | PinkFlow</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap"
        rel="stylesheet"
    >

    <style>

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: #fff4f8;
            color: #573746;
        }

        /* SIDEBAR */

        .admin-container {
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 260px;
            min-height: 100vh;
            background: #f0c7d8;
            padding: 35px 22px;
            display: flex;
            flex-direction: column;
            flex-shrink: 0;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 48px;
            padding-left: 8px;
        }

        .brand-icon {
            width: 46px;
            height: 46px;
            background: #fff;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
        }

        .brand h2 {
            font-size: 23px;
            font-weight: 700;
            color: #a63e6b;
        }

        .menu-label {
            font-size: 12px;
            font-weight: 600;
            letter-spacing: 1.4px;
            color: #92546f;
            margin: 0 0 17px 12px;
        }

        .sidebar-menu {
            display: flex;
            flex-direction: column;
            gap: 11px;
        }

        .sidebar a {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 16px;
            text-decoration: none;
            color: #75445a;
            font-size: 14px;
            font-weight: 500;
            border-radius: 14px;
            transition: 0.25s ease;
        }

        .sidebar a:hover {
            background: rgba(255, 255, 255, 0.75);
            color: #a63e6b;
            transform: translateX(3px);
        }

        .sidebar a.active {
            background: #fff;
            color: #a63e6b;
            box-shadow: 0 5px 15px rgba(166, 62, 107, 0.10);
            font-weight: 600;
        }

        .menu-icon {
            width: 23px;
            text-align: center;
            font-size: 19px;
        }

        .sidebar-bottom {
            margin-top: auto;
            padding-top: 30px;
        }

        .logout {
            color: #a63e6b !important;
        }

        /* MAIN CONTENT */

        .main-content {
            flex: 1;
            min-width: 0;
            padding: 42px;
        }

        /* TOPBAR */

        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 25px;
            margin-bottom: 40px;
        }

        .welcome {
            flex: 1;
            min-width: 0;
        }

        .welcome-header {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 18px;
            margin-bottom: 12px;
        }

        .welcome h1 {
            font-size: 31px;
            font-weight: 700;
            color: #6c3c52;
            line-height: 1.4;
        }

        .welcome p {
            font-size: 14px;
            color: #946d80;
            line-height: 1.9;
        }

        /* TOMBOL TAMBAH PRODUK */

        .add-product-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 12px 18px;
            background: #c6537d;
            color: #fff;
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
            border-radius: 13px;
            white-space: nowrap;
            transition: 0.25s ease;
            box-shadow: 0 5px 15px rgba(166, 62, 107, 0.12);
        }

        .add-product-btn:hover {
            background: #a63e6b;
            transform: translateY(-2px);
        }

        /* PROFIL ADMIN */

        .admin-profile {
            display: flex;
            align-items: center;
            gap: 14px;
            background: #fff;
            padding: 13px 19px;
            border-radius: 17px;
            box-shadow: 0 5px 18px rgba(166, 62, 107, 0.07);
            flex-shrink: 0;
        }

        .profile-icon {
            width: 46px;
            height: 46px;
            background: #f9dce8;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #a63e6b;
            font-size: 21px;
        }

        .profile-info p {
            font-size: 14px;
            font-weight: 600;
            color: #6c3c52;
        }

        .profile-info span {
            font-size: 12px;
            color: #946d80;
        }

        /* STATISTICS */

        .section-heading {
            margin-bottom: 22px;
        }

        .section-heading h2 {
            font-size: 21px;
            color: #6c3c52;
            font-weight: 600;
        }

        .section-heading p {
            font-size: 13px;
            color: #946d80;
            margin-top: 7px;
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 24px;
            margin-bottom: 38px;
        }

        .stat-card {
            background: #fff;
            padding: 29px;
            border-radius: 22px;
            border: 1px solid #f3d7e3;
            box-shadow: 0 7px 22px rgba(166, 62, 107, 0.06);
            transition: 0.25s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 28px rgba(166, 62, 107, 0.12);
        }

        .stat-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        .stat-icon {
            width: 54px;
            height: 54px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 17px;
            font-size: 25px;
        }

        .icon-pink {
            background: #f9d6e5;
        }

        .icon-purple {
            background: #e8ddf8;
        }

        .icon-peach {
            background: #ffe6d5;
        }

        .stat-small {
            font-size: 12px;
            font-weight: 500;
            color: #a65d7d;
            background: #fff1f6;
            padding: 8px 12px;
            border-radius: 20px;
        }

        .stat-card p {
            font-size: 14px;
            color: #946d80;
            margin-bottom: 10px;
        }

        .stat-card h2 {
            font-size: 38px;
            font-weight: 700;
            color: #783e59;
        }

        /* ORDER TABLE */

        .table-card {
            background: #fff;
            padding: 30px;
            border-radius: 22px;
            border: 1px solid #f3d7e3;
            box-shadow: 0 7px 22px rgba(166, 62, 107, 0.06);
            overflow-x: auto;
        }

        .table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
            margin-bottom: 27px;
        }

        .table-header h2 {
            font-size: 21px;
            color: #6c3c52;
            font-weight: 600;
        }

        .view-all {
            text-decoration: none;
            color: #a63e6b;
            background: #fbe2ec;
            padding: 12px 17px;
            border-radius: 12px;
            font-size: 13px;
            font-weight: 600;
            transition: 0.2s;
        }

        .view-all:hover {
            background: #f4c9dc;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 700px;
        }

        th {
            padding: 17px 15px;
            text-align: left;
            font-size: 12px;
            font-weight: 600;
            color: #80566a;
            background: #fcecf3;
            border-bottom: 1px solid #f2d9e4;
        }

        td {
            padding: 19px 15px;
            font-size: 13px;
            color: #765363;
            border-bottom: 1px solid #f6e7ee;
        }

        tbody tr:last-child td {
            border-bottom: none;
        }

        tbody tr:hover {
            background: #fff8fb;
        }

        .order-code {
            color: #a63e6b;
            font-weight: 600;
        }

        .customer-name {
            color: #6c3c52;
            font-weight: 500;
        }

        .type-badge {
            display: inline-block;
            padding: 7px 12px;
            border-radius: 20px;
            background: #f9dce8;
            color: #a63e6b;
            font-size: 11px;
            font-weight: 600;
        }

        .type-badge.custom {
            background: #e9def8;
            color: #805cae;
        }

        .status-badge {
            display: inline-block;
            padding: 8px 12px;
            border-radius: 20px;
            background: #ffead9;
            color: #a9672c;
            font-size: 11px;
            font-weight: 600;
        }

        .detail-link {
            display: inline-block;
            text-decoration: none;
            color: #a63e6b;
            font-size: 12px;
            font-weight: 600;
            background: #fbe2ec;
            padding: 10px 14px;
            border-radius: 11px;
            transition: 0.2s;
        }

        .detail-link:hover {
            background: #f4c9dc;
        }

        .empty-row {
            text-align: center;
            padding: 40px;
            color: #946d80;
        }

        .footer {
            text-align: center;
            padding: 32px 0 5px;
            font-size: 12px;
            color: #a18491;
        }

        /* RESPONSIVE */

        @media (max-width: 1100px) {

            .main-content {
                padding: 30px;
            }

            .stats {
                gap: 16px;
            }

            .stat-card {
                padding: 22px;
            }

            .stat-card h2 {
                font-size: 33px;
            }

            .welcome h1 {
                font-size: 27px;
            }

        }

        @media (max-width: 850px) {

            .admin-container {
                flex-direction: column;
            }

            .sidebar {
                width: 100%;
                min-height: auto;
                padding: 22px;
            }

            .brand {
                margin-bottom: 22px;
            }

            .sidebar-menu {
                flex-direction: row;
                flex-wrap: wrap;
            }

            .sidebar-menu a {
                flex: 1;
                justify-content: center;
                min-width: 140px;
            }

            .menu-label {
                display: none;
            }

            .sidebar-bottom {
                margin-top: 15px;
                padding-top: 0;
            }

            .main-content {
                padding: 25px 20px;
            }

            .stats {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .topbar {
                align-items: flex-start;
            }

            .admin-profile {
                padding: 10px 13px;
            }

            .welcome-header {
                gap: 12px;
            }

        }

        @media (max-width: 650px) {

            .topbar {
                flex-direction: column;
                gap: 20px;
            }

            .welcome {
                width: 100%;
            }

            .welcome-header {
                align-items: flex-start;
                flex-direction: column;
            }

            .admin-profile {
                align-self: flex-start;
            }

        }

        @media (max-width: 550px) {

            .stats {
                grid-template-columns: 1fr;
            }

            .welcome h1 {
                font-size: 25px;
            }

            .table-card {
                padding: 20px;
            }

            .table-header {
                align-items: flex-start;
                flex-direction: column;
            }

        }

    </style>

</head>

<body>

<div class="admin-container">

    <!-- SIDEBAR -->

    <aside class="sidebar">

        <div class="brand">
            <div class="brand-icon">🌸</div>
            <h2>PinkFlow</h2>
        </div>

        <p class="menu-label">MENU UTAMA</p>

        <nav class="sidebar-menu">

        <a href="produk.php">
          <span class="menu-icon">🌸</span>
          Kelola Produk
        </a>
            <a href="dashboard.php" class="active">
                <span class="menu-icon">⌂</span>
                Dashboard
            </a>

            <a href="pesanan.php">
                <span class="menu-icon">♡</span>
                Daftar Pesanan
            </a>

        </nav>

        <div class="sidebar-bottom">

            <a href="logout.php" class="logout">
                <span class="menu-icon">↪</span>
                Logout
            </a>

        </div>

    </aside>

    <!-- MAIN CONTENT -->

    <main class="main-content">

        <!-- TOPBAR -->

        <div class="topbar">

            <div class="welcome">

                <div class="welcome-header">

                    <h1>Dashboard Admin 🌷</h1>

                    <a href="tambah_produk.php" class="add-product-btn">
                        + Tambah Produk
                    </a>

                </div>

                <p>
                    Selamat datang kembali,
                    <?= htmlspecialchars($_SESSION["admin_username"] ?? "Admin"); ?>!
                    <br>
                    Berikut ringkasan pesanan PinkFlow hari ini.
                </p>

            </div>

            <div class="admin-profile">

                <div class="profile-icon">♡</div>

                <div class="profile-info">

                    <p>
                        <?= htmlspecialchars($_SESSION["admin_username"] ?? "Admin"); ?>
                    </p>

                    <span>Administrator</span>

                </div>

            </div>

        </div>

        <!-- STATISTICS -->

        <div class="section-heading">

            <h2>Ringkasan Pesanan</h2>

            <p>
                Informasi jumlah pesanan yang masuk ke PinkFlow.
            </p>

        </div>

        <section class="stats">

            <div class="stat-card">

                <div class="stat-top">

                    <div class="stat-icon icon-pink">♡</div>

                    <span class="stat-small">Total</span>

                </div>

                <p>Seluruh Pesanan</p>

                <h2><?= $total_pesanan; ?></h2>

            </div>

            <div class="stat-card">

                <div class="stat-top">

                    <div class="stat-icon icon-purple">🌸</div>

                    <span class="stat-small">Katalog</span>

                </div>

                <p>Pesanan Katalog</p>

                <h2><?= $total_katalog; ?></h2>

            </div>

            <div class="stat-card">

                <div class="stat-top">

                    <div class="stat-icon icon-peach">🎀</div>

                    <span class="stat-small">Custom</span>

                </div>

                <p>Pesanan Custom</p>

                <h2><?= $total_custom; ?></h2>

            </div>

        </section>

        <!-- RECENT ORDERS -->

        <section class="table-card">

            <div class="table-header">

                <div>
                    <h2>Pesanan Terbaru</h2>
                </div>

                <a href="pesanan.php" class="view-all">
                    Lihat Semua →
                </a>

            </div>

            <table>

                <thead>

                    <tr>
                        <th>KODE PESANAN</th>
                        <th>PELANGGAN</th>
                        <th>JENIS</th>
                        <th>TOTAL</th>
                        <th>STATUS</th>
                        <th>DETAIL</th>
                    </tr>

                </thead>

                <tbody>

                    <?php if ($pesanan && $pesanan->num_rows > 0): ?>

                        <?php while ($row = $pesanan->fetch_assoc()): ?>

                            <tr>

                                <td class="order-code">
                                    <?= htmlspecialchars($row["order_code"]); ?>
                                </td>

                                <td class="customer-name">
                                    <?= htmlspecialchars($row["customer_name"]); ?>
                                </td>

                                <td>

                                    <span class="type-badge <?= $row["order_type"] === "custom" ? "custom" : ""; ?>">

                                        <?= htmlspecialchars(ucfirst($row["order_type"])); ?>

                                    </span>

                                </td>

                                <td>
                                    Rp<?= number_format(
                                        (float)$row["total"],
                                        0,
                                        ",",
                                        "."
                                    ); ?>
                                </td>

                                <td>

                                    <span class="status-badge">
                                        <?= htmlspecialchars($row["status"]); ?>
                                    </span>

                                </td>

                                <td>

                                    <a
                                        class="detail-link"
                                        href="detail_pesanan.php?id=<?= (int)$row["id"]; ?>"
                                    >
                                        Lihat Detail
                                    </a>

                                </td>

                            </tr>

                        <?php endwhile; ?>

                    <?php else: ?>

                        <tr>

                            <td colspan="6" class="empty-row">
                                Belum ada pesanan yang masuk 🌸
                            </td>

                        </tr>

                    <?php endif; ?>

                </tbody>

            </table>

        </section>

        <!-- FOOTER -->

        <div class="footer">
            © 2026 PinkFlow Admin · Made with ♡
        </div>

    </main>

</div>

</body>

</html>
