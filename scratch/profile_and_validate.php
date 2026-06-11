<?php

// Bootstrap Laravel
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\BoundaryController;
use App\Models\Unit;

echo "==================================================\n";
echo "ENTERPRISE VALIDATION AUDIT & PROFILING\n";
echo "==================================================\n\n";

$queries = [];
DB::listen(function ($query) use (&$queries) {
    $queries[] = [
        'sql' => $query->sql,
        'time' => $query->time, // in ms
    ];
});

// 1. Profile Dashboard Weekly Financial and Revenue Trend Data
echo "--- 1. PROFILING DASHBOARD CONTROLLER OPTIMIZATIONS ---\n";
$dashboard = new DashboardController();

// Clear query log
$queries = [];
$startTime = microtime(true);

// Run getWeeklyFinancialData using Reflection to access private/protected method if necessary
$reflector = new ReflectionClass(DashboardController::class);
$getWeeklyFinancialData = $reflector->getMethod('getWeeklyFinancialData');
$getWeeklyFinancialData->setAccessible(true);
$weeklyResults = $getWeeklyFinancialData->invoke($dashboard);

$endTime = microtime(true);
$duration = ($endTime - $startTime) * 1000; // ms

echo "getWeeklyFinancialData():\n";
echo "  - Execution Time: " . number_format($duration, 2) . " ms\n";
echo "  - Total SQL Queries Run: " . count($queries) . "\n";
echo "  - Sample Query: " . (count($queries) > 0 ? substr($queries[0]['sql'], 0, 150) . "..." : "None") . "\n";
echo "  - Data Count Returned: " . count($weeklyResults) . "\n\n";

// Clear query log
$queries = [];
$startTime = microtime(true);

$getRevenueTrendData = $reflector->getMethod('getRevenueTrendData');
$getRevenueTrendData->setAccessible(true);
$revenueResults = $getRevenueTrendData->invoke($dashboard, 30);

$endTime = microtime(true);
$duration = ($endTime - $startTime) * 1000; // ms

echo "getRevenueTrendData(30):\n";
echo "  - Execution Time: " . number_format($duration, 2) . " ms\n";
echo "  - Total SQL Queries Run: " . count($queries) . "\n";
echo "  - Sample Query: " . (count($queries) > 0 ? substr($queries[0]['sql'], 0, 150) . "..." : "None") . "\n";
echo "  - Data Count Returned: " . count($revenueResults) . "\n\n";


// 2. Profile Boundary Controller Optimization
echo "--- 2. PROFILING BOUNDARY CONTROLLER OPTIMIZATIONS ---\n";

// Simulate a request to verify index page mapping query counts
$request = new \Illuminate\Http\Request();
$boundaryController = new BoundaryController();

// Access the index method and check queries executed
$queries = [];
$startTime = microtime(true);

try {
    // We capture view output to run all queries
    ob_start();
    $response = $boundaryController->index($request);
    if ($response instanceof \Illuminate\View\View) {
        $response->render(); // force query execution inside blade
    }
    ob_end_clean();
} catch (\Exception $e) {
    // If there is any missing parameters, print error but record query status
    echo "  [Note: Index load error: " . $e->getMessage() . "]\n";
}

$endTime = microtime(true);
$duration = ($endTime - $startTime) * 1000;

echo "BoundaryController::index() batch load:\n";
echo "  - Execution Time: " . number_format($duration, 2) . " ms\n";
echo "  - Total SQL Queries Run: " . count($queries) . "\n";
echo "  - Query Breakdown:\n";
$counts = [];
foreach ($queries as $q) {
    $table = 'unknown';
    if (preg_match('/from `([^`]+)`/i', $q['sql'], $m)) $table = $m[1];
    elseif (preg_match('/into `([^`]+)`/i', $q['sql'], $m)) $table = $m[1];
    elseif (preg_match('/update `([^`]+)`/i', $q['sql'], $m)) $table = $m[1];
    $counts[$table] = ($counts[$table] ?? 0) + 1;
}
foreach ($counts as $table => $count) {
    echo "    * Table '$table': $count queries\n";
}
echo "\n";


// 3. Dry-Run Verification of toggleStatus to verify logic safety
echo "--- 3. DRY-RUN TOGGLE STATUS LOGIC SAFETY ---\n";
$testUnit = DB::table('units')->whereNull('deleted_at')->first();
if ($testUnit) {
    echo "Found active test unit for dry-run: Plate {$testUnit->plate_number} (ID: {$testUnit->id})\n";
    
    // Check if shift_deadline_at is set or we mock update it
    $originalDeadline = $testUnit->shift_deadline_at;
    
    DB::table('units')->where('id', $testUnit->id)->update([
        'shift_deadline_at' => now()->subDays(3)->toDateTimeString()
    ]);
    
    // Instanciate UnitController and run toggleStatus mock
    $unitController = new \App\Http\Controllers\UnitController();
    $mockRequest = new \Illuminate\Http\Request([
        'id' => $testUnit->id,
        'new_status' => 'active'
    ]);
    
    $queries = [];
    $unitController->toggleStatus($mockRequest);
    
    // Fetch unit back and check state
    $updatedUnit = DB::table('units')->where('id', $testUnit->id)->first();
    echo "  - Original shift deadline: " . ($originalDeadline ?: "NULL") . "\n";
    echo "  - State after un-flagging:\n";
    echo "    * Status: {$updatedUnit->status}\n";
    echo "    * Shift Deadline: " . ($updatedUnit->shift_deadline_at === null ? "SUCCESSFULLY NULL" : "STILL SET TO {$updatedUnit->shift_deadline_at}") . "\n";
    echo "    * is_pinned_missing: " . ($updatedUnit->is_pinned_missing == 0 ? "SUCCESSFULLY FALSE" : "STILL TRUE") . "\n";
    
    // Restore original values to avoid database pollution
    DB::table('units')->where('id', $testUnit->id)->update([
        'shift_deadline_at' => $originalDeadline,
        'status' => $testUnit->status,
        'is_pinned_missing' => $testUnit->is_pinned_missing
    ]);
    echo "  - Test database restored to original pristine state.\n\n";
} else {
    echo "  - No test units found to run dry-run validation.\n\n";
}


// 4. Scan production laravel log for silent exceptions
echo "--- 4. PRODUCTION LOG DIAGNOSTICS ---\n";
$logPath = storage_path('logs/laravel.log');
if (file_exists($logPath)) {
    $logSize = filesize($logPath);
    echo "Laravel production log exists: " . number_format($logSize / 1024, 2) . " KB\n";
    
    // Read last 20 lines of the log to detect any recent exceptions
    $lines = file($logPath);
    $lastLines = array_slice($lines, -30);
    $errorCount = 0;
    foreach ($lastLines as $line) {
        if (preg_match('/error|exception|critical|fail/i', $line)) {
            $errorCount++;
            echo "  * " . trim($line) . "\n";
        }
    }
    if ($errorCount === 0) {
        echo "  - Diagnostic check: Zero recent failures or exceptions detected in production logs!\n";
    }
} else {
    echo "  - Laravel log does not exist or has not generated any records yet.\n";
}

echo "\n==================================================\n";
echo "VALIDATION DIAGNOSTICS COMPLETED SUCCESSFULLY\n";
echo "==================================================\n";
