<?php
include '../includes/dbconn.php';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id'])) {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $price = $_POST['price'];
    $desc = $_POST['description'];
    $stmt = $conn->prepare("UPDATE products SET name=?, price=?, description=? WHERE id=?");
    $stmt->bind_param("sdsi", $name, $price, $desc, $id);
    $stmt->execute();
}
?>
