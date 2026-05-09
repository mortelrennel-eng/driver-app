<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Traits\CalculatesBoundary;
use App\Traits\CalculatesDriverPerformance;
use App\Http\Controllers\ActivityLogController;

class DriverManagementController extends Controller
{
    use CalculatesBoundary, CalculatesDriverPerformance;

    public function index(Request $request)
    {
        $search        = $request->input('search', '');
        $status_filter = $request->input('status', '');
        $sort          = $request->input('sort', 'alphabetical');
        $page          = max(1, (int) $request->input('page', 1));
        $limit         = 10;
        $offset        = ($page - 1) * $limit;

        // Build base query — no users JOIN needed, names are in drivers table
        $query = DB::table('drivers as d')
            ->whereNull('d.deleted_at')
            ->leftJoin('users as creator', 'd.created_by', '=', 'creator.id')
            ->leftJoin('users as editor', 'd.updated_by', '=', 'editor.id')
            ->select(
                'd.id', 'd.user_id', 'd.first_name', 'd.last_name',
                'd.license_number', 'd.license_expiry',
                'd.contact_number', 'd.hire_date', 'd.daily_boundary_target',
                'd.driver_type', 'd.driver_status',
                'd.emergency_contact', 'd.emergency_phone',
                DB::raw("CONCAT(COALESCE(d.first_name,''), ' ', COALESCE(d.last_name,'')) as full_name"),
                'creator.full_name as creator_name',
                'editor.full_name as editor_name',
                // Unit assignment — units.driver_id links to drivers.id
                DB::raw("(SELECT plate_number FROM units WHERE (driver_id = d.id OR secondary_driver_id = d.id) AND deleted_at IS NULL LIMIT 1) as assigned_unit"),
                DB::raw("(SELECT plate_number FROM units WHERE (driver_id = d.id OR secondary_driver_id = d.id) AND deleted_at IS NULL LIMIT 1) as assigned_plate"),
                DB::raw("(SELECT boundary_rate FROM units WHERE (driver_id = d.id OR secondary_driver_id = d.id) AND deleted_at IS NULL LIMIT 1) as assigned_boundary_rate"),
                DB::raw("(SELECT coding_day FROM units WHERE (driver_id = d.id OR secondary_driver_id = d.id) AND deleted_at IS NULL LIMIT 1) as assigned_coding_day"),
                DB::raw("(SELECT year FROM units WHERE (driver_id = d.id OR secondary_driver_id = d.id) AND deleted_at IS NULL LIMIT 1) as assigned_unit_year"),
                DB::raw("(SELECT COALESCE(SUM(actual_boundary * 0.05), 0) FROM boundaries WHERE driver_id = d.id AND status IN ('paid', 'excess') AND MONTH(date) = MONTH(CURRENT_DATE()) AND YEAR(date) = YEAR(CURRENT_DATE()) AND deleted_at IS NULL) as monthly_incentive"),
                // Basic counts for PHP-side Unified Rating Calculation (30-day window)
                DB::raw("(SELECT COUNT(*) FROM boundaries WHERE driver_id = d.id AND deleted_at IS NULL AND date >= DATE_SUB(CURRENT_DATE(), INTERVAL 30 DAY)) as shifts_count"),
                DB::raw("(SELECT COUNT(*) FROM boundaries WHERE driver_id = d.id AND status IN ('paid', 'excess') AND deleted_at IS NULL AND date >= DATE_SUB(CURRENT_DATE(), INTERVAL 30 DAY)) as paid_shifts_count"),
                DB::raw("(SELECT COUNT(*) FROM driver_behavior WHERE driver_id = d.id AND created_at >= DATE_SUB(CURRENT_DATE(), INTERVAL 30 DAY) AND " . $this->getViolationQuerySnippet() . ") as incidents_count"),
                DB::raw("(SELECT COUNT(*) FROM boundaries WHERE driver_id = d.id AND has_incentive = 0 AND deleted_at IS NULL AND date >= DATE_SUB(CURRENT_DATE(), INTERVAL 30 DAY)) as missed_incentive_count"),
                DB::raw("(SELECT COUNT(*) FROM boundaries WHERE expected_driver_id = d.id AND driver_id != d.id AND deleted_at IS NULL AND date >= DATE_SUB(CURRENT_DATE(), INTERVAL 30 DAY)) as absent_count"),
                // Lifetime count to identify "Fresh" drivers
                DB::raw("(SELECT COUNT(*) FROM boundaries WHERE driver_id = d.id AND status IN ('paid', 'excess') AND deleted_at IS NULL) as total_paid_count"),
                
                // is_active derived from driver_status
                DB::raw("CASE WHEN d.driver_status IN ('available','assigned') THEN 1 ELSE 0 END as is_active"),
                // Net unpaid shortage: sum of all shortages minus sum of all excess
                DB::raw("(SELECT GREATEST(0, COALESCE(SUM(shortage),0) - COALESCE(SUM(excess),0)) FROM boundaries WHERE driver_id = d.id AND deleted_at IS NULL) as net_shortage"),
                // Total Pending Accident/Incident Debt
                DB::raw("(SELECT COALESCE(SUM(remaining_balance), 0) FROM driver_behavior WHERE driver_id = d.id AND charge_status = 'pending') as total_pending_debt")
            );

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('d.first_name',    'like', "%{$search}%")
                  ->orWhere('d.last_name',   'like', "%{$search}%")
                  ->orWhere('d.license_number', 'like', "%{$search}%")
                  ->orWhere('d.contact_number', 'like', "%{$search}%");
            });
        }

        if ($status_filter) {
            if ($status_filter === 'active') {
                $query->whereIn('d.driver_status', ['available', 'assigned']);
            } elseif ($status_filter === 'inactive') {
                $query->whereNotIn('d.driver_status', ['available', 'assigned']);
            } elseif ($status_filter === 'no_unit') {
                $query->whereNotExists(function($q) {
                    $q->select(DB::raw(1))
                      ->from('units')
                      ->whereNull('deleted_at')
                      ->where(function($q2) {
                          $q2->whereColumn('units.driver_id', 'd.id')
                             ->orWhereColumn('units.secondary_driver_id', 'd.id');
                      });
                });
            } elseif ($status_filter === 'banned') {
                $query->where('d.driver_status', 'banned');
            }
        }

        switch ($sort) {
            case 'newest':
                $query->orderBy('d.created_at', 'desc');
                break;
            case 'oldest':
                $query->orderBy('d.created_at', 'asc');
                break;
            case 'status':
                $query->orderBy('d.driver_status', 'asc')->orderBy('d.last_name', 'asc');
                break;
            case 'alphabetical':
            default:
                $query->orderBy('d.last_name', 'asc')->orderBy('d.first_name', 'asc');
                break;
        }

        $total       = $query->count();
        $drivers     = $query->offset($offset)->limit($limit)->get();
        $total_pages = max(1, ceil($total / $limit));

        $rules = DB::table('boundary_rules')->get();

        foreach ($drivers as $driver) {
            if (!empty($driver->assigned_plate) || !empty($driver->assigned_unit)) {
                // Smart Pricing Calculation
                $pricing = $this->getCurrentPricing([
                    'year' => $driver->assigned_unit_year,
                    'boundary_rate' => $driver->assigned_boundary_rate,
                    'plate_number' => $driver->assigned_plate,
                    'coding_day' => $driver->assigned_coding_day,
                    'daily_boundary_target' => $driver->daily_boundary_target
                ], $rules);

                $driver->current_target = $pricing['rate'];
                $driver->target_label = $pricing['label'];
                $driver->target_type = $pricing['type'];
            } else {
                $driver->current_target = 0;
                $driver->target_label = null;
                $driver->target_type = null;
            }

            // --- 360-Degree Performance Rating (PHP Side) ---
            $driver->performance_rating = $this->calculatePerformanceRating($driver);
        }

        // Stats
        $stats = [
            'total'     => DB::table('drivers')->whereNull('deleted_at')->count(),
            'available' => DB::table('drivers')->whereNull('deleted_at')->where('driver_status', 'available')->count(),
            'assigned'  => DB::table('drivers')->whereNull('deleted_at')->where('driver_status', 'assigned')->count(),
            'on_leave'  => DB::table('drivers')->whereNull('deleted_at')->where('driver_status', 'on_leave')->count(),
        ];

        // Expiring licenses within 30 days
        $expiring_licenses = DB::table('drivers as d')
            ->whereNull('d.deleted_at')
            ->whereRaw('d.license_expiry BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)')
            ->select(
                DB::raw("CONCAT(COALESCE(d.first_name,''), ' ', COALESCE(d.last_name,'')) as full_name"),
                'd.license_number', 'd.license_expiry'
            )
            ->get();

        $pagination = [
            'page'        => $page,
            'total_pages' => $total_pages,
            'total_items' => $total,
            'has_prev'    => $page > 1,
            'has_next'    => $page < $total_pages,
            'prev_page'   => $page - 1,
            'next_page'   => $page + 1,
        ];

        if ($request->ajax()) {
            return view('driver-management.partials._drivers_table', compact(
                'drivers', 'pagination', 'search', 'status_filter', 'sort'
            ))->render();
        }

        $boundary_rules = \App\Models\BoundaryRule::all();

        return view('driver-management.index', compact(
            'drivers', 'search', 'pagination', 'stats', 'expiring_licenses', 'status_filter', 'sort', 'boundary_rules'
        ));
    }

    public function show($id)
    {
        $driver = DB::table('drivers as d')
            ->whereNull('d.deleted_at')
            ->where('d.id', $id)
            ->select(
                'd.*',
                DB::raw("CONCAT(COALESCE(d.first_name,''), ' ', COALESCE(d.last_name,'')) as full_name"),
                DB::raw("(SELECT plate_number FROM units WHERE (driver_id = d.id OR secondary_driver_id = d.id) AND deleted_at IS NULL LIMIT 1) as assigned_unit"),
                DB::raw("(SELECT COUNT(*) FROM units WHERE (driver_id = d.id OR secondary_driver_id = d.id) AND deleted_at IS NULL LIMIT 1) as assigned_plate"),
                DB::raw("(SELECT boundary_rate FROM units WHERE (driver_id = d.id OR secondary_driver_id = d.id) AND deleted_at IS NULL LIMIT 1) as assigned_boundary_rate"),
                DB::raw("(SELECT coding_day FROM units WHERE (driver_id = d.id OR secondary_driver_id = d.id) AND deleted_at IS NULL LIMIT 1) as assigned_coding_day"),
                DB::raw("(SELECT year FROM units WHERE (driver_id = d.id OR secondary_driver_id = d.id) AND deleted_at IS NULL LIMIT 1) as assigned_unit_year"),
                // Basic counts for PHP-side Rating Calculation (30 Day Window)
                DB::raw("(SELECT COUNT(*) FROM boundaries WHERE driver_id = d.id AND deleted_at IS NULL AND date >= DATE_SUB(CURRENT_DATE(), INTERVAL 30 DAY)) as shifts_count"),
                DB::raw("(SELECT COUNT(*) FROM boundaries WHERE driver_id = d.id AND status IN ('paid', 'excess') AND deleted_at IS NULL AND date >= DATE_SUB(CURRENT_DATE(), INTERVAL 30 DAY)) as paid_shifts_count"),
                DB::raw("(SELECT COUNT(*) FROM driver_behavior WHERE driver_id = d.id AND created_at >= DATE_SUB(CURRENT_DATE(), INTERVAL 30 DAY) AND " . $this->getViolationQuerySnippet() . ") as incidents_count"),
                DB::raw("(SELECT COUNT(*) FROM boundaries WHERE driver_id = d.id AND has_incentive = 0 AND deleted_at IS NULL AND date >= DATE_SUB(CURRENT_DATE(), INTERVAL 30 DAY)) as missed_incentive_count"),
                DB::raw("(SELECT COUNT(*) FROM boundaries WHERE expected_driver_id = d.id AND driver_id != d.id AND deleted_at IS NULL AND date >= DATE_SUB(CURRENT_DATE(), INTERVAL 30 DAY)) as absent_count")
            )
            ->first();

        if ($driver) {
            $driver->performance_rating = $this->calculatePerformanceRating($driver);
        }

        if (!empty($driver->assigned_plate) || !empty($driver->assigned_unit)) {
            $driver->current_pricing = $this->getCurrentPricing([
                'year' => $driver->assigned_unit_year,
                'boundary_rate' => $driver->assigned_boundary_rate,
                'plate_number' => $driver->assigned_plate,
                'coding_day' => $driver->assigned_coding_day,
                'daily_boundary_target' => $driver->daily_boundary_target
            ]);
        } else {
            $driver->current_pricing = null;
        }

        // --- Incentive Eligibility Logic ---
        // Determine if unit has 1 driver (solo) or 2 drivers (dual)
        $assignedUnit = DB::table('units')
            ->where(function($q) use ($id) {
                $q->where('driver_id', $id)->orWhere('secondary_driver_id', $id);
            })
            ->whereNull('deleted_at')
            ->select('id', 'driver_id', 'secondary_driver_id', 'plate_number')
            ->first();

        $isDualDriver = $assignedUnit && !empty($assignedUnit->driver_id) && !empty($assignedUnit->secondary_driver_id);
        $lookbackDays = $isDualDriver ? 60 : 30;   // 2 months if dual, 1 month if solo
        $lookbackFrom = now()->subDays($lookbackDays);

        $currentMonth = now()->month;
        $currentYear  = now()->year;

        // All boundaries in lookback period
        $allBoundaries = DB::table('boundaries')
            ->where('driver_id', $id)
            ->whereNull('deleted_at')
            ->where('date', '>=', $lookbackFrom->toDateString())
            ->get();

        $thisMonthBoundaries = DB::table('boundaries')
            ->where('driver_id', $id)
            ->whereNull('deleted_at')
            ->whereMonth('date', $currentMonth)
            ->whereYear('date', $currentYear)
            ->get();

        $earned_count  = $thisMonthBoundaries->where('has_incentive', 1)->count();
        $missed_count  = $thisMonthBoundaries->where('has_incentive', 0)->count();
        $total_shifts  = $thisMonthBoundaries->count();

        // Monthly incentive (5% of actual collections on eligible shifts)
        $total_incentive = $thisMonthBoundaries
            ->where('has_incentive', 1)
            ->whereIn('status', ['paid', 'excess'])
            ->sum(fn($b) => $b->actual_boundary * 0.05);

        // Missed reason breakdown (current month - Unified from Behavior + Boundaries)
        $late_turn_missed  = DB::table('driver_behavior')
            ->where('driver_id', $id)
            ->whereMonth('incident_date', $currentMonth)
            ->whereYear('incident_date', $currentYear)
            ->where('incident_type', 'Late Remittance')
            ->count();

        $damage_missed     = DB::table('driver_behavior')
            ->where('driver_id', $id)
            ->whereMonth('incident_date', $currentMonth)
            ->whereYear('incident_date', $currentYear)
            ->where('incident_type', 'Vehicle Damage')
            ->count();

        $behavior_missed   = DB::table('driver_behavior')
            ->where('driver_id', $id)
            ->whereMonth('incident_date', $currentMonth)
            ->whereYear('incident_date', $currentYear)
            ->whereRaw($this->getViolationQuerySnippet())
            ->whereNotIn('incident_type', ['Late Remittance', 'Vehicle Damage', 'Short Boundary'])
            ->count();

        $shortage_missed   = DB::table('driver_behavior')
            ->where('driver_id', $id)
            ->whereMonth('incident_date', $currentMonth)
            ->whereYear('incident_date', $currentYear)
            ->where('incident_type', 'Short Boundary')
            ->count();

        // Backup check for boundaries that might have missed incentive for other reasons (e.g. manual toggle)
        $other_missed = $thisMonthBoundaries->where('has_incentive', 0)->count() 
            - ($late_turn_missed + $damage_missed + $shortage_missed);
        $other_missed = max(0, $other_missed);

        // --- Full Eligibility Check (Non-Stacking Penalty Block Logic) ---
        $periodStart = now()->subDays(150)->toDateString();

        $incDates = DB::table('driver_behavior')
            ->where('driver_id', $id)
            ->where('created_at', '>=', $periodStart)
            ->whereRaw($this->getViolationQuerySnippet())
            ->pluck('created_at')->map(fn($d) => \Carbon\Carbon::parse($d))->toArray();

        $bndDates = DB::table('boundaries')
            ->where('driver_id', $id)->where('has_incentive', 0)->whereNull('deleted_at')->where('date', '>=', $periodStart)
            ->pluck('date')->map(fn($d) => \Carbon\Carbon::parse($d))->toArray();

        $absDates = DB::table('boundaries')
            ->where('expected_driver_id', $id)->where('driver_id', '!=', $id)->whereNull('deleted_at')->where('date', '>=', $periodStart)
            ->pluck('date')->map(fn($d) => \Carbon\Carbon::parse($d))->toArray();

        $allViolationDates = array_merge($incDates, $bndDates, $absDates);
        usort($allViolationDates, fn($a, $b) => $a->timestamp <=> $b->timestamp);

        $currentPenaltyStart = null;
        $currentPenaltyEnd = null;

        foreach ($allViolationDates as $date) {
            if (!$currentPenaltyEnd || $date->gt($currentPenaltyEnd)) {
                // Start a new non-stacking block
                $currentPenaltyStart = $date->copy();
                $currentPenaltyEnd = $date->copy()->addDays($lookbackDays);
            }
        }

        $is_eligible = !$currentPenaltyEnd || now()->gt($currentPenaltyEnd);
        $activeBlockStart = ($currentPenaltyEnd && now()->lte($currentPenaltyEnd)) ? $currentPenaltyStart->startOfDay() : null;

        $has_shifts = $allBoundaries->count() > 0;
        if (!$has_shifts) $is_eligible = false;

        $violations_no_incentive = 0;
        $violations_damage = 0;
        $violations_breakdown = 0;
        $violations_incidents = 0;
        $violations_absences = 0;
        $blocking_violations = [];

        if ($activeBlockStart && !$is_eligible) {
            $activeBoundaries = DB::table('boundaries')
                ->where('driver_id', $id)->where('has_incentive', 0)->whereNull('deleted_at')
                ->where('date', '>=', $activeBlockStart->toDateString())->where('date', '<=', $currentPenaltyEnd->toDateString())->get();

            $violations_no_incentive = $activeBoundaries->count();
            $violations_damage = $activeBoundaries->filter(fn($b) => str_contains(strtolower($b->notes ?? ''), 'vehicle damaged'))->count();
            $violations_breakdown = $activeBoundaries->filter(fn($b) => str_contains(strtolower($b->notes ?? ''), 'maintenance'))->count();

            $violations_incidents = DB::table('driver_behavior')
                ->where('driver_id', $id)
                ->where('created_at', '>=', $activeBlockStart)
                ->where('created_at', '<=', $currentPenaltyEnd)
                ->whereRaw($this->getViolationQuerySnippet())
                ->count();

            $violations_absences = DB::table('boundaries')
                ->where('expected_driver_id', $id)->where('driver_id', '!=', $id)->whereNull('deleted_at')
                ->where('date', '>=', $activeBlockStart->toDateString())->where('date', '<=', $currentPenaltyEnd->toDateString())->count();
        }

        // Is it the 1st week of the month? (days 1-7)
        $is_first_week = now()->day <= 7;

        // Build violations blocking list
        $blocking_violations = [];
        if ($violations_no_incentive > 0) {
            $lateVio = $activeBoundaries->filter(fn($b) =>
                !str_contains(strtolower($b->notes ?? ''), 'vehicle damaged') &&
                !str_contains(strtolower($b->notes ?? ''), 'maintenance')
            )->count();
            $dmgVio = $activeBoundaries->filter(fn($b) =>
                str_contains(strtolower($b->notes ?? ''), 'vehicle damaged')
            )->count();
            $brkVio = $activeBoundaries->filter(fn($b) =>
                str_contains(strtolower($b->notes ?? ''), 'maintenance')
            )->count();
            if ($lateVio > 0) $blocking_violations[] = "{$lateVio} late/skipped boundary turn(s)";
            if ($dmgVio > 0) $blocking_violations[] = "{$dmgVio} vehicle damage incident(s)";
            if ($brkVio > 0) $blocking_violations[] = "{$brkVio} breakdown incident(s)";
        }
        if ($violations_absences > 0) $blocking_violations[] = "{$violations_absences} unattended shift(s) (Absent)";
        if ($violations_incidents > 0) $blocking_violations[] = "{$violations_incidents} behavior incident(s) on record";
        if (!$is_eligible && $currentPenaltyEnd) $blocking_violations[] = "Penalty Expires: " . $currentPenaltyEnd->format('M d, Y');

        $driver->monthly_incentive        = round($total_incentive, 2);
        $driver->incentive_earned_count   = $earned_count;
        $driver->incentive_missed_count   = $missed_count;
        $driver->total_shifts_month       = $total_shifts;
        $driver->incentive_rate           = $total_shifts > 0 ? round($earned_count / $total_shifts * 100, 1) : 0;
        $driver->late_turn_missed         = $late_turn_missed;
        $driver->damage_missed            = $damage_missed;
        $driver->behavior_missed          = $behavior_missed;
        $driver->shortage_missed          = $shortage_missed;
        $driver->other_missed             = $other_missed;
        $driver->is_dual_driver           = $isDualDriver;
        $driver->lookback_days            = $lookbackDays;
        $driver->is_eligible              = $is_eligible;
        $driver->is_first_week            = $is_first_week;
        $driver->blocking_violations      = $blocking_violations;
        $driver->violations_no_incentive  = $violations_no_incentive;
        $driver->violations_incidents     = $violations_incidents;
        $driver->violations_absences      = $violations_absences;

        // Fetch actual absentee dates with the name of who substituted them
        $driver->absentee_logs = DB::table('boundaries as b')
            ->where('b.expected_driver_id', $id)
            ->where('b.driver_id', '!=', $id)
            ->whereNull('b.deleted_at')
            ->leftJoin('drivers as actual', 'b.driver_id', '=', 'actual.id')
            ->select('b.date', 'actual.first_name', 'actual.last_name')
            ->orderByDesc('b.date')
            ->limit(10)
            ->get();

        // Per-shift incentive breakdown (last 15 records)
        $driver->incentive_breakdown = DB::table('boundaries as b')
            ->where('b.driver_id', $id)
            ->whereNull('b.deleted_at')
            ->leftJoin('units as u', 'b.unit_id', '=', 'u.id')
            ->select('b.date', 'b.actual_boundary', 'b.boundary_amount', 'b.status', 'b.shortage', 'b.has_incentive', 'b.notes', 'u.plate_number')
            ->orderByDesc('b.date')
            ->limit(15)
            ->get();

        // Recent performance logs for Performance tab (last 10)
        $driver->recent_performance = DB::table('boundaries as b')
            ->where('b.driver_id', $id)
            ->whereNull('b.deleted_at')
            ->leftJoin('units as u', 'b.unit_id', '=', 'u.id')
            ->select('b.date', 'b.actual_boundary', 'b.boundary_amount', 'b.status', 'b.shortage', 'b.excess', 'b.has_incentive', 'u.plate_number')
            ->orderByDesc('b.date')
            ->limit(10)
            ->get();

        // Driver Behavior incidents (last 10)
        $driver->incidents = DB::table('driver_behavior as db')
            ->where('db.driver_id', $id)
            ->leftJoin('units as u', 'db.unit_id', '=', 'u.id')
            ->select('db.created_at', 'db.incident_type', 'db.severity', 'db.description', 'u.plate_number')
            ->orderByDesc('db.created_at')
            ->limit(10)
            ->get();

        $driver->total_incidents_30d = DB::table('driver_behavior')
            ->where('driver_id', $id)
            ->where('created_at', '>=', now()->subDays(30))
            ->whereRaw($this->getViolationQuerySnippet())
            ->count();

        $driver->high_severity_incidents = DB::table('driver_behavior')
            ->where('driver_id', $id)
            ->whereIn('severity', ['high', 'critical'])
            ->where('created_at', '>=', now()->subDays(30))
            ->whereRaw($this->getViolationQuerySnippet())
            ->count();

        return response()->json($driver);
    }

    public function store(Request $request)
    {
        // Normalize numeric inputs (avoid '' inserting into DECIMAL columns)
        // Also normalize request value so nullable|numeric validation behaves as expected.
        if ($request->filled('daily_boundary_target')) {
            $request->merge([
                'daily_boundary_target' => str_replace(',', '', $request->input('daily_boundary_target')),
            ]);
        } else {
            $request->merge(['daily_boundary_target' => null]);
        }
        $dailyBoundaryTarget = (float) ($request->input('daily_boundary_target') ?? 0);

        $request->validate([
            'first_name'            => 'required|string|max:100',
            'last_name'             => 'required|string|max:100',
            'contact_number'        => 'required|string|max:20',
            'address'               => 'required|string',
            'license_number'        => 'required|string|max:50|unique:drivers,license_number',
            'license_expiry'        => 'required|date',
            'emergency_contact'     => 'required|string|max:100',
            'emergency_phone'       => 'required|string|max:20',
            'hire_date'             => 'required|date',
            'daily_boundary_target' => 'nullable|numeric|min:0',
        ]);

        Driver::create([
            'first_name'            => $request->first_name,
            'last_name'             => $request->last_name,

            'license_number'        => $request->license_number,
            'license_expiry'        => $request->license_expiry,
            'contact_number'        => $request->contact_number,
            'address'               => $request->address,
            'emergency_contact'     => $request->emergency_contact,
            'emergency_phone'       => $request->emergency_phone,
            'hire_date'             => $request->hire_date,
            'daily_boundary_target' => $dailyBoundaryTarget,
            'driver_type'           => $request->driver_type ?? 'regular',
            'driver_status'         => 'available',
        ]);

        ActivityLogController::log('Created Driver Record', "Driver: {$request->first_name} {$request->last_name}\nLicense: {$request->license_number}\nStatus: Available");

        return redirect()->route('driver-management.index')
            ->with('success', "Driver {$request->first_name} {$request->last_name} added successfully!");
    }

    public function update(Request $request, $id)
    {
        $driver_instance = Driver::findOrFail($id);

        // Normalize numeric inputs (avoid '' inserting into DECIMAL columns)
        // Also normalize request value so nullable|numeric validation behaves as expected.
        if ($request->filled('daily_boundary_target')) {
            $request->merge([
                'daily_boundary_target' => str_replace(',', '', $request->input('daily_boundary_target')),
            ]);
        } else {
            $request->merge(['daily_boundary_target' => null]);
        }
        $dailyBoundaryTarget = (float) ($request->input('daily_boundary_target') ?? 0);

        $request->validate([
            'first_name'            => 'required|string|max:100',
            'last_name'             => 'required|string|max:100',
            'contact_number'        => 'required|string|max:20',
            'address'               => 'required|string',
            'license_number'        => 'required|string|max:50',
            'license_expiry'        => 'required|date',
            'emergency_contact'     => 'required|string|max:100',
            'emergency_phone'       => 'required|string|max:20',
            'hire_date'             => 'required|date',
            'daily_boundary_target' => 'nullable|numeric|min:0',
            'driver_type'           => 'nullable|in:regular,senior,trainee',
            'driver_status'         => 'nullable|in:available,assigned,on_leave,suspended,banned',
        ]);

        $driver_instance->update([
            'first_name'            => $request->first_name,
            'last_name'             => $request->last_name,

            'license_number'        => $request->license_number,
            'license_expiry'        => $request->license_expiry,
            'contact_number'        => $request->contact_number,
            'address'               => $request->address,
            'emergency_contact'     => $request->emergency_contact,
            'emergency_phone'       => $request->emergency_phone,
            'hire_date'             => $request->hire_date,
            'daily_boundary_target' => $dailyBoundaryTarget,
            'driver_type'           => $request->driver_type ?? 'regular',
            'driver_status'         => $request->driver_status ?? 'available',
        ]);

        // If manually set to banned, ensure they are unassigned from units
        if ($request->driver_status === 'banned') {
            DB::table('units')->where('driver_id', $driver_instance->id)->update(['driver_id' => null, 'updated_at' => now()]);
            DB::table('units')->where('secondary_driver_id', $driver_instance->id)->update(['secondary_driver_id' => null, 'updated_at' => now()]);
        }

        ActivityLogController::log('Updated Driver Record', "Driver: {$driver_instance->first_name} {$driver_instance->last_name}\nUpdated details and status to " . ucfirst($driver_instance->driver_status));

        return redirect()->route('driver-management.index')->with('success', 'Driver updated successfully');
    }

    public function destroy($id)
    {
        $driver = Driver::find($id);
        if ($driver) {
            // Unassign from units before soft-deleting
            DB::table('units')->where('driver_id', $driver->id)->update(['driver_id' => null]);
            DB::table('units')->where('secondary_driver_id', $driver->id)->update(['secondary_driver_id' => null]);
            $name = $driver->first_name . ' ' . $driver->last_name;
            $driver->delete();
            
            ActivityLogController::log('Archived Driver', "Driver: {$name} moved to archive.");

            return redirect()->route('driver-management.index')->with('success', 'Driver archived successfully');
        }
        return redirect()->route('driver-management.index')->with('error', 'Driver not found.');
    }

    public function uploadDocuments(Request $request, $id)
    {
        $request->validate([
            'license_scan'        => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'nbi_clearance'       => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'medical_certificate' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        $driver = DB::table('drivers')->where('id', $id)->first();
        if (!$driver) {
            return back()->with('error', 'Driver not found.');
        }

        foreach (['license_scan', 'nbi_clearance', 'medical_certificate'] as $field) {
            if ($request->hasFile($field)) {
                $file     = $request->file($field);
                $filename = time() . '_' . $field . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('uploads/drivers'), $filename);
            }
        }

        return back()->with('success', 'Documents uploaded successfully!');
    }

    public function getDebtHistory()
    {
        // 1. Get fully paid debts (Settled)
        $settledDebts = DB::table('driver_behavior as db')
            ->join('drivers as d', 'db.driver_id', '=', 'd.id')
            ->leftJoin('units as u', 'db.unit_id', '=', 'u.id')
            ->where('db.charge_status', 'paid')
            ->whereNull('d.deleted_at')
            ->select(
                'db.id', 'db.incident_date as date', 'db.description', 
                'db.severity', 'db.total_charge_to_driver as total_charge', 
                DB::raw("CONCAT(COALESCE(d.first_name,''), ' ', COALESCE(d.last_name,'')) as driver_name"),
                'u.plate_number as unit_plate'
            )
            ->orderBy('db.updated_at', 'desc')
            ->limit(20)
            ->get();

        // 2. Get recent payment transactions from Expenses
        $payments = DB::table('expenses as e')
            ->leftJoin('units as u', 'e.unit_id', '=', 'u.id')
            ->where('e.category', 'Damage Recovery')
            ->where('e.status', 'approved')
            ->select(
                'e.id', 'e.date', 'e.description', 'e.amount',
                'u.plate_number as unit_plate'
            )
            ->orderBy('e.date', 'desc')
            ->orderBy('e.created_at', 'desc')
            ->limit(30)
            ->get();
            
        // Map payments to remove the negative sign for UI display
        $payments = $payments->map(function($p) {
            $p->amount = abs($p->amount);
            return $p;
        });

        return response()->json([
            'success' => true, 
            'settled' => $settledDebts,
            'payments' => $payments
        ]);
    }

    public function getPendingDebts()
    {
        $debtsRaw = DB::table('driver_behavior as db')
            ->join('drivers as d', 'db.driver_id', '=', 'd.id')
            ->leftJoin('units as u', 'db.unit_id', '=', 'u.id')
            ->where('db.charge_status', 'pending')
            ->where('db.remaining_balance', '>', 0)
            ->whereNull('d.deleted_at')
            ->select(
                'db.id', 'db.driver_id', 'db.incident_date as date', 'db.timestamp', 'db.description', 
                'db.severity', 'db.total_charge_to_driver as total_charge', 
                'db.total_paid', 'db.remaining_balance',
                DB::raw("CONCAT(COALESCE(d.first_name,''), ' ', COALESCE(d.last_name,'')) as driver_name"),
                'u.plate_number as unit_plate'
            )
            ->orderBy('db.timestamp', 'desc')
            ->get();

        $drivers = [];
        foreach ($debtsRaw as $debt) {
            $dId = $debt->driver_id;
            if (!isset($drivers[$dId])) {
                $drivers[$dId] = [
                    'driver_id' => $dId,
                    'driver_name' => trim($debt->driver_name),
                    'unit_plate' => $debt->unit_plate,
                    'total_remaining' => 0,
                    'debts' => []
                ];
            }
            $drivers[$dId]['total_remaining'] += $debt->remaining_balance;
            $drivers[$dId]['debts'][] = $debt;
        }

        return response()->json(['success' => true, 'debts' => array_values($drivers)]);
    }

    public function payDebt(Request $request)
    {
        $request->validate([
            'debt_id' => 'required|integer',
            'payment_amount' => 'required|numeric|min:1'
        ]);

        $debt = \App\Models\DriverBehavior::find($request->debt_id);
        if (!$debt || $debt->remaining_balance <= 0) {
            return back()->with('error', 'Debt record not found or na-settle na ito.');
        }

        if ($request->payment_amount > $debt->remaining_balance) {
            return back()->with('error', 'Bawal ang sobra na bayad. Ang balance ay ₱' . number_format($debt->remaining_balance, 2) . ' lang.');
        }

        $amount = (float) $request->payment_amount;
        
        $debt->total_paid += $amount;
        $debt->remaining_balance -= $amount;
        if ($debt->remaining_balance <= 0) {
            $debt->charge_status = 'paid';
        }
        $debt->save();

        // Register as a negative expense (cash inflow / revenue recovery)
        $driverName = DB::table('drivers')->where('id', $debt->driver_id)->select(DB::raw("CONCAT(first_name, ' ', last_name) as name"))->value('name');
        
        \App\Models\Expense::create([
            'category' => 'Damage Recovery',
            'description' => "Direct cash payment from {$driverName} for accident debt (Incident Date: {$debt->incident_date})",
            'amount' => -$amount, // Negative means revenue in an expense table
            'payment_method' => 'Cash',
            'date' => now()->toDateString(),
            'recorded_by' => \Illuminate\Support\Facades\Auth::id(),
            'unit_id' => $debt->unit_id,
            'status' => 'approved'
        ]);

        ActivityLogController::log('Debt Payment', "Processed ₱" . number_format($amount, 2) . " cash payment from {$driverName} for accident debt.");

        return back()->with('success', 'Cash payment processed successfully. The balance has been updated and the revenue has been recorded.');
    }

    // calculatePerformanceRating moved to App\Traits\CalculatesDriverPerformance

    public function unban($id)
    {
        $driver = Driver::findOrFail($id);

        if ($driver->driver_status !== 'banned') {
            return response()->json(['success' => false, 'message' => 'Driver is not banned.'], 422);
        }

        $driver->update(['driver_status' => 'available']);

        $name = trim($driver->first_name . ' ' . $driver->last_name);
        ActivityLogController::log('Unbanned Driver', "Driver: {$name} has been unbanned and status set to Available.");

        return response()->json(['success' => true, 'message' => "{$name} has been successfully unbanned."]);
    }
}
