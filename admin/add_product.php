<?php
session_start();
include '../includes/dbconn.php';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
    $description = filter_var($_POST['description'], FILTER_SANITIZE_STRING);
    $price = filter_var($_POST['price'], FILTER_VALIDATE_FLOAT);
    $original_price = filter_var($_POST['original_price'], FILTER_VALIDATE_FLOAT);
    $stock_quantity = filter_var($_POST['stock_quantity'], FILTER_VALIDATE_INT);
    $color = filter_var($_POST['color'], FILTER_SANITIZE_STRING);
    $category = filter_var($_POST['category'], FILTER_SANITIZE_STRING);
    $features = filter_var($_POST['features'], FILTER_SANITIZE_STRING);
    $specifications = filter_var($_POST['specifications'], FILTER_SANITIZE_STRING);

    // Handle image upload
    $image_path = '';
    if (isset($_FILES["image"]) && $_FILES["image"]["error"] == 0) {
        $target_dir = "../assets/images/products/";
        $file_extension = strtolower(pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION));
        $allowed_types = array("jpg", "jpeg", "png", "gif", "webp");
        
        if (in_array($file_extension, $allowed_types)) {
            $file_name = uniqid() . "." . $file_extension;
            $target_file = $target_dir . $file_name;
            
            if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                $image_path = "/minor project/assets/images/products/" . $file_name;
            } else {
                $_SESSION['error'] = "Failed to upload image.";
            }
        } else {
            $_SESSION['error'] = "Invalid file type. Allowed types: jpg, jpeg, png, gif, webp";
        }
    }

    // Insert new product into database
    $sql = "INSERT INTO products (name, description, price, original_price, stock_quantity, 
            stock, color, category, image_url, features, specifications, status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active')";
    
    $stmt = $conn->prepare($sql);
    $stock = $stock_quantity; // Initial stock equals stock_quantity
    $stmt->bind_param("ssddiiissss", 
        $name, $description, $price, $original_price, $stock_quantity, 
        $stock, $color, $category, $image_path, $features, $specifications
    );

    if ($stmt->execute()) {
        $_SESSION['success'] = "Product added successfully!";
        header("Location: product.php");
        exit();
    } else {
        $_SESSION['error'] = "Error adding product: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Product - Admin Dashboard</title>
    <link rel="stylesheet" href="/minor project/admin/css/dashboard.css">
    <link rel="stylesheet" href="/minor project/admin/css/add_product.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="sidebar">
        <div class="logo">
            <img src="/minor project/assets/images/logo.png.png" alt="Logo">
            <span>Admin Panel</span>
        </div>
        <a href="dashboard.php" class="nav-item">
            <i class="fas fa-home"></i>
            <span>Dashboard</span>
        </a>
        <a href="products.php" class="nav-item active">
            <i class="fas fa-box"></i>
            <span>Products</span>
        </a>
        <a href="orders.php" class="nav-item">
            <i class="fas fa-shopping-cart"></i>
            <span>Orders</span>
        </a>
        <a href="customers.php" class="nav-item">
            <i class="fas fa-users"></i>
            <span>Customers</span>
        </a>
        <a href="settings.php" class="nav-item">
            <i class="fas fa-cog"></i>
            <span>Settings</span>
        </a>
        <a href="logout.php" class="nav-item">
            <i class="fas fa-sign-out-alt"></i>
            <span>Logout</span>
        </a>
    </div>

    <div class="main-content">
        <div class="header">
            <h1>Add New Product</h1>
        </div>

        <div class="content-card">
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-error">
                    <?php 
                    echo $_SESSION['error'];
                    unset($_SESSION['error']);
                    ?>
                </div>
            <?php endif; ?>

            <form action="" method="POST" enctype="multipart/form-data" class="product-form">
                <div class="form-group">
                    <label for="name">Product Name</label>
                    <input type="text" id="name" name="name" required>
                </div>

                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" rows="4" required></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="price">Price (Rs)</label>
                        <input type="number" id="price" name="price" step="0.01" required>
                    </div>

                    <div class="form-group">
                        <label for="original_price">Original Price (Rs)</label>
                        <input type="number" id="original_price" name="original_price" step="0.01" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="stock_quantity">Stock Quantity</label>
                        <input type="number" id="stock_quantity" name="stock_quantity" required>
                    </div>

                    <div class="form-group">
                        <label for="color">Color</label>
                        <input type="text" id="color" name="color" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="category">Category</label>
                    <select id="category" name="category" required>
                        <option value="">Select Category</option>
                        <option value="Laptops">Laptops</option>
                        <option value="Smartphones">Smartphones</option>
                        <option value="Audio">Audio</option>
                        <option value="Television">Television</option>
                        <option value="Accessories">Accessories</option>
                        <option value="Gaming Laptops">Gaming Laptops</option>
                        <option value="Wearables">Wearables</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="features">Features (JSON format)</label>
                    <textarea id="features" name="features" rows="4" placeholder='{"features": ["Feature 1", "Feature 2", "Feature 3"]}'></textarea>
                </div>

                <div class="form-group">
                    <label for="specifications">Specifications (JSON format)</label>
                    <textarea id="specifications" name="specifications" rows="4" placeholder='{"specs": {"key1": "value1", "key2": "value2"}}'></textarea>
                </div>

                <div class="form-group">
                    <label for="image">Product Image</label>
                    <input type="file" id="image" name="image" accept="image/*" required>
                    <div id="image-preview"></div>
                </div>

                <div class="form-actions">
                    <button type="button" onclick="window.location.href='product.php'" class="btn-secondary">Cancel</button>
                    <button type="submit" class="btn-primary">Add Product</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Keep existing image preview script
    </script>
</body>
</html>