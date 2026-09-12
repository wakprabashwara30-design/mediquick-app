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
                $successMsg = "Your prescription (#RX-" . str_pad($rxId, 4, '0', STR_PAD_LEFT) . ") has been uploaded successfully! Our duty pharmacist will review your dosages and prepare your quotation. You can track status and pay online or via COD from your dashboard.";
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
    <nav aria-label="breadcrumb" class="mb-3.5 mb-md-4">
      <ol class="breadcrumb small mb-0">
        <li class="breadcrumb-item"><a href="index.php" class="text-emerald">Home</a></li>
        <li class="breadcrumb-item active" aria-current="page">Upload Prescription</li>
      </ol>
    </nav>

    <div class="row justify-content-center">
      <div class="col-lg-8 col-md-10">
        
        <div class="card card-custom p-3.5 p-sm-4 p-md-5 bg-white shadow-sm border-0">
          
          <!-- Header -->
          <div class="d-flex align-items-center gap-3 mb-4 pb-3 border-bottom">
            <div class="rounded-4 d-flex align-items-center justify-content-center text-white shadow-sm flex-shrink-0" style="width: 50px; height: 50px; background: linear-gradient(135deg, #059669 0%, #047857 100%);">
              <i class="bi bi-cloud-arrow-up-fill fs-3"></i>
            </div>
            <div>
              <h3 class="fw-bold font-heading mb-1" style="font-size: clamp(1.2rem, 3.5vw, 1.55rem);">Upload Doctor's Prescription</h3>
              <p class="text-muted small mb-0">Fast-track verification and quotation by SLMC registered pharmacists</p>
            </div>
          </div>

          <?php if (!empty($successMsg)): ?>
            <div class="alert alert-success d-flex align-items-center gap-3 p-3 rounded-4 mb-4" role="alert">
              <i class="bi bi-check-circle-fill fs-2 text-success flex-shrink-0"></i>
              <div>
                <strong class="fs-6">Prescription Uploaded Successfully!</strong>
                <p class="small mb-2 mt-1"><?php echo htmlspecialchars($successMsg); ?></p>
                <div class="d-flex flex-wrap gap-2">
                  <a href="my-orders.php" class="btn btn-sm btn-outline-success rounded-3">View Upload Status &rarr;</a>
                  <a href="shop.php" class="btn btn-sm btn-success rounded-3">Browse Medicines</a>
                </div>
              </div>
            </div>
          <?php endif; ?>

          <?php if (!empty($errorMsg)): ?>
            <div class="alert alert-danger alert-dismissible fade show rounded-4 mb-4" role="alert">
              <i class="bi bi-exclamation-triangle-fill me-2"></i> <?php echo htmlspecialchars($errorMsg); ?>
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
          <?php endif; ?>

          <form action="prescription.php" method="POST" enctype="multipart/form-data">
            
            <!-- Patient Name -->
            <div class="mb-3.5">
              <label for="pName" class="form-label small fw-bold mb-1.5">
                Patient's Full Name <span class="text-danger">*</span>
              </label>
              <div class="input-group">
                <span class="input-group-text bg-light text-muted border-end-0 rounded-start-3 px-3">
                  <i class="bi bi-person-fill text-emerald"></i>
                </span>
                <input type="text" id="pName" name="patient_name" class="form-control border-start-0 rounded-end-3 py-2.5" required value="<?php echo htmlspecialchars($defaultName); ?>" placeholder="e.g. Kasun Perera">
              </div>
            </div>

            <div class="row g-3 mb-3.5">
              <!-- Contact Phone Number -->
              <div class="col-md-6">
                <label for="pPhone" class="form-label small fw-bold mb-1.5">
                  Contact Phone Number <span class="text-danger">*</span>
                </label>
                <div class="input-group">
                  <span class="input-group-text bg-light text-muted border-end-0 rounded-start-3 px-3">
                    <i class="bi bi-telephone-fill text-emerald"></i>
                  </span>
                  <input type="tel" id="pPhone" name="phone" class="form-control border-start-0 rounded-end-3 py-2.5" required value="<?php echo htmlspecialchars($defaultPhone); ?>" placeholder="e.g. +94 77 123 4567">
                </div>
                <div class="form-text small text-muted mt-1">Our pharmacist will call you on this number to confirm.</div>
              </div>

              <!-- Delivery Address -->
              <div class="col-md-6">
                <label for="pAddress" class="form-label small fw-bold mb-1.5">
                  Delivery Address <span class="text-danger">*</span>
                </label>
                <div class="input-group">
                  <span class="input-group-text bg-light text-muted border-end-0 rounded-start-3 px-3 align-items-start pt-2.5">
                    <i class="bi bi-geo-alt-fill text-emerald"></i>
                  </span>
                  <textarea id="pAddress" name="delivery_address" class="form-control border-start-0 rounded-end-3 py-2" rows="2" required placeholder="Street address, city, district..."><?php echo htmlspecialchars($defaultAddress); ?></textarea>
                </div>
              </div>
            </div>

            <!-- Modern Interactive Upload Dropzone -->
            <div class="mb-4">
              <label class="form-label small fw-bold mb-2">
                Attach Doctor's Prescription Photo / PDF <span class="text-danger">*</span>
              </label>

              <div class="rx-upload-dropzone" id="rxDropzone" onclick="document.getElementById('rxFile').click();">
                <input type="file" id="rxFile" name="prescription_image" class="d-none" accept="image/jpeg,image/png,image/webp,application/pdf" required onchange="handlePrescriptionFile(this)">
                
                <!-- Default Initial State -->
                <div id="rxDropzonePrompt" class="text-center py-4 px-3">
                  <div class="rx-upload-icon-circle mb-2.5">
                    <i class="bi bi-camera-fill fs-3 text-emerald"></i>
                  </div>
                  <h6 class="fw-bold text-dark mb-1">Tap to Take Photo or Upload File</h6>
                  <p class="text-muted small mb-2.5">Upload a clear picture of your prescription or PDF file</p>
                  <div class="d-flex flex-wrap justify-content-center gap-1.5">
                    <span class="badge bg-white text-secondary border px-2 py-1">JPG</span>
                    <span class="badge bg-white text-secondary border px-2 py-1">PNG</span>
                    <span class="badge bg-white text-secondary border px-2 py-1">WEBP</span>
                    <span class="badge bg-white text-secondary border px-2 py-1">PDF</span>
                    <span class="badge bg-emerald-subtle text-emerald border border-emerald px-2 py-1">Max 10MB</span>
                  </div>
                </div>

                <!-- Live File Selected State -->
                <div id="rxPreviewCard" class="d-none p-3 text-start">
                  <div class="d-flex align-items-center justify-content-between p-3 bg-emerald-subtle rounded-3 border border-emerald">
                    <div class="d-flex align-items-center gap-3 overflow-hidden">
                      <!-- Image / PDF Preview Icon -->
                      <div class="rx-preview-thumb-wrap flex-shrink-0 shadow-sm">
                        <img id="rxPreviewImg" src="#" alt="Rx Preview" class="rx-preview-thumb d-none">
                        <div id="rxPreviewPdf" class="rx-preview-pdf-icon d-none">
                          <i class="bi bi-file-earmark-pdf-fill text-danger fs-2"></i>
                        </div>
                      </div>
                      <!-- File Details -->
                      <div class="overflow-hidden">
                        <div class="fw-bold text-dark text-truncate small" id="rxFileName">prescription.jpg</div>
                        <div class="text-muted small" id="rxFileSize" style="font-size: 0.75rem;">1.2 MB</div>
                        <span class="badge bg-emerald text-white mt-1" style="font-size: 0.68rem;">
                          <i class="bi bi-check-circle-fill me-1"></i> Ready to submit
                        </span>
                      </div>
                    </div>
                    <!-- Change Button -->
                    <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-3 py-1 ms-2 flex-shrink-0" onclick="event.stopPropagation(); resetPrescriptionFile();" title="Remove & Change File">
                      <i class="bi bi-x-lg me-1"></i> Change
                    </button>
                  </div>
                </div>
              </div>

              <div class="form-text small text-muted mt-1.5">
                <i class="bi bi-info-circle text-emerald me-1"></i> Please ensure doctor's name, medicines, dosage, and date are clearly visible.
              </div>
            </div>

            <!-- Standardized Prescription Guidelines Box -->
            <div class="p-3.5 mb-4 rounded-4 bg-light border">
              <div class="d-flex align-items-center gap-2 mb-2.5">
                <div class="rounded-circle bg-emerald-subtle text-emerald d-flex align-items-center justify-content-center" style="width: 26px; height: 26px; min-width: 26px;">
                  <i class="bi bi-shield-fill-check fs-6"></i>
                </div>
                <h6 class="fw-bold text-dark mb-0 small">Official Prescription Guidelines:</h6>
              </div>
              <div class="d-flex flex-column gap-2 small text-secondary">
                <div class="d-flex align-items-start gap-2">
                  <i class="bi bi-check2-circle text-emerald mt-0.5 flex-shrink-0"></i>
                  <span>Prescription must be issued by a registered medical practitioner (SLMC / Dental / Ayurveda).</span>
                </div>
                <div class="d-flex align-items-start gap-2">
                  <i class="bi bi-check2-circle text-emerald mt-0.5 flex-shrink-0"></i>
                  <span>Ensure all medicine names, dosage, quantity, and date are clearly readable and uncropped.</span>
                </div>
                <div class="d-flex align-items-start gap-2">
                  <i class="bi bi-check2-circle text-emerald mt-0.5 flex-shrink-0"></i>
                  <span>All prescription orders are strictly reviewed by our licensed duty pharmacist before dispensing.</span>
                </div>
              </div>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="btn btn-emerald w-100 py-3 fw-bold shadow">
              <i class="bi bi-cloud-arrow-up-fill fs-5 me-1"></i> Submit Prescription for Review
            </button>
          </form>

        </div>

      </div>
    </div>

  </div>
</main>

<?php include_once __DIR__ . '/includes/footer.php'; ?>
