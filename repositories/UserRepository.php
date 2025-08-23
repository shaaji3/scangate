<?php

require_once __DIR__ . '/../classes/User.php';

class UserRepository {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Creates a new user in the database.
     * @param User $user The user object to save.
     * @return bool True on success, false on failure.
     */
    public function createUser(User $user) {
        $sql = "INSERT INTO users (name, email, password, role) VALUES (:name, :email, :password, :role)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':name', $user->name);
        $stmt->bindValue(':email', $user->email);
        $stmt->bindValue(':password', $user->password); // The hashed password
        $stmt->bindValue(':role', $user->role);
        return $stmt->execute();
    }

    /**
     * Finds a user by their email address.
     * @param string $email The email to search for.
     * @return User|false The User object if found, false otherwise.
     */
    public function findUserByEmail($email) {
        $sql = "SELECT * FROM users WHERE email = :email";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':email', $email);
        $stmt->execute();
        $stmt->setFetchMode(PDO::FETCH_CLASS, 'User');
        return $stmt->fetch();
    }

    /**
     * Finds a user by their ID.
     * @param int $id The user ID to search for.
     * @return User|false The User object if found, false otherwise.
     */
    public function findUserById($id) {
        $sql = "SELECT * FROM users WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id', $id);
        $stmt->execute();
        $stmt->setFetchMode(PDO::FETCH_CLASS, 'User');
        return $stmt->fetch();
    }
}
