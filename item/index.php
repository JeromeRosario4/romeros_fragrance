<?php
session_start();
include('../includes/adminHeader.php');
include('../includes/config.php');

// Check if a search term is provided
if (isset($_GET['search'])) {
    $keyword = strtolower(trim($_GET['search']));
} else {
    $keyword = '';
}

// Prepare the SQL query based on the search keyword
if ($keyword) {
    $sql = "SELECT * FROM products WHERE name LIKE ? OR description LIKE ?";
    $stmt = $conn->prepare($sql);
    $searchTerm = "%{$keyword}%";
    $stmt->bind_param("ss", $searchTerm, $searchTerm);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $sql = "SELECT * FROM products";
    $result = $conn->query($sql);
}

// Get the total number of items
$itemCount = $result->num_rows;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
    body {
        background: linear-gradient(135deg, #b3d1f2, #a3a1f7, #c3a6e5); /* Diagonal gradient */
        color: #000; /* Dark text for contrast */
        min-height: 100vh;
        margin: 0;
        display: flex;
        flex-direction: column;
    }
    .main-container {
        background: #fff; /* White container background */
        margin: 30px auto;
        padding: 20px;
        border-radius: 12px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        max-width: 1200px;
    }
    .container-title {
        text-align: center;
        margin-bottom: 20px;
    }
    .card {
        background-color: rgba(255, 255, 255, 0.9); /* Slight transparency for card background */
        border: none;
        border-radius: 8px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }
    .card img {
        border-top-left-radius: 8px;
        border-top-right-radius: 8px;
        max-height: 200px;
        width: 100%; /* Ensures the image takes up full width */
        object-fit: contain; /* Makes sure the full image is visible without cropping */
    }
    .btn-primary {
    background-color: #6f42c1; /* Purple background */
    border-color: #6f42c1; /* Purple border */
    color: white; /* White text */
}

.btn-primary:hover {
    background-color: #00008B; /* Blue background on hover */
    border-color: #00008B; /* Blue border on hover */
    color: white; /* White text on hover */
}

    .btn-outline-primary {
        color: #6f42c1;
        border-color: #6f42c1;
    }
    .btn-outline-primary:hover {
        background-color: #6f42c1;
        color: #fff;
    }
    .btn-outline-danger {
        color: #dc3545;
        border-color: #dc3545;
    }
    .btn-outline-danger:hover {
        background-color: #dc3545;
        color: #fff;
    }

    .btn-outline-light {
    background-color: #6f42c1; /* Purple background */
    color: black; /* Black text/icon */
    border: none; /* Remove border */
}

.btn-outline-light:hover {
    background-color: #00008B; /* Blue background on hover */
    color: white; /* White text/icon */
    border: none; /* Keep no border on hover */
}

.btn-outline-light i {
    color: black; /* Black icon */
}

.btn-outline-light:hover i {
    color: white; /* White icon on hover */
}

    
</style>

</head>
<body>
<div class="main-container">
    <h2 class="container-title">Number of items: <?= $itemCount ?> </h2>
    <a href="create.php" class="btn btn-primary btn-lg mb-4" role="button">Add Products</a>
    <div class="row">
        <?php
        // Check if there are any results
        if ($itemCount > 0) {
            // Loop through and display each product
            while ($row = $result->fetch_assoc()) {
        ?>
        <div class="col-md-4">
            <div class="card mb-4">
                <img src="<?= $row['image'] ?>" class="card-img-top" alt="Product Image">
                <div class="card-body">
                    <h5 class="card-title"><?= htmlspecialchars($row['name']) ?></h5>
                    <p class="card-text"><?= htmlspecialchars($row['description']) ?></p>
                    <p class="card-text"><strong>Price:</strong> <?= htmlspecialchars($row['price']) ?></p>
                    <p class="card-text"><strong>Stock:</strong> <?= htmlspecialchars($row['stock']) ?></p>
                    <div class="d-flex justify-content-between">
                        <a href="edit.php?id=<?= $row['product_id'] ?>" class="btn btn-outline-primary btn-sm"><i class="fa-regular fa-pen-to-square"></i> Edit</a>
                        <a href="delete.php?id=<?= $row['product_id'] ?>" class="btn btn-outline-danger btn-sm"><i class="fa-solid fa-trash"></i> Delete</a>
                    </div>
                </div>
            </div>
        </div>
        <?php
            }
        } else {
            echo "<p>No products found.</p>";
        }
        ?>
    </div>
</div>
</body>
</html>
