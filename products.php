<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';

$pdo = getDB();

// Pagination
$perPage = 9;
$page = max(1, (int)($_GET['page'] ?? 1));
$offset = ($page - 1) * $perPage;

// Filters
$categoryIds = [];
if (!empty($_GET['category_id'])) {
    $categoryIds = array_map('intval', (array)$_GET['category_id']);
}
$search = trim($_GET['search'] ?? '');

// Build WHERE clause
$where = [];
$params = [];

if (!empty($categoryIds)) {
    $placeholders = implode(',', array_fill(0, count($categoryIds), '?'));
    $where[] = "p.category_id IN ($placeholders)";
    $params = array_merge($params, $categoryIds);
}
if ($search !== '') {
    $where[] = '(p.name LIKE ? OR p.description LIKE ?)';
    $params[] = '%' . $search . '%';
    $params[] = '%' . $search . '%';
}

$whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

// Count total
$countSql = "SELECT COUNT(*) FROM products p LEFT JOIN categories c ON p.category_id = c.id $whereClause";
$countStmt = $pdo->prepare($countSql);
$countStmt->execute($params);
$totalProducts = (int)$countStmt->fetchColumn();
$totalPages = max(1, (int)ceil($totalProducts / $perPage));

// Fetch products
$sql = "SELECT p.*, c.category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id $whereClause ORDER BY p.created_at DESC LIMIT ? OFFSET ?";
$stmt = $pdo->prepare($sql);
$stmt->execute(array_merge($params, [$perPage, $offset]));
$products = $stmt->fetchAll();

// Fetch all categories for sidebar
$catStmt = $pdo->query('SELECT * FROM categories ORDER BY category_name');
$allCategories = $catStmt->fetchAll();

include __DIR__ . '/includes/header.php';
?>

<div class="page-banner">
    <div class="container">
        <h1 class="page-banner__title">The Shop</h1>
        <p class="page-banner__subtitle">Discover our complete collection</p>
    </div>
</div>

<div class="container shop-layout">
    <!-- Sidebar Filters -->
    <aside class="shop-sidebar">
        <form method="GET" action="" id="filter-form">
            <div class="sidebar__section">
                <h3 class="sidebar__title">Search</h3>
                <div class="sidebar__search">
                    <input type="text" name="search" id="search-input" value="<?= htmlspecialchars($search) ?>" placeholder="Search products…" class="form-control">
                </div>
            </div>

            <div class="sidebar__section">
                <h3 class="sidebar__title">Categories</h3>
                <ul class="sidebar__categories">
                    <?php foreach ($allCategories as $cat): ?>
                    <li>
                        <label class="checkbox-label">
                            <input type="checkbox" name="category_id[]" value="<?= $cat['id'] ?>"
                                <?= in_array($cat['id'], $categoryIds) ? 'checked' : '' ?>>
                            <span><?= htmlspecialchars($cat['category_name']) ?></span>
                        </label>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <button type="submit" class="btn btn--primary btn--full">Apply Filters</button>
            <?php if ($search || !empty($categoryIds)): ?>
                <a href="<?= SITE_URL ?>/products.php" class="btn btn--secondary btn--full" style="margin-top:8px;">Clear Filters</a>
            <?php endif; ?>
        </form>
    </aside>

    <!-- Product Grid -->
    <div class="shop-main">
        <div class="shop-header">
            <p class="shop-count">
                <?php if ($search): ?>
                    <?= $totalProducts ?> result<?= $totalProducts !== 1 ? 's' : '' ?> for "<?= htmlspecialchars($search) ?>"
                <?php else: ?>
                    <?= $totalProducts ?> product<?= $totalProducts !== 1 ? 's' : '' ?>
                <?php endif; ?>
            </p>
        </div>

        <?php if (empty($products)): ?>
        <div class="empty-state">
            <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="#c0b8b0" stroke-width="1">
                <circle cx="11" cy="11" r="8"></circle>
                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
            </svg>
            <p>No products found.</p>
            <a href="<?= SITE_URL ?>/products.php" class="btn btn--secondary">Browse All</a>
        </div>
        <?php else: ?>
        <div class="products-grid">
            <?php foreach ($products as $product): ?>
            <article class="product-card" data-product-id="<?= $product['id'] ?>">
                <a href="<?= SITE_URL ?>/product.php?id=<?= $product['id'] ?>" class="product-card__link">
                    <div class="product-card__image">
                        <?php if (!empty($product['image_url']) && str_starts_with($product['image_url'], 'http')): ?>
                            <img src="<?= htmlspecialchars($product['image_url']) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
                        <?php else: ?>
                        <svg viewBox="0 0 300 400" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                            <rect width="300" height="400" fill="#f0ede8"/>
                            <text x="150" y="210" text-anchor="middle" fill="#c0b8b0" font-family="Georgia, serif" font-size="72"><?= htmlspecialchars(mb_substr($product['name'], 0, 1)) ?></text>
                        </svg>
                        <?php endif; ?>
                    </div>
                    <div class="product-card__body">
                        <p class="product-card__category"><?= htmlspecialchars($product['category_name'] ?? '') ?></p>
                        <h3 class="product-card__title"><?= htmlspecialchars($product['name']) ?></h3>
                        <p class="product-card__price">$<?= number_format($product['price'], 2) ?></p>
                        <p class="product-card__stock <?= $product['stock_quantity'] > 0 ? 'in-stock' : 'out-of-stock' ?>">
                            <?= $product['stock_quantity'] > 0 ? 'In Stock' : 'Out of Stock' ?>
                        </p>
                    </div>
                </a>
                <div class="product-card__footer">
                    <button class="btn btn--primary btn--full add-to-cart-btn"
                            data-product-id="<?= $product['id'] ?>"
                            <?= $product['stock_quantity'] <= 0 ? 'disabled' : '' ?>>
                        <?= $product['stock_quantity'] > 0 ? 'Add to Cart' : 'Out of Stock' ?>
                    </button>
                </div>
            </article>
            <?php endforeach; ?>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
        <nav class="pagination" aria-label="Product pagination">
            <?php
            $queryBase = http_build_query(array_filter([
                'search' => $search,
                'category_id' => $categoryIds ?: null,
            ]));
            ?>
            <?php if ($page > 1): ?>
                <a href="?<?= $queryBase ?>&page=<?= $page - 1 ?>" class="pagination__btn">← Prev</a>
            <?php endif; ?>

            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a href="?<?= $queryBase ?>&page=<?= $i ?>"
                   class="pagination__btn <?= $i === $page ? 'pagination__btn--active' : '' ?>">
                    <?= $i ?>
                </a>
            <?php endfor; ?>

            <?php if ($page < $totalPages): ?>
                <a href="?<?= $queryBase ?>&page=<?= $page + 1 ?>" class="pagination__btn">Next →</a>
            <?php endif; ?>
        </nav>
        <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
