<?php
/*
|--------------------------------------------------------------------------
| FILE: wishlist.php
|--------------------------------------------------------------------------
| PURPOSE: User's wishlist page - save products for later
|
| DICTIONARY:
| -----------
| Lines 20-22  : Include session config and database connection
| Lines 24-28  : Check if user is logged in (redirect to login if not)
| Lines 30    : Get user ID from session
| Lines 32-43  : Auto-create wishlist table if missing
| Lines 45-52  : Handle REMOVE from wishlist (via POST)
| Lines 54-78  : Handle ADD TO CART from wishlist (adds to cart and removes from wishlist)
| Lines 80-86  : Handle CLEAR ALL wishlist (via POST)
| Lines 88-101 : Fetch user's wishlist items from database (JOIN with products)
| Lines 104-373: HTML HEAD and CSS STYLES (background, cards, buttons, mobile)
| Lines 375-378: Body with background blur and navbar include
| Lines 380-434 : WISHLIST DISPLAY (header with count, grid of product cards, empty state)
| Lines 436    : Include footer component
|--------------------------------------------------------------------------
*/

// Optimized session handling for concurrent users
require_once 'session_config.php';
include 'DBM.php';

// Redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Ensure wishlist table exists
$check_wishlist_table = $conn->query("SHOW TABLES LIKE 'wishlist'");
if ($check_wishlist_table->num_rows == 0) {
    $conn->query("CREATE TABLE wishlist (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        product_id INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY unique_wishlist (user_id, product_id),
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
    )");
}

// Handle remove from wishlist
if (isset($_POST['remove_from_wishlist'])) {
    $product_id = intval($_POST['product_id']);
    $stmt = $conn->prepare("DELETE FROM wishlist WHERE user_id = ? AND product_id = ?");
    $stmt->bind_param("ii", $user_id, $product_id);
    $stmt->execute();
    $stmt->close();
}

// Handle add to cart from wishlist
if (isset($_POST['add_to_cart_from_wishlist'])) {
    $product_id = intval($_POST['product_id']);
    
    // Initialize cart if not exists
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    
    // Add to cart
    if (isset($_SESSION['cart'][$product_id])) {
        $_SESSION['cart'][$product_id]++;
    } else {
        $_SESSION['cart'][$product_id] = 1;
    }
    
    // Remove from wishlist
    $stmt = $conn->prepare("DELETE FROM wishlist WHERE user_id = ? AND product_id = ?");
    $stmt->bind_param("ii", $user_id, $product_id);
    $stmt->execute();
    $stmt->close();
    
    header("Location: cart.php");
    exit;
}

