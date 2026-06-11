<?php
// Script to test logic on live DB
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$units = DB::table('units')->get();
$offlineCount = 0;
$stoppedCount = 0;
$idleCount = 0;
$movingCount = 0;

$controller = app(\App\Http\Controllers\LiveTrackingController::class);
$request = \Illuminate\Http\Request::create('/live-tracking/api/status', 'GET');
$response = $controller->getUnitsLive();
$data = json_decode($response->getContent(), true);

if (!isset($data['data'])) {
    var_dump($data);
    exit;
}

foreach($data['data']['units'] as $u) {
    if ($u['gps_status'] == 'offline') $offlineCount++;
    if ($u['gps_status'] == 'stopped') $stoppedCount++;
    if ($u['gps_status'] == 'idle') $idleCount++;
    if ($u['gps_status'] == 'moving') $movingCount++;
}

echo "API Response Counts:\n";
echo "Moving: $movingCount\n";
echo "Idle: $idleCount\n";
echo "Stopped: $stoppedCount\n";
echo "Offline: $offlineCount\n";
