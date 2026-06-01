@extends('layouts.app')

@section('title', 'AI Fleet Intelligence - Euro System')
@section('page-heading', 'Advanced Fleet Analytics')
@section('page-subheading', 'Real-time Pulse • Historical Trends • Predictive Forecasting')

@section('content')
    {{-- Export Actions Bar --}}
    <div class="flex flex-wrap gap-2 mb-4 justify-end">
        <a href="{{ route('analytics.export.csv', ['type' => 'revenue', 'date_from' => $date_from, 'date_to' => $date_to]) }}"
           class="flex items-center gap-1.5 px-4 py-2 bg-white border border-slate-200 text-slate-700 text-xs font-bold rounded-xl hover:bg-slate-50 shadow-sm transition-all">
            <i data-lucide="download" class="w-3.5 h-3.5 text-emerald-500"></i> Revenue CSV
        </a>
        <a href="{{ route('analytics.export.csv', ['type' => 'drivers', 'date_from' => $date_from, 'date_to' => $date_to]) }}"
           class="flex items-center gap-1.5 px-4 py-2 bg-white border border-slate-200 text-slate-700 text-xs font-bold rounded-xl hover:bg-slate-50 shadow-sm transition-all">
            <i data-lucide="download" class="w-3.5 h-3.5 text-blue-500"></i> Drivers CSV
        </a>
        <a href="{{ route('analytics.export.csv', ['type' => 'maintenance', 'date_from' => $date_from, 'date_to' => $date_to]) }}"
           class="flex items-center gap-1.5 px-4 py-2 bg-white border border-slate-200 text-slate-700 text-xs font-bold rounded-xl hover:bg-slate-50 shadow-sm transition-all">
            <i data-lucide="download" class="w-3.5 h-3.5 text-amber-500"></i> Maintenance CSV
        </a>
    </div>

    {{-- Global Filter --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 mb-8">
        <form method="GET" action="{{ route('analytics.index') }}" class="flex flex-col md:flex-row items-end gap-4">
            <div class="flex-1 w-full">
                <label class="text-[11px] font-black text-slate-400 uppercase tracking-widest mb-1.5 block">Analysis Period</label>
                <div class="grid grid-cols-2 gap-3">
                    <input type="date" name="date_from" value="{{ $date_from }}"
                        class="block w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all text-sm font-semibold">
                    <input type="date" name="date_to" value="{{ $date_to }}"
                        class="block w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all text-sm font-semibold">
                </div>
            </div>
            <button type="submit"
                class="w-full md:w-auto px-8 py-2.5 bg-indigo-600 text-white rounded-xl font-black text-sm hover:bg-indigo-700 transition-all shadow-lg shadow-indigo-200 flex items-center justify-center gap-2">
                <i data-lucide="refresh-cw" class="w-4 h-4"></i> Sync Data
            </button>
        </form>
    </div>

    {{-- ── Advanced Navigation ──────────────────────────────────────────────── --}}
    <div class="flex flex-wrap items-center gap-2 p-1.5 bg-slate-100/80 backdrop-blur-md rounded-2xl mb-8 w-fit mx-auto shadow-inner border border-slate-200/60">
        <button onclick="switchTab('pulse')" id="tab-pulse"
            class="tab-btn px-6 py-2.5 rounded-xl text-xs font-black transition-all flex items-center gap-2 bg-white text-slate-900 shadow-sm border border-slate-200">
            <i data-lucide="activity" class="w-4 h-4 text-indigo-500"></i> Real-time Pulse
        </button>
        <button onclick="switchTab('performance')" id="tab-performance"
            class="tab-btn px-6 py-2.5 rounded-xl text-xs font-black transition-all flex items-center gap-2 text-slate-500 hover:text-slate-700">
            <i data-lucide="bar-chart-3" class="w-4 h-4"></i> Descriptive Analytics
        </button>
        <button onclick="switchTab('forecast')" id="tab-forecast"
            class="tab-btn px-6 py-2.5 rounded-xl text-xs font-black transition-all flex items-center gap-2 text-slate-500 hover:text-slate-700">
            <i data-lucide="trending-up" class="w-4 h-4"></i> Predictive Forecasting
        </button>
        <button onclick="switchTab('strategy')" id="tab-strategy"
            class="tab-btn px-6 py-2.5 rounded-xl text-xs font-black transition-all flex items-center gap-2 text-slate-500 hover:text-slate-700">
            <i data-lucide="brain-circuit" class="w-4 h-4"></i> AI Strategic Insights
        </button>
    </div>

    {{-- ══════════════════════════════════════════════════════════════════════
         SECTION 1: OPERATIONAL PULSE (Real-time)
         ══════════════════════════════════════════════════════════════════════ --}}
    <div id="section-pulse" class="space-y-8 animate-in fade-in duration-500">
        {{-- High Level Pulse Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            {{-- Fleet Health --}}
            <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm hover:shadow-md transition-all group">
                <div class="flex items-start justify-between mb-4">
                    <div class="p-3 bg-indigo-50 rounded-2xl text-indigo-600 group-hover:scale-110 transition-transform">
                        <i data-lucide="car" class="w-6 h-6"></i>
                    </div>
                    <span class="text-[10px] font-black text-indigo-600 bg-indigo-50 px-2.5 py-1 rounded-full uppercase tracking-widest">Real-time</span>
                </div>
                <h3 class="text-[11px] font-black text-slate-400 uppercase tracking-widest mb-1">Fleet Utilization</h3>
                <div class="flex items-end gap-2 mb-2">
                    <span class="text-3xl font-black text-slate-800 leading-none">{{ $fleet_utilization }}%</span>
                    <span class="text-xs font-bold text-slate-500 pb-0.5">Active Now</span>
                </div>
                <div class="h-1.5 bg-slate-100 rounded-full overflow-hidden">
                    <div class="h-full bg-indigo-500 transition-all duration-1000" style="width: {{ $fleet_utilization }}%"></div>
                </div>
                <p class="text-[10px] text-slate-500 mt-3 leading-relaxed">
                    Percentage of units currently generating revenue versus idle or in maintenance.
                </p>
            </div>

            {{-- Financial Pulse --}}
            <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm hover:shadow-md transition-all group">
                <div class="flex items-start justify-between mb-4">
                    <div class="p-3 bg-emerald-50 rounded-2xl text-emerald-600 group-hover:scale-110 transition-transform">
                        <i data-lucide="wallet" class="w-6 h-6"></i>
                    </div>
                    <span class="text-[10px] font-black text-emerald-600 bg-emerald-50 px-2.5 py-1 rounded-full uppercase tracking-widest">Net Pulse</span>
                </div>
                <h3 class="text-[11px] font-black text-slate-400 uppercase tracking-widest mb-1">Net Margin</h3>
                <div class="flex items-end gap-2 mb-2">
                    <span class="text-3xl font-black text-slate-800 leading-none">{{ formatCurrency($net_income) }}</span>
                </div>
                <p class="text-[10px] text-slate-500 mt-3 leading-relaxed">
                    Total boundary collections minus all operating expenses for the selected period.
                </p>
            </div>

            {{-- Revenue Leakage --}}
            <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm hover:shadow-md transition-all group">
                <div class="flex items-start justify-between mb-4">
                    <div class="p-3 bg-rose-50 rounded-2xl text-rose-600 group-hover:scale-110 transition-transform">
                        <i data-lucide="trending-down" class="w-6 h-6"></i>
                    </div>
                    <span class="text-[10px] font-black text-rose-600 bg-rose-50 px-2.5 py-1 rounded-full uppercase tracking-widest">Risk Factor</span>
                </div>
                <h3 class="text-[11px] font-black text-slate-400 uppercase tracking-widest mb-1">Revenue Leakage</h3>
                <div class="flex items-end gap-2 mb-2">
                    <span class="text-3xl font-black text-slate-800 leading-none">{{ $revenue_leakage_pct }}%</span>
                    <span class="text-xs font-bold text-rose-500 pb-0.5">Shortage</span>
                </div>
                <p class="text-[10px] text-slate-500 mt-3 leading-relaxed">
                    Uncollected boundary revenue (shortages) relative to total expected revenue.
                </p>
            </div>

            {{-- Break-even Analysis --}}
            <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm hover:shadow-md transition-all group">
                <div class="flex items-start justify-between mb-4">
                    <div class="p-3 bg-amber-50 rounded-2xl text-amber-600 group-hover:scale-110 transition-transform">
                        <i data-lucide="target" class="w-6 h-6"></i>
                    </div>
                    <span class="text-[10px] font-black text-amber-600 bg-amber-50 px-2.5 py-1 rounded-full uppercase tracking-widest">KPI Target</span>
                </div>
                <h3 class="text-[11px] font-black text-slate-400 uppercase tracking-widest mb-1">Break-even Cycle</h3>
                <div class="flex items-end gap-2 mb-2">
                    <span class="text-3xl font-black text-slate-800 leading-none">{{ $break_even_days }}</span>
                    <span class="text-xs font-bold text-slate-500 pb-0.5">Oper. Days</span>
                </div>
                <p class="text-[10px] text-slate-500 mt-3 leading-relaxed">
                    Estimated number of full-revenue days needed each month to cover all fixed expenses.
                </p>
            </div>
        </div>

        {{-- Detailed Pulse Breakdown --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            {{-- Fleet Status Distribution --}}
            <div class="lg:col-span-2 bg-white rounded-3xl border border-slate-200 overflow-hidden shadow-sm">
                <div class="px-8 py-6 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                    <div>
                        <h3 class="text-lg font-black text-slate-800">Operational Unit Distribution</h3>
                        <p class="text-xs text-slate-500">Live breakdown of fleet readiness and activity</p>
                    </div>
                    <div class="flex items-center gap-4">
                         <div class="flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                            <span class="text-[10px] font-bold text-slate-600 uppercase">Active</span>
                         </div>
                         <div class="flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-slate-400"></span>
                            <span class="text-[10px] font-bold text-slate-600 uppercase">Idle</span>
                         </div>
                         <div class="flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                            <span class="text-[10px] font-bold text-slate-600 uppercase">Maint</span>
                         </div>
                    </div>
                </div>
                <div class="p-8">
                    <div class="flex h-12 w-full rounded-2xl overflow-hidden shadow-inner border border-slate-200/50 mb-8">
                        @php 
                            $total = array_sum($fleet_pulse); 
                            $actPct = $total > 0 ? ($fleet_pulse['active_units'] / $total) * 100 : 0;
                            $idlPct = $total > 0 ? ($fleet_pulse['idle_units'] / $total) * 100 : 0;
                            $mntPct = $total > 0 ? ($fleet_pulse['maintenance'] / $total) * 100 : 0;
                            $surPct = $total > 0 ? ($fleet_pulse['surveillance'] / $total) * 100 : 0;
                        @endphp
                        <div class="h-full bg-emerald-500 transition-all" style="width: {{ $actPct }}%" title="Active: {{ $fleet_pulse['active_units'] }}"></div>
                        <div class="h-full bg-slate-300 transition-all" style="width: {{ $idlPct }}%" title="Idle: {{ $fleet_pulse['idle_units'] }}"></div>
                        <div class="h-full bg-amber-500 transition-all" style="width: {{ $mntPct }}%" title="Maintenance: {{ $fleet_pulse['maintenance'] }}"></div>
                        <div class="h-full bg-rose-500 transition-all" style="width: {{ $surPct }}%" title="Surveillance: {{ $fleet_pulse['surveillance'] }}"></div>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-6">
                        <div class="space-y-1">
                            <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest block">Active Fleet</span>
                            <p class="text-2xl font-black text-emerald-600">{{ $fleet_pulse['active_units'] }}</p>
                            <p class="text-[10px] text-slate-500 leading-tight">Units currently assigned to drivers and on the road.</p>
                        </div>
                        <div class="space-y-1">
                            <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest block">Awaiting Drivers</span>
                            <p class="text-2xl font-black text-slate-600">{{ $fleet_pulse['idle_units'] }}</p>
                            <p class="text-[10px] text-slate-500 leading-tight">Functional units parked due to lack of available drivers.</p>
                        </div>
                        <div class="space-y-1">
                            <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest block">Under Repair</span>
                            <p class="text-2xl font-black text-amber-600">{{ $fleet_pulse['maintenance'] }}</p>
                            <p class="text-[10px] text-slate-500 leading-tight">Units in the garage for scheduled or emergency service.</p>
                        </div>
                        <div class="space-y-1">
                            <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest block">Under Watch</span>
                            <p class="text-2xl font-black text-rose-600">{{ $fleet_pulse['surveillance'] }}</p>
                            <p class="text-[10px] text-slate-500 leading-tight">Units flagged for suspicious activity or non-payment.</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Operational Insight Summary --}}
            <div class="bg-indigo-900 rounded-3xl p-8 text-white relative overflow-hidden group">
                <div class="relative z-10">
                    <h3 class="text-xl font-black mb-4">Pulse Analysis</h3>
                    <div class="space-y-6">
                        <div class="flex gap-4">
                            <div class="w-8 h-8 rounded-full bg-white/10 flex items-center justify-center shrink-0">
                                <i data-lucide="info" class="w-4 h-4 text-indigo-300"></i>
                            </div>
                            <p class="text-sm text-indigo-100/90 leading-relaxed">
                                Your fleet is operating at <span class="text-white font-bold">{{ $fleet_utilization }}% capacity</span>. 
                                @if($fleet_utilization < 80)
                                    @php
                                        $lostRevenue = ($fleet_pulse['idle_units'] + $fleet_pulse['maintenance'] + $fleet_pulse['surveillance']) * 1200;
                                    @endphp
                                    This is <span class="text-rose-300 font-bold underline">below optimal levels</span>. You are losing approximately ₱{{ number_format($lostRevenue) }} in daily potential revenue.
                                @else
                                    Excellent utilization! Your fleet is highly optimized.
                                @endif
                            </p>
                        </div>
                        <div class="flex gap-4">
                            <div class="w-8 h-8 rounded-full bg-white/10 flex items-center justify-center shrink-0">
                                <i data-lucide="shield-check" class="w-4 h-4 text-emerald-300"></i>
                            </div>
                            <p class="text-sm text-indigo-100/90 leading-relaxed">
                                Financial health is <span class="text-white font-bold">{{ $net_income > 0 ? 'Stable' : 'At Risk' }}</span>. 
                                Current shortage leakage is <span class="text-rose-300 font-bold">{{ $revenue_leakage_pct }}%</span>. Reducing this to < 3% would add <span class="text-emerald-300 font-bold">₱{{ number_format($total_shortage * 0.5) }}</span> to your bottom line.
                            </p>
                        </div>
                    </div>
                    <button onclick="switchTab('strategy')" class="mt-8 w-full py-3 bg-white text-indigo-900 rounded-xl font-black text-sm hover:bg-indigo-50 transition-all flex items-center justify-center gap-2 shadow-xl">
                        View AI Strategy <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    </button>
                </div>
                {{-- Decorative background --}}
                <div class="absolute -right-20 -bottom-20 w-64 h-64 bg-indigo-500/20 rounded-full blur-3xl group-hover:scale-150 transition-transform duration-1000"></div>
                <div class="absolute -left-10 -top-10 w-32 h-32 bg-white/5 rounded-full blur-xl animate-pulse"></div>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════════════════
         SECTION 2: PERFORMANCE TRENDS (Historical)
         ══════════════════════════════════════════════════════════════════════ --}}
    <div id="section-performance" class="hidden space-y-8 animate-in slide-in-from-bottom-4 duration-500">

        {{-- Revenue Heatmap Calendar --}}
        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6 mb-6">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h3 class="text-base font-black text-slate-800 flex items-center gap-2">
                        <i data-lucide="calendar-days" class="w-5 h-5 text-indigo-500"></i>
                        Revenue Collection Heatmap
                    </h3>
                    <p class="text-[11px] text-slate-500 mt-0.5">Daily boundary collection intensity over the last 12 months</p>
                </div>
                <div class="flex items-center gap-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest">
                    <span class="w-3 h-3 rounded-sm bg-slate-100 inline-block"></span> Low
                    <span class="w-3 h-3 rounded-sm bg-amber-200 inline-block"></span>
                    <span class="w-3 h-3 rounded-sm bg-amber-400 inline-block"></span>
                    <span class="w-3 h-3 rounded-sm bg-emerald-500 inline-block"></span> High
                </div>
            </div>
            <div id="revenueHeatmap" class="overflow-x-auto">
                <div class="flex gap-1" id="heatmapGrid" style="min-width: 600px;">
                    {{-- Generated by JS --}}
                </div>
            </div>
        </div>

        {{-- Driver Utilization Chart --}}
        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6 mb-6">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h3 class="text-base font-black text-slate-800 flex items-center gap-2">
                        <i data-lucide="users" class="w-5 h-5 text-purple-500"></i>
                        Driver Utilization Rate
                    </h3>
                    <p class="text-[11px] text-slate-500 mt-0.5">Days worked vs idle per driver in the last 30 days</p>
                </div>
                <div class="flex gap-3 text-[10px] font-bold uppercase tracking-widest">
                    <span class="flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-full bg-emerald-500 inline-block"></span>Active (80%+)</span>
                    <span class="flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-full bg-amber-400 inline-block"></span>Moderate</span>
                    <span class="flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-full bg-rose-400 inline-block"></span>Low</span>
                </div>
            </div>
            <div style="position:relative; height:320px;">
                <canvas id="driverUtilizationChart"></canvas>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            {{-- Revenue vs Expenses --}}
            <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-8">
                <div class="flex items-center justify-between mb-8">
                    <div>
                        <h3 class="text-lg font-black text-slate-800">Financial Growth Trend</h3>
                        <p class="text-xs text-slate-500">Comparing 6-month revenue against operating costs</p>
                    </div>
                </div>
                <div class="h-[300px]">
                    <canvas id="revenueChart"></canvas>
                </div>
                <div class="mt-8 p-5 bg-indigo-50/50 rounded-2xl border border-indigo-100">
                    <div class="flex items-start gap-3">
                        <i data-lucide="info" class="w-5 h-5 text-indigo-500 flex-shrink-0 mt-0.5"></i>
                        <div>
                            <p class="text-[11px] font-black text-indigo-700 uppercase tracking-widest mb-1">Detailed Explanation & Insight</p>
                            <p class="text-xs text-slate-600 leading-relaxed">
                                This graph visualizes your <strong>"Net Margin Gap"</strong> over the last 6 months. 
                                <br><br>
                                <strong>How to read this:</strong> A widening gap between the green (Revenue) and blue (Expenses) lines indicates improving operational efficiency. If the lines converge or cross, your operating costs are eating up your profit.
                                <br><br>
                                <strong>Suggested Action:</strong> Look closely at the months where the gap is tightest. Review recurring office expenses and maintenance frequency during those periods to identify and eliminate wasteful spending.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Daily Collection Trend --}}
            <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-8">
                <div class="flex items-center justify-between mb-8">
                    <div>
                        <h3 class="text-lg font-black text-slate-800">Daily Collection Consistency</h3>
                        <p class="text-xs text-slate-500">Daily boundary totals for the current period</p>
                    </div>
                </div>
                <div class="h-[300px]">
                    <canvas id="dailyTrendChart"></canvas>
                </div>
                <div class="mt-8 p-5 bg-indigo-50/50 rounded-2xl border border-indigo-100">
                    <div class="flex items-start gap-3">
                        <i data-lucide="info" class="w-5 h-5 text-indigo-500 flex-shrink-0 mt-0.5"></i>
                        <div>
                            <p class="text-[11px] font-black text-indigo-700 uppercase tracking-widest mb-1">Detailed Explanation & Insight</p>
                            <p class="text-xs text-slate-600 leading-relaxed">
                                This chart displays the <strong>daily collection performance</strong> across the current period.
                                <br><br>
                                <strong>How to read this:</strong> Consistent bar heights indicate stable driver performance and reliable cash flow. Look for "Valley Patterns" (dips in the bars) which typically correlate with coding days or weekends. 
                                <br><br>
                                <strong>Suggested Action:</strong> If you spot unexpected deep valleys on normal weekdays, investigate immediately for unauthorized driver absences or sudden mechanical breakdowns across multiple units.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Maintenance Breakdown --}}
            <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-8">
                <div class="flex items-center justify-between mb-8">
                    <div>
                        <h3 class="text-lg font-black text-slate-800">Maintenance Cost Distribution</h3>
                        <p class="text-xs text-slate-500">Where is your maintenance budget going?</p>
                    </div>
                </div>
                <div class="h-[300px]">
                    <canvas id="expenseChart"></canvas>
                </div>
                <div class="mt-8 p-5 bg-indigo-50/50 rounded-2xl border border-indigo-100">
                    <div class="flex items-start gap-3">
                        <i data-lucide="info" class="w-5 h-5 text-indigo-500 flex-shrink-0 mt-0.5"></i>
                        <div>
                            <p class="text-[11px] font-black text-indigo-700 uppercase tracking-widest mb-1">Detailed Explanation & Insight</p>
                            <p class="text-xs text-slate-600 leading-relaxed">
                                This doughnut chart highlights the <strong>distribution of your maintenance budget</strong>.
                                <br><br>
                                <strong>How to read this:</strong> It identifies the specific expense categories that drain your profits. A very large slice means a significant portion of your capital is spent there.
                                <br><br>
                                <strong>Suggested Action:</strong> High "Spare Parts" or "Engine" costs usually suggest a need for more preventive maintenance to avoid major component failure. Consider negotiating bulk discounts with your suppliers for the largest slice.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- High Risk Units --}}
            <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-8">
                <div class="flex items-center justify-between mb-8">
                    <div>
                        <h3 class="text-lg font-black text-slate-800">High-Cost Unit Analysis</h3>
                        <p class="text-xs text-slate-500">Units with excessive maintenance spend (90 Days)</p>
                    </div>
                </div>
                <div class="h-[300px]">
                    <canvas id="maintenanceChart"></canvas>
                </div>
                <div class="mt-8 p-5 bg-rose-50/50 rounded-2xl border border-rose-100">
                    <div class="flex items-start gap-3">
                        <i data-lucide="alert-triangle" class="w-5 h-5 text-rose-500 flex-shrink-0 mt-0.5"></i>
                        <div>
                            <p class="text-[11px] font-black text-rose-700 uppercase tracking-widest mb-1">Detailed Explanation & Insight</p>
                            <p class="text-xs text-slate-600 leading-relaxed">
                                This graph flags the <strong>High-Cost Units</strong> over the last 90 days. Red bars signify critical spend levels.
                                <br><br>
                                <strong>How to read this:</strong> These units are your "Low Yield Assets". If a unit appears on the far right with a high bar, it means its repair costs are eating directly into its lifetime ROI.
                                <br><br>
                                <strong>Suggested Action:</strong> For any bar shown in Red (above ₱30,000), launch a full diagnostic. If the unit continues to break down, consider decommissioning or replacing the unit before it becomes a complete financial liability.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════════════════
         SECTION 3: FUTURE FORECAST (Predictive)
         ══════════════════════════════════════════════════════════════════════ --}}
    <div id="section-forecast" class="hidden space-y-8">

        {{-- ┌─────────────────────────────────────────────────────────────────────┐
             │  1. HERO BANNER – Hulaan ng Kita sa Susunod na Buwan               │
             └─────────────────────────────────────────────────────────────────────┘ --}}
        <div class="relative bg-gradient-to-br from-indigo-900 via-indigo-800 to-slate-900 rounded-3xl p-8 md:p-12 text-white shadow-2xl overflow-hidden">
            {{-- Decorative elements --}}
            <div class="absolute -right-16 -top-16 w-64 h-64 bg-indigo-500/20 rounded-full blur-3xl"></div>
            <div class="absolute -left-10 -bottom-10 w-48 h-48 bg-emerald-500/10 rounded-full blur-2xl"></div>
            <div class="absolute right-8 top-8 w-20 h-20 bg-white/5 rounded-full blur-xl animate-pulse"></div>

            <div class="relative z-10">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-8">
                    {{-- Left side – Title & Net Income --}}
                    <div>
                        <div class="flex items-center gap-3 mb-4">
                            <div class="p-2.5 bg-white/10 backdrop-blur-sm rounded-xl">
                                <i data-lucide="trending-up" class="w-6 h-6 text-emerald-400"></i>
                            </div>
                            <div>
                                <h2 class="text-xl md:text-2xl font-black leading-tight">Hulaan ng Kita sa Susunod na Buwan</h2>
                                <p class="text-indigo-300 text-xs font-bold">Next Month Income Prediction</p>
                            </div>
                        </div>
                        <p class="text-indigo-200/80 text-sm max-w-xl leading-relaxed mb-6">
                            Batay sa iyong nakaraang 6 na buwan na datos ng boundary, gastos, pagpapaayos, at sahod — ito ang aming hinuhulaang kita mo sa susunod na buwan.
                        </p>
                        @php
                            $lastMonthNet = collect($forecast_monthly_history)->last()['net_income'] ?? 0;
                            $diffNet = ($forecast_predicted['net_income'] ?? 0) - $lastMonthNet;
                            $trendUpNet = $diffNet >= 0;
                        @endphp
                        <div class="flex items-end gap-3 mb-2">
                            <span class="text-4xl md:text-5xl font-black text-white leading-none tracking-tight">{{ formatCurrency($forecast_predicted['net_income'] ?? 0) }}</span>
                            <div class="flex flex-col">
                                <div class="flex items-center gap-1 px-3 py-1 rounded-full text-xs font-black w-fit
                                    {{ $trendUpNet ? 'bg-emerald-500/20 text-emerald-300' : 'bg-rose-500/20 text-rose-300' }}">
                                    <i data-lucide="{{ $trendUpNet ? 'trending-up' : 'trending-down' }}" class="w-3.5 h-3.5"></i>
                                    {{ $trendUpNet ? '+' : '' }}{{ formatCurrency($diffNet) }}
                                </div>
                            </div>
                        </div>
                        <p class="text-indigo-300/70 text-[11px] font-bold mt-1">
                            Compute: ({{ formatCurrency($forecast_predicted['net_income'] ?? 0) }} Inaasahan - {{ formatCurrency($lastMonthNet) }} Huling Buwan)
                        </p>
                    </div>

                    {{-- Right side – Range & Confidence --}}
                    <div class="flex flex-col sm:flex-row lg:flex-col gap-4 lg:min-w-[260px]">
                        {{-- Best Case --}}
                        <div class="flex-1 p-5 bg-white/10 backdrop-blur-sm rounded-2xl border border-white/10">
                            <div class="flex items-center gap-2 mb-2">
                                <i data-lucide="arrow-up-circle" class="w-4 h-4 text-emerald-400"></i>
                                <span class="text-[10px] font-black text-emerald-300 uppercase tracking-widest">Pinakamataas (Best Case)</span>
                            </div>
                            <p class="text-2xl font-black text-emerald-300">{{ formatCurrency($forecast_predicted['best_case_net'] ?? 0) }}</p>
                            <p class="text-[10px] text-indigo-300/60 mt-1">Kung lahat ng unit kumita nang maayos</p>
                        </div>
                        {{-- Worst Case --}}
                        <div class="flex-1 p-5 bg-white/10 backdrop-blur-sm rounded-2xl border border-white/10">
                            <div class="flex items-center gap-2 mb-2">
                                <i data-lucide="arrow-down-circle" class="w-4 h-4 text-amber-400"></i>
                                <span class="text-[10px] font-black text-amber-300 uppercase tracking-widest">Pinakamababa (Worst Case)</span>
                            </div>
                            <p class="text-2xl font-black text-amber-300">{{ formatCurrency($forecast_predicted['worst_case_net'] ?? 0) }}</p>
                            <p class="text-[10px] text-indigo-300/60 mt-1">Kung may unexpected na gastos o shortage</p>
                        </div>
                        {{-- Confidence --}}
                        <div class="flex items-center gap-3 p-4 bg-white/5 rounded-2xl border border-white/10">
                            <i data-lucide="shield-check" class="w-5 h-5 text-indigo-300"></i>
                            <div>
                                <p class="text-[10px] font-black text-indigo-300/70 uppercase tracking-widest">Confidence Level</p>
                                <p class="text-sm font-black text-white">{{ $forecast_predicted['confidence'] ?? 'N/A' }}</p>
                            </div>
                            <span class="ml-auto px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider
                                @php
                                    $conf = $forecast_predicted['confidence'] ?? '';
                                @endphp
                                {{ $conf === 'Mataas' ? 'bg-emerald-500/20 text-emerald-300' : ($conf === 'Katamtaman' ? 'bg-amber-500/20 text-amber-300' : 'bg-rose-500/20 text-rose-300') }}
                            ">{{ $conf }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ┌─────────────────────────────────────────────────────────────────────┐
             │  2. INCOME BREAKDOWN CARDS (4 columns)                             │
             └─────────────────────────────────────────────────────────────────────┘ --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            @php
                $forecastCards = [
                    [
                        'label' => 'Inaasahang Koleksyon',
                        'sublabel' => 'Expected Collections',
                        'key' => 'boundary',
                        'icon' => 'wallet',
                        'color' => 'emerald',
                        'source' => 'Mula sa average ng nakolektang boundary ng mga taxi unit sa nakaraang 6 na buwan.',
                    ],
                    [
                        'label' => 'Inaasahang Gastos',
                        'sublabel' => 'Expected Expenses',
                        'key' => 'expenses',
                        'icon' => 'receipt',
                        'color' => 'rose',
                        'source' => 'Mula sa average ng mga office expenses (kuryente, tubig, supplies, atbp.) sa nakaraang 6 na buwan.',
                    ],
                    [
                        'label' => 'Inaasahang Pagpapaayos',
                        'sublabel' => 'Expected Repairs',
                        'key' => 'maintenance',
                        'icon' => 'wrench',
                        'color' => 'amber',
                        'source' => 'Mula sa maintenance records — average ng repair costs ng lahat ng unit sa nakaraang 6 na buwan.',
                    ],
                    [
                        'label' => 'Inaasahang Sahod',
                        'sublabel' => 'Expected Salaries',
                        'key' => 'salaries',
                        'icon' => 'users',
                        'color' => 'indigo',
                        'source' => 'Mula sa payroll records — average ng mga salary payments sa nakaraang 6 na buwan.',
                    ],
                ];
            @endphp

            @foreach($forecastCards as $card)
                @php
                    $val = $forecast_predicted['predicted_'.$card['key']] ?? 0;
                    $c = $card['color'];
                    // Determine trend by comparing predicted vs last month's actual
                    $lastMonth = collect($forecast_monthly_history)->last();
                    $lastVal = $lastMonth[$card['key']] ?? 0;
                    $trendAmt = $val - $lastVal;
                    $trendUp = $trendAmt >= 0;
                @endphp
                <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm hover:shadow-lg transition-all duration-300 group relative overflow-hidden">
                    {{-- Decorative bg --}}
                    <div class="absolute -right-6 -top-6 w-28 h-28 bg-{{ $c }}-50 rounded-full opacity-60 group-hover:scale-150 transition-transform duration-500"></div>

                    <div class="relative z-10">
                        <div class="flex items-start justify-between mb-4">
                            <div class="p-3 bg-{{ $c }}-50 rounded-2xl text-{{ $c }}-600 group-hover:scale-110 transition-transform">
                                <i data-lucide="{{ $card['icon'] }}" class="w-6 h-6"></i>
                            </div>
                            <div class="flex flex-col items-end">
                                <div class="flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-black
                                    {{ $card['key'] === 'boundary' ? ($trendUp ? 'bg-emerald-50 text-emerald-600' : 'bg-rose-50 text-rose-600') : ($trendUp ? 'bg-rose-50 text-rose-600' : 'bg-emerald-50 text-emerald-600') }}">
                                    <i data-lucide="{{ $trendUp ? 'arrow-up-right' : 'arrow-down-right' }}" class="w-3 h-3"></i>
                                    {{ $trendUp ? '+' : '' }}{{ formatCurrency($trendAmt) }}
                                </div>
                            </div>
                        </div>

                        <h3 class="text-[11px] font-black text-slate-400 uppercase tracking-widest mb-1">{{ $card['label'] }}</h3>
                        <p class="text-[9px] font-bold text-slate-400 mb-3 border-b border-slate-100 pb-2">
                            Compute: ({{ formatCurrency($val) }} Inaasahan - {{ formatCurrency($lastVal) }} Huling Buwan)
                        </p>

                        <p class="text-3xl font-black text-slate-800 mb-4">{{ formatCurrency($val) }}</p>

                        <div class="pt-4 border-t border-slate-100">
                            <div class="flex items-start gap-2">
                                <i data-lucide="info" class="w-3.5 h-3.5 text-slate-400 flex-shrink-0 mt-0.5"></i>
                                <p class="text-[10px] text-slate-500 leading-relaxed">{{ $card['source'] }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- ┌─────────────────────────────────────────────────────────────────────┐
             │  3. 6-MONTH TREND CHART + NEXT MONTH PREDICTION                    │
             └─────────────────────────────────────────────────────────────────────┘ --}}
        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="px-8 py-6 border-b border-slate-100 bg-slate-50/50">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <h3 class="text-lg font-black text-slate-800">Trend ng 6 na Buwan + Hulaan sa Susunod</h3>
                        <p class="text-xs text-slate-500">Actual net income ng nakaraang 6 na buwan at inaasahan sa susunod</p>
                    </div>
                    <div class="flex items-center gap-4 text-[10px] font-bold text-slate-500">
                        <div class="flex items-center gap-1.5">
                            <span class="w-3 h-3 rounded-sm bg-indigo-500"></span> Net Income (Actual)
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="w-3 h-3 rounded-sm bg-emerald-400 border-2 border-dashed border-emerald-600"></span> Prediction
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="w-3 h-1 bg-emerald-500 rounded"></span> Revenue
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="w-3 h-1 bg-rose-400 rounded"></span> Expenses
                        </div>
                    </div>
                </div>
            </div>
            <div class="p-8">
                <div class="h-[350px]">
                    <canvas id="forecastTrendChart"></canvas>
                </div>
            </div>
            <div class="px-8 pb-8">
                <div class="p-5 bg-indigo-50/50 rounded-2xl border border-indigo-100">
                    <div class="flex items-start gap-3">
                        <i data-lucide="info" class="w-5 h-5 text-indigo-500 flex-shrink-0 mt-0.5"></i>
                        <div>
                            <p class="text-[11px] font-black text-indigo-700 uppercase tracking-widest mb-1">Paano Basahin ang Chart</p>
                            <p class="text-xs text-slate-600 leading-relaxed">
                                Ang <strong>blue bars</strong> ay ang tunay na net income sa nakaraang 6 na buwan. Ang <strong>green striped bar</strong> sa dulo ay ang aming prediction para sa susunod na buwan.
                                Ang <strong>green line</strong> (Revenue) at <strong>red line</strong> (Total Expenses) ang nagpapakita ng overall trend. Kapag malayo ang green sa red, maganda ang margin mo.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ┌─────────────────────────────────────────────────────────────────────┐
             │  4. TOP EARNING UNITS TABLE                                        │
             └─────────────────────────────────────────────────────────────────────┘ --}}
        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="px-8 py-6 border-b border-slate-100 bg-slate-50/50 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h3 class="text-lg font-black text-slate-800">Top 10 Pinakamalaking Kita na Unit</h3>
                    <p class="text-xs text-slate-500">Mga taxi unit na may pinakamataas na inaasahang monthly profit</p>
                </div>
                <div class="flex items-center gap-2 px-4 py-2 bg-indigo-50 rounded-xl">
                    <i data-lucide="database" class="w-4 h-4 text-indigo-500"></i>
                    <span class="text-[10px] font-black text-indigo-600 uppercase tracking-widest">Data mula sa Boundary & Maintenance Records</span>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-slate-100">
                            <th class="px-8 py-4 text-left text-[10px] font-black text-slate-400 uppercase tracking-widest">#</th>
                            <th class="px-6 py-4 text-left text-[10px] font-black text-slate-400 uppercase tracking-widest">Plate Number</th>
                            <th class="px-6 py-4 text-right text-[10px] font-black text-slate-400 uppercase tracking-widest">Avg Daily Boundary</th>
                            <th class="px-6 py-4 text-right text-[10px] font-black text-slate-400 uppercase tracking-widest">Avg Daily Gastos</th>
                            <th class="px-6 py-4 text-right text-[10px] font-black text-slate-400 uppercase tracking-widest">Daily Profit</th>
                            <th class="px-6 py-4 text-right text-[10px] font-black text-slate-400 uppercase tracking-widest">Monthly Prediction</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @forelse($forecast_unit_profits ?? [] as $index => $unit)
                            @php
                                $mp = $unit['monthly_profit'] ?? 0;
                                $rowColor = $mp >= 35000 ? 'hover:bg-emerald-50/50' : ($mp >= 20000 ? 'hover:bg-amber-50/50' : 'hover:bg-rose-50/50');
                                $profitColor = $mp >= 35000 ? 'text-emerald-600' : ($mp >= 20000 ? 'text-amber-600' : 'text-rose-600');
                                $badge = $mp >= 35000 ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : ($mp >= 20000 ? 'bg-amber-50 text-amber-700 border-amber-200' : 'bg-rose-50 text-rose-700 border-rose-200');
                                $badgeText = $mp >= 35000 ? 'Mataas' : ($mp >= 20000 ? 'Katamtaman' : 'Mababa');
                            @endphp
                            <tr class="transition-colors {{ $rowColor }}">
                                <td class="px-8 py-4 text-sm font-black text-slate-300">{{ $index + 1 }}</td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 bg-slate-100 rounded-xl flex items-center justify-center">
                                            <i data-lucide="car" class="w-4 h-4 text-slate-500"></i>
                                        </div>
                                        <div>
                                            <p class="text-sm font-black text-slate-800">{{ $unit['plate'] }}</p>
                                            <span class="px-2 py-0.5 text-[8px] font-black rounded-full border {{ $badge }}">{{ $badgeText }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-right text-sm font-bold text-slate-700">{{ formatCurrency($unit['avg_daily_boundary'] ?? 0) }}</td>
                                <td class="px-6 py-4 text-right text-sm font-bold text-slate-500">{{ formatCurrency($unit['avg_daily_maint'] ?? 0) }}</td>
                                <td class="px-6 py-4 text-right text-sm font-black {{ $profitColor }}">{{ formatCurrency($unit['daily_profit'] ?? 0) }}</td>
                                <td class="px-6 py-4 text-right">
                                    <span class="text-lg font-black {{ $profitColor }}">{{ formatCurrency($mp) }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-8 py-12 text-center text-sm text-slate-400">
                                    <i data-lucide="inbox" class="w-8 h-8 mx-auto mb-3 text-slate-300"></i>
                                    <p class="font-bold">Walang available na data para sa unit profitability.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- ┌─────────────────────────────────────────────────────────────────────┐
             │  5. FINANCIAL HEALTH SCORE                                         │
             └─────────────────────────────────────────────────────────────────────┘ --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            {{-- Gauge --}}
            <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-8 flex flex-col items-center justify-center">
                <h3 class="text-[11px] font-black text-slate-400 uppercase tracking-widest mb-6">Financial Health Score</h3>
                @php
                    $healthScore = $forecast_health['score'] ?? 0;
                    $gaugeColor = $healthScore >= 80 ? '#10b981' : ($healthScore >= 60 ? '#f59e0b' : '#ef4444');
                    $gaugeLabel = $healthScore >= 80 ? 'Maganda' : ($healthScore >= 60 ? 'Katamtaman' : 'Nanganganib');
                    $gaugeBg = $healthScore >= 80 ? 'bg-emerald-50 text-emerald-700' : ($healthScore >= 60 ? 'bg-amber-50 text-amber-700' : 'bg-rose-50 text-rose-700');
                @endphp
                <div class="relative w-48 h-48 mb-6">
                    <canvas id="healthGaugeCanvas" width="192" height="192"></canvas>
                    <div class="absolute inset-0 flex flex-col items-center justify-center">
                        <span class="text-5xl font-black text-slate-800">{{ $healthScore }}</span>
                        <span class="text-xs font-bold text-slate-400">/ 100</span>
                    </div>
                </div>
                <span class="px-4 py-1.5 rounded-full text-xs font-black {{ $gaugeBg }}">{{ $gaugeLabel }}</span>
                <p class="text-[10px] text-slate-400 mt-3 text-center leading-relaxed">
                    Sinusukat base sa consistency ng revenue, trend ng expenses, at maintenance patterns.
                </p>
            </div>

            {{-- Sub-metrics --}}
            <div class="lg:col-span-2 bg-white rounded-3xl border border-slate-200 shadow-sm p-8">
                <h3 class="text-lg font-black text-slate-800 mb-2">Detalye ng Financial Health</h3>
                <p class="text-xs text-slate-500 mb-8">Mga bahagi na bumubuo sa overall health score ng iyong fleet business</p>

                <div class="space-y-6">
                    {{-- Revenue Consistency --}}
                    @php
                        $revCon = $forecast_health['revenue_consistency'] ?? 'N/A';
                        $revConColor = $revCon === 'Stable' ? 'emerald' : ($revCon === 'Volatile' ? 'rose' : 'amber');
                    @endphp
                    <div class="p-5 bg-slate-50 rounded-2xl border border-slate-100">
                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center gap-3">
                                <div class="p-2.5 bg-{{ $revConColor }}-50 rounded-xl">
                                    <i data-lucide="bar-chart-3" class="w-5 h-5 text-{{ $revConColor }}-600"></i>
                                </div>
                                <div>
                                    <h4 class="text-sm font-black text-slate-800">Revenue Consistency</h4>
                                    <p class="text-[10px] text-slate-500">Gaano ka-stable ang iyong monthly collections?</p>
                                </div>
                            </div>
                            <span class="px-3 py-1 bg-{{ $revConColor }}-50 text-{{ $revConColor }}-700 rounded-full text-[10px] font-black border border-{{ $revConColor }}-200">{{ $revCon }}</span>
                        </div>
                        <p class="text-[10px] text-slate-500 leading-relaxed pl-[52px]">Kung "Stable", ibig sabihin malapit-lapit ang koleksyon buwan-buwan. Kung "Volatile", malaki ang pagbabago ng collection amounts.</p>
                    </div>

                    {{-- Expense Trend --}}
                    @php
                        $expTrend = $forecast_health['expense_trend'] ?? 'N/A';
                        $expColor = $expTrend === 'Pababa' ? 'emerald' : ($expTrend === 'Pataas' ? 'rose' : 'amber');
                    @endphp
                    <div class="p-5 bg-slate-50 rounded-2xl border border-slate-100">
                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center gap-3">
                                <div class="p-2.5 bg-{{ $expColor }}-50 rounded-xl">
                                    <i data-lucide="{{ $expTrend === 'Pababa' ? 'trending-down' : 'trending-up' }}" class="w-5 h-5 text-{{ $expColor }}-600"></i>
                                </div>
                                <div>
                                    <h4 class="text-sm font-black text-slate-800">Expense Trend</h4>
                                    <p class="text-[10px] text-slate-500">Pataas ba o pababa ang iyong gastos?</p>
                                </div>
                            </div>
                            <span class="px-3 py-1 bg-{{ $expColor }}-50 text-{{ $expColor }}-700 rounded-full text-[10px] font-black border border-{{ $expColor }}-200">{{ $expTrend }}</span>
                        </div>
                        <p class="text-[10px] text-slate-500 leading-relaxed pl-[52px]">Kung "Pataas" ang gastos, baka kailangan mong i-review ang mga office expenses at maintenance costs para makatipid.</p>
                    </div>

                    {{-- Maintenance Trend --}}
                    @php
                        $maintTrend = $forecast_health['maintenance_trend'] ?? 'N/A';
                        $maintColor = $maintTrend === 'Pababa' ? 'emerald' : ($maintTrend === 'Pataas' ? 'rose' : 'amber');
                    @endphp
                    <div class="p-5 bg-slate-50 rounded-2xl border border-slate-100">
                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center gap-3">
                                <div class="p-2.5 bg-{{ $maintColor }}-50 rounded-xl">
                                    <i data-lucide="wrench" class="w-5 h-5 text-{{ $maintColor }}-600"></i>
                                </div>
                                <div>
                                    <h4 class="text-sm font-black text-slate-800">Maintenance Trend</h4>
                                    <p class="text-[10px] text-slate-500">Pataas ba o pababa ang repair costs?</p>
                                </div>
                            </div>
                            <span class="px-3 py-1 bg-{{ $maintColor }}-50 text-{{ $maintColor }}-700 rounded-full text-[10px] font-black border border-{{ $maintColor }}-200">{{ $maintTrend }}</span>
                        </div>
                        <p class="text-[10px] text-slate-500 leading-relaxed pl-[52px]">Kung "Pababa" — magandang senyales! Ibig sabihin bumaba ang pagpapaayos. Kung "Pataas" — check kung may lumang unit na paulit-ulit na nasasagawa.</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- ┌─────────────────────────────────────────────────────────────────────┐
             │  6. HOW WE COMPUTE – Transparent Methodology                       │
             └─────────────────────────────────────────────────────────────────────┘ --}}
        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="px-8 py-6 border-b border-slate-100 bg-slate-50/50">
                <div class="flex items-center gap-3">
                    <div class="p-2.5 bg-indigo-50 rounded-xl">
                        <i data-lucide="eye" class="w-5 h-5 text-indigo-600"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-black text-slate-800">Paano Namin Kinukuwenta Ito?</h3>
                        <p class="text-xs text-slate-500">Transparency — makikita mo dito kung saan nanggaling ang bawat numero</p>
                    </div>
                </div>
            </div>

            <div class="p-8">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- Boundary Forecast --}}
                    <div class="p-5 bg-emerald-50/50 rounded-2xl border border-emerald-100 hover:shadow-md transition-shadow">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="p-2 bg-emerald-100 rounded-xl">
                                <i data-lucide="wallet" class="w-5 h-5 text-emerald-600"></i>
                            </div>
                            <h4 class="text-sm font-black text-slate-800">Inaasahang Koleksyon (Boundary)</h4>
                        </div>
                        <p class="text-xs text-slate-600 leading-relaxed">
                            Kinukuha namin ang <strong>average ng nakolektang boundary sa nakaraang 6 na buwan</strong>. Binibigyan namin ng mas mataas na timbang (weight) ang mga kamakailang buwan dahil mas accurately nila ipinapakita ang kasalukuyang performance.
                        </p>
                        <div class="mt-3 flex items-center gap-2 text-[10px] font-bold text-emerald-600">
                            <i data-lucide="database" class="w-3 h-3"></i>
                            Source: Boundary Management System
                        </div>
                    </div>

                    {{-- Expenses Forecast --}}
                    <div class="p-5 bg-rose-50/50 rounded-2xl border border-rose-100 hover:shadow-md transition-shadow">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="p-2 bg-rose-100 rounded-xl">
                                <i data-lucide="receipt" class="w-5 h-5 text-rose-600"></i>
                            </div>
                            <h4 class="text-sm font-black text-slate-800">Inaasahang Gastos (Office Expenses)</h4>
                        </div>
                        <p class="text-xs text-slate-600 leading-relaxed">
                            Tinitignan namin ang <strong>pattern ng iyong monthly office expenses</strong> — kasama ang kuryente, tubig, supplies, at iba pa. Kinukuha ang trend para malaman kung pataas o pababa ang gastos.
                        </p>
                        <div class="mt-3 flex items-center gap-2 text-[10px] font-bold text-rose-600">
                            <i data-lucide="database" class="w-3 h-3"></i>
                            Source: Office Expenses Module
                        </div>
                    </div>

                    {{-- Maintenance Forecast --}}
                    <div class="p-5 bg-amber-50/50 rounded-2xl border border-amber-100 hover:shadow-md transition-shadow">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="p-2 bg-amber-100 rounded-xl">
                                <i data-lucide="wrench" class="w-5 h-5 text-amber-600"></i>
                            </div>
                            <h4 class="text-sm font-black text-slate-800">Inaasahang Pagpapaayos (Maintenance)</h4>
                        </div>
                        <p class="text-xs text-slate-600 leading-relaxed">
                            Batay sa <strong>maintenance records ng lahat ng unit</strong>, kinukuha namin ang average ng monthly repair costs. Kasama ang parts replacement, labor, at emergency repairs. Ini-adjust din base sa edad ng bawat unit.
                        </p>
                        <div class="mt-3 flex items-center gap-2 text-[10px] font-bold text-amber-600">
                            <i data-lucide="database" class="w-3 h-3"></i>
                            Source: Maintenance & Parts Records
                        </div>
                    </div>

                    {{-- Salary Forecast --}}
                    <div class="p-5 bg-indigo-50/50 rounded-2xl border border-indigo-100 hover:shadow-md transition-shadow">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="p-2 bg-indigo-100 rounded-xl">
                                <i data-lucide="users" class="w-5 h-5 text-indigo-600"></i>
                            </div>
                            <h4 class="text-sm font-black text-slate-800">Inaasahang Sahod (Salaries)</h4>
                        </div>
                        <p class="text-xs text-slate-600 leading-relaxed">
                            Tinitingnan ang <strong>payroll records ng nakaraang 6 na buwan</strong> — average ng lahat ng salary payments kasama ang overtime. Kung may bagong empleyado o nag-resign, naadjust ang prediction.
                        </p>
                        <div class="mt-3 flex items-center gap-2 text-[10px] font-bold text-indigo-600">
                            <i data-lucide="database" class="w-3 h-3"></i>
                            Source: Salary & Payroll Module
                        </div>
                    </div>
                </div>

                {{-- Formula summary --}}
                <div class="mt-8 p-5 bg-slate-50 rounded-2xl border border-slate-200">
                    <div class="flex items-start gap-3">
                        <i data-lucide="calculator" class="w-5 h-5 text-indigo-600 flex-shrink-0 mt-0.5"></i>
                        <div>
                            <p class="text-[11px] font-black text-indigo-700 uppercase tracking-widest mb-2">Simpleng Formula</p>
                            <p class="text-sm font-bold text-slate-700 mb-2">
                                Net Income = Boundary Collections − Office Expenses − Maintenance Costs − Salaries
                            </p>
                            <p class="text-xs text-slate-500 leading-relaxed">
                                Ang bawat component ay kinukuha gamit ang <strong>weighted moving average</strong> ng nakaraang 6 na buwan. Mas malaki ang bigat (weight) ng pinakabagong buwan. Ang Best Case at Worst Case ay ±15% ng predicted net income, adjusted base sa historical volatility.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Unit ROI Scorecard --}}
        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h3 class="text-base font-black text-slate-800 flex items-center gap-2">
                        <i data-lucide="trophy" class="w-5 h-5 text-amber-500"></i>
                        Unit ROI Scorecard
                    </h3>
                    <p class="text-[11px] text-slate-500 mt-0.5">Revenue vs maintenance cost per unit — all-time profitability</p>
                </div>
                <a href="{{ route('analytics.export.csv', ['type' => 'units']) }}"
                   class="flex items-center gap-1.5 px-3 py-1.5 bg-emerald-600 text-white text-xs font-bold rounded-lg hover:bg-emerald-700 transition-colors">
                    <i data-lucide="download" class="w-3.5 h-3.5"></i> CSV
                </a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-100">
                            <th class="text-left text-[10px] font-black text-slate-400 uppercase tracking-widest pb-3">Unit</th>
                            <th class="text-left text-[10px] font-black text-slate-400 uppercase tracking-widest pb-3">Status</th>
                            <th class="text-right text-[10px] font-black text-slate-400 uppercase tracking-widest pb-3">Revenue</th>
                            <th class="text-right text-[10px] font-black text-slate-400 uppercase tracking-widest pb-3">Maintenance</th>
                            <th class="text-right text-[10px] font-black text-slate-400 uppercase tracking-widest pb-3">Net ROI</th>
                            <th class="text-right text-[10px] font-black text-slate-400 uppercase tracking-widest pb-3">ROI %</th>
                            <th class="text-center text-[10px] font-black text-slate-400 uppercase tracking-widest pb-3">Rating</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @foreach($unit_roi as $unit)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="py-3 font-black text-slate-800">{{ $unit['plate'] }}</td>
                            <td class="py-3">
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full uppercase tracking-wider
                                    {{ $unit['status'] === 'active' ? 'bg-emerald-50 text-emerald-700' : ($unit['status'] === 'maintenance' ? 'bg-amber-50 text-amber-700' : 'bg-slate-100 text-slate-500') }}">
                                    {{ $unit['status'] }}
                                </span>
                            </td>
                            <td class="py-3 text-right font-semibold text-emerald-700">₱{{ number_format($unit['revenue']) }}</td>
                            <td class="py-3 text-right font-semibold text-rose-600">₱{{ number_format($unit['maintenance']) }}</td>
                            <td class="py-3 text-right font-black {{ $unit['net_roi'] >= 0 ? 'text-emerald-700' : 'text-rose-600' }}">
                                {{ $unit['net_roi'] >= 0 ? '+' : '' }}₱{{ number_format($unit['net_roi']) }}
                            </td>
                            <td class="py-3 text-right font-bold text-slate-700">{{ $unit['roi_pct'] }}%</td>
                            <td class="py-3 text-center">
                                <span class="text-[10px] font-black px-2.5 py-1 rounded-full uppercase tracking-wider
                                    bg-{{ $unit['rating_color'] }}-50 text-{{ $unit['rating_color'] }}-700 border border-{{ $unit['rating_color'] }}-100">
                                    {{ $unit['rating'] }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════════════════
         SECTION 4: AI STRATEGIC SUPPORT
         ══════════════════════════════════════════════════════════════════════ --}}
    <div id="section-strategy" class="hidden space-y-8 animate-in zoom-in-95 duration-500">
        {{-- AI Logic Container --}}
        <div class="bg-slate-900 rounded-[2.5rem] p-1 shadow-2xl overflow-hidden">
            <div class="bg-white rounded-[2.3rem] overflow-hidden">
                <div class="px-10 py-12 border-b border-slate-100 flex flex-col md:flex-row items-start md:items-center justify-between gap-6 bg-gradient-to-br from-indigo-50/50 to-white">
                    <div class="flex items-center gap-6">
                        <div class="w-16 h-16 bg-slate-900 rounded-3xl flex items-center justify-center shadow-xl shadow-indigo-200/50 relative">
                            <i data-lucide="brain-circuit" class="w-8 h-8 text-indigo-400"></i>
                            <div class="absolute -top-1 -right-1 w-4 h-4 bg-emerald-500 rounded-full border-4 border-white"></div>
                        </div>
                        <div>
                            <h2 class="text-2xl font-black text-slate-900 leading-tight">AI Strategic Advisor</h2>
                            <p id="dss-subtitle" class="text-sm font-bold text-slate-500 flex items-center gap-2 mt-1">
                                <span class="w-2 h-2 rounded-full bg-indigo-500 animate-pulse"></span>
                                Processing deep fleet telemetry...
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 w-full md:w-auto">
                        <div id="dss-cache-badge" class="hidden px-4 py-2 bg-slate-100 text-slate-600 rounded-xl text-[10px] font-black uppercase tracking-widest border border-slate-200">
                            📦 Using Cached Analysis
                        </div>
                        <button onclick="loadDSSInsights(true)" id="refresh-ai" class="flex-1 md:flex-none px-6 py-3 bg-slate-900 text-white rounded-xl font-black text-xs hover:bg-slate-800 transition-all flex items-center justify-center gap-2 shadow-lg shadow-slate-200">
                            <i data-lucide="sparkles" class="w-4 h-4 text-indigo-400"></i> Fresh AI Analysis
                        </button>
                    </div>
                </div>

                {{-- AI Content State: Loading --}}
                <div id="dss-loading" class="py-32 flex flex-col items-center justify-center">
                    <div class="relative w-20 h-20 mb-6">
                        <div class="absolute inset-0 border-4 border-indigo-100 rounded-full"></div>
                        <div class="absolute inset-0 border-4 border-indigo-600 rounded-full border-t-transparent animate-spin"></div>
                        <div class="absolute inset-0 flex items-center justify-center">
                            <i data-lucide="cpu" class="w-8 h-8 text-indigo-600"></i>
                        </div>
                    </div>
                    <p class="text-lg font-black text-slate-800 mb-2">Analyzing Fleet Dynamics</p>
                    <p class="text-sm text-slate-500 max-w-xs text-center leading-relaxed">Cross-referencing boundaries, maintenance logs, and expense patterns for strategic insights...</p>
                </div>

                {{-- AI Content State: Error --}}
                <div id="dss-error" class="hidden py-24 px-10 flex flex-col items-center justify-center">
                    <div class="w-16 h-16 bg-rose-50 text-rose-600 rounded-3xl flex items-center justify-center mb-6">
                        <i data-lucide="alert-circle" class="w-8 h-8"></i>
                    </div>
                    <p class="text-lg font-black text-slate-800">Analysis Interrupted</p>
                    <p id="dss-error-msg" class="text-sm text-slate-500 mt-2 mb-8">Server communication failed.</p>
                    <button onclick="loadDSSInsights(true)" class="px-8 py-3 bg-slate-900 text-white rounded-xl font-black text-xs">Retry Analysis</button>
                </div>

                {{-- AI Content State: Success --}}
                <div id="dss-insights" class="hidden p-10 animate-in fade-in zoom-in-95 duration-700">
                    <div class="grid grid-cols-1 lg:grid-cols-4 gap-10">
                        {{-- Left Column: Summary Metrics --}}
                        <div class="space-y-8">
                            <div class="p-6 bg-slate-50 rounded-3xl border border-slate-100">
                                <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4">Strategic Pulse</h4>
                                <div id="dss-stats-bar" class="space-y-4">
                                    {{-- JS Populated --}}
                                </div>
                            </div>

                            <div class="p-6 bg-indigo-50/50 rounded-3xl border border-indigo-100">
                                <h4 class="text-[10px] font-black text-indigo-600 uppercase tracking-widest mb-4">Operational Risks</h4>
                                <div id="dss-risks" class="space-y-4">
                                    {{-- JS Populated --}}
                                </div>
                            </div>
                        </div>

                        {{-- Middle Column: Recommendation Cards --}}
                        <div class="lg:col-span-3">
                             <div id="dss-cards" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                {{-- JS Populated --}}
                             </div>
                        </div>
                    </div>

                    {{-- Data Lineage (Transparency) --}}
                    <div class="mt-16 pt-10 border-t border-slate-100">
                        <div class="flex items-center gap-3 mb-8">
                            <i data-lucide="database" class="w-5 h-5 text-indigo-600"></i>
                            <h4 class="text-sm font-black text-slate-800">AI Data Lineage <span class="text-xs font-bold text-slate-400 ml-2">(How we calculated this)</span></h4>
                        </div>
                        <div id="dss-lineage" class="grid grid-cols-2 md:grid-cols-4 gap-6">
                            {{-- JS Populated --}}
                        </div>
                    </div>
                </div>

                {{-- Footer --}}
                <div class="px-10 py-6 bg-slate-50 border-t border-slate-100 flex items-center justify-between text-[10px] font-black text-slate-400 uppercase tracking-widest">
                    <div id="dss-footer">Ready for Analysis</div>
                    <div class="flex items-center gap-2">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                        Gemini 1.5 Flash Engine
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
<script>
    // ── Tab Switching Logic ──────────────────────────────────────────────────
    function switchTab(tab) {
        // Sections
        const sections = {
            pulse: document.getElementById('section-pulse'),
            performance: document.getElementById('section-performance'),
            forecast: document.getElementById('section-forecast'),
            strategy: document.getElementById('section-strategy')
        };

        // Buttons
        const buttons = {
            pulse: document.getElementById('tab-pulse'),
            performance: document.getElementById('tab-performance'),
            forecast: document.getElementById('tab-forecast'),
            strategy: document.getElementById('tab-strategy')
        };

        // Reset All
        Object.values(sections).forEach(s => s.classList.add('hidden'));
        Object.entries(buttons).forEach(([key, btn]) => {
            btn.classList.remove('bg-white', 'text-slate-900', 'shadow-sm', 'border', 'border-slate-200');
            btn.classList.add('text-slate-500', 'hover:text-slate-700');
        });

        // Activate Selected
        sections[tab].classList.remove('hidden');
        buttons[tab].classList.add('bg-white', 'text-slate-900', 'shadow-sm', 'border', 'border-slate-200');
        buttons[tab].classList.remove('text-slate-500', 'hover:text-slate-700');

        // Handle Chart Resizing if needed
        if (tab === 'performance' || tab === 'forecast') {
            window.dispatchEvent(new Event('resize'));
        }
    }

    // ── Chart Data ────────────────────────────────────────────────────────────
    const dailyData         = @json($daily_trend);
    const expenseData       = @json($expense_by_category);
    const monthlyRevenueData= @json($monthlyRevenueData);
    const maintenanceCostData = @json($maintenance_cost_trend);

    // Revenue vs Expenses Chart
    const revCtx = document.getElementById('revenueChart').getContext('2d');
    new Chart(revCtx, {
        type: 'line',
        data: {
            labels: monthlyRevenueData.map(d => d.month),
            datasets: [
                { label: 'Revenue (₱)', data: monthlyRevenueData.map(d => d.boundary), borderColor: '#10b981', backgroundColor: 'rgba(16, 185, 129, 0.1)', fill: true, tension: 0.4, borderWidth: 3, pointRadius: 4, pointBackgroundColor: '#fff' },
                { label: 'Expenses (₱)', data: monthlyRevenueData.map(d => d.expenses), borderColor: '#3b82f6', backgroundColor: 'rgba(59, 130, 246, 0.1)', fill: true, tension: 0.4, borderWidth: 3, pointRadius: 4, pointBackgroundColor: '#fff' }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { position: 'top', labels: { usePointStyle: true, font: { weight: 'bold', size: 11 } } } },
            scales: { y: { beginAtZero: true, grid: { color: '#f1f5f9' }, ticks: { callback: v => '₱' + v.toLocaleString(), font: { size: 10 } } }, x: { grid: { display: false }, ticks: { font: { size: 10 } } } }
        }
    });

    // Expense Distribution Chart
    const expCtx = document.getElementById('expenseChart').getContext('2d');
    new Chart(expCtx, {
        type: 'doughnut',
        data: {
            labels: expenseData.map(d => d.category),
            datasets: [{ data: expenseData.map(d => d.total), backgroundColor: ['#6366f1','#10b981','#f59e0b','#ef4444','#ec4899','#8b5cf6','#06b6d4','#94a3b8'], borderWidth: 0, hoverOffset: 15 }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '75%',
            plugins: { legend: { position: 'right', labels: { boxWidth: 12, padding: 20, font: { weight: 'bold', size: 11 } } } }
        }
    });

    // Daily Boundary Trend Chart
    const dailyCtx = document.getElementById('dailyTrendChart').getContext('2d');
    new Chart(dailyCtx, {
        type: 'bar',
        data: {
            labels: dailyData.map(d => d.day),
            datasets: [{ label: 'Daily Collection (₱)', data: dailyData.map(d => d.total), backgroundColor: '#6366f1', borderRadius: 8, barThickness: 12 }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: { y: { beginAtZero: true, grid: { color: '#f1f5f9' }, ticks: { callback: v => '₱' + v.toLocaleString(), font: { size: 10 } } }, x: { grid: { display: false }, ticks: { font: { size: 10 } } } }
        }
    });

    // Maintenance Cost Chart
    const maintCtx = document.getElementById('maintenanceChart').getContext('2d');
    new Chart(maintCtx, {
        type: 'bar',
        data: {
            labels: maintenanceCostData.map(d => d.unit),
            datasets: [{ label: 'Repair Cost (₱)', data: maintenanceCostData.map(d => d.cost), backgroundColor: d => d.raw > 30000 ? '#ef4444' : '#f59e0b', borderRadius: 8 }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: { y: { beginAtZero: true, grid: { color: '#f1f5f9' }, ticks: { callback: v => '₱' + v.toLocaleString(), font: { size: 10 } } }, x: { grid: { display: false }, ticks: { font: { size: 10 } } } }
        }
    });

    // ── AI DSS Logic ──────────────────────────────────────────────────────────
    const priorityConfig = {
        critical: { bg: 'bg-rose-50', border: 'border-rose-200', badge: 'bg-rose-600 text-white', label: 'CRITICAL' },
        high: { bg: 'bg-amber-50', border: 'border-amber-200', badge: 'bg-amber-500 text-white', label: 'HIGH' },
        medium: { bg: 'bg-indigo-50', border: 'border-indigo-200', badge: 'bg-indigo-500 text-white', label: 'MEDIUM' },
        low: { bg: 'bg-slate-50', border: 'border-slate-200', badge: 'bg-slate-500 text-white', label: 'LOW' },
    };

    const categoryColors = {
        fleet: 'text-indigo-700 bg-indigo-50',
        finance: 'text-emerald-700 bg-emerald-50',
        drivers: 'text-blue-700 bg-blue-50',
        maintenance: 'text-orange-700 bg-orange-50',
        operations: 'text-purple-700 bg-purple-50',
    };

    function renderInsightCard(insight) {
        const p = priorityConfig[insight.priority] || priorityConfig.medium;
        const cc = categoryColors[insight.category] || 'text-gray-700 bg-gray-100';
        const actions = (insight.actions || []).map(a => `<li class="flex items-start gap-2 text-slate-600 text-[11px] font-bold"><span class="text-indigo-500 mt-0.5">●</span> ${a}</li>`).join('');

        return `
            <div class="rounded-3xl border-2 ${p.border} ${p.bg} p-6 transition-all hover:shadow-xl hover:-translate-y-1 duration-300">
                <div class="flex items-start justify-between mb-4">
                    <div class="flex items-center gap-3">
                        <span class="text-3xl">${insight.icon || '💡'}</span>
                        <div>
                            <p class="font-black text-slate-800 text-sm">${insight.title}</p>
                            <span class="px-2 py-0.5 text-[8px] font-black rounded-full uppercase tracking-widest ${p.badge}">${p.label}</span>
                        </div>
                    </div>
                </div>
                <p class="text-slate-600 text-xs leading-relaxed mb-6 font-medium">${insight.insight}</p>
                <div class="mb-6 p-4 bg-white/60 rounded-2xl border border-white">
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Deep Reasoning</p>
                    <p class="text-[11px] text-slate-600 leading-relaxed font-semibold">${insight.reasoning}</p>
                </div>
                <div class="space-y-4">
                    <p class="text-[10px] font-black text-indigo-600 uppercase tracking-widest">Recommended Actions</p>
                    <ul class="space-y-2">${actions}</ul>
                </div>
            </div>`;
    }

    function renderStatsBar(data) {
        const s = data.snapshot || {};
        const items = [
            { label: 'Fleet Efficiency', value: (s.fleet_utilization || 0) + '%', icon: '🚕', color: 'text-indigo-600' },
            { label: 'Period Net Profit', value: '₱' + Number(s.latest_net || 0).toLocaleString(), icon: '💰', color: 'text-emerald-600' },
            { label: 'Uncollected Leakage', value: '₱' + Number(s.total_shortage || 0).toLocaleString(), icon: '⚠️', color: 'text-rose-600' },
        ];
        return items.map(i => `
            <div class="flex items-center gap-4 px-4 py-3 bg-white rounded-2xl border border-slate-100 shadow-sm transition-all hover:scale-105">
                <span class="text-xl">${i.icon}</span>
                <div>
                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">${i.label}</p>
                    <p class="text-sm font-black ${i.color}">${i.value}</p>
                </div>
            </div>`).join('');
    }

    function renderForecastPanel(f) {
        if (!f) return '';
        const items = [
            { label: 'Revenue Projection', value: '₱' + Number(f.predicted_revenue).toLocaleString(), color: 'text-emerald-600', bg: 'bg-emerald-50', icon: 'trending-up', desc: 'Anticipated gross boundary collections for the next 30 days.' },
            { label: 'Operational Expenses', value: '₱' + Number(f.predicted_expenses).toLocaleString(), color: 'text-slate-700', bg: 'bg-slate-50', icon: 'receipt', desc: 'Estimated fixed and variable overhead costs.' },
            { label: 'Maintenance Reserve', value: '₱' + Number(f.predicted_maintenance).toLocaleString(), color: 'text-orange-600', bg: 'bg-orange-50', icon: 'wrench', desc: 'Projected repair requirements. Keep this cash ready.' },
            { label: 'Target Growth', value: f.growth_rate_pct + '%', color: 'text-indigo-600', bg: 'bg-indigo-50', icon: 'activity', desc: 'Anticipated momentum shift compared to previous month.' },
        ];
        return items.map(i => `
            <div class="bg-white p-6 rounded-[2rem] border border-slate-200 shadow-sm hover:shadow-md transition-shadow relative overflow-hidden group">
                <div class="absolute -right-4 -top-4 w-24 h-24 ${i.bg} rounded-full group-hover:scale-150 transition-transform duration-500 ease-out z-0"></div>
                <div class="relative z-10">
                    <div class="flex items-center justify-between mb-4">
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">${i.label}</p>
                    </div>
                    <p class="text-3xl font-black ${i.color} mb-3">${i.value}</p>
                    <p class="text-[11px] font-semibold text-slate-500 leading-relaxed mb-6">${i.desc}</p>
                    <div class="pt-4 border-t border-slate-100 flex items-center justify-between">
                        <span class="text-[9px] font-black text-slate-400 uppercase">Forecast Reliability</span>
                        <span class="px-2 py-0.5 bg-indigo-50 text-indigo-600 text-[9px] font-black rounded-full uppercase border border-indigo-100 shadow-sm">${f.confidence_level}</span>
                    </div>
                </div>
            </div>`).join('');
    }

    async function loadDSSInsights(force = false) {
        const loading = document.getElementById('dss-loading');
        const content = document.getElementById('dss-insights');
        const error   = document.getElementById('dss-error');
        const refreshBtn = document.getElementById('refresh-ai');
        
        loading.classList.remove('hidden');
        content.classList.add('hidden');
        error.classList.add('hidden');
        refreshBtn.classList.add('opacity-50', 'pointer-events-none');

        try {
            const url = '{{ route("analytics.ai-insights") }}' + (force ? '?refresh=1' : '');
            const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
            if (!res.ok) throw new Error('Server returned ' + res.status);
            const data = await res.json();

            document.getElementById('dss-stats-bar').innerHTML = renderStatsBar(data);
            const fpEl = document.getElementById('dss-forecast-panel');
            if (fpEl) fpEl.innerHTML = renderForecastPanel(data.forecast);
            document.getElementById('dss-risks').innerHTML = (data.risks || []).map(r => `<div class="flex items-start gap-3 p-3 bg-white rounded-xl border border-slate-100 text-[11px] font-bold text-slate-600 shadow-sm"><span class="w-1.5 h-1.5 rounded-full bg-rose-500 mt-1.5 shrink-0"></span> ${r}</div>`).join('');
            document.getElementById('dss-cards').innerHTML = (data.insights || []).map(renderInsightCard).join('');
            document.getElementById('dss-lineage').innerHTML = Object.entries(data.lineage || {}).map(([key, info]) => `<div class="p-4 bg-slate-50 rounded-2xl border border-slate-100"><p class="text-[10px] font-black text-slate-800 uppercase mb-2">${key}</p><p class="text-[9px] text-slate-500 leading-tight">${info.Description}</p></div>`).join('');

            const genAt = data.generated_at ? new Date(data.generated_at).toLocaleTimeString() : '';
            document.getElementById('dss-footer').textContent = `AI Strategy Generated at ${genAt} • ${data.fallback ? 'Heuristic Mode' : 'Gemini Engine'}`;
            
            const cacheBadge = document.getElementById('dss-cache-badge');
            if (data.from_cache) cacheBadge.classList.remove('hidden'); else cacheBadge.classList.add('hidden');

            document.getElementById('dss-subtitle').textContent = (data.insights || []).length + ' Strategic Actions Identified';
            loading.classList.add('hidden');
            content.classList.remove('hidden');
        } catch (err) {
            loading.classList.add('hidden');
            error.classList.remove('hidden');
            document.getElementById('dss-error-msg').textContent = err.message;
        } finally {
            refreshBtn.classList.remove('opacity-50', 'pointer-events-none');
            if (window.lucide) lucide.createIcons();
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        loadDSSInsights(false);
    });

    // ── Forecast Section: Trend Chart & Health Gauge ──────────────────────────
    (function initForecastCharts() {
        // Data from PHP
        const fHistory = @json($forecast_monthly_history ?? []);
        const fPredicted = @json($forecast_predicted ?? []);
        const fHealth = @json($forecast_health ?? []);

        // 1. Build forecast trend chart
        const fCtx = document.getElementById('forecastTrendChart');
        if (fCtx) {
            const histLabels = fHistory.map(h => h.month);
            const histNet = fHistory.map(h => h.net);
            const histBoundary = fHistory.map(h => h.boundary);
            const histTotalExp = fHistory.map(h => (h.expenses || 0) + (h.maintenance || 0) + (h.salaries || 0));

            // Add predicted month
            const allLabels = [...histLabels, 'Susunod'];
            const allNet = [...histNet, fPredicted.net || 0];
            const allBoundary = [...histBoundary, fPredicted.boundary || 0];
            const predTotalExp = (fPredicted.expenses || 0) + (fPredicted.maintenance || 0) + (fPredicted.salaries || 0);
            const allTotalExp = [...histTotalExp, predTotalExp];

            // Background colors for the bar (actual = indigo, predicted = emerald with pattern)
            const barBg = histNet.map(() => '#6366f1').concat(['#34d399']);
            const barBorder = histNet.map(() => '#6366f1').concat(['#059669']);
            const barBorderWidth = histNet.map(() => 0).concat([3]);
            const barBorderDash = histNet.map(() => []).concat([[6, 4]]);

            new Chart(fCtx.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: allLabels,
                    datasets: [
                        {
                            type: 'bar',
                            label: 'Net Income (₱)',
                            data: allNet,
                            backgroundColor: barBg,
                            borderColor: barBorder,
                            borderWidth: barBorderWidth.map(w => w),
                            borderRadius: 10,
                            barPercentage: 0.6,
                            order: 2
                        },
                        {
                            type: 'line',
                            label: 'Revenue (₱)',
                            data: allBoundary,
                            borderColor: '#10b981',
                            backgroundColor: 'rgba(16, 185, 129, 0.08)',
                            fill: true,
                            tension: 0.4,
                            borderWidth: 2.5,
                            pointRadius: 4,
                            pointBackgroundColor: '#fff',
                            pointBorderColor: '#10b981',
                            pointBorderWidth: 2,
                            order: 1
                        },
                        {
                            type: 'line',
                            label: 'Total Expenses (₱)',
                            data: allTotalExp,
                            borderColor: '#f43f5e',
                            backgroundColor: 'rgba(244, 63, 94, 0.05)',
                            fill: true,
                            tension: 0.4,
                            borderWidth: 2,
                            borderDash: [5, 3],
                            pointRadius: 3,
                            pointBackgroundColor: '#fff',
                            pointBorderColor: '#f43f5e',
                            pointBorderWidth: 2,
                            order: 0
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: { mode: 'index', intersect: false },
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top',
                            labels: { usePointStyle: true, font: { weight: 'bold', size: 11 }, padding: 20 }
                        },
                        tooltip: {
                            backgroundColor: '#1e293b',
                            titleFont: { weight: 'bold', size: 12 },
                            bodyFont: { size: 11 },
                            padding: 12,
                            cornerRadius: 12,
                            callbacks: {
                                label: ctx => {
                                    let label = ctx.dataset.label || '';
                                    return label + ': ₱' + Number(ctx.parsed.y).toLocaleString();
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: { color: '#f1f5f9' },
                            ticks: { callback: v => '₱' + Number(v).toLocaleString(), font: { size: 10 } }
                        },
                        x: {
                            grid: { display: false },
                            ticks: {
                                font: function(ctx) {
                                    return { size: 11, weight: ctx.index === allLabels.length - 1 ? 'bold' : 'normal' };
                                },
                                color: function(ctx) {
                                    return ctx.index === allLabels.length - 1 ? '#059669' : '#64748b';
                                }
                            }
                        }
                    }
                }
            });
        }

        // 2. Health Gauge (Canvas arc)
        const gaugeCanvas = document.getElementById('healthGaugeCanvas');
        if (gaugeCanvas) {
            const gCtx = gaugeCanvas.getContext('2d');
            const score = fHealth.score || 0;
            const centerX = gaugeCanvas.width / 2;
            const centerY = gaugeCanvas.height / 2;
            const radius = 80;
            const lineWidth = 14;
            const startAngle = 0.75 * Math.PI;  // 135 degrees
            const endAngle = 2.25 * Math.PI;    // 405 degrees
            const totalArc = endAngle - startAngle;
            const scoreAngle = startAngle + (totalArc * (score / 100));

            // Determine color
            let gaugeColor = '#ef4444'; // red
            if (score >= 80) gaugeColor = '#10b981'; // green
            else if (score >= 60) gaugeColor = '#f59e0b'; // amber

            // Background track
            gCtx.beginPath();
            gCtx.arc(centerX, centerY, radius, startAngle, endAngle);
            gCtx.strokeStyle = '#f1f5f9';
            gCtx.lineWidth = lineWidth;
            gCtx.lineCap = 'round';
            gCtx.stroke();

            // Animated score arc
            let currentAngle = startAngle;
            const animationDuration = 1200;
            const startTime = performance.now();

            function animateGauge(currentTime) {
                const elapsed = currentTime - startTime;
                const progress = Math.min(elapsed / animationDuration, 1);
                const eased = 1 - Math.pow(1 - progress, 3); // ease-out cubic
                currentAngle = startAngle + (scoreAngle - startAngle) * eased;

                // Clear and redraw
                gCtx.clearRect(0, 0, gaugeCanvas.width, gaugeCanvas.height);

                // Background track
                gCtx.beginPath();
                gCtx.arc(centerX, centerY, radius, startAngle, endAngle);
                gCtx.strokeStyle = '#f1f5f9';
                gCtx.lineWidth = lineWidth;
                gCtx.lineCap = 'round';
                gCtx.stroke();

                // Score arc with gradient
                if (currentAngle > startAngle) {
                    const gradient = gCtx.createLinearGradient(0, 0, gaugeCanvas.width, gaugeCanvas.height);
                    gradient.addColorStop(0, gaugeColor + 'aa');
                    gradient.addColorStop(1, gaugeColor);

                    gCtx.beginPath();
                    gCtx.arc(centerX, centerY, radius, startAngle, currentAngle);
                    gCtx.strokeStyle = gradient;
                    gCtx.lineWidth = lineWidth;
                    gCtx.lineCap = 'round';
                    gCtx.stroke();
                }

                // Small dot at the end
                if (progress < 1) {
                    requestAnimationFrame(animateGauge);
                } else {
                    // Draw endpoint dot
                    const dotX = centerX + radius * Math.cos(scoreAngle);
                    const dotY = centerY + radius * Math.sin(scoreAngle);
                    gCtx.beginPath();
                    gCtx.arc(dotX, dotY, lineWidth / 2 + 2, 0, 2 * Math.PI);
                    gCtx.fillStyle = gaugeColor;
                    gCtx.fill();
                    gCtx.beginPath();
                    gCtx.arc(dotX, dotY, 4, 0, 2 * Math.PI);
                    gCtx.fillStyle = '#ffffff';
                    gCtx.fill();
                }
            }

            // Observe when section-forecast becomes visible
            const forecastSection = document.getElementById('section-forecast');
            let gaugeAnimated = false;
            const observer = new MutationObserver(() => {
                if (!forecastSection.classList.contains('hidden') && !gaugeAnimated) {
                    gaugeAnimated = true;
                    requestAnimationFrame(animateGauge);
                }
            });
            observer.observe(forecastSection, { attributes: true, attributeFilter: ['class'] });

            // Also trigger if already visible
            if (!forecastSection.classList.contains('hidden')) {
                gaugeAnimated = true;
                requestAnimationFrame(animateGauge);
            }
        }
    })();

// Revenue Heatmap Calendar
(function() {
    const heatmapData = @json($heatmap_data);
    const grid = document.getElementById('heatmapGrid');
    if (!grid) return;

    // Get max value for color scaling
    const maxVal = Math.max(...heatmapData.map(d => d.total), 1);

    // Group by week
    const weeks = {};
    heatmapData.forEach(d => {
        const weekKey = d.date.substring(0, 8) + (Math.floor(parseInt(d.date.substring(8)) / 7) * 7).toString().padStart(2,'0');
        const iso = d.date;
        const dow = new Date(iso).getDay(); // 0=Sun
        const adjustedDow = dow === 0 ? 6 : dow - 1; // Mon=0
        if (!weeks[weekKey]) weeks[weekKey] = { days: Array(7).fill(null), month: d.month };
        weeks[weekKey].days[adjustedDow] = d;
    });

    function getColor(total) {
        if (total === 0) return 'bg-slate-100';
        const pct = total / maxVal;
        if (pct < 0.25) return 'bg-amber-200';
        if (pct < 0.5)  return 'bg-amber-400';
        if (pct < 0.75) return 'bg-emerald-400';
        return 'bg-emerald-600';
    }

    // Day labels column
    const dayLabels = ['Mon','Tue','Wed','Thu','Fri','Sat','Sun'];
    const labelCol = document.createElement('div');
    labelCol.className = 'flex flex-col gap-1 pt-5';
    labelCol.innerHTML = dayLabels.map(d =>
        `<div class="w-6 h-3 text-[9px] text-slate-400 font-bold flex items-center">${d}</div>`
    ).join('');
    grid.appendChild(labelCol);

    // Week columns
    const weekKeys = Object.keys(weeks).sort();
    let lastMonth = '';
    weekKeys.forEach(wk => {
        const w = weeks[wk];
        const col = document.createElement('div');
        col.className = 'flex flex-col gap-1';

        // Month label
        const monthLabel = document.createElement('div');
        monthLabel.className = 'h-4 text-[9px] text-slate-400 font-bold mb-0.5';
        const firstDay = w.days.find(d => d !== null);
        if (firstDay && firstDay.month !== lastMonth) {
            monthLabel.textContent = firstDay.month;
            lastMonth = firstDay.month;
        }
        col.appendChild(monthLabel);

        // Day cells
        w.days.forEach(d => {
            const cell = document.createElement('div');
            cell.className = `w-3 h-3 rounded-sm transition-all hover:scale-125 cursor-pointer ${d ? getColor(d.total) : 'bg-transparent'}`;
            if (d && d.total > 0) {
                cell.title = `${d.date}: \u20b1${d.total.toLocaleString()}`;
            }
            col.appendChild(cell);
        });
        grid.appendChild(col);
    });
})();

// Driver Utilization Chart
(function() {
    const utilData = @json($driver_utilization);
    const ctx = document.getElementById('driverUtilizationChart');
    if (!ctx || !utilData.length) return;

    const colors = utilData.map(d =>
        d.category === 'high' ? 'rgba(16,185,129,0.85)' :
        d.category === 'medium' ? 'rgba(251,191,36,0.85)' :
        'rgba(251,113,133,0.85)'
    );

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: utilData.map(d => d.name.split(' ')[0]), // First name only
            datasets: [{
                label: 'Days Worked',
                data: utilData.map(d => d.days_worked),
                backgroundColor: colors,
                borderRadius: 8,
                borderSkipped: false,
            }, {
                label: 'Days Idle',
                data: utilData.map(d => d.days_idle),
                backgroundColor: 'rgba(203,213,225,0.5)',
                borderRadius: 8,
                borderSkipped: false,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom', labels: { font: { size: 11, weight: 'bold' } } },
                tooltip: {
                    callbacks: {
                        afterLabel: (ctx) => {
                            const d = utilData[ctx.dataIndex];
                            return `Utilization: ${d.utilization}% (${d.category_label})`;
                        }
                    }
                }
            },
            scales: {
                x: { stacked: true, grid: { display: false }, ticks: { font: { size: 10, weight: 'bold' } } },
                y: { stacked: true, max: 30, grid: { color: 'rgba(148,163,184,0.1)' }, ticks: { stepSize: 5 } }
            }
        }
    });
})();
</script>
@endpush