<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UnitProfitabilityController extends Controller
{
    public function index(Request $request)
    {
        // Get date range filter
        $date_from = $request->input('date_from', date('Y-m-01'));
        $date_to = $request->input('date_to', date('Y-m-t'));
        $unit_filter = $request->input('unit', '');

        // Build WHERE conditions
        $where_conditions = [];
        $params = [];
        $types = "";

        if (!empty($unit_filter)) {
            $where_conditions[] = "u.plate_number = ?";
            $params[] = $unit_filter;
            $types .= "s";
        }
        $where_conditions[] = "u.deleted_at IS NULL";

        $where_clause = "WHERE " . implode(' AND ', $where_conditions);

        // Get all units for dropdown
        $units_dropdown = DB::table('units')->whereNull('deleted_at')->orderBy('plate_number')->get();

        // Get unit profitability data
        $sql = "SELECT 
                u.id,
                u.plate_number,
                COALESCE(u.make, 'Unknown') as make,
                COALESCE(u.model, 'Unknown') as model,
                COALESCE(u.year, 0) as year,
                COALESCE(u.purchase_cost, 0) as purchase_cost,
                COALESCE(u.boundary_rate, 0) as boundary_rate,
                COALESCE(b.total_boundary, 0) as total_boundary,
                COALESCE(b.total_target_boundary, 0) as total_target_boundary,
                COALESCE(b.boundary_days, 0) as boundary_days,
                COALESCE(m.total_maintenance, 0) as total_maintenance,
                COALESCE(m.maintenance_days, 0) as maintenance_days,
                COALESCE(e.total_expenses, 0) as total_expenses,
                COALESCE(e.expense_days, 0) as expense_days
            FROM units u
            LEFT JOIN (
                SELECT unit_id,
                       SUM(actual_boundary) as total_boundary,
                       SUM(boundary_amount) as total_target_boundary,
                       COUNT(DISTINCT id) as boundary_days
                FROM boundaries
                WHERE deleted_at IS NULL AND date BETWEEN ? AND ?
                GROUP BY unit_id
            ) b ON u.id = b.unit_id
            LEFT JOIN (
                SELECT unit_id,
                       SUM(cost) as total_maintenance,
                       COUNT(DISTINCT id) as maintenance_days
                FROM maintenance
                WHERE deleted_at IS NULL AND date_started BETWEEN ? AND ?
                GROUP BY unit_id
            ) m ON u.id = m.unit_id
            LEFT JOIN (
                SELECT unit_id,
                       SUM(ABS(amount)) as total_expenses,
                       COUNT(DISTINCT id) as expense_days
                FROM expenses
                WHERE deleted_at IS NULL AND date BETWEEN ? AND ?
                GROUP BY unit_id
            ) e ON u.id = e.unit_id
            $where_clause
            ORDER BY u.plate_number";

        // Build parameters array
        $all_params = array_merge(
            [$date_from, $date_to], // boundaries
            [$date_from, $date_to], // maintenance
            [$date_from, $date_to], // expenses
            $params
        );

        $profitability = DB::select($sql, $all_params);

        // Calculate additional metrics
        foreach ($profitability as &$unit) {
            $unit->net_income = $unit->total_boundary - $unit->total_maintenance - $unit->total_expenses;
            $unit->profit_margin = $unit->total_boundary > 0 ? (($unit->net_income / $unit->total_boundary) * 100) : 0;
            $unit->maintenance_cost = $unit->total_maintenance;
            $unit->other_expenses = $unit->total_expenses;
            $unit->roi_percentage = $unit->purchase_cost > 0 ? (($unit->net_income / $unit->purchase_cost) * 100) : 0;
            $unit->payback_period = $unit->total_boundary > 0 ? ($unit->purchase_cost / $unit->total_boundary) : 0;
            $unit->roi_achieved = $unit->purchase_cost > 0 && $unit->net_income >= $unit->purchase_cost ? 1 : 0;
        }

        // Calculate totals / overview
        $overview = [
            'total_boundary' => array_sum(array_column($profitability, 'total_boundary')),
            'total_maintenance' => array_sum(array_column($profitability, 'total_maintenance')),
            'total_expenses' => array_sum(array_column($profitability, 'total_expenses')),
            'net_income' => array_sum(array_column($profitability, 'net_income')),
            'total_units' => count($profitability),
            'avg_margin' => count($profitability) > 0 ? array_sum(array_column($profitability, 'profit_margin')) / count($profitability) : 0,
            'roi_units' => count(array_filter($profitability, function($u) { return $u->roi_achieved; })),
        ];

        $units = $units_dropdown;
        $selected_unit = $unit_filter;
        $full_profitability = $profitability; // Keep original for summary sections

        // Calculate top 10 forecasting unit profitability
        $ninetyDaysAgo = date('Y-m-d', strtotime('-90 days'));
        $forecast_unit_profits = DB::table('units as u')
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
                ];
            })
            ->values()
            ->toArray();

        // Manual Pagination (10 per page)
        $currentPage = \Illuminate\Pagination\Paginator::resolveCurrentPage() ?: 1;
        $perPage = 10;
        $currentItems = array_slice($profitability, ($currentPage - 1) * $perPage, $perPage);
        $profitability = new \Illuminate\Pagination\LengthAwarePaginator(
            $currentItems, 
            count($profitability), 
            $perPage, 
            $currentPage, 
            ['path' => \Illuminate\Pagination\Paginator::resolveCurrentPath(), 'query' => $request->query()]
        );

        return view('unit-profitability.index', compact('profitability', 'full_profitability', 'units', 'overview', 'date_from', 'date_to', 'selected_unit', 'forecast_unit_profits'));
    }

    public function getDetails(Request $request)
    {
        $unit_id = $request->unit_id;
        $date_from = $request->date_from ?? date('Y-m-01');
        $date_to = $request->date_to ?? date('Y-m-d');

        $unit = \DB::table('units')->where('id', $unit_id)->first();
        if (!$unit) {
            return response()->json(['error' => 'Unit not found'], 404);
        }

        // Fetch Boundaries (Revenue) - uses 'date' column
        $boundaries = \DB::table('boundaries')
            ->where('unit_id', $unit_id)
            ->whereNull('deleted_at')
            ->whereBetween('date', [$date_from, $date_to])
            ->orderBy('date', 'desc')
            ->get();

        // Fetch Maintenances (Expenses) - table is 'maintenance', column is 'date_started'
        $maintenances = \DB::table('maintenance')
            ->where('unit_id', $unit_id)
            ->whereNull('deleted_at')
            ->whereBetween('date_started', [$date_from, $date_to])
            ->orderBy('date_started', 'desc')
            ->get();

        // Fetch Other Expenses - table is 'expenses', column is 'date'
        $expenses = \DB::table('expenses')
            ->where('unit_id', $unit_id)
            ->whereNull('deleted_at')
            ->whereBetween('date', [$date_from, $date_to])
            ->orderBy('date', 'desc')
            ->get();

        return response()->json([
            'unit' => $unit,
            'boundaries' => $boundaries,
            'maintenances' => $maintenances,
            'expenses' => $expenses,
        ]);
    }

    public function generateAiDss(Request $request)
    {
        $date_from = $request->input('date_from', date('Y-m-01'));
        $date_to = $request->input('date_to', date('Y-m-t'));

        // Gather critical data for AI
        $boundariesSub = DB::table('boundaries')
            ->whereNull('deleted_at')
            ->whereBetween('date', [$date_from, $date_to])
            ->select('unit_id', DB::raw('SUM(actual_boundary) as total_revenue'), DB::raw('COUNT(DISTINCT id) as active_days'))
            ->groupBy('unit_id');

        $maintSub = DB::table('maintenance')
            ->whereNull('deleted_at')
            ->whereBetween('date_started', [$date_from, $date_to])
            ->select('unit_id', DB::raw('SUM(cost) as total_maintenance'))
            ->groupBy('unit_id');

        $stats = DB::table('units as u')
            ->leftJoinSub($boundariesSub, 'b', 'u.id', '=', 'b.unit_id')
            ->leftJoinSub($maintSub, 'm', 'u.id', '=', 'm.unit_id')
            ->select(
                'u.plate_number',
                'u.make',
                'u.model',
                'u.purchase_cost',
                DB::raw('COALESCE(b.total_revenue, 0) as total_revenue'),
                DB::raw('COALESCE(m.total_maintenance, 0) as total_maintenance'),
                DB::raw('COALESCE(b.active_days, 0) as active_days')
            )
            ->whereNull('u.deleted_at')
            ->get();

        $totalUnits = $stats->count();
        $totalRevenue = $stats->sum('total_revenue');
        $totalMaint = $stats->sum('total_maintenance');
        
        if ($totalUnits === 0) {
            return response()->json(['success' => false, 'message' => 'No unit data available for the selected period.']);
        }

        $topPerformer = $stats->sortByDesc('total_revenue')->first();
        $worstPerformer = $stats->sortBy('total_revenue')->first();

        $topPlate = $topPerformer->plate_number ?? 'N/A';
        $topRev = $topPerformer->total_revenue ?? 0;
        $worstPlate = $worstPerformer->plate_number ?? 'N/A';
        $worstRev = $worstPerformer->total_revenue ?? 0;

        $prompt = "As a Taxi Fleet Financial Analyst AI, analyze this profitability data for $totalUnits units from $date_from to $date_to:
        - Total Revenue: ₱" . number_format($totalRevenue, 2) . "
        - Total Maintenance Cost: ₱" . number_format($totalMaint, 2) . "
        - Top Performer: $topPlate (₱" . number_format($topRev, 2) . ")
        - Lowest Revenue: $worstPlate (₱" . number_format($worstRev, 2) . ")
        
        Provide a strategic Decision Support (DSS) report including:
        1. Financial Health Score (1-100).
        2. Top 3 Revenue Leakage risks identified.
        3. Strategic recommendations for fleet maintenance vs replacement.
        4. ROI projection based on current performance.
        
        Keep it professional, data-driven, and highly actionable. Format in HTML-ready markdown.";

        try {
            $aiResponse = \App\Services\GeminiService::generate($prompt);
            return response()->json(['success' => true, 'analysis' => $aiResponse]);
        } catch (\Exception $e) {
            \Log::error('AI Analysis Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'AI Service currently unavailable.'], 500);
        }
    }
}
