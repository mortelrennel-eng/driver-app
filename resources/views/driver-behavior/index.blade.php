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
    .cls-tab-btn.active {
        color: #111827;
        position: relative;
    }
    .cls-tab-btn.active::after {
        content: '';
        position: absolute;
        bottom: -17px;
        left: 0;
        right: 0;
        height: 3px;
        background: #eab308;
        border-radius: 99px;
    }
    
    #sa-toast {
        position: fixed; bottom: 1.5rem; left: 50%; transform: translateX(-50%) translateY(4rem);
        background: #1e293b; border: 1px solid #eab308; color: #ffffff;
        padding: .85rem 1.75rem; border-radius: 999px; font-size: .85rem; font-weight: 600;
        box-shadow: 0 12px 40px rgba(0,0,0,.6);
        z-index: 9999; transition: transform .4s cubic-bezier(.34,1.56,.64,1);
        max-width: 90vw; display: flex; align-items: center; gap: .75rem;
    }
    #sa-toast.show { transform: translateX(-50%) translateY(0); }
    #sa-toast.error { border-color: #ef4444; }

    /* ── Modal ── */
    .sa-modal-backdrop {
        position: fixed; inset: 0; background: rgba(0,0,0,.75); backdrop-filter: blur(4px);
        z-index: 9990; display: none; align-items: center; justify-content: center;
    }
    .sa-modal-backdrop.open { display: flex; }
    .sa-modal {
        background: #fff; border: 1px solid #e2e8f0; border-radius: 2rem;
        padding: 2rem; width: 100%; max-width: 520px; max-height: 90vh; overflow-y: auto;
        box-shadow: 0 24px 80px rgba(0,0,0,.7);
        animation: modal-in .25s ease;
    }
    @keyframes modal-in { from { opacity:0; transform:scale(.94) translateY(1rem); } to { opacity:1; transform:none; } }

    .btn-danger { background:#7f1d1d; color:#f87171; border:1px solid #991b1b; border-radius:.5rem; padding:.3rem .9rem; font-size:.72rem; font-weight:700; cursor:pointer; transition:all .2s; }
    .btn-ghost   { background:transparent; color:#64748b; border:1px solid #e2e8f0; border-radius:.5rem; padding:.3rem .9rem; font-size:.72rem; font-weight:700; cursor:pointer; transition:all .2s; }
    .btn-ghost:hover   { background:rgba(0,0,0,0.04); color:#1e293b; }
    .sa-input {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        color: #1e293b;
        border-radius: .6rem;
        padding: .5rem 1rem;
        font-size: .82rem;
        outline: none;
        transition: border-color .2s;
        width: 100%;
    }
    .sa-input:focus { border-color: #eab308; }
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
                    @foreach($classifications as $c)
                        <option value="{{ $c->name }}" {{ $type_filter === $c->name ? 'selected' : '' }}>{{ $c->name }}</option>
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
                    @php
                        $classificationsMapForUI = collect($classifications)->keyBy('name');
                    @endphp
                    @forelse($incidents as $inc)
                    @php
                        $sevColors = [
                            'critical' => 'bg-red-100 text-red-700 border-red-200',
                            'high'     => 'bg-orange-100 text-orange-700 border-orange-200',
                            'medium'   => 'bg-yellow-100 text-yellow-700 border-yellow-200',
                            'low'      => 'bg-blue-100 text-blue-700 border-blue-200',
                        ];
                        $typeColors = [
                            
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
                                    @php
                                        $incClass = $classificationsMapForUI[$inc->incident_type] ?? null;
                                        $showNotAtFault = $incClass ? $incClass->show_not_at_fault : false;
                                    @endphp
                                    @if($showNotAtFault)
                                        <span class="px-2 py-0.5 bg-blue-500 text-white text-[9px] font-black uppercase tracking-widest rounded-full shadow-sm shadow-blue-100">Not at Fault</span>
                                    @endif
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
                                    class="p-2 text-gray-400 hover:text-orange-500 hover:bg-orange-50 rounded-xl transition-all duration-300 group/delete cursor-pointer" 
                                    title="Archive Record">
                                    <i data-lucide="archive" class="w-4 h-4 group-hover/delete:scale-110 pointer-events-none"></i>
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
     RECORD INCIDENT MODAL — SMART DYNAMIC
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
                                        data-driver-id="{{ $u->driver_id }}"
                                        data-secondary-driver-id="{{ $u->secondary_driver_id }}">
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
                                    <div class="search-option driver-search-option" data-id="{{ $d->id }}" data-name="{{ $d->full_name }}" data-contact="{{ $d->contact_number ?? '' }}">
                                        <div class="flex justify-between items-start">
                                            <div class="font-black text-xs text-gray-900">{{ $d->full_name }}</div>
                                            <span class="recommend-badge hidden px-1.5 py-0.5 bg-green-100 text-green-700 text-[8px] font-black rounded uppercase tracking-widest animate-pulse">
                                                Recommended
                                            </span>
                                        </div>
                                        <div class="text-[9px] text-gray-400 font-black uppercase tracking-tighter mt-1">{{ $d->current_plate ?? 'Floating / Unassigned' }}</div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-5">
                        <div class="col-span-2 sm:col-span-1">
                            <div class="flex items-center justify-between mb-2 ml-1">
                                <label class="block text-[10px] font-black text-gray-500 uppercase">Incident Classification *</label>
                                @if(Auth::user()->role === 'super_admin')
                                    <button type="button" onclick="openClassificationSettings()" class="text-[10px] font-black text-yellow-600 hover:text-yellow-700 uppercase tracking-tighter flex items-center gap-1">
                                        <i data-lucide="settings" class="w-3 h-3"></i> Manage Types
                                    </button>
                                @endif
                            </div>
                            <select name="incident_type" required id="incidentTypeSelect" onchange="handleTypeChange(this.value)"
                                class="w-full px-4 py-3.5 bg-gray-50 border border-gray-100 rounded-2xl text-sm font-bold focus:ring-4 focus:ring-yellow-500/10 focus:border-yellow-500 focus:outline-none transition-all">
                                <option value="">Select Type</option>
                                @foreach($classifications as $c)
                                    <option value="{{ $c->name }}"
                                        data-mode="{{ $c->behavior_mode ?? 'narrative' }}"
                                        data-sub-options='{{ json_encode($c->sub_options ?? \App\Models\IncidentClassification::getDefaultSubOptions($c->behavior_mode ?? 'narrative')) }}'
                                        data-auto-ban="{{ $c->auto_ban_trigger ? '1' : '0' }}"
                                        data-ban-value="{{ $c->ban_trigger_value ?? '' }}">
                                        {{ $c->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-span-2 sm:col-span-1">
                            <label class="block text-[10px] font-black text-gray-500 uppercase mb-2 ml-1">Severity / Priority *</label>
                            <select name="severity" required id="severitySelect" onchange="window._checkAutoBanState()"
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

                {{-- ── COMPLAINT MODE: Sub-Classification Picker ── --}}
                <div id="section-complaint" class="hidden bg-blue-50/60 p-6 rounded-3xl border border-blue-100 shadow-sm space-y-4">
                    <div class="flex items-center gap-3 mb-1">
                        <div class="w-1.5 h-5 bg-blue-500 rounded-full"></div>
                        <p class="text-[11px] font-black text-blue-700 uppercase tracking-widest">Complaint Sub-Classification</p>
                    </div>
                    <div id="subOptionsContainer" class="grid grid-cols-2 gap-2"></div>
                    <input type="hidden" name="sub_classification" id="subClassificationInput">
                </div>

                {{-- GLOBAL AUTO-BAN WARNING --}}
                <div id="autoBanWarning" class="hidden p-4 bg-red-50 border border-red-200 rounded-2xl flex items-start gap-3 shadow-sm">
                    <i data-lucide="alert-triangle" class="w-5 h-5 text-red-500 flex-shrink-0 mt-0.5"></i>
                    <div>
                        <p class="text-[11px] font-black text-red-700 uppercase tracking-widest leading-relaxed">⚠ SYSTEM AUTO-BAN TRIGGERED</p>
                        <p id="banTriggerLabel" class="text-[10px] font-bold text-red-500 mt-1">This driver will be automatically banned from the system.</p>
                    </div>
                </div>

                {{-- ── TRAFFIC MODE: Fine Amount ── --}}
                <div id="section-traffic" class="hidden bg-orange-50/60 p-6 rounded-3xl border border-orange-100 shadow-sm space-y-4">
                    <div class="flex items-center gap-3 mb-1">
                        <div class="w-1.5 h-5 bg-orange-500 rounded-full"></div>
                        <p class="text-[11px] font-black text-orange-700 uppercase tracking-widest">Traffic Violation Details</p>
                    </div>
                    <div id="trafficSubOptionsContainer" class="grid grid-cols-2 gap-2 mb-4"></div>
                    <input type="hidden" name="sub_classification" id="trafficSubClassificationInput">
                    <div>
                        <label class="block text-[10px] font-black text-orange-600 uppercase mb-2 ml-1">Traffic Fine Amount (₱) — charged to driver</label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-orange-400 font-bold">₱</span>
                            <input type="number" name="traffic_fine_amount" id="trafficFineInput" step="0.01" min="0" placeholder="0.00"
                                class="w-full pl-9 pr-4 py-3.5 bg-white border border-orange-200 rounded-2xl text-sm font-black text-orange-700 focus:ring-4 focus:ring-orange-500/10 focus:border-orange-500 focus:outline-none transition-all">
                        </div>
                    </div>
                </div>

                {{-- ── NARRATIVE: Description (always visible) ── --}}
                <div class="bg-white p-6 rounded-3xl border border-gray-100 shadow-sm space-y-4">
                    <div class="flex items-center gap-3 mb-1">
                        <div class="w-1.5 h-5 bg-orange-500 rounded-full"></div>
                        <p class="text-[11px] font-black text-gray-400 uppercase tracking-widest">Incident Narrative <span id="narrativeModeLabel" class="text-gray-300">(Describe the incident)</span></p>
                    </div>
                    <textarea name="description" required rows="3" maxlength="250" placeholder="Provide a detailed report of the incident..."
                        class="w-full px-5 py-4 bg-gray-50 border border-gray-200 rounded-2xl text-sm font-medium focus:ring-4 focus:ring-orange-500/5 focus:border-orange-500 focus:outline-none resize-none transition-all placeholder:text-gray-300"></textarea>
                </div>

                {{-- ── SECURITY MODE: Lockdown Warning ── --}}
                <div id="section-security" class="hidden p-6 bg-red-600 rounded-3xl border border-red-700 shadow-xl shadow-red-600/20 space-y-3 relative overflow-hidden group">
                    <div class="absolute right-[-10px] top-[-10px] opacity-10">
                        <i data-lucide="shield-alert" class="w-20 h-20 text-white"></i>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-white/20 rounded-xl backdrop-blur-sm">
                            <i data-lucide="shield-alert" class="w-5 h-5 text-white"></i>
                        </div>
                        <p class="text-[12px] font-black text-white uppercase tracking-widest">Security Lockdown Protocol</p>
                    </div>
                    <p class="text-[10px] font-bold text-red-100 leading-relaxed uppercase">Recording this incident will trigger an immediate permanent ban for the driver and flag the vehicle as stolen/missing in the system dashboard.</p>
                </div>

                {{-- Manual stolen/taken: operator-entered missing days + driver details (does not change auto boundary detection) --}}
                <div id="manualStolenDetailSection" class="hidden bg-white p-6 rounded-3xl border-2 border-amber-200 shadow-sm space-y-4">
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="text-[10px] font-black uppercase tracking-widest text-amber-900 bg-amber-100 px-2.5 py-1 rounded-lg border border-amber-300">Manual input</span>
                        <p class="text-[11px] font-black text-gray-700 uppercase tracking-widest">Missing unit — operator report</p>
                    </div>
                    <p class="text-[10px] text-gray-500 leading-relaxed">Ilagay ang bilang ng araw na nawawala ang unit at ang detalyadong impormasyon ng driver. Makikita ito sa <strong>Flagged units</strong> bilang manual report (hindi auto-detected).</p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="sm:col-span-1">
                            <label class="block text-[10px] font-black text-gray-500 uppercase mb-2 ml-1">Days unit has been missing *</label>
                            <input type="number" name="missing_days_reported" id="missing_days_reported" min="0" max="3650" step="1"
                                class="w-full px-4 py-3.5 bg-amber-50/50 border border-amber-100 rounded-2xl text-sm font-bold text-gray-900 focus:ring-4 focus:ring-amber-500/10 focus:border-amber-400 focus:outline-none transition-all"
                                placeholder="0" autocomplete="off">
                        </div>
                        <div class="sm:col-span-1">
                            <label class="block text-[10px] font-black text-gray-500 uppercase mb-2 ml-1">Driver full name (detailed) *</label>
                            <input type="text" name="stolen_driver_detail_name" id="stolen_driver_detail_name" maxlength="255"
                                class="w-full px-4 py-3.5 bg-amber-50/50 border border-amber-100 rounded-2xl text-sm font-bold text-gray-900 focus:ring-4 focus:ring-amber-500/10 focus:border-amber-400 focus:outline-none transition-all"
                                placeholder="Buong pangalan ng driver" autocomplete="off">
                        </div>
                        <div class="sm:col-span-1">
                            <label class="block text-[10px] font-black text-gray-500 uppercase mb-2 ml-1">Driver contact #</label>
                            <input type="text" name="stolen_driver_detail_contact" id="stolen_driver_detail_contact" maxlength="64"
                                class="w-full px-4 py-3.5 bg-amber-50/50 border border-amber-100 rounded-2xl text-sm font-bold text-gray-900 focus:ring-4 focus:ring-amber-500/10 focus:border-amber-400 focus:outline-none transition-all"
                                placeholder="Mobile / phone" autocomplete="off">
                        </div>
                        <div class="sm:col-span-1">
                            <label class="block text-[10px] font-black text-gray-500 uppercase mb-2 ml-1">License no. (optional)</label>
                            <input type="text" name="stolen_driver_license_no" id="stolen_driver_license_no" maxlength="64"
                                class="w-full px-4 py-3.5 bg-amber-50/50 border border-amber-100 rounded-2xl text-sm font-bold text-gray-900 focus:ring-4 focus:ring-amber-500/10 focus:border-amber-400 focus:outline-none transition-all"
                                placeholder="Driver license" autocomplete="off">
                        </div>
                    </div>
                </div>

                {{-- ── DAMAGE MODE: Full Assessment ── --}}
                <div id="section-damage" class="hidden p-8 bg-purple-50/50 rounded-[2.5rem] border border-purple-100 space-y-8 ring-1 ring-purple-100/50">
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
                    </div>
                </div>

                {{-- Global Liability Acknowledgement (Visible for all modes) --}}
                <div class="space-y-4">
                    <label class="flex items-center gap-4 cursor-pointer p-6 bg-white rounded-[2rem] border border-red-100 hover:bg-red-50 hover:border-red-200 transition-all group select-none shadow-sm">
                        <div class="relative flex items-center justify-center">
                            <input type="checkbox" name="is_driver_fault" id="faultCheck" value="1" onchange="typeof computeTotal === 'function' ? computeTotal() : null"
                                class="w-7 h-7 accent-red-600 rounded-2xl cursor-pointer transition-transform group-hover:scale-110 border-2 border-red-200">
                        </div>
                        <div>
                            <p class="text-xs font-black text-gray-800 uppercase tracking-wider">Driver is at Fault</p>
                            <p class="text-[9px] text-red-500 font-bold uppercase mt-1 tracking-widest leading-relaxed">Check this if the driver is primarily responsible for the incident.</p>
                        </div>
                    </label>
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
                        <select name="incident_type" id="edit_incident_type" required onchange="handleTypeChange(this.value, 'edit')"
                            class="w-full px-4 py-3.5 bg-gray-50 border border-gray-100 rounded-2xl text-sm font-bold focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 focus:outline-none transition-all">
                            @foreach($classifications as $c)
                                <option value="{{ $c->name }}">{{ $c->name }}</option>
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
                    <textarea name="description" id="edit_description" required rows="3" maxlength="250"
                        class="w-full px-4 py-3.5 bg-gray-50 border border-gray-100 rounded-2xl text-sm font-bold focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 focus:outline-none transition-all resize-none"></textarea>
                </div>

                {{-- ── DYNAMIC SECTIONS FOR EDIT MODAL ── --}}
                
                {{-- COMPLAINT MODE (EDIT) --}}
                <div id="edit-section-complaint" class="hidden bg-blue-50/60 p-6 rounded-3xl border border-blue-100 shadow-sm space-y-4">
                    <div class="flex items-center gap-3 mb-1">
                        <div class="w-1.5 h-5 bg-blue-500 rounded-full"></div>
                        <p class="text-[11px] font-black text-blue-700 uppercase tracking-widest">Complaint Sub-Classification</p>
                    </div>
                    <div id="edit-subOptionsContainer" class="grid grid-cols-2 gap-2"></div>
                    <input type="hidden" name="sub_classification" id="edit-subClassificationInput">
                </div>

                {{-- TRAFFIC MODE (EDIT) --}}
                <div id="edit-section-traffic" class="hidden bg-orange-50/60 p-6 rounded-3xl border border-orange-100 shadow-sm space-y-4">
                    <div class="flex items-center gap-3 mb-1">
                        <div class="w-1.5 h-5 bg-orange-500 rounded-full"></div>
                        <p class="text-[11px] font-black text-orange-700 uppercase tracking-widest">Traffic Violation Details</p>
                    </div>
                    <div id="edit-trafficSubOptionsContainer" class="grid grid-cols-2 gap-2 mb-4"></div>
                    <input type="hidden" name="sub_classification" id="edit-trafficSubClassificationInput">
                    <div>
                        <label class="block text-[10px] font-black text-orange-600 uppercase mb-2 ml-1">Traffic Fine Amount (₱)</label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-orange-400 font-bold">₱</span>
                            <input type="number" name="traffic_fine_amount" id="edit_traffic_fine_amount" step="0.01" min="0" placeholder="0.00"
                                class="w-full pl-9 pr-4 py-3.5 bg-white border border-orange-200 rounded-2xl text-sm font-black text-orange-700 focus:ring-4 focus:ring-orange-500/10 focus:border-orange-500 focus:outline-none transition-all">
                        </div>
                    </div>
                </div>

                {{-- DAMAGE MODE (EDIT) --}}
                <div id="edit-section-damage" class="hidden bg-purple-50/60 p-6 rounded-3xl border border-purple-100 shadow-sm space-y-4">
                     <div class="flex items-center gap-3 mb-1">
                        <div class="w-1.5 h-5 bg-purple-500 rounded-full"></div>
                        <p class="text-[11px] font-black text-purple-700 uppercase tracking-widest">Damage Assessment</p>
                    </div>
                    <p class="text-[10px] text-purple-400 font-bold uppercase tracking-tight leading-relaxed italic">Note: To manage itemized parts and services, please use the main assessment system.</p>
                </div>

                {{-- SECURITY MODE (EDIT) --}}
                <div id="edit-section-security" class="hidden p-6 bg-red-600 rounded-3xl border border-red-700 shadow-lg space-y-3">
                    <div class="flex items-center gap-3">
                        <i data-lucide="shield-alert" class="w-5 h-5 text-white"></i>
                        <p class="text-[12px] font-black text-white uppercase tracking-widest">Security Lockdown Active</p>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-5 pt-2">
                    <div id="edit-total-charge-section" class="hidden">
                        <label class="block text-[10px] font-black text-gray-500 uppercase mb-2 ml-1">Total Charge to Driver (₱)</label>
                        <div class="relative">
                            <input type="number" step="0.01" name="total_charge_to_driver" id="edit_total_charge"
                                class="w-full pl-9 pr-4 py-3.5 bg-gray-50 border border-gray-100 rounded-2xl text-sm font-black text-red-600 focus:ring-4 focus:ring-red-500/10 focus:border-red-500 focus:outline-none transition-all">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 font-bold">₱</span>
                        </div>
                    </div>
                    <div id="edit-fault-section">
                        <label class="block text-[10px] font-black text-gray-500 uppercase mb-2 ml-1">Liability Status</label>
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

{{-- Incident Classification Settings Modal --}}
<div id="classificationSettingsModal" style="display: none;" class="fixed inset-0 z-[9999] items-center justify-center bg-black/60 backdrop-blur-sm p-4">
    <div class="bg-white rounded-[2rem] w-full max-w-4xl overflow-hidden shadow-2xl border border-white/20 animate-in fade-in zoom-in duration-300">
        <div class="bg-yellow-500 p-6 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-white/20 rounded-xl">
                    <i data-lucide="settings" class="w-5 h-5 text-white"></i>
                </div>
                <h3 class="text-white font-black uppercase tracking-tighter">Incident Classification Settings</h3>
            </div>
            <button onclick="closeClassificationSettings()" class="p-2 hover:bg-white/10 rounded-xl transition-colors">
                <i data-lucide="x" class="w-5 h-5 text-white"></i>
            </button>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2">
            {{-- Left: List --}}
            <div class="p-8 border-r border-gray-100 flex flex-col max-h-[70vh]">
                <div class="flex items-center gap-6 mb-8 border-b border-gray-100 pb-4">
                    <button onclick="switchClsList('active')" id="clsTabBtn-active" class="cls-tab-btn active text-[10px] font-black uppercase tracking-widest transition-all">Active Classifications</button>
                    <button onclick="switchClsList('archived')" id="clsTabBtn-archived" class="cls-tab-btn text-[10px] font-black uppercase tracking-widest text-gray-400 hover:text-gray-600 transition-all">Archives</button>
                </div>

                <div id="clsList-active" class="cls-list-content space-y-3 overflow-y-auto custom-scroll pr-2 flex-1">
                    @foreach($classifications as $c)
                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-2xl border border-gray-100 hover:border-yellow-200 transition-all group">
                        <div>
                            <div class="text-sm font-black text-gray-900">{{ $c->name }}</div>
                            <div class="text-[10px] font-bold text-gray-400 uppercase tracking-tighter">Default: <span class="text-yellow-600">{{ $c->default_severity }}</span></div>
                        </div>
                        <div class="flex gap-2 opacity-0 group-hover:opacity-100 transition-all relative z-10">
                            <button type="button" onclick="editClassification({{ $c->id }}, '{{ addslashes($c->name) }}', '{{ $c->default_severity }}')" class="p-2 bg-white text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-xl border border-gray-100 shadow-sm transition-all cursor-pointer relative z-10">
                                <i data-lucide="edit-3" class="w-4 h-4"></i>
                            </button>
                            <button type="button" onclick="archiveClassification({{ $c->id }})" class="p-2 bg-white text-gray-400 hover:text-orange-500 hover:bg-orange-50 rounded-xl border border-gray-100 shadow-sm transition-all cursor-pointer relative z-10">
                                <i data-lucide="archive" class="w-4 h-4"></i>
                            </button>
                        </div>
                    </div>
                    @endforeach
                </div>

                <div id="clsList-archived" class="cls-list-content hidden space-y-3 overflow-y-auto custom-scroll pr-2 flex-1">
                    @forelse($archivedClassifications as $ac)
                    <div class="flex items-center justify-between p-4 bg-gray-100/50 rounded-2xl border border-gray-200 opacity-60">
                        <div class="text-sm font-black text-gray-500 line-through">{{ $ac->name }}</div>
                        <div class="flex gap-2 relative z-10">
                            <button type="button" onclick="restoreClassification({{ $ac->id }})" class="p-2 bg-white text-gray-400 hover:text-green-600 hover:bg-green-50 rounded-xl border border-gray-100 transition-all cursor-pointer relative z-10">
                                <i data-lucide="refresh-cw" class="w-4 h-4"></i>
                            </button>
                            <button type="button" onclick="deleteClassification({{ $ac->id }})" class="p-2 bg-white text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-xl border border-gray-100 transition-all cursor-pointer relative z-10">
                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                            </button>
                        </div>
                    </div>
                    @empty
                    <div class="h-full flex flex-col items-center justify-center p-12 text-center">
                        <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mb-4">
                            <i data-lucide="archive" class="w-8 h-8 text-gray-200"></i>
                        </div>
                        <div class="text-[10px] font-black text-gray-300 uppercase tracking-widest">No archived items found</div>
                    </div>
                    @endforelse
                </div>
            </div>

            {{-- Right: Add/Edit Form --}}
            <div class="p-8 bg-gray-50/50 overflow-y-auto max-h-[70vh]">
                <div class="flex items-center justify-between mb-6">
                    <h4 id="quickClsTitle" class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Add New Classification</h4>
                    <button onclick="resetClsForm()" class="text-[10px] font-black text-blue-600 uppercase tracking-tighter">Clear Form</button>
                </div>
                <input type="hidden" id="quickClsId">
                <div class="space-y-5">
                    <div>
                        <label class="block text-[10px] font-black text-gray-500 uppercase mb-2 ml-1">Classification Name</label>
                        <input type="text" id="quickClsName" placeholder="e.g. Passenger Complaint"
                            class="w-full px-5 py-4 bg-white border border-gray-100 rounded-2xl text-sm font-black focus:ring-4 focus:ring-yellow-500/10 focus:border-yellow-500 focus:outline-none transition-all">
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-gray-500 uppercase mb-2 ml-1">Default Severity</label>
                        <select id="quickClsSeverity"
                            class="w-full px-5 py-4 bg-white border border-gray-100 rounded-2xl text-sm font-black focus:ring-4 focus:ring-yellow-500/10 focus:border-yellow-500 focus:outline-none transition-all">
                            <option value="low">Low</option>
                            <option value="medium" selected>Medium</option>
                            <option value="high">High</option>
                            <option value="critical">Critical</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-gray-500 uppercase mb-2 ml-1">Modal Behavior Mode</label>
                        <select id="quickClsMode" onchange="toggleClsModeFields()" class="w-full px-5 py-4 bg-white border border-gray-100 rounded-2xl text-sm font-black focus:ring-4 focus:ring-yellow-500/10 focus:border-yellow-500 focus:outline-none transition-all">
                            <option value="narrative">Narrative Only (remark/absent)</option>
                            <option value="complaint">Passenger Complaint (sub-options + ban)</option>
                            <option value="traffic">Traffic Violation (sub-options + fine)</option>
                            <option value="damage">Vehicle Damage / Accident (full cost assessment)</option>
                            <option value="security">Vehicle Security Lockdown (Taken/Stolen)</option>
                        </select>
                        <p class="text-[9px] text-gray-400 font-bold mt-1 ml-1">Controls which sections appear in the Record Incident form</p>
                    </div>
                    <div id="clsFaultBadgeRow" class="space-y-3 mt-4">
                        <label class="flex items-center gap-3 p-4 bg-blue-50 rounded-2xl border border-blue-100 cursor-pointer transition-all hover:bg-blue-100/50">
                            <input type="checkbox" id="quickClsShowNotAtFault" class="w-5 h-5 accent-blue-600 rounded">
                            <div>
                                <p class="text-[10px] font-black text-blue-700 uppercase tracking-widest">Show "Not at Fault" Badge</p>
                                <p class="text-[9px] text-blue-500 font-bold mt-0.5">When unchecked in incidents, display the Blue "Not at Fault" badge.</p>
                            </div>
                        </label>
                    </div>
                    <div id="clsSubOptionsRow" class="hidden mt-4">
                        <label class="block text-[10px] font-black text-gray-500 uppercase mb-2 ml-1">Sub-Options <span class="text-gray-300">(one per line)</span></label>
                        <textarea id="quickClsSubOptions" rows="5" placeholder="e.g.&#10;Contracting&#10;Discourtesy&#10;Overcharging"
                            class="w-full px-4 py-3 bg-white border border-gray-100 rounded-2xl text-sm font-medium focus:ring-4 focus:ring-yellow-500/10 focus:border-yellow-500 focus:outline-none resize-none"></textarea>
                    </div>
                    <div id="clsBanRow" class="hidden space-y-3">
                        <label class="flex items-center gap-3 p-4 bg-red-50 rounded-2xl border border-red-100 cursor-pointer">
                            <input type="checkbox" id="quickClsAutoBan" class="w-5 h-5 accent-red-600" onchange="toggleBanValueField()">
                            <div>
                                <p class="text-[10px] font-black text-red-700 uppercase tracking-widest">Enable Auto-Ban Trigger</p>
                                <p class="text-[9px] text-red-400 font-bold mt-0.5">Selecting a specific sub-option will automatically ban the driver</p>
                            </div>
                        </label>
                        <div id="clsBanValueRow" class="hidden">
                            <label class="block text-[10px] font-black text-gray-500 uppercase mb-2 ml-1">Ban Trigger Sub-Option</label>
                            <input type="text" id="quickClsBanValue" placeholder="e.g. Contracting"
                                class="w-full px-4 py-3 bg-white border border-red-200 rounded-2xl text-sm font-black text-red-700 focus:ring-4 focus:ring-red-500/10 focus:border-red-500 focus:outline-none">
                        </div>
                    </div>
                    <button type="button" onclick="saveQuickClassification()" class="w-full bg-yellow-500 hover:bg-yellow-600 text-white font-black py-5 rounded-2xl shadow-xl shadow-yellow-500/20 transition-all flex items-center justify-center gap-2 mt-2">
                        <i data-lucide="save" class="w-5 h-5"></i>
                        SAVE CLASSIFICATION
                    </button>
                    {{-- Global Toast Container --}}
                    <div id="sa-toast"></div>

                    {{-- Archive Deletion Security Modal --}}
                    <div class="sa-modal-backdrop" id="archiveSecurityModal">
                        <div class="sa-modal" style="max-width: 420px; text-align: center;">
                            <div style="background: #fef2f2; width: 64px; height: 64px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem; border: 4px solid #fee2e2;">
                                <i data-lucide="shield-alert" style="width: 32px; height: 32px; color: #dc2626;"></i>
                            </div>
                            
                            <h3 style="font-weight: 900; font-size: 1.25rem; color: #991b1b; margin-bottom: .5rem;">Security Verification</h3>
                            <p style="color: #64748b; font-size: .85rem; margin-bottom: 1.5rem;">To permanently delete this item, please enter the **Archive Deletion Password** below.</p>
                            
                            <div class="mb-6">
                                <input type="password" id="archive-security-pwd" class="sa-input" style="text-align: center; font-size: 1.2rem; letter-spacing: .2em;" placeholder="••••••">
                            </div>
                            
                            <div style="background: #fffbeb; border: 1px solid #fef3c7; border-radius: .75rem; padding: .75rem; margin-bottom: 1.5rem; display: flex; align-items: flex-start; gap: .75rem; text-align: left;">
                                <i data-lucide="alert-triangle" style="width: 16px; height: 16px; color: #d97706; flex-shrink: 0; margin-top: 2px;"></i>
                                <p style="font-size: .7rem; color: #92400e; font-weight: 500;">Warning: Permanent deletion is **irreversible**. All associated data will be removed from the system forever.</p>
                            </div>

                            <div class="flex gap-3">
                                <button class="btn-ghost flex-1 py-3" onclick="document.getElementById('archiveSecurityModal').classList.remove('open')">Cancel</button>
                                <button class="btn-danger flex-1 py-3" id="btn-confirm-permanent-delete">Confirm Delete</button>
                            </div>
                        </div>
                    </div>

                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            const confirmBtn = document.getElementById('btn-confirm-permanent-delete');
                            if (confirmBtn) {
                                confirmBtn.addEventListener('click', async function() {
                                    const password = document.getElementById('archive-security-pwd').value;
                                    if (!password) { toast('Please enter the security password.', true); return; }
                                    
                                    const btn = this;
                                    const originalText = btn.innerHTML;
                                    btn.disabled = true;
                                    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Verifying...';

                                    if (typeof archiveSecurityCallback !== 'undefined' && archiveSecurityCallback) {
                                        await archiveSecurityCallback(password);
                                    }

                                    btn.disabled = false;
                                    btn.innerHTML = originalText;
                                    document.getElementById('archiveSecurityModal').classList.remove('open');
                                });
                            }
                        });
                    </script>
                </div>
                <div id="clsSecurityWarning" class="hidden mt-8 p-5 bg-red-50 rounded-2xl border border-red-100">
                    <div class="flex gap-3">
                        <i data-lucide="shield-alert" class="w-5 h-5 text-red-600 flex-shrink-0"></i>
                        <div>
                            <p class="text-[10px] font-black text-red-700 uppercase tracking-widest">Security Lockdown Mode Active</p>
                            <p class="text-[9px] font-bold text-red-500 leading-relaxed uppercase tracking-tight mt-0.5">Recording an incident with this classification will automatically BANNED the driver and flag the unit as MISSING/STOLEN.</p>
                        </div>
                    </div>
                </div>

                <div id="clsBehaviorInfo" class="mt-8 p-5 bg-yellow-50 rounded-2xl border border-yellow-100">
                    <div class="flex gap-3">
                        <i data-lucide="info" class="w-5 h-5 text-yellow-600 flex-shrink-0"></i>
                        <p class="text-[10px] font-bold text-yellow-800 leading-relaxed uppercase tracking-tight">The Behavior Mode controls what the dispatcher sees when recording an incident of this type.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


@include('partials._driver_details_modal')


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

// ─── Incident Classification Management ────────────────────────────────────
 window.toast = function(msg, isError = false) {
     const el = document.getElementById('sa-toast');
     if (!el) return;
     
     const icon = isError ? 'alert-circle' : 'check-circle';
     const iconColor = isError ? '#ef4444' : '#10b981';
     
     el.innerHTML = `
         <i data-lucide="${icon}" style="width:18px;height:18px;color:${iconColor}; flex-shrink:0;"></i>
         <span style="white-space: nowrap;">${msg}</span>
     `;
     
     if (window.lucide) window.lucide.createIcons();
     
     el.className = 'show' + (isError ? ' error' : '');
     setTimeout(() => el.className = '', 3500);
 };

 window.promptArchivePassword = function(callback, isPermanent = false) {
     window.archiveSecurityCallback = callback;
     const pwdInput = document.getElementById('archive-security-pwd');
     if (pwdInput) pwdInput.value = '';
     
     const modal = document.getElementById('archiveSecurityModal');
     const title = modal?.querySelector('h3');
     const desc = modal?.querySelector('p');
     const confirmBtn = document.getElementById('btn-confirm-permanent-delete');
     
     if (title) title.textContent = isPermanent ? 'Permanent Deletion' : 'Archive Confirmation';
     if (desc) desc.textContent = isPermanent ? 'This action is irreversible. Please enter the archive password to proceed.' : 'This item will be moved to archives. Please enter the security password.';
     if (confirmBtn) {
         confirmBtn.textContent = isPermanent ? 'Confirm Permanent Delete' : 'Confirm Archive';
         confirmBtn.className = isPermanent 
            ? 'bg-red-600 hover:bg-red-700 text-white flex-1 py-3 font-bold rounded-xl transition-all shadow-lg shadow-red-200'
            : 'bg-orange-500 hover:bg-orange-600 text-white flex-1 py-3 font-bold rounded-xl transition-all shadow-lg shadow-orange-200';
     }

     if (modal) modal.classList.add('open');
 };

 window.refreshClassificationsList = async function() {
     try {
         const res = await fetch(window.location.href);
         const text = await res.text();
         const parser = new DOMParser();
         const doc = parser.parseFromString(text, 'text/html');
         
         const newActive = doc.getElementById('clsList-active');
         const newArchived = doc.getElementById('clsList-archived');
         
         if (newActive) {
             document.getElementById('clsList-active').innerHTML = newActive.innerHTML;
         }
         if (newArchived) {
             document.getElementById('clsList-archived').innerHTML = newArchived.innerHTML;
         }
         
         if (window.lucide) window.lucide.createIcons();
     } catch (err) {
         console.error('Failed to refresh list:', err);
     }
 };

 window.openClassificationSettings = function() {
     const modal = document.getElementById('classificationSettingsModal');
     if (!modal) return;
     modal.style.display = 'flex';
     if(window.lucide) lucide.createIcons();
 };

 window.closeClassificationSettings = function() {
     const modal = document.getElementById('classificationSettingsModal');
     if (!modal) return;
     modal.style.display = 'none';
 };

 window.editClassification = function(id, name, severity) {
     document.getElementById('quickClsId').value = id;
     document.getElementById('quickClsName').value = name;
     document.getElementById('quickClsSeverity').value = severity;
     document.getElementById('quickClsTitle').textContent = 'Edit Classification';

     // Fetch full metadata for this classification via XHR
     fetch(`/super-admin/incident-classifications/${id}/details`, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
         .then(r => r.json()).then(res => {
             const data = res.data;
             if (data) {
                 document.getElementById('quickClsMode').value = data.behavior_mode || 'narrative';
                 toggleClsModeFields();
                 if (data.sub_options && Array.isArray(data.sub_options)) {
                     document.getElementById('quickClsSubOptions').value = data.sub_options.join('\n');
                 } else {
                     document.getElementById('quickClsSubOptions').value = '';
                 }
                 document.getElementById('quickClsAutoBan').checked = !!data.auto_ban_trigger;
                 toggleBanValueField();
                 document.getElementById('quickClsBanValue').value = data.ban_trigger_value || '';
                 document.getElementById('quickClsShowNotAtFault').checked = !!data.show_not_at_fault;
             }
         }).catch(err => {
             console.error('Error fetching details:', err);
         });
 };

 window.resetClsForm = function() {
     document.getElementById('quickClsId').value = '';
     document.getElementById('quickClsName').value = '';
     document.getElementById('quickClsTitle').textContent = 'Add New Classification';
     document.getElementById('quickClsMode').value = 'narrative';
     document.getElementById('quickClsSubOptions').value = '';
     document.getElementById('quickClsAutoBan').checked = false;
     document.getElementById('quickClsBanValue').value = '';
     document.getElementById('quickClsShowNotAtFault').checked = false;
     toggleClsModeFields();
 };

  window.toggleClsModeFields = function() {
      const mode = document.getElementById('quickClsMode').value;
      const subRow = document.getElementById('clsSubOptionsRow');
      const banRow = document.getElementById('clsBanRow');
      const securityWarning = document.getElementById('clsSecurityWarning');
      const behaviorInfo = document.getElementById('clsBehaviorInfo');

      if (['complaint','traffic'].includes(mode)) {
          subRow.classList.remove('hidden');
          banRow.classList.toggle('hidden', mode !== 'complaint');
      } else {
          subRow.classList.add('hidden');
          banRow.classList.add('hidden');
      }

      if (mode === 'security') {
          securityWarning?.classList.remove('hidden');
          behaviorInfo?.classList.add('hidden');
          document.getElementById('quickClsSeverity').value = 'critical';
      } else {
          securityWarning?.classList.add('hidden');
          behaviorInfo?.classList.remove('hidden');
      }
  };

 window.toggleBanValueField = function() {
     const checked = document.getElementById('quickClsAutoBan').checked;
     document.getElementById('clsBanValueRow').classList.toggle('hidden', !checked);
 };

 window.saveQuickClassification = async function() {
     const id = document.getElementById('quickClsId').value;
     const name = document.getElementById('quickClsName').value.trim();
     const severity = document.getElementById('quickClsSeverity').value;
     const mode = document.getElementById('quickClsMode').value;
     const subOptsRaw = document.getElementById('quickClsSubOptions').value;
     const autoBan = document.getElementById('quickClsAutoBan').checked;
     const banValue = document.getElementById('quickClsBanValue').value.trim();
     const showNotAtFault = document.getElementById('quickClsShowNotAtFault').checked;

     if (!name) return alert('Please enter a classification name.');

     const subOptions = subOptsRaw ? subOptsRaw.split('\n').map(s => s.trim()).filter(Boolean) : [];
     const url = id ? `/super-admin/incident-classifications/${id}` : '/super-admin/incident-classifications';

     const btn = event?.target?.closest('button') || document.querySelector('button[onclick="saveQuickClassification()"]');
     const originalHtml = btn ? btn.innerHTML : '';
     if (btn) {
         btn.disabled = true;
         btn.innerHTML = '<i class="animate-spin" data-lucide="refresh-cw"></i> SAVING...';
         if(window.lucide) lucide.createIcons();
     }

     try {
         const res = await fetch(url, {
             method: id ? 'PATCH' : 'POST',
             headers: { 
                 'Content-Type': 'application/json', 
                 'Accept': 'application/json',
                 'X-CSRF-TOKEN': '{{ csrf_token() }}' 
             },
             body: JSON.stringify({
                 name, default_severity: severity, color: 'gray', icon: 'alert-circle',
                 behavior_mode: mode, sub_options: subOptions,
                 auto_ban_trigger: autoBan, ban_trigger_value: banValue || null,
                 show_not_at_fault: showNotAtFault
             })
         });
         
         const result = await res.json();
         if (res.ok && result.success) {
             toast('✔ ' + result.message);
             resetClsForm();
             window.refreshClassificationsList();
             if (typeof closeClassificationSettings === 'function') closeClassificationSettings();
         } else {
             const errorMsg = result.message || result.error || 'Validation Failed: Please check your inputs.';
             toast(errorMsg, true);
         }
     } catch(e) { 
         console.error(e);
         toast('Error saving classification.', true); 
     } finally {
         if (btn) {
             btn.disabled = false;
             btn.innerHTML = originalHtml;
             if(window.lucide) lucide.createIcons();
         }
     }
 };

 window.archiveClassification = async function(id) {
      if (!confirm('Are you sure you want to move this classification to the archives?')) return;
      console.log('Archiving ID:', id);
      try {
          const res = await fetch(`/super-admin/incident-classifications/${id}/archive`, {
              method: 'DELETE',
              headers: { 
                  'Content-Type': 'application/json',
                  'X-CSRF-TOKEN': '{{ csrf_token() }}' 
              }
          });
          
          const text = await res.text();
          console.log('Archive Response Text:', text);
          
          let data;
          try { data = JSON.parse(text); } catch(e) { throw new Error('Invalid JSON response'); }

          if (res.ok && data.success) {
              toast('✔ ' + (data.message || 'Archived successfully.'));
              window.refreshClassificationsList();
          } else {
              toast(data.message || 'Error archiving: ' + res.status, true);
          }
      } catch (e) { 
          console.error('Archive Error:', e);
          toast('Error archiving: ' + e.message, true); 
      }
  };

 window.restoreClassification = async function(id) {
     try {
         const res = await fetch(`/super-admin/incident-classifications/${id}/restore`, {
             method: 'POST',
             headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
         });
         const data = await res.json();
         if (data.success) {
             toast('✔ ' + (data.message || 'Restored successfully.'));
             window.refreshClassificationsList();
         }
     } catch (e) { toast('Error restoring.', true); }
 };

 window.deleteClassification = async function(id) {
     window.promptArchivePassword(async (password) => {
         console.log('Deleting Permanent ID:', id);
         try {
             const res = await fetch(`/super-admin/incident-classifications/${id}`, {
                 method: 'DELETE',
                 headers: { 
                     'Content-Type': 'application/json',
                     'X-CSRF-TOKEN': '{{ csrf_token() }}' 
                 },
                 body: JSON.stringify({ archive_password: password })
             });
             
             const text = await res.text();
             console.log('Delete Response Text:', text);
             
             let data;
             try { data = JSON.parse(text); } catch(e) { throw new Error('Invalid JSON response'); }

             if (res.ok && data.success) {
                 toast('✔ ' + (data.message || 'Deleted successfully.'));
                 window.refreshClassificationsList();
                 document.getElementById('archiveSecurityModal').classList.remove('open');
             } else {
                 toast(data.message || 'Verification failed: ' + res.status, true);
             }
         } catch (e) { 
             console.error('Delete Error:', e);
             toast('Error deleting: ' + e.message, true); 
         }
     }, true);
 };

 window.switchClsList = function(type) {
     document.querySelectorAll('.cls-list-content').forEach(l => l.classList.add('hidden'));
     document.querySelectorAll('.cls-tab-btn').forEach(b => {
         b.classList.remove('active', 'text-gray-900');
         b.classList.add('text-gray-400');
     });
     
     document.getElementById('clsList-' + type).classList.remove('hidden');
     const btn = document.getElementById('clsTabBtn-' + type);
     btn.classList.add('active', 'text-gray-900');
     btn.classList.remove('text-gray-400');
 };

window.openIncidentModal = function() {
    const modal = document.getElementById('incidentModal');
    if (!modal) return;
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    if(window.lucide) lucide.createIcons();
    
    // Reset driver filtering state
    allowedDriverIds = null;
    setDriverOptionsVisibility(null);
    
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
var partsCatalog = @json($spare_parts ?? []);
var incidentPartsCart = [];
var incidentServices = [];
var classificationsMap = @json($classifications->pluck('default_severity', 'name'));

// Full classification metadata for the smart modal
var classificationsMeta = {};
@foreach($classifications as $c)
classificationsMeta["{{ $c->name }}"] = {
    mode: "{{ $c->behavior_mode ?? 'narrative' }}",
    subOptions: @json($c->sub_options ?? $c->getDefaultSubOptions($c->behavior_mode ?? 'narrative')),
    autoBan: {{ $c->auto_ban_trigger ? 'true' : 'false' }},
    banValue: "{{ addslashes($c->ban_trigger_value ?? '') }}"
};
@endforeach

window.handleTypeChange = function(val, context = '') {
    const prefix = context ? context + '-' : '';
    
    // 1. Auto-set severity
    const severitySelect = document.getElementById(prefix + 'severitySelect') || document.getElementById('edit_severity');
    if (severitySelect && classificationsMap[val]) severitySelect.value = classificationsMap[val];

    const meta = classificationsMeta[val] || { mode: 'narrative', subOptions: [], autoBan: false, banValue: '' };
    const mode = meta.mode;

    // 2. Hide all mode-specific sections
    ['complaint','traffic','damage','security'].forEach(s => {
        const el = document.getElementById(prefix + 'section-' + s);
        if (el) el.classList.add('hidden');
    });

    // 3. Clear any previous sub_classification inputs
    const subInput = document.getElementById(prefix + 'subClassificationInput');
    const trafInput = document.getElementById(prefix + 'trafficSubClassificationInput');
    if (subInput) subInput.value = '';
    if (trafInput) trafInput.value = '';

    // 4. Show relevant section
    if (mode === 'complaint') {
        const sec = document.getElementById(prefix + 'section-complaint');
        if (sec) sec.classList.remove('hidden');
        _renderSubOptions(prefix + 'subOptionsContainer', meta.subOptions, prefix + 'subClassificationInput', meta.autoBan, meta.banValue, 'blue');
    } else if (mode === 'traffic' || val === 'Traffic Violation') {
        const sec = document.getElementById(prefix + 'section-traffic');
        if (sec) sec.classList.remove('hidden');
        _renderSubOptions(prefix + 'trafficSubOptionsContainer', meta.subOptions, prefix + 'trafficSubClassificationInput', false, '', 'orange');
    } else if (mode === 'damage' || val === 'Vehicle Damage') {
        const sec = document.getElementById(prefix + 'section-damage');
        if (sec) sec.classList.remove('hidden');
    } else if (mode === 'security') {
        const sec = document.getElementById(prefix + 'section-security');
        if (sec) sec.classList.remove('hidden');
    }

    // Manual stolen/taken fields (record modal only)
    if (!context) {
        const stolenSec = document.getElementById('manualStolenDetailSection');
        if (stolenSec) {
            if (mode === 'security') {
                stolenSec.classList.remove('hidden');
                if (typeof window._prefillManualStolenFromSelectedDriver === 'function') {
                    window._prefillManualStolenFromSelectedDriver();
                }
            } else {
                stolenSec.classList.add('hidden');
                ['missing_days_reported', 'stolen_driver_detail_name', 'stolen_driver_detail_contact', 'stolen_driver_license_no'].forEach(function (id) {
                    const el = document.getElementById(id);
                    if (el) el.value = '';
                });
            }
        }
    }

    // 5. Explicitly handle Charge Section for Edit Modal
    if (context === 'edit' || (val && document.getElementById('edit_incident_type')?.value === val)) {
        const chargeSection = document.getElementById('edit-total-charge-section');
        if (chargeSection) {
            // ONLY show charge field for Vehicle Damage, regardless of mode
            if (val === 'Vehicle Damage' || mode === 'damage') {
                chargeSection.classList.remove('hidden');
                chargeSection.style.display = 'block'; 
            } else {
                chargeSection.classList.add('hidden');
                chargeSection.style.display = 'none'; // Force hide
            }
        }
    }

    // 5. Update narrative label hint
    const hint = document.getElementById(prefix + 'narrativeModeLabel');
    if (hint) {
        const labels = { 
            complaint:'(Describe the passenger complaint)', 
            traffic:'(Describe the traffic violation)', 
            damage:'(Describe the accident/damage)', 
            security:'(Describe the vehicle security incident)',
            narrative:'(Describe the incident)' 
        };
        hint.textContent = labels[mode] || '(Describe the incident)';
    }

    if (window.lucide) lucide.createIcons();
    if (typeof window._checkAutoBanState === 'function') window._checkAutoBanState();
};

// Render pill buttons for sub-options
function _renderSubOptions(containerId, options, inputId, autoBan, banValue, color) {
    const container = document.getElementById(containerId);
    const input = document.getElementById(inputId);
    if (!container || !input) return;
    if (!options || options.length === 0) { container.innerHTML = ''; return; }

    const colors = {
        blue: 'focus:ring-blue-500/10 focus:border-blue-500',
        orange: 'focus:ring-orange-500/10 focus:border-orange-500'
    };
    const c = colors[color] || colors.blue;

    let html = `
        <div class="col-span-2">
            <select class="w-full px-4 py-3.5 bg-gray-50 border border-gray-100 rounded-2xl text-sm font-bold focus:ring-4 ${c} focus:outline-none transition-all"
                onchange="document.getElementById('${inputId}').value = this.value; window._checkAutoBanState();">
                <option value="">-- Select Sub-Classification --</option>
                ${options.map(opt => `<option value="${opt}">${opt}</option>`).join('')}
            </select>
        </div>
    `;
    container.innerHTML = html;
}

window._checkAutoBanState = function() {
    const typeVal = document.getElementById('incidentTypeSelect').value;
    const sevVal = document.getElementById('severitySelect').value;
    
    // Check both potential sub-classification inputs based on mode
    const subInput = document.getElementById('subClassificationInput')?.value || '';
    const trafSubInput = document.getElementById('trafficSubClassificationInput')?.value || '';
    const activeSubValue = subInput || trafSubInput;
    
    const meta = classificationsMeta[typeVal] || { autoBan: false, banValue: '' };
    const warning = document.getElementById('autoBanWarning');
    const label = document.getElementById('banTriggerLabel');
    if (!warning) return;

    let shouldBan = false;
    let reason = '';

    if (sevVal === 'critical') {
        shouldBan = true;
        reason = "Critical severity triggers an automatic driver ban.";
    } else if (meta.autoBan && meta.banValue && activeSubValue.trim() === meta.banValue.trim()) {
        shouldBan = true;
        reason = `Selecting "${meta.banValue}" triggers an automatic driver ban.`;
    }

    if (shouldBan) {
        warning.classList.remove('hidden');
        if (label) label.textContent = reason;
    } else {
        warning.classList.add('hidden');
    }
};

// ─── Searchable Dropdowns (Unit/Driver) ───
// When a unit is selected, limit driver suggestions to its assigned drivers.
// If null/empty, show all drivers.
var allowedDriverIds = null;

window._prefillManualStolenFromSelectedDriver = function (driverOpt) {
    const sec = document.getElementById('manualStolenDetailSection');
    if (!sec || sec.classList.contains('hidden')) return;
    const nameEl = document.getElementById('stolen_driver_detail_name');
    const contactEl = document.getElementById('stolen_driver_detail_contact');
    if (!nameEl || !contactEl) return;
    let opt = driverOpt;
    if (!opt) {
        const hid = document.getElementById('incidentDriverId');
        const id = hid && hid.value;
        if (id) {
            opt = document.querySelector('.driver-search-option[data-id="' + String(id) + '"]');
        }
    }
    if (opt && opt.dataset) {
        if (!String(nameEl.value).trim()) nameEl.value = opt.dataset.name || '';
        if (!String(contactEl.value).trim()) contactEl.value = opt.dataset.contact || '';
    } else {
        const disp = document.getElementById('driverSearchDisplay');
        if (disp && disp.value && !String(nameEl.value).trim()) nameEl.value = disp.value;
    }
};

function setDriverOptionsVisibility(allowedIds) {
    const allowed = new Set((allowedIds || []).map(String).filter(v => v && v !== '0' && v !== 'null'));
    const opts = document.querySelectorAll('.driver-search-option');
    opts.forEach(opt => {
        const id = String(opt.dataset.id || '');
        const isMatch = (allowed.size === 0 || allowed.has(id));
        opt.setAttribute('data-unit-match', isMatch ? '1' : '0');
        
        // Toggle Recommended Badge
        const badge = opt.querySelector('.recommend-badge');
        if (badge) {
            if (allowed.size > 0 && isMatch) {
                badge.classList.remove('hidden');
            } else {
                badge.classList.add('hidden');
            }
        }

        // Initial visibility based on unit match
        opt.style.display = isMatch ? 'block' : 'none';
    });
}

function filterDropdown(input, optClass) {
    const q = input.value.toLowerCase().trim();
    const isDriverSearch = (optClass === 'driver-search-option');
    
    document.querySelectorAll('.' + optClass).forEach(opt => {
        const text = opt.innerText.toLowerCase();
        const matchesQuery = (!q || text.includes(q));
        
        if (isDriverSearch && allowedDriverIds) {
            // If searching (q has value), show all matching drivers.
            // If just focusing (q is empty), show only unit-matched drivers.
            const isUnitMatch = opt.getAttribute('data-unit-match') === '1';
            if (q) {
                opt.style.display = matchesQuery ? 'block' : 'none';
            } else {
                opt.style.display = isUnitMatch ? 'block' : 'none';
            }
        } else {
            opt.style.display = matchesQuery ? 'block' : 'none';
        }
    });
}

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

            if (options === 'driver-search-option' && typeof window._prefillManualStolenFromSelectedDriver === 'function') {
                window._prefillManualStolenFromSelectedDriver(opt);
            }

            // Robust Unit -> Driver Auto-fill
            if (options === 'unit-search-option') {
                const driverHidden = document.getElementById('incidentDriverId');
                const driverDisplay = document.getElementById('driverSearchDisplay');
                const drvId1 = opt.dataset.driverId;
                const drvId2 = opt.dataset.secondaryDriverId;

                const ids = [drvId1, drvId2].filter(v => v && v !== 'null' && v !== '' && v !== '0');
                
                // Update global state
                allowedDriverIds = ids.length ? ids : null;
                setDriverOptionsVisibility(allowedDriverIds);

                if (driverHidden) driverHidden.value = '';
                if (driverDisplay) {
                    driverDisplay.value = '';
                    // If exactly 1 driver assigned, auto-fill for convenience.
                    if (ids.length === 1) {
                        const driverOpt = document.querySelector(`.driver-search-option[data-id="${ids[0]}"]`);
                        if (driverOpt) {
                            driverHidden.value = ids[0];
                            driverDisplay.value = driverOpt.dataset.name;
                            if (typeof window._prefillManualStolenFromSelectedDriver === 'function') {
                                window._prefillManualStolenFromSelectedDriver(driverOpt);
                            }
                        }
                    } else {
                        // For shared units or no assigned: open dropdown immediately so suggestions are visible
                        filterDropdown(driverDisplay, 'driver-search-option');
                        const driverDrop = document.getElementById('driverSearchDropdown');
                        driverDrop?.classList.remove('hidden');
                        driverDrop?.classList.add('flex');
                        // Put focus on driver input so the user can pick D1/D2 immediately
                        setTimeout(() => driverDisplay.focus(), 0);
                    }
                }
            }
        };

        dInput.onfocus = () => { 
            filterDropdown(dInput, options); 
            drop.classList.remove('hidden'); 
            drop.classList.add('flex'); 
        };
        dInput.oninput = () => { filterDropdown(dInput, options); drop.classList.remove('hidden'); drop.classList.add('flex'); };
        dInput.onblur = () => { setTimeout(() => { if (drop) { drop.classList.add('hidden'); drop.classList.remove('flex'); } }, 200); };
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

            // Handle Dynamic Sections for Edit
            window.handleTypeChange(data.incident_type, 'edit');
            
            // Populate sub-fields if any
            if (data.sub_classification) {
                const subInput = document.getElementById('edit-subClassificationInput');
                const trafInput = document.getElementById('edit-trafficSubClassificationInput');
                if (subInput) subInput.value = data.sub_classification;
                if (trafInput) trafInput.value = data.sub_classification;
                
                // Find and select in the dropdowns
                const selects = document.querySelectorAll('#editIncidentModal select');
                selects.forEach(s => {
                    for(let i=0; i<s.options.length; i++) {
                        if(s.options[i].value === data.sub_classification) {
                            s.selectedIndex = i;
                            break;
                        }
                    }
                });
            }

            if (data.traffic_fine_amount) {
                const fineInput = document.getElementById('edit_traffic_fine_amount');
                if (fineInput) fineInput.value = data.traffic_fine_amount;
            }
            
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
@endsection
