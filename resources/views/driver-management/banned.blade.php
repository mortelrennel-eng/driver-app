@extends('layouts.app')

@section('title', 'Banned Drivers Roster - Euro System')
@section('page-heading', 'Banned Drivers Roster')
@section('page-subheading', 'Manage drivers under lock-out or administrative suspension')

@section('content')
<style>
    .banned-profile-card {
        transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .banned-profile-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 20px 25px -5px rgba(239, 68, 68, 0.08), 0 10px 10px -5px rgba(239, 68, 68, 0.04);
    }
    .fade-out-card {
        opacity: 0;
        transform: scale(0.9) translateY(20px);
        transition: all 0.5s ease;
    }
    /* Searchable select styling */
    .driver-search-item {
        transition: background 0.15s;
    }
    .driver-search-item:hover, .driver-search-item.active {
        background: #f1f5f9;
    }
    #driverDropdown {
        max-height: 220px;
        overflow-y: auto;
    }
    #driverDropdown::-webkit-scrollbar { width: 4px; }
    #driverDropdown::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 2px; }

    /* Modal animation */
    #addBanSuspendModal .modal-box {
        transform: scale(0.95) translateY(10px);
        transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
    }
    #addBanSuspendModal.open .modal-box {
        transform: scale(1) translateY(0);
    }
</style>

