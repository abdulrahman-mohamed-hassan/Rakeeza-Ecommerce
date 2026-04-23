<?php
/*
|--------------------------------------------------------------------------
| FILE: cart.php
|--------------------------------------------------------------------------
| PURPOSE: Shopping cart page - displays items, handles add/remove/update
|
| DICTIONARY:
| -----------
| Lines 21-23  : Include session config and database connection
| Lines 25-28  : Initialize empty cart in session if not exists
| Lines 30-42  : Handle ADD to cart (from product page via POST)
| Lines 44-48  : Handle REMOVE from cart (via GET parameter)
| Lines 50-60  : Handle UPDATE quantities (via POST)
| Lines 62-66  : Handle CHECKOUT button click (redirect to checkout.php)
| Lines 68-75  : Fetch products from database
| Lines 77-94  : Calculate cart total and prepare display data
| Lines 97-267 : HTML HEAD and CSS STYLES (navbar, cart items, mobile)
| Lines 269-299 : NAVBAR and MOBILE MENU (inline - should use navbar.php)
| Lines 299-341 : CART DISPLAY (empty state or items with quantities, total)
| Lines 343    : Include footer component
| Lines 345    : Mobile menu script (should be in navbar.php)
|--------------------------------------------------------------------------
*/

// Optimized session handling for concurrent users
require_once 'session_config.php';
include 'DBM.php';

// Initialize cart if not exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Handle add to cart
if (isset($_POST['add_to_cart'])) {
    $product_id = (int)$_POST['product_id'];
    $quantity = (int)$_POST['quantity'];

    if ($quantity > 0) {
        if (isset($_SESSION['cart'][$product_id])) {
            $_SESSION['cart'][$product_id] += $quantity;
        } else {
            $_SESSION['cart'][$product_id] = $quantity;
        }
    }
}

// Handle remove from cart
if (isset($_GET['remove'])) {
    $product_id = (int)$_GET['remove'];
    unset($_SESSION['cart'][$product_id]);
}

// Handle update quantity
if (isset($_POST['update_cart'])) {
    foreach ($_POST['quantity'] as $product_id => $quantity) {
        $quantity = (int)$quantity;
        if ($quantity > 0) {
            $_SESSION['cart'][$product_id] = $quantity;
        } else {
            unset($_SESSION['cart'][$product_id]);
        }
    }
}

// Handle checkout redirect
if (isset($_POST['checkout'])) {
    header("Location: checkout.php");
    exit;
}

// Fetch products from database
$products = [];
$result = $conn->query("SELECT * FROM products ORDER BY id ASC");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $products[$row['id']] = $row;
    }
}

