<?php
/**
 * Smart Service Booking System - Services Catalog
 * Filterable by Category & Search
 */
require_once __DIR__ . '/includes/header.php';

$selected_category_slug = sanitize(isset($_GET['category']) ? $_GET['category'] : '');
$search_query = sanitize(isset($_GET['search']) ? $_GET['search'] : '');

// Fetch Categories
$categories = [];
$cat_res = @$conn->query("SELECT * FROM categories WHERE status = 'active' ORDER BY name ASC");
if ($cat_res) {
    while ($c = $cat_res->fetch_assoc()) {
        $categories[] = $c;
    }
}

// Fetch Services with category details
$services_sql = "
    SELECT s.*, c.name AS category_name, c.slug AS category_slug, c.icon AS category_icon
    FROM services s
    JOIN categories c ON s.category_id = c.id
    WHERE s.status = 'active'
";

if (!empty($selected_category_slug)) {
    $services_sql .= " AND c.slug = '" . $conn->real_escape_string($selected_category_slug) . "'";
}
if (!empty($search_query)) {
    $services_sql .= " AND (s.name LIKE '%" . $conn->real_escape_string($search_query) . "%' OR s.description LIKE '%" . $conn->real_escape_string($search_query) . "%')";
}
$services_sql .= " ORDER BY s.rating DESC";

$services = [];
$serv_res = @$conn->query($services_sql);
if ($serv_res) {
    while ($s = $serv_res->fetch_assoc()) {
        $services[] = $s;
    }
}
?>

<div class="py-4">
    <div class="container">
        <!-- Page Header -->
        <div class="mb-4">
            <h1 class="fw-bold fs-2 mb-1">Explore All Home Services</h1>
            <p class="text-muted">Instant bookings with verified professionals and transparent pricing.</p>
        </div>

        <!-- Filter Bar -->
        <div class="card border-0 shadow-sm rounded-4 p-3 mb-4 bg-white">
            <div class="row g-3 align-items-center">
                <div class="col-md-6">
                    <div class="search-input-group m-0">
                        <i class="bi bi-search"></i>
                        <input type="text" id="serviceSearchInput" value="<?= htmlspecialchars($search_query) ?>" placeholder="Filter services by keyword...">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="category-scroller pb-0">
                        <a href="javascript:void(0);" data-category="all" class="category-chip category-chip-btn <?= empty($selected_category_slug) ? 'active' : '' ?>">
                            <i class="bi bi-grid-fill"></i> All
                        </a>
                        <?php foreach ($categories as $cat): ?>
                            <a href="javascript:void(0);" data-category="<?= htmlspecialchars($cat['slug']) ?>" class="category-chip category-chip-btn <?= ($selected_category_slug === $cat['slug']) ? 'active' : '' ?>">
                                <i class="bi <?= htmlspecialchars($cat['icon']) ?>"></i> <?= htmlspecialchars($cat['name']) ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Services Grid -->
        <div class="row g-4" id="servicesGrid">
            <?php if (!empty($services)): ?>
                <?php foreach ($services as $srv): ?>
                    <div class="col-lg-4 col-md-6 service-item-col" data-category="<?= htmlspecialchars($srv['category_slug']) ?>">
                        <div class="service-card">
                            <div class="service-card-top">
                                <div class="service-icon-wrap">
                                    <i class="bi <?= htmlspecialchars(isset($srv['category_icon']) ? $srv['category_icon'] : 'bi-tools') ?>"></i>
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
                                <div class="d-flex gap-2">
                                    <a href="<?= BASE_URL ?>service_details.php?id=<?= (int)$srv['id'] ?>" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                                        Details
                                    </a>
                                    <a href="<?= BASE_URL ?>book.php?service_id=<?= (int)$srv['id'] ?>" class="btn-book-action">
                                        Book <i class="bi bi-arrow-right"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Empty Results Alert -->
        <div id="noServicesFound" class="text-center py-5" style="display: <?= empty($services) ? 'block' : 'none' ?>;">
            <div class="fs-1 text-muted mb-2"><i class="bi bi-search"></i></div>
            <h4 class="fw-bold">No services found</h4>
            <p class="text-muted">Try changing your search keywords or switching category filters.</p>
            <a href="<?= BASE_URL ?>services.php" class="btn btn-dark rounded-pill px-4">Reset Filters</a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
