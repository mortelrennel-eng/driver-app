<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

$ts = app(\App\Services\TracksolidService::class);
for ($i=0; $i<3; $i++) {
    $res = $ts->getAllLocations();
    foreach ($res as $r) {
        if ($r['deviceName'] == 'AAA 4591') {
            echo "Attempt " . $i . ": lat=" . $r['lat'] . ", lng=" . $r['lng'] . "
";
        }
    }
    sleep(11);
}
