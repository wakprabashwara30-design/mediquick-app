<?php
/**
 * MediQuick Pharmacy - Checkout Page
 * University 1st-Year Web Application (PHP & MySQL)
 */
require_once __DIR__ . '/config/db.php';

// Check if user is logged in (Mandatory Authentication for Pharmacy Checkout)
if (!isLoggedIn()) {
    $_SESSION['login_notice'] = "Please log in or create an account to complete your pharmacy order and track delivery.";
    header("Location: login.php?redirect=checkout.php");
    exit();
}

// Check if cart is empty
if (!isset($_SESSION['cart']) || count($_SESSION['cart']) === 0) {
    header("Location: cart.php");
    exit();
}

// Fetch Cart Products and calculate total
$productIds = array_keys($_SESSION['cart']);
$cleanIds = array_map('intval', $productIds);
$idsString = implode(',', $cleanIds);

$cartQuery = "SELECT * FROM products WHERE id IN ($idsString)";
$cartResult = mysqli_query($conn, $cartQuery);

$cartItems = [];
$subtotal = 0;

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
            'quantity' => $itemQty,
            'total' => $itemTotal
        ];
    }
}

$deliveryFee = ($subtotal > 0 && $subtotal < 3000) ? 250.00 : 0.00;
$grandTotal = $subtotal + $deliveryFee;

// Pre-fill user data from database for logged in user
$userId = (int)$_SESSION['user_id'];
$defaultName = $_SESSION['user_name'] ?? '';
$defaultEmail = $_SESSION['user_email'] ?? '';
$defaultPhone = '';
$defaultAddress = '';

$userQuery = mysqli_query($conn, "SELECT * FROM users WHERE id = $userId LIMIT 1");
if ($userQuery && $u = mysqli_fetch_assoc($userQuery)) {
    $defaultName = !empty($u['name']) ? $u['name'] : $defaultName;
    $defaultEmail = !empty($u['email']) ? $u['email'] : $defaultEmail;
    $defaultPhone = $u['phone'] ?? '';
    $defaultAddress = $u['address'] ?? '';
}

$errorMsg = '';

// Handle Order Placement POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['customer_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $address = trim($_POST['delivery_address'] ?? '');
    $city = trim($_POST['city'] ?? 'Colombo');
    $paymentMethod = trim($_POST['payment_method'] ?? 'Cash on Delivery');
    $userIdVal = (int)$_SESSION['user_id'];

    if (empty($name) || empty($phone) || empty($address)) {
        $errorMsg = 'Please fill in all required delivery fields (Name, Phone, Address).';
    } else {
        // Sanitize for SQL
        $safeName = mysqli_real_escape_string($conn, $name);
        $safePhone = mysqli_real_escape_string($conn, $phone);
        $safeEmail = mysqli_real_escape_string($conn, $email);
        $safeAddress = mysqli_real_escape_string($conn, $address);
        $safeCity = mysqli_real_escape_string($conn, $city);
        $safePayment = mysqli_real_escape_string($conn, $paymentMethod);

        // 1. Insert into orders table
        $orderSql = "INSERT INTO orders (user_id, customer_name, phone, email, delivery_address, city, total_amount, payment_method, status) 
                     VALUES ($userIdVal, '$safeName', '$safePhone', '$safeEmail', '$safeAddress', '$safeCity', $grandTotal, '$safePayment', 'Pending')";

        if (mysqli_query($conn, $orderSql)) {
            $orderId = mysqli_insert_id($conn);

            // 2. Insert into order_items table
            foreach ($cartItems as $item) {
                $pId = (int)$item['id'];
                $pName = mysqli_real_escape_string($conn, $item['name']);
                $uPrice = (float)$item['price'];
                $qty = (int)$item['quantity'];
                $iTotal = (float)$item['total'];

                $itemSql = "INSERT INTO order_items (order_id, product_id, product_name, unit_price, quantity, total_price) 
                            VALUES ($orderId, $pId, '$pName', $uPrice, $qty, $iTotal)";
                mysqli_query($conn, $itemSql);
            }

            // 3. Clear Shopping Cart
            $_SESSION['cart'] = [];

            // 4. Redirect to Order Confirmation
            header("Location: order-success.php?order_id=$orderId");
            exit();
        } else {
            $errorMsg = 'Failed to place order. Database error: ' . mysqli_error($conn);
        }
    }
}

