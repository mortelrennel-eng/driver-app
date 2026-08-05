<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle($request = Illuminate\Http\Request::capture());

$joel = Illuminate\Support\Facades\DB::table('drivers')
    ->where('first_name', 'like', '%Joel%')
    ->where('last_name', 'like', '%Sumando%')
    ->first();

if ($joel) {
    echo "Joel ID: " . $joel->id . "\n";
    $debts = Illuminate\Support\Facades\DB::table('driver_behavior')
        ->where('driver_id', $joel->id)
        ->where('incident_type', 'Short Boundary')
        ->get();
    print_r($debts);
} else {
    echo "Joel not found";
}
