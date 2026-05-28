<div class="space-y-4">
    {{-- Responsive Premium Header --}}
    <div class="bg-gradient-to-r from-slate-800 to-slate-900 p-4 rounded-xl text-white shadow-md">
        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-start gap-3">
            <div class="space-y-1">
                <div class="flex flex-wrap items-center gap-2">
                    <h3 class="text-xl font-extrabold tracking-tight">{{ $unit->plate_number }}</h3>
                    <div class="flex items-center gap-1.5">
                        <span class="px-2 py-0.5 bg-white bg-opacity-10 rounded text-[10px] font-bold uppercase tracking-wider">
                            {{ ucfirst($unit->status ?? '') }}
                        </span>
                        <span class="px-2 py-0.5 bg-white bg-opacity-10 rounded text-[10px] font-bold uppercase tracking-wider">
                            {{ ucfirst($unit->unit_type ?? 'Standard') }}
                        </span>
                    </div>
                </div>
                <p class="text-slate-300 text-xs font-semibold">{{ ($unit->make ?? '') . ' ' . ($unit->model ?? '') . ' (' . ($unit->year ?? '') . ')' }}</p>
            </div>
            <div class="sm:text-right flex sm:flex-col justify-between items-center sm:items-end bg-white bg-opacity-5 p-2.5 rounded-lg sm:p-0 sm:bg-transparent">
                @php
                    $displayRate = isset($unit->current_pricing['rate']) ? $unit->current_pricing['rate'] : ($unit->boundary_rate ?? 0);
                    $rateLabel = isset($unit->current_pricing['label']) ? $unit->current_pricing['label'] : 'Daily Boundary Rate';
                @endphp
                <p class="text-slate-400 text-[9px] font-black uppercase tracking-widest sm:hidden">{{ $rateLabel }}</p>
                <div class="text-right">
                    <div class="text-base sm:text-xl font-black text-blue-400 sm:text-white">₱{{ number_format((float) $displayRate, 2) }}</div>
                    <p class="text-slate-300 text-[10px] sm:text-xs font-bold hidden sm:block">{{ $rateLabel }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Swipeable Tab Navigation on Mobile --}}
    <div class="border-b border-gray-200">
        <div class="overflow-x-auto scrollbar-none" style="-webkit-overflow-scrolling: touch;">
            <nav class="-mb-px flex space-x-1 min-w-max px-1">
                <button onclick="showTab('overview')" class="tab-btn py-3 px-3.5 border-b-2 border-blue-500 font-extrabold text-xs uppercase tracking-wider text-blue-600 transition-all duration-200 whitespace-nowrap" data-tab="overview">
                    Overview
                </button>
                <button onclick="showTab('drivers')" class="tab-btn py-3 px-3.5 border-b-2 border-transparent font-extrabold text-xs uppercase tracking-wider text-gray-500 hover:text-gray-700 hover:border-gray-300 transition-all duration-200 whitespace-nowrap" data-tab="drivers">
                    Drivers
                </button>
                <button onclick="showTab('coding')" class="tab-btn py-3 px-3.5 border-b-2 border-transparent font-extrabold text-xs uppercase tracking-wider text-gray-500 hover:text-gray-700 hover:border-gray-300 transition-all duration-200 whitespace-nowrap" data-tab="coding">
                    Coding
                </button>
                <button onclick="showTab('boundary')" class="tab-btn py-3 px-3.5 border-b-2 border-transparent font-extrabold text-xs uppercase tracking-wider text-gray-500 hover:text-gray-700 hover:border-gray-300 transition-all duration-200 whitespace-nowrap" data-tab="boundary">
                    Boundary
                </button>
                <button onclick="showTab('maintenance')" class="tab-btn py-3 px-3.5 border-b-2 border-transparent font-extrabold text-xs uppercase tracking-wider text-gray-500 hover:text-gray-700 hover:border-gray-300 transition-all duration-200 whitespace-nowrap" data-tab="maintenance">
                    Maintenance
                </button>
                <button onclick="showTab('roi')" class="tab-btn py-3 px-3.5 border-b-2 border-transparent font-extrabold text-xs uppercase tracking-wider text-gray-500 hover:text-gray-700 hover:border-gray-300 transition-all duration-200 whitespace-nowrap" data-tab="roi">
                    ROI
                </button>
                <button onclick="showTab('location')" class="tab-btn py-3 px-3.5 border-b-2 border-transparent font-extrabold text-xs uppercase tracking-wider text-gray-500 hover:text-gray-700 hover:border-gray-300 transition-all duration-200 whitespace-nowrap" data-tab="location">
                    Location
                </button>
            </nav>
        </div>
    </div>

    {{-- Tabs Content Container --}}
    <div id="tabContent">
        {{-- OVERVIEW TAB --}}
        <div id="overview-tab" class="tab-content">
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-5">
                <div class="bg-white border border-gray-100 rounded-xl p-3 sm:p-4 shadow-sm hover:shadow-md transition-shadow">
                    <div class="flex items-center gap-2.5">
                        <div class="p-2 bg-blue-50 rounded-lg">
                            <i data-lucide="users" class="w-4.5 h-4.5 text-blue-600"></i>
                        </div>
                        <div>
                            <p class="text-[9px] text-gray-400 uppercase font-black tracking-widest">Drivers</p>
                            <p class="text-base sm:text-lg font-black text-gray-900">{{ count($assigned_drivers) }}/2</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white border border-gray-100 rounded-xl p-3 sm:p-4 shadow-sm hover:shadow-md transition-shadow">
                    <div class="flex items-center gap-2.5">
                        <div class="p-2 bg-green-50 rounded-lg">
                            <i data-lucide="calendar" class="w-4.5 h-4.5 text-green-600"></i>
                        </div>
                        <div>
                            <p class="text-[9px] text-gray-400 uppercase font-black tracking-widest">Next Coding</p>
                            <p class="text-base sm:text-lg font-black text-gray-900">{{ $days_until_coding ?? 0 }}d</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white border border-gray-100 rounded-xl p-3 sm:p-4 shadow-sm hover:shadow-md transition-shadow">
                    <div class="flex items-center gap-2.5">
                        <div class="p-2 bg-purple-50 rounded-lg">
                            <i data-lucide="trending-up" class="w-4.5 h-4.5 text-purple-600"></i>
                        </div>
                        <div>
                            <p class="text-[9px] text-gray-400 uppercase font-black tracking-widest">ROI</p>
                            <p class="text-base sm:text-lg font-black text-gray-900">{{ number_format((float) ($roi_data['roi_percentage'] ?? 0), 1) }}%</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white border border-gray-100 rounded-xl p-3 sm:p-4 shadow-sm hover:shadow-md transition-shadow">
                    <div class="flex items-center gap-2.5">
                        <div class="p-2 bg-orange-50 rounded-lg">
                            <i data-lucide="wrench" class="w-4.5 h-4.5 text-orange-600"></i>
                        </div>
                        <div>
                            <p class="text-[9px] text-gray-400 uppercase font-black tracking-widest">Maint</p>
                            <p class="text-base sm:text-lg font-black text-gray-900">{{ count($maintenance_records) }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                {{-- Basic Information Section --}}
                <div class="bg-white border border-gray-100 rounded-2xl p-4 sm:p-6 shadow-sm">
                    <h4 class="text-xs sm:text-sm font-black text-gray-900 mb-4 flex items-center gap-2 uppercase tracking-widest border-b border-gray-50 pb-2.5">
                        <i data-lucide="info" class="w-4 h-4 text-blue-600"></i>
                        Basic Information
                    </h4>
                    <div class="space-y-3.5">
                        <div class="flex justify-between items-center group">
                            <span class="text-[11px] sm:text-xs font-bold text-gray-400 uppercase tracking-tight">Plate Number</span>
                            <span class="font-black text-gray-900 bg-gray-50 px-2 py-0.5 rounded text-xs sm:text-sm">{{ $unit->plate_number }}</span>
                        </div>
                        <div class="flex justify-between items-center group">
                            <span class="text-[11px] sm:text-xs font-bold text-gray-400 uppercase tracking-tight">Vehicle</span>
                            <span class="font-black text-xs sm:text-sm text-gray-700">{{ ($unit->make ?? '') . ' ' . ($unit->model ?? '') }}</span>
                        </div>
                        <div class="flex justify-between items-center group">
                            <span class="text-[11px] sm:text-xs font-bold text-gray-400 uppercase tracking-tight">Year</span>
                            <span class="font-black text-xs sm:text-sm text-gray-700">{{ $unit->year }}</span>
                        </div>
                        <div class="flex justify-between items-center group">
                            <span class="text-[11px] sm:text-xs font-bold text-gray-400 uppercase tracking-tight">Status</span>
                            <span class="px-2.5 py-0.5 text-[9px] font-black uppercase rounded bg-green-50 text-green-600 border border-green-100">
                                {{ $unit->status ?? 'Active' }}
                            </span>
                        </div>
                        
                        <div class="pt-3 border-t border-gray-50 mt-3">
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <span class="text-[8px] text-gray-400 uppercase font-black tracking-widest block mb-0.5">Created</span>
                                    <span class="text-[10px] font-bold text-gray-600">{{ !empty($unit->created_at) ? \Carbon\Carbon::parse($unit->created_at)->format('M d, Y h:i A') : 'System' }}</span>
                                </div>
                                <div>
                                    <span class="text-[8px] text-gray-400 uppercase font-black tracking-widest block mb-0.5">Updated</span>
                                    <span class="text-[10px] font-bold text-gray-600">{{ !empty($unit->updated_at) ? \Carbon\Carbon::parse($unit->updated_at)->format('M d, Y h:i A') : 'System' }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-between items-center pt-3 border-t border-gray-50 mt-3">
                            <span class="text-xs font-black text-gray-900 uppercase tracking-widest">Boundary Rate</span>
                            <span class="text-base sm:text-lg font-black text-blue-600">₱{{ number_format((float) $displayRate, 2) }}</span>
                        </div>
                    </div>
                </div>

                {{-- Driver Assignment Section --}}
                <div class="bg-white border border-gray-100 rounded-2xl p-4 sm:p-6 shadow-sm">
                    <h4 class="text-xs sm:text-sm font-black text-gray-900 mb-4 flex items-center gap-2 uppercase tracking-widest border-b border-gray-50 pb-2.5">
                        <i data-lucide="users" class="w-4 h-4 text-blue-600"></i>
                        Assignment
                    </h4>
                    <div class="space-y-3.5">
                        <div class="flex justify-between items-center">
                            <span class="text-[11px] sm:text-xs font-bold text-gray-400 uppercase tracking-tight">Drivers</span>
                            <span class="font-black text-xs sm:text-sm text-gray-900">{{ count($assigned_drivers) }}/2</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-[11px] sm:text-xs font-bold text-gray-400 uppercase tracking-tight">Status</span>
                            <span class="px-2.5 py-0.5 text-[9px] font-black uppercase rounded {{ count($assigned_drivers) >= 2 ? 'bg-red-50 text-red-600 border border-red-100' : 'bg-green-50 text-green-600 border border-green-100' }}">
                                {{ count($assigned_drivers) >= 2 ? 'Full' : 'Available' }}
                            </span>
                        </div>
                        
                        @if(!empty($assigned_drivers))
                            <div class="mt-4 space-y-2.5">
                                @foreach($assigned_drivers as $driver)
                                    <div class="bg-gray-50 p-3 sm:p-4 rounded-xl border border-gray-100 group hover:border-blue-200 transition-colors">
                                        <div class="flex justify-between items-start mb-1.5">
                                            <p class="text-xs sm:text-sm font-black text-gray-900 group-hover:text-blue-600 transition-colors">{{ $driver->full_name }}</p>
                                            <span class="text-[8px] font-black bg-blue-100 text-blue-700 px-1.5 py-0.5 rounded uppercase">Active</span>
                                        </div>
                                        <div class="grid grid-cols-2 gap-2 text-[10px]">
                                            <p class="text-gray-500 font-medium">TBD-{{ substr($driver->license_number ?? '0000', -4) }} EFF</p>
                                            <p class="text-gray-500 font-medium text-right">Contact: {{ $driver->contact_number ?? 'N/A' }}</p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="mt-8 text-center py-6">
                                <div class="bg-gray-50 w-10 h-10 rounded-full flex items-center justify-center mx-auto mb-2">
                                    <i data-lucide="user-x" class="w-5 h-5 text-gray-300"></i>
                                </div>
                                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">No Drivers Assigned</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- DRIVERS TAB --}}
        <div id="drivers-tab" class="tab-content hidden">
            <div class="bg-white border border-gray-200 rounded-lg p-4 sm:p-6">
                <h4 class="text-sm sm:text-base font-black text-gray-900 uppercase tracking-widest mb-4">Assigned Drivers</h4>
                @if(!empty($assigned_drivers))
                    <div class="space-y-4">
                        @foreach($assigned_drivers as $driver)
                            <div class="border border-gray-100 rounded-xl p-3 sm:p-4 bg-gray-50 bg-opacity-50">
                                <div class="flex justify-between items-start border-b border-gray-100 pb-2.5 mb-3">
                                    <div>
                                        <h5 class="font-extrabold text-sm sm:text-base text-gray-900">{{ $driver->full_name }}</h5>
                                        <p class="text-[10px] sm:text-xs text-gray-500 font-bold uppercase tracking-tight mt-0.5">License: {{ $driver->license_number }}</p>
                                    </div>
                                    <span class="px-2 py-0.5 text-[9px] font-bold rounded bg-green-100 text-green-700 uppercase">Active</span>
                                </div>
                                <div class="grid grid-cols-2 gap-3.5 text-xs">
                                    <div>
                                        <span class="text-[10px] text-gray-400 uppercase font-black tracking-widest block mb-0.5">Contact Number</span>
                                        <p class="font-bold text-gray-800">{{ $driver->contact_number ?? 'N/A' }}</p>
                                    </div>
                                    <div>
                                        <span class="text-[10px] text-gray-400 uppercase font-black tracking-widest block mb-0.5">Daily Target</span>
                                        <p class="font-bold text-blue-600">₱{{ number_format((float) ($driver->daily_boundary_target ?? 1100), 2) }}</p>
                                    </div>
                                    <div>
                                        <span class="text-[10px] text-gray-400 uppercase font-black tracking-widest block mb-0.5">Hire Date</span>
                                        <p class="font-bold text-gray-800">{{ !empty($driver->hire_date) ? \Carbon\Carbon::parse($driver->hire_date)->format('M d, Y') : 'Not set' }}</p>
                                    </div>
                                    <div>
                                        <span class="text-[10px] text-gray-400 uppercase font-black tracking-widest block mb-0.5">License Expiry</span>
                                        <p class="font-bold text-gray-800">{{ !empty($driver->license_expiry) ? \Carbon\Carbon::parse($driver->license_expiry)->format('M d, Y') : 'Not set' }}</p>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8 text-gray-400">
                        <i data-lucide="users" class="w-10 h-10 mx-auto mb-2 text-gray-300"></i>
                        <p class="text-xs">No drivers assigned to this unit</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- CODING TAB --}}
        <div id="coding-tab" class="tab-content hidden">
            <div class="bg-white border border-gray-200 rounded-lg p-4 sm:p-6">
                <h4 class="text-sm sm:text-base font-black text-gray-900 uppercase tracking-widest mb-4">MMDA Coding Schedule</h4>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <h5 class="font-bold text-xs uppercase tracking-wider text-blue-600 mb-3 border-b border-gray-50 pb-1.5">Current Schedule</h5>
                        <div class="space-y-2.5 text-xs">
                            <div class="flex justify-between items-center">
                                <span class="text-gray-500 font-medium">Coding Day</span>
                                <span class="px-2.5 py-0.5 bg-blue-100 text-blue-800 rounded text-[10px] font-black uppercase">
                                    {{ $coding_day }}
                                </span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-gray-500 font-medium">Last Digit</span>
                                <span class="font-bold text-gray-800">{{ substr($unit->plate_number ?? '', -1) }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-gray-500 font-medium">Next Coding Date</span>
                                <span class="font-bold text-gray-800">{{ $next_coding_date }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-gray-500 font-medium">Days Until Coding</span>
                                <span class="font-bold {{ ($days_until_coding ?? 0) === 0 ? 'text-red-600' : 'text-green-600' }}">
                                    {{ ($days_until_coding ?? 0) === 0 ? 'Today' : ($days_until_coding . ' days') }}
                                </span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-gray-500 font-medium">Coding Time</span>
                                <span class="font-bold text-gray-800">7:00 AM - 10:00 AM</span>
                            </div>
                        </div>
                    </div>
                    <div>
                        <h5 class="font-bold text-xs uppercase tracking-wider text-slate-600 mb-3 border-b border-gray-50 pb-1.5">Weekly Cheat Sheet</h5>
                        <div class="space-y-1.5 text-[11px]">
                            <div class="flex justify-between p-1.5 bg-blue-50 rounded">
                                <span class="font-bold text-blue-700">Monday</span>
                                <span class="font-black text-blue-950">1, 2</span>
                            </div>
                            <div class="flex justify-between p-1.5 bg-green-50 rounded">
                                <span class="font-bold text-green-700">Tuesday</span>
                                <span class="font-black text-green-950">3, 4</span>
                            </div>
                            <div class="flex justify-between p-1.5 bg-yellow-50 rounded">
                                <span class="font-bold text-yellow-700">Wednesday</span>
                                <span class="font-black text-yellow-950">5, 6</span>
                            </div>
                            <div class="flex justify-between p-1.5 bg-orange-50 rounded">
                                <span class="font-bold text-orange-700">Thursday</span>
                                <span class="font-black text-orange-950">7, 8</span>
                            </div>
                            <div class="flex justify-between p-1.5 bg-red-50 rounded">
                                <span class="font-bold text-red-700">Friday</span>
                                <span class="font-black text-red-950">9, 0</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- BOUNDARY TAB --}}
        <div id="boundary-tab" class="tab-content hidden">
            <div class="bg-white border border-gray-200 rounded-lg p-4 sm:p-6">
                <h4 class="text-sm sm:text-base font-black text-gray-900 uppercase tracking-widest mb-4">Boundary Collection History</h4>
                @if(!empty($boundary_history))
                    <div class="overflow-x-auto scrollbar-thin">
                        <table class="min-w-full divide-y divide-gray-100">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-3 sm:px-6 py-2.5 text-left text-[10px] font-black text-gray-400 uppercase tracking-wider">Date</th>
                                    <th class="px-3 sm:px-6 py-2.5 text-left text-[10px] font-black text-gray-400 uppercase tracking-wider">Driver</th>
                                    <th class="px-3 sm:px-6 py-2.5 text-left text-[10px] font-black text-gray-400 uppercase tracking-wider">Amount</th>
                                    <th class="px-3 sm:px-6 py-2.5 text-left text-[10px] font-black text-gray-400 uppercase tracking-wider">Status</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-50">
                                @foreach($boundary_history as $index => $bh)
                                    @php
                                        $remarks = trim($bh->notes ?? $bh->remarks ?? '');
                                    @endphp
                                    {{-- Main Boundary Row --}}
                                    <tr onclick="toggleBoundaryRemarks({{ $index }})" class="hover:bg-slate-50 transition-colors cursor-pointer sm:cursor-default group/row">
                                        <td class="px-3 sm:px-6 py-3 whitespace-nowrap text-xs text-gray-700 font-medium">
                                            {{ !empty($bh->date) ? \Carbon\Carbon::parse($bh->date)->format('M d, Y') : '' }}
                                        </td>
                                        <td class="px-3 sm:px-6 py-3 whitespace-nowrap text-xs text-gray-700 font-bold">
                                            {{ $bh->full_name ?? 'N/A' }}
                                        </td>
                                        <td class="px-3 sm:px-6 py-3 whitespace-nowrap text-xs font-extrabold text-green-600">
                                            ₱{{ number_format((float) ($bh->actual_boundary ?? 0), 2) }}
                                        </td>
                                        <td class="px-3 sm:px-6 py-3 whitespace-nowrap text-xs">
                                            <div class="flex items-center justify-between gap-1">
                                                <span class="px-2 py-0.5 rounded text-[9px] font-bold uppercase {{ $bh->status === 'paid' ? 'bg-green-50 text-green-700 border border-green-100' : 'bg-red-50 text-red-700 border border-red-100' }}">
                                                    {{ $bh->status ?? 'Unpaid' }}
                                                </span>
                                                @if(!empty($remarks))
                                                    <i data-lucide="chevron-down" id="chevron-bnd-{{ $index }}" class="w-3.5 h-3.5 text-gray-400 transition-transform duration-200 sm:hidden"></i>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>

                                    {{-- Collapsible Remarks Row (Only visible on mobile and if remarks exist) --}}
                                    @if(!empty($remarks))
                                        <tr id="remarks-bnd-{{ $index }}" class="hidden bg-blue-50/40">
                                            <td colspan="4" class="px-3.5 py-2.5 text-xs text-slate-700">
                                                <div class="flex items-start gap-2 bg-white p-2.5 rounded-lg border border-blue-100 shadow-xs">
                                                    <div class="p-1 bg-blue-100 rounded text-blue-700 mt-0.5">
                                                        <i data-lucide="message-square" class="w-3.5 h-3.5"></i>
                                                    </div>
                                                    <div>
                                                        <span class="text-[8px] text-blue-600 font-black uppercase tracking-widest block mb-0.5">Remarks / Notes</span>
                                                        <p class="font-semibold text-slate-800 leading-relaxed">{{ $remarks }}</p>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-8 text-gray-400">
                        <i data-lucide="wallet" class="w-10 h-10 mx-auto mb-2 text-gray-300"></i>
                        <p class="text-xs">No boundary collection history found</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- MAINTENANCE TAB --}}
        <div id="maintenance-tab" class="tab-content hidden">
            <div class="bg-white border border-gray-200 rounded-lg p-4 sm:p-6">
                <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3 mb-5 border-b border-gray-50 pb-3">
                    <h4 class="text-xs sm:text-sm font-black text-gray-900 flex items-center gap-2 uppercase tracking-widest">
                        <i data-lucide="history" class="w-4 h-4 text-blue-600"></i>
                        Maintenance History
                    </h4>
                    
                    <form action="{{ route('units.reset-health', $unit->id) }}" method="POST" onsubmit="return confirm('RESET HEALTH COUNTER?\n\nThis will set the Maintenance baseline to the current GPS Odometer ({{ number_format((float)$unit->current_gps_odo) }} KM).\n\nUse this only if maintenance was done but not recorded.')" class="w-full sm:w-auto">
                        @csrf
                        <button type="submit" class="w-full sm:w-auto flex items-center justify-center gap-1.5 px-3 py-1.5 bg-orange-50 text-orange-600 rounded-lg hover:bg-orange-100 transition-colors text-[9px] font-black uppercase tracking-widest border border-orange-100">
                            <i data-lucide="rotate-ccw" class="w-3 h-3"></i>
                            Reset Counter
                        </button>
                    </form>
                </div>
                @if(!empty($maintenance_records))
                    <div class="space-y-5">
                        @foreach($maintenance_records as $maintenance)
                            <div class="relative pl-5 sm:pl-6 border-l-2 {{ $maintenance->status === 'completed' ? 'border-green-500' : 'border-yellow-500' }} pb-4 transition-all hover:bg-gray-50 p-3 sm:p-4 rounded-r-lg">
                                <div class="absolute -left-[9px] top-4 w-4 h-4 rounded-full border-4 border-white {{ $maintenance->status === 'completed' ? 'bg-green-500' : 'bg-yellow-500' }} shadow-sm"></div>
                                
                                <div class="flex flex-col lg:flex-row justify-between items-start gap-3 mb-2.5">
                                    <div>
                                        <div class="flex items-center gap-2 mb-1.5">
                                            <h5 class="font-extrabold text-gray-900 text-xs sm:text-sm uppercase tracking-tight">{{ $maintenance->maintenance_type ?? 'Maintenance' }}</h5>
                                            <span class="px-2 py-0.5 text-[8px] font-black rounded uppercase {{ $maintenance->status === 'completed' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                                                {{ $maintenance->status ?? 'pending' }}
                                            </span>
                                        </div>
                                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">
                                            Started: {{ !empty($maintenance->date_started) ? \Carbon\Carbon::parse($maintenance->date_started)->format('M d, Y') : 'N/A' }}
                                            @if($maintenance->date_completed)
                                                <span class="mx-1 text-slate-300">|</span> Done: {{ \Carbon\Carbon::parse($maintenance->date_completed)->format('M d, Y') }}
                                            @endif
                                        </p>
                                    </div>
                                    <div class="text-left lg:text-right w-full lg:w-auto bg-gray-50 lg:bg-transparent p-2 rounded-lg">
                                        <div class="text-[8px] text-gray-400 font-bold uppercase mb-0.5">Maintenance Cost</div>
                                        <div class="text-base sm:text-lg font-black text-red-600">₱{{ number_format((float) ($maintenance->cost ?? 0), 2) }}</div>
                                    </div>
                                </div>

                                <div class="space-y-3.5">
                                    <!-- Driver Information -->
                                    @if($maintenance->driver_name)
                                    <div class="bg-green-50/50 p-2.5 rounded-lg border border-green-100">
                                        <div class="flex items-center gap-1.5 mb-1 text-green-700">
                                            <i data-lucide="user" class="w-3.5 h-3.5"></i>
                                            <span class="text-[8px] font-black uppercase tracking-wider">Assigned Driver</span>
                                        </div>
                                        <p class="text-xs font-bold text-green-900">{{ $maintenance->driver_name }}</p>
                                    </div>
                                    @endif

                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                        <div class="bg-white p-2.5 rounded-lg border border-gray-100 shadow-xs">
                                            <span class="text-[8px] text-gray-400 font-bold uppercase block mb-1">Work Description</span>
                                            <p class="text-xs text-gray-800 leading-relaxed">{{ $maintenance->description ?? 'No description provided' }}</p>
                                        </div>
                                        <div class="bg-gray-50 p-2.5 rounded-lg border border-gray-100">
                                            <span class="text-[8px] text-gray-400 font-bold uppercase block mb-1">Mechanic</span>
                                            <p class="text-xs font-extrabold text-gray-700">{{ $maintenance->mechanic_name ?? 'Not specified' }}</p>
                                        </div>
                                    </div>

                                    <!-- Detailed Cost Breakdown -->
                                    @if(isset($maintenance->parts_details) && count($maintenance->parts_details) > 0)
                                    <div class="bg-amber-50/50 p-3 rounded-lg border border-amber-100">
                                        <div class="flex items-center gap-1.5 mb-2.5 text-amber-700">
                                            <i data-lucide="receipt" class="w-3.5 h-3.5"></i>
                                            <span class="text-[8px] font-black uppercase tracking-wider">Detailed Cost Breakdown</span>
                                        </div>
                                        
                                        <div class="space-y-1.5">
                                            @php
                                                $parts = $maintenance->parts_details->where('part_id', '!=', null);
                                                $others = $maintenance->parts_details->where('part_id', null);
                                            @endphp
                                            
                                            @if($parts->count() > 0)
                                            <div class="bg-white p-2 rounded border border-amber-100 text-[11px]">
                                                <div class="text-[8px] font-black text-gray-400 uppercase mb-1.5">Parts Replaced</div>
                                                @foreach($parts as $part)
                                                <div class="flex justify-between items-center py-1 border-b border-gray-50 last:border-0">
                                                    <div class="flex-1 pr-2">
                                                        <span class="font-semibold text-gray-800">{{ $part->part_name }}</span>
                                                        @if($part->quantity > 1)
                                                        <span class="text-gray-400 ml-1 font-bold">(x{{ $part->quantity }})</span>
                                                        @endif
                                                    </div>
                                                    <div class="font-extrabold text-gray-800">₱{{ number_format($part->total, 2) }}</div>
                                                </div>
                                                @endforeach
                                            </div>
                                            @endif

                                            @if($others->count() > 0)
                                            <div class="bg-orange-50/50 p-2 rounded border border-orange-100 text-[11px]">
                                                <div class="text-[8px] font-black text-gray-400 uppercase mb-1.5">Other Costs & Services</div>
                                                @foreach($others as $other)
                                                <div class="flex justify-between items-center py-1 border-b border-orange-50 last:border-0">
                                                    <span class="font-semibold text-gray-800">{{ $other->part_name }}</span>
                                                    <span class="font-extrabold text-gray-800">₱{{ number_format($other->total, 2) }}</span>
                                                </div>
                                                @endforeach
                                            </div>
                                            @endif
                                        </div>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-12 bg-gray-50 rounded-xl border border-dashed border-gray-200">
                        <i data-lucide="wrench" class="w-10 h-10 mx-auto mb-2 text-gray-300"></i>
                        <h5 class="text-gray-900 font-extrabold text-xs uppercase tracking-widest mb-1">No Maintenance</h5>
                        <p class="text-[11px] text-gray-500 max-w-[180px] mx-auto">This unit has no recorded maintenance jobs yet.</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- ROI TAB --}}
        <div id="roi-tab" class="tab-content hidden">
            <div class="space-y-4">
                <div class="bg-gradient-to-r from-purple-800 to-purple-950 p-4 sm:p-5 rounded-xl text-white shadow">
                    <h4 class="text-xs sm:text-sm font-black uppercase tracking-widest mb-3.5 text-purple-200 border-b border-purple-800 pb-2">ROI Analysis</h4>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3.5 text-xs">
                        <div>
                            <p class="text-purple-300 text-[9px] uppercase font-black tracking-widest block mb-0.5">Total Investment</p>
                            <p class="text-base sm:text-lg font-black">₱{{ number_format((float) ($roi_data['total_investment'] ?? 0), 2) }}</p>
                        </div>
                        <div>
                            <p class="text-purple-300 text-[9px] uppercase font-black tracking-widest block mb-0.5">Total Revenue</p>
                            <p class="text-base sm:text-lg font-black text-green-400">₱{{ number_format((float) ($roi_data['total_revenue'] ?? 0), 2) }}</p>
                        </div>
                        <div class="col-span-2 sm:col-span-1">
                            <p class="text-purple-300 text-[9px] uppercase font-black tracking-widest block mb-0.5">Total Expenses</p>
                            <p class="text-base sm:text-lg font-black text-red-400">₱{{ number_format((float) ($roi_data['total_expenses'] ?? 0), 2) }}</p>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="bg-white border border-gray-100 rounded-xl p-4 shadow-sm">
                        <h4 class="text-xs font-black text-gray-900 uppercase tracking-widest mb-3 border-b border-gray-50 pb-2">ROI Metrics</h4>
                        <div class="space-y-3 text-xs">
                            <div class="flex justify-between items-center">
                                <span class="text-gray-500 font-medium">ROI Percentage</span>
                                <span class="text-sm font-black {{ ($roi_data['roi_percentage'] ?? 0) > 0 ? 'text-green-600' : 'text-red-600' }}">
                                    {{ number_format((float) ($roi_data['roi_percentage'] ?? 0), 1) }}%
                                </span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-gray-500 font-medium">Payback Period</span>
                                <span class="text-sm font-black text-blue-600">
                                    {{ number_format((float) ($roi_data['payback_period'] ?? 0), 1) }} months
                                </span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-gray-500 font-medium">Monthly Revenue</span>
                                <span class="text-sm font-black text-green-600">
                                    ₱{{ number_format((float) ($roi_data['monthly_revenue'] ?? 0), 2) }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white border border-gray-100 rounded-xl p-4 shadow-sm">
                        <h4 class="text-xs font-black text-gray-900 uppercase tracking-widest mb-3 border-b border-gray-50 pb-2">ROI Progress</h4>
                        <div class="space-y-3.5 text-xs">
                            <div>
                                <div class="flex justify-between items-center mb-1.5">
                                    <span class="text-gray-500 font-semibold">ROI Achievement</span>
                                    <span class="font-extrabold text-purple-600">{{ number_format((float) ($roi_data['roi_percentage'] ?? 0), 1) }}%</span>
                                </div>
                                <div class="w-full bg-gray-100 rounded-full h-3">
                                    <div class="bg-purple-600 h-3 rounded-full" style="width: {{ min(100, max(0, (float) ($roi_data['roi_percentage'] ?? 0))) }}%"></div>
                                </div>
                            </div>
                            <div>
                                <div class="flex justify-between items-center mb-1.5">
                                    <span class="text-gray-500 font-semibold">Base Target Progress</span>
                                    @php
                                        $investment_per_month = ((float) ($roi_data['total_investment'] ?? 0)) / 12;
                                        $monthly_boundary = (float) ($roi_data['monthly_boundary'] ?? 0);
                                        $progress_percentage = $investment_per_month > 0 ? min(100, ($monthly_boundary / $investment_per_month) * 100) : 0;
                                    @endphp
                                    <span class="font-extrabold text-green-600">{{ number_format($progress_percentage, 1) }}%</span>
                                </div>
                                <div class="w-full bg-gray-100 rounded-full h-3">
                                    <div class="bg-green-600 h-3 rounded-full" style="width: {{ $progress_percentage }}%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- LOCATION TAB --}}
        <div id="location-tab" class="tab-content hidden">
            <div class="bg-white border border-gray-200 rounded-lg p-4 sm:p-6">
                <div class="flex justify-between items-center mb-4">
                    <h4 class="text-xs sm:text-sm font-black text-gray-900 uppercase tracking-widest flex items-center gap-2">
                        <i data-lucide="map-pin" class="w-4 h-4 text-blue-600"></i>
                        Real-Time Location
                    </h4>
                    <button onclick="refreshUnitMap({{ $unit->id }})" class="flex items-center gap-1.5 text-[10px] font-black text-blue-600 hover:text-blue-800 uppercase tracking-widest">
                        <i data-lucide="refresh-cw" class="w-3.5 h-3.5"></i>
                        REFRESH GPS
                    </button>
                </div>
                
                <div class="space-y-4">
                    {{-- GPS status grid optimized --}}
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                        <div class="bg-gray-50 rounded-lg p-3 border border-gray-100">
                            <span class="text-[9px] text-gray-400 uppercase font-black tracking-widest block mb-1">Status</span>
                            <div id="detail-gps-status-badge">
                                <span class="px-2 py-0.5 text-[9px] font-bold rounded bg-gray-100 text-gray-400 uppercase">CONNECTING...</span>
                            </div>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-3 border border-gray-100">
                            <span class="text-[9px] text-gray-400 uppercase font-black tracking-widest block mb-1">Speed</span>
                            <div class="flex items-baseline gap-0.5">
                                <span id="detail-gps-speed" class="text-base font-black text-gray-800">0.0</span>
                                <span class="text-[8px] text-gray-400 font-black uppercase">KM/H</span>
                            </div>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-3 border border-gray-100">
                            <span class="text-[9px] text-gray-400 uppercase font-black tracking-widest block mb-1">Engine</span>
                            <div id="detail-gps-ignition" class="flex items-center gap-1 text-[11px] font-black text-gray-400 uppercase mt-0.5">
                                <i data-lucide="zap" class="w-3.5 h-3.5"></i>
                                <span>OFFLINE</span>
                            </div>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-3 border border-gray-100">
                            <span class="text-[9px] text-gray-400 uppercase font-black tracking-widest block mb-1">Last Sync</span>
                            <span id="detail-gps-time" class="text-[10px] font-bold text-gray-600 block mt-1">N/A</span>
                        </div>
                    </div>

                    {{-- Map Container --}}
                    <div class="relative rounded-xl overflow-hidden border border-gray-100 shadow-inner" style="height: 300px; sm:height: 400px; background: #f8fafc;">
                        <div id="unitDetailMap" class="w-full h-full z-0"></div>
                        
                        {{-- Loading Overlay --}}
                        <div id="mapLoader" class="absolute inset-0 bg-white/80 backdrop-blur-[1px] z-10 flex flex-col items-center justify-center transition-opacity duration-300">
                            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mb-2"></div>
                            <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest">Fetching GPS Data...</p>
                        </div>

                        {{-- Error Overlay --}}
                        <div id="mapError" class="hidden absolute inset-0 bg-gray-50 z-20 flex flex-col items-center justify-center p-6 text-center">
                            <i data-lucide="map-pin-off" class="w-10 h-10 text-gray-300 mb-2"></i>
                            <h5 class="text-gray-800 font-extrabold text-xs uppercase tracking-widest mb-1">GPS Error</h5>
                            <p id="mapErrorMessage" class="text-xs text-gray-500 max-w-xs mb-3.5">We couldn't retrieve the live location for this unit.</p>
                            <a href="https://tracksolidpro.com/" target="_blank" class="px-3.5 py-2 bg-blue-600 text-white text-[10px] font-black uppercase tracking-widest rounded hover:bg-blue-700 transition-colors">
                                OPEN TRACKSOLID
                            </a>
                        </div>
                    </div>

                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-1 text-[10px] text-gray-400 px-1 pt-1.5 border-t border-gray-50">
                        <div class="flex items-center gap-1.5">
                            <i data-lucide="info" class="w-3.5 h-3.5"></i>
                            <span>Powered by Tracksolid Pro IoT Platform</span>
                        </div>
                        <div id="detail-gps-coords" class="font-mono">Coordinates: --, --</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let detailMap = null;
let detailMarker = null;

function showTab(tabName) {
    document.querySelectorAll('.tab-content').forEach(tab => {
        tab.classList.add('hidden');
    });
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.remove('border-blue-500', 'text-blue-600');
        btn.classList.add('border-transparent', 'text-gray-500');
    });
    document.getElementById(tabName + '-tab').classList.remove('hidden');
    const activeBtn = document.querySelector('[data-tab="' + tabName + '"]');
    activeBtn.classList.remove('border-transparent', 'text-gray-500');
    activeBtn.classList.add('border-blue-500', 'text-blue-600');
    
    // Trigger map load if location tab
    if (tabName === 'location') {
        setTimeout(() => {
            initUnitDetailMap({{ $unit->id }});
        }, 300);
    }
}

