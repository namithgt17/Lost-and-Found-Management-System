# Campus Lost & Found Management System

A sleek, responsive, and secure web application engineered to streamline campus lost-and-found reporting and item recovery for **Dr. Ambedkar Institute of Technology**.

---

##  Key Features

- **Institutional Identity Gate**: Lightweight authentication validating 10-character college USN formatting (`1DA22CS001`) with automatic uppercase casting and SQL-level attribution.
- **Categorized Reporting & Recovery**: Dedicated pipelines for reporting lost valuables and cataloging found items with dynamic location tags and contact links.
- **Instant Client-Side Filtering**: Real-time keyword filtering and segmented tab switching (Lost, Found, All) for zero-lag item discovery without page reloads.
- **Secure Media Pipeline**: Server-side MIME-type validation, randomized filename hashing, and async lazy-loaded asset handling.
- **Swiss Noir Aesthetic**: Precision typography with local Helvetica Neue, an architectural grid layout, and GPU-composited volumetric lighting effects.

---

## 🛠 Tech Stack & Architecture

- **Backend**: Native PHP 8.x
- **Database**: MySQL via **PHP Data Objects (PDO)** with parameterized prepared statements
- **Frontend**: Vanilla JavaScript (ES6+), Modern Semantic HTML5, Custom CSS3 Variables & Flexbox/Grid
- **Security**: 
  - Token-based **CSRF Protection**
  - **SQL Injection Prevention** via strict PDO parameter binding
  - Strict input sanitization against **XSS**
  - Private database storage of user identities (USNs omitted from public feed)

---

## 🗄 Database Schema

The database consists of two optimized relational tables:
- `lost_items`: Tracks `id`, `usn`, `object_name`, `location_found`, `phone`, `photo`, and `created_at`.
- `found_items`: Tracks `id`, `usn`, `object_name`, `location_found`, `collect_location`, `phone`, `photo`, and `created_at`.

---

##  Setup & Installation

1. **Clone or Download the Repository:**
   ```bash
   git clone https://github.com/namithgt17/Lost-and-Found-Management-System.git
   ```

2. **Configure Database:**
   - Import `database.sql` into your local MySQL server (XAMPP / cPanel phpMyAdmin).
   - Update database credentials in `config.php`:
     ```php
     define('DB_HOST', 'localhost');
     define('DB_NAME', 'your_database_name');
     define('DB_USER', 'your_username');
     define('DB_PASS', 'your_password');
     ```

3. **Run Application:**
   - Launch your local Apache/PHP server and access `http://localhost/lostandfound/`.

---

## 👥 Authors

Developed by **Team Blaze Forge**:
- **Namith G T** ([LinkedIn](https://www.linkedin.com/in/namith-g-t-8164a842a))

