<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$reference = filter_input(INPUT_GET, 'ref', FILTER_SANITIZE_STRING);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Payment Successful</title>
    <style>
        body { font-family: sans-serif; text-align: center; padding-top: 50px; }
        .container { max-width: 600px; margin: auto; }
        .success-icon { color: green; font-size: 5em; }
    </style>
</head>
<body>
    <div class="container">
        <div class="success-icon">&#10004;</div>
        <h1>Payment Successful!</h1>
        <p>Thank you! Your payment has been successfully processed.</p>
        <?php if ($reference): ?>
            <p>Your payment reference is: <strong><?php echo htmlspecialchars($reference); ?></strong></p>
        <?php endif; ?>
        <p>Your tickets are now available in your dashboard. A confirmation email has been sent to you.</p>
        <p><a href="dashboard.php">Go to Dashboard</a></p>
    </div>
</body>
</html>
