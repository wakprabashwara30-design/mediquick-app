<?php
/**
 * MediQuick Pharmacy - Health Blog Article Details Page
 * University 1st-Year Web Application (PHP & MySQL)
 */
require_once __DIR__ . '/config/db.php';

// Comprehensive Clinical Health Blog Articles Data
$allBlogs = [
    1 => [
        'id' => 1,
        'title' => 'Understanding Antibiotics: Why Completing the Full Course Matters',
        'category' => 'Prescription Care',
        'categoryId' => 1,
        'date' => 'Sep 02, 2026',
        'readTime' => '4 min read',
        'author' => 'Dr. Rohan Gunawardena (SLMC-PH 4812)',
        'authorRole' => 'Senior Consultant Pharmacist, SLMC Certified',
        'image' => 'assets/img/blogs/antibiotic-course-guide.jpg',
        'summary' => 'Stopping antibiotics early when symptoms improve leads to antibiotic resistance. Learn how bacteria develop immunity and how to safely take penicillin and amoxicillin.',
        'content' => '
            <p class="lead text-secondary mb-4">
              Antibiotics are among the most powerful tools in modern medicine, responsible for saving millions of lives from bacterial infections. However, their misuse and the premature discontinuation of prescribed courses represent one of the fastest-growing public health crises worldwide: <strong>antimicrobial resistance (AMR)</strong>.
            </p>

            <h4 class="fw-bold text-dark mt-4 mb-3 border-start border-4 border-emerald ps-3">1. How Antibiotics Work Against Bacteria</h4>
            <p>
              When a doctor prescribes an antibiotic like Amoxicillin or Ciprofloxacin, the medication targets specific biological mechanisms in bacteria—such as disrupting their cell wall synthesis or halting protein production. In the first 48 to 72 hours of treatment, the most susceptible bacteria are killed off rapidly, which is why patients often feel a dramatic improvement in their symptoms.
            </p>

            <div class="p-3.5 my-4 rounded-4 bg-emerald-subtle border border-emerald">
              <div class="d-flex align-items-center gap-2 mb-2">
                <i class="bi bi-shield-fill-check fs-5 text-emerald"></i>
                <h6 class="fw-bold text-dark mb-0">Clinical Takeaway from our Pharmacist:</h6>
              </div>
              <p class="small text-secondary mb-0">
                Feeling better does <strong>not</strong> mean the infection is completely eradicated. The toughest, most resilient bacterial strains survive the initial doses and require the full prescribed duration (e.g., 5 to 7 days) to be eliminated.
              </p>
            </div>

            <h4 class="fw-bold text-dark mt-4 mb-3 border-start border-4 border-emerald ps-3">2. What Happens When You Stop Early?</h4>
            <p>
              When you stop taking antibiotics before completing the course, the surviving resilient bacteria mutate. They multiply and develop genetic defenses against the antibiotic, transforming into "superbugs." If symptoms return, that same antibiotic may no longer work, requiring stronger, more expensive second-line medications with higher side-effect risks.
            </p>

            <!-- Do\'s and Don\'ts Box -->
            <div class="card card-custom p-4 my-4 bg-white border">
              <h5 class="fw-bold text-dark mb-3"><i class="bi bi-check2-circle text-emerald me-1"></i> Pharmacist Guidelines for Antibiotic Use</h5>
              <div class="row g-3">
                <div class="col-md-6">
                  <div class="p-3 rounded-3 bg-light h-100 border border-success-subtle">
                    <h6 class="fw-bold text-success mb-2"><i class="bi bi-check-lg me-1"></i> What You SHOULD Do:</h6>
                    <ul class="small text-secondary mb-0 ps-3 d-flex flex-column gap-1.5">
                      <li>Take every single dose exactly on schedule (e.g., every 8 hours).</li>
                      <li>Complete the full 5-7 day course even if fever or pain disappears.</li>
                      <li>Drink plenty of water and store the medicine as directed on the label.</li>
                    </ul>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="p-3 rounded-3 bg-light h-100 border border-danger-subtle">
                    <h6 class="fw-bold text-danger mb-2"><i class="bi bi-x-lg me-1"></i> What You MUST AVOID:</h6>
                    <ul class="small text-secondary mb-0 ps-3 d-flex flex-column gap-1.5">
                      <li>Never take leftover antibiotics for a new cold, cough, or viral flu.</li>
                      <li>Never share prescription antibiotics with family members or friends.</li>
                      <li>Do not double up doses if you accidentally forget a dose.</li>
                    </ul>
                  </div>
                </div>
              </div>
            </div>

            <h4 class="fw-bold text-dark mt-4 mb-3 border-start border-4 border-emerald ps-3">3. Sri Lankan Pharmacy Regulations (NMRA)</h4>
            <p>
              In accordance with Sri Lanka\'s National Medicines Regulatory Authority (NMRA), antibiotics are classified as <strong>Schedule II Prescription-Only Medicines</strong>. At MediQuick Pharmacy, all antibiotic orders require a valid, written doctor\'s prescription verified by our registered SLMC pharmacists before dispatch.
            </p>
        '
    ],
    2 => [
        'id' => 2,
        'title' => 'Safe Storage of Insulin and Heat-Sensitive Medications in Tropical Climates',
        'category' => 'Diabetes & Storage',
        'categoryId' => 1,
        'date' => 'Aug 28, 2026',
        'readTime' => '5 min read',
        'author' => 'MediQuick Clinical Pharmacy Team',
        'authorRole' => 'Dispensary & Cold-Chain Management Dept',
        'image' => 'assets/img/blogs/insulin-cold-chain-storage.jpg',
        'summary' => 'In Sri Lankan tropical temperatures, unmonitored heat degrades insulin efficacy. Discover optimal 2°C–8°C refrigeration practices and traveling storage tips.',
        'content' => '
            <p class="lead text-secondary mb-4">
              In Sri Lanka\'s tropical climate—where ambient temperatures frequently exceed 30°C–34°C with high humidity—storing temperature-sensitive medications safely is crucial. Insulin, eye drops, biologicals, and certain liquid antibiotics quickly lose potency when exposed to excessive heat.
            </p>

            <h4 class="fw-bold text-dark mt-4 mb-3 border-start border-4 border-emerald ps-3">1. Why Temperature Control Matters for Insulin</h4>
            <p>
              Insulin is a polypeptide hormone. When exposed to temperatures above 25°C or direct sunlight, the peptide bonds break down (denature), rendering the medication ineffective at controlling blood sugar spikes. Injecting degraded insulin can lead to unexplained hyperglycemia and diabetic complications.
            </p>

            <div class="p-3.5 my-4 rounded-4 bg-sky-subtle border" style="border-color: #7dd3fc !important;">
              <div class="d-flex align-items-center gap-2 mb-2">
                <i class="bi bi-thermometer-snow fs-5 text-sky"></i>
                <h6 class="fw-bold text-dark mb-0">The 2°C – 8°C Golden Storage Rule:</h6>
              </div>
              <p class="small text-secondary mb-0">
                <strong>Unopened Insulin Vials/Pens:</strong> Must be kept in a home refrigerator between <strong>2°C and 8°C</strong>. Never store in the freezer compartment, as freezing permanently destroys insulin proteins.
              </p>
            </div>

            <h4 class="fw-bold text-dark mt-4 mb-3 border-start border-4 border-emerald ps-3">2. Best Practices for In-Use Insulin Pens</h4>
            <p>
              Once an insulin pen or vial is opened and in daily use, it can be kept at room temperature (below 25°C) for up to <strong>28 days</strong>. Injecting cold insulin directly from the fridge can cause discomfort and stinging.
            </p>
            <ul class="small text-secondary d-flex flex-column gap-2 mb-4">
              <li><strong>Avoid Hot Spots:</strong> Never keep insulin near windows, kitchen stoves, atop refrigerators, or in parked car glove compartments.</li>
              <li><strong>Inspect Appearance:</strong> Normal rapid-acting or long-acting clear insulin should be crystal clear. If it looks cloudy, clumpy, or discolored, discard immediately.</li>
              <li><strong>Traveling in Sri Lanka:</strong> When traveling long distance (e.g. from Kurunegala to Colombo), use an insulated cooling pouch with cold gel packs, ensuring the pen does not directly touch the ice block.</li>
            </ul>

            <div class="card card-custom p-4 my-4 bg-white border">
              <h5 class="fw-bold text-dark mb-2"><i class="bi bi-truck text-emerald me-1"></i> Cold-Chain Dispatch at MediQuick</h5>
              <p class="small text-secondary mb-0">
                Every insulin order placed on MediQuick Pharmacy is packed inside temperature-regulated, insulated cool-packs with ice gel cushioning, ensuring it reaches your doorstep in Kurunegala in pristine clinical condition.
              </p>
            </div>
        '
    ],
    3 => [
        'id' => 3,
        'title' => 'Managing Hypertension: How to Accurately Measure Blood Pressure at Home',
        'category' => 'Cardiovascular Health',
        'categoryId' => 3,
        'date' => 'Aug 20, 2026',
        'readTime' => '4 min read',
        'author' => 'Staff Pharmacist Kurunegala (SLMC)',
        'authorRole' => 'Clinical Cardiology & Patient Counseling Lead',
        'image' => 'assets/img/blogs/hypertension-bp-monitoring.jpg',
        'summary' => 'Accurate upper-arm blood pressure monitoring requires resting 5 minutes, correct cuff positioning at heart level, and avoiding caffeine prior to readings.',
        'content' => '
            <p class="lead text-secondary mb-4">
              High blood pressure (Hypertension) is known as the "silent killer" because it rarely shows noticeable symptoms until serious cardiovascular events occur. Regular home blood pressure monitoring empowers patients to track treatment efficacy and prevent complications.
            </p>

            <h4 class="fw-bold text-dark mt-4 mb-3 border-start border-4 border-emerald ps-3">1. The 5-Step Golden Routine for Accurate Home BP</h4>
            <div class="d-flex flex-column gap-3 mb-4">
              <div class="d-flex align-items-start gap-3 p-3 rounded-3 bg-light">
                <span class="badge bg-emerald text-white rounded-circle p-2 fs-6">1</span>
                <div>
                  <h6 class="fw-bold text-dark mb-1">Rest for 5 Minutes</h6>
                  <p class="small text-secondary mb-0">Sit quietly in a comfortable chair with back support for at least 5 minutes before taking a reading. Avoid talking or checking your phone.</p>
                </div>
              </div>
              <div class="d-flex align-items-start gap-3 p-3 rounded-3 bg-light">
                <span class="badge bg-emerald text-white rounded-circle p-2 fs-6">2</span>
                <div>
                  <h6 class="fw-bold text-dark mb-1">Avoid Stimulants 30 Mins Prior</h6>
                  <p class="small text-secondary mb-0">Do not drink tea, coffee, energy drinks, or smoke for at least 30 minutes before taking a measurement.</p>
                </div>
              </div>
              <div class="d-flex align-items-start gap-3 p-3 rounded-3 bg-light">
                <span class="badge bg-emerald text-white rounded-circle p-2 fs-6">3</span>
                <div>
                  <h6 class="fw-bold text-dark mb-1">Correct Cuff Placement at Heart Level</h6>
                  <p class="small text-secondary mb-0">Use a validated upper-arm digital monitor. Place the cuff on bare skin roughly 2 cm above the elbow bend, positioned level with your heart.</p>
                </div>
              </div>
              <div class="d-flex align-items-start gap-3 p-3 rounded-3 bg-light">
                <span class="badge bg-emerald text-white rounded-circle p-2 fs-6">4</span>
                <div>
                  <h6 class="fw-bold text-dark mb-1">Feet Flat on Floor</h6>
                  <p class="small text-secondary mb-0">Keep both feet uncrossed and flat on the floor. Crossing your legs can temporarily elevate systolic blood pressure by 2 to 8 mmHg.</p>
                </div>
              </div>
            </div>

            <h4 class="fw-bold text-dark mt-4 mb-3 border-start border-4 border-emerald ps-3">2. Understanding Your Numbers</h4>
            <p>
              A standard reading produces two numbers: <strong>Systolic</strong> (top number, pressure during heartbeats) and <strong>Diastolic</strong> (bottom number, pressure between beats). A healthy target for most adults is below <strong>120/80 mmHg</strong>. Always log readings morning and evening to share with your physician.
            </p>
        '
    ],
    4 => [
        'id' => 4,
        'title' => 'Immunity Boosters: The Science Behind Vitamin C and Zinc Supplements',
        'category' => 'Wellness & Immunity',
        'categoryId' => 4,
        'date' => 'Aug 14, 2026',
        'readTime' => '3 min read',
        'author' => 'Nutritional Health Dept',
        'authorRole' => 'Dietary Supplements & Preventive Care Lead',
        'image' => 'assets/img/blogs/vitamin-c-zinc-immunity.jpg',
        'summary' => 'How daily antioxidants enhance cellular immunity against seasonal viral infections and flu in North Western province weather conditions.',
        'content' => '
            <p class="lead text-secondary mb-4">
              Seasonal weather changes, monsoons, and environmental stress place continuous demands on the human immune system. While a balanced diet remains foundational, specific micronutrients like <strong>Vitamin C (Ascorbic Acid)</strong> and <strong>Zinc</strong> have robust clinical evidence supporting immune cell function.
            </p>

            <h4 class="fw-bold text-dark mt-4 mb-3 border-start border-4 border-emerald ps-3">1. The Role of Vitamin C in Cellular Defense</h4>
            <p>
              Vitamin C is a water-soluble antioxidant that accumulates in phagocytes (specialized immune cells) to generate reactive oxygen species that destroy invading pathogens. It also stimulates the production and migration of white blood cells while protecting host tissues from oxidative damage.
            </p>

            <h4 class="fw-bold text-dark mt-4 mb-3 border-start border-4 border-emerald ps-3">2. Why Zinc is Crucial for Lymphocyte Activation</h4>
            <p>
              Zinc functions as a cofactor for over 300 enzymatic reactions in the human body. Even mild zinc deficiency impairs macrophage function, natural killer cell activity, and T-cell development, making the respiratory tract more vulnerable to seasonal viral infections.
            </p>

            <div class="p-3.5 my-4 rounded-4 bg-emerald-subtle border border-emerald">
              <div class="d-flex align-items-center gap-2 mb-2">
                <i class="bi bi-capsule fs-5 text-emerald"></i>
                <h6 class="fw-bold text-dark mb-0">Recommended Daily Intake Guidelines:</h6>
              </div>
              <ul class="small text-secondary mb-0 ps-3 d-flex flex-column gap-1">
                <li><strong>Vitamin C:</strong> 500mg to 1000mg daily during seasonal cold/flu exposure.</li>
                <li><strong>Elemental Zinc:</strong> 15mg to 25mg daily with food to avoid stomach upset.</li>
                <li>Take with a full glass of water, ideally in the morning after breakfast.</li>
              </ul>
            </div>
        '
    ]
];

