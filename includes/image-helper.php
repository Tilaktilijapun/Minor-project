<?php
/**
 * Helper function to find the correct image path for a product
 * @param int $product_id The product ID
 * @param string $original_path The original image path from database
 * @return string The correct image path or placeholder path
 */
function getProductImagePath($product_id, $original_path = '') {
    // Define possible image locations with multiple formats
    $possible_paths = [
        $original_path,
        "/minor project/uploads/products/{$product_id}.jpg",
        "/minor project/uploads/products/{$product_id}.png",
        "/minor project/uploads/products/{$product_id}.webp",
        "/minor project/uploads/product-{$product_id}.jpg",
        "/minor project/uploads/product-{$product_id}.png",
        "/minor project/uploads/product-{$product_id}.webp",
        "/minor project/assets/images/products/{$product_id}.jpg",
        "/minor project/assets/images/products/{$product_id}.png",
        "/minor project/assets/images/products/{$product_id}.webp",
        "/minor project/assets/images/product-{$product_id}.jpg",
        "/minor project/assets/images/product-{$product_id}.png",
        "/minor project/assets/images/product-{$product_id}.webp"
    ];
    
    // If original path is not empty, try to fix it
    if (!empty($original_path)) {
        // Remove any double slashes except after protocol
        $original_path = preg_replace('#(?<!:)//+#', '/', $original_path);
        
        // Fix spaces in image paths
        $original_path = str_replace(' ', '%20', $original_path);
        
        // Handle relative paths
        if (!preg_match('/^\/minor project\//', $original_path)) {
            // If path doesn't start with /minor project/
            if (strpos($original_path, 'assets/images/') !== false) {
                // If it contains assets/images/ but missing the prefix
                $fixed_path = '/minor project/' . $original_path;
                array_unshift($possible_paths, $fixed_path);
            } else {
                // Otherwise assume it's just a filename
                $basename = basename($original_path);
                $fixed_path = '/minor project/assets/images/' . $basename;
                array_unshift($possible_paths, $fixed_path);
                
                // Also try with different extensions
                $filename = pathinfo($basename, PATHINFO_FILENAME);
                array_unshift($possible_paths, "/minor project/assets/images/{$filename}.jpg");
                array_unshift($possible_paths, "/minor project/assets/images/{$filename}.png");
                array_unshift($possible_paths, "/minor project/assets/images/{$filename}.webp");
            }
        }
    }
    
    // Check each path to see if file exists
    foreach ($possible_paths as $path) {
        if (empty($path)) continue;
        
        // Convert to server path
        $server_path = $_SERVER['DOCUMENT_ROOT'] . $path;
        
        // Check if file exists
        if (file_exists($server_path)) {
            return $path;
        }
    }
    
    // If no image found, return placeholder
    return '/minor project/assets/images/placeholder.jpg';
}
?>