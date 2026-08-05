@extends('layouts.app')
@section('title', 'Driver Performance - Euro System')
@section('page-heading', 'Driver Performance & Violations')
@section('page-subheading', 'Incidents • Incentives • Performance Summary — All in one place')

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
        position: fixed; bottom: 1.5rem; left: 50%; transform: translateX(-50%) translateY(10rem); opacity: 0; visibility: hidden;
        background: #1e293b; border: 1px solid #eab308; color: #ffffff;
        padding: .85rem 1.75rem; border-radius: 999px; font-size: .85rem; font-weight: 600;
        box-shadow: 0 12px 40px rgba(0,0,0,.6);
        z-index: 9999; transition: transform .4s cubic-bezier(.34,1.56,.64,1);
        max-width: 90vw; display: flex; align-items: center; gap: .75rem;
     opacity: 0; visibility: hidden; }
    #sa-toast.show { transform: translateX(-50%) translateY(0);  opacity: 1; visibility: visible; }
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

    /* Dashboard Wave CSS */
    @keyframes drawChart { 0% { clip-path: inset(0 100% 0 0); opacity: 0; } 100% { clip-path: inset(0 0 0 0); opacity: 1; } }
    .card-hover::after {
        content: ''; position: absolute; bottom: 0; left: 0; width: 100%; height: 75px; background-size: 100% 100%; background-repeat: no-repeat; opacity: 0; transition: none !important; z-index: 0;
    }
    .wave-red::after { background-image: url('data:image/svg+xml;utf8,<svg viewBox="0 0 100 50" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg"><polygon fill="rgba(239,68,68,0.15)" stroke="rgba(239,68,68,0.4)" stroke-width="1.5" vector-effect="non-scaling-stroke" stroke-linejoin="miter" points="0,50 0,35 15,20 30,30 45,10 60,25 75,5 90,15 100,0 100,50" /></svg>'); }
    .wave-teal::after { background-image: url('data:image/svg+xml;utf8,<svg viewBox="0 0 100 50" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg"><polygon fill="rgba(20,184,166,0.15)" stroke="rgba(20,184,166,0.4)" stroke-width="1.5" vector-effect="non-scaling-stroke" stroke-linejoin="miter" points="0,50 0,35 15,20 30,30 45,10 60,25 75,5 90,15 100,0 100,50" /></svg>'); }
    .wave-purple::after { background-image: url('data:image/svg+xml;utf8,<svg viewBox="0 0 100 50" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg"><polygon fill="rgba(168,85,247,0.15)" stroke="rgba(168,85,247,0.4)" stroke-width="1.5" vector-effect="non-scaling-stroke" stroke-linejoin="miter" points="0,50 0,35 15,20 30,30 45,10 60,25 75,5 90,15 100,0 100,50" /></svg>'); }
    .wave-yellow::after { background-image: url('data:image/svg+xml;utf8,<svg viewBox="0 0 100 50" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg"><polygon fill="rgba(234,179,8,0.15)" stroke="rgba(234,179,8,0.4)" stroke-width="1.5" vector-effect="non-scaling-stroke" stroke-linejoin="miter" points="0,50 0,35 15,20 30,30 45,10 60,25 75,5 90,15 100,0 100,50" /></svg>'); }
    .wave-green::after { background-image: url('data:image/svg+xml;utf8,<svg viewBox="0 0 100 50" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg"><polygon fill="rgba(34,197,94,0.15)" stroke="rgba(34,197,94,0.4)" stroke-width="1.5" vector-effect="non-scaling-stroke" stroke-linejoin="miter" points="0,50 0,35 15,20 30,30 45,10 60,25 75,5 90,15 100,0 100,50" /></svg>'); }
    .card-hover.in-view::after { animation: drawChart 1s ease-out forwards !important; }
</style>

