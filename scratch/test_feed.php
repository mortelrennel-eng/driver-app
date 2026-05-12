<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$user = \App\Models\User::where('role', 'driver')->first();
if (!$user) {
    die("No driver user found.\n");
}
echo "Testing for User ID: {$user->id} ({$user->name})\n";

$driver = \DB::table('drivers')->where('user_id', $user->id)->first();
if ($driver) {
    echo "Driver Record Found. ID: {$driver->id}\n";
    $service = new \App\Services\NotificationService();
    $feed = $service->getDriverFeed($user->id);
    echo "Feed count: " . count($feed) . "\n";
    // print_r($feed);
} else {
    echo "No Driver Record found for User ID {$user->id}!\n";
}
