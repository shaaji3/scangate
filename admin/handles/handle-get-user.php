<?php
require_once __DIR__ . '/../../bootstrap.php';
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

$user_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$user_id) {
    json_response(false, ['error' => 'Invalid user ID.']);
}

try {
    $userRepo = new UserRepository($pdo);
    $user = $userRepo->findUserById($user_id);

    if ($user) {
        // We don't want to send the password hash
        unset($user->password);
        json_response(true, ['user' => $user]);
    } else {
        json_response(false, ['error' => 'User not found.']);
    }
} catch (Exception $e) {
    error_log("Get User Error: " . $e->getMessage());
    json_response(false, ['error' => 'An internal server error occurred.']);
}
