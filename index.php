<?php
require_once __DIR__ . '/bootstrap.php';

// If user is logged in, redirect to their dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

require_once __DIR__ . '/repositories/EventRepository.php';
require_once __DIR__ . '/utils/URLUtils.php';

$page_title = "Home";

try {
    $eventRepo = new EventRepository($pdo);
    $events = $eventRepo->findAllEvents('published');
} catch (Exception $e) {
    $events = [];
    $pageError = "Could not fetch events. Please try again later.";
    error_log("Homepage events error: " . $e->getMessage());
}

// Use the public layout for the homepage
require_once __DIR__ . '/includes/header-public.php';
?>

<!-- Custom hero section for the homepage -->
<div class="container my-5">
    <div class="p-5 text-center bg-light rounded-3">
        <h1 class="text-body-emphasis">Find Your Next Experience</h1>
        <p class="col-lg-8 mx-auto fs-5 text-muted">
            Buy tickets for the best events in town. Secure, fast, and easy.
        </p>
        <div class="d-inline-flex gap-2 mb-5">
            <a href="#events" class="d-inline-flex align-items-center btn btn-primary btn-lg px-4 rounded-pill" role="button">
                Browse Events
            </a>
            <a href="login.php" class="btn btn-outline-secondary btn-lg px-4 rounded-pill" role="button">
                Login / Register
            </a>
        </div>
    </div>
</div>

<div class="container" id="events">
    <h2 class="pb-2 border-bottom">Upcoming Events</h2>

    <?php if (isset($pageError)): ?>
        <div class="alert alert-danger"><?php echo $pageError; ?></div>
    <?php endif; ?>

    <div class="row row-cols-1 row-cols-lg-3 align-items-stretch g-4 py-5">
        <?php if (!empty($events)): ?>
            <?php foreach ($events as $event): ?>
                <div class="col">
                    <div class="card card-cover h-100 overflow-hidden text-bg-dark rounded-4 shadow-lg" style="background-image: url('<?php echo htmlspecialchars($event->banner ?: 'assets/images/default_banner.jpg'); ?>'); background-size: cover; background-position: center;">
                        <div class="d-flex flex-column h-100 p-5 pb-3 text-white text-shadow-1" style="background-color: rgba(0,0,0,0.5);">
                            <h3 class="pt-5 mt-5 mb-4 display-6 lh-1 fw-bold"><?php echo htmlspecialchars($event->title); ?></h3>
                            <ul class="d-flex list-unstyled mt-auto">
                                <li class="me-auto">
                                    <i class="fa fa-calendar"></i>
                                    <?php echo date('F j, Y', strtotime($event->date)); ?>
                                </li>
                                <li class="d-flex align-items-center">
                                    <a href="event.php?id=<?php echo URLUtils::encrypt($event->id); ?>" class="btn btn-sm btn-outline-light">View Details</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12">
                <p>There are no upcoming events at the moment. Please check back later.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
// We are using the public footer
require_once __DIR__ . '/includes/footer-public.php';
?>
