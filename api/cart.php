<?php
header('Content-Type: application/json; charset=utf-8');
require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/db.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $action = $_GET['action'] ?? '';
    if ($action === 'count') {
        echo json_encode(['count' => getCartCount()]);
    } else {
        http_response_code(400);
        echo json_encode(['error' => 'Unknown action']);
    }
    exit;
}

if ($method === 'POST') {
    if (!isLoggedIn()) {
        http_response_code(401);
        echo json_encode(['error' => 'Not authenticated']);
        exit;
    }

    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true) ?? [];
    $action = $data['action'] ?? '';
    $userId = $_SESSION['user_id'];
    $pdo = getDB();

    if ($action === 'add') {
        $productId = (int)($data['product_id'] ?? 0);
        $quantity  = max(1, (int)($data['quantity'] ?? 1));

        if ($productId <= 0) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid product ID']);
            exit;
        }

        // Check product exists and has stock
        $prodStmt = $pdo->prepare('SELECT id, stock_quantity FROM products WHERE id = ?');
        $prodStmt->execute([$productId]);
        $product = $prodStmt->fetch();

        if (!$product) {
            http_response_code(404);
            echo json_encode(['error' => 'Product not found']);
            exit;
        }

        // Check if already in cart
        $existStmt = $pdo->prepare('SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ?');
        $existStmt->execute([$userId, $productId]);
        $existing = $existStmt->fetch();

        if ($existing) {
            $newQty = $existing['quantity'] + $quantity;
            $newQty = min($newQty, $product['stock_quantity']);
            $pdo->prepare('UPDATE cart SET quantity = ? WHERE id = ?')->execute([$newQty, $existing['id']]);
        } else {
            $addQty = min($quantity, $product['stock_quantity']);
            $pdo->prepare('INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)')->execute([$userId, $productId, $addQty]);
        }

        echo json_encode(['success' => true, 'count' => getCartCount()]);

    } elseif ($action === 'remove') {
        $cartId = (int)($data['cart_id'] ?? 0);
        $stmt = $pdo->prepare('DELETE FROM cart WHERE id = ? AND user_id = ?');
        $stmt->execute([$cartId, $userId]);
        echo json_encode(['success' => true, 'count' => getCartCount()]);

    } elseif ($action === 'update') {
        $cartId  = (int)($data['cart_id'] ?? 0);
        $quantity = max(1, (int)($data['quantity'] ?? 1));

        // Validate against stock
        $stockStmt = $pdo->prepare('SELECT p.stock_quantity FROM cart c JOIN products p ON c.product_id = p.id WHERE c.id = ? AND c.user_id = ?');
        $stockStmt->execute([$cartId, $userId]);
        $row = $stockStmt->fetch();

        if (!$row) {
            http_response_code(404);
            echo json_encode(['error' => 'Cart item not found']);
            exit;
        }

        $quantity = min($quantity, $row['stock_quantity']);
        $pdo->prepare('UPDATE cart SET quantity = ? WHERE id = ? AND user_id = ?')->execute([$quantity, $cartId, $userId]);
        echo json_encode(['success' => true, 'quantity' => $quantity, 'count' => getCartCount()]);

    } else {
        http_response_code(400);
        echo json_encode(['error' => 'Unknown action']);
    }
    exit;
}

http_response_code(405);
echo json_encode(['error' => 'Method not allowed']);
