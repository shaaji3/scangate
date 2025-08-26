<?php
require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../utils/CSRF.php';
require_once __DIR__ . '/../repositories/UserRepository.php';
require_once __DIR__ . '/../repositories/PasswordResetRepository.php';

header('Content-Type: application/json');

function json_response($success, $data) {
    echo json_encode(['success' => $success] + $data);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(false, ['error' => 'Invalid request method.']);
}

// --- CSRF and Form Data Validation ---
if (!CSRF::validateToken($_POST['csrf_token'] ?? '', 'reset_password_form')) {
    json_response(false, ['error' => 'Invalid request. Please try again.']);
}

$token = $_POST['token'] ?? '';
$password = $_POST['password'] ?? '';
$password_confirm = $_POST['password_confirm'] ?? '';

$errors = [];
if (empty($token)) {
    $errors['general'] = "Invalid reset request.";
}
if (empty($password)) {
    $errors['password'] = "Password is required.";
} elseif (strlen($password) < 8) {
    $errors['password'] = "Password must be at least 8 characters long.";
}
if ($password !== $password_confirm) {
    $errors['password_confirm'] = "Passwords do not match.";
}

if (!empty($errors)) {
    // Use 'errors' for field-specific, 'error' for general
    if (isset($errors['general'])) {
        json_response(false, ['error' => $errors['general']]);
    }
    json_response(false, ['errors' => $errors]);
}

// --- Token and User Validation ---
try {
    $resetRepo = new PasswordResetRepository($pdo);
    $token_hash = hash('sha256', $token);
    $reset_data = $resetRepo->findToken($token_hash);

    if (!$reset_data) {
        json_response(false, ['error' => 'This password reset link is invalid. It may have already been used.']);
    }

    if (time() > $reset_data['expires']) {
        json_response(false, ['error' => 'This password reset link has expired. Please request a new one.']);
    }

    $userRepo = new UserRepository($pdo);
    $user = $userRepo->findUserByEmail($reset_data['email']);

    if (!$user) {
        // This case is unlikely if the reset token exists, but good to handle.
        json_response(false, ['error' => 'No user found for this reset request.']);
    }

    // --- Update Password and Clean Up ---
    $user->setPassword($password); // Hashes the new password
    if ($userRepo->updateUserPassword($user->id, $user->password)) {
        // Password updated successfully, now delete the reset token
        $resetRepo->deleteTokenForEmail($user->email);

        json_response(true, [
            'message' => 'Your password has been reset successfully! You can now log in.',
            'redirect_url' => 'login.php'
        ]);
    } else {
        json_response(false, ['error' => 'Failed to update your password.']);
    }

} catch (Exception $e) {
    error_log("Reset Password Error: " . $e->getMessage());
    json_response(false, ['error' => 'An internal server error occurred.']);
}
