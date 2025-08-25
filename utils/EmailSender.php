<?php

// Assumes Composer's autoloader is included.
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class EmailSender {
    private $mailer;

    public function __construct() {
        // Assumes config/config.php is included where this class is instantiated.
        $this->mailer = new PHPMailer(true); // Enable exceptions

        // Server settings
        $this->mailer->isSMTP();
        $this->mailer->Host       = SMTP_HOST;
        $this->mailer->SMTPAuth   = true;
        $this->mailer->Username   = SMTP_USERNAME;
        $this->mailer->Password   = SMTP_PASSWORD;
        $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $this->mailer->Port       = SMTP_PORT;

        // Sender
        $this->mailer->setFrom(SMTP_USERNAME, APP_NAME);
    }

    /**
     * Sends an email.
     *
     * @param string $to The recipient's email address.
     * @param string $subject The email subject.
     * @param string $body The HTML body of the email.
     * @param array $attachments An array of file paths to attach.
     * @return bool True on success, false on failure.
     */
    public function send(string $to, string $subject, string $body, array $attachments = []): bool {
        try {
            // Recipient
            $this->mailer->addAddress($to);

            // Content
            $this->mailer->isHTML(true);
            $this->mailer->Subject = $subject;
            $this->mailer->Body    = $body;
            $this->mailer->AltBody = strip_tags($body); // Plain text version

            // Attachments
            foreach ($attachments as $attachment) {
                if (file_exists($attachment)) {
                    $this->mailer->addAttachment($attachment);
                }
            }

            $this->mailer->send();
            return true;
        } catch (Exception $e) {
            // Log the error in a real application
            error_log("Message could not be sent. Mailer Error: {$this->mailer->ErrorInfo}");
            return false;
        }
    }
}
