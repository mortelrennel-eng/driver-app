<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    /**
     * Get list of staff users to chat with.
     */
    public function users()
    {
        $currentUserId = Auth::id();

        $users = DB::table('users')
            ->where('id', '!=', $currentUserId)
            ->whereNull('deleted_at')
            ->whereIn('role', ['admin', 'cashier', 'manager', 'super_admin'])
            ->select('id', 'first_name', 'last_name', 'role', 'profile_image')
            ->orderBy('first_name')
            ->get()
            ->map(function ($u) use ($currentUserId) {
                // Unread count for this user
                $unread = DB::table('chat_messages')
                    ->where('from_user_id', $u->id)
                    ->where('to_user_id', $currentUserId)
                    ->whereNull('read_at')
                    ->count();

                // Last message
                $last = DB::table('chat_messages')
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

        return response()->json($users);
    }

    /**
     * Get messages between current user and another user.
     */
    public function messages(int $userId)
    {
        $currentUserId = Auth::id();

        // Mark incoming messages as read
        DB::table('chat_messages')
            ->where('from_user_id', $userId)
            ->where('to_user_id', $currentUserId)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        $messages = DB::table('chat_messages as m')
            ->join('users as u', 'u.id', '=', 'm.from_user_id')
            ->where(function ($q) use ($userId, $currentUserId) {
                $q->where('m.from_user_id', $currentUserId)->where('m.to_user_id', $userId);
            })
            ->orWhere(function ($q) use ($userId, $currentUserId) {
                $q->where('m.from_user_id', $userId)->where('m.to_user_id', $currentUserId);
            })
            ->select(
                'm.id', 'm.message', 'm.created_at', 'm.read_at',
                'm.from_user_id',
                DB::raw("CONCAT(COALESCE(u.first_name,''), ' ', COALESCE(u.last_name,'')) as sender_name")
            )
            ->orderBy('m.created_at', 'asc')
            ->limit(50)
            ->get()
            ->map(function ($m) use ($currentUserId) {
                return [
                    'id'       => $m->id,
                    'message'  => $m->message,
                    'time'     => \Carbon\Carbon::parse($m->created_at)->format('h:i A'),
                    'is_mine'  => $m->from_user_id == $currentUserId,
                    'sender'   => $m->sender_name,
                    'read'     => !is_null($m->read_at),
                ];
            });

        return response()->json($messages);
    }

    /**
     * Send a message.
     */
    public function send(Request $request)
    {
        $request->validate([
            'to_user_id' => 'required|integer|exists:users,id',
            'message'    => 'required|string|max:1000',
        ]);

        $id = DB::table('chat_messages')->insertGetId([
            'from_user_id' => Auth::id(),
            'to_user_id'   => $request->to_user_id,
            'message'      => $request->message,
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);

        // Send push notification to recipient
        $sender = Auth::user();
        PushSubscriptionController::sendPush(
            $request->to_user_id,
            'New Message from ' . ($sender->first_name ?? 'Staff'),
            substr($request->message, 0, 100),
            '/dashboard',
            'chat-msg'
        );

        return response()->json([
            'status' => 'sent',
            'id'     => $id,
            'time'   => now()->format('h:i A'),
        ]);
    }

    /**
     * Get total unread count for current user (for navbar badge).
     */
    public function unreadCount()
    {
        $count = DB::table('chat_messages')
            ->where('to_user_id', Auth::id())
            ->whereNull('read_at')
            ->count();

        return response()->json(['count' => $count]);
    }
}
