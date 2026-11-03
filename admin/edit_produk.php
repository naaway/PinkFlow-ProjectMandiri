
<?php
session_start();

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

require_once "../config/database.php";

// Pastikan ID produk tersedia
$id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

if (!$id || $id <= 0) {
    header("Location: produk.php");
    exit;
}

// Ambil data produk
$stmt = $conn->prepare(
    "SELECT id, name, category, price, stock, image, description
     FROM products
     WHERE id = ?"
);

$stmt->bind_param("i", $id);
$stmt->execute();

$result = $stmt->get_result();
$produk = $result->fetch_assoc();

$stmt->close();

if (!$produk) {
    exit("Produk tidak ditemukan.");
}

// Token keamanan
if (empty($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Edit Produk | PinkFlow</title>

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

        .page {
            min-height: 100vh;
            padding: 35px 20px 50px;
        }

        .container {
            max-width: 700px;
            margin: auto;
        }

        .back-btn {
            display: inline-block;
            margin-bottom: 20px;
            padding: 10px 16px;
            background: #fff;
            color: #a63e6b;
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
            border: 1px solid #f3d7e3;
            border-radius: 12px;
        }

        .form-card {
            padding: 35px;
            background: #fff;
            border: 1px solid #f3d7e3;
            border-radius: 22px;
            box-shadow: 0 8px 30px rgba(166, 62, 107, 0.06);
        }

        .heading {
            text-align: center;
            margin-bottom: 30px;
        }

        .heading h1 {
            font-family: Georgia, serif;
            font-size: 30px;
            color: #6c3c52;
            margin: 0 0 10px;
        }

        .heading p {
            font-size: 13px;
            color: #946d80;
            line-height: 1.7;
        }

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
            width: 100%;
            padding: 13px 15px;
            background: #fff;
            color: #573746;
            border: 1px solid #ead5dc;
            border-radius: 11px;
            font-family: 'Poppins', sans-serif;
            font-size: 13px;
            outline: none;
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

        .current-image {
            display: block;
            width: 150px;
            height: 150px;
            object-fit: cover;
            border-radius: 15px;
            margin-bottom: 15px;
            background: #fff5f7;
            border: 1px solid #f3d7e3;
        }

        .image-note {
            display: block;
            margin-top: 8px;
            color: #946d80;
            font-size: 12px;
            line-height: 1.7;
        }

        .submit-btn {
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

        .submit-btn:hover {
            background: #a63e6b;
            transform: translateY(-2px);
        }

        @media (max-width: 550px) {
            .page {
                padding: 25px 15px;
            }

            .form-card {
                padding: 25px 20px;
            }

            .heading h1 {
                font-size: 25px;
            }
        }
    </style>
</head>

<body>

<main class="page">

    <div class="container">

        <a href="produk.php" class="back-btn">
            ← Kembali ke Kelola Produk
        </a>

        <section class="form-card">

            <div class="heading">
                <h1>Edit Produk PinkFlow 🌸</h1>
                <p>
                    Perbarui informasi produk, harga, stok, atau gambar.
                </p>
            </div>

            <form
                action="proses_edit_produk.php"
                method="POST"
                enctype="multipart/form-data"
            >

                <input
                    type="hidden"
                    name="id"
                    value="<?= (int)$produk["id"]; ?>"
                >

                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?= htmlspecialchars($_SESSION["csrf_token"]); ?>"
                >

                <div class="form-group">
                    <label for="name">Nama Produk</label>

                    <input
                        type="text"
                        id="name"
                        name="name"
                        value="<?= htmlspecialchars($produk["name"]); ?>"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="category">Kategori</label>

                    <select id="category" name="category" required>

                        <?php
                        $categories = [
                            "Buket Bunga",
                            "Buket Snack",
                            "Buket Uang",
                            "Buket Gift",
                            "Aksesoris"
                        ];
                        ?>

                        <?php foreach ($categories as $category): ?>

                            <option
                                value="<?= htmlspecialchars($category); ?>"
                                <?= $produk["category"] === $category ? "selected" : ""; ?>
                            >
                                <?= htmlspecialchars($category); ?>
                            </option>

                        <?php endforeach; ?>

                    </select>
                </div>

                <div class="form-group">
                    <label for="price">Harga (Rp)</label>

                    <input
                        type="number"
                        id="price"
                        name="price"
                        min="1"
                        value="<?= (float)$produk["price"]; ?>"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="stock">Jumlah Stok</label>

                    <input
                        type="number"
                        id="stock"
                        name="stock"
                        min="0"
                        value="<?= (int)$produk["stock"]; ?>"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="description">Deskripsi Produk</label>

                    <textarea
                        id="description"
                        name="description"
                        required
                    ><?= htmlspecialchars($produk["description"]); ?></textarea>
                </div>

                <div class="form-group">
                    <label>Gambar Produk Saat Ini</label>

                    <img
                        src="../assets/images/<?= htmlspecialchars($produk["image"]); ?>"
                        alt="<?= htmlspecialchars($produk["name"]); ?>"
                        class="current-image"
                    >

                    <label for="image">Ganti Gambar Produk</label>

                    <input
                        type="file"
                        id="image"
                        name="image"
                        accept="image/jpeg,image/png,image/webp"
                    >

                    <span class="image-note">
                        Kosongkan jika tidak ingin mengganti gambar.
                        Format JPG, PNG, atau WEBP. Maksimal 2 MB.
                    </span>
                </div>

                <button type="submit" class="submit-btn">
                    Simpan Perubahan
                </button>

            </form>

        </section>

    </div>

</main>

</body>
</html>
