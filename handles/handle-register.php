<?php
require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/utils/CSRF.php';
require_once __DIR__ . '/classes/User.php';
require_once __DIR__ . '/repositories/UserRepository.php';
require_once __DIR__ . '/utils/EmailSender.php';

if (!CSRF::validateToken($_POST['csrf_token'] ?? '')) {
    die("CSRF token validation failed.");
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // Redirect or show an error if not a POST request
    header('Location: register.php');
    exit;
}

// 1. Get form data
$name = trim($_POST['name']);
$email = trim($_POST['email']);
$password = $_POST['password'];
$role = $_POST['role'];

// 2. Validate input
$errors = [];
if (empty($name)) {
    $errors[] = "Name is required.";
}
if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "A valid email is required.";
}
if (empty($password) || strlen($password) < 6) {
    $errors[] = "Password must be at least 6 characters long.";
}
if (!in_array($role, ['attendee', 'planner'])) {
    $errors[] = "Invalid role selected.";
}

if (!empty($errors)) {
    $_SESSION['error_message'] = implode('<br>', $errors);
    header('Location: register.php');
    exit;
}

// 3. Check if user already exists
$userRepo = new UserRepository($pdo);
$existingUser = $userRepo->findUserByEmail($email);

if ($existingUser) {
    $_SESSION['error_message'] = "A user with this email address already exists.";
    header('Location: register.php');
    exit;
}

// 4. Create and save the new user
try {
    $user = new User();
    $user->name = $name;
    $user->email = $email;
    $user->role = $role;
    $user->setPassword($password); // Hash the password

    if ($userRepo->createUser($user)) {
        $_SESSION['success_message'] = "Registration successful! Please log in.";

        // Send welcome email
        try {
            $emailSender = new EmailSender();
            $subject = "Welcome to " . APP_NAME . "!";
            $body = "<h1>Welcome, " . htmlspecialchars($user->name) . "!</h1>
                     <p>Thank you for registering. You can now log in and start browsing events.</p>";
            $emailSender->send($user->email, $subject, $body);
        } catch (Exception $e) {
            // Log error but don't block the user registration
            error_log("Failed to send welcome email: " . $e->getMessage());
        }

        header('Location: login.php');
        exit;
    } else {
        throw new Exception("Failed to create user.");
    }
} catch (Exception $e) {
    $_SESSION['error_message'] = "An error occurred during registration: " . $e->getMessage();
    header('Location: register.php');
    exit;
}
