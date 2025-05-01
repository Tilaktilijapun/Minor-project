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

// Fetch user data
$stmt = $conn->prepare("SELECT fullname, email, phone, city, street, state FROM user WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: /minor project/auth/logout.php");
    exit;
}

$user = $result->fetch_assoc();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_profile'])) {
        // Update profile information
        $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
        $phone = filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_STRING);
        $street = filter_input(INPUT_POST, 'street', FILTER_SANITIZE_STRING);
        $city = filter_input(INPUT_POST, 'city', FILTER_SANITIZE_STRING);
        $state = filter_input(INPUT_POST, 'state', FILTER_SANITIZE_STRING);
        
        $update_stmt = $conn->prepare("UPDATE user SET fullname = ?, phone = ?, street = ?, city = ?, state = ? WHERE id = ?");
        $update_stmt->bind_param("sssssi", $name, $phone, $street, $city, $state, $user_id);
        
        if ($update_stmt->execute()) {
            $success_message = "Profile updated successfully!";
            // Update session data
            $_SESSION['fullname'] = $name;
            
            // Refresh user data
            $user['fullname'] = $name;
            $user['phone'] = $phone;
            $user['street'] = $street;
            $user['city'] = $city;
            $user['state'] = $state;
        } else {
            $error_message = "Failed to update profile: " . $conn->error;
        }
    } elseif (isset($_POST['change_password'])) {
        // Change password
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        // Verify current password
        $pwd_stmt = $conn->prepare("SELECT password FROM user WHERE id = ?");
        $pwd_stmt->bind_param("i", $user_id);
        $pwd_stmt->execute();
        $pwd_result = $pwd_stmt->get_result();
        $user_pwd = $pwd_result->fetch_assoc();
        
        if (password_verify($current_password, $user_pwd['password'])) {
            if ($new_password === $confirm_password) {
                // Update password
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $pwd_update = $conn->prepare("UPDATE user SET password = ? WHERE id = ?");
                $pwd_update->bind_param("si", $hashed_password, $user_id);
                
                if ($pwd_update->execute()) {
                    $success_message = "Password changed successfully!";
                } else {
                    $error_message = "Failed to update password: " . $conn->error;
                }
            } else {
                $error_message = "New passwords do not match!";
            }
        } else {
            $error_message = "Current password is incorrect!";
        }
    }
}

// Fetch order history
$orders_stmt = $conn->prepare("SELECT o.id, o.created_at, o.total_price, o.status FROM orders o WHERE o.user_id = ? ORDER BY o.created_at DESC LIMIT 5");
$orders_stmt->bind_param("i", $user_id);
$orders_stmt->execute();
$orders_result = $orders_stmt->get_result();

// Get order count
$order_count_stmt = $conn->prepare("SELECT COUNT(*) as count FROM orders WHERE user_id = ?");
$order_count_stmt->bind_param("i", $user_id);
$order_count_stmt->execute();
$order_count_result = $order_count_stmt->get_result();
$order_count = $order_count_result->fetch_assoc()['count'];

// Get total spent
$total_spent_stmt = $conn->prepare("SELECT SUM(total_price) as total FROM orders WHERE user_id = ? AND status != 'cancelled'");
$total_spent_stmt->bind_param("i", $user_id);
$total_spent_stmt->execute();
$total_spent_result = $total_spent_stmt->get_result();
$total_spent = $total_spent_result->fetch_assoc()['total'] ?? 0;

// Get account age
$account_age_stmt = $conn->prepare("SELECT created_at FROM user WHERE id = ?");
$account_age_stmt->bind_param("i", $user_id);
$account_age_stmt->execute();
$account_age_result = $account_age_stmt->get_result();
$created_at = $account_age_result->fetch_assoc()['created_at'] ?? date('Y-m-d H:i:s');
$account_age = floor((time() - strtotime($created_at)) / (60 * 60 * 24));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Account</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/minor project/assets/css/account.css">
    <style>
        .address-link {
            display: flex !important;
            justify-content: space-between;
            align-items: center;
        }
        
        .address-count {
            background-color:rgb(240, 240, 240);
            color: #666;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 500;
        }
        
        .address-link:hover .address-count {
            background-color: #ff6b00;
            color: white;
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
                    <li><a href="/minor project/cart/cart.php">Cart</a></li>
                    <li><a href="/minor project/aboutus.php">About</a></li>
                    <li><a href="/minor project/contact.php">Contact</a></li>
                    <li>
  <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') { ?>
    <a href="/minor project/admin/dashboard.php">Admin</a>
  <?php } else { ?>
    <a href="#" onclick="alert('You are not Admin'); return false;">Admin</a>
  <?php } ?>
