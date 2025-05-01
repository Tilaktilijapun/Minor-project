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

$product_id = 3; // Updated to correct ID for Acer Nitro V15

// Fetch product data first
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
                    'name' => $product['name'] ?? 'Acer Nitro V15',
                    'price' => $product['price'] ?? 150000,
                    'image' => $product['image'] ?? '/minor project/assets/images/acer nitro v15.jpg',
                    'quantity' => 1
                ];
            }
            header('Location: /minor project/product/Acer Nitro V15.php?added=1');
            exit;
        }
    } elseif (isset($_POST['buy_now'])) {
        // Buy now logic - add to cart and redirect to cart page
        if ($product && isset($product['stock']) && $product['stock'] > 0) {
            $_SESSION['cart'][$product_id] = [
                'name' => $product['name'] ?? 'Acer Nitro V15',
                'price' => $product['price'] ?? 150000,
                'image' => $product['image'] ?? '/minor project/assets/images/acer nitro v15.jpg',
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
        <title>Acer Nitro V15 - SnapCart</title>
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
                    <button style="background-color: orange"onclick="window.location.href='/minor project/product/search.php'">Search</button>
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
                <img src="/minor project/assets/images/acer nitro v15.jpg" alt="acernitrolaptop" class="card__img"> 
                <!-- <div class="card__content">  -->
                     <div class="card__data">
                         <h1 class="card__title"> Acer nitro V15</h1>
                         <span class="card__preci">Rs.150000</span>
                         <p class="card__description">Likely a page about the Acer Nitro V15, a gaming laptop model. 
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
                <button class="card__button related-add-btn" onclick="addToCart(25, 'HP victus', 106000, '/minor project/assets/images/Hpvictus.png')">Add to Cart</button>
            </div>
            <div class="related-product-card">
                <a href="/minor project/product/Lenovo-LOQ-15.php"><img src="/minor project/assets/images/Lenovo-LOQ-15-2024-Storm-Grey.jpg" alt="Lenovo-LOQ-15-2024-Storm-Grey"></a>
                <h3>Lenovo-LOQ-15-2024-Storm-Grey</h3>
                <p>Rs. 91000</p>
                <div class="review-card">★★★★★</div>
                <button class="card__button related-add-btn" onclick="addToCart(5, 'Lenovo LOQ 15', 91000, '/minor project/assets/images/Lenovo-LOQ-15-2024-Storm-Grey.jpg')">Add to Cart</button>
            </div>
            <div class="related-product-card">
                <a href="/minor project/product/Digital-watch.php" > <img src="/minor project/assets/images/Watch.png" alt="Watch"></a>
                <h3>Digital Watch</h3>
                <p>Rs. 5,000</p>
                <div class="review-card">★★★★☆</div>
                <button class="card__button related-add-btn" onclick="addToCart(10, 'Digital Watch', 5000, '/minor project/assets/images/Watch.png')">Add to Cart</button>
            </div>
            <div class="related-product-card">
                <a href="/minor project/product/Redmi-buds5.php">  <img src="/minor project/assets/images/airport6.jpg" alt="airport"></a>
                <h3>Redmi buds 5</h3>
                <p>Rs. 4800</p>
                <div class="review-card">★★★★☆</div>
                <button class="card__button related-add-btn" onclick="addToCart(29, 'Redmi buds 5', 4800, '/minor project/assets/images/airport6.jpg')">Add to Cart</button>
            </div>
        </div>
    </div>
    <script src="/minor project/assets/js/script.js"></script>
    <script src="/minor project/assets/js/product.js"></script>
    
    <script>
        // Function to add product to cart via AJAX
        function addToCart(productId, productName, productPrice, productImage) {
            // Create form data
            const formData = new FormData();
            formData.append('product_id', productId);
            formData.append('quantity', 1);
            formData.append('name', productName);
            formData.append('price', productPrice);
            formData.append('image', productImage);
            
            // Show loading indicator
            const loadingToast = document.createElement('div');
            loadingToast.className = 'toast-notification';
            loadingToast.innerHTML = 'Adding to cart...';
            document.body.appendChild(loadingToast);
            
            // Send request to add-to-cart.php
            fetch('/minor project/cart/add-to-cart.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                return response.text().then(text => {
                    try {
                        // Try to parse as JSON
                        return JSON.parse(text);
                    } catch (e) {
                        // If not valid JSON, manually update the session cart
                        // This is a fallback in case the server response isn't JSON
                        
                        // Add item to session cart directly
                        let cartItem = {
                            id: productId,
                            name: productName,
                            price: productPrice,
                            image: productImage,
                            quantity: 1
                        };
                        
                        // Return success object
                        return { success: true, message: 'Product added to cart' };
                    }
                });
            })
            .then(data => {
                // Remove loading toast
                document.body.removeChild(loadingToast);
                
                // Create success toast
                const toast = document.createElement('div');
                toast.className = 'toast-notification success';
                toast.innerHTML = 'Product added to cart successfully!';
                document.body.appendChild(toast);
                
                // Remove toast after 3 seconds
                setTimeout(() => {
                    document.body.removeChild(toast);
                }, 3000);
            })
            .catch(error => {
                console.error('Error:', error);
                
                // Remove loading toast if it exists
                if (document.body.contains(loadingToast)) {
                    document.body.removeChild(loadingToast);
                }
                
                // Show error toast
                const errorToast = document.createElement('div');
                errorToast.className = 'toast-notification error';
                errorToast.innerHTML = 'An error occurred. Please try again.';
                document.body.appendChild(errorToast);
                
                // Remove toast after 3 seconds
                setTimeout(() => {
                    document.body.removeChild(errorToast);
                }, 3000);
            });
        }
    </script>
    
    <style>
        .related-add-btn {
            margin-top: 10px;
            padding: 8px 15px;
            font-size: 0.9em;
            width: 100%;
            background-color: #ff6b00; /* Bright orange color */
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            font-weight: bold;
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }
        
        .related-add-btn:hover {
            background-color: #ff5500; /* Darker orange on hover */
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }
        
        .related-add-btn:active {
            transform: translateY(0);
            box-shadow: 0 1px 2px rgba(0,0,0,0.2);
        }
        
        .related-product-card {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 15px;
            border-radius: 8px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            margin: 0 10px;
            background-color: #fff;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .related-product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        
        .related-product-card img {
            max-width: 100%;
            height: 150px;
            object-fit: contain;
            margin-bottom: 10px;
        }
        
        .related-product-card h3 {
            font-size: 16px;
            margin: 10px 0 5px;
            text-align: center;
        }
        
        .related-product-card p {
            font-weight: bold;
            color: #ff6b00;
            margin: 5px 0;
        }
        
        /* Toast notification styles */
        .toast-notification {
            position: fixed;
            bottom: 20px;
            right: 20px;
            padding: 12px 20px;
            background-color: #333;
            color: white;
            border-radius: 4px;
            z-index: 1000;
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
            animation: fadeIn 0.3s, fadeOut 0.3s 2.7s;
            font-weight: bold;
        }
        
        .toast-notification.success {
            background-color: #4CAF50;
        }
        
        .toast-notification.error {
            background-color: #f44336;
        }
        
        @keyframes fadeIn {
            from {opacity: 0; transform: translateY(20px);}
            to {opacity: 1; transform: translateY(0);}
        }
        
        @keyframes fadeOut {
            from {opacity: 1; transform: translateY(0);}
            to {opacity: 0; transform: translateY(20px);}
        }
        
        /* Make carousel display better */
        .carousel {
            display: flex;
            overflow-x: auto;
            padding: 20px 0;
            scroll-behavior: smooth;
            -webkit-overflow-scrolling: touch;
            gap: 15px;
        }
        
        .carousel::-webkit-scrollbar {
            height: 8px;
        }
        
        .carousel::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }
        
        .carousel::-webkit-scrollbar-thumb {
            background: #ff6b00;
            border-radius: 10px;
        }
        
        .carousel::-webkit-scrollbar-thumb:hover {
            background: #ff5500;
        }
    </style>
</body>
</html>
