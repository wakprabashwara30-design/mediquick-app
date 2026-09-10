<?php
/**
 * MediQuick Pharmacy - My Orders & Prescriptions Page
 * University 1st-Year Web Application (PHP & MySQL)
 */
require_once __DIR__ . '/config/db.php';

// Require Login
if (!isLoggedIn()) {
    header("Location: login.php");
    exit();
}

$userId = (int)$_SESSION['user_id'];

// Fetch user orders
$ordersQuery = mysqli_query($conn, "SELECT * FROM orders WHERE user_id = $userId ORDER BY id DESC");

// Fetch user prescriptions
$rxQuery = mysqli_query($conn, "SELECT * FROM prescriptions WHERE user_id = $userId ORDER BY id DESC");

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
      <a href="shop.php" class="btn btn-emerald btn-sm">
        <i class="bi bi-cart-plus me-1"></i> New Order
      </a>
    </div>

    <!-- Navigation Tabs -->
    <ul class="nav nav-tabs mb-4" id="accountTabs" role="tablist">
      <li class="nav-item" role="presentation">
        <button class="nav-link active fw-semibold" id="orders-tab" data-bs-toggle="tab" data-bs-target="#orders-tab-pane" type="button" role="tab" aria-controls="orders-tab-pane" aria-selected="true">
          <i class="bi bi-bag-check me-1"></i> My Orders (<?php echo $ordersQuery ? mysqli_num_rows($ordersQuery) : 0; ?>)
        </button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link fw-semibold" id="rx-tab" data-bs-toggle="tab" data-bs-target="#rx-tab-pane" type="button" role="tab" aria-controls="rx-tab-pane" aria-selected="false">
          <i class="bi bi-file-earmark-medical me-1"></i> Uploaded Prescriptions (<?php echo $rxQuery ? mysqli_num_rows($rxQuery) : 0; ?>)
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
                      <td><?php echo htmlspecialchars($ord['payment_method']); ?></td>
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
            <p class="text-muted mb-0">You have not placed any orders yet.</p>
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
                    <th>Uploaded Date</th>
                    <th>Patient Name</th>
                    <th>Status</th>
                    <th>Pharmacist Note</th>
                  </tr>
                </thead>
                <tbody class="small">
                  <?php while ($rx = mysqli_fetch_assoc($rxQuery)): ?>
                    <tr>
                      <td class="font-mono fw-bold">#RX-<?php echo str_pad($rx['id'], 4, '0', STR_PAD_LEFT); ?></td>
                      <td>
                        <button type="button" class="btn btn-sm btn-outline-emerald py-1 px-2 d-inline-flex align-items-center gap-1"
                                onclick="previewRxModal('<?php echo htmlspecialchars($rx['image'], ENT_QUOTES); ?>', '#RX-<?php echo str_pad($rx['id'], 4, '0', STR_PAD_LEFT); ?>', '<?php echo htmlspecialchars($rx['patient_name'], ENT_QUOTES); ?>')">
                          <i class="bi bi-image"></i> View Document
                        </button>
                      </td>
                      <td class="text-secondary"><?php echo date('M d, Y', strtotime($rx['created_at'])); ?></td>
                      <td><?php echo htmlspecialchars($rx['patient_name']); ?></td>
                      <td>
                        <?php 
                          $rxSt = $rx['status'];
                          $rxBadge = 'bg-warning text-dark';
                          if ($rxSt === 'Approved') $rxBadge = 'bg-success';
                          elseif ($rxSt === 'Rejected') $rxBadge = 'bg-danger';
                        ?>
                        <span class="badge <?php echo $rxBadge; ?>"><?php echo htmlspecialchars($rxSt); ?></span>
                      </td>
                      <td class="text-secondary">
                        <?php echo htmlspecialchars($rx['pharmacist_notes'] ?? 'Pending review by duty pharmacist.'); ?>
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

<!-- Prescription Image Preview Modal -->
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

<script>
function previewRxModal(imgSrc, refCode, patientName) {
  document.getElementById('rxModalTitle').innerHTML = '<i class="bi bi-file-earmark-medical text-emerald me-2"></i> Prescription Slip &mdash; ' + refCode;
  document.getElementById('rxModalSub').textContent = 'Patient: ' + patientName;
  document.getElementById('rxModalImg').src = imgSrc;
  document.getElementById('rxModalOpenBtn').href = imgSrc;
  const modal = new bootstrap.Modal(document.getElementById('rxImageModal'));
  modal.show();
}
</script>

<?php include_once __DIR__ . '/includes/footer.php'; ?>
