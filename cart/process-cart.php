<?php
session_start();
include '../includes/dbconn.php';

// Initialize response array
$response = ['success' => false, 'message' => 'Invalid request'];

// Check if request is POST and has required data
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'])) {
    $product_id = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
    $action = isset($_POST['action']) ? $_POST['action'] : 'cart';
    
    // Validate product exists and has stock
    $stmt = $conn->prepare("SELECT id, name, price, stock, image FROM products WHERE id = ? AND status = 'active'");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $product = $stmt->get_result()->fetch_assoc();

    if ($product && $product['stock'] > 0) {
        // Initialize cart if it doesn't exist
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
        
        // Add product to cart
        if (isset($_SESSION['cart'][$product_id])) {
            $_SESSION['cart'][$product_id]['quantity']++;
        } else {
            $_SESSION['cart'][$product_id] = [
                'name' => $product['name'],
                'price' => $product['price'],
                'quantity' => 1,
                'image' => $product['image']
            ];
        }
        
        // If action is buy, set a session flag for direct checkout
        if ($action === 'buy') {
            $_SESSION['direct_checkout'] = true;
        }
        
        $response = [
            'success' => true, 
            'message' => 'Product added successfully',
            'cart_count' => count($_SESSION['cart']),
            'action' => $action
        ];
    } else {
        $response = ['success' => false, 'message' => 'Product unavailable or out of stock'];
    }
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);
exit;
?>