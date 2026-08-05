<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

try {
    $results = \Illuminate\Support\Facades\DB::select("SHOW COLUMNS FROM chat_messages LIKE 'reactions'");
    if (count($results) > 0) {
        echo "YES_COLUMN_EXISTS";
    } else {
        echo "NO_COLUMN";
    }
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage();
}
