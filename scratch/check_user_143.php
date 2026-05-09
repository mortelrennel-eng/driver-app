<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$user = \App\Models\User::find(143);
if ($user) {
    echo "ID: {$user->id}, Name: {$user->name}, Phone: {$user->phone}\n";
} else {
    echo "User 143 not found\n";
}
