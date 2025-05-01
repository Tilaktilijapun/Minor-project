<?php
session_start();
include '../includes/dbconn.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

// Get product ID
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $conn->real_escape_string($_POST['name']);
    $category = $conn->real_escape_string($_POST['category']);
    $price = (float)$_POST['price'];
    $stock = (int)$_POST['stock_quantity'];
    $status = $conn->real_escape_string($_POST['status']);
    $description = $conn->real_escape_string($_POST['description']);

    // Handle image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        $filename = $_FILES['image']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        if (in_array($ext, $allowed)) {
            $new_filename = uniqid() . '.' . $ext;
            $upload_path = '../uploads/products/' . $new_filename;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                $image_url = '/minor project/uploads/products/' . $new_filename;
                $sql = "UPDATE products SET 
                        name = ?, category = ?, price = ?, 
                        stock_quantity = ?, status = ?, 
                        description = ?, image_url = ?
                        WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ssdiissi", $name, $category, $price, $stock, $status, $description, $image_url, $product_id);
            }
        }
    } else {
        // Update without changing the image
        $sql = "UPDATE products SET 
                name = ?, category = ?, price = ?, 
                stock_quantity = ?, status = ?, 
                description = ?
                WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssdiisi", $name, $category, $price, $stock, $status, $description, $product_id);
    }

    if ($stmt->execute()) {
        header('Location: product.php?success=1');
        exit();
    } else {
        $error = "Error updating product: " . $conn->error;
    }
}

// Get product data
$sql = "SELECT * FROM products WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();

