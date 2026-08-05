<?php

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

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
            Log::error('Semaphore API Key is missing. Cannot send SMS.');
            return false;
        }

        try {
            Log::info("Preparing to send Semaphore OTP. API Key Length: " . strlen($apiKey));
            
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

            Log::info("Semaphore OTP Payload: " . json_encode($data));

            $response = Http::asForm()
                ->timeout(30)
                ->connectTimeout(10)
                ->post('https://api.semaphore.co/api/v4/otp', $data);

            Log::info("Semaphore OTP Response Status: " . $response->status());

            if ($response->successful()) {
                Log::info('Semaphore OTP Success Response: ' . $response->body());
                return true;
            } else {
                Log::error('Semaphore OTP Failed: ' . $response->body() . ' | Status: ' . $response->status());
                return false;
            }
        } catch (\Exception $e) {
            Log::error('Semaphore OTP Exception: ' . $e->getMessage());
            return false;
        }
    }
}
