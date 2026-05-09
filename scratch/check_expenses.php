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

$breakdown = $data['modalData']['financialBreakdown'] ?? [];
echo "Available period keys: " . implode(', ', array_keys($breakdown)) . "\n\n";

foreach (['today', 'week', 'month', 'year'] as $key) {
    if (isset($breakdown[$key])) {
        $p = $breakdown[$key];
        echo "[$key] Revenue: ₱" . number_format($p['total_revenue'], 2) 
             . " | Expenses: ₱" . number_format($p['total_expenses'], 2)
             . " | General: " . count($p['general']) . " records"
             . " | Maintenance: " . count($p['maintenance']) . " records\n";
    } else {
        echo "[$key] NOT FOUND\n";
    }
}
