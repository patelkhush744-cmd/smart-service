<?php
/**
 * Customer Portal: My Bookings
 * Smart Service Booking System
 */
require_once __DIR__ . '/../includes/header.php';
require_auth();

$user = current_user();

// Fetch customer bookings
$stmt = $conn->prepare("
    SELECT 
        b.*, 
        s.name AS service_name, s.duration_mins,
        c.name AS category_name, c.icon AS category_icon,
        t.name AS tech_name, t.phone AS tech_phone, t.rating AS tech_rating
    FROM bookings b
    JOIN services s ON b.service_id = s.id
    JOIN categories c ON s.category_id = c.id
    LEFT JOIN technicians t ON b.technician_id = t.id
    WHERE b.user_id = ?
    ORDER BY b.id DESC
");
$stmt->bind_param("i", $user['id']);
$stmt->execute();
$bookings = $stmt->get_result();
?>

<div class="py-5">
    <div class="container">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
            <div>
                <h1 class="fw-bold fs-2 mb-1">My Service Bookings</h1>
                <p class="text-muted mb-0">Track active requests and view past service history.</p>
            </div>
            <a href="<?= BASE_URL ?>services.php" class="btn btn-dark rounded-pill px-4 fw-bold">
                <i class="bi bi-plus-lg me-1"></i> Book New Service
            </a>
        </div>

        <!-- Bookings List -->
        <?php if ($bookings->num_rows > 0): ?>
            <div class="row g-4">
                <?php while ($b = $bookings->fetch_assoc()): ?>
                    <div class="col-lg-6">
                        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100 position-relative">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <span class="text-muted small fw-bold">ORDER #<?= htmlspecialchars($b['booking_code']) ?></span>
                                    <h4 class="fw-bold text-dark mt-1 mb-0"><?= htmlspecialchars($b['service_name']) ?></h4>
                                    <span class="badge bg-light text-dark border rounded-pill mt-1">
                                        <i class="bi <?= htmlspecialchars($b['category_icon']) ?> me-1"></i> <?= htmlspecialchars($b['category_name']) ?>
                                    </span>
                                </div>
                                <div>
                                    <?= get_status_badge($b['status']) ?>
                                </div>
                            </div>

                            <div class="p-3 bg-light rounded-3 mb-3 small text-muted">
                                <div class="d-flex justify-content-between mb-1">
                                    <span><i class="bi bi-calendar-event me-1"></i> Date & Time:</span>
                                    <span class="fw-bold text-dark"><?= date('M d, Y', strtotime($b['service_date'])) ?> (<?= htmlspecialchars($b['time_slot']) ?>)</span>
                                </div>
                                <div class="d-flex justify-content-between mb-1">
                                    <span><i class="bi bi-geo-alt me-1"></i> Address:</span>
                                    <span class="fw-semibold text-dark text-truncate" style="max-width: 250px;"><?= htmlspecialchars($b['service_address']) ?></span>
                                </div>
                                <?php if (!empty($b['tech_name'])): ?>
                                    <div class="d-flex justify-content-between">
                                        <span><i class="bi bi-person-badge me-1"></i> Technician:</span>
                                        <span class="fw-bold text-primary"><?= htmlspecialchars($b['tech_name']) ?></span>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mt-auto pt-2">
                                <div>
                                    <div class="fs-4 fw-extrabold text-dark"><?= format_currency($b['total_amount']) ?></div>
                                    <span class="small"><?= get_payment_badge($b['payment_status']) ?></span>
                                </div>
                                <a href="<?= BASE_URL ?>user/booking_details.php?code=<?= htmlspecialchars($b['booking_code']) ?>" class="btn btn-dark rounded-pill px-4 fw-bold">
                                    <i class="bi bi-broadcast me-1"></i> Live Tracker <i class="bi bi-chevron-right ms-1"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="card border-0 shadow-sm rounded-4 p-5 text-center bg-white">
                <div class="fs-1 text-muted mb-3"><i class="bi bi-calendar-x"></i></div>
                <h3 class="fw-bold">No Bookings Yet</h3>
                <p class="text-muted max-w-500 mx-auto mb-4">You haven't requested any home services yet. Choose from our wide selection of certified technicians.</p>
                <div>
                    <a href="<?= BASE_URL ?>services.php" class="btn btn-dark rounded-pill px-5 py-2 fw-bold">
                        Browse Services
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
