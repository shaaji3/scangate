<?php
$page_title = "Admin Dashboard";
require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../repositories/AdminRepository.php';

// AuthGuard is in header-auth.php
// Additional check for super_admin role
if ($_SESSION['user_role'] !== 'super_admin') {
    header("Location: ../dashboard.php");
    exit;
}

$adminRepo = new AdminRepository($pdo);
$summary = $adminRepo->getDashboardSummary();
$salesChartData = $adminRepo->getSalesChartData();

// Prepare data for Chart.js
$chartLabels = [];
$chartValues = [];
foreach ($salesChartData as $data) {
    $chartLabels[] = date("M d", strtotime($data['sale_date']));
    $chartValues[] = $data['daily_total'];
}

require_once __DIR__ . '/../includes/header-auth.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>

<div class="row">
    <!-- Summary Card: Total Users -->
    <div class="col-xl-4 col-sm-6">
        <div class="card">
            <div class="card-body d-flex px-4 pb-0 justify-content-between">
                <div>
                    <h4 class="fs-18 font-w600 mb-4 text-nowrap">Total Users</h4>
                    <div class="d-flex align-items-center">
                        <h2 class="fs-32 font-w700 mb-0"><?php echo number_format($summary['total_users']); ?></h2>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Summary Card: Total Events -->
    <div class="col-xl-4 col-sm-6">
        <div class="card">
            <div class="card-body d-flex px-4 pb-0 justify-content-between">
                <div>
                    <h4 class="fs-18 font-w600 mb-4 text-nowrap">Total Events</h4>
                    <div class="d-flex align-items-center">
                        <h2 class="fs-32 font-w700 mb-0"><?php echo number_format($summary['total_events']); ?></h2>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Summary Card: Total Sales -->
    <div class="col-xl-4 col-sm-6">
        <div class="card">
            <div class="card-body d-flex px-4 pb-0 justify-content-between">
                <div>
                    <h4 class="fs-18 font-w600 mb-4 text-nowrap">Total Sales</h4>
                    <div class="d-flex align-items-center">
                        <h2 class="fs-32 font-w700 mb-0">$<?php echo number_format($summary['total_sales'], 2); ?></h2>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">Sales Over Last 30 Days</h4>
            </div>
            <div class="card-body">
                <canvas id="salesOverTimeChart"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js is already included in footer-auth.php -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('salesOverTimeChart').getContext('2d');
    if (ctx) {
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($chartLabels); ?>,
                datasets: [{
                    label: 'Daily Sales',
                    data: <?php echo json_encode($chartValues); ?>,
                    borderColor: 'rgba(33, 48, 184, 1)',
                    backgroundColor: 'rgba(33, 48, 184, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                },
                responsive: true,
                maintainAspectRatio: false
            }
        });
    }
});
</script>


<?php
require_once __DIR__ . '/../includes/footer-auth.php';
?>
