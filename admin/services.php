<?php
/**
 * Admin Panel: Services & Categories Catalog Manager
 * Smart Service Booking System
 */
require_once __DIR__ . '/includes/admin_header.php';

// Handle Add New Service
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_service') {
    $category_id = (int)$_POST['category_id'];
    $name = sanitize(isset($_POST['name']) ? $_POST['name'] : '');
    $description = sanitize(isset($_POST['description']) ? $_POST['description'] : '');
    $price = (float)$_POST['price'];
    $duration = (int)$_POST['duration_mins'];

    if (!empty($name) && $category_id > 0 && $price > 0) {
        $stmt = $conn->prepare("INSERT INTO services (category_id, name, description, price, duration_mins) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("issdi", $category_id, $name, $description, $price, $duration);
        if ($stmt->execute()) {
            set_flash('success', 'New service added successfully!');
        } else {
            set_flash('error', 'Failed to add service: ' . $conn->error);
        }
    } else {
        set_flash('error', 'Please fill all required service fields.');
    }
    header('Location: ' . BASE_URL . 'admin/services.php');
    exit;
}

// Handle Add New Category
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_category') {
    $cat_name = sanitize(isset($_POST['category_name']) ? $_POST['category_name'] : '');
    $icon = sanitize(isset($_POST['icon']) ? $_POST['icon'] : 'bi-tools');
    $badge = sanitize(isset($_POST['badge']) ? $_POST['badge'] : 'Popular');
    $slug = strtolower(preg_replace('/[^A-Za-z0-9-]+/', '-', $cat_name));

    if (!empty($cat_name)) {
        $stmt = $conn->prepare("INSERT INTO categories (name, slug, icon, badge) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $cat_name, $slug, $icon, $badge);
        if ($stmt->execute()) {
            set_flash('success', 'New category created!');
        } else {
            set_flash('error', 'Failed to add category: ' . $conn->error);
        }
    }
    header('Location: ' . BASE_URL . 'admin/services.php');
    exit;
}

// Handle Toggle Status
if (isset($_GET['toggle_id'])) {
    $id = (int)$_GET['toggle_id'];
    $conn->query("UPDATE services SET status = IF(status = 'active', 'inactive', 'active') WHERE id = $id");
    set_flash('info', 'Service status updated.');
    header('Location: ' . BASE_URL . 'admin/services.php');
    exit;
}

// Fetch categories
$categories = [];
$cat_res = $conn->query("SELECT * FROM categories ORDER BY name ASC");
if ($cat_res) {
    while ($c = $cat_res->fetch_assoc()) $categories[] = $c;
}

// Fetch services
$services_res = $conn->query("
    SELECT s.*, c.name AS category_name, c.icon AS category_icon
    FROM services s
    JOIN categories c ON s.category_id = c.id
    ORDER BY s.category_id ASC, s.name ASC
");
?>

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
    <div>
        <h2 class="fw-bold mb-1">Services & Categories</h2>
        <p class="text-muted mb-0">Manage service pricing, offerings, durations, and active statuses.</p>
    </div>
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-outline-dark rounded-pill px-3 fw-semibold" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
            <i class="bi bi-folder-plus me-1"></i> Add Category
        </button>
        <button type="button" class="btn btn-dark rounded-pill px-4 fw-bold" data-bs-toggle="modal" data-bs-target="#addServiceModal">
            <i class="bi bi-plus-lg me-1"></i> Add New Service
        </button>
    </div>
</div>

<!-- Services Master Table -->
<div class="admin-card">
    <div class="table-responsive">
        <table class="table admin-table mb-0">
            <thead>
                <tr>
                    <th>Service Name</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Duration</th>
                    <th>Rating</th>
                    <th>Status</th>
                    <th class="text-end">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($services_res && $services_res->num_rows > 0): ?>
                    <?php while ($s = $services_res->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <div class="fw-bold text-dark"><?= htmlspecialchars($s['name']) ?></div>
                                <div class="small text-muted text-truncate" style="max-width: 260px;"><?= htmlspecialchars($s['description']) ?></div>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border rounded-pill">
                                    <i class="bi <?= htmlspecialchars($s['category_icon']) ?> me-1"></i> <?= htmlspecialchars($s['category_name']) ?>
                                </span>
                            </td>
                            <td>
                                <span class="fw-extrabold text-dark"><?= format_currency($s['price']) ?></span>
                            </td>
                            <td>
                                <span class="small text-muted"><i class="bi bi-clock"></i> <?= (int)$s['duration_mins'] ?> mins</span>
                            </td>
                            <td>
                                <span class="text-warning small fw-bold"><i class="bi bi-star-fill"></i> <?= number_format((float)$s['rating'], 1) ?></span>
                                <span class="text-muted small">(<?= (int)$s['total_reviews'] ?>)</span>
                            </td>
                            <td>
                                <?php if ($s['status'] === 'active'): ?>
                                    <span class="status-pill badge-completed"><i class="bi bi-check2"></i> Active</span>
                                <?php else: ?>
                                    <span class="status-pill badge-secondary"><i class="bi bi-dash"></i> Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <a href="?toggle_id=<?= (int)$s['id'] ?>" class="btn-action-pill" title="Toggle status">
                                    <i class="bi bi-power"></i> <?= ($s['status'] === 'active') ? 'Deactivate' : 'Activate' ?>
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal: Add New Service -->
<div class="modal fade" id="addServiceModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold">Add New Service Offering</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="add_service">
                <div class="modal-body py-4">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">Category</label>
                        <select name="category_id" class="form-select rounded-3" required>
                            <option value="">-- Choose Category --</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= (int)$cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">Service Name</label>
                        <input type="text" name="name" class="form-control rounded-3" placeholder="e.g. Microwave Oven Repair" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">Short Description</label>
                        <textarea name="description" rows="2" class="form-control rounded-3" placeholder="Explain what is included in this service" required></textarea>
                    </div>
                    <div class="row g-2">
                        <div class="col-6">
                            <label class="form-label small fw-bold text-muted">Price ($)</label>
                            <input type="number" step="0.01" name="price" class="form-control rounded-3" placeholder="49.00" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold text-muted">Duration (Minutes)</label>
                            <input type="number" name="duration_mins" class="form-control rounded-3" value="60" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-dark rounded-pill px-4 fw-bold">Save Service</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Add New Category -->
<div class="modal fade" id="addCategoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold">Add Service Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="add_category">
                <div class="modal-body py-4">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">Category Title</label>
                        <input type="text" name="category_name" class="form-control rounded-3" placeholder="e.g. Carpentry & Furniture" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">Bootstrap Icon Class</label>
                        <input type="text" name="icon" class="form-control rounded-3" value="bi-hammer" placeholder="bi-hammer, bi-tools, bi-brush" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">Badge Label</label>
                        <input type="text" name="badge" class="form-control rounded-3" value="New" placeholder="Popular, New, Trending">
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-dark rounded-pill px-4 fw-bold">Create Category</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
