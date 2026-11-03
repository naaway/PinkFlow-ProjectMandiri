
<?php
session_start();

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

require_once "../config/database.php";

// Pastikan form dikirim menggunakan POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: produk.php");
    exit;
}

// Validasi token keamanan
$csrfToken = $_POST["csrf_token"] ?? "";

if (
    empty($_SESSION["csrf_token"]) ||
    !hash_equals($_SESSION["csrf_token"], $csrfToken)
) {
    exit("Permintaan tidak valid. Silakan kembali dan coba lagi.");
}

// Ambil data dari form
$id = filter_input(INPUT_POST, "id", FILTER_VALIDATE_INT);

$name = trim($_POST["name"] ?? "");
$category = trim($_POST["category"] ?? "");
$price = (float)($_POST["price"] ?? 0);
$stock = (int)($_POST["stock"] ?? 0);
$description = trim($_POST["description"] ?? "");

// Validasi data
if (
    !$id ||
    $id <= 0 ||
    $name === "" ||
    $category === "" ||
    $description === "" ||
    $price <= 0 ||
    $stock < 0
) {
    exit("Data produk tidak valid.");
}

// Ambil gambar lama
$stmt = $conn->prepare(
    "SELECT image FROM products WHERE id = ?"
);

if (!$stmt) {
    exit("Gagal menyiapkan query.");
}

$stmt->bind_param("i", $id);
$stmt->execute();

$result = $stmt->get_result();
$produkLama = $result->fetch_assoc();

$stmt->close();

if (!$produkLama) {
    exit("Produk tidak ditemukan.");
}

$imageName = $produkLama["image"];
$uploadPath = null;

// Periksa apakah admin mengunggah gambar baru
if (
    isset($_FILES["image"]) &&
    $_FILES["image"]["error"] !== UPLOAD_ERR_NO_FILE
) {

    if ($_FILES["image"]["error"] !== UPLOAD_ERR_OK) {
        exit("Gagal mengunggah gambar.");
    }

    $file = $_FILES["image"];

    // Validasi tipe gambar
    $allowedTypes = [
        "image/jpeg",
        "image/png",
        "image/webp"
    ];

    $fileInfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($fileInfo, $file["tmp_name"]);
    finfo_close($fileInfo);

    if (!in_array($mimeType, $allowedTypes, true)) {
        exit("Format gambar harus JPG, PNG, atau WEBP.");
    }

    // Batasi ukuran maksimal 2 MB
    if ($file["size"] > 2 * 1024 * 1024) {
        exit("Ukuran gambar maksimal 2 MB.");
    }

    // Tentukan ekstensi file
    $extensions = [
        "image/jpeg" => "jpg",
        "image/png" => "png",
        "image/webp" => "webp"
    ];

    $extension = $extensions[$mimeType];

    // Buat nama file baru
    $newImageName = uniqid("pinkflow_", true) . "." . $extension;

    $uploadDir = "../assets/images/";
    $uploadPath = $uploadDir . $newImageName;

    if (!is_dir($uploadDir)) {
        exit("Folder gambar tidak ditemukan.");
    }

    // Upload gambar baru
    if (!move_uploaded_file($file["tmp_name"], $uploadPath)) {
        exit("Gagal menyimpan gambar baru.");
    }

    $imageName = $newImageName;
}

// Update data produk
$stmt = $conn->prepare(
    "UPDATE products
     SET name = ?,
         category = ?,
         price = ?,
         stock = ?,
         image = ?,
         description = ?
     WHERE id = ?"
);

if (!$stmt) {

    // Hapus gambar baru jika query gagal disiapkan
    if ($uploadPath !== null && is_file($uploadPath)) {
        unlink($uploadPath);
    }

    exit("Gagal menyiapkan query update.");
}

$stmt->bind_param(
    "ssdissi",
    $name,
    $category,
    $price,
    $stock,
    $imageName,
    $description,
    $id
);

if ($stmt->execute()) {

    $stmt->close();

    // Jika gambar diganti, hapus gambar lama
    if ($uploadPath !== null) {

        $oldImageName = basename($produkLama["image"]);
        $oldImagePath = "../assets/images/" . $oldImageName;

        if (
            $oldImageName !== "" &&
            is_file($oldImagePath)
        ) {
            unlink($oldImagePath);
        }
    }

    header("Location: produk.php?status=updated");
    exit;

} else {

    $stmt->close();

    // Hapus gambar baru jika update gagal
    if ($uploadPath !== null && is_file($uploadPath)) {
        unlink($uploadPath);
    }

    exit("Gagal memperbarui produk.");
}
