<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Ensure we're outputting JSON
header('Content-Type: application/json');

try {
    // Get POST data
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['product_id']) || !isset($data['best_selling'])) {
        throw new Exception('Missing required fields');
    }

    $productId = intval($data['product_id']);
    $bestSelling = $data['best_selling'] ? 1 : 0;

    // Database connection
    $host = 'localhost';
    $user = 'root';
    $password = '';
    $dbname = 'jellyscent';

    $conn = new mysqli($host, $user, $password, $dbname);

    if ($conn->connect_error) {
        throw new Exception('Database connection failed: ' . $conn->connect_error);
    }

    // Update the best_selling status
    $stmt = $conn->prepare("UPDATE product SET best_selling = ? WHERE product_id = ?");
    if (!$stmt) {
        throw new Exception('Failed to prepare statement: ' . $conn->error);
    }

    $stmt->bind_param("ii", $bestSelling, $productId);
    
    if (!$stmt->execute()) {
        throw new Exception('Failed to update best selling status: ' . $stmt->error);
    }

    if ($stmt->affected_rows === 0) {
        throw new Exception('No product found with ID: ' . $productId);
    }

    echo json_encode([
        'success' => true,
        'message' => 'Best selling status updated successfully'
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
} finally {
    if (isset($stmt)) {
        $stmt->close();
    }
    if (isset($conn)) {
        $conn->close();
    }
}
?> 