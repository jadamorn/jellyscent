<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Ensure we're outputting JSON even for errors
header('Content-Type: application/json');

try {
    $host = 'localhost';
    $user = 'root';
    $password = '';
    $dbname = 'jellyscent';

    $conn = new mysqli($host, $user, $password, $dbname);

    if ($conn->connect_error) {
        throw new Exception('Database connection failed: ' . $conn->connect_error);
    }

    // Get best selling products with their sizes
    $query = "
        SELECT 
            p.product_id,
            p.name,
            p.image,
            p.description,
            ps.id AS size_id,
            ps.size,
            FORMAT(ps.price, 2) as price,
            ps.stock_quantity
        FROM product p
        JOIN product_size ps ON p.product_id = ps.product_id
        WHERE p.best_selling = 1
        ORDER BY p.created_at DESC, ps.id ASC
    ";

    $result = $conn->query($query);

    if (!$result) {
        throw new Exception('Query failed: ' . $conn->error);
    }

    $products = [];
    while ($row = $result->fetch_assoc()) {
        $pid = $row['product_id'];

        if (!isset($products[$pid])) {
            $products[$pid] = [
                'id' => $pid,
                'name' => $row['name'],
                'image' => $row['image'],
                'description' => $row['description'],
                'sizes' => []
            ];
        }

        $products[$pid]['sizes'][] = [
            'size' => $row['size'],
            'price' => $row['price'],
            'stock' => $row['stock_quantity']
        ];
    }

    if (empty($products)) {
        // Return empty array instead of throwing error for no products
        echo json_encode([]);
    } else {
        echo json_encode(array_values($products));
    }

} catch (Exception $e) {
    // Return error in JSON format
    http_response_code(500);
    echo json_encode([
        'error' => true,
        'message' => $e->getMessage()
    ]);
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
?>
