<?php
session_start();
include '../includes/dbconn.php';

// Check admin authentication
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: /minor project/admin/login.php");
    exit();
}

// Fetch customers data
$customers_query = "SELECT 
    u.*, 
    COUNT(o.id) as total_orders,
    SUM(o.total_price) as total_spent
    FROM user u
    LEFT JOIN orders o ON u.id = o.user_id
    GROUP BY u.id
    ORDER BY total_spent DESC";
$customers_result = $conn->query($customers_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Management</title>
    <link rel="stylesheet" href="/minor project/admin/css/dashboard.css">
    <link rel="stylesheet" href="/minor project/admin/css/customers.css">
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
        <a href="product.php" class="nav-item">
            <i class="fas fa-box"></i>
            <span>Products</span>
        </a>
        <a href="orders.php" class="nav-item">
            <i class="fas fa-shopping-cart"></i>
            <span>Orders</span>
        </a>
        <a href="customers.php" class="nav-item active">
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
        <main class="main-content">
            <header>
                <div class="header-title">
                    <h1 style="color: darkorange; margin-bottom: +20px; margin-top: -4px;">Customer Management</h1>
                </div>
                <div class="search-bar">
                    <input  style="width: 600px; float: right; margin-top: +14px;" type="text" id="customerSearch" placeholder="Search customers...">
                    <i class="fas fa-search" style=" margin-top: +7px;"></i>
                </div>
            </header>

            <div class="customers-grid">
                <div class="customer-filters" >
                    <button class="filter-btn active";>All Customers</button>
                    <button class="filter-btn">Active</button>
                    <button class="filter-btn">New</button>
                    <button class="filter-btn">Inactive</button>
                </div>

                <div class="customer-table">
                    <table style="width: 100%; border-collapse: collapse; border: 1px solid #000; margin-top: 20px; box-shadow: 0 4px 8px rgba(0,0,0,0.1);">
                        <thead>
                            <tr style="background-color: #f8f9fa; border-bottom: 2px solid #000;">
                                <th style="padding: 15px; text-align: left; border: 1px solid #000; color: darkorange; font-size: 16px;">Customer</th>
                                <th style="padding: 15px; text-align: left; border: 1px solid #000; color: darkorange; font-size: 16px;">Email</th>
                                <th style="padding: 15px; text-align: center; border: 1px solid #000; color: darkorange; font-size: 16px;">Total Orders</th>
                                <th style="padding: 15px; text-align: center; border: 1px solid #000; color: darkorange; font-size: 16px;">Total Spent</th>
                                <th style="padding: 15px; text-align: center; border: 1px solid #000; color: darkorange; font-size: 16px;">Status</th>
                                <th style="padding: 15px; text-align: center; border: 1px solid #000; color: darkorange; font-size: 16px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($customer = $customers_result->fetch_assoc()): ?>
                            <tr style="border-bottom: 10px solid #000; transition: all 0.3s ease;" onmouseover="this.style.backgroundColor='#f5f5f5'" onmouseout="this.style.backgroundColor=''" >
                                <td class="customer-info" style="padding: 15px; border: 1px solid #000; margin-top: -3px; margin-bottom: -3px;">
                                    <div style="display: flex; align-items: center;">
                                        <div style="width: 45px; height: 40px; background-color: darkorange; border-radius: 50%; display: flex; margin-bottom: 3px; align-items: center; justify-content: center; margin-right: 8px; font-weight: bold; color: white; border: 2px solid #e65100; box-shadow: 0 2px 5px rgba(0,0,0,0.2);">
                                            <?= strtoupper(substr($customer['username'], 0, 1)) ?>
                                        </div>
                                        <div>
                                            <h4 style="margin: 0; color: #333; font-size: 17px; font-weight: 600;"><?= htmlspecialchars($customer['username']) ?></h4>
                                            <span style="color: #666; font-size: 13px;">Customer ID: #<?= $customer['id'] ?></span>
                                        </div>
                                    </div>
                                </td>
                                <td style="padding: 15px; border: 1px solid #000; color: #444;">
                                    <div style="display: flex; align-items: center;">
                                        <i class="fas fa-envelope" style="color: #888; margin-right: 8px;"></i>
                                        <?= htmlspecialchars($customer['email']) ?>
                                    </div>
                                </td>
                                <td style="padding: 15px; border: 1px solid #000; font-weight: 500; text-align: center; font-size: 16px;">
                                    <span style="background-color: #e3f2fd; color: #1976d2; padding: 5px 10px; border-radius: 20px; display: inline-block; min-width: 30px;">
                                        <?= number_format($customer['total_orders']) ?>
                                    </span>
                                </td>
                                <td style="padding: 15px; border: 1px solid #000; font-weight: 600; color: #e65100; text-align: center; font-size: 16px;">
                                    रू <?= number_format($customer['total_spent'], 2) ?>
                                </td>
                                <td style="padding: 15px; border: 1px solid #000; text-align: center;">
                                    <?php 
                                    $statusColor = 'green';
                                    $status = $customer['status'] ?? 'active';
                                    if ($status == 'inactive') $statusColor = '#d32f2f';
                                    if ($status == 'new') $statusColor = '#1976d2';
                                    ?>
                                    <span class="status-badge <?= $status ?>" style="background-color: <?= $statusColor ?>; color: white; padding: 6px 12px; border-radius: 20px; font-size: 13px; font-weight: 500; display: inline-block; min-width: 80px;">
                                        <?= ucfirst($status) ?>
                                    </span>
                                </td>
                                <td class="actions" style="padding: 15px; border: 1px solid #000; text-align: center;">
                                    <div style="display: flex; justify-content: center; gap: 10px;">
                                        <button class="action-btn view-btn" title="View Details" style="background-color: #e3f2fd; color: #1976d2; border: none; width: 40px; height: 40px; border-radius: 8px; cursor: pointer; transition: all 0.2s ease; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="action-btn edit-btn" title="Edit Customer" style="background-color: #fff8e1; color: #f57c00; border: none; width: 40px; height: 40px; border-radius: 8px; cursor: pointer; transition: all 0.2s ease; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="action-btn delete-btn" title="Delete Customer" style="background-color: #ffebee; color: #d32f2f; border: none; width: 40px; height: 40px; border-radius: 8px; cursor: pointer; transition: all 0.2s ease; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Search functionality
        document.getElementById('customerSearch').addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const rows = document.querySelectorAll('tbody tr');
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchTerm) ? '' : 'none';
            });
        });

        // Filter buttons
        document.querySelectorAll('.filter-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                document.querySelector('.filter-btn.active').classList.remove('active');
                this.classList.add('active');
            });
        });
    </script>
</body>
</html>