<?php
/**
 * MediQuick Pharmacy - Order Success & Printable Cash on Delivery Receipt Page
 * Module: CSE4206 - Web Application Development (Kurunegala)
 */
require_once __DIR__ . '/config/db.php';

$orderId = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;

if ($orderId <= 0) {
    header("Location: index.php");
    exit();
}

// Fetch order details
$orderQuery = mysqli_query($conn, "SELECT * FROM orders WHERE id = $orderId LIMIT 1");
if (!$orderQuery || mysqli_num_rows($orderQuery) === 0) {
    header("Location: index.php");
    exit();
}

$order = mysqli_fetch_assoc($orderQuery);

// Fetch order items
$itemsQuery = mysqli_query($conn, "SELECT * FROM order_items WHERE order_id = $orderId");
$itemsList = [];
$itemsSubtotal = 0;

if ($itemsQuery) {
    while ($row = mysqli_fetch_assoc($itemsQuery)) {
        $itemsList[] = $row;
        $itemsSubtotal += (float)$row['total_price'];
    }
}

$deliveryFee = ((float)$order['total_amount'] > $itemsSubtotal) ? ((float)$order['total_amount'] - $itemsSubtotal) : 0.00;

$pageTitle = 'Order Receipt #' . str_pad($orderId, 5, '0', STR_PAD_LEFT) . ' | MediQuick Pharmacy';
include_once __DIR__ . '/includes/header.php';
include_once __DIR__ . '/includes/navbar.php';
?>