<div class="space-y-8">
    {{-- ── Premium Header ── --}}
    <div class="relative bg-slate-900 rounded-[2.5rem] p-8 overflow-hidden shadow-2xl border border-red-500/10">
        <div class="absolute -right-20 -top-20 w-80 h-80 bg-red-600/10 rounded-full blur-[100px] pointer-events-none"></div>
        <div class="absolute -left-20 -bottom-20 w-60 h-60 bg-yellow-500/5 rounded-full blur-[80px] pointer-events-none"></div>
        
        <div class="relative flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
            <div class="flex items-center gap-5">
                <div class="w-16 h-16 bg-gradient-to-br from-red-500 to-amber-600 rounded-3xl flex items-center justify-center shadow-xl shadow-red-500/20">
                    <i data-lucide="shield-alert" class="w-8 h-8 text-white animate-pulse"></i>
                </div>
                <div>
                    <h3 class="text-2xl font-black text-white tracking-tight">Suspended Fleet Drivers</h3>
                    <p class="text-sm text-slate-400 mt-1 font-medium max-w-xl leading-relaxed">
                        Drivers blocked due to critical incidents or placed on administrative lock-out. Use the button to add a new suspension or ban.
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-4 shrink-0">
                <div class="flex flex-col text-left md:text-right bg-red-500/5 px-6 py-4 rounded-2xl border border-red-500/10">
                    <span class="text-[10px] font-black text-red-400 uppercase tracking-widest block mb-0.5">Total Lockouts</span>
                    <span id="banned-count-badge" class="text-4xl font-black text-red-500 tracking-tighter">{{ count($bannedDrivers) }}</span>
                </div>
                {{-- ADD BAN/SUSPEND BUTTON --}}
                <button type="button" onclick="openAddBanModal()"
                    class="flex items-center gap-2.5 px-6 py-4 bg-gradient-to-br from-red-600 to-rose-700 hover:from-red-500 hover:to-rose-600 text-white font-black text-xs uppercase tracking-widest rounded-2xl shadow-xl shadow-red-500/30 transition-all active:scale-95">
                    <i data-lucide="shield-plus" class="w-5 h-5"></i>
                    <span class="hidden sm:inline">Add Ban / Suspend</span>
                </button>
            </div>
        </div>
    </div>

    {{-- ── Controls Bar ── --}}
    <div class="flex flex-col lg:flex-row gap-4 justify-between items-center bg-white p-4 rounded-2xl border border-gray-100 shadow-sm">
        {{-- Search --}}
        <div class="relative w-full lg:max-w-xs">
            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">
                <i data-lucide="search" class="w-4 h-4"></i>
            </span>
            <input type="text" id="bannedSearchInput" placeholder="Search suspended drivers..."
                   autocomplete="new-password" spellcheck="false" autocorrect="off" autocapitalize="off"
                   readonly onfocus="this.removeAttribute('readonly');"
                   class="w-full pl-11 pr-4 py-3 text-sm border-2 border-gray-100 rounded-xl focus:border-red-500/30 focus:ring-4 focus:ring-red-500/5 transition-all outline-none bg-slate-50/50">
        </div>

        {{-- Filter Buttons --}}
        <div class="flex items-center gap-2 w-full lg:w-auto overflow-x-auto py-1 scrollbar-none">
            <button type="button" data-filter-status="all" onclick="setStatusFilter('all')"
                class="status-filter-btn px-4 py-2.5 bg-slate-900 text-white text-xs font-black rounded-xl transition-all shadow-md">
                All Lockouts
            </button>
            <button type="button" data-filter-status="banned" onclick="setStatusFilter('banned')"
                class="status-filter-btn px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition-all">
                Banned Only
            </button>
            <button type="button" data-filter-status="suspended" onclick="setStatusFilter('suspended')"
                class="status-filter-btn px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition-all">
                Suspended Only
            </button>
        </div>

        <div class="flex items-center gap-3 w-full lg:w-auto shrink-0 justify-end">
            <a href="{{ route('driver-management.index') }}"
               class="flex items-center justify-center gap-2 px-5 py-3 bg-slate-900 text-white text-xs font-black rounded-xl hover:bg-slate-800 transition-all active:scale-95 shadow-lg shadow-slate-200 w-full lg:w-auto">
                <i data-lucide="arrow-left" class="w-4 h-4"></i> Back to Roster
            </a>
        </div>
    </div>

    {{-- ── Banned Drivers Grid ── --}}
    <div id="bannedDriversGrid" class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @forelse($bannedDrivers as $driver)
            <div class="banned-profile-card bg-white rounded-[2rem] border border-gray-100 shadow-sm overflow-hidden flex flex-col cursor-pointer"
                 id="driver-card-{{ $driver->id }}"
                 data-status="{{ $driver->driver_status }}"
                 data-search-terms="{{ strtolower($driver->full_name . ' ' . $driver->license_number . ' ' . $driver->contact_number) }}"
                 onclick="openDriverDetails({{ $driver->id }})">
                
                {{-- Card Header --}}
                <div class="p-6 border-b border-gray-50 flex items-start gap-4 bg-slate-50/50">
                    <div class="w-14 h-14 bg-gradient-to-br {{ $driver->driver_status === 'suspended' ? 'from-amber-500 to-orange-600 shadow-amber-500/10' : 'from-red-500 to-rose-600 shadow-red-500/10' }} rounded-2xl flex items-center justify-center text-white text-lg font-black shrink-0 shadow-lg">
                        {{ substr($driver->first_name, 0, 1) }}{{ substr($driver->last_name, 0, 1) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 flex-wrap">
                            <h4 class="text-base font-black text-slate-900 truncate">{{ $driver->full_name }}</h4>
                            @if($driver->driver_status === 'suspended')
                                <span class="px-2 py-0.5 bg-amber-100 text-amber-700 rounded-md text-[9px] font-black uppercase tracking-widest border border-amber-200">Suspended</span>
                            @else
                                <span class="px-2 py-0.5 bg-red-100 text-red-700 rounded-md text-[9px] font-black uppercase tracking-widest border border-red-200">Banned</span>
                            @endif
                        </div>
                        <p class="text-[10px] text-slate-400 mt-1 font-bold">REG KEY: DRV-{{ str_pad($driver->id, 4, '0', STR_PAD_LEFT) }}</p>
                        @if($driver->driver_status === 'suspended')
                            <p class="text-[10px] text-amber-600 mt-1 font-black flex items-center gap-1">
                                <i data-lucide="clock" class="w-3.5 h-3.5 animate-pulse"></i>
                                SUSPENSION: {{ $driver->days_left ?? 0 }} DAYS REMAINING
                            </p>
                        @endif
                    </div>
                </div>

                {{-- Card Content --}}
                <div class="p-6 space-y-4 flex-1">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-0.5">
                            <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest block">License</span>
                            <span class="text-xs font-black text-slate-800 font-mono tracking-wider">{{ $driver->license_number ?? 'N/A' }}</span>
                        </div>
                        <div class="space-y-0.5">
                            <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest block">Contact</span>
                            <span class="text-xs font-black text-slate-800">{{ $driver->contact_number ?? 'N/A' }}</span>
                        </div>
                        <div class="space-y-0.5">
                            <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest block">Hire Date</span>
                            <span class="text-xs font-bold text-slate-600">{{ $driver->hire_date ? \Carbon\Carbon::parse($driver->hire_date)->format('M d, Y') : 'N/A' }}</span>
                        </div>
                        <div class="space-y-0.5">
                            <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest block">Registrar</span>
                            <span class="text-xs font-bold text-slate-600 uppercase">{{ $driver->creator_name ?? 'System' }}</span>
                        </div>
                    </div>

                    @if(!empty($driver->suspension_reason))
                        <div class="pt-3 border-t border-gray-50 space-y-0.5">
                            <span class="text-[9px] font-black text-red-400 uppercase tracking-widest block">Reason / Description</span>
                            <p class="text-xs font-bold text-slate-700 leading-relaxed italic">"{{ $driver->suspension_reason }}"</p>
                        </div>
                    @elseif(!empty($driver->address))
                        <div class="pt-3 border-t border-gray-50 space-y-0.5">
                            <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest block">Address</span>
                            <span class="text-xs font-bold text-slate-600 leading-relaxed block">{{ $driver->address }}</span>
                        </div>
                    @endif

                    {{-- Ban Incidents --}}
                    @if(count($driver->ban_incidents) > 0)
                        <div class="pt-4 border-t border-gray-50">
                            <h5 class="text-[10px] font-black text-red-500 uppercase tracking-widest mb-2 flex items-center gap-1.5">
                                <i data-lucide="alert-circle" class="w-3.5 h-3.5"></i> Ban Triggers ({{ count($driver->ban_incidents) }} incident(s))
                            </h5>
                            <div class="space-y-2 max-h-32 overflow-y-auto custom-scrollbar">
                                @foreach($driver->ban_incidents as $inc)
                                    <div class="p-2.5 bg-red-50/50 rounded-xl border border-red-100/50 flex flex-col gap-1">
                                        <div class="flex justify-between items-center gap-2">
                                            <span class="text-[10px] font-black text-red-800">{{ $inc->incident_type }}</span>
                                            <span class="text-[8px] font-black uppercase tracking-widest px-1.5 py-0.5 bg-red-200/50 text-red-700 rounded">{{ $inc->severity }}</span>
                                        </div>
                                        <p class="text-[10px] text-slate-600 leading-relaxed italic break-words">"{{ $inc->description }}"</p>
                                        <span class="text-[8px] text-slate-400 font-bold block text-right">{{ \Carbon\Carbon::parse($inc->incident_date)->format('M d, Y') }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <div class="pt-4 border-t border-gray-50 flex items-center gap-2 text-slate-400 text-xs font-bold italic py-2">
                            <i data-lucide="info" class="w-4 h-4"></i> No behavioral incidents logged.
                        </div>
                    @endif
                </div>

                {{-- Action Footer --}}
                <div class="p-5 border-t border-gray-50 bg-slate-50 flex justify-between items-center gap-3 relative z-50" onclick="event.stopPropagation()">
                    {{-- Re-Suspend Button (for banned drivers) / Extend Suspension (for suspended) --}}
                    <button type="button"
                        class="modify-suspension-btn px-4 py-2.5 bg-amber-50 hover:bg-amber-100 text-amber-700 text-xs font-black rounded-xl transition-all flex items-center gap-2 border border-amber-200 hover:border-amber-300 active:scale-95 cursor-pointer relative z-50"
                        data-id="{{ $driver->id }}"
                        data-name="{{ $driver->full_name }}"
                        data-status="{{ $driver->driver_status }}">
                        <i data-lucide="shield-alert" class="w-4 h-4 pointer-events-none"></i>
                        {{ $driver->driver_status === 'suspended' ? 'Modify' : 'Re-Suspend' }}
                    </button>
                    <button type="button"
                            class="restore-driver-btn px-5 py-2.5 bg-slate-900 hover:bg-slate-800 text-white text-xs font-black rounded-xl transition-all flex items-center gap-2 shadow-md hover:shadow-lg active:scale-95 cursor-pointer relative z-50"
                            data-id="{{ $driver->id }}"
                            data-name="{{ $driver->full_name }}">
                        <i data-lucide="shield-check" class="w-4 h-4 pointer-events-none text-emerald-400"></i> Restore Driver
                    </button>
                </div>
            </div>
        @empty
            <div id="empty-state-card" class="col-span-1 md:col-span-2 flex flex-col items-center justify-center py-24 bg-slate-50 rounded-[2.5rem] border border-dashed border-slate-200">
                <div class="w-20 h-20 bg-emerald-50 text-emerald-500 rounded-3xl flex items-center justify-center mb-6 shadow-xl shadow-emerald-100">
                    <i data-lucide="shield-check" class="w-10 h-10 animate-bounce"></i>
                </div>
                <h4 class="text-xl font-black text-slate-800 mb-2">No Suspended Drivers</h4>
                <p class="text-sm text-slate-500 max-w-xs text-center font-medium leading-relaxed">
                    All drivers are currently active and ready for dispatch.
                </p>
            </div>
        @endforelse
    </div>

    {{-- No Search Results --}}
    <div id="noSearchResultsCard" class="hidden flex-col items-center justify-center py-20 bg-slate-50 rounded-[2.5rem] border border-dashed border-slate-200">
        <div class="w-16 h-16 bg-slate-100 text-slate-400 rounded-full flex items-center justify-center mb-4">
            <i data-lucide="search-code" class="w-8 h-8"></i>
        </div>
        <h4 class="text-base font-black text-slate-800 mb-1">No Matching Drivers Found</h4>
        <p class="text-xs text-slate-400">Try a different name, license, or contact number.</p>
    </div>
</div>

{{-- ════════════════════════════════════════════════════════
     ADD NEW BAN / SUSPENSION MODAL
════════════════════════════════════════════════════════ --}}
<div id="addBanSuspendModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm hidden z-[9999] flex items-center justify-center p-4">
    <div class="modal-box relative bg-white rounded-[2rem] shadow-2xl w-full max-w-lg overflow-hidden flex flex-col">
        
        {{-- Modal Header --}}
        <div class="bg-gradient-to-r from-slate-900 to-slate-800 p-6 shrink-0">
            <div class="flex justify-between items-center">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-red-500/20 rounded-2xl flex items-center justify-center">
                        <i data-lucide="shield-ban" class="w-6 h-6 text-red-400"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-black text-white uppercase tracking-wide">Add Suspension / Ban</h3>
                        <p class="text-[11px] font-bold text-slate-400 mt-0.5 uppercase tracking-widest">Administrative Lock-Out Action</p>
                    </div>
                </div>
                <button type="button" onclick="closeAddBanModal()" class="text-slate-400 hover:text-white bg-slate-700/50 hover:bg-slate-700 p-2 rounded-full transition-colors">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
        </div>

        {{-- Form --}}
        <form id="addBanSuspendForm" onsubmit="submitAddBanSuspend(event)" class="p-7 space-y-6 overflow-y-auto">
            
            {{-- STEP 1: Select Driver --}}
            <div class="space-y-2">
                <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest">
                    Select Driver <span class="text-red-500">*</span>
                </label>
                {{-- Searchable Driver Input --}}
                <div class="relative" id="driverSelectContainer">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <i data-lucide="user-search" class="w-4 h-4 text-slate-400"></i>
                    </div>
                    <input type="text" id="driverSearchInput"
                        placeholder="Type driver name to search..."
                        autocomplete="off"
                        oninput="filterDriverList(this.value)"
                        onfocus="showDriverDropdown()"
                        class="w-full pl-11 pr-4 py-3.5 border-2 border-slate-100 rounded-xl focus:border-red-400/50 focus:ring-4 focus:ring-red-500/5 transition-all outline-none bg-slate-50/50 font-bold text-sm text-slate-700">
                    <input type="hidden" id="selectedDriverId" value="">
                    {{-- Dropdown --}}
                    <div id="driverDropdown" class="absolute z-50 w-full mt-1 bg-white rounded-xl border border-slate-100 shadow-2xl hidden">
                        @forelse($activeDrivers as $d)
                            <div class="driver-search-item px-4 py-3 cursor-pointer flex items-center gap-3"
                                 data-id="{{ $d->id }}"
                                 data-name="{{ $d->full_name }}"
                                 data-status="{{ $d->driver_status }}"
                                 onclick="selectDriver({{ $d->id }}, '{{ addslashes($d->full_name) }}', '{{ $d->driver_status }}')">
                                <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center text-blue-600 text-xs font-black shrink-0">
                                    {{ substr($d->first_name, 0, 1) }}{{ substr($d->last_name, 0, 1) }}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-black text-slate-800 truncate">{{ $d->full_name }}</p>
                                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ ucfirst($d->driver_status) }}</p>
                                </div>
                            </div>
                        @empty
                            <div class="px-4 py-4 text-center text-xs text-slate-400 font-bold">No active drivers found</div>
                        @endforelse
                    </div>
                </div>
                <p class="text-[10px] text-slate-400 font-bold">Only active (non-banned/suspended) drivers are listed.</p>
            </div>

            {{-- STEP 2: Action Type --}}
            <div class="space-y-2">
                <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest">
                    Action Type <span class="text-red-500">*</span>
                </label>
                <div class="grid grid-cols-2 gap-3">
                    <button type="button" id="btnSuspend" onclick="setActionType('suspend')"
                        class="action-type-btn relative flex flex-col items-center gap-2 px-4 py-4 border-2 border-amber-400 bg-amber-50 text-amber-700 rounded-2xl transition-all font-black text-xs uppercase tracking-widest shadow-sm">
                        <i data-lucide="clock" class="w-6 h-6"></i>
                        Temporary Suspension
                        <span class="text-[9px] font-bold normal-case tracking-normal text-amber-500">Limited days</span>
                        <div class="absolute top-2 right-2 w-4 h-4 bg-amber-400 rounded-full flex items-center justify-center" id="checkSuspend">
                            <i data-lucide="check" class="w-2.5 h-2.5 text-white"></i>
                        </div>
                    </button>
                    <button type="button" id="btnBan" onclick="setActionType('ban')"
                        class="action-type-btn relative flex flex-col items-center gap-2 px-4 py-4 border-2 border-slate-200 bg-slate-50 text-slate-500 rounded-2xl transition-all font-black text-xs uppercase tracking-widest">
                        <i data-lucide="ban" class="w-6 h-6"></i>
                        Permanent Ban
                        <span class="text-[9px] font-bold normal-case tracking-normal">Indefinite</span>
                        <div class="absolute top-2 right-2 w-4 h-4 bg-slate-200 rounded-full hidden items-center justify-center" id="checkBan">
                            <i data-lucide="check" class="w-2.5 h-2.5 text-white"></i>
                        </div>
                    </button>
                </div>
                <input type="hidden" id="addBanActionType" value="suspend">
            </div>

            {{-- STEP 3: Duration (suspend only) --}}
            <div id="durationSection" class="space-y-2">
                <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest">
                    Suspension Duration <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <i data-lucide="calendar-clock" class="w-4 h-4 text-slate-400"></i>
                    </div>
                    <input type="number" id="addBanDuration" min="1" max="365" value="7"
                        class="w-full pl-11 pr-4 py-3.5 border-2 border-slate-100 rounded-xl focus:border-amber-400/50 focus:ring-4 focus:ring-amber-500/5 transition-all outline-none bg-slate-50/50 font-black text-sm text-slate-800"
                        placeholder="Enter number of days (e.g. 7)">
                    <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none">
                        <span class="text-xs font-black text-slate-400">DAYS</span>
                    </div>
                </div>
                {{-- Quick Day Presets --}}
                <div class="flex flex-wrap gap-2 mt-2">
                    @foreach([3, 7, 14, 30, 60, 90] as $d)
                        <button type="button" onclick="document.getElementById('addBanDuration').value = {{ $d }}"
                            class="px-3 py-1.5 bg-slate-100 hover:bg-amber-100 hover:text-amber-700 text-slate-600 text-[10px] font-black rounded-lg transition-all border border-slate-200 hover:border-amber-300">
                            {{ $d }}d
                        </button>
                    @endforeach
                </div>
            </div>

            {{-- STEP 4: Description / Reason --}}
            <div class="space-y-2">
                <div class="flex justify-between items-center">
                    <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest">
                        Reason / Description <span class="text-red-500">*</span>
                    </label>
                    <span id="addReasonCount" class="text-[10px] font-bold text-slate-400">0 / 500</span>
                </div>
                <textarea id="addBanReason" rows="4"
                    placeholder="Provide a clear and detailed explanation for this administrative action. This will be recorded in the driver's history..."
                    required minlength="5" maxlength="500"
                    oninput="document.getElementById('addReasonCount').textContent = this.value.length + ' / 500'"
                    class="w-full px-4 py-3.5 border-2 border-slate-100 rounded-xl focus:border-red-400/50 focus:ring-4 focus:ring-red-500/5 transition-all outline-none bg-slate-50/50 font-medium text-sm text-slate-700 resize-none"></textarea>
            </div>

            {{-- Warning Note --}}
            <div class="bg-red-50 border border-red-100 rounded-2xl p-4 flex items-start gap-3">
                <i data-lucide="triangle-alert" class="w-5 h-5 text-red-500 shrink-0 mt-0.5 animate-pulse"></i>
                <div>
                    <p class="text-[10px] font-black text-red-700 uppercase tracking-widest">Important Notice</p>
                    <p class="text-[11px] text-red-600 font-semibold mt-1 leading-relaxed">
                        The driver will be automatically <strong>unassigned from their active vehicle unit</strong> and this action will be logged in the system activity records.
                    </p>
                </div>
            </div>

            {{-- Footer Buttons --}}
            <div class="flex justify-end gap-3 pt-2">
                <button type="button" onclick="closeAddBanModal()"
                    class="px-6 py-3 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-black rounded-xl transition-all">
                    Cancel
                </button>
                <button type="submit" id="addBanSubmitBtn"
                    class="px-8 py-3 bg-gradient-to-r from-red-600 to-rose-700 hover:from-red-500 hover:to-rose-600 text-white text-xs font-black rounded-xl shadow-lg shadow-red-200 transition-all active:scale-95 flex items-center gap-2">
                    <i data-lucide="shield-alert" class="w-4 h-4"></i>
                    <span id="addBanSubmitLabel">Apply Suspension</span>
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ════════════════════════════════════════════════════════
     MODIFY / RE-SUSPEND MODAL (for already-banned drivers)
════════════════════════════════════════════════════════ --}}
<div id="changeSuspensionModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm hidden z-[9999] flex items-center justify-center p-4">
    <div class="relative bg-white rounded-[2rem] shadow-2xl w-full max-w-md overflow-hidden">
        <div class="bg-gradient-to-r from-amber-600 to-orange-600 p-6">
            <div class="flex justify-between items-center">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center">
                        <i data-lucide="shield-alert" class="w-5 h-5 text-white"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-black text-white">Modify Lock-Out</h3>
                        <p id="changeSuspendSubtitle" class="text-[10px] text-amber-200 font-bold mt-0.5 uppercase tracking-widest">Driver Name</p>
                    </div>
                </div>
                <button type="button" onclick="closeChangeSuspensionModal()" class="text-white/60 hover:text-white p-1.5 rounded-full transition-colors">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>
        </div>

        <form id="changeSuspensionForm" onsubmit="submitChangeSuspension(event)" class="p-7 space-y-5">
            <input type="hidden" id="changeSuspendDriverId" value="">

            <div class="space-y-2">
                <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest">Action Type <span class="text-red-500">*</span></label>
                <select id="changeSuspendActionType" onchange="toggleChangeDuration()"
                    class="w-full px-4 py-3.5 border-2 border-slate-100 rounded-xl focus:border-amber-400/50 focus:ring-4 focus:ring-amber-500/5 transition-all outline-none bg-slate-50/50 font-bold text-sm text-slate-700">
                    <option value="suspend">Temporary Suspension</option>
                    <option value="ban">Permanent Ban</option>
                </select>
            </div>

            <div class="space-y-2" id="changeDurationSection">
                <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest">Duration (Days) <span class="text-red-500">*</span></label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <i data-lucide="calendar-clock" class="w-4 h-4 text-slate-400"></i>
                    </div>
                    <input type="number" id="changeSuspendDuration" min="1" max="365" value="7"
                        class="w-full pl-11 pr-14 py-3.5 border-2 border-slate-100 rounded-xl focus:border-amber-400/50 focus:ring-4 focus:ring-amber-500/5 transition-all outline-none bg-slate-50/50 font-black text-sm text-slate-800"
                        placeholder="e.g. 14">
                    <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none">
                        <span class="text-xs font-black text-slate-400">DAYS</span>
                    </div>
                </div>
                <div class="flex flex-wrap gap-2">
                    @foreach([3, 7, 14, 30, 60, 90] as $d)
                        <button type="button" onclick="document.getElementById('changeSuspendDuration').value = {{ $d }}"
                            class="px-3 py-1.5 bg-slate-100 hover:bg-amber-100 hover:text-amber-700 text-slate-600 text-[10px] font-black rounded-lg transition-all border border-slate-200">
                            {{ $d }}d
                        </button>
                    @endforeach
                </div>
            </div>

            <div class="space-y-2">
                <div class="flex justify-between items-center">
                    <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest">Reason / Description <span class="text-red-500">*</span></label>
                    <span id="changeReasonCount" class="text-[10px] font-bold text-slate-400">0 / 500</span>
                </div>
                <textarea id="changeSuspendReason" rows="3"
                    placeholder="Explain the reason for this modified administrative action..."
                    required minlength="5" maxlength="500"
                    oninput="document.getElementById('changeReasonCount').textContent = this.value.length + ' / 500'"
                    class="w-full px-4 py-3.5 border-2 border-slate-100 rounded-xl focus:border-amber-400/50 focus:ring-4 focus:ring-amber-500/5 transition-all outline-none bg-slate-50/50 font-medium text-sm text-slate-700 resize-none"></textarea>
            </div>

            <div class="flex justify-end gap-3 pt-2">
                <button type="button" onclick="closeChangeSuspensionModal()"
                    class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-black rounded-xl transition-all">Cancel</button>
                <button type="submit" id="changeSuspendSubmitBtn"
                    class="px-6 py-2.5 bg-amber-600 hover:bg-amber-700 text-white text-xs font-black rounded-xl shadow-md transition-all active:scale-95 flex items-center gap-2">
                    <i data-lucide="save" class="w-4 h-4"></i> Update Lock-Out
                </button>
            </div>
        </form>
    </div>
