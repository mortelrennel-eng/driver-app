<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== NOTIFICATIONS DB CHECK ===\n";
echo 'Total System Alerts: ' . \DB::table('system_alerts')->count() . "\n";
echo 'Unresolved System Alerts: ' . \DB::table('system_alerts')->where('is_resolved', false)->count() . "\n";
echo 'Total Support Messages: ' . \DB::table('support_messages')->count() . "\n";
echo 'Total Boundaries: ' . \DB::table('boundaries')->count() . "\n";
echo 'Total Driver Behaviors: ' . \DB::table('driver_behavior')->count() . "\n";
