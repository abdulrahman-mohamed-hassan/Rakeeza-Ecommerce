<?php
/*
|--------------------------------------------------------------------------
| FILE: categories.php
|--------------------------------------------------------------------------
| PURPOSE: Displays all product categories in a grid layout
|
| DICTIONARY:
| -----------
| Lines 17-19  : Include session config and database connection
| Lines 22-29  : Fetch all categories from database
| Lines 31-219 : HTML HEAD and CSS STYLES (background, navbar, cards, footer)
| Lines 222-223: Body with background blur
| Lines 225-271 : NAVBAR and MOBILE MENU (inline - should use navbar.php)
| Lines 273-287 : Products datalist for search autocomplete
| Lines 289-310 : CATEGORIES GRID display with images and links
| Lines 312    : Include footer component
| Lines 313    : Mobile menu script (should be in navbar.php)
|--------------------------------------------------------------------------
*/

// Optimized session handling for concurrent users
require_once 'session_config.php';
include 'DBM.php';


// Fetch all categories
$categories = [];
$result = $conn->query("SELECT * FROM categories ORDER BY name ASC");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $categories[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Categories - Furniture Store</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes, viewport-fit=cover">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<meta name="format-detection" content="telephone=no">
<link rel="stylesheet" href="css/mobile-styles.css">

<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
body {
  background: #f4ede2;
  padding-top: 60px;
  font-family: Arial, sans-serif;
  position: relative;
}



.products-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 30px;
}

.products-title {
  font-size: 48px;
  color: #cfa967;
  text-align: center;
  margin-bottom: 40px;
  font-weight: bold;
}

.grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 25px;
  margin-bottom: 40px;
}

.card {
  border: none;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 8px 20px rgba(0,0,0,0.12);
  transition: transform 0.3s, box-shadow 0.3s;
  background: white;
  text-decoration: none;
  color: black;
  display: block;
}

.card:hover {
  transform: translateY(-6px);
  box-shadow: 0 14px 30px rgba(0,0,0,0.18);
}

.card img {
  width: 100%;
  height: 230px;
  object-fit: cover;
  display: block;
}

.card-body {
  padding: 20px;
}

.card-title {
  text-align: center;
  font-weight: 600;
  font-size: 18px;
  color: #2c3e50;
  margin: 0;
}

footer {
  background: #1e1e1e;
  color: #fff;
  text-align: center;
  padding: 40px 20px;
  margin-top: 100px!important;
}
</style>
</head>

<body>
<div class="bg-blur"></div>

<!-- Mobile Menu -->
<ul class="mobile-menu" id="mobileMenu">
  <?php if(isset($_SESSION['username'])): ?>
    <li class="username">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></li>
  <?php endif; ?>
  <li><a href="products.php">Products</a></li>
  <li><a href="categories.php">Categories</a></li>
  <li><a href="cart.php">🛒 Cart</a></li>
  <li><a href="wishlist.php">❤️ Wishlist</a></li>
  <li><a href="about.php">About us</a></li>
  <li><a href="contact.php">Contact us</a></li>
  <li>
    <form action="product.php" method="get" style="padding: 15px 20px;">
      <input type="text" name="q" list="products-list" autocomplete="off" placeholder="Search products" value="<?php echo isset($_GET['q']) ? htmlspecialchars($_GET['q']) : ''; ?>" style="width: 100%; padding: 10px; border-radius: 4px; border: 1px solid #ccc; font-size: 16px;">
      <button type="submit" style="width: 100%; margin-top: 10px; padding: 12px; background: #cfa967; color: #2c3e50; border: none; border-radius: 4px; font-size: 16px; font-weight: 600; cursor: pointer;">Search</button>
    </form>
  </li>
  <li><a href="logout.php" style="background-color: #dc3545; color: white;">Logout</a></li>
</ul>

<?php
// Fetch all products for search datalist
$all_products = [];
$datalist_result = $conn->query("SELECT name FROM products ORDER BY name ASC");
if ($datalist_result) {
    while ($row = $datalist_result->fetch_assoc()) {
        $all_products[] = $row['name'];
    }
}
?>
<datalist id="products-list">
  <?php foreach ($all_products as $pname): ?>
    <option value="<?php echo htmlspecialchars($pname); ?>"></option>
  <?php endforeach; ?>
</datalist>

<!-- Categories -->
<div class="products-container">
  <div class="products-title">Our Categories</div>

  <?php if (count($categories) > 0): ?>
    <div class="grid">
      <?php foreach($categories as $category): ?>
        <a href="category.php?id=<?php echo $category['id']; ?>" class="card">
          <img src="<?php echo htmlspecialchars($category['img']); ?>" alt="<?php echo htmlspecialchars($category['name']); ?>">
          <div class="card-body">
            <h5 class="card-title"><?php echo htmlspecialchars($category['name']); ?></h5>
          </div>
        </a>
      <?php endforeach; ?>
    </div>
  <?php else: ?>
    <div style="text-align: center; padding: 60px; color: #666;">
      <h3>No categories available yet</h3>
      <p>Check back soon for our category collection!</p>
    </div>
  <?php endif; ?>
</div>
<?php include 'navbar.php'; ?>
<?php include 'footer.php'; ?>
<script src="mobile-menu.js"></script>
<?php $conn->close(); ?>
</body>
</html>

