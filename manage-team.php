<?php
session_start();

// Security: Only planners can access
if (!isset($_SESSION["user_id"]) || $_SESSION['user_role'] !== 'planner') {
    header("Location: login.php");
    exit;
}

require_once 'config/database.php';
require_once 'repositories/UserRepository.php';

$userRepo = new UserRepository($pdo);
$team_members = $userRepo->findTeamMembersByPlannerId($_SESSION['user_id']);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Team</title>
    <style>
        /* Re-using styles from dashboard.php for consistency */
        body { font-family: sans-serif; }
        .container { padding: 20px; max-width: 960px; margin: auto; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .btn { background-color: #007bff; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .section { margin-top: 40px; padding: 20px; border: 1px solid #eee; border-radius: 5px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; }
        input[type="text"], input[type="email"], select { width: 100%; padding: 8px; box-sizing: border-box; }
        .message { padding: 10px; margin-bottom: 15px; border-radius: 5px; }
        .error { background-color: #f8d7da; color: #721c24; }
        .success { background-color: #d4edda; color: #155724; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Manage Team</h1>
            <a href="dashboard.php" class="btn">Back to Dashboard</a>
        </div>

        <?php
        if (isset($_SESSION['success_message'])) {
            echo '<div class="message success">' . $_SESSION['success_message'] . '</div>';
            unset($_SESSION['success_message']);
        }
        if (isset($_SESSION['error_message'])) {
            echo '<div class="message error">' . $_SESSION['error_message'] . '</div>';
            unset($_SESSION['error_message']);
        }
        ?>

        <div class="section">
            <h3>Current Team Members</h3>
            <?php if (!empty($team_members)): ?>
                <table>
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
                                <td><?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $member['role']))); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>You have not added any team members yet.</p>
            <?php endif; ?>
        </div>

        <div class="section">
            <h3>Add New Team Member</h3>
            <form action="handle-add-member.php" method="POST">
                <div class="form-group">
                    <label for="name">Full Name</label>
                    <input type="text" id="name" name="name" required>
                </div>
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="role">Assign Role</label>
                    <select id="role" name="role" required>
                        <option value="event_manager">Event Manager</option>
                        <option value="gate_agent">Gate Agent</option>
                    </select>
                </div>
                <button type="submit" class="btn">Add Member</button>
            </form>
        </div>
    </div>
</body>
</html>
