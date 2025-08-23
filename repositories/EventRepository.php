<?php

require_once __DIR__ . '/../classes/Event.php';

class EventRepository {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Creates a new event in the database.
     * @param Event $event The event object to save.
     * @return bool True on success, false on failure.
     */
    public function createEvent(Event $event) {
        $sql = "INSERT INTO events (planner_id, title, description, location, date, banner, status)
                VALUES (:planner_id, :title, :description, :location, :date, :banner, :status)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':planner_id', $event->planner_id);
        $stmt->bindValue(':title', $event->title);
        $stmt->bindValue(':description', $event->description);
        $stmt->bindValue(':location', $event->location);
        $stmt->bindValue(':date', $event->date);
        $stmt->bindValue(':banner', $event->banner);
        $stmt->bindValue(':status', $event->status);
        return $stmt->execute();
    }

    /**
     * Finds an event by its ID.
     * @param int $id The event ID.
     * @return Event|false The Event object if found, false otherwise.
     */
    public function findEventById($id) {
        $sql = "SELECT * FROM events WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id', $id);
        $stmt->execute();
        $stmt->setFetchMode(PDO::FETCH_CLASS, 'Event');
        return $stmt->fetch();
    }

    /**
     * Finds all events, optionally filtered by status.
     * @param string|null $status The status to filter by (e.g., 'published').
     * @return array An array of Event objects.
     */
    public function findAllEvents($status = null) {
        $sql = "SELECT * FROM events";
        if ($status) {
            $sql .= " WHERE status = :status";
        }
        $sql .= " ORDER BY date DESC";
        $stmt = $this->pdo->prepare($sql);
        if ($status) {
            $stmt->bindValue(':status', $status);
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_CLASS, 'Event');
    }

    /**
     * Finds all events created by a specific planner.
     * @param int $planner_id The planner's user ID.
     * @return array An array of Event objects.
     */
    public function findEventsByPlanner($planner_id) {
        $sql = "SELECT * FROM events WHERE planner_id = :planner_id ORDER BY date DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':planner_id', $planner_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_CLASS, 'Event');
    }
}
