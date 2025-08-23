<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$order_id = filter_input(INPUT_GET, 'order_id', FILTER_VALIDATE_INT);
if (!$order_id) {
    header("Location: dashboard.php");
    exit;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Order Confirmation</title>
    <style>
        body { font-family: sans-serif; text-align: center; padding-top: 50px; }
        .container { max-width: 600px; margin: auto; }
        .success-icon { color: green; font-size: 5em; }
    </style>
</head>
<body>
    <div class="container">
        <div class="success-icon">&#10004;</div>
        <h1>Thank You for Your Order!</h1>
        <p>Your order has been successfully placed with Order ID: <strong><?php echo htmlspecialchars($order_id); ?></strong></p>
        <p>A confirmation email will be sent to you shortly. This is where the payment processing (e.g., Paystack) would begin.</p>
        <p><a href="dashboard.php">Go to Your Dashboard</a></p>
    </div>
</body>
</html>
