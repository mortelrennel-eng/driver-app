<?php $__env->startSection('title', 'AI Fleet Intelligence - Euro System'); ?>
<?php $__env->startSection('page-heading', 'Advanced Fleet Analytics'); ?>
<?php $__env->startSection('page-subheading', 'Real-time Pulse • Historical Trends • Predictive Forecasting'); ?>

<?php $__env->startSection('content'); ?>
    
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 mb-8">
        <form method="GET" action="<?php echo e(route('analytics.index')); ?>" class="flex flex-col md:flex-row items-end gap-4">
            <div class="flex-1 w-full">
                <label class="text-[11px] font-black text-slate-400 uppercase tracking-widest mb-1.5 block">Analysis Period</label>
                <div class="grid grid-cols-2 gap-3">
                    <input type="date" name="date_from" value="<?php echo e($date_from); ?>"
                        class="block w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all text-sm font-semibold">
                    <input type="date" name="date_to" value="<?php echo e($date_to); ?>"
                        class="block w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all text-sm font-semibold">
                </div>
            </div>
            <button type="submit"
                class="w-full md:w-auto px-8 py-2.5 bg-indigo-600 text-white rounded-xl font-black text-sm hover:bg-indigo-700 transition-all shadow-lg shadow-indigo-200 flex items-center justify-center gap-2">
                <i data-lucide="refresh-cw" class="w-4 h-4"></i> Sync Data
            </button>
        </form>
    </div>

    
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

    
    <div id="section-pulse" class="space-y-8 animate-in fade-in duration-500">
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            
            <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm hover:shadow-md transition-all group">
                <div class="flex items-start justify-between mb-4">
                    <div class="p-3 bg-indigo-50 rounded-2xl text-indigo-600 group-hover:scale-110 transition-transform">
                        <i data-lucide="car" class="w-6 h-6"></i>
                    </div>
                    <span class="text-[10px] font-black text-indigo-600 bg-indigo-50 px-2.5 py-1 rounded-full uppercase tracking-widest">Real-time</span>
                </div>
                <h3 class="text-[11px] font-black text-slate-400 uppercase tracking-widest mb-1">Fleet Utilization</h3>
                <div class="flex items-end gap-2 mb-2">
                    <span class="text-3xl font-black text-slate-800 leading-none"><?php echo e($fleet_utilization); ?>%</span>
                    <span class="text-xs font-bold text-slate-500 pb-0.5">Active Now</span>
                </div>
                <div class="h-1.5 bg-slate-100 rounded-full overflow-hidden">
                    <div class="h-full bg-indigo-500 transition-all duration-1000" style="width: <?php echo e($fleet_utilization); ?>%"></div>
                </div>
                <p class="text-[10px] text-slate-500 mt-3 leading-relaxed">
                    Percentage of units currently generating revenue versus idle or in maintenance.
                </p>
            </div>

            
            <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm hover:shadow-md transition-all group">
                <div class="flex items-start justify-between mb-4">
                    <div class="p-3 bg-emerald-50 rounded-2xl text-emerald-600 group-hover:scale-110 transition-transform">
                        <i data-lucide="wallet" class="w-6 h-6"></i>
                    </div>
                    <span class="text-[10px] font-black text-emerald-600 bg-emerald-50 px-2.5 py-1 rounded-full uppercase tracking-widest">Net Pulse</span>
                </div>
                <h3 class="text-[11px] font-black text-slate-400 uppercase tracking-widest mb-1">Net Margin</h3>
                <div class="flex items-end gap-2 mb-2">
                    <span class="text-3xl font-black text-slate-800 leading-none"><?php echo e(formatCurrency($net_income)); ?></span>
                </div>
                <p class="text-[10px] text-slate-500 mt-3 leading-relaxed">
                    Total boundary collections minus all operating expenses for the selected period.
                </p>
            </div>

            
            <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm hover:shadow-md transition-all group">
                <div class="flex items-start justify-between mb-4">
                    <div class="p-3 bg-rose-50 rounded-2xl text-rose-600 group-hover:scale-110 transition-transform">
                        <i data-lucide="trending-down" class="w-6 h-6"></i>
                    </div>
                    <span class="text-[10px] font-black text-rose-600 bg-rose-50 px-2.5 py-1 rounded-full uppercase tracking-widest">Risk Factor</span>
                </div>
                <h3 class="text-[11px] font-black text-slate-400 uppercase tracking-widest mb-1">Revenue Leakage</h3>
                <div class="flex items-end gap-2 mb-2">
                    <span class="text-3xl font-black text-slate-800 leading-none"><?php echo e($revenue_leakage_pct); ?>%</span>
                    <span class="text-xs font-bold text-rose-500 pb-0.5">Shortage</span>
                </div>
                <p class="text-[10px] text-slate-500 mt-3 leading-relaxed">
                    Uncollected boundary revenue (shortages) relative to total expected revenue.
                </p>
            </div>

            
            <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm hover:shadow-md transition-all group">
                <div class="flex items-start justify-between mb-4">
                    <div class="p-3 bg-amber-50 rounded-2xl text-amber-600 group-hover:scale-110 transition-transform">
                        <i data-lucide="target" class="w-6 h-6"></i>
                    </div>
                    <span class="text-[10px] font-black text-amber-600 bg-amber-50 px-2.5 py-1 rounded-full uppercase tracking-widest">KPI Target</span>
                </div>
                <h3 class="text-[11px] font-black text-slate-400 uppercase tracking-widest mb-1">Break-even Cycle</h3>
                <div class="flex items-end gap-2 mb-2">
                    <span class="text-3xl font-black text-slate-800 leading-none"><?php echo e($break_even_days); ?></span>
                    <span class="text-xs font-bold text-slate-500 pb-0.5">Oper. Days</span>
                </div>
                <p class="text-[10px] text-slate-500 mt-3 leading-relaxed">
                    Estimated number of full-revenue days needed each month to cover all fixed expenses.
                </p>
            </div>
        </div>

        
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
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
                        <?php 
                            $total = array_sum($fleet_pulse); 
                            $actPct = $total > 0 ? ($fleet_pulse['active_units'] / $total) * 100 : 0;
                            $idlPct = $total > 0 ? ($fleet_pulse['idle_units'] / $total) * 100 : 0;
                            $mntPct = $total > 0 ? ($fleet_pulse['maintenance'] / $total) * 100 : 0;
                            $surPct = $total > 0 ? ($fleet_pulse['surveillance'] / $total) * 100 : 0;
                        ?>
                        <div class="h-full bg-emerald-500 transition-all" style="width: <?php echo e($actPct); ?>%" title="Active: <?php echo e($fleet_pulse['active_units']); ?>"></div>
                        <div class="h-full bg-slate-300 transition-all" style="width: <?php echo e($idlPct); ?>%" title="Idle: <?php echo e($fleet_pulse['idle_units']); ?>"></div>
                        <div class="h-full bg-amber-500 transition-all" style="width: <?php echo e($mntPct); ?>%" title="Maintenance: <?php echo e($fleet_pulse['maintenance']); ?>"></div>
                        <div class="h-full bg-rose-500 transition-all" style="width: <?php echo e($surPct); ?>%" title="Surveillance: <?php echo e($fleet_pulse['surveillance']); ?>"></div>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-6">
                        <div class="space-y-1">
                            <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest block">Active Fleet</span>
                            <p class="text-2xl font-black text-emerald-600"><?php echo e($fleet_pulse['active_units']); ?></p>
                            <p class="text-[10px] text-slate-500 leading-tight">Units currently assigned to drivers and on the road.</p>
                        </div>
                        <div class="space-y-1">
                            <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest block">Awaiting Drivers</span>
                            <p class="text-2xl font-black text-slate-600"><?php echo e($fleet_pulse['idle_units']); ?></p>
                            <p class="text-[10px] text-slate-500 leading-tight">Functional units parked due to lack of available drivers.</p>
                        </div>
                        <div class="space-y-1">
                            <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest block">Under Repair</span>
                            <p class="text-2xl font-black text-amber-600"><?php echo e($fleet_pulse['maintenance']); ?></p>
                            <p class="text-[10px] text-slate-500 leading-tight">Units in the garage for scheduled or emergency service.</p>
                        </div>
                        <div class="space-y-1">
                            <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest block">Under Watch</span>
                            <p class="text-2xl font-black text-rose-600"><?php echo e($fleet_pulse['surveillance']); ?></p>
                            <p class="text-[10px] text-slate-500 leading-tight">Units flagged for suspicious activity or non-payment.</p>
                        </div>
                    </div>
                </div>
            </div>

            
            <div class="bg-indigo-900 rounded-3xl p-8 text-white relative overflow-hidden group">
                <div class="relative z-10">
                    <h3 class="text-xl font-black mb-4">Pulse Analysis</h3>
                    <div class="space-y-6">
                        <div class="flex gap-4">
                            <div class="w-8 h-8 rounded-full bg-white/10 flex items-center justify-center shrink-0">
                                <i data-lucide="info" class="w-4 h-4 text-indigo-300"></i>
                            </div>
                            <p class="text-sm text-indigo-100/90 leading-relaxed">
                                Your fleet is operating at <span class="text-white font-bold"><?php echo e($fleet_utilization); ?>% capacity</span>. 
                                <?php if($fleet_utilization < 80): ?>
                                    This is <span class="text-rose-300 font-bold underline">below optimal levels</span>. You are losing approximately ₱<?php echo e(number_format($fleet_pulse['idle_units'] * 1200)); ?> in daily potential revenue.
                                <?php else: ?>
                                    Excellent utilization! Your fleet is highly optimized.
                                <?php endif; ?>
                            </p>
                        </div>
                        <div class="flex gap-4">
                            <div class="w-8 h-8 rounded-full bg-white/10 flex items-center justify-center shrink-0">
                                <i data-lucide="shield-check" class="w-4 h-4 text-emerald-300"></i>
                            </div>
                            <p class="text-sm text-indigo-100/90 leading-relaxed">
                                Financial health is <span class="text-white font-bold"><?php echo e($net_income > 0 ? 'Stable' : 'At Risk'); ?></span>. 
                                Current shortage leakage is <span class="text-rose-300 font-bold"><?php echo e($revenue_leakage_pct); ?>%</span>. Reducing this to < 3% would add <span class="text-emerald-300 font-bold">₱<?php echo e(number_format($total_shortage * 0.5)); ?></span> to your bottom line.
                            </p>
                        </div>
                    </div>
                    <button onclick="switchTab('strategy')" class="mt-8 w-full py-3 bg-white text-indigo-900 rounded-xl font-black text-sm hover:bg-indigo-50 transition-all flex items-center justify-center gap-2 shadow-xl">
                        View AI Strategy <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    </button>
                </div>
                
                <div class="absolute -right-20 -bottom-20 w-64 h-64 bg-indigo-500/20 rounded-full blur-3xl group-hover:scale-150 transition-transform duration-1000"></div>
                <div class="absolute -left-10 -top-10 w-32 h-32 bg-white/5 rounded-full blur-xl animate-pulse"></div>
            </div>
        </div>
    </div>

    
    <div id="section-performance" class="hidden space-y-8 animate-in slide-in-from-bottom-4 duration-500">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            
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

    
    <div id="section-forecast" class="hidden space-y-8 animate-in slide-in-from-right-4 duration-500">
        <div class="bg-gradient-to-r from-indigo-900 to-slate-900 rounded-3xl p-8 text-white mb-8 shadow-xl">
            <h2 class="text-2xl font-black mb-2">Predictive Forecasting & Modeling</h2>
            <p class="text-indigo-200 text-sm mb-6 max-w-3xl leading-relaxed">
                Using advanced predictive algorithms and historical fleet performance data, this module projects your financial and operational standing for the upcoming periods. Use this data to prepare budgets, allocate maintenance funds, and set driver targets.
            </p>
            <div class="flex items-center gap-2 px-4 py-2 bg-white/10 rounded-xl w-fit">
                <i data-lucide="zap" class="w-4 h-4 text-amber-400"></i>
                <span class="text-xs font-bold text-white uppercase tracking-widest">Real-Time Projections</span>
            </div>
        </div>

        
        <div id="dss-forecast-panel" class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        </div>
        
        <div class="mt-8 p-6 bg-white rounded-3xl border border-slate-200 shadow-sm flex gap-4">
            <div class="p-3 bg-indigo-50 text-indigo-600 rounded-2xl h-fit">
                <i data-lucide="bar-chart" class="w-6 h-6"></i>
            </div>
            <div>
                <h4 class="text-sm font-black text-slate-800 mb-2">How to utilize these forecasts</h4>
                <ul class="space-y-3 text-xs text-slate-600">
                    <li class="flex items-start gap-2"><span class="text-indigo-500 font-black mt-0.5">•</span> <strong>Revenue Projection:</strong> Set your collection targets based on this number. If it's lower than expected, review unit availability.</li>
                    <li class="flex items-start gap-2"><span class="text-indigo-500 font-black mt-0.5">•</span> <strong>Operational Expenses:</strong> Ensure you have sufficient cash flow allocated to cover these anticipated overheads.</li>
                    <li class="flex items-start gap-2"><span class="text-indigo-500 font-black mt-0.5">•</span> <strong>Maintenance Reserve:</strong> Ring-fence this exact amount. Do not treat it as profit; these repairs are statistically guaranteed to happen.</li>
                </ul>
            </div>
        </div>
    </div>

    
    <div id="section-strategy" class="hidden space-y-8 animate-in zoom-in-95 duration-500">
        
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

                
                <div id="dss-error" class="hidden py-24 px-10 flex flex-col items-center justify-center">
                    <div class="w-16 h-16 bg-rose-50 text-rose-600 rounded-3xl flex items-center justify-center mb-6">
                        <i data-lucide="alert-circle" class="w-8 h-8"></i>
                    </div>
                    <p class="text-lg font-black text-slate-800">Analysis Interrupted</p>
                    <p id="dss-error-msg" class="text-sm text-slate-500 mt-2 mb-8">Server communication failed.</p>
                    <button onclick="loadDSSInsights(true)" class="px-8 py-3 bg-slate-900 text-white rounded-xl font-black text-xs">Retry Analysis</button>
                </div>

                
                <div id="dss-insights" class="hidden p-10 animate-in fade-in zoom-in-95 duration-700">
                    <div class="grid grid-cols-1 lg:grid-cols-4 gap-10">
                        
                        <div class="space-y-8">
                            <div class="p-6 bg-slate-50 rounded-3xl border border-slate-100">
                                <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4">Strategic Pulse</h4>
                                <div id="dss-stats-bar" class="space-y-4">
                                    
                                </div>
                            </div>

                            <div class="p-6 bg-indigo-50/50 rounded-3xl border border-indigo-100">
                                <h4 class="text-[10px] font-black text-indigo-600 uppercase tracking-widest mb-4">Operational Risks</h4>
                                <div id="dss-risks" class="space-y-4">
                                    
                                </div>
                            </div>
                        </div>

                        
                        <div class="lg:col-span-3">
                             <div id="dss-cards" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                
                             </div>
                        </div>
                    </div>

                    
                    <div class="mt-16 pt-10 border-t border-slate-100">
                        <div class="flex items-center gap-3 mb-8">
                            <i data-lucide="database" class="w-5 h-5 text-indigo-600"></i>
                            <h4 class="text-sm font-black text-slate-800">AI Data Lineage <span class="text-xs font-bold text-slate-400 ml-2">(How we calculated this)</span></h4>
                        </div>
                        <div id="dss-lineage" class="grid grid-cols-2 md:grid-cols-4 gap-6">
                            
                        </div>
                    </div>
                </div>

                
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

<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
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
        if (tab === 'performance') {
            window.dispatchEvent(new Event('resize'));
        }
    }

    // ── Chart Data ────────────────────────────────────────────────────────────
    const dailyData         = <?php echo json_encode($daily_trend, 15, 512) ?>;
    const expenseData       = <?php echo json_encode($expense_by_category, 15, 512) ?>;
    const monthlyRevenueData= <?php echo json_encode($monthlyRevenueData, 15, 512) ?>;
    const maintenanceCostData = <?php echo json_encode($maintenance_cost_trend, 15, 512) ?>;

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
            const url = '<?php echo e(route("analytics.ai-insights")); ?>' + (force ? '?refresh=1' : '');
            const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
            if (!res.ok) throw new Error('Server returned ' + res.status);
            const data = await res.json();

            document.getElementById('dss-stats-bar').innerHTML = renderStatsBar(data);
            document.getElementById('dss-forecast-panel').innerHTML = renderForecastPanel(data.forecast);
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
</script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\eurotaxisystem-main\resources\views/analytics/index.blade.php ENDPATH**/ ?>