<?php
function formatPrice($price) {
    return number_format($price, 2);
}

function sanitizeInput($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

function generateOrderId() {
    return 'ORD-' . date('Ymd') . '-' . substr(uniqid(), -5);
}

function updateStock($conn, $productId, $quantity) {
    $stmt = $conn->prepare("UPDATE products SET stock = stock - ? WHERE id = ? AND stock >= ?");
    $stmt->bind_param("iii", $quantity, $productId, $quantity);
    return $stmt->execute();
}

function getProductDetails($conn, $productId) {
    $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->bind_param("i", $productId);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}