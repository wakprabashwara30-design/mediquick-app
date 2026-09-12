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

// 1. Handle Delete Action
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $delId = (int)$_GET['id'];
    if ($delId > 0) {
        $delQuery = "DELETE FROM prescriptions WHERE id = $delId";
        if (mysqli_query($conn, $delQuery)) {
            $successMsg = "Prescription #RX-" . str_pad($delId, 4, '0', STR_PAD_LEFT) . " deleted successfully.";
        } else {
            $errorMsg = "Failed to delete prescription: " . mysqli_error($conn);
        }
    }
}

// 2. Handle Review / Status Update POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_rx'])) {
    $rxId = (int)$_POST['rx_id'];
    $status = trim($_POST['status'] ?? 'Pending');
    $notes = trim($_POST['pharmacist_notes'] ?? '');
    $quotedAmount = !empty($_POST['quoted_amount']) ? (float)$_POST['quoted_amount'] : 0.00;

    $allowedStatuses = ['Pending', 'Approved', 'Rejected'];
    if (in_array($status, $allowedStatuses) && $rxId > 0) {
        $safeStatus = mysqli_real_escape_string($conn, $status);
        $safeNotes = mysqli_real_escape_string($conn, $notes);

        // Fetch current prescription record
        $currRxRes = mysqli_query($conn, "SELECT p.*, u.email AS user_email FROM prescriptions p LEFT JOIN users u ON p.user_id = u.id WHERE p.id = $rxId LIMIT 1");
        $currRx = ($currRxRes) ? mysqli_fetch_assoc($currRxRes) : null;

        if ($currRx) {
            $existingOrderId = !empty($currRx['order_id']) ? (int)$currRx['order_id'] : 0;
            $userIdVal = !empty($currRx['user_id']) ? (int)$currRx['user_id'] : "NULL";
            $patientName = mysqli_real_escape_string($conn, $currRx['patient_name']);
            $phone = mysqli_real_escape_string($conn, $currRx['phone']);
            $email = mysqli_real_escape_string($conn, $currRx['user_email'] ?? 'customer@mediquick.lk');
            $address = mysqli_real_escape_string($conn, $currRx['delivery_address']);
            $rxRef = '#RX-' . str_pad($rxId, 4, '0', STR_PAD_LEFT);

            if ($status === 'Approved' && $quotedAmount > 0) {
                if ($existingOrderId > 0) {
                    // Update existing linked order
                    mysqli_query($conn, "UPDATE orders SET total_amount = $quotedAmount WHERE id = $existingOrderId");
                    mysqli_query($conn, "UPDATE order_items SET unit_price = $quotedAmount, total_price = $quotedAmount WHERE order_id = $existingOrderId");
                    $orderId = $existingOrderId;
                } else {
                    // Create new linked order in Pending payment state
                    $insertOrderSql = "INSERT INTO orders (user_id, customer_name, phone, email, delivery_address, city, total_amount, payment_method, payment_status, status) 
                                       VALUES ($userIdVal, '$patientName', '$phone', '$email', '$address', 'Kurunegala', $quotedAmount, 'Prescription Quotation', 'Pending', 'Pending')";
                    mysqli_query($conn, $insertOrderSql);
                    $orderId = mysqli_insert_id($conn);

                    if ($orderId > 0) {
                        $itemName = mysqli_real_escape_string($conn, "Prescription Medication Course ($rxRef)");
                        mysqli_query($conn, "INSERT INTO order_items (order_id, product_id, product_name, unit_price, quantity, total_price) 
                                             VALUES ($orderId, NULL, '$itemName', $quotedAmount, 1, $quotedAmount)");
                    }
                }

                $sql = "UPDATE prescriptions SET status = '$safeStatus', pharmacist_notes = '$safeNotes', quoted_amount = $quotedAmount, order_id = " . ($orderId > 0 ? $orderId : "NULL") . " WHERE id = $rxId";
            } else {
                $sql = "UPDATE prescriptions SET status = '$safeStatus', pharmacist_notes = '$safeNotes', quoted_amount = " . ($quotedAmount > 0 ? $quotedAmount : "NULL") . " WHERE id = $rxId";
            }

            if (mysqli_query($conn, $sql)) {
                $successMsg = "Prescription $rxRef marked as $status" . ($quotedAmount > 0 ? " with quotation of " . formatLKR($quotedAmount) . "." : ".");
            } else {
                $errorMsg = "Failed to update prescription: " . mysqli_error($conn);
            }
        }
    }
}

