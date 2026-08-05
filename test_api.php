<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$tracksolid = app(\App\Services\TracksolidService::class);
print_r($tracksolid->getLocations(['352503097297284']));
