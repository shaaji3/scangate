<?php
session_start();

// Security checks
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION["user_id"]) || $_SESSION['user_role'] !== 'planner') {
    // Redirect to a safe place if the checks fail
    header("Location: login.php");
    exit;
}

require_once 'utils/CSRF.php';
if (!CSRF::validateToken($_POST['csrf_token'] ?? '')) {
    die("CSRF token validation failed.");
}

require_once 'config/database.php';
require_once 'repositories/EventRepository.php';
require_once 'repositories/TicketRepository.php';

// 1. Get and validate form data
$event_id = filter_input(INPUT_POST, 'event_id', FILTER_VALIDATE_INT);
$name = trim($_POST['name']);
$price = filter_input(INPUT_POST, 'price', FILTER_VALIDATE_FLOAT);
$quantity = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT);

$errors = [];
if (!$event_id) $errors[] = "Invalid event ID.";
if (empty($name)) $errors[] = "Ticket name is required.";
if ($price === false || $price < 0) $errors[] = "Invalid price.";
if ($quantity === false || $quantity < 1) $errors[] = "Invalid quantity.";

// Redirect back with errors if any
if (!empty($errors)) {
    $_SESSION['error_message'] = implode('<br>', $errors);
    header("Location: edit-event.php?id=" . $event_id);
    exit;
}

// 2. Security Check: Verify event ownership
$eventRepo = new EventRepository($pdo);
$event = $eventRepo->findEventById($event_id);

if (!$event || $event->planner_id !== $_SESSION['user_id']) {
    $_SESSION['error_message'] = "You do not have permission to add tickets to this event.";
    header("Location: dashboard.php");
    exit;
}

// 3. Create and save the new ticket
try {
    $ticketRepo = new TicketRepository($pdo);
    $ticket = new Ticket(); // Assuming Ticket class has public properties
    $ticket->event_id = $event_id;
    $ticket->name = $name;
    $ticket->price = $price;
    $ticket->quantity = $quantity;

    if ($ticketRepo->createTicket($ticket)) {
        $_SESSION['success_message'] = "New ticket type added successfully!";
    } else {
        throw new Exception("Failed to add ticket to the database.");
    }
} catch (Exception $e) {
    $_SESSION['error_message'] = "An error occurred: " . $e->getMessage();
}

// 4. Redirect back to the edit event page
header("Location: edit-event.php?id=" . $event_id);
exit;
