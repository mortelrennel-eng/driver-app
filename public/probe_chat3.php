<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle($request = Illuminate\Http\Request::capture());

try {
    $currentUserId = 130; // Rea Remitra
    $users = \Illuminate\Support\Facades\DB::table('users')
        ->where('id', '!=', $currentUserId)
        ->whereNull('deleted_at')
        ->where('role', '!=', 'driver')
        ->select('id', 'first_name', 'last_name', 'role', 'profile_image')
        ->orderBy('first_name')
        ->get();
    echo json_encode($users);
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage();
}
