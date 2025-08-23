<?php

require_once __DIR__ . '/../classes/Order.php';

class OrderRepository {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Creates a new order, its items, and a payment record in a transaction.
     * @param Order $order The order object.
     * @param array $items An array of items, each with 'ticket_id', 'quantity', and 'price'.
     * @return string|false The new payment reference on success, false on failure.
     */
    public function createOrderWithItems(Order $order, array $items) {
        $paymentReference = 'ref_' . uniqid() . bin2hex(random_bytes(4)); // Generate a more unique reference

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

            // Step 3: Create the payment record
            $sqlPayment = "INSERT INTO payments (order_id, reference, method, status)
                           VALUES (:order_id, :reference, :method, :status)";
            $stmtPayment = $this->pdo->prepare($sqlPayment);
            $stmtPayment->bindValue(':order_id', $orderId);
            $stmtPayment->bindValue(':reference', $paymentReference);
            $stmtPayment->bindValue(':method', 'paystack'); // Default method
            $stmtPayment->bindValue(':status', 'pending'); // Initial status
            $stmtPayment->execute();

            $this->pdo->commit();
            return $paymentReference;

        } catch (Exception $e) {
            $this->pdo->rollBack();
            error_log($e->getMessage()); // Log the actual error
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

    /**
     * Finds an order by its ID.
     * @param int $order_id The order ID.
     * @return Order|false The Order object if found, false otherwise.
     */
    public function findOrderById($order_id) {
        $sql = "SELECT * FROM orders WHERE id = :order_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':order_id', $order_id, PDO::PARAM_INT);
        $stmt->execute();
        $stmt->setFetchMode(PDO::FETCH_CLASS, 'Order');
        return $stmt->fetch();
    }

    /**
     * Updates the status of an order.
     * @param int $order_id The order ID.
     * @param string $status The new status.
     * @return bool True on success, false on failure.
     */
    public function updateOrderStatus($order_id, $status) {
        $sql = "UPDATE orders SET status = :status WHERE id = :order_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':status', $status);
        $stmt->bindValue(':order_id', $order_id);
        return $stmt->execute();
    }
}
