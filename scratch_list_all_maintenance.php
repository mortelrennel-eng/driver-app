<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$all = DB::table('maintenance')->get();
echo "ID\tStatus\t\tDeleted At\n";
foreach ($all as $m) {
    echo "{$m->id}\t'{$m->status}'\t\t'{$m->deleted_at}'\n";
}
