<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$driver = \App\Models\Driver::where('first_name', 'LIKE', '%Joel%')->orWhere('last_name', 'LIKE', '%Sumando%')->first();
if ($driver) {
    echo "Driver found!\n";
    echo "ID: " . $driver->id . "\n";
    echo "First Name: '" . $driver->first_name . "'\n";
    echo "Last Name: '" . $driver->last_name . "'\n";
    echo "User ID: " . var_export($driver->user_id, true) . "\n";
    echo "Plate: " . \App\Models\Unit::where('driver_id', $driver->id)->orWhere('secondary_driver_id', $driver->id)->value('plate_number') . "\n";
} else {
    echo "Driver not found.\n";
}
