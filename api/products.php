<?php
header('Content-Type: application/json; charset=utf-8');
require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/includes/db.php';

$action = $_GET['action'] ?? 'list';

if ($action === 'list') {
    $pdo = getDB();
    $where  = [];
    $params = [];

    if (!empty($_GET['category_id'])) {
        $ids = array_map('intval', (array)$_GET['category_id']);
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $where[] = "p.category_id IN ($placeholders)";
        $params  = array_merge($params, $ids);
    }
    if (!empty($_GET['search'])) {
        $where[] = '(p.name LIKE ? OR p.description LIKE ?)';
        $params[] = '%' . $_GET['search'] . '%';
        $params[] = '%' . $_GET['search'] . '%';
    }

    $sql = 'SELECT p.*, c.category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id';
    if ($where) $sql .= ' WHERE ' . implode(' AND ', $where);
    $sql .= ' ORDER BY p.created_at DESC';

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    echo json_encode($stmt->fetchAll());

} elseif ($action === 'get') {
    $id = (int)($_GET['id'] ?? 0);
    $pdo = getDB();
    $stmt = $pdo->prepare('SELECT p.*, c.category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.id = ?');
    $stmt->execute([$id]);
    $product = $stmt->fetch();
    echo json_encode($product ?: ['error' => 'Not found']);
} else {
    http_response_code(400);
    echo json_encode(['error' => 'Unknown action']);
}
