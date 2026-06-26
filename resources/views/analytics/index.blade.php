@extends('layouts.app')

@section('title', 'AI Fleet Intelligence - Euro System')
@section('page-heading', 'Advanced Fleet Analytics')
@section('page-subheading', 'Real-time Pulse • Historical Trends • Predictive Forecasting')

@section('content')
    {{-- ── Analytics Command Bar ────────────────────────────────────────────── --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-4 mb-6">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            {{-- Quick Title / Info --}}
            <div class="flex items-center gap-3">
                <div class="p-2.5 bg-indigo-50 text-indigo-600 rounded-xl">
                    <i data-lucide="line-chart" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="text-sm font-black text-slate-800 uppercase tracking-wider">Fleet Performance Intelligence</h3>
                    <p class="text-[10px] text-slate-500 font-bold">Dynamic baseline analytics — updated in real time</p>
                </div>
            </div>

            {{-- Action Buttons --}}
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('analytics.history') }}"
                   class="flex items-center gap-1.5 px-4 py-2.5 bg-indigo-600 text-white text-xs font-black rounded-xl hover:bg-indigo-700 shadow-md shadow-indigo-200/50 transition-all">
                    <i data-lucide="history" class="w-4 h-4"></i> Daily History Ledger
                </a>
                <span class="w-px h-6 bg-slate-200 mx-1 hidden lg:block"></span>
                <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest hidden sm:block">Quick Export:</span>
                <a href="{{ route('analytics.export.csv', ['type' => 'revenue', 'date_from' => $date_from, 'date_to' => $date_to]) }}"
                   class="flex items-center gap-1.5 px-3 py-2 bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs font-bold rounded-xl hover:bg-emerald-100 transition-all">
                    <i data-lucide="download" class="w-3 h-3"></i> Revenue
                </a>
                <a href="{{ route('analytics.export.csv', ['type' => 'drivers', 'date_from' => $date_from, 'date_to' => $date_to]) }}"
                   class="flex items-center gap-1.5 px-3 py-2 bg-blue-50 border border-blue-200 text-blue-700 text-xs font-bold rounded-xl hover:bg-blue-100 transition-all">
                    <i data-lucide="download" class="w-3 h-3"></i> Drivers
                </a>
                <a href="{{ route('analytics.export.csv', ['type' => 'maintenance', 'date_from' => $date_from, 'date_to' => $date_to]) }}"
                   class="flex items-center gap-1.5 px-3 py-2 bg-amber-50 border border-amber-200 text-amber-700 text-xs font-bold rounded-xl hover:bg-amber-100 transition-all">
                    <i data-lucide="download" class="w-3 h-3"></i> Maintenance
                </a>
            </div>
        </div>
    </div>

    {{-- ── Advanced Navigation Tabs ─────────────────────────────────────────── --}}
    <div class="flex flex-wrap items-center gap-1.5 p-1.5 bg-slate-900/5 backdrop-blur-md rounded-2xl mb-8 w-fit mx-auto shadow-inner border border-slate-200/60">
        <button onclick="switchTab('pulse')" id="tab-pulse"
            class="tab-btn px-5 py-2.5 rounded-xl text-xs font-black transition-all duration-200 flex items-center gap-2 bg-white text-indigo-700 shadow-sm border border-indigo-100 ring-1 ring-indigo-500/20">
            <i data-lucide="activity" class="w-4 h-4 text-indigo-500"></i>
            <span>Real-time Pulse</span>
        </button>
        <button onclick="switchTab('performance')" id="tab-performance"
            class="tab-btn px-5 py-2.5 rounded-xl text-xs font-black transition-all duration-200 flex items-center gap-2 text-slate-500 hover:bg-white hover:text-slate-800 hover:shadow-sm">
            <i data-lucide="bar-chart-3" class="w-4 h-4"></i>
            <span>Descriptive Analytics</span>
        </button>
        <button onclick="switchTab('forecast')" id="tab-forecast"
            class="tab-btn px-5 py-2.5 rounded-xl text-xs font-black transition-all duration-200 flex items-center gap-2 text-slate-500 hover:bg-white hover:text-slate-800 hover:shadow-sm">
            <i data-lucide="trending-up" class="w-4 h-4"></i>
            <span>Predictive Forecasting</span>
        </button>
        <button onclick="switchTab('strategy')" id="tab-strategy"
            class="tab-btn px-5 py-2.5 rounded-xl text-xs font-black transition-all duration-200 flex items-center gap-2 text-slate-500 hover:bg-white hover:text-slate-800 hover:shadow-sm">
            <i data-lucide="brain-circuit" class="w-4 h-4"></i>
            <span>AI Strategic Insights</span>
        </button>
    </div>

    {{-- ══════════════════════════════════════════════════════════════════════
         SECTION 1: OPERATIONAL PULSE (Real-time)
         ══════════════════════════════════════════════════════════════════════ --}}
    <div id="section-pulse" class="space-y-8">
        {{-- High Level Pulse Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            {{-- Fleet Health --}}
            <div class="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-sm hover:shadow-xl hover:border-indigo-200 hover:-translate-y-1 transition-all duration-300 group">
                <div class="flex items-start justify-between mb-4">
                    <div class="p-3 bg-indigo-50 rounded-2xl text-indigo-600 group-hover:scale-110 group-hover:shadow-md transition-all duration-300">
                        <i data-lucide="car" class="w-6 h-6"></i>
                    </div>
                    <span class="text-[10px] font-black text-indigo-600 bg-indigo-50 px-2.5 py-1 rounded-full uppercase tracking-widest">Real-time</span>
                </div>
                <h3 class="text-[11px] font-black text-slate-400 uppercase tracking-widest mb-1">Fleet Utilization</h3>
                <div class="flex items-end gap-2 mb-2">
                    <span class="text-3xl font-black text-slate-800 leading-none">{{ $fleet_utilization }}%</span>
                    <span class="text-xs font-bold text-slate-500 pb-0.5">Active Now</span>
                </div>
                <div class="h-2 bg-slate-100 rounded-full overflow-hidden p-0.5 border border-slate-200/30">
                    <div class="h-full bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500 rounded-full transition-all duration-1000 shadow-[0_0_8px_rgba(99,102,241,0.5)]" style="width: {{ $fleet_utilization }}%"></div>
                </div>
                <p class="text-[10px] text-slate-500 mt-3 leading-relaxed">
                    Percentage of units currently generating revenue versus idle or in maintenance.
                </p>
            </div>

            {{-- Financial Pulse --}}
            <div class="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-sm hover:shadow-xl hover:border-emerald-200 hover:-translate-y-1 transition-all duration-300 group">
                <div class="flex items-start justify-between mb-4">
                    <div class="p-3 bg-emerald-50 rounded-2xl text-emerald-600 group-hover:scale-110 group-hover:shadow-md transition-all duration-300">
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
            <div class="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-sm hover:shadow-xl hover:border-rose-200 hover:-translate-y-1 transition-all duration-300 group">
                <div class="flex items-start justify-between mb-4">
                    <div class="p-3 bg-rose-50 rounded-2xl text-rose-600 group-hover:scale-110 group-hover:shadow-md transition-all duration-300">
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
            <div class="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-sm hover:shadow-xl hover:border-amber-200 hover:-translate-y-1 transition-all duration-300 group">
                <div class="flex items-start justify-between mb-4">
                    <div class="p-3 bg-amber-50 rounded-2xl text-amber-600 group-hover:scale-110 group-hover:shadow-md transition-all duration-300">
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
                    @php 
                        $total = array_sum($fleet_pulse); 
                        $actPct = $total > 0 ? ($fleet_pulse['active_units'] / $total) * 100 : 0;
                        $idlPct = $total > 0 ? ($fleet_pulse['idle_units'] / $total) * 100 : 0;
                        $mntPct = $total > 0 ? ($fleet_pulse['maintenance'] / $total) * 100 : 0;
                        $surPct = $total > 0 ? ($fleet_pulse['surveillance'] / $total) * 100 : 0;
                    @endphp
                    <div class="flex h-10 w-full rounded-3xl overflow-hidden shadow-inner border border-slate-200/40 p-1 bg-slate-50 gap-1 mb-8">
                        @if($actPct > 0)
                            <div class="h-full bg-gradient-to-r from-emerald-400 to-emerald-500 rounded-l-2xl transition-all hover:scale-[1.01] hover:brightness-105 cursor-help" style="width: {{ $actPct }}%" title="Active: {{ $fleet_pulse['active_units'] }}"></div>
                        @endif
                        @if($idlPct > 0)
                            <div class="h-full bg-gradient-to-r from-slate-300 to-slate-400 transition-all hover:scale-[1.01] hover:brightness-105 cursor-help" style="width: {{ $idlPct }}%" title="Idle: {{ $fleet_pulse['idle_units'] }}"></div>
                        @endif
                        @if($mntPct > 0)
                            <div class="h-full bg-gradient-to-r from-amber-400 to-amber-500 transition-all hover:scale-[1.01] hover:brightness-105 cursor-help" style="width: {{ $mntPct }}%" title="Maintenance: {{ $fleet_pulse['maintenance'] }}"></div>
                        @endif
                        @if($surPct > 0)
                            <div class="h-full bg-gradient-to-r from-rose-400 to-rose-500 rounded-r-2xl transition-all hover:scale-[1.01] hover:brightness-105 cursor-help" style="width: {{ $surPct }}%" title="Surveillance: {{ $fleet_pulse['surveillance'] }}"></div>
                        @endif
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
            <div class="bg-gradient-to-br from-indigo-900 via-indigo-950 to-slate-900 rounded-3xl p-8 text-white relative overflow-hidden group shadow-xl shadow-indigo-950/40 border border-indigo-800/30">
                <div class="relative z-10">
                    <h3 class="text-xl font-black mb-4 flex items-center gap-2">
                        <i data-lucide="activity" class="w-5 h-5 text-indigo-400 animate-pulse"></i> Pulse Analysis
                    </h3>
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
                    <button onclick="switchTab('strategy')" class="mt-8 w-full py-3 bg-gradient-to-r from-emerald-500 to-teal-600 text-white rounded-xl font-black text-sm hover:from-emerald-400 hover:to-teal-500 hover:scale-[1.02] active:scale-[0.98] transition-all flex items-center justify-center gap-2 shadow-lg shadow-emerald-900/30 border border-emerald-400/20">
                        View AI Strategy <i data-lucide="arrow-right" class="w-4 h-4 text-emerald-300"></i>
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
    <div id="section-performance" class="hidden space-y-8">

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
        <div class="relative z-20 bg-gradient-to-br from-indigo-900 via-indigo-800 to-slate-900 rounded-3xl p-8 md:p-12 text-white shadow-2xl">
            {{-- Decorative elements --}}
            <div class="absolute inset-0 overflow-hidden rounded-3xl pointer-events-none">
                <div class="absolute -right-16 -top-16 w-64 h-64 bg-indigo-500/20 rounded-full blur-3xl"></div>
                <div class="absolute -left-10 -bottom-10 w-48 h-48 bg-emerald-500/10 rounded-full blur-2xl"></div>
                <div class="absolute right-8 top-8 w-20 h-20 bg-white/5 rounded-full blur-xl animate-pulse"></div>
            </div>

            <div class="relative z-10">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-8">
                    {{-- Left side – Title & Net Income --}}
                    <div>
                        <div class="flex items-center gap-3 mb-4">
                            <div class="p-2.5 bg-white/10 backdrop-blur-sm rounded-xl">
                                <i data-lucide="trending-up" class="w-6 h-6 text-emerald-400"></i>
                            </div>
                            <div>
                                <h2 class="text-xl md:text-2xl font-black leading-tight">Next Month Income Prediction</h2>
                                <p class="text-indigo-300 text-xs font-bold">Predictive Analytics Model</p>
                            </div>
                        </div>
                        <p class="text-indigo-200/80 text-sm max-w-xl leading-relaxed mb-6">
                            Based on your past 6 months of boundary collections, expenses, maintenance, and salaries — this is your predicted net income for next month.
                        </p>
                        @php
                            $lastMonthNet = collect($forecast_monthly_history)->last()['net_income'] ?? 0;
                            $diffNet = ($forecast_predicted['net_income'] ?? 0) - $lastMonthNet;
                            $trendUpNet = $diffNet >= 0;
                        @endphp
                        <div class="flex items-end gap-3 mb-2 relative z-30 w-fit">
                            <span class="text-4xl md:text-5xl font-black text-white leading-none tracking-tight cursor-help border-b-2 border-dashed border-indigo-400/50 pb-1" id="forecast-net-income-trigger" onclick="toggleForecastPopover(event)" onmouseenter="showForecastPopover(event)" onmouseleave="scheduleForecastPopoverHide()">
                                {{ formatCurrency($forecast_predicted['net_income'] ?? 0) }}
                            </span>
                            
                            {{-- DETAILED COMPUTATION POPOVER - JS Smart Positioning --}}
                            <div id="forecast-computation-popover" class="fixed z-[9999] w-[360px] bg-white rounded-2xl shadow-2xl border border-slate-200 p-6 text-slate-800 opacity-0 invisible transition-all duration-200 pointer-events-none" onmouseenter="cancelForecastPopoverHide()" onmouseleave="scheduleForecastPopoverHide()">
                                {{-- Arrow indicator --}}
                                <div id="forecast-popover-arrow" class="absolute w-4 h-4 bg-white rotate-45 border-slate-200"></div>
                                <div class="flex items-center gap-2 mb-4 pb-3 border-b border-slate-100 relative z-10">
                                    <div class="w-8 h-8 bg-indigo-50 rounded-xl flex items-center justify-center shrink-0">
                                        <i data-lucide="calculator" class="w-4 h-4 text-indigo-600"></i>
                                    </div>
                                    <div>
                                        <h4 class="text-sm font-black text-slate-800 leading-tight">Detailed Computation</h4>
                                        <p class="text-[10px] text-indigo-500 font-bold">Next Month Income Forecast Breakdown</p>
                                    </div>
                                </div>
                                
                                <p class="text-[10px] text-slate-500 mb-4 leading-relaxed font-medium bg-indigo-50 p-3 rounded-xl border border-indigo-100">
                                    📊 Calculated using a <strong class="text-indigo-700">Weighted Moving Average</strong> of the past <strong class="text-indigo-700">6 months</strong>. More recent months have a higher weight.
                                </p>
                                
                                {{-- Revenue Line --}}
                                <div class="space-y-2.5 mb-4">
                                    <div class="flex justify-between items-center p-2.5 bg-emerald-50 rounded-xl border border-emerald-100">
                                        <div class="flex items-center gap-2">
                                            <span class="w-6 h-6 rounded-lg bg-emerald-500 text-white text-xs font-black flex items-center justify-center">+</span>
                                            <div>
                                                <p class="text-xs font-black text-slate-700">Boundary Collection</p>
                                                <p class="text-[9px] text-slate-400 font-bold">Expected boundary collections</p>
                                            </div>
                                        </div>
                                        <span class="font-black text-emerald-600 text-sm">{{ formatCurrency($forecast_predicted['predicted_boundary'] ?? 0) }}</span>
                                    </div>
                                    <div class="flex justify-between items-center p-2.5 bg-rose-50 rounded-xl border border-rose-100">
                                        <div class="flex items-center gap-2">
                                            <span class="w-6 h-6 rounded-lg bg-rose-500 text-white text-xs font-black flex items-center justify-center">-</span>
                                            <div>
                                                <p class="text-xs font-black text-slate-700">Office Expenses</p>
                                                <p class="text-[9px] text-slate-400 font-bold">Electricity, water, office supplies, etc.</p>
                                            </div>
                                        </div>
                                        <span class="font-black text-rose-600 text-sm">{{ formatCurrency($forecast_predicted['predicted_expenses'] ?? 0) }}</span>
                                    </div>
                                    <div class="flex justify-between items-center p-2.5 bg-amber-50 rounded-xl border border-amber-100">
                                        <div class="flex items-center gap-2">
                                            <span class="w-6 h-6 rounded-lg bg-amber-500 text-white text-xs font-black flex items-center justify-center">-</span>
                                            <div>
                                                <p class="text-xs font-black text-slate-700">Maintenance Costs</p>
                                                <p class="text-[9px] text-slate-400 font-bold">Repair costs for all active units</p>
                                            </div>
                                        </div>
                                        <span class="font-black text-amber-600 text-sm">{{ formatCurrency($forecast_predicted['predicted_maintenance'] ?? 0) }}</span>
                                    </div>
                                    <div class="flex justify-between items-center p-2.5 bg-indigo-50 rounded-xl border border-indigo-100">
                                        <div class="flex items-center gap-2">
                                            <span class="w-6 h-6 rounded-lg bg-indigo-500 text-white text-xs font-black flex items-center justify-center">-</span>
                                            <div>
                                                <p class="text-xs font-black text-slate-700">Employee Salaries</p>
                                                <p class="text-[9px] text-slate-400 font-bold">Payroll for all employees</p>
                                            </div>
                                        </div>
                                        <span class="font-black text-indigo-600 text-sm">{{ formatCurrency($forecast_predicted['predicted_salaries'] ?? 0) }}</span>
                                    </div>
                                </div>
                                
                                {{-- Divider + Formula --}}
                                <div class="border-t-2 border-dashed border-slate-200 pt-3 mt-3">
                                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-2">Formula: Collections − Expenses − Maintenance − Salaries</p>
                                    <div class="flex justify-between items-center bg-gradient-to-r from-slate-800 to-slate-900 p-4 rounded-xl">
                                        <span class="text-[11px] font-black text-white uppercase tracking-widest">Expected Net Income</span>
                                        <span class="text-lg font-black {{ ($forecast_predicted['net_income'] ?? 0) >= 0 ? 'text-emerald-400' : 'text-rose-400' }}">
                                            {{ formatCurrency($forecast_predicted['net_income'] ?? 0) }}
                                        </span>
                                    </div>
                                    <div class="mt-3 grid grid-cols-2 gap-2">
                                        <div class="p-2.5 bg-emerald-50 rounded-xl border border-emerald-100 text-center">
                                            <p class="text-[9px] font-black text-emerald-600 uppercase tracking-widest mb-0.5">Best Case (+15%)</p>
                                            <p class="text-sm font-black text-emerald-700">{{ formatCurrency($forecast_predicted['best_case_net'] ?? 0) }}</p>
                                        </div>
                                        <div class="p-2.5 bg-amber-50 rounded-xl border border-amber-100 text-center">
                                            <p class="text-[9px] font-black text-amber-600 uppercase tracking-widest mb-0.5">Worst Case (-15%)</p>
                                            <p class="text-sm font-black text-amber-700">{{ formatCurrency($forecast_predicted['worst_case_net'] ?? 0) }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="flex flex-col">
                                <div class="flex items-center gap-1 px-3 py-1 rounded-full text-xs font-black w-fit
                                    {{ $trendUpNet ? 'bg-emerald-500/20 text-emerald-300' : 'bg-rose-500/20 text-rose-300' }}">
                                    <i data-lucide="{{ $trendUpNet ? 'trending-up' : 'trending-down' }}" class="w-3.5 h-3.5"></i>
                                    {{ $trendUpNet ? '+' : '' }}{{ formatCurrency($diffNet) }}
                                </div>
                            </div>
                        </div>
                        <p class="text-indigo-300/70 text-[11px] font-bold mt-1 relative z-0 flex items-center gap-1.5">
                            <i data-lucide="mouse-pointer-2" class="w-3 h-3"></i>
                            Hover over the figure to view the full calculation
                        </p>
                    </div>

                    {{-- Right side – Range & Confidence --}}
                    <div class="flex flex-col sm:flex-row lg:flex-col gap-4 lg:min-w-[260px]">
                        {{-- Best Case --}}
                        <div class="flex-1 p-5 bg-white/10 backdrop-blur-sm rounded-2xl border border-white/10">
                            <div class="flex items-center gap-2 mb-2">
                                <i data-lucide="arrow-up-circle" class="w-4 h-4 text-emerald-400"></i>
                                <span class="text-[10px] font-black text-emerald-300 uppercase tracking-widest">Highest (Best Case)</span>
                            </div>
                            <p class="text-2xl font-black text-emerald-300">{{ formatCurrency($forecast_predicted['best_case_net'] ?? 0) }}</p>
                            <p class="text-[10px] text-indigo-300/60 mt-1">Assumes optimal performance across all units</p>
                        </div>
                        {{-- Worst Case --}}
                        <div class="flex-1 p-5 bg-white/10 backdrop-blur-sm rounded-2xl border border-white/10">
                            <div class="flex items-center gap-2 mb-2">
                                <i data-lucide="arrow-down-circle" class="w-4 h-4 text-amber-400"></i>
                                <span class="text-[10px] font-black text-amber-300 uppercase tracking-widest">Lowest (Worst Case)</span>
                            </div>
                            <p class="text-2xl font-black text-amber-300">{{ formatCurrency($forecast_predicted['worst_case_net'] ?? 0) }}</p>
                            <p class="text-[10px] text-indigo-300/60 mt-1">Accounts for unexpected expenses or shortages</p>
                        </div>
                        {{-- Confidence --}}
                        <div class="flex items-center gap-3 p-4 bg-white/5 rounded-2xl border border-white/10">
                            <i data-lucide="shield-check" class="w-5 h-5 text-indigo-300"></i>
                            <div>
                                <p class="text-[10px] font-black text-indigo-300/70 uppercase tracking-widest">Confidence Level</p>
                                <p class="text-sm font-black text-white">@if(($forecast_predicted['confidence'] ?? '') === 'Mataas' || ($forecast_predicted['confidence'] ?? '') === 'High') High @elseif(($forecast_predicted['confidence'] ?? '') === 'Katamtaman' || ($forecast_predicted['confidence'] ?? '') === 'Medium') Medium @elseif(($forecast_predicted['confidence'] ?? '') === 'Mababa' || ($forecast_predicted['confidence'] ?? '') === 'Low') Low @else {{ $forecast_predicted['confidence'] ?? 'N/A' }} @endif</p>
                            </div>
                            <span class="ml-auto px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider
                                @php
                                    $conf = $forecast_predicted['confidence'] ?? '';
                                @endphp
                                {{ $conf === 'High' || $conf === 'Mataas' ? 'bg-emerald-500/20 text-emerald-300' : ($conf === 'Medium' || $conf === 'Katamtaman' ? 'bg-amber-500/20 text-amber-300' : 'bg-rose-500/20 text-rose-300') }}
                            ">@if($conf === 'Mataas' || $conf === 'High') High @elseif($conf === 'Katamtaman' || $conf === 'Medium') Medium @elseif($conf === 'Mababa' || $conf === 'Low') Low @else {{ $conf }} @endif</span>
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
                        'label' => 'Expected Collections',
                        'sublabel' => 'Expected Collections',
                        'key' => 'boundary',
                        'icon' => 'wallet',
                        'color' => 'emerald',
                        'source' => 'Derived from the average boundary collected across active taxi units over the past 6 months.',
                    ],
                    [
                        'label' => 'Expected Expenses',
                        'sublabel' => 'Expected Expenses',
                        'key' => 'expenses',
                        'icon' => 'receipt',
                        'color' => 'rose',
                        'source' => 'Derived from the average office expenses (utilities, office supplies, etc.) over the past 6 months.',
                    ],
                    [
                        'label' => 'Expected Repairs',
                        'sublabel' => 'Expected Repairs',
                        'key' => 'maintenance',
                        'icon' => 'wrench',
                        'color' => 'amber',
                        'source' => 'Derived from vehicle maintenance records — average repair costs of all units over the past 6 months.',
                    ],
                    [
                        'label' => 'Expected Salaries',
                        'sublabel' => 'Expected Salaries',
                        'key' => 'salaries',
                        'icon' => 'users',
                        'color' => 'indigo',
                        'source' => 'Derived from employee payroll records — average salary payouts over the past 6 months.',
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
                            Compute: ({{ formatCurrency($val) }} Expected - {{ formatCurrency($lastVal) }} Last Month)
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
                        <h3 class="text-lg font-black text-slate-800">6-Month Trend & Next Month Prediction</h3>
                        <p class="text-xs text-slate-500">Actual net income of the last 6 months and predicted next month</p>
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
                            <p class="text-[11px] font-black text-indigo-700 uppercase tracking-widest mb-1">How to Read the Chart</p>
                            <p class="text-xs text-slate-600 leading-relaxed">
                                The <strong>blue bars</strong> represent the actual net income of the past 6 months. The <strong>green striped bar</strong> at the end is our prediction for next month.
                                The <strong>green line</strong> (Revenue) and <strong>red line</strong> (Total Expenses) show the overall trends. A wider gap between the green and red lines indicates a better profit margin.
                            </p>
                        </div>
                    </div>
                </div>
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
                    $gaugeLabel = $healthScore >= 80 ? 'Good' : ($healthScore >= 60 ? 'Moderate' : ($healthScore >= 40 ? 'Needs Attention' : 'Critical'));
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
                    Measured based on revenue consistency, expense trends, and maintenance patterns.
                </p>
            </div>

            {{-- Sub-metrics --}}
            <div class="lg:col-span-2 bg-white rounded-3xl border border-slate-200 shadow-sm p-8">
                <h3 class="text-lg font-black text-slate-800 mb-2">Financial Health Details</h3>
                <p class="text-xs text-slate-500 mb-8">Key components that determine the overall health score of your fleet business</p>

                <div class="space-y-6">
                    {{-- Revenue Consistency --}}
                    @php
                        $revCon = $forecast_health['revenue_consistency'] ?? 'N/A';
                        $revConColor = ($revCon === 'Stable') ? 'emerald' : (($revCon === 'Volatile' || $revCon === 'Medyo Pabago-bago') ? 'rose' : 'amber');
                    @endphp
                    <div class="p-5 bg-slate-50 rounded-2xl border border-slate-100">
                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center gap-3">
                                <div class="p-2.5 bg-{{ $revConColor }}-50 rounded-xl">
                                    <i data-lucide="bar-chart-3" class="w-5 h-5 text-{{ $revConColor }}-600"></i>
                                </div>
                                <div>
                                    <h4 class="text-sm font-black text-slate-800">Revenue Consistency</h4>
                                    <p class="text-[10px] text-slate-500">How stable are your monthly collections?</p>
                                </div>
                            </div>
                            <span class="px-3 py-1 bg-{{ $revConColor }}-50 text-{{ $revConColor }}-700 rounded-full text-[10px] font-black border border-{{ $revConColor }}-200">
                                @if($revCon === 'Medyo Pabago-bago') Moderate Volatility @else {{ $revCon }} @endif
                            </span>
                        </div>
                        <p class="text-[10px] text-slate-500 leading-relaxed pl-[52px]">A "Stable" status indicates consistent month-to-month collections. A "Volatile" status indicates high fluctuations in revenue.</p>
                    </div>

                    {{-- Expense Trend --}}
                    @php
                        $expTrend = $forecast_health['expense_trend'] ?? 'N/A';
                        $expColor = ($expTrend === 'Decreasing' || $expTrend === 'Pababa') ? 'emerald' : (($expTrend === 'Increasing' || $expTrend === 'Pataas') ? 'rose' : 'amber');
                    @endphp
                    <div class="p-5 bg-slate-50 rounded-2xl border border-slate-100">
                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center gap-3">
                                <div class="p-2.5 bg-{{ $expColor }}-50 rounded-xl">
                                    <i data-lucide="{{ ($expTrend === 'Decreasing' || $expTrend === 'Pababa') ? 'trending-down' : 'trending-up' }}" class="w-5 h-5 text-{{ $expColor }}-600"></i>
                                </div>
                                <div>
                                    <h4 class="text-sm font-black text-slate-800">Expense Trend</h4>
                                    <p class="text-[10px] text-slate-500">Are your company expenses increasing or decreasing?</p>
                                </div>
                            </div>
                            <span class="px-3 py-1 bg-{{ $expColor }}-50 text-{{ $expColor }}-700 rounded-full text-[10px] font-black border border-{{ $expColor }}-200">
                                @if($expTrend === 'Pataas') Increasing @elseif($expTrend === 'Pababa') Decreasing @else {{ $expTrend }} @endif
                            </span>
                        </div>
                        <p class="text-[10px] text-slate-500 leading-relaxed pl-[52px]">An "Increasing" trend suggests you should audit office expenses and vehicle maintenance costs to identify savings.</p>
                    </div>

                    {{-- Maintenance Trend --}}
                    @php
                        $maintTrend = $forecast_health['maintenance_trend'] ?? 'N/A';
                        $maintColor = ($maintTrend === 'Decreasing' || $maintTrend === 'Pababa') ? 'emerald' : (($maintTrend === 'Increasing' || $maintTrend === 'Pataas') ? 'rose' : 'amber');
                    @endphp
                    <div class="p-5 bg-slate-50 rounded-2xl border border-slate-100">
                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center gap-3">
                                <div class="p-2.5 bg-{{ $maintColor }}-50 rounded-xl">
                                    <i data-lucide="wrench" class="w-5 h-5 text-{{ $maintColor }}-600"></i>
                                </div>
                                <div>
                                    <h4 class="text-sm font-black text-slate-800">Maintenance Trend</h4>
                                    <p class="text-[10px] text-slate-500">Are vehicle repair costs increasing or decreasing?</p>
                                </div>
                            </div>
                            <span class="px-3 py-1 bg-{{ $maintColor }}-50 text-{{ $maintColor }}-700 rounded-full text-[10px] font-black border border-{{ $maintColor }}-200">
                                @if($maintTrend === 'Pataas') Increasing @elseif($maintTrend === 'Pababa') Decreasing @else {{ $maintTrend }} @endif
                            </span>
                        </div>
                        <p class="text-[10px] text-slate-500 leading-relaxed pl-[52px]">A "Decreasing" trend is ideal, indicating fewer repairs. An "Increasing" trend suggests evaluating older units that require frequent repairs.</p>
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
                        <h3 class="text-lg font-black text-slate-800">How Do We Calculate This?</h3>
                        <p class="text-xs text-slate-500">Transparency — track the data source of each metric below</p>
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
                            <h4 class="text-sm font-black text-slate-800">Expected Collections (Boundary)</h4>
                        </div>
                        <p class="text-xs text-slate-600 leading-relaxed">
                            We calculate the <strong>average boundary collected across the past 6 months</strong>. More recent months carry higher weights to reflect recent fleet productivity and trends accurately.
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
                            <h4 class="text-sm font-black text-slate-800">Expected Expenses (Office Expenses)</h4>
                        </div>
                        <p class="text-xs text-slate-600 leading-relaxed">
                            We analyze the <strong>recurring patterns in monthly office expenses</strong> — including utility bills, office supplies, and administrative fees, calculating the trend line.
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
                            <h4 class="text-sm font-black text-slate-800">Expected Repairs (Maintenance)</h4>
                        </div>
                        <p class="text-xs text-slate-600 leading-relaxed">
                            Based on <strong>maintenance logs from all active vehicles</strong>, we average the monthly repair costs (parts, labor, and emergency servicing), adjusted by vehicle age.
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
                            <h4 class="text-sm font-black text-slate-800">Expected Salaries (Salaries)</h4>
                        </div>
                        <p class="text-xs text-slate-600 leading-relaxed">
                            We compile the <strong>payroll metrics over the past 6 months</strong> — including fixed base salaries, overtime pay, and adjustments, computing a reliable forward average.
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
                            <p class="text-[11px] font-black text-indigo-700 uppercase tracking-widest mb-2">Simple Formula</p>
                            <p class="text-sm font-bold text-slate-700 mb-2">
                                Net Income = Expected Collections − Expected Expenses − Expected Repairs − Expected Salaries
                            </p>
                            <p class="text-xs text-slate-500 leading-relaxed">
                                Each component is computed using a <strong>weighted moving average</strong> of the last 6 months, prioritizing recent performance. Best/Worst Case represents a ±15% margin based on historical fleet volatility.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ┌─────────────────────────────────────────────────────────────────────┐
             │  TOP PERFORMERS – Top 10 Highest Earning Units                     │
             └─────────────────────────────────────────────────────────────────────┘ --}}
        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
            {{-- Header --}}
            <div class="px-8 py-6 border-b border-slate-100 bg-gradient-to-r from-amber-50/60 to-white flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-gradient-to-br from-amber-400 to-orange-500 rounded-2xl flex items-center justify-center shadow-lg shadow-amber-200">
                        <i data-lucide="trophy" class="w-6 h-6 text-white"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-black text-slate-800 flex items-center gap-2">
                            Top 10 Highest Earning Units
                        </h3>
                        <p class="text-[11px] text-slate-500 mt-0.5">
                            Based on average daily boundary collections over the past <strong>90 days</strong> — active units only
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    <span class="px-3 py-1.5 bg-amber-100 text-amber-700 text-[10px] font-black rounded-full uppercase tracking-widest border border-amber-200">
                        📊 90-Day Average
                    </span>
                </div>
            </div>

            {{-- Unit Profitability Cards --}}
            <div class="p-6">
                @if(count($forecast_unit_profits) > 0)
                    {{-- Top 3 Podium --}}
                    @php $topThree = array_slice($forecast_unit_profits, 0, 3); @endphp
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                        @foreach($topThree as $idx => $unit)
                        @php
                            $rank = $idx + 1;
                            $podiumColors = [
                                1 => ['bg' => 'from-amber-400 to-yellow-500', 'border' => 'border-amber-300', 'bg_light' => 'bg-amber-50', 'text' => 'text-amber-700', 'icon' => '🥇'],
                                2 => ['bg' => 'from-slate-300 to-slate-400', 'border' => 'border-slate-300', 'bg_light' => 'bg-slate-50', 'text' => 'text-slate-600', 'icon' => '🥈'],
                                3 => ['bg' => 'from-orange-300 to-amber-400', 'border' => 'border-orange-200', 'bg_light' => 'bg-orange-50', 'text' => 'text-orange-700', 'icon' => '🥉'],
                            ];
                            $pc = $podiumColors[$rank];
                            $monthlyProfit = $unit['monthly_profit'] ?? 0;
                            $dailyProfit = $unit['daily_profit'] ?? 0;
                            $avgDaily = $unit['avg_daily_boundary'] ?? 0;
                            $opDays = $unit['operating_days_90d'] ?? 0;
                            $avgMaint = $unit['avg_daily_maint'] ?? 0;
                        @endphp
                        <div class="relative rounded-2xl border-2 {{ $pc['border'] }} {{ $pc['bg_light'] }} p-5 overflow-hidden group hover:shadow-lg transition-all duration-300 hover:-translate-y-1">
                            {{-- Rank Badge --}}
                            <div class="absolute -top-3 -right-3 w-14 h-14 bg-gradient-to-br {{ $pc['bg'] }} rounded-2xl flex items-center justify-center shadow-lg transform rotate-12 group-hover:rotate-0 transition-transform duration-300">
                                <span class="text-2xl">{{ $pc['icon'] }}</span>
                            </div>

                            {{-- Plate Number --}}
                            <div class="mb-4 pr-8">
                                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Rank #{{ $rank }}</p>
                                <h4 class="text-2xl font-black text-slate-900 tracking-tight">{{ $unit['plate'] }}</h4>
                            </div>

                            {{-- Monthly Profit (Main Metric) --}}
                            <div class="mb-4">
                                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-0.5">Expected Monthly Net Profit</p>
                                <p class="text-xl font-black {{ $monthlyProfit >= 0 ? $pc['text'] : 'text-rose-600' }}">
                                    {{ $monthlyProfit >= 0 ? '+' : '' }}₱{{ number_format($monthlyProfit) }}
                                </p>
                            </div>

                            {{-- Stats Grid --}}
                            <div class="grid grid-cols-2 gap-2 pt-3 border-t border-slate-200/50">
                                <div>
                                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Avg Daily</p>
                                    <p class="text-xs font-black text-emerald-700">₱{{ number_format($avgDaily) }}</p>
                                </div>
                                <div>
                                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Daily Profit</p>
                                    <p class="text-xs font-black {{ $dailyProfit >= 0 ? 'text-slate-700' : 'text-rose-600' }}">{{ $dailyProfit >= 0 ? '+' : '' }}₱{{ number_format($dailyProfit) }}</p>
                                </div>
                                <div>
                                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Op. Days (90d)</p>
                                    <p class="text-xs font-black text-slate-700">{{ $opDays }} days</p>
                                </div>
                                <div>
                                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Avg Daily Maint.</p>
                                    <p class="text-xs font-black text-rose-600">₱{{ number_format($avgMaint) }}</p>
                                </div>
                            </div>

                            {{-- Computation note --}}
                            <div class="mt-3 p-2 bg-white/60 rounded-xl border border-white text-[9px] text-slate-500 leading-tight">
                                <strong>Formula:</strong> (Avg Daily ₱{{ number_format($avgDaily) }} − Maint ₱{{ number_format($avgMaint) }}) × 30 days
                            </div>
                        </div>
                        @endforeach
                    </div>

                    {{-- Ranks 4-10 Table --}}
                    @if(count($forecast_unit_profits) > 3)
                    <div class="bg-slate-50 rounded-2xl border border-slate-100 overflow-hidden">
                        <div class="px-5 py-3 border-b border-slate-100 flex items-center gap-2">
                            <i data-lucide="list-ordered" class="w-4 h-4 text-slate-500"></i>
                            <h4 class="text-[11px] font-black text-slate-600 uppercase tracking-widest">Ranks 4–{{ min(10, count($forecast_unit_profits)) }} — Unit Profitability Breakdown</h4>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="border-b border-slate-200">
                                        <th class="text-left text-[10px] font-black text-slate-400 uppercase tracking-widest py-3 px-4">Rank</th>
                                        <th class="text-left text-[10px] font-black text-slate-400 uppercase tracking-widest py-3">Unit</th>
                                        <th class="text-right text-[10px] font-black text-slate-400 uppercase tracking-widest py-3">Avg Daily Boundary</th>
                                        <th class="text-right text-[10px] font-black text-slate-400 uppercase tracking-widest py-3">Avg Daily Maint.</th>
                                        <th class="text-right text-[10px] font-black text-slate-400 uppercase tracking-widest py-3">Daily Net Profit</th>
                                        <th class="text-right text-[10px] font-black text-slate-400 uppercase tracking-widest py-3">Monthly Projection</th>
                                        <th class="text-right text-[10px] font-black text-slate-400 uppercase tracking-widest py-3 pr-4">Op. Days (90d)</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @foreach(array_slice($forecast_unit_profits, 3) as $idx => $unit)
                                    @php $rank = $idx + 4; @endphp
                                    <tr class="hover:bg-white transition-colors">
                                        <td class="py-3 px-4 font-black text-slate-400 text-sm">#{{ $rank }}</td>
                                        <td class="py-3 font-black text-slate-800">{{ $unit['plate'] }}</td>
                                        <td class="py-3 text-right font-semibold text-emerald-700">₱{{ number_format($unit['avg_daily_boundary'] ?? 0) }}</td>
                                        <td class="py-3 text-right font-semibold text-rose-500">₱{{ number_format($unit['avg_daily_maint'] ?? 0) }}</td>
                                        <td class="py-3 text-right font-black {{ ($unit['daily_profit'] ?? 0) >= 0 ? 'text-emerald-700' : 'text-rose-600' }}">
                                            {{ ($unit['daily_profit'] ?? 0) >= 0 ? '+' : '' }}₱{{ number_format($unit['daily_profit'] ?? 0) }}
                                        </td>
                                        <td class="py-3 text-right font-black {{ ($unit['monthly_profit'] ?? 0) >= 0 ? 'text-indigo-700' : 'text-rose-600' }}">
                                            {{ ($unit['monthly_profit'] ?? 0) >= 0 ? '+' : '' }}₱{{ number_format($unit['monthly_profit'] ?? 0) }}
                                        </td>
                                        <td class="py-3 text-right font-semibold text-slate-600 pr-4">{{ $unit['operating_days_90d'] ?? 0 }}d</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @endif

                    {{-- Explanation Panel --}}
                    <div class="mt-6 p-5 bg-indigo-50/60 rounded-2xl border border-indigo-100">
                        <div class="flex items-start gap-3">
                            <i data-lucide="info" class="w-5 h-5 text-indigo-500 flex-shrink-0 mt-0.5"></i>
                            <div>
                                <p class="text-[11px] font-black text-indigo-700 uppercase tracking-widest mb-2">How is the Ranking Calculated?</p>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-xs text-slate-600">
                                    <div class="flex items-start gap-2">
                                        <span class="w-4 h-4 rounded-full bg-emerald-500 text-white text-[9px] font-black flex items-center justify-center shrink-0 mt-0.5">1</span>
                                        <p><strong>Avg Daily Boundary</strong> — The average actual boundary collected for this unit in the last 90 days.</p>
                                    </div>
                                    <div class="flex items-start gap-2">
                                        <span class="w-4 h-4 rounded-full bg-rose-500 text-white text-[9px] font-black flex items-center justify-center shrink-0 mt-0.5">2</span>
                                        <p><strong>Avg Daily Maintenance Cost</strong> — Total repair expenses of the unit over 90 days ÷ 90.</p>
                                    </div>
                                    <div class="flex items-start gap-2">
                                        <span class="w-4 h-4 rounded-full bg-amber-500 text-white text-[9px] font-black flex items-center justify-center shrink-0 mt-0.5">3</span>
                                        <p><strong>Daily Net Profit</strong> = Avg Daily Boundary − Avg Daily Maint. Cost.</p>
                                    </div>
                                    <div class="flex items-start gap-2">
                                        <span class="w-4 h-4 rounded-full bg-indigo-500 text-white text-[9px] font-black flex items-center justify-center shrink-0 mt-0.5">4</span>
                                        <p><strong>Monthly Projection</strong> = Daily Net Profit × 30 days. This represents the unit's expected monthly profit.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="py-16 flex flex-col items-center justify-center text-center">
                        <div class="w-16 h-16 bg-amber-50 text-amber-400 rounded-3xl flex items-center justify-center mb-4">
                            <i data-lucide="trophy" class="w-8 h-8"></i>
                        </div>
                        <h4 class="text-lg font-black text-slate-700 mb-2">No Active Unit Data</h4>
                        <p class="text-sm text-slate-400 max-w-xs leading-relaxed">No active units with boundary records in the last 90 days.</p>
                    </div>
                @endif
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
    <div id="section-strategy" class="hidden space-y-8">
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

                            {{-- AI Control & Security Status Card --}}
                            <div class="p-6 bg-emerald-50/50 rounded-3xl border border-emerald-100 shadow-sm transition-all hover:shadow-md duration-300">
                                <h4 class="text-[10px] font-black text-emerald-700 uppercase tracking-widest mb-4 flex items-center gap-1.5">
                                    <i data-lucide="shield-check" class="w-4 h-4 text-emerald-600 animate-pulse"></i> AI Integration Status
                                </h4>
                                <div class="space-y-3">
                                    <div class="flex items-center justify-between text-xs font-bold text-slate-700">
                                        <span>AI Data Visibility</span>
                                        <span class="px-2 py-0.5 bg-emerald-100/70 text-emerald-800 text-[9px] font-black rounded-lg uppercase tracking-wider">Full Read</span>
                                    </div>
                                    <div class="flex items-center justify-between text-xs font-bold text-slate-700">
                                        <span>System Control</span>
                                        <span class="px-2 py-0.5 bg-indigo-100/70 text-indigo-800 text-[9px] font-black rounded-lg uppercase tracking-wider">Advisor Mode</span>
                                    </div>
                                    <div class="flex items-center justify-between text-xs font-bold text-slate-700">
                                        <span>Security Protocol</span>
                                        <span class="px-2 py-0.5 bg-amber-100/70 text-amber-800 text-[9px] font-black rounded-lg uppercase tracking-wider">No Passwords</span>
                                    </div>
                                    <div class="pt-3 border-t border-emerald-100/70 text-[10px] text-slate-500 leading-normal font-semibold">
                                        The AI advisor reads telemetry data across all tables (Fleet, Drivers, Boundaries, Maintenance, Expenses, Legal, and Inventory) in real time to generate recommendations. User passwords and credentials are completely excluded.
                                    </div>
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

        // Active and Inactive classes
        const activeClasses   = ['bg-white', 'text-indigo-700', 'shadow-sm', 'border', 'border-indigo-100', 'ring-1', 'ring-indigo-500/20'];
        const inactiveClasses = ['text-slate-500', 'hover:bg-white', 'hover:text-slate-800', 'hover:shadow-sm'];

        // Reset All
        Object.values(sections).forEach(s => s.classList.add('hidden'));
        Object.entries(buttons).forEach(([key, btn]) => {
            btn.classList.remove(...activeClasses);
            btn.classList.add(...inactiveClasses);
        });

        // Activate Selected
        sections[tab].classList.remove('hidden');
        buttons[tab].classList.remove(...inactiveClasses);
        buttons[tab].classList.add(...activeClasses);

        // Handle Chart Resizing if needed
        if (tab === 'performance' || tab === 'forecast') {
            window.dispatchEvent(new Event('resize'));
        }
    }

    // ── Forecast Income Popover – Smart Viewport-Aware Positioning ──────────
    (function() {
        let popoverHideTimer = null;
        const POPOVER_GAP = 14;

        window.showForecastPopover = function(e) {
            cancelForecastPopoverHide();
            const trigger  = document.getElementById('forecast-net-income-trigger');
            const popover  = document.getElementById('forecast-computation-popover');
            const arrow    = document.getElementById('forecast-popover-arrow');
            if (!trigger || !popover) return;

            popover.classList.remove('opacity-0', 'invisible', 'pointer-events-none');
            popover.classList.add('opacity-100', 'visible', 'pointer-events-auto');

            const tr = trigger.getBoundingClientRect();
            const pw = popover.offsetWidth  || 360;
            const ph = popover.offsetHeight || 400;
            const vw = window.innerWidth;
            const vh = window.innerHeight;

            let top, left, arrowClass;

            // Prefer right side
            if (tr.right + pw + POPOVER_GAP <= vw) {
                left = tr.right + POPOVER_GAP + window.scrollX;
                top  = Math.max(8, Math.min(tr.top + window.scrollY - ph / 2 + tr.height / 2, vh + window.scrollY - ph - 8));
                arrowClass = 'border-l border-b -left-2 top-1/2 -translate-y-1/2 border-r-0 border-t-0';
            // Prefer left side
            } else if (tr.left - pw - POPOVER_GAP >= 0) {
                left = tr.left - pw - POPOVER_GAP + window.scrollX;
                top  = Math.max(8, Math.min(tr.top + window.scrollY - ph / 2 + tr.height / 2, vh + window.scrollY - ph - 8));
                arrowClass = 'border-r border-t -right-2 top-1/2 -translate-y-1/2 border-l-0 border-b-0';
            // Fall to below trigger
            } else if (tr.bottom + ph + POPOVER_GAP <= vh) {
                top  = tr.bottom + POPOVER_GAP + window.scrollY;
                left = Math.max(8, Math.min(tr.left + window.scrollX, vw + window.scrollX - pw - 8));
                arrowClass = 'border-t border-l -top-2 left-8 border-r-0 border-b-0';
            // Fall to above trigger
            } else {
                top  = tr.top + window.scrollY - ph - POPOVER_GAP;
                left = Math.max(8, Math.min(tr.left + window.scrollX, vw + window.scrollX - pw - 8));
                arrowClass = 'border-b border-r -bottom-2 left-8 border-t-0 border-l-0';
            }

            popover.style.top  = top  + 'px';
            popover.style.left = left + 'px';
            if (arrow) {
                arrow.className = 'absolute w-4 h-4 bg-white rotate-45 border-slate-200 ' + arrowClass;
            }
        };

        window.scheduleForecastPopoverHide = function() {
            popoverHideTimer = setTimeout(() => {
                const popover = document.getElementById('forecast-computation-popover');
                if (popover) {
                    popover.classList.add('opacity-0', 'invisible', 'pointer-events-none');
                    popover.classList.remove('opacity-100', 'visible', 'pointer-events-auto');
                }
            }, 180);
        };

        window.cancelForecastPopoverHide = function() {
            if (popoverHideTimer) clearTimeout(popoverHideTimer);
            popoverHideTimer = null;
        };

        window.toggleForecastPopover = function(e) {
            e.stopPropagation();
            const popover = document.getElementById('forecast-computation-popover');
            if (!popover) return;
            if (popover.classList.contains('invisible')) {
                window.showForecastPopover(e);
            } else {
                scheduleForecastPopoverHide();
            }
        };

        // Close popover when clicking outside
        document.addEventListener('click', function(e) {
            const trigger = document.getElementById('forecast-net-income-trigger');
            const popover = document.getElementById('forecast-computation-popover');
            if (!trigger || !popover) return;
            if (!trigger.contains(e.target) && !popover.contains(e.target)) {
                popover.classList.add('opacity-0', 'invisible', 'pointer-events-none');
                popover.classList.remove('opacity-100', 'visible', 'pointer-events-auto');
            }
        });
    })();


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
    const expCanvas = document.getElementById('expenseChart');
    if (expCanvas) {
        if (expenseData.length > 0) {
            const expCtx = expCanvas.getContext('2d');
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
        } else {
            const parent = expCanvas.parentElement;
            parent.innerHTML = `
                <div class="flex flex-col items-center justify-center text-center h-full">
                    <div class="w-16 h-16 bg-slate-50 text-slate-400 rounded-3xl flex items-center justify-center mb-4 border border-slate-100/80">
                        <i data-lucide="wrench" class="w-8 h-8"></i>
                    </div>
                    <h4 class="text-sm font-black text-slate-700 mb-1">No Data for this Period</h4>
                    <p class="text-xs text-slate-400 max-w-[200px] leading-normal font-medium">No maintenance expenses recorded in the selected dates.</p>
                </div>
            `;
            if (window.lucide) window.lucide.createIcons();
        }
    }

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
        legal: 'text-rose-700 bg-rose-50',
        inventory: 'text-amber-700 bg-amber-50',
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
            const histNet = fHistory.map(h => h.net_income);
            const histBoundary = fHistory.map(h => h.boundary);
            const histTotalExp = fHistory.map(h => (h.expenses || 0) + (h.maintenance || 0) + (h.salaries || 0));

            // Add predicted month
            const allLabels = [...histLabels, 'Predicted'];
            const allNet = [...histNet, fPredicted.net_income || 0];
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