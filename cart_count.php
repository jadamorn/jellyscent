<?php
session_start();
header('Content-Type: application/json');

// Database connection
$conn = new mysqli('localhost', 'root', '', 'jellyscent');
if ($conn->connect_error) {
    echo json_encode(['count' => 0]);
    exit;
}

// Get user ID from session
$userId = $_SESSION['user_id'] ?? null;

if (!$userId) {
    echo json_encode(['count' => 0]);
    exit;
}

// Get total quantity of items in cart
$stmt = $conn->prepare("SELECT SUM(quantity) as total FROM cart_item WHERE cart_user_id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

echo json_encode(['count' => (int)($row['total'] ?? 0)]);
$conn->close();
?>
