<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "--- COLUMNS FOR staff ---\n";
print_r(DB::select('SHOW COLUMNS FROM staff'));

echo "\n--- COLUMNS FOR drivers ---\n";
print_r(DB::select('SHOW COLUMNS FROM drivers'));
