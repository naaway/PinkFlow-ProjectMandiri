<?php
session_start();

// Menghapus seluruh data session admin
$_SESSION = [];

// Mengakhiri session
session_destroy();

// Kembali ke halaman login
header("Location: login.php");
exit;
?>
