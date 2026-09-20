<?php
/**
 * Customer Portal: Submit Review Handler
 */
require_once __DIR__ . '/../includes/functions.php';
require_auth();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . 'user/my_bookings.php');
    exit;
}

$user = current_user();
$booking_id = isset($_POST['booking_id']) ? (int)$_POST['booking_id'] : 0;
$service_id = isset($_POST['service_id']) ? (int)$_POST['service_id'] : 0;
$booking_code = sanitize(isset($_POST['booking_code']) ? $_POST['booking_code'] : '');
$rating = isset($_POST['rating']) ? (int)$_POST['rating'] : 5;
$comment = sanitize(isset($_POST['comment']) ? $_POST['comment'] : '');

if ($booking_id <= 0 || $service_id <= 0 || empty($comment)) {
    set_flash('error', 'Please provide a valid rating and review comment.');
    header('Location: ' . BASE_URL . 'user/booking_details.php?code=' . urlencode($booking_code));
    exit;
}

// Check if review already exists
$check_stmt = $conn->prepare("SELECT id FROM reviews WHERE booking_id = ?");
$check_stmt->bind_param("i", $booking_id);
$check_stmt->execute();
if ($check_stmt->get_result()->num_rows > 0) {
    set_flash('info', 'You have already submitted a review for this booking.');
    header('Location: ' . BASE_URL . 'user/booking_details.php?code=' . urlencode($booking_code));
    exit;
}

// Insert review
$stmt = $conn->prepare("INSERT INTO reviews (booking_id, user_id, service_id, rating, comment) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("iiiis", $booking_id, $user['id'], $service_id, $rating, $comment);

if ($stmt->execute()) {
    // Recalculate service rating
    $conn->query("
        UPDATE services s
        SET 
            rating = (SELECT AVG(rating) FROM reviews WHERE service_id = $service_id),
            total_reviews = (SELECT COUNT(*) FROM reviews WHERE service_id = $service_id)
        WHERE id = $service_id
    ");

    set_flash('success', 'Thank you for your rating and feedback!');
} else {
    set_flash('error', 'Failed to save review: ' . $conn->error);
}

header('Location: ' . BASE_URL . 'user/booking_details.php?code=' . urlencode($booking_code));
exit;
