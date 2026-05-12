<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\NotificationService;
use Carbon\Carbon;

class NotificationController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Get all active alerts and notifications formatted for mobile.
     */
    public function index(Request $request)
    {
        try {
            $user = auth()->user();
            
            // Check if user is a driver to provide personalized feed
            $driver = DB::table('drivers')->where('user_id', $user->id)->first();
            
            if ($driver) {
                $notifications = $this->notificationService->getDriverFeed($user->id);
            } else {
                $notifications = $this->notificationService->getGlobalNotifications();
            }

            // Structure data specifically for easy rendering on mobile list view with nice badges
            $formatted = collect($notifications)->map(function ($notif) {
                // Use predefined icons/severity if they exist (from getDriverFeed), otherwise set defaults
                $severity = $notif['severity'] ?? 'info';
                $icon = $notif['icon'] ?? 'bell-outline';

                $type = $notif['type'] ?? 'notice';
                switch ($type) {
                    case 'at_risk':
                        $severity = 'danger';
                        $icon = 'alert-octagon-outline';
                        break;
                    case 'violation_alert':
                        $severity = 'warning';
                        $icon = 'alert-circle-outline';
                        break;
                    case 'case_expiry':
                        $severity = 'danger';
                        $icon = 'calendar-alert';
                        break;
                    case 'maintenance_today':
                    case 'odo_maint_due':
                        $severity = 'warning';
                        $icon = 'wrench-outline';
                        break;
                    case 'low_stock':
                        $severity = 'danger';
                        $icon = 'package-variant-alert';
                        break;
                    case 'driver_incident':
                        $severity = 'warning';
                        $icon = 'car-off';
                        break;
                    case 'license_expiry':
                        $severity = 'danger';
                        $icon = 'card-account-details-outline';
                        break;
                }

                return [
                    'id' => $notif['id'] ?? uniqid('notif_'),
                    'type' => $type,
                    'title' => $notif['title'] ?? 'System Alert',
                    'message' => $notif['message'] ?? '',
                    'time_display' => $notif['time_display'] ?? 'Now',
                    'timestamp' => isset($notif['timestamp']) ? Carbon::parse($notif['timestamp'])->toIso8601String() : now()->toIso8601String(),
                    'icon' => $icon,
                    'severity' => $severity,
                    'url' => $notif['url'] ?? '#'
                ];
            });

            return response()->json([
                'success' => true,
                'count' => $formatted->count(),
                'notifications' => $formatted
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching notifications: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Dismiss a system alert notification.
     */
    public function dismiss(Request $request)
    {
        $id = $request->input('id');
        
        if (!$id) {
            return response()->json([
                'success' => false,
                'message' => 'Notification ID is required.'
            ], 400);
        }

        try {
            // Check if it's a DB-based system alert (has numeric or distinct system_alerts key)
            if (is_numeric($id)) {
                $alert = DB::table('system_alerts')->where('id', $id)->first();
                if ($alert) {
                    DB::table('system_alerts')->where('id', $id)->update([
                        'is_resolved' => true,
                        'resolved_at' => now(),
                        'resolved_by' => auth()->id(),
                    ]);

                    \App\Http\Controllers\ActivityLogController::log(
                        'Dismissed Alert (API)', 
                        "Alert: " . ($alert->title ?? 'Unknown') . "\nMarked as resolved by API user: " . auth()->user()->name
                    );
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Notification marked as read/dismissed.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to dismiss notification: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Simulate or Trigger an FCM Push Notification Payload.
     * This represents the exact payload sent to Google/Apple Servers
     * to wake up the mobile app even when it is fully closed!
     */
    public function simulatePushNotification(Request $request)
    {
        $request->validate([
            'title' => 'required|string',
            'body' => 'required|string',
            'type' => 'nullable|string',
        ]);

        $title = $request->input('title');
        $body = $request->input('body');
        $type = $request->input('type', 'system_alert');

        $pushedCount = 0;
        $failedCount = 0;

        // Get all registered FCM tokens in database
        $tokens = DB::table('users')
            ->whereNotNull('fcm_token')
            ->where('fcm_token', '!=', '')
            ->pluck('fcm_token')
            ->unique();

        if ($tokens->isNotEmpty()) {
            foreach ($tokens as $token) {
                $success = \App\Services\FirebasePushService::sendPush($title, $body, $token, $type);
                if ($success) {
                    $pushedCount++;
                } else {
                    $failedCount++;
                }
            }
        }

        $credentialsExist = file_exists(storage_path('app/firebase/firebase-credentials.json'));

        return response()->json([
            'success' => true,
            'message' => 'FCM HTTP v1 push dispatch process completed.',
            'firebase_credentials_file_active' => $credentialsExist,
            'real_pushed_devices_count' => $pushedCount,
            'failed_devices_count' => $failedCount,
            'info' => 'Dispatched live background pushes using Google Firebase HTTP v1 OAuth2 protocol.'
        ]);
    }

    /**
     * Manually trigger coding alerts for today's restricted vehicles.
     */
    public function triggerDailyCodingAlerts(Request $request)
    {
        // Simple security key for cron access
        if ($request->get('key') !== 'eurotaxi_secret_cron_2026') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $count = $this->notificationService->dispatchDailyCodingNotifications();
        return response()->json([
            'success' => true,
            'message' => "Dispatched coding alerts to {$count} drivers.",
        ]);
    }

    /**
     * Poll the latest unread notifications for the web layout.
     */
    public function pollNotifications(Request $request)
    {
        try {
            $notifications = $this->notificationService->getGlobalNotifications();

            // Filter out read notifications using cookie
            $readNotifIds = [];
            $cookieData = $request->cookie('read_notifs');
            if ($cookieData) {
                try {
                    $decodedVal = stripslashes($cookieData);
                    $readData = json_decode($decodedVal, true);
                    if (!$readData) {
                        $readData = json_decode($cookieData, true);
                    }
                    
                    if (is_array($readData) && array_is_list($readData)) {
                        $readNotifIds = array_map('strval', $readData);
                    } elseif (is_array($readData)) {
                        $nowMs = time() * 1000;
                        foreach ($readData as $id => $timestamp) {
                            if ($nowMs - $timestamp < 1800000) {
                                $readNotifIds[] = (string)$id;
                            }
                        }
                    }
                } catch (\Exception $ex) {}
            }

            $activeNotifs = [];
            foreach ($notifications as $n) {
                $notifId = isset($n['id']) ? (string)$n['id'] : md5(($n['title'] ?? '') . ($n['message'] ?? ''));
                if (!in_array($notifId, $readNotifIds)) {
                    $activeNotifs[] = [
                        'id' => $notifId,
                        'type' => $n['type'] ?? 'system_alert',
                        'title' => $n['title'] ?? 'System Alert',
                        'message' => $n['message'] ?? '',
                        'url' => $n['url'] ?? '#',
                        'time' => $n['time'] ?? 'Now',
                        'timestamp' => isset($n['timestamp']) ? (is_object($n['timestamp']) ? $n['timestamp']->toIso8601String() : $n['timestamp']) : null
                    ];
                }
            }

            $partsCount = collect($activeNotifs)->where('type', 'low_stock')->count();
            $totalCount = count($activeNotifs);
            $systemCount = $totalCount - $partsCount;

            return response()->json([
                'success' => true,
                'total' => $totalCount,
                'system_count' => $systemCount,
                'parts_count' => $partsCount,
                'notifications' => $activeNotifs
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Polling failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Save the user's FCM token for real background push notifications.
     */
    public function saveToken(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
        ]);

        try {
            $user = auth()->user();
            
            if ($user) {
                // Try updating with Eloquent or fallback to direct DB if migration has delay
                try {
                    $user->update(['fcm_token' => $request->input('token')]);
                } catch (\Exception $ex) {
                    DB::table('users')->where('id', $user->id)->update([
                        'fcm_token' => $request->input('token')
                    ]);
                }
            } else {
                // Store in session for guests, to be associated upon login/registration
                session(['fcm_token' => $request->input('token')]);
            }

            return response()->json([
                'success' => true,
                'message' => $user ? 'FCM Device Token saved successfully.' : 'FCM Device Token stored in session.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to save FCM token: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Log diagnostics from the mobile app's WebView to detect any Native Bridge failures.
     */
    public function logDiagnostics(Request $request)
    {
        try {
            $message = $request->input('message', 'No message');
            $data = $request->input('data', []);
            $userId = $request->input('user_id', 'guest');
            
            $logLine = sprintf(
                "[%s] [User:%s] [IP:%s] %s | %s\n",
                now()->toIso8601String(),
                $userId,
                $request->ip(),
                $message,
                json_encode($data)
            );
            
            // Ensure log directory exists
            if (!file_exists(storage_path('logs'))) {
                mkdir(storage_path('logs'), 0755, true);
            }
            
            file_put_contents(storage_path('logs/capacitor_diagnostics.log'), $logLine, FILE_APPEND);
            
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
