<?php
/**
 * Smart Service Booking System - Uber-Style Multi-Step Booking Page
 */
require_once __DIR__ . '/includes/header.php';

// Auth check - if guest, show login invitation
$logged_in = is_logged_in();
$user = current_user();

$service_id = isset($_GET['service_id']) ? (int)$_GET['service_id'] : 1;

// Fetch selected service
$stmt = $conn->prepare("
    SELECT s.*, c.name AS category_name, c.icon AS category_icon
    FROM services s
    JOIN categories c ON s.category_id = c.id
    WHERE s.id = ? AND s.status = 'active'
");
$stmt->bind_param("i", $service_id);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 0) {
    // Fallback to first available service
    $res = $conn->query("SELECT s.*, c.name AS category_name, c.icon AS category_icon FROM services s JOIN categories c ON s.category_id = c.id WHERE s.status = 'active' LIMIT 1");
}
$service = $res->fetch_assoc();
?>

<div class="py-5">
    <div class="container">
        <!-- Header -->
        <div class="mb-4">
            <h1 class="fw-bold fs-2 mb-1">Confirm Service Booking</h1>
            <p class="text-muted">Instant matching with nearby certified professionals.</p>
        </div>

        <?php if (!$logged_in): ?>
            <div class="alert alert-warning border-0 rounded-4 shadow-sm p-4 mb-4 d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <h5 class="fw-bold mb-1"><i class="bi bi-person-lock me-2"></i>Sign in to complete your booking</h5>
                    <p class="mb-0 text-muted small">You can sign in with your account or use the 1-click Demo Customer button.</p>
                </div>
                <div>
                    <a href="<?= BASE_URL ?>auth/login.php?redirect=book.php?service_id=<?= (int)$service['id'] ?>" class="btn btn-dark rounded-pill px-4 fw-bold">
                        Login / Sign Up
                    </a>
                </div>
            </div>
        <?php endif; ?>

        <form id="serviceBookingForm">
            <input type="hidden" name="service_id" value="<?= (int)$service['id'] ?>">
            <input type="hidden" name="latitude" id="inputLat" value="40.7128">
            <input type="hidden" name="longitude" id="inputLng" value="-74.0060">
            <input type="hidden" name="total_amount" id="totalAmountInput" value="<?= $service['price'] ?>">
            <input type="hidden" name="time_slot" id="selectedTimeSlot" value="09:00 AM - 11:00 AM">

            <div class="row g-4">
                <!-- Left Booking Steps Column -->
                <div class="col-lg-8">
                    <!-- Step 1: Service Chosen & Addons -->
                    <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="fw-bold mb-0">1. Selected Home Service</h5>
                            <a href="<?= BASE_URL ?>services.php" class="small fw-semibold text-primary">Change Service</a>
                        </div>
                        <div class="p-3 bg-light rounded-3 d-flex justify-content-between align-items-center flex-wrap gap-3">
                            <div class="d-flex align-items-center gap-3">
                                <div class="bg-white p-3 rounded-3 shadow-sm text-primary fs-3">
                                    <i class="bi <?= htmlspecialchars($service['category_icon']) ?>"></i>
                                </div>
                                <div>
                                    <h5 class="fw-bold mb-1"><?= htmlspecialchars($service['name']) ?></h5>
                                    <span class="badge bg-secondary-subtle text-secondary rounded-pill me-2"><?= htmlspecialchars($service['category_name']) ?></span>
                                    <span class="small text-muted"><i class="bi bi-clock"></i> <?= (int)$service['duration_mins'] ?> mins</span>
                                </div>
                            </div>
                            <div class="text-end">
                                <div class="fs-4 fw-extrabold text-dark" id="serviceBasePrice" data-price="<?= $service['price'] ?>">
                                    <?= format_currency($service['price']) ?>
                                </div>
                                <span class="small text-muted">Base Fare</span>
                            </div>
                        </div>

                        <!-- Optional Addons -->
                        <div class="mt-4">
                            <h6 class="fw-bold mb-2">Recommended Add-Ons</h6>
                            <div class="row g-2">
                                <div class="col-md-6">
                                    <label class="p-3 border rounded-3 d-flex align-items-center justify-content-between w-100 cursor-pointer">
                                        <div class="d-flex align-items-center gap-2">
                                            <input type="checkbox" class="form-check-input addon-checkbox" data-price="12.00" value="Anti-Bacterial Sanitize">
                                            <span class="small fw-semibold">Anti-Bacterial Sanitize</span>
                                        </div>
                                        <span class="small fw-bold text-success">+&#8377;12.00</span>
                                    </label>
                                </div>
                                <div class="col-md-6">
                                    <label class="p-3 border rounded-3 d-flex align-items-center justify-content-between w-100 cursor-pointer">
                                        <div class="d-flex align-items-center gap-2">
                                            <input type="checkbox" class="form-check-input addon-checkbox" data-price="18.00" value="Extended 60-Day Warranty">
                                            <span class="small fw-semibold">Extended 60-Day Warranty</span>
                                        </div>
                                        <span class="small fw-bold text-success">+&#8377;18.00</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Step 2: Date & Preferred Time Window -->
                    <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white">
                        <h5 class="fw-bold mb-3">2. Schedule Date & Time Slot</h5>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted">Service Date</label>
                                <input type="date" name="service_date" class="form-control py-2 rounded-3" value="<?= date('Y-m-d') ?>" min="<?= date('Y-m-d') ?>" required>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label small fw-bold text-muted">Select Arrival Window</label>
                                <div class="slot-grid">
                                    <div class="slot-option selected" data-slot="09:00 AM - 11:00 AM">
                                        <i class="bi bi-sun me-1"></i> 09:00 AM - 11:00 AM
                                    </div>
                                    <div class="slot-option" data-slot="11:00 AM - 01:00 PM">
                                        <i class="bi bi-brightness-high me-1"></i> 11:00 AM - 01:00 PM
                                    </div>
                                    <div class="slot-option" data-slot="02:00 PM - 04:00 PM">
                                        <i class="bi bi-clock me-1"></i> 02:00 PM - 04:00 PM
                                    </div>
                                    <div class="slot-option" data-slot="05:00 PM - 07:00 PM">
                                        <i class="bi bi-moon-stars me-1"></i> 05:00 PM - 07:00 PM
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Step 3: Service Address & Interactive Map -->
                    <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white">
                        <h5 class="fw-bold mb-3">3. Service Location & Address</h5>
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">Doorstep Address</label>
                            <textarea name="service_address" class="form-control rounded-3" rows="3" placeholder="Enter flat/house no, street, building name, landmark, and city" required><?= htmlspecialchars(isset($user['address']) ? $user['address'] : '425 Grand Avenue, Apartment 3B, New York, NY') ?></textarea>
                        </div>
                        <div class="mb-2">
                            <label class="form-label small fw-bold text-muted d-flex justify-content-between">
                                <span><i class="bi bi-pin-map-fill text-danger me-1"></i> Pin on Map (Click map or drag pin to adjust)</span>
                                <span class="text-primary small" id="latLngDisplay">40.7128, -74.0060</span>
                            </label>
                            <div id="bookingMap"></div>
                        </div>
                    </div>

                    <!-- Step 4: Payment Method & Special Notes -->
                    <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
                        <h5 class="fw-bold mb-3">4. Payment & Instructions</h5>
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">Select Payment Option</label>
                            <div class="row g-2">
                                <div class="col-md-4">
                                    <label class="p-3 border rounded-3 d-flex align-items-center gap-2 cursor-pointer w-100">
                                        <input type="radio" name="payment_method" value="cash" checked class="form-check-input">
                                        <div>
                                            <div class="fw-bold small">Cash After Service</div>
                                            <div class="text-muted" style="font-size: 11px;">Pay technician directly</div>
                                        </div>
                                    </label>
                                </div>
                                <div class="col-md-4">
                                    <label class="p-3 border rounded-3 d-flex align-items-center gap-2 cursor-pointer w-100">
                                        <input type="radio" name="payment_method" value="online" class="form-check-input">
                                        <div>
                                            <div class="fw-bold small">Instant Online (Simulated)</div>
                                            <div class="text-muted" style="font-size: 11px;">UPI / Net Banking</div>
                                        </div>
                                    </label>
                                </div>
                                <div class="col-md-4">
                                    <label class="p-3 border rounded-3 d-flex align-items-center gap-2 cursor-pointer w-100">
                                        <input type="radio" name="payment_method" value="card" class="form-check-input">
                                        <div>
                                            <div class="fw-bold small">Credit / Debit Card</div>
                                            <div class="text-muted" style="font-size: 11px;">Prepaid security</div>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="form-label small fw-bold text-muted">Instructions for Technician (Optional)</label>
                            <input type="text" name="special_notes" class="form-control rounded-3" placeholder="e.g. Ring bell twice, gate passcode #4021, pet inside">
                        </div>
                    </div>
                </div>

                <!-- Right Sticky Summary & Order Confirmation -->
                <div class="col-lg-4">
                    <div class="card border-0 shadow-lg rounded-4 p-4 sticky-top bg-white" style="top: 100px;">
                        <h5 class="fw-bold mb-3">Fare Summary</h5>

                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Service Base Fare:</span>
                            <span class="fw-bold"><?= format_currency($service['price']) ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Safety & Verification Fee:</span>
                            <span class="fw-semibold text-success">FREE</span>
                        </div>
                        <div class="d-flex justify-content-between mb-3 pb-3 border-bottom">
                            <span class="text-muted">Taxes & Consumables:</span>
                            <span class="fw-semibold">&#8377;0.00</span>
                        </div>

                        <div class="d-flex justify-content-between align-items-baseline mb-4">
                            <span class="fw-bold fs-5">Estimated Total:</span>
                            <span class="fs-2 fw-extrabold text-dark" id="totalAmountDisplay"><?= format_currency($service['price']) ?></span>
                        </div>

                        <div class="p-3 bg-light rounded-3 small text-muted mb-4">
                            <div class="d-flex align-items-center gap-2 mb-1 text-dark fw-bold">
                                <i class="bi bi-shield-lock-fill text-success"></i> Uber-Style Safety Commitment
                            </div>
                            Transparent quote. No surprise surcharges upon arrival.
                        </div>

                        <div class="d-grid">
                            <button type="submit" id="btnSubmitBooking" class="btn btn-dark btn-lg rounded-pill fw-bold py-3 shadow">
                                <i class="bi bi-lightning-fill text-warning me-1"></i> Request Service Now
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // Initialize interactive Leaflet Map for pin location
    let defaultLat = 40.7128;
    let defaultLng = -74.0060;

    const map = L.map('bookingMap').setView([defaultLat, defaultLng], 13);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap'
    }).addTo(map);

    let marker = L.marker([defaultLat, defaultLng], { draggable: true }).addTo(map);

    function updateMarker(lat, lng) {
        document.getElementById('inputLat').value = lat.toFixed(6);
        document.getElementById('inputLng').value = lng.toFixed(6);
        document.getElementById('latLngDisplay').textContent = `${lat.toFixed(4)}, ${lng.toFixed(4)}`;
    }

    marker.on('dragend', function(e) {
        const pos = e.target.getLatLng();
        updateMarker(pos.lat, pos.lng);
    });

    map.on('click', function(e) {
        marker.setLatLng(e.latlng);
        updateMarker(e.latlng.lat, e.latlng.lng);
    });

    // Handle Form Submission via AJAX
    const bookingForm = document.getElementById('serviceBookingForm');
    const submitBtn = document.getElementById('btnSubmitBooking');

    bookingForm.addEventListener('submit', async (e) => {
        e.preventDefault();

        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Matching Technician...';

        const formData = new FormData(bookingForm);

        try {
            const response = await fetch('<?= BASE_URL ?>api/book_service.php', {
                method: 'POST',
                body: formData
            });
            const res = await response.json();

            if (res.success) {
                window.location.href = res.redirect;
            } else {
                if (res.redirect) {
                    window.location.href = res.redirect;
                } else {
                    alert(res.message || 'Booking submission error');
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<i class="bi bi-lightning-fill text-warning me-1"></i> Request Service Now';
                }
            }
        } catch (err) {
            console.error(err);
            alert('Failed to connect to server. Please check your network.');
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="bi bi-lightning-fill text-warning me-1"></i> Request Service Now';
        }
    });
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
