<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$driver = DB::table('drivers')->where('first_name', 'like', '%Jesus%')->first();
if ($driver) {
    echo "Driver: {$driver->first_name} {$driver->last_name} (ID: {$driver->id})\n";
    $debts = DB::table('driver_behavior')->where('driver_id', $driver->id)->get();
    foreach ($debts as $d) {
        echo "ID: {$d->id}, Type: {$d->incident_type}, Balance: {$d->remaining_balance}, Status: {$d->charge_status}, Date: {$d->incident_date}, Deleted: " . ($d->deleted_at ?? 'NULL') . "\n";
    }
} else {
    echo "Driver not found\n";
}
