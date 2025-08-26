<?php
require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../utils/CSRF.php';
require_once __DIR__ . '/../repositories/UserRepository.php';
require_once __DIR__ . '/../utils/EmailSender.php';

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

if (!CSRF::validateToken($_POST['csrf_token'] ?? '', 'add_member_form')) {
    json_response(false, ['error' => 'Invalid request. Please try again.']);
}

// --- Form Data Validation ---
$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$role = $_POST['role'] ?? '';
$planner_id = $_SESSION['user_id'];

$errors = [];
if (empty($name)) $errors['name'] = "Name is required.";
if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['email'] = "A valid email is required.";
if (empty($password) || strlen($password) < 8) $errors['password'] = "Password must be at least 8 characters long.";

$allowed_roles = ['event_manager', 'gate_agent'];
if (empty($role) || !in_array($role, $allowed_roles)) {
    $errors['role'] = "Invalid role selected.";
}

if (!empty($errors)) {
    json_response(false, ['errors' => $errors]);
}

// --- Database Interaction ---
try {
    $userRepo = new UserRepository($pdo);

    if ($userRepo->findUserByEmail($email)) {
        json_response(false, ['errors' => ['email' => 'A user with this email address already exists.']]);
    }

    $user = new User();
    $user->name = $name;
    $user->email = $email;
    $user->role = $role;
    $user->parent_planner_id = $planner_id;
    $user->setPassword($password);

    if ($userRepo->createUser($user)) {
        // Send welcome email
        try {
            $emailSender = new EmailSender();
            $subject = "You have been added to a team on " . APP_NAME;
            $body = "<h1>Welcome!</h1>
                     <p>You have been added as a team member by your event planner.</p>
                     <p>You can now log in with your email and the password that was set for you.</p>";
            $emailSender->send($user->email, $subject, $body);
        } catch (Exception $e) {
            error_log("Failed to send team member welcome email: " . $e->getMessage());
        }
        json_response(true, ['message' => 'Team member added successfully!']);
    } else {
        json_response(false, ['error' => 'Database error: Failed to create team member.']);
    }

} catch (Exception $e) {
    error_log("Add Member Error: " . $e->getMessage());
    json_response(false, ['error' => 'An internal server error occurred.']);
}
