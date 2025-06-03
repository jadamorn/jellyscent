<?php
// Prevent any output before headers
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', 'php_errors.log');

header('Content-Type: application/json');
session_start();

try {
    // Database connection
    $host = "localhost";
    $user = "root";
    $pass = "";
    $dbname = "jellyscent";

    $conn = new mysqli($host, $user, $pass, $dbname);
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        if (!isset($_POST['email']) || !isset($_POST['password'])) {
            throw new Exception("Email and password are required");
        }

        $email = $_POST['email'];
        $password = $_POST['password'];

        $sql = "SELECT * FROM users WHERE EMAIL = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['PASSWORD'])) {
 // Direct comparison since password is not hashed
                $_SESSION['user_id'] = $user['USER_ID'];
                $_SESSION['username'] = $user['USERNAME'];
                $_SESSION['email'] = $user['EMAIL'];
                $_SESSION['role'] = $user['ROLE'];

                echo json_encode([
                    'success' => true,
                    'role' => $user['ROLE']
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Invalid email or password'
                ]);
            }
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'User not found'
            ]);
        }
    } else {
        if (isset($_SESSION['user_id'])) {
            echo json_encode([
                'success' => true,
                'message' => 'Already logged in',
                'role' => $_SESSION['role']
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Not logged in'
            ]);
        }
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
