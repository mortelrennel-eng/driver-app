<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $days = (int)($request->input('days', 7));
        if (!in_array($days, [7, 30, 90, 365])) $days = 7;

        $cacheKey = 'api_dashboard_data_days_' . $days;

        // Cache the entire dashboard dataset for 60 seconds (1 minute) to prevent database resource exhaustion on shared hosting plans.
        $responseData = Cache::remember($cacheKey, 60, function() use ($days) {
            $today         = now()->timezone('Asia/Manila')->toDateString();
            $startOfMonth  = now()->timezone('Asia/Manila')->startOfMonth()->toDateString();
            $todayDay      = now()->timezone('Asia/Manila')->format('l');

            // ── STATS ──────────────────────────────────────────────────────────────

            $stats['active_units'] = DB::table('units')->whereNull('deleted_at')->count();

            $stats['roi_units'] = DB::table('units as u')
                ->whereNull('u.deleted_at')
                ->whereExists(function ($q) {
                    $q->select(DB::raw(1))
                      ->from('boundaries as b')
                      ->whereNull('b.deleted_at')
                      ->whereRaw('b.unit_id = u.id')
                      ->whereIn('b.status', ['paid', 'excess', 'shortage'])
                      ->groupBy('b.unit_id')
                      ->havingRaw('SUM(b.actual_boundary) >= u.purchase_cost');
                })->count();
            $stats['roi_achieved'] = $stats['roi_units'];

            // Boundary stats (Matching Web)
            $yesterday = now()->timezone('Asia/Manila')->subDay()->toDateString();
            $year      = now()->timezone('Asia/Manila')->year;
            $month     = now()->timezone('Asia/Manila')->month;

            $stats['today_boundary'] = (float)(DB::table('boundaries')->whereNull('deleted_at')->whereDate('date', $today)->sum('actual_boundary') ?? 0);
            $stats['yesterday_boundary'] = (float)(DB::table('boundaries')->whereNull('deleted_at')->whereDate('date', $yesterday)->sum('actual_boundary') ?? 0);
            $stats['month_boundary'] = (float)(DB::table('boundaries')->whereNull('deleted_at')->whereMonth('date', $month)->whereYear('date', $year)->sum('actual_boundary') ?? 0);
            $stats['year_boundary'] = (float)(DB::table('boundaries')->whereNull('deleted_at')->whereYear('date', $year)->sum('actual_boundary') ?? 0);

            // Detailed boundaries for today (for modal)
            $boundaryList = DB::table('boundaries as b')
                ->join('units as u', 'b.unit_id', '=', 'u.id')
                ->leftJoin('drivers as d', 'b.driver_id', '=', 'd.id')
                ->select(
                    'b.id', 'b.actual_boundary', 'b.status', 'b.date',
                    'u.plate_number',
                    DB::raw("CONCAT(COALESCE(d.first_name,''), ' ', COALESCE(d.last_name,'')) as driver_name")
                )
                ->whereNull('b.deleted_at')
                ->whereDate('b.date', $today)
                ->orderByDesc('b.created_at')
                ->get();

            // Expenses
            $genEx  = (float)(DB::table('expenses')->whereNull('deleted_at')->whereDate('date', $today)->sum('amount') ?? 0);
            $salEx  = (float)(DB::table('salaries')->whereDate('pay_date', $today)->sum('total_salary') ?? 0);
            $mntEx  = (float)(DB::table('maintenance')->whereNull('deleted_at')->whereDate('date_started', $today)->where('status', '!=', 'cancelled')->sum('cost') ?? 0);
            $stats['total_expenses_today'] = $genEx + $salEx + $mntEx;
            $stats['today_expenses']       = $stats['total_expenses_today'];
            $stats['expense_general']      = $genEx;
            $stats['expense_salary']       = $salEx;
            $stats['expense_maintenance']  = $mntEx;

            // Month expenses (Matching Web: whereMonth and whereYear)
            $mGenEx = (float)(DB::table('expenses')->whereNull('deleted_at')->whereMonth('date', $month)->whereYear('date', $year)->sum('amount') ?? 0);
            $mSalEx = (float)(DB::table('salaries')->whereMonth('pay_date', $month)->whereYear('pay_date', $year)->sum('total_salary') ?? 0);
            $mMntEx = (float)(DB::table('maintenance')->whereNull('deleted_at')->whereMonth('date_started', $month)->whereYear('date_started', $year)->where('status', '!=', 'cancelled')->sum('cost') ?? 0);
            $stats['total_expenses_month'] = $mGenEx + $mSalEx + $mMntEx;

            // 3. Expense Breakdown (Detailed Categories from Web)
            $genExpenses = DB::table('expenses')
                ->whereNull('deleted_at')
                ->select('category', DB::raw('SUM(amount) as total'))
                ->whereMonth('date', $month)
                ->whereYear('date', $year)
                ->groupBy('category')
                ->get();

            $expenseBreakdown = $genExpenses->map(fn($e) => [
                'name' => $e->category ?: 'General',
                'value' => (float)$e->total
            ])->toArray();

            // Add Salaries & Maintenance
            $expenseBreakdown[] = ['name' => 'Salaries', 'value' => (float)$mSalEx];
            $expenseBreakdown[] = ['name' => 'Maintenance', 'value' => (float)$mMntEx];

            // Filter out zero values to keep chart clean
            $expenseBreakdown = array_values(array_filter($expenseBreakdown, fn($e) => $e['value'] > 0));

            // Net income
            $stats['net_income']       = $stats['today_boundary'] - $stats['total_expenses_today'];
            $stats['net_income_month'] = $stats['month_boundary'] - $stats['total_expenses_month'];

            // Maintenance units
            $stats['maintenance_units'] = DB::table('maintenance')
                ->join('units', 'maintenance.unit_id', '=', 'units.id')
                ->whereNull('maintenance.deleted_at')->whereNull('units.deleted_at')
                ->whereNotIn(DB::raw('LOWER(maintenance.status)'), ['complete', 'completed', 'cancelled'])
                ->count();

            // Active drivers
            $stats['active_drivers'] = DB::table('drivers')->whereNull('deleted_at')->count();



            // ── CHART DATA ─────────────────────────────────────────────────────────

            // 1. Revenue Trend (dynamic period)
            $revenueTrend = [];
            for ($i = $days - 1; $i >= 0; $i--) {
                $d    = now()->timezone('Asia/Manila')->subDays($i)->toDateString();
                $rev  = (float)(DB::table('boundaries')->whereNull('deleted_at')->whereDate('date', $d)->sum('actual_boundary') ?? 0);
                $gx   = (float)(DB::table('expenses')->whereNull('deleted_at')->whereDate('date', $d)->sum('amount') ?? 0);

                $label = ($days <= 30)
                    ? now()->timezone('Asia/Manila')->subDays($i)->format('M d')
                    : now()->timezone('Asia/Manila')->subDays($i)->format('M d y');

                // Matching Web: Trend only shows boundaries vs general expenses
                $revenueTrend[] = ['date' => $label, 'revenue' => $rev, 'expenses' => $gx, 'netIncome' => $rev - $gx];
            }

            // 4. UNIT PERFORMANCE (Copying Web Logic Exactly)
            $unitPerformance = DB::table('units as u')
                ->whereNull('u.deleted_at')
                ->leftJoin('boundaries as b', function($join) {
                    $join->on('u.id', '=', 'b.unit_id')->whereNull('b.deleted_at');
                })
                ->select('u.plate_number', DB::raw('COALESCE(SUM(b.actual_boundary), 0) as total_boundary'), 'u.boundary_rate')
                ->where('u.status', 'active')
                ->groupBy('u.id', 'u.plate_number', 'u.boundary_rate')
                ->orderByDesc('total_boundary')
                ->limit(10)
                ->get()
                ->map(fn($u) => [
                    'plate' => $u->plate_number,
                    'actual' => (float)$u->total_boundary,
                    'target' => (float)$u->boundary_rate * 30
                ])->toArray();

            // 5. TOP DRIVERS (Copying Web Logic Exactly)
            $topDriversData = DB::table('drivers as d')
                ->whereNull('d.deleted_at')
                ->leftJoin('boundaries as b', function($join) {
                    $join->on('d.id', '=', 'b.driver_id')->whereNull('b.deleted_at');
                })
                ->select(
                    DB::raw("CONCAT(COALESCE(d.first_name,''), ' ', COALESCE(d.last_name,'')) as name"),
                    DB::raw('COUNT(CASE WHEN b.status IN ("paid", "excess", "shortage") THEN 1 END) as good_days'),
                    DB::raw('SUM(b.actual_boundary) as total')
                )
                ->whereIn('d.driver_status', ['available', 'assigned'])
                ->groupBy('d.id', 'd.first_name', 'd.last_name')
                ->orderByDesc('good_days')
                ->orderByDesc('total')
                ->limit(10)
                ->get();

            $topDrivers = $topDriversData->map(fn($d) => [
                'name' => $d->name,
                'total' => (float)$d->total,
                'score' => (int)$d->good_days
            ])->toArray();


            // 4. Weekly Financial Overview (Matching Web Exactly: Boundaries vs Expenses)
            $weeklyData = [];
            for ($i = 6; $i >= 0; $i--) {
                $d   = now()->timezone('Asia/Manila')->subDays($i)->toDateString();
                $rev = (float)(DB::table('boundaries')->whereNull('deleted_at')->whereDate('date', $d)->sum('actual_boundary') ?? 0);
                $gx  = (float)(DB::table('expenses')->whereNull('deleted_at')->whereDate('date', $d)->sum('amount') ?? 0);
                $weeklyData[] = [
                    'day' => now()->timezone('Asia/Manila')->subDays($i)->format('D'),
                    'boundary' => $rev,
                    'expenses' => $gx,
                    'net' => $rev - $gx
                ];
            }

            // 5. Unit Status Distribution (Matching Web Legend Exactly)
            $unitStatusDist = [
                ['name' => 'Active',             'value' => (int)DB::table('units')->whereNull('deleted_at')->where('status', 'active')->count()],
                ['name' => 'Under Maintenance',  'value' => (int)DB::table('units')->whereNull('deleted_at')->where('status', 'maintenance')->count()],
                ['name' => 'Coding',             'value' => (int)DB::table('units')->whereNull('deleted_at')->where('status', 'coding')->count()],
                ['name' => 'Missing / Stolen',    'value' => (int)DB::table('units')->whereNull('deleted_at')->where('status', 'missing')->count()],
                ['name' => 'Retired',            'value' => (int)DB::table('units')->whereNull('deleted_at')->where('status', 'retired')->count()],
            ];

            // ── MODAL DATA ─────────────────────────────────────────────────────────

            // Maintenance details
            $maintenanceList = DB::table('maintenance')
                ->join('units', 'maintenance.unit_id', '=', 'units.id')
                ->leftJoin('drivers', 'units.driver_id', '=', 'drivers.id')
                ->select(
                    'maintenance.id', 'maintenance.maintenance_type as type', 'maintenance.status',
                    'maintenance.cost', 'maintenance.description',
                    'maintenance.date_started', 'maintenance.date_completed',
                    'units.plate_number',
                    DB::raw("CONCAT(COALESCE(drivers.first_name,''), ' ', COALESCE(drivers.last_name,'')) as driver_name")
                )
                ->whereNull('maintenance.deleted_at')->whereNull('units.deleted_at')
                ->orderByDesc('maintenance.date_started')->limit(50)->get();

            // Drivers list (MATCHING WEB LOGIC EXACTLY)
            $driversList = DB::table('drivers as d')
                ->leftJoin('units as u', function($join) {
                    $join->on('d.id', '=', 'u.driver_id')
                         ->orOn('d.id', '=', 'u.secondary_driver_id')
                         ->whereNull('u.deleted_at');
                })
                ->leftJoin('boundaries as b', function($join) {
                    $join->on('u.id', '=', 'b.unit_id')
                         ->whereNull('b.deleted_at');
                })
                ->select(
                    'd.id', 'd.first_name', 'd.last_name',
                    'd.contact_number as phone', 'd.license_number', 'd.driver_status',
                    'd.hire_date', 'd.address',
                    DB::raw('COUNT(DISTINCT u.id) as assigned_units'),
                    DB::raw('GROUP_CONCAT(DISTINCT u.plate_number) as plate_numbers'),
                    DB::raw('COALESCE(SUM(b.actual_boundary), 0) as total_collected'),
                    DB::raw('COALESCE(AVG(b.actual_boundary), 0) as avg_boundary')
                )
                ->whereNull('d.deleted_at')
                ->groupBy('d.id', 'd.first_name', 'd.last_name', 'd.contact_number', 'd.license_number', 'd.driver_status', 'd.hire_date', 'd.address')
                ->orderBy('d.first_name')
                ->get()
                ->map(function($driver) {
                    $avg = (float)$driver->avg_boundary;
                    $rating = 'average';
                    if ($avg >= 2000) $rating = 'excellent';
                    elseif ($avg >= 1500) $rating = 'good';
                    elseif ($avg >= 1000) $rating = 'average';
                    else $rating = 'needs_improvement';

                    return [
                        'id' => $driver->id,
                        'name' => trim($driver->first_name . ' ' . $driver->last_name),
                        'license_number' => $driver->license_number,
                        'phone' => $driver->phone,
                        'address' => $driver->address,
                        'hire_date' => $driver->hire_date,
                        'assigned_units' => (int)$driver->assigned_units,
                        'plate_number' => $driver->plate_numbers, // For frontend compatibility
                        'plate_numbers' => $driver->plate_numbers,
                        'total_collected' => (float)$driver->total_collected,
                        'performance_rating' => $rating,
                        'is_top_performer' => ($rating === 'excellent')
                    ];
                });

            // Recalculate accurate stats for parity matching the web
            $stats['total_drivers'] = $driversList->count();
            $stats['vacant_drivers_count'] = $driversList->where('assigned_units', 0)->count();
            $stats['active_drivers_count'] = $driversList->where('assigned_units', '>', 0)->count();
            $stats['top_performers_count'] = $driversList->where('is_top_performer', true)->count();

            // Coding units (MATCHING WEB DETAILED FETCH)
            $hasCodingRecords = Schema::hasTable('coding_records');
            $codingUnitsQuery = DB::table('units as u')->whereNull('u.deleted_at');
            $todayName = now()->timezone('Asia/Manila')->format('l');

            if (Schema::hasTable('drivers')) {
                $codingUnitsQuery->leftJoin('drivers as d', 'u.driver_id', '=', 'd.id');
            }

            if ($hasCodingRecords) {
                $latestC = DB::table('coding_records')
                    ->select('unit_id', DB::raw('MAX(id) as latest_id'))
                    ->whereNull('deleted_at')
                    ->groupBy('unit_id');

                $codingUnitsQuery->leftJoinSub($latestC, 'latest_c', function($join) {
                    $join->on('u.id', '=', 'latest_c.unit_id');
                })->leftJoin('coding_records as c', 'latest_c.latest_id', '=', 'c.id');
            }

            $codingList = $codingUnitsQuery->select([
                'u.id', 'u.plate_number', 'u.status', 'u.purchase_cost', 'u.boundary_rate',
                DB::raw("CONCAT(COALESCE(d.first_name,''), ' ', COALESCE(d.last_name,'')) as driver_name"),
                'c.id as coding_id', 'c.description', 'c.date as start_date', 'c.date as end_date',
                'c.status as coding_status', 'c.cost as coding_cost'
            ])->get()
            ->filter(function($unit) use ($todayName) {
                $plateCodingDay = $this->getCodingDay($unit->plate_number);
                $isManualCoding = ($unit->status === 'coding' || ($unit->coding_id && $unit->coding_status !== 'completed'));
                return ($plateCodingDay === $todayName || $isManualCoding);
            })
            ->map(function($unit) {
                return [
                    'id' => $unit->id,
                    'plate_number' => $unit->plate_number,
                    'status' => $unit->status,
                    'driver_name' => $unit->driver_name,
                    'coding_type' => 'Coding',
                    'coding_day' => $this->getCodingDay($unit->plate_number),
                    'description' => $unit->description ?: 'No description available',
                    'start_date' => $unit->start_date,
                    'end_date' => $unit->end_date,
                    'estimated_completion' => $unit->end_date ?: 'Not specified',
                    'coding_status' => $unit->coding_status ?: 'Ongoing',
                    'coding_cost' => (float)$unit->coding_cost
                ];
            })->values()->toArray();

            // Update stats coding count to match the web's list count
            $stats['coding_units'] = count($codingList);

            // Units list for Overview Modal (Matching Web)
            $unitsList = DB::table('units as u')
                ->whereNull('u.deleted_at')
                ->leftJoin('boundaries as b', function($join) {
                    $join->on('u.id', '=', 'b.unit_id')->whereNull('b.deleted_at');
                })
                ->leftJoin('drivers as d', 'u.driver_id', '=', 'd.id')
                ->select(
                    'u.id', 'u.plate_number', 'u.status', 'u.purchase_cost',
                    DB::raw('COALESCE(SUM(b.actual_boundary), 0) as total_collection'),
                    'd.id as driver_id'
                )
                ->groupBy('u.id', 'u.plate_number', 'u.status', 'u.purchase_cost', 'd.id')
                ->get()
                ->map(function($u) {
                    $roi = ($u->purchase_cost > 0) ? ($u->total_collection / $u->purchase_cost) * 100 : 0;
                    
                    // Determine display status like web
                    $displayStatus = $u->status;
                    if ($u->status === 'active' && !$u->driver_id) {
                        $displayStatus = 'vacant';
                    }

                    return [
                        'id' => $u->id,
                        'plate' => $u->plate_number,
                        'status' => strtolower($displayStatus),
                        'total_collection' => (float)$u->total_collection,
                        'roi' => round($roi, 1),
                    ];
                });

            // ── FINANCIAL BREAKDOWN (Multi-period for Modal) ───────────────────────
            // Web uses Sunday-start week (JS: today.getDate() - today.getDay())
            $nowMNL = now()->timezone('Asia/Manila');
            $dayOfWeek = (int)$nowMNL->format('w'); // 0=Sunday, 1=Monday ... 6=Saturday
            $weekStart = $nowMNL->copy()->subDays($dayOfWeek)->toDateString(); // Go back to Sunday

            $periods = [
                'today'   => [$today, $today],
                'week'    => [$weekStart, $today],      // Sunday-to-today (matches web JS)
                'month'   => [$nowMNL->copy()->startOfMonth()->toDateString(), $today],
                'year'    => [$nowMNL->copy()->startOfYear()->toDateString(), $today],
            ];
            // Add aliases so both 'weekly'/'monthly'/'yearly' (old) and 'week'/'month'/'year' (web-matching) keys work
            $periods['weekly']  = $periods['week'];
            $periods['monthly'] = $periods['month'];
            $periods['yearly']  = $periods['year'];

            $financialBreakdown = [];
            foreach ($periods as $key => [$start, $end]) {
                $rev = (float)(DB::table('boundaries')->whereNull('deleted_at')->whereBetween('date', [$start, $end])->sum('actual_boundary') ?? 0);
                $gx  = (float)(DB::table('expenses')->whereNull('deleted_at')->whereBetween('date', [$start, $end])->sum('amount') ?? 0);
                $sx  = (float)(DB::table('salaries')->whereBetween('pay_date', [$start, $end])->sum('total_salary') ?? 0);
                $mx  = (float)(DB::table('maintenance')->whereNull('deleted_at')->whereBetween('date_started', [$start, $end])->where('status', '!=', 'cancelled')->sum('cost') ?? 0);

                $financialBreakdown[$key] = [
                    'total_revenue'  => $rev,
                    'total_expenses' => $gx + $sx + $mx,
                    // Boundaries: no limit — matches web (fetches all, filters client-side)
                    'boundaries'     => DB::table('boundaries as b')
                                        ->join('units as u', 'b.unit_id', '=', 'u.id')
                                        ->leftJoin('drivers as d', 'b.driver_id', '=', 'd.id')
                                        ->select('b.id', 'b.actual_boundary', 'b.date', 'u.plate_number', DB::raw("CONCAT(COALESCE(d.first_name,''), ' ', COALESCE(d.last_name,'')) as driver_name"))
                                        ->whereNull('b.deleted_at')
                                        ->whereBetween('b.date', [$start, $end])
                                        ->orderByDesc('b.date')
                                        ->get(),
                    // Maintenance: no limit — same structure as web renderExpensesReport
                    'maintenance'    => DB::table('maintenance as m')
                                        ->join('units as u', 'm.unit_id', '=', 'u.id')
                                        ->select('m.id', 'm.maintenance_type as type', 'm.cost', 'm.description', 'm.date_started as date', 'u.plate_number')
                                        ->whereNull('m.deleted_at')
                                        ->whereBetween('m.date_started', [$start, $end])
                                        ->where('m.status', '!=', 'cancelled')
                                        ->orderByDesc('m.date_started')
                                        ->get(),
                    // General expenses: no limit, includes date for web-compatible filtering
                    'general'        => DB::table('expenses')
                                        ->whereNull('deleted_at')
                                        ->whereBetween('date', [$start, $end])
                                        ->select('id', 'category', 'description', 'amount', 'date')
                                        ->orderByDesc('date')
                                        ->get(),
                    'salaries'       => DB::table('salaries')
                                        ->whereBetween('pay_date', [$start, $end])
                                        ->select('id', 'total_salary', 'pay_date')
                                        ->orderByDesc('pay_date')
                                        ->get(),
                ];
            }

            // Executive Insights (Harmonizing with Web hardcoded/static values for parity)
            $topPerformerUnit = !empty($unitPerformance) ? $unitPerformance[0]['plate'] : 'N/A';
            $topPerformerDriver = !empty($topDrivers) ? $topDrivers[0]['name'] : 'N/A';

            return [
                'success' => true,
                'stats' => $stats,
                'chartData' => [
                    'revenueTrend' => $revenueTrend,
                    'expenseBreakdown' => $expenseBreakdown,
                    'unitStatusDist' => $unitStatusDist,
                    'unitPerformance' => $unitPerformance,
                    'weeklyData' => $weeklyData,
                    'topDrivers' => $topDrivers
                ],
                'insights' => [
                    'fleetHealth' => 82, // Hardcoded to match web precisely
                    'healthMessage' => 'Most units are meeting over 80% of their monthly boundary targets.',
                    'topPerformerUnit' => $topPerformerUnit,
                    'topPerformerDriver' => $topPerformerDriver
                ],
                'modalData'  => [
                    'maintenanceList'      => $maintenanceList,
                    'driversList'          => $driversList,
                    'codingList'           => $codingList,
                    'unitsList'            => $unitsList,
                    'boundaryList'         => $boundaryList,
                    'financialBreakdown'   => $financialBreakdown
                ]
            ];
        });

        return response()->json($responseData);
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
