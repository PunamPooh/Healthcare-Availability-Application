# Healthcare Availability Application

A web-based system that helps users find nearby healthcare facilities and book appointments, including specialized medical resources such as MRI scans, CT scans, X‑rays, blood tests, and eye examinations. The application uses location-based search (GPS or zip code) with radius filtering and displays distances using the Haversine formula.

## Features

- **User Authentication** – Register, login, logout with password hashing (bcrypt)
- **Location Search** – Search by GPS or Copenhagen zip code (2km, 5km, 10km, 20km radius)
- **Distance Calculation** – Haversine formula for accurate distances in kilometers
- **Appointment Booking** – Book, view, and cancel regular appointments
- **Resource Booking** – View and book specific medical resources (MRI, CT scan, ultrasound, blood tests, vaccinations, physiotherapy) with availability days and time slots
- **Admin Panel** – Manage facilities, resources, users, and all appointments with dashboard statistics
- **Responsive Design** – Works on desktop and mobile browsers

## Technologies

- **Backend:** PHP 8.x
- **Database:** MySQL 8.x
- **Web Server:** Apache (XAMPP)
- **Frontend:** HTML5, CSS3, JavaScript
- **Geocoding:** Local zip code lookup table (no external API)

## Project Structure
healthcare_app/
├── config/
│ └── db.php
├── zipcodes.php
├── index.php
├── register.php
├── login.php
├── dashboard.php
├── search.php
├── book.php
├── my_appointments.php
├── cancel.php
├── resources.php
├── book_resource.php
├── cancel_resource.php
├── admin_login.php
├── admin_dashboard.php
├── admin_facilities.php
├── admin_resources.php
├── admin_users.php
├── admin_appointments.php
├── admin_add_facility.php
├── admin_edit_facility.php
├── admin_logout.php
└── logout.php

text

## Installation

### Prerequisites
- XAMPP (or any LAMP/WAMP stack) with PHP 8+ and MySQL
- Git (optional, for cloning)

### Steps

1. **Clone or download** the repository into your web server's document root:
   ```bash
   git clone https://github.com/yourusername/healthcare-app.git
Or extract the ZIP file into C:\xampp\htdocs\healthcare_app\ (Windows) or /Applications/XAMPP/htdocs/healthcare_app/ (macOS).

Start Apache and MySQL from XAMPP control panel.

Import the database:

Open phpMyAdmin at http://localhost/phpmyadmin

Create a new database named healthcare_app

Import the SQL file from database.sql (create it using the schema below)

Configure database connection:

Edit config/db.php with your database credentials (default: username root, password empty).

Set up admin account (optional):

Run this SQL in phpMyAdmin to create an admin user:

sql
INSERT INTO users (name, email, password, is_admin) 
VALUES ('Admin', 'admin@admin.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1);
Password for this account is password.

Access the application:

User portal: http://localhost/healthcare_app/login.php

Admin panel: log in with the admin account (same login page, toggle to Admin Login)

Database Schema (Simplified)
sql
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100),
    email VARCHAR(100) UNIQUE,
    password VARCHAR(255),
    is_admin TINYINT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE facilities (...);
CREATE TABLE appointments (...);
CREATE TABLE resources (...);
CREATE TABLE resource_bookings (...);
Full schema is available in database.sql.

Usage
Regular User
Register an account.

Log in and search by GPS or enter a Copenhagen zip code (e.g., 2100).

Select a radius and view nearby facilities with distances.

Book a regular appointment or click "View Resources" to see available medical services (MRI, CT, etc.).

Book a resource by choosing date and time (based on the resource's availability days).

View all bookings in "My Appointments" and cancel if needed.

Admin
Toggle to Admin Login on the login page.

Manage facilities (add/edit/delete), resources (add/delete), users (delete), and view all appointments.

Known Limitations
Zip code geocoding only covers Copenhagen region (postal codes 1000–3000).

No email or SMS notifications (on‑screen confirmation only).

Facility and resource data are static; no external API integration.

The system runs on HTTP; production deployment would require HTTPS for secure geolocation.

Future Work
Email/SMS reminders for appointments

Mobile app (React Native / Flutter)

Integration with electronic health records (FHIR)

Expansion of zip code coverage to all of Denmark

Patient feedback and rating system

License
This project is for academic purposes as part of a BSc Computer Science thesis.

Author
Punam Chochangi Pun
De Montfort University

Acknowledgements
Supervisor: Fati Tahiru

Copenhagen hospitals and clinics for providing public data

