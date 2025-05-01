<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'snapcart');

// Site configuration
define('SITE_NAME', 'SnapCart Admin');
define('SITE_URL', '/minor project/');
define('ADMIN_URL', '/minor project/admin/');
define('UPLOAD_PATH', $_SERVER['DOCUMENT_ROOT'] . '/minor project/assets/images/');
define('UPLOAD_URL', '/minor project/assets/images/');

// Session timeout (in seconds)
define('SESSION_TIMEOUT', 1800); // 30 minutes

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Time zone
date_default_timezone_set('Asia/Kathmandu');

// Create database connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Function to sanitize input data
function sanitize($data) {
    global $conn;
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    $data = $conn->real_escape_string($data);
    return $data;
}

// Function to check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']);
}

// Function to redirect
function redirect($url) {
    header("Location: " . $url);
    exit();
}

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>