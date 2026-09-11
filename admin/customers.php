<?php
/**
 * MediQuick Pharmacy - Registered Customers & Patient Directory Panel
 * Module: CSE4206 - Web Application Development (Kurunegala)
 */
$basePath = '../';
require_once __DIR__ . '/../config/db.php';

// Security check: Only authenticated admins
if (!isAdmin()) {
    header('Location: ../login.php');
    exit();
}

$successMsg = '';
$errorMsg = '';

// Handle Delete Customer Action
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $deleteId = (int)$_GET['id'];
    if ($deleteId > 0) {
        // Prevent deleting admin accounts from customer panel
        $chk = mysqli_query($conn, "SELECT role, name FROM users WHERE id = $deleteId");
        $userRow = mysqli_fetch_assoc($chk);
        if ($userRow && $userRow['role'] === 'customer') {
            $delQuery = "DELETE FROM users WHERE id = $deleteId";
            if (mysqli_query($conn, $delQuery)) {
                $successMsg = "Customer account for '" . htmlspecialchars($userRow['name']) . "' deleted successfully.";
            } else {
                $errorMsg = "Could not delete customer account: " . mysqli_error($conn);
            }
        } else {
            $errorMsg = "Cannot delete admin account via customer panel.";
        }
    }
}

// Fetch all customers with aggregated metrics
$customersSql = "
    SELECT 
        u.*,
        COUNT(DISTINCT o.id) AS total_orders,
        IFNULL(SUM(o.total_amount), 0) AS total_spent,
        COUNT(DISTINCT p.id) AS total_prescriptions
    FROM users u
    LEFT JOIN orders o ON u.id = o.user_id
    LEFT JOIN prescriptions p ON u.id = p.user_id
    WHERE u.role = 'customer'
    GROUP BY u.id
    ORDER BY u.id DESC
";
$customersQuery = mysqli_query($conn, $customersSql);

// Calculate KPI Metrics
$totalCustomers = 0;
$activeBuyers = 0;
$totalSpentAll = 0.0;
$totalRxUsers = 0;

$customerList = [];
if ($customersQuery) {
    while ($row = mysqli_fetch_assoc($customersQuery)) {
        $totalCustomers++;
        if ($row['total_orders'] > 0) $activeBuyers++;
        $totalSpentAll += (float)$row['total_spent'];
        if ($row['total_prescriptions'] > 0) $totalRxUsers++;
        $customerList[] = $row;
    }
}

