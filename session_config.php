<?php
/*
||--------------------------------------------------------------------------
|| FILE: session_config.php
||--------------------------------------------------------------------------
|| PURPOSE: Optimized session configuration for concurrent users
||          Centralizes session settings for all pages
||
|| DICTIONARY:
|| -----------
|| Lines 15-20  : Session save path configuration
|| Lines 22-25  : Session timeout and garbage collection
|| Lines 27-30  : Session security settings
|| Lines 32-40  : Cookie parameters (httponly, samesite, secure)
|| Lines 42-45  : Start session with optimized settings
||--------------------------------------------------------------------------
*/

// Session save path (file-based storage for better concurrent access)
$session_save_path = sys_get_temp_dir() . '/php_sessions';
if (!is_dir($session_save_path)) {
    @mkdir($session_save_path, 0700, true);
}
ini_set('session.save_path', $session_save_path);

// Session timeout and garbage collection
ini_set('session.gc_maxlifetime', 604800); // 1 week (7 days)
ini_set('session.cookie_lifetime', 604800); // 1 week
ini_set('session.gc_probability', 1);
ini_set('session.gc_divisor', 100);

// Session security settings
ini_set('session.use_strict_mode', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.use_trans_sid', 0);

// Cookie parameters for security
$cookie_params = [
    'lifetime' => 604800, // 1 week
    'path' => '/',
    'domain' => '',
    'secure' => false, // Set to true if using HTTPS
    'httponly' => true,
    'samesite' => 'Lax'
];

// Set cookie parameters
if (PHP_VERSION_ID >= 70300) {
    session_set_cookie_params($cookie_params);
} else {
    session_set_cookie_params(
        $cookie_params['lifetime'],
        $cookie_params['path'],
        $cookie_params['domain'],
        $cookie_params['secure'],
        $cookie_params['httponly']
    );
}

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
    
    // Regenerate session ID periodically for security (every 30 minutes)
    if (!isset($_SESSION['created'])) {
        $_SESSION['created'] = time();
    } elseif (time() - $_SESSION['created'] > 1800) {
        session_regenerate_id(true);
        $_SESSION['created'] = time();
    }
}
?>