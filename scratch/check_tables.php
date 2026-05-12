<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$tables = \DB::select('SHOW TABLES');
foreach ($tables as $table) {
    $vars = get_object_vars($table);
    $tableName = array_values($vars)[0];
    if (strpos($tableName, 'charge') !== false || strpos($tableName, 'incentive') !== false || strpos($tableName, 'bonus') !== false) {
        echo "- Found relevant table: " . $tableName . "\n";
    }
}
echo "Done checking tables.\n";
