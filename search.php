<?php
session_start();
include('./includes/header.php');
include('./includes/config.php');

// Check if the 'search' query parameter exists
if (isset($_GET['search']) && !empty(trim($_GET['search']))) {
    $keyword = strtolower(trim($_GET['search']));

    // Prepare the SQL query with LIKE for a case-insensitive search
    $sql = "SELECT product_id, name, description, image, price FROM products WHERE LOWER(name) LIKE ? ORDER BY product_id DESC";
    $stmt = $conn->prepare($sql);

    // Check if the SQL query preparation was successful
    if ($stmt === false) {
        die("Error preparing SQL query: " . $conn->error);
    }

    $searchTerm = "%{$keyword}%";
    $stmt->bind_param("s", $searchTerm);
    $stmt->execute();
    $results = $stmt->get_result();

    if ($results->num_rows > 0) {
        $products_item = '<ul class="products">';

        // Fetch results and output HTML
        while ($row = $results->fetch_assoc()) {
            $products_item .= <<<EOT
            <li class="product">
                <form method="POST" action="cart_update.php">
                    <div class="product-content">
                        <h3>{$row['name']}</h3>
                        <div class="product-thumb"><img src="./item/{$row['image']}" width="50px" height="50px"></div>
                        <div class="product-info">
                            <p>Description: {$row['description']}</p>
                            <p>Price: ₱{$row['price']}</p>
                            <fieldset>
                                <label>
                                    <span>Quantity</span>
                                    <input type="number" size="2" maxlength="2" name="item_qty" value="1" />
                                </label>
                            </fieldset>
                            <input type="hidden" name="item_id" value="{$row['product_id']}" />
                            <input type="hidden" name="type" value="add" />
                            <div align="center"><button type="submit" class="add_to_cart">Add</button></div>
                        </div>
                    </div>
                </form>
            </li>
EOT;
        }

        $products_item .= '</ul>';
        echo $products_item;
    } else {
        echo "<p>No products found for '$keyword'</p>";
    }

    // Close the statement
    $stmt->close();
} else {
    // Display a message if no search term was provided
    echo "<p>Please enter a search term.</p>";
}
?>