// 3. Status Filter
$statusFilter = isset($_GET['status']) ? trim($_GET['status']) : 'All';
$whereClause = "";
if (in_array($statusFilter, ['Pending', 'Approved', 'Rejected'])) {
    $whereClause = "WHERE p.status = '$statusFilter'";
}

// 4. Counts for KPI Cards
$totalCountRes = mysqli_query($conn, "SELECT COUNT(*) AS total FROM prescriptions");
$totalCount = ($totalCountRes) ? mysqli_fetch_assoc($totalCountRes)['total'] : 0;

$pendingCountRes = mysqli_query($conn, "SELECT COUNT(*) AS total FROM prescriptions WHERE status = 'Pending'");
$pendingCount = ($pendingCountRes) ? mysqli_fetch_assoc($pendingCountRes)['total'] : 0;

$approvedCountRes = mysqli_query($conn, "SELECT COUNT(*) AS total FROM prescriptions WHERE status = 'Approved'");
$approvedCount = ($approvedCountRes) ? mysqli_fetch_assoc($approvedCountRes)['total'] : 0;

$rejectedCountRes = mysqli_query($conn, "SELECT COUNT(*) AS total FROM prescriptions WHERE status = 'Rejected'");
$rejectedCount = ($rejectedCountRes) ? mysqli_fetch_assoc($rejectedCountRes)['total'] : 0;