$pageTitle = 'Registered Customers | MediQuick Admin';
include_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex" style="min-height: 100vh;">
  <!-- 1. LEFT SIDEBAR -->
  <?php include_once __DIR__ . '/../includes/admin_sidebar.php'; ?>

  <!-- 2. MAIN CONTENT AREA -->
  <div class="flex-grow-1 d-flex flex-column" style="background-color: #f8fafc; min-width: 0;">
    
    <!-- Admin Topbar -->
    <div class="admin-topbar d-flex justify-content-between align-items-center">
      <div class="d-flex align-items-center gap-2">
        <button class="btn btn-sm btn-light d-lg-none border px-2 py-1 shadow-none" type="button" onclick="toggleAdminSidebar()" aria-label="Toggle Menu">
          <i class="bi bi-list fs-5"></i>
        </button>
        <div class="small text-secondary fw-semibold text-truncate">
          <i class="bi bi-people-fill text-emerald me-1"></i> <span class="d-none d-sm-inline">Customer Relationship &mdash; </span>Registered Customers
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
          <h3 class="fw-bold text-dark mb-1 font-heading">Registered Customers & Patients</h3>
          <p class="text-muted small mb-0">View registered patient accounts, order history, lifetime value, and contact profiles</p>
        </div>
        <div class="d-flex align-items-center gap-2">
          <span class="badge bg-emerald-subtle text-emerald border border-emerald px-3 py-2 rounded-pill fw-semibold">
            <i class="bi bi-person-check-fill me-1"></i> <?php echo $totalCustomers; ?> Total Accounts
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
              <div class="text-muted small fw-semibold mb-1">Total Registered</div>
              <h3 class="fw-bold mb-0 text-dark"><?php echo $totalCustomers; ?></h3>
            </div>
            <div class="rounded-3 d-flex align-items-center justify-content-center text-primary" style="width: 46px; height: 46px; background-color: #e0f2fe;">
              <i class="bi bi-people fs-4"></i>
            </div>
          </div>
        </div>

        <div class="col-6 col-lg-3">
          <div class="stat-card-modern d-flex align-items-center justify-content-between" style="border-left: 4px solid #059669;">
            <div>
              <div class="text-muted small fw-semibold mb-1">Active Buyers</div>
              <h3 class="fw-bold mb-0 text-emerald"><?php echo $activeBuyers; ?></h3>
            </div>
            <div class="rounded-3 d-flex align-items-center justify-content-center text-emerald" style="width: 46px; height: 46px; background-color: #ecfdf5;">
              <i class="bi bi-cart-check fs-4"></i>
            </div>
          </div>
        </div>

        <div class="col-6 col-lg-3">
          <div class="stat-card-modern d-flex align-items-center justify-content-between" style="border-left: 4px solid #3b82f6;">
            <div>
              <div class="text-muted small fw-semibold mb-1">Customer Revenue</div>
              <h3 class="fw-bold mb-0 text-primary"><?php echo formatLKR($totalSpentAll); ?></h3>
            </div>
            <div class="rounded-3 d-flex align-items-center justify-content-center text-primary" style="width: 46px; height: 46px; background-color: #eff6ff;">
              <i class="bi bi-cash-stack fs-4"></i>
            </div>
          </div>
        </div>

        <div class="col-6 col-lg-3">
          <div class="stat-card-modern d-flex align-items-center justify-content-between" style="border-left: 4px solid #8b5cf6;">
            <div>
              <div class="text-muted small fw-semibold mb-1">Rx Uploaders</div>
              <h3 class="fw-bold mb-0" style="color: #7c3aed;"><?php echo $totalRxUsers; ?></h3>
            </div>
            <div class="rounded-3 d-flex align-items-center justify-content-center" style="width: 46px; height: 46px; background-color: #f5f3ff; color: #7c3aed;">
              <i class="bi bi-file-earmark-medical fs-4"></i>
            </div>
          </div>
        </div>
      </div>

      <!-- Main Card with Toolbar & Table -->
      <div class="card card-custom bg-white border-0 shadow-sm overflow-hidden">
        
        <!-- Filter & Search Toolbar -->
        <div class="p-3 border-bottom bg-light d-flex flex-wrap align-items-center justify-content-between gap-3">
          <div class="fw-bold text-dark small d-flex align-items-center gap-1.5">
            <i class="bi bi-person-lines-fill text-emerald"></i>
            Customer Directory (<?php echo count($customerList); ?>)
          </div>

          <!-- Instant Live Search Input -->
          <div class="position-relative" style="min-width: 280px;">
            <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
            <input type="text" id="customerLiveSearch" class="form-control form-control-sm ps-5 rounded-pill" placeholder="Search by name, email, or phone...">
          </div>
        </div>

        <!-- Customers Table -->
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0" id="customerTable">
            <thead class="bg-light text-secondary" style="font-size: 0.76rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; border-bottom: 2px solid #e2e8f0;">
              <tr>
                <th class="ps-4 py-3.5" style="width: 80px;">ID</th>
                <th style="min-width: 240px;">Customer Profile</th>
                <th style="min-width: 240px;">Contact & Location</th>
                <th class="text-center" style="width: 140px;">Orders Placed</th>
                <th class="text-center" style="width: 150px;">Total Spent</th>
                <th class="text-end pe-4" style="width: 110px;">Actions</th>
              </tr>
            </thead>
            <tbody class="small">
              <?php if (!empty($customerList)): ?>
                <?php foreach ($customerList as $cust): 
                  $nameParts = explode(' ', trim($cust['name']));
                  $initials = strtoupper(substr($nameParts[0], 0, 1) . (isset($nameParts[1]) ? substr($nameParts[1], 0, 1) : ''));
                ?>
                  <tr class="customer-row-item" style="transition: background-color 0.15s ease;">
                    <!-- ID -->
                    <td class="ps-4 py-3.5 font-mono text-muted">
                      #C-<?php echo str_pad($cust['id'], 4, '0', STR_PAD_LEFT); ?>
                    </td>

                    <!-- Profile -->
                    <td class="py-3.5">
                      <div class="d-flex align-items-center gap-3">
                        <div class="avatar-initials shadow-xs" style="width: 42px; height: 42px; font-size: 0.9rem; flex-shrink: 0;">
                          <?php echo htmlspecialchars($initials); ?>
                        </div>
                        <div>
                          <strong class="text-dark d-block cust-name" style="font-size: 0.92rem;">
                            <?php echo htmlspecialchars($cust['name']); ?>
                          </strong>
                          <div class="text-muted d-flex align-items-center gap-1 mt-0.5" style="font-size: 0.75rem;">
                            <i class="bi bi-calendar3"></i> Joined <?php echo date('M d, Y', strtotime($cust['created_at'])); ?>
                          </div>
                        </div>
                      </div>
                    </td>

                    <!-- Contact & Address -->
                    <td class="py-3.5">
                      <div class="d-flex flex-column gap-1">
                        <a href="mailto:<?php echo htmlspecialchars($cust['email']); ?>" class="text-dark text-decoration-none d-inline-flex align-items-center gap-1 cust-email">
                          <i class="bi bi-envelope-fill text-emerald" style="font-size: 0.8rem;"></i> <?php echo htmlspecialchars($cust['email']); ?>
                        </a>
                        <?php if (!empty($cust['phone'])): ?>
                          <a href="tel:<?php echo htmlspecialchars($cust['phone']); ?>" class="text-secondary text-decoration-none d-inline-flex align-items-center gap-1 cust-phone" style="font-size: 0.78rem;">
                            <i class="bi bi-telephone-fill text-muted" style="font-size: 0.72rem;"></i> <?php echo htmlspecialchars($cust['phone']); ?>
                          </a>
                        <?php endif; ?>
                        <?php if (!empty($cust['address'])): ?>
                          <div class="text-muted text-truncate" style="max-width: 240px; font-size: 0.75rem;" title="<?php echo htmlspecialchars($cust['address']); ?>">
                            <i class="bi bi-geo-alt-fill text-secondary"></i> <?php echo htmlspecialchars($cust['address']); ?>
                          </div>
                        <?php endif; ?>
                      </div>
                    </td>

                    <!-- Orders Placed Badge -->
                    <td class="text-center py-3.5">
                      <?php if ($cust['total_orders'] > 0): ?>
                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1 rounded-pill fw-semibold">
                          <i class="bi bi-bag-check-fill me-1"></i> <?php echo $cust['total_orders']; ?> Orders
                        </span>
                      <?php else: ?>
                        <span class="badge bg-light text-secondary border px-2.5 py-1 rounded-pill">0 Orders</span>
                      <?php endif; ?>
                    </td>

                    <!-- Total Spend in LKR -->
                    <td class="text-center py-3.5">
                      <strong class="text-emerald d-block" style="font-size: 0.9rem;">
                        <?php echo formatLKR($cust['total_spent']); ?>
                      </strong>
                      <?php if ($cust['total_prescriptions'] > 0): ?>
                        <small class="text-muted d-block" style="font-size: 0.7rem;">
                          <i class="bi bi-file-earmark-medical text-emerald"></i> <?php echo $cust['total_prescriptions']; ?> Rx Uploaded
                        </small>
                      <?php endif; ?>
                    </td>

                    <!-- Actions -->
                    <td class="text-end pe-4 py-3.5">
                      <a href="customers.php?action=delete&id=<?php echo $cust['id']; ?>" class="btn btn-sm btn-outline-danger p-1.5 rounded-3" 
                         onclick="return confirmAction('Are you sure you want to delete customer account <?php echo htmlspecialchars(addslashes($cust['name'])); ?> (#C-<?php echo str_pad($cust['id'], 4, '0', STR_PAD_LEFT); ?>)? This will remove their profile record.', 'Delete Customer?');" title="Delete Customer">
                        <i class="bi bi-trash"></i>
                      </a>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr>
                  <td colspan="6" class="text-center text-muted py-5">
                    <i class="bi bi-people fs-1 d-block mb-2 text-secondary"></i>
                    No registered customers found.
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

<script>
// Instant Search for Customer Table
document.addEventListener('DOMContentLoaded', function() {
  const searchInput = document.getElementById('customerLiveSearch');
  if (searchInput) {
    searchInput.addEventListener('input', function() {
      const query = this.value.toLowerCase().trim();
      const rows = document.querySelectorAll('.customer-row-item');

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
