<?php

namespace App\Http\Controllers;

use App\Models\SupportMessage;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SupportManagementController extends Controller
{
    /**
     * Display the Messenger-style support interface.
     */
    public function index(Request $request)
    {
        $selectedDriverId = $request->get('driver_id');

        // Fetch all drivers with their latest message info
        $drivers = User::where('role', 'driver')
            ->select('users.*')
            ->addSelect([
                'latest_message' => SupportMessage::select('message')
                    ->whereColumn('driver_id', 'users.id')
                    ->orderByDesc('created_at')
                    ->limit(1),
                'latest_message_time' => SupportMessage::select('created_at')
                    ->whereColumn('driver_id', 'users.id')
                    ->orderByDesc('created_at')
                    ->limit(1),
                'unread_count' => SupportMessage::select(DB::raw('count(*)'))
                    ->whereColumn('driver_id', 'users.id')
                    ->where('sender_type', 'driver')
                    ->where('is_read', false)
            ])
            ->orderByRaw('latest_message_time IS NULL, latest_message_time DESC')
            ->orderBy('full_name')
            ->get();

        $chatMessages = [];
        $selectedDriver = null;

        if ($selectedDriverId) {
            $selectedDriver = User::where('role', 'driver')->findOrFail($selectedDriverId);
            
            // Mark messages as read
            SupportMessage::where('driver_id', $selectedDriverId)
                ->where('sender_type', 'driver')
                ->where('is_read', false)
                ->update(['is_read' => true]);

            $chatMessages = SupportMessage::where('driver_id', $selectedDriverId)
                ->orderBy('created_at', 'asc')
                ->get();
        }

        return view('support.index', compact('drivers', 'selectedDriver', 'chatMessages'));
    }

    /**
     * Get chat messages for the selected driver (AJAX).
     */
    public function getMessagesJson($driverId)
    {
        // Mark as read
        SupportMessage::where('driver_id', $driverId)
            ->where('sender_type', 'driver')
            ->where('is_read', false)
            ->update(['is_read' => true]);

        $messages = SupportMessage::where('driver_id', $driverId)
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'messages' => $messages
        ]);
    }

    /**
     * Get unread counts and latest messages for the driver list (AJAX).
     */
    public function getStatusJson()
    {
        $drivers = User::where('role', 'driver')
            ->select('id')
            ->addSelect([
                'latest_message' => SupportMessage::select('message')
                    ->whereColumn('driver_id', 'users.id')
                    ->orderByDesc('created_at')
                    ->limit(1),
                'latest_message_time' => SupportMessage::select('created_at')
                    ->whereColumn('driver_id', 'users.id')
                    ->orderByDesc('created_at')
                    ->limit(1),
                'unread_count' => SupportMessage::select(DB::raw('count(*)'))
                    ->whereColumn('driver_id', 'users.id')
                    ->where('sender_type', 'driver')
                    ->where('is_read', false)
            ])
            ->get();

        return response()->json([
            'success' => true,
            'drivers' => $drivers
        ]);
    }

    /**
     * Send a message to a driver.
     */
    public function sendMessage(Request $request)
    {
        $request->validate([
            'driver_id' => 'required|exists:users,id',
            'message' => 'required|string|max:2000',
        ]);

        $msg = SupportMessage::create([
            'driver_id' => $request->driver_id,
            'sender_type' => 'admin',
            'sender_id' => Auth::id(),
            'message' => $request->message,
        ]);

        // Send Push Notification to Driver
        $driverUser = User::find($request->driver_id);
        if ($driverUser && $driverUser->fcm_token) {
            \App\Services\FirebasePushService::sendPush(
                'EuroTaxi Support', 
                'Admin: ' . \Illuminate\Support\Str::limit($request->message, 50),
                $driverUser->fcm_token,
                'chat'
            );
        }

        return response()->json(['success' => true, 'message' => 'Message sent.']);
    }

    /**
     * (Optional) Keep the old ticket-based show for backward compatibility if needed, 
     * but we are moving to Messenger style.
     */
    public function show($id)
    {
        return redirect()->route('support.index', ['driver_id' => $id]);
    }
}
