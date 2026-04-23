<?php
/*
|--------------------------------------------------------------------------
| FILE: admin.php
|--------------------------------------------------------------------------
| PURPOSE: Admin panel for managing products, categories, and orders
|
| DICTIONARY:
| -----------
| Lines 35-37  : Include session config and database connection
| Lines 39-43  : Admin access check (commented out)
| Lines 45-61  : CSV Template Download handler
| Lines 63-160 : CSV Import handler (bulk product import)
| Lines 162-195 : CSV Export handler (download all products)
| Lines 197-220 : Add Category handler
| Lines 222-240 : Delete Category handler
| Lines 242-320 : Add Product handler (with multiple images)
| Lines 322-430 : Edit Product handler (update + new images)
| Lines 432-460 : Delete Product Image handler
| Lines 462-485 : Delete Product handler
| Lines 487-510 : Update Order Status handler
| Lines 512-560 : Auto-create tables (categories, product_images, wishlist)
| Lines 562-595 : Fetch products, categories, orders from database
| Lines 597-610 : Get product for editing
| Lines 612-620 : Calculate statistics (total products, orders, revenue)
| Lines 640-1085: HTML HEAD and CSS STYLES (dashboard, tabs, forms, tables, mobile)
| Lines 1038-1040: Body with background blur and navbar include
| Lines 1042-1096: STATS CARDS (products, orders, revenue)
| Lines 1098-1103: TAB BUTTONS (Products, Categories, Orders)
| Lines 1105-1180: PRODUCTS TAB (add form, CSV import, product list)
| Lines 1182-1250: CATEGORIES TAB (add form, category list)
| Lines 1252-1350: ORDERS TAB (order list with status update)
| Lines 1352+   : JAVASCRIPT (tab switching)
|--------------------------------------------------------------------------
*/

// Optimized session handling for concurrent users
require_once 'session_config.php';
include("DBM.php");

// Check if user is logged in and is admin (for now, allow access - you can add admin check later)
// if (!isset($_SESSION['user_id']) || !$_SESSION['is_admin']) {
//     header("Location: login.html");
//     exit;
// }

// Handle CSV Template Download
if (isset($_GET['download_template'])) {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="products_template.csv"');
    
    $output = fopen('php://output', 'w');
    
    // CSV Header
    fputcsv($output, ['name', 'price', 'description', 'category_name', 'image_url', 'additional_images']);
    
    // Example rows
    fputcsv($output, ['Example Product 1', '1500.00', 'Product description here', 'Tables', 'https://example.com/image1.jpg', 'https://example.com/image2.jpg|https://example.com/image3.jpg']);
    fputcsv($output, ['Example Product 2', '2500.00', 'Another product description', 'Chairs', 'assets/images/product.jpg', '']);
    
    fclose($output);
    exit;
}

// Handle CSV Import
if (isset($_POST['import_csv']) && isset($_FILES['csv_file'])) {
    $file = $_FILES['csv_file'];
    
    if ($file['error'] == 0 && pathinfo($file['name'], PATHINFO_EXTENSION) == 'csv') {
        $handle = fopen($file['tmp_name'], 'r');
        
        // Skip header row
        $header = fgetcsv($handle);
        
        $imported = 0;
        $errors = 0;
        $error_messages = [];
        
        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) < 5) continue;
            
            $name = trim($row[0]);
            $price = floatval($row[1]);
            $description = trim($row[2]);
            $category_name = trim($row[3]);
            $image_url = trim($row[4]);
            $additional_images = isset($row[5]) ? trim($row[5]) : '';
            
            if (empty($name) || $price <= 0) {
                $errors++;
                $error_messages[] = "Skipped row: Invalid name or price";
                continue;
            }
            
            // Get category ID
            $category_id = null;
            if (!empty($category_name)) {
                $cat_result = $conn->query("SELECT id FROM categories WHERE name = '" . $conn->real_escape_string($category_name) . "'");
                if ($cat_result && $cat_result->num_rows > 0) {
                    $category_id = $cat_result->fetch_assoc()['id'];
                }
            }
            
            // Handle image
            $img_path = '';
            if (!empty($image_url)) {
                // Check if it's a URL or local path
                if (filter_var($image_url, FILTER_VALIDATE_URL)) {
                    // Download image from URL
                    $img_content = @file_get_contents($image_url);
                    if ($img_content !== false) {
                        $ext = pathinfo(parse_url($image_url, PHP_URL_PATH), PATHINFO_EXTENSION);
                        if (empty($ext)) $ext = 'jpg';
                        $new_filename = time() . '_' . uniqid() . '.' . $ext;
                        $img_path = 'assets/images/' . $new_filename;
                        file_put_contents($img_path, $img_content);
                    }
                } else {
                    // Local path - check if file exists
                    if (file_exists($image_url)) {
                        $img_path = $image_url;
                    }
                }
            }
            
            if (empty($img_path)) {
                $errors++;
                $error_messages[] = "Skipped '$name': Could not load image";
                continue;
            }
            
            // Insert product
            if ($category_id) {
                $stmt = $conn->prepare("INSERT INTO products (name, price, img, description, category_id) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("sdssi", $name, $price, $img_path, $description, $category_id);
            } else {
                $stmt = $conn->prepare("INSERT INTO products (name, price, img, description) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("sdss", $name, $price, $img_path, $description);
            }
            
            if ($stmt->execute()) {
                $product_id = $conn->insert_id;
                
                // Add main image to product_images
                $stmt_img = $conn->prepare("INSERT INTO product_images (product_id, img, is_primary, sort_order) VALUES (?, ?, 1, 0)");
                $stmt_img->bind_param("is", $product_id, $img_path);
                $stmt_img->execute();
                $stmt_img->close();
                
                // Handle additional images
                if (!empty($additional_images)) {
                    $add_imgs = explode('|', $additional_images);
                    $sort_order = 1;
                    
                    foreach ($add_imgs as $add_img_url) {
                        $add_img_url = trim($add_img_url);
                        if (empty($add_img_url)) continue;
                        
                        $add_img_path = '';
                        if (filter_var($add_img_url, FILTER_VALIDATE_URL)) {
                            $img_content = @file_get_contents($add_img_url);
                            if ($img_content !== false) {
                                $ext = pathinfo(parse_url($add_img_url, PHP_URL_PATH), PATHINFO_EXTENSION);
                                if (empty($ext)) $ext = 'jpg';
                                $new_filename = time() . '_' . $sort_order . '_' . uniqid() . '.' . $ext;
                                $add_img_path = 'assets/images/' . $new_filename;
                                file_put_contents($add_img_path, $img_content);
                            }
                        } else if (file_exists($add_img_url)) {
                            $add_img_path = $add_img_url;
                        }
                        
                        if (!empty($add_img_path)) {
                            $stmt_add = $conn->prepare("INSERT INTO product_images (product_id, img, is_primary, sort_order) VALUES (?, ?, 0, ?)");
                            $stmt_add->bind_param("isi", $product_id, $add_img_path, $sort_order);
                            $stmt_add->execute();
                            $stmt_add->close();
                            $sort_order++;
                        }
                    }
                }
                
                $imported++;
            } else {
                $errors++;
                $error_messages[] = "Failed to import '$name'";
            }
            $stmt->close();
        }
        
        fclose($handle);
        
        if ($imported > 0) {
            $message = "Successfully imported $imported product(s).";
            if ($errors > 0) {
                $message .= " $errors row(s) had errors.";
            }
            $messageType = "success";
        } else {
            $message = "No products were imported. " . implode("; ", array_slice($error_messages, 0, 3));
            $messageType = "error";
        }
    } else {
        $message = "Please upload a valid CSV file.";
        $messageType = "error";
    }
}

