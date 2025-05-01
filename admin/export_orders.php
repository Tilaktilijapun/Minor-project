<?php
session_start();
include '../includes/dbconn.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$format = $_GET['format'] ?? 'pdf';

$query = "SELECT o.*, u.username, u.email, u.phone, u.address
          FROM orders o
          LEFT JOIN user u ON o.user_id = u.id
          ORDER BY o.created_at DESC";
          
$result = $conn->query($query);

if ($format === 'excel') {
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename="orders.xls"');
    
    echo "Order ID\tCustomer\tEmail\tPhone\tStatus\tTotal\tDate\n";
    
    while ($row = $result->fetch_assoc()) {
        echo implode("\t", [
            $row['id'],
            $row['username'],
            $row['email'],
            $row['phone'],
            $row['status'],
            $row['total_price'],
            $row['created_at']
        ]) . "\n";
    }
} else {
    require_once('../vendor/autoload.php');
    // Add PDF generation code here using a library like TCPDF or FPDF
}

$conn->close();