<?php
// Simple email helper function
// Works with or without PHPMailer

function sendEmail($to, $subject, $message, $replyTo = null) {
    $config = require __DIR__ . '/../config/email_config.php';
    
    // Try PHPMailer if available and SMTP is enabled
    if ($config['use_smtp'] && file_exists(__DIR__ . '/../vendor/autoload.php')) {
        require_once __DIR__ . '/../vendor/autoload.php';
        
        try {
            $mail = new PHPMailer\PHPMailer\PHPMailer(true);
            
            // SMTP Configuration
            $mail->isSMTP();
            $mail->Host = $config['smtp_host'];
            $mail->SMTPAuth = true;
            $mail->Username = $config['smtp_username'];
            $mail->Password = $config['smtp_password'];
            $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = $config['smtp_port'];
            $mail->CharSet = 'UTF-8';
            
            // Email settings
            $mail->setFrom($config['smtp_from_email'], $config['smtp_from_name']);
            $mail->addAddress($to);
            if ($replyTo) {
                $mail->addReplyTo($replyTo);
            }
            $mail->isHTML(false);
            $mail->Subject = $subject;
            $mail->Body = $message;
            
            $result = $mail->send();
            if (!$result) {
                error_log("PHPMailer failed: " . $mail->ErrorInfo);
            }
            return $result;
        } catch (Exception $e) {
            error_log("PHPMailer exception: " . $e->getMessage());
            // Fall back to mail() if PHPMailer fails
        }
    }
    
    // Fallback to PHP mail() function
    $headers = "From: {$config['smtp_from_email']}\r\n";
    $headers .= "Reply-To: " . ($replyTo ?: $config['smtp_from_email']) . "\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion();
    
    $result = @mail($to, $subject, $message, $headers);
    if (!$result) {
        error_log("PHP mail() failed for: $to");
    }
    return $result;
}

