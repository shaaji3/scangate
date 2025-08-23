<?php
session_start();

require_once 'config/database.php';
require_once 'repositories/UserRepository.php'; // User class is included in here

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit;
}

// 1. Get form data
$email = trim($_POST['email']);
$password = $_POST['password'];

// 2. Validate input
if (empty($email) || empty($password)) {
    $_SESSION['error_message'] = "Email and password are required.";
    header('Location: login.php');
    exit;
}

// 3. Find user and verify password
$userRepo = new UserRepository($pdo);
$user = $userRepo->findUserByEmail($email);

if ($user && $user->verifyPassword($password)) {
    // 4. Authentication successful
    session_regenerate_id(true); // Prevent session fixation attacks
    $_SESSION['user_id'] = $user->id;
    $_SESSION['user_role'] = $user->role;
    $_SESSION['user_name'] = $user->name;

    // 5. Redirect based on role
    if ($user->role === 'super_admin') {
        header('Location: admin-dashboard.php');
    } else {
        header('Location: dashboard.php');
    }
    exit;
} else {
    // 6. Authentication failed
    $_SESSION['error_message'] = "Invalid email or password.";
    header('Location: login.php');
    exit;
}
