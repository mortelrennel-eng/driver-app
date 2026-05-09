<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class DecisionSupportService
{
    protected string $apiKey;
    protected string $endpoint = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent';

    public function __construct()
    {
        $this->apiKey = config('services.gemini.api_key', env('GEMINI_API_KEY', ''));
    }

    /**
     * Gather a full data snapshot from every critical table in the system.
     */
    public function gatherSystemSnapshot(): array
    {
        // ─── FLEET ───────────────────────────────────────────────────────────
        $units = DB::table('units')
            ->whereNull('deleted_at')
            ->select('id', 'plate_number', 'make', 'model', 'year', 'status',
                     'boundary_rate', 'purchase_cost', 'purchase_date', 'unit_type')
            ->get();

        $totalUnits    = $units->count();
        $activeUnits   = $units->where('status', 'active')->count();
        $idleUnits     = $units->where('status', 'idle')->count();
        $maintUnits    = $units->where('status', 'maintenance')->count();

        // ─── DRIVERS ─────────────────────────────────────────────────────────
        $drivers = DB::table('drivers')
            ->whereNull('deleted_at')
            ->selectRaw("id, CONCAT(COALESCE(first_name,''),' ',COALESCE(last_name,'')) as name, driver_status")
            ->get();

        $activeDrivers = $drivers->whereIn('driver_status', ['available', 'assigned'])->count();

        // ─── BOUNDARIES (Last 60 days) ────────────────────────────────────────
        $boundaryStats = DB::table('boundaries')
            ->whereNull('deleted_at')
            ->where('date', '>=', now()->subDays(60)->toDateString())
            ->selectRaw('
                COUNT(*) as total_records,
                SUM(actual_boundary) as total_collected,
                SUM(shortage) as total_shortage,
                SUM(excess) as total_excess,
                AVG(actual_boundary) as avg_daily,
                COUNT(CASE WHEN shortage > 0 THEN 1 END) as shortage_days
            ')
            ->first();

        // ─── UNIT ROI (Lifetime boundary vs purchase cost) ───────────────────
        $unitROI = DB::table('units as u')
            ->whereNull('u.deleted_at')
            ->leftJoin('boundaries as b', function ($join) {
                $join->on('b.unit_id', '=', 'u.id')->whereNull('b.deleted_at');
            })
            ->selectRaw('
                u.id,
                u.plate_number,
                u.purchase_cost,
                u.boundary_rate,
                COALESCE(SUM(b.actual_boundary), 0) as lifetime_collected,
                COUNT(b.id) as total_days_operated
            ')
            ->groupBy('u.id', 'u.plate_number', 'u.purchase_cost', 'u.boundary_rate')
            ->get()
            ->map(function ($u) {
                $roi = $u->purchase_cost > 0
                    ? round(($u->lifetime_collected / $u->purchase_cost) * 100, 1)
                    : 0;
                return [
                    'plate'             => $u->plate_number,
                    'purchase_cost'     => $u->purchase_cost,
                    'lifetime_collected'=> $u->lifetime_collected,
                    'roi_pct'           => $roi,
                    'days_operated'     => $u->total_days_operated,
                    'roi_achieved'      => $roi >= 100,
                ];
            });

        // ─── MAINTENANCE (Last 90 days) ───────────────────────────────────────
        $maintenanceStats = DB::table('maintenance as m')
            ->whereNull('m.deleted_at')
            ->where('m.date_started', '>=', now()->subDays(90)->toDateString())
            ->join('units as u', 'u.id', '=', 'm.unit_id')
            ->selectRaw('
                u.plate_number,
                COUNT(m.id) as breakdown_count,
                SUM(m.cost) as total_maint_cost,
                AVG(m.cost) as avg_maint_cost
            ')
            ->groupBy('u.plate_number')
            ->orderByDesc('total_maint_cost')
            ->limit(10)
            ->get();

        $totalMaintenanceCost = DB::table('maintenance')
            ->whereNull('deleted_at')
            ->where('date_started', '>=', now()->subDays(90)->toDateString())
            ->sum('cost');

        // ─── EXPENSES (Last 60 days) ──────────────────────────────────────────
        $expenseStats = DB::table('expenses')
            ->whereNull('deleted_at')
            ->where('date', '>=', now()->subDays(60)->toDateString())
            ->selectRaw('category, SUM(amount) as total, COUNT(*) as count')
            ->groupBy('category')
            ->orderByDesc('total')
            ->get();

        $totalExpenses = $expenseStats->sum('total');

        // ─── DRIVER PERFORMANCE (Last 60 days) ───────────────────────────────
        $driverPerf = DB::table('boundaries as b')
            ->whereNull('b.deleted_at')
            ->where('b.date', '>=', now()->subDays(60)->toDateString())
            ->join('drivers as d', 'd.id', '=', 'b.driver_id')
            ->whereNull('d.deleted_at')
            ->selectRaw('
                CONCAT(COALESCE(d.first_name,\'\'),\' \',COALESCE(d.last_name,\'\')) as driver_name,
                COUNT(b.id) as days_worked,
                SUM(b.actual_boundary) as total_collected,
                SUM(b.shortage) as total_shortage,
                SUM(b.excess) as total_excess,
                AVG(b.actual_boundary) as avg_daily,
                COUNT(CASE WHEN b.shortage > 0 THEN 1 END) as shortage_days
            ')
            ->groupBy('d.id', 'driver_name')
            ->orderByDesc('total_collected')
            ->limit(15)
            ->get();

        // ─── NET INCOME (6 months) ────────────────────────────────────────────
        $monthlyFinancials = [];
        for ($i = 5; $i >= 0; $i--) {
            $startDate = date('Y-m-01', strtotime("-$i months"));
            $endDate   = date('Y-m-t',  strtotime("-$i months"));
            $monthLabel = date('Y-m',   strtotime("-$i months"));

            $rev = DB::table('boundaries')->whereNull('deleted_at')
                ->whereBetween('date', [$startDate, $endDate])->sum('actual_boundary') ?? 0;
            $exp = DB::table('expenses')->whereNull('deleted_at')
                ->whereBetween('date', [$startDate, $endDate])->sum('amount') ?? 0;

            $monthlyFinancials[] = [
                'month'   => $monthLabel,
                'revenue' => (float)$rev,
                'expense' => (float)$exp,
                'net'     => (float)($rev - $exp),
            ];
        }

        $latestNet = end($monthlyFinancials)['net'] ?? 0;
        $trend     = count($monthlyFinancials) >= 2
            ? ($monthlyFinancials[5]['net'] - $monthlyFinancials[4]['net'])
            : 0;

        // ─── SALARIES (Last 60 days) ──────────────────────────────────────────
        $totalSalaries = DB::table('salaries')
            ->where('created_at', '>=', now()->subDays(60))
            ->sum('total_salary') ?? 0;

        return [
            'fleet' => [
                'total'       => $totalUnits,
                'active'      => $activeUnits,
                'idle'        => $idleUnits,
                'maintenance' => $maintUnits,
                'utilization_pct' => $totalUnits > 0 ? round(($activeUnits / $totalUnits) * 100, 1) : 0,
            ],
            'drivers' => [
                'total'  => $drivers->count(),
                'active' => $activeDrivers,
            ],
            'financials' => [
                'monthly'        => $monthlyFinancials,
                'latest_net'     => round($latestNet, 2),
                'net_trend'      => round($trend, 2),
                'total_expenses' => round($totalExpenses, 2),
                'total_salaries' => round($totalSalaries, 2),
            ],
            'boundaries' => [
                'total_collected'  => round($boundaryStats->total_collected ?? 0, 2),
                'total_shortage'   => round($boundaryStats->total_shortage ?? 0, 2),
                'total_excess'     => round($boundaryStats->total_excess ?? 0, 2),
                'avg_daily'        => round($boundaryStats->avg_daily ?? 0, 2),
                'shortage_days'    => (int)($boundaryStats->shortage_days ?? 0),
                'total_records'    => (int)($boundaryStats->total_records ?? 0),
            ],
            'unit_roi'  => $unitROI->values()->toArray(),
            'maintenance' => [
                'total_cost_90d' => round($totalMaintenanceCost, 2),
                'per_unit'       => $maintenanceStats->toArray(),
            ],
            'expenses_by_category' => $expenseStats->toArray(),
            'driver_performance'   => $driverPerf->toArray(),
        ];
    }

    /**
     * Build a rich contextual prompt for Gemini.
     */
    protected function buildPrompt(array $snapshot): string
    {
        $json = json_encode($snapshot, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        return <<<PROMPT
You are the AI Strategic Advisor for "Euro Taxi Inc.", a professional taxi fleet management company in the Philippines. 
You have been given a FULL real-time data snapshot of the company's operations.
Your job is to analyze the data deeply and provide actionable strategic recommendations AND a 30-day forecast.

## SYSTEM DATA SNAPSHOT (Real-time):
{$json}

## YOUR TASK:
Analyze the above data and return a JSON object containing:
1. "recommendations": An array of 5-7 strategic recommendations.
2. "forecast": A 30-day prediction object for Revenue, Expenses, and Maintenance.
3. "risks": A list of top 3 operational risks based on current trends.

You must return ONLY valid JSON, no markdown, no extra text.

Each recommendation must follow this structure:
{
  "category": "fleet|finance|drivers|maintenance|operations",
  "priority": "critical|high|medium|low",
  "icon": "a single relevant emoji",
  "title": "Short title",
  "insight": "1-2 sentence summary with numbers",
  "reasoning": "Detailed explanation",
  "actions": ["Action 1", "Action 2"],
  "metric": "Number",
  "metric_label": "Label",
  "confidence": 85
}

The forecast object should look like this:
{
  "predicted_revenue": 150000.00,
  "predicted_expenses": 50000.00,
  "predicted_maintenance": 12000.00,
  "confidence_level": "high|medium|low",
  "growth_rate_pct": 5.2
}

## RULES:
1. Use REAL numbers from the data.
2. Prioritize financial impact.
3. Return ONLY the JSON object, nothing else.
PROMPT;
    }

    /**
     * Call the Gemini API and return the structured insights.
     */
    public function getAiInsights(bool $forceRefresh = false): array
    {
        $cacheKey = 'dss_ai_insights_v3';

        // Return cached result if fresh (15 min cache)
        if (!$forceRefresh && Cache::has($cacheKey)) {
            $cached = Cache::get($cacheKey);
            $cached['from_cache'] = true;
            return $cached;
        }

        try {
            $snapshot = $this->gatherSystemSnapshot();
            $forecast = $this->calculateHeuristicForecast($snapshot);
            $lineage  = $this->getDataLineageMap();

            if (empty($this->apiKey)) {
                return $this->buildFallbackInsights($snapshot, $forecast, $lineage);
            }

            $prompt = $this->buildPrompt($snapshot);

            $payload = json_encode([
                'contents' => [[
                    'parts' => [['text' => $prompt]]
                ]],
                'generationConfig' => [
                    'temperature'     => 0.3,
                    'maxOutputTokens' => 2048,
                    'responseMimeType'=> 'application/json',
                ],
            ]);

            $ch = curl_init($this->endpoint . '?key=' . $this->apiKey);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST           => true,
                CURLOPT_POSTFIELDS     => $payload,
                CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
                CURLOPT_TIMEOUT        => 30,
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode !== 200 || !$response) {
                return $this->buildFallbackInsights($snapshot, $forecast, $lineage);
            }

            $data = json_decode($response, true);
            $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? '{}';
            
            // Strip markdown fences if present
            $text = preg_replace('/^```(?:json)?\s*/i', '', trim($text));
            $text = preg_replace('/\s*```$/', '', $text);

            $aiResult = json_decode($text, true);

            if (!is_array($aiResult) || empty($aiResult)) {
                return $this->buildFallbackInsights($snapshot, $forecast, $lineage);
            }

            $result = [
                'insights'    => $aiResult['recommendations'] ?? [],
                'forecast'    => $aiResult['forecast'] ?? $forecast,
                'risks'       => $aiResult['risks'] ?? [],
                'lineage'     => $lineage,
                'generated_at'=> now()->toIso8601String(),
                'from_cache'  => false,
                'data_points' => count($snapshot['unit_roi']) + count($snapshot['driver_performance']),
                'snapshot'    => [
                    'fleet_utilization' => $snapshot['fleet']['utilization_pct'],
                    'latest_net'        => $snapshot['financials']['latest_net'],
                    'total_shortage'    => $snapshot['boundaries']['total_shortage'],
                ],
            ];

            Cache::put($cacheKey, $result, now()->addMinutes(15));
            return $result;

        } catch (\Throwable $e) {
            \Log::error('DSS Exception: ' . $e->getMessage());
            return $this->buildFallbackInsights();
        }
    }

    /**
     * Calculate 30-day forecast using weighted moving averages.
     */
    public function calculateHeuristicForecast(array $snapshot): array
    {
        $monthly = $snapshot['financials']['monthly'] ?? [];
        if (count($monthly) < 2) {
            return [
                'predicted_revenue' => 0,
                'predicted_expenses' => 0,
                'predicted_maintenance' => 0,
                'confidence_level' => 'low',
                'growth_rate_pct' => 0
            ];
        }

        // Revenue Forecast
        $revenues = collect($monthly)->pluck('revenue');
        $avgRev   = $revenues->avg();
        $growth   = ($revenues->last() > 0 && $revenues->first() > 0) ? (($revenues->last() - $revenues->first()) / $revenues->first()) : 0;
        
        // Expense Forecast
        $expenses = collect($monthly)->pluck('expense');
        $avgExp   = $expenses->avg();

        // Maintenance Forecast (Last 90 days cost / 3 to get monthly)
        $avgMaint = ($snapshot['maintenance']['total_cost_90d'] ?? 0) / 3;

        return [
            'predicted_revenue'    => round($revenues->last() * (1 + ($growth / 6)), 2),
            'predicted_expenses'   => round($avgExp, 2),
            'predicted_maintenance' => round($avgMaint, 2),
            'confidence_level'     => count($monthly) >= 6 ? 'high' : 'medium',
            'growth_rate_pct'      => round($growth * 100, 1)
        ];
    }

    /**
     * Detailed map of where AI gets its data.
     */
    public function getDataLineageMap(): array
    {
        return [
            'Fleet Data' => [
                'Source'      => 'units table',
                'Frequency'   => 'Real-time',
                'Data Points' => 'Unit Status, ROI, Purchase Cost, Boundary Rates',
                'Description' => 'Calculates fleet utilization and investment recovery (ROI).'
            ],
            'Financials' => [
                'Source'      => 'boundaries & expenses tables',
                'Frequency'   => 'Live transactions',
                'Data Points' => 'Actual collections, Shortages, Operating Expenses',
                'Description' => 'Determines net income trends and financial health.'
            ],
            'Driver Performance' => [
                'Source'      => 'boundaries cross-referenced with drivers',
                'Frequency'   => 'Daily closing',
                'Data Points' => 'Daily collection average, Shortage frequency, Net excess',
                'Description' => 'Identifies top performers and high-risk driver behavior.'
            ],
            'Maintenance' => [
                'Source'      => 'maintenance & spare_parts tables',
                'Frequency'   => 'On-job update',
                'Data Points' => 'Service costs, Breakdown frequency, Unit downtime',
                'Description' => 'Alerts on high-cost units and predicts future repair needs.'
            ]
        ];
    }

    /**
     * Fallback: heuristic-based insights when no API key or API fails.
     */
    protected function buildFallbackInsights(array $snapshot = [], array $forecast = [], array $lineage = []): array
    {
        if (empty($snapshot)) {
            try { $snapshot = $this->gatherSystemSnapshot(); } catch (\Throwable $e) { $snapshot = []; }
        }
        if (empty($forecast)) { $forecast = $this->calculateHeuristicForecast($snapshot); }
        if (empty($lineage)) { $lineage = $this->getDataLineageMap(); }

        $insights = [];
        $fleet    = $snapshot['fleet']    ?? [];
        $fin      = $snapshot['financials'] ?? [];
        $bound    = $snapshot['boundaries'] ?? [];
        $unitROI  = $snapshot['unit_roi']   ?? [];
        $maint    = $snapshot['maintenance'] ?? [];

        // 1. Fleet Utilization
        $util = $fleet['utilization_pct'] ?? 0;
        $insights[] = [
            'category'     => 'fleet',
            'priority'     => $util < 80 ? 'high' : 'medium',
            'icon'         => '🚕',
            'title'        => $util < 80 ? 'Fleet Utilization Below Target' : 'Fleet Utilization On Track',
            'insight'      => "Current fleet utilization is {$util}% ({$fleet['active']} active out of {$fleet['total']} total units). You have " . ($fleet['idle'] ?? 0) . " idle units losing revenue daily.",
            'reasoning'    => "Every idle unit represents a static asset that still incurs fixed costs (registration, insurance, base maintenance) without generating offsetting revenue. At an average boundary of ₱1,200, having " . ($fleet['idle'] ?? 0) . " idle units costs the company approximately ₱" . number_format(($fleet['idle'] ?? 0) * 1200) . " per day in lost gross margin. High utilization is the primary driver of fleet profitability.",
            'actions'      => ['Post driver recruitment ads immediately', 'Review idle unit conditions', 'Consider temporary driver swap arrangements'],
            'metric'       => $util . '%',
            'metric_label' => 'Fleet Utilization',
            'confidence'   => 90,
        ];

        // 2. ROI Units
        $roiAchieved = collect($unitROI)->where('roi_achieved', true)->count();
        if ($roiAchieved > 0) {
            $insights[] = [
                'category'     => 'finance',
                'priority'     => 'high',
                'icon'         => '💰',
                'title'        => "{$roiAchieved} Units Have Achieved Full ROI",
                'insight'      => "{$roiAchieved} unit(s) have collected more than their original purchase cost in boundary revenue. These units are now generating pure profit.",
                'reasoning'    => "Achieving 100% ROI (Return on Investment) means the vehicle has completely paid for itself through daily operations. These units are now 'Profit Engines'—any revenue they generate, minus maintenance and insurance, is pure net income. This is the optimal time to either reinvest those profits into new fleet expansion or allocate them to a 'Fleet Refresh Fund' for future unit replacements.",
                'actions'      => ['Review ROI-achieved units in Unit Profitability', 'Consider boundary rate reduction by ₱50-100/day to reward loyal drivers'],
                'metric'       => $roiAchieved . ' units',
                'metric_label' => 'Full ROI Achieved',
                'confidence'   => 95,
            ];
        }

        // 3. Shortage warning
        $shortageAmt = $bound['total_shortage'] ?? 0;
        if ($shortageAmt > 0) {
            $shortDays = $bound['shortage_days'] ?? 0;
            $insights[] = [
                'category'     => 'drivers',
                'priority'     => $shortageAmt > 10000 ? 'critical' : 'high',
                'icon'         => '⚠️',
                'title'        => 'Driver Shortage Pattern Detected',
                'insight'      => "₱" . number_format($shortageAmt, 2) . " in uncollected boundary across {$shortDays} shortage-days in the last 60 days.",
                'reasoning'    => "Shortages represent 'Revenue Leakage'. While occasional shortages are normal due to traffic or vehicle issues, a pattern of uncollected boundary suggests either external market pressure (e.g., rising fuel costs) or internal driver accountability issues. If left unaddressed, this 'Debt Drift' can lead to driver turnover or significant financial losses. Every 1% reduction in shortage directly increases net profit by the same amount.",
                'actions'      => ['Identify drivers with recurring shortages in Analytics', 'Schedule a one-on-one driver performance review', 'Verify if current boundary rates are sustainable for the current market'],
                'metric'       => '₱' . number_format($shortageAmt, 0),
                'metric_label' => 'Total Shortage (60 days)',
                'confidence'   => 92,
            ];
        }

        // 4. Maintenance Cost
        $maintCost = $maint['total_cost_90d'] ?? 0;
        if ($maintCost > 0) {
            $insights[] = [
                'category'     => 'maintenance',
                'priority'     => $maintCost > 50000 ? 'critical' : 'medium',
                'icon'         => '🔧',
                'title'        => 'High Maintenance Spending Alert',
                'insight'      => "₱" . number_format($maintCost, 2) . " spent on maintenance in the last 90 days. Top-cost units should be evaluated for retirement.",
                'reasoning'    => "Maintenance costs typically follow an 'Exponential Aging Curve'. Once a unit's repair costs exceed 25% of its generated revenue over a 90-day period, it is likely reaching its 'Economic End-of-Life'. High spending here suggests we are reacting to breakdowns rather than preventing them. Shifting 10% of this budget to Preventive Maintenance (PM) could reduce emergency repair costs by up to 30%.",
                'actions'      => ['Check Unit Profitability for maintenance-to-revenue ratio', 'Schedule preventive maintenance for units with high frequency but low cost'],
                'metric'       => '₱' . number_format($maintCost, 0),
                'metric_label' => 'Maintenance Cost (90 days)',
                'confidence'   => 88,
            ];
        }

        // 5. Net Income Trend
        $latestNet = $fin['latest_net'] ?? 0;
        $netTrend  = $fin['net_trend']  ?? 0;
        $insights[] = [
            'category'     => 'finance',
            'priority'     => $latestNet < 0 ? 'critical' : 'medium',
            'icon'         => $latestNet >= 0 ? '📈' : '📉',
            'title'        => $latestNet >= 0 ? 'Net Income is Positive' : 'Net Income Warning',
            'insight'      => "Latest monthly net income: ₱" . number_format($latestNet, 2) . ". Trend vs previous month: " . ($netTrend >= 0 ? '+' : '') . "₱" . number_format($netTrend, 2) . ".",
            'reasoning'    => "Your net income is the true 'Heartbeat' of the business. A positive trend suggests that your revenue growth is outpacing your expense growth. However, if the trend is negative despite stable revenue, look for 'Hidden Costs' in office expenses or payroll. A healthy fleet should maintain a net margin of at least 15-20% to account for future fleet reinvestment requirements.",
            'actions'      => ['Review expense categories for cost-cutting opportunities', 'Focus on maximizing boundary collection efficiency'],
            'metric'       => '₱' . number_format($latestNet, 0),
            'metric_label' => 'Latest Monthly Net',
            'confidence'   => 85,
        ];

        return [
            'insights'    => $insights,
            'forecast'    => $forecast,
            'risks'       => [
                'Increasing maintenance costs on older units',
                'Driver attrition due to boundary pressure',
                'Revenue leakage through uncollected shortages'
            ],
            'lineage'     => $lineage,
            'generated_at'=> now()->toIso8601String(),
            'from_cache'  => false,
            'fallback'    => true,
            'data_points' => count($unitROI) + count($snapshot['driver_performance'] ?? []),
            'snapshot'    => [
                'fleet_utilization' => $fleet['utilization_pct'] ?? 0,
                'latest_net'        => $latestNet,
                'total_shortage'    => $shortageAmt ?? 0,
            ],
        ];
    }
}
