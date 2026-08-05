<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$driver = DB::table('drivers')->where('first_name', 'like', '%Jesus%')->first();
if ($driver) {
    echo "Driver: {$driver->first_name} {$driver->last_name} (ID: {$driver->id})\n";
    $boundaries = DB::table('boundaries')->where('driver_id', $driver->id)->whereNull('deleted_at')->get();
    foreach ($boundaries as $b) {
        echo "Date: {$b->date}, Target: {$b->boundary_amount}, Actual: {$b->actual_boundary}, Shortage: {$b->shortage}, Excess: {$b->excess}, Status: {$b->status}\n";
    }
} else {
    echo "Driver not found\n";
}
