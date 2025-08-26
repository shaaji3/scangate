# 🎫 Online Ticketing System

## ✅ Overview

The **Online Ticketing System** is a multi-role platform for managing and selling tickets for events. It supports roles for Super Admins, Event Planners (and their teams), and Attendees. The system is built with plain PHP, follows a repository pattern for database interactions, and includes key features from event creation to secure gate validation.

This document provides a comprehensive overview of the project, its features, and instructions for setup and installation.

---

## ✨ Core Features

*   **Modern UI & UX**: The entire frontend has been rebuilt with a modern theme, featuring a clean dashboard and responsive design.
*   **AJAX-Powered Interface**: All forms (login, registration, event creation, etc.) are now handled asynchronously, providing a smoother user experience without page reloads.
*   **Role-Based Access Control**:
    *   **Super Admin**: Full control of the platform, including user management and platform settings.
    *   **Event Planner**: Can create events, manage tickets, and build a team.
    *   **Team Members**: Planners can add members to their team with specific roles (`Event Manager`, `Gate Agent`).
    *   **Attendee**: Can browse events, purchase tickets, and manage their bookings.
*   **User Profile Management**: Logged-in users have a dedicated profile page to update their name and change their password.
*   **Event & Ticket Management**: Planners can create detailed events with banners and add multiple ticket types (e.g., VIP, General Admission).
*   **Secure Ordering Flow**:
    *   Attendees can select tickets for an event and proceed to checkout.
    *   Orders are created securely, and users are redirected to a payment gateway.
*   **Payment Integration**: The system is built to integrate with **Paystack** for processing payments.
*   **PDF Ticket Generation**: Upon successful payment, the system automatically generates unique PDF tickets.
*   **QR Code Validation**: Each PDF ticket includes a unique QR code for real-time, secure validation at the gate.
*   **Email Notifications**: Automated emails for welcome messages and order confirmations.
*   **Enhanced Security**:
    *   **Two-Factor Authentication (2FA)**: An OTP is required for logins from new devices, enhancing account security.
    *   **Password Recovery**: A secure "Forgot Password" flow allows users to reset their password via an email link.
    *   **Rate Limiting**: Protects against brute-force attacks on sensitive actions.
    *   **CSRF Protection**: All state-changing forms are protected against Cross-Site Request Forgery.
    *   **Encrypted URL Parameters**: Sensitive IDs in URLs are encrypted to prevent tampering.
    *   **Environment-based Configuration**: All sensitive keys are loaded from a `.env` file.

---

## 💻 Technology Stack

*   **Backend**: PHP 8.2+ (Plain PHP)
*   **Database**: MySQL 8
*   **Dependency Management**: Composer
*   **Frontend**: HTML, CSS, JavaScript (with AJAX)

### PHP Dependencies
*   `paystack/paystack-php`: For interacting with the Paystack API.
*   `dompdf/dompdf`: For generating PDF tickets from HTML.
*   `endroid/qr-code`: For creating QR codes.
*   `phpmailer/phpmailer`: For sending emails via SMTP.
*   `vlucas/phpdotenv`: For managing environment variables.

---

## 🚀 Setup and Installation

### Prerequisites
*   PHP 8.2 or higher
*   MySQL 8 or higher
*   Composer installed globally

### Step 1: Clone the Repository
```bash
git clone <repository-url>
cd <project-directory>
```

### Step 2: Install Dependencies
```bash
composer install
```

### Step 3: Configure Environment Variables
1.  Copy the example file: `cp .env.example .env`
2.  Open `.env` and fill in the required values for `APP_KEY`, `DB_*`, `SMTP_*`, and `PAYSTACK_*`.

### Step 4: Database Migration
The database schema is defined in the `.sql` files located in the `migrations/` directory. You need to import them into your database **in order**.

1.  Create a database in MySQL.
2.  Import each `.sql` file sequentially, from `001_...` to `015_...`.
    ```bash
    mysql -u [username] -p [database_name] < migrations/001_create_users_table.sql
    mysql -u [username] -p [database_name] < migrations/002_create_events_table.sql
    # ... and so on for all 15 migration files.
    ```

---

## 📁 Folder Structure
```
project-root/
│
├── admin/                # Admin-specific pages and handlers
├── api/                  # API endpoints (e.g., for ticket verification)
├── assets/               # CSS, JS, Images
├── classes/              # Core classes (User, Event, Ticket, Order, etc.)
├── config/               # DB config, app keys, global vars (loads from .env)
├── includes/             # Reusable UI components (header, footer, templates)
├── migrations/           # SQL migration files
├── repositories/         # Database interaction logic (UserRepository, etc.)
├── utils/                # Helper utilities (EmailSender, PDFGenerator, etc.)
├── handles/              # Page helper scripts
├── upload/               # Directory for user-uploaded content
├── ...                   # Other root-level files
```
---

## 🔐 Security
*   **Password Hashing**: User passwords are securely hashed using `password_hash`.
*   **Prepared Statements**: The repository pattern uses PDO prepared statements to prevent SQL injection.
*   **Authorization**: A central `AuthGuard` utility ensures that users only have access to the resources they are permitted to manage.
*   **CSRF Protection & Encrypted URLs**: Standard protections are in place for forms and URL parameters.
