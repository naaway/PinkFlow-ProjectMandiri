
<?php
session_start();

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

require_once "../config/database.php";

// Hanya menerima request POST
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

// Validasi ID produk
$id = filter_input(INPUT_POST, "id", FILTER_VALIDATE_INT);

if (!$id || $id <= 0) {
    exit("ID produk tidak valid.");
}

// Ambil nama gambar produk
$stmt = $conn->prepare(
    "SELECT image FROM products WHERE id = ?"
);

if (!$stmt) {
    exit("Gagal menyiapkan query.");
}

$stmt->bind_param("i", $id);
$stmt->execute();

$result = $stmt->get_result();
$product = $result->fetch_assoc();

$stmt->close();

if (!$product) {
    exit("Produk tidak ditemukan.");
}

// Hapus produk dari database
$delete = $conn->prepare(
    "DELETE FROM products WHERE id = ?"
);

if (!$delete) {
    exit("Gagal menyiapkan query penghapusan.");
}

$delete->bind_param("i", $id);

if ($delete->execute()) {

    $delete->close();

    // Hapus file gambar jika tersedia
    $imageName = basename($product["image"]);
    $imagePath = "../assets/images/" . $imageName;

    if (
        $imageName !== "" &&
        is_file($imagePath)
    ) {
        unlink($imagePath);
    }

    header("Location: produk.php?status=deleted");
    exit;

} else {

    $delete->close();

    exit("Gagal menghapus produk.");
}
