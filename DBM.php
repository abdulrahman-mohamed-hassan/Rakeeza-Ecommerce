<?php
/*
||--------------------------------------------------------------------------
|| FILE: DBM.php (Database Manager)
||--------------------------------------------------------------------------
|| PURPOSE: Connects the website to the MySQL database with optimizations
||          for handling multiple concurrent users (20+ users)
||
|| DICTIONARY:
|| -----------
|| Lines 18-21  : Database configuration variables
|| Lines 24-50  : Database connection with optimizations for concurrency
||--------------------------------------------------------------------------
*/

// DATABASE CONFIGURATION
$db_server = "localhost";
$db_user = "root";
$db_pass = "";
$db_name = "websitesystem";

// CONNECT TO DATABASE
$conn = mysqli_connect($db_server, $db_user, $db_pass, $db_name);

if (!$conn) {
    // For development - show error details
    die("Connection failed: " . mysqli_connect_error() . 
        "<br>Please check:<br>" .
        "1. Is MySQL running?<br>" .
        "2. Database name: '$db_name' exists?<br>" .
        "3. Username/Password correct?");
}

// Set charset to UTF8 for proper encoding
mysqli_set_charset($conn, "utf8mb4");

// Set SQL mode
mysqli_query($conn, "SET SESSION sql_mode = 'STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION'");
?>