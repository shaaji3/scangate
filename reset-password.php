<?php
$page_title = "Reset Password";
require_once 'includes/header-public.php';
require_once 'utils/CSRF.php';
require_once 'repositories/PasswordResetRepository.php';

$token = $_GET['token'] ?? '';
$is_token_valid = false;
$token_error_message = '';

if (empty($token)) {
    $token_error_message = "No reset token provided. Please use the link from your email.";
} else {
    $resetRepo = new PasswordResetRepository($pdo);
    $token_hash = hash('sha256', $token);
    $reset_data = $resetRepo->findToken($token_hash);

    if (!$reset_data) {
        $token_error_message = "This password reset link is invalid.";
    } elseif (time() > $reset_data['expires']) {
        $token_error_message = "This password reset link has expired. Please request a new one.";
    } else {
        $is_token_valid = true;
    }
}
?>

<h4 class="text-center mb-4">Reset Your Password</h4>

<div id="error-message" class="alert alert-danger" style="display: none;"></div>
<div id="success-message" class="alert alert-success" style="display: none;"></div>

<?php if ($is_token_valid): ?>
    <form id="reset-password-form" method="POST">
        <input type="hidden" name="csrf_token" value="<?php echo CSRF::generateToken('reset_password_form'); ?>">
        <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">

        <div class="mb-3">
            <label class="mb-1"><strong>New Password</strong></label>
            <input type="password" class="form-control" name="password" required>
            <div class="invalid-feedback"></div>
        </div>

        <div class="mb-3">
            <label class="mb-1"><strong>Confirm New Password</strong></label>
            <input type="password" class="form-control" name="password_confirm" required>
            <div class="invalid-feedback"></div>
        </div>

        <div class="text-center mt-4">
            <button type="submit" id="reset-password-button" class="btn btn-primary btn-block">
                <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true" style="display: none;"></span>
                Reset Password
            </button>
        </div>
    </form>
<?php else: ?>
    <div class="alert alert-danger"><?php echo $token_error_message; ?></div>
    <div class="text-center">
        <a href="forgot-password.php" class="btn btn-primary">Request a New Link</a>
    </div>
<?php endif; ?>

<div class="new-account mt-3">
    <p>Remember your password? <a class="text-primary" href="login.php">Sign in</a></p>
</div>

<script src="assets/js/auth.js"></script>

<?php
require_once 'includes/footer-public.php';
?>
