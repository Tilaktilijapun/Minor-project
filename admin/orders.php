<?php
session_start();
include '../includes/dbconn.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Add date range filter
$date_filter = "";
if (isset($_GET['start_date']) && isset($_GET['end_date'])) {
    $start_date = $_GET['start_date'];
    $end_date = $_GET['end_date'];
    $date_filter = "WHERE o.created_at BETWEEN '$start_date' AND '$end_date'";
}

// Enhanced order query with more details
// Update the query to include delivery_address
$query = "SELECT o.*, u.username, u.email, u.phone, o.shipping_address,
    GROUP_CONCAT(DISTINCT p.name SEPARATOR '||') as products,
    GROUP_CONCAT(DISTINCT oi.quantity SEPARATOR '||') as quantities,
    GROUP_CONCAT(DISTINCT p.price SEPARATOR '||') as prices,
    GROUP_CONCAT(DISTINCT p.image SEPARATOR '||') as product_images,
    COUNT(DISTINCT oi.id) as total_items,
    MAX(o.created_at) as created_at
    FROM orders o
    LEFT JOIN user u ON o.user_id = u.id
    LEFT JOIN order_items oi ON o.id = oi.order_id
    LEFT JOIN products p ON oi.product_id = p.id
    $date_filter
    GROUP BY o.id
    ORDER BY o.created_at DESC";
$result = $conn->query($query);

// Enhanced statistics with more metrics
$stats_query = "SELECT 
    COUNT(*) as total_orders,
    COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_orders,
    COUNT(CASE WHEN status = 'processing' THEN 1 END) as processing_orders,
    COUNT(CASE WHEN status = 'shipped' THEN 1 END) as shipped_orders,
    COUNT(CASE WHEN status = 'delivered' THEN 1 END) as delivered_orders,
    SUM(total_price) as total_revenue,
    AVG(total_price) as average_order_value,
    COUNT(DISTINCT user_id) as unique_customers,
    SUM(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN total_price ELSE 0 END) as monthly_revenue
    FROM orders";
$stats_result = $conn->query($stats_query);
$stats = $stats_result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Management - Admin Panel</title>
    <link rel="stylesheet" href="/minor project/admin/css/orders.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
