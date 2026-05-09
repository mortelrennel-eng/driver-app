<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class FirebasePushService
{
    /**
     * Get OAuth2 Access Token from Google OAuth2 Server using pure PHP JWT.
     */
    private static function getAccessToken()
    {
        return Cache::remember('firebase_fcm_access_token', 55, function () {
            $keyPath = storage_path('app/firebase/firebase-credentials.json');
            
            if (!file_exists($keyPath)) {
                Log::error('Firebase credentials file not found at: ' . $keyPath);
                return null;
            }

            try {
                $credentials = json_decode(file_get_contents($keyPath), true);
                if (!$credentials || !isset($credentials['private_key'], $credentials['client_email'])) {
                    Log::error('Invalid Firebase credentials JSON format.');
                    return null;
                }

                $privateKey = $credentials['private_key'];
                $clientEmail = $credentials['client_email'];

                // Build JWT Header & Payload
                $header = json_encode(['alg' => 'RS256', 'typ' => 'JWT']);
                $now = time();
                $payload = json_encode([
                    'iss' => $clientEmail,
                    'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
                    'aud' => 'https://oauth2.googleapis.com/token',
                    'iat' => $now,
                    'exp' => $now + 3600
                ]);

                // Base64Url Encode
                $base64UrlHeader = self::base64UrlEncode($header);
                $base64UrlPayload = self::base64UrlEncode($payload);

                // Sign JWT using OpenSSL RS256
                $signature = '';
                $signatureResult = openssl_sign(
                    $base64UrlHeader . "." . $base64UrlPayload,
                    $signature,
                    $privateKey,
                    OPENSSL_ALGO_SHA256
                );

                if (!$signatureResult) {
                    Log::error('Failed to sign JWT with private key.');
                    return null;
                }

                $base64UrlSignature = self::base64UrlEncode($signature);
                $jwt = $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;

                // Request OAuth2 Token
                $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
                    'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                    'assertion' => $jwt
                ]);

                if ($response->failed()) {
                    Log::error('Google OAuth2 exchange failed: ' . $response->body());
                    return null;
                }

                $data = $response->json();
                return $data['access_token'] ?? null;

            } catch (\Exception $e) {
                Log::error('Firebase Access Token retrieval error: ' . $e->getMessage());
                return null;
            }
        });
    }

    /**
     * Helper to perform Base64Url encoding.
     */
    private static function base64UrlEncode($data)
    {
        return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($data));
    }

    /**
     * Send Push Notification using Firebase FCM HTTP v1 API.
     */
    public static function sendPush($title, $body, $fcmToken, $type = 'system_alert')
    {
        $accessToken = self::getAccessToken();
        if (!$accessToken) {
            Log::warning('FCM HTTP v1 skipped: Could not retrieve OAuth2 Access Token.');
            return false;
        }

        $projectId = 'eurotaxi-4c240';
        $url = "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send";

        $payload = [
            'message' => [
                'token' => $fcmToken,
                'notification' => [
                    'title' => $title,
                    'body' => $body,
                ],
                'data' => [
                    'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                    'type' => $type,
                    'screen' => 'notifications',
                    'sent_at' => now()->toIso8601String(),
                ],
                'android' => [
                    'priority' => 'HIGH',
                    'notification' => [
                        'sound' => 'default',
                        'default_sound' => true,
                        'notification_priority' => 'PRIORITY_HIGH',
                    ]
                ],
                'apns' => [
                    'payload' => [
                        'aps' => [
                            'sound' => 'default',
                            'badge' => 1,
                        ]
                    ]
                ]
            ]
        ];

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type' => 'application/json',
            ])->post($url, $payload);

            if ($response->failed()) {
                Log::error('FCM HTTP v1 Push failed: ' . $response->body());
                return false;
            }

            return true;
        } catch (\Exception $e) {
            Log::error('FCM HTTP v1 Error: ' . $e->getMessage());
            return false;
        }
    }
}
