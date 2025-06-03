<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

$userId = $_SESSION['user_id'];
$conn = new mysqli('localhost', 'root', '', 'jellyscent');
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

// Fetch user info
$userSql = "SELECT email, first_name, last_name, comp_address, brgy, zipcode, city, region, phone_no FROM users WHERE user_id = ?";
$stmt = $conn->prepare($userSql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Fetch cart items
$cartSql = "SELECT pr.name, ps.price, c.size, c.quantity, (ps.price * c.quantity) AS total_price, pr.image
            FROM cart_item c
            JOIN product pr ON c.product_id = pr.product_id
            JOIN product_size ps ON c.product_id = ps.product_id AND c.size = ps.size
            WHERE c.cart_user_id = ?";
$stmt2 = $conn->prepare($cartSql);
$stmt2->bind_param("i", $userId);
$stmt2->execute();
$cartResult = $stmt2->get_result();

$cart = [];
while ($row = $cartResult->fetch_assoc()) {
    $cart[] = $row;
}

echo json_encode(['success' => true, 'user' => $user, 'cart' => $cart]);
$conn->close();
?>
