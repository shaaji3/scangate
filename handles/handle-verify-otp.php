<?php
require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../utils/CSRF.php';

// Set header to return JSON
header('Content-Type: application/json');

// --- Response helper ---
function json_response($success, $data) {
    echo json_encode(['success' => $success] + $data);
    exit;
}

// --- Session and CSRF Validation ---
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!CSRF::validateToken($_POST['csrf_token'] ?? '', 'otp_form')) {
    json_response(false, ['error' => 'Invalid request. Please try again.']);
}

// --- Authorization Check ---
if (!isset($_SESSION['otp_user_id'])) {
    json_response(false, ['error' => 'No verification in progress.', 'redirect_url' => 'login.php']);
}

// --- OTP Verification Logic ---
$submitted_otp = trim($_POST['otp_code'] ?? '');

if (empty($submitted_otp)) {
    json_response(false, ['error' => 'Please enter the verification code.']);
}

if (!isset($_SESSION['otp_code']) || !isset($_SESSION['otp_expiry'])) {
    json_response(false, ['error' => 'Verification process is invalid. Please try logging in again.', 'redirect_url' => 'login.php']);
}

if (time() > $_SESSION['otp_expiry']) {
    // Clear expired OTP data
    unset($_SESSION['otp_user_id'], $_SESSION['otp_code'], $_SESSION['otp_expiry']);
    json_response(false, ['error' => 'Verification code has expired. Please log in again to get a new code.', 'redirect_url' => 'login.php']);
}

if ((int)$submitted_otp === $_SESSION['otp_code']) {
    // OTP is correct
    // Set a cookie to trust this device for 30 days
    setcookie('trusted_device', 'yes', time() + (86400 * 30), "/"); // 86400 = 1 day

    // Clear OTP data from session
    unset($_SESSION['otp_user_id'], $_SESSION['otp_code'], $_SESSION['otp_expiry']);

    json_response(true, ['redirect_url' => 'dashboard.php']);
} else {
    // OTP is incorrect
    json_response(false, ['error' => 'The verification code is incorrect.']);
}
