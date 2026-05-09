<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FirebaseHelper
{
    /**
     * Send a push notification to a specific user via FCM Legacy API.
     */
    public static function sendPushNotification($fcmToken, $title, $body, $data = [])
    {
        if (!$fcmToken) {
            return false;
        }

        $serverKey = config('services.firebase.server_key');

        if (!$serverKey) {
            Log::warning('Firebase Server Key not found in config/services.php. Please add FIREBASE_SERVER_KEY to your .env');
            return false;
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'key=' . $serverKey,
                'Content-Type' => 'application/json',
            ])->post('https://fcm.googleapis.com/fcm/send', [
                'to' => $fcmToken,
                'notification' => [
                    'title' => $title,
                    'body' => $body,
                    'sound' => 'default',
                ],
                'data' => array_merge($data, [
                    'click_action' => 'FLUTTER_NOTIFICATION_CLICK', // For common compatibility
                    'id' => '1',
                    'status' => 'done',
                ]),
                'priority' => 'high',
            ]);

            if (!$response->successful()) {
                Log::error('Firebase Response Error', ['body' => $response->body()]);
            }

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('Firebase Notification Exception: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send notification to a specific User model.
     */
    public static function notifyUser($user, $title, $body, $data = [])
    {
        if ($user && $user->fcm_token) {
            return self::sendPushNotification($user->fcm_token, $title, $body, $data);
        }
        return false;
    }
}
