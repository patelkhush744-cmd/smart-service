<?php
/**
 * Admin Panel: Comprehensive Bookings Manager
 * Smart Service Booking System
 */
require_once __DIR__ . '/includes/admin_header.php';

$filter_status = sanitize(isset($_GET['status']) ? $_GET['status'] : 'all');
$search = sanitize(isset($_GET['search']) ? $_GET['search'] : '');

$query = "
    SELECT 
        b.*, 
        u.name AS user_name, u.email AS user_email, u.phone AS user_phone,
        s.name AS service_name, s.category_id,
        t.name AS tech_name, t.phone AS tech_phone
    FROM bookings b
    JOIN users u ON b.user_id = u.id
    JOIN services s ON b.service_id = s.id
    LEFT JOIN technicians t ON b.technician_id = t.id
    WHERE 1=1
";

if ($filter_status !== 'all' && !empty($filter_status)) {
    $query .= " AND b.status = '" . $conn->real_escape_string($filter_status) . "'";
}

if (!empty($search)) {
    $esc = $conn->real_escape_string($search);
    $query .= " AND (b.booking_code LIKE '%$esc%' OR u.name LIKE '%$esc%' OR u.phone LIKE '%$esc%' OR s.name LIKE '%$esc%')";
}

$query .= " ORDER BY b.id DESC";
$bookings_res = $conn->query($query);

// Fetch all technicians for assignment
$all_techs = [];
$t_res = $conn->query("SELECT id, name, category_id, status, rating FROM technicians ORDER BY name ASC");
if ($t_res) {
    while ($tech = $t_res->fetch_assoc()) {
        $all_techs[] = $tech;
    }
}
?>

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
    <div>
        <h2 class="fw-bold mb-1">Service Bookings</h2>
        <p class="text-muted mb-0">Monitor, assign, and update lifecycle states for customer jobs.</p>
    </div>
</div>

<!-- Filter Tabs & Search Bar -->
<div class="card border-0 shadow-sm rounded-4 p-3 mb-4 bg-white">
    <div class="row g-3 align-items-center">
        <div class="col-lg-7">
            <div class="d-flex gap-2 flex-wrap">
                <a href="?status=all" class="btn btn-sm rounded-pill px-3 <?= ($filter_status === 'all') ? 'btn-dark' : 'btn-light text-muted' ?>">All</a>
                <a href="?status=pending" class="btn btn-sm rounded-pill px-3 <?= ($filter_status === 'pending') ? 'btn-dark' : 'btn-light text-muted' ?>">Pending</a>
                <a href="?status=confirmed" class="btn btn-sm rounded-pill px-3 <?= ($filter_status === 'confirmed') ? 'btn-dark' : 'btn-light text-muted' ?>">Confirmed</a>
                <a href="?status=technician_assigned" class="btn btn-sm rounded-pill px-3 <?= ($filter_status === 'technician_assigned') ? 'btn-dark' : 'btn-light text-muted' ?>">Pro Assigned</a>
                <a href="?status=in_progress" class="btn btn-sm rounded-pill px-3 <?= ($filter_status === 'in_progress') ? 'btn-dark' : 'btn-light text-muted' ?>">In Progress</a>
                <a href="?status=completed" class="btn btn-sm rounded-pill px-3 <?= ($filter_status === 'completed') ? 'btn-dark' : 'btn-light text-muted' ?>">Completed</a>
                <a href="?status=cancelled" class="btn btn-sm rounded-pill px-3 <?= ($filter_status === 'cancelled') ? 'btn-dark' : 'btn-light text-muted' ?>">Cancelled</a>
            </div>
        </div>

        <div class="col-lg-5">
            <form method="GET" class="d-flex gap-2">
                <input type="hidden" name="status" value="<?= htmlspecialchars($filter_status) ?>">
                <div class="search-input-group m-0 w-100">
                    <i class="bi bi-search"></i>
                    <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search by Code, Customer, Phone...">
                </div>
                <button type="submit" class="btn btn-dark rounded-3 px-3">Filter</button>
            </form>
        </div>
    </div>
</div>

