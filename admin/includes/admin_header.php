<?php
/**
 * Admin Panel: Header & Sidebar Component
 * Smart Service Booking System
 */
require_once __DIR__ . '/../../includes/functions.php';
require_admin();

$admin_user = current_user();
$current_page = basename($_SERVER['PHP_SELF']);

// Count pending bookings for badge
$pending_count_res = $conn->query("SELECT COUNT(*) AS total FROM bookings WHERE status = 'pending'");
$pending_count = $pending_count_res ? (int)$pending_count_res->fetch_assoc()['total'] : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Command Center | <?= SITE_NAME ?></title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/admin.css">
</head>
<body class="admin-body">

<!-- Sidebar -->
<aside class="admin-sidebar">
    <div class="sidebar-header">
        <a href="<?= BASE_URL ?>admin/index.php" class="brand-title text-decoration-none">
            <i class="bi bi-shield-check text-primary"></i> <?= SITE_NAME ?>
        </a>
        <span class="sidebar-badge">ADMIN</span>
    </div>

    <ul class="sidebar-menu">
        <div class="menu-category">Main Overview</div>
        <li>
            <a href="<?= BASE_URL ?>admin/index.php" class="<?= ($current_page === 'index.php') ? 'active' : '' ?>">
                <i class="bi bi-speedometer2"></i> <span>Dashboard</span>
            </a>
        </li>
        <li>
            <a href="<?= BASE_URL ?>admin/bookings.php" class="<?= ($current_page === 'bookings.php') ? 'active' : '' ?>">
                <i class="bi bi-calendar2-check"></i> <span>Bookings</span>
                <?php if ($pending_count > 0): ?>
                    <span class="nav-badge"><?= $pending_count ?></span>
                <?php endif; ?>
            </a>
        </li>

        <div class="menu-category">Catalog & Workforce</div>
        <li>
            <a href="<?= BASE_URL ?>admin/services.php" class="<?= ($current_page === 'services.php') ? 'active' : '' ?>">
                <i class="bi bi-tools"></i> <span>Services Catalog</span>
            </a>
        </li>
        <li>
            <a href="<?= BASE_URL ?>admin/technicians.php" class="<?= ($current_page === 'technicians.php') ? 'active' : '' ?>">
                <i class="bi bi-person-gear"></i> <span>Technicians / Pros</span>
            </a>
        </li>

        <div class="menu-category">Community & Feedback</div>
        <li>
            <a href="<?= BASE_URL ?>admin/users.php" class="<?= ($current_page === 'users.php') ? 'active' : '' ?>">
                <i class="bi bi-people"></i> <span>Customers</span>
            </a>
        </li>
        <li>
            <a href="<?= BASE_URL ?>admin/reviews.php" class="<?= ($current_page === 'reviews.php') ? 'active' : '' ?>">
                <i class="bi bi-chat-heart"></i> <span>Reviews & Ratings</span>
            </a>
        </li>
    </ul>

    <div class="sidebar-footer">
        <div class="admin-user-info">
            <div class="admin-avatar">
                <i class="bi bi-person-fill"></i>
            </div>
            <div class="admin-details">
                <div class="admin-name"><?= htmlspecialchars($admin_user['name']) ?></div>
                <div class="admin-role">System Administrator</div>
            </div>
            <a href="<?= BASE_URL ?>auth/logout.php" class="text-danger fs-5" title="Logout">
                <i class="bi bi-box-arrow-right"></i>
            </a>
        </div>
    </div>
</aside>

<!-- Main Wrapper -->
<div class="admin-main">
    <!-- Topbar -->
    <header class="admin-topbar">
        <div class="topbar-left">
            <button type="button" class="sidebar-toggle-btn" id="sidebarToggleBtn">
                <i class="bi bi-list fs-5"></i>
            </button>
            <div class="topbar-title">
                <h3>Admin Control Center</h3>
            </div>
        </div>

        <div class="topbar-right">
            <a href="<?= BASE_URL ?>index.php" target="_blank" class="view-site-link">
                <i class="bi bi-box-arrow-up-right"></i> <span>View Live Site</span>
            </a>

            <div class="dropdown">
                <button class="btn btn-light border rounded-pill d-flex align-items-center gap-2 py-1 px-3" type="button" data-bs-toggle="dropdown">
                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width:26px; height:26px; font-size:11px;">
                        A
                    </div>
                    <span class="small fw-bold"><?= htmlspecialchars($admin_user['name']) ?></span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-3">
                    <li><a class="dropdown-item py-2" href="<?= BASE_URL ?>admin/index.php"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a></li>
                    <li><a class="dropdown-item py-2" href="<?= BASE_URL ?>admin/bookings.php"><i class="bi bi-calendar2-check me-2"></i>Manage Bookings</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item py-2 text-danger" href="<?= BASE_URL ?>auth/logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                </ul>
            </div>
        </div>
    </header>

    <div class="admin-content-area">
        <?= render_flash() ?>
