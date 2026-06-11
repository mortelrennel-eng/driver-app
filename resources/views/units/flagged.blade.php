@extends('layouts.app')

@section('title', 'Flagged Units - Euro System')
@section('page-heading', 'Flagged Units')
@section('page-subheading', 'Units reported missing or automatically flagged by the system due to boundary delays')

@section('content')
<style>
    .flag-card {
        transition: all 0.35s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .flag-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 20px 30px -8px rgba(0,0,0,0.08);
    }
    .fade-remove {
        opacity: 0;
        transform: scale(0.9) translateY(20px);
        transition: all 0.5s ease;
        pointer-events: none;
    }
    .pulse-ring {
        animation: pulse-ring 1.8s cubic-bezier(0.215, 0.61, 0.355, 1) infinite;
    }
    @keyframes pulse-ring {
        0%   { box-shadow: 0 0 0 0 rgba(239,68,68,0.45); }
        70%  { box-shadow: 0 0 0 10px rgba(239,68,68,0); }
        100% { box-shadow: 0 0 0 0 rgba(239,68,68,0); }
    }
    .pulse-ring-amber {
        animation: pulse-ring-amber 1.8s cubic-bezier(0.215, 0.61, 0.355, 1) infinite;
    }
    @keyframes pulse-ring-amber {
        0%   { box-shadow: 0 0 0 0 rgba(245,158,11,0.45); }
        70%  { box-shadow: 0 0 0 10px rgba(245,158,11,0); }
        100% { box-shadow: 0 0 0 0 rgba(245,158,11,0); }
    }
</style>

