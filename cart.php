<?php
/**
 * MediQuick Pharmacy - Shopping Cart Page
 * University 1st-Year Web Application (PHP & MySQL Session Cart)
 */
require_once __DIR__ . '/config/db.php';

// Initialize session cart array if not present
if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$action = isset($_GET['action']) ? $_GET['action'] : '';
$productId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$qty = isset($_GET['qty']) ? (int)$_GET['qty'] : 1;
if ($qty < 1) $qty = 1;

// 1. Handle Cart Actions
if ($action === 'add' && $productId > 0) {
    if (isset($_SESSION['cart'][$productId])) {
        $_SESSION['cart'][$productId] += $qty;
    } else {
        $_SESSION['cart'][$productId] = $qty;
    }
    header("Location: cart.php");
    exit();
}

if ($action === 'update' && $productId > 0) {
    $newQty = isset($_POST['qty']) ? (int)$_POST['qty'] : (isset($_GET['qty']) ? (int)$_GET['qty'] : 1);
    if ($newQty > 0) {
        $_SESSION['cart'][$productId] = $newQty;
    } else {
        unset($_SESSION['cart'][$productId]);
    }
    header("Location: cart.php");
    exit();
}

if ($action === 'remove' && $productId > 0) {
    if (isset($_SESSION['cart'][$productId])) {
        unset($_SESSION['cart'][$productId]);
    }
    header("Location: cart.php");
    exit();
}

if ($action === 'clear') {
    $_SESSION['cart'] = [];
    header("Location: cart.php");
    exit();
}

$pageTitle = 'Shopping Cart | MediQuick Pharmacy';
include_once __DIR__ . '/includes/header.php';
include_once __DIR__ . '/includes/navbar.php';

// 2. Fetch cart products details from MySQL
$cartItems = [];
$subtotal = 0;

if (count($_SESSION['cart']) > 0) {
    $productIds = array_keys($_SESSION['cart']);
    // Sanitize integer IDs
    $cleanIds = array_map('intval', $productIds);
    $idsString = implode(',', $cleanIds);

    $cartQuery = "SELECT * FROM products WHERE id IN ($idsString)";
    $cartResult = mysqli_query($conn, $cartQuery);

    if ($cartResult) {
        while ($row = mysqli_fetch_assoc($cartResult)) {
            $pId = $row['id'];
            $itemQty = $_SESSION['cart'][$pId];
            $itemTotal = $row['price'] * $itemQty;
            $subtotal += $itemTotal;

            $cartItems[] = [
                'id' => $row['id'],
                'name' => $row['name'],
                'price' => $row['price'],
                'image' => $row['image'],
                'requires_prescription' => $row['requires_prescription'],
                'quantity' => $itemQty,
                'total' => $itemTotal
            ];
        }
    }
}

// Flat delivery fee (Free if subtotal > 3000)
$deliveryFee = ($subtotal > 0 && $subtotal < 3000) ? 250.00 : 0.00;
$grandTotal = $subtotal + $deliveryFee;
?>

