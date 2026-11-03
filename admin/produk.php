
<?php
session_start();

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

require_once "../config/database.php";

// Mengambil seluruh produk
$produk = $conn->query(
    "SELECT id, name, category, price, stock, image, description
     FROM products
     ORDER BY id DESC"
);

// Token keamanan untuk form
if (empty($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Kelola Produk | PinkFlow</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: #fff4f8;
            color: #573746;
        }

        .page {
            max-width: 1200px;
            margin: auto;
            padding: 40px 25px;
        }

        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
            flex-wrap: wrap;
            margin-bottom: 30px;
        }

        .heading h1 {
            color: #6c3c52;
            font-size: 30px;
            margin-bottom: 8px;
        }

        .heading p {
            color: #946d80;
            font-size: 13px;
        }

        .actions {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 12px 18px;
            border-radius: 12px;
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
            border: none;
            cursor: pointer;
            font-family: inherit;
            transition: 0.2s ease;
        }

        .btn-dashboard {
            background: #fff;
            color: #a63e6b;
            border: 1px solid #f3d7e3;
        }

        .btn-add {
            background: #c6537d;
            color: #fff;
        }

        .btn:hover {
            transform: translateY(-2px);
        }

        .btn-add:hover {
            background: #a63e6b;
        }

        .product-card {
            background: #fff;
            border: 1px solid #f3d7e3;
            border-radius: 20px;
            padding: 25px;
            box-shadow: 0 7px 22px rgba(166, 62, 107, 0.06);
            overflow-x: auto;
        }

        .table-title {
            margin-bottom: 22px;
            color: #6c3c52;
            font-size: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 760px;
        }

        th {
            background: #fcecf3;
            color: #80566a;
            padding: 15px 12px;
            text-align: left;
            font-size: 12px;
        }

        td {
            padding: 15px 12px;
            border-bottom: 1px solid #f6e7ee;
            font-size: 13px;
            color: #765363;
            vertical-align: middle;
        }

        tbody tr:last-child td {
            border-bottom: none;
        }

        .product-image {
            width: 70px;
            height: 70px;
            object-fit: cover;
            border-radius: 12px;
            background: #fff4f8;
        }

        .product-name {
            color: #6c3c52;
            font-weight: 600;
        }

        /* Tombol Edit */
        .edit-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 9px 13px;
            background: #fcecf3;
            color: #a63e6b;
            border: 1px solid #f3d7e3;
            border-radius: 9px;
            text-decoration: none;
            font-family: inherit;
            font-size: 12px;
            font-weight: 600;
            transition: 0.2s ease;
        }

        .edit-btn:hover {
            background: #c6537d;
            color: #fff;
        }

        /* Tombol Hapus */
        .delete-btn {
            padding: 9px 13px;
            background: #fff0f0;
            color: #c0395b;
            border: 1px solid #f4caca;
            border-radius: 9px;
            font-family: inherit;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: 0.2s ease;
        }

        .delete-btn:hover {
            background: #c0395b;
            color: #fff;
        }

        /* Susunan tombol aksi */
        .action-buttons {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }

        .empty {
            text-align: center;
            padding: 40px;
            color: #946d80;
        }

        .footer {
            text-align: center;
            margin-top: 30px;
            color: #a18491;
            font-size: 12px;
        }

        @media (max-width: 600px) {
            .page {
                padding: 25px 15px;
            }

            .heading h1 {
                font-size: 24px;
            }

            .product-card {
                padding: 18px;
            }
        }
    </style>
</head>

<body>

<main class="page">

    <div class="topbar">

        <div class="heading">
            <h1>Kelola Produk 🌸</h1>
            <p>Kelola produk yang tersedia di katalog PinkFlow.</p>
        </div>

        <div class="actions">
            <a href="dashboard.php" class="btn btn-dashboard">
                ← Dashboard
            </a>

            <a href="tambah_produk.php" class="btn btn-add">
                + Tambah Produk
            </a>
        </div>

    </div>

    <section class="product-card">

        <h2 class="table-title">Daftar Produk PinkFlow</h2>

        <table>

            <thead>
                <tr>
                    <th>GAMBAR</th>
                    <th>NAMA PRODUK</th>
                    <th>KATEGORI</th>
                    <th>HARGA</th>
                    <th>STOK</th>
                    <th>AKSI</th>
                </tr>
            </thead>

            <tbody>

                <?php if ($produk && $produk->num_rows > 0): ?>

                    <?php while ($row = $produk->fetch_assoc()): ?>

                        <tr>

                            <td>
                                <img
                                    class="product-image"
                                    src="../assets/images/<?= htmlspecialchars($row["image"]); ?>"
                                    alt="<?= htmlspecialchars($row["name"]); ?>"
                                >
                            </td>

                            <td class="product-name">
                                <?= htmlspecialchars($row["name"]); ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($row["category"]); ?>
                            </td>

                            <td>
                                Rp<?= number_format((float)$row["price"], 0, ",", "."); ?>
                            </td>

                            <td>
                                <?= (int)$row["stock"]; ?>
                            </td>

                            <td>
                                <div class="action-buttons">

                                    <!-- Tombol Edit -->
                                    <a
                                        href="edit_produk.php?id=<?= (int)$row["id"]; ?>"
                                        class="edit-btn"
                                    >
                                        Edit
                                    </a>

                                    <!-- Tombol Hapus -->
                                    <form
                                        action="proses_hapus_produk.php"
                                        method="POST"
                                        onsubmit="return confirm('Yakin ingin menghapus produk ini? Produk dan gambar akan dihapus.');"
                                    >

                                        <input
                                            type="hidden"
                                            name="id"
                                            value="<?= (int)$row["id"]; ?>"
                                        >

                                        <input
                                            type="hidden"
                                            name="csrf_token"
                                            value="<?= htmlspecialchars($_SESSION["csrf_token"]); ?>"
                                        >

                                        <button type="submit" class="delete-btn">
                                            Hapus
                                        </button>

                                    </form>

                                </div>
                            </td>

                        </tr>

                    <?php endwhile; ?>

                <?php else: ?>

                    <tr>
                        <td colspan="6" class="empty">
                            Belum ada produk yang ditambahkan 🌷
                        </td>
                    </tr>

                <?php endif; ?>

            </tbody>

        </table>

    </section>

    <div class="footer">
        © 2026 PinkFlow Admin · Made with ♡
    </div>

</main>

</body>
</html>
