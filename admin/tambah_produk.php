<?php
session_start();

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Tambah Produk | PinkFlow</title>

    <link rel="stylesheet" href="../assets/css/style.css">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap"
        rel="stylesheet"
    >

    <style>

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: 'Poppins', sans-serif;
            background: #fff5f7;
            color: #573746;
        }

        /* HALAMAN TAMBAH PRODUK */

        .form-page {
            min-height: 100vh;
            padding: 35px 20px 50px;
            background: #fff5f7;
        }

        .form-container {
            max-width: 650px;
            margin: auto;
        }

        /* TOMBOL KEMBALI */

        .back-dashboard {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 20px;
            padding: 10px 16px;

            background: #fff;
            color: #a63e6b;

            text-decoration: none;
            font-size: 13px;
            font-weight: 600;

            border: 1px solid #f3d7e3;
            border-radius: 12px;

            transition: 0.25s ease;
        }

        .back-dashboard:hover {
            background: #fbe2ec;
            transform: translateX(-3px);
        }

        /* FORM CARD */

        .form-card {
            width: 100%;
            padding: 35px;
            background: #fff;

            border: 1px solid #f3d7e3;
            border-radius: 22px;

            box-shadow: 0 8px 30px rgba(166, 62, 107, 0.06);
        }

        .form-heading {
            text-align: center;
            margin-bottom: 30px;
        }

        .form-heading h1 {
            font-family: Georgia, serif;
            font-size: 30px;
            color: #6c3c52;
            margin: 0 0 10px;
        }

        .form-heading p {
            font-size: 13px;
            color: #946d80;
            line-height: 1.7;
            margin: 0;
        }

        /* FORM INPUT */

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 9px;

            font-size: 14px;
            font-weight: 600;
            color: #6c3c52;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            display: block;
            width: 100%;

            padding: 13px 15px;

            background: #fff;
            color: #573746;

            border: 1px solid #ead5dc;
            border-radius: 11px;

            font-family: 'Poppins', sans-serif;
            font-size: 13px;

            outline: none;
            transition: 0.2s ease;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            border-color: #c6537d;
            box-shadow: 0 0 0 3px rgba(198, 83, 125, 0.10);
        }

        .form-group textarea {
            min-height: 120px;
            resize: vertical;
        }

        .form-group input[type="file"] {
            padding: 11px;
            background: #fffafd;
        }

        /* TOMBOL SIMPAN */

        .form-submit {
            display: block;
            width: 100%;

            padding: 14px 20px;

            background: #c6537d;
            color: #fff;

            border: none;
            border-radius: 12px;

            font-family: 'Poppins', sans-serif;
            font-size: 14px;
            font-weight: 600;

            cursor: pointer;
            transition: 0.25s ease;
        }

        .form-submit:hover {
            background: #a63e6b;
            transform: translateY(-2px);

            box-shadow: 0 6px 18px rgba(166, 62, 107, 0.15);
        }

        /* RESPONSIVE */

        @media (max-width: 550px) {

            .form-page {
                padding: 25px 15px;
            }

            .form-card {
                padding: 25px 20px;
                border-radius: 18px;
            }

            .form-heading h1 {
                font-size: 25px;
            }

        }

    </style>

</head>

<body>

<main class="form-page">

    <div class="form-container">

        <!-- KEMBALI KE DASHBOARD -->

        <a href="dashboard.php" class="back-dashboard">
            ← Kembali ke Dashboard
        </a>

        <!-- FORM TAMBAH PRODUK -->

        <section class="form-card">

            <div class="form-heading">

                <h1>Tambah Produk PinkFlow 🌸</h1>

                <p>
                    Tambahkan produk baru agar tampil di katalog PinkFlow.
                </p>

            </div>

            <form
                action="proses_tambah.php"
                method="POST"
                enctype="multipart/form-data"
            >

                <div class="form-group">

                    <label for="name">
                        Nama Produk
                    </label>

                    <input
                        type="text"
                        id="name"
                        name="name"
                        placeholder="Contoh: Buket Thumbelina"
                        required
                    >

                </div>

                <div class="form-group">

                    <label for="category">
                        Kategori
                    </label>

                    <select
                        id="category"
                        name="category"
                        required
                    >

                        <option value="">
                            Pilih Kategori
                        </option>

                        <option value="Buket Bunga">
                            Buket Bunga
                        </option>

                        <option value="Buket Snack">
                            Buket Snack
                        </option>

                        <option value="Buket Uang">
                            Buket Uang
                        </option>

                        <option value="Buket Gift">
                            Buket Gift
                        </option>

                        <option value="Aksesoris">
                            Aksesoris
                        </option>

                    </select>

                </div>

                <div class="form-group">

                    <label for="price">
                        Harga (Rp)
                    </label>

                    <input
                        type="number"
                        id="price"
                        name="price"
                        min="1"
                        placeholder="Masukkan harga produk"
                        required
                    >

                </div>

                <div class="form-group">

                    <label for="stock">
                        Stok
                    </label>

                    <input
                        type="number"
                        id="stock"
                        name="stock"
                        min="0"
                        placeholder="Masukkan jumlah stok"
                        required
                    >

                </div>

                <div class="form-group">

                    <label for="description">
                        Deskripsi Produk
                    </label>

                    <textarea
                        id="description"
                        name="description"
                        placeholder="Jelaskan detail produk..."
                        required
                    ></textarea>

                </div>

                <div class="form-group">

                    <label for="image">
                        Gambar Produk
                    </label>

                    <input
                        type="file"
                        id="image"
                        name="image"
                        accept="image/jpeg,image/png,image/webp"
                        required
                    >

                </div>

                <button
                    type="submit"
                    class="form-submit"
                >
                    Simpan Produk
                </button>

            </form>

        </section>

    </div>

</main>

</body>

</html>
