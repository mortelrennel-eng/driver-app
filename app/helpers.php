<?php

if (!function_exists('formatCurrency')) {
    /**
     * Format a number as Philippine Peso currency.
     */
    function formatCurrency($amount, string $symbol = '₱'): string
    {
        if ($amount === null || $amount === '') {
            return $symbol . '0.00';
        }
        return $symbol . number_format((float) $amount, 2);
    }
}

if (!function_exists('formatDate')) {
    /**
     * Format a date string to a human-readable format.
     */
    function formatDate(?string $date, string $format = 'M d, Y'): string
    {
        if (!$date || $date === '0000-00-00') {
            return '—';
        }
        try {
            return \Carbon\Carbon::parse($date)->format($format);
        } catch (\Exception $e) {
            return $date;
        }
    }
}

if (!function_exists('formatDateTime')) {
    function formatDateTime(?string $datetime, string $format = 'M d, Y h:i A'): string
    {
        if (!$datetime) return '—';
        try {
            return \Carbon\Carbon::parse($datetime)->format($format);
        } catch (\Exception $e) {
            return $datetime;
        }
    }
}

if (!function_exists('formatPercent')) {
    function formatPercent($value, int $decimals = 1): string
    {
        return number_format((float) $value, $decimals) . '%';
    }
}

if (!function_exists('statusBadge')) {
    /**
     * Return a Tailwind CSS class string for a status badge.
     */
    function statusBadge(string $status): string
    {
        return match (strtolower($status)) {
            'active', 'completed', 'approved', 'paid'  => 'bg-green-100 text-green-800',
            'maintenance', 'in_progress', 'pending'     => 'bg-yellow-100 text-yellow-800',
            'coding', 'denied', 'cancelled'             => 'bg-red-100 text-red-800',
            'retired', 'expired'                        => 'bg-gray-100 text-gray-600',
            default                                     => 'bg-blue-100 text-blue-800',
        };
    }
}

if (!function_exists('base_url')) {
    /**
     * Get the base URL of the application (Laravel equivalent of CodeIgniter base_url).
     * 
     * @param string $path - Additional path to append to base URL
     * @param bool $secure - Force HTTPS (true) or HTTP (false). null for auto-detect
     * @return string - Complete URL
     */
    function base_url($path = '', $secure = null): string
    {
        // Get the base URL from config or use Laravel's url() helper
        $baseUrl = config('app.url') ?: url('/', $secure);
        
        // Ensure base URL ends with single slash
        $baseUrl = rtrim($baseUrl, '/') . '/';
        
        // Clean the path (remove leading slash)
        $path = ltrim($path, '/');
        
        // If path is empty, return base URL
        if (empty($path)) {
            return $baseUrl;
        }
        
// Combine base URL with path
        return $baseUrl . $path;
    }
}

/**
 * ─── Email & SMS Functions (PHPMailer & Semaphore) ───
 */

// Manual inclusion of PHPMailer Library components
require_once __DIR__ . '/Libraries/PHPMailer/Exception.php';
require_once __DIR__ . '/Libraries/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/Libraries/PHPMailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as MailerException;
use PHPMailer\PHPMailer\SMTP;

if (!function_exists('send_custom_email')) {
    /**
     * Send an email using PHPMailer (Anti-Spam & Hostinger Optimized)
     */
    function send_custom_email($to, $subject, $body, $altBody = null)
    {
        $mail = new PHPMailer(true);

        try {
            // Server settings
            $mail->SMTPDebug = SMTP::DEBUG_SERVER;
            // Redirect debug output to a file for easier troubleshooting
            $debugFile = base_path('scratch/smtp_debug.log');
            $mail->Debugoutput = function($str, $level) use ($debugFile) {
                file_put_contents($debugFile, "[" . date('Y-m-d H:i:s') . "] $str\n", FILE_APPEND);
            };

            $mail->isSMTP();
            $mail->Host = config('mail.mailers.smtp.host', 'smtp.gmail.com');
            $mail->SMTPAuth = true;
            $mail->Username = config('mail.mailers.smtp.username');
            $mail->Password = config('mail.mailers.smtp.password');
            
            $encryption = config('mail.mailers.smtp.encryption', 'tls');
            $port = (int) config('mail.mailers.smtp.port', 587);

            if ($port === 465 || $encryption === 'ssl') {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                $mail->Port = 465;
            } else {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = $port ?: 587;
            }

            // Hostinger/Shared Hosting SSL Fix
            $mail->SMTPOptions = array(
                'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                )
            );

            // Anti-Spam Headers
            $mail->CharSet = 'UTF-8';
            $mail->setFrom(config('mail.from.address', 'noreply@eurotaxisystem.site'), config('mail.from.name', 'Euro Taxi System'));
            $mail->addAddress($to);
            $mail->addReplyTo(config('mail.from.address', 'support@eurotaxisystem.site'), config('mail.from.name', 'Support'));

            // Content
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $body;
            $mail->AltBody = $altBody ?: strip_tags($body);

            // Additional headers
            $mail->addCustomHeader('X-Priority', '3');
            $mail->addCustomHeader('X-Mailer', 'EurotaxisystemPHPMailer');

            $sent = $mail->send();
            if ($sent && file_exists($debugFile)) {
                @unlink($debugFile); // Clean up on success
            }
            return $sent;
        } catch (MailerException $e) {
            \Log::error("Mail Error for {$to}: {$mail->ErrorInfo}");
            return false;
        } catch (\Exception $e) {
            \Log::error("General Mail Error: " . $e->getMessage());
            return false;
        }
    }
}

if (!function_exists('send_sms_otp')) {
    /**
     * Send SMS using Semaphore API
     *
     * @param string $phone The recipient's phone number
     * @param string $message The message body to send
     * @return bool True if successful, false otherwise
     */
    function send_sms_otp($phone, $message, $code = null)
    {
        $apiKey = config('services.semaphore.api_key');
        $senderName = config('services.semaphore.sender_name');

        if (empty($apiKey)) {
            \Log::error('Semaphore API Key is missing. Cannot send SMS.');
            return false;
        }

        try {
            // Semaphore /otp endpoint requirement: 
            // If 'message' is provided, it MUST contain '{code}' which will be replaced by the 'code' parameter.
            if ($code && !str_contains($message, '{code}')) {
                $message = str_replace($code, '{code}', $message);
                if (!str_contains($message, '{code}')) {
                    $message .= " Code: {code}";
                }
            }

            $data = [
                'apikey' => $apiKey,
                'number' => $phone,
                'message' => $message,
                'code'    => $code,
            ];

            if (!empty($senderName)) {
                $data['sendername'] = $senderName;
            }

            $response = \Illuminate\Support\Facades\Http::asForm()
                ->timeout(30)
                ->connectTimeout(10)
                ->post('https://api.semaphore.co/api/v4/otp', $data);

            if ($response->successful()) {
                \Log::info('Semaphore OTP Success Response: ' . $response->body());
                return true;
            } else {
                \Log::error('Semaphore OTP Failed: ' . $response->body() . ' | Status: ' . $response->status());
                return false;
            }
        } catch (\Exception $e) {
            \Log::error('Semaphore OTP Exception: ' . $e->getMessage());
            return false;
        }
    }
}
if (!function_exists('system_log')) {
    /**
     * Centralized system logging helper.
     */
    function system_log(string $action, string $notes = null): void
    {
        \App\Http\Controllers\ActivityLogController::log($action, $notes);
    }
}
