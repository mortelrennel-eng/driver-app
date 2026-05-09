<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$active = DB::table('maintenance')
    ->whereNull('deleted_at')
    ->whereNotIn(DB::raw('LOWER(status)'), ['complete', 'completed', 'cancelled'])
    ->get();

echo "Active Maintenance Records (Total: " . count($active) . "):\n";
$types = [];
foreach ($active as $r) {
    $type = strtolower($r->maintenance_type);
    if (!isset($types[$type])) $types[$type] = 0;
    $types[$type]++;
    echo "ID: {$r->id}, Type: '{$r->maintenance_type}', Status: '{$r->status}'\n";
}

echo "\nSummary by Type:\n";
foreach ($types as $type => $count) {
    echo "$type: $count\n";
}

$completedCount = DB::table('maintenance')
    ->whereNull('deleted_at')
    ->whereIn(DB::raw('LOWER(status)'), ['complete', 'completed'])
    ->count();

echo "\nCompleted Total: $completedCount\n";
