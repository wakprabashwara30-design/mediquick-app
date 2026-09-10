<?php
/**
 * MediQuick Pharmacy - Admin Dashboard
 * University 1st-Year Web Application (PHP & MySQL Admin Portal)
 */
$basePath = '../';
require_once __DIR__ . '/../config/db.php';

// Check Admin Access
if (!isAdmin()) {
    header("Location: ../login.php");
    exit();
}

// 1. Fetch Summary Statistics
$totalProducts = 0;
$totalOrders = 0;
$totalPrescriptions = 0;
$totalRevenue = 0;

$pCountRes = mysqli_query($conn, "SELECT COUNT(*) as count FROM products");
if ($pCountRes) $totalProducts = mysqli_fetch_assoc($pCountRes)['count'] ?? 0;

$oCountRes = mysqli_query($conn, "SELECT COUNT(*) as count, SUM(total_amount) as revenue FROM orders");
if ($oCountRes) {
    $row = mysqli_fetch_assoc($oCountRes);
    $totalOrders = $row['count'] ?? 0;
    $totalRevenue = $row['revenue'] ?? 0;
}

$rxCountRes = mysqli_query($conn, "SELECT COUNT(*) as count FROM prescriptions");
if ($rxCountRes) $totalPrescriptions = mysqli_fetch_assoc($rxCountRes)['count'] ?? 0;

// 2. Fetch Recent 5 Orders
$recentOrders = mysqli_query($conn, "SELECT * FROM orders ORDER BY id DESC LIMIT 5");

// 3. Fetch Recent 5 Prescriptions
$recentRx = mysqli_query($conn, "SELECT * FROM prescriptions ORDER BY id DESC LIMIT 5");