$pageTitle = 'Checkout | MediQuick Pharmacy';
include_once __DIR__ . '/includes/header.php';
include_once __DIR__ . '/includes/navbar.php';
?>

<main class="py-4">
  <div class="container">
    
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
      <ol class="breadcrumb small">
        <li class="breadcrumb-item"><a href="index.php" class="text-emerald">Home</a></li>
        <li class="breadcrumb-item"><a href="cart.php" class="text-emerald">Cart</a></li>
        <li class="breadcrumb-item active" aria-current="page">Checkout</li>
      </ol>
    </nav>

    <h2 class="fw-bold mb-4">Complete Your Order</h2>

    <?php if (!empty($errorMsg)): ?>
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i> <?php echo htmlspecialchars($errorMsg); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    <?php endif; ?>

    <div class="row g-4">
      
      <!-- Checkout Form -->
      <div class="col-lg-7">
        <div class="card card-custom p-4 bg-white">
          <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
            <h5 class="fw-bold mb-0">
              <i class="bi bi-geo-alt me-1 text-emerald"></i> Delivery & Contact Details
            </h5>
            <span class="badge bg-emerald-subtle text-emerald border border-emerald rounded-pill small">
              <i class="bi bi-person-check-fill me-1"></i> <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Verified Account'); ?>
            </span>
          </div>

          <form action="checkout.php" method="POST">
            
            <div class="mb-3">
              <label for="cName" class="form-label small fw-bold">Full Name <span class="text-danger">*</span></label>
              <input type="text" id="cName" name="customer_name" class="form-control" required value="<?php echo htmlspecialchars($defaultName); ?>" placeholder="e.g. Kasun Perera">
            </div>

            <div class="row g-3 mb-3">
              <div class="col-md-6">
                <label for="cPhone" class="form-label small fw-bold">Phone Number <span class="text-danger">*</span></label>
                <input type="tel" id="cPhone" name="phone" class="form-control" required value="<?php echo htmlspecialchars($defaultPhone); ?>" placeholder="e.g. +94 77 123 4567">
              </div>
              <div class="col-md-6">
                <label for="cEmail" class="form-label small fw-bold">Email Address (Optional)</label>
                <input type="email" id="cEmail" name="email" class="form-control" value="<?php echo htmlspecialchars($defaultEmail); ?>" placeholder="e.g. kasun@gmail.com">
              </div>
            </div>

            <div class="mb-3">
              <label for="cAddress" class="form-label small fw-bold">Delivery Street Address <span class="text-danger">*</span></label>
              <textarea id="cAddress" name="delivery_address" class="form-control" rows="3" required placeholder="House number, street name, apartment details..."><?php echo htmlspecialchars($defaultAddress); ?></textarea>
            </div>

            <div class="mb-4">
              <label for="cCity" class="form-label small fw-bold">City / Area</label>
              <input type="text" id="cCity" name="city" class="form-control" value="Colombo" placeholder="e.g. Colombo 03 / Nugegoda / Rajagiriya">
            </div>

            <h5 class="fw-bold mb-3 border-bottom pb-2">
              <i class="bi bi-credit-card me-1 text-emerald"></i> Payment Method
            </h5>

            <div class="mb-4">
              <!-- Cash on Delivery -->
              <div class="form-check p-3 rounded-3 border mb-2 cursor-pointer" style="background-color: #f8fafc;" onclick="document.getElementById('payCod').checked = true; togglePaymentUi();">
                <input class="form-check-input mt-1" type="radio" name="payment_method" id="payCod" value="Cash on Delivery" checked onchange="togglePaymentUi()">
                <label class="form-check-label fw-semibold text-dark w-100 ps-1" for="payCod">
                  <div class="d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-cash-stack text-success me-1.5 fs-5"></i> Cash on Delivery (COD)</span>
                    <span class="badge bg-secondary-subtle text-secondary border rounded-pill">Pay at Doorstep</span>
                  </div>
                  <div class="text-muted small fw-normal mt-1">Pay with cash directly to our pharmacy delivery courier upon receiving your parcel.</div>
                </label>
              </div>

              <!-- PayHere Online Payment Gateway -->
              <div class="form-check p-3 rounded-3 border mb-3 cursor-pointer" style="background-color: #f0fdf4; border-color: #a7f3d0 !important;" onclick="document.getElementById('payPayHere').checked = true; togglePaymentUi();">
                <input class="form-check-input mt-1" type="radio" name="payment_method" id="payPayHere" value="PayHere (Online Card / Mobile)" onchange="togglePaymentUi()">
                <label class="form-check-label fw-semibold text-dark w-100 ps-1" for="payPayHere">
                  <div class="d-flex justify-content-between align-items-center flex-wrap gap-1">
                    <span><i class="bi bi-shield-check text-emerald me-1.5 fs-5"></i> PayHere Online Payment <span class="badge bg-emerald text-white rounded-pill ms-1" style="font-size: 0.68rem;"><i class="bi bi-shield-lock-fill me-1"></i>Secure Gateway</span></span>
                    <span class="badge bg-success text-white rounded-pill px-2 py-1" style="font-size: 0.7rem;">CBSL Approved</span>
                  </div>
                  <div class="text-muted small fw-normal mt-1 mb-2">Instant online payment via Visa, MasterCard, AMEX, eZ Cash, mCash, FriMi, and Genie.</div>
                  <div class="d-flex align-items-center gap-2 flex-wrap">
                    <span class="badge bg-white text-dark border px-2 py-1 shadow-2xs fw-bold" style="font-size: 0.72rem;"><i class="bi bi-credit-card text-primary me-1"></i> Visa / Mastercard</span>
                    <span class="badge bg-white text-dark border px-2 py-1 shadow-2xs fw-bold" style="font-size: 0.72rem;"><i class="bi bi-phone text-warning me-1"></i> eZ Cash / mCash</span>
                    <span class="badge bg-white text-dark border px-2 py-1 shadow-2xs fw-bold" style="font-size: 0.72rem;"><i class="bi bi-wallet2 text-danger me-1"></i> FriMi / Genie</span>
                  </div>
                </label>
              </div>
            </div>

            <button type="button" id="submitOrderBtn" onclick="handleOrderPlacement()" class="btn btn-emerald w-100 py-3 fs-6 fw-bold shadow-sm">
              <i class="bi bi-bag-check-fill me-2"></i> <span id="btnSubmitText">Confirm & Place Order (<?php echo formatLKR($grandTotal); ?>)</span>
            </button>
          </form>

        </div>
      </div>

      <!-- Order Items Review (Right) -->
      <div class="col-lg-5">
        <div class="card card-custom p-4 bg-white">
          <h5 class="fw-bold mb-3 border-bottom pb-2">Your Items (<?php echo count($cartItems); ?>)</h5>
          
          <div class="list-group list-group-flush mb-3">
            <?php foreach ($cartItems as $item): ?>
              <div class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                <div>
                  <h6 class="mb-0 small fw-bold text-dark"><?php echo htmlspecialchars($item['name']); ?></h6>
                  <small class="text-muted"><?php echo $item['quantity']; ?> x <?php echo formatLKR($item['price']); ?></small>
                </div>
                <span class="fw-semibold small text-emerald"><?php echo formatLKR($item['total']); ?></span>
              </div>
            <?php endforeach; ?>
          </div>

          <hr class="my-2">

          <div class="d-flex justify-content-between mb-2 small text-secondary">
            <span>Subtotal:</span>
            <span><?php echo formatLKR($subtotal); ?></span>
          </div>

          <div class="d-flex justify-content-between mb-3 small text-secondary">
            <span>Delivery Fee:</span>
            <span><?php echo ($deliveryFee == 0) ? '<strong class="text-success">FREE</strong>' : formatLKR($deliveryFee); ?></span>
          </div>

          <div class="d-flex justify-content-between fs-5 fw-bold text-dark border-top pt-2">
            <span>Total Payable:</span>
            <span class="text-emerald"><?php echo formatLKR($grandTotal); ?></span>
          </div>

          <div class="p-3 mt-4 rounded-3 bg-light small text-secondary">
            <i class="bi bi-info-circle-fill text-emerald me-1"></i>
            By confirming your order, our dispensing pharmacist will review any prescription items before dispatch.
          </div>
        </div>
      </div>

    </div>

  </div>
