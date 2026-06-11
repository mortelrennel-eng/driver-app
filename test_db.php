
<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $msg = \App\Models\SupportMessage::create([
        'driver_id' => 1, // assuming driver 1 exists
        'sender_type' => 'admin',
        'sender_id' => 1,
        'message' => 'test message from CLI',
    ]);
    echo "Message created ID: " . $msg->id . "\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

