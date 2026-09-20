/**
 * Smart Service Booking System - Real-time Uber-Style Service Tracker
 * Live Stepper, Leaflet Map Simulation & Status Poller
 */

document.addEventListener('DOMContentLoaded', () => {
    const trackerContainer = document.getElementById('uberTrackerContainer');
    if (!trackerContainer) return;

    const bookingCode = trackerContainer.getAttribute('data-booking-code');
    const initialStatus = trackerContainer.getAttribute('data-status');
    const userLat = parseFloat(trackerContainer.getAttribute('data-lat') || 40.7128);
    const userLng = parseFloat(trackerContainer.getAttribute('data-lng') || -74.0060);

    // 1. Initialize Map via Leaflet if container exists
    let map = null;
    let userMarker = null;
    let techMarker = null;

    if (document.getElementById('liveServiceMap') && typeof L !== 'undefined') {
        try {
            map = L.map('liveServiceMap').setView([userLat, userLng], 14);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap contributors',
                maxZoom: 19
            }).addTo(map);

            // User Destination Pin (Home)
            const homeIcon = L.divIcon({
                className: 'custom-map-pin home-pin',
                html: '<div style="background:#000; color:#fff; width:34px; height:34px; border-radius:50%; display:flex; align-items:center; justify-content:center; box-shadow:0 4px 10px rgba(0,0,0,0.3);"><i class="bi bi-house-door-fill"></i></div>',
                iconSize: [34, 34],
                iconAnchor: [17, 17]
            });
            userMarker = L.marker([userLat, userLng], { icon: homeIcon }).addTo(map)
                .bindPopup('<b>Service Location</b><br>Your Home Address').openPopup();

            // Pro / Technician Pin (Moving vehicle / technician)
            const techIcon = L.divIcon({
                className: 'custom-map-pin tech-pin',
                html: '<div style="background:#2563eb; color:#fff; width:34px; height:34px; border-radius:50%; display:flex; align-items:center; justify-content:center; box-shadow:0 4px 10px rgba(37,99,235,0.4);"><i class="bi bi-wrench-adjustable"></i></div>',
                iconSize: [34, 34],
                iconAnchor: [17, 17]
            });

            // Offset slightly to simulate technician en route
            const techLat = userLat + 0.008;
            const techLng = userLng + 0.007;
            techMarker = L.marker([techLat, techLng], { icon: techIcon }).addTo(map)
                .bindPopup('<b>Assigned Pro</b><br>On the way to your location');

            // Draw simulated route
            const latlngs = [
                [techLat, techLng],
                [userLat + 0.004, userLng + 0.003],
                [userLat, userLng]
            ];
            const polyline = L.polyline(latlngs, { color: '#2563eb', weight: 4, dashArray: '6, 8' }).addTo(map);
            map.fitBounds(polyline.getBounds(), { padding: [40, 40] });

        } catch (e) {
            console.warn('Map initialization error:', e);
        }
    }

    // 2. Status Stepper Mapping
    const statusOrder = ['pending', 'confirmed', 'technician_assigned', 'in_progress', 'completed'];

    function updateStepperUI(status) {
        const currentIdx = statusOrder.indexOf(status);
        const progressFill = document.getElementById('stepperFill');
        const items = document.querySelectorAll('.stepper-item');

        if (status === 'cancelled') {
            if (progressFill) progressFill.style.width = '0%';
            items.forEach(it => {
                it.classList.remove('active', 'completed');
            });
            return;
        }

        if (progressFill && currentIdx >= 0) {
            const pct = (currentIdx / (statusOrder.length - 1)) * 100;
            progressFill.style.width = `${pct}%`;
        }

        items.forEach(it => {
            const stepName = it.getAttribute('data-step');
            const stepIdx = statusOrder.indexOf(stepName);

            it.classList.remove('active', 'completed');
            if (stepIdx < currentIdx) {
                it.classList.add('completed');
            } else if (stepIdx === currentIdx) {
                it.classList.add('active');
            }
        });
    }

    updateStepperUI(initialStatus);

    // 3. Polling live status from API
    let poller = setInterval(async () => {
        try {
            const res = await fetch(`../api/get_booking_status.php?code=${encodeURIComponent(bookingCode)}`);
            if (!res.ok) return;
            const data = await res.json();

            if (data && data.success && data.booking) {
                const newStatus = data.booking.status;
                const statusBadgeEl = document.getElementById('currentStatusBadge');
                if (statusBadgeEl && data.badge_html) {
                    statusBadgeEl.innerHTML = data.badge_html;
                }

                updateStepperUI(newStatus);

                // Update technician details if newly assigned
                const techCard = document.getElementById('technicianCardContainer');
                if (techCard && data.technician) {
                    techCard.style.display = 'block';
                    document.getElementById('techNameDisplay').textContent = data.technician.name;
                    document.getElementById('techPhoneDisplay').href = `tel:${data.technician.phone}`;
                    document.getElementById('techRatingDisplay').textContent = data.technician.rating;
                }

                // If completed, show review modal if not rated yet
                if (newStatus === 'completed') {
                    const reviewSection = document.getElementById('reviewSection');
                    if (reviewSection) reviewSection.style.display = 'block';
                    clearInterval(poller); // Stop polling when completed
                } else if (newStatus === 'cancelled') {
                    clearInterval(poller);
                }
            }
        } catch (err) {
            console.log('Status polling...', err);
        }
    }, 7000);
});