function toggleBoundaryRemarks(index) {
    // Check if on mobile view (< 640px screen width)
    if (window.innerWidth >= 640) {
        return; // Do nothing on Desktop/Web! "wag mo gagalwin ang web ko"
    }

    const remarksRow = document.getElementById('remarks-bnd-' + index);
    const chevronIcon = document.getElementById('chevron-bnd-' + index);

    if (remarksRow) {
        const isHidden = remarksRow.classList.contains('hidden');
        if (isHidden) {
            remarksRow.classList.remove('hidden');
            remarksRow.classList.add('animate-fade-in');
            if (chevronIcon) {
                chevronIcon.classList.add('rotate-180');
                chevronIcon.classList.remove('text-gray-400');
                chevronIcon.classList.add('text-blue-600');
            }
        } else {
            remarksRow.classList.add('hidden');
            if (chevronIcon) {
                chevronIcon.classList.remove('rotate-180');
                chevronIcon.classList.remove('text-blue-600');
                chevronIcon.classList.add('text-gray-400');
            }
        }
    }
}

function initUnitDetailMap(unitId) {
    if (!detailMap) {
        detailMap = L.map('unitDetailMap', {
            zoomControl: true,
            attributionControl: false
        }).setView([14.5995, 120.9842], 13);
        
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19
        }).addTo(detailMap);
    }
    
    // Ensure map takes full container size if it was hidden
    detailMap.invalidateSize();
    
    refreshUnitMap(unitId);
}

