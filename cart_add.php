<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();

header('Content-Type: application/json');

$host = 'localhost';
$user = 'root';
$password = '';
$dbname = 'jellyscent';

$conn = new mysqli($host, $user, $password, $dbname);

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

$userId = $_SESSION['user_id'];
$action = $_REQUEST['action'] ?? '';

switch ($action) {
    case 'get':
        // Join cart_item with products to get product info and product_id
        $stmt = $conn->prepare("
            SELECT ci.*, p.name, p.product_id AS product_id
            FROM cart_item ci
            JOIN product p ON ci.product_id = p.product_id
            WHERE ci.cart_user_id = ?
        ");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();

        $items = [];

        while ($row = $result->fetch_assoc()) {
            // Fetch sizes for this product
            $sizeStmt = $conn->prepare("SELECT size FROM product_size WHERE product_id = ?");
            $sizeStmt->bind_param("i", $row['product_id']);
            $sizeStmt->execute();
            $sizeResult = $sizeStmt->get_result();

            $sizes = [];
            while ($sizeRow = $sizeResult->fetch_assoc()) {
                $sizes[] = $sizeRow['size'];
            }
            $row['sizes'] = $sizes;

            $items[] = $row;
        }

        echo json_encode($items);
        break;

    case 'update_quantity':
        $id = (int)($_POST['id'] ?? 0);
        $quantity = (int)($_POST['quantity'] ?? 0);

        if ($id <= 0 || $quantity <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid input']);
            exit();
        }

        $stmt = $conn->prepare("SELECT price FROM cart_item WHERE id = ? AND cart_user_id = ?");
        $stmt->bind_param("ii", $id, $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $item = $result->fetch_assoc();

        if (!$item) {
            echo json_encode(['status' => 'error', 'message' => 'Item not found']);
            exit();
        }

        $total = $item['price'] * $quantity;

        $update = $conn->prepare("UPDATE cart_item SET quantity = ?, total_price = ? WHERE id = ? AND cart_user_id = ?");
        $update->bind_param("idii", $quantity, $total, $id, $userId);
        $success = $update->execute();

        echo json_encode(['status' => $success ? 'success' : 'error', 'newTotalPrice' => $total]);
        break;

    case 'update_size':
        $id = (int)($_POST['id'] ?? 0);
        $size = $_POST['size'] ?? '';

        if ($id <= 0 || !in_array($size, ['50ml', '100ml', '150ml'])) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid size or ID']);
            exit();
        }

        // Step 1: Get product_id from cart_item
        $stmt = $conn->prepare("SELECT product_id, quantity FROM cart_item WHERE id = ? AND cart_user_id = ?");
        $stmt->bind_param("ii", $id, $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $item = $result->fetch_assoc();

        if (!$item) {
            echo json_encode(['status' => 'error', 'message' => 'Cart item not found']);
            exit();
        }

        $productId = $item['product_id'];
        $quantity = $item['quantity'];

        // Step 2: Get the price from product_sizes table
        $stmt = $conn->prepare("SELECT price FROM product_size WHERE product_id = ? AND size = ?");
        $stmt->bind_param("is", $productId, $size);
        $stmt->execute();
        $result = $stmt->get_result();
        $sizeData = $result->fetch_assoc();

        if (!$sizeData) {
            echo json_encode(['status' => 'error', 'message' => 'Size not found for this product']);
            exit();
        }

        $price = (float)$sizeData['price'];
        $total = $price * $quantity;

        // Step 3: Update cart_item with new size, price, total
        $stmt = $conn->prepare("UPDATE cart_item SET size = ?, price = ?, total_price = ? WHERE id = ? AND cart_user_id = ?");
        $stmt->bind_param("sddii", $size, $price, $total, $id, $userId);
        $success = $stmt->execute();

        echo json_encode([
            'status' => $success ? 'success' : 'error',
            'newPrice' => $price,
            'newTotalPrice' => $total
        ]);
        break;

    case 'delete':
        $id = (int)($_POST['id'] ?? 0);

        $stmt = $conn->prepare("DELETE FROM cart_item WHERE id = ? AND cart_user_id = ?");
        $stmt->bind_param("ii", $id, $userId);
        $success = $stmt->execute();

        echo json_encode(['status' => $success ? 'success' : 'error']);
        break;

    case 'delete_selected':
        $ids = $_POST['ids'] ?? [];
        if (!is_array($ids) || empty($ids)) {
            echo json_encode(['status' => 'error', 'message' => 'No IDs provided']);
            exit();
        }

        $ids = array_map('intval', $ids);
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $types = str_repeat('i', count($ids)) . 'i'; // IDs + userId
        $params = array_merge($ids, [$userId]);

        $stmt = $conn->prepare("DELETE FROM cart_item WHERE id IN ($placeholders) AND cart_user_id = ?");
        $stmt->bind_param($types, ...$params);
        $success = $stmt->execute();

        echo json_encode(['status' => $success ? 'success' : 'error']);
        break;

    case 'add':
        // Get POST data
        $data = json_decode(file_get_contents('php://input'), true);

        if (!$data) {
            echo json_encode(['success' => false, 'message' => 'No data received']);
            exit;
        }

        $productId = $data['product_id'] ?? null;
        $productName = $data['product_name'] ?? null;
        $size = $data['size'] ?? null;
        $price = floatval($data['price'] ?? 0);
        $quantity = intval($data['quantity'] ?? 1);
        $totalPrice = $price * $quantity;

        // Validate input
        if (!$productId || !$productName || !$size || $price <= 0 || $quantity <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid input data']);
            exit;
        }

        // Check if item already exists in cart
        $stmt = $conn->prepare("SELECT id, quantity FROM cart_item WHERE cart_user_id = ? AND product_id = ? AND size = ?");
        $stmt->bind_param("iis", $userId, $productId, $size);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Update existing item
            $item = $result->fetch_assoc();
            $newQuantity = $item['quantity'] + $quantity;
            $newTotal = $price * $newQuantity;
            
            $update = $conn->prepare("UPDATE cart_item SET quantity = ?, total_price = ? WHERE id = ?");
            $update->bind_param("idi", $newQuantity, $newTotal, $item['id']);
            
            if ($update->execute()) {
                echo json_encode(['success' => true, 'message' => 'Cart updated successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to update cart']);
            }
        } else {
            // Insert new item
            $insert = $conn->prepare("INSERT INTO cart_item (cart_user_id, product_id, product_name, size, price, quantity, total_price) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $insert->bind_param("iissdid", $userId, $productId, $productName, $size, $price, $quantity, $totalPrice);
            
            if ($insert->execute()) {
                echo json_encode(['success' => true, 'message' => 'Item added to cart successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to add item to cart']);
            }
        }
        break;

    default:
        echo json_encode(['status' => 'error', 'message' => 'Unknown action']);
        break;
}

$conn->close();
?>