// Handle CSV Export (download existing products as CSV)
if (isset($_GET['export_products'])) {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="products_export_' . date('Y-m-d') . '.csv"');
    
    $output = fopen('php://output', 'w');
    
    // CSV Header
    fputcsv($output, ['id', 'name', 'price', 'description', 'category_name', 'image', 'created_at']);
    
    // Fetch all products
    $export_result = $conn->query("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.id ASC");
    if ($export_result) {
        while ($row = $export_result->fetch_assoc()) {
            fputcsv($output, [
                $row['id'],
                $row['name'],
                $row['price'],
                $row['description'],
                $row['category_name'] ?? '',
                $row['img'],
                $row['created_at']
            ]);
        }
    }
    
    fclose($output);
    exit;
}

$message = isset($message) ? $message : '';
$messageType = isset($messageType) ? $messageType : '';

// Handle Add Category
if (isset($_POST['add_category'])) {
    $name = trim($_POST['name']);
    
    // Handle image upload
    $img_path = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'avif', 'webp'];
        $filename = $_FILES['image']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (in_array($ext, $allowed)) {
            $new_filename = time() . '_' . preg_replace('/[^a-zA-Z0-9.]/', '_', $filename);
            $upload_path = 'assets/images/' . $new_filename;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                $img_path = $upload_path;
            }
        }
    }
    
    if ($name && $img_path) {
        $stmt = $conn->prepare("INSERT INTO categories (name, img) VALUES (?, ?)");
        $stmt->bind_param("ss", $name, $img_path);
        
        if ($stmt->execute()) {
            $message = "Category added successfully!";
            $messageType = "success";
        } else {
            $message = "Error adding category.";
            $messageType = "error";
        }
        $stmt->close();
    } else {
        $message = "Please fill all required fields and upload an image.";
        $messageType = "error";
    }
}

// Handle Delete Category
if (isset($_POST['delete_category'])) {
    $id = intval($_POST['category_id']);
    
    $stmt = $conn->prepare("DELETE FROM categories WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        $message = "Category deleted successfully!";
        $messageType = "success";
    } else {
        $message = "Error deleting category.";
        $messageType = "error";
    }
    $stmt->close();
}

// Handle Add Product
if (isset($_POST['add_product'])) {
    $name = trim($_POST['name']);
    $price = floatval($_POST['price']);
    $description = trim($_POST['description']);
    $category_id = !empty($_POST['category_id']) ? intval($_POST['category_id']) : null;
    
    // Handle main image upload
    $img_path = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'avif', 'webp'];
        $filename = $_FILES['image']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (in_array($ext, $allowed)) {
            $new_filename = time() . '_' . preg_replace('/[^a-zA-Z0-9.]/', '_', $filename);
            $upload_path = 'assets/images/' . $new_filename;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                $img_path = $upload_path;
            }
        }
    }
    
    if ($name && $price && $img_path) {
        if ($category_id) {
            $stmt = $conn->prepare("INSERT INTO products (name, price, img, description, category_id) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sdssi", $name, $price, $img_path, $description, $category_id);
        } else {
            $stmt = $conn->prepare("INSERT INTO products (name, price, img, description) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("sdss", $name, $price, $img_path, $description);
        }
        
        if ($stmt->execute()) {
            $product_id = $conn->insert_id;
            
            // Add main image to product_images as primary
            $stmt_img = $conn->prepare("INSERT INTO product_images (product_id, img, is_primary, sort_order) VALUES (?, ?, 1, 0)");
            $stmt_img->bind_param("is", $product_id, $img_path);
            $stmt_img->execute();
            $stmt_img->close();
            
            // Handle additional images
            if (isset($_FILES['additional_images'])) {
                $allowed = ['jpg', 'jpeg', 'png', 'gif', 'avif', 'webp'];
                $sort_order = 1;
                
                foreach ($_FILES['additional_images']['tmp_name'] as $key => $tmp_name) {
                    if ($_FILES['additional_images']['error'][$key] == 0) {
                        $filename = $_FILES['additional_images']['name'][$key];
                        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                        
                        if (in_array($ext, $allowed)) {
                            $new_filename = time() . '_' . $sort_order . '_' . preg_replace('/[^a-zA-Z0-9.]/', '_', $filename);
                            $add_path = 'assets/images/' . $new_filename;
                            
                            if (move_uploaded_file($tmp_name, $add_path)) {
                                $stmt_add = $conn->prepare("INSERT INTO product_images (product_id, img, is_primary, sort_order) VALUES (?, ?, 0, ?)");
                                $stmt_add->bind_param("isi", $product_id, $add_path, $sort_order);
                                $stmt_add->execute();
                                $stmt_add->close();
                                $sort_order++;
                            }
                        }
                    }
                }
            }
            
            $message = "Product added successfully!";
            $messageType = "success";
        } else {
            $message = "Error adding product.";
            $messageType = "error";
        }
        $stmt->close();
    } else {
        $message = "Please fill all required fields and upload an image.";
        $messageType = "error";
    }
}

