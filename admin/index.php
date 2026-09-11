<?php
/**
 * MediQuick Pharmacy - Real-Time Admin Operations Dashboard
 * University 1st-Year Web Application (PHP & MySQL Admin Portal)
 */
$basePath = '../';
require_once __DIR__ . '/../config/db.php';

// Check Admin Access
if (!isAdmin()) {
    header("Location: ../login.php");
    exit();
}

// 1. Fetch Real KPI Metrics from Database
$totalProducts = 0;
$totalInventoryValue = 0;
$totalUnitsInStock = 0;
$lowStockCount = 0;

$pStatsRes = mysqli_query($conn, "SELECT COUNT(*) as count, SUM(price * stock) as total_val, SUM(stock) as total_units FROM products");
if ($pStatsRes && $row = mysqli_fetch_assoc($pStatsRes)) {
    $totalProducts = (int)($row['count'] ?? 0);
    $totalInventoryValue = (float)($row['total_val'] ?? 0);
    $totalUnitsInStock = (int)($row['total_units'] ?? 0);
}

// Low stock items (stock <= 50)
$lowStockRes = mysqli_query($conn, "SELECT COUNT(*) as count FROM products WHERE stock <= 50");
if ($lowStockRes) {
    $lowStockCount = (int)(mysqli_fetch_assoc($lowStockRes)['count'] ?? 0);
}

// Orders Metrics
$totalOrders = 0;
$totalRevenue = 0;
$pendingOrdersCount = 0;
$deliveredOrdersCount = 0;