// Get selected blog ID
$blogId = isset($_GET['id']) ? (int)$_GET['id'] : 1;

// 1. Try fetching from MySQL database
$dbBlogQuery = mysqli_query($conn, "SELECT * FROM blogs WHERE id = $blogId LIMIT 1");
if ($dbBlogQuery && mysqli_num_rows($dbBlogQuery) > 0) {
    $dbBlog = mysqli_fetch_assoc($dbBlogQuery);
    
    // Increment view count
    mysqli_query($conn, "UPDATE blogs SET views = views + 1 WHERE id = $blogId");
    
    // Determine category ID mapping
    $catMap = [
        'Prescription Care' => 1,
        'Diabetes & Storage' => 1,
        'Cardiovascular Health' => 4,
        'Wellness & Immunity' => 3,
        'Baby & Pediatric Care' => 5,
        'Clinical Pharmacotherapy' => 1
    ];
    $catId = $catMap[$dbBlog['category']] ?? 1;

    // Rich content fallback if static template content exists, or format paragraphs
    $contentBody = $dbBlog['content'];
    if (isset($allBlogs[$blogId]['content']) && $blogId <= 4) {
        $contentBody = $allBlogs[$blogId]['content'];
    } elseif (strpos($contentBody, '<p>') === false) {
        $contentBody = '<p class="lead text-secondary mb-4">' . htmlspecialchars($dbBlog['summary']) . '</p><div class="text-secondary leading-relaxed">' . nl2br(htmlspecialchars($contentBody)) . '</div>';
    }

    $post = [
        'id' => $dbBlog['id'],
        'title' => $dbBlog['title'],
        'category' => $dbBlog['category'],
        'categoryId' => $catId,
        'date' => date('M d, Y', strtotime($dbBlog['created_at'])),
        'readTime' => max(2, ceil(str_word_count(strip_tags($dbBlog['content'])) / 200)) . ' min read',
        'author' => $dbBlog['author'],
        'authorRole' => (isset($allBlogs[$blogId]['authorRole']) ? $allBlogs[$blogId]['authorRole'] : 'SLMC Registered Pharmacist, Clinical Advisory'),
        'image' => $dbBlog['image'],
        'summary' => $dbBlog['summary'],
        'content' => $contentBody
    ];
} elseif (isset($allBlogs[$blogId])) {
    $post = $allBlogs[$blogId];
    $catId = (int)$post['categoryId'];
} else {
    $blogId = 1;
    $post = $allBlogs[1];
    $catId = 1;
}

