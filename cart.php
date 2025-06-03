<?php
session_start();
header('Content-Type: application/json');

// Database connection
$conn = new mysqli('localhost', 'root', '', 'jellyscent');
if ($conn->connect_error) {
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'User not logged in']);
    exit;
}

$userId = $_SESSION['user_id'];
$action = $_REQUEST['action'] ?? '';

// Switch for actions
switch ($action) {
    case 'get':
        // Get all cart items for the user, including 'is_selected' field
        $stmt = $conn->prepare("SELECT ci.*, p.name, ci.is_selected FROM cart_item ci JOIN product p ON ci.product_id = p.product_id WHERE ci.cart_user_id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();

        $items = [];
        while ($row = $result->fetch_assoc()) {
            // Get sizes for the product
            $sizeStmt = $conn->prepare("SELECT size FROM product_size WHERE product_id = ?");
            $sizeStmt->bind_param("i", $row['product_id']);
            $sizeStmt->execute();
            $sizeResult = $sizeStmt->get_result();

            $sizes = [];
            while ($sizeRow = $sizeResult->fetch_assoc()) {
                $sizes[] = $sizeRow['size'];
            }
            $row['sizes'] = $sizes;

            // Add 'is_selected' to the item
            $items[] = $row;
        }

        echo json_encode($items);
        break;

    case 'update_selection':
        $id = (int)($_POST['id'] ?? 0);
        $isSelected = (int)($_POST['is_selected'] ?? 0); // 0 or 1 for unselected or selected

        if ($id <= 0 || !in_array($isSelected, [0, 1])) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid input']);
            exit();
        }

        // Update the is_selected field
        $stmt = $conn->prepare("UPDATE cart_item SET is_selected = ? WHERE id = ? AND cart_user_id = ?");
        $stmt->bind_param("iii", $isSelected, $id, $userId);
        $success = $stmt->execute();

        if ($success) {
            echo json_encode(['status' => 'success', 'is_selected' => $isSelected]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to update selection']);
        }
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

    case 'delete':
        $id = (int)($_POST['id'] ?? 0);

        $stmt = $conn->prepare("DELETE FROM cart_item WHERE id = ? AND cart_user_id = ?");
        $stmt->bind_param("ii", $id, $userId);
        $success = $stmt->execute();

        echo json_encode(['status' => $success ? 'success' : 'error']);
        break;

    case 'update_selection_all':
        $isSelected = (int)($_POST['is_selected'] ?? 0);
        if (!in_array($isSelected, [0, 1])) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid input']);
            exit();
        }
        $stmt = $conn->prepare("UPDATE cart_item SET is_selected = ? WHERE cart_user_id = ?");
        $stmt->bind_param("ii", $isSelected, $userId);
        $success = $stmt->execute();
        if ($success) {
            echo json_encode(['status' => 'success', 'is_selected' => $isSelected]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to update selection for all items']);
        }
        break;

    default:
        echo json_encode(['status' => 'error', 'message' => 'Unknown action']);
        break;
}
?>
