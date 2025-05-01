<?php
include '../includes/dbconn.php';
include '../includes/image-helper.php'; // Include the image helper
session_start();

// Get search query
$search_query = isset($_GET['query']) ? trim($_GET['query']) : '';

// Initialize products array
$products = [];

// If search query exists, search for products
if (!empty($search_query)) {
    // Prepare search query with wildcards for partial matches
    $search_param = "%{$search_query}%";
    
    // Prepare and execute the query
    $stmt = $conn->prepare("SELECT * FROM products WHERE name LIKE ? OR description LIKE ? OR category LIKE ? ORDER BY name");
    $stmt->bind_param("sss", $search_param, $search_param, $search_param);
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Fetch all products
    $products = $result->fetch_all(MYSQLI_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Results - SnapCart</title>
    <link rel="stylesheet" href="/minor project/assets/css/index.css">
    <link rel="stylesheet" href="/minor project/product/assets/css/search.css">
    <link rel="stylesheet" href="/minor project/assets/css/product.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet">
</head>
<body>
    <div class="header">
        <div class="container">
            <div class="navbar">
                <div class="logo">
                    <img src="/minor project/assets/images/logo.png" alt="SnapCart" width="125px" />
                </div>
                <form action="/minor project/product/search.php" method="GET">
                   <div class="search-container">
                <input type="text" name="query" class="search-bar" placeholder="Search for products..." value="<?php echo htmlspecialchars($search_query); ?>">
                <button type="submit" class="search-button">Search</button>
        </div>
        </form>
                <nav>
                    <ul id="MenuItems">
                        <li><a href="/minor project/index.php">Home</a></li>
                        <li><a href="/minor project/product/product.php">Products</a></li>
                        <li><a href="/minor project/aboutus.php">About</a></li>
                        <li><a href="/minor project/contact.php">Contact</a></li>
                        <li><a href="/minor project/auth/account.php">Account</a></li>
                    </ul>
                </nav>
                <img
                    onclick="window.location.href='/minor project/cart/view-cart.php'"
                    src="/minor project/assets/images/cart.png"
                    alt="Shopping cart icon"
                    width="30px"
                    height="30px"
                />
                <img
                    src="/minor project/assets/images/menu.png"
                    alt="menu icon"
                    class="menu-icon"
                    onclick="menutoggle()"
                />
            </div>
        </div>
    </div>

    <div class="small-container search-results">
        <div class="search-header">
            <h2>Search Results</h2>
            <p>Showing results for: "<?php echo htmlspecialchars($search_query); ?>"</p>
        </div>

        <?php if (empty($products) && !empty($search_query)): ?>
            <div class="no-results">
                <i class="fa fa-search"></i>
                <p>No products found matching your search.</p>
                <p>Try different keywords or browse our categories.</p>
            </div>
        <?php elseif (empty($search_query)): ?>
            <div class="no-results">
                <i class="fa fa-search"></i>
                <p>Please enter a search term to find products.</p>
            </div>
        <?php else: ?>
            <div class="row">
                <?php foreach ($products as $product): ?>
                    <div class="col-4">
                        <div class="product-card">
                            <?php
                            // Use the image helper to get the correct image path
                            $product_id = $product['id'] ?? 0;
                            $original_image = $product['image'] ?? '';
                            $image_path = getProductImagePath($product_id, $original_image);
                            ?>
                            <a href="/minor project/product/<?php echo htmlspecialchars($product['url'] ?? '#'); ?>">
                                <img src="<?php echo htmlspecialchars($image_path); ?>" 
                                     alt="<?php echo htmlspecialchars($product['name']); ?>"
                                     style="max-height: 200px; width: 100%; object-fit: contain;" />
                            </a>
                            <h4><?php echo htmlspecialchars($product['name']); ?></h4>
                            <p class="product-price">NRS. <?php echo number_format($product['price'], 2); ?></p>
                            
                            <?php if (isset($product['stock_quantity']) && $product['stock_quantity'] > 0): ?>
                                <div class="stock-status in-stock">In Stock (<?php echo $product['stock_quantity']; ?>)</div>
                                <button class="add-to-cart-btn" onclick="addToCart(
                                    <?php echo $product['id']; ?>, 
                                    '<?php echo addslashes($product['name']); ?>', 
                                    <?php echo $product['price']; ?>, 
                                    '<?php echo addslashes($image_path); ?>'
                                )">Add to Cart</button>
                            <?php else: ?>
                                <div class="stock-status out-of-stock">Out of Stock</div>
                                <button class="add-to-cart-btn disabled" disabled>Out of Stock</button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Footer -->
    <div class="footer">
        <div class="container">
            <div class="row">
                <div class="footer-col-1">
                    <h3>Download Our App</h3>
                    <p>Download App for Android and ios mobile phones.</p>
                    <div class="app-logo">
                        <img src="/minor project/assets/images/play-store.png" alt="Google Play Store Logo" />
                        <img src="/minor project/assets/images/app-store.png" alt="ios store Logo" />
                    </div>
                </div>
                <div class="footer-col-2">
                    <!-- Fixed logo path -->
                    <img src="/minor project/assets/images/logo.png" alt="snap cart logo " />
                    <p>
                        Our purpose is to sustainably make the pleasure and benefits of
                        sports accessible to the many.
                    </p>
                </div>
                <div class="footer-col-3">
                    <h3>Useful Links</h3>
                    <ul>
                        <li>Coupons</li>
                        <li>Blog Post</li>
                        <li>Return Policy</li>
                        <li>Joins Affiliates</li>
                    </ul>
                </div>
                <div class="footer-col-4">
                    <h3>Follow Us</h3>
                    <ul>
                        <li>Facebook</li>
                        <li>Twitter</li>
                        <li>Instagram</li>
                        <li>YouTube</li>
                    </ul>
                </div>
            </div>
            <hr />
            <p class="copyright">Copyright 2025</p>
        </div>
    </div>

    <!-- JS for Menu Toggle-->
    <script>
        var MenuItems = document.getElementById("MenuItems");

        MenuItems.style.maxHeight = "0px";

        function menutoggle() {
            if (MenuItems.style.maxHeight == "0px") {
                MenuItems.style.maxHeight = "200px";
            } else {
                MenuItems.style.maxHeight = "0px";
            }
        }
        

        function addToCart(productId, productName, productPrice, productImage) {
            // Use URLSearchParams instead of FormData for better compatibility
            const data = new URLSearchParams();
            data.append('product_id', productId);
            data.append('quantity', 1);
            data.append('name', productName);
            data.append('price', productPrice);
            data.append('image', productImage);
            
            // Create and show notification
            const notification = document.createElement('div');
            notification.className = 'notification';
            notification.innerHTML = '<div class="notification-content">Adding to cart...</div>';
            document.body.appendChild(notification);
            
            // Send request to add-to-cart.php
            fetch('/minor project/cart/add-to-cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: data
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.text();
            })
            .then(data => {
                console.log('Response:', data); // Log the response for debugging
                
                // Update notification with success message
                notification.innerHTML = '<div class="notification-content success">' + 
                                         '<i class="fa fa-check-circle"></i> ' + 
                                         productName + ' added to cart successfully!</div>';
                
                // Remove notification after 3 seconds
                setTimeout(() => {
                    notification.classList.add('fade-out');
                    setTimeout(() => {
                        if (document.body.contains(notification)) {
                            document.body.removeChild(notification);
                        }
                    }, 500);
                }, 3000);
                
                // Refresh cart count display if needed
                updateCartCount();
            })
            .catch(error => {
                console.error('Error:', error);
                
                // Update notification with error message
                notification.innerHTML = '<div class="notification-content error">' + 
                                         '<i class="fa fa-exclamation-circle"></i> ' + 
                                         'Error adding product to cart. Please try again.</div>';
                
                // Remove notification after 3 seconds
                setTimeout(() => {
                    notification.classList.add('fade-out');
                    setTimeout(() => {
                        if (document.body.contains(notification)) {
                            document.body.removeChild(notification);
                        }
                    }, 500);
                }, 3000);
            });
        }
        
        // Function to update cart count
        function updateCartCount() {
            fetch('/minor project/cart/get-cart-count.php')
            .then(response => response.json())
            .then(data => {
                // Update cart count display if it exists
                const cartCountElement = document.getElementById('cart-count');
                if (cartCountElement) {
                    cartCountElement.textContent = data.count;
                }
            })
            .catch(error => console.error('Error updating cart count:', error));
        }
    </script>
    
    <style>
        /* Product card styling */
        .product-card {
            border: 1px solid #e1e1e1;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
            transition: transform 0.3s, box-shadow 0.3s;
            background-color: white;
            height: 100%;
            display: flex;
            flex-direction: column;
        }
        
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        
        .product-card img {
            width: 100%;
            height: 200px;
            object-fit: contain;
            margin-bottom: 15px;
            border-radius: 4px;
        }
        
        .product-card h4 {
            font-size: 16px;
            margin: 10px 0;
            height: 40px;
            overflow: hidden;
        }
        
        .product-price {
            font-weight: bold;
            color: #ff6b00;
            margin: 10px 0;
        }
        
        .stock-status {
            margin: 10px 0;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 14px;
            text-align: center;
        }
        
        .in-stock {
            background-color: #e8f5e9;
            color: #2e7d32;
        }
        
        .out-of-stock {
            background-color: #ffebee;
            color: #c62828;
        }
        
        .add-to-cart-btn {
            background-color: #ff6b00;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
            transition: background-color 0.3s;
            margin-top: auto;
        }
        
        .add-to-cart-btn:hover {
            background-color: #ff5500;
        }
        
        .add-to-cart-btn.disabled {
            background-color: #cccccc;
            cursor: not-allowed;
        }
        
        /* Notification styling */
        .notification {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 1000;
            animation: slideIn 0.5s forwards;
        }
        
        .notification-content {
            background-color: #333;
            color: white;
            padding: 15px 20px;
            border-radius: 4px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
            display: flex;
            align-items: center;
            font-size: 14px;
        }
        
        .notification-content.success {
            background-color: #4CAF50;
        }
        
        .notification-content.error {
            background-color: #f44336;
        }
        
        .notification-content i {
            margin-right: 10px;
            font-size: 18px;
        }
        
        .fade-out {
            animation: fadeOut 0.5s forwards;
        }
        
        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        
        @keyframes fadeOut {
            from {
                opacity: 1;
            }
            to {
                opacity: 0;
            }
        }
    </style>
</body>
</html>