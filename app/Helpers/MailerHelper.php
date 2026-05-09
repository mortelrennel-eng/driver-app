<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

// Manual inclusion because it's not in the vendor/autoload for some reason
require_once __DIR__ . '/../Libraries/PHPMailer/Exception.php';
require_once __DIR__ . '/../Libraries/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/../Libraries/PHPMailer/SMTP.php';

if (!function_exists('send_custom_email')) {
    /**
     * Send an email using PHPMailer (Optimized for Hostinger/Gmail)
     *
     * @param string $to Recipient email
     * @param string $subject Email subject
     * @param string $body Email content (HTML)
     * @param string|null $altBody Plain text version of the body
     * @return bool True if sent, false otherwise
     */
    function send_custom_email($to, $subject, $body, $altBody = null)
    {
        $mail = new PHPMailer(true);

        try {
            // Server settings
            $mail->SMTPDebug = SMTP::DEBUG_SERVER;
            // Redirect debug output to a file for easier troubleshooting without huge logs
            $debugFile = base_path('scratch/smtp_debug.log');
            $mail->Debugoutput = function($str, $level) use ($debugFile) {
                file_put_contents($debugFile, "[" . date('Y-m-d H:i:s') . "] $str\n", FILE_APPEND);
            };

            $mail->isSMTP();
            
            // Use config values, fallback to Hostinger if not set
            $host = config('mail.mailers.smtp.host', env('MAIL_HOST', 'smtp.hostinger.com'));
            $user = config('mail.mailers.smtp.username', env('MAIL_USERNAME'));
            $pass = config('mail.mailers.smtp.password', env('MAIL_PASSWORD'));
            $encryption = config('mail.mailers.smtp.encryption', env('MAIL_ENCRYPTION', 'ssl'));
            $port = (int) config('mail.mailers.smtp.port', env('MAIL_PORT', 465));

            $mail->Host = $host;
            $mail->SMTPAuth = true;
            $mail->Username = $user;
            $mail->Password = $pass;
            
            // Hostinger Optimization: Use SMTPS (465) if possible as it's less likely to be blocked
            if ($port === 465 || strtolower($encryption) === 'ssl') {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                $mail->Port = 465;
            } else {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = $port ?: 587;
            }
            
            // Shared Hosting Certificate Fix
            $mail->SMTPOptions = array(
                'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                )
            );

            // Anti-Spam Headers
            $mail->CharSet = 'UTF-8';
            $fromAddr = config('mail.from.address', env('MAIL_FROM_ADDRESS', 'noreply@eurotaxisystem.site'));
            $fromName = config('mail.from.name', env('MAIL_FROM_NAME', 'Euro Taxi System'));
            
            $mail->setFrom($fromAddr, $fromName);
            $mail->addAddress($to);
            $mail->addReplyTo($fromAddr, 'Support');

            // Content
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $body;
            $mail->AltBody = $altBody ?: strip_tags($body);

            // Priority and Identification
            $mail->addCustomHeader('X-Priority', '3');
            $mail->addCustomHeader('X-Mailer', 'EuroTaxiSystem-PHPMailer');

            \Log::info("Attempting SMTP send to: {$to} via {$mail->Host}:{$mail->Port}");

            $sent = $mail->send();
            
            if ($sent) {
                \Log::info("Email successfully sent to: {$to}");
                // Clear debug log on success to save space
                if (file_exists($debugFile)) @unlink($debugFile);
            }
            return $sent;
            
        } catch (Exception $e) {
            \Log::error("PHPMailer Exception for {$to}: " . $e->getMessage());
            \Log::error("SMTP Error Info: " . $mail->ErrorInfo);
            return false;
        } catch (\Throwable $t) {
            \Log::error("Fatal Mailer Error for {$to}: " . $t->getMessage());
            return false;
        }
    }
}
