<?php
session_start();

require_once "../config/database.php";

$nomorWhatsApp = "6285242771020";

$jenisBuket = [
    "Buket Bunga",
    "Buket Snack",
    "Buket Uang",
    "Buket Thumbelina",
    "Buket Custom (Kirim Gambar Referensi di WhatsApp)"
];

$temaWarna = [
    "Pink Soft",
    "Pink",
    "Pastel",
    "White Elegant",
    "Blue",
    "Blue Soft",
    "Green",
    "Custom"
];

$error = "";
$linkWhatsApp = "";
$nomorPesanan = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $nama = trim($_POST["nama"] ?? "");
    $telepon = trim($_POST["telepon"] ?? "");
    $alamat = trim($_POST["alamat"] ?? "");

    $jenis = trim($_POST["jenis"] ?? "");
    $tema = trim($_POST["tema"] ?? "");
    $budget = trim($_POST["budget"] ?? "");
    $catatan = trim($_POST["catatan"] ?? "");

    // Validasi data pelanggan
    if (
        $nama === "" ||
        $telepon === "" ||
        $alamat === "" ||
        $jenis === "" ||
        $tema === "" ||
        $budget === ""
    ) {
        $error = "Mohon lengkapi semua data yang wajib diisi.";

    } elseif (
        !in_array($jenis, $jenisBuket, true) ||
        !in_array($tema, $temaWarna, true)
    ) {
        $error = "Pilihan jenis buket atau tema warna tidak valid.";

    } else {

        try {

            // Simpan pesanan custom ke database
            $stmt = $conn->prepare(
                "INSERT INTO orders (
                    order_type,
                    customer_name,
                    phone,
                    address,
                    custom_type,
                    custom_theme,
                    custom_budget,
                    notes,
                    total
                ) VALUES (
                    'custom',
                    ?, ?, ?, ?, ?, ?, ?, 0
                )"
            );

            $stmt->bind_param(
                "sssssss",
                $nama,
                $telepon,
                $alamat,
                $jenis,
                $tema,
                $budget,
                $catatan
            );

            $stmt->execute();

            $idPesanan = $conn->insert_id;

            $stmt->close();

            // Buat kode pesanan
            $nomorPesanan = "PF-C" . str_pad(
                $idPesanan,
                5,
                "0",
                STR_PAD_LEFT
            );

            // Simpan kode pesanan
            $stmtKode = $conn->prepare(
                "UPDATE orders
                 SET order_code = ?
                 WHERE id = ?"
            );

            $stmtKode->bind_param(
                "si",
                $nomorPesanan,
                $idPesanan
            );

            $stmtKode->execute();
            $stmtKode->close();

            // Siapkan pesan WhatsApp
            $pesanWhatsApp =
                "Halo PinkFlow, saya ingin memesan buket custom.\n\n" .
                "Kode Pesanan: " . $nomorPesanan . "\n" .
                "Nama: " . $nama . "\n" .
                "Nomor HP: " . $telepon . "\n" .
                "Alamat: " . $alamat . "\n" .
                "Jenis Buket: " . $jenis . "\n" .
                "Tema Warna: " . $tema . "\n" .
                "Budget: " . $budget . "\n" .
                "Catatan: " . $catatan;

            $linkWhatsApp =
                "https://wa.me/" . $nomorWhatsApp .
                "?text=" . urlencode($pesanWhatsApp);

        } catch (Exception $e) {

            $error = "Pesanan gagal disimpan. Silakan coba kembali.";

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

    <title>Pesan Custom | PinkFlow</title>

    <link
        rel="stylesheet"
        href="../assets/css/style.css"
    >
</head>

<body>

<header class="navbar">

    <div class="container navbar-inner">

        <a href="../index.php" class="brand">
            Pink<span>Flow</span>
            <small>Flower & Gifts</small>
        </a>

        <nav class="nav-links">
            <a href="../index.php">Beranda</a>
            <a href="../index.php#produk">Katalog</a>
            <a href="../index.php#tentang">Tentang Kami</a>
        </nav>

        <a
            href="../index.php#produk"
            class="btn btn-outline"
        >
            Kembali ke Katalog
        </a>

    </div>

</header>

<main>

    <section>

        <div class="container">

            <div class="section-heading">

                <p class="eyebrow">
                    MAKE IT PERSONAL
                </p>

                <h2>Pesan Buket Custom</h2>

                <p>
                    Wujudkan buket impianmu dengan pilihan warna,
                    jenis, dan budget sesuai keinginan.
                </p>

            </div>

            <div class="card">

                <div class="card-body">

                    <?php if ($error !== ""): ?>

                        <div class="error-message">
                            <?= htmlspecialchars($error) ?>
                        </div>

                    <?php endif; ?>

                    <form method="POST" action="">

                        <div class="form-group">

                            <label for="nama">
                                Nama Lengkap
                            </label>

                            <input
                                type="text"
                                name="nama"
                                id="nama"
                                required
                                value="<?= htmlspecialchars($_POST["nama"] ?? "") ?>"
                                placeholder="Masukkan nama lengkap"
                            >

                        </div>

                        <div class="form-group">

                            <label for="telepon">
                                Nomor HP / WhatsApp
                            </label>

                            <input
                                type="tel"
                                name="telepon"
                                id="telepon"
                                required
                                value="<?= htmlspecialchars($_POST["telepon"] ?? "") ?>"
                                placeholder="Contoh: 08xxxxxxxxxx"
                            >

                        </div>

                        <div class="form-group">

                            <label for="alamat">
                                Alamat
                            </label>

                            <textarea
                                name="alamat"
                                id="alamat"
                                required
                                placeholder="Masukkan alamat penerima"
                            ><?= htmlspecialchars($_POST["alamat"] ?? "") ?></textarea>

                        </div>

                        <div class="form-group">

                            <label for="jenis">
                                Jenis Buket
                            </label>

                            <select
                                name="jenis"
                                id="jenis"
                                required
                            >

                                <option value="">
                                    Pilih jenis buket
                                </option>

                                <?php foreach ($jenisBuket as $item): ?>

                                    <option
                                        value="<?= htmlspecialchars($item) ?>"
                                        <?= (($_POST["jenis"] ?? "") === $item) ? "selected" : "" ?>
                                    >
                                        <?= htmlspecialchars($item) ?>
                                    </option>

                                <?php endforeach; ?>

                            </select>

                        </div>

                        <div class="form-group">

                            <label for="tema">
                                Tema atau Warna
                            </label>

                            <select
                                name="tema"
                                id="tema"
                                required
                            >

                                <option value="">
                                    Pilih tema warna
                                </option>

                                <?php foreach ($temaWarna as $item): ?>

                                    <option
                                        value="<?= htmlspecialchars($item) ?>"
                                        <?= (($_POST["tema"] ?? "") === $item) ? "selected" : "" ?>
                                    >
                                        <?= htmlspecialchars($item) ?>
                                    </option>

                                <?php endforeach; ?>

                            </select>

                        </div>

                        <div class="form-group">

                            <label for="budget">
                                Budget
                            </label>

                            <select
                                name="budget"
                                id="budget"
                                required
                            >

                                <option value="">
                                    Pilih budget
                                </option>

                                <option value="Di bawah Rp50.000">
                                    Di bawah Rp50.000
                                </option>

                                <option value="Rp50.000 - Rp100.000">
                                    Rp50.000 - Rp100.000
                                </option>

                                <option value="Rp100.000 - Rp200.000">
                                    Rp100.000 - Rp200.000
                                </option>

                                <option value="Di atas Rp200.000">
                                    Di atas Rp200.000
                                </option>

                            </select>

                        </div>

                        <div class="form-group">

                            <label for="catatan">
                                Catatan Tambahan
                            </label>

                            <textarea
                                name="catatan"
                                id="catatan"
                                placeholder="Contoh: Untuk hadiah ulang tahun..."
                            ><?= htmlspecialchars($_POST["catatan"] ?? "") ?></textarea>

                        </div>

                        <button
                            type="submit"
                            class="btn btn-primary"
                        >
                            Simpan Pesanan Custom
                        </button>

                    </form>

                    <?php if ($linkWhatsApp !== ""): ?>

                        <div
                            style="
                                margin-top: 28px;
                                padding: 28px;
                                background: linear-gradient(
                                    135deg,
                                    #fff5f7 0%,
                                    #fce7ed 100%
                                );
                                border: 1px solid #f4ccd8;
                                border-radius: 16px;
                            "
                        >

                            <p
                                style="
                                    margin: 0 0 8px;
                                    color: #ad496c;
                                    font-size: 0.8rem;
                                    font-weight: 700;
                                    letter-spacing: 1.5px;
                                "
                            >
                                PESANAN TERSIMPAN
                            </p>

                            <h3
                                style="
                                    margin: 0 0 16px;
                                    color: #49343e;
                                    font-family: Georgia, serif;
                                    font-size: 1.6rem;
                                    font-weight: 500;
                                "
                            >
                                Pesanan berhasil disimpan!
                            </h3>

                            <p style="margin-bottom: 12px;">
                                Kode pesanan:
                                <strong>
                                    <?= htmlspecialchars($nomorPesanan) ?>
                                </strong>
                            </p>

                            <p
                                style="
                                    margin-bottom: 24px;
                                    color: #806d76;
                                    line-height: 1.8;
                                "
                            >
                                Pesanan kamu sudah tersimpan.
                                Silakan konfirmasi pesanan melalui WhatsApp PinkFlow
                                agar pesanan dapat segera diproses.
                            </p>

                            <a
                                href="<?= htmlspecialchars($linkWhatsApp) ?>"
                                target="_blank"
                                rel="noopener noreferrer"
                                style="
                                    display: inline-flex;
                                    align-items: center;
                                    justify-content: center;
                                    padding: 13px 24px;
                                    color: #ffffff;
                                    background: #c45d80;
                                    border: 1px solid #c45d80;
                                    border-radius: 8px;
                                    font-weight: 600;
                                    text-decoration: none;
                                    transition: 0.2s ease;
                                "
                            >
                                Konfirmasi Pesanan di WhatsApp
                            </a>

                        </div>

                    <?php endif; ?>

                </div>

            </div>

        </div>

    </section>

</main>

<footer class="footer">

    <div class="container footer-inner">

        <a href="../index.php" class="brand">
            Pink<span>Flow</span>
            <small>Flower & Gifts</small>
        </a>

        <p>Small gifts, meaningful moments. ♡</p>

        <p>© <?= date("Y") ?> PinkFlow</p>

    </div>

</footer>

</body>
</html>
