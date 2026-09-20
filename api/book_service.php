<?php
/**
 * API: Book Service Endpoint
 * Creates new booking and returns booking code for tracking
 */

header('Content-Type: application/json');
require_once __DIR__ . '/../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

if (!is_logged_in()) {
    echo json_encode([
        'success' => false, 
        'message' => 'Please login to confirm your service booking.',
        'redirect' => BASE_URL . 'auth/login.php'
    ]);
    exit;
}

$user = current_user();
$service_id = isset($_POST['service_id']) ? (int)$_POST['service_id'] : 0;
$service_date = sanitize(isset($_POST['service_date']) ? $_POST['service_date'] : '');
$time_slot = sanitize(isset($_POST['time_slot']) ? $_POST['time_slot'] : '');
$service_address = sanitize(isset($_POST['service_address']) ? $_POST['service_address'] : '');
$special_notes = sanitize(isset($_POST['special_notes']) ? $_POST['special_notes'] : '');
$payment_method = sanitize(isset($_POST['payment_method']) ? $_POST['payment_method'] : 'cash');
$total_amount = isset($_POST['total_amount']) ? (float)$_POST['total_amount'] : 0.00;
$latitude = isset($_POST['latitude']) && is_numeric($_POST['latitude']) ? (float)$_POST['latitude'] : 40.7128;
$longitude = isset($_POST['longitude']) && is_numeric($_POST['longitude']) ? (float)$_POST['longitude'] : -74.0060;

// Validations
if ($service_id <= 0 || empty($service_date) || empty($time_slot) || empty($service_address)) {
    echo json_encode(['success' => false, 'message' => 'Please fill in all required fields (Date, Slot, Address).']);
    exit;
}

// Verify service exists
$stmt = $conn->prepare("SELECT id, name, price FROM services WHERE id = ? AND status = 'active'");
$stmt->bind_param("i", $service_id);
$stmt->execute();
$service_res = $stmt->get_result();

if ($service_res->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'The selected service is no longer available.']);
    exit;
}
$service = $service_res->fetch_assoc();
if ($total_amount <= 0) {
    $total_amount = (float)$service['price'];
}

$booking_code = generate_booking_code();
$status = 'pending';
$payment_status = ($payment_method === 'online' || $payment_method === 'card') ? 'paid' : 'pending';

// Insert booking
$insert_query = "INSERT INTO bookings 
    (booking_code, user_id, service_id, service_date, time_slot, status, total_amount, payment_method, payment_status, service_address, latitude, longitude, special_notes) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$insert_stmt = $conn->prepare($insert_query);
$insert_stmt->bind_param(
    "siisssdsssdds",
    $booking_code,
    $user['id'],
    $service_id,
    $service_date,
    $time_slot,
    $status,
    $total_amount,
    $payment_method,
    $payment_status,
    $service_address,
    $latitude,
    $longitude,
    $special_notes
);

if ($insert_stmt->execute()) {
    echo json_encode([
        'success' => true,
        'message' => 'Service booked successfully!',
        'booking_code' => $booking_code,
        'redirect' => BASE_URL . 'user/booking_details.php?code=' . $booking_code
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to record booking: ' . $conn->error
    ]);
}
