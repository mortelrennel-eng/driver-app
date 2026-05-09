<div class="space-y-4">
    <div class="bg-gradient-to-r from-blue-500 to-blue-600 p-4 rounded-lg text-white">
        <div class="flex justify-between items-start">
            <div>
                <h3 class="text-xl font-bold">{{ $unit->plate_number }}</h3>
                <p class="text-blue-100 text-sm">{{ ($unit->make ?? '') . ' ' . ($unit->model ?? '') . ' (' . ($unit->year ?? '') . ')' }}</p>
                <div class="flex items-center gap-2 mt-1">
                    <span class="px-2 py-1 bg-white bg-opacity-20 rounded-full text-xs font-medium">
                        {{ ucfirst($unit->status ?? '') }}
                    </span>
                    <span class="px-2 py-1 bg-white bg-opacity-20 rounded-full text-xs font-medium">
                        {{ ucfirst($unit->unit_type ?? 'Standard') }}
                    </span>
                </div>
            </div>
            <div class="text-right">
                @php
                    $displayRate = isset($unit->current_pricing['rate']) ? $unit->current_pricing['rate'] : ($unit->boundary_rate ?? 0);
                    $rateLabel = isset($unit->current_pricing['label']) ? $unit->current_pricing['label'] : 'Daily Boundary Rate';
                @endphp
                <div class="text-xl font-bold">₱{{ number_format((float) $displayRate, 2) }}</div>
                <p class="text-blue-100 text-xs">{{ $rateLabel }}</p>
            </div>
        </div>
    </div>

    <div class="border-b border-gray-200">
        <nav class="-mb-px flex w-full">
            <button onclick="showTab('overview')" class="tab-btn flex-1 py-3 px-1 border-b-2 border-blue-500 font-bold text-xs uppercase tracking-wider text-blue-600 transition-all duration-200" data-tab="overview">
                Overview
            </button>
            <button onclick="showTab('drivers')" class="tab-btn flex-1 py-3 px-1 border-b-2 border-transparent font-bold text-xs uppercase tracking-wider text-gray-500 hover:text-gray-700 hover:border-gray-300 transition-all duration-200" data-tab="drivers">
                Drivers
            </button>
            <button onclick="showTab('coding')" class="tab-btn flex-1 py-3 px-1 border-b-2 border-transparent font-bold text-xs uppercase tracking-wider text-gray-500 hover:text-gray-700 hover:border-gray-300 transition-all duration-200" data-tab="coding">
                Coding
            </button>
            <button onclick="showTab('boundary')" class="tab-btn flex-1 py-3 px-1 border-b-2 border-transparent font-bold text-xs uppercase tracking-wider text-gray-500 hover:text-gray-700 hover:border-gray-300 transition-all duration-200" data-tab="boundary">
                Boundary
            </button>
            <button onclick="showTab('maintenance')" class="tab-btn flex-1 py-3 px-1 border-b-2 border-transparent font-bold text-xs uppercase tracking-wider text-gray-500 hover:text-gray-700 hover:border-gray-300 transition-all duration-200" data-tab="maintenance">
                Maintenance
            </button>
            <button onclick="showTab('roi')" class="tab-btn flex-1 py-3 px-1 border-b-2 border-transparent font-bold text-xs uppercase tracking-wider text-gray-500 hover:text-gray-700 hover:border-gray-300 transition-all duration-200" data-tab="roi">
                ROI
            </button>
            <button onclick="showTab('location')" class="tab-btn flex-1 py-3 px-1 border-b-2 border-transparent font-bold text-xs uppercase tracking-wider text-gray-500 hover:text-gray-700 hover:border-gray-300 transition-all duration-200" data-tab="location">
                Location
            </button>
            <button onclick="showTab('dashcam')" class="tab-btn flex-1 py-3 px-1 border-b-2 border-transparent font-bold text-xs uppercase tracking-wider text-gray-500 hover:text-gray-700 hover:border-gray-300 transition-all duration-200" data-tab="dashcam">
                Dashcam
            </button>
        </nav>
    </div>

    <div id="tabContent">
        <div id="overview-tab" class="tab-content">
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <div class="bg-white border border-gray-100 rounded-xl p-4 shadow-sm hover:shadow-md transition-shadow">
                    <div class="flex items-center gap-3">
                        <div class="p-2.5 bg-blue-50 rounded-lg">
                            <i data-lucide="users" class="w-5 h-5 text-blue-600"></i>
                        </div>
                        <div>
                            <p class="text-[10px] text-gray-400 uppercase font-black tracking-widest">Drivers</p>
                            <p class="text-xl font-black text-gray-900">{{ count($assigned_drivers) }}/2</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white border border-gray-100 rounded-xl p-4 shadow-sm hover:shadow-md transition-shadow">
                    <div class="flex items-center gap-3">
                        <div class="p-2.5 bg-green-50 rounded-lg">
                            <i data-lucide="calendar" class="w-5 h-5 text-green-600"></i>
                        </div>
                        <div>
                            <p class="text-[10px] text-gray-400 uppercase font-black tracking-widest">Next Coding</p>
                            <p class="text-xl font-black text-gray-900">{{ $days_until_coding ?? 0 }}d</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white border border-gray-100 rounded-xl p-4 shadow-sm hover:shadow-md transition-shadow">
                    <div class="flex items-center gap-3">
                        <div class="p-2.5 bg-purple-50 rounded-lg">
                            <i data-lucide="trending-up" class="w-5 h-5 text-purple-600"></i>
                        </div>
                        <div>
                            <p class="text-[10px] text-gray-400 uppercase font-black tracking-widest">ROI</p>
                            <p class="text-xl font-black text-gray-900">{{ number_format((float) ($roi_data['roi_percentage'] ?? 0), 1) }}%</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white border border-gray-100 rounded-xl p-4 shadow-sm hover:shadow-md transition-shadow">
                    <div class="flex items-center gap-3">
                        <div class="p-2.5 bg-orange-50 rounded-lg">
                            <i data-lucide="wrench" class="w-5 h-5 text-orange-600"></i>
                        </div>
                        <div>
                            <p class="text-[10px] text-gray-400 uppercase font-black tracking-widest">Maint</p>
                            <p class="text-xl font-black text-gray-900">{{ count($maintenance_records) }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                {{-- Basic Information Section --}}
                <div class="bg-white border border-gray-100 rounded-2xl p-6 shadow-sm">
                    <h4 class="text-sm font-black text-gray-900 mb-5 flex items-center gap-2 uppercase tracking-widest border-b border-gray-50 pb-3">
                        <i data-lucide="info" class="w-4 h-4 text-blue-600"></i>
                        Basic Information
                    </h4>
                    <div class="space-y-4">
                        <div class="flex justify-between items-center group">
                            <span class="text-xs font-bold text-gray-400 uppercase tracking-tight">Plate Number</span>
                            <span class="font-black text-gray-900 bg-gray-50 px-2 py-1 rounded">{{ $unit->plate_number }}</span>
                        </div>
                        <div class="flex justify-between items-center group">
                            <span class="text-xs font-bold text-gray-400 uppercase tracking-tight">Vehicle</span>
                            <span class="font-black text-gray-700">{{ ($unit->make ?? '') . ' ' . ($unit->model ?? '') }}</span>
                        </div>
                        <div class="flex justify-between items-center group">
                            <span class="text-xs font-bold text-gray-400 uppercase tracking-tight">Year</span>
                            <span class="font-black text-gray-700">{{ $unit->year }}</span>
                        </div>
                        <div class="flex justify-between items-center group">
                            <span class="text-xs font-bold text-gray-400 uppercase tracking-tight">Status</span>
                            <span class="px-3 py-1 text-[10px] font-black uppercase rounded-full bg-green-50 text-green-600 border border-green-100">
                                {{ $unit->status ?? 'Active' }}
                            </span>
                        </div>
                        
                        <div class="pt-4 border-t border-gray-50 mt-4">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <span class="text-[9px] text-gray-400 uppercase font-black tracking-widest block mb-1">Created</span>
                                    <span class="text-[11px] font-bold text-gray-600">{{ !empty($unit->created_at) ? \Carbon\Carbon::parse($unit->created_at)->format('M d, Y h:i A') : 'System' }}</span>
                                </div>
                                <div>
                                    <span class="text-[9px] text-gray-400 uppercase font-black tracking-widest block mb-1">Updated</span>
                                    <span class="text-[11px] font-bold text-gray-600">{{ !empty($unit->updated_at) ? \Carbon\Carbon::parse($unit->updated_at)->format('M d, Y h:i A') : 'System' }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-between items-center pt-4 border-t border-gray-50 mt-4">
                            <span class="text-xs font-black text-gray-900 uppercase tracking-widest">Boundary Rate</span>
                            <span class="text-xl font-black text-blue-600">₱{{ number_format((float) $displayRate, 2) }}</span>
                        </div>
                    </div>
                </div>

                {{-- Driver Assignment Section --}}
                <div class="bg-white border border-gray-100 rounded-2xl p-6 shadow-sm">
                    <h4 class="text-sm font-black text-gray-900 mb-5 flex items-center gap-2 uppercase tracking-widest border-b border-gray-50 pb-3">
                        <i data-lucide="users" class="w-4 h-4 text-blue-600"></i>
                        Assignment
                    </h4>
                    <div class="space-y-4">
                        <div class="flex justify-between items-center">
                            <span class="text-xs font-bold text-gray-400 uppercase tracking-tight">Drivers</span>
                            <span class="font-black text-gray-900">{{ count($assigned_drivers) }}/2</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-xs font-bold text-gray-400 uppercase tracking-tight">Status</span>
                            <span class="px-3 py-1 text-[10px] font-black uppercase rounded-full {{ count($assigned_drivers) >= 2 ? 'bg-red-50 text-red-600 border border-red-100' : 'bg-green-50 text-green-600 border border-green-100' }}">
                                {{ count($assigned_drivers) >= 2 ? 'Full' : 'Available' }}
                            </span>
                        </div>
                        
                        @if(!empty($assigned_drivers))
                            <div class="mt-6 space-y-3">
                                @foreach($assigned_drivers as $driver)
                                    <div class="bg-gray-50 p-4 rounded-xl border border-gray-100 group hover:border-blue-200 transition-colors">
                                        <div class="flex justify-between items-start mb-2">
                                            <p class="text-sm font-black text-gray-900 group-hover:text-blue-600 transition-colors">{{ $driver->full_name }}</p>
                                            <span class="text-[9px] font-black bg-blue-100 text-blue-700 px-1.5 py-0.5 rounded uppercase">Active</span>
                                        </div>
                                        <div class="grid grid-cols-2 gap-2 text-[11px]">
                                            <p class="text-gray-500 font-medium">TBD-{{ substr($driver->license_number ?? '0000', -4) }} EFF</p>
                                            <p class="text-gray-500 font-medium text-right">Contact: {{ $driver->contact_number ?? 'N/A' }}</p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="mt-12 text-center py-8">
                                <div class="bg-gray-50 w-12 h-12 rounded-full flex items-center justify-center mx-auto mb-3">
                                    <i data-lucide="user-x" class="w-6 h-6 text-gray-300"></i>
                                </div>
                                <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">No Drivers Assigned</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div id="drivers-tab" class="tab-content hidden">
            <div class="bg-white border border-gray-200 rounded-lg p-6">
                <h4 class="text-lg font-semibold text-gray-900 mb-4">Assigned Drivers</h4>
                @if(!empty($assigned_drivers))
                    <div class="space-y-4">
                        @foreach($assigned_drivers as $driver)
                            <div class="border border-gray-200 rounded-lg p-4">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <h5 class="font-semibold text-gray-900">{{ $driver->full_name }}</h5>
                                        <p class="text-sm text-gray-600">License: {{ $driver->license_number }}</p>
                                        <p class="text-sm text-gray-600">Contact: {{ $driver->contact_number ?? 'N/A' }}</p>
                                    </div>
                                    <div class="text-right">
                                        <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">Active</span>
                                    </div>
                                </div>
                                <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                    <div>
                                        <span class="text-gray-600">License Number:</span>
                                        <p class="font-medium">{{ $driver->license_number ?? 'N/A' }}</p>
                                    </div>
                                    <div>
                                        <span class="text-gray-600">Contact:</span>
                                        <p class="font-medium">{{ $driver->contact_number ?? 'N/A' }}</p>
                                    </div>
                                    <div>
                                        <span class="text-gray-600">Daily Target:</span>
                                        <p class="font-medium">₱{{ number_format((float) ($driver->daily_boundary_target ?? 1100), 2) }}</p>
                                    </div>
                                    <div>
                                        <span class="text-gray-600">Hire Date:</span>
                                        <p class="font-medium">{{ !empty($driver->hire_date) ? \Carbon\Carbon::parse($driver->hire_date)->format('M d, Y') : 'Not set' }}</p>
                                    </div>
                                    <div>
                                        <span class="text-gray-600">License Expiry:</span>
                                        <p class="font-medium">{{ !empty($driver->license_expiry) ? \Carbon\Carbon::parse($driver->license_expiry)->format('M d, Y') : 'Not set' }}</p>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8 text-gray-500">
                        <i data-lucide="users" class="w-12 h-12 mx-auto mb-4 text-gray-300"></i>
                        <p>No drivers assigned to this unit</p>
                    </div>
                @endif
            </div>
        </div>

        <div id="coding-tab" class="tab-content hidden">
            <div class="bg-white border border-gray-200 rounded-lg p-6">
                <h4 class="text-lg font-semibold text-gray-900 mb-4">MMDA Coding Schedule</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h5 class="font-medium text-gray-900 mb-3">Current Coding Information</h5>
                        <div class="space-y-3">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Coding Day:</span>
                                <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm font-medium">
                                    {{ $coding_day }}
                                </span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Last Digit:</span>
                                <span class="font-medium">{{ substr($unit->plate_number ?? '', -1) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Next Coding:</span>
                                <span class="font-medium">{{ $next_coding_date }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Days Until Coding:</span>
                                <span class="font-medium {{ ($days_until_coding ?? 0) === 0 ? 'text-red-600' : 'text-green-600' }}">
                                    {{ ($days_until_coding ?? 0) === 0 ? 'Today' : ($days_until_coding . ' days') }}
                                </span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Coding Time:</span>
                                <span class="font-medium">7:00 AM - 10:00 AM</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Coding Status:</span>
                                <span class="px-2 py-1 text-xs rounded-full {{ ($days_until_coding ?? 0) === 0 ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' }}">
                                    {{ ($days_until_coding ?? 0) === 0 ? 'Coding Today' : 'No Coding' }}
                                </span>
                            </div>
                        </div>
                    </div>
                    <div>
                        <h5 class="font-medium text-gray-900 mb-3">MMDA Coding Schedule</h5>
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between p-2 bg-blue-50 rounded">
                                <span>Monday</span>
                                <span class="font-medium">1, 2</span>
                            </div>
                            <div class="flex justify-between p-2 bg-green-50 rounded">
                                <span>Tuesday</span>
                                <span class="font-medium">3, 4</span>
                            </div>
                            <div class="flex justify-between p-2 bg-yellow-50 rounded">
                                <span>Wednesday</span>
                                <span class="font-medium">5, 6</span>
                            </div>
                            <div class="flex justify-between p-2 bg-orange-50 rounded">
                                <span>Thursday</span>
                                <span class="font-medium">7, 8</span>
                            </div>
                            <div class="flex justify-between p-2 bg-red-50 rounded">
                                <span>Friday</span>
                                <span class="font-medium">9, 0</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div id="boundary-tab" class="tab-content hidden">
            <div class="bg-white border border-gray-200 rounded-lg p-6">
                <h4 class="text-lg font-semibold text-gray-900 mb-4">Boundary Collection History</h4>
                @if(!empty($boundary_history))
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Driver</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($boundary_history as $bh)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ !empty($bh->date) ? \Carbon\Carbon::parse($bh->date)->format('M d, Y') : '' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $bh->full_name ?? 'N/A' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-green-600">
                                            ₱{{ number_format((float) ($bh->actual_boundary ?? 0), 2) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ ucfirst($bh->status ?? '') }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-8 text-gray-500">
                        <i data-lucide="dollar-sign" class="w-12 h-12 mx-auto mb-4 text-gray-300"></i>
                        <p>No boundary collection history found</p>
                    </div>
                @endif
            </div>
        </div>

        <div id="maintenance-tab" class="tab-content hidden">
            <div class="bg-white border border-gray-200 rounded-lg p-6">
                <h4 class="text-lg font-semibold text-gray-900 mb-6 flex items-center gap-2">
                    <i data-lucide="history" class="w-5 h-5 text-gray-600"></i>
                    Comprehensive Maintenance History
                </h4>
                @if(!empty($maintenance_records))
                    <div class="space-y-6">
                        @foreach($maintenance_records as $maintenance)
                            <div class="relative pl-6 border-l-2 {{ $maintenance->status === 'completed' ? 'border-green-500' : 'border-yellow-500' }} pb-6 transition-all hover:bg-gray-50 p-4 rounded-r-lg">
                                <div class="absolute -left-[9px] top-4 w-4 h-4 rounded-full border-4 border-white {{ $maintenance->status === 'completed' ? 'bg-green-500' : 'bg-yellow-500' }} shadow-sm"></div>
                                
                                <div class="flex flex-col md:flex-row justify-between items-start gap-4 mb-3">
                                    <div>
                                        <div class="flex items-center gap-2 mb-1">
                                            <h5 class="font-bold text-gray-900 text-base uppercase tracking-tight">{{ $maintenance->maintenance_type ?? 'Maintenance' }}</h5>
                                            <span class="px-2 py-0.5 text-[10px] font-bold rounded-full uppercase {{ $maintenance->status === 'completed' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                                                {{ $maintenance->status ?? 'pending' }}
                                            </span>
                                        </div>
                                        <p class="text-xs font-semibold text-gray-500">
                                            <i data-lucide="calendar" class="inline w-3 h-3 mr-1"></i>
                                            Started: {{ !empty($maintenance->date_started) ? \Carbon\Carbon::parse($maintenance->date_started)->format('M d, Y') : 'N/A' }}
                                            @if($maintenance->date_completed)
                                                <span class="mx-2">|</span>
                                                Done: {{ \Carbon\Carbon::parse($maintenance->date_completed)->format('M d, Y') }}
                                            @endif
                                        </p>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-[10px] text-gray-400 font-bold uppercase mb-1">Maintenance Cost</div>
                                        <div class="text-xl font-black text-red-600">₱{{ number_format((float) ($maintenance->cost ?? 0), 2) }}</div>
                                    </div>
                                </div>

                                <div class="space-y-4">
                                    <!-- Driver Information -->
                                    @if($maintenance->driver_name)
                                    <div class="bg-green-50 p-3 rounded-lg border border-green-100">
                                        <div class="flex items-center gap-1.5 mb-2 text-green-700">
                                            <i data-lucide="user" class="w-4 h-4"></i>
                                            <span class="text-[10px] font-black uppercase tracking-wider">Assigned Driver</span>
                                        </div>
                                        <p class="text-sm font-bold text-green-900">{{ $maintenance->driver_name }}</p>
                                    </div>
                                    @endif

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div class="bg-white p-3 rounded-lg border border-gray-100 shadow-sm">
                                            <span class="text-[10px] text-gray-400 font-bold uppercase block mb-1">Work Description</span>
                                            <p class="text-sm text-gray-800 leading-relaxed">{{ $maintenance->description ?? 'No description provided' }}</p>
                                        </div>
                                        <div class="bg-gray-50 p-3 rounded-lg border border-gray-100 italic">
                                            <div class="flex items-center gap-1.5 mb-1">
                                                <i data-lucide="wrench" class="w-3.5 h-3.5 text-gray-400"></i>
                                                <span class="text-[10px] text-gray-400 font-bold uppercase">Mechanic</span>
                                            </div>
                                            <p class="text-sm font-bold text-gray-700">{{ $maintenance->mechanic_name ?? 'Not specified' }}</p>
                                        </div>
                                    </div>

                                    <!-- Detailed Cost Breakdown -->
                                    @if(isset($maintenance->parts_details) && count($maintenance->parts_details) > 0)
                                    <div class="bg-amber-50 p-4 rounded-lg border border-amber-200">
                                        <div class="flex items-center gap-1.5 mb-3 text-amber-700">
                                            <i data-lucide="receipt" class="w-4 h-4"></i>
                                            <span class="text-[10px] font-black uppercase tracking-wider">Detailed Cost Breakdown</span>
                                        </div>
                                        
                                        <div class="space-y-2">
                                            <!-- Parts List -->
                                            @php
                                                $parts = $maintenance->parts_details->where('part_id', '!=', null);
                                                $others = $maintenance->parts_details->where('part_id', null);
                                            @endphp
                                            
                                            @if($parts->count() > 0)
                                            <div class="bg-white p-3 rounded border border-amber-100">
                                                <div class="text-[10px] font-bold text-gray-600 uppercase mb-2">Parts Replaced</div>
                                                @foreach($parts as $part)
                                                <div class="flex justify-between items-center py-1 border-b border-gray-100 last:border-0">
                                                    <div class="flex-1">
                                                        <span class="text-sm font-medium text-gray-900">{{ $part->part_name }}</span>
                                                        @if($part->quantity > 1)
                                                        <span class="text-xs text-gray-500 ml-1">(x{{ $part->quantity }})</span>
                                                        @endif
                                                        @if(!empty($part->supplier))
                                                        <span class="mx-1 text-gray-300">|</span> <span class="text-[10px] font-bold text-blue-600 uppercase tracking-tight">Supplier: {{ $part->supplier }}</span>
                                                        @endif
                                                    </div>
                                                    <div class="text-sm font-bold text-gray-900">₱{{ number_format($part->total, 2) }}</div>
                                                </div>
                                                @endforeach
                                                <div class="flex justify-between items-center pt-2 mt-2 border-t border-gray-200">
                                                    <span class="text-xs font-bold text-gray-600 uppercase">Parts Subtotal</span>
                                                    <span class="text-sm font-black text-blue-600">₱{{ number_format($parts->sum('total'), 2) }}</span>
                                                </div>
                                            </div>
                                            @endif

                                            <!-- Other Costs/Services -->
                                            @if($others->count() > 0)
                                            <div class="bg-orange-50 p-3 rounded border border-orange-100">
                                                <div class="text-[10px] font-bold text-gray-600 uppercase mb-2">Other Costs & Services</div>
                                                @foreach($others as $other)
                                                <div class="flex justify-between items-center py-1 border-b border-orange-100 last:border-0">
                                                    <div class="flex-1">
                                                        <span class="text-sm font-medium text-gray-900">{{ $other->part_name }}</span>
                                                    </div>
                                                    <div class="text-sm font-bold text-gray-900">₱{{ number_format($other->total, 2) }}</div>
                                                </div>
                                                @endforeach
                                                <div class="flex justify-between items-center pt-2 mt-2 border-t border-orange-200">
                                                    <span class="text-xs font-bold text-gray-600 uppercase">Other Costs Subtotal</span>
                                                    <span class="text-sm font-black text-orange-600">₱{{ number_format($others->sum('total'), 2) }}</span>
                                                </div>
                                            </div>
                                            @endif

                                            <!-- Total Summary -->
                                            <div class="bg-gradient-to-r from-amber-100 to-amber-50 p-3 rounded border border-amber-200">
                                                <div class="flex justify-between items-center">
                                                    <span class="text-sm font-black text-gray-900 uppercase">Total Maintenance Cost</span>
                                                    <span class="text-lg font-black text-red-600">₱{{ number_format($maintenance->cost, 2) }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @endif

                                    <!-- Legacy Parts List (fallback) -->
                                    @if(!isset($maintenance->parts_details) && $maintenance->parts_list)
                                    <div class="md:col-span-2 bg-blue-50/50 p-3 rounded-lg border border-blue-100">
                                        <div class="flex items-center gap-1.5 mb-2 text-blue-700">
                                            <i data-lucide="package" class="w-4 h-4"></i>
                                            <span class="text-[10px] font-black uppercase tracking-wider">Parts Replaced</span>
                                        </div>
                                        <p class="text-sm text-blue-900 bg-white/50 p-2 rounded border border-blue-100 min-h-[40px] whitespace-pre-line">{{ $maintenance->parts_list }}</p>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-16 bg-gray-50 rounded-xl border border-dashed border-gray-200">
                        <div class="relative inline-block mb-4">
                            <i data-lucide="wrench" class="w-16 h-16 text-gray-200"></i>
                            <i data-lucide="slash" class="w-16 h-16 text-gray-100 absolute inset-0"></i>
                        </div>
                        <h5 class="text-gray-900 font-bold mb-1">No Maintenance Records</h5>
                        <p class="text-sm text-gray-500 max-w-[200px] mx-auto">This unit hasn't had any recorded maintenance jobs yet.</p>
                    </div>
                @endif
            </div>
        </div>

        <div id="roi-tab" class="tab-content hidden">
            <div class="space-y-6">
                <div class="bg-gradient-to-r from-purple-500 to-purple-600 p-6 rounded-lg text-white">
                    <h4 class="text-xl font-bold mb-4">ROI Analysis</h4>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <p class="text-purple-100">Total Investment</p>
                            <p class="text-2xl font-bold">₱{{ number_format((float) ($roi_data['total_investment'] ?? 0), 2) }}</p>
                        </div>
                        <div>
                            <p class="text-purple-100">Total Revenue</p>
                            <p class="text-2xl font-bold">₱{{ number_format((float) ($roi_data['total_revenue'] ?? 0), 2) }}</p>
                        </div>
                        <div>
                            <p class="text-purple-100">Total Expenses</p>
                            <p class="text-2xl font-bold">₱{{ number_format((float) ($roi_data['total_expenses'] ?? 0), 2) }}</p>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="bg-white border border-gray-200 rounded-lg p-6">
                        <h4 class="text-lg font-semibold text-gray-900 mb-4">ROI Metrics</h4>
                        <div class="space-y-4">
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600">ROI Percentage</span>
                                <span class="text-lg font-bold {{ ($roi_data['roi_percentage'] ?? 0) > 0 ? 'text-green-600' : 'text-red-600' }}">
                                    {{ number_format((float) ($roi_data['roi_percentage'] ?? 0), 1) }}%
                                </span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600">Payback Period</span>
                                <span class="text-lg font-bold text-blue-600">
                                    {{ number_format((float) ($roi_data['payback_period'] ?? 0), 1) }} months
                                </span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600">Monthly Revenue</span>
                                <span class="text-lg font-bold text-green-600">
                                    ₱{{ number_format((float) ($roi_data['monthly_revenue'] ?? 0), 2) }}
                                </span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600">Monthly Expenses</span>
                                <span class="text-lg font-bold text-red-600">
                                    ₱{{ number_format((float) ($roi_data['monthly_expenses'] ?? 0), 2) }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white border border-gray-200 rounded-lg p-6">
                        <h4 class="text-lg font-semibold text-gray-900 mb-4">ROI Progress</h4>
                        <div class="space-y-4">
                            <div>
                                <div class="flex justify-between items-center mb-2">
                                    <span class="text-sm text-gray-600">ROI Achievement</span>
                                    <span class="text-sm font-medium">{{ number_format((float) ($roi_data['roi_percentage'] ?? 0), 1) }}%</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-4">
                                    <div class="bg-gradient-to-r from-purple-500 to-purple-600 h-4 rounded-full" style="width: {{ min(100, max(0, (float) ($roi_data['roi_percentage'] ?? 0))) }}%"></div>
                                </div>
                            </div>
                            <div>
                                <div class="flex justify-between items-center mb-2">
                                    <span class="text-sm text-gray-600">Base Boundary to Achieve ROI</span>
                                    <span class="text-sm font-medium">₱{{ number_format(((float) ($roi_data['total_investment'] ?? 0)) / 12, 2) }}/month</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-4">
                                    @php
                                        $investment_per_month = ((float) ($roi_data['total_investment'] ?? 0)) / 12;
                                        $monthly_boundary = (float) ($roi_data['monthly_boundary'] ?? 0);
                                        $progress_percentage = $investment_per_month > 0 ? min(100, ($monthly_boundary / $investment_per_month) * 100) : 0;
                                    @endphp
                                    <div class="bg-gradient-to-r from-green-500 to-green-600 h-4 rounded-full" style="width: {{ $progress_percentage }}%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div id="location-tab" class="tab-content hidden">
            <div class="bg-white border border-gray-200 rounded-lg p-6">
                <div class="flex justify-between items-center mb-4">
                    <h4 class="text-lg font-semibold text-gray-900">Real-Time Location</h4>
                    <button onclick="refreshUnitMap({{ $unit->id }})" class="flex items-center gap-1.5 text-xs font-bold text-blue-600 hover:text-blue-800 transition-colors">
                        <i data-lucide="refresh-cw" class="w-3.5 h-3.5"></i>
                        REFRESH GPS
                    </button>
                </div>
                
                <div class="space-y-4">
                    {{-- Info Cards Row --}}
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                        <div class="bg-gray-50 rounded-lg p-3 border border-gray-100">
                            <span class="text-[10px] text-gray-400 uppercase font-black tracking-widest block mb-1">Status</span>
                            <div id="detail-gps-status-badge">
                                <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-gray-100 text-gray-400">CONNECTING...</span>
                            </div>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-3 border border-gray-100">
                            <span class="text-[10px] text-gray-400 uppercase font-black tracking-widest block mb-1">Speed</span>
                            <div class="flex items-baseline gap-1">
                                <span id="detail-gps-speed" class="text-lg font-black text-gray-800">0.0</span>
                                <span class="text-[10px] text-gray-400 font-bold">KM/H</span>
                            </div>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-3 border border-gray-100">
                            <span class="text-[10px] text-gray-400 uppercase font-black tracking-widest block mb-1">Engine</span>
                            <div id="detail-gps-ignition" class="flex items-center gap-1.5 text-sm font-bold text-gray-400">
                                <i data-lucide="zap" class="w-4 h-4"></i>
                                <span>OFFLINE</span>
                            </div>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-3 border border-gray-100">
                            <span class="text-[10px] text-gray-400 uppercase font-black tracking-widest block mb-1">Last Sync</span>
                            <span id="detail-gps-time" class="text-xs font-bold text-gray-700 block mt-1">N/A</span>
                        </div>
                    </div>

                    {{-- Map Container --}}
                    <div class="relative rounded-xl overflow-hidden border border-gray-200 shadow-inner" style="height: 400px; background: #f8fafc;">
                        <div id="unitDetailMap" class="w-full h-full z-0"></div>
                        
                        {{-- Loading Overlay --}}
                        <div id="mapLoader" class="absolute inset-0 bg-white/80 backdrop-blur-[1px] z-10 flex flex-col items-center justify-center transition-opacity duration-300">
                            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mb-2"></div>
                            <p class="text-xs font-bold text-gray-500 uppercase tracking-widest">Fetching GPS Data...</p>
                        </div>

                        {{-- Error Overlay --}}
                        <div id="mapError" class="hidden absolute inset-0 bg-gray-50 z-20 flex flex-col items-center justify-center p-6 text-center">
                            <i data-lucide="map-pin-off" class="w-12 h-12 text-gray-300 mb-3"></i>
                            <h5 class="text-gray-800 font-bold mb-1">GPS Connection Error</h5>
                            <p id="mapErrorMessage" class="text-sm text-gray-500 max-w-xs mb-4">We couldn't retrieve the live location for this unit.</p>
                            <a href="https://tracksolidpro.com/" target="_blank" class="px-4 py-2 bg-blue-600 text-white text-xs font-bold rounded-lg hover:bg-blue-700 transition-colors">
                                OPEN TRACKSOLID PORTAL
                            </a>
                        </div>
                    </div>

                    <div class="flex justify-between items-center text-[11px] text-gray-400 px-1">
                        <div class="flex items-center gap-1.5">
                            <i data-lucide="info" class="w-3.5 h-3.5"></i>
                            <span>Powered by Tracksolid Pro IoT Platform</span>
                        </div>
                        <div id="detail-gps-coords" class="font-mono">Coordinates: --, --</div>
                    </div>
                </div>
            </div>
        </div>

        <div id="dashcam-tab" class="tab-content hidden">
            <div class="bg-white border border-gray-200 rounded-lg p-6">
                <h4 class="text-lg font-semibold text-gray-900 mb-4">Dashcam Information</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h5 class="font-medium text-gray-900 mb-3">Device Status</h5>
                        <div class="space-y-3">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Dashcam Status:</span>
                                <span class="px-2 py-1 text-xs rounded-full {{ ($dashcam_info['dashcam_enabled'] ?? false) ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ ($dashcam_info['dashcam_enabled'] ?? false) ? 'Enabled' : 'Disabled' }}
                                </span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Connection Status:</span>
                                <span class="px-2 py-1 text-xs rounded-full {{ ($dashcam_info['dashcam_status'] ?? '') === 'Online' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                    {{ $dashcam_info['dashcam_status'] ?? 'Offline' }}
                                </span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Last Recording:</span>
                                <span class="font-medium">{{ $dashcam_info['last_recording'] ?? 'Never' }}</span>
                            </div>
                        </div>
                    </div>
                    <div>
                        <h5 class="font-medium text-gray-900 mb-3">Storage Information</h5>
                        <div class="space-y-3">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Storage Used:</span>
                                <span class="font-medium">{{ number_format((float) ($dashcam_info['storage_used'] ?? 0), 2) }} GB</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Total Storage:</span>
                                <span class="font-medium">{{ number_format((float) ($dashcam_info['storage_total'] ?? 0), 2) }} GB</span>
                            </div>
                            <div>
                                @php
                                    $storage_total = (float) ($dashcam_info['storage_total'] ?? 0);
                                    $storage_used = (float) ($dashcam_info['storage_used'] ?? 0);
                                    $storage_pct = $storage_total > 0 ? ($storage_used / $storage_total) * 100 : 0;
                                @endphp
                                <div class="flex justify-between items-center mb-2">
                                    <span class="text-sm text-gray-600">Storage Usage</span>
                                    <span class="text-sm font-medium">{{ number_format($storage_pct, 1) }}%</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-4">
                                    <div class="bg-gradient-to-r from-blue-500 to-blue-600 h-4 rounded-full" style="width: {{ $storage_total > 0 ? min(100, $storage_pct) : 0 }}%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mt-6">
                    <h5 class="font-medium text-gray-900 mb-3">Recent Recordings</h5>
                    <div class="bg-gray-100 rounded-lg h-32 flex items-center justify-center">
                        <div class="text-center text-gray-500">
                            <i data-lucide="video" class="w-8 h-8 mx-auto mb-2"></i>
                            <p>Video integration coming soon</p>
                        </div>
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
            
            statusBadge.innerHTML = `<span class="px-2 py-0.5 text-[10px] font-black uppercase rounded-full ${badgeClass}">${data.status}</span>`;
            
            // Ignition
            const ignitionIcon = document.getElementById('detail-gps-ignition');
            if (data.ignition) {
                ignitionIcon.className = 'flex items-center gap-1.5 text-sm font-bold text-green-600';
                ignitionIcon.innerHTML = '<i data-lucide="zap" class="w-4 h-4 fill-green-600"></i><span>ENGINE ON</span>';
            } else {
                ignitionIcon.className = 'flex items-center gap-1.5 text-sm font-bold text-gray-400';
                ignitionIcon.innerHTML = '<i data-lucide="zap-off" class="w-4 h-4"></i><span>ENGINE OFF</span>';
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
