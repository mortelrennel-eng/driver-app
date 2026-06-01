@extends('layouts.app')

@section('title', 'Maintenance - Euro System')
@section('page-heading', 'Maintenance Management')
@section('page-subheading', 'Track unit maintenance records')

@section('content')

<style>
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
    .search-dropdown:not(.hidden) {
        display: flex;
    }
    .search-option {
        padding: 0.5rem 0.75rem;
        cursor: pointer;
        border-bottom: 1px solid #f3f4f6;
    }
    .search-option:last-child {
        border-bottom: none;
    }
    .search-option:hover {
        background-color: #fefce8;
    }
    .recommended-option {
        background-color: #f0fdf4 !important;
        border-left: 4px solid #22c55e !important;
        order: -1;
    }
    .recommended-option:hover {
        background-color: #dcfce7 !important;
    }
    .suggested-badge {
        background-color: #22c55e;
        color: white;
        font-size: 9px;
        font-weight: 800;
        padding: 1px 6px;
        border-radius: 9999px;
        text-transform: uppercase;
        letter-spacing: 0.025em;
        margin-left: 0.5rem;
        box-shadow: 0 1px 2px rgba(0,0,0,0.05);
    }
    .parts-cart-item {
        border-bottom: 1px solid #f3f4f6;
        padding: 0.75rem 0.5rem;
    }
    .parts-cart-item:last-child {
        border-bottom: none;
    }
    .modern-badge {
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    .parts-list-summary {
        max-height: 200px;
        overflow-y: auto;
    }
    .modern-table-sep {
        border-collapse: separate;
        border-spacing: 0 0.6rem;
    }
    .modern-row {
        background-color: white;
        box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05);
        transition: all 0.2s ease-in-out;
    }
    .modern-row:hover {
        box-shadow: 0 10px 15px -3px rgba(234, 179, 8, 0.2), 0 4px 6px -2px rgba(234, 179, 8, 0.1);
        transform: translateY(-2px);
    }
    .modern-row td:first-child {
        border-top-left-radius: 0.75rem;
        border-bottom-left-radius: 0.75rem;
        border-left: 4px solid transparent;
    }
    .modern-row:hover td:first-child {
        border-left-color: #eab308;
    }
    .modern-row td:last-child {
        border-top-right-radius: 0.75rem;
        border-bottom-right-radius: 0.75rem;
    }
</style>

@php
    if(!function_exists('renderSparkline')) {
        function renderSparkline($data, $colorClass) {
            if(empty($data)) return '';
            $max = max($data) > 0 ? max($data) : 1;
            $min = min($data);
            if ($max == $min) { $max = $min + 1; }
            $height = 16; 
            $width = 60; 
            $points = [];
            $step = $width / (count($data) - 1);
            
            foreach($data as $i => $val) {
                $x = $i * $step;
                $y = $height - ((($val - $min) / ($max - $min)) * $height);
                $points[] = "{$x},{$y}";
            }
            $pointsStr = implode(' ', $points);
            return '<svg class="w-14 h-4 opacity-80" viewBox="-2 -2 ' . ($width+4) . ' ' . ($height+4) . '" fill="none" xmlns="http://www.w3.org/2000/svg">
                <polyline points="'.$pointsStr.'" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="'.$colorClass.'"/>
            </svg>';
        }
    }
@endphp

{{-- Stats --}}
<div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
    <!-- Card 1: Total Records -->
    <div class="relative overflow-hidden bg-white rounded-2xl shadow-sm border border-gray-100 border-l-[6px] border-l-blue-800 p-5 text-center group hover:shadow-xl hover:-translate-y-1.5 transition-all duration-300">
        <div class="absolute -right-4 -bottom-4 opacity-[0.03] group-hover:opacity-[0.06] transition-opacity duration-500">
            <i data-lucide="folder-open" class="w-28 h-28 text-gray-900"></i>
        </div>
        <div class="w-12 h-12 rounded-full bg-blue-50 flex items-center justify-center mb-3 mx-auto shadow-[0_0_15px_rgba(30,58,138,0.2)] transition-transform group-hover:scale-110 duration-300">
            <i data-lucide="folder-open" class="w-6 h-6 text-blue-800"></i>
        </div>
        <p class="text-3xl font-black text-gray-800 tracking-tighter relative z-10">{{ $totals->total_count ?? 0 }}</p>
        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mt-1 relative z-10">Total Records</p>
        <div class="flex items-center justify-center gap-2 mt-3 relative z-10">
            {!! renderSparkline($trends['total'] ?? [], 'text-gray-400') !!}
            <span class="text-[8px] font-black text-gray-400 uppercase tracking-widest">7D Trend</span>
        </div>
    </div>

    <!-- Card 2: Pending -->
    <div class="relative overflow-hidden bg-gradient-to-br from-yellow-50/80 to-white rounded-2xl shadow-sm border border-yellow-100/50 border-l-[6px] border-l-orange-500 p-5 text-center group hover:shadow-xl hover:shadow-yellow-100/50 hover:-translate-y-1.5 transition-all duration-300">
        <div class="absolute -right-4 -bottom-4 opacity-[0.04] group-hover:opacity-[0.08] transition-opacity duration-500">
            <i data-lucide="clock" class="w-28 h-28 text-yellow-600"></i>
        </div>
        <div class="absolute top-0 right-0 w-32 h-32 bg-yellow-400/5 blur-2xl rounded-full scale-150 group-hover:bg-yellow-400/10 transition-colors duration-500"></div>
        <div class="w-12 h-12 rounded-full bg-yellow-50 flex items-center justify-center mb-3 mx-auto shadow-[0_0_15px_rgba(234,179,8,0.25)] transition-transform group-hover:scale-110 duration-300">
            <i data-lucide="clock" class="w-6 h-6 text-yellow-600"></i>
        </div>
        <p class="text-3xl font-black text-yellow-600 tracking-tighter relative z-10 drop-shadow-sm">{{ $totals->pending_count ?? 0 }}</p>
        <p class="text-[10px] font-black text-yellow-700/60 uppercase tracking-widest mt-1 relative z-10">Pending</p>
        <div class="flex items-center justify-center gap-2 mt-3 relative z-10">
            {!! renderSparkline($trends['pending'] ?? [], 'text-yellow-500') !!}
            <span class="text-[8px] font-black text-yellow-600/70 uppercase tracking-widest">7D Trend</span>
        </div>
    </div>

    <!-- Card 3: Active Work -->
    <div class="relative overflow-hidden bg-gradient-to-br from-blue-50/80 to-white rounded-2xl shadow-sm border border-blue-100/50 border-l-[6px] border-l-indigo-500 p-5 text-center group hover:shadow-xl hover:shadow-blue-100/50 hover:-translate-y-1.5 transition-all duration-300">
        <div class="absolute -right-4 -bottom-4 opacity-[0.04] group-hover:opacity-[0.08] transition-opacity duration-500">
            <i data-lucide="wrench" class="w-28 h-28 text-blue-600"></i>
        </div>
        <div class="absolute top-0 right-0 w-32 h-32 bg-blue-400/5 blur-2xl rounded-full scale-150 group-hover:bg-blue-400/10 transition-colors duration-500"></div>
        <div class="w-12 h-12 rounded-full bg-indigo-50 flex items-center justify-center mb-3 mx-auto shadow-[0_0_15px_rgba(79,70,229,0.25)] transition-transform group-hover:scale-110 duration-300">
            <i data-lucide="wrench" class="w-6 h-6 text-indigo-600"></i>
        </div>
        <p class="text-3xl font-black text-blue-600 tracking-tighter relative z-10 drop-shadow-sm">{{ $totals->in_progress_count ?? 0 }}</p>
        <p class="text-[10px] font-black text-blue-700/60 uppercase tracking-widest mt-1 relative z-10">Active Work</p>
        <div class="flex items-center justify-center gap-2 mt-3 relative z-10">
            {!! renderSparkline($trends['active'] ?? [], 'text-blue-500') !!}
            <span class="text-[8px] font-black text-blue-600/70 uppercase tracking-widest">7D Trend</span>
        </div>
    </div>

    <!-- Card 4: Total Cost -->
    <div class="relative overflow-hidden bg-gradient-to-br from-green-50/80 to-white rounded-2xl shadow-sm border border-green-100/50 border-l-[6px] border-l-emerald-500 p-5 text-center group hover:shadow-xl hover:shadow-green-100/50 hover:-translate-y-1.5 transition-all duration-300">
        <div class="absolute -right-2 -bottom-8 opacity-[0.04] group-hover:opacity-[0.08] transition-opacity duration-500">
            <span class="text-[120px] font-black text-green-700 leading-none font-serif">₱</span>
        </div>
        <div class="absolute top-0 right-0 w-32 h-32 bg-green-400/10 blur-2xl rounded-full scale-150 group-hover:bg-green-400/20 transition-colors duration-500"></div>
        <div class="w-12 h-12 rounded-full bg-green-50 flex items-center justify-center mb-3 mx-auto shadow-[0_0_15px_rgba(16,185,129,0.25)] transition-transform group-hover:scale-110 duration-300">
            <i data-lucide="banknote" class="w-6 h-6 text-green-700"></i>
        </div>
        <p class="text-2xl font-black text-green-700 tracking-tighter relative z-10 drop-shadow-sm">{{ formatCurrency($totals->total_cost ?? 0) }}</p>
        <p class="text-[10px] font-black text-green-700/60 uppercase tracking-widest mt-1 relative z-10">Total Cost</p>
        <div class="flex items-center justify-center gap-2 mt-3 relative z-10">
            {!! renderSparkline($trends['cost'] ?? [], 'text-green-500') !!}
            <span class="text-[8px] font-black text-green-600/70 uppercase tracking-widest">7D Trend</span>
        </div>
    </div>
</div>

{{-- Filter + Add --}}
<div class="bg-white rounded-lg shadow p-4 mb-5">
    <form method="GET" action="{{ route('maintenance.index') }}" class="flex flex-wrap gap-3">
        <input type="text" name="search" value="{{ $search }}" placeholder="Search plate or mechanic..."
            class="flex-1 min-w-[150px] px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-yellow-500 focus:outline-none" autocomplete="off">
        <select name="status" onchange="this.form.submit()" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-yellow-500 focus:outline-none">
            <option value="">All Status</option>
            <option value="pending" @selected($status=='pending')>Pending</option>
            <option value="in_shop" @selected($status=='in_shop' || $status=='in_progress')>In Shop</option>
            <option value="testing" @selected($status=='testing')>Testing</option>
            <option value="completed" @selected($status=='completed')>Completed</option>
            <option value="cancelled" @selected($status=='cancelled')>Cancelled</option>
        </select>
        <select name="type" onchange="this.form.submit()" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-yellow-500 focus:outline-none">
            <option value="">All Types</option>
            <option value="preventive" @selected($type=='preventive')>Preventive</option>
            <option value="corrective" @selected($type=='corrective')>Corrective</option>
            <option value="emergency" @selected($type=='emergency')>Emergency</option>
        </select>
        <button type="button" onclick="openPurchaseHistoryModal()"
            class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 text-sm flex items-center gap-2">
            <i data-lucide="history" class="w-4 h-4"></i> History
        </button>
        <button type="button" onclick="openPartsModal()"
            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm flex items-center gap-2">
            <i data-lucide="package" class="w-4 h-4"></i> Manage Inventory
        </button>
        <button type="button" onclick="openAddMaint()"
            class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 text-sm flex items-center gap-2">
            <i data-lucide="plus" class="w-4 h-4"></i> Add Record
        </button>
    </form>
</div>

