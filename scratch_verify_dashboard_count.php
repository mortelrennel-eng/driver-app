<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$count = DB::table('maintenance')
    ->whereNull('deleted_at')
    ->whereNotIn(DB::raw('LOWER(status)'), ['complete', 'completed', 'cancelled'])
    ->count();

echo "Maintenance Units Count (Dashboard Logic): $count\n";

$all_with_status = DB::table('maintenance')
    ->whereNull('deleted_at')
    ->select('status', DB::raw('LOWER(status) as lower_status'))
    ->get();

echo "\nAll statuses (LOWER):\n";
foreach ($all_with_status as $m) {
    echo "'{$m->status}' -> '{$m->lower_status}'\n";
}