<!-- Bookings Master Table -->
<div class="admin-card">
    <div class="table-responsive">
        <table class="table admin-table mb-0">
            <thead>
                <tr>
                    <th>Order #</th>
                    <th>Customer</th>
                    <th>Service & Slot</th>
                    <th>Address</th>
                    <th>Assigned Provider</th>
                    <th>Job Status</th>
                    <th>Payment</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($bookings_res && $bookings_res->num_rows > 0): ?>
                    <?php while ($b = $bookings_res->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <span class="fw-bold text-dark">#<?= htmlspecialchars($b['booking_code']) ?></span>
                                <div class="small text-muted"><?= date('M d, H:i', strtotime($b['created_at'])) ?></div>
                            </td>
                            <td>
                                <div class="fw-bold text-dark"><?= htmlspecialchars($b['user_name']) ?></div>
                                <div class="small text-muted"><i class="bi bi-telephone"></i> <?= htmlspecialchars($b['user_phone']) ?></div>
                            </td>
                            <td>
                                <div class="fw-semibold text-dark"><?= htmlspecialchars($b['service_name']) ?></div>
                                <div class="small text-muted">
                                    <i class="bi bi-calendar"></i> <?= date('M d, Y', strtotime($b['service_date'])) ?><br>
                                    <i class="bi bi-clock"></i> <?= htmlspecialchars($b['time_slot']) ?>
                                </div>
                            </td>
                            <td>
                                <div class="small text-muted text-truncate" style="max-width: 180px;" title="<?= htmlspecialchars($b['service_address']) ?>">
                                    <i class="bi bi-geo-alt-fill text-danger me-1"></i> <?= htmlspecialchars($b['service_address']) ?>
                                </div>
                            </td>
                            <td>
                                <?php if (!empty($b['tech_name'])): ?>
                                    <span class="badge bg-light text-primary border rounded-pill py-2 px-3">
                                        <i class="bi bi-person-check-fill me-1"></i> <?= htmlspecialchars($b['tech_name']) ?>
                                    </span>
                                    <button type="button" class="btn btn-link btn-sm text-muted p-0 ms-1 btn-open-assign-modal"
                                            data-booking-id="<?= (int)$b['id'] ?>"
                                            data-booking-code="<?= htmlspecialchars($b['booking_code']) ?>"
                                            data-service-name="<?= htmlspecialchars($b['service_name']) ?>"
                                            data-category-id="<?= (int)$b['category_id'] ?>"
                                            title="Reassign">
                                        <i class="bi bi-arrow-repeat"></i>
                                    </button>
                                <?php else: ?>
                                    <button type="button" class="btn btn-assign-tech btn-action-pill btn-open-assign-modal"
                                            data-booking-id="<?= (int)$b['id'] ?>"
                                            data-booking-code="<?= htmlspecialchars($b['booking_code']) ?>"
                                            data-service-name="<?= htmlspecialchars($b['service_name']) ?>"
                                            data-category-id="<?= (int)$b['category_id'] ?>">
                                        <i class="bi bi-plus-circle-fill"></i> Assign Pro
                                    </button>
                                <?php endif; ?>
                            </td>
                            <td>
                                <select class="form-select form-select-sm rounded-pill status-quick-change border-0 fw-semibold shadow-sm"
                                        style="width: 140px; font-size: 12px;"
                                        data-booking-id="<?= (int)$b['id'] ?>"
                                        data-current="<?= htmlspecialchars($b['status']) ?>">
                                    <option value="pending" <?= $b['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                                    <option value="confirmed" <?= $b['status'] === 'confirmed' ? 'selected' : '' ?>>Confirmed</option>
                                    <option value="technician_assigned" <?= $b['status'] === 'technician_assigned' ? 'selected' : '' ?>>Pro Assigned</option>
                                    <option value="in_progress" <?= $b['status'] === 'in_progress' ? 'selected' : '' ?>>In Progress</option>
                                    <option value="completed" <?= $b['status'] === 'completed' ? 'selected' : '' ?>>Completed</option>
                                    <option value="cancelled" <?= $b['status'] === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                </select>
                            </td>
                            <td>
                                <div class="fw-bold"><?= format_currency($b['total_amount']) ?></div>
                                <span class="small"><?= get_payment_badge($b['payment_status']) ?></span>
                            </td>
                            <td class="text-end">
                                <a href="<?= BASE_URL ?>user/booking_details.php?code=<?= htmlspecialchars($b['booking_code']) ?>" target="_blank" class="btn-action-pill" title="Live Tracker View">
                                    <i class="bi bi-broadcast"></i> Tracker
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">
                            <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                            No bookings match the filter criteria.
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
                    <small class="text-muted d-block mt-2">Assigning a technician automatically advances booking status to <strong>Pro En Route</strong> and notifies the customer.</small>
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
