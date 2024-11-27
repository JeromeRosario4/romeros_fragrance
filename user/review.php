<?php
session_start();
include_once '../includes/config.php';  // Adjusted file path
include '../includes/header.php';

// Redirect to login if customer is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Define a function to filter profanities
function filterProfanity($text) {
    $profanities = ['gago', 'puta', 'tanginamo', 'fuck you', 'shit', 'putanginamo'];
    foreach ($profanities as $profanity) {
        $text = preg_replace("/\b" . preg_quote($profanity, '/') . "\b/i", "*****", $text);
    }
    return $text;
}

// Get the order_id, product_id, and review_id from the URL
$order_id = isset($_GET['order_id']) ? (int)$_GET['order_id'] : null;
$product_id = isset($_GET['product_id']) ? (int)$_GET['product_id'] : null;
$review_id = isset($_GET['review_id']) ? (int)$_GET['review_id'] : null;

// Redirect back to orders page if required params are missing
if (!$order_id || !$product_id) {
    header("Location: myorders.php");
    exit;
}

// Fetch the customer_id based on the session user_id
$stmt = $conn->prepare("SELECT customer_id FROM customer WHERE user_id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$customer_data = $result->fetch_assoc();

if (!$customer_data) {
    die("Error: No matching customer found for the logged-in user.");
}
$customer_id = $customer_data['customer_id'];
$stmt->close();

// Check if the review exists for updating
if ($review_id) {
    $stmt = $conn->prepare("SELECT review_content, rating FROM review WHERE review_id = ? AND customer_id = ?");
    $stmt->bind_param("ii", $review_id, $customer_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $existing_review = $result->fetch_assoc();
    $stmt->close();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $review_content = $_POST['review_content'];
    $rating = (int)$_POST['rating'];

    // Filter profanity in the review content
    $review_content = filterProfanity($review_content);

    // Validate rating range
    if ($rating < 1 || $rating > 5) {
        $error_message = "Invalid rating. Please provide a rating between 1 and 5.";
    } else {
        // Insert or update the review
        if ($review_id) {
            // Update the existing review
            $stmt = $conn->prepare("UPDATE review SET review_content = ?, rating = ? WHERE review_id = ? AND customer_id = ?");
            $stmt->bind_param("siii", $review_content, $rating, $review_id, $customer_id);
        } else {
            // Insert a new review
            $stmt = $conn->prepare("INSERT INTO review (product_id, customer_id, review_content, rating) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("iisi", $product_id, $customer_id, $review_content, $rating);
        }

        if (!$stmt->execute()) {
            $error_message = "Error saving review: " . $stmt->error;
        } else {
            $success_message = $review_id ? "Review updated successfully!" : "Review submitted successfully!";
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($review_id) ? "Update Your Review" : "Leave a Review" ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background: white;
            color: #fff;
        }

        header {
            text-align: center;
            padding: 1em 0;
        }

        h1 {
            margin: 0;
            font-size: 2rem;
            color: #fff;
        }

        .review-container {
            max-width: 600px;
            margin: 2em auto;
            padding: 2em;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            color: #333;
            border: 2px solid #6a0dad; /* Purple border */
        }

        .review-container p.error {
            color: #ff0000;
            background: #ffecec;
            padding: 0.5em;
            border: 1px solid #ff0000;
            border-radius: 4px;
        }

        .review-container p.success {
            color: #4caf50;
            background: #e8f5e9;
            padding: 0.5em;
            border: 1px solid #4caf50;
            border-radius: 4px;
        }

        form {
            display: flex;
            flex-direction: column;
            gap: 1em;
        }

        form div {
            display: flex;
            flex-direction: column;
        }

        form label {
            margin-bottom: 0.5em;
            font-weight: bold;
        }

        form input[type="number"],
        form textarea {
            padding: 0.5em;
            font-size: 1rem;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        form textarea {
            min-height: 100px;
            resize: vertical;
        }

        form button {
            background: linear-gradient(to right, #1e90ff, #6a0dad); /* Blue to purple gradient */
            color: #fff;
            padding: 0.75em;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1rem;
            transition: background 0.3s ease;
        }

        form button:hover {
            background: linear-gradient(to right, #6a0dad, #1e90ff); /* Reversed gradient */
        }
    </style>
</head>
<body>
    <header>
        <h1><?= isset($review_id) ? "Update Your Review" : "Leave a Review" ?></h1>
    </header>
    
    <div class="review-container">
        <!-- Display error or success messages -->
        <?php if (isset($error_message)): ?>
            <p class="error"><?= htmlspecialchars($error_message) ?></p>
        <?php elseif (isset($success_message)): ?>
            <p class="success"><?= htmlspecialchars($success_message) ?></p>
        <?php endif; ?>

        <!-- Review Form -->
        <form method="POST" action="review.php?order_id=<?= htmlspecialchars($order_id) ?>&product_id=<?= htmlspecialchars($product_id) ?>&review_id=<?= htmlspecialchars($review_id) ?>">
            <div>
                <label for="rating">Rating (1-5):</label>
                <input type="number" id="rating" name="rating" min="1" max="5" 
                    value="<?= isset($existing_review['rating']) ? htmlspecialchars($existing_review['rating']) : 1 ?>" required>
            </div>
            <div>
                <label for="review_content">Review:</label>
                <textarea id="review_content" name="review_content" required><?= isset($existing_review['review_content']) ? htmlspecialchars($existing_review['review_content']) : '' ?></textarea>
            </div>
            <div>
                <button type="submit"><?= isset($review_id) ? "Update Review" : "Submit Review" ?></button>
            </div>
        </form>
    </div>
</body>
</html>
