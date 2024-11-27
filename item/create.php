<?php
include('../includes/header.php');
include('../includes/config.php');

// Define the allowed image types and maximum file size
$allowedImageTypes = ['image/jpeg', 'image/png', 'image/gif'];
$maxFileSize = 5 * 1024 * 1024; // 5MB

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Initialize error variables
    $errors = [];

    // Validate product name
    if (empty($_POST['name'])) {
        $errors['name'] = 'Product name is required.';
    } else {
        $_SESSION['name'] = $_POST['name'];
    }

    // Validate description
    if (empty($_POST['description'])) {
        $errors['description'] = 'Product description is required.';
    } else {
        $_SESSION['description'] = $_POST['description'];
    }

    // Validate price
    if (empty($_POST['price']) || !is_numeric($_POST['price'])) {
        $errors['price'] = 'Please enter a valid price.';
    } else {
        $_SESSION['price'] = $_POST['price'];
    }

    // Validate stock
    if (empty($_POST['stock']) || !is_numeric($_POST['stock'])) {
        $errors['stock'] = 'Please enter a valid stock quantity.';
    } else {
        $_SESSION['stock'] = $_POST['stock'];
    }

    // Validate and handle main image upload
    $mainImagePath = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $imageType = $_FILES['image']['type'];
        $imageSize = $_FILES['image']['size'];

        if (!in_array($imageType, $allowedImageTypes)) {
            $errors['image'] = 'Only JPG, PNG, and GIF images are allowed.';
        }

        if ($imageSize > $maxFileSize) {
            $errors['image'] = 'The image file size should not exceed 5MB.';
        }

        if (empty($errors)) {
            $uploadDir = '../item/images/';
            $imageName = uniqid() . '_' . basename($_FILES['image']['name']);
            $mainImagePath = $uploadDir . $imageName;

            // Move the uploaded file to the designated directory
            if (!move_uploaded_file($_FILES['image']['tmp_name'], $mainImagePath)) {
                $errors['image'] = 'Failed to upload the image.';
            }
        }
    }

    // If no errors, proceed with inserting into the database
    if (empty($errors)) {
        // Insert into the products table
        $sql = "INSERT INTO products (name, description, price, stock, image) 
                VALUES (?, ?, ?, ?, ?)";

        if ($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, 'ssdss', $_POST['name'], $_POST['description'], $_POST['price'], $_POST['stock'], $mainImagePath);
            if (mysqli_stmt_execute($stmt)) {
                $productId = mysqli_insert_id($conn); // Get the ID of the newly created product

                // Handle additional images
                if (isset($_FILES['additional_images'])) {
                    foreach ($_FILES['additional_images']['name'] as $index => $additionalImageName) {
                        if ($_FILES['additional_images']['error'][$index] === UPLOAD_ERR_OK) {
                            $additionalImageType = $_FILES['additional_images']['type'][$index];
                            $additionalImageSize = $_FILES['additional_images']['size'][$index];

                            if (in_array($additionalImageType, $allowedImageTypes) && $additionalImageSize <= $maxFileSize) {
                                $uploadDir = '../item/images/';
                                $uniqueImageName = uniqid() . '_' . basename($additionalImageName);
                                $additionalImagePath = $uploadDir . $uniqueImageName;

                                if (move_uploaded_file($_FILES['additional_images']['tmp_name'][$index], $additionalImagePath)) {
                                    // Insert the additional image into the product_images table
                                    $sqlAdditionalImage = "INSERT INTO product_images (product_id, image) VALUES (?, ?)";
                                    if ($stmtAdditionalImage = mysqli_prepare($conn, $sqlAdditionalImage)) {
                                        mysqli_stmt_bind_param($stmtAdditionalImage, 'is', $productId, $additionalImagePath);
                                        mysqli_stmt_execute($stmtAdditionalImage);
                                        mysqli_stmt_close($stmtAdditionalImage);
                                    }
                                }
                            }
                        }
                    }
                }

                $_SESSION['message'] = 'Product created successfully!';
                header("Location: index.php");
                exit;
            } else {
                $errors['database'] = 'Failed to insert product into the database.';
            }
            mysqli_stmt_close($stmt);
        } else {
            $errors['database'] = 'Database connection error.';
        }
    }

    // Save errors to session to display them in the form
    $_SESSION['errors'] = $errors;
    header("Location: create.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Product</title>
    <link rel="stylesheet" href="../includes/style/styles.css">
</head>

<body>
    <div class="container">
        <h2>Create New Product</h2>

        <!-- Display session messages -->
        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-success">
                <?= $_SESSION['message'] ?>
            </div>
            <?php unset($_SESSION['message']); ?>
        <?php endif; ?>

        <!-- Display validation errors -->
        <?php if (isset($_SESSION['errors'])): ?>
            <div class="alert alert-danger">
                <ul>
                    <?php foreach ($_SESSION['errors'] as $error): ?>
                        <li><?= $error ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php unset($_SESSION['errors']); ?>
        <?php endif; ?>

        <!-- Product Creation Form -->
        <form method="POST" action="create.php" enctype="multipart/form-data">
            <div class="form-group">
                <label for="name">Product Name</label>
                <input type="text" class="form-control" id="name" name="name" value="<?= $_SESSION['name'] ?? '' ?>" required>

                <label for="description">Product Description</label>
                <textarea class="form-control" id="description" name="description" required><?= $_SESSION['description'] ?? '' ?></textarea>

                <label for="price">Product Price</label>
                <input type="text" class="form-control" id="price" name="price" value="<?= $_SESSION['price'] ?? '' ?>" required>

                <label for="stock">Product Stock</label>
                <input type="number" class="form-control" id="stock" name="stock" value="<?= $_SESSION['stock'] ?? '' ?>" required>

                <label for="image">Main Product Image</label>
                <input type="file" class="form-control" id="image" name="image">
                <small>Max file size: 5MB. Allowed types: JPG, PNG, GIF.</small>

                <label for="additional_images">Additional Product Images</label>
                <input type="file" class="form-control" id="additional_images" name="additional_images[]" multiple>
                <small>You can upload multiple images. Max file size: 5MB each. Allowed types: JPG, PNG, GIF.</small>

                <button type="submit" name="submit" class="btn btn-primary">Submit</button>
                <a href="index.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>

    <?php include('../includes/footer.php'); ?>
</body>
</html>
