<?php
header('Content-Type: application/json; charset=utf-8');
require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Not authenticated']);
    exit;
}

$raw  = file_get_contents('php://input');
$data = json_decode($raw, true) ?? [];
$userId = $_SESSION['user_id'];
$pdo = getDB();

// Fetch cart items
$stmt = $pdo->prepare(
    'SELECT c.id as cart_id, c.quantity, p.id as product_id, p.price, p.stock_quantity
     FROM cart c JOIN products p ON c.product_id = p.id
     WHERE c.user_id = ?'
);
$stmt->execute([$userId]);
$cartItems = $stmt->fetchAll();

if (empty($cartItems)) {
    http_response_code(400);
    echo json_encode(['error' => 'Cart is empty']);
    exit;
}

// Validate stock
foreach ($cartItems as $item) {
    if ($item['quantity'] > $item['stock_quantity']) {
        http_response_code(422);
        echo json_encode(['error' => 'Insufficient stock for a product in your cart']);
        exit;
    }
}

$subtotal = array_reduce($cartItems, fn($carry, $item) => $carry + $item['price'] * $item['quantity'], 0);
$shipping = $subtotal >= 150 ? 0 : 9.99;
$total    = $subtotal + $shipping;

$user = getCurrentUser();
$shippingName    = trim($data['shipping_name']    ?? $user['full_name'] ?? '');
$shippingEmail   = trim($data['shipping_email']   ?? $user['email'] ?? '');
$shippingAddress = trim($data['shipping_address'] ?? $user['address'] ?? '');

try {
    $pdo->beginTransaction();

    $orderStmt = $pdo->prepare(
        'INSERT INTO orders (user_id, total_price, shipping_name, shipping_email, shipping_address)
         VALUES (?, ?, ?, ?, ?)'
    );
    $orderStmt->execute([$userId, $total, $shippingName, $shippingEmail, $shippingAddress]);
    $orderId = (int)$pdo->lastInsertId();

    $itemStmt  = $pdo->prepare('INSERT INTO order_items (order_id, product_id, quantity, price_at_purchase) VALUES (?, ?, ?, ?)');
    $stockStmt = $pdo->prepare('UPDATE products SET stock_quantity = stock_quantity - ? WHERE id = ?');

    foreach ($cartItems as $item) {
        $itemStmt->execute([$orderId, $item['product_id'], $item['quantity'], $item['price']]);
        $stockStmt->execute([$item['quantity'], $item['product_id']]);
    }

    $pdo->prepare('DELETE FROM cart WHERE user_id = ?')->execute([$userId]);

    $pdo->commit();
    echo json_encode(['success' => true, 'order_id' => $orderId]);
} catch (Exception $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['error' => 'Order could not be placed']);
}
