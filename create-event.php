<?php
session_start();

// Only planners can access this page
if (!isset($_SESSION["user_id"]) || $_SESSION['user_role'] !== 'planner') {
    // Redirect to login or a generic access denied page
    header("Location: login.php");
    exit;
}
require_once 'utils/CSRF.php';
$csrf_token = CSRF::generateToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create New Event</title>
    <style>
        body { font-family: sans-serif; }
        .container { max-width: 600px; margin: 50px auto; padding: 20px; border: 1px solid #ccc; border-radius: 5px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; }
        input[type="text"], input[type="datetime-local"], textarea, select { width: 100%; padding: 8px; box-sizing: border-box; }
        textarea { height: 120px; }
        button { background-color: #007bff; color: white; padding: 10px 15px; border: none; border-radius: 5px; cursor: pointer; }
        .message { padding: 10px; margin-bottom: 15px; border-radius: 5px; }
        .error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Create a New Event</h2>
        <?php
        if (isset($_SESSION['error_message'])) {
            echo '<div class="message error">' . $_SESSION['error_message'] . '</div>';
            unset($_SESSION['error_message']);
        }
        ?>
        <form action="handle-create-event.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
            <div class="form-group">
                <label for="title">Event Title</label>
                <input type="text" id="title" name="title" required>
            </div>
            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" required></textarea>
            </div>
            <div class="form-group">
                <label for="location">Location</label>
                <input type="text" id="location" name="location" required>
            </div>
            <div class="form-group">
                <label for="date">Date and Time</label>
                <input type="datetime-local" id="date" name="date" required>
            </div>
            <div class="form-group">
                <label for="banner">Event Banner</label>
                <input type="file" id="banner" name="banner" accept="image/png, image/jpeg, image/gif">
            </div>
             <div class="form-group">
                <label for="status">Status</label>
                <select id="status" name="status" required>
                    <option value="draft">Draft</option>
                    <option value="published">Published</option>
                </select>
            </div>
            <button type="submit">Create Event</button>
        </form>
    </div>
</body>
</html>
