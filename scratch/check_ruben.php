<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$phone = '09911275418';
$name = 'RUBEN PATAJO';

$cleanPhone = substr(preg_replace('/[^0-9]/', '', $phone), -10);

echo "Searching for Phone: $cleanPhone\n";
$driverByPhone = \App\Models\Driver::where('contact_number', 'LIKE', '%' . $cleanPhone)->get();
echo "Found by Phone: " . $driverByPhone->count() . "\n";
foreach ($driverByPhone as $d) {
    echo "ID: {$d->id}, Name: {$d->first_name} {$d->last_name}, UserID: " . ($d->user_id ?? 'NULL') . "\n";
}

echo "\nSearching for Name: $name\n";
$driverByName = \App\Models\Driver::where('first_name', 'LIKE', '%RUBEN%')
    ->orWhere('last_name', 'LIKE', '%PATAJO%')
    ->get();
echo "Found by Name: " . $driverByName->count() . "\n";
foreach ($driverByName as $d) {
    echo "ID: {$d->id}, Name: {$d->first_name} {$d->last_name}, UserID: " . ($d->user_id ?? 'NULL') . "\n";
}

$user = \App\Models\User::where('name', 'LIKE', '%RUBEN%')->first();
if ($user) {
    echo "\nUser Found: ID: {$user->id}, Name: {$user->name}, Phone: {$user->phone}\n";
} else {
    echo "\nUser RUBEN not found in users table.\n";
}
