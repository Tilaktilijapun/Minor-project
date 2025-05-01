<?php
session_start();
include '../includes/dbconn.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: /minor project/auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$success_message = '';
$error_message = '';

// Fetch user's addresses
$stmt = $conn->prepare("SELECT * FROM addresses WHERE user_id = ? ORDER BY is_default DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$addresses = $stmt->get_result();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Add new address
    if (isset($_POST['add_address'])) {
        $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
        $phone = filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_STRING);
        $street = filter_input(INPUT_POST, 'street', FILTER_SANITIZE_STRING);
        $city = filter_input(INPUT_POST, 'city', FILTER_SANITIZE_STRING);
        $state = filter_input(INPUT_POST, 'state', FILTER_SANITIZE_STRING);
        $postal_code = filter_input(INPUT_POST, 'postal_code', FILTER_SANITIZE_STRING);
        $is_default = isset($_POST['is_default']) ? 1 : 0;
        
        // If this is set as default, unset all other defaults
        if ($is_default) {
            $update_defaults = $conn->prepare("UPDATE addresses SET is_default = 0 WHERE user_id = ?");
            $update_defaults->bind_param("i", $user_id);
            $update_defaults->execute();
        }
        
        // Insert new address
        $insert = $conn->prepare("INSERT INTO addresses (user_id, username, phone, street, city, state, postal_code, is_default) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $insert->bind_param("issssssi", $user_id, $username, $phone, $street, $city, $state, $postal_code, $is_default);
        
        if ($insert->execute()) {
            $success_message = "Address added successfully!";
            // Refresh the page to show the new address
            header("Location: /minor project/auth/address.php?success=added");
            exit;
        } else {
            $error_message = "Failed to add address: " . $conn->error;
        }
    }
    
    // Set address as default
    if (isset($_POST['set_default'])) {
        $address_id = filter_input(INPUT_POST, 'address_id', FILTER_VALIDATE_INT);
        
        // First, unset all defaults
        $update_defaults = $conn->prepare("UPDATE addresses SET is_default = 0 WHERE user_id = ?");
        $update_defaults->bind_param("i", $user_id);
        $update_defaults->execute();
        
        // Then set the selected address as default
        $set_default = $conn->prepare("UPDATE addresses SET is_default = 1 WHERE id = ? AND user_id = ?");
        $set_default->bind_param("ii", $address_id, $user_id);
        
        if ($set_default->execute()) {
            $success_message = "Default address updated!";
            header("Location: /minor project/auth/address.php?success=default");
            exit;
        } else {
            $error_message = "Failed to update default address: " . $conn->error;
        }
    }
    
    // Delete address
    if (isset($_POST['delete_address'])) {
        $address_id = filter_input(INPUT_POST, 'address_id', FILTER_VALIDATE_INT);
        
        $delete = $conn->prepare("DELETE FROM addresses WHERE id = ? AND user_id = ?");
        $delete->bind_param("ii", $address_id, $user_id);
        
        if ($delete->execute()) {
            $success_message = "Address deleted successfully!";
            header("Location: /minor project/auth/address.php?success=deleted");
            exit;
        } else {
            $error_message = "Failed to delete address: " . $conn->error;
        }
    }
    
    // Edit address
    if (isset($_POST['edit_address'])) {
        $address_id = filter_input(INPUT_POST, 'address_id', FILTER_VALIDATE_INT);
        $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
        $phone = filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_STRING);
        $street = filter_input(INPUT_POST, 'street', FILTER_SANITIZE_STRING);
        $city = filter_input(INPUT_POST, 'city', FILTER_SANITIZE_STRING);
        $state = filter_input(INPUT_POST, 'state', FILTER_SANITIZE_STRING);
        $postal_code = filter_input(INPUT_POST, 'postal_code', FILTER_SANITIZE_STRING);
        $is_default = isset($_POST['is_default']) ? 1 : 0;
        
        // If this is set as default, unset all other defaults
        if ($is_default) {
            $update_defaults = $conn->prepare("UPDATE addresses SET is_default = 0 WHERE user_id = ?");
            $update_defaults->bind_param("i", $user_id);
            $update_defaults->execute();
        }
        
        // Update address
        $update = $conn->prepare("UPDATE addresses SET username = ?, phone = ?, street = ?, city = ?, state = ?, postal_code = ?, is_default = ? WHERE id = ? AND user_id = ?");
        $update->bind_param("ssssssiis", $username, $phone, $street, $city, $state, $postal_code, $is_default, $address_id, $user_id);
        
        if ($update->execute()) {
            $success_message = "Address updated successfully!";
            header("Location: /minor project/auth/address.php?success=updated");
            exit;
        } else {
            $error_message = "Failed to update address: " . $conn->error;
        }
    }
}

