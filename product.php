<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    header('HTTP/1.1 404 Not Found');
    include __DIR__ . '/includes/header.php';
    echo '<div class="container" style="padding:80px 20px;text-align:center;"><h2>Product Not Found</h2><a href="' . SITE_URL . '/products.php" class="btn btn--primary" style="margin-top:20px;">Back to Shop</a></div>';
    include __DIR__ . '/includes/footer.php';
    exit;
}

$pdo = getDB();
$stmt = $pdo->prepare('SELECT p.*, c.category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.id = ?');
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) {
    header('HTTP/1.1 404 Not Found');
    include __DIR__ . '/includes/header.php';
    echo '<div class="container" style="padding:80px 20px;text-align:center;"><h2>Product Not Found</h2><a href="' . SITE_URL . '/products.php" class="btn btn--primary" style="margin-top:20px;">Back to Shop</a></div>';
    include __DIR__ . '/includes/footer.php';
    exit;
}

include __DIR__ . '/includes/header.php';
?>

<div class="container">
    <!-- Breadcrumb -->
    <nav class="breadcrumb" aria-label="Breadcrumb">
        <a href="<?= SITE_URL ?>/index.php">Home</a>
        <span class="breadcrumb__sep">›</span>
        <a href="<?= SITE_URL ?>/products.php">Shop</a>
        <?php if ($product['category_name']): ?>
        <span class="breadcrumb__sep">›</span>
        <a href="<?= SITE_URL ?>/products.php?category_id=<?= $product['category_id'] ?>"><?= htmlspecialchars($product['category_name']) ?></a>
        <?php endif; ?>
        <span class="breadcrumb__sep">›</span>
        <span class="breadcrumb__current"><?= htmlspecialchars($product['name']) ?></span>
    </nav>

    <!-- Product Detail -->
    <div class="product-detail">
        <!-- Product Image -->
        <div class="product-detail__image">
            <?php if (!empty($product['image_url']) && str_starts_with($product['image_url'], 'http')): ?>
                <img src="<?= htmlspecialchars($product['image_url']) ?>"
                     alt="<?= htmlspecialchars($product['name']) ?>"
                     class="product-detail__img">
            <?php else: ?>
            <div class="product-detail__placeholder">
                <svg viewBox="0 0 500 660" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                    <rect width="500" height="660" fill="#f0ede8"/>
                    <text x="250" y="345" text-anchor="middle" fill="#c0b8b0"
                          font-family="Georgia, serif" font-size="120">
                        <?= htmlspecialchars(mb_substr($product['name'], 0, 1)) ?>
                    </text>
                </svg>
            </div>
            <?php endif; ?>
        </div>

        <!-- Product Info -->
        <div class="product-detail__info">
            <p class="product-detail__category"><?= htmlspecialchars($product['category_name'] ?? '') ?></p>
            <h1 class="product-detail__name"><?= htmlspecialchars($product['name']) ?></h1>
            <p class="product-detail__price">$<?= number_format($product['price'], 2) ?></p>

            <div class="product-detail__divider"></div>

            <p class="product-detail__description"><?= nl2br(htmlspecialchars($product['description'])) ?></p>

            <div class="product-detail__divider"></div>

            <div class="product-detail__stock">
                <?php if ($product['stock_quantity'] > 0): ?>
                    <span class="stock-badge stock-badge--in">In Stock (<?= $product['stock_quantity'] ?> available)</span>
                <?php else: ?>
                    <span class="stock-badge stock-badge--out">Out of Stock</span>
                <?php endif; ?>
            </div>

            <?php if ($product['stock_quantity'] > 0): ?>
            <div class="product-detail__add">
                <div class="quantity-selector">
                    <button type="button" class="qty-btn qty-btn--minus" aria-label="Decrease quantity">−</button>
                    <input type="number" class="qty-input" id="qty-input" value="1"
                           min="1" max="<?= $product['stock_quantity'] ?>" readonly>
                    <button type="button" class="qty-btn qty-btn--plus" aria-label="Increase quantity">+</button>
                </div>
                <button class="btn btn--primary btn--lg add-to-cart-detail"
                        data-product-id="<?= $product['id'] ?>"
                        data-max-stock="<?= $product['stock_quantity'] ?>">
                    Add to Cart
                </button>
            </div>
            <?php endif; ?>

            <a href="<?= SITE_URL ?>/products.php" class="product-detail__back">
                ← Back to Shop
            </a>
        </div>
    </div>
</div>

<script>
(function () {
    const qtyInput = document.getElementById('qty-input');
    const minusBtn = document.querySelector('.qty-btn--minus');
    const plusBtn  = document.querySelector('.qty-btn--plus');
    const addBtn   = document.querySelector('.add-to-cart-detail');

    if (!qtyInput) return;

    const maxStock = parseInt(addBtn.dataset.maxStock, 10);

    minusBtn.addEventListener('click', () => {
        const val = parseInt(qtyInput.value, 10);
        if (val > 1) qtyInput.value = val - 1;
    });

    plusBtn.addEventListener('click', () => {
        const val = parseInt(qtyInput.value, 10);
        if (val < maxStock) qtyInput.value = val + 1;
    });

    if (addBtn) {
        addBtn.addEventListener('click', () => {
            const productId = parseInt(addBtn.dataset.productId, 10);
            const qty = parseInt(qtyInput.value, 10);
            if (window.addToCart) {
                window.addToCart(productId, qty);
            }
        });
    }
})();
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
