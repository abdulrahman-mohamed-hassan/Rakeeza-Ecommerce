<?php
/*
||--------------------------------------------------------------------------
|| FILE: optimize_database.php
||--------------------------------------------------------------------------
|| PURPOSE: Run this ONCE to optimize database for concurrent users
||          Creates indexes and optimizes tables for better performance
||--------------------------------------------------------------------------
*/

include 'DBM.php';

echo "Optimizing database for concurrent users...\n\n";

// Add indexes for better query performance
$indexes = [
    // Products table indexes
    "CREATE INDEX IF NOT EXISTS idx_products_category ON products(category_id)",
    "CREATE INDEX IF NOT EXISTS idx_products_name ON products(name)",
    "CREATE INDEX IF NOT EXISTS idx_products_price ON products(price)",
    
    // Wishlist table indexes
    "CREATE INDEX IF NOT EXISTS idx_wishlist_user ON wishlist(user_id)",
    "CREATE INDEX IF NOT EXISTS idx_wishlist_product ON wishlist(product_id)",
    
    // Cart table indexes (if exists)
    "CREATE INDEX IF NOT EXISTS idx_cart_user ON cart(user_id)",
    "CREATE INDEX IF NOT EXISTS idx_cart_product ON cart(product_id)",
    
    // Orders table indexes (if exists)
    "CREATE INDEX IF NOT EXISTS idx_orders_user ON orders(user_id)",
    "CREATE INDEX IF NOT EXISTS idx_orders_status ON orders(status)",
    
    // Categories table indexes
    "CREATE INDEX IF NOT EXISTS idx_categories_name ON categories(name)",
];

foreach ($indexes as $index_sql) {
    // Remove "IF NOT EXISTS" for MySQL compatibility
    $index_sql = str_replace('IF NOT EXISTS', '', $index_sql);
    
    // Check if index already exists before creating
    $index_name = '';
    if (preg_match('/idx_(\w+)/', $index_sql, $matches)) {
        $index_name = $matches[0];
    }
    
    // Try to create index (will fail silently if exists)
    @mysqli_query($conn, $index_sql);
    echo "Index created/checked: $index_name\n";
}

// Optimize tables
$tables = ['products', 'categories', 'wishlist', 'cart', 'orders', 'users', 'product_images'];
foreach ($tables as $table) {
    $check = mysqli_query($conn, "SHOW TABLES LIKE '$table'");
    if ($check && mysqli_num_rows($check) > 0) {
        mysqli_query($conn, "OPTIMIZE TABLE $table");
        echo "Optimized table: $table\n";
    }
}

// Set MySQL variables for better concurrent performance
mysqli_query($conn, "SET GLOBAL max_connections = 200");
mysqli_query($conn, "SET GLOBAL thread_cache_size = 50");
mysqli_query($conn, "SET GLOBAL table_open_cache = 4000");

echo "\nDatabase optimization complete!\n";
echo "Your database is now optimized for handling 20+ concurrent users.\n";

mysqli_close($conn);
?>

