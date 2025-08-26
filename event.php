<?php
require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/repositories/EventRepository.php';
require_once __DIR__ . '/repositories/TicketRepository.php';
require_once __DIR__ . '/utils/URLUtils.php';

$page_title = "Event Details"; // Default title

// Get and decrypt the event ID from the URL
$encrypted_event_id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_STRING);
if (!$encrypted_event_id) {
    header("Location: index.php");
    exit;
}

$event_id = URLUtils::decrypt($encrypted_event_id);
if (!$event_id) {
    die("Invalid Event ID.");
}

// Fetch the event and tickets from the database
try {
    $eventRepo = new EventRepository($pdo);
    $event = $eventRepo->findEventById($event_id);

    $ticketRepo = new TicketRepository($pdo);
    $tickets = $ticketRepo->findTicketsByEventId($event_id);
} catch (Exception $e) {
    $event = null;
    $tickets = [];
    error_log("Event page error: " . $e->getMessage());
}

if (!$event) {
    http_response_code(404);
    $page_title = "Event Not Found";
} else {
    $page_title = htmlspecialchars($event->title);
}

require_once __DIR__ . '/includes/header-public.php';
?>

<div class="container my-5">
    <?php if ($event): ?>
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <img src="<?php echo htmlspecialchars($event->banner ?: 'assets/images/default_banner.jpg'); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($event->title); ?>" style="max-height: 400px; object-fit: cover;">
                    <div class="card-body">
                        <h1 class="card-title"><?php echo htmlspecialchars($event->title); ?></h1>
                        <h5 class="card-subtitle mb-2 text-muted"><?php echo date('l, F j, Y - g:i A', strtotime($event->date)); ?></h5>
                        <p class="card-text"><i class="fa fa-map-marker-alt me-2"></i><?php echo htmlspecialchars($event->location); ?></p>
                        <hr>
                        <h4>About this Event</h4>
                        <p><?php echo nl2br(htmlspecialchars($event->description)); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Get Your Tickets</h4>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($tickets)): ?>
                            <form action="checkout.php" method="POST">
                                <input type="hidden" name="event_id" value="<?php echo URLUtils::encrypt($event->id); ?>">
                                <?php foreach ($tickets as $ticket): ?>
                                    <div class="d-flex justify-content-between align-items-center p-3 border-bottom">
                                        <div>
                                            <h5 class="mb-0"><?php echo htmlspecialchars($ticket->name); ?></h5>
                                            <span class="text-muted">$<?php echo htmlspecialchars(number_format($ticket->price, 2)); ?></span>
                                        </div>
                                        <div class="d-flex align-items-center">
                                            <label for="ticket_<?php echo $ticket->id; ?>" class="form-label me-2 mb-0">Quantity:</label>
                                            <input type="number" id="ticket_<?php echo $ticket->id; ?>" name="tickets[<?php echo $ticket->id; ?>]" class="form-control" style="width: 100px;" min="0" max="<?php echo $ticket->quantity; ?>" value="0">
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                                <div class="text-end mt-4">
                                    <button type="submit" class="btn btn-primary btn-lg">Proceed to Checkout</button>
                                </div>
                            </form>
                        <?php else: ?>
                            <p class="text-muted">Tickets for this event are not yet available. Please check back soon.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="text-center">
            <h1>404 - Event Not Found</h1>
            <p class="lead">Sorry, the event you are looking for does not exist.</p>
            <a href='index.php' class="btn btn-primary">Go back to events list</a>
        </div>
    <?php endif; ?>
</div>

<?php
require_once __DIR__ . '/includes/footer-public.php';
?>
