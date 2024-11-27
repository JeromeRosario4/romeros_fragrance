<?php
session_start();
include 'includes/config.php';

// Update product quantities in the cart
if (isset($_POST['product_qty'])) {
    foreach ($_POST['product_qty'] as $product_id => $quantity) {
        // Ensure the quantity is within bounds
        if (isset($_SESSION['cart_products'][$product_id])) {
            $quantity = max(1, min(intval($quantity), $_SESSION['cart_products'][$product_id]['stock']));

            // Update the quantity in the session
            $_SESSION['cart_products'][$product_id]['quantity'] = $quantity;
        }
    }
}

// Remove products from the cart
if (isset($_POST['remove_code'])) {
    foreach ($_POST['remove_code'] as $product_id) {
        unset($_SESSION['cart_products'][$product_id]); // Remove the product from the session
    }
}

header("Location: view_cart.php"); // Redirect back to the cart
exit();
?>
