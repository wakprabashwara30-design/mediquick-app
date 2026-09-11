<?php
/**
 * MediQuick Pharmacy - Reusable Admin Left Sidebar Include
 */
$currentAdminScript = basename($_SERVER['PHP_SELF']);
$adminName = $_SESSION['user_name'] ?? 'Staff Admin';
?>
<!-- Mobile Admin Sidebar Backdrop -->
<div id="adminSidebarBackdrop" class="admin-sidebar-backdrop" onclick="toggleAdminSidebar()"></div>

<aside class="admin-sidebar p-3 d-flex flex-column justify-content-between">
  
  <!-- Top Brand & Navigation -->
  <div>
    <!-- Brand Logo & Mobile Close -->
    <div class="d-flex align-items-center justify-content-between mb-4 border-bottom border-secondary border-opacity-25 pb-3">
      <a href="index.php" class="d-flex align-items-center gap-2.5 text-decoration-none px-2">
        <div class="rounded-3 d-flex align-items-center justify-content-center text-white shadow-sm" style="width: 2.4rem; height: 2.4rem; background: linear-gradient(135deg, #059669 0%, #10b981 100%);">
          <i class="bi bi-heart-pulse-fill fs-5"></i>
        </div>
        <div>
          <div class="fs-5 fw-bold text-white font-heading lh-1">
            Medi<span class="text-emerald">Quick</span>
          </div>
          <span class="badge bg-success bg-opacity-25 text-success border border-success border-opacity-25 mt-1" style="font-size: 0.65rem;">
            Staff Portal
          </span>
        </div>
      </a>
      <!-- Close button on mobile -->
      <button class="btn btn-sm btn-outline-secondary d-lg-none text-white border-0" onclick="toggleAdminSidebar()" aria-label="Close sidebar">
        <i class="bi bi-x-lg fs-5"></i>
      </button>
    </div>

    <!-- Navigation Menu -->
    <div class="small text-uppercase text-secondary fw-bold px-2 mb-1.5" style="font-size: 0.68rem; letter-spacing: 0.06em;">
      Pharmacy Operations
    </div>

    <nav class="nav flex-column mb-3">
      <a class="admin-nav-link <?php echo ($currentAdminScript === 'index.php') ? 'active' : ''; ?>" href="index.php">
        <i class="bi bi-grid-1x2-fill"></i>
        <span>Dashboard</span>
      </a>

      <a class="admin-nav-link <?php echo ($currentAdminScript === 'products.php') ? 'active' : ''; ?>" href="products.php">
        <i class="bi bi-capsule"></i>
        <span>Manage Products</span>
      </a>

      <a class="admin-nav-link <?php echo ($currentAdminScript === 'orders.php') ? 'active' : ''; ?>" href="orders.php">
        <i class="bi bi-bag-check-fill"></i>
        <span>Manage Orders</span>
      </a>

      <a class="admin-nav-link <?php echo ($currentAdminScript === 'prescriptions.php') ? 'active' : ''; ?>" href="prescriptions.php">
        <i class="bi bi-file-earmark-medical-fill"></i>
        <span>Prescriptions</span>
      </a>
    </nav>

    <div class="small text-uppercase text-secondary fw-bold px-2 mb-1.5" style="font-size: 0.68rem; letter-spacing: 0.06em;">
      CRM & Health Content
    </div>

    <nav class="nav flex-column mb-3">
      <a class="admin-nav-link <?php echo ($currentAdminScript === 'customers.php') ? 'active' : ''; ?>" href="customers.php">
        <i class="bi bi-people-fill"></i>
        <span>Customers</span>
      </a>

      <a class="admin-nav-link <?php echo ($currentAdminScript === 'blogs.php') ? 'active' : ''; ?>" href="blogs.php">
        <i class="bi bi-journal-medical"></i>
        <span>Health Blogs</span>
      </a>

      <a class="admin-nav-link <?php echo ($currentAdminScript === 'inquiries.php') ? 'active' : ''; ?>" href="inquiries.php">
        <i class="bi bi-chat-left-dots-fill"></i>
        <span>Inquiries</span>
      </a>
    </nav>

    <div class="small text-uppercase text-secondary fw-bold px-2 mb-1.5" style="font-size: 0.68rem; letter-spacing: 0.06em;">
      Administration
    </div>

    <nav class="nav flex-column mb-3">
      <a class="admin-nav-link <?php echo ($currentAdminScript === 'users.php') ? 'active' : ''; ?>" href="users.php">
        <i class="bi bi-shield-lock-fill"></i>
        <span>Staff & Users</span>
      </a>
    </nav>
  </div>

  <!-- Bottom Account & Storefront Switcher -->
  <div class="border-top border-secondary border-opacity-25 pt-3">
    
    <!-- User Badge -->
    <div class="d-flex align-items-center gap-2.5 px-2 mb-3">
      <div class="rounded-circle bg-emerald-subtle text-emerald d-flex align-items-center justify-content-center fw-bold" style="width: 36px; height: 36px; font-size: 0.85rem;">
        <i class="bi bi-person-fill"></i>
      </div>
      <div class="overflow-hidden">
        <div class="text-white small fw-bold text-truncate"><?php echo htmlspecialchars($adminName); ?></div>
        <div class="text-secondary" style="font-size: 0.72rem;">SLMC Administrator</div>
      </div>
    </div>

    <div class="d-grid gap-1.5">
      <a href="../index.php" target="_blank" class="btn btn-sm btn-outline-light text-start py-1.5 px-2.5" style="font-size: 0.8rem;">
        <i class="bi bi-shop me-1.5 text-success"></i> View Storefront &rarr;
      </a>
      <a href="logout.php" class="btn btn-sm btn-outline-danger text-start py-1.5 px-2.5" style="font-size: 0.8rem;">
        <i class="bi bi-box-arrow-right me-1.5"></i> Logout
      </a>
    </div>

  </div>

</aside>

<script>
function toggleAdminSidebar() {
  const sidebar = document.querySelector('.admin-sidebar');
  const backdrop = document.getElementById('adminSidebarBackdrop');
  if (sidebar) sidebar.classList.toggle('show');
  if (backdrop) backdrop.classList.toggle('show');
}
</script>
