<?php
require_once __DIR__ . '/../../bootstrap.php';
require_once __DIR__ . '/../../utils/CSRF.php';
require_once __DIR__ . '/../../repositories/UserRepository.php';
require_once __DIR__ . '/../../utils/RateLimiter.php'; // Assuming mysqli is available via bootstrap

header('Content-Type: application/json');

function json_response($success, $data) {
    echo json_encode(['success' => $success] + $data);
    exit;
}

session_start();
if (!isset($_SESSION["user_id"]) || $_SESSION['user_role'] !== 'super_admin') {
    json_response(false, ['error' => 'Unauthorized']);
}

if (!CSRF::validateToken($_POST['csrf_token'] ?? '', 'edit_user_form')) {
    json_response(false, ['error' => 'Invalid request.']);
}

// --- Rate Limiting ---
// Note: This assumes $mysqli is available from bootstrap.php. If not, this needs adjustment.
// $rateLimiter = new RateLimiter($mysqli, $_SESSION['user_id']);
// $limitCheck = $rateLimiter->checkWithGlobal('edit_user');
// if (!$limitCheck['allowed']) {
//     json_response(false, ['error' => "You are performing this action too frequently. Please try again in " . $limitCheck['retry_after'] . " seconds."]);
// }

// --- Form Data Validation ---
$user_id = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT);
$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$role = $_POST['role'] ?? '';
$status = $_POST['status'] ?? '';

$errors = [];
if (!$user_id) $errors['general'] = "Invalid user ID.";
if (empty($name)) $errors['name'] = "Name is required.";
if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['email'] = "A valid email is required.";
if (empty($role) || !in_array($role, ['attendee', 'planner', 'super_admin'])) $errors['role'] = "A valid role is required.";
if (empty($status) || !in_array($status, ['active', 'suspended'])) $errors['status'] = "A valid status is required.";


if (!empty($errors)) {
    json_response(false, isset($errors['general']) ? ['error' => $errors['general']] : ['errors' => $errors]);
}

try {
    $userRepo = new UserRepository($pdo);

    // Check if email is being changed to one that already exists
    $existingUser = $userRepo->findUserByEmail($email);
    if ($existingUser && $existingUser->id !== $user_id) {
        json_response(false, ['errors' => ['email' => 'This email address is already in use by another account.']]);
    }

    if ($userRepo->updateUserAsAdmin($user_id, $name, $email, $role, $status)) {
        json_response(true, ['message' => 'User updated successfully.']);
    } else {
        json_response(false, ['error' => 'Failed to update user.']);
    }
} catch (Exception $e) {
    error_log("Admin Edit User Error: " . $e->getMessage());
    json_response(false, ['error' => 'An internal server error occurred.']);
}
