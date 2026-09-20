<?php
/**
 * Global Footer Component
 * Smart Service Booking System
 */
?>
</main>

<footer class="uber-footer">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-4 col-md-6">
                <div class="d-flex align-items-center gap-2 mb-3">
                    <span class="fs-4 fw-bold text-white"><i class="bi bi-shield-check text-primary"></i> <?= SITE_NAME ?></span>
                    <span class="badge bg-white text-dark rounded-pill">HOME SERVICES</span>
                </div>
                <p class="text-secondary small mb-4" style="line-height: 1.7;">
                    Experience professional, reliable home maintenance at the tap of a button. Verified technicians, upfront pricing, and real-time live service tracking.
                </p>
                <div class="d-flex gap-3">
                    <a href="#" class="text-secondary fs-5"><i class="bi bi-facebook"></i></a>
                    <a href="#" class="text-secondary fs-5"><i class="bi bi-instagram"></i></a>
                    <a href="#" class="text-secondary fs-5"><i class="bi bi-twitter-x"></i></a>
                    <a href="#" class="text-secondary fs-5"><i class="bi bi-linkedin"></i></a>
                </div>
            </div>

            <div class="col-lg-2 col-md-6 col-6">
                <h5>Categories</h5>
                <ul>
                    <li><a href="<?= BASE_URL ?>services.php?category=ac-appliances">AC & Appliances</a></li>
                    <li><a href="<?= BASE_URL ?>services.php?category=electrician">Electricians</a></li>
                    <li><a href="<?= BASE_URL ?>services.php?category=plumber">Plumbing Services</a></li>
                    <li><a href="<?= BASE_URL ?>services.php?category=cleaning">Deep Cleaning</a></li>
                    <li><a href="<?= BASE_URL ?>services.php?category=salon">Home Salon</a></li>
                </ul>
            </div>

            <div class="col-lg-3 col-md-6 col-6">
                <h5>Platform</h5>
                <ul>
                    <li><a href="<?= BASE_URL ?>services.php">All Services</a></li>
                    <li><a href="<?= BASE_URL ?>user/my_bookings.php">My Bookings</a></li>
                    <li><a href="<?= BASE_URL ?>auth/login.php">Customer Login</a></li>
                    <li><a href="<?= BASE_URL ?>admin/index.php">Admin Portal</a></li>
                    <li><a href="<?= BASE_URL ?>README.md" target="_blank">Documentation</a></li>
                </ul>
            </div>

            <div class="col-lg-3 col-md-6">
                <h5>Customer Trust</h5>
                <div class="p-3 rounded-3 mb-3" style="background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1);">
                    <div class="d-flex align-items-center gap-2 mb-2 text-white">
                        <i class="bi bi-patch-check-fill text-primary"></i>
                        <span class="fw-bold">100% Quality Assured</span>
                    </div>
                    <p class="text-secondary small mb-0">Free rework warranty on every completed job if not satisfied.</p>
                </div>
                <div class="small text-secondary">
                    <i class="bi bi-telephone-inbound me-1"></i> Helpline: +1 (800) 555-UBERPRO
                </div>
            </div>
        </div>

        <div class="footer-bottom">
            <div>
                &copy; <?= date('Y') ?> <?= SITE_NAME ?> Inc. All rights reserved. Crafted with PHP, MySQLi, JavaScript & CSS.
            </div>
            <div class="d-flex gap-3">
                <a href="#" class="text-secondary">Privacy Policy</a>
                <a href="#" class="text-secondary">Terms of Service</a>
                <a href="#" class="text-secondary">Safety Guidelines</a>
            </div>
        </div>
    </div>
</footer>

<!-- Bootstrap 5 JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<!-- Leaflet JS for Maps -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<!-- Main App Script -->
<script src="<?= BASE_URL ?>assets/js/main.js"></script>

</body>
</html>
