<?php
include 'includes/config.php';

if (isset($_GET['id'])) {
    $productId = intval($_GET['id']);
    
    try {
        // Fetch product details
        $stmt = $conn->prepare("SELECT * FROM products WHERE product_id = ?");
        if (!$stmt) {
            throw new Exception("Failed to prepare product query: " . $conn->error);
        }
        $stmt->bind_param('i', $productId);
        $stmt->execute();
        $product = $stmt->get_result()->fetch_assoc();

        if (!$product) {
            throw new Exception("No product found with ID: $productId");
        }

        // Fetch product images
        $imageStmt = $conn->prepare("SELECT image FROM product_images WHERE product_id = ?");
        if (!$imageStmt) {
            throw new Exception("Failed to prepare image query: " . $conn->error);
        }
        $imageStmt->bind_param('i', $productId);
        $imageStmt->execute();
        $images = $imageStmt->get_result()->fetch_all(MYSQLI_ASSOC);

        // Fetch product reviews with customer full names
        $reviewStmt = $conn->prepare("
            SELECT 
                r.review_id, 
                r.review_content, 
                r.rating, 
                r.review_date, 
                CONCAT(c.fname, ' ', c.lname) AS customer_name 
            FROM 
                review r
            LEFT JOIN 
                customer c ON r.customer_id = c.customer_id
            WHERE 
                r.product_id = ?
        ");
        if (!$reviewStmt) {
            throw new Exception("Failed to prepare review query: " . $conn->error);
        }
        $reviewStmt->bind_param('i', $productId);
        $reviewStmt->execute();
        $reviews = $reviewStmt->get_result()->fetch_all(MYSQLI_ASSOC);

        // Return JSON response
        echo json_encode([
            'name' => $product['name'],
            'description' => $product['description'],
            'price' => $product['price'],
            'stock' => $product['stock'],
            'images' => array_column($images, 'image'),
            'reviews' => $reviews
        ]);
    } catch (Exception $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
} else {
    echo json_encode(['error' => 'Product ID not provided']);
}
?>
