<?php
require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/includes/auth.php';
requireAdmin();
require_once dirname(__DIR__) . '/includes/db.php';

$pdo = getDB();

$userCount    = (int)$pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
$productCount = (int)$pdo->query('SELECT COUNT(*) FROM products')->fetchColumn();
$orderCount   = (int)$pdo->query('SELECT COUNT(*) FROM orders')->fetchColumn();
$revenue      = (float)$pdo->query("SELECT COALESCE(SUM(total_price),0) FROM orders WHERE status != 'Pending'")->fetchColumn();

$recentOrders = $pdo->query(
    'SELECT o.*, u.full_name FROM orders o LEFT JOIN users u ON o.user_id = u.id ORDER BY o.created_at DESC LIMIT 10'
)->fetchAll();

$pageTitle = 'Admin Dashboard';
include dirname(__DIR__) . '/includes/header.php';
?>

<div class="admin-layout">
    <?php include __DIR__ . '/partials/sidebar.php'; ?>

    <div class="admin-main">
        <div class="admin-topbar">
            <h1 class="admin-page-title">Dashboard</h1>
        </div>

        <!-- Stats Cards -->
        <div class="admin-stats">
            <div class="admin-stat-card">
                <div class="admin-stat-card__icon admin-stat-card__icon--blue">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                        <circle cx="9" cy="7" r="4"></circle>
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"></path>
                    </svg>
                </div>
                <div class="admin-stat-card__body">
                    <p class="admin-stat-card__label">Total Users</p>
                    <p class="admin-stat-card__value"><?= $userCount ?></p>
                </div>
            </div>
            <div class="admin-stat-card">
                <div class="admin-stat-card__icon admin-stat-card__icon--green">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect>
                        <line x1="8" y1="21" x2="16" y2="21"></line>
                        <line x1="12" y1="17" x2="12" y2="21"></line>
                    </svg>
                </div>
                <div class="admin-stat-card__body">
                    <p class="admin-stat-card__label">Products</p>
                    <p class="admin-stat-card__value"><?= $productCount ?></p>
                </div>
            </div>
            <div class="admin-stat-card">
                <div class="admin-stat-card__icon admin-stat-card__icon--orange">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"></path>
                        <line x1="3" y1="6" x2="21" y2="6"></line>
                        <path d="M16 10a4 4 0 01-8 0"></path>
                    </svg>
                </div>
                <div class="admin-stat-card__body">
                    <p class="admin-stat-card__label">Orders</p>
                    <p class="admin-stat-card__value"><?= $orderCount ?></p>
                </div>
            </div>
            <div class="admin-stat-card">
                <div class="admin-stat-card__icon admin-stat-card__icon--purple">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <line x1="12" y1="1" x2="12" y2="23"></line>
                        <path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"></path>
                    </svg>
                </div>
                <div class="admin-stat-card__body">
                    <p class="admin-stat-card__label">Revenue</p>
                    <p class="admin-stat-card__value">$<?= number_format($revenue, 2) ?></p>
                </div>
            </div>
        </div>

        <!-- Recent Orders -->
        <div class="admin-card">
            <div class="admin-card__header">
                <h2 class="admin-card__title">Recent Orders</h2>
                <a href="<?= SITE_URL ?>/admin/orders.php" class="btn btn--secondary btn--sm">View All</a>
            </div>
            <div class="admin-table-wrap">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Customer</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($recentOrders)): ?>
                        <tr><td colspan="5" class="admin-table__empty">No orders yet.</td></tr>
                        <?php else: ?>
                        <?php foreach ($recentOrders as $order): ?>
                        <tr>
                            <td><?= $order['id'] ?></td>
                            <td><?= htmlspecialchars($order['full_name'] ?? 'Unknown') ?></td>
                            <td>$<?= number_format($order['total_price'], 2) ?></td>
                            <td>
                                <span class="status-badge status-badge--<?= strtolower($order['status']) ?>">
                                    <?= htmlspecialchars($order['status']) ?>
                                </span>
                            </td>
                            <td><?= date('M d, Y', strtotime($order['created_at'])) ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include dirname(__DIR__) . '/includes/footer.php'; ?>
