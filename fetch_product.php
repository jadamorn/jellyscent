<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection
$host = 'localhost';
$user = 'root';
$password = '';
$dbname = 'jellyscent';

$conn = new mysqli($host, $user, $password, $dbname);
if ($conn->connect_error) {
    echo json_encode(["error" => "Connection failed: " . $conn->connect_error]);
    exit;
}

// Get query params
$best_selling = isset($_GET['best_selling']) && $_GET['best_selling'] == '1' ? 1 : 0;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;

// First get the products (limited and filtered)
if ($best_selling) {
    $stmt = $conn->prepare("SELECT * FROM product WHERE best_selling = 1 ORDER BY created_at DESC LIMIT ?");
} else {
    $stmt = $conn->prepare("SELECT * FROM product ORDER BY created_at DESC LIMIT ?");
}
$stmt->bind_param("i", $limit);
$stmt->execute();
$productResult = $stmt->get_result();

$productIds = [];
$products = [];

while ($row = $productResult->fetch_assoc()) {
    $pid = $row['product_id'];
    $productIds[] = $pid;
    $products[$pid] = [
        'product_id' => $pid,
        'name' => $row['name'],
        'description' => $row['description'],
        'best_selling' => (bool)$row['best_selling'],
        'image' => !empty($row['image']) ? '/' . ltrim($row['image'], '/') : null,
        'sizes' => []
    ];
}

if (count($productIds) > 0) {
    // Get sizes only for the filtered product IDs
    $placeholders = implode(',', array_fill(0, count($productIds), '?'));
    $types = str_repeat('i', count($productIds));

    $sizeSql = "SELECT product_id, size, price, stock_quantity FROM product_size WHERE product_id IN ($placeholders)";
    $sizeStmt = $conn->prepare($sizeSql);
    $sizeStmt->bind_param($types, ...$productIds);
    $sizeStmt->execute();
    $sizeResult = $sizeStmt->get_result();

    while ($sizeRow = $sizeResult->fetch_assoc()) {
        $pid = $sizeRow['product_id'];
        if (isset($products[$pid])) {
            $products[$pid]['sizes'][] = [
                'size' => $sizeRow['size'],
                'price' => (float)$sizeRow['price'],
                'stock' => (int)$sizeRow['stock_quantity']
            ];
        }
    }
}

echo json_encode(array_values($products));

$conn->close();
?>
