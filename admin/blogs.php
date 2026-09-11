<?php
/**
 * MediQuick Pharmacy - Health Blogs & Clinical Articles Management
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

// 1. Handle Delete Blog
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $deleteId = (int)$_GET['id'];
    if ($deleteId > 0) {
        $delQuery = "DELETE FROM blogs WHERE id = $deleteId";
        if (mysqli_query($conn, $delQuery)) {
            $successMsg = "Article #$deleteId deleted successfully.";
        } else {
            $errorMsg = "Could not delete article: " . mysqli_error($conn);
        }
    }
}

// 2. Handle Add / Edit Blog POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $blogId = isset($_POST['blog_id']) ? (int)$_POST['blog_id'] : 0;
    $title = trim($_POST['title'] ?? '');
    $category = trim($_POST['category'] ?? 'Prescription Care');
    $author = trim($_POST['author'] ?? 'Dr. Rohan Gunawardena (SLMC-PH)');
    $summary = trim($_POST['summary'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $image = trim($_POST['image'] ?? 'assets/img/blogs/antibiotic-course-guide.jpg');

    // Handle local image file upload if uploaded
    if (isset($_FILES['blog_file']) && $_FILES['blog_file']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['blog_file'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        if (in_array($ext, $allowed)) {
            $uploadDir = __DIR__ . '/../assets/img/blogs/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            $newFileName = 'blog_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
            $targetPath = $uploadDir . $newFileName;
            if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                $image = 'assets/img/blogs/' . $newFileName;
            } else {
                $mimeType = function_exists('mime_content_type') ? mime_content_type($file['tmp_name']) : 'image/jpeg';
                $binaryData = file_get_contents($file['tmp_name']);
                $image = 'data:' . $mimeType . ';base64,' . base64_encode($binaryData);
            }
        }
    }

    if (empty($title) || empty($summary) || empty($content)) {
        $errorMsg = "Please provide an article title, summary, and clinical content.";
    } else {
        $safeTitle = mysqli_real_escape_string($conn, $title);
        $safeCategory = mysqli_real_escape_string($conn, $category);
        $safeAuthor = mysqli_real_escape_string($conn, $author);
        $safeSummary = mysqli_real_escape_string($conn, $summary);
        $safeContent = mysqli_real_escape_string($conn, $content);
        $safeImage = mysqli_real_escape_string($conn, $image);

        if ($blogId > 0) {
            // Update existing article
            $updateSql = "UPDATE blogs SET 
                          title = '$safeTitle',
                          category = '$safeCategory',
                          author = '$safeAuthor',
                          summary = '$safeSummary',
                          content = '$safeContent',
                          image = '$safeImage'
                          WHERE id = $blogId";
            if (mysqli_query($conn, $updateSql)) {
                $successMsg = "Health article #$blogId updated successfully.";
            } else {
                $errorMsg = "Error updating article: " . mysqli_error($conn);
            }
        } else {
            // Insert new article
            $insertSql = "INSERT INTO blogs (title, category, author, summary, content, image, views)
                          VALUES ('$safeTitle', '$safeCategory', '$safeAuthor', '$safeSummary', '$safeContent', '$safeImage', 120)";
            if (mysqli_query($conn, $insertSql)) {
                $successMsg = "New health article published successfully to storefront.";
            } else {
                $errorMsg = "Error publishing article: " . mysqli_error($conn);
            }
        }
    }
}

// 3. Fetch all blogs
$blogsQuery = mysqli_query($conn, "SELECT * FROM blogs ORDER BY id DESC");
$totalBlogs = 0;
$totalViews = 0;
$rxCareCount = 0;
$wellnessCount = 0;

$blogsList = [];
if ($blogsQuery) {
    while ($b = mysqli_fetch_assoc($blogsQuery)) {
        $totalBlogs++;
        $totalViews += (int)$b['views'];
        if (stripos($b['category'], 'Prescription') !== false) $rxCareCount++;
        if (stripos($b['category'], 'Wellness') !== false || stripos($b['category'], 'Immunity') !== false) $wellnessCount++;
        $blogsList[] = $b;
    }
}

$pageTitle = 'Manage Health Blogs | MediQuick Admin';
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
          <i class="bi bi-journal-medical text-emerald me-1"></i> <span class="d-none d-sm-inline">Health Education &mdash; </span>Health Blogs
        </div>
      </div>
      <div class="d-flex align-items-center gap-2">
        <a href="../blogs.php" target="_blank" class="btn btn-sm btn-outline-secondary" style="font-size: 0.8rem;">
          <i class="bi bi-eye me-1"></i> View Blog Feed
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
          <h3 class="fw-bold text-dark mb-1 font-heading">Health Blogs & Patient Guides</h3>
          <p class="text-muted small mb-0">Publish clinical dosage advisories, storage guidelines, and wellness articles</p>
        </div>
        <button type="button" class="btn btn-emerald btn-sm py-2 px-3 shadow-sm rounded-3 d-inline-flex align-items-center gap-1.5 fw-semibold" 
                data-bs-toggle="modal" data-bs-target="#blogModal" onclick="resetBlogForm()">
          <i class="bi bi-plus-circle-fill"></i> Publish New Guide
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

      <!-- KPI Summary Cards -->
      <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
          <div class="stat-card-modern d-flex align-items-center justify-content-between">
            <div>
              <div class="text-muted small fw-semibold mb-1">Published Guides</div>
              <h3 class="fw-bold mb-0 text-dark"><?php echo $totalBlogs; ?></h3>
            </div>
            <div class="rounded-3 d-flex align-items-center justify-content-center text-primary" style="width: 46px; height: 46px; background-color: #e0f2fe;">
              <i class="bi bi-journal-richtext fs-4"></i>
            </div>
          </div>
        </div>

        <div class="col-6 col-lg-3">
          <div class="stat-card-modern d-flex align-items-center justify-content-between" style="border-left: 4px solid #059669;">
            <div>
              <div class="text-muted small fw-semibold mb-1">Prescription Care</div>
              <h3 class="fw-bold mb-0 text-emerald"><?php echo $rxCareCount; ?></h3>
            </div>
            <div class="rounded-3 d-flex align-items-center justify-content-center text-emerald" style="width: 46px; height: 46px; background-color: #ecfdf5;">
              <i class="bi bi-capsule fs-4"></i>
            </div>
          </div>
        </div>

        <div class="col-6 col-lg-3">
          <div class="stat-card-modern d-flex align-items-center justify-content-between" style="border-left: 4px solid #f59e0b;">
            <div>
              <div class="text-muted small fw-semibold mb-1">Wellness & Immunity</div>
              <h3 class="fw-bold mb-0 text-warning"><?php echo $wellnessCount; ?></h3>
            </div>
            <div class="rounded-3 d-flex align-items-center justify-content-center text-warning" style="width: 46px; height: 46px; background-color: #fef3c7;">
              <i class="bi bi-heart-pulse fs-4"></i>
            </div>
          </div>
        </div>

        <div class="col-6 col-lg-3">
          <div class="stat-card-modern d-flex align-items-center justify-content-between" style="border-left: 4px solid #8b5cf6;">
            <div>
              <div class="text-muted small fw-semibold mb-1">Total Patient Reads</div>
              <h3 class="fw-bold mb-0" style="color: #7c3aed;"><?php echo number_format($totalViews); ?></h3>
            </div>
            <div class="rounded-3 d-flex align-items-center justify-content-center" style="width: 46px; height: 46px; background-color: #f5f3ff; color: #7c3aed;">
              <i class="bi bi-eye fs-4"></i>
            </div>
          </div>
        </div>
      </div>

      <!-- Main Card with Toolbar & Table -->
      <div class="card card-custom bg-white border-0 shadow-sm overflow-hidden">
        
        <!-- Filter & Search Toolbar -->
        <div class="p-3 border-bottom bg-light d-flex flex-wrap align-items-center justify-content-between gap-3">
          <div class="fw-bold text-dark small d-flex align-items-center gap-1.5">
            <i class="bi bi-collection-fill text-emerald"></i>
            Article Library (<?php echo count($blogsList); ?>)
          </div>

          <!-- Instant Live Search Input -->
          <div class="position-relative" style="min-width: 280px;">
            <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
            <input type="text" id="blogLiveSearch" class="form-control form-control-sm ps-5 rounded-pill" placeholder="Search by title, category, author...">
          </div>
        </div>

        <!-- Blogs Table -->
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0" id="blogsTable">
            <thead class="bg-light text-secondary" style="font-size: 0.76rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; border-bottom: 2px solid #e2e8f0;">
              <tr>
                <th class="ps-4 py-3.5" style="width: 70px;">ID</th>
                <th style="width: 90px;">Cover</th>
                <th style="min-width: 260px;">Article Title & Summary</th>
                <th style="min-width: 150px;">Category</th>
                <th style="min-width: 170px;">Author</th>
                <th class="text-center" style="width: 100px;">Reads</th>
                <th class="text-end pe-4" style="width: 140px;">Actions</th>
              </tr>
            </thead>
            <tbody class="small">
              <?php if (!empty($blogsList)): ?>
                <?php foreach ($blogsList as $b): 
                  $bImg = $b['image'];
                  if (strpos($bImg, 'http') !== 0 && strpos($bImg, 'data:') !== 0) {
                      $bImg = '../' . $bImg;
                  }
                ?>
                  <tr class="blog-row-item" style="transition: background-color 0.15s ease;">
                    <!-- ID -->
                    <td class="ps-4 py-3.5 font-mono text-muted">#B-<?php echo str_pad($b['id'], 3, '0', STR_PAD_LEFT); ?></td>

                    <!-- Cover Image -->
                    <td class="py-3.5">
                      <img src="<?php echo htmlspecialchars($bImg); ?>" alt="<?php echo htmlspecialchars($b['title']); ?>" 
                           style="width: 60px; height: 42px; object-fit: cover; border-radius: 8px; border: 1px solid #e2e8f0;">
                    </td>

                    <!-- Title & Summary -->
                    <td class="py-3.5">
                      <strong class="text-dark d-block blog-title" style="font-size: 0.92rem;">
                        <?php echo htmlspecialchars($b['title']); ?>
                      </strong>
                      <div class="text-muted text-truncate mt-0.5" style="max-width: 320px; font-size: 0.76rem;">
                        <?php echo htmlspecialchars($b['summary']); ?>
                      </div>
                    </td>

                    <!-- Category -->
                    <td class="py-3.5">
                      <span class="badge bg-emerald-subtle text-emerald border border-emerald rounded-pill px-2.5 py-1">
                        <?php echo htmlspecialchars($b['category']); ?>
                      </span>
                    </td>

                    <!-- Author -->
                    <td class="py-3.5">
                      <div class="text-dark fw-semibold" style="font-size: 0.82rem;">
                        <i class="bi bi-person-badge text-emerald me-1"></i><?php echo htmlspecialchars($b['author']); ?>
                      </div>
                      <div class="text-muted" style="font-size: 0.72rem;">
                        <?php echo date('M d, Y', strtotime($b['created_at'])); ?>
                      </div>
                    </td>

                    <!-- Reads / Views -->
                    <td class="text-center py-3.5">
                      <span class="badge bg-light text-secondary border px-2.5 py-1 rounded-pill">
                        <i class="bi bi-eye-fill text-muted me-1"></i> <?php echo number_format($b['views']); ?>
                      </span>
                    </td>

                    <!-- Actions -->
                    <td class="text-end pe-4 py-3.5">
                      <div class="d-inline-flex align-items-center gap-1">
                        <a href="../blog-details.php?id=<?php echo $b['id']; ?>" target="_blank" class="btn btn-sm btn-outline-secondary p-1.5 rounded-3" title="View on Storefront">
                          <i class="bi bi-box-arrow-up-right"></i>
                        </a>
                        <button type="button" class="btn btn-sm btn-outline-primary p-1.5 rounded-3" 
                                onclick='editBlog(<?php echo htmlspecialchars(json_encode($b), ENT_QUOTES, 'UTF-8'); ?>)' title="Edit Guide">
                          <i class="bi bi-pencil"></i>
                        </button>
                        <a href="blogs.php?action=delete&id=<?php echo $b['id']; ?>" class="btn btn-sm btn-outline-danger p-1.5 rounded-3" 
                           onclick="return confirmAction('Are you sure you want to delete article \'<?php echo htmlspecialchars(addslashes($b['title'])); ?>\'?', 'Delete Blog Article?');" title="Delete Guide">
                          <i class="bi bi-trash"></i>
                        </a>
                      </div>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr>
                  <td colspan="7" class="text-center text-muted py-5">
                    <i class="bi bi-journal-x fs-1 d-block mb-2 text-secondary"></i>
                    No health articles published yet.
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

<!-- ========================================================================= -->
<!-- ADD / EDIT HEALTH BLOG MODAL -->
<!-- ========================================================================= -->
<div class="modal fade" id="blogModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
    <form action="blogs.php" method="POST" enctype="multipart/form-data" class="modal-content border-0 shadow-lg" style="border-radius: 20px; overflow: hidden; max-height: 90vh;">
      
      <!-- Modal Header -->
      <div class="modal-header bg-dark text-white py-3 px-4 flex-shrink-0" style="background-color: #0f172a !important;">
        <h5 class="modal-title font-heading fw-bold mb-0" id="blogModalTitle">
          <i class="bi bi-plus-circle-fill text-emerald me-2"></i> Publish Health Guide
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <!-- Modal Body -->
      <div class="modal-body p-4" style="overflow-y: auto;">
        <input type="hidden" name="blog_id" id="bId" value="0">

        <!-- Title -->
        <div class="mb-3">
          <label for="bTitle" class="form-label small fw-bold text-dark">Article Title <span class="text-danger">*</span></label>
          <input type="text" id="bTitle" name="title" class="form-control" required placeholder="e.g. Safe Storage of Insulin in Tropical Climates">
        </div>

        <div class="row g-3 mb-3">
          <!-- Category -->
          <div class="col-md-6">
            <label for="bCat" class="form-label small fw-bold text-dark">Clinical Category</label>
            <select id="bCat" name="category" class="form-select">
              <option value="Prescription Care">Prescription Care</option>
              <option value="Diabetes & Storage">Diabetes & Storage</option>
              <option value="Cardiovascular Health">Cardiovascular Health</option>
              <option value="Wellness & Immunity">Wellness & Immunity</option>
              <option value="Baby & Pediatric Care">Baby & Pediatric Care</option>
              <option value="Clinical Pharmacotherapy">Clinical Pharmacotherapy</option>
            </select>
          </div>

          <!-- Author -->
          <div class="col-md-6">
            <label for="bAuthor" class="form-label small fw-bold text-dark">Author / Verification Credential</label>
            <input type="text" id="bAuthor" name="author" class="form-control" value="Dr. Rohan Gunawardena (SLMC-PH)" placeholder="e.g. Dr. Rohan Gunawardena (SLMC-PH)">
          </div>
        </div>

        <!-- Cover Image Uploader & Live Preview -->
        <div class="card p-3 mb-3 bg-light border rounded-3">
          <div class="d-flex justify-content-between align-items-center mb-2">
            <label class="form-label small fw-bold text-dark mb-0">
              <i class="bi bi-image text-emerald me-1"></i> Featured Cover Image
            </label>
            <span class="badge bg-emerald-subtle text-emerald border border-emerald rounded-pill px-2.5 py-1" id="bImgBadge" style="font-size: 0.72rem;">
              Current Cover
            </span>
          </div>

          <div class="row g-3 align-items-center">
            <div class="col-auto">
              <div class="rounded-3 border bg-white p-1 d-flex align-items-center justify-content-center shadow-sm" style="width: 110px; height: 75px; overflow: hidden;">
                <img id="bImgPreview" src="../assets/img/blogs/antibiotic-course-guide.jpg" alt="Cover Preview" class="img-fluid rounded" style="max-height: 70px; object-fit: cover;">
              </div>
            </div>

            <div class="col">
              <input type="hidden" id="bImg" name="image" value="assets/img/blogs/antibiotic-course-guide.jpg">
              <label for="bFile" class="form-label text-dark small fw-semibold mb-1">
                Upload New Cover Image <span class="text-muted fw-normal">(JPG, PNG, WEBP)</span>
              </label>
              <input type="file" id="bFile" name="blog_file" class="form-control" accept="image/*" onchange="handleBlogFilePreview(this)">
              <small class="text-muted d-block mt-1" style="font-size: 0.74rem;">Leave empty when editing to keep current cover image.</small>
            </div>
          </div>
        </div>

        <!-- Summary -->
        <div class="mb-3">
          <label for="bSummary" class="form-label small fw-bold text-dark">Article Abstract / Summary <span class="text-danger">*</span></label>
          <textarea id="bSummary" name="summary" class="form-control" rows="2" required placeholder="A concise 2-sentence overview displayed on blog feeds and cards..."></textarea>
        </div>

        <!-- Full Content -->
        <div class="mb-3">
          <label for="bContent" class="form-label small fw-bold text-dark">Full Clinical Guide Content <span class="text-danger">*</span></label>
          <textarea id="bContent" name="content" class="form-control" rows="6" required placeholder="Detailed clinical guidelines, patient indications, dosage rules, and storage best practices..."></textarea>
        </div>

      </div>

      <!-- Modal Footer -->
      <div class="modal-footer bg-light py-2.5 px-4 flex-shrink-0">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-emerald px-4 fw-bold">
          <i class="bi bi-send-fill me-1"></i> Publish Article
        </button>
      </div>

    </form>
  </div>
</div>

<script>
function handleBlogFilePreview(input) {
  if (input.files && input.files[0]) {
    const file = input.files[0];
    const reader = new FileReader();
    reader.onload = function(e) {
      document.getElementById('bImgPreview').src = e.target.result;
      const badge = document.getElementById('bImgBadge');
      if (badge) {
        badge.textContent = 'New File (' + (file.size / 1024).toFixed(0) + ' KB)';
        badge.className = 'badge bg-success text-white rounded-pill px-2.5 py-1';
      }
    };
    reader.readAsDataURL(file);
  }
}

function resetBlogForm() {
  document.getElementById('blogModalTitle').innerHTML = '<i class="bi bi-plus-circle-fill text-emerald me-2"></i> Publish Health Guide';
  document.getElementById('bId').value = '0';
  document.getElementById('bTitle').value = '';
  document.getElementById('bCat').value = 'Prescription Care';
  document.getElementById('bAuthor').value = 'Dr. Rohan Gunawardena (SLMC-PH)';
  document.getElementById('bSummary').value = '';
  document.getElementById('bContent').value = '';
  document.getElementById('bImg').value = 'assets/img/blogs/antibiotic-course-guide.jpg';
  if (document.getElementById('bFile')) document.getElementById('bFile').value = '';
  document.getElementById('bImgPreview').src = '../assets/img/blogs/antibiotic-course-guide.jpg';
  const badge = document.getElementById('bImgBadge');
  if (badge) {
    badge.textContent = 'Default Cover';
    badge.className = 'badge bg-secondary-subtle text-secondary border rounded-pill px-2.5 py-1';
  }
}

function editBlog(b) {
  document.getElementById('blogModalTitle').innerHTML = '<i class="bi bi-pencil-square text-primary me-2"></i> Edit Health Guide #' + b.id;
  document.getElementById('bId').value = b.id;
  document.getElementById('bTitle').value = b.title || '';
  document.getElementById('bCat').value = b.category || 'Prescription Care';
  document.getElementById('bAuthor').value = b.author || '';
  document.getElementById('bSummary').value = b.summary || '';
  document.getElementById('bContent').value = b.content || '';
  document.getElementById('bImg').value = b.image || '';
  if (document.getElementById('bFile')) document.getElementById('bFile').value = '';

  let src = b.image || 'assets/img/blogs/antibiotic-course-guide.jpg';
  if (src.indexOf('http') !== 0 && src.indexOf('data:') !== 0) {
    src = '../' + src;
  }
  document.getElementById('bImgPreview').src = src;
  const badge = document.getElementById('bImgBadge');
  if (badge) {
    badge.textContent = 'Current Cover';
    badge.className = 'badge bg-emerald-subtle text-emerald border border-emerald rounded-pill px-2.5 py-1';
  }

  const modal = new bootstrap.Modal(document.getElementById('blogModal'));
  modal.show();
}

// Live Search
document.addEventListener('DOMContentLoaded', function() {
  const searchInput = document.getElementById('blogLiveSearch');
  if (searchInput) {
    searchInput.addEventListener('input', function() {
      const query = this.value.toLowerCase().trim();
      const rows = document.querySelectorAll('.blog-row-item');

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
