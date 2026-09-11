<?php
/**
 * MediQuick Pharmacy - Order Management
 * University 1st-Year Web Application (PHP & MySQL Admin Portal)
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

// 1. Handle Status Update POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $orderId = (int)$_POST['order_id'];
    $newStatus = trim($_POST['status'] ?? 'Pending');

    $allowedStatuses = ['Pending', 'Processing', 'Delivered', 'Cancelled'];
    if (in_array($newStatus, $allowedStatuses) && $orderId > 0) {
        $safeStatus = mysqli_real_escape_string($conn, $newStatus);
        $updateSql = "UPDATE orders SET status = '$safeStatus' WHERE id = $orderId";
        if (mysqli_query($conn, $updateSql)) {
            $successMsg = "Order #MQ-" . str_pad($orderId, 5, '0', STR_PAD_LEFT) . " status updated to $newStatus.";
        } else {
            $errorMsg = "Failed to update order status: " . mysqli_error($conn);
        }
    }
}

// 2. Fetch all orders with item count
$ordersQuery = mysqli_query($conn, "SELECT o.*, COUNT(oi.id) as total_items 
                                    FROM orders o 
                                    LEFT JOIN order_items oi ON o.id = oi.order_id 
                                    GROUP BY o.id 
                                    ORDER BY o.id DESC");

$pageTitle = 'Manage Orders | MediQuick Admin';
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
          <i class="bi bi-bag-check-fill text-emerald me-1"></i> <span class="d-none d-sm-inline">Fulfillment &mdash; </span>Orders
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

    <div class="p-4 flex-grow-1">
      
      <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
          <h3 class="fw-bold mb-0">Customer Orders Management</h3>
          <p class="text-muted small mb-0">Track customer orders, verify dispatch, and update shipping fulfillment statuses</p>
        </div>
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

    <!-- Orders Table -->
    <div class="card card-custom p-3 bg-white">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0 small">
          <thead class="table-light">
            <tr>
              <th>Order Ref</th>
              <th>Date</th>
              <th>Customer Name</th>
              <th>Phone</th>
              <th>Address / City</th>
              <th>Total (LKR)</th>
              <th>Payment</th>
              <th>Fulfillment Status</th>
              <th class="text-end">Action</th>
            </tr>
          </thead>
          <tbody>
            <?php if ($ordersQuery && mysqli_num_rows($ordersQuery) > 0): ?>
              <?php while ($ord = mysqli_fetch_assoc($ordersQuery)): ?>
                <tr>
                  <td class="font-mono fw-bold">#MQ-<?php echo str_pad($ord['id'], 5, '0', STR_PAD_LEFT); ?></td>
                  <td class="text-secondary"><?php echo date('M d, Y', strtotime($ord['created_at'])); ?></td>
                  <td><strong><?php echo htmlspecialchars($ord['customer_name']); ?></strong></td>
                  <td><?php echo htmlspecialchars($ord['phone']); ?></td>
                  <td><?php echo htmlspecialchars($ord['delivery_address']); ?>, <?php echo htmlspecialchars($ord['city']); ?></td>
                  <td class="fw-bold text-emerald"><?php echo formatLKR($ord['total_amount']); ?></td>
                  <td>
                    <div><?php echo htmlspecialchars($ord['payment_method']); ?></div>
                    <?php if (($ord['payment_status'] ?? '') === 'Paid'): ?>
                      <span class="badge bg-success-subtle text-success border border-success-subtle" style="font-size: 0.68rem;">
                        <i class="bi bi-check-circle-fill me-1"></i> Paid
                      </span>
                    <?php else: ?>
                      <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle" style="font-size: 0.68rem;">
                        <i class="bi bi-clock me-1"></i> Pending Payment
                      </span>
                    <?php endif; ?>
                    <?php if (!empty($ord['payhere_payment_id'])): ?>
                      <div class="text-muted font-monospace" style="font-size: 0.68rem;"><?php echo htmlspecialchars($ord['payhere_payment_id']); ?></div>
                    <?php endif; ?>
                  </td>
                  <td>
                    <!-- Status Form Inline -->
                    <form action="orders.php" method="POST" class="d-flex align-items-center gap-1">
                      <input type="hidden" name="update_status" value="1">
                      <input type="hidden" name="order_id" value="<?php echo $ord['id']; ?>">
                      
                      <select name="status" class="form-select form-select-sm" style="width: 125px; font-size: 0.78rem;">
                        <option value="Pending" <?php echo ($ord['status'] === 'Pending') ? 'selected' : ''; ?>>Pending</option>
                        <option value="Processing" <?php echo ($ord['status'] === 'Processing') ? 'selected' : ''; ?>>Processing</option>
                        <option value="Delivered" <?php echo ($ord['status'] === 'Delivered') ? 'selected' : ''; ?>>Delivered</option>
                        <option value="Cancelled" <?php echo ($ord['status'] === 'Cancelled') ? 'selected' : ''; ?>>Cancelled</option>
                      </select>

                      <button type="submit" class="btn btn-sm btn-outline-secondary" title="Save Status">
                        <i class="bi bi-check2"></i>
                      </button>
                    </form>
                  </td>
                  <td class="text-end">
                    <a href="../order-success.php?order_id=<?php echo $ord['id']; ?>" target="_blank" class="btn btn-sm btn-outline-primary" title="View Customer Invoice">
                      <i class="bi bi-receipt"></i>
                    </a>
                  </td>
                </tr>
              <?php endwhile; ?>
            <?php else: ?>
              <tr><td colspan="9" class="text-center text-muted py-4">No customer orders placed yet.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- Bootstrap Bundle & JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/main.js"></script>
</body>
</html>
