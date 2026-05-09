<?php
$logPath = __DIR__.'/storage/logs/laravel.log';
if (file_exists($logPath)) {
    $logs = shell_exec('grep "153" ' . escapeshellarg($logPath) . ' | tail -n 50');
    echo $logs;
} else {
    echo "Log file not found\n";
}
