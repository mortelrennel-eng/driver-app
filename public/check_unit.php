<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
use Illuminate\Support\Facades\DB;

// 1. Call getAllLocations just like the web dashboard does
$tracksolid = app(App\Services\TracksolidService::class);
$allLive = $tracksolid->getAllLocations();

echo "=== getAllLocations() result ===\n";
echo "Type: " . gettype($allLive) . "\n";
echo "Count: " . ($allLive ? count($allLive) : 'NULL') . "\n\n";

// 2. Find CAV 2607 IMEI
$unit = DB::table('units')->where('plate_number', 'like', '%CAV%2607%')->first();
echo "=== Unit ===\n";
echo "Plate: {$unit->plate_number}\n";
echo "IMEI: {$unit->imei}\n\n";

// 3. Find this IMEI in liveData
if ($allLive) {
    $found = collect($allLive)->where('imei', $unit->imei)->values()->all();
    echo "=== Live Data for this IMEI ===\n";
    if (!empty($found)) {
        $gps = $found[0];
        echo "accStatus: " . ($gps['accStatus'] ?? 'N/A') . "\n";
        echo "speed: " . ($gps['speed'] ?? 'N/A') . "\n";
        echo "gpsTime: " . ($gps['gpsTime'] ?? 'N/A') . "\n";
        echo "hbTime: " . ($gps['hbTime'] ?? 'N/A') . "\n";
        echo "lat: " . ($gps['lat'] ?? 'N/A') . "\n";
        echo "lng: " . ($gps['lng'] ?? 'N/A') . "\n";
        
        // Compute status EXACTLY like the web dashboard (LiveTrackingController)
        $ignition = ($gps['accStatus'] ?? 0) == 1;
        $rawSpeed = (float)($gps['speed'] ?? 0);
        
        $diff = PHP_INT_MAX;
        $gpsDiff = PHP_INT_MAX;
        
        if (isset($gps['hbTime']) && isset($gps['gpsTime'])) {
            $hbTs = strtotime($gps['hbTime'] . ' UTC');
            $gpsTs = strtotime($gps['gpsTime'] . ' UTC');
            $diff = abs(time() - max($hbTs, $gpsTs));
            $gpsDiff = abs(time() - $gpsTs);
        }
        
        echo "\n=== Web Dashboard Status Calc ===\n";
        echo "hbTs: " . (isset($hbTs) ? $hbTs : 'N/A') . "\n";
        echo "gpsTs: " . (isset($gpsTs) ? $gpsTs : 'N/A') . "\n";
        echo "time(): " . time() . "\n";
        echo "diff (heartbeat): {$diff}\n";
        echo "gpsDiff: {$gpsDiff}\n";
        echo "ignition: " . ($ignition ? 'ON' : 'OFF') . "\n";
        
        $status = 'offline';
        if ($diff < 600) {
            if ($ignition) {
                $actualSpeed = ($gpsDiff > 300) ? 0 : $rawSpeed;
                $status = $actualSpeed > 2 ? 'moving' : 'idle';
            } else {
                if ($gpsDiff > 600) {
                    $status = 'offline';
                } else {
                    $status = 'stopped';
                }
            }
        }
        echo "WEB STATUS: {$status}\n";
        
        // Now compute EXACTLY like DriverAppController
        echo "\n=== App (DriverAppController) Status Calc ===\n";
        $appIgnition = ($gps['accStatus'] ?? 0) == 1;
        $appSpeed = $appIgnition ? (float)($gps['speed'] ?? 0) : 0;
        
        // After writing to DB, it reads back from DB and uses gps->timestamp
        // The timestamp stored is gps['gpsTime']
        $gpsTime = $gps['gpsTime'] ?? null;
        if ($gpsTime) {
            $lastUpdateTs = strtotime($gpsTime . ' UTC');
            $appDiff = time() - $lastUpdateTs;
            echo "gpsTime: {$gpsTime}\n";
            echo "lastUpdateTs: {$lastUpdateTs}\n";
            echo "appDiff: {$appDiff}\n";
            
            $appStatus = 'Offline';
            if ($appDiff < 600) {
                if ($appIgnition) {
                    $appStatus = $appSpeed > 2 ? 'Moving' : 'Idle';
                } else {
                    $appStatus = 'Stopped';
                }
            }
            echo "APP STATUS: {$appStatus}\n";
        } else {
            echo "NO gpsTime available!\n";
        }
    } else {
        echo "IMEI NOT FOUND in live data!\n";
        echo "\nAll IMEIs in live data:\n";
        foreach ($allLive as $item) {
            echo "  " . ($item['imei'] ?? '?') . "\n";
        }
    }
} else {
    echo "getAllLocations() returned NULL - API FAILED!\n";
}
