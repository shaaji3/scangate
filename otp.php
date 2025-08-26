<?php
$page_title = "OTP Verification";
require_once 'includes/header-public.php';
require_once 'utils/CSRF.php';

// If user hasn't been prompted for OTP, redirect them.
if (!isset($_SESSION['otp_user_id'])) {
    header('Location: login.php');
    exit;
}
?>

<h4 class="text-center mb-4">Enter Verification Code</h4>
<p class="text-center">A verification code has been sent to your email.</p>

<div id="error-message" class="alert alert-danger" style="display: none;"></div>

<form id="otp-form" method="POST">
    <input type="hidden" name="csrf_token" value="<?php echo CSRF::generateToken('otp_form'); ?>">
    <div class="mb-3">
        <label class="mb-1"><strong>Verification Code</strong></label>
        <input type="text" class="form-control" name="otp_code" required>
    </div>
    <div class="text-center">
        <button type="submit" id="otp-button" class="btn btn-primary btn-block">
            <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true" style="display: none;"></span>
            Verify
        </button>
    </div>
</form>
<div class="new-account mt-3">
    <p>Didn't get a code? <a class="text-primary" href="#">Resend</a></p>
</div>

<script src="assets/js/auth.js"></script>

<?php
require_once 'includes/footer-public.php';
?>
