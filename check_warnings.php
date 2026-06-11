<?php
$logPath = '/home/u747826271/domains/eurotaxisystem.site/public_html/storage/logs/laravel.log';
$lines = file($logPath);
$latestLines = array_slice($lines, -50);
foreach ($latestLines as $line) {
    if (strpos($line, 'production.WARNING') !== false || strpos($line, 'production.ERROR') !== false) {
        echo $line;
    }
}
