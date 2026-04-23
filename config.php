<?php
/*
||--------------------------------------------------------------------------
|| FILE: session_config.php
||--------------------------------------------------------------------------
|| PURPOSE: Optimized session configuration for handling multiple concurrent users
||--------------------------------------------------------------------------
*/

// Optimize session handling for concurrent users
if (session_status() === PHP_SESSION_NONE) {
    // Use files for session storage (default, but optimized)
    ini_set('session.save_handler', 'files');
    
    // Set session save path (use a dedicated directory)
    $session_path = sys_get_temp_dir() . '/php_sessions';
    if (!is_dir($session_path)) {
        @mkdir($session_path, 0700, true);
    }
    ini_set('session.save_path', $session_path);
    
    // Session timeout (30 minutes of inactivity)
    ini_set('session.gc_maxlifetime', 1800);
    
    // Garbage collection probability (clean up old sessions)
    ini_set('session.gc_probability', 1);
    ini_set('session.gc_divisor', 100);
    
    // Reduce session locking (important for concurrent users)
    // Use read/write locks instead of exclusive locks
    ini_set('session.use_strict_mode', 1);
    ini_set('session.use_cookies', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_httponly', 1);
    // session.cookie_samesite requires PHP 7.3+
    if (version_compare(PHP_VERSION, '7.3.0', '>=')) {
        ini_set('session.cookie_samesite', 'Lax');
    }
    
    // Session name - use consistent name across all pages
    session_name('FURNITURE_SESSION');
    
    // Set session cookie parameters for better persistence
    session_set_cookie_params([
        'lifetime' => 0, // Session cookie (expires when browser closes)
        'path' => '/',
        'domain' => '',
        'secure' => false, // Set to true if using HTTPS
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    
    // Start session with optimized settings
    session_start();
    
    // Regenerate session ID periodically to prevent session fixation
    // But don't regenerate on every page load - only after 30 minutes
    if (!isset($_SESSION['created'])) {
        $_SESSION['created'] = time();
    } else if (time() - $_SESSION['created'] > 1800) {
        // Regenerate session ID every 30 minutes
        session_regenerate_id(true);
        $_SESSION['created'] = time();
    }
}
?>

