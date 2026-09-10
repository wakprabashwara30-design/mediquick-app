<?php
/**
 * MediQuick Pharmacy - About Us Page
 * Module: CSE4206 - Web Application Development (Kurunegala)
 */
require_once __DIR__ . '/config/db.php';

$pageTitle = 'About Us | MediQuick Pharmacy Kurunegala';
include_once __DIR__ . '/includes/header.php';
include_once __DIR__ . '/includes/navbar.php';
?>

<main class="py-5">
  <div class="container">
    
    <!-- Hero Header -->
    <div class="text-center max-w-7xl mx-auto mb-5">
      <span class="badge bg-emerald-subtle text-emerald px-3 py-1.5 rounded-pill mb-2 fw-bold">
        <i class="bi bi-geo-alt-fill me-1"></i> Serving Kurunegala & North Western Province
      </span>
      <h1 class="display-5 fw-bold mb-3">About <span class="text-emerald">MediQuick Pharmacy</span></h1>
      <p class="lead text-secondary mx-auto" style="max-width: 750px;">
        Providing genuine, affordable, and temperature-regulated pharmaceuticals supervised strictly by registered pharmacists to families across Kurunegala.
      </p>
    </div>

    <!-- Mission & Vision Cards -->
    <div class="row g-4 mb-5">
      <div class="col-md-6">
        <div class="card card-custom p-4 h-100 bg-white">
          <div class="rounded-3 bg-emerald-subtle text-emerald d-inline-flex align-items-center justify-content-center p-3 mb-3" style="width: 50px; height: 50px;">
            <i class="bi bi-bullseye fs-4"></i>
          </div>
          <h4 class="fw-bold text-dark mb-2">Our Mission</h4>
          <p class="text-secondary small leading-relaxed mb-0">
            To deliver accessible, trustworthy, and express healthcare services directly to patients' doorsteps in Kurunegala, ensuring 100% authentic medications, proper patient counseling, and seamless digital prescription management.
          </p>
        </div>
      </div>

      <div class="col-md-6">
        <div class="card card-custom p-4 h-100 bg-white">
          <div class="rounded-3 bg-primary-subtle text-primary d-inline-flex align-items-center justify-content-center p-3 mb-3" style="width: 50px; height: 50px;">
            <i class="bi bi-eye fs-4"></i>
          </div>
          <h4 class="fw-bold text-dark mb-2">Our Vision</h4>
          <p class="text-secondary small leading-relaxed mb-0">
            To be the most reliable and technology-driven digital dispensary in Sri Lanka, bridging the gap between clinical pharmacy expertise and modern e-commerce convenience for regional communities.
          </p>
        </div>
      </div>
    </div>

    <!-- Why Choose Us Grid -->
    <div class="card card-custom p-4 p-md-5 bg-white mb-5">
      <h3 class="fw-bold text-center mb-4">Why Trust MediQuick Kurunegala?</h3>
      
      <div class="row g-4 text-center">
        <div class="col-sm-6 col-lg-3">
          <div class="p-3">
            <div class="rounded-circle bg-emerald-subtle text-emerald mx-auto d-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
              <i class="bi bi-patch-check fs-3"></i>
            </div>
            <h6 class="fw-bold">100% Authentic Medicines</h6>
            <p class="text-muted small mb-0">Sourced directly from NMRA licensed distributors and State Pharmaceuticals (SPMC).</p>
          </div>
        </div>

        <div class="col-sm-6 col-lg-3">
          <div class="p-3">
            <div class="rounded-circle bg-primary-subtle text-primary mx-auto d-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
              <i class="bi bi-person-badge fs-3"></i>
            </div>
            <h6 class="fw-bold">SLMC Pharmacists</h6>
            <p class="text-muted small mb-0">Every doctor's prescription is audited by certified healthcare professionals.</p>
          </div>
        </div>

        <div class="col-sm-6 col-lg-3">
          <div class="p-3">
            <div class="rounded-circle bg-warning-subtle text-warning mx-auto d-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
              <i class="bi bi-snow fs-3"></i>
            </div>
            <h6 class="fw-bold">Cold-Chain Storage</h6>
            <p class="text-muted small mb-0">Temperature-monitored storage for insulin, vaccines, and sensitive syrups.</p>
          </div>
        </div>

        <div class="col-sm-6 col-lg-3">
          <div class="p-3">
            <div class="rounded-circle bg-info-subtle text-info mx-auto d-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
              <i class="bi bi-truck fs-3"></i>
            </div>
            <h6 class="fw-bold">Express Kurunegala Delivery</h6>
            <p class="text-muted small mb-0">Fast dispatch to Kurunegala town, Mawathagama, Wariyapola, and surrounding areas.</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Contact CTA -->
    <div class="text-center bg-light p-4 rounded-4 border">
      <h5 class="fw-bold mb-2">Need to speak with our on-duty pharmacist?</h5>
      <p class="text-muted small mb-3">Our pharmacy counter at Colombo Road, Kurunegala is open 24 hours a day, 7 days a week.</p>
      <a href="contact.php" class="btn btn-emerald px-4 py-2 me-2">Contact Us</a>
      <a href="shop.php" class="btn btn-outline-secondary px-4 py-2">Browse Medicines</a>
    </div>

  </div>
</main>

<?php include_once __DIR__ . '/includes/footer.php'; ?>
