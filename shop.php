<?php
/**
 * MediQuick Pharmacy - Shop / Medicines Catalog Page
 * University 1st-Year Web Application (PHP & MySQL)
 */
require_once __DIR__ . '/config/db.php';

$pageTitle = 'Browse Medicines & Healthcare Products | MediQuick';
include_once __DIR__ . '/includes/header.php';
include_once __DIR__ . '/includes/navbar.php';

// Capture Search & Filter parameters
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$selectedCategory = isset($_GET['category']) ? (int)$_GET['category'] : 0;

// Build SQL Query dynamically
$whereClauses = [];
if (!empty($search)) {
    $safeSearch = mysqli_real_escape_string($conn, $search);
    $whereClauses[] = "(p.name LIKE '%$safeSearch%' OR p.generic_name LIKE '%$safeSearch%' OR p.description LIKE '%$safeSearch%')";
}
if ($selectedCategory > 0) {
    $whereClauses[] = "p.category_id = $selectedCategory";
}

$sql = "SELECT p.*, c.name AS category_name 
        FROM products p 
        JOIN categories c ON p.category_id = c.id";

if (count($whereClauses) > 0) {
    $sql .= " WHERE " . implode(" AND ", $whereClauses);
}
$sql .= " ORDER BY p.id ASC";

$result = mysqli_query($conn, $sql);

// Fetch all categories for sidebar and mobile pills
$allCategoriesQuery = mysqli_query($conn, "SELECT * FROM categories ORDER BY name ASC");
$categoriesList = [];
if ($allCategoriesQuery) {
    while ($catRow = mysqli_fetch_assoc($allCategoriesQuery)) {
        $categoriesList[] = $catRow;
    }
}
?>

