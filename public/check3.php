<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle($request = Illuminate\Http\Request::capture());

echo "Maintenance Table exists: " . (Illuminate\Support\Facades\Schema::hasTable('maintenance') ? 'Yes' : 'No') . "\n";
echo "Salaries Table exists: " . (Illuminate\Support\Facades\Schema::hasTable('salaries') ? 'Yes' : 'No') . "\n";
echo "Payrolls Table exists: " . (Illuminate\Support\Facades\Schema::hasTable('payrolls') ? 'Yes' : 'No') . "\n";
