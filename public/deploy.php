<?php
/**
 * Web-based Deployer for EuroTaxi System
 * This script allows deployment by visiting it in the browser.
 */

echo "--- WEB DEPLOY START ---<br>";

// 1. Pull from GitHub
echo "Pulling from GitHub...<br>";
$output = [];
$return_var = 0;
exec("git pull origin main 2>&1", $output, $return_var);
echo "<pre>" . implode("\n", $output) . "</pre>";

if ($return_var !== 0) {
    echo "<b>ERROR during git pull.</b><br>";
} else {
    echo "<b>SUCCESS: Code updated.</b><br>";
}

// 2. Run migrations
echo "Running migrations...<br>";
$output = [];
exec("php artisan migrate --force 2>&1", $output, $return_var);
echo "<pre>" . implode("\n", $output) . "</pre>";

// 3. Optimize
echo "Optimizing...<br>";
$output = [];
exec("php artisan optimize 2>&1", $output, $return_var);
echo "<pre>" . implode("\n", $output) . "</pre>";

echo "--- DEPLOY COMPLETE ---";
?>