<main class="py-4">
  <div class="container">
    
    <!-- Breadcrumb & Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
      <div>
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-1 small">
            <li class="breadcrumb-item"><a href="index.php" class="text-emerald">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Shop</li>
          </ol>
        </nav>
        <h2 class="fw-bold mb-0 fs-3">Medicines & Healthcare Catalog</h2>
      </div>

      <!-- Quick Search Bar -->
      <form action="shop.php" method="GET" class="d-flex gap-2 w-100 w-md-auto mt-2 mt-md-0" style="max-width: 380px;">
        <?php if ($selectedCategory > 0): ?>
          <input type="hidden" name="category" value="<?php echo $selectedCategory; ?>">
        <?php endif; ?>
        <div class="input-group">
          <input type="text" name="search" class="form-control" placeholder="Search Panadol, Amoxil..." value="<?php echo htmlspecialchars($search); ?>">
          <button class="btn btn-emerald" type="submit"><i class="bi bi-search"></i></button>
        </div>
      </form>
    </div>

    <!-- Mobile Horizontal Category Scroll Pills & All Categories Drawer Trigger -->
    <div class="d-md-none mb-3">
      <div class="d-flex align-items-center justify-content-between mb-2">
        <span class="small fw-semibold text-secondary">
          <i class="bi bi-tag-fill text-emerald me-1"></i>
          <?php 
            if ($selectedCategory > 0) {
              $currentCatName = 'Category';
              foreach ($categoriesList as $c) {
                if ($c['id'] == $selectedCategory) { $currentCatName = $c['name']; break; }
              }
              echo 'Filter: <strong class="text-emerald">' . htmlspecialchars($currentCatName) . '</strong>';
            } else {
              echo 'Filter: <strong class="text-emerald">All Categories</strong>';
            }
          ?>
        </span>
        <button class="btn btn-sm btn-outline-emerald py-1 px-2.5 rounded-pill d-inline-flex align-items-center gap-1 shadow-2xs" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileCategoryDrawer" aria-controls="mobileCategoryDrawer" style="font-size: 0.74rem; font-weight: 600;">
          <i class="bi bi-funnel"></i> All Categories (<?php echo count($categoriesList); ?>)
        </button>
      </div>

      <div class="category-scroll-container position-relative">
        <div class="category-scroll-pills" id="categoryScrollPills">
          <a href="shop.php<?php echo !empty($search) ? '?search=' . urlencode($search) : ''; ?>" 
             class="category-pill-item <?php echo ($selectedCategory === 0) ? 'active' : ''; ?>">
            <i class="bi bi-grid-fill"></i> All Items
          </a>
          <?php foreach ($categoriesList as $cat): ?>
            <a href="shop.php?category=<?php echo $cat['id']; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" 
               class="category-pill-item <?php echo ($selectedCategory == $cat['id']) ? 'active' : ''; ?>">
              <i class="bi <?php echo htmlspecialchars($cat['icon'] ?? 'bi-capsule'); ?>"></i>
              <?php echo htmlspecialchars($cat['name']); ?>
            </a>
          <?php endforeach; ?>
        </div>
      </div>
    </div>

    <div class="row g-4">
      
      <!-- 1. LEFT SIDEBAR: CATEGORY FILTERS (Visible on Desktop / Tablet) -->
      <div class="col-lg-3 col-md-4 d-none d-md-block">
        <div class="card card-custom p-3 bg-white">
          <h6 class="fw-bold text-dark mb-3 border-bottom pb-2">
            <i class="bi bi-funnel me-1 text-emerald"></i> Filter by Category
          </h6>
          
          <div class="list-group list-group-flush small">
            <a href="shop.php<?php echo !empty($search) ? '?search=' . urlencode($search) : ''; ?>" 
               class="list-group-item list-group-item-action d-flex justify-content-between align-items-center py-2 px-1 border-0 rounded-2 <?php echo ($selectedCategory === 0) ? 'fw-bold text-emerald bg-emerald-subtle' : 'text-secondary'; ?>">
              <span>All Categories</span>
              <i class="bi bi-chevron-right" style="font-size: 0.75rem;"></i>
            </a>

            <?php foreach ($categoriesList as $cat): ?>
              <a href="shop.php?category=<?php echo $cat['id']; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" 
                 class="list-group-item list-group-item-action d-flex justify-content-between align-items-center py-2 px-1 border-0 rounded-2 <?php echo ($selectedCategory == $cat['id']) ? 'fw-bold text-emerald bg-emerald-subtle' : 'text-secondary'; ?>">
                <span><?php echo htmlspecialchars($cat['name']); ?></span>
                <i class="bi bi-chevron-right" style="font-size: 0.75rem;"></i>
              </a>
            <?php endforeach; ?>
          </div>

          <!-- Active Filter Clear Button -->
          <?php if ($selectedCategory > 0 || !empty($search)): ?>
            <div class="mt-3 pt-3 border-top">
              <a href="shop.php" class="btn btn-sm btn-outline-secondary w-100">
                <i class="bi bi-x-circle me-1"></i> Clear Filters
              </a>
            </div>
          <?php endif; ?>

          <!-- Prescription Notice Box -->
          <div class="p-3 mt-4 rounded-3 bg-emerald-subtle border border-emerald">
            <h6 class="fw-bold text-emerald small mb-1"><i class="bi bi-info-circle-fill me-1"></i> Have an Rx Slip?</h6>
            <p class="small text-secondary mb-2" style="font-size: 0.8rem;">
              Directly upload a photo of your doctor's prescription for express review.
            </p>
            <a href="prescription.php" class="btn btn-emerald btn-sm w-100 py-1.5" style="font-size: 0.8rem;">
              Upload Prescription
            </a>
          </div>
        </div>
      </div>

      <!-- 2. RIGHT CONTENT: PRODUCTS GRID -->
      <div class="col-lg-9 col-md-8">
        
        <?php if ($result && mysqli_num_rows($result) > 0): ?>
          <div class="d-flex justify-content-between align-items-center mb-3">
            <small class="text-muted">Showing <strong><?php echo mysqli_num_rows($result); ?></strong> product(s)</small>
            <?php if ($selectedCategory > 0 || !empty($search)): ?>
              <a href="shop.php" class="btn btn-sm btn-link text-emerald p-0 d-md-none text-decoration-none">
                <i class="bi bi-arrow-counterclockwise"></i> Reset
              </a>
            <?php endif; ?>
          </div>

          <div class="row g-2 g-md-3 g-lg-4">
            <?php while ($product = mysqli_fetch_assoc($result)): ?>
              <div class="col-6 col-md-6 col-lg-4">
                <div class="card card-custom h-100 d-flex flex-column border shadow-sm">
                  
                  <!-- Product Image -->
                  <div class="product-img-container position-relative">
                    <img src="<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" loading="lazy">
                    
                    <!-- Prescription Requirement Badge -->
                    <div class="position-absolute top-0 start-0 m-1.5 m-md-2">
                      <?php if ($product['requires_prescription']): ?>
                        <span class="badge-rx"><i class="bi bi-file-earmark-medical me-1"></i><span class="d-none d-sm-inline">Rx </span>Required</span>
                      <?php else: ?>
                        <span class="badge-otc"><i class="bi bi-check-circle me-1"></i>OTC<span class="d-none d-sm-inline"> Medicine</span></span>
                      <?php endif; ?>
                    </div>

                    <!-- Quick View Floating Button -->
                    <a href="product-details.php?id=<?php echo $product['id']; ?>" class="product-quick-view-btn position-absolute top-0 end-0 m-1.5 m-md-2" title="Quick View">
                      <i class="bi bi-eye"></i>
                    </a>
                  </div>

                  <!-- Product Details -->
                  <div class="card-body p-2.5 p-md-3 d-flex flex-column">
                    <span class="text-muted small mb-1" style="font-size: 0.72rem;"><?php echo htmlspecialchars($product['category_name']); ?></span>
                    <h6 class="fw-bold mb-1" style="font-size: 0.88rem; min-height: 2.3rem; line-height: 1.3;">
                      <a href="product-details.php?id=<?php echo $product['id']; ?>" class="text-dark text-decoration-none">
                        <?php echo htmlspecialchars($product['name']); ?>
                      </a>
                    </h6>
                    <small class="text-secondary mb-2 mb-md-3 text-truncate" style="font-size: 0.75rem;"><?php echo htmlspecialchars($product['dosage'] ?? ''); ?></small>
                    
                    <!-- Price & Full-Width Add Button -->
                    <div class="mt-auto pt-2 border-top">
                      <div class="d-flex justify-content-between align-items-center mb-1.5 mb-md-2">
                        <div class="price-tag fw-bold" style="font-size: 0.95rem;">
                          <?php echo formatLKR($product['price']); ?>
                        </div>
                        <?php if (!empty($product['stock']) && $product['stock'] > 0): ?>
                          <span class="badge bg-success-subtle text-success border border-success-subtle d-none d-sm-inline-block" style="font-size: 0.65rem;">In Stock</span>
                        <?php endif; ?>
                      </div>

                      <a href="cart.php?action=add&id=<?php echo $product['id']; ?>" class="btn btn-emerald btn-sm w-100 py-1.5 py-md-2 d-flex align-items-center justify-content-center gap-1 fw-semibold shadow-sm" style="font-size: 0.8rem;" title="Add to Cart">
                        <i class="bi bi-cart-plus"></i>
                        <span>Add to Cart</span>
                      </a>
                    </div>
                  </div>

                </div>
              </div>
            <?php endwhile; ?>
          </div>

        <?php else: ?>
          <!-- Empty State -->
          <div class="card card-custom p-5 text-center bg-white">
            <div class="text-muted mb-3">
              <i class="bi bi-search display-4 text-secondary opacity-50"></i>
            </div>
            <h5 class="fw-bold">No Products Found</h5>
            <p class="text-secondary small mb-3">
              We could not find any medicines matching your search criteria. Try a different keyword or category.
            </p>
            <div>
              <a href="shop.php" class="btn btn-emerald btn-sm px-3">
                View All Products
              </a>
            </div>
          </div>
        <?php endif; ?>

      </div>

    </div>
  </div>
