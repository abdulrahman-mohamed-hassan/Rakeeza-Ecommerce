<?php
/*
|--------------------------------------------------------------------------
| FILE: checkout.php
|--------------------------------------------------------------------------
| PURPOSE: Checkout process with OTP verification and order placement
|
| DICTIONARY:
| -----------
| Lines 28-31  : Include session config, database, and mail config
| Lines 33-37  : Redirect if cart is empty
| Lines 39-46  : Fetch products from database
| Lines 48-65  : Calculate cart total and prepare items array
| Lines 67-73  : Initialize checkout variables (step, OTP status)
| Lines 75-91  : Ensure orders table has required shipping columns
| Lines 93-133 : Handle OTP sending (email verification, store in session)
| Lines 135-155: Handle OTP verification (check code and expiration)
| Lines 157-171: Handle Resend OTP
| Lines 173-235: Handle ORDER PLACEMENT (save to database, send emails, clear cart)
| Lines 237-242: Handle back button (return to form)
| Lines 246-694 : HTML HEAD and CSS STYLES (form, steps, order summary, mobile)
| Lines 696-732 : NAVBAR and MOBILE MENU (inline - should use navbar.php)
| Lines 734-746 : ORDER SUCCESS page (if order placed)
| Lines 748-929 : CHECKOUT FORM (3 steps: details, OTP verification, confirm order)
| Lines 931    : Include footer component
| Lines 932    : Mobile menu script (should be in navbar.php)
|--------------------------------------------------------------------------
*/

// Optimized session handling for concurrent users
require_once 'session_config.php';
include 'DBM.php';
include 'mail_config.php';

// Check if cart is empty
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    header("Location: cart.php");
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

// Calculate cart items and total
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

$order_placed = false;
$order_id = null;
$error = '';
$success = '';
$step = isset($_SESSION['checkout_step']) ? $_SESSION['checkout_step'] : 1;
$otp_sent = false;
$otp_verified = isset($_SESSION['otp_verified']) && $_SESSION['otp_verified'] === true;

// Ensure orders table has all required columns
$shipping_columns = [
    "shipping_name" => "VARCHAR(255)",
    "shipping_email" => "VARCHAR(255)", 
    "shipping_phone" => "VARCHAR(50)",
    "shipping_address" => "TEXT",
    "shipping_city" => "VARCHAR(100)",
    "notes" => "TEXT",
    "payment_method" => "VARCHAR(50) DEFAULT 'cash'"
];

foreach ($shipping_columns as $col_name => $col_type) {
    $check = $conn->query("SHOW COLUMNS FROM orders LIKE '$col_name'");
    if ($check && $check->num_rows == 0) {
        $conn->query("ALTER TABLE orders ADD COLUMN $col_name $col_type");
    }
}

// Handle Send OTP
if (isset($_POST['send_otp'])) {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $notes = trim($_POST['notes'] ?? '');
    
    if (empty($name) || empty($email) || empty($phone) || empty($address) || empty($city)) {
        $error = "Please fill in all required fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } else {
        // Generate OTP
        $otp = generateOTP(6);
        
        // Store in session
        $_SESSION['checkout_otp'] = $otp;
        $_SESSION['checkout_otp_time'] = time();
        $_SESSION['checkout_data'] = [
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'address' => $address,
            'city' => $city,
            'notes' => $notes
        ];
        $_SESSION['checkout_step'] = 2;
        $step = 2;
        
        // Send OTP email
        if (sendOTPEmail($email, $otp, $name)) {
            $success = "A verification code has been sent to your email.";
            $otp_sent = true;
        } else {
            $success = "Verification code generated. Check your email (or use code: $otp for testing).";
            $otp_sent = true;
        }
    }
}

// Handle Verify OTP
if (isset($_POST['verify_otp'])) {
    $entered_otp = trim($_POST['otp'] ?? '');
    $stored_otp = $_SESSION['checkout_otp'] ?? '';
    $otp_time = $_SESSION['checkout_otp_time'] ?? 0;
    
    // Check if OTP is expired (10 minutes)
    if (time() - $otp_time > 600) {
        $error = "OTP has expired. Please request a new one.";
        $_SESSION['checkout_step'] = 1;
        $step = 1;
    } elseif ($entered_otp === $stored_otp) {
        $_SESSION['otp_verified'] = true;
        $_SESSION['checkout_step'] = 3;
        $step = 3;
        $otp_verified = true;
        $success = "Email verified successfully! You can now place your order.";
    } else {
        $error = "Invalid verification code. Please try again.";
    }
}

