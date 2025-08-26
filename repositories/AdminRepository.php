<?php

class AdminRepository
{
    protected PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Gets dashboard summary statistics.
     * @return array
     */
    public function getDashboardSummary(): array
    {
        $summary = [];

        // Total Users
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM users");
        $summary['total_users'] = $stmt->fetchColumn();

        // Total Events
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM events");
        $summary['total_events'] = $stmt->fetchColumn();

        // Total Sales (from successful payments)
        $stmt = $this->pdo->query("SELECT SUM(amount) FROM payments WHERE status = 'success'");
        $total_sales = $stmt->fetchColumn();
        $summary['total_sales'] = $total_sales ?: 0;

        return $summary;
    }

    /**
     * Gets sales data for a chart, e.g., last 30 days.
     * @return array
     */
    public function getSalesChartData(): array
    {
        // Example: Get daily sales for the last 30 days
        $sql = "SELECT
                    DATE(created_at) as sale_date,
                    SUM(amount) as daily_total
                FROM payments
                WHERE status = 'success' AND created_at >= CURDATE() - INTERVAL 30 DAY
                GROUP BY DATE(created_at)
                ORDER BY sale_date ASC";

        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
