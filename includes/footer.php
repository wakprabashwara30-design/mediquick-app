<?php
/**
 * MediQuick Pharmacy - Universal Footer Include
 */
if (!isset($basePath)) {
    $basePath = '';
}
?>
<footer class="footer-custom mt-auto">
  <div class="container">
    <div class="row g-4">
      
      <!-- Column 1: Brand & About -->
      <div class="col-lg-4 col-md-6">
        <div class="d-flex align-items-center gap-2 mb-3">
          <div class="rounded-3 d-flex align-items-center justify-content-center text-white" style="width: 2.2rem; height: 2.2rem; background-color: #059669;">
            <i class="bi bi-heart-pulse-fill"></i>
          </div>
          <span class="fs-4 fw-bold text-white font-heading">Medi<span class="text-emerald">Quick</span></span>
        </div>
        <p class="small text-secondary-subtle">
          Licensed online pharmacy and dispensary in Kurunegala, Sri Lanka. Dispensing 100% genuine NMRA registered pharmaceuticals supervised by SLMC registered pharmacists.
        </p>
        <div class="small text-secondary-subtle">
          <i class="bi bi-geo-alt me-1 text-emerald"></i> No. 45, Colombo Road, Kurunegala, Sri Lanka
        </div>
      </div>

      <!-- Column 2: Quick Links -->
      <div class="col-lg-4 col-md-6">
        <h5>Quick Links</h5>
        <ul class="list-unstyled d-flex flex-column gap-2 small">
          <li><a href="<?php echo $basePath; ?>index.php"><i class="bi bi-chevron-right text-emerald me-1"></i> Home</a></li>
          <li><a href="<?php echo $basePath; ?>shop.php"><i class="bi bi-chevron-right text-emerald me-1"></i> Browse Medicines</a></li>
          <li><a href="<?php echo $basePath; ?>prescription.php"><i class="bi bi-chevron-right text-emerald me-1"></i> Upload Prescription</a></li>
          <li><a href="<?php echo $basePath; ?>blogs.php"><i class="bi bi-chevron-right text-emerald me-1"></i> Health Blogs & Guides</a></li>
          <li><a href="<?php echo $basePath; ?>about.php"><i class="bi bi-chevron-right text-emerald me-1"></i> About Us (Kurunegala)</a></li>
          <li><a href="<?php echo $basePath; ?>contact.php"><i class="bi bi-chevron-right text-emerald me-1"></i> Contact & Inquiries</a></li>
        </ul>
      </div>

      <!-- Column 3: Helpline & Standards -->
      <div class="col-lg-4 col-md-12">
        <h5>Customer Support & Help</h5>
        <p class="small text-secondary-subtle mb-3">
          Have questions about your prescription or dosage? Speak to our duty pharmacist 24/7.
        </p>
        <div class="p-3 rounded-3 mb-3" style="background-color: #1e293b; border: 1px solid #334155;">
          <div class="small text-secondary-subtle">Pharmacist Hotline (Kurunegala)</div>
          <div class="fs-5 fw-bold text-emerald">+94 37 222 3456</div>
          <div class="small text-secondary-subtle mt-1"><i class="bi bi-clock me-1"></i> Open 24 Hours / 7 Days</div>
        </div>
      </div>

    </div>

    <hr class="my-4" style="border-color: #334155;">

    <div class="text-center small text-secondary-subtle">
      &copy; <?php echo date('Y'); ?> MediQuick Pharmacy Ltd. All rights reserved.
    </div>
  </div>
</footer>

<!-- Bootstrap 5.3 JavaScript Bundle CDN -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- Project Custom JavaScript -->
<script src="<?php echo $basePath; ?>assets/js/main.js"></script>
</body>
</html>
