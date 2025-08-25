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
     * Processes a successful payment: updates statuses, issues tickets, and sends confirmation email.
     * @param string $reference The payment reference.
     * @return bool True on success, false on failure.
     */
    public function processSuccessfulPayment($reference) {
        // This repository needs access to other repositories, which is not ideal,
        // but acceptable for this project's structure.
        require_once __DIR__ . '/OrderRepository.php';
        require_once __DIR__ . '/UserRepository.php';
        require_once __DIR__ . '/EventRepository.php';
        require_once __DIR__ . '/../utils/PDFGenerator.php';
        require_once __DIR__ . '/../utils/EmailSender.php';
        require_once __DIR__ . '/../config/config.php';

        if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
            require_once __DIR__ . '/../vendor/autoload.php';
        }

        $orderRepo = new OrderRepository($this->pdo);
        $userRepo = new UserRepository($this->pdo);
        $eventRepo = new EventRepository($this->pdo);

        $ticketCodes = [];
        $order_id = null;

        try {
            $this->pdo->beginTransaction();

            $payment = $this->findByReference($reference);
            if (!$payment) throw new Exception("Payment not found.");

            if ($payment->status === 'success') return true; // Idempotency

            $this->updateStatus($reference, 'success');
            $orderRepo->updateOrderStatus($payment->order_id, 'paid');

            $order_id = $payment->order_id;

            $sqlItems = "SELECT * FROM order_items WHERE order_id = :order_id";
            $stmtItems = $this->pdo->prepare($sqlItems);
            $stmtItems->bindValue(':order_id', $order_id);
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
                    $ticketCodes[] = $ticketCode;
                }
            }

            $this->pdo->commit();

        } catch (Exception $e) {
            $this->pdo->rollBack();
            error_log("Failed to process successful payment transaction: " . $e->getMessage());
            return false;
        }

        // --- Post-Transaction Actions (Email) ---
        try {
            $order = $orderRepo->findOrderById($order_id);
            $user = $userRepo->findUserById($order->user_id);
            $event = $eventRepo->findEventById($order->event_id);

            $attachmentPaths = [];
            foreach ($ticketCodes as $code) {
                $pdfPath = PDFGenerator::generateTicketPDF($code);
                if ($pdfPath) $attachmentPaths[] = $pdfPath;
            }

            $emailSender = new EmailSender();
            $subject = "Your Tickets for " . $event->title;
            $body = "<h1>Thank you for your purchase!</h1>
                     <p>Your tickets for the event '" . htmlspecialchars($event->title) . "' are attached to this email.</p>
                     <p>You can also view them in your dashboard.</p>";
            $emailSender->send($user->email, $subject, $body, $attachmentPaths);

            foreach ($attachmentPaths as $path) {
                if (file_exists($path)) unlink($path);
            }
        } catch (Exception $e) {
            error_log("Payment processed, but failed to send ticket confirmation email for order ID $order_id: " . $e->getMessage());
        }

        return true;
    }
}
