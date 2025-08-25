<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/repositories/PaymentRepository.php';
require_once __DIR__ . '/repositories/OrderRepository.php';

$reference = filter_input(INPUT_GET, 'ref', FILTER_SANITIZE_STRING);
if (!$reference) {
    header("Location: dashboard.php");
    exit;
}

$paymentRepo = new PaymentRepository($pdo);
$payment = $paymentRepo->findByReference($reference);

if (!$payment) {
    die("Invalid payment reference.");
}

$orderRepo = new OrderRepository($pdo);
$order = $orderRepo->findOrderById($payment->order_id);

// Security Check: Ensure the order belongs to the logged-in user
if (!$order || $order->user_id !== $_SESSION['user_id']) {
    die("Permission denied.");
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Confirm Your Order</title>
    <style>
        body { font-family: sans-serif; text-align: center; padding-top: 50px; }
        .container { max-width: 600px; margin: auto; border: 1px solid #ddd; padding: 20px; border-radius: 5px; }
        .btn-pay { background-color: #007bff; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; font-size: 1.2em; display: inline-block; margin-top: 20px; }
        .message { padding: 10px; margin-bottom: 15px; border-radius: 5px; background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Final Step: Complete Your Payment</h1>
        <p>Your order has been created. Please proceed to payment to finalize your purchase.</p>

        <?php
        if (isset($_SESSION['error_message'])) {
            echo '<div class="message">' . $_SESSION['error_message'] . '</div>';
            unset($_SESSION['error_message']);
        }
        ?>

        <p><strong>Order Reference:</strong> <?php echo htmlspecialchars($payment->reference); ?></p>
        <p><strong>Total Amount:</strong> $<?php echo htmlspecialchars(number_format($order->total_amount, 2)); ?></p>
        <p>You will be redirected to our secure payment partner, Paystack.</p>
        <a href="initiate-payment.php?ref=<?php echo htmlspecialchars($payment->reference); ?>" class="btn-pay">Pay $<?php echo htmlspecialchars(number_format($order->total_amount, 2)); ?> Now</a>
    </div>
</body>
</html>