// Handle Edit Product
if (isset($_POST['edit_product'])) {
    $id = intval($_POST['product_id']);
    $name = trim($_POST['name']);
    $price = floatval($_POST['price']);
    $description = trim($_POST['description']);
    $category_id = !empty($_POST['category_id']) ? intval($_POST['category_id']) : null;
    
    // Check if new main image uploaded
    $img_update = '';
    $upload_path = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'avif', 'webp'];
        $filename = $_FILES['image']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (in_array($ext, $allowed)) {
            $new_filename = time() . '_' . preg_replace('/[^a-zA-Z0-9.]/', '_', $filename);
            $upload_path = 'assets/images/' . $new_filename;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                $img_update = ", img = '$upload_path'";
                
                // Update primary image in product_images table
                $conn->query("UPDATE product_images SET is_primary = 0 WHERE product_id = $id");
                $stmt_img = $conn->prepare("INSERT INTO product_images (product_id, img, is_primary, sort_order) VALUES (?, ?, 1, 0)");
                $stmt_img->bind_param("is", $id, $upload_path);
                $stmt_img->execute();
                $stmt_img->close();
            }
        }
    }
    
    if ($img_update) {
        if ($category_id) {
            $stmt = $conn->prepare("UPDATE products SET name = ?, price = ?, description = ?, img = ?, category_id = ? WHERE id = ?");
            $stmt->bind_param("sdssii", $name, $price, $description, $upload_path, $category_id, $id);
        } else {
            $stmt = $conn->prepare("UPDATE products SET name = ?, price = ?, description = ?, img = ?, category_id = NULL WHERE id = ?");
            $stmt->bind_param("sdssi", $name, $price, $description, $upload_path, $id);
        }
    } else {
        if ($category_id) {
            $stmt = $conn->prepare("UPDATE products SET name = ?, price = ?, description = ?, category_id = ? WHERE id = ?");
            $stmt->bind_param("sdsii", $name, $price, $description, $category_id, $id);
        } else {
            $stmt = $conn->prepare("UPDATE products SET name = ?, price = ?, description = ?, category_id = NULL WHERE id = ?");
            $stmt->bind_param("sdsi", $name, $price, $description, $id);
        }
    }
    
    if ($stmt->execute()) {
        // Handle additional images
        if (isset($_FILES['additional_images'])) {
            $allowed = ['jpg', 'jpeg', 'png', 'gif', 'avif', 'webp'];
            // Get highest sort order
            $max_order = $conn->query("SELECT MAX(sort_order) as max_order FROM product_images WHERE product_id = $id")->fetch_assoc()['max_order'] ?? 0;
            $sort_order = $max_order + 1;
            
            foreach ($_FILES['additional_images']['tmp_name'] as $key => $tmp_name) {
                if ($_FILES['additional_images']['error'][$key] == 0) {
                    $filename = $_FILES['additional_images']['name'][$key];
                    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                    
                    if (in_array($ext, $allowed)) {
                        $new_filename = time() . '_' . $sort_order . '_' . preg_replace('/[^a-zA-Z0-9.]/', '_', $filename);
                        $add_path = 'assets/images/' . $new_filename;
                        
                        if (move_uploaded_file($tmp_name, $add_path)) {
                            $stmt_add = $conn->prepare("INSERT INTO product_images (product_id, img, is_primary, sort_order) VALUES (?, ?, 0, ?)");
                            $stmt_add->bind_param("isi", $id, $add_path, $sort_order);
                            $stmt_add->execute();
                            $stmt_add->close();
                            $sort_order++;
                        }
                    }
                }
            }
        }
        
        $message = "Product updated successfully!";
        $messageType = "success";
    } else {
        $message = "Error updating product.";
        $messageType = "error";
    }
    $stmt->close();
}

// Handle Delete Product Image
if (isset($_POST['delete_product_image'])) {
    $img_id = intval($_POST['image_id']);
    $product_id = intval($_POST['product_id']);
    
    // Check if it's the primary image
    $check = $conn->query("SELECT is_primary FROM product_images WHERE id = $img_id");
    $img_data = $check->fetch_assoc();
    
    if ($img_data && $img_data['is_primary'] == 0) {
        $stmt = $conn->prepare("DELETE FROM product_images WHERE id = ?");
        $stmt->bind_param("i", $img_id);
        $stmt->execute();
        $stmt->close();
        $message = "Image deleted successfully!";
        $messageType = "success";
    } else {
        $message = "Cannot delete primary image. Upload a new main image first.";
        $messageType = "error";
    }
    
    header("Location: admin.php?edit=$product_id");
    exit;
}

