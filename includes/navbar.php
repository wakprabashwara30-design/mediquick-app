<?php
/**
 * MediQuick Pharmacy - Clean Navigation Bar Include
 */
if (!isset($basePath)) {
    $basePath = '';
}
$currentScript = basename($_SERVER['PHP_SELF']);
$cartCount = function_exists('getCartCount') ? getCartCount() : 0;
?>

<!-- Top Notice Strip -->
<div class="py-1 px-3 bg-dark text-white text-center no-print" style="font-size: 0.8rem; background-color: #0f172a !important;">
  <div class="container d-flex justify-content-between align-items-center">
    <div>
      <i class="bi bi-shield-check text-success me-1"></i>
      <span>Licensed Online Pharmacy - Kurunegala, Sri Lanka</span>
    </div>
    <div class="d-none d-md-block">
      <i class="bi bi-telephone-fill text-success me-1"></i>
      <span>Pharmacist Helpline: <strong>+94 37 222 3456</strong></span>
    </div>
  </div>
</div>

<!-- Main Navbar -->
<nav class="navbar navbar-expand-lg navbar-custom sticky-top">
  <div class="container">
    
    <!-- Brand Logo -->
    <a class="navbar-brand d-flex align-items-center gap-2" href="<?php echo $basePath; ?>index.php">
      <div class="rounded-3 d-flex align-items-center justify-content-center text-white" style="width: 2.4rem; height: 2.4rem; background-color: #059669;">
        <i class="bi bi-heart-pulse-fill fs-5"></i>
      </div>
      <div>
        <span class="fs-4 fw-bold text-dark font-heading">Medi<span class="text-emerald">Quick</span></span>
        <span class="navbar-brand-badge ms-1">Rx</span>
      </div>
    </a>

    <!-- Mobile Action Icons (Cart Badge + Drawer Trigger) -->
    <div class="d-flex align-items-center gap-2 d-lg-none">
      <a href="<?php echo $basePath; ?>cart.php" class="mobile-nav-btn" aria-label="Shopping Cart">
        <i class="bi bi-cart3 fs-5"></i>
        <?php if ($cartCount > 0): ?>
          <span class="cart-badge-pill"><?php echo $cartCount; ?></span>
        <?php endif; ?>
      </a>
      <button class="mobile-nav-btn" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileNavDrawer" aria-controls="mobileNavDrawer" aria-label="Toggle navigation">
        <i class="bi bi-list fs-4"></i>
      </button>
    </div>

    <!-- Desktop Horizontal Menu Bar (>= 992px) -->
    <div class="collapse navbar-collapse d-none d-lg-flex" id="desktopNavbar">
      
      <!-- Center Navigation Links -->
      <ul class="navbar-nav mx-auto mb-2 mb-lg-0 gap-1">
        <li class="nav-item">
          <a class="nav-link <?php echo ($currentScript === 'index.php') ? 'active' : ''; ?>" href="<?php echo $basePath; ?>index.php">
            Home
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?php echo ($currentScript === 'shop.php' || $currentScript === 'product-details.php') ? 'active' : ''; ?>" href="<?php echo $basePath; ?>shop.php">
            Medicines
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?php echo ($currentScript === 'prescription.php') ? 'active' : ''; ?>" href="<?php echo $basePath; ?>prescription.php">
            Upload Rx
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?php echo ($currentScript === 'blogs.php') ? 'active' : ''; ?>" href="<?php echo $basePath; ?>blogs.php">
            Health Blogs
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?php echo ($currentScript === 'about.php') ? 'active' : ''; ?>" href="<?php echo $basePath; ?>about.php">
            About Us
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?php echo ($currentScript === 'contact.php') ? 'active' : ''; ?>" href="<?php echo $basePath; ?>contact.php">
            Contact
          </a>
        </li>
      </ul>

      <!-- Right Action Items -->
      <div class="d-flex align-items-center gap-2">
        <a href="<?php echo $basePath; ?>cart.php" class="btn btn-soft-emerald position-relative d-flex align-items-center gap-1.5 px-3 py-2">
          <i class="bi bi-cart3 fs-6"></i>
          <span class="fw-semibold">Cart</span>
          <?php if ($cartCount > 0): ?>
            <span class="badge bg-danger rounded-pill ms-1" style="font-size: 0.75rem;">
              <?php echo $cartCount; ?>
            </span>
          <?php endif; ?>
        </a>

        <?php if (isset($_SESSION['user_id'])): ?>
          <div class="dropdown">
            <button class="btn btn-outline-emerald dropdown-toggle d-flex align-items-center gap-1.5 px-3 py-2" type="button" data-bs-toggle="dropdown" aria-expanded="false">
              <i class="bi bi-person-circle"></i>
              <span><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Account'); ?></span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 mt-2">
              <li class="px-3 py-1 text-muted small">
                Signed in as <strong><?php echo htmlspecialchars($_SESSION['role'] ?? 'Customer'); ?></strong>
              </li>
              <li><hr class="dropdown-divider"></li>
              <li>
                <a class="dropdown-item d-flex align-items-center gap-2" href="<?php echo $basePath; ?>my-orders.php">
                  <i class="bi bi-bag-check text-emerald"></i> My Orders & Rx Status
                </a>
              </li>
              <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                <li>
                  <a class="dropdown-item d-flex align-items-center gap-2" href="<?php echo $basePath; ?>admin/index.php">
                    <i class="bi bi-speedometer2 text-primary"></i> Admin Dashboard
                  </a>
                </li>
              <?php endif; ?>
              <li><hr class="dropdown-divider"></li>
              <li>
                <a class="dropdown-item d-flex align-items-center gap-2 text-danger" href="<?php echo $basePath; ?>logout.php">
                  <i class="bi bi-box-arrow-right"></i> Logout
                </a>
              </li>
            </ul>
          </div>
        <?php else: ?>
          <a href="<?php echo $basePath; ?>login.php" class="btn btn-outline-emerald px-3 py-2">
            <i class="bi bi-box-arrow-in-right me-1"></i> Login
          </a>
          <a href="<?php echo $basePath; ?>register.php" class="btn btn-emerald px-3 py-2">
            Register
          </a>
        <?php endif; ?>
      </div>
    </div>

  </div>
