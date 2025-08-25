<?php

// Assumes Composer's autoloader is included.
use Dompdf\Dompdf;
use Dompdf\Options;

class PDFGenerator {

    /**
     * Generates a ticket PDF and saves it to a temporary file.
     *
     * @param string $ticket_code The unique code for the ticket.
     * @return string|false The absolute file path to the generated PDF, or false on failure.
     */
    public static function generateTicketPDF(string $ticket_code) {
        // This utility needs a PDO instance. It's better to pass it in.
        // For now, we'll follow the project's pattern of including the database file.
        require __DIR__ . '/../config/database.php';
        require_once __DIR__ . '/QRGenerator.php';

        // 1. Fetch all ticket details with a single query
        $sql = "SELECT
                    it.ticket_code, it.status AS ticket_status,
                    u.name AS user_name, u.email AS user_email, o.user_id,
                    e.title AS event_title, e.date AS event_date, e.location AS event_location,
                    t.name AS ticket_type
                FROM issued_tickets AS it
                JOIN order_items AS oi ON it.order_item_id = oi.id
                JOIN tickets AS t ON oi.ticket_id = t.id
                JOIN orders AS o ON oi.order_id = o.id
                JOIN events AS e ON o.event_id = e.id
                JOIN users AS u ON o.user_id = u.id
                WHERE it.ticket_code = :ticket_code";

        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':ticket_code', $ticket_code);
        $stmt->execute();
        $ticket_data = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$ticket_data) {
            return false; // Ticket not found
        }

        // 2. Generate QR Code
        try {
            $qr_code_path_relative = QRGenerator::generate($ticket_data['ticket_code'], $ticket_data['ticket_code']);
            $qr_code_path_absolute = __DIR__ . '/../' . $qr_code_path_relative;
        } catch (Exception $e) {
            error_log("Could not generate QR code for PDF: " . $e->getMessage());
            return false;
        }

        // 3. Generate PDF
        $options = new Options();
        $options->set('isRemoteEnabled', true); // Allows loading images, etc.
        $dompdf = new Dompdf($options);

        // Start output buffering to capture HTML template
        ob_start();
        include __DIR__ . '/../includes/ticket_template.php';
        $html = ob_get_clean();

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        // 4. Save PDF to a temporary file
        $temp_dir = __DIR__ . '/../upload/temp_tickets/';
        $pdf_filepath = $temp_dir . 'ticket-' . $ticket_data['ticket_code'] . '.pdf';

        file_put_contents($pdf_filepath, $dompdf->output());

        // Clean up the generated QR code image as it's now embedded in the PDF
        if (file_exists($qr_code_path_absolute)) {
            unlink($qr_code_path_absolute);
        }

        return $pdf_filepath;
    }
}
