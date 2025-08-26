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

if (!CSRF::validateToken($_POST['csrf_token'] ?? '', 'add_user_form')) {
    json_response(false, ['error' => 'Invalid request.']);
}

// --- Form Data Validation ---
$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$role = $_POST['role'] ?? '';

$errors = [];
if (empty($name)) $errors['name'] = "Name is required.";
if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['email'] = "A valid email is required.";
if (empty($password) || strlen($password) < 8) $errors['password'] = "Password must be at least 8 characters long.";
if (empty($role) || !in_array($role, ['attendee', 'planner', 'super_admin'])) $errors['role'] = "A valid role is required.";

if (!empty($errors)) {
    json_response(false, ['errors' => $errors]);
}

try {
    $userRepo = new UserRepository($pdo);
    if ($userRepo->findUserByEmail($email)) {
        json_response(false, ['errors' => ['email' => 'A user with this email already exists.']]);
    }

    $user = new User();
    $user->name = $name;
    $user->email = $email;
    $user->role = $role;
    $user->setPassword($password);

    if ($userRepo->createUser($user)) {
        json_response(true, ['message' => 'User created successfully.']);
    } else {
        json_response(false, ['error' => 'Failed to create user.']);
    }
} catch (Exception $e) {
    error_log("Admin Add User Error: " . $e->getMessage());
    json_response(false, ['error' => 'An internal server error occurred.']);
}
