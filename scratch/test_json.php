<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$user = \App\Models\User::where('role', 'driver')->first();
$driver = \DB::table('drivers')->where('user_id', $user->id)->first();
$service = new \App\Services\NotificationService();
$notifications = $service->getDriverFeed($user->id);

$formatted = collect($notifications)->map(function ($notif) {
    $severity = 'info';
    $icon = 'bell-outline';
    $type = $notif['type'] ?? 'notice';
    return [
        'id' => $notif['id'] ?? uniqid('notif_'),
        'type' => $type,
        'title' => $notif['title'] ?? 'System Alert',
        'message' => $notif['message'] ?? '',
        'time_display' => $notif['time'] ?? 'Now',
        'timestamp' => isset($notif['timestamp']) ? \Carbon\Carbon::parse($notif['timestamp'])->toIso8601String() : now()->toIso8601String(),
        'icon' => $icon,
        'severity' => $severity,
        'url' => $notif['url'] ?? '#'
    ];
});

echo json_encode([
    'success' => true,
    'count' => $formatted->count(),
    'notifications' => $formatted->values()->all()
], JSON_PRETTY_PRINT);
