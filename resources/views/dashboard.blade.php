@extends('layouts.app')

@section('title', 'Euro Taxi System | Professional Fleet Management Dashboard')
@section('page-heading', 'Euro Taxi System')
@section('page-subheading', 'Professional taxi fleet management and real-time tracking solutions')

@push('styles')
    <style>
        @media print {
            @page {
                margin: 0;
            }
            * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            body * {
                visibility: hidden;
            }
            #incomeReport, #incomeReport *,
            #expensesReport, #expensesReport * {
                visibility: visible !important;
            }
            #incomeReport, #expensesReport {
                position: absolute;
                left: 0;
                top: 0;
                width: 100% !important;
                max-width: none !important;
                border: none !important;
                box-shadow: none !important;
                padding: 15mm !important;
                margin: 0 !important;
                background: white !important;
            }
            #netIncomeModal, #expensesModal {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
                background: white !important;
                visibility: visible !important;
            }
            .print-only {
                display: block !important;
            }
            .no-print {
                display: none !important;
            }
            /* Hidden elements must stay hidden even in print */
            .hidden {
                display: none !important;
                visibility: hidden !important;
            }
        }
        
        @media screen {
            .print-only {
                display: none !important;
            }
        }
        
        .receipt-paper::before {
            content: '';
            position: absolute;
            top: -10px;
            left: 0;
            right: 0;
            height: 10px;
            background: linear-gradient(135deg, transparent 33.333%, white 33.333%, white 66.666%, transparent 66.666%), 
                        linear-gradient(45deg, transparent 33.333%, white 33.333%, white 66.666%, transparent 66.666%);
            background-size: 15px 15px;
            background-repeat: repeat-x;
        }
        
        .receipt-paper::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 0;
            right: 0;
            height: 10px;
            background: linear-gradient(-135deg, transparent 33.333%, white 33.333%, white 66.666%, transparent 66.666%), 
                        linear-gradient(-45deg, transparent 33.333%, white 33.333%, white 66.666%, transparent 66.666%);
            background-size: 15px 15px;
            background-repeat: repeat-x;
        }

        @keyframes drawChart {
            0% { clip-path: polygon(0 0, 0% 0, 0% 100%, 0 100%); opacity: 0; }
            20% { opacity: 1; }
            100% { clip-path: polygon(0 0, 100% 0, 100% 100%, 0 100%); opacity: 1; }
        }
        .card-hover::after {
            content: '';
            position: absolute;
            left: 0;
            bottom: 0;
            width: 100%;
            height: 75px;
            background-size: 100% 100%;
            background-repeat: no-repeat;
            opacity: 0;
            transition: none !important;
            z-index: 0;
        }
        .wave-blue::after { background-image: url('data:image/svg+xml;utf8,<svg viewBox="0 0 100 50" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg"><polygon fill="rgba(59,130,246,0.15)" stroke="rgba(59,130,246,0.4)" stroke-width="1.5" vector-effect="non-scaling-stroke" stroke-linejoin="miter" points="0,50 0,35 15,20 30,30 45,10 60,25 75,5 90,15 100,0 100,50" /></svg>'); }
        .wave-emerald::after { background-image: url('data:image/svg+xml;utf8,<svg viewBox="0 0 100 50" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg"><polygon fill="rgba(16,185,129,0.15)" stroke="rgba(16,185,129,0.4)" stroke-width="1.5" vector-effect="non-scaling-stroke" stroke-linejoin="miter" points="0,50 0,35 15,20 30,30 45,10 60,25 75,5 90,15 100,0 100,50" /></svg>'); }
        .wave-green::after { background-image: url('data:image/svg+xml;utf8,<svg viewBox="0 0 100 50" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg"><polygon fill="rgba(34,197,94,0.15)" stroke="rgba(34,197,94,0.4)" stroke-width="1.5" vector-effect="non-scaling-stroke" stroke-linejoin="miter" points="0,50 0,35 15,20 30,30 45,10 60,25 75,5 90,15 100,0 100,50" /></svg>'); }
        .wave-orange::after { background-image: url('data:image/svg+xml;utf8,<svg viewBox="0 0 100 50" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg"><polygon fill="rgba(249,115,22,0.15)" stroke="rgba(249,115,22,0.4)" stroke-width="1.5" vector-effect="non-scaling-stroke" stroke-linejoin="miter" points="0,50 0,35 15,20 30,30 45,10 60,25 75,5 90,15 100,0 100,50" /></svg>'); }
        .wave-indigo::after { background-image: url('data:image/svg+xml;utf8,<svg viewBox="0 0 100 50" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg"><polygon fill="rgba(99,102,241,0.15)" stroke="rgba(99,102,241,0.4)" stroke-width="1.5" vector-effect="non-scaling-stroke" stroke-linejoin="miter" points="0,50 0,35 15,20 30,30 45,10 60,25 75,5 90,15 100,0 100,50" /></svg>'); }
        .wave-rose::after { background-image: url('data:image/svg+xml;utf8,<svg viewBox="0 0 100 50" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg"><polygon fill="rgba(244,63,94,0.15)" stroke="rgba(244,63,94,0.4)" stroke-width="1.5" vector-effect="non-scaling-stroke" stroke-linejoin="miter" points="0,50 0,35 15,20 30,30 45,10 60,25 75,5 90,15 100,0 100,50" /></svg>'); }
        .wave-violet::after { background-image: url('data:image/svg+xml;utf8,<svg viewBox="0 0 100 50" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg"><polygon fill="rgba(139,92,246,0.15)" stroke="rgba(139,92,246,0.4)" stroke-width="1.5" vector-effect="non-scaling-stroke" stroke-linejoin="miter" points="0,50 0,35 15,20 30,30 45,10 60,25 75,5 90,15 100,0 100,50" /></svg>'); }
        .card-hover.in-view::after {
            animation: drawChart 1s ease-out forwards !important;
        }
    </style>
@endpush
@section('content')


    <script>
        // Inject initial stats for JS to prevent flickering on load
        window.__INITIAL_STATS__ = @json($stats);
    </script>

    <!-- Stats Grid -->
    <div class="grid grid-cols-2 gap-3 sm:gap-5 md:grid-cols-2 lg:grid-cols-4">

        {{-- Total Units --}}
        <div onclick="showUnitsModal()" class="card-hover wave-blue cursor-pointer group relative overflow-hidden rounded-2xl shadow-sm border border-blue-100 bg-gradient-to-br from-blue-50 to-indigo-50/70">
            
            <div class="relative p-3.5 sm:p-5 flex items-center justify-between z-20">
                <div class="flex-1 min-w-0">
                    <p class="text-blue-400 text-[9px] sm:text-[10px] font-black uppercase tracking-widest mb-1">Total Units</p>
                    <p class="text-gray-900 text-xl sm:text-3xl font-black leading-none mb-1" data-stat="active_units">{{ $stats['active_units'] }}</p>
                    <p class="text-blue-400 text-[10px] sm:text-xs font-medium"><span class="text-emerald-600 font-bold" data-stat="roi_achieved">{{ $stats['roi_units'] }}</span> ROI Achieved</p>
                </div>
                <div class="p-1.5 sm:p-3 bg-blue-100 rounded-xl sm:rounded-2xl border border-blue-200 shadow-sm flex-shrink-0">
                    <i data-lucide="car" class="w-5 h-5 sm:w-7 sm:h-7 text-blue-600"></i>
                </div>
            </div>
            <i data-lucide="car" stroke-width="1" class="absolute right-0 bottom-0 w-24 h-24 -rotate-12 pointer-events-none" style="opacity: 0.15 !important; color: #2563eb !important; z-index: 5 !important;"></i>
        </div>

        {{-- Daily Boundary Collection --}}
        <div onclick="showDailyBoundaryModal()" class="card-hover wave-emerald cursor-pointer group relative overflow-hidden rounded-2xl shadow-sm border border-emerald-100 bg-gradient-to-br from-emerald-50 to-teal-50/70">
            
            <div class="relative p-3.5 sm:p-5 flex items-center justify-between z-20">
                <div class="flex-1 min-w-0">
                    <p class="text-emerald-500 text-[9px] sm:text-[10px] font-black uppercase tracking-widest mb-1">Boundary Revenue</p>
                    <div class="flex flex-col">
                        <span class="text-gray-900 text-lg sm:text-2xl font-black leading-none mb-0.5" data-stat="today_boundary">{{ formatCurrency($stats['today_boundary']) }}</span>
                        <span class="text-[9px] sm:text-[10px] font-bold text-emerald-600 uppercase">Today</span>
                    </div>
                    <div class="mt-2 pt-2 sm:mt-3 sm:pt-3 border-t border-emerald-100/50">
                        <p class="text-gray-900 text-base sm:text-lg font-black leading-none mb-0.5" data-stat="month_boundary">{{ formatCurrency($stats['month_boundary'] ?? 0) }}</p>
                        <p class="text-emerald-500 text-[8px] sm:text-[9px] font-black uppercase tracking-widest">This Month</p>
                    </div>
                </div>
                <div class="p-1.5 sm:p-3 bg-emerald-100 rounded-xl sm:rounded-2xl border border-emerald-200 shadow-sm flex-shrink-0">
                    <i data-lucide="banknote" class="w-5 h-5 sm:w-7 sm:h-7 text-emerald-600"></i>
                </div>
            </div>
            <i data-lucide="banknote" stroke-width="1" class="absolute right-0 bottom-0 w-24 h-24 -rotate-12 pointer-events-none" style="opacity: 0.15 !important; color: #059669 !important; z-index: 5 !important;"></i>
        </div>

        {{-- Net Income --}}
        <div onclick="showNetIncomeModal()" class="card-hover wave-green cursor-pointer group relative overflow-hidden rounded-2xl shadow-sm border border-green-100 bg-gradient-to-br from-green-50 to-lime-50/70">
            
            <div class="relative p-3.5 sm:p-5 flex items-center justify-between z-20">
                <div class="flex-1 min-w-0">
                    <p class="text-green-500 text-[9px] sm:text-[10px] font-black uppercase tracking-widest mb-1">Net Income (Kita)</p>
                    <div class="flex flex-col">
                        <span class="text-gray-900 text-lg sm:text-2xl font-black leading-none mb-0.5" data-stat="net_income">{{ formatCurrency($stats['net_income']) }}</span>
                        <span class="text-[9px] sm:text-[10px] font-bold text-green-600 uppercase">Today</span>
                    </div>
                    <div class="mt-2 pt-2 sm:mt-3 sm:pt-3 border-t border-green-100/50">
                        <p class="text-gray-900 text-base sm:text-lg font-black leading-none mb-0.5" data-stat="net_income_month">{{ formatCurrency($stats['net_income_month'] ?? 0) }}</p>
                        <p class="text-green-500 text-[8px] sm:text-[9px] font-black uppercase tracking-widest">This Month</p>
                    </div>
                </div>
                <div class="p-1.5 sm:p-3 bg-green-100 rounded-xl sm:rounded-2xl border border-green-200 shadow-sm flex-shrink-0">
                    <i data-lucide="trending-up" class="w-5 h-5 sm:w-7 sm:h-7 text-green-600"></i>
                </div>
            </div>
            <i data-lucide="trending-up" stroke-width="1" class="absolute right-0 bottom-0 w-24 h-24 -rotate-12 pointer-events-none" style="opacity: 0.15 !important; color: #16a34a !important; z-index: 5 !important;"></i>
        </div>

        {{-- Units Under Maintenance --}}
        <div onclick="showMaintenanceUnitsModal()" class="card-hover wave-orange cursor-pointer group relative overflow-hidden rounded-2xl shadow-sm border border-orange-100 bg-gradient-to-br from-orange-50 to-amber-50/70">
            
            <div class="relative p-3.5 sm:p-5 flex items-center justify-between z-20">
                <div class="flex-1 min-w-0">
                    <p class="text-orange-400 text-[9px] sm:text-[10px] font-black uppercase tracking-widest mb-1">Under Maintenance</p>
                    <p class="text-gray-900 text-xl sm:text-3xl font-black leading-none mb-1" data-stat="maintenance_units">{{ $stats['maintenance_units'] }}</p>
                    <p class="text-orange-400 text-[10px] sm:text-xs font-medium truncate" data-stat="maintenance_subtitle">Ongoing units</p>
                </div>
                <div class="p-1.5 sm:p-3 bg-orange-100 rounded-xl sm:rounded-2xl border border-orange-200 shadow-sm flex-shrink-0">
                    <i data-lucide="wrench" class="w-5 h-5 sm:w-7 sm:h-7 text-orange-600"></i>
                </div>
            </div>
            <i data-lucide="wrench" stroke-width="1" class="absolute right-0 bottom-0 w-24 h-24 -rotate-12 pointer-events-none" style="opacity: 0.15 !important; color: #ea580c !important; z-index: 5 !important;"></i>
        </div>

    </div>

    <!-- Quick Stats -->
    <div class="mt-4 grid grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4">

        {{-- Active Drivers --}}
        <div onclick="showActiveDriversModal()" class="card-hover wave-indigo cursor-pointer group relative overflow-hidden rounded-2xl shadow-sm border border-indigo-100 bg-gradient-to-br from-indigo-50 to-violet-50/70">
            
            <div class="relative p-3.5 sm:p-5 flex items-center justify-between z-20">
                <div class="flex-1 min-w-0">
                    <p class="text-indigo-400 text-[9px] sm:text-[10px] font-black uppercase tracking-widest mb-1">Active Drivers</p>
                    <p class="text-gray-900 text-xl sm:text-3xl font-black leading-none" data-stat="active_drivers">{{ $stats['active_drivers'] }}</p>
                </div>
                <div class="p-1.5 sm:p-3 bg-indigo-100 rounded-xl sm:rounded-2xl border border-indigo-200 shadow-sm flex-shrink-0">
                    <i data-lucide="users" class="w-5 h-5 sm:w-7 sm:h-7 text-indigo-600"></i>
                </div>
            </div>
            <i data-lucide="users" stroke-width="1" class="absolute right-0 bottom-0 w-24 h-24 -rotate-12 pointer-events-none" style="opacity: 0.15 !important; color: #4f46e5 !important; z-index: 5 !important;"></i>
        </div>

        {{-- Total Expenses Today --}}
        <div onclick="showExpensesModal()" class="card-hover wave-rose cursor-pointer group relative overflow-hidden rounded-2xl shadow-sm border border-rose-100 bg-gradient-to-br from-rose-50 to-red-50/70">
            
            <div class="relative p-3.5 sm:p-5 flex items-center justify-between z-20">
                <div class="flex-1 min-w-0">
                    <p class="text-rose-400 text-[9px] sm:text-[10px] font-black uppercase tracking-widest mb-1">Expenses Today</p>
                    <p class="text-gray-900 text-lg sm:text-2xl font-black leading-none" data-stat="today_expenses">{{ formatCurrency($stats['total_expenses_today']) }}</p>
                </div>
                <div class="p-1.5 sm:p-3 bg-rose-100 rounded-xl sm:rounded-2xl border border-rose-200 shadow-sm flex-shrink-0">
                    <i data-lucide="trending-down" class="w-5 h-5 sm:w-7 sm:h-7 text-rose-600"></i>
                </div>
            </div>
            <i data-lucide="trending-down" stroke-width="1" class="absolute right-0 bottom-0 w-24 h-24 -rotate-12 pointer-events-none" style="opacity: 0.15 !important; color: #e11d48 !important; z-index: 5 !important;"></i>
        </div>

        {{-- Coding Units Today --}}
        <div onclick="showCodingUnitsModal()" class="col-span-2 lg:col-span-1 card-hover wave-violet cursor-pointer group relative overflow-hidden rounded-2xl shadow-sm border border-violet-100 bg-gradient-to-br from-violet-50 to-purple-50/70">
            
            <div class="relative p-3.5 sm:p-5 flex items-center justify-between z-20">
                <div class="flex-1 min-w-0">
                    <p class="text-violet-400 text-[9px] sm:text-[10px] font-black uppercase tracking-widest mb-1">Coding Units Today</p>
                    <p class="text-gray-900 text-xl sm:text-3xl font-black leading-none mb-1" data-stat="coding_units">{{ $stats['coding_units'] }}</p>
                    <p class="text-violet-400 text-[9px] sm:text-[10px] font-bold uppercase tracking-tight">{{ now()->timezone('Asia/Manila')->format('l') }}</p>
                </div>
                <div class="p-1.5 sm:p-3 bg-violet-100 rounded-xl sm:rounded-2xl border border-violet-200 shadow-sm flex-shrink-0">
                    <i data-lucide="calendar" class="w-5 h-5 sm:w-7 sm:h-7 text-violet-600"></i>
                </div>
            </div>
            <i data-lucide="calendar" stroke-width="1" class="absolute right-0 bottom-0 w-24 h-24 -rotate-12 pointer-events-none" style="opacity: 0.15 !important; color: #7c3aed !important; z-index: 5 !important;"></i>
        </div>

    </div>

    <!-- Unit Performance (Full Width) -->
    <div class="mt-4 bg-white rounded-lg shadow-lg border border-gray-100 overflow-hidden">
        <div class="p-4 border-b bg-gray-50/50 flex justify-between items-center">
            <div class="flex items-center gap-2">
                <div class="p-1.5 bg-blue-100 rounded-lg">
                    <i data-lucide="bar-chart-3" class="w-4 h-4 text-blue-600"></i>
                </div>
                <div>
                    <h3 class="text-base font-bold text-gray-900 uppercase tracking-tight">Unit Performance</h3>
                    <p class="text-[11px] text-gray-500 mt-0.5 leading-tight">Total actual boundary collections over the <strong>last 30 days</strong> vs their 30-day target.</p>
                </div>
            </div>
            <span class="text-[10px] font-black text-blue-600 bg-blue-50 px-2 py-1 rounded-full uppercase tracking-widest border border-blue-100">Top 10 Performers</span>
        </div>
        <div class="grid grid-cols-1 lg:grid-cols-4">
            <div class="lg:col-span-3 p-6">
                <div style="height: 380px;">
                    <canvas id="unitPerformanceChart"></canvas>
                </div>
            </div>
            <!-- Executive Insight Panel -->
            <div class="bg-gray-50 p-6 border-l border-gray-100 flex flex-col justify-center">
                <h4 class="text-xs font-black text-gray-400 uppercase tracking-widest mb-6">Executive Insights</h4>
                <div class="space-y-8">
                    <div>
                        <p class="text-[10px] text-gray-400 font-bold uppercase tracking-wider mb-2">Fleet Health</p>
                        <div class="flex items-end gap-2">
                            <p class="text-3xl font-black text-gray-900 leading-none">82%</p>
                            <p class="text-xs font-bold text-green-600 flex items-center mb-0.5">
                                <i data-lucide="trending-up" class="w-3 h-3 mr-0.5"></i> +2.4%
                            </p>
                        </div>
                        <p class="text-[11px] text-gray-500 mt-2 leading-relaxed font-medium">Most units are meeting over 80% of their monthly boundary targets.</p>
                    </div>
                    
                    <div class="pt-6 border-t border-gray-200">
                        <p class="text-[10px] text-gray-400 font-bold uppercase tracking-wider mb-2">Top Performer</p>
                        <p class="text-base font-black text-gray-900" id="insightTopPlate">--</p>
                        <p class="text-[11px] text-gray-500 mt-2 font-medium">Consistency in daily collections makes this your most reliable asset.</p>
                    </div>

                    <div class="pt-6 border-t border-gray-200">
                        <p class="text-[10px] text-gray-400 font-bold uppercase tracking-wider mb-3">Legend</p>
                        <div class="space-y-3">
                            <div class="flex items-center gap-3">
                                <div class="w-3 h-3 rounded bg-blue-500 shadow-sm"></div>
                                <span class="text-[10px] font-black text-gray-600 uppercase tracking-widest">Actual Collection</span>
                            </div>
                            <div class="flex items-center gap-3">
                                <div class="w-3 h-3 rounded border-2 border-amber-500 bg-amber-500/20"></div>
                                <span class="text-[10px] font-black text-gray-600 uppercase tracking-widest">Monthly Target</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Revenue Trend (Full Width) -->
    <div class="mt-4 bg-white rounded-lg shadow overflow-hidden">
        <div class="p-4 border-b">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <div class="p-1.5 bg-blue-50 rounded-lg">
                        <i data-lucide="trending-up" class="w-4 h-4 text-blue-600"></i>
                    </div>
                    <h3 class="text-base font-bold text-gray-900 uppercase tracking-tight">Revenue Trend</h3>
                </div>
                <div class="flex gap-2">
                    <button onclick="updateRevenueTrend('7')" id="btn-7days" class="px-3 py-1 text-[10px] font-bold uppercase rounded-lg bg-blue-600 text-white hover:bg-blue-700 transition-all shadow-sm">
                        7 Days
                    </button>
                    <button onclick="updateRevenueTrend('30')" id="btn-30days" class="px-3 py-1 text-[10px] font-bold uppercase rounded-lg bg-gray-100 text-gray-600 hover:bg-gray-200 transition-all border border-gray-200">
                        30 Days
                    </button>
                    <button onclick="updateRevenueTrend('90')" id="btn-90days" class="px-3 py-1 text-[10px] font-bold uppercase rounded-lg bg-gray-100 text-gray-600 hover:bg-gray-200 transition-all border border-gray-200">
                        3 Months
                    </button>
                    <button onclick="updateRevenueTrend('365')" id="btn-365days" class="px-3 py-1 text-[10px] font-bold uppercase rounded-lg bg-gray-100 text-gray-600 hover:bg-gray-200 transition-all border border-gray-200">
                        1 Year
                    </button>
                </div>
            </div>
        </div>
        <div class="p-4">
            <canvas id="revenueTrendChart" style="width: 100%; height: 320px;"></canvas>
        </div>
    </div>

    <!-- Secondary Analytics Grid (Aligned for Balance) -->
    <div class="mt-4 grid grid-cols-1 lg:grid-cols-2 gap-4">

        <div class="bg-white rounded-lg shadow">
            <div class="p-4 border-b">
                <h3 class="text-base font-semibold text-gray-900">Expense Breakdown & Distribution</h3>
            </div>
            <div class="p-4">
                <canvas id="expenseBreakdownChart" width="400" height="200"></canvas>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow">
            <div class="p-4 border-b">
                <h3 class="text-base font-semibold text-gray-900">Weekly Financial Overview</h3>
            </div>
            <div class="p-4">
                <canvas id="weeklyChart" width="400" height="200"></canvas>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow">
            <div class="p-4 border-b">
                <h3 class="text-base font-semibold text-gray-900">Unit Status Distribution</h3>
            </div>
            <div class="p-4">
                <canvas id="unitStatusChart" width="400" height="200"></canvas>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow">
            <div class="p-4 border-b">
                <h3 class="text-base font-semibold text-gray-900">Top Performing Drivers</h3>
            </div>
            <div class="p-4">
                <canvas id="topDriversChart" width="400" height="200"></canvas>
            </div>
        </div>
    </div>

    <!-- Maintenance Units Modal -->
