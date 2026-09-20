/**
 * Smart Service Booking System - Admin Operations JavaScript
 * Sidebar, AJAX Status Updates & Technician Assignment
 */

document.addEventListener('DOMContentLoaded', () => {
    // 1. Sidebar Toggle on mobile
    const toggleBtn = document.getElementById('sidebarToggleBtn');
    const sidebar = document.querySelector('.admin-sidebar');
    if (toggleBtn && sidebar) {
        toggleBtn.addEventListener('click', () => {
            sidebar.classList.toggle('show');
        });
    }

    // 2. Open Technician Assignment Modal
    const assignModalEl = document.getElementById('assignTechModal');
    let assignModal = null;
    if (assignModalEl && typeof bootstrap !== 'undefined') {
        assignModal = new bootstrap.Modal(assignModalEl);
    }

    document.querySelectorAll('.btn-open-assign-modal').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            const bookingId = btn.getAttribute('data-booking-id');
            const bookingCode = btn.getAttribute('data-booking-code');
            const serviceName = btn.getAttribute('data-service-name');
            const categoryId = btn.getAttribute('data-category-id');

            document.getElementById('modalBookingId').value = bookingId;
            document.getElementById('modalBookingCodeDisplay').textContent = bookingCode;
            document.getElementById('modalServiceNameDisplay').textContent = serviceName;

            // Filter technicians dropdown to matching category if desired
            const techSelect = document.getElementById('modalTechSelect');
            if (techSelect) {
                Array.from(techSelect.options).forEach(opt => {
                    if (!opt.value) return;
                    const optCat = opt.getAttribute('data-category-id');
                    if (!categoryId || optCat === categoryId) {
                        opt.style.display = '';
                    } else {
                        opt.style.display = 'none';
                    }
                });
                techSelect.selectedIndex = 0;
            }

            if (assignModal) assignModal.show();
        });
    });

    // 3. Handle Technician Assignment Form Submission (AJAX)
    const assignForm = document.getElementById('assignTechForm');
    if (assignForm) {
        assignForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(assignForm);
            formData.append('action', 'assign_technician');

            try {
                const res = await fetch('../api/update_booking.php', {
                    method: 'POST',
                    body: formData
                });
                const result = await res.json();
                if (result.success) {
                    alert('Technician assigned successfully! Booking status updated to Pro Assigned.');
                    window.location.reload();
                } else {
                    alert(result.message || 'Failed to assign technician');
                }
            } catch (err) {
                console.error(err);
                alert('Connection error while assigning technician.');
            }
        });
    }

    // 4. Fast Inline Status Update
    document.querySelectorAll('.status-quick-change').forEach(select => {
        select.addEventListener('change', async () => {
            const bookingId = select.getAttribute('data-booking-id');
            const newStatus = select.value;

            if (!confirm(`Are you sure you want to change booking status to "${newStatus}"?`)) {
                select.value = select.getAttribute('data-current');
                return;
            }

            const formData = new FormData();
            formData.append('action', 'update_status');
            formData.append('booking_id', bookingId);
            formData.append('status', newStatus);

            try {
                const res = await fetch('../api/update_booking.php', {
                    method: 'POST',
                    body: formData
                });
                const result = await res.json();
                if (result.success) {
                    select.setAttribute('data-current', newStatus);
                    // Flash feedback
                    select.style.backgroundColor = '#ecfdf5';
                    setTimeout(() => select.style.backgroundColor = '', 1200);
                } else {
                    alert(result.message || 'Status update failed.');
                    select.value = select.getAttribute('data-current');
                }
            } catch (err) {
                console.error(err);
                alert('Error connecting to server.');
                select.value = select.getAttribute('data-current');
            }
        });
    });
});
