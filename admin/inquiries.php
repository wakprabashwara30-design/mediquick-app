<?php
/**
 * MediQuick Pharmacy - Staff Inquiries Management Portal
 * Module: CSE4206 - Web Application Development (Kurunegala)
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

// Handle Delete Action
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $delId = (int)$_GET['id'];
    if ($delId > 0) {
        if (mysqli_query($conn, "DELETE FROM inquiries WHERE id = $delId")) {
            $successMsg = "Inquiry message #$delId removed.";
        }
    }
}

// Handle Status Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_inquiry'])) {
    $inqId = (int)$_POST['inquiry_id'];
    $status = trim($_POST['status'] ?? 'New');
    if ($inqId > 0 && ($status === 'New' || $status === 'Replied')) {
        $safeStatus = mysqli_real_escape_string($conn, $status);
        if (mysqli_query($conn, "UPDATE inquiries SET status = '$safeStatus' WHERE id = $inqId")) {
            $successMsg = "Inquiry #$inqId status marked as $status.";
        }
    }
}

// Fetch all inquiries
$inquiriesQuery = mysqli_query($conn, "SELECT * FROM inquiries ORDER BY id DESC");

$pageTitle = 'Customer Inquiries | MediQuick Staff Portal';
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
        <i class="bi bi-chat-left-dots-fill text-emerald me-1"></i> Customer Consultations & Inquiries &mdash; Kurunegala
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
      
      <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
          <h3 class="fw-bold mb-0">Customer Inquiries & Consultations</h3>
          <p class="text-muted small mb-0">Messages submitted from the Contact Us page in Kurunegala</p>
        </div>
      </div>

    <?php if (!empty($successMsg)): ?>
      <div class="alert alert-success alert-dismissible fade show small" role="alert">
        <i class="bi bi-check-circle-fill me-1"></i> <?php echo htmlspecialchars($successMsg); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    <?php endif; ?>

    <!-- Inquiries Table -->
    <div class="card card-custom p-3 bg-white">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0 small">
          <thead class="table-light">
            <tr>
              <th>ID</th>
              <th>Date</th>
              <th>Sender</th>
              <th>Contact Details</th>
              <th>Subject</th>
              <th style="max-width: 350px;">Message</th>
              <th>Status</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php if ($inquiriesQuery && mysqli_num_rows($inquiriesQuery) > 0): ?>
              <?php while ($inq = mysqli_fetch_assoc($inquiriesQuery)): ?>
                <tr>
                  <td class="font-mono text-muted">#INQ-<?php echo $inq['id']; ?></td>
                  <td class="text-secondary"><?php echo date('M d, Y', strtotime($inq['created_at'])); ?></td>
                  <td><strong><?php echo htmlspecialchars($inq['name']); ?></strong></td>
                  <td>
                    <div><a href="mailto:<?php echo htmlspecialchars($inq['email']); ?>" class="text-emerald"><?php echo htmlspecialchars($inq['email']); ?></a></div>
                    <?php if (!empty($inq['phone'])): ?>
                      <div class="text-muted" style="font-size: 0.75rem;"><i class="bi bi-telephone me-1"></i><?php echo htmlspecialchars($inq['phone']); ?></div>
                    <?php endif; ?>
                  </td>
                  <td><span class="badge bg-light text-dark border"><?php echo htmlspecialchars($inq['subject']); ?></span></td>
                  <td style="max-width: 350px;" class="text-secondary">
                    <?php echo nl2br(htmlspecialchars($inq['message'])); ?>
                  </td>
                  <td>
                    <form action="inquiries.php" method="POST" class="d-inline-flex align-items-center gap-1">
                      <input type="hidden" name="update_inquiry" value="1">
                      <input type="hidden" name="inquiry_id" value="<?php echo $inq['id']; ?>">
                      <select name="status" class="form-select form-select-sm" style="width: 95px; font-size: 0.75rem;" onchange="this.form.submit()">
                        <option value="New" <?php echo ($inq['status'] === 'New') ? 'selected' : ''; ?>>New</option>
                        <option value="Replied" <?php echo ($inq['status'] === 'Replied') ? 'selected' : ''; ?>>Replied</option>
                      </select>
                    </form>
                  </td>
                  <td class="text-end">
                    <a href="mailto:<?php echo htmlspecialchars($inq['email']); ?>?subject=Re: <?php echo urlencode($inq['subject']); ?>" class="btn btn-sm btn-outline-primary me-1" title="Reply via Email">
                      <i class="bi bi-reply"></i>
                    </a>
                    <a href="inquiries.php?action=delete&id=<?php echo $inq['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirmAction('Delete this inquiry message?');" title="Delete">
                      <i class="bi bi-trash"></i>
                    </a>
                  </td>
                </tr>
              <?php endwhile; ?>
            <?php else: ?>
              <tr><td colspan="8" class="text-center text-muted py-4">No customer inquiries found.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/main.js"></script>
</body>
</html>