// Calculate total
$total = 0;
$cart_items = [];
foreach ($_SESSION['cart'] as $product_id => $quantity) {
    if (isset($products[$product_id])) {
        $product = $products[$product_id];
        $subtotal = $product['price'] * $quantity;
        $total += $subtotal;
        $cart_items[] = [
            'id' => $product_id,
            'name' => $product['name'],
            'price' => $product['price'],
            'img' => $product['img'],
            'quantity' => $quantity,
            'subtotal' => $subtotal
        ];
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
 <title>Shopping Cart - Furniture</title>
 <link rel="stylesheet" href="css/mobile-styles.css">
 <style type="text/css">
 *{ margin:0; padding:0; box-sizing:border-box; }
 html, body{ width:100%; }
  ul{
 list-style-type: none;
 margin: 0;
 padding: 0;
 overflow: hidden;
 position: fixed;
 top: 0;
 left: 0;
 right: 0;
 z-index: 4;
 width: 100vw;
 background-color: #2c3e50;
 box-shadow: 0 2px 8px rgba(0,0,0,0.2);
 display: flex;
 align-items: center;
 height: 60px;
 flex-wrap: nowrap;
 justify-content: space-between;
 padding-left: env(safe-area-inset-left);
 padding-right: env(safe-area-inset-right);
 }
 li{
 display: flex;
 align-items: center;
 height: 100%;
 }
 li a{
 display: flex;
 align-items: center;
 padding: 0 12px;
 color: #ecf0f1;
 text-decoration: none;
 height: 100%;
 font-size: 14px;
 font-weight: 500;
 transition: all 0.3s ease;
 white-space: nowrap;
 min-height: 44px; /* iOS touch target minimum */
 -webkit-tap-highlight-color: rgba(207, 169, 103, 0.3);
 }
 li a:hover{
    background-color: #cfa967;
    color: #2c3e50;
    }
 .logo{
 height: 40px;
 width: auto;
 display: block;
 }
  body{ background:#f4ede2; margin:0; margin-top:60px; font-family:Arial; width:100%; position: relative; }
  .container{ max-width:1200px; margin:30px auto 50px; padding:30px; display:flex; flex-direction:column; gap:30px; background:white; border-radius:15px; box-shadow:0 10px 30px rgba(0,0,0,0.1); }
  .cart-header{ text-align:center; margin-bottom:20px; }
  .cart-header h1{ font-size:36px; color:#333; margin-bottom:10px; }
  .cart-item{ display:flex; gap:20px; padding:20px; border:1px solid #eee; border-radius:10px; margin-bottom:15px; align-items:center; }
  .cart-item img{ width:120px; height:120px; object-fit:cover; border-radius:8px; }
  .cart-item-details{ flex:1; }
  .cart-item-details h3{ font-size:20px; color:#333; margin-bottom:5px; }
  .cart-item-details p{ color:#666; margin-bottom:10px; }
  .quantity-controls{ display:flex; align-items:center; gap:10px; }
  .quantity-controls input{ width:60px; padding:5px; text-align:center; border:1px solid #ddd; border-radius:4px; font-size:16px; min-height:44px; }
  .remove-btn{ background:#dc3545; color:white; border:none; padding:8px 15px; border-radius:5px; cursor:pointer; transition:0.3s; min-height:44px; min-width:44px; }
  .remove-btn:hover{ background:#c82333; }
  .cart-total{ text-align:right; font-size:24px; font-weight:bold; color:#cfa967; margin-top:20px; padding-top:20px; border-top:2px solid #eee; }
  .checkout-btn{ background:#cfa967; color:white; padding:16px 40px; font-size:20px; border:none; border-radius:8px; cursor:pointer; margin-top:20px; transition:0.3s; min-height:50px; }
  .checkout-btn:hover{ background:#b58956; }
  .empty-cart{ text-align:center; padding:50px; color:#666; }
  .empty-cart h2{ font-size:28px; margin-bottom:20px; }
  .empty-cart p{ font-size:18px; margin-bottom:30px; }
  .continue-shopping{ display:inline-block; background:#2c3e50; color:white; padding:12px 25px; text-decoration:none; border-radius:6px; transition:0.3s; min-height:44px; }
  .continue-shopping:hover{ background:#34495e; }
  footer{ background:#1e1e1e; color:#fff; text-align:center; padding:40px 20px; margin-top:auto; padding-bottom: calc(40px + env(safe-area-inset-bottom)); }
  
  /* Mobile Responsive */
  @media (max-width: 768px) {
    .cart-item {
      flex-direction: column;
      text-align: center;
    }
    .cart-item img {
      width: 100%;
      max-width: 200px;
      height: auto;
      margin: 0 auto 15px;
    }
    .cart-item-details {
      width: 100%;
    }
    .quantity-controls {
      justify-content: center;
      margin-top: 10px;
    }
    .cart-total {
      text-align: center;
      font-size: 20px;
    }
    .checkout-btn {
      width: 100%;
      margin: 10px 0;
      font-size: 18px;
    }
    .container {
      padding: 20px 15px;
    }
    input[type="number"],
    input[type="text"] {
      font-size: 16px !important;
    }
  }
  
  @media (max-width: 480px) {
    .cart-header h1 {
      font-size: 24px;
    }
    .cart-item {
      padding: 15px;
    }
    .cart-item-details h3 {
      font-size: 18px;
    }
  }
 </style>
 <script>
 function updateTotal() {
   let total = 0;
   const cartItems = document.querySelectorAll('.cart-item');

   cartItems.forEach(item => {
     const quantityInput = item.querySelector('input[type="number"]');
     const priceText = item.querySelector('.cart-item-details p').textContent;
     const price = parseFloat(priceText.replace('EGP ', '').replace(',', ''));
     const quantity = parseInt(quantityInput.value) || 0;
     const subtotal = price * quantity;

     // Update subtotal display
     const subtotalElement = item.querySelector('p[style*="font-size:18px"]');
     if (subtotalElement) {
       subtotalElement.textContent = 'EGP ' + subtotal.toLocaleString();
     }

     total += subtotal;
   });

   // Update total display
   const totalElement = document.querySelector('.cart-total');
   if (totalElement) {
     totalElement.textContent = 'Total: EGP ' + total.toLocaleString();
   }
 }

 // Add event listeners to quantity inputs
 document.addEventListener('DOMContentLoaded', function() {
   const quantityInputs = document.querySelectorAll('input[type="number"]');
   quantityInputs.forEach(input => {
     input.addEventListener('input', updateTotal);
     input.addEventListener('change', updateTotal);
   });
 });
 </script>
</head>
<body style="position: relative;">
<?php include 'navbar.php'; ?>

 

<!-- Mobile Menu -->
<ul class="mobile-menu" id="mobileMenu">
  <li><a href="products.php">Products</a></li>
  <li><a href="categories.php">Categories</a></li>
  <li><a href="cart.php">🛒 Cart</a></li>
  <li><a href="wishlist.php">❤️ Wishlist</a></li>
  <li><a href="about.php">About us</a></li>
  <li><a href="contact.php">Contact us</a></li>
  <li><a href="logout.php" style="background-color: #dc3545; color: white;">Logout</a></li>
</ul> <div class="container">
  <div class="cart-header">
   <h1>Shopping Cart</h1>
   <p>Review your items and proceed to checkout</p>
  </div>

  <?php if (empty($cart_items)): ?>
   <div class="empty-cart">
    <h2>Your cart is empty</h2>
    <p>Add some products to get started!</p>
    <a href="index.php#products" class="continue-shopping">Continue Shopping</a>
   </div>
  <?php else: ?>
   <form method="post" action="cart.php">
    <?php foreach ($cart_items as $item): ?>
     <div class="cart-item">
      <img src="<?php echo $item['img']; ?>" alt="<?php echo $item['name']; ?>">
      <div class="cart-item-details">
       <h3><?php echo $item['name']; ?></h3>
       <p>EGP <?php echo number_format($item['price']); ?> each</p>
       <div class="quantity-controls">
        <label>Quantity:</label>
        <input type="number" name="quantity[<?php echo $item['id']; ?>]" value="<?php echo $item['quantity']; ?>" min="1">
        <a href="cart.php?remove=<?php echo $item['id']; ?>" class="remove-btn">Remove</a>
       </div>
      </div>
      <div style="text-align:right;">
       <p style="font-size:18px; font-weight:bold; color:#cfa967;">EGP <?php echo number_format($item['subtotal']); ?></p>
      </div>
     </div>
    <?php endforeach; ?>

    <div class="cart-total">
     Total: EGP <?php echo number_format($total); ?>
    </div>

    <div style="text-align:center;">
     <button type="submit" name="update_cart" class="checkout-btn" style="background:#2c3e50; margin-right:20px;">Update Cart</button>
     <button type="submit" name="checkout" class="checkout-btn">Proceed to Checkout</button>
    </div>
   </form>
  <?php endif; ?>
 </div>

 <?php include 'footer.php'; ?>
</div>
<script src="mobile-menu.js"></script>
<?php $conn->close(); ?>
</body>
</html>