<main class="py-4">
  <div class="container">
    
    <!-- Title -->
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2 class="fw-bold mb-0">Shopping Cart (<?php echo getCartCount(); ?> items)</h2>
      <?php if (count($cartItems) > 0): ?>
        <a href="cart.php?action=clear" class="btn btn-outline-danger btn-sm" onclick="return confirmAction('Are you sure you want to clear your cart?');">
          <i class="bi bi-trash me-1"></i> Clear Cart
        </a>
      <?php endif; ?>
    </div>

    <?php if (count($cartItems) > 0): ?>
      <div class="row g-4">
        
        <!-- Cart Items Table -->
        <div class="col-lg-8">
          <div class="card card-custom p-3 bg-white">
            <div class="table-responsive">
              <table class="table align-middle mb-0">
                <thead class="table-light small">
                  <tr>
                    <th>Product</th>
                    <th>Price</th>
                    <th style="width: 130px;">Quantity</th>
                    <th>Subtotal</th>
                    <th class="text-end">Action</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($cartItems as $item): ?>
                    <tr>
                      <td>
                        <div class="d-flex align-items-center gap-3">
                          <img src="<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" style="width: 55px; height: 55px; object-fit: cover; border-radius: 8px;">
                          <div>
                            <h6 class="fw-bold mb-0" style="font-size: 0.95rem;">
                              <a href="product-details.php?id=<?php echo $item['id']; ?>" class="text-dark">
                                <?php echo htmlspecialchars($item['name']); ?>
                              </a>
                            </h6>
                            <?php if ($item['requires_prescription']): ?>
                              <span class="badge bg-danger-subtle text-danger" style="font-size: 0.7rem;">Rx Required</span>
                            <?php endif; ?>
                          </div>
                        </div>
                      </td>
                      <td class="fw-semibold small">
                        <?php echo formatLKR($item['price']); ?>
                      </td>
                      <td>
                        <form action="cart.php" method="GET" class="d-flex align-items-center gap-1">
                          <input type="hidden" name="action" value="update">
                          <input type="hidden" name="id" value="<?php echo $item['id']; ?>">
                          <input type="number" name="qty" value="<?php echo $item['quantity']; ?>" min="1" max="99" class="form-control form-control-sm text-center" style="width: 60px;">
                          <button type="submit" class="btn btn-sm btn-outline-secondary" title="Update Quantity">
                            <i class="bi bi-arrow-repeat"></i>
                          </button>
                        </form>
                      </td>
                      <td class="fw-bold text-emerald">
                        <?php echo formatLKR($item['total']); ?>
                      </td>
                      <td class="text-end">
                        <a href="cart.php?action=remove&id=<?php echo $item['id']; ?>" class="btn btn-sm btn-outline-danger" title="Remove item" onclick="return confirmAction('Remove this item from your cart?');">
                          <i class="bi bi-x-lg"></i>
                        </a>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          </div>

          <div class="mt-3">
            <a href="shop.php" class="btn btn-soft-emerald btn-sm">
              <i class="bi bi-arrow-left me-1"></i> Continue Browsing Medicines
            </a>
          </div>
        </div>

        <!-- Order Summary Box -->
        <div class="col-lg-4">
          <div class="card card-custom p-4 bg-white">
            <h5 class="fw-bold mb-3 border-bottom pb-2">Order Summary</h5>

            <div class="d-flex justify-content-between mb-2 small text-secondary">
              <span>Items Subtotal:</span>
              <strong class="text-dark"><?php echo formatLKR($subtotal); ?></strong>
            </div>

            <div class="d-flex justify-content-between mb-3 small text-secondary">
              <span>Estimated Delivery:</span>
              <span class="<?php echo ($deliveryFee == 0) ? 'text-success fw-bold' : 'text-dark'; ?>">
                <?php echo ($deliveryFee == 0) ? 'FREE' : formatLKR($deliveryFee); ?>
              </span>
            </div>

            <?php if ($deliveryFee > 0): ?>
              <div class="alert alert-info py-1 px-2 small mb-3" style="font-size: 0.75rem;">
                <i class="bi bi-info-circle me-1"></i> Add items worth <strong><?php echo formatLKR(3000 - $subtotal); ?></strong> more for FREE delivery!
              </div>
            <?php endif; ?>

            <hr class="my-3">

            <div class="d-flex justify-content-between align-items-center mb-4">
              <span class="fs-6 fw-bold">Total Amount:</span>
              <span class="fs-4 fw-bold text-emerald"><?php echo formatLKR($grandTotal); ?></span>
            </div>

            <a href="checkout.php" class="btn btn-emerald w-100 py-2.5 fw-bold">
              Proceed to Checkout <i class="bi bi-arrow-right ms-1"></i>
            </a>

            <div class="text-center mt-3 text-muted small" style="font-size: 0.75rem;">
              <i class="bi bi-shield-check text-success me-1"></i> Safe & Secure Checkout
            </div>
          </div>
        </div>

      </div>

    <?php else: ?>
      <!-- Empty Cart Message -->
      <div class="card card-custom p-5 text-center bg-white">
        <div class="mb-3">
          <i class="bi bi-cart-x display-3 text-muted"></i>
        </div>
        <h4 class="fw-bold">Your Cart is Currently Empty</h4>
        <p class="text-secondary small mb-4">Looks like you haven't added any medicines or healthcare items yet.</p>
        <div>
          <a href="shop.php" class="btn btn-emerald px-4 py-2">
            <i class="bi bi-capsule me-2"></i> Start Shopping Now
          </a>
        </div>
      </div>
    <?php endif; ?>

  </div>
</main>

<?php include_once __DIR__ . '/includes/footer.php'; ?>
