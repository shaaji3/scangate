<?php
// event.php - Displays the details for a single event.

// Include necessary files
require_once 'config/database.php';
require_once 'repositories/EventRepository.php';

// 1. Get and validate the event ID from the URL
$event_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$event_id) {
    // Optional: Redirect to a 404 page or the index page
    header("Location: index.php");
    exit;
}

// 2. Fetch the event from the database
try {
    $eventRepo = new EventRepository($pdo);
    $event = $eventRepo->findEventById($event_id);
} catch (Exception $e) {
    // In a real app, log this error
    $event = null;
}

// 3. If event not found, display an error message
if (!$event) {
    http_response_code(404);
    $pageTitle = "Event Not Found";
    // A simple 404 message. You could also include a dedicated 404 view.
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
        .btn-buy { background-color: #28a745; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; font-size: 1.2em; display: inline-block; margin-top: 20px; }
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
                <hr>
                <a href="checkout.php?event_id=<?php echo $event->id; ?>" class="btn-buy">Buy Ticket</a>
            </div>
        <?php else: ?>
            <?php echo $pageContent; ?>
        <?php endif; ?>
    </div>
</body>
</html>
