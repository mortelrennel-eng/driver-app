<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Http\Controllers\DashboardController;
use Illuminate\Http\Request;

$controller = new DashboardController();
$request = new Request();
$response = $controller->getNetIncomeDetails($request);
$data = json_decode($response->getContent(), true);

$income_data = $data['income_data'] ?? [];

$mnt_total = 0;
$office_total = 0;
$salary_total = 0;
$coding_total = 0;

$yearStart = now()->timezone('Asia/Manila')->startOfYear()->toDateString();
$today = now()->timezone('Asia/Manila')->toDateString();

foreach ($income_data as $item) {
    $date = substr($item['date'] ?? '', 0, 10);
    if ($date >= $yearStart && $date <= $today) {
        $amount = (float)($item['amount'] ?? 0);
        if ($item['type'] === 'maintenance') {
            $mnt_total += $amount;
        } elseif ($item['type'] === 'expense') {
            $office_total += $amount;
        } elseif ($item['type'] === 'salary') {
            $salary_total += $amount;
        } elseif ($item['type'] === 'coding') {
            $coding_total += $amount;
        }
    }
}

echo "Web 'Yearly' calculations:\n";
echo "Maintenance Total: " . $mnt_total . "\n";
echo "Office Total: " . $office_total . "\n";
echo "Salary Total: " . $salary_total . "\n";
echo "Coding Total: " . $coding_total . "\n";
echo "Total (Maintenance + Office): " . ($mnt_total + $office_total) . "\n";
echo "Total (All): " . ($mnt_total + $office_total + $salary_total + $coding_total) . "\n";
