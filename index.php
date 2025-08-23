<?php
// index.php - The landing page for the Online Ticketing System

// Include necessary files
require_once 'config/database.php';
require_once 'repositories/EventRepository.php';

// Fetch all published events
try {
    $eventRepo = new EventRepository($pdo);
    $events = $eventRepo->findAllEvents('published');
} catch (Exception $e) {
    // Handle database errors gracefully
    $events = [];
    $pageError = "Could not fetch events. Please try again later.";
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Online Ticketing System</title>
    <style>
        body { font-family: sans-serif; margin: 0; background-color: #f4f4f4; }
        .navbar { background-color: #333; overflow: hidden; }
        .navbar a { float: left; display: block; color: white; text-align: center; padding: 14px 20px; text-decoration: none; }
        .navbar a.right { float: right; }
        .container { padding: 20px; }
        .hero { background-color: #007bff; color: white; padding: 40px 20px; text-align: center; }
        .hero h1 { margin: 0; font-size: 2.5em; }
        .events-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px; margin-top: 20px; }
        .event-card { background-color: white; border: 1px solid #ddd; border-radius: 5px; overflow: hidden; }
        .event-card img { width: 100%; height: 200px; object-fit: cover; }
        .event-card-content { padding: 15px; }
        .event-card h3 { margin-top: 0; }
        .event-card a { text-decoration: none; color: #007bff; }
    </style>
</head>
<body>

    <div class="navbar">
        <a href="index.php">Ticketing System</a>
        <a href="dashboard.php" class="right">Dashboard</a>
        <a href="login.php" class="right">Login</a>
        <a href="register.php" class="right">Register</a>
    </div>

    <div class="hero">
        <h1>Find Your Next Experience</h1>
        <p>Buy tickets for the best events in town</p>
    </div>

    <div class="container">
        <h2>Upcoming Events</h2>
        <?php if (isset($pageError)): ?>
            <p style="color: red;"><?php echo $pageError; ?></p>
        <?php endif; ?>

        <div class="events-grid">
            <?php if (!empty($events)): ?>
                <?php foreach ($events as $event): ?>
                    <div class="event-card">
                        <img src="<?php echo htmlspecialchars($event->banner ?: 'assets/images/default_banner.jpg'); ?>" alt="<?php echo htmlspecialchars($event->title); ?>">
                        <div class="event-card-content">
                            <h3><?php echo htmlspecialchars($event->title); ?></h3>
                            <p><?php echo date('F j, Y, g:i a', strtotime($event->date)); ?></p>
                            <p><?php echo htmlspecialchars($event->location); ?></p>
                            <a href="event.php?id=<?php echo $event->id; ?>">View Details</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>There are no upcoming events at the moment. Please check back later.</p>
            <?php endif; ?>
        </div>
    </div>

</body>
</html>
