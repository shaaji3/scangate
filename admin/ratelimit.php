<?php
$page_title = "Rate Limit Settings";
require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../repositories/RateLimitRepository.php';
require_once __DIR__ . '/../utils/CSRF.php';

// Auth check
if ($_SESSION['user_role'] !== 'super_admin') {
    header("Location: ../dashboard.php");
    exit;
}

$rateLimitRepo = new RateLimitRepository($pdo);
$rules = $rateLimitRepo->getAllRules();

require_once __DIR__ . '/../includes/header-auth.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>

<div class="row">
    <!-- Rules List Column -->
    <div class="col-xl-8">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">Current Rate Limit Rules</h4>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Action Name</th>
                                <th>Limit Count</th>
                                <th>Window (seconds)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($rules as $rule): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($rule['action']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($rule['limit_count']); ?></td>
                                    <td><?php echo htmlspecialchars($rule['window_seconds']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Add/Edit Rule Column -->
    <div class="col-xl-4">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">Add / Edit Rule</h4>
            </div>
            <div class="card-body">
                <div id="ratelimit-success" class="alert alert-success" style="display: none;"></div>
                <div id="ratelimit-error" class="alert alert-danger" style="display: none;"></div>

                <form id="ratelimit-form" method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo CSRF::generateToken('ratelimit_form'); ?>">

                    <div class="mb-3">
                        <label class="form-label">Action Name</label>
                        <input type="text" class="form-control" name="action" placeholder="e.g., delete_user" required>
                        <small class="form-text text-muted">This is the unique identifier for the rule.</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Limit Count</label>
                        <input type="number" class="form-control" name="limit_count" min="1" required>
                         <small class="form-text text-muted">Max number of attempts allowed.</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Window (seconds)</label>
                        <input type="number" class="form-control" name="window_seconds" min="1" required>
                        <small class="form-text text-muted">The time frame for the limit in seconds.</small>
                    </div>

                    <button type="submit" class="btn btn-primary">Save Rule</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="../assets/js/admin.js"></script>

<?php
require_once __DIR__ . '/../includes/footer-auth.php';
?>
