<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);
$users = \Illuminate\Support\Facades\DB::table('users')->get();
echo "Users in DB:\n";
foreach($users as $u) {
    echo "ID: {$u->id}, Name: {$u->first_name} {$u->last_name}, Role: {$u->role}\n";
}
echo "\nChat Messages:\n";
$msgs = \Illuminate\Support\Facades\DB::table('chat_messages')->get();
foreach($msgs as $m) {
    echo "ID: {$m->id}, From: {$m->from_user_id}, To: {$m->to_user_id}, Msg: {$m->message}\n";
}
