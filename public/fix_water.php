<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle($request = Illuminate\Http\Request::capture());

Illuminate\Support\Facades\DB::table('expenses')
    ->where('category', 'Water (Maynilad)')
    ->where('amount', '>', 1000000)
    ->update(['amount' => 1500.00]);

echo "Fixed large water expense.\n";
