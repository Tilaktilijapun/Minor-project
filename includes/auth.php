<?php
include '../includes/dbconn.php';
function isAdmin() {
    if (!isset($_SESSION['user_id'], $_SESSION['role'])) {
        return false;
    }
    
    // Check if user is admin
    return $_SESSION['role'] === 'admin';
}

function requireAdmin() {
    if (!isAdmin()) {
        $_SESSION['error'] = "You must be an administrator to access this page.";
        header('Location: /minor project/auth/login.php');
        exit();
    }
}
?>