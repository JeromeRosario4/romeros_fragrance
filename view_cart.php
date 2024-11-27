<?php
session_start();
include 'includes/header.php';
include 'includes/config.php';

// Check if the cart is empty
if (!isset($_SESSION["cart_products"]) || empty($_SESSION["cart_products"])) {
    echo "<h1 align='center'>View Cart</h1>";
    echo "<p>Your cart is empty. <a href='index.php'>Continue shopping</a></p>";
    include('./includes/footer.php');
    exit();
}

?>

<h1 align="center">View Cart</h1>
<div class="cart-view-table-back">
    <form method="POST" action="cart_update.php">
        <table class="cart-table">
            <thead>
                <tr>
                    <th>Quantity</th>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Price</th>
                    <th>Total</th>
                    <th>Remove</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $total = 0; // Initialize total
                $b = 0; // Variable for zebra stripe rows

                // Loop through the cart items
                foreach ($_SESSION["cart_products"] as $product_id => $cart_item) {
                    if (isset($cart_item["name"], $cart_item["quantity"], $cart_item["price"], $cart_item["description"], $cart_item["image"])) {
                        $product_name = htmlspecialchars($cart_item["name"]);
                        $product_qty = intval($cart_item["quantity"]);
                        $product_price = floatval($cart_item["price"]);
                        $product_description = htmlspecialchars($cart_item["description"]);
                        $product_image = htmlspecialchars($cart_item["image"]);
                        $subtotal = $product_price * $product_qty;

                        // Zebra stripe effect for rows
                        $bg_color = ($b++ % 2 === 1) ? 'odd' : 'even';

                        // Render table row
                        echo '<tr class="' . $bg_color . '">';
                        echo '<td><input type="number" name="product_qty[' . htmlspecialchars($product_id) . ']" value="' . $product_qty . '" min="1" max="' . $cart_item['stock'] . '" /></td>';
                        echo '<td>' . $product_name . '</td>';
                        echo '<td>' . $product_description . '</td>';
                        echo '<td>₱' . number_format($product_price, 2) . '</td>';
                        echo '<td>₱' . number_format($subtotal, 2) . '</td>';
                        echo '<td><input type="checkbox" name="remove_code[]" value="' . htmlspecialchars($product_id) . '" /></td>';
                        echo '</tr>';

                        // Update total
                        $total += $subtotal;
                    } else {
                        echo "<p>Error: Missing product details for product ID $product_id.</p>";
                    }
                }
                ?>
                <tr>
                    <td colspan="6" align="right">
                        <strong>Amount Payable:</strong> ₱<?= number_format($total, 2); ?>
                    </td>
                </tr>
                <tr>
                    <td colspan="6" class="action-buttons">
                        <a href="index.php" class="button">Add More Items</a>
                        <button type="submit" class="button">Update</button>
                        <a href="checkout.php" class="button">Checkout</a>
                    </td>
                </tr>
            </tbody>
        </table>
    </form>
</div>

<?php
include('./includes/footer.php');
?>
