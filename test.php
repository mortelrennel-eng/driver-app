<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$imei = '352503097246554';
$res = app(App\Services\TracksolidService::class)->getLocations([$imei]);
print_r($res);
