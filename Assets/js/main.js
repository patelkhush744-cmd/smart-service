/**
 * Smart Service Booking System - Core Frontend JavaScript
 * Interactions, Filters, Booking Wizard & Cart Calculation
 */

document.addEventListener('DOMContentLoaded', () => {
    // 1. Toast Notification Helper
    window.showToast = function(message, type = 'info') {
        let toastContainer = document.getElementById('toast-container');
        if (!toastContainer) {
            toastContainer = document.createElement('div');
            toastContainer.id = 'toast-container';
            toastContainer.style.cssText = 'position:fixed; bottom:24px; right:24px; z-index:9999; display:flex; flex-direction:column; gap:10px;';
            document.body.appendChild(toastContainer);
        }

        const toast = document.createElement('div');
        const bg = type === 'success' ? '#10b981' : (type === 'error' ? '#ef4444' : '#0f172a');
        toast.style.cssText = `background:${bg}; color:#fff; padding:12px 20px; border-radius:10px; font-weight:600; font-size:14px; box-shadow:0 10px 25px rgba(0,0,0,0.15); display:flex; align-items:center; gap:8px; animation:slideUp 0.3s ease;`;
        toast.innerHTML = `<i class="bi bi-bell-fill"></i> <span>${message}</span>`;
        toastContainer.appendChild(toast);

        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transition = 'opacity 0.4s ease';
            setTimeout(() => toast.remove(), 400);
        }, 3500);
    };

    // 2. Client-side Category & Search Filters for services.php
    const categoryChips = document.querySelectorAll('.category-chip-btn');
    const serviceCards = document.querySelectorAll('.service-item-col');
    const serviceSearchInput = document.getElementById('serviceSearchInput');

    function filterServices() {
        const activeChip = document.querySelector('.category-chip-btn.active');
        const selectedCategory = activeChip ? activeChip.getAttribute('data-category') : 'all';
        const searchKeyword = (serviceSearchInput ? serviceSearchInput.value : '').toLowerCase().trim();

        let visibleCount = 0;
        serviceCards.forEach(card => {
            const cardCategory = card.getAttribute('data-category');
            const title = (card.querySelector('.service-title') ? card.querySelector('.service-title').textContent : '').toLowerCase();
            const desc = (card.querySelector('.service-desc') ? card.querySelector('.service-desc').textContent : '').toLowerCase();

            const matchesCategory = (selectedCategory === 'all' || cardCategory === selectedCategory);
            const matchesSearch = !searchKeyword || title.includes(searchKeyword) || desc.includes(searchKeyword);

            if (matchesCategory && matchesSearch) {
                card.style.display = '';
                visibleCount++;
            } else {
                card.style.display = 'none';
            }
        });

        const noResultsEl = document.getElementById('noServicesFound');
        if (noResultsEl) {
            noResultsEl.style.display = visibleCount === 0 ? 'block' : 'none';
        }
    }

    if (categoryChips.length > 0) {
        categoryChips.forEach(chip => {
            chip.addEventListener('click', (e) => {
                e.preventDefault();
                categoryChips.forEach(c => c.classList.remove('active'));
                chip.classList.add('active');
                filterServices();
            });
        });
    }

    if (serviceSearchInput) {
        serviceSearchInput.addEventListener('input', filterServices);
    }

    // 3. Time Slot Selector in Booking Page
    const slotOptions = document.querySelectorAll('.slot-option');
    const hiddenSlotInput = document.getElementById('selectedTimeSlot');

    if (slotOptions.length > 0 && hiddenSlotInput) {
        slotOptions.forEach(slot => {
            slot.addEventListener('click', () => {
                slotOptions.forEach(s => s.classList.remove('selected'));
                slot.classList.add('selected');
                hiddenSlotInput.value = slot.getAttribute('data-slot');
            });
        });
    }

    // 4. Booking Summary Calculation
    const serviceBasePriceEl = document.getElementById('serviceBasePrice');
    const totalAmountDisplay = document.getElementById('totalAmountDisplay');
    const totalAmountInput = document.getElementById('totalAmountInput');
    const addOnCheckboxes = document.querySelectorAll('.addon-checkbox');

    function recalculateTotal() {
        if (!serviceBasePriceEl || !totalAmountDisplay) return;

        let total = parseFloat(serviceBasePriceEl.getAttribute('data-price') || 0);
        addOnCheckboxes.forEach(cb => {
            if (cb.checked) {
                total += parseFloat(cb.getAttribute('data-price') || 0);
            }
        });

        totalAmountDisplay.textContent = '$' + total.toFixed(2);
        if (totalAmountInput) {
            totalAmountInput.value = total.toFixed(2);
        }
    }

    if (addOnCheckboxes.length > 0) {
        addOnCheckboxes.forEach(cb => cb.addEventListener('change', recalculateTotal));
    }
});
