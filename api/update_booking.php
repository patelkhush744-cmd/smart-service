<?php
/**
 * API: Update Booking Actions
 * Admin status updates & Technician assignment, plus Customer cancellation
 */

header('Content-Type: application/json');
require_once __DIR__ . '/../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

if (!is_logged_in()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$action = sanitize(isset($_POST['action']) ? $_POST['action'] : '');
$booking_id = isset($_POST['booking_id']) ? (int)$_POST['booking_id'] : 0;

if ($booking_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid booking ID']);
    exit;
}

// 1. Customer cancellation flow
if ($action === 'cancel_by_user') {
    $user = current_user();
    // Verify booking belongs to this user and is still in pending/confirmed state
    $stmt = $conn->prepare("SELECT id, status FROM bookings WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $booking_id, $user['id']);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Booking not found or not owned by you.']);
        exit;
    }

    $b = $res->fetch_assoc();
    if ($b['status'] === 'in_progress' || $b['status'] === 'completed') {
        echo json_encode(['success' => false, 'message' => 'Cannot cancel a service that is already in progress or completed.']);
        exit;
    }

    $cancel_stmt = $conn->prepare("UPDATE bookings SET status = 'cancelled' WHERE id = ?");
    $cancel_stmt->bind_param("i", $booking_id);
    if ($cancel_stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Booking cancelled successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to cancel booking.']);
    }
    exit;
}

// All subsequent actions require Admin permission
if (!is_admin()) {
    echo json_encode(['success' => false, 'message' => 'Admin permissions required.']);
    exit;
}

// 2. Admin: Assign Technician
if ($action === 'assign_technician') {
    $tech_id = isset($_POST['technician_id']) ? (int)$_POST['technician_id'] : 0;
    if ($tech_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Please select a technician']);
        exit;
    }

    // Assign technician and set status to technician_assigned
    $stmt = $conn->prepare("
        UPDATE bookings 
        SET technician_id = ?, status = 'technician_assigned' 
        WHERE id = ?
    ");
    $stmt->bind_param("ii", $tech_id, $booking_id);

    if ($stmt->execute()) {
        // Also mark technician as busy
        $conn->query("UPDATE technicians SET status = 'busy' WHERE id = " . (int)$tech_id);
        echo json_encode(['success' => true, 'message' => 'Technician assigned successfully!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to assign technician']);
    }
    exit;
}

// 3. Admin: Update Status
if ($action === 'update_status') {
    $new_status = sanitize(isset($_POST['status']) ? $_POST['status'] : '');
    $valid_statuses = ['pending', 'confirmed', 'technician_assigned', 'in_progress', 'completed', 'cancelled'];

    if (!in_array($new_status, $valid_statuses)) {
        echo json_encode(['success' => false, 'message' => 'Invalid status requested']);
        exit;
    }

    $stmt = $conn->prepare("UPDATE bookings SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $new_status, $booking_id);

    if ($stmt->execute()) {
        // If completed or cancelled, free up technician
        if ($new_status === 'completed' || $new_status === 'cancelled') {
            $conn->query("
                UPDATE technicians t 
                JOIN bookings b ON b.technician_id = t.id 
                SET t.status = 'available', t.total_jobs = t.total_jobs + 1 
                WHERE b.id = " . (int)$booking_id
            );
        }
        echo json_encode(['success' => true, 'message' => 'Status updated successfully!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update status']);
    }
    exit;
}

// 4. Admin: Update Payment Status
if ($action === 'update_payment') {
    $payment_status = sanitize(isset($_POST['payment_status']) ? $_POST['payment_status'] : 'pending');
    $stmt = $conn->prepare("UPDATE bookings SET payment_status = ? WHERE id = ?");
    $stmt->bind_param("si", $payment_status, $booking_id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Payment status updated!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Update failed']);
    }
    exit;
}

echo json_encode(['success' => false, 'message' => 'Unknown action']);
