<?php
session_start();
include '../includes/dbconn.php';
include '../includes/image-helper.php'; // Include our new image helper

// Initialize cart if it doesn't exist
if(!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = array();
}

// Calculate cart total
$cart_total = 0;
foreach($_SESSION['cart'] as $item) {
    $cart_total += $item['price'] * $item['quantity'];
}

// Enhance cart items with proper image paths
foreach($_SESSION['cart'] as $key => $item) {
    $product_id = $item['id'] ?? 0;
    
    // If we have a product ID, fetch the correct image from database
    if($product_id > 0) {
        $stmt = $conn->prepare("SELECT image FROM products WHERE id = ?");
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if($row = $result->fetch_assoc()) {
            // Update the image path in the cart
            $_SESSION['cart'][$key]['image'] = $row['image'];
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - SnapCart</title>
    <link rel="stylesheet" href="/minor project/assets/css/index.css">
    <link rel="stylesheet" href="/minor project/assets/css/product.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet">
    <style>
        .cart-page {
            margin: 80px auto;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .cart-info {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
        }
        
        th {
            text-align: left;
            padding: 5px;
            color: #fff;
            background: #ff523b;
            font-weight: normal;
        }
        
        td {
            padding: 10px 5px;
        }
        
        td input {
            width: 40px;
            height: 30px;
            padding: 5px;
            text-align: center;
        }
        
        td a {
            color: #ff523b;
            font-size: 12px;
            text-decoration: none;
        }
        
        td img {
            width: 80px;
            height: 80px;
            margin-right: 10px;
            object-fit: contain;
            background-color: #f9f9f9;
            border: 1px solid #eee;
        }
        
        .cart-product-image {
            width: 80px !important;
            height: 80px !important;
            object-fit: contain !important;
            background-color: #f9f9f9;
            border: 1px solid #eee;
        }
        
        .total-price {
            display: flex;
            justify-content: flex-end;
        }
        
        .total-price table {
            border-top: 3px solid #ff523b;
            width: 100%;
            max-width: 400px;
        }
        
        td:last-child {
            text-align: right;
        }
        
        th:last-child {
            text-align: right;
        }
        
        .empty-cart {
            text-align: center;
            padding: 50px 0;
        }
        
        .empty-cart i {
            font-size: 64px;
            color: #ddd;
            margin-bottom: 20px;
        }
        
        .empty-cart p {
            color: #666;
            margin-bottom: 20px;
        }
        
        .checkout-btn {
            background-color: #ff523b;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 500;
            margin-top: 20px;
            float: right;
        }
        
        .checkout-btn:hover {
            background-color: #ff3c20;
        }
        
        .update-btn {
            background-color: #4CAF50;
            color: white;
            padding: 5px 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
        }
        
        .update-btn:hover {
            background-color: #45a049;
        }
        
        .remove-btn {
            background-color: #f44336;
            color: white;
            padding: 5px 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
        }
        
        .remove-btn:hover {
            background-color: #d32f2f;
        }
    </style>
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
                        <input type="text" name="query" class="search-bar" placeholder="Search for products...">
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

    <div class="small-container cart-page">
        <h2>Your Shopping Cart</h2>
        
        <?php if(empty($_SESSION['cart'])): ?>
            <div class="empty-cart">
                <i class="fa fa-shopping-cart"></i>
                <p>Your cart is empty</p>
                <p>Browse our products and add items to your cart</p>
                <a href="/minor project/product/product.php" class="btn">Continue Shopping</a>
            </div>
        <?php else: ?>
            <table>
                <tr>
                    <th>Product</th>
                    <th>Quantity</th>
                    <th>Subtotal</th>
                </tr>
                <?php foreach($_SESSION['cart'] as $key => $item): ?>
                    <tr>
                        <td>
                            <div class="cart-info">
                                <?php
                                // Enhanced image path handling
                                $product_id = $item['id'] ?? 0;
                                $original_image = $item['image'] ?? '';
                                
                                // If image path is empty or doesn't exist, use placeholder
                                if (empty($original_image)) {
                                    $image_path = '/minor project/assets/images/placeholder.jpg';
                                } else {
                                    // Check if the image path is already a full URL or absolute path
                                    if (filter_var($original_image, FILTER_VALIDATE_URL) || 
                                        strpos($original_image, '/minor project/') === 0) {
                                        $image_path = $original_image;
                                    } else {
                                        // Try to construct the path based on product ID
                                        if ($product_id > 0) {
                                            // First check if it's in the uploads directory
                                            $possible_path = '/minor project/uploads/products/' . $original_image;
                                            if (file_exists($_SERVER['DOCUMENT_ROOT'] . $possible_path)) {
                                                $image_path = $possible_path;
                                            } else {
                                                // Try the images directory
                                                $possible_path = '/minor project/assets/images/products/' . $original_image;
                                                if (file_exists($_SERVER['DOCUMENT_ROOT'] . $possible_path)) {
                                                    $image_path = $possible_path;
                                                } else {
                                                    // Use the original path as is, with project prefix
                                                    $image_path = '/minor project/' . ltrim($original_image, '/');
                                                }
                                            }
                                        } else {
                                            // No product ID, just use the image with project prefix
                                            $image_path = '/minor project/' . ltrim($original_image, '/');
                                        }
                                    }
                                    
                                    // Make sure the path starts with the project path
                                    if (!preg_match('/^\/minor project\//', $image_path)) {
                                        $image_path = '/minor project/' . ltrim($image_path, '/');
                                    }
                                }
                                ?>
                                <img src="<?php echo htmlspecialchars($image_path); ?>" 
                                     alt="<?php echo htmlspecialchars($item['name']); ?>"
                                     class="cart-product-image"
                                     onerror="this.src='/minor project/assets/images/placeholder.jpg';">
                                <div>
                                    <p><?php echo htmlspecialchars($item['name']); ?></p>
                                    <small>Price: NRS. <?php echo number_format($item['price'], 2); ?></small>
                                    <br>
                                    <button class="remove-btn" onclick="removeFromCart(<?php echo $key; ?>)">Remove</button>
                                </div>
                            </div>
                        </td>
                        <td>
                            <input type="number" value="<?php echo $item['quantity']; ?>" min="1" id="qty-<?php echo $key; ?>">
                            <button class="update-btn" onclick="updateCart(<?php echo $key; ?>)">Update</button>
                        </td>
                        <td>NRS. <?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
            
            <div class="total-price">
                <table>
                    <tr>
                        <td>Subtotal</td>
                        <td>NRS. <?php echo number_format($cart_total, 2); ?></td>
                    </tr>
                    <tr>
                        <td>Shipping Fee</td>
                        <td>NRS. 40.00</td>
                    </tr>
                    <tr>
                        <td>Total</td>
                        <td>NRS. <?php echo number_format($cart_total + 40, 2); ?></td>
                    </tr>
                </table>
            </div>
            
            <button class="checkout-btn" onclick="window.location.href='/minor project/cart/cart.php'">Proceed to Checkout</button>
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

    <!-- JS for Menu Toggle and Cart Functions -->
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
        
        // Function to update cart quantity
        function updateCart(key) {
            const quantity = document.getElementById('qty-' + key).value;
            
            const data = new URLSearchParams();
            data.append('key', key);
            data.append('quantity', quantity);
            data.append('action', 'update');
            
            fetch('/minor project/cart/update-cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: data
            })
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    window.location.reload();
                } else {
                    alert('Failed to update cart: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while updating the cart.');
            });
        }
        
        // Function to remove item from cart
        function removeFromCart(key) {
            if(confirm('Are you sure you want to remove this item from your cart?')) {
                const data = new URLSearchParams();
                data.append('key', key);
                data.append('action', 'remove');
                
                fetch('/minor project/cart/update-cart.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: data
                })
                .then(response => response.json())
                .then(data => {
                    if(data.success) {
                        window.location.reload();
                    } else {
                        alert('Failed to remove item: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while removing the item.');
                });
            }
        }
        
        // Simple image error handling as fallback
        document.addEventListener('DOMContentLoaded', function() {
            // Add simple error handler to all images
            const images = document.querySelectorAll('img');
            images.forEach(img => {
                img.onerror = function() {
                    console.log('Image failed to load: ' + this.src);
                    this.src = '/minor project/assets/images/placeholder.jpg';
                };
            });
        });
    </script>
</body>
</html>
