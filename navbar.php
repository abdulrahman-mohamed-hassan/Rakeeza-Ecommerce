<?php
/*
||--------------------------------------------------------------------------
|| FILE: navbar.php
||--------------------------------------------------------------------------
|| PURPOSE: Reusable navbar component with mobile menu support
||          Includes mobile fixes for touch scrolling and background issues
||
|| DICTIONARY:
|| -----------
|| Lines 20-150  : Navbar CSS styles with mobile optimizations
|| Lines 152-200 : Desktop navbar HTML
|| Lines 202-220 : Mobile menu HTML
||--------------------------------------------------------------------------
*/

// Ensure session is started with optimized config
require_once 'session_config.php';
?>
<style type="text/css">
/* Mobile Menu Toggle Button */
.mobile-menu-toggle {
  display: none;
  background: none;
  border: none;
  color: #ecf0f1;
  font-size: 24px;
  cursor: pointer;
  padding: 10px 15px;
  z-index: 1001;
  min-width: 44px;
  min-height: 44px;
  touch-action: manipulation;
  position: relative;
  -webkit-tap-highlight-color: rgba(207, 169, 103, 0.3);
}
.mobile-menu-toggle:focus {
  outline: 2px solid #cfa967;
  outline-offset: 2px;
}

/* The nav bar */
.navbar-container {
  list-style-type: none;
  margin: 0;
  padding: 0;
  overflow: hidden;
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  z-index: 1000;
  width: 100%;
  background-color: #2c3e50;
  box-shadow: 0 2px 8px rgba(0,0,0,0.2);
  display: flex;
  align-items: center;
  height: 60px;
  flex-wrap: nowrap;
  justify-content: space-between;
  padding-left: env(safe-area-inset-left);
  padding-right: env(safe-area-inset-right);
  touch-action: pan-y;
  -webkit-overflow-scrolling: touch;
}

.navbar-container > div {
  display: flex;
  align-items: center;
  height: 100%;
}

.username {
  color: #ecf0f1;
  padding: 0 12px;
  font-size: 14px;
  font-weight: 500;
  white-space: nowrap;
}

.nav-search { 
  margin-left: auto; 
  margin-right: 20px; 
  display: flex; 
  align-items: center; 
  gap: 8px; 
}

.nav-search input[type="text"] { 
  padding: 6px 8px; 
  border-radius: 4px; 
  border: 1px solid #ccc; 
  font-size: 13px; 
}

.nav-search button { 
  padding: 6px 10px; 
  border: none; 
  border-radius: 4px; 
  background: #cfa967; 
  color: #2c3e50; 
  cursor: pointer; 
  font-size: 13px; 
  touch-action: manipulation;
}

.nav-search button:hover { 
  background: #b58956; 
}

.navbar-container li {
  display: flex;
  align-items: center;
  height: 100%;
}

.navbar-container li a {
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
  touch-action: manipulation;
}

.navbar-container li a:hover {
  background-color: #cfa967;
  color: #2c3e50;
}

.logo {
  height: 40px;
  width: auto;
  display: block;
}

/* Mobile Menu Overlay */
.mobile-menu {
  position: fixed !important;
  top: 60px !important;
  left: 0 !important;
  right: 0 !important;
  bottom: 0 !important;
  width: 100vw !important;
  max-width: 100vw !important;
  min-width: 100vw !important;
  background: #2c3e50;
  z-index: 10000;
  transform: translateX(-100%);
  transition: transform 0.3s ease;
  overflow-y: auto;
  overflow-x: visible;
  padding: 20px;
  padding-left: env(safe-area-inset-left);
  padding-right: env(safe-area-inset-right);
  padding-top: calc(20px + env(safe-area-inset-top));
  padding-bottom: calc(20px + env(safe-area-inset-bottom));
  -webkit-overflow-scrolling: touch;
  overscroll-behavior: contain;
  touch-action: pan-y;
  box-sizing: border-box;
  margin: 0;
}

.mobile-menu.active {
  transform: translateX(0) !important;
  z-index: 10000;
  left: 0 !important;
  right: 0 !important;
  width: 100vw !important;
}

.mobile-menu li {
  display: block;
  width: 100%;
  border-bottom: 1px solid rgba(255,255,255,0.1);
}

.mobile-menu li a {
  display: block;
  padding: 15px 20px;
  width: 100%;
  min-height: 50px;
  font-size: 16px;
  touch-action: manipulation;
  color: #ecf0f1 !important;
  text-decoration: none !important;
  -webkit-tap-highlight-color: rgba(207, 169, 103, 0.3);
}

