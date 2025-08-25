<?php
require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/repositories/EventRepository.php';
require_once __DIR__ . '/utils/URLUtils.php';

// Page-specific logic
$page_title = "Home";

try {
    $eventRepo = new EventRepository($pdo);
    $events = $eventRepo->findAllEvents('published');
} catch (Exception $e) {
    $events = [];
    $pageError = "Could not fetch events. Please try again later.";
}

// Render the page
require_once __DIR__ . '/includes/header.php';
?>

<style>
    /* Page-specific styles */
    .hero { background-color: #007bff; color: white; padding: 40px 20px; text-align: center; margin: -20px -20px 20px -20px; } /* Adjust margins to fill container padding */
    .hero h1 { margin: 0; font-size: 2.5em; }
    .events-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px; margin-top: 20px; }
    .event-card { background-color: white; border: 1px solid #ddd; border-radius: 5px; overflow: hidden; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
    .event-card img { width: 100%; height: 200px; object-fit: cover; }
    .event-card-content { padding: 15px; }
    .event-card h3 { margin-top: 0; }
    .event-card a { text-decoration: none; color: #007bff; font-weight: bold; }
</style>

<div class="hero">
    <h1>Find Your Next Experience</h1>
    <p>Buy tickets for the best events in town</p>
</div>

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
                    <a href="event.php?id=<?php echo URLUtils::encrypt($event->id); ?>">View Details</a>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>There are no upcoming events at the moment. Please check back later.</p>
    <?php endif; ?>
</div>

<?php
require_once __DIR__ . '/includes/footer.php';
?>
