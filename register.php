<?php
require_once '../config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username     = trim($_POST['username']);
    $password     = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $email        = trim($_POST['email']);
    $role         = 'buyer'; // Must match enum lowercase 'buyer' as per your DB schema
    $first_name   = trim($_POST['first_name']);
    $last_name    = trim($_POST['last_name']);
    $comp_address = trim($_POST['address']);
    $brgy         = trim($_POST['barangay']);
    $city         = trim($_POST['city']);
    $region       = trim($_POST['region']);
    $zipcode      = (int) $_POST['zip'];
    $phone_no     = (int) $_POST['phone_number'];

    // Gender: must be one of the enum values, fallback to 'OTHER' if missing or invalid
    $allowed_genders = ['MALE', 'FEMALE', 'OTHER'];
    $gender = strtoupper(trim($_POST['gender'] ?? 'OTHER'));
    if (!in_array($gender, $allowed_genders)) {
        $gender = 'OTHER';
    }

    // Birthdate: construct datetime string or use default '2000-01-01 00:00:00'
    if (!empty($_POST['birth_year']) && !empty($_POST['birth_month']) && !empty($_POST['birth_date'])) {
        $birth_year  = (int) $_POST['birth_year'];
        $birth_month = (int) $_POST['birth_month'];
        $birth_date  = (int) $_POST['birth_date'];

        if (checkdate($birth_month, $birth_date, $birth_year)) {
            // datetime format with time at 00:00:00
            $birth_date_str = sprintf('%04d-%02d-%02d 00:00:00', $birth_year, $birth_month, $birth_date);
        } else {
            // invalid date fallback
            $birth_date_str = '2000-01-01 00:00:00';
        }
    } else {
        $birth_date_str = '2000-01-01 00:00:00'; // default birthdate
    }

    try {
        $sql = "INSERT INTO users (
            USERNAME, PASSWORD, EMAIL, ROLE,
            FIRST_NAME, LAST_NAME, BIRTH_DATE, GENDER,
            COMP_ADDRESS, BRGY, CITY, REGION, ZIPCODE, PHONE_NO, CREATED_AT
        ) VALUES (
            :username, :password, :email, :role,
            :first_name, :last_name, :birth_date, :gender,
            :comp_address, :brgy, :city, :region, :zipcode, :phone_no, NOW()
        )";

        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':username'     => $username,
            ':password'     => $password,
            ':email'        => $email,
            ':role'         => $role,
            ':first_name'   => $first_name,
            ':last_name'    => $last_name,
            ':birth_date'   => $birth_date_str,
            ':gender'       => $gender,
            ':comp_address' => $comp_address,
            ':brgy'         => $brgy,
            ':city'         => $city,
            ':region'       => $region,
            ':zipcode'      => $zipcode,
            ':phone_no'     => $phone_no,
        ]);

        header('Location: ../login.html?registered=success');
        exit();
    } catch (PDOException $e) {
        echo "Registration failed: " . $e->getMessage();
    }
} else {
    echo "Invalid request method.";
}
?>
