<?php
session_start();
require_once '../config.php';  // adjust path if needed

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

try {
    // Prepare and execute query with uppercase column names
    $stmt = $conn->prepare("SELECT USER_ID, USERNAME, FIRST_NAME, LAST_NAME, EMAIL, PHONE_NO, GENDER, BIRTH_DATE FROM users WHERE USER_ID = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $userData = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($userData) {
        // Map DB columns to JS keys expected
        $user = [
            'user_id'    => $userData['USER_ID'],
            'username'   => $userData['USERNAME'],
            'first_name' => $userData['FIRST_NAME'],
            'last_name'  => $userData['LAST_NAME'],
            'email'      => $userData['EMAIL'],
            'phone'      => $userData['PHONE_NO'],
            'gender'     => $userData['GENDER'],
            'birth_date' => $userData['BIRTH_DATE'],
        ];

        // Format birth date parts if birth_date is set
        if (!empty($user['birth_date'])) {
            $date = new DateTime($user['birth_date']);
            $user['birth_date_formatted'] = [
                'date'  => $date->format('d'),
                'month' => $date->format('m'),
                'year'  => $date->format('Y')
            ];
        }

        echo json_encode(['success' => true, 'user' => $user]);
    } else {
        echo json_encode(['success' => false, 'message' => 'User not found']);
    }
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred',
        'debug'   => $e->getMessage()
    ]);
}
