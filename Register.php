<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { echo json_encode(['success' => false, 'message' => 'Method not allowed']); exit; }

require_once 'db.php';

$data = json_decode(file_get_contents('php://input'), true);

if (empty($data['name']) || empty($data['email']) || empty($data['password'])) {
    echo json_encode(['success' => false, 'message' => 'All fields are required']);
    exit;
}

if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email address']);
    exit;
}

if (strlen($data['password']) < 6) {
    echo json_encode(['success' => false, 'message' => 'Password must be at least 6 characters']);
    exit;
}

try {
    // Check if email exists
    $check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $check->execute([$data['email']]);
    if ($check->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Email already registered']);
        exit;
    }

    $hashed = password_hash($data['password'], PASSWORD_DEFAULT);
    $role = isset($data['role']) && $data['role'] === 'admin' ? 'admin' : 'customer';
    $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
    $stmt->execute([htmlspecialchars($data['name']), $data['email'], $hashed, $role]);

    echo json_encode([
        'success' => true,
        'message' => 'Account created successfully!',
        'user'    => ['id' => $pdo->lastInsertId(), 'name' => $data['name'], 'email' => $data['email'], 'role' => $role]
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Registration failed: ' . $e->getMessage()]);
}
?>