</main>

<!-- PayHere Official JavaScript SDK -->
<script type="text/javascript" src="https://www.payhere.lk/lib/payhere.js"></script>

<script>
function togglePaymentUi() {
  const isPayHere = document.getElementById('payPayHere').checked;
  const btnText = document.getElementById('btnSubmitText');
  
  if (isPayHere) {
    if (btnText) btnText.innerHTML = 'Pay Online with PayHere (<?php echo formatLKR($grandTotal); ?>)';
  } else {
    if (btnText) btnText.innerHTML = 'Confirm & Place Order (<?php echo formatLKR($grandTotal); ?>)';
  }
}

// PayHere Official SDK Callback Listeners
payhere.onCompleted = function onCompleted(orderId) {
  console.log("PayHere Official Payment completed. OrderID:" + orderId);
  const form = document.querySelector('form[action="checkout.php"]');
  const formData = new FormData(form);
  formData.append('payment_id', orderId || ('PH_OFFICIAL_' + Date.now()));

  fetch('process_card_payment.php', {
    method: 'POST',
    body: formData
  })
  .then(r => r.json())
  .then(data => {
    if (data.status === 'success') {
      window.location.href = 'order-success.php?order_id=' + data.order_id + '&payment_status=success';
    } else {
      window.location.href = 'order-success.php?payment_status=success';
    }
  })
  .catch(() => {
    window.location.href = 'order-success.php?payment_status=success';
  });
};

