<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle($request = Illuminate\Http\Request::capture());

$statuses = Illuminate\Support\Facades\DB::table('units')->select('status', Illuminate\Support\Facades\DB::raw('count(*) as count'))->groupBy('status')->get();
echo "Statuses:\n";
foreach($statuses as $s) {
    echo $s->status . ": " . $s->count . "\n";
}
