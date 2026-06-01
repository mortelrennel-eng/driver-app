@extends('layouts.app')
@section('title', 'Driver Performance - Euro System')
@section('page-heading', 'Driver Performance & Violations')
@section('page-subheading', 'Incidents • Incentives • Driver Profiles — All in one place')

@section('content')
<style>
    .tab-btn { 
        padding: 0.625rem 1.25rem;
        font-size: 0.75rem;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: 0.1em;
        border-radius: 0.75rem;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
        cursor: pointer;
    }
    .tab-btn.active { 
        background-color: #eab308; 
        color: white; 
        box-shadow: 0 10px 15px -3px rgba(234, 179, 8, 0.3);
        border: 1px solid #eab308;
    }
    .tab-btn:not(.active) { 
        background-color: white; 
        color: #6b7280; 
        border: 1px solid #f3f4f6; 
    }
    .tab-btn:not(.active):hover { 
        background-color: #fefce8; 
        color: #ca8a04; 
        transform: translateY(-2px) scale(1.05);
        box-shadow: 0 20px 25px -5px rgba(234, 179, 8, 0.1);
        border-color: #fde047;
    }
    .tab-btn:active { transform: scale(0.95); }
    .incident-tag { @apply px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-widest border; }
    .stat-card-premium { @apply transition-all duration-500 hover:-translate-y-2 hover:shadow-2xl cursor-default; }
    .custom-scroll::-webkit-scrollbar { width: 4px; }
    .custom-scroll::-webkit-scrollbar-thumb { background: #eab308; border-radius: 99px; }
    
    .search-dropdown {
        display: none;
        position: absolute;
        z-index: 50;
        width: 100%;
        margin-top: 0.25rem;
        background-color: white;
        border: 1px solid #d1d5db;
        border-radius: 0.5rem;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        max-height: 10rem;
        overflow-y: auto;
        flex-direction: column;
    }
    .search-dropdown:not(.hidden) { display: flex; }
    .search-option { padding: 0.5rem 0.75rem; cursor: pointer; border-bottom: 1px solid #f3f4f6; }
    .search-option:last-child { border-bottom: none; }
    .search-option:hover { background-color: #fefce8; }
</style>

{{-- ════════ HEADER STATS (COMPACT) ════════ --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">

    {{-- 1. VIOLATIONS TODAY --}}
    <div class="stat-card-premium relative overflow-hidden bg-gradient-to-br from-red-600 to-rose-700 rounded-2xl p-4 text-white shadow-lg shadow-red-100 group">
        <div class="absolute right-[-5px] top-[-5px] opacity-10 transition-transform group-hover:scale-110 duration-500">
            <i data-lucide="alert-circle" class="w-16 h-16"></i>
        </div>
        <div class="relative z-10 flex flex-col items-center text-center">
            <p class="text-3xl font-black tracking-tighter leading-none">{{ $stats['violations_today'] ?? 0 }}</p>
            <p class="text-[9px] font-black uppercase tracking-[0.1em] opacity-80 mt-1">Violations Today</p>
        </div>
    </div>

    {{-- 2. TOTAL VIOLATORS --}}
    <div class="stat-card-premium relative overflow-hidden bg-gradient-to-br from-teal-500 to-emerald-600 rounded-2xl p-4 text-white shadow-lg shadow-teal-100 group">
        <div class="absolute right-[-5px] top-[-5px] opacity-10 transition-transform group-hover:scale-110 duration-500">
            <i data-lucide="users" class="w-16 h-16"></i>
        </div>
        <div class="relative z-10 flex flex-col items-center text-center">
            <p class="text-3xl font-black tracking-tighter leading-none">{{ $stats['total_violators'] ?? 0 }}</p>
             <p class="text-[9px] font-black uppercase tracking-[0.1em] opacity-80 mt-1">Total Violators</p>
        </div>
    </div>

    {{-- 3. TOTAL CHARGES --}}
    <div class="stat-card-premium relative overflow-hidden bg-gradient-to-br from-purple-600 to-indigo-700 rounded-2xl p-4 text-white shadow-lg shadow-purple-100 group">
        <div class="absolute right-[-5px] top-[-5px] opacity-10 transition-transform group-hover:scale-110 duration-500">
            <i data-lucide="banknote" class="w-16 h-16"></i>
        </div>
        <div class="relative z-10 flex flex-col items-center text-center">
            <p class="text-xl font-black tracking-tighter leading-none">₱{{ number_format($stats['total_charges'] ?? 0, 0) }}</p>
            <p class="text-[9px] font-black uppercase tracking-[0.1em] opacity-80 mt-1">Total Charges</p>
        </div>
    </div>

    {{-- 4. ELIGIBLE INCENTIVE --}}
    <div class="stat-card-premium relative overflow-hidden bg-gradient-to-br from-yellow-500 to-orange-500 rounded-2xl p-4 text-white shadow-lg shadow-yellow-100 group">
        <div class="absolute right-[-5px] top-[-5px] opacity-10 transition-transform group-hover:scale-110 duration-500">
            <i data-lucide="trophy" class="w-16 h-16"></i>
        </div>
        <div class="relative z-10 flex flex-col items-center text-center">
            <p class="text-3xl font-black tracking-tighter leading-none">{{ count($incentive_summary['eligible'] ?? []) }}</p>
            <p class="text-[9px] font-black uppercase tracking-[0.1em] opacity-80 mt-1">Eligible Incentive</p>
        </div>
    </div>
</div>

{{-- ════════ TAB NAVIGATION ════════ --}}
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-3 mb-5 flex flex-wrap gap-2">
    <button onclick="switchTab('incidents')" id="tab-btn-incidents"
        class="tab-btn {{ ($tab ?? 'incidents') === 'incidents' ? 'active' : '' }}">
        <i data-lucide="list" class="w-3.5 h-3.5 inline mr-1"></i> Incident Log
    </button>
    <button onclick="switchTab('incentives')" id="tab-btn-incentives"
        class="tab-btn {{ ($tab ?? '') === 'incentives' ? 'active' : '' }}">
        <i data-lucide="trophy" class="w-3.5 h-3.5 inline mr-1"></i>
        Incentive Dashboard
        @if(count($incentive_summary['eligible'] ?? []) > 0)
            <span class="ml-1 px-1.5 py-0.5 bg-green-500 text-white text-[9px] rounded-full">{{ count($incentive_summary['eligible']) }}</span>
        @endif
    </button>
    <button onclick="switchTab('profiles')" id="tab-btn-profiles"
        class="tab-btn {{ ($tab ?? '') === 'profiles' ? 'active' : '' }}">
        <i data-lucide="user-circle" class="w-3.5 h-3.5 inline mr-1"></i> Driver Profiles
    </button>
    <div class="flex-1"></div>
    <button onclick="openIncidentModal()" class="px-5 py-2.5 bg-red-600 text-white font-black text-xs uppercase tracking-widest rounded-xl hover:bg-red-700 hover:scale-105 hover:shadow-xl hover:shadow-red-200 transition-all active:scale-95 flex items-center gap-2 shadow-sm">
        <i data-lucide="plus" class="w-4 h-4"></i> Record Incident
    </button>
</div>

@if(session('success'))
<div class="mb-4 px-5 py-3 bg-green-50 border border-green-200 text-green-700 rounded-2xl text-sm font-semibold flex items-center gap-2">
    <i data-lucide="check-circle" class="w-4 h-4"></i> {{ session('success') }}
</div>
@endif

{{-- ════════════════════════════════════════
     TAB 1: INCIDENT LOG
     ════════════════════════════════════════ --}}
<div id="tab-incidents" class="tab-content {{ ($tab ?? 'incidents') === 'incidents' ? '' : 'hidden' }}">

    {{-- Filters --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 mb-4">
        <form method="GET" action="{{ route('driver-behavior.index') }}" class="flex flex-wrap gap-3 items-end">
            <input type="hidden" name="tab" value="incidents">
            <div class="flex-1 min-w-[180px]">
                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Search</label>
                <div class="relative">
                    <i data-lucide="search" class="absolute left-3 top-2.5 w-3.5 h-3.5 text-gray-400"></i>
                    <input type="text" name="search" value="{{ $search }}" placeholder="Driver, unit, description..."
                        class="w-full pl-9 pr-3 py-2 bg-gray-50 border border-gray-100 rounded-xl text-sm font-medium focus:ring-2 focus:ring-yellow-500 focus:outline-none">
                </div>
            </div>
            <div class="w-40">
                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Type</label>
                <select name="type" onchange="this.form.submit()" class="w-full px-3 py-2 bg-gray-50 border border-gray-100 rounded-xl text-sm font-medium focus:ring-2 focus:ring-yellow-500 focus:outline-none">
                    <option value="">All Types</option>
                    @foreach(App\Http\Controllers\DriverBehaviorController::$incidentTypes as $type => $meta)
                        <option value="{{ $type }}" {{ $type_filter === $type ? 'selected' : '' }}>{{ $type }}</option>
                    @endforeach
                </select>
            </div>
            <div class="w-36">
                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Severity</label>
                <select name="severity" onchange="this.form.submit()" class="w-full px-3 py-2 bg-gray-50 border border-gray-100 rounded-xl text-sm font-medium focus:ring-2 focus:ring-yellow-500 focus:outline-none">
                    <option value="">All</option>
                    <option value="critical" {{ $severity_filter === 'critical' ? 'selected' : '' }}>Critical</option>
                    <option value="high" {{ $severity_filter === 'high' ? 'selected' : '' }}>High</option>
                    <option value="medium" {{ $severity_filter === 'medium' ? 'selected' : '' }}>Medium</option>
                    <option value="low" {{ $severity_filter === 'low' ? 'selected' : '' }}>Low</option>
                </select>
            </div>
            <div class="w-36">
                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">From</label>
                <input type="date" name="date_from" value="{{ $date_from }}" onchange="this.form.submit()"
                    class="w-full px-3 py-2 bg-gray-50 border border-gray-100 rounded-xl text-sm font-medium focus:ring-2 focus:ring-yellow-500 focus:outline-none">
            </div>
            <div class="w-36">
                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">To</label>
                <input type="date" name="date_to" value="{{ $date_to }}" onchange="this.form.submit()"
                    class="w-full px-3 py-2 bg-gray-50 border border-gray-100 rounded-xl text-sm font-medium focus:ring-2 focus:ring-yellow-500 focus:outline-none">
            </div>
        </form>
    </div>

    {{-- Incident Table --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-50">
                <thead class="bg-gray-50/80">
                    <tr>
                        <th class="px-5 py-3.5 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Date / Time</th>
                        <th class="px-5 py-3.5 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Driver</th>
                        <th class="px-5 py-3.5 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Unit</th>
                        <th class="px-5 py-3.5 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Incident Description & Charges</th>
                        <th class="px-5 py-3.5 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Incentive Status</th>
                        <th class="px-5 py-3.5 text-right text-[10px] font-black text-gray-400 uppercase tracking-widest">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($incidents as $inc)
                    @php
                        $sevColors = [
                            'critical' => 'bg-red-100 text-red-700 border-red-200',
                            'high'     => 'bg-orange-100 text-orange-700 border-orange-200',
                            'medium'   => 'bg-yellow-100 text-yellow-700 border-yellow-200',
                            'low'      => 'bg-blue-100 text-blue-700 border-blue-200',
                        ];
                        $typeColors = [
                            'Coding Violation'    => 'bg-red-100 text-red-700 border-red-200',
                            'Late Boundary'       => 'bg-orange-100 text-orange-700 border-orange-200',
                            'Short Boundary'      => 'bg-yellow-100 text-yellow-700 border-yellow-200',
                            'Vehicle Damage'      => 'bg-purple-100 text-purple-700 border-purple-200',
                            'Accident'            => 'bg-red-100 text-red-700 border-red-200',
                            'Traffic Violation'   => 'bg-orange-100 text-orange-700 border-orange-200',
                            'Absent / No Show'    => 'bg-gray-100 text-gray-600 border-gray-200',
                            'Passenger Complaint' => 'bg-blue-100 text-blue-700 border-blue-200',
                        ];
                        $tc  = $typeColors[$inc->incident_type] ?? 'bg-gray-100 text-gray-600 border-gray-200';
                        $sc  = $sevColors[$inc->severity] ?? 'bg-gray-100 text-gray-600 border-gray-200';
                        $isAccident = in_array($inc->incident_type, ['Accident','Vehicle Damage']);
                    @endphp
                    <tr class="hover:bg-gray-50/50 transition-colors group">
                        <td class="px-5 py-3.5 whitespace-nowrap">
                            <div class="text-xs font-bold text-gray-800">{{ \Carbon\Carbon::parse($inc->timestamp)->timezone('Asia/Manila')->format('M d, Y') }}</div>
                            <div class="text-[10px] text-gray-400 font-medium">{{ \Carbon\Carbon::parse($inc->timestamp)->timezone('Asia/Manila')->format('h:i A') }}</div>
                        </td>
                        <td class="px-5 py-3.5 whitespace-nowrap">
                            <div class="text-xs font-bold text-gray-800">{{ $inc->driver_name ?? '—' }}</div>
                        </td>
                        <td class="px-5 py-3.5 whitespace-nowrap">
                            <span class="text-xs font-black text-blue-600 uppercase">{{ $inc->plate_number ?? '—' }}</span>
                        </td>
                        <td class="px-5 py-3.5 max-w-[450px]">
                            {{-- Unified Tags Row --}}
                            <div class="flex flex-wrap gap-1.5 mb-2">
                                {{-- Driver Fault Status --}}
                                @if($inc->is_driver_fault)
                                    <span class="px-2 py-0.5 bg-red-500 text-white text-[9px] font-black uppercase tracking-widest rounded-full shadow-sm shadow-red-100">Driver at Fault</span>
                                @else
                                    <span class="px-2 py-0.5 bg-blue-500 text-white text-[9px] font-black uppercase tracking-widest rounded-full shadow-sm shadow-blue-100">Not at Fault</span>
                                @endif

                                {{-- Charge Info --}}
                                @if($inc->total_charge_to_driver > 0)
                                    <span class="px-2 py-0.5 bg-purple-600 text-white text-[9px] font-black uppercase tracking-widest rounded-full shadow-sm shadow-purple-100">
                                        Amount: ₱{{ number_format($inc->total_charge_to_driver, 2) }}
                                    </span>
                                @endif
                            </div>

                            {{-- Cause --}}
                            @if($inc->cause_of_incident)
                                <div class="mb-1.5">
                                    <span class="text-[9px] font-black text-orange-600 bg-orange-50 px-1.5 py-0.5 rounded-full border border-orange-100 uppercase tracking-widest">Cause: {{ $inc->cause_of_incident }}</span>
                                </div>
                            @endif

                            <p class="text-xs text-gray-800 font-medium leading-relaxed">{{ $inc->description }}</p>
                        </td>

                        <td class="px-5 py-3.5 whitespace-nowrap">
                            @if(in_array($inc->severity, ['high','critical']) || $inc->is_driver_fault)
                                <div class="text-[10px] font-black text-red-500 uppercase tracking-widest leading-tight">VOID</div>
                                <div class="text-[8px] text-gray-400 font-medium uppercase">Performance Impacted</div>
                            @else
                                <div class="text-[10px] font-black text-green-500 uppercase tracking-widest leading-tight">ELIGIBLE</div>
                                <div class="text-[8px] text-gray-400 font-medium uppercase">Active Cycle</div>
                            @endif
                        <td class="px-5 py-3.5 whitespace-nowrap text-right">
                            <div class="flex justify-end items-center gap-2">
                                {{-- Edit Button --}}
                                <button type="button" 
                                    onclick="IncidentManager.openEdit({{ $inc->id }})"
                                    class="p-2 text-gray-400 hover:text-blue-500 hover:bg-blue-50 rounded-xl transition-all duration-300 group/edit cursor-pointer" 
                                    title="Edit Incident">
                                    <i data-lucide="edit-3" class="w-4 h-4 group-hover/edit:scale-110 transition-transform"></i>
                                </button>
                                {{-- Archive Button --}}
                                <button type="button" 
                                    onclick="IncidentManager.archive({{ $inc->id }})"
                                    class="p-2 text-gray-400 hover:text-red-500 hover:bg-red-50 rounded-xl transition-all duration-300 group/delete cursor-pointer" 
                                    title="Archive Record">
                                    <i data-lucide="trash-2" class="w-4 h-4 group-hover/delete:scale-110 pointer-events-none"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="9" class="px-5 py-16 text-center">
                        <div class="w-16 h-16 bg-green-50 rounded-full flex items-center justify-center mx-auto mb-3 border border-green-100">
                            <i data-lucide="shield-check" class="w-8 h-8 text-green-500"></i>
                        </div>
                        <p class="text-sm font-black text-gray-400 uppercase tracking-widest">No incidents found</p>
                        <p class="text-xs text-gray-300 mt-1">All drivers are performing well</p>
                    </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{-- Pagination --}}
        @if($pagination['total_pages'] > 1)
        <div class="px-5 py-4 border-t border-gray-50 flex items-center justify-between">
            <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between font-bold text-[10px] text-gray-400 uppercase tracking-widest">
                <div>
                    <p>Showing <span class="text-gray-900">{{ min($pagination['total_items'], ($pagination['page'] - 1) * 10 + 1) }}</span> to <span class="text-gray-900">{{ min($pagination['total_items'], $pagination['page'] * 10) }}</span> of <span class="text-gray-900">{{ $pagination['total_items'] }}</span> incidents</p>
                </div>
                <div>
                    <nav class="relative z-0 inline-flex rounded-xl shadow-sm -space-x-px" aria-label="Pagination">
                        @if($pagination['has_prev'])
                            <a href="{{ request()->fullUrlWithQuery(['page' => $pagination['prev_page']]) }}" class="relative inline-flex items-center px-2 py-2 rounded-l-xl border border-gray-200 bg-white text-gray-400 hover:bg-gray-50">
                                <i data-lucide="chevron-left" class="w-4 h-4"></i>
                            </a>
                        @endif

                        @php
                            $start = max(1, $pagination['page'] - 2);
                            $end = min($pagination['total_pages'], $pagination['page'] + 2);
                        @endphp

                        @for($i = $start; $i <= $end; $i++)
                            <a href="{{ request()->fullUrlWithQuery(['page' => $i]) }}" 
                               class="relative inline-flex items-center px-4 py-2 border text-[11px] font-black {{ $i === $pagination['page'] ? 'z-10 bg-yellow-500 border-yellow-500 text-white shadow-lg shadow-yellow-500/20' : 'bg-white border-gray-200 text-gray-500 hover:bg-gray-50' }}">
                                {{ $i }}
                            </a>
                        @endfor

                        @if($pagination['has_next'])
                            <a href="{{ request()->fullUrlWithQuery(['page' => $pagination['next_page']]) }}" class="relative inline-flex items-center px-2 py-2 rounded-r-xl border border-gray-200 bg-white text-gray-400 hover:bg-gray-50">
                                <i data-lucide="chevron-right" class="w-4 h-4"></i>
                            </a>
                        @endif
                    </nav>
                </div>
            </div>
            {{-- Mobile simple pagination --}}
            <div class="flex-1 flex justify-between sm:hidden">
                @if($pagination['has_prev'])
                    <a href="{{ request()->fullUrlWithQuery(['page' => $pagination['prev_page']]) }}" class="relative inline-flex items-center px-4 py-2 border border-gray-200 text-xs font-bold rounded-xl text-gray-700 bg-white hover:bg-gray-50">Previous</a>
                @endif
                @if($pagination['has_next'])
                    <a href="{{ request()->fullUrlWithQuery(['page' => $pagination['next_page']]) }}" class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-200 text-xs font-bold rounded-xl text-gray-700 bg-white hover:bg-gray-50">Next</a>
                @endif
            </div>
        </div>
        @endif
    </div>
</div>

{{-- ════════════════════════════════════════
     TAB 2: INCENTIVE DASHBOARD
     ════════════════════════════════════════ --}}
<div id="tab-incentives" class="tab-content {{ ($tab ?? '') === 'incentives' ? '' : 'hidden' }}">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-5">
        <div class="bg-gradient-to-br from-green-500 to-emerald-600 rounded-2xl p-5 text-white shadow-lg">
            <i data-lucide="trophy" class="w-6 h-6 mb-2 opacity-80"></i>
            <p class="text-3xl font-black">{{ count($incentive_summary['eligible'] ?? []) }}</p>
            <p class="text-xs font-black uppercase tracking-widest opacity-80 mt-1">Eligible for Incentive</p>
        </div>
        <div class="bg-gradient-to-br from-red-500 to-rose-600 rounded-2xl p-5 text-white shadow-lg">
            <i data-lucide="x-circle" class="w-6 h-6 mb-2 opacity-80"></i>
            <p class="text-3xl font-black">{{ count($incentive_summary['ineligible'] ?? []) }}</p>
            <p class="text-xs font-black uppercase tracking-widest opacity-80 mt-1">Disqualified</p>
        </div>
        <div class="bg-gradient-to-br from-yellow-500 to-amber-600 rounded-2xl p-5 text-white shadow-lg">
            <i data-lucide="calendar-check" class="w-6 h-6 mb-2 opacity-80"></i>
            @php
                $now = now()->timezone('Asia/Manila');
                $firstSundayThisMonth = $now->copy()->startOfMonth();
                while($firstSundayThisMonth->dayOfWeek !== \Carbon\Carbon::SUNDAY) { $firstSundayThisMonth->addDay(); }
                
                if ($now->gt($firstSundayThisMonth->endOfDay())) {
                    // Already passed this month's, target next month
                    $targetDate = $now->copy()->addMonth()->startOfMonth();
                } else {
                    $targetDate = $now->copy()->startOfMonth();
                }

                while($targetDate->dayOfWeek !== \Carbon\Carbon::SUNDAY) { $targetDate->addDay(); }
            @endphp
            <p class="text-xl font-black">{{ $targetDate->format('M d, Y') }}</p>
            <p class="text-xs font-black uppercase tracking-widest opacity-80 mt-1">Next Payout Sunday</p>
        </div>
    </div>

    {{-- Eligible Drivers --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mb-5">
        <div class="px-5 py-4 border-b bg-green-50/50 flex items-center gap-2">
            <i data-lucide="check-circle" class="w-4 h-4 text-green-600"></i>
            <h3 class="font-black text-sm text-gray-800 uppercase tracking-widest">Eligible Drivers ({{ count($incentive_summary['eligible'] ?? []) }})</h3>
        </div>
        <table class="min-w-full divide-y divide-gray-50">
            <thead class="bg-gray-50/80">
                <tr>
                    <th class="px-5 py-3 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Driver</th>
                    <th class="px-5 py-3 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Unit</th>
                    <th class="px-5 py-3 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Type</th>
                    <th class="px-5 py-3 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Valid Days</th>
                    <th class="px-5 py-3 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Violations</th>
                    <th class="px-5 py-3 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Next Payout</th>
                    <th class="px-5 py-3 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($incentive_summary['eligible'] as $d)
                <tr class="hover:bg-green-50/30 transition-colors">
                    <td class="px-5 py-3.5"><span class="text-xs font-black text-gray-800">{{ $d['name'] }}</span></td>
                    <td class="px-5 py-3.5"><span class="text-xs font-black text-blue-600 uppercase">{{ $d['unit'] ?? '—' }}</span></td>
                    <td class="px-5 py-3.5">
                        <span class="text-[9px] font-black px-2 py-0.5 rounded-full {{ $d['driver_type'] === 'Dual Driver' ? 'bg-purple-100 text-purple-700 border border-purple-200' : 'bg-blue-100 text-blue-700 border border-blue-200' }}">{{ $d['driver_type'] }}</span>
                    </td>
                    <td class="px-5 py-3.5">
                        <div class="flex items-center gap-2">
                            <div class="w-20 h-1.5 bg-gray-100 rounded-full"><div class="h-1.5 bg-green-500 rounded-full" style="width:{{ min(100, ($d['valid_days']/20)*100) }}%"></div></div>
                            <span class="text-xs font-black text-green-600">{{ $d['valid_days'] }}/20</span>
                        </div>
                    </td>
                    <td class="px-5 py-3.5"><span class="text-xs font-black {{ $d['violations'] > 0 ? 'text-red-600' : 'text-green-600' }}">{{ $d['violations'] }}</span></td>
                    <td class="px-5 py-3.5"><span class="text-xs font-medium text-gray-600">{{ $d['next_payout'] }}</span></td>
                    <td class="px-5 py-3.5">
                        <form method="POST" action="{{ route('driver-behavior.release-incentive') }}" onsubmit="return confirm('Release incentive for {{ addslashes($d['name']) }}? This will reset their counter.')">
                            @csrf
                            <input type="hidden" name="driver_id" value="{{ $d['driver_id'] }}">
                            <button type="submit" class="px-3 py-1.5 bg-green-600 text-white text-[10px] font-black uppercase tracking-widest rounded-lg hover:bg-green-700 transition-all">
                                Release ✓
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="px-5 py-8 text-center text-xs text-gray-400 font-medium italic">No drivers eligible yet this period.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Ineligible Drivers --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-5 py-4 border-b bg-red-50/50 flex items-center gap-2">
            <i data-lucide="x-circle" class="w-4 h-4 text-red-500"></i>
            <h3 class="font-black text-sm text-gray-800 uppercase tracking-widest">Disqualified / Pending ({{ count($incentive_summary['ineligible'] ?? []) }})</h3>
        </div>
        <table class="min-w-full divide-y divide-gray-50">
            <thead class="bg-gray-50/80">
                <tr>
                    <th class="px-5 py-3 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Driver</th>
                    <th class="px-5 py-3 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Unit</th>
                    <th class="px-5 py-3 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Type</th>
                    <th class="px-5 py-3 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Valid Days</th>
                    <th class="px-5 py-3 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Violations</th>
                    <th class="px-5 py-3 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($incentive_summary['ineligible'] as $d)
                @php $reason = $d['violations'] > 0 ? 'Has Violations' : 'Insufficient Days'; @endphp
                <tr class="hover:bg-red-50/20 transition-colors">
                    <td class="px-5 py-3.5"><span class="text-xs font-bold text-gray-700">{{ $d['name'] }}</span></td>
                    <td class="px-5 py-3.5"><span class="text-xs font-black text-blue-600 uppercase">{{ $d['unit'] ?? '—' }}</span></td>
                    <td class="px-5 py-3.5">
                        <span class="text-[9px] font-black px-2 py-0.5 rounded-full {{ $d['driver_type'] === 'Dual Driver' ? 'bg-purple-100 text-purple-700 border border-purple-200' : 'bg-blue-100 text-blue-700 border border-blue-200' }}">{{ $d['driver_type'] }}</span>
                    </td>
                    <td class="px-5 py-3.5">
                        <div class="flex items-center gap-2">
                            <div class="w-20 h-1.5 bg-gray-100 rounded-full"><div class="h-1.5 bg-red-400 rounded-full" style="width:{{ min(100, ($d['valid_days']/20)*100) }}%"></div></div>
                            <span class="text-xs font-black text-red-500">{{ $d['valid_days'] }}/20</span>
                        </div>
                    </td>
                    <td class="px-5 py-3.5"><span class="text-xs font-black {{ $d['violations'] > 0 ? 'text-red-600' : 'text-gray-400' }}">{{ $d['violations'] }}</span></td>
                    <td class="px-5 py-3.5">
                        <span class="text-[9px] font-black px-2 py-0.5 rounded-full bg-red-100 text-red-700 border border-red-200">{{ $reason }}</span>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-5 py-8 text-center text-xs text-gray-400 font-medium italic">All drivers are eligible! 🎉</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- ════════════════════════════════════════
     TAB 3: DRIVER PROFILES
     ════════════════════════════════════════ --}}
<div id="tab-profiles" class="tab-content {{ ($tab ?? '') === 'profiles' ? '' : 'hidden' }}">
    <div class="mb-4">
        <input type="text" id="profileSearch" placeholder="Search driver name..."
            class="w-full md:w-80 px-4 py-2.5 bg-white border border-gray-200 rounded-xl text-sm font-medium focus:ring-2 focus:ring-yellow-500 focus:outline-none shadow-sm"
            onkeyup="filterProfiles(this.value)">
    </div>

    <div id="profileGrid" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
        @foreach($driver_profiles as $profile)
        @php
            $inc = $profile['incentive'];
            $eligible = $inc['eligible'];
        @endphp
        <div class="profile-card bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-all cursor-pointer" data-name="{{ strtolower($profile['name']) }}" onclick="openDriverDetails({{ $profile['id'] }})">
            {{-- Card Header --}}
            <div class="p-5 border-b border-gray-50 flex items-center gap-3 {{ $eligible ? 'bg-gradient-to-r from-green-50 to-emerald-50' : 'bg-gray-50/50' }}">
                <div class="w-11 h-11 rounded-xl {{ $eligible ? 'bg-green-500' : 'bg-gray-300' }} flex items-center justify-center text-white font-black text-lg shadow-sm flex-shrink-0">
                    {{ strtoupper(substr($profile['name'], 0, 1)) }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="font-black text-sm text-gray-800 truncate">{{ $profile['name'] }}</p>
                    <p class="text-[10px] font-bold text-blue-600 uppercase">{{ $profile['unit'] ?? 'No Unit Assigned' }}</p>
                </div>
                <div>
                    @if($eligible)
                        <span class="text-[9px] font-black px-2 py-1 bg-green-500 text-white rounded-xl shadow-sm">✓ ELIGIBLE</span>
                    @else
                        <span class="text-[9px] font-black px-2 py-1 bg-red-100 text-red-600 rounded-xl border border-red-200">✗ NOT YET</span>
                    @endif
                </div>
            </div>

            {{-- Stats Grid --}}
            <div class="grid grid-cols-3 divide-x divide-gray-50 border-b border-gray-50">
                <div class="p-3 text-center">
                    <p class="text-lg font-black text-gray-800">{{ $profile['incidents'] }}</p>
                    <p class="text-[9px] font-black text-gray-400 uppercase tracking-wider">Incidents</p>
                </div>
                <div class="p-3 text-center">
                    <p class="text-lg font-black text-gray-800">{{ $profile['boundaries'] }}</p>
                    <p class="text-[9px] font-black text-gray-400 uppercase tracking-wider">Shifts</p>
                </div>
                <div class="p-3 text-center">
                    <p class="text-lg font-black {{ $profile['charges'] > 0 ? 'text-red-600' : 'text-green-600' }}">₱{{ number_format($profile['charges'], 0) }}</p>
                    <p class="text-[9px] font-black text-gray-400 uppercase tracking-wider">Charges</p>
                </div>
            </div>

            {{-- Incentive Progress --}}
            <div class="p-4">
                <div class="flex justify-between items-center mb-1.5">
                    <span class="text-[10px] font-black text-gray-500 uppercase tracking-widest">{{ $inc['driver_type'] }}</span>
                    <span class="text-[10px] font-bold text-gray-500">{{ $inc['valid_days'] }}/{{ $inc['required_days'] }} valid days</span>
                </div>
                <div class="w-full h-2 bg-gray-100 rounded-full overflow-hidden">
                    <div class="h-2 rounded-full transition-all {{ $eligible ? 'bg-green-500' : 'bg-yellow-400' }}"
                        style="width: {{ min(100, ($inc['valid_days'] / $inc['required_days']) * 100) }}%"></div>
                </div>
                <div class="flex justify-between items-center mt-2">
                    <span class="text-[10px] text-gray-400">{{ $inc['violations'] }} violation(s)</span>
                    <span class="text-[10px] font-bold text-gray-500">Next: {{ $inc['next_payout_date'] }}</span>
                </div>
                @if($profile['total_debt'] > 0)
                <div class="mt-2 flex items-center gap-1.5 text-[10px] font-bold text-red-600 bg-red-50 px-2 py-1 rounded-lg border border-red-100">
                    <i data-lucide="alert-circle" class="w-3 h-3 text-red-500"></i> Pending Debt: ₱{{ number_format($profile['total_debt'], 2) }}
                </div>
                @endif
                @if($profile['shortages'] > 0)
                <div class="mt-2 flex items-center gap-1.5 text-[10px] font-bold text-orange-600">
                    <i data-lucide="trending-down" class="w-3 h-3"></i> Total Shortage: ₱{{ number_format($profile['shortages'], 2) }}
                </div>
                @endif
            </div>
        </div>
        @endforeach
    </div>
</div>

{{-- ════════════════════════════════════════
     RECORD INCIDENT MODAL (PREMIUM & FUNCTIONAL)
     ════════════════════════════════════════ --}}
<div id="incidentModal" class="fixed inset-0 bg-black/60 backdrop-blur-md hidden z-[100] flex items-center justify-center p-4">
    <div class="w-full max-w-2xl bg-white rounded-3xl shadow-2xl overflow-hidden flex flex-col max-h-[90vh] animate-in fade-in zoom-in duration-300">
        {{-- Modal Header --}}
        <div class="px-8 py-6 bg-gray-900 text-white flex items-center justify-between shadow-lg z-10">
            <div>
                <h3 class="text-xl font-black tracking-tight leading-none">Record Driver Incident</h3>
                <p class="text-[10px] text-gray-400 font-bold mt-2 uppercase tracking-[0.2em]">Deployment & Damage Assessment System</p>
            </div>
            <button onclick="closeIncidentModal()" class="p-2.5 rounded-2xl bg-white/10 hover:bg-white/20 transition-all active:scale-95 border border-white/10">
                <i data-lucide="x" class="w-5 h-5 text-white"></i>
            </button>
        </div>

        <form method="POST" action="{{ route('driver-behavior.store') }}" id="incidentForm" class="flex flex-col flex-1 overflow-hidden">
            @csrf
            
            {{-- Scrollable Body --}}
            <div class="flex-1 overflow-y-auto custom-scroll px-8 py-8 space-y-8 bg-gray-50/30">
                
                {{-- Section: Basic Info --}}
                <div class="bg-white p-6 rounded-3xl border border-gray-100 shadow-sm space-y-5">
                    <div class="flex items-center gap-3 mb-1">
                        <div class="w-1.5 h-5 bg-yellow-500 rounded-full"></div>
                        <p class="text-[11px] font-black text-gray-400 uppercase tracking-widest">Incident Registry</p>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-5">
                        <div class="relative">
                            <label class="block text-[10px] font-black text-gray-500 uppercase mb-2 ml-1">Fleet Unit / Plate Number *</label>
                            <input type="text" id="unitSearchDisplay" placeholder="Type Plate #..." required
                                class="w-full px-4 py-3.5 bg-gray-50 border border-gray-200 rounded-2xl text-sm font-bold focus:ring-4 focus:ring-yellow-500/10 focus:border-yellow-500 focus:outline-none transition-all placeholder:text-gray-300" autocomplete="off">
                            <input type="hidden" name="unit_id" id="incidentUnitId" required>
                            <div id="unitSearchDropdown" class="search-dropdown hidden">
                                @foreach($units as $u)
                                    <div class="search-option unit-search-option" 
                                        data-id="{{ $u->id }}" 
                                        data-name="{{ $u->plate_number }}"
                                        data-driver-id="{{ $u->driver_id }}">
                                        <div class="font-black text-xs text-gray-900">{{ $u->plate_number }}</div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <div class="relative">
                            <label class="block text-[10px] font-black text-gray-500 uppercase mb-2 ml-1">Assignee Driver *</label>
                            <input type="text" id="driverSearchDisplay" placeholder="Search Driver..." required
                                class="w-full px-4 py-3.5 bg-gray-50 border border-gray-200 rounded-2xl text-sm font-bold focus:ring-4 focus:ring-yellow-500/10 focus:border-yellow-500 focus:outline-none transition-all placeholder:text-gray-300" autocomplete="off">
                            <input type="hidden" name="driver_id" id="incidentDriverId" required>
                            <div id="driverSearchDropdown" class="search-dropdown hidden">
                                @foreach($drivers as $d)
                                    <div class="search-option driver-search-option" data-id="{{ $d->id }}" data-name="{{ $d->full_name }}">
                                        <div class="font-black text-xs text-gray-900">{{ $d->full_name }}</div>
                                        <div class="text-[9px] text-gray-400 font-black uppercase tracking-tighter mt-1">{{ $d->current_plate ?? 'Floating / Unassigned' }}</div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-5">
                        <div class="col-span-2 sm:col-span-1">
                            <label class="block text-[10px] font-black text-gray-500 uppercase mb-2 ml-1">Incident Classification *</label>
                            <select name="incident_type" required id="incidentTypeSelect" onchange="handleTypeChange(this.value)"
                                class="w-full px-4 py-3.5 bg-gray-50 border border-gray-100 rounded-2xl text-sm font-bold focus:ring-4 focus:ring-yellow-500/10 focus:border-yellow-500 focus:outline-none transition-all">
                                <option value="">Select Type</option>
                                @foreach(App\Http\Controllers\DriverBehaviorController::$incidentTypes as $type => $meta)
                                    <option value="{{ $type }}">{{ $type }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-span-2 sm:col-span-1">
                            <label class="block text-[10px] font-black text-gray-500 uppercase mb-2 ml-1">Severity / Priority *</label>
                            <select name="severity" required id="severitySelect"
                                class="w-full px-4 py-3.5 bg-gray-50 border border-gray-100 rounded-2xl text-sm font-bold focus:ring-4 focus:ring-yellow-500/10 focus:border-yellow-500 focus:outline-none transition-all">
                                <option value="low">Low</option>
                                <option value="medium" selected>Medium</option>
                                <option value="high">High</option>
                                <option value="critical">Critical</option>
                            </select>
                        </div>
                        <div class="col-span-2 sm:col-span-1">
                            <label class="block text-[10px] font-black text-gray-500 uppercase mb-2 ml-1">Occurrence Date *</label>
                            <div class="relative">
                                <input type="date" name="incident_date" value="{{ date('Y-m-d') }}" required
                                    class="w-full px-4 py-3.5 bg-gray-50 border border-gray-100 rounded-2xl text-sm font-bold focus:ring-4 focus:ring-yellow-500/10 focus:border-yellow-500 focus:outline-none transition-all">
                                <i data-lucide="calendar" class="w-4 h-4 absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none"></i>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Section: Narrative --}}
                <div class="bg-white p-6 rounded-3xl border border-gray-100 shadow-sm space-y-4">
                    <div class="flex items-center gap-3 mb-1">
                        <div class="w-1.5 h-5 bg-orange-500 rounded-full"></div>
                        <p class="text-[11px] font-black text-gray-400 uppercase tracking-widest">Incident Narrative</p>
                    </div>
                    <textarea name="description" required rows="3" placeholder="Provide a detailed report of the incident..."
                        class="w-full px-5 py-4 bg-gray-50 border border-gray-200 rounded-2xl text-sm font-medium focus:ring-4 focus:ring-orange-500/5 focus:border-orange-500 focus:outline-none resize-none transition-all placeholder:text-gray-300"></textarea>
                </div>

                {{-- Section: Financial Charges (MAINTENANCE STYLE) --}}
                <div class="p-8 bg-purple-50/50 rounded-[2.5rem] border border-purple-100 space-y-8 ring-1 ring-purple-100/50">
                    <div class="flex items-center gap-4">
                        <div class="p-3 bg-purple-600 rounded-2xl shadow-xl shadow-purple-600/20">
                            <i data-lucide="calculator" class="w-5 h-5 text-white"></i>
                        </div>
                        <div>
                            <p class="text-[11px] font-black text-purple-700 uppercase tracking-[0.2em]">Damage & Cost Assessment</p>
                            <p class="text-[9px] text-purple-400 font-bold uppercase mt-1">Itemized Repair Tracking</p>
                        </div>
                    </div>

                    {{-- Conditional Accident Details (Shared) --}}
                    <div id="accidentDetailsSection" class="hidden space-y-5 animate-in slide-in-from-top duration-300">
                        <div id="partiesContainer" class="space-y-3">
                            {{-- Parties Injected Here --}}
                        </div>
                        <button type="button" onclick="addPartyRow()" class="w-full py-4 border-2 border-dashed border-purple-200 text-purple-400 hover:text-purple-600 text-[10px] font-black uppercase rounded-2xl hover:bg-white hover:border-purple-400 transition-all group flex items-center justify-center gap-3">
                            <i data-lucide="user-plus" class="w-4 h-4 transition-transform group-hover:scale-110"></i>
                            Record Involved Third Party
                        </button>
                    </div>

                    {{-- 1. SPARE PARTS SELECTION (MAINTENANCE STYLE) --}}
                    <div class="bg-white p-6 rounded-3xl border border-purple-100 shadow-sm space-y-4">
                        <div class="flex justify-between items-center">
                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Spare Parts Selection</label>
                            <button type="button" onclick="openQuickAddPart()" class="text-[9px] font-black text-purple-600 hover:text-purple-800 uppercase tracking-widest">+ New Part</button>
                        </div>
                        
                        <div class="relative">
                            <i data-lucide="search" class="w-4 h-4 absolute left-4 top-1/2 -translate-y-1/2 text-gray-300"></i>
                            <input type="text" id="incidentPartSearch" placeholder="Type to search parts..."
                                class="w-full pl-11 pr-4 py-3.5 bg-gray-50 border border-gray-100 rounded-2xl text-sm font-bold focus:ring-4 focus:ring-purple-500/10 focus:border-purple-400 focus:outline-none transition-all placeholder:text-gray-300">
                            
                            <div id="incidentPartDropdown" class="search-dropdown hidden max-h-60 overflow-y-auto">
                                @foreach($spare_parts as $p)
                                    @php $isAvailable = ($p->stock_quantity ?? 0) > 0; @endphp
                                    <div class="search-option part-search-option group {{ !$isAvailable ? 'opacity-60 cursor-not-allowed bg-gray-50' : '' }}" 
                                        data-id="{{ $p->id }}" 
                                        data-name="{{ $p->name }}" 
                                        data-price="{{ $p->price }}"
                                        data-available="{{ $isAvailable ? '1' : '0' }}">
                                        <div class="flex justify-between items-start w-full">
                                            <div>
                                                <div class="font-black text-xs {{ $isAvailable ? 'text-gray-900' : 'text-gray-400' }}">{{ $p->name }}</div>
                                                <div class="flex items-center gap-2 mt-1">
                                                    <span class="text-[9px] font-black px-1.5 py-0.5 rounded {{ $isAvailable ? 'bg-green-100 text-green-700' : 'bg-red-500 text-white shadow-sm' }}">
                                                        {{ $isAvailable ? 'STOCK: ' . $p->stock_quantity : 'UNAVAILABLE' }}
                                                    </span>
                                                    <span class="text-[9px] text-gray-400 font-bold uppercase tracking-tighter italic">Supplier: {{ $p->supplier ?? 'Unknown' }}</span>
                                                </div>
                                            </div>
                                            <div class="text-[10px] font-black text-purple-600">₱{{ number_format($p->price, 2) }}</div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div id="partsCartContainer" class="space-y-3 min-h-[50px]">
                            <div class="text-center py-4 text-gray-300 italic text-[10px] uppercase tracking-widest">No parts selected yet.</div>
                        </div>

                        <div class="flex justify-between items-center pt-3 border-t border-gray-50">
                            <span class="text-[9px] font-black text-gray-400 uppercase">Total Parts Value:</span>
                            <span id="partsTotalLabel" class="text-xs font-black text-gray-900 tracking-tight">₱0.00</span>
                        </div>
                    </div>

                    {{-- 2. ADDITIONAL SERVICE / OTHER COSTS (MAINTENANCE STYLE) --}}
                    <div class="bg-white p-6 rounded-3xl border border-purple-100 shadow-sm space-y-4">
                        <div class="flex justify-between items-center">
                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Additional Service / Other Costs</label>
                            <button type="button" onclick="addServiceRow()" class="px-4 py-2 bg-orange-50 text-orange-600 text-[9px] font-black uppercase tracking-widest rounded-xl hover:bg-orange-100 transition-all flex items-center gap-2">
                                <i data-lucide="plus-circle" class="w-3 h-3"></i> Add Service
                            </button>
                        </div>
                        
                        <div id="servicesContainer" class="space-y-3 min-h-[50px]">
                            {{-- Rows injected by JS --}}
                            <div class="text-center py-4 text-gray-300 italic text-[10px] uppercase tracking-widest">No services recorded.</div>
                        </div>
                    </div>

                    {{-- Settlement & Fault Section --}}
                    <div class="space-y-4">
                        <div id="thirdPartyCostRow" class="hidden animate-in fade-in duration-300">
                             <div class="bg-white/40 p-5 rounded-3xl border border-purple-100/50">
                                <label class="block text-[10px] font-black text-purple-700 uppercase mb-2.5 ml-1">Third Party Damage Settlement (₱)</label>
                                <input type="number" name="third_party_damage_cost" step="0.01" min="0" id="thirdDamage" oninput="computeTotal()"
                                    placeholder="0.00" class="w-full px-5 py-3.5 bg-white border border-purple-100 rounded-2xl text-sm font-black text-purple-600 focus:ring-4 focus:ring-purple-500/10 focus:border-purple-500 focus:outline-none transition-all placeholder:text-purple-200">
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-5">
                            {{-- Grand Total Cost (Green - Maintenance Style) --}}
                            <div class="bg-green-600 p-6 rounded-[2.5rem] shadow-xl shadow-green-600/20 relative overflow-hidden group">
                                <div class="absolute right-[-10px] top-[-10px] opacity-10">
                                    <i data-lucide="calculator" class="w-16 h-16 text-white"></i>
                                </div>
                                <p class="text-[10px] font-black text-white/60 uppercase tracking-widest mb-2">Grand Total Cost</p>
                                <p class="text-3xl font-black text-white tracking-tighter" id="totalDamageLabel">₱0.00</p>
                                <p class="text-[8px] text-green-100/50 font-bold uppercase mt-2">Sum of all parts & services</p>
                            </div>

                            {{-- Driver Liability (Red - Premium Style) --}}
                            <div class="bg-red-600 p-6 rounded-[2.5rem] shadow-xl shadow-red-600/20 relative overflow-hidden group">
                                <div class="absolute right-[-10px] top-[-10px] opacity-10">
                                    <i data-lucide="alert-triangle" class="w-16 h-16 text-white"></i>
                                </div>
                                <p class="text-[10px] font-black text-white/60 uppercase tracking-widest mb-2 font-sans">Total Driver Liability</p>
                                <p class="text-3xl font-black text-white tracking-tighter" id="driverChargeLabel">₱0.00</p>
                                <input type="hidden" name="total_charge_to_driver" id="totalChargeValue" value="0">
                                <p class="text-[8px] text-red-100/50 font-bold uppercase mt-2">Deductible Balance</p>
                            </div>
                        </div>

                        {{-- Liability Acknowledgement --}}
                        <label class="flex items-center gap-4 cursor-pointer p-6 bg-white rounded-[2rem] border border-red-100 hover:bg-red-50 hover:border-red-200 transition-all group select-none shadow-sm">
                            <div class="relative flex items-center justify-center">
                                <input type="checkbox" name="is_driver_fault" id="faultCheck" value="1" onchange="computeTotal()"
                                    class="w-7 h-7 accent-red-600 rounded-2xl cursor-pointer transition-transform group-hover:scale-110 border-2 border-red-200">
                            </div>
                            <div>
                                <p class="text-xs font-black text-gray-800 uppercase tracking-wider">Driver is at Fault</p>
                                <p class="text-[9px] text-red-500 font-bold uppercase mt-1 tracking-widest leading-relaxed">Checking this will include Third-Party costs in Driver's balance.</p>
                            </div>
                        </label>
                    </div>
                </div>

                {{-- Section: Cause (Conditional) --}}
                <div id="causeSection" class="hidden space-y-4 bg-orange-50/30 p-6 rounded-3xl border border-orange-100">
                    <div class="flex items-center gap-3">
                        <i data-lucide="alert-circle" class="w-4 h-4 text-orange-600"></i>
                        <label class="block text-[11px] font-black text-orange-700 uppercase tracking-widest">Root Cause Analysis</label>
                    </div>
                    <input type="text" name="cause_of_incident" id="causeInput" placeholder="e.g. Brake failure, Sleepy, Reckless..."
                        class="w-full px-5 py-4 bg-white border border-orange-100 rounded-2xl text-sm font-bold text-orange-900 focus:ring-4 focus:ring-orange-500/10 focus:border-orange-400 focus:outline-none transition-all placeholder:text-orange-200">
                </div>
            </div>

            <div class="px-8 py-7 bg-white border-t border-gray-100 flex gap-4 shadow-[0_-10px_20px_rgba(0,0,0,0.02)]">
                <button type="submit" class="flex-1 py-4.5 bg-gray-900 text-white font-black text-xs uppercase tracking-[0.2em] rounded-[1.25rem] hover:bg-gray-800 shadow-2xl shadow-gray-900/30 transition-all active:scale-[0.98] flex items-center justify-center gap-3">
                     <i data-lucide="save" class="w-4 h-4"></i> Commit Incident Record
                </button>
                <button type="button" onclick="closeIncidentModal()" class="px-8 py-4.5 bg-white border border-gray-200 text-gray-500 font-black text-xs uppercase tracking-[0.2em] rounded-[1.25rem] hover:bg-gray-50 hover:text-gray-800 transition-all active:scale-[0.98]">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ════════════════════════════════════════
     EDIT INCIDENT MODAL
     ════════════════════════════════════════ --}}
<div id="editIncidentModal" class="fixed inset-0 bg-black/60 backdrop-blur-md hidden z-[101] flex items-center justify-center p-4">
    <div class="w-full max-w-xl bg-white rounded-3xl shadow-2xl overflow-hidden flex flex-col max-h-[90vh] animate-in fade-in zoom-in duration-300">
        {{-- Modal Header --}}
        <div class="px-8 py-6 bg-blue-600 text-white flex items-center justify-between shadow-lg z-10">
            <div>
                <h3 class="text-xl font-black tracking-tight leading-none">Edit Incident Record</h3>
                <p class="text-[10px] text-blue-100 font-bold mt-2 uppercase tracking-[0.2em]">Update incident details & charges</p>
            </div>
            <button onclick="closeEditIncidentModal()" class="p-2.5 rounded-2xl bg-white/10 hover:bg-white/20 transition-all active:scale-95 border border-white/10">
                <i data-lucide="x" class="w-5 h-5 text-white"></i>
            </button>
        </div>

        <form method="POST" id="editIncidentForm" class="flex flex-col flex-1 overflow-hidden">
            @csrf
            @method('PUT')
            
            <div class="flex-1 overflow-y-auto custom-scroll px-8 py-8 space-y-6">
                {{-- Driver & Unit Info (Read Only) --}}
                <div class="bg-gray-50 p-4 rounded-2xl border border-gray-100 flex items-center justify-between">
                    <div>
                        <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1">Driver & Unit</p>
                        <p id="editInfoDisplay" class="text-sm font-black text-gray-800 italic uppercase">Loading...</p>
                    </div>
                    <i data-lucide="info" class="w-5 h-5 text-blue-500 opacity-30"></i>
                </div>

                <div class="grid grid-cols-2 gap-5">
                    <div>
                        <label class="block text-[10px] font-black text-gray-500 uppercase mb-2 ml-1">Classification</label>
                        <select name="incident_type" id="edit_incident_type" required
                            class="w-full px-4 py-3.5 bg-gray-50 border border-gray-100 rounded-2xl text-sm font-bold focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 focus:outline-none transition-all">
                            @foreach(App\Http\Controllers\DriverBehaviorController::$incidentTypes as $type => $meta)
                                <option value="{{ $type }}">{{ $type }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-gray-500 uppercase mb-2 ml-1">Severity</label>
                        <select name="severity" id="edit_severity" required
                            class="w-full px-4 py-3.5 bg-gray-50 border border-gray-100 rounded-2xl text-sm font-bold focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 focus:outline-none transition-all">
                            <option value="low">Low</option>
                            <option value="medium">Medium</option>
                            <option value="high">High</option>
                            <option value="critical">Critical</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-[10px] font-black text-gray-500 uppercase mb-2 ml-1">Occurrence Date</label>
                    <input type="date" name="incident_date" id="edit_incident_date" required
                        class="w-full px-4 py-3.5 bg-gray-50 border border-gray-100 rounded-2xl text-sm font-bold focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 focus:outline-none transition-all">
                </div>

                <div>
                    <label class="block text-[10px] font-black text-gray-500 uppercase mb-2 ml-1">Narrative Description</label>
                    <textarea name="description" id="edit_description" required rows="3"
                        class="w-full px-4 py-3.5 bg-gray-50 border border-gray-100 rounded-2xl text-sm font-bold focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 focus:outline-none transition-all resize-none"></textarea>
                </div>

                <div class="grid grid-cols-2 gap-5 pt-2">
                    <div>
                        <label class="block text-[10px] font-black text-gray-500 uppercase mb-2 ml-1">Total Charge (₱)</label>
                        <div class="relative">
                            <input type="number" step="0.01" name="total_charge_to_driver" id="edit_total_charge" required
                                class="w-full pl-9 pr-4 py-3.5 bg-gray-50 border border-gray-100 rounded-2xl text-sm font-black text-red-600 focus:ring-4 focus:ring-red-500/10 focus:border-red-500 focus:outline-none transition-all">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 font-bold">₱</span>
                        </div>
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-gray-500 uppercase mb-2 ml-1">Driver Liability</label>
                        <label class="flex items-center gap-3 p-3.5 bg-gray-50 border border-gray-100 rounded-2xl cursor-pointer hover:bg-gray-100 transition-all">
                            <input type="checkbox" name="is_driver_fault" id="edit_is_driver_fault" value="1" class="w-5 h-5 rounded-lg border-gray-300 text-red-600 focus:ring-red-500">
                            <span class="text-xs font-black text-gray-700 uppercase">At Fault</span>
                        </label>
                    </div>
                </div>
            </div>

            {{-- Modal Footer --}}
            <div class="px-8 py-6 bg-gray-50 border-t border-gray-100 flex gap-3">
                <button type="button" id="editModalArchiveBtn"
                    class="p-4 bg-red-50 text-red-500 font-black text-xs uppercase tracking-widest rounded-2xl border border-red-100 hover:bg-red-100 transition-all active:scale-95"
                    title="Archive Record">
                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                </button>
                <button type="button" onclick="closeEditIncidentModal()"
                    class="px-6 py-4 bg-white text-gray-500 font-black text-xs uppercase tracking-widest rounded-2xl border border-gray-200 hover:bg-gray-100 transition-all active:scale-95">
                    Cancel
                </button>
                <button type="submit"
                    class="flex-1 px-6 py-4 bg-blue-600 text-white font-black text-xs uppercase tracking-widest rounded-2xl shadow-lg shadow-blue-200 hover:bg-blue-700 hover:shadow-blue-300 transition-all active:scale-95 flex items-center justify-center gap-2">
                    <i data-lucide="check-circle" class="w-4 h-4"></i>
                    Update Record
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Quick Add Part Modal (Same as Maintenance) --}}
<div id="quickAddPartModal" class="hidden fixed inset-0 z-[9999] flex items-center justify-center bg-black bg-opacity-40 backdrop-blur-sm transition-all">
    <div class="bg-white rounded-3xl shadow-[0_0_100px_rgba(0,0,0,0.5)] w-full max-w-sm p-8 animate-in zoom-in duration-200">
        <div class="flex items-center gap-3 mb-6">
            <div class="p-2 bg-blue-100 rounded-xl">
                <i data-lucide="package-plus" class="w-5 h-5 text-blue-600"></i>
            </div>
            <h4 class="text-lg font-black text-gray-900 uppercase tracking-tight">Quick Add Part</h4>
        </div>
        
        <div class="space-y-5">
            <input type="hidden" id="quickPartId">
            <div>
                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Part Name / Service Description</label>
                <input type="text" id="quickPartName" placeholder="e.g. Brake Pads, Side Mirror..."
                    class="w-full px-5 py-3.5 bg-gray-50 border border-gray-100 rounded-2xl text-sm font-black focus:ring-4 focus:ring-blue-500/10 focus:border-blue-400 focus:outline-none transition-all placeholder:text-gray-300">
            </div>
            <div>
                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Standard Price (₱)</label>
                <input type="number" id="quickPartPrice" placeholder="0.00"
                    class="w-full px-5 py-3.5 bg-gray-50 border border-gray-100 rounded-2xl text-sm font-black focus:ring-4 focus:ring-blue-500/10 focus:border-blue-400 focus:outline-none transition-all placeholder:text-gray-300">
            </div>
            
            <div class="flex gap-3 pt-3">
                <button type="button" onclick="window.saveQuickPart()" class="flex-1 py-4 bg-blue-600 text-white rounded-2xl text-[10px] font-black uppercase tracking-widest hover:bg-blue-700 shadow-lg shadow-blue-600/20 transition-all active:scale-95">Save to Catalog</button>
                <button type="button" onclick="window.closeQuickAddPart()" class="flex-1 py-4 bg-gray-100 text-gray-500 rounded-2xl text-[10px] font-black uppercase tracking-widest hover:bg-gray-200 transition-all active:scale-95">Cancel</button>
            </div>
        </div>
    </div>
</div>

@include('partials._driver_details_modal')

@endsection

@push('scripts')
<script>
@include('partials._driver_details_scripts')
// ─── Global Scoping & Initialization ───
window.switchTab = function(name) {
    document.querySelectorAll('.tab-content').forEach(t => t.classList.add('hidden'));
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    document.getElementById('tab-' + name)?.classList.remove('hidden');
    document.getElementById('tab-btn-' + name)?.classList.add('active');
    if(window.lucide) lucide.createIcons();
};

window.openIncidentModal = function() {
    const modal = document.getElementById('incidentModal');
    if (!modal) return;
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    if(window.lucide) lucide.createIcons();
    
    // Always re-init to ensure fresh state
    initializeSearchDropdowns();
    initPartSearch();
};

window.closeIncidentModal = function() {
    const modal = document.getElementById('incidentModal');
    if (!modal) return;
    modal.classList.add('hidden');
    modal.classList.remove('flex');
};

window.openQuickAddPart = function() {
    const modal = document.getElementById('quickAddPartModal');
    if (!modal) return;
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    if(window.lucide) lucide.createIcons();
};

window.closeQuickAddPart = function() {
    const modal = document.getElementById('quickAddPartModal');
    if (!modal) return;
    modal.classList.add('hidden');
    modal.classList.remove('flex');
    document.getElementById('quickPartName').value = '';
    document.getElementById('quickPartPrice').value = '';
};

// ─── Constants & State ───
let partsCatalog = @json($spare_parts ?? []);
let incidentPartsCart = [];
let incidentServices = [];
let partyIndex = 0;

// ─── Searchable Dropdowns (Unit/Driver) ───
function initializeSearchDropdowns() {
    const searchConfig = [
        { display: 'unitSearchDisplay', hidden: 'incidentUnitId', dropdown: 'unitSearchDropdown', options: 'unit-search-option' },
        { display: 'driverSearchDisplay', hidden: 'incidentDriverId', dropdown: 'driverSearchDropdown', options: 'driver-search-option' }
    ];

    searchConfig.forEach(({ display, hidden, dropdown, options }) => {
        const dInput = document.getElementById(display);
        const hInput = document.getElementById(hidden);
        const drop = document.getElementById(dropdown);
        if (!dInput || !drop) return;

        drop.onmousedown = (e) => {
            const opt = e.target.closest('.' + options);
            if (!opt) return;
            
            hInput.value = opt.dataset.id;
            dInput.value = opt.dataset.name;
            drop.classList.add('hidden');
            drop.classList.remove('flex');

            // Robust Unit -> Driver Auto-fill
            if (options === 'unit-search-option') {
                const driverHidden = document.getElementById('incidentDriverId');
                const driverDisplay = document.getElementById('driverSearchDisplay');
                const drvId = opt.dataset.driverId;

                if (drvId && drvId !== 'null' && drvId !== '' && drvId !== '0') {
                    const driverOpt = document.querySelector(`.driver-search-option[data-id="${drvId}"]`);
                    if (driverOpt && driverHidden && driverDisplay) {
                        driverHidden.value = drvId;
                        driverDisplay.value = driverOpt.dataset.name;
                        driverDisplay.dispatchEvent(new Event('input'));
                    }
                } else {
                    if (driverHidden) driverHidden.value = '';
                    if (driverDisplay) driverDisplay.value = '';
                }
            }
        };

        dInput.onfocus = () => { filterDropdown(dInput, options); drop.classList.remove('hidden'); drop.classList.add('flex'); };
        dInput.oninput = () => { filterDropdown(dInput, options); drop.classList.remove('hidden'); drop.classList.add('flex'); };
        dInput.onblur = () => { setTimeout(() => { if (drop) { drop.classList.add('hidden'); drop.classList.remove('flex'); } }, 200); };
    });
}

function filterDropdown(input, optClass) {
    const q = input.value.toLowerCase().trim();
    document.querySelectorAll('.' + optClass).forEach(opt => {
        const text = opt.innerText.toLowerCase();
        opt.style.display = (!q || text.includes(q)) ? 'block' : 'none';
    });
}

// ─── Spare Parts & Catalog Management ───
window.saveQuickPart = async function() {
    const name = document.getElementById('quickPartName').value;
    const price = document.getElementById('quickPartPrice').value;
    if(!name || !price) return alert('Please fill in both name and price.');
    
    try {
        const res = await fetch("{{ route('spare-parts.store') }}", {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify({ name, price })
        });
        const result = await res.json();
        if(result.success) {
            partsCatalog.push(result.data);
            addPartToIncidentCart({
                id: result.data.id,
                name: result.data.name,
                price: parseFloat(result.data.price) || 0,
                qty: 1,
                isCharged: true
            });
            refreshPartSearchDropdown();
            window.closeQuickAddPart();
        }
    } catch(e) { alert('Failed to save part to catalog.'); }
};

function refreshPartSearchDropdown() {
    const dropdown = document.getElementById('incidentPartDropdown');
    if(!dropdown) return;
    dropdown.innerHTML = partsCatalog.map(p => {
        const isAvailable = (parseInt(p.stock_quantity) || 0) > 0;
        return `
            <div class="search-option part-search-option group ${!isAvailable ? 'opacity-60 cursor-not-allowed bg-gray-50' : ''}" 
                data-id="${p.id}" data-name="${p.name}" data-price="${p.price}" data-available="${isAvailable ? '1' : '0'}">
                <div class="flex justify-between items-start w-full">
                    <div>
                        <div class="font-black text-xs ${isAvailable ? 'text-gray-900' : 'text-gray-400'}">${p.name}</div>
                        <div class="flex items-center gap-2 mt-1">
                            <span class="text-[9px] font-black px-1.5 py-0.5 rounded ${isAvailable ? 'bg-green-100 text-green-700' : 'bg-red-500 text-white shadow-sm'}">
                                ${isAvailable ? 'STOCK: ' + p.stock_quantity : 'UNAVAILABLE'}
                            </span>
                            <span class="text-[9px] text-gray-400 font-bold uppercase tracking-tighter italic">Supplier: ${p.supplier || 'Unknown'}</span>
                        </div>
                    </div>
                    <div class="text-[10px] font-black text-purple-600">₱${parseFloat(p.price).toFixed(2)}</div>
                </div>
            </div>
        `;
    }).join('');
}

function initPartSearch() {
    const input = document.getElementById('incidentPartSearch');
    const dropdown = document.getElementById('incidentPartDropdown');
    if(!input || !dropdown) return;

    input.onfocus = () => { refreshPartSearchDropdown(); dropdown.classList.remove('hidden'); };
    input.oninput = () => { 
        const q = input.value.toLowerCase();
        dropdown.querySelectorAll('.part-search-option').forEach(opt => {
            opt.style.display = opt.dataset.name.toLowerCase().includes(q) ? 'block' : 'none';
        });
        dropdown.classList.remove('hidden');
    };
    dropdown.onmousedown = (e) => {
        const opt = e.target.closest('.part-search-option');
        if (!opt) return;
        
        // Anti-Unavailable Lock
        if (opt.dataset.available === '0') {
            e.preventDefault();
            return;
        }

        addPartToIncidentCart({ id: opt.dataset.id, name: opt.dataset.name, price: parseFloat(opt.dataset.price) || 0, qty: 1, isCharged: true });
        input.value = ''; dropdown.classList.add('hidden');
    };
    input.onblur = () => { setTimeout(() => dropdown.classList.add('hidden'), 200); };
}

function addPartToIncidentCart(part) {
    const existing = incidentPartsCart.find(p => p.id === part.id);
    if(existing) existing.qty++;
    else incidentPartsCart.push(part);
    refreshPartsCart();
}

function refreshPartsCart() {
    const container = document.getElementById('partsCartContainer');
    if(!container) return;
    if(incidentPartsCart.length === 0) {
        container.innerHTML = `<div class="text-center py-4 text-gray-300 italic text-[10px] uppercase tracking-widest">No parts selected yet.</div>`;
        document.getElementById('partsTotalLabel').textContent = '₱0.00';
        computeTotal(); return;
    }
    let partsTotal = 0;
    container.innerHTML = incidentPartsCart.map((p, i) => {
        const sub = p.price * p.qty; partsTotal += sub;
        return `<div class="flex items-center gap-4 bg-gray-50/50 p-4 rounded-2xl border border-gray-100 animate-in slide-in-from-right duration-200">
            <input type="hidden" name="parts[${i}][spare_part_id]" value="${p.id}">
            <input type="hidden" name="parts[${i}][unit_price]" value="${p.price}">
            <div class="flex-1"><p class="text-[10px] font-black text-gray-800 uppercase">${p.name}</p></div>
            <div class="w-16"><input type="number" name="parts[${i}][quantity]" value="${p.qty}" onchange="window.updatePartQty(${i}, this.value)" class="w-full text-center py-2 bg-white border border-gray-100 rounded-xl text-[10px] font-black"></div>
            <div class="w-24 text-right"><p class="text-[10px] font-black text-gray-900">₱${sub.toLocaleString()}</p></div>
            <div class="flex items-center"><label class="relative inline-flex items-center cursor-pointer"><input type="checkbox" name="parts[${i}][is_charged_to_driver]" value="1" ${p.isCharged ? 'checked' : ''} onchange="window.togglePartCharge(${i}, this.checked)" class="sr-only peer"><div class="w-8 h-4 bg-gray-200 rounded-full peer peer-checked:bg-red-500 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-3 after:w-3 after:transition-all peer-checked:after:translate-x-4"></div></label></div>
            <button type="button" onclick="window.removePartFromIncident(${i})" class="text-gray-300 hover:text-red-500"><i data-lucide="trash-2" class="w-4 h-4"></i></button></div>`;
    }).join('');
    document.getElementById('partsTotalLabel').innerText = '₱' + partsTotal.toLocaleString('en-PH', {minimumFractionDigits: 2});
    if(window.lucide) lucide.createIcons();
    computeTotal();
}

window.updatePartQty = (i, val) => { incidentPartsCart[i].qty = parseInt(val) || 1; refreshPartsCart(); };
window.togglePartCharge = (i, val) => { incidentPartsCart[i].isCharged = val; computeTotal(); };
window.removePartFromIncident = (i) => { incidentPartsCart.splice(i, 1); refreshPartsCart(); };

// ─── Service Costs Logic ───
window.addServiceRow = () => { incidentServices.push({ description: '', price: 0, isCharged: true }); refreshServices(); };
function refreshServices() {
    const container = document.getElementById('servicesContainer');
    if(!container || incidentServices.length === 0) {
        if(container) container.innerHTML = `<div class="text-center py-4 text-gray-300 italic text-[10px] uppercase tracking-widest">No services recorded.</div>`;
        computeTotal(); return;
    }
    const startIndex = incidentPartsCart.length;
    container.innerHTML = incidentServices.map((s, i) => {
        const fullIndex = startIndex + i;
        return `<div class="flex items-center gap-4 bg-white p-4.5 rounded-2xl border border-orange-100 shadow-sm animate-in zoom-in duration-200">
            <div class="flex-1"><p class="text-[8px] font-black text-orange-400 uppercase tracking-widest">Description</p><input type="text" name="parts[${fullIndex}][custom_part_name]" value="${s.description}" oninput="window.updateServiceDesc(${i}, this.value)" class="w-full text-xs font-bold border-none bg-transparent p-0 focus:ring-0"></div>
            <div class="w-24 border-l border-orange-50 pl-4"><p class="text-[8px] font-black text-orange-400 uppercase tracking-widest">Price</p><input type="number" name="parts[${fullIndex}][unit_price]" value="${s.price || ''}" oninput="window.updateServicePrice(${i}, this.value)" class="w-full text-xs font-black text-orange-600 border-none bg-transparent p-0 focus:ring-0"></div>
            <div class="flex items-center pt-3"><label class="relative inline-flex items-center cursor-pointer"><input type="checkbox" name="parts[${fullIndex}][is_charged_to_driver]" value="1" ${s.isCharged ? 'checked' : ''} onchange="window.toggleServiceCharge(${i}, this.checked)" class="sr-only peer"><div class="w-8 h-4 bg-gray-200 rounded-full peer peer-checked:bg-red-500 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-3 after:w-3 after:transition-all peer-checked:after:translate-x-4"></div></label></div>
            <button type="button" onclick="window.removeService(${i})" class="text-orange-200 hover:text-red-500 pt-3"><i data-lucide="x-circle" class="w-5 h-5"></i></button></div>`;
    }).join('');
    if(window.lucide) lucide.createIcons();
    computeTotal();
}
window.updateServiceDesc = (i, val) => { incidentServices[i].description = val; };
window.updateServicePrice = (i, val) => { incidentServices[i].price = parseFloat(val) || 0; computeTotal(); };
window.toggleServiceCharge = (i, val) => { incidentServices[i].isCharged = val; computeTotal(); };
window.removeService = (i) => { incidentServices.splice(i, 1); refreshServices(); };

// ─── Financial Calculations ───
function computeTotal() {
    let grandTotal = 0, driverCharge = 0;
    incidentPartsCart.forEach(p => { const sub = p.price * p.qty; grandTotal += sub; if (p.isCharged) driverCharge += sub; });
    incidentServices.forEach(s => { grandTotal += s.price; if (s.isCharged) driverCharge += s.price; });
    const tDamage = parseFloat(document.getElementById('thirdDamage')?.value) || 0;
    if (document.getElementById('faultCheck')?.checked) { grandTotal += tDamage; driverCharge += tDamage; } else { grandTotal += tDamage; }
    document.getElementById('totalDamageLabel').textContent = '₱' + grandTotal.toLocaleString('en-PH', {minimumFractionDigits: 2});
    document.getElementById('driverChargeLabel').textContent = '₱' + driverCharge.toLocaleString('en-PH', {minimumFractionDigits: 2});
    document.getElementById('totalChargeValue').value = driverCharge;
}
window.computeTotal = computeTotal;

// ─── Incident Manager (Edit/Archive Actions) ───
window.IncidentManager = {
    openEdit: async function(id) {
        try {
            const res = await fetch(`/api/incidents/${id}/details`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            const data = await res.json();
            if (data.error) throw new Error(data.error);

            // Populate Modal
            document.getElementById('edit_incident_type').value = data.incident_type;
            document.getElementById('edit_severity').value = data.severity;
            document.getElementById('edit_incident_date').value = data.incident_date || data.timestamp.split(' ')[0];
            document.getElementById('edit_description').value = data.description;
            document.getElementById('edit_total_charge').value = data.total_charge_to_driver;
            document.getElementById('edit_is_driver_fault').checked = !!data.is_driver_fault;
            document.getElementById('editInfoDisplay').textContent = `${data.driver_name} • ${data.plate_number}`;
            
            // Set Form action
            const form = document.getElementById('editIncidentForm');
            form.action = `/api/incidents/${id}/update`;
            
            // Set Archive button for this specific ID
            const archiveBtn = document.getElementById('editModalArchiveBtn');
            if (archiveBtn) {
                archiveBtn.onclick = () => this.archive(id);
            }

            // Show Modal
            const modal = document.getElementById('editIncidentModal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            if(window.lucide) lucide.createIcons();
        } catch (e) {
            console.error(e);
            alert('Failed to fetch incident details: ' + e.message);
        }
    },

    archive: async function(id) {
        if (!confirm('Are you sure you want to move this incident to Archive?')) return;
        try {
            const res = await fetch(`/api/incidents/${id}/archive`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            const result = await res.json();
            if (result.success) {
                window.location.reload();
            } else {
                alert(result.message || 'Failed to archive record.');
            }
        } catch (e) {
            console.error(e);
            alert('Error connecting to server.');
        }
    }
};

window.closeEditIncidentModal = function() {
    const modal = document.getElementById('editIncidentModal');
    if (modal) {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }
};

// Handle Edit Form Submission via AJAX
document.addEventListener('DOMContentLoaded', () => {
    const editForm = document.getElementById('editIncidentForm');
    if (editForm) {
        editForm.onsubmit = async (e) => {
            e.preventDefault();
            const formData = new FormData(editForm);
            
            // Manual check for checkbox because FormData might omit if unchecked or use "on"
            if (!formData.has('is_driver_fault')) {
                formData.append('is_driver_fault', '0');
            }

            try {
                const res = await fetch(editForm.action, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                });
                const result = await res.json();
                if (result.success) {
                    window.location.reload();
                } else {
                    alert(result.message || 'Failed to update record.');
                }
            } catch (e) {
                console.error(e);
                alert('Error updating record.');
            }
        };
    }
});
</script>
@endpush

