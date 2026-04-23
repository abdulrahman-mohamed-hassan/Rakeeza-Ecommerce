<?php
/*
|--------------------------------------------------------------------------
| FILE: index.php (HOME PAGE)
|--------------------------------------------------------------------------
| PURPOSE: Main landing page with categories carousel and featured products
|
| DICTIONARY:
| -----------
| Lines 22-24  : Include session config and database connection
| Lines 26-34  : Fetch 9 most recent products for home display
| Lines 36-38  : Note about removed table creation checks
| Lines 40-47  : Fetch all categories for carousel
| Lines 49-330 : HTML HEAD and CSS STYLES (gallery, cards, carousel, mobile)
| Lines 332-333: Body with background blur
| Lines 335    : Include navbar component
| Lines 337-341: Products datalist for search autocomplete
| Lines 343-379: CATEGORIES CAROUSEL (auto-scrolling with discover more card)
| Lines 381-397: PRODUCTS GRID with prices and "More Products" link
| Lines 400    : Include footer component
| Lines 404-456: JAVASCRIPT for carousel auto-scroll animation
|--------------------------------------------------------------------------
*/

// Optimized session handling for concurrent users
require_once 'session_config.php';
include 'DBM.php';

// Fetch only the most recent products for home page (limit to 9)
$products = [];
$limit = 9; // Number of products to show on home page
$result = $conn->query("SELECT * FROM products ORDER BY id DESC LIMIT $limit");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $products[$row['id']] = $row;
    }
}

// Removed table creation checks - these should be done once during setup
// Running these on every page load blocks concurrent users
// Use setup_db.php or optimize_database.php to set up tables initially

