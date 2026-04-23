<?php
/*
||--------------------------------------------------------------------------
|| FILE: qr_login.php
||--------------------------------------------------------------------------
|| PURPOSE: Generates a QR code for the login page that updates when IP changes
||          Also displays the WiFi network name that devices should connect to
||
|| DICTIONARY:
|| -----------
|| Lines 15-30  : Get current IP address from server
|| Lines 32-50  : Get WiFi SSID (network name) using Windows commands
|| Lines 52-55  : Build login URL with current IP
|| Lines 57-400 : HTML/CSS for QR code display page
|| Lines 402-450: JavaScript for QR code generation and auto-refresh
||--------------------------------------------------------------------------
*/

// Get the current IP address
function getCurrentIP() {
    // Try multiple methods to get the IP
    $ip = null;
    
    // Method 1: From $_SERVER
    if (!empty($_SERVER['SERVER_ADDR']) && $_SERVER['SERVER_ADDR'] !== '127.0.0.1') {
        $ip = $_SERVER['SERVER_ADDR'];
    }
    
    // Method 2: From HTTP_HOST (if accessing via IP)
    if (empty($ip) && !empty($_SERVER['HTTP_HOST'])) {
        $host = $_SERVER['HTTP_HOST'];
        if (filter_var($host, FILTER_VALIDATE_IP)) {
            $ip = $host;
        } else {
            // Extract IP from hostname if it contains IP
            if (preg_match('/\d+\.\d+\.\d+\.\d+/', $host, $matches)) {
                $ip = $matches[0];
            }
        }
    }
    
    // Method 3: Execute ipconfig command (Windows)
    if (empty($ip) || $ip === '127.0.0.1' || $ip === '::1') {
        $output = [];
        $return_var = 0;
        exec('ipconfig | findstr /i "IPv4" | findstr /v "127.0.0.1"', $output, $return_var);
        if (!empty($output)) {
            foreach ($output as $line) {
                if (preg_match('/\d+\.\d+\.\d+\.\d+/', $line, $matches)) {
                    $ip = $matches[0];
                    break;
                }
            }
        }
    }
    
    // Fallback to localhost if nothing found
    return $ip ?: 'localhost';
}

// Get WiFi SSID (network name)
function getWiFiSSID() {
    $ssid = null;
    
    // Windows: Get current connected WiFi network
    $output = [];
    exec('netsh wlan show interfaces', $output, $return_var);
    
    if (!empty($output)) {
        foreach ($output as $line) {
            if (stripos($line, 'SSID') !== false && stripos($line, 'BSSID') === false) {
                // Extract SSID value
                if (preg_match('/SSID\s*:\s*(.+)/i', $line, $matches)) {
                    $ssid = trim($matches[1]);
                    break;
                }
            }
        }
    }
    
    return $ssid ?: 'Not Available';
}

// Handle AJAX IP check request FIRST (before any HTML output)
if (isset($_GET['check_ip'])) {
    header('Content-Type: application/json');
    echo json_encode(['ip' => getCurrentIP()]);
    exit;
}

// Get current values
require_once 'session_config.php';
$currentIP = getCurrentIP();
$wifiSSID = getWiFiSSID();
$loginURL = "http://{$currentIP}/project12/login.html";

