<?php
session_start();
include '../includes/dbconn.php';

// Check admin authentication
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

// Get date range filters
$start_date = $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days'));
$end_date = $_GET['end_date'] ?? date('Y-m-d');

// Calculate previous period for comparison
$date_diff = strtotime($end_date) - strtotime($start_date);
$prev_end_date = date('Y-m-d', strtotime($start_date) - 1);
$prev_start_date = date('Y-m-d', strtotime($prev_end_date) - $date_diff);

// Fetch current period statistics
$stats_query = "SELECT 
    COUNT(DISTINCT o.id) as total_orders,
    SUM(o.total_price) as total_revenue,
    COUNT(DISTINCT o.user_id) as total_customers,
    AVG(o.total_price) as average_order_value
FROM orders o 
WHERE o.created_at BETWEEN ? AND ?";

$stats_stmt = $conn->prepare($stats_query);
$stats_stmt->bind_param("ss", $start_date, $end_date);
$stats_stmt->execute();
$stats = $stats_stmt->get_result()->fetch_assoc();

// Fetch previous period statistics for comparison
$prev_stats_stmt = $conn->prepare($stats_query);
$prev_stats_stmt->bind_param("ss", $prev_start_date, $prev_end_date);
$prev_stats_stmt->execute();
$prev_stats = $prev_stats_stmt->get_result()->fetch_assoc();

// Calculate percentage changes
$revenue_change = $prev_stats['total_revenue'] > 0 ? 
    (($stats['total_revenue'] - $prev_stats['total_revenue']) / $prev_stats['total_revenue']) * 100 : 0;
$orders_change = $prev_stats['total_orders'] > 0 ? 
    (($stats['total_orders'] - $prev_stats['total_orders']) / $prev_stats['total_orders']) * 100 : 0;
$customers_change = $prev_stats['total_customers'] > 0 ? 
    (($stats['total_customers'] - $prev_stats['total_customers']) / $prev_stats['total_customers']) * 100 : 0;
$aov_change = $prev_stats['average_order_value'] > 0 ? 
    (($stats['average_order_value'] - $prev_stats['average_order_value']) / $prev_stats['average_order_value']) * 100 : 0;

// Monthly Sales Trend
$monthly_sales_query = "
    SELECT 
        DATE_FORMAT(created_at, '%Y-%m') as month,
        DATE_FORMAT(created_at, '%b %Y') as month_name,
        COUNT(id) as order_count,
        SUM(total_price) as revenue
    FROM orders
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
    GROUP BY DATE_FORMAT(created_at, '%Y-%m'), DATE_FORMAT(created_at, '%b %Y')
    ORDER BY month ASC";

$monthly_sales_result = $conn->query($monthly_sales_query);
$months = [];
$sales_data = [];
$orders_data = [];

while ($row = $monthly_sales_result->fetch_assoc()) {
    $months[] = $row['month_name'];
    $sales_data[] = $row['revenue'];
    $orders_data[] = $row['order_count'];
}

// Top Selling Products
$top_products_query = "
    SELECT 
        p.id,
        p.name,
        p.image,
        SUM(oi.quantity) as total_quantity,
        SUM(oi.quantity * oi.price) as total_revenue,
        COUNT(DISTINCT o.id) as order_count,
        CASE 
            WHEN SUM(oi.quantity) > 10 THEN 'up'
            ELSE 'down'
        END as trend
    FROM order_items oi
    JOIN products p ON p.id = oi.product_id
    JOIN orders o ON o.id = oi.order_id
    WHERE o.created_at BETWEEN ? AND ?
    GROUP BY p.id
    ORDER BY total_revenue DESC
    LIMIT 10";

$top_products_stmt = $conn->prepare($top_products_query);
$top_products_stmt->bind_param("ss", $start_date, $end_date);
$top_products_stmt->execute();
$top_products_result = $top_products_stmt->get_result();

// Payment Method Distribution
$payment_methods_query = "
    SELECT 
        payment_method,
        COUNT(*) as count,
        SUM(total_price) as total
    FROM orders
    WHERE created_at BETWEEN ? AND ?
    GROUP BY payment_method";

$payment_methods_stmt = $conn->prepare($payment_methods_query);
$payment_methods_stmt->bind_param("ss", $start_date, $end_date);
$payment_methods_stmt->execute();
$payment_methods_result = $payment_methods_stmt->get_result();

$payment_methods = [];
$payment_data = [];

