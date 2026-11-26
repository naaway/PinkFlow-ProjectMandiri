<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

$base = '../';
$err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();

    $n = trim($_POST['name'] ?? '');
    $ph = trim($_POST['phone'] ?? '');
    $ad = trim($_POST['address'] ?? '');
    $kind = trim($_POST['kind'] ?? '');
    $theme = trim($_POST['theme'] ?? '');
    $budget = trim($_POST['budget'] ?? '');
    $note = trim($_POST['note'] ?? '');

    if (!$n || !$ph || !$kind) {
        $err = 'Nama, telepon, dan jenis buket wajib diisi.';
    } else {
        $type = 'custom';
        $total = 0;
        $status = 'Menunggu Konfirmasi';
        $details = "Jenis: $kind | Tema: $theme | Anggaran: $budget | Catatan: $note";

        $s = $conn->prepare(
            "INSERT INTO orders
            (order_type, customer_name, phone, address, total, status, custom_details)
            VALUES (?, ?, ?, ?, ?, ?, ?)"
        );
        $s->bind_param('ssssdss', $type, $n, $ph, $ad, $total, $status, $details);
        $s->execute();

        $id = $conn->insert_id;
        $code = 'PF-C' . str_pad($id, 5, '0', STR_PAD_LEFT);

        $u = $conn->prepare('UPDATE orders SET order_code = ? WHERE id = ?');
        $u->bind_param('si', $code, $id);
        $u->execute();

        $message = "Halo PinkFlow, saya ingin memesan buket custom.
"
            . "Kode: $code
"
            . "Nama: $n
"
            . "Telepon: $ph
"
            . "Alamat: $ad
"
            . "Jenis: $kind
"
            . "Tema: $theme
"
            . "Anggaran: $budget
"
            . "Catatan: $note";

        header('Location: ' . wa($message));
        exit;
    }
}

$pageCss = 'custom.css';
$pageTitle = 'Buket Custom | PinkFlow';
include '../includes/header.php';
?>

<main class="custom-page">
    <section class="custom-hero" data-reveal>
        <span class="eyebrow">MADE JUST FOR YOU</span>

        <h1>
            Rangkai hadiah yang<br>
            <em>punya cerita.</em>
        </h1>

        <p>
            Ceritakan buket impianmu. Kami bantu memilih detailnya
            dan merangkainya dengan sepenuh hati.
        </p>

        <span class="custom-hero-flower" aria-hidden="true">✿</span>
    </section>

    <section class="custom-section">
        <div class="custom-layout">
            <aside class="custom-aside" data-reveal>
                <span class="custom-step">PINKFLOW · CUSTOM ORDER</span>

                <h2>
                    Buket yang<br>
                    lebih personal.
                </h2>

                <p>
                    Setiap detail bisa kamu sesuaikan. Isi formulir ini
                    agar tim PinkFlow mendapat gambaran buket yang kamu inginkan.
                </p>

                <div class="custom-note">
                    <span class="custom-note-icon">🌸</span>

                    <div>
                        <strong>Belum yakin dengan desainnya?</strong>
                        <br>
                        <span>
                            Tidak apa-apa. Tulis ide atau referensimu
                            di catatan tambahan.
                        </span>
                    </div>
                </div>

                <div class="custom-aside-caption">
                    THOUGHTFULLY MADE, JUST FOR YOU
                </div>
            </aside>

            <div class="form-card" data-reveal>
                <div class="form-heading">
                    <div>
                        <span class="form-kicker">LET'S CREATE SOMETHING LOVELY</span>
                        <h2>Rancang buketmu</h2>
                    </div>

                    <span class="form-flower" aria-hidden="true">✿</span>
                </div>

                <p class="form-intro">
                    Lengkapi informasi berikut. Kolom bertanda
                    <b>*</b> wajib diisi.
                </p>

                <?php if ($err): ?>
                    <p class="error" role="alert">
                        <?= e($err) ?>
                    </p>
                <?php endif; ?>

                <form method="post" class="custom-form">
                    <input type="hidden" name="csrf" value="<?= e(csrf()) ?>">

                    <div class="form-grid">
                        <label class="field">
                            <span>Nama lengkap <b>*</b></span>
                            <input
                                name="name"
                                placeholder="Nama kamu"
                                autocomplete="name"
                                required
                            >
                        </label>

                        <label class="field">
                            <span>Nomor telepon <b>*</b></span>
                            <input
                                name="phone"
                                type="tel"
                                placeholder="08xxxxxxxxxx"
                                autocomplete="tel"
                                required
                            >
                        </label>

                        <label class="field field-full">
                            <span>Alamat / lokasi pengambilan</span>
                            <textarea
                                name="address"
                                rows="2"
                                placeholder="Tuliskan alamat atau pilihan lokasi pengambilan"
                            ></textarea>
                        </label>

                        <label class="field">
                            <span>Jenis buket <b>*</b></span>
                            <select name="kind" required>
                                <option value="">Pilih jenis buket</option>
                                <option>Buket bunga</option>
                                <option>Buket uang</option>
                                <option>Buket snack</option>
                                <option>Buket hadiah</option>
                                <option>Lainnya</option>
                            </select>
                        </label>

                        <label class="field">
                            <span>Anggaran</span>
                            <input name="budget" placeholder="Contoh: Rp150.000">
                        </label>

                        <label class="field field-full">
                            <span>Tema warna</span>
                            <input
                                name="theme"
                                placeholder="Contoh: dusty pink, cream, sage green"
                            >
                        </label>

                        <label class="field field-full">
                            <span>Catatan tambahan</span>
                            <textarea
                                name="note"
                                rows="3"
                                placeholder="Ukuran, jenis bunga, ucapan pada kartu, referensi, atau detail lainnya..."
                            ></textarea>
                        </label>
                    </div>

                    <div class="form-submit-row">
                        <p>
                            Dengan mengirim formulir, kamu akan diarahkan
                            ke WhatsApp PinkFlow untuk konfirmasi pesanan.
                        </p>

                        <button class="btn dark custom-submit" type="submit">
                            Kirim detail pesanan <span>↗</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </section>
</main>

<?php include '../includes/footer.php'; ?>