</nav>

<!-- Mobile Offcanvas Drawer (< 992px only) - Positioned outside navbar to prevent backdrop-filter coordinate trapping on mobile devices -->
<div class="offcanvas offcanvas-end d-lg-none" tabindex="-1" id="mobileNavDrawer" aria-labelledby="mobileNavDrawerLabel">
  <div class="offcanvas-header border-bottom py-2.5 px-3.5 bg-white d-flex align-items-center justify-content-between">
    <div class="d-flex align-items-center gap-2" id="mobileNavDrawerLabel">
      <div class="rounded-3 d-flex align-items-center justify-content-center text-white shadow-sm flex-shrink-0" style="width: 34px; height: 34px; background: linear-gradient(135deg, #059669 0%, #047857 100%);">
        <i class="bi bi-heart-pulse-fill fs-6"></i>
      </div>
      <div class="d-flex align-items-center">
        <span class="fs-5 fw-bold text-dark font-heading">Medi<span class="text-emerald">Quick</span></span>
        <span class="navbar-brand-badge ms-1.5">Rx</span>
      </div>
    </div>
    <button type="button" class="btn-close-custom shadow-none flex-shrink-0" data-bs-dismiss="offcanvas" aria-label="Close">
      <i class="bi bi-x-lg"></i>
    </button>
  </div>

  <div class="offcanvas-body p-3 d-flex flex-column justify-content-between">
    <?php if (isset($_SESSION['user_id'])): ?>
      <div class="p-3 mb-3 rounded-4 bg-light border d-flex align-items-center justify-content-between shadow-sm">
        <div class="d-flex align-items-center gap-2.5">
          <div class="rounded-circle text-white d-flex align-items-center justify-content-center fw-bold shadow-sm" style="width: 42px; height: 42px; background: linear-gradient(135deg, #059669 0%, #047857 100%); font-size: 1.1rem;">
            <?php echo strtoupper(substr($_SESSION['user_name'] ?? 'U', 0, 1)); ?>
          </div>
          <div>
            <div class="fw-bold text-dark mb-0 small"><?php echo htmlspecialchars($_SESSION['user_name'] ?? ''); ?></div>
            <span class="badge bg-emerald-subtle text-emerald" style="font-size: 0.72rem;"><?php echo htmlspecialchars(ucfirst($_SESSION['role'] ?? 'Customer')); ?></span>
          </div>
        </div>
        <a href="<?php echo $basePath; ?>logout.php" class="btn btn-sm btn-outline-danger rounded-3 py-1.5 px-2.5" style="font-size: 0.75rem;" title="Sign Out">
          <i class="bi bi-box-arrow-right me-1"></i> Logout
        </a>
      </div>
    <?php endif; ?>

    <ul class="navbar-nav gap-1">
      <li class="nav-item">
        <a class="nav-link d-flex align-items-center gap-2.5 <?php echo ($currentScript === 'index.php') ? 'active' : ''; ?>" href="<?php echo $basePath; ?>index.php">
          <span class="drawer-icon-wrap bg-emerald-subtle text-emerald">
            <i class="bi bi-house-door-fill"></i>
          </span>
          <span>Home</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link d-flex align-items-center gap-2.5 <?php echo ($currentScript === 'shop.php' || $currentScript === 'product-details.php') ? 'active' : ''; ?>" href="<?php echo $basePath; ?>shop.php">
          <span class="drawer-icon-wrap bg-sky-subtle text-sky">
            <i class="bi bi-capsule"></i>
          </span>
          <span>Medicines</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link d-flex align-items-center gap-2.5 <?php echo ($currentScript === 'prescription.php') ? 'active' : ''; ?>" href="<?php echo $basePath; ?>prescription.php">
          <span class="drawer-icon-wrap bg-purple-subtle text-purple">
            <i class="bi bi-cloud-arrow-up-fill"></i>
          </span>
          <span>Upload Rx</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link d-flex align-items-center gap-2.5 <?php echo ($currentScript === 'blogs.php') ? 'active' : ''; ?>" href="<?php echo $basePath; ?>blogs.php">
          <span class="drawer-icon-wrap bg-warning-subtle text-warning">
            <i class="bi bi-journal-richtext"></i>
          </span>
          <span>Health Blogs</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link d-flex align-items-center gap-2.5 <?php echo ($currentScript === 'about.php') ? 'active' : ''; ?>" href="<?php echo $basePath; ?>about.php">
          <span class="drawer-icon-wrap bg-info-subtle text-info">
            <i class="bi bi-info-circle-fill"></i>
          </span>
          <span>About Us</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link d-flex align-items-center gap-2.5 <?php echo ($currentScript === 'contact.php') ? 'active' : ''; ?>" href="<?php echo $basePath; ?>contact.php">
          <span class="drawer-icon-wrap bg-danger-subtle text-danger">
            <i class="bi bi-telephone-fill"></i>
          </span>
          <span>Contact</span>
        </a>
      </li>
    </ul>

    <div class="mt-auto pt-3 border-top d-flex flex-column gap-2">
      <a href="<?php echo $basePath; ?>cart.php" class="btn btn-soft-emerald d-flex align-items-center justify-content-between px-3 py-2 rounded-3">
        <div class="d-flex align-items-center gap-2">
          <i class="bi bi-cart3 text-emerald fs-5"></i>
          <span class="fw-bold">My Shopping Cart</span>
        </div>
        <span class="badge bg-danger rounded-pill px-2.5 py-1">
          <?php echo $cartCount; ?> items
        </span>
      </a>

      <?php if (isset($_SESSION['user_id'])): ?>
        <a href="<?php echo $basePath; ?>my-orders.php" class="btn btn-outline-secondary d-flex align-items-center justify-content-center gap-2 py-2 rounded-3">
          <i class="bi bi-bag-check text-emerald"></i> My Orders & Rx Status
        </a>
        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
          <a href="<?php echo $basePath; ?>admin/index.php" class="btn btn-emerald d-flex align-items-center justify-content-center gap-2 py-2 rounded-3">
            <i class="bi bi-speedometer2"></i> Admin Dashboard
          </a>
        <?php endif; ?>
      <?php else: ?>
        <div class="d-flex gap-2 mt-1">
          <a href="<?php echo $basePath; ?>login.php" class="btn btn-outline-emerald flex-fill text-center py-2">Login</a>
          <a href="<?php echo $basePath; ?>register.php" class="btn btn-emerald flex-fill text-center py-2">Register</a>
        </div>
      <?php endif; ?>

      <div class="drawer-contact-card text-center mt-2">
        <div class="fw-bold text-dark small mb-0.5"><i class="bi bi-telephone-fill text-emerald me-1"></i> Pharmacist Helpline</div>
        <a href="tel:+94372223456" class="text-emerald fw-bold fs-6 text-decoration-none">+94 37 222 3456</a>
      </div>
    </div>
  </div>
</div>
