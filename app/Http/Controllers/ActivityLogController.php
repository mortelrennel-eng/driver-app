<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LoginAudit;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class ActivityLogController extends Controller
{
    /**
     * Display a listing of system activity logs.
     */
    public function index(Request $request)
    {
        $query = LoginAudit::with('user')->orderByDesc('created_at');

        $type = $request->input('type');

        // Apply Category Filters
        if ($type === 'auth') {
            // Only show auth-related logs
            $query->whereIn('action', ['login', 'logout', 'failed_login']);
        } elseif ($type === 'admin') {
            // Show administrative actions (staff/user management, approvals, roles, etc.)
            $query->where(function ($q) {
                $q->where('action', 'like', '%approved%')
                  ->orWhere('action', 'like', '%rejected%')
                  ->orWhere('action', 'like', '%role%')
                  ->orWhere('action', 'like', '%password%')
                  ->orWhere('action', 'like', '%user%')
                  ->orWhere('action', 'like', '%staff%')
                  ->orWhere('action', 'like', '%driver%')
                  ->orWhere('action', 'like', '%term%');
            });
        } elseif ($type === 'system') {
            // Show system logic (units, boundaries, maintenance, inventory, etc) 
            // Exclude auth and admin-heavy terms
            $query->whereNotIn('action', ['login', 'logout', 'failed_login'])
                  ->where('action', 'not like', '%user%')
                  ->where('action', 'not like', '%staff%')
                  ->where('action', 'not like', '%driver%');
        } else {
            // Default "All Activities": Show everything EXCEPT the spammy auth logs
            $query->whereNotIn('action', ['login', 'logout', 'failed_login']);
        }

        // Search by name, email, action, or notes
        if ($request->filled('search')) {
            $s = $request->input('search');
            $query->where(function ($q) use ($s) {
                $q->where('user_name', 'like', "%$s%")
                  ->orWhere('user_email', 'like', "%$s%")
                  ->orWhere('action', 'like', "%$s%")
                  ->orWhere('notes', 'like', "%$s%");
            });
        }

        // Filter by role
        if ($request->filled('role')) {
            $query->where('user_role', $request->input('role'));
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->input('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->input('date_to'));
        }

        $logs = $query->paginate(50)->withQueryString();

        return view('activity-log.index', compact('logs'));
    }

    /**
     * Helper method to log an activity (static utility)
     */
    public static function log(string $action, string $notes = null): void
    {
        LoginAudit::log($action, Auth::user(), $notes);
    }
}
