<?php
/**
 * MediQuick Pharmacy - User Registration Page
 * University 1st-Year Web Application (PHP & MySQL)
 */
require_once __DIR__ . '/config/db.php';

if (isLoggedIn()) {
    header("Location: index.php");
    exit();
}

$errorMsg = '';
$successMsg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if (empty($name) || empty($email) || empty($password)) {
        $errorMsg = 'Please provide your full name, email, and a secure password.';
    } elseif ($password !== $confirmPassword) {
        $errorMsg = 'Passwords do not match. Please re-enter.';
    } elseif (strlen($password) < 6) {
        $errorMsg = 'Password must be at least 6 characters long.';
    } else {
        $safeEmail = mysqli_real_escape_string($conn, $email);
        $checkQuery = mysqli_query($conn, "SELECT id FROM users WHERE email = '$safeEmail' LIMIT 1");

        if ($checkQuery && mysqli_num_rows($checkQuery) > 0) {
            $errorMsg = 'An account with this email already exists. Please login.';
        } else {
            $safeName = mysqli_real_escape_string($conn, $name);
            $safePhone = mysqli_real_escape_string($conn, $phone);
            $safeAddress = mysqli_real_escape_string($conn, $address);
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            $insertSql = "INSERT INTO users (name, email, password, phone, address, role) 
                          VALUES ('$safeName', '$safeEmail', '$hashedPassword', '$safePhone', '$safeAddress', 'customer')";

            if (mysqli_query($conn, $insertSql)) {
                $newUserId = mysqli_insert_id($conn);

                // Auto login user
                $_SESSION['user_id'] = $newUserId;
                $_SESSION['user_name'] = $name;
                $_SESSION['user_email'] = $email;
                $_SESSION['role'] = 'customer';

                header("Location: index.php");
                exit();
            } else {
                $errorMsg = 'Registration failed due to a database error: ' . mysqli_error($conn);
            }
        }
    }
}

$pageTitle = 'Register Patient Account | MediQuick Pharmacy';
include_once __DIR__ . '/includes/header.php';
include_once __DIR__ . '/includes/navbar.php';
?>

<main class="py-5">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-md-7 col-lg-6">
        
        <div class="card card-custom p-4 p-md-5 bg-white">
          
          <div class="text-center mb-4">
            <div class="rounded-3 d-inline-flex align-items-center justify-content-center text-white p-3 mb-2" style="background-color: #059669;">
              <i class="bi bi-person-plus-fill fs-3"></i>
            </div>
            <h3 class="fw-bold mb-1">Create Account</h3>
            <p class="text-muted small">Join MediQuick for fast medicine dispatch and prescription tracking</p>
          </div>

          <?php if (!empty($errorMsg)): ?>
            <div class="alert alert-danger alert-dismissible fade show small" role="alert">
              <i class="bi bi-exclamation-triangle-fill me-1"></i> <?php echo htmlspecialchars($errorMsg); ?>
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
          <?php endif; ?>

          <form action="register.php" method="POST">
            
            <div class="mb-3">
              <label for="regName" class="form-label small fw-bold">Full Name <span class="text-danger">*</span></label>
              <input type="text" id="regName" name="name" class="form-control" required placeholder="e.g. Kasun Perera" value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>">
            </div>

            <div class="row g-3 mb-3">
              <div class="col-md-6">
                <label for="regEmail" class="form-label small fw-bold">Email Address <span class="text-danger">*</span></label>
                <input type="email" id="regEmail" name="email" class="form-control" required placeholder="e.g. kasun@gmail.com" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
              </div>
              <div class="col-md-6">
                <label for="regPhone" class="form-label small fw-bold">Contact Phone Number</label>
                <input type="tel" id="regPhone" name="phone" class="form-control" placeholder="e.g. +94 77 123 4567" value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>">
              </div>
            </div>

            <div class="mb-3">
              <label for="regAddress" class="form-label small fw-bold">Delivery Address</label>
              <textarea id="regAddress" name="address" class="form-control" rows="2" placeholder="House number, street, city..."><?php echo htmlspecialchars($_POST['address'] ?? ''); ?></textarea>
            </div>

            <div class="row g-3 mb-4">
              <div class="col-md-6">
                <label for="regPass" class="form-label small fw-bold">Create Password <span class="text-danger">*</span></label>
                <input type="password" id="regPass" name="password" class="form-control" required placeholder="Min 6 characters">
              </div>
              <div class="col-md-6">
                <label for="regConfirm" class="form-label small fw-bold">Confirm Password <span class="text-danger">*</span></label>
                <input type="password" id="regConfirm" name="confirm_password" class="form-control" required placeholder="Re-type password">
              </div>
            </div>

            <button type="submit" class="btn btn-emerald w-100 py-2.5 fw-bold mb-3">
              <i class="bi bi-check-circle me-1"></i> Complete Registration
            </button>

            <div class="text-center small text-secondary">
              Already have an account? <a href="login.php" class="text-emerald fw-bold">Sign In here</a>
            </div>
          </form>

        </div>

      </div>
    </div>
  </div>
</main>

<?php include_once __DIR__ . '/includes/footer.php'; ?>
