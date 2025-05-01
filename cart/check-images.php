<?php
session_start();
include '../includes/dbconn.php';

header('Content-Type: application/json');

try {
    // Check if cart exists
    if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
        echo json_encode(['error' => 'Cart is empty']);
        exit;
    }
    
    $results = [];
    
    // Check each image in the cart
    foreach ($_SESSION['cart'] as $key => $item) {
        if (!empty($item['image'])) {
            $image_path = $item['image'];
            
            // Check if the image exists in the file system
            $file_exists = false;
            
            // Try different path formats
            $paths_to_check = [
                $image_path,
                str_replace(' ', '%20', $image_path),
                str_replace('%20', ' ', $image_path),
                '/minor project/' . ltrim($image_path, '/'),
                '../' . ltrim(str_replace('/minor project/', '', $image_path), '/'),
                '/minor project/assets/images/' . basename($image_path)
            ];
            
            foreach ($paths_to_check as $path) {
                $server_path = $_SERVER['DOCUMENT_ROOT'] . $path;
                if (file_exists($server_path)) {
                    $file_exists = true;
                    break;
                }
            }
            
            $results[$key] = [
                'product_name' => $item['name'],
                'image_path' => $image_path,
                'exists' => $file_exists,
                'paths_checked' => $paths_to_check
            ];
        }
    }
    
    echo json_encode([
        'success' => true,
        'results' => $results
    ]);
} catch (Exception $e) {
    echo json_encode([
        'error' => $e->getMessage()
    ]);
}
?>