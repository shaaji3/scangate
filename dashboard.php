<?php
require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/repositories/EventRepository.php';
require_once __DIR__ . '/repositories/UserRepository.php';
require_once __DIR__ . '/utils/URLUtils.php';

// AuthGuard is included in header-auth.php
$user_role = $_SESSION['user_role'];
$user_id = $_SESSION['user_id'];

$page_title = "Dashboard";

// Depending on the role, we fetch different data
$planner_events = [];
$attendee_tickets = [];

try {
    if ($user_role === 'planner') {
        $eventRepo = new EventRepository($pdo);
        $planner_events = $eventRepo->findEventsByPlanner($user_id);
    } elseif ($user_role === 'attendee') {
        $userRepo = new UserRepository($pdo);
        $attendee_tickets = $userRepo->findUserTickets($user_id);
    }
} catch (Exception $e) {
    $page_error = "Could not fetch dashboard data. Please try again later.";
    error_log("Dashboard Error: " . $e->getMessage());
}

require_once __DIR__ . '/includes/header-auth.php';
require_once __DIR__ . '/includes/sidebar.php';
?>

<!--**********************************
    Content body start
***********************************-->
<div class="content-body rightside-event">
    <!-- row -->
    <div class="container-fluid">
        <div class="row">
            <div class="col-xl-12">
                <div class="welcome-card rounded ps-5 pt-5 pb-4 mt-3 position-relative mb-5">
                    <h4 class="text-warning">Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</h4>
                    <p>Here is a summary of your events and sales.</p>
                    <a class="btn btn-warning btn-rounded" href="my-events.php">View My Events</a>
                    <a class="btn-link text-dark ms-3" href="create-event.php">Create New Event</a>
                    <img src="images/svg/welcom-card.svg" alt="" class="position-absolute">
                </div>
            </div>

            <?php if (isset($page_error)): ?>
                <div class="col-xl-12">
                    <div class="alert alert-danger"><?php echo $page_error; ?></div>
                </div>
            <?php endif; ?>

            <?php if ($user_role === 'planner'): ?>
                <div class="col-xl-12">
						<div id="user-activity" class="card">
							<div class="card-header border-0 pb-0 d-sm-flex d-block">
								<div>
									<h4 class="card-title mb-1">My Events</h4>
								</div>
								<div class="card-action card-tabs mt-3 mt-sm-0">
                                    <a href="create-event.php" class="btn btn-primary">Create New Event</a>
                                    <a href="manage-team.php" class="btn btn-info" style="margin-left: 10px;">Manage Team</a>
								</div>
							</div>
							<div class="card-body">
                                <?php if (!empty($planner_events)): ?>
                                    <div class="table-responsive">
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Title</th>
                                                    <th>Date</th>
                                                    <th>Status</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($planner_events as $event): ?>
                                                    <tr>
                                                        <td><?php echo htmlspecialchars($event->title); ?></td>
                                                        <td><?php echo date('F j, Y, g:i a', strtotime($event->date)); ?></td>
                                                        <td><span class="badge light badge-primary"><?php echo htmlspecialchars($event->status); ?></span></td>
                                                        <td>
                                                            <a href="edit-event.php?id=<?php echo URLUtils::encrypt($event->id); ?>" class="btn btn-sm btn-secondary">Manage</a>
                                                            <a href="scan-ticket.php?event_id=<?php echo URLUtils::encrypt($event->id); ?>" class="btn btn-sm btn-success">Scan Tickets</a>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php else: ?>
                                    <p>You have not created any events yet.</p>
                                <?php endif; ?>
							</div>
						</div>
					</div>

            <?php elseif ($user_role === 'attendee'): ?>
                <div class="col-xl-12">
                    <h5 class="card-title">My Tickets</h5>
                    <?php if (!empty($attendee_tickets)): ?>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Event</th>
                                        <th>Ticket Type</th>
                                        <th>Event Date</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($attendee_tickets as $ticket): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($ticket['event_title']); ?></td>
                                            <td><?php echo htmlspecialchars($ticket['ticket_type']); ?></td>
                                            <td><?php echo date('F j, Y, g:i a', strtotime($ticket['event_date'])); ?></td>
                                            <td><a href="download-ticket.php?code=<?php echo htmlspecialchars($ticket['ticket_code']); ?>" class="btn btn-sm btn-primary">Download PDF</a></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p>You have not purchased any tickets yet.</p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<!--**********************************
    Content body end
***********************************-->

<?php
require_once __DIR__ . '/includes/footer-auth.php';
?>
