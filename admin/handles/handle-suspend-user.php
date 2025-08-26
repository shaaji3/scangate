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

// For this action, we expect the token and user ID in the POST body
$user_id = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT);
$csrf_token = $_POST['csrf_token'] ?? '';

if (!CSRF::validateToken($csrf_token, 'suspend_user_form')) {
    json_response(false, ['error' => 'Invalid request.']);
}

if (!$user_id) {
    json_response(false, ['error' => 'Invalid user ID.']);
}

try {
    $userRepo = new UserRepository($pdo);
    $user = $userRepo->findUserById($user_id);

    if (!$user) {
        json_response(false, ['error' => 'User not found.']);
    }

    // Toggle status
    $new_status = ($user->status === 'active') ? 'suspended' : 'active';

    if ($userRepo->updateUserStatus($user_id, $new_status)) {
        $action = ($new_status === 'suspended') ? 'suspended' : 'activated';
        json_response(true, ['message' => 'User has been successfully ' . $action . '.']);
    } else {
        json_response(false, ['error' => 'Failed to update user status.']);
    }
} catch (Exception $e) {
    error_log("Admin Suspend User Error: " . $e->getMessage());
    json_response(false, ['error' => 'An internal server error occurred.']);
}
