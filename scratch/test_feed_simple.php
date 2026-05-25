<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

try {
    $service = app(App\Services\NotificationService::class);
    $feed = $service->getDriverFeed(1);
    echo "SUCCESS: Found " . count($feed) . " feed items!\n";
    foreach ($feed as $item) {
        echo " - [{$item['type']}] {$item['title']}: {$item['message']}\n";
    }
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
