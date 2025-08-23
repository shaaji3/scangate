<?php
session_start();

// Only planners can access this page
if (!isset($_SESSION["user_id"]) || $_SESSION['user_role'] !== 'planner') {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: create-event.php');
    exit;
}

require_once 'config/database.php';
require_once 'repositories/EventRepository.php';

// 1. Validate form data
$title = trim($_POST['title']);
$description = trim($_POST['description']);
$location = trim($_POST['location']);
$date = $_POST['date'];
$status = $_POST['status'];
$planner_id = $_SESSION['user_id'];
$banner_path = null;

$errors = [];
if (empty($title)) $errors[] = "Title is required.";
if (empty($description)) $errors[] = "Description is required.";
if (empty($location)) $errors[] = "Location is required.";
if (empty($date)) $errors[] = "Date is required.";
if (!in_array($status, ['draft', 'published'])) $errors[] = "Invalid status.";

// 2. Handle file upload
if (isset($_FILES['banner']) && $_FILES['banner']['error'] === UPLOAD_ERR_OK) {
    $upload_dir = 'upload/';
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
    $file_type = $_FILES['banner']['type'];

    if (in_array($file_type, $allowed_types)) {
        // Create a unique filename to prevent overwriting
        $file_extension = pathinfo($_FILES['banner']['name'], PATHINFO_EXTENSION);
        $unique_filename = uniqid('banner_', true) . '.' . $file_extension;
        $banner_path = $upload_dir . $unique_filename;

        if (!move_uploaded_file($_FILES['banner']['tmp_name'], $banner_path)) {
            $errors[] = "Failed to upload banner image.";
            $banner_path = null; // Reset path on failure
        }
    } else {
        $errors[] = "Invalid file type for banner. Only JPG, PNG, and GIF are allowed.";
    }
}

if (!empty($errors)) {
    $_SESSION['error_message'] = implode('<br>', $errors);
    header('Location: create-event.php');
    exit;
}

// 3. Create and save the event
try {
    $eventRepo = new EventRepository($pdo);
    $event = new Event([
        'planner_id' => $planner_id,
        'title' => $title,
        'description' => $description,
        'location' => $location,
        'date' => $date,
        'banner' => $banner_path,
        'status' => $status,
    ]);

    if ($eventRepo->createEvent($event)) {
        $_SESSION['success_message'] = "Event created successfully!";
        header('Location: dashboard.php');
        exit;
    } else {
        throw new Exception("Database error: Failed to create event.");
    }

} catch (Exception $e) {
    $_SESSION['error_message'] = "An error occurred: " . $e->getMessage();
    // Clean up uploaded file if database insertion fails
    if ($banner_path && file_exists($banner_path)) {
        unlink($banner_path);
    }
    header('Location: create-event.php');
    exit;
}
