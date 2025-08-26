<?php
require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../utils/CSRF.php';
require_once __DIR__ . '/../repositories/EventRepository.php';

header('Content-Type: application/json');

function json_response($success, $data) {
    echo json_encode(['success' => $success] + $data);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(false, ['error' => 'Invalid request method.']);
}

session_start();

// --- Auth and CSRF Validation ---
if (!isset($_SESSION["user_id"]) || $_SESSION['user_role'] !== 'planner') {
    json_response(false, ['error' => 'You are not authorized to perform this action.']);
}

if (!CSRF::validateToken($_POST['csrf_token'] ?? '', 'create_event_form')) {
    json_response(false, ['error' => 'Invalid request. Please try again.']);
}

// --- Form Data Validation ---
$title = trim($_POST['title'] ?? '');
$description = trim($_POST['description'] ?? '');
$location = trim($_POST['location'] ?? '');
$date = $_POST['date'] ?? '';
$status = $_POST['status'] ?? '';
$planner_id = $_SESSION['user_id'];
$banner_path = null;

$errors = [];
if (empty($title)) $errors['title'] = "Title is required.";
if (empty($description)) $errors['description'] = "Description is required.";
if (empty($location)) $errors['location'] = "Location is required.";
if (empty($date)) $errors['date'] = "Date is required.";
if (!in_array($status, ['draft', 'published'])) $errors['status'] = "Invalid status.";

// --- File Upload Handling ---
if (isset($_FILES['banner']) && $_FILES['banner']['error'] === UPLOAD_ERR_OK) {
    $upload_dir = __DIR__ . '/../upload/banners/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
    $file_type = mime_content_type($_FILES['banner']['tmp_name']);

    if (in_array($file_type, $allowed_types)) {
        $file_extension = pathinfo($_FILES['banner']['name'], PATHINFO_EXTENSION);
        $unique_filename = uniqid('banner_', true) . '.' . $file_extension;
        $banner_path_on_disk = $upload_dir . $unique_filename;
        $banner_path_for_db = 'upload/banners/' . $unique_filename;

        if (!move_uploaded_file($_FILES['banner']['tmp_name'], $banner_path_on_disk)) {
            $errors['banner'] = "Failed to upload banner image.";
        }
    } else {
        $errors['banner'] = "Invalid file type. Only JPG, PNG, and GIF are allowed.";
    }
}

if (!empty($errors)) {
    json_response(false, ['errors' => $errors]);
}

// --- Database Interaction ---
try {
    $eventRepo = new EventRepository($pdo);
    $event = new Event([
        'planner_id' => $planner_id,
        'title' => $title,
        'description' => $description,
        'location' => $location,
        'date' => $date,
        'banner' => $banner_path_for_db ?? null,
        'status' => $status,
    ]);

    if ($eventRepo->createEvent($event)) {
        json_response(true, ['message' => 'Event created successfully!']);
    } else {
        json_response(false, ['error' => 'Database error: Failed to create event.']);
    }
} catch (Exception $e) {
    error_log("Create Event Error: " . $e->getMessage());
    if (isset($banner_path_on_disk) && file_exists($banner_path_on_disk)) {
        unlink($banner_path_on_disk);
    }
    json_response(false, ['error' => 'An internal server error occurred.']);
}
