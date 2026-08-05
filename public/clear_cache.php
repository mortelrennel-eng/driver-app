<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle($request = Illuminate\Http\Request::capture());

echo "Clearing caches...\n";
\Illuminate\Support\Facades\Artisan::call('optimize:clear');
echo \Illuminate\Support\Facades\Artisan::output() . "\n";

if (function_exists('opcache_reset')) {
    opcache_reset();
    echo "OPcache reset successfully.\n";
} else {
    echo "OPcache is not enabled or function does not exist.\n";
}
