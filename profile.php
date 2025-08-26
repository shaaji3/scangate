<?php
$page_title = "My Profile";
require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/utils/CSRF.php';

// AuthGuard is in header-auth.php, so user is guaranteed to be logged in.
$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];
// We can fetch the full user object if we need more details like email
// For now, session data is sufficient.

require_once __DIR__ . '/includes/header-auth.php';
require_once __DIR__ . '/includes/sidebar.php';
?>

<div class="row">
    <!-- Update Profile Column -->
    <div class="col-xl-6">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">Update Profile Information</h4>
            </div>
            <div class="card-body">
                <div id="update-profile-success" class="alert alert-success" style="display: none;"></div>
                <div id="update-profile-error" class="alert alert-danger" style="display: none;"></div>

                <form id="update-profile-form">
                    <input type="hidden" name="csrf_token" value="<?php echo CSRF::generateToken('update_profile_form'); ?>">

                    <div class="mb-3">
                        <label class="form-label">Full Name</label>
                        <input type="text" class="form-control" name="name" value="<?php echo htmlspecialchars($user_name); ?>" required>
                        <div class="invalid-feedback"></div>
                    </div>

                    <button type="submit" id="update-profile-button" class="btn btn-primary">
                        <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true" style="display: none;"></span>
                        Save Changes
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Change Password Column -->
    <div class="col-xl-6">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">Change Password</h4>
            </div>
            <div class="card-body">
                <div id="change-password-success" class="alert alert-success" style="display: none;"></div>
                <div id="change-password-error" class="alert alert-danger" style="display: none;"></div>

                <form id="change-password-form">
                    <input type="hidden" name="csrf_token" value="<?php echo CSRF::generateToken('change_password_form'); ?>">

                    <div class="mb-3">
                        <label class="form-label">Current Password</label>
                        <input type="password" class="form-control" name="current_password" required>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">New Password</label>
                        <input type="password" class="form-control" name="new_password" required>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Confirm New Password</label>
                        <input type="password" class="form-control" name="password_confirm" required>
                        <div class="invalid-feedback"></div>
                    </div>

                    <button type="submit" id="change-password-button" class="btn btn-primary">
                        <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true" style="display: none;"></span>
                        Change Password
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="assets/js/profile.js"></script>

<?php
require_once __DIR__ . '/includes/footer-auth.php';
?>
