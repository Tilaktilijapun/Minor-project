<?php
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    include '../includes/dbconn.php';

    $Name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $role = 'user';

    $checkQuery = "SELECT * FROM `user` WHERE email = '$email'";
    $checkResult = mysqli_query($conn, $checkQuery);

    if ($checkResult && mysqli_num_rows($checkResult) > 0) {
        echo "User with this email already exists!";
    } else {
        $insertQuery = "INSERT INTO `user` (name, email, password, role) VALUES ('$Name', '$email', '$password', '$role')";
        if (mysqli_query($conn, $insertQuery)) {
            echo "Signup successful!";
            header("Location: /minor project/index.php");
                exit();
        } else {
            echo "Error: " . mysqli_error($conn);
        }
    }

    mysqli_close($conn);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up Page</title>
    <link rel="stylesheet" href="/minor project/assets/css/signup.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <img src="/minor project/assets/images/logo.png.png" alt="Logo">
            <nav>
                <a href="/minor project/index.php">Home</a>
                <a href="/minor project/demo3.php">About Us</a>
                <a href="#">Help</a>
                <a href="/minor project/contact.php">Contact</a>
            </nav>
        </div>
        <div class="main">
            <div class="image-section">
                <img src="/minor project/assets/images/photo 2.1.png.png" alt="Image">
            </div>
            <div class="form-section">
                <h2>Sign Up</h2>
                <form method="POST" action="">
                    <input type="text" name="name" placeholder="Full Name" required>
                    <input type="email" name="email" placeholder="Email Address" required>
                    <input type="password" name="password" placeholder="Password" required>
                    <label>
                        <input type="checkbox" name="remember_me"> Remember Me
                    </label>
                    <button type="submit">Sign Up</button>
                </form>
                <p>Already have an account? <a href="/minor project/auth/login.php">Sign in</a>.</p>
            </div>
        </div>
        <div class="footer">
            Terms & Conditions | Privacy Policy
            <div class="social-icons">
                <a href="https://www.facebook.com" target="_blank">
                    <i class="fa-brands fa-facebook"></i>
                </a>
                <a href="https://www.instagram.com" target="_blank">
                    <i class="fa-brands fa-instagram"></i>
                </a>
                <a href="https://github.com/Tilaktilijapun/Minor-project" target="_blank">
                    <i class="fa-brands fa-github"></i>
                </a>
            </div>
        </div>
    </div>
    <script src="/minor project/assets/js/signup.js" defer></script>
</body>

</html>
