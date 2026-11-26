<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

$base = '../';
$items = [];
$total = 0;

// Ambil detail produk yang tersimpan di keranjang.
if (!empty($_SESSION['cart'])) {
    $ids = array_map('intval', array_keys($_SESSION['cart']));
    $placeholders = implode(',', array_fill(0, count($ids), '?'));

    $statement = $conn->prepare(
        "SELECT id, name, price, stock, image
         FROM products
         WHERE id IN ($placeholders)"
    );

    $statement->bind_param(str_repeat('i', count($ids)), ...$ids);
    $statement->execute();

    $result = $statement->get_result();

    while ($product = $result->fetch_assoc()) {
        $product['qty'] = (int) $_SESSION['cart'][$product['id']];

        if (
            $product['qty'] < 1 ||
            $product['qty'] > (int) $product['stock']
        ) {
            continue;
        }

        $product['subtotal'] = $product['qty'] * (float) $product['price'];
        $total += $product['subtotal'];
        $items[] = $product;
    }
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();

    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $date = $_POST['date'] ?? '';
    $time = $_POST['time'] ?? '';

    if (
        empty($items) ||
        $name === '' ||
        $phone === '' ||
        $address === '' ||
        $date === '' ||
        $time === '' ||
        $date < date('Y-m-d')
    ) {
        $error = 'Periksa kembali data pemesan, jadwal, dan stok produk.';
    } else {
        try {
            $conn->begin_transaction();

            // Kurangi stok dengan pemeriksaan agar tidak terjadi stok minus.
            foreach ($items as $product) {
                $updateStock = $conn->prepare(
                    'UPDATE products
                     SET stock = stock - ?
                     WHERE id = ? AND stock >= ?'
                );

                $updateStock->bind_param(
                    'iii',
                    $product['qty'],
                    $product['id'],
                    $product['qty']
                );
                $updateStock->execute();

                if ($updateStock->affected_rows !== 1) {
                    throw new Exception('Stok produk tidak mencukupi.');
                }
            }

            $orderType = 'catalog';
            $status = 'Menunggu Konfirmasi';

            $insertOrder = $conn->prepare(
                'INSERT INTO orders
                    (order_type, customer_name, phone, address, total, status, pickup_date, pickup_time)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
            );

            $insertOrder->bind_param(
                'ssssdsss',
                $orderType,
                $name,
                $phone,
                $address,
                $total,
                $status,
                $date,
                $time
            );
            $insertOrder->execute();

            $orderId = $conn->insert_id;
            $orderCode = 'PF-' . str_pad($orderId, 5, '0', STR_PAD_LEFT);

            $updateCode = $conn->prepare(
                'UPDATE orders SET order_code = ? WHERE id = ?'
            );
            $updateCode->bind_param('si', $orderCode, $orderId);
            $updateCode->execute();

            $insertItem = $conn->prepare(
                'INSERT INTO order_items
                    (order_id, product_id, product_name, price, quantity, subtotal)
                 VALUES (?, ?, ?, ?, ?, ?)'
            );

            foreach ($items as $product) {
                $insertItem->bind_param(
                    'iisdid',
                    $orderId,
                    $product['id'],
                    $product['name'],
                    $product['price'],
                    $product['qty'],
                    $product['subtotal']
                );
                $insertItem->execute();
            }

            $conn->commit();

            $_SESSION['cart'] = [];

            $messageLines = [];

            foreach ($items as $product) {
                $messageLines[] =
                    '• ' . $product['name'] .
                    ' x' . $product['qty'] .
                    ' — ' . rp($product['subtotal']);
            }

            $message =
                "Halo PinkFlow, saya ingin mengonfirmasi pesanan.\n" .
                "Kode: $orderCode\n" .
                "Nama: $name\n" .
                "Telepon: $phone\n" .
                "Alamat: $address\n\n" .
                implode("\n", $messageLines) .
                "\n\nTotal: " . rp($total) .
                "\nPengambilan: $date pukul $time";

            header('Location: ' . wa($message));
            exit;
        } catch (Throwable $exception) {
            $conn->rollback();
            $error = 'Pesanan belum berhasil disimpan. Silakan periksa stok dan coba kembali.';
        }
    }
}

$pageCss = 'checkout.css';
$pageTitle = 'Checkout | PinkFlow';

include '../includes/header.php';
?>