// Fetch all categories for carousel
$categories = [];
$cat_result = $conn->query("SELECT * FROM categories ORDER BY name ASC");
if ($cat_result) {
    while ($row = $cat_result->fetch_assoc()) {
        $categories[] = $row;
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
 <title>Products</title>
 <link rel="stylesheet" href="css/mobile-fixes.css">
 <style type="text/css">

 /* the first div  */
 div.gallery{
 border: 1px solid gold;
 float:left;
 width:150px;
 margin: 10px;
 opacity: 100%;
 overflow: hidden;
 flex: 0 0 150px; 
 }
 div.gallery img{
 width: 100%;
 height:200px;
 display: block;
 }
 div.desc{
 padding:15px;
 height: auto;
 }
 div.gallery:hover{
 border:1px solid green;
 opacity: 50%;
}

/* Alternate gallery:*/
.alt-card{
  position: relative;
  overflow: hidden;
  border-radius: 8px;
  box-shadow: 0 8px 20px rgba(0,0,0,0.12);
  background: #fff;
  transition: transform .25s ease, box-shadow .25s ease;
}
.alt-card img{
  width: 100%;
  height: 220px;
  object-fit: cover;
  display: block;
  transition: transform .35s ease;
}
.alt-card:hover{ 
  transform: translateY(-6px); 
  box-shadow: 0 14px 30px rgba(0,0,0,0.18); 
}
.alt-card:hover img{ 
  transform: scale(1.06); 
}
.alt-caption{
  position: absolute;
  left: 0;
  right: 0;
  bottom: 0;
  padding: 12px 8px;
  color: #fff;
  font-weight: 600;
  text-align: center;
  background: linear-gradient(180deg, rgba(0,0,0,0) 0%, rgba(0,0,0,0.7) 100%);
}
.product-price {
  display: block;
  margin-top: 4px;
  font-size: 16px;
  font-weight: 700;
  color: #cfa967;
}
.more-products-card {
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 220px;
  text-decoration: none;
  transition: transform .25s ease, box-shadow .25s ease;
}
.more-products-card:hover {
  transform: translateY(-6px);
  box-shadow: 0 14px 30px rgba(0,0,0,0.18);
}
.more-products-text {
  color: white;
  font-size: 24px;
  font-weight: 700;
  text-align: center;
  text-transform: uppercase;
  letter-spacing: 1px;
}

/* click */
.card-link {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 10;
}


.gallery-container {
  padding-top: 90px;
  max-width: 1200px;
  margin: 0 auto;
  padding-left: 16px;
  padding-right: 16px;
}
.gallery-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  gap: 24px;
  margin-bottom: 60px;
}

/* Categories Carousel */
.categories-carousel-wrapper {
  overflow: hidden;
  margin-bottom: 60px;
  padding: 10px 0;
  position: relative;
  width: 100%;
}

.categories-carousel {
  display: flex;
  gap: 0;
  width: 100%;
}

.categories-slider {
  display: flex;
  gap: 24px;
  transition: transform 0.6s ease;
  width: fit-content;
  will-change: transform;
}

.categories-carousel-wrapper:hover .categories-slider {
  transition: transform 0.6s ease;
}

.categories-carousel .alt-card {
  min-width: 220px;
  width: 220px;
  flex-shrink: 0;
}

.discover-more-card {
  position: relative;
  z-index: 2;
}

footer {
  background:#1e1e1e;
  color:#fff;
  text-align:center;
  padding:40px 20px;
  margin-top:auto;
  padding-bottom: calc(40px + env(safe-area-inset-bottom));
}

/* ========================================================================
   MOBILE RESPONSIVE STYLES - iPhone & Mobile Optimization
   ======================================================================== */

@media (max-width: 768px) {
  /* Gallery container */
  .gallery-container {
    padding-top: 70px;
    padding-left: 12px;
    padding-right: 12px;
  }
  
  /* Gallery grid - single column on very small screens */
  .gallery-grid {
    grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
    gap: 16px;
    margin-bottom: 40px;
  }
  
  /* Category carousel - smaller cards on mobile */
  .categories-carousel .alt-card {
    min-width: 180px;
    width: 180px;
  }
  
  /* Product cards */
  .alt-card img {
    height: 200px;
  }
  
  .alt-caption {
    padding: 10px 6px;
    font-size: 14px;
  }
  
  .product-price {
    font-size: 14px;
  }
  
  .more-products-text {
    font-size: 20px;
  }
}

/* iPhone specific optimizations */
@media (max-width: 480px) {
  /* Single column layout */
  .gallery-grid {
    grid-template-columns: 1fr;
    gap: 20px;
  }
  
  /* Smaller category cards */
  .categories-carousel .alt-card {
    min-width: 160px;
    width: 160px;
  }
  
  
  /* Touch-friendly buttons */
  button, .btn, input[type="submit"] {
    min-height: 44px;
    min-width: 44px;
    font-size: 16px; /* Prevents zoom on iOS */
  }
  
  /* Form inputs */
  input[type="text"],
  input[type="email"],
  input[type="password"],
  input[type="number"],
  textarea,
  select {
    font-size: 16px; /* Prevents zoom on iOS */
    padding: 12px;
    min-height: 44px;
  }
  
  /* Better spacing */
  .gallery-container {
    padding-top: 66px;
    padding-left: 10px;
    padding-right: 10px;
  }
}


/* Landscape orientation on mobile */
@media (max-width: 768px) and (orientation: landscape) {
  .gallery-container {
    padding-top: 60px;
  }
}

/* Prevent text size adjustment on iOS */
html {
  -webkit-text-size-adjust: 100%;
  text-size-adjust: 100%;
}

/* Smooth scrolling */
html {
  scroll-behavior: smooth;
  -webkit-overflow-scrolling: touch;
}

/* Better touch targets */
a, button {
  touch-action: manipulation;
}
 </style>
</head>
<body style="background:#f4ede2; margin-top: 60px; position: relative; overscroll-behavior: none; touch-action: pan-y;">

<?php include 'navbar.php'; ?>

<datalist id="products-list">
  <?php foreach ($products as $p): ?>
    <option value="<?php echo htmlspecialchars($p['name']); ?>"></option>
  <?php endforeach; ?>
</datalist>

 <div class="gallery-container" id="products">

   <!-- Categories Carousel -->
<div class="categories-carousel-wrapper">
  <div class="categories-carousel" id="categoriesCarousel">
    <div class="categories-slider" id="categoriesSlider">
      <?php if (count($categories) > 0): ?>
        <?php foreach($categories as $cat): ?>
          <a href="category.php?id=<?php echo $cat['id']; ?>" class="alt-card category-card" style="position:relative; flex: 0 0 auto;">
            <img src="<?php echo htmlspecialchars($cat['img']); ?>" alt="<?php echo htmlspecialchars($cat['name']); ?>">
            <div class="alt-caption"><?php echo htmlspecialchars($cat['name']); ?></div>
          </a>
        <?php endforeach; ?>
      <?php else: ?>
        <!-- Fallback: Show original categories if none exist in database -->
        <a href="category.php?name=Tables" class="alt-card category-card" style="position:relative; flex: 0 0 auto;">
          <img src="assets/images/img2.avif" alt="Tables">
          <div class="alt-caption">Tables</div>
        </a>
        <a href="category.php?name=Chairs" class="alt-card category-card" style="position:relative; flex: 0 0 auto;">
          <img src="assets/images/seat.avif" alt="Chairs">
          <div class="alt-caption">Chairs</div>
        </a>
        <a href="category.php?name=Drawers" class="alt-card category-card" style="position:relative; flex: 0 0 auto;">
          <img src="assets/images/Drawers.avif" alt="Drawers">
          <div class="alt-caption">Drawers</div>
        </a>
      <?php endif; ?>
    </div>
    <!-- Discover More Card - Always at the end, not duplicated -->
    <a href="categories.php" class="alt-card discover-more-card" style="background:#cfa967; flex: 0 0 auto; margin-left: 24px;">
      <div class="alt-caption" style="position:relative; color:white; font-size:24px; padding:70px 20px;">
        discover more !
      </div>
    </a>
  </div>
</div>

   <!-- Products from database -->
   <div class="gallery-grid">
  <?php foreach ($products as $id => $p): ?>
  <div class="alt-card" style="position:relative;">
    <a href="product.php?id=<?php echo $id; ?>" class="card-link"></a>
    <img src="<?php echo htmlspecialchars($p['img']); ?>" alt="<?php echo htmlspecialchars($p['name']); ?>">
    <div class="alt-caption">
      <?php echo htmlspecialchars($p['name']); ?>
      <span class="product-price">EGP <?php echo number_format($p['price'], 2); ?></span>
    </div>
  </div>
  <?php endforeach; ?>
  <!-- More Products Card -->
  <a href="products.php"class="alt-card category-card" style="position:relative; flex: 0 0 auto;">
    <img src="assets/images/WhatsApp Image 2025-12-21 at 6.56.15 PM (1).jpeg" alt="Drawers">
  </a>
</div>
 

<?php include 'footer.php'; ?>
</div>
<?php $conn->close(); ?>

<script>
// Categories Carousel Auto-Scroll
document.addEventListener('DOMContentLoaded', function() {
  const slider = document.getElementById('categoriesSlider');
  if (!slider) return;
  
  const wrapper = slider.closest('.categories-carousel-wrapper');
  const categoryCards = Array.from(slider.querySelectorAll('.category-card'));
  if (categoryCards.length === 0) return;
  
  const cardWidth = 220 + 24; // card width + gap
  let currentIndex = 0;
  let isPaused = false;
  let scrollInterval;
  
  // Duplicate only category cards (not discover more) for seamless loop
  const originalCount = categoryCards.length;
  categoryCards.forEach(card => {
    const clone = card.cloneNode(true);
    slider.appendChild(clone);
  });
  
  function scrollNext() {
    if (isPaused || originalCount === 0) return;
    
    currentIndex++;
    if (currentIndex >= originalCount) {
      currentIndex = 0;
      slider.style.transition = 'none';
      slider.style.transform = 'translateX(0)';
      setTimeout(() => {
        slider.style.transition = 'transform 0.6s ease';
      }, 50);
    } else {
      slider.style.transform = `translateX(-${currentIndex * cardWidth}px)`;
    }
  }
  
  // Start auto-scroll every 3 seconds
  if (originalCount > 0) {
    scrollInterval = setInterval(scrollNext, 3000);
  }
  
  // Pause on hover
  wrapper.addEventListener('mouseenter', () => {
    isPaused = true;
  });
  
  wrapper.addEventListener('mouseleave', () => {
    isPaused = false;
  });
});
</script>
</body>
</html>