<?php
$page_title = "Manage Team";
require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/repositories/UserRepository.php';
require_once __DIR__ . '/utils/CSRF.php';

// AuthGuard is in header-auth.php
// Additional check for planner role
if ($_SESSION['user_role'] !== 'planner') {
    header("Location: dashboard.php");
    exit;
}

$userRepo = new UserRepository($pdo);
$team_members = $userRepo->findTeamMembersByPlannerId($_SESSION['user_id']);

require_once __DIR__ . '/includes/header-auth.php';
require_once __DIR__ . '/includes/sidebar.php';
?>

<div class="row">
    <!-- Team Members List Column -->
    <div class="col-xl-8">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">Current Team Members</h4>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <?php if (!empty($team_members)): ?>
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($team_members as $member): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($member['name']); ?></td>
                                        <td><?php echo htmlspecialchars($member['email']); ?></td>
                                        <td><span class="badge light badge-info"><?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $member['role']))); ?></span></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p>You have not added any team members yet.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Team Member Column -->
    <div class="col-xl-4">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">Add New Team Member</h4>
            </div>
            <div class="card-body">
                <div id="add-member-error" class="alert alert-danger" style="display: none;"></div>
                <div id="add-member-success" class="alert alert-success" style="display: none;"></div>

                <form id="add-member-form" method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo CSRF::generateToken('add_member_form'); ?>">

                    <div class="mb-3">
                        <label class="form-label">Full Name</label>
                        <input type="text" class="form-control" name="name" required>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email Address</label>
                        <input type="email" class="form-control" name="email" required>
                        <div class="invalid-feedback"></div>
                    </div>
                     <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" class="form-control" name="password" required>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Assign Role</label>
                        <select class="form-select form-control" name="role" required>
                            <option value="event_manager">Event Manager</option>
                            <option value="gate_agent">Gate Agent</option>
                        </select>
                    </div>
                    <button type="submit" id="add-member-button" class="btn btn-primary">
                        <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true" style="display: none;"></span>
                        Add Member
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="assets/js/team.js"></script>

<?php
require_once __DIR__ . '/includes/footer-auth.php';
?>
