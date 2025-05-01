<?php
include '../includes/dbconn.php';
session_start();

// Get category from URL parameter
$category = isset($_GET['category']) ? $_GET['category'] : '';

// Initialize products array
$products = [];
$category_title = ucfirst($category); // Default title

if (!empty($category)) {
    // Prepare query to get products by category - using LIKE for more flexible matching
    $stmt = $conn->prepare("SELECT * FROM products WHERE category LIKE ? ORDER BY name");
    $search_category = "%$category%";
    $stmt->bind_param("s", $search_category);
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Fetch all products
    $products = $result->fetch_all(MYSQLI_ASSOC);
}

// Handle sorting if requested
$sort_option = isset($_GET['sort']) ? $_GET['sort'] : 'default';
if (!empty($products) && $sort_option != 'default') {
    switch ($sort_option) {
        case 'price_asc':
            usort($products, function($a, $b) {
                return $a['price'] - $b['price'];
            });
            break;
        case 'price_desc':
            usort($products, function($a, $b) {
                return $b['price'] - $a['price'];
            });
            break;
        case 'rating':
            usort($products, function($a, $b) {
                return ($b['rating'] ?? 0) - ($a['rating'] ?? 0);
            });
            break;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($category_title); ?> - SnapCart</title>
    <link rel="stylesheet" href="/minor project/assets/css/index.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet">
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="container">
            <div class="navbar">
                <div class="logo">
                    <img src="/minor project/assets/images/logo.png.png" alt="SnapCart" width="125px">
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

    <!-- Category Products -->
    <div class="small-container">
        <div class="row row-2">
            <h2><?php echo htmlspecialchars($category_title); ?> Products</h2>
            <form id="sort-form" method="GET" action="">
                <input type="hidden" name="category" value="<?php echo htmlspecialchars($category); ?>">
                <select name="sort" onchange="document.getElementById('sort-form').submit()">
                    <option value="default" <?php echo $sort_option == 'default' ? 'selected' : ''; ?>>Default Sorting</option>
                    <option value="price_asc" <?php echo $sort_option == 'price_asc' ? 'selected' : ''; ?>>Sort by Price (Low to High)</option>
                    <option value="price_desc" <?php echo $sort_option == 'price_desc' ? 'selected' : ''; ?>>Sort by Price (High to Low)</option>
                    <option value="rating" <?php echo $sort_option == 'rating' ? 'selected' : ''; ?>>Sort by Rating</option>
                </select>
            </form>
        </div>

        <?php if (empty($products)): ?>
            <div class="empty-category">
                <h3>No products found in this category.</h3>
                <p>Please check back later or explore other categories.</p>
                <a href="/minor project/product/product.php" class="btn">View All Products</a>
            </div>
        <?php else: ?>
            <div class="row">
                <?php foreach ($products as $product): ?>
                    <div class="col-4">
                        <?php 
                        // Fix for product URL - handle both cases
                        $product_url = isset($product['url']) && !empty($product['url']) 
                            ? $product['url'] 
                            : str_replace(' ', '-', $product['name']) . '.php';
                            
                        // Improved image path handling
                        $image_path = $product['image'] ?? '';
                        // Remove any leading slashes or path segments if present
                        $image_path = basename($image_path);
                        ?>
                        <a href="/minor project/product/<?php echo htmlspecialchars($product_url); ?>">
                            <img src="/minor project/assets/images/<?php echo htmlspecialchars($image_path); ?>" 
                                 alt="<?php echo htmlspecialchars($product['name']); ?>"
                                 onerror="this.src='/minor project/assets/images/placeholder.jpg'; this.onerror='';"
                                 style="max-height: 200px; object-fit: contain;">
                        </a>
                        <h4><?php echo htmlspecialchars($product['name']); ?></h4>
                        <div class="rating">
                            <?php 
                            $rating = isset($product['rating']) ? $product['rating'] : 4;
                            for ($i = 1; $i <= 5; $i++) {
                                if ($i <= $rating) {
                                    echo '<i class="fa fa-star"></i>';
                                } elseif ($i - 0.5 <= $rating) {
                                    echo '<i class="fa fa-star-half-o"></i>';
                                } else {
                                    echo '<i class="fa fa-star-o"></i>';
                                }
                            }
                            ?>
                        </div>
                        <p>NRS.<?php echo number_format($product['price']); ?></p>
                        <button onclick="addToCart(
                            <?php echo $product['id']; ?>, 
                            '<?php echo addslashes($product['name']); ?>', 
                            <?php echo $product['price']; ?>, 
                            '<?php echo addslashes($image_path); ?>'
                        )" class="btn">Add to Cart</button>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Category Navigation -->
            <div class="category-navigation">
                <h3>Browse Other Categories</h3>
                <div class="category-links">
                    <a href="/minor project/product/category.php?category=Smartphones" class="category-link <?php echo $category == 'Smartphones' ? 'active' : ''; ?>">Smartphones</a>
                    <a href="/minor project/product/category.php?category=Laptops" class="category-link <?php echo $category == 'Laptops' ? 'active' : ''; ?>">Laptops</a>
                    <a href="/minor project/product/category.php?category=Tablets" class="category-link <?php echo $category == 'Tablets' ? 'active' : ''; ?>">Tablets</a>
                    <a href="/minor project/product/category.php?category=Audio" class="category-link <?php echo $category == 'Audio' ? 'active' : ''; ?>">Audio</a>
                    <a href="/minor project/product/category.php?category=Smartwatches" class="category-link <?php echo $category == 'Smartwatches' ? 'active' : ''; ?>">Smartwatches</a>
                    <a href="/minor project/product/category.php?category=Speakers" class="category-link <?php echo $category == 'Speakers' ? 'active' : ''; ?>">Speakers</a>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Footer -->
    <div class="footer">
        <div class="container">
            <div class="row">
                <div class="footer-col-1">
                    <h3>Download Our App</h3>
                    <p>Download App for Android and iOS mobile phones.</p>
                    <div class="app-logo">
                        <img src="/minor project/assets/images/play-store.png" alt="Google Play Store Logo">
                        <img src="/minor project/assets/images/app-store.png" alt="iOS Store Logo">
                    </div>
                </div>
                <div class="footer-col-2">
                    <img src="/minor project/assets/images/logo.png" alt="SnapCart logo">
                    <p>Our purpose is to sustainably make the pleasure and benefits of technology accessible to everyone.</p>
                </div>
                <div class="footer-col-3">
                    <h3>Useful Links</h3>
                    <ul>
                        <li>Coupons</li>
                        <li>Blog Post</li>
                        <li>Return Policy</li>
                        <li>Join Affiliate</li>
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
            <hr>
            <p class="copyright">Copyright 2025</p>
        </div>
    </div>

    <!-- JS for Menu Toggle -->
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
        
        // Add to cart function
        function addToCart(productId, productName, productPrice, productImage) {
            // Create form data
            const formData = new FormData();
            formData.append('product_id', productId);
            formData.append('quantity', 1);
            formData.append('name', productName);
            formData.append('price', productPrice);
            formData.append('image', '/minor project/assets/images/' + productImage);
            
            // Send request to add-to-cart.php
            fetch('/minor project/cart/add-to-cart.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.text();
            })
            .then(data => {
                try {
                    const jsonData = JSON.parse(data);
                    if(jsonData.success) {
                        alert('Product added to cart successfully!');
                    } else {
                        console.error('Server error:', jsonData.message);
                        alert('Failed to add product to cart: ' + (jsonData.message || 'Unknown error'));
                    }
                } catch(e) {
                    console.error('Error parsing response:', e, 'Raw response:', data);
                    // If we can't parse JSON, still try to add to cart directly
                    const cartItem = {
                        id: productId,
                        name: productName,
                        price: productPrice,
                        image: '/minor project/assets/images/' + productImage,
                        quantity: 1
                    };
                    
                    // Make a direct request to update the session
                    const directFormData = new FormData();
                    directFormData.append('direct_add', JSON.stringify(cartItem));
                    
                    fetch('/minor project/cart/direct-add.php', {
                        method: 'POST',
                        body: directFormData
                    })
                    .then(() => {
                        alert('Product added to cart successfully!');
                    })
                    .catch(err => {
                        console.error('Direct add error:', err);
                        alert('Error adding product to cart. Please try again.');
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while adding the product to cart.');
            });
        }
    </script>
</body>
</html>