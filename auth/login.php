<?php
session_start();
include '../includes/dbconn.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (empty($username) || empty($password)) {
        echo "Username and Password are required!";
    } else {
        $stmt = $conn->prepare("SELECT * FROM user WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();

            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['login_success'] = true;
                
                if ($user['role'] === 'admin') {
                    header("Location: /minor project/admin/dashboard.php");
                } else {
                    header("Location: /minor project/index.php");
                }
                exit();
            } else {
                echo "Invalid credentials!";
            }
        } else {
            echo "No user found with that username!";
        }

        $stmt->close();
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login</title>
  <link rel="stylesheet" href="/minor project/assets/css/login.css">
</head>
<body>
  <div class="login-container">
    <h2>Welcome Back</h2>
    <p class="subtitle">Please login to continue</p>
    <form method="POST" action="login.php">
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
      <button type="submit">Login</button>
      <p class="forgot-password"><a href="#">Forgot Password?</a></p>
    </form>
    <div id="loading" class="loading-spinner hidden"></div>
  </div>
  <script src="/minor project/assets/js/login.js"></script>
</body>
</html>

   
