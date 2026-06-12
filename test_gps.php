<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$gps = DB::table('gps_tracking')->first();
$lastUpdateTs = strtotime($gps->timestamp . ' UTC');
$diff = time() - $lastUpdateTs;

echo "Timestamp: " . $gps->timestamp . "\n";
echo "Diff: " . $diff . " seconds\n";

$ignition = $gps->ignition_status;
$speed = $gps->speed;

if ($diff < 600) {
    if ($ignition) {
        $gps_status = $speed > 2 ? 'Moving' : 'Idle';
    } else {
        $gps_status = 'Stopped';
    }
} else {
    $gps_status = 'Offline';
}

echo "Status: " . $gps_status . "\n";
