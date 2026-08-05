<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class StaffController extends Controller
{
    public function index()
    {
        // Fetch users who are staff (not drivers and not super_admins)
        $staff = User::whereNotIn('role', ['driver', 'super_admin'])->get();
        return response()->json(['success' => true, 'data' => $staff]);
    }

    public function show($id)
    {
        $staff = User::findOrFail($id);
        return response()->json(['success' => true, 'data' => $staff]);
    }

    public function store(Request $request)
    {
        $user = User::create([
            'full_name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'password' => \Illuminate\Support\Facades\Hash::make($request->password ?? 'staff123'),
            'is_active' => 1
        ]);
        return response()->json(['success' => true, 'id' => $user->id]);
    }
}
