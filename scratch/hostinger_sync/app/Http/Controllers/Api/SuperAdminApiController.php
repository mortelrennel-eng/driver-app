<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class SuperAdminApiController extends Controller
{
    public function dashboard()
    {
        return response()->json([
            'success' => true,
            'stats' => [
                'total_users'   => User::whereNotIn('role', ['super_admin'])->count(),
                'pending_users' => User::where('is_active', 0)->count(),
                'active_users'  => User::where('is_active', 1)->whereNotIn('role', ['super_admin'])->count(),
            ],
            'users' => User::whereNotIn('role', ['super_admin'])->orderByDesc('created_at')->get()
        ]);
    }

    public function approveUser($id)
    {
        $user = User::findOrFail($id);
        $user->is_active = 1;
        $user->save();
        return response()->json(['success' => true, 'message' => 'User approved successfully.']);
    }

    public function rejectUser($id)
    {
        $user = User::findOrFail($id);
        $user->delete();
        return response()->json(['success' => true, 'message' => 'User rejected and deleted.']);
    }

    public function toggleActive($id)
    {
        $user = User::findOrFail($id);
        $user->is_active = !$user->is_active;
        $user->save();
        return response()->json(['success' => true, 'is_active' => $user->is_active]);
    }

    public function updatePageAccess(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $user->page_access = json_encode($request->pages);
        $user->save();
        return response()->json(['success' => true]);
    }

    public function updateRole(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $user->role = $request->role;
        $user->save();
        return response()->json(['success' => true]);
    }

    public function deleteUser($id)
    {
        User::findOrFail($id)->delete();
        return response()->json(['success' => true]);
    }

    public function getUsers()
    {
        return response()->json(['success' => true, 'data' => User::all()]);
    }
}
