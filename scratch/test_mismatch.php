<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$controller = new \App\Http\Controllers\LiveTrackingController();
$request = \Illuminate\Http\Request::create('/live-tracking/units-live', 'GET');
$response = $controller->getUnitsLive($request);
$data = json_decode($response->getContent(), true);

$idle_units = [];
$offline_units = [];

if (isset($data['units'])) {
    foreach ($data['units'] as $unit) {
        if ($unit['gps_status'] == 'idle' || $unit['gps_status'] == 'stopped') {
            $idle_units[] = $unit['plate_number'] . ' (Diff: ' . (time() - strtotime($unit['last_update'] . ' UTC')) . ')';
        } else if ($unit['gps_status'] == 'offline') {
            $offline_units[] = $unit['plate_number'];
        }
    }
}

echo "IDLE / STOPPED UNITS in WEB:\n";
print_r($idle_units);

echo "\nOFFLINE UNITS in WEB:\n";
print_r(count($offline_units) . ' units');

// Also test the Driver App endpoint directly for comparison
$driverController = new \App\Http\Controllers\Api\DriverAppController(
    new \App\Services\TracksolidService()
);
// Mock the API Request for Driver App using Almar's unit
$unit = \Illuminate\Support\Facades\DB::table('units')->where('plate_number', 'EUV-389')->first();
if ($unit) {
    echo "\n\nTesting DriverAppController performance for EUV-389 (Almar):\n";
    $driverRequest = \Illuminate\Http\Request::create('/api/driver/performance', 'GET', ['unit_id' => $unit->id]);
    $perfResponse = $driverController->performance($driverRequest);
    $perfData = json_decode($perfResponse->getContent(), true);
    if (isset($perfData['success']) && $perfData['success']) {
        echo "Driver App Status: " . $perfData['data']['gps_status'] . "\n";
    }
}
