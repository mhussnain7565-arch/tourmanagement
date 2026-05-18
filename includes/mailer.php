<?php
class Mailer {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function send($template_key, $recipient_email, $data = [], $user_id = null) {
        // 1. Fetch Template
        $stmt = $this->pdo->prepare("SELECT * FROM email_templates WHERE template_key = ?");
        $stmt->execute([$template_key]);
        $template = $stmt->fetch();

        if (!$template) return false;

        $subject = $template['subject'];
        $body = $template['body'];

        // 2. Replace Variables
        foreach ($data as $key => $value) {
            $subject = str_replace('{{' . $key . '}}', $value, $subject);
            $body = str_replace('{{' . $key . '}}', $value, $body);
        }

        // 3. Real Email Send (using PHP mail function)
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= 'From: <no-reply@tourmanagement.com>' . "\r\n";

        $status = 'Sent';
        $error = null;

        // Use @ to suppress local server warnings (XAMPP usually lacks local mailserver)
        if (!@mail($recipient_email, $subject, $body, $headers)) {
            $status = 'Failed';
            $error = 'Failed to connect to mailserver. (XAMPP users: Configure SMTP in php.ini or use a real server)';
        }

        // 4. Log the Notification
        $logStmt = $this->pdo->prepare("
            INSERT INTO notification_logs (user_id, template_key, recipient_email, subject, body, status, error_message) 
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $logStmt->execute([$user_id, $template_key, $recipient_email, $subject, $body, $status, $error]);

        return true;
    }
}
?>
