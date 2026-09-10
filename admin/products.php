<?php
/**
 * MediQuick Pharmacy - Product Inventory Management (CRUD)
 * University 1st-Year Web Application (PHP & MySQL CRUD)
 */
$basePath = '../';
require_once __DIR__ . '/../config/db.php';

// Check Admin Access
if (!isAdmin()) {
    header("Location: ../login.php");
    exit();
}

$successMsg = '';
$errorMsg = '';

// 1. Handle Delete Action
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $deleteId = (int)$_GET['id'];
    if ($deleteId > 0) {
        $delQuery = "DELETE FROM products WHERE id = $deleteId";
        if (mysqli_query($conn, $delQuery)) {
            $successMsg = "Product #$deleteId deleted successfully.";
        } else {
            $errorMsg = "Could not delete product: " . mysqli_error($conn);
        }
    }
}

// 2. Handle Add / Edit Product POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $prodId = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
    $name = trim($_POST['name'] ?? '');
    $generic = trim($_POST['generic_name'] ?? '');
    $categoryId = (int)($_POST['category_id'] ?? 1);
    $price = (float)($_POST['price'] ?? 0.0);
    $stock = (int)($_POST['stock'] ?? 10);
    $dosage = trim($_POST['dosage'] ?? '');
    $manufacturer = trim($_POST['manufacturer'] ?? 'State Pharmaceuticals (SPMC)');
    $requiresRx = isset($_POST['requires_prescription']) ? 1 : 0;
    $image = trim($_POST['image'] ?? 'https://images.unsplash.com/photo-1584308666744-24d5c474f2ae?w=500&auto=format&fit=crop&q=80');
    $description = trim($_POST['description'] ?? '');

    // Handle local image file upload if provided (Base64 Conversion)
    if (isset($_FILES['product_file']) && $_FILES['product_file']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['product_file'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        if (in_array($ext, $allowed)) {
            $mimeType = function_exists('mime_content_type') ? mime_content_type($file['tmp_name']) : '';
            if (empty($mimeType) || $mimeType === 'application/octet-stream') {
                $mimeType = ($ext === 'png') ? 'image/png' : (($ext === 'webp') ? 'image/webp' : 'image/jpeg');
            }
            $binaryData = file_get_contents($file['tmp_name']);
            $image = 'data:' . $mimeType . ';base64,' . base64_encode($binaryData);
        }
    }

    if (empty($name) || $price <= 0) {
        $errorMsg = "Please provide a valid product name and price.";
    } else {
        $safeName = mysqli_real_escape_string($conn, $name);
        $safeGeneric = mysqli_real_escape_string($conn, $generic);
        $safeDosage = mysqli_real_escape_string($conn, $dosage);
        $safeManufacturer = mysqli_real_escape_string($conn, $manufacturer);
        $safeImage = mysqli_real_escape_string($conn, $image);
        $safeDescription = mysqli_real_escape_string($conn, $description);

        if ($prodId > 0) {
            // Update existing product
            $updateSql = "UPDATE products SET 
                          name = '$safeName',
                          generic_name = '$safeGeneric',
                          category_id = $categoryId,
                          price = $price,
                          stock = $stock,
                          dosage = '$safeDosage',
                          manufacturer = '$safeManufacturer',
                          requires_prescription = $requiresRx,
                          image = '$safeImage',
                          description = '$safeDescription'
                          WHERE id = $prodId";
            if (mysqli_query($conn, $updateSql)) {
                $successMsg = "Product #$prodId updated successfully.";
            } else {
                $errorMsg = "Error updating product: " . mysqli_error($conn);
            }
        } else {
            // Insert new product
            $insertSql = "INSERT INTO products (name, generic_name, category_id, price, stock, dosage, manufacturer, requires_prescription, image, description)
                          VALUES ('$safeName', '$safeGeneric', $categoryId, $price, $stock, '$safeDosage', '$safeManufacturer', $requiresRx, '$safeImage', '$safeDescription')";
            if (mysqli_query($conn, $insertSql)) {
                $successMsg = "New product added to inventory successfully.";
            } else {
                $errorMsg = "Error adding product: " . mysqli_error($conn);
            }
        }
    }
}

// 3. Fetch all products
$productsQuery = mysqli_query($conn, "SELECT p.*, c.name AS category_name FROM products p JOIN categories c ON p.category_id = c.id ORDER BY p.id DESC");

// Fetch categories for modal dropdown
$categoriesQuery = mysqli_query($conn, "SELECT * FROM categories ORDER BY name ASC");
$categoriesList = [];
if ($categoriesQuery) {
    while ($c = mysqli_fetch_assoc($categoriesQuery)) {
        $categoriesList[] = $c;
    }
}

