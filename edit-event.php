<?php
require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/repositories/EventRepository.php';
require_once __DIR__ . '/repositories/TicketRepository.php';
require_once __DIR__ . '/utils/AuthGuard.php';
require_once __DIR__ . '/utils/CSRF.php';
require_once __DIR__ . '/utils/URLUtils.php';

$page_title = "Edit Event";

// Decrypt and validate event ID from URL
$encrypted_event_id = $_GET['id'] ?? '';
$event_id = URLUtils::decrypt($encrypted_event_id);

if (!$event_id) {
    header("Location: dashboard.php");
    exit;
}

// AuthGuard is in header-auth.php, but we need to run the specific check here.
if (!AuthGuard::canEditEvent($pdo, $_SESSION['user_id'], $event_id)) {
    $_SESSION['error_message'] = "You do not have permission to edit this event.";
    header("Location: dashboard.php");
    exit;
}

// Fetch event details
$eventRepo = new EventRepository($pdo);
$event = $eventRepo->findEventById($event_id);

if (!$event) {
    // Event not found
    header("Location: dashboard.php");
    exit;
}

// Fetch tickets for this event
$ticketRepo = new TicketRepository($pdo);
$tickets = $ticketRepo->findTicketsByEventId($event_id);

require_once __DIR__ . '/includes/header-auth.php';
require_once __DIR__ . '/includes/sidebar.php';
?>

<div class="row">
    <!-- Event Details Column -->
    <div class="col-xl-7">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">Manage Event: <?php echo htmlspecialchars($event->title); ?></h4>
            </div>
            <div class="card-body">
                <!-- In a future step, we can make this form editable -->
                <p><strong>Status:</strong> <span class="badge light badge-primary"><?php echo htmlspecialchars($event->status); ?></span></p>
                <p><strong>Date:</strong> <?php echo date('F j, Y, g:i a', strtotime($event->date)); ?></p>
                <p><strong>Location:</strong> <?php echo htmlspecialchars($event->location); ?></p>
                <p><strong>Description:</strong> <?php echo nl2br(htmlspecialchars($event->description)); ?></p>
                <?php if ($event->banner): ?>
                    <p><strong>Banner:</strong></p>
                    <img src="<?php echo htmlspecialchars($event->banner); ?>" alt="Event Banner" class="img-fluid rounded">
                <?php endif; ?>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h4 class="card-title">Existing Ticket Types</h4>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <?php if (!empty($tickets)): ?>
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Ticket Name</th>
                                    <th>Price</th>
                                    <th>Quantity</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($tickets as $ticket): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($ticket->name); ?></td>
                                        <td>$<?php echo htmlspecialchars(number_format($ticket->price, 2)); ?></td>
                                        <td><?php echo htmlspecialchars($ticket->quantity); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p>No ticket types have been added for this event yet.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Ticket Column -->
    <div class="col-xl-5">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">Add New Ticket Type</h4>
            </div>
            <div class="card-body">
                <div id="add-ticket-error" class="alert alert-danger" style="display: none;"></div>
                <div id="add-ticket-success" class="alert alert-success" style="display: none;"></div>

                <form id="add-ticket-form" method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo CSRF::generateToken('add_ticket_form'); ?>">
                    <input type="hidden" name="event_id" value="<?php echo $event->id; ?>">

                    <div class="mb-3">
                        <label class="form-label">Ticket Name (e.g., General Admission, VIP)</label>
                        <input type="text" class="form-control" name="name" required>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Price ($)</label>
                        <input type="number" class="form-control" name="price" min="0" step="0.01" required>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Quantity Available</label>
                        <input type="number" class="form-control" name="quantity" min="1" required>
                        <div class="invalid-feedback"></div>
                    </div>
                    <button type="submit" id="add-ticket-button" class="btn btn-primary">
                        <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true" style="display: none;"></span>
                        Add Ticket
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="assets/js/event.js"></script>

<?php
require_once __DIR__ . '/includes/footer-auth.php';
?>
