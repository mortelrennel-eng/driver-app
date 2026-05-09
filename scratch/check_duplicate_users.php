<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$users = \App\Models\User::where('name', 'LIKE', '%RUBEN%')->get();
echo "Found " . $users->count() . " users named RUBEN:\n";
foreach ($users as $u) {
    echo "ID: {$u->id}, Name: {$u->name}, Phone: {$u->phone}\n";
}
