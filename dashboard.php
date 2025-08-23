<?php
session_start();

// Check if the user is logged in, otherwise redirect to login page
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

// Check for allowed roles
$allowed_roles = ['attendee', 'planner'];
if (!in_array($_SESSION['user_role'], $allowed_roles)) {
    echo "<h1>Access Denied</h1>";
    echo "<p>You do not have permission to view this page.</p>";
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <style>
        body { font-family: sans-serif; }
        .container { padding: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Welcome to your Dashboard, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</h1>
        <p>Your role is: <?php echo htmlspecialchars($_SESSION['user_role']); ?></p>
        <p><a href="logout.php">Logout</a></p>
    </div>
</body>
</html>
