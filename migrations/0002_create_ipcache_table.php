<?php
require_once __DIR__ . '/../config/database.php';

function up()
{
    global $pdo;
    $conn = $pdo;

    // IP Cache table
    $conn->query("CREATE TABLE IF NOT EXISTS ip_cache (
        ip VARCHAR(25) PRIMARY KEY,
        country VARCHAR(100) NOT NULL,
        city VARCHAR(100) NOT NULL,
        last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )") or die($conn->error);

    echo "Created core tables successfully\n";
}

function down()
{
    global $pdo;
    $conn = $pdo;

    // Drop tables in reverse order to handle foreign key constraints
    $tables = [
        'ip_cache'
    ];

    foreach ($tables as $table) {
        $conn->query("DROP TABLE IF EXISTS $table") or die($conn->error);
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