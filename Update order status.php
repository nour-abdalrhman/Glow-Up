<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }

require_once 'db.php';

$data = json_decode(file_get_contents('php://input'), true);

if (empty($data['order_id']) || empty($data['status'])) {
    echo json_encode(['success' => false, 'message' => 'order_id and status are required']);
    exit;
}

$allowed = ['pending', 'confirmed', 'shipped', 'delivered', 'cancelled'];
if (!in_array($data['status'], $allowed)) {
    echo json_encode(['success' => false, 'message' => 'Invalid status']);
    exit;
}

try {
    $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $stmt->execute([$data['status'], $data['order_id']]);
    echo json_encode(['success' => true, 'message' => 'Status updated successfully']);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>