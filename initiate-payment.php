<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Assume composer autoload is available
if (file_exists('vendor/autoload.php')) {
    require_once 'vendor/autoload.php';
} else {
    // Fallback or error if the vendor directory doesn't exist
    die("Composer dependencies not installed. Please run 'composer install'.");
}
require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/repositories/PaymentRepository.php';
require_once __DIR__ . '/repositories/OrderRepository.php';
require_once __DIR__ . '/repositories/UserRepository.php';

$reference = filter_input(INPUT_GET, 'ref', FILTER_SANITIZE_STRING);
if (!$reference) {
    die("No payment reference provided.");
}

// Fetch payment, order, and user details
$paymentRepo = new PaymentRepository($pdo);
$payment = $paymentRepo->findByReference($reference);

if (!$payment) {
    die("Invalid payment reference.");
}

$orderRepo = new OrderRepository($pdo);
$order = $orderRepo->findOrderById($payment->order_id);

$userRepo = new UserRepository($pdo);
$user = $userRepo->findUserById($order->user_id);

if (!$order || !$user) {
    die("Could not retrieve order details.");
}

// Security Check: Ensure the order belongs to the logged-in user
if ($order->user_id !== $_SESSION['user_id']) {
    die("Permission denied.");
}

try {
    $paystack = new \Yabacon\Paystack(PAYSTACK_SECRET_KEY);

    $tranx = $paystack->transaction->initialize([
        'amount' => $order->total_amount * 100, // Amount in kobo
        'email' => $user->email,
        'reference' => $reference,
        'callback_url' => APP_URL . '/verify-payment.php',
        'metadata' => [
            'order_id' => $order->id,
            'user_id' => $user->id
        ]
    ]);

    if (!$tranx->status) {
        // API call failed
        throw new Exception("Paystack API Error: " . $tranx->message);
    }

    // Redirect to Paystack's payment page
    header('Location: ' . $tranx->data->authorization_url);
    exit;

} catch (\Exception $e) {
    // API connection error or other exception
    $_SESSION['error_message'] = "Payment gateway error. Please try again later.";
    error_log("Paystack Initiation Error: " . $e->getMessage());
    header("Location: order-confirmation.php?ref=" . $reference);
    exit;
}
