<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$statuses = DB::table('maintenance')->select('status', DB::raw('count(*) as count'))->groupBy('status')->get();
echo "Maintenance Statuses:\n";
foreach ($statuses as $s) {
    echo "Status: '{$s->status}', Count: {$s->count}\n";
}

$types = DB::table('maintenance')->select('maintenance_type', DB::raw('count(*) as count'))->groupBy('maintenance_type')->get();
echo "\nMaintenance Types:\n";
foreach ($types as $t) {
    echo "Type: '{$t->maintenance_type}', Count: {$t->count}\n";
}
