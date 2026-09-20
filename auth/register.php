<?php
/**
 * Authentication: Customer Registration
 * Smart Service Booking System
 */
require_once __DIR__ . '/../includes/functions.php';

if (is_logged_in()) {
    header('Location: ' . BASE_URL . 'user/my_bookings.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize(isset($_POST['name']) ? $_POST['name'] : '');
    $email = sanitize(isset($_POST['email']) ? $_POST['email'] : '');
    $phone = sanitize(isset($_POST['phone']) ? $_POST['phone'] : '');
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $address = sanitize(isset($_POST['address']) ? $_POST['address'] : '');

    if (empty($name) || empty($email) || empty($phone) || empty($password)) {
        $error = 'All fields except address are required.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } else {
        // Check duplicate email
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $error = 'An account with that email already exists. Please login instead.';
        } else {
            $hash = function_exists('password_hash') ? password_hash($password, PASSWORD_BCRYPT) : md5($password);
            $role = 'customer';

            $ins = $conn->prepare("INSERT INTO users (name, email, phone, password, role, address) VALUES (?, ?, ?, ?, ?, ?)");
            $ins->bind_param("ssssss", $name, $email, $phone, $hash, $role, $address);

            if ($ins->execute()) {
                $_SESSION['user_id'] = $ins->insert_id;
                $_SESSION['user_name'] = $name;
                $_SESSION['user_email'] = $email;
                $_SESSION['user_role'] = $role;
                $_SESSION['user_phone'] = $phone;
                $_SESSION['user_avatar'] = 'default_user.png';

                set_flash('success', 'Account created successfully! Welcome to ' . SITE_NAME);
                header('Location: ' . BASE_URL . 'services.php');
                exit;
            } else {
                $error = 'Registration failed: ' . $conn->error;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account | <?= SITE_NAME ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
</head>
<body style="background: #0f172a; min-height: 100vh; display:flex; align-items:center; justify-content:center;">

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <!-- Brand Logo -->
            <div class="text-center mb-4">
                <a href="<?= BASE_URL ?>index.php" class="text-white text-decoration-none fs-3 fw-extrabold">
                    <i class="bi bi-shield-check text-primary"></i> <?= SITE_NAME ?>
                </a>
                <p class="text-secondary small mt-1">Join the premier on-demand home service platform</p>
            </div>

            <!-- Register Card -->
            <div class="card border-0 shadow-lg rounded-4 p-4 p-md-5 bg-white">
                <h3 class="fw-bold mb-1">Create Account</h3>
                <p class="text-muted small mb-4">Book certified technicians in seconds</p>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger border-0 rounded-3 py-2 small mb-3">
                        <i class="bi bi-exclamation-triangle-fill me-1"></i> <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">Full Name</label>
                        <input type="text" name="name" class="form-control rounded-3" placeholder="e.g. Sarah Jenkins" required>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted">Email Address</label>
                            <input type="email" name="email" class="form-control rounded-3" placeholder="name@domain.com" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted">Phone Number</label>
                            <input type="tel" name="phone" class="form-control rounded-3" placeholder="+1 (555) 000-0000" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">Password (6+ chars)</label>
                        <input type="password" name="password" class="form-control rounded-3" placeholder="••••••••" required>
                    </div>

                    <div class="mb-4">
                        <label class="form-label small fw-bold text-muted">Home Address (Optional)</label>
                        <textarea name="address" rows="2" class="form-control rounded-3" placeholder="Flat / House no, street, locality"></textarea>
                    </div>

                    <div class="d-grid mb-3">
                        <button type="submit" class="btn btn-dark btn-lg rounded-pill fw-bold py-2 fs-6">
                            Create Free Account
                        </button>
                    </div>
                </form>

                <div class="text-center small text-muted">
                    Already have an account? <a href="<?= BASE_URL ?>auth/login.php" class="fw-bold text-dark">Sign In</a>
                </div>
            </div>

            <div class="text-center mt-3">
                <a href="<?= BASE_URL ?>index.php" class="text-secondary small text-decoration-none">
                    <i class="bi bi-arrow-left"></i> Return to Homepage
                </a>
            </div>
        </div>
    </div>
</div>

</body>
</html>
