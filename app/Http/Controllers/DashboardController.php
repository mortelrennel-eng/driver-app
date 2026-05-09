<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Models\Unit;
use App\Models\Boundary;
use App\Models\Maintenance;
use App\Models\Expense;
use App\Models\User;
use App\Models\SystemAlert;
use App\Models\FranchiseCase;
use App\Models\DriverBehavior;
use App\Traits\CalculatesDriverPerformance;
use Carbon\Carbon;

class DashboardController extends Controller
{
    use CalculatesDriverPerformance;
    public function index(Request $request)
    {
        // Get dashboard statistics using centralized method
        $stats = $this->getDashboardStats();
        
        // System alerts (unresolved)
        $alerts = DB::table('system_alerts')
            ->where('is_resolved', false)
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        // Revenue trend (dynamic based on period)
        $period = $request->get('period', 30);
        $revenue_trend = $this->getRevenueTrendData($period);

        // Weekly financial trend (last 7 days real data)
        $weekly_data = $this->getWeeklyFinancialData();

        // Unit performance (top performing units)
        $unit_performance = $this->getUnitPerformanceData();

        // Unit status distribution data
        $unit_status_data = $this->getUnitStatusDistributionData();
        $unit_status_distribution_data = $unit_status_data;

        // Expense breakdown
        $expense_breakdown = $this->getExpenseBreakdownData();

        // Top Drivers
        $top_drivers = $this->getTopDriversData();

        return view('dashboard', compact(
            'stats', 'alerts', 'revenue_trend', 'weekly_data', 
            'unit_status_data', 'unit_status_distribution_data', 
            'unit_performance', 'expense_breakdown', 'top_drivers'
        ));
    }

