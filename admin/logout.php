<?php
session_start();
session_destroy();
header("Location: /minor project/admin/login.php");
?>