</head>
<body>  
        <main class="admin-main">
            <header class="admin-header">
                <div class="header-left">
                    <h1>Order Management</h1>
                    <div class="order-stats">
                        <span class="stat-item">
                            <i class="fas fa-shopping-cart"></i>
                            <div class="stat-details">
                                <span class="stat-label">Total Orders</span>
                                <span class="stat-value"><?= number_format($stats['total_orders']) ?></span>
                            </div>
                        </span>
                        <span class="stat-item">
                            <i class="fas fa-money-bill-wave"></i>
                            <div class="stat-details">
                                <span class="stat-label">Total Revenue</span>
                                <span class="stat-value">रू <?= number_format($stats['total_revenue'], 2) ?></span>
                            </div>
                        </span>
                        <span class="stat-item">
                            <i class="fas fa-chart-line"></i>
                            <div class="stat-details">
                                <span class="stat-label">Avg. Order Value</span>
                                <span class="stat-value">रू <?= number_format($stats['average_order_value'], 2) ?></span>
                            </div>
                        </span>
                        <span class="stat-item">
                            <i class="fas fa-users"></i>
                            <div class="stat-details">
                                <span class="stat-label">Unique Customers</span>
                                <span class="stat-value"><?= number_format($stats['unique_customers']) ?></span>
                            </div>
                        </span>
                    </div>
                </div>
                <div class="header-right">
                    <div class="date-filter">
                        <input type="text" id="dateRange" placeholder="           Date Select">
                        <i class="fas fa-calendar"></i>
                    </div>
                    <div class="search-container" style="height: 50px; margin-top: 19px;">
                        <input type="text" id="orderSearch" placeholder="Search orders...">
                        <i class="fas fa-search"  style="float: right;"></i>
                    </div>
                    <div class="filter-container">
                        <select id="statusFilter">
                            <option value="">All Status (<?= $stats['total_orders'] ?>)</option>
                            <option value="pending">Pending (<?= $stats['pending_orders'] ?>)</option>
                            <option value="processing">Processing (<?= $stats['processing_orders'] ?>)</option>
                            <option value="shipped">Shipped (<?= $stats['shipped_orders'] ?>)</option>
                            <option value="delivered">Delivered (<?= $stats['delivered_orders'] ?>)</option>
                        </select>
                        <select id="sortOrder">
                            <option value="newest">Newest First</option>
                            <option value="oldest">Oldest First</option>
                            <option value="highest">Highest Amount</option>
                            <option value="lowest">Lowest Amount</option>
                        </select>
                        <select id="priceRange">
                            <option value="">Price Range</option>
                            <option value="0-1000">रू 0 - रू 1,000</option>
                            <option value="1000-5000">रू 1,000 - रू 5,000</option>
                            <option value="5000+">रू 5,000+</option>
                        </select>
                    </div>
                </div>
            </header>

            <div class="orders-container">
                <div class="orders-grid">
                    <?php while($order = $result->fetch_assoc()): 
                        $products = explode('||', $order['products']);
                        $quantities = explode('||', $order['quantities']);
                        $prices = explode('||', $order['prices']);
                    ?>
                    <div class="order-card" data-status="<?= htmlspecialchars($order['status']) ?>" 
                         data-amount="<?= htmlspecialchars($order['total_price']) ?>"
                         data-date="<?= htmlspecialchars($order['created_at']) ?>">
                        <div class="order-header">
                            <div class="order-title">
                                <h3>Order #<?= htmlspecialchars($order['id']) ?></h3>
                                <span class="order-date">
                                    <i class="far fa-calendar-alt"></i>
                                    <?= date('M d, Y h:i A', strtotime($order['created_at'])) ?>
                                </span>
                            </div>
                            <span class="status-badge <?= htmlspecialchars($order['status']) ?>">
                                <i class="fas fa-circle"></i>
                                <?= ucfirst(htmlspecialchars($order['status'])) ?>
                            </span>
                        </div>
                        <div class="order-details">
                            // Update the customer info section
                            <div class="customer-info">
                                <h4><i class="fas fa-user-circle"></i> Customer Details</h4>
                                <p><i class="fas fa-user"></i> <?= htmlspecialchars($order['username']) ?></p>
                                <p><i class="fas fa-envelope"></i> <?= htmlspecialchars($order['email']) ?></p>
                                <p><i class="fas fa-phone"></i> <?= htmlspecialchars($order['phone']) ?></p>
                                <p><i class="fas fa-shipping-fast"></i> Delivery Address:</p>
                                <p class="delivery-address"><?= nl2br(htmlspecialchars($order['shipping_address'])) ?></p>
                                <p><i class="fas fa-clock"></i> Last Updated: <?= date('M d, Y h:i A', strtotime($order['created_at'])) ?></p>
                            </div>
                            <div class="order-products">
                                <h4><i class="fas fa-box-open"></i> Order Items (<?= count($products) ?>)</h4>
                                <?php for($i = 0; $i < count($products); $i++): ?>
                                <div class="product-item">
                                    <span class="product-name"><?= htmlspecialchars($products[$i]) ?></span>
                                    <span class="product-quantity">x<?= htmlspecialchars($quantities[$i]) ?></span>
                                    <span class="product-price">रू <?= number_format($prices[$i], 2) ?></span>
                                </div>
                                <?php endfor; ?>
                            </div>
                            <div class="order-total">
                                <div class="total-row">
                                    <span>Subtotal:</span>
                                    <span>रू <?= number_format($order['total_price'] * 0.87, 2) ?></span>
                                </div>
                                <div class="total-row">
                                    <span>Tax (13%):</span>
                                    <span>रू <?= number_format($order['total_price'] * 0.13, 2) ?></span>
                                </div>
                                <div class="total-row grand-total">
                                    <span>Total Amount:</span>
                                    <span>रू <?= number_format($order['total_price'], 2) ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="order-actions">
                            <button class="btn-view" onclick="viewOrder(<?= $order['id'] ?>)">
                                <i class="fas fa-eye"></i> View Details
                            </button>
                            <button class="btn-update" onclick="updateOrderStatus(<?= $order['id'] ?>, '<?= $order['status'] ?>')">
                                <i class="fas fa-edit"></i> Update Status
                            </button>
                            <button class="btn-print" onclick="printOrder(<?= $order['id'] ?>)">
                                <i class="fas fa-print"></i> Print
                            </button>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </main>
    </div>

    <!-- Enhanced Modal -->
    <div id="updateModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-edit"></i> Update Order Status</h2>
                <button class="close-modal" onclick="closeModal()">×</button>
            </div>
            <form id="updateOrderForm">
                <input type="hidden" id="orderId">
                <div class="form-group">
                    <label for="orderStatus">Select New Status</label>
                    <select id="orderStatus" required>
                        <option value="pending">Pending</option>
                        <option value="processing">Processing</option>
                        <option value="shipped">Shipped</option>
                        <option value="delivered">Delivered</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="statusNote">Add Note (Optional)</label>
                    <textarea id="statusNote" rows="3" placeholder="Enter any additional notes..."></textarea>
                </div>
                <div class="modal-actions">
                    <button type="submit" class="btn-save">
                        <i class="fas fa-check"></i> Save Changes
                    </button>
                    <button type="button" class="btn-cancel" onclick="closeModal()">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="/minor project/admin/js/order.js"></script>
    <script>
        // Initialize date picker
        flatpickr("#dateRange", {
            mode: "range",
            dateFormat: "Y-m-d",
            onChange: function(selectedDates, dateStr) {
                if (selectedDates.length === 2) {
                    filterOrders();
                }
            }
        });
    </script>
</body>
</html>

<?php mysqli_close($conn); ?>
