<?php
require 'auth.php';

$query = $conn->query(
    "SELECT
        o.id,
        o.order_code,
        o.customer_name,
        o.phone,
        o.order_type,
        o.total,
        oi.product_name,
        oi.quantity,
        p.image
    FROM orders AS o
    LEFT JOIN order_items AS oi ON oi.order_id = o.id
    LEFT JOIN products AS p ON p.id = oi.product_id
    ORDER BY o.id DESC, oi.id ASC"
);

$orders = [];

while ($row = $query->fetch_assoc()) {
    $orderId = (int) $row['id'];

    if (!isset($orders[$orderId])) {
        $orders[$orderId] = [
            'id' => $orderId,
            'order_code' => $row['order_code'],
            'customer_name' => $row['customer_name'],
            'phone' => $row['phone'],
            'order_type' => $row['order_type'],
            'total' => $row['total'],
            'items' => [],
        ];
    }

    if ($row['product_name'] !== null) {
        $orders[$orderId]['items'][] = [
            'name' => $row['product_name'],
            'quantity' => (int) $row['quantity'],
            'image' => $row['image'],
        ];
    }
}

include 'layout-top.php';
?>

<div class="admin-head">
    <div>
        <span class="eyebrow">ORDER MANAGEMENT</span>
        <h1>Pesanan</h1>
        <p>Pantau pesanan katalog dan custom yang masuk ke PinkFlow.</p>
    </div>
</div>

<section class="admin-panel orders-panel">
    <div class="table-scroll">
        <table class="orders-table">
            <thead>
                <tr>
                    <th>Kode</th>
                    <th>Pelanggan</th>
                    <th>Kontak</th>
                    <th>Produk dipesan</th>
                    <th>Jenis</th>
                    <th>Total</th>
                    <th>Detail</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!$orders): ?>
                    <tr>
                        <td colspan="7" class="empty-state">Belum ada pesanan yang masuk.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td>
                                <span class="order-code">
                                    <?= e($order['order_code']) ?>
                                </span>
                            </td>
                            <td><?= e($order['customer_name']) ?></td>
                            <td><?= e($order['phone']) ?></td>
                            <td>
                                <div class="ordered-products">
                                    <?php if ($order['items']): ?>
                                        <?php foreach ($order['items'] as $item): ?>
                                            <div class="ordered-product">
                                                <?php if (!empty($item['image'])): ?>
                                                    <img
                                                        src="<?= e(product_image_url($item['image'], $item['name'])) ?>"
                                                        alt="<?= e($item['name']) ?>"
                                                        loading="lazy"
                                                    >
                                                <?php else: ?>
                                                    <span class="ordered-product-placeholder" aria-hidden="true">✿</span>
                                                <?php endif; ?>
                                                <span>
                                                    <strong><?= e($item['name']) ?></strong>
                                                    <small>Qty: <?= $item['quantity'] ?></small>
                                                </span>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <span class="custom-order-label">Pesanan custom</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td><?= e($order['order_type']) ?></td>
                            <td class="order-total"><?= rp($order['total']) ?></td>
                            <td>
                                <a
                                    class="admin-btn small order-detail-link"
                                    href="detail-pesanan.php?id=<?= $order['id'] ?>"
                                >
                                    Lihat detail
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<?php include 'layout-bottom.php'; ?>
