<?php

require_once __DIR__ . '/../repositories/UserRepository.php';
require_once __DIR__ . '/../repositories/EventRepository.php';

class AuthGuard {

    /**
     * Checks if a user has permission to edit/manage a specific event.
     * An event can be managed by its direct planner or an assigned Event Manager.
     *
     * @param PDO $pdo The database connection.
     * @param int $user_id The ID of the user trying to perform the action.
     * @param int $event_id The ID of the event in question.
     * @return bool True if authorized, false otherwise.
     */
    public static function canEditEvent(PDO $pdo, int $user_id, int $event_id): bool {
        $userRepo = new UserRepository($pdo);
        $eventRepo = new EventRepository($pdo);

        $user = $userRepo->findUserById($user_id);
        $event = $eventRepo->findEventById($event_id);

        if (!$user || !$event) {
            return false; // User or event not found
        }

        // Case 1: The user is the main planner for the event.
        if ($event->planner_id === $user->id) {
            return true;
        }

        // Case 2: The user is a team member with the 'event_manager' role.
        if ($user->parent_planner_id === $event->planner_id && $user->role === 'event_manager') {
            return true;
        }

        return false;
    }

    /**
     * Checks if a user has permission to scan tickets for a specific event.
     * Tickets can be scanned by the planner, an Event Manager, or a Gate Agent.
     *
     * @param PDO $pdo The database connection.
     * @param int $user_id The ID of the user trying to perform the action.
     * @param int $event_id The ID of the event in question.
     * @return bool True if authorized, false otherwise.
     */
    public static function canScanTickets(PDO $pdo, int $user_id, int $event_id): bool {
        $userRepo = new UserRepository($pdo);
        $eventRepo = new EventRepository($pdo);

        $user = $userRepo->findUserById($user_id);
        $event = $eventRepo->findEventById($event_id);

        if (!$user || !$event) {
            return false; // User or event not found
        }

        // Case 1: The user is the main planner for the event.
        if ($event->planner_id === $user->id) {
            return true;
        }

        // Case 2: The user is a team member with a role that can scan.
        $allowed_roles = ['event_manager', 'gate_agent'];
        if ($user->parent_planner_id === $event->planner_id && in_array($user->role, $allowed_roles)) {
            return true;
        }

        return false;
    }
}
