<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

echo "Checking Alerts for AAA 4591:\n";
$alerts = DB::table('system_alerts')->where('title', 'like', '%AAA 4591%')->orderBy('id', 'desc')->limit(10)->get();
foreach ($alerts as $a) {
    echo "[$a->created_at] $a->title : $a->message\n";
}
