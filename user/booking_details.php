<?php
/**
 * Customer Portal: Uber-Style Live Service Booking Tracker
 */
require_once __DIR__ . '/../includes/header.php';
require_auth();

$code = sanitize(isset($_GET['code']) ? $_GET['code'] : '');
if (empty($code)) {
    header('Location: ' . BASE_URL . 'user/my_bookings.php');
    exit;
}

$user = current_user();

// Fetch booking details
$stmt = $conn->prepare("
    SELECT 
        b.*, 
        s.name AS service_name, s.description AS service_desc, s.duration_mins,
        c.name AS category_name, c.icon AS category_icon,
        t.name AS tech_name, t.phone AS tech_phone, t.rating AS tech_rating, t.total_jobs AS tech_jobs, t.avatar AS tech_avatar,
        r.id AS review_id, r.rating AS user_rating, r.comment AS user_comment
    FROM bookings b
    JOIN services s ON b.service_id = s.id
    JOIN categories c ON s.category_id = c.id
    LEFT JOIN technicians t ON b.technician_id = t.id
    LEFT JOIN reviews r ON r.booking_id = b.id
    WHERE b.booking_code = ? AND (b.user_id = ? OR ? = 'admin')
");
$stmt->bind_param("sis", $code, $user['id'], $user['role']);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 0) {
    echo "<div class='container py-5 text-center'><h3>Booking not found.</h3><a href='my_bookings.php' class='btn btn-dark mt-3'>Back to My Bookings</a></div>";
    require_once __DIR__ . '/../includes/footer.php';
    exit;
}

$booking = $res->fetch_assoc();
?>

