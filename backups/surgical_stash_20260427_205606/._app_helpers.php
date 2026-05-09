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
     * Send an email using PHPMailer (Anti-Spam Configuration)
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
            $mail->SMTPDebug = SMTP::DEBUG_OFF;
            $mail->isSMTP();
            $mail->Host = env('MAIL_HOST', 'smtp.gmail.com');
            $mail->SMTPAuth = true;
            $mail->Username = env('MAIL_USERNAME');
            $mail->Password = env('MAIL_PASSWORD');
            $mail->SMTPSecure = env('MAIL_ENCRYPTION', PHPMailer::ENCRYPTION_STARTTLS);
            $mail->Port = env('MAIL_PORT', 587);

            // Anti-Spam Headers
            $mail->CharSet = 'UTF-8';
            $mail->setFrom(env('MAIL_FROM_ADDRESS', 'noreply@eurotaxisystem.site'), env('MAIL_FROM_NAME', 'Eurotaxisystem'));
            $mail->addAddress($to);
            $mail->addReplyTo(env('MAIL_FROM_ADDRESS', 'support@eurotaxisystem.site'), env('MAIL_FROM_NAME', 'Support'));

            // Content
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $body;
            $mail->AltBody = $altBody ?: strip_tags($body);

            // Additional headers to avoid spam filters
            $mail->addCustomHeader('X-Priority', '3');
            $mail->addCustomHeader('X-Mailer', 'EurotaxisystemPHPMailer');

            return $mail->send();
        } catch (MailerException $e) {
            \Log::error("Mail Error: {$mail->ErrorInfo}");
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
            $data = [
                'apikey' => $apiKey,
                'number' => $phone,
                'message' => $message,
            ];

            if ($code) {
                $data['code'] = $code;
            }

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
