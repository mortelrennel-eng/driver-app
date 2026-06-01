@extends('layouts.app')

@section('title', 'Euro Taxi System | Professional Fleet Management Dashboard')
@section('page-heading', 'Euro Taxi System')
@section('page-subheading', 'Professional taxi fleet management and real-time tracking solutions')

    <style>
        @media print {
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
                padding: 0 !important;
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
    </style>
@section('content')
    <!-- Stats Grid -->
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">

        {{-- Total Units --}}
        <div onclick="showUnitsModal()" class="card-hover cursor-pointer group relative overflow-hidden rounded-2xl shadow-lg transition-all duration-300 hover:-translate-y-1 hover:shadow-xl" style="background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 60%, #3b82f6 100%);">
            <div class="absolute inset-0 opacity-10" style="background: radial-gradient(circle at 80% 20%, #fff 0%, transparent 60%);"></div>
            <div class="relative p-5 flex items-center justify-between">
                <div>
                    <p class="text-blue-200 text-xs font-semibold uppercase tracking-widest mb-1">Total Units</p>
                    <p class="text-white text-3xl font-black leading-none mb-1" data-stat="active_units">{{ $stats['active_units'] }}</p>
                    <p class="text-blue-200 text-xs font-medium"><span class="text-emerald-300 font-bold">{{ $stats['roi_units'] }}</span> ROI Achieved</p>
                </div>
                <div class="p-3 rounded-2xl" style="background: rgba(255,255,255,0.15); backdrop-filter: blur(8px);">
                    <i data-lucide="car" class="w-7 h-7 text-white"></i>
                </div>
            </div>
        </div>

        {{-- Daily Boundary Collection --}}
        <div onclick="showDailyBoundaryModal()" class="card-hover cursor-pointer group relative overflow-hidden rounded-2xl shadow-lg transition-all duration-300 hover:-translate-y-1 hover:shadow-xl" style="background: linear-gradient(135deg, #065f46 0%, #059669 60%, #34d399 100%);">
            <div class="absolute inset-0 opacity-10" style="background: radial-gradient(circle at 80% 20%, #fff 0%, transparent 60%);"></div>
            <div class="relative p-5 flex items-center justify-between">
                <div>
                    <p class="text-emerald-200 text-xs font-semibold uppercase tracking-widest mb-1">Daily Boundary Collection</p>
                    <p class="text-white text-2xl font-black leading-none mb-1" data-stat="today_boundary">{{ formatCurrency($stats['today_boundary']) }}</p>
                    <p class="text-emerald-200 text-xs font-medium">Target: <span class="text-white font-bold" data-stat="daily_target">{{ formatCurrency($stats['daily_target']) }}</span></p>
                </div>
                <div class="p-3 rounded-2xl" style="background: rgba(255,255,255,0.15); backdrop-filter: blur(8px);">
                    <i data-lucide="dollar-sign" class="w-7 h-7 text-white"></i>
                </div>
            </div>
        </div>

        {{-- Net Income Today --}}
        <div onclick="showNetIncomeModal()" class="card-hover cursor-pointer group relative overflow-hidden rounded-2xl shadow-lg transition-all duration-300 hover:-translate-y-1 hover:shadow-xl" style="background: linear-gradient(135deg, #14532d 0%, #16a34a 60%, #4ade80 100%);">
            <div class="absolute inset-0 opacity-10" style="background: radial-gradient(circle at 80% 20%, #fff 0%, transparent 60%);"></div>
            <div class="relative p-5 flex items-center justify-between">
                <div class="flex-1">
                    <p class="text-green-200 text-[10px] font-black uppercase tracking-widest mb-1" id="netIncomeLabel">Net Income Today</p>
                    <p class="text-white text-2xl font-black leading-none mb-2" data-stat="net_income">{{ formatCurrency($stats['net_income']) }}</p>
                    
                    <div class="flex gap-1.5 pt-1">
                        <button onclick="event.stopPropagation(); updateNetIncomeStat('today', this)" 
                            class="net-income-btn px-2.5 py-1 text-[8px] font-black uppercase tracking-widest bg-white text-green-900 rounded-lg transition-all active:scale-95 shadow-lg shadow-black/10">Today</button>
                        <button onclick="event.stopPropagation(); updateNetIncomeStat('month', this)" 
                            class="net-income-btn px-2.5 py-1 text-[8px] font-black uppercase tracking-widest bg-white/20 text-white rounded-lg hover:bg-white/30 transition-all active:scale-95">Month</button>
                        <button onclick="event.stopPropagation(); updateNetIncomeStat('year', this)" 
                            class="net-income-btn px-2.5 py-1 text-[8px] font-black uppercase tracking-widest bg-white/20 text-white rounded-lg hover:bg-white/30 transition-all active:scale-95">Year</button>
                    </div>
                </div>
                <div class="p-3 rounded-2xl" style="background: rgba(255,255,255,0.15); backdrop-filter: blur(8px);">
                    <i data-lucide="trending-up" class="w-7 h-7 text-white"></i>
                </div>
            </div>
        </div>

        {{-- Units Under Maintenance --}}
        <div onclick="showMaintenanceUnitsModal()" class="card-hover cursor-pointer group relative overflow-hidden rounded-2xl shadow-lg transition-all duration-300 hover:-translate-y-1 hover:shadow-xl" style="background: linear-gradient(135deg, #7c2d12 0%, #ea580c 60%, #fb923c 100%);">
            <div class="absolute inset-0 opacity-10" style="background: radial-gradient(circle at 80% 20%, #fff 0%, transparent 60%);"></div>
            <div class="relative p-5 flex items-center justify-between">
                <div>
                    <p class="text-orange-200 text-xs font-semibold uppercase tracking-widest mb-1">Units Under Maintenance</p>
                    <p class="text-white text-3xl font-black leading-none mb-1" data-stat="maintenance_units">{{ $stats['maintenance_units'] }}</p>
                    <p class="text-orange-200 text-xs font-medium" data-stat="maintenance_subtitle">Units ongoing maintenance</p>
                </div>
                <div class="p-3 rounded-2xl" style="background: rgba(255,255,255,0.15); backdrop-filter: blur(8px);">
                    <i data-lucide="wrench" class="w-7 h-7 text-white"></i>
                </div>
            </div>
        </div>

    </div>

    <!-- Quick Stats -->
    <div class="mt-4 grid grid-cols-1 sm:grid-cols-3 gap-4">

        {{-- Active Drivers --}}
        <div onclick="showActiveDriversModal()" class="card-hover cursor-pointer group relative overflow-hidden rounded-2xl shadow-lg transition-all duration-300 hover:-translate-y-1 hover:shadow-xl" style="background: linear-gradient(135deg, #1e1b4b 0%, #4338ca 60%, #818cf8 100%);">
            <div class="absolute inset-0 opacity-10" style="background: radial-gradient(circle at 80% 20%, #fff 0%, transparent 60%);"></div>
            <div class="relative p-5 flex items-center justify-between">
                <div>
                    <p class="text-indigo-200 text-xs font-semibold uppercase tracking-widest mb-1">Active Drivers</p>
                    <p class="text-white text-3xl font-black leading-none" data-stat="active_drivers">{{ $stats['active_drivers'] }}</p>
                </div>
                <div class="p-3 rounded-2xl" style="background: rgba(255,255,255,0.15); backdrop-filter: blur(8px);">
                    <i data-lucide="users" class="w-7 h-7 text-white"></i>
                </div>
            </div>
        </div>

        {{-- Total Expenses Today --}}
        <div onclick="showExpensesModal()" class="card-hover cursor-pointer group relative overflow-hidden rounded-2xl shadow-lg transition-all duration-300 hover:-translate-y-1 hover:shadow-xl" style="background: linear-gradient(135deg, #7f1d1d 0%, #dc2626 60%, #f87171 100%);">
            <div class="absolute inset-0 opacity-10" style="background: radial-gradient(circle at 80% 20%, #fff 0%, transparent 60%);"></div>
            <div class="relative p-5 flex items-center justify-between">
                <div>
                    <p class="text-red-200 text-xs font-semibold uppercase tracking-widest mb-1">Total Expenses Today</p>
                    <p class="text-white text-2xl font-black leading-none" data-stat="total_expenses_today">{{ formatCurrency($stats['total_expenses_today']) }}</p>
                </div>
                <div class="p-3 rounded-2xl" style="background: rgba(255,255,255,0.15); backdrop-filter: blur(8px);">
                    <i data-lucide="trending-down" class="w-7 h-7 text-white"></i>
                </div>
            </div>
        </div>

        {{-- Coding Units Today --}}
        <div onclick="showCodingUnitsModal()" class="card-hover cursor-pointer group relative overflow-hidden rounded-2xl shadow-lg transition-all duration-300 hover:-translate-y-1 hover:shadow-xl" style="background: linear-gradient(135deg, #4c1d95 0%, #7c3aed 60%, #a78bfa 100%);">
            <div class="absolute inset-0 opacity-10" style="background: radial-gradient(circle at 80% 20%, #fff 0%, transparent 60%);"></div>
            <div class="relative p-5 flex items-center justify-between">
                <div>
                    <p class="text-violet-200 text-xs font-semibold uppercase tracking-widest mb-1">Coding Units Today</p>
                    <p class="text-white text-3xl font-black leading-none mb-1" data-stat="coding_units">{{ $stats['coding_units'] }}</p>
                    <p class="text-violet-200 text-[10px] font-bold uppercase tracking-tight">{{ now()->timezone('Asia/Manila')->format('l') }}</p>
                </div>
                <div class="p-3 rounded-2xl" style="background: rgba(255,255,255,0.15); backdrop-filter: blur(8px);">
                    <i data-lucide="calendar" class="w-7 h-7 text-white"></i>
                </div>
            </div>
        </div>

    </div>

    <!-- Unit Performance (Full Width) -->
    <div class="mt-4 bg-white rounded-lg shadow-lg border border-gray-100 overflow-hidden">
        <div class="p-4 border-b bg-gray-50/50 flex justify-between items-center">
            <div class="flex items-center gap-2">
                <div class="p-1.5 bg-blue-100 rounded-lg">
                    <i data-lucide="bar-chart-3" class="w-4 h-4 text-blue-600"></i>
                </div>
                <h3 class="text-base font-bold text-gray-900 uppercase tracking-tight">Unit Performance</h3>
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
                <canvas id="unitStatusDistributionChart" width="400" height="200"></canvas>
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
                    <input 
                        type="text" 
                        id="maintenanceSearchInput"
                        placeholder="Search by unit number, plate, or maintenance type..."
                        class="w-full pl-10 pr-4 py-2 bg-white/20 backdrop-blur-sm border border-white/30 rounded-lg text-white placeholder-white/70 focus:outline-none focus:ring-2 focus:ring-white/50 focus:border-transparent transition-all duration-200 text-sm"
                        onkeyup="filterMaintenanceUnits()"
                    >
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
                                <i data-lucide="tool" class="w-4 h-4 text-amber-600"></i>
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
                    <input 
                        type="text" 
                        id="driversSearchInput"
                        placeholder="Search by name, license, or contact..."
                        class="w-full pl-10 pr-4 py-2 bg-white/20 backdrop-blur-sm border border-white/30 rounded-lg text-white placeholder-white/70 focus:outline-none focus:ring-2 focus:ring-white/50 focus:border-transparent transition-all duration-200 text-sm"
                        onkeyup="filterActiveDrivers()"
                    >
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                        <button onclick="clearDriversSearch()" class="text-white/60 hover:text-white transition-colors">
                            <i data-lucide="x-circle" class="w-4 h-4"></i>
                        </button>
                    </div>
                </div>
                <button 
                    onclick="toggleDriversSort()" 
                    id="driversSortBtn"
                    class="px-3 py-2 bg-white/20 backdrop-blur-sm border border-white/30 rounded-lg text-white hover:bg-white/30 transition-all duration-200 text-sm flex items-center gap-2 min-w-[100px] justify-center"
                >
                    <i data-lucide="sort-asc" id="driversSortIcon" class="w-4 h-4"></i>
                    <span id="driversSortText">A-Z</span>
                </button>
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
                    <input 
                        type="text" 
                        id="codingSearchInput"
                        placeholder="Search by unit number, plate, or coding status..."
                        class="w-full pl-10 pr-4 py-2 bg-white/20 backdrop-blur-sm border border-white/30 rounded-lg text-white placeholder-white/70 focus:outline-none focus:ring-2 focus:ring-white/50 focus:border-transparent transition-all duration-200 text-sm"
                        oninput="filterCodingUnits()"
                    >
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                        <button onclick="clearCodingSearch()" class="text-white/60 hover:text-white transition-colors">
                            <i data-lucide="x-circle" class="w-4 h-4"></i>
                        </button>
                    </div>
                </div>

                <!-- Coding Period Filters -->
                <div class="flex bg-white/10 backdrop-blur-sm border border-white/30 rounded-lg p-1">
                    <button 
                        id="btn-all-coding" 
                        onclick="setCodingPeriod('all')"
                        class="px-3 py-1.5 text-xs font-medium rounded-md transition-all duration-200 bg-white text-purple-700"
                    >
                        All
                    </button>
                    <button 
                        id="btn-today-coding" 
                        onclick="setCodingPeriod('today')"
                        class="px-3 py-1.5 text-xs font-medium rounded-md transition-all duration-200 text-white/70 hover:text-white hover:bg-white/10"
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
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="bg-white rounded-lg p-3 shadow-sm border border-purple-100 hover:shadow-md transition-shadow">
                        <div class="flex items-center gap-2">
                            <div class="p-1.5 bg-purple-100 rounded">
                                <i data-lucide="code" class="w-4 h-4 text-purple-600"></i>
                            </div>
                            <div>
                                <div class="text-lg font-bold text-purple-600" id="codingUnitsCount">0</div>
                                <div class="text-xs text-gray-600 uppercase tracking-wide font-medium">Coding</div>
                            </div>
                        </div>
                    </div>
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
                <div class="max-w-4xl mx-auto bg-white border border-gray-200 rounded-xl p-6 shadow-sm relative" id="incomeReport">
                    <!-- Report Header (Print Only) -->
                    <div class="text-center mb-10 print-only">
                        <div class="flex flex-col items-center mb-4">
                            <img src="{{ asset('image/logo.png') }}" alt="Euro Taxi Logo" class="h-16 w-auto mb-2">
                        </div>
                        <h4 class="text-4xl font-black uppercase tracking-[0.4em] text-gray-900 mb-2">Financial Report</h4>
                        <div class="text-base text-gray-600 uppercase font-black tracking-widest" id="reportPeriodLabelPrint">Period: TODAY</div>
                        <div class="text-[12px] text-gray-400 mt-3 font-bold tracking-[0.2em]">EURO TAXI MANAGEMENT SYSTEM • OFFICIAL RECORD</div>
                        <div class="border-t-2 border-gray-100 mt-8 pt-2 h-0 border-dashed"></div>
                    </div>
                    
                    <!-- Revenue Section -->
                    <div class="mb-6">
                        <div class="flex justify-between items-center bg-gray-900 text-white px-6 py-3 rounded-t-lg">
                            <span class="text-[11px] uppercase font-black tracking-[0.1em]">Revenue: Total Boundary Collected</span>
                            <span class="text-xl font-black text-emerald-400" id="reportTotalIncome">₱0.00</span>
                        </div>
                        <div class="border-x border-b border-gray-100 rounded-b-lg">
                            <div class="bg-gray-50 px-6 py-1 border-b border-gray-100 flex justify-between text-[9px] font-black text-gray-400 uppercase tracking-widest">
                                <span>Unit / Driver Detail</span>
                                <span>Amount Collected</span>
                            </div>
                            <div id="revenueDetailList" class="divide-y divide-gray-50 min-h-[0px]">
                                <!-- Dynamically populated -->
                            </div>
                        </div>
                    </div>
                    
                    <!-- Operating Expenses Section -->
                    <div class="mb-6">
                        <div class="flex justify-between items-center bg-red-900 text-white px-6 py-3 rounded-t-lg">
                            <span class="text-[11px] uppercase font-black tracking-[0.1em]">Operating Expenses Breakdown</span>
                            <span class="text-xl font-black text-red-300" id="reportTotalExpenses">₱0.00</span>
                        </div>
                        <div class="border-x border-b border-gray-100 rounded-b-lg p-0">
                            <!-- Maintenance Breakdown -->
                            <div class="border-b border-gray-100">
                                <div class="bg-gray-50 px-6 py-1 border-b border-gray-100 flex justify-between text-[9px] font-black text-gray-400 uppercase tracking-widest">
                                    <span>Maintenance & Repairs Itemized</span>
                                    <span id="reportMaintenanceTotal" class="text-orange-600 font-black">Total: ₱0.00</span>
                                </div>
                                <div id="maintenanceDetailList" class="divide-y divide-gray-50 bg-white">
                                    <!-- Dynamically populated -->
                                </div>
                            </div>

                            <!-- Office Breakdown -->
                            <div>
                                <div class="bg-gray-50 px-6 py-1 border-b border-gray-100 flex justify-between text-[9px] font-black text-gray-400 uppercase tracking-widest">
                                    <span>General Office Expenses Itemized</span>
                                    <span id="reportGeneralExpensesTotal" class="text-red-500 font-black">Total: ₱0.00</span>
                                </div>
                                <div id="officeExpensesDetailList" class="divide-y divide-gray-50 bg-white">
                                    <!-- Dynamically populated -->
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Computation Summary Box -->
                    <div class="bg-gray-100 border-2 border-gray-900 p-6 rounded-xl shadow-sm mb-6">
                        <div class="flex justify-between items-end mb-2">
                            <span class="text-[10px] font-black text-gray-500 uppercase tracking-[0.2em]">Computation Summary</span>
                            <div class="flex items-center gap-1">
                                <i data-lucide="shield-check" class="w-3 h-3 text-green-600"></i>
                                <span class="text-[9px] text-green-600 font-black italic uppercase tracking-wider">verified</span>
                            </div>
                        </div>
                        <div class="flex justify-between items-center text-3xl font-black text-gray-900 tracking-tighter">
                            <span>NET INCOME</span>
                            <span class="text-emerald-700" id="reportNetIncome">₱0.00</span>
                        </div>
                        <div class="flex justify-between text-[10px] mt-4 pt-3 border-t border-gray-300 text-gray-500 font-black">
                            <span class="uppercase tracking-widest">Efficiency Profit Margin</span>
                            <span class="text-gray-900 font-black" id="reportProfitMargin">0.0%</span>
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
                    <button onclick="window.print()" class="flex items-center gap-2 px-4 py-2 bg-white/20 hover:bg-white/30 text-white rounded-lg font-black text-xs uppercase tracking-widest transition-all border border-white/20">
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
                <div class="max-w-4xl mx-auto bg-white border border-gray-200 rounded-xl p-6 shadow-sm relative" id="expensesReport">
                    <!-- Report Header (Print Only) -->
                    <div class="text-center mb-10 print-only">
                        <div class="flex flex-col items-center mb-4">
                            <img src="{{ asset('image/logo.png') }}" alt="Euro Taxi Logo" class="h-16 w-auto mb-2">
                        </div>
                        <h4 class="text-4xl font-black uppercase tracking-[0.4em] text-gray-900 mb-2">Expense Statement</h4>
                        <div class="text-base text-gray-600 uppercase font-black tracking-widest" id="expensesPeriodLabelPrint">Period: TODAY</div>
                        <div class="text-[12px] text-gray-400 mt-3 font-bold tracking-[0.2em]">EURO TAXI MANAGEMENT SYSTEM • OFFICIAL EXPENSE RECORD</div>
                        <div class="border-t-2 border-gray-100 mt-8 pt-2 h-0 border-dashed"></div>
                    </div>
                    
                    <!-- Operating Expenses Section -->
                    <div class="mb-6">
                        <div class="flex justify-between items-center bg-red-900 text-white px-6 py-3 rounded-t-lg">
                            <span class="text-[11px] uppercase font-black tracking-[0.1em]">Detailed Expenses Breakdown</span>
                            <span class="text-xl font-black text-red-300" id="expensesTotalValue">₱0.00</span>
                        </div>
                        <div class="border-x border-b border-gray-100 rounded-b-lg p-0">
                            <!-- Maintenance Breakdown -->
                            <div class="border-b border-gray-100">
                                <div class="bg-gray-50 px-6 py-1 border-b border-gray-100 flex justify-between text-[9px] font-black text-gray-400 uppercase tracking-widest">
                                    <span>Maintenance & Repairs Itemized</span>
                                    <span id="expensesMaintenanceTotal" class="text-orange-600 font-black text-[10px]">Total: ₱0.00</span>
                                </div>
                                <div id="expensesMaintenanceList" class="divide-y divide-gray-50 bg-white"></div>
                            </div>

                            <!-- Office Breakdown -->
                            <div>
                                <div class="bg-gray-50 px-6 py-1 border-b border-gray-100 flex justify-between text-[9px] font-black text-gray-400 uppercase tracking-widest">
                                    <span>General Office Expenses Itemized</span>
                                    <span id="expensesOfficeTotal" class="text-red-500 font-black text-[10px]">Total: ₱0.00</span>
                                </div>
                                <div id="expensesOfficeList" class="divide-y divide-gray-50 bg-white"></div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Computation Summary Box -->
                    <div class="bg-gray-100 border-2 border-red-900 p-6 rounded-xl shadow-sm mb-6">
                        <div class="flex justify-between items-end mb-2">
                            <span class="text-[10px] font-black text-gray-500 uppercase tracking-[0.2em]">Computation Summary</span>
                            <div class="flex items-center gap-1">
                                <i data-lucide="check-circle" class="w-3 h-3 text-red-600"></i>
                                <span class="text-[9px] text-red-600 font-black italic uppercase tracking-wider">calculated</span>
                            </div>
                        </div>
                        <div class="flex justify-between items-center text-3xl font-black text-gray-900 tracking-tighter">
                            <span>TOTAL OPERATING EXPENSES</span>
                            <span class="text-red-700" id="finalExpensesTotal">₱0.00</span>
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
                    <input 
                        type="text" 
                        id="boundarySearchInput"
                        placeholder="Search by unit number, driver, or amount..."
                        class="w-full pl-10 pr-4 py-2 bg-white/20 backdrop-blur-sm border border-white/30 rounded-lg text-white placeholder-white/70 focus:outline-none focus:ring-2 focus:ring-white/50 focus:border-transparent transition-all duration-200 text-sm"
                        onkeyup="filterBoundaryCollections()"
                    >
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
        <div class="bg-white rounded-2xl shadow-2xl max-w-7xl w-full mx-4 h-[95vh] flex flex-col border border-gray-100">
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
                        <input 
                            type="text" 
                            id="unitSearchInput"
                            placeholder="Search units by number, status, or performance..."
                            class="w-full pl-10 pr-4 py-2 bg-white/20 backdrop-blur-sm border border-white/30 rounded-lg text-white placeholder-white/70 focus:outline-none focus:ring-2 focus:ring-white/50 focus:border-transparent transition-all duration-200 text-sm"
                            onkeyup="filterUnits()"
                        >
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

@endsection

@push('scripts')
    <script src="{{ asset('js/realtime-dashboard.js') }}"></script>
    <script>
        // Register Chart.js datalabels plugin
        Chart.register(ChartDataLabels);
        
        // Debug: Check if Chart.js is loaded
        console.log('Chart.js loaded:', typeof Chart !== 'undefined');
        console.log('ChartDataLabels loaded:', typeof ChartDataLabels !== 'undefined');
        
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
            window.weeklyChart = new Chart(weeklyCtx, {
                type: 'line',
                data: {
                    labels: weeklyData.map(d => d.day),
                    datasets: [
                        { label: 'Boundary', data: weeklyData.map(d => d.boundary), borderColor: '#eab308', backgroundColor: wGrad1, borderWidth: 2.5, tension: 0.45, fill: true, pointBackgroundColor: '#eab308', pointRadius: 4, pointHoverRadius: 7 },
                        { label: 'Expenses', data: weeklyData.map(d => d.expenses), borderColor: '#ef4444', backgroundColor: wGrad2, borderWidth: 2.5, tension: 0.45, fill: true, pointBackgroundColor: '#ef4444', pointRadius: 4, pointHoverRadius: 7 },
                        { label: 'Net Income', data: weeklyData.map(d => d.net), borderColor: '#22c55e', backgroundColor: wGrad3, borderWidth: 2.5, tension: 0.45, fill: true, pointBackgroundColor: '#22c55e', pointRadius: 4, pointHoverRadius: 7 }
                    ]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
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
            });
        } catch (error) { console.error('Weekly Chart Error:', error); }

        // Unit Status Chart
        try {
            const unitStatusCtx = document.getElementById('unitStatusChart').getContext('2d');
            const unitStatusData = @json($unit_status_data);
            console.log('Unit Status Data:', unitStatusData);
            window.unitStatusChart = new Chart(unitStatusCtx, {
                type: 'bar',
                data: {
                    labels: unitStatusData.map(d => d.status),
                    datasets: [{ label: 'Units', data: unitStatusData.map(d => d.count), backgroundColor: '#eab308', borderColor: '#ca8a04', borderWidth: 1 }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
                }
            });
        } catch (error) {
            console.error('Unit Status Chart Error:', error);
        }

        // Revenue Trend Chart - Premium Line
        try {
            const revenueTrendCtx = document.getElementById('revenueTrendChart').getContext('2d');
            const revenueTrendData = @json($revenue_trend);
            const rGrad = revenueTrendCtx.createLinearGradient(0, 0, 0, 320);
            rGrad.addColorStop(0, 'rgba(37,99,235,0.3)'); rGrad.addColorStop(0.6, 'rgba(37,99,235,0.08)'); rGrad.addColorStop(1, 'rgba(37,99,235,0)');
            window.revenueTrendChart = new Chart(revenueTrendCtx, {
                type: 'line',
                data: {
                    labels: revenueTrendData.map(d => d.date),
                    datasets: [{
                        label: 'Revenue', data: revenueTrendData.map(d => d.revenue),
                        borderColor: '#2563eb', backgroundColor: rGrad,
                        borderWidth: 3, tension: 0.45, fill: true,
                        pointBackgroundColor: '#fff', pointBorderColor: '#2563eb', pointBorderWidth: 2.5,
                        pointRadius: 5, pointHoverRadius: 8, pointHoverBackgroundColor: '#2563eb'
                    }]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
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
            });
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

            window.unitPerformanceChart = new Chart(unitPerformanceCtx, {
                type: 'bar',
                data: {
                    labels: unitPerformanceData.map(d => d.unit),
                    datasets: [
                        {
                            label: 'Actual Collected',
                            data: unitPerformanceData.map(d => d.performance),
                            backgroundColor: actualGradient,
                            borderColor: '#2563eb',
                            borderWidth: 0,
                            borderRadius: 6,
                            barThickness: 12,
                        },
                        {
                            label: 'Monthly Target (30 Days)',
                            data: unitPerformanceData.map(d => d.target),
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
                    plugins: {
                        legend: {
                            display: false // Using custom legend in sidebar instead
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
            });

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
            window.expenseBreakdownChart = new Chart(expenseBreakdownCtx, {
                type: 'pie',
                data: {
                    labels: expenseBreakdownData.map(d => d.category),
                    datasets: [{ data: expenseBreakdownData.map(d => d.amount), backgroundColor: pieColors, hoverBackgroundColor: pieHover, borderWidth: 3, borderColor: '#fff', hoverOffset: 12 }]
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
                                    const pct = ((ctx.parsed / total) * 100).toFixed(1);
                                    return ` ${ctx.label}: ₱${ctx.parsed.toLocaleString()} (${pct}%)`;
                                }
                            }
                        },
                        datalabels: { color: '#fff', font: { weight: 'bold', size: 12 }, formatter: (val, ctx) => { const total = ctx.dataset.data.reduce((a,b)=>a+b,0); const pct = ((val/total)*100).toFixed(0); return pct > 5 ? pct+'%' : ''; } }
                    },
                    animation: { animateRotate: true, duration: 900, easing: 'easeOutQuart' }
                }
            });
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
            window.topDriversChart = new Chart(topDriversCtx, {
                type: 'bar',
                data: {
                    labels: topDriversData.map((d,i) => { const medals=['🥇','🥈','🥉']; return `${medals[i]||'  '} ${d.name}`; }),
                    datasets: [{ label: 'Reliability Score', data: topDriversData.map(d => d.score),
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
                    animation: { duration: 1200, easing: 'easeOutQuart' }
                }
            });
        } catch (error) { console.error('Top Drivers Chart Error:', error); }




        // Unit Status Distribution Chart - Premium Donut
        try {
            const unitStatusDistCtx = document.getElementById('unitStatusDistributionChart').getContext('2d');
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
            window.unitStatusDistChart = new Chart(unitStatusDistCtx, {
                type: 'doughnut',
                data: { labels: distLabels, datasets: [{ data: distValues, backgroundColor: donutColors, hoverBackgroundColor: donutHover, borderWidth: 4, borderColor: '#fff', hoverOffset: 16 }] },
                options: {
                    responsive: true, maintainAspectRatio: false, cutout: '72%',
                    plugins: {
                        legend: { position: 'right', labels: { usePointStyle: true, pointStyleWidth: 12, font: { size: 12, weight: '600' }, padding: 18, color: '#374151',
                            generateLabels: (chart) => chart.data.labels.map((label, i) => ({ text: `${label}: ${chart.data.datasets[0].data[i]}`, fillStyle: donutColors[i], strokeStyle: '#fff', lineWidth: 2, index: i })) } },
                        tooltip: { backgroundColor: 'rgba(15,23,42,0.95)', padding: 14, cornerRadius: 12,
                            callbacks: { label: ctx => { const total = ctx.dataset.data.reduce((a,b)=>a+b,0); const pct = total>0?((ctx.parsed/total)*100).toFixed(1):0; return ` ${ctx.label}: ${ctx.parsed} units (${pct}%)`; } } },
                        datalabels: { color: '#fff', font: { weight: 'bold', size: 13 }, formatter: (val, ctx) => { const sum = ctx.dataset.data.reduce((a,b)=>a+b,0); const pct = sum>0?((val/sum)*100).toFixed(0):0; return pct>5?pct+'%':''; } }
                    },
                    animation: { animateRotate: true, duration: 900, easing: 'easeOutQuart' }
                },
                plugins: [{ id: 'donutCenter', afterDraw(chart) {
                    const { ctx, chartArea: { left, top, right, bottom } } = chart;
                    const cx = (left+right)/2, cy = (top+bottom)/2;
                    ctx.save();
                    ctx.font = 'bold 28px Inter, sans-serif'; ctx.fillStyle = '#0f172a'; ctx.textAlign = 'center'; ctx.textBaseline = 'middle';
                    ctx.fillText(totalUnits, cx, cy-10);
                    ctx.font = '600 11px Inter, sans-serif'; ctx.fillStyle = '#94a3b8';
                    ctx.fillText('TOTAL UNITS', cx, cy+14);
                    ctx.restore();
                }}]
            });
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
                        window.revenueTrendChart.data.datasets[0].data = data.data.map(d => d.revenue);
                        window.revenueTrendChart.update('none');
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
                <div class="bg-white rounded-xl shadow-md hover:shadow-lg transition-all duration-300 overflow-hidden border-l-4 ${statusColor} hover:scale-102">
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
                                <span class="text-xs font-bold text-orange-600">${isComplete ? '₱' + (unit.maintenance_cost || 0).toLocaleString() : (unit.estimated_completion || 'N/A')}</span>
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
                    console.log('API Response Status:', response.status);
                    console.log('API Response Headers:', response.headers);
                    return response.text();
                })
                .then(text => {
                    console.log('API Response Text:', text);
                    try {
                        const data = JSON.parse(text);
                        console.log('Parsed API Data:', data);
                        showMaintenanceError('API Test Complete - Check Console for Details', {
                            response_status: 'success',
                            data_keys: Object.keys(data),
                            data: data
                        });
                    } catch (parseError) {
                        console.log('JSON Parse Error:', parseError);
                        showMaintenanceError('API Test Complete - JSON Parse Error', {
                            response_status: 'parse_error',
                            raw_response: text.substring(0, 500) + (text.length > 500 ? '...' : ''),
                            parse_error: parseError.message
                        });
                    }
                })
                .catch(error => {
                    console.log('API Fetch Error:', error);
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
                    console.error('Error loading active drivers data:', error);
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
                <div class="bg-white rounded-xl shadow-md hover:shadow-lg transition-all duration-300 overflow-hidden border-l-4 border-blue-500 hover:scale-102">
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
                        
                        <!-- Driver Details -->
                        <div class="bg-gray-50 rounded-lg p-3 mb-3">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm font-medium text-gray-900">Contact: ${driver.phone || 'N/A'}</span>
                            </div>
                            <div class="text-xs text-gray-600">
                                <span class="font-medium">Address:</span> ${driver.address || 'No address available'}
                            </div>
                        </div>
                        
                        <!-- Performance Stats -->
                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center gap-2">
                                <div class="w-1.5 h-1.5 rounded-full ${driver.performance_rating === 'excellent' ? 'bg-green-500' : driver.performance_rating === 'good' ? 'bg-yellow-500' : driver.performance_rating === 'average' ? 'bg-orange-500' : 'bg-gray-400'} animate-pulse"></div>
                                <span class="text-xs font-medium text-gray-600">
                                    ${driver.performance_rating ? driver.performance_rating.charAt(0).toUpperCase() + driver.performance_rating.slice(1) : 'Unknown'}
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
                });
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
                    console.log('API Response Status:', response.status);
                    console.log('API Response Headers:', response.headers);
                    return response.text();
                })
                .then(text => {
                    console.log('API Response Text:', text);
                    try {
                        const data = JSON.parse(text);
                        console.log('Parsed API Data:', data);
                        showActiveDriversError('API Test Complete - Check Console for Details', {
                            response_status: 'success',
                            data_keys: Object.keys(data),
                            data: data
                        });
                    } catch (parseError) {
                        console.log('JSON Parse Error:', parseError);
                        showActiveDriversError('API Test Complete - JSON Parse Error', {
                            response_status: 'parse_error',
                            raw_response: text.substring(0, 500) + (text.length > 500 ? '...' : ''),
                            parse_error: parseError.message
                        });
                    }
                })
                .catch(error => {
                    console.log('API Fetch Error:', error);
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
                    console.error('Error loading coding units data:', error);
                    showCodingError('Error loading coding units data. Please try again.');
                });
        }
        
        function displayCodingUnitsData(data) {
            const grid = document.getElementById('codingGrid');
            const units = data.units || [];
            const stats = data.stats || {};
            
            // Update summary stats
            document.getElementById('codingUnitsCount').textContent = units.length || 0;
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
            
            grid.innerHTML = units.map(unit => `
                <div class="bg-white rounded-xl shadow-md hover:shadow-lg transition-all duration-300 overflow-hidden border-l-4 border-purple-500 hover:scale-102">
                    <div class="p-4">
                        <!-- Header -->
                        <div class="flex items-start justify-between mb-3">
                            <div class="flex items-center gap-3">
                                <div class="p-2 bg-purple-100 rounded-lg">
                                    <i data-lucide="code" class="w-4 h-4 text-purple-600"></i>
                                </div>
                                <div>
                                    <h4 class="text-lg font-bold text-gray-900">${unit.plate_number || 'N/A'}</h4>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="text-lg font-bold text-purple-600">${unit.coding_type || 'Coding'}</div>
                                <div class="text-xs text-gray-500">${unit.start_date ? unit.start_date : (unit.coding_day !== 'Unknown' ? 'Every ' + unit.coding_day : 'No date')}</div>
                            </div>
                        </div>
                        
                        <!-- Coding Details -->
                        <div class="bg-gray-50 rounded-lg p-3 mb-3">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm font-medium text-gray-900">Status: ${unit.status || 'Unknown'}</span>
                                <span class="text-xs text-gray-600">${unit.estimated_completion || 'N/A'}</span>
                            </div>
                            <div class="text-xs text-gray-600">
                                <span class="font-medium">Description:</span> ${unit.description || 'No description available'}
                            </div>
                        </div>
                        
                        <!-- Footer -->
                        <div class="flex items-center justify-between text-xs text-gray-500">
                            <span class="flex items-center gap-1">
                                <i data-lucide="calendar" class="w-3 h-3"></i>
                                ${unit.start_date ? unit.start_date : (unit.coding_day !== 'Unknown' ? 'Every ' + unit.coding_day : 'No start date')}
                            </span>
                            <span class="flex items-center gap-1">
                                <i data-lucide="check-circle" class="w-3 h-3"></i>
                                ${unit.status || 'Unknown'}
                            </span>
                        </div>
                    </div>
                </div>
            `).join('');
        }
        
        function updateCodingSummary(units) {
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
            
            const dayNames = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
            const todayDayName = dayNames[today.getDay()];
            const tomorrowDayName = dayNames[tomorrow.getDay()];
            
            const counts = {
                today: 0,
                tomorrow: 0,
                past: 0
            };
            
            units.forEach(unit => {
                const unitDate = unit.start_date;
                const codingDay = unit.coding_day;
                const isCompleted = unit.coding_status === 'completed';
                
                if (isCompleted || (unitDate && unitDate < todayStr)) {
                    counts.past++;
                } else if (unitDate === todayStr || (!unitDate && codingDay === todayDayName)) {
                    counts.today++;
                } else if (unitDate === tomorrowStr || (!unitDate && codingDay === tomorrowDayName)) {
                    counts.tomorrow++;
                }
            });
            
            document.getElementById('todayCodingCount').textContent = counts.today;
            document.getElementById('tomorrowCodingCount').textContent = counts.tomorrow;
            document.getElementById('pastCodingCount').textContent = counts.past;
        }
        
        window.currentCodingPeriod = 'all';

        function setCodingPeriod(period) {
            window.currentCodingPeriod = period;
            
            // Update UI
            const periods = ['all', 'today', 'tomorrow', 'past'];
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
            const currentPeriod = window.currentCodingPeriod || 'all';
            
            let filteredUnits = window.originalCodingUnitsData || [];

            // Get current dates
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
            
            const dayNames = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
            const todayDayName = dayNames[today.getDay()];
            const tomorrowDayName = dayNames[tomorrow.getDay()];
            
            // Apply period filter
            if (currentPeriod !== 'all') {
                filteredUnits = filteredUnits.filter(unit => {
                    const unitDate = unit.start_date;
                    const codingDay = unit.coding_day;
                    const isCompleted = unit.coding_status === 'completed';
                    
                    if (currentPeriod === 'today') {
                        return !isCompleted && (unitDate === todayStr || (!unitDate && codingDay === todayDayName));
                    }
                    if (currentPeriod === 'tomorrow') {
                        return !isCompleted && (unitDate === tomorrowStr || (!unitDate && codingDay === tomorrowDayName));
                    }
                    if (currentPeriod === 'past') {
                        return isCompleted || (unitDate && unitDate < todayStr);
                    }
                    return true;
                });
            }
            
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
                    console.log('API Response Status:', response.status);
                    console.log('API Response Headers:', response.headers);
                    return response.text();
                })
                .then(text => {
                    console.log('API Response Text:', text);
                    try {
                        const data = JSON.parse(text);
                        console.log('Parsed API Data:', data);
                        showCodingError('API Test Complete - Check Console for Details', {
                            response_status: 'success',
                            data_keys: Object.keys(data),
                            data: data
                        });
                    } catch (parseError) {
                        console.log('JSON Parse Error:', parseError);
                        showCodingError('API Test Complete - JSON Parse Error', {
                            response_status: 'parse_error',
                            raw_response: text.substring(0, 500) + (text.length > 500 ? '...' : ''),
                            parse_error: parseError.message
                        });
                    }
                })
                .catch(error => {
                    console.log('API Fetch Error:', error);
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
                    console.error('Error loading income data:', error);
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
                        <span class="text-right">Amount (PHP)</span>
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
                                        ${item.type === 'income' ? '+' : '-'} ${Math.abs(parseFloat(item.amount) || 0).toLocaleString(undefined, {minimumFractionDigits: 2})}
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
            safeSet('reportTotalExpenses', fmt(totalExpenses));
            safeSet('reportMaintenanceTotal', 'Total: ' + fmt(breakdown.maintenanceTotal));
            safeSet('reportGeneralExpensesTotal', 'Total: ' + fmt(breakdown.officeTotal));
            safeSet('reportNetIncome', fmt(netIncome));
            safeSet('reportProfitMargin', profitMargin.toFixed(1) + '%');
            safeSet('reportTimestamp', new Date().toLocaleString());
            
            // Helper to render lists
            const renderList = (id, items) => {
                const el = document.getElementById(id);
                if (!el) return;
                
                if (items.length > 0) {
                    el.innerHTML = items.map(item => `
                        <div class="px-6 py-2 flex justify-between items-center hover:bg-gray-50/50 transition-colors border-b border-gray-50 last:border-0">
                            <div class="flex flex-col">
                                <span class="text-[10px] font-black text-gray-800 tracking-tight leading-tight">${item.description}</span>
                                <span class="text-[8px] text-gray-400 font-bold uppercase">${item.date}</span>
                            </div>
                            <span class="text-xs font-black ${id === 'revenueDetailList' ? 'text-emerald-600' : 'text-red-500'}">${fmt(item.amount)}</span>
                        </div>
                    `).join('');
                } else {
                    el.innerHTML = '';
                }
            };

            renderList('revenueDetailList', breakdown.revenueItems);
            renderList('maintenanceDetailList', breakdown.maintenanceItems);
            renderList('officeExpensesDetailList', breakdown.officeItems);
        }

        function renderIncomeData(data) {
            // Grid rendering is now integrated into updateIncomeSummary
            // This function is kept for compatibility with fetchIncomeData flow
            console.log("Income report updated with " + data.length + " items");
        }

        function printReport() {
            window.print();
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

        function renderExpensesReport(data) {
            const breakdown = {
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

            const totalExpenses = breakdown.maintenanceTotal + breakdown.officeTotal;
            const fmt = (num) => '₱' + num.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});

            const safeSet = (id, val) => {
                const el = document.getElementById(id);
                if (el) el.textContent = val;
            };

            safeSet('expensesTotalValue', fmt(totalExpenses));
            safeSet('expensesMaintenanceTotal', 'Total: ' + fmt(breakdown.maintenanceTotal));
            safeSet('expensesOfficeTotal', 'Total: ' + fmt(breakdown.officeTotal));
            safeSet('finalExpensesTotal', fmt(totalExpenses));
            safeSet('expensesTimestamp', new Date().toLocaleString());

            const renderList = (id, items) => {
                const el = document.getElementById(id);
                if (!el) return;
                
                if (items.length > 0) {
                    el.innerHTML = items.map(item => `
                        <div class="px-6 py-2 flex justify-between items-center hover:bg-gray-50/50 transition-colors border-b border-gray-50 last:border-0">
                            <div class="flex flex-col">
                                <span class="text-[10px] font-black text-gray-800 tracking-tight leading-tight">${item.description}</span>
                                <span class="text-[8px] text-gray-400 font-bold uppercase">${item.date}</span>
                            </div>
                            <span class="text-xs font-black text-red-500">${fmt(item.amount)}</span>
                        </div>
                    `).join('');
                } else {
                    el.innerHTML = '';
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
                    console.log('API Response Status:', response.status);
                    console.log('API Response Headers:', response.headers);
                    console.log('Content-Type:', response.headers.get('content-type'));
                    
                    // Check if response is HTML (error page) or JSON
                    const contentType = response.headers.get('content-type');
                    if (contentType && contentType.includes('text/html')) {
                        return response.text().then(text => {
                            throw new Error('API returned HTML instead of JSON. This usually means a Laravel error occurred. Response: ' + text.substring(0, 200) + '...');
                        });
                    }
                    
                    return response.text();
                })
                .then(text => {
                    console.log('API Response Text:', text);
                    console.log('Response Length:', text.length);
                    console.log('First 100 chars:', text.substring(0, 100));
                    
                    // Check if response starts with HTML
                    if (text.trim().startsWith('<')) {
                        throw new Error('API returned HTML instead of JSON. Response starts with: ' + text.substring(0, 100) + '...');
                    }
                    
                    try {
                        const data = JSON.parse(text);
                        console.log('Parsed API Data:', data);
                        showIncomeError('API Test Complete - Check Console for Details', {
                            response_status: 'success',
                            data_keys: Object.keys(data),
                            data: data
                        });
                    } catch (parseError) {
                        console.log('JSON Parse Error:', parseError);
                        console.log('Raw Response:', text);
                        showIncomeError('API Test Complete - JSON Parse Error', {
                            response_status: 'parse_error',
                            raw_response: text.substring(0, 500) + (text.length > 500 ? '...' : ''),
                            parse_error: parseError.message,
                            response_length: text.length,
                            first_chars: text.substring(0, 100)
                        });
                    }
                })
                .catch(error => {
                    console.log('API Fetch Error:', error);
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
                    console.error('Error loading boundary collections:', error);
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
                    console.log('Units data received:', data);
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
                <div class="bg-white rounded-lg shadow border-t-2 ${statusColors[unit.status] || 'border-gray-200'} hover:shadow-md transition-shadow">
                    <div class="p-2">
                        <!-- Summary Header -->
                        <div class="flex items-center justify-between gap-2 mb-1.5">
                            <div class="flex items-center gap-1.5 min-w-0">
                                <i data-lucide="car" class="w-3 h-3 text-gray-400"></i>
                                <h4 class="text-sm font-bold text-gray-900 truncate">${unit.plate_number}</h4>
                            </div>
                            <span class="px-1.5 py-0.5 text-[9px] font-bold rounded-full ${statusColors[unit.status] || 'bg-gray-100'} uppercase">
                                ${unit.status}
                            </span>
                        </div>
                        
                        <!-- Essential Stats -->
                        <div class="grid grid-cols-2 gap-2 text-center py-1 bg-gray-50 rounded">
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
                        <div class="mt-1.5 flex items-center justify-between text-[8px] font-bold text-gray-400 uppercase tracking-tighter">
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
        
        window.currentNetIncomeFilter = 'today';

        function updateNetIncomeStat(filter, btn) {
            window.currentNetIncomeFilter = filter;
            
            // Update UI buttons
            document.querySelectorAll('.net-income-btn').forEach(b => {
                b.classList.remove('bg-white', 'text-green-900', 'shadow-lg', 'shadow-black/10');
                b.classList.add('bg-white/20', 'text-white');
            });
            
            btn.classList.remove('bg-white/20', 'text-white');
            btn.classList.add('bg-white', 'text-green-900', 'shadow-lg', 'shadow-black/10');
            
            // Update Label
            const labels = { 'today': 'Net Income Today', 'month': 'Net Income Monthly', 'year': 'Net Income Yearly' };
            document.getElementById('netIncomeLabel').textContent = labels[filter];

            // Immediate Refresh
            refreshDashboard();
        }

        function refreshDashboard() {
            const filter = window.currentNetIncomeFilter || 'today';
            fetch(`{{ url('/api/dashboard/realtime') }}?net_income_filter=${filter}`)
                .then(response => response.json())
                .then(data => {
                    if (data.stats) {
                        updateDashboardStats(data.stats);
                    }
                })
                .catch(err => console.error('Error refreshing stats:', err));
        }

        function clearSearch() {
            document.getElementById('unitSearchInput').value = '';
            setUnitStatusFilter('all');
        }
    </script>
@endpush
