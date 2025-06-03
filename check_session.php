<?php
session_start();

header('Content-Type: application/json');

error_log("Checking session status");
error_log("Current session data: " . print_r($_SESSION, true));
error_log("Session ID: " . session_id());

echo json_encode([
    'success' => true,
    'session_active' => isset($_SESSION['user_id']),
    'session_data' => $_SESSION,
    'session_id' => session_id(),
    'php_session_path' => session_save_path(),
    'cookies' => $_COOKIE
]);
?> 