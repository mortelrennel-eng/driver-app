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
                'id'          => $u->id,
                'name'        => trim($u->first_name . ' ' . $u->last_name),
                'role'        => ucfirst(str_replace('_', ' ', $u->role ?? '')),
                'avatar'      => strtoupper(substr($u->first_name ?? 'U', 0, 1)),
                'unread'      => $unread,
                'last_msg'    => $last ? substr($last->message, 0, 50) : null,
                'last_time'   => $last ? \Carbon\Carbon::parse($last->created_at)->diffForHumans() : null,
            ];
        });

    echo json_encode($users);
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n" . $e->getTraceAsString();
}
