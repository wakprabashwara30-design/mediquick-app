<?php
/**
 * MediQuick Pharmacy - Staff & System Administrators Management
 * Module: CSE4206 - Web Application Development (Kurunegala)
 */
$basePath = '../';
require_once __DIR__ . '/../config/db.php';

// Security check: Only authenticated admins
if (!isAdmin()) {
    header('Location: ../login.php');
    exit();
}

$currentAdminId = (int)($_SESSION['user_id'] ?? 0);
$successMsg = '';
$errorMsg = '';

// 1. Handle Delete Staff Action
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $deleteId = (int)$_GET['id'];
    if ($deleteId === $currentAdminId) {
        $errorMsg = "Security Protection: You cannot delete your own currently logged-in administrator account.";
    } elseif ($deleteId > 0) {
        $delQuery = "DELETE FROM users WHERE id = $deleteId AND role = 'admin'";
        if (mysqli_query($conn, $delQuery)) {
            $successMsg = "Staff account #$deleteId deleted successfully.";
        } else {
            $errorMsg = "Could not delete staff account: " . mysqli_error($conn);
        }
    }
}

// 2. Handle Add / Edit Staff POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($name) || empty($email)) {
        $errorMsg = "Please provide the staff member's full name and email address.";
    } else {
        $safeName = mysqli_real_escape_string($conn, $name);
        $safeEmail = mysqli_real_escape_string($conn, $email);
        $safePhone = mysqli_real_escape_string($conn, $phone);
        $safeAddress = mysqli_real_escape_string($conn, $address);

        if ($userId > 0) {
            // Check for duplicate email on other users
            $dupCheck = mysqli_query($conn, "SELECT id FROM users WHERE email = '$safeEmail' AND id != $userId");
            if (mysqli_num_rows($dupCheck) > 0) {
                $errorMsg = "The email address '$safeEmail' is already in use by another account.";
            } else {
                if (!empty($password)) {
                    $hashed = password_hash($password, PASSWORD_DEFAULT);
                    $updateSql = "UPDATE users SET 
                                  name = '$safeName',
                                  email = '$safeEmail',
                                  phone = '$safePhone',
                                  address = '$safeAddress',
                                  password = '$hashed'
                                  WHERE id = $userId AND role = 'admin'";
                } else {
                    $updateSql = "UPDATE users SET 
                                  name = '$safeName',
                                  email = '$safeEmail',
                                  phone = '$safePhone',
                                  address = '$safeAddress'
                                  WHERE id = $userId AND role = 'admin'";
                }

                if (mysqli_query($conn, $updateSql)) {
                    $successMsg = "Staff profile for '$safeName' updated successfully.";
                } else {
                    $errorMsg = "Error updating staff profile: " . mysqli_error($conn);
                }
            }
        } else {
            // New Staff User
            if (empty($password)) {
                $errorMsg = "Please provide an initial password for the new staff member.";
            } else {
                $dupCheck = mysqli_query($conn, "SELECT id FROM users WHERE email = '$safeEmail'");
                if (mysqli_num_rows($dupCheck) > 0) {
                    $errorMsg = "An account with email '$safeEmail' already exists.";
                } else {
                    $hashed = password_hash($password, PASSWORD_DEFAULT);
                    $insertSql = "INSERT INTO users (name, email, password, phone, address, role)
                                  VALUES ('$safeName', '$safeEmail', '$hashed', '$safePhone', '$safeAddress', 'admin')";
                    if (mysqli_query($conn, $insertSql)) {
                        $successMsg = "New staff administrator '$safeName' registered successfully.";
                    } else {
                        $errorMsg = "Error adding staff member: " . mysqli_error($conn);
                    }
                }
            }
        }
    }
}

// 3. Fetch all Admin / Staff Users
$staffQuery = mysqli_query($conn, "SELECT * FROM users WHERE role = 'admin' ORDER BY id ASC");
$staffList = [];
$totalStaff = 0;

if ($staffQuery) {
    while ($s = mysqli_fetch_assoc($staffQuery)) {
        $totalStaff++;
        $staffList[] = $s;
    }
}

