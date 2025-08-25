<?php
session_start();
header('Content-Type: application/json');

// Helper function for sending JSON responses
function send_json_response($status, $message, $data = []) {
    echo json_encode(['status' => $status, 'message' => $message, 'data' => $data]);
    exit;
}

// Security: Must be a POST request and user must be a logged-in planner
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'planner') {
    send_json_response('error', 'Unauthorized');
}

// Get the request body
$input = json_decode(file_get_contents('php://input'), true);
$ticket_code = $input['ticket_code'] ?? null;
$event_id_from_scanner = $input['event_id'] ?? null;

if (!$ticket_code || !$event_id_from_scanner) {
    send_json_response('error', 'Invalid input.');
}

// --- Database Operations ---
require_once '../config/database.php';
require_once '../repositories/IssuedTicketRepository.php';

try {
    $repo = new IssuedTicketRepository($pdo);
    $ticket = $repo->findByCode($ticket_code);

    // 1. Check if ticket exists
    if (!$ticket) {
        send_json_response('error', 'Invalid Ticket Code.');
    }

    // 2. Security Check: Does the ticket belong to the event being scanned?
    if ($ticket['event_id'] != $event_id_from_scanner) {
        send_json_response('error', 'Ticket is not for this event.');
    }

    // 3. Security Check: Does the event belong to the logged-in planner?
    if ($ticket['planner_id'] != $_SESSION['user_id']) {
        send_json_response('error', 'You do not have permission to scan tickets for this event.');
    }

    // 4. Check the ticket status and take action
    switch ($ticket['status']) {
        case 'valid':
            // Mark as used and return success
            $repo->updateStatus($ticket_code, 'used');
            send_json_response('success', 'Check-in Successful!');
            break;
        case 'used':
            send_json_response('warning', 'This ticket has already been used.');
            break;
        case 'cancelled':
            send_json_response('error', 'This ticket has been cancelled.');
            break;
        default:
            send_json_response('error', 'Unknown ticket status.');
    }

} catch (Exception $e) {
    // Log the real error
    error_log('API Error: ' . $e->getMessage());
    send_json_response('error', 'A server error occurred. Please try again.');
}
