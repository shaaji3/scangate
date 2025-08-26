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

    /**
     * Updates a user's name.
     * @param int $user_id The ID of the user to update.
     * @param string $new_name The new name for the user.
     * @return bool True on success, false on failure.
     */
    public function updateUserName(int $user_id, string $new_name): bool
    {
        $sql = "UPDATE users SET name = :name WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':name' => $new_name,
            ':id' => $user_id
        ]);
    }

    /**
     * Gets users for server-side DataTables.
     * @param array $params Parameters from DataTables (start, length, search, order).
     * @return array Contains `data`, `recordsTotal`, `recordsFiltered`.
     */
    public function getUsersForDataTable(array $params): array
    {
        $baseSql = "FROM users";
        $totalRecords = $this->pdo->query("SELECT COUNT(*) $baseSql")->fetchColumn();

        // Search filtering
        $searchSql = "";
        if (!empty($params['search']['value'])) {
            $searchValue = "%" . $params['search']['value'] . "%";
            $searchSql = " WHERE name LIKE :search_value OR email LIKE :search_value";
        }

        // Get filtered records count
        $filteredRecordsSql = "SELECT COUNT(*) $baseSql $searchSql";
        $stmt = $this->pdo->prepare($filteredRecordsSql);
        if (!empty($searchValue)) {
            $stmt->bindValue(':search_value', $searchValue, PDO::PARAM_STR);
        }
        $stmt->execute();
        $recordsFiltered = $stmt->fetchColumn();

        // Ordering
        $orderSql = "";
        if (isset($params['order'])) {
            $columnIdx = $params['order'][0]['column'];
            $columnName = $params['columns'][$columnIdx]['data'];
            $dir = $params['order'][0]['dir'];
            // Whitelist column names to prevent SQL injection
            $allowedColumns = ['id', 'name', 'email', 'role', 'status', 'created_at'];
            if (in_array($columnName, $allowedColumns)) {
                $orderSql = " ORDER BY " . $columnName . " " . strtoupper($dir);
            }
        }

        // Pagination
        $limitSql = "";
        if (isset($params['start']) && isset($params['length'])) {
            $limitSql = " LIMIT " . (int)$params['start'] . ", " . (int)$params['length'];
        }

        // Final query
        $dataSql = "SELECT id, name, email, role, status, created_at $baseSql $searchSql $orderSql $limitSql";
        $stmt = $this->pdo->prepare($dataSql);
        if (!empty($searchValue)) {
            $stmt->bindValue(':search_value', $searchValue, PDO::PARAM_STR);
        }
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            "draw"            => intval($params['draw']),
            "recordsTotal"    => intval($totalRecords),
            "recordsFiltered" => intval($recordsFiltered),
            "data"            => $data
        ];
    }

    /**
     * Updates a user's details by an admin.
     * @param int $user_id
     * @param string $name
     * @param string $email
     * @param string $role
     * @param string $status
     * @return bool
     */
    public function updateUserAsAdmin(int $user_id, string $name, string $email, string $role, string $status): bool
    {
        $sql = "UPDATE users SET name = :name, email = :email, role = :role, status = :status WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':name' => $name,
            ':email' => $email,
            ':role' => $role,
            ':status' => $status,
            ':id' => $user_id
        ]);
    }

    /**
     * Updates a user's status.
     * @param int $user_id
     * @param string $status
     * @return bool
     */
    public function updateUserStatus(int $user_id, string $status): bool
    {
        $sql = "UPDATE users SET status = :status WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([':status' => $status, ':id' => $user_id]);
    }

    /**
     * Deletes a user from the database.
     * @param int $user_id
     * @return bool
     */
    public function deleteUser(int $user_id): bool
    {
        // Add constraints check here if needed, e.g., don't delete if they have orders.
        // For now, we assume direct deletion is allowed.
        $sql = "DELETE FROM users WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([':id' => $user_id]);
    }
}