$pageTitle = 'Staff & System Administrators | MediQuick Admin';
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
          <i class="bi bi-shield-lock-fill text-emerald me-1"></i> <span class="d-none d-sm-inline">System Administration &mdash; </span>Staff & Administrators
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
          <h3 class="fw-bold text-dark mb-1 font-heading">Staff & System Administrators</h3>
          <p class="text-muted small mb-0">Manage authorized duty pharmacists, dispensing officers, and administrative credentials</p>
        </div>
        <button type="button" class="btn btn-emerald btn-sm py-2 px-3 shadow-sm rounded-3 d-inline-flex align-items-center gap-1.5 fw-semibold" 
                data-bs-toggle="modal" data-bs-target="#userModal" onclick="resetStaffForm()">
          <i class="bi bi-person-plus-fill"></i> Add New Staff Member
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
              <div class="text-muted small fw-semibold mb-1">Active Staff Users</div>
              <h3 class="fw-bold mb-0 text-dark"><?php echo $totalStaff; ?></h3>
            </div>
            <div class="rounded-3 d-flex align-items-center justify-content-center text-primary" style="width: 46px; height: 46px; background-color: #e0f2fe;">
              <i class="bi bi-person-badge fs-4"></i>
            </div>
          </div>
        </div>

        <div class="col-6 col-lg-3">
          <div class="stat-card-modern d-flex align-items-center justify-content-between" style="border-left: 4px solid #059669;">
            <div>
              <div class="text-muted small fw-semibold mb-1">Duty Pharmacists</div>
              <h3 class="fw-bold mb-0 text-emerald"><?php echo $totalStaff; ?></h3>
            </div>
            <div class="rounded-3 d-flex align-items-center justify-content-center text-emerald" style="width: 46px; height: 46px; background-color: #ecfdf5;">
              <i class="bi bi-shield-check fs-4"></i>
            </div>
          </div>
        </div>

        <div class="col-6 col-lg-3">
          <div class="stat-card-modern d-flex align-items-center justify-content-between" style="border-left: 4px solid #f59e0b;">
            <div>
              <div class="text-muted small fw-semibold mb-1">Access Level</div>
              <h3 class="fw-bold mb-0 text-warning" style="font-size: 1.25rem;">Full Admin</h3>
            </div>
            <div class="rounded-3 d-flex align-items-center justify-content-center text-warning" style="width: 46px; height: 46px; background-color: #fef3c7;">
              <i class="bi bi-key-fill fs-4"></i>
            </div>
          </div>
        </div>

        <div class="col-6 col-lg-3">
          <div class="stat-card-modern d-flex align-items-center justify-content-between" style="border-left: 4px solid #8b5cf6;">
            <div>
              <div class="text-muted small fw-semibold mb-1">Security Standard</div>
              <h3 class="fw-bold mb-0" style="color: #7c3aed; font-size: 1.25rem;">BCrypt 256</h3>
            </div>
            <div class="rounded-3 d-flex align-items-center justify-content-center" style="width: 46px; height: 46px; background-color: #f5f3ff; color: #7c3aed;">
              <i class="bi bi-lock-fill fs-4"></i>
            </div>
          </div>
        </div>
      </div>

      <!-- Main Card with Toolbar & Table -->
      <div class="card card-custom bg-white border-0 shadow-sm overflow-hidden">
        
        <!-- Filter & Search Toolbar -->
        <div class="p-3 border-bottom bg-light d-flex flex-wrap align-items-center justify-content-between gap-3">
          <div class="fw-bold text-dark small d-flex align-items-center gap-1.5">
            <i class="bi bi-shield-shaded text-emerald"></i>
            Staff Directory (<?php echo count($staffList); ?>)
          </div>

          <!-- Instant Live Search Input -->
          <div class="position-relative" style="min-width: 280px;">
            <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
            <input type="text" id="staffLiveSearch" class="form-control form-control-sm ps-5 rounded-pill" placeholder="Search by name, email, or role...">
          </div>
        </div>

        <!-- Staff Table -->
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0" id="staffTable">
            <thead class="bg-light text-secondary" style="font-size: 0.76rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; border-bottom: 2px solid #e2e8f0;">
              <tr>
                <th class="ps-4 py-3.5" style="width: 80px;">ID</th>
                <th style="min-width: 240px;">Staff Member</th>
                <th style="min-width: 240px;">Contact Details</th>
                <th style="min-width: 170px;">Role & Clearance</th>
                <th style="min-width: 150px;">Account Created</th>
                <th class="text-end pe-4" style="width: 130px;">Actions</th>
              </tr>
            </thead>
            <tbody class="small">
              <?php if (!empty($staffList)): ?>
                <?php foreach ($staffList as $s): 
                  $nameParts = explode(' ', trim($s['name']));
                  $initials = strtoupper(substr($nameParts[0], 0, 1) . (isset($nameParts[1]) ? substr($nameParts[1], 0, 1) : ''));
                  $isSelf = ((int)$s['id'] === $currentAdminId);
                ?>
                  <tr class="staff-row-item" style="transition: background-color 0.15s ease;">
                    <!-- ID -->
                    <td class="ps-4 py-3.5 font-mono text-muted">
                      #A-<?php echo str_pad($s['id'], 3, '0', STR_PAD_LEFT); ?>
                    </td>

                    <!-- Staff Profile -->
                    <td class="py-3.5">
                      <div class="d-flex align-items-center gap-3">
                        <div class="avatar-initials shadow-xs" style="width: 42px; height: 42px; font-size: 0.9rem; flex-shrink: 0; background: linear-gradient(135deg, #0f172a 0%, #334155 100%);">
                          <?php echo htmlspecialchars($initials); ?>
                        </div>
                        <div>
                          <strong class="text-dark d-block staff-name" style="font-size: 0.92rem;">
                            <?php echo htmlspecialchars($s['name']); ?>
                            <?php if ($isSelf): ?>
                              <span class="badge bg-emerald-subtle text-emerald border border-emerald ms-1" style="font-size: 0.65rem;">You (Active)</span>
                            <?php endif; ?>
                          </strong>
                          <span class="text-muted" style="font-size: 0.75rem;">
                            <i class="bi bi-shield-check text-emerald me-1"></i> Verified Staff Pharmacist
                          </span>
                        </div>
                      </div>
                    </td>

                    <!-- Contact Details -->
                    <td class="py-3.5">
                      <div class="d-flex flex-column gap-1">
                        <a href="mailto:<?php echo htmlspecialchars($s['email']); ?>" class="text-dark text-decoration-none d-inline-flex align-items-center gap-1 staff-email">
                          <i class="bi bi-envelope-fill text-emerald" style="font-size: 0.8rem;"></i> <?php echo htmlspecialchars($s['email']); ?>
                        </a>
                        <?php if (!empty($s['phone'])): ?>
                          <div class="text-secondary d-inline-flex align-items-center gap-1" style="font-size: 0.78rem;">
                            <i class="bi bi-telephone-fill text-muted" style="font-size: 0.72rem;"></i> <?php echo htmlspecialchars($s['phone']); ?>
                          </div>
                        <?php endif; ?>
                      </div>
                    </td>

                    <!-- Role Badge -->
                    <td class="py-3.5">
                      <span class="badge bg-dark text-white font-mono px-2.5 py-1 rounded-pill" style="background-color: #0f172a !important;">
                        <i class="bi bi-shield-fill text-emerald me-1"></i> Administrator
                      </span>
                    </td>

                    <!-- Created Date -->
                    <td class="py-3.5 text-muted" style="font-size: 0.78rem;">
                      <i class="bi bi-calendar3 me-1"></i> <?php echo date('M d, Y', strtotime($s['created_at'])); ?>
                    </td>

                    <!-- Actions -->
                    <td class="text-end pe-4 py-3.5">
                      <div class="d-inline-flex align-items-center gap-1">
                        <button type="button" class="btn btn-sm btn-outline-primary p-1.5 rounded-3" 
                                onclick='editStaff(<?php echo htmlspecialchars(json_encode($s), ENT_QUOTES, 'UTF-8'); ?>)' title="Edit Staff Profile">
                          <i class="bi bi-pencil"></i>
                        </button>
                        <?php if (!$isSelf): ?>
                          <a href="users.php?action=delete&id=<?php echo $s['id']; ?>" class="btn btn-sm btn-outline-danger p-1.5 rounded-3" 
                             onclick="return confirmAction('Are you sure you want to remove administrator <?php echo htmlspecialchars(addslashes($s['name'])); ?>?', 'Delete Staff Account?');" title="Delete Staff Account">
                            <i class="bi bi-trash"></i>
                          </a>
                        <?php else: ?>
                          <button class="btn btn-sm btn-light text-muted p-1.5 rounded-3" disabled title="Cannot delete active session account">
                            <i class="bi bi-shield-slash"></i>
                          </button>
                        <?php endif; ?>
                      </div>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr>
                  <td colspan="6" class="text-center text-muted py-5">
                    <i class="bi bi-shield-slash fs-1 d-block mb-2 text-secondary"></i>
                    No staff accounts found.
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
<!-- ADD / EDIT STAFF MODAL -->
<!-- ========================================================================= -->
<div class="modal fade" id="userModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-md modal-dialog-centered modal-dialog-scrollable">
    <form action="users.php" method="POST" class="modal-content border-0 shadow-lg" style="border-radius: 20px; overflow: hidden; max-height: 90vh;">
      
      <!-- Modal Header -->
      <div class="modal-header bg-dark text-white py-3 px-4 flex-shrink-0" style="background-color: #0f172a !important;">
        <h5 class="modal-title font-heading fw-bold mb-0" id="staffModalTitle">
          <i class="bi bi-person-plus-fill text-emerald me-2"></i> Add Staff Member
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <!-- Modal Body -->
      <div class="modal-body p-4" style="overflow-y: auto;">
        <input type="hidden" name="user_id" id="sId" value="0">

        <div class="mb-3">
          <label for="sName" class="form-label small fw-bold text-dark">Staff Full Name <span class="text-danger">*</span></label>
          <input type="text" id="sName" name="name" class="form-control" required placeholder="e.g. Dr. Kasun Weerasinghe">
        </div>

        <div class="mb-3">
          <label for="sEmail" class="form-label small fw-bold text-dark">Email Address <span class="text-danger">*</span></label>
          <input type="email" id="sEmail" name="email" class="form-control" required placeholder="e.g. kasun.w@mediquick.lk">
        </div>

        <div class="row g-3 mb-3">
          <div class="col-md-6">
            <label for="sPhone" class="form-label small fw-bold text-dark">Contact Phone</label>
            <input type="tel" id="sPhone" name="phone" class="form-control" placeholder="e.g. +94 77 234 5678">
          </div>
          <div class="col-md-6">
            <label for="sRole" class="form-label small fw-bold text-dark">Role & Permission</label>
            <select id="sRole" name="role" class="form-select" disabled>
              <option value="admin" selected>System Administrator / Pharmacist</option>
            </select>
          </div>
        </div>

        <div class="mb-3">
          <label for="sAddress" class="form-label small fw-bold text-dark">Branch / Delivery Location</label>
          <input type="text" id="sAddress" name="address" class="form-control" placeholder="e.g. MediQuick Central Pharmacy, Kurunegala">
        </div>

        <div class="mb-3">
          <label for="sPassword" class="form-label small fw-bold text-dark" id="sPassLabel">
            Password <span class="text-danger" id="sPassReq">*</span>
          </label>
          <input type="password" id="sPassword" name="password" class="form-control" placeholder="Enter secure account password">
          <small class="text-muted d-block mt-1" id="sPassHelp" style="font-size: 0.74rem;">
            Must be at least 6 characters. When editing, leave blank to keep existing password.
          </small>
        </div>

      </div>

      <!-- Modal Footer -->
      <div class="modal-footer bg-light py-2.5 px-4 flex-shrink-0">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-emerald px-4 fw-bold">
          <i class="bi bi-save2 me-1"></i> Save Staff Account
        </button>
      </div>

    </form>
  </div>
