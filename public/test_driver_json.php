<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$all_drivers = DB::select("
    SELECT d.id, CONCAT(COALESCE(d.first_name,''), ' ', COALESCE(d.last_name,'')) as name, 
           COALESCE(ua.plate_number, 'No Assignment') as current_unit,
           COALESCE(ua.plate_number, '') as current_plate,
           (SELECT COUNT(*) FROM units WHERE (driver_id = d.id OR secondary_driver_id = d.id) AND deleted_at IS NULL) as assigned_units_count,
           (SELECT GREATEST(0, COALESCE(SUM(shortage),0) - COALESCE(SUM(excess),0)) FROM boundaries WHERE driver_id = d.id AND deleted_at IS NULL) as net_shortage,
           (SELECT COUNT(*) FROM driver_behavior WHERE driver_id = d.id AND charge_status = 'pending' AND remaining_balance > 0) as has_accident_debt,
           (SELECT COALESCE(SUM(remaining_balance), 0) FROM driver_behavior WHERE driver_id = d.id AND charge_status = 'pending' AND remaining_balance > 0) as total_accident_debt
    FROM drivers d 
    LEFT JOIN units ua ON (d.id = ua.driver_id OR d.id = ua.secondary_driver_id) AND ua.deleted_at IS NULL
    WHERE d.deleted_at IS NULL AND d.driver_status != 'banned'
    ORDER BY 
        CASE WHEN ua.plate_number IS NOT NULL THEN 1 ELSE 0 END,
        d.last_name, d.first_name
");
$pendingDebts = DB::table('driver_behavior')
    ->where('charge_status', 'pending')
    ->where('remaining_balance', '>', 0)
    ->whereNull('deleted_at')
    ->select('id', 'driver_id', 'incident_type', 'incident_date', 'description', 'remaining_balance')
    ->orderBy('incident_date', 'asc')
    ->get()
    ->groupBy('driver_id');

$all_drivers = array_map(function($d) use ($pendingDebts) {
    $driverArray = (array) $d;
    $dId = $driverArray['id'];
    $driverArray['pending_debts'] = isset($pendingDebts[$dId]) 
        ? $pendingDebts[$dId]->toArray() 
        : [];
    return $driverArray;
}, $all_drivers);

foreach ($all_drivers as $d) {
    if (strpos($d['name'], 'Jesus') !== false) {
        echo "Driver Name: " . $d['name'] . "\n";
        echo "Has Accident Debt: " . $d['has_accident_debt'] . "\n";
        echo "Total Accident Debt: " . $d['total_accident_debt'] . "\n";
        echo "Pending Debts Array:\n";
        print_r($d['pending_debts']);
        echo "\nJSON Encoded:\n";
        echo json_encode($d['pending_debts']) . "\n";
    }
}
