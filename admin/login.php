<?php
require_once '../includes/functions.php';
require_once '../config/database.php';

if (!empty($_SESSION['admin_id'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $statement = $conn->prepare(
        'SELECT id, username, password_hash
         FROM admins
         WHERE username = ?'
    );
    $statement->bind_param('s', $username);
    $statement->execute();
    $admin = $statement->get_result()->fetch_assoc();

    if ($admin && password_verify($password, $admin['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_name'] = $admin['username'];

        header('Location: dashboard.php');
        exit;
    }

    $error = 'Username atau password tidak sesuai.';
}
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login Admin | PinkFlow</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body class="login-body">
    <main class="login-shell">
        <section class="login-visual">
            <div class="login-visual-copy">
                <img
                    class="login-visual-logo"
                    src="../assets/images/Logo Admin.jpg"
                    alt="Logo PinkFlow Bouquet & Skincare"
                >
                <span class="eyebrow">PINKFLOW · ADMIN PORTAL</span>
                <h1>Every lovely moment<br><em>starts with care.</em></h1>
                <p>Kelola koleksi, pesanan, dan cerita indah dari pelanggan PinkFlow.</p>
            </div>

            <span class="login-visual-foot">BLOOM WITH LOVE · MADE FOR EVERY MOMENT</span>
        </section>

        <section class="login-form-side">
            <div class="login-form-wrap">
                <span class="eyebrow">WELCOME BACK</span>
                <h2>Masuk ke<br><em>ruang admin.</em></h2>
                <p class="login-intro">Senang melihatmu kembali. Silakan masuk untuk melanjutkan pengelolaan toko.</p>

                <?php if ($error !== ''): ?>
                    <div class="alert" role="alert"><?= e($error) ?></div>
                <?php endif; ?>

                <form method="post" class="login-form">
                    <input type="hidden" name="csrf" value="<?= e(csrf()) ?>">

                    <label>
                        Username
                        <input
                            type="text"
                            name="username"
                            placeholder="Masukkan username"
                            autocomplete="username"
                            required
                            autofocus
                        >
                    </label>

                    <label>
                        Password
                        <input
                            type="password"
                            name="password"
                            placeholder="Masukkan password"
                            autocomplete="current-password"
                            required
                        >
                    </label>

                    <button class="admin-btn login-submit" type="submit">
                        Masuk ke Dashboard <span aria-hidden="true">↗</span>
                    </button>
                </form>

                <a class="backlink" href="../index.php">← Kembali ke website PinkFlow</a>
                <p class="login-security">Akses ini khusus untuk pengelola PinkFlow.</p>
            </div>
        </section>
    </main>
</body>
</html>
