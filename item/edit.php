<?php
session_start();
include('../includes/adminHeader.php');
include('../includes/config.php');

// Ensure the product ID is passed in the URL
if (!isset($_GET['id'])) {
    $_SESSION['message'] = "Product ID not provided.";
    header("Location: index.php");
    exit();
}

$productId = intval($_GET['id']); // Ensure the ID is an integer

// Fetch product details from the database
$sql = "SELECT * FROM products WHERE product_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $productId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    $_SESSION['message'] = "Product not found.";
    header("Location: index.php");
    exit();
}

$product = $result->fetch_assoc();

// Check if the form has been submitted
if (isset($_POST['submit'])) {
    // Get form data
    $productName = mysqli_real_escape_string($conn, $_POST['name']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $price = floatval($_POST['price']);
    $stock = intval($_POST['stock']);
    $image = $_FILES['image']['name'];
    
    // Handle main product image upload
    $imagePath = $product['image']; // Default to the old image if no new image is uploaded

    // Check if a new product image is uploaded
    if ($image) {
        // Validate file type (e.g., jpeg, png)
        $allowedTypes = ['image/jpeg', 'image/png'];
        $fileType = $_FILES['image']['type'];

        if (in_array($fileType, $allowedTypes)) {
            // Validate file size (max 5 MB)
            if ($_FILES['image']['size'] <= 5000000) { // 5 MB max size
                // Define the image directory
                $imageDir = '../item/images';

                // Check if the image directory is writable
                if (is_writable($imageDir)) {
                    // Sanitize and create a unique filename for the image
                    $imageFileName = time() . '_' . basename($image); // Prefix with timestamp to ensure uniqueness
                    $imagePath = $imageDir . '/' . $imageFileName;

                    // Move the uploaded file to the server directory
                    if (!move_uploaded_file($_FILES['image']['tmp_name'], $imagePath)) {
                        $_SESSION['message'] = "Error uploading the image.";
                        header("Location: edit.php?id=$productId");
                        exit();
                    }
                } else {
                    $_SESSION['message'] = "The directory is not writable.";
                    header("Location: edit.php?id=$productId");
                    exit();
                }
            } else {
                $_SESSION['message'] = "File size exceeds the maximum allowed size of 5 MB.";
                header("Location: edit.php?id=$productId");
                exit();
            }
        } else {
            $_SESSION['message'] = "Invalid file type. Only JPEG and PNG files are allowed.";
            header("Location: edit.php?id=$productId");
            exit();
        }
    }

    // Update product details in the database
    $updateSql = "UPDATE products SET 
                    name = ?, 
                    description = ?, 
                    price = ?, 
                    stock = ?, 
                    image = ? 
                  WHERE product_id = ?";
    $stmt = $conn->prepare($updateSql);
    $stmt->bind_param("ssdiss", $productName, $description, $price, $stock, $imagePath, $productId);

    if ($stmt->execute()) {
        // If product updated successfully, handle additional image uploads
        if (isset($_FILES['additional_image']) && !empty($_FILES['additional_image']['name'])) {
            $additionalImage = $_FILES['additional_image'];
            
            // Validate additional image
            $imageName = $additionalImage['name'];
            $fileTmpName = $additionalImage['tmp_name'];
            $allowedTypes = ['image/jpeg', 'image/png'];
            $fileType = $additionalImage['type'];

            if (in_array($fileType, $allowedTypes)) {
                // Validate file size (max 5 MB)
                if ($additionalImage['size'] <= 5000000) { // 5 MB max size
                    $imageFileName = time() . '_' . basename($imageName); // Prefix with timestamp to ensure uniqueness
                    $imagePath = '../item/images/' . $imageFileName;

                    // Move the uploaded file to the server directory
                    if (move_uploaded_file($fileTmpName, $imagePath)) {
                        // Insert new image record into product_images table
                        $insertImageSql = "INSERT INTO product_images (product_id, image) VALUES (?, ?)";
                        $stmt = $conn->prepare($insertImageSql);
                        $stmt->bind_param("is", $productId, $imagePath);
                        $stmt->execute();
                    } else {
                        $_SESSION['message'] = "Error uploading additional image.";
                        header("Location: edit.php?id=$productId");
                        exit();
                    }
                } else {
                    $_SESSION['message'] = "Additional image file size exceeds the maximum allowed size of 5 MB.";
                    header("Location: edit.php?id=$productId");
                    exit();
                }
            } else {
                $_SESSION['message'] = "Invalid file type for additional image. Only JPEG and PNG files are allowed.";
                header("Location: edit.php?id=$productId");
                exit();
            }
        }

        $_SESSION['message'] = "Product updated successfully.";
        header("Location: index.php");
        exit();
    } else {
        $_SESSION['message'] = "Error updating product.";
    }
}
?>

<body>
    <div class="container">
        <h2>Edit Product</h2>

        <!-- Display success or error messages -->
        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-info">
                <?= $_SESSION['message'] ?>
            </div>
            <?php unset($_SESSION['message']); ?>
        <?php endif; ?>

        <!-- Edit Product Form -->
        <form method="POST" action="" enctype="multipart/form-data">
            <div class="form-group">
                <label for="name">Product Name</label>
                <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($product['name'], ENT_QUOTES) ?>" required>

                <label for="description">Product Description</label>
                <textarea class="form-control" id="description" name="description" required><?= htmlspecialchars($product['description'], ENT_QUOTES) ?></textarea>

                <label for="price">Product Price</label>
                <input type="text" class="form-control" id="price" name="price" value="<?= htmlspecialchars($product['price'], ENT_QUOTES) ?>" required>

                <label for="stock">Product Stock</label>
                <input type="number" class="form-control" id="stock" name="stock" value="<?= htmlspecialchars($product['stock'], ENT_QUOTES) ?>" required>

                <label for="image">Product Thumbnail</label>
                <input type="file" class="form-control" id="image" name="image">
                <small>Leave empty to keep the current image</small><br>

                <!-- Display current image -->
                <img src="<?= htmlspecialchars($product['image'], ENT_QUOTES) ?>" width="150" height="150" alt="Current Product Image" /><br>

                <label for="additional_image">Upload Additional Image</label>
                <input type="file" class="form-control" id="additional_image" name="additional_image" multiple><br>

                <button type="submit" name="submit" class="btn btn-primary">Update Product</button>
            </div>
        </form>
    </div>

    <a href="index.php" class="btn btn-secondary">Cancel</a>
</body>
</html>
