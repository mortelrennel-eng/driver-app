<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$unit = \App\Models\Unit::find(152);
echo json_encode([
    'boundary_rate' => $unit->boundary_rate,
    'year' => $unit->year
]);
