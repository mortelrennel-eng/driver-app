<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$mCount = DB::table('maintenance')
    ->whereNull('deleted_at')
    ->whereNotIn(DB::raw('LOWER(status)'), ['complete', 'completed', 'cancelled'])
    ->count();

echo "Total Maintenance Records (Active): $mCount\n";

$mCountWithUnits = DB::table('maintenance')
    ->join('units', 'maintenance.unit_id', '=', 'units.id')
    ->whereNull('maintenance.deleted_at')
    ->whereNull('units.deleted_at')
    ->whereNotIn(DB::raw('LOWER(maintenance.status)'), ['complete', 'completed', 'cancelled'])
    ->count();

echo "Active Maintenance Records with Active Units: $mCountWithUnits\n";

if ($mCount != $mCountWithUnits) {
    echo "\nORPHANED RECORDS FOUND!\n";
    $orphans = DB::table('maintenance')
        ->leftJoin('units', 'maintenance.unit_id', '=', 'units.id')
        ->whereNull('maintenance.deleted_at')
        ->where(function($q) {
            $q->whereNull('units.id')->orWhereNotNull('units.deleted_at');
        })
        ->whereNotIn(DB::raw('LOWER(maintenance.status)'), ['complete', 'completed', 'cancelled'])
        ->select('maintenance.id', 'maintenance.unit_id', 'maintenance.status')
        ->get();
    foreach ($orphans as $o) {
        echo "ID: {$o->id}, Unit ID: {$o->unit_id}, Status: '{$o->status}'\n";
    }
}
