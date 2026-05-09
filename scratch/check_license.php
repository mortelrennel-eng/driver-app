<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$d = \App\Models\Driver::find(54);
if ($d) {
    echo "Driver 54 license: " . ($d->license_number ?: "NULL") . "\n";
} else {
    echo "Driver 54 not found.\n";
}
