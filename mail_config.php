<?php
/**
 * Email Configuration
 * 
 * SETUP INSTRUCTIONS:
 * 1. Enable 2-Step Verification on your Gmail account
 * 2. Go to: https://myaccount.google.com/apppasswords
 * 3. Generate an "App Password" for "Mail"
 * 4. Use that 16-character password below (not your regular Gmail password)
 */

// SMTP Configuration - UPDATE THESE VALUES
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'abdulrahmanhassan20058@gmail.com');      // Your Gmail address
define('SMTP_PASSWORD', 'bdes tlkg ocrv visd');  // REPLACE with your 16-char App Password from Google
define('SMTP_FROM_EMAIL', 'abdulrahmanhassan20058@gmail.com');     // Same as username
define('SMTP_FROM_NAME', 'Rakezaa');

// Admin email to receive order notifications
define('ADMIN_EMAIL', 'abdulrahmanhassan20058@gmail.com');        // Admin email to receive order notifications

/**
 * Send email using PHPMailer or PHP mail()
 */
function sendEmail($to, $subject, $htmlBody, $plainBody = '') {
    // Check if PHPMailer exists
    $phpmailerPath = __DIR__ . '/PHPMailer/src/PHPMailer.php';
    
    if (file_exists($phpmailerPath)) {
        // Use PHPMailer
        require_once __DIR__ . '/PHPMailer/src/Exception.php';
        require_once __DIR__ . '/PHPMailer/src/PHPMailer.php';
        require_once __DIR__ . '/PHPMailer/src/SMTP.php';
        
        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
        
        try {
            $mail->isSMTP();
            $mail->Host = SMTP_HOST;
            $mail->SMTPAuth = true;
            $mail->Username = SMTP_USERNAME;
            $mail->Password = SMTP_PASSWORD;
            $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = SMTP_PORT;
            
            $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
            $mail->addAddress($to);
            
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $htmlBody;
            $mail->AltBody = $plainBody ?: strip_tags($htmlBody);
            
            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log("Email error: " . $mail->ErrorInfo);
            return false;
        }
    } else {
        // Fallback to PHP mail() - may not work on all servers
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html; charset=UTF-8\r\n";
        $headers .= "From: " . SMTP_FROM_NAME . " <" . SMTP_FROM_EMAIL . ">\r\n";
        
        return @mail($to, $subject, $htmlBody, $headers);
    }
}

/**
 * Generate OTP
 */
function generateOTP($length = 6) {
    return str_pad(random_int(0, pow(10, $length) - 1), $length, '0', STR_PAD_LEFT);
}

/**
 * Send OTP email
 */
function sendOTPEmail($email, $otp, $name = 'Customer') {
    $subject = "Your Verification Code - Furniture Store";
    
    $htmlBody = "
    <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;'>
        <div style='background: #2c3e50; padding: 20px; text-align: center; border-radius: 10px 10px 0 0;'>
            <h1 style='color: #cfa967; margin: 0;'>Furniture Store</h1>
        </div>
        <div style='background: #f4ede2; padding: 30px; border-radius: 0 0 10px 10px;'>
            <h2 style='color: #2c3e50;'>Email Verification</h2>
            <p style='color: #333;'>Hello $name,</p>
            <p style='color: #333;'>Your verification code is:</p>
            <div style='background: #2c3e50; color: #cfa967; font-size: 32px; font-weight: bold; padding: 20px; text-align: center; border-radius: 10px; letter-spacing: 8px; margin: 20px 0;'>
                $otp
            </div>
            <p style='color: #666; font-size: 14px;'>This code will expire in 10 minutes.</p>
            <p style='color: #666; font-size: 14px;'>If you didn't request this code, please ignore this email.</p>
        </div>
    </div>";
    
    return sendEmail($email, $subject, $htmlBody);
}

/**
 * Send order confirmation to customer
 */
