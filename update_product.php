<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 0); // Prevent PHP errors from corrupting JSON output

$host = 'localhost';
$user = 'root';
$password = '';
$dbname = 'jellyscent';

try {
    // Get and decode the input data
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON input: ' . json_last_error_msg());
    }

    // Validate required fields
    if (!isset($data['product_id'], $data['name'], $data['description'], $data['stocks'])) {
        throw new Exception('Missing required fields');
    }

    $product_id = (int)$data['product_id'];
    $name = trim($data['name']);
    $description = trim($data['description']);
    $stocks = $data['stocks'];

    if ($product_id <= 0 || empty($name)) {
        throw new Exception('Invalid product ID or name');
    }

    // Connect to database
    $conn = new mysqli($host, $user, $password, $dbname);
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    // Start transaction
    $conn->begin_transaction();

    try {
        // Update product details
        $stmt = $conn->prepare("UPDATE product SET name = ?, description = ?, updated_at = NOW() WHERE product_id = ?");
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }

        $stmt->bind_param("ssi", $name, $description, $product_id);
        if (!$stmt->execute()) {
            throw new Exception("Failed to update product: " . $stmt->error);
        }
        $stmt->close();

        // Update stocks
        $stmt = $conn->prepare("UPDATE product_size SET stock_quantity = ? WHERE product_id = ? AND size = ?");
        if (!$stmt) {
            throw new Exception("Prepare failed for stock update: " . $conn->error);
        }

        foreach ($stocks as $size => $quantity) {
            $quantity = (int)$quantity;
            if ($quantity < 0) {
                throw new Exception("Invalid stock quantity for size $size");
            }

            $stmt->bind_param("iis", $quantity, $product_id, $size);
            if (!$stmt->execute()) {
                throw new Exception("Failed to update stock for size $size: " . $stmt->error);
            }
        }
        $stmt->close();

        // Commit transaction
        $conn->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Product updated successfully'
        ]);

    } catch (Exception $e) {
        $conn->rollback();
        throw $e;
    }

} catch (Exception $e) {
    error_log("Update error: " . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
?>
