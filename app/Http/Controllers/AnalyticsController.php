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

        // ── Monthly Revenue (last 6 months) ───────────────────────────────────
        $monthlyRevenueData = [];
        for ($i = 5; $i >= 0; $i--) {
            $month     = date('M', strtotime("-$i months"));
            $startDate = date('Y-m-01', strtotime("-$i months"));
            $endDate   = date('Y-m-t',  strtotime("-$i months"));

            $boundary = DB::table('boundaries')->whereNull('deleted_at')
                ->whereBetween('date', [$startDate, $endDate])->sum('actual_boundary') ?? 0;
            $expenses = DB::table('expenses')->whereNull('deleted_at')
                ->whereBetween('date', [$startDate, $endDate])->sum('amount') ?? 0;

            $monthlyRevenueData[] = [
                'month'    => $month,
                'boundary' => (float)$boundary,
                'expenses' => (float)$expenses,
                'net'      => (float)($boundary - $expenses),
            ];
        }

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
            ->selectRaw('DATE_FORMAT(date, "%Y-%m") as month, SUM(amount) as total, COUNT(*) as count')
            ->whereBetween('date', [$date_from, $date_to])
            ->groupBy('month')->orderBy('month')->get();

        // ── Maintenance by Type ────────────────────────────────────────────────
        $maintenanceCosts = DB::table('maintenance')
            ->whereNull('deleted_at')
            ->selectRaw('maintenance_type, SUM(cost) as total_cost, COUNT(*) as count, AVG(cost) as avg_cost')
            ->whereBetween('date_started', [$date_from, $date_to])
            ->groupBy('maintenance_type')->orderByDesc('total_cost')->get();

        // ── Real-time Operational Pulse ──────────────────────────────────────
        $fleet_pulse = [
            'active_units' => DB::table('units')->whereNull('deleted_at')->where('status', 'active')->count(),
            'idle_units'   => DB::table('units')->whereNull('deleted_at')->where('status', 'idle')->count(),
            'maintenance'  => DB::table('units')->whereNull('deleted_at')->where('status', 'maintenance')->count(),
            'surveillance' => DB::table('units')->whereNull('deleted_at')->where('status', 'surveillance')->count(),
        ];

        // ── Financial Health Analysis ────────────────────────────────────────
        $total_boundary = DB::table('boundaries')->whereNull('deleted_at')
            ->whereBetween('date', [$date_from, $date_to])->sum('actual_boundary') ?? 0;
        $total_expenses = DB::table('expenses')->whereNull('deleted_at')
            ->whereBetween('date', [$date_from, $date_to])->sum('amount') ?? 0;
        $total_shortage = DB::table('boundaries')->whereNull('deleted_at')
            ->whereBetween('date', [$date_from, $date_to])->sum('shortage') ?? 0;
        
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
            ->selectRaw('category, SUM(amount) as total, COUNT(*) as count')
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

        return view('analytics.index', compact(
            'monthlyRevenueData', 'unit_idle_analysis', 'driverPerformance',
            'expenseTrends', 'maintenanceCosts', 'total_boundary', 'total_expenses',
            'net_income', 'active_drivers', 'top_units', 'daily_trend',
            'expense_by_category', 'date_from', 'date_to', 'maintenance_cost_trend',
            'fleet_pulse', 'total_shortage', 'break_even_days', 'revenue_leakage_pct',
            'fleet_utilization'
        ));
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
}
