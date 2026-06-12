<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Unit;
use App\Models\Driver;
use App\Models\User;
use Carbon\Carbon;

use App\Traits\CalculatesBoundary;
use App\Http\Controllers\ActivityLogController;

class UnitController extends Controller
{
    use CalculatesBoundary;

    public function index(Request $request)
    {
        $search = $request->input('search', '');
        $status_filter = $request->input('status', '');
        $page = max(1, (int) $request->input('page', 1));
        $view_mode = $request->input('view', 'table');
        $limit = $view_mode === 'grid' ? 8 : 5;
        $offset = ($page - 1) * $limit;

        $query = DB::table('units as u')
            ->whereNull('u.deleted_at')
            ->leftJoin('drivers as drv1', 'u.driver_id', '=', 'drv1.id')
            ->leftJoin('drivers as drv2', 'u.secondary_driver_id', '=', 'drv2.id')
            ->select(
                'u.*', 
                DB::raw("CONCAT(COALESCE(drv1.first_name,''), ' ', COALESCE(drv1.last_name,''), '|', COALESCE(drv1.contact_number, '')) as primary_driver"),
                DB::raw("CONCAT(COALESCE(drv2.first_name,''), ' ', COALESCE(drv2.last_name,''), '|', COALESCE(drv2.contact_number, '')) as secondary_driver")
            )
            ->addSelect([
                'total_collected' => DB::table('boundaries')
                    ->whereColumn('unit_id', 'u.id')
                    ->whereIn('status', ['paid', 'excess', 'shortage'])
                    ->selectRaw('COALESCE(SUM(actual_boundary), 0)'),
                'maintenance_cost' => DB::table('maintenance')
                    ->whereColumn('unit_id', 'u.id')
                    ->whereNull('deleted_at')
                    ->where('status', '!=', 'cancelled')
                    ->selectRaw('COALESCE(SUM(cost), 0)'),
                'gps_device_count' => DB::table('gps_devices')
                    ->selectRaw('COUNT(*)')
                    ->whereColumn('unit_id', 'u.id'),
                'dashcam_device_count' => DB::table('dashcam_devices')
                    ->selectRaw('COUNT(*)')
                    ->whereColumn('unit_id', 'u.id'),
                'latest_odo' => DB::table('maintenance')
                    ->select('odometer_reading')
                    ->whereColumn('unit_id', 'u.id')
                    ->whereNull('deleted_at')
                    ->orderBy('date_completed', 'desc')
                    ->limit(1),
                'last_service_odo' => DB::table('maintenance')
                    ->select('odometer_reading')
                    ->whereColumn('unit_id', 'u.id')
                    ->whereNull('deleted_at')
                    ->where('status', 'completed')
                    ->orderBy('date_completed', 'desc')
                    ->limit(1),
            ]);

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('u.plate_number', 'like', "%{$search}%")
                    ->orWhere('u.make', 'like', "%{$search}%")
                    ->orWhere('u.model', 'like', "%{$search}%");
            });
        }

        if (!empty($status_filter)) {
            if ($status_filter === 'available') {
                $query->whereNull('u.driver_id')
                      ->whereNull('u.secondary_driver_id');
            } elseif ($status_filter === '1_2') {
                $query->where(function($q) {
                    $q->whereNotNull('u.driver_id')->whereNull('u.secondary_driver_id')
                      ->orWhereNull('u.driver_id')->whereNotNull('u.secondary_driver_id');
                });
            } elseif ($status_filter === '2_2') {
                $query->whereNotNull('u.driver_id')
                      ->whereNotNull('u.secondary_driver_id');
            } else {
                $query->where('u.status', $status_filter);
            }
        }

        $sort = $request->input('sort', 'alphabetical');
        switch ($sort) {
            case 'newest':
                $query->orderBy('u.created_at', 'desc');
                break;
            case 'oldest':
                $query->orderBy('u.created_at', 'asc');
                break;
            case 'vacant':
                $query->orderByRaw('CASE WHEN u.driver_id IS NULL THEN 0 ELSE 1 END')
                    ->orderBy('u.plate_number');
                break;
            case 'alphabetical':
            default:
                $query->orderBy('u.plate_number', 'asc');
                break;
        }

        // Get today's coding status globally (matching Coding Management exactly)
        $today_name = now()->timezone('Asia/Manila')->format('l');
        $all_units_for_coding = DB::table('units')->select('plate_number', 'coding_day')->get();
        $coding_today_count = 0;
        foreach ($all_units_for_coding as $cu) {
            $c_day = $cu->coding_day;
            if (empty($c_day)) {
                $lastDigit = @substr(preg_replace('/[^0-9]/', '', $cu->plate_number), -1);
                if ($lastDigit == 1 || $lastDigit == 2) $c_day = 'Monday';
                elseif ($lastDigit == 3 || $lastDigit == 4) $c_day = 'Tuesday';
                elseif ($lastDigit == 5 || $lastDigit == 6) $c_day = 'Wednesday';
                elseif ($lastDigit == 7 || $lastDigit == 8) $c_day = 'Thursday';
                elseif ($lastDigit === '9' || $lastDigit === '0') $c_day = 'Friday';
            }
            if ($c_day === $today_name) {
                $coding_today_count++;
            }
        }

        // Calculate Contextual Stats (Unpaginated but Filtered)
        $stats = [
            'total'    => $query->count(),
            'on_road'  => (clone $query)->where('u.status', 'active')->count(),
            'garage'   => (clone $query)->where('u.status', 'at_risk')->count(),
            'workshop' => (clone $query)->where('u.status', 'maintenance')->count(),
            'coding'   => $coding_today_count,
        ];

        $total_units = $stats['total'];
        $units = $query->offset($offset)->limit($limit)->get();

        // Fetch all boundary rules once to avoid N+1
        $boundary_rules = DB::table('boundary_rules')->get();

        foreach ($units as $unit) {
            $net_income = (data_get($unit, 'total_collected', 0)) - (data_get($unit, 'maintenance_cost', 0));
            $unit->roi_achieved = (data_get($unit, 'purchase_cost', 0)) > 0 && $net_income >= (data_get($unit, 'purchase_cost', 0));

            // Smart Pricing Automation
            $pricing = $this->getCurrentPricing($unit, $boundary_rules);
            $unit->current_rate = $pricing['rate'];
            $unit->rate_label = $pricing['label'];
            $unit->rate_type = $pricing['type'];
        }

        $total_pages = ceil($total_units / $limit);
        $pagination = [
            'page' => $page,
            'total_pages' => $total_pages,
            'total_items' => $total_units,
            'has_prev' => $page > 1,
            'has_next' => $page < $total_pages,
            'prev_page' => $page - 1,
            'next_page' => $page + 1,
        ];

        // Drivers list for add/edit modal
        $all_drivers = DB::table('drivers as d')
            ->whereNull('d.deleted_at')
            ->where('d.driver_status', '!=', 'banned')
            ->select(
                'd.id', 
                DB::raw("CONCAT(COALESCE(d.first_name,''), ' ', COALESCE(d.last_name,'')) as full_name"), 
                'd.contact_number', 
                'd.license_number',
                DB::raw("(SELECT id FROM units WHERE (driver_id = d.id OR secondary_driver_id = d.id) AND deleted_at IS NULL LIMIT 1) as assigned_unit_id")
            )
            ->get();

        // Determine view mode for both AJAX and full-page loads
        $view_mode = $request->input('view', 'table');

        if ($request->ajax()) {
            $partial = ($view_mode === 'grid')
                ? 'units.partials._units_grid'
                : 'units.partials._units_table';
            
            return view($partial, compact('units', 'pagination', 'search', 'status_filter', 'sort'))->render();
        }

        return view('units.index', compact('units', 'pagination', 'search', 'status_filter', 'all_drivers', 'sort', 'boundary_rules', 'view_mode', 'stats'));
    }

    public function store(Request $request)
    {
        $request->merge([
            'boundary_rate' => str_replace(',', '', $request->boundary_rate),
            'purchase_cost' => str_replace(',', '', $request->purchase_cost),
        ]);

        $today = now()->format('Y-m-d');
        $maxYear = 2026;

        $data = $request->validate([
            'plate_number' => 'required|string|max:8|regex:/^[A-Z0-9]+ ?[A-Z0-9]*$/|unique:units,plate_number',
            'make' => ['required', 'string', 'max:15', 'regex:/^(?![0-9\s\W]+$)[a-zA-Z0-9\s\W]+$/'],
            'model' => ['required', 'string', 'max:15', 'regex:/^(?![0-9\s\W]+$)[a-zA-Z0-9\s\W]+$/'],
            'year' => 'required|integer|digits:4|max:'.$maxYear,
            'status' => 'sometimes|required|string',
            'boundary_rate' => 'required|numeric|max:100000',
            'purchase_date' => 'nullable|date|before_or_equal:'.$today,
            'purchase_cost' => 'nullable|numeric|max:1000000',
            'motor_no' => 'required|string|max:25|regex:/^[A-Z0-9\-]+$/',
            'chassis_no' => 'required|string|max:25|regex:/^[A-Z0-9\-]+$/',
            'unit_type' => 'sometimes|required|in:new,old,rented',
            'coding_day' => 'nullable|string',
            'driver_id' => 'nullable|integer',
            'secondary_driver_id' => 'nullable|integer',
            'imei' => 'nullable|string|max:30|regex:/^[a-zA-Z0-9-]+$/|unique:units,imei',
            'gps_provider' => 'nullable|string|in:tracksolid,aksh',
            'gps_password' => 'nullable|string|max:50',
        ], [
            'plate_number.regex' => 'Plate number must be alphanumeric and can contain at most one space.',
            'make.regex' => 'Vehicle make cannot be pure numbers, spaces, or symbols.',
            'model.regex' => 'Vehicle model cannot be pure numbers, spaces, or symbols.',
            'motor_no.regex' => 'Motor number must be alphanumeric (hyphens allowed) with no spaces or other symbols.',
            'chassis_no.regex' => 'Chassis number must be alphanumeric (hyphens allowed) with no spaces or other symbols.',
            'imei.regex' => 'IMEI must be alphanumeric (hyphens allowed) with no spaces or symbols.',
            'purchase_date.before_or_equal' => 'Purchase date cannot be in the future.',
            'year.max' => 'Year cannot exceed 2026.',
        ]);

        $driver_id = $request->input('driver_id') ?: null;
        $secondary_driver_id = $request->input('secondary_driver_id') ?: null;

        // Check driver conflict
        if ($driver_id) {
            $conflict = DB::table('units')
                ->where(function ($q) use ($driver_id) {
                    $q->where('driver_id', $driver_id)->orWhere('secondary_driver_id', $driver_id);
                })->first();
            if ($conflict) {
                return back()->with('error', 'Selected primary driver is already assigned to unit ' . $conflict->plate_number . '.');
            }
        }
        if ($secondary_driver_id) {
            $conflict = DB::table('units')
                ->where(function ($q) use ($secondary_driver_id) {
                    $q->where('driver_id', $secondary_driver_id)->orWhere('secondary_driver_id', $secondary_driver_id);
                })->first();
            if ($conflict) {
                return back()->with('error', 'Selected secondary driver is already assigned to unit ' . $conflict->plate_number . '.');
            }
        }

        // Auto set coding status
        $status = $data['status'] ?? 'active';
        $coding_day = $data['coding_day'] ?? null;
        if ($coding_day && date('l') === $coding_day) {
            $status = 'coding';
        }

        // Use Eloquent to trigger TrackChanges trait
        $newUnit = Unit::create([
            'plate_number' => $data['plate_number'],
            'make' => $data['make'],
            'model' => $data['model'],
            'year' => $data['year'],
            'status' => $status,
            'boundary_rate' => $data['boundary_rate'],
            'purchase_date' => $data['purchase_date'] ?? null,
            'purchase_cost' => $data['purchase_cost'] ?? 0,
            'motor_no' => $data['motor_no'],
            'chassis_no' => $data['chassis_no'],
            'unit_type' => $data['unit_type'] ?? 'new',
            'coding_day' => $coding_day,
            'driver_id' => $driver_id,
            'secondary_driver_id' => $secondary_driver_id,
            'imei' => $data['imei'] ?? null,
            'gps_provider' => $data['gps_provider'] ?? 'tracksolid',
            'gps_password' => $data['gps_password'] ?? null,
            'coding_updated_at' => now(),
        ]);

        // Sync driver boundary target
        if ($driver_id) $this->syncDriverBoundaryTarget($driver_id, $newUnit);
        if ($secondary_driver_id) $this->syncDriverBoundaryTarget($secondary_driver_id, $newUnit);

        ActivityLogController::log('Created Unit', "Unit: {$newUnit->plate_number}\nCategory: {$newUnit->make} {$newUnit->model} ({$newUnit->year})\nStatus: " . ucfirst($newUnit->status));

        return $this->preserveStateAndRedirect('units.index', ['success' => 'Unit added successfully!']);
    }

    public function update(Request $request, $id)
    {
        $today   = now()->format('Y-m-d');
        $maxYear = (int) date('Y') + 1;

        // Step 1: Clean numeric inputs before validation
        $request->merge([
            'boundary_rate' => str_replace(',', '', $request->boundary_rate),
            'purchase_cost' => str_replace(',', '', $request->purchase_cost),
        ]);

        // Step 2: Validate and capture into $data
        $data = $request->validate([
            'plate_number'         => 'required|string|max:15|regex:/^[A-Z0-9 ]{1,15}$/',
            'make'                 => 'sometimes|required|string|max:20|regex:/^(?![0-9\s\W]+$)[a-zA-Z0-9\s\W]+$/',
            'model'                => 'sometimes|required|string|max:15|regex:/^(?![0-9\s\W]+$)[a-zA-Z0-9\s\W]+$/',
            'year'                 => 'sometimes|required|integer|digits:4|max:'.$maxYear,
            'status'               => 'sometimes|required|string',
            'boundary_rate'        => 'required|numeric|max:100000',
            'purchase_date'        => 'nullable|date|before_or_equal:'.$today,
            'purchase_cost'        => 'nullable|numeric|max:1000000',
            'motor_no'             => 'required|string|max:25|regex:/^[A-Z0-9\-]+$/',
            'chassis_no'           => 'required|string|max:25|regex:/^[A-Z0-9\-]+$/',
            'unit_type'            => 'sometimes|required|in:new,old,rented',
            'coding_day'           => 'nullable|string',
            'driver_id'            => 'nullable|integer',
            'secondary_driver_id'  => 'nullable|integer',
            'imei'                 => 'nullable|string|max:30|regex:/^[a-zA-Z0-9-]+$/|unique:units,imei,'.$id,
            'gps_provider'         => 'nullable|string|in:tracksolid,aksh',
            'gps_password'         => 'nullable|string|max:50',
        ], [
            'plate_number.regex'              => 'Plate number must be alphanumeric and can contain at most one space.',
            'make.regex'                      => 'Vehicle make cannot be pure numbers, spaces, or symbols.',
            'model.regex'                     => 'Vehicle model cannot be pure numbers, spaces, or symbols.',
            'motor_no.regex'                  => 'Motor number must be alphanumeric (hyphens allowed) with no spaces or other symbols.',
            'chassis_no.regex'                => 'Chassis number must be alphanumeric (hyphens allowed) with no spaces or other symbols.',
            'imei.regex'                      => 'IMEI must be alphanumeric (hyphens allowed) with no spaces or symbols.',
            'purchase_date.before_or_equal'   => 'Purchase date cannot be in the future.',
            'year.max'                        => 'Year cannot exceed '.$maxYear.'.',
        ]);

        $driver_id = $data['driver_id'] ?? null;
        $secondary_driver_id = $data['secondary_driver_id'] ?? null;

        // Check driver conflict (excluding this unit)
        if ($driver_id) {
            $conflict = DB::table('units')
                ->where('id', '!=', $id)
                ->where(function ($q) use ($driver_id) {
                    $q->where('driver_id', $driver_id)->orWhere('secondary_driver_id', $driver_id);
                })->first();
            if ($conflict) {
                return back()->with('error', 'Selected primary driver is already assigned to unit ' . $conflict->plate_number . '.');
            }
        }
        if ($secondary_driver_id) {
            $conflict = DB::table('units')
                ->where('id', '!=', $id)
                ->where(function ($q) use ($secondary_driver_id) {
                    $q->where('driver_id', $secondary_driver_id)->orWhere('secondary_driver_id', $secondary_driver_id);
                })->first();
            if ($conflict) {
                return back()->with('error', 'Selected secondary driver is already assigned to unit ' . $conflict->plate_number . '.');
            }
        }

        // Auto set coding status
        $status = $data['status'] ?? null;
        $coding_day = $data['coding_day'] ?? null;
        if ($coding_day && date('l') === $coding_day) {
            $status = 'coding';
        }

        $updateData = [
            'plate_number' => $data['plate_number'],
            'boundary_rate' => $data['boundary_rate'],
            'purchase_date' => $data['purchase_date'] ?? null,
            'purchase_cost' => $data['purchase_cost'] ?? 0,
            'motor_no' => $data['motor_no'],
            'chassis_no' => $data['chassis_no'],
            'coding_day' => $coding_day,
            'driver_id' => $driver_id,
            'secondary_driver_id' => $secondary_driver_id,
            'imei' => $data['imei'] ?? null,
            'gps_provider' => $data['gps_provider'] ?? 'tracksolid',
            'gps_password' => $data['gps_password'] ?? null,
            'updated_at' => now(),
        ];

        if (isset($data['make'])) $updateData['make'] = $data['make'];
        if (isset($data['model'])) $updateData['model'] = $data['model'];
        if (isset($data['year'])) $updateData['year'] = $data['year'];
        if ($status) $updateData['status'] = $status;
        if (isset($data['unit_type'])) $updateData['unit_type'] = $data['unit_type'];

        // Cache old drivers to clean up their targets if removed/replaced
        $unit = Unit::findOrFail($id);
        $old_p_driver = $unit->driver_id;
        $old_s_driver = $unit->secondary_driver_id;

        $unit->update($updateData);

        // Cleanup old primary driver if removed or replaced
        if ($old_p_driver && $old_p_driver != $driver_id) {
            DB::table('drivers')->where('id', $old_p_driver)->update(['daily_boundary_target' => 0]);
        }
        // Sync new primary driver
        if ($driver_id) {
            $this->syncDriverBoundaryTarget($driver_id, $unit);
        }

        // Cleanup old secondary driver if removed or replaced
        if ($old_s_driver && $old_s_driver != $secondary_driver_id) {
            DB::table('drivers')->where('id', $old_s_driver)->update(['daily_boundary_target' => 0]);
        }
        // Sync new secondary driver
        if ($secondary_driver_id) {
            $this->syncDriverBoundaryTarget($secondary_driver_id, $unit);
        }
        
        // Remove 'updated_at' from manual array since Eloquent handles it
        if (isset($updateData['updated_at'])) unset($updateData['updated_at']);

        ActivityLogController::log('Updated Unit', "Unit: {$unit->plate_number}\nCategory: {$unit->make} {$unit->model}\nStatus: " . ucfirst($unit->status));

        return $this->preserveStateAndRedirect('units.index', ['success' => 'Unit updated successfully!']);
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            DB::table('gps_devices')->where('unit_id', $id)->delete();
            DB::table('dashcam_devices')->where('unit_id', $id)->delete();
            
            $unit = Unit::findOrFail($id);
            $plate = $unit->plate_number;
            $unit->delete(); // This now triggers soft delete
            
            ActivityLogController::log('Archived Unit', "Unit: {$plate} moved to archive system.");

            DB::commit();
            return $this->preserveStateAndRedirect('units.index', ['success' => 'Unit archived successfully!']);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->preserveStateAndRedirect('units.index', ['error' => 'Failed to archive unit: ' . $e->getMessage()]);
        }
    }

    public function getDetails(Request $request)
    {
        $unit_id = (int) $request->input('id', 0);
        if (!$unit_id) {
            return response()->json(['error' => 'Invalid ID'], 400);
        }

        $unit = DB::table('units as u')
            ->leftJoin('users as creator', 'u.created_by', '=', 'creator.id')
            ->leftJoin('users as editor', 'u.updated_by', '=', 'editor.id')
            ->where('u.id', $unit_id)
            ->select('u.*', 'creator.full_name as created_by_name', 'editor.full_name as updated_by_name')
            ->first();
        if (!$unit) {
            return response()->json(['error' => 'Unit not found'], 404);
        }

        // Assigned drivers
        $assigned_drivers = [];
        $driver_ids = array_filter([(int) ($unit->driver_id ?? 0), (int) ($unit->secondary_driver_id ?? 0)]);
        if (!empty($driver_ids)) {
            $assigned_drivers = DB::table('drivers as d')
                ->whereIn('d.id', $driver_ids)
                ->select('d.id', DB::raw("CONCAT(COALESCE(d.first_name,''), ' ', COALESCE(d.last_name,'')) as full_name"), 'd.license_number', 'd.contact_number', 'd.license_expiry', 'd.hire_date', 'd.daily_boundary_target')
                ->get()->toArray();
        }

        // ROI data from real boundaries
        $roi = DB::table('boundaries')
            ->where('unit_id', $unit_id)
            ->whereNull('deleted_at')
            ->selectRaw('
                SUM(actual_boundary) as total_boundary,
                SUM(CASE WHEN MONTH(date)=MONTH(CURDATE()) AND YEAR(date)=YEAR(CURDATE()) THEN actual_boundary ELSE 0 END) as monthly_boundary,
                SUM(CASE WHEN MONTH(date)=MONTH(CURDATE()) AND YEAR(date)=YEAR(CURDATE()) THEN boundary_amount ELSE 0 END) as monthly_expected_boundary,
                SUM(actual_boundary) as paid_boundary
            ')->first();

        $maintenance_cost = DB::table('maintenance')
            ->where('unit_id', $unit_id)
            ->whereNull('deleted_at')
            ->where('status', '!=', 'cancelled')
            ->selectRaw('SUM(cost) as total, SUM(CASE WHEN MONTH(date_started)=MONTH(CURDATE()) AND YEAR(date_started)=YEAR(CURDATE()) THEN cost ELSE 0 END) as monthly')
            ->first();


        $total_investment = $unit->purchase_cost ?? 0;
        $total_revenue = $roi->paid_boundary ?? 0;
        $total_expenses = $maintenance_cost->total ?? 0;
        $monthly_revenue = $roi->monthly_boundary ?? 0;
        
        // NEW: Theoretical full-month target (accounts for coding/weekends for every day of the month)
        $monthly_theoretical = $this->getMonthlyExpectedTarget($unit);
        
        $roi_percentage = $total_investment > 0 ? (($total_revenue - $total_expenses) / $total_investment) * 100 : 0;
        $payback_period = $monthly_revenue > 0 ? $total_investment / $monthly_revenue : 0;

        $unit->current_pricing = $this->getCurrentPricing($unit);
        $roi_data = [
            'total_investment' => $total_investment,
            'total_revenue' => $total_revenue,
            'total_expenses' => $total_expenses,
            'actual_monthly_revenue' => $monthly_revenue,
            'monthly_theoretical_target' => $monthly_theoretical,
            'monthly_expenses' => $maintenance_cost->monthly ?? 0,
            'roi_percentage' => round($roi_percentage, 2),
            'payback_period' => round($payback_period, 2),
            'monthly_boundary' => $roi->monthly_boundary ?? 0,
            'total_boundary' => $roi->total_boundary ?? 0,
        ];

        // Boundary history (last 10 records from boundaries table)
        $boundary_history = DB::table('boundaries as bh')
            ->leftJoin('drivers as d', 'bh.driver_id', '=', 'd.id')
            ->where('bh.unit_id', $unit_id)
            ->whereNull('bh.deleted_at')
            ->select('bh.*', DB::raw("CONCAT(COALESCE(d.first_name,''), ' ', COALESCE(d.last_name,'')) as full_name"))
            ->orderByDesc('bh.date')
            ->limit(10)->get()->toArray();

        // Ensure tracking info is present
        $unit->created_at_fmt = $unit->created_at ? date('M d, Y h:i A', strtotime($unit->created_at)) : 'N/A';
        $unit->updated_at_fmt = $unit->updated_at ? date('M d, Y h:i A', strtotime($unit->updated_at)) : 'N/A';

        // Maintenance records from real maintenance table with enhanced details
        $maintenance_records = DB::table('maintenance as mr')
            ->where('mr.unit_id', $unit_id)
            ->whereNull('mr.deleted_at')
            ->leftJoin('drivers', 'mr.driver_id', '=', 'drivers.id')
            ->select(
                'mr.*',
                DB::raw('CONCAT(drivers.first_name, " ", drivers.last_name) as driver_name')
            )
            ->orderByDesc('mr.date_started')
            ->limit(10)->get()->toArray();

        // Pre-fetch all parts for the maintenance records
        $maint_ids = array_column($maintenance_records, 'id');
        $all_parts = collect();
        if (!empty($maint_ids)) {
            $all_parts = DB::table('maintenance_parts')
                ->leftJoin('spare_parts', 'maintenance_parts.part_id', '=', 'spare_parts.id')
                ->whereIn('maintenance_parts.maintenance_id', $maint_ids)
                ->select('maintenance_parts.*', 'spare_parts.supplier')
                ->orderBy('maintenance_parts.part_name')
                ->get()
                ->groupBy('maintenance_id');
        }

        foreach ($maintenance_records as &$record) {
            $parts = $all_parts->get($record->id, collect());
            
            $record->parts_details = $parts;
            $record->total_parts_cost = $parts->where('part_id', '!=', null)->sum('total');
            $record->total_other_costs = $parts->where('part_id', null)->sum('total');
        }


        // Coding info
        $last_digit = substr($unit->plate_number ?? '', -1);
        $coding_schedule = [
            'Monday' => [1, 2],
            'Tuesday' => [3, 4],
            'Wednesday' => [5, 6],
            'Thursday' => [7, 8],
            'Friday' => [9, 0],
        ];
        $coding_day = 'Not Set';
        foreach ($coding_schedule as $day => $endings) {
            if (in_array((int) $last_digit, $endings)) {
                $coding_day = $day;
                break;
            }
        }

        $gps_device = DB::table('gps_devices')->where('unit_id', $unit_id)->where('status', 'active')->first();
        $dashcam_device = DB::table('dashcam_devices')->where('unit_id', $unit_id)->where('status', 'active')->first();
        
        // Fetch latest location from gps_tracking
        $latest_gps = DB::table('gps_tracking')
            ->where('unit_id', $unit_id)
            ->orderBy('timestamp', 'desc')
            ->first();

        $has_gps = ($gps_device || !empty($unit->gps_link) || $latest_gps);

        $location_info = [
            'current_location' => 'Not Available',
            'last_location_update' => 'Never',
            'gps_enabled' => $has_gps,
            'latitude' => null,
            'longitude' => null,
            'speed' => 0,
            'timestamp' => null,
            'coordinates' => null
        ];

        if ($latest_gps) {
            $location_info['latitude'] = $latest_gps->latitude;
            $location_info['longitude'] = $latest_gps->longitude;
            $location_info['speed'] = $latest_gps->speed;
            $location_info['timestamp'] = $latest_gps->timestamp;
            $location_info['last_location_update'] = Carbon::parse($latest_gps->timestamp)->timezone('Asia/Manila')->diffForHumans();
            $location_info['current_location'] = $latest_gps->latitude . ', ' . $latest_gps->longitude;
            $location_info['coordinates'] = $latest_gps->latitude . ', ' . $latest_gps->longitude;
        } elseif ($unit->gps_link) {
            $location_info['current_location'] = 'Live Tracking Active';
            $location_info['last_location_update'] = 'Real-time';
        }

        return response()->json([
            'unit' => $unit,
            'assigned_drivers' => $assigned_drivers,
            'roi_data' => $roi_data,
            'boundary_history' => $boundary_history,
            'maintenance_records' => $maintenance_records,
            'coding_day' => $coding_day,
            'location_info' => $location_info,
            'dashcam_info' => [
                'dashcam_enabled' => $dashcam_device ? true : false,
                'dashcam_status' => $dashcam_device ? 'Online' : 'Offline',
                'last_recording' => $dashcam_device ? date('Y-m-d H:i') : 'Never',
                'storage_used' => $dashcam_device ? (data_get($dashcam_device, 'storage_used') ?: rand(10, 25)) : 0, 
                'storage_total' => data_get($dashcam_device, 'storage_total') ?: 32,
            ],
        ]);
    }

    public function getDetailsHtml(Request $request)
    {
        $unit_id = (int) $request->input('id', 0);
        if (!$unit_id) {
            return response('Invalid ID', 400);
        }

        $unit = DB::table('units as u')
            ->leftJoin('users as creator', 'u.created_by', '=', 'creator.id')
            ->leftJoin('users as editor', 'u.updated_by', '=', 'editor.id')
            ->where('u.id', $unit_id)
            ->select('u.*', 'creator.full_name as created_by_name', 'editor.full_name as updated_by_name')
            ->first();
        if (!$unit) {
            return response('Unit not found', 404);
        }

        $assigned_drivers = [];
        $driver_ids = array_filter([(int) ($unit->driver_id ?? 0), (int) ($unit->secondary_driver_id ?? 0)]);
        if (!empty($driver_ids)) {
            $assigned_drivers = DB::table('drivers as d')
                ->whereIn('d.id', $driver_ids)
                ->select('d.id', DB::raw("CONCAT(COALESCE(d.first_name,''), ' ', COALESCE(d.last_name,'')) as full_name"), 'd.license_number', 'd.contact_number', 'd.license_expiry', 'd.hire_date', 'd.daily_boundary_target')
                ->get()->toArray();
        }

        $roi = DB::table('boundaries')
            ->where('unit_id', $unit_id)
            ->whereNull('deleted_at')
            ->selectRaw('
                SUM(actual_boundary) as total_boundary,
                SUM(CASE WHEN MONTH(date)=MONTH(CURDATE()) AND YEAR(date)=YEAR(CURDATE()) THEN actual_boundary ELSE 0 END) as monthly_boundary,
                SUM(actual_boundary) as paid_boundary
            ')->first();

        $maintenance_cost = DB::table('maintenance')
            ->where('unit_id', $unit_id)
            ->whereNull('deleted_at')
            ->where('status', '!=', 'cancelled')
            ->selectRaw('SUM(cost) as total, SUM(CASE WHEN MONTH(date_started)=MONTH(CURDATE()) AND YEAR(date_started)=YEAR(CURDATE()) THEN cost ELSE 0 END) as monthly')
            ->first();

        $total_investment = $unit->purchase_cost ?? 0;
        $total_revenue = $roi->paid_boundary ?? 0;
        $total_expenses = $maintenance_cost->total ?? 0;
        $monthly_revenue = $roi->monthly_boundary ?? 0;
        $roi_percentage = $total_investment > 0 ? (($total_revenue - $total_expenses) / $total_investment) * 100 : 0;
        $payback_period = $monthly_revenue > 0 ? $total_investment / $monthly_revenue : 0;

        $roi_data = [
            'total_investment' => $total_investment,
            'total_revenue' => $total_revenue,
            'total_expenses' => $total_expenses,
            'monthly_revenue' => $monthly_revenue,
            'monthly_expenses' => $maintenance_cost->monthly ?? 0,
            'roi_percentage' => round($roi_percentage, 2),
            'payback_period' => round($payback_period, 2),
            'monthly_boundary' => $roi->monthly_boundary ?? 0,
            'total_boundary' => $roi->total_boundary ?? 0,
        ];

        $boundary_history = DB::table('boundaries as bh')
            ->leftJoin('drivers as d', 'bh.driver_id', '=', 'd.id')
            ->where('bh.unit_id', $unit_id)
            ->whereNull('bh.deleted_at')
            ->select('bh.*', DB::raw("CONCAT(COALESCE(d.first_name,''), ' ', COALESCE(d.last_name,'')) as full_name"))
            ->orderByDesc('bh.date')
            ->limit(10)->get()->toArray();

        $maintenance_records = DB::table('maintenance as mr')
            ->where('mr.unit_id', $unit_id)
            ->whereNull('mr.deleted_at')
            ->leftJoin('drivers', 'mr.driver_id', '=', 'drivers.id')
            ->select(
                'mr.*',
                DB::raw('CONCAT(drivers.first_name, " ", drivers.last_name) as driver_name')
            )
            ->orderByDesc('mr.date_started')
            ->limit(10)->get()->toArray();

        // Pre-fetch all parts for the maintenance records
        $maint_ids = array_column($maintenance_records, 'id');
        $all_parts = collect();
        if (!empty($maint_ids)) {
            $all_parts = DB::table('maintenance_parts')
                ->whereIn('maintenance_id', $maint_ids)
                ->orderBy('part_name')
                ->get()
                ->groupBy('maintenance_id');
        }

        foreach ($maintenance_records as &$record) {
            $parts = $all_parts->get($record->id, collect());
            
            $record->parts_details = $parts;
            $record->total_parts_cost = $parts->where('part_id', '!=', null)->sum('total');
            $record->total_other_costs = $parts->where('part_id', null)->sum('total');
        }

        $last_digit = substr($unit->plate_number ?? '', -1);
        $coding_schedule = [
            'Monday' => [1, 2],
            'Tuesday' => [3, 4],
            'Wednesday' => [5, 6],
            'Thursday' => [7, 8],
            'Friday' => [9, 0],
        ];
        $coding_day = 'Not Set';
        foreach ($coding_schedule as $day => $endings) {
            if (in_array((int) $last_digit, $endings)) {
                $coding_day = $day;
                break;
            }
        }

        $next_coding_date = '';
        $days_until_coding = 0;
        if ($coding_day !== 'Not Set') {
            $today = Carbon::today();
            if ($today->format('l') === $coding_day) {
                $next_coding_date = $today->format('M d, Y');
                $days_until_coding = 0;
            } else {
                $target = Carbon::parse('next ' . $coding_day);
                $next_coding_date = $target->format('M d, Y');
                $days_until_coding = $today->diffInDays($target);
            }
        }

        $location_info = [
            'current_location' => data_get($unit, 'current_location', 'Unknown'),
            'last_location_update' => data_get($unit, 'last_location_update', 'Never'),
            'gps_enabled' => (bool) data_get($unit, 'gps_enabled', false) || !empty($unit->gps_link),
            'coordinates' => (data_get($unit, 'latitude') && data_get($unit, 'longitude')) ? (data_get($unit, 'latitude') . ', ' . data_get($unit, 'longitude')) : null,
        ];

        $dashcam_info = [
            'dashcam_enabled' => (bool) data_get($unit, 'dashcam_enabled', false),
            'dashcam_status' => data_get($unit, 'dashcam_status', 'Offline'),
            'last_recording' => data_get($unit, 'last_recording', 'Never'),
            'storage_used' => (float) data_get($unit, 'storage_used', 0),
            'storage_total' => (float) data_get($unit, 'storage_total', 0),
        ];

        return view('units.partials.unit_details_modal', compact(
            'unit',
            'assigned_drivers',
            'boundary_history',
            'maintenance_records',
            'roi_data',
            'coding_day',
            'next_coding_date',
            'days_until_coding',
            'location_info',
            'dashcam_info'
        ));
    }

    public function toggleStatus(Request $request)
    {
        $id = $request->input('id');
        $new_status = $request->input('new_status', 'active');

        $unit = DB::table('units')->where('id', $id)->first();
        if ($unit) {
            $updateData = [
                'status' => $new_status,
                'updated_at' => now(),
            ];

            // If we are clearing the missing/flagged state (marking back to active),
            // reset the shift deadline and pinned flags to fully clear auto-detected status
            if ($new_status === 'active') {
                $updateData['shift_deadline_at'] = null;
                $updateData['is_pinned_missing'] = false;
            }

            DB::table('units')->where('id', $id)->update($updateData);

            // Resolve any related missing unit alerts in system alerts
            DB::table('system_alerts')
                ->where('title', 'like', "%{$unit->plate_number}%")
                ->where('is_resolved', false)
                ->update([
                    'is_resolved' => true,
                    'updated_at' => now()
                ]);
        }

        return $this->preserveStateAndRedirect('units.index', ['success' => 'Unit status updated and flagged status cleared successfully!'], ['open_flagged' => 1]);
    }

    public function resetHealth($id)
    {
        $unit = Unit::findOrFail($id);
        
        DB::table('units')->where('id', $id)->update([
            'last_service_odo_gps' => $unit->current_gps_odo,
            'updated_at' => now(),
        ]);

        ActivityLogController::log('Reset Maintenance Health', "Manual health reset for Unit: {$unit->plate_number}. Counter restarted at " . number_format($unit->current_gps_odo) . " KM.");

        return back()->with('success', 'Maintenance health counter has been reset to current odometer!');
    }

    public function storeManualFlag(Request $request)
    {
        $request->validate([
            'unit_id' => 'required|exists:units,id',
            'description' => 'required|string|min:5|max:1000',
            'missing_since' => 'nullable|date',
            'suspect_driver_id' => 'nullable|exists:drivers,id'
        ]);

        DB::beginTransaction();
        try {
            $unit = DB::table('units')->where('id', $request->unit_id)->first();
            
            // Update Unit Status
            $updateData = [
                'status' => 'missing',
                'updated_at' => now(),
                'is_pinned_missing' => true
            ];

            DB::table('units')->where('id', $request->unit_id)->update($updateData);

            // Log Incident for missing unit
            $suspectName = null;
            $suspectContact = null;
            $suspectLicense = null;

            if ($request->suspect_driver_id) {
                $driver = DB::table('drivers')->where('id', $request->suspect_driver_id)->first();
                if ($driver) {
                    $suspectName = trim($driver->first_name . ' ' . $driver->last_name);
                    $suspectContact = $driver->contact_number;
                    $suspectLicense = $driver->license_number;
                }
            }

            // Calculate days missing
            $missingSince = $request->missing_since ? Carbon::parse($request->missing_since) : now();
            $daysMissing = max(0, $missingSince->diffInDays(now()));

            DB::table('driver_behavior')->insert([
                'unit_id' => $request->unit_id,
                'driver_id' => $request->suspect_driver_id,
                'incident_type' => 'The vehicle unit was taken/stolen',
                'severity' => 'CRITICAL',
                'description' => $request->description,
                'incident_date' => $missingSince->format('Y-m-d'),
                'missing_days_reported' => $daysMissing,
                'stolen_driver_detail_name' => $suspectName,
                'stolen_driver_detail_contact' => $suspectContact,
                'stolen_driver_license_no' => $suspectLicense,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            DB::commit();
            return redirect()->back()->with('success', 'Unit successfully flagged as Missing/Stolen.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to flag unit: ' . $e->getMessage());
        }
    }

    public function getFlaggedUnits(Request $request)
    {
        // 1. Manually flagged as Missing/Stolen units
        $missingUnits = DB::table('units')
            ->whereNull('deleted_at')
            ->where('status', 'missing')
            ->select('id', 'plate_number', 'make', 'model', 'year', 'status', 'driver_id', 'secondary_driver_id', 'shift_deadline_at')
            ->get()
            ->map(function ($unit) {
                $unit->flag_source = 'manual_stolen';
                return $unit;
            });

        // 2. Auto-detected missing units: has a driver, overdue boundary (>48h)
        $autoMissingUnits = DB::table('units')
            ->whereNull('deleted_at')
            ->whereNotIn('status', ['maintenance', 'retired', 'coding', 'missing'])
            ->whereNotNull('shift_deadline_at')
            ->where('shift_deadline_at', '<', now()->subHours(48))
            ->where(function($q) {
                $q->whereNotNull('driver_id')
                  ->orWhereNotNull('secondary_driver_id');
            })
            ->select('id', 'plate_number', 'make', 'model', 'year', 'status', 'driver_id', 'secondary_driver_id', 'shift_deadline_at')
            ->get()
            ->map(function ($unit) {
                $unit->flag_source = 'auto_boundary';
                return $unit;
            });

        // Merge and de-duplicate by id
        $allFlagged = $missingUnits->merge($autoMissingUnits)->unique('id')->values();

        // Eager load related data to avoid N+1
        $unit_ids = $allFlagged->pluck('id')->toArray();
        $last_boundaries = collect();
        $stolen_metas = collect();
        $all_drivers = collect();
        
        if (!empty($unit_ids)) {
            $latest_boundary_sub = DB::table('boundaries')
                ->select('unit_id', DB::raw('MAX(created_at) as max_created_at'))
                ->whereIn('unit_id', $unit_ids)
                ->groupBy('unit_id');
                
            $last_boundaries = DB::table('boundaries as b')
                ->joinSub($latest_boundary_sub, 'latest', function ($join) {
                    $join->on('b.unit_id', '=', 'latest.unit_id')
                         ->on('b.created_at', '=', 'latest.max_created_at');
                })
                ->get()
                ->keyBy('unit_id');

            $latest_incident_sub = DB::table('driver_behavior')
                ->whereNull('deleted_at')
                ->where(function ($q) {
                    $q->where('incident_type', 'like', '%taken%')
                        ->orWhere('incident_type', 'like', '%stolen%');
                })
                ->select('unit_id', DB::raw('MAX(id) as max_id'))
                ->whereIn('unit_id', $unit_ids)
                ->groupBy('unit_id');

            $stolen_metas = DB::table('driver_behavior as d')
                ->joinSub($latest_incident_sub, 'latest', function ($join) {
                    $join->on('d.unit_id', '=', 'latest.unit_id')
                         ->on('d.id', '=', 'latest.max_id');
                })
                ->get()
                ->keyBy('unit_id');
                
            $all_drivers = DB::table('drivers')
                ->select('id', 'first_name', 'last_name', 'contact_number')
                ->get()
                ->keyBy('id');
        }

        foreach ($allFlagged as $unit) {
            $lastBoundary = $last_boundaries->get($unit->id);
                
            if ($lastBoundary) {
                $lastDate = \Carbon\Carbon::parse($lastBoundary->created_at);
                $unit->last_boundary_date = $lastDate->format('M d, Y g:i A');
                $unit->days_inactive = max(0, $lastDate->diffInDays(now()));
                
                $lastBoundaryDriverId = $lastBoundary->driver_id;
                $suspectDriverId = null;

                if ($unit->driver_id && $unit->secondary_driver_id) {
                    if ($lastBoundaryDriverId == $unit->driver_id) {
                        $suspectDriverId = $unit->secondary_driver_id;
                    } else {
                        $suspectDriverId = $unit->driver_id;
                    }
                } else if ($unit->driver_id || $unit->secondary_driver_id) {
                    $suspectDriverId = $unit->driver_id ?? $unit->secondary_driver_id;
                } else {
                    $suspectDriverId = null;
                }

                if ($suspectDriverId) {
                    $suspect = $all_drivers->get($suspectDriverId);
                    $unit->suspect_driver = $suspect 
                        ? trim($suspect->first_name . ' ' . $suspect->last_name)
                        : 'Unknown';
                    $unit->suspect_contact = $suspect->contact_number ?? null;
                    $unit->is_vacant = false;
                } else {
                    $unit->suspect_driver = 'NO ASSIGNED DRIVER';
                    $unit->suspect_contact = null;
                    $unit->is_vacant = true;
                }

                if ($lastBoundaryDriverId) {
                    $lastD = $all_drivers->get($lastBoundaryDriverId);
                    $unit->last_known_driver = $lastD ? trim($lastD->first_name . ' ' . $lastD->last_name) : 'Unknown';
                } else {
                    $unit->last_known_driver = 'None';
                }
            } else {
                $unit->last_boundary_date = null;
                $unit->days_inactive = null;
                $unit->last_known_driver = 'No boundary record';
                $unit->last_driver_contact = null;
                $unit->suspect_driver = 'Unknown';
                $unit->suspect_contact = null;
                $unit->is_vacant = true;
            }

            if (($unit->flag_source ?? '') === 'manual_stolen') {
                $stolenMeta = $stolen_metas->get($unit->id);

                if ($stolenMeta) {
                    if ($stolenMeta->missing_days_reported !== null) {
                        $unit->days_inactive = (int) $stolenMeta->missing_days_reported;
                    }
                    if (!empty($stolenMeta->stolen_driver_detail_name)) {
                        $unit->suspect_driver = $stolenMeta->stolen_driver_detail_name;
                        $unit->suspect_contact = $stolenMeta->stolen_driver_detail_contact ?: null;
                        $unit->is_vacant = false;
                    }
                    if (!empty($stolenMeta->stolen_driver_license_no)) {
                        $unit->stolen_driver_license_no = $stolenMeta->stolen_driver_license_no;
                    }
                    if (!empty($stolenMeta->description)) {
                        $unit->description = $stolenMeta->description;
                    }
                    if (!empty($stolenMeta->incident_date)) {
                        $unit->missing_since = \Carbon\Carbon::parse($stolenMeta->incident_date)->format('M d, Y');
                    }
                }
            } elseif (($unit->flag_source ?? '') === 'auto_boundary') {
                $unit->description = "Auto-detected: Ang unit na ito ay may boundary delay at lumampas na ng 48 oras ang shift deadline.";
                if (!empty($unit->shift_deadline_at)) {
                    $unit->missing_since = \Carbon\Carbon::parse($unit->shift_deadline_at)->format('M d, Y');
                }
            }
        }

        // Return JSON for AJAX requests (used by the unit management panel internals)
        if ($request->ajax() || $request->expectsJson()) {
            return response()->json($allFlagged);
        }

        // Return full page view for normal browser requests
        $flaggedCount = $allFlagged->count();
        $stolenCount  = $allFlagged->where('flag_source', 'manual_stolen')->count();
        $autoCount    = $allFlagged->where('flag_source', 'auto_boundary')->count();

        // Data for manual flagging modal
        $availableUnits = DB::table('units')
            ->whereNull('deleted_at')
            ->where('status', '!=', 'missing')
            ->select('id', 'plate_number', 'make', 'model')
            ->orderBy('plate_number')
            ->get();
            
        $availableDrivers = DB::table('drivers')
            ->whereNull('deleted_at')
            ->whereNotIn('driver_status', ['banned'])
            ->select('id', 'first_name', 'last_name', 'contact_number', 'license_number')
            ->orderBy('first_name')
            ->get();

        return view('units.flagged', compact(
            'allFlagged',
            'flaggedCount',
            'stolenCount',
            'autoCount',
            'availableUnits',
            'availableDrivers'
        ));
    }

    public function showImport()
    {
        return view('units.import');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:csv,xlsx,xls|max:10240'
        ]);

        $file = $request->file('file');
        $extension = $file->getClientOriginalExtension();
        
        try {
            if ($extension == 'csv') {
                $this->importCSV($file);
            } else {
                $this->importExcel($file);
            }
            
            return redirect()->route('units.index')->with('success', 'Units imported successfully!');
        } catch (\Exception $e) {
            return redirect()->route('units.index')->with('error', 'Error importing file: ' . $e->getMessage());
        }
    }

    private function importCSV($file)
    {
        $csvData = array_map('str_getcsv', file($file->getPathname()));
        $headers = array_shift($csvData);
        
        foreach ($csvData as $row) {
            if (count($row) >= 3) {
                DB::table('units')->insert([
                    'plate_number' => $row[0] ?? '',
                    'make' => $row[1] ?? '',
                    'model' => $row[2] ?? '',
                    'status' => $row[3] ?? 'active',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function quickStats(Request $request)
    {
        $search = $request->input('search', '');
        $status_filter = $request->input('status', '');

        // Get today's coding status globally (matching Coding Management exactly)
        $today_name = now()->timezone('Asia/Manila')->format('l');
        $all_units_for_coding = DB::table('units')->select('plate_number', 'coding_day')->get();
        $coding_today_count = 0;
        foreach ($all_units_for_coding as $cu) {
            $c_day = $cu->coding_day;
            if (empty($c_day)) {
                $lastDigit = @substr(preg_replace('/[^0-9]/', '', $cu->plate_number), -1);
                if ($lastDigit == 1 || $lastDigit == 2) $c_day = 'Monday';
                elseif ($lastDigit == 3 || $lastDigit == 4) $c_day = 'Tuesday';
                elseif ($lastDigit == 5 || $lastDigit == 6) $c_day = 'Wednesday';
                elseif ($lastDigit == 7 || $lastDigit == 8) $c_day = 'Thursday';
                elseif ($lastDigit === '9' || $lastDigit === '0') $c_day = 'Friday';
            }
            if ($c_day === $today_name) {
                $coding_today_count++;
            }
        }

        // Base query for both global and filtered
        $baseQuery = DB::table('units')->whereNull('deleted_at');

        // Helper to calculate stats from a query
        $getStats = function($q) use ($coding_today_count) {
            return [
                'total'    => (clone $q)->count(),
                'on_road'  => (clone $q)->where('status', 'active')->count(),
                'garage'   => (clone $q)->where('status', 'at_risk')->count(),
                'workshop' => (clone $q)->where('status', 'maintenance')->count(),
                'coding'   => $coding_today_count, // Global coding count always matches Coding Management
            ];
        };

        $global = $getStats($baseQuery);

        // Apply filters for the filtered stats
        $filteredQuery = clone $baseQuery;
        if (!empty($search)) {
            $filteredQuery->where(function ($q) use ($search) {
                $q->where('plate_number', 'like', "%{$search}%")
                  ->orWhere('make', 'like', "%{$search}%")
                  ->orWhere('model', 'like', "%{$search}%");
            });
        }

        if (!empty($status_filter)) {
            if ($status_filter === 'available') {
                $filteredQuery->whereNull('driver_id')->whereNull('secondary_driver_id');
            } elseif ($status_filter === '1_2') {
                $filteredQuery->where(function($q) {
                    $q->whereNotNull('driver_id')->whereNull('secondary_driver_id')
                      ->orWhereNull('driver_id')->whereNotNull('secondary_driver_id');
                });
            } elseif ($status_filter === '2_2') {
                $filteredQuery->whereNotNull('driver_id')->whereNotNull('secondary_driver_id');
            } else {
                $filteredQuery->where('status', $status_filter);
            }
        }

        $filtered = $getStats($filteredQuery);

        return response()->json([
            'is_filtered' => !empty($search) || !empty($status_filter),
            'global'      => $global,
            'filtered'    => $filtered
        ]);
    }

    private function importExcel($file)
    {
        // For now, treat as CSV (you can install phpoffice/phpspreadsheet for full Excel support)
        $this->importCSV($file);
    }

    public function printPdf()
    {
        $units = DB::table('units as u')
            ->leftJoin('drivers as drv1', 'u.driver_id', '=', 'drv1.id')
            ->leftJoin('drivers as drv2', 'u.secondary_driver_id', '=', 'drv2.id')
            ->select(
                'u.*',
                DB::raw("CONCAT(COALESCE(drv1.first_name,''), ' ', COALESCE(drv1.last_name,'')) as driver1_name"),
                DB::raw("CONCAT(COALESCE(drv2.first_name,''), ' ', COALESCE(drv2.last_name,'')) as driver2_name")
            )
            ->orderBy('u.plate_number')
            ->get();

        foreach ($units as $unit) {
            $driverCount = 0;
            if ($unit->driver1_name) $driverCount++;
            if ($unit->driver2_name) $driverCount++;
            $unit->driver_count = $driverCount;
        }

        return view('units.print', compact('units'));
    }

    public function recover(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $unit = Unit::findOrFail($id);
            $old_status = $unit->status;
            
            // Set back to active
            $unit->update([
                'status' => 'active',
                'is_pinned_missing' => false,
                'shift_deadline_at' => null,
                'updated_at' => now(),
            ]);

            ActivityLogController::log('Recovered Unit', "Unit: {$unit->plate_number} recovered from status: " . ucfirst($old_status));

            // Resolve any related missing unit alerts
            DB::table('system_alerts')
                ->where('title', 'like', "%{$unit->plate_number}%")
                ->where('is_resolved', false)
                ->update(['is_resolved' => true, 'updated_at' => now()]);

            DB::commit();
            
            if ($request->ajax()) {
                return response()->json(['success' => true, 'message' => "Unit {$unit->plate_number} has been recovered."]);
            }
            
            return back()->with('success', "Unit {$unit->plate_number} has been marked as RECOVERED.");
        } catch (\Exception $e) {
            DB::rollBack();
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
            }
            return back()->with('error', 'Recovery failed: ' . $e->getMessage());
        }
    }

    private function syncDriverBoundaryTarget($driver_id, $unit)
    {
        if (!$driver_id || !$unit) return;

        // Simplify: Just sync the BASE boundary rate of the unit.
        // The smart pricing (coding/weekend) is handled dynamically in the view/trait.
        $baseRate = (float) $unit->boundary_rate;

        DB::table('drivers')->where('id', $driver_id)->update([
            'daily_boundary_target' => $baseRate,
            'updated_at' => now(),
        ]);
    }
}
