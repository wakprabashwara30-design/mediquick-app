<?php
/**
 * MediQuick Pharmacy - My Orders & Prescriptions Page
 * University 1st-Year Web Application (PHP & MySQL)
 */
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/payhere.php';

// Require Login
if (!isLoggedIn()) {
    header("Location: login.php");
    exit();
}

$userId = (int)$_SESSION['user_id'];

// Fetch valid user orders (COD orders or paid online orders)
$ordersQuery = mysqli_query($conn, "SELECT * FROM orders WHERE user_id = $userId AND (payment_method = 'Cash on Delivery' OR payment_status = 'Paid') ORDER BY id DESC");

// Fetch user prescriptions with linked order details
$rxQuery = mysqli_query($conn, "SELECT p.*, o.id AS linked_order_id, o.payment_status AS order_payment_status, o.payment_method AS order_payment_method, o.status AS order_status, o.total_amount AS order_total_amount 
                                FROM prescriptions p 
                                LEFT JOIN orders o ON p.order_id = o.id 
                                WHERE p.user_id = $userId 
                                ORDER BY p.id DESC");

$pageTitle = 'My Orders & Prescriptions | MediQuick Pharmacy';
include_once __DIR__ . '/includes/header.php';
include_once __DIR__ . '/includes/navbar.php';
?>

<main class="py-4">
  <div class="container">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
      <div>
        <h2 class="fw-bold mb-0">My Healthcare Dashboard</h2>
        <p class="text-muted small mb-0">Welcome back, <strong><?php echo htmlspecialchars($_SESSION['user_name']); ?></strong> (<?php echo htmlspecialchars($_SESSION['user_email'] ?? ''); ?>)</p>
      </div>
      <div class="d-flex gap-2">
        <a href="prescription.php" class="btn btn-outline-emerald btn-sm">
          <i class="bi bi-cloud-arrow-up me-1"></i> Upload Rx
        </a>
        <a href="shop.php" class="btn btn-emerald btn-sm">
          <i class="bi bi-cart-plus me-1"></i> New Order
        </a>
      </div>
    </div>

    <!-- Navigation Tabs -->
    <ul class="nav nav-tabs mb-4" id="accountTabs" role="tablist">
      <li class="nav-item" role="presentation">
        <button class="nav-link active fw-semibold" id="orders-tab" data-bs-toggle="tab" data-bs-target="#orders-tab-pane" type="button" role="tab" aria-controls="orders-tab-pane" aria-selected="true">
          <i class="bi bi-bag-check me-1"></i> My Store Orders (<?php echo $ordersQuery ? mysqli_num_rows($ordersQuery) : 0; ?>)
        </button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link fw-semibold" id="rx-tab" data-bs-toggle="tab" data-bs-target="#rx-tab-pane" type="button" role="tab" aria-controls="rx-tab-pane" aria-selected="false">
          <i class="bi bi-file-earmark-medical me-1"></i> Uploaded Prescriptions & Quotations (<?php echo $rxQuery ? mysqli_num_rows($rxQuery) : 0; ?>)
        </button>
      </li>
    </ul>

    <!-- Tab Contents -->
    <div class="tab-content" id="accountTabsContent">
      
      <!-- 1. ORDERS TAB -->
      <div class="tab-pane fade show active" id="orders-tab-pane" role="tabpanel" aria-labelledby="orders-tab" tabindex="0">
        <?php if ($ordersQuery && mysqli_num_rows($ordersQuery) > 0): ?>
          <div class="card card-custom p-3 bg-white">
            <div class="table-responsive">
              <table class="table align-middle mb-0">
                <thead class="table-light small">
                  <tr>
                    <th>Order Ref</th>
                    <th>Date</th>
                    <th>Delivery City</th>
                    <th>Total Amount</th>
                    <th>Payment</th>
                    <th>Status</th>
                    <th class="text-end">Details</th>
                  </tr>
                </thead>
                <tbody class="small">
                  <?php while ($ord = mysqli_fetch_assoc($ordersQuery)): ?>
                    <tr>
                      <td class="font-mono fw-bold">#MQ-<?php echo str_pad($ord['id'], 5, '0', STR_PAD_LEFT); ?></td>
                      <td class="text-secondary"><?php echo date('M d, Y', strtotime($ord['created_at'])); ?></td>
                      <td><?php echo htmlspecialchars($ord['city']); ?></td>
                      <td class="fw-bold text-emerald"><?php echo formatLKR($ord['total_amount']); ?></td>
                      <td>
                        <div class="fw-semibold text-dark"><?php echo htmlspecialchars($ord['payment_method']); ?></div>
                        <?php if (($ord['payment_status'] ?? '') === 'Paid'): ?>
                          <span class="badge bg-success-subtle text-success border border-success-subtle" style="font-size: 0.68rem;">
                            <i class="bi bi-check-circle-fill me-1"></i> Paid Online
                          </span>
                        <?php else: ?>
                          <span class="badge bg-secondary-subtle text-secondary border" style="font-size: 0.68rem;">
                            <i class="bi bi-hourglass-split me-1"></i> To be collected
                          </span>
                        <?php endif; ?>
                      </td>
                      <td>
                        <?php 
                          $st = $ord['status'];
                          $badgeClass = 'bg-secondary';
                          if ($st === 'Pending') $badgeClass = 'bg-warning text-dark';
                          elseif ($st === 'Processing') $badgeClass = 'bg-info text-dark';
                          elseif ($st === 'Delivered') $badgeClass = 'bg-success';
                          elseif ($st === 'Cancelled') $badgeClass = 'bg-danger';
                        ?>
                        <span class="badge <?php echo $badgeClass; ?>"><?php echo htmlspecialchars($st); ?></span>
                      </td>
                      <td class="text-end">
                        <a href="order-success.php?order_id=<?php echo $ord['id']; ?>" class="btn btn-sm btn-outline-secondary">
                          <i class="bi bi-eye"></i> View
                        </a>
                      </td>
                    </tr>
                  <?php endwhile; ?>
                </tbody>
              </table>
            </div>
          </div>
        <?php else: ?>
          <div class="card card-custom p-4 text-center bg-white">
            <p class="text-muted mb-0">You have not placed any store orders yet.</p>
          </div>
        <?php endif; ?>
      </div>

      <!-- 2. PRESCRIPTIONS TAB -->
      <div class="tab-pane fade" id="rx-tab-pane" role="tabpanel" aria-labelledby="rx-tab" tabindex="0">
        <?php if ($rxQuery && mysqli_num_rows($rxQuery) > 0): ?>
          <div class="card card-custom p-3 bg-white">
            <div class="table-responsive">
              <table class="table align-middle mb-0">
                <thead class="table-light small">
                  <tr>
                    <th>Rx Ref</th>
                    <th>Prescription Slip</th>
                    <th>Patient / Date</th>
                    <th>Verification & Quote</th>
                    <th>Pharmacist Instructions</th>
                    <th class="text-end">Action & Payment</th>
                  </tr>
                </thead>
                <tbody class="small">
                  <?php while ($rx = mysqli_fetch_assoc($rxQuery)): 
                    $rxIdStr = '#RX-' . str_pad($rx['id'], 4, '0', STR_PAD_LEFT);
                    $hasQuote = !empty($rx['quoted_amount']) && (float)$rx['quoted_amount'] > 0;
                    $isPaid = ($rx['order_payment_status'] ?? '') === 'Paid';
                    $isCodConfirmed = (($rx['order_payment_method'] ?? '') === 'Cash on Delivery') && in_array($rx['order_status'] ?? '', ['Processing', 'Delivered']);
                    $hasLinkedOrder = !empty($rx['linked_order_id']);
                  ?>
                    <tr>
                      <td class="font-mono fw-bold"><?php echo $rxIdStr; ?></td>
                      <td>
                        <button type="button" class="btn btn-sm btn-outline-emerald py-1 px-2 d-inline-flex align-items-center gap-1 shadow-none"
                                onclick="previewRxModal('<?php echo htmlspecialchars($rx['image'], ENT_QUOTES); ?>', '<?php echo $rxIdStr; ?>', '<?php echo htmlspecialchars($rx['patient_name'], ENT_QUOTES); ?>')">
                          <i class="bi bi-image"></i> View Slip
                        </button>
                      </td>
                      <td>
                        <div class="fw-bold text-dark"><?php echo htmlspecialchars($rx['patient_name']); ?></div>
                        <div class="text-muted" style="font-size: 0.76rem;">
                          <i class="bi bi-calendar3 me-1"></i> <?php echo date('M d, Y', strtotime($rx['created_at'])); ?>
                        </div>
                      </td>
                      <td>
                        <?php 
                          $rxSt = $rx['status'];
                          if ($rxSt === 'Approved'): ?>
                            <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 rounded-pill fw-semibold">
                              <i class="bi bi-check-circle-fill me-1"></i> Approved
                            </span>
                            <?php if ($hasQuote): ?>
                              <div class="mt-1">
                                <span class="fw-bold text-emerald font-mono" style="font-size: 0.88rem;">
                                  <?php echo formatLKR($rx['quoted_amount']); ?>
                                </span>
                              </div>
                            <?php endif; ?>
                          <?php elseif ($rxSt === 'Rejected'): ?>
                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1 rounded-pill fw-semibold">
                              <i class="bi bi-x-circle-fill me-1"></i> Rejected
                            </span>
                          <?php else: ?>
                            <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2 py-1 rounded-pill fw-semibold">
                              <i class="bi bi-hourglass-split me-1"></i> Pending Review
                            </span>
                          <?php endif; ?>
                      </td>
                      <td>
                        <?php if (!empty($rx['pharmacist_notes'])): ?>
                          <div class="p-2 rounded bg-light border text-secondary" style="font-size: 0.78rem; max-width: 260px;">
                            <i class="bi bi-chat-quote-fill text-emerald me-1"></i> <?php echo htmlspecialchars($rx['pharmacist_notes']); ?>
                          </div>
                        <?php else: ?>
                          <span class="text-muted small">Pending pharmacist verification & dosage review.</span>
                        <?php endif; ?>
                      </td>
                      <td class="text-end">
                        <?php if ($rxSt === 'Approved' && $hasQuote): ?>
                          <?php if ($isPaid): ?>
                            <div class="d-flex flex-column align-items-end gap-1">
                              <span class="badge bg-success-subtle text-success border border-success-subtle" style="font-size: 0.7rem;">
                                <i class="bi bi-check2-circle me-0.5"></i> Paid Online
                              </span>
                              <a href="order-success.php?order_id=<?php echo $rx['linked_order_id']; ?>" class="btn btn-sm btn-outline-emerald py-1 px-2.5">
                                <i class="bi bi-receipt me-1"></i> Receipt
                              </a>
                            </div>
                          <?php elseif ($isCodConfirmed): ?>
                            <div class="d-flex flex-column align-items-end gap-1">
                              <span class="badge bg-info-subtle text-info-emphasis border" style="font-size: 0.7rem;">
                                <i class="bi bi-truck me-0.5"></i> COD Confirmed
                              </span>
                              <a href="order-success.php?order_id=<?php echo $rx['linked_order_id']; ?>" class="btn btn-sm btn-outline-emerald py-1 px-2.5">
                                <i class="bi bi-receipt me-1"></i> Receipt
                              </a>
                            </div>
                          <?php else: ?>
                            <button type="button" class="btn btn-sm btn-emerald fw-bold px-3 py-1.5 shadow-sm d-inline-flex align-items-center gap-1.5"
                                    onclick="openRxCheckoutModal(<?php echo htmlspecialchars(json_encode($rx), ENT_QUOTES, 'UTF-8'); ?>)">
                              <i class="bi bi-credit-card-2-front-fill"></i> Pay & Confirm
                            </button>
                          <?php endif; ?>
                        <?php elseif ($rxSt === 'Approved' && !$hasQuote): ?>
                          <span class="text-muted small">Pharmacist will call to confirm stock</span>
                        <?php elseif ($rxSt === 'Rejected'): ?>
                          <span class="text-danger small">Invalid prescription slip</span>
                        <?php else: ?>
                          <span class="text-muted small"><i class="bi bi-clock me-1"></i> In Review Queue</span>
                        <?php endif; ?>
                      </td>
                    </tr>
                  <?php endwhile; ?>
                </tbody>
              </table>
            </div>
          </div>
        <?php else: ?>
          <div class="card card-custom p-4 text-center bg-white">
            <p class="text-muted mb-2">No prescriptions uploaded yet.</p>
            <div>
              <a href="prescription.php" class="btn btn-emerald btn-sm">Upload Prescription Now</a>
            </div>
          </div>
        <?php endif; ?>
      </div>

    </div>

  </div>
</main>

<!-- ========================================================================= -->
<!-- 1. PRESCRIPTION IMAGE PREVIEW MODAL -->
<!-- ========================================================================= -->
<div class="modal fade" id="rxImageModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header bg-dark text-white py-2">
        <h6 class="modal-title font-heading fw-bold" id="rxModalTitle">
          <i class="bi bi-file-earmark-medical text-emerald me-2"></i> Uploaded Prescription Document
        </h6>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-3 text-center bg-light">
        <div class="mb-2 text-muted small fw-semibold" id="rxModalSub"></div>
        <div style="max-height: 70vh; overflow: auto;" class="border rounded bg-white p-2 text-center">
          <img id="rxModalImg" src="" alt="Doctor's Prescription" class="img-fluid rounded shadow-sm" style="max-height: 65vh; object-fit: contain;">
        </div>
      </div>
      <div class="modal-footer py-2 bg-white">
        <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Close</button>
        <a id="rxModalOpenBtn" href="#" target="_blank" class="btn btn-sm btn-emerald">
          <i class="bi bi-box-arrow-up-right me-1"></i> Open Full View
        </a>
      </div>
    </div>
  </div>
</div>

<!-- ========================================================================= -->
<!-- 2. PRESCRIPTION CHECKOUT & PAYMENT MODAL -->
<!-- ========================================================================= -->
<div class="modal fade" id="rxCheckoutModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">
      
      <div class="modal-header bg-dark text-white py-3 px-4">
        <div class="d-flex align-items-center gap-2">
          <div class="rounded-3 bg-emerald text-white d-flex align-items-center justify-content-center" style="width: 34px; height: 34px;">
            <i class="bi bi-receipt-cutoff fs-5"></i>
          </div>
          <div>
            <h6 class="modal-title font-heading fw-bold mb-0" id="rxPayModalTitle">Confirm Prescription Order</h6>
            <div class="text-white-50" style="font-size: 0.74rem;">SLMC Verified Medication Course</div>
          </div>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body p-4">
        
        <!-- Quotation Summary Card -->
        <div class="p-3 rounded-3 bg-emerald-subtle border border-emerald-subtle mb-3">
          <div class="d-flex justify-content-between align-items-center mb-1">
            <span class="text-secondary small fw-semibold">Prescription Total Quotation:</span>
            <span class="fw-bold text-emerald font-heading fs-5" id="rxPayAmountDisplay">Rs. 0.00</span>
          </div>
          <div class="small text-muted" style="font-size: 0.75rem;" id="rxPayPatientDisplay">Patient: &mdash;</div>
        </div>

        <!-- Pharmacist Instructions Box -->
        <div class="mb-3 p-2.5 rounded-3 bg-light border small">
          <div class="fw-bold text-dark mb-1 d-flex align-items-center gap-1" style="font-size: 0.78rem;">
            <i class="bi bi-clipboard2-pulse text-emerald"></i> Pharmacist Notes & Dosage:
          </div>
          <div class="text-secondary" id="rxPayNotesDisplay" style="font-size: 0.76rem;">Verified prescription medication.</div>
        </div>

        <!-- Payment Method Selection -->
        <label class="form-label fw-bold text-dark small mb-2">Select Payment Method:</label>
        <div class="d-flex flex-column gap-2 mb-3">
          
          <label class="d-flex align-items-center gap-2.5 p-3 rounded-3 border cursor-pointer hover-shadow" style="background-color: #f8fafc;" id="optPayHereLabel">
            <input type="radio" name="rx_payment_option" id="rxPayOptPayHere" value="PayHere" class="form-check-input mt-0" checked>
            <div class="flex-grow-1">
              <div class="d-flex justify-content-between align-items-center">
                <strong class="text-dark small"><i class="bi bi-credit-card-2-front-fill text-emerald me-1"></i> Pay Online (PayHere / Card)</strong>
                <span class="badge bg-emerald text-white px-2 py-0.5" style="font-size: 0.65rem;">Instant</span>
              </div>
              <span class="text-muted d-block" style="font-size: 0.72rem;">Visa, Mastercard, eZ Cash, Genie & Web Mobile Banking</span>
            </div>
          </label>

          <label class="d-flex align-items-center gap-2.5 p-3 rounded-3 border cursor-pointer hover-shadow" style="background-color: #f8fafc;" id="optCodLabel">
            <input type="radio" name="rx_payment_option" id="rxPayOptCod" value="COD" class="form-check-input mt-0">
            <div class="flex-grow-1">
              <div class="d-flex justify-content-between align-items-center">
                <strong class="text-dark small"><i class="bi bi-cash-stack text-success me-1"></i> Cash on Delivery (COD)</strong>
                <span class="badge bg-secondary text-white px-2 py-0.5" style="font-size: 0.65rem;">Pay at Doorstep</span>
              </div>
              <span class="text-muted d-block" style="font-size: 0.72rem;">Pay cash to the delivery rider when receiving medications</span>
            </div>
          </label>

        </div>

        <!-- Action Button -->
        <input type="hidden" id="currentActiveRxId" value="0">
        <button type="button" id="rxSubmitPaymentBtn" onclick="submitRxCheckout()" class="btn btn-emerald w-100 py-2.5 fw-bold shadow-sm d-flex align-items-center justify-content-center gap-2">
          <i class="bi bi-lock-fill"></i> <span id="rxSubmitBtnText">Proceed with Online Payment</span>
        </button>

      </div>

    </div>
  </div>
</div>

<!-- PayHere Official Sandbox JavaScript SDK -->
<script type="text/javascript" src="https://www.payhere.lk/lib/payhere.js"></script>

<script>
// 1. Preview Prescription Slip Modal
function previewRxModal(imgSrc, refCode, patientName) {
  document.getElementById('rxModalTitle').innerHTML = '<i class="bi bi-file-earmark-medical text-emerald me-2"></i> Prescription Slip &mdash; ' + refCode;
  document.getElementById('rxModalSub').textContent = 'Patient: ' + patientName;
  document.getElementById('rxModalImg').src = imgSrc;
  document.getElementById('rxModalOpenBtn').href = imgSrc;
  const modal = new bootstrap.Modal(document.getElementById('rxImageModal'));
  modal.show();
}

// 2. Open Prescription Checkout Modal
let checkoutModalInstance = null;
let selectedRx = null;

function openRxCheckoutModal(rx) {
  selectedRx = rx;
  const refCode = '#RX-' + String(rx.id).padStart(4, '0');
  const amount = parseFloat(rx.quoted_amount || 0);

  document.getElementById('rxPayModalTitle').innerHTML = 'Confirm Order &mdash; ' + refCode;
  document.getElementById('rxPayAmountDisplay').textContent = 'Rs. ' + amount.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
  document.getElementById('rxPayPatientDisplay').textContent = 'Patient: ' + rx.patient_name + ' • ' + rx.phone;
  document.getElementById('rxPayNotesDisplay').textContent = rx.pharmacist_notes || 'Verified medication course. Ready for dispatch.';
  document.getElementById('currentActiveRxId').value = rx.id;

  // Toggle button text based on radio selection
  document.getElementById('rxPayOptPayHere').checked = true;
  updateRxPayBtnText();

  const modalEl = document.getElementById('rxCheckoutModal');
  checkoutModalInstance = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
  checkoutModalInstance.show();
}

document.addEventListener('DOMContentLoaded', function() {
  const optPayHere = document.getElementById('rxPayOptPayHere');
  const optCod = document.getElementById('rxPayOptCod');
  if (optPayHere && optCod) {
    optPayHere.addEventListener('change', updateRxPayBtnText);
    optCod.addEventListener('change', updateRxPayBtnText);
  }
});

function updateRxPayBtnText() {
  const isPayHere = document.getElementById('rxPayOptPayHere').checked;
  const btnText = document.getElementById('rxSubmitBtnText');
  if (isPayHere) {
    btnText.textContent = 'Proceed with Online Payment (PayHere)';
  } else {
    btnText.textContent = 'Confirm Cash on Delivery Order';
  }
}

// 3. PayHere Official SDK Callbacks
payhere.onCompleted = function onCompleted(orderId) {
  console.log("PayHere Prescription payment completed. OrderID:" + orderId);
  const rxId = document.getElementById('currentActiveRxId').value;
  
  const fd = new FormData();
  fd.append('rx_id', rxId);
  fd.append('payment_method', 'PayHere (Online Card / Mobile)');
  fd.append('payment_id', orderId || ('PH_RX_' + Date.now()));

  fetch('process_rx_payment.php', {
    method: 'POST',
    body: fd
  })
  .then(r => r.json())
  .then(data => {
    if (data.status === 'success') {
      window.location.href = 'order-success.php?order_id=' + data.order_id + '&payment_status=success';
    } else {
      window.location.reload();
    }
  })
  .catch(() => {
    window.location.reload();
  });
};

payhere.onDismissed = function onDismissed() {
  console.log("PayHere prescription payment dismissed.");
  const btn = document.getElementById('rxSubmitPaymentBtn');
  if (btn) {
    btn.disabled = false;
    updateRxPayBtnText();
  }
};

payhere.onError = function onError(error) {
  console.log("PayHere Error: " + error);
  alert("PayHere Notification: " + error);
  const btn = document.getElementById('rxSubmitPaymentBtn');
  if (btn) {
    btn.disabled = false;
    updateRxPayBtnText();
  }
};

// 4. Submit Prescription Checkout
function submitRxCheckout() {
  const rxId = document.getElementById('currentActiveRxId').value;
  const isPayHere = document.getElementById('rxPayOptPayHere').checked;
  const btn = document.getElementById('rxSubmitPaymentBtn');

  if (!rxId || rxId <= 0) {
    alert('Please select a valid prescription.');
    return;
  }

  btn.disabled = true;

  if (isPayHere) {
    // Online PayHere Gateway
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status"></span> Opening PayHere Gateway...';

    const fd = new FormData();
    fd.append('rx_id', rxId);

    fetch('get_rx_payhere_params.php', {
      method: 'POST',
      body: fd
    })
    .then(r => r.json())
    .then(data => {
      if (data.status === 'success') {
        const payment = {
          "sandbox": data.sandbox,
          "merchant_id": data.merchant_id,
          "return_url": data.return_url,
          "cancel_url": data.cancel_url,
          "notify_url": data.notify_url,
          "order_id": data.order_id,
          "items": data.items,
          "amount": data.amount,
          "currency": data.currency,
          "hash": data.hash,
          "first_name": data.first_name,
          "last_name": data.last_name,
          "email": data.email,
          "phone": data.phone,
          "address": data.address,
          "city": data.city,
          "country": data.country
        };
        payhere.startPayment(payment);
      } else {
        alert(data.message || 'Unable to start PayHere gateway.');
        btn.disabled = false;
        updateRxPayBtnText();
      }
    })
    .catch(err => {
      console.error(err);
      alert('Network error while connecting to payment gateway.');
      btn.disabled = false;
      updateRxPayBtnText();
    });

  } else {
    // Cash on Delivery Confirmation
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status"></span> Confirming Delivery Order...';

    const fd = new FormData();
    fd.append('rx_id', rxId);
    fd.append('payment_method', 'Cash on Delivery');

    fetch('process_rx_payment.php', {
      method: 'POST',
      body: fd
    })
    .then(r => r.json())
    .then(data => {
      if (data.status === 'success') {
        window.location.href = 'order-success.php?order_id=' + data.order_id + '&payment_status=success';
      } else {
        alert(data.message || 'Failed to confirm order.');
        btn.disabled = false;
        updateRxPayBtnText();
      }
    })
    .catch(err => {
      console.error(err);
      alert('Error confirming order.');
      btn.disabled = false;
      updateRxPayBtnText();
    });
  }
}
</script>

<?php include_once __DIR__ . '/includes/footer.php'; ?>
