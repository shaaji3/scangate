<?php
require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../utils/CSRF.php';
require_once __DIR__ . '/../repositories/UserRepository.php';

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
if (!isset($_SESSION["user_id"])) {
    json_response(false, ['error' => 'You must be logged in to do that.']);
}

if (!CSRF::validateToken($_POST['csrf_token'] ?? '', 'update_profile_form')) {
    json_response(false, ['error' => 'Invalid request. Please try again.']);
}

// --- Form Data Validation ---
$name = trim($_POST['name'] ?? '');
if (empty($name)) {
    json_response(false, ['errors' => ['name' => 'Name cannot be empty.']]);
}

// --- Database Interaction ---
try {
    $userRepo = new UserRepository($pdo);
    if ($userRepo->updateUserName($_SESSION['user_id'], $name)) {
        // Update the name in the session as well
        $_SESSION['user_name'] = $name;
        json_response(true, ['message' => 'Profile updated successfully!']);
    } else {
        json_response(false, ['error' => 'Failed to update profile.']);
    }
} catch (Exception $e) {
    error_log("Update Profile Error: " . $e->getMessage());
    json_response(false, ['error' => 'An internal server error occurred.']);
}
