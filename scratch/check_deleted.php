<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$d = \App\Models\Driver::withTrashed()->find(54);
if ($d) {
    echo "Driver 54 deleted_at: " . ($d->deleted_at ?: "NULL") . "\n";
    echo "Driver 54 user_id: " . ($d->user_id ?: "NULL") . "\n";
} else {
    echo "Driver 54 not found even in trashed.\n";
}
