<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

echo "Latest System Alerts:\n";
$alerts = DB::table('system_alerts')->orderBy('id', 'desc')->limit(5)->get();
foreach ($alerts as $a) {
    echo "[$a->created_at] $a->title : $a->message\n";
}

echo "\nLatest Logs:\n";
echo shell_exec('tail -n 20 ' . __DIR__ . '/storage/logs/laravel.log');
