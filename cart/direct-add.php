<?php
session_start();

// Initialize cart if it doesn't exist
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Check if we have direct add data
if (isset($_POST['direct_add'])) {
    try {
        $item = json_decode($_POST['direct_add'], true);
        
        if (json_last_error() === JSON_ERROR_NONE && isset($item['id'])) {
            $product_id = $item['id'];
            
            // Add to cart
            if (isset($_SESSION['cart'][$product_id])) {
                $_SESSION['cart'][$product_id]['quantity']++;
            } else {
                $_SESSION['cart'][$product_id] = [
                    'id' => $product_id,
                    'name' => $item['name'] ?? 'Unknown Product',
                    'price' => $item['price'] ?? 0,
                    'image' => $item['image'] ?? '',
                    'quantity' => 1
                ];
            }
            
            echo json_encode(['success' => true]);
            exit;
        }
    } catch (Exception $e) {
        // Log error
        error_log('Error in direct-add.php: ' . $e->getMessage());
    }
}

// If we get here, something went wrong
echo json_encode(['success' => false, 'message' => 'Invalid data']);
?>