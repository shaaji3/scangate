<?php
// event.php - Displays the details for a single event.

session_start();

// Include necessary files
require_once 'config/database.php';
require_once 'repositories/EventRepository.php';
require_once 'repositories/TicketRepository.php';

// 1. Get and validate the event ID from the URL
$event_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$event_id) {
    header("Location: index.php");
    exit;
}

// 2. Fetch the event and tickets from the database
try {
    $eventRepo = new EventRepository($pdo);
    $event = $eventRepo->findEventById($event_id);

    $ticketRepo = new TicketRepository($pdo);
    $tickets = $ticketRepo->findTicketsByEventId($event_id);
} catch (Exception $e) {
    $event = null;
    $tickets = [];
}

// 3. If event not found, display an error message
if (!$event) {
    http_response_code(404);
    $pageTitle = "Event Not Found";
    $pageContent = "<h1>404 - Event Not Found</h1><p>Sorry, the event you are looking for does not exist.</p><a href='index.php'>Go back to events list</a>";
} else {
    $pageTitle = htmlspecialchars($event->title);
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo $pageTitle; ?> - Online Ticketing System</title>
    <style>
        body { font-family: sans-serif; margin: 0; background-color: #f4f4f4; }
        .navbar { background-color: #333; overflow: hidden; }
        .navbar a { float: left; display: block; color: white; text-align: center; padding: 14px 20px; text-decoration: none; }
        .navbar a.right { float: right; }
        .container { max-width: 800px; margin: 20px auto; background-color: white; padding: 20px; border-radius: 5px; }
        .event-banner { width: 100%; height: 300px; object-fit: cover; border-radius: 5px; }
        .event-details h1 { margin-top: 20px; }
        .tickets-section { margin-top: 30px; }
        .ticket-type { display: flex; justify-content: space-between; align-items: center; padding: 10px; border: 1px solid #ddd; border-radius: 5px; margin-bottom: 10px; }
        .ticket-type input { width: 60px; text-align: center; }
        .btn-checkout { background-color: #28a745; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; font-size: 1.2em; display: inline-block; margin-top: 20px; border: none; cursor: pointer; }
    </style>
</head>
<body>
    <div class="navbar">
        <a href="index.php">Ticketing System</a>
        <a href="dashboard.php" class="right">Dashboard</a>
        <a href="login.php" class="right">Login</a>
    </div>

    <div class="container">
        <?php if ($event): ?>
            <img src="<?php echo htmlspecialchars($event->banner ?: 'assets/images/default_banner.jpg'); ?>" alt="<?php echo htmlspecialchars($event->title); ?>" class="event-banner">
            <div class="event-details">
                <h1><?php echo htmlspecialchars($event->title); ?></h1>
                <p><strong>Date:</strong> <?php echo date('F j, Y, g:i a', strtotime($event->date)); ?></p>
                <p><strong>Location:</strong> <?php echo htmlspecialchars($event->location); ?></p>
                <hr>
                <h3>About this event</h3>
                <p><?php echo nl2br(htmlspecialchars($event->description)); ?></p>
            </div>

            <div class="tickets-section">
                <h3>Get Your Tickets</h3>
                <?php if (!empty($tickets)): ?>
                    <form action="checkout.php" method="POST">
                        <input type="hidden" name="event_id" value="<?php echo $event->id; ?>">
                        <?php foreach ($tickets as $ticket): ?>
                            <div class="ticket-type">
                                <div>
                                    <strong><?php echo htmlspecialchars($ticket->name); ?></strong><br>
                                    <span>$<?php echo htmlspecialchars(number_format($ticket->price, 2)); ?></span>
                                </div>
                                <div>
                                    <label for="ticket_<?php echo $ticket->id; ?>">Quantity:</label>
                                    <input type="number" id="ticket_<?php echo $ticket->id; ?>" name="tickets[<?php echo $ticket->id; ?>]" min="0" max="<?php echo $ticket->quantity; ?>" value="0">
                                </div>
                            </div>
                        <?php endforeach; ?>
                        <button type="submit" class="btn-checkout">Proceed to Checkout</button>
                    </form>
                <?php else: ?>
                    <p>Tickets for this event are not yet available. Please check back soon.</p>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <?php echo $pageContent; ?>
        <?php endif; ?>
    </div>
</body>
</html>
