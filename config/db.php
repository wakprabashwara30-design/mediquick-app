<?php
/**
 * MediQuick Pharmacy - Core Database Connection & Session Initializer
 * Simple, standard MySQLi configuration for 1st-year university level.
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database Credentials (XAMPP / WAMP / MAMP defaults)
$db_host = "localhost";
$db_user = "root";
$db_pass = "";
$db_name = "mediquick_db";

// Create Connection
$conn = mysqli_connect($db_host, $db_user, $db_pass, $db_name);

// Check Connection
if (!$conn) {
    die("<div style='font-family:sans-serif; padding:20px; background:#fee2e2; color:#991b1b; border-radius:8px; margin:20px;'>
        <h3>Database Connection Error</h3>
        <p>Could not connect to MySQL server. Please make sure MySQL is running in XAMPP/WAMP and the database <strong>mediquick_db</strong> is imported.</p>
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
