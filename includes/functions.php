<?php
/**
 * Core Helper Functions
 * Smart Service Booking System
 */

require_once __DIR__ . '/../config/db.php';

/**
 * Sanitize User Input
 */
function sanitize($data) {
    if (is_array($data)) {
        return array_map('sanitize', $data);
    }
    return htmlspecialchars(trim((string)$data), ENT_QUOTES, 'UTF-8');
}

/**
 * Format currency with symbol
 */
function format_currency($amount) {
    return CURRENCY_SYMBOL . number_format((float)$amount, 2);
}

/**
 * Check if user is logged in
 */
function is_logged_in() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Check if current session is an admin
 */
function is_admin() {
    return is_logged_in() && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

/**
 * Get current logged in user data array
 */
function current_user() {
    if (!is_logged_in()) {
        return null;
    }
    return array(
        'id' => $_SESSION['user_id'],
        'name' => isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'User',
        'email' => isset($_SESSION['user_email']) ? $_SESSION['user_email'] : '',
        'role' => isset($_SESSION['user_role']) ? $_SESSION['user_role'] : 'customer',
        'phone' => isset($_SESSION['user_phone']) ? $_SESSION['user_phone'] : '',
        'avatar' => isset($_SESSION['user_avatar']) ? $_SESSION['user_avatar'] : 'default_user.png'
    );
}

/**
 * Enforce Authentication
 */
function require_auth($redirect = 'auth/login.php') {
    if (!is_logged_in()) {
        set_flash('warning', 'Please login to access this page.');
        header('Location: ' . BASE_URL . $redirect);
        exit;
    }
}

/**
 * Enforce Admin Role
 */
function require_admin($redirect = 'auth/login.php') {
    if (!is_admin()) {
        set_flash('error', 'Admin authorization required.');
        header('Location: ' . BASE_URL . $redirect);
        exit;
    }
}

/**
 * Generate Unique Booking Code (e.g., SRV-8492)
 */
function generate_booking_code() {
    return 'SRV-' . strtoupper(substr(uniqid(), -4)) . rand(10, 99);
}

/**
 * Status HTML Badge Generator for Bookings
 */
function get_status_badge($status) {
    $map = array(
        'pending' => array('bg' => 'badge-pending', 'label' => 'Pending Match', 'icon' => 'bi-hourglass-split'),
        'confirmed' => array('bg' => 'badge-confirmed', 'label' => 'Confirmed', 'icon' => 'bi-check-circle'),
        'technician_assigned' => array('bg' => 'badge-assigned', 'label' => 'Pro En Route', 'icon' => 'bi-geo-alt-fill'),
        'in_progress' => array('bg' => 'badge-inprogress', 'label' => 'In Progress', 'icon' => 'bi-tools'),
        'completed' => array('bg' => 'badge-completed', 'label' => 'Completed', 'icon' => 'bi-patch-check-fill'),
        'cancelled' => array('bg' => 'badge-cancelled', 'label' => 'Cancelled', 'icon' => 'bi-x-circle-fill')
    );

    $badge = isset($map[$status]) ? $map[$status] : array('bg' => 'badge-secondary', 'label' => ucfirst($status), 'icon' => 'bi-info-circle');
    return '<span class="status-pill ' . $badge['bg'] . '"><i class="' . $badge['icon'] . '"></i> ' . $badge['label'] . '</span>';
}

/**
 * Human friendly status label
 */
function get_status_label($status) {
    $labels = array(
        'pending' => 'Pending Match',
        'confirmed' => 'Booking Confirmed',
        'technician_assigned' => 'Technician Assigned',
        'in_progress' => 'Service In Progress',
        'completed' => 'Completed',
        'cancelled' => 'Cancelled'
    );
    return isset($labels[$status]) ? $labels[$status] : ucfirst(str_replace('_', ' ', $status));
}

/**
 * Payment Status Badge
 */
function get_payment_badge($status) {
    if ($status === 'paid') {
        return '<span class="status-pill badge-completed"><i class="bi-check2"></i> Paid</span>';
    } elseif ($status === 'refunded') {
        return '<span class="status-pill badge-secondary"><i class="bi-arrow-counterclockwise"></i> Refunded</span>';
    }
    return '<span class="status-pill badge-pending"><i class="bi-clock"></i> Unpaid</span>';
}

/**
 * Flash Notification Messages
 */
function set_flash($type, $message) {
    $_SESSION['flash_message'] = array(
        'type' => $type, // success, error, warning, info
        'message' => $message
    );
}

function get_flash() {
    if (isset($_SESSION['flash_message'])) {
        $flash = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $flash;
    }
    return null;
}

/**
 * Render Flash Alert HTML
 */
function render_flash() {
    $flash = get_flash();
    if (!$flash) return '';
    
    $icon = 'bi-info-circle-fill';
    $class = 'alert-info';
    if ($flash['type'] === 'success') {
        $icon = 'bi-check-circle-fill';
        $class = 'alert-success';
    } elseif ($flash['type'] === 'error') {
        $icon = 'bi-exclamation-triangle-fill';
        $class = 'alert-danger';
    } elseif ($flash['type'] === 'warning') {
        $icon = 'bi-exclamation-circle-fill';
        $class = 'alert-warning';
    }

    return '
    <div class="alert ' . $class . ' alert-dismissible fade show modern-alert" role="alert">
        <i class="' . $icon . ' me-2"></i> ' . $flash['message'] . '
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>';
}

/**
 * Check if Database connection is healthy and prompt if not setup
 */
function render_db_alert_if_needed() {
    if (isset($GLOBALS['db_connection_error'])) {
        $err = $GLOBALS['db_connection_error'];
        return '
        <div class="db-setup-banner">
            <div class="container">
                <div class="db-setup-card">
                    <div class="icon-wrap"><i class="bi bi-database-fill-exclamation"></i></div>
                    <div class="content-wrap">
                        <h4>Database Needs Setup</h4>
                        <p>Database <code>smart_services_db</code> is not connected yet (' . htmlspecialchars($err['message']) . ').</p>
                        <p class="mb-0"><strong>Quick Fix:</strong> Open phpMyAdmin, create database <code>smart_services_db</code>, and import <code>schema.sql</code>.</p>
                    </div>
                </div>
            </div>
        </div>';
    }
    return '';
}
