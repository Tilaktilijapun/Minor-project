<?php
include '../includes/dbconn.php';
session_start();

// Check admin authentication
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$success = '';
$error = '';
$admin_id = $_SESSION['admin_id'];

// Fetch current settings
$stmt = $conn->prepare("SELECT * FROM site_settings WHERE 1");
$stmt->execute();
$settings = $stmt->get_result()->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Site Info Update
    if (isset($_POST['update_site_info'])) {
        $site_name = trim($_POST['site_name']);
        
        // Logo Upload
        if (isset($_FILES['site_logo']) && $_FILES['site_logo']['error'] == 0) {
            $allowed = ['jpg', 'jpeg', 'png', 'gif'];
            $filename = $_FILES['site_logo']['name'];
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            
            if (in_array($ext, $allowed)) {
                $new_filename = 'logo_' . time() . '.' . $ext;
                $upload_path = '../assets/images/' . $new_filename;
                
                if (move_uploaded_file($_FILES['site_logo']['tmp_name'], $upload_path)) {
                    $update = $conn->prepare("UPDATE site_settings SET site_name = ?, logo_path = ?");
                    $update->bind_param("ss", $site_name, $new_filename);
                }
            } else {
                $error = "Invalid file type. Allowed: JPG, JPEG, PNG, GIF";
            }
        } else {
            $update = $conn->prepare("UPDATE site_settings SET site_name = ?");
            $update->bind_param("s", $site_name);
        }
        
        if (isset($update) && $update->execute()) {
            $success = "Site information updated successfully!";
        }
    }

    // Basic Configurations Update
    if (isset($_POST['update_config'])) {
        $currency = $_POST['currency'];
        $tax_rate = floatval($_POST['tax_rate']);
        $shipping_cost = floatval($_POST['shipping_cost']);

        $update = $conn->prepare("UPDATE site_settings SET currency = ?, tax_rate = ?, shipping_cost = ?");
        $update->bind_param("sdd", $currency, $tax_rate, $shipping_cost);
        
        if ($update->execute()) {
            $success = "Configuration updated successfully!";
        }
    }
}

// Fetch fresh settings after update
$stmt->execute();
$settings = $stmt->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Settings - SnapCart</title>
    <link rel="stylesheet" href="/minor project/admin/css/settings.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="settings-container">
        <div class="settings-header">
            <h2 style="color:lightblue;"><i class="fas fa-cog"></i> ⚙️ Admin Settings</h2>
        </div>

        <?php if ($success): ?>
            <div class="message success-message">
                <i class="fas fa-check-circle"></i> <?= htmlspecialchars($success) ?>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="message error-message">
                <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <div class="settings-nav">
            <a href="#site-info" class="nav-link active" style="color: orange;"><i style="color: orange;" class="fas fa-info-circle"></i> Site Info</a>
            <a href="product.php" class="nav-link" style="color: orange;"><i style="color: orange;" class="fas fa-box"></i> Products</a>
            <a href="orders.php" class="nav-link" style="color: orange;"><i style="color: orange;" class="fas fa-shopping-cart"></i> Orders</a>
            <a href="user.php" class="nav-link" style="color: orange;"><i style="color: orange;" class="fas fa-users"></i> Users</a>
            <a href="config.php" class="nav-link" style="color: orange;"><i style="color: orange;" class="fas fa-sliders-h"></i> Configuration</a>
        </div>

        <div class="settings-section" id="site-info">
            <h3><i class="fas fa-info-circle"></i> Site Information</h3>
            <form method="POST" action="" enctype="multipart/form-data">
                <div class="input-group">
                    <label for="site_name">Website Name</label>
                    <input type="text" id="site_name" name="site_name" 
                           value="<?= htmlspecialchars($settings['site_name'] ?? '') ?>" required>
                </div>

                <div class="input-group">
                    <label>Website Logo</label>
                    <div class="file-upload">
                        <label for="site_logo" class="file-upload-label">
                            <i class="fas fa-cloud-upload-alt"></i> Choose Logo File
                        </label>
                        <input type="file" id="site_logo" name="site_logo" accept="image/*">
                    </div>
                    <?php if (!empty($settings['logo_path'])): ?>
                        <img src="../assets/images/<?= htmlspecialchars($settings['logo_path']) ?>" 
                             alt="Current Logo" class="preview-image">
                    <?php endif; ?>
                </div>

                <button type="submit" name="update_site_info" class="submit-btn">
                    <i class="fas fa-save"></i> Save Site Information
                </button>
            </form>
        </div>

        <div class="settings-section" id="config">
            <h3><i class="fas fa-sliders-h"></i> Basic Configurations</h3>
            <form method="POST" action="">
                <div class="input-group">
                    <label for="currency">Currency</label>
                    <select id="currency" name="currency" required>
                    <option value="Rs" <?= ($settings['currency'] ?? '') === 'Rs' ? 'selected' : '' ?>>Rupees (₹)</option>
                        <option value="USD" <?= ($settings['currency'] ?? '') === 'USD' ? 'selected' : '' ?>>USD ($)</option>
                        <option value="EUR" <?= ($settings['currency'] ?? '') === 'EUR' ? 'selected' : '' ?>>EUR (€)</option>
                        <option value="INR" <?= ($settings['currency'] ?? '') === 'INR' ? 'selected' : '' ?>>INR (₹)</option>
                    </select>
                </div>

                <div class="input-group">
                    <label for="tax_rate">Tax Rate (%)</label>
                    <input type="number" id="tax_rate" name="tax_rate" step="0.01" min="0" max="100"
                           value="<?= htmlspecialchars($settings['tax_rate'] ?? '0') ?>" required>
                </div>

                <div class="input-group">
                    <label for="shipping_cost">Flat Rate Shipping Cost</label>
                    <input type="number" id="shipping_cost" name="shipping_cost" step="0.01" min="0"
                           value="<?= htmlspecialchars($settings['shipping_cost'] ?? '0') ?>" required>
                </div>

                <button type="submit" name="update_config" class="submit-btn">
                    <i class="fas fa-save"></i> Save Configuration
                </button>
            </form>
        </div>

        <div class="settings-grid">
            <a href="stock.php" class="stat-card">
                <i style="color: orange;" class="fas fa-box"></i>
                <h3 style="color: orange;">Product Management</h3>
                <p style="color: lightblue;">Add, edit, or delete products</p>
            </a>
            <a href="orders.php" class="stat-card">
                <i style="color: orange;" class="fas fa-shopping-cart"></i>
                <h3 style="color: orange;">Order Management</h3>
                <p style="color: lightblue;">View and manage orders</p>
            </a>
            <a href="user.php" class="stat-card">
                <i style="color: orange;" class="fas fa-users"></i>
                <h3 style="color: orange;">User Management</h3>
                <p style="color: lightblue;">Manage registered users</p>
            </a>
        </div>

        <a href="dashboard.php" class="back-link">
            <i class="fas fa-arrow-left"></i> Back to Dashboard
        </a>
    </div>

    <script>
        document.getElementById('site_logo').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.querySelector('.preview-image') || document.createElement('img');
                    preview.src = e.target.result;
                    preview.classList.add('preview-image');
                    if (!document.querySelector('.preview-image')) {
                        document.querySelector('.file-upload').appendChild(preview);
                    }
                }
                reader.readAsDataURL(file);
            }
        });
    </script>
    <script src="/minor project/admin/js/settings.js"></script>
</body>
</html>
