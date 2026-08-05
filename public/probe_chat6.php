<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

// Boot Laravel
$kernel->bootstrap();

// Login as Rea (id=130)
$user = \App\Models\User::find(130);
if (!$user) {
    echo "Rea not found\n";
    exit;
}
\Illuminate\Support\Facades\Auth::login($user);

// Simulate request to /chat/users
$request = Illuminate\Http\Request::create('/chat/users', 'GET');
$response = $kernel->handle($request);

echo $response->getContent();
