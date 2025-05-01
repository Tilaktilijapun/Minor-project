<?php
session_start();
include '../includes/dbconn.php';

// Check admin login
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}

// Handle stock updates
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_stock'])) {
    $product_id = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
    $new_stock = filter_input(INPUT_POST, 'new_stock', FILTER_VALIDATE_INT);
    
    if ($product_id && $new_stock !== false) {
        $stmt = $conn->prepare("UPDATE products SET stock = ?, stock_quantity = ? WHERE id = ?");
        $stmt->bind_param("iii", $new_stock, $new_stock, $product_id);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "Stock updated successfully!";
        } else {
            $_SESSION['error'] = "Error updating stock!";
        }
        header("Location: stock.php");
        exit();
    }
}

// Get all products with stock information
$query = "SELECT id, name, category, stock, stock_quantity, price FROM products ORDER BY category, name";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stock Management - Admin Dashboard</title>
    <link rel="stylesheet" href="/minor project/admin/css/stock.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="main-content">
        <div class="header">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <h1 style="color: darkorange;">Stock Management</h1>
                <a href="dashboard.php" style="background-color: #0066cc; color: white; padding: 10px 20px; border-radius: 5px; text-decoration: none; font-weight: bold; box-shadow: 0 2px 5px rgba(0,0,0,0.2);"
                onmouseover="this.style.backgroundColor='blue';"
                onmouseout="this.style.backgroundColor='lightblue';">
                    <i class="fas fa-arrow-left" style="margin-right: 8px;"></i> Back to Dashboard
                </a>
            </div>
            <hr style="border: none; height: 2px; background-color: blue; border-radius: 5px; margin: 20px 0;">

            <div class="stock-filters">
                <select style="background-color: darkorange; color: white; border-radius: 5px;  height: 30px;" id="categoryFilter" class="form-control">
                    <option value="">All Categories</option>
                    <option value="low">Low Stock (≤ 10)</option>
                    <option value="medium">Medium Stock (11-30)</option>
                    <option value="good">Good Stock (> 30)</option>
                </select>
                <input style="width: 760px; border-radius: 5px; padding: 2px; font-size: 16px; margin-bottom: 20px; height: 30px;" id="searchProduct" class="form-control" placeholder="Search products...">
                <button style="background-color: darkorange; color: white; border-radius: 5px; padding: 2px; height: 35px;" type="button" class="btn btn-primary">Search Product</button>
            </div>
        </div>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <?php 
                echo $_SESSION['success'];
                unset($_SESSION['success']);
                ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-error">
                <?php 
                echo $_SESSION['error'];
                unset($_SESSION['error']);
                ?>
            </div>
        <?php endif; ?>

        <div class="content-card">
            <div class="table-responsive">
                <table id="stockTable" style="border-collapse: collapse; border: 1px solid #000;">
                    <thead>
                        <tr style="border-bottom: 2px solid #000;">
                            <th style="border: 1px solid #000;">Product Name</th>
                            <th style="border: 1px solid #000;">Category</th>
                            <th style="border: 1px solid #000;">Current Stock</th>
                            <th style="border: 1px solid #000;">Price (Rs)</th>
                            <th style="border: 1px solid #000;">Status</th>
                            <th style="border: 1px solid #000;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($result)): ?>
                            <tr data-category="<?php echo htmlspecialchars($row['category']); ?>" style="border-bottom: 1px solid #000;">
                                <td style="border: 1px solid #000;"><?php echo htmlspecialchars($row['name']); ?></td>
                                <td style="border: 1px solid #000;"><?php echo htmlspecialchars($row['category']); ?></td>
                                <td style="border: 1px solid #000;"><?php echo $row['stock']; ?></td>
                                <td style="border: 1px solid #000;">Rs. <?php echo number_format($row['price'], 2); ?></td>
                                <td style="border: 1px solid #000;">
                                    <?php
                                    $status_class = '';
                                    $status_color = '';
                                    if ($row['stock'] <= 10) {
                                        $status_class = 'status-low';
                                        $status_text = 'Low Stock';
                                        $status_color = '#FF0000'; // Red color
                                    } elseif ($row['stock'] <= 30) {
                                        $status_class = 'status-medium';
                                        $status_text = 'Medium Stock';
                                        $status_color = '#FFA500'; // Orange color
                                    } else {
                                        $status_class = 'status-good';
                                        $status_text = 'Good Stock';
                                        $status_color = '#008000'; // Green color
                                    }
                                    ?>
                                    <span class="stock-status <?php echo $status_class; ?>" style="background-color: <?php echo $status_color; ?>; color: white; padding: 5px 10px; border-radius: 4px; font-weight: bold;">
                                        <?php echo $status_text; ?>
                                    </span>
                                </td>
                                <td style="border: 1px solid #000;">
                                    <form method="POST" class="quick-update">
                                        <input type="hidden" name="product_id" value="<?php echo $row['id']; ?>">
                                        <input type="number" name="new_stock" value="<?php echo $row['stock']; ?>" min="0" required>
                                        <button type="submit" name="update_stock" class="btn btn-primary" style="background-color: darkorange; color: white; border-radius: 5px;">Update</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        // Search functionality
        document.getElementById('searchProduct').addEventListener('input', function(e) {
            const searchText = e.target.value.toLowerCase();
            const rows = document.querySelectorAll('#stockTable tbody tr');
            
            rows.forEach(row => {
                const productName = row.cells[0].textContent.toLowerCase();
                const category = row.cells[1].textContent.toLowerCase();
                row.style.display = (productName.includes(searchText) || category.includes(searchText)) ? '' : 'none';
            });
        });

        // Category filter
        document.getElementById('categoryFilter').addEventListener('change', function(e) {
            const filterValue = e.target.value;
            const rows = document.querySelectorAll('#stockTable tbody tr');
            
            rows.forEach(row => {
                const stock = parseInt(row.cells[2].textContent);
                let show = true;

                if (filterValue === 'low') {
                    show = stock <= 10;
                } else if (filterValue === 'medium') {
                    show = stock > 10 && stock <= 30;
                } else if (filterValue === 'good') {
                    show = stock > 30;
                }

                row.style.display = show ? '' : 'none';
            });
        });
    </script>
</body>
</html>

<?php 
mysqli_close($conn); 
?>
