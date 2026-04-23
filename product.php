<?php
/*
|--------------------------------------------------------------------------
| FILE: product.php
|--------------------------------------------------------------------------
| PURPOSE: Single product page with image gallery, details, and wishlist
|
| DICTIONARY:
| -----------
| Lines 29-31  : Include session config and database connection
| Lines 33-34  : Note about removed table creation checks
| Lines 36-56  : Handle WISHLIST toggle (add/remove via POST)
| Lines 58-65  : Fetch all products for search functionality
| Lines 67-88  : Handle search query - find matching product by name
| Lines 90-92  : Get product by ID from URL or fallback to first product
| Lines 94-106 : Fetch product images (gallery) from product_images table
| Lines 108-114: Check if product is in user's wishlist
| Lines 116-128: Get related products (same category or random)
| Lines 131-558 : HTML HEAD and CSS STYLES (gallery, buttons, features, mobile)
| Lines 560-563: Body with background blur and navbar include
| Lines 565-569 : Products datalist for search autocomplete
| Lines 571-587 : IMAGE GALLERY with main image and thumbnails
| Lines 589-668 : PRODUCT INFO (category, name, price, description, quantity, buttons)
| Lines 671-687 : RELATED PRODUCTS section (if available)
| Lines 689    : Include footer component
| Lines 691-750 : JAVASCRIPT (mobile menu fallback, image switching, quantity controls)
|--------------------------------------------------------------------------
*/

// Optimized session handling for concurrent users
require_once 'session_config.php';
include 'DBM.php';

// Removed table creation checks - these block concurrent users
// Tables should be created once during initial setup via setup_db.php

// Handle wishlist toggle
if (isset($_POST['toggle_wishlist']) && isset($_SESSION['user_id'])) {
    $product_id = intval($_POST['product_id']);
    $user_id = $_SESSION['user_id'];
    
    // Check if already in wishlist
    $check = $conn->query("SELECT id FROM wishlist WHERE user_id = $user_id AND product_id = $product_id");
    if ($check->num_rows > 0) {
        // Remove from wishlist
        $conn->query("DELETE FROM wishlist WHERE user_id = $user_id AND product_id = $product_id");
    } else {
        // Add to wishlist
        $stmt = $conn->prepare("INSERT INTO wishlist (user_id, product_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $user_id, $product_id);
        $stmt->execute();
        $stmt->close();
    }
    
    header("Location: product.php?id=" . $product_id);
    exit;
}

// Fetch all products from database
$products = [];
$result = $conn->query("SELECT * FROM products ORDER BY id ASC");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $products[$row['id']] = $row;
    }
}

// Handle search query
$searchQuery = isset($_GET['q']) ? trim($_GET['q']) : '';
$noResults = false;

if ($searchQuery !== '') {
    $matchedId = null;
    foreach ($products as $pid => $p) {
        if (stripos($p['name'], $searchQuery) !== false) {
            $matchedId = $pid;
            break;
        }
    }

    if ($matchedId !== null) {
        $id = $matchedId;
    } else {
        $noResults = true;
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 1;
    }
} else {
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 1;
}

// Get the first product id if $id doesn't exist
$firstProductId = !empty($products) ? array_key_first($products) : 1;
$product = $products[$id] ?? ($products[$firstProductId] ?? ['name' => 'Product Not Found', 'price' => 0, 'img' => '', 'description' => 'No products available']);

// Fetch product images
$product_images = [];
$images_result = $conn->query("SELECT * FROM product_images WHERE product_id = $id ORDER BY is_primary DESC, sort_order ASC");
if ($images_result && $images_result->num_rows > 0) {
    while ($img = $images_result->fetch_assoc()) {
        $product_images[] = $img;
    }
} else {
    // Fallback to main product image
    if (!empty($product['img'])) {
        $product_images[] = ['img' => $product['img'], 'is_primary' => 1];
    }
}

// Check if in wishlist
$in_wishlist = false;
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $wishlist_check = $conn->query("SELECT id FROM wishlist WHERE user_id = $user_id AND product_id = $id");
    $in_wishlist = $wishlist_check && $wishlist_check->num_rows > 0;
}

