<?php
session_start();
header('Content-Type: application/json');


$host = "localhost";
    $user = "root";
    $pass = "";
    $dbname = "jellyscent";

    $conn = new mysqli($host, $user, $pass, $dbname);
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }


// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
  error_log("User not logged in. Session ID: " . session_id());  // Log session issue
  echo json_encode(['success' => false, 'message' => 'User not logged in']);
  exit;
}

// Get order data from the request body
$data = json_decode(file_get_contents('php://input'), true);

// Log the incoming data to debug
error_log(print_r($data, true));  // Log the data to the PHP error log

// Get user details from the session
$userId = $_SESSION['user_id'];
$email = $data['email'] ?? '';
$firstName = $data['firstName'] ?? '';
$lastName = $data['lastName'] ?? '';
$address = $data['address'] ?? '';
$barangay = $data['barangay'] ?? '';
$zipCode = $data['zipCode'] ?? '';
$city = $data['city'] ?? '';
$region = $data['region'] ?? '';
$mobile = $data['mobile'] ?? '';
$paymentMethod = strtolower($data['paymentMethod'] ?? 'gcash');  // Ensure payment method is lowercase

// Validate required fields
$missingFields = [];
if (!$email) $missingFields[] = 'email';
if (!$firstName) $missingFields[] = 'firstName';
if (!$lastName) $missingFields[] = 'lastName';
if (!$address) $missingFields[] = 'address';
if (!$barangay) $missingFields[] = 'barangay';
if (!$zipCode) $missingFields[] = 'zipCode';
if (!$city) $missingFields[] = 'city';
if (!$region) $missingFields[] = 'region';
if (!$mobile) $missingFields[] = 'mobile';

if (!empty($missingFields)) {
  error_log("Missing required fields: " . implode(', ', $missingFields));  // Log missing fields
  echo json_encode(['success' => false, 'message' => 'Missing required fields: ' . implode(', ', $missingFields)]);
  exit;
}

// Connect to the database
$conn = new mysqli('localhost', 'root', '', 'jellyscent');
if ($conn->connect_error) {
  error_log("Database connection failed: " . $conn->connect_error);  // Log DB connection error
  echo json_encode(['success' => false, 'message' => 'Database connection failed']);
  exit;
}

// Fetch the cart items for the user
$cartSql = "SELECT c.cart_item_id, p.product_id, p.name, p.price, c.size, c.quantity 
            FROM cart_item c JOIN product p ON c.product_id = p.product_id WHERE c.user_id = ?";
$stmt = $conn->prepare($cartSql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

$cartItems = [];
$total = 0;
while ($row = $result->fetch_assoc()) {
  $cartItems[] = $row;
  $total += $row['price'] * $row['quantity'];
}

// Add shipping fee if there are cart items
$shippingFee = count($cartItems) > 0 ? 50 : 0;
$total += $shippingFee; 

// Check if the cart is empty
if (count($cartItems) === 0) {
  echo json_encode(['success' => false, 'message' => 'Cart is empty']);
  exit;
}

// Generate order number and transaction ID
$orderNumber = 'JSC' . str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
$transactionId = 'TXN' . str_pad(rand(0, 999999999), 10, '0', STR_PAD_LEFT);
$orderDate = date('Y-m-d H:i:s');

// Insert order into the orders table
$orderInsert = $conn->prepare("INSERT INTO orders (user_id, order_number, transaction_id, payment_method, order_date, total, email, first_name, last_name, address, barangay, zip_code, city, region, mobile) 
                               VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
$orderInsert->bind_param("issssidssssssss", $userId, $orderNumber, $transactionId, $paymentMethod, $orderDate, $total, $email, $firstName, $lastName, $address, $barangay, $zipCode, $city, $region, $mobile);

// Check if the order insert was successful
if (!$orderInsert->execute()) {
  error_log("Error inserting order: " . $orderInsert->error);  // Log insert failure
  echo json_encode(['success' => false, 'message' => 'Failed to insert order']);
  exit;
}

// Get the ID of the newly inserted order
$orderId = $orderInsert->insert_id;

// Insert order items into the order_items table
$itemInsert = $conn->prepare("INSERT INTO order_items (order_id, product_id, size, quantity, price) VALUES (?, ?, ?, ?, ?)");
foreach ($cartItems as $item) {
  $itemInsert->bind_param("iisis", $orderId, $item['product_id'], $item['size'], $item['quantity'], $item['price']);
  if (!$itemInsert->execute()) {
    error_log("Error inserting order item: " . $itemInsert->error);  // Log item insert failure
  }
}

// Clear the user's cart after placing the order
$cartClear = $conn->prepare("DELETE FROM cart_item WHERE user_id = ?");
$cartClear->bind_param("i", $userId);
$cartClear->execute();

// Return a success response
echo json_encode(['success' => true]);

// Close database connection
$conn->close();
?>
