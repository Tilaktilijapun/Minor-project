<?php 
include 'includes/dbconn.php';
session_start();

// Get search query
$search_query = isset($_GET['query']) ? trim($_GET['query']) : '';

// Initialize products array
$products = [];

// If search query exists, search for products
if (!empty($search_query)) {
    // Prepare search query with wildcards for partial matches
    $search_param = "%{$search_query}%";
    
    // Prepare and execute the query
    $stmt = $conn->prepare("SELECT * FROM products WHERE name LIKE ? OR description LIKE ? OR category LIKE ? ORDER BY name");
    $stmt->bind_param("sss", $search_param, $search_param, $search_param);
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Fetch all products
    $products = $result->fetch_all(MYSQLI_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>SnapCart Ecommerce Website</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="stylesheet" href="/minor project/assets/css/index.css" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"rel="stylesheet" />
    <link href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet" />
  </head>
  <body>
    <div class="header">
      <div class="container">
        <div class="navbar">
          <div class="logo">
            <img src="../minor project/assets/images/logo.png.png" alt="SnapCart" width="125px" />
          </div>
          <form action="/minor project/product/search.php" method="GET">
                   <div class="search-container">
                <input type="text" name="query" class="search-bar" placeholder="Search for products..." value="<?php echo htmlspecialchars($search_query); ?>">
                <button type="submit" class="search-button">Search</button>
        </div>
        </form>
          <nav>
            <ul id="MenuItems">
              <li><a href="../minor project/index.php">Home</a></li>
              <li><a href="../minor project/product/product.php">Products</a></li>
              <li><a href="../minor project/aboutus.php">About</a></li>
              <li><a href="../minor project/contact.php">Contact</a></li>
              <li><a href="../minor project/auth/account.php">Account</a></li>
            </ul>
          </nav>
          
          <img
            onclick="window.location.href='../minor project/cart/view-cart.php'"
            src="../minor project/assets/images/cart.png"
            alt="Shopping cart icon"
            width="30px"
            height="30px"
            />
          <img
            src="../minor project/assets/images/menu.png"
            alt="menu icon"
            class="menu-icon"
            onclick="menutoggle()"
          />
        </div>
        <div class="row">
          <div class="col-2">
            <h1>Upgrade Your Lifestyle <br />with Snapcart!!</h1>
            <p>
              "Snapcart is your ultimate destination for trendsetting gadgets, fashion, and essentials—all in one place. Upgrade your lifestyle with seamless shopping, unbeatable deals, and effortless convenience!"
              
            </p>
            <a href="" class="btn">Explore Now &#8594; </a>
          </div>
          <div class="col-2">
            <div class="banner-slideshow">
              <div class="banner-slide active">
                <img src="../minor project/assets/images/main-product.png" alt="main products" />
              </div>
              <div class="banner-slide">
                <img src="../minor project/assets/images/main-product 2.png" alt="main products 2" />
              </div>
              <div class="banner-slide">
                <img src="../minor project/assets/images/main-product 3.png" alt="main products 3" />
              </div>
              <div class="banner-dots">
                <span class="dot active" onclick="currentSlide(1)"></span>
                <span class="dot" onclick="currentSlide(2)"></span>
                <span class="dot" onclick="currentSlide(3)"></span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Shop By Categories -->
    <div class="shop-categories">
      <div class="container">
        <div class="section-header">
          <h2 class="section-title">SHOP BY CATEGORIES</h2>
          <a href="/minor project/product/categories.php" class="view-all">VIEW ALL CATEGORIES</a>
        </div>
        
        <div class="categories-grid" style="display: grid; grid-template-columns: repeat(6, 1fr); grid-template-rows: repeat(3, auto); gap: 20px; margin-top: 25px;">
          <!-- Smartphones Category - spans 3 columns -->
          <div class="category-card" style="transition: transform 0.3s ease; grid-column: span 3;">
            <a href="/minor project/product/category.php?category=Smartphones" style="text-decoration: none; display: block;">
              <div class="category-image" style="background-color: #ffdbf0; border-radius: 12px; position: relative; overflow: hidden; height: 220px; box-shadow: 0 4px 8px rgba(0,0,0,0.05); transition: transform 0.3s ease, box-shadow 0.3s ease; cursor: pointer;">
                <div class="confetti" style="position: absolute; width: 100%; height: 100%; background: url('/minor project/assets/images/confetti.png'); opacity: 0.3; z-index: 1;"></div>
                <img src="/minor project/assets/images/samsung-galaxy s24 ultra.jpg" alt="Smartphones" 
                     style="width: 45%; height: 150px; object-fit: contain; display: block; margin: 25px auto 0; z-index: 2; position: relative; transition: transform 0.3s ease;" />
                <div class="category-name" style="padding: 10px 15px; text-align: left; position: absolute; bottom: 0; left: 0; width: 100%; z-index: 2; background: linear-gradient(to top, rgba(255,255,255,0.9), transparent);">
                  <h3 style="margin: 0; font-size: 16px; font-weight: 600; color: #333; text-transform: uppercase;">SMARTPHONES</h3>
                  <span class="view-more" style="font-size: 11px; color: #ff523b; display: block; margin-top: 3px;">VIEW MORE →</span>
                </div>
              </div>
            </a>
          </div>
          
          <!-- Earbuds Category - spans 3 columns -->
          <div class="category-card" style="transition: transform 0.3s ease; grid-column: span 3;">
            <a href="/minor project/product/category.php?category=Audio" style="text-decoration: none; display: block;">
              <div class="category-image" style="background-color: #d9f9e6; border-radius: 12px; position: relative; overflow: hidden; height: 220px; box-shadow: 0 4px 8px rgba(0,0,0,0.05); transition: transform 0.3s ease, box-shadow 0.3s ease; cursor: pointer;">
                <div class="confetti" style="position: absolute; width: 100%; height: 100%; background: url('/minor project/assets/images/confetti.png'); opacity: 0.3; z-index: 1;"></div>
                <img src="/minor project/assets/images/airpot.png" alt="Earbuds" 
                     style="width: 45%; height: 150px; object-fit: contain; display: block; margin: 25px auto 0; z-index: 2; position: relative; transition: transform 0.3s ease;" />
                <div class="category-name" style="padding: 10px 15px; text-align: left; position: absolute; bottom: 0; left: 0; width: 100%; z-index: 2; background: linear-gradient(to top, rgba(255,255,255,0.9), transparent);">
                  <h3 style="margin: 0; font-size: 16px; font-weight: 600; color: #333; text-transform: uppercase;">EARBUDS</h3>
                  <span class="view-more" style="font-size: 11px; color: #ff523b; display: block; margin-top: 3px;">VIEW MORE →</span>
                </div>
              </div>
            </a>
          </div>
          
          <!-- Tablets Category - spans 2 columns -->
          <div class="category-card" style="transition: transform 0.3s ease; grid-column: span 2;">
            <a href="/minor project/product/category.php?category=Tablets" style="text-decoration: none; display: block;">
              <div class="category-image" style="background-color: #fff8d9; border-radius: 12px; position: relative; overflow: hidden; height: 220px; box-shadow: 0 4px 8px rgba(0,0,0,0.05); transition: transform 0.3s ease, box-shadow 0.3s ease; cursor: pointer;">
                <div class="confetti" style="position: absolute; width: 100%; height: 100%; background: url('/minor project/assets/images/confetti.png'); opacity: 0.3; z-index: 1;"></div>
                <img src="/minor project/assets/images/ipad1.jpg" alt="Tablets" 
                     style="width: 45%; height: 150px; object-fit: contain; display: block; margin: 25px auto 0; z-index: 2; position: relative; transition: transform 0.3s ease;" />
                <div class="category-name" style="padding: 10px 15px; text-align: left; position: absolute; bottom: 0; left: 0; width: 100%; z-index: 2; background: linear-gradient(to top, rgba(255,255,255,0.9), transparent);">
                  <h3 style="margin: 0; font-size: 16px; font-weight: 600; color: #333; text-transform: uppercase;">TABLETS</h3>
                  <span class="view-more" style="font-size: 11px; color: #ff523b; display: block; margin-top: 3px;">VIEW MORE →</span>
                </div>
              </div>
            </a>
          </div>
          
          <!-- Smartwatches Category - spans 2 columns -->
          <div class="category-card" style="transition: transform 0.3s ease; grid-column: span 2;">
            <a href="/minor project/product/category.php?category=Watches" style="text-decoration: none; display: block;">
              <div class="category-image" style="background-color: #ffdbf0; border-radius: 12px; position: relative; overflow: hidden; height: 220px; box-shadow: 0 4px 8px rgba(0,0,0,0.05); transition: transform 0.3s ease, box-shadow 0.3s ease; cursor: pointer;">
                <div class="confetti" style="position: absolute; width: 100%; height: 100%; background: url('/minor project/assets/images/confetti.png'); opacity: 0.3; z-index: 1;"></div>
                <img src="/minor project/assets/images/Digital-watch-with-fitness.jpg" alt="Smartwatches" 
                     style="width: 45%; height: 150px; object-fit: contain; display: block; margin: 25px auto 0; z-index: 2; position: relative; transition: transform 0.3s ease;" />
                <div class="category-name" style="padding: 10px 15px; text-align: left; position: absolute; bottom: 0; left: 0; width: 100%; z-index: 2; background: linear-gradient(to top, rgba(255,255,255,0.9), transparent);">
                  <h3 style="margin: 0; font-size: 16px; font-weight: 600; color: #333; text-transform: uppercase;">SMARTWATCHES</h3>
                  <span class="view-more" style="font-size: 11px; color: #ff523b; display: block; margin-top: 3px;">VIEW MORE →</span>
                </div>
              </div>
            </a>
          </div>
          
          <!-- Laptops Category - spans 2 columns -->
          <div class="category-card" style="transition: transform 0.3s ease; grid-column: span 2;">
            <a href="/minor project/product/category.php?category=Laptops" style="text-decoration: none; display: block;">
              <div class="category-image" style="background-color: #e0e6ff; border-radius: 12px; position: relative; overflow: hidden; height: 220px; box-shadow: 0 4px 8px rgba(0,0,0,0.05); transition: transform 0.3s ease, box-shadow 0.3s ease; cursor: pointer;">
                <div class="confetti" style="position: absolute; width: 100%; height: 100%; background: url('/minor project/assets/images/confetti.png'); opacity: 0.3; z-index: 1;"></div>
                <img src="/minor project/assets/images/HP-victus.jpg" alt="Laptops" 
                     style="width: 45%; height: 150px; object-fit: contain; display: block; margin: 25px auto 0; z-index: 2; position: relative; transition: transform 0.3s ease;" />
                <div class="category-name" style="padding: 10px 15px; text-align: left; position: absolute; bottom: 0; left: 0; width: 100%; z-index: 2; background: linear-gradient(to top, rgba(255,255,255,0.9), transparent);">
                  <h3 style="margin: 0; font-size: 16px; font-weight: 600; color: #333; text-transform: uppercase;">LAPTOPS</h3>
                  <span class="view-more" style="font-size: 11px; color: #ff523b; display: block; margin-top: 3px;">VIEW MORE →</span>
                </div>
              </div>
            </a>
          </div>
          
          <!-- Speakers Category - spans all 6 columns (widest) -->
          <div class="category-card" style="transition: transform 0.3s ease; grid-column: span 6;">
            <a href="/minor project/product/category.php?category=Speakers" style="text-decoration: none; display: block;">
              <div class="category-image" style="background-color: #d9f9e6; border-radius: 12px; position: relative; overflow: hidden; height: 220px; box-shadow: 0 4px 8px rgba(0,0,0,0.05); transition: transform 0.3s ease, box-shadow 0.3s ease; cursor: pointer;">
                <div class="confetti" style="position: absolute; width: 100%; height: 100%; background: url('/minor project/assets/images/confetti.png'); opacity: 0.3; z-index: 1;"></div>
                <img src="/minor project/assets/images/bluetooth speaker.jpg" alt="Bluetooth Speaker" 
                     style="width: 30%; height: 150px; object-fit: contain; display: block; margin: 25px auto 0; z-index: 2; position: relative; transition: transform 0.3s ease;" />
                <div class="category-name" style="padding: 10px 15px; text-align: center; position: absolute; bottom: 0; left: 0; width: 100%; z-index: 2; background: linear-gradient(to top, rgba(255,255,255,0.9), transparent);">
                  <h3 style="margin: 0; font-size: 20px; font-weight: 600; color: #333; text-transform: uppercase;">SPEAKERS</h3>
                  <span class="view-more" style="font-size: 13px; color: #ff523b; display: block; margin-top: 3px;">VIEW MORE →</span>
                </div>
              </div>
            </a>
          </div>
        </div>
          
        </div>
      </div>
    </div>
    
    <!-- Featured Categories -->

    <div class="categories">
      <div class="small-container">
        <div class="row">
          <div class="col-3">
            <img
              src="../minor project/assets/images/photo1.jpg"
              alt="background 1"
            />
          </div>
          <div class="col-3">
            <img
              src="../minor project/assets/images/photo2.jpeg"
              alt="background 2 "
            />
          </div>
          <div class="col-3">
            <img
              src="../minor project/assets/images/photo3.jpg"
              alt="background 3 "
            />
          </div>
        </div>
      </div>
    </div>

    <!-- Featured Products -->

    <div class="small-container">
      <h2 class="title">Featured Products</h2>
      <div class="row">
        <div class="col-4">
         <a href="../minor project/product/MacBook 3 Pro.php"> <img src="../minor project/assets/images/macbook.webp" alt="Macbook 3 " /></a>
          <h4>macbook 3 pro</h4>
          <div class="rating">
            <i class="fa fa-star"></i>
            <i class="fa fa-star"></i>
            <i class="fa fa-star"></i>
            <i class="fa fa-star-half-o"></i>
            <i class="fa fa-star-o"></i>
          </div>
          <p>NRS.180,000</p>
        </div>
        <div class="col-4">
          <a href="../minor project/product/iPad.php">  <img src="../minor project/assets/images/ipad1.jpg" alt="I-pad" /></a>
          <h4>Ipad</h4>
          <div class="rating">
            <i class="fa fa-star"></i>
            <i class="fa fa-star"></i>
            <i class="fa fa-star"></i>
            <i class="fa fa-star"></i>
            <i class="fa fa-star-o"></i>
          </div>
          <p>NRS.85,000</p>
        </div>
        <div class="col-4">
          <a href="../minor project/product/Acer Nitro V15.php"> <img src="../minor project/assets/images/acer nitro v15.jpg" alt="acer v15" /></a>
          <h4>Acer Nitro v15</h4>
          <div class="rating">
            <i class="fa fa-star"></i>
            <i class="fa fa-star"></i>
            <i class="fa fa-star"></i>
            <i class="fa fa-star"></i>
            <i class="fa fa-star"></i>
          </div>
          <p>NRS.150,000</p>
        </div>
        <div class="col-4">
          <a href="../minor project/product/iPad New Model.php"><img src="../minor project/assets/images/ipad.jpg" alt="ipad new model" /></a>
          <h4>Ipad new model with lastest Feature</h4>
          <div class="rating">
            <i class="fa fa-star"></i>
            <i class="fa fa-star"></i>
            <i class="fa fa-star"></i>
          </div>
          <p>NRS.120,000</p>
        </div>
      </div>

      <h2 class="title">Latest Products</h2>
      <div class="row">
        <div class="col-4">
          <a href="../minor project/product/Samsung Galaxy S24.php"><img src="../minor project/assets/images/samsung-galaxy s24 ultra.jpg" alt="samsung-galaxy" /></a>
          <h4>samsung-galaxy S24</h4>
          <div class="rating">
            <i class="fa fa-star"></i>
            <i class="fa fa-star"></i>
            <i class="fa fa-star"></i>
            <i class="fa fa-star-half-o"></i>
            <i class="fa fa-star-o"></i>
          </div>
          <p>NRS.230,000</p>
        </div>
        <div class="col-4">
          <a href="../minor project/product/iPhone 16 Pro Max.php"><img src="../minor project/assets/images/iphone16 pro max.jpg" alt="iphone16 pro max" /></a>
          <h4>Iphone 16 pro max</h4>
          <div class="rating">
            <i class="fa fa-star"></i>
            <i class="fa fa-star"></i>
            <i class="fa fa-star"></i>
            <i class="fa fa-star"></i>
            <i class="fa fa-star-o"></i>
          </div>
          <p>NRS.200,000</p>
        </div>
        <div class="col-4">
          <a href="../minor project/product/MacBook Pro.php"> <img src="../minor project/assets/images/macbook1.jpg" alt="macbook" /></a>
          <h4>Macbook pro </h4>
          <div class="rating">
            <i class="fa fa-star"></i>
            <i class="fa fa-star"></i>
            <i class="fa fa-star"></i>
            <i class="fa fa-star"></i>
            <i class="fa fa-star"></i>
          </div>
          <p>NRS.150,00</p>
        </div>
        <div class="col-4">
          <a href="../minor project/product/Acer V16.php"> <img src="../minor project/assets/images/acer-V16.jpg" alt="acer-V16" /></a>
          <h4>Acer V16</h4>
          <div class="rating">
            <i class="fa fa-star"></i>
            <i class="fa fa-star"></i>
            <i class="fa fa-star"></i>
            <i class="fa fa-star"></i>
            <i class="fa fa-star"></i>
          </div>
          <p>NRS.150,000</p>
        </div>
        <div class="row">
          <div class="col-4">
            <a href="../minor project/product/HP Victus.php"><img src="../minor project/assets/images/HP-victus.jpg" alt="HP-victus" /></a>
            <h4>HP_victus</h4>
            <div class="rating">
              <i class="fa fa-star"></i>
              <i class="fa fa-star"></i>
              <i class="fa fa-star"></i>
              <i class="fa fa-star-half-o"></i>
              <i class="fa fa-star-o"></i>
            </div>
            <p>NRS.125,000</p>
          </div>
          <div class="col-4">
            <a href="../minor project/product/Lenovo Legion 5 2024 RTX 4050.php"> <img src="../minor project/assets/images/Lenovo-Legion-5-2024-.jpg" alt="Black Watch" /></a>
            <h4>Lenovo-Legion-5-2024-RTX 4050</h4>
            <div class="rating">
              <i class="fa fa-star"></i>
              <i class="fa fa-star"></i>
              <i class="fa fa-star"></i>
              <i class="fa fa-star"></i>
              <i class="fa fa-star-o"></i>
            </div>
            <p>NRS.200,000</p>
          </div>
          <div class="col-4">
            <a href="../minor project/product/Digital Watch with Fitness.php"> <img src="../minor project/assets/images/Digital-watch-with-fitness.jpg" alt="Digital-watch-with-fitness" /></a>
            <h4>Digital-watch-with-fitness</h4>
            <div class="rating">
              <i class="fa fa-star"></i>
              <i class="fa fa-star"></i>
              <i class="fa fa-star"></i>
              <i class="fa fa-star"></i>
              <i class="fa fa-star"></i>
            </div>
            <p>NRS.10,000</p>
          </div>
          <div class="col-4">
            <a href="../minor project/product/Samsung Galaxy Watch.php"> <img src="../minor project/assets/images/Montre-Samsung-galaxy_watch.jpg" alt="Montre-Samsung-galaxy_watch" /></a>
            <h4>Montre-Samsung-galaxy_watch</h4>
            <div class="rating">
              <i class="fa fa-star"></i>
              <i class="fa fa-star"></i>
              <i class="fa fa-star"></i>
              <i class="fa fa-star"></i>
              <i class="fa fa-star"></i>
            </div>
            <p>NRS.20,000</p>
          </div>
        </div>
      </div>
    </div>

    <!--Offer -->

    <div class="offer">
      <div class="small-container">
        <div class="row">
          <div class="col-2">
            <img
              src="../minor project/assets/images/exclusive.png"
              class="offer-img"
              alt="orange watch"
            />
          </div>
          <div class="col-2">
            <p>Exclusively Available on SnapCart</p>
            <h1>Huawei Digital Watch – Smart, Stylish & Powerful! </h1>
            <small>The **Huawei Digital Watch** blends sleek design with cutting-edge technology, offering a stunning display, advanced health tracking, and long battery life. Stay connected, track your fitness, and elevate your style—all from your wrist! ⌚🔥
            </small>
            
            <a href="../minor project/product/Huawei Digital Watch.php" class="btn">Buy Now &#8594;</a>
          </div>
        </div>
      </div>
    </div>

    <!-- Testimonials -->

    <div class="testimonial">
      <div class="small-container">
        <div class="row">
          <div class="col-3">
            <i class="fa fa-quote-left"></i>
            <p>
              Snapcart makes shopping effortless with amazing deals and a wide range of products! Fast delivery and a smooth shopping experience every time. 🚀🛒


            </p>
            <div class="rating">
              <i class="fa fa-star"></i>
              <i class="fa fa-star"></i>
              <i class="fa fa-star"></i>
              <i class="fa fa-star"></i>
              <i class="fa fa-star"></i>
            </div>
            <img src="../minor project/assets/images/user1.jpg" alt="human face" />
            <h3>Sankalpa Paudel</h3>
          </div>
          <div class="col-3">
            <i class="fa fa-quote-left"></i>
            <p>
              Love the convenience of Snapcart—everything I need in one place with unbeatable prices! The user-friendly interface makes shopping a breeze. 😍✨
            </p>
            <div class="rating">
              <i class="fa fa-star"></i>
              <i class="fa fa-star"></i>
              <i class="fa fa-star"></i>
              <i class="fa fa-star"></i>
              <i class="fa fa-star"></i>
            </div>
            <img src="../minor project/assets/images/user2.jpg" alt="human face" />
            <h3>Tilak Tilija Pun</h3>
          </div>
          <div class="col-3">
            <i class="fa fa-quote-left"></i>
            <p>
              Snapcart is my go-to for trendy gadgets and essentials at great prices! Highly recommended for anyone who loves smart and hassle-free shopping. 🔥🛍️
            </p>
            <div class="rating">
              <i class="fa fa-star"></i>
              <i class="fa fa-star"></i>
              <i class="fa fa-star"></i>
              <i class="fa fa-star"></i>
              <i class="fa fa-star"></i>
            </div>
            <img src="../minor project/assets/images/user3.jpg" alt="human face" />
            <h3>Anush Shrestha</h3>
          </div>
        </div>
      </div>
    </div>

    <!-- Brands -->

    <div class="brands">
      <div class="small-container">
        <div class="row">
          <div class="col-5">
            <img src="../minor project/assets/images/esewa.png" alt="e-sewa " />
          </div>
          <div class="col-5">
            <img src="../minor project/assets/images/khalti.png" alt="khalti" />
          </div>
          <div class="col-5">
            <img src="../minor project/assets/images/fonepay.png" alt="frone-pay" />
          </div>
         
        </div>
      </div>
    </div>

    <!-- Footer -->

    <div class="footer">
      <div class="container">
        <div class="row">
          <div class="footer-col-1">
            <h3>Download Our App</h3>
            <p>Download App for Android and ios mobile phones.</p>
            <div class="app-logo">
              <img src="../minor project/assets/images/play-store.png" alt="Google Play Store Logo" />
              <img src="../minor project/assets/images/app-store.png" alt="ios store Logo" />
            </div>
          </div>
          <div class="footer-col-2">
            <img src="../minor project/assets/images/logo.png" alt="snap cart logo " />
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

    <!-- JS for Menu Toggle-->
    <script>
      var MenuItems = document.getElementById("MenuItems");

      MenuItems.style.maxHeight = "0px";

      function menutoggle() {
        if (MenuItems.style.maxHeight == "0px") {
          MenuItems.style.maxHeight = "200px";
        } else {
          MenuItems.style.maxHeight = "0px";
        }
      }
    </script>
    <script src="../minor project/assets/js/main.js"></script>
    
    <!-- JS for Banner Slideshow -->
    <script>
      let slideIndex = 1;
      let slideInterval;
      
      function startSlideshow() {
        // Clear any existing interval
        if (slideInterval) {
          clearInterval(slideInterval);
        }
        
        // Auto slide change
        slideInterval = setInterval(function() {
          plusSlides(1);
        }, 3800); // Change image every 3.8 seconds
      }
      
      function plusSlides(n) {
        showSlides(slideIndex += n);
      }
      
      function currentSlide(n) {
        showSlides(slideIndex = n);
      }
      
      function showSlides(n) {
        let i;
        let slides = document.getElementsByClassName("banner-slide");
        let dots = document.getElementsByClassName("dot");
        
        if (n > slides.length) {slideIndex = 1}
        if (n < 1) {slideIndex = slides.length}
        
        // Hide all slides first
        for (i = 0; i < slides.length; i++) {
          slides[i].style.display = "none";
          slides[i].classList.remove("active");
        }
        
        // Remove active class from all dots
        for (i = 0; i < dots.length; i++) {
          dots[i].classList.remove("active");
        }
        
        // Show the current slide and activate its dot
        slides[slideIndex-1].style.display = "block";
        setTimeout(function() {
          slides[slideIndex-1].classList.add("active");
        }, 10);
        dots[slideIndex-1].classList.add("active");
      }
      
      // Initialize slideshow
      document.addEventListener("DOMContentLoaded", function() {
        showSlides(slideIndex);
        startSlideshow();
        
        // Pause slideshow when user hovers over it
        const slideshow = document.querySelector('.banner-slideshow');
        slideshow.addEventListener('mouseenter', function() {
          clearInterval(slideInterval);
        });
        
        // Resume slideshow when user leaves
        slideshow.addEventListener('mouseleave', function() {
          startSlideshow();
        });
      });
    </script>
  </body>
</html>
