<?php
/**
 * Smart Service Booking System - Service Details Page
 */
require_once __DIR__ . '/includes/header.php';

$service_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($service_id <= 0) {
    header('Location: ' . BASE_URL . 'services.php');
    exit;
}

$stmt = $conn->prepare("
    SELECT s.*, c.name AS category_name, c.slug AS category_slug, c.icon AS category_icon
    FROM services s
    JOIN categories c ON s.category_id = c.id
    WHERE s.id = ? AND s.status = 'active'
");
$stmt->bind_param("i", $service_id);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 0) {
    echo "<div class='container py-5 text-center'><h3>Service not found.</h3><a href='services.php' class='btn btn-dark mt-3'>Back to Services</a></div>";
    require_once __DIR__ . '/includes/footer.php';
    exit;
}
$service = $res->fetch_assoc();

// Fetch reviews for this service
$rev_stmt = $conn->prepare("
    SELECT r.*, u.name AS user_name 
    FROM reviews r
    JOIN users u ON r.user_id = u.id
    WHERE r.service_id = ?
    ORDER BY r.created_at DESC
");
$rev_stmt->bind_param("i", $service_id);
$rev_stmt->execute();
$reviews = $rev_stmt->get_result();
?>

<div class="py-5">
    <div class="container">
        <!-- Breadcrumbs -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb small">
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>index.php">Home</a></li>
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>services.php">Services</a></li>
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>services.php?category=<?= htmlspecialchars($service['category_slug']) ?>"><?= htmlspecialchars($service['category_name']) ?></a></li>
                <li class="breadcrumb-item active" aria-current="page"><?= htmlspecialchars($service['name']) ?></li>
            </ol>
        </nav>

        <div class="row g-5">
            <!-- Left Details Column -->
            <div class="col-lg-8">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <span class="badge bg-light text-dark border px-3 py-2 rounded-pill">
                        <i class="bi <?= htmlspecialchars($service['category_icon']) ?> me-1"></i> <?= htmlspecialchars($service['category_name']) ?>
                    </span>
                    <span class="badge bg-warning-subtle text-warning-emphasis px-3 py-2 rounded-pill">
                        <i class="bi bi-star-fill text-warning me-1"></i> <?= number_format((float)$service['rating'], 1) ?> (<?= (int)$service['total_reviews'] ?> reviews)
                    </span>
                </div>

                <h1 class="fw-bold mb-3"><?= htmlspecialchars($service['name']) ?></h1>
                <p class="fs-5 text-muted mb-4"><?= htmlspecialchars($service['description']) ?></p>

                <!-- Feature Highlights (Uber Style Assurance) -->
                <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white">
                    <h5 class="fw-bold mb-3"><i class="bi bi-shield-check text-primary me-2"></i>Service Guarantee & Benefits</h5>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="d-flex align-items-start gap-3">
                                <div class="bg-light p-2 rounded-circle text-primary"><i class="bi bi-tools fs-5"></i></div>
                                <div>
                                    <h6 class="fw-bold mb-1">Standard Professional Gear</h6>
                                    <p class="text-muted small mb-0">Technicians carry all specialized equipment & genuine spare parts.</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-start gap-3">
                                <div class="bg-light p-2 rounded-circle text-success"><i class="bi bi-shield-lock-fill fs-5"></i></div>
                                <div>
                                    <h6 class="fw-bold mb-1">30-Day Free Warranty</h6>
                                    <p class="text-muted small mb-0">Complimentary revisit if any issue recurs within 30 days of completion.</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-start gap-3">
                                <div class="bg-light p-2 rounded-circle text-info"><i class="bi bi-clock-history fs-5"></i></div>
                                <div>
                                    <h6 class="fw-bold mb-1">On-Time Arrival</h6>
                                    <p class="text-muted small mb-0">Estimated arrival window of <?= (int)$service['duration_mins'] ?> mins with live map tracking.</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-start gap-3">
                                <div class="bg-light p-2 rounded-circle text-warning"><i class="bi bi-cash-stack fs-5"></i></div>
                                <div>
                                    <h6 class="fw-bold mb-1">Transparent Pricing</h6>
                                    <p class="text-muted small mb-0">No hidden visit charges or surprise labor fees on delivery.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Customer Reviews Section -->
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="fw-bold mb-0">Customer Feedback</h5>
                        <div class="text-warning fw-bold"><i class="bi bi-star-fill"></i> <?= number_format((float)$service['rating'], 1) ?> Rating</div>
                    </div>

                    <?php if ($reviews->num_rows > 0): ?>
                        <div class="d-flex flex-column gap-3">
                            <?php while ($rev = $reviews->fetch_assoc()): ?>
                                <div class="p-3 rounded-3 border bg-light">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="fw-bold"><?= htmlspecialchars($rev['user_name']) ?></span>
                                        <div class="text-warning small">
                                            <?php for ($i = 0; $i < (int)$rev['rating']; $i++): ?><i class="bi bi-star-fill"></i><?php endfor; ?>
                                        </div>
                                    </div>
                                    <p class="text-muted small mb-0"><?= htmlspecialchars($rev['comment']) ?></p>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-muted mb-0">No reviews yet. Be the first to book and rate this service!</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Right Sticky Booking Summary Card -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-lg rounded-4 p-4 sticky-top" style="top: 100px; background: #ffffff;">
                    <div class="d-flex justify-content-between align-items-baseline mb-3">
                        <span class="text-muted fw-semibold">Standard Service</span>
                        <div class="fs-2 fw-extrabold text-dark"><?= format_currency($service['price']) ?></div>
                    </div>

                    <div class="p-3 bg-light rounded-3 mb-4 small text-muted">
                        <div class="d-flex justify-content-between mb-2">
                            <span><i class="bi bi-clock me-1"></i> Job Duration:</span>
                            <span class="fw-bold text-dark"><?= (int)$service['duration_mins'] ?> Minutes</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span><i class="bi bi-geo-alt me-1"></i> Coverage:</span>
                            <span class="fw-bold text-dark">Doorstep Delivery</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span><i class="bi bi-shield-check me-1"></i> Safety:</span>
                            <span class="fw-bold text-dark">Background Verified</span>
                        </div>
                    </div>

                    <div class="d-grid gap-2">
                        <a href="<?= BASE_URL ?>book.php?service_id=<?= (int)$service['id'] ?>" class="btn btn-dark btn-lg rounded-pill fw-bold py-3 shadow">
                            Proceed to Book <i class="bi bi-arrow-right ms-2"></i>
                        </a>
                        <a href="<?= BASE_URL ?>services.php" class="btn btn-outline-secondary rounded-pill py-2">
                            Browse Other Services
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
