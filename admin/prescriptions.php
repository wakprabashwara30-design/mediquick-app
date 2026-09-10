<?php
/**
 * MediQuick Pharmacy - Pharmacist Prescription Review Portal
 * University 1st-Year Web Application (PHP & MySQL Admin Portal)
 */
$basePath = '../';
require_once __DIR__ . '/../config/db.php';

// Check Admin Access
if (!isAdmin()) {
    header("Location: ../login.php");
    exit();
}

$successMsg = '';
$errorMsg = '';

// 1. Handle Review / Status Update POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_rx'])) {
    $rxId = (int)$_POST['rx_id'];
    $status = trim($_POST['status'] ?? 'Pending');
    $notes = trim($_POST['pharmacist_notes'] ?? '');

    $allowedStatuses = ['Pending', 'Approved', 'Rejected'];
    if (in_array($status, $allowedStatuses) && $rxId > 0) {
        $safeStatus = mysqli_real_escape_string($conn, $status);
        $safeNotes = mysqli_real_escape_string($conn, $notes);

        $sql = "UPDATE prescriptions SET status = '$safeStatus', pharmacist_notes = '$safeNotes' WHERE id = $rxId";
        if (mysqli_query($conn, $sql)) {
            $successMsg = "Prescription #RX-" . str_pad($rxId, 4, '0', STR_PAD_LEFT) . " marked as $status.";
        } else {
            $errorMsg = "Failed to update prescription: " . mysqli_error($conn);
        }
    }
}

// 2. Fetch all prescriptions
$rxQuery = mysqli_query($conn, "SELECT p.*, u.email AS user_email FROM prescriptions p LEFT JOIN users u ON p.user_id = u.id ORDER BY p.id DESC");

