<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

Schema::table('units', function (Blueprint $table) {
    if (Schema::hasColumn('units', 'unit_driver_id')) {
        $table->dropColumn('unit_driver_id');
        echo "Dropped unit_driver_id\n";
    }
    if (Schema::hasColumn('units', 'license_id')) {
        $table->dropColumn('license_id');
        echo "Dropped license_id\n";
    }
});

echo "Clean check finished.\n";
