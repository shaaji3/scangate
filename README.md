# 🎫 Online Ticketing System

## ✅ Overview

The **Online Ticketing System** is a multi-role platform for managing and selling tickets for events. It supports roles for Super Admins, Event Planners (and their teams), and Attendees. The system is built with plain PHP, follows a repository pattern for database interactions, and includes key features from event creation to secure gate validation.

This document provides a comprehensive overview of the project, its features, and instructions for setup and installation.

---

## ✨ Core Features

*   **Role-Based Access Control**:
    *   **Super Admin**: Full control of the platform.
    *   **Event Planner**: Can create events, manage tickets, and build a team.
    *   **Team Members**: Planners can add members to their team with specific roles (`Event Manager`, `Gate Agent`) who can help manage events.
    *   **Attendee**: Can browse events, purchase tickets, and manage their bookings.
*   **Event & Ticket Management**: Planners can create detailed events with banners and add multiple ticket types (e.g., VIP, General Admission) with different prices and quantities.
*   **Secure Ordering Flow**:
    *   Attendees can select tickets for an event and proceed to checkout.
    *   Orders are created securely, and users are redirected to a payment gateway.
*   **Payment Integration**: The system is built to integrate with **Paystack** for processing payments. It includes a callback and a reliable webhook handler.
*   **PDF Ticket Generation**: Upon successful payment, the system automatically generates unique PDF tickets for each ticket purchased.
*   **QR Code Validation**: Each PDF ticket includes a unique QR code. A dedicated scanner interface allows authorized event staff to scan tickets and validate entry in real-time.
*   **Email Notifications**: The system sends automated emails for key events:
    *   Welcome email upon user registration.
    *   Order confirmation email with PDF tickets attached after a successful purchase.
*   **Security Hardening**:
    *   **CSRF Protection**: All state-changing forms are protected against Cross-Site Request Forgery.
    *   **Encrypted URL Parameters**: Sensitive IDs in URLs are encrypted to prevent tampering.
    *   **Environment-based Configuration**: All sensitive keys and configurations are loaded from a `.env` file, keeping them out of version control.

---

## 💻 Technology Stack

*   **Backend**: PHP 8.2+ (Plain PHP)
*   **Database**: MySQL 8
*   **Dependency Management**: Composer
*   **Frontend**: Plain HTML, CSS, and JavaScript

### PHP Dependencies
The following libraries are managed via Composer:
*   `paystack/paystack-php`: For interacting with the Paystack API.
*   `dompdf/dompdf`: For generating PDF tickets from HTML.
*   `endroid/qr-code`: For creating QR codes.
*   `phpmailer/phpmailer`: For sending emails via SMTP.
*   `vlucas/phpdotenv`: For managing environment variables.

---

## 🚀 Setup and Installation

Follow these steps to set up the project in a local development environment.

### Prerequisites
*   PHP 8.2 or higher
*   MySQL 8 or higher
*   Composer installed globally
*   A web server (e.g., Apache, Nginx)

### Step 1: Clone the Repository
Clone this repository to your local machine:
```bash
git clone <repository-url>
cd <project-directory>
```

### Step 2: Install Dependencies
Run Composer to install all the required PHP libraries:
```bash
composer install
```
This will create a `vendor/` directory in the project root.

### Step 3: Configure Environment Variables
The project uses a `.env` file for all configuration.
1.  Copy the example file:
    ```bash
    cp .env.example .env
    ```
2.  Open the `.env` file and fill in the required values:
    *   **`APP_KEY`**: This is a secret key used for encryption. Generate a secure 32-byte key. You can use the following command to generate one:
        ```bash
        openssl rand -base64 32
        ```
    *   **`DB_*`**: Fill in your database connection details.
    *   **`SMTP_*`**: Fill in the credentials for your SMTP server (e.g., from Mailtrap, SendGrid, etc.).
    *   **`PAYSTACK_*`**: Fill in your test or live API keys from your Paystack dashboard.

### Step 4: Database Migration
The database schema is defined in the `.sql` files located in the `migrations/` directory. You need to import them into your database **in order**.

1.  Create a database in MySQL with the name you specified in `DB_NAME` in your `.env` file.
2.  Import each `.sql` file sequentially, from `001_...` to `013_...`. You can do this using a tool like phpMyAdmin or via the command line:
    ```bash
    mysql -u [username] -p [database_name] < migrations/001_create_users_table.sql
    mysql -u [username] -p [database_name] < migrations/002_create_events_table.sql
    # ... and so on for all migration files.
    ```

### Step 5: Web Server Configuration
Configure your web server (e.g., Apache Virtual Host) to point its document root to the root directory of the project.

---

## 📁 Folder Structure
```
project-root/
│
├── api/                  # API endpoints (e.g., for ticket verification)
├── assets/               # CSS, JS, Images (currently unused)
├── classes/              # Core classes (User, Event, Ticket, Order, etc.)
├── config/               # DB config, app keys, global vars (loads from .env)
├── includes/             # Reusable UI components (header, footer, templates)
├── migrations/           # SQL migration files
├── repositories/         # Database interaction logic (UserRepository, etc.)
├── utils/                # Helper utilities (EmailSender, PDFGenerator, etc.)
├── upload/               # Directory for user-uploaded content
│   ├── banners/          # Event banners
│   └── qrcodes/          # Generated QR code images
├── vendor/               # Composer dependencies
├── .env.example          # Example environment file
├── bootstrap.php         # Central bootstrap file for the application
├── index.php             # Main landing page
└── ...                   # Other root-level page scripts
```

---

## 🔐 Security
*   **CSRF Protection**: All POST forms are protected.
*   **Encrypted URLs**: Sensitive IDs in GET requests are encrypted.
*   **Password Hashing**: User passwords are securely hashed using `password_hash`.
*   **Prepared Statements**: The repository pattern uses PDO prepared statements to prevent SQL injection.
*   **Authorization**: A central `AuthGuard` utility ensures that users (including team members) only have access to the resources they are permitted to manage.
