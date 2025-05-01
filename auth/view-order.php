<?php
session_start();
require_once '../includes/dbconn.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: /minor project/auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch all orders for the user
$stmt = $conn->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Get selected order details if an order is selected
$selected_order = null;
$order_items = [];

if (isset($_GET['order_id']) && !empty($_GET['order_id'])) {
    $order_id = $_GET['order_id'];
    
    // Fetch order details
    $stmt = $conn->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $order_id, $user_id);
    $stmt->execute();
    $selected_order = $stmt->get_result()->fetch_assoc();
    
    if ($selected_order) {
        // Fetch order items
        $stmt = $conn->prepare("SELECT oi.*, p.name, p.image, p.description FROM order_items oi 
                               JOIN products p ON oi.product_id = p.id 
                               WHERE oi.order_id = ?");
        $stmt->bind_param("i", $order_id);
        $stmt->execute();
        $order_items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        // Calculate subtotal from order items if not available
        if (!isset($selected_order['subtotal']) || $selected_order['subtotal'] == 0) {
            $subtotal = 0;
            foreach ($order_items as $item) {
                $subtotal += $item['price'] * $item['quantity'];
            }
            $selected_order['subtotal'] = $subtotal;
        }
        
        // Set shipping fee if not available
        if (!isset($selected_order['shipping_fee']) || $selected_order['shipping_fee'] == 0) {
            $selected_order['shipping_fee'] = 40; // Fixed shipping fee of Rs. 40
        }
        
        // Recalculate total price if needed
        if (!isset($selected_order['total_price']) || $selected_order['total_price'] == 0) {
            $selected_order['total_price'] = $selected_order['subtotal'] + $selected_order['shipping_fee'];
            
            // Apply discount if available
            if (isset($selected_order['discount']) && $selected_order['discount'] > 0) {
                $selected_order['total_price'] -= $selected_order['discount'];
            }
            
            // Update the total price in the database
            $update_stmt = $conn->prepare("UPDATE orders SET total_price = ? WHERE id = ?");
            $update_stmt->bind_param("di", $selected_order['total_price'], $order_id);
            $update_stmt->execute();
        }
    }
}

// Generate tracking number if not exists
function generateTrackingNumber($order_id) {
    return 'SC' . str_pad($order_id, 8, '0', STR_PAD_LEFT);
}

// Calculate order progress percentage based on status
function getOrderProgress($status) {
    switch ($status) {
        case 'pending':
            return 25;
        case 'processing':
            return 50;
        case 'shipped':
            return 75;
        case 'delivered':
            return 100;
        case 'cancelled':
            return 0;
        default:
            return 25;
    }
}

// Format date
function formatDate($date) {
    return date('F j, Y', strtotime($date));
}

