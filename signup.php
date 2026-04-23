<?php
/*
|--------------------------------------------------------------------------
| FILE: signup.php
|--------------------------------------------------------------------------
| PURPOSE: Handles new user registration - creates new accounts
|
| DICTIONARY:
| -----------
| Lines 23    : Include database connection
| Lines 25-27 : Password hashing function for security
| Lines 29-96  : Handle POST request - process registration
| Lines 31-40  : Check if form was submitted and all required fields filled
| Lines 43-52 : Get all form values from POST
| Lines 54-58 : Verify passwords match
| Lines 60-61 : Hash (encrypt) the password
| Lines 63-68 : Prepare SQL insert statement (prepared statement for security)
| Lines 70-80 : Bind form values to query parameters
| Lines 82-92 : Execute query and show success/error message
| Lines 98    : Close database connection
|--------------------------------------------------------------------------
*/

include("DBM.php");

function hashPin($pin) {
    return password_hash((string)$pin, PASSWORD_DEFAULT);
}

$message = '';
$messageType = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // Required fields check
    if (!empty($_POST['Username']) &&
        !empty($_POST['First_Name']) &&
        !empty($_POST['Last_Name']) &&
        !empty($_POST['Email']) &&
        !empty($_POST['Password']) &&
        !empty($_POST['Confirm_Password']) &&
        !empty($_POST['Phone_Number']) &&
        !empty($_POST['DOB']) &&
        !empty($_POST['Gender'])) 
    {

        // Assign POST values
        $username = $_POST['Username'];
        $first = $_POST['First_Name'];
        $last = $_POST['Last_Name'];
        $email = $_POST['Email'];
        $password = $_POST['Password'];
        $confirm = $_POST['Confirm_Password'];
        $phone = $_POST['Phone_Number'];
        $dob = $_POST['DOB'];
        $gender = $_POST['Gender'];

        // Check password match
        if ($password !== $confirm) {
            $message = "Passwords do not match!";
            $messageType = "error";
        } else {
            // Hash password
            $hashed_pin = hashPin($password);

            // Check if email already exists
            $check_stmt = $conn->prepare("SELECT id FROM users WHERE Email = ?");
            $check_stmt->bind_param("s", $email);
            $check_stmt->execute();
            $check_stmt->store_result();
            
            if ($check_stmt->num_rows > 0) {
                $message = "Email already exists! Please use a different email.";
                $messageType = "error";
                $check_stmt->close();
            } else {
                $check_stmt->close();
                
                // PREPARED STATEMENT (SAFE)
                $stmt = $conn->prepare("
                    INSERT INTO users 
                    (Username, First_Name, Last_Name, Email, Password, Phone, DOB, Gender)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                ");

                $stmt->bind_param(
                    "ssssssss",
                    $username,
                    $first,
                    $last,
                    $email,
                    $hashed_pin,
                    $phone,
                    $dob,
                    $gender
                );

                if ($stmt->execute()) {
                    $message = "Account created successfully! You can now login.";
                    $messageType = "success";
                } else {
                    $message = "Failed to create account. Please try again.";
                    $messageType = "error";
                }
                $stmt->close();
            }
        }
    } else {
        $message = "Please fill in all required fields!";
        $messageType = "error";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes, viewport-fit=cover">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="format-detection" content="telephone=no">
    <title>Sign Up - Rakeeza Furniture</title>
    <link rel="stylesheet" href="css/mobile-fixes.css">
    <style type="text/css">
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    
    body {
        min-height: 100vh;
        display: flex;
        justify-content: center;
        align-items: center;
        position: relative;
        overscroll-behavior: none;
        touch-action: pan-y;
    }
    
    .signup-container {
        width: 100%;
        max-width: 450px;
        background: #fff;
        padding: 40px;
        border-radius: 15px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        margin: 20px;
    }
    
    .signup-container h2 {
        margin-bottom: 30px;
        color: #2c3e50;
        font-size: 28px;
        text-align: center;
    }
    
    .message {
        padding: 15px 20px;
        border-radius: 8px;
        margin-bottom: 20px;
        font-weight: 500;
        text-align: center;
    }
    
    .message.success {
        background: #d4edda;
        border: 1px solid #28a745;
        color: #155724;
    }
    
    .message.error {
        background: #f8d7da;
        border: 1px solid #dc3545;
        color: #721c24;
    }
    
    .form-group {
        margin-bottom: 20px;
        position: relative;
    }
    
    .form-group label {
        display: block;
        margin-bottom: 8px;
        color: #2c3e50;
        font-weight: 500;
        font-size: 14px;
    }
    
    .form-group input,
    .form-group select {
        width: 100%;
        padding: 12px 15px;
        border: 1px solid #ddd;
        border-radius: 6px;
        background: #fff;
        color: #333;
        font-size: 16px;
        font-family: Arial, sans-serif;
        min-height: 44px;
    }
    
    .form-group input:focus,
    .form-group select:focus {
        outline: none;
        border-color: #cfa967;
        box-shadow: 0 0 0 3px rgba(207, 169, 103, 0.2);
    }
    
    .form-row {
        display: flex;
        gap: 15px;
        margin-bottom: 20px;
    }
    
    .form-row .form-group {
        flex: 1;
        margin-bottom: 0;
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
        margin-top: 10px;
    }
    
    .button:hover {
        background: #b58956;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(207, 169, 103, 0.4);
    }
    
    .login-text {
        margin-top: 20px;
        font-size: 14px;
        color: #666;
        text-align: center;
    }
    
    .login-text a {
        color: #cfa967;
        text-decoration: none;
        font-weight: 600;
    }
    
    .login-text a:hover {
        text-decoration: underline;
    }
    
    .required {
        color: #e74c3c;
    }
    
    @media (max-width: 768px) {
        .signup-container {
            padding: 30px 20px;
        }
        
        .form-row {
            flex-direction: column;
            gap: 20px;
        }
    }
    
    @media (max-width: 480px) {
        .signup-container {
            padding: 25px 15px;
        }
        
        .signup-container h2 {
            font-size: 24px;
        }
    }
    </style>
</head>
<body>
    <div class="signup-container">
        <h2>Create Account</h2>
        
        <?php if ($message): ?>
            <div class="message <?php echo $messageType; ?>"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <form action="signup.php" method="post" autocomplete="off">
            <div class="form-row">
                <div class="form-group">
                    <label>First Name <span class="required">*</span></label>
                    <input type="text" name="First_Name" required>
                </div>
                
                <div class="form-group">
                    <label>Last Name <span class="required">*</span></label>
                    <input type="text" name="Last_Name" required>
                </div>
            </div>
            
            <div class="form-group">
                <label>Username <span class="required">*</span></label>
                <input type="text" name="Username" required>
            </div>
            
            <div class="form-group">
                <label>Email <span class="required">*</span></label>
                <input type="email" name="Email" required>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Password <span class="required">*</span></label>
                    <input type="password" name="Password" required minlength="6">
                </div>
                
                <div class="form-group">
                    <label>Confirm Password <span class="required">*</span></label>
                    <input type="password" name="Confirm_Password" required minlength="6">
                </div>
            </div>
            
            <div class="form-group">
                <label>Phone Number <span class="required">*</span></label>
                <input type="tel" name="Phone_Number" required>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Date of Birth <span class="required">*</span></label>
                    <input type="date" name="DOB" required>
                </div>
                
                <div class="form-group">
                    <label>Gender <span class="required">*</span></label>
                    <select name="Gender" required>
                        <option value="">Select Gender</option>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
            </div>
            
            <button type="submit" class="button">Create Account</button>
            
            <p class="login-text">
                Already have an account? <a href="login.php">Login here</a>
            </p>
        </form>
    </div>
</body>
</html>
<?php $conn->close(); ?>