// 5. Fetch Prescriptions
$rxQuery = mysqli_query($conn, "SELECT p.*, u.email AS user_email FROM prescriptions p LEFT JOIN users u ON p.user_id = u.id $whereClause ORDER BY p.id DESC");

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
      <div class="d-flex align-items-center gap-2">
        <button class="btn btn-sm btn-light d-lg-none border px-2 py-1 shadow-none" type="button" onclick="toggleAdminSidebar()" aria-label="Toggle Menu">
          <i class="bi bi-list fs-5"></i>
        </button>
        <div class="small text-secondary fw-semibold text-truncate">
          <i class="bi bi-file-earmark-medical text-emerald me-1"></i> <span class="d-none d-sm-inline">Clinical Verification &mdash; </span>Prescriptions
        </div>
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

    <div class="p-3 p-md-4 flex-grow-1">
      
      <!-- Page Header -->
      <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div>
          <h3 class="fw-bold text-dark mb-1 font-heading">Doctor's Prescription Verification</h3>
          <p class="text-muted small mb-0">SLMC Pharmacist clinical dosage review, document verification & quotation approval</p>
        </div>
        <div class="d-flex align-items-center gap-2">
          <span class="badge bg-emerald-subtle text-emerald border border-emerald px-3 py-2 rounded-pill fw-semibold">
            <i class="bi bi-shield-fill-check me-1"></i> SLMC Pharmacist On Duty
          </span>
        </div>
      </div>

      <!-- Alerts -->
      <?php if (!empty($successMsg)): ?>
        <div class="alert alert-success alert-dismissible fade show small rounded-3 shadow-sm mb-4" role="alert">
          <i class="bi bi-check-circle-fill me-1.5 fs-6"></i> <?php echo htmlspecialchars($successMsg); ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
      <?php endif; ?>

      <?php if (!empty($errorMsg)): ?>
        <div class="alert alert-danger alert-dismissible fade show small rounded-3 shadow-sm mb-4" role="alert">
          <i class="bi bi-exclamation-triangle-fill me-1.5 fs-6"></i> <?php echo htmlspecialchars($errorMsg); ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
      <?php endif; ?>

      <!-- KPI Summary Cards -->
      <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
          <div class="stat-card-modern d-flex align-items-center justify-content-between">
            <div>
              <div class="text-muted small fw-semibold mb-1">Total Submissions</div>
              <h3 class="fw-bold mb-0 text-dark"><?php echo $totalCount; ?></h3>
            </div>
            <div class="rounded-3 d-flex align-items-center justify-content-center text-primary" style="width: 46px; height: 46px; background-color: #e0f2fe;">
              <i class="bi bi-file-earmark-medical fs-4"></i>
            </div>
          </div>
        </div>

        <div class="col-6 col-lg-3">
          <div class="stat-card-modern d-flex align-items-center justify-content-between" style="border-left: 4px solid #f59e0b;">
            <div>
              <div class="text-muted small fw-semibold mb-1">Pending Review</div>
              <h3 class="fw-bold mb-0 text-warning"><?php echo $pendingCount; ?></h3>
            </div>
            <div class="rounded-3 d-flex align-items-center justify-content-center text-warning" style="width: 46px; height: 46px; background-color: #fef3c7;">
              <i class="bi bi-hourglass-split fs-4"></i>
            </div>
          </div>
        </div>

        <div class="col-6 col-lg-3">
          <div class="stat-card-modern d-flex align-items-center justify-content-between" style="border-left: 4px solid #059669;">
            <div>
              <div class="text-muted small fw-semibold mb-1">Approved & Quoted</div>
              <h3 class="fw-bold mb-0 text-emerald"><?php echo $approvedCount; ?></h3>
            </div>
            <div class="rounded-3 d-flex align-items-center justify-content-center text-emerald" style="width: 46px; height: 46px; background-color: #ecfdf5;">
              <i class="bi bi-check2-circle fs-4"></i>
            </div>
          </div>
        </div>

        <div class="col-6 col-lg-3">
          <div class="stat-card-modern d-flex align-items-center justify-content-between" style="border-left: 4px solid #ef4444;">
            <div>
              <div class="text-muted small fw-semibold mb-1">Rejected / Invalid</div>
              <h3 class="fw-bold mb-0 text-danger"><?php echo $rejectedCount; ?></h3>
            </div>
            <div class="rounded-3 d-flex align-items-center justify-content-center text-danger" style="width: 46px; height: 46px; background-color: #fee2e2;">
              <i class="bi bi-x-circle fs-4"></i>
            </div>
          </div>
        </div>
      </div>

      <!-- Main Card with Toolbar & Table -->
      <div class="card card-custom bg-white border-0 shadow-sm overflow-hidden">
        
        <!-- Filter & Search Toolbar -->
        <div class="p-3 border-bottom bg-light d-flex flex-wrap align-items-center justify-content-between gap-3">
          
          <!-- Status Filter Tabs -->
          <div class="d-flex flex-wrap align-items-center gap-1.5">
            <a href="prescriptions.php?status=All" class="rx-status-tab-btn <?php echo ($statusFilter === 'All') ? 'active' : ''; ?>">
              <i class="bi bi-grid-fill"></i> All (<?php echo $totalCount; ?>)
            </a>
            <a href="prescriptions.php?status=Pending" class="rx-status-tab-btn <?php echo ($statusFilter === 'Pending') ? 'active' : ''; ?>">
              <i class="bi bi-hourglass-split"></i> Pending (<?php echo $pendingCount; ?>)
            </a>
            <a href="prescriptions.php?status=Approved" class="rx-status-tab-btn <?php echo ($statusFilter === 'Approved') ? 'active' : ''; ?>">
              <i class="bi bi-check-circle-fill"></i> Approved (<?php echo $approvedCount; ?>)
            </a>
            <a href="prescriptions.php?status=Rejected" class="rx-status-tab-btn <?php echo ($statusFilter === 'Rejected') ? 'active' : ''; ?>">
              <i class="bi bi-x-circle-fill"></i> Rejected (<?php echo $rejectedCount; ?>)
            </a>
          </div>

          <!-- Instant Live Search Input -->
          <div class="position-relative" style="min-width: 260px;">
            <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
            <input type="text" id="rxLiveSearch" class="form-control form-control-sm ps-5 rounded-pill" placeholder="Search patient, phone, or #RX...">
          </div>

        </div>

        <!-- Prescriptions Table (Clean, Spacious, User-Friendly 5-Column Layout) -->
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0" id="rxTable">
            <thead class="bg-light text-secondary" style="font-size: 0.76rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; border-bottom: 2px solid #e2e8f0;">
              <tr>
                <th class="ps-4 py-3.5" style="width: 170px;">Prescription</th>
                <th style="min-width: 260px;">Patient Details</th>
                <th class="text-center" style="width: 120px;">Document Slip</th>
                <th style="min-width: 200px;">Verification Status</th>
                <th class="text-end pe-4" style="width: 150px;">Action</th>
              </tr>
            </thead>
            <tbody class="small">
              <?php if ($rxQuery && mysqli_num_rows($rxQuery) > 0): ?>
                <?php while ($rx = mysqli_fetch_assoc($rxQuery)): 
                  $rxIdStr = '#RX-' . str_pad($rx['id'], 4, '0', STR_PAD_LEFT);
                  $nameParts = explode(' ', trim($rx['patient_name']));
                  $initials = strtoupper(substr($nameParts[0], 0, 1) . (isset($nameParts[1]) ? substr($nameParts[1], 0, 1) : ''));
                  $isPdf = strpos($rx['image'], 'application/pdf') !== false;
                ?>
                  <tr class="rx-row-item" style="transition: background-color 0.15s ease;">
                    <!-- 1. Ref Code & Submission Timestamp -->
                    <td class="ps-4 py-3.5">
                      <span class="badge bg-dark text-white font-mono px-2.5 py-1 rounded-2 mb-1 d-inline-block shadow-xs" style="background-color: #0f172a !important; font-size: 0.8rem;">
                        <?php echo $rxIdStr; ?>
                      </span>
                      <div class="text-muted d-flex align-items-center gap-1" style="font-size: 0.74rem;">
                        <i class="bi bi-clock-history text-secondary"></i> <?php echo date('M d, Y', strtotime($rx['created_at'])); ?>
                      </div>
                    </td>

                    <!-- 2. Consolidated Patient Profile & Contact Card -->
                    <td class="py-3.5">
                      <div class="d-flex align-items-center gap-3">
                        <div class="avatar-initials shadow-xs" style="width: 42px; height: 42px; font-size: 0.88rem; flex-shrink: 0;">
                          <?php echo htmlspecialchars($initials); ?>
                        </div>
                        <div>
                          <div class="fw-bold text-dark rx-patient-name" style="font-size: 0.92rem;">
                            <?php echo htmlspecialchars($rx['patient_name']); ?>
                          </div>
                          <div class="d-flex flex-wrap align-items-center gap-2 mt-0.5" style="font-size: 0.78rem;">
                            <a href="tel:<?php echo htmlspecialchars($rx['phone']); ?>" class="text-emerald fw-semibold text-decoration-none d-inline-flex align-items-center gap-1 rx-phone">
                              <i class="bi bi-telephone-fill" style="font-size: 0.72rem;"></i> <?php echo htmlspecialchars($rx['phone']); ?>
                            </a>
                            <span class="text-muted">•</span>
                            <span class="text-muted text-truncate" style="max-width: 220px;" title="<?php echo htmlspecialchars($rx['delivery_address']); ?>">
                              <i class="bi bi-geo-alt-fill text-secondary"></i> <?php echo htmlspecialchars($rx['delivery_address']); ?>
                            </span>
                          </div>
                        </div>
                      </div>
                    </td>

                    <!-- 3. Prescription Slip Clickable Thumbnail -->
                    <td class="text-center py-3.5">
                      <div class="d-inline-flex flex-column align-items-center position-relative cursor-pointer" 
                           onclick="openClinicalModal(<?php echo htmlspecialchars(json_encode($rx), ENT_QUOTES, 'UTF-8'); ?>)" 
                           title="Click to inspect prescription">
                        <?php if ($isPdf): ?>
                          <div class="rounded-3 border bg-light d-flex align-items-center justify-content-center text-danger rx-slip-thumb shadow-xs" style="width: 48px; height: 48px;">
                            <i class="bi bi-file-earmark-pdf-fill fs-3"></i>
                          </div>
                        <?php else: ?>
                          <img src="<?php echo htmlspecialchars($rx['image']); ?>" alt="Prescription Slip" class="rx-slip-thumb shadow-xs" style="width: 48px; height: 48px; border-radius: 10px; object-fit: cover;">
                        <?php endif; ?>
                        <span class="text-emerald fw-semibold mt-1 d-block" style="font-size: 0.7rem;">
                          <i class="bi bi-zoom-in"></i> Inspect
                        </span>
                      </div>
                    </td>

                    <!-- 4. Clinical Status & Notes Preview -->
                    <td class="py-3.5">
                      <div class="d-flex flex-column align-items-start gap-1">
                        <?php 
                          $st = $rx['status'];
                          if ($st === 'Approved'): ?>
                            <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1.5 rounded-pill fw-semibold" style="font-size: 0.78rem;">
                              <i class="bi bi-check-circle-fill me-1"></i> Approved & Quoted
                            </span>
                          <?php elseif ($st === 'Rejected'): ?>
                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-3 py-1.5 rounded-pill fw-semibold" style="font-size: 0.78rem;">
                              <i class="bi bi-x-circle-fill me-1"></i> Rejected / Invalid
                            </span>
                          <?php else: ?>
                            <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-3 py-1.5 rounded-pill fw-semibold" style="font-size: 0.78rem;">
                              <i class="bi bi-hourglass-split me-1"></i> Pending Verification
                            </span>
                          <?php endif; ?>

                        <?php if (!empty($rx['quoted_amount']) && (float)$rx['quoted_amount'] > 0): ?>
                          <div class="mt-1 d-flex flex-wrap align-items-center gap-1">
                            <span class="badge bg-emerald text-white font-mono px-2 py-1 shadow-xs" style="font-size: 0.75rem;">
                              <i class="bi bi-tag-fill me-1"></i> <?php echo formatLKR($rx['quoted_amount']); ?>
                            </span>
                            <?php if (!empty($rx['order_id'])): ?>
                              <a href="orders.php" class="badge bg-dark-subtle text-dark border text-decoration-none px-2 py-1" style="font-size: 0.72rem;" title="View Linked Order in Orders Queue">
                                <i class="bi bi-bag-check me-0.5"></i> #MQ-<?php echo str_pad($rx['order_id'], 5, '0', STR_PAD_LEFT); ?>
                              </a>
                            <?php endif; ?>
                          </div>
                        <?php endif; ?>

                        <?php if (!empty($rx['pharmacist_notes'])): ?>
                          <div class="text-secondary text-truncate mt-0.5 d-flex align-items-center gap-1" style="font-size: 0.74rem; max-width: 230px;" title="<?php echo htmlspecialchars($rx['pharmacist_notes']); ?>">
                            <i class="bi bi-chat-left-dots-fill text-emerald"></i> <?php echo htmlspecialchars($rx['pharmacist_notes']); ?>
                          </div>
                        <?php endif; ?>
                      </div>
                    </td>

                    <!-- 5. Actions -->
                    <td class="text-end pe-4 py-3.5">
                      <div class="d-inline-flex align-items-center gap-1.5">
                        <button type="button" class="btn btn-sm btn-emerald py-1.5 px-3 rounded-3 d-inline-flex align-items-center gap-1.5 shadow-sm fw-semibold" 
                                onclick="openClinicalModal(<?php echo htmlspecialchars(json_encode($rx), ENT_QUOTES, 'UTF-8'); ?>)" title="Review Prescription">
                          <i class="bi bi-clipboard2-pulse"></i> <span>Review</span>
                        </button>
                        <a href="prescriptions.php?action=delete&id=<?php echo $rx['id']; ?>" class="btn btn-sm btn-outline-danger p-1.5 rounded-3" 
                           onclick="return confirmAction('Are you sure you want to permanently delete prescription <?php echo $rxIdStr; ?> for <?php echo htmlspecialchars(addslashes($rx['patient_name'])); ?>?', 'Delete Prescription?');" title="Delete Slip">
                          <i class="bi bi-trash"></i>
                        </a>
                      </div>
                    </td>
                  </tr>
                <?php endwhile; ?>
              <?php else: ?>
                <tr>
                  <td colspan="5" class="text-center text-muted py-5">
                    <i class="bi bi-inbox fs-1 d-block mb-2 text-secondary"></i>
                    No prescriptions found matching this filter.
                  </td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>

      </div>

    </div>
  </div>
