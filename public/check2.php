<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle($request = Illuminate\Http\Request::capture());

$joel = Illuminate\Support\Facades\DB::table('drivers')
    ->where('first_name', 'like', '%Joel%')
    ->where('last_name', 'like', '%Sumando%')
    ->first();

if ($joel) {
    echo "Joel ID: " . $joel->id . "\n";
    echo "User ID: " . $joel->user_id . "\n";
    
    $alerts = Illuminate\Support\Facades\DB::table('system_alerts')
        ->where('user_id', $joel->user_id)
        ->get();
    echo "Alerts: " . count($alerts) . "\n";
    print_r($alerts);
}
