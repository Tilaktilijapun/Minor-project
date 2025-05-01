<?php
session_start();
include '../includes/dbconn.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    
    // Check if email exists in admin table
    $stmt = $conn->prepare("SELECT id FROM admin WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        // Generate reset token
        $token = bin2hex(random_bytes(32));
        $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        // Store token in database
        $stmt = $conn->prepare("UPDATE admin SET reset_token = ?, reset_expiry = ? WHERE email = ?");
        $stmt->bind_param("sss", $token, $expiry, $email);
        $stmt->execute();
        
        // Send reset email
        $reset_link = "http://localhost/minor project/admin/reset-password.php?token=" . $token;
        $to = $email;
        $subject = "Password Reset - SnapCart Admin";
        $message = "Click the following link to reset your password: " . $reset_link;
        $headers = "From: noreply@snapcart.com";
        
        mail($to, $subject, $message, $headers);
        
        $_SESSION['success'] = "Password reset link has been sent to your email.";
        header("Location: login.php");
        exit();
    } else {
        $error = "Email not found in our records.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - SnapCart Admin</title>
    <link rel="stylesheet" href="/minor project/admin/css/auth.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <img src="/minor project/assets/images/logo.png.png" alt="SnapCart Logo" class="logo">
                <h1>Forgot Password</h1>
                <p>Enter your email address to receive a password reset link.</p>
            </div>

            <?php if(isset($error)): ?>
                <div class="alert alert-error">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="auth-form">
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <div class="input-group">
                        <i class="fas fa-envelope"></i>
                        <input type="email" id="email" name="email" required 
                               placeholder="Enter your email">
                    </div>
                </div>

                <button type="submit" class="btn-primary">
                    Send Reset Link
                </button>

                <div class="auth-links">
                    <a href="login.php">Back to Login</a>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Form validation
        document.querySelector('.auth-form').addEventListener('submit', function(e) {
            const email = document.getElementById('email').value;
            const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            
            if (!emailPattern.test(email)) {
                e.preventDefault();
                alert('Please enter a valid email address');
            }
        });
    </script>
</body>
</html>