$pageTitle = 'Manage Products | MediQuick Admin';
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
        <i class="bi bi-capsule text-emerald me-1"></i> Inventory & Stock Management &mdash; Kurunegala
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
      
      <!-- Top Action Bar -->
      <div class="d-flex justify-content-between align-items-center mb-4">
      <div>
        <h3 class="fw-bold mb-0">Pharmaceutical Products Inventory</h3>
        <p class="text-muted small mb-0">Add, edit, modify stock, and manage prescription categories</p>
      </div>
      <button type="button" class="btn btn-emerald btn-sm py-2 px-3" data-bs-toggle="modal" data-bs-target="#productModal" onclick="resetForm()">
        <i class="bi bi-plus-circle me-1"></i> Add New Medicine
      </button>
    </div>

    <?php if (!empty($successMsg)): ?>
      <div class="alert alert-success alert-dismissible fade show small" role="alert">
        <i class="bi bi-check-circle-fill me-1"></i> <?php echo htmlspecialchars($successMsg); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    <?php endif; ?>

    <?php if (!empty($errorMsg)): ?>
      <div class="alert alert-danger alert-dismissible fade show small" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-1"></i> <?php echo htmlspecialchars($errorMsg); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    <?php endif; ?>

    <!-- Products Table -->
    <div class="card card-custom p-3 bg-white">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0 small">
          <thead class="table-light">
            <tr>
              <th>ID</th>
              <th>Image</th>
              <th>Medicine Name</th>
              <th>Category</th>
              <th>Price</th>
              <th>Stock</th>
              <th>Type</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php if ($productsQuery && mysqli_num_rows($productsQuery) > 0): ?>
              <?php while ($prod = mysqli_fetch_assoc($productsQuery)): ?>
                <tr>
                  <td class="font-mono text-muted">#<?php echo $prod['id']; ?></td>
                  <td>
                    <img src="<?php echo htmlspecialchars($prod['image']); ?>" alt="" style="width: 45px; height: 45px; object-fit: cover; border-radius: 6px;">
                  </td>
                  <td>
                    <strong class="text-dark"><?php echo htmlspecialchars($prod['name']); ?></strong>
                    <?php if (!empty($prod['generic_name'])): ?>
                      <div class="text-muted" style="font-size: 0.75rem;"><?php echo htmlspecialchars($prod['generic_name']); ?></div>
                    <?php endif; ?>
                  </td>
                  <td><?php echo htmlspecialchars($prod['category_name']); ?></td>
                  <td class="fw-bold text-emerald"><?php echo formatLKR($prod['price']); ?></td>
                  <td>
                    <?php if ($prod['stock'] > 10): ?>
                      <span class="badge bg-success-subtle text-success"><?php echo $prod['stock']; ?> in stock</span>
                    <?php elseif ($prod['stock'] > 0): ?>
                      <span class="badge bg-warning-subtle text-warning"><?php echo $prod['stock']; ?> low</span>
                    <?php else: ?>
                      <span class="badge bg-danger-subtle text-danger">Out of Stock</span>
                    <?php endif; ?>
                  </td>
                  <td>
                    <?php if ($prod['requires_prescription']): ?>
                      <span class="badge bg-danger-subtle text-danger">Rx Required</span>
                    <?php else: ?>
                      <span class="badge bg-success-subtle text-success">OTC</span>
                    <?php endif; ?>
                  </td>
                  <td class="text-end">
                    <button type="button" class="btn btn-sm btn-outline-primary me-1" 
                            onclick='editProduct(<?php echo json_encode($prod); ?>)' title="Edit Product">
                      <i class="bi bi-pencil"></i>
                    </button>
                    <a href="products.php?action=delete&id=<?php echo $prod['id']; ?>" class="btn btn-sm btn-outline-danger" 
                       onclick="return confirmAction('Are you sure you want to permanently delete this medicine?');" title="Delete Product">
                      <i class="bi bi-trash"></i>
                    </a>
                  </td>
                </tr>
              <?php endwhile; ?>
            <?php else: ?>
              <tr><td colspan="8" class="text-center text-muted py-4">No products in inventory.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- ADD / EDIT PRODUCT MODAL -->
