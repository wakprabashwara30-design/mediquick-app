<?php
/**
 * MediQuick Pharmacy - Upload Prescription Page
 * University 1st-Year Web Application (PHP File Upload & MySQL)
 */
require_once __DIR__ . '/config/db.php';

$successMsg = '';
$errorMsg = '';

// Pre-fill if user is logged in
$defaultName = '';
$defaultPhone = '';
$defaultAddress = '';

if (isLoggedIn()) {
    $userId = (int)$_SESSION['user_id'];
    $uRes = mysqli_query($conn, "SELECT * FROM users WHERE id = $userId LIMIT 1");
    if ($uRes && $u = mysqli_fetch_assoc($uRes)) {
        $defaultName = $u['name'] ?? '';
        $defaultPhone = $u['phone'] ?? '';
        $defaultAddress = $u['address'] ?? '';
    }
}

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $patientName = trim($_POST['patient_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['delivery_address'] ?? '');
    $userIdVal = isLoggedIn() ? (int)$_SESSION['user_id'] : "NULL";

    if (empty($patientName) || empty($phone) || empty($address)) {
        $errorMsg = 'Please complete all required contact and delivery fields.';
    } elseif (!isset($_FILES['prescription_image']) || $_FILES['prescription_image']['error'] !== UPLOAD_ERR_OK) {
        $errorMsg = 'Please select a valid image file of your doctor\'s prescription.';
    } else {
        // Handle Base64 Image Conversion
        $file = $_FILES['prescription_image'];
        $fileName = $file['name'];
        $fileTmp = $file['tmp_name'];
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'pdf'];

        if (!in_array($fileExt, $allowedExtensions)) {
            $errorMsg = 'Invalid file type. Allowed formats: JPG, PNG, WEBP, or PDF.';
        } else {
            // Determine MIME type
            $mimeType = function_exists('mime_content_type') ? mime_content_type($fileTmp) : '';
            if (empty($mimeType) || $mimeType === 'application/octet-stream') {
                if ($fileExt === 'png') $mimeType = 'image/png';
                elseif ($fileExt === 'webp') $mimeType = 'image/webp';
                elseif ($fileExt === 'pdf') $mimeType = 'application/pdf';
                else $mimeType = 'image/jpeg';
            }

            // Read binary data & encode to Base64
            $binaryData = file_get_contents($fileTmp);
            $base64Image = 'data:' . $mimeType . ';base64,' . base64_encode($binaryData);

            $safeName = mysqli_real_escape_string($conn, $patientName);
            $safePhone = mysqli_real_escape_string($conn, $phone);
            $safeAddress = mysqli_real_escape_string($conn, $address);
            $safeImg = mysqli_real_escape_string($conn, $base64Image);

            $sql = "INSERT INTO prescriptions (user_id, patient_name, phone, delivery_address, image, status) 
                    VALUES ($userIdVal, '$safeName', '$safePhone', '$safeAddress', '$safeImg', 'Pending')";

            if (mysqli_query($conn, $sql)) {
                $rxId = mysqli_insert_id($conn);
                $successMsg = "Your prescription (#RX-" . str_pad($rxId, 4, '0', STR_PAD_LEFT) . ") has been uploaded successfully as Base64! Our pharmacist will review it and contact you on $phone shortly.";
            } else {
                $errorMsg = 'Failed to save prescription to database: ' . mysqli_error($conn);
            }
        }
    }
}

$pageTitle = 'Upload Doctor\'s Prescription | MediQuick Pharmacy';
include_once __DIR__ . '/includes/header.php';
include_once __DIR__ . '/includes/navbar.php';
?>

