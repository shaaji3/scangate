<?php

class RateLimitRepository
{
    protected PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * @return array
     */
    public function getAllRules(): array
    {
        $stmt = $this->pdo->query("SELECT * FROM rate_limit_rules ORDER BY action ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * @param string $action
     * @param int $limit_count
     * @param int $window_seconds
     * @return bool
     */
    public function saveRule(string $action, int $limit_count, int $window_seconds): bool
    {
        // Use INSERT ... ON DUPLICATE KEY UPDATE to handle both create and update
        $sql = "INSERT INTO rate_limit_rules (action, limit_count, window_seconds)
                VALUES (:action, :limit_count, :window_seconds)
                ON DUPLICATE KEY UPDATE
                limit_count = VALUES(limit_count),
                window_seconds = VALUES(window_seconds)";

        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':action' => $action,
            ':limit_count' => $limit_count,
            ':window_seconds' => $window_seconds
        ]);
    }
}
