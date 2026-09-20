<?php
/**
 * Customer Portal: Profile Settings
 */
require_once __DIR__ . '/../includes/header.php';
require_auth();

$user = current_user();

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize(isset($_POST['name']) ? $_POST['name'] : '');
    $phone = sanitize(isset($_POST['phone']) ? $_POST['phone'] : '');
    $address = sanitize(isset($_POST['address']) ? $_POST['address'] : '');

    if (!empty($name) && !empty($phone)) {
        $stmt = $conn->prepare("UPDATE users SET name = ?, phone = ?, address = ? WHERE id = ?");
        $stmt->bind_param("sssi", $name, $phone, $address, $user['id']);
        if ($stmt->execute()) {
            $_SESSION['user_name'] = $name;
            $_SESSION['user_phone'] = $phone;
            set_flash('success', 'Profile updated successfully!');
            header('Location: ' . BASE_URL . 'user/profile.php');
            exit;
        } else {
            set_flash('error', 'Update failed: ' . $conn->error);
        }
    } else {
        set_flash('error', 'Name and Phone cannot be blank.');
    }
}

// Fetch fresh user record
$u_stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$u_stmt->bind_param("i", $user['id']);
$u_stmt->execute();
$user_data = $u_stmt->get_result()->fetch_assoc();

// Count bookings
$count_res = $conn->query("SELECT COUNT(*) AS total FROM bookings WHERE user_id = " . (int)$user['id']);
$total_bookings = $count_res ? $count_res->fetch_assoc()['total'] : 0;
?>

<div class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-7">
                <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-white">
                    <div class="d-flex align-items-center gap-3 mb-4">
                        <div class="bg-dark text-white rounded-circle d-flex align-items-center justify-content-center fw-bold fs-3" style="width:64px; height:64px;">
                            <?= strtoupper(substr($user_data['name'], 0, 1)) ?>
                        </div>
                        <div>
                            <h2 class="fw-bold fs-3 mb-1"><?= htmlspecialchars($user_data['name']) ?></h2>
                            <span class="badge bg-secondary-subtle text-secondary rounded-pill"><?= ucfirst($user_data['role']) ?></span>
                            <span class="text-muted small ms-2"><i class="bi bi-bag-check me-1"></i> <?= (int)$total_bookings ?> Bookings</span>
                        </div>
                    </div>

                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">Email Address (Read-only)</label>
                            <input type="email" class="form-control rounded-3 bg-light" value="<?= htmlspecialchars($user_data['email']) ?>" readonly>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">Full Name</label>
                            <input type="text" name="name" class="form-control rounded-3" value="<?= htmlspecialchars($user_data['name']) ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">Phone Number</label>
                            <input type="text" name="phone" class="form-control rounded-3" value="<?= htmlspecialchars($user_data['phone']) ?>" required>
                        </div>

                        <div class="mb-4">
                            <label class="form-label small fw-bold text-muted">Default Service Address</label>
                            <textarea name="address" rows="3" class="form-control rounded-3" placeholder="Enter full address for fast 1-click booking"><?= htmlspecialchars(isset($user_data['address']) ? $user_data['address'] : '') ?></textarea>
                        </div>

                        <button type="submit" class="btn btn-dark rounded-pill px-5 py-2 fw-bold">
                            Save Changes
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