async function refreshUnitMap(unitId) {
    const loader = document.getElementById('mapLoader');
    const errorOverlay = document.getElementById('mapError');
    
    loader.classList.remove('opacity-0');
    loader.style.display = 'flex';
    errorOverlay.classList.add('hidden');
    
    try {
        const response = await fetch(`/live-tracking/unit/${unitId}`);
        const result = await response.json();
        
        if (result.success && result.data) {
            const data = result.data;
            const pos = [data.latitude, data.longitude];
            
            // Update Marker
            if (!detailMarker) {
                detailMarker = L.marker(pos).addTo(detailMap);
            } else {
                detailMarker.setLatLng(pos);
            }
            
            detailMap.setView(pos, 16);
            
            // Update UI Labels
            document.getElementById('detail-gps-speed').textContent = data.speed.toFixed(1);
            document.getElementById('detail-gps-time').textContent = data.last_update || 'Just now';
            document.getElementById('detail-gps-coords').textContent = `Coordinates: ${data.coordinates}`;
            
            // Status Badge
            const statusBadge = document.getElementById('detail-gps-status-badge');
            let badgeClass = 'bg-gray-100 text-gray-400';
            if (data.status === 'moving') badgeClass = 'bg-green-100 text-green-700 border border-green-200';
            if (data.status === 'idle') badgeClass = 'bg-yellow-100 text-yellow-700 border border-yellow-200';
            if (data.status === 'stopped') badgeClass = 'bg-blue-100 text-blue-700 border border-blue-200';
            
            statusBadge.innerHTML = `<span class="px-2 py-0.5 text-[9px] font-black uppercase rounded ${badgeClass}">${data.status}</span>`;
            
            // Ignition
            const ignitionIcon = document.getElementById('detail-gps-ignition');
            if (data.ignition) {
                ignitionIcon.className = 'flex items-center gap-1.5 text-xs font-black text-green-600';
                ignitionIcon.innerHTML = '<i data-lucide="zap" class="w-3.5 h-3.5 fill-green-600"></i><span>ENGINE ON</span>';
            } else {
                ignitionIcon.className = 'flex items-center gap-1.5 text-xs font-black text-gray-400';
                ignitionIcon.innerHTML = '<i data-lucide="zap-off" class="w-3.5 h-3.5"></i><span>ENGINE OFF</span>';
            }
            
            lucide.createIcons();
        } else {
            throw new Error(result.error || 'Failed to fetch GPS data');
        }
    } catch (err) {
        console.error('GPS Error:', err);
        errorOverlay.classList.remove('hidden');
        document.getElementById('mapErrorMessage').textContent = err.message;
    } finally {
        loader.classList.add('opacity-0');
        setTimeout(() => { loader.style.display = 'none'; }, 300);
    }
}
</script>