</div>

<script>
/* ═══════════════════════════════════════════════════════
   SEARCH & FILTER
═══════════════════════════════════════════════════════ */
let currentFilter = 'all';

function filterDrivers() {
    const query = document.getElementById('bannedSearchInput')?.value.trim().toLowerCase() || '';
    const cards = document.querySelectorAll('.banned-profile-card');
    const noResults = document.getElementById('noSearchResultsCard');
    let visibleCount = 0;
    cards.forEach(card => {
        const terms = card.getAttribute('data-search-terms') || '';
        const status = card.getAttribute('data-status') || '';
        const matchesSearch = terms.includes(query);
        const matchesStatus = (currentFilter === 'all' || status === currentFilter);
        if (matchesSearch && matchesStatus) { card.classList.remove('hidden'); visibleCount++; }
        else { card.classList.add('hidden'); }
    });
    if (visibleCount === 0 && cards.length > 0) {
        noResults.classList.remove('hidden'); noResults.classList.add('flex');
    } else {
        noResults.classList.add('hidden'); noResults.classList.remove('flex');
    }
}

function setStatusFilter(status) {
    currentFilter = status;
    document.querySelectorAll('.status-filter-btn').forEach(btn => {
        const btnStatus = btn.getAttribute('data-filter-status');
        btn.className = btnStatus === status
            ? 'status-filter-btn px-4 py-2.5 bg-slate-900 text-white text-xs font-black rounded-xl transition-all shadow-md'
            : 'status-filter-btn px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition-all';
    });
    filterDrivers();
}
document.getElementById('bannedSearchInput')?.addEventListener('input', filterDrivers);