function sendOrderConfirmation($email, $name, $orderId, $orderItems, $total, $shippingAddress, $city) {
    $subject = "Order Confirmed #$orderId - Furniture Store";
    
    $itemsHtml = '';
    foreach ($orderItems as $item) {
        $itemsHtml .= "
        <tr>
            <td style='padding: 10px; border-bottom: 1px solid #eee;'>{$item['name']}</td>
            <td style='padding: 10px; border-bottom: 1px solid #eee; text-align: center;'>{$item['quantity']}</td>
            <td style='padding: 10px; border-bottom: 1px solid #eee; text-align: right;'>EGP " . number_format($item['price']) . "</td>
            <td style='padding: 10px; border-bottom: 1px solid #eee; text-align: right;'>EGP " . number_format($item['subtotal']) . "</td>
        </tr>";
    }
    
    $htmlBody = "
    <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;'>
        <div style='background: #2c3e50; padding: 20px; text-align: center; border-radius: 10px 10px 0 0;'>
            <h1 style='color: #cfa967; margin: 0;'>Furniture Store</h1>
        </div>
        <div style='background: #f4ede2; padding: 30px; border-radius: 0 0 10px 10px;'>
            <div style='text-align: center; margin-bottom: 20px;'>
                <span style='font-size: 48px;'>✅</span>
                <h2 style='color: #27ae60; margin: 10px 0;'>Order Confirmed!</h2>
            </div>
            
            <p style='color: #333;'>Hello $name,</p>
            <p style='color: #333;'>Thank you for your order! We've received your order and will process it shortly.</p>
            
            <div style='background: #fff; padding: 15px; border-radius: 8px; margin: 20px 0;'>
                <p style='margin: 0; color: #666;'>Order Number:</p>
                <p style='margin: 5px 0; font-size: 24px; font-weight: bold; color: #cfa967;'>#$orderId</p>
            </div>
            
            <h3 style='color: #2c3e50; border-bottom: 2px solid #cfa967; padding-bottom: 10px;'>Order Details</h3>
            <table style='width: 100%; border-collapse: collapse;'>
                <thead>
                    <tr style='background: #2c3e50; color: #fff;'>
                        <th style='padding: 10px; text-align: left;'>Item</th>
                        <th style='padding: 10px; text-align: center;'>Qty</th>
                        <th style='padding: 10px; text-align: right;'>Price</th>
                        <th style='padding: 10px; text-align: right;'>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    $itemsHtml
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan='3' style='padding: 15px; text-align: right; font-weight: bold; color: #2c3e50;'>Total:</td>
                        <td style='padding: 15px; text-align: right; font-weight: bold; font-size: 18px; color: #cfa967;'>EGP " . number_format($total) . "</td>
                    </tr>
                </tfoot>
            </table>
            
            <h3 style='color: #2c3e50; border-bottom: 2px solid #cfa967; padding-bottom: 10px; margin-top: 30px;'>Shipping Address</h3>
            <p style='color: #333; margin: 10px 0;'>$shippingAddress</p>
            <p style='color: #333; margin: 10px 0;'>$city</p>
            
            <div style='background: #fff3cd; padding: 15px; border-radius: 8px; margin-top: 20px;'>
                <p style='margin: 0; color: #856404;'><strong>💵 Payment:</strong> Cash on Delivery</p>
                <p style='margin: 5px 0 0 0; color: #856404; font-size: 13px;'>Please have the exact amount ready when your order arrives.</p>
            </div>
            
            <p style='color: #666; font-size: 14px; margin-top: 30px; text-align: center;'>
                Questions? Reply to this email or contact us anytime.
            </p>
        </div>
    </div>";
    
    return sendEmail($email, $subject, $htmlBody);
}

/**
 * Send order notification to admin
 */
function sendAdminNotification($orderId, $customerName, $customerEmail, $customerPhone, $orderItems, $total, $shippingAddress, $city, $notes = '') {
    $subject = "🛒 New Order #$orderId - Furniture Store";
    
    $itemsHtml = '';
    foreach ($orderItems as $item) {
        $itemsHtml .= "<li>{$item['name']} × {$item['quantity']} = EGP " . number_format($item['subtotal']) . "</li>";
    }
    
    $notesHtml = $notes ? "<p style='color: #856404; background: #fff3cd; padding: 10px; border-radius: 5px;'><strong>📝 Notes:</strong> $notes</p>" : '';
    
    $htmlBody = "
    <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;'>
        <div style='background: #2c3e50; padding: 20px; text-align: center; border-radius: 10px 10px 0 0;'>
            <h1 style='color: #cfa967; margin: 0;'>🛒 New Order Received!</h1>
        </div>
        <div style='background: #d4edda; padding: 30px; border-radius: 0 0 10px 10px;'>
            <h2 style='color: #155724;'>Order #$orderId</h2>
            <p style='font-size: 24px; font-weight: bold; color: #cfa967;'>Total: EGP " . number_format($total) . "</p>
            
            <div style='background: #fff; padding: 20px; border-radius: 8px; margin: 20px 0;'>
                <h3 style='color: #2c3e50; margin-top: 0;'>👤 Customer Details</h3>
                <p style='margin: 5px 0;'><strong>Name:</strong> $customerName</p>
                <p style='margin: 5px 0;'><strong>Email:</strong> $customerEmail</p>
                <p style='margin: 5px 0;'><strong>Phone:</strong> $customerPhone</p>
                <p style='margin: 5px 0;'><strong>Address:</strong> $shippingAddress, $city</p>
                $notesHtml
            </div>
            
            <div style='background: #fff; padding: 20px; border-radius: 8px;'>
                <h3 style='color: #2c3e50; margin-top: 0;'>📦 Order Items</h3>
                <ul style='color: #333;'>$itemsHtml</ul>
            </div>
            
            <p style='margin-top: 20px; text-align: center;'>
                <a href='http://localhost/project12/admin.php' style='display: inline-block; background: #cfa967; color: #2c3e50; padding: 15px 30px; text-decoration: none; border-radius: 8px; font-weight: bold;'>View in Admin Panel</a>
            </p>
        </div>
    </div>";
    
    return sendEmail(ADMIN_EMAIL, $subject, $htmlBody);
}
?>


