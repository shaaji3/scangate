<?php
$page_title = "Scan Tickets";
require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ .'/repositories/EventRepository.php';
require_once __DIR__ . '/utils/AuthGuard.php';
require_once __DIR__ . '/utils/URLUtils.php';

// AuthGuard is in header-auth.php
$encrypted_event_id = $_GET['event_id'] ?? '';
$event_id = URLUtils::decrypt($encrypted_event_id);

if (!$event_id) {
    // Redirect or show error if event ID is missing/invalid
    header("Location: dashboard.php");
    exit;
}

// Specific permission check for scanning
if (!AuthGuard::canScanTickets($pdo, $_SESSION['user_id'], $event_id)) {
    $_SESSION['error_message'] = "You do not have permission to scan tickets for this event.";
    header("Location: dashboard.php");
    exit;
}

$eventRepo = new EventRepository($pdo);
$event = $eventRepo->findEventById($event_id);

if (!$event) {
    header("Location: dashboard.php");
    exit;
}

require_once __DIR__ . '/includes/header-auth.php';
require_once __DIR__ . '/includes/sidebar.php';
?>

<div class="row d-flex justify-content-center">
    <div class="col-xl-6">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">Ticket Scanner: <?php echo htmlspecialchars($event->title); ?></h4>
            </div>
            <div class="card-body text-center">
                <div id="reader" style="width: 100%; max-width: 500px; margin: auto;"></div>
                <div id="result" class="mt-3 p-3 fw-bold rounded-3 fs-5">Awaiting scan...</div>
            </div>
        </div>
    </div>
</div>

<!-- The QR Code scanning library -->
<script src="https://unpkg.com/html5-qrcode/minified/html5-qrcode.min.js"></script>
<!-- Custom scanner logic -->
<script>
    // Pass the event ID to the scanner script
    const currentEventId = <?php echo json_encode($event_id); ?>;
</script>
<script src="assets/js/scanner.js"></script>

<?php
require_once __DIR__ . '/includes/footer-auth.php';
?>
