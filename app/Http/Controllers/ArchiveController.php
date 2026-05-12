<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Unit;
use App\Models\Driver;
use App\Models\Expense;
use App\Models\Boundary;
use App\Models\Maintenance;
use App\Models\FranchiseCase;
use App\Models\Staff;
use App\Models\BoundaryRule;
use App\Models\Supplier;

use Illuminate\Support\Facades\DB;

class ArchiveController extends Controller
{
    public function index()
    {
        $archivedUnits = Unit::onlyTrashed()->get();
        $archivedDrivers = Driver::onlyTrashed()->get();
        $archivedExpenses = Expense::onlyTrashed()->get();
        $archivedBoundaries = Boundary::onlyTrashed()->get();
        $archivedMaintenance = Maintenance::with('unit')->onlyTrashed()->get();
        $archivedFranchiseCases = FranchiseCase::onlyTrashed()->get();
        $archivedStaff = Staff::onlyTrashed()->get();
        $archivedIncidents = \App\Models\DriverBehavior::onlyTrashed()
            ->leftJoin('units as u', 'driver_behavior.unit_id', '=', 'u.id')
            ->leftJoin('drivers as d', 'driver_behavior.driver_id', '=', 'd.id')
            ->select(
                'driver_behavior.*',
                'u.plate_number',
                DB::raw("TRIM(CONCAT(COALESCE(d.first_name,''), ' ', COALESCE(d.last_name,''))) as driver_name")
            )->get();
        
        $archivedPricingRules = BoundaryRule::onlyTrashed()->get();
        $archivedSuppliers = Supplier::onlyTrashed()->get();

        // ─── Archived User Accounts (System Login Access) ───
        $archivedUserAccounts = \App\Models\User::onlyTrashed()
            ->where('role', '!=', 'super_admin')
            ->get();



        return view('archive.index', compact(
            'archivedUnits',
            'archivedDrivers',
            'archivedExpenses',
            'archivedBoundaries',
            'archivedMaintenance',
            'archivedFranchiseCases',
            'archivedStaff',
            'archivedIncidents',
            'archivedPricingRules',
            'archivedSuppliers',
            'archivedUserAccounts'
        ));
    }

    public function restore($type, $id)
    {
        $model = $this->getModelByType($type);
        if (!$model) {
            return back()->with('error', 'Invalid model type.');
        }

        $item = $model::withTrashed()->findOrFail($id);
        $name = $item->plate_number ?? ($item->full_name ?? ($item->name ?? ($item->case_no ?? ($item->description ?? ("ID# " . $item->id)))));
        $item->restore();

        system_log("Restored " . ucfirst($type), "Item: {$name} was restored from the system archive.");

        if (request()->wantsJson() || request()->expectsJson()) {
            return response()->json(['success' => true, 'message' => ucfirst($type) . ' restored successfully.']);
        }

        return back()->with('success', ucfirst($type) . ' restored successfully.');
    }

    public function forceDelete($type, $id, Request $request)
    {
        $password = $request->input('archive_password');
        if (!\App\Models\SystemSetting::verifyPassword($password)) {
            $msg = !\App\Models\SystemSetting::get('archive_deletion_password')
                ? 'Archive deletion password is not set. Please set it in the System Security tab.'
                : 'Invalid archive deletion password.';

            if ($request->wantsJson() || $request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }
            return back()->with('error', $msg);
        }

        $model = $this->getModelByType($type);
        if (!$model) {
            if ($request->wantsJson() || $request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Invalid model type.'], 400);
            }
            return back()->with('error', 'Invalid model type.');
        }

        $item = $model::withTrashed()->findOrFail($id);
        $name = $item->plate_number ?? ($item->full_name ?? ($item->name ?? ($item->case_no ?? ($item->description ?? ("ID# " . $item->id)))));

        // Safety: Unlink any driver records before permanently deleting a User
        if ($type === 'user') {
            Driver::where('user_id', $item->id)->update(['user_id' => null]);
        }

        $item->forceDelete();

        system_log("Permanently Deleted " . ucfirst($type), "Item: {$name} was permanently wiped from the database.");

        if ($request->wantsJson() || $request->expectsJson()) {
            return response()->json(['success' => true, 'message' => ucfirst($type) . ' permanently deleted.']);
        }
        return back()->with('success', ucfirst($type) . ' permanently deleted.');
    }

    private function getModelByType($type)
    {
        return match ($type) {
            'unit' => Unit::class,
            'driver' => Driver::class,
            'expense' => Expense::class,
            'boundary' => Boundary::class,
            'maintenance' => Maintenance::class,
            'franchise_case' => FranchiseCase::class,
            'staff' => Staff::class,
            'incident' => \App\Models\DriverBehavior::class,
            'pricing_rule' => BoundaryRule::class,
            'supplier' => Supplier::class,
            'user' => \App\Models\User::class,

            default => null,
        };
    }
}
