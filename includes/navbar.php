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

    <!-- Mobile Toggler -->
    <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar" aria-controls="mainNavbar" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <!-- Navbar Links & Actions -->
    <div class="collapse navbar-collapse" id="mainNavbar">
      
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
        
        <!-- Cart Button -->
        <a href="<?php echo $basePath; ?>cart.php" class="btn btn-soft-emerald position-relative d-flex align-items-center gap-1.5 px-3 py-2">
          <i class="bi bi-cart3 fs-6"></i>
          <span class="fw-semibold d-none d-sm-inline">Cart</span>
          <?php if ($cartCount > 0): ?>
            <span class="badge bg-danger rounded-pill ms-1" style="font-size: 0.75rem;">
              <?php echo $cartCount; ?>
            </span>
          <?php endif; ?>
        </a>

        <!-- User Authentication State -->
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
                  <i class="bi bi-bag-check text-emerald"></i> My Orders & Rx
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
