<?php
session_start();
require_once '../includes/dbconn.php';
require_once '../includes/auth.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'Product ID not provided']);
    exit();
}

$product_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$product_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid product ID']);
    exit();
}

try {
    $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
    $stmt->bind_param("i", $product_id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to delete product']);
    }
} catch (Exception $e) {
    error_log("Error deleting product: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error']);
}

$stmt->close();
$conn->close();