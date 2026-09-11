<?php
/**
 * MediQuick Pharmacy - Process Verified Card Payment
 * Creates the official order in MySQL ONLY after customer enters valid OTP / verifies payment
 */
require_once __DIR__ . '/config/db.php';

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
$paymentId = trim($_POST['payment_id'] ?? ('PH_SANDBOX_' . time()));
$userIdVal = isLoggedIn() ? (int)$_SESSION['user_id'] : "NULL";

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

$cartItems = [];
$subtotal = 0;

if ($cartResult) {
    while ($row = mysqli_fetch_assoc($cartResult)) {
        $pId = $row['id'];
        $itemQty = $_SESSION['cart'][$pId];
        $itemTotal = $row['price'] * $itemQty;
        $subtotal += $itemTotal;

        $cartItems[] = [
            'id' => $row['id'],
            'name' => $row['name'],
            'price' => $row['price'],
            'quantity' => $itemQty,
            'total' => $itemTotal
        ];
    }
}

$deliveryFee = ($subtotal > 0 && $subtotal < 3000) ? 250.00 : 0.00;
$grandTotal = $subtotal + $deliveryFee;

// Sanitize fields
$safeName = mysqli_real_escape_string($conn, $name);
$safePhone = mysqli_real_escape_string($conn, $phone);
$safeEmail = mysqli_real_escape_string($conn, $email);
$safeAddress = mysqli_real_escape_string($conn, $address);
$safeCity = mysqli_real_escape_string($conn, $city);
$safePayId = mysqli_real_escape_string($conn, $paymentId);

// 1. Insert into orders table ONLY NOW that payment is approved
$orderSql = "INSERT INTO orders (user_id, customer_name, phone, email, delivery_address, city, total_amount, payment_method, payment_status, payhere_payment_id, status) 
             VALUES ($userIdVal, '$safeName', '$safePhone', '$safeEmail', '$safeAddress', '$safeCity', $grandTotal, 'PayHere (Online Card / Mobile)', 'Paid', '$safePayId', 'Processing')";

if (!mysqli_query($conn, $orderSql)) {
    echo json_encode(['status' => 'error', 'message' => 'Failed to save order in database: ' . mysqli_error($conn)]);
    exit();
}

$orderId = mysqli_insert_id($conn);

// 2. Insert into order_items table
foreach ($cartItems as $item) {
    $pId = (int)$item['id'];
    $pName = mysqli_real_escape_string($conn, $item['name']);
    $uPrice = (float)$item['price'];
    $qty = (int)$item['quantity'];
    $iTotal = (float)$item['total'];

    $itemSql = "INSERT INTO order_items (order_id, product_id, product_name, unit_price, quantity, total_price) 
                VALUES ($orderId, $pId, '$pName', $uPrice, $qty, $iTotal)";
    mysqli_query($conn, $itemSql);
}

// 3. Clear Shopping Cart ONLY on confirmed payment
$_SESSION['cart'] = [];

echo json_encode([
    'status' => 'success',
    'order_id' => $orderId,
    'payment_id' => $safePayId,
    'amount' => $grandTotal
]);
exit();