/* ═══════════════════════════════════════════════════════
   RESTORE (UNBAN)
═══════════════════════════════════════════════════════ */
function performUnban(driverId, driverName) {
    if (!confirm('Are you sure you want to RESTORE ' + driverName + '?\nThis will set their status back to Available.')) return;
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    fetch(`/driver-management/${driverId}/unban`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            if (typeof showNotification === 'function') showNotification(data.message, 'success');
            else alert(data.message);
            const card = document.getElementById('driver-card-' + driverId);
            if (card) {
                card.classList.add('fade-out-card');
                setTimeout(() => {
                    card.remove();
                    const badge = document.getElementById('banned-count-badge');
                    if (badge) {
                        let curr = parseInt(badge.textContent) || 0;
                        badge.textContent = Math.max(0, curr - 1);
                    }
                }, 500);
            }
        } else { alert('Error: ' + data.message); }
    })
    .catch(err => { console.error(err); alert('Failed to restore driver. Please try again.'); });
}

/* ═══════════════════════════════════════════════════════
   ADD NEW BAN / SUSPEND MODAL
═══════════════════════════════════════════════════════ */
let currentActionType = 'suspend';

function openAddBanModal() {
    document.getElementById('driverSearchInput').value = '';
    document.getElementById('selectedDriverId').value = '';
    document.getElementById('addBanDuration').value = '7';
    document.getElementById('addBanReason').value = '';
    document.getElementById('addReasonCount').textContent = '0 / 500';
    setActionType('suspend');
    const modal = document.getElementById('addBanSuspendModal');
    modal.classList.remove('hidden');
    setTimeout(() => modal.classList.add('open'), 10);
    if (typeof lucide !== 'undefined') lucide.createIcons();
}

