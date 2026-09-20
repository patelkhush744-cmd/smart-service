<?php
/**
 * Global Header Component
 * Smart Service Booking System
 */
require_once __DIR__ . '/functions.php';
$current_user = current_user();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= SITE_NAME ?> | <?= SITE_TAGLINE ?></title>
    
    <!-- Google Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Leaflet.js CSS (For Live Maps) -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
    
    <!-- Custom Theme CSS -->
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
</head>
<body>

<?= render_db_alert_if_needed() ?>

<!-- Uber-Style Header Navbar -->
<header class="uber-navbar">
    <div class="container">
        <nav class="navbar navbar-expand-lg navbar-dark p-0">
            <a class="navbar-brand" href="<?= BASE_URL ?>index.php">
                <i class="bi bi-shield-check text-primary-gradient"></i> <?= SITE_NAME ?>
                <span class="brand-badge">PRO</span>
            </a>

            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarMain">
                <ul class="navbar-nav me-auto ms-lg-4 mb-2 mb-lg-0 gap-1">
                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>index.php"><i class="bi bi-house me-1"></i> Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>services.php"><i class="bi bi-grid me-1"></i> Services</a>
                    </li>
                    <?php if (is_logged_in()): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>user/my_bookings.php">
                            <i class="bi bi-calendar2-check me-1"></i> My Bookings
                        </a>
                    </li>
                    <?php endif; ?>
                </ul>

                <div class="d-flex align-items-center gap-3">
                    <?php if (is_admin()): ?>
                        <a href="<?= BASE_URL ?>admin/index.php" class="btn uber-btn-outline btn-sm">
                            <i class="bi bi-speedometer2 me-1"></i> Admin Portal
                        </a>
                    <?php endif; ?>

                    <?php if (is_logged_in()): ?>
                        <div class="dropdown">
                            <button class="btn user-pill dropdown-toggle border-0" type="button" data-bs-toggle="dropdown">
                                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width:28px; height:28px; font-size:12px;">
                                    <?= strtoupper(substr($current_user['name'], 0, 1)) ?>
                                </div>
                                <span><?= htmlspecialchars($current_user['name']) ?></span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-4 mt-2">
                                <li>
                                    <div class="px-3 py-2 border-bottom">
                                        <div class="fw-bold text-dark"><?= htmlspecialchars($current_user['name']) ?></div>
                                        <div class="small text-muted"><?= htmlspecialchars($current_user['email']) ?></div>
                                    </div>
                                </li>
                                <li><a class="dropdown-item py-2" href="<?= BASE_URL ?>user/my_bookings.php"><i class="bi bi-bag-check me-2"></i>My Bookings</a></li>
                                <li><a class="dropdown-item py-2" href="<?= BASE_URL ?>user/profile.php"><i class="bi bi-person me-2"></i>Profile Settings</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item py-2 text-danger" href="<?= BASE_URL ?>auth/logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                            </ul>
                        </div>
                    <?php else: ?>
                        <a href="<?= BASE_URL ?>auth/login.php" class="uber-btn-outline">Sign In</a>
                        <a href="<?= BASE_URL ?>auth/register.php" class="uber-btn-primary">Register</a>
                    <?php endif; ?>
                </div>
            </div>
        </nav>
    </div>
</header>

<main class="py-3">
    <div class="container">
        <?= render_flash() ?>
    </div>
