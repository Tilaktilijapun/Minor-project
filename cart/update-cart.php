<?php
session_start();
include '../includes/dbconn.php';

// Initialize response array
$response = array('success' => false, 'message' => '');

// Check if cart exists
if(!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = array();
}

// Check if action is set
if(isset($_POST['action']) && isset($_POST['key'])) {
    $key = intval($_POST['key']);
    $action = $_POST['action'];
    
    // Check if key exists in cart
    if(isset($_SESSION['cart'][$key])) {
        // Update quantity
        if($action == 'update' && isset($_POST['quantity'])) {
            $quantity = intval($_POST['quantity']);
            
            // Validate quantity
            if($quantity <= 0) {
                $response['message'] = 'Quantity must be greater than 0';
            } else {
                // Check if product has enough stock
                $product_id = $_SESSION['cart'][$key]['id'];
                $stmt = $conn->prepare("SELECT stock_quantity FROM products WHERE id = ?");
                $stmt->bind_param("i", $product_id);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if($result->num_rows > 0) {
                    $product = $result->fetch_assoc();
                    
                    if($quantity <= $product['stock_quantity']) {
                        $_SESSION['cart'][$key]['quantity'] = $quantity;
                        $response['success'] = true;
                        $response['message'] = 'Cart updated successfully';
                    } else {
                        $response['message'] = 'Not enough stock available. Only ' . $product['stock_quantity'] . ' items available.';
                    }
                } else {
                    $response['message'] = 'Product not found';
                }
            }
        }
        // Remove item from cart
        elseif($action == 'remove') {
            array_splice($_SESSION['cart'], $key, 1);
            $response['success'] = true;
            $response['message'] = 'Item removed from cart';
        }
        else {
            $response['message'] = 'Invalid action';
        }
    } else {
        $response['message'] = 'Item not found in cart';
    }
} else {
    $response['message'] = 'Invalid request';
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);
?>
