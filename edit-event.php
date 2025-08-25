<?php
session_start();

// A user must be logged in to access this page
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}
require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/repositories/EventRepository.php';
require_once __DIR__ . '/repositories/TicketRepository.php';
require_once __DIR__ . '/utils/AuthGuard.php';
require_once __DIR__ . '/utils/CSRF.php';

// Get event ID from URL
$event_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$event_id) {
    header("Location: dashboard.php"); // Redirect if no ID is provided
    exit;
}

// Security Check: Use the AuthGuard to verify permission
if (!AuthGuard::canEditEvent($pdo, $_SESSION['user_id'], $event_id)) {
    $_SESSION['error_message'] = "You do not have permission to edit this event.";
    header("Location: dashboard.php");
    exit;
}

// If permission is granted, fetch the event details for display
$eventRepo = new EventRepository($pdo);
$event = $eventRepo->findEventById($event_id);
$ticketRepo = new TicketRepository($pdo);
$csrf_token = CSRF::generateToken();

// Fetch tickets for this event
$tickets = $ticketRepo->findTicketsByEventId($event_id);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Event: <?php echo htmlspecialchars($event->title); ?></title>
    <style>
        body { font-family: sans-serif; }
        .container { padding: 20px; max-width: 960px; margin: auto; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .btn { background-color: #007bff; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .section { margin-top: 40px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; }
        input[type="text"], input[type="number"] { width: 100%; padding: 8px; box-sizing: border-box; }
        .message { padding: 10px; margin-bottom: 15px; border-radius: 5px; }
        .error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Edit Event</h1>
            <a href="dashboard.php" class="btn">Back to Dashboard</a>
        </div>

        <?php
        if (isset($_SESSION['success_message'])) {
            echo '<div class="message success">' . $_SESSION['success_message'] . '</div>';
            unset($_SESSION['success_message']);
        }
        if (isset($_SESSION['error_message'])) {
            echo '<div class="message error">' . $_SESSION['error_message'] . '</div>';
            unset($_SESSION['error_message']);
        }
        ?>

        <h2><?php echo htmlspecialchars($event->title); ?></h2>
        <p><strong>Date:</strong> <?php echo htmlspecialchars($event->date); ?></p>
        <p><strong>Location:</strong> <?php echo htmlspecialchars($event->location); ?></p>

        <div class="section">
            <h3>Ticket Types</h3>
            <?php if (!empty($tickets)): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Ticket Name</th>
                            <th>Price</th>
                            <th>Quantity</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tickets as $ticket): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($ticket->name); ?></td>
                                <td>$<?php echo htmlspecialchars(number_format($ticket->price, 2)); ?></td>
                                <td><?php echo htmlspecialchars($ticket->quantity); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No ticket types have been added for this event yet.</p>
            <?php endif; ?>
        </div>

        <div class="section">
            <h3>Add New Ticket Type</h3>
            <form action="handle-add-ticket.php" method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                <input type="hidden" name="event_id" value="<?php echo $event->id; ?>">
                <div class="form-group">
                    <label for="name">Ticket Name (e.g., General Admission, VIP)</label>
                    <input type="text" id="name" name="name" required>
                </div>
                <div class="form-group">
                    <label for="price">Price ($)</label>
                    <input type="number" id="price" name="price" min="0" step="0.01" required>
                </div>
                <div class="form-group">
                    <label for="quantity">Quantity Available</label>
                    <input type="number" id="quantity" name="quantity" min="1" required>
                </div>
                <button type="submit" class="btn">Add Ticket</button>
            </form>
        </div>

    </div>
</body>
</html>
