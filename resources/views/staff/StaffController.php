<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\ActivityLogController;

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

        // Fetch General Staff (those without accounts, excluding drivers)
        $generalQuery = \App\Models\Staff::query()->where('role', '!=', 'driver');
        if ($search) {
            $generalQuery->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('role', 'like', "%{$search}%");
            });
        }
        $generalStaff = $generalQuery->orderBy('name')->get();

        return view('staff.index', compact('generalStaff'));
    }

    public function adminIndex(Request $request)
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

        return view('staff.admin', compact('adminStaff'));
    }

    public function driversIndex(Request $request)
    {
        $search = $request->get('search');

        // Fetch Mobile App Drivers (those with accounts, role = 'driver')
        $driverQuery = \App\Models\User::query()->where('role', 'driver')->with('verifiedBrowsers');
        if ($search) {
            $driverQuery->where(function($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }
        $appDrivers = $driverQuery->orderBy('full_name')->get();

        return view('staff.drivers', compact('appDrivers'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:20', 'regex:/^[A-Za-z]+( [A-Za-z]+){0,5}$/'],
            'role' => 'required|string|max:255',
            'phone' => 'nullable|numeric|digits_between:1,11',
            'contact_person' => ['nullable', 'string', 'max:20', 'regex:/^[A-Za-z]+( [A-Za-z]+){0,5}$/'],
            'emergency_phone' => 'nullable|numeric|digits_between:1,11',
            'address' => 'nullable|string|max:200',
            'status' => 'required|in:active,inactive',
        ], [
            'name.regex' => 'The name must only contain letters and a maximum of 5 spaces.',
            'contact_person.regex' => 'The emergency contact name must only contain letters and a maximum of 5 spaces.',
            'phone.numeric' => 'The phone number must only contain digits.',
            'emergency_phone.numeric' => 'The emergency phone number must only contain digits.',
        ]);

        \App\Models\Staff::create($data);

        ActivityLogController::log('Created Staff Record', "Name: {$data['name']}\nRole: {$data['role']}");

        return back()->with('success', 'Staff record added successfully.');
    }

    public function update(Request $request, $id)
    {
        $staff = \App\Models\Staff::where('id', $id)->firstOrFail();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:20', 'regex:/^[A-Za-z]+( [A-Za-z]+){0,5}$/'],
            'role' => 'required|string|max:255',
            'phone' => 'nullable|numeric|digits_between:1,11',
            'contact_person' => ['nullable', 'string', 'max:20', 'regex:/^[A-Za-z]+( [A-Za-z]+){0,5}$/'],
            'emergency_phone' => 'nullable|numeric|digits_between:1,11',
            'address' => 'nullable|string|max:200',
            'status' => 'required|in:active,inactive',
        ], [
            'name.regex' => 'The name must only contain letters and a maximum of 5 spaces.',
            'contact_person.regex' => 'The emergency contact name must only contain letters and a maximum of 5 spaces.',
            'phone.numeric' => 'The phone number must only contain digits.',
            'emergency_phone.numeric' => 'The emergency phone number must only contain digits.',
        ]);

        $staff->update($data);

        ActivityLogController::log('Updated Staff Record', "Name: {$staff->name}\nRole: {$staff->role}");

        return back()->with('success', 'Staff record updated successfully.');
    }

    public function destroy($id)
    {
        $staff = \App\Models\Staff::where('id', $id)->firstOrFail();
        $name = $staff->name;
        $staff->delete();

        ActivityLogController::log('Archived Staff Record', "Staff: {$name} moved to archive.");

        return back()->with('success', 'Staff record archived successfully.');
    }

    public function destroyAppDriver($id)
    {
        $user = \App\Models\User::where('id', $id)->firstOrFail();
        
        // Ensure they are actually an app driver
        if ($user->role !== 'driver') {
            return back()->with('error', 'Cannot delete this record.');
        }

        $name = $user->full_name ?? $user->name;

        // Unlink the Driver record so driver data stays visible on the web
        \App\Models\Driver::where('user_id', $user->id)->update(['user_id' => null]);

        // Revoke tokens
        $user->tokens()->delete();

        // Soft-delete the user account so it goes to the Archive
        // If they need to free up the email/phone, they can permanently delete it from the Archive
        $user->delete();

        ActivityLogController::log('Deleted Mobile App Driver', "Driver Account: {$name} moved to archive.");

        return back()->with('success', 'Mobile App Driver account deleted successfully.');
    }

    public function toggleAppDriverStatus($id)
    {
        $user = \App\Models\User::where('id', $id)->where('role', 'driver')->firstOrFail();

        // Ensure only drivers can be toggled here
        if ($user->role !== 'driver') {
            return back()->with('error', 'Cannot toggle status for this account.');
        }

        $user->is_active = !$user->is_active;
        $user->save();

        $name = $user->full_name ?? $user->name;
        $statusLabel = $user->is_active ? 'Activated' : 'Deactivated';

        ActivityLogController::log('Toggled App Driver Status', "Driver: {$name} — {$statusLabel}");

        return back()->with('success', "Driver account {$statusLabel} successfully.");
    }
}


