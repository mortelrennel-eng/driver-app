<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

$ts = app(\App\Services\TracksolidService::class);
$unit = DB::table('units')->where('plate_number', 'AAA 4591')->first();

echo "SENDING KILL ENGINE COMMAND TO AAA 4591...\n";
$start = time();
$resultKill = $ts->sendEngineCommand($unit->imei, 'kill');
echo "Time elapsed: " . (time() - $start) . " seconds\n";
echo "Response from Tracksolid Server:\n";
print_r($resultKill);
