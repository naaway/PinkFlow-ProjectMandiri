<?php
require 'auth.php';

$id = (int) ($_GET['id'] ?? 0);

$orderStatement = $conn->prepare('SELECT * FROM orders WHERE id = ?');
$orderStatement->bind_param('i', $id);
$orderStatement->execute();
$order = $orderStatement->get_result()->fetch_assoc();

if (!$order) {
    http_response_code(404);
    exit('Pesanan tidak ditemukan.');
}

$itemStatement = $conn->prepare(
    'SELECT
        oi.product_name,
        oi.price,
        oi.quantity,
        oi.subtotal,
        p.image
     FROM order_items AS oi
     LEFT JOIN products AS p ON p.id = oi.product_id
     WHERE oi.order_id = ?'
);
$itemStatement->bind_param('i', $id);
$itemStatement->execute();
$items = $itemStatement->get_result()->fetch_all(MYSQLI_ASSOC);

include 'layout-top.php';
?>

<div class="admin-head">
    <div>
        <span class="eyebrow">ORDER DETAILS</span>
        <h1><?= e($order['order_code']) ?></h1>
        <p>Detail pesanan pelanggan PinkFlow.</p>
    </div>
    <a class="admin-btn" href="pesanan.php">Kembali ke pesanan</a>
</div>

<section class="admin-panel order-detail-panel">
    <h2>Informasi pelanggan</h2>
    <div class="order-customer-grid">
        <p><strong>Nama</strong><span><?= e($order['customer_name']) ?></span></p>
        <p><strong>Telepon</strong><span><?= e($order['phone']) ?></span></p>
        <p><strong>Alamat</strong><span><?= e($order['address']) ?></span></p>
        <p><strong>Jenis pesanan</strong><span><?= e($order['order_type']) ?></span></p>
        <p><strong>Jadwal pengambilan</strong><span><?= e($order['pickup_date'] ?: '-') ?> <?= e($order['pickup_time'] ?: '') ?></span></p>
    </div>

    <?php if (!empty($order['custom_details'])): ?>
        <div class="custom-details-box">
            <strong>Detail pesanan custom</strong>
            <p><?= nl2br(e($order['custom_details'])) ?></p>
        </div>
    <?php endif; ?>

    <h2 class="order-products-heading">Produk yang dipesan</h2>
    <div class="table-scroll">
        <table class="order-detail-table">
            <thead>
                <tr>
                    <th>Produk</th>
                    <th>Harga</th>
                    <th>Jumlah</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!$items): ?>
                    <tr>
                        <td colspan="4" class="empty-state">Tidak ada rincian produk katalog untuk pesanan ini.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($items as $item): ?>
                        <tr>
                            <td>
                                <div class="ordered-product detail-ordered-product">
                                    <?php if (!empty($item['image'])): ?>
                                        <img
                                            src="<?= e(product_image_url($item['image'], $item['product_name'])) ?>"
                                            alt="<?= e($item['product_name']) ?>"
                                        >
                                    <?php else: ?>
                                        <span class="ordered-product-placeholder" aria-hidden="true">✿</span>
                                    <?php endif; ?>
                                    <strong><?= e($item['product_name']) ?></strong>
                                </div>
                            </td>
                            <td><?= rp($item['price']) ?></td>
                            <td><?= (int) $item['quantity'] ?></td>
                            <td class="order-total"><?= rp($item['subtotal']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="order-grand-total">
        <span>Total pesanan</span>
        <strong><?= rp($order['total']) ?></strong>
    </div>
</section>

<?php include 'layout-bottom.php'; ?>