{{-- Table --}}
<div class="overflow-x-auto pb-4">
    <table class="min-w-full text-sm modern-table-sep">
        <thead>
            <tr>
                <th class="px-4 py-2 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Unit / Driver</th>
                <th class="px-4 py-2 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Type</th>
                <th class="px-4 py-2 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Mechanic</th>
                <th class="px-4 py-2 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Date Started</th>
                <th class="px-4 py-2 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Date Done</th>
                <th class="px-4 py-2 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Cost</th>
                <th class="px-4 py-2 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Status</th>
                <th class="px-4 py-2 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($records as $r)
            <tr class="modern-row cursor-pointer group"
                onclick="openViewMaint({{ $r->id }})">
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-2">
                           <p class="font-bold text-gray-900 group-hover:text-yellow-700 transition-colors">{{ $r->plate_number }}</p>
                           @if($r->date_started == date('Y-m-d') && $r->status != 'completed')
                               <span class="px-1.5 py-0.5 bg-red-100 text-red-600 text-[9px] font-black uppercase rounded animate-pulse">Today</span>
                           @endif
                           <i data-lucide="external-link" class="w-3 h-3 text-gray-300 opacity-0 group-hover:opacity-100 transition-opacity"></i>
                        </div>
                        @if($r->driver_name)
                            <p class="text-[10px] text-gray-400 font-bold uppercase truncate max-w-[120px] group-hover:text-yellow-600 transition-colors" title="{{ $r->driver_name }}">
                                {{ $r->driver_name }}
                            </p>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        @if($r->maintenance_type === 'emergency')
                            <span class="inline-flex items-center gap-1 px-2 py-1 text-[10px] font-black rounded-lg bg-red-600 text-white uppercase tracking-wider shadow-sm">
                                🚨 Emergency
                            </span>
                        @elseif($r->maintenance_type === 'corrective')
                            <span class="inline-flex items-center gap-1 px-2 py-1 text-[10px] font-black rounded-lg bg-orange-100 text-orange-800 uppercase tracking-wider border border-orange-200">
                                🔧 Corrective
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 px-2 py-1 text-[10px] font-black rounded-lg bg-blue-100 text-blue-800 uppercase tracking-wider border border-blue-200">
                                🛡️ Preventive
                            </span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        @if(!$r->mechanic_name || $r->mechanic_name === '—')
                            <button onclick="openEditMaint(document.querySelector('button[data-id=\'{{ $r->id }}\']')); event.stopPropagation();" data-id="{{ $r->id }}" class="flex items-center gap-1.5 px-2 py-1 border border-dashed border-gray-300 rounded-lg text-[10px] font-bold text-gray-500 hover:text-blue-600 hover:border-blue-400 hover:bg-blue-50 transition w-max">
                                <i data-lucide="user-plus" class="w-3 h-3"></i> Assign Mechanic
                            </button>
                        @else
                            <div class="flex flex-col gap-1.5">
                                @foreach(array_filter(array_map('trim', explode(',', $r->mechanic_name))) as $mech)
                                    @php
                                        $initials = collect(explode(' ', $mech))->map(function($n) { return substr($n, 0, 1); })->take(2)->implode('');
                                    @endphp
                                    <div class="flex items-center gap-2">
                                        <div class="w-6 h-6 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center text-[10px] font-black uppercase shrink-0 shadow-sm border border-blue-200">
                                            {{ $initials }}
                                        </div>
                                        <span class="text-xs font-bold text-gray-700 truncate max-w-[140px]" title="{{ $mech }}">{{ $mech }}</span>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                        <div class="text-[9px] text-gray-400 mt-1.5 font-bold tracking-tight">
                            <span title="Input by {{ $r->creator_name ?? 'System' }}">In: <span class="uppercase text-gray-500">{{ $r->creator_name ?? 'System' }}</span></span>
                            @if(isset($r->editor_name) && $r->editor_name)
                                <span class="ml-1" title="Last edit by {{ $r->editor_name }}">Ed: <span class="uppercase text-gray-500">{{ $r->editor_name }}</span></span>
                            @endif
                        </div>
                    </td>
                    <td class="px-4 py-3 text-gray-600">{{ formatDate($r->date_started) }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ $r->date_completed ? formatDate($r->date_completed) : '—' }}</td>
                    <td class="px-4 py-3 font-semibold text-gray-900">{{ formatCurrency($r->cost) }}</td>
                    <td class="px-4 py-3 min-w-[240px]">
                        @php
                            $s = $r->status ?? 'pending';
                            $step = $s === 'cancelled' ? 0 : ($s === 'completed' ? 4 : ($s === 'testing' ? 3 : (in_array($s, ['in_shop', 'in_progress']) ? 2 : 1)));
                        @endphp
                        @if($step === 0)
                            <span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-800 font-black uppercase tracking-widest">Cancelled</span>
                        @else
                            <div class="flex items-center w-full mt-1.5">
                                <!-- Pending -->
                                <div class="flex flex-col items-center relative flex-1 group cursor-help" title="Pending">
                                    <div class="w-5 h-5 rounded-full {{ $step >= 1 ? 'bg-yellow-500' : 'bg-gray-200' }} z-10 flex items-center justify-center shadow-sm">
                                        @if($step > 1) <i data-lucide="check" class="w-3 h-3 text-white"></i> @endif
                                    </div>
                                    <div class="absolute top-2.5 left-1/2 w-full h-0.5 {{ $step >= 2 ? 'bg-blue-500' : 'bg-gray-200' }} -z-0"></div>
                                    <span class="text-[9px] font-black mt-1.5 {{ $step >= 1 ? 'text-yellow-600' : 'text-gray-400' }} uppercase tracking-wider">Pending</span>
                                </div>
                                
                                <!-- In Shop -->
                                <div class="flex flex-col items-center relative flex-1 group cursor-help" title="In Shop">
                                    <div class="absolute top-2.5 right-1/2 w-full h-0.5 {{ $step >= 2 ? 'bg-blue-500' : 'bg-gray-200' }} -z-0"></div>
                                    <div class="w-5 h-5 rounded-full {{ $step >= 2 ? 'bg-blue-500' : 'bg-gray-200' }} z-10 flex items-center justify-center shadow-sm">
                                         @if($step > 2) <i data-lucide="check" class="w-3 h-3 text-white"></i> @endif
                                    </div>
                                    <div class="absolute top-2.5 left-1/2 w-full h-0.5 {{ $step >= 3 ? 'bg-purple-500' : 'bg-gray-200' }} -z-0"></div>
                                    <span class="text-[9px] font-black mt-1.5 {{ $step >= 2 ? 'text-blue-600' : 'text-gray-400' }} uppercase tracking-wider text-center leading-none">In Shop</span>
                                </div>
                                
                                <!-- Testing -->
                                <div class="flex flex-col items-center relative flex-1 group cursor-help" title="Testing">
                                    <div class="absolute top-2.5 right-1/2 w-full h-0.5 {{ $step >= 3 ? 'bg-purple-500' : 'bg-gray-200' }} -z-0"></div>
                                    <div class="w-5 h-5 rounded-full {{ $step >= 3 ? 'bg-purple-500' : 'bg-gray-200' }} z-10 flex items-center justify-center shadow-sm">
                                         @if($step > 3) <i data-lucide="check" class="w-3 h-3 text-white"></i> @endif
                                    </div>
                                    <div class="absolute top-2.5 left-1/2 w-full h-0.5 {{ $step >= 4 ? 'bg-green-500' : 'bg-gray-200' }} -z-0"></div>
                                    <span class="text-[9px] font-black mt-1.5 {{ $step >= 3 ? 'text-purple-600' : 'text-gray-400' }} uppercase tracking-wider">Testing</span>
                                </div>

                                <!-- Completed -->
                                <div class="flex flex-col items-center relative flex-1 group cursor-help" title="Completed">
                                    <div class="absolute top-2.5 right-1/2 w-full h-0.5 {{ $step >= 4 ? 'bg-green-500' : 'bg-gray-200' }} -z-0"></div>
                                    <div class="w-5 h-5 rounded-full {{ $step >= 4 ? 'bg-green-500' : 'bg-gray-200' }} z-10 flex items-center justify-center shadow-sm">
                                         @if($step == 4) <i data-lucide="check" class="w-3 h-3 text-white"></i> @endif
                                    </div>
                                    <span class="text-[9px] font-black mt-1.5 {{ $step >= 4 ? 'text-green-600' : 'text-gray-400' }} uppercase tracking-wider text-center leading-none">Done</span>
                                </div>
                            </div>
                        @endif
                    </td>
                    <td class="px-4 py-3" onclick="event.stopPropagation()">
                        <div class="flex gap-2">
                            {{-- Toggle Complete --}}
                            <form method="POST" action="{{ route('maintenance.toggle-complete', $r->id) }}">
                                @csrf
                                <button type="submit" title="{{ $r->date_completed ? 'Mark as Incomplete' : 'Mark as Complete' }}" 
                                    class="{{ $r->date_completed ? 'text-orange-600 hover:text-orange-900 hover:bg-orange-50' : 'text-green-600 hover:text-green-900 hover:bg-green-50' }} p-1 rounded transition">
                                    <i data-lucide="{{ $r->date_completed ? 'rotate-ccw' : 'check-circle' }}" class="w-4 h-4"></i>
                                </button>
                            </form>

                            {{-- Advance Maintenance Stage (only for non-completed records) --}}
                            @if(!$r->date_completed && $r->status !== 'cancelled')
                            <form method="POST" action="{{ route('maintenance.toggle-in-progress', $r->id) }}">
                                @csrf
                                <button type="submit"
                                    title="Advance Stage (Pending -> In Shop -> Testing)"
                                    class="text-blue-500 hover:text-blue-800 hover:bg-blue-50 p-1 rounded transition">
                                    <i data-lucide="fast-forward" class="w-4 h-4"></i>
                                </button>
                            </form>
                            @endif

                            <button onclick="openEditMaint(this)" data-id="{{ $r->id }}" class="text-blue-600 hover:text-blue-900 p-1 rounded hover:bg-blue-50 transition"><i data-lucide="edit" class="w-4 h-4"></i></button>
                            <form method="POST" action="{{ route('maintenance.destroy', $r->id) }}" onsubmit="return confirm('Delete?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-900 p-1 rounded hover:bg-red-50 transition"><i data-lucide="trash-2" class="w-4 h-4"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-4 py-12 text-center text-gray-400">
                        <i data-lucide="wrench" class="w-12 h-12 mx-auto mb-3 text-gray-300"></i>
                        <p>No maintenance records found.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($pagination['total_pages'] > 1)
    <div class="px-4 py-3 flex items-center justify-between">
        <div class="flex-1 flex justify-between sm:hidden">
            @if($pagination['has_prev'])
                <a href="?page={{ $pagination['prev_page'] }}&search={{ urlencode($search) }}&status={{ urlencode($status) }}&type={{ urlencode($type) }}" class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-xs font-bold rounded-xl text-gray-700 bg-white hover:bg-gray-50">Previous</a>
            @endif
            @if($pagination['has_next'])
                <a href="?page={{ $pagination['next_page'] }}&search={{ urlencode($search) }}&status={{ urlencode($status) }}&type={{ urlencode($type) }}" class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-xs font-bold rounded-xl text-gray-700 bg-white hover:bg-gray-50">Next</a>
            @endif
        </div>
        <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between font-bold text-[10px] text-gray-500 uppercase tracking-widest">
            <div>
                <p>Showing <span class="text-gray-900">{{ min($pagination['total_items'], ($pagination['page'] - 1) * 10 + 1) }}</span> to <span class="text-gray-900">{{ min($pagination['total_items'], $pagination['page'] * 10) }}</span> of <span class="text-gray-900">{{ $pagination['total_items'] }}</span> records</p>
            </div>
            <div>
                <nav class="relative z-0 inline-flex rounded-xl shadow-sm -space-x-px" aria-label="Pagination">
                    @if($pagination['has_prev'])
                        <a href="?page={{ $pagination['prev_page'] }}&search={{ urlencode($search) }}&status={{ urlencode($status) }}&type={{ urlencode($type) }}" class="relative inline-flex items-center px-2 py-2 rounded-l-xl border border-gray-300 bg-white text-gray-400 hover:bg-gray-50">
                            <i data-lucide="chevron-left" class="w-4 h-4"></i>
                        </a>
                    @endif

                    @php
                        $start = max(1, $pagination['page'] - 2);
                        $end = min($pagination['total_pages'], $pagination['page'] + 2);
                    @endphp

                    @for($i = $start; $i <= $end; $i++)
                        <a href="?page={{ $i }}&search={{ urlencode($search) }}&status={{ urlencode($status) }}&type={{ urlencode($type) }}" 
                           class="relative inline-flex items-center px-4 py-2 border text-[11px] font-black {{ $i === $pagination['page'] ? 'z-10 bg-yellow-50 border-yellow-500 text-yellow-600' : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50' }}">
                            {{ $i }}
                        </a>
                    @endfor

                    @if($pagination['has_next'])
                        <a href="?page={{ $pagination['next_page'] }}&search={{ urlencode($search) }}&status={{ urlencode($status) }}&type={{ urlencode($type) }}" class="relative inline-flex items-center px-2 py-2 rounded-r-xl border border-gray-300 bg-white text-gray-400 hover:bg-gray-50">
                            <i data-lucide="chevron-right" class="w-4 h-4"></i>
                        </a>
                    @endif
                </nav>
            </div>
        </div>
    </div>
    @endif
</div>

