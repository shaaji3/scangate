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
        $sql = "INSERT INTO users (name, email, password, role, parent_planner_id)
                VALUES (:name, :email, :password, :role, :parent_planner_id)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':name', $user->name);
        $stmt->bindValue(':email', $user->email);
        $stmt->bindValue(':password', $user->password); // The hashed password
        $stmt->bindValue(':role', $user->role);
        $stmt->bindValue(':parent_planner_id', $user->parent_planner_id, PDO::PARAM_INT);
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

    /**
     * Finds all issued tickets for a given user for paid orders.
     * @param int $user_id The user's ID.
     * @return array An array of associated ticket data.
     */
    public function findUserTickets($user_id) {
        $sql = "SELECT
                    it.ticket_code,
                    e.title AS event_title,
                    t.name AS ticket_type,
                    e.date AS event_date
                FROM issued_tickets AS it
                JOIN order_items AS oi ON it.order_item_id = oi.id
                JOIN tickets AS t ON oi.ticket_id = t.id
                JOIN orders AS o ON oi.order_id = o.id
                JOIN events AS e ON o.event_id = e.id
                WHERE o.user_id = :user_id AND o.status = 'paid'
                ORDER BY e.date ASC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Finds all team members associated with a given planner.
     * @param int $planner_id The planner's user ID.
     * @return array An array of team member data.
     */
    public function findTeamMembersByPlannerId($planner_id) {
        $sql = "SELECT id, name, email, role FROM users WHERE parent_planner_id = :planner_id ORDER BY name ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':planner_id', $planner_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Updates a user's password.
     * @param int $user_id The ID of the user to update.
     * @param string $new_password_hash The new, hashed password.
     * @return bool True on success, false on failure.
     */
    public function updateUserPassword(int $user_id, string $new_password_hash): bool
    {
        $sql = "UPDATE users SET password = :password WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':password' => $new_password_hash,
            ':id' => $user_id
        ]);
    }
}