<div class="space-y-8">

    {{-- ── Hero Header Panel ──────────────────────────────── --}}
    <div class="relative bg-slate-900 rounded-[2.5rem] p-8 overflow-hidden shadow-2xl border border-orange-500/10">
        <div class="absolute -right-24 -top-24 w-96 h-96 bg-orange-600/10 rounded-full blur-[120px] pointer-events-none"></div>
        <div class="absolute -left-24 -bottom-24 w-72 h-72 bg-red-500/8 rounded-full blur-[100px] pointer-events-none"></div>

        <div class="relative flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
            <div class="flex items-center gap-5">
                <div class="w-16 h-16 bg-gradient-to-br from-orange-500 to-red-600 rounded-3xl flex items-center justify-center shadow-xl shadow-orange-500/25 pulse-ring shrink-0">
                    <i data-lucide="flag" class="w-8 h-8 text-white"></i>
                </div>
                <div>
                    <h3 class="text-2xl font-black text-white tracking-tight">Flagged Units Registry</h3>
                    <p class="text-sm text-slate-400 mt-1 font-medium max-w-xl leading-relaxed">
                        Units manually marked as missing/stolen, or automatically flagged by the system due to overdue boundaries exceeding 48 hours.
                    </p>
                </div>
            </div>

            {{-- Stats row --}}
            <div class="flex flex-wrap gap-3 shrink-0">
                <div class="flex flex-col text-center bg-red-500/10 px-5 py-3 rounded-2xl border border-red-500/15">
                    <span class="text-[10px] font-black text-red-400 uppercase tracking-widest mb-0.5">Total Flagged</span>
                    <span class="text-3xl font-black text-red-400 tracking-tight" id="total-flagged-count">{{ $flaggedCount }}</span>
                </div>
                <div class="flex flex-col text-center bg-slate-800/60 px-5 py-3 rounded-2xl border border-slate-700/40">
                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-0.5">Missing</span>
                    <span class="text-3xl font-black text-white tracking-tight">{{ $stolenCount }}</span>
                </div>
                <div class="flex flex-col text-center bg-slate-800/60 px-5 py-3 rounded-2xl border border-slate-700/40">
                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-0.5">Auto-Detected</span>
                    <span class="text-3xl font-black text-white tracking-tight">{{ $autoCount }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Controls Bar ─────────────────────────────────── --}}
    <div class="flex flex-col lg:flex-row gap-4 justify-between items-center bg-white p-4 rounded-2xl border border-gray-100 shadow-sm">

        {{-- Search --}}
        <div class="relative w-full lg:max-w-xs">
            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">
                <i data-lucide="search" class="w-4 h-4"></i>
            </span>
            <input type="text" id="flagSearchInput" placeholder="Search plate, make, model…"
                   autocomplete="off" spellcheck="false" readonly onfocus="this.removeAttribute('readonly');"
                   class="w-full pl-11 pr-4 py-3 text-sm border-2 border-gray-100 rounded-xl focus:border-orange-400/40 focus:ring-4 focus:ring-orange-400/8 transition-all outline-none bg-slate-50/50">
        </div>

        {{-- Filter tabs --}}
        <div class="flex items-center gap-2 w-full lg:w-auto overflow-x-auto py-1 scrollbar-none">
            <button type="button" data-filter="all" onclick="setFilter('all')"
                class="filter-tab-btn px-4 py-2.5 bg-slate-900 text-white text-xs font-black rounded-xl transition-all shadow-md whitespace-nowrap">
                All Flagged
            </button>
            <button type="button" data-filter="manual_stolen" onclick="setFilter('manual_stolen')"
                class="filter-tab-btn px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition-all whitespace-nowrap">
                Missing / Stolen
            </button>
            <button type="button" data-filter="auto_boundary" onclick="setFilter('auto_boundary')"
                class="filter-tab-btn px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition-all whitespace-nowrap">
                Auto-Detected
            </button>
        </div>

        {{-- Back button & Add Flag --}}
        <div class="shrink-0 w-full lg:w-auto flex flex-col sm:flex-row justify-end gap-3">
            <button type="button" onclick="openManualFlagModal()"
                    class="flex items-center justify-center gap-2 px-5 py-3 bg-red-50 hover:bg-red-100 text-red-600 text-xs font-black rounded-xl transition-all border border-red-200 hover:border-red-300 w-full sm:w-auto">
                <i data-lucide="flag" class="w-4 h-4"></i> Flag Unit Manually
            </button>
            <a href="{{ route('units.index') }}"
               class="flex items-center justify-center gap-2 px-5 py-3 bg-slate-900 text-white text-xs font-black rounded-xl hover:bg-slate-800 transition-all active:scale-95 shadow-lg w-full sm:w-auto">
                <i data-lucide="arrow-left" class="w-4 h-4"></i> Back to Units
            </a>
        </div>
    </div>

    {{-- ── Grid of Cards ──────────────────────────────────── --}}
    <div id="flaggedGrid" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">

        @forelse($allFlagged as $unit)
        @php
            $isMissing      = $unit->flag_source === 'manual_stolen';
            $isAuto         = $unit->flag_source === 'auto_boundary';

            $badgeText  = $isMissing ? 'Missing' : 'Auto-Flagged';
            $badgeCss   = $isMissing
                ? 'bg-red-100 text-red-700 border-red-200'
                : 'bg-orange-100 text-orange-700 border-orange-200';
            $gradientCss = $isMissing
                ? 'from-red-500 to-rose-600 shadow-red-500/20'
                : 'from-orange-500 to-amber-600 shadow-orange-500/20';
            $pulseClass = $isMissing ? 'pulse-ring' : 'pulse-ring-amber';
        @endphp

        <div class="flag-card bg-white rounded-[2rem] border border-gray-100 shadow-sm overflow-hidden flex flex-col"
             id="flagcard-{{ $unit->id }}"
             data-flag-source="{{ $unit->flag_source }}"
             data-search-terms="{{ strtolower($unit->plate_number . ' ' . $unit->make . ' ' . $unit->model) }}">

            {{-- Card Header --}}
            <div class="p-6 bg-slate-50/60 border-b border-gray-50 flex items-start gap-4">
                <div class="w-14 h-14 bg-gradient-to-br {{ $gradientCss }} rounded-2xl flex items-center justify-center text-white text-lg font-black shrink-0 shadow-lg {{ $pulseClass }}">
                    <i data-lucide="{{ $isMissing ? 'alert-triangle' : 'clock' }}" class="w-6 h-6"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 flex-wrap">
                        <h4 class="text-base font-black text-slate-900 tracking-tight">{{ $unit->plate_number }}</h4>
                        <span class="px-2 py-0.5 rounded-md text-[9px] font-black uppercase tracking-widest border {{ $badgeCss }}">{{ $badgeText }}</span>
                    </div>
                    <p class="text-xs text-slate-500 font-bold mt-0.5">{{ $unit->make }} {{ $unit->model }} @if(!empty($unit->year))({{ $unit->year }})@endif</p>
                    <p class="text-[10px] text-slate-400 mt-0.5 font-bold uppercase tracking-widest">Unit ID: UNT-{{ str_pad($unit->id, 4, '0', STR_PAD_LEFT) }}</p>
                </div>
            </div>

            {{-- Card Body --}}
            <div class="p-6 flex-1 space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-0.5">
                        <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest block">Status</span>
                        <span class="text-xs font-black text-slate-800 capitalize">{{ str_replace('_', ' ', $unit->status) }}</span>
                    </div>
                    <div class="space-y-0.5">
                        <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest block">Days Inactive</span>
                        <span class="text-xs font-black {{ ($unit->days_inactive ?? 0) > 7 ? 'text-red-600' : 'text-amber-600' }}">
                            {{ $unit->days_inactive !== null ? $unit->days_inactive . ' day(s)' : 'N/A' }}
                        </span>
                    </div>
                    <div class="space-y-0.5">
                        <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest block">Suspect Driver</span>
                        <span class="text-xs font-bold text-slate-700 truncate block">{{ $unit->suspect_driver ?? 'Unknown' }}</span>
                    </div>
                    <div class="space-y-0.5">
                        <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest block">Contact</span>
                        <span class="text-xs font-bold text-slate-600">{{ $unit->suspect_contact ?? '—' }}</span>
                    </div>
                </div>

                @if(!empty($unit->missing_since))
                <div class="pt-3 border-t border-gray-50 space-y-0.5">
                    <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest block">Missing Since</span>
                    <span class="text-xs font-bold text-slate-700">{{ $unit->missing_since }}</span>
                </div>
                @endif

                @if(!empty($unit->description))
                <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-100/85 space-y-1">
                    <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest block">Description / Details</span>
                    <p class="text-xs text-slate-600 font-medium leading-relaxed">{{ $unit->description }}</p>
                </div>
                @endif

                @if(!empty($unit->last_boundary_date))
                <div class="pt-3 border-t border-gray-50 space-y-0.5">
                    <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest block">Last Boundary Submitted</span>
                    <span class="text-xs font-bold text-slate-600">{{ $unit->last_boundary_date }}</span>
                </div>
                @endif

                @if(!empty($unit->last_known_driver) && $unit->last_known_driver !== 'No boundary record')
                <div class="space-y-0.5">
                    <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest block">Last Known Driver</span>
                    <span class="text-xs font-bold text-slate-700">{{ $unit->last_known_driver }}</span>
                </div>
                @endif

                @if(!empty($unit->stolen_driver_license_no))
                <div class="pt-3 border-t border-gray-50 flex items-center gap-2 p-2 bg-red-50 rounded-xl border border-red-100">
                    <i data-lucide="credit-card" class="w-4 h-4 text-red-500 shrink-0"></i>
                    <div>
                        <span class="text-[9px] font-black text-red-500 uppercase tracking-widest block">Suspect License No.</span>
                        <span class="text-xs font-black text-red-800 font-mono tracking-wider">{{ $unit->stolen_driver_license_no }}</span>
                    </div>
                </div>
                @endif

                {{-- Flag Source Info --}}
                <div class="pt-3 border-t border-gray-50 flex items-start gap-2 text-[10px] font-semibold text-slate-400 italic">
                    <i data-lucide="{{ $isAuto ? 'cpu' : 'user-check' }}" class="w-3.5 h-3.5 mt-0.5 shrink-0"></i>
                    <span>
                        @if($isMissing) Manually reported as missing or stolen
                        @else Auto-detected: no boundary submitted for 48+ hours
                        @endif
                    </span>
                </div>
            </div>

            {{-- Card Footer Actions --}}
            <div class="p-5 border-t border-gray-50 bg-slate-50 flex justify-between items-center gap-2 relative z-10 pointer-events-auto">
                <a href="{{ route('units.index') }}?highlight={{ $unit->id }}"
                   class="flex items-center gap-1.5 px-3 py-2 text-[10px] font-black text-slate-600 bg-white border border-gray-200 rounded-xl hover:bg-slate-100 transition-all">
                    <i data-lucide="eye" class="w-3.5 h-3.5"></i> View
                </a>
                <button type="button"
                        onclick="recoverUnit({{ $unit->id }}, '{{ $unit->plate_number }}')"
                        class="relative z-50 pointer-events-auto flex items-center gap-1.5 px-4 py-2 bg-slate-900 hover:bg-slate-700 text-white text-[10px] font-black rounded-xl transition-all active:scale-95 shadow-md cursor-pointer">
                    <i data-lucide="shield-check" class="w-3.5 h-3.5 text-emerald-400"></i> Mark Recovered
                </button>
            </div>
        </div>
        @empty

        {{-- Empty state --}}
        <div id="empty-flagged-state" class="col-span-1 md:col-span-2 xl:col-span-3 flex flex-col items-center justify-center py-28 bg-slate-50 rounded-[2.5rem] border border-dashed border-slate-200">
            <div class="w-20 h-20 bg-emerald-50 text-emerald-500 rounded-3xl flex items-center justify-center mb-6 shadow-xl shadow-emerald-100">
                <i data-lucide="check-circle-2" class="w-10 h-10 animate-bounce"></i>
            </div>
            <h4 class="text-xl font-black text-slate-800 mb-2">No Flagged Units</h4>
            <p class="text-sm text-slate-500 max-w-xs text-center font-medium leading-relaxed">
                All units are operating normally with no missing reports or overdue boundaries.
            </p>
        </div>

        @endforelse
    </div>

    {{-- No results from search --}}
    <div id="noSearchResults" class="hidden flex-col items-center justify-center py-20 bg-slate-50 rounded-[2.5rem] border border-dashed border-slate-200">
        <div class="w-16 h-16 bg-slate-100 text-slate-400 rounded-full flex items-center justify-center mb-4">
            <i data-lucide="search-x" class="w-8 h-8"></i>
        </div>
        <h4 class="text-base font-black text-slate-800 mb-1">No Matching Units</h4>
        <p class="text-xs text-slate-400">Try a different plate number, make, or model.</p>
    </div>

