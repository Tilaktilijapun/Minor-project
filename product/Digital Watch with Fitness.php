<?php
session_start();
include '../includes/dbconn.php';

// Create CSRF token if it doesn't exist
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Initialize cart
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$product_id = 11; // Updated to correct ID
$stmt = $conn->prepare("SELECT p.*, COUNT(r.id) as review_count, AVG(r.rating) as avg_rating 
                       FROM products p 
                       LEFT JOIN reviews r ON p.id = r.product_id 
                       WHERE p.id = ?
                       GROUP BY p.id");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();

// Get product reviews
$reviews_stmt = $conn->prepare("SELECT r.*, u.username 
                              FROM reviews r 
                              JOIN user u ON r.user_id = u.id 
                              WHERE r.product_id = ? 
                              ORDER BY r.created_at DESC 
                              LIMIT 5");
$reviews_stmt->bind_param("i", $product_id);
$reviews_stmt->execute();
$reviews = $reviews_stmt->get_result();

// Handle add to cart directly in this page
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_to_cart'])) {
        // Add to cart logic
        if ($product && isset($product['stock']) && $product['stock'] > 0) {
            if (isset($_SESSION['cart'][$product_id])) {
                $_SESSION['cart'][$product_id]['quantity']++;
            } else {
                $_SESSION['cart'][$product_id] = [
                    'name' => $product['name'] ?? 'Digital-watch-with-fitness',
                    'price' => $product['price'] ?? 10000,
                    'image' => $product['image'] ?? '/minor project/assets/images/Digital-watch-with-fitness.jpg',
                    'quantity' => 1
                ];
            }
            header('Location: /minor project/product/Digital Watch with Fitness.php?added=1');
            exit;
        }
    } elseif (isset($_POST['buy_now'])) {
        // Buy now logic - add to cart and redirect to cart page
        if ($product && isset($product['stock']) && $product['stock'] > 0) {
            $_SESSION['cart'][$product_id] = [
                'name' => $product['name'] ?? 'Digital-watch-with-fitness',
                'price' => $product['price'] ?? 10000,
                'image' => $product['image'] ?? '/minor project/assets/images/Digital-watch-with-fitness.jpg',
                'quantity' => 1
            ];
            header('Location: /minor project/cart/cart.php');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <!-- ===== CSS ===== -->
        <link rel="stylesheet" href="/minor project/product/assets/css/styles.css">
        <title>Digital Watch with Fitness - SnapCart</title>
    </head>
    <body>
        <div class="side-nav">
            <h3>Menu</h3>
            <a href="/minor project/index.php"><img src="/minor project/assets/images/home.png" alt="Home"> Home</a>
            <a href="/minor project/product/product.php"><img src="/minor project/assets/images/product.png" alt="Products"> Products</a>
            <a href="/minor project/aboutus.php"><img src="/minor project/assets/images/about.png" alt="About"> About</a>
            <a href="/minor project/contact.php"><img src="/minor project/assets/images/contact.png" alt="Contact"> Contact</a>
        </div>
        <div class="top">
            <div class="header">
                <img src="/minor project/assets/images/logo.png.png" alt="Logo">
                <div class="search-bar">
                    <input type="text" placeholder="Search Products...">
                    <button style="background-color: orange" onclick="window.location.href='/minor project/product/search.php'">Search</button>
                </div>
            </div>
            <div class="nav-bar">
                <a href="/minor project/product/product.php">Shop</a>
                <a href="/minor project/cart/view-cart.php">Cart</a>
                <a href="/minor project/product/categories.php">Categories</a>
                <a href="/minor project/aboutus.php">Help</a>
            </div>


        <div class="container">
            <div class="card">
                <img src="/minor project/assets/images/Digital-watch-with-fitness.jpg" alt="Digital-watch-with-fitness" class="card__img"> 
                <!-- <div class="card__content">  -->
                     <div class="card__data">
                         <h1 class="card__title">Digital-watch-with-fitness </h1>
                         <span class="card__preci">Rs.10000</span>
                         <p class="card__description">A web file that might contain information about a digital smartwatch with fitness tracking features.  
                        </p>
                        
                        <?php if (isset($_GET['added']) && $_GET['added'] == 1): ?>
                            <div class="alert-success" style="color: green; margin-bottom: 10px;">
                                Product added to cart successfully!
                            </div>
                        <?php endif; ?>
                        
                        <!-- Simplified Buy Now form -->
                        <form method="POST" action="">
                            <input type="hidden" name="buy_now" value="1">
                            <button type="submit" class="card__button">Buy Now</button>
                        </form>
                        
                        <!-- Simplified Add to Cart form -->
                        <form method="POST" action="">
                            <input type="hidden" name="add_to_cart" value="1">
                            <button type="submit" class="card__button">Add to Cart</button>
                        </form>
                     </div>
                <!-- </div> -->
            </div>
        </div>
        <div class="reviews">
            <h2>Latest Reviews</h2>
            <div class="review-card">
                <span>★★★★★</span>
                <p> Best gaming laptop</p>
            </div>
            <div class="review-card">
                <span>★★★★☆</span>
                <p>Comfortable design but expensive.</p>
            </div>
        </div>
        <div class="carousel">
            <div class="related-product-card">
                <a href="/minor project/product/Hpvictus.php">  <img src="/minor project/assets/images/Hpvictus.png" alt="Laptop"></a>
                <h3>Hp victus</h3>
                <p>Rs. 106,000</p>
    
                <div class="review-card">★★★★☆</div>
            </div>
            <div class="related-product-card">
                <a href="/minor project/product/Lenovo-LOQ-15.php"><img src="/minor project/assets/images/Lenovo-LOQ-15-2024-Storm-Grey.jpg" alt="Lenovo-LOQ-15-2024-Storm-Grey"></a>
                <h3>Lenovo-LOQ-15-2024-Storm-Grey</h3>
                <p>Rs. 91000</p>
    
                <div class="review-card">★★★★★</div>
            </div>
            <div class="related-product-card">
                <a href="/minor project/product/Digital-watch.php" > <img src="/minor project/assets/images/Watch.png" alt="Watch"></a>
            <h3>Digital Watch</h3>
            <p>Rs. 5,000</p>

                <div class="review-card">★★★★☆</div>
            </div>
            <div class="related-product-card">
                <a href="/minor project/product/Redmi-buds5.php">  <img src="/minor project/assets/images/airport6.jpg" alt="airport"></a>
            <h3>Redmi buds 5</h3>
            <p>Rs. 4800</p>

                <div class="review-card">★★★★☆</div>
            </div>
            </div>
        </div>
    </div>
    <script src="/minor project/assets/js/script.js"></script>
    <script src="/minor project/assets/js/product.js"></script>
</body>
</html>
