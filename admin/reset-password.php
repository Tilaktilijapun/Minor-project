<?php
session_start();
include '../includes/dbconn.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if (empty($username) || empty($new_password) || empty($confirm_password)) {
        $error = "All fields are required.";
    } elseif ($new_password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        $stmt = $conn->prepare("SELECT id FROM admin WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $update_stmt = $conn->prepare("UPDATE admin SET password = ? WHERE username = ?");
            $update_stmt->bind_param("ss", $hashed_password, $username);
            
            if ($update_stmt->execute()) {
                $success = "Password updated successfully. You can now login with your new password.";
            } else {
                $error = "Failed to update password. Please try again.";
            }
            $update_stmt->close();
        } else {
            $error = "Username not found.";
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
    <title>Reset Password - Admin Panel</title>
    <link rel="stylesheet" href="/minor project/admin/css/forget-password.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="reset-container">
        <div class="reset-header">
            <h2>Reset Password</h2>
            <p>Enter your username and new password</p>
        </div>

        <?php if ($error): ?>
            <div class="error-message">
                <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="success-message">
                <i class="fas fa-check-circle"></i> <?= htmlspecialchars($success) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="input-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required>
            </div>

            <div class="input-group">
                <label for="new_password">New Password</label>
                <div class="password-wrapper">
                    <input type="password" id="new_password" name="new_password" required>
                    <button type="button" class="toggle-password" onclick="togglePassword('new_password')">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>

            <div class="input-group">
                <label for="confirm_password">Confirm Password</label>
                <div class="password-wrapper">
                    <input type="password" id="confirm_password" name="confirm_password" required>
                    <button type="button" class="toggle-password" onclick="togglePassword('confirm_password')">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>

            <button type="submit" class="submit-btn">Reset Password</button>
        </form>

        <div class="back-to-login">
            <a href="login.php"><i class="fas fa-arrow-left"></i> Back to Login</a>
        </div>
    </div>

    <script>
        function togglePassword(inputId) {
            const input = document.getElementById(inputId);
            const icon = input.nextElementSibling.querySelector('i');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
    </script>
</body>
</html>