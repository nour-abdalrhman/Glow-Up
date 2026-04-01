<?php
// Allow requests from frontend
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

require_once 'db.php';

// Get JSON body
$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit;
}

// Validate required fields
$required = ['first_name', 'last_name', 'email', 'phone', 'address', 'city', 'governorate', 'items', 'total'];
foreach ($required as $field) {
    if (empty($data[$field])) {
        echo json_encode(['success' => false, 'message' => "Field '$field' is required"]);
        exit;
    }
}

// Generate order number
$order_number = 'GU-' . strtoupper(substr(md5(uniqid()), 0, 8));

try {
    $stmt = $pdo->prepare("
        INSERT INTO orders 
        (order_number, first_name, last_name, email, phone, address, city, governorate, apartment, shipping_method, payment_method, notes, items, subtotal, shipping_cost, total)
        VALUES 
        (:order_number, :first_name, :last_name, :email, :phone, :address, :city, :governorate, :apartment, :shipping_method, :payment_method, :notes, :items, :subtotal, :shipping_cost, :total)
    ");

    $stmt->execute([
        ':order_number'    => $order_number,
        ':first_name'      => htmlspecialchars($data['first_name']),
        ':last_name'       => htmlspecialchars($data['last_name']),
        ':email'           => htmlspecialchars($data['email']),
        ':phone'           => htmlspecialchars($data['phone']),
        ':address'         => htmlspecialchars($data['address']),
        ':city'            => htmlspecialchars($data['city']),
        ':governorate'     => htmlspecialchars($data['governorate']),
        ':apartment'       => htmlspecialchars($data['apartment'] ?? ''),
        ':shipping_method' => htmlspecialchars($data['shipping_method'] ?? 'standard'),
        ':payment_method'  => htmlspecialchars($data['payment_method'] ?? 'cod'),
        ':notes'           => htmlspecialchars($data['notes'] ?? ''),
        ':items'           => json_encode($data['items']),
        ':subtotal'        => floatval($data['subtotal'] ?? 0),
        ':shipping_cost'   => floatval($data['shipping_cost'] ?? 0),
        ':total'           => floatval($data['total']),
    ]);

    echo json_encode([
        'success'      => true,
        'message'      => 'Order placed successfully!',
        'order_number' => $order_number,
        'order_id'     => $pdo->lastInsertId()
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to save order: ' . $e->getMessage()]);
}
?>