// Get related products (same category or random)
$related_products = [];
if (isset($product['category_id']) && $product['category_id']) {
    $cat_id = $product['category_id'];
    $related_result = $conn->query("SELECT * FROM products WHERE category_id = $cat_id AND id != $id ORDER BY RAND() LIMIT 4");
} else {
    $related_result = $conn->query("SELECT * FROM products WHERE id != $id ORDER BY RAND() LIMIT 4");
}
if ($related_result) {
    while ($rel = $related_result->fetch_assoc()) {
        $related_products[] = $rel;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
 <meta charset="UTF-8">
 <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes, viewport-fit=cover">
 <meta name="apple-mobile-web-app-capable" content="yes">
 <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
 <meta name="format-detection" content="telephone=no">
 <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
 <meta http-equiv="Pragma" content="no-cache">
 <meta http-equiv="Expires" content="0">
 <title><?php echo htmlspecialchars($product['name']); ?> - Furniture</title>
 <link rel="stylesheet" href="css/mobile-styles.css">
 <link rel="stylesheet" href="css/mobile-fixes.css">
 <style type="text/css">
 *{ margin:0; padding:0; box-sizing:border-box; }
 html, body{ width:100%; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; overscroll-behavior: none; touch-action: pan-y; }
 .logo{
   height: 40px;
   width: auto;
   display: block;
 }
 
 body{ background:#f4ede2; margin:0; margin-top:60px; width:100%; min-height:100vh; overscroll-behavior: none; touch-action: pan-y; position: relative; }
 
 
 
 .container{ 
   max-width:1200px; 
   margin:30px auto 50px; 
   padding:30px; 
   display:flex; 
   gap:50px; 
   flex-wrap:wrap; 
   background:white; 
   border-radius:15px; 
   box-shadow:0 10px 30px rgba(0,0,0,0.1); 
 }
 
 /* Image Gallery */
 .gallery-section{ 
   flex:1; 
   min-width:350px; 
 }
 
 .main-image-container {
   position: relative;
   width: 100%;
   border-radius: 12px;
   overflow: hidden;
   background: #f8f8f8;
 }
 
 .main-image {
   width: 100%;
   height: 450px;
   object-fit: contain;
   display: block;
   transition: opacity 0.3s ease;
 }
 
 .thumbnails {
   display: flex;
   gap: 10px;
   margin-top: 15px;
   flex-wrap: wrap;
 }
 
 .thumbnail {
   width: 80px;
   height: 80px;
   border-radius: 8px;
   overflow: hidden;
   cursor: pointer;
   border: 3px solid transparent;
   transition: all 0.3s ease;
   background: #f8f8f8;
 }
 
 .thumbnail:hover {
   border-color: #cfa967;
   transform: scale(1.05);
 }
 
 .thumbnail.active {
   border-color: #cfa967;
 }
 
 .thumbnail img {
   width: 100%;
   height: 100%;
   object-fit: cover;
 }
 
 /* Product Info */
 .info{ 
   flex:1; 
   min-width:350px; 
   padding:20px; 
 }
 
 .product-title{ 
   font-size:36px; 
   color:#2c3e50; 
   margin-bottom: 10px;
   font-weight: 700;
 }
 
 .product-category {
   color: #7f8c8d;
   font-size: 14px;
   margin-bottom: 15px;
   text-transform: uppercase;
   letter-spacing: 1px;
 }
 
 .price{ 
   font-size:42px; 
   color:#cfa967; 
   font-weight:bold; 
   margin:20px 0; 
 }
 
 .price-note {
   font-size: 14px;
   color: #95a5a6;
   margin-top: -15px;
   margin-bottom: 20px;
 }
 
 .desc{ 
   font-size:16px; 
   line-height:1.8; 
   color:#555; 
   margin-bottom: 30px;
 }
 
 /* Action Buttons */
 .actions {
   display: flex;
   gap: 15px;
   flex-wrap: wrap;
   margin-bottom: 30px;
 }
 
 .btn-cart { 
   background:#cfa967; 
   color:white; 
   padding:16px 40px; 
   font-size:18px; 
   border:none; 
   border-radius:8px; 
   cursor:pointer; 
   font-weight: 600;
   transition: all 0.3s ease;
   display: inline-flex;
   align-items: center;
   gap: 10px;
 }
 
 .btn-cart:hover{ 
   background:#b58956; 
   transform: translateY(-2px);
 }
 
 .btn-wishlist {
   background: #cfa967;
   color: white;
   padding: 16px 30px;
   font-size: 18px;
   border: 2px solid;
   border-radius: 8px;
   cursor: pointer;
   font-weight: 600;
   transition: all 0.3s ease;
   display: inline-flex;
   align-items: center;
   gap: 10px;
 }
 
 .btn-wishlist:hover {
   background:#b58956; 
   transform: translateY(-2px);
 }
 
 .btn-wishlist.in-wishlist {
   background: #e74c3c;
   color: white;
 }
 
 .btn-wishlist.in-wishlist:hover {
   background: white;
   color: #e74c3c;
 }
 
 /* Features */
 .features {
   display: grid;
   grid-template-columns: repeat(2, 1fr);
   gap: 15px;
   margin-top: 20px;
   padding-top: 20px;
   border-top: 1px solid #eee;
 }
 
 .feature {
   display: flex;
   align-items: center;
   gap: 10px;
   color: #555;
   font-size: 14px;
 }
 
 .feature-icon {
   width: 40px;
   height: 40px;
   background: #f8f8f8;
   border-radius: 50%;
   display: flex;
   align-items: center;
   justify-content: center;
   font-size: 18px;
 }
 
 /* Related Products */
 .related-section {
   max-width: 1200px;
   margin: 0 auto 50px;
   padding: 0 30px;
 }
 
 .related-title {
   font-size: 24px;
   color: #cfa967;
   margin-bottom: 20px;
   font-weight: 600;
   background: #2c3e50;
   border-radius: 10px;
   padding: 10px;
   color: white;
   text-align: center;
   font-size: 24px;
   font-weight: 600;
   margin-bottom: 20px;
   margin-top: 20px;
   margin-left: 20px;
 }
 
 .related-grid {
   display: grid;
   grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
   gap: 20px;
 }
 
 .related-card {
   background: white;
   border-radius: 12px;
   overflow: hidden;
   box-shadow: 0 5px 15px rgba(0,0,0,0.08);
   transition: all 0.3s ease;
   text-decoration: none;
 }
 
 .related-card:hover {
   transform: translateY(-5px);
   box-shadow: 0 10px 25px rgba(0,0,0,0.15);
 }
 
 .related-card img {
   width: 100%;
   height: 180px;
   object-fit: cover;
 }
 
 .related-card-body {
   padding: 15px;
 }
 
 .related-card-title {
   font-size: 16px;
   color: #2c3e50;
   font-weight: 600;
   margin-bottom: 8px;
 }
 
 .related-card-price {
   font-size: 18px;
   color: #cfa967;
   font-weight: bold;
 }
 
 /* Quantity Selector */
 .quantity-selector {
   display: flex;
   align-items: center;
   gap: 15px;
   margin-bottom: 25px;
 }
 
 .quantity-label {
   font-size: 14px;
   color: #555;
   font-weight: 600;
 }
 
 .quantity-controls {
   display: flex;
   align-items: center;
   border: 2px solid #eee;
   border-radius: 8px;
   overflow: hidden;
 }
 
 .qty-btn {
   width: 40px;
   height: 40px;
   background: #f8f8f8;
   border: none;
   cursor: pointer;
   font-size: 20px;
   color: #2c3e50;
   transition: background 0.3s ease;
 }
 
 .qty-btn:hover {
   background: #cfa967;
   color: white;
 }
 
 .qty-input {
   width: 60px;
   height: 40px;
   text-align: center;
   border: none;
   font-size: 16px;
   font-weight: 600;
   color: #2c3e50;
 }
 
 .qty-input:focus {
   outline: none;
 }
 
 
 footer{ background:#1e1e1e; color:#fff; text-align:center; padding:40px 20px; margin-top:auto; }
 
 /* Image Zoom Effect */
 .zoom-lens {
   position: absolute;
   border: 1px solid #cfa967;
   width: 100px;
   height: 100px;
   pointer-events: none;
   opacity: 0;
   transition: opacity 0.2s;
 }
 
 .main-image-container:hover .zoom-lens {
   opacity: 1;
 }
 
 @media (max-width: 768px) {
   .container {
     padding: 20px;
     gap: 30px;
     flex-direction: column;
   }
   .gallery-section, .info {
     min-width: 100%;
   }
   .product-title {
     font-size: 28px;
   }
   .price {
     font-size: 32px;
   }
   .features {
     grid-template-columns: 1fr;
   }
   .actions {
     flex-direction: column;
   }
   .btn-cart, .btn-wishlist {
     width: 100%;
     justify-content: center;
     min-height: 50px;
     font-size: 16px;
   }
   .main-image {
     height: 350px;
   }
   .quantity-controls {
     min-height: 44px;
   }
   .qty-input {
     font-size: 16px;
   }
   input[type="text"],
   input[type="number"],
   textarea,
   select {
     font-size: 16px !important;
   }
 }
 
 @media (max-width: 480px) {
   .container {
     padding: 15px;
   }
   .product-title {
     font-size: 24px;
   }
   .price {
     font-size: 28px;
   }
   .main-image {
     height: 300px;
   }
   .thumbnail {
     width: 60px;
     height: 60px;
   }
 }
 </style>
</head>
<body style="background:#f4ede2; margin-top: 60px; position: relative; overscroll-behavior: none; touch-action: pan-y;">
<div class="bg-blur"></div>

<?php include 'navbar.php'; ?>

<datalist id="products-list">
  <?php foreach ($products as $p): ?>
    <option value="<?php echo htmlspecialchars($p['name']); ?>"></option>
  <?php endforeach; ?>
</datalist>

<div class="container">
  <!-- Image Gallery -->
  <div class="gallery-section">
    <div class="main-image-container">
      <img src="<?php echo htmlspecialchars($product_images[0]['img'] ?? $product['img']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="main-image" id="mainImage">
    </div>
    
    <?php if (count($product_images) > 1): ?>
    <div class="thumbnails">
      <?php foreach ($product_images as $index => $img): ?>
      <div class="thumbnail <?php echo $index === 0 ? 'active' : ''; ?>" onclick="changeImage('<?php echo htmlspecialchars($img['img']); ?>', this)">
        <img src="<?php echo htmlspecialchars($img['img']); ?>" alt="Thumbnail <?php echo $index + 1; ?>">
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>
  
  <!-- Product Info -->
  <div class="info">
    <?php if ($noResults): ?>
      <p style="color:#c0392b; margin-bottom:10px;">No products found for "<?php echo htmlspecialchars($searchQuery); ?>"</p>
    <?php endif; ?>
    
    <?php 
    // Get category name
    $category_name = '';
    if (isset($product['category_id']) && $product['category_id']) {
        $cat_result = $conn->query("SELECT name FROM categories WHERE id = " . $product['category_id']);
        if ($cat_result && $cat = $cat_result->fetch_assoc()) {
            $category_name = $cat['name'];
        }
    }
    ?>
    
    <?php if ($category_name): ?>
    <div class="product-category"><?php echo htmlspecialchars($category_name); ?></div>
    <?php endif; ?>
    
    <h1 class="product-title"><?php echo htmlspecialchars($product['name']); ?></h1>
    
    <div class="price">EGP <?php echo number_format($product['price'], 2); ?></div>
    <p class="price-note">Tax included. Shipping calculated at checkout.</p>
    
    <div class="desc"><?php echo nl2br(htmlspecialchars($product['description'] ?? 'No description available.')); ?></div>
    
    <!-- Quantity Selector -->
    <div class="quantity-selector">
      <span class="quantity-label">Quantity:</span>
      <div class="quantity-controls">
        <button type="button" class="qty-btn" onclick="changeQty(-1)">−</button>
        <input type="number" class="qty-input" id="quantity" value="1" min="1" max="99">
        <button type="button" class="qty-btn" onclick="changeQty(1)">+</button>
      </div>
    </div>
    
    <!-- Action Buttons -->
    <div class="actions">
      <form method="post" action="cart.php" style="display:inline;">
        <input type="hidden" name="product_id" value="<?php echo $id; ?>">
        <input type="hidden" name="quantity" value="1" id="cartQuantity">
        <button type="submit" name="add_to_cart" class="btn-cart">
          🛒 Add to Cart
        </button>
      </form>
      
      <?php if (isset($_SESSION['user_id'])): ?>
      <form method="post" style="display:inline;">
        <input type="hidden" name="product_id" value="<?php echo $id; ?>">
        <button type="submit" name="toggle_wishlist" class="btn-wishlist <?php echo $in_wishlist ? 'in-wishlist' : ''; ?>">
          <?php echo $in_wishlist ? '❤️ In Wishlist' : '🤍 Add to Wishlist'; ?>
        </button>
      </form>
      <?php else: ?>
      <a href="login.html" class="btn-wishlist">🤍 Add to Wishlist</a>
      <?php endif; ?>
    </div>
    
    <!-- Features -->
    <div class="features">
      <div class="feature">
        <div class="feature-icon">🚚</div>
        <span>Free shipping over EGP 5,000</span>
      </div>
      <div class="feature">
        <div class="feature-icon">↩️</div>
        <span>30-day return policy</span>
      </div>
      <div class="feature">
        <div class="feature-icon">🛡️</div>
        <span>2-year warranty</span>
      </div>
      <div class="feature">
        <div class="feature-icon">💳</div>
        <span>Secure payment</span>
      </div>
    </div>
  </div>
</div>

<!-- Related Products -->
<?php if (count($related_products) > 0): ?>
<div class="related-section">
  <h2 class="related-title">You May Also Like</h2>
  <div class="related-grid">
    <?php foreach ($related_products as $rel): ?>
    <a href="product.php?id=<?php echo $rel['id']; ?>" class="related-card">
      <img src="<?php echo htmlspecialchars($rel['img']); ?>" alt="<?php echo htmlspecialchars($rel['name']); ?>">
      <div class="related-card-body">
        <div class="related-card-title"><?php echo htmlspecialchars($rel['name']); ?></div>
        <div class="related-card-price">EGP <?php echo number_format($rel['price'], 2); ?></div>
      </div>
    </a>
    <?php endforeach; ?>
  </div>
</div>
<?php endif; ?>

<?php include 'footer.php'; ?>

<script>
// Ensure mobile menu function is available (fallback if mobile-menu.js doesn't load)
if (typeof toggleMobileMenu === 'undefined') {
  function toggleMobileMenu() {
    const menu = document.getElementById('mobileMenu');
    const toggle = document.querySelector('.mobile-menu-toggle');
    
    if (!menu || !toggle) {
      console.error('Mobile menu elements not found');
      return;
    }
    
    if (menu.classList.contains('active')) {
      menu.classList.remove('active');
      toggle.innerHTML = '☰';
      document.body.style.overflow = '';
      toggle.setAttribute('aria-expanded', 'false');
    } else {
      menu.classList.add('active');
      toggle.innerHTML = '✕';
      document.body.style.overflow = 'hidden';
      toggle.setAttribute('aria-expanded', 'true');
    }
  }
  
  // Make it globally available
  window.toggleMobileMenu = toggleMobileMenu;
}

// Change main image
function changeImage(src, element) {
  document.getElementById('mainImage').src = src;
  
  // Update active thumbnail
  document.querySelectorAll('.thumbnail').forEach(thumb => {
    thumb.classList.remove('active');
  });
  element.classList.add('active');
}

// Quantity controls
function changeQty(delta) {
  const input = document.getElementById('quantity');
  let value = parseInt(input.value) || 1;
  value += delta;
  if (value < 1) value = 1;
  if (value > 99) value = 99;
  input.value = value;
  document.getElementById('cartQuantity').value = value;
}

// Sync quantity input
document.getElementById('quantity').addEventListener('change', function() {
  let value = parseInt(this.value) || 1;
  if (value < 1) value = 1;
  if (value > 99) value = 99;
  this.value = value;
  document.getElementById('cartQuantity').value = value;
});
</script>
</body>
</html>
<?php $conn->close(); ?>
