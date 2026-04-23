<?php
/*
|--------------------------------------------------------------------------
| FILE: about.php
|--------------------------------------------------------------------------
| PURPOSE: About Us page with company story and team info
|
| DICTIONARY:
| -----------
| Lines 19-20  : Include session config
| Lines 22-172 : HTML HEAD and CSS STYLES (navbar, about section, mobile)
| Lines 175-199 : NAVBAR (inline - should use navbar.php)
| Lines 201-238 : ABOUT CONTENT section (company story and image)
| Lines 240    : Include footer component
|--------------------------------------------------------------------------
*/

// Optimized session handling for concurrent users
require_once 'session_config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>About Us - Furniture Store</title>

<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
body {
  background: #f4ede2;
  font-family: Arial, sans-serif;
  margin: 0;
  padding-top: 60px;
  color: #333;
  position: relative;
}


/* About Section */
.about-section {
  max-width: 1200px;
  margin: 40px auto;
  background: #fff;
  padding: 60px;
  border-radius: 18px;
  box-shadow: 0 10px 30px rgba(0,0,0,0.1);
}

.about-content {
  display: flex;
  gap: 50px;
  align-items: center;
  flex-wrap: wrap;
}

.about-text {
  flex: 1;
  min-width: 300px;
}

.about-text h1 {
  font-size: 52px;
  font-weight: bold;
  color: #cfa967;
  margin-bottom: 25px;
}

.about-text p {
  font-size: 18px;
  line-height: 1.9;
  margin-bottom: 18px;
  color: #555;
}

.about-image {
  flex: 1;
  min-width: 300px;
}

.about-image img {
  width: 550px;
  border-radius: 15px;
  background: #1b6c61ff;
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

<!-- Navbar -->
<?php include 'navbar.php'; ?>

<!-- About Content -->
<div class="about-section">
  <div class="about-content">

    <!-- LEFT TEXT -->
    <div class="about-text">
      <h1>About Us</h1>

      <p>
        Our furniture store was founded with a simple vision: to create high-quality furniture
        that combines comfort, durability, and modern design.
      </p>

      <p>
        Since our establishment, we have grown into a passionate team of dedicated
        professionals who share the same goal — delivering furniture
        that truly feels like home.
      </p>

      <p>
        We carefully source our raw materials from some of the best suppliers
        around the world, ensuring premium quality, strength, and long-lasting beauty
        in every piece we create.
      </p>

      <p>
        At our store, we believe your home should reflect your personality —
        <strong>Your home, your way.</strong>
      </p>
    </div>

    <!-- IMAGE -->
    <div class="about-image">
      <img src="assets/images/WhatsApp Image 2025-12-07 at 4.50.21 PM.jpeg" alt="About Us">
    </div>

  </div>
</div>

<?php include 'footer.php'; ?>

</body>
</html>

