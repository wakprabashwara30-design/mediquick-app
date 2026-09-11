<?php
/**
 * MediQuick Pharmacy - PayHere Webhook IPN Listener
 * Verifies PayHere MD5 Signature and updates order payment status
 */
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/payhere.php';

$merchantId = $_POST['merchant_id'] ?? '';
$orderId = (int)($_POST['order_id'] ?? 0);
$payhereAmount = $_POST['payhere_amount'] ?? '';
$payhereCurrency = $_POST['payhere_currency'] ?? '';
$statusCode = $_POST['status_code'] ?? '';
$md5sig = $_POST['md5sig'] ?? '';
$paymentId = $_POST['payment_id'] ?? ('PH_NOTIFY_' . time());

$merchantSecret = PAYHERE_MERCHANT_SECRET;
$localMd5sig = strtoupper(
    md5(
        $merchantId . 
        $orderId . 
        $payhereAmount . 
        $payhereCurrency . 
        $statusCode . 
        strtoupper(md5($merchantSecret))
    )
);

if (($localMd5sig === $md5sig) && ($statusCode == 2)) {
    // Payment is Approved (Status Code 2 = SUCCESS)
    $safePayId = mysqli_real_escape_string($conn, $paymentId);
    $updateSql = "UPDATE orders SET payment_status = 'Paid', payhere_payment_id = '$safePayId', status = 'Processing' WHERE id = $orderId";
    mysqli_query($conn, $updateSql);
    http_response_code(200);
    echo "Payment verified and recorded successfully.";
} else {
    // Payment Failed or Invalid Signature
    http_response_code(400);
    echo "Invalid Signature or Payment Unsuccessful.";
}
?>