// Fetch related medicines from MySQL database for this article
$relatedProductsQuery = mysqli_query($conn, "SELECT * FROM products WHERE category_id = $catId ORDER BY id ASC LIMIT 3");
$relatedProducts = [];
if ($relatedProductsQuery && mysqli_num_rows($relatedProductsQuery) > 0) {
    while ($p = mysqli_fetch_assoc($relatedProductsQuery)) {
        $relatedProducts[] = $p;
    }
} else {
    // Fallback featured
    $fallbackQuery = mysqli_query($conn, "SELECT * FROM products ORDER BY id ASC LIMIT 3");
    if ($fallbackQuery) {
        while ($p = mysqli_fetch_assoc($fallbackQuery)) {
            $relatedProducts[] = $p;
        }
    }
}

$pageTitle = htmlspecialchars($post['title']) . ' | MediQuick Health Blogs';
include_once __DIR__ . '/includes/header.php';
include_once __DIR__ . '/includes/navbar.php';
?>

<!-- Clean Top Breadcrumb Bar -->
<div class="blog-breadcrumb-bar mb-4">
  <div class="container">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb small mb-0">
          <li class="breadcrumb-item"><a href="index.php" class="text-emerald text-decoration-none"><i class="bi bi-house-door me-1"></i>Home</a></li>
          <li class="breadcrumb-item"><a href="blogs.php" class="text-emerald text-decoration-none">Health Blogs</a></li>
          <li class="breadcrumb-item active text-truncate" style="max-width: 320px;" aria-current="page"><?php echo htmlspecialchars($post['title']); ?></li>
        </ol>
      </nav>
      <a href="blogs.php" class="btn btn-sm btn-soft-emerald rounded-pill px-3 py-1 text-decoration-none">
        <i class="bi bi-arrow-left me-1"></i> Back to All Guides
      </a>
    </div>
  </div>
