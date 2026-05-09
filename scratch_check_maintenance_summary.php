<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$totals = DB::table('maintenance')->whereNull('deleted_at')->selectRaw('
    COUNT(*) as total_count,
    SUM(cost) as total_cost,
    SUM(CASE WHEN maintenance.status = "complete" THEN 1 ELSE 0 END) as completed_count,
    SUM(CASE WHEN maintenance.status = "pending" THEN 1 ELSE 0 END) as pending_count,
    SUM(CASE WHEN maintenance.status = "ongoing" THEN 1 ELSE 0 END) as in_progress_count
')->first();

echo "Maintenance Controller Totals:\n";
echo "Total Count: {$totals->total_count}\n";
echo "Completed: " . ($totals->completed_count ?? 0) . "\n";
echo "Pending: " . ($totals->pending_count ?? 0) . "\n";
echo "In Progress: " . ($totals->in_progress_count ?? 0) . "\n";

$other_statuses = DB::table('maintenance')
    ->whereNull('deleted_at')
    ->whereNotIn('status', ['complete', 'pending', 'ongoing'])
    ->select('status', DB::raw('count(*) as count'))
    ->groupBy('status')
    ->get();

echo "\nOther Statuses (Not handled by MaintenanceController):\n";
foreach ($other_statuses as $s) {
    echo "Status: '{$s->status}', Count: {$s->count}\n";
}
