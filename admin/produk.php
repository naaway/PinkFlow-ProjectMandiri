<?php
require_once 'auth.php';

$message = '';
$error = '';

/**
 * Menyimpan foto produk ke assets/images.
 * Mengembalikan nama file baru jika berhasil, atau null jika tidak ada unggahan.
 */
function upload_product_image(?array $file): ?string
{
    if (!$file || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    $uploadError = (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE);

    if ($uploadError !== UPLOAD_ERR_OK) {
        $messages = [
            UPLOAD_ERR_INI_SIZE => 'Ukuran foto melebihi batas upload PHP.',
            UPLOAD_ERR_FORM_SIZE => 'Ukuran foto melebihi batas formulir.',
            UPLOAD_ERR_PARTIAL => 'Foto hanya terunggah sebagian. Coba lagi.',
            UPLOAD_ERR_NO_TMP_DIR => 'Folder sementara upload tidak tersedia.',
            UPLOAD_ERR_CANT_WRITE => 'Server gagal menulis file foto.',
            UPLOAD_ERR_EXTENSION => 'Upload foto dihentikan oleh konfigurasi PHP.',
        ];

        throw new RuntimeException($messages[$uploadError] ?? 'Upload foto gagal.');
    }

    if (!is_uploaded_file($file['tmp_name'])) {
        throw new RuntimeException('File unggahan tidak valid. Silakan pilih foto kembali.');
    }

    if ((int) $file['size'] > 4 * 1024 * 1024) {
        throw new RuntimeException('Ukuran foto maksimal 4 MB.');
    }

    $imageInfo = getimagesize($file['tmp_name']);

    if ($imageInfo === false) {
        throw new RuntimeException('File yang dipilih bukan gambar yang valid.');
    }

    $allowedMimeTypes = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];

    $mimeType = $imageInfo['mime'] ?? '';

    if (!isset($allowedMimeTypes[$mimeType])) {
        throw new RuntimeException('Format foto harus JPG, PNG, atau WEBP.');
    }

    $imageDirectory = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'images';

    if (!is_dir($imageDirectory) && !mkdir($imageDirectory, 0755, true) && !is_dir($imageDirectory)) {
        throw new RuntimeException('Folder penyimpanan foto tidak dapat dibuat.');
    }

    if (!is_writable($imageDirectory)) {
        throw new RuntimeException('Folder assets/images tidak memiliki izin tulis.');
    }

    $fileName = 'product-' . bin2hex(random_bytes(8)) . '.' . $allowedMimeTypes[$mimeType];
    $destination = $imageDirectory . DIRECTORY_SEPARATOR . $fileName;

    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        throw new RuntimeException('Foto gagal dipindahkan ke folder assets/images.');
    }

    return $fileName;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();

    $id = (int) ($_POST['id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $price = (float) ($_POST['price'] ?? 0);
    $stock = (int) ($_POST['stock'] ?? 0);
    $image = trim($_POST['current_image'] ?? '');
    $description = trim($_POST['description'] ?? '');

    try {
        $newImage = upload_product_image($_FILES['image_file'] ?? null);

        if ($newImage !== null) {
            $image = $newImage;
        }

        if (isset($_POST['delete'])) {
            $statement = $conn->prepare('UPDATE products SET is_active = 0 WHERE id = ?');
            $statement->bind_param('i', $id);
            $statement->execute();
        } elseif ($id > 0) {
            $statement = $conn->prepare(
                'UPDATE products
                 SET name = ?, category = ?, price = ?, stock = ?, image = ?, description = ?
                 WHERE id = ?'
            );
            $statement->bind_param('ssdissi', $name, $category, $price, $stock, $image, $description, $id);
            $statement->execute();
        } else {
            $statement = $conn->prepare(
                'INSERT INTO products (name, category, price, stock, image, description)
                 VALUES (?, ?, ?, ?, ?, ?)'
            );
            $statement->bind_param('ssdiss', $name, $category, $price, $stock, $image, $description);
            $statement->execute();
        }

        header('Location: produk.php?saved=1');
        exit;
    } catch (Throwable $exception) {
        $error = $exception->getMessage();
    }
}

if (isset($_GET['saved'])) {
    $message = 'Produk berhasil disimpan.';
}

$edit = null;

if (isset($_GET['edit'])) {
    $id = (int) $_GET['edit'];
    $statement = $conn->prepare('SELECT * FROM products WHERE id = ?');
    $statement->bind_param('i', $id);
    $statement->execute();
    $edit = $statement->get_result()->fetch_assoc();
}

$rows = $conn->query(
    'SELECT * FROM products WHERE is_active = 1 ORDER BY id DESC'
)->fetch_all(MYSQLI_ASSOC);

include 'layout-top.php';
?>

<div class="admin-head">
    <div>
        <span class="eyebrow">CATALOG MANAGEMENT</span>
        <h1>Produk</h1>
        <p>Tambah, ubah, dan atur stok produk.</p>
    </div>
</div>

<?php if ($message !== ''): ?>
    <p class="admin-notice" role="status"><?= e($message) ?></p>
<?php endif; ?>

<?php if ($error !== ''): ?>
    <p class="admin-error" role="alert"><?= e($error) ?></p>
<?php endif; ?>

<section class="admin-panel">
    <h2><?= $edit ? 'Edit produk' : 'Tambah produk' ?></h2>

    <form method="post" enctype="multipart/form-data" class="admin-form">
        <input type="hidden" name="csrf" value="<?= e(csrf()) ?>">
        <input type="hidden" name="id" value="<?= e($edit['id'] ?? 0) ?>">

        <label>
            Nama produk
            <input name="name" required value="<?= e($edit['name'] ?? '') ?>">
        </label>

        <label>
            Kategori
            <input
                name="category"
                required
                value="<?= e($edit['category'] ?? '') ?>"
                placeholder="Buket bunga"
            >
        </label>

        <div class="form-two">
            <label>
                Harga
                <input
                    type="number"
                    name="price"
                    min="0"
                    required
                    value="<?= e($edit['price'] ?? '') ?>"
                >
            </label>

            <label>
                Stok
                <input
                    type="number"
                    name="stock"
                    min="0"
                    required
                    value="<?= e($edit['stock'] ?? '') ?>"
                >
            </label>
        </div>

        <input type="hidden" name="current_image" value="<?= e($edit['image'] ?? '') ?>">

        <label>
            Unggah foto produk
            <input type="file" name="image_file" accept="image/jpeg,image/png,image/webp">
            <small>JPG, PNG, WEBP · Maksimal 4 MB. Kosongkan jika tidak ingin mengganti foto.</small>
        </label>

        <label>
            Deskripsi
            <textarea name="description" rows="3"><?= e($edit['description'] ?? '') ?></textarea>
        </label>

        <button class="admin-btn" type="submit">Simpan produk</button>

        <?php if ($edit): ?>
            <a class="backlink" href="produk.php">Batal edit</a>
        <?php endif; ?>
    </form>
</section>

<section class="admin-panel">
    <h2>Daftar produk</h2>

    <div class="table-scroll">
        <table>
            <tr>
                <th>Produk</th>
                <th>Kategori</th>
                <th>Harga</th>
                <th>Stok</th>
                <th>Aksi</th>
            </tr>

            <?php foreach ($rows as $product): ?>
                <tr>
                    <td><?= e($product['name']) ?></td>
                    <td><?= e($product['category']) ?></td>
                    <td><?= rp($product['price']) ?></td>
                    <td><?= (int) $product['stock'] ?></td>
                    <td><a href="?edit=<?= (int) $product['id'] ?>">Edit</a></td>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>
</section>

<?php include 'layout-bottom.php'; ?>
