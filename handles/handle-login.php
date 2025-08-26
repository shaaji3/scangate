<?php
require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../utils/CSRF.php';
require_once __DIR__ . '/../utils/RateLimiter.php';
require_once __DIR__ . '/../repositories/UserRepository.php';

// Set header to return JSON
header('Content-Type: application/json');


// --- Response helper ---
function json_response($success, $data) {
    echo json_encode(['success' => $success] + $data);
    exit;
}



$rateLimiter = new RateLimiter($pdo);
$allowed_rate = $rateLimiter->checkWithGlobal("login_attempt", "global");

if ($allowed_rate < 1) {
    json_response(false, ['error' => 'Rate limit exceeded. Please try again later.']);
}

// --- CSRF Validation ---
if (!CSRF::validateToken($_POST['csrf_token'] ?? '', 'login_form')) {
    json_response(false, ['error' => 'Invalid request. Please try again.']);
}

// --- Request Method Check ---
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(false, ['error' => 'Invalid request method.']);
}

// --- Get and Validate Form Data ---
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if (empty($email) || empty($password)) {
    json_response(false, ['error' => 'Email and password are required.']);
}

// --- Authenticate User ---
try {
    $userRepo = new UserRepository($pdo);
    $user = $userRepo->findUserByEmail($email);

    if ($user && $user->verifyPassword($password)) {
        // Authentication successful
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user->id;
        $_SESSION['user_role'] = $user->role;
        $_SESSION['user_name'] = $user->name;

        // Check for trusted device cookie
        if (isset($_COOKIE['trusted_device']) && $_COOKIE['trusted_device'] === 'yes') {
            json_response(true, ['redirect_url' => 'dashboard.php']);
        } else {
            // Device not trusted, proceed to OTP verification
            $_SESSION['otp_user_id'] = $user->id; // Store user ID for OTP check

            // Generate and store OTP
            $otp_code = random_int(100000, 999999);
            $_SESSION['otp_code'] = $otp_code;
            $_SESSION['otp_expiry'] = time() + 300; // OTP valid for 5 minutes

            // In a real application, you would send the OTP via email here.
            // For simulation, we can log it or just rely on the session.
            error_log("OTP for user " . $user->id . ": " . $otp_code);

            json_response(true, ['redirect_url' => 'otp.php']);
        }
    } else {
        // Authentication failed
        json_response(false, ['error' => 'Invalid email or password.']);
    }
} catch (Exception $e) {
    // Log the exception $e->getMessage()
    json_response(false, ['error' => 'An internal server error occurred.']);
}
