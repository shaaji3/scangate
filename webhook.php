<?php
// webhook.php - Handles server-to-server notifications from Paystack

// Only respond to POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    exit;
}

require_once 'config/config.php';
require_once 'config/database.php';
require_once 'repositories/PaymentRepository.php';

// 1. Retrieve the request's body and parse it as JSON
$input = @file_get_contents("php://input");
$event = json_decode($input);

// 2. Verify the webhook signature
if (!isset($_SERVER['HTTP_X_PAYSTACK_SIGNATURE']) || ($_SERVER['HTTP_X_PAYSTACK_SIGNATURE'] !== hash_hmac('sha512', $input, PAYSTACK_SECRET_KEY))) {
    // This request isn't from Paystack. Ignore.
    http_response_code(401); // Unauthorized
    exit;
}

// 3. Process the event
http_response_code(200); // Acknowledge receipt of the event immediately

if ('charge.success' === $event->event) {
    $reference = $event->data->reference;

    try {
        $paymentRepo = new PaymentRepository($pdo);
        // The repository method is idempotent, so it's safe to call here.
        $paymentRepo->processSuccessfulPayment($reference);
    } catch (Exception $e) {
        // Log the error for debugging
        error_log("Webhook processing failed: " . $e->getMessage());
        // Even if it fails, we return a 200 to Paystack to prevent retries
        // that will also likely fail. The error must be handled manually by an admin.
    }
}

exit;
