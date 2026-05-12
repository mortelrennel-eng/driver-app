<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "--- DRIVER INCENTIVES ---\n";
print_r(\Schema::getColumnListing('driver_incentives'));

echo "--- BOUNDARIES ---\n";
print_r(\Schema::getColumnListing('boundaries'));
