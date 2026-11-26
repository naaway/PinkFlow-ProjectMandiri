<?php
require_once '../includes/functions.php';
require_once '../config/database.php';

$count = (int) $conn->query('SELECT COUNT(*) AS total FROM admins')->fetch_assoc()['total'];
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $count === 0) {
    check_csrf();

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (strlen($username) < 4 || strlen($password) < 10) {
        $message = 'Username minimal 4 karakter dan password minimal 10 karakter.';
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $statement = $conn->prepare(
            'INSERT INTO admins (username, password_hash) VALUES (?, ?)'
        );
        $statement->bind_param('ss', $username, $hash);
        $statement->execute();

        $message = 'Admin berhasil dibuat. Silakan login.';
        $count = 1;
    }
}
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Buat Admin | PinkFlow</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body class="login-body">
    <main class="login-shell">
        <section class="login-visual">
            <a class="login-logo" href="../index.php">
                <img src="../assets/images/logo-pinkflow-cropped.png" alt="PinkFlow Bouquet & Skincare">
            </a>
            <div class="login-illustration" aria-hidden="true">
                <span class="illustration-orbit orbit-one"></span>
                <span class="illustration-orbit orbit-two"></span>
                <span class="illustration-flower">✿</span>
                <span class="illustration-leaf leaf-one"></span>
                <span class="illustration-leaf leaf-two"></span>
            </div>
            <div class="login-visual-copy">
                <span class="eyebrow">PINKFLOW · ADMIN PORTAL</span>
                <h1>A lovely space<br><em>to grow your store.</em></h1>
                <p>Buat akses pengelola pertama untuk mulai mengatur produk dan pesanan PinkFlow.</p>
            </div>
            <span class="login-visual-foot">BLOOM WITH LOVE · MADE FOR EVERY MOMENT</span>
        </section>

        <section class="login-form-side">
            <div class="login-form-wrap">
                <span class="login-mark">✿</span>
                <span class="eyebrow">FIRST-TIME SETUP</span>
                <h2>Buat akun<br><em>admin.</em></h2>

                <?php if ($message !== ''): ?>
                    <div class="alert" role="status"><?= e($message) ?></div>
                <?php endif; ?>

                <?php if ($count > 0): ?>
                    <p class="login-intro">Akun admin sudah tersedia. Silakan masuk untuk melanjutkan.</p>
                    <a class="admin-btn login-submit" href="login.php">Ke halaman login ↗</a>
                <?php else: ?>
                    <p class="login-intro">Buat username dan password untuk akun admin PinkFlow.</p>
                    <form method="post" class="login-form">
                        <input type="hidden" name="csrf" value="<?= e(csrf()) ?>">
                        <label>
                            Username
                            <input name="username" minlength="4" autocomplete="username" required placeholder="Minimal 4 karakter">
                        </label>
                        <label>
                            Password
                            <input type="password" name="password" minlength="10" autocomplete="new-password" required placeholder="Minimal 10 karakter">
                        </label>
                        <button class="admin-btn login-submit" type="submit">Buat akun admin ↗</button>
                    </form>
                <?php endif; ?>

                <a class="backlink" href="../index.php">← Kembali ke website PinkFlow</a>
            </div>
        </section>
    </main>
</body>
</html>
