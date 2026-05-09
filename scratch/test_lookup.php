<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$user = \App\Models\User::find(153);
echo "User: " . ($user ? $user->name : "Not found") . "\n";

$driverRel = $user->driver;
echo "Relationship Match: " . ($driverRel ? "ID {$driverRel->id}" : "None") . "\n";

$driverQuery = \App\Models\Driver::where('user_id', 153)->first();
echo "Query Match: " . ($driverQuery ? "ID {$driverQuery->id}" : "None") . "\n";
