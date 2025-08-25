<?php
session_start();

// Security: Only planners can access
if (!isset($_SESSION["user_id"]) || $_SESSION['user_role'] !== 'planner') {
    header("Location: login.php");
    exit;
}

require_once 'config/database.php';
require_once 'repositories/EventRepository.php';

// Get event ID from URL and validate it
$event_id = filter_input(INPUT_GET, 'event_id', FILTER_VALIDATE_INT);
if (!$event_id) {
    die("Event ID is required.");
}

// Verify that the event belongs to the logged-in planner
$eventRepo = new EventRepository($pdo);
$event = $eventRepo->findEventById($event_id);

if (!$event || $event->planner_id !== $_SESSION['user_id']) {
    die("Access Denied: You do not have permission to manage this event.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Scan Tickets - <?php echo htmlspecialchars($event->title); ?></title>
    <style>
        body { font-family: sans-serif; }
        .container { max-width: 500px; margin: 20px auto; text-align: center; }
        #reader { width: 100%; border: 2px solid #ddd; }
        #result { margin-top: 20px; padding: 15px; font-size: 1.2em; border-radius: 5px; font-weight: bold; min-height: 50px; }
        .result-success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .result-warning { background-color: #fff3cd; color: #856404; border: 1px solid #ffeeba; }
        .result-error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Ticket Scanner</h1>
        <h3><?php echo htmlspecialchars($event->title); ?></h3>
        <div id="reader"></div>
        <div id="result">Awaiting scan...</div>
    </div>

    <!-- Include the QR Code scanning library -->
    <script src="https://unpkg.com/html5-qrcode/minified/html5-qrcode.min.js"></script>

    <script>
        const resultDiv = document.getElementById('result');

        function onScanSuccess(decodedText, decodedResult) {
            // Pause the scanner to prevent multiple scans of the same code
            html5QrcodeScanner.pause();

            resultDiv.innerHTML = `Scanning...`;
            resultDiv.className = ''; // Reset class

            // Send the scanned code to the server for verification
            verifyTicket(decodedText);
        }

        async function verifyTicket(ticketCode) {
            try {
                const response = await fetch('api/verify-ticket.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        ticket_code: ticketCode,
                        event_id: <?php echo $event_id; ?>
                    })
                });

                const data = await response.json();

                // Display the result
                resultDiv.innerHTML = data.message;
                resultDiv.className = 'result-' + data.status; // e.g., result-success

            } catch (error) {
                resultDiv.innerHTML = 'An error occurred while verifying the ticket.';
                resultDiv.className = 'result-error';
                console.error('Error:', error);
            } finally {
                // Resume scanning after a short delay
                setTimeout(() => {
                    if(html5QrcodeScanner.getState() !== 2) { // 2 is SCANNING state
                        html5QrcodeScanner.resume();
                    }
                    resultDiv.innerHTML = 'Awaiting scan...';
                    resultDiv.className = '';
                }, 3000); // 3-second delay
            }
        }

        // Initialize the scanner
        var html5QrcodeScanner = new Html5QrcodeScanner(
            "reader", { fps: 10, qrbox: 250 });
        html5QrcodeScanner.render(onScanSuccess);
    </script>
</body>
</html>
