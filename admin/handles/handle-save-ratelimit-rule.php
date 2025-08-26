<?php
require_once __DIR__ . '/../../bootstrap.php';
require_once __DIR__ . '/../../utils/CSRF.php';
require_once __DIR__ . '/../../repositories/RateLimitRepository.php';

header('Content-Type: application/json');

function json_response($success, $data) {
    echo json_encode(['success' => $success] + $data);
    exit;
}

session_start();
if (!isset($_SESSION["user_id"]) || $_SESSION['user_role'] !== 'super_admin') {
    json_response(false, ['error' => 'Unauthorized']);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(false, ['error' => 'Invalid request method.']);
}

if (!CSRF::validateToken($_POST['csrf_token'] ?? '', 'ratelimit_form')) {
    json_response(false, ['error' => 'Invalid request.']);
}

// --- Form Data Validation ---
$action = trim($_POST['action'] ?? '');
$limit_count = filter_input(INPUT_POST, 'limit_count', FILTER_VALIDATE_INT);
$window_seconds = filter_input(INPUT_POST, 'window_seconds', FILTER_VALIDATE_INT);

$errors = [];
if (empty($action) || !preg_match('/^[a-z0-9_]+$/', $action)) {
    $errors['action'] = "Action name must be lowercase letters, numbers, and underscores only.";
}
if ($limit_count === false || $limit_count < 1) {
    $errors['limit_count'] = "Limit count must be a number greater than 0.";
}
if ($window_seconds === false || $window_seconds < 1) {
    $errors['window_seconds'] = "Window must be a number greater than 0.";
}

if (!empty($errors)) {
    json_response(false, ['errors' => $errors]);
}

// --- Database Interaction ---
try {
    $repo = new RateLimitRepository($pdo);
    if ($repo->saveRule($action, $limit_count, $window_seconds)) {
        json_response(true, ['message' => 'Rate limit rule saved successfully! Page will reload.']);
    } else {
        json_response(false, ['error' => 'Failed to save the rule.']);
    }
} catch (Exception $e) {
    error_log("Save Rate Limit Rule Error: " . $e->getMessage());
    json_response(false, ['error' => 'An internal server error occurred.']);
}
