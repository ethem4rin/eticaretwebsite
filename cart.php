<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/auth.php';
requireLogin();
require_once __DIR__ . '/includes/db.php';

$pdo = getDB();
$userId = $_SESSION['user_id'];

// Fetch cart items with product details
$stmt = $pdo->prepare(
    'SELECT c.id as cart_id, c.quantity, p.id as product_id, p.name, p.price, p.stock_quantity, p.image_url
     FROM cart c
     JOIN products p ON c.product_id = p.id
     WHERE c.user_id = ?
     ORDER BY c.id'
);
$stmt->execute([$userId]);
$cartItems = $stmt->fetchAll();

$total = array_reduce($cartItems, fn($carry, $item) => $carry + $item['price'] * $item['quantity'], 0);

include __DIR__ . '/includes/header.php';
?>

<div class="container">
    <div class="page-title-section">
        <h1 class="page-title">Shopping Cart</h1>
    </div>

    <?php if (empty($cartItems)): ?>
    <div class="empty-state empty-state--cart">
        <svg width="72" height="72" viewBox="0 0 24 24" fill="none" stroke="#c0b8b0" stroke-width="1">
            <circle cx="9" cy="21" r="1"></circle>
            <circle cx="20" cy="21" r="1"></circle>
            <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
        </svg>
        <h2>Your cart is empty</h2>
        <p>Discover our collection and add your favourite pieces.</p>
        <a href="<?= SITE_URL ?>/products.php" class="btn btn--primary">Shop Now</a>
    </div>

    <?php else: ?>
    <div class="cart-layout">
        <div class="cart-items">
            <table class="cart-table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Total</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody id="cart-tbody">
                <?php foreach ($cartItems as $item): ?>
                <tr class="cart-row" data-cart-id="<?= $item['cart_id'] ?>">
                    <td class="cart-product">
                        <div class="cart-product__img">
                            <?php if (!empty($item['image_url']) && str_starts_with($item['image_url'], 'http')): ?>
                                <img src="<?= htmlspecialchars($item['image_url']) ?>" alt="">
                            <?php else: ?>
                            <svg viewBox="0 0 100 130" xmlns="http://www.w3.org/2000/svg">
                                <rect width="100" height="130" fill="#f0ede8"/>
                                <text x="50" y="70" text-anchor="middle" fill="#c0b8b0"
                                      font-family="Georgia" font-size="36">
                                    <?= htmlspecialchars(mb_substr($item['name'], 0, 1)) ?>
                                </text>
                            </svg>
                            <?php endif; ?>
                        </div>
                        <div class="cart-product__info">
                            <a href="<?= SITE_URL ?>/product.php?id=<?= $item['product_id'] ?>"
                               class="cart-product__name"><?= htmlspecialchars($item['name']) ?></a>
                        </div>
                    </td>
                    <td class="cart-price" data-price="<?= $item['price'] ?>">
                        $<?= number_format($item['price'], 2) ?>
                    </td>
                    <td class="cart-qty-cell">
                        <div class="quantity-selector">
                            <button type="button" class="qty-btn qty-btn--minus cart-qty-btn"
                                    data-cart-id="<?= $item['cart_id'] ?>" data-action="minus">−</button>
                            <input type="number" class="qty-input cart-qty-input"
                                   value="<?= $item['quantity'] ?>"
                                   min="1" max="<?= $item['stock_quantity'] ?>"
                                   data-cart-id="<?= $item['cart_id'] ?>" readonly>
                            <button type="button" class="qty-btn qty-btn--plus cart-qty-btn"
                                    data-cart-id="<?= $item['cart_id'] ?>" data-action="plus"
                                    <?= $item['quantity'] >= $item['stock_quantity'] ? 'disabled' : '' ?>>+</button>
                        </div>
                    </td>
                    <td class="cart-subtotal" data-quantity="<?= $item['quantity'] ?>">
                        $<?= number_format($item['price'] * $item['quantity'], 2) ?>
                    </td>
                    <td class="cart-remove">
                        <button type="button" class="remove-cart-btn" data-cart-id="<?= $item['cart_id'] ?>"
                                aria-label="Remove item">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                <polyline points="3 6 5 6 21 6"></polyline>
                                <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"></path>
                                <path d="M10 11v6M14 11v6"></path>
                                <path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"></path>
                            </svg>
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <aside class="cart-summary">
            <div class="cart-summary__card">
                <h2 class="cart-summary__title">Order Summary</h2>
                <div class="cart-summary__row">
                    <span>Subtotal</span>
                    <span id="cart-subtotal">$<?= number_format($total, 2) ?></span>
                </div>
                <div class="cart-summary__row">
                    <span>Shipping</span>
                    <span class="cart-summary__shipping"><?= $total >= 150 ? 'Free' : '$9.99' ?></span>
                </div>
                <div class="cart-summary__divider"></div>
                <div class="cart-summary__row cart-summary__total">
                    <span>Total</span>
                    <span id="cart-total">$<?= number_format($total >= 150 ? $total : $total + 9.99, 2) ?></span>
                </div>
                <a href="<?= SITE_URL ?>/checkout.php" class="btn btn--primary btn--full cart-checkout-btn">
                    Proceed to Checkout
                </a>
                <a href="<?= SITE_URL ?>/products.php" class="btn btn--secondary btn--full" style="margin-top:10px;">
                    Continue Shopping
                </a>
            </div>
        </aside>
    </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