{{-- ════════ HEADER STATS (COMPACT) ════════ --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">

    {{-- 1. VIOLATIONS TODAY --}}
    <div class="card-hover in-view wave-red cursor-default group relative overflow-hidden rounded-2xl shadow-sm border border-red-100 transition-all duration-300 hover:-translate-y-1 hover:shadow-xl bg-gradient-to-br from-red-50 to-rose-50/70">
        <div class="relative p-3.5 sm:p-5 flex items-center justify-between z-20">
            <div class="flex-1 min-w-0">
                <p class="text-red-400 text-[9px] sm:text-[10px] font-black uppercase tracking-widest mb-1">Violations Today</p>
                <p class="text-gray-900 text-xl sm:text-3xl font-black leading-none mb-1">{{ $stats['violations_today'] ?? 0 }}</p>
            </div>
            <div class="p-1.5 sm:p-3 bg-red-100 rounded-xl sm:rounded-2xl border border-red-200 shadow-sm flex-shrink-0">
                <i data-lucide="alert-circle" class="w-5 h-5 sm:w-7 sm:h-7 text-red-600"></i>
            </div>
        </div>
        <i data-lucide="alert-circle" stroke-width="1" class="absolute right-0 bottom-0 w-24 h-24 -rotate-12 pointer-events-none" style="opacity: 0.15 !important; color: #ef4444 !important; z-index: 5 !important;"></i>
    </div>

    {{-- 2. TOTAL VIOLATORS --}}
    <div class="card-hover in-view wave-teal cursor-default group relative overflow-hidden rounded-2xl shadow-sm border border-teal-100 transition-all duration-300 hover:-translate-y-1 hover:shadow-xl bg-gradient-to-br from-teal-50 to-emerald-50/70">
        <div class="relative p-3.5 sm:p-5 flex items-center justify-between z-20">
            <div class="flex-1 min-w-0">
                <p class="text-teal-400 text-[9px] sm:text-[10px] font-black uppercase tracking-widest mb-1">Total Violators</p>
                <p class="text-gray-900 text-xl sm:text-3xl font-black leading-none mb-1">{{ $stats['total_violators'] ?? 0 }}</p>
            </div>
            <div class="p-1.5 sm:p-3 bg-teal-100 rounded-xl sm:rounded-2xl border border-teal-200 shadow-sm flex-shrink-0">
                <i data-lucide="users" class="w-5 h-5 sm:w-7 sm:h-7 text-teal-600"></i>
            </div>
        </div>
        <i data-lucide="users" stroke-width="1" class="absolute right-0 bottom-0 w-24 h-24 -rotate-12 pointer-events-none" style="opacity: 0.15 !important; color: #14b8a6 !important; z-index: 5 !important;"></i>
    </div>

    {{-- 3. TOTAL CHARGES --}}
    <div class="card-hover in-view wave-purple cursor-default group relative overflow-hidden rounded-2xl shadow-sm border border-purple-100 transition-all duration-300 hover:-translate-y-1 hover:shadow-xl bg-gradient-to-br from-purple-50 to-fuchsia-50/70">
        <div class="relative p-3.5 sm:p-5 flex items-center justify-between z-20">
            <div class="flex-1 min-w-0">
                <p class="text-purple-400 text-[9px] sm:text-[10px] font-black uppercase tracking-widest mb-1">Total Charges</p>
                <p class="text-gray-900 text-xl sm:text-3xl font-black leading-none mb-1">₱{{ number_format($stats['total_charges'] ?? 0, 0) }}</p>
            </div>
            <div class="p-1.5 sm:p-3 bg-purple-100 rounded-xl sm:rounded-2xl border border-purple-200 shadow-sm flex-shrink-0">
                <i data-lucide="banknote" class="w-5 h-5 sm:w-7 sm:h-7 text-purple-600"></i>
            </div>
        </div>
        <i data-lucide="banknote" stroke-width="1" class="absolute right-0 bottom-0 w-24 h-24 -rotate-12 pointer-events-none" style="opacity: 0.15 !important; color: #a855f7 !important; z-index: 5 !important;"></i>
    </div>

    {{-- 4. ELIGIBLE INCENTIVE --}}
    <div class="card-hover in-view wave-yellow cursor-default group relative overflow-hidden rounded-2xl shadow-sm border border-yellow-100 transition-all duration-300 hover:-translate-y-1 hover:shadow-xl bg-gradient-to-br from-yellow-50 to-amber-50/70">
        <div class="relative p-3.5 sm:p-5 flex items-center justify-between z-20">
            <div class="flex-1 min-w-0">
                <p class="text-yellow-500 text-[9px] sm:text-[10px] font-black uppercase tracking-widest mb-1">Eligible Incentive</p>
                <p class="text-gray-900 text-xl sm:text-3xl font-black leading-none mb-1">{{ count($incentive_summary['eligible'] ?? []) }}</p>
            </div>
            <div class="p-1.5 sm:p-3 bg-yellow-100 rounded-xl sm:rounded-2xl border border-yellow-200 shadow-sm flex-shrink-0">
                <i data-lucide="trophy" class="w-5 h-5 sm:w-7 sm:h-7 text-yellow-600"></i>
            </div>
        </div>
        <i data-lucide="trophy" stroke-width="1" class="absolute right-0 bottom-0 w-24 h-24 -rotate-12 pointer-events-none" style="opacity: 0.15 !important; color: #eab308 !important; z-index: 5 !important;"></i>
    </div>
</div>

    <div class="mb-4 flex flex-col sm:flex-row gap-3">
        <!-- Hidden input to trick browser autofill -->
        <input type="text" style="display:none" autocomplete="username">
        <input type="search" id="profileSearch" placeholder="Search driver name..." name="perf_search_query"
            class="w-full sm:flex-1 md:w-80 px-4 py-2.5 bg-white border border-gray-200 rounded-xl text-sm font-medium focus:ring-2 focus:ring-yellow-500 focus:outline-none shadow-sm"
            autocomplete="new-password" spellcheck="false" autocorrect="off" autocapitalize="off" readonly onfocus="this.removeAttribute('readonly');">
        
        <select id="profileStatusFilter" class="w-full sm:w-64 px-4 py-2.5 bg-white border border-gray-200 rounded-xl text-sm font-medium focus:ring-2 focus:ring-yellow-500 focus:outline-none shadow-sm">
            <option value="all">All Drivers</option>
            <option value="violators_today">Violators Today</option>
            <option value="total_violators">Total Violators</option>
            <option value="eligible">Eligible Incentives</option>
        </select>
    </div>

    <div id="profileGrid" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
        @foreach($driver_profiles as $profile)
        @php
            $inc = $profile['incentive'];
            $eligible = $inc['eligible'];
        @endphp
        <div class="profile-card bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-all cursor-pointer" 
            data-name="{{ strtolower($profile['name']) }}" 
            data-eligible="{{ $eligible ? '1' : '0' }}"
            data-violators-today="{{ $profile['incidents_today'] > 0 ? '1' : '0' }}"
            data-total-violators="{{ $profile['incidents'] > 0 ? '1' : '0' }}"
            onclick="openDriverDetails({{ $profile['id'] }})">
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
@include('driver-management.partials._driver_details_modal')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('profileSearch');
    const statusSelect = document.getElementById('profileStatusFilter');
    const cards = document.querySelectorAll('.profile-card');

    if (searchInput) {
        searchInput.value = '';
    }

    window.filterProfiles = function() {
        const term = searchInput ? searchInput.value.toLowerCase() : '';
        const status = statusSelect ? statusSelect.value : 'all';

        cards.forEach(card => {
            const name = card.dataset.name || '';
            const isEligible = card.dataset.eligible === '1';
            const isViolatorToday = card.dataset.violatorsToday === '1';
            const isTotalViolator = card.dataset.totalViolators === '1';

            let matchesSearch = name.includes(term);
            let matchesStatus = true;

            if (status === 'violators_today') {
                matchesStatus = isViolatorToday;
            } else if (status === 'total_violators') {
                matchesStatus = isTotalViolator;
            } else if (status === 'eligible') {
                matchesStatus = isEligible;
            }

            if (matchesSearch && matchesStatus) {
                card.style.display = 'block';
            } else {
                card.style.display = 'none';
            }
        });
    };

    if (searchInput) searchInput.addEventListener('input', filterProfiles);
    if (statusSelect) statusSelect.addEventListener('change', filterProfiles);
});
</script>
@endsection
