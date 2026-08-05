<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ChatController extends Controller
{
    /**
     * Get list of staff users to chat with.
     */
    public function users(Request $request)
    {
        $currentUserId = Auth::id();

        $users = DB::table('users')
            ->where('id', '!=', $currentUserId)
            ->whereNull('deleted_at')
            ->where('role', '!=', 'driver')
            ->select('id', 'first_name', 'last_name', 'role', 'profile_image', 'last_seen_at')
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
                
                // Determine online status (active within last 5 mins)
                $isOnline = false;
                $lastActive = null;
                if ($u->last_seen_at) {
                    $seenAt = Carbon::parse($u->last_seen_at);
                    if ($seenAt->diffInMinutes(now()) <= 5) {
                        $isOnline = true;
                    } else {
                        $lastActive = $seenAt->diffForHumans();
                    }
                }

                $msgText = $last ? $last->message : null;
                if ($last && !$msgText && $last->attachment_name) {
                    $msgText = 'Sent an attachment';
                }

                return [
                    'id'          => $u->id,
                    'name'        => trim($u->first_name . ' ' . $u->last_name),
                    'role'        => ucfirst(str_replace('_', ' ', $u->role ?? '')),
                    'avatar'      => strtoupper(substr($u->first_name ?? 'U', 0, 1)),
                    'unread'      => $unread,
                    'last_msg'    => $msgText ? substr($msgText, 0, 50) : null,
                    'last_time'   => $last ? Carbon::parse($last->created_at)->diffForHumans() : null,
                    'is_online'   => $isOnline,
                    'last_active' => $lastActive,
                ];
            })
            ->values()
            ->toArray();

        // Group Chat Item
        $lastGroupMsg = DB::table('chat_messages')
            ->whereNull('to_user_id')
            ->orderByDesc('created_at')
            ->first();

        $gMsgText = $lastGroupMsg ? $lastGroupMsg->message : null;
        if ($lastGroupMsg && !$gMsgText && $lastGroupMsg->attachment_name) {
            $gMsgText = 'Sent an attachment';
        }

        $unreadGroup = 0;
        if ($request->has('last_group_msg_id')) {
            $lastGroupMsgId = (int) $request->last_group_msg_id;
            $unreadGroup = DB::table('chat_messages')
                ->whereNull('to_user_id')
                ->where('id', '>', $lastGroupMsgId)
                ->where('from_user_id', '!=', Auth::id())
                ->count();
        }

        $groupChat = [
            'id'          => 0,
            'name'        => '📢 General Staff Chat',
            'role'        => 'Group',
            'avatar'      => 'ALL',
            'unread'      => $unreadGroup,
            'last_msg'    => $gMsgText ? substr($gMsgText, 0, 50) : 'Chat with all staff',
            'last_time'   => $lastGroupMsg ? Carbon::parse($lastGroupMsg->created_at)->diffForHumans() : null,
            'is_online'   => true,
            'last_active' => null,
        ];

        array_unshift($users, $groupChat);

        return response()->json($users)->header('Cache-Control', 'no-cache, no-store, must-revalidate')->header('Pragma', 'no-cache')->header('Expires', '0');
    }

    /**
     * Get messages between current user and another user.
     */
    public function messages(int $userId)
    {
        $currentUserId = Auth::id();

        if ($userId != 0) {
            // Mark incoming messages as read
            DB::table('chat_messages')
                ->where('from_user_id', $userId)
                ->where('to_user_id', $currentUserId)
                ->whereNull('read_at')
                ->update(['read_at' => now()]);
        }

        $query = DB::table('chat_messages as m')
            ->join('users as u', 'u.id', '=', 'm.from_user_id')
            ->leftJoin('chat_messages as rm', 'rm.id', '=', 'm.reply_to_id')
            ->leftJoin('users as ru', 'ru.id', '=', 'rm.from_user_id')
            ->select(
                'm.id', 'm.message', 'm.created_at', 'm.read_at', 'm.reactions', 'm.reply_to_id',
                'm.from_user_id', 'm.attachment_path', 'm.attachment_type', 'm.attachment_name', 'm.is_forwarded',
                DB::raw("CONCAT(COALESCE(u.first_name,''), ' ', COALESCE(u.last_name,'')) as sender_name"),
                'u.first_name as sender_first_name',
                'rm.message as reply_message', 'rm.attachment_type as reply_attachment_type',
                DB::raw("CONCAT(COALESCE(ru.first_name,''), ' ', COALESCE(ru.last_name,'')) as reply_sender_name")
            );

        if ($userId == 0) {
            $query->whereNull('m.to_user_id');
        } else {
            $query->where(function ($q) use ($userId, $currentUserId) {
                $q->where('m.from_user_id', $currentUserId)->where('m.to_user_id', $userId);
            })
            ->orWhere(function ($q) use ($userId, $currentUserId) {
                $q->where('m.from_user_id', $userId)->where('m.to_user_id', $currentUserId);
            });
        }

        $messages = $query->orderBy('m.created_at', 'desc')
            ->limit(50)
            ->get()
            ->reverse()
            ->values()
            ->map(function ($m) use ($currentUserId) {
                return [
                    'id'              => $m->id,
                    'message'         => $m->message,
                    'attachment_path' => $m->attachment_path ? asset('storage/' . $m->attachment_path) : null,
                    'attachment_type' => $m->attachment_type,
                    'attachment_name' => $m->attachment_name,
                    'time'            => Carbon::parse($m->created_at)->format('h:i A'),
                    'is_mine'         => $m->from_user_id == $currentUserId,
                    'sender'          => $m->sender_name,
                    'sender_avatar'   => strtoupper(substr($m->sender_first_name ?? 'U', 0, 1)),
                    'is_forwarded'    => (bool)($m->is_forwarded ?? false),
                    'read'            => !is_null($m->read_at),
                    'reactions'       => json_decode($m->reactions ?? '{}', true) ?: [],
                    'reply_data'      => $m->reply_to_id ? [
                        'id'              => $m->reply_to_id,
                        'message'         => $m->reply_message,
                        'attachment_type' => $m->reply_attachment_type,
                        'sender'          => $m->reply_sender_name,
                    ] : null,
                ];
            });

        return response()->json($messages);
    }

    /**
     * Send a message.
     */
    public function send(Request $request)
    {
        \Log::info('Chat Send Payload:', $request->all());
        
        try {
            $request->validate([
                'to_user_id'      => 'required|integer', // Allow 0
                'message'         => 'nullable|string|max:1000',
                'attachment'      => 'nullable|file|max:10240', // 10MB max
                'reply_to_id'     => 'nullable|integer',
                'forward_from_id' => 'nullable|integer',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Chat Send Validation Failed: ', $e->errors());
            throw $e;
        }

        // If to_user_id != 0, must exist
        if ($request->to_user_id != 0) {
            $request->validate([
                'to_user_id' => 'exists:users,id'
            ]);
        }

        if (!$request->message && !$request->hasFile('attachment') && !$request->forward_from_id) {
            return response()->json(['error' => 'Message, attachment or forward ID is required'], 422);
        }

        $attachmentPath = null;
        $attachmentType = null;
        $attachmentName = null;
        $messageText = $request->message;

        if ($request->forward_from_id) {
            $origMsg = DB::table('chat_messages')->where('id', $request->forward_from_id)->first();
            if ($origMsg) {
                $messageText = $origMsg->message;
                $attachmentPath = $origMsg->attachment_path;
                $attachmentType = $origMsg->attachment_type;
                $attachmentName = $origMsg->attachment_name;
            }
        } elseif ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $attachmentName = $file->getClientOriginalName();
            $mime = $file->getMimeType();
            if (str_starts_with($mime, 'image/')) {
                $attachmentType = 'image';
            } elseif (str_starts_with($mime, 'video/')) {
                $attachmentType = 'video';
            } else {
                $attachmentType = 'file';
            }
            $attachmentPath = $file->store('chat_attachments', 'public');
        }

        $id = DB::table('chat_messages')->insertGetId([
            'from_user_id'    => Auth::id(),
            'to_user_id'      => $request->to_user_id == 0 ? null : $request->to_user_id,
            'message'         => $messageText,
            'attachment_path' => $attachmentPath,
            'attachment_type' => $attachmentType,
            'attachment_name' => $attachmentName,
            'reply_to_id'     => $request->reply_to_id,
            'is_forwarded'    => $request->forward_from_id ? 1 : 0,
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);

        // Send push notification
        $sender = Auth::user();
        if ($request->to_user_id != 0) {
            PushSubscriptionController::sendPush(
                $request->to_user_id,
                'New Message from ' . ($sender->first_name ?? 'Staff'),
                substr($request->message ?? $attachmentName, 0, 100),
                '/dashboard',
                'chat-msg'
            );
        }

        return response()->json([
            'status' => 'sent',
            'id'     => $id,
            'time'   => now()->format('h:i A'),
        ]);
    }

    /**
     * Add or remove a reaction.
     */
    public function react(Request $request, $messageId)
    {
        $message = DB::table('chat_messages')->where('id', $messageId)->first();
        if (!$message) return response()->json(['error' => 'Not found'], 404);

        $reactions = json_decode($message->reactions ?? '{}', true) ?: [];
        $userId = Auth::id();

        if ($request->reaction) {
            $reactions[$userId] = $request->reaction;
        } else {
            unset($reactions[$userId]);
        }

        DB::table('chat_messages')->where('id', $messageId)->update([
            'reactions' => json_encode($reactions)
        ]);

        return response()->json(['success' => true]);
    }

    /**
     * Get total unread count for current user (for navbar badge).
     */
    public function unreadCount()
    {
        DB::table('users')->where('id', Auth::id())->update([
            'last_seen_at' => now(),
            'is_online' => true
        ]);

        $count = DB::table('chat_messages')
            ->where('to_user_id', Auth::id())
            ->whereNull('read_at')
            ->count();

        // Optionally, could add unread group messages if tracked

        return response()->json(['count' => $count]);
    }
}
