<?php
session_start();
include '../includes/dbconn.php';

// Initialize cart if it doesn't exist
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Check if product_id is set
if (isset($_POST['product_id'])) {
    $product_id = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
    $quantity = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT) ?: 1;
    $name = $_POST['name'] ?? '';
    $price = filter_input(INPUT_POST, 'price', FILTER_VALIDATE_FLOAT) ?: 0;
    $image = $_POST['image'] ?? '';
    
    // Validate product exists in database (optional but recommended)
    $stmt = $conn->prepare("SELECT id, name, price, image FROM products WHERE id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $product_db = $result->fetch_assoc();
    
    if ($product_id) {
        // If product exists in cart, update quantity
        if (isset($_SESSION['cart'][$product_id])) {
            $_SESSION['cart'][$product_id]['quantity'] += $quantity;
        } else {
            // Add new product to cart
            $_SESSION['cart'][$product_id] = [
                'id' => $product_id,
                'name' => $name ?: ($product_db['name'] ?? 'Unknown Product'),
                'price' => $price ?: ($product_db['price'] ?? 0),
                'image' => $image ?: ($product_db['image'] ?? ''),
                'quantity' => $quantity
            ];
        }
        
        echo json_encode([
            'success' => true, 
            'message' => 'Product added to cart',
            'cart_count' => count($_SESSION['cart'])
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid product ID']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Product ID not provided']);
}