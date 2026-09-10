<?php
/**
 * MediQuick Pharmacy - Home Page
 * University 1st-Year Web Application (PHP & MySQL)
 */
require_once __DIR__ . '/config/db.php';

$pageTitle = 'MediQuick Pharmacy | Online Healthcare & Medicine Delivery in Sri Lanka';
include_once __DIR__ . '/includes/header.php';
include_once __DIR__ . '/includes/navbar.php';

// Fetch 6 featured products directly from MySQL database
$featuredQuery = "SELECT p.*, c.name AS category_name 
                  FROM products p 
                  JOIN categories c ON p.category_id = c.id 
                  ORDER BY p.id ASC LIMIT 6";
$featuredResult = mysqli_query($conn, $featuredQuery);

// Fetch categories for category cards
$catQuery = "SELECT * FROM categories ORDER BY id ASC LIMIT 5";
$catResult = mysqli_query($conn, $catQuery);
?>

<main>
  <!-- 1. HERO SECTION -->
  <section class="hero-section">
    <div class="container">
      <div class="row align-items-center g-4">
        
        <!-- Left Hero Content -->
        <div class="col-lg-7">
          <div class="hero-pill mb-3">
            <i class="bi bi-patch-check-fill text-success"></i>
            <span>Licensed Online Healthcare in Sri Lanka</span>
          </div>

          <h1 class="display-5 fw-bold mb-3">
            Fast, Safe & Reliable <br>
            <span class="text-emerald">Medicine Delivery</span> to Your Doorstep
          </h1>

          <p class="lead text-secondary mb-4" style="font-size: 1.1rem;">
            Order genuine OTC medicines, wellness supplements, or simply upload your doctor's prescription for quick pharmacist review and express delivery across Kurunegala and suburbs.
          </p>

          <div class="d-flex flex-wrap gap-3">
            <a href="shop.php" class="btn btn-emerald btn-lg px-4 py-2.5">
              <i class="bi bi-capsule me-2"></i> Browse Medicines
            </a>
            <a href="prescription.php" class="btn btn-soft-emerald btn-lg px-4 py-2.5">
              <i class="bi bi-cloud-arrow-up-fill me-2"></i> Upload Prescription
            </a>
          </div>

          <!-- Trust Badges -->
          <div class="row g-3 mt-4 pt-2 border-top border-secondary border-opacity-10">
            <div class="col-4">
              <div class="fw-bold fs-4 text-dark">100%</div>
              <small class="text-muted">NMRA Approved</small>
            </div>
            <div class="col-4">
              <div class="fw-bold fs-4 text-dark">Fast</div>
              <small class="text-muted">Express Delivery</small>
            </div>
            <div class="col-4">
              <div class="fw-bold fs-4 text-dark">24/7</div>
              <small class="text-muted">Pharmacist Support</small>
            </div>
          </div>
        </div>

        <!-- Right Hero Promo Card -->
        <div class="col-lg-5">
          <div class="card card-custom p-4 border-0 shadow-sm" style="background-color: #ffffff;">
            <div class="d-flex align-items-center gap-3 mb-3">
              <div class="rounded-3 d-flex align-items-center justify-content-center text-white p-3" style="background-color: #059669;">
                <i class="bi bi-file-earmark-medical-fill fs-2"></i>
              </div>
              <div>
                <h5 class="fw-bold mb-0">Have a Doctor's Prescription?</h5>
                <small class="text-muted">Fast-Track Pharmacist Review</small>
              </div>
            </div>
            <p class="small text-secondary mb-3">
              Upload a clear photo of your doctor's slip. Our registered SLMC pharmacists will review it and dispense your medicines safely.
            </p>
            <ul class="list-unstyled small text-secondary d-flex flex-column gap-1 mb-4">
              <li><i class="bi bi-check-circle-fill text-success me-2"></i> 100% Confidential & Secure</li>
              <li><i class="bi bi-check-circle-fill text-success me-2"></i> Free dosage guidance by pharmacists</li>
              <li><i class="bi bi-check-circle-fill text-success me-2"></i> Cash on Delivery available</li>
            </ul>
            <a href="prescription.php" class="btn btn-emerald w-100 py-2.5">
              <i class="bi bi-upload me-2"></i> Upload Prescription Slip Now
            </a>
          </div>
        </div>

      </div>
    </div>
  </section>

  <!-- 2. HEALTHCARE CATEGORIES -->
  <section class="py-5 bg-white border-bottom">
    <div class="container">
      <div class="text-center mb-4">
        <h2 class="fw-bold">Explore by Category</h2>
        <p class="text-muted">Find exactly what you need from our comprehensive departments</p>
      </div>

      <div class="row g-3 justify-content-center">
        <?php if ($catResult && mysqli_num_rows($catResult) > 0): ?>
          <?php while ($cat = mysqli_fetch_assoc($catResult)): ?>
            <div class="col-6 col-md-4 col-lg">
              <a href="shop.php?category=<?php echo $cat['id']; ?>" class="card card-custom text-center p-3 h-100 text-decoration-none">
                <div class="rounded-circle mx-auto d-flex align-items-center justify-content-center mb-2" style="width: 50px; height: 50px; background-color: #ecfdf5; color: #059669;">
                  <i class="bi <?php echo htmlspecialchars($cat['icon'] ?? 'bi-capsule'); ?> fs-4"></i>
                </div>
                <h6 class="fw-bold text-dark mb-1" style="font-size: 0.9rem;"><?php echo htmlspecialchars($cat['name']); ?></h6>
                <small class="text-muted" style="font-size: 0.75rem;">Browse items &rarr;</small>
              </a>
            </div>
          <?php endwhile; ?>
        <?php endif; ?>
      </div>
    </div>
  </section>

  <!-- 3. FEATURED MEDICINES & PRODUCTS -->
  <section class="py-5">
    <div class="container">
      <div class="d-flex justify-content-between align-items-end mb-4">
        <div>
          <h2 class="fw-bold mb-1">Featured Healthcare Products</h2>
          <p class="text-muted mb-0">Genuine, temperature-regulated medicines ready for dispatch</p>
        </div>
        <a href="shop.php" class="btn btn-outline-emerald btn-sm px-3">
          View All Products <i class="bi bi-arrow-right ms-1"></i>
        </a>
      </div>

      <div class="row g-4">
        <?php if ($featuredResult && mysqli_num_rows($featuredResult) > 0): ?>
          <?php while ($product = mysqli_fetch_assoc($featuredResult)): ?>
            <div class="col-sm-6 col-md-4 col-lg-4">
              <div class="card card-custom h-100 d-flex flex-column">
                
                <!-- Product Image -->
                <div class="product-img-container position-relative">
                  <img src="<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                  
                  <!-- Prescription Requirement Badge -->
                  <div class="position-absolute top-0 start-0 m-2">
                    <?php if ($product['requires_prescription']): ?>
                      <span class="badge-rx"><i class="bi bi-file-earmark-medical me-1"></i> Rx Required</span>
                    <?php else: ?>
                      <span class="badge-otc"><i class="bi bi-check-circle me-1"></i> OTC Medicine</span>
                    <?php endif; ?>
                  </div>
                </div>

                <!-- Product Details -->
                <div class="card-body p-3 d-flex flex-column">
                  <span class="text-muted small mb-1"><?php echo htmlspecialchars($product['category_name']); ?></span>
                  <h6 class="fw-bold text-dark mb-1">
                    <a href="product-details.php?id=<?php echo $product['id']; ?>" class="text-dark">
                      <?php echo htmlspecialchars($product['name']); ?>
                    </a>
                  </h6>
                  <small class="text-secondary mb-2"><?php echo htmlspecialchars($product['dosage'] ?? ''); ?></small>
                  
                  <div class="mt-auto pt-3 border-top d-flex align-items-center justify-content-between">
                    <div class="price-tag">
                      <?php echo formatLKR($product['price']); ?>
                    </div>
                    <div class="d-flex gap-1">
                      <a href="product-details.php?id=<?php echo $product['id']; ?>" class="btn btn-sm btn-outline-secondary" title="View Details">
                        <i class="bi bi-eye"></i>
                      </a>
                      <a href="cart.php?action=add&id=<?php echo $product['id']; ?>" class="btn btn-sm btn-emerald" title="Add to Cart">
                        <i class="bi bi-cart-plus me-1"></i> Add
                      </a>
                    </div>
                  </div>
                </div>

              </div>
            </div>
          <?php endwhile; ?>
        <?php else: ?>
          <div class="col-12 text-center py-5">
            <p class="text-muted">No products found in the database. Please import the database script in phpMyAdmin.</p>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </section>

  <!-- 4. HOW IT WORKS SECTION -->
  <section class="py-5 bg-white border-top border-bottom">
    <div class="container">
      <div class="text-center mb-5">
        <h2 class="fw-bold">How MediQuick Works</h2>
        <p class="text-muted">Order your medications in 3 simple steps</p>
      </div>

      <div class="row g-4 text-center">
        <div class="col-md-4">
          <div class="p-4 rounded-4 bg-light h-100">
            <div class="rounded-circle bg-emerald-subtle text-emerald mx-auto d-flex align-items-center justify-content-center mb-3" style="width: 64px; height: 64px;">
              <i class="bi bi-search fs-3"></i>
            </div>
            <h5 class="fw-bold mb-2">1. Choose Medicines or Upload Rx</h5>
            <p class="text-secondary small mb-0">
              Search our catalog of genuine healthcare products or upload a clear photo of your doctor's prescription.
            </p>
          </div>
        </div>

        <div class="col-md-4">
          <div class="p-4 rounded-4 bg-light h-100">
            <div class="rounded-circle bg-emerald-subtle text-emerald mx-auto d-flex align-items-center justify-content-center mb-3" style="width: 64px; height: 64px;">
              <i class="bi bi-shield-check fs-3"></i>
            </div>
            <h5 class="fw-bold mb-2">2. Pharmacist Verification</h5>
            <p class="text-secondary small mb-0">
              Our registered Sri Lanka Medical Council (SLMC) pharmacists verify dosage and prepare your order safely.
            </p>
          </div>
        </div>

        <div class="col-md-4">
          <div class="p-4 rounded-4 bg-light h-100">
            <div class="rounded-circle bg-emerald-subtle text-emerald mx-auto d-flex align-items-center justify-content-center mb-3" style="width: 64px; height: 64px;">
              <i class="bi bi-truck fs-3"></i>
            </div>
            <h5 class="fw-bold mb-2">3. Express Doorstep Delivery</h5>
            <p class="text-secondary small mb-0">
              Your sealed package is delivered quickly to your doorstep with Cash on Delivery or Card payment options.
            </p>
          </div>
        </div>
      </div>
    </div>
  </section>
</main>

<?php include_once __DIR__ . '/includes/footer.php'; ?>
