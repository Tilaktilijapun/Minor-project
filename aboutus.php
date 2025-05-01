<?php 
session_start();
include "includes/dbconn.php"; 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SnapCart - About Us</title>
    <link rel="stylesheet" href="../minor project/assets/css/aboutus.css">


</head>
<body>
   <!-- Navigation -->
   <nav class="navbar">
    <div class="container">
        <a href="index.php" class="logo">
            <img src="../minor project/assets/images/logo.png" alt="SnapCart" width="125px">
        </a>
        <ul class="nav-links">
            <li><a href="index.php">Home</a></li>
            <li><a href="product/product.php">Products</a></li>
            <li><a href="aboutus.php" class="active">About</a></li>
            <li><a href="contact.php">Contact</a></li>
            <li><a href="auth/account.php">Account</a></li>
        </ul>
        
</nav>

    <div class="container">
        <section class="about-section">
            <h2 class="section-title">About SnapCart</h2>

            <div class="content-block">
                <h3>Who We Are</h3>
                <p>Welcome to SnapCart, your premier destination for cutting-edge electronics and tech accessories. Established in 2024, we've quickly become one of Nepal's most trusted online electronics retailers.</p>
                
                <p>At SnapCart, we carefully curate our product selection to bring you the latest and most reliable electronic devices, from smartphones and laptops to audio equipment and accessories. Our commitment to quality ensures that every product we offer meets the highest standards of performance and durability.</p>
                
                <p>What sets us apart is our dedication to customer satisfaction:</p>
                
                <ul class="highlights">
                    <li>100% authentic products with manufacturer warranty</li>
                    <li>Competitive prices and regular deals</li>
                    <li>Expert technical support</li>
                    <li>Secure payment options</li>
                    <li>Fast delivery across Nepal</li>
                </ul>
            </div>

            <div class="content-block">
                <h3>Our Mission</h3>
                <p>To revolutionize online electronics shopping in Nepal by providing premium products, exceptional service, and an unmatched customer experience.</p>
            </div>

            <div class="content-block">
                <h3>Our Vision</h3>
                <p>To become Nepal's most trusted e-commerce platform for electronics, known for our reliability, innovation, and customer-first approach.</p>
            </div>

            <div class="content-block">
                <h3>Our Values</h3>
                <p>Authenticity in products, transparency in business, excellence in service, and unwavering commitment to customer satisfaction.</p>
            </div>

            <div class="content-block">
                <h3>Our Team</h3>
                <div class="team-section">
                    <div class="team-member">
                        <h4>Tilak Tilija Pun</h4>
                        <p>Team Leader</p>
                    </div>
                    <div class="team-member">
                        <h4>Sandesh Baral</h4>
                        <p>Assistant</p>
                    </div>
                    <div class="team-member">
                        <h4>Sankalpa Paudel</h4>
                        <p>Member</p>
                    </div>
                    <div class="team-member">
                        <h4>Anush Shrestha</h4>
                        <p>Member</p>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <footer>
        <p>&copy; 2025 SnapCart. All rights reserved.</p>
    </footer>
</body>
</html>