<main class="py-4 py-md-5">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-lg-8 col-md-10">
        
        <!-- 1. Top Action & Success Bar (Hidden on Print) -->
        <div class="no-print mb-4 text-center">
          
          <div class="d-inline-flex align-items-center justify-content-center bg-emerald-subtle text-emerald rounded-circle mb-2" style="width: 60px; height: 60px;">
            <i class="bi bi-check-lg fs-2 fw-bold"></i>
          </div>

          <h3 class="fw-bold text-dark mb-1">Thank You! Order Confirmed</h3>
          <p class="text-secondary small mb-3">
            Your order has been placed successfully. A copy of your official pharmacy receipt is generated below.
          </p>

          <div class="d-flex flex-wrap justify-content-center gap-2">
            <button type="button" id="downloadPdfBtn" onclick="downloadReceiptPDF()" class="btn btn-emerald px-4 py-2 shadow-sm fw-bold">
              <i class="bi bi-file-earmark-arrow-down-fill me-2"></i> Download PDF Receipt
            </button>
            <button type="button" onclick="window.print()" class="btn btn-outline-secondary px-3 py-2">
              <i class="bi bi-printer me-1"></i> Print Slip
            </button>
            <a href="shop.php" class="btn btn-outline-secondary px-3 py-2">
              <i class="bi bi-capsule me-1"></i> Continue Shopping
            </a>
            <?php if (isLoggedIn()): ?>
              <a href="my-orders.php" class="btn btn-soft-emerald px-3 py-2">
                <i class="bi bi-bag-check me-1"></i> My Orders
              </a>
            <?php endif; ?>
          </div>

        </div>

        <!-- 2. OFFICIAL PHARMACY RECEIPT / TAX INVOICE CARD (Printable) -->
        <div id="receiptInvoice" class="card card-custom receipt-card p-4 p-md-5 bg-white border shadow-sm">
          
          <!-- Receipt Header -->
          <div class="d-flex flex-wrap justify-content-between align-items-start border-bottom pb-4 mb-4 gap-3">
            <div class="d-flex align-items-center gap-3">
              <div class="rounded-3 d-flex align-items-center justify-content-center text-white p-2.5" style="background-color: #059669; width: 50px; height: 50px;">
                <i class="bi bi-heart-pulse-fill fs-3"></i>
              </div>
              <div>
                <h4 class="fw-bold mb-0 text-dark font-heading">Medi<span class="text-emerald">Quick</span> Pharmacy</h4>
                <div class="small text-secondary fw-semibold">Licensed Online Dispensary & Healthcare Store</div>
                <div class="small text-muted" style="font-size: 0.78rem;">
                  <i class="bi bi-geo-alt me-1 text-emerald"></i> No. 45, Colombo Road, Kurunegala, Sri Lanka
                </div>
              </div>
            </div>

            <div class="text-md-end">
              <?php if (($order['payment_status'] ?? '') === 'Paid' || isset($_GET['payment_status'])): ?>
                <div class="badge bg-success text-white px-3 py-1.5 mb-1 font-mono shadow-sm" style="font-size: 0.82rem;">
                  <i class="bi bi-check-circle-fill me-1"></i> PAID - PAYHERE VERIFIED
                </div>
              <?php else: ?>
                <div class="badge bg-dark text-white px-2.5 py-1 mb-1 font-mono">
                  CASH ON DELIVERY INVOICE
                </div>
              <?php endif; ?>
              <div class="small text-secondary">
                <strong>Invoice #:</strong> <span class="font-mono fw-bold text-dark">#MQ-<?php echo str_pad($order['id'], 5, '0', STR_PAD_LEFT); ?></span>
              </div>
              <div class="small text-muted">
                <strong>Date:</strong> <?php echo date('M d, Y - h:i A', strtotime($order['created_at'])); ?>
              </div>
            </div>
          </div>

          <!-- Customer & Order Meta Information -->
          <div class="row g-3 mb-4 small">
            
            <div class="col-sm-6">
              <div class="p-3 bg-light rounded-3 h-100 border">
                <div class="fw-bold text-dark mb-2 text-uppercase" style="font-size: 0.75rem; letter-spacing: 0.05em;">
                  <i class="bi bi-person-fill text-emerald me-1"></i> Customer & Delivery Details
                </div>
                <div><strong>Name:</strong> <?php echo htmlspecialchars($order['customer_name']); ?></div>
                <div><strong>Phone:</strong> <?php echo htmlspecialchars($order['phone']); ?></div>
                <?php if (!empty($order['email'])): ?>
                  <div><strong>Email:</strong> <?php echo htmlspecialchars($order['email']); ?></div>
                <?php endif; ?>
                <div><strong>Address:</strong> <?php echo htmlspecialchars($order['delivery_address']); ?>, <?php echo htmlspecialchars($order['city']); ?></div>
              </div>
            </div>

            <div class="col-sm-6">
              <div class="p-3 bg-light rounded-3 h-100 border">
                <div class="fw-bold text-dark mb-2 text-uppercase" style="font-size: 0.75rem; letter-spacing: 0.05em;">
                  <i class="bi bi-credit-card-fill text-emerald me-1"></i> Payment & Dispatch Status
                </div>
                <div>
                  <strong>Payment Method:</strong> 
                  <span class="badge bg-emerald-subtle text-emerald border border-emerald ms-1">
                    <?php echo htmlspecialchars($order['payment_method']); ?>
                  </span>
                </div>
                <div class="mt-1">
                  <strong>Payment Status:</strong> 
                  <?php if (($order['payment_status'] ?? '') === 'Paid' || isset($_GET['payment_status'])): ?>
                    <span class="text-success fw-bold"><i class="bi bi-check-circle-fill"></i> Paid (PayHere Sandbox Approved)</span>
                    <?php if (!empty($order['payhere_payment_id'])): ?>
                      <div class="text-muted font-monospace" style="font-size: 0.72rem;">Ref: <?php echo htmlspecialchars($order['payhere_payment_id']); ?></div>
                    <?php endif; ?>
                  <?php else: ?>
                    <span class="text-danger fw-bold"><i class="bi bi-hourglass-split"></i> To be collected upon delivery</span>
                  <?php endif; ?>
                </div>
                <div class="mt-1">
                  <strong>Order Status:</strong> 
                  <span class="badge bg-warning text-dark"><?php echo htmlspecialchars($order['status']); ?></span>
                </div>
                <div class="mt-1 text-muted" style="font-size: 0.75rem;">
                  <strong>Dispatch Hub:</strong> Kurunegala Express Delivery Center
                </div>
              </div>
            </div>

          </div>

          <!-- Purchased Items Table -->
          <div class="table-responsive mb-4">
            <table class="table align-middle small mb-0 border">
              <thead class="table-light">
                <tr>
                  <th style="width: 40px;">#</th>
                  <th>Pharmaceutical / Healthcare Item</th>
                  <th class="text-center" style="width: 100px;">Unit Price</th>
                  <th class="text-center" style="width: 80px;">Qty</th>
                  <th class="text-end" style="width: 120px;">Total (LKR)</th>
                </tr>
              </thead>
              <tbody>
                <?php if (count($itemsList) > 0): ?>
                  <?php $idx = 1; foreach ($itemsList as $item): ?>
                    <tr>
                      <td class="text-muted text-center"><?php echo $idx++; ?></td>
                      <td>
                        <strong class="text-dark"><?php echo htmlspecialchars($item['product_name']); ?></strong>
                      </td>
                      <td class="text-center"><?php echo formatLKR($item['unit_price']); ?></td>
                      <td class="text-center fw-bold"><?php echo $item['quantity']; ?></td>
                      <td class="text-end fw-semibold"><?php echo formatLKR($item['total_price']); ?></td>
                    </tr>
                  <?php endforeach; ?>
                <?php else: ?>
                  <tr><td colspan="5" class="text-center py-3 text-muted">No items recorded.</td></tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>

          <!-- Calculation Totals Row -->
          <div class="row justify-content-end mb-4">
            <div class="col-md-6 col-lg-5">
              <div class="d-flex justify-content-between mb-1 small text-secondary">
                <span>Items Subtotal:</span>
                <span><?php echo formatLKR($itemsSubtotal); ?></span>
              </div>
              <div class="d-flex justify-content-between mb-2 small text-secondary">
                <span>Delivery Charge:</span>
                <span><?php echo ($deliveryFee == 0) ? '<strong class="text-success">FREE</strong>' : formatLKR($deliveryFee); ?></span>
              </div>
              <hr class="my-2">
              <div class="d-flex justify-content-between align-items-center fs-5 fw-bold text-dark border-top pt-2">
                <span>Cash Payable:</span>
                <span class="text-emerald font-mono"><?php echo formatLKR($order['total_amount']); ?></span>
              </div>
            </div>
          </div>

          <!-- Cash on Delivery Collection Notice Box -->
          <div class="alert alert-warning d-flex align-items-center gap-3 p-3 rounded-3 mb-4 small" role="alert">
            <i class="bi bi-cash-coin fs-3 text-warning"></i>
            <div>
              <strong>Cash on Delivery (COD) Instructions:</strong><br>
              Please hand over the exact cash amount of <strong><?php echo formatLKR($order['total_amount']); ?></strong> to our delivery rider when receiving the package in Kurunegala.
            </div>
          </div>
        </div>

      </div>
    </div>
  </div>
