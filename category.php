<?php
/*
|--------------------------------------------------------------------------
| FILE: category.php
|--------------------------------------------------------------------------
| PURPOSE: Displays products belonging to a specific category
|
| DICTIONARY:
| -----------
| Lines 17-19  : Include session config and database connection
| Lines 21     : Get category ID from URL parameter
| Lines 23-28  : Fetch category info from database
| Lines 30-39  : Fetch all products in this category
| Lines 41-178 : HTML HEAD and CSS STYLES (background, cards, mobile)
| Lines 181-184: Body with background blur and navbar include
| Lines 186-200 : Products datalist for search autocomplete
| Lines 202-232 : CATEGORY HEADER and PRODUCT GRID display
| Lines 234    : Include footer component
|--------------------------------------------------------------------------
*/

// Optimized session handling for concurrent users
require_once 'session_config.php';
include 'DBM.php';

$category_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch category info
$category = null;
$cat_result = $conn->query("SELECT * FROM categories WHERE id = $category_id");
if ($cat_result && $cat_result->num_rows > 0) {
    $category = $cat_result->fetch_assoc();
}

// Fetch products in this category
$products = [];
if ($category) {
    $result = $conn->query("SELECT * FROM products WHERE category_id = $category_id ORDER BY id ASC");
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $products[] = $row;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title><?php echo $category ? htmlspecialchars($category['name']) : 'Category'; ?> - Furniture Store</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes, viewport-fit=cover">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<meta name="format-detection" content="telephone=no">
<link rel="stylesheet" href="css/mobile-fixes.css">

<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
body {
  background: #f4ede2;
  padding-top: 60px;
  font-family: Arial, sans-serif;
  position: relative;
  overscroll-behavior: none;
  touch-action: pan-y;
}

.bg-blur {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-image: url('assets/images/WhatsApp Image 2025-12-07 at 4.50.21 PM.jpeg');
  background-size: cover;
  background-position: center;
  background-repeat: no-repeat;
  filter: blur(3px);
  -webkit-filter: blur(3px);
  z-index: -1;
}

@media (max-width: 768px) {
  .bg-blur {
    position: absolute;
    height: 100vh;
    min-height: 100%;
    background-attachment: scroll !important;
  }
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
  margin: 0 0 10px 0;
}

.card-price {
  text-align: center;
  font-size: 20px;
  font-weight: bold;
  color: #cfa967;
  margin: 0;
}

.discover-btn {
  display: block;
  text-align: center;
  background: #cfa967;
  color: #2c3e50;
  padding: 15px 40px;
  font-size: 18px;
  font-weight: bold;
  text-decoration: none;
  border-radius: 10px;
  margin: 40px auto;
  max-width: 300px;
  transition: background 0.3s;
}

.discover-btn:hover {
  background: #b58956;
}

footer {
  background: #1e1e1e;
  color: #fff;
  text-align: center;
  padding: 40px 20px;
  margin-top: auto;
}
</style>
</head>

<body>
<div class="bg-blur"></div>

<?php include 'navbar.php'; ?>

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

<!-- Products in Category -->
<div class="products-container">
  <?php if ($category): ?>
    <div class="products-title"><?php echo htmlspecialchars($category['name']); ?></div>

    <?php if (count($products) > 0): ?>
      <div class="grid">
        <?php foreach($products as $product): ?>
          <a href="product.php?id=<?php echo $product['id']; ?>" class="card">
            <img src="<?php echo htmlspecialchars($product['img']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
            <div class="card-body">
              <h5 class="card-title"><?php echo htmlspecialchars($product['name']); ?></h5>
              <p class="card-price">EGP <?php echo number_format($product['price']); ?></p>
            </div>
          </a>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <div style="text-align: center; padding: 60px; color: #666;">
        <h3>No products in this category yet</h3>
        <p>Check back soon for products in <?php echo htmlspecialchars($category['name']); ?>!</p>
        <a href="products.php" class="discover-btn">Discover All Products</a>
      </div>
    <?php endif; ?>
  <?php else: ?>
    <div style="text-align: center; padding: 60px; color: #666;">
      <h3>Category not found</h3>
      <a href="categories.php" class="discover-btn">View All Categories</a>
    </div>
  <?php endif; ?>
</div>

<?php include 'footer.php'; ?>
<?php $conn->close(); ?>
</body>
</html>


