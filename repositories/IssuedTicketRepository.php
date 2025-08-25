<?php

require_once __DIR__ . '/../classes/IssuedTicket.php';

class IssuedTicketRepository {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Finds a single issued ticket by its code and joins related event info.
     * @param string $ticket_code The unique code of the ticket.
     * @return array|false An associative array with ticket and event info, or false if not found.
     */
    public function findByCode($ticket_code) {
        $sql = "SELECT
                    it.id,
                    it.ticket_code,
                    it.status,
                    o.event_id,
                    e.planner_id
                FROM issued_tickets AS it
                JOIN order_items AS oi ON it.order_item_id = oi.id
                JOIN orders AS o ON oi.order_id = o.id
                JOIN events AS e ON o.event_id = e.id
                WHERE it.ticket_code = :ticket_code";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':ticket_code', $ticket_code);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Updates the status of an issued ticket.
     * @param string $ticket_code The unique code of the ticket.
     * @param string $new_status The new status (e.g., 'used').
     * @return bool True on success, false on failure.
     */
    public function updateStatus($ticket_code, $new_status) {
        $sql = "UPDATE issued_tickets SET status = :new_status WHERE ticket_code = :ticket_code";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':new_status', $new_status);
        $stmt->bindValue(':ticket_code', $ticket_code);
        return $stmt->execute();
    }
}