if (!$product) {
    header('Location: product.php?error=product_not_found');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product - SnapCart</title>
    <link rel="stylesheet" href="/minor project/admin/css/dashboard.css">
    <link rel="stylesheet" href="/minor project/admin/css/edit_product.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="sidebar">
        <div class="logo">
            <img src="/minor project/assets/images/logo.png" alt="Logo">
            <span>Admin Panel</span>
        </div>
        <a href="dashboard.php" class="nav-item">
            <i class="fas fa-home"></i>
            <span>Dashboard</span>
        </a>
        <a href="product.php" class="nav-item active">
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
        <a href="stock.php" class="nav-item">
            <i class="fas fa-chart-bar"></i>
            <span>Stocks</span>
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
            <div class="header-left">
                <h1 style="color: #FFA500;">Edit Product</h1>
                <a href="product.php" class="back-btn" style="display: inline-flex; align-items: center; background-color: #f8f9fa; color: #555; padding: 8px 15px; border-radius: 4px; text-decoration: none; font-weight: 500; transition: all 0.3s ease; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                    <i class="fas fa-arrow-left" style="margin-right: 8px;"></i> Back to Products
                </a>
            </div>
        </div>

        <div class="edit-form" style="background-color: white; padding: 25px; border-radius: 8px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); margin-top: 20px; max-width: 900px;">
            <?php if (isset($error)): ?>
                <div class="alert alert-error" style="background-color: #ffebee; color: #d32f2f; padding: 12px 15px; border-radius: 4px; margin-bottom: 20px; border-left: 4px solid #d32f2f;"><?php echo $error; ?></div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">
                <div class="form-group" style="margin-bottom: 20px;">
                    <label for="name" style="display: block; margin-bottom: 8px; font-weight: 500; color: #333;">Product Name</label>
                    <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($product['name']); ?>" required style="width: 100%; padding: 10px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 15px; transition: border-color 0.3s ease;">
                </div>

                <div class="form-group" style="margin-bottom: 20px;">
                    <label for="category" style="display: block; margin-bottom: 8px; font-weight: 500; color: #333;">Category</label>
                    <select id="category" name="category" required style="width: 100%; padding: 10px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 15px; background-color: white;">
                        <option value="electronics" <?php echo $product['category'] === 'electronics' ? 'selected' : ''; ?>>Electronics</option>
                        <option value="clothing" <?php echo $product['category'] === 'clothing' ? 'selected' : ''; ?>>Clothing</option>
                        <option value="books" <?php echo $product['category'] === 'books' ? 'selected' : ''; ?>>Books</option>
                        <!-- Add more categories as needed -->
                    </select>
                </div>

                <div class="form-row" style="display: flex; gap: 20px; margin-bottom: 20px;">
                    <div class="form-group" style="flex: 1;">
                        <label for="price" style="display: block; margin-bottom: 8px; font-weight: 500; color: #333;">Price (Rs.)</label>
                        <input type="number" id="price" name="price" step="0.01" value="<?php echo $product['price']; ?>" required style="width: 100%; padding: 10px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 15px;">
                    </div>

                    <div class="form-group" style="flex: 1;">
                        <label for="stock_quantity" style="display: block; margin-bottom: 8px; font-weight: 500; color: #333;">Stock Quantity</label>
                        <input type="number" id="stock_quantity" name="stock_quantity" value="<?php echo $product['stock_quantity']; ?>" required style="width: 100%; padding: 10px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 15px;">
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 20px;">
                    <label for="status" style="display: block; margin-bottom: 8px; font-weight: 500; color: #333;">Status</label>
                    <div style="display: flex; gap: 15px;">
                        <label style="display: flex; align-items: center; cursor: pointer;">
                            <input type="radio" name="status" value="active" <?php echo $product['status'] === 'active' ? 'checked' : ''; ?> style="margin-right: 8px;">
                            <span style="display: inline-block; padding: 5px 10px; background-color: #e8f5e9; color: #4caf50; border-radius: 20px; font-size: 14px; font-weight: 500;">Active</span>
                        </label>
                        <label style="display: flex; align-items: center; cursor: pointer;">
                            <input type="radio" name="status" value="inactive" <?php echo $product['status'] === 'inactive' ? 'checked' : ''; ?> style="margin-right: 8px;">
                            <span style="display: inline-block; padding: 5px 10px; background-color: #ffebee; color: #d32f2f; border-radius: 20px; font-size: 14px; font-weight: 500;">Inactive</span>
                        </label>
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 20px;">
                    <label for="description" style="display: block; margin-bottom: 8px; font-weight: 500; color: #333;">Description</label>
                    <textarea id="description" name="description" rows="4" style="width: 100%; padding: 10px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 15px; resize: vertical;"><?php echo htmlspecialchars($product['description']); ?></textarea>
                </div>

                <div class="form-group" style="margin-bottom: 25px;">
                    <label for="image" style="display: block; margin-bottom: 8px; font-weight: 500; color: #333;">Product Image</label>
                    <div style="display: flex; align-items: flex-start; gap: 20px;">
                        <?php if ($product['image']): ?>
                            <div class="current-image" style="flex: 0 0 300px;">
                                <img src="<?php echo htmlspecialchars($product['image']); ?>" alt="Current product image" style="width: 100%; height: auto; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                                <p style="margin-top: 8px; color: #666; font-size: 14px; text-align: center;">Current image</p>
                            </div>
                        <?php endif; ?>
                        <div style="flex: 1;">
                            <div style="border: 2px dashed #ddd; padding: 20px; border-radius: 8px; text-align: center; background-color: #f9f9f9;">
                                <i class="fas fa-cloud-upload-alt" style="font-size: 24px; color: #FFA500; margin-bottom: 10px;"></i>
                                <p style="margin: 0 0 10px; color: #666;">Click to select or drag a new image</p>
                                <input type="file" id="image" name="image" accept="image/*" style="width: 100%;">
                                <p style="margin: 10px 0 0; color: #888; font-size: 12px;">Supported formats: JPG, JPEG, PNG, WEBP</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-actions" style="display: flex; gap: 15px; margin-top: 30px;">
                    <button type="submit" class="btn-primary" style="background-color: #FFA500; color: white; border: none; padding: 12px 24px; border-radius: 4px; font-weight: 500; cursor: pointer; transition: background-color 0.3s ease; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">Update Product</button>
                    <a href="product.php" class="btn-secondary" style="background-color: #f5f5f5; color: #333; border: none; padding: 12px 24px; border-radius: 4px; font-weight: 500; text-decoration: none; display: inline-block; text-align: center; transition: background-color 0.3s ease; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
