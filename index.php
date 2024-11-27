<?php
include 'includes/header.php';
include 'includes/config.php';

// Fetch products with error handling
$stmt = $conn->prepare("SELECT * FROM products");
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ROMEROS' PERFUMES</title>
    <link rel="stylesheet" href="includes/style/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <!-- Swiper.js Styles -->
    <link rel="stylesheet" href="https://unpkg.com/swiper/swiper-bundle.min.css">
</head>
<body>
<header>
    <h1>ROMEROS' PERFUMES</h1>
</header>

<div class="product-list">
    <?php foreach ($product as $item): ?>
    <div class="product">
        <img src="images/<?= htmlspecialchars($item['image']) ?>" 
             alt="<?= htmlspecialchars($item['name']) ?>" 
             class="product-image"
             onclick="showProductPopup(<?= $item['product_id'] ?>)">
        
        <h2><?= htmlspecialchars($item['name']) ?></h2>
        <p><?= htmlspecialchars($item['description']) ?></p>
        <p>Price: ₱<?= htmlspecialchars($item['price']) ?></p>
        <p>Stock: <?= htmlspecialchars($item['stock']) ?></p>

        <!-- Add to Cart Form -->
        <form method="post" action="cart.php">
            <input type="hidden" name="product_id" value="<?= $item['product_id'] ?>">
            <label for="quantity-<?= $item['product_id'] ?>">Quantity:</label>
            <input type="number" id="quantity-<?= $item['product_id'] ?>" name="quantity" min="1" max="<?= $item['stock'] ?>" value="1" required>
            <button type="submit" class="add-to-cart-button">Add to Cart</button>
        </form>
    </div>
    <?php endforeach; ?>
</div>

<!-- Modal Popup (Hidden by default) -->
<div id="product-popup" class="product-popup" style="display: none;">
    <div class="product-popup-content">
        <span class="close" onclick="closePopup()">&times;</span>
        
        <!-- Product Details -->
        <h2 id="popup-product-name">Product Name</h2>
        
        <!-- Image Gallery -->
        <div class="swiper-container">
            <div id="popup-image-gallery" class="swiper-wrapper">
                <!-- Images will be dynamically loaded here -->
            </div>
            <!-- Swiper navigation -->
            <div class="swiper-pagination"></div>
            <div class="swiper-button-next"></div>
            <div class="swiper-button-prev"></div>
        </div>

        <p id="popup-product-description">Product description goes here...</p>
        <p id="popup-product-price">Price: ₱0.00</p>
        <p id="popup-product-stock">Stock: 0</p>

        <!-- Reviews Section -->
        <div id="popup-reviews">
            <h3>Reviews</h3>
            <div id="review-list">
                <p>No reviews yet. Be the first to review!</p>
            </div>
        </div>
    </div>
</div>

<!-- Swiper.js Script -->
<script src="https://unpkg.com/swiper/swiper-bundle.min.js"></script>
<script>
   function showProductPopup(productId) {
    fetch('product_details.php?id=' + productId)
        .then(response => {
            if (!response.ok) throw new Error('HTTP error: ' + response.status);
            return response.json();
        })
        .then(data => {
            if (data.error) throw new Error(data.error);

            // Update product details in modal
            document.getElementById('popup-product-name').innerText = data.name;
            document.getElementById('popup-product-description').innerText = data.description;
            document.getElementById('popup-product-price').innerText = 'Price: ₱' + data.price;
            document.getElementById('popup-product-stock').innerText = 'Stock: ' + data.stock;

            // Load images into Swiper gallery
            const imageGallery = document.getElementById('popup-image-gallery');
            imageGallery.innerHTML = ''; // Clear previous images
            data.images.forEach(image => {
                const slide = document.createElement('div');
                slide.classList.add('swiper-slide');
                slide.innerHTML = `<img src="images/${image}" alt="Product Image" class="product-popup-image" />`;
                imageGallery.appendChild(slide);
            });

            // Initialize Swiper
            new Swiper('.swiper-container', {
                navigation: {
                    nextEl: '.swiper-button-next',
                    prevEl: '.swiper-button-prev',
                },
                pagination: { el: '.swiper-pagination', clickable: true },
            });

            // Load reviews
            const reviewList = document.getElementById('review-list');
            reviewList.innerHTML = '';
            if (data.reviews && data.reviews.length > 0) {
                data.reviews.forEach(review => {
                    const reviewItem = document.createElement('div');
                    reviewItem.classList.add('review');
                    reviewItem.innerHTML = `
                        <p><strong>${review.customer_name}</strong> (${generateStarRating(review.rating)})</p>
                        <p>${review.review_content}</p>
                        <small>${new Date(review.review_date).toLocaleDateString()}</small>
                    `;
                    reviewList.appendChild(reviewItem);
                });
            } else {
                reviewList.innerHTML = '<p>No reviews yet. Be the first to review!</p>';
            }

            // Show modal
            document.getElementById('product-popup').style.display = 'flex';
        })
        .catch(error => {
            console.error('Error loading product details:', error);
            alert(error.message);
        });
   }

   // Close Modal
   function closePopup() {
       document.getElementById('product-popup').style.display = 'none';
   }

   // Generate Star Rating
   function generateStarRating(rating) {
       const fullStars = '★'.repeat(Math.floor(rating));
       const emptyStars = '☆'.repeat(5 - Math.floor(rating));
       return fullStars + emptyStars;
   }
</script>

<?php include 'includes/footer.php'; ?>
</body>
</html>
