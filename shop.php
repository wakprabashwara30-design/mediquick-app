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

// Fetch all categories for sidebar filter
$allCategories = mysqli_query($conn, "SELECT * FROM categories ORDER BY name ASC");
?>

<main class="py-4">
  <div class="container">
    
    <!-- Breadcrumb & Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
      <div>
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-1 small">
            <li class="breadcrumb-item"><a href="index.php" class="text-emerald">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Shop</li>
          </ol>
        </nav>
        <h2 class="fw-bold mb-0">Browse Medicines & Healthcare Catalog</h2>
      </div>

      <!-- Quick Search Bar -->
      <form action="shop.php" method="GET" class="d-flex gap-2">
        <?php if ($selectedCategory > 0): ?>
          <input type="hidden" name="category" value="<?php echo $selectedCategory; ?>">
        <?php endif; ?>
        <div class="input-group">
          <input type="text" name="search" class="form-control" placeholder="Search Panadol, Amoxicillin..." value="<?php echo htmlspecialchars($search); ?>">
          <button class="btn btn-emerald" type="submit"><i class="bi bi-search"></i></button>
        </div>
      </form>
    </div>

    <div class="row g-4">
      
      <!-- 1. LEFT SIDEBAR: CATEGORY FILTERS -->
      <div class="col-lg-3 col-md-4">
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

            <?php if ($allCategories && mysqli_num_rows($allCategories) > 0): ?>
              <?php while ($cat = mysqli_fetch_assoc($allCategories)): ?>
                <a href="shop.php?category=<?php echo $cat['id']; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" 
                   class="list-group-item list-group-item-action d-flex justify-content-between align-items-center py-2 px-1 border-0 rounded-2 <?php echo ($selectedCategory == $cat['id']) ? 'fw-bold text-emerald bg-emerald-subtle' : 'text-secondary'; ?>">
                  <span><?php echo htmlspecialchars($cat['name']); ?></span>
                  <i class="bi bi-chevron-right" style="font-size: 0.75rem;"></i>
                </a>
              <?php endwhile; ?>
            <?php endif; ?>
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
              You can directly upload a photo of your doctor's prescription without manually searching for each item.
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
          </div>

          <div class="row g-3">
            <?php while ($product = mysqli_fetch_assoc($result)): ?>
              <div class="col-sm-6 col-lg-4">
                <div class="card card-custom h-100 d-flex flex-column">
                  
                  <!-- Product Image -->
                  <div class="product-img-container position-relative">
                    <img src="<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                    
                    <!-- Rx Badge -->
                    <div class="position-absolute top-0 start-0 m-2">
                      <?php if ($product['requires_prescription']): ?>
                        <span class="badge-rx"><i class="bi bi-file-earmark-medical me-1"></i> Rx Required</span>
                      <?php else: ?>
                        <span class="badge-otc"><i class="bi bi-check-circle me-1"></i> OTC</span>
                      <?php endif; ?>
                    </div>
                  </div>

                  <!-- Details -->
                  <div class="card-body p-3 d-flex flex-column">
                    <span class="text-muted small mb-1" style="font-size: 0.75rem;"><?php echo htmlspecialchars($product['category_name']); ?></span>
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

<?php include_once __DIR__ . '/includes/footer.php'; ?>
