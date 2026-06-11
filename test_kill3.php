<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

$ts = app(\App\Services\TracksolidService::class);
$unit = DB::table('units')->where('plate_number', 'AAA 4591')->first();
$imei = $unit->imei;

echo "Sending Kill Command to AAA 4591 (IMEI: {$imei})...\n";
$start = time();
$result = $ts->sendEngineCommand($imei, 'kill');
$end = time();

echo "Time taken: " . ($end - $start) . " seconds\n";
print_r($result);