// Handle clear all wishlist
if (isset($_POST['clear_wishlist'])) {
    $stmt = $conn->prepare("DELETE FROM wishlist WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->close();
}

// Fetch wishlist items
$wishlist_items = [];
$result = $conn->query("
    SELECT w.*, p.name, p.price, p.img, p.description 
    FROM wishlist w 
    JOIN products p ON w.product_id = p.id 
    WHERE w.user_id = $user_id 
    ORDER BY w.created_at DESC
");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $wishlist_items[] = $row;
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
  <link rel="stylesheet" href="css/mobile-styles.css">
  <link rel="stylesheet" href="css/mobile-fixes.css">
  <title>My Wishlist - Furniture Store</title>
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { 
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
      background: #f4ede2; 
      margin-top: 60px; 
      min-height: 100vh;
    }
    

    
    /* Navbar styles are in navbar.php - no need to duplicate */
    
    .container {
      max-width: 1200px;
      margin: 30px auto;
      margin-top: 100px;
      padding: 0 20px;
    }
    
    .page-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 30px;
      background: white;
      padding: 25px 30px;
      border-radius: 12px;
      box-shadow: 0 5px 15px rgba(0,0,0,0.08);
    }
    
    .page-title {
      font-size: 28px;
      color: #2c3e50;
      display: flex;
      align-items: center;
      gap: 12px;
    }
    
    .item-count {
      background: #cfa967;
      color: white;
      padding: 5px 15px;
      border-radius: 20px;
      font-size: 14px;
    }
    
    .clear-btn {
      background: #e74c3c;
      color: white;
      border: none;
      padding: 12px 25px;
      border-radius: 8px;
      cursor: pointer;
      font-size: 14px;
      font-weight: 600;
      transition: all 0.3s ease;
    }
    
    .clear-btn:hover {
      background: #c0392b;
      transform: translateY(-2px);
    }
    
    .wishlist-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
      gap: 25px;
    }
    
    .wishlist-card {
      background: white;
      border-radius: 12px;
      overflow: hidden;
      box-shadow: 0 5px 15px rgba(0,0,0,0.08);
      transition: all 0.3s ease;
    }
    
    .wishlist-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 10px 30px rgba(0,0,0,0.15);
    }
    
    .card-image {
      position: relative;
      width: 100%;
      height: 220px;
      overflow: hidden;
    }
    
    .card-image img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      transition: transform 0.3s ease;
    }
    
    .wishlist-card:hover .card-image img {
      transform: scale(1.05);
    }
    
    .remove-btn {
      position: absolute;
      top: 10px;
      right: 10px;
      background: rgba(231, 76, 60, 0.9);
      color: white;
      border: none;
      width: 35px;
      height: 35px;
      border-radius: 50%;
      cursor: pointer;
      font-size: 18px;
      transition: all 0.3s ease;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    
    .remove-btn:hover {
      background: #c0392b;
      transform: scale(1.1);
    }
    
    .card-body {
      padding: 20px;
    }
    
    .card-title {
      font-size: 18px;
      color: #2c3e50;
      font-weight: 600;
      margin-bottom: 8px;
    }
    
    .card-title a {
      color: inherit;
      text-decoration: none;
    }
    
    .card-title a:hover {
      color: #cfa967;
    }
    
    .card-price {
      font-size: 22px;
      color: #cfa967;
      font-weight: bold;
      margin-bottom: 15px;
    }
    
    .card-actions {
      display: flex;
      gap: 10px;
    }
    
    .btn-cart {
      flex: 1;
      background: #cfa967;
      color: white;
      border: none;
      padding: 12px;
      border-radius: 8px;
      cursor: pointer;
      font-size: 14px;
      font-weight: 600;
      transition: all 0.3s ease;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
    }
    
    .btn-cart:hover {
      background: #b58956;
    }
    
    .btn-view {
      background: #2c3e50;
      color: white;
      border: none;
      padding: 12px 20px;
      border-radius: 8px;
      cursor: pointer;
      font-size: 14px;
      font-weight: 600;
      text-decoration: none;
      transition: all 0.3s ease;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    
    .btn-view:hover {
      background: #34495e;
    }
    
    /* Empty State */
    .empty-state {
      text-align: center;
      padding: 80px 20px;
      background: white;
      border-radius: 12px;
      box-shadow: 0 5px 15px rgba(0,0,0,0.08);
    }
    
    .empty-icon {
      font-size: 80px;
      margin-bottom: 20px;
    }
    
    .empty-title {
      font-size: 24px;
      color: #2c3e50;
      margin-bottom: 10px;
    }
    
    .empty-text {
      color: #7f8c8d;
      margin-bottom: 30px;
    }
    
    .btn-browse {
      background: #cfa967;
      color: white;
      border: none;
      padding: 15px 40px;
      border-radius: 8px;
      font-size: 16px;
      font-weight: 600;
      text-decoration: none;
      display: inline-block;
      transition: all 0.3s ease;
    }
    
    .btn-browse:hover {
      background: #b58956;
      transform: translateY(-2px);
    }
    
    footer {
      background: #1e1e1e;
      color: #fff;
      text-align: center;
      padding: 40px 20px;
      margin-top: 250px!important;
      min-height: 10vh!important;
    }
  </style>
</head>
<body>
<div class="bg-blur"></div>

<?php include 'navbar.php'; ?>

<div class="container">
  <?php if (count($wishlist_items) > 0): ?>
  
  <div class="page-header">
    <div class="page-title">
      ❤️ My Wishlist
      <span class="item-count"><?php echo count($wishlist_items); ?> item<?php echo count($wishlist_items) > 1 ? 's' : ''; ?></span>
    </div>
    <form method="post" onsubmit="return confirm('Are you sure you want to clear your entire wishlist?');">
      <button type="submit" name="clear_wishlist" class="clear-btn">🗑️ Clear All</button>
    </form>
  </div>
  
  <div class="wishlist-grid">
    <?php foreach ($wishlist_items as $item): ?>
    <div class="wishlist-card">
      <div class="card-image">
        <a href="product.php?id=<?php echo $item['product_id']; ?>">
          <img src="<?php echo htmlspecialchars($item['img']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
        </a>
        <form method="post" style="display: inline;">
          <input type="hidden" name="product_id" value="<?php echo $item['product_id']; ?>">
          <button type="submit" name="remove_from_wishlist" class="remove-btn" title="Remove from wishlist">×</button>
        </form>
      </div>
      <div class="card-body">
        <h3 class="card-title">
          <a href="product.php?id=<?php echo $item['product_id']; ?>"><?php echo htmlspecialchars($item['name']); ?></a>
        </h3>
        <div class="card-price">EGP <?php echo number_format($item['price'], 2); ?></div>
        <div class="card-actions">
          <form method="post" style="flex: 1;">
            <input type="hidden" name="product_id" value="<?php echo $item['product_id']; ?>">
            <button type="submit" name="add_to_cart_from_wishlist" class="btn-cart">
              🛒 Add to Cart
            </button>
          </form>
          <a href="product.php?id=<?php echo $item['product_id']; ?>" class="btn-view">View</a>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  
  <?php else: ?>
  
  <div class="empty-state">
    <div class="empty-icon">💔</div>
    <h2 class="empty-title">Your wishlist is empty</h2>
    <p class="empty-text">Start adding products you love to your wishlist!</p>
    <a href="products.php" class="btn-browse">Browse Products</a>
  </div>
  
  <?php endif; ?>
</div>

<?php include 'footer.php'; ?>
</body>
</html>
<?php $conn->close(); ?>