</div>

<main class="pb-5">
  <div class="container">
    
    <div class="row g-4 g-lg-5">
      
      <!-- 1. LEFT MAIN ARTICLE COLUMN -->
      <div class="col-lg-8">
        
        <article class="bg-white p-4 p-md-5 rounded-4 border shadow-sm">
          
          <!-- Article Meta Badges -->
          <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
            <span class="badge bg-emerald-subtle text-emerald border border-emerald px-3 py-1.5 rounded-pill fw-semibold">
              <i class="bi bi-tag-fill me-1"></i> <?php echo htmlspecialchars($post['category']); ?>
            </span>
            <span class="text-muted small">
              <i class="bi bi-calendar3 me-1"></i> <?php echo htmlspecialchars($post['date']); ?>
            </span>
            <span class="text-muted small">
              &bull; <i class="bi bi-clock me-1"></i> <?php echo htmlspecialchars($post['readTime']); ?>
            </span>
          </div>

          <!-- Main Article Title -->
          <h1 class="fw-bold font-heading mb-3 text-dark" style="font-size: clamp(1.45rem, 3.5vw, 2.2rem); line-height: 1.3;">
            <?php echo htmlspecialchars($post['title']); ?>
          </h1>

          <!-- Verified Author Strip -->
          <div class="d-flex align-items-center gap-3 p-3 mb-4 rounded-3 bg-light border">
            <div class="rounded-circle text-white d-flex align-items-center justify-content-center fw-bold shadow-sm flex-shrink-0" style="width: 46px; height: 46px; background: linear-gradient(135deg, #059669 0%, #047857 100%);">
              <i class="bi bi-person-check-fill fs-5"></i>
            </div>
            <div class="flex-grow-1">
              <div class="d-flex flex-wrap align-items-center justify-content-between gap-1">
                <div class="fw-bold text-dark small"><?php echo htmlspecialchars($post['author']); ?></div>
                <span class="badge bg-emerald-subtle text-emerald border border-emerald rounded-pill px-2.5 py-1 d-none d-sm-inline-flex align-items-center gap-1" style="font-size: 0.72rem;">
                  <i class="bi bi-patch-check-fill"></i> Clinically Reviewed
                </span>
              </div>
              <div class="text-muted" style="font-size: 0.76rem;">
                <i class="bi bi-award-fill text-emerald me-1"></i> <?php echo htmlspecialchars($post['authorRole']); ?>
              </div>
            </div>
          </div>

          <!-- Cover Image -->
          <div class="rounded-4 overflow-hidden mb-4 border shadow-sm" style="max-height: 400px;">
            <img src="<?php echo htmlspecialchars($post['image']); ?>" alt="<?php echo htmlspecialchars($post['title']); ?>" class="w-100" style="object-fit: cover; aspect-ratio: 16/9;">
          </div>

          <!-- Article Rich Body -->
          <div class="article-content text-secondary leading-relaxed mb-4">
            <?php echo $post['content']; ?>
          </div>

          <hr class="my-4">

          <!-- Share & Tags Footer -->
          <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div class="d-flex align-items-center gap-2">
              <span class="fw-bold small text-dark"><i class="bi bi-share me-1"></i> Share Guide:</span>
              <a href="https://api.whatsapp.com/send?text=<?php echo urlencode($post['title'] . ' - Read at: http://localhost:8000/blog-details.php?id=' . $post['id']); ?>" target="_blank" class="btn btn-sm btn-outline-success rounded-pill px-3 py-1.5 text-decoration-none">
                <i class="bi bi-whatsapp me-1"></i> WhatsApp
              </a>
              <button onclick="navigator.clipboard.writeText(window.location.href); alert('Article link copied to clipboard!');" class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1.5">
                <i class="bi bi-link-45deg me-1"></i> Copy Link
              </button>
            </div>
            <a href="blogs.php" class="btn btn-soft-emerald btn-sm rounded-pill px-3 py-1.5 text-decoration-none">
              <i class="bi bi-arrow-left me-1"></i> All Health Guides
            </a>
          </div>

        </article>

        <!-- Related Genuine Medicines Section under Article -->
        <div class="mt-5" id="related-medicines">
          <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <div>
              <h4 class="fw-bold text-dark mb-0"><i class="bi bi-capsule text-emerald me-1.5"></i> Recommended Medicines & Supplies</h4>
              <p class="text-muted small mb-0">Genuine, NMRA certified pharmaceuticals for this health condition</p>
            </div>
            <a href="shop.php?category=<?php echo $catId; ?>" class="btn btn-outline-emerald btn-sm px-3 rounded-pill">
              View Category &rarr;
            </a>
          </div>

          <div class="row g-3">
            <?php foreach ($relatedProducts as $relP): ?>
              <div class="col-md-4">
                <div class="card card-custom h-100 d-flex flex-column p-3 bg-white border">
                  <div class="product-img-container rounded-3 mb-2 bg-light d-flex align-items-center justify-content-center p-2" style="height: 140px;">
                    <img src="<?php echo htmlspecialchars($relP['image']); ?>" alt="<?php echo htmlspecialchars($relP['name']); ?>" style="max-height: 100%; max-width: 100%; object-fit: contain;">
                  </div>
                  <div class="card-body p-1 d-flex flex-column">
                    <h6 class="fw-bold text-dark mb-1 leading-snug" style="font-size: 0.88rem;">
                      <a href="product-details.php?id=<?php echo $relP['id']; ?>" class="text-dark text-decoration-none">
                        <?php echo htmlspecialchars($relP['name']); ?>
                      </a>
                    </h6>
                    <div class="price-tag fw-bold text-emerald mb-2" style="font-size: 0.95rem;">
                      <?php echo formatLKR($relP['price']); ?>
                    </div>
                    <a href="cart.php?action=add&id=<?php echo $relP['id']; ?>" class="btn btn-emerald btn-sm w-100 py-1.5 rounded-3 mt-auto text-decoration-none">
                      <i class="bi bi-cart-plus me-1"></i> Add to Cart
                    </a>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>

      </div>

      <!-- 2. RIGHT SIDEBAR -->
      <div class="col-lg-4">
        
        <!-- Fast Prescription Upload Card -->
        <div class="blog-rx-card p-4 mb-4 text-center">
          <div class="rounded-3 mx-auto d-flex align-items-center justify-content-center text-white mb-3 shadow-sm" style="width: 50px; height: 50px; background: linear-gradient(135deg, #059669 0%, #047857 100%);">
            <i class="bi bi-cloud-arrow-up-fill fs-4"></i>
          </div>
          <h5 class="fw-bold text-dark mb-1">Have a Doctor's Prescription?</h5>
          <p class="small text-secondary mb-3">
            Upload your prescription slip now. Our SLMC duty pharmacist will review dosages and prepare your quotation.
          </p>
          <div class="text-start bg-white p-2.5 rounded-3 border mb-3">
            <div class="d-flex align-items-center gap-2 small text-dark mb-1">
              <i class="bi bi-check-circle-fill text-emerald"></i>
              <span>100% Genuine NMRA Approved</span>
            </div>
            <div class="d-flex align-items-center gap-2 small text-dark">
              <i class="bi bi-check-circle-fill text-emerald"></i>
              <span>~15 Min Pharmacist Review</span>
            </div>
          </div>
          <a href="prescription.php" class="btn btn-emerald w-100 py-2.5 fw-bold shadow-sm text-decoration-none">
            <i class="bi bi-upload me-1.5"></i> Upload Prescription Now
          </a>
        </div>

        <!-- Pharmacist Hotline Widget (High Contrast Dark Slate Card) -->
        <div class="blog-hotline-card p-4 mb-4 shadow-sm">
          <span class="badge bg-emerald text-white px-2.5 py-1 rounded-pill fw-bold mb-2.5">
            <i class="bi bi-headset me-1"></i> 24/7 Clinical Hotline
          </span>
          <h5 class="fw-bold text-white mb-1.5" style="color: #ffffff !important;">Pharmacist Consultation</h5>
          <p class="small mb-3" style="color: #94a3b8 !important; font-size: 0.85rem; line-height: 1.5;">
            Have questions regarding dosages, drug interactions, or prescription storage? Talk directly to our registered pharmacist.
          </p>
          <a href="tel:+94372223456" class="btn btn-emerald w-100 py-2.5 fw-bold text-white shadow text-decoration-none">
            <i class="bi bi-telephone-fill me-1.5"></i> Call +94 37 222 3456
          </a>
        </div>

        <!-- Other Recent Health Guides -->
        <div class="card card-custom p-3.5 bg-white border">
          <h6 class="fw-bold text-dark mb-3 border-bottom pb-2 d-flex align-items-center gap-2">
            <i class="bi bi-journal-medical text-emerald fs-5"></i> More Health Guides
          </h6>
          <div class="d-flex flex-column gap-2.5">
            <?php foreach ($allBlogs as $otherId => $otherPost): ?>
              <?php if ($otherId !== $blogId): ?>
                <a href="blog-details.php?id=<?php echo $otherId; ?>" class="blog-guide-item d-flex align-items-center gap-3">
                  <img src="<?php echo htmlspecialchars($otherPost['image']); ?>" alt="<?php echo htmlspecialchars($otherPost['title']); ?>" class="rounded-3 flex-shrink-0" style="width: 64px; height: 64px; object-fit: cover;">
                  <div class="overflow-hidden">
                    <span class="badge bg-emerald-subtle text-emerald rounded-pill px-2 py-0.5 mb-1" style="font-size: 0.68rem;">
                      <?php echo htmlspecialchars($otherPost['category']); ?>
                    </span>
                    <h6 class="fw-bold mb-0 text-dark leading-snug text-truncate" style="font-size: 0.82rem; line-height: 1.35;" title="<?php echo htmlspecialchars($otherPost['title']); ?>">
                      <?php echo htmlspecialchars($otherPost['title']); ?>
                    </h6>
                    <small class="text-muted" style="font-size: 0.72rem;"><?php echo htmlspecialchars($otherPost['date']); ?> &bull; <?php echo htmlspecialchars($otherPost['readTime']); ?></small>
                  </div>
                </a>
              <?php endif; ?>
            <?php endforeach; ?>
          </div>
        </div>

      </div>

    </div>

  </div>
</main>

<?php include_once __DIR__ . '/includes/footer.php'; ?>
