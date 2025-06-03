<?php
header('Content-Type: application/json');

$host = 'localhost';
$user = 'root';
$password = '';
$dbname = 'jellyscent';

$conn = new mysqli($host, $user, $password, $dbname);
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'error' => 'Database connection failed']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['product_id'])) {
    $product_id = intval($data['product_id']);

    // Delete from product_sizes first due to foreign key constraint
    $conn->query("DELETE FROM product_size WHERE product_id = $product_id");

    $stmt = $conn->prepare("DELETE FROM product WHERE product_id = ?");
    $stmt->bind_param('i', $product_id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Delete failed']);
    }

    $stmt->close();
} elseif (isset($data['product_ids']) && is_array($data['product_ids'])) {
    // Bulk delete
    $placeholders = implode(',', array_fill(0, count($data['product_ids']), '?'));
    $types = str_repeat('i', count($data['product_ids']));

    // Delete from product_sizes
    $stmt = $conn->prepare("DELETE FROM product_size WHERE product_id IN ($placeholders)");
    $stmt->bind_param($types, ...$data['product_ids']);
    $stmt->execute();
    $stmt->close();

    // Delete from products
    $stmt = $conn->prepare("DELETE FROM product WHERE product_id IN ($placeholders)");
    $stmt->bind_param($types, ...$data['product_ids']);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Bulk delete failed']);
    }

    $stmt->close();
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request']);
}

$conn->close();
?>
