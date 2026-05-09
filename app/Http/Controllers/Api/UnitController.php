<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Unit;

class UnitController extends Controller
{
    /**
     * Display a listing of the units — Web-parity data.
     */
    public function index(Request $request)
    {
        $search = $request->input('search', '');
        $statusFilter = $request->input('status', '');
        $sort = $request->input('sort', 'alphabetical');

        $query = DB::table('units as u')
            ->whereNull('u.deleted_at')
            ->leftJoin('drivers as d1', 'u.driver_id', '=', 'd1.id')
            ->leftJoin('drivers as d2', 'u.secondary_driver_id', '=', 'd2.id')
            ->select(
                'u.id',
                'u.plate_number',
                'u.make',
                'u.model',
                'u.year',
                'u.status',
                'u.motor_no',
                'u.chassis_no',
                'u.gps_device_count',
                'u.imei',
                'u.current_gps_odo',
                'u.last_service_odo_gps',
                'u.boundary_rate',
                'u.purchase_cost',
                'u.unit_type',
                'u.driver_id',
                'u.secondary_driver_id',
                'u.purchase_date',
                DB::raw("CONCAT(COALESCE(d1.first_name,''), ' ', COALESCE(d1.last_name,'')) as primary_driver_name"),
                DB::raw("CONCAT(COALESCE(d2.first_name,''), ' ', COALESCE(d2.last_name,'')) as secondary_driver_name")
            );

        // Search filter
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('u.plate_number', 'like', "%$search%")
                  ->orWhere('u.model', 'like', "%$search%")
                  ->orWhere('u.make', 'like', "%$search%")
                  ->orWhere(DB::raw("CONCAT(COALESCE(d1.first_name,''), ' ', COALESCE(d1.last_name,''))"), 'like', "%$search%")
                  ->orWhere(DB::raw("CONCAT(COALESCE(d2.first_name,''), ' ', COALESCE(d2.last_name,''))"), 'like', "%$search%");
            });
        }

        // Status filter
        if ($statusFilter) {
            $query->where('u.status', $statusFilter);
        }

        // Sort
        switch ($sort) {
            case 'newest':
                $query->orderByDesc('u.created_at');
                break;
            case 'oldest':
                $query->orderBy('u.created_at');
                break;
            default:
                $query->orderBy('u.plate_number');
        }

        $rawUnits = $query->get();

        $units = $rawUnits->map(function ($u) {
            // Derive rate label from unit_type (matching what the web shows)
            $rateLabel = match(strtolower($u->unit_type ?? '')) {
                'new'    => 'Sunday Discount',
                'old'    => 'Standard Rate',
                'rented' => 'Rental Rate',
                default  => 'Standard Rate',
            };

            // ROI calculation
            $boundaryCollected = (float) DB::table('boundaries')
                ->where('unit_id', $u->id)
                ->whereNull('deleted_at')
                ->whereIn('status', ['paid', 'excess', 'shortage'])
                ->sum('actual_boundary');

            $maintenanceCost = (float) DB::table('maintenance')
                ->where('unit_id', $u->id)
                ->whereNull('deleted_at')
                ->where('status', '!=', 'cancelled')
                ->sum('cost');

            $totalInvestment = (float)($u->purchase_cost ?? 0) + $maintenanceCost;
            $roiPercentage = $totalInvestment > 0 ? ($boundaryCollected / $totalInvestment) * 100 : 0;
            $isRoi = $boundaryCollected >= $totalInvestment && $totalInvestment > 0;

            $d1Name = trim($u->primary_driver_name ?? '');
            $d2Name = trim($u->secondary_driver_name ?? '');

            return [
                'id'                   => $u->id,
                'plate_number'         => $u->plate_number,
                'make'                 => $u->make ?? '',
                'model'                => $u->model ?? '',
                'year'                 => $u->year ?? '',
                'motor_no'             => $u->motor_no ?? '—',
                'chassis_no'           => $u->chassis_no ?? '—',
                'gps_device_count'     => (int)($u->gps_device_count ?? 0),
                'imei'                 => $u->imei,
                'current_gps_odo'      => (float)($u->current_gps_odo ?? 0),
                'last_service_odo_gps' => (float)($u->last_service_odo_gps ?? 0),
                'status'               => $u->status ?? 'active',
                'boundary_rate'        => (float)($u->boundary_rate ?? 0),
                'rate_label'           => $rateLabel,
                'driver_id'            => $u->driver_id,
                'secondary_driver_id'  => $u->secondary_driver_id,
                'primary_driver'       => $d1Name ?: null,
                'secondary_driver'     => $d2Name ?: null,
                'purchase_cost'        => (float)($u->purchase_cost ?? 0),
                'maintenance_cost'     => $maintenanceCost,
                'revenue'              => $boundaryCollected,
                'roi'                  => $isRoi,
                'roi_percentage'       => round($roiPercentage, 2),
            ];
        });

        // Compute stats matching the web's quick-stats bar
        $all = DB::table('units')->whereNull('deleted_at')->get(['status']);
        $stats = [
            'total'       => $all->count(),
            'on_road'     => $all->where('status', 'active')->count(),
            'garage'      => $all->where('status', 'at_risk')->count(),
            'workshop'    => $all->where('status', 'maintenance')->count(),
            'coding'      => $all->where('status', 'coding')->count(),
        ];

        return response()->json([
            'success' => true,
            'data'    => $units,
            'stats'   => $stats,
        ]);
    }

    /**
     * Store a new unit.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'plate_number'   => 'required|string|unique:units,plate_number|max:20',
            'make'           => 'required|string|max:50',
            'model'          => 'required|string|max:50',
            'year'           => 'required|integer|min:1990|max:2100',
            'motor_no'       => 'required|string|max:191',
            'chassis_no'     => 'required|string|max:191',
            'boundary_rate'  => 'required|numeric|min:0',
            'purchase_cost'  => 'nullable|numeric|min:0',
        ]);

        $unit = DB::table('units')->insertGetId(array_merge($validated, [
            'status'     => 'active',
            'unit_type'  => $validated['boundary_rate'] > 1000 ? 'new' : 'old',
            'created_at' => now(),
            'updated_at' => now(),
        ]));

        return response()->json(['success' => true, 'message' => 'Unit added successfully.', 'id' => $unit], 201);
    }

    /**
     * Display full unit details for the mobile Unit Detail page.
     */
    public function show($id)
    {
        $unit = DB::table('units')->whereNull('deleted_at')->where('id', $id)->first();
        if (!$unit) {
            return response()->json(['success' => false, 'message' => 'Unit not found.'], 404);
        }

        // Drivers with contacts
        $d1Raw = $unit->driver_id ? DB::table('drivers')->where('id', $unit->driver_id)->first(['first_name','last_name','contact_number','license_number','hire_date','license_expiry']) : null;
        $d2Raw = $unit->secondary_driver_id ? DB::table('drivers')->where('id', $unit->secondary_driver_id)->first(['first_name','last_name','contact_number','license_number','hire_date','license_expiry']) : null;
        $d1 = $d1Raw ? (object)array_merge((array)$d1Raw, ['full_name' => trim(($d1Raw->first_name ?? '') . ' ' . ($d1Raw->last_name ?? ''))]) : null;
        $d2 = $d2Raw ? (object)array_merge((array)$d2Raw, ['full_name' => trim(($d2Raw->first_name ?? '') . ' ' . ($d2Raw->last_name ?? ''))]) : null;

        // Maintenance records
        $maintenanceRecords = DB::table('maintenance as m')
            ->leftJoin('drivers as d', 'm.driver_id', '=', 'd.id')
            ->where('m.unit_id', $id)->whereNull('m.deleted_at')
            ->orderByDesc('m.date_started')->limit(20)
            ->select(
                'm.id','m.maintenance_type','m.status','m.date_started','m.date_completed',
                'm.cost','m.description','m.mechanic_name',
                DB::raw("TRIM(CONCAT(COALESCE(d.first_name,''), ' ', COALESCE(d.last_name,''))) as driver_name")
            )
            ->get();

        // Attach detailed parts and services
        foreach ($maintenanceRecords as $record) {
            $items = DB::table('maintenance_parts')->where('maintenance_id', $record->id)->get();
            $record->parts = $items->whereNotNull('part_id')->values();
            $record->others = $items->whereNull('part_id')->values();
            $record->parts_subtotal = (float)$record->parts->sum('total');
            $record->others_subtotal = (float)$record->others->sum('total');
        }

        // Boundary history
        $boundaryHistory = DB::table('boundaries as b')
            ->leftJoin('drivers as d', 'b.driver_id', '=', 'd.id')
            ->where('b.unit_id', $id)->whereNull('b.deleted_at')
            ->orderByDesc('b.date')->limit(20)
            ->select('b.date','b.actual_boundary','b.status','b.notes as remarks', DB::raw("CONCAT(d.first_name, ' ', d.last_name) as full_name"))
            ->get();

        // Coding info
        $lastDigit  = substr($unit->plate_number ?? '', -1);
        $dayMap = ['1'=>'Monday','2'=>'Monday','3'=>'Tuesday','4'=>'Tuesday','5'=>'Wednesday','6'=>'Wednesday','7'=>'Thursday','8'=>'Thursday','9'=>'Friday','0'=>'Friday'];
        $codingDay  = $dayMap[$lastDigit] ?? 'Unknown';
        $nextCoding = null; $daysUntil = 0;
        $dayIndex   = ['Monday'=>1,'Tuesday'=>2,'Wednesday'=>3,'Thursday'=>4,'Friday'=>5];
        if (isset($dayIndex[$codingDay])) {
            $now = \Carbon\Carbon::now(); $target = $dayIndex[$codingDay];
            $today = (int)$now->format('N');
            $diff  = ($target - $today + 7) % 7;
            $daysUntil = $diff; $nextCoding = $now->copy()->addDays($diff)->format('M d, Y');
        }

        // ROI Logic Sync with Web Dashboard
        $revenue    = (float) DB::table('boundaries')->where('unit_id',$id)->whereNull('deleted_at')->whereIn('status',['paid','excess','shortage'])->sum('actual_boundary');
        
        // "Avg Monthly Revenue" in the web actually refers to the CURRENT month's boundary collection
        $monthly    = (float) DB::table('boundaries')
                        ->where('unit_id', $id)
                        ->whereNull('deleted_at')
                        ->whereIn('status', ['paid', 'excess', 'shortage'])
                        ->whereMonth('date', now()->month)
                        ->whereYear('date', now()->year)
                        ->sum('actual_boundary');

        $maintCost  = (float) DB::table('maintenance')->where('unit_id',$id)->whereNull('deleted_at')->where('status','!=','cancelled')->sum('cost');
        
        $purchaseCost = (float)($unit->purchase_cost ?? 0);
        $netProfit    = $revenue - $maintCost;
        $roiPct       = $purchaseCost > 0 ? ($netProfit / $purchaseCost) * 100 : 0;
        
        $payback      = $monthly > 0 ? ($purchaseCost / $monthly) : 0;
        $target       = $purchaseCost / 12; // 1-year payback goal base on purchase cost

        $investment = $purchaseCost; // Match web's "Total Investment" card which shows purchase cost only

        $rateLabel = match(true) {
            str_contains(strtolower($unit->unit_type ?? ''), 'rent') => 'Sunday Discount',
            str_contains(strtolower($unit->unit_type ?? ''), 'new')  => 'New Unit Rate',
            default => 'Standard Rate',
        };

        return response()->json(['success' => true, 'data' => [
            'id'                   => $unit->id,
            'plate_number'         => $unit->plate_number,
            'make'                 => $unit->make ?? '',
            'model'                => $unit->model ?? '',
            'year'                 => $unit->year ?? '',
            'motor_no'             => $unit->motor_no ?? '—',
            'chassis_no'           => $unit->chassis_no ?? '—',
            'status'               => $unit->status ?? 'active',
            'unit_type'            => $unit->unit_type ?? 'standard',
            'boundary_rate'        => (float)($unit->boundary_rate ?? 0),
            'purchase_cost'        => (float)($unit->purchase_cost ?? 0),
            'rate_label'           => $rateLabel,
            'imei'                 => $unit->imei,
            'gps_device_count'     => (int)($unit->gps_device_count ?? 0),
            'current_gps_odo'      => (float)($unit->current_gps_odo ?? 0),
            'last_service_odo_gps' => (float)($unit->last_service_odo_gps ?? 0),
            'gps_speed'            => $unit->gps_speed ?? 0,
            'gps_ignition'         => $unit->gps_ignition ?? 0,
            'dashcam_enabled'      => (bool)($unit->dashcam_enabled ?? false),
            'coding_day'           => $codingDay,
            'days_until_coding'    => $daysUntil,
            'next_coding_date'     => $nextCoding,
            'created_at'           => $unit->created_at,
            'updated_at'           => $unit->updated_at,
            'primary_driver' => $d1 ? [
                'full_name'             => $d1->full_name,
                'contact_number'        => $d1->contact_number,
                'license_number'        => $d1->license_number,
                'daily_boundary_target' => (float)($d1->daily_boundary_target ?? 0),
                'hire_date'             => $d1->hire_date,
                'license_expiry'        => $d1->license_expiry,
            ] : null,
            'secondary_driver' => $d2 ? [
                'full_name'             => $d2->full_name,
                'contact_number'        => $d2->contact_number,
                'license_number'        => $d2->license_number,
                'daily_boundary_target' => (float)($d2->daily_boundary_target ?? 0),
                'hire_date'             => $d2->hire_date,
                'license_expiry'        => $d2->license_expiry,
            ] : null,
            'maintenance_count'      => $maintenanceRecords->count(),
            'maintenance_total_cost' => round($maintCost, 2),
            'maintenance_records'    => $maintenanceRecords,
            'boundary_history'       => $boundaryHistory,
            'roi_percentage'       => round($roiPct, 1),
            'roi' => [
                'total_investment'  => round($investment, 2),
                'total_revenue'     => round($revenue, 2),
                'total_expenses'    => round($maintCost, 2),
                'roi_percentage'    => round($roiPct, 1),
                'monthly_avg'       => round($monthly, 2),
                'payback_period'    => round($payback, 1),
                'monthly_target'    => round($investment / 12, 2),
                'roi_status'        => $roiPct >= 100 ? 'Achieved' : 'In Progress',
            ],
        ]]);
    }

    /**
     * Update an existing unit.
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'plate_number'   => 'required|string|max:20|unique:units,plate_number,'.$id,
            'make'           => 'required|string|max:50',
            'model'          => 'required|string|max:50',
            'year'           => 'required|integer|min:1990|max:2100',
            'motor_no'       => 'required|string|max:191',
            'chassis_no'     => 'required|string|max:191',
            'status'         => 'required|string|max:20',
            'unit_type'      => 'required|string|max:20',
            'boundary_rate'  => 'required|numeric|min:0',
            'purchase_date'  => 'nullable|date',
            'purchase_cost'  => 'nullable|numeric|min:0',
            'driver_id'      => 'nullable|exists:drivers,id',
            'secondary_driver_id' => 'nullable|exists:drivers,id',
        ]);

        try {
            $oldUnit = DB::table('units')->where('id', $id)->first();
            if (!$oldUnit) return response()->json(['success' => false, 'message' => 'Unit not found.'], 404);

            DB::table('units')->where('id', $id)->update(array_merge($validated, [
                'updated_at' => now(),
            ]));

            // Log activity
            try {
                if (class_exists(\App\Http\Controllers\ActivityLogController::class)) {
                    \App\Http\Controllers\ActivityLogController::log('Updated Unit Details', "Unit: {$validated['plate_number']} information updated.");
                }
            } catch (\Exception $e) {}

            return response()->json(['success' => true, 'message' => 'Unit updated successfully.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $unit = DB::table('units')->where('id', $id)->whereNull('deleted_at')->first();
            if (!$unit) {
                return response()->json(['success' => false, 'message' => 'Unit not found.'], 404);
            }

            // Remove device links (optional, matching web logic)
            DB::table('gps_devices')->where('unit_id', $id)->delete();
            DB::table('dashcam_devices')->where('unit_id', $id)->delete();
            
            // Soft delete the unit
            DB::table('units')->where('id', $id)->update([
                'deleted_at' => now(),
                'updated_at' => now(),
            ]);
            
            // Log activity (Try to use ActivityLogController if it exists, else silent)
            try {
                if (class_exists(\App\Http\Controllers\ActivityLogController::class)) {
                    \App\Http\Controllers\ActivityLogController::log('Archived Unit', "Unit: {$unit->plate_number} moved to archive system.");
                }
            } catch (\Exception $e) {}

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Unit archived successfully.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