function closeAddBanModal() {
    const modal = document.getElementById('addBanSuspendModal');
    modal.classList.remove('open');
    setTimeout(() => modal.classList.add('hidden'), 250);
}

function setActionType(type) {
    currentActionType = type;
    document.getElementById('addBanActionType').value = type;
    const durationSection = document.getElementById('durationSection');
    const submitLabel = document.getElementById('addBanSubmitLabel');
    const btnSuspend = document.getElementById('btnSuspend');
    const btnBan = document.getElementById('btnBan');
    const checkSuspend = document.getElementById('checkSuspend');
    const checkBan = document.getElementById('checkBan');

    if (type === 'suspend') {
        durationSection.style.display = 'block';
        document.getElementById('addBanDuration').required = true;
        submitLabel.textContent = 'Apply Suspension';
        // Suspend active
        btnSuspend.className = 'action-type-btn relative flex flex-col items-center gap-2 px-4 py-4 border-2 border-amber-400 bg-amber-50 text-amber-700 rounded-2xl transition-all font-black text-xs uppercase tracking-widest shadow-sm';
        checkSuspend.className = 'absolute top-2 right-2 w-4 h-4 bg-amber-400 rounded-full flex items-center justify-center';
        // Ban inactive
        btnBan.className = 'action-type-btn relative flex flex-col items-center gap-2 px-4 py-4 border-2 border-slate-200 bg-slate-50 text-slate-400 rounded-2xl transition-all font-black text-xs uppercase tracking-widest';
        checkBan.className = 'absolute top-2 right-2 w-4 h-4 bg-slate-200 rounded-full hidden items-center justify-center';
    } else {
        durationSection.style.display = 'none';
        document.getElementById('addBanDuration').required = false;
        submitLabel.textContent = 'Apply Permanent Ban';
        // Ban active
        btnBan.className = 'action-type-btn relative flex flex-col items-center gap-2 px-4 py-4 border-2 border-red-500 bg-red-50 text-red-700 rounded-2xl transition-all font-black text-xs uppercase tracking-widest shadow-sm';
        checkBan.className = 'absolute top-2 right-2 w-4 h-4 bg-red-500 rounded-full flex items-center justify-center';
        // Suspend inactive
        btnSuspend.className = 'action-type-btn relative flex flex-col items-center gap-2 px-4 py-4 border-2 border-slate-200 bg-slate-50 text-slate-400 rounded-2xl transition-all font-black text-xs uppercase tracking-widest';
        checkSuspend.className = 'absolute top-2 right-2 w-4 h-4 bg-slate-200 rounded-full hidden items-center justify-center';
    }
}

