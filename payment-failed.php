<?php
require_once __DIR__ . '/bootstrap.php';

$reference = filter_input(INPUT_GET, 'ref', FILTER_SANITIZE_STRING);
$error_message = $_SESSION['error_message'] ?? "An unknown error occurred, or the payment was cancelled.";
unset($_SESSION['error_message']); // Clear the message after displaying it

$page_title = "Payment Failed";
require_once __DIR__ . '/includes/header-public.php';
?>

<div class="container my-5">
    <div class="row d-flex justify-content-center">
        <div class="col-lg-8 text-center">
            <div class="card">
                <div class="card-body">
                    <i class="fa fa-times-circle text-danger" style="font-size: 80px; margin-bottom: 20px;"></i>
                    <h1 class="card-title">Payment Failed</h1>
                    <p class="lead">Unfortunately, we were unable to process your payment.</p>

                    <div class="alert alert-warning">
                        <strong>Reason:</strong> <?php echo htmlspecialchars($error_message); ?>
                    </div>

                    <?php if ($reference): ?>
                        <p>You can try the payment again for your order.</p>
                        <a href="order-confirmation.php?ref=<?php echo urlencode($reference); ?>" class="btn btn-primary btn-lg mt-3">Try Again</a>
                    <?php endif; ?>

                    <p class="mt-4"><a href="index.php">Back to Homepage</a></p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/includes/footer-public.php';
?>
