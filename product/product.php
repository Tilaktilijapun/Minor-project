<?php
session_start();
include '../includes/dbconn.php';

// Initialize cart
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Get all active products
$query = "SELECT id, name, price, image, stock FROM products WHERE status = 'active' ORDER BY id";
$result = $conn->query($query);

if (!$result) {
    die("Database query failed: " . $conn->error);
}

// Handle add to cart
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'])) {
    $product_id = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
    
    // Validate product exists and has stock
    $stmt = $conn->prepare("SELECT id, name, price, stock, image FROM products WHERE id = ? AND status = 'active'");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $product = $stmt->get_result()->fetch_assoc();

    if ($product && $product['stock'] > 0) {
        if (isset($_SESSION['cart'][$product_id])) {
            $_SESSION['cart'][$product_id]['quantity']++;
        } else {
            $_SESSION['cart'][$product_id] = [
                'id' => $product_id,
                'name' => $product['name'],
                'price' => $product['price'],
                'image' => $product['image'] ?? '',
                'quantity' => 1
            ];
        }
        echo json_encode(['success' => true, 'cart_count' => count($_SESSION['cart'])]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Product unavailable']);
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SnapCart</title>
    <link rel="stylesheet"  href="/minor project/assets/css/product.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"rel="stylesheet" />
    <link href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap">
</head>
<body>
    <div class="header">
        <div class="container">
          <div class="navbar">
            <div class="logo">
              <img src="/minor project/assets/images/logo.png.png" alt="SnapCart" width="125px" />
            </div>
            
            <nav>
              <ul id="MenuItems">
                <li><a href="/minor project/index.php">Home</a></li>
                <li><a href="/minor project/product/product.php">Products</a></li>
                <li><a href="/minor project/aboutus.php">About</a></li>
                <li><a href="/minor project/contact.php">Contact</a></li>
                <li><a href="/minor project/auth/account.php">Account</a></li>
              </ul>
            </nav>
            <div class="cart-icon-container">
                <img src="/minor project/assets/images/cart.png" onclick="window.location.href='/minor project/cart/view-cart.php'" alt="Shopping cart icon" width="30px" height="30px" id="cart-icon" />
                <span class="cart-count" id="cart-count"><?php echo count($_SESSION['cart']); ?></span>
            </div>
            
      
          </div>
          <div class="row">
            <div class="col-2">
              <h1>Upgrade Your Lifestyle <br />with Snapcart!!</h1>
              <p>
                "Snapcart is your ultimate destination for trendsetting gadgets, fashion, and essentials—all in one place. Upgrade your lifestyle with seamless shopping, unbeatable deals, and effortless convenience!"
                
              </p>
              <a href="/minor project/product/product.php" class="btn">Explore Now &#8594; </a>
            </div>
            <div class="col-2">
              <img src="/minor project/assets/images/product_home.png" alt="main products" />
            </div>
          </div>
        </div>
      </div>

   
    <section class="products" id="products">
    <form id="addtocartForm"  method="POST">
        <div class="product">
             <a href="/minor project/product/homeiphone16promax.php"><img src="/minor project/assets/images/iphone16 pro max.jpg" alt="iphone 16"></a>
            <h3>iphone 16 pro max</h3>
            <p>Rs. 200,000</p>
            <button type="submit" onclick="setAction('/minor project/cart/add-to-cart.php')" class="add-to-cart-btn" data-name="iphone 16 pro max" data-price="200000" data-image="/minor project/assets/images/iphone16.webp">
    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
    <input type="hidden" name="product_id" value="4">
    Add to Cart
</button>
</button>
        </div>
        <div class="product">
          <a href="/minor project/product/Digital_watch_with_fitness.php"> <img src="/minor project/assets/images/Watch.png" alt="Watch"></a>
            <h3>Digital Watch</h3>
            <p>Rs. 5,000</p>
            <button type="submit" onclick="setAction('/minor project/cart/add-to-cart.php')" class="add-to-cart-btn" data-name="Digital-watch" data-price="5000" data-image="Digital-watch-with-fitness/Watch.png">
    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
    <input type="hidden" name="product_id" value="11">
    Add to Cart
</button>
        </div>
        <div class="product">
          <a href="/minor project/product/headphone1.php">  <img src="/minor project/assets/images/headphone.png" alt="headphone"></a>
            <h3>Simple headphone wireless</h3>
            <p>Rs. 3,000</p>
            <button type="submit" onclick="setAction('/minor project/cart/add-to-cart.php')" class="add-to-cart-btn" data-name="Simple headphone wireless" data-price="3000" data-image="/minor project/assets/images/headphone.png">
    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
    <input type="hidden" name="product_id" value="14">
    Add to Cart
</button>
        </div>
        <div class="product">
            <a href="/minor project/product/Ultima-airpot.php"> <img src="/minor project/assets/images/airpot.png" alt="airport"></a>
            <h3>Ultima airport</h3>
            <p>Rs. 4,000</p>
            <button type="submit" onclick="setAction('/minor project/cart/add-to-cart.php')" class="add-to-cart-btn" data-name="Ultima airport" data-price="4000" data-image="/minor project/assets/images/airpot.png">
    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
    <input type="hidden" name="product_id" value="15">
    Add to Cart
</button>
        </div>
        <div class="product">
            <a href="/minor project/product/powerbank.php"> <img src="/minor project/assets/images/powerbank.png" alt="powerbank"></a>
            <h3>Power bank </h3>
            <p>Rs. 5,000</p>
            <button type="submit" onclick="setAction('/minor project/cart/add-to-cart.php')" class="add-to-cart-btn" data-name="Power bank" data-price="5000" data-image="/minor project/assets/images/powerbank.png">
    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
    <input type="hidden" name="product_id" value="16">
    Add to Cart
</button>
        </div>
        
        
<div class="product">
          <a href="/minor project/product/HP-victus.php">  <img src="/minor project/assets/images/laptop.png" alt="Laptop"></a>
            <h3>Hp victus</h3>
            <p>Rs. 106,000</p>
            <button type="submit" onclick="setAction('/minor project/cart/add-to-cart.php')" class="add-to-cart-btn" data-name="Hp victus" data-price="106000" data-image="/minor project/assets/images/laptop.png">
    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
    <input type="hidden" name="product_id" value="17">
    Add to Cart
</button>
        </div>

        <div class="product">
            <a href="/minor project/product/acernitro5.php">  <img src="/minor project/assets/images/acernitrolaptop.webp" alt="Laptop"></a>
              <h3>Acer Nitro 5</h3>
              <p>Rs. 107,000</p>
              <button type="submit" onclick="setAction('/minor project/cart/add-to-cart.php')" class="add-to-cart-btn" data-name="Acer Nitor 5" data-price="10700" data-image="/minor project/assets/images/acernitrolaptop.webp">
    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
    <input type="hidden" name="product_id" value="18">
    Add to Cart
</button>
          </div>

          <div class="product">
             <a href="/minor project/product/SamsungA15.php"> <img src="/minor project/assets/images/Samsung A15.webp" alt="Samsung mobile"></a>
             <h3>Samsung A15</h3>
              <p>Rs. 38,000</p>
<button type="submit" onclick="setAction('/minor project/cart/add-to-cart.php')" class="add-to-cart-btn" data-name="SamsungA15" data-price="38000" data-image="/minor project/assets/images/Samsung A15.webp">
    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
    <input type="hidden" name="product_id" value="19">
    Add to Cart
</button>
          </div>

          <div class="product">
              <a href="/minor project/product/samung-galaxy_homes24.php"><img src="/minor project/assets/images/Samsung-Galaxy-S24-FE-Blue.jpg" alt="Samsung mobile new"></a>
              <h3>Samsung Galaxy S25</h3>
              <p>Rs. 59,000</p>
              <button type="submit" onclick="setAction('/minor project/cart/add-to-cart.php')" class="add-to-cart-btn" data-name="Samsung-galaxy-s24" data-price="59000" data-image="/minor project/assets/images/Samsung-Galaxy-S24-FE-Blue.jpg">
    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
    <input type="hidden" name="product_id" value="20">
    Add to Cart
</button>
          </div>


          <div class="product">
             <a href="/minor project/product/Simpledigital-watch.php"> <img src="/minor project/assets/images/Watch1.webp" alt="Watch1"></a>
              <h3>simle Digital watch</h3>
              <p>Rs. 10,000</p>
              <button type="submit" onclick="setAction('/minor project/cart/add-to-cart.php')" class="add-to-cart-btn" data-name="Simpledigital-watch" data-price="10000" data-image="/minor project/assets/images/Watch1.webp">
    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
    <input type="hidden" name="product_id" value="21">
    Add to Cart
</button>
          </div>

          <div class="product">
             <a href="/minor project/product/Digital-watch.php"> <img src="/minor project/assets/images/Watch2.webp" alt="Watch2"></a>
              <h3>Digital watch </h3>
              <p>Rs. 11,000</p>
              <button type="submit" onclick="setAction('/minor project/cart/add-to-cart.php')" class="add-to-cart-btn" data-name="Digital-watch" data-price="11000" data-image="/minor project/assets/images/Watch2.webp">
    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
    <input type="hidden" name="product_id" value="22">
    Add to Cart
</button>
          </div>

          <div class="product">
             <a href="/minor project/product/Digital-watch-latest.php"> <img src="/minor project/assets/images/Watch3.webp" alt="Watch3"></a>
              <h3>Digital watch with latest feature</h3>
              <p>Rs. 12,000</p>
              <button type="submit" onclick="setAction('/minor project/cart/add-to-cart.php')" class="add-to-cart-btn" data-name="Digital-watch-latest" data-price="12000" data-image="/minor project/assets/images/Watch3.webp">
    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
    <input type="hidden" name="product_id" value="23">
    Add to Cart
</button>
          </div>

          <div class="product">
               <a href="/minor project/product/Digitalgaming-watch.php"><img src="/minor project/assets/images/Watch4gaming.webp" alt="Watch"></a>
              <h3>Digital Gaming watch</h3>
              <p>Rs. 13,000</p>
              <button type="submit" onclick="setAction('/minor project/cart/add-to-cart.php')" class="add-to-cart-btn" data-name="Digitalgaming-watch" data-price="13000" data-image="/minor project/assets/images/Watch4gaming.webp">
    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
    <input type="hidden" name="product_id" value="24">
    Add to Cart
</button>
          </div>

          <div class="product">
            <a href="/minor project/product/HP-victus.php">  <img src="/minor project/assets/images/Hpvictus.png" alt="Hp victus 15"></a>
              <h3>HP victus</h3>
              <p>Rs. 114,000</p>
              <button type="submit" onclick="setAction('/minor project/cart/add-to-cart.php')" class="add-to-cart-btn" data-name="HP-victus" data-price="114000" data-image="/minor project/assets/images/Hpvictus.png">
    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
    <input type="hidden" name="product_id" value="25">
    Add to Cart
</button>
          </div>

          <div class="product">
               <a href="/minor project/product/HP-Laptop.php"><img src="/minor project/assets/images/HP laptop.webp" alt="Hp latop"></a>
              <h3>HP laptop</h3>
              <p>Rs. 115,000</p>
              <button type="submit" onclick="setAction('/minor project/cart/add-to-cart.php')" class="add-to-cart-btn" data-name="HP-Laptop" data-price="115000" data-image="/minor project/assets/images/HP laptop.webp">
    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
    <input type="hidden" name="product_id" value="26">
    Add to Cart
</button>
          </div>

          <div class="product">
               <a href="/minor project/product/Mic.php"><img src="/minor project/assets/images/mic.png" alt="mic"></a>
              <h3>Mic</h3>
              <p>Rs. 11000</p>
              <button type="submit" onclick="setAction('/minor project/cart/add-to-cart.php')" class="add-to-cart-btn" data-name="Mic" data-price="11000" data-image="/minor project/assets/images/mic.png">
    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
    <input type="hidden" name="product_id" value="27">
    Add to Cart
</button>
          </div>

          <div class="product">
               <a href="/minor project/product/Ultima-Atom192.php"><img src="/minor project/assets/images/airport6.jpg" alt="airport6"></a>
              <h3>Ultima atom 192</h3>
              <p>Rs. 2799</p>
              <button type="submit" onclick="setAction('/minor project/cart/add-to-cart.php')" class="add-to-cart-btn" data-name="Ultima-Atom192" data-price="2799" data-image="/minor project/assets/images/airport6.jpg">
    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
    <input type="hidden" name="product_id" value="28">
    Add to Cart
</button>
          </div>

          <div class="product">
            <a href="/minor project/product/Redmi-Bud5.php">  <img src="/minor project/assets/images/airport7.jpg" alt="airport7"></a>
              <h3>Redmi buds 5</h3>
              <p>Rs. 4800</p>
              <button type="submit" onclick="setAction('/minor project/cart/add-to-cart.php')" class="add-to-cart-btn" data-name="Redmi-Bud5" data-price="4800" data-image="/minor project/assets/images/airport7.jpg">
    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
    <input type="hidden" name="product_id" value="29">
    Add to Cart
</button>
          </div>


          <div class="product">
              <a href="/minor project/product/Nothing-Ear.php"> <img src="/minor project/assets/images/airport8.jpg" alt="airport8"></a>
              <h3>Nothing Ear</h3>
              <p>Rs. 15100</p>
              <button type="submit" onclick="setAction('/minor project/cart/add-to-cart.php')" class="add-to-cart-btn" data-name="Nothing-Ear" data-price="15100" data-image="/minor project/assets/images/airport8.jpg">
    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
    <input type="hidden" name="product_id" value="30">
    Add to Cart
</button>
          </div>

          <div class="product">
             <a href="/minor project/product/Airpot4.php"> <img src="/minor project/assets/images/airport9.jpg" alt="airport9"></a>
              <h3>Airport 4</h3>
              <p>Rs. 24900</p>
              <button type="submit" onclick="setAction('/minor project/cart/add-to-cart.php')" class="add-to-cart-btn" data-name="Airpot4" data-price="24900" data-image="/minor project/assets/images/airport9.jpg">
    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
    <input type="hidden" name="product_id" value="28">
    Add to Cart
</button>
          </div>

          <div class="product">
               <a href="/minor project/product/Acer-Aspire14.php"><img src="/minor project/assets/images/Acer-Aspire-14-Intel-Core-5-120U.jpg" alt="acer aspire"></a>
              <h3>Acer Aspire 14</h3>
              <p>Rs. 80000</p>
              <button type="submit" onclick="setAction('/minor project/cart/add-to-cart.php')" class="add-to-cart-btn" data-name="Acer-Aspire-14-Intel-Core-5-120U" data-price="80000" data-image="/minor project/assets/images/Acer-Aspire-14-Intel-Core-5-120U.jpg">
    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
    <input type="hidden" name="product_id" value="3">
    Add to Cart
</button>
          </div>

          <div class="product">
               <a href="/minor project/product/Lenovo-LQO15.php"><img src="/minor project/assets/images/Lenovo-LOQ-15-2024-Storm-Grey.jpg" alt="Lenovo-LOQ-15-2024-Storm-Grey"></a>
              <h3>Lenovo-LOQ-15-2024-Storm-Grey</h3>
              <p>Rs. 91000</p>
              <button type="submit" onclick="setAction('/minor project/cart/add-to-cart.php')" class="add-to-cart-btn" data-name="Lenovo-Legion-5-2024-RTX-4060" data-price="91000" data-image="/minor project/assets/images/Lenovo-LOQ-15-2024-Storm-Grey.jpg">
    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
    <input type="hidden" name="product_id" value="10">
    Add to Cart
</button>
          </div>

          <div class="product">
               <a href="/minor project/product/Asus-Zenbook.php"><img src="/minor project/assets/images/Asus-Zenbook-Duo-2024-Core-Ultra-9-185H - Copy.jpg" alt="Asus-Zenbook-Duo-2024-Core-Ultra-9-185H"></a>
              <h3>Asus-Zenbook-Duo-2024-Core-Ultra-9-185H</h3>
              <p>Rs. 300000</p>
              <button type="submit" onclick="setAction('/minor project/cart/add-to-cart.php')" class="add-to-cart-btn" data-name="Asus-Zenbook-Duo-2024-Core-Ultra-9-185H" data-price="300000" data-image="/minor project/assets/images/Asus-Zenbook-Duo-2024-Core-Ultra-9-185H - Copy.jpg">
    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
    <input type="hidden" name="product_id" value="53">
    Add to Cart
</button>
          </div>

          <div class="product">
             <a href="/minor project/product/Anker-soundcore.php"> <img src="/minor project/assets/images/Anker-Soundcore-Motion-Boom-Black - Copy.jpg" alt="Anker-Soundcore-Motion-Boom-Black"></a>
              <h3>Anker-Soundcore-Motion-Boom-Black</h3>
              <p>Rs. 31000</p>
              <button type="submit" onclick="setAction('/minor project/cart/add-to-cart.php')" class="add-to-cart-btn" data-name="Anker-soundcore" data-price="31000" data-image="/minor project/assets/images/Anker-Soundcore-Motion-Boom-Black - Copy.jpg">
    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
    <input type="hidden" name="product_id" value="44">
    Add to Cart
</button>
          </div>

          <div class="product">
               <a href="/minor project/product/Lenovo-Legion5.php"><img src="/minor project/assets/images/Lenovo-Legion-5-2024-RTX-4060.jpg" alt="Lenovo-Legion-5-2024-RTX-4060"></a>
              <h3>Lenovo-Legion-5-2024-RTX-4060</h3>
              <p>Rs. 190100</p>
              <button type="submit" onclick="setAction('/minor project/cart/add-to-cart.php')" class="add-to-cart-btn" data-name="Lenovo-Legion-5-2024-RTX-4060" data-price="190100" data-image="/minor project/assets/images/Lenovo-Legion-5-2024-RTX-4060.jpg">
    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
    <input type="hidden" name="product_id" value="54">
    Add to Cart
</button>
          </div>

          <div class="product">
             <a href="/minor project/product/HP-victus.php"> <img src="/minor project/assets/images/laptop.png" alt="laptop"></a>
              <h3>HP victus</h3>
              <p>Rs. 31000</p>
<button type="submit" onclick="setAction('/minor project/cart/add-to-cart.php')" class="add-to-cart-btn" data-name="Hpvictus" data-price="31000" data-image="/minor project/assets/images/laptop.png">
    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
    <input type="hidden" name="product_id" value="55">
    Add to Cart
</button>
          </div>

          <div class="product">
             <a href="/minor project/product/Redmi-14C.php"> <img src="/minor project/assets/images/Redmi-14C-Dreamy-Purple.jpg" alt="Redmi-14C-Dreamy-Purple"></a>
              <h3>Redmi-14C-Dreamy-Purple</h3>
              <p>Rs. 13100</p>
       <button type="submit" onclick="setAction('/minor project/cart/add-to-cart.php')" class="add-to-cart-btn" data-name="Redmi-14C" data-price="13100" data-image="/minor project/assets/images/Redmi-14C-Dreamy-Purple.jpg">
    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
    <input type="hidden" name="product_id" value="34">
    Add to Cart
</button>
          </div>

          <div class="product">
             <a href="/minor project/product/Vivo-V40.php"> <img src="/minor project/assets/images/Vivo-V40-5G-Ganges-Blue.jpg" alt="Vivo-V40-5G-Ganges-Blue"></a>
              <h3>Vivo-V40-5G-Ganges-Blue</h3>
              <p>Rs. 65100</p>
              <button type="submit" onclick="setAction('/minor project/cart/add-to-cart.php')" class="add-to-cart-btn" data-name="Vivo-V40-5G-Ganges-Blue" data-price="65100" data-image="/minor project/assets/images/Vivo-V40-5G-Ganges-Blue.jpg">
    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
    <input type="hidden" name="product_id" value="56">
    Add to Cart
</button>
          </div>

          <div class="product">
             <a href="/minor project/product/Honor-200.php"> <img src="/minor project/assets/images/Honor-200-2.jpg" alt="Honor-200-2"></a>
              <h3>Honor-200-2</h3>
              <p>Rs. 80100</p>
             <button type="submit" onclick="setAction('/minor project/cart/add-to-cart.php')" class="add-to-cart-btn" data-name="Honor-200-2" data-price="80100" data-image="/minor project/assets/images/Honor-200-2.jpg">
    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
    <input type="hidden" name="product_id" value="57">
    Add to Cart
</button>
          </div>

          <div class="product">
             <a href="/minor project/product/Samsung-galaxy-s25.php"> <img src="/minor project/assets/images/Samsung-Galaxy-S24-FE-Blue.jpg" alt="Samsung-Galaxy-S24-FE-Blue"></a>
              <h3>Samsung-Galaxy-S24-FE-Blue</h3>
              <p>Rs. 94100</p>
              <button type="submit" onclick="setAction('/minor project/cart/add-to-cart.php')" class="add-to-cart-btn" data-name="Samsung-Galaxy-S24-FE-Blue" data-price="94100" data-image="/minor project/assets/images/Samsung-Galaxy-S24-FE-Blue.jpg">
    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
    <input type="hidden" name="product_id" value="35">
    Add to Cart
</button>
          </div>

          <div class="product">
              <a href="/minor project/product/iphone16promax-new.php"><img src="/minor project/assets/images/iPhone-16-Pro-Max-Gold-Titanium.jpg" alt="iPhone-16-Pro-Max-Gold-Titanium"></a>
              <h3>iPhone-16-Pro-Max-Gold-Titanium</h3>
              <p>Rs. 225100</p>
             <button type="submit" onclick="setAction('/minor project/cart/add-to-cart.php')" class="add-to-cart-btn" data-name="iPhone-16-Pro-Max-Gold-Titanium" data-price="225100" data-image="/minor project/assets/images/iPhone-16-Pro-Max-Gold-Titanium.jpg">
    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
    <input type="hidden" name="product_id" value="32">
    Add to Cart
</button>
          </div>

          <div class="product">
             <a href="/minor project/product/Apple-ultrapro.php"> <img src="/minor project/assets/images/Apple-Watch-Ultra-2-Ocean-Band-Orange - Copy.jpg" alt="Apple-Watch-Ultra-2-Ocean-Band-Orange"></a>
              <h3>Apple Watch ultra</h3>
              <p>Rs. 140100</p>
             <button type="submit" onclick="setAction('/minor project/cart/add-to-cart.php')" class="add-to-cart-btn" data-name="Apple Watch ultra" data-price="140100" data-image="/minor project/assets/images/Apple-Watch-Ultra-2-Ocean-Band-Orange - Copy.jpg">
    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
    <input type="hidden" name="product_id" value="33">
    Add to Cart
</button>
          </div>

          <div class="product">
             <a href="/minor project/product/Redmi-14C.php"> <img src="/minor project/assets/images/Redmi-14C-Dreamy-Purple.jpg" alt="Redmi-14C-Dreamy-Purple"></a>
              <h3>Redmi-14C-Dreamy-Purple</h3>
              <p>Rs. 130100</p>
              <button type="submit" onclick="setAction('/minor project/cart/add-to-cart.php')" class="add-to-cart-btn" data-name="Redmi-14C-Dreamy-Purple" data-price="130100" data-image="/minor project/assets/images/Redmi-14C-Dreamy-Purple.jpg">
    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
    <input type="hidden" name="product_id" value="34">
    Add to Cart
</button>
          </div>

          <div class="product">
             <a href="/minor project/product/Samsung-galaxy-s25.php"> <img src="/minor project/assets/images/Samsung-Galaxy-S24-FE-Blue.jpg" alt="Samsung"></a>
              <h3>Samsung-Galaxy-S24-FE-Blue</h3>
              <p>Rs. 80100</p>
              <button type="submit" onclick="setAction('/minor project/cart/add-to-cart.php')" class="add-to-cart-btn" data-name="Samsung-Galaxy-S24-FE-Blue" data-price="80100" data-image="/minor project/assets/images/Samsung-Galaxy-S24-FE-Blue.jpg">
    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
    <input type="hidden" name="product_id" value="35">
    Add to Cart
</button>
          </div>

          <div class="product">
              <a href="/minor project/product/Xiamoni-watch.php"> <img src="/minor project/assets/images/Xiaomi-Watch-S1-Active-Black.jpg" alt="Xiaomi-Watch-S1-Active-Black"></a>
              <h3>Xiaomi-Watch-S1-Active-Black</h3>
              <p>Rs. 22000</p>
              <button type="submit" onclick="setAction('/minor project/cart/add-to-cart.php')" class="add-to-cart-btn" data-name="Xiaomi-Watch-S1-Active-Black" data-price="22000" data-image="/minor project/assets/images/Xiaomi-Watch-S1-Active-Black.jpg">
    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
    <input type="hidden" name="product_id" value="36">
    Add to Cart
</button>
          </div>

          <div class="product">
              <a href="/minor project/product/Amazfit-cheetah.php">  <img src="/minor project/assets/images/Amazfit-Cheetah-Run-Track-Black.jpg" alt="Amazfit-Cheetah-Run-Track-Black"></a>
              <h3>Amazfit-Cheetah-Run-Track-Black</h3>
              <p>Rs. 49000</p>
              <button type="submit" onclick="setAction('/minor project/cart/add-to-cart.php')" class="add-to-cart-btn" data-name="Amazfit-Cheetah-Run-Track-Black" data-price="49000" data-image="/minor project/assets/images/Amazfit-Cheetah-Run-Track-Black.jpg">
    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
    <input type="hidden" name="product_id" value="37">
    Add to Cart
</button>
          </div>

          <div class="product">
              <a href="/minor project/product/huawie-watch.php">  <img src="/minor project/assets/images/exclusive.png" alt="Huawei-Watch-Buds-Khaki-New"></a>
              <h3>Huawei-Watch-Buds-Khaki-New</h3>
              <p>Rs. 25000</p>
             <button type="submit" onclick="setAction('/minor project/cart/add-to-cart.php')" class="add-to-cart-btn" data-name="Huawei-Watch-Buds-Khaki-New" data-price="25000" data-image="/minor project/assets/images/exclusive.png">
    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
    <input type="hidden" name="product_id" value="13">
    Add to Cart
</button>
          </div>

          <div class="product">
              <a href="/minor project/product/Samsunggalaxy-watchs7.php">  <img src="/minor project/assets/images/Samsung-Galaxy-Watch-7.jpg" alt="Samsung-Galaxy-Watch-7"></a>
              <h3>Samsung-Galaxy-Watch-7</h3>
              <p>Rs. 44000</p>
             <button type="submit" onclick="setAction('/minor project/cart/add-to-cart.php')" class="add-to-cart-btn" data-name="Samsung-Galaxy-Watch-7" data-price="44000" data-image="/minor project/assets/images/Samsung-Galaxy-Watch-7.jpg">
    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
    <input type="hidden" name="product_id" value="37">
    Add to Cart
</button>
          </div>

          <div class="product">
              <a href="/minor project/product/Appleultrawatch.php">  <img src="/minor project/assets/images/Apple-Watch-Ultra-2-Ocean-Band-Orange - Copy.jpg" alt="Apple ultra pro"></a>
              <h3>Apple ultra pro</h3>
              <p>Rs. 144000</p>
             <button type="submit" onclick="setAction('/minor project/cart/add-to-cart.php')" class="add-to-cart-btn" data-name="Apple-Watch-Ultra-2-Ocean-Band-Orange - Copy" data-price="144000" data-image="/minor project/assets/images/Apple-Watch-Ultra-2-Ocean-Band-Orange - Copy.jpg">
    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
    <input type="hidden" name="product_id" value="39">
    Add to Cart
</button>
          </div>

          <div class="product">
              <a href="/minor project/product/">  <img src="/minor project/assets/images/Mivi-Roam-2-Black.jpg" alt="Mivi-Roam-2-Black"></a>
              <h3>Mivi-Roam-2-Black</h3>
              <p>Rs. 2599</p>
              <button type="submit" onclick="setAction('/minor project/cart/add-to-cart.php')" class="add-to-cart-btn" data-name="Mivi-Roam-2-Black" data-price="2599" data-image="/minor project/assets/images/Mivi-Roam-2-Black.jpg">
    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
    <input type="hidden" name="product_id" value="40">
    Add to Cart
</button>
</div>
          <div class="product">
              <a href="/minor project/product/Havit-SK835BT-Black.php"> <img src="/minor project/assets/images/Havit-SK835BT-Black.jpg" alt="Havit-SK835BT-Black"></a>
              <h3>Havit-SK835BT-Black</h3>
              <p>Rs. 3000</p>
             <button type="submit" onclick="setAction('/minor project/cart/add-to-cart.php')" class="add-to-cart-btn" data-name="Vivo-V40-5G-Ganges-Blue" data-price="3000" data-image="/minor project/assets/images/Vivo-V40-5G-Ganges-Blue.jpg">
    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
    <input type="hidden" name="product_id" value="41">
    Add to Cart
</button>
          </div>

          <div class="product">
              <a href="/minor project/product/J">  <img src="/minor project/assets/images/JBL-GO-4-Black.jpg" alt="JBL-GO-4-Black"></a>
              <h3>JBL-GO-4-Black</h3>
              <p>Rs. 4000</p>
              <button type="submit" onclick="setAction('/minor project/cart/add-to-cart.php')" class="add-to-cart-btn" data-name="JBL-GO-4-Black" data-price="4000" data-image="/minor project/assets/images/JBL-GO-4-Black.jpg">
    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
    <input type="hidden" name="product_id" value="42">
    Add to Cart
</button>
          </div>

          <div class="product">
              <a href="/minor project/product/Hifuture-galaxy.php"> <img src="/minor project/assets/images/HiFuture-Gravity-Black.jpg" alt="HiFuture-Gravity-Black"></a>
              <h3>HiFuture-Gravity-Black</h3>
              <p>Rs. 7000</p>
              <button type="submit" onclick="setAction('/minor project/cart/add-to-cart.php')" class="add-to-cart-btn" data-name="HiFuture-Gravity-Black" data-price="7000" data-image="/minor project/assets/images/HiFuture-Gravity-Black.jpg">
    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
    <input type="hidden" name="product_id" value="43">
    Add to Cart
</button>
          </div>

          <div class="product">
              <a href="/minor project/product/Anker-soundcore.php"> <img src="/minor project/assets/images/Anker-Soundcore-Motion-Boom-Black - Copy.jpg" alt="Anker-Soundcore-Motion-Boom-Black"></a>
              <h3>Anker-Soundcore-Motion-Boom-Black</h3>
              <p>Rs. 18000</p>
              <button type="submit" onclick="setAction('/minor project/cart/add-to-cart.php')" class="add-to-cart-btn" data-name="Anker-Soundcore-Motion-Boom-Black - Copy" data-price="18000" data-image="/minor project/assets/images/Anker-Soundcore-Motion-Boom-Black - Copy.jpg">
    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
    <input type="hidden" name="product_id" value="44">
    Add to Cart
</button>
          </div>

          <div class="product">
              <a href="/minor project/product/Budgetgamingunder50K.php">  <img src="/minor project/assets/images/Budget-Gaming-PC-50k.jpg" alt="Pc build"></a>
              <h3>Budget-Gaming-PC-50k</h3>
              <p>Rs. 49999</p>
              <button type="submit" onclick="setAction('/minor project/cart/add-to-cart.php')" class="add-to-cart-btn" data-name="Budget-Gaming-PC-50k" data-price="49999" data-image="/minor project/assets/images/Budget-Gaming-PC-50k.jpg">
    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
    <input type="hidden" name="product_id" value="45">
    Add to Cart
</button>
          </div>

          <div class="product">
              <a href="/minor project/product/Bestbudgetunder1lakhAmd.php"> <img src="/minor project/assets/images/Best-Budget-Gaming-PC-under-1-lakh-AMD.jpg" alt="pc build"></a>
              <h3>Best-Budget-Gaming-PC-under-1-lakh-AMD</h3>
              <p>Rs. 99999</p>
              <button type="submit" onclick="setAction('/minor project/cart/add-to-cart.php')" class="add-to-cart-btn" data-name="Best-Budget-Gaming-PC-under-1-lakh-AMD" data-price="99999" data-image="/minor project/assets/images/Best-Budget-Gaming-PC-under-1-lakh-AMD.jpg">
    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
    <input type="hidden" name="product_id" value="46">
    Add to Cart
</button>
          </div>

          <div class="product">
              <a href="/minor project/product/BestgamingPc-under1.5.php"> <img src="/minor project/assets/images/Best-Gaming-PC-Under-1.5-lakh-amd-with-monitor.jpg" alt="pc build"></a>
              <h3>Best-Budget-Gaming-PC-under-1-lakh-AMD with monitor</h3>
              <p>Rs. 149999</p>
<button type="submit" onclick="setAction('/minor project/cart/add-to-cart.php')" class="add-to-cart-btn" data-name="Best-Gaming-PC-Under-1.5-lakh-amd-with-monitor" data-price="149999" data-image="/minor project/assets/images/Best-Gaming-PC-Under-1.5-lakh-amd-with-monitor.jpg">
    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
    <input type="hidden" name="product_id" value="57">
    Add to Cart
</button>
          </div>

          <div class="product">
              <a href="/minor project/product/Bestbudgetunder1lakhAmd.php"> <img src="/minor project/assets/images/Best-Gaming-PC-Under-1.5-lakh-intel.jpg" alt="pc build"></a>
              <h3>Best-Budget-Gaming-PC-under-1-lakh-AMD</h3>
              <p>Rs. 149999</p>
              <button type="submit" onclick="setAction('/minor project/cart/add-to-cart.php')" class="add-to-cart-btn" data-name="Best-Gaming-PC-Under-1.5-lakh-intel" data-price="149999" data-image="/minor project/assets/images/Best-Gaming-PC-Under-1.5-lakh-intel.jpg">
    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
    <input type="hidden" name="product_id" value="47">
    Add to Cart
</button>
          </div>

          <div class="product">
              <a href="/minor project/product/BestgamingPc-under1.5.php"> <img src="/minor project/assets/images/Best-Gaming-PC-Under-1.5-lakh-intel.jpg" alt="pc build"></a>
              <h3>Best-Gaming-PC-Under-1.5</h3>
              <p>Rs. 149999</p>
             <button type="submit" onclick="setAction('/minor project/cart/add-to-cart.php')" class="add-to-cart-btn" data-name="Best-Gaming-PC-Under-1.5-lakh-intel" data-price="149999" data-image="/minor project/assets/images/Best-Gaming-PC-Under-1.5-lakh-intel.jpg">
    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
    <input type="hidden" name="product_id" value="48">
    Add to Cart
</button>
          </div>

          <div class="product">
              <a href="/minor project/product/charger.php"> <img src="/minor project/assets/images/charger.png" alt="charger"></a>
              <h3>charger</h3>
              <p>Rs. 740</p>
              <button type="submit" onclick="setAction('/minor project/cart/add-to-cart.php')" class="add-to-cart-btn" data-name="charger" data-price="740" data-image="/minor project/assets/images/charger.png">
    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
    <input type="hidden" name="product_id" value="49">
    Add to Cart
</button>

          </div>

          <div class="product">
              <a href="/minor project/product/keyboard.php"> <img src="/minor project/assets/images/keyboard-redragon-k636clo-mechanical-price-in-nepal.webp" alt="keyboard-redragon-k636clo-mechanical-price-in-nepal"></a>
              <h3>keyboard-redragon-k636clo-mechanical-price-in-nepal</h3>
              <p>Rs. 6000</p>
             <button type="submit" onclick="setAction('/minor project/cart/add-to-cart.php')" class="add-to-cart-btn" data-name="keyboard-redragon-k636clo-mechanical-price-in-nepal" data-price="6000" data-image="/minor project/assets/images/keyboard-redragon-k636clo-mechanical-price-in-nepal.webp">
    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
    <input type="hidden" name="product_id" value="50">
    Add to Cart
</button>
          </div>

          <div class="product">
              <a href="/minor project/product/gamingmouse.php"> <img src="/minor project/assets/images/gaming mouse.jpg" alt="gaming mouse"></a>
              <h3>Gaming mouse</h3>
              <p>Rs. 2000</p>
              <button type="submit" onclick="setAction('/minor project/cart/add-to-cart.php')" class="add-to-cart-btn" data-name="gaming mouse" data-price="2000" data-image="/minor project/assets/images/gaming mouse.jpg">
    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
    <input type="hidden" name="product_id" value="51">
    Add to Cart
</button>
          </div>

          <div class="product">
              <a href="/minor project/product/printer.php"> <img src="/minor project/assets/images/printer.jpg" alt="printer"></a>
              <h3>Printer</h3>
              <p>Rs. 30000</p>
              <button type="submit" onclick="setAction('/minor project/cart/add-to-cart.php')" class="add-to-cart-btn" data-name="printer" data-price="30000" data-image="/minor project/assets/images/printer.jpg">
    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
    <input type="hidden" name="product_id" value="52">
    Add to Cart
</button>
          </div>

          <div class="product">
              <a href="/minor project/product/bluetooth-speaker.php"> <img src="/minor project/assets/images/bluetooth speaker.jpg" alt="speaker"></a>
              <h3>Bluetooth Speaker</h3>
              <p>Rs. 5000</p>
              <button type="submit" onclick="setAction('/minor project/cart/add-to-cart.php')" class="add-to-cart-btn" data-name="bluetooth speaker" data-price="5000" data-image="/minor project/assets/images/bluetooth speaker.jpg">
    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
    <input type="hidden" name="product_id" value="31">
    Add to Cart
</button>
          </div>
</form>
        
    </section>

    <div class="footer">
        <div class="container">
          <div class="row">
            <div class="footer-col-1">
              <h3>Download Our App</h3>
              <p>Download App for Android and ios mobile phones.</p>
              <div class="app-logo">
                <img src="/minor project/assets/images/play-store.png" alt="Google Play Store Logo" />
                <img src="/minor project/assets/images/app-store.png" alt="ios store Logo" />
              </div>
            </div>
            <div class="footer-col-2">
              <img src="/minor project/assets/images/logo.png" alt="snap cart logo " />
              <p>
                Our purpose is to sustainably make the pleasure and benefits of
                sports accessible to the many.
              </p>
            </div>
            <div class="footer-col-3">
              <h3>Useful Links</h3>
              <ul>
                <li>Coupons</li>
                <li>Blog Post</li>
                <li>Return Policy</li>
                <li>Joins Affiliates</li>
              </ul>
            </div>
            <div class="footer-col-4">
              <h3>Follow Us</h3>
              <ul>
                <li>Facebook</li>
                <li>Twitter</li>
                <li>Instagram</li>
                <li>YouTube</li>
              </ul>
            </div>
          </div>
          <hr />
          <p class="copyright">Copyright 2025</p>
        </div>
      </div>
      
      <!-- Toast Notification Container -->
      <div id="toast-container"></div>
      <script src="/minor project/assets/js/script.js"></script>
      <script src="/minor project/assets/js/product.js"></script>
      
<script>
  function setAction(actionUrl) {
    document.getElementById('addtocartForm').action = actionUrl;
  }
  
  // Add image error handling
  document.addEventListener('DOMContentLoaded', function() {
    // Add error handler to all product images
    const productImages = document.querySelectorAll('.product img');
    productImages.forEach(img => {
      img.onerror = function() {
        console.log('Image failed to load: ' + this.alt);
        // Try to fix common image path issues
        if (this.src.includes(' ')) {
          // Replace spaces in URLs with %20
          this.src = this.src.replace(/ /g, '%20');
        } else {
          // If still fails, use a placeholder
          this.src = '/minor project/assets/images/placeholder.png';
        }
      };
    });
    
    // Fix the cart form submission to prevent double products
    const addToCartButtons = document.querySelectorAll('.add-to-cart-btn');
    addToCartButtons.forEach(button => {
      button.addEventListener('click', function(e) {
        // Set the form action correctly
        if (this.getAttribute('onclick') && this.getAttribute('onclick').includes('setAction')) {
          const actionUrl = this.getAttribute('onclick').match(/setAction\(['"]([^'"]*)['"]\)/);
          if (actionUrl && actionUrl[1]) {
            document.getElementById('addtocartForm').action = actionUrl[1];
          } else {
            document.getElementById('addtocartForm').action = '/minor project/cart/add-to-cart.php';
          }
        }
      });
    });
  });
  
  function setAction(actionurl) {
    document.getElementById('addtocartForm').action = actionurl;
    return true;
  }
</script>

<style>
  /* Ensure images display properly */
  .product img {
    width: 100%;
    height: 200px;
    object-fit: contain;
    margin-bottom: 10px;
  }
  
  /* Toast notification styling */
  #toast-container {
    position: fixed;
    bottom: 20px;
    right: 20px;
    z-index: 1000;
  }
  
  .toast {
    background-color: #333;
    color: white;
    padding: 15px 20px;
    border-radius: 4px;
    margin-bottom: 10px;
    box-shadow: 0 4px 8px rgba(0,0,0,0.2);
    animation: slideIn 0.5s forwards;
  }
  
  @keyframes slideIn {
    from {
      transform: translateX(100%);
      opacity: 0;
    }
    to {
      transform: translateX(0);
      opacity: 1;
    }
  }
</style>
</body>
</html>