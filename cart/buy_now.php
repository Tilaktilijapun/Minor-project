<?php
session_start();
include '../includes/dbconn.php';

// Redirect if cart is empty
if (empty($_SESSION['cart'])) {
    header('Location: /minor project/product/product.php');
    exit;
}

// Generate CSRF token if not exists
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Calculate cart totals
$cart_totals = [
    'items_count' => 0,
    'subtotal' => 0,
    'shipping_fee' => 40, // Default shipping fee
    'total' => 0
];

// Get shipping fee from database if available
$fee_result = $conn->query("SELECT value FROM settings WHERE name = 'shipping_fee'");
if ($fee_result && $fee_result->num_rows > 0) {
    $row = $fee_result->fetch_assoc();
    $cart_totals['shipping_fee'] = (float)$row['value'];
}

// Calculate totals
foreach ($_SESSION['cart'] as $product_id => $item) {
    $cart_totals['items_count'] += $item['quantity'];
    $cart_totals['subtotal'] += $item['price'] * $item['quantity'];
}

// Always apply the shipping fee (remove the conditional that makes it free)
// $cart_totals['shipping_fee'] = ($cart_totals['subtotal'] < 1000) ? $cart_totals['shipping_fee'] : 0;

// Calculate total with shipping fee
$cart_totals['total'] = $cart_totals['subtotal'] + $cart_totals['shipping_fee'];

// Apply coupon if exists
if (isset($_SESSION['applied_coupon'])) {
    $discount = $_SESSION['applied_coupon']['discount_amount'];
    $cart_totals['total'] = max(0, $cart_totals['total'] - $discount);
}

// Process order submission
// Add this code near the top of the file after session_start() and database connection
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {
    try {
        // Start transaction
        $conn->begin_transaction();
        
        // Get form data
        $fullname = $_POST['fullname'];
        $email = $_POST['email'];
        $phone = $_POST['phone'];
        $street = $_POST['street'];
        $city = $_POST['city'];
        $state = $_POST['state'];
        $payment_method = $_POST['payment_method'];
        
        // Format shipping address
        $shipping_address = "$street, $city, $state";
        
        // Calculate subtotal
        $subtotal = 0;
        foreach ($_SESSION['cart'] as $item) {
            $subtotal += $item['price'] * $item['quantity'];
        }
        
        // Create order record
        $stmt = $conn->prepare("INSERT INTO orders (user_id, subtotal, total_price, shipping_fee, status, payment_method, 
                               fullname, email, phone, shipping_address, created_at) 
                               VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
        
        $user_id = $_SESSION['user_id'];
        $status = 'pending';
        $shipping_fee = $cart_totals['shipping_fee'];
        $total_price = $cart_totals['total'];
        
        $stmt->bind_param("iddsssssss", $user_id, $subtotal, $total_price, $shipping_fee, 
                         $status, $payment_method, $fullname, $email, $phone, $shipping_address);
        $stmt->execute();
        
        $order_id = $conn->insert_id;
        
        // Insert order items
        $stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
        
        foreach ($_SESSION['cart'] as $product_id => $item) {
            $stmt->bind_param("iiid", $order_id, $product_id, $item['quantity'], $item['price']);
            $stmt->execute();
            
            // Update product stock
            $conn->query("UPDATE products SET stock = stock - {$item['quantity']} WHERE id = {$product_id}");
        }
        
        // Commit transaction
        $conn->commit();
        
        // Store order ID in session for confirmation page
        $_SESSION['order_id'] = $order_id;
        
        // Set a flag to indicate this order came from buy_now
        $_SESSION['buy_now_order'] = true;
        
        // Redirect to confirmation page
        header("Location: /minor project/cart/order-confirmation.php?order_id=$order_id");
        exit();
        
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        $error_message = "Error processing order: " . $e->getMessage();
        // Log error for debugging
        error_log($error_message);
    }
}

// Get user information if logged in
$user_info = [
    'fullname' => '',
    'email' => '',
    'phone' => '',
    'street' => '',
    'city' => '',
    'state' => '',
];