<main class="py-4">
  <div class="container">
    
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
      <ol class="breadcrumb small">
        <li class="breadcrumb-item"><a href="index.php" class="text-emerald">Home</a></li>
        <li class="breadcrumb-item active" aria-current="page">Upload Prescription</li>
      </ol>
    </nav>

    <div class="row justify-content-center">
      <div class="col-lg-8 col-md-10">
        
        <div class="card card-custom p-4 p-md-5 bg-white">
          
          <div class="d-flex align-items-center gap-3 mb-3 border-bottom pb-3">
            <div class="rounded-3 d-flex align-items-center justify-content-center text-white p-3" style="background-color: #059669;">
              <i class="bi bi-cloud-arrow-up-fill fs-2"></i>
            </div>
            <div>
              <h3 class="fw-bold mb-0">Upload Doctor's Prescription</h3>
              <p class="text-muted small mb-0">Fast-track verification and quotation by SLMC registered pharmacists</p>
            </div>
          </div>

          <?php if (!empty($successMsg)): ?>
            <div class="alert alert-success d-flex align-items-center gap-2" role="alert">
              <i class="bi bi-check-circle-fill fs-4"></i>
              <div>
                <strong>Prescription Uploaded!</strong><br>
                <?php echo htmlspecialchars($successMsg); ?>
                <div class="mt-2">
                  <a href="my-orders.php" class="btn btn-sm btn-outline-success">View Upload Status &rarr;</a>
                  <a href="shop.php" class="btn btn-sm btn-success ms-2">Browse More Products</a>
                </div>
              </div>
            </div>
          <?php endif; ?>

          <?php if (!empty($errorMsg)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
              <i class="bi bi-exclamation-triangle-fill me-2"></i> <?php echo htmlspecialchars($errorMsg); ?>
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
          <?php endif; ?>

          <form action="prescription.php" method="POST" enctype="multipart/form-data">
            
            <div class="mb-3">
              <label for="pName" class="form-label small fw-bold">Patient's Full Name <span class="text-danger">*</span></label>
              <input type="text" id="pName" name="patient_name" class="form-control" required value="<?php echo htmlspecialchars($defaultName); ?>" placeholder="e.g. Kasun Perera">
            </div>

            <div class="row g-3 mb-3">
              <div class="col-md-6">
                <label for="pPhone" class="form-label small fw-bold">Contact Phone Number <span class="text-danger">*</span></label>
                <input type="tel" id="pPhone" name="phone" class="form-control" required value="<?php echo htmlspecialchars($defaultPhone); ?>" placeholder="e.g. +94 77 123 4567">
                <div class="form-text small">Our pharmacist will call you on this number to confirm the order.</div>
              </div>
              <div class="col-md-6">
                <label for="pAddress" class="form-label small fw-bold">Delivery Address <span class="text-danger">*</span></label>
                <textarea id="pAddress" name="delivery_address" class="form-control" rows="2" required placeholder="Street address, city..."><?php echo htmlspecialchars($defaultAddress); ?></textarea>
              </div>
            </div>

            <!-- Image File Upload -->
            <div class="mb-4">
              <label for="rxFile" class="form-label small fw-bold">Attach Doctor's Prescription Photo / PDF <span class="text-danger">*</span></label>
              <input type="file" id="rxFile" name="prescription_image" class="form-control" accept="image/*,.pdf" required onchange="previewImage(this, 'rxPreview')">
              <div class="form-text small">Please ensure doctor's name, medicines, dosage, and date are clearly readable.</div>
              
              <!-- Image Preview Box -->
              <div class="mt-3 text-center">
                <img id="rxPreview" src="#" alt="Prescription Preview" class="img-thumbnail d-none" style="max-height: 220px;">
              </div>
            </div>

            <!-- Guidelines Box -->
            <div class="p-3 mb-4 rounded-3 bg-light border small text-secondary">
              <h6 class="fw-bold text-dark small mb-2"><i class="bi bi-shield-check text-success me-1"></i> Prescription Guidelines:</h6>
              <ul class="mb-0 ps-3">
                <li>Prescription must be issued by a registered medical practitioner (SLMC / Dental / Ayurveda).</li>
                <li>Ensure all medicine names and dosage quantities are visible and not cropped.</li>
                <li>Medicines are strictly dispensed according to the doctor's written instructions.</li>
              </ul>
            </div>

            <button type="submit" class="btn btn-emerald w-100 py-2.5 fs-6 fw-bold">
              <i class="bi bi-upload me-2"></i> Submit Prescription for Review
            </button>
          </form>

        </div>

      </div>
    </div>

  </div>
</main>

<?php include_once __DIR__ . '/includes/footer.php'; ?>
