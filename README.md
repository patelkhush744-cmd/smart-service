# SmartService - Uber for Home Services 🚀
### On-Demand Home Service Booking System (Customer & Admin Portals)

A complete, production-ready, professional web application built using **PHP + MySQLi**, **Vanilla JavaScript (ES6)**, and **Modern CSS**. Inspired by Uber and Urban Company, it brings smooth on-demand service dispatch, live status radar tracking, technician assignment, upfront fare calculation, and an admin command center.

---

## 🌟 Key Features

### 👤 Customer Portal (User Side)
- **Uber-Inspired Modern Landing Page**: Clean dark/light theme, category scroller, trending services, and upfront transparent pricing.
- **Dynamic Service Search & Filtering**: Instant client-side keyword filtering and category switching without page reloads.
- **Multi-Step Smart Booking Wizard**:
  - Service selection with optional recommended add-ons (auto recalculates fare).
  - Date and convenient 2-hour arrival window picker.
  - Interactive OpenStreetMap (Leaflet.js) pin-point location picker.
  - Cash on Delivery or simulated Instant Online/Card checkout.
- **Uber-Style Live Service Radar & Tracker (`user/booking_details.php`)**:
  - Live animated 5-step status timeline (`Requested` ➔ `Confirmed` ➔ `Pro En Route` ➔ `In Progress` ➔ `Completed`).
  - Real-time polling via background API (`api/get_booking_status.php`).
  - Interactive live map showing technician arrival simulation.
  - Assigned technician profile card with direct click-to-call button.
  - Cancellation capability for pending/confirmed bookings.
  - Interactive 5-star review & comment submission upon service completion.
- **My Bookings Dashboard (`user/my_bookings.php`)**: View active and historical requests, receipt details, and live radar shortcuts.
- **User Authentication & Profile (`auth/` & `user/profile.php`)**: Secure registration, login with 1-click demo filler, profile and address updates.

### 🛡️ Admin Command Center (Admin Side)
- **KPI Metrics & Analytics Dashboard (`admin/index.php`)**: Real-time revenue tally, total order counts, active jobs radar, and ready technician workforce.
- **Complete Booking Control Room (`admin/bookings.php`)**:
  - Status filters (`All`, `Pending`, `Confirmed`, `Pro Assigned`, `In Progress`, `Completed`, `Cancelled`).
  - Search by order code, customer name, or phone.
  - **1-Click Technician Assignment modal**: Select and dispatch available specialists to customer bookings.
  - **Inline Real-time Status Changer**: Instantly switch service lifecycle states via AJAX without reloading the page.
  - Payment status tracker (Unpaid / Paid).
- **Service & Catalog Management (`admin/services.php`)**: Add new services, set custom pricing, configure job durations, create new service categories, and toggle active/inactive status.
- **Workforce / Technician Manager (`admin/technicians.php`)**: Register new service technicians, assign domain specialties, view job counters, and switch availability (`Available`, `Busy`, `Offline`).
- **Customer Accounts & Order History (`admin/users.php`)**: Overview of all registered customers with lifetime spend and order counts.
- **Quality Reviews & Rating Moderation (`admin/reviews.php`)**: Monitor customer feedback, star ratings, and remove spam.

---

## 🛠️ Tech Stack & Architecture

- **Backend**: PHP 7.4+ / 8.x with **MySQLi** (Prepared Statements used throughout to prevent SQL Injection).
- **Database**: Relational MySQL / MariaDB (`smart_services_db`).
- **Frontend**: Vanilla JavaScript (ES6+), Fetch API, Leaflet.js (OpenStreetMap, 100% free), Bootstrap 5 & Bootstrap Icons.
- **Styling**: Custom modern CSS (`assets/css/style.css` and `assets/css/admin.css`) with Uber/Urban Company design aesthetics.

---

## 📂 Project Structure

