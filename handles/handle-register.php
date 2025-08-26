<?php
require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../utils/CSRF.php';
require_once __DIR__ . '/../classes/User.php';
require_once __DIR__ . '/../repositories/UserRepository.php';
require_once __DIR__ . '/../utils/EmailSender.php';

// Set header to return JSON
header('Content-Type: application/json');

// --- Response helper ---
function json_response($success, $data) {
    echo json_encode(['success' => $success] + $data);
    exit;
}

// --- CSRF Validation ---
if (!CSRF::validateToken($_POST['csrf_token'] ?? '', 'register_form')) {
    json_response(false, ['error' => 'Invalid request. Please try again.']);
}

// --- Request Method Check ---
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(false, ['error' => 'Invalid request method.']);
}

// --- Get and Validate Form Data ---
$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$role = $_POST['role'] ?? '';

$errors = [];
if (empty($name)) {
    $errors['name'] = "Name is required.";
}
if (empty($email)) {
    $errors['email'] = "Email is required.";
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors['email'] = "A valid email is required.";
}
if (empty($password)) {
    $errors['password'] = "Password is required.";
} elseif (strlen($password) < 8) {
    $errors['password'] = "Password must be at least 8 characters long.";
}
if (empty($role) || !in_array($role, ['attendee', 'planner'])) {
    $errors['role'] = "Invalid role selected.";
}

if (!empty($errors)) {
    json_response(false, ['errors' => $errors]);
}

// --- Check if user already exists ---
try {
    $userRepo = new UserRepository($pdo);
    if ($userRepo->findUserByEmail($email)) {
        json_response(false, ['errors' => ['email' => 'A user with this email address already exists.']]);
    }

    // --- Create and save the new user ---
    $user = new User();
    $user->name = $name;
    $user->email = $email;
    $user->role = $role;
    $user->setPassword($password); // Hash the password

    if ($userRepo->createUser($user)) {
        // Send welcome email (simulated)
        try {
            $emailSender = new EmailSender();
            $subject = "Welcome to " . APP_NAME . "!";
            $body = "<h1>Welcome, " . htmlspecialchars($user->name) . "!</h1><p>Thank you for registering. You can now log in and start browsing events.</p>";
            $emailSender->send($user->email, $subject, $body);
        } catch (Exception $e) {
            error_log("Failed to send welcome email: " . $e->getMessage());
        }

        json_response(true, ['message' => 'Registration successful! You can now log in.']);
    } else {
        json_response(false, ['error' => 'Failed to create user account.']);
    }

} catch (Exception $e) {
    error_log("Registration Error: " . $e->getMessage());
    json_response(false, ['error' => 'An internal server error occurred during registration.']);
}
