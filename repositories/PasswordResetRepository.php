<?php

class PasswordResetRepository
{
    protected PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * @param string $email
     * @param string $token
     * @param int $expires
     * @return bool
     */
    public function createResetToken(string $email, string $token, int $expires): bool
    {
        // Delete any existing tokens for this email to prevent multiple valid tokens
        $this->deleteTokenForEmail($email);

        $sql = "INSERT INTO password_resets (email, token, expires) VALUES (:email, :token, :expires)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':email' => $email,
            ':token' => $token,
            ':expires' => $expires
        ]);
    }

    /**
     * @param string $token
     * @return array|null
     */
    public function findToken(string $token): ?array
    {
        $sql = "SELECT * FROM password_resets WHERE token = :token";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':token' => $token]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * @param string $email
     * @return bool
     */
    public function deleteTokenForEmail(string $email): bool
    {
        $sql = "DELETE FROM password_resets WHERE email = :email";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([':email' => $email]);
    }
}