<div id="maintenanceUnitsModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm hidden z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-6xl h-[95vh] flex flex-col overflow-hidden">
        <!-- Modal Header -->
        <div class="p-4 border-b bg-gradient-to-r from-orange-600 via-amber-600 to-yellow-600 flex-shrink-0">
            <div class="flex items-center justify-between mb-3">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-white/20 backdrop-blur-sm rounded-lg border border-white/30">
                        <i data-lucide="wrench" class="w-6 h-6 text-white"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-white">Units Under Maintenance</h3>
                        <p class="text-orange-100 text-xs font-medium">Complete maintenance tracking details</p>
                    </div>
                </div>
                <button onclick="hideMaintenanceUnitsModal()" class="text-white/80 hover:text-white hover:bg-white/20 p-2 rounded-lg transition-all duration-200 backdrop-blur-sm">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
            
            <!-- Search and Date Filter -->
            <div class="flex items-center gap-3">
                <div class="relative flex-1">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i data-lucide="search" class="w-4 h-4 text-white/60"></i>
                    </div>
                    <input type="search" 
                        id="maintenanceSearchInput"
                        placeholder="Search by unit number, plate, or maintenance type..."
                        class="w-full pl-10 pr-4 py-2 bg-white/20 backdrop-blur-sm border border-white/30 rounded-lg text-white placeholder-white/70 focus:outline-none focus:ring-2 focus:ring-white/50 focus:border-transparent transition-all duration-200 text-sm"
                        onkeyup="filterMaintenanceUnits()"
                     autocomplete="new-password" spellcheck="false" autocorrect="off" autocapitalize="off" readonly onfocus="this.removeAttribute('readonly');">
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                        <button onclick="clearMaintenanceSearch()" class="text-white/60 hover:text-white transition-colors">
                            <i data-lucide="x-circle" class="w-4 h-4"></i>
                        </button>
                    </div>
                </div>
                <div class="flex items-center gap-1 bg-white/20 backdrop-blur-sm p-1 rounded-xl border border-white/30">
                    <button onclick="setMaintenanceFilter('all')" id="mFilterAll" class="px-4 py-1.5 rounded-lg text-sm font-bold transition-all duration-200 bg-white text-orange-600 shadow-sm">
                        All
                    </button>
                    <button onclick="setMaintenanceFilter('preventive')" id="mFilterPreventive" class="px-4 py-1.5 rounded-lg text-sm font-medium transition-all duration-200 text-white hover:bg-white/10">
                        Preventive
                    </button>
                    <button onclick="setMaintenanceFilter('corrective')" id="mFilterCorrective" class="px-4 py-1.5 rounded-lg text-sm font-medium transition-all duration-200 text-white hover:bg-white/10">
                        Corrective
                    </button>
                    <button onclick="setMaintenanceFilter('emergency')" id="mFilterEmergency" class="px-4 py-1.5 rounded-lg text-sm font-medium transition-all duration-200 text-white hover:bg-white/10">
                        Emergency
                    </button>
                    <button onclick="setMaintenanceFilter('complete')" id="mFilterComplete" class="px-4 py-1.5 rounded-lg text-sm font-medium transition-all duration-200 text-white hover:bg-white/10">
                        Complete
                    </button>
                </div>
            </div>
        </div>
        
        <div class="flex-1 overflow-hidden flex flex-col min-h-0">
            <!-- Summary Stats -->
            <div class="bg-gradient-to-r from-orange-50 to-amber-50 p-4 border-b border-orange-200 flex-shrink-0">
                <div class="grid grid-cols-2 lg:grid-cols-5 gap-3">
                    <!-- Total Maintenance -->
                    <div class="bg-white rounded-lg p-3 shadow-sm border border-orange-100 hover:shadow-md transition-shadow">
                        <div class="flex items-center gap-2">
                            <div class="p-1.5 bg-orange-100 rounded">
                                <i data-lucide="wrench" class="w-4 h-4 text-orange-600"></i>
                            </div>
                            <div>
                                <div class="text-lg font-bold text-orange-600" id="maintenanceUnitsCount">0</div>
                                <div class="text-[10px] text-gray-600 uppercase tracking-wide font-bold">Maintenance</div>
                            </div>
                        </div>
                    </div>
                    <!-- Preventive -->
                    <div class="bg-white rounded-lg p-3 shadow-sm border border-blue-100 hover:shadow-md transition-shadow">
                        <div class="flex items-center gap-2">
                            <div class="p-1.5 bg-blue-100 rounded">
                                <i data-lucide="shield-check" class="w-4 h-4 text-blue-600"></i>
                            </div>
                            <div>
                                <div class="text-lg font-bold text-blue-600" id="preventiveMaintenanceCount">0</div>
                                <div class="text-[10px] text-gray-600 uppercase tracking-wide font-bold">Preventive</div>
                            </div>
                        </div>
                    </div>
                    <!-- Corrective -->
                    <div class="bg-white rounded-lg p-3 shadow-sm border border-amber-100 hover:shadow-md transition-shadow">
                        <div class="flex items-center gap-2">
                            <div class="p-1.5 bg-amber-100 rounded">
                                <i data-lucide="wrench" class="w-4 h-4 text-amber-600"></i>
                            </div>
                            <div>
                                <div class="text-lg font-bold text-amber-600" id="correctiveMaintenanceCount">0</div>
                                <div class="text-[10px] text-gray-600 uppercase tracking-wide font-bold">Corrective</div>
                            </div>
                        </div>
                    </div>
                    <!-- Emergency -->
                    <div class="bg-white rounded-lg p-3 shadow-sm border border-red-100 hover:shadow-md transition-shadow">
                        <div class="flex items-center gap-2">
                            <div class="p-1.5 bg-red-100 rounded">
                                <i data-lucide="alert-triangle" class="w-4 h-4 text-red-600"></i>
                            </div>
                            <div>
                                <div class="text-lg font-bold text-red-600" id="emergencyMaintenanceCount">0</div>
                                <div class="text-[10px] text-gray-600 uppercase tracking-wide font-bold">Emergency</div>
                            </div>
                        </div>
                    </div>
                    <!-- Completed -->
                    <div class="bg-white rounded-lg p-3 shadow-sm border border-green-100 hover:shadow-md transition-shadow">
                        <div class="flex items-center gap-2">
                            <div class="p-1.5 bg-green-100 rounded">
                                <i data-lucide="check-circle" class="w-4 h-4 text-green-600"></i>
                            </div>
                            <div>
                                <div class="text-lg font-bold text-green-600" id="completedTotalCount">0</div>
                                <div class="text-[10px] text-gray-600 uppercase tracking-wide font-bold">Complete</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Maintenance Units Grid -->
            <div class="flex-1 overflow-y-auto p-4 bg-gray-50 min-h-0">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 pb-4" id="maintenanceGrid">
                    <!-- Loading State -->
                    <div class="col-span-full text-center py-16">
                        <div class="inline-flex flex-col items-center">
                            <div class="animate-spin rounded-full h-12 w-12 border-4 border-orange-600 border-t-transparent mb-4"></div>
                            <span class="text-lg text-gray-600 font-semibold mb-2">Loading maintenance data...</span>
                            <p class="text-sm text-gray-400">Please wait while we fetch maintenance details</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Maintenance Details Modal -->
<div id="maintenanceDetailsModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm hidden z-[60] flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl w-full max-w-4xl max-h-[90vh] flex flex-col shadow-2xl overflow-hidden">
        <!-- Header -->
        <div class="bg-gradient-to-r from-orange-600 to-amber-600 p-4 sm:p-5 flex justify-between items-center shrink-0">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-white/20 rounded-lg backdrop-blur-sm">
                    <i data-lucide="wrench" class="w-5 h-5 text-white"></i>
                </div>
                <div>
                    <h3 class="text-lg font-black text-white tracking-wide uppercase" id="mdm-plate">PLATE</h3>
                    <p class="text-[10px] text-orange-100 font-bold uppercase tracking-widest" id="mdm-type">Maintenance Details</p>
                </div>
            </div>
            <button onclick="hideMaintenanceDetailsModal()" class="text-white/80 hover:text-white hover:bg-white/20 p-2 rounded-lg transition-all duration-200">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        
        <!-- Content -->
        <div class="p-4 sm:p-6 overflow-y-auto flex-1 bg-gray-50">
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
                <div class="bg-white p-3 rounded-xl border border-gray-100 shadow-sm flex flex-col justify-center">
                    <p class="text-[9px] text-gray-400 font-black uppercase tracking-widest mb-1">Assigned Mechanic</p>
                    <p class="text-sm font-bold text-gray-800 flex items-center gap-2" id="mdm-mechanic">
                        <i data-lucide="user-cog" class="w-4 h-4 text-orange-500 flex-shrink-0"></i> <span>Name</span>
                    </p>
                </div>
                <div class="bg-white p-3 rounded-xl border border-gray-100 shadow-sm flex flex-col justify-center">
                    <p class="text-[9px] text-gray-400 font-black uppercase tracking-widest mb-1">Driver</p>
                    <p class="text-sm font-bold text-gray-800 flex items-center gap-2" id="mdm-driver">
                        <i data-lucide="user" class="w-4 h-4 text-blue-500 flex-shrink-0"></i> <span>Name</span>
                    </p>
                </div>
                <div class="bg-white p-3 rounded-xl border border-gray-100 shadow-sm flex flex-col justify-center">
                    <p class="text-[9px] text-gray-400 font-black uppercase tracking-widest mb-1">Date Started</p>
                    <p class="text-sm font-bold text-gray-800 flex items-center gap-2" id="mdm-start-date">
                        <i data-lucide="calendar" class="w-4 h-4 text-green-500 flex-shrink-0"></i> <span>Date</span>
                    </p>
                </div>
                <div class="bg-white p-3 rounded-xl border border-gray-100 shadow-sm flex flex-col justify-center">
                    <p class="text-[9px] text-gray-400 font-black uppercase tracking-widest mb-1">Target Completion</p>
                    <p class="text-sm font-bold text-gray-800 flex items-center gap-2" id="mdm-end-date">
                        <i data-lucide="clock" class="w-4 h-4 text-purple-500 flex-shrink-0"></i> <span>TBD</span>
                    </p>
                </div>
            </div>

            <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden mb-6">
                <div class="bg-gray-50 px-4 py-3 border-b border-gray-100 flex items-center gap-2">
                    <i data-lucide="list-checks" class="w-4 h-4 text-gray-500"></i>
                    <h4 class="text-[10px] font-black text-gray-700 uppercase tracking-widest">Parts & Services Breakdown</h4>
                </div>
                <div class="p-0 min-h-[150px]">
                    <div id="mdm-parts-loading" class="py-12 text-center text-gray-400">
                        <i data-lucide="loader-2" class="w-8 h-8 animate-spin mx-auto mb-3 text-orange-500"></i>
                        <p class="text-[10px] font-bold uppercase tracking-widest">Fetching data...</p>
                    </div>
                    <ul id="mdm-parts-list" class="divide-y divide-gray-50 hidden max-h-[320px] overflow-y-auto scrollbar-thin scrollbar-thumb-gray-200 scrollbar-track-transparent">
                        <!-- Populated by JS -->
                    </ul>
                </div>
            </div>

            <!-- Totals -->
            <div class="bg-gradient-to-br from-orange-50 to-amber-50 rounded-xl p-4 border border-orange-100 flex justify-between items-center">
                <div>
                    <p class="text-[10px] text-orange-600/70 font-black uppercase tracking-widest">Total Cost</p>
                    <p class="text-xs font-black uppercase tracking-widest mt-0.5" id="mdm-status-badge">Status</p>
                </div>
                <div class="text-right">
                    <p class="text-2xl font-black text-orange-600" id="mdm-total-cost">₱0.00</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Active Drivers Modal -->
<div id="activeDriversModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm hidden z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-6xl h-[95vh] flex flex-col overflow-hidden">
        <!-- Modal Header -->
        <div class="p-4 border-b bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600 flex-shrink-0">
            <div class="flex items-center justify-between mb-3">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-white/20 backdrop-blur-sm rounded-lg border border-white/30">
                        <i data-lucide="users" class="w-6 h-6 text-white"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-white">Active Drivers</h3>
                        <p class="text-blue-100 text-xs font-medium">Complete driver management details</p>
                    </div>
                </div>
                <button onclick="hideActiveDriversModal()" class="text-white/80 hover:text-white hover:bg-white/20 p-2 rounded-lg transition-all duration-200 backdrop-blur-sm">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
            
            <!-- Search and Date Filter -->
            <div class="flex items-center gap-3">
                <div class="relative flex-1">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i data-lucide="search" class="w-4 h-4 text-white/60"></i>
                    </div>
                    <input type="search" 
                        id="driversSearchInput"
                        placeholder="Search by name, license, or contact..."
                        class="w-full pl-10 pr-4 py-2 bg-white/20 backdrop-blur-sm border border-white/30 rounded-lg text-white placeholder-white/70 focus:outline-none focus:ring-2 focus:ring-white/50 focus:border-transparent transition-all duration-200 text-sm"
                        onkeyup="filterActiveDrivers()"
                     autocomplete="new-password" spellcheck="false" autocorrect="off" autocapitalize="off" readonly onfocus="this.removeAttribute('readonly');">
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                        <button onclick="clearDriversSearch()" class="text-white/60 hover:text-white transition-colors">
                            <i data-lucide="x-circle" class="w-4 h-4"></i>
                        </button>
                    </div>
                </div>
                
                <button 
                    onclick="toggleDriversSort()" 
                    id="driversSortBtn"
                    class="px-3 py-2 bg-white/20 backdrop-blur-sm border border-white/30 rounded-lg text-white hover:bg-white/30 transition-all duration-200 text-sm flex items-center gap-2 min-w-[90px] justify-center"
                >
                    <i data-lucide="sort-asc" id="driversSortIcon" class="w-4 h-4"></i>
                    <span id="driversSortText">A-Z</span>
                </button>

                <!-- Category Filter Buttons -->
                <div class="hidden md:flex items-center gap-1 bg-white/20 backdrop-blur-sm p-1 rounded-xl border border-white/30">
                    <button onclick="setDriversFilter('all')" id="dFilterAll" class="px-4 py-1.5 rounded-lg text-sm font-bold transition-all duration-200 bg-white text-blue-600 shadow-sm">
                        All
                    </button>
                    <button onclick="setDriversFilter('vacant')" id="dFilterVacant" class="px-4 py-1.5 rounded-lg text-sm font-medium transition-all duration-200 text-white hover:bg-white/10">
                        Vacant
                    </button>
                    <button onclick="setDriversFilter('active')" id="dFilterActive" class="px-4 py-1.5 rounded-lg text-sm font-medium transition-all duration-200 text-white hover:bg-white/10">
                        Active
                    </button>
                    <button onclick="setDriversFilter('top')" id="dFilterTop" class="px-4 py-1.5 rounded-lg text-sm font-medium transition-all duration-200 text-white hover:bg-white/10">
                        Top Performers
                    </button>
                </div>
            </div>
        </div>
        
        <div class="flex-1 overflow-hidden flex flex-col min-h-0">
            <!-- Summary Stats -->
            <div class="bg-gradient-to-r from-blue-50 to-indigo-50 p-4 border-b border-blue-200 flex-shrink-0">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="bg-white rounded-lg p-3 shadow-sm border border-blue-100 hover:shadow-md transition-shadow">
                        <div class="flex items-center gap-2">
                            <div class="p-1.5 bg-blue-100 rounded">
                                <i data-lucide="users" class="w-4 h-4 text-blue-600"></i>
                            </div>
                            <div>
                                <div class="text-lg font-bold text-blue-600" id="totalDriversCount">0</div>
                                <div class="text-xs text-gray-600 uppercase tracking-wide font-medium">Total Drivers</div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white rounded-lg p-3 shadow-sm border border-green-100 hover:shadow-md transition-shadow">
                        <div class="flex items-center gap-2">
                            <div class="p-1.5 bg-green-100 rounded">
                                <i data-lucide="user-minus" class="w-4 h-4 text-green-600"></i>
                            </div>
                            <div>
                                <div class="text-lg font-bold text-green-600" id="vacantDriversCount">0</div>
                                <div class="text-xs text-gray-600 uppercase tracking-wide font-medium">Total Vacant Drivers</div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white rounded-lg p-3 shadow-sm border border-orange-100 hover:shadow-md transition-shadow">
                        <div class="flex items-center gap-2">
                            <div class="p-1.5 bg-orange-100 rounded">
                                <i data-lucide="user-check" class="w-4 h-4 text-orange-600"></i>
                            </div>
                            <div>
                                <div class="text-lg font-bold text-orange-600" id="activeWithUnitsCount">0</div>
                                <div class="text-xs text-gray-600 uppercase tracking-wide font-medium">Total Active Drivers</div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white rounded-lg p-3 shadow-sm border border-purple-100 hover:shadow-md transition-shadow">
                        <div class="flex items-center gap-2">
                            <div class="p-1.5 bg-purple-100 rounded">
                                <i data-lucide="award" class="w-4 h-4 text-purple-600"></i>
                            </div>
                            <div>
                                <div class="text-lg font-bold text-purple-600" id="topPerformersCount">0</div>
                                <div class="text-xs text-gray-600 uppercase tracking-wide font-medium">Top Performers</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Active Drivers Grid -->
            <div class="flex-1 overflow-y-auto p-4 bg-gray-50 min-h-0">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 pb-4" id="activeDriversGrid">
                    <!-- Loading State -->
                    <div class="col-span-full text-center py-16">
                        <div class="inline-flex flex-col items-center">
                            <div class="animate-spin rounded-full h-12 w-12 border-4 border-blue-600 border-t-transparent mb-4"></div>
                            <span class="text-lg text-gray-600 font-semibold mb-2">Loading driver data...</span>
                            <p class="text-sm text-gray-400">Please wait while we fetch driver details</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Coding Units Modal -->
<div id="codingUnitsModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm hidden z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-6xl h-[95vh] flex flex-col overflow-hidden">
        <!-- Modal Header -->
        <div class="p-4 border-b bg-gradient-to-r from-purple-600 via-pink-600 to-rose-600 flex-shrink-0">
            <div class="flex items-center justify-between mb-3">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-white/20 backdrop-blur-sm rounded-lg border border-white/30">
                        <i data-lucide="code" class="w-6 h-6 text-white"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-white">Coding Units</h3>
                        <p class="text-purple-100 text-xs font-medium">Complete coding unit management details</p>
                    </div>
                </div>
                <button onclick="hideCodingUnitsModal()" class="text-white/80 hover:text-white hover:bg-white/20 p-2 rounded-lg transition-all duration-200 backdrop-blur-sm">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
            
            <!-- Search and Date Filter -->
            <div class="flex items-center gap-3">
                <div class="relative flex-1">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i data-lucide="search" class="w-4 h-4 text-white/60"></i>
                    </div>
                    <input type="search" 
                        id="codingSearchInput"
                        placeholder="Search by unit number, plate, or coding status..."
                        class="w-full pl-10 pr-4 py-2 bg-white/20 backdrop-blur-sm border border-white/30 rounded-lg text-white placeholder-white/70 focus:outline-none focus:ring-2 focus:ring-white/50 focus:border-transparent transition-all duration-200 text-sm"
                        oninput="filterCodingUnits()"
                     autocomplete="new-password" spellcheck="false" autocorrect="off" autocapitalize="off" readonly onfocus="this.removeAttribute('readonly');">
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                        <button onclick="clearCodingSearch()" class="text-white/60 hover:text-white transition-colors">
                            <i data-lucide="x-circle" class="w-4 h-4"></i>
                        </button>
                    </div>
                </div>

                <!-- Coding Period Filters -->
                <div class="flex bg-white/10 backdrop-blur-sm border border-white/30 rounded-lg p-1">
                    <button 
                        id="btn-today-coding" 
                        onclick="setCodingPeriod('today')"
                        class="px-3 py-1.5 text-xs font-medium rounded-md transition-all duration-200 bg-white text-purple-700"
                    >
                        Today
                    </button>
                    <button 
                        id="btn-tomorrow-coding" 
                        onclick="setCodingPeriod('tomorrow')"
                        class="px-3 py-1.5 text-xs font-medium rounded-md transition-all duration-200 text-white/70 hover:text-white hover:bg-white/10"
                    >
                        Tomorrow
                    </button>
                    <button 
                        id="btn-past-coding" 
                        onclick="setCodingPeriod('past')"
                        class="px-3 py-1.5 text-xs font-medium rounded-md transition-all duration-200 text-white/70 hover:text-white hover:bg-white/10"
                    >
                        Past
                    </button>
                </div>
            </div>
        </div>
        
        <div class="flex-1 overflow-hidden flex flex-col min-h-0">
            <!-- Summary Stats -->
            <div class="bg-gradient-to-r from-purple-50 to-pink-50 p-4 border-b border-purple-200 flex-shrink-0">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="bg-white rounded-lg p-3 shadow-sm border border-blue-100 hover:shadow-md transition-shadow">
                        <div class="flex items-center gap-2">
                            <div class="p-1.5 bg-blue-100 rounded">
                                <i data-lucide="calendar" class="w-4 h-4 text-blue-600"></i>
                            </div>
                            <div>
                                <div class="text-lg font-bold text-blue-600" id="todayCodingCount">0</div>
                                <div class="text-xs text-gray-600 uppercase tracking-wide font-medium">Today's Coding</div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white rounded-lg p-3 shadow-sm border border-green-100 hover:shadow-md transition-shadow">
                        <div class="flex items-center gap-2">
                            <div class="p-1.5 bg-green-100 rounded">
                                <i data-lucide="check-circle" class="w-4 h-4 text-green-600"></i>
                            </div>
                            <div>
                                <div class="text-lg font-bold text-green-600" id="tomorrowCodingCount">0</div>
                                <div class="text-xs text-gray-600 uppercase tracking-wide font-medium">Tomorrow's Coding</div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white rounded-lg p-3 shadow-sm border border-orange-100 hover:shadow-md transition-shadow">
                        <div class="flex items-center gap-2">
                            <div class="p-1.5 bg-orange-100 rounded">
                                <i data-lucide="alert-circle" class="w-4 h-4 text-orange-600"></i>
                            </div>
                            <div>
                                <div class="text-lg font-bold text-orange-600" id="pastCodingCount">0</div>
                                <div class="text-xs text-gray-600 uppercase tracking-wide font-medium">Past Coding</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Coding Units Grid -->
            <div class="flex-1 overflow-y-auto p-4 bg-gray-50 min-h-0">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 pb-4" id="codingGrid">
                    <!-- Loading State -->
                    <div class="col-span-full text-center py-16">
                        <div class="inline-flex flex-col items-center">
                            <div class="animate-spin rounded-full h-12 w-12 border-4 border-purple-600 border-t-transparent mb-4"></div>
                            <span class="text-lg text-gray-600 font-semibold mb-2">Loading coding data...</span>
                            <p class="text-sm text-gray-400">Please wait while we fetch coding details</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Net Income Modal -->
