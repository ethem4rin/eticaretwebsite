<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';

$pdo = getDB();

// Fetch 6 latest products
$featuredStmt = $pdo->query('SELECT p.*, c.category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.created_at DESC LIMIT 6');
$featuredProducts = $featuredStmt->fetchAll();

// Fetch categories
$catStmt = $pdo->query('SELECT * FROM categories ORDER BY id');
$categories = $catStmt->fetchAll();

include __DIR__ . '/includes/header.php';
?>

<!-- Hero Section -->
<section class="hero">
    <div class="hero__overlay"></div>
    <div class="hero__content">
        <p class="hero__eyebrow">New Collection 2024</p>
        <h1 class="hero__title">Timeless<br>Elegance</h1>
        <p class="hero__subtitle">Discover curated luxury fashion crafted for the modern individual.</p>
        <a href="<?= SITE_URL ?>/products.php" class="btn btn--hero">Shop the Collection</a>
    </div>
</section>

<!-- Featured Products -->
<section class="section">
    <div class="container">
        <div class="section__header">
            <h2 class="section__title">Featured Pieces</h2>
            <p class="section__subtitle">Carefully selected for the discerning wardrobe</p>
        </div>
        <div class="products-grid">
            <?php foreach ($featuredProducts as $product): ?>
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
        <div class="section__cta">
            <a href="<?= SITE_URL ?>/products.php" class="btn btn--secondary">View All Products</a>
        </div>
    </div>
</section>

<!-- Categories Section -->
<section class="section section--gray">
    <div class="container">
        <div class="section__header">
            <h2 class="section__title">Shop by Category</h2>
            <p class="section__subtitle">Explore our curated collections</p>
        </div>
        <div class="categories-grid">
            <div class="category-card">
                <a href="<?= SITE_URL ?>/products.php?category_id=1" class="category-card__link">
                    <div class="category-card__image">
                        <svg viewBox="0 0 400 300" xmlns="http://www.w3.org/2000/svg">
                            <rect width="400" height="300" fill="#1a1a1a"/>
                            <text x="200" y="140" text-anchor="middle" fill="#f0ede8" font-family="Georgia, serif" font-size="24" letter-spacing="4">MEN</text>
                            <line x1="160" y1="160" x2="240" y2="160" stroke="#8c7b6b" stroke-width="1"/>
                            <text x="200" y="185" text-anchor="middle" fill="#8c7b6b" font-family="Georgia, serif" font-size="13" letter-spacing="2">COLLECTION</text>
                        </svg>
                    </div>
                    <div class="category-card__body">
                        <h3>Men</h3>
                        <span class="category-card__explore">Explore →</span>
                    </div>
                </a>
            </div>
            <div class="category-card">
                <a href="<?= SITE_URL ?>/products.php?category_id=2" class="category-card__link">
                    <div class="category-card__image">
                        <svg viewBox="0 0 400 300" xmlns="http://www.w3.org/2000/svg">
                            <rect width="400" height="300" fill="#8c7b6b"/>
                            <text x="200" y="140" text-anchor="middle" fill="#fff" font-family="Georgia, serif" font-size="24" letter-spacing="4">WOMEN</text>
                            <line x1="155" y1="160" x2="245" y2="160" stroke="rgba(255,255,255,0.5)" stroke-width="1"/>
                            <text x="200" y="185" text-anchor="middle" fill="rgba(255,255,255,0.7)" font-family="Georgia, serif" font-size="13" letter-spacing="2">COLLECTION</text>
                        </svg>
                    </div>
                    <div class="category-card__body">
                        <h3>Women</h3>
                        <span class="category-card__explore">Explore →</span>
                    </div>
                </a>
            </div>
            <div class="category-card">
                <a href="<?= SITE_URL ?>/products.php?category_id=3" class="category-card__link">
                    <div class="category-card__image">
                        <svg viewBox="0 0 400 300" xmlns="http://www.w3.org/2000/svg">
                            <rect width="400" height="300" fill="#e8e0d8"/>
                            <text x="200" y="130" text-anchor="middle" fill="#1a1a1a" font-family="Georgia, serif" font-size="20" letter-spacing="3">ACCESSORIES</text>
                            <line x1="140" y1="150" x2="260" y2="150" stroke="#8c7b6b" stroke-width="1"/>
                            <text x="200" y="175" text-anchor="middle" fill="#8c7b6b" font-family="Georgia, serif" font-size="13" letter-spacing="2">COLLECTION</text>
                        </svg>
                    </div>
                    <div class="category-card__body">
                        <h3>Accessories</h3>
                        <span class="category-card__explore">Explore →</span>
                    </div>
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Value Proposition -->
<section class="section">
    <div class="container">
        <div class="values-grid">
            <div class="value-item">
                <div class="value-item__icon">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2">
                        <path d="M5 12h14M12 5l7 7-7 7"/>
                    </svg>
                </div>
                <h4>Free Shipping</h4>
                <p>On all orders over $150</p>
            </div>
            <div class="value-item">
                <div class="value-item__icon">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2">
                        <polyline points="23 4 23 10 17 10"></polyline>
                        <path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"></path>
                    </svg>
                </div>
                <h4>Easy Returns</h4>
                <p>30-day hassle-free returns</p>
            </div>
            <div class="value-item">
                <div class="value-item__icon">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                    </svg>
                </div>
                <h4>Secure Payment</h4>
                <p>SSL encrypted checkout</p>
            </div>
            <div class="value-item">
                <div class="value-item__icon">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2">
                        <circle cx="12" cy="12" r="10"></circle>
                        <path d="M12 8v4l3 3"/>
                    </svg>
                </div>
                <h4>24/7 Support</h4>
                <p>Always here to help</p>
            </div>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
