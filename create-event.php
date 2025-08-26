<?php
$page_title = "Create New Event";
require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/utils/CSRF.php';

// AuthGuard is in header-auth
// Additional check for planner role
if ($_SESSION['user_role'] !== 'planner') {
    // Or redirect to a more appropriate "access denied" page
    header("Location: dashboard.php");
    exit;
}

require_once __DIR__ . '/includes/header-auth.php';
require_once __DIR__ . '/includes/sidebar.php';
?>

<div class="row">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">Create a New Event</h4>
            </div>
            <div class="card-body">
                <div id="error-message" class="alert alert-danger" style="display: none;"></div>
                <div id="success-message" class="alert alert-success" style="display: none;"></div>

                <form id="create-event-form" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?php echo CSRF::generateToken('create_event_form'); ?>">

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Event Title</label>
                            <input type="text" class="form-control" name="title" required>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Location</label>
                            <input type="text" class="form-control" name="location" required>
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" rows="5" required></textarea>
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Date and Time</label>
                            <input type="datetime-local" class="form-control" name="date" required>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Event Banner</label>
                            <input type="file" class="form-control" name="banner" accept="image/png, image/jpeg, image/gif">
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Status</label>
                            <select class="form-select form-control" name="status" required>
                                <option value="draft">Draft</option>
                                <option value="published">Published</option>
                            </select>
                        </div>
                    </div>

                    <button type="submit" id="create-event-button" class="btn btn-primary">
                        <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true" style="display: none;"></span>
                        Create Event
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
