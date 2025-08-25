<?php
// Global configuration settings, loaded from environment variables

// Paystack API Keys
define('PAYSTACK_PUBLIC_KEY', getenv('PAYSTACK_PUBLIC_KEY'));
define('PAYSTACK_SECRET_KEY', getenv('PAYSTACK_SECRET_KEY'));

// Application Settings
define('APP_NAME', getenv('APP_NAME') ?: 'Online Ticketing System');
define('APP_URL', getenv('APP_URL') ?: 'http://localhost');
define('APP_KEY', getenv('APP_KEY')); // Used for encryption

// Email Configuration
define('SMTP_HOST', getenv('SMTP_HOST'));
define('SMTP_USERNAME', getenv('SMTP_USERNAME'));
define('SMTP_PASSWORD', getenv('SMTP_PASSWORD'));
define('SMTP_PORT', getenv('SMTP_PORT') ?: 587);
