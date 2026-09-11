<?php
/**
 * MediQuick Pharmacy - Health Blogs & Wellness Guides
 * Module: CSE4206 - Web Application Development (Kurunegala)
 */
require_once __DIR__ . '/config/db.php';

$pageTitle = 'Health Blogs & Wellness Guides | MediQuick Pharmacy';
include_once __DIR__ . '/includes/header.php';
include_once __DIR__ . '/includes/navbar.php';

$blogsQuery = mysqli_query($conn, "SELECT * FROM blogs ORDER BY id DESC");
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
      <?php if ($blogsQuery && mysqli_num_rows($blogsQuery) > 0): ?>
        <?php while ($post = mysqli_fetch_assoc($blogsQuery)): 
          $postImg = $post['image'];
          $postDate = date('M d, Y', strtotime($post['created_at']));
        ?>
          <div class="col-md-6 col-lg-6">
            <div class="card card-custom h-100 overflow-hidden bg-white d-flex flex-column border">
              
              <a href="blog-details.php?id=<?php echo $post['id']; ?>" class="d-block" style="height: 220px; overflow: hidden; background-color: #f8fafc;">
                <img src="<?php echo htmlspecialchars($postImg); ?>" alt="<?php echo htmlspecialchars($post['title']); ?>" style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s ease;">
              </a>

              <div class="card-body p-4 d-flex flex-column">
                <div class="d-flex justify-content-between align-items-center mb-2.5 small text-muted">
                  <span class="badge bg-emerald-subtle text-emerald border border-emerald"><?php echo htmlspecialchars($post['category']); ?></span>
                  <span><i class="bi bi-calendar3 me-1"></i> <?php echo htmlspecialchars($postDate); ?></span>
                </div>

                <h4 class="fw-bold text-dark mb-2" style="font-size: 1.15rem; line-height: 1.35;">
                  <a href="blog-details.php?id=<?php echo $post['id']; ?>" class="text-dark text-decoration-none">
                    <?php echo htmlspecialchars($post['title']); ?>
                  </a>
                </h4>
                <p class="text-secondary small leading-relaxed mb-4">
                  <?php echo htmlspecialchars($post['summary']); ?>
                </p>

                <div class="mt-auto pt-3 border-top d-flex flex-wrap justify-content-between align-items-center gap-2 small">
                  <span class="text-muted"><i class="bi bi-person-fill text-emerald me-1"></i> <?php echo htmlspecialchars($post['author']); ?></span>
                  <div class="d-flex align-items-center gap-3">
                    <a href="blog-details.php?id=<?php echo $post['id']; ?>#related-medicines" class="text-secondary fw-semibold text-decoration-none" title="View Related Medicines">
                      <i class="bi bi-capsule text-emerald me-1"></i> Medicines
                    </a>
                    <a href="blog-details.php?id=<?php echo $post['id']; ?>" class="btn btn-sm btn-soft-emerald rounded-pill px-3 py-1 fw-bold">
                      Read Guide &rarr;
                    </a>
                  </div>
                </div>
              </div>

            </div>
          </div>
        <?php endwhile; ?>
      <?php else: ?>
        <div class="col-12 text-center py-5 text-muted">
          <i class="bi bi-journal-x fs-1 d-block mb-2 text-secondary"></i>
          No health guides published yet. Check back soon!
        </div>
      <?php endif; ?>
    </div>

  </div>
</main>

<?php include_once __DIR__ . '/includes/footer.php'; ?>
