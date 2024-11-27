<?php
session_start();
include('../includes/config.php');

if (isset($_GET['id'])) {
    $product_id = intval($_GET['id']); // Ensure the ID is an integer

    // Validate the product ID
    if ($product_id <= 0) {
        $_SESSION['error'] = "Invalid product ID.";
        header("Location: index.php");
        exit();
    }

    // Retrieve the product's image to delete it from the server
    $query = "SELECT image FROM products WHERE product_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $product = $result->fetch_assoc();
        $imagePath = $product['image'];

        // Delete the product from the database
        $deleteQuery = "DELETE FROM products WHERE product_id = ?";
        $deleteStmt = $conn->prepare($deleteQuery);
        $deleteStmt->bind_param("i", $product_id);

        if ($deleteStmt->execute()) {
            // Check if the image file exists and delete it
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }

            $_SESSION['success'] = "Product deleted successfully.";
            header("Location: index.php");
            exit();
        } else {
            $_SESSION['error'] = "Failed to delete the product. Please try again.";
            header("Location: index.php");
            exit();
        }
    } else {
        $_SESSION['error'] = "Product not found.";
        header("Location: index.php");
        exit();
    }
} else {
    $_SESSION['error'] = "No product ID provided.";
    header("Location: index.php");
    exit();
}
?>
