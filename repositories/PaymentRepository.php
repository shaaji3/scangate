<?php

require_once __DIR__ . '/../classes/Payment.php';

class PaymentRepository {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Creates a new payment record.
     * @param Payment $payment The payment object to save.
     * @return bool True on success, false on failure.
     */
    public function createPayment(Payment $payment) {
        $sql = "INSERT INTO payments (order_id, reference, method, status)
                VALUES (:order_id, :reference, :method, :status)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':order_id', $payment->order_id);
        $stmt->bindValue(':reference', $payment->reference);
        $stmt->bindValue(':method', $payment->method);
        $stmt->bindValue(':status', $payment->status);
        return $stmt->execute();
    }

    /**
     * Finds a payment by its reference.
     * @param string $reference The payment reference.
     * @return Payment|false
     */
    public function findByReference($reference) {
        $sql = "SELECT * FROM payments WHERE reference = :reference";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':reference', $reference);
        $stmt->execute();
        $stmt->setFetchMode(PDO::FETCH_CLASS, 'Payment');
        return $stmt->fetch();
    }

    /**
     * Updates the status of a payment.
     * @param string $reference The payment reference.
     * @param string $status The new status.
     * @return bool True on success, false on failure.
     */
    public function updateStatus($reference, $status) {
        $sql = "UPDATE payments SET status = :status WHERE reference = :reference";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':status', $status);
        $stmt->bindValue(':reference', $reference);
        return $stmt->execute();
    }

    /**
     * Processes a successful payment by updating payment and order statuses in a transaction.
     * @param string $reference The payment reference.
     * @return bool True on success, false on failure.
     */
    public function processSuccessfulPayment($reference) {
        // This repository needs access to OrderRepository, which is not ideal.
        // For this project, we'll require it here.
        require_once __DIR__ . '/OrderRepository.php';

        try {
            $this->pdo->beginTransaction();

            // 1. Find the payment to get the order ID
            $payment = $this->findByReference($reference);
            if (!$payment) {
                throw new Exception("Payment with reference $reference not found.");
            }

            // Idempotency Check: If already processed, don't do it again.
            if ($payment->status === 'success') {
                return true;
            }

            // 2. Update payment status to 'success'
            $this->updateStatus($reference, 'success');

            // 3. Update order status to 'paid'
            $orderRepo = new OrderRepository($this->pdo);
            $orderRepo->updateOrderStatus($payment->order_id, 'paid');

            // 4. Create the individual issued_tickets records
            $sqlItems = "SELECT * FROM order_items WHERE order_id = :order_id";
            $stmtItems = $this->pdo->prepare($sqlItems);
            $stmtItems->bindValue(':order_id', $payment->order_id);
            $stmtItems->execute();
            $orderItems = $stmtItems->fetchAll(PDO::FETCH_ASSOC);

            $sqlIssue = "INSERT INTO issued_tickets (order_item_id, ticket_code) VALUES (:order_item_id, :ticket_code)";
            $stmtIssue = $this->pdo->prepare($sqlIssue);

            foreach ($orderItems as $item) {
                for ($i = 0; $i < $item['quantity']; $i++) {
                    $ticketCode = 'TKT-' . strtoupper(bin2hex(random_bytes(8)));
                    $stmtIssue->bindValue(':order_item_id', $item['id']);
                    $stmtIssue->bindValue(':ticket_code', $ticketCode);
                    $stmtIssue->execute();
                }
            }

            $this->pdo->commit();
            return true;

        } catch (Exception $e) {
            $this->pdo->rollBack();
            error_log("Failed to process successful payment: " . $e->getMessage());
            return false;
        }
    }
}
