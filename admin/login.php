
<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session at the very beginning
session_start();

// Check if already logged in
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("Location: dashboard.php");
    exit();
}

include '../includes/dbconn.php';

// Check if admin is already logged in
if (isset($_SESSION['admin_id'])) {
    header("Location: dashboard.php");
    exit();
}

// Process login form
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
    $password = $_POST['password'];
    
    if (empty($username) || empty($password)) {
        $error = "Both username and password are required.";
    } else {
        $stmt = $conn->prepare("SELECT id, username, password FROM admin WHERE username = ? LIMIT 1");
        $stmt->bind_param("s", $username);
        
        try {
            $stmt->execute();
            $result = $stmt->get_result();
            $admin = $result->fetch_assoc();

            if ($admin && $password === $admin['password']) {
                // Clear any existing output buffers
                while (ob_get_level()) {
                    ob_end_clean();
                }
                
                // Set session variables
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_username'] = $admin['username'];
                $_SESSION['admin_logged_in'] = true;
                
                // Use relative path instead of absolute
                header("Location: dashboard.php");
                exit();
            } else {
                $error = "Invalid username or password.";
            }
        } catch (Exception $e) {
            $error = "Database error: " . $e->getMessage();
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <link rel="stylesheet" href="/minor project/assets/css/login.css">
</head>
<body class="dark-theme">
<div class="login-container">
    <h2>Admin Login</h2>
    <?php if (isset($error)): ?>
        <p class="error-message"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
    <form method="POST" action="">
        <div class="input-group">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" placeholder="Enter your username" required>
        </div>
        <div class="input-group">
            <label for="password">Password</label>
            <div class="password-wrapper">
                <input type="password" id="password" name="password" placeholder="Enter your password" required>
                <span id="toggle-password" class="toggle-password">👁️</span>
            </div>
        </div>
        <button type="submit" class="submit-btn">Login</button>
        <p class="forgot-password"><a href="forgot-password.php">Forgot Password?</a></p>
    </form>
</div>
<script src="/minor project/admin/js/login.js"></script>

</body>
</html>
