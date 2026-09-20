<?php
/**
 * Admin Panel: Reviews & Quality Ratings
 * Smart Service Booking System
 */
require_once __DIR__ . '/includes/admin_header.php';

// Handle Delete Review
if (isset($_GET['delete_id'])) {
    $del_id = (int)$_GET['delete_id'];
    $conn->query("DELETE FROM reviews WHERE id = $del_id");
    set_flash('info', 'Review deleted successfully.');
    header('Location: ' . BASE_URL . 'admin/reviews.php');
    exit;
}

// Fetch reviews
$reviews_res = $conn->query("
    SELECT 
        r.*, 
        u.name AS user_name, u.email AS user_email,
        s.name AS service_name,
        b.booking_code
    FROM reviews r
    JOIN users u ON r.user_id = u.id
    JOIN services s ON r.service_id = s.id
    JOIN bookings b ON r.booking_id = b.id
    ORDER BY r.id DESC
");
?>

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
    <div>
        <h2 class="fw-bold mb-1">Customer Reviews & Ratings</h2>
        <p class="text-muted mb-0">Monitor service feedback and maintain high quality ratings.</p>
    </div>
</div>

<div class="admin-card">
    <div class="table-responsive">
        <table class="table admin-table mb-0">
            <thead>
                <tr>
                    <th>Order #</th>
                    <th>Customer</th>
                    <th>Service</th>
                    <th>Rating</th>
                    <th>Review Feedback</th>
                    <th>Date</th>
                    <th class="text-end">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($reviews_res && $reviews_res->num_rows > 0): ?>
                    <?php while ($r = $reviews_res->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <a href="<?= BASE_URL ?>user/booking_details.php?code=<?= htmlspecialchars($r['booking_code']) ?>" target="_blank" class="fw-bold text-dark text-decoration-none">
                                    #<?= htmlspecialchars($r['booking_code']) ?>
                                </a>
                            </td>
                            <td>
                                <div class="fw-bold text-dark"><?= htmlspecialchars($r['user_name']) ?></div>
                                <div class="small text-muted"><?= htmlspecialchars($r['user_email']) ?></div>
                            </td>
                            <td>
                                <div class="fw-semibold text-dark"><?= htmlspecialchars($r['service_name']) ?></div>
                            </td>
                            <td>
                                <div class="text-warning fw-bold">
                                    <?php for ($i = 0; $i < (int)$r['rating']; $i++): ?>
                                        <i class="bi bi-star-fill"></i>
                                    <?php endfor; ?>
                                </div>
                            </td>
                            <td>
                                <div class="small text-dark" style="max-width: 320px;">
                                    "<?= htmlspecialchars($r['comment']) ?>"
                                </div>
                            </td>
                            <td>
                                <span class="small text-muted"><?= date('M d, Y', strtotime($r['created_at'])) ?></span>
                            </td>
                            <td class="text-end">
                                <a href="?delete_id=<?= (int)$r['id'] ?>" class="btn btn-outline-danger btn-sm rounded-pill px-3" onclick="return confirm('Delete this review?');">
                                    <i class="bi bi-trash3"></i> Delete
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            No reviews submitted yet.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
