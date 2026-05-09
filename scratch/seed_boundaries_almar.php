<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Boundary;
use Carbon\Carbon;

$unitId = 136; // DCQ 1551
$driverId = 124; // Almar Monarba
$boundaryRate = 1200;

$days = 14;
$today = Carbon::today();

for ($i = $days; $i >= 0; $i--) {
    $date = $today->copy()->subDays($i);
    
    $exists = Boundary::where('unit_id', $unitId)
        ->whereDate('date', $date)
        ->exists();
        
    if (!$exists) {
        Boundary::create([
            'unit_id' => $unitId,
            'driver_id' => $driverId,
            'date' => $date,
            'boundary_amount' => $boundaryRate,
            'actual_boundary' => 0,
            'shortage' => $boundaryRate,
            'status' => 'pending',
            'created_by' => 1,
            'created_at' => $date->setHour(8),
            'updated_at' => $date->setHour(8),
        ]);
        echo "Created boundary for date: {$date->toDateString()}\n";
    } else {
        echo "Boundary already exists for date: {$date->toDateString()}\n";
    }
}
echo "Done!\n";
