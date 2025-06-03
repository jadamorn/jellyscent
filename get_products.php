<?php
$host = "localhost";
$user = "root";
$password = "";
$database = "jellyscent";

$conn = new mysqli($host, $user, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT p.product_id, p.name, p.description, p.image, ps.id AS size_id, ps.size, ps.price, ps.stock_quantity
        FROM product p
        JOIN product_size ps ON p.product_id = ps.product_id
        ORDER BY p.created_at DESC, ps.id ASC";

$result = $conn->query($sql);
$products = [];

while ($row = $result->fetch_assoc()) {
    $pid = $row['product_id'];
    if (!isset($products[$pid])) {
        $products[$pid] = [
            'product_id' => $pid,
            'name' => $row['name'],
            'description' => $row['description'],
            'image' => $row['image'],
            'sizes' => []
        ];
    }

    $products[$pid]['sizes'][] = [
        'size_id' => $row['size_id'],
        'size' => $row['size'],
        'price' => floatval($row['price']),
        'stock_quantity' => intval($row['stock_quantity'])
    ];
}

header('Content-Type: application/json');
echo json_encode(array_values($products));
$conn->close();
?>
