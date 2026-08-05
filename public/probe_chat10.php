<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

$currentUserId = 130;
$users = \Illuminate\Support\Facades\DB::table('users')
    ->where('id', '!=', $currentUserId)
    ->whereNull('deleted_at')
    ->where('role', '!=', 'driver')
    ->select('id', 'first_name', 'last_name', 'role')
    ->orderBy('first_name')
    ->get()
    ->map(function ($u) use ($currentUserId) {
        $unread = \Illuminate\Support\Facades\DB::table('chat_messages')
            ->where('from_user_id', $u->id)
            ->where('to_user_id', $currentUserId)
            ->whereNull('read_at')
            ->count();
        $last = \Illuminate\Support\Facades\DB::table('chat_messages')
            ->where(function ($q) use ($u, $currentUserId) {
                $q->where('from_user_id', $currentUserId)->where('to_user_id', $u->id);
            })
            ->orWhere(function ($q) use ($u, $currentUserId) {
                $q->where('from_user_id', $u->id)->where('to_user_id', $currentUserId);
            })
            ->orderByDesc('created_at')
            ->first();
        return [
            'id' => $u->id,
            'name' => $u->first_name,
            'unread' => $unread,
            'last_msg' => $last ? $last->message : null
        ];
    });

echo json_encode($users);
