# MyEstate — Premium Real Estate Platform

<div align="center">

```
  __  __       _____     _        _       
 |  \/  |_   _| ____|___| |_ __ _| |_ ___ 
 | |\/| | | | |  _| / __| __/ _` | __/ _ \
 | |  | | |_| | |___\__ \ || (_| | ||  __/
 |_|  |_|\__, |_____|___/\__\__,_|\__\___|
          |___/                            
```

**Verified. Transparent. Zero Commission.**  
A full-stack real estate web application serving Mumbai & Pune.

</div>

---

## 📌 Project Overview

**MyEstate** is a premium, full-featured real estate platform built with PHP, MySQL, and vanilla HTML/CSS/JS. It connects verified property listings directly with serious buyers and renters — completely commission-free. Every listed property goes through an admin approval process before it's visible to users.

The platform is designed for the **Mumbai & Pune real estate market**, with a focus on transparency, real-time data, and a sleek, premium user interface.

---

## ✨ Key Features

### For Users (After Login/Register)
- 🏠 Browse verified property listings (Apartments, Villas, Plots, Commercial)
- 🔍 Search & filter by location, type, budget
- ❤️ Save favourite properties
- 📅 Book site visits with date/time slot selection
- 📩 Send property enquiries directly to the owner
- 📊 Real-time activity feed (live listing saves, registrations, new listings)
- 💰 EMI Calculator (built-in slider-based tool)
- 🗺️ Neighbourhood Explorer (Bandra West, Andheri West, Borivali West)
- 🏗️ View Upcoming Projects (Under Construction)
- 📞 Contact the platform via the Contact page
- 👤 User profile management (Edit Profile)

### For Admins (Admin Panel)
- 📋 Full dashboard with KPI stats, charts, activity timeline
- 🏘️ Manage all property listings (Add, Edit, Delete)
- 👥 Manage registered users
- 📬 View & manage contact messages (general enquiries)
- 📩 View property-specific enquiries with property details
- 📅 Manage site visit bookings (Confirm / Cancel)
- 🛡️ Manage admin accounts

---

## 🛠️ Technology Stack

| Layer | Technology |
|-------|------------|
| **Backend** | PHP 8.x (PDO, prepared statements) |
| **Database** | MySQL / MariaDB (via XAMPP) |
| **Frontend** | HTML5, Vanilla CSS, Vanilla JavaScript |
| **UI Fonts** | Google Fonts — Cormorant Garamond, Outfit, DM Sans |
| **Icons** | Font Awesome 6.2.0 |
| **Charts** | Chart.js 4.4.0 (Admin Dashboard) |
| **Image Slider** | Swiper.js 8 (View Property gallery) |
| **Alerts** | SweetAlert 2.1.2 |
| **Email** | PHPMailer (OTP verification) |
| **Server** | XAMPP (Apache + MySQL) |

---

## 📂 Project Structure

```
MyEstateYT/
├── README.md                        ← You are here
├── home_dbV2.sql                    ← Database schema
├── projectV2/
│   ├── index.php                    ← Pre-login homepage (visitor)
│   ├── login.php                    ← User login
│   ├── register.php                 ← User registration
│   ├── verify_otp.php               ← OTP email verification
│   ├── forgot_password.php          ← Password reset request
│   ├── reset_password.php           ← Password reset form
│   ├── home.php                     ← User dashboard (after login)
│   ├── listings.php                 ← All property listings
│   ├── view_property.php            ← Individual property detail
│   ├── search.php                   ← Search results page
│   ├── saved.php                    ← User's saved properties
│   ├── requests.php                 ← User's site visit bookings
│   ├── my_listings.php              ← Agent's posted properties
│   ├── post_property.php            ← Post a new property (agents)
│   ├── update_property.php          ← Edit an existing property
│   ├── about.php                    ← About MyEstate page
│   ├── contact.php                  ← Contact page
│   ├── upcoming.php                 ← Upcoming projects (under construction)
│   ├── update.php                   ← User profile edit
│   ├── dashboard.php                ← Redirect handler
│   │
│   ├── admin/                       ← Admin panel (separate auth)
│   │   ├── login.php                ← Admin login
│   │   ├── register.php             ← Admin registration
│   │   ├── dashboard.php            ← Admin overview dashboard
│   │   ├── listings.php             ← Manage all listings
│   │   ├── users.php                ← Manage all users
│   │   ├── messages.php             ← View contact messages
│   │   ├── requests.php             ← Manage site visit bookings
│   │   ├── admins.php               ← Manage admin accounts
│   │   ├── view_property.php        ← View property from admin
│   │   ├── update.php               ← Admin profile settings
│   │
│   ├── components/
│   │   ├── connect.php              ← DB connection + auth helpers
│   │   ├── admin_header.php         ← Admin sidebar navigation
│   │   ├── admin_logout.php         ← Admin session destroy
│   │   ├── user_logout.php          ← User session destroy
│   │   ├── book_visit.php           ← AJAX: Book site visit handler
│   │   ├── save_send.php            ← Save/Unsave property handler
│   │   ├── message.php              ← SweetAlert message display
│   │   ├── footer.php               ← Shared footer (some pages)
│   │   └── mailer.php               ← PHPMailer OTP/confirmation emails
│   │
│   ├── ajax/
│   │   └── search_suggest.php       ← Live search autocomplete
│   │
│   ├── css/
│   │   ├── style.css                ← Global user-facing styles
│   │   └── admin_style.css          ← Admin panel styles (sidebar, nav)
│   │
│   ├── js/
│   │   ├── script.js                ← Global user JS
│   │   └── admin_script.js          ← Admin panel JS
│   │
│   ├── images/                      ← Static UI images
│   ├── uploaded_files/              ← User-uploaded property images
│   └── PHPMailer/                   ← PHPMailer library files
```

---

## 🗃️ Database Schema (`home_db`)

### Table: `users`
| Column | Type | Description |
|--------|------|-------------|
| `id` | VARCHAR(20) | Unique user identifier |
| `name` | VARCHAR(50) | Full name |
| `number` | VARCHAR(10) | Phone number |
| `email` | VARCHAR(50) | Email address |
| `password` | VARCHAR(50) | SHA1 hashed password |

### Table: `admins`
| Column | Type | Description |
|--------|------|-------------|
| `id` | VARCHAR(20) | Admin identifier |
| `name` | VARCHAR(20) | Admin name |
| `password` | VARCHAR(50) | SHA1 hashed password |

### Table: `property`
| Column | Type | Description |
|--------|------|-------------|
| `id` | VARCHAR(20) | Property identifier |
| `user_id` | VARCHAR(20) | Owner/agent user ID |
| `property_name` | VARCHAR(50) | Property title |
| `address` | VARCHAR(100) | Full address |
| `price` | VARCHAR(10) | Price (INR) |
| `type` | VARCHAR(10) | apartment / villa / plot / commercial |
| `offer` | VARCHAR(10) | sale / rent |
| `status` | VARCHAR(50) | Ready to Move / Under Construction |
| `furnished` | VARCHAR(50) | yes / no / semi |
| `bhk` | VARCHAR(10) | BHK configuration |
| `bedroom` | VARCHAR(10) | No. of bedrooms |
| `bathroom` | VARCHAR(10) | No. of bathrooms |
| `balcony` | VARCHAR(10) | No. of balconies |
| `carpet` | VARCHAR(10) | Carpet area (sqft) |
| `age` | VARCHAR(2) | Property age (years) |
| `total_floors` | VARCHAR(2) | Total floors in building |
| `room_floor` | VARCHAR(2) | Unit's floor number |
| `loan` | VARCHAR(50) | Loan available (yes/no) |
| `lift`, `security_guard`, `play_ground`, `garden`, `water_supply`, `power_backup`, `parking_area`, `gym`, `shopping_mall`, `hospital`, `school`, `market_area` | VARCHAR(3) | Amenities (yes/no) |
| `image_01`–`image_05` | VARCHAR(50) | Uploaded image filenames |
| `description` | VARCHAR(1000) | Property description |
| `date` | DATE | Listing date |

### Table: `messages`
| Column | Type | Description |
|--------|------|-------------|
| `id` | VARCHAR(20) | Message identifier |
| `name` | VARCHAR(50) | Sender name |
| `email` | VARCHAR(50) | Sender email |
| `number` | VARCHAR(10) | Sender phone |
| `message` | VARCHAR(1000) | Message content |
| `responded` | TINYINT(1) | 0 = pending, 1 = admin responded |

### Table: `requests` (Site Visit Bookings)
| Column | Type | Description |
|--------|------|-------------|
| `id` | VARCHAR(20) | Booking identifier |
| `property_id` | VARCHAR(20) | Property being visited |
| `sender` | VARCHAR(20) | User ID who booked |
| `receiver` | VARCHAR(20) | Owner/admin ID |
| `date` | DATE | Booking creation date |

> **Note:** The `requests` table is extended with additional columns (`visit_date`, `time_slot`, `purpose`, `status`, `user_name`, `user_phone`) added during development.

### Table: `saved`
| Column | Type | Description |
|--------|------|-------------|
| `id` | VARCHAR(20) | Record identifier |
| `property_id` | VARCHAR(20) | Saved property |
| `user_id` | VARCHAR(20) | User who saved it |

---

## 🚀 Setup & Installation

### Prerequisites
- [XAMPP](https://www.apachefriends.org/) (PHP 8.x, MySQL/MariaDB)
- A browser

### Step 1 — Clone / Copy Files
```bash
# Place the project in your XAMPP htdocs folder
C:\xampp\htdocs\MyEstateYT\
```

### Step 2 — Start XAMPP
- Open XAMPP Control Panel
- Start **Apache** and **MySQL**

### Step 3 — Import Database
1. Open [http://localhost/phpmyadmin](http://localhost/phpmyadmin)
2. Create a new database named: `home_db`
3. Click **Import** → Select `home_dbV2.sql` → Click **Go**

OR via command line:
```bash
C:\xampp\mysql\bin\mysql.exe -u root -e "CREATE DATABASE home_db;"
C:\xampp\mysql\bin\mysql.exe -u root home_db < home_dbV2.sql
```

### Step 4 — Apply Database Updates
Run this to add the `responded` column (required for enquiry counter):
```bash
C:\xampp\mysql\bin\mysql.exe -u root -e "ALTER TABLE home_db.messages ADD COLUMN IF NOT EXISTS responded TINYINT(1) NOT NULL DEFAULT 0;"
```

### Step 5 — Access the Application
| URL | Description |
|-----|-------------|
| `http://localhost/MyEstateYT/projectV2/index.php` | Visitor Homepage |
| `http://localhost/MyEstateYT/projectV2/login.php` | User Login |
| `http://localhost/MyEstateYT/projectV2/register.php` | User Registration |
| `http://localhost/MyEstateYT/projectV2/home.php` | User Dashboard (after login) |
| `http://localhost/MyEstateYT/projectV2/admin/login.php` | Admin Login |
| `http://localhost/MyEstateYT/projectV2/admin/dashboard.php` | Admin Dashboard |

---

## 🔐 Default Admin Credentials

| Field | Value |
|-------|-------|
| **Admin ID** | `BcjKNX58e4x7bIqIvxG7` |
| **Password** | `admin` (SHA1 hashed in DB) |

> Login at: `http://localhost/MyEstateYT/projectV2/admin/login.php`

---

## 📩 Inquiry / Messaging Workflow

The platform has **two separate inquiry channels**:

### Channel 1 — Property Enquiry (via View Property page)
1. User clicks **View Detail** on any property listing
2. On the property page, user fills the **Send Enquiry** form (name, phone, message)
3. Submit creates a record in the `messages` table linked to that `property_id`
4. In Admin Panel → **Messages**, admin can see: user's message + **which specific property** it's about
5. Admin clicks **View Property** → sees full property detail (same interface as listings)
6. After admin responds, they mark message as `responded = 1` → enquiry count decreases on user dashboard

### Channel 2 — General Contact (via Contact page)
1. User visits **Contact** page and fills the contact form (name, email, phone, message)
2. Submit creates a record in the `messages` table (NO property_id — just a general message)
3. In Admin Panel → **Messages**, admin sees the message with NO property attached
4. Admin can only respond via email (no in-app reply for general messages)

---

## 🏗️ Upcoming Projects

The platform features 6 high-quality under-construction real estate projects accessible via the **Upcoming** navbar link:

1. **Skyline Residences** — Borivali West, Mumbai (Luxury Apartments, Q4 2026)
2. **The Horizon Towers** — Andheri East, Mumbai (Premium Apartments, Q2 2027)
3. **Greenfield Villas** — Panvel, Navi Mumbai (Villas, Q1 2027)
4. **Prestige One** — Wakad, Pune (Mixed-Use, Q3 2026)
5. **Eden Estates** — Thane West (Gated Community, Q1 2028)
6. **Marina Heights** — Bandra West, Mumbai (Ultra-Luxury, Q4 2027)

---

## 📍 Business Contact

| Field | Details |
|-------|---------|
| **Office** | Nalasopara West, Maharashtra — 401203 |
| **Email** | rayyanbhagate@gmail.com |
| **Hours** | Mon–Sat: 9 AM – 7 PM · Sunday: 10 AM – 4 PM |

---

## 🎨 Design System

| Token | Value | Usage |
|-------|-------|-------|
| `--r` | `#d62828` | Primary red (CTAs, accents) |
| `--rd` | `#9e1c1c` | Dark red (gradients) |
| `--rp` | `#fdf1f1` | Light red tint (backgrounds) |
| `--ink` | `#1a0505` | Primary text |
| `--ink3` | `#9a6565` | Muted text |
| `--bg` | `#faf5f5` | Page background |
| `--white` | `#ffffff` | Cards, modals |

**Fonts:**
- **Cormorant Garamond** — Headlines, prices, serif elements
- **Outfit** — Body text, buttons, nav
- **DM Sans** — Descriptive paragraphs (about page)

---

## 📋 Admin Panel Features

| Page | Function |
|------|----------|
| `admin/dashboard.php` | Overview: KPIs, charts (line, bar, donut), recent bookings, users, messages, activity timeline |
| `admin/listings.php` | View / Delete all property listings |
| `admin/users.php` | View / Delete registered users |
| `admin/messages.php` | View property enquiries + general contact messages |
| `admin/requests.php` | Manage site visit bookings (Confirm/Cancel) |
| `admin/admins.php` | Manage admin accounts (add/remove) |
| `admin/view_property.php` | View full property detail from admin context |

---

## 🔒 Authentication

- **Users:** Session + Cookie-based (`user_id`). Validated via `validate_user_cookie()` in `connect.php`.
- **Admins:** Session + Cookie-based (`admin_id`). Validated via `validate_admin_cookie()` in `connect.php`.
- **OTP Verification:** Email OTP sent via PHPMailer on registration.
- **Password Reset:** Token-based email flow via PHPMailer.

---

## 📝 License

This project is a private real estate platform. All rights reserved © 2026 MyEstate.

---

<div align="center">
Made with ❤️ in Mumbai · <strong>MyEstate</strong> — Your Home Awaits
</div>