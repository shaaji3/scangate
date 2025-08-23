<?php

require_once __DIR__ . '/../classes/Ticket.php';

class TicketRepository {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Creates a new ticket type for an event.
     * @param Ticket $ticket The ticket object to save.
     * @return bool True on success, false on failure.
     */
    public function createTicket(Ticket $ticket) {
        $sql = "INSERT INTO tickets (event_id, name, price, quantity)
                VALUES (:event_id, :name, :price, :quantity)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':event_id', $ticket->event_id);
        $stmt->bindValue(':name', $ticket->name);
        $stmt->bindValue(':price', $ticket->price);
        $stmt->bindValue(':quantity', $ticket->quantity);
        return $stmt->execute();
    }

    /**
     * Finds all ticket types for a given event.
     * @param int $event_id The ID of the event.
     * @return array An array of Ticket objects.
     */
    public function findTicketsByEventId($event_id) {
        $sql = "SELECT * FROM tickets WHERE event_id = :event_id ORDER BY price ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':event_id', $event_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_CLASS, 'Ticket');
    }

    /**
     * Finds a single ticket by its ID.
     * @param int $ticket_id The ID of the ticket.
     * @return Ticket|false The Ticket object if found, false otherwise.
     */
    public function findTicketById($ticket_id) {
        $sql = "SELECT * FROM tickets WHERE id = :ticket_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':ticket_id', $ticket_id, PDO::PARAM_INT);
        $stmt->execute();
        $stmt->setFetchMode(PDO::FETCH_CLASS, 'Ticket');
        return $stmt->fetch();
    }
}
