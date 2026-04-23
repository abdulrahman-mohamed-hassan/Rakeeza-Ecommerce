<?php
/*
|--------------------------------------------------------------------------
| FILE: contact.php
|--------------------------------------------------------------------------
| PURPOSE: Contact page with company info and contact form
|
| DICTIONARY:
| -----------
| Lines 19-20  : Include session config
| Lines 22-184 : HTML HEAD and CSS STYLES (navbar, contact card, mobile)
| Lines 187-211 : NAVBAR (inline - should use navbar.php)
| Lines 213-238 : CONTACT INFORMATION (address, website, email, phone)
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
<title>Contact Us - Furniture Store</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
body {
  background: #f4ede2;
  margin: 0;
  padding-top: 60px;
  color: #333;
  font-family: Arial, sans-serif;
  position: relative;
}




li {
  display: flex;
  align-items: center;
  height: 100%;
}

li a {
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
}

li a:hover {
  background-color: #cfa967;
  color: #2c3e50;
}

.logo {
  height: 40px;
  width: auto;
  display: block;
}

.username {
  color: #ecf0f1;
  padding: 0 12px;
  font-size: 14px;
  font-weight: 500;
  white-space: nowrap;
}

/* Contact Card */
.contact-wrapper {
  max-width: 850px;
  margin: 40px auto;
  background: white;
  padding: 50px;
  border-radius: 20px;
  box-shadow: 0 20px 40px rgba(0,0,0,.12);
}

.contact-title {
  font-size: 40px;
  font-weight: bold;
  color: #2c3e50;
  text-align: center;
  margin-bottom: 15px;
}

.contact-text {
  text-align: center;
  color: #666666ff;
  margin-bottom: 40px;
  font-size: 17px;
}

.contact-item {
  display: flex;
  align-items: center;
  gap: 18px;
  padding: 15px;
  background: #f9f9f9;
  border-radius: 10px;
  transition: all 0.3s;
}

.contact-item:hover {
  background: #cfa967;
}

.contact-item-icon {
  width: 42px;
  height: 42px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #0d3b52ff;
  border-radius: 50%;
  font-size: 20px;
}

.contact-item span,
.contact-item a {
  font-size: 18px;
  color: #333;
  text-decoration: none;
}

.contact-item a:hover {
  color: #cfa967;
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

<main class="contact-wrapper">
  <div class="contact-title">Contact Us</div>
  <div class="contact-text">
    We'd love to hear from you. Reach out using the details below.
  </div>

  <div class="contact-item">
    <div class="contact-item-icon">📍</div>
    <span>64 orabi Street maadi</span>
  </div>

  <div class="contact-item">
    <div class="contact-item-icon">🌐</div>
    <a href="index.php" target="_blank">www.rakeeza.com</a>
  </div>

  <div class="contact-item">
    <div class="contact-item-icon">✉️</div>
    <a href="mailto:info@furniturestore.com">rakeeza@gmail.com</a>
  </div>

  <div class="contact-item">
    <div class="contact-item-icon">📞</div>
    <span>(20)112944557</span>
  </div>
</main>

<?php include 'footer.php'; ?>

</body>
</html>

