<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "--- Maintenance Records (NOT Deleted) ---\n";
$statuses = DB::table('maintenance')
    ->whereNull('deleted_at')
    ->select('status', DB::raw('count(*) as count'))
    ->groupBy('status')
    ->get();
foreach ($statuses as $s) {
    echo "Status: '{$s->status}', Count: {$s->count}\n";
}

echo "\n--- Maintenance Records (Deleted) ---\n";
$deleted_statuses = DB::table('maintenance')
    ->whereNotNull('deleted_at')
    ->select('status', DB::raw('count(*) as count'))
    ->groupBy('status')
    ->get();
foreach ($deleted_statuses as $s) {
    echo "Status: '{$s->status}', Count: {$s->count}\n";
}

echo "\n--- Distinct Statuses in Maintenance Table (All) ---\n";
$all_statuses = DB::table('maintenance')->distinct()->pluck('status');
foreach ($all_statuses as $s) {
    echo "'$s'\n";
}
