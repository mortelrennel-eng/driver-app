<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
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
        $data = [
            'units' => Unit::onlyTrashed()->get()->map(fn($u) => [
                'id' => $u->id,
                'name' => $u->plate_number,
                'archived_at' => $u->deleted_at->format('M d, Y h:i A')
            ]),
            'drivers' => Driver::onlyTrashed()->get()->map(fn($d) => [
                'id' => $d->id,
                'name' => trim($d->first_name . ' ' . $d->last_name),
                'archived_at' => $d->deleted_at->format('M d, Y h:i A')
            ]),
            'expenses' => Expense::onlyTrashed()->get()->map(fn($e) => [
                'id' => $e->id,
                'name' => $e->description . ' (₱' . number_format($e->amount, 2) . ')',
                'archived_at' => $e->deleted_at->format('M d, Y h:i A')
            ]),
            'maintenance' => Maintenance::with('unit')->onlyTrashed()->get()->map(fn($m) => [
                'id' => $m->id,
                'name' => ($m->unit->plate_number ?? 'Unknown') . ' - ' . $m->maintenance_type,
                'archived_at' => $m->deleted_at->format('M d, Y h:i A')
            ]),
            'boundaries' => Boundary::onlyTrashed()->get()->map(fn($b) => [
                'id' => $b->id,
                'name' => 'Date: ' . $b->date . ' (₱' . number_format($b->actual_boundary, 2) . ')',
                'archived_at' => $b->deleted_at->format('M d, Y h:i A')
            ]),
            'staff' => Staff::onlyTrashed()->get()->map(fn($s) => [
                'id' => $s->id,
                'name' => trim($s->first_name . ' ' . $s->last_name),
                'archived_at' => $s->deleted_at->format('M d, Y h:i A')
            ]),
            'incidents' => \App\Models\DriverBehavior::onlyTrashed()
                ->leftJoin('units as u', 'driver_behavior.unit_id', '=', 'u.id')
                ->leftJoin('drivers as d', 'driver_behavior.driver_id', '=', 'd.id')
                ->select(
                    'driver_behavior.*',
                    'u.plate_number',
                    DB::raw("TRIM(CONCAT(COALESCE(d.first_name,''), ' ', COALESCE(d.last_name,''))) as driver_name")
                )->get()->map(fn($i) => [
                    'id' => $i->id,
                    'name' => ($i->plate_number ?? 'Unknown') . ' - ' . ($i->driver_name ?? 'Unknown'),
                    'archived_at' => $i->deleted_at->format('M d, Y h:i A')
                ]),
            'pricing_rules' => BoundaryRule::onlyTrashed()->get()->map(fn($pr) => [
                'id' => $pr->id,
                'name' => $pr->name,
                'archived_at' => $pr->deleted_at->format('M d, Y h:i A')
            ]),
            'suppliers' => Supplier::onlyTrashed()->get()->map(fn($sup) => [
                'id' => $sup->id,
                'name' => $sup->name,
                'archived_at' => $sup->deleted_at->format('M d, Y h:i A')
            ]),
        ];

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    public function restore($type, $id)
    {
        $model = $this->getModelByType($type);
        if (!$model) return response()->json(['success' => false, 'message' => 'Invalid type.'], 400);

        $item = $model::withTrashed()->find($id);
        if (!$item) return response()->json(['success' => false, 'message' => 'Item not found.'], 404);

        $item->restore();
        return response()->json(['success' => true, 'message' => ucfirst($type) . ' restored successfully.']);
    }

    public function forceDelete($type, $id, Request $request)
    {
        $password = $request->input('password');
        if (!\App\Models\SystemSetting::verifyPassword($password)) {
            return response()->json(['success' => false, 'message' => 'Invalid deletion password.'], 403);
        }

        $model = $this->getModelByType($type);
        if (!$model) return response()->json(['success' => false, 'message' => 'Invalid type.'], 400);

        $item = $model::withTrashed()->find($id);
        if (!$item) return response()->json(['success' => false, 'message' => 'Item not found.'], 404);

        $item->forceDelete();
        return response()->json(['success' => true, 'message' => ucfirst($type) . ' permanently deleted.']);
    }

    private function getModelByType($type)
    {
        return match ($type) {
            'units' => Unit::class,
            'drivers' => Driver::class,
            'expenses' => Expense::class,
            'boundaries' => Boundary::class,
            'maintenance' => Maintenance::class,
            'staff' => Staff::class,
            'incidents' => \App\Models\DriverBehavior::class,
            'pricing_rules' => BoundaryRule::class,
            'suppliers' => Supplier::class,
            default => null,
        };
    }
}
