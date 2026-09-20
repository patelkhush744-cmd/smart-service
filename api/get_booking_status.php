<?php
/**
 * API: Real-time Booking Status Poller
 * Returns JSON with live status, technician details & status badge
 */

header('Content-Type: application/json');
require_once __DIR__ . '/../includes/functions.php';

$code = sanitize(isset($_GET['code']) ? $_GET['code'] : '');
if (empty($code)) {
    echo json_encode(['success' => false, 'message' => 'Missing booking code']);
    exit;
}

$stmt = $conn->prepare("
    SELECT 
        b.id, b.booking_code, b.status, b.payment_status, b.service_date, b.time_slot, b.total_amount,
        s.name AS service_name,
        t.id AS tech_id, t.name AS tech_name, t.phone AS tech_phone, t.rating AS tech_rating, t.avatar AS tech_avatar
    FROM bookings b
    JOIN services s ON b.service_id = s.id
    LEFT JOIN technicians t ON b.technician_id = t.id
    WHERE b.booking_code = ?
");

$stmt->bind_param("s", $code);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Booking not found']);
    exit;
}

$booking = $res->fetch_assoc();

$technician = null;
if (!empty($booking['tech_id'])) {
    $technician = [
        'id' => $booking['tech_id'],
        'name' => $booking['tech_name'],
        'phone' => $booking['tech_phone'],
        'rating' => $booking['tech_rating'],
        'avatar' => $booking['tech_avatar']
    ];
}

echo json_encode([
    'success' => true,
    'booking' => [
        'code' => $booking['booking_code'],
        'status' => $booking['status'],
        'payment_status' => $booking['payment_status'],
        'service_name' => $booking['service_name'],
        'date' => $booking['service_date'],
        'time_slot' => $booking['time_slot'],
        'total' => $booking['total_amount']
    ],
    'technician' => $technician,
    'badge_html' => get_status_badge($booking['status'])
]);
