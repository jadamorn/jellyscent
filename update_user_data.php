<?php
// Error reporting - comment these in production
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once '../config.php';
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

// Get JSON data
$data = json_decode(file_get_contents('php://input'), true);

// Validate required fields
$required_fields = ['username', 'first_name', 'last_name', 'email'];
foreach ($required_fields as $field) {
    if (empty($data[$field])) {
        echo json_encode(['success' => false, 'message' => ucfirst(str_replace('_', ' ', $field)) . ' is required']);
        exit;
    }
}

try {
    // Check if username is already taken by another user
    $stmt = $conn->prepare("SELECT USER_ID FROM users WHERE USERNAME = ? AND USER_ID != ?");
    $stmt->execute([$data['username'], $_SESSION['user_id']]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Username is already taken']);
        exit;
    }

    // Format birth date if provided
    $birth_date = null;
    if (!empty($data['birth_year']) && !empty($data['birth_month']) && !empty($data['birth_date'])) {
        $birth_date = sprintf('%04d-%02d-%02d 00:00:00',  // add time portion for datetime
            intval($data['birth_year']), 
            intval($data['birth_month']), 
            intval($data['birth_date'])
        );
    }

    // Update user data with correct column names
    $stmt = $conn->prepare("
        UPDATE users 
        SET USERNAME = :username,
            FIRST_NAME = :first_name,
            LAST_NAME = :last_name,
            EMAIL = :email,
            PHONE_NO = :phone,
            GENDER = :gender,
            BIRTH_DATE = :birth_date
        WHERE USER_ID = :user_id
    ");

    $stmt->execute([
        ':username' => $data['username'],
        ':first_name' => $data['first_name'],
        ':last_name' => $data['last_name'],
        ':email' => $data['email'],
        ':phone' => $data['phone'] ?? null,
        ':gender' => $data['gender'] ?? null,
        ':birth_date' => $birth_date,
        ':user_id' => $_SESSION['user_id']
    ]);

    echo json_encode([
        'success' => true, 
        'message' => 'Profile updated successfully'
    ]);

} catch(PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred',
        'debug' => $e->getMessage()
    ]);
}
?>