</div>

<script>
function resetStaffForm() {
  document.getElementById('staffModalTitle').innerHTML = '<i class="bi bi-person-plus-fill text-emerald me-2"></i> Add Staff Member';
  document.getElementById('sId').value = '0';
  document.getElementById('sName').value = '';
  document.getElementById('sEmail').value = '';
  document.getElementById('sPhone').value = '';
  document.getElementById('sAddress').value = 'MediQuick Central Pharmacy, Kurunegala';
  document.getElementById('sPassword').value = '';
  document.getElementById('sPassword').required = true;
  document.getElementById('sPassReq').style.display = 'inline';
  document.getElementById('sPassHelp').textContent = 'Must be at least 6 characters.';
}

function editStaff(s) {
  document.getElementById('staffModalTitle').innerHTML = '<i class="bi bi-pencil-square text-primary me-2"></i> Edit Staff Member #' + s.id;
  document.getElementById('sId').value = s.id;
  document.getElementById('sName').value = s.name || '';
  document.getElementById('sEmail').value = s.email || '';
  document.getElementById('sPhone').value = s.phone || '';
  document.getElementById('sAddress').value = s.address || '';
  document.getElementById('sPassword').value = '';
  document.getElementById('sPassword').required = false;
  document.getElementById('sPassReq').style.display = 'none';
  document.getElementById('sPassHelp').textContent = 'Leave empty to keep the existing password unchanged.';

  const modal = new bootstrap.Modal(document.getElementById('userModal'));
  modal.show();
}

// Live Search
document.addEventListener('DOMContentLoaded', function() {
  const searchInput = document.getElementById('staffLiveSearch');
  if (searchInput) {
    searchInput.addEventListener('input', function() {
      const query = this.value.toLowerCase().trim();
      const rows = document.querySelectorAll('.staff-row-item');

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
