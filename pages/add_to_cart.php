
<?php
session_start();

require_once "../config/database.php";

header("Content-Type: application/json");


// Pastikan permintaan menggunakan POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);

    echo json_encode([
        "success" => false,
        "message" => "Metode permintaan tidak diizinkan."
    ]);

    exit;
}


// Ambil ID produk
$productId = (int) ($_POST["product_id"] ?? 0);

if ($productId <= 0) {
    http_response_code(400);

    echo json_encode([
        "success" => false,
        "message" => "Produk tidak valid."
    ]);

    exit;
}


// Ambil informasi produk dan stok
$stmt = $conn->prepare(
    "SELECT id, stock
     FROM products
     WHERE id = ?
     LIMIT 1"
);

$stmt->bind_param("i", $productId);
$stmt->execute();

$result = $stmt->get_result();
$product = $result->fetch_assoc();

$stmt->close();

if (!$product) {
    http_response_code(404);

    echo json_encode([
        "success" => false,
        "message" => "Produk tidak ditemukan."
    ]);

    exit;
}


// Periksa stok
$stock = (int) $product["stock"];

if ($stock <= 0) {
    http_response_code(400);

    echo json_encode([
        "success" => false,
        "message" => "Maaf, stok produk sudah habis."
    ]);

    exit;
}


// Siapkan keranjang
if (!isset($_SESSION["cart"]) || !is_array($_SESSION["cart"])) {
    $_SESSION["cart"] = [];
}


// Jumlah produk yang sudah ada
$currentQuantity = (int) (
    $_SESSION["cart"][$productId] ?? 0
);


// Periksa batas stok
if ($currentQuantity >= $stock) {
    http_response_code(400);

    echo json_encode([
        "success" => false,
        "message" => "Jumlah produk sudah mencapai batas stok."
    ]);

    exit;
}


// Tambahkan produk ke keranjang
$_SESSION["cart"][$productId] = $currentQuantity + 1;


// Hitung total seluruh barang
$cartCount = 0;

foreach ($_SESSION["cart"] as $quantity) {
    $cartCount += (int) $quantity;
}


// Kirim hasil ke JavaScript
echo json_encode([
    "success" => true,
    "message" => "Produk berhasil ditambahkan ke keranjang.",
    "cart_count" => $cartCount
]);

exit;
