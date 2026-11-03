<?php
session_start();

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

require_once "../config/database.php";

$keyword = trim($_GET["keyword"] ?? "");

if ($keyword !== "") {
    $search = "%" . $keyword . "%";

    $stmt = $conn->prepare(
        "SELECT id, order_code, order_type, customer_name,
                phone, total, status, created_at
         FROM orders
         WHERE order_code LIKE ?
            OR customer_name LIKE ?
         ORDER BY created_at DESC"
    );

    $stmt->bind_param("ss", $search, $search);
    $stmt->execute();

    $pesanan = $stmt->get_result();
} else {
    $pesanan = $conn->query(
        "SELECT id, order_code, order_type, customer_name,
                phone, total, status, created_at
         FROM orders
         ORDER BY created_at DESC"
    );
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Pesanan | PinkFlow</title>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        * 
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

        .container {
            max-width: 1500px;
            margin: auto;
            padding: 42px;
        }

        /* HEADER */

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
            flex-wrap: wrap;
            margin-bottom: 35px;
        }

        .header h1 {
            font-size: 30px;
            font-weight: 700;
            color: #6c3c52;
            margin-bottom: 10px;
        }

        .header p {
            font-size: 14px;
            color: #946d80;
            line-height: 1.8;
        }

        .back-link {
            display: inline-block;
            padding: 13px 18px;
            background: #f0c7d8;
            color: #8d3e60;
            text-decoration: none;
            border-radius: 12px;
            font-size: 13px;
            font-weight: 600;
            transition: 0.2s;
        }

        .back-link:hover {
            background: #e9b4cb;
            transform: translateY(-2px);
        }

        /* SEARCH */

        .search-box {
            display: flex;
            gap: 13px;
            margin-bottom: 28px;
            flex-wrap: wrap;
        }

        .search-box input {
            flex: 1;
            min-width: 230px;
            padding: 15px 18px;
            border: 1px solid #efcfdd;
            border-radius: 13px;
            background: #fff;
            color: #573746;
            font-family: inherit;
            font-size: 14px;
            outline: none;
            transition: 0.2s;
        }

        .search-box input:focus {
            border-color: #c96b91;
            box-shadow: 0 0 0 3px rgba(201, 107, 145, 0.12);
        }

        .search-box input::placeholder {
            color: #b49aa7;
        }

        .search-box button {
            padding: 14px 24px;
            background: #c96b91;
            color: #fff;
            border: none;
            border-radius: 13px;
            font-family: inherit;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: 0.2s;
        }

        .search-box button:hover {
            background: #a63e6b;
            transform: translateY(-2px);
        }

        /* TABLE CARD */

        .table-card {
            background: #fff;
            padding: 30px;
            border-radius: 22px;
            border: 1px solid #f3d7e3;
            box-shadow: 0 7px 22px rgba(166, 62, 107, 0.06);
            overflow-x: auto;
        }

        .table-card h2 {
            font-size: 21px;
            font-weight: 600;
            color: #6c3c52;
            margin-bottom: 25px;
        }

        /* TABLE */

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 950px;
        }

        th {
            padding: 17px 15px;
            text-align: left;
            font-size: 12px;
            font-weight: 600;
            color: #80566a;
            background: #fcecf3;
            border-bottom: 1px solid #f2d9e4;
            white-space: nowrap;
        }

        td {
            padding: 18px 15px;
            font-size: 13px;
            color: #765363;
            border-bottom: 1px solid #f6e7ee;
            vertical-align: middle;
        }

        tbody tr:last-child td {
            border-bottom: none;
        }

        tbody tr:hover {
            background: #fff8fb;
        }

        /* BADGES */

        .type-badge {
            display: inline-block;
            padding: 7px 12px;
            border-radius: 20px;
            background: #f9dce8;
            color: #a63e6b;
            font-size: 11px;
            font-weight: 600;
            white-space: nowrap;
        }

        .type-badge.custom {
            background: #e9def8;
            color: #805cae;
        }

        /* DETAIL LINK */

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
            white-space: nowrap;
        }

        .detail-link:hover {
            background: #f4c9dc;
            transform: translateY(-2px);
        }

        /* EMPTY STATE */

        .empty {
            text-align: center;
            padding: 40px;
            color: #946d80;
            font-size: 14px;
        }

        /* RESPONSIVE */

        @media (max-width: 850px) {
            .container {
                padding: 28px 20px;
            }

            .header h1 {
                font-size: 26px;
            }

            .table-card {
                padding: 22px;
            }
        }

        @media (max-width: 550px) {
            .container {
                padding: 20px 12px;
            }

            .header h1 {
                font-size: 23px;
            }

            .search-box {
                flex-direction: column;
            }

            .search-box input,
            .search-box button {
                width: 100%;
            }

            .table-card {
                padding: 18px;
            }

            .table-card h2 {
                font-size: 19px;
            }
        }

    </style>
</head>

<body>

<div class="container">

    <div class="header">
        <div>
            <h1>Daftar Pesanan 🌸</h1>
            <p>Kelola informasi pesanan pelanggan PinkFlow.</p>
        </div>

        <a href="dashboard.php" class="back-link">
            ← Kembali ke Dashboard
        </a>
    </div>

    <form method="GET" class="search-box">
        <input
            type="text"
            name="keyword"
            placeholder="Cari kode pesanan atau nama pelanggan..."
            value="<?= htmlspecialchars($keyword); ?>"
        >

        <button type="submit">Cari Pesanan</button>
    </form>

    <div class="table-card">

        <h2>Semua Pesanan</h2>

        <table>
            <thead>
                <tr>
                    <th>No.</th>
                    <th>Kode Pesanan</th>
                    <th>Nama Pelanggan</th>
                    <th>No. Telepon</th>
                    <th>Jenis</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Tanggal</th>
                    <th>Detail</th>
                </tr>
            </thead>

            <tbody>

                <?php if ($pesanan && $pesanan->num_rows > 0): ?>

                    <?php $no = 1; ?>

                    <?php while ($row = $pesanan->fetch_assoc()): ?>

                        <tr>
                            <td><?= $no++; ?></td>

                            <td>
                                <?= htmlspecialchars($row["order_code"]); ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($row["customer_name"]); ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($row["phone"]); ?>
                            </td>

                            <td>
                                <span class="type-badge">
                                    <?= htmlspecialchars(ucfirst($row["order_type"])); ?>
                                </span>
                            </td>

                            <td>
                                Rp<?= number_format((float)$row["total"], 0, ",", "."); ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($row["status"]); ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($row["created_at"]); ?>
                            </td>

                            <td>
                                <a
                                    href="detail_pesanan.php?id=<?= (int)$row["id"]; ?>"
                                    class="detail-link"
                                >
                                    Lihat Detail
                                </a>
                            </td>
                        </tr>

                    <?php endwhile; ?>

                <?php else: ?>

                    <tr>
                        <td colspan="9" class="empty">
                            Pesanan tidak ditemukan.
                        </td>
                    </tr>

                <?php endif; ?>

            </tbody>
        </table>

    </div>

</div>

</body>
</html>