{{-- Add Modal --}}
<div id="addMaintenanceModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-5xl h-[95vh] flex flex-col overflow-hidden">
        {{-- Modal Header (Deep Navy) --}}
        <div class="bg-slate-800 p-4 shrink-0">
            <div class="flex justify-between items-center">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-white bg-opacity-20 rounded-lg">
                        <i data-lucide="wrench" class="w-5 h-5 text-white"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-white leading-tight">Add Maintenance Record</h3>
                        <p class="text-sm text-blue-100 leading-tight">Create a new maintenance job for a unit</p>
                    </div>
                </div>
                <button type="button" onclick="document.getElementById('addMaintenanceModal').classList.add('hidden')" class="text-white hover:text-gray-200 transition-colors">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
        </div>
        
        {{-- Content Area --}}
        <form method="POST" action="{{ route('maintenance.store') }}" class="flex flex-col flex-1 overflow-hidden">
            @csrf
            <div class="p-6 flex-1 overflow-y-auto custom-scrollbar">
                <div class="max-w-3xl mx-auto space-y-4">
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Unit *</label>
                    <div class="relative">
                        <input type="text" id="addUnitDisplay" placeholder="Type to search unit..." required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-yellow-500 focus:outline-none" autocomplete="off">
                        <input type="hidden" name="unit_id" id="addUnitId" required>
                        <input type="hidden" name="driver_id" id="addDriverId">
                        <div id="addUnitDropdown" class="search-dropdown hidden">
                            @foreach($units as $u)
                            <div class="search-option unit-option" 
                                data-id="{{ $u->id }}" 
                                data-name="{{ $u->plate_number }}"
                                data-driver-id="{{ $u->driver_id }}"
                                data-secondary-id="{{ $u->secondary_driver_id }}"
                                data-driver-name="{{ $drivers->where('id', $u->driver_id)->first()->name ?? '' }}">
                                <div class="font-medium text-xs text-gray-900">{{ $u->plate_number }}</div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Driver Assigned</label>
                    <div class="relative">
                        <input type="text" id="addDriverDisplay" placeholder="Auto-fills on unit select..."
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-yellow-500 focus:outline-none" autocomplete="off">
                        <div id="addDriverDropdown" class="search-dropdown hidden">
                            @foreach($drivers as $d)
                            <div class="search-option driver-option" data-id="{{ $d->id }}" data-name="{{ $d->name }}">
                                <div class="font-medium text-xs text-gray-900">{{ $d->name }}</div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Type *</label>
                        <select name="maintenance_type" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-yellow-500 focus:outline-none" required>
                            <option value="preventive">Preventive</option>
                            <option value="corrective">Corrective</option>
                            <option value="emergency">Emergency</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Status *</label>
                        <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-yellow-500 focus:outline-none" required>
                            <option value="pending">Pending</option>
                            <option value="in_shop">In Shop</option>
                            <option value="testing">Testing</option>
                            <option value="completed">Completed</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Description (Optional)</label>
                    <textarea name="description" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-yellow-500 focus:outline-none"></textarea>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Date Started *</label>
                        <input type="date" name="date_started" value="{{ date('Y-m-d') }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-yellow-500 focus:outline-none" required>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Date Completed</label>
                        <input type="date" name="date_completed" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-yellow-500 focus:outline-none">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1 uppercase tracking-tight">Mechanic Name *</label>
                    <div class="space-y-2">
                        <div class="relative">
                            <input type="text" name="mechanic_name[]" id="addMechDisplay1" placeholder="Search primary mechanic..." required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-yellow-500 focus:outline-none">
                            <div id="addMechDropdown1" class="search-dropdown hidden">
                                @foreach($staff as $s)
                                <div class="search-option mech-option" data-name="{{ $s->name }}" onclick="selectMech('addMechDisplay1', '{{ $s->name }}', 'addMechDropdown1')">
                                    <div class="font-medium text-xs text-gray-900">{{ $s->name }}</div>
                                    <div class="text-[10px] text-gray-500">{{ $s->role }}</div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        
                        <div id="addSecondMechRow" class="hidden animate-fade-in">
                            <div class="relative">
                                <input type="text" name="mechanic_name[]" id="addMechDisplay2" placeholder="Search secondary mechanic..."
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-yellow-500 focus:outline-none">
                                <div id="addMechDropdown2" class="search-dropdown hidden">
                                    @foreach($staff as $s)
                                    <div class="search-option mech-option" data-name="{{ $s->name }}" onclick="selectMech('addMechDisplay2', '{{ $s->name }}', 'addMechDropdown2')">
                                        <div class="font-medium text-xs text-gray-900">{{ $s->name }}</div>
                                        <div class="text-[10px] text-gray-500">{{ $s->role }}</div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <button type="button" id="btnAddSecondMech" onclick="toggleSecondMech('add')" class="text-[10px] text-blue-600 font-bold hover:underline flex items-center gap-1">
                            <i data-lucide="plus-circle" class="w-3 h-3"></i> Add Second Mechanic
                        </button>
                    </div>
                </div>

                <!-- Parts Selection -->
                <div class="p-3 bg-gray-50 rounded-lg border border-gray-200">
                    <div class="flex justify-between items-center mb-2">
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-tight">Spare Parts Selection</label>
                        <button type="button" onclick="openQuickAddPart()" class="text-[10px] bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full font-bold hover:bg-blue-200 transition">
                            + New Part
                        </button>
                    </div>
                    
                    <div class="relative mb-3">
                        <input type="text" id="addPartDisplay" placeholder="Type to search parts..."
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        <div id="addPartDropdown" class="search-dropdown hidden">
                            @foreach($spare_parts as $p)
                            @php $isOut = ($p->stock_quantity ?? 0) <= 0; @endphp
                            <div class="search-option part-option {{ $isOut ? 'opacity-50 grayscale cursor-not-allowed' : '' }}" 
                                data-id="{{ $p->id }}" data-name="{{ $p->name }}" data-price="{{ $p->price }}" data-qty="{{ $p->stock_quantity ?? 0 }}">
                                <div class="flex justify-between items-center">
                                    <div class="flex-1">
                                        <div class="font-medium text-xs text-gray-900">{{ $p->name }}</div>
                                        <div class="text-[9px] {{ $isOut ? 'text-red-500 font-bold' : 'text-gray-400' }}">
                                            {{ $isOut ? '⚠️ Unavailable / Out of Stock' : 'Stock: ' . ($p->stock_quantity ?? 0) }}
                                        </div>
                                    </div>
                                    <div class="text-[10px] font-bold {{ $isOut ? 'text-gray-400' : 'text-blue-600' }}">₱{{ number_format($p->price, 2) }}</div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <div id="partsCartItems" class="space-y-2 mb-3 max-h-[150px] overflow-y-auto pr-1">
                        <!-- Items will be injected here -->
                    </div>

                    <div class="flex justify-between items-center pt-2 border-t border-gray-200">
                        <span class="text-xs font-bold text-gray-500 uppercase">Parts Total:</span>
                        <span id="partsTotalDisplay" class="text-sm font-black text-gray-900">₱0.00</span>
                    </div>
                </div>

                <!-- Other Costs -->
                <div class="p-3 bg-gray-50 rounded-lg border border-gray-200">
                    <div class="flex justify-between items-center mb-2">
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-tight">Additional Service / Other Costs</label>
                        <button type="button" onclick="addOtherCostRow('add')" class="text-[10px] bg-orange-100 text-orange-700 px-2 py-0.5 rounded-full font-bold hover:bg-orange-200 transition">
                            + Add Service
                        </button>
                    </div>
                    
                    <div id="otherCostsItems" class="space-y-2">
                        <!-- Dynamic items here -->
                    </div>
                </div>

                <input type="hidden" name="parts_data" id="addPartsData">
                
                <div class="pt-4 mt-2 border-t-2 border-dashed border-gray-200">
                    <div class="flex justify-between items-center bg-green-50 p-4 rounded-xl border-2 border-green-200">
                        <div>
                            <span class="block text-[10px] font-black text-green-600 uppercase tracking-widest leading-none mb-1">Grand Total Cost</span>
                            <span id="addTotalCostDisplay" class="text-3xl font-black text-green-700 tabular-nums leading-none line-height-1">₱0.00</span>
                        </div>
                        <input type="hidden" name="cost" id="addTotalCostValue" value="0">
                        <div class="text-right">
                            <i data-lucide="calculator" class="w-8 h-8 text-green-200"></i>
                        </div>
                    </div>
                    <p class="text-[10px] text-gray-400 mt-2 px-1 italic">Calculated sum of all parts and additional services above.</p>
                </div>
            </div>
        </div>
        <div class="p-4 border-t flex justify-end gap-3 shadow-inner bg-gray-50 shrink-0">
                <button type="button" onclick="document.getElementById('addMaintenanceModal').classList.add('hidden')" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 text-sm font-bold transition-all">
                    Cancel
                </button>
                <button type="submit" class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 text-sm font-bold shadow-lg shadow-green-200/50 transition-all flex items-center gap-2">
                    <i data-lucide="check" class="w-4 h-4"></i> Save Record
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Edit Modal --}}
<div id="editMaintenanceModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-5xl h-[95vh] flex flex-col overflow-hidden">
        {{-- Modal Header (Deep Navy) --}}
        <div class="bg-slate-800 p-4 shrink-0">
            <div class="flex justify-between items-center">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-white bg-opacity-20 rounded-lg">
                        <i data-lucide="edit-3" class="w-5 h-5 text-white"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-white leading-tight">Edit Maintenance Record</h3>
                        <p class="text-sm text-blue-100 leading-tight">Modify an existing maintenance job</p>
                    </div>
                </div>
                <button type="button" onclick="document.getElementById('editMaintenanceModal').classList.add('hidden')" class="text-white hover:text-gray-200 transition-colors">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
        </div>
        
        {{-- Content Area --}}
        <form id="editMaintForm" method="POST" class="flex flex-col flex-1 overflow-hidden">
            @csrf @method('PUT')
            <div class="p-6 flex-1 overflow-y-auto custom-scrollbar">
                <div class="max-w-3xl mx-auto space-y-4">
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Unit *</label>
                    <div class="relative">
                        <input type="text" id="editUnitDisplay" placeholder="Type to search unit..." required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-yellow-500 focus:outline-none" autocomplete="off">
                        <input type="hidden" name="unit_id" id="editUnitId" required>
                        <input type="hidden" name="driver_id" id="editDriverId">
                        <div id="editUnitDropdown" class="search-dropdown hidden">
                            @foreach($units as $u)
                            <div class="search-option unit-option" 
                                data-id="{{ $u->id }}" 
                                data-name="{{ $u->plate_number }}"
                                data-driver-id="{{ $u->driver_id }}"
                                data-secondary-id="{{ $u->secondary_driver_id }}"
                                data-driver-name="{{ $drivers->where('id', $u->driver_id)->first()->name ?? '' }}">
                                <div class="font-medium text-xs text-gray-900">{{ $u->plate_number }}</div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Driver Assigned</label>
                    <div class="relative">
                        <input type="text" id="editDriverDisplay" placeholder="Auto-fills on unit select..."
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-yellow-500 focus:outline-none" autocomplete="off">
                        <div id="editDriverDropdown" class="search-dropdown hidden">
                            @foreach($drivers as $d)
                            <div class="search-option driver-option" data-id="{{ $d->id }}" data-name="{{ $d->name }}">
                                <div class="font-medium text-xs text-gray-900">{{ $d->name }}</div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Type *</label>
                        <select name="maintenance_type" id="em_type" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                            <option value="preventive">Preventive</option>
                            <option value="corrective">Corrective</option>
                            <option value="emergency">Emergency</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Status *</label>
                        <select name="status" id="em_status" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                            <option value="pending">Pending</option>
                            <option value="in_shop">In Shop</option>
                            <option value="in_progress" class="hidden">In Progress (Legacy)</option>
                            <option value="testing">Testing</option>
                            <option value="completed">Completed</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-1 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Service / Reported Issue (Optional)</label>
                        <textarea name="description" id="em_description" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-yellow-500 focus:outline-none bg-gray-50"></textarea>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-blue-700 mb-1">Dispatcher Notes (Optional)</label>
                        <textarea name="dispatcher_notes" id="em_dispatcher_notes" rows="2" placeholder="Additional remarks..." class="w-full px-3 py-2 border border-blue-300 rounded-lg text-sm bg-blue-50 focus:ring-2 focus:ring-blue-500 focus:outline-none"></textarea>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1 uppercase tracking-tight">Mechanic Name *</label>
                    <div class="space-y-2">
                        <div class="relative">
                            <input type="text" name="mechanic_name[]" id="editMechDisplay1" placeholder="Search primary mechanic..." required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-yellow-500 focus:outline-none">
                            <div id="editMechDropdown1" class="search-dropdown hidden">
                                @foreach($staff as $s)
                                <div class="search-option mech-option" data-name="{{ $s->name }}" onclick="selectMech('editMechDisplay1', '{{ $s->name }}', 'editMechDropdown1')">
                                    <div class="font-medium text-xs text-gray-900">{{ $s->name }}</div>
                                    <div class="text-[10px] text-gray-500">{{ $s->role }}</div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        
                        <div id="editSecondMechRow" class="hidden">
                            <div class="relative">
                                <input type="text" name="mechanic_name[]" id="editMechDisplay2" placeholder="Search secondary mechanic..."
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-yellow-500 focus:outline-none">
                                <div id="editMechDropdown2" class="search-dropdown hidden">
                                    @foreach($staff as $s)
                                    <div class="search-option mech-option" data-name="{{ $s->name }}" onclick="selectMech('editMechDisplay2', '{{ $s->name }}', 'editMechDropdown2')">
                                        <div class="font-medium text-xs text-gray-900">{{ $s->name }}</div>
                                        <div class="text-[10px] text-gray-500">{{ $s->role }}</div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <button type="button" id="btnEditSecondMech" onclick="toggleSecondMech('edit')" class="text-[10px] text-blue-600 font-bold hover:underline flex items-center gap-1">
                            <i data-lucide="plus-circle" class="w-3 h-3"></i> Add Second Mechanic
                        </button>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Date Started *</label>
                        <input type="date" name="date_started" id="em_date_started" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-yellow-500 focus:outline-none" required>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Date Completed</label>
                        <input type="date" name="date_completed" id="em_date_completed" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-yellow-500 focus:outline-none">
                    </div>
                </div>

                <div class="p-3 bg-gray-50 rounded-lg border border-gray-200">
                    <div class="flex justify-between items-center mb-2">
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-tight">Spare Parts Selection</label>
                        <button type="button" onclick="openQuickAddPart()" class="text-[10px] bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full font-bold hover:bg-blue-200 transition">
                            + New Part
                        </button>
                    </div>
                    
                    <div class="relative mb-3">
                        <input type="text" id="editPartDisplay" placeholder="Type to search parts..."
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        <div id="editPartDropdown" class="search-dropdown hidden">
                            @foreach($spare_parts as $p)
                            @php $isOut = ($p->stock_quantity ?? 0) <= 0; @endphp
                            <div class="search-option part-option {{ $isOut ? 'opacity-50 grayscale cursor-not-allowed' : '' }}" 
                                data-id="{{ $p->id }}" data-name="{{ $p->name }}" data-price="{{ $p->price }}" data-qty="{{ $p->stock_quantity ?? 0 }}">
                                <div class="flex justify-between items-center">
                                    <div class="flex-1">
                                        <div class="font-medium text-xs text-gray-900">{{ $p->name }}</div>
                                        <div class="text-[9px] {{ $isOut ? 'text-red-500 font-bold' : 'text-gray-400' }}">
                                            {{ $isOut ? '⚠️ Unavailable / Out of Stock' : 'Stock: ' . ($p->stock_quantity ?? 0) }}
                                        </div>
                                    </div>
                                    <div class="text-[10px] font-bold {{ $isOut ? 'text-gray-400' : 'text-blue-600' }}">₱{{ number_format($p->price, 2) }}</div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <div id="editPartsCartItems" class="space-y-2 mb-3 max-h-[150px] overflow-y-auto pr-1">
                        <!-- Items will be injected here -->
                    </div>

                    <div class="flex justify-between items-center pt-2 border-t border-gray-200">
                        <span class="text-xs font-bold text-gray-500 uppercase">Parts Total:</span>
                        <span id="editPartsTotalDisplay" class="text-sm font-black text-gray-900">₱0.00</span>
                    </div>
                </div>

                <!-- Other Costs -->
                <div class="p-3 bg-gray-50 rounded-lg border border-gray-200">
                    <div class="flex justify-between items-center mb-2">
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-tight">Additional Service / Other Costs</label>
                        <button type="button" onclick="addOtherCostRow('edit')" class="text-[10px] bg-orange-100 text-orange-700 px-2 py-0.5 rounded-full font-bold hover:bg-orange-200 transition">
                            + Add Service
                        </button>
                    </div>
                    
                    <div id="editOtherCostsItems" class="space-y-2">
                        <!-- Dynamic items here -->
                    </div>
                </div>

                <input type="hidden" name="parts_data" id="editPartsData">

                <div class="pt-4 mt-2 border-t-2 border-dashed border-gray-200">
                    <div class="flex justify-between items-center bg-yellow-50 p-4 rounded-xl border-2 border-yellow-200">
                        <div>
                            <span class="block text-[10px] font-bold text-yellow-600 uppercase tracking-widest leading-none mb-1 text-xs">Final Maintenance Cost</span>
                            <span id="editTotalCostDisplay" class="text-3xl font-black text-yellow-700 tabular-nums leading-none">₱0.00</span>
                        </div>
                        <input type="hidden" name="cost" id="editTotalCostValue" value="0">
                        <div class="text-right">
                            <i data-lucide="calculator" class="w-8 h-8 text-yellow-200"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="p-4 border-t flex justify-end gap-3 shadow-inner bg-gray-50 shrink-0">
                <button type="button" onclick="document.getElementById('editMaintenanceModal').classList.add('hidden')" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 text-sm font-bold transition-all">
                    Cancel
                </button>
                <button type="submit" class="px-6 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 text-sm font-bold shadow-lg shadow-yellow-200/50 transition-all flex items-center gap-2">
                    <i data-lucide="check" class="w-4 h-4"></i> Update Record
                </button>
            </div>
        </form>
    </div>
</div>

