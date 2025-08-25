<?php

class User {
    // Properties
    public $id;
    public $name;
    public $email;
    public $password;
    public $role;
    public $status;
    public $parent_planner_id;

    // Methods
    public function __construct() {
        // Constructor logic
    }

    /**
     * Hashes the password and sets it on the user object.
     * @param string $plain_password The password to hash.
     */
    public function setPassword($plain_password) {
        $this->password = password_hash($plain_password, PASSWORD_DEFAULT);
    }

    /**
     * Verifies a given password against the user's hashed password.
     * @param string $plain_password The password to verify.
     * @return bool True if the password is correct, false otherwise.
     */
    public function verifyPassword($plain_password) {
        return password_verify($plain_password, $this->password);
    }
}