$oStatsRes = mysqli_query($conn, "SELECT 
    COUNT(*) as total_orders, 
    SUM(total_amount) as total_revenue,
    SUM(CASE WHEN status = 'Pending' OR status = 'Processing' THEN 1 ELSE 0 END) as pending_orders,
    SUM(CASE WHEN status = 'Delivered' THEN 1 ELSE 0 END) as delivered_orders
    FROM orders");
if ($oStatsRes && $row = mysqli_fetch_assoc($oStatsRes)) {
    $totalOrders = (int)($row['total_orders'] ?? 0);
    $totalRevenue = (float)($row['total_revenue'] ?? 0);
    $pendingOrdersCount = (int)($row['pending_orders'] ?? 0);
    $deliveredOrdersCount = (int)($row['delivered_orders'] ?? 0);
}

// Prescriptions Metrics
$totalPrescriptions = 0;
$pendingRxCount = 0;
$approvedRxCount = 0;

$rxStatsRes = mysqli_query($conn, "SELECT 
    COUNT(*) as total_rx,
    SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) as pending_rx,
    SUM(CASE WHEN status = 'Approved' THEN 1 ELSE 0 END) as approved_rx
    FROM prescriptions");
if ($rxStatsRes && $row = mysqli_fetch_assoc($rxStatsRes)) {
    $totalPrescriptions = (int)($row['total_rx'] ?? 0);
    $pendingRxCount = (int)($row['pending_rx'] ?? 0);
    $approvedRxCount = (int)($row['approved_rx'] ?? 0);
}

// Customer Inquiries Metrics
$newInquiriesCount = 0;
$inqStatsRes = mysqli_query($conn, "SELECT COUNT(*) as count FROM inquiries WHERE status = 'New'");
if ($inqStatsRes) {
    $newInquiriesCount = (int)(mysqli_fetch_assoc($inqStatsRes)['count'] ?? 0);
}

// 2. Fetch Recent Real Orders (Top 6)
$recentOrders = mysqli_query($conn, "SELECT * FROM orders ORDER BY id DESC LIMIT 6");

// 3. Fetch Recent Real Prescriptions (Top 6)
$recentRx = mysqli_query($conn, "SELECT p.*, u.email as user_email FROM prescriptions p LEFT JOIN users u ON p.user_id = u.id ORDER BY p.id DESC LIMIT 6");

// 4. Fetch Real Low Stock Products
$lowStockProducts = mysqli_query($conn, "SELECT * FROM products WHERE stock <= 50 ORDER BY stock ASC LIMIT 4");

$pageTitle = 'Pharmacy Operations Dashboard | MediQuick Admin';
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
          <i class="bi bi-geo-alt-fill text-emerald me-1"></i> <span class="d-none d-sm-inline">Kurunegala Dispensary &mdash; </span>Admin Control Center
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
      
      <!-- Dashboard Top Header -->
      <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
          <h3 class="fw-bold mb-1 font-heading text-dark">Pharmacy Operations Dashboard</h3>
          <p class="text-muted small mb-0">
            Welcome back, <strong><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Administrator'); ?></strong> &bull; Real-time inventory, customer orders, and clinical prescription verification
          </p>
        </div>
        <div class="d-flex align-items-center gap-2">
          <span class="badge bg-emerald-subtle text-emerald border border-emerald px-3 py-2 rounded-pill fw-semibold d-none d-md-inline-flex align-items-center gap-1.5">
            <span class="d-inline-block rounded-circle bg-emerald" style="width: 8px; height: 8px;"></span> Live Pharmacy DB Connected
          </span>
          <a href="products.php" class="btn btn-emerald btn-sm py-2 px-3 shadow-sm rounded-3">
            <i class="bi bi-plus-circle-fill me-1"></i> Add New Medicine
          </a>
        </div>
      </div>

      <!-- 1. STAT CARDS ROW (REAL METRICS) -->
      <div class="row g-3 mb-4">
        
        <!-- Total Products & Inventory -->
        <div class="col-sm-6 col-xl-3">
          <div class="stat-card-modern h-100 d-flex flex-column justify-content-between">
            <div class="d-flex align-items-center justify-content-between mb-2">
              <span class="text-muted small fw-semibold">Inventory Catalog</span>
              <div class="rounded-3 d-flex align-items-center justify-content-center text-emerald" style="width: 44px; height: 44px; background-color: #ecfdf5;">
                <i class="bi bi-capsule fs-4"></i>
              </div>
            </div>
            <div>
              <h3 class="fw-bold text-dark mb-0"><?php echo $totalProducts; ?> <span class="fs-6 fw-normal text-muted">Medicines</span></h3>
              <div class="text-secondary small mt-1">
                <strong><?php echo number_format($totalUnitsInStock); ?></strong> units in stock
              </div>
            </div>
            <div class="border-top pt-2 mt-3 d-flex justify-content-between align-items-center">
              <a href="products.php" class="small text-emerald fw-semibold text-decoration-none">Manage inventory &rarr;</a>
              <?php if ($lowStockCount > 0): ?>
                <span class="badge bg-warning-subtle text-warning border" style="font-size: 0.7rem;"><?php echo $lowStockCount; ?> low stock</span>
              <?php endif; ?>
            </div>
          </div>
        </div>

        <!-- Total Customer Orders -->
        <div class="col-sm-6 col-xl-3">
          <div class="stat-card-modern h-100 d-flex flex-column justify-content-between" style="border-left: 4px solid #0284c7;">
            <div class="d-flex align-items-center justify-content-between mb-2">
              <span class="text-muted small fw-semibold">Customer Orders</span>
              <div class="rounded-3 d-flex align-items-center justify-content-center text-primary" style="width: 44px; height: 44px; background-color: #e0f2fe;">
                <i class="bi bi-bag-check-fill fs-4"></i>
              </div>
            </div>
            <div>
              <h3 class="fw-bold text-dark mb-0"><?php echo $totalOrders; ?> <span class="fs-6 fw-normal text-muted">Orders</span></h3>
              <div class="text-secondary small mt-1">
                <span class="text-warning fw-bold"><?php echo $pendingOrdersCount; ?></span> pending fulfillment
              </div>
            </div>
            <div class="border-top pt-2 mt-3 d-flex justify-content-between align-items-center">
              <a href="orders.php" class="small text-primary fw-semibold text-decoration-none">View customer orders &rarr;</a>
              <span class="badge bg-success-subtle text-success" style="font-size: 0.7rem;"><?php echo $deliveredOrdersCount; ?> delivered</span>
            </div>
          </div>
        </div>

        <!-- Doctor Prescriptions -->
        <div class="col-sm-6 col-xl-3">
          <div class="stat-card-modern h-100 d-flex flex-column justify-content-between" style="border-left: 4px solid #f59e0b;">
            <div class="d-flex align-items-center justify-content-between mb-2">
              <span class="text-muted small fw-semibold">Prescription Slips</span>
              <div class="rounded-3 d-flex align-items-center justify-content-center text-warning" style="width: 44px; height: 44px; background-color: #fef3c7;">
                <i class="bi bi-file-earmark-medical-fill fs-4"></i>
              </div>
            </div>
            <div>
              <h3 class="fw-bold text-dark mb-0"><?php echo $totalPrescriptions; ?> <span class="fs-6 fw-normal text-muted">Uploaded</span></h3>
              <div class="text-secondary small mt-1">
                <span class="text-warning fw-bold"><?php echo $pendingRxCount; ?></span> awaiting SLMC review
              </div>
            </div>
            <div class="border-top pt-2 mt-3 d-flex justify-content-between align-items-center">
              <a href="prescriptions.php" class="small text-warning fw-semibold text-decoration-none">Review doctor slips &rarr;</a>
              <span class="badge bg-emerald-subtle text-emerald" style="font-size: 0.7rem;"><?php echo $approvedRxCount; ?> approved</span>
            </div>
          </div>
        </div>

        <!-- Total Sales Revenue -->
        <div class="col-sm-6 col-xl-3">
          <div class="stat-card-modern h-100 d-flex flex-column justify-content-between" style="border-left: 4px solid #059669;">
            <div class="d-flex align-items-center justify-content-between mb-2">
              <span class="text-muted small fw-semibold">Total Sales Revenue</span>
              <div class="rounded-3 d-flex align-items-center justify-content-center text-emerald" style="width: 44px; height: 44px; background-color: #ecfdf5;">
                <i class="bi bi-cash-stack fs-4"></i>
              </div>
            </div>
            <div>
              <h3 class="fw-bold text-emerald mb-0"><?php echo formatLKR($totalRevenue); ?></h3>
              <div class="text-muted small mt-1">
                From <?php echo $totalOrders; ?> customer transactions
              </div>
            </div>
            <div class="border-top pt-2 mt-3 d-flex justify-content-between align-items-center">
              <span class="small text-muted">Stock Val: <strong><?php echo formatLKR($totalInventoryValue); ?></strong></span>
              <span class="badge bg-light text-secondary border" style="font-size: 0.7rem;">Live LKR</span>
            </div>
          </div>
        </div>

      </div>

      <!-- 2. QUICK ALERT STRIP (LOW STOCK + INQUIRIES) -->
      <?php if ($lowStockCount > 0 || $newInquiriesCount > 0): ?>
        <div class="row g-3 mb-4">
          
          <?php if ($lowStockCount > 0): ?>
            <div class="col-lg-6">
              <div class="card card-custom p-3 bg-white border-0 shadow-sm">
                <div class="d-flex justify-content-between align-items-center mb-2">
                  <div class="fw-bold text-dark small d-flex align-items-center gap-1.5">
                    <i class="bi bi-exclamation-triangle-fill text-warning"></i>
                    <span>Low Stock Reorder Alerts (Stock &le; 50 units)</span>
                  </div>
                  <a href="products.php" class="small text-emerald text-decoration-none">Manage &rarr;</a>
                </div>
                <div class="d-flex flex-column gap-2">
                  <?php while ($lowP = mysqli_fetch_assoc($lowStockProducts)): 
                    $lpImg = $lowP['image'];
                    if (strpos($lpImg, 'http') !== 0 && strpos($lpImg, 'data:') !== 0) {
                        $lpImg = $basePath . $lpImg;
                    }
                  ?>
                    <div class="d-flex justify-content-between align-items-center p-2 rounded-3 bg-light">
                      <div class="d-flex align-items-center gap-2 overflow-hidden">
                        <img src="<?php echo htmlspecialchars($lpImg); ?>" alt="<?php echo htmlspecialchars($lowP['name']); ?>" class="rounded-2 border bg-white p-0.5" style="width: 34px; height: 34px; object-fit: contain;">
                        <span class="small fw-semibold text-dark text-truncate" style="max-width: 240px;"><?php echo htmlspecialchars($lowP['name']); ?></span>
                      </div>
                      <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-warning text-dark"><?php echo $lowP['stock']; ?> left</span>
                        <span class="small text-emerald fw-bold"><?php echo formatLKR($lowP['price']); ?></span>
                      </div>
                    </div>
                  <?php endwhile; ?>
                </div>
              </div>
            </div>
          <?php endif; ?>

          <div class="<?php echo ($lowStockCount > 0) ? 'col-lg-6' : 'col-12'; ?>">
            <div class="card card-custom p-3 bg-white border-0 shadow-sm h-100 d-flex flex-column justify-content-between">
              <div class="d-flex justify-content-between align-items-center mb-2">
                <div class="fw-bold text-dark small d-flex align-items-center gap-1.5">
                  <i class="bi bi-chat-left-text-fill text-primary"></i>
                  <span>Patient Inquiries & Dispensary Support</span>
                </div>
                <a href="inquiries.php" class="small text-emerald text-decoration-none">View Inquiries &rarr;</a>
              </div>
              <div class="p-3 rounded-3 bg-light border d-flex align-items-center justify-content-between">
                <div>
                  <h5 class="fw-bold mb-0 text-dark"><?php echo $newInquiriesCount; ?> New Messages</h5>
                  <p class="text-muted small mb-0">Customer inquiries submitted through the contact page</p>
                </div>
                <a href="inquiries.php" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                  Reply &rarr;
                </a>
              </div>
            </div>
          </div>

        </div>
      <?php endif; ?>

      <!-- 3. RECENT ORDERS & PRESCRIPTIONS TABLES -->
      <div class="row g-4">
        
        <!-- Recent Customer Orders Table -->
        <div class="col-lg-7">
          <div class="card card-custom bg-white border-0 shadow-sm h-100 overflow-hidden">
            <div class="p-3 border-bottom d-flex justify-content-between align-items-center bg-light">
              <h6 class="fw-bold mb-0 text-dark d-flex align-items-center gap-1.5">
                <i class="bi bi-bag-check-fill text-emerald"></i> Recent Customer Orders
              </h6>
              <a href="orders.php" class="small text-emerald fw-semibold text-decoration-none">View All Orders &rarr;</a>
            </div>

            <div class="table-responsive">
              <table class="table table-hover align-middle mb-0 small">
                <thead class="table-light text-secondary text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.04em;">
                  <tr>
                    <th class="ps-3">Order Ref</th>
                    <th>Customer</th>
                    <th>City</th>
                    <th>Amount</th>
                    <th>Status</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if ($recentOrders && mysqli_num_rows($recentOrders) > 0): ?>
                    <?php while ($ord = mysqli_fetch_assoc($recentOrders)): 
                      $nameParts = explode(' ', trim($ord['customer_name']));
                      $initials = strtoupper(substr($nameParts[0], 0, 1) . (isset($nameParts[1]) ? substr($nameParts[1], 0, 1) : ''));
                    ?>
                      <tr>
                        <td class="ps-3 font-mono fw-bold">
                          <span class="badge bg-dark text-white">#MQ-<?php echo str_pad($ord['id'], 5, '0', STR_PAD_LEFT); ?></span>
                        </td>
                        <td>
                          <div class="d-flex align-items-center gap-2">
                            <div class="avatar-initials" style="width: 28px; height: 28px; font-size: 0.7rem;">
                              <?php echo htmlspecialchars($initials); ?>
                            </div>
                            <span class="fw-semibold text-dark"><?php echo htmlspecialchars($ord['customer_name']); ?></span>
                          </div>
                        </td>
                        <td class="text-secondary"><?php echo htmlspecialchars($ord['city']); ?></td>
                        <td class="fw-bold text-emerald"><?php echo formatLKR($ord['total_amount']); ?></td>
                        <td>
                          <?php 
                            $st = $ord['status'];
                            $badgeClass = 'bg-secondary';
                            if ($st === 'Pending') $badgeClass = 'bg-warning-subtle text-warning border border-warning-subtle';
                            elseif ($st === 'Processing') $badgeClass = 'bg-info-subtle text-info border border-info-subtle';
                            elseif ($st === 'Delivered') $badgeClass = 'bg-success-subtle text-success border border-success-subtle';
                            elseif ($st === 'Cancelled') $badgeClass = 'bg-danger-subtle text-danger border border-danger-subtle';
                          ?>
                          <span class="badge rounded-pill px-2.5 py-1 <?php echo $badgeClass; ?>"><?php echo htmlspecialchars($st); ?></span>
                        </td>
                      </tr>
                    <?php endwhile; ?>
                  <?php else: ?>
                    <tr><td colspan="5" class="text-center text-muted py-4">No customer orders found in database.</td></tr>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- Recent Prescriptions Table -->
        <div class="col-lg-5">
          <div class="card card-custom bg-white border-0 shadow-sm h-100 overflow-hidden">
            <div class="p-3 border-bottom d-flex justify-content-between align-items-center bg-light">
              <h6 class="fw-bold mb-0 text-dark d-flex align-items-center gap-1.5">
                <i class="bi bi-file-earmark-medical-fill text-warning"></i> Recent Prescriptions
              </h6>
              <a href="prescriptions.php" class="small text-emerald fw-semibold text-decoration-none">Review All &rarr;</a>
            </div>

            <div class="table-responsive">
              <table class="table table-hover align-middle mb-0 small">
                <thead class="table-light text-secondary text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.04em;">
                  <tr>
                    <th class="ps-3">Rx Ref</th>
                    <th>Patient</th>
                    <th class="text-center">Slip</th>
                    <th class="text-center">Status</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if ($recentRx && mysqli_num_rows($recentRx) > 0): ?>
                    <?php while ($rx = mysqli_fetch_assoc($recentRx)): 
                      $isPdf = strpos($rx['image'], 'application/pdf') !== false;
                    ?>
                      <tr>
                        <td class="ps-3 font-mono fw-bold">
                          <span class="badge bg-dark text-white">#RX-<?php echo str_pad($rx['id'], 4, '0', STR_PAD_LEFT); ?></span>
                        </td>
                        <td>
                          <div class="fw-semibold text-dark"><?php echo htmlspecialchars($rx['patient_name']); ?></div>
                          <small class="text-muted"><?php echo date('M d', strtotime($rx['created_at'])); ?></small>
                        </td>
                        <td class="text-center">
                          <button type="button" class="btn btn-sm btn-outline-primary py-0.5 px-2 rounded-pill" style="font-size: 0.75rem;" 
                                  onclick="previewDashboardRx('<?php echo htmlspecialchars($rx['image'], ENT_QUOTES); ?>', '#RX-<?php echo str_pad($rx['id'], 4, '0', STR_PAD_LEFT); ?>', '<?php echo htmlspecialchars($rx['patient_name'], ENT_QUOTES); ?>')">
                            <i class="bi <?php echo $isPdf ? 'bi-file-earmark-pdf' : 'bi-image'; ?> me-1"></i> View
                          </button>
                        </td>
                        <td class="text-center">
                          <?php 
                            $rxSt = $rx['status'];
                            $rxBadge = 'bg-warning-subtle text-warning border border-warning-subtle';
                            if ($rxSt === 'Approved') $rxBadge = 'bg-success-subtle text-success border border-success-subtle';
                            elseif ($rxSt === 'Rejected') $rxBadge = 'bg-danger-subtle text-danger border border-danger-subtle';
                          ?>
                          <span class="badge rounded-pill px-2.5 py-1 <?php echo $rxBadge; ?>"><?php echo htmlspecialchars($rxSt); ?></span>
                        </td>
                      </tr>
                    <?php endwhile; ?>
                  <?php else: ?>
                    <tr><td colspan="4" class="text-center text-muted py-4">No prescriptions found.</td></tr>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>

      </div>

    </div>
  </div>
</div>

<!-- Dashboard Prescription Quick View Modal -->
<div class="modal fade" id="dashboardRxModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg" style="border-radius: 20px; overflow: hidden;">
      <div class="modal-header bg-dark text-white py-2.5 px-3">
        <h6 class="modal-title font-heading fw-bold mb-0" id="dashRxTitle">
          <i class="bi bi-file-earmark-medical text-emerald me-2"></i> Doctor's Prescription Slip
        </h6>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-3 text-center bg-light">
        <div class="mb-2 text-muted small fw-semibold" id="dashRxSub"></div>
        <div style="max-height: 65vh; overflow: auto;" class="border rounded bg-white p-2 text-center shadow-sm">
          <img id="dashRxImg" src="" alt="Doctor's Prescription" class="img-fluid rounded" style="max-height: 60vh; object-fit: contain;">
        </div>
      </div>
      <div class="modal-footer py-2 bg-white d-flex justify-content-between">
        <a href="prescriptions.php" class="btn btn-sm btn-emerald fw-bold">
          <i class="bi bi-clipboard2-pulse me-1"></i> Open Verification Portal &rarr;
        </a>
        <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<script>
function previewDashboardRx(imgSrc, refCode, patientName) {
  document.getElementById('dashRxTitle').innerHTML = '<i class="bi bi-file-earmark-medical text-emerald me-2"></i> Doctor\'s Prescription &mdash; ' + refCode;
  document.getElementById('dashRxSub').textContent = 'Patient: ' + patientName;
  document.getElementById('dashRxImg').src = imgSrc;
  const modal = new bootstrap.Modal(document.getElementById('dashboardRxModal'));
  modal.show();
}
</script>

<!-- Bootstrap Bundle & JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/main.js"></script>
</body>
</html>