while ($row = $payment_methods_result->fetch_assoc()) {
    $payment_methods[] = $row['payment_method'];
    $payment_data[] = $row['total'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - Admin Dashboard</title>
    <link rel="stylesheet" href="/minor project/admin/css/dashboard.css">
    <link rel="stylesheet" href="/minor project/admin/css/report.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <style>
        .report-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-top: 20px;
        }
        
        .report-card {
            background: #2c3e50;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }
        
        .product-image {
            width: 40px;
            height: 40px;
            border-radius: 4px;
            object-fit: cover;
            margin-right: 10px;
        }
        
        .product-info {
            display: flex;
            align-items: center;
        }
        
        .date-range-info {
            background: #34495e;
            padding: 10px 15px;
            border-radius: 4px;
            margin-bottom: 20px;
            font-size: 14px;
            color: #ecf0f1;
        }
        
        .export-options {
            position: absolute;
            background: #2c3e50;
            border-radius: 4px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            padding: 10px;
            display: none;
            z-index: 100;
            right: 0;
            top: 40px;
        }
        
        .export-option {
            padding: 8px 15px;
            cursor: pointer;
            transition: background 0.3s;
            display: block;
            text-align: left;
            width: 100%;
            border: none;
            background: none;
            color: white;
        }
        
        .export-option:hover {
            background: #3d5a80;
        }
        
        .export-container {
            position: relative;
        }
        
        .show {
            display: block;
        }
        
        @media print {
            .sidebar, .export-btn, .report-filters {
                display: none !important;
            }
            
            body, .main-content {
                background: white !important;
                color: black !important;
            }
            
            .report-card {
                break-inside: avoid;
                background: white !important;
                color: black !important;
                box-shadow: none !important;
            }
        }
    </style>
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
        <a href="customers.php" class="nav-item">
            <i class="fas fa-users"></i>
            <span>Customers</span>
        </a>
        <a href="report.php" class="nav-item active">
            <i class="fas fa-file-alt"></i>
            <span>Reports</span>
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

    <div class="main-content" id="report-content">
        <div class="report-container">
            <div class="report-header">
                <h1>Sales Reports</h1>
                <div class="export-container">
                    <button class="export-btn" onclick="toggleExportOptions()">
                        <i class="fas fa-download"></i> Export Report
                    </button>
                    <div class="export-options" id="exportOptions">
                        <button class="export-option" onclick="exportReportPDF()">
                            <i class="fas fa-file-pdf"></i> Export as PDF
                        </button>
                        <button class="export-option" onclick="printReport()">
                            <i class="fas fa-print"></i> Print Report
                        </button>
                        <button class="export-option" onclick="exportReportCSV()">
                            <i class="fas fa-file-csv"></i> Export as CSV
                        </button>
                    </div>
                </div>
            </div>

            <div class="report-filters">
                <div class="date-filter">
                    <form method="get" action="">
                        <input type="date" class="filter-input" id="start_date" name="start_date" value="<?= $start_date ?>">
                        <span>to</span>
                        <input type="date" class="filter-input" id="end_date" name="end_date" value="<?= $end_date ?>">
                        <button type="submit" class="export-btn">Apply</button>
                    </form>
                </div>
            </div>
            
            <div class="date-range-info">
                Showing data from <strong><?= date('F j, Y', strtotime($start_date)) ?></strong> to <strong><?= date('F j, Y', strtotime($end_date)) ?></strong>
                (Compared to previous period: <strong><?= date('M j', strtotime($prev_start_date)) ?></strong> - <strong><?= date('M j, Y', strtotime($prev_end_date)) ?></strong>)
            </div>

            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-value">₹<?= number_format($stats['total_revenue'] ?? 0, 2) ?></div>
                    <div class="stat-label">Total Revenue</div>
                    <div class="trend-indicator <?= $revenue_change >= 0 ? 'trend-up' : 'trend-down' ?>">
                        <i class="fas fa-arrow-<?= $revenue_change >= 0 ? 'up' : 'down' ?>"></i> <?= abs(round($revenue_change, 1)) ?>%
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-value"><?= $stats['total_orders'] ?? 0 ?></div>
                    <div class="stat-label">Total Orders</div>
                    <div class="trend-indicator <?= $orders_change >= 0 ? 'trend-up' : 'trend-down' ?>">
                        <i class="fas fa-arrow-<?= $orders_change >= 0 ? 'up' : 'down' ?>"></i> <?= abs(round($orders_change, 1)) ?>%
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-value"><?= $stats['total_customers'] ?? 0 ?></div>
                    <div class="stat-label">Total Customers</div>
                    <div class="trend-indicator <?= $customers_change >= 0 ? 'trend-up' : 'trend-down' ?>">
                        <i class="fas fa-arrow-<?= $customers_change >= 0 ? 'up' : 'down' ?>"></i> <?= abs(round($customers_change, 1)) ?>%
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-value">₹<?= number_format($stats['average_order_value'] ?? 0, 2) ?></div>
                    <div class="stat-label">Average Order Value</div>
                    <div class="trend-indicator <?= $aov_change >= 0 ? 'trend-up' : 'trend-down' ?>">
                        <i class="fas fa-arrow-<?= $aov_change >= 0 ? 'up' : 'down' ?>"></i> <?= abs(round($aov_change, 1)) ?>%
                    </div>
                </div>
            </div>

            <div class="report-grid">
                <div class="report-card">
                    <h2>Sales Trend</h2>
                    <div class="chart-container">
                        <canvas id="salesChart"></canvas>
                    </div>
                </div>
                
                <div class="report-card">
                    <h2>Payment Methods</h2>
                    <div class="chart-container">
                        <canvas id="paymentChart"></canvas>
                    </div>
                </div>
            </div>

            <div class="report-card">
                <h2>Top Selling Products</h2>
                <table class="report-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Units Sold</th>
                            <th>Orders</th>
                            <th>Revenue</th>
                            <th>Trend</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($product = $top_products_result->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <div class="product-info">
                                    <?php 
                                    $image_path = !empty($product['image']) ? $product['image'] : '/minor project/assets/images/placeholder.jpg';
                                    if (!preg_match('/^\/minor project\//', $image_path)) {
                                        $image_path = '/minor project/assets/images/' . basename($image_path);
                                    }
                                    ?>
                                    <img src="<?= htmlspecialchars($image_path) ?>" alt="<?= htmlspecialchars($product['name']) ?>" class="product-image" onerror="this.src='/minor project/assets/images/placeholder.jpg'">
                                    <?= htmlspecialchars($product['name']) ?>
                                </div>
                            </td>
                            <td><?= $product['total_quantity'] ?></td>
                            <td><?= $product['order_count'] ?></td>
                            <td>₹<?= number_format($product['total_revenue'], 2) ?></td>
                            <td>
                                <?php if ($product['trend'] == 'up'): ?>
                                    <span class="trend-up"><i class="fas fa-arrow-up"></i></span>
                                <?php else: ?>
                                    <span class="trend-down"><i class="fas fa-arrow-down"></i></span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                        <?php if ($top_products_result->num_rows == 0): ?>
                        <tr>
                            <td colspan="5" class="no-data">No products data available</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        // Initialize charts and load data
        function initializeCharts() {
            // Sales Chart
            const salesCtx = document.getElementById('salesChart').getContext('2d');
            new Chart(salesCtx, {
                type: 'line',
                data: {
                    labels: <?= json_encode($months) ?>,
                    datasets: [{
                        label: 'Revenue',
                        data: <?= json_encode($sales_data) ?>,
                        borderColor: '#ff6d00',
                        backgroundColor: 'rgba(255, 109, 0, 0.1)',
                        tension: 0.4,
                        fill: true,
                        yAxisID: 'y'
                    }, {
                        label: 'Orders',
                        data: <?= json_encode($orders_data) ?>,
                        borderColor: '#2980b9',
                        backgroundColor: 'rgba(41, 128, 185, 0.1)',
                        tension: 0.4,
                        fill: true,
                        yAxisID: 'y1'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            labels: {
                                color: '#ffffff'
                            }
                        },
                        tooltip: {
                            mode: 'index',
                            intersect: false
                        }
                    },
                    scales: {
                        y: {
                            type: 'linear',
                            display: true,
                            position: 'left',
                            title: {
                                display: true,
                                text: 'Revenue (₹)',
                                color: '#ffffff'
                            },
                            ticks: {
                                color: '#ffffff'
                            }
                        },
                        y1: {
                            type: 'linear',
                            display: true,
                            position: 'right',
                            title: {
                                display: true,
                                text: 'Orders',
                                color: '#ffffff'
                            },
                            ticks: {
                                color: '#ffffff'
                            },
                            grid: {
                                drawOnChartArea: false
                            }
                        },
                        x: {
                            ticks: {
                                color: '#ffffff'
                            }
                        }
                    }
                }
            });
            
            // Payment Methods Chart
            const paymentCtx = document.getElementById('paymentChart').getContext('2d');
            new Chart(paymentCtx, {
                type: 'doughnut',
                data: {
                    labels: <?= json_encode($payment_methods) ?>,
                    datasets: [{
                        data: <?= json_encode($payment_data) ?>,
                        backgroundColor: [
                            '#3498db',
                            '#2ecc71',
                            '#e74c3c',
                            '#f39c12',
                            '#9b59b6'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'right',
                            labels: {
                                color: '#ffffff'
                            }
                        }
                    }
                }
            });
        }

        // Toggle export options
        function toggleExportOptions() {
            document.getElementById('exportOptions').classList.toggle('show');
        }
        
        // Close export options when clicking outside
        window.onclick = function(event) {
            if (!event.target.matches('.export-btn')) {
                const dropdowns = document.getElementsByClassName('export-options');
                for (let i = 0; i < dropdowns.length; i++) {
                    const openDropdown = dropdowns[i];
                    if (openDropdown.classList.contains('show')) {
                        openDropdown.classList.remove('show');
                    }
                }
            }
        }

        // Export report as PDF
        function exportReportPDF() {
            const { jsPDF } = window.jspdf;
            
            // Hide export options
            document.getElementById('exportOptions').classList.remove('show');
            
            // Create loading indicator
            const loadingEl = document.createElement('div');
            loadingEl.style.position = 'fixed';
            loadingEl.style.top = '0';
            loadingEl.style.left = '0';
            loadingEl.style.width = '100%';
            loadingEl.style.height = '100%';
            loadingEl.style.backgroundColor = 'rgba(0,0,0,0.7)';
            loadingEl.style.display = 'flex';
            loadingEl.style.justifyContent = 'center';
            loadingEl.style.alignItems = 'center';
            loadingEl.style.zIndex = '9999';
            loadingEl.innerHTML = '<div style="color: white; font-size: 24px;">Generating PDF...</div>';
            document.body.appendChild(loadingEl);
            
            // Wait for loading indicator to be displayed
            setTimeout(() => {
                const reportContent = document.getElementById('report-content');
                
                html2canvas(reportContent, {
                    scale: 1,
                    useCORS: true,
                    logging: false
                }).then(canvas => {
                    const imgData = canvas.toDataURL('image/png');
                    const pdf = new jsPDF('p', 'mm', 'a4');
                    const pdfWidth = pdf.internal.pageSize.getWidth();
                    const pdfHeight = pdf.internal.pageSize.getHeight();
                    const imgWidth = canvas.width;
                    const imgHeight = canvas.height;
                    const ratio = Math.min(pdfWidth / imgWidth, pdfHeight / imgHeight);
                    const imgX = (pdfWidth - imgWidth * ratio) / 2;
                    const imgY = 30;
                    
                    // Add title
                    pdf.setFontSize(18);
                    pdf.text('Sales Report', pdfWidth / 2, 15, { align: 'center' });
                    
                    // Add date range
                    pdf.setFontSize(12);
                    pdf.text(`Period: ${formatDate(start_date)} to ${formatDate(end_date)}`, pdfWidth / 2, 22, { align: 'center' });
                    
                    // Add image
                    pdf.addImage(imgData, 'PNG', imgX, imgY, imgWidth * ratio, imgHeight * ratio);
                    
                    // Save PDF
                    pdf.save(`Sales_Report_${start_date}_to_${end_date}.pdf`);
                    
                    // Remove loading indicator
                    document.body.removeChild(loadingEl);
                });
            }, 100);
        }
        
        // Format date for PDF
        function formatDate(dateString) {
            const date = new Date(dateString);
            return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
        }
        
        // Print report
        function printReport() {
            window.print();
        }
        
        // Export as CSV
        function exportReportCSV() {
            // Create CSV content for top products
            let csvContent = "Product Name,Units Sold,Orders,Revenue\n";
            
            // Get table data
            const table = document.querySelector('.report-table');
            const rows = table.querySelectorAll('tbody tr');
            
            rows.forEach(row => {
                const cells = row.querySelectorAll('td');
                if (cells.length >= 4) {
                    const productName = cells[0].textContent.trim();
                    const unitsSold = cells[1].textContent.trim();
                    const orders = cells[2].textContent.trim();
                    const revenue = cells[3].textContent.trim().replace('₹', '').replace(',', '');
                    
                    csvContent += `"${productName}",${unitsSold},${orders},${revenue}\n`;
                }
            });
            
            // Create download link
            const encodedUri = encodeURI('data:text/csv;charset=utf-8,' + csvContent);
            const link = document.createElement('a');
            link.setAttribute('href', encodedUri);
            link.setAttribute('download', `Top_Products_${start_date}_to_${end_date}.csv`);
            document.body.appendChild(link);
            
            // Trigger download
            link.click();
            
            // Clean up
            document.body.removeChild(link);
        }

        // Initialize on load
        document.addEventListener('DOMContentLoaded', function() {
            initializeCharts();
            
            // Store date values for PDF export
            window.start_date = '<?= $start_date ?>';
            window.end_date = '<?= $end_date ?>';
        });
    </script>
</body>
</html>