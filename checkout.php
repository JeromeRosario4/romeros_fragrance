<?php
session_start();
include('includes/header.php');
include('includes/config.php');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    die("Please log in to continue with checkout.");
}

// Check if cart is not empty
if (empty($_SESSION['cart_products'])) {
    die("Your cart is empty. Please add products to your cart before checking out.");
}

try {
    // Start a transaction
    mysqli_query($conn, 'START TRANSACTION');

    // Get the customer ID associated with the logged-in user
    $sql = "SELECT customer_id FROM customer WHERE user_id = ? LIMIT 1";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'i', $_SESSION['user_id']);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($result->num_rows === 0) {
        throw new Exception("Unable to find customer associated with the user.");
    }

    $customer = mysqli_fetch_assoc($result);
    $customer_id = $customer['customer_id'];

    // Calculate the total amount from the cart
    $total_amount = 0.00;
    foreach ($_SESSION['cart_products'] as $product_id => $item) {
        $total_amount += $item['quantity'] * $item['price'];
    }

    // Insert the order into 'orders' table
    $order_query = 'INSERT INTO orders (customer_id, order_date, total, status, shipping) VALUES (?, NOW(), ?, "pending", ?)';
    $order_stmt = mysqli_prepare($conn, $order_query);
    $shipping = 10.00;  // Define shipping cost
    mysqli_stmt_bind_param($order_stmt, 'idi', $customer_id, $total_amount, $shipping);
    mysqli_stmt_execute($order_stmt);
    $order_id = mysqli_insert_id($conn); // Get the actual order_id from 'orders' table

    // Insert order info into 'orderinfo' table
    $orderinfo_query = 'INSERT INTO orderinfo (customer_id, date_placed, date_shipped, shipping)   VALUES (?, NOW(), NOW(), ?)';

    $orderinfo_stmt = mysqli_prepare($conn, $orderinfo_query);
    mysqli_stmt_bind_param($orderinfo_stmt, 'id', $customer_id, $shipping);
    mysqli_stmt_execute($orderinfo_stmt);
    $orderinfo_id = mysqli_insert_id($conn); // Get the orderinfo_id from 'orderinfo' table

    // Prepare statements for adding order details and updating stock
    $orderline_query = 'INSERT INTO order_details (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)';
    $orderline_stmt = mysqli_prepare($conn, $orderline_query);
    mysqli_stmt_bind_param($orderline_stmt, 'iiid', $order_id, $product_id, $quantity, $price);

    $stock_update_query = 'UPDATE products SET stock = stock - ? WHERE product_id = ?';
    $stock_stmt = mysqli_prepare($conn, $stock_update_query);
    mysqli_stmt_bind_param($stock_stmt, 'ii', $quantity, $product_id);

    // Loop through cart products
    foreach ($_SESSION['cart_products'] as $product_id => $item) {
        // Get quantity and price from the cart
        $quantity = $item['quantity'];
        $price = $item['price'];

        // Check for missing product ID or quantity
        if (empty($product_id) || empty($quantity)) {
            throw new Exception("Missing product ID or quantity in cart.");
        }

        // Insert order line into 'order_detail'
        mysqli_stmt_execute($orderline_stmt);

        // Update product stock in 'products' table
        if (!mysqli_stmt_execute($stock_stmt)) {
            throw new Exception("Failed to update stock for product ID: $product_id");
        }
    }

    // Commit the transaction
    mysqli_commit($conn);

    // Clear cart
    unset($_SESSION['cart_products']);

    // Success message
    echo "<h2>Checkout successful!</h2>";
    echo "<p>Your order has been placed. Thank you for shopping with us!</p>";
    echo "<a href='index.php'>Continue Shopping</a>";
} catch (Exception $e) {
    // Rollback transaction on error
    mysqli_rollback($conn);

    // Display error
    echo "<h2>Checkout failed</h2>";
    echo "<p>Error: " . $e->getMessage() . "</p>";
    echo "<a href='viewcart.php'>Return to Cart</a>";
} finally {
    // Close statements
    if (isset($stmt)) mysqli_stmt_close($stmt);
    if (isset($order_stmt)) mysqli_stmt_close($order_stmt);
    if (isset($orderinfo_stmt)) mysqli_stmt_close($orderinfo_stmt);
    if (isset($orderline_stmt)) mysqli_stmt_close($orderline_stmt);
    if (isset($stock_stmt)) mysqli_stmt_close($stock_stmt);

    // Close connection
    mysqli_close($conn);
}
    
include('includes/footer.php');
?>