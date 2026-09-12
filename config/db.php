<?php
/**
 * MediQuick Pharmacy - Core Database Connection & Session Initializer
 * Simple, standard MySQLi configuration for 1st-year university level.
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Auto-detect environment: Localhost vs Live Hosting (InfinityFree)
$is_local = in_array($_SERVER['HTTP_HOST'] ?? 'localhost', ['localhost', '127.0.0.1', 'localhost:8000', '127.0.0.1:8000']);

if ($is_local) {
    // Local Development Credentials (XAMPP)
    $db_host = "127.0.0.1";
    $db_user = "root";
    $db_pass = "";
    $db_name = "mediquick_db";
} else {
    // InfinityFree Live Server Credentials
    $db_host = "sql107.infinityfree.com";
    $db_user = "if0_42895541";
    $db_pass = "Vgj7duCGgBqPbyJ";
    $db_name = "if0_42895541_mediquick";
}

// Create Connection
$conn = mysqli_connect($db_host, $db_user, $db_pass, $db_name);

// Check Connection
if (!$conn) {
    die("<div style='font-family:sans-serif; padding:20px; background:#fee2e2; color:#991b1b; border-radius:8px; margin:20px;'>
        <h3>Database Connection Error</h3>
        <p>Could not connect to MySQL server. Please verify database credentials.</p>
        <p><strong>Error details:</strong> " . mysqli_connect_error() . "</p>
    </div>");
}

// Set UTF-8 character set
mysqli_set_charset($conn, "utf8mb4");

/**
 * Helper: Check if a user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Helper: Check if logged-in user is an Admin
 */
function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

/**
 * Helper: Get total quantity of items in the shopping cart
 */
function getCartCount() {
    if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
        return 0;
    }
    $count = 0;
    foreach ($_SESSION['cart'] as $qty) {
        $count += (int)$qty;
    }
    return $count;
}

/**
 * Helper: Format Currency in Sri Lankan Rupees (LKR)
 */
function formatLKR($amount) {
    return 'Rs. ' . number_format((float)$amount, 2);
}
?>
