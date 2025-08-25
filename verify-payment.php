<?php
session_start();

// Assume composer autoload is available
if (file_exists('vendor/autoload.php')) {
    require_once 'vendor/autoload.php';
} else {
    die("Composer dependencies not installed.");
}
require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ .'/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/repositories/PaymentRepository.php';

$reference = filter_input(INPUT_GET, 'reference', FILTER_SANITIZE_STRING);
if (!$reference) {
    die("No payment reference provided.");
}

try {
    $paystack = new \Yabacon\Paystack(PAYSTACK_SECRET_KEY);
    $tranx = $paystack->transaction->verify(['reference' => $reference]);

    if ('success' === $tranx->data->status) {
        // Payment was successful
        $paymentRepo = new PaymentRepository($pdo);

        // Use a dedicated method to handle DB updates transactionally
        $processed = $paymentRepo->processSuccessfulPayment($reference);

        if ($processed) {
            // Redirect to a success page
            header("Location: payment-success.php?ref=" . $reference);
            exit;
        } else {
            // The DB update failed, this is a critical error to log
            throw new Exception("Payment was successful but we could not update your order. Please contact support. Reference: " . $reference);
        }
    } else {
        // Payment was not successful
        throw new Exception("The payment was not successful. Status: " . $tranx->data->status);
    }

} catch (\Exception $e) {
    // API error or other exception
    $_SESSION['error_message'] = "There was an error verifying your payment: " . $e->getMessage();
    header("Location: payment-failed.php?ref=" . $reference);
    exit;
}
