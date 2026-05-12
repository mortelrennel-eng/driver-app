<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$u = \App\Models\Unit::where('plate_number', 'DCQ 1551')->first();
$d1 = $u->driver_id ? \App\Models\Driver::find($u->driver_id) : null;
$d2 = $u->secondary_driver_id ? \App\Models\Driver::find($u->secondary_driver_id) : null;

echo json_encode([
    'unit' => $u->plate_number,
    'unit_driver_id' => $u->unit_driver_id,
    'primary_driver' => $d1 ? $d1->full_name : 'NONE',
    'secondary_driver' => $d2 ? $d2->full_name : 'NONE'
]);