</div>

{{-- ── Manual Flag Modal ──────────────────────────────── --}}
<div id="manualFlagModal" class="fixed inset-0 z-[100] hidden">
    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity opacity-0" id="manualFlagBackdrop" onclick="closeManualFlagModal()"></div>
    <div class="flex items-center justify-center min-h-screen p-4 pointer-events-none">
        <div class="bg-white rounded-[2rem] shadow-2xl w-full max-w-2xl transform scale-95 opacity-0 transition-all pointer-events-auto border border-slate-100" id="manualFlagPanel">
            <form action="{{ route('units.flag-manually') }}" method="POST" class="flex flex-col h-full max-h-[90vh]">
                @csrf
                {{-- Modal Header --}}
                <div class="p-6 border-b border-gray-50 flex items-center justify-between bg-slate-50/50 rounded-t-[2rem]">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-red-100 text-red-600 rounded-2xl flex items-center justify-center shadow-inner">
                            <i data-lucide="flag-triangle-right" class="w-6 h-6"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-black text-slate-800 tracking-tight">Flag Unit Manually</h3>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">Mark as Missing or Stolen</p>
                        </div>
                    </div>
                    <button type="button" onclick="closeManualFlagModal()" class="w-10 h-10 rounded-full flex items-center justify-center text-slate-400 hover:text-slate-600 hover:bg-slate-200/50 transition-colors">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                {{-- Modal Body --}}
                <div class="p-6 overflow-y-auto custom-scrollbar flex-1 space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        {{-- Select Unit --}}
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest flex items-center gap-1.5">
                                <i data-lucide="car" class="w-3.5 h-3.5"></i> Select Unit
                            </label>
                            <select name="unit_id" required class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold text-slate-700 focus:outline-none focus:ring-4 focus:ring-red-500/10 focus:border-red-400 transition-all">
                                <option value="">-- Choose Unit --</option>
                                @foreach($availableUnits as $u)
                                    <option value="{{ $u->id }}">{{ $u->plate_number }} ({{ $u->make }} {{ $u->model }})</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Select Flag Type (Hidden) --}}
                        <input type="hidden" name="flag_type" value="missing">
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        {{-- Select Suspect Driver --}}
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest flex items-center gap-1.5">
                                <i data-lucide="user-x" class="w-3.5 h-3.5"></i> Suspect Driver (Optional)
                            </label>
                            <select name="suspect_driver_id" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold text-slate-700 focus:outline-none focus:ring-4 focus:ring-red-500/10 focus:border-red-400 transition-all">
                                <option value="">-- No Specific Driver --</option>
                                @foreach($availableDrivers as $d)
                                    <option value="{{ $d->id }}">{{ $d->first_name }} {{ $d->last_name }} ({{ $d->contact_number }})</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Missing Since Date --}}
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest flex items-center justify-between gap-1.5">
                                <span class="flex items-center gap-1.5"><i data-lucide="calendar-off" class="w-3.5 h-3.5"></i> Missing / Flagged Since</span>
                                <span id="days-ago-badge" class="px-2 py-0.5 bg-red-100 text-red-700 rounded text-[9px] font-bold">0 day(s) missing</span>
                            </label>
                            <input type="date" name="missing_since" value="{{ date('Y-m-d') }}" max="{{ date('Y-m-d') }}"
                                class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold text-slate-700 focus:outline-none focus:ring-4 focus:ring-red-500/10 focus:border-red-400 transition-all">
                        </div>
                    </div>

                    {{-- Description --}}
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest flex items-center gap-1.5">
                            <i data-lucide="file-text" class="w-3.5 h-3.5"></i> Reason / Description
                        </label>
                        <textarea name="description" required rows="4" minlength="5" maxlength="1000" placeholder="E.g., Hindi na nagrereply sa texts, huling boundary ay nung..."
                            class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold text-slate-700 focus:outline-none focus:ring-4 focus:ring-red-500/10 focus:border-red-400 transition-all resize-none"></textarea>
                    </div>

                    {{-- Alert Box --}}
                    <div class="p-4 bg-amber-50 rounded-xl border border-amber-200 flex gap-3">
                        <i data-lucide="info" class="w-5 h-5 text-amber-500 shrink-0 mt-0.5"></i>
                        <p class="text-xs text-amber-700 font-medium leading-relaxed">
                            Flagging a unit as <strong>Missing/Stolen</strong> will log a critical incident on the assigned driver's behavior record. The unit will remain flagged until manually recovered.
                        </p>
                    </div>
                </div>

                {{-- Modal Footer --}}
                <div class="p-6 border-t border-gray-50 bg-slate-50/50 flex justify-end gap-3 rounded-b-[2rem]">
                    <button type="button" onclick="closeManualFlagModal()" class="px-6 py-2.5 text-sm font-bold text-slate-600 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 hover:text-slate-900 transition-colors">
                        Cancel
                    </button>
                    <button type="submit" class="px-6 py-2.5 text-sm font-black text-white bg-red-600 rounded-xl hover:bg-red-700 shadow-md shadow-red-500/20 active:scale-95 transition-all flex items-center gap-2">
                        <i data-lucide="flag" class="w-4 h-4"></i> Submit Flag
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    let currentFilter = 'all';

    document.addEventListener('DOMContentLoaded', () => {
        const dateInput = document.querySelector('input[name="missing_since"]');
        const updateDaysAgo = () => {
            if (!dateInput || !dateInput.value) return;
            const selectedDate = new Date(dateInput.value);
            const today = new Date();
            selectedDate.setHours(0,0,0,0);
            today.setHours(0,0,0,0);
            
            const diffTime = today - selectedDate;
            const diffDays = Math.max(0, Math.floor(diffTime / (1000 * 60 * 60 * 24)));
            
            const badge = document.getElementById('days-ago-badge');
            if (badge) {
                badge.textContent = `${diffDays} day(s) missing`;
            }
        };
        
        dateInput?.addEventListener('change', updateDaysAgo);
        updateDaysAgo();
    });

    function openManualFlagModal() {
        const modal = document.getElementById('manualFlagModal');
        const backdrop = document.getElementById('manualFlagBackdrop');
        const panel = document.getElementById('manualFlagPanel');
        
        modal.classList.remove('hidden');
        // trigger reflow
        void modal.offsetWidth;
        
        backdrop.classList.remove('opacity-0');
        backdrop.classList.add('opacity-100');
        
        panel.classList.remove('scale-95', 'opacity-0');
        panel.classList.add('scale-100', 'opacity-100');
    }

    function closeManualFlagModal() {
        const modal = document.getElementById('manualFlagModal');
        const backdrop = document.getElementById('manualFlagBackdrop');
        const panel = document.getElementById('manualFlagPanel');
        
        backdrop.classList.remove('opacity-100');
        backdrop.classList.add('opacity-0');
        
        panel.classList.remove('scale-100', 'opacity-100');
        panel.classList.add('scale-95', 'opacity-0');
        
        setTimeout(() => {
            modal.classList.add('hidden');
        }, 300);
    }

    function filterCards() {
        const query = (document.getElementById('flagSearchInput')?.value || '').trim().toLowerCase();
        const cards = document.querySelectorAll('.flag-card');
        const noResults = document.getElementById('noSearchResults');
        let visible = 0;

        cards.forEach(card => {
            const terms  = card.getAttribute('data-search-terms') || '';
            const source = card.getAttribute('data-flag-source') || '';
            const matchSearch = !query || terms.includes(query);
            const matchFilter = currentFilter === 'all' || source === currentFilter;

            if (matchSearch && matchFilter) {
                card.classList.remove('hidden');
                visible++;
            } else {
                card.classList.add('hidden');
            }
        });

        if (noResults) {
            if (visible === 0 && cards.length > 0) {
                noResults.classList.remove('hidden');
                noResults.classList.add('flex');
            } else {
                noResults.classList.add('hidden');
                noResults.classList.remove('flex');
            }
        }
    }

    function setFilter(filter) {
        currentFilter = filter;
        document.querySelectorAll('.filter-tab-btn').forEach(btn => {
            const active = btn.getAttribute('data-filter') === filter;
            btn.className = active
                ? 'filter-tab-btn px-4 py-2.5 bg-slate-900 text-white text-xs font-black rounded-xl transition-all shadow-md whitespace-nowrap'
                : 'filter-tab-btn px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition-all whitespace-nowrap';
        });
        filterCards();
    }

    document.getElementById('flagSearchInput')?.addEventListener('input', filterCards);

    function recoverUnit(unitId, plateName) {
        if (!confirm(`Mark unit "${plateName}" as RECOVERED?\nThis will reset its status back to Active.`)) return;

        const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

        fetch(`/units/${unitId}/recover`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrf,
                'Accept': 'application/json'
            }
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                const card = document.getElementById('flagcard-' + unitId);
                if (card) {
                    card.classList.add('fade-remove');
                    setTimeout(() => {
                        card.remove();

                        // Update total count badge
                        const badge = document.getElementById('total-flagged-count');
                        if (badge) {
                            const curr = parseInt(badge.textContent) || 0;
                            badge.textContent = Math.max(0, curr - 1);
                        }

                        // If grid is empty, show empty state
                        const remaining = document.querySelectorAll('.flag-card:not(.hidden)').length;
                        if (remaining === 0) {
                            const grid = document.getElementById('flaggedGrid');
                            if (grid) {
                                grid.innerHTML = `
                                    <div class="col-span-1 md:col-span-2 xl:col-span-3 flex flex-col items-center justify-center py-28 bg-slate-50 rounded-[2.5rem] border border-dashed border-slate-200">
                                        <div class="w-20 h-20 bg-emerald-50 text-emerald-500 rounded-3xl flex items-center justify-center mb-6 shadow-xl shadow-emerald-100">
                                            <i data-lucide="check-circle-2" class="w-10 h-10 animate-bounce"></i>
                                        </div>
                                        <h4 class="text-xl font-black text-slate-800 mb-2">No Flagged Units</h4>
                                        <p class="text-sm text-slate-500 max-w-xs text-center font-medium leading-relaxed">All units are operating normally.</p>
                                    </div>`;
                                if (typeof lucide !== 'undefined') lucide.createIcons();
                            }
                        }
                    }, 500);
                }

                if (typeof showNotification === 'function') {
                    showNotification(data.message, 'success');
                } else {
                    alert(data.message);
                }
            } else {
                alert('Error: ' + (data.message || 'Failed to recover unit.'));
            }
        })
        .catch(err => {
            console.error('Recover error:', err);
            alert('Network error. Please try again.');
        });
    }
</script>
@endsection
