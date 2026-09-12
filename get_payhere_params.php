<?php
/**
 * MediQuick Pharmacy - Get PayHere Official Payment Parameters
 * Generates Sandbox parameters and valid MD5 Hash for PayHere JS SDK
 */
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/payhere.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
    exit();
}

if (!isset($_SESSION['cart']) || count($_SESSION['cart']) === 0) {
    echo json_encode(['status' => 'error', 'message' => 'Your shopping cart is empty.']);
    exit();
}

$name = trim($_POST['customer_name'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$email = trim($_POST['email'] ?? 'customer@mediquick.lk');
$address = trim($_POST['delivery_address'] ?? '');
$city = trim($_POST['city'] ?? 'Kurunegala');

if (empty($name) || empty($phone) || empty($address)) {
    echo json_encode(['status' => 'error', 'message' => 'Please fill in Name, Phone, and Delivery Address.']);
    exit();
}

// Calculate Cart Totals
$productIds = array_keys($_SESSION['cart']);
$cleanIds = array_map('intval', $productIds);
$idsString = implode(',', $cleanIds);

$cartQuery = "SELECT * FROM products WHERE id IN ($idsString)";
$cartResult = mysqli_query($conn, $cartQuery);

$subtotal = 0;
if ($cartResult) {
    while ($row = mysqli_fetch_assoc($cartResult)) {
        $pId = $row['id'];
        $itemQty = $_SESSION['cart'][$pId];
        $subtotal += ($row['price'] * $itemQty);
    }
}

$deliveryFee = ($subtotal > 0 && $subtotal < 3000) ? 250.00 : 0.00;
$grandTotal = $subtotal + $deliveryFee;

// Split name into first and last name
$nameParts = explode(' ', $name, 2);
$firstName = $nameParts[0] ?? 'Customer';
$lastName = $nameParts[1] ?? 'MediQuick';

// Generate Unique PayHere Order ID
$orderRef = 'MQ' . time();

// Generate MD5 Checksum Hash using official secret
$hash = generatePayHereHash($orderRef, $grandTotal, PAYHERE_CURRENCY);

// Dynamic Base URL detection for Localhost and Live Hosting
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || ($_SERVER['SERVER_PORT'] ?? 80) == 443) ? "https://" : "http://";
$baseUrl = $protocol . ($_SERVER['HTTP_HOST'] ?? 'localhost:8000');

echo json_encode([
    'status' => 'success',
    'sandbox' => PAYHERE_SANDBOX_MODE,
    'merchant_id' => PAYHERE_MERCHANT_ID,
    'order_id' => (string)$orderRef,
    'items' => 'MediQuick Pharmacy Order',
    'amount' => number_format((float)$grandTotal, 2, '.', ''),
    'currency' => PAYHERE_CURRENCY,
    'hash' => $hash,
    'first_name' => $firstName,
    'last_name' => $lastName,
    'email' => $email,
    'phone' => $phone,
    'address' => $address,
    'city' => $city,
    'country' => 'Sri Lanka',
    'return_url' => $baseUrl . '/order-success.php',
    'cancel_url' => $baseUrl . '/checkout.php',
    'notify_url' => $baseUrl . '/payhere_notify.php'
]);
exit();
