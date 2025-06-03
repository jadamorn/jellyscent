<?php
ob_start(); // buffer output to catch accidental output before headers
session_start();


ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

$host = "localhost";
$user = "root";
$password = "";
$database = "jellyscent";

$conn = new mysqli($host, $user, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}




// Check if user logged in
if (!isset($_SESSION['user_id'])) {
    error_log("User not logged in. SESSION: " . print_r($_SESSION, true));
    echo json_encode(['error' => 'User not logged in']);
    exit;
}

$userId = (int)$_SESSION['user_id'];

// Determine action
$action = $_GET['action'] ?? null;
$input = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$action) {
        $action = $input['action'] ?? null;
    }
}

switch ($action) {
    case 'get_count':
        $stmt = $conn->prepare("SELECT COALESCE(SUM(quantity), 0) AS count FROM cart_item WHERE cart_user_id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        echo json_encode(['count' => (int)($result['count'] ?? 0)]);
        break;

    case 'add_item':
        if (!$input) {
            echo json_encode(['error' => 'No input data']);
            break;
        }

        $name = $input['name'] ?? '';
        $size = $input['size'] ?? '';
        $price = floatval($input['price'] ?? 0);
        $quantity = intval($input['quantity'] ?? 0);
        $totalPrice = $price * $quantity;

        if (!$name || !$size || $price <= 0 || $quantity <= 0) {
            echo json_encode(['error' => 'Invalid input']);
            break;
        }

        // Check if item already exists
        $stmt = $conn->prepare("SELECT quantity, total_price FROM cart_item WHERE cart_user_id = ? AND product_name = ? AND size = ?");
        $stmt->bind_param("iss", $userId, $name, $size);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Update existing item
            $stmt = $conn->prepare("UPDATE cart_item SET quantity = quantity + ?, total_price = total_price + ? WHERE cart_user_id = ? AND product_name = ? AND size = ?");
            $stmt->bind_param("dids", $quantity, $totalPrice, $userId, $name, $size);
        } else {
            // Insert new item
            $stmt = $conn->prepare("INSERT INTO cart_item (cart_user_id, product_name, size, price, quantity, total_price) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("issdid", $userId, $name, $size, $price, $quantity, $totalPrice);
        }

        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['error' => 'Database operation failed']);
        }
        break;

    case 'buy_now':
        if (!$input) {
            echo json_encode(['error' => 'No input data']);
            break;
        }
        $_SESSION['checkout_item'] = $input;
        echo json_encode(['success' => true]);
        break;

    default:
        echo json_encode(['error' => 'Invalid or missing action']);
        break;
}

$conn->close();
exit;