{{-- View Info Modal --}}
<div id="viewMaintenanceModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-60 backdrop-blur-sm transition-all">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl p-0 overflow-hidden max-h-[90vh] flex flex-col animate-fade-in transition-all">
        <div class="bg-yellow-600 p-6 text-white shrink-0">
            <div class="flex justify-between items-start">
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <span id="viewMaintType" class="px-2 py-0.5 bg-white/20 rounded text-[10px] font-black uppercase tracking-widest"></span>
                        <span id="viewMaintStatus" class="px-2 py-0.5 bg-black/20 rounded text-[10px] font-black uppercase tracking-widest"></span>
                    </div>
                    <h3 id="viewPlateNumber" class="text-3xl font-black tracking-tighter uppercase"></h3>
                    <p id="viewDriverName" class="text-yellow-100 font-bold text-sm uppercase"></p>
                </div>
                <button onclick="document.getElementById('viewMaintenanceModal').classList.add('hidden')" class="p-2 hover:bg-white/10 rounded-full transition">
                    <i data-lucide="x" class="w-6 h-6"></i>
                </button>
            </div>
        </div>
        
        <div class="p-8 overflow-y-auto custom-scrollbar flex-1">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                {{-- Left Column: Basics & Mechanics --}}
                <div class="space-y-6">
                    <div>
                        <h4 class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-3 flex items-center gap-2">
                            <i data-lucide="info" class="w-3 h-3 text-yellow-600"></i> Service Description
                        </h4>
                        <div id="viewDescription" class="p-4 bg-gray-50 rounded-xl text-gray-700 text-sm italic border-l-4 border-yellow-200 leading-relaxed shadow-sm max-h-[76px] overflow-y-auto custom-scrollbar break-words whitespace-pre-wrap"></div>
                    </div>

                    <div id="dispatcherNotesContainer" class="hidden">
                        <h4 class="text-[10px] font-black text-gray-400 uppercase tracking-widest mt-4 mb-3 flex items-center gap-2">
                            <i data-lucide="message-square" class="w-3 h-3 text-blue-500"></i> Dispatcher Notes
                        </h4>
                        <div id="viewDispatcherNotes" class="p-4 bg-blue-50/50 rounded-xl text-gray-700 text-sm italic border-l-4 border-blue-400 leading-relaxed shadow-sm max-h-[76px] overflow-y-auto custom-scrollbar break-words whitespace-pre-wrap"></div>
                    </div>
                    
                    <div>
                        <h4 class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-3 flex items-center gap-2">
                            <i data-lucide="wrench" class="w-3 h-3 text-yellow-600"></i> Assigned Mechanics
                        </h4>
                        <div id="viewMechanicsList" class="flex flex-wrap gap-2"></div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="p-4 bg-gray-50 rounded-xl border border-gray-100">
                            <h4 class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Date Started</h4>
                            <p id="viewDateStarted" class="text-sm font-bold text-gray-800"></p>
                        </div>
                        <div class="p-4 bg-gray-50 rounded-xl border border-gray-100">
                            <h4 class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Date Completed</h4>
                            <p id="viewDateCompleted" class="text-sm font-bold text-gray-800"></p>
                        </div>
                    </div>
                </div>

                {{-- Right Column: Costing Breakdown --}}
                <div class="bg-gray-50 p-6 rounded-2xl border border-gray-100 flex flex-col shadow-inner">
                    <h4 class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-4 flex items-center gap-2">
                        <i data-lucide="receipt" class="w-3 h-3 text-yellow-600"></i> Costing Breakdown
                    </h4>
                    
                    <div id="viewBreakdownList" class="space-y-3 mb-6 flex-1 max-h-[250px] overflow-y-auto pr-2 custom-scrollbar">
                        {{-- Items injected here --}}
                    </div>

                    <div class="pt-4 border-t-2 border-dashed border-gray-200 mt-auto">
                        <div class="flex justify-between items-center mb-1">
                            <span class="text-[10px] font-black text-gray-400 uppercase">Grand Total Amount</span>
                        </div>
                        <div class="flex items-baseline gap-1">
                            <span class="text-3xl font-black text-yellow-700 tabular-nums tracking-tighter" id="viewGrandTotal"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="bg-gray-50 p-4 shrink-0 flex justify-end gap-3 border-t">
            <button onclick="document.getElementById('viewMaintenanceModal').classList.add('hidden')" 
                class="px-6 py-2 bg-white border border-gray-200 text-gray-600 rounded-lg hover:bg-gray-100 text-sm font-black uppercase tracking-tight transition shadow-sm font-bold">Close View</button>
        </div>
    </div>
