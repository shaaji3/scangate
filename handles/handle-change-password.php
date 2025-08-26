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

if (!CSRF::validateToken($_POST['csrf_token'] ?? '', 'change_password_form')) {
    json_response(false, ['error' => 'Invalid request. Please try again.']);
}

// --- Form Data Validation ---
$current_password = $_POST['current_password'] ?? '';
$new_password = $_POST['new_password'] ?? '';
$password_confirm = $_POST['password_confirm'] ?? '';

$errors = [];
if (empty($current_password)) $errors['current_password'] = "Current password is required.";
if (empty($new_password)) {
    $errors['new_password'] = "New password is required.";
} elseif (strlen($new_password) < 8) {
    $errors['new_password'] = "Password must be at least 8 characters long.";
}
if ($new_password !== $password_confirm) {
    $errors['password_confirm'] = "Passwords do not match.";
}

if (!empty($errors)) {
    json_response(false, ['errors' => $errors]);
}

// --- Database Interaction ---
try {
    $userRepo = new UserRepository($pdo);
    $user = $userRepo->findUserById($_SESSION['user_id']);

    if (!$user || !$user->verifyPassword($current_password)) {
        json_response(false, ['errors' => ['current_password' => 'Current password is not correct.']]);
    }

    $user->setPassword($new_password);
    if ($userRepo->updateUserPassword($user->id, $user->password)) {
        json_response(true, ['message' => 'Password changed successfully!']);
    } else {
        json_response(false, ['error' => 'Failed to change password.']);
    }

} catch (Exception $e) {
    error_log("Change Password Error: " . $e->getMessage());
    json_response(false, ['error' => 'An internal server error occurred.']);
}
