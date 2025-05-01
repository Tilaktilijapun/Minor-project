<?php
session_start();

// Initialize cart if not exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Count items in cart
$count = count($_SESSION['cart']);

// Return count as JSON
header('Content-Type: application/json');
echo json_encode(['count' => $count]);
?>