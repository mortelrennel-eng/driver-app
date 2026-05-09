<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\NotificationService;
use Illuminate\Http\Request;

// Mock logged in user (id = 1, admin)
$user = \App\Models\User::find(1);
if ($user) {
    auth()->login($user);
}

$service = app(NotificationService::class);
$notifications = $service->getGlobalNotifications();

echo "Service Global Notifications count: " . count($notifications) . "\n";

// Let's print out the first 5 titles of notifications returned
foreach (array_slice($notifications, 0, 5) as $n) {
    echo "Title: " . $n['title'] . " | Type: " . $n['type'] . "\n";
}

// Let's simulate pollNotifications request without any cookie
$request = Request::create('/web-notifications/poll', 'GET');
$controller = app(\App\Http\Controllers\Api\NotificationController::class);
$response = $controller->pollNotifications($request);

echo "\nPoll Response JSON (No Cookie):\n";
echo json_encode(json_decode($response->getContent(), true), JSON_PRETTY_PRINT) . "\n";
