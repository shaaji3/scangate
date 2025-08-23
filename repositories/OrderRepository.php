<?php

require_once __DIR__ . '/../classes/Order.php';

class OrderRepository {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Creates a new order and its associated items in a transaction.
     * @param Order $order The order object.
     * @param array $items An array of items, each with 'ticket_id', 'quantity', and 'price'.
     * @return int|false The new order ID on success, false on failure.
     */
    public function createOrderWithItems(Order $order, array $items) {
        try {
            $this->pdo->beginTransaction();

            // Step 1: Create the order
            $sqlOrder = "INSERT INTO orders (user_id, event_id, total_amount, status)
                         VALUES (:user_id, :event_id, :total_amount, :status)";
            $stmtOrder = $this->pdo->prepare($sqlOrder);
            $stmtOrder->bindValue(':user_id', $order->user_id);
            $stmtOrder->bindValue(':event_id', $order->event_id);
            $stmtOrder->bindValue(':total_amount', $order->total_amount);
            $stmtOrder->bindValue(':status', $order->status);
            $stmtOrder->execute();

            $orderId = $this->pdo->lastInsertId();

            // Step 2: Create the order items
            $sqlItem = "INSERT INTO order_items (order_id, ticket_id, quantity, price)
                        VALUES (:order_id, :ticket_id, :quantity, :price)";
            $stmtItem = $this->pdo->prepare($sqlItem);

            foreach ($items as $item) {
                $stmtItem->bindValue(':order_id', $orderId);
                $stmtItem->bindValue(':ticket_id', $item['ticket_id']);
                $stmtItem->bindValue(':quantity', $item['quantity']);
                $stmtItem->bindValue(':price', $item['price']);
                $stmtItem->execute();
            }

            // Step 3: (Optional but good practice) Update ticket quantities
            // This is complex and can be added later. For now, we assume infinite tickets.

            $this->pdo->commit();
            return $orderId;

        } catch (Exception $e) {
            $this->pdo->rollBack();
            // In a real app, log the error: error_log($e->getMessage());
            return false;
        }
    }

    /**
     * Finds all orders for a given user.
     * @param int $user_id The user's ID.
     * @return array An array of Order objects.
     */
    public function findOrdersByUserId($user_id) {
        $sql = "SELECT * FROM orders WHERE user_id = :user_id ORDER BY created_at DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_CLASS, 'Order');
    }
}