<div id="netIncomeModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm hidden z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-6xl h-[95vh] flex flex-col overflow-hidden">
        <!-- Modal Header -->
        <div class="p-4 border-b bg-gradient-to-r from-green-600 via-emerald-600 to-teal-600 flex-shrink-0">
            <div class="flex items-center justify-between mb-3">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-white/20 backdrop-blur-sm rounded-lg border border-white/30">
                        <i data-lucide="trending-up" class="w-6 h-6 text-white"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-white">Net Income Details</h3>
                        <p class="text-green-100 text-xs font-medium">Complete income and expense breakdown</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <button onclick="printReport()" class="bg-white text-green-700 hover:bg-green-50 px-4 py-2 rounded-lg transition-all duration-200 shadow-lg flex items-center gap-2 text-sm font-bold border-2 border-white animate-pulse hover:animate-none">
                        <i data-lucide="printer" class="w-4 h-4 text-green-700"></i>
                        PRINT REPORT
                    </button>
                    <button onclick="hideNetIncomeModal()" class="text-white/80 hover:text-white hover:bg-white/20 p-2 rounded-lg transition-all duration-200 backdrop-blur-sm">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>
            </div>
            
            <!-- Search and Date Filter -->
            <!-- Centered Period Filters (Net Income) -->
            <div class="mt-6 flex justify-center bg-black/10 rounded-xl p-1.5 backdrop-blur-sm border border-white/10">
                <div class="flex gap-1 p-0.5 bg-black/20 rounded-lg shadow-inner">
                    <button id="btn-today-income" onclick="setIncomePeriod('today')" class="px-3 py-1.5 text-xs font-black rounded-md transition-all duration-200">Today</button>
                    <button id="btn-week-income" onclick="setIncomePeriod('week')" class="px-3 py-1.5 text-xs font-black rounded-md transition-all duration-200">Weekly</button>
                    <button id="btn-month-income" onclick="setIncomePeriod('month')" class="px-3 py-1.5 text-xs font-black rounded-md transition-all duration-200">Monthly</button>
                    <button id="btn-year-income" onclick="setIncomePeriod('year')" class="px-3 py-1.5 text-xs font-black rounded-md transition-all duration-200">Yearly</button>
                </div>
            </div>
        </div>
        
        <div class="flex-1 overflow-hidden flex flex-col min-h-0">
            <!-- Detailed Report Document (Integrated) -->
            <div class="bg-gray-50 p-4 border-b border-gray-200 flex-shrink-0 print-section overflow-y-auto max-h-[85vh]">
                <div class="max-w-5xl mx-auto bg-white border border-gray-200 rounded-xl p-6 shadow-sm relative" id="incomeReport">
                    <!-- Report Header (Print Only) -->
                    <div class="text-center mb-10 print-only">
                        <div class="flex flex-col items-center mb-4">
                            <img src="{{ asset('image/logo.png') }}" alt="Euro Taxi Logo" class="h-16 w-auto mb-2">
                        </div>
                        <h4 class="text-4xl font-black uppercase tracking-[0.4em] text-gray-900 mb-2">Financial Report</h4>
                        <div class="text-base text-gray-600 uppercase font-black tracking-widest" id="reportPeriodLabelPrint">Period: TODAY</div>
                        <div class="text-[12px] text-gray-400 mt-3 font-bold tracking-[0.2em]">EURO TAXI MANAGEMENT SYSTEM • OFFICIAL RECORD</div>
                        <div class="border-t-2 border-gray-100 mt-8 pt-2 h-0 border-dashed"></div>
                        
                        <!-- Print-Only Summary Box -->
                        <div class="mt-6 border border-gray-200 p-4 text-left mx-auto max-w-sm" style="font-family: monospace;">
                            <div style="display: flex; justify-content: space-between; border-bottom: 1px solid #eee; padding-bottom: 4px; margin-bottom: 4px; font-weight: bold;">
                                <span style="text-transform: uppercase; font-size: 10px; letter-spacing: 0.1em; color: #666;">Total Revenue</span> 
                                <span id="reportTotalIncomePrint">₱0.00</span>
                            </div>
                            <div style="display: flex; justify-content: space-between; border-bottom: 1px solid #eee; padding-bottom: 4px; margin-bottom: 4px; font-weight: bold;">
                                <span style="text-transform: uppercase; font-size: 10px; letter-spacing: 0.1em; color: #666;">Total Expenses</span> 
                                <span id="reportTotalExpensesPrint" style="color: #dc2626;">₱0.00</span>
                            </div>
                            <div style="display: flex; justify-content: space-between; font-weight: 900; font-size: 12px; margin-top: 8px;">
                                <span style="text-transform: uppercase; letter-spacing: 0.1em;">Net Income</span> 
                                <span id="reportNetIncomePrint">₱0.00</span>
                            </div>
                        </div>
                    </div>

                    <!-- Net Income Summary Section (Screen Only) -->
                    <div class="mb-8 p-6 bg-gradient-to-br from-gray-900 to-gray-800 rounded-xl shadow-lg border border-gray-700 text-white relative overflow-hidden no-print">
                        <!-- Decorative element -->
                        <div class="absolute top-0 right-0 opacity-10 pointer-events-none">
                            <i data-lucide="calculator" class="w-48 h-48 -mt-8 -mr-8"></i>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 relative z-10">
                            <!-- Revenue -->
                            <div class="flex flex-col border-b md:border-b-0 md:border-r border-gray-700 pb-4 md:pb-0 md:pr-6">
                                <span class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">Total Revenue</span>
                                <span class="text-3xl font-black text-emerald-400" id="reportTotalIncome">₱0.00</span>
                            </div>
                            
                            <!-- Expenses -->
                            <div class="flex flex-col border-b md:border-b-0 md:border-r border-gray-700 pb-4 md:pb-0 md:pr-6 md:pl-2">
                                <span class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">Total Expenses</span>
                                <span class="text-3xl font-black text-red-400" id="reportTotalExpenses">₱0.00</span>
                            </div>
                            
                            <!-- Net Income -->
                            <div class="flex flex-col md:pl-2">
                                <span class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-1 flex items-center">
                                    Net Income 
                                    <span id="reportProfitMargin" class="ml-2 text-[9px] bg-white/10 px-2 py-0.5 rounded-full text-gray-300">0.0% Margin</span>
                                </span>
                                <span class="text-4xl font-black text-white" id="reportNetIncome">₱0.00</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Revenue Section -->
                    <div class="mb-6">
                        <div class="flex justify-between items-center bg-gray-100 text-gray-800 px-6 py-3 rounded-t-lg border-x border-t border-gray-200">
                            <span class="text-[11px] uppercase font-black tracking-[0.1em]">Revenue Breakdown</span>
                        </div>
                        <div class="border-x border-b border-gray-200 rounded-b-lg">
                            <div id="revenueDetailList" class="min-h-[100px] flex flex-col justify-center">
                                <!-- Dynamically populated -->
                            </div>
                        </div>
                    </div>
                    
                    <!-- Operating Expenses Section -->
                    <div class="mb-6">
                        <div class="flex justify-between items-center bg-red-50 text-red-900 px-6 py-3 rounded-t-lg border-x border-t border-red-100">
                            <span class="text-[11px] uppercase font-black tracking-[0.1em]">Operating Expenses Breakdown</span>
                        </div>
                        <div class="border-x border-b border-gray-200 rounded-b-lg p-0">
                            <!-- Maintenance Breakdown -->
                            <div class="border-b border-gray-200">
                                <div class="bg-gray-50 px-6 py-2 border-b border-gray-200 flex justify-between text-[9px] font-black text-gray-400 uppercase tracking-widest">
                                    <span>Maintenance & Repairs Itemized</span>
                                    <span id="reportMaintenanceTotal" class="text-orange-600 font-black">Total: ₱0.00</span>
                                </div>
                                <div id="maintenanceDetailList" class="bg-white min-h-[60px] flex flex-col justify-center">
                                    <!-- Dynamically populated -->
                                </div>
                            </div>

                            <!-- Office Breakdown -->
                            <div>
                                <div class="bg-gray-50 px-6 py-2 border-b border-gray-200 flex justify-between text-[9px] font-black text-gray-400 uppercase tracking-widest">
                                    <span>General Office Expenses Itemized</span>
                                    <span id="reportGeneralExpensesTotal" class="text-red-500 font-black">Total: ₱0.00</span>
                                </div>
                                <div id="officeExpensesDetailList" class="bg-white min-h-[60px] flex flex-col justify-center rounded-b-lg">
                                    <!-- Dynamically populated -->
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Report Footer (Print Only) -->
                    <div class="text-center mt-8 pt-6 border-t border-gray-100 print-only">
                        <p class="text-[10px] text-gray-400 font-black uppercase tracking-[0.2em] mb-1">Authenticated Financial Statement</p>
                        <p class="text-[9px] text-gray-300 font-medium tracking-widest">TIMESTAMP: <span id="reportTimestamp"></span></p>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- Total Expenses Details Modal (NEW) -->
<div id="expensesModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm hidden z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-6xl h-[95vh] flex flex-col overflow-hidden border border-white/20">
        <!-- Modal Header -->
        <div class="p-6 border-b bg-gradient-to-r from-red-600 to-rose-700 flex-shrink-0 text-white">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div class="p-2.5 bg-white/20 backdrop-blur-md rounded-xl border border-white/30 shadow-inner">
                        <i data-lucide="trending-down" class="w-7 h-7"></i>
                    </div>
                    <div>
                        <h3 class="text-2xl font-black tracking-tight leading-none mb-1">Total Expenses Today</h3>
                        <p class="text-red-100 text-[11px] font-bold uppercase tracking-widest opacity-80">Detailed expense records and computation</p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <button onclick="printExpensesNewTab()" class="flex items-center gap-2 px-4 py-2 bg-white/20 hover:bg-white/30 text-white rounded-lg font-black text-xs uppercase tracking-widest transition-all border border-white/20">
                        <i data-lucide="printer" class="w-4 h-4"></i>
                        Print Expenses
                    </button>
                    <button onclick="hideExpensesModal()" class="p-2 hover:bg-white/10 text-white rounded-full transition-colors">
                        <i data-lucide="x" class="w-6 h-6"></i>
                    </button>
                </div>
            </div>
            
            <!-- Period Filters (Expenses Only) -->
            <div class="mt-6 flex justify-center bg-black/10 rounded-xl p-1.5 backdrop-blur-sm border border-white/10">
                <div class="flex gap-1 p-0.5 bg-black/20 rounded-lg shadow-inner">
                    <button id="btn-today-expenses" onclick="setExpensesPeriod('today')" class="px-3 py-1.5 text-xs font-black rounded-md transition-all duration-200">Today</button>
                    <button id="btn-week-expenses" onclick="setExpensesPeriod('week')" class="px-3 py-1.5 text-xs font-black rounded-md transition-all duration-200">Weekly</button>
                    <button id="btn-month-expenses" onclick="setExpensesPeriod('month')" class="px-3 py-1.5 text-xs font-black rounded-md transition-all duration-200">Monthly</button>
                    <button id="btn-year-expenses" onclick="setExpensesPeriod('year')" class="px-3 py-1.5 text-xs font-black rounded-md transition-all duration-200">Yearly</button>
                </div>
            </div>
        </div>
        
        <div class="flex-1 overflow-hidden flex flex-col min-h-0">
            <!-- Detailed Report Document (Expenses Focused) -->
            <div class="bg-gray-50 p-4 border-b border-gray-200 flex-shrink-0 print-section overflow-y-auto max-h-[85vh]">
                <div class="max-w-5xl mx-auto bg-white border border-gray-200 rounded-xl p-6 shadow-sm relative" id="expensesReport">
                    <!-- Report Header (Print Only) -->
                    <div class="text-center mb-10 print-only">
                        <div class="flex flex-col items-center mb-4">
                            <img src="{{ asset('image/logo.png') }}" alt="Euro Taxi Logo" class="h-16 w-auto mb-2" style="height:64px; width:auto;">
                        </div>
                        <h4 class="text-4xl font-black uppercase tracking-[0.4em] text-gray-900 mb-2">Expense Statement</h4>
                        <div class="text-base text-gray-600 uppercase font-black tracking-widest" id="expensesPeriodLabelPrint">Period: TODAY</div>
                        <div class="text-[12px] text-gray-400 mt-3 font-bold tracking-[0.2em]">EURO TAXI MANAGEMENT SYSTEM • OFFICIAL EXPENSE RECORD</div>
                        <div class="border-t-2 border-gray-100 mt-8 pt-2 h-0 border-dashed"></div>
                        
                        <!-- Print-Only Summary Box -->
                        <div class="mt-6 border border-gray-200 p-4 text-left mx-auto max-w-sm" style="font-family: monospace;">
                            <div style="display: flex; justify-content: space-between; border-bottom: 1px solid #eee; padding-bottom: 4px; margin-bottom: 4px; font-weight: bold;">
                                <span style="text-transform: uppercase; font-size: 10px; letter-spacing: 0.1em; color: #666;">Maintenance Total</span> 
                                <span id="expensesMaintenanceTotalPrint">₱0.00</span>
                            </div>
                            <div style="display: flex; justify-content: space-between; border-bottom: 1px solid #eee; padding-bottom: 4px; margin-bottom: 4px; font-weight: bold;">
                                <span style="text-transform: uppercase; font-size: 10px; letter-spacing: 0.1em; color: #666;">Office Expenses Total</span> 
                                <span id="expensesOfficeTotalPrint" style="color: #dc2626;">₱0.00</span>
                            </div>
                            <div style="display: flex; justify-content: space-between; font-weight: 900; font-size: 12px; margin-top: 8px;">
                                <span style="text-transform: uppercase; letter-spacing: 0.1em;">Total Expenses</span> 
                                <span id="expensesTotalValuePrint">₱0.00</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Total Expenses Summary Section (Screen Only) -->
                    <div class="mb-8 p-6 bg-gradient-to-br from-red-900 to-red-800 rounded-xl shadow-lg border border-red-700 text-white relative overflow-hidden no-print">
                        <!-- Decorative element -->
                        <div class="absolute top-0 right-0 opacity-10 pointer-events-none">
                            <i data-lucide="receipt" class="w-48 h-48 -mt-8 -mr-8"></i>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 relative z-10">
                            <!-- Maintenance Expenses -->
                            <div class="flex flex-col border-b md:border-b-0 md:border-r border-red-700 pb-4 md:pb-0 md:pr-6">
                                <span class="text-xs font-bold text-red-200 uppercase tracking-widest mb-1">Maintenance Total</span>
                                <span class="text-2xl font-black text-white" id="expensesMaintenanceTotal">₱0.00</span>
                            </div>
                            
                            <!-- Office Expenses -->
                            <div class="flex flex-col md:pl-2">
                                <span class="text-xs font-bold text-red-200 uppercase tracking-widest mb-1 flex items-center">
                                    Office Expenses Total 
                                </span>
                                <span class="text-2xl font-black text-white" id="expensesOfficeTotal">₱0.00</span>
                            </div>
                        </div>

                        <!-- Grand Total -->
                        <div class="mt-6 pt-4 border-t border-red-700/50">
                            <span class="text-xs font-bold text-red-200 uppercase tracking-widest mb-1 block">Total Expenses</span>
                            <span class="text-5xl font-black text-white drop-shadow-md" id="expensesTotalValue">₱0.00</span>
                        </div>
                    </div>
                    
                    <!-- Operating Expenses Section -->
                    <div class="mb-6">
                        <div class="flex justify-between items-center bg-red-50 text-red-900 px-6 py-3 rounded-t-lg border-x border-t border-red-100">
                            <span class="text-[11px] uppercase font-black tracking-[0.1em]">Detailed Expenses Breakdown</span>
                        </div>
                        <div class="border-x border-b border-gray-200 rounded-b-lg p-0">
                            <!-- Maintenance Breakdown -->
                            <div class="border-b border-gray-200">
                                <div class="bg-gray-50 px-6 py-2 border-b border-gray-200 flex justify-between text-[9px] font-black text-gray-400 uppercase tracking-widest">
                                    <span>Maintenance & Repairs Itemized</span>
                                </div>
                                <div id="expensesMaintenanceList" class="bg-white min-h-[60px] flex flex-col justify-center"></div>
                            </div>

                            <!-- Office Breakdown -->
                            <div>
                                <div class="bg-gray-50 px-6 py-2 border-b border-gray-200 flex justify-between text-[9px] font-black text-gray-400 uppercase tracking-widest">
                                    <span>General Office Expenses Itemized</span>
                                </div>
                                <div id="expensesOfficeList" class="bg-white min-h-[60px] flex flex-col justify-center rounded-b-lg"></div>
                            </div>
                        </div>
                    </div>
                    
                    

                    <!-- Report Footer (Print Only) -->
                    <div class="text-center mt-8 pt-6 border-t border-gray-100 print-only">
                        <p class="text-[10px] text-gray-400 font-black uppercase tracking-[0.2em] mb-1">Authenticated Expense Summary</p>
                        <p class="text-[9px] text-gray-300 font-medium tracking-widest">TIMESTAMP: <span id="expensesTimestamp"></span></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Daily Boundary Collection Modal -->
<div id="dailyBoundaryModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm hidden z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-6xl h-[95vh] flex flex-col overflow-hidden">
        <!-- Modal Header -->
        <div class="p-4 border-b bg-gradient-to-r from-green-600 via-emerald-600 to-teal-600 flex-shrink-0">
            <div class="flex items-center justify-between mb-3">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-white/20 backdrop-blur-sm rounded-lg border border-white/30">
                        <i data-lucide="calendar" class="w-6 h-6 text-white"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-white">Daily Boundary Collections</h3>
                        <p class="text-green-100 text-xs font-medium">Complete boundary collection details</p>
                    </div>
                </div>
                <button onclick="hideDailyBoundaryModal()" class="text-white/80 hover:text-white hover:bg-white/20 p-2 rounded-lg transition-all duration-200 backdrop-blur-sm">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
            
            <!-- Search and Date Filter -->
            <div class="flex items-center gap-3">
                <div class="relative flex-1">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i data-lucide="search" class="w-4 h-4 text-white/60"></i>
                    </div>
                    <input type="search" 
                        id="boundarySearchInput"
                        placeholder="Search by unit number, driver, or amount..."
                        class="w-full pl-10 pr-4 py-2 bg-white/20 backdrop-blur-sm border border-white/30 rounded-lg text-white placeholder-white/70 focus:outline-none focus:ring-2 focus:ring-white/50 focus:border-transparent transition-all duration-200 text-sm"
                        onkeyup="filterBoundaryCollections()"
                     autocomplete="new-password" spellcheck="false" autocorrect="off" autocapitalize="off" readonly onfocus="this.removeAttribute('readonly');">
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                        <button onclick="clearBoundarySearch()" class="text-white/60 hover:text-white transition-colors">
                            <i data-lucide="x-circle" class="w-4 h-4"></i>
                        </button>
                    </div>
                </div>
                <input 
                    type="date" 
                    id="boundaryDateFilter"
                    class="px-3 py-2 bg-white/20 backdrop-blur-sm border border-white/30 rounded-lg text-white placeholder-white/70 focus:outline-none focus:ring-2 focus:ring-white/50 focus:border-transparent transition-all duration-200 text-sm"
                    onchange="filterBoundaryCollections()"
                >
            </div>
        </div>
        
        <div class="flex-1 overflow-hidden flex flex-col min-h-0">
            <!-- Summary Stats -->
            <div class="bg-gradient-to-r from-green-50 to-emerald-50 p-4 border-b border-green-200 flex-shrink-0">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="bg-white rounded-lg p-3 shadow-sm border border-green-100 hover:shadow-md transition-shadow">
                        <div class="flex items-center gap-2">
                            <div class="p-1.5 bg-green-100 rounded">
                                <i data-lucide="calendar" class="w-4 h-4 text-green-600"></i>
                            </div>
                            <div>
                                <div class="text-lg font-bold text-green-600" id="totalBoundaryCount">0</div>
                                <div class="text-xs text-gray-600 uppercase tracking-wide font-medium">Total Today</div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white rounded-lg p-3 shadow-sm border border-green-100 hover:shadow-md transition-shadow">
                        <div class="flex items-center gap-2">
                            <div class="p-1.5 bg-blue-100 rounded">
                                <i data-lucide="history" class="w-4 h-4 text-blue-600"></i>
                            </div>
                            <div>
                                <div class="text-lg font-bold text-blue-600" id="uniqueUnitsCount">₱0</div>
                                <div class="text-xs text-gray-600 uppercase tracking-wide font-medium">Yesterday Total</div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white rounded-lg p-3 shadow-sm border border-green-100 hover:shadow-md transition-shadow">
                        <div class="flex items-center gap-2">
                            <div class="p-1.5 bg-purple-100 rounded">
                                <i data-lucide="bar-chart-2" class="w-4 h-4 text-purple-600"></i>
                            </div>
                            <div>
                                <div class="text-lg font-bold text-purple-600" id="uniqueDriversCount">₱0</div>
                                <div class="text-xs text-gray-600 uppercase tracking-wide font-medium">Monthly Total</div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white rounded-lg p-3 shadow-sm border border-green-100 hover:shadow-md transition-shadow">
                        <div class="flex items-center gap-2">
                            <div class="p-1.5 bg-yellow-100 rounded">
                                <i data-lucide="trending-up" class="w-4 h-4 text-yellow-600"></i>
                            </div>
                            <div>
                                <div class="text-lg font-bold text-yellow-600" id="totalBoundaryAmount">₱0</div>
                                <div class="text-xs text-gray-600 uppercase tracking-wide font-medium">Yearly Total Amount</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Boundary Collections Grid -->
            <div class="flex-1 overflow-y-auto p-4 bg-gray-50 min-h-0">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 pb-4" id="boundaryGrid">
                    <!-- Loading State -->
                    <div class="col-span-full text-center py-16">
                        <div class="inline-flex flex-col items-center">
                            <div class="animate-spin rounded-full h-12 w-12 border-4 border-green-600 border-t-transparent mb-4"></div>
                            <span class="text-lg text-gray-600 font-semibold mb-2">Loading boundary collections...</span>
                            <p class="text-sm text-gray-400">Please wait while we fetch collection details</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Units Overview Modal -->
    <div id="unitsModal" class="hidden fixed inset-0 bg-black bg-opacity-60 z-50 flex items-center justify-center p-4 backdrop-blur-sm">
        <div class="bg-white rounded-2xl shadow-2xl max-w-7xl w-full mx-4 h-[95vh] flex flex-col border border-gray-100 overflow-hidden">
            <!-- Compact Header with Search -->
            <div class="p-4 border-b bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600 flex-shrink-0">
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-white/20 backdrop-blur-sm rounded-lg border border-white/30">
                            <i data-lucide="car" class="w-6 h-6 text-white"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-white leading-tight">Units Overview</h3>
                            <p class="text-blue-100 text-[10px] font-medium mt-0.5">Fleet Management Dashboard</p>
                        </div>
                    </div>
                    <button onclick="hideUnitsModal()" class="text-white/80 hover:text-white hover:bg-white/20 p-2 rounded-lg transition-all duration-200 backdrop-blur-sm">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>
                
                <!-- Search and Filter Row -->
                <div class="flex items-center gap-3 mb-3">
                    <!-- Compact Search Bar -->
                    <div class="relative flex-1">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i data-lucide="search" class="w-4 h-4 text-white/60"></i>
                        </div>
                        <input type="search" 
                            id="unitSearchInput"
                            placeholder="Search units by number, status, or performance..."
                            class="w-full pl-10 pr-4 py-2 bg-white/20 backdrop-blur-sm border border-white/30 rounded-lg text-white placeholder-white/70 focus:outline-none focus:ring-2 focus:ring-white/50 focus:border-transparent transition-all duration-200 text-sm"
                            onkeyup="filterUnits()"
                         autocomplete="new-password" spellcheck="false" autocorrect="off" autocapitalize="off" readonly onfocus="this.removeAttribute('readonly');">
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                            <button onclick="clearSearch()" class="text-white/60 hover:text-white transition-colors">
                                <i data-lucide="x-circle" class="w-4 h-4"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Status Filter Buttons -->
                    <div class="flex bg-white/10 backdrop-blur-sm border border-white/30 rounded-lg p-1">
                        <button 
                            id="btn-all-units" 
                            onclick="setUnitStatusFilter('all')"
                            class="px-3 py-1.5 text-xs font-medium rounded-md transition-all duration-200 bg-white text-blue-700"
                        >
                            All
                        </button>
                        <button 
                            id="btn-active-units" 
                            onclick="setUnitStatusFilter('active')"
                            class="px-3 py-1.5 text-xs font-medium rounded-md transition-all duration-200 text-white/70 hover:text-white hover:bg-white/10"
                        >
                            Active
                        </button>
                        <button 
                            id="btn-maintenance-units" 
                            onclick="setUnitStatusFilter('maintenance')"
                            class="px-3 py-1.5 text-xs font-medium rounded-md transition-all duration-200 text-white/70 hover:text-white hover:bg-white/10"
                        >
                            Maintenance
                        </button>
                        <button 
                            id="btn-coding-units" 
                            onclick="setUnitStatusFilter('coding')"
                            class="px-3 py-1.5 text-xs font-medium rounded-md transition-all duration-200 text-white/70 hover:text-white hover:bg-white/10"
                        >
                            Coding
                        </button>
                    </div>
                </div>
            </div>
            
            <div class="flex-1 overflow-hidden flex flex-col min-h-0">
                <!-- Compact Summary Stats -->
                <div class="bg-gradient-to-r from-gray-50 to-blue-50 p-3 border-b border-gray-200 flex-shrink-0">
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                        <div class="bg-white rounded-lg p-2 shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
                            <div class="flex items-center gap-2">
                                <div class="p-1 bg-blue-100 rounded">
                                    <i data-lucide="car" class="w-3.5 h-3.5 text-blue-600"></i>
                                </div>
                                <div class="flex-1">
                                    <div class="text-base font-bold text-blue-600 leading-tight" id="totalUnitsCount">0</div>
                                    <div class="text-[10px] text-gray-500 uppercase tracking-tight font-bold">Total</div>
                                </div>
                            </div>
                        </div>
                        <div class="bg-white rounded-lg p-2 shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
                            <div class="flex items-center gap-2">
                                <div class="p-1 bg-green-100 rounded">
                                    <i data-lucide="user-x" class="w-3.5 h-3.5 text-green-600"></i>
                                </div>
                                <div class="flex-1">
                                    <div class="text-base font-bold text-green-600 leading-tight" id="activeUnitsCount">0</div>
                                    <div class="text-[10px] text-gray-500 uppercase tracking-tight font-bold">Vacant (No Driver)</div>
                                </div>
                            </div>
                        </div>
                        <div class="bg-white rounded-lg p-2 shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
                            <div class="flex items-center gap-2">
                                <div class="p-1 bg-yellow-100 rounded">
                                    <i data-lucide="activity" class="w-3.5 h-3.5 text-yellow-600"></i>
                                </div>
                                <div class="flex-1">
                                    <div class="text-base font-bold text-yellow-600 leading-tight" id="roiUnitsCount">0</div>
                                    <div class="text-[10px] text-gray-500 uppercase tracking-tight font-bold">Active Units (With Driver)</div>
                                </div>
                            </div>
                        </div>
                        <div class="bg-white rounded-lg p-2 shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
                            <div class="flex items-center gap-2">
                                <div class="p-1 bg-purple-100 rounded">
                                    <i data-lucide="pie-chart" class="w-3.5 h-3.5 text-purple-600"></i>
                                </div>
                                <div class="flex-1">
                                    <div class="text-base font-bold text-purple-600 leading-tight" id="avgRoiCount">0%</div>
                                    <div class="text-[10px] text-gray-500 uppercase tracking-tight font-bold">Avg ROI</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Enhanced Units Grid with Maximum Space -->
                <div class="flex-1 overflow-y-auto p-4 bg-gray-50 min-h-0">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 xl:grid-cols-4 gap-4 pb-4" id="unitsGrid">
                        <!-- Enhanced Loading State -->
                        <div class="col-span-full text-center py-16">
                            <div class="inline-flex flex-col items-center">
                                <div class="animate-spin rounded-full h-12 w-12 border-4 border-blue-600 border-t-transparent mb-4"></div>
                                <span class="text-lg text-gray-600 font-semibold mb-2">Loading units data...</span>
                                <p class="text-sm text-gray-400">Please wait while we fetch your fleet information</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Flagged Units Modal -->
    <div id="flaggedUnitsModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black bg-opacity-60 backdrop-blur-sm">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl overflow-hidden border border-red-100">
            <div class="p-4 bg-gradient-to-r from-red-600 to-rose-700 flex justify-between items-center">
                <div class="flex items-center gap-2">
                    <div class="p-1.5 bg-white/20 rounded-lg backdrop-blur-sm">
                        <i data-lucide="shield-alert" class="w-5 h-5 text-white"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-black text-white uppercase tracking-tight">Security Flags / Missing Units</h3>
                        <p class="text-red-100 text-[10px] font-bold uppercase tracking-widest">Fleet Lockdown Management</p>
                    </div>
                </div>
                <button onclick="document.getElementById('flaggedUnitsModal').classList.add('hidden')" class="text-white/80 hover:text-white hover:bg-white/20 p-2 rounded-lg transition-all duration-200 backdrop-blur-sm">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
            <div id="flaggedUnitsList" class="max-h-[70vh] overflow-y-auto p-4 space-y-4 bg-gray-50/50">
                <div class="text-center py-12">
                    <div class="animate-spin rounded-full h-10 w-10 border-4 border-red-600 border-t-transparent mx-auto mb-3"></div>
                    <p class="text-gray-500 font-bold uppercase tracking-widest text-[10px]">Loading security status...</p>
                </div>
            </div>
        </div>
    </div>

    @include('driver-management.partials._driver_details_modal')
