<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

require_once 'db.php';

try {
    $stmt = $pdo->query("SELECT * FROM orders ORDER BY created_at DESC");
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Decode items JSON for each order
    foreach ($orders as &$order) {
        $order['items'] = json_decode($order['items'], true);
    }

    echo json_encode([
        'success' => true,
        'count'   => count($orders),
        'orders'  => $orders
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>