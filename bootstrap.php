<?php

// Composer Autoloader
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
}

// Environment Variables
// In a production environment, these variables should be set directly on the server
// and this library might not be used. The try-catch block handles cases where .env isn't present.
try {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();
} catch (\Dotenv\Exception\InvalidPathException $e) {
    // .env file not found. This is fine for production, but log for debugging.
    error_log("Could not find .env file. Relying on server environment variables.");
}

// Load Application Configuration
// These files will now use getenv() to get their values
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
