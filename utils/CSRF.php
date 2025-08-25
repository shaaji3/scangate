<?php

class CSRF {

    /**
     * Generates a CSRF token, stores it in the session, and returns it.
     * If a token already exists in the session, it will be returned instead of creating a new one.
     *
     * @return string The CSRF token.
     */
    public static function generateToken(): string {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    /**
     * Validates a submitted CSRF token against the one stored in the session.
     *
     * @param string|null $submitted_token The token submitted with the form.
     * @return bool True if the token is valid, false otherwise.
     */
    public static function validateToken(?string $submitted_token): bool {
        if (empty($submitted_token) || empty($_SESSION['csrf_token'])) {
            return false;
        }

        // Use hash_equals for timing-attack-safe comparison
        $is_valid = hash_equals($_SESSION['csrf_token'], $submitted_token);

        // A single-use token would be unset here. For simplicity and better UX with multiple tabs,
        // we'll use a single token per session. To make it single-use, you would add:
        // unset($_SESSION['csrf_token']);

        return $is_valid;
    }
}
