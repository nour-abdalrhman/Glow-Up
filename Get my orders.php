<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

require_once 'db.php';

$email = $_GET['email'] ?? '';

if (empty($email)) {
    echo json_encode(['success' => false, 'message' => 'Email is required']);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE email = ? ORDER BY created_at DESC");
    $stmt->execute([$email]);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($orders as &$order) {
        $order['items'] = json_decode($order['items'], true);
    }

    echo json_encode(['success' => true, 'orders' => $orders]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>