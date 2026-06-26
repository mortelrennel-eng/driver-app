<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$tracksolid = app(\App\Services\TracksolidService::class);
$all = $tracksolid->getAllLocations();
$filtered = array_filter($all, function($item) {
    return $item['deviceName'] === 'CAV 2607' || strpos($item['deviceName'], 'CAV 2607') !== false;
});

print_r(array_values($filtered));
