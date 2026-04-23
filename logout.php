<?php
/*
|--------------------------------------------------------------------------
| FILE: logout.php
|--------------------------------------------------------------------------
| PURPOSE: Properly logs out the user by destroying their session
|
| WHAT IT DOES:
| 1. Starts the session (to access it)
| 2. Clears all session data
| 3. Destroys the session
| 4. Deletes the session cookie
| 5. Redirects to login page
|--------------------------------------------------------------------------
*/

// Start session with optimized config
require_once 'session_config.php';

// Clear all session variables
$_SESSION = array();

// Delete the session cookie (if it exists)
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),  // Cookie name (FURNITURE_SESSION)
        '',              // Empty value
        time() - 42000,  // Expired time (in the past)
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// Destroy the session
session_destroy();

// Redirect to login page
header("Location: login.html");
exit;
?>


