<?php
require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/repositories/EventRepository.php';
require_once __DIR__ . '/utils/URLUtils.php';

// AuthGuard is included in header-auth.php
$user_role = $_SESSION['user_role'];
$user_id = $_SESSION['user_id'];

if ($user_role !== 'planner') {
    // Only planners can access this page
    header("Location: dashboard.php");
    exit;
}

$page_title = "My Events";

$eventRepo = new EventRepository($pdo);
$planner_events = $eventRepo->findEventsByPlanner($user_id);

require_once __DIR__ . '/includes/header-auth.php';
require_once __DIR__ . '/includes/sidebar.php';
?>

<!--**********************************
    Content body start
***********************************-->
<div class="content-body">
    <!-- row -->
    <div class="container-fluid">
        <div class="row page-titles">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                <li class="breadcrumb-item active"><a href="javascript:void(0)">My Events</a></li>
            </ol>
        </div>
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">All My Events</h4>
                        <a href="create-event.php" class="btn btn-primary">Create New Event</a>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive ticket-table">
                            <table id="example" class="display dataTablesCard table-responsive-xl" style="min-width: 845px">
                                <thead>
                                    <tr>
                                        <th>Title</th>
                                        <th>Date</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($planner_events as $event): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($event->title); ?></td>
                                            <td><?php echo date('F j, Y, g:i a', strtotime($event->date)); ?></td>
                                            <td><span class="badge light badge-primary"><?php echo htmlspecialchars($event->status); ?></span></td>
                                            <td>
                                                <div class="d-flex">
                                                    <a href="edit-event.php?id=<?php echo URLUtils::encrypt($event->id); ?>" class="btn btn-primary shadow btn-xs sharp me-1"><i class="fas fa-pencil-alt"></i></a>
                                                    <a href="scan-ticket.php?event_id=<?php echo URLUtils::encrypt($event->id); ?>" class="btn btn-success shadow btn-xs sharp"><i class="fas fa-camera"></i></a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
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

<?php
require_once __DIR__ . '/includes/footer-auth.php';
?>
