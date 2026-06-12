<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
use Illuminate\Support\Facades\DB;

$gps = DB::table('gps_tracking')->orderBy('id', 'desc')->first();

if ($gps) {
    echo "GPS Record Found:\n";
    echo "Timestamp: " . $gps->timestamp . "\n";
    $lastUpdateTs = strtotime($gps->timestamp . ' UTC');
    $diff = time() - $lastUpdateTs;
    echo "Time(): " . time() . "\n";
    echo "lastUpdateTs: " . $lastUpdateTs . "\n";
    echo "Diff: " . $diff . " seconds\n";
    
    $ignition = $gps->ignition_status;
    $speed = $gps->speed;
    
    echo "Ignition: " . ($ignition ? 'ON' : 'OFF') . "\n";
    echo "Speed: " . $speed . "\n";
    
    if ($diff < 600) {
        if ($ignition) {
            $gps_status = $speed > 2 ? 'Moving' : 'Idle';
        } else {
            $gps_status = 'Stopped';
        }
    } else {
        $gps_status = 'Offline';
    }
    
    echo "Resulting Status: " . $gps_status . "\n";
} else {
    echo "No GPS record found.\n";
}