$pageTitle = 'Review Prescriptions | MediQuick Admin';
include_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex" style="min-height: 100vh;">
  <!-- 1. LEFT SIDEBAR -->
  <?php include_once __DIR__ . '/../includes/admin_sidebar.php'; ?>

  <!-- 2. MAIN CONTENT AREA -->
  <div class="flex-grow-1 d-flex flex-column" style="background-color: #f8fafc; min-width: 0;">
    
    <!-- Admin Top Bar -->
    <div class="admin-topbar d-flex justify-content-between align-items-center">
      <div class="small text-secondary fw-semibold">
        <i class="bi bi-file-earmark-medical text-emerald me-1"></i> Doctor's Prescription Review &mdash; Kurunegala
      </div>
      <div class="d-flex align-items-center gap-2">
        <a href="../index.php" target="_blank" class="btn btn-sm btn-outline-secondary" style="font-size: 0.8rem;">
          <i class="bi bi-shop me-1"></i> Storefront
        </a>
        <a href="logout.php" class="btn btn-sm btn-outline-danger" style="font-size: 0.8rem;">
          <i class="bi bi-box-arrow-right me-1"></i> Logout
        </a>
      </div>
    </div>

    <div class="p-4 flex-grow-1">
      
      <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
          <h3 class="fw-bold mb-0">Doctor's Prescription Verification</h3>
          <p class="text-muted small mb-0">SLMC Pharmacist verification and clinical quotation review for patient uploads</p>
        </div>
      </div>

    <?php if (!empty($successMsg)): ?>
      <div class="alert alert-success alert-dismissible fade show small" role="alert">
        <i class="bi bi-check-circle-fill me-1"></i> <?php echo htmlspecialchars($successMsg); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    <?php endif; ?>

    <?php if (!empty($errorMsg)): ?>
      <div class="alert alert-danger alert-dismissible fade show small" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-1"></i> <?php echo htmlspecialchars($errorMsg); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    <?php endif; ?>

    <!-- Prescriptions Table -->
    <div class="card card-custom p-3 bg-white">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0 small">
          <thead class="table-light">
            <tr>
              <th>Rx Ref</th>
              <th>Date</th>
              <th>Patient Name</th>
              <th>Contact Phone</th>
              <th>Delivery Address</th>
              <th>Prescription Document</th>
              <th>Current Status</th>
              <th style="min-width: 280px;">Review / Pharmacist Notes</th>
            </tr>
          </thead>
          <tbody>
            <?php if ($rxQuery && mysqli_num_rows($rxQuery) > 0): ?>
              <?php while ($rx = mysqli_fetch_assoc($rxQuery)): ?>
                <tr>
                  <td class="font-mono fw-bold">#RX-<?php echo str_pad($rx['id'], 4, '0', STR_PAD_LEFT); ?></td>
                  <td class="text-secondary"><?php echo date('M d, Y', strtotime($rx['created_at'])); ?></td>
                  <td><strong><?php echo htmlspecialchars($rx['patient_name']); ?></strong></td>
                  <td><a href="tel:<?php echo htmlspecialchars($rx['phone']); ?>" class="text-dark"><?php echo htmlspecialchars($rx['phone']); ?></a></td>
                  <td><?php echo htmlspecialchars($rx['delivery_address']); ?></td>
                  <td>
                    <button type="button" class="btn btn-sm btn-outline-primary py-1 px-2" 
                            onclick="previewRxModal('<?php echo htmlspecialchars($rx['image'], ENT_QUOTES); ?>', '#RX-<?php echo str_pad($rx['id'], 4, '0', STR_PAD_LEFT); ?>', '<?php echo htmlspecialchars($rx['patient_name'], ENT_QUOTES); ?>')">
                      <i class="bi bi-file-earmark-medical me-1"></i> View Slip
                    </button>
                  </td>
                  <td>
                    <?php 
                      $st = $rx['status'];
                      $badge = 'bg-warning text-dark';
                      if ($st === 'Approved') $badge = 'bg-success';
                      elseif ($st === 'Rejected') $badge = 'bg-danger';
                    ?>
                    <span class="badge <?php echo $badge; ?>"><?php echo htmlspecialchars($st); ?></span>
                  </td>
                  <td>
                    <!-- Review Form -->
                    <form action="prescriptions.php" method="POST" class="d-flex flex-column gap-1">
                      <input type="hidden" name="update_rx" value="1">
                      <input type="hidden" name="rx_id" value="<?php echo $rx['id']; ?>">
                      
                      <div class="d-flex gap-1">
                        <select name="status" class="form-select form-select-sm" style="width: 115px; font-size: 0.75rem;">
                          <option value="Pending" <?php echo ($rx['status'] === 'Pending') ? 'selected' : ''; ?>>Pending</option>
                          <option value="Approved" <?php echo ($rx['status'] === 'Approved') ? 'selected' : ''; ?>>Approve</option>
                          <option value="Rejected" <?php echo ($rx['status'] === 'Rejected') ? 'selected' : ''; ?>>Reject</option>
                        </select>

                        <button type="submit" class="btn btn-sm btn-emerald py-1 px-2" title="Save Review" style="font-size: 0.75rem;">
                          <i class="bi bi-check2"></i> Save
                        </button>
                      </div>

                      <input type="text" name="pharmacist_notes" class="form-control form-control-sm" placeholder="Add pharmacist note/instructions..." value="<?php echo htmlspecialchars($rx['pharmacist_notes'] ?? ''); ?>" style="font-size: 0.75rem;">
                    </form>
                  </td>
                </tr>
              <?php endwhile; ?>
            <?php else: ?>
              <tr><td colspan="8" class="text-center text-muted py-4">No doctor prescriptions submitted yet.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- Prescription Image Preview Modal -->
<div class="modal fade" id="rxImageModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header bg-dark text-white py-2">
        <h6 class="modal-title font-heading fw-bold" id="rxModalTitle">
          <i class="bi bi-file-earmark-medical text-emerald me-2"></i> Prescription Slip Preview
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
  document.getElementById('rxModalTitle').innerHTML = '<i class="bi bi-file-earmark-medical text-emerald me-2"></i> Doctor\'s Prescription &mdash; ' + refCode;
  document.getElementById('rxModalSub').textContent = 'Patient: ' + patientName;
  document.getElementById('rxModalImg').src = imgSrc;
  document.getElementById('rxModalOpenBtn').href = imgSrc;
  const modal = new bootstrap.Modal(document.getElementById('rxImageModal'));
  modal.show();
}
</script>

<!-- Bootstrap Bundle & JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/main.js"></script>
</body>
</html>
