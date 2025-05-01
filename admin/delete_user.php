<?php
session_start();
include '../includes/dbconn.php';

// Check for admin session
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

// Verify CSRF token
if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token']) || 
    $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    die('Invalid request');
}

// Validate user_id
if (!isset($_POST['user_id']) || !is_numeric($_POST['user_id'])) {
    $_SESSION['error'] = 'Invalid user ID';
    header('Location: users.php');
    exit();
}

$user_id = (int)$_POST['user_id'];

try {
    // Start transaction
    $conn->begin_transaction();

    // Delete user's orders first (if any)
    $stmt = $conn->prepare("DELETE FROM orders WHERE user_id = ?");
    $stmt->bind_param('i', $user_id);
    $stmt->execute();

    // Delete the user
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ? AND id != ?");
    $stmt->bind_param('ii', $user_id, $_SESSION['admin_id']);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        $conn->commit();
        $_SESSION['success'] = 'User deleted successfully';
    } else {
        throw new Exception('Could not delete user');
    }

} catch (Exception $e) {
    $conn->rollback();
    $_SESSION['error'] = 'Error deleting user: ' . $e->getMessage();
}

header('Location: users.php');
exit();