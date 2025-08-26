<?php
$page_title = "Register";
require_once 'includes/header-public.php';
require_once 'utils/CSRF.php';
?>

<h4 class="text-center mb-4">Sign up your account</h4>

<div id="error-message" class="alert alert-danger" style="display: none;"></div>
<div id="success-message" class="alert alert-success" style="display: none;"></div>


<form id="register-form" method="POST">
    <input type="hidden" name="csrf_token" value="<?php echo CSRF::generateToken('register_form'); ?>">

    <div class="mb-3">
        <label class="mb-1"><strong>Full Name</strong></label>
        <input type="text" class="form-control" name="name" placeholder="John Doe" required>
        <div class="invalid-feedback"></div>
    </div>

    <div class="mb-3">
        <label class="mb-1"><strong>Email</strong></label>
        <input type="email" class="form-control" name="email" placeholder="hello@example.com" required>
        <div class="invalid-feedback"></div>
    </div>

    <div class="mb-3">
        <label class="mb-1"><strong>Password</strong></label>
        <input type="password" class="form-control" name="password" required>
        <div class="invalid-feedback"></div>
    </div>

    <div class="mb-3">
        <label class="mb-1"><strong>Register as</strong></label>
        <select class="form-select form-control" name="role" required>
            <option value="attendee">Attendee</option>
            <option value="planner">Event Planner</option>
        </select>
    </div>

    <div class="text-center mt-4">
        <button type="submit" id="register-button" class="btn btn-primary btn-block">
            <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true" style="display: none;"></span>
            Sign me up
        </button>
    </div>
</form>
<div class="new-account mt-3">
    <p>Already have an account? <a class="text-primary" href="login.php">Sign in</a></p>
</div>

<script src="assets/js/auth.js"></script>

<?php
require_once 'includes/footer-public.php';
?>
