<?php
/**
 * MediQuick Pharmacy - Get PayHere Parameters for Prescription Order
 * Module: Clinical Prescription Quotation Checkout
 */
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/payhere.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
    exit();
}

if (!isLoggedIn()) {
    echo json_encode(['status' => 'error', 'message' => 'You must be logged in to proceed with payment.']);
    exit();
}

$userId = (int)$_SESSION['user_id'];
$rxId = isset($_POST['rx_id']) ? (int)$_POST['rx_id'] : 0;

if ($rxId <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid prescription ID.']);
    exit();
}

// Fetch prescription and linked order
$sql = "SELECT p.*, o.id AS linked_order_id, o.total_amount, o.customer_name, o.phone, o.email, o.delivery_address, o.city 
        FROM prescriptions p 
        LEFT JOIN orders o ON p.order_id = o.id 
        WHERE p.id = $rxId AND (p.user_id = $userId OR p.user_id IS NULL) 
        LIMIT 1";
$res = mysqli_query($conn, $sql);

if (!$res || mysqli_num_rows($res) === 0) {
    echo json_encode(['status' => 'error', 'message' => 'Prescription record not found.']);
    exit();
}

$rx = mysqli_fetch_assoc($res);

if ($rx['status'] !== 'Approved') {
    echo json_encode(['status' => 'error', 'message' => 'This prescription has not been approved yet.']);
    exit();
}

$amount = !empty($rx['quoted_amount']) ? (float)$rx['quoted_amount'] : (!empty($rx['total_amount']) ? (float)$rx['total_amount'] : 0);
if ($amount <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'No price quotation is set for this prescription yet.']);
    exit();
}

$orderId = !empty($rx['linked_order_id']) ? (int)$rx['linked_order_id'] : 0;
if ($orderId <= 0) {
    // Create linked order if missing
    $safeName = mysqli_real_escape_string($conn, $rx['patient_name']);
    $safePhone = mysqli_real_escape_string($conn, $rx['phone']);
    $safeAddress = mysqli_real_escape_string($conn, $rx['delivery_address']);
    $safeEmail = mysqli_real_escape_string($conn, $_SESSION['user_email'] ?? 'customer@mediquick.lk');
    
    $insSql = "INSERT INTO orders (user_id, customer_name, phone, email, delivery_address, city, total_amount, payment_method, payment_status, status) 
               VALUES ($userId, '$safeName', '$safePhone', '$safeEmail', '$safeAddress', 'Kurunegala', $amount, 'Prescription Quotation', 'Pending', 'Pending')";
    mysqli_query($conn, $insSql);
    $orderId = mysqli_insert_id($conn);
    
    $rxRef = '#RX-' . str_pad($rxId, 4, '0', STR_PAD_LEFT);
    $itemName = mysqli_real_escape_string($conn, "Prescription Medication Course ($rxRef)");
    mysqli_query($conn, "INSERT INTO order_items (order_id, product_id, product_name, unit_price, quantity, total_price) 
                         VALUES ($orderId, NULL, '$itemName', $amount, 1, $amount)");
    mysqli_query($conn, "UPDATE prescriptions SET order_id = $orderId WHERE id = $rxId");
}

// Split name
$nameParts = explode(' ', $rx['patient_name'], 2);
$firstName = $nameParts[0] ?? 'Customer';
$lastName = $nameParts[1] ?? 'MediQuick';

$orderRef = 'MQ-RX-' . $orderId . '-' . time();
$hash = generatePayHereHash($orderRef, $amount, PAYHERE_CURRENCY);

$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || ($_SERVER['SERVER_PORT'] ?? 80) == 443) ? "https://" : "http://";
$baseUrl = $protocol . ($_SERVER['HTTP_HOST'] ?? 'localhost:8000');

echo json_encode([
    'status' => 'success',
    'sandbox' => PAYHERE_SANDBOX_MODE,
    'merchant_id' => PAYHERE_MERCHANT_ID,
    'order_id' => (string)$orderRef,
    'internal_order_id' => $orderId,
    'items' => 'MediQuick Prescription #RX-' . str_pad($rxId, 4, '0', STR_PAD_LEFT),
    'amount' => number_format((float)$amount, 2, '.', ''),
    'currency' => PAYHERE_CURRENCY,
    'hash' => $hash,
    'first_name' => $firstName,
    'last_name' => $lastName,
    'email' => $rx['email'] ?? ($_SESSION['user_email'] ?? 'customer@mediquick.lk'),
    'phone' => $rx['phone'],
    'address' => $rx['delivery_address'],
    'city' => 'Kurunegala',
    'country' => 'Sri Lanka',
    'return_url' => $baseUrl . '/order-success.php?order_id=' . $orderId,
    'cancel_url' => $baseUrl . '/my-orders.php',
    'notify_url' => $baseUrl . '/payhere_notify.php'
]);
exit();
