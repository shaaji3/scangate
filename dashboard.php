<?php
require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/repositories/EventRepository.php';
require_once __DIR__ . '/repositories/UserRepository.php';

// Check if the user is logged in, otherwise redirect to login page
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

$user_role = $_SESSION['user_role'];
$user_id = $_SESSION['user_id'];

$page_title = "Dashboard";

// Depending on the role, we fetch different data
$planner_events = [];
$attendee_tickets = [];

if ($user_role === 'planner') {
    $eventRepo = new EventRepository($pdo);
    $planner_events = $eventRepo->findEventsByPlanner($user_id);
} elseif ($user_role === 'attendee') {
    $userRepo = new UserRepository($pdo);
    $attendee_tickets = $userRepo->findUserTickets($user_id);
}

require_once __DIR__ . '/includes/header.php';
?>

<style>
    /* Page-specific styles */
    .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
    .header h1 { margin: 0; }
    .btn { background-color: #007bff; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px; display: inline-block; margin-bottom: 10px; }
    .btn-download { background-color: #28a745; }
    table { width: 100%; border-collapse: collapse; margin-top: 20px; }
    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
    th { background-color: #f2f2f2; }
</style>

<div class="header">
    <h1>Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</h1>
</div>

<h2>My Dashboard</h2>

<?php if ($user_role === 'planner'): ?>
    <h3>My Events</h3>
    <a href="create-event.php" class="btn">Create New Event</a>
    <a href="manage-team.php" class="btn" style="margin-left: 10px;">Manage Team</a>
    <?php if (!empty($planner_events)): ?>
        <table>
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
                        <td><?php echo htmlspecialchars($event->status); ?></td>
                        <td>
                            <a href="edit-event.php?id=<?php echo $event->id; ?>">Manage</a> |
                            <a href="scan-ticket.php?event_id=<?php echo $event->id; ?>">Scan Tickets</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>You have not created any events yet.</p>
    <?php endif; ?>

<?php elseif ($user_role === 'attendee'): ?>
    <h3>My Tickets</h3>
    <?php if (!empty($attendee_tickets)): ?>
        <table>
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
                        <td><a href="download-ticket.php?code=<?php echo htmlspecialchars($ticket['ticket_code']); ?>" class="btn btn-download">Download PDF</a></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>You have not purchased any tickets yet.</p>
    <?php endif; ?>

<?php endif; ?>

<?php
require_once __DIR__ . '/includes/footer.php';
?>
