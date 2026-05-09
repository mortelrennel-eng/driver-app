<?php $__env->startSection('title', 'Unit Profitability - Euro System'); ?>
<?php $__env->startSection('page-heading', 'Unit Profitability Analysis'); ?>
<?php $__env->startSection('page-subheading', 'Evaluate each unit\'s revenue versus expenses to determine profitability'); ?>

<?php $__env->startSection('content'); ?>
<style>
    .card-number-container {
        container-type: inline-size;
    }
    .auto-scale-text {
        font-size: 14px; /* Fallback */
        font-size: clamp(12px, 8cqw, 30px);
    }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .animate-fade-in {
        animation: fadeIn 0.6s ease-out forwards;
    }
    /* Hide scrollbar for Chrome, Safari and Opera */
    ::-webkit-scrollbar {
        display: none;
    }
    /* Hide scrollbar for IE, Edge and Firefox */
    html {
        scrollbar-width: none;
        -ms-overflow-style: none;
    }
</style>

    
    <div class="bg-gradient-to-br from-indigo-900 via-slate-900 to-black rounded-3xl p-8 mb-8 relative shadow-2xl border border-indigo-500/20 transition-all duration-500">
        <div class="absolute top-0 right-0 w-96 h-96 bg-indigo-500/10 rounded-full blur-3xl -mr-48 -mt-48"></div>
        <div class="absolute bottom-0 left-0 w-64 h-64 bg-blue-500/10 rounded-full blur-3xl -ml-32 -mb-32"></div>
        
        <div class="relative z-10 flex flex-col lg:flex-row items-center gap-8">
            <div class="flex-1 text-center lg:text-left">
                <div class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-500/20 rounded-full border border-indigo-400/30 mb-6">
                    <div class="w-2 h-2 bg-indigo-400 rounded-full animate-pulse"></div>
                    <span class="text-[10px] font-black text-indigo-200 uppercase tracking-[0.2em]">Next-Gen Intelligence</span>
                </div>
                <h2 class="text-3xl sm:text-4xl font-black text-white mb-4 tracking-tight leading-tight">AI Decision Support <span class="text-indigo-400">System (DSS)</span></h2>
                <p class="text-indigo-100/70 text-sm mb-8 leading-relaxed max-w-2xl">Leverage Gemini 1.5 Flash to analyze your fleet's profitability data. Get strategic insights on revenue leakage, maintenance efficiency, and ROI projections in real-time.</p>
                
                <button onclick="generateAiAnalysis()" id="ai-btn" class="inline-flex items-center gap-3 px-8 py-4 bg-indigo-600 hover:bg-indigo-500 text-white rounded-2xl font-black text-sm transition-all shadow-xl shadow-indigo-500/20 active:scale-95 group">
                    <i data-lucide="sparkles" class="w-5 h-5 group-hover:rotate-12 transition-transform"></i>
                    GENERATE STRATEGIC REPORT
                </button>
            </div>
            
            <div class="w-full lg:w-1/3 bg-white/5 backdrop-blur-xl rounded-3xl border border-white/10 p-6 shadow-inner">
                <div id="ai-status" class="flex flex-col items-center justify-center min-h-[160px]">
                    <div class="w-16 h-16 rounded-full bg-indigo-500/20 flex items-center justify-center mb-4">
                        <i data-lucide="brain-circuit" class="w-8 h-8 text-indigo-300"></i>
                    </div>
                    <p class="text-xs font-black text-indigo-200 uppercase tracking-widest">Ready to Analyze</p>
                    <p class="text-[10px] text-indigo-100/40 mt-1">Based on <?php echo e(count($full_profitability)); ?> active units</p>
                </div>
                <div id="ai-loader" class="hidden flex flex-col items-center justify-center min-h-[160px]">
                    <div class="w-12 h-12 border-4 border-indigo-500/30 border-t-indigo-400 rounded-full animate-spin mb-4"></div>
                    <p class="text-xs font-black text-indigo-200 uppercase tracking-widest animate-pulse">Processing Data...</p>
                </div>
            </div>
        </div>

        
        <div id="ai-result-container" class="hidden mt-8 pt-8 border-t border-white/10 animate-fade-in">
            <div class="bg-white/5 rounded-3xl p-6 sm:p-8 text-indigo-50 font-medium leading-relaxed prose prose-invert prose-indigo max-w-none shadow-inner ring-1 ring-white/10" id="ai-result-content">
                
            </div>
        </div>
    </div>

    
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 card-hover overflow-hidden card-number-container relative">
            <div class="px-2 py-4 sm:p-6 text-center relative z-10">
                <p class="text-[9px] sm:text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Total Units</p>
                <div class="flex justify-center items-center h-10 sm:h-12">
                    <p class="auto-scale-text font-black text-gray-800 whitespace-nowrap"><?php echo e($overview['total_units'] ?? 0); ?></p>
                </div>
            </div>
            <div class="absolute bottom-0 left-0 w-full h-1/2 opacity-10 pointer-events-none">
                <svg viewBox="0 0 100 20" class="w-full h-full"><path d="M0 20 Q 25 15, 50 18 T 100 12 L 100 20 L 0 20 Z" fill="#64748b"/></svg>
            </div>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 card-hover overflow-hidden card-number-container relative">
            <div class="px-2 py-4 sm:p-6 text-center relative z-10">
                <p class="text-[9px] sm:text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1 text-green-600">Total Boundary</p>
                <div class="flex justify-center items-center h-10 sm:h-12">
                    <p class="auto-scale-text font-black text-green-600 whitespace-nowrap"><?php echo e(formatCurrency($overview['total_boundary'] ?? 0)); ?></p>
                </div>
            </div>
            <div class="absolute bottom-0 left-0 w-full h-1/2 opacity-10 pointer-events-none">
                <svg viewBox="0 0 100 20" class="w-full h-full"><path d="M0 20 L 20 15 L 40 18 L 60 12 L 80 16 L 100 10 L 100 20 L 0 20 Z" fill="#22c55e"/></svg>
            </div>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 card-hover overflow-hidden card-number-container relative">
            <div class="px-2 py-4 sm:p-6 text-center relative z-10">
                <p class="text-[9px] sm:text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1 text-red-600">Total Expenses</p>
                <div class="flex justify-center items-center h-10 sm:h-12">
                    <p class="auto-scale-text font-black text-red-600 whitespace-nowrap"><?php echo e(formatCurrency($overview['total_expenses'] ?? 0)); ?></p>
                </div>
            </div>
            <div class="absolute bottom-0 left-0 w-full h-1/2 opacity-10 pointer-events-none">
                <svg viewBox="0 0 100 20" class="w-full h-full"><path d="M0 20 L 10 18 L 30 15 L 50 19 L 70 14 L 90 17 L 100 15 L 100 20 L 0 20 Z" fill="#ef4444"/></svg>
            </div>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 card-hover overflow-hidden card-number-container relative">
            <div class="px-2 py-4 sm:p-6 text-center relative z-10">
                <p class="text-[9px] sm:text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1 text-blue-600">Net Income</p>
                <?php $ni = $overview['net_income'] ?? 0; ?>
                <div class="flex justify-center items-center h-10 sm:h-12">
                    <p class="auto-scale-text font-black <?php echo e($ni >= 0 ? 'text-blue-600' : 'text-red-600'); ?> whitespace-nowrap"><?php echo e(formatCurrency($ni)); ?></p>
                </div>
            </div>
            <div class="absolute bottom-0 left-0 w-full h-1/2 opacity-10 pointer-events-none">
                <svg viewBox="0 0 100 20" class="w-full h-full"><path d="M0 20 Q 25 12, 50 15 T 100 5 L 100 20 L 0 20 Z" fill="#3b82f6"/></svg>
            </div>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 card-hover overflow-hidden card-number-container relative">
            <div class="px-2 py-4 sm:p-6 text-center relative z-10">
                <p class="text-[9px] sm:text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1 text-violet-600">Avg Profit Margin</p>
                <div class="flex justify-center items-center h-10 sm:h-12">
                    <p class="auto-scale-text font-black text-violet-600 whitespace-nowrap"><?php echo e(number_format($overview['avg_margin'] ?? 0, 1)); ?>%</p>
                </div>
            </div>
            <div class="absolute bottom-0 left-0 w-full h-1/2 opacity-10 pointer-events-none">
                <svg viewBox="0 0 100 20" class="w-full h-full"><path d="M0 20 L 20 18 L 40 16 L 60 19 L 80 15 L 100 17 L 100 20 L 0 20 Z" fill="#8b5cf6"/></svg>
            </div>
        </div>
    </div>

    
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-6">
        <form method="GET" action="<?php echo e(route('unit-profitability.index')); ?>" class="flex flex-col md:flex-row gap-4">
            <div class="flex-1">
                <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">From Date</label>
                <div class="relative group">
                    <i data-lucide="calendar" class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 group-focus-within:text-yellow-600 transition-colors"></i>
                    <input type="date" name="date_from" value="<?php echo e($date_from ?? date('Y-m-01')); ?>"
                        onchange="this.form.submit()"
                        class="block w-full pl-11 pr-4 py-3.5 bg-gray-50 border border-gray-100 rounded-2xl text-xs font-black shadow-sm focus:ring-4 focus:ring-yellow-500/10 focus:border-yellow-400 transition-all outline-none">
                </div>
            </div>
            <div class="flex-1">
                <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-1">To Date</label>
                <div class="relative group">
                    <i data-lucide="calendar" class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 group-focus-within:text-yellow-600 transition-colors"></i>
                    <input type="date" name="date_to" value="<?php echo e($date_to ?? date('Y-m-d')); ?>"
                        onchange="this.form.submit()"
                        class="block w-full pl-11 pr-4 py-3.5 bg-gray-50 border border-gray-100 rounded-2xl text-xs font-black shadow-sm focus:ring-4 focus:ring-yellow-500/10 focus:border-yellow-400 transition-all outline-none">
                </div>
            </div>
            <div class="flex-1">
                <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-1">Unit Selection</label>
                <div class="relative group">
                    <i data-lucide="car-front" class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 group-focus-within:text-yellow-600 transition-colors"></i>
                    <select name="unit_id" onchange="this.form.submit()"
                        class="block w-full pl-11 pr-4 py-3.5 bg-gray-50 border border-gray-100 rounded-2xl text-xs font-black shadow-sm focus:ring-4 focus:ring-yellow-500/10 focus:border-yellow-400 transition-all outline-none appearance-none cursor-pointer">
                        <option value="">All Units</option>
                        <?php $__currentLoopData = $units; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $unit): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($unit->id); ?>" <?php echo e(($selected_unit ?? '') == $unit->id ? 'selected' : ''); ?>>
                                <?php echo e($unit->plate_number); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
            </div>
        </form>
    </div>

    
    <div class="bg-white rounded-lg shadow overflow-hidden mb-6">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-800">Unit Profitability Details</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Unit</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Vehicle</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total Boundary</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Maintenance</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Other Exp.</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Net Income</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Margin%</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Performance</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php $__empty_1 = true; $__currentLoopData = $profitability; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php
                            $margin = $item->profit_margin ?? 0;
                            $perf = $margin > 60 ? 'Excellent' : ($margin > 40 ? 'Good' : ($margin > 20 ? 'Fair' : 'Poor'));
                            $perfColor = $margin > 60 ? 'bg-green-100 text-green-800' : ($margin > 40 ? 'bg-blue-100 text-blue-800' : ($margin > 20 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800'));
                        ?>
                        <tr class="hover:bg-gray-50 cursor-pointer group transition-all" onclick="openComputationModal('<?php echo e($item->id); ?>', '<?php echo e($item->plate_number); ?>')">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-gray-100 flex items-center justify-center text-[10px] font-black text-gray-500 group-hover:bg-yellow-100 group-hover:text-yellow-600 transition-colors">
                                        <?php echo e(substr($item->plate_number, 0, 3)); ?>

                                    </div>
                                    <div class="text-sm font-black text-gray-900"><?php echo e($item->plate_number); ?></div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 font-medium">
                                <?php echo e($item->make ?? ''); ?> <?php echo e($item->model ?? ''); ?>

                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-black text-green-600 font-mono tracking-tighter">
                                <?php echo e(formatCurrency($item->total_boundary ?? 0)); ?>

                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-red-500 font-mono tracking-tighter">
                                <?php echo e(formatCurrency($item->maintenance_cost ?? 0)); ?>

                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-red-500 font-mono tracking-tighter">
                                <?php echo e(formatCurrency($item->other_expenses ?? 0)); ?>

                            </td>
                            <td
                                class="px-6 py-4 whitespace-nowrap text-sm font-black tracking-tighter font-mono <?php echo e(($item->net_income ?? 0) >= 0 ? 'text-blue-600' : 'text-red-600'); ?>">
                                <?php echo e(formatCurrency($item->net_income ?? 0)); ?>

                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-2">
                                    <div class="flex-1 h-1.5 w-16 bg-gray-100 rounded-full overflow-hidden">
                                        <div class="h-full <?php echo e($margin > 50 ? 'bg-green-500' : ($margin > 20 ? 'bg-yellow-500' : 'bg-red-500')); ?>" style="width: <?php echo e(min(100, max(0, $margin))); ?>%"></div>
                                    </div>
                                    <span class="text-xs font-black <?php echo e($margin >= 0 ? 'text-green-600' : 'text-red-600'); ?>"><?php echo e(number_format($margin, 1)); ?>%</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 text-[10px] font-black uppercase tracking-widest rounded-lg <?php echo e($perfColor); ?> border border-current opacity-80">
                                    <i data-lucide="<?php echo e($margin > 50 ? 'shield-check' : ($margin > 20 ? 'activity' : 'alert-octagon')); ?>" class="w-3 h-3"></i>
                                    <?php echo e($perf); ?>

                                </span>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center text-gray-500">
                                <i data-lucide="bar-chart-2" class="w-12 h-12 mx-auto mb-4 text-gray-300"></i>
                                <p>No profitability data for the selected period.</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php if($profitability->hasPages()): ?>
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 pagination-container">
                <?php echo e($profitability->appends(request()->query())->links()); ?>

            </div>
        <?php endif; ?>
    </div>

    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-green-50">
                <h3 class="text-md font-semibold text-green-800 flex items-center gap-2">
                    <i data-lucide="trending-up" class="w-5 h-5 text-green-600"></i>
                    Top Performers
                </h3>
            </div>
            <div class="divide-y divide-gray-100">
                <?php
                    $topPerformers = collect($full_profitability)->filter(fn($u) => ($u->profit_margin ?? 0) >= 40)->sortByDesc('profit_margin')->take(5);
                ?>
                <?php $__empty_1 = true; $__currentLoopData = $topPerformers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $unit): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <div class="px-5 py-3 flex items-center justify-between">
                        <div>
                            <p class="text-sm font-semibold text-gray-800"><?php echo e($unit->plate_number); ?></p>
                            <p class="text-[10px] text-gray-500"><?php echo e($unit->make ?? ''); ?> <?php echo e($unit->model ?? ''); ?></p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-bold text-green-600"><?php echo e(formatCurrency($unit->net_income ?? 0)); ?></p>
                            <p class="text-xs text-gray-500"><?php echo e(number_format($unit->profit_margin ?? 0, 1)); ?>% margin</p>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div class="px-5 py-4 text-sm text-gray-400 text-center">No top performers yet.</div>
                <?php endif; ?>
            </div>
        </div>

        
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-red-50">
                <h3 class="text-md font-semibold text-red-800 flex items-center gap-2">
                    <i data-lucide="alert-triangle" class="w-5 h-5 text-red-600"></i>
                    Needs Attention
                </h3>
            </div>
            <div class="divide-y divide-gray-100">
                <?php
                    $needsAttention = collect($full_profitability)->filter(fn($u) => ($u->profit_margin ?? 0) < 40)->sortBy('profit_margin')->take(5);
                ?>
                <?php $__empty_1 = true; $__currentLoopData = $needsAttention; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $unit): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <div class="px-5 py-3 flex items-center justify-between">
                        <div>
                            <p class="text-sm font-semibold text-gray-800"><?php echo e($unit->plate_number); ?></p>
                            <p class="text-[10px] text-gray-500"><?php echo e($unit->make ?? ''); ?> <?php echo e($unit->model ?? ''); ?></p>
                        </div>
                        <div class="text-right">
                            <p
                                class="text-sm font-bold <?php echo e(($unit->net_income ?? 0) >= 0 ? 'text-yellow-600' : 'text-red-600'); ?>">
                                <?php echo e(formatCurrency($unit->net_income ?? 0)); ?></p>
                            <p class="text-xs text-gray-500"><?php echo e(number_format($unit->profit_margin ?? 0, 1)); ?>% margin</p>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div class="px-5 py-4 text-sm text-gray-400 text-center">All units are performing well!</div>
                <?php endif; ?>
            </div>
        </div>
    </div>


    
    <div id="computationModal" class="fixed inset-0 z-[100] hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-900 bg-opacity-70 transition-opacity backdrop-blur-sm" aria-hidden="true" onclick="closeComputationModal()"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full border border-gray-100">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-8 sm:pb-4">
                    <div class="flex justify-between items-center border-b border-gray-100 pb-6 mb-6">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 bg-yellow-100 rounded-2xl flex items-center justify-center">
                                <i data-lucide="calculator" class="w-6 h-6 text-yellow-600"></i>
                            </div>
                            <div>
                                <h3 class="text-xl font-black text-gray-900 leading-none mb-1" id="modal-title">Profitability Analysis Details</h3>
                                <p class="text-xs font-black text-yellow-600 uppercase tracking-widest" id="modal-unit-plate"></p>
                            </div>
                        </div>
                        <button type="button" onclick="closeComputationModal()" class="w-10 h-10 flex items-center justify-center rounded-xl bg-gray-50 text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition-all">
                            <i data-lucide="x" class="w-5 h-5"></i>
                        </button>
                    </div>

                    <div id="modal-content" class="min-h-[400px]">
                        
                        <div id="modal-loading" class="py-20 text-center">
                            <div class="inline-block animate-spin rounded-full h-10 w-10 border-4 border-yellow-500 border-t-transparent shadow-lg shadow-yellow-500/20"></div>
                            <p class="mt-6 text-gray-400 font-black uppercase tracking-widest text-[10px]">Analyzing records data...</p>
                        </div>

                        
                        <div id="modal-data" class="hidden pb-8">
                             
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function openComputationModal(unitId, plateNumber) {
            document.body.style.overflow = 'hidden';
            document.getElementById('computationModal').classList.remove('hidden');
            document.getElementById('modal-unit-plate').innerText = 'Vehicle Identification: ' + plateNumber;
            document.getElementById('modal-loading').classList.remove('hidden');
            document.getElementById('modal-data').classList.add('hidden');

            const dateFrom = document.querySelector('input[name="date_from"]').value;
            const dateTo = document.querySelector('input[name="date_to"]').value;

            fetch(`/unit-profitability/details?unit_id=${unitId}&date_from=${dateFrom}&date_to=${dateTo}`)
                .then(response => response.json())
                .then(data => {
                    renderModalContent(data);
                })
                .catch(err => {
                    console.error('Error fetching details:', err);
                    alert('Error loading unit details.');
                    closeComputationModal();
                });
        }

        function closeComputationModal() {
            document.body.style.overflow = 'auto';
            document.getElementById('computationModal').classList.add('hidden');
        }

        function renderModalContent(data) {
            const container = document.getElementById('modal-data');
            
            const totalRev = data.boundaries.reduce((acc, b) => acc + parseFloat(b.actual_boundary || 0), 0);
            const totalMaint = data.maintenances.reduce((acc, m) => acc + parseFloat(m.cost || 0), 0);
            const totalOther = data.expenses.reduce((acc, e) => acc + parseFloat(e.amount || 0), 0);
            const totalExp = totalMaint + totalOther;
            const netInc = totalRev - totalExp;

            container.innerHTML = `
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
                    <div class="bg-gradient-to-br from-green-50 to-white p-6 rounded-3xl border border-green-100 shadow-sm">
                        <p class="text-[10px] font-black text-green-700 uppercase tracking-widest mb-1">Gross Boundary</p>
                        <p class="text-3xl font-black text-green-600">${formatCurr(totalRev)}</p>
                    </div>
                    <div class="bg-gradient-to-br from-red-50 to-white p-6 rounded-3xl border border-red-100 shadow-sm">
                        <p class="text-[10px] font-black text-red-700 uppercase tracking-widest mb-1">Total Operating Exp.</p>
                        <p class="text-3xl font-black text-red-600">${formatCurr(totalExp)}</p>
                    </div>
                    <div class="bg-gradient-to-br from-blue-50 to-white p-6 rounded-3xl border border-blue-100 shadow-sm relative overflow-hidden">
                        <p class="text-[10px] font-black text-blue-700 uppercase tracking-widest mb-1">Calculated Net</p>
                        <p class="text-3xl font-black ${netInc >= 0 ? 'text-blue-600' : 'text-red-500'}">${formatCurr(netInc)}</p>
                        <i data-lucide="bar-chart" class="absolute -right-4 -bottom-4 w-16 h-16 text-blue-100 opacity-50"></i>
                    </div>
                </div>

                <div class="space-y-12">
                    
                    <div class="bg-gray-50/50 rounded-3xl p-6 border border-gray-100">
                        <div class="flex items-center justify-between mb-6">
                            <h4 class="text-xs font-black text-gray-400 uppercase tracking-widest flex items-center gap-2">
                                <i data-lucide="wallet" class="w-4 h-4 text-green-500"></i>
                                Boundary Income Stream
                            </h4>
                            <span class="px-3 py-1 bg-white rounded-full border border-gray-100 text-[10px] font-black text-gray-500">${data.boundaries.length} Remit Records</span>
                        </div>
                        <div class="max-h-64 overflow-y-auto custom-scrollbar pr-2">
                            <table class="min-w-full text-xs">
                                <thead class="bg-white/80 backdrop-blur sticky top-0 border-b border-gray-100">
                                    <tr>
                                        <th class="px-4 py-3 text-left font-black text-gray-400 uppercase tracking-tight">Remittance Date</th>
                                        <th class="px-4 py-3 text-right font-black text-gray-400 uppercase tracking-tight">Amount Remitted</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    ${data.boundaries.map(b => `
                                        <tr class="hover:bg-white transition-colors">
                                            <td class="px-4 py-4 text-gray-600 font-medium">${new Date(b.date).toLocaleDateString(undefined, {month: 'long', day: 'numeric', year: 'numeric'})}</td>
                                            <td class="px-4 py-4 text-right font-black text-green-600 font-mono tracking-tighter">${formatCurr(b.actual_boundary)}</td>
                                        </tr>
                                    `).join('') || '<tr><td colspan="2" class="py-12 text-center text-gray-400 font-medium italic">No boundary records for this period.</td></tr>'}
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                        
                        <div class="bg-gray-50/50 rounded-3xl p-6 border border-gray-100">
                            <div class="flex items-center justify-between mb-6">
                                <h4 class="text-xs font-black text-gray-400 uppercase tracking-widest flex items-center gap-2">
                                    <i data-lucide="wrench" class="w-4 h-4 text-red-500"></i>
                                    Maintenance Costs
                                </h4>
                            </div>
                            <div class="max-h-64 overflow-y-auto custom-scrollbar pr-2">
                                <table class="min-w-full text-xs">
                                    <thead class="bg-white/80 backdrop-blur sticky top-0 border-b border-gray-100">
                                        <tr>
                                            <th class="px-3 py-3 text-left font-black text-gray-400 uppercase tracking-tight">Date</th>
                                            <th class="px-3 py-3 text-left font-black text-gray-400 uppercase tracking-tight">Type/Desc</th>
                                            <th class="px-3 py-3 text-right font-black text-gray-400 uppercase tracking-tight">Cost</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100">
                                        ${data.maintenances.map(m => `
                                            <tr class="hover:bg-white transition-colors">
                                                <td class="px-3 py-4 text-gray-600 font-medium">${new Date(m.date_started).toLocaleDateString(undefined, {month: 'short', day: 'numeric'})}</td>
                                                <td class="px-3 py-4 text-gray-900 font-semibold truncate max-w-[120px]" title="${m.description}">${m.description}</td>
                                                <td class="px-3 py-4 text-right font-black text-red-600 font-mono tracking-tighter">${formatCurr(m.cost)}</td>
                                            </tr>
                                        `).join('') || '<tr><td colspan="3" class="py-8 text-center text-gray-300 italic">No maintenance logs.</td></tr>'}
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        
                        <div class="bg-gray-50/50 rounded-3xl p-6 border border-gray-100">
                            <div class="flex items-center justify-between mb-6">
                                <h4 class="text-xs font-black text-gray-400 uppercase tracking-widest flex items-center gap-2">
                                    <i data-lucide="receipt" class="w-4 h-4 text-violet-500"></i>
                                    Other Expenses
                                </h4>
                            </div>
                            <div class="max-h-64 overflow-y-auto custom-scrollbar pr-2">
                                <table class="min-w-full text-xs">
                                    <thead class="bg-white/80 backdrop-blur sticky top-0 border-b border-gray-100">
                                        <tr>
                                            <th class="px-3 py-3 text-left font-black text-gray-400 uppercase tracking-tight">Date</th>
                                            <th class="px-3 py-3 text-left font-black text-gray-400 uppercase tracking-tight">Desc</th>
                                            <th class="px-3 py-3 text-right font-black text-gray-400 uppercase tracking-tight">Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100">
                                        ${data.expenses.map(e => `
                                            <tr class="hover:bg-white transition-colors">
                                                <td class="px-3 py-4 text-gray-600 font-medium">${new Date(e.date).toLocaleDateString(undefined, {month: 'short', day: 'numeric'})}</td>
                                                <td class="px-3 py-4 text-gray-900 font-semibold truncate max-w-[120px]" title="${e.description}">${e.description}</td>
                                                <td class="px-3 py-4 text-right font-black text-red-600 font-mono tracking-tighter">${formatCurr(e.amount)}</td>
                                            </tr>
                                        `).join('') || '<tr><td colspan="3" class="py-8 text-center text-gray-300 italic">No other expenses.</td></tr>'}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            document.getElementById('modal-loading').classList.add('hidden');
            document.getElementById('modal-data').classList.remove('hidden');
            lucide.createIcons();
        }

        function formatCurr(val) {
            return '₱' + parseFloat(val || 0).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
        }

        async function generateAiAnalysis() {
            const btn = document.getElementById('ai-btn');
            const status = document.getElementById('ai-status');
            const loader = document.getElementById('ai-loader');
            const resultContainer = document.getElementById('ai-result-container');
            const resultContent = document.getElementById('ai-result-content');

            // Set Loading State
            btn.disabled = true;
            btn.classList.add('opacity-50', 'cursor-not-allowed');
            status.classList.add('hidden');
            loader.classList.remove('hidden');
            resultContainer.classList.add('hidden');

            const dateFrom = document.querySelector('input[name="date_from"]').value;
            const dateTo = document.querySelector('input[name="date_to"]').value;

            try {
                const url = `<?php echo e(route('unit-profitability.ai-dss')); ?>?date_from=${encodeURIComponent(dateFrom)}&date_to=${encodeURIComponent(dateTo)}`;
                const response = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });

                const contentType = response.headers.get('content-type') || '';
                if (!contentType.includes('application/json')) {
                    const text = await response.text();
                    throw new Error(`AI DSS returned non-JSON (${response.status}). ${text?.slice(0, 120) || ''}`);
                }
                const data = await response.json();

                if (data.success) {
                    resultContent.innerHTML = marked.parse(data.analysis);
                    resultContainer.classList.remove('hidden');
                    resultContainer.scrollIntoView({ behavior: 'smooth', block: 'start' });
                } else {
                    alert('AI Analysis failed: ' + (data.message || 'Unknown error'));
                    loader.classList.add('hidden');
                    status.classList.remove('hidden');
                }
            } catch (error) {
                console.error('AI Error:', error);
                alert('AI Service error. Please try again later.\n\n' + (error?.message || 'Unknown error'));
            } finally {
                btn.disabled = false;
                btn.classList.remove('opacity-50', 'cursor-not-allowed');
                status.classList.remove('hidden');
                loader.classList.add('hidden');
            }
        }
    </script>
    <script src="<?php echo e(asset('assets/marked.min.js')); ?>"></script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\eurotaxisystem-main\resources\views/unit-profitability/index.blade.php ENDPATH**/ ?>