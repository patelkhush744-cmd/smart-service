-- ==========================================================
-- SMART SERVICE BOOKING SYSTEM (UBER FOR HOME SERVICES)
-- Complete Database Schema & Seed Data
-- ==========================================================

CREATE DATABASE IF NOT EXISTS `smart_services_db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `smart_services_db`;

-- Drop tables if they exist (clean setup)
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS `reviews`;
DROP TABLE IF EXISTS `bookings`;
DROP TABLE IF EXISTS `technicians`;
DROP TABLE IF EXISTS `services`;
DROP TABLE IF EXISTS `categories`;
DROP TABLE IF EXISTS `users`;
SET FOREIGN_KEY_CHECKS = 1;

-- ----------------------------------------------------------
-- 1. Table: users (Customers and Administrators)
-- ----------------------------------------------------------
CREATE TABLE `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(120) NOT NULL,
  `email` VARCHAR(150) NOT NULL UNIQUE,
  `phone` VARCHAR(30) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `role` ENUM('customer', 'admin') DEFAULT 'customer',
  `address` TEXT NULL,
  `avatar` VARCHAR(255) DEFAULT 'default_user.png',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------------------------
-- 2. Table: categories (Home Service Domains)
-- ----------------------------------------------------------
CREATE TABLE `categories` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `slug` VARCHAR(120) NOT NULL UNIQUE,
  `icon` VARCHAR(50) NOT NULL,
  `badge` VARCHAR(50) DEFAULT 'Popular',
  `description` TEXT,
  `status` ENUM('active', 'inactive') DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------------------------
-- 3. Table: services (Individual bookable services)
-- ----------------------------------------------------------
CREATE TABLE `services` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `category_id` INT NOT NULL,
  `name` VARCHAR(150) NOT NULL,
  `description` TEXT,
  `price` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `duration_mins` INT NOT NULL DEFAULT 60,
  `rating` DECIMAL(3,2) DEFAULT 4.80,
  `total_reviews` INT DEFAULT 0,
  `status` ENUM('active', 'inactive') DEFAULT 'active',
  `image` VARCHAR(255) DEFAULT 'service_default.jpg',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------------------------
-- 4. Table: technicians (Service Pros / Partners like Uber Drivers)
-- ----------------------------------------------------------
CREATE TABLE `technicians` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(120) NOT NULL,
  `phone` VARCHAR(30) NOT NULL,
  `email` VARCHAR(150) NOT NULL,
  `category_id` INT NOT NULL,
  `rating` DECIMAL(3,2) DEFAULT 4.90,
  `total_jobs` INT DEFAULT 0,
  `status` ENUM('available', 'busy', 'offline') DEFAULT 'available',
  `avatar` VARCHAR(255) DEFAULT 'tech_default.png',
  `current_lat` DECIMAL(10, 7) DEFAULT 40.7128,
  `current_lng` DECIMAL(10, 7) DEFAULT -74.0060,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------------------------
-- 5. Table: bookings (Uber-style service orders)
-- ----------------------------------------------------------
CREATE TABLE `bookings` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `booking_code` VARCHAR(30) NOT NULL UNIQUE,
  `user_id` INT NOT NULL,
  `service_id` INT NOT NULL,
  `technician_id` INT NULL,
  `service_date` DATE NOT NULL,
  `time_slot` VARCHAR(50) NOT NULL,
  `status` ENUM('pending', 'confirmed', 'technician_assigned', 'in_progress', 'completed', 'cancelled') DEFAULT 'pending',
  `total_amount` DECIMAL(10,2) NOT NULL,
  `payment_method` ENUM('cash', 'online', 'card') DEFAULT 'cash',
  `payment_status` ENUM('pending', 'paid', 'refunded') DEFAULT 'pending',
  `service_address` TEXT NOT NULL,
  `latitude` DECIMAL(10,7) NULL,
  `longitude` DECIMAL(10,7) NULL,
  `special_notes` TEXT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`service_id`) REFERENCES `services`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`technician_id`) REFERENCES `technicians`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------------------------
-- 6. Table: reviews (Ratings & Testimonials)
-- ----------------------------------------------------------
CREATE TABLE `reviews` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `booking_id` INT NOT NULL,
  `user_id` INT NOT NULL,
  `service_id` INT NOT NULL,
  `rating` INT NOT NULL CHECK (`rating` BETWEEN 1 AND 5),
  `comment` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`booking_id`) REFERENCES `bookings`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`service_id`) REFERENCES `services`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ==========================================================
