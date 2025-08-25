<?php
require_once __DIR__ . '/../config/database.php';

function up()
{
    global $pdo;
    $conn = $pdo;

    $conn->query("CREATE TABLE IF NOT EXISTS `user_otps` (
        `id` int NOT NULL,
        `email` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
        `otp` varchar(6) COLLATE utf8mb4_general_ci NOT NULL,
        `expiry_time` datetime NOT NULL,
        `verified` tinyint(1) DEFAULT '0',
        `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
      )") or die($conn->errorInfo());

    // Password resets table
    $conn->query("CREATE TABLE IF NOT EXISTS `password_resets` (
        `id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
        `user_id` int NOT NULL,
        `token` varchar(255) NOT NULL,
        `expiry` datetime NOT NULL
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;")
        or die($conn->errorInfo());

    // Activity logs table
    $conn->query("CREATE TABLE IF NOT EXISTS activity_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    action VARCHAR(255) NOT NULL,
    description TEXT,
    country VARCHAR(100),
    city VARCHAR(100),
    os VARCHAR(50),
    user_agent VARCHAR(50),
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
)") or die($conn->errorInfo());


    echo "Created core tables successfully\n";
}

function down()
{
    global $pdo;
    $conn = $pdo;

    // Drop tables in reverse order to handle foreign key constraints
    $tables = [
        'user_otps',
        'password_resets',
        'activity_logs',
    ];

    foreach ($tables as $table) {
        $conn->query("DROP TABLE IF EXISTS $table") or die($conn->errorInfo());
    }

    echo "Dropped core tables successfully\n";
}

// Execute the migration
if (!isset($argv[1])) {
    echo "Please specify 'up' or 'down' as an argument\n";
    exit(1);
}

try {
    if ($argv[1] === 'up') {
        up();
    } else if ($argv[1] === 'down') {
        down();
    } else {
        echo "Invalid argument. Use 'up' or 'down'\n";
        exit(1);
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}