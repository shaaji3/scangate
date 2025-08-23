<?php
session_start();

// User must be logged in to checkout
if (!isset($_SESSION["user_id"])) {
    $_SESSION['error_message'] = "You must be logged in to purchase tickets.";
    // Save the intended purchase to the session to resume after login
    $_SESSION['intended_purchase'] = $_POST;
    header("Location: login.php");
    exit;
}

require_once 'config/database.php';
require_once 'repositories/EventRepository.php';
require_once 'repositories/TicketRepository.php';

// Validate POST data
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['event_id']) || !isset($_POST['tickets'])) {
    header("Location: index.php");
    exit;
}

$event_id = filter_input(INPUT_POST, 'event_id', FILTER_VALIDATE_INT);
$selected_tickets = $_POST['tickets'];

// Filter out tickets with zero quantity
$selected_tickets = array_filter($selected_tickets, function($qty) {
    return (int)$qty > 0;
});

if (!$event_id || empty($selected_tickets)) {
    // Redirect back if no tickets were selected
    header("Location: event.php?id=" . $event_id);
    exit;
}

$eventRepo = new EventRepository($pdo);
$ticketRepo = new TicketRepository($pdo);

$event = $eventRepo->findEventById($event_id);
if (!$event) {
    // Handle error: event not found
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

// Store the final order details in the session for the handler
$_SESSION['order_details'] = [
    'user_id' => $_SESSION['user_id'],
    'event_id' => $event_id,
    'items' => $order_items,
    'total_amount' => $total_amount
];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Checkout</title>
    <style>
        body { font-family: sans-serif; }
        .container { padding: 20px; max-width: 700px; margin: auto; }
        .order-summary { border: 1px solid #ddd; padding: 20px; border-radius: 5px; }
        .order-summary h2 { margin-top: 0; }
        .summary-item { display: flex; justify-content: space-between; margin-bottom: 10px; }
        .total { font-weight: bold; font-size: 1.2em; margin-top: 20px; }
        .btn-confirm { background-color: #007bff; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; font-size: 1.2em; display: inline-block; margin-top: 20px; border: none; cursor: pointer; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Order Checkout</h1>
        <div class="order-summary">
            <h2>Order Summary</h2>
            <p><strong>Event:</strong> <?php echo htmlspecialchars($event->title); ?></p>
            <hr>
            <?php foreach ($order_items as $item): ?>
                <div class="summary-item">
                    <span><?php echo htmlspecialchars($item['quantity']); ?> x <?php echo htmlspecialchars($item['name']); ?></span>
                    <span>$<?php echo htmlspecialchars(number_format($item['subtotal'], 2)); ?></span>
                </div>
            <?php endforeach; ?>
            <hr>
            <div class="summary-item total">
                <span>Total Amount</span>
                <span>$<?php echo htmlspecialchars(number_format($total_amount, 2)); ?></span>
            </div>
        </div>
        <form action="handle-create-order.php" method="POST">
            <button type="submit" class="btn-confirm">Confirm and Proceed to Payment</button>
        </form>
    </div>
</body>
</html>