</li>
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
        <div class="account-header">
            <h1>Welcome back, <?php echo htmlspecialchars($user['fullname']); ?>!</h1>
            <a href="/minor project/auth/logout.php" class="btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
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




        <div class="account-grid">
            <div class="account-sidebar">
                <div class="profile-picture">
                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($user['fullname']); ?>&background=e6f2ff&color=ff6b00&size=120" alt="Profile" class="avatar">
                    <button class="upload-btn"><i class="fas fa-camera"></i> Change Photo</button>
                </div>
                <ul class="account-menu">
                    <li><a href="#profile" class="active"><i class="fas fa-user"></i> Profile Information</a></li>
                    <li><a href="#password"><i class="fas fa-lock"></i> Change Password</a></li>
                    <li><a href="#orders"><i class="fas fa-shopping-cart"></i> Order History</a></li>
                    <li>
                        <a href="/minor project/auth/address.php" class="address-link">
                            <i class="fas fa-map-marker-alt"></i> Manage Addresses
                            <?php
                            // Get address count
                            $address_count_stmt = $conn->prepare("SELECT COUNT(*) as count FROM addresses WHERE user_id = ?");
                            $address_count_stmt->bind_param("i", $user_id);
                            $address_count_stmt->execute();
                            $address_count_result = $address_count_stmt->get_result();
                            $address_count = $address_count_result->fetch_assoc()['count'] ?? 0;
                            ?>
                            <span class="address-count"><?php echo $address_count; ?> saved</span>
                        </a>
                    </li>
                </ul>
            </div>

            <div class="account-content">
                <div id="profile" class="account-section">
                    <h2>Profile Information</h2>
                    <form method="POST" action="">
                        <div class="form-group">
                            <label for="name"><i class="fas fa-user"></i> Full Name</label>
                            <input type="text" id="name" name="name" class="form-control" value="<?php echo htmlspecialchars($user['fullname']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="email"><i class="fas fa-envelope"></i> Email Address</label>
                            <input type="email" id="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" readonly>
                            <small>Email cannot be changed</small>
                        </div>
                        <div class="form-group">
                            <label for="phone"><i class="fas fa-phone"></i> Phone Number</label>
                            <input type="tel" id="phone" name="phone" class="form-control" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="street"><i class="fas fa-home"></i> Street Address</label>
                            <input type="text" id="street" name="street" class="form-control" value="<?php echo htmlspecialchars($user['street'] ?? ''); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="city"><i class="fas fa-city"></i> City</label>
                            <input type="text" id="city" name="city" class="form-control" value="<?php echo htmlspecialchars($user['city'] ?? ''); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="state"><i class="fas fa-map"></i> State</label>
                            <input type="text" id="state" name="state" class="form-control" value="<?php echo htmlspecialchars($user['state'] ?? ''); ?>">
                        </div>
                        
                        <button type="submit" name="update_profile" class="btn"><i class="fas fa-save"></i> Update Profile</button>
                    </form>
                </div>

                <div id="password" class="account-section">
                    <h2>Change Password</h2>
                    <form method="POST" action="">
                        <div class="form-group">
                            <label for="current_password"><i class="fas fa-key"></i> Current Password</label>
                            <input type="password" id="current_password" name="current_password" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="new_password"><i class="fas fa-lock"></i> New Password</label>
                            <input type="password" id="new_password" name="new_password" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="confirm_password"><i class="fas fa-check-circle"></i> Confirm New Password</label>
                            <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                        </div>
                        <button type="submit" name="change_password" class="btn">Change Password</button>
                    </form>
                </div>

                <div id="orders" class="account-section">
                    <h2>Recent Orders</h2>
                    <?php if ($orders_result->num_rows > 0): ?>
                        <table class="order-table">
                            <thead>
                                <tr>
                                    <th>Order ID</th>
                                    <th>Date</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($order = $orders_result->fetch_assoc()): ?>
                                    <tr>
                                        <td>#<?php echo $order['id']; ?></td>
                                        <td><?php echo date('d M Y', strtotime($order['created_at'])); ?></td>
                                        <td>Rs <?php echo number_format($order['total_price'], 2); ?></td>
                                        <td>
                                            <span class="status-badge status-<?php echo strtolower($order['status']); ?>">
                                                <?php echo ucfirst($order['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="/minor project/orders/view.php?id=<?php echo $order['id']; ?>">View Details</a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                        <p><a href="/minor project/auth/view-order.php">View All Orders</a></p>
                    <?php else: ?>
                        <p>You haven't placed any orders yet.</p>
                        <a href="/minor project/product/product.php" class="btn">Start Shopping</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>
 
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

  <!-- Social Icons Positioned at Footer Bottom Right -->
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
        // Simple tab navigation
        document.addEventListener('DOMContentLoaded', function() {
            const menuLinks = document.querySelectorAll('.account-menu a');
            
            menuLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    
                    // Remove active class from all links
                    menuLinks.forEach(item => item.classList.remove('active'));
                    
                    // Add active class to clicked link
                    this.classList.add('active');
                    
                    // Show the corresponding section
                    const targetId = this.getAttribute('href').substring(1);
                    document.querySelectorAll('.account-section').forEach(section => {
                        section.style.display = section.id === targetId ? 'block' : 'none';
                    });
                });
            });
            
            // Show the first section by default
            document.querySelectorAll('.account-section').forEach((section, index) => {
                section.style.display = index === 0 ? 'block' : 'none';
            });
        });
    </script>
</body>
</html>