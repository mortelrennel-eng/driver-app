<?php
echo "<h1>EuroTaxi System Final Diagnosis</h1>";

$basePath = dirname(getcwd());

// 1. Check for Critical Files
$filesToCheck = [
    'Api/DriverController' => $basePath . '/app/Http/Controllers/Api/DriverController.php',
    'Api/FranchiseController' => $basePath . '/app/Http/Controllers/Api/FranchiseController.php',
    'LiveTrackingController' => $basePath . '/app/Http/Controllers/LiveTrackingController.php',
    'Routes API' => $basePath . '/routes/api.php'
];

echo "<h3>File Audit:</h3><ul>";
foreach ($filesToCheck as $name => $path) {
    echo "<li>$name: ";
    if (file_exists($path)) {
        echo "<b style='color:green;'>EXISTS</b> (" . date("H:i:s", filemtime($path)) . ")";
    } else {
        echo "<b style='color:red;'>MISSING</b>";
    }
    echo "</li>";
}
echo "</ul>";

// 2. Read Laravel Log for errors
echo "<h3>LATEST SYSTEM ERRORS (laravel.log):</h3>";
$logPath = $basePath . '/storage/logs/laravel.log';
if (file_exists($logPath)) {
    $logContent = file($logPath);
    $lastLines = array_slice($logContent, -20); // Get last 20 lines
    echo "<pre style='background:#f4f4f4; padding:10px; border:1px solid #ccc; max-height:300px; overflow:auto;'>";
    foreach ($lastLines as $line) {
        echo htmlspecialchars($line);
    }
    echo "</pre>";
} else {
    echo "<p style='color:orange;'>Log file not found at $logPath</p>";
}

echo "<hr>";
echo "<h3>Next Step:</h3>";
echo "Paki-screenshot ang log sa taas para malaman natin kung bakit nag-eerror ang App.";

echo "<h1>EuroTaxi System Error Decoder</h1>";

$basePath = dirname(getcwd());
$logPath = $basePath . '/storage/logs/laravel.log';

if (file_exists($logPath)) {
    $content = file_get_contents($logPath);
    // Find the last occurrence of "local.ERROR" or "production.ERROR"
    $parts = explode('] local.ERROR:', $content);
    if (count($parts) < 2) $parts = explode('] production.ERROR:', $content);

    $latestError = end($parts);

    echo "<div style='background:#ffebee; color:#c62828; padding:20px; border:2px solid #ef5350; font-family:monospace;'>";
    echo "<h2>🔴 THE ACTUAL ERROR:</h2>";
    echo "<b>" . nl2br(htmlspecialchars(substr($latestError, 0, 500))) . "</b>";
    echo "</div>";

    echo "<h3>Full Context (Last 10 Lines):</h3>";
    echo "<pre style='background:#f5f5f5; padding:10px; border:1px solid #ddd;'>";
    $lines = file($logPath);
    $last10 = array_slice($lines, -10);
    foreach($last10 as $l) echo htmlspecialchars($l);
    echo "</pre>";
} else {
    echo "Log file not found.";
}

echo "<hr><a href='clear.php'>Refresh Log</a>";