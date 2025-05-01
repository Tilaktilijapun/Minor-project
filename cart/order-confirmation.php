<?php
session_start();
require_once '../includes/dbconn.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: /minor project/auth/login.php");
    exit();
}

// Get order ID from session or URL
if (isset($_SESSION['order_id']) && !empty($_SESSION['order_id'])) {
    $order_id = $_SESSION['order_id'];
} elseif (isset($_GET['order_id']) && !empty($_GET['order_id'])) {
    // Try to get order ID from URL parameter as fallback
    $order_id = $_GET['order_id'];
    // Store in session for future use
    $_SESSION['order_id'] = $order_id;
} elseif (isset($_SESSION['buy_now_order_id']) && !empty($_SESSION['buy_now_order_id'])) {
    // Get order ID from buy_now session variable
    $order_id = $_SESSION['buy_now_order_id'];
    $_SESSION['order_id'] = $order_id;
    // Clear the buy_now session variable after using it
    unset($_SESSION['buy_now_order_id']);
} else {
    // Check if we have the most recent order for this user
    $user_id = $_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT id FROM orders WHERE user_id = ? ORDER BY created_at DESC LIMIT 1");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $latest_order = $result->fetch_assoc();
        $order_id = $latest_order['id'];
        $_SESSION['order_id'] = $order_id;
    } else {
        header("Location: /minor project/cart/cart.php");
        exit();
    }
}