/* Driver Search Dropdown */
function showDriverDropdown() {
    document.getElementById('driverDropdown').classList.remove('hidden');
    document.addEventListener('click', hideDriverDropdownOnOutside, true);
}
function hideDriverDropdownOnOutside(e) {
    const container = document.getElementById('driverSelectContainer');
    if (!container.contains(e.target)) {
        document.getElementById('driverDropdown').classList.add('hidden');
        document.removeEventListener('click', hideDriverDropdownOnOutside, true);
    }
}
function filterDriverList(query) {
    const items = document.querySelectorAll('.driver-search-item');
    const lq = query.toLowerCase();
    let visible = 0;
    items.forEach(item => {
        const name = (item.getAttribute('data-name') || '').toLowerCase();
        if (name.includes(lq)) { item.classList.remove('hidden'); visible++; }
        else { item.classList.add('hidden'); }
    });
    document.getElementById('driverDropdown').classList.remove('hidden');
}
function selectDriver(id, name, status) {
    document.getElementById('selectedDriverId').value = id;
    document.getElementById('driverSearchInput').value = name;
    document.getElementById('driverDropdown').classList.add('hidden');
}

function submitAddBanSuspend(event) {
    event.preventDefault();
    const driverId = document.getElementById('selectedDriverId').value;
    if (!driverId) { alert('Please select a driver first.'); return; }
    const actionType = document.getElementById('addBanActionType').value;
    const durationDays = document.getElementById('addBanDuration').value.trim();
    const reason = document.getElementById('addBanReason').value.trim();

    if (!reason || reason.length < 5) { alert('Please provide a reason (minimum 5 characters).'); return; }
    if (actionType === 'suspend') {
        const days = parseInt(durationDays, 10);
        if (isNaN(days) || days < 1 || days > 365) { alert('Suspension duration must be between 1 and 365 days.'); return; }
    }

    const confirmMsg = actionType === 'suspend'
        ? `Confirm SUSPEND for ${document.getElementById('driverSearchInput').value} for ${durationDays} day(s)?`
        : `Confirm PERMANENT BAN for ${document.getElementById('driverSearchInput').value}?`;
    if (!confirm(confirmMsg)) return;

    const btn = document.getElementById('addBanSubmitBtn');
    const origHtml = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<span class="inline-block w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span> Processing...';

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    fetch(`/driver-management/${driverId}/suspend-or-ban`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
        body: JSON.stringify({ action_type: actionType, duration_days: actionType === 'suspend' ? durationDays : null, reason: reason })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            closeAddBanModal();
            if (typeof showNotification === 'function') showNotification(data.message, 'success');
            setTimeout(() => window.location.reload(), 800);
        } else {
            alert('Error: ' + (data.message || 'Failed to apply lockout.'));
            btn.disabled = false;
            btn.innerHTML = origHtml;
        }
    })
    .catch(err => {
        console.error(err);
        alert('Failed to apply lockout. Please try again.');
        btn.disabled = false;
        btn.innerHTML = origHtml;
    });
}

