<?php
$page_title = "Forgot Password";
require_once 'includes/header-public.php';
require_once 'utils/CSRF.php';
?>

<h4 class="text-center mb-4">Forgot Password</h4>
<p class="text-center">Enter your email address and we will send you a link to reset your password.</p>

<div id="error-message" class="alert alert-danger" style="display: none;"></div>
<div id="success-message" class="alert alert-success" style="display: none;"></div>

<form id="forgot-password-form" method="POST">
    <input type="hidden" name="csrf_token" value="<?php echo CSRF::generateToken('forgot_password_form'); ?>">
    <div class="mb-3">
        <label class="mb-1"><strong>Email Address</strong></label>
        <input type="email" class="form-control" name="email" placeholder="hello@example.com" required>
        <div class="invalid-feedback"></div>
    </div>

    <div class="text-center mt-4">
        <button type="submit" id="forgot-password-button" class="btn btn-primary btn-block">
            <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true" style="display: none;"></span>
            Send Reset Link
        </button>
    </div>
</form>
<div class="new-account mt-3">
    <p>Remember your password? <a class="text-primary" href="login.php">Sign in</a></p>
</div>

<script src="assets/js/auth.js"></script>

<?php
require_once 'includes/footer-public.php';
?>
