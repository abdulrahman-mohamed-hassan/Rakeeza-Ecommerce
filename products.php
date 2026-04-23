<?php
/*
|--------------------------------------------------------------------------
| FILE: products.php
|--------------------------------------------------------------------------
| PURPOSE: Displays all products with pagination
|
| DICTIONARY:
| -----------
| Lines 22-24  : Include session config and database connection
| Lines 26-29  : Pagination settings (12 products per page, get current page)
| Lines 31-33  : Get total product count for pagination
| Lines 35-37  : Calculate total pages and validate current page
| Lines 39-40  : Calculate offset for SQL query
| Lines 42-49  : Fetch products for current page
| Lines 51-231 : HTML HEAD and CSS STYLES (background, cards, pagination, mobile)
| Lines 234-237: Body with background blur and navbar include
| Lines 239-253 : Products datalist for search autocomplete
| Lines 255-324 : PRODUCTS GRID display with pagination controls
| Lines 326    : Include footer component
|--------------------------------------------------------------------------
*/

// Optimized session handling for concurrent users
require_once 'session_config.php';
include 'DBM.php';

// Pagination settings
$products_per_page = 12;
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($current_page < 1) $current_page = 1;

// Get total number of products
$total_products_result = $conn->query("SELECT COUNT(*) as total FROM products");
$total_products = $total_products_result->fetch_assoc()['total'];

// Calculate total pages
$total_pages = ceil($total_products / $products_per_page);
if ($current_page > $total_pages && $total_pages > 0) $current_page = $total_pages;

// Calculate offset
$offset = ($current_page - 1) * $products_per_page;

// Fetch products for current page
$products = [];
$result = $conn->query("SELECT * FROM products ORDER BY id DESC LIMIT $products_per_page OFFSET $offset");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Products - Furniture Store</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes, viewport-fit=cover">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<meta name="format-detection" content="telephone=no">
<link rel="stylesheet" href="css/mobile-styles.css">
<link rel="stylesheet" href="css/mobile-fixes.css">

<style>
* { margin: 0; padding: 0; box-sizing: border-box; }





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
  grid-template-columns: repeat(auto-fit, minmax(250px,350px)); 
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

/* Pagination */
.pagination {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 10px;
  margin: 40px 0;
  flex-wrap: wrap;
}

.pagination a,
.pagination span {
  display: inline-block;
  padding: 10px 16px;
  border-radius: 8px;
  text-decoration: none;
  font-weight: 500;
  transition: all 0.3s;
  min-width: 44px;
  text-align: center;
}

.pagination a {
  background: #2c3e50;
  color: #ecf0f1;
  border: 2px solid #2c3e50;
}

.pagination a:hover {
  background: #cfa967;
  color: #2c3e50;
  border-color: #cfa967;
}

.pagination .active {
  background: #cfa967;
  color: #2c3e50;
  border: 2px solid #cfa967;
  font-weight: bold;
}

.pagination .disabled {
  background: #ccc;
  color: #666;
  cursor: not-allowed;
  border: 2px solid #ccc;
}

.pagination .disabled:hover {
  background: #ccc;
  color: #666;
  border-color: #ccc;
}

.pagination-info {
  text-align: center;
  color: #666;
  margin-bottom: 20px;
  font-size: 14px;
}

footer {
  background: #200c59ff;
  color: #fff;
  text-align: center;
  padding: 40px 20px;
  margin-top: auto;
}
</style>
</head>

<body style="background:#f4ede2; margin-top: 60px; position: relative; overscroll-behavior: none; touch-action: pan-y;">


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

<!-- Products -->
<div class="products-container">
  <div class="products-title">Our Products</div>

  <?php if ($total_products > 0): ?>
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

    <!-- Pagination Bar -->
    <?php if ($total_pages > 1): ?>
    <div class="pagination">
      <!-- Previous Button -->
      <?php if ($current_page > 1): ?>
        <a href="?page=<?php echo $current_page - 1; ?>">← Previous</a>
      <?php else: ?>
        <span class="disabled">← Previous</span>
      <?php endif; ?>

      <!-- Page Numbers -->
      <?php
      $start_page = max(1, $current_page - 2);
      $end_page = min($total_pages, $current_page + 2);
      
      if ($start_page > 1): ?>
        <a href="?page=1">1</a>
        <?php if ($start_page > 2): ?>
          <span>...</span>
        <?php endif; ?>
      <?php endif; ?>

      <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
        <?php if ($i == $current_page): ?>
          <span class="active"><?php echo $i; ?></span>
        <?php else: ?>
          <a href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
        <?php endif; ?>
      <?php endfor; ?>

      <?php if ($end_page < $total_pages): ?>
        <?php if ($end_page < $total_pages - 1): ?>
          <span>...</span>
        <?php endif; ?>
        <a href="?page=<?php echo $total_pages; ?>"><?php echo $total_pages; ?></a>
      <?php endif; ?>

      <!-- Next Button -->
      <?php if ($current_page < $total_pages): ?>
        <a href="?page=<?php echo $current_page + 1; ?>">Next →</a>
      <?php else: ?>
        <span class="disabled">Next →</span>
      <?php endif; ?>
    </div>
    <?php endif; ?>

  <?php else: ?>
    <div style="text-align: center; padding: 60px; color: #666;">
      <h3>No products available yet</h3>
      <p>Check back soon for our amazing furniture collection!</p>
    </div>
  <?php endif; ?>
</div>

<?php include 'footer.php'; ?>
</body>
</html>
<?php $conn->close(); ?>

