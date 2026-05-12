<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SupportTicket;
use Illuminate\Support\Facades\Auth;

class SupportController extends Controller
{
    /**
     * Get all tickets for the authenticated driver.
     */
    public function index()
    {
        $tickets = SupportTicket::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'tickets' => $tickets
        ]);
    }

    /**
     * Submit a new support ticket.
     */
    public function store(Request $request)
    {
        $request->validate([
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
            'category' => 'nullable|string'
        ]);

        $ticket = SupportTicket::create([
            'user_id' => Auth::id(),
            'subject' => $request->subject,
            'message' => $request->message,
            'category' => $request->category ?? 'general',
            'status' => 'pending'
        ]);

        // Also create as a message for the new chat interface
        \App\Models\SupportMessage::create([
            'driver_id' => Auth::id(),
            'sender_type' => 'driver',
            'sender_id' => Auth::id(),
            'message' => "Subject: " . $request->subject . "\n" . $request->message
        ]);

        // Log activity
        \App\Http\Controllers\ActivityLogController::log(
            'New Support Ticket',
            "Driver " . Auth::user()->name . " submitted a new support ticket: " . $request->subject
        );

        return response()->json([
            'success' => true,
            'message' => 'Your message has been sent to EuroTaxi support. We will get back to you soon.',
            'ticket' => $ticket
        ]);
    }

    /**
     * Get unread chat messages count for the authenticated driver.
     */
    public function getUnreadCount()
    {
        $count = \App\Models\SupportMessage::where('driver_id', Auth::id())
            ->where('sender_type', 'admin')
            ->where('is_read', false)
            ->count();

        return response()->json([
            'success' => true,
            'count' => $count
        ]);
    }

    /**
     * Get chat messages for the authenticated driver.
     */
    public function getMessages()
    {
        // Mark unread admin messages as read since the driver is opening the chat
        \App\Models\SupportMessage::where('driver_id', Auth::id())
            ->where('sender_type', 'admin')
            ->where('is_read', false)
            ->update(['is_read' => true]);

        $messages = \App\Models\SupportMessage::where('driver_id', Auth::id())
            ->leftJoin('users', 'support_messages.sender_id', '=', 'users.id')
            ->select(
                'support_messages.*',
                'users.full_name as sender_name',
                'users.role as sender_role'
            )
            ->orderBy('support_messages.created_at', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'messages' => $messages
        ]);
    }

    /**
     * Send a chat message.
     */
    public function sendMessage(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:2000'
        ]);

        $msg = \App\Models\SupportMessage::create([
            'driver_id' => Auth::id(),
            'sender_type' => 'driver',
            'sender_id' => Auth::id(),
            'message' => $request->message
        ]);

        return response()->json([
            'success' => true,
            'message' => $msg
        ]);
    }
}
