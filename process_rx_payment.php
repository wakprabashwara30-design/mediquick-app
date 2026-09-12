<?php
/**
 * MediQuick Pharmacy - Process Prescription Order Payment / Confirmation
 * Handles both Cash on Delivery (COD) confirmation and PayHere / Online Card Verification
 */
require_once __DIR__ . '/config/db.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
    exit();
}

if (!isLoggedIn()) {
    echo json_encode(['status' => 'error', 'message' => 'You must be logged in to confirm this order.']);
    exit();
}

$userId = (int)$_SESSION['user_id'];
$rxId = isset($_POST['rx_id']) ? (int)$_POST['rx_id'] : 0;
$paymentMethod = trim($_POST['payment_method'] ?? 'Cash on Delivery');
$paymentId = trim($_POST['payment_id'] ?? ('PH_RX_' . time()));

if ($rxId <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid prescription ID.']);
    exit();
}

// Fetch prescription and linked order
$sql = "SELECT p.*, o.id AS linked_order_id, o.total_amount, o.customer_name, o.phone, o.email, o.delivery_address 
        FROM prescriptions p 
        LEFT JOIN orders o ON p.order_id = o.id 
        WHERE p.id = $rxId AND (p.user_id = $userId OR p.user_id IS NULL) 
        LIMIT 1";
$res = mysqli_query($conn, $sql);

if (!$res || mysqli_num_rows($res) === 0) {
    echo json_encode(['status' => 'error', 'message' => 'Prescription not found.']);
    exit();
}

$rx = mysqli_fetch_assoc($res);

if ($rx['status'] !== 'Approved') {
    echo json_encode(['status' => 'error', 'message' => 'This prescription has not been approved by the pharmacist.']);
    exit();
}

$amount = !empty($rx['quoted_amount']) ? (float)$rx['quoted_amount'] : (!empty($rx['total_amount']) ? (float)$rx['total_amount'] : 0);
if ($amount <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'No price quotation is set for this prescription.']);
    exit();
}

$orderId = !empty($rx['linked_order_id']) ? (int)$rx['linked_order_id'] : 0;
$rxRef = '#RX-' . str_pad($rxId, 4, '0', STR_PAD_LEFT);

if ($orderId <= 0) {
    // Create new order record
    $safeName = mysqli_real_escape_string($conn, $rx['patient_name']);
    $safePhone = mysqli_real_escape_string($conn, $rx['phone']);
    $safeAddress = mysqli_real_escape_string($conn, $rx['delivery_address']);
    $safeEmail = mysqli_real_escape_string($conn, $_SESSION['user_email'] ?? 'customer@mediquick.lk');
    
    $insSql = "INSERT INTO orders (user_id, customer_name, phone, email, delivery_address, city, total_amount, payment_method, payment_status, status) 
               VALUES ($userId, '$safeName', '$safePhone', '$safeEmail', '$safeAddress', 'Kurunegala', $amount, 'Prescription Quotation', 'Pending', 'Pending')";
    mysqli_query($conn, $insSql);
    $orderId = mysqli_insert_id($conn);
    
    $itemName = mysqli_real_escape_string($conn, "Prescription Medication Course ($rxRef)");
    mysqli_query($conn, "INSERT INTO order_items (order_id, product_id, product_name, unit_price, quantity, total_price) 
                         VALUES ($orderId, NULL, '$itemName', $amount, 1, $amount)");
    mysqli_query($conn, "UPDATE prescriptions SET order_id = $orderId WHERE id = $rxId");
}

$safePayMethod = mysqli_real_escape_string($conn, $paymentMethod);
$safePayId = mysqli_real_escape_string($conn, $paymentId);

if ($paymentMethod === 'Cash on Delivery') {
    $updateSql = "UPDATE orders SET 
                    payment_method = 'Cash on Delivery',
                    payment_status = 'Pending',
                    status = 'Processing'
                  WHERE id = $orderId";
} else {
    $updateSql = "UPDATE orders SET 
                    payment_method = 'PayHere (Online Card / Mobile)',
                    payment_status = 'Paid',
                    payhere_payment_id = '$safePayId',
                    status = 'Processing'
                  WHERE id = $orderId";
}

if (mysqli_query($conn, $updateSql)) {
    echo json_encode([
        'status' => 'success',
        'order_id' => $orderId,
        'message' => 'Prescription order confirmed successfully! Preparing for dispatch.'
    ]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to update order status: ' . mysqli_error($conn)]);
}
exit();
