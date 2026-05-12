<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$u = \App\Models\Unit::where('plate_number', 'DCQ 1551')->first();
$d = \App\Models\Driver::where('id', $u->driver_id)->first();

echo json_encode([
    'driver_name' => $d->full_name,
    'license_number' => $d->license_number
]);
