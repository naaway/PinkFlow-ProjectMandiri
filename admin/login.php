
<?php
session_start();

require_once "../config/database.php";

$error = "";

if (isset($_SESSION["admin_id"])) {
    header("Location: dashboard.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $username = trim($_POST["username"] ?? "");
    $password = $_POST["password"] ?? "";

    if ($username === "" || $password === "") {

        $error = "Username dan password wajib diisi.";

    } else {

        $stmt = $conn->prepare(
            "SELECT id, username, password
             FROM admins
             WHERE username = ?
             LIMIT 1"
        );

        $stmt->bind_param("s", $username);
        $stmt->execute();

        $result = $stmt->get_result();
        $admin = $result->fetch_assoc();

        $stmt->close();

        if ($admin && password_verify($password, $admin["password"])) {

            session_regenerate_id(true);

            $_SESSION["admin_id"] = $admin["id"];
            $_SESSION["admin_username"] = $admin["username"];

            header("Location: dashboard.php");
            exit;

        } else {

            $error = "Username atau password salah.";

        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Login Admin | PinkFlow</title>

    <link
        rel="stylesheet"
        href="../assets/css/style.css"
    >

    <style>
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(
                135deg,
                #fff5f7,
                #fce7ed
            );
        }

        .admin-login {
            width: 100%;
            max-width: 420px;
            padding: 40px;
            background: #ffffff;
            border: 1px solid #f4ccd8;
            border-radius: 20px;
            box-shadow: 0 12px 35px rgba(196, 93, 128, 0.12);
        }

        .admin-brand {
            margin-bottom: 30px;
            text-align: center;
        }

        .admin-brand h1 {
            margin-bottom: 8px;
            font-family: Georgia, serif;
            font-size: 2rem;
            color: #c45d80;
        }

        .admin-brand p {
            color: #806d76;
            font-size: 0.9rem;
        }

        .admin-login h2 {
            margin-bottom: 24px;
            text-align: center;
            font-size: 1.4rem;
        }

        .admin-login .form-group {
            margin-bottom: 18px;
        }

        .admin-login label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
        }

        .admin-login input {
            width: 100%;
            box-sizing: border-box;
        }

        .admin-login button {
            width: 100%;
            margin-top: 10px;
        }

        .login-error {
            margin-bottom: 20px;
            padding: 12px 15px;
            color: #a52d50;
            background: #fff0f3;
            border: 1px solid #f4ccd8;
            border-radius: 10px;
            font-size: 0.9rem;
        }

        .back-home {
            display: block;
            margin-top: 22px;
            text-align: center;
            color: #806d76;
            text-decoration: none;
            font-size: 0.9rem;
        }

        .back-home:hover {
            color: #c45d80;
        }
    </style>
</head>

<body>

    <main class="admin-login">

        <div class="admin-brand">
            <h1>PinkFlow.</h1>
            <p>Flower & Gifts</p>
        </div>

        <h2>Login Admin</h2>

        <?php if ($error !== ""): ?>

            <div class="login-error">
                <?= htmlspecialchars($error) ?>
            </div>

        <?php endif; ?>

        <form method="POST" action="">

            <div class="form-group">
                <label for="username">
                    Username
                </label>

                <input
                    type="text"
                    id="username"
                    name="username"
                    placeholder="Masukkan username"
                    autocomplete="username"
                    required
                >
            </div>

            <div class="form-group">
                <label for="password">
                    Password
                </label>

                <input
                    type="password"
                    id="password"
                    name="password"
                    placeholder="Masukkan password"
                    autocomplete="current-password"
                    required
                >
            </div>

            <button
                type="submit"
                class="btn btn-primary"
            >
                Masuk
            </button>

        </form>

        <a
            href="../index.php"
            class="back-home"
        >
            ← Kembali ke Beranda
        </a>

    </main>

</body>
</html>
