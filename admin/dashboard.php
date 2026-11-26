<?php
require 'auth.php';

$totalOrders = (int) $conn->query(
    'SELECT COUNT(*) AS total FROM orders'
)->fetch_assoc()['total'];

$totalProducts = (int) $conn->query(
    'SELECT COUNT(*) AS total FROM products WHERE is_active = 1'
)->fetch_assoc()['total'];

$pendingOrders = (int) $conn->query(
    "SELECT COUNT(*) AS total FROM orders WHERE status = 'Menunggu Konfirmasi'"
)->fetch_assoc()['total'];

$completedRevenue = (float) $conn->query(
    "SELECT COALESCE(SUM(total), 0) AS revenue FROM orders WHERE status = 'Selesai'"
)->fetch_assoc()['revenue'];

$recentOrders = $conn->query(
    'SELECT id, order_code, customer_name, order_type, total, status
     FROM orders
     ORDER BY id DESC
     LIMIT 6'
)->fetch_all(MYSQLI_ASSOC);

$lowStock = $conn->query(
    'SELECT id, name, stock, image
     FROM products
     WHERE is_active = 1
     ORDER BY stock ASC, id DESC
     LIMIT 4'
)->fetch_all(MYSQLI_ASSOC);

include 'layout-top.php';
?>

<section class="dashboard-welcome">
    <div>
        <span class="eyebrow">YOUR STORE AT A GLANCE</span>
        <h1>Dashboard<span>.</span></h1>
        <p>Selamat datang kembali, <?= e($_SESSION['admin_name']) ?>. Ini ringkasan PinkFlow hari ini.</p>
    </div>
    <a class="admin-btn" href="produk.php">＋ Tambah produk</a>
</section>

<section class="stats-grid">
    <article class="stat-card stat-products">
        <div class="stat-topline">
            <span>Total produk</span>
            <span class="stat-icon">✿</span>
        </div>
        <strong><?= number_format($totalProducts, 0, ',', '.') ?></strong>
        <small>Produk aktif di katalog</small>
    </article>

    <article class="stat-card stat-orders">
        <div class="stat-topline">
            <span>Total pesanan</span>
            <span class="stat-icon">▤</span>
        </div>
        <strong><?= number_format($totalOrders, 0, ',', '.') ?></strong>
        <small>Pesanan yang tercatat</small>
    </article>

    <article class="stat-card stat-pending">
        <div class="stat-topline">
            <span>Perlu konfirmasi</span>
            <span class="stat-icon">◷</span>
        </div>
        <strong><?= number_format($pendingOrders, 0, ',', '.') ?></strong>
        <small>Menunggu tindak lanjut</small>
    </article>

    <article class="stat-card stat-revenue">
        <div class="stat-topline">
            <span>Omzet pesanan selesai</span>
            <span class="stat-icon">↗</span>
        </div>
        <strong class="stat-currency"><?= rp($completedRevenue) ?></strong>
        <small>Total dari pesanan berstatus selesai</small>
    </article>
</section>

<div class="dashboard-columns">
    <section class="admin-panel recent-panel">
        <div class="panel-head">
            <div>
                <span class="panel-kicker">LATEST ACTIVITY</span>
                <h2>Pesanan terbaru</h2>
            </div>
            <a class="text-link" href="pesanan.php">Lihat semua ↗</a>
        </div>

        <div class="table-scroll">
            <table>
                <thead>
                    <tr>
                        <th>Kode pesanan</th>
                        <th>Pelanggan</th>
                        <th>Jenis</th>
                        <th>Total</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!$recentOrders): ?>
                        <tr>
                            <td colspan="5" class="empty-state">Belum ada pesanan yang masuk.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($recentOrders as $order): ?>
                            <tr>
                                <td>
                                    <a class="order-code" href="detail-pesanan.php?id=<?= (int) $order['id'] ?>">
                                        <?= e($order['order_code']) ?>
                                    </a>
                                </td>
                                <td><?= e($order['customer_name']) ?></td>
                                <td><?= e($order['order_type']) ?></td>
                                <td><?= rp($order['total']) ?></td>
                                <td><span class="status-pill"><?= e($order['status']) ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>

    <section class="admin-panel stock-panel">
        <div class="panel-head">
            <div>
                <span class="panel-kicker">INVENTORY CHECK</span>
                <h2>Stok produk</h2>
            </div>
            <a class="text-link" href="produk.php">Kelola ↗</a>
        </div>

        <div class="stock-list">
            <?php if (!$lowStock): ?>
                <p class="empty-state">Belum ada produk aktif.</p>
            <?php else: ?>
                <?php foreach ($lowStock as $product): ?>
                    <div class="stock-item">
                        <div class="stock-flower">✿</div>
                        <div class="stock-info">
                            <strong><?= e($product['name']) ?></strong>
                            <small>Persediaan produk</small>
                        </div>
                        <span class="stock-number"><?= (int) $product['stock'] ?></span>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </section>
</div>

<section class="dashboard-bottom-banner">
    <div>
        <span> A LITTLE REMINDER </span>
        <h2>Thoughtfully made,<br><em>beautifully managed.</em></h2>
        <p>Terima kasih sudah merawat setiap detail PinkFlow.</p>
    </div>
    <div class="banner-flower" aria-hidden="true">✿</div>
</section>

<?php include 'layout-bottom.php'; ?>
