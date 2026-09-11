<?php
/**
 * MediQuick Pharmacy - Official PayHere Sandbox Configuration
 * Central Bank of Sri Lanka (CBSL) Approved Payment Gateway Integration
 */

// Official PayHere Sandbox Credentials (Test Mode)
define('PAYHERE_MERCHANT_ID', '1237973'); 
define('PAYHERE_MERCHANT_SECRET', 'NDUxNjk1ODk0MzgwMzk3NzAyODI3OTEwOTgwMjUxMTU3MjQxNjM5'); 
define('PAYHERE_CURRENCY', 'LKR');
define('PAYHERE_SANDBOX_MODE', true);

/**
 * Generate PayHere MD5 Security Hash
 * Format: strtoupper(md5(merchant_id + order_id + amountFormatted + currency + strtoupper(md5(merchant_secret))))
 * 
 * @param int|string $orderId
 * @param float $amount
 * @param string $currency
 * @return string
 */
function generatePayHereHash($orderId, $amount, $currency = 'LKR') {
    $merchantId = PAYHERE_MERCHANT_ID;
    $merchantSecret = PAYHERE_MERCHANT_SECRET;
    $amountFormatted = number_format((float)$amount, 2, '.', '');
    
    $secretHash = strtoupper(md5($merchantSecret));
    $rawString = $merchantId . $orderId . $amountFormatted . $currency . $secretHash;
    
    return strtoupper(md5($rawString));
}
?>
