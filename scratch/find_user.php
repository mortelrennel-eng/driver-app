<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$user = \App\Models\User::where('name', 'LIKE', '%RUBEN%')->first();
if ($user) {
    echo json_encode([
        'id' => $user->id,
        'name' => $user->name,
        'phone' => $user->phone,
        'phone_number' => $user->phone_number,
        'role' => $user->role
    ]);
} else {
    echo "User not found";
}