// Handle Resend OTP
if (isset($_POST['resend_otp'])) {
    $checkout_data = $_SESSION['checkout_data'] ?? [];
    if (!empty($checkout_data['email'])) {
        $otp = generateOTP(6);
        $_SESSION['checkout_otp'] = $otp;
        $_SESSION['checkout_otp_time'] = time();
        
        if (sendOTPEmail($checkout_data['email'], $otp, $checkout_data['name'])) {
            $success = "A new verification code has been sent to your email.";
        } else {
            $success = "New code generated: $otp (for testing)";
        }
    }
}

// Handle Place Order (after OTP verification)
if (isset($_POST['place_order']) && $otp_verified) {
    $checkout_data = $_SESSION['checkout_data'] ?? [];
    $name = $checkout_data['name'] ?? '';
    $email = $checkout_data['email'] ?? '';
    $phone = $checkout_data['phone'] ?? '';
    $address = $checkout_data['address'] ?? '';
    $city = $checkout_data['city'] ?? '';
    $notes = $checkout_data['notes'] ?? '';
    $payment_method = 'cash';
    
    if (empty($name) || empty($email)) {
        $error = "Session expired. Please start again.";
        $_SESSION['checkout_step'] = 1;
        $step = 1;
    } else {
        // Get user_id if logged in, otherwise use 0 for guest
        $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;
        
        // Create order
        $stmt = $conn->prepare("INSERT INTO orders (user_id, total_amount, status, shipping_name, shipping_email, shipping_phone, shipping_address, shipping_city, notes, payment_method) VALUES (?, ?, 'pending', ?, ?, ?, ?, ?, ?, ?)");
        
        if ($stmt) {
            $stmt->bind_param("idsssssss", $user_id, $total, $name, $email, $phone, $address, $city, $notes, $payment_method);
            
            if ($stmt->execute()) {
                $order_id = $conn->insert_id;
                
                // Add order items
                $stmt_items = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
                
                if ($stmt_items) {
                    foreach ($cart_items as $item) {
                        $stmt_items->bind_param("iiid", $order_id, $item['id'], $item['quantity'], $item['price']);
                        $stmt_items->execute();
                    }
                    $stmt_items->close();
                }
                
                // Send confirmation email to customer
                sendOrderConfirmation($email, $name, $order_id, $cart_items, $total, $address, $city);
                
                // Send notification to admin
                sendAdminNotification($order_id, $name, $email, $phone, $cart_items, $total, $address, $city, $notes);
                
                // Clear cart and checkout session
                $_SESSION['cart'] = [];
                unset($_SESSION['checkout_otp']);
                unset($_SESSION['checkout_otp_time']);
                unset($_SESSION['checkout_data']);
                unset($_SESSION['checkout_step']);
                unset($_SESSION['otp_verified']);
                
                $order_placed = true;
            } else {
                $error = "Error placing order. Please try again.";
            }
            $stmt->close();
        } else {
            $error = "Database error: " . $conn->error;
        }
    }
}

// Handle back button
if (isset($_POST['back_to_form'])) {
    $_SESSION['checkout_step'] = 1;
    $step = 1;
    unset($_SESSION['otp_verified']);
}

