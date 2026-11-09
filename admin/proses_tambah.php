<?php

require_once "../config/database.php";

// Pastikan form dikirim menggunakan POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: tambah_produk.php");
    exit;
}

// Ambil data dari form
$name = trim($_POST["name"] ?? "");
$category = trim($_POST["category"] ?? "");
$price = (float) ($_POST["price"] ?? 0);
$stock = (int) ($_POST["stock"] ?? 0);
$description = trim($_POST["description"] ?? "");

// Validasi data
if (
    $name === "" ||
    $category === "" ||
    $description === "" ||
    $price <= 0 ||
    $stock < 0
) {
    exit("Data produk tidak valid.");
}

// Validasi gambar
if (
    !isset($_FILES["image"]) ||
    $_FILES["image"]["error"] !== UPLOAD_ERR_OK
) {
    exit("Gambar produk wajib diunggah.");
}

$file = $_FILES["image"];

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

// Batasi ukuran gambar maksimal 2 MB
if ($file["size"] > 2 * 1024 * 1024) {
    exit("Ukuran gambar maksimal 2 MB.");
}

// Tentukan ekstensi gambar
$extensions = [
    "image/jpeg" => "jpg",
    "image/png" => "png",
    "image/webp" => "webp"
];

$extension = $extensions[$mimeType];

// Nama file unik
$imageName = uniqid("pinkflow_", true) . "." . $extension;

// Folder penyimpanan gambar
$uploadDir = "../assets/images/";
$uploadPath = $uploadDir . $imageName;

// Pastikan folder tersedia
if (!is_dir($uploadDir)) {
    exit("Folder gambar tidak ditemukan.");
}

// Upload gambar
if (!move_uploaded_file($file["tmp_name"], $uploadPath)) {
    exit("Gagal mengunggah gambar.");
}

// Simpan data ke database
$stmt = $conn->prepare(
    "INSERT INTO products
    (name, category, price, stock, image, description)
    VALUES (?, ?, ?, ?, ?, ?)"
);

if (!$stmt) {
    unlink($uploadPath);
    exit("Gagal menyiapkan query.");
}

$stmt->bind_param(
    "ssdiss",
    $name,
    $category,
    $price,
    $stock,
    $imageName,
    $description
);

if ($stmt->execute()) {

    $stmt->close();

    // Kembali ke katalog setelah berhasil
    header("Location: ../pages/katalog.php?status=success");
    exit;

} else {

    unlink($uploadPath);
    $stmt->close();

    exit("Gagal menyimpan produk ke database.");
}
