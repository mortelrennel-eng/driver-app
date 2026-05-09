<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;

$users = User::whereNotNull('fcm_token')->get(['id', 'name', 'fcm_token']);
echo "USERS WITH TOKENS:\n";
foreach ($users as $user) {
    echo "ID: " . $user->id . " | Name: " . $user->name . " | Token: " . substr($user->fcm_token, 0, 20) . "...\n";
}
