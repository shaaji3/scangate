<?php
session_start();

$reference = filter_input(INPUT_GET, 'ref', FILTER_SANITIZE_STRING);
$error_message = $_SESSION['error_message'] ?? "An unknown error occurred.";
unset($_SESSION['error_message']); // Clear the message after displaying it

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Payment Failed</title>
    <style>
        body { font-family: sans-serif; text-align: center; padding-top: 50px; }
        .container { max-width: 600px; margin: auto; }
        .error-icon { color: red; font-size: 5em; }
    </style>
</head>
<body>
    <div class="container">
        <div class="error-icon">&#10060;</div>
        <h1>Payment Failed</h1>
        <p>Unfortunately, we were unable to process your payment.</p>
        <p><strong>Reason:</strong> <?php echo htmlspecialchars($error_message); ?></p>
        <?php if ($reference): ?>
            <p>You can try the payment again for reference: <strong><?php echo htmlspecialchars($reference); ?></strong></p>
            <p><a href="order-confirmation.php?ref=<?php echo htmlspecialchars($reference); ?>">Try Again</a></p>
        <?php endif; ?>
        <p><a href="index.php">Back to Homepage</a></p>
    </div>
</body>
</html>