// Store current IP in session to detect changes
$previousIP = $_SESSION['qr_last_ip'] ?? null;
$_SESSION['qr_last_ip'] = $currentIP;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes, viewport-fit=cover">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="format-detection" content="telephone=no">
    <title>QR Code - Login Access</title>
    <link rel="stylesheet" href="css/mobile-fixes.css">
    <script src="https://cdn.jsdelivr.net/npm/qrcode@1.5.3/build/qrcode.min.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f4ede2;
            min-height: 100vh;
            padding-top: 60px;
            padding-bottom: 40px;
            position: relative;
            overscroll-behavior: none;
            touch-action: pan-y;
        }
        

        @media (max-width: 768px) {
            .bg-blur {
                position: absolute;
                height: 100vh;
                min-height: 100%;
                background-attachment: scroll !important;
            }
        }
        
        .container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.12);
            padding: 40px;
            max-width: 500px;
            width: 100%;
            margin: 40px auto;
            text-align: center;
            position: relative;
        }
        
        h1 {
            color: #2c3e50;
            margin-bottom: 10px;
            font-size: 28px;
            font-weight: 700;
        }
        
        .subtitle {
            color: #7f8c8d;
            margin-bottom: 30px;
            font-size: 14px;
        }
        
        .qr-container {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 30px;
            margin: 30px 0;
            display: inline-block;
            border: 2px solid #e9ecef;
            transition: all 0.3s ease;
        }
        
        .qr-container:hover {
            border-color: #cfa967;
            box-shadow: 0 10px 30px rgba(207, 169, 103, 0.2);
        }
        
        #qrcode {
            display: inline-block;
            background: white;
            padding: 10px;
            border-radius: 10px;
        }
        
        .info-box {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin: 20px 0;
            text-align: left;
        }
        
        .info-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid #e9ecef;
        }
        
        .info-item:last-child {
            border-bottom: none;
        }
        
        .info-label {
            font-weight: 600;
            color: #2c3e50;
            font-size: 14px;
        }
        
        .info-value {
            color: #cfa967;
            font-weight: 700;
            font-size: 14px;
            word-break: break-all;
            text-align: right;
            flex: 1;
            margin-left: 15px;
        }
        
        .wifi-info {
            background: linear-gradient(135deg, #cfa967 0%, #b58956 100%);
            color: white;
            border-radius: 10px;
            padding: 15px;
            margin: 20px 0;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        
        .wifi-icon {
            font-size: 24px;
        }
        
        .wifi-text {
            font-weight: 600;
            font-size: 16px;
        }
        
        .url-display {
            background: #2c3e50;
            color: #ecf0f1;
            border-radius: 8px;
            padding: 15px;
            margin: 20px 0;
            font-family: 'Courier New', monospace;
            font-size: 13px;
            word-break: break-all;
            cursor: pointer;
            transition: background 0.3s ease;
        }
        
        .url-display:hover {
            background: #34495e;
        }
        
        .url-display:active {
            background: #1a252f;
        }
        
        .status-indicator {
            display: inline-block;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: #27ae60;
            margin-right: 8px;
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
        
        .refresh-info {
            color: #7f8c8d;
            font-size: 12px;
            margin-top: 20px;
            font-style: italic;
        }
        
        .instructions {
            background: #fff3cd;
            border-left: 4px solid #cfa967;
            border-radius: 5px;
            padding: 15px;
            margin: 20px 0;
            text-align: left;
        }
        
        .instructions h3 {
            color: #856404;
            margin-bottom: 10px;
            font-size: 16px;
        }
        
        .instructions ol {
            margin-left: 20px;
            color: #856404;
            font-size: 14px;
            line-height: 1.8;
        }
        
        .instructions li {
            margin-bottom: 8px;
        }
        
        .copy-btn {
            background: #cfa967;
            color: #2c3e50;
            border: none;
            border-radius: 8px;
            padding: 12px 24px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 10px;
            transition: all 0.3s ease;
            min-height: 44px;
            touch-action: manipulation;
        }
        
        .copy-btn:hover {
            background: #b58956;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(207, 169, 103, 0.3);
        }
        
        .copy-btn:active {
            transform: translateY(0);
        }
        
        .copy-btn.copied {
            background: #27ae60;
            color: white;
        }
        
        @media (max-width: 768px) {
            body {
                padding-top: 70px;
                padding-left: 12px;
                padding-right: 12px;
            }
            
            .container {
                padding: 30px 20px;
                margin: 20px auto;
            }
            
            h1 {
                font-size: 24px;
            }
            
            .qr-container {
                padding: 20px;
            }
            
            .info-value {
                font-size: 12px;
            }
        }
    </style>
</head>
<body>
<div class="bg-blur"></div>
<?php include 'navbar.php'; ?>

    <div class="container">
        <h1>📱 Quick Login Access</h1>
        <p class="subtitle">Scan this QR code with your phone to access the login page</p>
        
        <div class="qr-container">
            <canvas id="qrcode" style="display: none;"></canvas>
            <img id="qrcode-img" src="https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=<?php echo urlencode($loginURL); ?>" alt="QR Code" style="max-width: 250px; height: auto; display: block; margin: 0 auto;">
            <div id="qr-loading" style="display: none; color: #667eea; font-size: 14px;">Loading QR code...</div>
        </div>
        
        <div class="wifi-info">
            <span class="wifi-icon">📶</span>
            <span class="wifi-text">Connect to: <strong><?php echo htmlspecialchars($wifiSSID); ?></strong></span>
        </div>
        
        <div class="info-box">
            <div class="info-item">
                <span class="info-label">IP Address:</span>
                <span class="info-value" id="ipDisplay"><?php echo htmlspecialchars($currentIP); ?></span>
            </div>
            <div class="info-item">
                <span class="info-label">Status:</span>
                <span class="info-value">
                    <span class="status-indicator"></span>
                    Active
                </span>
            </div>
        </div>
        
        <div class="url-display" id="urlDisplay" onclick="copyToClipboard()">
            <?php echo htmlspecialchars($loginURL); ?>
        </div>
        
        <button class="copy-btn" onclick="copyToClipboard()" id="copyBtn">
            📋 Copy URL
        </button>
        
        <div class="instructions">
            <h3>📖 How to Use:</h3>
            <ol>
                <li>Make sure your phone is connected to <strong><?php echo htmlspecialchars($wifiSSID); ?></strong></li>
                <li>Open your phone's camera or QR code scanner</li>
                <li>Point it at the QR code above</li>
                <li>Tap the notification to open the login page</li>
            </ol>
        </div>
        
        <p class="refresh-info">
            This page automatically updates when your IP address changes
        </p>
    </div>
    
<?php include 'footer.php'; ?>
    
    <script>
        // Generate QR code
        const loginURL = <?php echo json_encode($loginURL); ?>;
        const currentIP = <?php echo json_encode($currentIP); ?>;
        
        function generateQRCode() {
            const qrCanvas = document.getElementById('qrcode');
            const qrImg = document.getElementById('qrcode-img');
            const qrLoading = document.getElementById('qr-loading');
            
            // Hide loading message
            if (qrLoading) qrLoading.style.display = 'none';
            
            // Try using QRCode library first
            if (typeof QRCode !== 'undefined') {
                try {
                    QRCode.toCanvas(qrCanvas, loginURL, {
                        width: 250,
                        margin: 2,
                        color: {
                            dark: '#2c3e50',
                            light: '#ffffff'
                        },
                        errorCorrectionLevel: 'M'
                    }, function (error) {
                        if (error) {
                            console.error('QR Code library error:', error);
                            // Fallback to API
                            useAPIQRCode();
                        } else {
                            if (qrCanvas) qrCanvas.style.display = 'block';
                            if (qrImg) qrImg.style.display = 'none';
                            console.log('QR Code generated successfully');
                        }
                    });
                } catch (e) {
                    console.error('QR Code generation exception:', e);
                    useAPIQRCode();
                }
            } else {
                // Library not loaded, use API
                console.log('QRCode library not available, using API');
                useAPIQRCode();
            }
        }
        
        function useAPIQRCode() {
            const qrCanvas = document.getElementById('qrcode');
            const qrImg = document.getElementById('qrcode-img');
            const qrLoading = document.getElementById('qr-loading');
            
            if (qrLoading) qrLoading.style.display = 'none';
            
            // Use QR code API as fallback
            if (qrImg) {
                qrImg.src = 'https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=' + encodeURIComponent(loginURL);
                qrImg.onload = function() {
                    qrImg.style.display = 'block';
                    if (qrCanvas) qrCanvas.style.display = 'none';
                    console.log('QR Code loaded from API');
                };
                qrImg.onerror = function() {
                    console.error('Failed to load QR code from API');
                    if (qrLoading) {
                        qrLoading.innerHTML = 'Error loading QR code. Please refresh the page.';
                        qrLoading.style.display = 'block';
                        qrLoading.style.color = 'red';
                    }
                };
            } else {
                console.error('QR code image element not found');
            }
        }
        
        // Copy URL to clipboard
        function copyToClipboard() {
            const url = loginURL;
            const copyBtn = document.getElementById('copyBtn');
            
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(url).then(function() {
                    copyBtn.textContent = '✅ Copied!';
                    copyBtn.classList.add('copied');
                    setTimeout(function() {
                        copyBtn.textContent = '📋 Copy URL';
                        copyBtn.classList.remove('copied');
                    }, 2000);
                }).catch(function(err) {
                    console.error('Failed to copy:', err);
                    fallbackCopy(url);
                });
            } else {
                fallbackCopy(url);
            }
        }
        
        function fallbackCopy(text) {
            const textArea = document.createElement('textarea');
            textArea.value = text;
            textArea.style.position = 'fixed';
            textArea.style.opacity = '0';
            document.body.appendChild(textArea);
            textArea.select();
            try {
                document.execCommand('copy');
                const copyBtn = document.getElementById('copyBtn');
                copyBtn.textContent = '✅ Copied!';
                copyBtn.classList.add('copied');
                setTimeout(function() {
                    copyBtn.textContent = '📋 Copy URL';
                    copyBtn.classList.remove('copied');
                }, 2000);
            } catch (err) {
                console.error('Fallback copy failed:', err);
                alert('Failed to copy. Please copy manually: ' + text);
            }
            document.body.removeChild(textArea);
        }
        
        // Check for IP changes and refresh QR code
        let lastIP = currentIP;
        
        function checkIPChange() {
            fetch('qr_login.php?check_ip=1')
                .then(response => response.json())
                .then(data => {
                    if (data.ip && data.ip !== lastIP) {
                        lastIP = data.ip;
                        location.reload(); // Reload page to update QR code
                    }
                })
                .catch(error => {
                    console.error('Error checking IP:', error);
                });
        }
        
        // Check for IP changes every 10 seconds
        setInterval(checkIPChange, 10000);
        
        // Try to use QRCode library if available, otherwise use API (which is already loaded)
        function initQRCode() {
            const qrImg = document.getElementById('qrcode-img');
            const qrCanvas = document.getElementById('qrcode');
            
            // Image is already loaded from PHP, so it should work immediately
            if (qrImg) {
                qrImg.onload = function() {
                    console.log('QR Code image loaded successfully');
                };
                qrImg.onerror = function() {
                    console.error('QR Code image failed to load');
                    // Try to regenerate with library if available
                    if (typeof QRCode !== 'undefined') {
                        generateQRCode();
                    }
                };
            }
            
            // Also try to use library for better quality (optional)
            if (typeof QRCode !== 'undefined') {
                setTimeout(function() {
                    generateQRCode();
                }, 500);
            }
        }
        
        // Initialize when page loads
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initQRCode);
        } else {
            initQRCode();
        }
    </script>
    
</body>
</html>

