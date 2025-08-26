<?php
require_once __DIR__ . '/bootstrap.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$reference = filter_input(INPUT_GET, 'ref', FILTER_SANITIZE_STRING);

$page_title = "Payment Successful";
require_once __DIR__ . '/includes/header-public.php';
?>

<div class="container my-5">
    <div class="row d-flex justify-content-center">
        <div class="col-lg-8 text-center">
            <div class="card">
                <div class="card-body">
                    <i class="fa fa-check-circle text-success" style="font-size: 80px; margin-bottom: 20px;"></i>
                    <h1 class="card-title">Payment Successful!</h1>
                    <p class="lead">Thank you! Your payment has been successfully processed.</p>

                    <?php if ($reference): ?>
                        <p>Your payment reference is: <strong><?php echo htmlspecialchars($reference); ?></strong></p>
                    <?php endif; ?>

                    <p>Your tickets are now available in your dashboard. A confirmation email with your tickets attached has been sent to you.</p>
                    <a href="dashboard.php" class="btn btn-primary btn-lg mt-3">Go to Dashboard</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/includes/footer-public.php';
?>
