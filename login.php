<?php
/*
|--------------------------------------------------------------------------
| FILE: login.php
|--------------------------------------------------------------------------
| PURPOSE: Handles user login - verifies email/password and creates session
|
| DICTIONARY:
| -----------
| Lines 22-24  : Include session config and database connection
| Lines 26-78  : Handle POST request - verify credentials and create session
| Lines 30-31  : Get email and password from form
| Lines 33-37  : Query database for user with matching email (prepared statement)
| Lines 39-59  : If user found, verify password and create session
| Lines 60-66  : Handle incorrect password (alert and redirect back)
| Lines 68-74  : Handle email not found (alert and redirect back)
| Lines 80-81  : Close database connection
| Lines 82-246 : HTML LOGIN FORM (email, password, signup link)
|--------------------------------------------------------------------------
*/

// Optimized session handling for concurrent users
require_once 'session_config.php';
include("DBM.php"); // database connection file

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    if (!empty($_POST['Email']) && !empty($_POST['Password'])) {

        $email = $_POST['Email'];
        $password = $_POST['Password'];

        // Look up the user by email
        $stmt = $conn->prepare("SELECT id, email, password, username FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        // If email exists
        if ($stmt->num_rows > 0) {

            $stmt->bind_result($id, $db_email, $db_password, $db_username);
            $stmt->fetch();

            // Verify password
            if (password_verify($password, $db_password)) {

                // SUCCESS: Create a session
                // Regenerate session ID for security after login
                session_regenerate_id(true);
                
                $_SESSION['user_id'] = $id;
                $_SESSION['email'] = $db_email;
                $_SESSION['username'] = $db_username;
                $_SESSION['created'] = time(); // Update creation time

                // Redirect immediately with header (more reliable than JavaScript)
                header("Location: index.php");
                exit;
            } else {
                echo "<script>
                    alert('Incorrect password!');
                    window.history.back();
                </script>";
                exit;
            }

        } else {
            echo "<script>
                alert('Email not found!');
                window.history.back();
            </script>";
            exit;
        }

        $stmt->close();
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes, viewport-fit=cover">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="format-detection" content="telephone=no">
    <title>Login - rakeeza</title>
    <link rel="stylesheet" href="css/mobile-fixes.css">
    <style type="text/css">
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    
    body {
        height: 100vh;
        display: flex;
        justify-content: center;
        align-items: center;
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
    
    .login-container {
        width: 100%;
        max-width: 400px;
        background: #fff;
        padding: 40px;
        border-radius: 15px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        text-align: center;
        margin: 20px;
    }
    
    .login-container h2 {
        margin-bottom: 30px;
        color: #2c3e50;
        font-size: 28px;
    }
    
    .input-box {
        position: relative;
        margin-bottom: 30px;
    }
    
    .input-box input {
        width: 100%;
        padding: 12px 0;
        font-size: 16px;
        border: none;
        border-bottom: 2px solid #aaa;
        outline: none;
        background: none;
        color: #2c3e50;
    }
    
    .input-box label {
        position: absolute;
        left: 0;
        bottom: 12px;
        color: #777;
        pointer-events: none;
        transition: 0.3s;
    }
    
    .input-box input:focus ~ label,
    .input-box input:valid ~ label {
        bottom: 40px;
        font-size: 13px;
        color: #cfa967;
    }
    
    .button {
        width: 100%;
        padding: 14px;
        background: #cfa967;
        border: none;
        color: #2c3e50;
        font-size: 16px;
        font-weight: 600;
        border-radius: 8px;
        cursor: pointer;
        transition: 0.3s;
        touch-action: manipulation;
        min-height: 44px;
    }
    
    .button:hover {
        background: #b58956;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(207, 169, 103, 0.4);
    }
    
    .register-text {
        margin-top: 20px;
        font-size: 14px;
        color: #666;
    }
    
    .register-text a {
        color: #cfa967;
        text-decoration: none;
        font-weight: 600;
    }
    
    .register-text a:hover {
        text-decoration: underline;
    }
    </style>
</head>
<body>
    <div class="bg-blur"></div>
    
    <div class="login-container">
        <h2>Login</h2>
        
        <form action="login.php" method="post">
            <div class="input-box">
                <input type="email" name="Email" required>
                <label>Email</label>
            </div>
            
            <div class="input-box">
                <input type="password" name="Password" required>
                <label>Password</label>
            </div>
            
            <button type="submit" class="button">Login</button>
            
            <p class="register-text">
                Don't have an account? <a href="signup.php">Create one</a>
            </p>
        </form>
    </div>
</body>
</html>
