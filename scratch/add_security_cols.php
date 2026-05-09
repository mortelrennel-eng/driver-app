<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Adding ip_address and device_id to gps_tracking table...\n";
try {
    DB::statement("ALTER TABLE gps_tracking ADD COLUMN ip_address VARCHAR(45) NULL AFTER timestamp");
    DB::statement("ALTER TABLE gps_tracking ADD COLUMN device_id VARCHAR(255) NULL AFTER ip_address");
    echo "SUCCESS: Columns added successfully.\n";
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
