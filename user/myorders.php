<?php
// Include the header
include '../includes/header.php';

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "<script>
            alert('You must be logged in to view your orders.');
            window.location.href = 'login.php';
          </script>";
    exit();
}

// Get the user ID from the session and cast it for safety
$customer_id = (int)$_SESSION['user_id'];

// Database connection
include '../includes/config.php'; // Ensure this path is correct

// SQL query to fetch orders with product names and reviews
$query = "
    SELECT 
        o.order_id, 
        o.order_date, 
        o.total, 
        o.status, 
        od.product_id, 
        p.name AS product_name,
        r.review_id
    FROM 
        orders o
    JOIN 
        order_details od ON o.order_id = od.order_id
    JOIN 
        products p ON od.product_id = p.product_id
    LEFT JOIN 
        review r ON r.product_id = od.product_id AND r.customer_id = o.customer_id
    WHERE 
        o.customer_id = (SELECT customer_id FROM customer WHERE user_id = ?)
    ORDER BY 
        o.order_date DESC
";

// Prepare the SQL statement
$stmt = $conn->prepare($query);
if (!$stmt) {
    die("Error preparing query: " . $conn->error);
}

// Bind the customer ID to the query
$stmt->bind_param("i", $customer_id);

// Execute the query
$stmt->execute();
$result = $stmt->get_result();

// Fetch all orders into an array
$orders = $result->fetch_all(MYSQLI_ASSOC);

// Close the statement and connection
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders</title>
    <style>
        /* General Styles */
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4fc;
            margin: 0;
            padding: 0;
            color: #333;
        }
        header {
            background: linear-gradient(90deg, #6a0dad, #4b6cb7);
            color: white;
            text-align: center;
            padding: 15px 0;
            font-size: 24px;
            font-weight: bold;
        }
        .orders-container {
            margin: 20px auto;
            padding: 20px;
            max-width: 1200px;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        /* Table Styles */
        .orders-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 16px;
        }
        .orders-table th, .orders-table td {
            padding: 12px 15px;
            text-align: left;
        }
        .orders-table th {
            background: linear-gradient(90deg, #6a0dad, #4b6cb7);
            color: white;
            border-bottom: 2px solid #4b6cb7;
        }
        .orders-table tr:nth-child(even) {
            background-color: #f9f7ff;
        }
        .orders-table tr:hover {
            background-color: #e6e1f7;
        }
        .orders-table td {
            border-bottom: 1px solid #ddd;
        }

        /* Links and Buttons */
        .orders-table td a {
            color: #4b6cb7;
            text-decoration: none;
            font-weight: bold;
        }
        .orders-table td a:hover {
            color: #6a0dad;
            text-decoration: underline;
        }
        .orders-table td button {
            background: linear-gradient(90deg, #6a0dad, #4b6cb7);
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 5px;
            font-size: 14px;
            cursor: pointer;
        }
        .orders-table td button:hover {
            background: linear-gradient(90deg, #4b6cb7, #6a0dad);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        /* No Orders Message */
        p {
            text-align: center;
            font-size: 18px;
            color: #6a0dad;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <header>
        <h1>Your Orders</h1>
    </header>
    <div class="orders-container">
        <?php if (count($orders) > 0): ?>
            <table class="orders-table">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Order Date</th>
                        <th>Product Name</th>
                        <th>Total Amount</th>
                        <th>Status</th>
                        <th>Review</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td><?= htmlspecialchars($order['order_id']) ?></td>
                            <td><?= htmlspecialchars($order['order_date']) ?></td>
                            <td><?= htmlspecialchars($order['product_name']) ?></td>
                            <td>₱<?= isset($order['total']) ? number_format($order['total'], 2) : '0.00' ?></td>
                            <td><?= htmlspecialchars($order['status']) ?></td>
                            <td>
                                <?php if ($order['status'] === 'completed' || $order['status'] === 'Delivered'): ?>
                                    <?php if (!empty($order['review_id'])): ?>
                                        <a href="review.php?order_id=<?= htmlspecialchars($order['order_id']) ?>&product_id=<?= htmlspecialchars($order['product_id']) ?>&review_id=<?= htmlspecialchars($order['review_id']) ?>">Update Review</a>
                                    <?php else: ?>
                                        <a href="review.php?order_id=<?= htmlspecialchars($order['order_id']) ?>&product_id=<?= htmlspecialchars($order['product_id']) ?>">Leave a Review</a>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <button onclick="alert('You can leave a review once the order is completed or delivered.')">Leave a Review</button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>You have no orders yet.</p>
        <?php endif; ?>
    </div>
</body>
</html>
