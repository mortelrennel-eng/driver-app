<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;
use App\Models\Unit;
use App\Models\Boundary;
use App\Models\Maintenance;
use App\Models\Expense;
use App\Models\User;
use App\Models\SystemAlert;
use App\Models\FranchiseCase;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // Get dashboard statistics
        $stats = [];
        $alerts = [];

        // Total units (matches Unit Management default list)
        $stats['active_units'] = Unit::count();

        // Units with ROI achieved (calculated from real boundary data)
        $stats['roi_units'] = DB::table('units as u')
            ->whereNull('u.deleted_at')
            ->where('u.purchase_cost', '>', 0)
            ->whereExists(function($query) {
                $query->select(DB::raw(1))
                    ->from('boundaries as b')
                    ->whereNull('b.deleted_at')
                    ->whereRaw('b.unit_id = u.id')
                    ->whereIn('b.status', ['paid', 'excess', 'shortage'])
                    ->groupBy('b.unit_id')
                    ->havingRaw('SUM(b.actual_boundary) >= u.purchase_cost');
            })
            ->count();

        // Units under coding (Today only - Strictly Automated via plate ending)
        $todayDay = now()->timezone('Asia/Manila')->format('l');
        $allFleetForCoding = DB::table('units')->whereNull('deleted_at')->get();
        
        $codingUnitsCount = $allFleetForCoding->filter(function($unit) use ($todayDay) {
            $codingDay = $unit->coding_day ?: $this->getCodingDay($unit->plate_number);
            return $codingDay === $todayDay;
        })->count();

        $stats['coding_units'] = $codingUnitsCount;

        // Auto-generate notification if count > 0 and no alert yet for today
        if ($codingUnitsCount > 0) {
            $alertExists = DB::table('system_alerts')
                ->where('type', 'coding_notice')
                ->whereDate('created_at', now()->toDateString())
                ->exists();
            
            if (!$alertExists) {
                DB::table('system_alerts')->insert([
                    'type' => 'coding_notice',
                    'title' => "Today's Unit Coding",
                    'message' => "There are {$codingUnitsCount} units on coding today ({$todayDay}).",
                    'is_resolved' => false,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
        }

        // Auto-resolve missing unit alerts that are no longer applicable
        $activeMissingAlerts = DB::table('system_alerts')
            ->where('type', 'missing_unit')
            ->where('is_resolved', false)
            ->get();

        foreach ($activeMissingAlerts as $ama) {
            $plateStr = str_replace("🚨 Missing Unit: ", "", $ama->title);
            $u = DB::table('units')->where('plate_number', $plateStr)->whereNull('deleted_at')->first();
            
            if (!$u || strtolower($u->status) === 'maintenance' || !$u->shift_deadline_at || Carbon::parse($u->shift_deadline_at)->diffInHours(now(), false) < 24) {
                DB::table('system_alerts')->where('id', $ama->id)->update(['is_resolved' => true, 'updated_at' => now()]);
            }
        }

        // Auto-generate notifications for Missing Units (> 24 hours overdue)
        // RULE: Only units with at least one assigned driver are flagged as missing.
        // Vacant units (no driver_id and no secondary_driver_id) are exempt.
        $missingUnits = DB::table('units')
            ->leftJoin('drivers', 'units.current_turn_driver_id', '=', 'drivers.id')
            ->whereNull('units.deleted_at')
            ->whereRaw('LOWER(units.status) NOT IN (?, ?, ?)', ['maintenance', 'surveillance', 'retired'])
            ->whereNotNull('units.shift_deadline_at')
            ->where('units.shift_deadline_at', '<', now()->subHours(24))
            ->where(function($q) {
                // Must have at least one driver assigned
                $q->whereNotNull('units.driver_id')
                  ->orWhereNotNull('units.secondary_driver_id');
            })
            ->select('units.id', 'units.plate_number', 'drivers.first_name', 'drivers.last_name', 'units.shift_deadline_at')
            ->get();

        foreach ($missingUnits as $unit) {
            $diffHours = now()->diffInHours(Carbon::parse($unit->shift_deadline_at));
            $diffDays = floor($diffHours / 24);
            $driverName = $unit->first_name ? trim($unit->first_name . ' ' . $unit->last_name) : 'Unknown Driver';
            
            $alertTitle = "🚨 Missing Unit: {$unit->plate_number}";
            
            $existingAlert = DB::table('system_alerts')
                ->where('type', 'missing_unit')
                ->where('title', $alertTitle)
                ->where('is_resolved', false)
                ->first();

            // Note: Explicitly mentioning the last driver holds the unit
            $msg = "Unit {$unit->plate_number} has not remitted a boundary for {$diffDays} day(s). The last driver on record is {$driverName}. Need to locate this unit before another driver can use it.";

            if (!$existingAlert) {
                DB::table('system_alerts')->insert([
                    'type' => 'missing_unit',
                    'title' => $alertTitle,
                    'message' => $msg,
                    'is_resolved' => false,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            } else {
                DB::table('system_alerts')
                    ->where('id', $existingAlert->id)
                    ->update([
                        'message' => $msg,
                        'updated_at' => now()
                    ]);
            }

            // --- AUTO-FLAGDOWN LOGIC (48 Hours) ---
            // If unit is missing for 48 hours or more, automatically log a violation to the suspect driver
            if ($diffHours >= 48) {
                $suspectId = DB::table('units')->where('id', $unit->id)->value('current_turn_driver_id');
                
                if ($suspectId) {
                    $deadline = Carbon::parse($unit->shift_deadline_at);
                    
                    // Check if already logged for this specific missing streak (based on incident_date)
                    $existingViolation = DB::table('driver_behavior')
                        ->where('driver_id', $suspectId)
                        ->where('unit_id', $unit->id)
                        ->where('incident_type', 'missing_unit_overdue')
                        ->where('incident_date', $deadline->toDateString())
                        ->exists();

                    if (!$existingViolation) {
                        DB::table('driver_behavior')->insert([
                            'unit_id'       => $unit->id,
                            'driver_id'     => $suspectId,
                            'incident_type' => 'missing_unit_overdue',
                            'severity'      => 'high',
                            'description'   => "Auto-logged [Flagdown]: Unit {$unit->plate_number} is overdue for >48 hours (Missing since {$deadline->format('M d, Y')}). Investigation required.",
                            'latitude'      => 0,
                            'longitude'     => 0,
                            'video_url'     => '',
                            'timestamp'     => now(),
                            'incident_date' => $deadline->toDateString(),
                            'created_at'    => now(),
                        ]);
                    }
                }
            }
        }

        // Units under maintenance
        $stats['maintenance_units'] = DB::table('units')->whereNull('deleted_at')->whereRaw('LOWER(status) = ?', ['maintenance'])->count();

        // Today's boundary collected
        $stats['today_boundary'] = DB::table('boundaries')
            ->whereNull('deleted_at')
            ->whereDate('date', now()->toDateString())
            ->sum('actual_boundary') ?? 0;

        // Today's expenses (General + Salaries)
        $generalExpensesToday = DB::table('expenses')
            ->whereNull('deleted_at')
            ->whereDate('date', now()->toDateString())
            ->sum('amount') ?? 0;

        $salariesToday = DB::table('salaries')
            ->whereDate('pay_date', now()->toDateString())
            ->sum('total_salary') ?? 0;

        $stats['today_expenses'] = $generalExpensesToday + $salariesToday;

        // Dynamic daily target (Sum of boundary rates for all active taxis)
        $stats['daily_target'] = DB::table('units')
            ->whereNull('deleted_at')
            ->whereRaw('LOWER(status) = ?', ['active'])
            ->sum('boundary_rate') ?? 0;
            
        // Fallback to a reasonable target if no units are active yet
        if ($stats['daily_target'] <= 0) {
            $stats['daily_target'] = 2500;
        }

        // Maintenance cost this month
        $stats['monthly_maintenance'] = DB::table('maintenance')
            ->whereNull('deleted_at')
            ->whereMonth('date_started', now()->month)
            ->whereYear('date_started', now()->year)
            ->where('status', '!=', 'cancelled')
            ->sum('cost') ?? 0;

        // Coding cost this month
        $stats['monthly_coding'] = DB::table('coding_records')
            ->whereNull('deleted_at')
            ->whereMonth('date', now()->month)
            ->whereYear('date', now()->year)
            ->where('status', 'completed')
            ->sum('cost') ?? 0;

        // Net income today (Calculation ignores coding fees as per user request)
        $todayMaintenance = DB::table('maintenance')
            ->whereNull('deleted_at')
            ->whereDate('date_started', now()->toDateString())
            ->where('status', '!=', 'cancelled')
            ->sum('cost') ?? 0;
            
        // Coding fees are excluded (assumed 0 as per user instruction)
        $todayCoding = 0; 

        $stats['total_expenses_today'] = $stats['today_expenses'] + $todayMaintenance;
        $stats['net_income'] = $stats['today_boundary'] - $stats['total_expenses_today'];

        // Active drivers — counts drivers who are currently assigned to a unit
        $stats['active_drivers'] = DB::table('drivers as d')
            ->whereNull('d.deleted_at')
            ->whereExists(function($query) {
                $query->select(DB::raw(1))
                    ->from('units')
                    ->whereRaw('units.driver_id = d.id')
                    ->orWhereRaw('units.secondary_driver_id = d.id');
            })
            ->count();

        // Average boundary rate for active units
        $stats['avg_boundary'] = DB::table('units')
            ->whereNull('deleted_at')
            ->where('status', 'active')
            ->avg('boundary_rate') ?? 0;

        // System alerts (unresolved)
        $alerts = DB::table('system_alerts')
            ->where('is_resolved', false)
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        // Revenue trend (dynamic based on period)
        $period = $request->get('period', 30); // Default to 30 days
        $revenue_trend = collect(range($period - 1, 0))->map(function ($daysAgo) use ($period) {
            $date = now()->subDays($daysAgo)->toDateString();
            $boundary = DB::table('boundaries')
                ->whereNull('deleted_at')
                ->whereDate('date', $date)
                ->sum('actual_boundary') ?? 0;
            
            // Format label based on period
            if ($period <= 7) {
                $label = now()->subDays($daysAgo)->format('M j');
            } elseif ($period <= 30) {
                $label = now()->subDays($daysAgo)->format('M j');
            } elseif ($period <= 90) {
                $label = now()->subDays($daysAgo)->format('M j');
            } else {
                $label = now()->subDays($daysAgo)->format('M Y');
            }
            
            return [
                'date' => $label,
                'revenue' => (float) $boundary,
            ];
        })->values()->toArray();

        // Weekly financial trend (last 7 days real data)
        $weekly_data = collect(range(6, 0))->map(function ($daysAgo) {
            $date = now()->subDays($daysAgo)->toDateString();
            $boundary = DB::table('boundaries')->whereNull('deleted_at')->whereDate('date', $date)->sum('actual_boundary') ?? 0;
            $expenses = DB::table('expenses')->whereNull('deleted_at')->whereDate('date', $date)->sum('amount') ?? 0;
            return [
                'day'      => now()->subDays($daysAgo)->format('D'),
                'boundary' => (float) $boundary,
                'expenses' => (float) $expenses,
                'net'      => (float) ($boundary - $expenses),
            ];
        })->values()->toArray();

        $allUnitsForStats = DB::table('units')->whereNull('deleted_at')->get();
        $codingUnitsCount = $allUnitsForStats->filter(function($unit) use ($todayDay) {
            $codingDay = $unit->coding_day ?: $this->getCodingDay($unit->plate_number);
            return $codingDay === $todayDay;
        })->count();

        $maintenanceUnitsCount = $allUnitsForStats->filter(function($unit) {
            return strtolower($unit->status) === 'maintenance';
        })->count();

        $retiredUnitsCount = $allUnitsForStats->filter(function($unit) {
            return strtolower($unit->status) === 'retired';
        })->count();

        // Active units are those that are NOT coding, NOT maintenance, and NOT retired
        $activeUnitsCount = $allUnitsForStats->count() - $codingUnitsCount - $maintenanceUnitsCount - $retiredUnitsCount;

        $unit_status_data = [
            ['status' => 'Active',            'count' => $activeUnitsCount],
            ['status' => 'Under Maintenance', 'count' => $maintenanceUnitsCount],
            ['status' => 'Coding',            'count' => $codingUnitsCount],
            ['status' => 'Retired',           'count' => $retiredUnitsCount],
        ];

        // Unit status distribution for pie chart
        $unit_status_distribution_data = $unit_status_data;

        // Unit performance (top performing units)
        $unit_performance = DB::table('units as u')
            ->whereNull('u.deleted_at')
            ->leftJoin('boundaries as b', function($join) {
                $join->on('u.id', '=', 'b.unit_id')
                    ->whereNull('b.deleted_at');
            })
            ->select('u.plate_number', DB::raw('COALESCE(SUM(b.actual_boundary), 0) as total_boundary'), 'u.boundary_rate')
            ->where('u.status', 'active')
            ->groupBy('u.id', 'u.plate_number', 'u.boundary_rate')
            ->orderByDesc('total_boundary')
            ->limit(10)
            ->get()
            ->map(function($unit) {
                return [
                    'unit' => $unit->plate_number,
                    'performance' => (float) $unit->total_boundary,
                    'target' => (float) $unit->boundary_rate * 30, // Monthly target
                ];
            });

        // Expense breakdown by category
        $expense_breakdown = DB::table('expenses')
            ->whereNull('deleted_at')
                        ->select('category', DB::raw('SUM(amount) as total'))
            ->whereMonth('date', now()->month)
            ->whereYear('date', now()->year)
            ->groupBy('category')
            ->orderByDesc('total')
            ->get()
            ->map(function($expense) {
                return [
                    'category' => $expense->category,
                    'amount' => (float) $expense->total,
                ];
            });

        // Top Drivers (Performance recognition)
        $top_drivers = DB::table('drivers as d')
            ->whereNull('d.deleted_at')
            ->leftJoin('boundaries as b', function($join) {
                $join->on('d.id', '=', 'b.driver_id')
                    ->whereNull('b.deleted_at');
            })
            ->leftJoin('driver_behavior as db', 'd.id', '=', 'db.driver_id')
            ->select(
                DB::raw("CONCAT(COALESCE(d.first_name,''), ' ', COALESCE(d.last_name,'')) as full_name"),
                DB::raw('COUNT(CASE WHEN b.status IN ("paid", "excess", "shortage") THEN 1 END) as good_days'),
                DB::raw('SUM(b.actual_boundary) as total_boundary'),
                DB::raw('COUNT(db.id) as incident_count')
            )
            ->whereIn('d.driver_status', ['available', 'assigned'])
            ->groupBy('d.id', 'd.first_name', 'd.last_name')
            ->orderByDesc('good_days')
            ->orderByDesc('total_boundary')
            ->limit(5)
            ->get()
            ->map(function($driver) {
                return [
                    'name' => $driver->full_name,
                    'score' => (int) $driver->good_days,
                    'total' => (float) $driver->total_boundary
                ];
            });

        return view('dashboard', compact('stats', 'alerts', 'weekly_data', 'unit_status_data', 'unit_status_distribution_data', 'revenue_trend', 'unit_performance', 'expense_breakdown', 'top_drivers'));
    }

    public function getRealTimeData()
    {
        // Get fresh dashboard statistics
        $stats = [];
        $alerts = [];

        // Total units (all non-deleted units)
        $stats['active_units'] = DB::table('units')->whereNull('deleted_at')->count();

        // Units with ROI achieved (calculated from real boundary data)
        $stats['roi_units'] = DB::table('units as u')
            ->whereNull('u.deleted_at')
            ->where('u.purchase_cost', '>', 0)
            ->whereExists(function($query) {
                $query->select(DB::raw(1))
                    ->from('boundaries as b')
                    ->whereNull('b.deleted_at')
                    ->whereRaw('b.unit_id = u.id')
                    ->whereIn('b.status', ['paid', 'excess', 'shortage'])
                    ->groupBy('b.unit_id')
                    ->havingRaw('SUM(b.actual_boundary) >= u.purchase_cost');
            })
            ->count();

        // Units under coding (Today only - Strictly Automated)
        $todayDay = now()->format('l');
        $allUnitsForStats = DB::table('units')->whereNull('deleted_at')->get();
        $stats['coding_units'] = $allUnitsForStats->filter(function($unit) use ($todayDay) {
            $codingDay = $unit->coding_day ?: $this->getCodingDay($unit->plate_number);
            return $codingDay === $todayDay;
        })->count();

        // Units under maintenance
        $stats['maintenance_units'] = DB::table('units')->whereNull('deleted_at')->whereRaw('LOWER(status) = ?', ['maintenance'])->count();

        // Enhanced Net Income with filtering support
        $filter = request('net_income_filter', 'today');
        $income = 0;
        $expense = 0;

        if ($filter === 'month') {
            $income = DB::table('boundaries')->whereNull('deleted_at')->whereMonth('date', now()->month)->whereYear('date', now()->year)->sum('actual_boundary') ?? 0;
            $genEx = DB::table('expenses')->whereNull('deleted_at')->whereMonth('date', now()->month)->whereYear('date', now()->year)->sum('amount') ?? 0;
            $mntEx = DB::table('maintenance')->whereNull('deleted_at')->whereMonth('date_started', now()->month)->whereYear('date_started', now()->year)->where('status', '!=', 'cancelled')->sum('cost') ?? 0;
            $salEx = DB::table('salaries')->whereMonth('pay_date', now()->month)->whereYear('pay_date', now()->year)->sum('total_salary') ?? 0;
            $expense = $genEx + $mntEx + $salEx;
        } elseif ($filter === 'year') {
            $income = DB::table('boundaries')->whereNull('deleted_at')->whereYear('date', now()->year)->sum('actual_boundary') ?? 0;
            $genEx = DB::table('expenses')->whereNull('deleted_at')->whereYear('date', now()->year)->sum('amount') ?? 0;
            $mntEx = DB::table('maintenance')->whereNull('deleted_at')->whereYear('date_started', now()->year)->where('status', '!=', 'cancelled')->sum('cost') ?? 0;
            $salEx = DB::table('salaries')->whereYear('pay_date', now()->year)->sum('total_salary') ?? 0;
            $expense = $genEx + $mntEx + $salEx;
        } else {
            // Default Today
            $stats['today_boundary'] = DB::table('boundaries')
                ->whereNull('deleted_at')
                ->whereDate('date', now()->toDateString())
                ->sum('actual_boundary') ?? 0;

            $stats['today_expenses'] = DB::table('expenses')
                ->whereNull('deleted_at')
                ->whereDate('date', now()->toDateString())
                ->sum('amount') ?? 0;

            $income = $stats['today_boundary'];
            $genEx = $stats['today_expenses'];
            $mntEx = DB::table('maintenance')->whereNull('deleted_at')->whereDate('date_started', now()->toDateString())->where('status', '!=', 'cancelled')->sum('cost') ?? 0;
            $salEx = DB::table('salaries')->whereDate('pay_date', now()->toDateString())->sum('total_salary') ?? 0;
            $expense = $genEx + $mntEx + $salEx;
        }

        $stats['net_income'] = $income - $expense;

        // Dynamic daily target
        $stats['daily_target'] = DB::table('units')
            ->whereNull('deleted_at')
            ->whereRaw('LOWER(status) = ?', ['active'])
            ->sum('boundary_rate') ?? 0;
            
        if ($stats['daily_target'] <= 0) {
            $stats['daily_target'] = 2500;
        }

        // Active drivers — counts drivers who are currently assigned to a unit
        $stats['active_drivers'] = DB::table('drivers as d')
            ->whereNull('d.deleted_at')
            ->whereExists(function($query) {
                $query->select(DB::raw(1))
                    ->from('units')
                    ->whereRaw('units.driver_id = d.id')
                    ->orWhereRaw('units.secondary_driver_id = d.id');
            })
            ->count();

        // Average boundary rate for active units
        $stats['avg_boundary'] = DB::table('units')
            ->whereNull('deleted_at')
            ->where('status', 'active')
            ->avg('boundary_rate') ?? 0;

        // Maintenance cost this month
        $stats['monthly_maintenance'] = DB::table('maintenance')
            ->whereNull('deleted_at')
            ->whereMonth('date_started', now()->month)
            ->whereYear('date_started', now()->year)
            ->where('status', '!=', 'cancelled')
            ->sum('cost') ?? 0;

        // System alerts
        $alerts = DB::table('system_alerts')
            ->where('is_resolved', false)
            ->orderByDesc('created_at')
            ->limit(10)
            ->get()
            ->map(function($alert) {
                return [
                    'message' => $alert->message,
                    'severity' => $alert->severity,
                    'alert_type' => $alert->alert_type
                ];
            });

        // Weekly financial trend
        $weekly_data = collect(range(6, 0))->map(function ($daysAgo) {
            $date = now()->subDays($daysAgo)->toDateString();
            $boundary = DB::table('boundaries')
                ->whereNull('deleted_at')
                ->whereDate('date', $date)
                ->sum('actual_boundary') ?? 0;
            $expenses = DB::table('expenses')
                ->whereNull('deleted_at')
                ->whereDate('date', $date)
                ->sum('amount') ?? 0;
            return [
                'day'      => now()->subDays($daysAgo)->format('D'),
                'boundary' => (float) $boundary,
                'expenses' => (float) $expenses,
                'net'      => (float) ($boundary - $expenses),
            ];
        })->values()->toArray();

        // Unit status distribution data (Using synchronized logic)
        $maintenanceUnitsCount = $allUnitsForStats->filter(function($unit) {
            return strtolower($unit->status) === 'maintenance';
        })->count();

        $retiredUnitsCount = $allUnitsForStats->filter(function($unit) {
            return strtolower($unit->status) === 'retired';
        })->count();

        $codingUnitsCount = $stats['coding_units'];
        $activeUnitsCount = $allUnitsForStats->count() - $codingUnitsCount - $maintenanceUnitsCount - $retiredUnitsCount;

        $unit_status_data = [
            ['status' => 'Active',            'count' => $activeUnitsCount],
            ['status' => 'Under Maintenance', 'count' => $maintenanceUnitsCount],
            ['status' => 'Coding',            'count' => $codingUnitsCount],
            ['status' => 'Retired',           'count' => $retiredUnitsCount],
        ];

        // Revenue trend (last 30 days)
        $revenue_trend = collect(range(29, 0))->map(function ($daysAgo) {
            $date = now()->subDays($daysAgo)->toDateString();
            $boundary = DB::table('boundaries')
                ->whereNull('deleted_at')
                ->whereDate('date', $date)
                ->sum('actual_boundary') ?? 0;
            return [
                'date' => now()->subDays($daysAgo)->format('M j'),
                'revenue' => (float) $boundary,
            ];
        })->values()->toArray();

        // Unit performance (top performing units)
        $unit_performance = DB::table('units as u')
            ->whereNull('u.deleted_at')
            ->leftJoin('boundaries as b', function($join) {
                $join->on('u.id', '=', 'b.unit_id')
                    ->whereNull('b.deleted_at');
            })
            ->select('u.plate_number', DB::raw('COALESCE(SUM(b.actual_boundary), 0) as total_boundary'), 'u.boundary_rate')
            ->where('u.status', 'active')
            ->groupBy('u.id', 'u.plate_number', 'u.boundary_rate')
            ->orderByDesc('total_boundary')
            ->limit(10)
            ->get()
            ->map(function($unit) {
                return [
                    'unit' => $unit->plate_number,
                    'performance' => (float) $unit->total_boundary,
                    'target' => (float) $unit->boundary_rate * 30, // Monthly target
                ];
            });

        // Expense breakdown by category
        $expense_breakdown = DB::table('expenses')
            ->whereNull('deleted_at')
            ->select('category', DB::raw('SUM(amount) as total'))
            ->whereMonth('date', now()->month)
            ->whereYear('date', now()->year)
            ->groupBy('category')
            ->orderByDesc('total')
            ->get()
            ->map(function($expense) {
                return [
                    'category' => $expense->category,
                    'amount' => (float) $expense->total,
                ];
            });

        // Placeholder for expense breakdown if empty
        if ($expense_breakdown->isEmpty() || $expense_breakdown->every(fn($d) => $d['amount'] == 0)) {
            $expense_breakdown = collect([
                ['category' => 'Maintenance', 'amount' => 4500],
                ['category' => 'Repairs', 'amount' => 3200],
                ['category' => 'Salaries', 'amount' => 8000],
                ['category' => 'Parts', 'amount' => 2100],
                ['category' => 'Others', 'amount' => 1200]
            ]);
        }

        // Top Drivers (Performance recognition)
        $top_drivers = DB::table('drivers as d')
            ->whereNull('d.deleted_at')
            ->leftJoin('boundaries as b', function($join) {
                $join->on('d.id', '=', 'b.driver_id')
                    ->whereNull('b.deleted_at');
            })
            ->leftJoin('driver_behavior as db', 'd.id', '=', 'db.driver_id')
            ->select(
                DB::raw("CONCAT(COALESCE(d.first_name,''), ' ', COALESCE(d.last_name,'')) as full_name"),
                DB::raw('COUNT(CASE WHEN b.status IN ("paid", "excess", "shortage") THEN 1 END) as good_days'),
                DB::raw('SUM(b.actual_boundary) as total_boundary'),
                DB::raw('COUNT(db.id) as incident_count')
            )
            ->whereIn('d.driver_status', ['available', 'assigned'])
            ->groupBy('d.id', 'd.first_name', 'd.last_name')
            ->having('incident_count', '=', 0)
            ->orderByDesc('good_days')
            ->orderByDesc('total_boundary')
            ->limit(5)
            ->get()
            ->map(function($driver) {
                return [
                    'name' => $driver->full_name,
                    'score' => (int) $driver->good_days,
                    'total' => (float) $driver->total_boundary
                ];
            });

        // Placeholder for top drivers if empty
        if ($top_drivers->isEmpty() || $top_drivers->every(fn($d) => $d['score'] == 0)) {
            $top_drivers = collect([
                ['name' => 'Bernardo Silva', 'score' => 28, 'total' => 42000],
                ['name' => 'Kevin De Bruyne', 'score' => 26, 'total' => 39000],
                ['name' => 'Erling Haaland', 'score' => 25, 'total' => 37500],
                ['name' => 'Phil Foden', 'score' => 22, 'total' => 33000],
                ['name' => 'Rodri Hernandez', 'score' => 20, 'total' => 30000]
            ]);
        }

        return response()->json([
            'success' => true,
            'stats' => $stats,
            'alerts' => $alerts,
            'charts' => [
                'weekly_data' => $weekly_data,
                'unit_status_data' => $unit_status_data,
                'revenue_trend' => $revenue_trend,
                'unit_performance' => $unit_performance,
                'expense_breakdown' => $expense_breakdown,
                'top_drivers' => $top_drivers
            ]
        ]);
    }

    public function getRevenueTrend(Request $request)
    {
        $period = $request->get('period', 30);
        
        $revenue_trend = collect(range($period - 1, 0))->map(function ($daysAgo) use ($period) {
            $date = now()->subDays($daysAgo)->toDateString();
            $boundary = DB::table('boundaries')
                ->whereNull('deleted_at')
                ->whereDate('date', $date)
                ->sum('actual_boundary') ?? 0;
            
            // Format label based on period
            if ($period <= 7) {
                $label = now()->subDays($daysAgo)->format('M j');
            } elseif ($period <= 30) {
                $label = now()->subDays($daysAgo)->format('M j');
            } elseif ($period <= 90) {
                $label = now()->subDays($daysAgo)->format('M j');
            } else {
                $label = now()->subDays($daysAgo)->format('M Y');
            }
            
            return [
                'date' => $label,
                'revenue' => (float) $boundary,
            ];
        })->values()->toArray();

        return response()->json([
            'success' => true,
            'data' => $revenue_trend
        ]);
    }

    public function getUnitsOverview()
    {
        try {
            $todayDay = now()->format('l');

            $units = DB::table('units')
                ->whereNull('deleted_at')
                ->select('id', 'status', 'boundary_rate', 'purchase_cost', 'plate_number', 'driver_id')
                ->orderBy('plate_number')
                ->get()
                ->map(function($unit) use ($todayDay) {
                    $displayStatus = strtolower($unit->status);
                    
                    // Automation: Identify if it should be coding based on plate number
                    $plateCodingDay = $this->getCodingDay($unit->plate_number);
                    $shouldBeCodingToday = ($plateCodingDay === $todayDay);

                    if ($shouldBeCodingToday) {
                        $displayStatus = 'coding';
                    } elseif ($displayStatus === 'coding' && !$shouldBeCodingToday) {
                        $displayStatus = 'active';
                    }
                    
                    // Get total boundary for this unit from real data
                    $totalBoundary = DB::table('boundaries')
                        ->whereNull('deleted_at')
                        ->where('unit_id', $unit->id)
                        ->sum('actual_boundary') ?? 0;
                    
                    // Get today's boundary for real-time data
                    $todayBoundary = DB::table('boundaries')
                        ->whereNull('deleted_at')
                        ->where('unit_id', $unit->id)
                        ->whereDate('date', now()->toDateString())
                        ->sum('actual_boundary') ?? 0;
                    
                    // Get driver information
                    $driverName = 'No Driver';
                    if ($unit->driver_id) {
                        $driver = DB::table('drivers')
                            ->where('id', $unit->driver_id)
                            ->whereNull('deleted_at')
                            ->select('first_name', 'last_name', 'nickname')
                            ->first();
                        
                        if ($driver) {
                            $driverName = trim(($driver->first_name ?? '') . ' ' . ($driver->last_name ?? ''));
                            if (empty($driverName)) $driverName = $driver->nickname ?? 'Unknown';
                        }
                    }
                    
                    // Calculate Net ROI percentage (Revenue - Costs)
                    $totalCosts = (DB::table('maintenance')->where('unit_id', $unit->id)->whereNull('deleted_at')->where('status', '!=', 'cancelled')->sum('cost') ?? 0) + 
                                  (DB::table('coding_records')->where('unit_id', $unit->id)->whereNull('deleted_at')->sum('cost') ?? 0);
                    
                    $netRevenue = $totalBoundary - $totalCosts;
                    $roiPercentage = 0;
                    if ($unit->purchase_cost > 0 && $netRevenue > 0) {
                        $roiPercentage = min(100, round(($netRevenue / $unit->purchase_cost) * 100, 2));
                    }
                    
                    // Calculate days to ROI based on real performance
                    $daysToROI = 0;
                    if ($unit->purchase_cost > 0 && $totalBoundary > 0 && $roiPercentage < 100) {
                        // Method 1: Calculate based on recent 30-day average
                        $recent30DaysBoundary = DB::table('boundaries')
                            ->where('unit_id', $unit->id)
                            ->where('boundary_amount', '>', 0)
                            ->whereNull('deleted_at')
                            ->whereDate('date', '>=', now()->subDays(30)->toDateString())
                            ->sum('actual_boundary') ?? 0;
                        
                        // Method 2: Calculate based on last 10 days average
                        $last10DaysBoundary = DB::table('boundaries')
                            ->where('unit_id', $unit->id)
                            ->where('boundary_amount', '>', 0)
                            ->whereNull('deleted_at')
                            ->whereDate('date', '>=', now()->subDays(10)->toDateString())
                            ->sum('actual_boundary') ?? 0;
                        
                        // Method 3: Calculate based on last 7 days average
                        $last7DaysBoundary = DB::table('boundaries')
                            ->where('unit_id', $unit->id)
                            ->where('boundary_amount', '>', 0)
                            ->whereNull('deleted_at')
                            ->whereDate('date', '>=', now()->subDays(7)->toDateString())
                            ->sum('actual_boundary') ?? 0;
                        
                        // Choose the most reliable method
                        $dailyAverage = 0;
                        
                        if ($last7DaysBoundary > 0) {
                            // Use last 7 days if available (most recent)
                            $dailyAverage = $last7DaysBoundary / 7;
                        } elseif ($last10DaysBoundary > 0) {
                            // Use last 10 days
                            $dailyAverage = $last10DaysBoundary / 10;
                        } elseif ($recent30DaysBoundary > 0) {
                            // Use last 30 days
                            $dailyAverage = $recent30DaysBoundary / 30;
                        } else {
                            // Fallback to overall average
                            $activeDays = DB::table('boundaries')
                                ->where('unit_id', $unit->id)
                                ->where('boundary_amount', '>', 0)
                                ->count();
                            if ($activeDays > 0) {
                                $dailyAverage = $totalBoundary / $activeDays;
                            }
                        }
                        
                        // Calculate days to ROI with accuracy improvements
                        if ($dailyAverage > 0) {
                            $remainingAmount = $unit->purchase_cost - $totalBoundary;
                            $daysToROI = ceil($remainingAmount / $dailyAverage);
                            
                            // Cap at maximum 365 days (1 year) for realistic estimation
                            $daysToROI = min($daysToROI, 365);
                            
                            // If ROI is very close (within 5%), show as "Almost there"
                            if ($daysToROI <= 5) {
                                $daysToROI = 0; // Will be handled as "Almost there"
                            }
                        } else {
                            $daysToROI = 999; // No recent activity indicator
                        }
                    }
                    
                    return [
                        'id' => $unit->id,
                        'plate_number' => $unit->plate_number,
                        'status' => $displayStatus,
                        'boundary_rate' => (float) $unit->boundary_rate,
                        'total_boundary' => (float) $totalBoundary,
                        'today_boundary' => (float) $todayBoundary,
                        'purchase_cost' => (float) $unit->purchase_cost,
                        'driver_name' => $driverName,
                        'driver_id' => $unit->driver_id,
                        'roi_percentage' => $roiPercentage,
                        'roi_achieved' => $roiPercentage >= 100,
                        'days_to_roi' => $daysToROI,
                        'last_activity' => $this->getLastActivity($unit->id),
                        'performance_rating' => $this->getPerformanceRating($roiPercentage)
                    ];
                });

            // Calculate real stats from actual data
            $stats = [
                'total_units' => $units->count(),
                'vacant_units' => $units->whereNull('driver_id')->count(),
                'active_units' => $units->whereNotNull('driver_id')->count(),
                'coding_units' => $units->where('status', 'coding')->count(),
                'roi_units' => $units->where('roi_achieved', true)->count(),
                'avg_roi' => $units->avg('roi_percentage') ?: 0,
                'total_investment' => $units->sum('purchase_cost'),
                'total_collected' => $units->sum('total_boundary'),
                'today_collected' => $units->sum('today_boundary')
            ];

            return response()->json([
                'success' => true,
                'units' => $units,
                'stats' => $stats,
                'data_source' => 'real_database',
                'last_updated' => now()->toDateTimeString()
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error loading units overview: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error loading units data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get last activity for a unit
     */
    private function getLastActivity($unitId)
    {
        $lastBoundary = DB::table('boundaries')
            ->where('unit_id', $unitId)
            ->orderBy('date', 'desc')
            ->first();
            
        return $lastBoundary ? $lastBoundary->date : null;
    }

    /**
     * Get performance rating based on ROI
     */
    private function getPerformanceRating($roiPercentage)
    {
        if ($roiPercentage >= 100) return 'excellent';
        if ($roiPercentage >= 75) return 'good';
        if ($roiPercentage >= 50) return 'average';
        return 'growing';
    }

    /**
     * Get daily boundary collections with detailed information
     */
    public function getDailyBoundaryCollections(Request $request)
    {
        try {
            // Get optional date from request, default to today
            $date = $request->get('date', now()->toDateString());

            // Get boundary collections for the specific date with complete information
            $collections = DB::table('boundaries as b')
                ->leftJoin('units as u', 'b.unit_id', '=', 'u.id')
                ->leftJoin('drivers as d', 'b.driver_id', '=', 'd.id')
                ->select([
                    'b.id',
                    'b.unit_id',
                    'b.actual_boundary',
                    'b.boundary_amount',
                    'b.date',
                    'u.plate_number',
                    'd.first_name',
                    'd.last_name',
                    'd.nickname',
                    'd.id as driver_id'
                ])
                ->whereNull('b.deleted_at')
                ->whereDate('b.date', $date)
                ->orderBy('b.id', 'desc')
                ->get()
                ->map(function($collection) {
                    $driverName = trim(($collection->first_name ?? '') . ' ' . ($collection->last_name ?? ''));
                    if (empty($driverName)) $driverName = $collection->nickname ?? 'No Driver Assigned';
                    
                    return [
                        'id' => $collection->id,
                        'unit_id' => $collection->unit_id,
                        'plate_number' => $collection->plate_number,
                        'driver_name' => $driverName,
                        'driver_id' => $collection->driver_id,
                        'boundary_amount' => (float) ($collection->actual_boundary ?? 0),
                        'date' => $collection->date,
                        'time' => 'N/A', 
                        'location' => 'Main Office', 
                        'status' => 'verified' 
                    ];
                });

            // Calculate statistics
            $today = now()->toDateString();
            $yesterday = now()->subDay()->toDateString();
            $month = now()->month;
            $year = now()->year;

            $stats = [
                'total_today' => DB::table('boundaries')->whereNull('deleted_at')->whereDate('date', $today)->count(),
                'amount_yesterday' => DB::table('boundaries')->whereNull('deleted_at')->whereDate('date', $yesterday)->sum('actual_boundary'),
                'amount_monthly' => DB::table('boundaries')->whereNull('deleted_at')->whereMonth('date', $month)->whereYear('date', $year)->sum('actual_boundary'),
                'total_yearly_amount' => DB::table('boundaries')->whereNull('deleted_at')->whereYear('date', $year)->sum('actual_boundary'),
                'filter_date' => $date
            ];

            return response()->json([
                'success' => true,
                'collections' => $collections,
                'stats' => $stats,
                'data_source' => 'real_database',
                'last_updated' => now()->toDateTimeString()
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error loading daily boundary collections: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error loading boundary collections: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get net income details with breakdown
     */
    public function getNetIncomeDetails()
    {
        try {
            // Get income data from boundaries
            $incomeData = DB::table('boundaries as b')
                ->leftJoin('units as u', 'b.unit_id', '=', 'u.id')
                ->leftJoin('drivers as d', 'b.driver_id', '=', 'd.id')
                ->leftJoin('users as du', 'd.user_id', '=', 'du.id')
                ->select([
                    'b.id',
                    'b.unit_id',
                    'b.actual_boundary',
                    'b.boundary_amount',
                    'b.date',
                    'u.plate_number',
                    'du.name as driver_name',
                    'd.id as driver_id'
                ])
                ->whereNull('b.deleted_at')
                ->orderBy('b.date', 'desc')
                ->orderBy('b.id', 'desc')
                ->get()
                ->map(function($item) {
                    return [
                        'id' => $item->id,
                        'type' => 'income',
                        'description' => 'Boundary Collection - ' . $item->plate_number,
                        'category' => 'Boundary Income',
                        'amount' => (float) $item->actual_boundary,
                        'date' => $item->date,
                        'source' => $item->plate_number,
                        'reference' => 'Boundary #' . $item->id,
                        'plate_number' => $item->plate_number,
                        'driver_name' => $item->driver_name
                    ];
                });

            // Initialize expense data as empty collection
            $expenseData = collect();
            $expenseTable = null;

            // Try different expense table names - but handle gracefully
            try {
                // Check for office_expenses table
                if (Schema::hasTable('office_expenses')) {
                    $expenseTable = 'office_expenses';
                }
                // Check for expenses table
                elseif (Schema::hasTable('expenses')) {
                    $expenseTable = 'expenses';
                }
                // Check for office_expense table (singular)
                elseif (Schema::hasTable('office_expense')) {
                    $expenseTable = 'office_expense';
                }

                if ($expenseTable) {
                    if ($expenseTable === 'expenses') {
                        // The 'expenses' table has different column names
                        $expenseData = DB::table('expenses as oe')
                            ->leftJoin('users as u', 'oe.created_by', '=', 'u.id')
                            ->select([
                                'oe.id',
                                'oe.category as expense_type',
                                'oe.amount',
                                'oe.description',
                                'oe.date',
                                'oe.created_by as user_id',
                                'u.name as user_name'
                            ])
                            ->whereNull('oe.deleted_at')
                            ->orderBy('oe.date', 'desc')
                            ->orderBy('oe.id', 'desc')
                            ->get()
                            ->map(function($item) {
                                return [
                                    'id' => $item->id,
                                    'type' => 'expense',
                                    'description' => $item->description ?: $item->expense_type,
                                    'category' => $item->expense_type,
                                    'amount' => (float) $item->amount,
                                    'date' => $item->date,
                                    'source' => $item->user_name ?: 'Office / System',
                                    'reference' => 'Expense #' . $item->id,
                                    'expense_type' => $item->expense_type,
                                    'user_name' => $item->user_name ?: 'System Admin'
                                ];
                            });
                    } else {
                        $expenseData = DB::table($expenseTable . ' as oe')
                            ->leftJoin('users as u', 'oe.user_id', '=', 'u.id')
                            ->select([
                                'oe.id',
                                'oe.expense_type',
                                'oe.amount',
                                'oe.description',
                                'oe.date',
                                'oe.user_id',
                                'u.name as user_name'
                            ])
                            ->whereNull('oe.deleted_at')
                            ->orderBy('oe.date', 'desc')
                            ->orderBy('oe.id', 'desc')
                            ->get()
                            ->map(function($item) {
                                return [
                                    'id' => $item->id,
                                    'type' => 'expense',
                                    'description' => $item->description ?: $item->expense_type,
                                    'category' => $item->expense_type,
                                    'amount' => (float) $item->amount,
                                    'date' => $item->date,
                                    'source' => $item->user_name,
                                    'reference' => 'Expense #' . $item->id,
                                    'expense_type' => $item->expense_type,
                                    'user_name' => $item->user_name
                                ];
                            });
                    }
                }
            } catch (\Exception $expenseError) {
                Log::error('Error loading expense data: ' . $expenseError->getMessage());
                // Continue with empty expense data
                $expenseData = collect();
            }

            // Add Maintenance costs as expenses
            $maintenanceExpenses = DB::table('maintenance as m')
                ->join('units as u', 'm.unit_id', '=', 'u.id')
                ->where('m.status', '!=', 'cancelled')
                ->whereNull('m.deleted_at')
                ->select('m.*', 'u.plate_number')
                ->get()
                ->map(function($item) {
                    return [
                        'id' => $item->id,
                        'type' => 'maintenance',
                        'description' => 'Unit ' . $item->plate_number . ' - ' . ($item->maintenance_type ?: 'Maintenance'),
                        'category' => 'Maintenance',
                        'amount' => (float) $item->cost,
                        'date' => $item->date_started,
                        'source' => $item->mechanic_name ?: 'Workshop',
                        'reference' => 'MNT-#' . $item->id,
                        'expense_type' => $item->maintenance_type,
                        'user_name' => $item->mechanic_name
                    ];
                });

            // Add Coding costs as expenses
            $codingExpenses = DB::table('coding_records as c')
                ->join('units as u', 'c.unit_id', '=', 'u.id')
                ->whereNull('c.deleted_at')
                ->select('c.*', 'u.plate_number')
                ->get()
                ->map(function($item) {
                    return [
                        'id' => $item->id,
                        'type' => 'coding',
                        'description' => 'Unit ' . $item->plate_number . ' - Coding Fee',
                        'category' => 'Coding',
                        'amount' => (float) $item->cost,
                        'date' => $item->date,
                        'source' => 'System',
                        'reference' => 'COD-#' . $item->id,
                        'expense_type' => 'Coding Fee',
                        'user_name' => 'Automated'
                    ];
                });

            // Add Salaries as expenses
            $salaryExpenses = DB::table('salaries as s')
                ->leftJoin('users as u', function($join) {
                    $join->on('s.employee_id', '=', 'u.id')->where('s.source', '=', 'user');
                })
                ->leftJoin('staff as st', function($join) {
                    $join->on('s.employee_id', '=', 'st.id')->where('s.source', '=', 'staff');
                })
                ->select('s.*', DB::raw('COALESCE(u.full_name, st.name) as employee_name'))
                ->get()
                ->map(function($item) {
                    return [
                        'id' => $item->id,
                        'type' => 'salary',
                        'description' => 'Salary Payment - ' . $item->employee_name,
                        'category' => 'Payroll',
                        'amount' => (float) $item->total_salary,
                        'date' => $item->pay_date,
                        'source' => 'Finance',
                        'reference' => 'SAL-#' . $item->id,
                        'expense_type' => 'Salary',
                        'user_name' => 'System'
                    ];
                });

            // Combine all financial data
            $allData = $incomeData->concat($expenseData)
                ->concat($maintenanceExpenses)
                ->concat($codingExpenses)
                ->concat($salaryExpenses)
                ->sortByDesc('date')
                ->values();

            // Calculate statistics
            $totalIncome = $incomeData->sum('amount');
            $totalExpenses = $expenseData->sum('amount') + 
                            $maintenanceExpenses->sum('amount') + 
                            $codingExpenses->sum('amount') + 
                            $salaryExpenses->sum('amount');
            $netIncome = $totalIncome - $totalExpenses;
            $profitMargin = $totalIncome > 0 ? (($netIncome / $totalIncome) * 100) : 0;

            $stats = [
                'total_income' => $totalIncome,
                'total_expenses' => $totalExpenses,
                'net_income' => $netIncome,
                'profit_margin' => $profitMargin,
                'income_count' => $incomeData->count(),
                'expense_count' => $expenseData->count(),
                'total_transactions' => $allData->count(),
                'expense_table_used' => $expenseTable,
                'debug_info' => [
                    'income_data_count' => $incomeData->count(),
                    'expense_data_count' => $expenseData->count(),
                    'expense_table_found' => $expenseTable ? 'yes' : 'no'
                ]
            ];

            return response()->json([
                'success' => true,
                'income_data' => $allData,
                'stats' => $stats,
                'data_source' => 'real_database',
                'last_updated' => now()->toDateTimeString()
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error loading net income details: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => 'Error loading net income details: ' . $e->getMessage(),
                'debug_info' => [
                    'error_type' => get_class($e),
                    'error_code' => $e->getCode(),
                    'error_file' => $e->getFile(),
                    'error_line' => $e->getLine()
                ]
            ], 500);
        }
    }

    /**
     * Get units currently under maintenance or historical maintenance records.
     */
    public function getMaintenanceUnits(Request $request)
    {
        try {
            $filter = $request->query('filter', 'all'); // 'all', 'preventive', 'complete'
            $hasMaintenances = Schema::hasTable('maintenance');
            $hasDrivers = Schema::hasTable('drivers');

            if ($filter === 'complete') {
                // Query historical completed maintenance records
                $unitsQuery = DB::table('maintenance as m')
                    ->join('units as u', 'm.unit_id', '=', 'u.id')
                    ->where('m.status', '=', 'completed')
                    ->whereNull('m.deleted_at')
                    ->whereNull('u.deleted_at');
            } else {
                // Default logic for active maintenance units (Corrective, Preventive, Emergency)
                $unitsQuery = DB::table('units as u')
                    ->where('u.status', '=', 'maintenance')
                    ->whereNull('u.deleted_at');

                if ($hasMaintenances) {
                    // Use a LEFT JOIN instead of an INNER/WHERE-based join for strict filtering in "all" view
                    $latestM = DB::table('maintenance')
                        ->select('unit_id', DB::raw('MAX(id) as latest_id'))
                        ->whereNull('deleted_at')
                        ->groupBy('unit_id');

                    $unitsQuery->leftJoinSub($latestM, 'latest_m', function($join) {
                        $join->on('u.id', '=', 'latest_m.unit_id');
                    })->leftJoin('maintenance as m', 'latest_m.latest_id', '=', 'm.id');

                    // If NO filter is applied (all), we show ALL maintenance units
                    if ($filter !== 'all') {
                        if ($filter === 'preventive') {
                            $unitsQuery->where('m.maintenance_type', 'LIKE', '%preventive%');
                        } elseif ($filter === 'corrective') {
                            $unitsQuery->where('m.maintenance_type', 'LIKE', '%corrective%');
                        } elseif ($filter === 'emergency') {
                            $unitsQuery->where('m.maintenance_type', 'LIKE', '%emergency%');
                        }
                    }
                }
            }

            if ($hasDrivers) {
                $unitsQuery
                    ->leftJoin('drivers as d', 'u.driver_id', '=', 'd.id');
            }

            $select = [
                'u.id',
                'u.plate_number',
                'u.status',
                'u.purchase_cost',
                'u.boundary_rate',
                'u.created_at',
                $hasDrivers ? DB::raw("CONCAT(COALESCE(d.first_name,''), ' ', COALESCE(d.last_name,'')) as driver_name") : DB::raw('NULL as driver_name'),
                $hasMaintenances ? 'm.id as maintenance_id' : DB::raw('NULL as maintenance_id'),
                $hasMaintenances ? 'm.maintenance_type' : DB::raw('NULL as maintenance_type'),
                $hasMaintenances ? 'm.description' : DB::raw('NULL as description'),
                $hasMaintenances ? 'm.date_started as start_date' : DB::raw('NULL as start_date'),
                $hasMaintenances ? 'm.date_completed as end_date' : DB::raw('NULL as end_date'),
                $hasMaintenances ? 'm.status as maintenance_status' : DB::raw('NULL as maintenance_status'),
                $hasMaintenances ? 'm.cost as maintenance_cost' : DB::raw('NULL as maintenance_cost'),
            ];

            $maintenanceUnits = $unitsQuery
                ->select($select)
                ->when($filter === 'complete', function ($q) {
                    $q->orderBy('m.date_completed', 'desc');
                }, function ($q) use ($hasMaintenances) {
                    $q->when($hasMaintenances, function ($sq) {
                        $sq->orderBy('m.date_started', 'desc');
                    }, function ($sq) {
                        $sq->orderBy('u.id', 'desc');
                    });
                })
                ->get()
                ->map(function($unit) {
                    $startDate = data_get($unit, 'start_date');
                    $endDate = data_get($unit, 'end_date');
                    return [
                        'id' => $unit->id,
                        'plate_number' => $unit->plate_number,
                        'status' => $unit->status,
                        'driver_name' => $unit->driver_name,
                        'maintenance_type' => $unit->maintenance_type ?: 'Maintenance',
                        'description' => $unit->description ?: 'No description available',
                        'start_date' => $startDate,
                        'end_date' => $endDate,
                        'estimated_completion' => $endDate ?: 'Not specified',
                        'maintenance_status' => $unit->maintenance_status ?: 'Ongoing',
                        'maintenance_cost' => (float) ($unit->maintenance_cost ?? 0),
                        'purchase_cost' => (float) ($unit->purchase_cost ?? 0),
                        'boundary_rate' => (float) ($unit->boundary_rate ?? 0)
                    ];
                });

            // Calculate Global Overview Stats for the modal
            $latestMSub = DB::table('maintenance')
                ->select('unit_id', DB::raw('MAX(id) as latest_id'))
                ->whereNull('deleted_at')
                ->groupBy('unit_id');

            $mStats = DB::table('units as u')
                ->where('u.status', 'maintenance')
                ->whereNull('u.deleted_at')
                ->leftJoinSub($latestMSub, 'lm', 'u.id', '=', 'lm.unit_id')
                ->leftJoin('maintenance as m', 'lm.latest_id', '=', 'm.id')
                ->select([
                    DB::raw('COUNT(*) as total'),
                    DB::raw('SUM(CASE WHEN m.maintenance_type LIKE "%preventive%" THEN 1 ELSE 0 END) as preventive'),
                    DB::raw('SUM(CASE WHEN m.maintenance_type LIKE "%corrective%" THEN 1 ELSE 0 END) as corrective'),
                    DB::raw('SUM(CASE WHEN m.maintenance_type LIKE "%emergency%" THEN 1 ELSE 0 END) as emergency'),
                ])
                ->first();

            $completedCount = DB::table('maintenance')
                ->where('status', 'completed')
                ->whereNull('deleted_at')
                ->count();

            $avgMaintenanceDays = $maintenanceUnits->count() > 0 ? 
                $maintenanceUnits->filter(function($unit) {
                    return !empty($unit['start_date']) && !empty($unit['end_date']);
                })->map(function($unit) {
                    return Carbon::parse($unit['end_date'])->diffInDays(Carbon::parse($unit['start_date']));
                })->avg() : 0;

            $stats = [
                'total_maintenance' => (int) $mStats->total,
                'preventive_maintenance' => (int) ($mStats->preventive ?? 0),
                'corrective_maintenance' => (int) ($mStats->corrective ?? 0),
                'emergency_maintenance' => (int) ($mStats->emergency ?? 0),
                'completed_total' => $completedCount,
                'avg_maintenance_days' => round($avgMaintenanceDays, 1),
                'total_maintenance_cost' => $maintenanceUnits->sum('maintenance_cost')
            ];

            return response()->json([
                'success' => true,
                'units' => $maintenanceUnits,
                'stats' => $stats,
                'filter_applied' => $filter,
                'data_source' => 'real_database',
                'last_updated' => now()->toDateTimeString()
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error loading maintenance units: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error loading maintenance units: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get active drivers with detailed information
     */
    public function getActiveDrivers()
    {
        try {
            if (!Schema::hasTable('drivers')) {
                return response()->json([
                    'success' => true,
                    'drivers' => [],
                    'stats' => [
                        'active_drivers' => 0,
                        'assigned_units' => 0,
                        'avg_boundary' => 0,
                        'top_performers' => 0,
                        'total_boundary_collected' => 0
                    ],
                    'data_source' => 'real_database',
                    'last_updated' => now()->toDateTimeString()
                ]);
            }

            $select = [
                'd.id',
                'd.user_id',
                DB::raw("CONCAT(COALESCE(d.first_name,''), ' ', COALESCE(d.last_name,'')) as name"),
                DB::raw('NULL as email'),
                DB::raw('COUNT(DISTINCT unit.id) as assigned_units'),
                DB::raw('COALESCE(SUM(b.actual_boundary), 0) as total_boundary'),
                DB::raw('COALESCE(AVG(b.actual_boundary), 0) as avg_boundary'),
                DB::raw('GROUP_CONCAT(DISTINCT unit.plate_number) as plate_numbers'),
            ];
            $groupBy = ['d.id', 'd.user_id', 'd.first_name', 'd.last_name'];

            if (Schema::hasColumn('drivers', 'hire_date')) {
                $select[] = 'd.hire_date';
                $groupBy[] = 'd.hire_date';
            } else {
                $select[] = DB::raw('NULL as hire_date');
            }

            if (Schema::hasColumn('drivers', 'license_number')) {
                $select[] = 'd.license_number';
                $groupBy[] = 'd.license_number';
            } else {
                $select[] = DB::raw('NULL as license_number');
            }

            if (Schema::hasColumn('drivers', 'contact_number')) {
                $select[] = 'd.contact_number as phone';
                $groupBy[] = 'd.contact_number';
            } elseif (Schema::hasColumn('drivers', 'phone')) {
                $select[] = 'd.phone';
                $groupBy[] = 'd.phone';
            } else {
                $select[] = DB::raw('NULL as phone');
            }

            if (Schema::hasColumn('drivers', 'address')) {
                $select[] = 'd.address';
                $groupBy[] = 'd.address';
            } else {
                $select[] = DB::raw('NULL as address');
            }

            $query = DB::table('drivers as d')
                ->leftJoin('units as unit', function($join) {
                    $join->on('d.id', '=', 'unit.driver_id')
                         ->orOn('d.id', '=', 'unit.secondary_driver_id');
                })
                ->leftJoin('boundaries as b', function($join) {
                    $join->on('unit.id', '=', 'b.unit_id')
                         ->whereNull('b.deleted_at');
                })
                ->select($select)
                ->whereNull('d.deleted_at')
                ->whereNull('unit.deleted_at');

            if (Schema::hasColumn('drivers', 'status')) {
                $query->where('d.status', '=', 'active');
            }

            $activeDrivers = $query
                ->groupBy($groupBy)
                ->orderBy('d.first_name', 'asc')
                ->get()
                ->map(function($driver) {
                    $avgBoundary = (float) ($driver->avg_boundary ?? 0);
                    
                    // Base performance rating
                    $performanceRating = 'average';
                    if ($avgBoundary >= 2000) $performanceRating = 'excellent';
                    elseif ($avgBoundary >= 1500) $performanceRating = 'good';
                    elseif ($avgBoundary >= 1000) $performanceRating = 'average';
                    else $performanceRating = 'needs_improvement';

                    // Refined Top Performer Check (No accidents, No short boundaries)
                    $isTopPerformer = ($performanceRating === 'excellent');
                    
                    if ($isTopPerformer) {
                        // Check for any "short" payments in boundaries
                        $hasShort = DB::table('boundaries')
                            ->whereNull('deleted_at')
                            ->where('driver_id', $driver->user_id) // Using user_id for boundaries matches DB structure
                            ->where('status', 'short')
                            ->where('date', '>=', now()->subDays(30))
                            ->exists();
                            
                        // Check for accidents in maintenance for driver's units
                        $hasAccident = DB::table('maintenance as m')
                            ->join('units as u', 'm.unit_id', '=', 'u.id')
                            ->whereNull('m.deleted_at')
                            ->whereNull('u.deleted_at')
                            ->where('u.driver_id', $driver->id)
                            ->where('m.maintenance_type', 'like', '%accident%')
                            ->where('m.date_started', '>=', now()->subDays(30))
                            ->exists();
                            
                        if ($hasShort || $hasAccident) {
                            $isTopPerformer = false;
                        }
                    }

                    return [
                        'id' => $driver->id,
                        'name' => $driver->name,
                        'email' => $driver->email,
                        'license_number' => $driver->license_number,
                        'phone' => $driver->phone,
                        'address' => $driver->address,
                        'hire_date' => $driver->hire_date,
                        'assigned_units' => (int) ($driver->assigned_units ?? 0),
                        'plate_numbers' => $driver->plate_numbers ?? null,
                        'total_boundary' => (float) ($driver->total_boundary ?? 0),
                        'avg_boundary' => $avgBoundary,
                        'performance_rating' => $performanceRating,
                        'is_top_performer' => $isTopPerformer
                    ];
                });

            // Calculate statistics
            $totalDrivers = $activeDrivers->count();
            $vacantDrivers = $activeDrivers->where('assigned_units', 0)->count();
            $activeWithUnits = $activeDrivers->where('assigned_units', '>', 0)->count();
            $topPerformersCount = $activeDrivers->where('is_top_performer', true)->count();

            $stats = [
                'total_drivers' => $totalDrivers,
                'vacant_drivers' => $vacantDrivers,
                'active_with_units' => $activeWithUnits,
                'top_performers' => $topPerformersCount,
                'total_boundary_collected' => $activeDrivers->sum('total_boundary')
            ];

            return response()->json([
                'success' => true,
                'drivers' => $activeDrivers,
                'stats' => $stats,
                'data_source' => 'real_database',
                'last_updated' => now()->toDateTimeString()
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error loading active drivers: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error loading active drivers: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get coding units with detailed information
     */
    public function getCodingUnits()
    {
        try {
            $hasMaintenances = Schema::hasTable('maintenance');
            $hasDrivers = Schema::hasTable('drivers');
            $unitsQuery = DB::table('units as u')->whereNull('u.deleted_at');
            $today = now()->format('l');

            if ($hasDrivers) {
                $unitsQuery
                    ->leftJoin('drivers as d', 'u.driver_id', '=', 'd.id');
            }

            if ($hasMaintenances) {
                // Join with the separate coding_records table instead of maintenance
                $hasCodingRecords = Schema::hasTable('coding_records');
                if ($hasCodingRecords) {
                    $latestC = DB::table('coding_records')
                        ->select('unit_id', DB::raw('MAX(id) as latest_id'))
                        ->whereNull('deleted_at')
                        ->groupBy('unit_id');

                    $unitsQuery->leftJoinSub($latestC, 'latest_c', function($join) {
                        $join->on('u.id', '=', 'latest_c.unit_id');
                    })->leftJoin('coding_records as c', 'latest_c.latest_id', '=', 'c.id');
                }
            }

            $select = [
                'u.id',
                'u.plate_number',
                'u.status',
                'u.purchase_cost',
                'u.boundary_rate',
                'u.created_at',
                $hasDrivers ? DB::raw("CONCAT(COALESCE(d.first_name,''), ' ', COALESCE(d.last_name,'')) as driver_name") : DB::raw('NULL as driver_name'),
                'c.id as coding_id',
                DB::raw("'Coding' as coding_type"),
                'c.description',
                'c.date as start_date',
                'c.date as end_date',
                'c.status as coding_status',
                'c.cost as coding_cost',
            ];

            $allUnits = $unitsQuery->select($select)->get();
            
            $codingUnits = $allUnits->filter(function($unit) use ($today) {
                $plateCodingDay = $this->getCodingDay($unit->plate_number);
                $isManualCoding = ($unit->status === 'coding' || ($unit->coding_id && $unit->coding_status !== 'completed'));
                return ($plateCodingDay === $today || $isManualCoding);
            })->values();

            $codingUnits = $codingUnits->map(function($unit) {
                    $startDate = data_get($unit, 'start_date');
                    $endDate = data_get($unit, 'end_date');
                    
                    // Determine coding day based on plate ending (LTO rules)
                    $codingDay = $this->getCodingDay($unit->plate_number);

                    return [
                        'id' => $unit->id,
                        'plate_number' => $unit->plate_number,
                        'status' => $unit->status,
                        'driver_name' => $unit->driver_name,
                        'coding_type' => $unit->coding_type ?: 'Coding',
                        'coding_day' => $codingDay,
                        'description' => $unit->description ?: 'No description available',
                        'start_date' => $startDate,
                        'end_date' => $endDate,
                        'estimated_completion' => $endDate ?: 'Not specified',
                        'coding_status' => $unit->coding_status ?: 'Ongoing',
                        'coding_cost' => (float) ($unit->coding_cost ?? 0),
                        'purchase_cost' => (float) ($unit->purchase_cost ?? 0),
                        'boundary_rate' => (float) ($unit->boundary_rate ?? 0)
                    ];
                });

            // Calculate statistics
            $totalCoding = $codingUnits->count();
            $completedCoding = $codingUnits->where('coding_status', 'completed')->count();
            $pendingCoding = $codingUnits->where('coding_status', 'pending')->count();
            $avgCodingDays = $totalCoding > 0 ? 
                $codingUnits->filter(function($unit) {
                    return !empty($unit['start_date']) && !empty($unit['end_date']);
                })->map(function($unit) {
                    return Carbon::parse($unit['end_date'])->diffInDays(Carbon::parse($unit['start_date']));
                })->avg() : 0;

            $stats = [
                'total_coding' => $totalCoding,
                'completed_coding' => $completedCoding,
                'pending_coding' => $pendingCoding,
                'avg_coding_days' => round($avgCodingDays, 1),
                'total_coding_cost' => $codingUnits->sum('coding_cost')
            ];

            return response()->json([
                'success' => true,
                'units' => $codingUnits,
                'stats' => $stats,
                'data_source' => 'real_database',
                'last_updated' => now()->toDateTimeString()
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error loading coding units: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error loading coding units: ' . $e->getMessage()
            ], 500);
        }
    }

    private function getCodingDay($plateNumber)
    {
        if (empty($plateNumber)) return 'Unknown';
        $lastDigit = @substr(preg_replace('/[^0-9]/', '', $plateNumber), -1);
        if ($lastDigit === false || $lastDigit === '') return 'Unknown';
        
        if ($lastDigit == 1 || $lastDigit == 2) return 'Monday';
        if ($lastDigit == 3 || $lastDigit == 4) return 'Tuesday';
        if ($lastDigit == 5 || $lastDigit == 6) return 'Wednesday';
        if ($lastDigit == 7 || $lastDigit == 8) return 'Thursday';
        if ($lastDigit == 9 || $lastDigit == 0) return 'Friday';
        
        return 'Unknown';
    }
}
