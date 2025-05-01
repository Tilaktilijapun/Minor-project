<?php
session_start();
require_once '../includes/dbconn.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? AND user_type = 'admin'");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $admin = $result->fetch_assoc();

    if ($admin && password_verify($password, $admin['password'])) {
        // Set admin session variables
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_name'] = $admin['name'];
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        
        // Redirect to admin dashboard
        header('Location: dashboard.php');
        exit();
    } else {
        $_SESSION['error'] = "Invalid admin credentials";
        header('Location: login.php');
        exit();
    }
}