// Check if order is new (less than 24 hours old)
function isNewOrder($date) {
    $order_time = strtotime($date);
    $current_time = time();
    $diff = $current_time - $order_time;
    
    return $diff < 86400; // 24 hours in seconds
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Orders - SnapCart</title>
    <link rel="stylesheet" href="/minor project/assets/css/view-order.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

</head>
<body>
    <div class="header-nav">
        <div class="header-container">
            <a href="/minor project/index.php" class="logo">Snap<span>Cart</span></a>
            <div class="nav-links">
                <a href="/minor project/index.php">Home</a>
                <a href="/minor project/product/product.php">Products</a>
                <a href="/minor project/cart/view-cart.php">Cart</a>
                <a href="/minor project/auth/account.php">Account</a>
            </div>
        </div>
    </div>

    <div class="container">
        <a href="/minor project/auth/account.php" class="back-button">
            <i class="fas fa-arrow-left"></i> Back to Account
        </a>
        
        <h1 class="page-title">Your Orders</h1>
        
        <div class="orders-container">
            <div class="orders-list">
                <h2 style="color: var(--dark-orange); margin-bottom: 20px;">Order History</h2>
                
                <?php if (empty($orders)): ?>
                    <div class="no-orders">
                        <i class="fas fa-shopping-bag" style="font-size: 40px; color: #ddd; margin-bottom: 20px;"></i>
                        <p>You haven't placed any orders yet.</p>
                        <a href="/minor project/product/product.php" style="color: var(--dark-orange); display: block; margin-top: 15px;">
                            Start Shopping
                        </a>
                    </div>
                <?php else: ?>
                    <?php foreach ($orders as $order): ?>
                        <div class="order-card <?php echo (isset($_GET['order_id']) && $_GET['order_id'] == $order['id']) ? 'active' : ''; ?>" 
                             onclick="window.location.href='?order_id=<?php echo $order['id']; ?>'">
                            <?php if (isNewOrder($order['created_at'])): ?>
                                <span class="order-badge badge-new">New</span>
                            <?php endif; ?>
                            <h3>Order #<?php echo $order['id']; ?></h3>
                            <p>Date: <?php echo date('F j, Y', strtotime($order['created_at'])); ?></p>
                            <p>Total: Rs. <?php echo number_format($order['total_price'], 2); ?></p>
                            <span class="order-status status-<?php echo strtolower($order['status'] ?? 'pending'); ?>">
                                <?php echo ucfirst($order['status'] ?? 'Pending'); ?>
                            </span>
                            <div class="order-actions">
                                <a href="?order_id=<?php echo $order['id']; ?>" class="action-button view-details">
                                    <i class="fas fa-eye"></i> View Details
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            
            <div class="order-details-container">
                <?php if ($selected_order): ?>
                    <div class="order-details-header">
                        <h2>Order #<?php echo $selected_order['id']; ?> Details</h2>
                        <p>Placed on <?php echo date('F j, Y', strtotime($selected_order['created_at'])); ?></p>
                        <span class="order-status status-<?php echo strtolower($selected_order['status'] ?? 'pending'); ?>">
                            <?php echo ucfirst($selected_order['status'] ?? 'Pending'); ?>
                        </span>
                    </div>
                    
                    <!-- Order Tracking Section -->
                    <div class="tracking-section">
                        <h3>Order Status</h3>
                        <div class="tracking-progress">
                            <?php
                            $status = strtolower($selected_order['status'] ?? 'pending');
                            $steps = [
                                'pending' => ['icon' => 'fa-receipt', 'label' => 'Order Placed'],
                                'processing' => ['icon' => 'fa-box', 'label' => 'Processing'],
                                'shipped' => ['icon' => 'fa-shipping-fast', 'label' => 'Shipped'],
                                'delivered' => ['icon' => 'fa-check-circle', 'label' => 'Delivered']
                            ];
                            
                            $current_step = array_search($status, array_keys($steps));
                            if ($current_step === false) $current_step = 0;
                            
                            foreach ($steps as $key => $step):
                                $is_active = array_search($key, array_keys($steps)) <= $current_step;
                            ?>
                                <div class="progress-step">
                                    <div class="step-icon <?php echo $is_active ? 'active' : ''; ?>">
                                        <i class="fas <?php echo $step['icon']; ?>"></i>
                                    </div>
                                    <div class="step-label"><?php echo $step['label']; ?></div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <p style="text-align: center; margin-top: 15px;">
                            <strong>Tracking Number:</strong> 
                            SC<?php echo str_pad($selected_order['id'], 8, '0', STR_PAD_LEFT); ?>
                        </p>
                        
                        <?php if ($status == 'pending' || $status == 'processing'): ?>
                            <p style="text-align: center; color: #666;">
                                Estimated delivery: 
                                <?php 
                                    $delivery_date = date('F j, Y', strtotime($selected_order['created_at'] . ' + 5 days'));
                                    echo $delivery_date;
                                ?>
                            </p>
                        <?php endif; ?>
                    </div>
                    
                    <div class="order-info">
                        <div class="order-info-item">
                            <h4><i class="fas fa-map-marker-alt" style="color: var(--dark-orange); margin-right: 8px;"></i> Shipping Information</h4>
                            <p><strong>Name:</strong> <?php echo htmlspecialchars($selected_order['fullname'] ?? 'Not provided'); ?></p>
                            <p><strong>Address:</strong> <?php echo htmlspecialchars($selected_order['shipping_address']); ?></p>
                            <p><strong>Email:</strong> <?php echo htmlspecialchars($selected_order['email'] ?? 'Not provided'); ?></p>
                            <p><strong>Phone:</strong> <?php echo htmlspecialchars($selected_order['phone'] ?? 'Not provided'); ?></p>
                        </div>
                        
                        <div class="order-info-item">
                            <h4><i class="fas fa-credit-card" style="color: var(--dark-orange); margin-right: 8px;"></i> Payment Information</h4>
                            <p><strong>Method:</strong> <?php echo ucwords(str_replace('_', ' ', $selected_order['payment_method'] ?? 'Cash on Delivery')); ?></p>
                            <p><strong>Status:</strong> Paid</p>
                            <p><strong>Date:</strong> <?php echo date('F j, Y', strtotime($selected_order['created_at'])); ?></p>
                        </div>
                    </div>
                    
                    <div class="order-items-list">
                        <h3 style="color: var(--dark-blue); margin-bottom: 15px; border-bottom: 2px solid var(--dark-orange); padding-bottom: 8px;">
                            <i class="fas fa-shopping-basket" style="color: var(--dark-orange); margin-right: 8px;"></i> 
                            Items in Your Order
                        </h3>
                        
                        <?php foreach ($order_items as $item): ?>
                            <div class="order-item">
                                <img src="<?php echo htmlspecialchars($item['image'] ?? '/minor project/assets/images/placeholder.jpg'); ?>" 
                                     alt="<?php echo htmlspecialchars($item['name']); ?>">
                                <div class="item-details">
                                    <h4><?php echo htmlspecialchars($item['name']); ?></h4>
                                    <p><strong>Quantity:</strong> <?php echo $item['quantity']; ?></p>
                                    <p><strong>Price:</strong> Rs. <?php echo number_format($item['price'], 2); ?></p>
                                    <p><strong>Subtotal:</strong> Rs. <?php echo number_format($item['price'] * $item['quantity'], 2); ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="order-summary">
                        <h3 style="color: var(--dark-blue); margin-bottom: 15px; border-bottom: 2px solid var(--dark-orange); padding-bottom: 8px;">
                            <i class="fas fa-file-invoice-dollar" style="color: var(--dark-orange); margin-right: 8px;"></i> 
                            Order Summary
                        </h3>
                        <div class="summary-row">
                            <span>Subtotal:</span>
                            <span>Rs. <?php echo number_format($selected_order['subtotal'], 2); ?></span>
                        </div>
                        <div class="summary-row">
                            <span>Shipping:</span>
                            <span>Rs. <?php echo number_format($selected_order['shipping_fee'] ?? 40, 2); ?></span>
                        </div>
                        <?php if (isset($selected_order['discount']) && $selected_order['discount'] > 0): ?>
                        <div class="summary-row">
                            <span>Discount:</span>
                            <span>-Rs. <?php echo number_format($selected_order['discount'], 2); ?></span>
                        </div>
                        <?php endif; ?>
                        <div class="summary-row total">
                            <span>Total:</span>
                            <span>Rs. <?php echo number_format($selected_order['total_price'], 2); ?></span>
                        </div>
                    </div>
                    
                    <!-- Additional actions -->
                    <div style="margin-top: 30px; display: flex; gap: 15px; justify-content: center;">
                        <a href="/minor project/index.php" style="background-color: var(--dark-blue); color: white; padding: 10px 20px; border-radius: 5px; text-decoration: none; display: inline-flex; align-items: center;">
                            <i class="fas fa-shopping-cart" style="margin-right: 8px;"></i> Continue Shopping
                        </a>
                        <a href="#" onclick="window.print()" style="background-color: var(--dark-orange); color: white; padding: 10px 20px; border-radius: 5px; text-decoration: none; display: inline-flex; align-items: center;">
                            <i class="fas fa-print" style="margin-right: 8px;"></i> Print Order
                        </a>
                    </div>
                <?php else: ?>
                    <div class="no-order-selected">
                        <i class="fas fa-file-invoice" style="font-size: 50px; color: #ddd; margin-bottom: 20px;"></i>
                        <p>Select an order to view its details</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script>
        // Add smooth scrolling when clicking on order cards
        document.querySelectorAll('.order-card').forEach(card => {
            card.addEventListener('click', function() {
                setTimeout(() => {
                    document.querySelector('.order-details-container').scrollIntoView({
                        behavior: 'smooth'
                    });
                }, 100);
            });
        });
    </script>
</body>
</html>