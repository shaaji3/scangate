<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Autoloader and dependencies
if (file_exists('vendor/autoload.php')) {
    require_once 'vendor/autoload.php';
} else {
    die("Composer dependencies not installed. Please run 'composer install'.");
}

require_once 'config/database.php';
require_once 'utils/PDFGenerator.php';

// 1. Get and validate ticket code
$ticket_code = filter_input(INPUT_GET, 'code', FILTER_SANITIZE_STRING);
if (!$ticket_code) {
    die("No ticket code provided.");
}

// 2. Security Check: Can the current user view this ticket?
$sql = "SELECT o.user_id FROM issued_tickets it JOIN order_items oi ON it.order_item_id = oi.id JOIN orders o ON oi.order_id = o.id WHERE it.ticket_code = :ticket_code";
$stmt = $pdo->prepare($sql);
$stmt->bindValue(':ticket_code', $ticket_code);
$stmt->execute();
$owner_id = $stmt->fetchColumn();

if (!$owner_id || $owner_id != $_SESSION['user_id']) {
    die("Access Denied. You do not have permission to view this ticket.");
}

// 3. Generate the PDF and get the file path
$pdf_filepath = PDFGenerator::generateTicketPDF($ticket_code);

if (!$pdf_filepath) {
    die("There was an error generating your ticket. Please try again.");
}

// 4. Stream the file to the browser for download
header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="' . basename($pdf_filepath) . '"');
header('Content-Length: ' . filesize($pdf_filepath));
readfile($pdf_filepath);

// 5. Clean up the temporary file
unlink($pdf_filepath);

exit;
