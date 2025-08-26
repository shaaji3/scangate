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

<!--**********************************
    Content body start
***********************************-->
<div class="content-body">
    <!-- row -->
    <div class="container-fluid">
        <div class="page-titles">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                <li class="breadcrumb-item active"><a href="javascript:void(0)"><?php echo htmlspecialchars($event->title); ?></a></li>
            </ol>
        </div>
        <div class="row">
            <div class="col-xl-9 col-xxl-8">
                <div class="row">
                    <div class="col-xl-12">
                        <div class="card event-bx overflow-hidden">
                            <div class="card-media">
                                <img src="<?php echo htmlspecialchars($event->banner); ?>" alt="">
                            </div>
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col-xl-8 col-xxl-8">
                                        <div class="border-xl-end pe-xl-4 pe-lg-2">
                                            <h4 class="text-black">Event Description</h4>
                                            <p><?php echo nl2br(htmlspecialchars($event->description)); ?></p>
                                        </div>
                                    </div>
                                    <div class="col-xl-4 col-xxl-4">
                                        <div class="media mb-3 align-items-center">
                                            <div class="me-3">
                                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <g clip-path="">
                                                    <path d="M21 3H20C20 2.20435 19.6839 1.44129 19.1213 0.87868C18.5587 0.31607 17.7956 0 17 0C16.2044 0 15.4413 0.31607 14.8787 0.87868C14.3161 1.44129 14 2.20435 14 3H10C10 2.20435 9.68393 1.44129 9.12132 0.87868C8.55871 0.316071 7.79565 4.47035e-08 7 4.47035e-08C6.20435 4.47035e-08 5.44129 0.316071 4.87868 0.87868C4.31607 1.44129 4 2.20435 4 3H3C2.20435 3 1.44129 3.31607 0.87868 3.87868C0.31607 4.44129 0 5.20435 0 6L0 21C0 21.7956 0.31607 22.5587 0.87868 23.1213C1.44129 23.6839 2.20435 24 3 24H21C21.7956 24 22.5587 23.6839 23.1213 23.1213C23.6839 22.5587 24 21.7956 24 21V6C24 5.20435 23.6839 4.44129 23.1213 3.87868C22.5587 3.31607 21.7956 3 21 3ZM3 5H4C4 5.79565 4.31607 6.55871 4.87868 7.12132C5.44129 7.68393 6.20435 8 7 8C7.26522 8 7.51957 7.89464 7.70711 7.70711C7.89464 7.51957 8 7.26522 8 7C8 6.73478 7.89464 6.48043 7.70711 6.29289C7.51957 6.10536 7.26522 6 7 6C6.73478 6 6.48043 5.89464 6.29289 5.70711C6.10536 5.51957 6 5.26522 6 5V3C6 2.73478 6.10536 2.48043 6.29289 2.29289C6.48043 2.10536 6.73478 2 7 2C7.26522 2 7.51957 2.10536 7.70711 2.29289C7.89464 2.48043 8 2.73478 8 3V4C8 4.26522 8.10536 4.51957 8.29289 4.70711C8.48043 4.89464 8.73478 5 9 5H14C14 5.79565 14.3161 6.55871 14.8787 7.12132C15.4413 7.68393 16.2044 8 17 8C17.2652 8 17.5196 7.89464 17.7071 7.70711C17.8946 7.51957 18 7.26522 18 7C18 6.73478 17.8946 6.48043 17.7071 6.29289C17.5196 6.10536 17.2652 6 17 6C16.7348 6 16.4804 5.89464 16.2929 5.70711C16.1054 5.51957 16 5.26522 16 5V3C16 2.73478 16.1054 2.48043 16.2929 2.29289C16.4804 2.10536 16.7348 2 17 2C17.2652 2 17.5196 2.10536 17.7071 2.29289C17.8946 2.48043 18 2.73478 18 3V4C18 4.26522 18.1054 4.51957 18.2929 4.70711C18.4804 4.89464 18.7348 5 19 5H21C21.2652 5 21.5196 5.10536 21.7071 5.29289C21.8946 5.48043 22 5.73478 22 6V10H2V6C2 5.73478 2.10536 5.48043 2.29289 5.29289C2.48043 5.10536 2.73478 5 3 5ZM21 22H3C2.73478 22 2.48043 21.8946 2.29289 21.7071C2.10536 21.5196 2 21.2652 2 21V12H22V21C22 21.2652 21.8946 21.5196 21.7071 21.7071C21.5196 21.8946 21.2652 22 21 22Z" fill="var(--primary)"></path>
                                                    <path d="M12 16C12.5523 16 13 15.5523 13 15C13 14.4477 12.5523 14 12 14C11.4477 14 11 14.4477 11 15C11 15.5523 11.4477 16 12 16Z" fill="var(--primary)"></path>
                                                    <path d="M18 16C18.5523 16 19 15.5523 19 15C19 14.4477 18.5523 14 18 14C17.4477 14 17 14.4477 17 15C17 15.5523 17.4477 16 18 16Z" fill="var(--primary)"></path>
                                                    <path d="M6 16C6.55228 16 7 15.5523 7 15C7 14.4477 6.55228 14 6 14C5.44771 14 5 14.4477 5 15C5 15.5523 5.44771 16 6 16Z" fill="var(--primary)"></path>
                                                    <path d="M12 20C12.5523 20 13 19.5523 13 19C13 18.4477 12.5523 18 12 18C11.4477 18 11 18.4477 11 19C11 19.5523 11.4477 20 12 20Z" fill="var(--primary)"></path>
                                                    <path d="M18 20C18.5523 20 19 19.5523 19 19C19 18.4477 18.5523 18 18 18C17.4477 18 17 18.4477 17 19C17 19.5523 17.4477 20 18 20Z" fill="var(--primary)"></path>
                                                    <path d="M6 20C6.55228 20 7 19.5523 7 19C7 18.4477 6.55228 18 6 18C5.44771 18 5 18.4477 5 19C5 19.5523 5.44771 20 6 20Z" fill="var(--primary)"></path>
                                                    </g>
                                                </svg>
                                            </div>
                                            <div class="media-body">
                                                <p class="mb-0">Date</p>
                                                <h5 class="mb-0 text-black"><?php echo date('F j, Y, g:i a', strtotime($event->date)); ?></h5>
                                            </div>
                                        </div>
                                        <div class="media mb-3 align-items-center">
                                            <div class="me-3">
                                                <svg width="24" height="24" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <g clip-path="">
                                                    <path d="M27.5711 13.4286C27.5711 22.4286 15.9997 30.1428 15.9997 30.1428C15.9997 30.1428 4.42822 22.4286 4.42822 13.4286C4.42822 10.3596 5.64735 7.41638 7.81742 5.24632C9.98748 3.07625 12.9307 1.85712 15.9997 1.85712C19.0686 1.85712 22.0118 3.07625 24.1819 5.24632C26.3519 7.41638 27.5711 10.3596 27.5711 13.4286Z" stroke="var(--primary)" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"></path>
                                                    <path d="M15.9997 17.2857C18.13 17.2857 19.8569 15.5588 19.8569 13.4286C19.8569 11.2983 18.13 9.57141 15.9997 9.57141C13.8695 9.57141 12.1426 11.2983 12.1426 13.4286C12.1426 15.5588 13.8695 17.2857 15.9997 17.2857Z" stroke="var(--primary)" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"></path>
                                                    </g>
                                                </svg>
                                            </div>
                                            <div class="media-body">
                                                <p class="mb-0">Location</p>
                                                <h5 class="mb-0 text-black"><?php echo htmlspecialchars($event->location); ?></h5>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-xxl-4">
						<div class="row">
							<div class="col-md-12">
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
                            <div class="col-md-12">
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
						</div>
					</div>
        </div>
    </div>
</div>
<!--**********************************
    Content body end
***********************************-->

<script src="assets/js/event.js"></script>

<?php
require_once __DIR__ . '/includes/footer-auth.php';
?>
