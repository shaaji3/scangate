<?php
require_once __DIR__ . '/../../bootstrap.php';
require_once __DIR__ . '/../../utils/CSRF.php';
require_once __DIR__ . '/../../repositories/UserRepository.php';

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

$user_id = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT);
$csrf_token = $_POST['csrf_token'] ?? '';

if (!CSRF::validateToken($csrf_token, 'delete_user_form')) {
    json_response(false, ['error' => 'Invalid request.']);
}

if (!$user_id) {
    json_response(false, ['error' => 'Invalid user ID.']);
}

// Prevent admin from deleting themselves
if ($user_id === $_SESSION['user_id']) {
    json_response(false, ['error' => 'You cannot delete your own account.']);
}

try {
    $userRepo = new UserRepository($pdo);

    if ($userRepo->deleteUser($user_id)) {
        json_response(true, ['message' => 'User has been successfully deleted.']);
    } else {
        json_response(false, ['error' => 'Failed to delete user. The user may have associated records.']);
    }
} catch (Exception $e) {
    error_log("Admin Delete User Error: " . $e->getMessage());
    json_response(false, ['error' => 'An internal server error occurred.']);
}