</div>

<!-- ========================================================================= -->
<!-- COMPREHENSIVE PHARMACIST CLINICAL REVIEW & QUOTATION MODAL -->
<!-- ========================================================================= -->
<div class="modal fade" id="clinicalReviewModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content border-0 shadow-lg" style="border-radius: 20px; overflow: hidden;">
      
      <!-- Modal Header -->
      <div class="modal-header bg-dark text-white py-3 px-4" style="background-color: #0f172a !important;">
        <div class="d-flex align-items-center gap-2.5">
          <div class="rounded-3 d-flex align-items-center justify-content-center text-white" style="width: 36px; height: 36px; background-color: #059669;">
            <i class="bi bi-clipboard2-pulse fs-5"></i>
          </div>
          <div>
            <h5 class="modal-title font-heading fw-bold mb-0" id="modalRxRef">Prescription Review</h5>
            <div class="text-white-50" style="font-size: 0.75rem;" id="modalRxDate"></div>
          </div>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <!-- Modal Body -->
      <div class="modal-body p-0">
        <div class="row g-0">
          
          <!-- Left Column: Document Slip Viewer (60%) -->
          <div class="col-lg-7 p-3 p-md-4 bg-light border-end d-flex flex-column align-items-center justify-content-center">
            
            <div class="w-100 d-flex justify-content-between align-items-center mb-2 px-1">
              <span class="small fw-bold text-secondary"><i class="bi bi-file-earmark-medical me-1 text-emerald"></i> Original Prescription Document</span>
              <div class="d-flex gap-1.5">
                <a id="modalSlipDownload" href="#" download="prescription_slip" class="btn btn-sm btn-outline-secondary py-1 px-2.5 rounded-pill" title="Download Document">
                  <i class="bi bi-download me-1"></i> Download
                </a>
                <a id="modalSlipNewTab" href="#" target="_blank" class="btn btn-sm btn-outline-primary py-1 px-2.5 rounded-pill" title="Open in Full Size">
                  <i class="bi bi-box-arrow-up-right me-1"></i> Full Size
                </a>
              </div>
            </div>

            <!-- Image / Document Canvas -->
            <div class="w-100 rounded-3 border bg-white p-2 text-center shadow-sm d-flex align-items-center justify-content-center" style="min-height: 440px; max-height: 540px; overflow: auto;">
              <img id="modalSlipImg" src="" alt="Doctor's Prescription Slip" class="img-fluid rounded" style="max-height: 500px; object-fit: contain; transition: transform 0.2s ease;">
              <div id="modalPdfPlaceholder" class="d-none text-center p-5">
                <i class="bi bi-file-earmark-pdf-fill text-danger" style="font-size: 4rem;"></i>
                <h6 class="fw-bold text-dark mt-2 mb-1">PDF Prescription Document</h6>
                <p class="text-muted small mb-3">Click below to view the full PDF document in your browser.</p>
                <a id="modalPdfOpenBtn" href="#" target="_blank" class="btn btn-emerald btn-sm px-4 rounded-pill fw-bold">
                  <i class="bi bi-eye-fill me-1"></i> View PDF Document
                </a>
              </div>
            </div>

          </div>

          <!-- Right Column: Pharmacist Review & Status Form (40%) -->
          <div class="col-lg-5 p-3 p-md-4 bg-white d-flex flex-column">
            
            <!-- Patient Info Box -->
            <div class="p-3 mb-3 rounded-3 bg-light border">
              <h6 class="fw-bold text-dark mb-2 pb-1 border-bottom d-flex align-items-center gap-1.5" style="font-size: 0.85rem;">
                <i class="bi bi-person-badge text-emerald"></i> Patient Information
              </h6>
              <div class="d-flex flex-column gap-1.5 small">
                <div class="d-flex justify-content-between">
                  <span class="text-muted">Name:</span>
                  <strong class="text-dark" id="modalPatientName"></strong>
                </div>
                <div class="d-flex justify-content-between">
                  <span class="text-muted">Phone:</span>
                  <a href="#" id="modalPatientPhone" class="fw-bold text-emerald text-decoration-none"></a>
                </div>
                <div class="d-flex justify-content-between">
                  <span class="text-muted">Delivery Address:</span>
                  <span class="text-dark text-end fw-semibold" id="modalPatientAddress" style="max-width: 60%;"></span>
                </div>
              </div>
            </div>

            <!-- Review Action Form -->
            <form action="prescriptions.php" method="POST" class="d-flex flex-column flex-grow-1">
              <input type="hidden" name="update_rx" value="1">
              <input type="hidden" name="rx_id" id="modalFormRxId" value="0">

              <!-- Clinical Status Selection -->
              <div class="mb-3">
                <label class="form-label fw-bold text-dark small mb-2">
                  <i class="bi bi-shield-check text-emerald me-1"></i> Verification Decision:
                </label>
                <div class="d-flex flex-column gap-2">
                  
                  <label class="d-flex align-items-center gap-2 p-2.5 rounded-3 border cursor-pointer hover-shadow" style="background-color: #f0fdf4;">
                    <input type="radio" name="status" value="Approved" id="statusApproved" class="form-check-input mt-0" required>
                    <div>
                      <strong class="text-success small d-block"><i class="bi bi-check-circle-fill me-1"></i> Approved & Ready for Quotation</strong>
                      <span class="text-muted" style="font-size: 0.72rem;">Valid doctor signature, dosage verified by duty pharmacist</span>
                    </div>
                  </label>

                  <label class="d-flex align-items-center gap-2 p-2.5 rounded-3 border cursor-pointer hover-shadow" style="background-color: #fffbeb;">
                    <input type="radio" name="status" value="Pending" id="statusPending" class="form-check-input mt-0">
                    <div>
                      <strong class="text-warning small d-block"><i class="bi bi-hourglass-split me-1"></i> Pending Further Clarification</strong>
                      <span class="text-muted" style="font-size: 0.72rem;">Awaiting patient confirmation or alternative stock check</span>
                    </div>
                  </label>

                  <label class="d-flex align-items-center gap-2 p-2.5 rounded-3 border cursor-pointer hover-shadow" style="background-color: #fef2f2;">
                    <input type="radio" name="status" value="Rejected" id="statusRejected" class="form-check-input mt-0">
                    <div>
                      <strong class="text-danger small d-block"><i class="bi bi-x-circle-fill me-1"></i> Rejected / Invalid Prescription</strong>
                      <span class="text-muted" style="font-size: 0.72rem;">Illegible slip, expired prescription, or NMRA restricted</span>
                    </div>
                  </label>

                </div>
              </div>

              <!-- Prescription Quotation Amount (LKR) -->
              <div class="mb-3">
                <label for="modalQuotedAmount" class="form-label fw-bold text-dark small mb-1 d-flex justify-content-between">
                  <span><i class="bi bi-tag-fill text-emerald me-1"></i> Quoted Amount (LKR):</span>
                  <span class="text-muted fw-normal" style="font-size: 0.72rem;">(Optional if rejecting)</span>
                </label>
                <div class="input-group input-group-sm">
                  <span class="input-group-text bg-light fw-bold text-secondary">Rs.</span>
                  <input type="number" step="0.01" min="0" name="quoted_amount" id="modalQuotedAmount" class="form-control fw-bold text-emerald" placeholder="e.g. 2450.00">
                </div>
                <small class="text-muted" style="font-size: 0.72rem;">When approved with a price, the system links an order for customer online checkout / COD.</small>
              </div>

              <!-- Pharmacist Clinical Notes / Dosage Instructions -->
              <div class="mb-3">
                <label for="modalPharmacistNotes" class="form-label fw-bold text-dark small mb-1">
                  <i class="bi bi-chat-text text-emerald me-1"></i> Pharmacist Notes & Dosage Instructions:
                </label>
                <textarea name="pharmacist_notes" id="modalPharmacistNotes" rows="3" class="form-control form-control-sm" placeholder="e.g. Verified 5-day Amoxicillin course (Rs. 1,800) + Paracetamol (Rs. 400) + Delivery (Rs. 250). Take 1 Cap TDS with water..."></textarea>
                <small class="text-muted" style="font-size: 0.72rem;">These clinical notes & medicine instructions are shown on the patient's dashboard & invoice.</small>
              </div>

              <!-- Submit Button -->
              <div class="mt-auto">
                <button type="submit" class="btn btn-emerald w-100 py-2.5 fw-bold shadow-sm d-flex align-items-center justify-content-center gap-2">
                  <i class="bi bi-save2-fill"></i> Save Clinical Decision & Issue Quote
                </button>
              </div>

            </form>

          </div>

        </div>
      </div>

    </div>
  </div>
