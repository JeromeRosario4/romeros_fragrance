<?php
session_start();
include('../includes/config.php');

if (isset($_POST['submit'])) {
    // Retrieve and validate form data
    $name = isset($_POST['NAME']) ? trim($_POST['NAME']) : 'Unnamed Product';
    $desc = isset($_POST['DESCRIPTION']) ? trim($_POST['DESCRIPTION']) : '';
    $price = isset($_POST['PRICE']) ? trim($_POST['PRICE']) : '';
    $qty = isset($_POST['QUANTITY']) ? intval($_POST['QUANTITY']) : 0;

    // Validate product name
    if (empty($name)) {
        $_SESSION['nameError'] = "Please input a product name";
        header("Location: create.php");
        exit();
    }

    // Validate description
    if (empty($desc)) {
        $_SESSION['descError'] = "Please input a product description";
        header("Location: create.php");
        exit();
    }

    // Validate price
    if (empty($price) || !is_numeric($price)) {
        $_SESSION['priceError'] = "Invalid product price";
        header("Location: create.php");
        exit();
    }

    // Validate quantity
    if ($qty <= 0) {
        $_SESSION['qtyError'] = "Invalid product quantity";
        header("Location: create.php");
        exit();
    }

    // File upload logic
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
        $fileType = $_FILES['image']['type'];

        if (in_array($fileType, $allowedTypes)) {
            $imageName = uniqid('product_', true) . '.' . pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $target = 'images/' . $imageName;

            if (!move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
                $_SESSION['imageError'] = "Image upload failed.";
                header("Location: create.php");
                exit();
            }
        } else {
            $_SESSION['imageError'] = "Invalid file type. Please upload a JPEG or PNG image.";
            header("Location: create.php");
            exit();
        }
    } else {
        $_SESSION['imageError'] = "Image upload error.";
        header("Location: create.php");
        exit();
    }

    // Insert into products table
    $sql = "INSERT INTO products (name, description, price, image, stock) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssdsi", $name, $desc, $price, $target, $qty);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Product added successfully.";
        header("Location: index.php");
        exit();
    } else {
        $_SESSION['dbError'] = "Error inserting product: " . $conn->error;
        header("Location: create.php");
        exit();
    }
}
?>
