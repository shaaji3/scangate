<?php
require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../utils/CSRF.php';
require_once __DIR__ . '/../repositories/EventRepository.php';
require_once __DIR__ . '/../repositories/TicketRepository.php';
require_once __DIR__ . '/../utils/AuthGuard.php';

header('Content-Type: application/json');

function json_response($success, $data) {
    echo json_encode(['success' => $success] + $data);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(false, ['error' => 'Invalid request method.']);
}

session_start();

// --- Auth and CSRF Validation ---
if (!isset($_SESSION["user_id"]) || $_SESSION['user_role'] !== 'planner') {
    json_response(false, ['error' => 'You are not authorized to perform this action.']);
}

if (!CSRF::validateToken($_POST['csrf_token'] ?? '', 'add_ticket_form')) {
    json_response(false, ['error' => 'Invalid request. Please try again.']);
}

// --- Form Data Validation ---
$event_id = filter_input(INPUT_POST, 'event_id', FILTER_VALIDATE_INT);
$name = trim($_POST['name'] ?? '');
$price = filter_input(INPUT_POST, 'price', FILTER_VALIDATE_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
$quantity = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT);

$errors = [];
if (!$event_id) {
    json_response(false, ['error' => 'Invalid event specified.']);
}
if (empty($name)) $errors['name'] = "Ticket name is required.";
if ($price === false || $price < 0) $errors['price'] = "A valid, non-negative price is required.";
if ($quantity === false || $quantity < 1) $errors['quantity'] = "Quantity must be at least 1.";

if (!empty($errors)) {
    json_response(false, ['errors' => $errors]);
}

// --- Security Check: Verify event ownership ---
if (!AuthGuard::canEditEvent($pdo, $_SESSION['user_id'], $event_id)) {
    json_response(false, ['error' => 'You do not have permission to add tickets to this event.']);
}

// --- Database Interaction ---
try {
    $ticketRepo = new TicketRepository($pdo);

    $ticket = new Ticket();
    $ticket->event_id = $event_id;
    $ticket->name = $name;
    $ticket->price = $price;
    $ticket->quantity = $quantity;

    if ($ticketRepo->createTicket($ticket)) {
        json_response(true, ['message' => 'New ticket type added successfully! The page will now reload.']);
    } else {
        json_response(false, ['error' => 'Database error: Failed to add ticket.']);
    }
} catch (Exception $e) {
    error_log("Add Ticket Error: " . $e->getMessage());
    json_response(false, ['error' => 'An internal server error occurred.']);
}
