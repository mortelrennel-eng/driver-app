<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$app->make('router')->get('/test_analytics', function (\Illuminate\Http\Request $req) use ($app) {
    $user = \App\Models\User::first();
    \Illuminate\Support\Facades\Auth::login($user);
    $controller = $app->make(\App\Http\Controllers\AnalyticsController::class);
    return $controller->index($req);
})->middleware('web');

$req = Illuminate\Http\Request::create('/test_analytics', 'GET');
$response = $kernel->handle($req);

echo "STATUS: " . $response->getStatusCode() . "\n";
echo substr($response->getContent(), 0, 500) . "\n";
if ($response->exception) {
    echo "EXCEPTION:\n" . (string) $response->exception;
}