</div>

    <!-- ═══════════════════════════════════════════════════════════
         SPARE PARTS CATALOG MODAL — Clean list view
    ═══════════════════════════════════════════════════════════ -->
    <div id="partsModal" class="hidden fixed inset-0 z-[60] flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-6xl h-[95vh] flex flex-col overflow-hidden">
            {{-- Header (Deep Navy matching Unit Details) --}}
            <div class="bg-slate-800 p-4 shrink-0">
                <div class="flex justify-between items-center flex-wrap gap-4">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-white bg-opacity-20 rounded-lg">
                            <i data-lucide="box" class="w-5 h-5 text-white"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-white leading-tight">Spare Parts Catalog</h3>
                            <p class="text-sm text-blue-100 leading-tight">View, restock, and manage your parts inventory</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <button onclick="openSuppliersModal()" class="px-3 py-1.5 bg-white bg-opacity-10 text-white rounded-lg hover:bg-opacity-20 text-[10px] font-black uppercase tracking-widest transition flex items-center gap-1.5">
                            <i data-lucide="users" class="w-3 h-3"></i> Suppliers
                        </button>
                        <button onclick="openPartsArchiveModal()" class="px-3 py-1.5 bg-yellow-500/20 text-yellow-300 rounded-lg hover:bg-yellow-500/30 text-[10px] font-black uppercase tracking-widest transition flex items-center gap-1.5">
                            <i data-lucide="archive" class="w-3 h-3"></i> Archives
                        </button>
                        <button onclick="openPartMiniModal()" class="px-3 py-1.5 bg-blue-500 text-white rounded-lg hover:bg-blue-400 text-[10px] font-black uppercase tracking-widest transition flex items-center gap-1.5 shadow-md">
                            <i data-lucide="plus" class="w-3 h-3"></i> Add Part
                        </button>
                        <div class="w-px h-6 bg-slate-600 mx-2"></div>
                        <button onclick="closePartsModal()" class="text-white hover:text-gray-200 transition-colors">
                            <i data-lucide="x" class="w-5 h-5"></i>
                        </button>
                    </div>
                </div>
            </div>

            {{-- Toast --}}
            <div id="partsModalToast" class="hidden mx-6 mt-3 p-3 rounded-xl border flex items-center gap-3 text-sm font-bold shadow-sm"></div>

            {{-- Table & Search Container --}}
            <div class="flex-1 flex flex-col overflow-hidden">
                {{-- Search --}}
                <div class="px-6 pt-4 pb-2 shrink-0">
                    <div class="relative">
                        <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none"></i>
                        <input type="text" id="partsSearchInput"
                            placeholder="Search parts by name or supplier..."
                            oninput="filterPartsTable(this.value)"
                            class="w-full pl-9 pr-8 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none bg-gray-50">
                        <button id="btnClearPartsSearch" onclick="clearPartsSearch()" class="hidden absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-red-500 transition">
                            <i data-lucide="x-circle" class="w-4 h-4"></i>
                        </button>
                    </div>
                    <div class="flex justify-end mt-1">
                        <span id="partsSearchCount" class="text-[10px] font-bold text-gray-400 uppercase tracking-widest"></span>
                    </div>
                </div>

                {{-- Parts Table --}}
                <div class="flex-1 overflow-y-auto custom-scrollbar px-6 pb-4">
                    <table class="w-full divide-y divide-gray-100">
                        <thead class="bg-gray-50 sticky top-0 z-10">
                            <tr>
                                <th class="px-4 py-3 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Part Name</th>
                                <th class="px-4 py-3 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Supplier</th>
                                <th class="px-4 py-3 text-center text-[10px] font-black text-gray-400 uppercase tracking-widest">Stock</th>
                                <th class="px-4 py-3 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Price</th>
                                <th class="px-4 py-3 text-right text-[10px] font-black text-gray-400 uppercase tracking-widest">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="partsTableBody" class="divide-y divide-gray-50">
                            @foreach($spare_parts as $p)
                            <tr class="hover:bg-gray-50/60 transition parts-row" data-name="{{ strtolower($p->name) }}" data-supplier="{{ strtolower($p->supplier ?? '') }}">
                                <td class="px-4 py-3">
                                    <div class="text-sm font-semibold text-gray-800 part-name-cell">{{ $p->name }}</div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="text-[10px] font-bold text-gray-500 uppercase tracking-tighter">{{ $p->supplier ?? 'Unspecified' }}</div>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-black uppercase {{ ($p->stock_quantity ?? 0) <= 0 ? 'bg-red-100 text-red-600' : 'bg-green-100 text-green-700' }}">
                                        {{ $p->stock_quantity ?? 0 }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-sm font-bold text-blue-600">₱{{ number_format($p->price, 2) }}</td>
                                <td class="px-4 py-3 text-right">
                                    <div class="flex justify-end gap-1">
                                        <button onclick="editCatalogPart({{ $p->id }}, '{{ addslashes($p->name) }}', {{ $p->price }}, {{ $p->stock_quantity ?? 0 }}, '{{ addslashes($p->supplier ?? '') }}')"
                                            title="Add Stock"
                                            class="p-2 text-blue-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition">
                                            <i data-lucide="package-plus" class="w-4 h-4"></i>
                                        </button>
                                        <button onclick="deletePart({{ $p->id }}, this)"
                                            title="Delete"
                                            class="p-2 text-red-200 hover:text-red-600 hover:bg-red-50 rounded-lg transition">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Footer --}}
            <div class="p-4 border-t flex justify-end shadow-inner bg-gray-50 shrink-0">
                <button onclick="closePartsModal()" class="px-5 py-2 bg-gray-900 text-white rounded-lg hover:bg-black text-sm font-bold transition">Done</button>
            </div>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════════════════════
         ADD / EDIT STOCK MINI-MODAL (overlays on top of catalog)
    ═══════════════════════════════════════════════════════════ -->
    <div id="partMiniModal" class="hidden fixed inset-0 z-[70] flex items-center justify-center bg-black bg-opacity-40 backdrop-blur-sm">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4 p-6">
            <div class="flex items-center justify-between mb-5">
                <div class="flex items-center gap-3">
                    <div id="miniModalIcon" class="w-9 h-9 rounded-xl bg-blue-100 flex items-center justify-center">
                        <i data-lucide="plus" class="w-5 h-5 text-blue-600"></i>
                    </div>
                    <div>
                        <h4 id="miniModalTitle" class="text-base font-bold text-gray-900">Add New Part</h4>
                        <p id="miniModalSubtitle" class="text-[11px] text-gray-400">Fill in the part details below</p>
                    </div>
                </div>
                <button onclick="closePartMiniModal()" class="p-1.5 hover:bg-gray-100 rounded-lg text-gray-400 hover:text-gray-700 transition">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            {{-- Hidden fields --}}
            <input type="hidden" id="newPartId">
            <input type="hidden" id="newPartCurrentStock" value="0">

            <div class="space-y-4">
                {{-- Part Name --}}
                <div>
                    <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1">Part Name</label>
                    <input type="text" id="newPartName" placeholder="e.g., Oil Filter"
                        class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none">
                </div>

                {{-- Price + Qty row --}}
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1">Price (₱)</label>
                        <input type="number" id="newPartPrice" placeholder="0.00" min="0"
                            class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1">
                            <span id="lblQtyMode">Initial Qty</span>
                            <span id="lblCurrentStock" class="hidden text-gray-400 font-normal normal-case ml-1">(now: <span id="spanCurrentStock">0</span>)</span>
                        </label>
                        <input type="number" id="newPartQty" placeholder="0" min="0"
                            oninput="validateAddQty(this)"
                            class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none">
                        <p id="qtyError" class="hidden text-[10px] text-red-500 font-bold mt-1">⚠️ Must be ≥ 1 to add stock.</p>
                    </div>
                </div>

                {{-- Supplier --}}
                <div>
                    <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1">Supplier</label>
                    <select id="newPartSupplier" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none bg-white">
                        <option value="">Select Supplier (Optional)</option>
                        @foreach($suppliers as $s)
                            <option value="{{ $s->name }}">{{ $s->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Actions --}}
            <div class="flex gap-3 mt-6">
                <button onclick="closePartMiniModal()" class="flex-1 py-2.5 border border-gray-200 text-gray-600 rounded-xl text-sm font-bold hover:bg-gray-50 transition">Cancel</button>
                <button id="btnSavePart" onclick="saveNewPart()" class="flex-1 py-2.5 bg-blue-600 text-white rounded-xl text-sm font-bold hover:bg-blue-700 transition flex items-center justify-center gap-2">
                    <i data-lucide="save" class="w-4 h-4"></i>
                    <span id="txtSavePart">Save Part</span>
                </button>
            </div>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════════════════════
         SPARE PARTS ARCHIVE MODAL
    ═══════════════════════════════════════════════════════════ -->
    <div id="partsArchiveModal" class="hidden fixed inset-0 z-[70] flex items-center justify-center bg-black bg-opacity-50 backdrop-blur-sm">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl flex flex-col" style="max-height:80vh">
            <div class="flex items-center justify-between px-6 py-4 border-b bg-yellow-50">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-yellow-100 flex items-center justify-center">
                        <i data-lucide="archive" class="w-5 h-5 text-yellow-600"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">Spare Parts Archive</h3>
                        <p class="text-[11px] text-yellow-600 font-bold">Restore or permanently remove items</p>
                    </div>
                </div>
                <button onclick="closePartsArchiveModal()" class="p-1.5 hover:bg-yellow-100 rounded-lg text-gray-400 hover:text-yellow-700 transition">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <div class="flex-1 overflow-y-auto custom-scrollbar p-0">
                <table class="min-w-full divide-y divide-gray-100">
                    <thead class="bg-gray-50 border-b">
                        <tr>
                            <th class="px-6 py-3 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Part Name</th>
                            <th class="px-6 py-3 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Supplier</th>
                            <th class="px-6 py-3 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="archivedPartsTableBody" class="divide-y divide-gray-50">
                        <!-- Dynamic content -->
                    </tbody>
                </table>
            </div>

            <div class="px-6 py-4 border-t flex justify-end shadow-inner bg-gray-50">
                <button onclick="closePartsArchiveModal()" class="px-5 py-2 bg-gray-900 text-white rounded-lg hover:bg-black text-sm font-bold transition">Close Archive</button>
            </div>
        </div>
    </div>
    <!-- Purchase History Modal -->
    <div id="purchaseHistoryModal" class="hidden fixed inset-0 z-[80] flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-6xl h-[95vh] flex flex-col overflow-hidden">
            {{-- Modal Header (Deep Navy matching Unit Details) --}}
            <div class="bg-slate-800 p-4 shrink-0">
                <div class="flex justify-between items-center">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-white bg-opacity-20 rounded-lg">
                            <i data-lucide="history" class="w-5 h-5 text-white"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-white leading-tight">Stock Purchase History</h3>
                            <p class="text-sm text-blue-100 leading-tight">Logs from Office Expenses</p>
                        </div>
                    </div>
                    <button onclick="closePurchaseHistoryModal()" class="text-white hover:text-gray-200 transition-colors">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>
            </div>

            <div class="flex flex-col md:flex-row gap-4 mb-6">
                {{-- Daily Total Box --}}
                <div class="flex-1 bg-green-50 border border-green-100 p-4 rounded-2xl shadow-sm">
                    <p class="text-[10px] font-black text-green-600 uppercase tracking-widest mb-1">Total for Selected Date</p>
                    <h4 id="dailyPurchaseTotal" class="text-2xl font-black text-gray-900 tracking-tighter">₱0.00</h4>
                </div>
                {{-- Monthly Total Box --}}
                <div class="flex-1 bg-blue-50 border border-blue-100 p-4 rounded-2xl shadow-sm">
                    <p class="text-[10px] font-black text-blue-600 uppercase tracking-widest mb-1">Total for this Month</p>
                    <h4 id="monthlyPurchaseTotal" class="text-2xl font-black text-gray-900 tracking-tighter">₱0.00</h4>
                </div>
                {{-- Date Filter Input --}}
                <div class="flex-none flex flex-col justify-center">
                    <label class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1.5 ml-1">Validate Date</label>
                    <input type="date" id="historyDateFilter" value="{{ date('Y-m-d') }}" onchange="refreshPurchaseHistory(this.value)"
                        class="px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm font-black focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 focus:outline-none transition-all">
                </div>
            </div>

            <div class="flex-1 overflow-y-auto pr-2 custom-scrollbar">
                <table class="min-w-full divide-y divide-gray-100">
                    <thead class="bg-gray-50 sticky top-0 z-10">
                        <tr>
                            <th class="px-4 py-2 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Date</th>
                            <th class="px-4 py-2 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Description</th>
                            <th class="px-4 py-2 text-right text-[10px] font-black text-gray-400 uppercase tracking-widest">Amount</th>
                        </tr>
                    </thead>
                    <tbody id="purchaseHistoryTableBody" class="divide-y divide-gray-50">
                        @forelse($purchaseHistory as $ph)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-4 py-4 whitespace-nowrap">
                                <div class="text-xs font-bold text-gray-600">{{ \Carbon\Carbon::parse($ph->date)->format('M d, Y') }}</div>
                                <div class="text-[9px] text-gray-400">{{ \Carbon\Carbon::parse($ph->created_at)->format('h:i A') }}</div>
                            </td>
                            <td class="px-4 py-4">
                                <div class="text-sm font-black text-gray-800 tracking-tight">{{ $ph->description }}</div>
                                <div class="text-[10px] text-blue-500 font-bold uppercase">Maintenance Supplies</div>
                            </td>
                            <td class="px-4 py-4 text-right">
                                <div class="text-sm font-black text-green-600">₱{{ number_format($ph->amount, 2) }}</div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="px-4 py-12 text-center text-gray-400">
                                <p class="text-sm">No purchase records found.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <div class="p-4 border-t flex justify-end shadow-inner bg-gray-50 shrink-0">
                <button onclick="closePurchaseHistoryModal()" class="px-5 py-2 bg-gray-900 text-white rounded-lg hover:bg-black text-sm font-bold transition">
                    Close
                </button>
            </div>
        </div>
    </div>
    <div id="suppliersModal" class="hidden fixed inset-0 z-[80] flex items-center justify-center bg-black bg-opacity-60 backdrop-blur-sm">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg p-8 max-h-[85vh] flex flex-col">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h3 class="text-xl font-bold text-gray-900">Manage Suppliers</h3>
                    <p class="text-xs text-gray-400 uppercase font-bold tracking-widest mt-1">Directory of Parts Sources</p>
                </div>
                <button onclick="closeSuppliersModal()" class="p-2 hover:bg-gray-50 rounded-full transition text-gray-400">
                    <i data-lucide="x" class="w-6 h-6"></i>
                </button>
            </div>

            <div class="flex flex-col gap-3 mb-6 p-4 bg-gray-50 rounded-xl border border-gray-100">
                <input type="hidden" id="supplierId">
                <input type="text" id="supplierName" placeholder="Supplier Name" 
                    class="w-full px-4 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-yellow-500 focus:outline-none">
                <div class="grid grid-cols-2 gap-3">
                    <input type="text" id="supplierContact" placeholder="Contact Person" 
                        class="w-full px-4 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-yellow-500 focus:outline-none">
                    <input type="text" id="supplierPhone" placeholder="Phone Number" 
                        class="w-full px-4 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-yellow-500 focus:outline-none">
                </div>
                <button onclick="saveSupplier()" class="w-full py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 text-sm font-bold transition flex items-center justify-center gap-2 shadow-md">
                    <i data-lucide="save" class="w-4 h-4"></i> Save Supplier
                </button>
            </div>

            <div class="flex-1 overflow-y-auto pr-2 custom-scrollbar">
                <table class="min-w-full divide-y divide-gray-100">
                    <tbody id="suppliersTableBody" class="divide-y divide-gray-50">
                        @foreach($suppliers as $s)
                        <tr class="hover:bg-gray-50 transition group">
                            <td class="py-3 pr-4">
                                <div class="text-sm font-black text-gray-800 tracking-tight">{{ $s->name }}</div>
                                @if($s->contact_person || $s->phone_number)
                                <div class="text-[10px] text-gray-400 font-bold uppercase tracking-tighter">
                                    {{ $s->contact_person ?: '—' }} · {{ $s->phone_number ?: '—' }}
                                </div>
                                @endif
                            </td>
                            <td class="py-3 text-right">
                                <div class="flex justify-end gap-2">
                                    <button onclick="editSupplier({{ $s->id }}, '{{ addslashes($s->name) }}', '{{ addslashes($s->contact_person) }}', '{{ $s->phone_number }}')" 
                                        class="p-1.5 opacity-0 group-hover:opacity-100 text-blue-400 hover:text-blue-600 hover:bg-blue-50 rounded transition">
                                        <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                                    </button>
                                    <button onclick="deleteSupplier({{ $s->id }}, this)" 
                                        class="p-1.5 opacity-0 group-hover:opacity-100 text-red-200 hover:text-red-600 hover:bg-red-50 rounded transition">
                                        <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <div class="mt-6 flex justify-end pt-4 border-t border-gray-100">
                <button onclick="closeSuppliersModal()" class="px-6 py-2 bg-gray-100 text-gray-700 rounded-xl hover:bg-gray-200 text-sm font-black uppercase tracking-widest transition">
                    Done
                </button>
            </div>
        </div>
    </div>

    <!-- Quick Add Part Modal (Smaller) -->
    <div id="quickAddPartModal" class="hidden fixed inset-0 z-[70] flex items-center justify-center bg-black bg-opacity-40">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-sm p-6">
            <h4 class="text-base font-bold text-gray-900 mb-4">Quick Add Spare Part</h4>
            <div class="space-y-4">
                <input type="hidden" id="quickPartId">
                <div>
                    <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Part Name</label>
                    <input type="text" id="quickPartName" class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:outline-none">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Price (₱)</label>
                        <input type="number" id="quickPartPrice" class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Quantity</label>
                        <input type="number" id="quickPartQty" class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:outline-none">
                    </div>
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Supplier</label>
                    <select id="quickPartSupplier" class="supplier-dropdown w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:outline-none bg-white">
                        <option value="">Select Supplier</option>
                        @foreach($suppliers as $s)
                            <option value="{{ $s->name }}">{{ $s->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex gap-2 pt-2">
                    <button onclick="saveQuickPart()" class="flex-1 py-2 bg-blue-600 text-white rounded-lg text-sm font-bold hover:bg-blue-700 transition">Save Part</button>
                    <button onclick="closeQuickAddPart()" class="flex-1 py-2 bg-gray-100 text-gray-600 rounded-lg text-sm font-bold hover:bg-gray-200 transition">Cancel</button>
                </div>
            </div>
        </div>
    </div>

@push('scripts')
<script>
// Global state for parts catalog and carts
let partsCatalog = @json($spare_parts);
let suppliersList = @json($suppliers);
let addPartsCart = [];
let editPartsCart = [];
let addOtherCosts = [];
let editOtherCosts = [];

// Record store - keyed by ID, no HTML attribute encoding issues
const maintRecords = @json($records->keyBy('id'));

function openAddMaint() {
    closeAllDropdowns();
    document.getElementById('addMaintenanceModal').classList.remove('hidden');
    // Reset mechanics
    document.getElementById('addMechDisplay1').value = '';
    document.getElementById('addMechDisplay2').value = '';
    document.getElementById('addSecondMechRow').classList.add('hidden');
    document.getElementById('btnAddSecondMech').classList.remove('hidden');
    lucide.createIcons();
}

function selectMech(inputId, name, dropdownId) {
    document.getElementById(inputId).value = name;
    document.getElementById(dropdownId).classList.add('hidden');
}

function toggleSecondMech(mode) {
    const row = document.getElementById(mode + 'SecondMechRow');
    const btn = document.getElementById('btn' + mode + 'SecondMech');
    row.classList.remove('hidden');
    btn.classList.add('hidden');
    lucide.createIcons();
}

async function openViewMaint(id) {
    closeAllDropdowns();
    const r = maintRecords[id];
    if (!r) { console.error('Record not found:', id); return; }
    const modal = document.getElementById('viewMaintenanceModal');
    
    // Header Info
    document.getElementById('viewPlateNumber').innerText = r.plate_number;
    document.getElementById('viewDriverName').innerText = r.driver_name || 'No Driver Assigned';
    document.getElementById('viewMaintType').innerText = r.maintenance_type;
    document.getElementById('viewMaintStatus').innerText = (r.status || '').replace('_', ' ');
    let fullDesc = r.description || 'No description provided.';
    let splitIdx = fullDesc.indexOf('Dispatcher Notes:');
    
    const descEl = document.getElementById('viewDescription');
    const dispatchEl = document.getElementById('viewDispatcherNotes');
    const dispatchContainer = document.getElementById('dispatcherNotesContainer');
    
    if (splitIdx !== -1) {
        descEl.innerText = fullDesc.substring(0, splitIdx).trim();
        dispatchEl.innerText = fullDesc.substring(splitIdx + 'Dispatcher Notes:'.length).trim();
        dispatchContainer.classList.remove('hidden');
    } else {
        descEl.innerText = fullDesc;
        dispatchContainer.classList.add('hidden');
    }
    
    const toReadable = v => v ? String(v).substring(0, 10) : '—';
    document.getElementById('viewDateStarted').innerText   = toReadable(r.date_started);
    document.getElementById('viewDateCompleted').innerText = r.date_completed ? toReadable(r.date_completed) : 'Ongoing';
    document.getElementById('viewGrandTotal').innerText = '₱' + parseFloat(r.cost || 0).toLocaleString('en-US', {minimumFractionDigits: 2});

    // Mechanics badges
    const mechanics = (r.mechanic_name || '').split(',').map(m => m.trim()).filter(m => m);
    const mechList = document.getElementById('viewMechanicsList');
    mechList.innerHTML = mechanics.length > 0 
        ? mechanics.map(m => `<span class="px-3 py-1 bg-yellow-100 text-yellow-700 rounded-full text-xs font-bold border border-yellow-200 flex items-center gap-1.5"><i data-lucide="user" class="w-3 h-3"></i> ${m}</span>`).join('')
        : '<span class="text-gray-400 italic text-xs">No mechanic specified</span>';

    // Show modal immediately, then fetch breakdown
    modal.classList.remove('hidden');
    lucide.createIcons();

    const breakdown = document.getElementById('viewBreakdownList');
    breakdown.innerHTML = '<div class="text-center py-4 text-gray-400 text-xs">Loading breakdown...</div>';

    try {
        const res = await fetch(`{{ url('maintenance') }}/${id}/parts`);
        if (!res.ok) throw new Error('HTTP ' + res.status);
        const result = await res.json();
        if (result.success && result.data.length > 0) {
            breakdown.innerHTML = result.data.map(item => `
                <div class="flex justify-between items-center bg-white p-2.5 rounded-lg border border-gray-100 shadow-sm gap-4">
                    <div class="min-w-0 flex-1">
                        <div class="text-xs font-bold text-gray-800 break-words">${item.part_name}</div>
                        <div class="text-[10px] text-gray-400">${item.part_id ? `Qty: ${item.quantity} &times; &#8369;${parseFloat(item.price).toFixed(2)}${item.supplier ? ` <span class="mx-1 text-gray-300">|</span> Supplier: <span class="font-bold text-blue-600 tracking-tight">${item.supplier}</span>` : ''}` : 'Additional Service / Labor'}</div>
                    </div>
                    <div class="text-xs font-black text-gray-900 tabular-nums shrink-0 whitespace-nowrap">&#8369;${parseFloat(item.total || 0).toLocaleString('en-US', {minimumFractionDigits: 2})}</div>
                </div>
            `).join('');
        } else {
            breakdown.innerHTML = '<div class="text-center py-4 text-gray-400 text-xs italic">No detailed breakdown recorded for this entry.</div>';
        }
    } catch(e) {
        breakdown.innerHTML = '<div class="text-center py-4 text-red-400 text-xs">Could not load breakdown: ' + e.message + '</div>';
    }
}

async function openEditMaint(btn) {
    closeAllDropdowns();
    const id = btn.dataset.id;
    const r = maintRecords[id];
    if (!r) { console.error('Edit record not found:', id); return; }
    const modal = document.getElementById('editMaintenanceModal');
    
    // Open immediately for responsive feel
    modal.classList.remove('hidden');
    
    const base = "{{ url('maintenance') }}";
    document.getElementById('editMaintForm').action = base + '/' + id;
    document.getElementById('editUnitId').value         = r.unit_id;
    document.getElementById('editUnitDisplay').value    = r.plate_number;
    document.getElementById('editDriverId').value       = r.driver_id || '';
    document.getElementById('editDriverDisplay').value  = r.driver_name || '';
    
    // Set driver suggestion badges
    const unitOption = document.querySelector(`#editUnitDropdown .unit-option[data-id="${r.unit_id}"]`);
    if (unitOption) {
        const suggestions = [unitOption.dataset.driverId, unitOption.dataset.secondaryId].filter(id => id && id !== 'null').join(',');
        document.getElementById('editDriverDisplay').dataset.suggestedIds = suggestions;
    }

    document.getElementById('em_type').value   = r.maintenance_type || 'preventive';
    document.getElementById('em_status').value = r.status || 'pending';
    
    let fullDesc = r.description || '';
    let splitIdx = fullDesc.indexOf('Dispatcher Notes:');
    
    if (splitIdx !== -1) {
        document.getElementById('em_description').value = fullDesc.substring(0, splitIdx).trim();
        document.getElementById('em_dispatcher_notes').value = fullDesc.substring(splitIdx + 'Dispatcher Notes:'.length).trim();
    } else {
        document.getElementById('em_description').value = fullDesc;
        document.getElementById('em_dispatcher_notes').value = '';
    }
    
    // Handle Multiple Mechanics — filter empty strings before checking length
    const mechs = (r.mechanic_name || '').split(',').map(m => m.trim()).filter(m => m.length > 0);
    document.getElementById('editMechDisplay1').value = mechs[0] || '';
    if (mechs.length > 1 && mechs[1]) {
        document.getElementById('editSecondMechRow').classList.remove('hidden');
        document.getElementById('editMechDisplay2').value = mechs[1];
        document.getElementById('btnEditSecondMech').classList.add('hidden');
    } else {
        document.getElementById('editSecondMechRow').classList.add('hidden');
        document.getElementById('editMechDisplay2').value = '';
        document.getElementById('btnEditSecondMech').classList.remove('hidden');
    }
    
    // Date values: may come as "2026-04-13T00:00:00.000000Z" or "2026-04-13" — always slice to 10 chars
    const toDateInput = v => v ? String(v).substring(0, 10) : '';
    document.getElementById('em_date_started').value   = toDateInput(r.date_started);
    document.getElementById('em_date_completed').value = toDateInput(r.date_completed);
    
    // Clear carts and show loading placeholder
    editPartsCart = [];
    editOtherCosts = [];
    refreshCart('edit');
    refreshOtherCosts('edit');
    document.getElementById('editPartsCartItems').innerHTML = '<div class="text-center py-4 text-gray-400">Loading parts...</div>';
    
    // Fetch individual parts asynchronously — CRITICAL: use parseFloat to prevent string concat
    try {
        const res = await fetch(`${base}/${id}/parts`);
        if (!res.ok) throw new Error('Server error ' + res.status);
        const result = await res.json();
        editPartsCart = [];
        editOtherCosts = [];
        if (result.success && result.data.length > 0) {
            result.data.forEach(p => {
                if (p.part_id) {
                    editPartsCart.push({
                        id:    p.part_id,
                        name:  p.part_name,
                        price: parseFloat(p.price)    || 0,   // force number
                        qty:   parseInt(p.quantity)   || 1    // force integer
                    });
                } else {
                    editOtherCosts.push({
                        name:  p.part_name,
                        price: parseFloat(p.price) || 0       // force number
                    });
                }
            });
        }
        refreshCart('edit');
        refreshOtherCosts('edit');
    } catch(e) { 
        console.error('Edit parts fetch error:', e); 
        document.getElementById('editPartsCartItems').innerHTML =
            '<div class="text-center py-4 text-red-400 text-xs">Error loading parts: ' + e.message + '</div>';
    }
}

// --- Other Costs Management ---
function addOtherCostRow(type) {
    const list = type === 'add' ? addOtherCosts : editOtherCosts;
    list.push({ name: '', price: 0 });
    refreshOtherCosts(type);
}

function removeOtherCost(index, type) {
    const list = type === 'add' ? addOtherCosts : editOtherCosts;
    list.splice(index, 1);
    refreshOtherCosts(type);
    refreshCart(type);
}

function updateOtherCostValue(index, field, value, type) {
    const list = type === 'add' ? addOtherCosts : editOtherCosts;
    if (field === 'price') {
        list[index][field] = parseFloat(value) || 0;
    } else {
        list[index][field] = value;
    }
    refreshCart(type);
}

function refreshOtherCosts(type) {
    const list = type === 'add' ? addOtherCosts : editOtherCosts;
    const container = document.getElementById(type === 'add' ? 'otherCostsItems' : 'editOtherCostsItems');
    
    if (list.length === 0) {
        container.innerHTML = `<div class="text-center py-2 text-gray-400 italic text-[10px]">No additional services added.</div>`;
        return;
    }

    container.innerHTML = list.map((item, i) => `
        <div class="flex gap-2 items-center bg-white p-2 rounded border border-gray-100 shadow-sm">
            <input type="text" value="${item.name}" oninput="updateOtherCostValue(${i}, 'name', this.value, '${type}')" 
                placeholder="Service Description (e.g. Labor)" 
                class="flex-1 px-2 py-1 border rounded text-xs focus:ring-1 focus:ring-orange-500 focus:outline-none">
            <div class="relative w-24">
                <span class="absolute left-1 top-1.5 text-gray-400 text-[10px]">₱</span>
                <input type="number" value="${item.price}" oninput="updateOtherCostValue(${i}, 'price', this.value, '${type}')" 
                    class="w-full pl-3 pr-1 py-1 border rounded text-xs text-right focus:outline-none">
            </div>
            <button type="button" onclick="removeOtherCost(${i}, '${type}')" class="p-1 text-red-400 hover:text-red-600 transition">
                <i data-lucide="minus-circle" class="w-4 h-4"></i>
            </button>
        </div>
    `).join('');
    lucide.createIcons();
}

// --- Master Parts Catalog ---
function openPartsModal() {
    document.getElementById('partsModal').classList.remove('hidden');
    // Reset search bar on open
    const si = document.getElementById('partsSearchInput');
    if (si) { si.value = ''; }
    filterPartsTable('');
    refreshPartsTable();
}

function closePartsModal() { 
    document.getElementById('partsModal').classList.add('hidden'); 
}

// Mini-Modal Controls
function openPartsArchiveModal() {
    document.getElementById('partsArchiveModal').classList.remove('hidden');
    refreshArchivedParts();
}

function closePartsArchiveModal() {
    document.getElementById('partsArchiveModal').classList.add('hidden');
}

async function refreshArchivedParts() {
    try {
        const res = await fetch("{{ route('spare-parts.archived') }}");
        const result = await res.json();
        if (result.success) {
            const tbody = document.getElementById('archivedPartsTableBody');
            if (result.data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="3" class="px-6 py-12 text-center text-gray-400 italic text-xs">No archived parts found.</td></tr>';
                return;
            }
            tbody.innerHTML = result.data.map(p => `
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-6 py-3 text-sm font-medium text-gray-600">${p.name}</td>
                    <td class="px-6 py-3 text-[10px] font-bold text-gray-400 uppercase">${p.supplier || 'Unspecified'}</td>
                    <td class="px-6 py-3 text-right flex justify-end gap-2">
                        <button onclick="restorePart(${p.id})" title="Restore Item" class="p-2 text-green-500 hover:bg-green-50 rounded-lg transition">
                            <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                        </button>
                        <button onclick="forceDeletePart(${p.id})" title="Delete Permanently" class="p-2 text-red-300 hover:text-red-600 hover:bg-red-50 rounded-lg transition">
                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                        </button>
                    </td>
                </tr>
            `).join('');
            lucide.createIcons();
        }
    } catch(e) { console.error(e); }
}

async function restorePart(id) {
    try {
        const res = await fetch(`{{ url('spare-parts/restore') }}/${id}`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
        });
        const result = await res.json();
        if (result.success) {
            showModalToast(result.message, 'success');
            
            // Reload active catalog data
            try {
                const res2 = await fetch("{{ route('spare-parts.index') }}");
                const result2 = await res2.json();
                if (result2.success) {
                    partsCatalog = result2.data;
                    refreshPartsTable();
                    refreshPartDropdowns();
                }
            } catch(e) { console.error(e); }
            
            refreshArchivedParts();
        }
    } catch(e) { console.error(e); }
}

async function forceDeletePart(id) {
    if (!confirm('🛑 WARNING: This will permanently delete the part record. This action cannot be undone. Proceed?')) return;
    try {
        const res = await fetch(`{{ url('spare-parts/permanent') }}/${id}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
        });
        const result = await res.json();
        if (result.success) {
            showModalToast(result.message, 'success');
            refreshArchivedParts();
        }
    } catch(e) { console.error(e); }
}

function openPartMiniModal(isEdit = false) {
    const modal = document.getElementById('partMiniModal');
    modal.classList.remove('hidden');
    
    // UI clean up for Add Mode
    if (!isEdit) {
        resetPartForm();
        document.getElementById('miniModalTitle').innerText = 'Add New Part';
        document.getElementById('miniModalSubtitle').innerText = 'Create a new item in the spare parts catalog';
        document.getElementById('lblQtyMode').innerText = 'Initial Qty';
        document.getElementById('txtSavePart').innerText = 'Save Part';
        document.getElementById('newPartName').readOnly = false;
        
        const iconContainer = document.getElementById('miniModalIcon');
        iconContainer.className = 'w-9 h-9 rounded-xl bg-blue-100 flex items-center justify-center';
        iconContainer.innerHTML = '<i data-lucide="plus" class="w-5 h-5 text-blue-600"></i>';
        lucide.createIcons();
    }
}

function closePartMiniModal() {
    document.getElementById('partMiniModal').classList.add('hidden');
}

async function saveNewPart() {
    const id = document.getElementById('newPartId').value;
    const name = document.getElementById('newPartName').value;
    const price = document.getElementById('newPartPrice').value;
    const qty_to_add = parseInt(document.getElementById('newPartQty').value) || 0;
    const supplier = document.getElementById('newPartSupplier').value;

    if(!name || !price) {
        showModalToast('Part Name and Price are required.', 'error');
        return;
    }

    // For updates: qty must be >= 0
    if (id && qty_to_add < 0) {
        document.getElementById('qtyError').classList.remove('hidden');
        return;
    }

    try {
        const res = await fetch("{{ route('spare-parts.store') }}", {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify({ id, name, price, qty_to_add, supplier })
        });
        const result = await res.json();
        if(result.success) {
            if (id) {
                const idx = partsCatalog.findIndex(p => p.id == id);
                if (idx !== -1) partsCatalog[idx] = result.data;
            } else {
                partsCatalog.push(result.data);
            }
            refreshPartsTable();
            refreshPartDropdowns();
            refreshPurchaseHistory();
            showModalToast(result.message, 'success');
            closePartMiniModal();
        } else {
            showModalToast(result.message || 'Something went wrong.', 'error');
        }
    } catch(e) { 
        console.error(e); 
        showModalToast('Server error. Please try again.', 'error'); 
    }
}

function editCatalogPart(id, name, price, qty, supplier) {
    openPartMiniModal(true); // Open in edit mode

    document.getElementById('newPartId').value = id;
    document.getElementById('newPartCurrentStock').value = qty;
    document.getElementById('newPartName').value = name;
    document.getElementById('newPartPrice').value = price;
    document.getElementById('newPartQty').value = ''; 
    document.getElementById('newPartSupplier').value = supplier || '';

    // UI Updates for Add Stock mode
    document.getElementById('miniModalTitle').innerText = 'Add Stock';
    document.getElementById('miniModalSubtitle').innerText = `Restocking: ${name}`;
    document.getElementById('lblQtyMode').innerText = 'Qty to Add';
    document.getElementById('txtSavePart').innerText = 'Update Inventory';
    document.getElementById('newPartName').readOnly = true; // Protect name on restock

    // Show current stock badge
    document.getElementById('spanCurrentStock').textContent = qty;
    document.getElementById('lblCurrentStock').classList.remove('hidden');

    const iconContainer = document.getElementById('miniModalIcon');
    iconContainer.className = 'w-9 h-9 rounded-xl bg-orange-100 flex items-center justify-center';
    iconContainer.innerHTML = '<i data-lucide="package-plus" class="w-5 h-5 text-orange-600"></i>';
    lucide.createIcons();

    // Clear error state
    document.getElementById('qtyError').classList.add('hidden');
    
    setTimeout(() => document.getElementById('newPartQty').focus(), 200);
}

function resetPartForm() {
    document.getElementById('newPartId').value = '';
    document.getElementById('newPartCurrentStock').value = '0';
    document.getElementById('newPartName').value = '';
    document.getElementById('newPartPrice').value = '';
    document.getElementById('newPartQty').value = '';
    document.getElementById('newPartSupplier').value = '';
    document.getElementById('lblCurrentStock').classList.add('hidden');
    document.getElementById('qtyError').classList.add('hidden');
}

function validateAddQty(input) {
    const val = parseInt(input.value);
    const errEl = document.getElementById('qtyError');
    if (input.value !== '' && (isNaN(val) || val < 1)) {
        errEl.classList.remove('hidden');
        input.classList.add('border-red-400', 'ring-1', 'ring-red-300');
    } else {
        errEl.classList.add('hidden');
        input.classList.remove('border-red-400', 'ring-1', 'ring-red-300');
    }
}

async function deletePart(id, btn) {
    if(!confirm('Are you sure you want to delete this part from the catalog?')) return;
    try {
        const res = await fetch(`{{ url('spare-parts') }}/${id}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
        });
        const result = await res.json();
        if(result.success) {
            btn.closest('tr').remove();
            partsCatalog = partsCatalog.filter(p => p.id !== id);
            refreshPartDropdowns();
        }
    } catch(e) { console.error(e); }
}

function refreshPartsTable() {
    const tbody = document.getElementById('partsTableBody');
    tbody.innerHTML = partsCatalog.map(p => {
        const safeName = (p.name || '').replace(/'/g, "\\'").replace(/"/g, '&quot;');
        const safeSupplier = (p.supplier || '').replace(/'/g, "\\'").replace(/"/g, '&quot;');
        return `
            <tr class="hover:bg-gray-50/50 transition parts-row" data-name="${(p.name||'').toLowerCase()}" data-supplier="${(p.supplier||'').toLowerCase()}">
                <td class="px-4 py-3">
                    <div class="text-sm font-semibold text-gray-800 part-name-cell">${p.name}</div>
                </td>
                <td class="px-4 py-3">
                    <div class="text-[10px] font-bold text-gray-500 uppercase tracking-tighter">${p.supplier || 'Unspecified'}</div>
                </td>
                <td class="px-4 py-3 text-center">
                    <span class="px-2 py-0.5 rounded text-[10px] font-black uppercase ${ (p.stock_quantity || 0) <= 0 ? 'bg-red-100 text-red-600' : 'bg-green-100 text-green-700' }">
                        ${p.stock_quantity || 0}
                    </span>
                </td>
                <td class="px-4 py-3 text-sm font-bold text-blue-600">₱${(parseFloat(p.price) || 0).toFixed(2)}</td>
                <td class="px-4 py-3 text-right">
                    <div class="flex justify-end gap-1">
                        <button onclick="editCatalogPart(${p.id}, '${safeName}', ${p.price}, ${p.stock_quantity || 0}, '${safeSupplier}')" 
                            title="Add Stock"
                            class="p-2 text-blue-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition">
                            <i data-lucide="package-plus" class="w-4 h-4"></i>
                        </button>
                        <button onclick="deletePart(${p.id}, this)" 
                            title="Delete"
                            class="p-2 text-red-200 hover:text-red-600 hover:bg-red-50 rounded-lg transition">
                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `;
    }).join('');
    lucide.createIcons();

    // Re-apply active search filter after table is rebuilt
    const searchVal = document.getElementById('partsSearchInput')?.value || '';
    if (searchVal.trim()) filterPartsTable(searchVal);
}

function filterPartsTable(query) {
    const q = (query || '').toLowerCase().trim();
    const rows = document.querySelectorAll('#partsTableBody .parts-row');
    const clearBtn = document.getElementById('btnClearPartsSearch');
    const countEl = document.getElementById('partsSearchCount');

    let visible = 0;
    rows.forEach(row => {
        const name     = row.getAttribute('data-name') || '';
        const supplier = row.getAttribute('data-supplier') || '';
        const match = !q || name.includes(q) || supplier.includes(q);
        row.style.display = match ? '' : 'none';

        // Highlight matching text in part name cell
        const nameCell = row.querySelector('.part-name-cell');
        if (nameCell && q) {
            const original = nameCell.textContent;
            const regex = new RegExp(`(${q.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')})`, 'gi');
            nameCell.innerHTML = original.replace(regex, '<mark class="bg-yellow-200 text-yellow-900 rounded px-0.5">$1</mark>');
        } else if (nameCell) {
            nameCell.innerHTML = nameCell.textContent; // strip highlights
        }

        if (match) visible++;
    });

    // Show/hide clear button and count
    if (q) {
        clearBtn.classList.remove('hidden');
        countEl.textContent = `${visible} result${visible !== 1 ? 's' : ''}`;
    } else {
        clearBtn.classList.add('hidden');
        countEl.textContent = '';
    }
}

function clearPartsSearch() {
    const input = document.getElementById('partsSearchInput');
    input.value = '';
    filterPartsTable('');
    input.focus();
}


// --- Quick Add Part ---
function openQuickAddPart() { 
    if (!document.getElementById('quickPartId').value) {
        document.getElementById('quickPartName').value = '';
        document.getElementById('quickPartPrice').value = '';
        document.getElementById('quickAddPartModal').querySelector('h4').innerText = 'Quick Add Spare Part';
    }
    document.getElementById('quickAddPartModal').classList.remove('hidden'); 
}

function closeQuickAddPart() { 
    document.getElementById('quickAddPartModal').classList.add('hidden'); 
    document.getElementById('quickPartId').value = '';
    document.getElementById('quickPartName').value = '';
    document.getElementById('quickPartPrice').value = '';
    document.getElementById('quickPartQty').value = '';
    document.getElementById('quickPartSupplier').value = '';
}

async function saveQuickPart() {
    const id = document.getElementById('quickPartId').value;
    const name = document.getElementById('quickPartName').value;
    const price = document.getElementById('quickPartPrice').value;
    const stock_quantity = document.getElementById('quickPartQty').value;
    const supplier = document.getElementById('quickPartSupplier').value;

    if(!name || !price) {
        alert('Part Name and Price are required.');
        return;
    }
    
    try {
        const res = await fetch("{{ route('spare-parts.store') }}", {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify({ id, name, price, stock_quantity, supplier })
        });
        const result = await res.json();
        if(result.success) {
            if (id) {
                const index = partsCatalog.findIndex(p => p.id == id);
                if (index !== -1) partsCatalog[index] = result.data;
            } else {
                partsCatalog.push(result.data);
                addPartToCart(result.data, 'add');
            }
            refreshPartDropdowns();
            refreshPartsTable();
            refreshPurchaseHistory();
            showToast(result.message, 'success');
            closeQuickAddPart();
        }
    } catch(e) { console.error(e); }
}

function editPartFromDropdown(id, name, price, qty, supplier, event) {
    if(event) event.stopPropagation();
    document.getElementById('quickPartId').value = id;
    document.getElementById('quickPartName').value = name;
    document.getElementById('quickPartPrice').value = price;
    document.getElementById('quickPartQty').value = qty || '';
    document.getElementById('quickPartSupplier').value = supplier || '';
    document.getElementById('quickAddPartModal').querySelector('h4').innerText = 'Edit Spare Part';
    openQuickAddPart();
}

// --- Cart Management ---
function addPartToCart(part, type) {
    const cart = type === 'add' ? addPartsCart : editPartsCart;
    const existing = cart.find(p => p.id === part.id);
    if(existing) {
        existing.qty++;
    } else {
        cart.push({ ...part, qty: 1 });
    }
    refreshCart(type);
}

function removeFromCart(index, type) {
    const cart = type === 'add' ? addPartsCart : editPartsCart;
    cart.splice(index, 1);
    refreshCart(type);
}

function updateQty(index, qty, type) {
    const cart = type === 'add' ? addPartsCart : editPartsCart;
    cart[index].qty = parseInt(qty) || 1;
    refreshCart(type);
}

function refreshCart(type) {
    const cart = type === 'add' ? addPartsCart : editPartsCart;
    const otherCosts = type === 'add' ? addOtherCosts : editOtherCosts;
    const container = document.getElementById(type === 'add' ? 'partsCartItems' : 'editPartsCartItems');
    const totalDisplay = document.getElementById(type === 'add' ? 'partsTotalDisplay' : 'editPartsTotalDisplay');
    const grandTotalDisplay = document.getElementById(type === 'add' ? 'addTotalCostDisplay' : 'editTotalCostDisplay');
    const hiddenInput = document.getElementById(type === 'add' ? 'addPartsData' : 'editPartsData');

    let partsTotal = 0;
    if(cart.length === 0) {
        container.innerHTML = `<div class="text-center py-4 text-gray-400 italic text-xs">No parts selected yet.</div>`;
        if (totalDisplay) totalDisplay.innerText = '\u20b10.00';
    } else {
        container.innerHTML = cart.map((p, i) => {
            // Always use parseFloat to prevent string concatenation
            const price = parseFloat(p.price) || 0;
            const qty = parseInt(p.qty) || 1;
            const subtotal = price * qty;
            partsTotal += subtotal;
            return `
                <div class="parts-cart-item flex justify-between items-center">
                    <div class="flex-1">
                        <div class="text-xs font-bold text-gray-800">${p.name}</div>
                        <div class="text-[10px] text-gray-400">\u20b1${price.toFixed(2)} / pc</div>
                    </div>
                    <div class="flex items-center gap-3">
                        <input type="number" value="${qty}" onchange="updateQty(${i}, this.value, '${type}')" 
                            class="w-12 px-1 py-0.5 border rounded text-xs text-center focus:outline-none">
                        <div class="text-xs font-black text-gray-900 w-20 text-right">\u20b1${subtotal.toFixed(2)}</div>
                        <button type="button" onclick="removeFromCart(${i}, '${type}')" class="text-red-400 hover:text-red-600 transition">
                            <i data-lucide="x-circle" class="w-4 h-4"></i>
                        </button>
                    </div>
                </div>
            `;
        }).join('');
        if (totalDisplay) totalDisplay.innerText = '\u20b1' + partsTotal.toLocaleString('en-US', {minimumFractionDigits: 2});
    }

    // Always parseFloat on other costs to prevent string concat
    let otherTotal = otherCosts.reduce((sum, item) => sum + (parseFloat(item.price) || 0), 0);
    const grandTotal = partsTotal + otherTotal;
    
    if (grandTotalDisplay) grandTotalDisplay.innerText = '\u20b1' + grandTotal.toLocaleString('en-US', {minimumFractionDigits: 2});
    const costInput = document.getElementById(type === 'add' ? 'addTotalCostValue' : 'editTotalCostValue');
    if (costInput) costInput.value = grandTotal.toFixed(2);
    
    // Bundle everything into parts_data
    const combinedData = {
        parts: cart.map(p => ({ ...p, price: parseFloat(p.price) || 0, qty: parseInt(p.qty) || 1 })),
        others: otherCosts.map(o => ({ ...o, price: parseFloat(o.price) || 0 }))
    };
    if (hiddenInput) hiddenInput.value = JSON.stringify(combinedData);
    
    lucide.createIcons();
}

function refreshPartDropdowns() {
    const addDropdown = document.getElementById('addPartDropdown');
    const editDropdown = document.getElementById('editPartDropdown');
    
    const html = partsCatalog.map(p => {
        const safeName = p.name.replace(/'/g, "\\'").replace(/"/g, '&quot;');
        const safeSupplier = (p.supplier || '').replace(/'/g, "\\'").replace(/"/g, '&quot;');
        const isOut = (p.stock_quantity || 0) <= 0;

        return `
            <div class="search-option part-option group ${isOut ? 'opacity-50 grayscale cursor-not-allowed' : ''}" 
                data-id="${p.id}" data-name="${safeName}" data-price="${p.price}" data-qty="${p.stock_quantity || 0}" data-supplier="${safeSupplier}">
                <div class="flex justify-between items-center">
                    <div class="flex-1">
                        <div class="font-medium text-xs text-gray-900">${p.name}</div>
                        <div class="text-[9px] uppercase tracking-tighter ${isOut ? 'text-red-500 font-bold' : 'text-gray-400'}">
                            ${isOut ? '⚠️ Unavailable / Out of Stock' : (p.supplier || 'No Supplier') + ' · Stock: ' + (p.stock_quantity || 0)}
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="text-[10px] font-bold ${isOut ? 'text-gray-400' : 'text-blue-600'}">₱${parseFloat(p.price).toFixed(2)}</div>
                        <button onclick="editPartFromDropdown(${p.id}, '${safeName}', ${p.price}, ${p.stock_quantity || 0}, '${safeSupplier}', event)" 
                            class="p-1 opacity-10 sm:opacity-0 group-hover:opacity-100 hover:bg-yellow-100 rounded text-yellow-600 transition">
                            <i data-lucide="pencil" class="w-3 h-3"></i>
                        </button>
                    </div>
                </div>
            </div>
        `;
    }).join('');
    
    if (addDropdown) addDropdown.innerHTML = html;
    if (editDropdown) editDropdown.innerHTML = html;
    
    // Re-init listeners
    initPartSelectors();
    lucide.createIcons();
}

function initPartSelectors() {
    ['add', 'edit'].forEach(type => {
        const input = document.getElementById(`${type}PartDisplay`);
        const dropdown = document.getElementById(`${type}PartDropdown`);
        if(!input || !dropdown) return;
        const options = dropdown.querySelectorAll('.part-option');

        input.onfocus = () => { 
            dropdown.classList.remove('hidden'); 
            refreshPartDropdowns(); 
        };
        
        input.oninput = () => {
            const term = input.value.toLowerCase();
            options.forEach(opt => opt.style.display = opt.dataset.name.toLowerCase().includes(term) ? 'block' : 'none');
            dropdown.classList.remove('hidden');
        };

        options.forEach(opt => {
            opt.onclick = () => {
                // Prevent selection if out of stock
                if (parseInt(opt.dataset.qty) <= 0) {
                    return;
                }
                const part = { id: parseInt(opt.dataset.id), name: opt.dataset.name, price: parseFloat(opt.dataset.price) };
                addPartToCart(part, type);
                input.value = '';
                dropdown.classList.add('hidden');
            };
        });
        
        // Hide on click outside
        document.addEventListener('click', (e) => {
            if (!input.contains(e.target) && !dropdown.contains(e.target)) {
                dropdown.classList.add('hidden');
            }
        });
    });
}

function initSearchableDropdown(inputId, dropdownId, optionClass, onSelectCallback) {
    const input = document.getElementById(inputId);
    const dropdown = document.getElementById(dropdownId);
    if(!input || !dropdown) return;
    
    // We re-query options inside listeners to handle dynamic content if needed
    const getOptions = () => dropdown.querySelectorAll('.' + optionClass);

    input.addEventListener('mousedown', () => {
        closeAllDropdowns();
        dropdown.classList.remove('hidden');
    });

    input.addEventListener('input', () => {
        const searchTerm = input.value.toLowerCase();
        getOptions().forEach(opt => {
            const text = opt.dataset.name.toLowerCase();
            if (text.includes(searchTerm)) {
                opt.style.display = 'block';
            } else {
                opt.style.display = 'none';
            }
        });
        dropdown.classList.remove('hidden');
    });

    // Delegated click listener is safer if content changes, but options are static here for now
    dropdown.addEventListener('click', (e) => {
        const opt = e.target.closest('.' + optionClass);
        if (opt) {
            input.value = opt.dataset.name;
            if (opt.dataset.id) {
                const hiddenInputId = inputId.replace('Display', 'Id');
                document.getElementById(hiddenInputId).value = opt.dataset.id;
            }
            dropdown.classList.add('hidden');
            if (onSelectCallback) onSelectCallback(opt.dataset);
        }
    });

    document.addEventListener('click', (e) => {
        if (!input.contains(e.target) && !dropdown.contains(e.target)) {
            dropdown.classList.add('hidden');
        }
    });

    // Special logic for driver dropdown to highlight suggestions
    if (inputId.includes('Driver')) {
        const originalInputListener = input.oninput; // This might not work if added via addEventListener
        // Instead, we'll hook into the 'input' event we just added, but we need to run our suggestion logic
    }
}

function closeAllDropdowns() {
    document.querySelectorAll('.search-dropdown').forEach(d => d.classList.add('hidden'));
}

function filterDriverSuggestions(inputId, dropdownId, suggestedIds) {
    const dropdown = document.getElementById(dropdownId);
    if (!dropdown) return;
    const options = dropdown.querySelectorAll('.driver-option');
    const searchTerm = document.getElementById(inputId).value.toLowerCase();
    
    // Split suggested IDs into an array
    const recommendations = suggestedIds ? suggestedIds.split(',').filter(id => id && id !== 'null') : [];

    options.forEach(opt => {
        const driverId = opt.dataset.id;
        const driverName = opt.dataset.name.toLowerCase();
        const isMatch = driverName.includes(searchTerm);
        
        opt.style.display = isMatch ? 'block' : 'none';
        
        // Match suggested IDs
        const isSuggested = recommendations.includes(driverId);
        
        if (isSuggested) {
            opt.classList.add('recommended-option');
            let nameDiv = opt.querySelector('.font-medium');
            if (nameDiv && !opt.querySelector('.suggested-badge')) {
                nameDiv.innerHTML += ' <span class="suggested-badge">Recommended</span>';
            }
        } else {
            opt.classList.remove('recommended-option');
            let badge = opt.querySelector('.suggested-badge');
            if (badge) badge.remove();
        }
    });
}

document.addEventListener('DOMContentLoaded', () => {
    // Add Unit Selection
    initSearchableDropdown('addUnitDisplay', 'addUnitDropdown', 'unit-option', (data) => {
        const driverId = data.driverId;
        const secondaryId = data.secondaryId;
        const driverDisplay = document.getElementById('addDriverDisplay');
        const driverHidden = document.getElementById('addDriverId');
        
        // Clear current driver (match Boundary)
        driverDisplay.value = '';
        driverHidden.value = '';
        
        // Store suggestions and trigger filter
        const suggestions = [driverId, secondaryId].filter(id => id && id !== 'null').join(',');
        driverDisplay.dataset.suggestedIds = suggestions;
        filterDriverSuggestions('addDriverDisplay', 'addDriverDropdown', suggestions);
    });

    // Add Driver Input - show list on click/type + apply suggestion filter
    const addDriverInput = document.getElementById('addDriverDisplay');
    addDriverInput.addEventListener('mousedown', () => {
        closeAllDropdowns();
        document.getElementById('addDriverDropdown').classList.remove('hidden');
        filterDriverSuggestions('addDriverDisplay', 'addDriverDropdown', addDriverInput.dataset.suggestedIds);
    });
    addDriverInput.addEventListener('input', () => {
        document.getElementById('addDriverDropdown').classList.remove('hidden');
        filterDriverSuggestions('addDriverDisplay', 'addDriverDropdown', addDriverInput.dataset.suggestedIds);
    });
    addDriverInput.addEventListener('focus', () => {
        document.getElementById('addDriverDropdown').classList.remove('hidden');
        filterDriverSuggestions('addDriverDisplay', 'addDriverDropdown', addDriverInput.dataset.suggestedIds);
    });

    // Wire up click selection for Add Driver dropdown
    document.getElementById('addDriverDropdown').addEventListener('click', (e) => {
        const opt = e.target.closest('.driver-option');
        if (opt) {
            addDriverInput.value = opt.dataset.name;
            document.getElementById('addDriverId').value = opt.dataset.id;
            document.getElementById('addDriverDropdown').classList.add('hidden');
        }
    });
    
    initSearchableDropdown('addMechDisplay1', 'addMechDropdown1', 'mech-option');
    initSearchableDropdown('addMechDisplay2', 'addMechDropdown2', 'mech-option');
    initSearchableDropdown('editMechDisplay1', 'editMechDropdown1', 'mech-option');
    initSearchableDropdown('editMechDisplay2', 'editMechDropdown2', 'mech-option');
    
    // Edit Unit Selection
    initSearchableDropdown('editUnitDisplay', 'editUnitDropdown', 'unit-option', (data) => {
        const driverId = data.driverId;
        const secondaryId = data.secondaryId;
        const driverDisplay = document.getElementById('editDriverDisplay');
        const driverHidden = document.getElementById('editDriverId');
        
        // Clear current driver
        driverDisplay.value = '';
        driverHidden.value = '';
        
        // Store suggestions and trigger filter
        const suggestions = [driverId, secondaryId].filter(id => id && id !== 'null').join(',');
        driverDisplay.dataset.suggestedIds = suggestions;
        document.getElementById('editDriverDropdown').classList.remove('hidden');
        filterDriverSuggestions('editDriverDisplay', 'editDriverDropdown', suggestions);
    });

    // Edit Driver Input - show list on click/type + apply suggestion filter
    const editDriverInput = document.getElementById('editDriverDisplay');
    editDriverInput.addEventListener('mousedown', () => {
        closeAllDropdowns();
        document.getElementById('editDriverDropdown').classList.remove('hidden');
        filterDriverSuggestions('editDriverDisplay', 'editDriverDropdown', editDriverInput.dataset.suggestedIds);
    });
    editDriverInput.addEventListener('input', () => {
        document.getElementById('editDriverDropdown').classList.remove('hidden');
        filterDriverSuggestions('editDriverDisplay', 'editDriverDropdown', editDriverInput.dataset.suggestedIds);
    });
    editDriverInput.addEventListener('focus', () => {
        document.getElementById('editDriverDropdown').classList.remove('hidden');
        filterDriverSuggestions('editDriverDisplay', 'editDriverDropdown', editDriverInput.dataset.suggestedIds);
    });

    // Wire up click selection for Edit Driver dropdown
    document.getElementById('editDriverDropdown').addEventListener('click', (e) => {
        const opt = e.target.closest('.driver-option');
        if (opt) {
            editDriverInput.value = opt.dataset.name;
            document.getElementById('editDriverId').value = opt.dataset.id;
            document.getElementById('editDriverDropdown').classList.add('hidden');
        }
    });

    initPartSelectors();

    // Check for auto-open inventory
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('open_inventory')) {
        openPartsModal();
    }
});

// --- Supplier Management ---
function openSuppliersModal() {
    document.getElementById('suppliersModal').classList.remove('hidden');
    lucide.createIcons();
}

function closeSuppliersModal() {
    document.getElementById('suppliersModal').classList.add('hidden');
    document.getElementById('supplierId').value = '';
    document.getElementById('supplierName').value = '';
    document.getElementById('supplierContact').value = '';
    document.getElementById('supplierPhone').value = '';
}

function editSupplier(id, name, contact, phone) {
    document.getElementById('supplierId').value = id;
    document.getElementById('supplierName').value = name;
    document.getElementById('supplierContact').value = contact || '';
    document.getElementById('supplierPhone').value = phone || '';
}

async function saveSupplier() {
    const id = document.getElementById('supplierId').value;
    const name = document.getElementById('supplierName').value;
    const contact_person = document.getElementById('supplierContact').value;
    const phone_number = document.getElementById('supplierPhone').value;

    if (!name) { alert('Supplier Name is required'); return; }

    try {
        const res = await fetch("{{ route('suppliers.store') }}", {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify({ id, name, contact_person, phone_number })
        });
        const result = await res.json();
        if (result.success) {
            if (id) {
                const idx = suppliersList.findIndex(s => s.id == id);
                if (idx !== -1) suppliersList[idx] = result.data;
            } else {
                suppliersList.push(result.data);
            }
            refreshSuppliersTable();
            refreshSupplierDropdowns();
            closeSuppliersModal();
        } else {
            alert(result.message || 'Error saving supplier');
        }
    } catch (e) { console.error(e); }
}

async function deleteSupplier(id, btn) {
    if (!confirm('Archive this supplier?')) return;
    try {
        const res = await fetch("{{ url('suppliers') }}/" + id, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
        });
        const result = await res.json();
        if (result.success) {
            suppliersList = suppliersList.filter(s => s.id !== id);
            refreshSuppliersTable();
            refreshSupplierDropdowns();
        }
    } catch (e) { console.error(e); }
}

function refreshSuppliersTable() {
    const tbody = document.getElementById('suppliersTableBody');
    tbody.innerHTML = suppliersList.map(s => {
        const safeName = (s.name || '').replace(/'/g, "\\'").replace(/"/g, '&quot;');
        const safeContact = (s.contact_person || '').replace(/'/g, "\\'").replace(/"/g, '&quot;');
        return `
            <tr class="hover:bg-gray-50 transition group">
                <td class="py-3 pr-4">
                    <div class="text-sm font-black text-gray-800 tracking-tight">${s.name}</div>
                    ${(s.contact_person || s.phone_number) ? `
                        <div class="text-[10px] text-gray-400 font-bold uppercase tracking-tighter">
                            ${s.contact_person || '—'} · ${s.phone_number || '—'}
                        </div>
                    ` : ''}
                </td>
                <td class="py-3 text-right">
                    <div class="flex justify-end gap-2">
                        <button onclick="editSupplier(${s.id}, '${safeName}', '${safeContact}', '${s.phone_number || ''}')" 
                            class="p-1.5 opacity-0 group-hover:opacity-100 text-blue-400 hover:text-blue-600 hover:bg-blue-50 rounded transition">
                            <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                        </button>
                        <button onclick="deleteSupplier(${s.id}, this)" 
                            class="p-1.5 opacity-0 group-hover:opacity-100 text-red-200 hover:text-red-600 hover:bg-red-50 rounded transition">
                            <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `;
    }).join('');
    lucide.createIcons();
}

function refreshSupplierDropdowns() {
    document.querySelectorAll('.supplier-dropdown').forEach(select => {
        const currentVal = select.value;
        const defaultOption = select.querySelector('option[value=""]');
        select.innerHTML = '';
        if (defaultOption) select.appendChild(defaultOption);
        
        suppliersList.forEach(s => {
            const opt = document.createElement('option');
            opt.value = s.name;
            opt.innerText = s.name;
            if (s.name === currentVal) opt.selected = true;
            select.appendChild(opt);
        });
    });
}

function openPurchaseHistoryModal() {
    refreshPurchaseHistory();
    document.getElementById('purchaseHistoryModal').classList.remove('hidden');
}

function closePurchaseHistoryModal() {
    document.getElementById('purchaseHistoryModal').classList.add('hidden');
}

async function refreshPurchaseHistory(customDate = null) {
    try {
        const res = await fetch("{{ route('spare-parts.history') }}");
        const json = await res.json();
        if (json.success) {
            const tbody = document.getElementById('purchaseHistoryTableBody');
            const dailyTotalEl = document.getElementById('dailyPurchaseTotal');
            const monthlyTotalEl = document.getElementById('monthlyPurchaseTotal');
            const filterDate = customDate || document.getElementById('historyDateFilter').value;
            const currentMonth = new Date().getMonth();
            const currentYear = new Date().getFullYear();

            let dailyTotal = 0;
            let monthlyTotal = 0;
            let displayedRecords = 0;

            tbody.innerHTML = '';

            json.data.forEach(ph => {
                const phDate = new Date(ph.date);
                const recYear = phDate.getFullYear();
                const recMonth = phDate.getMonth();
                const amount = parseFloat(ph.amount) || 0;

                // Accrue Monthly Total (Current month)
                if (recYear === currentYear && recMonth === currentMonth) {
                    monthlyTotal += amount;
                }

                // Filtering by Date
                const rowDateStr = ph.date.substring(0, 10);
                const isDateMatch = (rowDateStr === filterDate);
                
                if (isDateMatch) {
                    dailyTotal += amount;
                    displayedRecords++;
                    
                    const dateDisplay = phDate.toLocaleDateString('en-US', { month: 'short', day: '2-digit', year: 'numeric' });
                    const timeDisplay = new Date(ph.created_at).toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', hour12: true });
                    
                    tbody.innerHTML += `
                        <tr class="hover:bg-gray-50 transition border-b border-gray-50">
                            <td class="px-4 py-4 whitespace-nowrap">
                                <div class="text-xs font-black text-gray-600">${dateDisplay}</div>
                                <div class="text-[9px] text-gray-400">${timeDisplay}</div>
                            </td>
                            <td class="px-4 py-4">
                                <div class="text-sm font-black text-gray-800 tracking-tight italic uppercase">${ph.description}</div>
                                <div class="text-[9px] text-blue-500 font-bold bg-blue-50 px-2 py-0.5 rounded-md inline-block mt-1">INVENTORY SYNC</div>
                            </td>
                            <td class="px-4 py-4 text-right">
                                <div class="text-sm font-black text-green-600">₱${amount.toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2})}</div>
                            </td>
                        </tr>
                    `;
                }
            });

            if (displayedRecords === 0) {
                tbody.innerHTML = `<tr><td colspan="3" class="px-4 py-16 text-center text-gray-400">
                    <i data-lucide="calendar-x" class="w-12 h-12 mx-auto mb-3 opacity-20"></i>
                    <p class="text-xs font-black uppercase tracking-widest italic">No purchases recorded for ${new Date(filterDate).toLocaleDateString('en-US', {month:'long', day:'numeric'})}</p>
                </td></tr>`;
                lucide.createIcons();
            }

            // Update Summaries
            dailyTotalEl.textContent = '₱' + dailyTotal.toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2});
            monthlyTotalEl.textContent = '₱' + monthlyTotal.toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2});
        }
    } catch (e) { console.error(e); }
}

