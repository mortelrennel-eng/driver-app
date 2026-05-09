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
    @php
        $selected_day = date('l', strtotime($date));
        $real_today = date('l');
        $active_day = $selected_day;

        // Clamp to a valid weekday — coding_calendar only has Mon–Fri keys
        $valid_coding_days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
        if (!in_array($active_day, $valid_coding_days)) {
            $active_day = 'Monday';
        }
        
        $highlight_plate = request('search');
        if ($highlight_plate) {
            foreach ($coding_calendar as $day => $day_units) {
                $match = $day_units->first(function($u) use ($highlight_plate) {
                    return stripos($u->plate_number, $highlight_plate) !== false;
                });
                
                if ($match) {
                    $active_day = $day;
                    break;
                }
            }
        }
    @endphp

    <style>
        .custom-scrollbar::-webkit-scrollbar { width: 4px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: #f1f1f1; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #eab308; border-radius: 10px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #ca8a04; }
        
        @keyframes bounce-x {
            0%, 100% { transform: translateX(0); animation-timing-function: cubic-bezier(0.8, 0, 1, 1); }
            50% { transform: translateX(25%); animation-timing-function: cubic-bezier(0, 0, 0.2, 1); }
        }
        .animate-bounce-x { animation: bounce-x 1s infinite; }
    </style>





    <script>
        const realTodayName = '{{ $today_name }}';

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
                    
                    // If input is cleared, return to today's schedule
                    if (query.length === 0) {
                        const todayCard = document.querySelector(`.day-card[data-day="${realTodayName}"]`);
                        if (todayCard) showDayUnits(realTodayName, todayCard);
                    }
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

        function showDayUnits(day, card) {
            // Update table header
            document.getElementById('selected-day-display').textContent = day;
            const count = card.dataset.count;
            document.getElementById('selected-day-count-badge').textContent = count + ' units';

            // Update cards active state
            document.querySelectorAll('.day-card').forEach(c => {
                c.classList.remove('border-blue-400', 'bg-gradient-to-br', 'from-blue-50', 'to-white', 'shadow-[0_0_15px_rgba(59,130,246,0.3)]', 'transform', '-translate-y-1', 'relative', 'overflow-hidden');
                c.classList.add('border-gray-100', 'bg-gradient-to-br', 'from-gray-50/80', 'to-white', 'shadow-inner', 'hover:shadow-md', 'hover:-translate-y-0.5');
                const glow = c.querySelector('.active-glow');
                if(glow) glow.classList.add('hidden');
            });
            
            card.classList.remove('border-gray-100', 'bg-gradient-to-br', 'from-gray-50/80', 'to-white', 'shadow-inner', 'hover:shadow-md', 'hover:-translate-y-0.5');
            card.classList.add('border-blue-400', 'bg-gradient-to-br', 'from-blue-50', 'to-white', 'shadow-[0_0_15px_rgba(59,130,246,0.3)]', 'transform', '-translate-y-1', 'relative', 'overflow-hidden');
            const activeGlow = card.querySelector('.active-glow');
            if(activeGlow) activeGlow.classList.remove('hidden');

            // Update table rows and clear highlights
            let visibleRows = 0;
            document.querySelectorAll('.coding-row').forEach(row => {
                // Clear any search highlights
                row.classList.remove('bg-yellow-50/80', 'border-yellow-200');
                const plateCell = row.querySelector('td:first-child');
                if (plateCell) {
                    plateCell.classList.remove('text-blue-700');
                    // Remove arrow icon if present
                    const arrow = plateCell.querySelector('i[data-lucide="arrow-right"]');
                    if (arrow) {
                        // Extract plate number (it's the only text left if we remove the arrow)
                        const plateNumber = plateCell.textContent.trim();
                        plateCell.innerHTML = plateNumber;
                    }
                }

                if (row.dataset.day === day) {
                    row.style.display = '';
                    visibleRows++;
                } else {
                    row.style.display = 'none';
                }
            });

            // Update empty state
            document.getElementById('no-units-row').style.display = visibleRows === 0 ? '' : 'none';
        }
    </script>

    <!-- Weekly Coding Calendar (Moved to Top) -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 mb-6 overflow-hidden">
        <div class="px-6 py-4 border-b bg-gray-50/50 flex justify-between items-center flex-wrap gap-4">
            <h3 class="font-black text-gray-800 text-sm flex items-center gap-2 shrink-0">
                <i data-lucide="calendar-range" class="w-4 h-4 text-yellow-600"></i>
                Weekly Coding Calendar
            </h3>

            <div class="flex-1 max-w-sm w-full">
                <form method="GET" action="{{ route('coding.index') }}" class="relative" id="searchContainer">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i data-lucide="search" class="h-4 w-4 text-gray-400"></i>
                    </div>
                    <input type="text" name="search" id="plateSearch" autocomplete="off" value="{{ $search }}" placeholder="Search plate..."
                        class="block w-full pl-10 pr-3 py-1.5 bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 focus:outline-none text-xs font-bold text-gray-700 shadow-sm transition-all">
                    
                    <!-- Industry Standard Suggestions Dropdown -->
                    <div id="suggestionsDropdown" class="hidden absolute z-50 mt-2 w-full bg-white rounded-2xl shadow-xl border border-gray-200 overflow-hidden">
                        <div id="suggestionsList" class="max-h-60 overflow-y-auto custom-scrollbar"></div>
                        <div id="noResults" class="hidden p-4 text-center">
                            <i data-lucide="search-x" class="w-8 h-8 mx-auto mb-2 text-gray-300"></i>
                            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Not Found</p>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <div class="p-6 grid grid-cols-1 md:grid-cols-5 gap-4">
            @php $totalFleet = max(1, \App\Models\Unit::count()); @endphp
            @foreach($coding_calendar as $day => $day_units)
                <div onclick="showDayUnits('{{ $day }}', this)" data-day="{{ $day }}" data-count="{{ $day_units->count() }}" class="day-card cursor-pointer border rounded-2xl p-4 transition-all duration-300 {{ $day === $active_day ? 'border-blue-400 bg-gradient-to-br from-blue-50 to-white shadow-[0_0_15px_rgba(59,130,246,0.3)] transform -translate-y-1 relative overflow-hidden' : 'border-gray-100 bg-gradient-to-br from-gray-50/80 to-white shadow-inner hover:shadow-md hover:-translate-y-0.5' }}">
                    <div class="active-glow absolute top-0 right-0 w-16 h-16 bg-blue-400 blur-[30px] opacity-20 -mr-8 -mt-8 pointer-events-none {{ $day === $active_day ? '' : 'hidden' }}"></div>
                    <div class="flex items-center justify-between mb-2 relative z-10">
                        <h4 class="font-black {{ $day === $active_day ? 'text-blue-800' : 'text-gray-800' }} text-sm tracking-tight">{{ $day }}</h4>
                        <div class="flex items-center gap-1">
                            <span class="px-2 py-0.5 bg-white shadow-sm border border-gray-100 text-gray-500 text-[10px] font-black rounded-full">{{ $day_units->count() }}</span>
                            @if($day === $real_today)
                                <span class="px-2 py-0.5 bg-blue-600 text-white text-[8px] font-black rounded-full shadow-md shadow-blue-200">TODAY</span>
                            @endif
                        </div>
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
                <h3 class="text-sm font-black text-gray-800 uppercase tracking-widest">Coding (<span id="selected-day-display">{{ $active_day }}</span>)</h3>
                <span id="selected-day-count-badge" class="px-3 py-1 bg-yellow-100 text-yellow-800 text-[10px] font-black rounded-full">{{ $coding_calendar[$active_day]->count() }} units</span>
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
                    @foreach($coding_calendar as $day => $units)
                        @php
                            if ($highlight_plate) {
                                $units = $units->sortByDesc(function($u) use ($highlight_plate) {
                                    return stripos($u->plate_number, $highlight_plate) !== false;
                                });
                            }
                        @endphp
                        @foreach($units as $unit)
                            @php 
                                $isMatch = $highlight_plate && stripos($unit->plate_number, $highlight_plate) !== false;
                            @endphp
                            <tr class="coding-row group {{ $isMatch ? 'bg-yellow-50/80 border-yellow-200' : '' }}" data-day="{{ $day }}" style="{{ $day === $active_day ? '' : 'display: none;' }}">
                                <td class="px-6 py-4 whitespace-nowrap font-black {{ $isMatch ? 'text-blue-700' : 'text-gray-900' }} group-hover:text-blue-600 transition-colors">
                                    @if($isMatch)
                                        <span class="inline-flex items-center gap-1">
                                            <i data-lucide="arrow-right" class="w-3 h-3 text-blue-500 animate-bounce-x"></i>
                                            {{ $unit->plate_number }}
                                        </span>
                                    @else
                                        {{ $unit->plate_number }}
                                    @endif
                                </td>
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
                        @endforeach
                    @endforeach

                    <tr id="no-units-row" style="{{ $coding_calendar[$active_day]->isEmpty() ? '' : 'display: none;' }}">
                        <td colspan="5" class="px-6 py-24 text-center">
                            <div class="flex flex-col items-center justify-center">
                                <div class="relative w-56 h-36 mb-6">
                                    <div class="absolute top-2 right-6 w-12 h-12 bg-yellow-50 rounded-full animate-pulse"></div>
                                    <svg class="absolute top-6 right-2 w-16 h-16 text-gray-50" fill="currentColor" viewBox="0 0 24 24"><path d="M17.5,19c2.48,0,4.5-2.02,4.5-4.5c0-2.43-1.92-4.41-4.33-4.49C17.06,7.18,14.47,5,11.5,5c-3.13,0-5.74,2.39-6.32,5.43 C2.36,10.73,0,13.08,0,16c0,3.31,2.69,6,6,6h11.5v-3H6c-1.65,0-3-1.35-3-3s1.35-3,3-3h1.02l0.29-0.96C7.65,9.65,9.45,8,11.5,8 c2.25,0,4.1,1.69,4.42,3.9L16.07,13H17.5c0.83,0,1.5,0.67,1.5,1.5S18.33,16,17.5,16V19z"/></svg>
                                    <div class="absolute left-10 bottom-4 w-1 h-16 bg-gray-200 rounded-full"></div>
                                    <div class="absolute left-6 bottom-16 w-9 h-9 bg-emerald-50 border-2 border-emerald-200 rounded-xl flex items-center justify-center transform rotate-[-5deg] shadow-sm">
                                        <span class="text-sm font-black text-emerald-500">P</span>
                                    </div>
                                    <div class="absolute bottom-4 left-1/2 -translate-x-1/2 w-32 transform transition-transform hover:scale-105 duration-300">
                                        <div class="absolute -top-3 left-1/2 -translate-x-1/2 w-10 h-3.5 bg-yellow-400 rounded-t-sm flex items-center justify-center shadow-sm z-10 border border-yellow-500/50">
                                            <span class="text-[6px] font-black text-yellow-900 leading-none">TAXI</span>
                                        </div>
                                        <svg class="w-full h-auto text-gray-200 drop-shadow-md" viewBox="0 0 24 24" fill="currentColor">
                                            <path d="M18.92 6.01C18.72 5.42 18.16 5 17.5 5h-11c-.66 0-1.21.42-1.42 1.01L3 12v8c0 .55.45 1 1 1h1c.55 0 1-.45 1-1v-1h12v1c0 .55.45 1 1 1h1c.55 0 1-.45 1-1v-8l-2.08-5.99zM6.5 16c-.83 0-1.5-.67-1.5-1.5S5.67 13 6.5 13s1.5.67 1.5 1.5S7.33 16 6.5 16zm11 0c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5zM5 11l1.5-4.5h11L19 11H5z"/>
                                        </svg>
                                    </div>
                                    <div class="absolute bottom-2 left-0 w-full h-1 bg-gradient-to-r from-transparent via-gray-200 to-transparent rounded-full"></div>
                                </div>
                                <h4 class="text-lg font-black text-gray-800 mb-1">Free Day!</h4>
                                <p class="text-sm font-bold text-gray-400 uppercase tracking-widest">No coding restrictions for this day.</p>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
@endsection