<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class StaffController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function index(Request $request)
    {
        $search = $request->get('search');

        // Fetch Admin/Web Staff (those with accounts, excluding drivers)
        $adminQuery = \App\Models\User::query()->where('role', '!=', 'driver');
        if ($search) {
            $adminQuery->where(function($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('role', 'like', "%{$search}%");
            });
        }
        $adminStaff = $adminQuery->orderBy('full_name')->get();

        // Fetch General Staff (those without accounts, excluding drivers)
        $generalQuery = \App\Models\Staff::query()->where('role', '!=', 'driver');
        if ($search) {
            $generalQuery->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('role', 'like', "%{$search}%");
            });
        }
        $generalStaff = $generalQuery->orderBy('name')->get();

        return view('staff.index', compact('adminStaff', 'generalStaff'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'role' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'status' => 'required|in:active,inactive',
        ]);

        \App\Models\Staff::create($data);

        return redirect()->route('staff.index')->with('success', 'Staff record added successfully.');
    }

    public function update(Request $request, $id)
    {
        $staff = \App\Models\Staff::findOrFail($id);

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'role' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'status' => 'required|in:active,inactive',
        ]);

        $staff->update($data);

        return redirect()->route('staff.index')->with('success', 'Staff record updated successfully.');
    }

    public function destroy($id)
    {
        $staff = \App\Models\Staff::findOrFail($id);
        $staff->delete();

        return redirect()->route('staff.index')->with('success', 'Staff record archived successfully.');
    }
}
