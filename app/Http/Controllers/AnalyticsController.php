<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\DecisionSupportService;

class AnalyticsController extends Controller
{
    public function index(Request $request)
    {
        $date_from = $request->input('date_from', date('Y-m-01'));
        $date_to   = $request->input('date_to',   date('Y-m-d'));

        // ── Predictive Forecasting Data ─────────────────────────────────────
        $forecastData = $this->getForecastData();

        $forecast_monthly_history = $forecastData['income_history'];
        $forecast_predicted       = $forecastData['prediction'];
        $forecast_unit_profits    = $forecastData['unit_profitability'];
        $forecast_health          = $forecastData['risk_assessment'];
        $forecast_data_sources    = $forecastData['data_sources'];

        // ── Monthly Revenue (last 6 months) ───────────────────────────────────
        $monthlyRevenueData = array_map(function($item) {
            return [
                'month'    => date('M', strtotime($item['month'] . '-01')),
                'boundary' => (float)$item['boundary'],
                'expenses' => (float)$item['expenses'],
                'net'      => (float)($item['boundary'] - $item['expenses'])
            ];
        }, $forecast_monthly_history);

        // ── Unit Idle Analysis (Revenue Impact) ──────────────────────────────
        $unit_idle_analysis = DB::table('units as u')
            ->whereNull('u.deleted_at')
            ->leftJoin('maintenance as m', function($join) {
                $join->on('u.id', '=', 'm.unit_id')
                    ->whereNull('m.deleted_at')
                    ->where('m.date_started', '>=', now()->subDays(30));
            })
            ->selectRaw('
                u.plate_number as unit,
                COUNT(m.id) as breakdown_count,
                SUM(CASE WHEN m.status = "completed" THEN DATEDIFF(m.date_completed, m.date_started) ELSE 0 END) as idleDays
            ')
            ->groupBy('u.id', 'u.plate_number')
            ->get()
            ->map(function($item) {
                $item = (array)$item;
                $item['reason'] = $item['breakdown_count'] > 0 ? 'Mechanical Breakdown' : 'Awaiting Driver';
                $item['impact'] = $item['idleDays'] * 1200; // Estimated 1200 boundary per day loss
                return $item;
            });

        // ── Driver Performance ─────────────────────────────────────────────────
        $driverPerformance = DB::table('boundaries as b')
            ->whereNull('b.deleted_at')
            ->join('drivers as d', 'b.driver_id', '=', 'd.id')
            ->whereNull('d.deleted_at')
            ->selectRaw("
                CONCAT(COALESCE(d.first_name,''),' ',COALESCE(d.last_name,'')) as full_name,
                COUNT(b.id) as days_worked,
                SUM(b.actual_boundary) as total_collected,
                AVG(b.actual_boundary) as avg_daily,
                SUM(b.excess) - SUM(b.shortage) as net_excess
            ")
            ->whereBetween('b.date', [$date_from, $date_to])
            ->groupBy('d.id', 'full_name')
            ->orderByDesc('avg_daily')
            ->limit(10)
            ->get();

        // ── Expense Trends ─────────────────────────────────────────────────────
        $expenseTrends = DB::table('expenses')
            ->whereNull('deleted_at')
            ->selectRaw('DATE_FORMAT(date, "%Y-%m") as month, SUM(ABS(amount)) as total, COUNT(*) as count')
            ->whereBetween('date', [$date_from, $date_to])
            ->groupBy('month')->orderBy('month')->get();

        // ── Maintenance by Type ────────────────────────────────────────────────
        $maintenanceCosts = DB::table('maintenance')
            ->whereNull('deleted_at')
            ->selectRaw('maintenance_type, SUM(cost) as total_cost, COUNT(*) as count, AVG(cost) as avg_cost')
            ->whereBetween('date_started', [$date_from, $date_to])
            ->groupBy('maintenance_type')->orderByDesc('total_cost')->get();

        // ── Real-time Operational Pulse ──────────────────────────────────────
        $fleetCounts = DB::table('units')->whereNull('deleted_at')
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status');

        $fleet_pulse = [
            'active_units' => $fleetCounts['active'] ?? 0,
            'idle_units'   => $fleetCounts['idle'] ?? 0,
            'maintenance'  => $fleetCounts['maintenance'] ?? 0,
            'surveillance' => $fleetCounts['surveillance'] ?? 0,
        ];

        // ── Financial Health Analysis ────────────────────────────────────────
        $financial_totals = DB::table('boundaries')->whereNull('deleted_at')
            ->whereBetween('date', [$date_from, $date_to])
            ->selectRaw('SUM(actual_boundary) as total_boundary, SUM(shortage) as total_shortage')
            ->first();
        
        $total_boundary = $financial_totals->total_boundary ?? 0;
        $total_shortage = $financial_totals->total_shortage ?? 0;
        
        $total_expenses = DB::table('expenses')->whereNull('deleted_at')
            ->whereBetween('date', [$date_from, $date_to])->sum(DB::raw('ABS(amount)')) ?? 0;
        
        $avg_boundary_rate = DB::table('units')->whereNull('deleted_at')->avg('boundary_rate') ?? 1000;
        $break_even_days   = $avg_boundary_rate > 0 ? ceil($total_expenses / $avg_boundary_rate) : 0;
        
        $net_income = $total_boundary - $total_expenses;
        $revenue_leakage_pct = $total_boundary > 0 ? round(($total_shortage / ($total_boundary + $total_shortage)) * 100, 1) : 0;

        // ── Operational Efficiency ───────────────────────────────────────────
        $active_drivers = DB::table('drivers')->whereNull('deleted_at')
            ->whereIn('driver_status', ['available', 'assigned'])->count();
        
        $total_units = DB::table('units')->whereNull('deleted_at')->count();
        $fleet_utilization = $total_units > 0 ? round(($fleet_pulse['active_units'] / $total_units) * 100, 1) : 0;

        // ── Additional Datasets ─────────────────────────────────────────────
        $top_units = DB::table('units')
            ->whereNull('units.deleted_at')
            ->leftJoin('drivers as d', 'units.driver_id', '=', 'd.id')
            ->leftJoin(DB::raw('(SELECT unit_id, SUM(actual_boundary) as total_collected, COUNT(*) as days_operated FROM boundaries WHERE deleted_at IS NULL GROUP BY unit_id) as bs'), 'bs.unit_id', '=', 'units.id')
            ->select('units.*',
                DB::raw("CONCAT(COALESCE(d.first_name,''), ' ', COALESCE(d.last_name,'')) as driver_name"),
                DB::raw('COALESCE(bs.total_collected, 0) as total_collected'),
                DB::raw('COALESCE(bs.days_operated, 0) as days_operated')
            )
            ->orderByDesc('total_collected')
            ->limit(5)->get();

        $daily_trend = DB::table('boundaries')
            ->whereNull('deleted_at')
            ->selectRaw('DATE(date) as day, SUM(actual_boundary) as total')
            ->whereBetween('date', [$date_from, $date_to])
            ->groupBy('day')->orderBy('day')->get();

        $expense_by_category = DB::table('expenses')
            ->whereNull('deleted_at')
            ->selectRaw('category, SUM(ABS(amount)) as total, COUNT(*) as count')
            ->whereBetween('date', [$date_from, $date_to])
            ->groupBy('category')->orderByDesc('total')->get();

        $maintenance_cost_trend = DB::table('maintenance as m')
            ->whereNull('m.deleted_at')
            ->join('units as u', 'm.unit_id', '=', 'u.id')
            ->selectRaw('
                u.plate_number as unit,
                SUM(m.cost) as cost,
                COUNT(m.id) as frequency
            ')
            ->where('m.date_started', '>=', DB::raw('DATE_SUB(CURDATE(), INTERVAL 90 DAY)'))
            ->groupBy('u.id', 'u.plate_number')
            ->orderByDesc('cost')
            ->get()
            ->map(function($item) {
                $item = (array)$item;
                $item['category'] = $item['cost'] > 50000 ? 'High Risk' : ($item['cost'] > 20000 ? 'Normal' : 'Low');
                return $item;
            });

        // ── Predictive Forecasting Data ─────────────────────────────────────
        // Processed at the top to reuse the data array.

        // ── Advanced Analytics Data ─────────────────────────────────────────
        $heatmap_data        = $this->heatmapData();
        $driver_utilization  = $this->driverUtilizationData();
        $unit_roi            = $this->unitRoiData($date_from, $date_to);

        return view('analytics.index', compact(
            'monthlyRevenueData', 'unit_idle_analysis', 'driverPerformance',
            'expenseTrends', 'maintenanceCosts', 'total_boundary', 'total_expenses',
            'net_income', 'active_drivers', 'top_units', 'daily_trend',
            'expense_by_category', 'date_from', 'date_to', 'maintenance_cost_trend',
            'fleet_pulse', 'total_shortage', 'break_even_days', 'revenue_leakage_pct',
            'fleet_utilization',
            'forecast_monthly_history', 'forecast_predicted', 'forecast_unit_profits',
            'forecast_health', 'forecast_data_sources',
            'heatmap_data', 'driver_utilization', 'unit_roi'
        ));
    }

    /**
     * ══════════════════════════════════════════════════════════════════════════
     *  PREDICTIVE FORECASTING ENGINE
     *  Computes comprehensive forecast data using weighted moving averages,
     *  historical trend analysis, and per-unit profitability modeling.
     * ══════════════════════════════════════════════════════════════════════════
     */
    public function getForecastData(): array
    {
        // ─────────────────────────────────────────────────────────────────────
        // 1. MONTHLY INCOME HISTORY (Last 6 Months — Full Breakdown)
        // ─────────────────────────────────────────────────────────────────────

        // Batch-query all monthly boundaries in one go
        $sixMonthsAgo = date('Y-m-01', strtotime('-5 months'));
        $today        = date('Y-m-t');

        $monthlyBoundaries = DB::table('boundaries')
            ->whereNull('deleted_at')
            ->whereBetween('date', [$sixMonthsAgo, $today])
            ->selectRaw('DATE_FORMAT(date, "%Y-%m") as month, SUM(actual_boundary) as total')
            ->groupByRaw('DATE_FORMAT(date, "%Y-%m")')
            ->get()->pluck('total', 'month');

        $monthlyExpenses = DB::table('expenses')
            ->whereNull('deleted_at')
            ->whereBetween('date', [$sixMonthsAgo, $today])
            ->selectRaw('DATE_FORMAT(date, "%Y-%m") as month, SUM(ABS(amount)) as total')
            ->groupByRaw('DATE_FORMAT(date, "%Y-%m")')
            ->get()->pluck('total', 'month');

        $monthlyMaintenance = DB::table('maintenance')
            ->whereNull('deleted_at')
            ->whereBetween('date_started', [$sixMonthsAgo, $today])
            ->selectRaw('DATE_FORMAT(date_started, "%Y-%m") as month, SUM(cost) as total')
            ->groupByRaw('DATE_FORMAT(date_started, "%Y-%m")')
            ->get()->pluck('total', 'month');

        $monthlySalaries = DB::table('salaries')
            ->whereBetween('pay_date', [$sixMonthsAgo, $today])
            ->selectRaw('DATE_FORMAT(pay_date, "%Y-%m") as month, SUM(total_salary) as total')
            ->groupByRaw('DATE_FORMAT(pay_date, "%Y-%m")')
            ->get()->pluck('total', 'month');

        $income_history = [];
        for ($i = 5; $i >= 0; $i--) {
            $monthKey   = date('Y-m', strtotime("-$i months"));
            $monthLabel = date('M Y', strtotime("-$i months"));

            $boundary    = (float)($monthlyBoundaries[$monthKey] ?? 0);
            $expense     = (float)($monthlyExpenses[$monthKey] ?? 0);
            $maintenance = (float)($monthlyMaintenance[$monthKey] ?? 0);
            $salary      = (float)($monthlySalaries[$monthKey] ?? 0);
            $netIncome   = $boundary - $expense - $maintenance - $salary;

            $income_history[] = [
                'month'          => $monthKey,
                'month_label'    => $monthLabel,
                'boundary'       => round($boundary, 2),
                'expenses'       => round($expense, 2),
                'maintenance'    => round($maintenance, 2),
                'salaries'       => round($salary, 2),
                'net_income'     => round($netIncome, 2),
                'source'         => 'Pinagsama-sama mula sa Boundary, Expenses, Maintenance, at Salaries records ng buwang ito',
            ];
        }

        // ─────────────────────────────────────────────────────────────────────
        // 2. PREDICTED NEXT MONTH INCOME (Weighted Moving Average)
        //    Weights: oldest → newest = 1, 1.5, 2, 2.5, 3, 4
        //    Recent months have heavier influence on the forecast.
        // ─────────────────────────────────────────────────────────────────────
        $weights    = [1, 1.5, 2, 2.5, 3, 4];
        $totalWeight = array_sum($weights);

        $predicted_boundary    = 0;
        $predicted_expenses    = 0;
        $predicted_maintenance = 0;
        $predicted_salaries    = 0;

        foreach ($income_history as $idx => $month) {
            $w = $weights[$idx] ?? 1;
            $predicted_boundary    += $month['boundary']    * $w;
            $predicted_expenses    += $month['expenses']    * $w;
            $predicted_maintenance += $month['maintenance'] * $w;
            $predicted_salaries    += $month['salaries']    * $w;
        }

        $predicted_boundary    = round($predicted_boundary    / $totalWeight, 2);
        $predicted_expenses    = round($predicted_expenses    / $totalWeight, 2);
        $predicted_maintenance = round($predicted_maintenance / $totalWeight, 2);
        $predicted_salaries    = round($predicted_salaries    / $totalWeight, 2);
        $predicted_net_income  = round($predicted_boundary - $predicted_expenses - $predicted_maintenance - $predicted_salaries, 2);

        // Growth trend: compare last month net to the average of earlier months
        $lastMonthNet = $income_history[5]['net_income'] ?? 0;
        $prevAvgNet   = 0;
        $prevCount    = 0;
        for ($i = 0; $i < 5; $i++) {
            if (($income_history[$i]['boundary'] ?? 0) > 0) {
                $prevAvgNet += $income_history[$i]['net_income'];
                $prevCount++;
            }
        }
        $prevAvgNet = $prevCount > 0 ? $prevAvgNet / $prevCount : 0;
        $growth_trend_pct = $prevAvgNet != 0
            ? round((($lastMonthNet - $prevAvgNet) / abs($prevAvgNet)) * 100, 1)
            : 0;

        $prediction = [
            'next_month_label'      => date('M Y', strtotime('+1 month')),
            'predicted_boundary'    => $predicted_boundary,
            'predicted_expenses'    => $predicted_expenses,
            'predicted_maintenance' => $predicted_maintenance,
            'predicted_salaries'    => $predicted_salaries,
            'predicted_net_income'  => $predicted_net_income,
            'net_income'            => $predicted_net_income, // Added for blade template compatibility
            'boundary'              => $predicted_boundary,
            'expenses'              => $predicted_expenses,
            'maintenance'           => $predicted_maintenance,
            'salaries'              => $predicted_salaries,
            'growth_trend_pct'      => $growth_trend_pct,
            'best_case_net'         => round($predicted_net_income * 1.15, 2),
            'worst_case_net'        => round($predicted_net_income * 0.85, 2),
            'confidence'            => count($income_history) >= 6 ? 'Mataas' : 'Katamtaman',
            'best_case' => [
                'net_income' => round($predicted_net_income * 1.15, 2),
                'boundary'   => round($predicted_boundary * 1.15, 2),
                'label'      => 'Pinakamataas na Posible',
                'source'     => 'Kung lahat ng unit ay aktibo at walang shortage, ito ang pinakamagandang senaryo',
            ],
            'worst_case' => [
                'net_income' => round($predicted_net_income * 0.85, 2),
                'boundary'   => round($predicted_boundary * 0.85, 2),
                'label'      => 'Pinakamababang Posible',
                'source'     => 'Kung may mga unit na idle o maraming shortage, ito ang pinaka-mababang inaasahan',
            ],
            'source' => 'Kinalkula gamit ang Weighted Moving Average ng huling 6 na buwan — mas mabigat ang timbang ng mga kamakailang buwan',
        ];

        // ─────────────────────────────────────────────────────────────────────
        // 3. PER-UNIT PROFITABILITY FORECAST (Top 10 Active Units)
        // ─────────────────────────────────────────────────────────────────────
        $ninetyDaysAgo = date('Y-m-d', strtotime('-90 days'));

        $unit_profitability = DB::table('units as u')
            ->whereNull('u.deleted_at')
            ->where('u.status', 'active')
            ->leftJoin(DB::raw("(
                SELECT unit_id,
                       AVG(actual_boundary) as avg_daily_boundary,
                       COUNT(DISTINCT date) as operating_days
                FROM boundaries
                WHERE deleted_at IS NULL AND date >= '{$ninetyDaysAgo}'
                GROUP BY unit_id
            ) as b"), 'b.unit_id', '=', 'u.id')
            ->leftJoin(DB::raw("(
                SELECT unit_id,
                       SUM(cost) as total_maint_cost,
                       COUNT(DISTINCT DATE(date_started)) as maint_days
                FROM maintenance
                WHERE deleted_at IS NULL AND date_started >= '{$ninetyDaysAgo}'
                GROUP BY unit_id
            ) as m"), 'm.unit_id', '=', 'u.id')
            ->selectRaw('
                u.id,
                u.plate_number,
                u.boundary_rate,
                COALESCE(b.avg_daily_boundary, 0) as avg_daily_boundary,
                COALESCE(b.operating_days, 0) as operating_days,
                COALESCE(m.total_maint_cost, 0) as total_maint_cost,
                COALESCE(m.maint_days, 0) as maint_days
            ')
            ->orderByDesc('avg_daily_boundary')
            ->limit(10)
            ->get()
            ->map(function ($unit) {
                $avgDailyBoundary = (float)$unit->avg_daily_boundary;
                $operatingDays    = (int)$unit->operating_days;

                // Average daily maintenance cost over 90 days
                $avgDailyMaint = $operatingDays > 0
                    ? round((float)$unit->total_maint_cost / 90, 2)
                    : 0;

                $netDailyProfit     = round($avgDailyBoundary - $avgDailyMaint, 2);
                $predictedMonthly   = round($netDailyProfit * 30, 2);

                return [
                    'plate'               => $unit->plate_number,
                    'boundary_rate'       => (float)$unit->boundary_rate,
                    'avg_daily_boundary'  => round($avgDailyBoundary, 2),
                    'avg_daily_maint'     => $avgDailyMaint,
                    'daily_profit'        => $netDailyProfit,
                    'monthly_profit'      => $predictedMonthly,
                    'operating_days_90d'  => $operatingDays,
                    'source'              => 'Kinukuha mula sa Boundary collections at Maintenance costs ng huling 90 araw para sa unit na ito',
                ];
            })
            ->values()
            ->toArray();

        // ─────────────────────────────────────────────────────────────────────
        // 4. RISK ASSESSMENT
        // ─────────────────────────────────────────────────────────────────────

        // 4a. Revenue Consistency Score (Std Deviation of daily collections, last 60 days)
        $dailyCollections = DB::table('boundaries')
            ->whereNull('deleted_at')
            ->where('date', '>=', date('Y-m-d', strtotime('-60 days')))
            ->selectRaw('DATE(date) as day, SUM(actual_boundary) as daily_total')
            ->groupBy('day')
            ->pluck('daily_total')
            ->map(fn($v) => (float)$v)
            ->values()
            ->toArray();

        $revMean   = count($dailyCollections) > 0 ? array_sum($dailyCollections) / count($dailyCollections) : 0;
        $revStdDev = 0;
        if (count($dailyCollections) > 1) {
            $sumSquares = 0;
            foreach ($dailyCollections as $val) {
                $sumSquares += ($val - $revMean) ** 2;
            }
            $revStdDev = sqrt($sumSquares / count($dailyCollections));
        }
        // Consistency score: 100 = perfectly consistent, lower = more volatile
        // Uses Coefficient of Variation inverted: 100 - (CV * 100), clamped 0-100
        $cv = $revMean > 0 ? ($revStdDev / $revMean) : 1;
        $revenue_consistency_score = max(0, min(100, round(100 - ($cv * 100), 1)));

        // 4b. Expense Trend Direction (compare first 3 vs last 3 months)
        $firstHalfExpense = 0;
        $lastHalfExpense  = 0;
        for ($i = 0; $i < 3; $i++) {
            $firstHalfExpense += $income_history[$i]['expenses'] ?? 0;
        }
        for ($i = 3; $i < 6; $i++) {
            $lastHalfExpense += $income_history[$i]['expenses'] ?? 0;
        }

        if ($firstHalfExpense > 0) {
            $expenseChangePct = round((($lastHalfExpense - $firstHalfExpense) / $firstHalfExpense) * 100, 1);
        } else {
            $expenseChangePct = $lastHalfExpense > 0 ? 100 : 0;
        }

        if ($expenseChangePct > 10) {
            $expense_trend_direction = 'pataas';        // increasing
            $expense_trend_icon      = '📈';
        } elseif ($expenseChangePct < -10) {
            $expense_trend_direction = 'pababa';        // decreasing
            $expense_trend_icon      = '📉';
        } else {
            $expense_trend_direction = 'matatag';       // stable
            $expense_trend_icon      = '➡️';
        }

        // 4c. Maintenance Cost Trend (same approach)
        $firstHalfMaint = 0;
        $lastHalfMaint  = 0;
        for ($i = 0; $i < 3; $i++) {
            $firstHalfMaint += $income_history[$i]['maintenance'] ?? 0;
        }
        for ($i = 3; $i < 6; $i++) {
            $lastHalfMaint += $income_history[$i]['maintenance'] ?? 0;
        }

        if ($firstHalfMaint > 0) {
            $maintChangePct = round((($lastHalfMaint - $firstHalfMaint) / $firstHalfMaint) * 100, 1);
        } else {
            $maintChangePct = $lastHalfMaint > 0 ? 100 : 0;
        }

        if ($maintChangePct > 10) {
            $maint_trend_direction = 'pataas';
            $maint_trend_icon      = '📈';
        } elseif ($maintChangePct < -10) {
            $maint_trend_direction = 'pababa';
            $maint_trend_icon      = '📉';
        } else {
            $maint_trend_direction = 'matatag';
            $maint_trend_icon      = '➡️';
        }

        // 4d. Overall Financial Health Score (0-100)
        //  - Revenue consistency    (weight 30)
        //  - Positive net income    (weight 30)
        //  - Low expense growth     (weight 20)
        //  - Low maintenance growth (weight 20)
        $netPositiveScore = $predicted_net_income > 0
            ? min(30, round(($predicted_net_income / max($predicted_boundary, 1)) * 100, 1))
            : max(0, 30 + round($predicted_net_income / max($predicted_boundary, 1) * 30, 1));

        $expenseGrowthScore = max(0, 20 - abs($expenseChangePct) * 0.2);
        $maintGrowthScore   = max(0, 20 - abs($maintChangePct) * 0.2);
        $consistencyComponent = ($revenue_consistency_score / 100) * 30;

        $financial_health_score = max(0, min(100, round(
            $consistencyComponent + $netPositiveScore + $expenseGrowthScore + $maintGrowthScore
        )));

        // Health label
        if ($financial_health_score >= 80) {
            $health_label = 'Maganda';       // Good
            $health_color = 'emerald';
        } elseif ($financial_health_score >= 60) {
            $health_label = 'Katamtaman';    // Moderate
            $health_color = 'amber';
        } elseif ($financial_health_score >= 40) {
            $health_label = 'Kailangan Bantayan'; // Needs Watching
            $health_color = 'orange';
        } else {
            $health_label = 'Delikado';      // Critical
            $health_color = 'rose';
        }

        $risk_assessment = [
            'score'               => $financial_health_score,
            'revenue_consistency' => $revenue_consistency_score >= 70 ? 'Stable' : ($revenue_consistency_score >= 40 ? 'Medyo Pabago-bago' : 'Volatile'),
            'expense_trend'       => $expense_trend_direction == 'pataas' ? 'Pataas' : ($expense_trend_direction == 'pababa' ? 'Pababa' : 'Stable'),
            'maintenance_trend'   => $maint_trend_direction == 'pataas' ? 'Pataas' : ($maint_trend_direction == 'pababa' ? 'Pababa' : 'Stable'),
        ];

        // ─────────────────────────────────────────────────────────────────────
        // 5. DATA SOURCE TRANSPARENCY
        // ─────────────────────────────────────────────────────────────────────
        $data_sources = [
            'boundary_collections' => [
                'table'       => 'boundaries',
                'column'      => 'actual_boundary',
                'description' => 'Kinukuha mula sa Boundary Management — Actual na nakolekta sa bawat araw',
                'icon'        => '💰',
            ],
            'expenses' => [
                'table'       => 'expenses',
                'column'      => 'amount',
                'description' => 'Kinukuha mula sa Expense Tracker — Lahat ng gastos ng kumpanya tulad ng gas, opisina, at iba pa',
                'icon'        => '🧾',
            ],
            'maintenance_costs' => [
                'table'       => 'maintenance',
                'column'      => 'cost',
                'description' => 'Kinukuha mula sa Maintenance Records — Gastos sa pagpapaayos at spare parts ng bawat unit',
                'icon'        => '🔧',
            ],
            'salaries' => [
                'table'       => 'salaries',
                'column'      => 'total_salary',
                'description' => 'Kinukuha mula sa Payroll — Kabuuang sweldo ng mga driver at staff sa bawat buwan',
                'icon'        => '👤',
            ],
            'unit_data' => [
                'table'       => 'units',
                'column'      => 'status, boundary_rate',
                'description' => 'Kinukuha mula sa Fleet Management — Status, boundary rate, at detalye ng bawat taxi unit',
                'icon'        => '🚕',
            ],
            'driver_data' => [
                'table'       => 'drivers',
                'column'      => 'driver_status',
                'description' => 'Kinukuha mula sa Driver Roster — Listahan at status ng lahat ng driver sa sistema',
                'icon'        => '🪪',
            ],
        ];

        return [
            'income_history'     => $income_history,
            'prediction'         => $prediction,
            'unit_profitability' => $unit_profitability,
            'risk_assessment'    => $risk_assessment,
            'data_sources'       => $data_sources,
        ];
    }

    /**
     * AJAX endpoint — returns AI-powered strategic insights as JSON.
     */
    public function aiInsights(Request $request)
    {
        $forceRefresh = $request->boolean('refresh', false);
        $service = new DecisionSupportService();
        $result  = $service->getAiInsights($forceRefresh);

        return response()->json($result);
    }

    /**
     * Revenue Heatmap Data (last 365 days daily collections)
     */
    public function heatmapData(): array
    {
        $startDate = date('Y-m-d', strtotime('-364 days'));
        $today     = date('Y-m-d');

        $daily = DB::table('boundaries')
            ->whereNull('deleted_at')
            ->whereBetween('date', [$startDate, $today])
            ->selectRaw('DATE(date) as day, SUM(actual_boundary) as total')
            ->groupBy('day')
            ->pluck('total', 'day');

        // Build full 365-day array
        $result = [];
        for ($i = 364; $i >= 0; $i--) {
            $d = date('Y-m-d', strtotime("-$i days"));
            $result[] = [
                'date'  => $d,
                'total' => (float)($daily[$d] ?? 0),
                'week'  => date('W', strtotime($d)),
                'month' => date('M', strtotime($d)),
                'dow'   => (int)date('N', strtotime($d)), // 1=Mon, 7=Sun
            ];
        }
        return $result;
    }

    /**
     * Driver Utilization Data (last 30 days)
     */
    public function driverUtilizationData(): array
    {
        $startDate   = date('Y-m-d', strtotime('-29 days'));
        $today       = date('Y-m-d');
        $workingDays = 30;

        return DB::table('drivers as d')
            ->whereNull('d.deleted_at')
            ->leftJoin(DB::raw("(
                SELECT driver_id, COUNT(DISTINCT date) as days_worked, SUM(actual_boundary) as total_collected
                FROM boundaries
                WHERE deleted_at IS NULL AND date BETWEEN '$startDate' AND '$today'
                GROUP BY driver_id
            ) as b"), 'b.driver_id', '=', 'd.id')
            ->select(
                'd.id',
                DB::raw("CONCAT(COALESCE(d.first_name,''), ' ', COALESCE(d.last_name,'')) as name"),
                DB::raw('COALESCE(b.days_worked, 0) as days_worked'),
                DB::raw('COALESCE(b.total_collected, 0) as total_collected')
            )
            ->where('d.driver_status', '!=', 'terminated')
            ->orderByDesc('days_worked')
            ->limit(15)
            ->get()
            ->map(function ($d) use ($workingDays) {
                $daysWorked    = (int)$d->days_worked;
                $utilization   = min(100, round(($daysWorked / $workingDays) * 100, 1));
                $category      = $utilization >= 80 ? 'high' : ($utilization >= 50 ? 'medium' : 'low');
                $categoryLabel = $utilization >= 80 ? 'Active' : ($utilization >= 50 ? 'Moderate' : 'Low Activity');
                return [
                    'name'           => trim($d->name),
                    'days_worked'    => $daysWorked,
                    'days_idle'      => max(0, $workingDays - $daysWorked),
                    'utilization'    => $utilization,
                    'total_collected'=> (float)$d->total_collected,
                    'category'       => $category,
                    'category_label' => $categoryLabel,
                ];
            })
            ->toArray();
    }

    /**
     * Unit ROI Scorecard (all-time or filtered)
     */
    public function unitRoiData(string $dateFrom, string $dateTo): array
    {
        return DB::table('units as u')
            ->whereNull('u.deleted_at')
            ->leftJoin(DB::raw("(
                SELECT unit_id,
                       SUM(actual_boundary) as total_revenue,
                       COUNT(DISTINCT date) as operating_days
                FROM boundaries
                WHERE deleted_at IS NULL
                GROUP BY unit_id
            ) as b"), 'b.unit_id', '=', 'u.id')
            ->leftJoin(DB::raw("(
                SELECT unit_id, SUM(cost) as total_maintenance
                FROM maintenance
                WHERE deleted_at IS NULL
                GROUP BY unit_id
            ) as m"), 'm.unit_id', '=', 'u.id')
            ->select(
                'u.plate_number',
                'u.status',
                'u.boundary_rate',
                DB::raw('COALESCE(b.total_revenue, 0) as total_revenue'),
                DB::raw('COALESCE(b.operating_days, 0) as operating_days'),
                DB::raw('COALESCE(m.total_maintenance, 0) as total_maintenance')
            )
            ->orderByDesc('total_revenue')
            ->get()
            ->map(function ($u) {
                $revenue     = (float)$u->total_revenue;
                $maintenance = (float)$u->total_maintenance;
                $netRoi      = $revenue - $maintenance;
                $roiPct      = $maintenance > 0 ? round(($netRoi / $maintenance) * 100, 1) : ($revenue > 0 ? 100 : 0);
                $rating      = $roiPct >= 200 ? 'Excellent' : ($roiPct >= 100 ? 'Good' : ($roiPct >= 50 ? 'Fair' : 'Poor'));
                $ratingColor = $roiPct >= 200 ? 'emerald' : ($roiPct >= 100 ? 'blue' : ($roiPct >= 50 ? 'amber' : 'rose'));
                return [
                    'plate'          => $u->plate_number,
                    'status'         => $u->status,
                    'revenue'        => $revenue,
                    'maintenance'    => $maintenance,
                    'net_roi'        => $netRoi,
                    'roi_pct'        => $roiPct,
                    'rating'         => $rating,
                    'rating_color'   => $ratingColor,
                    'operating_days' => (int)$u->operating_days,
                ];
            })
            ->toArray();
    }

    /**
     * AJAX endpoint for heatmap data.
     */
    public function heatmap()
    {
        return response()->json($this->heatmapData());
    }

    /**
     * Export analytics as CSV.
     */
    public function exportCsv(Request $request)
    {
        $dateFrom = $request->input('date_from', date('Y-m-01'));
        $dateTo   = $request->input('date_to',   date('Y-m-d'));
        $type     = $request->input('type', 'revenue'); // revenue|drivers|units|maintenance

        $filename = "eurotaxi_analytics_{$type}_" . date('Y-m-d') . '.csv';
        $headers  = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($type, $dateFrom, $dateTo) {
            $fp = fopen('php://output', 'w');

            if ($type === 'revenue') {
                fputcsv($fp, ['Date', 'Total Boundary (PHP)', 'Running Total']);
                $rows = DB::table('boundaries')->whereNull('deleted_at')
                    ->whereBetween('date', [$dateFrom, $dateTo])
                    ->selectRaw('DATE(date) as day, SUM(actual_boundary) as total')
                    ->groupBy('day')->orderBy('day')->get();
                $running = 0;
                foreach ($rows as $r) {
                    $running += $r->total;
                    fputcsv($fp, [$r->day, number_format($r->total, 2), number_format($running, 2)]);
                }
            } elseif ($type === 'drivers') {
                fputcsv($fp, ['Driver Name', 'Days Worked', 'Total Collected (PHP)', 'Avg Daily (PHP)', 'Net Excess (PHP)']);
                $rows = DB::table('boundaries as b')->whereNull('b.deleted_at')
                    ->join('drivers as d', 'b.driver_id', '=', 'd.id')->whereNull('d.deleted_at')
                    ->whereBetween('b.date', [$dateFrom, $dateTo])
                    ->selectRaw("CONCAT(COALESCE(d.first_name,''),' ',COALESCE(d.last_name,'')) as name, COUNT(b.id) as days, SUM(b.actual_boundary) as total, AVG(b.actual_boundary) as avg_daily, SUM(b.excess)-SUM(b.shortage) as net_excess")
                    ->groupBy('d.id')->orderByDesc('total')->get();
                foreach ($rows as $r) {
                    fputcsv($fp, [$r->name, $r->days, number_format($r->total, 2), number_format($r->avg_daily, 2), number_format($r->net_excess, 2)]);
                }
            } elseif ($type === 'units') {
                fputcsv($fp, ['Plate Number', 'Status', 'Total Revenue (PHP)', 'Total Maintenance (PHP)', 'Net ROI (PHP)', 'ROI %', 'Rating']);
                $rows = $this->unitRoiData($dateFrom, $dateTo);
                foreach ($rows as $r) {
                    fputcsv($fp, [$r['plate'], $r['status'], number_format($r['revenue'], 2), number_format($r['maintenance'], 2), number_format($r['net_roi'], 2), $r['roi_pct'] . '%', $r['rating']]);
                }
            } elseif ($type === 'maintenance') {
                fputcsv($fp, ['Unit', 'Maintenance Type', 'Cost (PHP)', 'Date Started', 'Status']);
                $rows = DB::table('maintenance as m')->whereNull('m.deleted_at')
                    ->join('units as u', 'm.unit_id', '=', 'u.id')
                    ->whereBetween('m.date_started', [$dateFrom, $dateTo])
                    ->select('u.plate_number', 'm.maintenance_type', 'm.cost', 'm.date_started', 'm.status')
                    ->orderBy('m.date_started', 'desc')->get();
                foreach ($rows as $r) {
                    fputcsv($fp, [$r->plate_number, $r->maintenance_type, number_format($r->cost, 2), $r->date_started, $r->status]);
                }
            }

            fclose($fp);
        };

        return response()->stream($callback, 200, $headers);
    }
}
