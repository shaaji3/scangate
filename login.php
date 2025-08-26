<?php
$page_title = "Login";
require_once 'includes/header-public.php';
require_once 'utils/CSRF.php';
?>

<h4 class="text-center mb-4">Sign in your account</h4>

<div id="error-message" class="alert alert-danger" style="display: none;"></div>

<form id="login-form" method="POST">
    <input type="hidden" name="csrf_token" value="<?php echo CSRF::generateToken(); ?>">
    <div class="mb-3">
        <label class="mb-1"><strong>Email</strong></label>
        <input type="email" class="form-control" name="email" placeholder="hello@example.com" required>
    </div>
    <div class="mb-3">
        <label class="mb-1"><strong>Password</strong></label>
        <input type="password" class="form-control" name="password" required>
    </div>
    <div class="form-row d-flex flex-wrap justify-content-between align-items-baseline mb-2">
        <div class="mb-3">
           <div class="form-check custom-checkbox ms-1">
                <input type="checkbox" class="form-check-input" id="basic_checkbox_1">
                <label class="form-check-label" for="basic_checkbox_1">Remember my preference</label>
            </div>
        </div>
        <div class="mb-3">
            <a href="forgot-password.php">Forgot Password?</a>
        </div>
    </div>
    <div class="text-center">
        <button type="submit" id="login-button" class="btn btn-primary btn-block">
            <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true" style="display: none;"></span>
            Sign Me In
        </button>
    </div>
</form>
<div class="new-account mt-3">
    <p>Don't have an account? <a class="text-primary" href="register.php">Sign up</a></p>
</div>

<script src="assets/js/auth.js"></script>

<?php
require_once 'includes/footer-public.php';
?>
