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
    $image = trim($_POST['image'] ?? 'assets/img/products/panadol-extra.jpg');
    $description = trim($_POST['description'] ?? '');

    // Handle local image file upload if provided
    if (isset($_FILES['product_file']) && $_FILES['product_file']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['product_file'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        if (in_array($ext, $allowed)) {
            $uploadDir = __DIR__ . '/../assets/img/products/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            $newFileName = 'prod_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
            $targetPath = $uploadDir . $newFileName;
            if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                $image = 'assets/img/products/' . $newFileName;
            } else {
                // Fallback to Base64
                $mimeType = function_exists('mime_content_type') ? mime_content_type($file['tmp_name']) : 'image/jpeg';
                $binaryData = file_get_contents($file['tmp_name']);
                $image = 'data:' . $mimeType . ';base64,' . base64_encode($binaryData);
            }
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
      <div class="d-flex align-items-center gap-2">
        <button class="btn btn-sm btn-light d-lg-none border px-2 py-1 shadow-none" type="button" onclick="toggleAdminSidebar()" aria-label="Toggle Menu">
          <i class="bi bi-list fs-5"></i>
        </button>
        <div class="small text-secondary fw-semibold text-truncate">
          <i class="bi bi-capsule-pill text-emerald me-1"></i> <span class="d-none d-sm-inline">Inventory & Stock &mdash; </span>Products
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
      
      <!-- Page Header & Action -->
      <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div>
          <h3 class="fw-bold mb-1 font-heading text-dark">Pharmaceutical Products Inventory</h3>
          <p class="text-muted small mb-0">Add, edit, modify stock, upload product photography, and manage prescription categories</p>
        </div>
        <button type="button" class="btn btn-emerald btn-sm py-2 px-3 shadow-sm rounded-3 d-inline-flex align-items-center gap-1.5" 
                data-bs-toggle="modal" data-bs-target="#productModal" onclick="resetForm()">
          <i class="bi bi-plus-circle-fill"></i> Add New Medicine
        </button>
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

      <!-- Products Inventory Table -->
      <div class="card card-custom bg-white border-0 shadow-sm overflow-hidden">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead class="table-light text-secondary small text-uppercase" style="font-size: 0.75rem; letter-spacing: 0.05em;">
              <tr>
                <th class="ps-3 py-3" style="width: 70px;">ID</th>
                <th style="width: 80px;">Image</th>
                <th style="min-width: 200px;">Medicine Name</th>
                <th>Category</th>
                <th>Price</th>
                <th>Stock</th>
                <th>Type</th>
                <th class="text-end pe-3">Actions</th>
              </tr>
            </thead>
            <tbody class="small">
              <?php if ($productsQuery && mysqli_num_rows($productsQuery) > 0): ?>
                <?php while ($prod = mysqli_fetch_assoc($productsQuery)): 
                  $pImg = $prod['image'];
                  if (strpos($pImg, 'http') !== 0 && strpos($pImg, 'data:') !== 0) {
                      $pImg = $basePath . $pImg;
                  }
                ?>
                  <tr>
                    <td class="ps-3 font-mono text-muted">#<?php echo $prod['id']; ?></td>
                    <td>
                      <img src="<?php echo htmlspecialchars($pImg); ?>" alt="<?php echo htmlspecialchars($prod['name']); ?>" style="width: 48px; height: 48px; object-fit: contain; background: #ffffff; border-radius: 8px; border: 1px solid #e2e8f0; padding: 2px;">
                    </td>
                    <td>
                      <strong class="text-dark d-block" style="font-size: 0.9rem;"><?php echo htmlspecialchars($prod['name']); ?></strong>
                      <?php if (!empty($prod['generic_name'])): ?>
                        <div class="text-muted" style="font-size: 0.75rem;"><?php echo htmlspecialchars($prod['generic_name']); ?></div>
                      <?php endif; ?>
                    </td>
                    <td><span class="badge bg-light text-dark border"><?php echo htmlspecialchars($prod['category_name']); ?></span></td>
                    <td class="fw-bold text-emerald"><?php echo formatLKR($prod['price']); ?></td>
                    <td>
                      <?php if ($prod['stock'] > 20): ?>
                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1 rounded-pill"><?php echo $prod['stock']; ?> in stock</span>
                      <?php elseif ($prod['stock'] > 0): ?>
                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2.5 py-1 rounded-pill"><?php echo $prod['stock']; ?> low</span>
                      <?php else: ?>
                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2.5 py-1 rounded-pill">Out of Stock</span>
                      <?php endif; ?>
                    </td>
                    <td>
                      <?php if ($prod['requires_prescription']): ?>
                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-0.5 rounded-pill" style="font-size: 0.7rem;">Rx Required</span>
                      <?php else: ?>
                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-0.5 rounded-pill" style="font-size: 0.7rem;">OTC</span>
                      <?php endif; ?>
                    </td>
                    <td class="text-end pe-3">
                      <button type="button" class="btn btn-sm btn-outline-primary me-1 rounded-3" 
                              onclick='editProduct(<?php echo htmlspecialchars(json_encode($prod), ENT_QUOTES, 'UTF-8'); ?>)' title="Edit Product">
                        <i class="bi bi-pencil"></i>
                      </button>
                      <a href="products.php?action=delete&id=<?php echo $prod['id']; ?>" class="btn btn-sm btn-outline-danger rounded-3" 
                         onclick="return confirmAction('Are you sure you want to permanently delete <?php echo htmlspecialchars(addslashes($prod['name'])); ?> (#<?php echo $prod['id']; ?>)? This medicine will be removed from your store inventory.', 'Delete Medicine?');" title="Delete Product">
                        <i class="bi bi-trash"></i>
                      </a>
                    </td>
                  </tr>
                <?php endwhile; ?>
              <?php else: ?>
                <tr><td colspan="8" class="text-center text-muted py-5">No products in inventory.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- ========================================================================= -->
<!-- ADD / EDIT PRODUCT MODAL WITH DEDICATED IMAGE MANAGER & LIVE PREVIEW -->
<!-- ========================================================================= -->
<div class="modal fade" id="productModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
    <form action="products.php" method="POST" enctype="multipart/form-data" class="modal-content border-0 shadow-lg" style="border-radius: 20px; overflow: hidden; max-height: 90vh;">
      
      <div class="modal-header bg-dark text-white py-3 px-4 flex-shrink-0" style="background-color: #0f172a !important;">
        <h5 class="modal-title font-heading fw-bold mb-0" id="modalTitle">
          <i class="bi bi-plus-circle-fill text-emerald me-2"></i> Add New Product
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body p-4" style="overflow-y: auto;">
        <input type="hidden" name="product_id" id="prodId" value="0">

        <div class="row g-3 mb-3">
          <div class="col-md-6">
            <label for="pName" class="form-label small fw-bold text-dark">Brand / Medicine Name <span class="text-danger">*</span></label>
            <input type="text" id="pName" name="name" class="form-control" required placeholder="e.g. Panadol Extra Tablets">
          </div>
          <div class="col-md-6">
            <label for="pGeneric" class="form-label small fw-bold text-dark">Generic Chemical Name</label>
            <input type="text" id="pGeneric" name="generic_name" class="form-control" placeholder="e.g. Paracetamol 500mg + Caffeine 65mg">
          </div>
        </div>

        <div class="row g-3 mb-3">
          <div class="col-md-4">
            <label for="pCat" class="form-label small fw-bold text-dark">Category</label>
            <select id="pCat" name="category_id" class="form-select">
              <?php foreach ($categoriesList as $cat): ?>
                <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-4">
            <label for="pPrice" class="form-label small fw-bold text-dark">Price (LKR) <span class="text-danger">*</span></label>
            <input type="number" step="0.01" id="pPrice" name="price" class="form-control" required placeholder="e.g. 180.00">
          </div>
          <div class="col-md-4">
            <label for="pStock" class="form-label small fw-bold text-dark">Initial Stock Units</label>
            <input type="number" id="pStock" name="stock" class="form-control" value="50" min="0">
          </div>
        </div>

        <div class="row g-3 mb-3">
          <div class="col-md-6">
            <label for="pDosage" class="form-label small fw-bold text-dark">Dosage / Unit Description</label>
            <input type="text" id="pDosage" name="dosage" class="form-control" placeholder="e.g. 12 Caplets Pack / 500mg">
          </div>
          <div class="col-md-6">
            <label for="pMfg" class="form-label small fw-bold text-dark">Manufacturer</label>
            <input type="text" id="pMfg" name="manufacturer" class="form-control" placeholder="e.g. GlaxoSmithKline (GSK)">
          </div>
        </div>

        <!-- CLEAN DIRECT PRODUCT IMAGE UPLOADER & PREVIEW -->
        <div class="card p-3 mb-3 bg-light border rounded-3">
          <div class="d-flex justify-content-between align-items-center mb-2.5">
            <label class="form-label small fw-bold text-dark mb-0">
              <i class="bi bi-camera-fill text-emerald me-1"></i> Product Photography / Image
            </label>
            <span class="badge bg-emerald-subtle text-emerald border border-emerald rounded-pill px-2.5 py-1" id="pImgBadge" style="font-size: 0.72rem;">
              Current Image
            </span>
          </div>

          <div class="row g-3 align-items-center">
            <!-- Live Image Thumbnail Preview Box -->
            <div class="col-auto">
              <div class="rounded-3 border bg-white p-1 d-flex align-items-center justify-content-center shadow-sm" style="width: 100px; height: 100px; overflow: hidden;">
                <img id="pImgPreview" src="../assets/img/products/panadol-extra.jpg" alt="Medicine Preview" class="img-fluid rounded" style="max-height: 90px; max-width: 90px; object-fit: contain;">
              </div>
            </div>

            <!-- Single File Upload Input & Note -->
            <div class="col">
              <input type="hidden" id="pImg" name="image" value="assets/img/products/panadol-extra.jpg">
              <label for="pFile" class="form-label text-dark small fw-semibold mb-1">
                Upload Medicine Image File <span class="text-muted fw-normal">(JPG, PNG, WEBP)</span>
              </label>
              <input type="file" id="pFile" name="product_file" class="form-control" accept="image/png, image/jpeg, image/webp" onchange="handleProductFilePreview(this)">
              <div class="form-text text-muted" style="font-size: 0.75rem;">
                <i class="bi bi-info-circle me-1"></i> Choose a photo from your computer. When editing, leave empty to keep current image.
              </div>
            </div>
          </div>
        </div>

        <div class="mb-3">
          <label for="pDesc" class="form-label small fw-bold text-dark">Product Description & Usage Guidelines</label>
          <textarea id="pDesc" name="description" class="form-control" rows="3" placeholder="Enter clinical usage, indications, and dosage warnings..."></textarea>
        </div>

        <div class="d-flex align-items-center justify-content-between p-3 bg-light rounded-3 border">
          <div class="d-flex align-items-center gap-2.5 pe-3">
            <div class="rounded-circle d-flex align-items-center justify-content-center text-danger bg-danger-subtle flex-shrink-0" style="width: 38px; height: 38px;">
              <i class="bi bi-prescription2 fs-5"></i>
            </div>
            <div>
              <label class="form-check-label fw-bold small text-dark d-block mb-0 cursor-pointer" for="pRx">
                Requires Doctor's Prescription (NMRA Schedule II)
              </label>
              <small class="text-muted d-block" style="font-size: 0.74rem;">
                Patients must upload a verified SLMC doctor prescription to order this medicine.
              </small>
            </div>
          </div>
          <div class="form-check form-switch m-0 fs-5 flex-shrink-0">
            <input class="form-check-input cursor-pointer" type="checkbox" role="switch" id="pRx" name="requires_prescription" value="1">
          </div>
        </div>

      </div>

      <div class="modal-footer bg-light py-2.5 px-4 flex-shrink-0">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-emerald px-4 fw-bold">
          <i class="bi bi-save2 me-1"></i> Save Product
        </button>
      </div>
    </form>
  </div>
</div>

<!-- JavaScript to populate and preview edit modal -->
<script>
function handleProductFilePreview(input) {
  if (input.files && input.files[0]) {
    const file = input.files[0];
    const reader = new FileReader();
    reader.onload = function(e) {
      document.getElementById('pImgPreview').src = e.target.result;
      const badge = document.getElementById('pImgBadge');
      if (badge) {
        badge.textContent = 'New File (' + (file.size / 1024).toFixed(0) + ' KB)';
        badge.className = 'badge bg-success text-white rounded-pill px-2.5 py-1';
      }
    };
    reader.readAsDataURL(file);
  }
}

function resetForm() {
  document.getElementById('modalTitle').innerHTML = '<i class="bi bi-plus-circle-fill text-emerald me-2"></i> Add New Medicine';
  document.getElementById('prodId').value = '0';
  document.getElementById('pName').value = '';
  document.getElementById('pGeneric').value = '';
  document.getElementById('pPrice').value = '';
  document.getElementById('pStock').value = '50';
  document.getElementById('pDosage').value = '';
  document.getElementById('pMfg').value = 'State Pharmaceuticals (SPMC)';
  document.getElementById('pDesc').value = '';
  document.getElementById('pRx').checked = false;
  document.getElementById('pImg').value = 'assets/img/products/panadol-extra.jpg';
  if (document.getElementById('pFile')) document.getElementById('pFile').value = '';
  document.getElementById('pImgPreview').src = '../assets/img/products/panadol-extra.jpg';
  const badge = document.getElementById('pImgBadge');
  if (badge) {
    badge.textContent = 'Default Preview';
    badge.className = 'badge bg-secondary-subtle text-secondary border rounded-pill px-2.5 py-1';
  }
}

function editProduct(prod) {
  document.getElementById('modalTitle').innerHTML = '<i class="bi bi-pencil-square text-primary me-2"></i> Edit Medicine #' + prod.id;
  document.getElementById('prodId').value = prod.id;
  document.getElementById('pName').value = prod.name || '';
  document.getElementById('pGeneric').value = prod.generic_name || '';
  document.getElementById('pCat').value = prod.category_id || '1';
  document.getElementById('pPrice').value = prod.price || '';
  document.getElementById('pStock').value = prod.stock || '0';
  document.getElementById('pDosage').value = prod.dosage || '';
  document.getElementById('pMfg').value = prod.manufacturer || '';
  document.getElementById('pImg').value = prod.image || 'assets/img/products/panadol-extra.jpg';
  document.getElementById('pDesc').value = prod.description || '';
  document.getElementById('pRx').checked = prod.requires_prescription == 1;
  if (document.getElementById('pFile')) document.getElementById('pFile').value = '';
  
  let src = prod.image || 'assets/img/products/panadol-extra.jpg';
  if (src.indexOf('http') !== 0 && src.indexOf('data:') !== 0) {
    src = '../' + src;
  }
  document.getElementById('pImgPreview').src = src;
  const badge = document.getElementById('pImgBadge');
  if (badge) {
    badge.textContent = 'Current Image';
    badge.className = 'badge bg-emerald-subtle text-emerald border border-emerald rounded-pill px-2.5 py-1';
  }

  const modal = new bootstrap.Modal(document.getElementById('productModal'));
  modal.show();
}
</script>

<!-- Bootstrap Bundle & JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/main.js"></script>
</body>
</html>
