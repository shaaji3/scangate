<?php
$page_title = "Users Management";
require_once __DIR__ . '/../bootstrap.php';

// Auth check
if ($_SESSION['user_role'] !== 'super_admin') {
    header("Location: ../dashboard.php");
    exit;
}

require_once __DIR__ . '/../includes/header-auth.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">Users Management</h4>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">+ Add New User</button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="users-table" class="display" style="min-width: 845px">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Joined Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Data will be populated by DataTables AJAX -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add User Modal -->
<div class="modal fade" id="addUserModal">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <!-- Form will be handled by admin.js -->
                <form id="add-user-form" novalidate>
                    <div class="alert alert-danger" style="display: none;"></div>
                    <input type="hidden" name="csrf_token" value="<?php echo CSRF::generateToken('add_user_form'); ?>">
                    <div class="mb-3">
                        <label class="form-label">Full Name</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Role</label>
                        <select name="role" class="form-select">
                            <option value="attendee">Attendee</option>
                            <option value="planner">Event Planner</option>
                            <option value="super_admin">Super Admin</option>
                        </select>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Add User</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Edit User Modal -->
<div class="modal fade" id="editUserModal">
    ... (content is the same) ...
</div>

<!-- Confirmation Modals -->
<div class="modal fade" id="suspendUserModal">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Action</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to toggle the status for this user?</p>
                <form id="suspend-user-form">
                    <input type="hidden" name="user_id" id="suspend-user-id">
                    <input type="hidden" name="csrf_token" value="<?php echo CSRF::generateToken('suspend_user_form'); ?>">
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" id="confirm-suspend-btn" class="btn btn-warning">Confirm</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="deleteUserModal">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Deletion</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to permanently delete this user? This action cannot be undone.</p>
                <form id="delete-user-form">
                    <input type="hidden" name="user_id" id="delete-user-id">
                    <input type="hidden" name="csrf_token" value="<?php echo CSRF::generateToken('delete_user_form'); ?>">
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" id="confirm-delete-btn" class="btn btn-danger">Delete</button>
            </div>
        </div>
    </div>
</div>


<!-- Include DataTables CSS/JS -->
<link href="../assets/vendor/datatables/css/jquery.dataTables.min.css" rel="stylesheet">
<script src="../assets/vendor/datatables/js/jquery.dataTables.min.js"></script>
<script src="../assets/js/admin.js"></script>

<?php
require_once __DIR__ . '/../includes/footer-auth.php';
?>
