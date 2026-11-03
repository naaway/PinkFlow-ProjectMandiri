
<?php
session_start();

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

require_once "../config/database.php";

// Mengambil ID pesanan dari URL
$id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    header("Location: pesanan.php");
    exit;
}

// Mengambil data pesanan berdasarkan ID
$stmt = $conn->prepare(
    "SELECT id, order_code, order_type, customer_name,
            phone, address, custom_type, custom_theme,
            custom_budget, notes, total, status, created_at
     FROM orders
     WHERE id = ?"
);

$stmt->bind_param("i", $id);
$stmt->execute();

$result = $stmt->get_result();
$pesanan = $result->fetch_assoc();

if (!$pesanan) {
    header("Location: pesanan.php");
    exit;
}

// Menampilkan data dengan aman
function tampilkan($data) {
    if ($data === null || $data === "") {
        return "-";
    }

    return htmlspecialchars((string)$data, ENT_QUOTES, "UTF-8");
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Detail Pesanan | PinkFlow</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

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
            font-size: 16px;
        }

        .container {
            max-width: 1250px;
            margin: auto;
            padding: 45px;
        }

        /* HEADER */

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 25px;
            flex-wrap: wrap;
            margin-bottom: 35px;
        }

        .header h1 {
            font-size: 34px;
            font-weight: 700;
            color: #6c3c52;
            margin-bottom: 12px;
        }

        .header p {
            font-size: 16px;
            color: #946d80;
            line-height: 1.8;
        }

        .back-link {
            display: inline-block;
            padding: 14px 22px;
            background: #efc3d5;
            color: #8d3e60;
            text-decoration: none;
            border-radius: 13px;
            font-size: 15px;
            font-weight: 600;
            transition: 0.2s;
        }

        .back-link:hover {
            background: #e7aec7;
            transform: translateY(-2px);
        }

        /* DETAIL CARD */

        .detail-card {
            background: #fff;
            padding: 35px 40px;
            border-radius: 24px;
            border: 1px solid #f1d2df;
            box-shadow: 0 8px 25px rgba(166, 62, 107, 0.07);
            margin-bottom: 28px;
        }

        .detail-card h2 {
            color: #6c3c52;
            font-size: 25px;
            font-weight: 600;
            margin-bottom: 28px;
            padding-bottom: 20px;
            border-bottom: 1px solid #f3e0e9;
        }

        /* DETAIL ROW */

        .detail-row {
            display: grid;
            grid-template-columns: 260px minmax(0, 1fr);
            gap: 30px;
            padding: 22px 0;
            border-bottom: 1px solid #f5e5ed;
            align-items: center;
        }

        .detail-row:last-child {
            border-bottom: none;
        }

        .detail-label {
            color: #946d80;
            font-size: 16px;
            font-weight: 500;
            line-height: 1.5;
        }

        .detail-value {
            display: flex;
            align-items: center;
            min-height: 40px;

            color: #6c3c52;
            font-size: 17px;
            font-weight: 500;
            overflow-wrap: anywhere;
            white-space: pre-line;
            line-height: 1.5;
        }

        /* BADGES */

        .type-badge,
        .status-value {
            display: inline-flex;
            align-items: center;
            justify-content: center;

            box-sizing: border-box;
            height: 40px;
            padding: 0 14px;
            margin: 0;

            font-family: 'Poppins', sans-serif;
            font-size: 14px;
            font-weight: 600;
            line-height: 1;
            text-align: center;
            white-space: nowrap;
            vertical-align: middle;

            border: none;
            border-radius: 12px;
        }

        .type-badge {
            background: #f9dce8;
            color: #a63e6b;
        }

        .status-value {
            background: #ffead9;
            color: #a9672c;
        }

        /* TOTAL */

        .total-value {
            color: #a63e6b;
            font-size: 30px;
            font-weight: 700;
        }

        /* RESPONSIVE */

        @media (max-width: 800px) {
            .container {
                padding: 30px 20px;
            }

            .header h1 {
                font-size: 28px;
            }

            .detail-card {
                padding: 25px;
            }

            .detail-card h2 {
                font-size: 22px;
            }

            .detail-row {
                grid-template-columns: 1fr;
                gap: 10px;
                padding: 18px 0;
                align-items: start;
            }

            .detail-value {
                font-size: 16px;
            }

            .detail-label {
                font-size: 14px;
            }

            .total-value {
                font-size: 25px;
            }
        }

        @media (max-width: 500px) {
            .container {
                padding: 20px 12px;
            }

            .detail-card {
                padding: 20px;
                border-radius: 18px;
            }

            .header h1 {
                font-size: 25px;
            }

            .detail-card h2 {
                font-size: 20px;
            }
        }
    </style>
