<?php
include '../includes/dbconn.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $full_name = mysqli_real_escape_string($conn, $_POST['fullname']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $confirm_password = mysqli_real_escape_string($conn, $_POST['confirmpassword']);
    $street = mysqli_real_escape_string($conn, $_POST['street']);
    $city = mysqli_real_escape_string($conn, $_POST['city']);
    $state = mysqli_real_escape_string($conn, $_POST['state']);
    $terms_accepted = isset($_POST['terms']) ? 1 : 0;
    $role = 'user'; // Set default role to user

    if (isset($_POST['role'])) {
        $selected_role = mysqli_real_escape_string($conn, $_POST['role']);
        if ($selected_role === 'admin') {
    
            $admin_check_sql = "SELECT COUNT(*) FROM `user` WHERE role = 'admin'";
            $admin_check_result = mysqli_query($conn, $admin_check_sql);

            if ($admin_check_result) {
                $admin_count = mysqli_fetch_array($admin_check_result)[0];

                if ($admin_count == 0) {
        
                    $role = 'admin';
                } else {
                    echo "An admin account already exists.";
                    exit; 
                }
            } else {
                echo "Error checking for existing admin: " . mysqli_error($conn);
                exit;
            }
        }
    }
    if ($password !== $confirm_password) {
        echo "Passwords do not match!";
        exit;
    }

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $sql = "INSERT INTO `user` (fullname, email, phone, username, password, street, city, state, terms_accepted, role) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $sql);

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "sssssssssi", $full_name, $email, $phone, $username, $hashed_password, $street, $city, $state, $terms_accepted, $role);

        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['registration_success'] = true;
            header("Location: /minor project/auth/login.php");
            exit();
        } else {
            echo "Error: " . mysqli_stmt_error($stmt);
        }

        mysqli_stmt_close($stmt);
    } else {
        echo "Error preparing statement: " . mysqli_error($conn);
    }


    mysqli_close($conn);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Electronics Hub</title>
    <link rel="stylesheet" href="/minor project/assets/css/register.css">
</head>
<body>
    <div class="navbar">
        <div class="logo"><img src="/minor project/assets/images/logo.png.png" height="70px" width="70px"></div>
        <div class="navbar-links">
            <a href="/minor project/index.php">Home</a>
            <a href="/minor project/admin/product.php">Products</a>
            <a href="/minor project/signup.php">Signup</a>
            <a href="/minor project/contact.php">Contact</a>
        </div>
    </div>

    <div class="main-content">
        <div class="container">
        <form class="register-form" id="registerForm" action="register.php" method="POST" onsubmit="return validateForm()">
    <h1>Create an Account</h1>

    <div class="form-group">
        <label for="fullname">Full Name</label>
        <input type="text" id="fullname" name="fullname" placeholder="Enter your full name" required>
    </div>

    <div class="form-group">
        <label for="email">Email</label>
        <input type="email" id="email" name="email" placeholder="Enter your email" required>
    </div>

    <div class="form-group">
        <label for="phone">Phone Number</label>
        <input type="tel" id="phone" name="phone" placeholder="Enter your phone number" required>
    </div>

    <div class="form-group">
        <label for="username">Username</label>
        <input type="text" id="username" name="username" placeholder="Choose a username" required>
    </div>

    <div class="form-group">
        <label for="password">Set Password</label>
        <input type="password" id="password" name="password" placeholder="Set your password" required>
    </div>

    <div class="form-group">
        <label for="confirmpassword">Confirm Password</label>
        <input type="password" id="confirmpassword" name="confirmpassword" placeholder="Confirm your password" required>
    </div>

    <div class="form-group">
        <label for="street">Street</label>
        <input type="text" id="street" name="street" placeholder="Street Address" required>
    </div>

    <div class="form-group">
        <label for="city">City</label>
        <input type="text" id="city" name="city" placeholder="City" required>
    </div>

    <div class="form-group">
        <label for="state">State</label>
        <input type="text" id="state" name="state" placeholder="State" required>
    </div>

    <div class="form-group terms">
        <input type="checkbox" id="terms" name="terms" required>
        <label for="terms">I agree to the <a href="#">Terms and Conditions</a></label>
    </div>

     <div class="form-group">
        <label for="role">Role</label>
        <select id="role" name="role">
            <option value="user" selected>User</option>
            <?php
            $admin_check_sql = "SELECT COUNT(*) FROM `user` WHERE role = 'admin'";
            $admin_check_result = mysqli_query($conn, $admin_check_sql);

            if ($admin_check_result) {
                $admin_count = mysqli_fetch_array($admin_check_result)[0];

                if ($admin_count == 0) {
                    echo '<option value="admin">Admin</option>';
                }
            }
            ?>
        </select>
    </div>

    <div class="form-group">
        <button type="submit">Register</button>
    </div>

    <div class="footer">
        Already have an account? <a href="#">Login</a>
    </div>
</form>

    <script src="/minor project/assets/js/register.js"></script>
</body>
</html>
