<?php
session_start();

// Load products from JSON file with error handling
$json_path = '../data/products.json';
if (!file_exists($json_path)) {
    die("Error: products.json file not found in data directory");
}

$json_file = file_get_contents($json_path);
if ($json_file === false) {
    die("Error: Unable to read products.json file");
}

$all_products = json_decode($json_file, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    die("Error: Invalid JSON format in products.json");
}

// Access the products array correctly
$products = $all_products['products'] ?? [];

// Extract unique categories from products with additional metrics
$categories = [];
foreach ($products as $product) {
    if (!empty($product['category'])) {
        $category_name = $product['category'];
        if (!isset($categories[$category_name])) {
            $categories[$category_name] = [
                'name' => $category_name,
                'count' => 1,
                'min_price' => $product['price'] ?? 0,
                'max_price' => $product['price'] ?? 0,
                'total_rating' => $product['rating'] ?? 0,
                'rating_count' => isset($product['rating']) ? 1 : 0,
                'featured' => in_array($category_name, ['Gaming Laptops', 'Smartphones', 'Audio']),
                'description' => getCategoryDescription($category_name)
            ];
        } else {
            $categories[$category_name]['count']++;
            if (isset($product['price'])) {
                $categories[$category_name]['min_price'] = min($categories[$category_name]['min_price'], $product['price']);
                $categories[$category_name]['max_price'] = max($categories[$category_name]['max_price'], $product['price']);
            }
            if (isset($product['rating'])) {
                $categories[$category_name]['total_rating'] += $product['rating'];
                $categories[$category_name]['rating_count']++;
            }
        }
    }
}

// Calculate average rating for each category
foreach ($categories as &$category) {
    $category['avg_rating'] = $category['rating_count'] > 0 
        ? round($category['total_rating'] / $category['rating_count'], 1) 
        : 0;
}

// Sort categories by featured status first, then by name
uasort($categories, function($a, $b) {
    if ($a['featured'] !== $b['featured']) {
        return $b['featured'] - $a['featured'];
    }
    return strcmp($a['name'], $b['name']);
});

// Function to get category description
function getCategoryDescription($category) {
    $descriptions = [
        'Gaming Laptops' => 'High-performance laptops designed for gaming enthusiasts',
        'Laptops' => 'Professional and personal laptops for everyday use',
        'Smartphones' => 'Latest smartphones with cutting-edge features',
        'Audio' => 'Premium audio devices for the best sound experience',
        'Wearables' => 'Smart wearable devices for fitness and lifestyle',
        'Accessories' => 'Essential accessories for your electronic devices',
        'Tablets' => 'Versatile tablets for work and entertainment'
    ];
    return $descriptions[$category] ?? 'Explore our range of ' . $category;
}

// Updated icons array with more categories
$icons = [
    'Gaming Laptops' => 'fa-gamepad',
    'Laptops' => 'fa-laptop',
    'Wearables' => 'fa-clock',
    'Smartphones' => 'fa-mobile-alt',
    'Accessories' => 'fa-plug',
    'Audio' => 'fa-headphones',
    'Tablets' => 'fa-tablet-alt'
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Categories - SnapCart</title>
    <link rel="stylesheet" href="/minor project/assets/css/categories.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <header class="categories-header">
        <nav class="breadcrumb">
            <a href="/minor project/index.php">Home</a>
            <i class="fas fa-chevron-right"></i>
            <span>Categories</span>
        </nav>
        <div class="header-content">
            <h1>Browse Categories</h1>
            <p>Discover our wide range of products across different categories</p>
        </div>
    </header>

    <main class="categories-container">
        <?php if (!empty($categories)): ?>
            <!-- Featured Categories Section -->
            <section class="featured-categories">
                <h2>Featured Categories</h2>
                <div class="categories-grid">
                    <?php foreach ($categories as $category): ?>
                        <?php if ($category['featured']): ?>
                            <a href="category.php?category=<?php echo urlencode($category['name']); ?>" 
                               class="category-card featured">
                                <div class="category-icon">
                                    <?php
                                    $icon = isset($icons[$category['name']]) ? $icons[$category['name']] : 'fa-box';
                                    ?>
                                    <i class="fas <?php echo $icon; ?>"></i>
                                </div>
                                <div class="category-info">
                                    <h2><?php echo htmlspecialchars($category['name']); ?></h2>
                                    <p class="category-description">
                                        <?php echo htmlspecialchars($category['description']); ?>
                                    </p>
                                    <div class="category-stats">
                                        <span class="product-count">
                                            <i class="fas fa-box"></i> <?php echo $category['count']; ?> Products
                                        </span>
                                        <span class="price-range">
                                            <i class="fas fa-tag"></i> Rs. <?php echo number_format($category['min_price']); ?> - 
                                            Rs. <?php echo number_format($category['max_price']); ?>
                                        </span>
                                        <?php if ($category['avg_rating'] > 0): ?>
                                            <span class="category-rating">
                                                <i class="fas fa-star"></i> <?php echo $category['avg_rating']; ?>/5
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    <span class="view-more">Browse Category <i class="fas fa-arrow-right"></i></span>
                                </div>
                            </a>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </section>

            <!-- All Categories Section -->
            <section class="all-categories">
                <h2>All Categories</h2>
                <div class="categories-grid">
                    <?php foreach ($categories as $category): ?>
                        <?php if (!$category['featured']): ?>
                            <a href="category.php?category=<?php echo urlencode($category['name']); ?>" class="category-card">
                                <div class="category-icon">
                                    <?php
                                    $icon = isset($icons[$category['name']]) ? $icons[$category['name']] : 'fa-box';
                                    ?>
                                    <i class="fas <?php echo $icon; ?>"></i>
                                </div>
                                <div class="category-info">
                                    <h2><?php echo htmlspecialchars($category['name']); ?></h2>
                                    <div class="category-stats">
                                        <span class="product-count"><?php echo $category['count']; ?> Products</span>
                                        <span class="price-range">
                                            Rs. <?php echo number_format($category['min_price']); ?> - 
                                            Rs. <?php echo number_format($category['max_price']); ?>
                                        </span>
                                    </div>
                                    <span class="view-more">Browse Category <i class="fas fa-arrow-right"></i></span>
                                </div>
                            </a>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php else: ?>
            <div class="no-categories">
                <i class="fas fa-boxes"></i>
                <p>No categories found.</p>
            </div>
        <?php endif; ?>
    </main>

    <footer>
        <p>&copy; <?php echo date('Y'); ?> SnapCart. All rights reserved.</p>
    </footer>
</body>
</html>