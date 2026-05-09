<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Boundary;
use Carbon\Carbon;
use App\Http\Controllers\ActivityLogController;

use App\Traits\CalculatesBoundary;
use App\Traits\CalculatesDriverPerformance;

class BoundaryController extends Controller
{
    use CalculatesBoundary, CalculatesDriverPerformance;
    /**
     * Display a listing of boundary records.
     */
    public function index(Request $request)
    {
        $search        = $request->get('search', '');
        $date_filter   = $request->get('date', date('Y-m-d')); // Default to today
        $status_filter = $request->get('status', '');
        $page          = max(1, (int) $request->get('page', 1));
        $limit         = 10;
        $offset        = ($page - 1) * $limit;

        // Build query joining units and drivers tables
        $query = DB::table('boundaries as b')
            ->whereNull('b.deleted_at')
            ->leftJoin('units as u', 'b.unit_id', '=', 'u.id')
            ->leftJoin('drivers as d', 'b.driver_id', '=', 'd.id')
            ->leftJoin('users as creator', 'b.created_by', '=', 'creator.id')
            ->leftJoin('users as editor', 'b.updated_by', '=', 'editor.id')
            ->select(
                'b.*',
                'u.plate_number',
                'u.year as unit_year',
                'u.coding_day as unit_coding_day',
                DB::raw("CONCAT(COALESCE(d.first_name,''), ' ', COALESCE(d.last_name,'')) as driver_name"),
                'creator.full_name as creator_name',
                'editor.full_name as editor_name'
            );

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('u.plate_number', 'like', "%{$search}%")
                  ->orWhere(DB::raw("CONCAT(COALESCE(d.first_name,''), ' ', COALESCE(d.last_name,''))"), 'like', "%{$search}%");
            });
        }

        if (!empty($date_filter)) {
            $query->whereDate('b.date', $date_filter);
        }

        if (!empty($status_filter)) {
            $query->where('b.status', $status_filter);
        }

        $total_boundaries = $query->count();

        $boundaries = $query
            ->orderByDesc('b.date')
            ->orderByDesc('b.created_at')
            ->offset($offset)
            ->limit($limit)
            ->get();

        // Get units for dropdowns
        $units = DB::table('units')
            ->whereNull('deleted_at')
            ->where('status', '!=', 'retired')
            ->where('status', '!=', 'missing')
            ->select('id', 'plate_number', 'make', 'model', 'year', 'boundary_rate', 'coding_day', 'driver_id', 'secondary_driver_id', 'current_turn_driver_id', 'last_swapping_at', 'shift_deadline_at')
            ->orderBy('plate_number')
            ->get()
            ->map(function ($unit) {
                $unitArray = (array) $unit;
                $unitArray['make_model'] = ($unitArray['make'] ?? '') . ' ' . ($unitArray['model'] ?? '');
                
                // Smart Integration: Check if expected driver has an 'Absent / No Show' incident recorded for today
                $unitArray['has_absent_today'] = false;
                if ($unit->current_turn_driver_id) {
                    $unitArray['has_absent_today'] = DB::table('driver_behavior')
                        ->where('driver_id', $unit->current_turn_driver_id)
                        ->whereDate('incident_date', date('Y-m-d'))
                        ->where('incident_type', 'Absent / No Show')
                        ->exists();
                }
                
                return $unitArray;
            })
            ->toArray();

        // Get all drivers with their current unit assignments
        $all_drivers = DB::select("
            SELECT d.id, CONCAT(COALESCE(d.first_name,''), ' ', COALESCE(d.last_name,'')) as name, 
                   COALESCE(ua.plate_number, 'No Assignment') as current_unit,
                   COALESCE(ua.plate_number, '') as current_plate,
                   (SELECT COUNT(*) FROM units WHERE (driver_id = d.id OR secondary_driver_id = d.id) AND deleted_at IS NULL) as assigned_units_count,
                   (SELECT GREATEST(0, COALESCE(SUM(shortage),0) - COALESCE(SUM(excess),0)) FROM boundaries WHERE driver_id = d.id AND deleted_at IS NULL) as net_shortage,
                   (SELECT COUNT(*) FROM driver_behavior WHERE driver_id = d.id AND charge_status = 'pending' AND remaining_balance > 0) as has_accident_debt,
                   (SELECT COALESCE(SUM(remaining_balance), 0) FROM driver_behavior WHERE driver_id = d.id AND charge_status = 'pending' AND remaining_balance > 0) as total_accident_debt
            FROM drivers d 
            LEFT JOIN units ua ON (d.id = ua.driver_id OR d.id = ua.secondary_driver_id) AND ua.deleted_at IS NULL
            WHERE d.deleted_at IS NULL AND d.driver_status != 'banned'
            ORDER BY 
                CASE WHEN ua.plate_number IS NOT NULL THEN 1 ELSE 0 END,
                d.last_name, d.first_name
        ");
        $all_drivers = array_map(function($d) { return (array) $d; }, $all_drivers);

        // Assigned drivers
        $assigned_drivers = DB::select("
            SELECT d.id, CONCAT(COALESCE(d.first_name,''), ' ', COALESCE(d.last_name,'')) as name, 
                   ua.plate_number as current_unit,
                   ua.plate_number as current_plate
            FROM drivers d 
            LEFT JOIN units ua ON (d.id = ua.driver_id OR d.id = ua.secondary_driver_id) AND ua.deleted_at IS NULL
            WHERE ua.plate_number IS NOT NULL
            AND d.deleted_at IS NULL AND d.driver_status != 'banned'
            ORDER BY ua.plate_number, d.last_name, d.first_name
        ");
        $assigned_drivers = array_map(function($d) { return (array) $d; }, $assigned_drivers);

        // Unit drivers
        $unit_drivers = [];
        foreach ($units as $unit) {
            $unit_id = $unit['id'];
            $res = DB::select("
                SELECT d.id, CONCAT(COALESCE(d.first_name,''), ' ', COALESCE(d.last_name,'')) as name, 
                       ua.plate_number as current_plate,
                       ua.plate_number as current_unit
                FROM drivers d 
                LEFT JOIN units ua ON (d.id = ua.driver_id OR d.id = ua.secondary_driver_id)
                WHERE ua.id = ? AND d.deleted_at IS NULL AND d.driver_status != 'banned'
                ORDER BY d.last_name, d.first_name
            ", [$unit_id]);
            $unit_drivers[$unit_id] = array_map(function($d) { return (array) $d; }, $res);
        }

        $total_pages = ceil($total_boundaries / $limit);
        $pagination = [
            'page'           => $page,
            'total_items'    => $total_boundaries,
            'total_pages'    => $total_pages,
            'items_per_page' => $limit,
            'offset'         => $offset,
            'has_prev'       => $page > 1,
            'prev_page'      => $page - 1,
            'has_next'       => $page < $total_pages,
            'next_page'      => $page + 1,
        ];

        // Ensure we pass $boundaries as array as backup ui expects arrays for json encoding
        $boundary_rules = DB::table('boundary_rules')->get();
        $boundariesArray = [];
        foreach ($boundaries as $b) {
            $recordDate = Carbon::parse($b->date);
            $dayOfWeek = $recordDate->format('l');
            
            // Temporary override to simulate date-specific pricing
            // We use our trait but we have to handle the "today" override if needed
            // Actually, we can just pass the day if we modify the trait, but for now 
            // since the trait uses date('l'), we'll check if it's the record's coding day manually or 
            // we simulate it.
            
            $pricing = $this->getCurrentPricing([
                'year' => $b->unit_year,
                'plate_number' => $b->plate_number,
                'boundary_rate' => $b->boundary_amount, // Use the target recorded
                'coding_day' => $b->unit_coding_day
            ], $boundary_rules);

            // Re-calculate specifically for the record's day if it's not today
            if ($dayOfWeek === 'Saturday') {
                $rule = $boundary_rules->where('start_year', '<=', $b->unit_year)->where('end_year', '>=', $b->unit_year)->first();
                $pricing['label'] = 'Saturday Discount';
                $pricing['type'] = 'discount';
            } elseif ($dayOfWeek === 'Sunday') {
                $pricing['label'] = 'Sunday Discount';
                $pricing['type'] = 'discount';
            } else {
                // Coding check for that day
                $cDay = $pricing['coding_day'] ?? null;
                if ($cDay && strtolower($dayOfWeek) === strtolower($cDay)) {
                    $pricing['label'] = 'Coding Rate';
                    $pricing['type'] = 'coding';
                } else {
                    $pricing['label'] = 'Regular Rate';
                    $pricing['type'] = 'regular';
                }
            }

            $item = (array) $b;
            $item['rate_label'] = $pricing['label'];
            $item['rate_type'] = $pricing['type'];
            $boundariesArray[] = $item;
        }

        if ($request->ajax() || $request->wantsJson() || $request->input('format') === 'json') {
            $html = view('boundaries.partials._boundaries_table', [
                'boundaries' => $boundariesArray,
                'pagination' => $pagination,
                'search'     => $search,
                'date_filter' => $date_filter,
                'status_filter' => $status_filter
            ])->render();

            return response()->json([
                'success' => true,
                'html'    => $html,
                'boundaries' => $boundariesArray,
                'pagination' => $pagination
            ]);
        }

        return view('boundaries.index', compact(
            'boundaries', 
            'pagination', 
            'page', 
            'search', 
            'date_filter',
            'status_filter', 
            'units', 
            'all_drivers', 
            'assigned_drivers',
            'unit_drivers',
            'boundary_rules',
            'boundariesArray'
        ));
    }

    /**
     * Store a newly created boundary record OR update existing if id is set.
     */
    public function store(Request $request)
    {
        $action = $request->input('action', '');
        
        if ($action === 'add_boundary') {
            $unit_id         = (int) $request->input('unit_id', 0);
            $driver_id       = (int) $request->input('driver_id', 0);
            $date            = $request->input('date', date('Y-m-d'));
            $boundary_amount = (float) $request->input('boundary_amount', 0);
            $actual_boundary = (float) $request->input('actual_boundary', 0);
            $notes           = $request->input('notes', '');
            $vehicle_damaged = $request->has('vehicle_damaged');
            $needs_maintenance_half = $request->has('needs_maintenance_half');
            $needs_maintenance_zero = $request->has('needs_maintenance_zero');
            $needs_maintenance = $needs_maintenance_half || $needs_maintenance_zero;

            // Security Check: Block missing/stolen units from recording boundaries
            $unitStatus = DB::table('units')->where('id', $unit_id)->value('status');
            if (strtolower($unitStatus) === 'missing') {
                return back()->with('error', 'Critical Security Alert: This vehicle is currently flagged as MISSING/STOLEN and is under security lockdown. Boundary recording is prohibited.');
            }
            
            // Server-side strict validation (Max 4 digits, No pure zero unless breakdown)
            if ($actual_boundary >= 10000 || $boundary_amount >= 10000) {
                return back()->with('error', 'Boundary amounts cannot exceed 4 digits (₱9,999.99)');
            }
            if (!$needs_maintenance_zero && $actual_boundary <= 0) {
                return back()->with('error', 'Actual collected amount cannot be zero unless it is an Early Shift Breakdown.');
            }

            $is_valid_amount = ($boundary_amount > 0 || $needs_maintenance_zero);

            if ($unit_id > 0 && $driver_id > 0 && $is_valid_amount) {
                // Check duplicate
                $existing = DB::table('boundaries')->where('unit_id', $unit_id)->where('date', $date)->first();
                if ($existing) {
                    return back()->with('error', 'Boundary record already exists for this unit and date');
                } else {
                    $shortage = max(0, $boundary_amount - $actual_boundary);
                    $excess   = max(0, $actual_boundary - $boundary_amount);
                    $status   = $shortage > 0 ? 'shortage' : ($excess > 0 ? 'excess' : 'paid');

                    $has_incentive = true;

                    if ($shortage > 0) {
                        $has_incentive = false;
                        $notes = trim($notes . " [Automatic Violation: Short Boundary]");
                        
                        // Auto-log to Driver Performance
                        \App\Models\DriverBehavior::create([
                            'unit_id'       => $unit_id,
                            'driver_id'     => $driver_id,
                            'incident_type' => 'Short Boundary',
                            'severity'      => 'medium',
                            'description'   => "Auto-logged [Shortage]: Driver remitted ₱" . number_format($actual_boundary, 2) . " instead of ₱" . number_format($boundary_amount, 2),
                            'incident_date' => $date,
                            'timestamp'     => now(),
                        ]);
                    }

                    // --- Check for ANY pre-existing violations today ---
                    if ($has_incentive && !$this->isEligibleToday(\App\Models\Driver::find($driver_id), $date)) {
                        $has_incentive = false;
                        $notes = trim($notes . " [Prior Incident Violation]");
                    }

                    $unit = \App\Models\Unit::find($unit_id);
                    $is_extra_driver = false;
                    $expected_driver_id = $unit ? $unit->current_turn_driver_id : $driver_id;
                    $now = now();

                    $past_cutoff = $request->has('past_cutoff');
                    if ($past_cutoff) {
                        $has_incentive = false;
                        $notes = trim($notes . " [Automatic Violation: Late Remittance (Past 10:00 AM)]");
                        
                        // Auto-log to Driver Performance
                        \App\Models\DriverBehavior::create([
                            'unit_id'       => $unit_id,
                            'driver_id'     => $driver_id,
                            'incident_type' => 'Late Remittance',
                            'severity'      => 'medium',
                            'description'   => 'Auto-logged [Late Remittance]: Driver remitted boundary after the 10:00 AM cutoff.',
                            'incident_date' => $date,
                            'timestamp'     => $now,
                        ]);
                    }

                    $is_absent = false; // "Absent / No Show" logic removed per user request

                    if ($unit) {
                        
                        // Shifting Deadline Check: Legacy auto-voiding for late returns removed per user request. 
                        // Incentives now only focus on the 10:00 AM Cut-off (Late Boundary).
                        $current_deadline = $unit->shift_deadline_at ? Carbon::parse($unit->shift_deadline_at) : Carbon::parse($date)->hour(10);

                        if ($unit->driver_id !== $driver_id && $unit->secondary_driver_id !== $driver_id) {
                            $is_extra_driver = true;
                        }

                        $next_turn_driver_id = $unit->current_turn_driver_id;
                        if (!empty($unit->secondary_driver_id)) {
                            if ($driver_id === $unit->driver_id) {
                                $next_turn_driver_id = $unit->secondary_driver_id;
                            } else {
                                $next_turn_driver_id = $unit->driver_id;
                            }
                        } else {
                            $next_turn_driver_id = $unit->driver_id;
                        }

                        // Dynamic Shifting: The next deadline is exactly 24 hours from THIS turnover moment.
                        // This prevents shifting-time drift from penalizing the next driver.
                        $next_deadline = $now->copy()->addHours(24);

                        if ($vehicle_damaged) {
                            $has_incentive = false;
                            $notes = trim($notes . " [Automatic Violation: Vehicle Damaged]");

                            // Auto-log to Driver Performance
                            \App\Models\DriverBehavior::create([
                                'unit_id'       => $unit_id,
                                'driver_id'     => $driver_id,
                                'incident_type' => 'Vehicle Damage',
                                'severity'      => 'high',
                                'description'   => 'Auto-logged [Damage]: Driver returned unit with damage reported during boundary turnover.',
                                'incident_date' => $date,
                                'timestamp'     => $now,
                                'is_driver_fault' => true,
                            ]);
                        }



                        // Low Fuel Violation Check
                        if ($request->has('low_fuel')) {
                            $has_incentive = false;
                            $notes = trim($notes . " [Automatic Violation: Low Fuel on Return]");

                            // Auto-log to Driver Performance
                            \App\Models\DriverBehavior::create([
                                'unit_id'       => $unit_id,
                                'driver_id'     => $driver_id,
                                'incident_type' => 'Other',
                                'severity'      => 'medium',
                                'description'   => 'Auto-logged [Low Fuel]: Driver returned the unit without refueling (Kulang sa gas).',
                                'incident_date' => $date,
                                'timestamp'     => $now,
                            ]);
                        }

                        $update_data = [
                            'current_turn_driver_id' => $next_turn_driver_id,
                            'last_swapping_at' => $now,
                            'shift_deadline_at' => $next_deadline,
                        ];

                        // Pause shifting schedule if unit is going to maintenance
                        if ($needs_maintenance) {
                            $update_data['shift_deadline_at'] = null; // Clears the anchor so it auto-learns again next time
                            $update_data['status'] = 'maintenance'; // Automatically flag the unit status
                            
                            $hours_driven = 0;
                            $hourly_rate = 0;
                            $comp_note = "";
                            
                            if ($unit->last_swapping_at) {
                                $swap_time = \Carbon\Carbon::parse($unit->last_swapping_at);
                            } else {
                                // Fallback: Assume start was 10:00 AM of the record date (or yesterday if currently past 10AM)
                                $swap_time = \Carbon\Carbon::parse($date . ' 10:00:00');
                                if ($swap_time->isFuture()) {
                                    $swap_time->subDay();
                                }
                            }
                            $hours_driven = max(0, $swap_time->diffInMinutes($now) / 60);
                            $hourly_rate = $unit->boundary_rate / 24;
                            $comp_note = sprintf("%.2f hrs x ₱%.2f/hr", $hours_driven, $hourly_rate);

                            $repair_desc = $needs_maintenance_half 
                                ? "Automatic entry: Reported broken down during boundary turnover (Half Boundary).\nComputation: " . $comp_note
                                : "Automatic entry: Reported broken down immediately upon deployment (No Boundary).";
                            
                            if ($needs_maintenance_zero && $hours_driven > 2) {
                                $repair_desc .= "\nNote: Driver claimed 'Free Boundary' but unit was out for " . number_format($hours_driven, 2) . " hrs.";
                            }
                            
                            $dispatcher_notes = trim($request->input('notes', ''));
                            if (!empty($dispatcher_notes)) {
                                $repair_desc .= "\n\nDispatcher Notes:\n" . $dispatcher_notes;
                            }

                            $notes = trim($notes . " [Unit Breakdown: " . $comp_note . " - Schedule Paused]");

                            // Automatically create a Pending Maintenance record
                            \App\Models\Maintenance::create([
                                'unit_id' => $unit_id,
                                'driver_id' => $driver_id,
                                'maintenance_type' => 'corrective',
                                'description' => $repair_desc,
                                'status' => 'pending',
                                'date_started' => $date,
                                'cost' => 0,
                                'created_by' => Auth::id(),
                            ]);

                            // Auto-log to Driver Performance
                            $behavior_desc = $needs_maintenance_half
                                ? "Auto-logged [Breakdown]: Unit broke down after " . number_format($hours_driven, 2) . " hrs on shift."
                                : "Auto-logged [Breakdown]: Unit broke down immediately upon deployment (<= 2 hrs).";
                                
                            if ($needs_maintenance_zero && $hours_driven > 2) {
                                $behavior_desc = "Auto-logged [Breakdown]: Unit broke down after " . number_format($hours_driven, 2) . " hrs. No boundary collected.";
                            }

                            \App\Models\DriverBehavior::create([
                                'unit_id'       => $unit_id,
                                'driver_id'     => $driver_id,
                                'incident_type' => 'Other',
                                'severity'      => ($needs_maintenance_half || ($needs_maintenance_zero && $hours_driven >= 5)) ? 'medium' : 'low',
                                'description'   => $behavior_desc,
                                'incident_date' => $date,
                                'timestamp'     => $now,
                                'is_driver_fault' => false, // Breakdowns are usually not driver's fault
                            ]);
                        }

                        $unit->update($update_data);
                    }

                    $damage_payment = (float) $request->input('damage_payment', 0);

                    $boundary = Boundary::create([
                        'unit_id'         => $unit_id,
                        'driver_id'       => $driver_id,
                        'expected_driver_id' => $expected_driver_id,
                        'date'            => $date,
                        'boundary_amount' => $boundary_amount,
                        'actual_boundary' => $actual_boundary,
                        'damage_payment'  => $damage_payment,
                        'shortage'        => $shortage,
                        'excess'          => $excess,
                        'status'          => $status,
                        'notes'           => $notes,
                        'is_extra_driver' => $is_extra_driver,
                        'vehicle_damaged' => $vehicle_damaged,
                        'is_absent'       => $is_absent,
                        'has_incentive'   => $has_incentive,
                        'created_by'      => Auth::id(),
                    ]);

                    // --- AUTOMATIC DEBT DEDUCTION LOGIC ---
                    if ($damage_payment > 0) {
                        $remaining_to_pay = $damage_payment;
                        
                        // Get all at-fault pending charges for this driver, oldest first
                        $pending_debts = \App\Models\DriverBehavior::where('driver_id', $driver_id)
                            ->where('charge_status', 'pending')
                            ->where('remaining_balance', '>', 0)
                            ->orderBy('timestamp', 'asc')
                            ->get();
                            
                        foreach ($pending_debts as $debt) {
                            if ($remaining_to_pay <= 0) break;
                            
                            $to_deduct = min($remaining_to_pay, $debt->remaining_balance);
                            $debt->total_paid += $to_deduct;
                            $debt->remaining_balance -= $to_deduct;
                            
                            if ($debt->remaining_balance <= 0) {
                                $debt->charge_status = 'paid';
                            }
                            
                            $debt->save();
                            $remaining_to_pay -= $to_deduct;
                        }
                    }

                    // --- AUTOMATIC VIOLATION LOGGING TO CENTRAL FEED ---
                    $now_ts = now();

                    if ($past_cutoff) {
                        \App\Models\DriverBehavior::create([
                            'unit_id'       => $unit_id,
                            'driver_id'     => $driver_id,
                            'incident_type' => 'Late Remittance',
                            'severity'      => 'medium',
                            'description'   => 'Auto-logged [Late Remittance]: Driver submitted boundary past the 10:00 AM cut-off.',
                            'incident_date' => $date,
                            'timestamp'     => $now_ts,
                        ]);
                    }
                    if ($shortage > 0) {
                        \App\Models\DriverBehavior::create([
                            'unit_id'       => $unit_id,
                            'driver_id'     => $driver_id,
                            'incident_type' => 'Short Boundary',
                            'severity'      => 'low',
                            'description'   => 'Auto-logged [Shortage]: Boundary payment was ₱' . number_format($shortage, 2) . ' short.',
                            'incident_date' => $date,
                            'timestamp'     => $now_ts,
                        ]);
                    }
                    if ($is_absent) {
                        // Prevent duplicate 'Absent / No Show' records for the same driver and date
                        $exists = \App\Models\DriverBehavior::where('driver_id', $expected_driver_id)
                            ->whereDate('incident_date', $date)
                            ->where('incident_type', 'Absent / No Show')
                            ->exists();

                        if (!$exists) {
                            \App\Models\DriverBehavior::create([
                                'unit_id'       => $unit_id,
                                'driver_id'     => $expected_driver_id,
                                'incident_type' => 'Absent / No Show',
                                'severity'      => 'medium',
                                'description'   => 'Auto-logged [Absent]: Marked as Absent / No Show because an Extra Driver took their expected shift.',
                                'incident_date' => $date,
                                'timestamp'     => $now_ts,
                            ]);
                        }
                    }

                    $plate = DB::table('units')->where('id', $unit_id)->value('plate_number');
                    $driverName = DB::table('drivers')->where('id', $driver_id)->select(DB::raw("CONCAT(first_name, ' ', last_name) as name"))->value('name');
                    ActivityLogController::log('Boundary Remittance', "Unit: {$plate}\nDriver: {$driverName}\nDate: {$date}\nCollected: ₱" . number_format($actual_boundary, 2) . "\nStatus: " . ucfirst($status));

                    return redirect()->route('boundaries.index')->with('success', 'Boundary record added successfully');
                }
            } else {
                return back()->with('error', 'Please fill in all required fields');
            }
        }

        if ($action === 'update_boundary') {
            $id              = (int) $request->input('id', 0);
            $boundary_amount = (float) $request->input('boundary_amount', 0);
            $actual_boundary = (float) $request->input('actual_boundary', 0);
            $notes           = $request->input('notes', '');
            
            $is_absent = $request->has('is_absent');
            $past_cutoff = $request->has('past_cutoff');
            $vehicle_damaged = $request->has('vehicle_damaged');
            $low_fuel = $request->has('low_fuel');
            $needs_maintenance_half = $request->has('needs_maintenance_half');
            $needs_maintenance_zero = $request->has('needs_maintenance_zero');

            // Server-side strict validation (Max 4 digits, No pure zero unless breakdown)
            if ($actual_boundary >= 10000 || $boundary_amount >= 10000) {
                return back()->with('error', 'Boundary amounts cannot exceed 4 digits (₱9,999.99)');
            }
            if (!$needs_maintenance_zero && $actual_boundary <= 0) {
                return back()->with('error', 'Actual collected amount cannot be zero unless it is an Early Shift Breakdown.');
            }

            $is_valid_amount = ($boundary_amount > 0 || $needs_maintenance_zero);

            if ($id > 0 && $is_valid_amount) {
                $boundary = Boundary::find($id);
                if (!$boundary) {
                    return back()->with('error', 'Boundary record not found');
                }

                $now = now();
                $now_ts = $now;

                // Strip existing system-generated tags from notes to prevent duplicates on edit
                $clean_notes = preg_replace('/\[Automatic Violation:.*?\]/i', '', $notes);
                $clean_notes = preg_replace('/\[Unit Sent to Maintenance.*?\]/i', '', $clean_notes);
                $clean_notes = trim($clean_notes);

                $has_incentive = true;

                if ($is_absent) {
                    $has_incentive = false;
                    $clean_notes .= " [Automatic Violation: Absent / No Show]";
                    
                    // Log behavior for update (prevent duplicates)
                    $target_absent_id = $boundary->expected_driver_id ?: $boundary->driver_id;
                    $exists = DB::table('driver_behavior')
                        ->where('driver_id', $target_absent_id)
                        ->whereDate('incident_date', $boundary->date)
                        ->where('incident_type', 'Absent / No Show')
                        ->exists();

                    if (!$exists) {
                        DB::table('driver_behavior')->insert([
                            'unit_id'       => $boundary->unit_id,
                            'driver_id'     => $target_absent_id,
                            'incident_type' => 'Absent / No Show',
                            'severity'      => 'medium',
                            'description'   => 'Auto-logged [Absent/Update]: Marked as Absent / No Show during record update.',
                            'incident_date' => $boundary->date,
                            'timestamp'     => $now_ts,
                            'created_at'    => $now_ts,
                        ]);
                    }
                }
                if ($past_cutoff) {
                    $has_incentive = false;
                    $clean_notes .= " [Automatic Violation: Late Remittance (Past 10:00 AM)]";
                    
                    // Log behavior for update
                    DB::table('driver_behavior')->insert([
                        'unit_id'       => $boundary->unit_id,
                        'driver_id'     => $boundary->driver_id,
                        'incident_type' => 'Late Remittance',
                        'severity'      => 'medium',
                        'description'   => 'Auto-logged [Late/Update]: Boundary update marked as Late Remittance (Past 10:00 AM).',
                        'timestamp'     => $now_ts,
                        'created_at'    => $now_ts,
                    ]);
                }
                if ($vehicle_damaged) {
                    $has_incentive = false;
                    $clean_notes .= " [Automatic Violation: Vehicle Damaged]";
                    
                    // Log behavior for update
                    DB::table('driver_behavior')->insert([
                        'unit_id'       => $boundary->unit_id,
                        'driver_id'     => $boundary->driver_id,
                        'incident_type' => 'other',
                        'severity'      => 'high',
                        'description'   => 'Auto-logged [Damage/Update]: Vehicle damage reported during record update.',
                        'timestamp'     => $now_ts,
                        'created_at'    => $now_ts,
                    ]);
                }
                if ($low_fuel) {
                    $has_incentive = false;
                    $clean_notes .= " [Automatic Violation: Low Fuel on Return]";
                    
                    // Log behavior for update
                    DB::table('driver_behavior')->insert([
                        'unit_id'       => $boundary->unit_id,
                        'driver_id'     => $boundary->driver_id,
                        'incident_type' => 'other',
                        'severity'      => 'medium',
                        'description'   => 'Auto-logged [Low Fuel/Update]: Driver returned unit without refueling (Update).',
                        'timestamp'     => $now_ts,
                        'created_at'    => $now_ts,
                    ]);
                }

                if ($needs_maintenance_half) {
                    $clean_notes .= " [Unit Sent to Maintenance - Shift Schedule Paused (Half Boundary)]";
                }
                if ($needs_maintenance_zero) {
                    $clean_notes .= " [Unit Sent to Maintenance - Shift Schedule Paused (No Boundary)]";
                }
                
                $clean_notes = trim($clean_notes);

                $shortage = max(0, $boundary_amount - $actual_boundary);
                $excess   = max(0, $actual_boundary - $boundary_amount);
                $status   = $shortage > 0 ? 'shortage' : ($excess > 0 ? 'excess' : 'paid');

                if ($shortage > 0) {
                    $has_incentive = false;
                    $clean_notes .= " [Automatic Violation: Short Boundary]";
                }

                $boundary->update([
                    'boundary_amount' => $boundary_amount,
                    'actual_boundary' => $actual_boundary,
                    'shortage'        => $shortage,
                    'excess'          => $excess,
                    'status'          => $status,
                    'notes'           => trim($clean_notes),
                    'has_incentive'   => $has_incentive,
                    'vehicle_damaged' => $vehicle_damaged ? 1 : 0,
                    'is_absent'       => $is_absent ? 1 : 0,
                ]);

                // --- Auto-log Shortage to Driver Performance for UPDATES ---
                if ($shortage > 0) {
                    DB::table('driver_behavior')->insert([
                        'unit_id'       => $boundary->unit_id,
                        'driver_id'     => $boundary->driver_id,
                        'incident_type' => 'short_boundary',
                        'severity'      => 'low',
                        'description'   => 'Auto-logged [Shortage/Update]: Boundary update resulted in a ₱' . number_format($shortage, 2) . ' shortage.',
                        'timestamp'     => $now_ts,
                        'created_at'    => $now_ts,
                    ]);
                }

                $plate = DB::table('units')->where('id', $boundary->unit_id)->value('plate_number');
                $driverName = DB::table('drivers')->where('id', $boundary->driver_id)->select(DB::raw("CONCAT(first_name, ' ', last_name) as name"))->value('name');
                ActivityLogController::log('Updated Boundary Record', "Unit: {$plate}\nDriver: {$driverName}\nNew Amount: ₱" . number_format($actual_boundary, 2) . " (" . ucfirst($status) . ")");

                return redirect()->route('boundaries.index')->with('success', 'Boundary record updated successfully');
            } else {
                return back()->with('error', 'Please fill in all required fields (Target amount must be valid)');
            }
        }

        return redirect()->route('boundaries.index');
    }

    public function edit($id) { return redirect()->route('boundaries.index'); }
    public function update(Request $request, $id) { return redirect()->route('boundaries.index'); }
    public function destroy($id)
    {
        $boundary = Boundary::findOrFail($id);
        $plate = DB::table('units')->where('id', $boundary->unit_id)->value('plate_number');
        $date = $boundary->date;
        $boundary->delete();

        ActivityLogController::log('Archived Boundary Record', "Unit: {$plate}\nDate: {$date}");

        return redirect()->route('boundaries.index')->with('success', 'Boundary record archived.');
    }
    public function show($id) { return redirect()->route('boundaries.index'); }
    public function create() { return redirect()->route('boundaries.index'); }
}