```text
c:\project\
├── schema.sql                     # Full MySQL schema & seed data
├── config\
│   └── db.php                     # MySQLi connection & base URL config
├── includes\
│   ├── header.php                 # Global header with dynamic user menu
│   ├── footer.php                 # Uber-style footer & script imports
│   └── functions.php              # Helpers: auth, sanitize, badges, flash
├── api\
│   ├── book_service.php           # AJAX booking submission handler
│   ├── get_booking_status.php     # Real-time status poller for live tracker
│   └── update_booking.php         # Admin status & technician assignment
├── auth\
│   ├── login.php                  # Sign in with 1-click demo fillers
│   ├── register.php               # Customer registration with password hash
│   └── logout.php                 # Session termination
├── user\
│   ├── my_bookings.php            # Customer bookings list
│   ├── booking_details.php        # Live Uber tracker with map & timeline
│   ├── submit_review.php          # 5-star rating submission
│   └── profile.php                # Customer profile & address settings
├── admin\
│   ├── index.php                  # KPI metrics & recent activity
│   ├── bookings.php               # Booking manager & technician dispatch
│   ├── services.php               # Services & categories manager
│   ├── technicians.php            # Service providers / technician manager
│   ├── users.php                  # Customer directory
│   ├── reviews.php                # Reviews & feedback moderation
│   └── includes\
│       ├── admin_header.php       # Admin sidebar & topbar
│       └── admin_footer.php       # Admin scripts
├── assets\
│   ├── css\
│   │   ├── style.css              # Main customer-facing styles
│   │   └── admin.css              # Admin command center styles
│   └── js\
│       ├── main.js                # Search/category filters & calculations
│       ├── booking-tracker.js     # Live stepper, map simulation & poller
│       └── admin.js               # Admin AJAX status & assignment modal
├── index.php                      # Homepage
├── services.php                   # Full service catalog
├── service_details.php            # Service details with guarantees & reviews
└── book.php                       # Interactive multi-step booking wizard
```

---

## 🚀 Setup & Installation Instructions

### 1. Requirements
- **XAMPP**, **WampServer**, or **Laragon** with Apache and MySQL.
- PHP version 7.4 or higher.

### 2. Move Project to Web Root
- If using **XAMPP**, ensure this folder is in `C:\xampp\htdocs\project\` (or alias).
- Start **Apache** and **MySQL** in the XAMPP Control Panel.

### 3. Import the Database
1. Open your browser and navigate to **phpMyAdmin**: `http://localhost/phpmyadmin/`.
2. Click **New** and create a database named `smart_services_db` (or import directly).
3. Click on `smart_services_db`, go to the **Import** tab.
4. Choose the file `c:\project\schema.sql` and click **Import** (or **Go**).
*All tables, categories, services, technicians, dummy orders, and pre-configured accounts will be created automatically!*

### 4. Open the Application
- Customer Facing Site: `http://localhost/project/index.php`
- Admin Command Center: `http://localhost/project/admin/index.php`

---

## 🔑 Pre-Seeded Demo Credentials

| Role | Email | Password |
| :--- | :--- | :--- |
| **Administrator** | `admin@smartservice.com` | `admin123` |
| **Customer** | `customer@demo.com` | `customer123` |

> *Tip: On the Login page (`auth/login.php`), you can simply click the **"Admin"** or **"Customer"** demo button to instantly pre-fill the fields and test without typing!*

---

## 🛡️ Security Features
- **SQL Injection Defense**: All user inputs in queries are executed through MySQLi prepared statements (`prepare()`, `bind_param()`, `execute()`).
- **Cross-Site Scripting (XSS) Prevention**: All rendered variables pass through `htmlspecialchars()` via `sanitize()`.
- **Role-Based Access Control**: Pages in `admin/` strictly require administrative privilege via `require_admin()`.
- **Secure Password Storage**: Uses standard PHP `password_hash()` with `PASSWORD_BCRYPT`.
