<?php
require_once __DIR__ . '/bootstrap.php';

// User must be logged in to checkout
if (!isset($_SESSION["user_id"])) {
    $_SESSION['error_message'] = "You must be logged in to purchase tickets.";
    // Save the intended purchase to the session to resume after login
    $_SESSION['intended_purchase'] = $_POST;
    header("Location: login.php");
    exit;
}

require_once __DIR__ . '/repositories/EventRepository.php';
require_once __DIR__ . '/repositories/TicketRepository.php';
require_once __DIR__ . '/utils/URLUtils.php';
require_once __DIR__ . '/utils/CSRF.php';

// Validate POST data
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['event_id']) || !isset($_POST['tickets'])) {
    header("Location: index.php");
    exit;
}

$encrypted_event_id = $_POST['event_id'];
$event_id = URLUtils::decrypt($encrypted_event_id);
$selected_tickets = $_POST['tickets'];

// Filter out tickets with zero quantity
$selected_tickets = array_filter($selected_tickets, function($qty) {
    return is_numeric($qty) && (int)$qty > 0;
});

if (!$event_id || empty($selected_tickets)) {
    header("Location: event.php?id=" . urlencode($encrypted_event_id));
    exit;
}

$eventRepo = new EventRepository($pdo);
$ticketRepo = new TicketRepository($pdo);

$event = $eventRepo->findEventById($event_id);
if (!$event) {
    die("Event not found.");
}

$order_items = [];
$total_amount = 0;

foreach ($selected_tickets as $ticket_id => $quantity) {
    $quantity = (int)$quantity;
    $ticket = $ticketRepo->findTicketById($ticket_id);

    if ($ticket && $quantity > 0) {
        $order_items[] = [
            'ticket_id' => $ticket->id,
            'name' => $ticket->name,
            'quantity' => $quantity,
            'price' => $ticket->price,
            'subtotal' => $ticket->price * $quantity
        ];
        $total_amount += $ticket->price * $quantity;
    }
}

$_SESSION['order_details'] = [
    'user_id' => $_SESSION['user_id'],
    'event_id' => $event_id,
    'items' => $order_items,
    'total_amount' => $total_amount
];

$page_title = "Checkout";
require_once __DIR__ . '/includes/header-public.php';
?>

<div class="container my-5">
    <div class="row d-flex justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Order Checkout</h4>
                </div>
                <div class="card-body">
                    <h5 class="card-title">Order Summary</h5>
                    <p><strong>Event:</strong> <?php echo htmlspecialchars($event->title); ?></p>
                    <hr>
                    <ul class="list-group list-group-flush">
                        <?php foreach ($order_items as $item): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <?php echo htmlspecialchars($item['quantity']); ?> x <?php echo htmlspecialchars($item['name']); ?>
                                <span>$<?php echo htmlspecialchars(number_format($item['subtotal'], 2)); ?></span>
                            </li>
                        <?php endforeach; ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center fw-bold fs-5">
                            Total Amount
                            <span>$<?php echo htmlspecialchars(number_format($total_amount, 2)); ?></span>
                        </li>
                    </ul>
                    <div class="text-center mt-4">
                        <form action="initiate-payment.php" method="POST">
                            <input type="hidden" name="csrf_token" value="<?php echo CSRF::generateToken('initiate_payment'); ?>">
                            <button type="submit" class="btn btn-primary btn-lg">Confirm and Proceed to Payment</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<?php
require_once __DIR__ . '/includes/footer-public.php';
?>
