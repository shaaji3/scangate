<?php
require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../utils/CSRF.php';
require_once __DIR__ . '/../repositories/UserRepository.php';
require_once __DIR__ . '/../repositories/PasswordResetRepository.php';
require_once __DIR__ . '/../utils/EmailSender.php';

header('Content-Type: application/json');

function json_response($success, $data) {
    echo json_encode(['success' => $success] + $data);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(false, ['error' => 'Invalid request method.']);
}

if (!CSRF::validateToken($_POST['csrf_token'] ?? '', 'forgot_password_form')) {
    json_response(false, ['error' => 'Invalid request. Please try again.']);
}

$email = trim($_POST['email'] ?? '');

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    json_response(false, ['errors' => ['email' => 'Please enter a valid email address.']]);
}

try {
    $userRepo = new UserRepository($pdo);
    $user = $userRepo->findUserByEmail($email);

    // To prevent user enumeration, we always return a success message.
    // The email is only sent if the user actually exists.
    if ($user) {
        $resetRepo = new PasswordResetRepository($pdo);

        // Create a secure token
        $token = bin2hex(random_bytes(32));
        $token_hash = hash('sha256', $token);
        $expires = time() + 3600; // Token valid for 1 hour

        $resetRepo->createResetToken($email, $token_hash, $expires);

        // Send the reset email
        $reset_link = APP_URL . "/reset-password.php?token=" . $token;

        $emailSender = new EmailSender();
        $subject = "Password Reset Request for " . APP_NAME;
        $body = "<h1>Password Reset</h1>
                 <p>You requested a password reset. Click the link below to set a new password:</p>
                 <p><a href='" . $reset_link . "'>" . $reset_link . "</a></p>
                 <p>This link will expire in 1 hour.</p>
                 <p>If you did not request a password reset, please ignore this email.</p>";

        try {
            $emailSender->send($email, $subject, $body);
        } catch (Exception $e) {
            error_log("Failed to send password reset email: " . $e->getMessage());
            // Do not expose this error to the user
        }
    }

    // Always show a generic success message
    json_response(true, ['message' => 'If an account with that email exists, a password reset link has been sent.']);

} catch (Exception $e) {
    error_log("Forgot Password Error: " . $e->getMessage());
    // Generic error for the user
    json_response(false, ['error' => 'An internal server error occurred.']);
}
