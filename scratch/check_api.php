<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Http\Controllers\Api\DashboardController;
use Illuminate\Http\Request;

$controller = new DashboardController();
$request = new Request();
$response = $controller->index($request);
$data = json_decode($response->getContent(), true);

echo "Total Drivers in list: " . count($data['modalData']['driversList']) . "\n";
echo "Total Drivers in stats: " . $data['stats']['total_drivers'] . "\n";
echo "Active Drivers in stats: " . $data['stats']['active_drivers_count'] . "\n";
echo "Vacant Drivers in stats: " . $data['stats']['vacant_drivers_count'] . "\n";
echo "First Driver Name: " . $data['modalData']['driversList'][0]['name'] . "\n";
echo "First Driver Assigned Units: " . $data['modalData']['driversList'][0]['assigned_units'] . "\n";