</main>

<!-- Mobile Category Filter Offcanvas Drawer (Bottom Sheet) -->
<div class="offcanvas offcanvas-bottom d-md-none rounded-top-4" tabindex="-1" id="mobileCategoryDrawer" aria-labelledby="mobileCategoryDrawerLabel" style="height: auto; max-height: 75vh;">
  <div class="offcanvas-header border-bottom py-3 px-4 bg-white d-flex align-items-center justify-content-between">
    <div class="d-flex align-items-center gap-2">
      <div class="bg-emerald-subtle text-emerald rounded-circle p-1.5 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
        <i class="bi bi-funnel-fill fs-6"></i>
      </div>
      <h6 class="offcanvas-title fw-bold text-dark mb-0" id="mobileCategoryDrawerLabel">Filter Medicines by Category</h6>
    </div>
    <button type="button" class="btn-close shadow-none" data-bs-dismiss="offcanvas" aria-label="Close"></button>
  </div>
  <div class="offcanvas-body p-3 bg-light overflow-y-auto">
    <div class="list-group list-group-flush rounded-3 shadow-2xs bg-white overflow-hidden mb-3">
      <a href="shop.php<?php echo !empty($search) ? '?search=' . urlencode($search) : ''; ?>" 
         class="list-group-item list-group-item-action d-flex justify-content-between align-items-center py-2.5 px-3 border-bottom <?php echo ($selectedCategory === 0) ? 'fw-bold text-emerald bg-emerald-subtle' : 'text-dark'; ?>">
        <div class="d-flex align-items-center gap-2.5">
          <i class="bi bi-grid-fill <?php echo ($selectedCategory === 0) ? 'text-emerald' : 'text-secondary'; ?>"></i>
          <span>All Categories</span>
        </div>
        <?php if ($selectedCategory === 0): ?>
          <i class="bi bi-check-circle-fill text-emerald"></i>
        <?php else: ?>
          <i class="bi bi-chevron-right text-muted small"></i>
        <?php endif; ?>
      </a>

      <?php foreach ($categoriesList as $cat): ?>
        <a href="shop.php?category=<?php echo $cat['id']; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" 
           class="list-group-item list-group-item-action d-flex justify-content-between align-items-center py-2.5 px-3 border-bottom <?php echo ($selectedCategory == $cat['id']) ? 'fw-bold text-emerald bg-emerald-subtle' : 'text-dark'; ?>">
          <div class="d-flex align-items-center gap-2.5">
            <i class="bi <?php echo htmlspecialchars($cat['icon'] ?? 'bi-capsule'); ?> <?php echo ($selectedCategory == $cat['id']) ? 'text-emerald' : 'text-secondary'; ?>"></i>
            <span><?php echo htmlspecialchars($cat['name']); ?></span>
          </div>
          <?php if ($selectedCategory == $cat['id']): ?>
            <i class="bi bi-check-circle-fill text-emerald"></i>
          <?php else: ?>
            <i class="bi bi-chevron-right text-muted small"></i>
          <?php endif; ?>
        </a>
      <?php endforeach; ?>
    </div>

    <?php if ($selectedCategory > 0 || !empty($search)): ?>
      <div>
        <a href="shop.php" class="btn btn-outline-secondary w-100 py-2 btn-sm fw-semibold">
          <i class="bi bi-x-circle me-1"></i> Clear Active Filters
        </a>
      </div>
    <?php endif; ?>
  </div>
</div>

<script>
// Auto-scroll active category pill into view on page load
document.addEventListener('DOMContentLoaded', () => {
  const activePill = document.querySelector('.category-pill-item.active');
  const container = document.getElementById('categoryScrollPills');
  if (activePill && container) {
    const pillLeft = activePill.offsetLeft;
    container.scrollTo({ left: pillLeft - 20, behavior: 'smooth' });
  }
});
</script>

<?php include_once __DIR__ . '/includes/footer.php'; ?>
