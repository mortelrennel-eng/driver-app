<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$request = Illuminate\Http\Request::capture();
$kernel->handle($request);

$notifiedCount = \App\Services\NotificationService::dispatchDailyCodingNotifications();
echo "Triggered daily coding notifications. Sent to: " . $notifiedCount . " devices.";
