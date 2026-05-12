<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$timestamp = '2026-05-12 05:09:35';
$lastUpdateTs = strtotime($timestamp . ' UTC');
$time = time();
$diff = $time - $lastUpdateTs;

echo "Timestamp from API: $timestamp\n";
echo "Parsed UTC Epoch: $lastUpdateTs\n";
echo "Current Time(): $time\n";
echo "Current UTC date(): " . gmdate("Y-m-d H:i:s", $time) . "\n";
echo "Difference (seconds): $diff\n";

if ($diff < 600) {
    echo "STATUS: IDLE/MOVING (Diff is < 600)\n";
} else {
    echo "STATUS: OFFLINE (Diff is >= 600)\n";
}

$unit_id = 1; // Try to fetch a real unit
$gps = \Illuminate\Support\Facades\DB::table('gps_tracking')->first();
if ($gps) {
    echo "\nDB GPS Timestamp: " . $gps->timestamp . "\n";
    $dbLast = strtotime($gps->timestamp . ' UTC');
    $dbDiff = $time - $dbLast;
    echo "DB Difference (seconds): $dbDiff\n";
}
