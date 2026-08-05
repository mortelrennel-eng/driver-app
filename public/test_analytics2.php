<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

try {
    $user = \App\Models\User::first();
    \Illuminate\Support\Facades\Auth::login($user);

    $request = Illuminate\Http\Request::create('/analytics', 'GET');
    $response = $kernel->handle($request);

    echo "STATUS: " . $response->getStatusCode() . "\n";
    echo substr($response->getContent(), 0, 500) . "\n";
} catch (\Throwable $e) {
    echo "CRASH: " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
}
