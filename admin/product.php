<?php
session_start();
include '../includes/dbconn.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

// Handle product filtering and search
$where_clause = "1=1";
if (isset($_GET['search'])) {
    $search = $conn->real_escape_string($_GET['search']);
    $where_clause .= " AND (name LIKE '%$search%' OR category LIKE '%$search%')";
}

// Pagination
$items_per_page = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start_from = ($page - 1) * $items_per_page;

// Get total products count
$total_query = "SELECT COUNT(*) as count FROM products WHERE $where_clause";
$total_result = $conn->query($total_query);
$total_products = $total_result->fetch_assoc()['count'];
$total_pages = ceil($total_products / $items_per_page);

// Get products with pagination
$query = "SELECT * FROM products WHERE $where_clause ORDER BY id DESC LIMIT $start_from, $items_per_page";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products Management - SnapCart</title>
    <link rel="stylesheet" href="/minor project/admin/css/dashboard.css">
    <link rel="stylesheet" href="/minor project/admin/css/product.css">
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
        <a href="product.php" class="nav-item  active">
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
                <h1 style="color: #FFA500;">Products Management</h1>
                <a href="add_product.php" class="add-product-btn">
                    <i class="fas fa-plus"></i> Add New Product
                </a>
            </div>
            <form method="GET" action="" class="search-form">
            <div class="search-bar" style="margin-right: 20px;">
                <input style="width: 480px; margin-top: +16px; height: 45px;" type="text" name="search" class="search-bar" placeholder="Search products..." 
                       value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                <i class="fas fa-search" style="margin-top: +38px; margin-right: 15px;"></i>
            </div>
            </form>
        </div>

        <div class="products-table">
            <table style="border-collapse: collapse; border: 1px solid #000; width: 100%; box-shadow: 0 4px 8px rgba(0,0,0,0.1); margin-top: 20px;">
                <thead>
                    <tr style="background-color: #f8f9fa; border-bottom: 2px solid #000;">
                        <th style="color: #FFA500; border: 1px solid #000; padding: 12px 15px; text-align: center; font-size: 16px;">ID</th>
                        <th style="color: #FFA500; border: 1px solid #000; padding: 12px 15px; text-align: center; font-size: 16px;">Image</th>
                        <th style="color: #FFA500; border: 1px solid #000; padding: 12px 15px; text-align: center; font-size: 16px;">Name</th>
                        <th style="color: #FFA500; border: 1px solid #000; padding: 12px 15px; text-align: center; font-size: 16px;">Category</th>
                        <th style="color: #FFA500; border: 1px solid #000; padding: 12px 15px; text-align: center; font-size: 16px;">Stock</th>
                        <th style="color: #FFA500; border: 1px solid #000; padding: 12px 15px; text-align: center; font-size: 16px;">Price</th>
                        <th style="color: #FFA500; border: 1px solid #000; padding: 12px 15px; text-align: center; font-size: 16px;">Status</th>
                        <th style="color: #FFA500; border: 1px solid #000; padding: 12px 15px; text-align: center; font-size: 16px;">Actions</th>
                    </tr>
                </thead>
                <tbody style="color:rgb(19, 19, 18);">
                    <?php
                    if ($result->num_rows > 0) {
                        while($row = $result->fetch_assoc()) {
                    ?>
                        <tr style="color:rgb(19, 19, 18); border-bottom: 1px solid #000; transition: background-color 0.3s ease;" onmouseover="this.style.backgroundColor='#f5f5f5'" onmouseout="this.style.backgroundColor=''">
                            <td style="color: #FFA500; border: 1px solid #000; padding: 12px 15px; text-align: center; font-weight: bold;">#<?php echo $row['id']; ?></td>
                            <td style="color:rgb(19, 19, 18); border: 1px solid #000; padding: 12px 15px; text-align: center;">
                                <img src="<?php echo htmlspecialchars($row['image']); ?>" 
                                     alt="<?php echo htmlspecialchars($row['name']); ?>" style="width: 300px; height: 200px; object-fit: cover; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);" 
                                     class="product-thumbnail">
                            </td>
                            <td style="color: #FFA500; border: 1px solid #000; padding: 12px 15px; font-weight: 500;"><?php echo htmlspecialchars($row['name']); ?></td>
                            <td style="color: #FFA500; border: 1px solid #000; padding: 12px 15px; text-align: center;"><?php echo htmlspecialchars($row['category']); ?></td>
                            <td style="border: 1px solid #000; padding: 12px 15px; text-align: center;">
                                <?php 
                                $stockColor = $row['stock_quantity'] < 10 ? '#d32f2f' : '#FFA500';
                                $stockBg = $row['stock_quantity'] < 10 ? '#ffebee' : '#fff3e0';
                                ?>
                                <span style="color: <?php echo $stockColor; ?>; background-color: <?php echo $stockBg; ?>; padding: 5px 10px; border-radius: 20px; font-weight: bold; display: inline-block; min-width: 30px;">
                                    <?php echo $row['stock_quantity']; ?>
                                </span>
                            </td>
                            <td style="color: #FFA500; border: 1px solid #000; padding: 12px 15px; text-align: center; font-weight: bold;">Rs. <?php echo number_format($row['price'], 2); ?></td>
                            <td style="border: 1px solid #000; padding: 12px 15px; text-align: center;">
                                <?php 
                                // Define status colors and styles
                                $statusStyles = [
                                    'active' => ['color' => 'white', 'bg' => '#4caf50', 'border' => 'none'],
                                    'inactive' => ['color' => 'white', 'bg' => '#d32f2f', 'border' => 'none'],
                                    'pending' => ['color' => 'white', 'bg' => '#ff9800', 'border' => 'none'],
                                    'out_of_stock' => ['color' => 'white', 'bg' => '#9e9e9e', 'border' => 'none']
                                ];
                                
                                // Get status from database or default to 'active'
                                $status = isset($row['status']) ? strtolower($row['status']) : 'active';
                                
                                // Use default style if status not in our defined styles
                                if (!isset($statusStyles[$status])) {
                                    $status = 'active';
                                }
                                
                                $style = $statusStyles[$status];
                                $statusText = ucfirst($status);
                                
                                echo '<span style="background-color: ' . $style['bg'] . '; 
                                             color: ' . $style['color'] . '; 
                                             padding: 5px 10px; 
                                             border-radius: 20px; 
                                             font-weight: bold; 
                                             display: inline-block; 
                                             min-width: 80px;
                                             border: ' . $style['border'] . ';">';
                                echo $statusText . '</span>';
                                ?>
                            </td>
                            <td style="border: 1px solid #000; padding: 12px 15px; text-align: center;">
                                <div class="action-buttons" style="display: flex; justify-content: center; gap: 10px;">
                                    <a href="edit_product.php?id=<?php echo $row['id']; ?>" class="edit-button" style="background-color: #fff8e1; color: #f57c00; border: none; padding: 8px 12px; border-radius: 4px; text-decoration: none; display: inline-flex; align-items: center; box-shadow: 0 2px 4px rgba(0,0,0,0.1); transition: all 0.2s ease;">
                                        <i class="fas fa-edit" style="margin-right: 5px;"></i> Edit
                                    </a>
                                    <button onclick="deleteProduct(<?php echo $row['id']; ?>)" class="delete-button" style="background-color: #ffebee; color: #d32f2f; border: none; padding: 8px 12px; border-radius: 4px; cursor: pointer; display: inline-flex; align-items: center; box-shadow: 0 2px 4px rgba(0,0,0,0.1); transition: all 0.2s ease;">
                                        <i class="fas fa-trash" style="margin-right: 5px;"></i> Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php
                        }
                    } else {
                        echo "<tr><td colspan='8' class='no-results' style='border: 1px solid #000; padding: 20px; text-align: center; color: #666;'>No products found</td></tr>";
                    }
                    ?>
                </tbody>
            </table>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="?page=<?php echo $i; ?><?php echo isset($_GET['search']) ? '&search='.urlencode($_GET['search']) : ''; ?>" 
                       class="<?php echo $page === $i ? 'active' : ''; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
    function deleteProduct(id) {
        if(confirm('Are you sure you want to delete this product?')) {
            fetch('delete_product.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ id: id })
            })
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    location.reload();
                } else {
                    alert('Error deleting product: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while deleting the product');
            });
        }
    }
    </script>
</body>
</html>
