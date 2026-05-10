<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\LoginAudit;
use App\Models\SystemSetting;
use App\Models\IncidentClassification;
use App\Models\Role;
use App\Models\Driver;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SuperAdminController extends Controller
{
    // ─── Centralized page definitions (route => label) ────────────────────────
    public static array $pageDefinitions = [
        // ─── Core Management ───────────────────
        'dashboard' => ['icon' => 'layout-dashboard', 'label' => 'Dashboard', 'group' => '1. Core Management'],
        'units.*' => ['icon' => 'car', 'label' => 'Unit Management', 'group' => '1. Core Management'],
        'driver-management.*' => ['icon' => 'users', 'label' => 'Driver Management', 'group' => '1. Core Management'],
        'activity-logs.*' => ['icon' => 'history', 'label' => 'History Logs', 'group' => '1. Core Management'],

        // ─── Operations ────────────────────────
        'live-tracking.*' => ['icon' => 'map-pin', 'label' => 'Live Tracking', 'group' => '2. Operations'],
        'maintenance.*' => ['icon' => 'wrench', 'label' => 'Maintenance', 'group' => '2. Operations'],
        'coding.*' => ['icon' => 'calendar', 'label' => 'Coding Management', 'group' => '2. Operations'],
        'driver-behavior.*' => ['icon' => 'alert-triangle', 'label' => 'Driver Behavior', 'group' => '2. Operations'],
        'spare-parts.*' => ['icon' => 'package', 'label' => 'Spare Parts Inventory', 'group' => '2. Operations'],
        'suppliers.*' => ['icon' => 'truck', 'label' => 'Suppliers', 'group' => '2. Operations'],

        // ─── Financial ─────────────────────────
        'boundaries.*' => ['icon' => 'banknote', 'label' => 'Boundaries', 'group' => '3. Financial'],
        'office-expenses.*' => ['icon' => 'receipt', 'label' => 'Office Expenses', 'group' => '3. Financial'],
        'salary.*' => ['icon' => 'calculator', 'label' => 'Salary Management', 'group' => '3. Financial'],
        'boundary-rules.*' => ['icon' => 'settings', 'label' => 'Boundary Rules', 'group' => '3. Financial'],

        // ─── Legal & Admin ─────────────────────
        'decision-management.*' => ['icon' => 'file-text', 'label' => 'Franchise', 'group' => '4. Legal & Admin'],
        'staff.*' => ['icon' => 'user-cog', 'label' => 'Staff Records', 'group' => '4. Legal & Admin'],
        'archive.*' => ['icon' => 'archive', 'label' => 'Archive Access', 'group' => '4. Legal & Admin'],
        'support.*' => ['icon' => 'message-square', 'label' => 'Support Center', 'group' => '4. Legal & Admin'],

        // ─── Reports ───────────────────────────
        'analytics.*' => ['icon' => 'bar-chart', 'label' => 'Analytics', 'group' => '5. Reports'],
        'unit-profitability.*' => ['icon' => 'trending-up', 'label' => 'Unit Profitability', 'group' => '5. Reports'],
    ];

    // ─── Dashboard ────────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $tab = $request->get('tab', 'overview');

        // Stats
        $totalUsers = User::whereNotIn('role', ['super_admin'])->count();
        $activeUsers = User::whereNotIn('role', ['super_admin'])->where('is_active', true)->where('approval_status', 'approved')->count();
        $rejectedUsers = User::whereNotIn('role', ['super_admin'])->where('approval_status', 'rejected')->count();

        // Recent login audit (for overview) - Filter for only login-related activity
        $recentAudit = LoginAudit::whereIn('action', ['login', 'failed_login', 'logout'])
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        // Users
        $allUsers = User::whereNotIn('role', ['super_admin'])
            ->withTrashed()
            ->orderByRaw("FIELD(approval_status, 'pending', 'approved', 'rejected')")
            ->orderByDesc('created_at')
            ->get();

        // Paginated audit log - Filter for login-related activity for this tab
        $auditLog = LoginAudit::whereIn('action', ['login', 'failed_login', 'logout', 'approved', 'rejected', 'password_changed', 'created'])
            ->orderByDesc('created_at')
            ->paginate(25);
        // Classifications
        $classifications = IncidentClassification::orderBy('name')->get();
        $archivedClassifications = IncidentClassification::onlyTrashed()->orderBy('name')->get();

        // Roles
        $roles = Role::orderBy('label')->get();
        $archivedRoles = Role::onlyTrashed()->orderBy('label')->get();

        return view('super-admin.index', compact(
            'tab',
            'totalUsers',
            'activeUsers',
            'rejectedUsers',
            'recentAudit',
            'allUsers',
            'auditLog',
            'classifications',
            'archivedClassifications',
            'roles',
            'archivedRoles'
        ));
    }

    public function indexJson(Request $request)
    {
        if (Auth::user()->role !== 'super_admin') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $totalUsers = User::whereNotIn('role', ['super_admin'])->count();
        $activeUsers = User::whereNotIn('role', ['super_admin'])->where('is_active', true)->where('approval_status', 'approved')->count();
        $rejectedUsers = User::whereNotIn('role', ['super_admin'])->where('approval_status', 'rejected')->count();

        $recentAudit = LoginAudit::whereIn('action', ['login', 'failed_login', 'logout'])
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        $allUsers = User::whereNotIn('role', ['super_admin'])
            ->orderByRaw("FIELD(approval_status, 'pending', 'approved', 'rejected')")
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'success' => true,
            'stats' => [
                'total_users' => $totalUsers,
                'active_users' => $activeUsers,
                'rejected_users' => $rejectedUsers,
            ],
            'recentAudit' => $recentAudit,
            'allUsers' => $allUsers,
            'archivedUsers' => User::onlyTrashed()->orderBy('deleted_at', 'desc')->get(),
            'roles' => Role::orderBy('label')->get(),
            'archivedRoles' => Role::onlyTrashed()->orderBy('label')->get()
        ]);
    }

    // ─── Approve User ─────────────────────────────────────────────────────────

    public function approveUser(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $user->update([
            'approval_status' => 'approved',
            'is_active' => true,
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);

        LoginAudit::log('approved', $user, 'Account approved by ' . Auth::user()->full_name);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => $user->full_name . '\'s account has been approved.']);
        }

        return back()->with('success', $user->full_name . '\'s account has been approved and is now active.');
    }

    // ─── Reject User ──────────────────────────────────────────────────────────

    public function rejectUser(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $user->update([
            'approval_status' => 'rejected',
            'is_active' => false,
        ]);

        LoginAudit::log('rejected', $user, 'Account rejected by ' . Auth::user()->full_name);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => $user->full_name . '\'s account has been rejected.']);
        }

        return back()->with('success', $user->full_name . '\'s account has been rejected.');
    }

    // ─── Toggle Active Status ─────────────────────────────────────────────────

    public function toggleDisable(Request $request, $id)
    {
        $user = User::findOrFail($id);

        if ($user->role === 'super_admin') {
            return response()->json(['success' => false, 'message' => 'Cannot disable the Super Admin account.'], 403);
        }

        $is_disabled = $request->input('is_disabled');
        $reason = $request->input('reason');

        $user->update([
            'is_disabled' => $is_disabled,
            'disable_reason' => $is_disabled ? $reason : null,
        ]);

        $action = $is_disabled ? 'account_disabled' : 'account_enabled';
        LoginAudit::log($is_disabled ? 'rejected' : 'approved', $user, 'Account ' . ($is_disabled ? 'disabled' : 'enabled') . ' by ' . Auth::user()->full_name . ($reason ? ' Reason: ' . $reason : ''));

        return response()->json([
            'success' => true,
            'is_disabled' => $user->is_disabled,
            'message' => 'Account ' . ($user->is_disabled ? 'disabled' : 'enabled') . ' successfully.'
        ]);
    }

    // ─── Update Page Access ───────────────────────────────────────────────────

    public function updatePageAccess(Request $request, $id)
    {
        $user = User::findOrFail($id);

        if ($user->role === 'super_admin') {
            return response()->json(['success' => false, 'message' => 'Cannot restrict Super Admin pages.'], 403);
        }

        $pages = $request->input('pages', null);

        // null = no restriction, [] = all blocked, [...] = specific pages allowed
        $user->update(['allowed_pages' => $pages]);

        return response()->json([
            'success' => true,
            'message' => 'Page access updated for ' . $user->full_name . '.',
        ]);
    }

    // ─── Login History (Paginated JSON) ───────────────────────────────────────

    public function loginHistory(Request $request)
    {
        $query = LoginAudit::whereIn('action', ['login', 'failed_login', 'logout', 'approved', 'rejected', 'password_changed', 'created'])
            ->orderByDesc('created_at');

        if ($request->filled('search')) {
            $s = $request->input('search');
            $query->where(function ($q) use ($s) {
                $q->where('user_name', 'like', "%$s%")
                    ->orWhere('user_email', 'like', "%$s%")
                    ->orWhere('ip_address', 'like', "%$s%");
            });
        }

        if ($request->filled('action')) {
            $query->where('action', $request->input('action'));
        }

        if ($request->filled('role')) {
            $query->where('user_role', $request->input('role'));
        }

        $perPage = min((int) $request->input('per_page', 25), 100);
        $results = $query->paginate($perPage);

        return response()->json($results);
    }

    // ─── Delete / Restore User ────────────────────────────────────────────────

    public function archiveUser(Request $request, $id)
    {
        $user = User::findOrFail($id);

        if ($user->role === 'super_admin') {
            return response()->json(['success' => false, 'message' => 'Cannot archive the Super Admin account.'], 403);
        }

        $user->delete(); // Soft delete
        LoginAudit::log('rejected', $user, 'Account archived by ' . Auth::user()->full_name);

        return response()->json(['success' => true, 'message' => $user->full_name . ' has been moved to archives.']);
    }

    public function restoreUser(Request $request, $id)
    {
        $user = User::withTrashed()->findOrFail($id);
        $user->restore();

        LoginAudit::log('approved', $user, 'Account restored by ' . Auth::user()->full_name);

        return response()->json(['success' => true, 'message' => $user->full_name . ' has been restored.']);
    }

    public function updateUser(Request $request, $id)
    {
        $user = User::withTrashed()->findOrFail($id);

        if ($user->role === 'super_admin' && Auth::user()->id != $user->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $data = $request->validate([
            'first_name' => 'required|string|max:50',
            'last_name' => 'required|string|max:50',
            'email' => 'required|email|unique:users,email,' . $id,
            'role' => 'required|string',
            'phone_number' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
        ]);

        $data['full_name'] = $data['first_name'] . ' ' . $data['last_name'];
        $data['name'] = $data['full_name'];

        $user->update($data);

        LoginAudit::log('approved', $user, 'Account details updated by ' . Auth::user()->full_name);

        return response()->json(['success' => true, 'message' => 'User account updated successfully.']);
    }

    // ─── Get User Details & History ───────────────────────────────────────────

    public function getUserDetails(Request $request, $id)
    {
        $user = User::withTrashed()->findOrFail($id);
        $history = LoginAudit::where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->limit(50)
            ->get();

        // Append profile image url for easier frontend handling
        $profileUrl = $user->profile_image ? asset('storage/' . $user->profile_image) : null;

        return response()->json([
            'success' => true,
            'user' => [
                'id' => $user->id,
                'full_name' => $user->full_name,
                'email' => $user->email,
                'phone_number' => $user->phone_number,
                'role' => $user->role,
                'status' => $user->approval_status,
                'is_active' => $user->is_active,
                'is_disabled' => $user->is_disabled,
                'trashed' => $user->trashed(),
                'must_change_password' => $user->must_change_password,
                'last_login' => $user->last_login,
                'created_at' => $user->created_at->format('M d, Y h:i A'),
                'profile_url' => $profileUrl,
                'initials' => strtoupper(substr($user->full_name ?? 'U', 0, 1))
            ],
            'history' => $history
        ]);
    }

    // ─── Reset Password (Super Admin override) ────────────────────────────────

    public function resetPassword(Request $request, $id)
    {
        $request->validate(['password' => 'required|string|min:6']);

        $user = User::findOrFail($id);

        if ($user->role === 'super_admin' && Auth::user()->role !== 'super_admin') {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $hashed = Hash::make($request->input('password'));
        $user->update(['password' => $hashed, 'password_hash' => $hashed]);

        LoginAudit::log('approved', $user, 'Password reset by ' . Auth::user()->full_name);

        return response()->json(['success' => true, 'message' => 'Password has been reset for ' . $user->full_name . '.']);
    }
    // ─── Update User Role ───────────────────────────────────────────────────
    public function updateRole(Request $request, $id)
    {
        $request->validate(['role' => 'required|string|in:manager,dispatcher,secretary,staff']);

        $user = User::findOrFail($id);

        if ($user->role === 'super_admin') {
            return response()->json(['success' => false, 'message' => 'Cannot change the Super Admin role.'], 403);
        }

        $oldRole = $user->role;
        $user->update(['role' => $request->input('role')]);

        LoginAudit::log('approved', $user, 'Role changed from ' . $oldRole . ' to ' . $user->role . ' by ' . Auth::user()->full_name);

        return response()->json(['success' => true, 'message' => 'Role updated for ' . $user->full_name . '.']);
    }

    // ─── CREATE STAFF ACCOUNT (Super Admin only) ──────────────────────────────
    public function storeStaff(Request $request)
    {
        $validRoles = Role::pluck('name')->toArray();
        $roleIn = implode(',', $validRoles);

        $request->validate([
            'first_name' => 'required|string|max:50',
            'last_name' => 'required|string|max:50',
            'email' => 'required|email|unique:users,email',
            'role' => 'required|in:' . $roleIn,
            'phone_number' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
        ]);

        // Auto-generate a secure temp password
        $tempPassword = strtoupper(substr(str_shuffle('abcdefghjkmnpqrstuvwxyz'), 0, 3))
            . rand(100, 999)
            . str_shuffle('!@#$%')[0];

        $user = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'full_name' => $request->first_name . ' ' . $request->last_name,
            'name' => $request->first_name . ' ' . $request->last_name,
            'username' => strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $request->first_name . $request->last_name)) . rand(100, 999),
            'email' => $request->email,
            'phone_number' => $request->phone_number,
            'address' => $request->address,
            'role' => $request->role,
            'password' => Hash::make($tempPassword),
            'password_hash' => Hash::make($tempPassword),
            'must_change_password' => true,
            'temp_password' => $tempPassword,
            'is_active' => true,
            'is_verified' => true,
            'approval_status' => 'approved',
        ]);

        // Send welcome email with temp password
        try {
            $mailResult = Mail::to($user->email)->send(new \App\Mail\StaffWelcomeMail($user, $tempPassword));
        } catch (\Throwable $e) {
            Log::error('StaffWelcomeMail failed: ' . $e->getMessage());
        }

        LoginAudit::log('created', $user, 'Staff account created by ' . Auth::user()->full_name . ' with role: ' . $user->role);

        return response()->json([
            'success' => true,
            'message' => 'Staff account created! Credentials sent to ' . $user->email,
            'temp_password' => $tempPassword,
        ]);
    }

    // ─── Incident Classification Management ───────────────────────────────────
    public function storeClassification(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|unique:incident_classifications,name',
            'default_severity' => 'required|in:low,medium,high,critical',
            'color' => 'required|string',
            'icon' => 'required|string',
            'behavior_mode' => 'nullable|in:narrative,complaint,traffic,damage,security',
            'sub_options' => 'nullable|array',
            'sub_options.*' => 'string|max:100',
            'auto_ban_trigger' => 'nullable|boolean',
            'ban_trigger_value' => 'nullable|string|max:100',
            'show_not_at_fault' => 'nullable|boolean',
        ]);

        $data['behavior_mode'] = $data['behavior_mode'] ?? 'narrative';
        $data['sub_options'] = $data['sub_options'] ?? null;
        $data['auto_ban_trigger'] = (bool) ($data['auto_ban_trigger'] ?? false);
        $data['show_not_at_fault'] = (bool) ($data['show_not_at_fault'] ?? false);

        $item = IncidentClassification::create($data);

        return response()->json(['success' => true, 'data' => $item, 'message' => 'New incident classification added!']);
    }

    public function getClassificationDetails($id)
    {
        $item = IncidentClassification::withTrashed()->findOrFail($id);
        return response()->json(['success' => true, 'data' => $item]);
    }

    public function updateClassification(Request $request, $id)
    {
        Log::info("Updating Classification ID: {$id}", $request->all());
        $item = IncidentClassification::findOrFail($id);

        $data = $request->validate([
            'name' => 'required|string|unique:incident_classifications,name,' . $id,
            'default_severity' => 'required|in:low,medium,high,critical',
            'color' => 'required|string',
            'icon' => 'required|string',
            'behavior_mode' => 'nullable|in:narrative,complaint,traffic,damage,security',
            'sub_options' => 'nullable|array',
            'sub_options.*' => 'string|max:100',
            'auto_ban_trigger' => 'nullable|boolean',
            'ban_trigger_value' => 'nullable|string|max:100',
            'show_not_at_fault' => 'nullable|boolean',
        ]);

        $data['sub_options'] = $data['sub_options'] ?? null;
        $data['auto_ban_trigger'] = (bool) ($data['auto_ban_trigger'] ?? false);
        $data['show_not_at_fault'] = (bool) ($data['show_not_at_fault'] ?? false);
        $data['behavior_mode'] = $data['behavior_mode'] ?? 'narrative';

        $item->update($data);

        return response()->json(['success' => true, 'data' => $item, 'message' => 'Classification updated successfully.']);
    }

    public function archiveClassification($id, Request $request)
    {
        try {
            $item = IncidentClassification::findOrFail($id);
            $item->delete();
            return response()->json(['success' => true, 'message' => 'Classification moved to Archive.']);
        } catch (\Exception $e) {
            Log::error("Archive Classification Error: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    public function restoreClassification($id)
    {
        $item = IncidentClassification::withTrashed()->findOrFail($id);
        $item->restore();

        return response()->json(['success' => true, 'message' => 'Classification restored.']);
    }



    // ─── Role Management ───────────────────────────────────────────────────────
    public function storeRole(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|unique:roles,name',
            'label' => 'required|string',
            'description' => 'nullable|string',
        ]);

        $role = Role::create($data);

        return response()->json(['success' => true, 'data' => $role, 'message' => 'New system role added!']);
    }

    public function updateRoleDetail(Request $request, $id)
    {
        $role = Role::findOrFail($id);

        $data = $request->validate([
            'name' => 'required|string|unique:roles,name,' . $id,
            'label' => 'required|string',
            'description' => 'nullable|string',
        ]);

        $role->update($data);

        return response()->json(['success' => true, 'data' => $role, 'message' => 'Role updated successfully.']);
    }

    public function archiveRole($id)
    {
        $role = Role::findOrFail($id);
        $role->delete();

        return response()->json(['success' => true, 'message' => 'Role moved to archive.']);
    }

    public function restoreRole($id)
    {
        $role = Role::withTrashed()->findOrFail($id);
        $role->restore();

        return response()->json(['success' => true, 'message' => 'Role restored.']);
    }

    public function deleteRole($id, Request $request)
    {
        try {
            $this->verifyArchivePassword($request);
            $role = Role::withTrashed()->findOrFail($id);
            $role->forceDelete();
            return response()->json(['success' => true, 'message' => 'Role permanently deleted.']);
        } catch (\Exception $e) {
            $code = ($e instanceof \Symfony\Component\HttpKernel\Exception\HttpException) ? $e->getStatusCode() : 403;
            return response()->json(['success' => false, 'message' => $e->getMessage()], $code);
        }
    }

    public function deleteUser($id, Request $request)
    {
        $this->verifyArchivePassword($request);

        $user = User::withTrashed()->findOrFail($id);

        if ($user->role === 'super_admin') {
            return response()->json(['success' => false, 'message' => 'Cannot delete the Super Admin.'], 403);
        }

        // Unlink from Driver record if exists to prevent CASCADE delete from DB
        Driver::where('user_id', $user->id)->update(['user_id' => null]);

        $user->forceDelete();
        return response()->json(['success' => true, 'message' => 'User permanently deleted (Driver record preserved).']);
    }

    public function updateArchivePassword(Request $request)
    {
        $request->validate([
            'archive_password' => 'required|string|min:6|confirmed',
        ]);

        $hashed = Hash::make($request->archive_password);

        SystemSetting::updateOrCreate(
            ['key' => 'archive_deletion_password'],
            ['value' => $hashed, 'group' => 'security']
        );

        return response()->json(['success' => true, 'message' => 'Archive deletion password updated successfully.']);
    }

    public function deleteClassification($id, Request $request)
    {
        try {
            $this->verifyArchivePassword($request);

            $item = IncidentClassification::withTrashed()->findOrFail($id);
            $item->forceDelete();

            return response()->json(['success' => true, 'message' => 'Classification permanently deleted.']);
        } catch (\Exception $e) {
            $code = ($e instanceof \Symfony\Component\HttpKernel\Exception\HttpException) ? $e->getStatusCode() : 500;
            return response()->json(['success' => false, 'message' => $e->getMessage()], $code);
        }
    }

    private function verifyArchivePassword(Request $request)
    {
        $password = $request->input('archive_password');

        if (!SystemSetting::verifyPassword($password)) {
            $msg = !SystemSetting::get('archive_deletion_password')
                ? 'Archive deletion password is not set. Please set it in the System Security tab.'
                : 'Invalid archive deletion password.';
            throw new \Exception($msg);
        }
    }
}