$pageTitle = 'Admin Dashboard | MediQuick Pharmacy';
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
        <i class="bi bi-geo-alt-fill text-emerald me-1"></i> Kurunegala Central Dispensary &mdash; Admin Management
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
      
      <!-- Heading -->
      <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
          <h3 class="fw-bold mb-0">Pharmacy Management Dashboard</h3>
          <p class="text-muted small mb-0">Welcome back, <strong><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Administrator'); ?></strong></p>
        </div>
        <a href="products.php" class="btn btn-emerald btn-sm py-2 px-3">
          <i class="bi bi-plus-circle me-1"></i> Add New Product
        </a>
      </div>

    <!-- 1. STAT CARDS ROW -->
    <div class="row g-3 mb-4">
      
      <!-- Total Products -->
      <div class="col-sm-6 col-xl-3">
        <div class="stat-card d-flex align-items-center justify-content-between">
          <div>
            <span class="text-muted small fw-semibold">Total Products</span>
            <h3 class="fw-bold text-dark mt-1 mb-0"><?php echo $totalProducts; ?></h3>
            <a href="products.php" class="small text-emerald text-decoration-none">Manage inventory &rarr;</a>
          </div>
          <div class="stat-icon bg-emerald-subtle text-emerald">
            <i class="bi bi-capsule"></i>
          </div>
        </div>
      </div>

      <!-- Total Orders -->
      <div class="col-sm-6 col-xl-3">
        <div class="stat-card d-flex align-items-center justify-content-between">
          <div>
            <span class="text-muted small fw-semibold">Total Orders</span>
            <h3 class="fw-bold text-dark mt-1 mb-0"><?php echo $totalOrders; ?></h3>
            <a href="orders.php" class="small text-emerald text-decoration-none">View customer orders &rarr;</a>
          </div>
          <div class="stat-icon bg-primary-subtle text-primary">
            <i class="bi bi-bag-check"></i>
          </div>
        </div>
      </div>

      <!-- Prescriptions -->
      <div class="col-sm-6 col-xl-3">
        <div class="stat-card d-flex align-items-center justify-content-between">
          <div>
            <span class="text-muted small fw-semibold">Prescriptions</span>
            <h3 class="fw-bold text-dark mt-1 mb-0"><?php echo $totalPrescriptions; ?></h3>
            <a href="prescriptions.php" class="small text-emerald text-decoration-none">Review doctor slips &rarr;</a>
          </div>
          <div class="stat-icon bg-warning-subtle text-warning">
            <i class="bi bi-file-earmark-medical"></i>
          </div>
        </div>
      </div>

      <!-- Total Revenue -->
      <div class="col-sm-6 col-xl-3">
        <div class="stat-card d-flex align-items-center justify-content-between">
          <div>
            <span class="text-muted small fw-semibold">Total Sales Revenue</span>
            <h3 class="fw-bold text-dark mt-1 mb-0"><?php echo formatLKR($totalRevenue); ?></h3>
            <span class="small text-muted">All completed & pending</span>
          </div>
          <div class="stat-icon bg-success-subtle text-success">
            <i class="bi bi-cash-stack"></i>
          </div>
        </div>
      </div>

    </div>

    <!-- 2. RECENT ORDERS & PRESCRIPTIONS TABLES -->
    <div class="row g-4">
      
      <!-- Recent Orders Table -->
      <div class="col-lg-7">
        <div class="card card-custom p-3 bg-white h-100">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="fw-bold mb-0"><i class="bi bi-bag-check text-emerald me-1"></i> Recent Customer Orders</h6>
            <a href="orders.php" class="small text-emerald">View All &rarr;</a>
          </div>

          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 small">
              <thead class="table-light">
                <tr>
                  <th>Order Ref</th>
                  <th>Customer</th>
                  <th>City</th>
                  <th>Amount</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody>
                <?php if ($recentOrders && mysqli_num_rows($recentOrders) > 0): ?>
                  <?php while ($ord = mysqli_fetch_assoc($recentOrders)): ?>
                    <tr>
                      <td class="font-mono fw-bold">#MQ-<?php echo str_pad($ord['id'], 5, '0', STR_PAD_LEFT); ?></td>
                      <td><?php echo htmlspecialchars($ord['customer_name']); ?></td>
                      <td><?php echo htmlspecialchars($ord['city']); ?></td>
                      <td class="fw-bold text-emerald"><?php echo formatLKR($ord['total_amount']); ?></td>
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
                    </tr>
                  <?php endwhile; ?>
                <?php else: ?>
                  <tr><td colspan="5" class="text-center text-muted py-3">No orders found.</td></tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Recent Prescriptions Table -->
      <div class="col-lg-5">
        <div class="card card-custom p-3 bg-white h-100">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="fw-bold mb-0"><i class="bi bi-file-earmark-medical text-warning me-1"></i> Recent Prescriptions</h6>
            <a href="prescriptions.php" class="small text-emerald">Review All &rarr;</a>
          </div>

          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 small">
              <thead class="table-light">
                <tr>
                  <th>Rx Ref</th>
                  <th>Patient</th>
                  <th>Slip</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody>
                <?php if ($recentRx && mysqli_num_rows($recentRx) > 0): ?>
                  <?php while ($rx = mysqli_fetch_assoc($recentRx)): ?>
                    <tr>
                      <td class="font-mono fw-bold">#RX-<?php echo str_pad($rx['id'], 4, '0', STR_PAD_LEFT); ?></td>
                      <td><?php echo htmlspecialchars($rx['patient_name']); ?></td>
                      <td>
                        <a href="../<?php echo htmlspecialchars($rx['image']); ?>" target="_blank" class="text-emerald">
                          <i class="bi bi-file-earmark-image"></i> View
                        </a>
                      </td>
                      <td>
                        <?php 
                          $rxSt = $rx['status'];
                          $rxBadge = 'bg-warning text-dark';
                          if ($rxSt === 'Approved') $rxBadge = 'bg-success';
                          elseif ($rxSt === 'Rejected') $rxBadge = 'bg-danger';
                        ?>
                        <span class="badge <?php echo $rxBadge; ?>"><?php echo htmlspecialchars($rxSt); ?></span>
                      </td>
                    </tr>
                  <?php endwhile; ?>
                <?php else: ?>
                  <tr><td colspan="4" class="text-center text-muted py-3">No prescriptions found.</td></tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>

    </div>
  </div>
</div>

<!-- Bootstrap Bundle & JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/main.js"></script>
</body>
</html>