payhere.onDismissed = function onDismissed() {
  console.log("PayHere official payment dismissed by user.");
  const btn = document.getElementById('submitOrderBtn');
  if (btn) {
    btn.disabled = false;
    btn.innerHTML = '<i class="bi bi-bag-check-fill me-2"></i> <span id="btnSubmitText">Pay Online with PayHere (<?php echo formatLKR($grandTotal); ?>)</span>';
  }
  togglePaymentUi();
};

payhere.onError = function onError(error) {
  console.log("PayHere SDK Error: " + error);
  alert("PayHere Gateway Notification: " + error);
  const btn = document.getElementById('submitOrderBtn');
  if (btn) {
    btn.disabled = false;
    btn.innerHTML = '<i class="bi bi-bag-check-fill me-2"></i> <span id="btnSubmitText">Pay Online with PayHere (<?php echo formatLKR($grandTotal); ?>)</span>';
  }
  togglePaymentUi();
};

function handleOrderPlacement() {
  const form = document.querySelector('form[action="checkout.php"]');
  const name = document.getElementById('cName').value.trim();
  const phone = document.getElementById('cPhone').value.trim();
  const address = document.getElementById('cAddress').value.trim();
  const isPayHere = document.getElementById('payPayHere').checked;

  if (!name || !phone || !address) {
    alert('Please complete all required delivery fields: Full Name, Phone, and Delivery Address.');
    return;
  }

  const btn = document.getElementById('submitOrderBtn');

  if (!isPayHere) {
    // Standard Cash on Delivery Submission
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status"></span> Placing Order...';
    form.submit();
  } else {
    // Official PayHere Online Cloud Gateway
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status"></span> Opening Official PayHere Gateway...';

    const formData = new FormData(form);

    fetch('get_payhere_params.php', {
      method: 'POST',
      body: formData
    })
    .then(res => res.json())
    .then(data => {
      if (data.status === 'success') {
        const payment = {
          "sandbox": data.sandbox,
          "merchant_id": data.merchant_id,
          "return_url": data.return_url,
          "cancel_url": data.cancel_url,
          "notify_url": data.notify_url,
          "order_id": data.order_id,
          "items": data.items,
          "amount": data.amount,
          "currency": data.currency,
          "hash": data.hash,
          "first_name": data.first_name,
          "last_name": data.last_name,
          "email": data.email,
          "phone": data.phone,
          "address": data.address,
          "city": data.city,
          "country": data.country
        };

        // Trigger Official PayHere Cloud Gateway
        payhere.startPayment(payment);
      } else {
        alert(data.message || 'Unable to start PayHere gateway.');
        btn.disabled = false;
        togglePaymentUi();
      }
    })
    .catch(err => {
      console.error(err);
      alert('Network error while connecting to PayHere.');
      btn.disabled = false;
      togglePaymentUi();
    });
  }
}
</script>

<?php include_once __DIR__ . '/includes/footer.php'; ?>