/* ═══════════════════════════════════════════════════════
   MODIFY EXISTING SUSPENSION MODAL
═══════════════════════════════════════════════════════ */
function openChangeSuspensionModal(driverId, driverName, currentStatus) {
    document.getElementById('changeSuspendDriverId').value = driverId;
    document.getElementById('changeSuspendSubtitle').textContent = driverName;
    document.getElementById('changeSuspendActionType').value = currentStatus === 'suspended' ? 'suspend' : 'ban';
    document.getElementById('changeSuspendDuration').value = '7';
    document.getElementById('changeSuspendReason').value = '';
    document.getElementById('changeReasonCount').textContent = '0 / 500';
    toggleChangeDuration();
    document.getElementById('changeSuspensionModal').classList.remove('hidden');
    if (typeof lucide !== 'undefined') lucide.createIcons();
}
function closeChangeSuspensionModal() {
    document.getElementById('changeSuspensionModal').classList.add('hidden');
}
function toggleChangeDuration() {
    const type = document.getElementById('changeSuspendActionType').value;
    const section = document.getElementById('changeDurationSection');
    const input = document.getElementById('changeSuspendDuration');
    if (type === 'suspend') { section.style.display = 'block'; input.required = true; }
    else { section.style.display = 'none'; input.required = false; }
}
function submitChangeSuspension(event) {
    event.preventDefault();
    const driverId = document.getElementById('changeSuspendDriverId').value;
    const actionType = document.getElementById('changeSuspendActionType').value;
    const durationDays = document.getElementById('changeSuspendDuration').value.trim();
    const reason = document.getElementById('changeSuspendReason').value.trim();

    if (!reason || reason.length < 5) { alert('Please provide a reason (minimum 5 characters).'); return; }
    if (actionType === 'suspend') {
        const days = parseInt(durationDays, 10);
        if (isNaN(days) || days < 1 || days > 365) { alert('Duration must be between 1 and 365 days.'); return; }
    }

    const btn = document.getElementById('changeSuspendSubmitBtn');
    const origHtml = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<span class="inline-block w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span> Updating...';

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    fetch(`/driver-management/${driverId}/suspend-or-ban`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
        body: JSON.stringify({ action_type: actionType, duration_days: actionType === 'suspend' ? durationDays : null, reason: reason })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            closeChangeSuspensionModal();
            if (typeof showNotification === 'function') showNotification(data.message, 'success');
            setTimeout(() => window.location.reload(), 800);
        } else {
            alert('Error: ' + (data.message || 'Failed.'));
            btn.disabled = false;
            btn.innerHTML = origHtml;
        }
    })
    .catch(err => {
        console.error(err);
        alert('Failed. Please try again.');
        btn.disabled = false;
        btn.innerHTML = origHtml;
    });
}

/* Close modals on Escape */
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') { closeAddBanModal(); closeChangeSuspensionModal(); }
});

/* Event listeners for driver card buttons to stop propagation safely */
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.restore-driver-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            performUnban(this.getAttribute('data-id'), this.getAttribute('data-name'));
        });
    });

    document.querySelectorAll('.modify-suspension-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            openChangeSuspensionModal(
                this.getAttribute('data-id'),
                this.getAttribute('data-name'),
                this.getAttribute('data-status')
            );
        });
    });
});
</script>

@include('driver-management.partials._driver_details_modal')
@endsection
