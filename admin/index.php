<?php
/**
 * Admin Panel: Dashboard & Analytics Overview
 * Smart Service Booking System
 */
require_once __DIR__ . '/includes/admin_header.php';

// 1. Calculate KPI Metrics
$rev_res = $conn->query("SELECT SUM(total_amount) AS revenue FROM bookings WHERE status = 'completed'");
$total_revenue = $rev_res ? (float)$rev_res->fetch_assoc()['revenue'] : 0.00;

$book_res = $conn->query("SELECT COUNT(*) AS total FROM bookings");
$total_bookings = $book_res ? (int)$book_res->fetch_assoc()['total'] : 0;

$active_res = $conn->query("SELECT COUNT(*) AS active FROM bookings WHERE status IN ('pending', 'confirmed', 'technician_assigned', 'in_progress')");
$active_jobs = $active_res ? (int)$active_res->fetch_assoc()['active'] : 0;

$tech_res = $conn->query("SELECT COUNT(*) AS available FROM technicians WHERE status = 'available'");
$available_techs = $tech_res ? (int)$tech_res->fetch_assoc()['available'] : 0;

// 2. Fetch Recent 6 Bookings
$recent_stmt = $conn->query("
    SELECT 
        b.*, 
        u.name AS user_name, u.phone AS user_phone,
        s.name AS service_name, s.category_id,
        t.name AS tech_name, t.phone AS tech_phone
    FROM bookings b
    JOIN users u ON b.user_id = u.id
    JOIN services s ON b.service_id = s.id
    LEFT JOIN technicians t ON b.technician_id = t.id
    ORDER BY b.id DESC
    LIMIT 6
");

// 3. Fetch All Available Technicians for assignment modal
$all_techs = [];
$t_res = $conn->query("SELECT id, name, category_id, status, rating FROM technicians ORDER BY name ASC");
if ($t_res) {
    while ($tech = $t_res->fetch_assoc()) {
        $all_techs[] = $tech;
    }
}
?>

<!-- Metric Stats Row -->
<div class="row g-4 mb-4">
    <div class="col-xl-3 col-md-6">
        <div class="stat-card">
            <div class="stat-data">
                <h6>Total Revenue</h6>
                <div class="stat-number"><?= format_currency($total_revenue) ?></div>
                <div class="stat-trend positive"><i class="bi bi-graph-up-arrow"></i> Completed Orders</div>
            </div>
            <div class="stat-icon icon-emerald">
                <i class="bi bi-currency-dollar"></i>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="stat-card">
            <div class="stat-data">
                <h6>Total Bookings</h6>
                <div class="stat-number"><?= $total_bookings ?></div>
                <div class="stat-trend neutral"><i class="bi bi-calendar-check"></i> Lifetime volume</div>
            </div>
            <div class="stat-icon icon-blue">
                <i class="bi bi-calendar3"></i>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="stat-card">
            <div class="stat-data">
                <h6>Active Jobs</h6>
                <div class="stat-number"><?= $active_jobs ?></div>
                <div class="stat-trend positive"><i class="bi bi-lightning-charge"></i> Realtime radar</div>
            </div>
            <div class="stat-icon icon-amber">
                <i class="bi bi-broadcast"></i>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="stat-card">
            <div class="stat-data">
                <h6>Ready Providers</h6>
                <div class="stat-number"><?= $available_techs ?></div>
                <div class="stat-trend positive"><i class="bi bi-check-circle"></i> Standby workforce</div>
            </div>
            <div class="stat-icon icon-purple">
                <i class="bi bi-people-fill"></i>
            </div>
        </div>
    </div>
</div>

<!-- Recent Bookings Table Card -->
<div class="admin-card">
    <div class="admin-card-header">
        <div>
            <h5>Recent Service Orders</h5>
            <small class="text-muted">Live view of incoming customer requests</small>
        </div>
        <a href="<?= BASE_URL ?>admin/bookings.php" class="btn btn-outline-dark btn-sm rounded-pill px-3 fw-semibold">
            View All Bookings <i class="bi bi-arrow-right ms-1"></i>
        </a>
    </div>

    <div class="table-responsive">
        <table class="table admin-table mb-0">
            <thead>
                <tr>
                    <th>Order Code</th>
                    <th>Customer</th>
                    <th>Service</th>
                    <th>Scheduled For</th>
                    <th>Assigned Pro</th>
                    <th>Status</th>
                    <th>Amount</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($recent_stmt && $recent_stmt->num_rows > 0): ?>
                    <?php while ($row = $recent_stmt->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <span class="fw-bold text-dark">#<?= htmlspecialchars($row['booking_code']) ?></span>
                            </td>
                            <td>
                                <div class="fw-bold text-dark"><?= htmlspecialchars($row['user_name']) ?></div>
                                <div class="small text-muted"><?= htmlspecialchars($row['user_phone']) ?></div>
                            </td>
                            <td>
                                <div class="fw-semibold"><?= htmlspecialchars($row['service_name']) ?></div>
                            </td>
                            <td>
                                <div class="small text-dark fw-semibold"><?= date('M d, Y', strtotime($row['service_date'])) ?></div>
                                <div class="small text-muted"><?= htmlspecialchars($row['time_slot']) ?></div>
                            </td>
                            <td>
                                <?php if (!empty($row['tech_name'])): ?>
                                    <span class="badge bg-light text-primary border rounded-pill py-2 px-3">
                                        <i class="bi bi-person-check-fill me-1"></i> <?= htmlspecialchars($row['tech_name']) ?>
                                    </span>
                                <?php else: ?>
                                    <button type="button" class="btn btn-assign-tech btn-action-pill btn-open-assign-modal"
                                            data-booking-id="<?= (int)$row['id'] ?>"
                                            data-booking-code="<?= htmlspecialchars($row['booking_code']) ?>"
                                            data-service-name="<?= htmlspecialchars($row['service_name']) ?>"
                                            data-category-id="<?= (int)$row['category_id'] ?>">
                                        <i class="bi bi-plus-circle-fill"></i> Assign Pro
                                    </button>
                                <?php endif; ?>
                            </td>
                            <td>
                                <select class="form-select form-select-sm rounded-pill status-quick-change border-0 fw-semibold shadow-sm"
                                        style="width: 140px; font-size: 12px;"
                                        data-booking-id="<?= (int)$row['id'] ?>"
                                        data-current="<?= htmlspecialchars($row['status']) ?>">
                                    <option value="pending" <?= $row['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                                    <option value="confirmed" <?= $row['status'] === 'confirmed' ? 'selected' : '' ?>>Confirmed</option>
                                    <option value="technician_assigned" <?= $row['status'] === 'technician_assigned' ? 'selected' : '' ?>>Pro Assigned</option>
                                    <option value="in_progress" <?= $row['status'] === 'in_progress' ? 'selected' : '' ?>>In Progress</option>
                                    <option value="completed" <?= $row['status'] === 'completed' ? 'selected' : '' ?>>Completed</option>
                                    <option value="cancelled" <?= $row['status'] === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                </select>
                            </td>
                            <td>
                                <div class="fw-bold"><?= format_currency($row['total_amount']) ?></div>
                                <div class="small"><?= get_payment_badge($row['payment_status']) ?></div>
                            </td>
                            <td class="text-end">
                                <a href="<?= BASE_URL ?>user/booking_details.php?code=<?= htmlspecialchars($row['booking_code']) ?>" target="_blank" class="btn-action-pill" title="Live Tracker View">
                                    <i class="bi bi-radar"></i> Track
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" class="text-center py-4 text-muted">
                            No bookings recorded yet.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal: Assign Technician -->
<div class="modal fade" id="assignTechModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold"><i class="bi bi-person-plus-fill text-primary me-2"></i>Assign Service Technician</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="assignTechForm">
                <div class="modal-body py-4">
                    <input type="hidden" name="booking_id" id="modalBookingId" value="">
                    
                    <div class="p-3 bg-light rounded-3 mb-3 small">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted">Order Ref:</span>
                            <span class="fw-bold" id="modalBookingCodeDisplay">#SRV-0000</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Service:</span>
                            <span class="fw-bold" id="modalServiceNameDisplay">AC Servicing</span>
                        </div>
                    </div>

                    <label class="form-label small fw-bold text-muted">Select Available Technician</label>
                    <select name="technician_id" id="modalTechSelect" class="form-select rounded-3 py-2" required>
                        <option value="">-- Choose Provider --</option>
                        <?php foreach ($all_techs as $t): ?>
                            <option value="<?= (int)$t['id'] ?>" data-category-id="<?= (int)$t['category_id'] ?>">
                                <?= htmlspecialchars($t['name']) ?> (<?= ucfirst($t['status']) ?> • ⭐ <?= $t['rating'] ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <small class="text-muted d-block mt-2">Assigning a technician will automatically change booking status to <strong>Pro En Route</strong> and notify the customer radar.</small>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-dark rounded-pill px-4 fw-bold">Confirm Assignment</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
