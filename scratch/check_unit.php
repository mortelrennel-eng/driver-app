<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$driverId = 54;
$unit = \App\Models\Unit::where('driver_id', $driverId)
    ->orWhere('secondary_driver_id', $driverId)
    ->first();

if ($unit) {
    echo json_encode([
        'id' => $unit->id,
        'plate_number' => $unit->plate_number,
        'driver_id' => $unit->driver_id,
        'secondary_driver_id' => $unit->secondary_driver_id
    ]);
} else {
    echo "No unit found for driver $driverId";
}
