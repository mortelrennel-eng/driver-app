<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle($request = Illuminate\Http\Request::capture());

echo json_encode(\Illuminate\Support\Facades\DB::table('users')->select('id', 'first_name', 'last_name', 'role')->get());
