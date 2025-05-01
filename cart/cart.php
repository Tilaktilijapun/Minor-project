<?php
session_start();
include '../includes/dbconn.php';

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

function updateCartItem($conn, $product_id, $quantity) {
    // Update the SQL query to include both image and color columns
    $stmt = $conn->prepare("SELECT id, name, price, stock, image, color FROM products WHERE id = ? AND status = 'active'");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();

    if (!$product || $product['stock'] < $quantity) {
        return ['success' => false, 'message' => 'Invalid quantity or insufficient stock'];
    }

    $_SESSION['cart'][$product_id] = [
        'name' => $product['name'],
        'price' => $product['price'],
        'quantity' => $quantity, // Fixed: Use the passed quantity parameter instead of product quantity
        'image' => $product['image'], // The database already has the full path
        'color' => $product['color']
    ];
    return ['success' => true, 'new_total' => calculateCartTotal()];
}

function calculateCartTotal() {
    $subtotal = 0;
    foreach ($_SESSION['cart'] as $item) {
        $subtotal += $item['price'] * $item['quantity'];
    }
    return $subtotal;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    try {
        switch ($_POST['action']) {
            case 'update':
                $result = updateCartItem(
                    $conn, 
                    filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT),
                    filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT)
                );
                echo json_encode($result);
                break;

            case 'remove':
                $product_id = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
                unset($_SESSION['cart'][$product_id]);
                echo json_encode([
                    'success' => true, 
                    'cart_count' => count($_SESSION['cart']),
                    'new_total' => calculateCartTotal()
                ]);
                break;

            case 'clear':
                $_SESSION['cart'] = [];
                echo json_encode(['success' => true]);
                break;
                
            case 'checkout':
                if (empty($_SESSION['cart'])) {
                    echo json_encode(['success' => false, 'message' => 'Cart is empty']);
                    exit;
                }
                echo json_encode(['success' => true, 'redirect' => '/minor project/cart/buy_now.php']);
                break;
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

// Calculate cart totals
$subtotal = 0;
$shipping_fee = 40;
foreach ($_SESSION['cart'] as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}
$total = $subtotal + $shipping_fee;

// Handle checkout
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['checkout'])) {
    // Process the order and clear the cart
    $_SESSION['cart'] = [];
    header("Location: /minor project/cart/buy_now.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart</title>
    <link rel="stylesheet" href="/minor project/assets/css/cart.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
            /* Existing styles... */
            
            /* Improved delivery location styles */
            .location-info {
                display: flex;
                padding: 15px;
                border-radius: 8px;
                background-color: #f9f9f9;
                margin-top: 10px;
            }
            
            .address-details {
                margin-left: 10px;
            }
            
            .address-title {
                font-weight: 600;
                margin-bottom: 5px;
                color: #333;
            }
            
            .address-text {
                margin-bottom: 5px;
                color: #555;
                line-height: 1.4;
            }
            
            .recipient-name {
                font-weight: 500;
                margin-bottom: 2px;
            }
            
            .recipient-phone {
                color: #666;
                margin-bottom: 8px;
            }
            
            .delivery-estimate {
                display: flex;
                align-items: center;
                margin-top: 8px;
                color: #4CAF50;
                font-size: 0.9em;
            }
            
            .delivery-estimate svg {
                margin-right: 5px;
            }
            
            .address-actions {
                display: flex;
                margin-top: 12px;
                gap: 10px;
            }
            
            .edit-address-btn, .change-address-btn {
                display: flex;
                align-items: center;
                padding: 6px 12px;
                border: 1px solid #ddd;
                background-color: #fff;
                border-radius: 4px;
                cursor: pointer;
                font-size: 0.9em;
                transition: all 0.2s ease;
            }
            
            .edit-address-btn:hover, .change-address-btn:hover {
                background-color: #f5f5f5;
            }
            
            .edit-address-btn svg, .change-address-btn svg {
                margin-right: 5px;
            }
            
            .no-address-text {
                color: #ff6b00;
                font-weight: 500;
                margin-bottom: 12px;
            }
            
            .address-options {
                display: flex;
                flex-direction: column;
                gap: 10px;
            }
            
            .add-address-btn, .use-saved-address-btn {
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 10px 15px;
                border-radius: 4px;
                cursor: pointer;
                font-weight: 500;
                transition: all 0.2s ease;
            }
            
            .add-address-btn {
                background-color: #ff6b00;
                color: white;
                border: none;
            }
            
            .add-address-btn:hover {
                background-color: #e55f00;
            }
            
            .use-saved-address-btn {
                background-color: #fff;
                color: #333;
                border: 1px solid #ddd;
            }
            
            .use-saved-address-btn:hover {
                background-color: #f5f5f5;
            }
            
            .add-address-btn svg, .use-saved-address-btn svg {
                margin-right: 8px;
            }
            
            /* Modal styles */
            .modal {
                display: none;
                position: fixed;
                z-index: 1000;
                left: 0;
                top: 0;
                width: 100%;
                height: 100%;
                overflow: auto;
                background-color: rgba(0,0,0,0.5);
            }
            
            .modal-content {
                background-color: #fff;
                margin: 10% auto;
                width: 90%;
                max-width: 600px;
                border-radius: 8px;
                box-shadow: 0 4px 20px rgba(0,0,0,0.15);
                animation: modalFadeIn 0.3s;
            }
            
            @keyframes modalFadeIn {
                from {opacity: 0; transform: translateY(-20px);}
                to {opacity: 1; transform: translateY(0);}
            }
            
            .modal-header {
                padding: 15px 20px;
                border-bottom: 1px solid #eee;
                display: flex;
                justify-content: space-between;
                align-items: center;
            }
            
            .modal-header h3 {
                margin: 0;
                color: #333;
            }
            
            .close-modal {
                color: #aaa;
                font-size: 24px;
                font-weight: bold;
                cursor: pointer;
            }
            
            .close-modal:hover {
                color: #555;
            }
            
            .modal-body {
                padding: 20px;
                max-height: 400px;
                overflow-y: auto;
            }
            
            .modal-footer {
                padding: 15px 20px;
                border-top: 1px solid #eee;
                display: flex;
                justify-content: flex-end;
                gap: 10px;
            }
            
            .add-new-address-btn, .cancel-btn {
                padding: 8px 15px;
                border-radius: 4px;
                cursor: pointer;
                font-weight: 500;
            }
            
            .add-new-address-btn {
                background-color: #ff6b00;
                color: white;
                border: none;
                display: flex;
                align-items: center;
            }
            
            .add-new-address-btn svg {
                margin-right: 5px;
            }
            
            .add-new-address-btn:hover {
                background-color: #e55f00;
            }
            
            .cancel-btn {
                background-color: #f5f5f5;
                color: #333;
                border: 1px solid #ddd;
            }
            
            .cancel-btn:hover {
                background-color: #eee;
            }
            
            /* Address list styles */
            .address-item {
                display: flex;
                padding: 15px;
                border: 1px solid #eee;
                border-radius: 6px;
                margin-bottom: 15px;
                transition: all 0.2s ease;
            }
            
            .address-item:hover {
                border-color: #ddd;
                box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            }
            
            .address-radio {
                margin-right: 15px;
                display: flex;
                align-items: flex-start;
                padding-top: 5px;
            }
            
            .address-content {
                flex: 1;
            }
            
            .address-name {
                font-weight: 600;
                margin-bottom: 3px;
            }
            
            .address-phone {
                color: #666;
                margin-bottom: 5px;
            }
            
            .address-full {
                color: #555;
                line-height: 1.4;
            }
            
            .default-badge {
                display: inline-block;
                background-color: #e8f5e9;
                color: #2e7d32;
                padding: 2px 8px;
                border-radius: 4px;
                font-size: 0.8em;
                margin-top: 5px;
            }
            
            .address-actions {
                display: flex;
                align-items: center;
                margin-left: 10px;
            }
            
            .address-actions button {
                background-color: #ff6b00;
                color: white;
                border: none;
                padding: 6px 12px;
                border-radius: 4px;
                cursor: pointer;
                font-weight: 500;
            }
            
            .address-actions button:hover {
                background-color: #e55f00;
            }
            
            .loading-spinner, .no-addresses, .error-message {
                text-align: center;
                padding: 20px;
                color: #666;
            }
            
            .error-message {
                color: #f44336;
            }
        </style>
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
                    <li><a href="/minor project/cart/cart.php" class="active">Cart</a></li>
                    <li><a href="/minor project/aboutus.php">About</a></li>
                    <li><a href="/minor project/contact.php">Contact</a></li>
                </ul>
            </nav>
            <div class="cart-icon" onclick="window.location.href='/minor project/cart/view-cart.php'">
                <span class="cart-count"><?php echo count($_SESSION['cart']); ?></span>
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/>
                    <path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/>
                </svg>
            </div>
        </div>
    </header>

    <main class="container">
        <a href="javascript:history.back()" class="back-button">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="15 18 9 12 15 6"/>
            </svg>
            <span>Back</span>
        </a>
        
        <div class="checkout-grid">
            <div class="checkout-left">
                <div class="checkout-card">
                    <h2>Preferred Delivery Option</h2>
                    <div class="delivery-option selected">
                        <div class="delivery-option-left">
                            <div class="radio-button">
                                <input type="radio" id="standard" name="delivery" checked>
                                <div class="radio-inner"></div>
                            </div>
                            <div class="delivery-info">
                                <div class="delivery-title">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#FF6B00" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <rect x="1" y="3" width="15" height="13"></rect>
                                        <polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon>
                                        <circle cx="5.5" cy="18.5" r="2.5"></circle>
                                        <circle cx="18.5" cy="18.5" r="2.5"></circle>
                                    </svg>
                                    <h3>Standard Delivery</h3>
                                </div>
                                <p><?php echo date('d M Y', strtotime('+3 days')) . ' - ' . date('d M Y', strtotime('+6 days')); ?></p>
                            </div>
                        </div>
                        <div class="delivery-price">Rs <?php echo $shipping_fee; ?></div>
                    </div>
                </div>
                
                <div class="checkout-card">
                    <div class="cart-header">
                        <div class="cart-select-all">
                            <input type="checkbox" id="select-all" checked>
                            <label for="select-all">SELECT ALL (<?php echo count($_SESSION['cart']); ?> ITEM(S))</label>
                        </div>
                        <button class="delete-button" id="deleteSelected">DELETE SELECTED</button>
                    </div>
                    
                    <div class="cart-items">
                        <?php if (empty($_SESSION['cart'])): ?>
                            <div class="empty-cart">
                                <p>Your cart is empty</p>
                                <a href="/minor project/index.php" class="continue-shopping">Continue Shopping</a>
                            </div>
                        <?php else: ?>
                            <?php foreach ($_SESSION['cart'] as $product_id => $item): ?>
                                <div class="cart-item" data-product-id="<?php echo $product_id; ?>">
                                    <div class="item-select">
                                        <input type="checkbox" id="item<?php echo $product_id; ?>" checked>
                                    </div>
                                    <div class="item-image">
                                    <img src="<?php echo htmlspecialchars($item['image'] ?? '/minor project/assets/images/placeholder.jpg'); ?>" 
                             alt="<?php echo htmlspecialchars($item['name']); ?>">
                                    </div>
                                    <div class="item-details">
                                        <h3><?php echo htmlspecialchars($item['name']); ?></h3>
                                        <?php if (isset($item['color']) && $item['color'] !== 'Default'): ?>
                                            <p>Color: <?php echo htmlspecialchars($item['color']); ?></p>
                                        <?php endif; ?>
                                        <div class="item-price">
                                            <span class="current-price">Rs <?php echo number_format($item['price'], 2); ?></span>
                                            <?php if (isset($item['original_price'])): ?>
                                                <span class="original-price">Rs <?php echo number_format($item['original_price'], 2); ?></span>
                                                <span class="discount">
                                                    <?php echo number_format(($item['original_price'] - $item['price']) / $item['original_price'] * 100, 2); ?>%
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="quantity-control">
                                            <button class="quantity-btn minus" data-product-id="<?php echo $product_id; ?>">-</button>
                                            <span class="quantity"><?php echo $item['quantity']; ?></span>
                                            <button class="quantity-btn plus" data-product-id="<?php echo $product_id; ?>">+</button>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="checkout-right">
                <div class="checkout-card">
                    <h2>Delivery Location</h2>
                    <div class="location-info <?php echo isset($_SESSION['delivery_address']) ? 'address-set' : ''; ?>">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                            <circle cx="12" cy="10" r="3"></circle>
                        </svg>
                        <div>
                            <?php if (isset($_SESSION['delivery_address'])): ?>
                                <div class="address-details">
                                    <p class="address-title">Delivery Address</p>
                                    <p class="address-text"><?php echo htmlspecialchars($_SESSION['delivery_address']); ?></p>
                                    <?php if (isset($_SESSION['username'])): ?>
                                        <p class="recipient-name"><?php echo htmlspecialchars($_SESSION['username']); ?></p>
                                    <?php endif; ?>
                                    <?php if (isset($_SESSION['phone'])): ?>
                                        <p class="recipient-phone"><?php echo htmlspecialchars($_SESSION['phone']); ?></p>
                                    <?php endif; ?>
                                    <div class="delivery-estimate">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#4CAF50" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <circle cx="12" cy="12" r="10"></circle>
                                            <polyline points="12 6 12 12 16 14"></polyline>
                                        </svg>
                                        <span>Estimated delivery: <?php echo date('d M', strtotime('+3 days')); ?> - <?php echo date('d M Y', strtotime('+6 days')); ?></span>
                                    </div>
                                </div>
                            <?php else: ?>
                                <p class="no-address-text">Please set your delivery address</p>
                            <?php endif; ?>
                            <button class="change-location <?php echo isset($_SESSION['delivery_address']) ? 'edit-btn' : 'add-btn'; ?>" onclick="window.location.href='/minor project/auth/address.php'">
                                <?php echo isset($_SESSION['delivery_address']) ? '<i class="edit-icon"></i>EDIT' : 'ADD ADDRESS'; ?>
                            </button>
                        </div>
                    </div>
                </div>
                
                <div class="checkout-card order-summary">
                    <h2>Order Summary</h2>
                    <div class="summary-item">
                        <span>Subtotal (<?php echo array_sum(array_column($_SESSION['cart'], 'quantity')); ?> items)</span>
                        <span>Rs <?php echo number_format($subtotal, 2); ?></span>
                    </div>
                    <div class="summary-item">
                        <span>Shipping Fee</span>
                        <span>Rs <?php echo number_format($shipping_fee, 2); ?></span>
                    </div>
                    <div class="summary-total">
                        <span>Total</span>
                        <span>Rs <?php echo number_format($total, 2); ?></span>
                    </div>
                    <form id="buynowForm" method="POST" >
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        <button type="submit"  onclick="window.location.href='/minor project/cart/buy_now.php'" class="checkout-button" name="checkout">
                            PROCEED TO CHECKOUT
                        </button>
                    </form>
                </div>
                
                <div class="help-section">
                    <h3>Need help?</h3>
                    <button class="call-button" onclick="window.location.href='/minor project/contact.php'" style="background-color: rgb(255, 77, 0)">Contact Support</button>
                </div>
            </div>
        </div>
    </main>

    <div id="addressSelectorModal" class="modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h3>Select Delivery Address</h3>
                    <span class="close-modal" onclick="closeAddressSelector()">&times;</span>
                </div>
                <div class="modal-body" id="addressList">
                    <!-- Address list will be loaded here -->
                    <div class="loading-spinner">Loading saved addresses...</div>
                </div>
                <div class="modal-footer">
                    <button class="add-new-address-btn" onclick="window.location.href='/minor project/auth/address.php'">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="12" y1="5" x2="12" y2="19"></line>
                            <line x1="5" y1="12" x2="19" y2="12"></line>
                        </svg>
                        Add New Address
                    </button>
                    <button class="cancel-btn" onclick="closeAddressSelector()">Cancel</button>
                </div>
            </div>
        </div>
    
        <script src="/minor project/assets/js/cart.js"></script>
        <script>
            function setAction(actionurl) {
                document.getElementById("buynowForm").action = actionurl;
                return true;
            }
            
            // Address selector functionality
            function showAddressSelector() {
                document.getElementById('addressSelectorModal').style.display = 'block';
                // Load saved addresses
                fetch('/minor project/auth/get_addresses.php')
                    .then(response => response.json())
                    .then(data => {
                        const addressList = document.getElementById('addressList');
                        if (data.success && data.addresses.length > 0) {
                            let addressHtml = '';
                            data.addresses.forEach(address => {
                                addressHtml += `
                                    <div class="address-item">
                                        <div class="address-radio">
                                            <input type="radio" name="selected_address" id="address_${address.id}" value="${address.id}" ${address.is_default ? 'checked' : ''}>
                                            <label for="address_${address.id}"></label>
                                        </div>
                                        <div class="address-content">
                                            <p class="address-name">${address.name}</p>
                                            <p class="address-phone">${address.phone}</p>
                                            <p class="address-full">${address.address_line1}, ${address.address_line2 ? address.address_line2 + ', ' : ''}${address.city}, ${address.state} ${address.postal_code}</p>
                                            ${address.is_default ? '<span class="default-badge">Default</span>' : ''}
                                        </div>
                                        <div class="address-actions">
                                            <button onclick="selectAddress(${address.id})">Use This</button>
                                        </div>
                                    </div>
                                `;
                            });
                            addressList.innerHTML = addressHtml;
                        } else {
                            addressList.innerHTML = '<div class="no-addresses">No saved addresses found. Please add a new address.</div>';
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching addresses:', error);
                        document.getElementById('addressList').innerHTML = '<div class="error-message">Failed to load addresses. Please try again.</div>';
                    });
            }
            
            function closeAddressSelector() {
                document.getElementById('addressSelectorModal').style.display = 'none';
            }
            
            function selectAddress(addressId) {
                fetch('/minor project/auth/set_delivery_address.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `address_id=${addressId}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Refresh the page to show the selected address
                        window.location.reload();
                    } else {
                        alert('Failed to set delivery address: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error setting address:', error);
                    alert('An error occurred while setting the delivery address. Please try again.');
                });
            }
            
            // Close modal when clicking outside of it
            window.onclick = function(event) {
                const modal = document.getElementById('addressSelectorModal');
                if (event.target == modal) {
                    closeAddressSelector();
                }
            }
        </script>
</body>
</html>
