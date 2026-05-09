<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "--- Unit Statuses (NOT Deleted) ---\n";
$stats = DB::table('units')
    ->whereNull('deleted_at')
    ->select('status', DB::raw('count(*) as count'))
    ->groupBy('status')
    ->get();
foreach ($stats as $s) {
    echo "Status: '{$s->status}', Count: {$s->count}\n";
}