.mobile-menu li a:link,
.mobile-menu li a:visited,
.mobile-menu li a:focus {
  color: #ecf0f1 !important;
  text-decoration: none !important;
  outline: none;
}

.mobile-menu li a:active {
  background-color: #cfa967 !important;
  color: #2c3e50 !important;
  text-decoration: none !important;
}

.mobile-menu li a:hover {
  background-color: rgba(207, 169, 103, 0.2) !important;
  color: #ecf0f1 !important;
  text-decoration: none !important;
}

/* Fix for logout button in mobile menu */
.mobile-menu li a[style*="background-color: #dc3545"] {
  background-color: #dc3545 !important;
  color: white !important;
}

.mobile-menu li a[style*="background-color: #dc3545"]:hover {
  background-color: #c82333 !important;
  color: white !important;
}

.mobile-menu .username {
  display: block;
  padding: 15px 20px;
  border-bottom: 1px solid rgba(255,255,255,0.1);
}

/* Mobile Responsive Styles */
@media (max-width: 768px) {
  /* Mobile Menu Toggle */
  .mobile-menu-toggle {
    display: block;
  }
  
  /* Hide desktop nav items but keep search visible */
  .navbar-container > div:first-child > li:not(:first-child):not(:nth-child(2)),
  .navbar-container > div:last-child > li:not(:last-child):not(.nav-search) {
    display: none;
  }
  
  /* Navbar adjustments */
  .navbar-container {
    height: 60px;
    padding: 0 10px;
  }
  
  .logo {
    height: 35px;
  }
  
  /* Show search in navbar on mobile */
  .nav-search {
    display: flex;
    margin-right: 10px;
  }
  
  .nav-search input[type="text"] {
    width: 120px;
    font-size: 12px;
    padding: 4px 6px;
  }
  
  .nav-search button {
    padding: 4px 8px;
    font-size: 12px;
  }
}

/* iPhone specific optimizations */
@media (max-width: 480px) {
  .navbar-container {
    height: 56px;
  }
  
  .logo {
    height: 32px;
  }
}

/* Landscape orientation on mobile */
@media (max-width: 768px) and (orientation: landscape) {
  .navbar-container {
    height: 50px;
  }
  
  .mobile-menu {
    top: 50px;
  }
}

/* iPhone X and newer - safe area support */
@supports (padding: max(0px)) {
  .navbar-container {
    padding-left: max(10px, env(safe-area-inset-left));
    padding-right: max(10px, env(safe-area-inset-right));
  }
}
</style>

<!-- The Nav Bar -->
<ul class="navbar-container">
  <div style="display: flex; align-items: center;">
    <li>
      <button class="mobile-menu-toggle" onclick="toggleMobileMenu()" aria-label="Toggle menu" aria-expanded="false">☰</button>
    </li>
    <li><a href="index.php#products"><img src="assets/images/Our logo.png" alt="Logo" class="logo"></a></li>
    <?php if(isset($_SESSION['username'])): ?>
      <li class="username">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></li>
    <?php endif; ?>
    <li><a href="products.php">Products</a></li>
    <li><a href="categories.php">Categories</a></li>
    <li><a href="cart.php">Cart</a></li>
    <li><a href="wishlist.php">❤️ Wishlist</a></li>
    <li><a href="about.php">About us</a></li>
    <li><a href="contact.php">Contact us</a></li>

  </div>
  <div style="display: flex; align-items: center;">
    <li class="nav-search">
      <form action="product.php" method="get" style="display:flex; align-items:center; gap:6px;">
        <input type="text" name="q" list="products-list" autocomplete="off" placeholder="Search products" value="<?php echo isset($_GET['q']) ? htmlspecialchars($_GET['q']) : ''; ?>">
        <button type="submit">Search</button>
      </form>
    </li>
    <?php if(isset($_SESSION['username'])): ?>
      <li><a href="logout.php" style="background-color: #dc3545; color: white;">Logout</a></li>
    <?php else: ?>
      <li><a href="login.php">Login</a></li>
    <?php endif; ?>
  </div>
</ul>

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
      <button type="submit" style="width: 100%; margin-top: 10px; padding: 12px; background: #cfa967; color: #2c3e50; border: none; border-radius: 4px; font-size: 16px; font-weight: 600; cursor: pointer; touch-action: manipulation;">Search</button>
    </form>
  </li>
  <?php if(isset($_SESSION['username'])): ?>
    <li><a href="logout.php" style="background-color: #dc3545; color: white;">Logout</a></li>
  <?php else: ?>
    <li><a href="login.html">Login</a></li>
  <?php endif; ?>
</ul>

<script src="js/mobile-menu.js"></script>

