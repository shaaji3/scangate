<?php
require_once __DIR__ . '/bootstrap.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require_once __DIR__ . '/repositories/PaymentRepository.php';
require_once __DIR__ . '/repositories/OrderRepository.php';
require_once __DIR__ . '/utils/URLUtils.php';

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

if (!$order || $order->user_id !== $_SESSION['user_id']) {
    die("Permission denied.");
}

$page_title = "Confirm Your Order";
require_once __DIR__ . '/includes/header-public.php';
?>

<div class="container my-5">
    <div class="row d-flex justify-content-center">
        <div class="col-lg-8 text-center">
            <div class="card">
                <div class="card-body">
                    <i class="fa fa-check-circle text-success" style="font-size: 80px; margin-bottom: 20px;"></i>
                    <h1 class="card-title">Final Step: Complete Your Payment</h1>
                    <p class="lead">Your order has been created successfully. Please proceed to payment to finalize your purchase and receive your tickets.</p>

                    <?php if (isset($_SESSION['error_message'])): ?>
                        <div class="alert alert-danger"><?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?></div>
                    <?php endif; ?>

                    <div class="alert alert-info">
                        <p class="mb-0"><strong>Order Reference:</strong> <?php echo htmlspecialchars($payment->reference); ?></p>
                        <p class="mb-0"><strong>Total Amount:</strong> $<?php echo htmlspecialchars(number_format($order->total_amount, 2)); ?></p>
                    </div>

                    <p class="mt-4">You will be redirected to our secure payment partner, Paystack, to complete your payment.</p>

                    <a href="initiate-payment.php?ref=<?php echo urlencode($payment->reference); ?>" class="btn btn-primary btn-lg mt-3">
                        Pay $<?php echo htmlspecialchars(number_format($order->total_amount, 2)); ?> Now
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/includes/footer-public.php';
?>
