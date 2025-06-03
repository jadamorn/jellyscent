<?php
require_once '../config.php';

$conn = new mysqli($host, $user, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $sizes = isset($_POST['sizes']) ? $_POST['sizes'] : [];

    if (empty($name) || empty($description) || empty($sizes)) {
        die('Please fill in all required fields and select at least one size.');
    }

    // Handle image upload
    if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        die('Error uploading image.');
    }

    $imageTmpPath = $_FILES['image']['tmp_name'];
    $imageName = basename($_FILES['image']['name']);
    $ext = pathinfo($imageName, PATHINFO_EXTENSION);
    $newImageName = uniqid('product_', true) . '.' . $ext;
    $uploadDir = 'uploads/';
    $destPath = $uploadDir . $newImageName;

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    if (!move_uploaded_file($imageTmpPath, $destPath)) {
        die('Failed to move uploaded file.');
    }

    // Insert product into products table
    $stmt = $conn->prepare("INSERT INTO product (name, description, image) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $name, $description, $newImageName);

    if ($stmt->execute()) {
        $product_id = $stmt->insert_id;

        // Insert each size's details
        foreach ($sizes as $size) {
            $priceKey = "price_$size";
            $stockKey = "stock_$size";

            if (isset($_POST[$priceKey]) && isset($_POST[$stockKey])) {
                $price = floatval($_POST[$priceKey]);
                $stock_quantity = intval($_POST[$stockKey]);

                $sizeStmt = $conn->prepare("INSERT INTO product_size (product_id, size, price, stock_quantity) VALUES (?, ?, ?, ?)");
                $sizeStmt->bind_param("isdi", $product_id, $size, $price, $stock_quantity);
                $sizeStmt->execute();
                $sizeStmt->close();
            }
        }

        echo "✅ Product added successfully! <a href='../admin/products.html'>Back to Product List</a>";
    } else {
        echo "❌ Error: " . $stmt->error;
    }

    $stmt->close();
}

$conn->close();
?>
