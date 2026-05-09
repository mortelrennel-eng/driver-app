<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$all = DB::table('maintenance')->whereNull('deleted_at')->get();
echo "Total Records: " . $all->count() . "\n\n";
foreach ($all as $m) {
    echo "ID: {$m->id}, Status: '{$m->status}', Type: '{$m->maintenance_type}'\n";
}
