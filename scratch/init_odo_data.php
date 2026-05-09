<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$units = DB::table('units')->get();
foreach ($units as $u) {
    $gt = DB::table('gps_tracking')->where('unit_id', $u->id)->first();
    if ($gt) {
        DB::table('units')->where('id', $u->id)->update([
            'current_gps_odo' => $gt->odo,
            'last_service_odo_gps' => $gt->odo
        ]);
        echo "Updated Unit {$u->plate_number} to ODO {$gt->odo}\n";
    }
}
