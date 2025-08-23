<?php
session_start();

// Security checks
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION["user_id"]) || !isset($_SESSION['order_details'])) {
    header("Location: index.php");
    exit;
}

require_once 'config/database.php';
require_once 'classes/Order.php';
require_once 'repositories/OrderRepository.php';

$order_details = $_SESSION['order_details'];

// Double-check that the session data matches the logged-in user
if ($order_details['user_id'] !== $_SESSION['user_id']) {
    // Mismatch, potential session tampering
    unset($_SESSION['order_details']);
    $_SESSION['error_message'] = "An error occurred. Please try again.";
    header("Location: index.php");
    exit;
}

try {
    $orderRepo = new OrderRepository($pdo);

    $order = new Order();
    $order->user_id = $order_details['user_id'];
    $order->event_id = $order_details['event_id'];
    $order->total_amount = $order_details['total_amount'];
    $order->status = 'pending'; // Initial status

    $orderId = $orderRepo->createOrderWithItems($order, $order_details['items']);

    if ($orderId) {
        // Success, clear the session data and redirect to confirmation
        unset($_SESSION['order_details']);
        header("Location: order-confirmation.php?order_id=" . $orderId);
        exit;
    } else {
        throw new Exception("Failed to create the order in the database.");
    }

} catch (Exception $e) {
    $_SESSION['error_message'] = "Could not place your order. " . $e->getMessage();
    // Redirect back to the event page, as checkout might not be possible to reconstruct
    header("Location: event.php?id=" . $order_details['event_id']);
    exit;
}