// Check for success messages from redirects
if (isset($_GET['success'])) {
    switch ($_GET['success']) {
        case 'added':
            $success_message = "Address added successfully!";
            break;
        case 'updated':
            $success_message = "Address updated successfully!";
            break;
        case 'deleted':
            $success_message = "Address deleted successfully!";
            break;
        case 'default':
            $success_message = "Default address updated!";
            break;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Addresses - SnapCart</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/minor project/assets/css/account.css">
    <style>
        .address-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        
        .address-card {
            border: 1px solid #e1e1e1;
            border-radius: 8px;
            padding: 15px;
            position: relative;
            background-color: #fff;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            transition: transform 0.2s, box-shadow 0.2s;
        }
        
        .address-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .address-card.default {
            border-color: #ff6b00;
            background-color: #fff9f5;
        }
        
        .default-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background-color: #ff6b00;
            color: white;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .address-name {
            font-weight: 600;
            font-size: 18px;
            margin-bottom: 10px;
            color: #333;
        }
        
        .address-details {
            margin-bottom: 15px;
            color: #555;
            line-height: 1.5;
        }
        
        .address-actions {
            display: flex;
            justify-content: space-between;
            margin-top: 15px;
        }
        
        .address-actions button {
            padding: 6px 12px;
            border-radius: 4px;
            border: none;
            cursor: pointer;
            font-weight: 500;
            transition: background-color 0.2s;
        }
        
        .edit-btn {
            background-color: #e6f2ff;
            color: #0066cc;
        }
        
        .edit-btn:hover {
            background-color: #cce5ff;
        }
        
        .delete-btn {
            background-color: #ffebee;
            color: #d32f2f;
        }
        
        .delete-btn:hover {
            background-color: #ffcdd2;
        }
        
        .default-btn {
            background-color: #f0f0f0;
            color: #333;
        }
        
        .default-btn:hover {
            background-color: #e0e0e0;
        }
        
        .add-address-card {
            border: 2px dashed #ccc;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            min-height: 200px;
            transition: border-color 0.2s, background-color 0.2s;
        }
        
        .add-address-card:hover {
            border-color: #ff6b00;
            background-color: #fff9f5;
        }
        
        .add-address-card i {
            font-size: 40px;
            color: #ccc;
            margin-bottom: 10px;
            transition: color 0.2s;
        }
        
        .add-address-card:hover i {
            color: #ff6b00;
        }
        
        .add-address-card p {
            color: #666;
            font-weight: 500;
            transition: color 0.2s;
        }
        
        .add-address-card:hover p {
            color: #ff6b00;
        }
        
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            overflow: auto;
        }
        
        .modal-content {
            background-color: #fff;
            margin: 50px auto;
            padding: 20px;
            border-radius: 8px;
            width: 90%;
            max-width: 600px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            animation: modalFadeIn 0.3s;
        }
        
        @keyframes modalFadeIn {
            from {opacity: 0; transform: translateY(-50px);}
            to {opacity: 1; transform: translateY(0);}
        }
        
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        
        .modal-header h3 {
            margin: 0;
            color: #333;
        }
        
        .close-btn {
            font-size: 24px;
            font-weight: bold;
            color: #aaa;
            cursor: pointer;
            transition: color 0.2s;
        }
        
        .close-btn:hover {
            color: #333;
        }
        
        .form-row {
            display: flex;
            gap: 15px;
            margin-bottom: 15px;
        }
        
        .form-row .form-group {
            flex: 1;
        }
        
        .checkbox-group {
            display: flex;
            align-items: center;
            margin: 15px 0;
        }
        
        .checkbox-group input {
            margin-right: 10px;
        }
        
        .modal-footer {
            margin-top: 20px;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }
        
        .btn-secondary {
            background-color: #f0f0f0;
            color: #333;
            border: none;
            padding: 10px 15px;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 500;
            transition: background-color 0.2s;
        }
        
        .btn-secondary:hover {
            background-color: #e0e0e0;
        }
        
        .btn-primary {
            background-color: #ff6b00;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 500;
            transition: background-color 0.2s;
        }
        
        .btn-primary:hover {
            background-color: #e65c00;
        }
        
        .back-link {
            display: inline-flex;
            align-items: center;
            color: #555;
            text-decoration: none;
            margin-bottom: 20px;
            font-weight: 500;
            transition: color 0.2s;
        }
        
        .back-link i {
            margin-right: 5px;
        }
        
        .back-link:hover {
            color: #ff6b00;
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
                    <li><a href="/minor project/cart/view-cart.php">Cart</a></li>
                    <li><a href="/minor project/aboutus.php">About</a></li>
                    <li><a href="/minor project/contact.php">Contact</a></li>
                </ul>
            </nav>
            <div class="user-actions">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="/minor project/auth/account.php" class="active">My Account</a>
                    <a href="/minor project/auth/logout.php">Logout</a>
                <?php else: ?>
                    <a href="/minor project/auth/login.php">Login</a>
                    <a href="/minor project/auth/register.php">Register</a>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <main class="account-container">
        <a href="/minor project/auth/account.php" class="back-link">
            <i class="fas fa-arrow-left"></i> Back to Account
        </a>
        
        <div class="account-header">
            <h1>Manage Addresses</h1>
        </div>

        <?php if (!empty($success_message)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error_message; ?>
            </div>
        <?php endif; ?>

        <div class="address-container">
            <!-- Add New Address Card -->
            <div class="address-card add-address-card" id="addAddressCard">
                <i class="fas fa-plus-circle"></i>
                <p>Add New Address</p>
            </div>
            
            <!-- Existing Addresses -->
            <?php if ($addresses->num_rows > 0): ?>
                <?php while ($address = $addresses->fetch_assoc()): ?>
                    <div class="address-card <?php echo $address['is_default'] ? 'default' : ''; ?>">
                        <?php if ($address['is_default']): ?>
                            <div class="default-badge">Default</div>
                        <?php endif; ?>
                        
                        <div class="address-name"><?php echo htmlspecialchars($address['username']); ?></div>
                        
                        <div class="address-details">
                            <p><?php echo htmlspecialchars($address['street']); ?></p>
                            <p><?php echo htmlspecialchars($address['city']); ?>, <?php echo htmlspecialchars($address['state']); ?> <?php echo htmlspecialchars($address['postal_code']); ?></p>
                            <p>Phone: <?php echo htmlspecialchars($address['phone']); ?></p>
                        </div>
                        
                        <div class="address-actions">
                            <button class="edit-btn" onclick="editAddress(<?php echo $address['id']; ?>, '<?php echo addslashes($address['username']); ?>', '<?php echo addslashes($address['phone']); ?>', '<?php echo addslashes($address['street']); ?>', '<?php echo addslashes($address['city']); ?>', '<?php echo addslashes($address['state']); ?>', '<?php echo addslashes($address['postal_code']); ?>', <?php echo $address['is_default'] ? 'true' : 'false'; ?>)">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            
                            <?php if (!$address['is_default']): ?>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="address_id" value="<?php echo $address['id']; ?>">
                                    <button type="submit" name="set_default" class="default-btn">
                                        <i class="fas fa-check-circle"></i> Set as Default
                                    </button>
                                </form>
                            <?php endif; ?>
                            
                            <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this address?');">
                                <input type="hidden" name="address_id" value="<?php echo $address['id']; ?>">
                                <button type="submit" name="delete_address" class="delete-btn">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <!-- No addresses message -->
                <div class="no-addresses-message" style="grid-column: span 2; text-align: center; padding: 20px;">
                    <p>You don't have any saved addresses yet. Add your first address to make checkout faster.</p>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <!-- Add/Edit Address Modal -->
    <div id="addressModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="modalTitle">Add New Address</h3>
                <span class="close-btn">&times;</span>
            </div>
            
            <form id="addressForm" method="POST">
                <input type="hidden" id="address_id" name="address_id">
                <input type="hidden" id="form_action" name="add_address">
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="username"><i class="fas fa-user"></i> Full Name</label>
                        <input type="text" id="username" name="username" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="phone"><i class="fas fa-phone"></i> Phone Number</label>
                        <input type="tel" id="phone" name="phone" class="form-control" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="street"><i class="fas fa-home"></i> Street Address</label>
                    <input type="text" id="street" name="street" class="form-control" required>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="city"><i class="fas fa-city"></i> City</label>
                        <input type="text" id="city" name="city" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="state"><i class="fas fa-map"></i> State</label>
                        <input type="text" id="state" name="state" class="form-control" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="postal_code"><i class="fas fa-mail-bulk"></i> Postal Code</label>
                    <input type="text" id="postal_code" name="postal_code" class="form-control" required>
                </div>
                
                <div class="checkbox-group">
                    <input type="checkbox" id="is_default" name="is_default">
                    <label for="is_default">Set as default address</label>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn-secondary" id="cancelBtn">Cancel</button>
                    <button type="submit" class="btn-primary" id="saveBtn">Save Address</button>
                </div>
            </form>
        </div>
    </div>

    <footer style="background-color:black;color:white;padding:20px 10px;">
        <div class="footer-container" style="display:flex;justify-content:space-between;flex-wrap:wrap;">
            <div class="footer-section" style="flex:1;min-width:200px;margin:10px;">
                <h3 style="color:white;">About Us</h3>
                <p style="color:white;">We provide high-quality tech products at competitive prices. Our mission is to make technology accessible to everyone.</p>
            </div>
            <div class="footer-section" style="flex:1;min-width:200px;margin:10px;">
                <h3 style="color:white;">Quick Links</h3>
                <ul style="list-style:none;padding:0;">
                    <li><a href="/minor project/index.php" style="color:white;text-decoration:none;">Home</a></li>
                    <li><a href="/minor project/product/product.php" style="color:white;text-decoration:none;">Products</a></li>
                    <li><a href="/minor project/aboutus.php" style="color:white;text-decoration:none;">About Us</a></li>
                    <li><a href="/minor project/contact.php" style="color:white;text-decoration:none;">Contact</a></li>
                </ul>
            </div>
            <div class="footer-section" style="flex:1;min-width:200px;margin:10px;">
                <h3 style="color:white;">Contact Us</h3>
                <p style="color:white;">Email: info@snapcart.com</p>
                <p style="color:white;">Phone: +1234567890</p>
                <p style="color:white;">Address: 8th street newroad, Pokhara, Nepal</p>
            </div>
        </div>

        <div style="margin-top:20px;display:flex;justify-content:flex-end;gap:10px;">
            <a href="https://facebook.com" target="_blank">
                <img src="/minor project/assets/images/facebook.png" style="width:50px;height:50px;border-radius:50%;object-fit:cover;">
            </a>
            <a href="https://twitter.com" target="_blank">
                <img src="/minor project/assets/images/twitter.webp" style="width:50px;height:50px;border-radius:50%;object-fit:cover;">
            </a>
            <a href="https://instagram.com" target="_blank">
                <img src="/minor project/assets/images/instagram.png" style="width:50px;height:50px;border-radius:50%;object-fit:cover;">
            </a>
        </div>

        <div class="footer-bottom" style="text-align:center;margin-top:20px;color:white;">
            <p>&copy; 2025 SnapCart Store. All Rights Reserved.</p>
        </div>
    </footer>

    <script>
        // Modal functionality
        const modal = document.getElementById('addressModal');
        const addAddressCard = document.getElementById('addAddressCard');
        const closeBtn = document.querySelector('.close-btn');
        const cancelBtn = document.getElementById('cancelBtn');
        const addressForm = document.getElementById('addressForm');
        const modalTitle = document.getElementById('modalTitle');
        const formAction = document.getElementById('form_action');
        
        // Open modal when clicking "Add New Address"
        addAddressCard.addEventListener('click', function() {
            // Reset form
            addressForm.reset();
            document.getElementById('address_id').value = '';
            formAction.name = 'add_address';
            modalTitle.textContent = 'Add New Address';
            modal.style.display = 'block';
        });
        
        // Close modal when clicking X or Cancel
        closeBtn.addEventListener('click', function() {
            modal.style.display = 'none';
        });
        
        cancelBtn.addEventListener('click', function() {
            modal.style.display = 'none';
        });
        
        // Close modal when clicking outside
        window.addEventListener('click', function(event) {
            if (event.target === modal) {
                modal.style.display = 'none';
            }
        });
        
        // Function to populate edit form
        function editAddress(id, name, phone, street, city, state, postal_code, is_default) {
            document.getElementById('address_id').value = id;
            document.getElementById('username').value = username;
            document.getElementById('phone').value = phone;
            document.getElementById('street').value = street;
            document.getElementById('city').value = city;
            document.getElementById('state').value = state;
            document.getElementById('postal_code').value = postal_code;
            document.getElementById('is_default').checked = is_default;
            
            formAction.name = 'edit_address';
            modalTitle.textContent = 'Edit Address';
            modal.style.display = 'block';
        }
    </script>
</body>
</html>