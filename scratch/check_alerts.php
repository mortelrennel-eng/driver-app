<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$total = DB::table('system_alerts')->count();
$unresolved = DB::table('system_alerts')->where('is_resolved', false)->count();

echo "Total System Alerts: " . $total . "\n";
echo "Unresolved Alerts: " . $unresolved . "\n";

$byType = DB::table('system_alerts')->select('type', 'is_resolved', DB::raw('count(*) as count'))->groupBy('type', 'is_resolved')->get();
foreach ($byType as $row) {
    echo "Type: " . $row->type . " | Resolved: " . ($row->is_resolved ? 'Yes' : 'No') . " | Count: " . $row->count . "\n";
}