$checkout_data = $_SESSION['checkout_data'] ?? [];
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
    <title>Checkout - Furniture Store</title>
    <style type="text/css">
        * { margin: 0; padding: 0; box-sizing: border-box; }
        html, body { width: 100%; }
        
        ul {
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
            min-height: 44px; /* iOS touch target minimum */
            -webkit-tap-highlight-color: rgba(207, 169, 103, 0.3);
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
        
        body {
            background: #f4ede2;
            margin: 0;
            margin-top: 60px;
            font-family: Arial, sans-serif;
            width: 100%;
            position: relative;
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
            background-attachment: fixed;
            background-repeat: no-repeat;
            filter: blur(3px);
            -webkit-filter: blur(3px);
            z-index: -1;
        }
        
        .container {
            max-width: 1200px;
            margin: 30px auto 50px;
            padding: 30px;
            display: flex;
            gap: 30px;
            flex-wrap: wrap;
        }
        
        .checkout-form {
            flex: 1;
            min-width: 350px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            padding: 30px;
        }
        
        .order-summary {
            width: 380px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            padding: 30px;
            height: fit-content;
        }
        
        h1 { font-size: 28px; color: #2c3e50; margin-bottom: 25px; }
        h2 { font-size: 22px; color: #2c3e50; margin-bottom: 20px; }
        
        .form-group { margin-bottom: 20px; }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #2c3e50;
            font-weight: 500;
            font-size: 14px;
        }
        
        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 16px; /* Prevents zoom on iOS */
            font-family: Arial, sans-serif;
            transition: border-color 0.3s, box-shadow 0.3s;
            min-height: 44px;
        }
        
        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #cfa967;
            box-shadow: 0 0 0 3px rgba(207, 169, 103, 0.2);
        }
        
        .form-group textarea { resize: vertical; min-height: 80px; }
        .form-row { display: flex; gap: 15px; }
        .form-row .form-group { flex: 1; }
        
        /* Steps indicator */
        .steps {
            display: flex;
            justify-content: center;
            margin-bottom: 30px;
            gap: 10px;
        }
        
        .step {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            border-radius: 25px;
            background: #eee;
            color: #666;
            font-size: 14px;
        }
        
        .step.active {
            background: #cfa967;
            color: #2c3e50;
            font-weight: bold;
        }
        
        .step.completed {
            background: #27ae60;
            color: white;
        }
        
        .step-number {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            background: rgba(0,0,0,0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
        }
        
        .step.active .step-number,
        .step.completed .step-number {
            background: rgba(255,255,255,0.3);
        }
        
        /* OTP Section */
        .otp-section {
            text-align: center;
            padding: 30px;
        }
        
        .otp-icon { font-size: 60px; margin-bottom: 20px; }
        
        .otp-input {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin: 30px 0;
        }
        
        .otp-input input {
            width: 50px;
            height: 60px;
            text-align: center;
            font-size: 24px;
            font-weight: bold;
            border: 2px solid #ddd;
            border-radius: 10px;
        }
        
        .otp-input input:focus {
            border-color: #cfa967;
            outline: none;
        }
        
        .otp-full-input {
            width: 200px !important;
            text-align: center;
            font-size: 24px !important;
            letter-spacing: 8px;
            margin: 0 auto;
        }
        
        .resend-link {
            color: #cfa967;
            cursor: pointer;
            text-decoration: underline;
            background: none;
            border: none;
            font-size: 14px;
        }
        
        .resend-link:hover { color: #b58956; }
        
        /* Payment Methods */
        .payment-methods { margin-bottom: 25px; }
        
        .payment-option {
            display: flex;
            align-items: center;
            padding: 15px;
            border: 2px solid #ddd;
            border-radius: 10px;
            margin-bottom: 10px;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .payment-option:hover { border-color: #cfa967; }
        .payment-option.selected {
            border-color: #cfa967;
            background: rgba(207, 169, 103, 0.1);
        }
        
        .payment-option input[type="radio"] {
            width: 20px;
            height: 20px;
            margin-right: 15px;
            accent-color: #cfa967;
        }
        
        .payment-option .payment-icon { font-size: 24px; margin-right: 15px; }
        .payment-option .payment-details h4 { color: #2c3e50; font-size: 16px; margin-bottom: 3px; }
        .payment-option .payment-details p { color: #666; font-size: 13px; }
        
        /* Order Items */
        .order-item {
            display: flex;
            gap: 15px;
            padding: 15px 0;
            border-bottom: 1px solid #eee;
        }
        
        .order-item img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 8px;
        }
        
        .order-item-details { flex: 1; }
        .order-item-details h4 { font-size: 14px; color: #333; margin-bottom: 5px; }
        .order-item-details p { font-size: 13px; color: #666; }
        .order-item-price { font-weight: bold; color: #cfa967; }
        
        .order-totals { margin-top: 20px; padding-top: 20px; border-top: 2px solid #eee; }
        .order-totals .row { display: flex; justify-content: space-between; margin-bottom: 10px; font-size: 14px; }
        .order-totals .row.total {
            font-size: 20px;
            font-weight: bold;
            color: #2c3e50;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 2px solid #cfa967;
        }
        .order-totals .row.total span:last-child { color: #cfa967; }
        
        .btn {
            width: 100%;
            padding: 16px;
            font-size: 16px;
            font-weight: bold;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            transition: background 0.3s;
            margin-top: 15px;
        }
        
        .btn-primary { background: #cfa967; color: white; }
        .btn-primary:hover { background: #b58956; }
        .btn-secondary { background: #2c3e50; color: white; }
        .btn-secondary:hover { background: #1a252f; }
        
        .error-message {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .success-message {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        /* Success Page */
        .success-container {
            max-width: 600px;
            margin: 50px auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            padding: 50px;
            text-align: center;
        }
        
        .success-icon { font-size: 80px; margin-bottom: 20px; }
        .success-container h1 { color: #27ae60; margin-bottom: 15px; }
        .success-container p { color: #666; font-size: 16px; line-height: 1.6; margin-bottom: 10px; }
        
        .order-number {
            background: #f4ede2;
            padding: 15px 30px;
            border-radius: 8px;
            display: inline-block;
            margin: 20px 0;
        }
        
        .order-number span { font-size: 24px; font-weight: bold; color: #cfa967; }
        
        .continue-btn {
            display: inline-block;
            background: #2c3e50;
            color: white;
            padding: 15px 40px;
            font-size: 16px;
            text-decoration: none;
            border-radius: 8px;
            margin-top: 20px;
            transition: background 0.3s;
        }
        
        .continue-btn:hover { background: #1a252f; }
        
        /* Verified badge */
        .verified-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            background: #d4edda;
            color: #155724;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 13px;
            margin-left: 10px;
        }
        
        footer { background: #1e1e1e; color: #fff; text-align: center; padding: 40px 20px; margin-top: auto; padding-bottom: calc(40px + env(safe-area-inset-bottom)); }
        
        /* Mobile Responsive */
        @media (max-width: 768px) {
            .container {
                flex-direction: column;
                padding: 20px 15px;
            }
            .checkout-form, .order-summary {
                width: 100%;
                min-width: 100%;
            }
            .form-row {
                flex-direction: column;
            }
            .steps {
                flex-wrap: wrap;
                gap: 5px;
            }
            .step {
                font-size: 12px;
                padding: 8px 15px;
            }
            .otp-input input {
                width: 40px;
                height: 50px;
                font-size: 20px;
            }
            .btn {
                width: 100%;
                min-height: 50px;
                font-size: 16px;
            }
        }
        
        @media (max-width: 480px) {
            h1 { font-size: 24px; }
            h2 { font-size: 20px; }
            .checkout-form, .order-summary {
                padding: 20px 15px;
            }
            .otp-input input {
                width: 35px;
                height: 45px;
                font-size: 18px;
            }
        }
    </style>
</head>
<body>
<div class="bg-blur"></div>

<ul class="navbar">
    <div style="display: flex; align-items: center;">
        <li>
            <button class="mobile-menu-toggle" onclick="toggleMobileMenu()" aria-label="Toggle menu" aria-expanded="false">☰</button>
        </li>
        <li><a href="index.php"><img src="assets/images/Our logo.png" alt="Logo" class="logo"></a></li>
        <?php if(isset($_SESSION['username'])): ?>
            <li style="color:#ecf0f1; padding:0 12px; font-size:14px;">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></li>
        <?php endif; ?>
        <li><a href="products.php">Products</a></li>
        <li><a href="categories.php">Categories</a></li>
        <li><a href="cart.php">Cart</a></li>
        <li><a href="wishlist.php">❤️ Wishlist</a></li>
        <li><a href="about.php">About us</a></li>
    </div>
    <div style="display: flex; align-items: center;">
        <li><a href="contact.php">Contact us</a></li>
        <li><a href="logout.php" style="background-color: #dc3545; color: white;">Logout</a></li>
    </div>
</ul>

<!-- Mobile Menu -->
<ul class="mobile-menu" id="mobileMenu">
    <?php if(isset($_SESSION['username'])): ?>
        <li style="color:#ecf0f1; padding:15px 20px; border-bottom:1px solid rgba(255,255,255,0.1);">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></li>
    <?php endif; ?>
    <li><a href="products.php">Products</a></li>
    <li><a href="categories.php">Categories</a></li>
    <li><a href="cart.php">🛒 Cart</a></li>
    <li><a href="wishlist.php">❤️ Wishlist</a></li>
    <li><a href="about.php">About us</a></li>
    <li><a href="contact.php">Contact us</a></li>
    <li><a href="logout.php" style="background-color: #dc3545; color: white;">Logout</a></li>
</ul>

<?php if ($order_placed): ?>
<!-- Order Success -->
<div class="success-container">
    <div class="success-icon">✅</div>
    <h1>Order Placed Successfully!</h1>
    <p>Thank you for your order. We've received your request and will process it shortly.</p>
    <p>A confirmation email has been sent to your email address.</p>
    <div class="order-number">
        Order Number: <span>#<?php echo $order_id; ?></span>
    </div>
    <p><strong>Payment Method:</strong> Cash on Delivery 💵</p>
    <a href="index.php" class="continue-btn">Continue Shopping</a>
</div>

<?php else: ?>
<!-- Checkout Form -->
<div class="container">
    <div class="checkout-form">
        <!-- Steps Indicator -->
        <div class="steps">
            <div class="step <?php echo $step >= 1 ? ($step > 1 ? 'completed' : 'active') : ''; ?>">
                <span class="step-number"><?php echo $step > 1 ? '✓' : '1'; ?></span>
                Details
            </div>
            <div class="step <?php echo $step >= 2 ? ($step > 2 ? 'completed' : 'active') : ''; ?>">
                <span class="step-number"><?php echo $step > 2 ? '✓' : '2'; ?></span>
                Verify Email
            </div>
            <div class="step <?php echo $step >= 3 ? 'active' : ''; ?>">
                <span class="step-number">3</span>
                Place Order
            </div>
        </div>
        
        <?php if ($error): ?>
            <div class="error-message"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="success-message"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <?php if ($step == 1): ?>
        <!-- Step 1: Shipping Details -->
        <h1>🚚 Shipping Details</h1>
        
        <form method="POST" action="checkout.php">
            <div class="form-group">
                <label>Full Name *</label>
                <input type="text" name="name" required value="<?php echo htmlspecialchars($checkout_data['name'] ?? $_SESSION['username'] ?? ''); ?>">
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Email * (for verification)</label>
                    <input type="email" name="email" required value="<?php echo htmlspecialchars($checkout_data['email'] ?? $_SESSION['email'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label>Phone Number *</label>
                    <input type="tel" name="phone" required value="<?php echo htmlspecialchars($checkout_data['phone'] ?? ''); ?>">
                </div>
            </div>
            
            <div class="form-group">
                <label>Delivery Address *</label>
                <textarea name="address" required placeholder="Street address, building number, apartment..."><?php echo htmlspecialchars($checkout_data['address'] ?? ''); ?></textarea>
            </div>
            
            <div class="form-group">
                <label>City *</label>
                <input type="text" name="city" required value="<?php echo htmlspecialchars($checkout_data['city'] ?? ''); ?>">
            </div>
            
            <div class="form-group">
                <label>Order Notes (Optional)</label>
                <textarea name="notes" placeholder="Any special instructions for delivery..."><?php echo htmlspecialchars($checkout_data['notes'] ?? ''); ?></textarea>
            </div>
            
            <button type="submit" name="send_otp" class="btn btn-primary">
                Continue & Verify Email →
            </button>
        </form>
        
        <?php elseif ($step == 2): ?>
        <!-- Step 2: OTP Verification -->
        <div class="otp-section">
            <div class="otp-icon">📧</div>
            <h1>Verify Your Email</h1>
            <p style="color:#666; margin-bottom:10px;">We've sent a 6-digit verification code to:</p>
            <p style="color:#cfa967; font-weight:bold; font-size:18px;"><?php echo htmlspecialchars($checkout_data['email'] ?? ''); ?></p>
            
            <form method="POST" action="checkout.php">
                <div class="form-group" style="margin-top:30px;">
                    <label>Enter Verification Code</label>
                    <input type="text" name="otp" class="otp-full-input" maxlength="6" pattern="[0-9]{6}" required placeholder="000000" autofocus>
                </div>
                
                <button type="submit" name="verify_otp" class="btn btn-primary">
                    Verify Code ✓
                </button>
            </form>
            
            <p style="margin-top:20px; color:#666; font-size:14px;">
                Didn't receive the code? 
                <form method="POST" style="display:inline;">
                    <button type="submit" name="resend_otp" class="resend-link">Resend Code</button>
                </form>
            </p>
            
            <form method="POST" style="margin-top:15px;">
                <button type="submit" name="back_to_form" class="btn btn-secondary" style="width:auto; padding:10px 20px;">
                    ← Back to Edit Details
                </button>
            </form>
        </div>
        
        <?php elseif ($step == 3 && $otp_verified): ?>
        <!-- Step 3: Confirm & Place Order -->
        <h1>✅ Confirm Your Order</h1>
        
        <div style="background:#d4edda; padding:15px; border-radius:10px; margin-bottom:20px;">
            <p style="color:#155724; margin:0;">
                <strong>✓ Email Verified:</strong> <?php echo htmlspecialchars($checkout_data['email'] ?? ''); ?>
            </p>
        </div>
        
        <div style="background:#f9f9f9; padding:20px; border-radius:10px; margin-bottom:20px;">
            <h3 style="color:#2c3e50; margin-bottom:15px;">📦 Shipping To:</h3>
            <p style="margin:5px 0;"><strong><?php echo htmlspecialchars($checkout_data['name'] ?? ''); ?></strong></p>
            <p style="margin:5px 0; color:#666;"><?php echo htmlspecialchars($checkout_data['phone'] ?? ''); ?></p>
            <p style="margin:5px 0; color:#666;"><?php echo htmlspecialchars($checkout_data['address'] ?? ''); ?></p>
            <p style="margin:5px 0; color:#666;"><?php echo htmlspecialchars($checkout_data['city'] ?? ''); ?></p>
            <?php if (!empty($checkout_data['notes'])): ?>
                <p style="margin:10px 0 0 0; color:#856404; font-size:13px;">📝 <?php echo htmlspecialchars($checkout_data['notes']); ?></p>
            <?php endif; ?>
        </div>
        
        <h2 style="margin-top:30px;">💳 Payment Method</h2>
        <div class="payment-methods">
            <label class="payment-option selected">
                <input type="radio" name="payment_method" value="cash" checked>
                <span class="payment-icon">💵</span>
                <div class="payment-details">
                    <h4>Cash on Delivery</h4>
                    <p>Pay when you receive your order</p>
                </div>
            </label>
        </div>
        
        <form method="POST" action="checkout.php">
            <button type="submit" name="place_order" class="btn btn-primary" style="font-size:18px;">
                🛒 Place Order - EGP <?php echo number_format($total); ?>
            </button>
        </form>
        
        <form method="POST" style="margin-top:10px;">
            <button type="submit" name="back_to_form" class="btn btn-secondary">
                ← Back to Edit Details
            </button>
        </form>
        <?php endif; ?>
    </div>
    
    <div class="order-summary">
        <h2>📦 Order Summary</h2>
        
        <?php foreach ($cart_items as $item): ?>
        <div class="order-item">
            <img src="<?php echo htmlspecialchars($item['img']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
            <div class="order-item-details">
                <h4><?php echo htmlspecialchars($item['name']); ?></h4>
                <p>Qty: <?php echo $item['quantity']; ?> × EGP <?php echo number_format($item['price']); ?></p>
            </div>
            <div class="order-item-price">
                EGP <?php echo number_format($item['subtotal']); ?>
            </div>
        </div>
        <?php endforeach; ?>
        
        <div class="order-totals">
            <div class="row">
                <span>Subtotal</span>
                <span>EGP <?php echo number_format($total); ?></span>
            </div>
            <div class="row">
                <span>Shipping</span>
                <span style="color:#27ae60;">Free</span>
            </div>
            <div class="row total">
                <span>Total</span>
                <span>EGP <?php echo number_format($total); ?></span>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php include 'footer.php'; ?>
<script src="mobile-menu.js"></script>
<?php $conn->close(); ?>
</body>
</html>