// Modal inline toast — shows INSIDE the Spare Parts Catalog modal, above the table
function showModalToast(message, type = 'success') {
    const toast = document.getElementById('partsModalToast');
    if (!toast) return;

    const isSuccess = type === 'success';
    toast.className = `mb-3 p-3 rounded-xl border flex items-center gap-3 text-sm font-bold shadow-sm transition-all duration-300 ${
        isSuccess ? 'bg-green-50 border-green-200 text-green-800' : 'bg-red-50 border-red-200 text-red-800'
    }`;
    toast.innerHTML = `
        <i data-lucide="${isSuccess ? 'check-circle' : 'alert-circle'}" class="w-5 h-5 flex-shrink-0"></i>
        <span class="flex-1">${message}</span>
        <button onclick="document.getElementById('partsModalToast').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">
            <i data-lucide="x" class="w-4 h-4"></i>
        </button>
    `;
    toast.classList.remove('hidden');
    lucide.createIcons();

    // Auto-hide after 5 seconds
    clearTimeout(toast._hideTimer);
    toast._hideTimer = setTimeout(() => {
        toast.classList.add('hidden');
    }, 5000);
}

// Global Toast (for non-modal areas)
function showToast(message, type = 'success') {
    const container = document.querySelector('main .overflow-y-auto.p-4');
    if (!container) return;

    const toast = document.createElement('div');
    toast.className = `alert-slide mb-4 p-4 rounded-lg border flex items-center gap-3 shadow-md transform transition-all duration-300 ${
        type === 'success' ? 'bg-green-50 border-green-200 text-green-800' : 'bg-red-50 border-red-200 text-red-800'
    }`;
    toast.innerHTML = `
        <i data-lucide="${type === 'success' ? 'check-circle' : 'x-circle'}" class="w-5 h-5 flex-shrink-0"></i>
        <div class="flex-1 font-bold text-sm tracking-tight">${message}</div>
        <button onclick="this.parentElement.remove()" class="text-gray-400 hover:text-gray-600">
            <i data-lucide="x" class="w-4 h-4"></i>
        </button>
    `;
    container.prepend(toast);
    lucide.createIcons();
    setTimeout(() => {
        toast.style.opacity = '0';
        setTimeout(() => toast.remove(), 300);
    }, 5000);
}
</script>
@endpush
@endsection