@endsection
@push('scripts')
    <script src="{{ asset('js/realtime-dashboard.js') }}"></script>
    <script>
        // Register Chart.js datalabels plugin
        Chart.register(ChartDataLabels);
        
        
        // Weekly Financial Chart
        try {
            const weeklyCtx = document.getElementById('weeklyChart').getContext('2d');
            const weeklyData = @json($weekly_data);
            const wGrad1 = weeklyCtx.createLinearGradient(0, 0, 0, 300);
            wGrad1.addColorStop(0, 'rgba(234,179,8,0.25)'); wGrad1.addColorStop(1, 'rgba(234,179,8,0.01)');
            const wGrad2 = weeklyCtx.createLinearGradient(0, 0, 0, 300);
            wGrad2.addColorStop(0, 'rgba(239,68,68,0.2)'); wGrad2.addColorStop(1, 'rgba(239,68,68,0.01)');
            const wGrad3 = weeklyCtx.createLinearGradient(0, 0, 0, 300);
            wGrad3.addColorStop(0, 'rgba(34,197,94,0.25)'); wGrad3.addColorStop(1, 'rgba(34,197,94,0.01)');
            let isWeeklyChartInitialized = false;
            function getWeeklyChartConfig() {
                return {
                    type: 'line',
                    data: {
                        labels: weeklyData.map(d => d.day),
                        datasets: [
                            { label: 'Boundary', data: weeklyData.map(d => 0), borderColor: '#eab308', backgroundColor: wGrad1, borderWidth: 2.5, tension: 0.45, fill: true, pointBackgroundColor: '#eab308', pointRadius: 4, pointHoverRadius: 7 },
                            { label: 'Expenses', data: weeklyData.map(d => 0), borderColor: '#ef4444', backgroundColor: wGrad2, borderWidth: 2.5, tension: 0.45, fill: true, pointBackgroundColor: '#ef4444', pointRadius: 4, pointHoverRadius: 7 },
                            { label: 'Net Income', data: weeklyData.map(d => 0), borderColor: '#22c55e', backgroundColor: wGrad3, borderWidth: 2.5, tension: 0.45, fill: true, pointBackgroundColor: '#22c55e', pointRadius: 4, pointHoverRadius: 7 }
                        ]
                    },
                    options: {
                        responsive: true, maintainAspectRatio: false,
                        animation: { duration: 1500, easing: 'easeOutQuart' },
                        interaction: { mode: 'index', intersect: false },
                        plugins: {
                            legend: { position: 'top', labels: { usePointStyle: true, pointStyleWidth: 10, font: { size: 12, weight: '600' }, padding: 18 } },
                            tooltip: { backgroundColor: 'rgba(15,23,42,0.95)', padding: 14, cornerRadius: 12, callbacks: { label: ctx => ` ${ctx.dataset.label}: ₱${ctx.parsed.y.toLocaleString()}` } }
                        },
                        scales: {
                            x: { grid: { color: 'rgba(0,0,0,0.04)' }, ticks: { font: { size: 11, weight: '600' }, color: '#64748b' } },
                            y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.05)' }, ticks: { font: { size: 11 }, color: '#64748b', callback: v => '₱' + v.toLocaleString() } }
                        }
                    }
                };
            }

            const weeklyObserver = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        if (!isWeeklyChartInitialized) {
                            window.weeklyChart = new Chart(weeklyCtx, getWeeklyChartConfig());
                            isWeeklyChartInitialized = true;
                        }
                        window.weeklyChart.data.datasets[0].data = weeklyData.map(d => d.boundary);
                        window.weeklyChart.data.datasets[1].data = weeklyData.map(d => d.expenses);
                        window.weeklyChart.data.datasets[2].data = weeklyData.map(d => d.net);
                        window.weeklyChart.update();
                    } else {
                        if (isWeeklyChartInitialized && window.weeklyChart) {
                            window.weeklyChart.data.datasets[0].data = weeklyData.map(d => 0);
                            window.weeklyChart.data.datasets[1].data = weeklyData.map(d => 0);
                            window.weeklyChart.data.datasets[2].data = weeklyData.map(d => 0);
                            window.weeklyChart.update('none');
                        }
                    }
                });
            }, { threshold: 0.3 });
            weeklyObserver.observe(document.getElementById('weeklyChart'));
        } catch (error) { console.error('Weekly Chart Error:', error); }

        // Unit Status Chart - (Handled by the premium donut chart below)


        // Revenue Trend Chart - Premium Line
        try {
            const revenueTrendCtx = document.getElementById('revenueTrendChart').getContext('2d');
            const revenueTrendData = @json($revenue_trend);
            
            window.currentRevenueTrendValues = revenueTrendData.map(d => d.revenue);
            window.isRevenueTrendIntersecting = false;
            let isRevenueTrendChartInitialized = false;

            const rGrad = revenueTrendCtx.createLinearGradient(0, 0, 0, 320);
            rGrad.addColorStop(0, 'rgba(37,99,235,0.3)'); rGrad.addColorStop(0.6, 'rgba(37,99,235,0.08)'); rGrad.addColorStop(1, 'rgba(37,99,235,0)');
            
            function getRevenueTrendChartConfig() {
                return {
                    type: 'line',
                    data: {
                        labels: revenueTrendData.map(d => d.date),
                        datasets: [{
                            label: 'Revenue', data: window.currentRevenueTrendValues.map(d => 0),
                            borderColor: '#2563eb', backgroundColor: rGrad,
                            borderWidth: 3, tension: 0.45, fill: true,
                            pointBackgroundColor: '#fff', pointBorderColor: '#2563eb', pointBorderWidth: 2.5,
                            pointRadius: 5, pointHoverRadius: 8, pointHoverBackgroundColor: '#2563eb'
                        }]
                    },
                    options: {
                        responsive: true, maintainAspectRatio: false,
                        animation: { duration: 1500, easing: 'easeOutQuart' },
                        interaction: { mode: 'index', intersect: false },
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                backgroundColor: 'rgba(15,23,42,0.95)', padding: 14, cornerRadius: 12,
                                callbacks: { label: ctx => ` Revenue: ₱${ctx.parsed.y.toLocaleString()}` }
                            }
                        },
                        scales: {
                            x: { grid: { display: false }, ticks: { font: { size: 11, weight: '600' }, color: '#94a3b8', maxRotation: 45 } },
                            y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.05)', drawBorder: false },
                                 ticks: { font: { size: 11 }, color: '#94a3b8', callback: v => '₱' + v.toLocaleString() } }
                        }
                    }
                };
            }

            const revenueTrendObserver = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    window.isRevenueTrendIntersecting = entry.isIntersecting;
                    if (entry.isIntersecting) {
                        if (!isRevenueTrendChartInitialized) {
                            window.revenueTrendChart = new Chart(revenueTrendCtx, getRevenueTrendChartConfig());
                            isRevenueTrendChartInitialized = true;
                        }
                        window.revenueTrendChart.data.datasets[0].data = window.currentRevenueTrendValues;
                        window.revenueTrendChart.update();
                    } else {
                        if (isRevenueTrendChartInitialized && window.revenueTrendChart) {
                            window.revenueTrendChart.data.datasets[0].data = window.currentRevenueTrendValues.map(d => 0);
                            window.revenueTrendChart.update('none');
                        }
                    }
                });
            }, { threshold: 0.3 });
            revenueTrendObserver.observe(document.getElementById('revenueTrendChart'));
        } catch (error) { console.error('Revenue Trend Chart Error:', error); }

        // Unit Performance Chart - Modernized Horizontal Enterprise View
        try {
            const unitPerformanceCtx = document.getElementById('unitPerformanceChart').getContext('2d');
            const unitPerformanceData = @json($unit_performance);
            
            // Create sleek gradients for a premium feel
            const actualGradient = unitPerformanceCtx.createLinearGradient(0, 0, 400, 0);
            actualGradient.addColorStop(0, '#3b82f6'); // Blue 500
            actualGradient.addColorStop(1, '#60a5fa'); // Blue 400
            
            const targetGradient = unitPerformanceCtx.createLinearGradient(0, 0, 400, 0);
            targetGradient.addColorStop(0, '#f59e0b'); // Amber 500
            targetGradient.addColorStop(1, '#fbbf24'); // Amber 400

            // Initialize chart via IntersectionObserver so animation runs when scrolled into view
            let isUnitChartInitialized = false;
            
            function getChartConfig() {
                return {
                    type: 'bar',
                    data: {
                        labels: unitPerformanceData.map(d => d.unit),
                        datasets: [
                            {
                                label: 'Actual Collected',
                                // Start at 0 for initial animation state
                                data: unitPerformanceData.map(d => 0),
                                backgroundColor: actualGradient,
                                borderColor: '#2563eb',
                                borderWidth: 0,
                                borderRadius: 6,
                                barThickness: 12,
                            },
                            {
                                label: 'Monthly Target (30 Days)',
                                // Start at 0 for initial animation state
                                data: unitPerformanceData.map(d => 0),
                                backgroundColor: 'rgba(245, 158, 11, 0.15)', // Subtle target indicator
                                borderColor: '#f59e0b',
                                borderWidth: 1,
                                borderRadius: 6,
                                barThickness: 12,
                                borderDash: [5, 5] // Dashed look for target
                            }
                        ]
                    },
                    options: {
                        indexAxis: 'y', // Switch to horizontal for better Plate Number readability
                        responsive: true,
                        maintainAspectRatio: false,
                        animation: {
                            duration: 2000,
                            easing: 'easeOutQuart'
                        },
                        plugins: {
                            legend: {
                                display: false // Using custom legend in sidebar instead
                            },
                            datalabels: {
                                color: function(context) {
                                    return context.datasetIndex === 0 ? '#ffffff' : '#64748b'; // White for actual, Slate for target
                                },
                                anchor: 'end',
                                align: 'start',
                                offset: 4,
                                font: { weight: 'bold', size: 10 },
                                formatter: function(value) {
                                    return value > 0 ? value.toLocaleString() : '';
                                }
                            },
                            tooltip: {
                                backgroundColor: 'rgba(15, 23, 42, 0.95)',
                                padding: 12,
                                cornerRadius: 10,
                                titleFont: { size: 14, weight: 'bold' },
                                callbacks: {
                                    label: function(context) {
                                        const val = context.parsed.x || 0;
                                        return ` ₱${val.toLocaleString()}`;
                                    },
                                    footer: (items) => {
                                        const index = items[0].dataIndex;
                                        const data = unitPerformanceData[index];
                                        const diff = data.performance - data.target;
                                        const pct = ((data.performance / data.target) * 100).toFixed(1);
                                        return ` Achievement: ${pct}% of target\n Variance: ₱${diff.toLocaleString()}`;
                                    }
                                }
                            }
                        },
                        scales: {
                            x: {
                                beginAtZero: true,
                                grid: { color: 'rgba(0,0,0,0.03)', drawBorder: false },
                                ticks: { 
                                    callback: function (value) { return '₱' + value.toLocaleString(); },
                                    font: { size: 10 }
                                }
                            },
                            y: {
                                grid: { display: false, drawBorder: false },
                                ticks: { 
                                    font: { size: 11, weight: '700' },
                                    color: '#334155'
                                }
                            }
                        }
                    }
                };
            }

            const chartObserver = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        if (!isUnitChartInitialized) {
                            window.unitPerformanceChart = new Chart(unitPerformanceCtx, getChartConfig());
                            isUnitChartInitialized = true;
                        }
                        
                        // Animate bars growing to actual values
                        window.unitPerformanceChart.data.datasets[0].data = unitPerformanceData.map(d => d.performance);
                        window.unitPerformanceChart.data.datasets[1].data = unitPerformanceData.map(d => d.target);
                        window.unitPerformanceChart.update();
                        
                    } else {
                        // Reset bars to zero instantly when out of view
                        if (isUnitChartInitialized && window.unitPerformanceChart) {
                            window.unitPerformanceChart.data.datasets[0].data = unitPerformanceData.map(d => 0);
                            window.unitPerformanceChart.data.datasets[1].data = unitPerformanceData.map(d => 0);
                            window.unitPerformanceChart.update('none'); // Update instantly without animation
                        }
                    }
                });
            }, { threshold: 0.3 }); // Use 0.3 so it safely triggers on small screens too
            
            chartObserver.observe(document.getElementById('unitPerformanceChart'));

            // Update Executive Insight: Top Performer
            if (unitPerformanceData && unitPerformanceData.length > 0) {
                const topUnit = unitPerformanceData[0]; // Data is sorted by performance descending
                document.getElementById('insightTopPlate').textContent = topUnit.unit;
            }
        } catch (error) {
            console.error('Unit Performance Chart Error:', error);
        }

        // Expense Breakdown Chart - Premium Pie
        try {
            const expenseBreakdownCtx = document.getElementById('expenseBreakdownChart').getContext('2d');
            let expenseBreakdownData = @json($expense_breakdown);
            let isPlaceholder = false;
            if (!expenseBreakdownData || expenseBreakdownData.length === 0 ||
                (Array.isArray(expenseBreakdownData) && expenseBreakdownData.every(d => d.amount === 0))) {
                isPlaceholder = true;
                expenseBreakdownData = [
                    { category: 'Maintenance', amount: 4500 },
                    { category: 'Fuel & Oil', amount: 3200 },
                    { category: 'Salaries', amount: 8000 },
                    { category: 'Parts', amount: 2100 },
                    { category: 'Others', amount: 1200 }
                ];
            }
            const pieColors = ['#ef4444','#f59e0b','#10b981','#3b82f6','#8b5cf6','#ec4899','#06b6d4'];
            const pieHover = ['#dc2626','#d97706','#059669','#2563eb','#7c3aed','#db2777','#0891b2'];
            let isExpenseChartInitialized = false;
            function getExpenseChartConfig() {
                return {
                    type: 'pie',
                    data: {
                        labels: expenseBreakdownData.map(d => d.category),
                        datasets: [{ data: expenseBreakdownData.map(d => 0), backgroundColor: pieColors, hoverBackgroundColor: pieHover, borderWidth: 3, borderColor: '#fff', hoverOffset: 12 }]
                    },
                    options: {
                        responsive: true, maintainAspectRatio: false,
                        plugins: {
                            legend: { position: 'right', labels: { usePointStyle: true, pointStyleWidth: 12, font: { size: 12, weight: '600' }, padding: 16, color: '#374151' } },
                            tooltip: {
                                backgroundColor: 'rgba(15,23,42,0.95)', padding: 14, cornerRadius: 12,
                                callbacks: {
                                    label: function(ctx) {
                                        const total = ctx.dataset.data.reduce((a,b) => a+b, 0);
                                        const pct = total > 0 ? ((ctx.parsed / total) * 100).toFixed(1) : 0;
                                        return ` ${ctx.label}: ₱${ctx.parsed.toLocaleString()} (${pct}%)`;
                                    }
                                }
                            },
                            datalabels: { color: '#fff', font: { weight: 'bold', size: 12 }, formatter: (val, ctx) => { const total = ctx.dataset.data.reduce((a,b)=>a+b,0); const pct = total > 0 ? ((val/total)*100).toFixed(0) : 0; return pct > 5 ? pct+'%' : ''; } }
                        },
                        animation: { animateRotate: true, animateScale: true, duration: 1500, easing: 'easeOutQuart' }
                    }
                };
            }

            const expenseObserver = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        if (!isExpenseChartInitialized) {
                            window.expenseBreakdownChart = new Chart(expenseBreakdownCtx, getExpenseChartConfig());
                            isExpenseChartInitialized = true;
                        }
                        window.expenseBreakdownChart.data.datasets[0].data = expenseBreakdownData.map(d => d.amount);
                        window.expenseBreakdownChart.update();
                    } else {
                        if (isExpenseChartInitialized && window.expenseBreakdownChart) {
                            window.expenseBreakdownChart.data.datasets[0].data = expenseBreakdownData.map(d => 0);
                            window.expenseBreakdownChart.update('none');
                        }
                    }
                });
            }, { threshold: 0.3 });
            expenseObserver.observe(document.getElementById('expenseBreakdownChart'));
        } catch (error) { console.error('Expense Chart Error:', error); }




        // Top Drivers Chart - Premium Horizontal Bar
        try {
            const topDriversCtx = document.getElementById('topDriversChart').getContext('2d');
            let topDriversData = @json($top_drivers);
            let isPlaceholder = false;
            if (!topDriversData || topDriversData.length === 0 ||
                (Array.isArray(topDriversData) && topDriversData.every(d => d.score === 0))) {
                isPlaceholder = true;
                topDriversData = [
                    { name: 'Bernardo Silva', score: 28, total: 42000 },
                    { name: 'Kevin De Bruyne', score: 26, total: 39000 },
                    { name: 'Erling Haaland', score: 25, total: 37500 },
                    { name: 'Phil Foden', score: 22, total: 33000 },
                    { name: 'Rodri Hernandez', score: 20, total: 30000 }
                ];
            }
            const barColors = topDriversData.map((_, i) => i===0?'#2563eb':i===1?'#7c3aed':i===2?'#0891b2':'#64748b');
            let isTopDriversChartInitialized = false;
            function getTopDriversChartConfig() {
                return {
                    type: 'bar',
                    data: {
                        labels: topDriversData.map((d,i) => { const medals=['🥇','🥈','🥉']; return `${medals[i]||'  '} ${d.name}`; }),
                        datasets: [{ label: 'Reliability Score', data: topDriversData.map(d => 0),
                            backgroundColor: barColors, borderColor: barColors, borderWidth: 0,
                            borderRadius: 10, borderSkipped: false, barThickness: 28 }]
                    },
                    options: {
                        indexAxis: 'y', responsive: true, maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: { backgroundColor: 'rgba(15,23,42,0.95)', padding: 14, cornerRadius: 12, displayColors: false,
                                callbacks: {
                                    label: ctx => ` ⭐ Reliability: ${ctx.parsed.x} clean service days`,
                                    footer: items => { const amt = topDriversData[items[0].dataIndex].total; return ` ₱ Total Revenue: ₱${amt.toLocaleString()}`; }
                                }
                            },
                            datalabels: { color: '#fff', font: { weight: 'bold', size: 12 }, anchor: 'end', align: 'start', offset: 8, formatter: v => v>0?v:'' }
                        },
                        scales: {
                            x: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.04)', drawBorder: false }, ticks: { font: { size: 11, weight: '500' }, color: '#94a3b8' } },
                            y: { grid: { display: false, drawBorder: false }, ticks: { font: { size: 13, weight: '600' }, color: '#1e293b' } }
                        },
                        animation: { duration: 1500, easing: 'easeOutQuart' }
                    }
                };
            }

            const topDriversObserver = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        if (!isTopDriversChartInitialized) {
                            window.topDriversChart = new Chart(topDriversCtx, getTopDriversChartConfig());
                            isTopDriversChartInitialized = true;
                        }
                        window.topDriversChart.data.datasets[0].data = topDriversData.map(d => d.score);
                        window.topDriversChart.update();
                    } else {
                        if (isTopDriversChartInitialized && window.topDriversChart) {
                            window.topDriversChart.data.datasets[0].data = topDriversData.map(d => 0);
                            window.topDriversChart.update('none');
                        }
                    }
                });
            }, { threshold: 0.3 });
            topDriversObserver.observe(document.getElementById('topDriversChart'));
        } catch (error) { console.error('Top Drivers Chart Error:', error); }




        // Unit Status Distribution Chart - Premium Donut
        try {
            const unitStatusDistCtx = document.getElementById('unitStatusChart').getContext('2d');
            const unitStatusDistData = @json($unit_status_distribution_data);
            const donutColors = ['#10b981','#3b82f6','#f59e0b','#ef4444'];
            const donutHover = ['#059669','#2563eb','#d97706','#dc2626'];
            let distLabels, distValues, distIsPlaceholder = false;
            if (!unitStatusDistData || unitStatusDistData.length === 0 || unitStatusDistData.every(d => d.count === 0)) {
                distIsPlaceholder = true;
                distLabels = ['Active','Maintenance','Coding','Retired'];
                distValues = [5,2,1,0];
            } else {
                distLabels = unitStatusDistData.map(d => d.status);
                distValues = unitStatusDistData.map(d => d.count);
            }
            const totalUnits = distValues.reduce((a,b) => a+b, 0);
            let isUnitStatusChartInitialized = false;
            function getUnitStatusChartConfig() {
                return {
                    type: 'doughnut',
                    data: { labels: distLabels, datasets: [{ data: distValues.map(d => 0), backgroundColor: donutColors, hoverBackgroundColor: donutHover, borderWidth: 4, borderColor: '#fff', hoverOffset: 16 }] },
                    options: {
                        responsive: true, maintainAspectRatio: false, cutout: '72%',
                        plugins: {
                            legend: { position: 'right', labels: { usePointStyle: true, pointStyleWidth: 12, font: { size: 12, weight: '600' }, padding: 18, color: '#374151',
                                generateLabels: (chart) => chart.data.labels.map((label, i) => ({ text: `${label}: ${chart.data.datasets[0].data[i]}`, fillStyle: donutColors[i], strokeStyle: '#fff', lineWidth: 2, index: i })) } },
                            tooltip: { backgroundColor: 'rgba(15,23,42,0.95)', padding: 14, cornerRadius: 12,
                                callbacks: { label: ctx => { const total = ctx.dataset.data.reduce((a,b)=>a+b,0); const pct = total>0?((ctx.parsed/total)*100).toFixed(1):0; return ` ${ctx.label}: ${ctx.parsed} units (${pct}%)`; } } },
                            datalabels: { color: '#fff', font: { weight: 'bold', size: 13 }, formatter: (val, ctx) => { const sum = ctx.dataset.data.reduce((a,b)=>a+b,0); const pct = sum>0?((val/sum)*100).toFixed(0):0; return pct>5?pct+'%':''; } }
                        },
                        animation: { animateRotate: true, animateScale: true, duration: 1500, easing: 'easeOutQuart' }
                    },
                    plugins: [{ id: 'donutCenter', afterDraw(chart) {
                        const { ctx, chartArea: { left, top, right, bottom } } = chart;
                        const cx = (left+right)/2, cy = (top+bottom)/2;
                        const currentTotal = chart.data.datasets[0].data.reduce((a, b) => a + b, 0);
                        ctx.save();
                        ctx.font = 'bold 28px Inter, sans-serif'; ctx.fillStyle = '#0f172a'; ctx.textAlign = 'center'; ctx.textBaseline = 'middle';
                        ctx.fillText(currentTotal, cx, cy-10);
                        ctx.font = '600 11px Inter, sans-serif'; ctx.fillStyle = '#94a3b8';
                        ctx.fillText('TOTAL UNITS', cx, cy+14);
                        ctx.restore();
                    }}]
                };
            }

            const unitStatusObserver = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        if (!isUnitStatusChartInitialized) {
                            window.unitStatusChart = new Chart(unitStatusDistCtx, getUnitStatusChartConfig());
                            isUnitStatusChartInitialized = true;
                        }
                        window.unitStatusChart.data.datasets[0].data = distValues;
                        window.unitStatusChart.update();
                    } else {
                        if (isUnitStatusChartInitialized && window.unitStatusChart) {
                            window.unitStatusChart.data.datasets[0].data = distValues.map(d => 0);
                            window.unitStatusChart.update('none');
                        }
                    }
                });
            }, { threshold: 0.3 });
            unitStatusObserver.observe(document.getElementById('unitStatusChart'));
        } catch (error) { console.error('Unit Status Distribution Chart Error:', error); }

        // Revenue Trend Period Selection
        function updateRevenueTrend(period) {
            // Update button styles
            document.querySelectorAll('[id^="btn-"]').forEach(btn => {
                btn.classList.remove('bg-blue-600', 'text-white', 'hover:bg-blue-700');
                btn.classList.add('bg-gray-200', 'text-gray-700', 'hover:bg-gray-300');
            });
            
            // Highlight active button
            const activeBtn = document.getElementById('btn-' + period + 'days');
            if (activeBtn) {
                activeBtn.classList.remove('bg-gray-200', 'text-gray-700', 'hover:bg-gray-300');
                activeBtn.classList.add('bg-blue-600', 'text-white', 'hover:bg-blue-700');
            }
            
            // Fetch new data
            fetch(`/api/revenue-trend?period=${period}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success && window.revenueTrendChart) {
                        window.revenueTrendChart.data.labels = data.data.map(d => d.date);
                        window.currentRevenueTrendValues = data.data.map(d => d.revenue);
                        
                        if (window.isRevenueTrendIntersecting) {
                            window.revenueTrendChart.data.datasets[0].data = window.currentRevenueTrendValues;
                            window.revenueTrendChart.update();
                        } else {
                            window.revenueTrendChart.data.datasets[0].data = window.currentRevenueTrendValues.map(d => 0);
                            window.revenueTrendChart.update('none');
                        }
                    }
                })
                .catch(error => console.error('Error updating revenue trend:', error));
        }

        // Maintenance Units Modal Functions
        function showMaintenanceUnitsModal() {
            document.getElementById('maintenanceUnitsModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
            
            // Set default filter to all
            window.currentMaintenanceFilter = 'all';
            updateMaintenanceFilterUI('all');
            
            loadMaintenanceUnitsData();
        }
        
        function hideMaintenanceUnitsModal() {
            document.getElementById('maintenanceUnitsModal').classList.add('hidden');
            document.body.style.overflow = 'auto';
        }
        
        function showMaintenanceDetailsModal(maintenanceId) {
            if (!maintenanceId) return;
            const unit = window.originalMaintenanceData.find(u => u.maintenance_id === maintenanceId);
            if (!unit) return;

            document.getElementById('mdm-plate').textContent = unit.plate_number;
            document.getElementById('mdm-type').textContent = (unit.maintenance_type || 'Maintenance').toUpperCase();
            
            document.getElementById('mdm-start-date').querySelector('span').textContent = unit.start_date || 'N/A';
            document.getElementById('mdm-end-date').querySelector('span').textContent = unit.estimated_completion || 'TBD';

            document.getElementById('mdm-mechanic').querySelector('span').textContent = unit.mechanic_name || 'Not specified';
            document.getElementById('mdm-driver').querySelector('span').textContent = unit.driver_name || 'No driver assigned';
            
            document.getElementById('mdm-total-cost').textContent = '₱' + (parseFloat(unit.maintenance_cost) || 0).toLocaleString('en-PH', {minimumFractionDigits: 2});
            
            const statusBadge = document.getElementById('mdm-status-badge');
            statusBadge.textContent = (unit.maintenance_status || 'Ongoing').toUpperCase();
            
            document.getElementById('maintenanceDetailsModal').classList.remove('hidden');
            document.getElementById('mdm-parts-loading').classList.remove('hidden');
            document.getElementById('mdm-parts-list').classList.add('hidden');
            document.getElementById('mdm-parts-list').innerHTML = '';

            fetch(`/maintenance/${maintenanceId}/parts`, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(r => r.json())
            .then(data => {
                document.getElementById('mdm-parts-loading').classList.add('hidden');
                const list = document.getElementById('mdm-parts-list');
                list.classList.remove('hidden');
                
                if (data.success && data.data && data.data.length > 0) {
                    data.data.forEach(p => {
                        const supplier = p.supplier ? `<span class="px-1.5 py-0.5 bg-gray-100 text-gray-500 rounded text-[9px] font-bold uppercase truncate max-w-[100px]" title="${p.supplier}">${p.supplier}</span>` : '';
                        list.innerHTML += `
                            <li class="px-4 py-3 flex justify-between items-start gap-3 hover:bg-orange-50/30 transition-colors">
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-semibold text-gray-800 truncate">${p.part_name}</p>
                                    <div class="flex items-center gap-2 mt-1">
                                        <span class="text-[10px] text-gray-500 font-bold bg-white border border-gray-200 px-1.5 py-0.5 rounded shadow-sm">x${p.quantity}</span>
                                        ${supplier}
                                    </div>
                                </div>
                                <div class="text-right shrink-0">
                                    <p class="text-sm font-black text-orange-600">₱${(parseFloat(p.total) || 0).toLocaleString('en-PH', {minimumFractionDigits:2})}</p>
                                    <p class="text-[9px] text-gray-400 font-medium">₱${(parseFloat(p.price) || 0).toLocaleString('en-PH', {minimumFractionDigits:2})} / ea</p>
                                </div>
                            </li>
                        `;
                    });
                } else {
                    list.innerHTML = `
                        <li class="py-8 text-center">
                            <div class="w-10 h-10 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-2">
                                <i data-lucide="package-x" class="w-5 h-5 text-gray-400"></i>
                            </div>
                            <p class="text-xs font-bold text-gray-500 uppercase tracking-widest">No specific parts listed</p>
                            <p class="text-[10px] text-gray-400 mt-1">${unit.description || 'See description for details'}</p>
                        </li>
                    `;
                }
                if (typeof lucide !== 'undefined') lucide.createIcons();
            })
            .catch(err => {
                document.getElementById('mdm-parts-loading').innerHTML = `<p class="text-xs text-red-500"><i data-lucide="alert-circle" class="w-4 h-4 inline mr-1"></i> Failed to load parts</p>`;
                if (typeof lucide !== 'undefined') lucide.createIcons();
            });
        }

        function hideMaintenanceDetailsModal() {
            document.getElementById('maintenanceDetailsModal').classList.add('hidden');
        }
        
        function loadMaintenanceUnitsData() {
            const filter = window.currentMaintenanceFilter || 'all';
            const url = `/api/maintenance-units?filter=${filter}`;
            
            fetch(url)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        displayMaintenanceUnitsData(data);
                    } else {
                        showMaintenanceError(data.message);
                    }
                })
                .catch(error => {
                    console.error('Error loading maintenance units data:', error);
                    showMaintenanceError('Error loading maintenance units data. Please try again.');
                });
        }
        
        function setMaintenanceFilter(filter) {
            window.currentMaintenanceFilter = filter;
            updateMaintenanceFilterUI(filter);
            loadMaintenanceUnitsData();
        }
        
        function updateMaintenanceFilterUI(filter) {
            const filters = ['all', 'preventive', 'corrective', 'emergency', 'complete'];
            filters.forEach(f => {
                const btn = document.getElementById('mFilter' + f.charAt(0).toUpperCase() + f.slice(1));
                if (btn) {
                    if (f === filter) {
                        btn.classList.remove('text-white', 'hover:bg-white/10', 'font-medium');
                        btn.classList.add('bg-white', 'text-orange-600', 'font-bold', 'shadow-sm');
                    } else {
                        btn.classList.add('text-white', 'hover:bg-white/10', 'font-medium');
                        btn.classList.remove('bg-white', 'text-orange-600', 'font-bold', 'shadow-sm');
                    }
                }
            });
        }
        
        function displayMaintenanceUnitsData(data) {
            const grid = document.getElementById('maintenanceGrid');
            const units = data.units || [];
            const stats = data.stats || {};
            const filter = window.currentMaintenanceFilter || 'all';
            
            // Update summary stats (Global Overview)
            document.getElementById('maintenanceUnitsCount').textContent = stats.total_maintenance || 0;
            document.getElementById('preventiveMaintenanceCount').textContent = stats.preventive_maintenance || 0;
            document.getElementById('correctiveMaintenanceCount').textContent = stats.corrective_maintenance || 0;
            document.getElementById('emergencyMaintenanceCount').textContent = stats.emergency_maintenance || 0;
            document.getElementById('completedTotalCount').textContent = stats.completed_total || 0;
            
            // Store original data for filtering
            window.originalMaintenanceData = units;
            window.maintenanceSortOrder = window.maintenanceSortOrder || 'desc';
            
            // Render maintenance units
            filterMaintenanceUnits();
            
            // Re-initialize Lucide icons
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        }
        
        function renderMaintenanceUnits(units) {
            const grid = document.getElementById('maintenanceGrid');
            const filter = window.currentMaintenanceFilter || 'all';
            
            if (units.length === 0) {
                grid.innerHTML = `
                    <div class="col-span-full text-center py-20">
                        <div class="inline-flex flex-col items-center">
                            <div class="p-4 bg-gray-100 rounded-full mb-4">
                                <i data-lucide="wrench" class="w-8 h-8 text-gray-400"></i>
                            </div>
                            <span class="text-xl text-gray-600 font-semibold mb-2">No maintenance units found</span>
                            <p class="text-sm text-gray-400">Try adjusting your search or filter</p>
                        </div>
                    </div>
                `;
                return;
            }
            
            grid.innerHTML = units.map(unit => {
                const isComplete = filter === 'complete';
                const mainDate = isComplete ? (unit.end_date || unit.start_date) : unit.start_date;
                const statusColor = isComplete ? 'border-green-500' : 'border-orange-500';
                const typeColor = isComplete ? 'text-green-600' : 'text-orange-600';
                const iconBg = isComplete ? 'bg-green-100' : 'bg-orange-100';
                const iconColor = isComplete ? 'text-green-600' : 'text-orange-600';

                return `
                <div onclick="showMaintenanceDetailsModal(${unit.maintenance_id})" class="cursor-pointer bg-white rounded-xl shadow-md hover:shadow-lg transition-all duration-300 overflow-hidden border-l-4 ${statusColor} hover:scale-102">
                    <div class="p-4">
                        <!-- Header -->
                        <div class="flex items-start justify-between mb-3">
                            <div class="flex items-center gap-3">
                                <div class="p-2 ${iconBg} rounded-lg">
                                    <i data-lucide="wrench" class="w-4 h-4 ${iconColor}"></i>
                                </div>
                                <div>
                                    <h4 class="text-lg font-bold text-gray-900">${unit.plate_number || 'N/A'}</h4>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="text-lg font-bold ${typeColor}">${unit.maintenance_type || 'Unknown'}</div>
                                <div class="text-xs text-gray-500">${mainDate || 'N/A'}</div>
                            </div>
                        </div>
                        
                        <!-- Maintenance Details -->
                        <div class="bg-gray-50 rounded-lg p-3 mb-3">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm font-medium text-gray-900">Status: ${unit.maintenance_status || 'Unknown'}</span>
                                <span class="text-xs font-bold text-orange-600">${isComplete ? '₱' + (unit.maintenance_cost || 0).toLocaleString('en-PH', {minimumFractionDigits: 2}) : (unit.estimated_completion ? 'Est: ' + unit.estimated_completion : '')}</span>
                            </div>
                            <div class="text-xs text-gray-600">
                                <span class="font-medium">Description:</span> ${unit.description || 'No description available'}
                            </div>
                        </div>
                        
                        <!-- Footer -->
                        <div class="flex items-center justify-between text-xs text-gray-500">
                            <span class="flex items-center gap-1">
                                <i data-lucide="calendar" class="w-3 h-3"></i>
                                ${isComplete ? 'Completed: ' + (unit.end_date || 'N/A') : 'Started: ' + (unit.start_date || 'N/A')}
                            </span>
                            <span class="flex items-center gap-1">
                                <i data-lucide="check-circle" class="w-3 h-3"></i>
                                ${unit.maintenance_status || 'Unknown'}
                            </span>
                        </div>
                    </div>
                </div>
            `;}).join('');
            
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        }
        
        function filterMaintenanceUnits() {
            const searchTerm = document.getElementById('maintenanceSearchInput').value.toLowerCase();
            const filter = window.currentMaintenanceFilter || 'all';
            
            let filteredUnits = [...(window.originalMaintenanceData || [])];
            
            // Apply search filter
            if (searchTerm) {
                filteredUnits = filteredUnits.filter(unit => {
                    const searchableText = [
                        unit.plate_number || '',
                        unit.maintenance_type || '',
                        unit.maintenance_status || '',
                        unit.description || '',
                        unit.start_date || '',
                        unit.end_date || '',
                        unit.estimated_completion || ''
                    ].join(' ').toLowerCase();
                    
                    return searchableText.includes(searchTerm);
                });
            }

            // Apply Sort Newest First (Backend already sorts, but search needs re-render)
            filteredUnits.sort((a, b) => {
                const dateA = new Date((filter === 'complete' ? a.end_date : a.start_date) || '1970-01-01');
                const dateB = new Date((filter === 'complete' ? b.end_date : b.start_date) || '1970-01-01');
                return dateB - dateA;
            });
            
            window.currentFilteredMaintenanceData = filteredUnits;
            renderMaintenanceUnits(filteredUnits);
        }

        // ToggleMaintenanceSort is now handled by buttons but keeping for compatibility if needed
        function toggleMaintenanceSort() {
            filterMaintenanceUnits();
        }
        
        function clearMaintenanceSearch() {
            document.getElementById('maintenanceSearchInput').value = '';
            filterMaintenanceUnits();
        }
        
        function showMaintenanceError(message, debugInfo = null) {
            const grid = document.getElementById('maintenanceGrid');
            const debugHtml = debugInfo ? `
                <div class="mt-4 p-3 bg-gray-100 rounded-lg text-xs">
                    <h4 class="font-bold text-gray-700 mb-2">Debug Information:</h4>
                    <pre class="text-gray-600 whitespace-pre-wrap">${JSON.stringify(debugInfo, null, 2)}</pre>
                </div>
            ` : '';
            
            grid.innerHTML = `
                <div class="col-span-full text-center py-20">
                    <div class="inline-flex flex-col items-center">
                        <div class="p-4 bg-red-100 rounded-full mb-4">
                            <i data-lucide="alert-circle" class="w-8 h-8 text-red-600"></i>
                        </div>
                        <span class="text-xl text-gray-600 font-semibold mb-2">Error Loading Maintenance Data</span>
                        <p class="text-sm text-gray-400 mb-4">${message}</p>
                        <div class="flex gap-2">
                            <button onclick="loadMaintenanceUnitsData()" class="px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700 transition-colors">
                                <i data-lucide="refresh-cw" class="w-4 h-4 inline mr-2"></i>
                                Retry
                            </button>
                            <button onclick="testMaintenanceAPI()" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                                <i data-lucide="bug" class="w-4 h-4 inline mr-2"></i>
                                Test API
                            </button>
                        </div>
                        ${debugHtml}
                    </div>
                </div>
            `;
            
            // Re-initialize Lucide icons
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        }
        
        function testMaintenanceAPI() {
            const grid = document.getElementById('maintenanceGrid');
            grid.innerHTML = `
                <div class="col-span-full text-center py-20">
                    <div class="inline-flex flex-col items-center">
                        <div class="p-4 bg-blue-100 rounded-full mb-4">
                            <i data-lucide="bug" class="w-8 h-8 text-blue-600"></i>
                        </div>
                        <span class="text-xl text-gray-600 font-semibold mb-2">Testing API Connection</span>
                        <p class="text-sm text-gray-400 mb-4">Checking API endpoint...</p>
                        <div class="w-64 bg-gray-200 rounded-full h-2 mb-4">
                            <div class="bg-blue-600 h-2 rounded-full animate-pulse" style="width: 60%"></div>
                        </div>
                    </div>
                </div>
            `;
            
            // Test the API endpoint
            fetch('/api/maintenance-units')
                .then(response => {
                    return response.text();
                })
                .then(text => {
                    try {
                        const data = JSON.parse(text);
                        showMaintenanceError('API Test Complete - Check Console for Details', {
                            response_status: 'success',
                            data_keys: Object.keys(data),
                            data: data
                        });
                    } catch (parseError) {
                        showMaintenanceError('API Test Complete - JSON Parse Error', {
                            response_status: 'parse_error',
                            raw_response: text.substring(0, 500) + (text.length > 500 ? '...' : ''),
                            parse_error: parseError.message
                        });
                    }
                })
                .catch(error => {
                    showMaintenanceError('API Test Complete - Fetch Error', {
                        response_status: 'fetch_error',
                        error: error.message,
                        stack: error.stack
                    });
                });
        }

        // Active Drivers Modal Functions
        function showActiveDriversModal() {
            document.getElementById('activeDriversModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
            loadActiveDriversData();
        }
        
        function hideActiveDriversModal() {
            document.getElementById('activeDriversModal').classList.add('hidden');
            document.body.style.overflow = 'auto';
        }
        
        function loadActiveDriversData() {
            fetch('/api/active-drivers')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        displayActiveDriversData(data);
                    } else {
                        showActiveDriversError(data.message);
                    }
                })
                .catch(error => {
                    showActiveDriversError('Error loading active drivers data. Please try again.');
                });
        }
        
        function displayActiveDriversData(data) {
            const grid = document.getElementById('activeDriversGrid');
            const drivers = data.drivers || [];
            const stats = data.stats || {};
            
            // Initialize Sort Order
            window.driversSortOrder = window.driversSortOrder || 'asc';
            updateDriversSortUI();
            
            // Update summary stats
            document.getElementById('totalDriversCount').textContent = stats.total_drivers || 0;
            document.getElementById('vacantDriversCount').textContent = stats.vacant_drivers || 0;
            document.getElementById('activeWithUnitsCount').textContent = stats.active_with_units || 0;
            document.getElementById('topPerformersCount').textContent = stats.top_performers || 0;
            
            // Store original data for filtering
            window.originalActiveDriversData = drivers;
            window.currentFilteredActiveDriversData = drivers;
            
            // Render active drivers
            renderActiveDrivers(drivers);
            
            // Re-initialize Lucide icons
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        }
        
        function renderActiveDrivers(drivers) {
            const grid = document.getElementById('activeDriversGrid');
            
            if (drivers.length === 0) {
                grid.innerHTML = `
                    <div class="col-span-full text-center py-20">
                        <div class="inline-flex flex-col items-center">
                            <div class="p-4 bg-gray-100 rounded-full mb-4">
                                <i data-lucide="users" class="w-8 h-8 text-gray-400"></i>
                            </div>
                            <span class="text-xl text-gray-600 font-semibold mb-2">No active drivers found</span>
                            <p class="text-sm text-gray-400">Try adjusting your search or date filter</p>
                        </div>
                    </div>
                `;
                return;
            }
            
            grid.innerHTML = drivers.map(driver => `
                <div onclick="openDriverDetails(${driver.id})" class="bg-white rounded-xl shadow-md hover:shadow-lg transition-all duration-300 overflow-hidden border-l-4 border-blue-500 hover:scale-102 cursor-pointer">
                    <div class="p-4">
                        <!-- Header -->
                        <div class="flex items-start justify-between mb-3">
                            <div class="flex items-center gap-3">
                                <div class="p-2 bg-blue-100 rounded-lg">
                                    <i data-lucide="users" class="w-4 h-4 text-blue-600"></i>
                                </div>
                                <div>
                                    <h4 class="text-lg font-bold text-gray-900">${driver.name || 'Unknown'}</h4>
                                    <span class="text-xs text-gray-500">${driver.license_number || 'N/A'}</span>
                                </div>
                            </div>
                            <div class="text-right mt-1 flex flex-col items-end gap-1">
                                ${driver.assigned_units > 0 
                                    ? `<span class="px-2.5 py-1 text-xs font-bold text-green-700 bg-green-100 rounded-full border border-green-200">Assigned</span>
                                       ${driver.plate_numbers ? `<span class="text-[10px] font-black text-gray-400 capitalize bg-gray-100 px-2 rounded-md">${driver.plate_numbers}</span>` : ''}`
                                    : `<span class="px-2.5 py-1 text-xs font-bold text-red-700 bg-red-100 rounded-full border border-red-200">Unassigned</span>`
                                }
                            </div>
                        </div>
                        

                        
                        <!-- Performance Stats -->
                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center gap-2">
                                <div class="w-1.5 h-1.5 rounded-full ${driver.performance_rating === 'excellent' ? 'bg-green-500' : driver.performance_rating === 'good' ? 'bg-yellow-500' : driver.performance_rating === 'average' ? 'bg-orange-500' : 'bg-gray-400'} animate-pulse"></div>
                                <span class="text-xs font-medium text-gray-600">
                                    ${driver.performance_rating ? driver.performance_rating.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase()) : 'Unknown'}
                                </span>
                            </div>
                            <div class="text-right">
                                <div class="text-lg font-bold text-blue-600">₱${driver.total_boundary ? driver.total_boundary.toLocaleString() : '0'}</div>
                                <div class="text-xs text-gray-500">Total Collected</div>
                            </div>
                        </div>
                        
                        <!-- Footer -->
                        <div class="flex items-center justify-between text-xs text-gray-500">
                            <span class="flex items-center gap-1">
                                <i data-lucide="calendar" class="w-3 h-3"></i>
                                ${driver.hire_date || 'No hire date'}
                            </span>
                            <span class="flex items-center gap-1">
                                <i data-lucide="check-circle" class="w-3 h-3"></i>
                                Active
                            </span>
                        </div>
                    </div>
                </div>
            `).join('');
        }
        
        function filterActiveDrivers() {
            const searchTerm = document.getElementById('driversSearchInput').value.toLowerCase();
            const sortOrder = window.driversSortOrder || 'asc';
            
            let filteredDrivers = [...(window.originalActiveDriversData || [])];
            
            // Apply search filter
            if (searchTerm) {
                filteredDrivers = filteredDrivers.filter(driver => {
                    const searchableText = [
                        driver.name || '',
                        driver.license_number || '',
                        driver.phone || '',
                        '', // email removed
                        driver.address || '',
                        driver.performance_rating || '',
                        driver.total_boundary ? driver.total_boundary.toString() : '',
                        driver.assigned_units ? driver.assigned_units.toString() : '',
                        driver.hire_date || ''
                    ].join(' ').toLowerCase();
                    
                    return searchableText.includes(searchTerm);
                    return searchableText.includes(searchTerm);
                });
            }
            
            // Apply Category Filter
            const category = window.currentDriversFilterCategory || 'all';
            if (category === 'vacant') {
                filteredDrivers = filteredDrivers.filter(driver => driver.assigned_units === 0);
            } else if (category === 'active') {
                filteredDrivers = filteredDrivers.filter(driver => driver.assigned_units > 0);
            } else if (category === 'top') {
                filteredDrivers = filteredDrivers.filter(driver => driver.is_top_performer === true);
            }

            // Apply Sorting (Alphabetical by Name)
            filteredDrivers.sort((a, b) => {
                const nameA = (a.name || '').toLowerCase();
                const nameB = (b.name || '').toLowerCase();
                
                if (sortOrder === 'asc') {
                    return nameA.localeCompare(nameB);
                } else {
                    return nameB.localeCompare(nameA);
                }
            });
            
            window.currentFilteredActiveDriversData = filteredDrivers;
            renderActiveDrivers(filteredDrivers);
        }
        
        function toggleDriversSort() {
            window.driversSortOrder = window.driversSortOrder === 'asc' ? 'desc' : 'asc';
            updateDriversSortUI();
            filterActiveDrivers();
        }

        function updateDriversSortUI() {
            const icon = document.getElementById('driversSortIcon');
            const text = document.getElementById('driversSortText');
            const order = window.driversSortOrder || 'asc';
            
            if (icon && text) {
                if (order === 'asc') {
                    icon.setAttribute('data-lucide', 'sort-asc');
                    text.textContent = 'A-Z';
                } else {
                    icon.setAttribute('data-lucide', 'sort-desc');
                    text.textContent = 'Z-A';
                }
                
                // Re-initialize Lucide for the new icon
                if (typeof lucide !== 'undefined') {
                    lucide.createIcons();
                }
            }
        }

        function clearDriversSearch() {
            document.getElementById('driversSearchInput').value = '';
            filterActiveDrivers();
        }
        
        function setDriversFilter(category) {
            window.currentDriversFilterCategory = category;
            
            const buttons = {
                'all': document.getElementById('dFilterAll'),
                'vacant': document.getElementById('dFilterVacant'),
                'active': document.getElementById('dFilterActive'),
                'top': document.getElementById('dFilterTop')
            };
            
            Object.keys(buttons).forEach(key => {
                const btn = buttons[key];
                if (!btn) return;
                
                if (key === category) {
                    btn.className = 'px-4 py-1.5 rounded-lg text-sm font-bold transition-all duration-200 bg-white text-blue-600 shadow-sm';
                } else {
                    btn.className = 'px-4 py-1.5 rounded-lg text-sm font-medium transition-all duration-200 text-white hover:bg-white/10';
                }
            });
            
            filterActiveDrivers();
        }
        
        function showActiveDriversError(message, debugInfo = null) {
            const grid = document.getElementById('activeDriversGrid');
            const debugHtml = debugInfo ? `
                <div class="mt-4 p-3 bg-gray-100 rounded-lg text-xs">
                    <h4 class="font-bold text-gray-700 mb-2">Debug Information:</h4>
                    <pre class="text-gray-600 whitespace-pre-wrap">${JSON.stringify(debugInfo, null, 2)}</pre>
                </div>
            ` : '';
            
            grid.innerHTML = `
                <div class="col-span-full text-center py-20">
                    <div class="inline-flex flex-col items-center">
                        <div class="p-4 bg-red-100 rounded-full mb-4">
                            <i data-lucide="alert-circle" class="w-8 h-8 text-red-600"></i>
                        </div>
                        <span class="text-xl text-gray-600 font-semibold mb-2">Error Loading Driver Data</span>
                        <p class="text-sm text-gray-400 mb-4">${message}</p>
                        <div class="flex gap-2">
                            <button onclick="loadActiveDriversData()" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                                <i data-lucide="refresh-cw" class="w-4 h-4 inline mr-2"></i>
                                Retry
                            </button>
                            <button onclick="testActiveDriversAPI()" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                                <i data-lucide="bug" class="w-4 h-4 inline mr-2"></i>
                                Test API
                            </button>
                        </div>
                        ${debugHtml}
                    </div>
                </div>
            `;
            
            // Re-initialize Lucide icons
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        }
        
        function testActiveDriversAPI() {
            const grid = document.getElementById('activeDriversGrid');
            grid.innerHTML = `
                <div class="col-span-full text-center py-20">
                    <div class="inline-flex flex-col items-center">
                        <div class="p-4 bg-blue-100 rounded-full mb-4">
                            <i data-lucide="bug" class="w-8 h-8 text-blue-600"></i>
                        </div>
                        <span class="text-xl text-gray-600 font-semibold mb-2">Testing API Connection</span>
                        <p class="text-sm text-gray-400 mb-4">Checking API endpoint...</p>
                        <div class="w-64 bg-gray-200 rounded-full h-2 mb-4">
                            <div class="bg-blue-600 h-2 rounded-full animate-pulse" style="width: 60%"></div>
                        </div>
                    </div>
                </div>
            `;
            
            // Test the API endpoint
            fetch('/api/active-drivers')
                .then(response => {
                    return response.text();
                })
                .then(text => {
                    try {
                        const data = JSON.parse(text);
                        showActiveDriversError('API Test Complete - Check Console for Details', {
                            response_status: 'success',
                            data_keys: Object.keys(data),
                            data: data
                        });
                    } catch (parseError) {
                        showActiveDriversError('API Test Complete - JSON Parse Error', {
                            response_status: 'parse_error',
                            raw_response: text.substring(0, 500) + (text.length > 500 ? '...' : ''),
                            parse_error: parseError.message
                        });
                    }
                })
                .catch(error => {
                    showActiveDriversError('API Test Complete - Fetch Error', {
                        response_status: 'fetch_error',
                        error: error.message,
                        stack: error.stack
                    });
                });
        }

        // Coding Units Modal Functions
        function showCodingUnitsModal() {
            document.getElementById('codingUnitsModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
            loadCodingUnitsData();
        }
        
        function hideCodingUnitsModal() {
            document.getElementById('codingUnitsModal').classList.add('hidden');
            document.body.style.overflow = 'auto';
        }
        
        function loadCodingUnitsData() {
            fetch('/api/coding-units')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        displayCodingUnitsData(data);
                    } else {
                        showCodingError(data.message);
                    }
                })
                .catch(error => {
                    showCodingError('Error loading coding units data. Please try again.');
                });
        }
        
        function displayCodingUnitsData(data) {
            const grid = document.getElementById('codingGrid');
            const units = data.units || [];
            const stats = data.stats || {};
            
            // Update summary stats
            updateCodingSummary(units);
            
            // Store original data for filtering
            window.originalCodingUnitsData = units;
            window.currentFilteredCodingUnitsData = units;
            
            // Render coding units
            renderCodingUnits(units);
            
            // Re-initialize Lucide icons
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        }
        
        function renderCodingUnits(units) {
            const grid = document.getElementById('codingGrid');
            
            if (units.length === 0) {
                grid.innerHTML = `
                    <div class="col-span-full text-center py-20">
                        <div class="inline-flex flex-col items-center">
                            <div class="p-4 bg-gray-100 rounded-full mb-4">
                                <i data-lucide="code" class="w-8 h-8 text-gray-400"></i>
                            </div>
                            <span class="text-xl text-gray-600 font-semibold mb-2">No coding units found</span>
                            <p class="text-sm text-gray-400">Try adjusting your search or date filter</p>
                        </div>
                    </div>
                `;
                return;
            }
            grid.innerHTML = units.map(unit => {
                const hasDriver1 = unit.driver1_name && unit.driver1_name.trim() !== '';
                const hasDriver2 = unit.driver2_name && unit.driver2_name.trim() !== '';
                const hasAnyDriver = hasDriver1 || hasDriver2;

                let driverOverlayHTML = `
                    <div class="absolute inset-0 bg-slate-900/95 opacity-0 group-hover:opacity-100 transition-all duration-250 flex flex-col items-center justify-center gap-1.5 z-10 pointer-events-none rounded-xl">
                        <p class="text-white/60 text-[9px] font-black uppercase tracking-widest mb-0.5">Assigned Drivers</p>
                `;

                if (hasAnyDriver) {
                    if (hasDriver1) {
                        driverOverlayHTML += `
                            <div class="flex items-center gap-2.5 bg-white/10 rounded-lg px-3 py-1.5 w-[90%]">
                                <div class="w-5 h-5 rounded-full bg-blue-500 flex items-center justify-center flex-shrink-0 shadow">
                                    <span class="text-white text-[8px] font-black">P1</span>
                                </div>
                                <div class="text-left overflow-hidden min-w-0">
                                    <p class="text-[7px] text-blue-300 font-bold uppercase tracking-wider leading-none truncate">Primary 1</p>
                                    <p class="text-white text-[11px] font-black leading-tight truncate">${unit.driver1_name.trim()}</p>
                                </div>
                            </div>
                        `;
                    }
                    if (hasDriver2) {
                        driverOverlayHTML += `
                            <div class="flex items-center gap-2.5 bg-white/10 rounded-lg px-3 py-1.5 w-[90%]">
                                <div class="w-5 h-5 rounded-full bg-purple-500 flex items-center justify-center flex-shrink-0 shadow">
                                    <span class="text-white text-[8px] font-black">P2</span>
                                </div>
                                <div class="text-left overflow-hidden min-w-0">
                                    <p class="text-[7px] text-purple-300 font-bold uppercase tracking-wider leading-none truncate">Primary 2</p>
                                    <p class="text-white text-[11px] font-black leading-tight truncate">${unit.driver2_name.trim()}</p>
                                </div>
                            </div>
                        `;
                    }
                } else {
                    driverOverlayHTML += `
                        <div class="flex flex-col items-center justify-center opacity-50">
                            <i data-lucide="user-x" class="w-5 h-5 text-white mb-1.5"></i>
                            <span class="text-[9px] text-white font-bold uppercase tracking-widest text-center leading-tight">No Driver<br>Assigned</span>
                        </div>
                    `;
                }

                driverOverlayHTML += `</div>`;

                return `
                <div class="bg-white rounded-xl shadow-sm hover:shadow-md transition-all duration-300 overflow-hidden border border-gray-100 hover:border-purple-200 relative group min-h-[130px]">
                    ${driverOverlayHTML}
                    <div class="p-4 border-l-4 border-purple-500 h-full flex flex-col relative z-0">
                        <!-- Header -->
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center gap-3">
                                <div class="p-2.5 bg-purple-50 rounded-lg text-purple-600">
                                    <i data-lucide="car" class="w-5 h-5"></i>
                                </div>
                                <div>
                                    <h4 class="text-xl font-black text-gray-900 tracking-tight leading-none">${unit.plate_number || 'N/A'}</h4>
                                </div>
                            </div>
                            <div class="text-right">
                                <span class="px-2.5 py-1 bg-purple-50 text-purple-700 text-[10px] font-black uppercase tracking-widest rounded-md border border-purple-100">${unit.coding_type || 'Coding'}</span>
                            </div>
                        </div>
                        
                        <div class="mt-auto">
                            <!-- Details / Footer -->
                            <div class="pt-3 border-t border-gray-100 flex items-center justify-between">
                                <div class="flex items-center gap-1.5 text-[11px] font-bold text-gray-500 uppercase tracking-wide">
                                    <i data-lucide="calendar" class="w-3.5 h-3.5 text-gray-400"></i>
                                    ${unit.start_date ? unit.start_date : (unit.coding_day !== 'Unknown' ? 'Every ' + unit.coding_day : 'No date')}
                                </div>
                                <div class="flex items-center gap-1.5 text-[10px] font-black uppercase tracking-widest text-gray-400">
                                    <i data-lucide="activity" class="w-3.5 h-3.5 text-gray-400"></i>
                                    ${unit.status || 'Unknown'}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `}).join('');
        }
        
        const getUnitPeriod = (unit) => {
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            const tomorrow = new Date(today);
            tomorrow.setDate(today.getDate() + 1);
            
            const formatDate = (date) => {
                const year = date.getFullYear();
                const month = String(date.getMonth() + 1).padStart(2, '0');
                const day = String(date.getDate()).padStart(2, '0');
                return `${year}-${month}-${day}`;
            };
            
            const todayStr = formatDate(today);
            const tomorrowStr = formatDate(tomorrow);
            
            const dayNames = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
            const todayDayIndex = today.getDay();
            const tomorrowDayIndex = tomorrow.getDay();
            
            const unitDate = unit.start_date;
            const codingDay = (unit.coding_day || '').trim().toLowerCase();
            const isCompleted = unit.coding_status === 'completed';
            
            if (isCompleted || (unitDate && unitDate < todayStr)) return 'past';
            if (unitDate === todayStr || (!unitDate && codingDay === dayNames[todayDayIndex])) return 'today';
            if (unitDate === tomorrowStr || (!unitDate && codingDay === dayNames[tomorrowDayIndex])) return 'tomorrow';
            
            const codingDayIndex = dayNames.indexOf(codingDay);
            if (!unitDate && codingDayIndex !== -1 && codingDayIndex < todayDayIndex) return 'past';
            
            return 'future';
        };

        function updateCodingSummary(units) {
            const counts = { today: 0, tomorrow: 0, past: 0 };
            
            units.forEach(unit => {
                const period = getUnitPeriod(unit);
                if (period === 'today') counts.today++;
                else if (period === 'tomorrow') counts.tomorrow++;
                else if (period === 'past') counts.past++;
            });
            
            document.getElementById('todayCodingCount').textContent = counts.today;
            document.getElementById('tomorrowCodingCount').textContent = counts.tomorrow;
            document.getElementById('pastCodingCount').textContent = counts.past;
        }
        
        window.currentCodingPeriod = 'today';

        function setCodingPeriod(period) {
            window.currentCodingPeriod = period;
            
            // Update UI
            const periods = ['today', 'tomorrow', 'past'];
            periods.forEach(p => {
                const btn = document.getElementById('btn-' + p + '-coding');
                if (btn) {
                    if (p === period) {
                        btn.classList.remove('text-white/70', 'hover:text-white', 'hover:bg-white/10');
                        btn.classList.add('bg-white', 'text-purple-700');
                    } else {
                        btn.classList.add('text-white/70', 'hover:text-white', 'hover:bg-white/10');
                        btn.classList.remove('bg-white', 'text-purple-700');
                    }
                }
            });
            
            filterCodingUnits();
        }

        function filterCodingUnits() {
            const searchTerm = document.getElementById('codingSearchInput').value.toLowerCase();
            const currentPeriod = window.currentCodingPeriod || 'today';
            
            let filteredUnits = window.originalCodingUnitsData || [];

            // Apply period filter
            filteredUnits = filteredUnits.filter(unit => {
                return getUnitPeriod(unit) === currentPeriod;
            });
            
            // Apply search filter
            if (searchTerm) {
                filteredUnits = filteredUnits.filter(unit => {
                    const searchableText = [
                        unit.plate_number || '',
                        unit.coding_type || '',
                        unit.status || '',
                        unit.description || '',
                        unit.start_date || '',
                        unit.estimated_completion || ''
                    ].join(' ').toLowerCase();
                    
                    return searchableText.includes(searchTerm);
                });
            }
            
            window.currentFilteredCodingUnitsData = filteredUnits;
            renderCodingUnits(filteredUnits);
        }
        
        function clearCodingSearch() {
            document.getElementById('codingSearchInput').value = '';
            filterCodingUnits();
        }
        
        function showCodingError(message, debugInfo = null) {
            const grid = document.getElementById('codingGrid');
            const debugHtml = debugInfo ? `
                <div class="mt-4 p-3 bg-gray-100 rounded-lg text-xs">
                    <h4 class="font-bold text-gray-700 mb-2">Debug Information:</h4>
                    <pre class="text-gray-600 whitespace-pre-wrap">${JSON.stringify(debugInfo, null, 2)}</pre>
                </div>
            ` : '';
            
            grid.innerHTML = `
                <div class="col-span-full text-center py-20">
                    <div class="inline-flex flex-col items-center">
                        <div class="p-4 bg-red-100 rounded-full mb-4">
                            <i data-lucide="alert-circle" class="w-8 h-8 text-red-600"></i>
                        </div>
                        <span class="text-xl text-gray-600 font-semibold mb-2">Error Loading Coding Data</span>
                        <p class="text-sm text-gray-400 mb-4">${message}</p>
                        <div class="flex gap-2">
                            <button onclick="loadCodingUnitsData()" class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors">
                                <i data-lucide="refresh-cw" class="w-4 h-4 inline mr-2"></i>
                                Retry
                            </button>
                            <button onclick="testCodingUnitsAPI()" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                                <i data-lucide="bug" class="w-4 h-4 inline mr-2"></i>
                                Test API
                            </button>
                        </div>
                        ${debugHtml}
                    </div>
                </div>
            `;
            
            // Re-initialize Lucide icons
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        }
        
        function testCodingUnitsAPI() {
            const grid = document.getElementById('codingGrid');
            grid.innerHTML = `
                <div class="col-span-full text-center py-20">
                    <div class="inline-flex flex-col items-center">
                        <div class="p-4 bg-blue-100 rounded-full mb-4">
                            <i data-lucide="bug" class="w-8 h-8 text-blue-600"></i>
                        </div>
                        <span class="text-xl text-gray-600 font-semibold mb-2">Testing API Connection</span>
                        <p class="text-sm text-gray-400 mb-4">Checking API endpoint...</p>
                        <div class="w-64 bg-gray-200 rounded-full h-2 mb-4">
                            <div class="bg-blue-600 h-2 rounded-full animate-pulse" style="width: 60%"></div>
                        </div>
                    </div>
                </div>
            `;
            
            // Test the API endpoint
            fetch('/api/coding-units')
                .then(response => {
                    return response.text();
                })
                .then(text => {
                    try {
                        const data = JSON.parse(text);
                        showCodingError('API Test Complete - Check Console for Details', {
                            response_status: 'success',
                            data_keys: Object.keys(data),
                            data: data
                        });
                    } catch (parseError) {
                        showCodingError('API Test Complete - JSON Parse Error', {
                            response_status: 'parse_error',
                            raw_response: text.substring(0, 500) + (text.length > 500 ? '...' : ''),
                            parse_error: parseError.message
                        });
                    }
                })
                .catch(error => {
                    showCodingError('API Test Complete - Fetch Error', {
                        response_status: 'fetch_error',
                        error: error.message,
                        stack: error.stack
                    });
                });
        }

        // Net Income Modal Functions
        function showNetIncomeModal() {
            document.getElementById('netIncomeModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
            
            // Initialize default period to today
            window.currentIncomePeriod = 'today';
            setIncomePeriod('today');
            
            loadIncomeData();
        }
        
        function hideNetIncomeModal() {
            document.getElementById('netIncomeModal').classList.add('hidden');
            document.body.style.overflow = 'auto';
        }
        
        function loadIncomeData() {
            fetch('/api/net-income-details')
                .then(response => {
                    // Check if response is HTML (error page) or JSON
                    const contentType = response.headers.get('content-type');
                    if (contentType && contentType.includes('text/html')) {
                        return response.text().then(text => {
                            throw new Error('API returned HTML instead of JSON. This usually means a Laravel error occurred. Check the Laravel logs for details.');
                        });
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        displayIncomeData(data);
                    } else {
                        showIncomeError(data.message, data.debug_info || null);
                    }
                })
                .catch(error => {
                    showIncomeError('Error loading income data. Please try again.', {
                        fetch_error: error.message,
                        stack: error.stack
                    });
                });
        }
        
        function displayIncomeData(data) {
            const incomeData = data.income_data || [];
            
            // Store original data for filtering
            window.originalIncomeData = incomeData;
            
            // Apply filtering directly via setIncomePeriod
            setIncomePeriod(window.currentIncomePeriod || 'today');
            
            // Re-initialize Lucide icons
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        }
        
        function renderIncomeData(incomeData) {
            const grid = document.getElementById('incomeGrid');
            if (!grid) return;
            
            if (!incomeData || incomeData.length === 0) {
                grid.innerHTML = `
                    <div class="col-span-full py-12 text-center">
                        <div class="bg-gray-50 rounded-xl p-8 border-2 border-dashed border-gray-200">
                            <i data-lucide="info" class="w-8 h-8 text-gray-300 mx-auto mb-3"></i>
                            <p class="text-gray-500 font-medium font-mono">NO TRANSACTIONS FOUND FOR THIS PERIOD</p>
                        </div>
                    </div>
                `;
                return;
            }

            // Receipt-style list (clean table-like rows)
            grid.classList.remove('grid-cols-1', 'md:grid-cols-2');
            grid.classList.add('grid-cols-1');
            
            grid.innerHTML = `
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden font-mono text-sm max-w-4xl mx-auto">
                    <div class="bg-gray-100 px-6 py-3 border-b-2 border-gray-200 flex justify-between text-[11px] font-black text-gray-500 uppercase tracking-widest">
                        <span>Description / Category</span>
                        <span class="text-right">Amount (₱)</span>
                    </div>
                    <div class="divide-y divide-gray-100">
                        ${incomeData.map(item => `
                            <div class="px-6 py-4 flex items-center justify-between hover:bg-gray-50 transition-colors border-l-4 ${item.type === 'income' ? 'border-green-500/20' : 'border-red-500/20'}">
                                <div class="flex items-center gap-4">
                                    <div class="flex-shrink-0 w-8 h-8 rounded bg-gray-50 border border-gray-200 flex items-center justify-center ${item.type === 'income' ? 'text-green-600' : 'text-red-600'}">
                                        <i data-lucide="${item.type === 'income' ? 'arrow-down-left' : 'arrow-up-right'}" class="w-4 h-4"></i>
                                    </div>
                                    <div>
                                        <div class="font-black text-gray-900 tracking-tight">${(item.description || 'Unknown').toUpperCase()}</div>
                                        <div class="flex items-center gap-3 text-[10px] text-gray-400 font-bold mt-0.5">
                                            <span class="text-gray-500">${(item.category || 'GENERAL').toUpperCase()}</span>
                                            <span class="text-gray-300">•</span>
                                            <span>${(item.date || '').split(' ')[0]}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="font-black text-lg ${item.type === 'income' ? 'text-green-600' : 'text-red-600'}">
                                        ${item.type === 'income' ? '+' : '-'} ₱${Math.abs(parseFloat(item.amount) || 0).toLocaleString(undefined, {minimumFractionDigits: 2})}
                                    </div>
                                    <div class="text-[9px] text-gray-400 font-bold uppercase tracking-tighter">${item.source || 'OFFICE'}</div>
                                </div>
                            </div>
                        `).join('')}
                    </div>
                    <div class="bg-gray-50 px-6 py-4 border-t-2 border-dashed border-gray-200 text-center">
                        <p class="text-[10px] text-gray-400 uppercase font-black tracking-[0.2em]">End of transaction list</p>
                    </div>
                </div>
            `;

            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        }
        
        
        function updateIncomeSummary(data) {
            let totalIncome = 0;
            let totalExpenses = 0;
            let breakdown = {
                revenueItems: [],
                maintenanceItems: [],
                officeItems: [],
                maintenanceTotal: 0,
                officeTotal: 0
            };
            
            data.forEach(item => {
                const amount = Math.abs(parseFloat(item.amount) || 0);
                const category = (item.category || '').toLowerCase();
                const type = (item.type || '').toLowerCase();
                const description = (item.description || 'Record').toUpperCase();
                const date = item.date ? new Date(item.date).toLocaleDateString() : '';

                if (item.type === 'income') {
                    totalIncome += amount;
                    breakdown.revenueItems.push({
                        description: description,
                        amount: amount,
                        date: date
                    });
                } else {
                    // Skip coding as per user request
                    if (category.includes('coding') || type === 'coding') {
                        return;
                    }

                    totalExpenses += amount;
                    
                    if (category.includes('maintenance') || type === 'maintenance') {
                        breakdown.maintenanceTotal += amount;
                        breakdown.maintenanceItems.push({
                            description: description,
                            amount: amount,
                            date: date
                        });
                    } else {
                        breakdown.officeTotal += amount;
                        breakdown.officeItems.push({
                            description: description,
                            amount: amount,
                            date: date
                        });
                    }
                }
            });
            
            // Summarize items for Yearly and Monthly views
            if (window.currentIncomePeriod === 'year' || window.currentIncomePeriod === 'month') {
                const summarizeItems = (items) => {
                    const map = {};
                    
                    // Generate dynamic period label
                    let periodLabel = '';
                    if (window.currentIncomePeriod === 'year') {
                        periodLabel = new Date().getFullYear() + ' SUMMARY';
                    } else if (window.currentIncomePeriod === 'month') {
                        const date = new Date();
                        periodLabel = date.toLocaleString('default', { month: 'long', year: 'numeric' }).toUpperCase() + ' SUMMARY';
                    }

                    items.forEach(item => {
                        if (!map[item.description]) {
                            map[item.description] = { 
                                description: item.description, 
                                amount: 0, 
                                date: periodLabel
                            };
                        }
                        map[item.description].amount += item.amount;
                    });
                    return Object.values(map).sort((a, b) => b.amount - a.amount);
                };
                
                breakdown.revenueItems = summarizeItems(breakdown.revenueItems);
                breakdown.maintenanceItems = summarizeItems(breakdown.maintenanceItems);
                breakdown.officeItems = summarizeItems(breakdown.officeItems);
            }
            
            const netIncome = totalIncome - totalExpenses;
            const profitMargin = totalIncome > 0 ? (netIncome / totalIncome) * 100 : 0;
            
            // Helper to format currency
            const fmt = (num) => '₱' + num.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});

            // Update Primary Report Fields
            const safeSet = (id, val) => {
                const el = document.getElementById(id);
                if (el) el.textContent = val;
            };

            safeSet('reportTotalIncome', fmt(totalIncome));
            safeSet('reportTotalIncomePrint', fmt(totalIncome));
            /* Tailwind imports removed to prevent 404s in production */
            safeSet('reportTotalExpenses', fmt(totalExpenses));
            safeSet('reportTotalExpensesPrint', fmt(totalExpenses));
            safeSet('reportMaintenanceTotal', 'Total: ' + fmt(breakdown.maintenanceTotal));
            safeSet('reportGeneralExpensesTotal', 'Total: ' + fmt(breakdown.officeTotal));
            safeSet('reportNetIncome', fmt(netIncome));
            safeSet('reportNetIncomePrint', fmt(netIncome));
            
            const netIncomeEl = document.getElementById('reportNetIncome');
            if (netIncomeEl) {
                netIncomeEl.className = 'text-4xl font-black ' + (netIncome > 0 ? 'text-emerald-400' : (netIncome < 0 ? 'text-red-400' : 'text-white'));
            }
            
            const profitMarginEl = document.getElementById('reportProfitMargin');
            if (profitMarginEl) {
                profitMarginEl.textContent = profitMargin.toFixed(1) + '% Margin';
                profitMarginEl.className = 'ml-2 text-[9px] px-2 py-0.5 rounded-full font-bold ' + 
                    (netIncome > 0 ? 'bg-emerald-500/20 text-emerald-300' : (netIncome < 0 ? 'bg-red-500/20 text-red-300' : 'bg-white/10 text-gray-300'));
            }
            
            safeSet('reportTimestamp', new Date().toLocaleString());
            
            // Helper to render lists
            const renderList = (id, items) => {
                const el = document.getElementById(id);
                if (!el) return;
                
                if (items.length > 0) {
                    el.innerHTML = `
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-gray-50/50 border-b border-gray-100 text-[8px] uppercase tracking-widest text-gray-400">
                                    <th class="px-6 py-2 font-bold w-1/4">Date</th>
                                    <th class="px-6 py-2 font-bold w-1/2">Description</th>
                                    <th class="px-6 py-2 font-bold text-right w-1/4">Amount</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                ${items.map(item => `
                                    <tr class="hover:bg-gray-50/50 transition-colors">
                                        <td class="px-6 py-2 text-[9px] text-gray-400 font-bold uppercase whitespace-nowrap">${item.date}</td>
                                        <td class="px-6 py-2 text-[10px] font-black text-gray-800 tracking-tight">${item.description}</td>
                                        <td class="px-6 py-2 text-xs font-black ${id === 'revenueDetailList' ? 'text-emerald-600' : 'text-red-500'} text-right whitespace-nowrap">${fmt(item.amount)}</td>
                                    </tr>
                                `).join('')}
                            </tbody>
                        </table>
                    `;
                } else {
                    el.innerHTML = `
                        <div class="px-6 py-4 text-center text-[10px] font-bold text-gray-400 uppercase tracking-widest border-b border-gray-50">
                            No records found
                        </div>
                    `;
                }
            };

            renderList('revenueDetailList', breakdown.revenueItems);
            renderList('maintenanceDetailList', breakdown.maintenanceItems);
            renderList('officeExpensesDetailList', breakdown.officeItems);
        }

        function renderIncomeData(data) {
            // Grid rendering is now integrated into updateIncomeSummary
            // This function is kept for compatibility with fetchIncomeData flow
        }

        function printReport() {
            const content = document.getElementById('incomeReport').innerHTML;
            const periodLabel = document.getElementById('reportPeriodLabelPrint');
            const period = periodLabel ? periodLabel.textContent : 'TODAY';

            const iframe = document.createElement('iframe');
            iframe.style.position = 'absolute';
            iframe.style.top = '-9999px';
            iframe.style.left = '-9999px';
            iframe.style.width = '0';
            iframe.style.height = '0';
            document.body.appendChild(iframe);

            const doc = iframe.contentWindow.document;
            doc.open();
            doc.write(`<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Financial Report &mdash; ${period}</title>
    <style>
        @page { margin: 0; size: auto; }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { background: #fff; font-family: 'Segoe UI', system-ui, sans-serif; padding: 20mm; color: #111; }
        h1 { text-align: center; font-size: 24px; font-weight: 900; text-transform: uppercase; letter-spacing: .15em; margin-bottom: 4px; }
        .subtitle { text-align: center; font-size: 11px; color: #64748b; font-weight: 700; letter-spacing: .15em; text-transform: uppercase; margin-bottom: 32px; }
        .section-header { display: flex; justify-content: space-between; align-items: center; padding: 10px 20px; border-radius: 6px 6px 0 0; }
        .sub-header { display: flex; justify-content: space-between; background: #f8f8f8; padding: 6px 20px; border-left: 1px solid #eee; border-right: 1px solid #eee; font-size: 9px; font-weight: 900; text-transform: uppercase; letter-spacing: .12em; color: #94a3b8; }
        table { width: 100%; border-collapse: collapse; border: 1px solid #f0f0f0; border-top: none; margin-bottom: 24px; }
        thead tr { background: #f8fafc; border-bottom: 1px solid #e2e8f0; }
        thead th { padding: 8px 20px; font-size: 8px; text-transform: uppercase; letter-spacing: .12em; color: #94a3b8; font-weight: 700; text-align: left; }
        thead th:last-child { text-align: right; }
        tr { page-break-inside: avoid; break-inside: avoid; }
        tbody tr { border-bottom: 1px solid #f8f8f8; }
        td { padding: 8px 20px; font-size: 11px; color: #1e293b; }
        td:last-child { text-align: right; font-weight: 900; white-space: nowrap; }
        .no-records { padding: 16px 20px; text-align: center; font-size: 10px; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: .1em; border: 1px solid #f0f0f0; border-top: none; margin-bottom: 24px; }
        .footer { text-align: center; margin-top: 40px; padding-top: 16px; border-top: 1px solid #e2e8f0; font-size: 9px; color: #94a3b8; }
        img { max-height: 64px !important; width: auto !important; display: block; margin: 0 auto 8px auto; }
        .print-only { display: block !important; }
        .no-print { display: none !important; }
    </style>
</head>
<body>
    <h1>Financial Report</h1>
    <p class="subtitle">Euro Taxi Management System &mdash; ${period}</p>
    ${content}
    <div class="footer">
        <p>Authenticated Financial Statement &mdash; Generated: ${new Date().toLocaleString()}</p>
    </div>
</body>
</html>`);
            doc.close();
            
            setTimeout(() => {
                iframe.contentWindow.focus();
                iframe.contentWindow.print();
                setTimeout(() => { document.body.removeChild(iframe); }, 1000);
            }, 300);
        }

        // --- Expenses Modal Functions ---
        function showExpensesModal() {
            document.getElementById('expensesModal').classList.remove('hidden');
            setExpensesPeriod('today');
        }

        function hideExpensesModal() {
            document.getElementById('expensesModal').classList.add('hidden');
        }

        function setExpensesPeriod(period) {
            window.currentExpensesPeriod = period;
            
            const periodLabels = {
                'today': 'Period: TODAY',
                'week': 'Period: THIS WEEK',
                'month': 'Period: THIS MONTH',
                'year': 'Period: THIS YEAR'
            };
            const labelText = periodLabels[period] || 'Period: Custom';
            const labelElPrint = document.getElementById('expensesPeriodLabelPrint');
            if (labelElPrint) labelElPrint.textContent = labelText;

            // Update button styles
            document.querySelectorAll('[id^="btn-"][id$="-expenses"]').forEach(btn => {
                btn.classList.remove('bg-white', 'text-red-700');
                btn.classList.add('text-white/70', 'hover:text-white', 'hover:bg-white/10');
            });
            
            const activeBtn = document.getElementById('btn-' + period + '-expenses');
            if (activeBtn) {
                activeBtn.classList.remove('text-white/70', 'hover:text-white', 'hover:bg-white/10');
                activeBtn.classList.add('bg-white', 'text-red-700');
            }
            
            updateExpensesSummary(period);
        }

        async function updateExpensesSummary(period) {
            try {
                const response = await fetch('/api/net-income-details');
                const result = await response.json();
                
                if (result.success) {
                    const filteredData = filterIncomeByPeriod(result.income_data, period);
                    renderExpensesReport(filteredData);
                }
            } catch (error) {
                console.error("Error fetching expenses data:", error);
            }
        }

        function printExpensesNewTab() {
            const content = document.getElementById('expensesReport').innerHTML;
            const periodLabel = document.getElementById('expensesPeriodLabelPrint');
            const period = periodLabel ? periodLabel.textContent : 'TODAY';

            const iframe = document.createElement('iframe');
            iframe.style.position = 'absolute';
            iframe.style.top = '-9999px';
            iframe.style.left = '-9999px';
            iframe.style.width = '0';
            iframe.style.height = '0';
            document.body.appendChild(iframe);

            const doc = iframe.contentWindow.document;
            doc.open();
            doc.write(`<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Expense Statement &mdash; ${period}</title>
    <style>
        @page { margin: 0; size: auto; }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { background: #fff; font-family: 'Segoe UI', system-ui, sans-serif; padding: 20mm; color: #111; }
        h1 { text-align: center; font-size: 24px; font-weight: 900; text-transform: uppercase; letter-spacing: .15em; margin-bottom: 4px; }
        .subtitle { text-align: center; font-size: 11px; color: #64748b; font-weight: 700; letter-spacing: .15em; text-transform: uppercase; margin-bottom: 32px; }
        .section-header { display: flex; justify-content: space-between; align-items: center; background: #7f1d1d; color: white; padding: 10px 20px; border-radius: 6px 6px 0 0; }
        .section-header span { font-size: 11px; font-weight: 900; text-transform: uppercase; letter-spacing: .08em; }
        .sub-header { display: flex; justify-content: space-between; background: #f8f8f8; padding: 6px 20px; border-left: 1px solid #eee; border-right: 1px solid #eee; font-size: 9px; font-weight: 900; text-transform: uppercase; letter-spacing: .12em; color: #94a3b8; }
        .sub-total { color: #dc2626; }
        table { width: 100%; border-collapse: collapse; border: 1px solid #f0f0f0; border-top: none; margin-bottom: 24px; }
        thead tr { background: #f8fafc; border-bottom: 1px solid #e2e8f0; }
        thead th { padding: 8px 20px; font-size: 8px; text-transform: uppercase; letter-spacing: .12em; color: #94a3b8; font-weight: 700; text-align: left; }
        thead th:last-child { text-align: right; }
        tr { page-break-inside: avoid; break-inside: avoid; }
        tbody tr { border-bottom: 1px solid #f8f8f8; }
        tbody tr:hover { background: #fafafa; }
        td { padding: 8px 20px; font-size: 11px; color: #1e293b; }
        td.date { color: #94a3b8; font-weight: 700; font-size: 9px; text-transform: uppercase; }
        td.amount { text-align: right; font-weight: 900; color: #dc2626; white-space: nowrap; }
        .no-records { padding: 16px 20px; text-align: center; font-size: 10px; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: .1em; border: 1px solid #f0f0f0; border-top: none; margin-bottom: 24px; }
        .footer { text-align: center; margin-top: 40px; padding-top: 16px; border-top: 1px solid #e2e8f0; font-size: 9px; color: #94a3b8; }
        img { max-height: 64px !important; width: auto !important; display: block; margin: 0 auto 8px auto; }
        .print-only { display: block !important; }
        .no-print { display: none !important; }
    </style>
</head>
<body>
    <h1>Expense Statement</h1>
    <p class="subtitle">Euro Taxi Management System &mdash; ${period}</p>
    ${content}
    <div class="footer">
        <p>Authenticated Expense Summary &mdash; Generated: ${new Date().toLocaleString()}</p>
    </div>
</body>
</html>`);
            doc.close();
            
            setTimeout(() => {
                iframe.contentWindow.focus();
                iframe.contentWindow.print();
                setTimeout(() => { document.body.removeChild(iframe); }, 1000);
            }, 300);
        }

        function renderExpensesReport(data) {
            let breakdown = {
                maintenanceTotal: 0,
                maintenanceItems: [],
                officeTotal: 0,
                officeItems: []
            };

            data.forEach(item => {
                const amount = parseFloat(item.amount) || 0;
                const description = item.description || 'No Description';
                const date = (item.date || '').split(' ')[0];

                if (item.type === 'maintenance') {
                    breakdown.maintenanceTotal += amount;
                    breakdown.maintenanceItems.push({ description, amount, date });
                } else if (item.type === 'expense') {
                    breakdown.officeTotal += amount;
                    breakdown.officeItems.push({ description, amount, date });
                }
            });

            // Summarize items for Yearly and Monthly views
            if (window.currentExpensesPeriod === 'year' || window.currentExpensesPeriod === 'month') {
                const summarizeItems = (items) => {
                    const map = {};
                    
                    let periodLabel = '';
                    if (window.currentExpensesPeriod === 'year') {
                        periodLabel = new Date().getFullYear() + ' SUMMARY';
                    } else if (window.currentExpensesPeriod === 'month') {
                        const date = new Date();
                        periodLabel = date.toLocaleString('default', { month: 'long', year: 'numeric' }).toUpperCase() + ' SUMMARY';
                    }

                    items.forEach(item => {
                        if (!map[item.description]) {
                            map[item.description] = { 
                                description: item.description, 
                                amount: 0, 
                                date: periodLabel
                            };
                        }
                        map[item.description].amount += item.amount;
                    });
                    return Object.values(map).sort((a, b) => b.amount - a.amount);
                };
                
                breakdown.maintenanceItems = summarizeItems(breakdown.maintenanceItems);
                breakdown.officeItems = summarizeItems(breakdown.officeItems);
            }

            const totalExpenses = breakdown.maintenanceTotal + breakdown.officeTotal;
            const fmt = (num) => '₱' + num.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});

            const safeSet = (id, val) => {
                const el = document.getElementById(id);
                if (el) el.textContent = val;
            };

            safeSet('expensesTotalValue', fmt(totalExpenses));
            safeSet('expensesTotalValuePrint', fmt(totalExpenses));
            safeSet('expensesMaintenanceTotal', fmt(breakdown.maintenanceTotal));
            safeSet('expensesMaintenanceTotalPrint', fmt(breakdown.maintenanceTotal));
            safeSet('expensesOfficeTotal', fmt(breakdown.officeTotal));
            safeSet('expensesOfficeTotalPrint', fmt(breakdown.officeTotal));
            safeSet('expensesTimestamp', new Date().toLocaleString());

            const renderList = (id, items) => {
                const el = document.getElementById(id);
                if (!el) return;
                
                if (items.length > 0) {
                    el.innerHTML = `
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-gray-50 border-b border-gray-200 text-[9px] uppercase tracking-widest text-gray-500">
                                    <th class="px-6 py-2 font-black w-1/4 border-r border-gray-200">Date</th>
                                    <th class="px-6 py-2 font-black w-1/2 border-r border-gray-200">Description</th>
                                    <th class="px-6 py-2 font-black text-right w-1/4">Amount</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                ${items.map(item => `
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="px-6 py-2.5 text-[10px] text-gray-500 font-bold uppercase whitespace-nowrap border-r border-gray-100">${item.date}</td>
                                        <td class="px-6 py-2.5 text-[10px] font-black text-gray-800 tracking-tight border-r border-gray-100">${item.description}</td>
                                        <td class="px-6 py-2.5 text-[11px] font-black text-red-600 text-right whitespace-nowrap">${fmt(item.amount)}</td>
                                    </tr>
                                `).join('')}
                            </tbody>
                        </table>
                    `;
                } else {
                    el.innerHTML = `
                        <div class="py-6 w-full flex flex-col items-center justify-center text-gray-400">
                            <span class="text-[10px] font-bold uppercase tracking-widest bg-gray-50 px-4 py-1.5 rounded-full border border-gray-100">No records found for this period</span>
                        </div>
                    `;
                }
            };

            renderList('expensesMaintenanceList', breakdown.maintenanceItems);
            renderList('expensesOfficeList', breakdown.officeItems);
        }

        
        function filterIncomeByPeriod(data, period) {
            // Get local date in YYYY-MM-DD format
            const today = new Date();
            today.setHours(0, 0, 0, 0); // Set to local midnight
            
            const year = today.getFullYear();
            const month = String(today.getMonth() + 1).padStart(2, '0');
            const day = String(today.getDate()).padStart(2, '0');
            const todayStr = `${year}-${month}-${day}`;
            
            switch(period) {
                case 'today':
                    return data.filter(item => {
                        const itemDateStr = (item.date || '').split(' ')[0];
                        return itemDateStr === todayStr;
                    });
                    
                case 'week':
                    const weekStart = new Date(today);
                    weekStart.setDate(today.getDate() - today.getDay());
                    const weekEnd = new Date(weekStart);
                    weekEnd.setDate(weekStart.getDate() + 6);
                    weekEnd.setHours(23, 59, 59, 999);
                    
                    return data.filter(item => {
                        const itemDateStr = (item.date || '').split(' ')[0];
                        const itemDate = new Date(itemDateStr + 'T00:00:00');
                        return itemDate >= weekStart && itemDate <= weekEnd;
                    });
                    
                case 'month':
                    const monthStart = new Date(today.getFullYear(), today.getMonth(), 1);
                    const monthEnd = new Date(today.getFullYear(), today.getMonth() + 1, 0);
                    monthEnd.setHours(23, 59, 59, 999);
                    
                    return data.filter(item => {
                        const itemDateStr = (item.date || '').split(' ')[0];
                        const itemDate = new Date(itemDateStr + 'T00:00:00');
                        return itemDate >= monthStart && itemDate <= monthEnd;
                    });
                    
                case 'year':
                    const yearStart = new Date(today.getFullYear(), 0, 1);
                    const yearEnd = new Date(today.getFullYear(), 11, 31);
                    yearEnd.setHours(23, 59, 59, 999);
                    
                    return data.filter(item => {
                        const itemDateStr = (item.date || '').split(' ')[0];
                        const itemDate = new Date(itemDateStr + 'T00:00:00');
                        return itemDate >= yearStart && itemDate <= yearEnd;
                    });
                    
                default:
                    return data;
            }
        }
        
        function setIncomePeriod(period) {
            window.currentIncomePeriod = period;
            
            // Update labels
            const periodLabels = {
                'today': 'Period: TODAY',
                'week': 'Period: THIS WEEK',
                'month': 'Period: THIS MONTH',
                'year': 'Period: THIS YEAR'
            };
            const labelText = periodLabels[period] || 'Period: Custom';
            const labelElPrint = document.getElementById('reportPeriodLabelPrint');
            if (labelElPrint) labelElPrint.textContent = labelText;

            // Update button styles
            document.querySelectorAll('[id^="btn-"][id$="-income"]').forEach(btn => {
                btn.classList.remove('bg-white', 'text-green-700');
                btn.classList.add('text-white/70', 'hover:text-white', 'hover:bg-white/10');
            });
            
            const activeBtn = document.getElementById('btn-' + period + '-income');
            if (activeBtn) {
                activeBtn.classList.remove('text-white/70', 'hover:text-white', 'hover:bg-white/10');
                activeBtn.classList.add('bg-white', 'text-green-700');
            }
            
            // Re-apply filters directly
            const filtered = filterIncomeByPeriod(window.originalIncomeData || [], period);
            updateIncomeSummary(filtered);
        }
        
        function showIncomeError(message, debugInfo = null) {
            const grid = document.getElementById('incomeGrid');
            const debugHtml = debugInfo ? `
                <div class="mt-4 p-3 bg-gray-100 rounded-lg text-xs">
                    <h4 class="font-bold text-gray-700 mb-2">Debug Information:</h4>
                    <pre class="text-gray-600 whitespace-pre-wrap">${JSON.stringify(debugInfo, null, 2)}</pre>
                </div>
            ` : '';
            
            grid.innerHTML = `
                <div class="col-span-full text-center py-20">
                    <div class="inline-flex flex-col items-center">
                        <div class="p-4 bg-red-100 rounded-full mb-4">
                            <i data-lucide="alert-circle" class="w-8 h-8 text-red-600"></i>
                        </div>
                        <span class="text-xl text-gray-600 font-semibold mb-2">Error Loading Income Data</span>
                        <p class="text-sm text-gray-400 mb-4">${message}</p>
                        <div class="flex gap-2">
                            <button onclick="loadIncomeData()" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                                <i data-lucide="refresh-cw" class="w-4 h-4 inline mr-2"></i>
                                Retry
                            </button>
                            <button onclick="testIncomeAPI()" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                                <i data-lucide="bug" class="w-4 h-4 inline mr-2"></i>
                                Test API
                            </button>
                        </div>
                        ${debugHtml}
                    </div>
                </div>
            `;
            
            // Re-initialize Lucide icons
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        }
        
        function testIncomeAPI() {
            const grid = document.getElementById('incomeGrid');
            grid.innerHTML = `
                <div class="col-span-full text-center py-20">
                    <div class="inline-flex flex-col items-center">
                        <div class="p-4 bg-blue-100 rounded-full mb-4">
                            <i data-lucide="bug" class="w-8 h-8 text-blue-600"></i>
                        </div>
                        <span class="text-xl text-gray-600 font-semibold mb-2">Testing API Connection</span>
                        <p class="text-sm text-gray-400 mb-4">Checking API endpoint...</p>
                        <div class="w-64 bg-gray-200 rounded-full h-2 mb-4">
                            <div class="bg-blue-600 h-2 rounded-full animate-pulse" style="width: 60%"></div>
                        </div>
                    </div>
                </div>
            `;
            
            // Test the API endpoint
            fetch('/api/net-income-details')
                .then(response => {
                    const contentType = response.headers.get('content-type');
                    if (contentType && contentType.includes('text/html')) {
                        return response.text().then(text => {
                            throw new Error('API returned HTML instead of JSON. This usually means a Laravel error occurred. Response: ' + text.substring(0, 200) + '...');
                        });
                    }
                    
                    return response.text();
                })
                .then(text => {
                    if (text.trim().startsWith('<')) {
                        throw new Error('API returned HTML instead of JSON. Response starts with: ' + text.substring(0, 100) + '...');
                    }
                    
                    try {
                        const data = JSON.parse(text);
                        showIncomeError('API Test Complete - Check Console for Details', {
                            response_status: 'success',
                            data_keys: Object.keys(data),
                            data: data
                        });
                    } catch (parseError) {
                        showIncomeError('API Test Complete - JSON Parse Error', {
                            response_status: 'parse_error',
                            raw_response: text.substring(0, 500) + (text.length > 500 ? '...' : ''),
                            parse_error: parseError.message
                        });
                    }
                })
                .catch(error => {
                    showIncomeError('API Test Complete - Fetch Error', {
                        response_status: 'fetch_error',
                        error: error.message,
                        stack: error.stack
                    });
                });
        }

        // Daily Boundary Collection Modal Functions
        function showDailyBoundaryModal() {
            document.getElementById('dailyBoundaryModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
            
            // Set default date to today if not set
            const dateInput = document.getElementById('boundaryDateFilter');
            if (dateInput && !dateInput.value) {
                dateInput.value = new Date().toISOString().split('T')[0];
            }
            
            loadBoundaryCollections();
        }
        
        function hideDailyBoundaryModal() {
            document.getElementById('dailyBoundaryModal').classList.add('hidden');
            document.body.style.overflow = 'auto';
        }
        
        function loadBoundaryCollections() {
            const date = document.getElementById('boundaryDateFilter').value;
            const url = `/api/daily-boundary-collections${date ? '?date=' + date : ''}`;
            
            fetch(url)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        displayBoundaryCollections(data);
                    } else {
                        showBoundaryError(data.message);
                    }
                })
                .catch(error => {
                    showBoundaryError('Error loading boundary collections. Please try again.');
                });
        }
        
        function displayBoundaryCollections(data) {
            const grid = document.getElementById('boundaryGrid');
            const collections = data.collections || [];
            const stats = data.stats || {};
            
            // Update summary stats with new logic (amounts for Yesterday and Monthly)
            document.getElementById('totalBoundaryCount').textContent = stats.total_today || 0;
            document.getElementById('uniqueUnitsCount').textContent = '₱' + (stats.amount_yesterday || 0).toLocaleString();
            document.getElementById('uniqueDriversCount').textContent = '₱' + (stats.amount_monthly || 0).toLocaleString();
            document.getElementById('totalBoundaryAmount').textContent = '₱' + (stats.total_yearly_amount || 0).toLocaleString();
            
            // Store original data for filtering and sync with date input
            window.originalBoundaryData = collections;
            
            const dateInput = document.getElementById('boundaryDateFilter');
            if (dateInput && stats.filter_date) {
                dateInput.value = stats.filter_date;
            }

            window.lastFetchedBoundaryDate = stats.filter_date;
            
            // Re-apply current search filter starting from the new background data
            filterBoundaryCollections();
            
            // Re-initialize Lucide icons
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        }
        
        function renderBoundaryCollections(collections) {
            const grid = document.getElementById('boundaryGrid');
            
            if (collections.length === 0) {
                grid.innerHTML = `
                    <div class="col-span-full text-center py-20">
                        <div class="inline-flex flex-col items-center">
                            <div class="p-4 bg-gray-100 rounded-full mb-4">
                                <i data-lucide="calendar" class="w-8 h-8 text-gray-400"></i>
                            </div>
                            <span class="text-xl text-gray-600 font-semibold mb-2">No boundary collections found</span>
                            <p class="text-sm text-gray-400">Try adjusting your search or date filter</p>
                        </div>
                    </div>
                `;
                return;
            }
            
            grid.innerHTML = collections.map(collection => `
                <div class="bg-white rounded-xl shadow-md hover:shadow-lg transition-all duration-300 overflow-hidden border-l-4 border-green-500 hover:scale-102">
                    <div class="p-4">
                        <!-- Header -->
                        <div class="flex items-start justify-between mb-3">
                            <div class="flex items-center gap-3">
                                <div class="p-2 bg-green-100 rounded-lg">
                                    <i data-lucide="car" class="w-4 h-4 text-green-600"></i>
                                </div>
                                <div>
                                    <h4 class="text-lg font-bold text-gray-900">${collection.plate_number}</h4>
                                    <span class="text-xs text-gray-500">${collection.plate_number || 'N/A'}</span>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="text-lg font-bold text-green-600">₱${collection.boundary_amount.toLocaleString()}</div>
                                <div class="text-xs text-gray-500">${collection.date}</div>
                            </div>
                        </div>
                        
                        <!-- Driver Information -->
                        <div class="bg-gray-50 rounded-lg p-3 mb-3">
                            <div class="flex items-center gap-2 mb-2">
                                <i data-lucide="user" class="w-4 h-4 text-gray-600"></i>
                                <span class="text-sm font-medium text-gray-900">Driver: ${collection.driver_name || 'N/A'}</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <i data-lucide="clock" class="w-4 h-4 text-gray-600"></i>
                                <span class="text-xs text-gray-600">Time: ${collection.time || 'N/A'}</span>
                            </div>
                        </div>
                        
                        <!-- Collection Details -->
                        <div class="flex items-center justify-between text-xs text-gray-500">
                            <span class="flex items-center gap-1">
                                <i data-lucide="map-pin" class="w-3 h-3"></i>
                                ${collection.location || 'Main Office'}
                            </span>
                            <span class="flex items-center gap-1">
                                <i data-lucide="check-circle" class="w-3 h-3"></i>
                                Verified
                            </span>
                        </div>
                    </div>
                </div>
            `).join('');
        }
        
        function filterBoundaryCollections() {
            const searchTerm = document.getElementById('boundarySearchInput').value.toLowerCase();
            const dateFilter = document.getElementById('boundaryDateFilter').value;
            
            // Check if we need to re-fetch (if date changed)
            if (window.lastFetchedBoundaryDate !== dateFilter) {
                window.lastFetchedBoundaryDate = dateFilter;
                loadBoundaryCollections();
                return;
            }

            let filteredCollections = window.originalBoundaryData || [];
            
            // Apply search filter
            if (searchTerm) {
                filteredCollections = filteredCollections.filter(collection => {
                    const searchableText = [
                        collection.plate_number || '',
                        collection.plate_number || '',
                        collection.driver_name || '',
                        collection.boundary_amount ? collection.boundary_amount.toString() : '',
                        collection.date || '',
                        collection.time || '',
                        collection.location || ''
                    ].join(' ').toLowerCase();
                    
                    return searchableText.includes(searchTerm);
                });
            }
            
            window.currentFilteredBoundaryData = filteredCollections;
            renderBoundaryCollections(filteredCollections);
        }
        
        function clearBoundarySearch() {
            document.getElementById('boundarySearchInput').value = '';
            document.getElementById('boundaryDateFilter').value = '';
            filterBoundaryCollections();
        }
        
        function showBoundaryError(message) {
            const grid = document.getElementById('boundaryGrid');
            grid.innerHTML = `
                <div class="col-span-full text-center py-20">
                    <div class="inline-flex flex-col items-center">
                        <div class="p-4 bg-red-100 rounded-full mb-4">
                            <i data-lucide="alert-circle" class="w-8 h-8 text-red-600"></i>
                        </div>
                        <span class="text-xl text-gray-600 font-semibold mb-2">Error Loading Collections</span>
                        <p class="text-sm text-gray-400 mb-4">${message}</p>
                        <button onclick="loadBoundaryCollections()" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                            <i data-lucide="refresh-cw" class="w-4 h-4 inline mr-2"></i>
                            Retry
                        </button>
                    </div>
                </div>
            `;
        }

        // Units Modal Functions
        function showUnitsModal() {
            const modal = document.getElementById('unitsModal');
            const grid = document.getElementById('unitsGrid');
            
            if (modal && grid) {
                modal.classList.remove('hidden');
                loadUnitsData();
            }
        }

        function hideUnitsModal() {
            const modal = document.getElementById('unitsModal');
            if (modal) {
                modal.classList.add('hidden');
            }
        }

        function loadUnitsData() {
            const grid = document.getElementById('unitsGrid');
            
            // Show loading state
            grid.innerHTML = `
                <div class="col-span-full text-center py-20">
                    <div class="inline-flex flex-col items-center">
                        <div class="animate-spin rounded-full h-16 w-16 border-4 border-blue-600 border-t-transparent mb-6"></div>
                        <span class="text-xl text-gray-600 font-semibold mb-2">Loading units data...</span>
                        <p class="text-sm text-gray-400">Please wait while we fetch your fleet information</p>
                    </div>
                </div>
            `;
            
            fetch('/api/units-overview')
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        displayUnitsData(data);
                    } else {
                        throw new Error(data.message || 'Unknown error occurred');
                    }
                })
                .catch(error => {
                    console.error('Error loading units:', error);
                    grid.innerHTML = `
                        <div class="col-span-full text-center py-20">
                            <div class="inline-flex flex-col items-center">
                                <div class="p-4 bg-red-100 rounded-full mb-4">
                                    <i data-lucide="alert-circle" class="w-8 h-8 text-red-600"></i>
                                </div>
                                <span class="text-xl text-red-600 font-semibold mb-2">Error Loading Units</span>
                                <p class="text-sm text-gray-400 mb-4">${error.message}</p>
                                <button onclick="loadUnitsData()" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                                    <i data-lucide="refresh-cw" class="w-4 h-4 mr-2"></i>
                                    Try Again
                                </button>
                            </div>
                        </div>
                    `;
                    
                    // Re-initialize Lucide icons for error state
                    if (typeof lucide !== 'undefined') {
                        lucide.createIcons();
                    }
                });
        }

        function renderUnits(units, statusColors, statusIcons, statusGradients) {
            const grid = document.getElementById('unitsGrid');
            
            if (units.length === 0) {
                grid.innerHTML = `
                    <div class="col-span-full text-center py-20">
                        <div class="inline-flex flex-col items-center">
                            <div class="p-4 bg-gray-100 rounded-full mb-4">
                                <i data-lucide="search" class="w-8 h-8 text-gray-400"></i>
                            </div>
                            <span class="text-xl text-gray-600 font-semibold mb-2">No units found</span>
                            <p class="text-sm text-gray-400">Try adjusting your search or filters</p>
                        </div>
                    </div>
                `;
                return;
            }
            
            grid.innerHTML = units.map(unit => `
                <div class="bg-white rounded-lg shadow border-t-2 ${statusColors[unit.status] || 'border-gray-200'} hover:shadow-md transition-all duration-300 relative group overflow-hidden cursor-pointer min-h-[110px] flex flex-col" onclick="window.location.href='/units?id=${unit.id}'">
                    
                    <!-- Hover Driver Overlay -->
                    <div class="absolute inset-0 bg-slate-900/95 opacity-0 group-hover:opacity-100 transition-all duration-300 flex flex-col items-center justify-center z-10 pointer-events-none p-2">
                        <p class="text-white/70 text-[9px] font-black uppercase tracking-[0.2em] mb-1">Assigned Drivers</p>
                        ${(unit.driver1_name || unit.driver2_name) ? `
                            ${unit.driver1_name ? `
                            <div class="flex items-center gap-2 bg-white/10 rounded-md px-2 py-1 w-[95%] border border-white/10 shadow-lg mb-1">
                                <div class="w-5 h-5 rounded-full bg-blue-500 flex items-center justify-center flex-shrink-0 shadow-inner">
                                    <span class="text-white text-[8px] font-black">P1</span>
                                </div>
                                <div class="min-w-0">
                                    <p class="text-[6px] text-blue-300 font-bold uppercase tracking-widest leading-none mb-0.5">Primary 1</p>
                                    <p class="text-white text-[10px] font-black leading-tight truncate tracking-wide">${unit.driver1_name}</p>
                                </div>
                            </div>
                            ` : ''}
                            ${unit.driver2_name ? `
                            <div class="flex items-center gap-2 bg-white/10 rounded-md px-2 py-1 w-[95%] border border-white/10 shadow-lg">
                                <div class="w-5 h-5 rounded-full bg-purple-500 flex items-center justify-center flex-shrink-0 shadow-inner">
                                    <span class="text-white text-[8px] font-black">P2</span>
                                </div>
                                <div class="min-w-0">
                                    <p class="text-[6px] text-purple-300 font-bold uppercase tracking-widest leading-none mb-0.5">Primary 2</p>
                                    <p class="text-white text-[10px] font-black leading-tight truncate tracking-wide">${unit.driver2_name}</p>
                                </div>
                            </div>
                            ` : ''}
                        ` : `
                            <div class="flex flex-col items-center justify-center h-full text-center">
                                <div class="w-6 h-6 rounded-full bg-red-500/20 flex items-center justify-center mb-1">
                                    <i data-lucide="user-x" class="w-3 h-3 text-red-400"></i>
                                </div>
                                <p class="text-red-300 text-[9px] font-bold uppercase tracking-wider">No Available Drivers</p>
                            </div>
                        `}
                    </div>

                    <div class="p-3 relative z-0 flex-1 flex flex-col justify-between">
                        <!-- Summary Header -->
                        <div class="flex items-center justify-between gap-2 mb-2">
                            <div class="flex items-center gap-1.5 min-w-0">
                                <i data-lucide="car" class="w-3.5 h-3.5 text-gray-400"></i>
                                <h4 class="text-sm font-bold text-gray-900 truncate">${unit.plate_number}</h4>
                            </div>
                            <span class="px-2 py-0.5 text-[9px] font-bold rounded-full ${statusColors[unit.status] || 'bg-gray-100'} uppercase">
                                ${unit.status}
                            </span>
                        </div>
                        
                        <!-- Essential Stats -->
                        <div class="grid grid-cols-2 gap-2 text-center py-1.5 bg-gray-50/80 rounded mb-2">
                            <div>
                                <div class="text-[8px] text-gray-500 uppercase font-bold tracking-tighter">Total Coll.</div>
                                <div class="text-xs font-bold text-green-600">₱${unit.total_boundary ? unit.total_boundary.toLocaleString() : '0'}</div>
                            </div>
                            <div>
                                <div class="text-[8px] text-gray-500 uppercase font-bold tracking-tighter">ROI</div>
                                <div class="text-xs font-bold ${unit.roi_percentage >= 100 ? 'text-blue-600' : 'text-gray-900'}">${unit.roi_percentage.toFixed(1)}%</div>
                            </div>
                        </div>

                        <!-- Mini Footer -->
                        <div class="flex items-center justify-between text-[8px] font-bold text-gray-400 uppercase tracking-tighter">
                            <span>ID: ${unit.plate_number || 'N/A'}</span>
                            <span class="${unit.today_boundary > 0 ? 'text-blue-500' : ''}">
                                ${unit.today_boundary > 0 ? `+₱${unit.today_boundary.toLocaleString()}` : 'No Daily'}
                            </span>
                        </div>
                    </div>
                </div>
            `).join('');
            
            // Re-initialize Lucide icons
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        }

        function displayUnitsData(data) {
            const grid = document.getElementById('unitsGrid');
            const units = data.units || [];
            const stats = data.stats || {};
            
            // Store original units data for filtering
            window.originalUnitsData = units;
            window.currentFilteredUnits = units;
            
            // Update summary stats with new logic (Vacant vs Active focus)
            document.getElementById('totalUnitsCount').textContent = stats.total_units || 0;
            document.getElementById('activeUnitsCount').textContent = stats.vacant_units || 0;
            document.getElementById('roiUnitsCount').textContent = stats.active_units || 0;
            document.getElementById('avgRoiCount').textContent = stats.avg_roi ? stats.avg_roi.toFixed(1) + '%' : '0%';
            
            // Remove any existing database data indicators to save space
            const indicator = grid.parentNode.querySelector('.data-source-indicator');
            if (indicator) indicator.remove();
            
            const statusColors = {
                'active': 'bg-green-100 text-green-800 border-green-200',
                'maintenance': 'bg-red-100 text-red-800 border-red-200',
                'coding': 'bg-yellow-100 text-yellow-800 border-yellow-200',
                'retired': 'bg-gray-100 text-gray-800 border-gray-200'
            };
            
            const statusIcons = {
                'active': '<i data-lucide="check-circle" class="w-3 h-3"></i>',
                'maintenance': '<i data-lucide="wrench" class="w-3 h-3"></i>',
                'coding': '<i data-lucide="code" class="w-3 h-3"></i>',
                'retired': '<i data-lucide="x-circle" class="w-3 h-3"></i>'
            };
            
            const statusGradients = {
                'active': 'from-green-500 to-emerald-600',
                'maintenance': 'from-red-500 to-rose-600', 
                'coding': 'from-yellow-500 to-amber-600',
                'retired': 'from-gray-500 to-slate-600'
            };
            
            renderUnits(units, statusColors, statusIcons, statusGradients);
            
            // Re-initialize Lucide icons
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        }
        
        // Search and Filter Functions
        window.currentUnitStatusFilter = 'all';

        function setUnitStatusFilter(status) {
            window.currentUnitStatusFilter = status;
            
            // Update UI
            const statusBtns = ['all', 'active', 'maintenance', 'coding'];
            statusBtns.forEach(s => {
                const btn = document.getElementById('btn-' + s + '-units');
                if (btn) {
                    if (s === status) {
                        btn.classList.remove('text-white/70', 'hover:text-white', 'hover:bg-white/10');
                        btn.classList.add('bg-white', 'text-blue-700');
                    } else {
                        btn.classList.add('text-white/70', 'hover:text-white', 'hover:bg-white/10');
                        btn.classList.remove('bg-white', 'text-blue-700');
                    }
                }
            });
            
            filterUnits();
        }

        function filterUnits() {
            const searchTerm = document.getElementById('unitSearchInput').value.toLowerCase();
            const currentStatus = window.currentUnitStatusFilter || 'all';
            
            let filteredUnits = window.originalUnitsData || [];
            
            // Apply status filter
            if (currentStatus !== 'all') {
                filteredUnits = filteredUnits.filter(unit => (unit.status || '').toLowerCase() === currentStatus);
            }
            
            // Apply search filter
            if (searchTerm) {
                filteredUnits = filteredUnits.filter(unit => {
                    const searchableText = [
                        unit.plate_number || '',
                        unit.status || '',
                        unit.driver_name || '',
                        unit.performance_rating || '',
                        unit.roi_percentage >= 100 ? 'excellent profitable' : 
                        unit.roi_percentage >= 75 ? 'good' : 
                        unit.roi_percentage >= 50 ? 'average growing' : 'growing investment',
                        unit.boundary_rate ? unit.boundary_rate.toString() : '',
                        unit.total_boundary ? unit.total_boundary.toString() : '',
                        unit.today_boundary ? unit.today_boundary.toString() : '',
                        unit.purchase_cost ? unit.purchase_cost.toString() : ''
                    ].join(' ').toLowerCase();
                    
                    return searchableText.includes(searchTerm);
                });
            }
            
            window.currentFilteredUnits = filteredUnits;
            
            // Re-render with current filters
            const statusColors = {
                'active': 'bg-green-100 text-green-800 border-green-200',
                'maintenance': 'bg-red-100 text-red-800 border-red-200',
                'coding': 'bg-yellow-100 text-yellow-800 border-yellow-200',
                'retired': 'bg-gray-100 text-gray-800 border-gray-200'
            };
            
            const statusIcons = {
                'active': '<i data-lucide="check-circle" class="w-3 h-3"></i>',
                'maintenance': '<i data-lucide="wrench" class="w-3 h-3"></i>',
                'coding': '<i data-lucide="code" class="w-3 h-3"></i>',
                'retired': '<i data-lucide="x-circle" class="w-3 h-3"></i>'
            };
            
            const statusGradients = {
                'active': 'from-green-500 to-emerald-600',
                'maintenance': 'from-red-500 to-rose-600', 
                'coding': 'from-yellow-500 to-amber-600',
                'retired': 'from-gray-500 to-slate-600'
            };
            
            renderUnits(filteredUnits, statusColors, statusIcons, statusGradients);
        }
        
        function filterByStatus(status) {
            // Update active filter tag
            document.querySelectorAll('.filter-tag').forEach(tag => {
                tag.classList.remove('active', 'bg-white/40');
                if (tag.dataset.status === status) {
                    tag.classList.add('active', 'bg-white/40');
                }
            });
            
            // Apply filter
            filterUnits();
        }
        
        function filterByMonth(month) {
            // Update active filter tag
            document.querySelectorAll('.filter-tag').forEach(tag => {
                tag.classList.remove('active', 'bg-white/40');
                if (tag.dataset.month === month) {
                    tag.classList.add('active', 'bg-white/40');
                }
            });
            
            // Apply filter
            filterUnits();
        }
        
        function clearSearch() {
            document.getElementById('unitSearchInput').value = '';
            setUnitStatusFilter('all');
        }

        window.showFlaggedUnitsModal = function() {
            const modal = document.getElementById('flaggedUnitsModal');
            modal.classList.remove('hidden');
            const container = document.getElementById('flaggedUnitsList');
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            fetch('/api/flagged-units')
                .then(res => res.json())
                .then(data => {
                    if (!data.success || data.units.length === 0) {
                        container.innerHTML = `
                            <div class="text-center py-12 bg-white rounded-2xl border border-gray-100 shadow-sm">
                                <div class="w-16 h-16 bg-green-50 rounded-full flex items-center justify-center mx-auto mb-4 border border-green-100">
                                    <i data-lucide="shield-check" class="w-8 h-8 text-green-600"></i>
                                </div>
                                <h4 class="text-lg font-black text-gray-900 uppercase tracking-tight">All Clear</h4>
                                <p class="text-gray-500 text-xs px-8 mt-1 font-medium">No units are currently flagged or missing. Fleet integrity is secured.</p>
                            </div>
                        `;
                        if (typeof lucide !== 'undefined') lucide.createIcons();
                        return;
                    }

                    let html = '<div class="space-y-3">';
                    data.units.forEach(unit => {
                        const isMissing = (unit.status || '').toLowerCase() === 'missing';
                        const badgeClass = isMissing ? 'bg-red-100 text-red-700 border-red-200' : 'bg-amber-100 text-amber-700 border-amber-200';
                        const icon = isMissing ? 'alert-octagon' : 'eye';
                        const statusText = isMissing ? 'MISSING / STOLEN' : 'SURVEILLANCE';
                        
                        const daysMissing = unit.days_missing || 0;
                        const daysColor = daysMissing > 3 ? 'text-red-600' : 'text-amber-600';

                        const contactDisplay = unit.driver_phone ? 
                            `<a href="tel:${unit.driver_phone}" class="text-blue-600 font-bold hover:underline flex items-center gap-1">
                                <i data-lucide="phone" class="w-3 h-3"></i> ${unit.driver_phone}
                            </a>` : '<span class="text-gray-400">Not recorded</span>';

                        html += `
                            <div class="bg-white p-4 rounded-2xl shadow-sm border border-gray-100 border-l-4 ${isMissing ? 'border-red-600' : 'border-amber-600'} hover:shadow-md transition-all relative overflow-hidden group">
                                ${isMissing ? '<div class="absolute top-0 right-0 p-1 bg-red-600 text-white text-[8px] font-black uppercase tracking-widest px-3">Lockdown</div>' : ''}
                                <div class="flex justify-between items-start">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2 mb-2">
                                            <span class="text-lg font-black text-gray-900 tracking-tighter">${unit.plate_number}</span>
                                            <span class="px-2 py-0.5 rounded text-[9px] font-black border ${badgeClass} flex items-center gap-1 uppercase tracking-widest shadow-sm">
                                                <i data-lucide="${icon}" class="w-3 h-3"></i> ${statusText}
                                            </span>
                                        </div>
                                        <div class="space-y-1.5">
                                            <div class="flex items-center gap-2 text-[10px]">
                                                <span class="text-gray-400 w-24 flex-shrink-0 font-bold uppercase tracking-tight">Current Driver:</span>
                                                <span class="text-gray-800 font-black uppercase tracking-tighter">${unit.driver_name || 'No Driver'}</span>
                                            </div>
                                            <div class="flex items-center gap-2 text-[10px]">
                                                <span class="text-gray-400 w-24 flex-shrink-0 font-bold uppercase tracking-tight">Contact # :</span>
                                                ${contactDisplay}
                                            </div>
                                            <div class="flex items-center gap-2 text-[10px] pt-1.5 mt-1.5 border-t border-gray-100">
                                                <span class="text-gray-400 w-24 flex-shrink-0 font-bold uppercase tracking-tight">Last Return:</span>
                                                <span class="text-gray-600 italic font-black uppercase tracking-tighter">${unit.last_known_driver || 'None'}</span>
                                            </div>
                                            <div class="flex items-center gap-2 text-[10px]">
                                                <span class="text-gray-400 w-24 flex-shrink-0 font-bold uppercase tracking-tight">Return Date:</span>
                                                <span class="text-gray-600 font-bold uppercase">${unit.last_boundary_date || 'No record'}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex flex-col items-center gap-3 flex-shrink-0 ml-4">
                                        <div class="text-center bg-gray-50 p-2 rounded-xl border border-gray-100 min-w-[70px]">
                                            <div class="text-[9px] uppercase font-black text-gray-400 tracking-widest">Time Elapsed</div>
                                            <div class="text-xl ${daysColor} leading-none mt-1 font-black">${daysMissing}</div>
                                            <div class="text-[9px] font-bold text-gray-400 uppercase tracking-tighter">day(s)</div>
                                        </div>
                                        ${isMissing ? `
                                        <form method="POST" action="/units/${unit.id}/recover" class="m-0 w-full" onsubmit="return confirm('Confirm RECOVERY of Unit ${unit.plate_number}? This will restore its active status and clear security alerts.');">
                                            <input type="hidden" name="_token" value="${csrfToken}">
                                            <button type="submit" class="w-full py-2 bg-green-600 text-white hover:bg-green-700 rounded-xl transition-all shadow-lg shadow-green-200 flex items-center justify-center gap-2 group/btn" title="Mark as Recovered">
                                                <i data-lucide="check-circle" class="w-4 h-4 group-hover/btn:scale-110 transition-transform"></i>
                                                <span class="text-[10px] font-black uppercase tracking-widest">Recover</span>
                                            </button>
                                        </form>
                                        ` : `
                                        <a href="/units?id=${unit.id}" class="w-full py-2 bg-gray-100 text-gray-600 hover:bg-gray-200 rounded-xl transition-all flex items-center justify-center gap-2 border border-gray-200">
                                            <i data-lucide="edit-3" class="w-4 h-4"></i>
                                            <span class="text-[10px] font-black uppercase tracking-widest text-center">Manage</span>
                                        </a>
                                        `}
                                    </div>
                                </div>
                            </div>
                        `;
                    });
                    html += '</div>';
                    container.innerHTML = html;
                    if (typeof lucide !== 'undefined') lucide.createIcons();
                })
                .catch(err => {
                    container.innerHTML = `
                        <div class="text-center py-12 bg-red-50 rounded-2xl border border-red-100">
                            <i data-lucide="alert-circle" class="w-12 h-12 text-red-500 mx-auto mb-3"></i>
                            <p class="text-red-700 font-black uppercase tracking-widest text-xs">Failed to load security flags.</p>
                            <button onclick="showFlaggedUnitsModal()" class="mt-4 px-4 py-2 bg-red-600 text-white rounded-lg text-xs font-bold uppercase tracking-widest">Retry</button>
                        </div>
                    `;
                    if (typeof lucide !== 'undefined') lucide.createIcons();
                });
        }

        // Intersection Observer for scroll-triggered wave animation
        function initWaveObserver() {
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('in-view');
                    } else {
                        // Remove class when out of view to reset animation
                        entry.target.classList.remove('in-view');
                    }
                });
            }, { threshold: 0.3 });

            document.querySelectorAll('.card-hover').forEach(card => {
                observer.observe(card);
            });
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initWaveObserver);
        } else {
            initWaveObserver();
        }
    </script>
@endpush
