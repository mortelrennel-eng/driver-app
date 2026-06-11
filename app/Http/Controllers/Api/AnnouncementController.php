<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use Illuminate\Http\Request;

class AnnouncementController extends Controller
{
    /**
     * Get announcements for the driver.
     */
    public function index()
    {
        $announcements = Announcement::where('is_active', true)
            ->orderBy('is_pinned', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'announcements' => $announcements
        ]);
    }

    /**
     * Get the latest pinned announcement for the dashboard.
     */
    public function latest()
    {
        $announcement = Announcement::where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('valid_until')
                      ->orWhere('valid_until', '>=', now()->startOfDay());
            })
            ->orderBy('is_pinned', 'desc')
            ->orderBy('created_at', 'desc')
            ->first();

        return response()->json([
            'success' => true,
            'announcement' => $announcement
        ]);
    }
}
