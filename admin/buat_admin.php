
<?php
require_once "../config/database.php";

// Ganti dengan username dan password admin kamu
$username = "admin";
$password = "12345";

// Buat tabel admin jika belum tersedia
$conn->query("
    CREATE TABLE IF NOT EXISTS admins (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )
");

// Amankan password menggunakan hash
$passwordHash = password_hash($password, PASSWORD_DEFAULT);

// Simpan akun admin
$stmt = $conn->prepare(
    "INSERT INTO admins (username, password)
     VALUES (?, ?)"
);

$stmt->bind_param(
    "ss",
    $username,
    $passwordHash
);

if ($stmt->execute()) {
    echo "Akun admin berhasil dibuat!";
} else {
    echo "Akun gagal dibuat. Username mungkin sudah digunakan.";
}

$stmt->close();
$conn->close();
?>
