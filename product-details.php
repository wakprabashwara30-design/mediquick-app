<?php
/**
 * MediQuick Pharmacy - Single Product Details Page
 * University 1st-Year Web Application (PHP & MySQL)
 */
require_once __DIR__ . '/config/db.php';

$productId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($productId <= 0) {
    header("Location: shop.php");
    exit();
}

// Fetch single product from database
$stmt = mysqli_prepare($conn, "SELECT p.*, c.name AS category_name FROM products p JOIN categories c ON p.category_id = c.id WHERE p.id = ?");
mysqli_stmt_bind_param($stmt, "i", $productId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (!$result || mysqli_num_rows($result) === 0) {
    header("Location: shop.php");
    exit();
}

$product = mysqli_fetch_assoc($result);

// Fetch Related Products (up to 4 products)
$relStmt = mysqli_prepare($conn, "SELECT p.*, c.name AS category_name FROM products p JOIN categories c ON p.category_id = c.id WHERE p.id != ? ORDER BY (p.category_id = ?) DESC, p.id ASC LIMIT 4");
$catId = (int)$product['category_id'];
mysqli_stmt_bind_param($relStmt, "ii", $productId, $catId);
mysqli_stmt_execute($relStmt);
$relResult = mysqli_stmt_get_result($relStmt);
$relatedProducts = [];
if ($relResult) {
    while ($r = mysqli_fetch_assoc($relResult)) {
        $relatedProducts[] = $r;
    }
}

$pageTitle = htmlspecialchars($product['name']) . ' | MediQuick Pharmacy';
include_once __DIR__ . '/includes/header.php';
include_once __DIR__ . '/includes/navbar.php';
?>

<main class="py-4">
  <div class="container">
    
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
      <ol class="breadcrumb small">
        <li class="breadcrumb-item"><a href="index.php" class="text-emerald">Home</a></li>
        <li class="breadcrumb-item"><a href="shop.php" class="text-emerald">Shop</a></li>
        <li class="breadcrumb-item"><a href="shop.php?category=<?php echo $product['category_id']; ?>" class="text-emerald"><?php echo htmlspecialchars($product['category_name']); ?></a></li>
        <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($product['name']); ?></li>
      </ol>
    </nav>

    <!-- Product Details Card -->
    <div class="card card-custom p-4 bg-white mb-5">
      <div class="row g-4 align-items-center">
        
        <!-- Left: Product Image -->
        <div class="col-md-5 text-center">
          <div class="p-3 bg-light rounded-4 border">
            <img src="<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="img-fluid rounded-3" style="max-height: 320px; object-fit: contain;">
          </div>
        </div>

        <!-- Right: Product Info & Buy Form -->
        <div class="col-md-7">
          
          <div class="d-flex items-center gap-2 mb-2">
            <span class="badge bg-secondary-subtle text-secondary small"><?php echo htmlspecialchars($product['category_name']); ?></span>
            <?php if ($product['requires_prescription']): ?>
              <span class="badge-rx"><i class="bi bi-file-earmark-medical me-1"></i> Doctor's Prescription (Rx) Required</span>
            <?php else: ?>
              <span class="badge-otc"><i class="bi bi-check-circle me-1"></i> Over The Counter (OTC)</span>
            <?php endif; ?>
          </div>

          <h2 class="fw-bold text-dark mb-1"><?php echo htmlspecialchars($product['name']); ?></h2>
          
          <?php if (!empty($product['generic_name'])): ?>
            <p class="text-muted small mb-3">
              <strong>Generic:</strong> <?php echo htmlspecialchars($product['generic_name']); ?>
            </p>
          <?php endif; ?>

          <div class="price-tag fs-2 mb-3">
            <?php echo formatLKR($product['price']); ?>
          </div>

          <!-- Key Details Grid -->
          <div class="row g-2 mb-4 small text-secondary">
            <div class="col-sm-6">
              <strong>Dosage / Pack:</strong> <?php echo htmlspecialchars($product['dosage'] ?? 'Standard Unit'); ?>
            </div>
            <div class="col-sm-6">
              <strong>Manufacturer:</strong> <?php echo htmlspecialchars($product['manufacturer'] ?? 'State Pharmaceuticals'); ?>
            </div>
            <div class="col-sm-6">
              <strong>Availability:</strong> 
              <?php if ($product['stock'] > 0): ?>
                <span class="text-success fw-bold"><i class="bi bi-check-circle-fill"></i> In Stock (<?php echo $product['stock']; ?> available)</span>
              <?php else: ?>
                <span class="text-danger fw-bold"><i class="bi bi-x-circle-fill"></i> Out of Stock</span>
              <?php endif; ?>
            </div>
          </div>

          <!-- Add to Cart Form -->
          <form action="cart.php" method="GET" class="d-flex flex-wrap align-items-end gap-2 mb-4">
            <input type="hidden" name="action" value="add">
            <input type="hidden" name="id" value="<?php echo $product['id']; ?>">
            
            <div style="width: 95px;">
              <label for="qtyInput" class="form-label small fw-bold mb-1">Quantity:</label>
              <input type="number" id="qtyInput" name="qty" class="form-control text-center" value="1" min="1" max="<?php echo max(1, $product['stock']); ?>">
            </div>

            <div class="flex-grow-1">
              <button type="submit" class="btn btn-emerald w-100" <?php echo ($product['stock'] <= 0) ? 'disabled' : ''; ?>>
                <i class="bi bi-cart-plus-fill me-1"></i> Add to Cart
              </button>
            </div>
          </form>

          <!-- Prescription Alert Box if Required -->
          <?php if ($product['requires_prescription']): ?>
            <div class="alert alert-warning d-flex align-items-center gap-2 small p-2.5 mb-0" role="alert">
              <i class="bi bi-exclamation-triangle-fill fs-5"></i>
              <div>
                This medicine is classified under Schedule Drugs. A valid doctor's prescription will be verified upon delivery.
              </div>
            </div>
          <?php endif; ?>

        </div>

      </div>

      <!-- Description Tab / Section -->
      <div class="mt-4 pt-4 border-top">
        <h5 class="fw-bold mb-2">Product Description & Instructions</h5>
        <p class="text-secondary small leading-relaxed">
          <?php echo nl2br(htmlspecialchars($product['description'] ?? 'No additional description provided. Please use as directed by your physician or pharmacist.')); ?>
        </p>
      </div>

    </div>

    <!-- Related Products Section (4 Columns) -->
    <?php if (count($relatedProducts) > 0): ?>
      <div class="mt-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
          <div>
            <h4 class="fw-bold mb-1">Related Healthcare Products</h4>
            <p class="text-muted small mb-0">Recommended medications & wellness supplies</p>
          </div>
          <a href="shop.php" class="btn btn-outline-emerald btn-sm px-3">
            View All <i class="bi bi-arrow-right ms-1"></i>
          </a>
        </div>

        <div class="row g-4">
          <?php foreach ($relatedProducts as $rel): ?>
            <div class="col-12 col-sm-6 col-md-6 col-lg-3">
              <div class="card card-custom h-100 d-flex flex-column border shadow-sm">
                
                <div class="product-img-container position-relative">
                  <img src="<?php echo htmlspecialchars($rel['image']); ?>" alt="<?php echo htmlspecialchars($rel['name']); ?>" loading="lazy">
                  
                  <div class="position-absolute top-0 start-0 m-2">
                    <?php if ($rel['requires_prescription']): ?>
                      <span class="badge-rx"><i class="bi bi-file-earmark-medical me-1"></i> Rx Required</span>
                    <?php else: ?>
                      <span class="badge-otc"><i class="bi bi-check-circle me-1"></i> OTC Medicine</span>
                    <?php endif; ?>
                  </div>

                  <a href="product-details.php?id=<?php echo $rel['id']; ?>" class="product-quick-view-btn position-absolute top-0 end-0 m-2" title="Quick View">
                    <i class="bi bi-eye"></i>
                  </a>
                </div>

                <div class="card-body p-3 d-flex flex-column">
                  <span class="text-muted small mb-1" style="font-size: 0.78rem;"><?php echo htmlspecialchars($rel['category_name']); ?></span>
                  <h6 class="fw-bold mb-1" style="font-size: 0.95rem; min-height: 2.5rem; line-height: 1.3;">
                    <a href="product-details.php?id=<?php echo $rel['id']; ?>" class="text-dark text-decoration-none">
                      <?php echo htmlspecialchars($rel['name']); ?>
                    </a>
                  </h6>
                  <small class="text-secondary mb-3" style="font-size: 0.78rem;"><?php echo htmlspecialchars($rel['dosage'] ?? ''); ?></small>
                  
                  <div class="mt-auto pt-2 border-top">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                      <div class="price-tag">
                        <?php echo formatLKR($rel['price']); ?>
                      </div>
                      <?php if (!empty($rel['stock']) && $rel['stock'] > 0): ?>
                        <span class="badge bg-success-subtle text-success border border-success-subtle" style="font-size: 0.68rem;">In Stock</span>
                      <?php endif; ?>
                    </div>

                    <a href="cart.php?action=add&id=<?php echo $rel['id']; ?>" class="btn btn-emerald btn-sm w-100 py-2 d-flex align-items-center justify-content-center gap-1.5 fw-semibold shadow-sm" title="Add to Cart">
                      <i class="bi bi-cart-plus"></i>
                      <span>Add to Cart</span>
                    </a>
                  </div>
                </div>

              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endif; ?>

  </div>
</main>

<?php include_once __DIR__ . '/includes/footer.php'; ?>
