<?php
session_start();
include '../includes/dbconn.php';

if (!isset($_SESSION['admin_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['orderId']) && isset($data['status'])) {
    $orderId = $conn->real_escape_string($data['orderId']);
    $status = $conn->real_escape_string($data['status']);
    $note = isset($data['note']) ? $conn->real_escape_string($data['note']) : '';
    
    $query = "UPDATE orders SET 
              status = '$status', 
              updated_at = NOW(),
              status_note = '$note'
              WHERE id = $orderId";
              
    $result = $conn->query($query);
    
    header('Content-Type: application/json');
    echo json_encode(['success' => $result]);
} else {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
}

$conn->close();