    public function getRealTimeData()
    {
        try {
            // Get dashboard statistics (Skip monitorSystemStatus for AJAX to avoid load and flickering)
            $stats = $this->getDashboardStats(false);
            
            // System alerts
            $alerts = DB::table('system_alerts')
                ->where('is_resolved', false)
                ->orderByDesc('created_at')
                ->limit(10)
                ->get()
                ->map(function($alert) {
                    return [
                        'message' => $alert->message,
                        'severity' => 'medium',
                        'alert_type' => $alert->type ?? 'notice'
                    ];
                });

            // Weekly data
            $weekly_data = $this->getWeeklyFinancialData();

            // Charts data
            $unit_status_data = $this->getUnitStatusDistributionData();
            $revenue_trend = $this->getRevenueTrendData(30);
            $unit_performance = $this->getUnitPerformanceData();
            $expense_breakdown = $this->getExpenseBreakdownData();
            $top_drivers = $this->getTopDriversData();

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
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Dashboard Realtime Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function getRevenueTrend(Request $request)
    {
        $period = (int) $request->get('period', 30);
        $startDate = now()->subDays($period - 1)->toDateString();
        
        // Use a single query with GROUP BY for the entire period
        $revenueData = DB::table('boundaries')
            ->whereNull('deleted_at')
            ->whereDate('date', '>=', $startDate)
            ->select(DB::raw('DATE(date) as revenue_date'), DB::raw('SUM(actual_boundary) as total_revenue'))
            ->groupBy(DB::raw('DATE(date)'))
            ->orderBy('revenue_date', 'asc')
            ->get()
            ->keyBy('revenue_date');

        $revenue_trend = collect(range($period - 1, 0))->map(function ($daysAgo) use ($period, $revenueData) {
            $carbonDate = now()->subDays($daysAgo);
            $dateString = $carbonDate->toDateString();
            
            $boundary = isset($revenueData[$dateString]) ? (float)$revenueData[$dateString]->total_revenue : 0;
            
            // Format label based on period
            $label = $carbonDate->format('M j');
            if ($period > 30) {
                $label = $carbonDate->format('M Y');
            }
            
            return [
                'date' => $label,
                'revenue' => $boundary,
            ];
        })->values()->toArray();

        return response()->json([
            'success' => true,
            'data' => $revenue_trend,
            'period' => $period,
            'total_period_revenue' => array_sum(array_column($revenue_trend, 'revenue'))
        ]);
    }

    public function getUnitsOverview()
    {
        try {
            $todayDay = now()->format('l');
            $todayDate = now()->toDateString();
            $sub30Days = now()->subDays(30)->toDateString();
            $sub10Days = now()->subDays(10)->toDateString();
            $sub7Days = now()->subDays(7)->toDateString();

            // 1. Get units with essential joined data and aggregate subqueries to avoid N+1
            $units = DB::table('units as u')
                ->leftJoin('drivers as d', 'u.driver_id', '=', 'd.id')
                ->whereNull('u.deleted_at')
                ->select([
                    'u.id', 'u.status', 'u.boundary_rate', 'u.purchase_cost', 'u.plate_number', 'u.driver_id',
                    DB::raw("TRIM(CONCAT(COALESCE(d.first_name, ''), ' ', COALESCE(d.last_name, ''))) as driver_full_name"),
                    'd.nickname as driver_nickname',
                    // Total Boundary
                    DB::raw("(SELECT SUM(actual_boundary) FROM boundaries WHERE unit_id = u.id AND deleted_at IS NULL) as total_boundary"),
                    // Today's Boundary
                    DB::raw("(SELECT SUM(actual_boundary) FROM boundaries WHERE unit_id = u.id AND deleted_at IS NULL AND DATE(date) = '$todayDate') as today_boundary"),
                    // Recent Boundary sums for ROI calculation
                    DB::raw("(SELECT SUM(actual_boundary) FROM boundaries WHERE unit_id = u.id AND deleted_at IS NULL AND DATE(date) >= '$sub30Days' AND boundary_amount > 0) as boundary_30d"),
                    DB::raw("(SELECT SUM(actual_boundary) FROM boundaries WHERE unit_id = u.id AND deleted_at IS NULL AND DATE(date) >= '$sub10Days' AND boundary_amount > 0) as boundary_10d"),
                    DB::raw("(SELECT SUM(actual_boundary) FROM boundaries WHERE unit_id = u.id AND deleted_at IS NULL AND DATE(date) >= '$sub7Days' AND boundary_amount > 0) as boundary_7d"),
                    // Active days count
                    DB::raw("(SELECT COUNT(*) FROM boundaries WHERE unit_id = u.id AND deleted_at IS NULL AND boundary_amount > 0) as active_days"),
                    // Maintenance Costs
                    DB::raw("(SELECT SUM(cost) FROM maintenance WHERE unit_id = u.id AND deleted_at IS NULL AND status != 'cancelled') as total_maintenance_cost"),
                    // Coding Costs
                    DB::raw("(SELECT SUM(cost) FROM coding_records WHERE unit_id = u.id AND deleted_at IS NULL) as total_coding_cost"),
                    // Last Activity Date
                    DB::raw("(SELECT MAX(date) FROM boundaries WHERE unit_id = u.id AND deleted_at IS NULL) as last_activity_date")
                ])
                ->orderBy('u.plate_number')
                ->get()
                ->map(function($unit) use ($todayDay) {
                    $displayStatus = strtolower($unit->status);
                    
                    // Automation: Identify if it should be coding based on plate number
                    $plateCodingDay = $this->getCodingDay($unit->plate_number);
                    $shouldBeCodingToday = ($plateCodingDay === $todayDay);

                    if ($shouldBeCodingToday && $displayStatus !== 'missing') {
                        $displayStatus = 'coding';
                    } elseif ($displayStatus === 'coding' && !$shouldBeCodingToday) {
                        $displayStatus = 'active';
                    }
                    
                    $totalBoundary = (float)($unit->total_boundary ?? 0);
                    $totalCosts = (float)($unit->total_maintenance_cost ?? 0) + (float)($unit->total_coding_cost ?? 0);
                    $netRevenue = $totalBoundary - $totalCosts;
                    
                    $roiPercentage = 0;
                    if ($unit->purchase_cost > 0 && $netRevenue > 0) {
                        $roiPercentage = min(100, round(($netRevenue / $unit->purchase_cost) * 100, 2));
                    }
                    
                    // Driver name logic
                    $driverName = $unit->driver_full_name ?: ($unit->driver_nickname ?: 'No Driver');
                    if (!$unit->driver_id) $driverName = 'No Driver';
                    
                    // Days to ROI calculation logic (optimized)
                    $daysToROI = 0;
                    if ($unit->purchase_cost > 0 && $totalBoundary > 0 && $roiPercentage < 100) {
                        $dailyAverage = 0;
                        if ($unit->boundary_7d > 0) $dailyAverage = $unit->boundary_7d / 7;
                        elseif ($unit->boundary_10d > 0) $dailyAverage = $unit->boundary_10d / 10;
                        elseif ($unit->boundary_30d > 0) $dailyAverage = $unit->boundary_30d / 30;
                        elseif ($unit->active_days > 0) $dailyAverage = $totalBoundary / $unit->active_days;

                        if ($dailyAverage > 0) {
                            $remainingAmount = $unit->purchase_cost - $totalBoundary;
                            $daysToROI = ceil($remainingAmount / $dailyAverage);
                            $daysToROI = min($daysToROI, 365);
                            if ($daysToROI <= 5) $daysToROI = 0; // Almost there
                        } else {
                            $daysToROI = 999;
                        }
                    }
                    
                    return [
                        'id' => $unit->id,
                        'plate_number' => $unit->plate_number,
                        'status' => $displayStatus,
                        'boundary_rate' => (float) $unit->boundary_rate,
                        'total_boundary' => $totalBoundary,
                        'today_boundary' => (float)($unit->today_boundary ?? 0),
                        'purchase_cost' => (float) $unit->purchase_cost,
                        'driver_name' => $driverName,
                        'driver_id' => $unit->driver_id,
                        'roi_percentage' => $roiPercentage,
                        'roi_achieved' => $roiPercentage >= 100,
                        'days_to_roi' => $daysToROI,
                        'last_activity' => $unit->last_activity_date,
                        'performance_rating' => $this->getPerformanceRating($roiPercentage)
                    ];
                });

            // Calculate real stats from actual data
            $stats = [
                'total_units' => $units->count(),
                'vacant_units' => $units->whereNull('driver_id')->count(),
                'active_units' => $units->whereNotNull('driver_id')->where('status', '!=', 'missing')->count(),
                'coding_units' => $units->where('status', 'coding')->count(),
                'missing_units' => $units->where('status', 'missing')->count(),
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

            if ($filter === 'complete' || $filter === 'completed') {
                // Query historical completed maintenance records
                $unitsQuery = DB::table('maintenance as m')
                    ->join('units as u', 'm.unit_id', '=', 'u.id')
                    ->whereIn(DB::raw('LOWER(m.status)'), ['completed', 'complete'])
                    ->whereNull('m.deleted_at')
                    ->whereNull('u.deleted_at');
            } else {
                // Base logic: All active maintenance records (Not completed/cancelled)
                $unitsQuery = DB::table('maintenance as m')
                    ->join('units as u', 'm.unit_id', '=', 'u.id')
                    ->whereNotIn(DB::raw('LOWER(m.status)'), ['completed', 'complete', 'cancelled'])
                    ->whereNull('m.deleted_at')
                    ->whereNull('u.deleted_at');

                // Filter by type if specified
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

            // Calculate Global Overview Stats based on MAINTENANCE records, not unit status
            $mStats = DB::table('maintenance')
                ->join('units', 'maintenance.unit_id', '=', 'units.id')
                ->whereNull('maintenance.deleted_at')
                ->whereNull('units.deleted_at')
                ->whereNotIn(DB::raw('LOWER(maintenance.status)'), ['completed', 'complete', 'cancelled'])
                ->select([
                    DB::raw('COUNT(*) as total'),
                    DB::raw('SUM(CASE WHEN LOWER(maintenance.maintenance_type) LIKE "%preventive%" THEN 1 ELSE 0 END) as preventive'),
                    DB::raw('SUM(CASE WHEN LOWER(maintenance.maintenance_type) LIKE "%corrective%" THEN 1 ELSE 0 END) as corrective'),
                    DB::raw('SUM(CASE WHEN LOWER(maintenance.maintenance_type) LIKE "%emergency%" THEN 1 ELSE 0 END) as emergency'),
                ])
                ->first();

            $completedCount = DB::table('maintenance')
                ->join('units', 'maintenance.unit_id', '=', 'units.id')
                ->whereNull('maintenance.deleted_at')
                ->whereNull('units.deleted_at')
                ->whereIn(DB::raw('LOWER(maintenance.status)'), ['completed', 'complete'])
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
                         ->orOn('d.id', '=', 'unit.secondary_driver_id')
                         ->whereNull('unit.deleted_at');
                })
                ->leftJoin('boundaries as b', function($join) {
                    $join->on('unit.id', '=', 'b.unit_id')
                         ->whereNull('b.deleted_at');
                })
                ->select($select)
                ->whereNull('d.deleted_at');

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

                    // Top Performer logic: No accidents, No short boundaries in last 30 days
                    // Note: We use the already aggregated data or small targeted subqueries if needed, 
                    // but for now we simplify to avoid the N+1 loop seen in original code.
                    $isTopPerformer = ($performanceRating === 'excellent');
                    
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
    /**
     * Centralized Dashboard Statistics
     */
    private function getDashboardStats($runMonitor = true)
    {
        // Run automated system monitoring only when requested and rate-limited (every 5 minutes)
        if ($runMonitor) {
            $lastMonitor = session('last_monitor_run');
            if (!$lastMonitor || now()->diffInMinutes(Carbon::parse($lastMonitor)) >= 5) {
                $this->monitorSystemStatus();
                session(['last_monitor_run' => now()->toDateTimeString()]);
            }
        }

        $today = now()->timezone('Asia/Manila')->toDateString();
        $todayDay = now()->timezone('Asia/Manila')->format('l');

        // Cache the entire web dashboard statistics for 60 seconds to prevent database resource/connection exhaustion on shared hosting.
        return Cache::remember('web_dashboard_stats', 60, function() use ($today, $todayDay) {
            $stats = [];

            // 1. Total Units
            $stats['active_units'] = DB::table('units')->whereNull('deleted_at')->count();

            // 2. ROI Achieved
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

            // 3. Coding Units Today
            $allUnits = DB::table('units')->whereNull('deleted_at')->get();
            $stats['coding_units'] = $allUnits->filter(function($unit) use ($todayDay) {
                $codingDay = $unit->coding_day ?: $this->getCodingDay($unit->plate_number);
                return $codingDay === $todayDay;
            })->count();

            // 4. Maintenance Units (Primary Source: Maintenance Table)
            $stats['maintenance_units'] = DB::table('maintenance')
                ->join('units', 'maintenance.unit_id', '=', 'units.id')
                ->whereNull('maintenance.deleted_at')
                ->whereNull('units.deleted_at')
                ->whereNotIn(DB::raw('LOWER(maintenance.status)'), ['complete', 'completed', 'cancelled'])
                ->count();

            // 5. Financials (Today)
            $stats['today_boundary'] = DB::table('boundaries')
                ->whereNull('deleted_at')
                ->whereDate('date', $today)
                ->sum('actual_boundary') ?? 0;

            $genExToday = DB::table('expenses')->whereNull('deleted_at')->whereDate('date', $today)->sum('amount') ?? 0;
            $salExToday = DB::table('salaries')->whereDate('pay_date', $today)->sum('total_salary') ?? 0;
            $mntExToday = DB::table('maintenance')->whereNull('deleted_at')->whereDate('date_started', $today)->where('status', '!=', 'cancelled')->sum('cost') ?? 0;
            
            $stats['total_expenses_today'] = $genExToday + $salExToday + $mntExToday;
            $stats['net_income'] = $stats['today_boundary'] - $stats['total_expenses_today'];

            // 6. Financials (This Month)
            $month = now()->timezone('Asia/Manila')->month;
            $year = now()->timezone('Asia/Manila')->year;

            $stats['month_boundary'] = DB::table('boundaries')
                ->whereNull('deleted_at')
                ->whereMonth('date', $month)
                ->whereYear('date', $year)
                ->sum('actual_boundary') ?? 0;

            $genExMonth = DB::table('expenses')->whereNull('deleted_at')->whereMonth('date', $month)->whereYear('date', $year)->sum('amount') ?? 0;
            $salExMonth = DB::table('salaries')->whereMonth('pay_date', $month)->whereYear('pay_date', $year)->sum('total_salary') ?? 0;
            $mntExMonth = DB::table('maintenance')->whereNull('deleted_at')->whereMonth('date_started', $month)->whereYear('date_started', $year)->where('status', '!=', 'cancelled')->sum('cost') ?? 0;
            
            $stats['total_expenses_month'] = $genExMonth + $salExMonth + $mntExMonth;
            $stats['net_income_month'] = $stats['month_boundary'] - $stats['total_expenses_month'];

            $stats['roi_achieved'] = $stats['roi_units']; // Harmonize for JS

            // 6. Daily Target (Active Units Rate)
            $stats['daily_target'] = DB::table('units')
                ->whereNull('deleted_at')
                ->whereRaw('LOWER(status) = ?', ['active'])
                ->sum('boundary_rate') ?? 0;
            if ($stats['daily_target'] <= 0) $stats['daily_target'] = 2500;

            // 7. Active Drivers
            $stats['active_drivers'] = DB::table('drivers')->whereNull('deleted_at')->count();

            // 8. Missing/Stolen Units
            $stats['missing_units'] = DB::table('units')
                ->whereNull('deleted_at')
                ->where('status', 'missing')
                ->count();

            // 8. Average Boundary
            $stats['avg_boundary'] = DB::table('units')
                ->whereNull('deleted_at')
                ->where('status', 'active')
                ->avg('boundary_rate') ?? 0;

            return $stats;
        });
    }

    /**
     * Automated System Monitoring & Maintenance
     */
    private function monitorSystemStatus()
    {
        $today = now()->timezone('Asia/Manila')->toDateString();
        $todayDay = now()->timezone('Asia/Manila')->format('l');

        // 1. Coding Notice
        $allFleetForCoding = DB::table('units')->whereNull('deleted_at')->get();
        $codingUnitsCount = $allFleetForCoding->filter(function($unit) use ($todayDay) {
            $codingDay = $unit->coding_day ?: $this->getCodingDay($unit->plate_number);
            return $codingDay === $todayDay;
        })->count();

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

        // 2. Auto-resolve missing unit alerts
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

        // 3. Auto-generate Missing Unit Notifications
        $missingUnits = DB::table('units')
            ->leftJoin('drivers', 'units.current_turn_driver_id', '=', 'drivers.id')
            ->whereNull('units.deleted_at')
            ->whereRaw('LOWER(units.status) NOT IN (?, ?, ?)', ['maintenance', 'surveillance', 'retired'])
            ->whereNotNull('units.shift_deadline_at')
            ->where('units.shift_deadline_at', '<', now()->subHours(24))
            ->where(function($q) {
                $q->whereNotNull('units.driver_id')->orWhereNotNull('units.secondary_driver_id');
            })
            ->select('units.id', 'units.plate_number', 'drivers.first_name', 'drivers.last_name', 'units.shift_deadline_at')
            ->get();

        foreach ($missingUnits as $unit) {
            $diffHours = now()->diffInHours(Carbon::parse($unit->shift_deadline_at));
            $diffDays = floor($diffHours / 24);
            $driverName = $unit->first_name ? trim($unit->first_name . ' ' . $unit->last_name) : 'Unknown Driver';
            
            $alertTitle = "🚨 Missing Unit: {$unit->plate_number}";
            $existingAlert = DB::table('system_alerts')->where('type', 'missing_unit')->where('title', $alertTitle)->where('is_resolved', false)->first();
            $msg = "Unit {$unit->plate_number} has not remitted a boundary for {$diffDays} day(s). The last driver on record is {$driverName}.";

            if (!$existingAlert) {
                DB::table('system_alerts')->insert([
                    'type' => 'missing_unit', 'title' => $alertTitle, 'message' => $msg, 'is_resolved' => false, 'created_at' => now(), 'updated_at' => now()
                ]);
            } else {
                DB::table('system_alerts')->where('id', $existingAlert->id)->update(['message' => $msg, 'updated_at' => now()]);
            }

            // 4. Auto-Flagdown (48 Hours)
            if ($diffHours >= 48) {
                $suspectId = DB::table('units')->where('id', $unit->id)->value('current_turn_driver_id');
                if ($suspectId) {
                    $deadline = Carbon::parse($unit->shift_deadline_at);
                    $existingViolation = DB::table('driver_behavior')
                        ->where('driver_id', $suspectId)->where('unit_id', $unit->id)
                        ->where('incident_type', 'missing_unit_overdue')->where('incident_date', $deadline->toDateString())
                        ->exists();

                    if (!$existingViolation) {
                        DB::table('driver_behavior')->insert([
                            'unit_id' => $unit->id, 'driver_id' => $suspectId, 'incident_type' => 'missing_unit_overdue', 'severity' => 'high',
                            'description' => "Auto-logged [Flagdown]: Unit {$unit->plate_number} is overdue for >48 hours.",
                            'latitude' => 0, 'longitude' => 0, 'video_url' => '', 'timestamp' => now(), 'incident_date' => $deadline->toDateString(), 'created_at' => now(),
                        ]);
                    }
                }
            }
        }
    }

    private function getWeeklyFinancialData()
    {
        return collect(range(6, 0))->map(function ($daysAgo) {
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
    }

    private function getRevenueTrendData($period)
    {
        return collect(range($period - 1, 0))->map(function ($daysAgo) {
            $label = now()->subDays($daysAgo)->format('M j');
            $date = now()->subDays($daysAgo)->toDateString();
            $boundary = DB::table('boundaries')->whereNull('deleted_at')->whereDate('date', $date)->sum('actual_boundary') ?? 0;
            return [
                'date' => $label,
                'revenue' => (float) $boundary,
            ];
        })->values()->toArray();
    }

    private function getUnitStatusDistributionData()
    {
        $allUnits = DB::table('units')->whereNull('deleted_at')->get();
        $todayDay = now()->timezone('Asia/Manila')->format('l');

        $codingCount = $allUnits->filter(function($unit) use ($todayDay) {
            $codingDay = $unit->coding_day ?: $this->getCodingDay($unit->plate_number);
            return $codingDay === $todayDay;
        })->count();

        $maintenanceCount = DB::table('maintenance')
            ->whereNull('deleted_at')
            ->whereNotIn(DB::raw('LOWER(status)'), ['complete', 'completed', 'cancelled'])
            ->count();

        $missingCount = $allUnits->filter(fn($u) => strtolower($u->status) === 'missing')->count();
        $retiredCount = $allUnits->filter(fn($u) => strtolower($u->status) === 'retired')->count();
        $totalCount = $allUnits->count();
        $activeCount = max(0, $totalCount - $codingCount - $maintenanceCount - $retiredCount - $missingCount);

        return [
            ['status' => 'Active',            'count' => $activeCount],
            ['status' => 'Under Maintenance', 'count' => $maintenanceCount],
            ['status' => 'Coding',            'count' => $codingCount],
            ['status' => 'Missing / Stolen',  'count' => $missingCount],
            ['status' => 'Retired',           'count' => $retiredCount],
        ];
    }

    private function getUnitPerformanceData()
    {
        return DB::table('units as u')
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
            ->map(function($unit) {
                return [
                    'unit' => $unit->plate_number,
                    'performance' => (float) $unit->total_boundary,
                    'target' => (float) $unit->boundary_rate * 30,
                ];
            });
    }

    private function getExpenseBreakdownData()
    {
        $month = now()->month;
        $year = now()->year;

        // 1. General Expenses from 'expenses' table
        $genExpenses = DB::table('expenses')
            ->whereNull('deleted_at')
            ->select('category', DB::raw('SUM(amount) as total'))
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->groupBy('category')
            ->get();

        // 2. Maintenance Costs
        $mntTotal = DB::table('maintenance')
            ->whereNull('deleted_at')
            ->where('status', '!=', 'cancelled')
            ->whereMonth('date_started', $month)
            ->whereYear('date_started', $year)
            ->sum('cost') ?? 0;

        // 3. Salaries / Payroll
        $salTotal = DB::table('salaries')
            ->whereMonth('pay_date', $month)
            ->whereYear('pay_date', $year)
            ->sum('total_salary') ?? 0;

        $breakdown = $genExpenses->map(fn($e) => ['category' => $e->category, 'amount' => (float) $e->total])->toArray();
        
        if ($mntTotal > 0) {
            $breakdown[] = ['category' => 'Maintenance', 'amount' => (float) $mntTotal];
        }
        if ($salTotal > 0) {
            $breakdown[] = ['category' => 'Payroll/Salaries', 'amount' => (float) $salTotal];
        }

        $data = collect($breakdown)->sortByDesc('amount')->values();

        if ($data->isEmpty() || $data->every(fn($d) => $d['amount'] == 0)) {
            return collect([]);
        }
        return $data;
    }

    private function getTopDriversData()
    {
        $data = DB::table('drivers as d')
            ->whereNull('d.deleted_at')
            ->leftJoin('boundaries as b', function($join) {
                $join->on('d.id', '=', 'b.driver_id')->whereNull('b.deleted_at');
            })
            ->leftJoin('driver_behavior as db', 'd.id', '=', 'db.driver_id')
            ->select(
                DB::raw("CONCAT(COALESCE(d.first_name,''), ' ', COALESCE(d.last_name,'')) as full_name"),
                DB::raw('COUNT(CASE WHEN b.status IN ("paid", "excess", "shortage") THEN 1 END) as good_days'),
                DB::raw('SUM(b.actual_boundary) as total_boundary'),
                DB::raw('COUNT(CASE WHEN ' . $this->getViolationQuerySnippet() . ' THEN 1 END) as violation_count')
            )
            ->whereIn('d.driver_status', ['available', 'assigned'])
            ->groupBy('d.id', 'd.first_name', 'd.last_name')
            ->having('violation_count', '=', 0)
            ->orderByDesc('good_days')
            ->orderByDesc('total_boundary')
            ->limit(5)
            ->get()
            ->map(fn($d) => ['name' => $d->full_name, 'score' => (int) $d->good_days, 'total' => (float) $d->total_boundary]);

        if ($data->isEmpty() || $data->every(fn($d) => $d['score'] == 0)) {
            return collect([]);
        }
        return $data;
    }
}

