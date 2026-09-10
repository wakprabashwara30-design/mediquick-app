<?php
/**
 * MediQuick Pharmacy - Health Blogs & Wellness Guides
 * Module: CSE4206 - Web Application Development (Kurunegala)
 */
require_once __DIR__ . '/config/db.php';

$pageTitle = 'Health Blogs & Wellness Guides | MediQuick Pharmacy';
include_once __DIR__ . '/includes/header.php';
include_once __DIR__ . '/includes/navbar.php';

$blogs = [
    [
        'id' => 1,
        'title' => 'Understanding Antibiotics: Why Completing the Full Course Matters',
        'category' => 'Prescription Care',
        'date' => 'Sep 02, 2026',
        'author' => 'Dr. Rohan Gunawardena (SLMC-PH)',
        'image' => 'https://images.unsplash.com/photo-1584308666744-24d5c474f2ae?w=600&auto=format&fit=crop&q=80',
        'summary' => 'Stopping antibiotics early when symptoms improve leads to antibiotic resistance. Learn how bacteria develop immunity and how to safely take penicillin and amoxicillin.'
    ],
    [
        'id' => 2,
        'title' => 'Safe Storage of Insulin and Heat-Sensitive Medications in Tropical Climates',
        'category' => 'Diabetes & Storage',
        'date' => 'Aug 28, 2026',
        'author' => 'MediQuick Clinical Team',
        'image' => 'https://images.unsplash.com/photo-1471864190281-a93a3070b6de?w=600&auto=format&fit=crop&q=80',
        'summary' => 'In Sri Lankan tropical temperatures, unmonitored heat degrades insulin efficacy. Discover optimal 2°C–8°C refrigeration practices and traveling storage tips.'
    ],
    [
        'id' => 3,
        'title' => 'Managing Hypertension: How to Accurately Measure Blood Pressure at Home',
        'category' => 'Cardiovascular Health',
        'date' => 'Aug 20, 2026',
        'author' => 'Staff Pharmacist Kurunegala',
        'image' => 'https://images.unsplash.com/photo-1576091160399-112ba8d25d1d?w=600&auto=format&fit=crop&q=80',
        'summary' => 'Accurate upper-arm blood pressure monitoring requires resting 5 minutes, correct cuff positioning at heart level, and avoiding caffeine prior to readings.'
    ],
    [
        'id' => 4,
        'title' => 'Immunity Boosters: The Science Behind Vitamin C and Zinc Supplements',
        'category' => 'Wellness & Immunity',
        'date' => 'Aug 14, 2026',
        'author' => 'Nutritional Health Dept',
        'image' => 'https://images.unsplash.com/photo-1577401239170-897942555fb3?w=600&auto=format&fit=crop&q=80',
        'summary' => 'How daily antioxidants enhance cellular immunity against seasonal viral infections and flu in North Western province weather conditions.'
    ]
];
?>

<main class="py-5">
  <div class="container">
    
    <!-- Hero Header -->
    <div class="text-center max-w-7xl mx-auto mb-5">
      <span class="badge bg-emerald-subtle text-emerald px-3 py-1.5 rounded-pill mb-2 fw-bold">
        <i class="bi bi-journal-medical me-1"></i> Patient Education & Health Advisory
      </span>
      <h1 class="display-5 fw-bold mb-2">Health Blogs & <span class="text-emerald">Wellness Guides</span></h1>
      <p class="text-muted">Evidence-based healthcare articles and clinical tips written by our registered pharmacists</p>
    </div>

    <!-- Blog Grid -->
    <div class="row g-4">
      <?php foreach ($blogs as $post): ?>
        <div class="col-md-6 col-lg-6">
          <div class="card card-custom h-100 overflow-hidden bg-white d-flex flex-column">
            
            <div style="height: 220px; overflow: hidden; background-color: #f8fafc;">
              <img src="<?php echo htmlspecialchars($post['image']); ?>" alt="<?php echo htmlspecialchars($post['title']); ?>" style="width: 100%; height: 100%; object-fit: cover;">
            </div>

            <div class="card-body p-4 d-flex flex-column">
              <div class="d-flex justify-content-between align-items-center mb-2 small text-muted">
                <span class="badge bg-emerald-subtle text-emerald"><?php echo htmlspecialchars($post['category']); ?></span>
                <span><i class="bi bi-calendar3 me-1"></i> <?php echo htmlspecialchars($post['date']); ?></span>
              </div>

              <h4 class="fw-bold text-dark mb-2"><?php echo htmlspecialchars($post['title']); ?></h4>
              <p class="text-secondary small leading-relaxed mb-4">
                <?php echo htmlspecialchars($post['summary']); ?>
              </p>

              <div class="mt-auto pt-3 border-top d-flex justify-content-between align-items-center small">
                <span class="text-muted"><i class="bi bi-person me-1"></i> <?php echo htmlspecialchars($post['author']); ?></span>
                <a href="shop.php" class="text-emerald fw-bold text-decoration-none">
                  Related Medicines &rarr;
                </a>
              </div>
            </div>

          </div>
        </div>
      <?php endforeach; ?>
    </div>

  </div>
</main>

<?php include_once __DIR__ . '/includes/footer.php'; ?>
