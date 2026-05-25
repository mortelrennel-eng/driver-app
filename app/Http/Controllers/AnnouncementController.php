<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\User;
use App\Services\FirebasePushService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AnnouncementController extends Controller
{
    /**
     * Display a listing of announcements for the admin.
     */
    public function index()
    {
        $announcements = Announcement::orderBy('is_pinned', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(10);
            
        return view('announcements.index', compact('announcements'));
    }

    /**
     * Store a newly created announcement and send push notifications.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'nullable|string',
            'is_pinned' => 'boolean',
            'valid_until' => 'required|date|after_or_equal:today',
        ]);

        // If this is pinned, unpin others (optional, or just allow multiple pinned)
        // For now, let's allow multiple pinned, but "latest pinned" will show first.

        $announcement = Announcement::create([
            'title' => $request->title,
            'message' => $request->message,
            'is_pinned' => $request->is_pinned ?? false,
            'is_active' => true,
            'created_by' => Auth::id(),
            'valid_until' => $request->valid_until ? \Carbon\Carbon::parse($request->valid_until)->endOfDay() : null,
        ]);

        // Send Push Notification to all drivers
        $drivers = User::where('role', 'driver')->whereNotNull('fcm_token')->get();
        foreach ($drivers as $driver) {
            FirebasePushService::sendPush(
                "📢 " . $request->title,
                $request->message ? \Illuminate\Support\Str::limit($request->message, 100) : 'Tap to view announcement details',
                $driver->fcm_token,
                'announcement',
                ['announcement_id' => $announcement->id]
            );
        }

        return redirect()->back()->with('success', 'Announcement created and sent to drivers.');
    }

    /**
     * Update the specified announcement.
     */
    public function update(Request $request, $id)
    {
        $announcement = Announcement::findOrFail($id);
        
        $request->validate([
            'title' => 'string|max:255',
            'message' => 'nullable|string',
            'is_pinned' => 'boolean',
            'is_active' => 'boolean',
            'valid_until' => 'required|date|after_or_equal:today',
        ]);

        $data = $request->all();
        if ($request->has('valid_until')) {
            $data['valid_until'] = $request->valid_until ? \Carbon\Carbon::parse($request->valid_until)->endOfDay() : null;
        }

        $announcement->update($data);

        return redirect()->back()->with('success', 'Announcement updated.');
    }

    /**
     * Remove the specified announcement.
     */
    public function destroy($id)
    {
        $announcement = Announcement::findOrFail($id);
        $announcement->delete();

        return redirect()->back()->with('success', 'Announcement deleted.');
    }

    /**
     * Toggle pinned status.
     */
    public function togglePin($id)
    {
        $announcement = Announcement::findOrFail($id);
        $announcement->update(['is_pinned' => !$announcement->is_pinned]);

        return redirect()->back()->with('success', 'Pin status updated.');
    }
}