<div class="modal fade" id="productModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header bg-dark text-white py-3">
        <h5 class="modal-title font-heading fw-bold" id="modalTitle">
          <i class="bi bi-plus-circle-fill text-success me-2"></i> Add New Product
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <form action="products.php" method="POST" enctype="multipart/form-data">
        <div class="modal-body p-4">
          <input type="hidden" name="product_id" id="prodId" value="0">

          <div class="row g-3 mb-3">
            <div class="col-md-6">
              <label for="pName" class="form-label small fw-bold">Brand / Medicine Name <span class="text-danger">*</span></label>
              <input type="text" id="pName" name="name" class="form-control" required placeholder="e.g. Panadol Extra Tablets">
            </div>
            <div class="col-md-6">
              <label for="pGeneric" class="form-label small fw-bold">Generic Chemical Name</label>
              <input type="text" id="pGeneric" name="generic_name" class="form-control" placeholder="e.g. Paracetamol 500mg + Caffeine 65mg">
            </div>
          </div>

          <div class="row g-3 mb-3">
            <div class="col-md-4">
              <label for="pCat" class="form-label small fw-bold">Category</label>
              <select id="pCat" name="category_id" class="form-select">
                <?php foreach ($categoriesList as $cat): ?>
                  <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-4">
              <label for="pPrice" class="form-label small fw-bold">Price (LKR) <span class="text-danger">*</span></label>
              <input type="number" step="0.01" id="pPrice" name="price" class="form-control" required placeholder="e.g. 180.00">
            </div>
            <div class="col-md-4">
              <label for="pStock" class="form-label small fw-bold">Initial Stock Units</label>
              <input type="number" id="pStock" name="stock" class="form-control" value="50" min="0">
            </div>
          </div>

          <div class="row g-3 mb-3">
            <div class="col-md-6">
              <label for="pDosage" class="form-label small fw-bold">Dosage / Unit Description</label>
              <input type="text" id="pDosage" name="dosage" class="form-control" placeholder="e.g. 12 Caplets Strip / 500mg">
            </div>
            <div class="col-md-6">
              <label for="pMfg" class="form-label small fw-bold">Manufacturer</label>
              <input type="text" id="pMfg" name="manufacturer" class="form-control" placeholder="e.g. GlaxoSmithKline (GSK)">
            </div>
          </div>

          <div class="row g-3 mb-3">
            <div class="col-md-6">
              <label for="pFile" class="form-label small fw-bold">Upload Image File (from PC)</label>
              <input type="file" id="pFile" name="product_file" class="form-control" accept="image/*">
            </div>
            <div class="col-md-6">
              <label for="pImg" class="form-label small fw-bold">Or Image URL</label>
              <input type="text" id="pImg" name="image" class="form-control" placeholder="https://..." value="https://images.unsplash.com/photo-1584308666744-24d5c474f2ae?w=500&auto=format&fit=crop&q=80">
            </div>
          </div>

          <div class="mb-3">
            <label for="pDesc" class="form-label small fw-bold">Product Description & Usage Guidelines</label>
            <textarea id="pDesc" name="description" class="form-control" rows="3" placeholder="Enter clinical usage, indications, and dosage warnings..."></textarea>
          </div>

          <div class="form-check form-switch p-3 bg-light rounded-3">
            <input class="form-check-input" type="checkbox" id="pRx" name="requires_prescription" value="1">
            <label class="form-check-label fw-bold small text-dark" for="pRx">
              Requires Valid Doctor's Prescription (Rx Schedule Drug)
            </label>
          </div>

        </div>

        <div class="modal-footer bg-light">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-emerald px-4">
            <i class="bi bi-save me-1"></i> Save Product
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- JavaScript to populate edit modal -->
<script>
function resetForm() {
  document.getElementById('modalTitle').innerHTML = '<i class="bi bi-plus-circle-fill text-success me-2"></i> Add New Product';
  document.getElementById('prodId').value = '0';
  document.getElementById('pName').value = '';
  document.getElementById('pGeneric').value = '';
  document.getElementById('pPrice').value = '';
  document.getElementById('pStock').value = '50';
  document.getElementById('pDosage').value = '';
  document.getElementById('pMfg').value = 'State Pharmaceuticals (SPMC)';
  document.getElementById('pDesc').value = '';
  document.getElementById('pRx').checked = false;
  if (document.getElementById('pFile')) document.getElementById('pFile').value = '';
}

function editProduct(prod) {
  document.getElementById('modalTitle').innerHTML = '<i class="bi bi-pencil-square text-primary me-2"></i> Edit Product #' + prod.id;
  document.getElementById('prodId').value = prod.id;
  document.getElementById('pName').value = prod.name || '';
  document.getElementById('pGeneric').value = prod.generic_name || '';
  document.getElementById('pCat').value = prod.category_id || '1';
  document.getElementById('pPrice').value = prod.price || '';
  document.getElementById('pStock').value = prod.stock || '0';
  document.getElementById('pDosage').value = prod.dosage || '';
  document.getElementById('pMfg').value = prod.manufacturer || '';
  document.getElementById('pImg').value = prod.image || '';
  document.getElementById('pDesc').value = prod.description || '';
  document.getElementById('pRx').checked = prod.requires_prescription == 1;

  const modal = new bootstrap.Modal(document.getElementById('productModal'));
  modal.show();
}
</script>

<!-- Bootstrap Bundle & JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/main.js"></script>
</body>
</html>
