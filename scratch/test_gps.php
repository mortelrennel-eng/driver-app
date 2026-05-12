<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$service = new \App\Services\TracksolidService();
$imei = '866952046467005'; // A typical IMEI format, let's just get all to find one first
$all = $service->getAllLocations();

if (!empty($all)) {
    $first = $all[0]['imei'];
    echo "Testing single getLocations for IMEI: $first\n";
    $single = $service->getLocations([$first]);
    echo "ALL LOCATIONS TIMESTAMP: " . $all[0]['gpsTime'] . "\n";
    if ($single && isset($single[0])) {
        echo "SINGLE LOCATION TIMESTAMP: " . $single[0]['gpsTime'] . "\n";
    } else {
        echo "SINGLE LOCATION FAILED!\n";
        print_r($single);
    }
} else {
    echo "NO LOCATIONS RETURNED FROM GETALL!\n";
}