<div class="py-5" id="uberTrackerContainer" 
     data-booking-code="<?= htmlspecialchars($booking['booking_code']) ?>"
     data-status="<?= htmlspecialchars($booking['status']) ?>"
     data-lat="<?= (float)(isset($booking['latitude']) ? $booking['latitude'] : 40.7128) ?>"
     data-lng="<?= (float)(isset($booking['longitude']) ? $booking['longitude'] : -74.0060) ?>">
    
    <div class="container">
        <!-- Back Link & Title -->
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
            <div>
                <a href="<?= BASE_URL ?>user/my_bookings.php" class="text-muted small fw-semibold text-decoration-none">
                    <i class="bi bi-arrow-left"></i> Back to My Bookings
                </a>
                <h1 class="fw-bold fs-2 mt-1 mb-0">Live Service Tracker</h1>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span class="text-muted small">Live Polling:</span>
                <span class="badge bg-success-subtle text-success rounded-pill px-3 py-2">
                    <i class="bi bi-broadcast me-1"></i> ACTIVE RADAR
                </span>
            </div>
        </div>

        <div class="row g-4">
            <!-- Main Live Status & Map Column -->
            <div class="col-lg-8">
                <!-- Tracker Master Card -->
                <div class="tracker-card mb-4">
                    <div class="tracker-header">
                        <div>
                            <div class="small text-secondary">BOOKING REFERENCE</div>
                            <div class="booking-code">#<?= htmlspecialchars($booking['booking_code']) ?></div>
                        </div>
                        <div id="currentStatusBadge">
                            <?= get_status_badge($booking['status']) ?>
                        </div>
                    </div>

                    <div class="p-4">
                        <!-- Uber Stepper Visual Line -->
                        <div class="stepper-wrapper">
                            <div class="stepper-progress-fill" id="stepperFill" style="width: 0%;"></div>

                            <div class="stepper-item" data-step="pending">
                                <div class="stepper-icon"><i class="bi bi-clock-history"></i></div>
                                <div class="stepper-title">Requested</div>
                                <div class="stepper-subtitle">Matching Pro</div>
                            </div>

                            <div class="stepper-item" data-step="confirmed">
                                <div class="stepper-icon"><i class="bi bi-check-lg"></i></div>
                                <div class="stepper-title">Confirmed</div>
                                <div class="stepper-subtitle">Slot Locked</div>
                            </div>

                            <div class="stepper-item" data-step="technician_assigned">
                                <div class="stepper-icon"><i class="bi bi-geo-alt-fill"></i></div>
                                <div class="stepper-title">Pro En Route</div>
                                <div class="stepper-subtitle">On the Way</div>
                            </div>

                            <div class="stepper-item" data-step="in_progress">
                                <div class="stepper-icon"><i class="bi bi-tools"></i></div>
                                <div class="stepper-title">In Progress</div>
                                <div class="stepper-subtitle">Service Ongoing</div>
                            </div>

                            <div class="stepper-item" data-step="completed">
                                <div class="stepper-icon"><i class="bi bi-patch-check-fill"></i></div>
                                <div class="stepper-title">Completed</div>
                                <div class="stepper-subtitle">Job Done</div>
                            </div>
                        </div>

                        <!-- Live GPS Map Box -->
                        <div class="mt-4">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="fw-bold small text-muted text-uppercase letter-spacing-1">
                                    <i class="bi bi-radar text-primary me-1"></i> Live Technician Route
                                </span>
                                <span class="small text-muted"><i class="bi bi-clock"></i> Estimated Arrival: ~12 Mins</span>
                            </div>
                            <div id="liveServiceMap" style="height: 320px; border-radius: 16px; border: 1px solid #e2e8f0;"></div>
                        </div>
                    </div>
                </div>

                <!-- Assigned Technician Profile Card (Uber Driver Style) -->
                <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white" id="technicianCardContainer" style="display: <?= !empty($booking['tech_name']) ? 'block' : 'none' ?>;">
                    <h5 class="fw-bold mb-3">Assigned Service Professional</h5>
                    <div class="tech-profile-card">
                        <div class="tech-info-left">
                            <div class="tech-avatar">
                                <i class="bi bi-person-fill"></i>
                            </div>
                            <div>
                                <div class="tech-name" id="techNameDisplay"><?= htmlspecialchars(isset($booking['tech_name']) ? $booking['tech_name'] : '') ?></div>
                                <div class="tech-rating">
                                    <span class="text-warning"><i class="bi bi-star-fill"></i> <span id="techRatingDisplay"><?= number_format((float)(isset($booking['tech_rating']) ? $booking['tech_rating'] : 4.9), 2) ?></span></span>
                                    • <span><?= (int)(isset($booking['tech_jobs']) ? $booking['tech_jobs'] : 100) ?> verified jobs</span>
                                </div>
                                <div class="small text-muted">Specialist • Identity Verified</div>
                            </div>
                        </div>
                        <div>
                            <a href="tel:<?= htmlspecialchars(isset($booking['tech_phone']) ? $booking['tech_phone'] : '') ?>" id="techPhoneDisplay" class="btn-call-pro">
                                <i class="bi bi-telephone-fill"></i> Call Professional
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Rating & Review Section (Displays when completed) -->
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4" id="reviewSection" style="display: <?= ($booking['status'] === 'completed') ? 'block' : 'none' ?>;">
                    <h5 class="fw-bold mb-2">Rate Your Experience</h5>
                    <?php if (!empty($booking['review_id'])): ?>
                        <div class="alert alert-success border-0 rounded-3 mb-0">
                            <div class="fw-bold mb-1">
                                You rated: 
                                <?php for ($i = 0; $i < (int)$booking['user_rating']; $i++): ?><i class="bi bi-star-fill text-warning"></i><?php endfor; ?>
                            </div>
                            <p class="mb-0 text-muted small">"<?= htmlspecialchars($booking['user_comment']) ?>"</p>
                        </div>
                    <?php else: ?>
                        <p class="text-muted small mb-3">How was the service provided by our technician? Your review helps maintain our high Uber-standard quality.</p>
                        <form action="<?= BASE_URL ?>user/submit_review.php" method="POST">
                            <input type="hidden" name="booking_id" value="<?= (int)$booking['id'] ?>">
                            <input type="hidden" name="service_id" value="<?= (int)$booking['service_id'] ?>">
                            <input type="hidden" name="booking_code" value="<?= htmlspecialchars($booking['booking_code']) ?>">
                            
                            <div class="mb-3">
                                <label class="form-label small fw-bold text-muted">Rating (1 to 5 Stars)</label>
                                <select name="rating" class="form-select rounded-3" required>
                                    <option value="5">⭐⭐⭐⭐⭐ 5 Stars - Exceptional Service</option>
                                    <option value="4">⭐⭐⭐⭐ 4 Stars - Very Good</option>
                                    <option value="3">⭐⭐⭐ 3 Stars - Average</option>
                                    <option value="2">⭐⭐ 2 Stars - Below Expectations</option>
                                    <option value="1">⭐ 1 Star - Unsatisfactory</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-bold text-muted">Review Comment</label>
                                <textarea name="comment" rows="2" class="form-control rounded-3" placeholder="Share your experience (punctuality, clean work, behavior)..." required></textarea>
                            </div>
                            <button type="submit" class="btn btn-dark rounded-pill px-4 fw-bold">
                                Submit Review
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Booking Order Summary Sidebar -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white sticky-top" style="top: 100px;">
                    <h5 class="fw-bold mb-3">Service Details</h5>

                    <div class="d-flex align-items-center gap-3 mb-3 p-3 bg-light rounded-3">
                        <div class="fs-2 text-primary">
                            <i class="bi <?= htmlspecialchars($booking['category_icon']) ?>"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-0"><?= htmlspecialchars($booking['service_name']) ?></h6>
                            <span class="small text-muted"><?= htmlspecialchars($booking['category_name']) ?></span>
                        </div>
                    </div>

                    <div class="small text-muted mb-4 d-flex flex-column gap-2">
                        <div class="d-flex justify-content-between">
                            <span>Scheduled Date:</span>
                            <span class="fw-bold text-dark"><?= date('D, M d, Y', strtotime($booking['service_date'])) ?></span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>Time Window:</span>
                            <span class="fw-bold text-dark"><?= htmlspecialchars($booking['time_slot']) ?></span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>Payment Method:</span>
                            <span class="fw-bold text-dark text-capitalize"><?= htmlspecialchars($booking['payment_method']) ?></span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>Payment Status:</span>
                            <span><?= get_payment_badge($booking['payment_status']) ?></span>
                        </div>
                    </div>

                    <div class="mb-4">
                        <h6 class="fw-bold small text-muted text-uppercase mb-1">Service Address</h6>
                        <p class="small text-dark mb-0 bg-light p-3 rounded-3">
                            <i class="bi bi-geo-alt-fill text-danger me-1"></i> <?= htmlspecialchars($booking['service_address']) ?>
                        </p>
                    </div>

                    <?php if (!empty($booking['special_notes'])): ?>
                        <div class="mb-4">
                            <h6 class="fw-bold small text-muted text-uppercase mb-1">Special Notes</h6>
                            <p class="small text-muted mb-0 bg-light p-2 rounded-3">
                                <?= htmlspecialchars($booking['special_notes']) ?>
                            </p>
                        </div>
                    <?php endif; ?>

                    <div class="d-flex justify-content-between align-items-baseline mb-4 pt-3 border-top">
                        <span class="fw-bold">Total Fare:</span>
                        <span class="fs-3 fw-extrabold text-dark"><?= format_currency($booking['total_amount']) ?></span>
                    </div>

                    <?php if ($booking['status'] === 'pending' || $booking['status'] === 'confirmed'): ?>
                        <div class="d-grid">
                            <button type="button" id="btnCancelBooking" class="btn btn-outline-danger rounded-pill py-2 fw-semibold">
                                <i class="bi bi-x-circle me-1"></i> Cancel Booking
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Realtime Tracker Script -->
<script src="<?= BASE_URL ?>assets/js/booking-tracker.js"></script>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const cancelBtn = document.getElementById('btnCancelBooking');
    if (cancelBtn) {
        cancelBtn.addEventListener('click', async () => {
            if (!confirm('Are you sure you want to cancel this booking?')) return;

            const formData = new FormData();
            formData.append('action', 'cancel_by_user');
            formData.append('booking_id', '<?= (int)$booking['id'] ?>');

            try {
                const res = await fetch('<?= BASE_URL ?>api/update_booking.php', {
                    method: 'POST',
                    body: formData
                });
                const data = await res.json();
                if (data.success) {
                    alert('Booking cancelled successfully.');
                    window.location.reload();
                } else {
                    alert(data.message || 'Failed to cancel');
                }
            } catch (err) {
                alert('Connection error');
            }
        });
    }
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