<main class="checkout-page">
    <section class="checkout-heading">
        <span class="eyebrow">ONE LOVELY STEP TO GO</span>
        <h1>Selesaikan <em>pesananmu.</em></h1>
        <p>Lengkapi informasi berikut agar PinkFlow dapat menyiapkan pesananmu.</p>
    </section>

    <?php if ($error !== ''): ?>
        <div class="checkout-error" role="alert">
            <?= e($error) ?>
        </div>
    <?php endif; ?>

    <?php if (empty($items)): ?>
        <section class="checkout-empty">
            <span class="checkout-empty-icon">🛒</span>
            <h2>Keranjangmu masih kosong</h2>
            <p>Pilih produk favoritmu terlebih dahulu sebelum melanjutkan checkout.</p>
            <a class="btn dark" href="katalog.php">Jelajahi koleksi</a>
        </section>
    <?php else: ?>
        <section class="checkout-grid">
            <div class="checkout-form-card">
                <div class="checkout-card-heading">
                    <span class="checkout-step">01</span>
                    <div>
                        <h2>Informasi pemesan</h2>
                        <p>Isi data yang dapat kami gunakan untuk menghubungimu.</p>
                    </div>
                </div>

                <form method="post" class="checkout-form">
                    <input type="hidden" name="csrf" value="<?= e(csrf()) ?>">

                    <label>
                        Nama lengkap
                        <input
                            type="text"
                            name="name"
                            placeholder="Masukkan nama lengkap"
                            autocomplete="name"
                            value="<?= e($_POST['name'] ?? '') ?>"
                            required
                        >
                    </label>

                    <label>
                        Nomor telepon / WhatsApp
                        <input
                            type="tel"
                            name="phone"
                            placeholder="Contoh: 08xxxxxxxxxx"
                            autocomplete="tel"
                            value="<?= e($_POST['phone'] ?? '') ?>"
                            required
                        >
                    </label>

                    <label>
                        Alamat lengkap
                        <textarea
                            name="address"
                            rows="3"
                            placeholder="Masukkan alamat pengambilan atau pengiriman"
                            required
                        ><?= e($_POST['address'] ?? '') ?></textarea>
                    </label>

                    <div class="checkout-form-row">
                        <label>
                            Tanggal pengambilan
                            <input
                                type="date"
                                name="date"
                                min="<?= date('Y-m-d') ?>"
                                value="<?= e($_POST['date'] ?? '') ?>"
                                required
                            >
                        </label>

                        <label>
                            Jam pengambilan
                            <input
                                type="time"
                                name="time"
                                value="<?= e($_POST['time'] ?? '') ?>"
                                required
                            >
                        </label>
                    </div>

                    <button class="btn dark checkout-submit" type="submit">
                        Simpan pesanan & lanjut WhatsApp
                        <span aria-hidden="true">↗</span>
                    </button>

                    <p class="checkout-note">
                        Pesanan akan dicatat terlebih dahulu. WhatsApp kemudian terbuka
                        untuk konfirmasi langsung dengan PinkFlow.
                    </p>
                </form>
            </div>

            <aside class="checkout-summary">
                <div class="checkout-card-heading">
                    <span class="checkout-step">02</span>
                    <div>
                        <h2>Ringkasan pesanan</h2>
                        <p>Periksa kembali produk pilihanmu.</p>
                    </div>
                </div>

                <div class="checkout-items">
                    <?php foreach ($items as $product): ?>
                        <article class="checkout-item">
                            <div class="checkout-item-image">
                                <img
                                    src="<?= e(product_image_url($product['image'], $product['name'], '../')) ?>"
                                    alt="<?= e($product['name']) ?>"
                                    onerror="this.style.display='none'"
                                >
                            </div>

                            <div class="checkout-item-info">
                                <h3><?= e($product['name']) ?></h3>
                                <p><?= $product['qty'] ?> produk × <?= rp($product['price']) ?></p>
                            </div>

                            <strong><?= rp($product['subtotal']) ?></strong>
                        </article>
                    <?php endforeach; ?>
                </div>

                <div class="checkout-total">
                    <span>Total pembayaran</span>
                    <strong><?= rp($total) ?></strong>
                </div>

                <div class="checkout-summary-foot">
                    <span aria-hidden="true">♡</span>
                    Dirangkai dengan penuh perhatian oleh PinkFlow.
                </div>
            </aside>
        </section>
    <?php endif; ?>
</main>

<?php include '../includes/footer.php'; ?>
