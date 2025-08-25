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
require_once 'utils/QRGenerator.php';

use Dompdf\Dompdf;
use Dompdf\Options;

// 1. Get and validate ticket code
$ticket_code = filter_input(INPUT_GET, 'code', FILTER_SANITIZE_STRING);
if (!$ticket_code) {
    die("No ticket code provided.");
}

// 2. Fetch all ticket details with a single query
$sql = "SELECT
            it.ticket_code, it.status AS ticket_status,
            u.name AS user_name, u.email AS user_email, o.user_id,
            e.title AS event_title, e.date AS event_date, e.location AS event_location,
            t.name AS ticket_type
        FROM issued_tickets AS it
        JOIN order_items AS oi ON it.order_item_id = oi.id
        JOIN tickets AS t ON oi.ticket_id = t.id
        JOIN orders AS o ON oi.order_id = o.id
        JOIN events AS e ON o.event_id = e.id
        JOIN users AS u ON o.user_id = u.id
        WHERE it.ticket_code = :ticket_code";

$stmt = $pdo->prepare($sql);
$stmt->bindValue(':ticket_code', $ticket_code);
$stmt->execute();
$ticket_data = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$ticket_data) {
    die("Invalid ticket code.");
}

// 3. Security Check: Ensure the ticket belongs to the logged-in user
if ($ticket_data['user_id'] !== $_SESSION['user_id']) {
    die("Access denied. You do not have permission to view this ticket.");
}

// 4. Generate QR Code
try {
    $qr_code_path_relative = QRGenerator::generate($ticket_data['ticket_code'], $ticket_data['ticket_code']);
    $qr_code_path_absolute = __DIR__ . '/' . $qr_code_path_relative;
} catch (Exception $e) {
    die("Could not generate QR code: " . $e->getMessage());
}

// 5. Generate PDF
$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true); // Important for loading images from remote URLs, but we use a local path

$dompdf = new Dompdf($options);

// HTML content for the PDF
ob_start();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Ticket</title>
    <style>
        body { font-family: sans-serif; }
        .ticket { border: 2px solid #000; padding: 20px; width: 100%; margin: auto; box-sizing: border-box; }
        .header { text-align: center; }
        .details { margin-top: 20px; }
        .qr-code { text-align: center; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="ticket">
        <div class="header">
            <h1><?php echo htmlspecialchars($ticket_data['event_title']); ?></h1>
            <p><strong>Official Event Ticket</strong></p>
        </div>
        <hr>
        <div class="details">
            <p><strong>Attendee:</strong> <?php echo htmlspecialchars($ticket_data['user_name']); ?></p>
            <p><strong>Ticket Type:</strong> <?php echo htmlspecialchars($ticket_data['ticket_type']); ?></p>
            <p><strong>Date:</strong> <?php echo date('F j, Y, g:i a', strtotime($ticket_data['event_date'])); ?></p>
            <p><strong>Location:</strong> <?php echo htmlspecialchars($ticket_data['event_location']); ?></p>
        </div>
        <div class="qr-code">
            <img src="<?php echo $qr_code_path_absolute; ?>" alt="QR Code" width="200">
            <p><?php echo htmlspecialchars($ticket_data['ticket_code']); ?></p>
        </div>
    </div>
</body>
</html>
<?php
$html = ob_get_clean();

$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

// Stream the file to the browser and force a download
$dompdf->stream("ticket-" . $ticket_data['ticket_code'] . ".pdf", ["Attachment" => true]);

exit;