</main>

<!-- html2pdf.js CDN for Direct Client-side PDF Downloading -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<script>
function downloadReceiptPDF() {
  const btn = document.getElementById('downloadPdfBtn');
  const originalText = btn.innerHTML;
  btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status"></span> Generating PDF...';
  btn.disabled = true;

  const element = document.getElementById('receiptInvoice');
  const opt = {
    margin:       [8, 8, 8, 8],
    filename:     'MediQuick_Receipt_MQ-<?php echo str_pad($order['id'], 5, '0', STR_PAD_LEFT); ?>.pdf',
    image:        { type: 'jpeg', quality: 0.98 },
    html2canvas:  { scale: 2, useCORS: true, logging: false },
    jsPDF:        { unit: 'mm', format: 'a4', orientation: 'portrait' }
  };

  html2pdf().set(opt).from(element).save().then(function() {
    btn.innerHTML = '<i class="bi bi-check2-circle me-2"></i> PDF Downloaded!';
    btn.classList.remove('btn-emerald');
    btn.classList.add('btn-success');
    setTimeout(() => {
      btn.innerHTML = originalText;
      btn.disabled = false;
      btn.classList.remove('btn-success');
      btn.classList.add('btn-emerald');
    }, 3000);
  }).catch(function(err) {
    console.error(err);
    btn.innerHTML = originalText;
    btn.disabled = false;
    window.print();
  });
}
</script>

<?php include_once __DIR__ . '/includes/footer.php'; ?>
