<?php
/**
 * MediQuick Pharmacy - Contact Us & Customer Inquiry Form
 * Module: CSE4206 - Web Application Development (Kurunegala)
 */
require_once __DIR__ . '/config/db.php';

$successMsg = '';
$errorMsg = '';

// Pre-fill if logged in
$defaultName = '';
$defaultEmail = '';
$defaultPhone = '';

if (isLoggedIn()) {
    $userId = (int)$_SESSION['user_id'];
    $uRes = mysqli_query($conn, "SELECT * FROM users WHERE id = $userId LIMIT 1");
    if ($uRes && $u = mysqli_fetch_assoc($uRes)) {
        $defaultName = $u['name'] ?? '';
        $defaultEmail = $u['email'] ?? '';
        $defaultPhone = $u['phone'] ?? '';
    }
}

// Handle Form POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        $errorMsg = 'Please fill in all required fields (Name, Email, Subject, Message).';
    } else {
        $safeName = mysqli_real_escape_string($conn, $name);
        $safeEmail = mysqli_real_escape_string($conn, $email);
        $safePhone = mysqli_real_escape_string($conn, $phone);
        $safeSubject = mysqli_real_escape_string($conn, $subject);
        $safeMessage = mysqli_real_escape_string($conn, $message);

        $sql = "INSERT INTO inquiries (name, email, phone, subject, message, status) 
                VALUES ('$safeName', '$safeEmail', '$safePhone', '$safeSubject', '$safeMessage', 'New')";

        if (mysqli_query($conn, $sql)) {
            $successMsg = 'Thank you for reaching out! Your message has been received by our pharmacy staff in Kurunegala. We will contact you shortly.';
        } else {
            $errorMsg = 'Failed to submit your inquiry: ' . mysqli_error($conn);
        }
    }
}

$pageTitle = 'Contact Us | MediQuick Pharmacy Kurunegala';
include_once __DIR__ . '/includes/header.php';
include_once __DIR__ . '/includes/navbar.php';
?>

<main class="py-5">
  <div class="container">
    
    <div class="text-center max-w-7xl mx-auto mb-5">
      <h1 class="display-5 fw-bold mb-2">Get in Touch with <span class="text-emerald">MediQuick</span></h1>
      <p class="text-muted">Have inquiries regarding prescriptions, dosage instructions, or delivery? Reach out to our pharmacy team.</p>
    </div>

    <div class="row g-4 justify-content-center">
      
      <!-- Contact Information Cards (Left) -->
      <div class="col-lg-5">
        
        <div class="card card-custom p-4 bg-white mb-3">
          <div class="d-flex align-items-start gap-3">
            <div class="rounded-3 bg-emerald-subtle text-emerald p-3">
              <i class="bi bi-geo-alt-fill fs-4"></i>
            </div>
            <div>
              <h6 class="fw-bold mb-1">Kurunegala Dispensary</h6>
              <p class="text-secondary small mb-0">No. 45, Colombo Road, Kurunegala, North Western Province, Sri Lanka</p>
            </div>
          </div>
        </div>

        <div class="card card-custom p-4 bg-white mb-3">
          <div class="d-flex align-items-start gap-3">
            <div class="rounded-3 bg-primary-subtle text-primary p-3">
              <i class="bi bi-telephone-fill fs-4"></i>
            </div>
            <div>
              <h6 class="fw-bold mb-1">Pharmacist Helpline</h6>
              <p class="text-secondary small mb-1">Landline: <strong>+94 37 222 3456</strong></p>
              <p class="text-secondary small mb-0">Direct Mobile / WhatsApp: <strong>+94 77 123 4567</strong></p>
            </div>
          </div>
        </div>

        <div class="card card-custom p-4 bg-white mb-3">
          <div class="d-flex align-items-start gap-3">
            <div class="rounded-3 bg-warning-subtle text-warning p-3">
              <i class="bi bi-envelope-fill fs-4"></i>
            </div>
            <div>
              <h6 class="fw-bold mb-1">Email Inquiries</h6>
              <p class="text-secondary small mb-0">support@mediquick.lk / kurunegala@mediquick.lk</p>
            </div>
          </div>
        </div>

        <div class="card card-custom p-4 bg-white">
          <div class="d-flex align-items-start gap-3">
            <div class="rounded-3 bg-success-subtle text-success p-3">
              <i class="bi bi-clock-fill fs-4"></i>
            </div>
            <div>
              <h6 class="fw-bold mb-1">Operational Hours</h6>
              <p class="text-secondary small mb-0">Physical Counter & Online Orders: <strong>24 Hours / 7 Days</strong></p>
            </div>
          </div>
        </div>

      </div>

      <!-- Contact / Inquiry Form (Right) -->
      <div class="col-lg-7">
        <div class="card card-custom p-4 p-md-5 bg-white">
          <h4 class="fw-bold mb-1"><i class="bi bi-chat-left-dots text-emerald me-2"></i> Send us a Message</h4>
          <p class="text-muted small mb-4">Our duty pharmacist will respond to your inquiry via phone or email.</p>

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

          <form action="contact.php" method="POST">
            
            <div class="row g-3 mb-3">
              <div class="col-md-6">
                <label for="cName" class="form-label small fw-bold">Your Full Name <span class="text-danger">*</span></label>
                <input type="text" id="cName" name="name" class="form-control" required placeholder="e.g. Samantha Bandara" value="<?php echo htmlspecialchars($defaultName); ?>">
              </div>
              <div class="col-md-6">
                <label for="cEmail" class="form-label small fw-bold">Email Address <span class="text-danger">*</span></label>
                <input type="email" id="cEmail" name="email" class="form-control" required placeholder="e.g. samantha@gmail.com" value="<?php echo htmlspecialchars($defaultEmail); ?>">
              </div>
            </div>

            <div class="row g-3 mb-3">
              <div class="col-md-6">
                <label for="cPhone" class="form-label small fw-bold">Phone Number</label>
                <input type="tel" id="cPhone" name="phone" class="form-control" placeholder="e.g. +94 77 123 4567" value="<?php echo htmlspecialchars($defaultPhone); ?>">
              </div>
              <div class="col-md-6">
                <label for="cSubject" class="form-label small fw-bold">Inquiry Subject <span class="text-danger">*</span></label>
                <input type="text" id="cSubject" name="subject" class="form-control" required placeholder="e.g. Prescription Inquiry / Delivery Zone">
              </div>
            </div>

            <div class="mb-4">
              <label for="cMessage" class="form-label small fw-bold">Message Details <span class="text-danger">*</span></label>
              <textarea id="cMessage" name="message" class="form-control" rows="4" required placeholder="Please describe your medicine or consultation inquiry here..."></textarea>
            </div>

            <button type="submit" class="btn btn-emerald w-100 py-2.5 fw-bold">
              <i class="bi bi-send-fill me-1"></i> Submit Message
            </button>
          </form>

        </div>
      </div>

    </div>

  </div>
</main>

<?php include_once __DIR__ . '/includes/footer.php'; ?>
