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

        // By default, exclude auth and account management logs unless explicitly requested via filter
        if ($request->input('type') !== 'auth') {
            $query->whereNotIn('action', ['login', 'logout', 'failed_login', 'created', 'approved', 'rejected']);
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

        // Filter by action type (roughly)
        if ($request->filled('type')) {
            $type = $request->input('type');
            if ($type === 'auth') {
                $query->whereIn('action', ['login', 'logout', 'failed_login']);
            } elseif ($type === 'admin') {
                $query->whereIn('action', ['approved', 'rejected', 'role_change', 'password_reset']);
            } elseif ($type === 'system') {
                $query->whereNotIn('action', ['login', 'logout', 'failed_login', 'approved', 'rejected']);
            }
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
