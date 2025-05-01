<?php
include '../includes/dbconn.php';
$result = $conn->query("SELECT * FROM products");
$products = [];
while ($row = $result->fetch_assoc()) $products[] = $row;
echo json_encode($products);
?>
