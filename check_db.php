<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
$res = DB::select('SHOW CREATE TABLE salaries');
print_r($res[0]);
?>
