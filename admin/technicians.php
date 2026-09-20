<?php
/**
 * Admin Panel: Technicians / Service Providers Manager
 * Smart Service Booking System
 */
require_once __DIR__ . '/includes/admin_header.php';

// Handle Add Technician
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_technician') {
    $name = sanitize(isset($_POST['name']) ? $_POST['name'] : '');
    $phone = sanitize(isset($_POST['phone']) ? $_POST['phone'] : '');
    $email = sanitize(isset($_POST['email']) ? $_POST['email'] : '');
    $category_id = (int)$_POST['category_id'];

    if (!empty($name) && !empty($phone) && $category_id > 0) {
        $stmt = $conn->prepare("INSERT INTO technicians (name, phone, email, category_id, status) VALUES (?, ?, ?, ?, 'available')");
        $stmt->bind_param("sssi", $name, $phone, $email, $category_id);
        if ($stmt->execute()) {
            set_flash('success', 'Technician registered successfully!');
        } else {
            set_flash('error', 'Failed to register technician: ' . $conn->error);
        }
    } else {
        set_flash('error', 'Name, Phone and Category are required.');
    }
    header('Location: ' . BASE_URL . 'admin/technicians.php');
    exit;
}

// Handle Status Switch (available, busy, offline)
if (isset($_GET['status_id']) && isset($_GET['set_status'])) {
    $tech_id = (int)$_GET['status_id'];
    $new_st = sanitize($_GET['set_status']);
    if (in_array($new_st, ['available', 'busy', 'offline'])) {
        $conn->query("UPDATE technicians SET status = '$new_st' WHERE id = $tech_id");
        set_flash('info', 'Technician status updated to ' . ucfirst($new_st));
    }
    header('Location: ' . BASE_URL . 'admin/technicians.php');
    exit;
}

// Fetch categories for modal
$categories = [];
$c_res = $conn->query("SELECT * FROM categories ORDER BY name ASC");
if ($c_res) {
    while ($cat = $c_res->fetch_assoc()) $categories[] = $cat;
}

// Fetch technicians
$techs_res = $conn->query("
    SELECT t.*, c.name AS category_name, c.icon AS category_icon
    FROM technicians t
    JOIN categories c ON t.category_id = c.id
    ORDER BY t.id DESC
");
?>

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
    <div>
        <h2 class="fw-bold mb-1">Technicians & Workforce</h2>
        <p class="text-muted mb-0">Manage certified service pros, on-demand availability, and job track records.</p>
    </div>
    <button type="button" class="btn btn-dark rounded-pill px-4 fw-bold" data-bs-toggle="modal" data-bs-target="#addTechModal">
        <i class="bi bi-person-plus-fill me-1"></i> Register New Provider
    </button>
</div>

<!-- Technicians Table -->
<div class="admin-card">
    <div class="table-responsive">
        <table class="table admin-table mb-0">
            <thead>
                <tr>
                    <th>Technician</th>
                    <th>Specialty</th>
                    <th>Phone / Contact</th>
                    <th>Rating</th>
                    <th>Jobs Done</th>
                    <th>Availability</th>
                    <th class="text-end">Status Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($techs_res && $techs_res->num_rows > 0): ?>
                    <?php while ($t = $techs_res->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-3">
                                    <div class="bg-dark text-white rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width:40px; height:40px;">
                                        <?= strtoupper(substr($t['name'], 0, 1)) ?>
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark"><?= htmlspecialchars($t['name']) ?></div>
                                        <div class="small text-muted"><?= htmlspecialchars($t['email']) ?></div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border rounded-pill">
                                    <i class="bi <?= htmlspecialchars($t['category_icon']) ?> me-1"></i> <?= htmlspecialchars($t['category_name']) ?>
                                </span>
                            </td>
                            <td>
                                <a href="tel:<?= htmlspecialchars($t['phone']) ?>" class="fw-semibold text-dark text-decoration-none">
                                    <i class="bi bi-telephone-fill text-success me-1"></i> <?= htmlspecialchars($t['phone']) ?>
                                </a>
                            </td>
                            <td>
                                <span class="text-warning small fw-bold"><i class="bi bi-star-fill"></i> <?= number_format((float)$t['rating'], 2) ?></span>
                            </td>
                            <td>
                                <span class="fw-bold text-dark"><?= (int)$t['total_jobs'] ?></span>
                            </td>
                            <td>
                                <?php if ($t['status'] === 'available'): ?>
                                    <span class="status-pill badge-completed"><i class="bi bi-broadcast"></i> Available</span>
                                <?php elseif ($t['status'] === 'busy'): ?>
                                    <span class="status-pill badge-pending"><i class="bi bi-clock-history"></i> Busy</span>
                                <?php else: ?>
                                    <span class="status-pill badge-secondary"><i class="bi bi-moon"></i> Offline</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <a href="?status_id=<?= (int)$t['id'] ?>&set_status=available" class="btn btn-outline-success <?= ($t['status'] === 'available') ? 'active' : '' ?>">Available</a>
                                    <a href="?status_id=<?= (int)$t['id'] ?>&set_status=busy" class="btn btn-outline-warning <?= ($t['status'] === 'busy') ? 'active' : '' ?>">Busy</a>
                                    <a href="?status_id=<?= (int)$t['id'] ?>&set_status=offline" class="btn btn-outline-secondary <?= ($t['status'] === 'offline') ? 'active' : '' ?>">Offline</a>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal: Add New Technician -->
<div class="modal fade" id="addTechModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold">Register Service Professional</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="add_technician">
                <div class="modal-body py-4">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">Full Name & Title</label>
                        <input type="text" name="name" class="form-control rounded-3" placeholder="e.g. Johnathan Doe (Master Electrician)" required>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-bold text-muted">Phone Number</label>
                            <input type="tel" name="phone" class="form-control rounded-3" placeholder="+1 (555) 000-0000" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold text-muted">Email</label>
                            <input type="email" name="email" class="form-control rounded-3" placeholder="tech@smartservice.com" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">Domain / Category Specialty</label>
                        <select name="category_id" class="form-select rounded-3" required>
                            <option value="">-- Choose Category --</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= (int)$cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-dark rounded-pill px-4 fw-bold">Register Technician</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
