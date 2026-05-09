<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $request = new \Illuminate\Http\Request();
    $controller = app(\App\Http\Controllers\Api\DashboardController::class);
    $response = $controller->index($request);
    echo "SUCCESS\n";
    // echo json_encode($response->getData());
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