-- SEED DATA
-- ==========================================================

-- Seed Users (Passwords: admin123, customer123 using bcrypt)
INSERT INTO `users` (`id`, `name`, `email`, `phone`, `password`, `role`, `address`) VALUES
(1, 'Admin Commander', 'admin@smartservice.com', '+1 800 555 0199', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'Headquarters, Suite 400, Tech Plaza'),
(2, 'Sarah Jenkins', 'customer@demo.com', '+1 555 234 5678', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', '425 Grand Avenue, Apartment 3B, New York, NY');

-- Seed Categories
INSERT INTO `categories` (`id`, `name`, `slug`, `icon`, `badge`, `description`) VALUES
(1, 'AC & Appliance Repair', 'ac-appliances', 'bi-snow', 'Hot Deal', 'Deep jet cleaning, gas refill, compressor inspection & repairs.'),
(2, 'Electrical Services', 'electrician', 'bi-lightning-charge', 'Popular', 'Wiring fixes, switchboards, appliance installations & inverter setups.'),
(3, 'Plumbing & Water', 'plumber', 'bi-droplet-half', 'Fast 30m', 'Leakage detection, pipe repairs, tap replacements, bath fitting.'),
(4, 'Deep Home Cleaning', 'cleaning', 'bi-stars', 'Bestseller', 'Full home sanitization, kitchen deep scrubbing, sofa & carpet shampoo.'),
(5, 'Painting & Water Proof', 'painting', 'bi-brush', 'Trending', 'Waterproofing, room makeover, wall crack treatment, texture painting.'),
(6, 'Home Salon & Spa', 'salon', 'bi-scissors', 'Luxury', 'Hair styling, relaxing facial, grooming & body massage at your doorstep.');

-- Seed Services
INSERT INTO `services` (`id`, `category_id`, `name`, `description`, `price`, `duration_mins`, `rating`, `total_reviews`, `image`) VALUES
(1, 1, 'AC Foam Jet Servicing', 'High-pressure foam wash for indoor & outdoor units. Boosts cooling by up to 40%.', 49.00, 60, 4.90, 142, 'ac_foam.jpg'),
(2, 1, 'AC Gas Leak Check & Refill', 'Complete pressure check, leak detection with brazing, and 100% genuine gas recharge.', 75.00, 90, 4.85, 88, 'ac_gas.jpg'),
(3, 1, 'Washing Machine Repair', 'Front & top load diagnostics, motor repair, belt change, and vibration fixing.', 39.00, 45, 4.78, 64, 'wm_repair.jpg'),
(4, 2, 'Full Home Electrical Audit', 'Comprehensive safety inspection of distribution box, earthing, load test & shorts.', 45.00, 60, 4.92, 98, 'elec_audit.jpg'),
(5, 2, 'Ceiling Fan & Light Installation', 'Mounting, blade balancing, wiring integration and functional load testing.', 25.00, 30, 4.88, 120, 'fan_install.jpg'),
(6, 2, 'Inverter & Battery Setup', 'Heavy-duty wiring, inverter bench test, battery water top-up and safety earthing.', 55.00, 75, 4.80, 41, 'inverter.jpg'),
(7, 3, 'Water Leakage & Pipe Fix', 'High precision ultrasonic leak detection, pipe joints repair & seal sealing.', 35.00, 45, 4.85, 115, 'plumb_leak.jpg'),
(8, 3, 'Kitchen Sink & Drain Unclogging', 'Motorized snaking, grease dissolver treatment, and odor elimination.', 30.00, 40, 4.91, 160, 'drain_unclog.jpg'),
(9, 3, 'Complete Bathroom Fixture Setup', 'Installation of rain shower, mixer taps, angle valves & vanity sinks.', 65.00, 90, 4.89, 74, 'bath_fixture.jpg'),
(10, 4, 'Full House Deep Cleaning', 'Intensive scrubbing of kitchen tiles, bathroom descaling, balcony wash & dusting.', 129.00, 240, 4.95, 210, 'deep_clean.jpg'),
(11, 4, 'Sofa & Upholstery Shampooing', 'Industrial dry foam shampoo extraction, stain removal, and mite treatment.', 49.00, 90, 4.86, 134, 'sofa_clean.jpg'),
(12, 4, 'Modular Kitchen Degreasing', 'Chimney degreasing, cabinet sanitizing, tile grout cleaning and hob polishing.', 59.00, 120, 4.90, 89, 'kitchen_clean.jpg'),
(13, 5, 'Single Room Wall Makeover', 'Two coats of premium washable emulsion paint with wall putty smoothing.', 99.00, 360, 4.82, 52, 'room_paint.jpg'),
(14, 5, 'Balcony & Ceiling Waterproofing', 'Elastomeric membrane application, crack bridging and dampness proof seal.', 119.00, 180, 4.79, 38, 'waterproof.jpg'),
(15, 6, 'Relaxing Swedish Body Massage', '60 minutes deep tissue relaxation with organic lavender aromatherapy oils.', 59.00, 60, 4.96, 178, 'massage.jpg'),
(16, 6, 'Hydra-Glow Facial & Cleanup', 'Skin brightening treatment, blackhead extraction, vitamin C mask and face steam.', 45.00, 50, 4.91, 145, 'facial.jpg');

-- Seed Technicians (Service Providers / Partners)
INSERT INTO `technicians` (`id`, `name`, `phone`, `email`, `category_id`, `rating`, `total_jobs`, `status`, `avatar`) VALUES
(1, 'Alex Carter (Senior HVAC Master)', '+1 (555) 301-4491', 'alex.c@smartservice.com', 1, 4.95, 342, 'available', 'tech1.jpg'),
(2, 'Marcus Vance (Certified Electrician)', '+1 (555) 482-9912', 'marcus.v@smartservice.com', 2, 4.89, 218, 'available', 'tech2.jpg'),
(3, 'David Miller (Master Plumber)', '+1 (555) 773-1104', 'david.m@smartservice.com', 3, 4.92, 480, 'busy', 'tech3.jpg'),
(4, 'Elena Rodriguez (Sanitization Lead)', '+1 (555) 662-8833', 'elena.r@smartservice.com', 4, 4.98, 512, 'available', 'tech4.jpg'),
(5, 'Liam Gallagher (Painter & Finisher)', '+1 (555) 912-3344', 'liam.g@smartservice.com', 5, 4.84, 160, 'available', 'tech5.jpg'),
(6, 'Sophia Chen (Aesthetician & Stylist)', '+1 (555) 884-2200', 'sophia.c@smartservice.com', 6, 4.97, 395, 'available', 'tech6.jpg');

-- Seed Sample Bookings (Showcases diverse statuses for the Uber tracker & Admin metrics)
INSERT INTO `bookings` (`id`, `booking_code`, `user_id`, `service_id`, `technician_id`, `service_date`, `time_slot`, `status`, `total_amount`, `payment_method`, `payment_status`, `service_address`, `latitude`, `longitude`, `special_notes`) VALUES
(1, 'SRV-8041', 2, 1, 1, CURDATE(), '10:00 AM - 12:00 PM', 'in_progress', 49.00, 'online', 'paid', '425 Grand Avenue, Apartment 3B, New York, NY', 40.7128, -74.0060, 'Please call when arriving at the security gate.'),
(2, 'SRV-8042', 2, 4, 2, DATE_ADD(CURDATE(), INTERVAL 1 DAY), '02:00 PM - 04:00 PM', 'confirmed', 45.00, 'cash', 'pending', '425 Grand Avenue, Apartment 3B, New York, NY', 40.7128, -74.0060, 'Distribution box is in the utility room.'),
(3, 'SRV-8039', 2, 10, 4, DATE_SUB(CURDATE(), INTERVAL 2 DAY), '09:00 AM - 01:00 PM', 'completed', 129.00, 'card', 'paid', '425 Grand Avenue, Apartment 3B, New York, NY', 40.7128, -74.0060, 'Pristine service completed with full house deep wash.');

-- Seed Reviews
INSERT INTO `reviews` (`id`, `booking_id`, `user_id`, `service_id`, `rating`, `comment`) VALUES
(1, 3, 2, 10, 5, 'Outstanding deep cleaning! Elena and her team were on time, super professional, and our apartment smells completely fresh. Highly recommended!');
