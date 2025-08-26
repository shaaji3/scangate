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


/*
|--------------------------------------------------------------------------
| Application INFORMATION
|--------------------------------------------------------------------------
*/

define('APP_INFO', [
    'app_name' => 'ALFPASS',
    'app_version' => '0.1.0',
    'app_description' => 'ALFPASS is a modular, secure, and scalable Event Ticketing System',
    'app_url' => 'https://alfpass.local',
    'app_logo' => '/assets/images/logo-sm.png',
    'app_emails' => [
        'otp' => 'no-reply@nisepa.ni.gov.ng',
        'invoice' => 'billing@nisepa.ni.gov.ng',
        'password_reset' => 'secure@nisepa.ni.gov.ng'
    ],
    'company_name' => 'Alfrix.',
    'company_logo' => '/assets/images/logo-sm-dark.png',
    'company_alias' => 'Alfrix',
    'company_phone' => '08036910019',
    'company_address' => 'No. 41 Okada Road, Minna, 920101, Niger',
]);