</div>

<script>
// Open and populate the Clinical Review Modal
let reviewModalInstance = null;

function openClinicalModal(rx) {
  const refCode = '#RX-' + String(rx.id).padStart(4, '0');
  
  document.getElementById('modalRxRef').innerHTML = '<i class="bi bi-file-earmark-medical text-emerald me-1.5"></i> ' + refCode + ' &mdash; Clinical Verification';
  document.getElementById('modalRxDate').textContent = 'Uploaded on: ' + rx.created_at;
  document.getElementById('modalFormRxId').value = rx.id;
  
  document.getElementById('modalPatientName').textContent = rx.patient_name;
  const phoneEl = document.getElementById('modalPatientPhone');
  phoneEl.textContent = rx.phone;
  phoneEl.href = 'tel:' + rx.phone;
  document.getElementById('modalPatientAddress').textContent = rx.delivery_address;
  
  document.getElementById('modalPharmacistNotes').value = rx.pharmacist_notes || '';
  document.getElementById('modalQuotedAmount').value = (rx.quoted_amount && parseFloat(rx.quoted_amount) > 0) ? parseFloat(rx.quoted_amount).toFixed(2) : '';

  // Select Status Radio
  if (rx.status === 'Approved') {
    document.getElementById('statusApproved').checked = true;
  } else if (rx.status === 'Rejected') {
    document.getElementById('statusRejected').checked = true;
  } else {
    document.getElementById('statusPending').checked = true;
  }

  // Handle Document Display (Image vs PDF)
  const isPdf = rx.image.includes('application/pdf');
  const imgEl = document.getElementById('modalSlipImg');
  const pdfPlaceholder = document.getElementById('modalPdfPlaceholder');
  const downloadBtn = document.getElementById('modalSlipDownload');
  const fullSizeBtn = document.getElementById('modalSlipNewTab');
  const pdfOpenBtn = document.getElementById('modalPdfOpenBtn');

  downloadBtn.href = rx.image;
  fullSizeBtn.href = rx.image;

  if (isPdf) {
    imgEl.classList.add('d-none');
    pdfPlaceholder.classList.remove('d-none');
    pdfOpenBtn.href = rx.image;
  } else {
    imgEl.src = rx.image;
    imgEl.classList.remove('d-none');
    pdfPlaceholder.classList.add('d-none');
  }

  const modalEl = document.getElementById('clinicalReviewModal');
  reviewModalInstance = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
  reviewModalInstance.show();
}

// Instant Live Search in Table
document.addEventListener('DOMContentLoaded', function() {
  const searchInput = document.getElementById('rxLiveSearch');
  if (searchInput) {
    searchInput.addEventListener('input', function() {
      const query = this.value.toLowerCase().trim();
      const rows = document.querySelectorAll('.rx-row-item');

      rows.forEach(function(row) {
        const text = row.textContent.toLowerCase();
        if (text.includes(query)) {
          row.style.display = '';
        } else {
          row.style.display = 'none';
        }
      });
    });
  }
});
</script>

<!-- Bootstrap Bundle & JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/main.js"></script>
</body>
</html>