</head>

<body>

<div class="container">

    <div class="header">
        <div>
            <h1>Detail Pesanan 🌸</h1>
            <p>Informasi lengkap pesanan pelanggan PinkFlow.</p>
        </div>

        <a href="pesanan.php" class="back-link">
            ← Kembali ke Pesanan
        </a>
    </div>

    <!-- Informasi pesanan -->
    <section class="detail-card">

        <h2>Informasi Pesanan</h2>

        <div class="detail-row">
            <div class="detail-label">Kode Pesanan</div>
            <div class="detail-value">
                <?= tampilkan($pesanan["order_code"]); ?>
            </div>
        </div>

        <div class="detail-row">
            <div class="detail-label">Tanggal Pesanan</div>
            <div class="detail-value">
                <?= tampilkan($pesanan["created_at"]); ?>
            </div>
        </div>

        <div class="detail-row">
            <div class="detail-label">Jenis Pesanan</div>
            <div class="detail-value">
                <span class="type-badge">
                    <?= tampilkan(ucfirst($pesanan["order_type"])); ?>
                </span>
            </div>
        </div>

        <div class="detail-row">
            <div class="detail-label">Status</div>
            <div class="detail-value">
                <span class="status-value">
                    <?= tampilkan($pesanan["status"]); ?>
                </span>
            </div>
        </div>

    </section>

    <!-- Informasi pelanggan -->
    <section class="detail-card">

        <h2>Informasi Pelanggan</h2>

        <div class="detail-row">
            <div class="detail-label">Nama Pelanggan</div>
            <div class="detail-value">
                <?= tampilkan($pesanan["customer_name"]); ?>
            </div>
        </div>

        <div class="detail-row">
            <div class="detail-label">Nomor Telepon</div>
            <div class="detail-value">
                <?= tampilkan($pesanan["phone"]); ?>
            </div>
        </div>

        <div class="detail-row">
            <div class="detail-label">Alamat</div>
            <div class="detail-value">
                <?= tampilkan($pesanan["address"]); ?>
            </div>
        </div>

    </section>

    <!-- Informasi custom -->
    <?php if ($pesanan["order_type"] === "custom"): ?>

        <section class="detail-card">

            <h2>Detail Pesanan Custom</h2>

            <div class="detail-row">
                <div class="detail-label">Jenis Buket</div>
                <div class="detail-value">
                    <?= tampilkan($pesanan["custom_type"]); ?>
                </div>
            </div>

            <div class="detail-row">
                <div class="detail-label">Tema</div>
                <div class="detail-value">
                    <?= tampilkan($pesanan["custom_theme"]); ?>
                </div>
            </div>

            <div class="detail-row">
                <div class="detail-label">Budget</div>
                <div class="detail-value">
                    <?= tampilkan($pesanan["custom_budget"]); ?>
                </div>
            </div>

        </section>

    <?php endif; ?>

    <!-- Catatan dan total -->
    <section class="detail-card">

        <h2>Catatan & Pembayaran</h2>

        <div class="detail-row">
            <div class="detail-label">Catatan Pelanggan</div>
            <div class="detail-value">
                <?= tampilkan($pesanan["notes"]); ?>
            </div>
        </div>

        <div class="detail-row">
            <div class="detail-label">Total Pesanan</div>
            <div class="detail-value total-value">
                Rp<?= number_format(
                    (float)$pesanan["total"],
                    0,
                    ",",
                    "."
                ); ?>
            </div>
        </div>

    </section>

</div>

</body>
</html>
