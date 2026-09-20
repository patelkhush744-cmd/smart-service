<?php
/**
 * Admin Panel: Customer Accounts
 * Smart Service Booking System
 */
require_once __DIR__ . '/includes/admin_header.php';

// Fetch customers and their total booking count
$users_res = $conn->query("
    SELECT 
        u.*, 
        COUNT(b.id) AS total_orders,
        COALESCE(SUM(CASE WHEN b.status = 'completed' THEN b.total_amount ELSE 0 END), 0) AS total_spent
    FROM users u
    LEFT JOIN bookings b ON b.user_id = u.id
    GROUP BY u.id
    ORDER BY u.id DESC
");
?>

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
    <div>
        <h2 class="fw-bold mb-1">Customer Accounts</h2>
        <p class="text-muted mb-0">Registered users, lifetime order history, and saved addresses.</p>
    </div>
</div>

<div class="admin-card">
    <div class="table-responsive">
        <table class="table admin-table mb-0">
            <thead>
                <tr>
                    <th>Customer Name</th>
                    <th>Contact Info</th>
                    <th>Account Role</th>
                    <th>Total Bookings</th>
                    <th>Lifetime Spent</th>
                    <th>Joined On</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($users_res && $users_res->num_rows > 0): ?>
                    <?php while ($u = $users_res->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-3">
                                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width:38px; height:38px; font-size:13px;">
                                        <?= strtoupper(substr($u['name'], 0, 1)) ?>
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark"><?= htmlspecialchars($u['name']) ?></div>
                                        <div class="small text-muted text-truncate" style="max-width:200px;" title="<?= htmlspecialchars(isset($u['address']) ? $u['address'] : '') ?>">
                                            <i class="bi bi-geo-alt"></i> <?= htmlspecialchars(isset($u['address']) ? $u['address'] : 'No saved address') ?>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="small fw-semibold text-dark"><?= htmlspecialchars($u['email']) ?></div>
                                <div class="small text-muted"><?= htmlspecialchars($u['phone']) ?></div>
                            </td>
                            <td>
                                <?php if ($u['role'] === 'admin'): ?>
                                    <span class="badge bg-dark rounded-pill px-3 py-1">Administrator</span>
                                <?php else: ?>
                                    <span class="badge bg-light text-dark border rounded-pill px-3 py-1">Customer</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="fw-bold text-dark"><?= (int)$u['total_orders'] ?> orders</span>
                            </td>
                            <td>
                                <span class="fw-extrabold text-success"><?= format_currency($u['total_spent']) ?></span>
                            </td>
                            <td>
                                <span class="small text-muted"><?= date('M d, Y', strtotime($u['created_at'])) ?></span>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
