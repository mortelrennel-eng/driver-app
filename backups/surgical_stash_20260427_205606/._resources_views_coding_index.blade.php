@php
/** @var \Illuminate\Support\Collection $units */
/** @var array $coding_calendar */
/** @var string $date */
/** @var string $search */
/** @var string $today_name */
@endphp
@extends('layouts.app')

@section('title', 'Coding Management - Euro System')
@section('page-heading', 'Coding Schedule Management')
@section('page-subheading', "Today: $today_name — Managing number coding days")

@section('content')
    <style>
        .custom-scrollbar::-webkit-scrollbar { width: 4px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: #f1f1f1; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #eab308; border-radius: 10px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #ca8a04; }
    </style>

    @php
        $tomorrow_date = date('Y-m-d', strtotime($date . ' +1 day'));
        $tomorrow_name = date('l', strtotime($tomorrow_date));
        $tomorrow_units = $coding_calendar[$tomorrow_name] ?? collect([]);
        $tomorrow_count = $tomorrow_units->count();
    @endphp

    <!-- Tomorrow's Proactive Reminder Banner -->
    <div class="mb-6 mt-2 bg-gradient-to-r from-blue-600 to-indigo-700 rounded-2xl shadow-xl shadow-blue-200 p-5 flex flex-col md:flex-row gap-4 items-center justify-between text-white relative overflow-hidden transform hover:-translate-y-1 transition-all duration-300">
        <!-- Decorative background elements -->
        <div class="absolute right-0 top-0 w-32 h-32 bg-white opacity-10 rounded-full blur-2xl -mt-10 -mr-10 pointer-events-none"></div>
        <div class="absolute right-32 bottom-0 w-24 h-24 bg-blue-300 opacity-20 rounded-full blur-xl -mb-10 pointer-events-none"></div>
        <div class="absolute left-10 top-1/2 w-40 h-40 bg-indigo-400 opacity-20 rounded-full blur-3xl -translate-y-1/2 pointer-events-none"></div>
        
        <div class="flex items-center gap-4 relative z-10 w-full md:w-auto">
            <div class="p-3 bg-white/10 rounded-xl backdrop-blur-md border border-white/20 shrink-0 shadow-inner">
                <i data-lucide="bell-ring" class="w-7 h-7 text-yellow-300 animate-pulse"></i>
            </div>
            <div>
                <h3 class="text-[10px] font-black uppercase tracking-widest text-blue-200 mb-0.5">Quick-Action Insight</h3>
                <p class="text-base font-medium leading-tight">Reminder: <strong class="text-yellow-300 font-black text-xl tabular-nums">{{ $tomorrow_count }}</strong> units will be coding tomorrow ({{ $tomorrow_name }}).</p>
            </div>
        </div>
    </div>

    <!-- Today's Focus Metrics -->
    @php
        $codingTodayCount = $stats['today_coding'];
        $onRoadCount = $stats['on_road'];
        $violationsCount = $stats['violations'];
    @endphp

    <div class="mb-6">
        <h2 class="text-sm font-black text-gray-800 uppercase tracking-widest mb-4 flex items-center gap-2">
            <i data-lucide="target" class="w-4 h-4 text-blue-600"></i> Today's Focus
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- Metric 1: Total Coding Today -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 flex items-center gap-4 relative overflow-hidden group hover:shadow-md transition-shadow cursor-default">
                <div class="absolute -right-4 -top-4 w-24 h-24 bg-red-50 rounded-full blur-xl group-hover:bg-red-100 transition-colors pointer-events-none"></div>
                <div class="w-14 h-14 rounded-full bg-red-50 border border-red-100 flex items-center justify-center shrink-0 relative z-10">
                    <i data-lucide="ban" class="w-6 h-6 text-red-500"></i>
                </div>
                <div class="relative z-10">
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-0.5">Total Coding Today</p>
                    <p class="text-3xl font-black text-gray-800 tabular-nums leading-none">{{ $codingTodayCount }}</p>
                </div>
            </div>

            <!-- Metric 2: On-Road Units -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 flex items-center gap-4 relative overflow-hidden group hover:shadow-md transition-shadow cursor-default">
                <div class="absolute -right-4 -top-4 w-24 h-24 bg-emerald-50 rounded-full blur-xl group-hover:bg-emerald-100 transition-colors pointer-events-none"></div>
                <div class="w-14 h-14 rounded-full bg-emerald-50 border border-emerald-100 flex items-center justify-center shrink-0 relative z-10">
                    <i data-lucide="navigation" class="w-6 h-6 text-emerald-500"></i>
                </div>
                <div class="relative z-10">
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-0.5">On-Road Units</p>
                    <p class="text-3xl font-black text-gray-800 tabular-nums leading-none">{{ $onRoadCount }}</p>
                </div>
            </div>

            <!-- Metric 3: Garage/Coding Alert -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 flex items-center gap-4 relative overflow-hidden group hover:shadow-md transition-shadow cursor-default">
                <div class="absolute -right-4 -top-4 w-24 h-24 {{ $violationsCount > 0 ? 'bg-orange-50' : 'bg-gray-50' }} rounded-full blur-xl group-hover:bg-orange-100 transition-colors pointer-events-none"></div>
                <div class="w-14 h-14 rounded-full {{ $violationsCount > 0 ? 'bg-orange-50 border border-orange-200' : 'bg-gray-50 border border-gray-100' }} flex items-center justify-center shrink-0 relative z-10 transition-colors">
                    <i data-lucide="alert-triangle" class="w-6 h-6 {{ $violationsCount > 0 ? 'text-orange-500 animate-pulse' : 'text-gray-400' }}"></i>
                </div>
                <div class="relative z-10">
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-0.5">Garage/Coding Alert</p>
                    @if($violationsCount > 0)
                        <div class="flex items-end gap-2">
                            <p class="text-3xl font-black text-orange-600 tabular-nums leading-none">{{ $violationsCount }}</p>
                            <span class="text-[9px] font-bold text-orange-500 uppercase tracking-widest mb-1 animate-pulse">On Road!</span>
                        </div>
                    @else
                        <p class="text-lg font-black text-gray-500 leading-tight mt-1">All Safe</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Date Filter & Actions -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 mb-6">
        <form method="GET" action="{{ route('coding.index') }}" class="flex flex-col md:flex-row gap-4 items-center">
            <div class="w-full md:w-48">
                <input type="date" name="date" value="{{ $date }}" onchange="this.form.submit()"
                    class="block w-full px-4 py-2 bg-gray-50 border border-gray-100 rounded-xl focus:ring-2 focus:ring-yellow-500 focus:outline-none text-sm font-bold text-gray-700">
            </div>
            <div class="flex-1 w-full">
                <div class="relative" id="searchContainer">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i data-lucide="search" class="h-4 w-4 text-gray-400"></i>
                    </div>
                    <input type="text" name="search" id="plateSearch" autocomplete="off" value="{{ $search }}" placeholder="Search plate..."
                        class="block w-full pl-10 pr-3 py-2 bg-gray-50 border border-gray-100 rounded-xl focus:ring-2 focus:ring-yellow-500 focus:outline-none text-sm font-bold text-gray-700">
                    
                    <!-- Industry Standard Suggestions Dropdown -->
                    <div id="suggestionsDropdown" class="hidden absolute z-50 mt-2 w-full bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden">
                        <div id="suggestionsList" class="max-h-60 overflow-y-auto custom-scrollbar"></div>
                        <div id="noResults" class="hidden p-4 text-center">
                            <i data-lucide="search-x" class="w-8 h-8 mx-auto mb-2 text-gray-300"></i>
                            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Not Found</p>
                        </div>
                    </div>
                </div>
            </div>


        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('plateSearch');
            const dropdown = document.getElementById('suggestionsDropdown');
            const list = document.getElementById('suggestionsList');
            const noResults = document.getElementById('noResults');
            let debounceTimer;

            searchInput.addEventListener('input', function() {
                clearTimeout(debounceTimer);
                const query = this.value.trim();

                if (query.length < 2) {
                    dropdown.classList.add('hidden');
                    return;
                }

                debounceTimer = setTimeout(() => {
                    fetchSuggestions(query);
                }, 300);
            });

            async function fetchSuggestions(query) {
                try {
                    const response = await fetch(`{{ route('coding.suggestions') }}?q=${encodeURIComponent(query)}`);
                    const data = await response.json();

                    renderSuggestions(data);
                } catch (error) {
                    console.error('Error fetching suggestions:', error);
                }
            }

            function renderSuggestions(items) {
                list.innerHTML = '';
                dropdown.classList.remove('hidden');

                if (items.length === 0) {
                    list.classList.add('hidden');
                    noResults.classList.remove('hidden');
                    return;
                }

                list.classList.remove('hidden');
                noResults.classList.add('hidden');

                items.forEach(item => {
                    const div = document.createElement('div');
                    div.className = 'px-4 py-3 hover:bg-gray-50 cursor-pointer flex items-center justify-between border-b border-gray-50 last:border-0 transition-colors group';
                    
                    const dayColors = {
                        'Monday': 'bg-red-100 text-red-600 border-red-200',
                        'Tuesday': 'bg-blue-100 text-blue-600 border-blue-200',
                        'Wednesday': 'bg-yellow-100 text-yellow-600 border-yellow-200',
                        'Thursday': 'bg-orange-100 text-orange-600 border-orange-200',
                        'Friday': 'bg-purple-100 text-purple-600 border-purple-200'
                    };
                    const colorClass = dayColors[item.coding_day] || 'bg-gray-100 text-gray-600 border-gray-200';

                    div.innerHTML = `
                        <div class="font-black text-gray-800 group-hover:text-blue-600 transition-colors uppercase tracking-tight">${item.plate_number}</div>
                        <div class="px-2 py-0.5 ${colorClass} text-[9px] font-black rounded-full border border-gray-100 uppercase tracking-widest">${item.coding_day}</div>
                    `;

                    div.addEventListener('click', () => {
                        searchInput.value = item.plate_number;
                        searchInput.form.submit();
                    });

                    list.appendChild(div);
                });
                
                // Refresh Lucide icons if needed
                if(window.lucide) {
                    lucide.createIcons();
                }
            }

            // Close dropdown on click outside
            document.addEventListener('click', function(e) {
                if (!document.getElementById('searchContainer').contains(e.target)) {
                    dropdown.classList.add('hidden');
                }
            });
        });
    </script>

    <!-- Weekly Coding Calendar (Moved to Top) -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 mb-6 overflow-hidden">
        <div class="px-6 py-4 border-b bg-gray-50/50 flex justify-between items-center">
            <h3 class="font-black text-gray-800 text-sm flex items-center gap-2">
                <i data-lucide="calendar-range" class="w-4 h-4 text-yellow-600"></i>
                Weekly Coding Calendar
            </h3>
        </div>
        <div class="p-6 grid grid-cols-1 md:grid-cols-5 gap-4">
            @php $totalFleet = max(1, \App\Models\Unit::count()); @endphp
            @foreach($coding_calendar as $day => $day_units)
                <div class="border rounded-2xl p-4 transition-all duration-300 {{ $day === $today_name ? 'border-blue-400 bg-gradient-to-br from-blue-50 to-white shadow-[0_0_15px_rgba(59,130,246,0.3)] transform -translate-y-1 relative overflow-hidden' : 'border-gray-100 bg-gradient-to-br from-gray-50/80 to-white shadow-inner hover:shadow-md hover:-translate-y-0.5' }}">
                    @if($day === $today_name)
                        <div class="absolute top-0 right-0 w-16 h-16 bg-blue-400 blur-[30px] opacity-20 -mr-8 -mt-8 pointer-events-none"></div>
                    @endif
                    <div class="flex items-center justify-between mb-2 relative z-10">
                        <h4 class="font-black {{ $day === $today_name ? 'text-blue-800' : 'text-gray-800' }} text-sm tracking-tight">{{ $day }}</h4>
                        <div class="flex items-center gap-1">
                            <span class="px-2 py-0.5 bg-white shadow-sm border border-gray-100 text-gray-500 text-[10px] font-black rounded-full">{{ $day_units->count() }}</span>
                            @if($day === $today_name)
                                <span class="px-2 py-0.5 bg-blue-600 text-white text-[8px] font-black rounded-full shadow-md shadow-blue-200">TODAY</span>
                            @endif
                        </div>
                    </div>

                    {{-- Heatmap Progress Bar --}}
                    @php
                        $codingCount = $day_units->count();
                        $percentage = round(($codingCount / $totalFleet) * 100);
                        
                        $barColor = 'bg-blue-400';
                        if ($percentage >= 15) $barColor = 'bg-orange-400';
                        if ($percentage >= 20) $barColor = 'bg-red-500';
                    @endphp
                    <div class="mb-4 relative z-10">
                        <div class="flex justify-between items-end mb-1">
                            <span class="text-[8px] font-black text-gray-400 uppercase tracking-widest">Fleet Impact</span>
                            <span class="text-[9px] font-black {{ $percentage >= 20 ? 'text-red-600' : 'text-gray-600' }}">{{ $percentage }}%</span>
                        </div>
                        <div class="w-full bg-gray-100 rounded-full h-1.5 shadow-inner overflow-hidden">
                            <div class="{{ $barColor }} h-1.5 rounded-full transition-all duration-1000" style="width: {{ $percentage }}%"></div>
                        </div>
                    </div>
                    <!-- Coding List Visibility: Maximum 2 units visible, then scroll -->
                    <div class="space-y-2 max-h-[120px] overflow-y-auto pr-1 custom-scrollbar relative z-10">
                        @forelse($day_units as $u)
                            @php
                                $type = strtolower($u->unit_type ?? 'sedan');
                                $pillClass = 'bg-blue-50/80 border-blue-100 text-blue-700 hover:bg-blue-100 hover:border-blue-300';
                                $iconName = 'car';
                                
                                if (str_contains($type, 'suv')) {
                                    $pillClass = 'bg-emerald-50/80 border-emerald-100 text-emerald-700 hover:bg-emerald-100 hover:border-emerald-300';
                                    $iconName = 'car-front';
                                } elseif (str_contains($type, 'van')) {
                                    $pillClass = 'bg-purple-50/80 border-purple-100 text-purple-700 hover:bg-purple-100 hover:border-purple-300';
                                    $iconName = 'bus-front';
                                }
                            @endphp
                            <div title="{{ ucfirst($type) }} - {{ $u->make }} {{ $u->model }}" class="text-[11px] flex justify-center items-center gap-1.5 px-3 py-1.5 {{ $pillClass }} backdrop-blur-sm rounded-full border shadow-sm font-black text-center hover:shadow-md hover:scale-[1.02] transition-all cursor-pointer group">
                                <i data-lucide="{{ $iconName }}" class="w-3.5 h-3.5 opacity-70 group-hover:opacity-100 transition-opacity"></i>
                                <span class="uppercase tracking-widest">{{ $u->plate_number }}</span>
                            </div>
                        @empty
                            <div class="flex flex-col items-center justify-center py-5 bg-gray-50/30 rounded-xl border border-dashed border-gray-200/60">
                                <div class="relative w-16 h-10 mb-1 opacity-60">
                                    <i data-lucide="car" class="absolute bottom-0 left-1/2 -translate-x-1/2 w-6 h-6 text-gray-400"></i>
                                    <i data-lucide="parking-circle" class="absolute top-0 right-1 w-4 h-4 text-emerald-500"></i>
                                    <div class="absolute bottom-[-2px] left-2 right-2 h-0.5 bg-gray-200 rounded-full"></div>
                                </div>
                                <span class="text-[9px] font-black text-gray-400 uppercase tracking-widest text-center leading-tight">No Coding<br>Restrictions</span>
                            </div>
                        @endforelse
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Today's Coding Units -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b bg-gray-50/50 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <i data-lucide="calendar-check" class="w-5 h-5 text-yellow-600"></i>
                <h3 class="text-sm font-black text-gray-800 uppercase tracking-widest">Coding Today ({{ $today_name }})</h3>
                <span class="px-3 py-1 bg-yellow-100 text-yellow-800 text-[10px] font-black rounded-full">{{ $today_units->count() }} units</span>
            </div>
        </div>
        
        <!-- Table Scroll: Maximum 5 units visible (approx 450px) -->
        <div class="max-h-[450px] overflow-y-auto custom-scrollbar">
            <table class="min-w-full divide-y divide-gray-100">
                <thead class="bg-gray-50/50 sticky top-0 z-10 backdrop-blur-sm">
                    <tr>
                        <th class="px-6 py-4 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Plate Number</th>
                        <th class="px-6 py-4 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Make / Model</th>
                        <th class="px-6 py-4 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Driver 1</th>
                        <th class="px-6 py-4 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Driver 2</th>
                        <th class="px-6 py-4 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Status</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-50">
                    @forelse($today_units as $unit)
                        <tr class="hover:bg-gray-100/50 even:bg-gray-50/50 transition-colors group">
                            <td class="px-6 py-4 whitespace-nowrap font-black text-gray-900 group-hover:text-blue-600 transition-colors">{{ $unit->plate_number }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-xs font-bold text-gray-500">{{ $unit->make }} {{ $unit->model }}</td>
                            <td class="px-6 py-2 whitespace-nowrap">
                                @if($unit->driver1_name)
                                    <div class="flex items-center gap-2">
                                        <div class="w-6 h-6 rounded-full bg-gradient-to-br from-blue-100 to-blue-50 border border-blue-200 flex items-center justify-center shrink-0 shadow-sm">
                                            <span class="text-[9px] font-black text-blue-700">{{ strtoupper(substr($unit->driver1_name, 0, 1)) }}</span>
                                        </div>
                                        <span class="text-xs font-bold text-gray-700">{{ $unit->driver1_name }}</span>
                                    </div>
                                @else
                                    <div class="flex items-center gap-2 opacity-60">
                                        <div class="w-6 h-6 rounded-full bg-gray-100 border border-gray-200 flex items-center justify-center shrink-0">
                                            <i data-lucide="user" class="w-3 h-3 text-gray-400"></i>
                                        </div>
                                        <span class="text-xs font-medium text-gray-400">Unassigned</span>
                                    </div>
                                @endif
                            </td>
                            <td class="px-6 py-2 whitespace-nowrap">
                                @if($unit->driver2_name)
                                    <div class="flex items-center gap-2">
                                        <div class="w-6 h-6 rounded-full bg-gradient-to-br from-indigo-100 to-indigo-50 border border-indigo-200 flex items-center justify-center shrink-0 shadow-sm">
                                            <span class="text-[9px] font-black text-indigo-700">{{ strtoupper(substr($unit->driver2_name, 0, 1)) }}</span>
                                        </div>
                                        <span class="text-xs font-bold text-gray-700">{{ $unit->driver2_name }}</span>
                                    </div>
                                @else
                                    <div class="flex items-center gap-2 opacity-60">
                                        <div class="w-6 h-6 rounded-full bg-gray-100 border border-gray-200 flex items-center justify-center shrink-0">
                                            <i data-lucide="user" class="w-3 h-3 text-gray-400"></i>
                                        </div>
                                        <span class="text-xs font-medium text-gray-400">Unassigned</span>
                                    </div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 text-[9px] font-black uppercase tracking-widest rounded-full bg-red-50 text-red-600 border border-red-100 shadow-sm">
                                    <span class="relative flex h-2 w-2">
                                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                                        <span class="relative inline-flex rounded-full h-1.5 w-1.5 bg-red-500 m-auto"></span>
                                    </span>
                                    Coding
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-24 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <!-- Minimalist Vector Illustration -->
                                    <div class="relative w-56 h-36 mb-6">
                                        <!-- Decorative Sun/Cloud -->
                                        <div class="absolute top-2 right-6 w-12 h-12 bg-yellow-50 rounded-full animate-pulse"></div>
                                        <svg class="absolute top-6 right-2 w-16 h-16 text-gray-50" fill="currentColor" viewBox="0 0 24 24"><path d="M17.5,19c2.48,0,4.5-2.02,4.5-4.5c0-2.43-1.92-4.41-4.33-4.49C17.06,7.18,14.47,5,11.5,5c-3.13,0-5.74,2.39-6.32,5.43 C2.36,10.73,0,13.08,0,16c0,3.31,2.69,6,6,6h11.5v-3H6c-1.65,0-3-1.35-3-3s1.35-3,3-3h1.02l0.29-0.96C7.65,9.65,9.45,8,11.5,8 c2.25,0,4.1,1.69,4.42,3.9L16.07,13H17.5c0.83,0,1.5,0.67,1.5,1.5S18.33,16,17.5,16V19z"/></svg>
                                        
                                        <!-- Parking Sign -->
                                        <div class="absolute left-10 bottom-4 w-1 h-16 bg-gray-200 rounded-full"></div>
                                        <div class="absolute left-6 bottom-16 w-9 h-9 bg-emerald-50 border-2 border-emerald-200 rounded-xl flex items-center justify-center transform rotate-[-5deg] shadow-sm">
                                            <span class="text-sm font-black text-emerald-500">P</span>
                                        </div>

                                        <!-- Parked Taxi -->
                                        <div class="absolute bottom-4 left-1/2 -translate-x-1/2 w-32 transform transition-transform hover:scale-105 duration-300">
                                            <!-- Taxi Top Sign -->
                                            <div class="absolute -top-3 left-1/2 -translate-x-1/2 w-10 h-3.5 bg-yellow-400 rounded-t-sm flex items-center justify-center shadow-sm z-10 border border-yellow-500/50">
                                                <span class="text-[6px] font-black text-yellow-900 leading-none">TAXI</span>
                                            </div>
                                            <!-- Car Silhouette -->
                                            <svg class="w-full h-auto text-gray-200 drop-shadow-md" viewBox="0 0 24 24" fill="currentColor">
                                                <path d="M18.92 6.01C18.72 5.42 18.16 5 17.5 5h-11c-.66 0-1.21.42-1.42 1.01L3 12v8c0 .55.45 1 1 1h1c.55 0 1-.45 1-1v-1h12v1c0 .55.45 1 1 1h1c.55 0 1-.45 1-1v-8l-2.08-5.99zM6.5 16c-.83 0-1.5-.67-1.5-1.5S5.67 13 6.5 13s1.5.67 1.5 1.5S7.33 16 6.5 16zm11 0c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5zM5 11l1.5-4.5h11L19 11H5z"/>
                                            </svg>
                                        </div>

                                        <!-- Ground Line -->
                                        <div class="absolute bottom-2 left-0 w-full h-1 bg-gradient-to-r from-transparent via-gray-200 to-transparent rounded-full"></div>
                                    </div>
                                    
                                    <h4 class="text-lg font-black text-gray-800 mb-1">Free Day!</h4>
                                    <p class="text-sm font-bold text-gray-400 uppercase tracking-widest">No coding restrictions for this day.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection