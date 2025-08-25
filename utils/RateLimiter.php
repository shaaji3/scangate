<?php
class RateLimiter
{
    private $conn;
    private $ipAddress;
    private $userId;

    public function __construct(mysqli $conn, $userId = null)
    {
        $this->conn = $conn;
        $this->ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $this->userId = $userId;
    }

    /**
     * Combined check for global + specific action
     * Returns array: ['allowed' => true/false, 'reason' => string, 'retry_after' => int, 'remaining' => int]
     */
    public function checkWithGlobal($action, $globalAction = 'global')
    {
        // 1. Check global limit first
        $globalCheck = $this->checkAndStatus($globalAction);
        if (!$globalCheck['allowed']) {
            return $globalCheck; // Blocked by global limit
        }

        // 2. Check action-specific limit
        $actionCheck = $this->checkAndStatus($action);
        return $actionCheck;
    }

    protected function up()
    {
        
        $this->conn->query("CREATE TABLE IF NOT EXISTS rate_limit_rules (
        id BIGINT AUTO_INCREMENT PRIMARY KEY,
        action VARCHAR(50) NOT NULL UNIQUE,
        limit_count INT NOT NULL DEFAULT 3,
        window_seconds INT NOT NULL DEFAULT 600, -- 10 minutes
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )") or die($this->conn->error);

        
        $this->conn->query("CREATE TABLE IF NOT EXISTS rate_limit_tracker (
        id BIGINT AUTO_INCREMENT PRIMARY KEY,
        user_id BIGINT NULL,
        ip_address VARCHAR(45) NULL,
        action VARCHAR(50) NOT NULL,
        attempts INT NOT NULL DEFAULT 1,
        window_start DATETIME NOT NULL,
        window_end DATETIME NOT NULL,
        last_attempt DATETIME NOT NULL,
        status ENUM('active', 'blocked') DEFAULT 'active',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_user_action (user_id, action),
        INDEX idx_ip_action (ip_address, action)
        )") or die($this->conn->error);
        return "Created core tables successfully!";
    }

    /**
     * Internal: Check limit and return detailed status
     */
    private function checkAndStatus($action)
    {
        $rule = $this->getRule($action);
        if (!$rule) {
            throw new Exception("Rate limit rule not defined for action: $action");
        }

        $record = $this->getTracker($action);
        $now = new DateTime();
        $windowEnd = null;

        if ($record) {
            $windowEnd = new DateTime($record['window_end']);

            if ($now > $windowEnd) {
                // Reset and allow
                $this->resetTracker($action, $rule['window_seconds']);
                return [
                    'allowed' => true,
                    'reason' => 'reset',
                    'retry_after' => 0,
                    'remaining' => $rule['limit_count'] - 1
                ];
            }

            if ($record['attempts'] >= $rule['limit_count']) {
                // Blocked
                return [
                    'allowed' => false,
                    'reason' => 'limit_exceeded',
                    'retry_after' => max(0, $windowEnd->getTimestamp() - $now->getTimestamp()),
                    'remaining' => 0
                ];
            }

            // Increment attempts and allow
            $this->incrementAttempts($record['id']);
            return [
                'allowed' => true,
                'reason' => 'ok',
                'retry_after' => 0,
                'remaining' => $rule['limit_count'] - ($record['attempts'] + 1)
            ];

        } else {
            // Create tracker entry
            $this->createTracker($action, $rule['window_seconds']);
            return [
                'allowed' => true,
                'reason' => 'new_entry',
                'retry_after' => 0,
                'remaining' => $rule['limit_count'] - 1
            ];
        }
    }

    private function getRule($action)
    {
        $stmt = $this->conn->prepare("SELECT * FROM rate_limit_rules WHERE action = ?");
        $stmt->bind_param("s", $action);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    private function getTracker($action)
    {
        $stmt = $this->conn->prepare("
            SELECT * FROM rate_limit_tracker
            WHERE action = ? AND (user_id = ? OR ip_address = ?)
            ORDER BY id DESC LIMIT 1
        ");
        $stmt->bind_param("sis", $action, $this->userId, $this->ipAddress);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    private function createTracker($action, $windowSeconds)
    {
        $windowStart = new DateTime();
        $windowEnd = (clone $windowStart)->modify("+$windowSeconds seconds");
        $windowStartStr = $windowStart->format('Y-m-d H:i:s');
        $windowEndStr = $windowEnd->format('Y-m-d H:i:s');

        $stmt = $this->conn->prepare("
            INSERT INTO rate_limit_tracker (user_id, ip_address, action, attempts, window_start, window_end, last_attempt)
            VALUES (?, ?, ?, 1, ?, ?, ?)
        ");
        $stmt->bind_param("isssss", $this->userId, $this->ipAddress, $action, $windowStartStr, $windowEndStr, $windowStartStr);
        $stmt->execute();
    }

    private function incrementAttempts($id)
    {
        $stmt = $this->conn->prepare("UPDATE rate_limit_tracker SET attempts = attempts + 1, last_attempt = NOW() WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
    }

    private function resetTracker($action, $windowSeconds)
    {
        $stmt = $this->conn->prepare("DELETE FROM rate_limit_tracker WHERE action = ? AND (user_id = ? OR ip_address = ?)");
        $stmt->bind_param("sis", $action, $this->userId, $this->ipAddress);
        $stmt->execute();
        $this->createTracker($action, $windowSeconds);
    }

}