if (isset($_SESSION['user_id'])) {
    $stmt = $conn->prepare("SELECT fullname, email, phone, street, city, state FROM user WHERE id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        $user_info = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Complete Your Order</title>
    <link rel="stylesheet" href="/minor project/assets/css/buy_now.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <header class="navbar">
        <div class="container">
            <a href="/minor project/index.php" class="logo">
                <img src="/minor project/assets/images/logo.png" alt="Logo" height="65" width="65">
            </a>
            <nav>
                <ul>
                    <li><a href="/minor project/index.php">Home</a></li>
                    <li><a href="/minor project/product/product.php">Products</a></li>
                    <li><a href="/minor project/cart/cart.php">Cart</a></li>
                    <li><a href="/minor project/aboutus.php">About</a></li>
                    <li><a href="/minor project/contact.php">Contact</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main class="container">
        <div class="checkout-header">
            <h1>Checkout</h1>
            <div class="checkout-steps">
                <div class="step active">
                    <span class="step-number">1</span>
                    <span class="step-name">Shipping</span>
                </div>
                <div class="step-divider"></div>
                <div class="step active">
                    <span class="step-number">2</span>
                    <span class="step-name">Payment</span>
                </div>
                <div class="step-divider"></div>
                <div class="step">
                    <span class="step-number">3</span>
                    <span class="step-name">Confirmation</span>
                </div>
            </div>
        </div>

        <?php if (isset($error_message)): ?>
            <div class="error-message">
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <div class="checkout-grid">
            <div class="checkout-left">
                <form id="checkout-form" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    
                    <div class="checkout-card">
                        <h2>Shipping Information</h2>
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="fullname">Full Name</label>
                                <input type="text" id="fullname" name="fullname" value="<?php echo htmlspecialchars($user_info['fullname']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user_info['email']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="phone">Phone Number</label>
                                <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($user_info['phone']); ?>" required>
                            </div>
                            <div class="form-group full-width">
                                <label for="street">street</label>
                                <input type="text" id="street" name="street" value="<?php echo htmlspecialchars($user_info['street']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="city">City</label>
                                <input type="text" id="city" name="city" value="<?php echo htmlspecialchars($user_info['city']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="state">State/Province</label>
                                <input type="text" id="state" name="state" value="<?php echo htmlspecialchars($user_info['state']); ?>" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="checkout-card">
                        <h2>Payment Method</h2>
                        <div class="payment-methods">
                            <div class="payment-method">
                                <input type="radio" id="cash_on_delivery" name="payment_method" value="cash_on_delivery" checked>
                                <label for="cash_on_delivery">
                                    <span class="radio-button"></span>
                                    <div class="payment-info">
                                        <h3>Cash on Delivery</h3>
                                        <p>Pay when you receive your order</p>
                                    </div>
                                </label>
                            </div>
                            <div class="payment-method">
                                <input type="radio" id="credit_card" name="payment_method" value="credit_card">
                                <label for="credit_card">
                                    <span class="radio-button"></span>
                                    <div class="payment-info">
                                        <h3>Credit/Debit Card</h3>
                                        <p>Pay securely with your card</p>
                                    </div>
                                </label>
                            </div>
                            <div class="payment-method">
                                <input type="radio" id="esewa" name="payment_method" value="esewa">
                                <label for="esewa">
                                    <span class="radio-button"></span>
                                    <div class="payment-info">
                                        <h3>eSewa</h3>
                                        <p>Pay using your eSewa account</p>
                                    </div>
                                </label>
                            </div>
                            <div class="payment-method">
                                <input type="radio" id="khalti" name="payment_method" value="khalti">
                                <label for="khalti">
                                    <span class="radio-button"></span>
                                    <div class="payment-info">
                                        <h3>Khalti</h3>
                                        <p>Pay securely with your Khalti wallet</p>
                                    </div>
                                </label>
                            </div>
                            <div class="payment-method">
                                <input type="radio" id="fonepay" name="payment_method" value="fonepay">
                                <label for="fonepay">
                                    <span class="radio-button"></span>
                                    <div class="payment-info">
                                        <h3>Fonepay</h3>
                                        <p>Quick payment via Fonepay QR</p>
                                    </div>
                                </label>
                            </div>
                        </div>
                        
                        <div id="credit_card_details" class="payment-details" style="display: none;">
                            <div class="form-grid">
                                <div class="form-group full-width">
                                    <label for="card_number">Card Number</label>
                                    <input type="text" id="card_number" placeholder="1234 5678 9012 3456">
                                </div>
                                <div class="form-group">
                                    <label for="expiry_date">Expiry Date</label>
                                    <input type="text" id="expiry_date" placeholder="MM/YY">
                                </div>
                                <div class="form-group">
                                    <label for="cvv">CVV</label>
                                    <input type="text" id="cvv" placeholder="123">
                                </div>
                                <div class="form-group full-width">
                                    <label for="card_name">Name on Card</label>
                                    <input type="text" id="card_name">
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            
            <div class="checkout-right">
                <div class="checkout-card order-summary">
                    <h2>Order Summary</h2>
                    <div class="order-items">
                        <?php foreach ($_SESSION['cart'] as $product_id => $item): ?>
                            <div class="order-item">
                                <div class="item-info">
                                    <div class="item-image">
                                    <img src="<?php echo htmlspecialchars($item['image'] ?? '/minor project/assets/images/placeholder.jpg');  ?>" 
                                    alt="<?php echo htmlspecialchars($item['name']); ?>">
                                        <span class="item-quantity"><?php echo $item['quantity']; ?></span>
                                    </div>
                                    <div class="item-details">
                                        <h3><?php echo htmlspecialchars($item['name']); ?></h3>
                                        <?php if (isset($item['color']) && $item['color'] !== 'Default'): ?>
                                            <p>Color: <?php echo htmlspecialchars($item['color']); ?></p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="item-price">
                                    Rs <?php echo number_format($item['price'] * $item['quantity'], 2); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="summary-divider"></div>
                    
                    <div class="summary-item">
                        <span>Subtotal (<?php echo $cart_totals['items_count']; ?> items)</span>
                        <span>Rs <?php echo number_format($cart_totals['subtotal'], 2); ?></span>
                    </div>
                    <div class="summary-item">
                        <span>Shipping Fee</span>
                        <?php if ($cart_totals['shipping_fee'] > 0): ?>
                            <span>Rs <?php echo number_format($cart_totals['shipping_fee'], 2); ?></span>
                        <?php else: ?>
                            <span class="free-shipping">Free</span>
                        <?php endif; ?>
                    </div>
                    
                    <?php if (isset($_SESSION['applied_coupon'])): ?>
                    <div class="summary-item discount">
                        <span>Discount (<?php echo htmlspecialchars($_SESSION['applied_coupon']['code']); ?>)</span>
                        <span>-Rs <?php echo number_format($_SESSION['applied_coupon']['discount_amount'], 2); ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <div class="summary-total">
                        <span>Total</span>
                        <span>Rs <?php echo number_format($cart_totals['total'], 2); ?></span>
                    </div>
                    
                    <button type="submit" form="checkout-form" name="place_order" class="place-order-button">
                        PLACE ORDER
                    </button>
                    
                    <p class="terms-notice">
                        By placing your order, you agree to our <a href="#">Terms of Service</a> and <a href="#">Privacy Policy</a>.
                    </p>
                </div>
                
                <div class="help-section" style="position: fixed; bottom: 10px; right: 10px; background-color: black; color: white; padding: 10px;">
                    <h3 style="color: white">Need help?</h3>
                    <a href="/minor project/contact.php" class="contact-support" style="background-color:rgb(255, 77, 0)">Contact Support</a>
                </div>
            </div>
        </div>
    </main>
    
    <footer class="footer">
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> SnapCart. All rights reserved.</p>
        </div>
    </footer>

    <script>
        // Show/hide credit card details based on payment method selection
        document.querySelectorAll('input[name="payment_method"]').forEach(input => {
            input.addEventListener('change', function() {
                const creditCardDetails = document.getElementById('credit_card_details');
                if (this.value === 'credit_card') {
                    creditCardDetails.style.display = 'block';
                } else {
                    creditCardDetails.style.display = 'none';
                }
            });
        });
        
        // Function to handle order submission and redirection
        function submitOrder(event) {
            event.preventDefault();
            
            const form = document.getElementById('checkout-form');
            const formData = new FormData(form);
            
            // Validate form
            const requiredFields = ['fullname', 'email', 'phone', 'street', 'city', 'state'];
            let isValid = true;
            
            requiredFields.forEach(field => {
                const input = document.getElementById(field);
                if (!input.value.trim()) {
                    input.classList.add('error');
                    isValid = false;
                } else {
                    input.classList.remove('error');
                }
            });
            
            // Validate email format
            const emailInput = document.getElementById('email');
            const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailPattern.test(emailInput.value)) {
                emailInput.classList.add('error');
                isValid = false;
            }
            
            // Validate phone number (simple validation)
            const phoneInput = document.getElementById('phone');
            if (phoneInput.value.length < 9) {
                phoneInput.classList.add('error');
                isValid = false;
            }
            
            if (!isValid) {
                alert('Please fill in all required fields correctly.');
                return;
            }
            
            // Submit form via AJAX
            fetch(window.location.href, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Redirect to confirmation page
                    window.location.href = '/minor project/cart/order-confirmation.php?order_id=' + data.order_id;
                } else {
                    alert(data.message || 'An error occurred while processing your order.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                // Fallback - submit form normally
                form.submit();
            });
        }
        
        // Form validation
        document.getElementById('checkout-form').addEventListener('submit', function(e) {
            const requiredFields = ['fullname', 'email', 'phone', 'street', 'city', 'state'];
            let isValid = true;
            
            requiredFields.forEach(field => {
                const input = document.getElementById(field);
                if (!input.value.trim()) {
                    input.classList.add('error');
                    isValid = false;
                } else {
                    input.classList.remove('error');
                }
            });
            
            // Validate email format
            const emailInput = document.getElementById('email');
            const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailPattern.test(emailInput.value)) {
                emailInput.classList.add('error');
                isValid = false;
            }
            
            // Validate phone number (simple validation)
            const phoneInput = document.getElementById('phone');
            if (phoneInput.value.length <= 10) {
                phoneInput.classList.add('error');
                isValid = false;
            }
            
            if (!isValid) {
                e.preventDefault();
                alert('Order Placed.');
            }
        });
    </script>
</body>
</html>