// Fetch order details with more comprehensive information
$stmt = $conn->prepare("SELECT o.*, a.street, a.city, a.state, a.postal_code, a.country, a.phone 
                       FROM orders o 
                       LEFT JOIN addresses a ON o.user_id = a.user_id 
                       WHERE o.id = ?");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

if (!$order) {
    header("Location: /minor project/index.php");
    exit();
}

// Check if subtotal column exists in orders table
$check_subtotal_column = $conn->query("SHOW COLUMNS FROM orders LIKE 'subtotal'");
$has_subtotal_column = $check_subtotal_column->num_rows > 0;

// Fetch order items with product details
$stmt = $conn->prepare("SELECT oi.*, p.name, p.image, p.description, p.stock FROM order_items oi 
                       JOIN products p ON oi.product_id = p.id 
                       WHERE oi.order_id = ?");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$order_items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Update product stock after successful order
foreach ($order_items as $item) {
    $new_stock = max(0, $item['stock'] - $item['quantity']);
    $update_stock = $conn->prepare("UPDATE products SET stock = ? WHERE id = ?");
    $update_stock->bind_param("ii", $new_stock, $item['product_id']);
    $update_stock->execute();
}

// Format shipping address
if (!empty($order['shipping_address'])) {
    $shipping_address = $order['shipping_address'];
} elseif (!empty($order['street']) && !empty($order['city'])) {
    $shipping_address = "{$order['street']}, {$order['city']}, {$order['state']} {$order['postal_code']}, {$order['country']}";
} else {
    // Fetch address from addresses table if not in order
    $addr_stmt = $conn->prepare("SELECT * FROM addresses WHERE user_id = ?");
    $addr_stmt->bind_param("i", $order['user_id']);
    $addr_stmt->execute();
    $address = $addr_stmt->get_result()->fetch_assoc();
    
    if ($address) {
        $shipping_address = "{$address['street']}, {$address['city']}, {$address['state']} {$address['postal_code']}, {$address['country']}";
        
        // Update order with shipping address for future reference
        $update_order = $conn->prepare("UPDATE orders SET shipping_address = ? WHERE id = ?");
        $update_order->bind_param("si", $shipping_address, $order_id);
        $update_order->execute();
    } else {
        $shipping_address = "No address provided";
    }
}

// Generate order tracking number if not exists
$tracking_number = 'SC' . str_pad($order_id, 8, '0', STR_PAD_LEFT);

// Check if tracking_number column exists in orders table
$check_column = $conn->query("SHOW COLUMNS FROM orders LIKE 'tracking_number'");
if ($check_column->num_rows > 0) {
    // Column exists, update the tracking number
    $update_tracking = $conn->prepare("UPDATE orders SET tracking_number = ? WHERE id = ?");
    $update_tracking->bind_param("si", $tracking_number, $order_id);
    $update_tracking->execute();
} else {
    // Column doesn't exist, we'll just use the generated tracking number without storing it
    // You may want to run this SQL query to add the column:
    // ALTER TABLE orders ADD COLUMN tracking_number VARCHAR(20) AFTER status;
}

// Calculate expected delivery date (5-7 days from order date)
$order_date = new DateTime($order['created_at']);
$min_delivery = clone $order_date;
$min_delivery->modify('+5 days');
$max_delivery = clone $order_date;
$max_delivery->modify('+7 days');
$delivery_estimate = $min_delivery->format('M d') . ' - ' . $max_delivery->format('M d, Y');

// Update order status if needed
if (empty($order['status']) || $order['status'] == 'pending') {
    $update_status = $conn->prepare("UPDATE orders SET status = 'processing' WHERE id = ?");
    $update_status->bind_param("i", $order_id);
    $update_status->execute();
}

// Clear cart after successful order
unset($_SESSION['cart']);
unset($_SESSION['applied_coupon']);

// Calculate order totals
$subtotal_amount = 0;
foreach ($order_items as $item) {
    $subtotal_amount += $item['price'] * $item['quantity'];
}

// If order doesn't have subtotal, update it (only if column exists)
if ($has_subtotal_column && (empty($order['subtotal']) || $order['subtotal'] <= 0)) {
    $update_subtotal = $conn->prepare("UPDATE orders SET subtotal = ? WHERE id = ?");
    $update_subtotal->bind_param("di", $subtotal_amount, $order_id);
    $update_subtotal->execute();
}

// Ensure shipping fee is set
$shipping_fee = $order['shipping_fee'] ?? $order['shipping_cost'] ?? 40;
if (empty($order['shipping_fee']) && empty($order['shipping_cost'])) {
    // Check if shipping_fee column exists
    $check_shipping_column = $conn->query("SHOW COLUMNS FROM orders LIKE 'shipping_fee'");
    if ($check_shipping_column->num_rows > 0) {
        $update_shipping = $conn->prepare("UPDATE orders SET shipping_fee = 40 WHERE id = ?");
        $update_shipping->bind_param("i", $order_id);
        $update_shipping->execute();
    }
    // Ensure the shipping fee is set for calculations
    $shipping_fee = 40;
}

// Calculate total - FORCE include shipping fee regardless of what's in the database
$total = $subtotal_amount + $shipping_fee;

// Add tax if it exists
if (isset($order['tax']) && $order['tax'] > 0) {
    $total += $order['tax'];
}

// Subtract discount if it exists
if (isset($order['discount']) && $order['discount'] > 0) {
    $total -= $order['discount'];
}

// Update total if needed - make sure shipping is included in the calculation
if (empty($order['total_price']) || $order['total_price'] <= 0 || $order['total_price'] != $total) {
    // Check if total_price column exists
    $check_total_column = $conn->query("SHOW COLUMNS FROM orders LIKE 'total_price'");
    if ($check_total_column->num_rows > 0) {
        $update_total = $conn->prepare("UPDATE orders SET total_price = ? WHERE id = ?");
        $update_total->bind_param("di", $total, $order_id);
        $update_total->execute();
    }
}
// At the end of the PHP section, before the closing PHP tag
// Add this code to ensure proper cleanup and refresh

// Make sure we have the latest order data
$refresh_order = $conn->prepare("SELECT * FROM orders WHERE id = ?");
$refresh_order->bind_param("i", $order_id);
$refresh_order->execute();
$latest_order = $refresh_order->get_result()->fetch_assoc();

if ($latest_order) {
    // Update our order variable with the latest data
    $order = array_merge($order, $latest_order);
}

// Clear any buy now related session variables
unset($_SESSION['buy_now_product']);
unset($_SESSION['buy_now_quantity']);
unset($_SESSION['buy_now_price']);
unset($_SESSION['buy_now_order']);

// Set a flag to indicate we've shown the confirmation
$_SESSION['order_confirmed'] = true;

// Add a timestamp to prevent caching issues
$_SESSION['confirmation_time'] = time();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation - SnapCart</title>
    <link rel="stylesheet" href="/minor project/assets/css/order-confirmation.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="confirmation-container">
        <div class="confirmation-header">
            <i class="fas fa-check-circle success-icon" style="color: green"></i>
            <h1>Order Confirmed!</h1>
            <p>Thank you for your purchase. Your order has been successfully placed.</p>
        </div>
        
        <div class="tracking-info">
            <div class="tracking-details">
                <p><strong>Tracking Number:</strong> <?php echo $tracking_number; ?></p>
                <p><strong>Estimated Delivery:</strong> <?php echo $delivery_estimate; ?></p>
            </div>
            
            <div class="order-status">
                <div class="status-step active">
                    <div class="status-icon"><i class="fas fa-check"></i></div>
                    <p>Order Placed</p>
                </div>
                <div class="status-step">
                    <div class="status-icon"><i class="fas fa-box"></i></div>
                    <p>Processing</p>
                </div>
                <div class="status-step">
                    <div class="status-icon"><i class="fas fa-shipping-fast"></i></div>
                    <p>Shipped</p>
                </div>
                <div class="status-step">
                    <div class="status-icon"><i class="fas fa-home"></i></div>
                    <p>Delivered</p>
                </div>
            </div>
        </div>

        <div class="order-details">
            <h2>Order Details</h2>
            <div class="order-info">
                <p><strong>Order ID:</strong> #<?php echo $order['id']; ?></p>
                <p><strong>Order Date:</strong> <?php echo date('F j, Y', strtotime($order['created_at'])); ?></p>
                <p><strong>Payment Method:</strong> <?php echo ucwords(str_replace('_', ' ', $order['payment_method'])); ?></p>
                <p><strong>Shipping Address:</strong> <?php echo htmlspecialchars($shipping_address); ?></p>
            </div>

            <div class="order-items">
                <h3>Items Ordered</h3>
                <?php foreach ($order_items as $item): ?>
                    <div class="order-item">
                        <img src="<?php echo htmlspecialchars($item['image'] ?? '/minor project/assets/images/placeholder.jpg'); ?>" 
                             alt="<?php echo htmlspecialchars($item['name']); ?>">
                        <div class="item-details">
                            <h4><?php echo htmlspecialchars($item['name']); ?></h4>
                            <p><?php echo htmlspecialchars(substr($item['description'] ?? '', 0, 100) . (strlen($item['description'] ?? '') > 100 ? '...' : '')); ?></p>
                            <p>Quantity: <?php echo $item['quantity']; ?></p>
                            <p>Price: Rs. <?php echo number_format($item['price'], 2); ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="order-summary">
                <h3>Order Summary</h3>
                
                <!-- Always use calculated subtotal since column might not exist -->
                <div class="summary-row">
                    <span>Subtotal:</span>
                    <span>Rs. <?php echo number_format($subtotal_amount, 2); ?></span>
                </div>
                
                <div class="summary-row">
                    <span>Shipping:</span>
                    <span>Rs. <?php echo number_format($shipping_fee, 2); ?></span>
                </div>
                
                <?php if (isset($order['tax']) && $order['tax'] > 0): ?>
                <div class="summary-row">
                    <span>Tax:</span>
                    <span>Rs. <?php echo number_format($order['tax'], 2); ?></span>
                </div>
                <?php endif; ?>
                
                <?php if (isset($order['discount']) && $order['discount'] > 0): ?>
                <div class="summary-row discount">
                    <span>Discount:</span>
                    <span>-Rs. <?php echo number_format($order['discount'], 2); ?></span>
                </div>
                <?php endif; ?>
                
                <div class="summary-row total">
                    <span>Total:</span>
                    <span>Rs. <?php 
                        // Ensure shipping fee is always included in the total
                        echo number_format($total, 2); 
                    ?></span>
                </div>
            </div>
        </div>

        <div class="confirmation-actions">
            <a href="/minor project/index.php" class="btn" style="color: darkorange">
                <i class="fas fa-arrow-left" style="color: darkorange"></i> Continue Shopping
            </a>
            <a href="/minor project/auth/view-order.php" class="btn" style="color: darkorange">
                <i class="fas fa-list" style="color: darkorange"></i> View All Orders
            </a>
        </div>

        <div class="support-info">
            <p>Need help? Contact our support team:</p>
            <p><i class="fas fa-envelope" style="color: darkorange"></i> support@snapcart.com</p>
            <p><i class="fas fa-phone" style="color: darkorange"></i> +977 9876543210</p>
        </div>
    </div>

    <script>
        // Clear cart after successful order
        localStorage.removeItem('cart');
    </script>
</body>
</html>