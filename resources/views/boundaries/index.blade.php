@extends('layouts.app')

@section('title', 'Boundary Management - Euro System')
@section('page-heading', 'Boundary Management')
@section('page-subheading', 'Track daily boundary collections and payments')

@section('content')

<!-- Search and Filters -->
<div class="bg-white rounded-lg shadow p-6 mb-6">
    <form class="flex flex-col sm:flex-row gap-4" method="GET" action="{{ route('boundaries.index') }}">
        <div class="flex-1">
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i data-lucide="search" class="h-5 w-5 text-gray-400"></i>
                </div>
                <input
                    type="text"
                    id="liveSearchInput"
                    name="search"
                    value="{{ $search }}"
                    oninput="performLiveSearch()"
                    autocomplete="off"
                    class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 focus:outline-none"
                    placeholder="Search by plate number or driver..."
                >
            </div>
        </div>
        
        <div class="sm:w-40">
            <input
                type="date"
                id="filterDate"
                name="date"
                value="{{ $date_filter }}"
                onchange="performLiveSearch()"
                class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 focus:outline-none"
            >
        </div>
        
        <div class="sm:w-40">
            <select
                id="filterStatus"
                name="status"
                onchange="performLiveSearch()"
                class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 focus:outline-none"
            >
                <option value="">All Status</option>
                <option value="pending" {{ $status_filter === 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="paid" {{ $status_filter === 'paid' ? 'selected' : '' }}>Paid</option>
                <option value="shortage" {{ $status_filter === 'shortage' ? 'selected' : '' }}>Shortage</option>
                <option value="excess" {{ $status_filter === 'excess' ? 'selected' : '' }}>Excess</option>
            </select>
        </div>
        
        <div class="flex gap-2">
            <a href="{{ route('boundary-rules.index') }}"
                class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-300 flex items-center gap-2 border border-gray-200 transition-all font-semibold"
                title="Manage Year-Based Pricing Rules"
            >
                <i data-lucide="settings" class="w-4 h-4"></i>
                Pricing Rules
            </a>
            
            <button type="button"
                onclick="addBoundary()"
                class="px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-yellow-500 flex items-center gap-2 shadow-sm font-bold"
            >
                <i data-lucide="plus" class="w-4 h-4"></i>
                Add Boundary
            </button>
        </div>
    </form>
</div>

<!-- Boundaries Table -->
<div id="boundariesTableWrapper">
    @include('boundaries.partials._boundaries_table', ['boundaries' => $boundariesArray])
</div>

<!-- Boundary Modal -->
<div id="boundaryModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm hidden z-50 flex items-center justify-center p-4 transition-all">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-4xl max-h-[95vh] flex flex-col overflow-hidden">
        {{-- Header (Deep Navy matching Unit Details) --}}
        <div class="bg-slate-800 p-5 shrink-0">
            <div class="flex justify-between items-start">
                <div class="flex items-center gap-3">
                    <div class="p-2.5 bg-white/10 rounded-xl">
                        <i data-lucide="calculator" class="w-6 h-6 text-yellow-500"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-black text-white tracking-wide" id="modalTitle">Add Boundary Record</h3>
                        <p class="text-xs font-medium text-slate-300 mt-0.5">Record daily collections and evaluate driver performance.</p>
                    </div>
                </div>
                <button onclick="closeModal()" type="button" class="text-slate-400 hover:text-white bg-slate-700/50 hover:bg-slate-700 p-2 rounded-full transition-colors">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
        </div>
        
        <form id="boundaryForm" method="POST" action="{{ route('boundaries.store') }}" class="flex flex-col flex-1 min-h-0">
            @csrf
            <input type="hidden" name="action" id="formAction" value="add_boundary">
            <input type="hidden" name="id" id="boundaryId">
            
            <div class="p-6 overflow-y-auto flex-1 space-y-5">
                
                {{-- Two-Column Grid for Unit & Driver --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-1.5">Unit <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <input type="text" id="unitDisplay" required 
                                   class="w-full px-3 py-2.5 border border-gray-300 rounded-xl bg-white cursor-pointer focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 text-sm font-bold shadow-sm"
                                   placeholder="Type to search units...">
                            <input type="hidden" name="unit_id" id="unitId" required>
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                <i data-lucide="chevron-down" class="w-4 h-4 text-gray-400"></i>
                            </div>
                            
                            <!-- Unit Dropdown -->
                            <div id="unit_dropdown" class="absolute z-50 w-full mt-1 bg-white border border-gray-200 rounded-xl shadow-xl max-h-48 overflow-y-auto hidden">
                                @foreach ($units as $unit)
                                    <div class="unit-option px-3 py-2 hover:bg-yellow-50 cursor-pointer border-b border-gray-100 last:border-b-0"
                                         data-id="{{ $unit['id'] }}"
                                         data-name="{{ $unit['plate_number'] }}"
                                         data-plate="{{ $unit['plate_number'] }}"
                                         data-year="{{ $unit['year'] ?? 0 }}"
                                         data-model="{{ $unit['make_model'] ?? '' }}"
                                         data-rate="{{ $unit['boundary_rate'] ?? 0 }}"
                                         data-coding-day="{{ $unit['coding_day'] ?? '' }}"
                                         data-primary-id="{{ $unit['driver_id'] }}"
                                         data-secondary-id="{{ $unit['secondary_driver_id'] }}"
                                         data-expected-id="{{ $unit['current_turn_driver_id'] }}"
                                         data-deadline="{{ $unit['shift_deadline_at'] }}"
                                         data-swapped-at="{{ $unit['last_swapping_at'] }}"
                                         data-has-absent-today="{{ $unit['has_absent_today'] ? 'true' : 'false' }}">
                                        <div class="font-black text-sm text-gray-900">{{ $unit['plate_number'] }}</div>
                                        <div class="text-[11px] font-bold text-gray-500">{{ $unit['make_model'] ?? 'N/A' }}</div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-1.5">Driver <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <input type="text" id="driverDisplay" required 
                                   class="w-full px-3 py-2.5 border border-gray-300 rounded-xl bg-white cursor-pointer focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 text-sm font-bold shadow-sm"
                                   placeholder="Type to search drivers...">
                            <input type="hidden" name="driver_id" id="driverId" required>
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                <i data-lucide="chevron-down" class="w-4 h-4 text-gray-400"></i>
                            </div>
                            
                            <!-- Driver Dropdown -->
                            <div id="driver_dropdown" class="absolute z-50 w-full mt-1 bg-white border border-gray-200 rounded-xl shadow-xl max-h-48 overflow-y-auto hidden">
                                <div class="driver-option px-3 py-2 hover:bg-yellow-50 cursor-pointer border-b border-gray-100"
                                     data-id="all"
                                     data-name="All Drivers"
                                     data-unit=""
                                     data-plate="">
                                    <div class="font-black text-sm text-gray-900">All Drivers</div>
                                    <div class="text-[11px] font-bold text-gray-500">Show all available drivers</div>
                                </div>
                                <div class="all-drivers-list">
                                    @foreach ($all_drivers as $driver)
                                        <div class="driver-option px-3 py-2 hover:bg-yellow-50 cursor-pointer border-b border-gray-100 last:border-b-0"
                                             data-id="{{ $driver['id'] }}"
                                             data-name="{{ $driver['name'] }}"
                                             data-unit="{{ $driver['current_unit'] }}"
                                             data-plate="{{ $driver['current_plate'] }}"
                                             data-shortage="{{ $driver['net_shortage'] ?? 0 }}"
                                             data-has-accident-debt="{{ ($driver['has_accident_debt'] ?? 0) > 0 ? 'true' : 'false' }}"
                                             data-accident-debt-amount="{{ $driver['total_accident_debt'] ?? 0 }}">
                                            <div class="font-black text-sm text-gray-900">{{ $driver['name'] }}</div>
                                            <div class="text-[11px] font-bold text-gray-500">{{ $driver['current_plate'] }}</div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Alerts --}}
                <div id="extraDriverAlert" class="hidden px-4 py-3 bg-orange-50 border border-orange-200 rounded-xl flex items-start gap-3 shadow-sm">
                    <span class="text-orange-500 mt-0.5"><i data-lucide="alert-triangle" class="w-5 h-5"></i></span>
                    <div>
                        <p class="text-sm font-black text-orange-800">Extra Driver Detected</p>
                        <p class="text-xs font-medium text-orange-700 mt-0.5">This driver is not regularly assigned to this unit. The record will be marked as <strong>Extra Driver</strong>.</p>
                    </div>
                </div>

                <div id="shortageBalanceAlert" class="hidden px-4 py-3 bg-red-50 border border-red-200 rounded-xl shadow-sm">
                    <div class="flex items-start gap-3 mb-3">
                        <span class="text-red-500 mt-0.5"><i data-lucide="alert-circle" class="w-5 h-5"></i></span>
                        <div class="flex-1">
                            <p class="text-sm font-black text-red-800">Past Balance Due</p>
                            <p class="text-xs font-medium text-red-700 mt-0.5">This driver has an unpaid balance of <strong id="shortageBalanceAmount" class="font-black">₱0.00</strong> from previous shortages.</p>
                        </div>
                    </div>
                    <button type="button" onclick="payFullBalance()" 
                            class="w-full py-2 bg-red-600 hover:bg-red-700 text-white text-xs font-black uppercase tracking-widest rounded-lg transition-colors shadow-sm focus:ring-2 focus:ring-red-500 focus:ring-offset-1">
                        Pay Full Balance & Clear Debt
                    </button>
                    <input type="hidden" id="rawShortageAmount" value="0">
                </div>

                {{-- Shift Status --}}
                <div id="shiftInfoGroup" class="hidden rounded-xl border border-gray-200 transition-all duration-300 overflow-hidden shadow-sm">
                    <div class="px-4 py-2.5 flex items-center justify-between border-b border-gray-100 bg-gray-50" id="shiftInfoHeader">
                        <span class="text-[10px] uppercase font-black tracking-widest text-gray-500">Shift Status</span>
                        <div id="incentiveStatusBadge"></div>
                    </div>
                    <div class="px-4 py-3 flex items-start gap-3" id="shiftInfoBody">
                        <div class="p-2 rounded-lg bg-gray-100 shrink-0" id="shiftIconWrap">
                            <i data-lucide="user-check" class="w-4 h-4 text-gray-600" id="shiftIcon"></i>
                        </div>
                        <div class="flex flex-col gap-1 w-full pt-0.5">
                            <span id="shiftMainLabel" class="text-sm font-black text-gray-800 leading-tight"></span>
                            <span id="shiftTimer" class="text-xs text-gray-500 font-bold leading-snug"></span>
                        </div>
                    </div>
                    <div id="shiftExtraNotice" class="hidden px-4 py-2.5 border-t border-orange-100 bg-orange-50 flex items-start gap-2">
                        <span class="text-orange-500 text-sm mt-0.5"><i data-lucide="alert-triangle" class="w-4 h-4"></i></span>
                        <p id="shiftExtraText" class="text-xs text-orange-800 font-bold leading-snug pt-0.5"></p>
                    </div>
                </div>

                {{-- Three-Column Grid for Date, Target, Actual --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-1.5">Date <span class="text-red-500">*</span></label>
                        <input type="date" name="date" id="date" required value="{{ date('Y-m-d') }}" 
                               class="w-full px-3 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 text-sm font-bold shadow-sm">
                    </div>
                    
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-1.5">Target Boundary <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                                <span class="text-gray-500 font-black">₱</span>
                            </div>
                            <input type="number" name="boundary_amount" id="boundaryAmount" required step="0.01" min="0" readonly
                                   class="w-full pl-8 px-3 py-2.5 border-2 border-yellow-100 bg-yellow-50/50 rounded-xl focus:ring-0 cursor-not-allowed font-black text-gray-600 shadow-inner text-base"
                                   title="Target boundary for this shift. This is fixed based on year-based rules.">
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-xs font-bold text-blue-600 uppercase tracking-widest mb-1.5">Actual Collected <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                                <span class="text-blue-600 font-black">₱</span>
                            </div>
                            <input type="number" name="actual_boundary" id="actualBoundary" required step="0.01" min="0" 
                                   oninput="validateActualCollected()"
                                   class="w-full pl-8 px-3 py-2.5 border-2 border-blue-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 font-black text-blue-800 shadow-sm text-base"
                                   placeholder="0.00">
                        </div>
                    </div>
                </div>

                <!-- Damage Payment Section (Hidden by default, shown if driver has accident debt) -->
                <div id="damagePaymentContainer" class="hidden p-4 bg-red-50 border border-red-200 rounded-xl shadow-inner mb-4 transition-all duration-300">
                    <div class="flex items-center justify-between gap-2 mb-3">
                        <div class="flex items-center gap-2">
                            <i data-lucide="shield-alert" class="w-4 h-4 text-red-600"></i>
                            <span class="text-xs font-black text-red-800 uppercase tracking-widest">Damage/Incident Payment</span>
                        </div>
                        <div class="text-right">
                            <div class="text-[10px] text-red-500 font-bold uppercase tracking-widest">Outstanding Debt</div>
                            <div id="damageDebtTotalDisplay" class="text-sm font-black text-red-700">₱0.00</div>
                        </div>
                    </div>
                    <div class="relative mb-2">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                            <span class="text-red-600 font-black">₱</span>
                        </div>
                        <input type="number" name="damage_payment" id="damage_payment" step="0.01" min="0"
                               oninput="updateDamagePaymentInfo()"
                               class="w-full pl-8 px-3 py-2.5 border-2 border-red-200 rounded-xl focus:ring-2 focus:ring-red-500 focus:border-red-500 font-black text-red-800 shadow-sm text-base"
                               placeholder="0.00">
                    </div>
                    <div class="flex justify-between items-center px-1">
                        <p class="text-[10px] text-red-600 font-bold italic leading-tight">Enter amount paid toward damage debt today.</p>
                        <div class="text-right shrink-0 ml-3">
                            <div class="text-[10px] text-red-500 font-bold uppercase tracking-widest">Remaining After Payment</div>
                            <div id="damageRemainingDisplay" class="text-sm font-black text-red-700">₱0.00</div>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-1.5">Notes</label>
                    <textarea name="notes" id="notes" rows="2" maxlength="250"
                              class="w-full px-3 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 text-sm font-medium shadow-sm"
                              placeholder="Optional remarks..."
                              oninput="let original = this.value; this.value = this.value.replace(/[^a-zA-Z0-9\s.,'-]/g, '').slice(0, 250); if(original !== this.value && this.value.length === 250) { document.getElementById('boundary-notes-notif').classList.remove('hidden'); } else { document.getElementById('boundary-notes-notif').classList.add('hidden'); }"></textarea>
                    <p id="boundary-notes-notif" class="text-xs text-red-500 hidden font-semibold mt-1">Notes limit reached (250 chars) or invalid character removed.</p>
                </div>

                {{-- Exception Controls Redesign --}}
                <div class="border border-gray-200 rounded-xl overflow-hidden bg-white shadow-sm">
                    <div class="bg-gray-50 px-4 py-3 border-b border-gray-200 flex items-center gap-2">
                        <i data-lucide="shield-alert" class="w-4 h-4 text-gray-500"></i>
                        <span class="text-[11px] font-black text-gray-600 uppercase tracking-widest">Exception Controls</span>
                    </div>
                    <div class="p-1">
                        <label class="flex items-start gap-3 cursor-pointer p-3 rounded-lg hover:bg-orange-50 transition-colors group">
                            <input type="checkbox" name="past_cutoff" id="past_cutoff" value="1" class="rounded border-gray-300 text-orange-600 focus:ring-orange-500 mt-0.5">
                            <div class="flex flex-col">
                                <span class="text-sm font-black text-gray-800 group-hover:text-orange-700 leading-tight mb-0.5 transition-colors">Late Remittance Enforcement</span>
                                <span class="text-xs text-gray-500 font-medium leading-snug">Boundary submitted after the 10:00 AM deadline. Voids incentives.</span>
                            </div>
                        </label>

                        <div class="h-px bg-gray-100 mx-3"></div>

                        <label class="flex items-start gap-3 cursor-pointer p-3 rounded-lg hover:bg-red-50 transition-colors group">
                            <input type="checkbox" name="is_absent" id="is_absent" value="1" class="rounded border-gray-300 text-red-600 focus:ring-red-500 mt-0.5">
                            <div class="flex flex-col">
                                <span class="text-sm font-black text-gray-800 group-hover:text-red-700 leading-tight mb-0.5 transition-colors">Absenteeism Validation (No Show)</span>
                                <span class="text-xs text-gray-500 font-medium leading-snug">Driver failed to report. Voids incentive and initiates a violation.</span>
                            </div>
                        </label>

                        <div class="h-px bg-gray-100 mx-3"></div>

                        <label class="flex items-start gap-3 cursor-pointer p-3 rounded-lg hover:bg-yellow-50 transition-colors group">
                            <input type="checkbox" name="needs_maintenance_half" id="needsMaintenanceHalfCheck" value="1" class="rounded border-gray-300 text-yellow-600 focus:ring-yellow-500 needs-maintenance-opt mt-0.5">
                            <div class="flex flex-col">
                                <span class="text-sm font-black text-gray-800 group-hover:text-yellow-700 leading-tight mb-0.5 transition-colors">Operational Breakdown (Prorated)</span>
                                <span class="text-xs text-gray-500 font-medium leading-snug">Mechanical failure during transit. Applies prorated boundary calculation.</span>
                            </div>
                        </label>

                        <div class="h-px bg-gray-100 mx-3"></div>

                        <label class="flex items-start gap-3 cursor-pointer p-3 rounded-lg hover:bg-orange-50 transition-colors group">
                            <input type="checkbox" name="needs_maintenance_zero" id="needsMaintenanceZeroCheck" value="1" class="rounded border-gray-300 text-orange-600 focus:ring-orange-500 needs-maintenance-opt mt-0.5">
                            <div class="flex flex-col">
                                <span class="text-sm font-black text-gray-800 group-hover:text-orange-700 leading-tight mb-0.5 transition-colors">Early Shift Maintenance Failure</span>
                                <span class="text-xs text-gray-500 font-medium leading-snug">Vehicle failure within 2 hours of deployment. Boundary is waived.</span>
                            </div>
                        </label>

                        <div class="h-px bg-gray-100 mx-3"></div>

                        <label class="flex items-start gap-3 cursor-pointer p-3 rounded-lg hover:bg-red-50 transition-colors group">
                            <input type="checkbox" name="vehicle_damaged" value="1" class="rounded border-gray-300 text-red-600 focus:ring-red-500 mt-0.5">
                            <div class="flex flex-col">
                                <span class="text-sm font-black text-gray-800 group-hover:text-red-700 leading-tight mb-0.5 transition-colors">Physical Asset Damage</span>
                                <span class="text-xs text-gray-500 font-medium leading-snug">Damage identified during turnover. Voids incentives and initiates report.</span>
                            </div>
                        </label>

                        <div class="h-px bg-gray-100 mx-3"></div>

                        <label class="flex items-start gap-3 cursor-pointer p-3 rounded-lg hover:bg-red-50 transition-colors group">
                            <input type="checkbox" name="low_fuel" value="1" class="rounded border-gray-300 text-red-600 focus:ring-red-500 mt-0.5">
                            <div class="flex flex-col">
                                <span class="text-sm font-black text-gray-800 group-hover:text-red-700 leading-tight mb-0.5 transition-colors">Fuel Replenishment Failure</span>
                                <span class="text-xs text-gray-500 font-medium leading-snug">Unit returned with insufficient fuel. Voids incentives.</span>
                            </div>
                        </label>

                        <!-- Calculation Transparency Box -->
                        <div id="breakdownComputationDraft" class="hidden mt-1 mb-2 mx-3 p-3 bg-blue-50 border border-blue-200 rounded-xl shadow-sm">
                            <div class="flex items-center gap-2 mb-2">
                                <i data-lucide="calculator" class="w-4 h-4 text-blue-600"></i>
                                <span class="text-xs font-black text-blue-800 uppercase tracking-wider">Breakdown Computation</span>
                            </div>
                            <div id="breakdownMathDisplay" class="text-xs text-blue-900 font-bold leading-relaxed whitespace-pre-line">
                                <!-- Injected calculation here -->
                            </div>
                            <input type="hidden" id="calculatedHours" name="hours_driven">
                        </div>
                    </div>
                </div>
            </div>
            
            {{-- Footer --}}
            <div class="bg-gray-50 px-6 py-4 border-t border-gray-200 flex justify-end gap-3 shrink-0">
                <button type="button" onclick="closeModal()" class="px-5 py-2.5 text-sm font-bold text-gray-600 bg-white border border-gray-300 rounded-xl hover:bg-gray-50 focus:ring-4 focus:ring-gray-100 transition-all shadow-sm">
                    Cancel
                </button>
                <button type="submit" class="px-6 py-2.5 text-sm font-black text-white bg-yellow-500 rounded-xl hover:bg-yellow-400 focus:ring-4 focus:ring-yellow-100 transition-all shadow-sm">
                    Save Record
                </button>
            </div>
        </form>
    </div>
</div>

{{-- View Boundary Info Modal --}}
<div id="viewBoundaryModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-60 backdrop-blur-sm transition-all">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-xl overflow-hidden max-h-[90vh] flex flex-col">
        {{-- Header (Modern Dark Theme) --}}
        <div class="bg-slate-800 p-5 shrink-0">
            <div class="flex justify-between items-start">
                <div class="flex items-center gap-3">
                    <div class="p-2.5 bg-white/10 rounded-xl">
                        <i data-lucide="banknote" class="w-6 h-6 text-yellow-500"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <span id="vb_statusBadge" class="px-2 py-0.5 bg-white/10 rounded text-[9px] font-black uppercase tracking-widest text-slate-300 border border-white/10"></span>
                            <span id="vb_incentiveBadge" class="px-2 py-0.5 rounded text-[9px] font-black uppercase tracking-widest text-white"></span>
                        </div>
                        <h3 id="vb_plate" class="text-2xl font-black text-white tracking-tighter uppercase leading-tight"></h3>
                        <p id="vb_driver" class="text-xs font-bold text-slate-400 uppercase tracking-wide"></p>
                    </div>
                </div>
                <button onclick="closeViewBoundary()" class="text-slate-400 hover:text-white bg-slate-700/50 hover:bg-slate-700 p-2 rounded-full transition-colors">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
        </div>

        {{-- Body --}}
        <div class="p-6 overflow-y-auto flex-1">
            <div class="grid grid-cols-2 gap-4 mb-5">
                <div class="bg-gray-50 rounded-xl p-4 border border-gray-100">
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Date</p>
                    <p id="vb_date" class="text-sm font-bold text-gray-800"></p>
                </div>
                <div class="bg-gray-50 rounded-xl p-4 border border-gray-100">
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Submitted By</p>
                    <p id="vb_creator" class="text-sm font-bold text-gray-800"></p>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4 mb-5">
                <div class="bg-yellow-50 rounded-xl p-4 border border-yellow-100">
                    <p class="text-[10px] font-black text-yellow-600 uppercase tracking-widest mb-1">Target Boundary</p>
                    <p id="vb_boundaryAmount" class="text-xl font-black text-yellow-800"></p>
                    <p id="vb_rateLabel" class="text-[10px] text-yellow-600 font-bold mt-0.5"></p>
                </div>
                <div class="bg-green-50 rounded-xl p-4 border border-green-100">
                    <p class="text-[10px] font-black text-green-600 uppercase tracking-widest mb-1">Actual Collected</p>
                    <p id="vb_actualBoundary" class="text-xl font-black text-green-800"></p>
                </div>
            </div>



            <div id="vb_differenceRow" class="mb-5 p-4 rounded-xl border hidden">
                <p class="text-[10px] font-black uppercase tracking-widest mb-1" id="vb_diffLabel"></p>
                <p id="vb_diffAmount" class="text-lg font-black"></p>
            </div>

            {{-- Exception Details (parsed from system flags) --}}
            <div id="vb_exceptionsRow" class="mb-5 hidden">
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Exception Details</p>
                <div id="vb_exceptionCards" class="space-y-2"></div>
            </div>

            {{-- Dispatcher Notes (user-typed only) --}}
            <div id="vb_notesRow" class="mb-5 hidden">
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Dispatcher Notes</p>
                <div id="vb_notes" class="p-4 bg-gray-50 rounded-xl text-sm text-gray-700 italic border-l-4 border-yellow-300 leading-relaxed"></div>
            </div>
        </div>

        {{-- Footer --}}
        <div class="bg-gray-50 px-6 py-4 border-t border-gray-200 flex justify-end gap-3 shrink-0">
            <button onclick="closeViewBoundary()"
                class="px-5 py-2.5 text-sm font-bold text-gray-600 bg-white border border-gray-300 rounded-xl hover:bg-gray-50 focus:ring-4 focus:ring-gray-100 transition-all shadow-sm uppercase tracking-tight">
                Close
            </button>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
// Boundary records keyed by ID for the view modal
const boundaryRecords = @json(collect($boundariesArray)->keyBy('id'));

function openViewBoundary(id) {
    const r = boundaryRecords[id];
    if (!r) return;

    // Header
    document.getElementById('vb_plate').innerText = r.plate_number || '—';
    document.getElementById('vb_driver').innerText = (r.driver_name || 'Unassigned') + (r.is_extra_driver ? ' • Extra Driver' : '');

    // Status badge
    const statusColors = { paid: 'bg-green-500', shortage: 'bg-red-500', excess: 'bg-blue-500' };
    const sBadge = document.getElementById('vb_statusBadge');
    sBadge.className = 'px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-widest ' + (statusColors[r.status] || 'bg-gray-500');
    sBadge.innerText = r.status || 'unknown';

    // Incentive badge
    const iBadge = document.getElementById('vb_incentiveBadge');
    if (r.has_incentive !== null && r.has_incentive !== undefined) {
        iBadge.className = 'px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-widest ' + (r.has_incentive ? 'bg-white/30' : 'bg-red-700');
        iBadge.innerText = r.has_incentive ? 'Incentive Earned' : 'No Incentive';
    } else {
        iBadge.className = 'hidden';
        iBadge.innerText = '';
    }

    // Details
    document.getElementById('vb_date').innerText = r.date || '—';
    document.getElementById('vb_creator').innerText = r.creator_name || 'System';
    document.getElementById('vb_boundaryAmount').innerText = '₱' + parseFloat(r.boundary_amount || 0).toLocaleString('en-PH', {minimumFractionDigits: 2});
    document.getElementById('vb_actualBoundary').innerText = '₱' + parseFloat(r.actual_boundary || 0).toLocaleString('en-PH', {minimumFractionDigits: 2});
    document.getElementById('vb_rateLabel').innerText = r.rate_label || '';


    // Shortage / Excess
    const diffRow = document.getElementById('vb_differenceRow');
    const shortage = parseFloat(r.shortage || 0);
    const excess = parseFloat(r.excess || 0);
    if (shortage > 0) {
        diffRow.className = 'mb-5 p-4 rounded-xl border bg-red-50 border-red-200';
        document.getElementById('vb_diffLabel').className = 'text-[10px] font-black uppercase tracking-widest mb-1 text-red-500';
        document.getElementById('vb_diffLabel').innerText = 'Shortage';
        document.getElementById('vb_diffAmount').className = 'text-lg font-black text-red-700';
        document.getElementById('vb_diffAmount').innerText = '₱' + shortage.toLocaleString('en-PH', {minimumFractionDigits: 2});
    } else if (excess > 0) {
        diffRow.className = 'mb-5 p-4 rounded-xl border bg-blue-50 border-blue-200';
        document.getElementById('vb_diffLabel').className = 'text-[10px] font-black uppercase tracking-widest mb-1 text-blue-500';
        document.getElementById('vb_diffLabel').innerText = 'Excess / Overpaid';
        document.getElementById('vb_diffAmount').className = 'text-lg font-black text-blue-700';
        document.getElementById('vb_diffAmount').innerText = '₱' + excess.toLocaleString('en-PH', {minimumFractionDigits: 2});
    } else {
        diffRow.className = 'mb-5 p-4 rounded-xl border hidden';
    }

    // Parse and display exception flags + clean user notes
    const rawNotes = (r.notes || '').trim();

    // --- Map of bracket tags → display info ---
    const exceptionDefs = [
        {
            tag: '[Automatic Violation: Vehicle Damaged]',
            label: 'Vehicle Damaged',
            sub: 'Driver incentive automatically voided.',
            icon: '⚠️',
            color: 'bg-red-50 border-red-300 text-red-800',
        },
        {
            tag: '[Unit Sent to Maintenance - Shift Schedule Paused (No Boundary)]',
            label: 'Broke Down Immediately (No Boundary)',
            sub: 'Unit broke down upon deployment. Boundary set to ₱0.00. Shift schedule paused. Pending maintenance created.',
            icon: '🔧',
            color: 'bg-orange-50 border-orange-300 text-orange-800',
        },
        {
            tag: '[Unit Sent to Maintenance - Shift Schedule Paused (Half Boundary)]',
            label: 'Broke Down During Shift (Half Boundary)',
            sub: 'Unit broke down mid-shift. Boundary halved. Shift schedule paused. Pending maintenance created.',
            icon: '🔧',
            color: 'bg-yellow-50 border-yellow-300 text-yellow-800',
        },
        {
            tag: '[Set New Boundary Schedule]',
            label: 'Boundary Schedule Reset',
            sub: 'Shift schedule was manually reset to the current time.',
            icon: '🔄',
            color: 'bg-blue-50 border-blue-300 text-blue-800',
        },
    ];

    let cleanNotes = rawNotes;
    const foundExceptions = [];

    exceptionDefs.forEach(def => {
        if (rawNotes.includes(def.tag)) {
            foundExceptions.push(def);
            cleanNotes = cleanNotes.replace(def.tag, '').trim();
        }
    });

    // Show exception cards
    const exceptRow = document.getElementById('vb_exceptionsRow');
    const exceptionCards = document.getElementById('vb_exceptionCards');
    if (foundExceptions.length > 0) {
        exceptionCards.innerHTML = foundExceptions.map(ex => `
            <div class="flex items-start gap-3 p-3 rounded-xl border ${ex.color}">
                <span class="text-lg leading-none mt-0.5">${ex.icon}</span>
                <div>
                    <p class="text-xs font-black uppercase tracking-wide leading-none mb-1">${ex.label}</p>
                    <p class="text-[11px] leading-snug opacity-80">${ex.sub}</p>
                </div>
            </div>
        `).join('');
        exceptRow.classList.remove('hidden');
    } else {
        exceptionCards.innerHTML = '';
        exceptRow.classList.add('hidden');
    }

    // Show user notes (cleaned)
    const notesRow = document.getElementById('vb_notesRow');
    const userNotes = cleanNotes.replace(/^\s+|\s+$/g, '');
    if (userNotes) {
        notesRow.classList.remove('hidden');
        document.getElementById('vb_notes').innerText = userNotes;
    } else {
        notesRow.classList.add('hidden');
    }

    document.getElementById('viewBoundaryModal').classList.remove('hidden');
    if (typeof lucide !== 'undefined') lucide.createIcons();
}

function closeViewBoundary() {
    document.getElementById('viewBoundaryModal').classList.add('hidden');
}

// Close on backdrop click
document.getElementById('viewBoundaryModal').addEventListener('click', function(e) {
    if (e.target === this) closeViewBoundary();
});

// Real-time Search Logic
let searchTimeout;
function performLiveSearch() {
    clearTimeout(searchTimeout);
    
    // Add a small loading effect to the table
    const tableWrapper = document.getElementById('boundariesTableWrapper');
    tableWrapper.style.opacity = '0.5';
    tableWrapper.style.pointerEvents = 'none';

    searchTimeout = setTimeout(async () => {
        const search = document.getElementById('liveSearchInput').value;
        const date = document.getElementById('filterDate').value;
        const status = document.getElementById('filterStatus').value;

        // Build the URL with current filters
        const params = new URLSearchParams({
            search: search,
            date: date,
            status: status
        });

        try {
            const response = await fetch(`{{ route('boundaries.index') }}?${params.toString()}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            const result = await response.json();
            
            if (result.html) {
                // Update Table
                tableWrapper.innerHTML = result.html;
                if (typeof lucide !== 'undefined') lucide.createIcons();
            }
        } catch (error) {
            console.error('Search error:', error);
        } finally {
            tableWrapper.style.opacity = '1';
            tableWrapper.style.pointerEvents = 'auto';
        }
    }, 300); // 300ms debounce
}

// Handle pagination clicks to remain on AJAX
document.addEventListener('click', function(e) {
    if (e.target.closest('.boundaries-pagination a')) {
        e.preventDefault();
        const url = e.target.closest('a').href;
        fetchPage(url);
    }
});

async function fetchPage(url) {
    const tableWrapper = document.getElementById('boundariesTableWrapper');
    tableWrapper.style.opacity = '0.5';
    
    try {
        const response = await fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        const result = await response.json();
        if (result.html) {
            tableWrapper.innerHTML = result.html;
            if (typeof lucide !== 'undefined') lucide.createIcons();
            // Scroll to top of table
            tableWrapper.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    } catch (error) {
        console.error('Pagination error:', error);
    } finally {
        tableWrapper.style.opacity = '1';
    }
}

// Auto-fill boundary amount and refresh drivers when unit is selected
document.getElementById('unitId').addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    if (!selectedOption.value) return;
    const rate = parseFloat(selectedOption.getAttribute('data-rate'));
    const codingDay = selectedOption.getAttribute('data-coding-day');
    const dateInput = document.getElementById('date');
    
    if (rate) {
        // Source of Truth: Get the smart rate based on year, plate, and date selected
        const suggestedRate = getSmartTargetRate(selectedOption.getAttribute('data-year'), selectedOption.getAttribute('data-plate'), rate, dateInput.value);
        document.getElementById('boundaryAmount').value = suggestedRate;
        document.getElementById('boundaryAmount').dataset.originalTarget = suggestedRate;
        document.getElementById('actualBoundary').value = suggestedRate;
    }
    
    // Clear driver display and reset dropdown to show new unit's drivers
    const driverDisplay = document.getElementById('driverDisplay');
    if (driverDisplay) {
        driverDisplay.value = '';
        document.getElementById('driverId').value = '';
        // Refresh driver dropdown to show suggested drivers at top
        if (typeof filterDrivers === 'function') {
            filterDrivers('');
        }
    }
});

// Auto-recalculate boundary when date changes
document.getElementById('date').addEventListener('change', function() {
    const unitSelect = document.getElementById('unitId');
    if(unitSelect.selectedIndex < 0) return;
    const selectedOption = unitSelect.options[unitSelect.selectedIndex];
    const rate = parseFloat(selectedOption.getAttribute('data-rate'));
    const codingDay = selectedOption.getAttribute('data-coding-day');
    
    if (rate) {
        // Source of Truth: Recalculate smart rate based on new date
        const suggestedRate = getSmartTargetRate(selectedOption.getAttribute('data-year'), selectedOption.getAttribute('data-plate'), rate, this.value);
        document.getElementById('boundaryAmount').value = suggestedRate;
        document.getElementById('boundaryAmount').dataset.originalTarget = suggestedRate;
        document.getElementById('actualBoundary').value = suggestedRate;
        
        // Refresh breakdown if active
        updateBreakdownComputation();
    }
});

function getSmartTargetRate(year, plate, customRate, dateStr) {
    const rules = window.boundaryRules || [];
    const yr = parseInt(year) || 0;
    const rate = parseFloat(customRate) || 0;
    const date = dateStr ? new Date(dateStr) : new Date();
    
    // Find rule for the year
    const rule = rules.find(r => yr >= r.start_year && yr <= r.end_year);
    
    // Base rate priority: Custom -> Rule -> Default
    const base = rate > 0 ? rate : (rule ? parseFloat(rule.regular_rate) : 1100);
    
    // Day of week
    const dayOfWeek = date.getDay(); // 0 = Sunday, 6 = Saturday
    const dayName = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'][dayOfWeek];
    
    // Detect coding day
    const codingDay = deriveCodingDay(plate);
    
    // 1. Coding Day Check (Overrides weekends)
    if (codingDay && dayName.toLowerCase() === codingDay.toLowerCase()) {
        if (rule && rule.coding_rate > 0) return parseFloat(rule.coding_rate).toFixed(2);
        return (base / 2).toFixed(2);
    }
    
    // 2. Weekend Check
    if (dayOfWeek === 6) { // Saturday
        const disc = rule ? parseFloat(rule.sat_discount) : 100;
        return (base - disc).toFixed(2);
    }
    if (dayOfWeek === 0) { // Sunday
        const disc = rule ? parseFloat(rule.sun_discount) : 200;
        return (base - disc).toFixed(2);
    }
    
    // 3. Regular Day
    return base.toFixed(2);
}

// Unit dropdown functionality
function initializeUnitDropdown() {
    const unitDisplay = document.getElementById('unitDisplay');
    const unitDropdown = document.getElementById('unit_dropdown');
    const unitOptions = document.querySelectorAll('.unit-option');
    
    if (unitDisplay && unitDropdown) {
        // Show dropdown on focus
        unitDisplay.addEventListener('focus', function() {
            filterUnits('');
            unitDropdown.classList.remove('hidden');
        });
        
        // Filter units on input
        unitDisplay.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            filterUnits(searchTerm);
        });
        
        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!unitDisplay.contains(e.target) && !unitDropdown.contains(e.target)) {
                unitDropdown.classList.add('hidden');
            }
        });
        
        // Handle unit selection - use mousedown to fire before document click hide
        unitOptions.forEach(option => {
            option.addEventListener('mousedown', function(e) {
                e.preventDefault(); // Prevent focus loss
                const unitId = this.getAttribute('data-id');
                const unitName = this.getAttribute('data-name');
                const unitPlate = this.getAttribute('data-plate');
                
                // Store primary and secondary driver IDs for suggestion
                const primaryId = this.getAttribute('data-primary-id');
                const secondaryId = this.getAttribute('data-secondary-id');
                const plate = this.getAttribute('data-plate');
                const year = this.getAttribute('data-year');
                const customRate = this.getAttribute('data-rate');

                document.getElementById('unitId').value = unitId;
                unitDisplay.value = unitPlate;
                unitDropdown.classList.add('hidden');

                // Reset extra driver alert when unit changes
                const alertBox = document.getElementById('extraDriverAlert');
                if (alertBox) alertBox.classList.add('hidden');
                document.getElementById('driverId').value = '';
                document.getElementById('driverDisplay').value = '';

                // Source of Truth: Get the smart rate
                const suggestedRate = getSmartTargetRate(year, plate, customRate, document.getElementById('date').value);
                const boundaryInput = document.getElementById('boundaryAmount');
                if (boundaryInput) {
                    boundaryInput.value = suggestedRate;
                    boundaryInput.dataset.originalTarget = suggestedRate;
                    
                    const needsMaintenanceHalfCheck = document.getElementById('needsMaintenanceHalfCheck');
                    const needsMaintenanceZeroCheck = document.getElementById('needsMaintenanceZeroCheck');
                    
                    if (needsMaintenanceZeroCheck && needsMaintenanceZeroCheck.checked) {
                        boundaryInput.value = '0.00';
                        document.getElementById('actualBoundary').value = '0.00';
                    } else if (needsMaintenanceHalfCheck && needsMaintenanceHalfCheck.checked) {
                        const halfLimit = (parseFloat(suggestedRate) / 2).toFixed(2);
                        boundaryInput.value = halfLimit;
                        document.getElementById('actualBoundary').value = halfLimit;
                    } else {
                        document.getElementById('actualBoundary').value = suggestedRate;
                    }
                }

                // New: Handle Swapping & Shift turn data
                updateShiftInfo(this);

                unitDisplay.setAttribute('data-primary-id', primaryId || '');
                unitDisplay.setAttribute('data-secondary-id', secondaryId || '');

                // Trigger change event
                document.getElementById('unitId').dispatchEvent(new Event('change'));
            });
        });
    }
}

function filterUnits(searchTerm) {
    const unitOptions = document.querySelectorAll('.unit-option');
    const unitDropdown = document.getElementById('unit_dropdown');
    
    let hasResults = false;
    unitOptions.forEach(option => {
        const unitName = option.getAttribute('data-name').toLowerCase();
        const unitPlate = option.getAttribute('data-plate').toLowerCase();
        const unitModel = (option.getAttribute('data-model') || '').toLowerCase();
        
        if (unitName.includes(searchTerm) || unitPlate.includes(searchTerm) || unitModel.includes(searchTerm)) {
            option.style.display = 'block';
            hasResults = true;
        } else {
            option.style.display = 'none';
        }
    });
    
    // Show/hide no results message
    let noResultsMsg = unitDropdown.querySelector('.no-results');
    if (!hasResults) {
        if (!noResultsMsg) {
            noResultsMsg = document.createElement('div');
            noResultsMsg.className = 'no-results px-2 py-1 text-xs text-gray-500 text-center';
            noResultsMsg.textContent = 'No units found';
            unitDropdown.appendChild(noResultsMsg);
        }
    } else if (noResultsMsg) {
        noResultsMsg.remove();
    }
}

// Driver dropdown functionality  
function initializeDriverDropdown() {
    const driverDisplay = document.getElementById('driverDisplay');
    const driverDropdown = document.getElementById('driver_dropdown');
    const driverOptions = document.querySelectorAll('.driver-option');
    
    if (driverDisplay && driverDropdown) {
        // Show dropdown on focus
        driverDisplay.addEventListener('focus', function() {
            filterDrivers('');
            driverDropdown.classList.remove('hidden');
        });
        
        // Filter drivers on input
        driverDisplay.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            filterDrivers(searchTerm);
        });
        
        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!driverDisplay.contains(e.target) && !driverDropdown.contains(e.target)) {
                driverDropdown.classList.add('hidden');
            }
        });
        
        // Handle driver selection
        driverOptions.forEach(option => {
            option.addEventListener('mousedown', function(e) {
                e.preventDefault();
                const driverId = this.getAttribute('data-id');
                const driverName = this.getAttribute('data-name');
                const shortage = parseFloat(this.getAttribute('data-shortage') || 0);

                document.getElementById('driverId').value = driverId;
                driverDisplay.value = driverName;
                driverDropdown.classList.add('hidden');

                // Check if extra driver (not assigned to selected unit)
                const unitIdInput = document.getElementById('unitId');
                const unitOption = document.querySelector(`.unit-option[data-id="${unitIdInput.value}"]`);
                const primaryId = unitOption ? unitOption.getAttribute('data-primary-id') : '';
                const secondaryId = unitOption ? unitOption.getAttribute('data-secondary-id') : '';
                
                const extraAlert = document.getElementById('extraDriverAlert');
                // Show extra driver alert if:
                // - a real driver is selected (not 'all')
                // - AND they are NOT the primary or secondary driver of this unit
                // - OR the unit has no assigned driver at all (any assignment is 'extra')
                const isExtra = driverId && driverId !== 'all' && (
                    !primaryId || // no assigned driver
                    (driverId !== primaryId && driverId !== secondaryId) // not a regular driver
                );
                if (isExtra) {
                    extraAlert.classList.remove('hidden');
                } else {
                    extraAlert.classList.add('hidden');
                }

                // Handle Shortage Balance Alert
                const shortageAlert = document.getElementById('shortageBalanceAlert');
                const shortageAmountSpan = document.getElementById('shortageBalanceAmount');
                const rawShortageInput = document.getElementById('rawShortageAmount');

                if (shortage > 0) {
                    shortageAlert.classList.remove('hidden');
                    shortageAmountSpan.textContent = "₱" + shortage.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
                    rawShortageInput.value = shortage;
                } else {
                    shortageAlert.classList.add('hidden');
                    rawShortageInput.value = 0;
                }

                // Toggle Damage Payment field visibility based on accident debt
                const hasAccidentDebt = this.getAttribute('data-has-accident-debt') === 'true';
                const accidentDebtAmount = parseFloat(this.getAttribute('data-accident-debt-amount') || 0);
                const damageContainer = document.getElementById('damagePaymentContainer');
                const debtLabel = document.getElementById('accidentDebtBalanceLabel');

                if (hasAccidentDebt && accidentDebtAmount > 0) {
                    damageContainer.classList.remove('hidden');
                    // Populate the outstanding debt display
                    const fmt = (n) => '₱' + n.toLocaleString('en-PH', { minimumFractionDigits: 2 });
                    const debtDisplay = document.getElementById('damageDebtTotalDisplay');
                    const remainDisplay = document.getElementById('damageRemainingDisplay');
                    if (debtDisplay) {
                        debtDisplay.textContent = fmt(accidentDebtAmount);
                        debtDisplay.dataset.rawDebt = accidentDebtAmount;
                    }
                    if (remainDisplay) remainDisplay.textContent = fmt(accidentDebtAmount);
                    if (debtLabel) debtLabel.textContent = fmt(accidentDebtAmount);
                } else {
                    damageContainer.classList.add('hidden');
                    document.getElementById('damage_payment').value = 0;
                }

                document.getElementById('driverId').dispatchEvent(new Event('change'));

                // Refresh shift status notice (extra driver vs expected driver)
                if (typeof refreshShiftStatusForDriver === 'function') {
                    refreshShiftStatusForDriver(this.getAttribute('data-id'));
                }
            });
        });
    }
}

function payFullBalance() {
    const dailyTarget = parseFloat(document.getElementById('boundaryAmount').value || 0);
    const pastShortage = parseFloat(document.getElementById('rawShortageAmount').value || 0);
    const actualCollectedInput = document.getElementById('actualBoundary');

    const totalToPay = dailyTarget + pastShortage;
    actualCollectedInput.value = totalToPay.toFixed(2);
    
    // Visual feedback/confirmation
    actualCollectedInput.classList.add('ring-4', 'ring-green-400');
    setTimeout(() => actualCollectedInput.classList.remove('ring-4', 'ring-green-400'), 1000);
}

// Actual Collected must NOT exceed the target boundary
function validateActualCollected() {
    const input = document.getElementById('actualBoundary');
    const target = parseFloat(document.getElementById('boundaryAmount').value || 0);
    let val = parseFloat(input.value || 0);

    // Strict Decimal Cap: Prevent more than 2 decimal places
    if (input.value.includes('.')) {
        const parts = input.value.split('.');
        if (parts[1].length > 2) {
            input.value = parts[0] + '.' + parts[1].substring(0, 2);
            val = parseFloat(input.value);
        }
    }

    // Amount Cap: Cannot exceed target
    if (val > target) {
        input.value = target.toFixed(2);
        input.classList.add('ring-4', 'ring-red-400', 'border-red-500');
        setTimeout(() => input.classList.remove('ring-4', 'ring-red-400', 'border-red-500'), 800);
    }
}

// Update the Damage Payment remaining balance display in real-time
function updateDamagePaymentInfo() {
    const input = document.getElementById('damage_payment');
    const debtTotal = parseFloat(document.getElementById('damageDebtTotalDisplay').dataset.rawDebt || 0);
    let val = parseFloat(input.value || 0);

    // Strict Decimal Cap: Prevent more than 2 decimal places
    if (input.value.includes('.')) {
        const parts = input.value.split('.');
        if (parts[1].length > 2) {
            input.value = parts[0] + '.' + parts[1].substring(0, 2);
            val = parseFloat(input.value);
        }
    }

    // Amount Cap: Cannot exceed total debt
    if (val > debtTotal && debtTotal > 0) {
        input.value = debtTotal.toFixed(2);
        val = debtTotal;
        input.classList.add('ring-4', 'ring-red-400', 'border-red-500');
        setTimeout(() => input.classList.remove('ring-4', 'ring-red-400', 'border-red-500'), 800);
    }

    const remaining = Math.max(0, debtTotal - val);
    const fmt = (n) => '₱' + n.toLocaleString('en-PH', { minimumFractionDigits: 2 });
    document.getElementById('damageRemainingDisplay').textContent = fmt(remaining);
}

// Prevent invalid number characters (e, +, -, .) - already handling . via oninput
document.addEventListener('DOMContentLoaded', function() {
    const numericInputs = ['actualBoundary', 'damage_payment', 'boundaryAmount'];
    numericInputs.forEach(id => {
        const el = document.getElementById(id);
        if (el) {
            // Force 2-decimal formatting when leaving the field
            el.addEventListener('blur', function() {
                if (this.value && !isNaN(this.value)) {
                    this.value = parseFloat(this.value).toFixed(2);
                }
            });

            el.addEventListener('keydown', function(e) {
                if (['e', 'E', '+', '-'].includes(e.key)) {
                    e.preventDefault();
                }
            });
        }
    });
});

function filterDrivers(searchTerm) {
    const driverOptions = document.querySelectorAll('.driver-option');
    const driverDropdown = document.getElementById('driver_dropdown');
    const allDriversList = document.querySelector('.all-drivers-list');
    
    if (allDriversList) {
        allDriversList.style.display = 'flex';
        allDriversList.style.flexDirection = 'column';
    }
    
    const unitDisplay = document.getElementById('unitDisplay');
    const primaryId = unitDisplay.getAttribute('data-primary-id');
    const secondaryId = unitDisplay.getAttribute('data-secondary-id');
    
    let hasResults = false;
    driverOptions.forEach(option => {
        const driverName = option.getAttribute('data-name').toLowerCase();
        const driverUnit = (option.getAttribute('data-unit') || '').toLowerCase();
        const driverPlate = (option.getAttribute('data-plate') || '').toLowerCase();
        const driverIdAttr = option.getAttribute('data-id');
        
        if (driverName.includes(searchTerm) || driverUnit.includes(searchTerm) || driverPlate.includes(searchTerm)) {
            option.style.display = 'block';
            
            // Don't modify "All Drivers" styling
            if (option.getAttribute('data-id') === 'all') {
                hasResults = true;
                return;
            }
            
            // Match via primary or secondary driver ID for strict suggestion
            const isSuggested = driverIdAttr && (driverIdAttr == primaryId || driverIdAttr == secondaryId);

            if (isSuggested) {
                option.style.order = '-1';
                option.classList.remove('hover:bg-yellow-50');
                option.classList.add('bg-green-50', 'border-l-4', 'border-green-500', 'hover:bg-green-100');
                
                let nameDiv = option.querySelector('.font-medium');
                if (nameDiv && !option.querySelector('.suggested-badge')) {
                    nameDiv.innerHTML += ' <span class="suggested-badge ml-2 px-1.5 py-0.5 bg-green-500 text-white text-[10px] rounded-full shadow-sm font-bold">Recommended</span>';
                }
            } else {
                option.style.order = '0';
                option.classList.remove('bg-green-50', 'border-l-4', 'border-green-500', 'hover:bg-green-100');
                option.classList.add('hover:bg-yellow-50');
                let badge = option.querySelector('.suggested-badge');
                if (badge) badge.remove();
            }
            hasResults = true;
        } else {
            option.style.display = 'none';
        }
    });
    
    // Show/hide no results message
    let noResultsMsg = driverDropdown.querySelector('.no-results');
    if (!hasResults) {
        if (!noResultsMsg) {
            noResultsMsg = document.createElement('div');
            noResultsMsg.className = 'no-results px-2 py-1 text-xs text-gray-500 text-center';
            noResultsMsg.textContent = 'No drivers found';
            driverDropdown.appendChild(noResultsMsg);
        }
    } else if (noResultsMsg) {
        noResultsMsg.remove();
    }
}

// Modal functions
function addBoundary() {
    document.getElementById('modalTitle').textContent = 'Add Boundary Record';
    document.getElementById('formAction').value = 'add_boundary';
    document.getElementById('boundaryForm').reset();

    const unitDisplay = document.getElementById('unitDisplay');
    if (unitDisplay) {
        unitDisplay.value = '';
        unitDisplay.removeAttribute('data-primary-id');
        unitDisplay.removeAttribute('data-secondary-id');
    }

    // Hide alerts on fresh open
    const extraAlert = document.getElementById('extraDriverAlert');
    const shortageAlert = document.getElementById('shortageBalanceAlert');
    const damageContainer = document.getElementById('damagePaymentContainer');
    if (extraAlert) extraAlert.classList.add('hidden');
    if (shortageAlert) shortageAlert.classList.add('hidden');
    if (damageContainer) damageContainer.classList.add('hidden');

    document.getElementById('date').value = new Date().toLocaleDateString('en-CA');

    // Auto-check the Past 10:00 AM Cut-off if current time is >= 10:00 AM
    const pastCutoffCheckbox = document.getElementById('past_cutoff');
    if (pastCutoffCheckbox) {
        pastCutoffCheckbox.checked = new Date().getHours() >= 10;
    }

    document.getElementById('boundaryModal').classList.remove('is-editing');
    document.getElementById('breakdownComputationDraft').classList.add('hidden');
    document.getElementById('boundaryModal').classList.remove('hidden');
    lucide.createIcons();
}

function editBoundary(id) {
    const boundaryData = @json($boundariesArray);
    
    // Use Object.values to handle both Array and Associative Object structures safely
    const boundary = Object.values(boundaryData).find(b => b.id == id);
    
    if (boundary) {
        document.getElementById('modalTitle').textContent = 'Edit Boundary Record';
        document.getElementById('formAction').value = 'update_boundary';
        document.getElementById('boundaryId').value = boundary.id;
        document.getElementById('unitId').value = boundary.unit_id;
        document.getElementById('driverId').value = boundary.driver_id;
        document.getElementById('date').value = boundary.date;
        document.getElementById('boundaryAmount').value = boundary.boundary_amount;
        document.getElementById('actualBoundary').value = boundary.actual_boundary || '';
        const savedDamagePayment = parseFloat(boundary.damage_payment || 0);
        document.getElementById('damage_payment').value = savedDamagePayment > 0 ? savedDamagePayment.toFixed(2) : '';
        document.getElementById('notes').value = boundary.notes || '';
        
        // Handle Damage Payment Visibility for Edit
        const damageContainer = document.getElementById('damagePaymentContainer');
        const driverOption = document.querySelector(`.driver-option[data-id="${boundary.driver_id}"]`);
        const hasAccidentDebt = driverOption && driverOption.getAttribute('data-has-accident-debt') === 'true';
        const accidentDebtAmount = driverOption ? parseFloat(driverOption.getAttribute('data-accident-debt-amount') || 0) : 0;
        
        if (hasAccidentDebt || savedDamagePayment > 0) {
            if (damageContainer) damageContainer.classList.remove('hidden');
            // Populate Outstanding Debt display
            const fmt = (n) => '₱' + n.toLocaleString('en-PH', { minimumFractionDigits: 2 });
            const debtDisplay = document.getElementById('damageDebtTotalDisplay');
            const remainDisplay = document.getElementById('damageRemainingDisplay');
            const totalDebt = accidentDebtAmount > 0 ? accidentDebtAmount : savedDamagePayment;
            if (debtDisplay) {
                debtDisplay.textContent = fmt(totalDebt);
                debtDisplay.dataset.rawDebt = totalDebt;
            }
            const remaining = Math.max(0, totalDebt - savedDamagePayment);
            if (remainDisplay) remainDisplay.textContent = fmt(remaining);
        } else {
            if (damageContainer) damageContainer.classList.add('hidden');
        }
        
        // Hide alerts on fresh open
        const extraAlert = document.getElementById('extraDriverAlert');
        const shortageAlert = document.getElementById('shortageBalanceAlert');
        if (extraAlert) extraAlert.classList.add('hidden');
        if (shortageAlert) shortageAlert.classList.add('hidden');

        // Set Unit Display (guaranteed to fill required field even if inactive)
        const unitDisplay = document.getElementById('unitDisplay');
        unitDisplay.value = boundary.plate_number || 'Unknown Unit';
        
        const unitOption = document.querySelector(`.unit-option[data-id="${boundary.unit_id}"]`);
        if (unitOption) {
            const pId = unitOption.getAttribute('data-primary-driver');
            const sId = unitOption.getAttribute('data-secondary-driver');
            unitDisplay.setAttribute('data-primary-id', pId || '');
            unitDisplay.setAttribute('data-secondary-id', sId || '');
            
            // Critical for computation box
            const swappedAt = unitOption.getAttribute('data-swapped-at');
            document.getElementById('boundaryModal').setAttribute('data-current-swap', swappedAt || '');

            // Set original target from unit rate for calculations
            const unitRate = unitOption.getAttribute('data-rate');
            document.getElementById('boundaryAmount').dataset.originalTarget = unitRate;
        }

        // Set Driver Display (guaranteed to fill required field even if inactive)
        const driverDisplay = document.getElementById('driverDisplay');
        driverDisplay.value = boundary.driver_name || 'Unknown Driver';

        // Keep target boundary amount readonly per user policy
        document.getElementById('boundaryAmount').readOnly = true;

        // Parse existing exception rules from notes
        const notesLc = (boundary.notes || '').toLowerCase();
        
        // Uncheck all first
        const pastCutoffEl = document.getElementById('past_cutoff');
        const damagedEl = document.querySelector('input[name="vehicle_damaged"]');
        const halfMaintEl = document.getElementById('needsMaintenanceHalfCheck');
        const zeroMaintEl = document.getElementById('needsMaintenanceZeroCheck');
        
        if (pastCutoffEl) pastCutoffEl.checked = false;
        if (damagedEl) damagedEl.checked = false;
        if (halfMaintEl) halfMaintEl.checked = false;
        if (zeroMaintEl) zeroMaintEl.checked = false;

        // Re-check based on existing data
        // Absent check removed per user request
        if (notesLc.includes('past 10:00 am') && pastCutoffEl) {
            pastCutoffEl.checked = true;
        }
        if (notesLc.includes('vehicle damaged') && damagedEl) {
            damagedEl.checked = true;
        }
        if ((notesLc.includes('half boundary') || notesLc.includes('broke down during') || notesLc.includes('hrs x')) && halfMaintEl) {
            halfMaintEl.checked = true;
        }
        if ((notesLc.includes('no boundary') || notesLc.includes('immediately')) && notesLc.includes('maintenance') && zeroMaintEl) {
            zeroMaintEl.checked = true;
        }
        
        // Refresh breakdown calculation display
        updateBreakdownComputation();

        document.getElementById('boundaryModal').classList.add('is-editing');
        document.getElementById('boundaryModal').classList.remove('hidden');
        lucide.createIcons();
    } else {
        alert('Boundary record not found');
    }
}

function closeModal() {
    document.getElementById('boundaryModal').classList.add('hidden');
}

window.boundaryRules = @json($boundary_rules ?? []);

// Legacy calculateAutomatedRate function replaced by getSmartTargetRate for accuracy and date-sync.

function deriveCodingDay(plate) {
    if (!plate) return null;
    const cleanPlate = plate.toString().trim();
    let lastChar = cleanPlate.slice(-1);
    
    if (isNaN(parseInt(lastChar))) {
        // Find last numeric char
        const matches = cleanPlate.match(/\d/g);
        if (matches) lastChar = matches[matches.length - 1];
        else return null;
    }
    
    const lastDigit = parseInt(lastChar);
    const mapping = {
        'Monday': [1, 2],
        'Tuesday': [3, 4],
        'Wednesday': [5, 6],
        'Thursday': [7, 8],
        'Friday': [9, 0]
    };
    
    for (const [day, digits] of Object.entries(mapping)) {
        if (digits.includes(lastDigit)) return day;
    }
    return null;
}

function updateShiftInfo(unitElement) {
    const expectedId    = unitElement.getAttribute('data-expected-id') || '0';
    const deadline      = unitElement.getAttribute('data-deadline');

    const shiftInfoGroup  = document.getElementById('shiftInfoGroup');
    const mainLabel       = document.getElementById('shiftMainLabel');
    const shiftTimer      = document.getElementById('shiftTimer');
    const badgeContainer  = document.getElementById('incentiveStatusBadge');
    const shiftIconWrap   = document.getElementById('shiftIconWrap');
    const shiftIcon       = document.getElementById('shiftIcon');
    const extraNotice     = document.getElementById('shiftExtraNotice');
    const extraText       = document.getElementById('shiftExtraText');
    const hasAbsentToday  = unitElement.getAttribute('data-has-absent-today') === 'true';

    // Store expected ID on the modal so refreshShiftStatusForDriver can read it later
    document.getElementById('boundaryModal').setAttribute('data-expected-id', expectedId);

    // Lookup expected driver name
    const driverOption  = document.querySelector(`.driver-option[data-id="${expectedId}"]`);
    const expectedName  = (driverOption && expectedId !== '0') ? driverOption.getAttribute('data-name') : null;

    // Auto-select expected driver (pre-fill form)
    if (expectedId && expectedId !== '0') {
        document.getElementById('driverId').value   = expectedId;
        document.getElementById('driverDisplay').value = expectedName;
        const shortage = parseFloat(driverOption ? driverOption.getAttribute('data-shortage') : 0);
        if (typeof triggerDriverAlerts === 'function') triggerDriverAlerts(expectedId, shortage);
    }

    // --- NEW: Smart Integration for Existing Absences ---
    if (hasAbsentToday) {
        extraNotice.classList.remove('hidden');
        extraText.innerHTML = `<strong>SMART ALERT:</strong> ${expectedName || 'The expected driver'} was already recorded as <strong>ABSENT</strong> in the Incident Report today. <span class="block mt-1 opacity-75">Absenteeism Validation has been auto-selected.</span>`;
        
        // Auto-check the is_absent checkbox
        const absentCheckbox = document.getElementById('is_absent');
        if (absentCheckbox) {
            absentCheckbox.checked = true;
            // Visual feedback
            absentCheckbox.closest('label').classList.add('bg-red-50');
        }
    } else {
        // Reset if no absent found (unless it's an extra driver case which is handled elsewhere)
        if (!extraNotice.classList.contains('stay-visible')) {
            // extraNotice.classList.add('hidden');
        }
    }

    // --- Build shift timing info ---
    if (deadline) {
        const deadlineDate  = new Date(deadline);
        const formatOptions = { hour: 'numeric', minute: 'numeric', hour12: true };
        const absoluteStr   = deadlineDate.toLocaleTimeString('en-US', formatOptions);
        const now           = new Date();
        const diffMs        = deadlineDate - now;
        const isPast        = diffMs < 0;
        const absDiff       = Math.abs(diffMs);
        const diffHours     = Math.floor(absDiff / (1000 * 60 * 60));
        const diffMins      = Math.floor((absDiff % (1000 * 60 * 60)) / (1000 * 60));

        if (isPast) {
            const diffDays = Math.floor(diffHours / 24);
            const remHours = diffHours % 24;

            let overdueTxt, borderColor, bgColor, iconColor, iconBg, iconName, badgeHtml;

            if (diffHours < 24) {
                // < 1 day overdue — amber warning
                overdueTxt   = `Overdue by ${diffHours}h ${diffMins}m`;
                borderColor  = 'border-amber-200';  bgColor = 'bg-amber-50/30';
                iconBg       = 'bg-amber-100';       iconColor = 'text-amber-600';
                iconName     = 'clock-4';
                badgeHtml    = '<span class="px-1.5 py-0.5 bg-green-100 text-green-700 text-[9px] font-bold rounded-full border border-green-300 uppercase tracking-tighter shadow-sm">Incentive Eligible</span>';
            } else if (diffHours < 48) {
                // 1–2 days — orange alert
                overdueTxt   = `⚠️ Missing for 1 day ${remHours}h — Last boundary was yesterday`;
                borderColor  = 'border-orange-300'; bgColor = 'bg-orange-50';
                iconBg       = 'bg-orange-100';      iconColor = 'text-orange-600';
                iconName     = 'alert-triangle';
                badgeHtml    = '<span class="px-1.5 py-0.5 bg-orange-100 text-orange-700 text-[9px] font-bold rounded-full border border-orange-300 uppercase tracking-tighter shadow-sm animate-pulse">CHECK UNIT</span>';
            } else {
                // 2+ days — red danger
                overdueTxt   = `🚨 NO BOUNDARY FOR ${diffDays} DAYS ${remHours}h — Possible Missing/Runaway Unit!`;
                borderColor  = 'border-red-400';    bgColor = 'bg-red-50';
                iconBg       = 'bg-red-100';         iconColor = 'text-red-600';
                iconName     = 'siren';
                badgeHtml    = '<span class="px-1.5 py-0.5 bg-red-100 text-red-700 text-[9px] font-bold rounded-full border border-red-400 uppercase tracking-tighter shadow-sm animate-pulse">MISSING UNIT</span>';
            }

            mainLabel.textContent = expectedName
                ? `${expectedName} — Shift Deadline Passed`
                : 'Last Driver — Shift Deadline Passed';
            shiftTimer.innerHTML  = `<span class="${iconColor} font-bold">${overdueTxt}</span><br><span class="text-gray-400 text-[9px]">Deadline was ${absoluteStr}</span>`;
            shiftIconWrap.className = `p-1.5 rounded-lg mt-0.5 shrink-0 ${iconBg}`;
            shiftIcon.className     = `w-3.5 h-3.5 ${iconColor}`;
            shiftIcon.setAttribute('data-lucide', iconName);
            shiftInfoGroup.className = shiftInfoGroup.className.replace(/border-\S+/g, '').trim();
            shiftInfoGroup.classList.add(borderColor, bgColor);
            badgeContainer.innerHTML = badgeHtml;
        } else {
            // Shift still active
            mainLabel.textContent = expectedName ? `${expectedName} — On Shift` : 'Driver On Shift';
            shiftTimer.innerHTML  = `<span class="text-green-600 font-bold">${diffHours}h ${diffMins}m remaining</span> &nbsp;·&nbsp; Expected Return: ${absoluteStr}`;
            shiftIconWrap.className = 'p-1.5 rounded-lg mt-0.5 shrink-0 bg-green-100';
            shiftIcon.className     = 'w-3.5 h-3.5 text-green-600';
            shiftIcon.setAttribute('data-lucide', 'user-check');
            shiftInfoGroup.className = shiftInfoGroup.className.replace(/border-\S+/g, '').trim();
            shiftInfoGroup.classList.add('border-green-200', 'bg-green-50/20');
            badgeContainer.innerHTML = '<span class="px-1.5 py-0.5 bg-green-100 text-green-700 text-[9px] font-bold rounded-full border border-green-300 uppercase tracking-tighter shadow-sm">Incentive Eligible</span>';
        }
    } else {
        // No deadline set — first time or schedule cleared
        mainLabel.textContent = expectedName ? `${expectedName} — New Shift` : 'No Schedule Yet';
        shiftTimer.innerHTML  = '<span class="text-gray-400 italic">Shift schedule not yet set for this unit.</span>';
        shiftIconWrap.className = 'p-1.5 rounded-lg mt-0.5 shrink-0 bg-blue-100';
        shiftIcon.className     = 'w-3.5 h-3.5 text-blue-600';
        shiftIcon.setAttribute('data-lucide', 'calendar-plus');
        shiftInfoGroup.className = shiftInfoGroup.className.replace(/border-\S+/g, '').trim();
        shiftInfoGroup.classList.add('border-blue-200', 'bg-blue-50/20');
        badgeContainer.innerHTML = '<span class="px-1.5 py-0.5 bg-blue-100 text-blue-700 text-[9px] font-bold rounded-full border border-blue-300 uppercase tracking-tighter">New Pattern</span>';
    }

    // Hide extra notice initially — will be shown by refreshShiftStatusForDriver if needed
    if (extraNotice) extraNotice.classList.add('hidden');

    const swappedAt = unitElement.getAttribute('data-swapped-at');
    document.getElementById('boundaryModal').setAttribute('data-current-swap', swappedAt || '');

    shiftInfoGroup.classList.remove('hidden');
    if (typeof lucide !== 'undefined') lucide.createIcons();
    updateBreakdownComputation();
}

// Called whenever the dispatcher changes the driver selection
function refreshShiftStatusForDriver(selectedDriverId) {
    const modal       = document.getElementById('boundaryModal');
    const expectedId  = modal.getAttribute('data-expected-id') || '0';
    const extraNotice = document.getElementById('shiftExtraNotice');
    const extraText   = document.getElementById('shiftExtraText');

    if (!extraNotice || !extraText) return;

    const shiftInfoGroup = document.getElementById('shiftInfoGroup');
    if (shiftInfoGroup.classList.contains('hidden')) return; // Nothing to update

    const isExtra = selectedDriverId && selectedDriverId !== 'all' && expectedId !== '0' && String(selectedDriverId) !== String(expectedId);

    if (isExtra) {
        const expectedOption = document.querySelector(`.driver-option[data-id="${expectedId}"]`);
        const expectedName   = expectedOption ? expectedOption.getAttribute('data-name') : 'the expected driver';
        extraText.textContent = `${expectedName} hasn't submitted their boundary yet. This record will be filed under the selected driver as Extra Driver.`;
        extraNotice.classList.remove('hidden');
    } else {
        extraNotice.classList.add('hidden');
    }
}

function updateBreakdownComputation() {
    const modal = document.getElementById('boundaryModal');
    const swappedAt = modal.getAttribute('data-current-swap');
    const dailyRate = parseFloat(document.getElementById('boundaryAmount').dataset.originalTarget || 0);
    
    const amtInput = document.getElementById('boundaryAmount');
    const actInput = document.getElementById('actualBoundary');
    const mathDisplay = document.getElementById('breakdownMathDisplay');
    const compBox = document.getElementById('breakdownComputationDraft');
    
    const halfCheck = document.getElementById('needsMaintenanceHalfCheck');
    const zeroCheck = document.getElementById('needsMaintenanceZeroCheck');

    if (!halfCheck || !zeroCheck || (!halfCheck.checked && !zeroCheck.checked)) {
        if (compBox) compBox.classList.add('hidden');
        
        // ROBUST RESET: Always return to original suggested rate if unselected
        if (amtInput && amtInput.dataset.originalTarget) {
             const original = parseFloat(amtInput.dataset.originalTarget).toFixed(2);
             if (amtInput.value !== original) {
                 amtInput.value = original;
                 if (actInput) actInput.value = original;
             }
        }
        return;
    }

    let swapDate;
    if (swappedAt) {
        swapDate = new Date(swappedAt);
    } else {
        // FALLBACK: If no handover timestamp exists, assume it started at 10:00 AM of the record date
        // or 10:00 AM yesterday if it's currently past 10:00 AM today.
        const recordDateVal = document.getElementById('date').value;
        swapDate = new Date(recordDateVal + 'T10:00:00');
        // If the resulting "start" is in the future relative to now, assume it started yesterday
        if (swapDate > new Date()) {
            swapDate.setDate(swapDate.getDate() - 1);
        }
    }

    const now = new Date();
    const diffMs = now - swapDate;
    const rawHours = diffMs / (1000 * 60 * 60);
    const hoursDriven = Math.max(0, rawHours);
    
    // SMART CAP: A shift is max 24 hours. Anything beyond is a backlog, but for a 
    // single daily boundary record, we cap the prorated charge to one full day.
    const cappedHours = Math.min(24, hoursDriven);
    const hoursDisplay = hoursDriven > 24 ? `24.00 (Capped from ${hoursDriven.toFixed(2)})` : hoursDriven.toFixed(2);
    
    const hourlyRate = (dailyRate / 24);
    let prorated = hourlyRate * cappedHours;
    
    // FINAL SAFETY CAP: Never exceed the original daily target
    if (prorated > dailyRate) prorated = dailyRate;
    const proratedStr = prorated.toFixed(2);

    compBox.classList.remove('hidden');
    document.getElementById('calculatedHours').value = cappedHours.toFixed(2);

    if (zeroCheck.checked) {
        if (cappedHours <= 2) {
            mathDisplay.innerHTML = `<span class="flex justify-between"><span>Driven:</span> <span class="font-bold text-green-700">${hoursDisplay} hrs (<= 2hr)</span></span>
                                     <span class="flex justify-between border-t border-blue-100 mt-1 pt-1"><span>Target:</span> <span class="font-bold text-green-700">₱0.00 (Free Boundary)</span></span>`;
            amtInput.value = '0.00';
            actInput.value = '0.00';
        } else {
            mathDisplay.innerHTML = `<span class="flex justify-between"><span>Driven:</span> <span class="font-bold text-red-600">${hoursDisplay} hrs (> 2hr)</span></span>
                                     <span class="flex justify-between border-t border-blue-100 mt-1 pt-1"><span>Target (Hourly):</span> <span class="font-bold text-red-700">₱${parseFloat(proratedStr).toLocaleString()}</span></span>`;
            amtInput.value = proratedStr;
            actInput.value = proratedStr;
        }
    } else if (halfCheck.checked) {
        mathDisplay.innerHTML = `<span class="flex justify-between"><span>Driven:</span> <span class="font-bold">${hoursDisplay} hrs</span></span>
                                 <span class="flex justify-between"><span>Rate:</span> <span>₱${hourlyRate.toFixed(2)}/hr</span></span>
                                 <span class="flex justify-between border-t border-blue-100 mt-1 pt-1"><span>Hourly Target:</span> <span class="font-bold text-blue-700">₱${parseFloat(proratedStr).toLocaleString()}</span></span>`;
        amtInput.value = proratedStr;
        actInput.value = proratedStr;
    }
}

function triggerDriverAlerts(driverId, shortage) {
    const unitId = document.getElementById('unitId').value;
    const unitOption = document.querySelector(`.unit-option[data-id="${unitId}"]`);
    const primaryId = unitOption ? unitOption.getAttribute('data-primary-id') : '';
    const secondaryId = unitOption ? unitOption.getAttribute('data-secondary-id') : '';

    const extraAlert = document.getElementById('extraDriverAlert');
    if (driverId && driverId !== 'all' && primaryId && driverId !== primaryId && driverId !== secondaryId) {
        if (extraAlert) extraAlert.classList.remove('hidden');
    } else {
        if (extraAlert) extraAlert.classList.add('hidden');
    }

    const shortageAlert = document.getElementById('shortageBalanceAlert');
    const shortageAmountSpan = document.getElementById('shortageBalanceAmount');
    if (shortage > 0) {
        if (shortageAlert) shortageAlert.classList.remove('hidden');
        if (shortageAmountSpan) shortageAmountSpan.textContent = "₱" + shortage.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
        const rawInput = document.getElementById('rawShortageAmount');
        if (rawInput) rawInput.value = shortage;
    } else {
        if (shortageAlert) shortageAlert.classList.add('hidden');
    }
}

document.addEventListener('DOMContentLoaded', function() {
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
    initializeUnitDropdown();
    initializeDriverDropdown();
    
    // Sync manual target changes with dataset and actual boundary
    const amtInput = document.getElementById('boundaryAmount');
    const actualInput = document.getElementById('actualBoundary');

    function applyStrictValidation(input, allowZero = false) {
        input.addEventListener('input', function(e) {
            let val = this.value;
            
            // 1. Numbers only (strip non-numeric except decimal)
            val = val.replace(/[^0-9.]/g, '');
            
            // 2. Prevent leading zeros (e.g. 0500 -> 500)
            if (val.length > 1 && val.startsWith('0') && !val.startsWith('0.')) {
                val = val.replace(/^0+/, '');
            }
            
            // 3. Limit to 4 digits for integer part (max 9999)
            if (val.includes('.')) {
                let parts = val.split('.');
                if (parts[0].length > 4) parts[0] = parts[0].substring(0, 4);
                val = parts[0] + '.' + parts[1].substring(0, 2);
            } else {
                if (val.length > 4) val = val.substring(0, 4);
            }
            
            this.value = val;
        });

        // Prevent invalid keys (e, +, -, etc.)
        input.addEventListener('keydown', function(e) {
            if (['e', 'E', '+', '-'].includes(e.key)) {
                e.preventDefault();
            }
        });
    }

    if (amtInput) {
        applyStrictValidation(amtInput);
        amtInput.addEventListener('input', function() {
            const val = this.value || '0.00';
            this.dataset.originalTarget = val;
            if (actualInput) actualInput.value = val;
            updateBreakdownComputation();
        });
    }

    if (actualInput) {
        applyStrictValidation(actualInput);
        actualInput.addEventListener('change', function() {
            // Prevent pure zero input if not a breakdown case
            const zeroCheck = document.getElementById('needsMaintenanceZeroCheck');
            if ((!zeroCheck || !zeroCheck.checked) && parseFloat(this.value || 0) <= 0) {
                this.value = '';
                alert('Actual collected amount cannot be zero.');
            }
        });
    }

    // Handle Needs Maintenance logic dynamically (Half vs Zero)
    // Mutually exclusive maintenance options
    const maintenanceOptions = document.querySelectorAll('.needs-maintenance-opt');
    maintenanceOptions.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            if (this.checked) {
                maintenanceOptions.forEach(opt => {
                    if (opt !== this) opt.checked = false;
                });
            }
            updateBreakdownComputation();
        });
    });
});
</script>
@endpush