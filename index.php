<?php
/**
 * Smart Service Booking System - Homepage
 * Uber for Home Services Experience
 */
require_once __DIR__ . '/includes/header.php';

// Fetch Categories
$categories = [];
$cat_query = @$conn->query("SELECT * FROM categories WHERE status = 'active' ORDER BY id ASC");
if ($cat_query) {
    while ($cat = $cat_query->fetch_assoc()) {
        $categories[] = $cat;
    }
}

// Fetch Popular Services (limit 6)
$popular_services = [];
$srv_query = @$conn->query("
    SELECT s.*, c.name AS category_name, c.icon AS category_icon 
    FROM services s
    JOIN categories c ON s.category_id = c.id
    WHERE s.status = 'active'
    ORDER BY s.rating DESC, s.total_reviews DESC
    LIMIT 6
");
if ($srv_query) {
    while ($srv = $srv_query->fetch_assoc()) {
        $popular_services[] = $srv;
    }
}
?>

<!-- Hero Section -->
<section class="uber-hero">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-7">
                <div class="hero-tagline">
                    <i class="bi bi-lightning-charge-fill"></i> Verified Professionals in 15 Minutes
                </div>
                <h1 class="hero-title">
                    Expert Home Services.<br>
                    <span>Booked Like Uber.</span>
                </h1>
                <p class="hero-subtitle">
                    Instant booking for AC servicing, certified electricians, plumbers, full house cleaning, and home salon treatments with live status tracking.
                </p>

                <!-- Search / Category Selector Box -->
                <div class="hero-booking-box">
                    <h3 class="d-flex align-items-center gap-2">
                        <i class="bi bi-search text-primary"></i> What service do you need today?
                    </h3>
                    <form action="<?= BASE_URL ?>services.php" method="GET">
                        <div class="row g-2">
                            <div class="col-md-7">
                                <div class="search-input-group m-0">
                                    <i class="bi bi-search"></i>
                                    <input type="text" name="search" placeholder="e.g. AC Servicing, Water Leak, Sofa Cleaning..." required>
                                </div>
                            </div>
                            <div class="col-md-5">
                                <div class="d-flex gap-2">
                                    <select name="category" class="form-select border-1 py-2 rounded-3">
                                        <option value="">All Categories</option>
                                        <?php foreach ($categories as $cat): ?>
                                            <option value="<?= htmlspecialchars($cat['slug']) ?>"><?= htmlspecialchars($cat['name']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button type="submit" class="btn btn-dark px-4 rounded-3 fw-bold">Find</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="col-lg-5 d-none d-lg-block">
                <!-- Visual Simulation Card (Uber-like active service status preview) -->
                <div class="card border-0 shadow-lg rounded-4 overflow-hidden" style="background: #ffffff;">
                    <div class="bg-black text-white p-4">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="badge bg-success rounded-pill px-3 py-1"><i class="bi bi-broadcast me-1"></i> LIVE MATCH</span>
                            <span class="small text-secondary">Arrival: 14 mins</span>
                        </div>
                        <h4 class="fw-bold mb-0">Technician En Route</h4>
                        <div class="small text-secondary">AC Jet Cleaning Service</div>
                    </div>
                    <div class="p-4">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div class="bg-dark text-white rounded-circle d-flex align-items-center justify-content-center" style="width:52px; height:52px; font-size:1.4rem;">
                                <i class="bi bi-person-fill"></i>
                            </div>
                            <div>
                                <div class="fw-bold fs-6">Alex Carter</div>
                                <div class="small text-warning"><i class="bi bi-star-fill"></i> 4.95 (342 jobs done)</div>
                                <div class="small text-muted">HVAC Certified Master</div>
                            </div>
                            <a href="tel:+18005550199" class="btn btn-outline-dark btn-sm rounded-circle ms-auto" style="width:40px; height:40px; display:flex; align-items:center; justify-content:center;">
                                <i class="bi bi-telephone-fill"></i>
                            </a>
                        </div>
                        <div class="bg-light p-3 rounded-3 small text-muted mb-3">
                            <i class="bi bi-geo-alt-fill text-danger me-1"></i> En route to 425 Grand Avenue • Realtime GPS simulated
                        </div>
                        <div class="d-grid">
                            <a href="<?= BASE_URL ?>services.php" class="btn btn-dark rounded-pill py-2 fw-semibold">
                                Browse All 16+ Services <i class="bi bi-arrow-right ms-1"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Category Horizontal Chips -->
<section class="py-5">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3 class="fw-bold mb-0">Browse by Category</h3>
            <a href="<?= BASE_URL ?>services.php" class="text-decoration-none fw-semibold">View All <i class="bi bi-chevron-right"></i></a>
        </div>
        <div class="category-scroller">
            <a href="<?= BASE_URL ?>services.php" class="category-chip active">
                <i class="bi bi-grid-fill"></i> All Services
            </a>
            <?php foreach ($categories as $cat): ?>
                <a href="<?= BASE_URL ?>services.php?category=<?= htmlspecialchars($cat['slug']) ?>" class="category-chip">
                    <i class="bi <?= htmlspecialchars($cat['icon']) ?>"></i> <?= htmlspecialchars($cat['name']) ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Popular Services Grid -->
<section class="pb-5">
    <div class="container">
        <div class="d-flex justify-content-between align-items-end mb-4">
            <div>
                <span class="text-primary fw-bold small text-uppercase letter-spacing-1">Trending Near You</span>
                <h2 class="fw-extrabold mb-0">Most Booked Services</h2>
            </div>
            <a href="<?= BASE_URL ?>services.php" class="btn btn-outline-dark rounded-pill px-4 fw-semibold">See Catalog</a>
        </div>

        <div class="row g-4">
            <?php if (!empty($popular_services)): ?>
                <?php foreach ($popular_services as $srv): ?>
                    <div class="col-lg-4 col-md-6">
                        <div class="service-card">
                            <div class="service-card-top">
                                <div class="service-icon-wrap">
                                    <i class="bi <?= htmlspecialchars(isset($srv['category_icon']) ? $srv['category_icon'] : 'bi-wrench') ?>"></i>
                                </div>
                                <span class="service-badge"><?= htmlspecialchars($srv['category_name']) ?></span>
                            </div>

                            <h4 class="service-title"><?= htmlspecialchars($srv['name']) ?></h4>
                            <p class="service-desc"><?= htmlspecialchars($srv['description']) ?></p>

                            <div class="service-meta">
                                <span><i class="bi bi-clock"></i> <?= (int)$srv['duration_mins'] ?> mins</span>
                                <span><i class="bi bi-star-fill rating-star"></i> <?= number_format((float)$srv['rating'], 1) ?> (<?= (int)$srv['total_reviews'] ?>)</span>
                            </div>

                            <div class="service-card-bottom">
                                <div class="service-price">
                                    <?= format_currency($srv['price']) ?>
                                    <small>/job</small>
                                </div>
                                <a href="<?= BASE_URL ?>book.php?service_id=<?= (int)$srv['id'] ?>" class="btn-book-action">
                                    Book Now <i class="bi bi-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12 text-center py-5">
                    <p class="text-muted">No services loaded yet. Please ensure database <code>smart_services_db</code> is imported from <code>schema.sql</code>.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- How It Works (Uber 3 Steps) -->
<section class="py-5 bg-white border-top border-bottom">
    <div class="container py-3">
        <div class="text-center max-w-600 mx-auto mb-5">
            <span class="text-primary fw-bold small text-uppercase">Seamless Experience</span>
            <h2 class="fw-bold">How SmartService Works</h2>
            <p class="text-muted">Booking certified home assistance takes less than 60 seconds with live technician tracking.</p>
        </div>

        <div class="row g-4">
            <div class="col-md-4">
                <div class="how-it-works-step">
                    <div class="step-num-badge">1</div>
                    <h4>Select Service & Slot</h4>
                    <p>Choose from our curated catalog of home services. Pick your preferred date and convenient 2-hour window.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="how-it-works-step">
                    <div class="step-num-badge">2</div>
                    <h4>Instant Pro Match</h4>
                    <p>Our smart platform matches you with the highest rated background-verified technician near your area.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="how-it-works-step">
                    <div class="step-num-badge">3</div>
                    <h4>Track Live & Relax</h4>
                    <p>Track your technician en route on the live map, inspect upfront pricing, and pay safely only after satisfaction.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Trust & Quality Guarantee Banner -->
<section class="py-5">
    <div class="container">
        <div class="p-5 rounded-4 text-white" style="background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 100%);">
            <div class="row align-items-center g-4">
                <div class="col-lg-8">
                    <h2 class="fw-bold mb-3">Ready to upgrade your home maintenance?</h2>
                    <p class="text-light fs-5 mb-0">Join over 15,000+ satisfied homeowners who enjoy stress-free, professional on-demand services.</p>
                </div>
                <div class="col-lg-4 text-lg-end">
                    <a href="<?= BASE_URL ?>services.php" class="btn btn-light btn-lg rounded-pill px-4 fw-bold">
                        Explore Services <i class="bi bi-chevron-right ms-1"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
