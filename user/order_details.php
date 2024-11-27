<?php
// Start session
session_start();

// Include necessary files
include 'includes/config.php';
include 'includes/header.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('You must be logged in to view your order details.'); window.location.href='login.php';</script>";
    exit();
}

// Get the order_id from the URL
if (isset($_GET['order_id'])) {
    $order_id = $_GET['order_id'];

    // Fetch order details from the database
    $stmt = $conn->prepare("SELECT * FROM order_details WHERE order_id = ?");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $order_details = $result->fetch_all(MYSQLI_ASSOC);

    // Fetch order summary (total amount, date, etc.)
    $stmt_order = $conn->prepare("SELECT * FROM orders WHERE order_id = ?");
    $stmt_order->bind_param("i", $order_id);
    $stmt_order->execute();
    $order = $stmt_order->get_result()->fetch_assoc();
} else {
    echo "<script>alert('Invalid Order ID.'); window.location.href='myorders.php';</script>";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Details</title>
    <link rel="stylesheet" href="includes/style/style.css">
</head>
<body>

    <header>
        <h1>Order Details</h1>
    </header>

    <div class="order-summary">
        <p><strong>Order ID:</strong> <?= htmlspecialchars($order['order_id']) ?></p>
        <p><strong>Order Date:</strong> <?= htmlspecialchars($order['order_date']) ?></p>
        <p><strong>Total Amount:</strong> ₱<?= number_format($order['total_amount'], 2) ?></p>
        <p><strong>Status:</strong> <?= htmlspecialchars($order['status']) ?></p>
    </div>

    <div class="order-items">
        <h2>Items Ordered</h2>
        <?php if (count($order_details) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Product Name</th>
                        <th>Quantity</th>
                        <th>Price per Unit</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($order_details as $detail): ?>
                        <tr>
                            <td><?= htmlspecialchars($detail['product_name']) ?></td>
                            <td><?= htmlspecialchars($detail['quantity']) ?></td>
                            <td>₱<?= number_format($detail['price_per_unit'], 2) ?></td>
                            <td>₱<?= number_format($detail['quantity'] * $detail['price_per_unit'], 2) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No items in this order.</p>
        <?php endif; ?>
    </div>

</body>
</html>

<?php
include 'includes/footer.php';
?>
