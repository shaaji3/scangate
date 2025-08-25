<?php
session_start();

// Security: Only planners can access
if (!isset($_SESSION["user_id"]) || $_SESSION['user_role'] !== 'planner') {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: manage-team.php');
    exit;
}

require_once 'config/database.php';
require_once 'repositories/UserRepository.php';
require_once 'classes/User.php';

// 1. Get and validate form data
$name = trim($_POST['name']);
$email = trim($_POST['email']);
$role = $_POST['role'];
$planner_id = $_SESSION['user_id'];

$errors = [];
if (empty($name)) {
    $errors[] = "Name is required.";
}
if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "A valid email is required.";
}
// Ensure the role is a valid team member role
$allowed_roles = ['event_manager', 'gate_agent'];
if (!in_array($role, $allowed_roles)) {
    $errors[] = "Invalid role selected.";
}

if (!empty($errors)) {
    $_SESSION['error_message'] = implode('<br>', $errors);
    header('Location: manage-team.php');
    exit;
}

$userRepo = new UserRepository($pdo);

// 2. Check if user already exists
if ($userRepo->findUserByEmail($email)) {
    $_SESSION['error_message'] = "A user with this email address already exists.";
    header('Location: manage-team.php');
    exit;
}

// 3. Create the new team member
try {
    $user = new User();
    $user->name = $name;
    $user->email = $email;
    $user->role = $role;
    $user->parent_planner_id = $planner_id;

    // Generate a temporary password
    $temporary_password = bin2hex(random_bytes(8));
    $user->setPassword($temporary_password);

    if ($userRepo->createUser($user)) {
        // In a real app, you would email this password. For now, display it.
        $_SESSION['success_message'] = "Team member added successfully! <br>Email: " . htmlspecialchars($email) . "<br>Temporary Password: <strong>" . $temporary_password . "</strong>";
    } else {
        throw new Exception("Failed to create team member.");
    }

} catch (Exception $e) {
    $_SESSION['error_message'] = "An error occurred: " . $e->getMessage();
}

header('Location: manage-team.php');
exit;
