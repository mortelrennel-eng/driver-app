@extends('layouts.app')

@section('title', 'Driver Management - Euro System')
@section('page-heading', 'Driver Management')
@section('page-subheading', 'Centralized driver records, incentives, and performance analytics')

@section('content')

<style>
    @keyframes shortage-blink {
        0% { opacity: 1; }
        50% { opacity: 0.7; }
        100% { opacity: 1; }
    }
    @keyframes shortage-text-pulse {
        0% { color: #dc2626; }
        50% { color: #991b1b; }
        100% { color: #dc2626; }
    }
    .shortage-blink {
        animation: shortage-blink 1.5s infinite ease-in-out;
    }
    .shortage-text-blink {
        animation: shortage-blink 1.5s infinite ease-in-out, shortage-text-pulse 1.5s infinite ease-in-out;
        font-weight: 800 !important;
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
    .modern-row.shortage-row {
        background-color: #fef2f2;
    }
    .modern-row.shortage-row:hover td:first-child {
        border-left-color: #ef4444;
    }
</style>

    <!-- Search and Filters -->
    <div class="bg-white px-4 lg:px-6 py-4 border-b border-gray-200">
        <form method="GET" action="{{ route('driver-management.index') }}" class="flex flex-col lg:flex-row gap-2 items-center justify-between w-full">
            <!-- 2-column Grid for mobile, completely bypassed on desktop -->
            <div class="grid grid-cols-2 lg:contents gap-2 w-full">
                
                <!-- Search input: spans both columns on mobile, expands wide on desktop with min-width -->
                <div class="col-span-2 lg:flex-grow lg:min-w-[260px] order-1 lg:order-2">
                    <div class="relative group">
                        <input type="text" name="search" id="tableSearchInput" value="{{ $search ?? '' }}"
                            class="block w-full pl-3 pr-10 py-2 lg:h-[38px] border border-gray-300 rounded-lg text-xs focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 focus:outline-none"
                            placeholder="Search by driver name or license...">
                        <button type="submit" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-yellow-600 transition-colors">
                            <i data-lucide="search" class="h-4 w-4"></i>
                        </button>
                    </div>
                </div>

                <!-- Sort A-Z: spans 1 column on mobile, w-full lg:w-44 on desktop -->
                <div class="col-span-1 lg:w-44 order-2 lg:order-1 flex-shrink-0">
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i data-lucide="arrow-up-z-a" class="h-4 w-4 text-gray-400"></i>
                        </div>
                        <select name="sort" onchange="this.form.submit()"
                            class="block w-full pl-9 pr-3 py-2 lg:h-[38px] border border-gray-300 rounded-lg text-xs focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 focus:outline-none appearance-none">
                            <option value="alphabetical" {{ ($sort ?? '') === 'alphabetical' ? 'selected' : '' }}>A-Z (Name)</option>
                            <option value="newest" {{ ($sort ?? '') === 'newest' ? 'selected' : '' }}>Newest Joined</option>
                            <option value="oldest" {{ ($sort ?? '') === 'oldest' ? 'selected' : '' }}>Oldest Joined</option>
                            <option value="status" {{ ($sort ?? '') === 'status' ? 'selected' : '' }}>Status (Active first)</option>
                        </select>
                    </div>
                </div>

                <!-- Status Filter: spans 1 column on mobile, w-full lg:w-44 on desktop -->
                <div class="col-span-1 lg:w-44 order-3 flex-shrink-0">
                    <select name="status" onchange="this.form.submit()"
                        class="block w-full px-3 py-2 lg:h-[38px] border border-gray-300 rounded-lg text-xs focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 focus:outline-none">
                        <option value="">All Status</option>
                        <option value="active" {{ ($status_filter ?? '') === 'active' ? 'selected' : '' }}>Active Only</option>
                        <option value="inactive" {{ ($status_filter ?? '') === 'inactive' ? 'selected' : '' }}>Inactive Only</option>
                        <option value="no_unit" {{ ($status_filter ?? '') === 'no_unit' ? 'selected' : '' }}>Available (No Unit)</option>
                        <option value="banned" {{ ($status_filter ?? '') === 'banned' ? 'selected' : '' }}>Banned Drivers</option>
                    </select>
                </div>

            </div>

            <!-- Action buttons container: uniform heights, single-row non-wrapping container on mobile, auto-width on desktop -->
            <div id="driverActionButtonsBar" class="flex gap-1.5 lg:gap-2 items-center flex-nowrap overflow-x-auto whitespace-nowrap scrollbar-none w-full lg:w-auto lg:flex-shrink-0 max-w-full pb-1 mt-2 lg:mt-0 order-4" style="-ms-overflow-style: none; scrollbar-width: none;">
                <style>
                    /* Hide scrollbar for Chrome, Safari and Opera */
                    #driverActionButtonsBar::-webkit-scrollbar {
                        display: none;
                    }
                </style>
                <button type="button" onclick="openPendingDebtsModal()" class="px-3 py-1 bg-red-600 text-white rounded-lg hover:bg-red-700 flex items-center justify-center gap-1.5 lg:gap-2 text-xs font-semibold shadow-sm h-[38px] flex-1 min-w-0 lg:flex-initial lg:w-[150px]">
                    <i data-lucide="wallet" class="w-3.5 h-3.5"></i> Pending Debts
                </button>
                <button type="button" onclick="openAddDriverModal()" class="px-3 py-1 bg-green-600 text-white rounded-lg hover:bg-green-700 flex items-center justify-center gap-1.5 lg:gap-2 text-xs font-semibold shadow-sm h-[38px] flex-1 min-w-0 lg:flex-initial lg:w-[150px]">
                    <i data-lucide="plus" class="w-3.5 h-3.5"></i> Add Driver
                </button>
            </div>
        </form>
    </div>

    {{-- Driver List Container --}}
    <div id="driversTableContainer">
        @include('driver-management.partials._drivers_table')
    </div>

    {{-- Add/Edit Driver Modal --}}
    <div id="addDriverModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-3xl h-[90vh] flex flex-col overflow-hidden">

            {{-- Modal Header (Dark Navy, matching Edit Unit) --}}
            <div class="bg-slate-800 p-4 shrink-0">
                <div class="flex justify-between items-center">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-white bg-opacity-20 rounded-lg">
                            <i data-lucide="user" class="w-5 h-5 text-white"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-white leading-tight" id="driverModalTitle">Add Driver</h3>
                            <p class="text-sm text-blue-100 leading-tight" id="driverModalSubtitle">Fill in the driver's information below</p>
                        </div>
                    </div>
                    <button type="button" onclick="closeAddDriverModal()" class="text-white hover:text-gray-200 transition-colors">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>
            </div>

            {{-- Form --}}
            <form id="driverForm" method="POST" class="flex flex-col flex-1 overflow-hidden">
                @csrf
                <input type="hidden" name="_method" id="driverFormMethod" value="POST">
                <input type="hidden" name="driver_id" id="editDriverId" value="">

                {{-- Scrollable Content --}}
                <div class="p-6 flex-1 overflow-y-auto space-y-8">

                    {{-- Section 1: Personal Information --}}
                    <div>
                        <div class="flex items-center gap-2 mb-4">
                            <div class="p-2 bg-blue-100 rounded-lg">
                                <i data-lucide="user" class="w-5 h-5 text-blue-600"></i>
                            </div>
                            <h4 class="text-lg font-semibold text-gray-900">Personal Information</h4>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div class="space-y-2">
                                <label class="block text-sm font-semibold text-gray-700">First Name <span class="text-red-500">*</span></label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i data-lucide="user" class="w-4 h-4 text-gray-400"></i>
                                    </div>
                                    <input type="text" name="first_name" id="driverFirstName" required
                                        maxlength="15"
                                        class="w-full pl-10 pr-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                        placeholder="e.g., Juan"
                                        pattern="^(?!\s+$)[A-Za-z]+(\s[A-Za-z]+)?$"
                                        title="First name: letters only, max 15 chars, one space allowed."
                                        oninput="let v=this.value.replace(/[^A-Za-z ]/g,''); let parts=v.split(' '); this.value=(parts.length>2?parts[0]+' '+parts.slice(1).join('').replace(/ /g,''):v).slice(0, 15)">
                                </div>
                            </div>
                            <div class="space-y-2">
                                <label class="block text-sm font-semibold text-gray-700">Last Name <span class="text-red-500">*</span></label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i data-lucide="user" class="w-4 h-4 text-gray-400"></i>
                                    </div>
                                    <input type="text" name="last_name" id="driverLastName" required
                                        maxlength="15"
                                        class="w-full pl-10 pr-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                        placeholder="e.g., Dela Cruz"
                                        pattern="^(?!\s+$)[A-Za-z]+(\s[A-Za-z]+)?$"
                                        title="Last name: letters only, max 15 chars, one space allowed."
                                        oninput="let v=this.value.replace(/[^A-Za-z ]/g,''); let parts=v.split(' '); this.value=(parts.length>2?parts[0]+' '+parts.slice(1).join('').replace(/ /g,''):v).slice(0,15)">
                                </div>
                            </div>
                            <div class="space-y-2">
                                <label class="block text-sm font-semibold text-gray-700">Contact Number <span class="text-red-500">*</span></label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i data-lucide="phone" class="w-4 h-4 text-gray-400"></i>
                                    </div>
                                    <input type="tel" name="contact_number" id="driverContact" required
                                        maxlength="11"
                                        class="w-full pl-10 pr-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                        placeholder="e.g., 09XXXXXXXXX"
                                        pattern="^[0-9]{11}$"
                                        title="Contact number must be exactly 11 digits."
                                        oninput="this.value = this.value.replace(/[^0-9]/g,'').slice(0, 11)">
                                </div>
                            </div>
                            <div class="space-y-2">
                                <label class="block text-sm font-semibold text-gray-700">Driver Status</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i data-lucide="activity" class="w-4 h-4 text-gray-400"></i>
                                    </div>
                                    <select name="is_active" id="editIsActive"
                                        class="w-full pl-10 pr-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent appearance-none">
                                        <option value="1">Active</option>
                                        <option value="0">Inactive</option>
                                    </select>
                                </div>
                            </div>
                            <div class="space-y-2 md:col-span-2">
                                <label class="block text-sm font-semibold text-gray-700">Address <span class="text-red-500">*</span></label>
                                <div class="relative">
                                    <div class="absolute top-3 left-0 pl-3 flex items-start pointer-events-none">
                                        <i data-lucide="map-pin" class="w-4 h-4 text-gray-400"></i>
                                    </div>
                                    <textarea name="address" id="driverAddress" required rows="2"
                                        maxlength="250"
                                        class="w-full pl-10 pr-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none"
                                        placeholder="Complete address..."
                                        oninput="let original = this.value; this.value = this.value.replace(/[^a-zA-Z0-9\s.,'-]/g, '').slice(0, 250); if(original !== this.value && this.value.length === 250) { document.getElementById('address-notif').classList.remove('hidden'); } else { document.getElementById('address-notif').classList.add('hidden'); }"></textarea>
                                </div>
                                <p id="address-notif" class="text-xs text-red-500 hidden font-semibold">Address limit reached (250 chars) or invalid character removed.</p>
                                <p class="text-[10px] text-gray-400">Only letters, numbers, spaces, dots, commas, and dashes. Max 250 chars.</p>
                            </div>
                        </div>
                    </div>

                    {{-- Section 2: License & Employment --}}
                    <div>
                        <div class="flex items-center gap-2 mb-4">
                            <div class="p-2 bg-yellow-100 rounded-lg">
                                <i data-lucide="credit-card" class="w-5 h-5 text-yellow-600"></i>
                            </div>
                            <h4 class="text-lg font-semibold text-gray-900">License & Employment</h4>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div class="space-y-2">
                                <label class="block text-sm font-semibold text-gray-700">License Number <span class="text-red-500">*</span></label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i data-lucide="credit-card" class="w-4 h-4 text-gray-400"></i>
                                    </div>
                                    <input type="text" name="license_number" id="driverLicense" required
                                        maxlength="13"
                                        class="w-full pl-10 pr-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent font-mono uppercase"
                                        placeholder="A00-00-000000"
                                        pattern="^[A-Z][0-9]{2}-[0-9]{2}-[0-9]{6}$"
                                        title="Format must be 1 letter, 10 numbers separated by dashes: X00-00-000000"
                                        oninput="
                                            let v = this.value.toUpperCase().replace(/[^A-Z0-9]/g, ''); 
                                            if (v.length > 0) v = v.replace(/^([^A-Z]+)/, ''); 
                                            if (v.length > 1) v = v[0] + v.substring(1).replace(/[^0-9]/g, ''); 
                                            if (v.length > 3) v = v.slice(0, 3) + '-' + v.slice(3);
                                            if (v.length > 6) v = v.slice(0, 6) + '-' + v.slice(6);
                                            this.value = v.slice(0, 13);
                                        ">
                                </div>
                                <p class="text-[10px] text-gray-400">Format: X00-00-000000 (Auto-formatted)</p>
                            </div>
                            <div class="space-y-2">
                                <label class="block text-sm font-semibold text-gray-700">License Expiry <span class="text-red-500">*</span></label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i data-lucide="calendar" class="w-4 h-4 text-gray-400"></i>
                                    </div>
                                    <input type="date" name="license_expiry" id="driverLicenseExpiry" required
                                        class="w-full pl-10 pr-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                </div>
                            </div>
                            <div class="space-y-2">
                                <label class="block text-sm font-semibold text-gray-700">Hire Date <span class="text-red-500">*</span></label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i data-lucide="briefcase" class="w-4 h-4 text-gray-400"></i>
                                    </div>
                                    <input type="date" name="hire_date" id="driverHireDate" required
                                        class="w-full pl-10 pr-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                        onchange="(function(el){ const n=new Date(); const today=n.getFullYear()+'-'+String(n.getMonth()+1).padStart(2,'0')+'-'+String(n.getDate()).padStart(2,'0'); if(el.value > today){ el.value=today; el.setCustomValidity('Hire date cannot be in the future.'); el.reportValidity(); } else { el.setCustomValidity(''); } })(this)">
                                </div>
                            </div>
                            <div class="space-y-2">
                                <label class="block text-sm font-semibold text-gray-700 flex justify-between items-center">
                                    <span>Daily Boundary Target</span>
                                    <span id="unitDerivedLabel" class="text-[10px] text-gray-500 font-bold hidden"></span>
                                    <span id="codingBoundaryAlert" class="text-[10px] text-red-600 font-bold hidden"></span>
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="text-gray-500 text-sm font-bold">₱</span>
                                    </div>
                                    <input type="number" name="daily_boundary_target" id="driverBoundaryTarget" step="0.01" readonly
                                        class="w-full pl-10 pr-4 py-3 border-2 border-gray-300 rounded-lg bg-gray-50 text-gray-500 cursor-not-allowed focus:outline-none"
                                        placeholder="Auto-synced from Unit Management">
                                </div>
                                <p class="text-xs text-gray-400 italic">Automatically synchronized from Unit Management.</p>
                            </div>
                        </div>
                    </div>

                    {{-- Section 3: Emergency Contact --}}
                    <div>
                        <div class="flex items-center gap-2 mb-4">
                            <div class="p-2 bg-red-100 rounded-lg">
                                <i data-lucide="alert-circle" class="w-5 h-5 text-red-600"></i>
                            </div>
                            <h4 class="text-lg font-semibold text-gray-900">Emergency Contact</h4>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div class="space-y-2">
                                <label class="block text-sm font-semibold text-gray-700">Contact Name <span class="text-red-500">*</span></label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i data-lucide="users" class="w-4 h-4 text-gray-400"></i>
                                    </div>
                                    <input type="text" name="emergency_contact" id="driverEmergencyContact" required
                                        maxlength="25"
                                        class="w-full pl-10 pr-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                        placeholder="e.g., Maria Dela Cruz"
                                        pattern="^(?!\s+$)[A-Za-z]+(\s[A-Za-z ]+)*$"
                                        title="Contact name: letters and spaces only, max 25 chars."
                                        oninput="this.value = this.value.replace(/[^A-Za-z ]/g,'').replace(/^ /, '').replace(/ {2,}/, ' ').slice(0, 25)">
                                </div>
                            </div>
                            <div class="space-y-2">
                                <label class="block text-sm font-semibold text-gray-700">Contact Phone <span class="text-red-500">*</span></label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i data-lucide="phone-call" class="w-4 h-4 text-gray-400"></i>
                                    </div>
                                    <input type="tel" name="emergency_phone" id="driverEmergencyPhone" required
                                        maxlength="11"
                                        class="w-full pl-10 pr-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                        placeholder="e.g., 09XXXXXXXXX"
                                        pattern="^[0-9]{11}$"
                                        title="Emergency phone must be exactly 11 digits."
                                        oninput="this.value = this.value.replace(/[^0-9]/g,'').slice(0, 11)">
                                </div>
                            </div>
                        </div>
                    </div>

                </div>{{-- End Scrollable Content --}}

                {{-- Fixed Footer --}}
                <div class="p-4 border-t flex justify-between items-center gap-3 shadow-inner bg-gray-50 shrink-0">
                    <button type="button" id="deleteDriverButton" onclick="confirmDeleteDriver()"
                        class="hidden px-5 py-2 bg-orange-100 text-orange-700 border border-orange-200 rounded-lg hover:bg-orange-200 text-sm font-bold transition-all flex items-center gap-2">
                        <i data-lucide="archive" class="w-4 h-4"></i> Archive Driver
                    </button>
                    <div class="flex gap-3 ml-auto">
                        <button type="button" onclick="closeAddDriverModal()"
                            class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 text-sm font-bold transition-all">
                            Cancel
                        </button>
                        <button type="submit"
                            class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-bold shadow-lg shadow-blue-200/50 transition-all flex items-center gap-2">
                            <i data-lucide="save" class="w-4 h-4"></i> Save Driver
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Driver Details Modal with Tabs --}}
    <div id="driverDetailsModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm hidden h-full w-full z-50 flex items-center justify-center p-4 transition-all duration-300">
        <div class="relative bg-white rounded-2xl shadow-2xl max-w-5xl w-full h-[90vh] overflow-hidden flex flex-col scale-95 transition-transform duration-300" id="driverDetailsModalContainer">
            {{-- Modal Header (Deep Navy) --}}
            <div class="bg-slate-800 p-5 shrink-0">
                <div class="flex justify-between items-center">
                    <div class="flex items-center gap-3">
                        <div class="p-2.5 bg-white/20 rounded-xl flex items-center justify-center">
                            <i data-lucide="user-check" class="w-6 h-6 text-blue-400"></i>
                        </div>
                        <div>
                            <h3 class="text-xl font-black text-white tracking-wide uppercase" id="driverDetailsName">Driver Details</h3>
                            <p class="text-[10px] font-bold text-slate-400 mt-0.5 uppercase tracking-widest" id="driverDetailsSubtitle">Profiling & Performance Analysis</p>
                        </div>
                    </div>
                    <button type="button" onclick="closeDriverDetails()" class="text-slate-400 hover:text-white bg-slate-700/50 hover:bg-slate-700 p-2 rounded-full transition-colors">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>
            </div>

            {{-- Tabs --}}
            <div class="px-6 bg-slate-50 border-b shrink-0 overflow-x-auto custom-scrollbar">
                <nav class="-mb-px flex space-x-1" aria-label="Tabs">
                    <button type="button" class="driver-tab active py-4 px-4 text-[10px] font-black uppercase tracking-widest border-b-2 border-blue-500 text-blue-600 transition-all flex items-center gap-2" data-tab="basic">
                        <i data-lucide="user" class="w-3.5 h-3.5"></i> Basic Info
                    </button>
                    <button type="button" class="driver-tab py-4 px-4 text-[10px] font-black uppercase tracking-widest border-b-2 border-transparent text-slate-400 hover:text-slate-600 transition-all flex items-center gap-2" data-tab="license">
                        <i data-lucide="file-text" class="w-3.5 h-3.5"></i> License & Documents
                    </button>
                    <button type="button" class="driver-tab py-4 px-4 text-[10px] font-black uppercase tracking-widest border-b-2 border-transparent text-slate-400 hover:text-slate-600 transition-all flex items-center gap-2" data-tab="incentives">
                        <i data-lucide="award" class="w-3.5 h-3.5"></i> Incentives
                    </button>
                    <button type="button" class="driver-tab py-4 px-4 text-[10px] font-black uppercase tracking-widest border-b-2 border-transparent text-slate-400 hover:text-slate-600 transition-all flex items-center gap-2" data-tab="performance">
                        <i data-lucide="trending-up" class="w-3.5 h-3.5"></i> Performance
                    </button>
                    <button type="button" class="driver-tab py-4 px-4 text-[10px] font-black uppercase tracking-widest border-b-2 border-transparent text-slate-400 hover:text-slate-600 transition-all flex items-center gap-2" data-tab="insights">
                        <i data-lucide="brain-circuit" class="w-3.5 h-3.5"></i> Insights
                    </button>
                </nav>
            </div>

            {{-- Tab Panels --}}
            <div class="p-8 overflow-y-auto flex-1 custom-scrollbar">
                <div class="driver-tab-panel" data-tab-panel="basic">
                    <div class="flex items-center gap-2 mb-6">
                        <div class="w-1 h-6 bg-blue-500 rounded-full"></div>
                        <h4 class="text-sm font-black text-slate-800 uppercase tracking-wider">Personal & Employment Details</h4>
                    </div>
                    <div id="basicInfoContent" class="grid grid-cols-1 md:grid-cols-2 gap-6 text-sm text-slate-600">
                        <p class="text-slate-400 animate-pulse">Synchronizing basic profile...</p>
                    </div>
                </div>

                <div class="driver-tab-panel hidden" data-tab-panel="license">
                    <div class="flex items-center gap-2 mb-6">
                        <div class="w-1 h-6 bg-indigo-500 rounded-full"></div>
                        <h4 class="text-sm font-black text-slate-800 uppercase tracking-wider">License & Credentials</h4>
                    </div>
                    <div id="licenseInfoContent" class="grid grid-cols-1 md:grid-cols-2 gap-6 text-sm text-slate-600">
                        <p class="text-slate-400 animate-pulse">Verifying license data...</p>
                    </div>

                    <div class="mt-8 pt-8 border-t border-slate-100">
                        <div class="bg-slate-50 rounded-2xl p-6 border border-slate-200">
                            <h5 class="text-xs font-black text-slate-800 uppercase tracking-widest mb-2 flex items-center gap-2">
                                <i data-lucide="upload-cloud" class="w-4 h-4 text-blue-500"></i> Secure Document Vault
                            </h5>
                            <p class="text-[11px] text-slate-500 mb-6 font-medium">Upload encrypted copies of NBI, Barangay, and Medical clearances. New uploads will overwrite legacy records.</p>
                            
                            <form id="driverDocumentsForm" method="POST" enctype="multipart/form-data" class="space-y-6">
                                @csrf
                                <input type="hidden" name="_method" value="POST">
                                <input type="hidden" name="driver_id" id="driverDocumentsDriverId" value="">

                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                    <div class="space-y-1.5">
                                        <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest">License (Front)</label>
                                        <input type="file" name="license_front" accept=".jpg,.jpeg,.png,.pdf" class="block w-full text-xs text-slate-700 bg-white border border-slate-200 rounded-xl p-2 cursor-pointer focus:outline-none hover:border-blue-300 transition-colors">
                                    </div>
                                    <div class="space-y-1.5">
                                        <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest">License (Back)</label>
                                        <input type="file" name="license_back" accept=".jpg,.jpeg,.png,.pdf" class="block w-full text-xs text-slate-700 bg-white border border-slate-200 rounded-xl p-2 cursor-pointer focus:outline-none hover:border-blue-300 transition-colors">
                                    </div>
                                    <div class="space-y-1.5">
                                        <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest">NBI Clearance</label>
                                        <input type="file" name="nbi_clearance" accept=".jpg,.jpeg,.png,.pdf" class="block w-full text-xs text-slate-700 bg-white border border-slate-200 rounded-xl p-2 cursor-pointer focus:outline-none hover:border-blue-300 transition-colors">
                                    </div>
                                    <div class="space-y-1.5">
                                        <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest">Barangay Clearance</label>
                                        <input type="file" name="barangay_clearance" accept=".jpg,.jpeg,.png,.pdf" class="block w-full text-xs text-slate-700 bg-white border border-slate-200 rounded-xl p-2 cursor-pointer focus:outline-none hover:border-blue-300 transition-colors">
                                    </div>
                                    <div class="space-y-1.5">
                                        <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest">Medical Certificate</label>
                                        <input type="file" name="medical_certificate" accept=".jpg,.jpeg,.png,.pdf" class="block w-full text-xs text-slate-700 bg-white border border-slate-200 rounded-xl p-2 cursor-pointer focus:outline-none hover:border-blue-300 transition-colors">
                                    </div>
                                </div>

                                <div class="pt-4 flex justify-end">
                                    <button type="submit" class="px-8 py-3 bg-slate-800 text-white text-[10px] font-black uppercase tracking-[0.2em] rounded-xl hover:bg-slate-900 transition-all shadow-lg shadow-slate-200 active:scale-95 flex items-center gap-2">
                                        <i data-lucide="shield-check" class="w-4 h-4 text-blue-400"></i> Commit Changes
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="driver-tab-panel hidden" data-tab-panel="incentives">
                    <div class="flex items-center gap-2 mb-6">
                        <div class="w-1 h-6 bg-emerald-500 rounded-full"></div>
                        <h4 class="text-sm font-black text-slate-800 uppercase tracking-wider">Incentive Performance Hub</h4>
                    </div>
                    <div id="incentivesContent" class="text-sm text-slate-600">
                        <p class="text-slate-400 animate-pulse">Calculating reward eligibility...</p>
                    </div>
                </div>

                <div class="driver-tab-panel hidden" data-tab-panel="performance">
                    <div class="flex items-center gap-2 mb-6">
                        <div class="w-1 h-6 bg-orange-500 rounded-full"></div>
                        <h4 class="text-sm font-black text-slate-800 uppercase tracking-wider">Telemetry & Metrics</h4>
                    </div>
                    <div id="performanceContent" class="text-sm text-slate-600 space-y-2">
                        <p class="text-slate-400 animate-pulse">Fetching operational data...</p>
                    </div>
                </div>

                <div class="driver-tab-panel hidden" data-tab-panel="insights">
                    <div class="flex items-center gap-2 mb-6">
                        <div class="w-1 h-6 bg-rose-500 rounded-full"></div>
                        <h4 class="text-sm font-black text-slate-800 uppercase tracking-wider">Strategic Recommendation</h4>
                    </div>
                    <div id="insightsContent" class="text-sm text-slate-600 space-y-2">
                        <p class="text-slate-400 animate-pulse">Synthesizing AI insights...</p>
                    </div>
                </div>
            </div>

            {{-- Footer --}}
            <div class="p-4 border-t flex justify-end shadow-inner bg-slate-50 shrink-0">
                <button type="button" onclick="closeDriverDetails()" 
                    class="px-8 py-2.5 bg-slate-200 text-slate-700 rounded-xl hover:bg-slate-300 text-sm font-black transition-all">
                    Close Details
                </button>
            </div>
        </div>
    </div>

    {{-- Pending Debts Modal --}}
    <div id="pendingDebtsModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm hidden h-full w-full z-50 flex items-center justify-center p-4 transition-all duration-300">
        <div class="relative bg-white rounded-2xl shadow-2xl max-w-4xl w-full h-[90vh] overflow-hidden flex flex-col scale-95 transition-transform duration-300" id="pendingDebtsModalContainer">
            {{-- Modal Header (Deep Navy) --}}
            <div class="bg-slate-800 p-5 shrink-0">
                <div class="flex justify-between items-center">
                    <div class="flex items-center gap-3">
                        <div class="p-2.5 bg-white/20 rounded-xl flex items-center justify-center">
                            <i data-lucide="wallet" class="w-6 h-6 text-red-400" id="modalIcon"></i>
                        </div>
                        <div>
                            <h3 class="text-xl font-black text-white tracking-wide" id="modalTitle">Pending Driver Debts</h3>
                            <p class="text-xs font-medium text-slate-300 mt-0.5 uppercase tracking-widest" id="modalSubtitle">Accident Charge Management</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <button type="button" onclick="toggleDebtHistory()" id="historyToggleBtn"
                            class="px-4 py-2 bg-slate-700 hover:bg-slate-600 text-white text-xs font-black rounded-xl transition-all flex items-center gap-2">
                            <i data-lucide="history" class="w-4 h-4"></i> View History
                        </button>
                        <button type="button" onclick="closePendingDebtsModal()" class="text-slate-400 hover:text-white bg-slate-700/50 hover:bg-slate-700 p-2 rounded-full transition-colors">
                            <i data-lucide="x" class="w-5 h-5"></i>
                        </button>
                    </div>
                </div>
            </div>
            
            <div class="p-8 overflow-y-auto flex-1 custom-scrollbar">
                <div id="pendingDebtsContent" class="space-y-6">
                    <div class="flex flex-col items-center justify-center py-20 text-slate-400">
                        <div class="relative w-16 h-16 mb-4">
                            <div class="absolute inset-0 border-4 border-slate-100 rounded-full"></div>
                            <div class="absolute inset-0 border-4 border-red-500 rounded-full border-t-transparent animate-spin"></div>
                        </div>
                        <p class="font-bold text-sm tracking-widest uppercase">Synchronizing Debt Records...</p>
                    </div>
                </div>
                <div id="debtHistoryContent" class="hidden space-y-6">
                    {{-- History content will be loaded here --}}
                </div>
            </div>

            {{-- Footer --}}
            <div class="p-4 border-t flex justify-end shadow-inner bg-slate-50 shrink-0">
                <button type="button" onclick="closePendingDebtsModal()" 
                    class="px-8 py-2.5 bg-slate-200 text-slate-700 rounded-xl hover:bg-slate-300 text-sm font-black transition-all">
                    Close Management
                </button>
            </div>
        </div>
    </div>


    <script>
    // ─── Set hire date max to TODAY (client local time) immediately on page load ───
    document.addEventListener('DOMContentLoaded', function () {
        const _n = new Date();
        const _today = _n.getFullYear() + '-' +
            String(_n.getMonth() + 1).padStart(2, '0') + '-' +
            String(_n.getDate()).padStart(2, '0');
        const hireDateEl = document.getElementById('driverHireDate');
        if (hireDateEl) {
            hireDateEl.max = _today;
            hireDateEl.value = _today;
        }
    });

    window.boundaryRules = @json($boundary_rules ?? []);
    function openAddDriverModal() {
        document.getElementById('driverModalTitle').textContent = 'Add Driver';
        document.getElementById('driverFormMethod').value = 'POST';
        document.getElementById('driverForm').action = '{{ route('driver-management.store') }}';
        document.getElementById('editDriverId').value = '';
        document.getElementById('driverFirstName').value = '';
        document.getElementById('driverLastName').value = '';

        document.getElementById('driverContact').value = '';
        document.getElementById('driverLicense').value = '';
        document.getElementById('driverLicenseExpiry').value = '';
        // Use local date (not UTC) to correctly enforce today's date in PH timezone
        const _now = new Date();
        const todayStr = _now.getFullYear() + '-' +
            String(_now.getMonth() + 1).padStart(2, '0') + '-' +
            String(_now.getDate()).padStart(2, '0');
        const hireDateEl = document.getElementById('driverHireDate');
        hireDateEl.max = todayStr;
        hireDateEl.value = todayStr;
        document.getElementById('driverAddress').value = '';
        document.getElementById('driverEmergencyContact').value = '';
        document.getElementById('driverEmergencyPhone').value = '';
        const targetInput = document.getElementById('driverBoundaryTarget');
        const codingAlert = document.getElementById('codingBoundaryAlert');
        
        targetInput.value = '';
        targetInput.placeholder = 'Please dispatch to appear boundary';
        if (codingAlert) {
            codingAlert.classList.remove('hidden');
            codingAlert.classList.remove('text-red-600');
            codingAlert.classList.add('text-gray-500');
            codingAlert.textContent = '(Pending Dispatch)';
        }

        document.getElementById('editIsActive').value = '1';
        document.getElementById('deleteDriverButton').classList.add('hidden');
        document.getElementById('addDriverModal').classList.remove('hidden');
        lucide.createIcons();
    }

    function openEditDriverModal(id) {
        fetch('{{ route('driver-management.index') }}/' + id + '?format=json', {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
        .then(r => r.json())
        .then(data => {
            document.getElementById('driverModalTitle').textContent = 'Edit Driver';
            document.getElementById('driverFormMethod').value = 'PUT';
            document.getElementById('driverForm').action = '{{ url('driver-management') }}/' + id;
            document.getElementById('editDriverId').value = id;
            document.getElementById('driverFirstName').value = data.first_name || '';
            document.getElementById('driverLastName').value = data.last_name || '';

            document.getElementById('driverContact').value = data.contact_number || '';
            document.getElementById('driverLicense').value = data.license_number || '';
            document.getElementById('driverLicenseExpiry').value = data.license_expiry || '';
            
            // Set max to today for Edit mode as well
            const _n = new Date();
            const _today = _n.getFullYear() + '-' +
                String(_n.getMonth() + 1).padStart(2, '0') + '-' +
                String(_n.getDate()).padStart(2, '0');
            const hireDateEl = document.getElementById('driverHireDate');
            hireDateEl.max = _today;
            hireDateEl.value = data.hire_date || _today;
            
            document.getElementById('driverAddress').value = data.address || '';
            document.getElementById('driverEmergencyContact').value = data.emergency_contact || '';
            document.getElementById('driverEmergencyPhone').value = data.emergency_phone || '';
            
            const targetInput = document.getElementById('driverBoundaryTarget');
            const codingAlert = document.getElementById('codingBoundaryAlert');
            
            if (data.current_pricing) {
                targetInput.value = data.current_pricing.rate.toFixed(2);
                targetInput.placeholder = '0.00';
                
                if (data.current_pricing.label && data.current_pricing.type !== 'regular') {
                    codingAlert.classList.remove('hidden');
                    codingAlert.textContent = data.current_pricing.label;
                    codingAlert.className = data.current_pricing.type === 'coding' ? 'text-[11px] text-red-600 font-bold' : 'text-[11px] text-blue-600 font-bold';
                } else {
                    codingAlert.classList.add('hidden');
                }
            } else {
                targetInput.value = data.daily_boundary_target || '';
                targetInput.placeholder = 'Enter boundary target...';
                codingAlert.classList.add('hidden');
            }
            document.getElementById('editIsActive').value = data.is_active ? '1' : '0';
            document.getElementById('deleteDriverButton').classList.remove('hidden');
            document.getElementById('addDriverModal').classList.remove('hidden');
            lucide.createIcons();
        });
    }

    function closeAddDriverModal() {
        document.getElementById('addDriverModal').classList.add('hidden');
    }

    function confirmDeleteDriver() {
        const id = document.getElementById('editDriverId').value;
        const firstName = document.getElementById('driverFirstName').value || '';
        const lastName = document.getElementById('driverLastName').value || '';
        const name = (firstName + ' ' + lastName).trim() || 'this driver';
        deleteDriver(id, name);
    }

    function deleteDriver(id, name) {
        if (!id) return;
        if (confirm('Are you sure you want to delete ' + name + '?')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ url('driver-management') }}/' + id;
            form.innerHTML = '@csrf' +
                            '<input type="hidden" name="_method" value="DELETE">';
            document.body.appendChild(form);
            form.submit();
        }
    }

    function openDriverDetails(id) {
        const modal = document.getElementById('driverDetailsModal');
        modal.classList.remove('hidden');
        
        setTimeout(() => {
            document.getElementById('driverDetailsModalContainer').classList.remove('scale-95');
        }, 10);

        document.querySelectorAll('.driver-tab').forEach(btn => {
            btn.classList.remove('border-blue-500', 'text-blue-600', 'active');
            btn.classList.add('border-transparent', 'text-slate-400');
        });
        document.querySelectorAll('.driver-tab-panel').forEach(panel => { panel.classList.add('hidden'); });
        
        const firstTab = document.querySelector('.driver-tab[data-tab="basic"]');
        const firstPanel = document.querySelector('.driver-tab-panel[data-tab-panel="basic"]');
        if (firstTab && firstPanel) {
            firstTab.classList.add('border-blue-500', 'text-blue-600', 'active');
            firstPanel.classList.remove('hidden');
        }

        document.getElementById('driverDocumentsDriverId').value = id;
        document.getElementById('driverDocumentsForm').action = '{{ url('driver-management/upload-documents') }}/' + id;

        fetch('{{ route('driver-management.index') }}/' + id + '?format=json', {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
        .then(r => r.json())
        .then(data => {
            document.getElementById('driverDetailsName').textContent = data.full_name || 'Driver Details';
            document.getElementById('driverDetailsSubtitle').textContent = data.assigned_unit ? `Assigned to ${data.assigned_unit}` : 'Not currently assigned';
            document.getElementById('basicInfoContent').innerHTML = `
                <div class="space-y-4">
                    <div class="flex flex-col">
                        <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Personal Identification</span>
                        <p class="text-base font-black text-slate-900 mt-1">${data.first_name || ''} ${data.last_name || ''}</p>
                    </div>
                    <div class="flex flex-col">
                        <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Primary Contact</span>
                        <p class="text-sm font-bold text-slate-700 mt-0.5 flex items-center gap-2">
                            <i data-lucide="phone" class="w-3.5 h-3.5 text-blue-500"></i> ${data.contact_number || 'N/A'}
                        </p>
                    </div>
                    <div class="flex flex-col">
                        <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Residential Address</span>
                        <p class="text-sm font-bold text-slate-700 mt-0.5 flex items-center gap-2">
                            <i data-lucide="map-pin" class="w-3.5 h-3.5 text-blue-500"></i> ${data.address || 'N/A'}
                        </p>
                    </div>
                    <div class="pt-4 border-t border-slate-50">
                        <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Emergency Nexus</span>
                        <div class="mt-2 bg-rose-50 p-3 rounded-xl border border-rose-100">
                            <p class="text-xs font-black text-rose-800">${data.emergency_contact || 'N/A'}</p>
                            <p class="text-[11px] font-bold text-rose-600 mt-0.5">${data.emergency_phone || 'N/A'}</p>
                        </div>
                    </div>
                </div>
                <div class="space-y-4">
                    <div class="bg-slate-50 p-4 rounded-2xl border border-slate-100">
                        <div class="flex justify-between items-start mb-4">
                            <div>
                                <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Employment Tenure</span>
                                <p class="text-sm font-black text-slate-900 mt-0.5">Joined ${data.hire_date || 'N/A'}</p>
                            </div>
                            <div class="text-right">
                                <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Fleet Status</span>
                                <div class="mt-1">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-black uppercase tracking-widest ${data.driver_status === 'banned' ? 'bg-red-100 text-red-700 ring-1 ring-red-300 animate-pulse' : (data.is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700')}">
                                        ${(data.driver_status || 'Unknown')}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="space-y-3">
                            <div class="flex justify-between items-center py-2 border-b border-slate-200/50">
                                <span class="text-xs font-bold text-slate-500">Standard Daily Rate</span>
                                <span class="text-sm font-black text-slate-900">₱${data.assigned_boundary_rate ? parseFloat(data.assigned_boundary_rate).toLocaleString() : '0.00'}</span>
                            </div>
                            <div class="flex justify-between items-center py-2 border-b border-slate-200/50">
                                <span class="text-xs font-bold text-slate-500">Active Targeted Rate</span>
                                <span class="text-sm font-black text-blue-600">₱${data.current_pricing ? data.current_pricing.rate.toFixed(2) : '0.00'}</span>
                            </div>
                            ${data.current_pricing && data.current_pricing.label ? `
                                <div class="mt-2 text-right">
                                    <span class="text-[9px] font-black bg-blue-100 text-blue-700 px-2 py-0.5 rounded uppercase tracking-tighter">${data.current_pricing.label}</span>
                                </div>
                            ` : ''}
                        </div>
                    </div>
                </div>
            `;

            document.getElementById('licenseInfoContent').innerHTML = `
                <div class="space-y-4">
                    <div class="flex flex-col">
                        <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Professional License Number</span>
                        <p class="text-base font-mono font-black text-slate-900 mt-1 tracking-wider">${data.license_number || ''}</p>
                    </div>
                    <div class="flex flex-col">
                        <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Validation Expiry Date</span>
                        <p class="text-sm font-bold ${new Date(data.license_expiry) < new Date() ? 'text-red-600' : 'text-slate-700'} mt-0.5 flex items-center gap-2">
                            <i data-lucide="calendar" class="w-3.5 h-3.5"></i> ${data.license_expiry || 'N/A'}
                        </p>
                    </div>
                </div>
                <div class="bg-indigo-50/50 p-5 rounded-2xl border border-indigo-100 self-start">
                    <div class="flex items-center gap-3 mb-2">
                        <div class="p-1.5 bg-indigo-100 rounded-lg">
                            <i data-lucide="shield-check" class="w-4 h-4 text-indigo-600"></i>
                        </div>
                        <p class="text-xs font-black text-indigo-800 uppercase tracking-widest">Integrity Guard</p>
                    </div>
                    <p class="text-xs text-indigo-600/80 leading-relaxed font-medium">Automatic system verification of driver credentials. All documents uploaded are cross-referenced with fleet security protocols.</p>
                </div>
            `;

            // ===================== INCENTIVES TAB =====================
            const incentiveRate = data.incentive_rate || 0;
            const rateColor = incentiveRate >= 80 ? 'text-emerald-600' : incentiveRate >= 50 ? 'text-amber-600' : 'text-rose-600';
            const rateBar  = incentiveRate >= 80 ? 'bg-emerald-500' : incentiveRate >= 50 ? 'bg-amber-400' : 'bg-rose-500';

            let incentiveRowsHtml = '';
            if (data.incentive_breakdown && data.incentive_breakdown.length > 0) {
                data.incentive_breakdown.forEach(b => {
                    const notes = (b.notes || '').toLowerCase();
                    let reason = '';
                    if (!b.has_incentive) {
                        if (notes.includes('vehicle damaged')) reason = '<span class="text-[10px] font-black uppercase text-orange-600">Damage</span>';
                        else if (notes.includes('maintenance')) reason = '<span class="text-[10px] font-black uppercase text-rose-600">Breakdown</span>';
                        else reason = '<span class="text-[10px] font-black uppercase text-slate-400">Late Turn</span>';
                    }
                    const statusColors = {paid:'text-emerald-600',shortage:'text-rose-600',excess:'text-blue-600'};
                    incentiveRowsHtml += `
                    <tr class="border-b border-slate-50 ${b.has_incentive ? '' : 'bg-rose-50/30'}">
                        <td class="p-4 font-bold text-slate-600">${new Date(b.date).toLocaleDateString('en-PH',{month:'short',day:'numeric'})}</td>
                        <td class="p-4 font-black text-slate-800 tracking-tight">${b.plate_number||'—'}</td>
                        <td class="p-4 font-bold text-slate-700">₱${parseFloat(b.actual_boundary||0).toLocaleString('en-PH',{minimumFractionDigits:2})}</td>
                        <td class="p-4 font-black text-[10px] uppercase tracking-widest ${statusColors[b.status]||'text-slate-600'}">${(b.status||'')}</td>
                        <td class="p-4 text-center">${b.has_incentive ? '<span class="p-1 bg-emerald-100 text-emerald-600 rounded-lg text-[10px] font-black">EARNED</span>' : '<span class="p-1 bg-rose-100 text-rose-600 rounded-lg text-[10px] font-black">MISSED</span>'}</td>
                        <td class="p-4">${reason}</td>
                    </tr>`;
                });
            } else {
                incentiveRowsHtml = '<tr><td colspan="6" class="p-8 text-center text-slate-400 font-bold uppercase tracking-widest text-[10px]">No active shift logs for this cycle</td></tr>';
            }

            document.getElementById('incentivesContent').innerHTML = `
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
                    <div class="bg-emerald-50 rounded-2xl p-5 border border-emerald-100/50 relative overflow-hidden group">
                        <div class="absolute -right-2 -bottom-2 opacity-5 transition-transform group-hover:scale-110"><i data-lucide="banknote" class="w-16 h-16 text-emerald-900"></i></div>
                        <p class="text-[9px] text-emerald-600 font-black uppercase tracking-[0.2em] mb-2">Monthly Reward</p>
                        <p class="text-2xl font-black text-emerald-900 leading-none">₱${parseFloat(data.monthly_incentive||0).toLocaleString('en-PH',{minimumFractionDigits:2})}</p>
                        <p class="text-[10px] text-emerald-600 font-bold mt-2">5% Revenue Share</p>
                    </div>
                    <div class="bg-blue-50 rounded-2xl p-5 border border-blue-100/50 relative overflow-hidden group">
                        <div class="absolute -right-2 -bottom-2 opacity-5 transition-transform group-hover:scale-110"><i data-lucide="calendar-check" class="w-16 h-16 text-blue-900"></i></div>
                        <p class="text-[9px] text-blue-600 font-black uppercase tracking-[0.2em] mb-2">Service Cycles</p>
                        <p class="text-2xl font-black text-blue-900 leading-none">${data.total_shifts_month||0}</p>
                        <p class="text-[10px] text-blue-600 font-bold mt-2">${data.incentive_earned_count||0} / ${data.total_shifts_month||0} Success</p>
                    </div>
                    <div class="bg-slate-900 rounded-2xl p-5 shadow-lg shadow-slate-200 relative overflow-hidden group">
                        <p class="text-[9px] text-slate-400 font-black uppercase tracking-[0.2em] mb-2">Quality Index</p>
                        <p class="text-2xl font-black ${rateColor.replace('text-','text-')} leading-none">${incentiveRate}%</p>
                        <div class="w-full bg-slate-800 rounded-full h-1.5 mt-3 overflow-hidden"><div class="${rateBar} h-1.5 rounded-full transition-all duration-1000" style="width:${incentiveRate}%"></div></div>
                    </div>
                    <div class="bg-rose-50 rounded-2xl p-5 border border-rose-100/50 relative overflow-hidden group">
                        <div class="absolute -right-2 -bottom-2 opacity-5"><i data-lucide="alert-triangle" class="w-16 h-16 text-rose-900"></i></div>
                        <p class="text-[9px] text-rose-600 font-black uppercase tracking-[0.2em] mb-2">Friction Points</p>
                        <div class="space-y-1 mt-1">
                            <p class="text-[10px] text-rose-800 font-black uppercase tracking-tight flex justify-between">Late Turn: <span>${data.late_turn_missed||0}</span></p>
                            <p class="text-[10px] text-rose-800 font-black uppercase tracking-tight flex justify-between">Damage: <span>${data.damage_missed||0}</span></p>
                            <p class="text-[10px] text-rose-800 font-black uppercase tracking-tight flex justify-between">Behavior: <span>${data.behavior_missed||0}</span></p>
                            <p class="text-[10px] text-rose-800 font-black uppercase tracking-tight flex justify-between">Shortage: <span>${data.shortage_missed||0}</span></p>
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-2 mb-4">
                    <i data-lucide="list" class="w-4 h-4 text-slate-400"></i>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Chronological Incentive Log (Cycle: ${new Date().toLocaleString('en-PH', { month: 'long' })})</p>
                </div>
                <div class="overflow-x-auto rounded-2xl border border-slate-100 shadow-sm">
                    <table class="w-full text-xs text-left">
                        <thead class="bg-slate-50 text-slate-400 font-black uppercase tracking-widest border-b border-slate-100">
                            <tr>
                                <th class="p-4">Timestamp</th><th class="p-4">Vessel</th><th class="p-4">Actual Coll.</th>
                                <th class="p-4">Finc. Status</th><th class="p-4 text-center">Outcome</th><th class="p-4">Factor</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">${incentiveRowsHtml}</tbody>
                    </table>
                </div>`;

            // ===================== PERFORMANCE TAB =====================
            let perfRowsHtml = '';
            if (data.recent_performance && data.recent_performance.length > 0) {
                data.recent_performance.forEach(log => {
                    const statusColors = {paid:'text-emerald-600 font-black',shortage:'text-rose-600 font-black',excess:'text-blue-600 font-black'};
                    const shortage = parseFloat(log.shortage||0);
                    const excess   = parseFloat(log.excess||0);
                    perfRowsHtml += `
                    <tr class="border-b border-slate-50 hover:bg-slate-50 transition-colors">
                        <td class="p-4 font-bold text-slate-600">${new Date(log.date).toLocaleDateString('en-PH',{month:'short',day:'numeric',year:'numeric'})}</td>
                        <td class="p-4 font-black text-slate-800">${log.plate_number||'N/A'}</td>
                        <td class="p-4 text-slate-500 font-medium">₱${parseFloat(log.boundary_amount||0).toLocaleString('en-PH',{minimumFractionDigits:2})}</td>
                        <td class="p-4 font-black text-slate-900">₱${parseFloat(log.actual_boundary||0).toLocaleString('en-PH',{minimumFractionDigits:2})}</td>
                        <td class="p-4 text-[10px] uppercase tracking-widest ${statusColors[log.status]||''}">${(log.status||'')}</td>
                        <td class="p-4">${shortage > 0 ? '<span class="px-2 py-0.5 bg-rose-50 text-rose-600 rounded-lg font-black">-₱'+parseFloat(shortage).toLocaleString()+'</span>' : excess > 0 ? '<span class="px-2 py-0.5 bg-blue-50 text-blue-600 rounded-lg font-black">+₱'+parseFloat(excess).toLocaleString()+'</span>' : '<span class="text-emerald-600 font-black">—</span>'}</td>
                        <td class="p-4 text-center">${log.has_incentive ? '<i data-lucide="check-circle" class="w-4 h-4 text-emerald-500 mx-auto"></i>' : '<i data-lucide="x-circle" class="w-4 h-4 text-rose-400 mx-auto"></i>'}</td>
                    </tr>`;
                });
            } else {
                perfRowsHtml = '<tr><td colspan="7" class="p-8 text-center text-slate-400 font-bold uppercase tracking-widest text-[10px]">Telemetry data unavailable</td></tr>';
            }

            // Behavior incidents section
            let incidentRowsHtml = '';
            if (data.incidents && data.incidents.length > 0) {
                const sevColors = {critical:'bg-rose-100 text-rose-700 border-rose-200',high:'bg-orange-100 text-orange-700 border-orange-200',medium:'bg-amber-100 text-amber-700 border-amber-200',low:'bg-blue-100 text-blue-700 border-blue-200'};
                data.incidents.forEach(i => {
                    incidentRowsHtml += `
                    <tr class="border-b border-slate-50 hover:bg-slate-50 transition-colors">
                        <td class="p-4 font-bold text-slate-600">${new Date(i.created_at).toLocaleDateString('en-PH',{month:'short',day:'numeric'})}</td>
                        <td class="p-4 font-black text-slate-800">${i.plate_number||'—'}</td>
                        <td class="p-4"><span class="px-2 py-1 border rounded-lg text-[9px] font-black uppercase tracking-widest bg-slate-100 text-slate-700">${(i.incident_type||'').replace('_',' ')}</span></td>
                        <td class="p-4"><span class="px-2 py-1 border rounded-lg text-[9px] font-black uppercase tracking-widest ${sevColors[i.severity]||'bg-slate-100 text-slate-600'}">${(i.severity||'')}</span></td>
                        <td class="p-4 text-[11px] font-bold text-slate-500 max-w-[220px] truncate" title="${i.description||''}">${i.description||''}</td>
                    </tr>`;
                });
            } else {
                incidentRowsHtml = '<tr><td colspan="5" class="p-8 text-center text-slate-400 font-bold uppercase tracking-widest text-[10px]">No behavioral anomalies detected</td></tr>';
            }

            // Absences section
            let absenceRowsHtml = '';
            if (data.absentee_logs && data.absentee_logs.length > 0) {
                data.absentee_logs.forEach(a => {
                    absenceRowsHtml += `
                    <tr class="border-b border-slate-50 hover:bg-rose-50/20 transition-colors">
                        <td class="p-4 text-rose-600 font-black tracking-tight">${new Date(a.date).toLocaleDateString('en-PH',{month:'short',day:'numeric',year:'numeric'})}</td>
                        <td class="p-4 text-slate-600 font-bold"><span class="px-3 py-1 bg-white border border-slate-200 rounded-xl text-[11px]">RELIEF: <strong>${a.first_name||''} ${a.last_name||''}</strong></span></td>
                        <td class="p-4 text-right"><span class="px-2 py-1 rounded-lg text-[9px] font-black tracking-[0.2em] bg-rose-100 text-rose-700 uppercase">Unattended</span></td>
                    </tr>`;
                });
            } else {
                absenceRowsHtml = '<tr><td colspan="3" class="p-8 text-center text-slate-400 font-bold uppercase tracking-widest text-[10px]">Perfect attendance record detected</td></tr>';
            }

            document.getElementById('performanceContent').innerHTML = `
                <div class="grid grid-cols-3 gap-4 mb-8">
                    <div class="bg-slate-900 rounded-2xl p-5 border border-slate-800 shadow-xl relative overflow-hidden group">
                        <div class="absolute right-0 top-0 p-2"><i data-lucide="star" class="w-4 h-4 text-amber-400 fill-amber-400"></i></div>
                        <p class="text-[9px] text-slate-400 font-black uppercase tracking-[0.2em] mb-2">Aggregate Rating</p>
                        <p class="text-2xl font-black text-white">${data.performance_rating ? data.performance_rating.label : 'N/A'}</p>
                        <p class="text-[10px] text-amber-400 font-bold mt-1">${data.performance_rating ? '★'.repeat(data.performance_rating.stars) + '☆'.repeat(Math.max(0, 5 - data.performance_rating.stars)) : ''}</p>
                    </div>
                    <div class="bg-rose-50 rounded-2xl p-5 border border-rose-100 shadow-sm group">
                        <p class="text-[9px] text-rose-500 font-black uppercase tracking-[0.2em] mb-2">30D Incidents</p>
                        <p class="text-2xl font-black text-rose-900">${data.total_incidents_30d||0}</p>
                    </div>
                    <div class="bg-orange-50 rounded-2xl p-5 border border-orange-100 shadow-sm group">
                        <p class="text-[9px] text-orange-500 font-black uppercase tracking-[0.2em] mb-2">Critical Events</p>
                        <p class="text-2xl font-black text-orange-900">${data.high_severity_incidents||0}</p>
                    </div>
                </div>
                
                <div class="space-y-10">
                    <div>
                        <div class="flex items-center justify-between mb-4 px-1">
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest flex items-center gap-2"><i data-lucide="clock" class="w-3.5 h-3.5"></i> Service Continuity (Absences)</p>
                        </div>
                        <div class="overflow-x-auto rounded-2xl border border-slate-100 shadow-sm">
                            <table class="w-full text-xs text-left">
                                <thead class="bg-slate-50 text-slate-400 font-black uppercase tracking-widest">
                                    <tr><th class="p-4">Schedule Date</th><th class="p-4">Relief Driver</th><th class="p-4 text-right">Status</th></tr>
                                </thead>
                                <tbody class="divide-y divide-slate-50">${absenceRowsHtml}</tbody>
                            </table>
                        </div>
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-4 px-1">
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest flex items-center gap-2"><i data-lucide="bar-chart-3" class="w-3.5 h-3.5"></i> Telemetry History (Last 10)</p>
                        </div>
                        <div class="overflow-x-auto rounded-2xl border border-slate-100 shadow-sm">
                            <table class="w-full text-xs text-left">
                                <thead class="bg-slate-50 text-slate-400 font-black uppercase tracking-widest border-b border-slate-100">
                                    <tr><th class="p-4">Date</th><th class="p-4">Unit</th><th class="p-4">Target</th><th class="p-4">Actual</th><th class="p-4">Status</th><th class="p-4">Variance</th><th class="p-4 text-center">Reward</th></tr>
                                </thead>
                                <tbody class="divide-y divide-slate-50">${perfRowsHtml}</tbody>
                            </table>
                        </div>
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-4 px-1">
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest flex items-center gap-2"><i data-lucide="shield-alert" class="w-3.5 h-3.5"></i> Behavioral Logs</p>
                        </div>
                        <div class="overflow-x-auto rounded-2xl border border-slate-100 shadow-sm">
                            <table class="w-full text-xs text-left">
                                <thead class="bg-slate-50 text-slate-400 font-black uppercase tracking-widest border-b border-slate-100">
                                    <tr><th class="p-4">Date</th><th class="p-4">Vessel</th><th class="p-4">Classification</th><th class="p-4">Severity</th><th class="p-4">Telemetry Notes</th></tr>
                                </thead>
                                <tbody class="divide-y divide-slate-50">${incidentRowsHtml}</tbody>
                            </table>
                        </div>
                    </div>
                </div>`;

            // ===================== INSIGHTS TAB =====================
            const score = Math.max(0, Math.min(100,
                (data.incentive_rate||0) * 0.5
                + Math.max(0, 100 - (data.total_incidents_30d||0) * 10) * 0.3
                + (data.high_severity_incidents === 0 ? 20 : 0)
            ));
            const scoreColor = score >= 80 ? 'text-emerald-600' : score >= 50 ? 'text-amber-600' : 'text-rose-600';
            const scoreBar   = score >= 80 ? 'bg-emerald-500' : score >= 50 ? 'bg-amber-400' : 'bg-rose-500';

            const eligStatus = data.is_eligible && data.is_first_week 
                ? '<div class="bg-emerald-900 border border-emerald-800 text-emerald-50 p-6 rounded-2xl mb-6 shadow-xl relative overflow-hidden group"><div class="absolute right-0 top-0 p-4 opacity-10 group-hover:rotate-12 transition-transform"><i data-lucide="party-popper" class="w-20 h-20"></i></div><h3 class="text-xl font-black uppercase tracking-tight mb-1 flex items-center gap-2"><i data-lucide="sparkles" class="w-6 h-6 text-amber-400"></i> Grand Incentive Unlocked</h3><p class="text-xs font-bold text-emerald-400 tracking-wide">Driver has met all operational excellence criteria for the current cycle.</p></div>'
                : data.is_eligible && !data.is_first_week
                ? '<div class="bg-blue-900 border border-blue-800 text-blue-50 p-6 rounded-2xl mb-6 shadow-xl relative overflow-hidden group"><div class="absolute right-0 top-0 p-4 opacity-10 group-hover:rotate-12 transition-transform"><i data-lucide="shield-check" class="w-20 h-20"></i></div><h3 class="text-xl font-black uppercase tracking-tight mb-1 flex items-center gap-2"><i data-lucide="timer" class="w-6 h-6 text-blue-400"></i> Excellence Track Active</h3><p class="text-xs font-bold text-blue-400 tracking-wide">Zero violations detected. Awaiting final validation during 1st cycle week.</p></div>'
                : '<div class="bg-rose-900 border border-rose-800 text-rose-50 p-6 rounded-2xl mb-6 shadow-xl relative overflow-hidden group"><div class="absolute right-0 top-0 p-4 opacity-10 group-hover:rotate-12 transition-transform"><i data-lucide="x-circle" class="w-20 h-20"></i></div><h3 class="text-xl font-black uppercase tracking-tight mb-1 flex items-center gap-2"><i data-lucide="shield-x" class="w-6 h-6 text-rose-400"></i> Eligibility Revoked</h3><p class="text-xs font-bold text-rose-400 tracking-wide">Violation anomalies detected during the evaluation lookback period.</p></div>';

            const reqList = [
                { passed: (data.violations_absences||0) === 0, text: 'Continuity: Zero Unattended Shifts' },
                { passed: data.violations_no_incentive === 0, text: 'Reliability: Perfect Boundary Discipline' },
                { passed: (!data.damage_missed && data.damage_missed === 0) && data.violations_incidents === 0, text: 'Safety: Zero Fleet Asset Damage' },
                { passed: (!data.breakdown_missed && data.breakdown_missed === 0), text: 'Maintenance: Zero Breakdown Factors' },
                { passed: data.violations_incidents === 0, text: 'Protocol: Zero Behavioral Deviations' }
            ];

            const reqsHtml = reqList.map(r => `
                <div class="flex items-center gap-4 py-3 border-b border-slate-100 last:border-0">
                    <span class="flex-shrink-0">${r.passed ? '<div class="p-1 bg-emerald-100 rounded-full"><i data-lucide="check" class="w-3.5 h-3.5 text-emerald-600"></i></div>' : '<div class="p-1 bg-rose-100 rounded-full"><i data-lucide="x" class="w-3.5 h-3.5 text-rose-600"></i></div>'}</span>
                    <span class="text-xs font-black uppercase tracking-widest ${r.passed ? 'text-slate-700' : 'text-rose-400 line-through'}">${r.text}</span>
                </div>
            `).join('');

            const blocksHtml = data.blocking_violations && data.blocking_violations.length > 0 
                ? '<div class="mt-6 p-4 bg-rose-50 rounded-2xl border border-rose-100 shadow-sm"><p class="text-[9px] font-black text-rose-600 uppercase tracking-[0.2em] mb-3 flex items-center gap-2"><i data-lucide="alert-octagon" class="w-3.5 h-3.5"></i> Critical Deviation Factors</p><ul class="space-y-2">' + data.blocking_violations.map(b => `<li class="text-[10px] text-rose-900 font-black uppercase tracking-tight flex items-start gap-2"><span class="w-1.5 h-1.5 bg-rose-500 rounded-full mt-1 shrink-0"></span> ${b}</li>`).join('') + '</ul></div>'
                : '<div class="mt-6 p-4 bg-emerald-50 rounded-2xl border border-emerald-100 shadow-sm"><p class="text-[9px] font-black text-emerald-700 uppercase tracking-[0.2em] text-center flex justify-center items-center gap-2"><i data-lucide="shield-check" class="w-3.5 h-3.5"></i> All Security Protocols Passed</p></div>';

            document.getElementById('insightsContent').innerHTML = `
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                    <div class="space-y-4">
                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-[0.2em]">Operational Excellence Dashboard</p>
                        ${eligStatus}
                        <div class="bg-slate-900 rounded-2xl border border-slate-800 shadow-xl overflow-hidden">
                            <div class="bg-amber-400 p-4 text-center shadow-lg">
                                <p class="text-slate-900 font-black text-sm uppercase tracking-[0.2em] flex justify-center items-center gap-2"><i data-lucide="gift" class="w-4 h-4"></i> Premium Reward Manifest</p>
                            </div>
                            <div class="p-6 flex gap-6 justify-center items-center">
                                <div class="text-center group"><span class="block text-3xl mb-1 transform group-hover:scale-125 transition-transform">🎫</span><span class="text-[8px] font-black text-slate-400 uppercase leading-none">Free<br>Coding</span></div>
                                <div class="w-px h-10 bg-slate-800"></div>
                                <div class="text-center group"><span class="block text-3xl mb-1 transform group-hover:scale-125 transition-transform">🍚</span><span class="text-[8px] font-black text-slate-400 uppercase leading-none">25kg Premium<br>Rice</span></div>
                                <div class="w-px h-10 bg-slate-800"></div>
                                <div class="text-center group"><span class="block text-3xl mb-1 transform group-hover:scale-125 transition-transform">💵</span><span class="text-[8px] font-black text-slate-400 uppercase leading-none">₱500 Performance<br>Cash</span></div>
                            </div>
                        </div>

                        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 flex items-center justify-between shadow-2xl relative overflow-hidden group">
                            <div class="absolute right-0 top-0 p-4 opacity-5 group-hover:scale-110 transition-transform"><i data-lucide="target" class="w-20 h-20 text-white"></i></div>
                            <div>
                                <p class="text-[9px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2">Fleet Strategic Index</p>
                                <p class="text-xs text-slate-500 font-bold leading-tight">Composite calculation of incentive velocity<br>and safety anomalous data.</p>
                            </div>
                            <div class="text-right">
                                <p class="text-4xl font-black ${scoreColor.replace('text-','text-')} leading-none tracking-tighter">${Math.round(score)}<span class="text-base font-black text-slate-700 ml-1">/100</span></p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white border border-slate-100 rounded-2xl p-6 shadow-xl relative overflow-hidden">
                        <div class="flex justify-between items-center mb-6">
                            <p class="text-[9px] font-black text-slate-400 uppercase tracking-[0.2em]">Compliance Protocol Lookback</p>
                            <span class="px-3 py-1 bg-slate-900 text-white rounded-xl text-[9px] font-black uppercase tracking-widest">${data.is_dual_driver ? '60 Days Dual-Vessel' : '30 Days Solo-Vessel'}</span>
                        </div>
                        <p class="text-[11px] text-slate-500 mb-6 font-bold leading-relaxed">System scan verified against the last <strong class="text-slate-900">${data.lookback_days} days</strong> of telemetry data. Compliance must be absolute (100%).</p>
                        
                        <div class="bg-slate-50 rounded-2xl p-5 border border-slate-100">
                            ${reqsHtml}
                        </div>
                        ${blocksHtml}
                    </div>
                </div>
            `;

            lucide.createIcons();
        });
    }

    function closeDriverDetails() {
        document.getElementById('driverDetailsModalContainer').classList.add('scale-95');
        setTimeout(() => {
            document.getElementById('driverDetailsModal').classList.add('hidden');
        }, 150);
    }

    document.querySelectorAll('.driver-tab').forEach(tab => {
        tab.addEventListener('click', () => {
            document.querySelectorAll('.driver-tab').forEach(t => {
                t.classList.remove('border-blue-500', 'text-blue-600', 'active');
                t.classList.add('border-transparent', 'text-slate-400');
            });
            document.querySelectorAll('.driver-tab-panel').forEach(p => p.classList.add('hidden'));
            tab.classList.add('border-blue-500', 'text-blue-600', 'active');
            const panel = document.querySelector(`.driver-tab-panel[data-tab-panel="${tab.dataset.tab}"]`);
            if (panel) panel.classList.remove('hidden');
        });
    });

    let searchTimer;
    const searchInput = document.getElementById('tableSearchInput');
    const statusFilter = document.querySelector('select[name="status"]');
    const sortFilter = document.querySelector('select[name="sort"]');
    const tableContainer = document.getElementById('driversTableContainer');

    function performSearch(page = 1) {
        const query = searchInput.value;
        const status = statusFilter.value;
        const sort = sortFilter.value;

        tableContainer.style.opacity = '0.5';
        tableContainer.style.pointerEvents = 'none';

        fetch(`{{ route('driver-management.index') }}?search=${encodeURIComponent(query)}&status=${status}&sort=${sort}&page=${page}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(response => response.text())
        .then(html => {
            tableContainer.innerHTML = html;
            tableContainer.style.opacity = '1';
            tableContainer.style.pointerEvents = 'auto';
            if (typeof lucide !== 'undefined') lucide.createIcons();
            if (typeof window.adjustMobileTableColumns === 'function') window.adjustMobileTableColumns();
        });
    }

    if (searchInput) {
        searchInput.addEventListener('input', () => {
            clearTimeout(searchTimer);
            searchTimer = setTimeout(() => performSearch(1), 300);
        });
    }
    if (statusFilter) statusFilter.addEventListener('change', () => performSearch(1));
    if (sortFilter) sortFilter.addEventListener('change', () => performSearch(1));

    window.changePage = function(page) {
        performSearch(page);
        window.scrollTo({ top: 0, behavior: 'smooth' });
    };

    let showingHistory = false;

    function toggleDebtHistory() {
        showingHistory = !showingHistory;
        const activeContent = document.getElementById('pendingDebtsContent');
        const historyContent = document.getElementById('debtHistoryContent');
        const toggleBtn = document.getElementById('historyToggleBtn');
        const modalTitle = document.getElementById('modalTitle');
        const modalSubtitle = document.getElementById('modalSubtitle');
        const modalIcon = document.getElementById('modalIcon');

        if (showingHistory) {
            activeContent.classList.add('hidden');
            historyContent.classList.remove('hidden');
            toggleBtn.innerHTML = '<i data-lucide="arrow-left" class="w-4 h-4"></i> Back to Debts';
            modalTitle.textContent = 'Transaction History';
            modalSubtitle.textContent = 'Settled Records & Payments';
            modalIcon.classList.remove('text-red-400');
            modalIcon.classList.add('text-emerald-400');
            fetchDebtHistory();
        } else {
            activeContent.classList.remove('hidden');
            historyContent.classList.add('hidden');
            toggleBtn.innerHTML = '<i data-lucide="history" class="w-4 h-4"></i> View History';
            modalTitle.textContent = 'Pending Driver Debts';
            modalSubtitle.textContent = 'Accident Charge Management';
            modalIcon.classList.remove('text-emerald-400');
            modalIcon.classList.add('text-red-400');
        }
        lucide.createIcons();
    }

    function fetchDebtHistory() {
        const historyContent = document.getElementById('debtHistoryContent');
        historyContent.innerHTML = `
            <div class="flex flex-col items-center justify-center py-20 text-slate-400">
                <div class="relative w-16 h-16 mb-4">
                    <div class="absolute inset-0 border-4 border-slate-100 rounded-full"></div>
                    <div class="absolute inset-0 border-4 border-emerald-500 rounded-full border-t-transparent animate-spin"></div>
                </div>
                <p class="font-bold text-sm tracking-widest uppercase text-emerald-600">Reconstructing Financial Logs...</p>
            </div>`;
        lucide.createIcons();

        fetch('{{ route('driver-management.debt-history') }}', {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
        .then(r => r.json())
        .then(data => {
            if (!data.success) throw new Error('Failed to load history');

            let settledHtml = '';
            if (data.settled.length > 0) {
                data.settled.forEach(item => {
                    settledHtml += `
                        <div class="bg-emerald-50/50 border border-emerald-100 rounded-2xl p-5 flex flex-col md:flex-row justify-between items-center gap-4">
                            <div class="flex items-center gap-4 flex-1">
                                <div class="w-12 h-12 bg-emerald-100 text-emerald-600 rounded-xl flex items-center justify-center shrink-0">
                                    <i data-lucide="check-circle" class="w-6 h-6"></i>
                                </div>
                                <div>
                                    <h5 class="text-sm font-black text-slate-900">${item.driver_name} <span class="text-xs font-bold text-slate-400">• ${item.unit_plate}</span></h5>
                                    <p class="text-[10px] font-bold text-slate-500 mt-0.5">${item.description}</p>
                                    <p class="text-[9px] font-black uppercase tracking-widest text-emerald-600 mt-1">Settled on ${new Date(item.date).toLocaleDateString('en-PH')}</p>
                                </div>
                            </div>
                            <div class="text-right shrink-0">
                                <p class="text-[9px] font-black text-emerald-400 uppercase tracking-widest mb-1">Total Paid</p>
                                <p class="text-xl font-black text-emerald-700">₱${parseFloat(item.total_charge).toLocaleString('en-PH', {minimumFractionDigits:2})}</p>
                            </div>
                        </div>
                    `;
                });
            } else {
                settledHtml = '<p class="text-sm text-slate-400 italic text-center py-4">No settled debts recorded yet.</p>';
            }

            let paymentsHtml = '';
            if (data.payments.length > 0) {
                data.payments.forEach(p => {
                    paymentsHtml += `
                        <div class="bg-white border border-slate-100 rounded-xl p-4 hover:border-indigo-100 hover:shadow-lg transition-all duration-300">
                            <div class="flex justify-between items-center">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 bg-indigo-50 text-indigo-600 rounded-lg flex items-center justify-center">
                                        <i data-lucide="banknote" class="w-4 h-4"></i>
                                    </div>
                                    <div>
                                        <p class="text-[10px] font-black text-indigo-500 uppercase tracking-widest">${new Date(p.date).toLocaleDateString('en-PH', {month:'short', day:'numeric', year:'numeric'})}</p>
                                        <p class="text-xs font-bold text-slate-700">${p.description}</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-black text-emerald-600">+₱${parseFloat(p.amount).toLocaleString('en-PH', {minimumFractionDigits:2})}</p>
                                    <p class="text-[9px] font-bold text-slate-400 uppercase">Cash Entry</p>
                                </div>
                            </div>
                        </div>
                    `;
                });
            } else {
                paymentsHtml = '<p class="text-sm text-slate-400 italic text-center py-4">No recent payment transactions found.</p>';
            }

            historyContent.innerHTML = `
                <div class="space-y-8">
                    <section>
                        <div class="flex items-center gap-3 mb-4">
                            <div class="h-px bg-slate-100 flex-1"></div>
                            <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Recently Settled Incidents</h4>
                            <div class="h-px bg-slate-100 flex-1"></div>
                        </div>
                        <div class="space-y-4">
                            ${settledHtml}
                        </div>
                    </section>

                    <section>
                        <div class="flex items-center gap-3 mb-4">
                            <div class="h-px bg-slate-100 flex-1"></div>
                            <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Individual Payment Logs</h4>
                            <div class="h-px bg-slate-100 flex-1"></div>
                        </div>
                        <div class="grid grid-cols-1 gap-3">
                            ${paymentsHtml}
                        </div>
                    </section>
                </div>
            `;
            lucide.createIcons();
        })
        .catch(err => {
            console.error(err);
            historyContent.innerHTML = `<div class="text-center py-10 text-rose-500 font-bold">Failed to load history.</div>`;
        });
    }

    function openPendingDebtsModal() {
        const modal = document.getElementById('pendingDebtsModal');
        modal.classList.remove('hidden');
        
        // Reset to debts view if it was on history
        showingHistory = true;
        toggleDebtHistory();

        setTimeout(() => {
            document.getElementById('pendingDebtsModalContainer').classList.remove('scale-95');
        }, 10);
        lucide.createIcons();
        fetchPendingDebts();
    }

    function closePendingDebtsModal() {
        document.getElementById('pendingDebtsModalContainer').classList.add('scale-95');
        setTimeout(() => {
            document.getElementById('pendingDebtsModal').classList.add('hidden');
        }, 150);
    }

    function fetchPendingDebts() {
        const content = document.getElementById('pendingDebtsContent');
        content.innerHTML = `
            <div class="flex flex-col items-center justify-center py-20 text-slate-400">
                <div class="relative w-16 h-16 mb-4">
                    <div class="absolute inset-0 border-4 border-slate-100 rounded-full"></div>
                    <div class="absolute inset-0 border-4 border-red-500 rounded-full border-t-transparent animate-spin"></div>
                </div>
                <p class="font-bold text-sm tracking-widest uppercase">Processing Telemetry...</p>
            </div>`;
        lucide.createIcons();

        fetch('{{ route('driver-management.pending-debts') }}', {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
        .then(r => r.json())
        .then(data => {
            if (!data.success || !data.debts || data.debts.length === 0) {
                content.innerHTML = `
                    <div class="flex flex-col items-center justify-center py-20 bg-slate-50 rounded-[2.5rem] border border-dashed border-slate-200">
                        <div class="w-20 h-20 bg-emerald-50 text-emerald-500 rounded-3xl flex items-center justify-center mb-6 shadow-xl shadow-emerald-100">
                            <i data-lucide="check-circle" class="w-10 h-10"></i>
                        </div>
                        <h4 class="text-xl font-black text-slate-800 mb-2">Zero Liabilities Detected</h4>
                        <p class="text-sm text-slate-500 max-w-xs text-center font-medium leading-relaxed">All driver at-fault accident charges have been fully settled and reconciled.</p>
                    </div>`;
                lucide.createIcons();
                return;
            }

            let html = '<div class="space-y-8 pb-10">';
            data.debts.forEach(driver => {
                let rows = '';
                driver.debts.forEach(debt => {
                    const severityColors = {
                        critical: 'bg-red-600 text-white shadow-red-100',
                        high: 'bg-orange-500 text-white shadow-orange-100',
                        medium: 'bg-amber-400 text-white shadow-amber-100',
                        low: 'bg-indigo-500 text-white shadow-indigo-100'
                    };
                    const badgeClass = severityColors[debt.severity.toLowerCase()] || 'bg-slate-500 text-white';

                    rows += `
                        <div class="group relative bg-white border border-slate-100 rounded-2xl p-5 hover:border-red-200 hover:shadow-xl hover:shadow-red-50 transition-all duration-300">
                            <div class="flex flex-col md:flex-row gap-6 items-start md:items-center">
                                <div class="w-full md:w-32 shrink-0">
                                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Incident Date & Time</p>
                                    <p class="text-sm font-black text-slate-800">${new Date(debt.timestamp || debt.date).toLocaleDateString('en-PH', {month:'short', day:'numeric', year:'numeric'})}</p>
                                    <p class="text-[10px] font-bold text-slate-400 mt-0.5 uppercase tracking-widest">${new Date(debt.timestamp || debt.date).toLocaleTimeString('en-PH', { hour: '2-digit', minute: '2-digit', hour12: true })}</p>
                                    <span class="inline-block mt-2 px-2 py-0.5 rounded-lg text-[9px] font-black uppercase tracking-widest shadow-lg ${badgeClass}">
                                        ${debt.severity} Risk
                                    </span>
                                </div>

                                <div class="flex-1 min-w-0">
                                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Details & Description</p>
                                    <p class="text-sm font-bold text-slate-700 leading-relaxed mb-3 line-clamp-2" title="${debt.description}">
                                        ${debt.description}
                                    </p>
                                    <div class="flex flex-wrap gap-4">
                                        <div class="px-3 py-1.5 bg-slate-50 rounded-xl border border-slate-100">
                                            <span class="text-[9px] font-black text-slate-400 uppercase block tracking-tighter">Total Charge</span>
                                            <span class="text-xs font-black text-slate-700">₱${parseFloat(debt.total_charge).toLocaleString('en-PH', {minimumFractionDigits:2})}</span>
                                        </div>
                                        <div class="px-3 py-1.5 bg-emerald-50 rounded-xl border border-emerald-100">
                                            <span class="text-[9px] font-black text-emerald-400 uppercase block tracking-tighter">Settled</span>
                                            <span class="text-xs font-black text-emerald-700">₱${parseFloat(debt.total_paid).toLocaleString('en-PH', {minimumFractionDigits:2})}</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="w-full md:w-auto flex flex-col items-end gap-3 shrink-0">
                                    <div class="text-right">
                                        <p class="text-[10px] font-black text-red-400 uppercase tracking-widest mb-1">Remaining Balance</p>
                                        <p class="text-2xl font-black text-red-600 tracking-tight">₱${parseFloat(debt.remaining_balance).toLocaleString('en-PH', {minimumFractionDigits:2})}</p>
                                    </div>
                                    
                                    <form method="POST" action="{{ route('driver-management.pay-debt') }}" class="flex items-center gap-2 w-full md:w-auto" 
                                        onsubmit="const amt=parseFloat(this.payment_amount.value); const bal=parseFloat('${debt.remaining_balance}'); if(amt > bal){ alert('Bawal lumampas sa balance (₱' + bal.toLocaleString() + ')'); return false; } return confirm('Confirm cash payment of ₱' + amt.toLocaleString() + ' for this incident?');">
                                        @csrf
                                        <input type="hidden" name="debt_id" value="${debt.id}">
                                        <div class="relative flex-1 md:w-32">
                                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs font-black">₱</span>
                                            <input type="number" name="payment_amount" step="0.01" max="${debt.remaining_balance}" min="0.01" required placeholder="0.00"
                                                oninput="const bal=parseFloat('${debt.remaining_balance}'); if(parseFloat(this.value) > bal) this.value = bal;"
                                                class="w-full pl-6 pr-3 py-2 text-sm font-black border border-slate-200 rounded-xl focus:ring-2 focus:ring-red-500/20 focus:border-red-500 transition-all outline-none bg-slate-50">
                                        </div>
                                        <button type="submit" class="px-5 py-2 bg-slate-900 text-white text-xs font-black rounded-xl hover:bg-slate-800 transition-all flex items-center gap-2 shadow-xl shadow-slate-200">
                                            <i data-lucide="banknote" class="w-4 h-4 text-emerald-400"></i> Pay
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    `;
                });

                html += `
                    <div class="bg-slate-900 rounded-[2.5rem] p-1 shadow-2xl relative overflow-hidden group">
                        <div class="absolute -right-10 -top-10 w-40 h-40 bg-red-500/10 rounded-full blur-3xl group-hover:scale-150 transition-transform duration-1000"></div>
                        <div class="bg-white rounded-[2.3rem] overflow-hidden">
                            <div class="bg-gradient-to-br from-slate-50 to-white p-8 border-b border-slate-100 flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
                                <div class="flex items-center gap-4">
                                    <div class="w-14 h-14 bg-slate-100 rounded-2xl flex items-center justify-center shadow-inner relative">
                                        <i data-lucide="user" class="w-7 h-7 text-slate-400"></i>
                                        <div class="absolute -bottom-1 -right-1 w-5 h-5 bg-red-500 rounded-full border-4 border-white flex items-center justify-center">
                                            <div class="w-1.5 h-1.5 bg-white rounded-full animate-pulse"></div>
                                        </div>
                                    </div>
                                    <div>
                                        <h4 class="text-xl font-black text-slate-900">${driver.driver_name}</h4>
                                        <div class="flex items-center gap-3 mt-1">
                                            <span class="px-2 py-0.5 bg-indigo-50 text-indigo-600 rounded-lg text-[10px] font-black uppercase tracking-widest border border-indigo-100">
                                                ${driver.unit_plate || 'Unassigned'}
                                            </span>
                                            <span class="text-xs font-bold text-slate-400">• Total Liability</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="text-left md:text-right w-full md:w-auto bg-red-50 p-4 md:p-0 md:bg-transparent rounded-2xl border border-red-100 md:border-0">
                                    <p class="text-[10px] font-black text-red-400 uppercase tracking-widest mb-1">Aggregated Pending Balance</p>
                                    <p class="text-3xl font-black text-red-600 tracking-tighter">₱${parseFloat(driver.total_remaining).toLocaleString('en-PH', {minimumFractionDigits:2})}</p>
                                </div>
                            </div>
                            <div class="p-6 bg-white space-y-4">
                                ${rows}
                            </div>
                        </div>
                    </div>
                `;
            });
            html += '</div>';
            content.innerHTML = html;
            lucide.createIcons();
        })
        .catch(err => {
            console.error(err);
            content.innerHTML = `
                <div class="flex flex-col items-center justify-center py-20 text-rose-500">
                    <div class="w-20 h-20 bg-rose-50 rounded-3xl flex items-center justify-center mb-6">
                        <i data-lucide="alert-triangle" class="w-10 h-10"></i>
                    </div>
                    <h4 class="text-xl font-black mb-2">Protocol Interrupted</h4>
                    <p class="text-sm text-slate-500 text-center max-w-xs font-medium leading-relaxed mb-6">Unable to sync debt telemetry with the central server.</p>
                    <button onclick="fetchPendingDebts()" class="px-8 py-3 bg-slate-900 text-white rounded-xl font-black text-xs">Retry Protocol</button>
                </div>`;
            lucide.createIcons();
        });
    }

    // Handle URL parameters for notifications
    window.addEventListener('DOMContentLoaded', () => {
        const urlParams = new URLSearchParams(window.location.search);
        const editDriverId = urlParams.get('edit_driver');
        const openDebts = urlParams.get('open_debts');

        if (editDriverId) {
            setTimeout(() => {
                openEditDriverModal(editDriverId);
            }, 500);
        }

        if (openDebts) {
            setTimeout(() => {
                openPendingDebtsModal();
            }, 500);
        }
    });
</script>
@endsection