<?php
/**
 * Database Configuration & MySQLi Connection
 * Smart Service Booking System
 */

// Database Credentials
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'smart_services_db');

// App Settings
define('SITE_NAME', 'SmartService');
define('SITE_TAGLINE', 'Uber for Home Services');
define('CURRENCY_SYMBOL', '₹');

// Detect Base URL dynamically
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
$host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';
$script_name = str_replace('\\', '/', dirname(isset($_SERVER['SCRIPT_NAME']) ? $_SERVER['SCRIPT_NAME'] : ''));
// Normalize base path
$base_dir = preg_replace('#/(admin|user|auth|api|includes).*$#', '', $script_name);
$base_url = rtrim($protocol . $host . $base_dir, '/') . '/';
define('BASE_URL', $base_url);

// Initialize MySQLi
mysqli_report(MYSQLI_REPORT_OFF); // Graceful error management
$conn = @new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if ($conn->connect_error) {
    // If DB doesn't exist, try connecting to MySQL server to notify gracefully
    $temp_conn = @new mysqli(DB_HOST, DB_USER, DB_PASS);
    $db_missing = ($temp_conn && !$temp_conn->connect_error);
    if ($temp_conn) $temp_conn->close();

    // Store connection error for display in friendly banner
    $GLOBALS['db_connection_error'] = array(
        'message' => $conn->connect_error,
        'code' => $conn->connect_errno,
        'db_missing' => $db_missing
    );
} else {
    $conn->set_charset("utf8mb4");
}

// Start PHP session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