// Handle Delete Product
if (isset($_POST['delete_product'])) {
    $id = intval($_POST['product_id']);
    
    $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        $message = "Product deleted successfully!";
        $messageType = "success";
    } else {
        $message = "Error deleting product.";
        $messageType = "error";
    }
    $stmt->close();
}

// Handle Update Order Status
if (isset($_POST['update_order_status'])) {
    $order_id = intval($_POST['order_id']);
    $status = $_POST['status'];
    
    $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $order_id);
    
    if ($stmt->execute()) {
        $message = "Order status updated!";
        $messageType = "success";
    } else {
        $message = "Error updating order status.";
        $messageType = "error";
    }
    $stmt->close();
}

// Auto-create categories table if it doesn't exist
$check_table = $conn->query("SHOW TABLES LIKE 'categories'");
if ($check_table->num_rows == 0) {
    $conn->query("CREATE TABLE categories (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        img VARCHAR(500) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
}

// Auto-seed categories if table is empty
$cat_count = $conn->query("SELECT COUNT(*) as count FROM categories");
$cat_row = $cat_count->fetch_assoc();
if ($cat_row['count'] == 0) {
    $initial_categories = [
        ["Tables", "assets/images/img2.avif"],
        ["Chairs", "assets/images/seat.avif"],
        ["Drawers", "assets/images/Drawers.avif"]
    ];
    
    $stmt_cat = $conn->prepare("INSERT INTO categories (name, img) VALUES (?, ?)");
    foreach ($initial_categories as $cat) {
        $stmt_cat->bind_param("ss", $cat[0], $cat[1]);
        $stmt_cat->execute();
    }
    $stmt_cat->close();
}

// Ensure category_id column exists in products table
$check_category_col = $conn->query("SHOW COLUMNS FROM products LIKE 'category_id'");
if ($check_category_col->num_rows == 0) {
    // Add category_id column
    $conn->query("ALTER TABLE products ADD COLUMN category_id INT");
    // Try to add foreign key, but don't fail if it doesn't work
    @$conn->query("ALTER TABLE products ADD FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL");
}

// Create product_images table if not exists
$check_images_table = $conn->query("SHOW TABLES LIKE 'product_images'");
if ($check_images_table->num_rows == 0) {
    $conn->query("CREATE TABLE product_images (
        id INT AUTO_INCREMENT PRIMARY KEY,
        product_id INT NOT NULL,
        img VARCHAR(500) NOT NULL,
        is_primary TINYINT(1) DEFAULT 0,
        sort_order INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
    )");
}

// Create wishlist table if not exists
$check_wishlist_table = $conn->query("SHOW TABLES LIKE 'wishlist'");
if ($check_wishlist_table->num_rows == 0) {
    $conn->query("CREATE TABLE wishlist (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        product_id INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY unique_wishlist (user_id, product_id),
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
    )");
}

// Check if category_id column exists in products table
$check_category_col = $conn->query("SHOW COLUMNS FROM products LIKE 'category_id'");
$has_category_col = $check_category_col && $check_category_col->num_rows > 0;

// Fetch all products
if ($has_category_col) {
    $products = $conn->query("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.id DESC");
} else {
    $products = $conn->query("SELECT * FROM products ORDER BY id DESC");
}

// If query failed, try simple query
if (!$products) {
    $products = $conn->query("SELECT * FROM products ORDER BY id DESC");
}

// Fetch all categories
$categories = $conn->query("SELECT * FROM categories ORDER BY name ASC");
if (!$categories) {
    // If query fails, create empty result set
    $categories = $conn->query("SELECT * FROM categories ORDER BY name ASC");
}

// Fetch all orders with user info
$orders = $conn->query("
    SELECT o.*, u.username AS user_username, u.email AS user_email,
           (SELECT SUM(oi.quantity) FROM order_items oi WHERE oi.order_id = o.id) as total_items
    FROM orders o 
    LEFT JOIN users u ON o.user_id = u.id 
    ORDER BY o.created_at DESC
");

// Get product for editing if requested
$editProduct = null;
if (isset($_GET['edit'])) {
    $editId = intval($_GET['edit']);
    $result = $conn->query("SELECT * FROM products WHERE id = $editId");
    $editProduct = $result->fetch_assoc();
}

// Stats
$totalProducts = $conn->query("SELECT COUNT(*) as count FROM products")->fetch_assoc()['count'];
$totalOrders = $conn->query("SELECT COUNT(*) as count FROM orders")->fetch_assoc()['count'];
$pendingOrders = $conn->query("SELECT COUNT(*) as count FROM orders WHERE status = 'pending'")->fetch_assoc()['count'];
$totalRevenue = $conn->query("SELECT SUM(total_amount) as total FROM orders WHERE status != 'cancelled'")->fetch_assoc()['total'] ?? 0;
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
    <link rel="stylesheet" href="css/mobile-fixes.css">
    <title>Admin Panel - Furniture Store</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: Arial, sans-serif;
            background: #f4ede2;
            min-height: 100vh;
            margin-top: 60px;
            position: relative;
        }
        
        /* Blurred background */
      
        
        /* Navbar styles are in navbar.php - no need to duplicate */
        
        .logo {
            height: 40px;
            width: auto;
            display: block;
        }
        
        .username {
            color: #ecf0f1;
            padding: 0 12px;
            font-size: 14px;
            font-weight: 500;
            white-space: nowrap;
        }
        
        /* Container */
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 30px 16px;
        }
        
        /* Page title */
        .page-title {
            color: #2c3e50;
            font-size: 28px;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        /* Stats cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.12);
            padding: 25px;
            text-align: center;
            transition: transform .25s ease, box-shadow .25s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 14px 30px rgba(0,0,0,0.18);
        }
        
        .stat-card h4 {
            color: #2c3e50;
            font-size: 14px;
            margin-bottom: 10px;
            text-transform: uppercase;
        }
        
        .stat-card .number {
            font-size: 32px;
            font-weight: bold;
            color: #cfa967;
        }
        
        /* Tabs */
        .tabs {
            display: flex;
            gap: 5px;
            margin-bottom: 0;
        }
        
        .tab {
            padding: 15px 30px;
            background: #2c3e50;
            border: none;
            color: #ecf0f1;
            cursor: pointer;
            border-radius: 8px 8px 0 0;
            font-size: 15px;
            font-weight: 500;
            transition: all 0.3s;
        }
        
        .tab:hover, .tab.active {
            background: #cfa967;
            color: #2c3e50;
        }
        
        .tab-content {
            display: none;
            background: #fff;
            border-radius: 0 8px 8px 8px;
            padding: 30px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.12);
        }
        
        .tab-content.active { display: block; }
        
        /* Messages */
        .message {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: 500;
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
        
        /* Form Styles */
        .form-section {
            background: #f4ede2;
            padding: 25px;
            border-radius: 10px;
            margin-bottom: 30px;
            border: 1px solid rgba(207, 169, 103, 0.3);
        }
        
        .form-section h3 {
            color: #2c3e50;
            margin-bottom: 20px;
            font-size: 18px;
        }
        
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
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
            border-radius: 6px;
            background: #fff;
            color: #333;
            font-size: 16px; /* Prevents zoom on iOS */
            font-family: Arial, sans-serif;
            min-height: 44px;
        }
        
        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #cfa967;
            box-shadow: 0 0 0 3px rgba(207, 169, 103, 0.2);
        }
        
        .form-group textarea { resize: vertical; min-height: 100px; }
        
        .form-group small { color: #666; font-size: 12px; }
        
        /* Buttons */
        .btn {
            padding: 12px 25px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-primary {
            background: #cfa967;
            color: #2c3e50;
        }
        
        .btn-primary:hover { background: #b58956; }
        
        .btn-danger {
            background: #dc3545;
            color: #fff;
        }
        
        .btn-danger:hover { background: #c82333; }
        
        .btn-edit {
            background: #2c3e50;
            color: #fff;
        }
        
        .btn-edit:hover { background: #1a252f; }
        
        /* Table Styles */
        .table-container {
            overflow-x: auto;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        
        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        
        th {
            background: #2c3e50;
            color: #fff;
            font-weight: 600;
            font-size: 14px;
        }
        
        tr:hover { background: #f9f9f9; }
        
        .product-img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .actions {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }
        
        .actions form { display: inline; }
        
        .actions .btn {
            padding: 8px 15px;
            font-size: 13px;
        }
        
        /* Status badges */
        .status {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .status-pending { background: #fff3cd; color: #856404; }
        .status-processing { background: #cce5ff; color: #004085; }
        .status-shipped { background: #e2d5f1; color: #6f42c1; }
        .status-delivered { background: #d4edda; color: #155724; }
        .status-cancelled { background: #f8d7da; color: #721c24; }
        
        .status-form {
            display: flex;
            gap: 8px;
            align-items: center;
        }
        
        .status-form select {
            padding: 8px 12px;
            border-radius: 5px;
            border: 1px solid #ddd;
            background: #fff;
            color: #333;
            font-size: 13px;
        }
        
        /* Section titles */
        .section-title {
            color: #2c3e50;
            margin-bottom: 20px;
            font-size: 18px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        /* Footer */
        footer {
            background: #1e1e1e;
            color: #fff;
            text-align: center;
            padding: 40px 20px;
            margin-top: 50px;
            padding-bottom: calc(40px + env(safe-area-inset-bottom));
        }
        
        /* Mobile Responsive */
        @media (max-width: 768px) {
            .container {
                padding: 20px 12px;
            }
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            .tabs {
                flex-wrap: wrap;
            }
            .tab {
                flex: 1;
                min-width: 120px;
                padding: 12px 15px;
                font-size: 14px;
            }
            .form-grid {
                grid-template-columns: 1fr;
            }
            .table-container {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }
            table {
                min-width: 800px;
            }
            .actions {
                flex-direction: column;
            }
            .btn {
                min-height: 44px;
                font-size: 16px;
            }
        }
        
        @media (max-width: 480px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
            .page-title {
                font-size: 24px;
            }
            .form-section {
                padding: 20px 15px;
            }
            .tab-content {
                padding: 20px 15px;
            }
        }
    </style>
</head>
<body>
<div class="bg-blur"></div>

<?php include 'navbar.php'; ?>

<div class="container">
    <h1 class="page-title">🛠️ Admin Panel</h1>
    
    <?php if ($message): ?>
        <div class="message <?php echo $messageType; ?>"><?php echo $message; ?></div>
    <?php endif; ?>
    
    <!-- Stats -->
    <div class="stats-grid">
        <div class="stat-card">
            <h4>Total Products</h4>
            <div class="number"><?php echo $totalProducts; ?></div>
        </div>
        <div class="stat-card">
            <h4>Total Orders</h4>
            <div class="number"><?php echo $totalOrders; ?></div>
        </div>
        <div class="stat-card">
            <h4>Pending Orders</h4>
            <div class="number"><?php echo $pendingOrders; ?></div>
        </div>
        <div class="stat-card">
            <h4>Total Revenue</h4>
            <div class="number">EGP <?php echo number_format($totalRevenue); ?></div>
        </div>
    </div>
    
    <!-- Tabs -->
    <div class="tabs">
        <button class="tab active" onclick="showTab('products', this)">📦 Products</button>
        <button class="tab" onclick="showTab('categories', this)">🏷️ Categories</button>
        <button class="tab" onclick="showTab('orders', this)">📋 Orders</button>
    </div>
    
    <!-- Products Tab -->
    <div id="products" class="tab-content active">
        <div class="form-section">
            <h3><?php echo $editProduct ? '✏️ Edit Product' : '➕ Add New Product'; ?></h3>
            <form method="POST" enctype="multipart/form-data">
                <?php if ($editProduct): ?>
                    <input type="hidden" name="product_id" value="<?php echo $editProduct['id']; ?>">
                <?php endif; ?>
                
                <div class="form-grid">
                    <div class="form-group">
                        <label>Product Name *</label>
                        <input type="text" name="name" required value="<?php echo $editProduct ? htmlspecialchars($editProduct['name']) : ''; ?>">
                    </div>
                    <div class="form-group">
                        <label>Price (EGP) *</label>
                        <input type="number" name="price" step="0.01" required value="<?php echo $editProduct ? $editProduct['price'] : ''; ?>">
                    </div>
                    <div class="form-group">
                        <label>Product Image <?php echo $editProduct ? '(leave empty to keep current)' : '*'; ?></label>
                        <input type="file" name="image" accept="image/*" <?php echo $editProduct ? '' : 'required'; ?>>
                        <?php if ($editProduct && $editProduct['img']): ?>
                            <small>Current: <?php echo basename($editProduct['img']); ?></small>
                        <?php endif; ?>
                    </div>
                    <div class="form-group">
                        <label>Category (Optional)</label>
                        <select name="category_id">
                            <option value="">-- No Category --</option>
                            <?php 
                            // Ensure categories table exists
                            $check_cat_table = $conn->query("SHOW TABLES LIKE 'categories'");
                            if ($check_cat_table && $check_cat_table->num_rows > 0) {
                                $cat_list = $conn->query("SELECT * FROM categories ORDER BY name ASC");
                                if ($cat_list && $cat_list->num_rows > 0) {
                                    while ($cat = $cat_list->fetch_assoc()): 
                            ?>
                                <option value="<?php echo $cat['id']; ?>" <?php echo ($editProduct && isset($editProduct['category_id']) && $editProduct['category_id'] == $cat['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat['name']); ?>
                                </option>
                            <?php 
                                    endwhile;
                                }
                            }
                            ?>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description"><?php echo $editProduct ? htmlspecialchars($editProduct['description']) : ''; ?></textarea>
                </div>
                <div class="form-group">
                    <label>Additional Images (optional - select multiple)</label>
                    <input type="file" name="additional_images[]" accept="image/*" multiple>
                    <small>Hold Ctrl/Cmd to select multiple images</small>
                </div>
                
                <?php if ($editProduct): 
                    // Fetch existing product images
                    $existing_images = $conn->query("SELECT * FROM product_images WHERE product_id = " . $editProduct['id'] . " ORDER BY is_primary DESC, sort_order ASC");
                    if ($existing_images && $existing_images->num_rows > 0):
                ?>
                <div class="form-group">
                    <label>Existing Images</label>
                    <div style="display: flex; gap: 15px; flex-wrap: wrap; margin-top: 10px;">
                        <?php while ($img = $existing_images->fetch_assoc()): ?>
                        <div style="position: relative; border: <?php echo $img['is_primary'] ? '3px solid #cfa967' : '1px solid #ddd'; ?>; border-radius: 8px; overflow: hidden;">
                            <img src="<?php echo htmlspecialchars($img['img']); ?>" alt="" style="width: 100px; height: 100px; object-fit: cover; display: block;">
                            <?php if ($img['is_primary']): ?>
                                <span style="position: absolute; top: 5px; left: 5px; background: #cfa967; color: white; padding: 2px 6px; font-size: 10px; border-radius: 3px;">Primary</span>
                            <?php else: ?>
                                <form method="POST" style="position: absolute; top: 5px; right: 5px;" onsubmit="return confirm('Delete this image?');">
                                    <input type="hidden" name="image_id" value="<?php echo $img['id']; ?>">
                                    <input type="hidden" name="product_id" value="<?php echo $editProduct['id']; ?>">
                                    <button type="submit" name="delete_product_image" style="background: #e74c3c; color: white; border: none; border-radius: 50%; width: 20px; height: 20px; cursor: pointer; font-size: 12px; line-height: 1;">×</button>
                                </form>
                            <?php endif; ?>
                        </div>
                        <?php endwhile; ?>
                    </div>
                </div>
                <?php endif; endif; ?>
                
                <button type="submit" name="<?php echo $editProduct ? 'edit_product' : 'add_product'; ?>" class="btn btn-primary">
                    <?php echo $editProduct ? '💾 Update Product' : '➕ Add Product'; ?>
                </button>
                <?php if ($editProduct): ?>
                    <a href="admin.php" class="btn btn-danger" style="margin-left:10px;">Cancel</a>
                <?php endif; ?>
            </form>
        </div>
        
        <!-- CSV Import/Export Section -->
        <div class="form-section" style="margin-top: 30px; background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);">
            <h3>📁 Bulk Import/Export Products</h3>
            <div style="display: flex; gap: 20px; flex-wrap: wrap; align-items: flex-start;">
                <!-- Import Section -->
                <div style="flex: 1; min-width: 300px;">
                    <h4 style="margin-bottom: 15px; color: #2c3e50;">Import Products from CSV</h4>
                    <form method="POST" enctype="multipart/form-data" style="display: flex; flex-direction: column; gap: 10px;">
                        <input type="file" name="csv_file" accept=".csv" required style="padding: 10px; border: 2px dashed #ccc; border-radius: 8px; background: white;">
                        <button type="submit" name="import_csv" class="btn btn-primary" style="width: fit-content;">
                            📤 Import Products
                        </button>
                    </form>
                    <p style="margin-top: 10px; font-size: 13px; color: #666;">
                        <strong>CSV Format:</strong> name, price, description, category_name, image_url, additional_images<br>
                        <small>• Images can be URLs or local paths (e.g., assets/images/product.jpg)<br>
                        • Additional images separated by | (pipe character)<br>
                        • Category must match existing category names</small>
                    </p>
                </div>
                
                <!-- Export/Template Section -->
                <div style="flex: 1; min-width: 300px;">
                    <h4 style="margin-bottom: 15px; color: #2c3e50;">Download Templates & Export</h4>
                    <div style="display: flex; flex-direction: column; gap: 10px;">
                        <a href="admin.php?download_template=1" class="btn btn-edit" style="text-decoration: none; text-align: center; display: inline-block; width: fit-content;">
                            📥 Download CSV Template
                        </a>
                        <a href="admin.php?export_products=1" class="btn" style="background: #27ae60; color: white; text-decoration: none; text-align: center; display: inline-block; width: fit-content;">
                            📊 Export All Products
                        </a>
                    </div>
                    <p style="margin-top: 10px; font-size: 13px; color: #666;">
                        <strong>Template:</strong> Pre-filled CSV with example rows<br>
                        <strong>Export:</strong> Download all existing products as CSV
                    </p>
                </div>
            </div>
        </div>
        
        <h3 class="section-title">📦 All Products</h3>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Image</th>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Description</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    if ($products) {
                        if ($products->num_rows > 0): 
                            while ($row = $products->fetch_assoc()): 
                    ?>
                        <tr>
                            <td><?php echo $row['id']; ?></td>
                            <td><img src="<?php echo htmlspecialchars($row['img']); ?>" alt="" class="product-img"></td>
                            <td><strong><?php echo htmlspecialchars($row['name']); ?></strong></td>
                            <td><?php echo isset($row['category_name']) && $row['category_name'] ? htmlspecialchars($row['category_name']) : '<span style="color:#999;">None</span>'; ?></td>
                            <td>EGP <?php echo number_format($row['price']); ?></td>
                            <td><?php echo htmlspecialchars(substr($row['description'] ?? '', 0, 50)) . '...'; ?></td>
                            <td class="actions">
                                <a href="admin.php?edit=<?php echo $row['id']; ?>" class="btn btn-edit">✏️ Edit</a>
                                <form method="POST" onsubmit="return confirm('Are you sure you want to delete this product?');">
                                    <input type="hidden" name="product_id" value="<?php echo $row['id']; ?>">
                                    <button type="submit" name="delete_product" class="btn btn-danger">🗑️ Delete</button>
                                </form>
                            </td>
                        </tr>
                        <?php 
                            endwhile;
                        else: 
                        ?>
                        <tr>
                            <td colspan="7" style="text-align:center; padding:40px; color:#888;">No products yet. Add your first product above!</td>
                        </tr>
                    <?php 
                        endif;
                    } else {
                        // Query failed - show error
                    ?>
                        <tr>
                            <td colspan="7" style="text-align:center; padding:40px; color:#e74c3c;">
                                Error loading products. Please check database connection.
                                <br><small><?php echo $conn->error; ?></small>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Categories Tab -->
    <div id="categories" class="tab-content">
        <div class="form-section">
            <h3>➕ Add New Category</h3>
            <form method="POST" enctype="multipart/form-data">
                <div class="form-grid">
                    <div class="form-group">
                        <label>Category Name *</label>
                        <input type="text" name="name" required placeholder="e.g., Tables, Chairs, Sofas">
                    </div>
                    <div class="form-group">
                        <label>Category Image *</label>
                        <input type="file" name="image" accept="image/*" required>
                    </div>
                </div>
                <button type="submit" name="add_category" class="btn btn-primary">➕ Add Category</button>
            </form>
        </div>
        
        <h3 class="section-title">🏷️ All Categories</h3>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Image</th>
                        <th>Name</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($categories && $categories->num_rows > 0): ?>
                        <?php while ($cat = $categories->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $cat['id']; ?></td>
                            <td><img src="<?php echo htmlspecialchars($cat['img']); ?>" alt="" class="product-img"></td>
                            <td><strong><?php echo htmlspecialchars($cat['name']); ?></strong></td>
                            <td class="actions">
                                <form method="POST" onsubmit="return confirm('Are you sure you want to delete this category? Products in this category will have their category removed.');">
                                    <input type="hidden" name="category_id" value="<?php echo $cat['id']; ?>">
                                    <button type="submit" name="delete_category" class="btn btn-danger">🗑️ Delete</button>
                                </form>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" style="text-align:center; padding:40px; color:#888;">No categories yet. Add your first category above!</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Orders Tab -->
    <div id="orders" class="tab-content">
        <h3 class="section-title">📋 All Orders</h3>
        
        <?php if ($orders && $orders->num_rows > 0): ?>
            <?php while ($order = $orders->fetch_assoc()): 
                // Get order items
                $order_items_query = $conn->query("SELECT oi.*, p.name, p.img FROM order_items oi LEFT JOIN products p ON oi.product_id = p.id WHERE oi.order_id = " . $order['id']);
            ?>
            <div class="order-card" style="background:#f9f9f9; border-radius:10px; padding:20px; margin-bottom:20px; border-left:4px solid <?php 
                echo $order['status'] == 'pending' ? '#f39c12' : 
                    ($order['status'] == 'processing' ? '#3498db' : 
                    ($order['status'] == 'shipped' ? '#9b59b6' : 
                    ($order['status'] == 'delivered' ? '#27ae60' : '#e74c3c'))); 
            ?>;">
                <div style="display:flex; justify-content:space-between; flex-wrap:wrap; gap:20px; margin-bottom:15px;">
                    <div>
                        <h4 style="color:#2c3e50; margin-bottom:5px;">Order #<?php echo $order['id']; ?></h4>
                        <span class="status status-<?php echo $order['status']; ?>"><?php echo ucfirst($order['status']); ?></span>
                        <span style="color:#666; font-size:13px; margin-left:10px;">
                            <?php echo date('M d, Y H:i', strtotime($order['created_at'])); ?>
                        </span>
                    </div>
                    <div style="text-align:right;">
                        <div style="font-size:24px; font-weight:bold; color:#cfa967;">EGP <?php echo number_format($order['total_amount']); ?></div>
                        <div style="color:#666; font-size:13px;">💵 <?php echo ucfirst($order['payment_method'] ?? 'Cash'); ?> on Delivery</div>
                    </div>
                </div>
                
                <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(250px, 1fr)); gap:20px; margin-bottom:15px;">
                    <!-- Customer Info -->
                    <div style="background:#fff; padding:15px; border-radius:8px;">
                        <h5 style="color:#2c3e50; margin-bottom:10px; font-size:14px;">📦 Shipping Details</h5>
                        <p style="margin:0; color:#333;"><strong><?php echo htmlspecialchars($order['shipping_name'] ?? $order['user_username'] ?? 'N/A'); ?></strong></p>
                        <p style="margin:5px 0; color:#666; font-size:13px;">📧 <?php echo htmlspecialchars($order['shipping_email'] ?? $order['user_email'] ?? 'N/A'); ?></p>
                        <p style="margin:5px 0; color:#666; font-size:13px;">📱 <?php echo htmlspecialchars($order['shipping_phone'] ?? 'N/A'); ?></p>
                        <p style="margin:5px 0; color:#666; font-size:13px;">📍 <?php echo htmlspecialchars($order['shipping_address'] ?? 'N/A'); ?></p>
                        <p style="margin:5px 0; color:#666; font-size:13px;">🏙️ <?php echo htmlspecialchars($order['shipping_city'] ?? 'N/A'); ?></p>
                        <?php if (!empty($order['notes'])): ?>
                            <p style="margin:10px 0 0 0; color:#856404; font-size:13px; background:#fff3cd; padding:8px; border-radius:5px;">📝 <?php echo htmlspecialchars($order['notes']); ?></p>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Order Items -->
                    <div style="background:#fff; padding:15px; border-radius:8px;">
                        <h5 style="color:#2c3e50; margin-bottom:10px; font-size:14px;">🛒 Order Items</h5>
                        <?php while ($item = $order_items_query->fetch_assoc()): ?>
                        <div style="display:flex; gap:10px; align-items:center; padding:8px 0; border-bottom:1px solid #eee;">
                            <img src="<?php echo htmlspecialchars($item['img'] ?? ''); ?>" alt="" style="width:40px; height:40px; object-fit:cover; border-radius:5px;">
                            <div style="flex:1;">
                                <p style="margin:0; font-size:13px; color:#333;"><?php echo htmlspecialchars($item['name'] ?? 'Product'); ?></p>
                                <p style="margin:0; font-size:12px; color:#666;">Qty: <?php echo $item['quantity']; ?> × EGP <?php echo number_format($item['price']); ?></p>
                            </div>
                            <strong style="color:#cfa967;">EGP <?php echo number_format($item['price'] * $item['quantity']); ?></strong>
                        </div>
                        <?php endwhile; ?>
                    </div>
                </div>
                
                <!-- Status Update -->
                <div style="display:flex; justify-content:flex-end; align-items:center; gap:10px; padding-top:15px; border-top:1px solid #ddd;">
                    <span style="color:#666; font-size:14px;">Update Status:</span>
                    <form method="POST" class="status-form">
                        <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                        <select name="status">
                            <option value="pending" <?php echo $order['status'] == 'pending' ? 'selected' : ''; ?>>⏳ Pending</option>
                            <option value="processing" <?php echo $order['status'] == 'processing' ? 'selected' : ''; ?>>🔄 Processing</option>
                            <option value="shipped" <?php echo $order['status'] == 'shipped' ? 'selected' : ''; ?>>🚚 Shipped</option>
                            <option value="delivered" <?php echo $order['status'] == 'delivered' ? 'selected' : ''; ?>>✅ Delivered</option>
                            <option value="cancelled" <?php echo $order['status'] == 'cancelled' ? 'selected' : ''; ?>>❌ Cancelled</option>
                        </select>
                        <button type="submit" name="update_order_status" class="btn btn-primary">Update</button>
                    </form>
                </div>
            </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div style="text-align:center; padding:60px; color:#888; background:#f9f9f9; border-radius:10px;">
                <div style="font-size:48px; margin-bottom:15px;">📋</div>
                <h3 style="margin-bottom:10px;">No orders yet</h3>
                <p>Orders will appear here when customers place them.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'footer.php'; ?>

<script src="mobile-menu.js"></script>
<script>
    function showTab(tabName, element) {
        // Hide all tabs
        document.querySelectorAll('.tab-content').forEach(tab => tab.classList.remove('active'));
        document.querySelectorAll('.tab').forEach(tab => tab.classList.remove('active'));
        
        // Show selected tab
        document.getElementById(tabName).classList.add('active');
        if (element) {
            element.classList.add('active');
        }
    }
</script>
<?php $conn->close(); ?>
</body>
</html>
