<?php
session_start();
include '../includes/dbconn.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

// Get dashboard statistics
$stats = [
    'total_sales' => 0,
    'products_sold' => 0,
    'active_products' => 0,
    'pending_orders' => 0
];

// Get total sales - Count all completed orders regardless of specific status name
$sales_query = "SELECT COALESCE(SUM(total_price), 0) as total FROM orders 
                WHERE status IN ('delivered', 'completed', 'shipped', 'processed')";
$result = $conn->query($sales_query);
if ($result && $row = $result->fetch_assoc()) {
    $stats['total_sales'] = $row['total'] ?: 0; // Use null coalescing to default to 0 if null
}

// Get products sold
$sold_query = "SELECT COUNT(*) as count FROM order_items";
$result = $conn->query($sold_query);
if ($result && $row = $result->fetch_assoc()) {
    $stats['products_sold'] = $row['count'];
}

// Get active products
$products_query = "SELECT COUNT(*) as count FROM products WHERE status = 'active' OR status IS NULL";
$result = $conn->query($products_query);
if ($result && $row = $result->fetch_assoc()) {
    $stats['active_products'] = $row['count'];
}

// Get pending orders
$orders_query = "SELECT COUNT(*) as count FROM orders WHERE status = 'pending'";
$result = $conn->query($orders_query);
if ($result && $row = $result->fetch_assoc()) {
    $stats['pending_orders'] = $row['count'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - SnapCart</title>
    <link rel="stylesheet" href="/minor project/admin/css/dashboard.css">
    <link rel="stylesheet" href="/minor project/assets/css/main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="sidebar">
        <div class="logo">
            <img src="/minor project/assets/images/logo.png" alt="Logo">
            <span>Admin Panel</span>
        </div>
        <a href="dashboard.php" class="nav-item active">
            <i class="fas fa-home"></i>
            <span>Dashboard</span>
        </a>
        <a href="product.php" class="nav-item">
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
                <h1>Dashboard Overview</h1>
                <p class="date"><?php echo date('l, F j, Y'); ?></p>
            </div>
            <div class="header-right">
                <div class="admin-profile">
                    <span>Welcome, <?php echo htmlspecialchars($_SESSION['admin_username']); ?></span>
                    <img src="/minor project/assets/images/admin-avatar.png" alt="Admin" class="avatar">
                </div>
            </div>
        </div>

        <div class="dashboard-grid">
            <div class="stat-card sales">
                <div class="icon">
                    <i class="fas fa-rupee-sign"></i>
                </div>
                <div class="stat-content">
                    <h3>Total Sales</h3>
                    <p class="value">Rs. <?php echo number_format($stats['total_sales'], 2); ?></p>
                    <span class="change positive">+12.5% <i class="fas fa-arrow-up"></i></span>
                </div>
            </div>

            <div class="stat-card products">
                <div class="icon">
                    <i class="fas fa-shopping-bag"></i>
                </div>
                <div class="stat-content">
                    <h3>Products Sold</h3>
                    <p class="value"><?php echo number_format($stats['products_sold']); ?></p>
                    <span class="change positive">+8.2% <i class="fas fa-arrow-up"></i></span>
                </div>
            </div>

            <div class="stat-card inventory">
                <div class="icon">
                    <i class="fas fa-box"></i>
                </div>
                <div class="stat-content">
                    <h3>Active Products</h3>
                    <p class="value"><?php echo number_format($stats['active_products']); ?></p>
                    <span class="change positive">+5.1% <i class="fas fa-arrow-up"></i></span>
                </div>
            </div>

            <div class="stat-card orders">
                <div class="icon">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-content">
                    <h3>Pending Orders</h3>
                    <p class="value"><?php echo number_format($stats['pending_orders']); ?></p>
                    <span class="change negative">-2.4% <i class="fas fa-arrow-down"></i></span>
                </div>
            </div>
        </div>

        <div class="dashboard-content">
            <div class="recent-orders">
                <div class="section-header">
                    <h2>Recent Orders</h2>
                    <a href="orders.php" class="view-all">View All</a>
                </div>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Customer</th>
                                <th>Products</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php
    $orders_query = "SELECT o.*, u.id as user_id, 
                           (SELECT COUNT(*) FROM order_items oi WHERE oi.order_id = o.id) as total_items
                    FROM orders o 
                    JOIN user u ON o.user_id = u.id 
                    ORDER BY o.created_at DESC LIMIT 5";
    $orders_result = $conn->query($orders_query);
    
    if ($orders_result->num_rows > 0) {
        while($order = $orders_result->fetch_assoc()) {
    ?>
        <tr>
            <td>#<?php echo $order['id']; ?></td>
            <td><?php echo htmlspecialchars($order['user_id']); ?></td>
            <td><?php echo $order['total_items']; ?> items</td>
            <td>Rs. <?php echo number_format($order['total_price'], 2); ?></td>
            <td><span class="status <?php echo strtolower($order['status']); ?>"><?php echo ucfirst($order['status']); ?></span></td>
            <td><?php echo date('M j, Y', strtotime($order['created_at'])); ?></td>
        </tr>
    <?php
        }
    } else {
        echo "<tr><td colspan='6' class='no-data'>No recent orders</td></tr>";
    }
    ?>
          
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="quick-actions">
                <div class="section-header">
                    <h2>Quick Actions</h2>
                </div>
                <div class="action-grid">
                    <a href="add_product.php" class="action-card">
                        <i class="fas fa-plus"></i>
                        <span>Add Product</span>
                    </a>
                    <a href="orders.php?status=pending" class="action-card">
                        <i class="fas fa-clipboard-list"></i>
                        <span>Process Orders</span>
                    </a>
                    <a href="stock.php" class="action-card">
                        <i class="fas fa-boxes"></i>
                        <span>Update Stock</span>
                    </a>
                    <a href="report.php" class="action-card">
                        <i class="fas fa-chart-pie"></i>
                        <span>View Reports</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>