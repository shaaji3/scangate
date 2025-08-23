<?php
session_start();

// Check if the user is logged in, otherwise redirect to login page
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

// Include necessary files
require_once 'config/database.php';
require_once 'repositories/EventRepository.php';

$user_role = $_SESSION['user_role'];
$user_id = $_SESSION['user_id'];

$page_title = "Dashboard";

// Depending on the role, we might fetch different data
$events = [];
if ($user_role === 'planner') {
    $eventRepo = new EventRepository($pdo);
    $events = $eventRepo->findEventsByPlanner($user_id);
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($page_title); ?> - Online Ticketing System</title>
    <style>
        body { font-family: sans-serif; }
        .container { padding: 20px; max-width: 960px; margin: auto; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .header h1 { margin: 0; }
        .btn { background-color: #007bff; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</h1>
            <a href="logout.php">Logout</a>
        </div>

        <h2>My Dashboard</h2>
        <p>Your role is: <?php echo htmlspecialchars($user_role); ?></p>

        <?php if ($user_role === 'planner'): ?>
            <h3>My Events</h3>
            <a href="create-event.php" class="btn">Create New Event</a>
            <?php if (!empty($events)): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Date</th>
                            <th>Location</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($events as $event): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($event->title); ?></td>
                                <td><?php echo htmlspecialchars($event->date); ?></td>
                                <td><?php echo htmlspecialchars($event->location); ?></td>
                                <td><?php echo htmlspecialchars($event->status); ?></td>
                                <td><a href="edit-event.php?id=<?php echo $event->id; ?>">Edit</a></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>You have not created any events yet.</p>
            <?php endif; ?>

        <?php elseif ($user_role === 'attendee'): ?>
            <h3>My Bookings</h3>
            <p>You have not booked any tickets yet.</p>

        <?php endif; ?>

    </div>
</body>
</html>
