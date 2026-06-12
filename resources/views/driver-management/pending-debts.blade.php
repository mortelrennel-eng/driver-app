@extends('layouts.app')

@section('title', 'Financial Liabilities — Euro Taxi Fleet')
@section('page-heading', 'Financial Liabilities')
@section('page-subheading', 'Track and manage driver accident charges, parts, and boundary shortages')

@section('content')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
/* ── General ── */
.stat-card   { transition: box-shadow .2s, border-color .2s; }
.stat-card:hover { box-shadow: 0 4px 24px -4px rgba(15,23,42,.1); }

/* ── Driver summary cards ── */
.driver-card {
    cursor: pointer;
    transition: box-shadow .2s ease, transform .15s ease, border-color .2s;
    position: relative;
}
.driver-card:hover {
    box-shadow: 0 8px 32px -4px rgba(15,23,42,.14);
    transform: translateY(-2px);
}
.driver-card.selected {
    border-color: #0f172a !important;
    box-shadow: 0 0 0 3px rgba(15,23,42,.12), 0 8px 32px -4px rgba(15,23,42,.2);
}

/* ── Modal overlay ── */
#driver-modal-overlay {
    position: fixed;
    inset: 0;
    z-index: 1000;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1rem;
    background: rgba(15,23,42,.55);
    backdrop-filter: blur(4px);
    -webkit-backdrop-filter: blur(4px);
    opacity: 0;
    pointer-events: none;
    transition: opacity .22s ease;
}
#driver-modal-overlay.open {
    opacity: 1;
    pointer-events: all;
}
#driver-modal-box {
    width: 100%;
    max-width: 860px;
    max-height: 88vh;
    background: #fff;
    border-radius: 20px;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    box-shadow: 0 32px 80px -12px rgba(15,23,42,.45);
    transform: scale(.96) translateY(12px);
    transition: transform .25s cubic-bezier(0.34,1.56,0.64,1);
}
#driver-modal-overlay.open #driver-modal-box {
    transform: scale(1) translateY(0);
}
#driver-modal-body {
    overflow-y: auto;
    flex: 1;
}

/* ── Tabs ── */
.tab-pill.active {
    background: #0f172a;
    color: #fff;
    box-shadow: 0 2px 8px rgba(15,23,42,.18);
}
.tab-pill { transition: background .18s, color .18s; }

/* ── Debt type badges ── */
.badge-shortage { background:#fff1f2; color:#be123c; border:1px solid #fecdd3; }
.badge-damage   { background:#fff7ed; color:#c2410c; border:1px solid #fed7aa; }
.badge-parts    { background:#fffbeb; color:#b45309; border:1px solid #fde68a; }
.badge-general  { background:#f8fafc; color:#475569; border:1px solid #e2e8f0; }

/* ── Progress bar ── */
.pbar-track { background:#f1f5f9; border-radius:99px; height:5px; overflow:hidden; }
.pbar-fill  { height:100%; border-radius:99px;
    background: linear-gradient(90deg,#ef4444,#f97316);
    transition: width .6s ease; }

/* ── Pay input ── */
.pay-input { transition: border-color .15s, box-shadow .15s; }
.pay-input:focus {
    outline: none;
    border-color: #ef4444;
    box-shadow: 0 0 0 3px rgba(239,68,68,.12);
}

/* ── Debt item row ── */
.debt-row { transition: background .15s; }
.debt-row:hover { background: #f8fafc; }

</style>

{{-- ══ DRIVER DETAIL MODAL OVERLAY ══ --}}
<div id="driver-modal-overlay" onclick="handleModalBackdropClick(event)">
    <div id="driver-modal-box">
        {{-- Modal header injected by JS --}}
        <div id="driver-modal-header"></div>
        {{-- Sub-header --}}
        <div id="driver-modal-subheader" class="hidden px-6 py-3 bg-gray-50 border-b border-gray-200 flex items-center justify-between">
            <span class="text-[10px] font-black uppercase tracking-widest text-gray-500 flex items-center gap-1.5">
                <i data-lucide="list" class="w-3.5 h-3.5"></i>
                Liability Breakdown
            </span>
            <span id="driver-modal-count" class="text-[10px] font-bold text-gray-400"></span>
        </div>
        {{-- Scrollable rows --}}
        <div id="driver-modal-body" class="bg-white divide-y divide-gray-100"></div>
    </div>
</div>

<div class="w-full mx-auto space-y-6 pb-20 relative z-10">

    {{-- ══════════════════════
         STATS
    ══════════════════════ --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 lg:grid-cols-3 gap-4">

        <div class="stat-card bg-white rounded-2xl border border-gray-100 p-5 flex items-center gap-4 relative overflow-hidden">
            <div class="absolute -right-3 -top-3 w-20 h-20 rounded-full bg-red-50 blur-xl pointer-events-none"></div>
            <div class="w-12 h-12 rounded-xl bg-red-50 border border-red-100 flex items-center justify-center shrink-0 z-10">
                <i data-lucide="users" class="w-5 h-5 text-red-500"></i>
            </div>
            <div class="z-10">
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Active Debtors</p>
                <p class="text-3xl font-black text-gray-900 leading-none mt-0.5" id="stat-debtors">—</p>
            </div>
        </div>

        <div class="stat-card bg-white rounded-2xl border border-gray-100 p-5 flex items-center gap-4 relative overflow-hidden">
            <div class="absolute -right-3 -top-3 w-20 h-20 rounded-full bg-rose-50 blur-xl pointer-events-none"></div>
            <div class="w-12 h-12 rounded-xl bg-rose-50 border border-rose-100 flex items-center justify-center shrink-0 z-10">
                <i data-lucide="alert-circle" class="w-5 h-5 text-rose-500"></i>
            </div>
            <div class="z-10">
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Total Outstanding</p>
                <p class="text-2xl font-black text-rose-600 leading-none mt-0.5" id="stat-total-pending">₱0.00</p>
            </div>
        </div>

        <div class="stat-card bg-white rounded-2xl border border-gray-100 p-5 flex items-center gap-4 relative overflow-hidden">
            <div class="absolute -right-3 -top-3 w-20 h-20 rounded-full bg-emerald-50 blur-xl pointer-events-none"></div>
            <div class="w-12 h-12 rounded-xl bg-emerald-50 border border-emerald-100 flex items-center justify-center shrink-0 z-10">
                <i data-lucide="piggy-bank" class="w-5 h-5 text-emerald-500"></i>
            </div>
            <div class="z-10">
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Total Collected</p>
                <p class="text-2xl font-black text-emerald-600 leading-none mt-0.5" id="stat-collections">₱0.00</p>
            </div>
        </div>
    </div>

    {{-- ══════════════════════
         TOOLBAR
    ══════════════════════ --}}
    <div class="bg-white rounded-2xl border border-gray-100 px-4 py-3 flex flex-col sm:flex-row justify-between items-center gap-3">
        <div class="flex gap-1 bg-gray-50 border border-gray-100 p-1 rounded-xl w-full sm:w-auto">
            <button onclick="switchTab('active')" id="tab-active"
                class="tab-pill active flex-1 sm:flex-none px-5 py-2 text-xs font-black rounded-lg flex items-center justify-center gap-1.5">
                <i data-lucide="wallet" class="w-3.5 h-3.5"></i> Active Liabilities
            </button>
            <button onclick="switchTab('history')" id="tab-history"
                class="tab-pill flex-1 sm:flex-none px-5 py-2 text-xs font-black text-gray-500 rounded-lg flex items-center justify-center gap-1.5">
                <i data-lucide="clock" class="w-3.5 h-3.5"></i> Settlement History
            </button>
        </div>

        {{-- Search with full autofill prevention --}}
        <div class="relative w-full sm:w-64" id="search-container">
            <div style="position:absolute;width:1px;height:1px;overflow:hidden;opacity:0;pointer-events:none;" aria-hidden="true">
                <input type="text"  name="fake_name_trap" tabindex="-1">
                <input type="email" name="email"          tabindex="-1">
                <input type="password" name="password"   tabindex="-1">
            </div>
            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                <i data-lucide="search" class="w-4 h-4 text-gray-400"></i>
            </div>
            <input type="text" id="searchInput" name="driver_search_xq9"
                autocomplete="new-password" readonly
                placeholder="Search driver or plate…"
                class="w-full pl-10 pr-4 py-2.5 text-sm font-semibold text-gray-700 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:border-slate-400 focus:ring-0 outline-none transition-all cursor-text">
        </div>
    </div>

    {{-- ══════════════════════
         ACTIVE LIABILITIES
    ══════════════════════ --}}
    <div id="pendingDebtsContent" class="space-y-5">

        {{-- Loading --}}
        <div id="loading-active" class="flex flex-col items-center justify-center py-24 bg-white rounded-2xl border border-gray-100">
            <div class="relative w-12 h-12 mb-4">
                <div class="absolute inset-0 rounded-full border-4 border-gray-100"></div>
                <div class="absolute inset-0 rounded-full border-4 border-red-400 border-t-transparent animate-spin"></div>
            </div>
            <p class="text-xs font-black text-gray-400 uppercase tracking-widest">Loading Financial Records…</p>
        </div>

        {{-- Driver Cards Grid --}}
        <div id="driver-cards-grid" class="hidden">
            <div id="cards-row" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5 gap-4"></div>

            {{-- Pagination Controls --}}
            <div id="pagination-controls" class="flex justify-center items-center gap-2 mt-6"></div>
        </div>

        {{-- Empty State --}}
        <div id="debts-empty" class="hidden flex flex-col items-center justify-center py-24 bg-white rounded-2xl border border-dashed border-gray-200">
            <div class="w-16 h-16 bg-emerald-50 rounded-full flex items-center justify-center mb-5 border-2 border-emerald-100">
                <i data-lucide="check-circle" class="w-8 h-8 text-emerald-500"></i>
            </div>
            <h4 class="text-base font-black text-gray-800 mb-1">Zero Active Liabilities</h4>
            <p class="text-sm text-gray-400 font-medium">All driver charges have been fully settled.</p>
        </div>

        {{-- No search results --}}
        <div id="debts-no-results" class="hidden text-center py-16 bg-white rounded-2xl border border-dashed border-gray-200">
            <i data-lucide="search-x" class="w-10 h-10 text-gray-300 mx-auto mb-3"></i>
            <p class="text-sm font-bold text-gray-400">No results found.</p>
        </div>
    </div>

    {{-- ══════════════════════
         HISTORY TAB
    ══════════════════════ --}}
    <div id="debtHistoryContent" class="hidden space-y-6">

        <div id="loading-history" class="hidden flex flex-col items-center justify-center py-24 bg-white rounded-2xl border border-gray-100">
            <div class="relative w-12 h-12 mb-4">
                <div class="absolute inset-0 rounded-full border-4 border-gray-100"></div>
                <div class="absolute inset-0 rounded-full border-4 border-emerald-400 border-t-transparent animate-spin"></div>
            </div>
            <p class="text-xs font-black text-emerald-500 uppercase tracking-widest">Reconstructing Logs…</p>
        </div>

        <div id="history-list" class="hidden">
            {{-- History Sub-Tabs --}}
            <div class="flex items-center justify-center mb-8 mt-2">
                <div class="inline-flex bg-gray-100/80 p-1 rounded-xl border border-gray-200/60 shadow-inner">
                    <button id="htab-cashin" onclick="switchHistoryTab('cashin')" class="px-6 py-2.5 text-xs font-black rounded-lg transition-all bg-white text-gray-900 shadow-sm border border-gray-200 flex items-center gap-2">
                        <i data-lucide="banknote" class="w-4 h-4 text-blue-500"></i> Cash-In Logs
                    </button>
                    <button id="htab-settled" onclick="switchHistoryTab('settled')" class="px-6 py-2.5 text-xs font-black rounded-lg transition-all text-gray-500 hover:text-gray-900 border border-transparent flex items-center gap-2">
                        <i data-lucide="check-circle" class="w-4 h-4 text-emerald-500"></i> Settled Accounts
                    </button>
                </div>
            </div>

            <section id="sec-cashin">
                <div id="payment-logs-list" class="space-y-6"></div>
            </section>
            
            <section id="sec-settled" class="hidden">
                <div id="settled-debts-list" class="space-y-6"></div>
            </section>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
/* ─── State ───────────────────────────────────────────── */
let allDebtsData   = [];
let allHistoryData = { settled: [], payments: [] };
let selectedDriverId = null;

/* Pagination State */
let currentPage = 1;
const itemsPerPage = 10;
let currentSearchTerm = '';

/* ─── Init ────────────────────────────────────────────── */
document.addEventListener('DOMContentLoaded', function () {
    if (typeof lucide !== 'undefined') lucide.createIcons();
    fetchPendingDebts();
    fetchDebtHistory();

    /* Autofill prevention */
    const si = document.getElementById('searchInput');
    setTimeout(() => { si.value = ''; si.removeAttribute('readonly'); }, 120);
    si.addEventListener('focus', function () {
        this.removeAttribute('readonly');
        if (this.value && this.value.includes('@')) this.value = '';
    }, { once: true });
    si.addEventListener('input', e => {
        const val = e.target.value.toLowerCase();
        if (document.getElementById('tab-active').classList.contains('active')) {
            filterCards(val);
        } else {
            renderHistoryList(val);
        }
    });
});

/* ─── Tab switcher ────────────────────────────────────── */
function switchTab(tab) {
    const tabA  = document.getElementById('tab-active');
    const tabH  = document.getElementById('tab-history');
    const contA = document.getElementById('pendingDebtsContent');
    const contH = document.getElementById('debtHistoryContent');
    const srchInput = document.getElementById('searchInput');

    if (tab === 'active') {
        tabA.classList.add('active');    tabH.classList.remove('active');
        tabH.classList.add('text-gray-500'); tabA.classList.remove('text-gray-500');
        contA.classList.remove('hidden'); contH.classList.add('hidden');
        srchInput.placeholder = "Search driver or plate…";
        renderCards(srchInput.value.toLowerCase(), 1);
    } else {
        tabH.classList.add('active');    tabA.classList.remove('active');
        tabA.classList.add('text-gray-500'); tabH.classList.remove('text-gray-500');
        contH.classList.remove('hidden'); contA.classList.add('hidden');
        srchInput.placeholder = "Search driver, plate, or date (e.g. Apr 22)…";
        renderHistoryList(srchInput.value.toLowerCase());
    }
}

/* ─── History Sub-Tab switcher ────────────────────────── */
function switchHistoryTab(tab) {
    const btnC = document.getElementById('htab-cashin');
    const btnS = document.getElementById('htab-settled');
    const secC = document.getElementById('sec-cashin');
    const secS = document.getElementById('sec-settled');

    if (tab === 'cashin') {
        btnC.className = "px-6 py-2.5 text-xs font-black rounded-lg transition-all bg-white text-gray-900 shadow-sm border border-gray-200 flex items-center gap-2";
        btnS.className = "px-6 py-2.5 text-xs font-black rounded-lg transition-all text-gray-500 hover:text-gray-900 border border-transparent flex items-center gap-2";
        secC.classList.remove('hidden');
        secS.classList.add('hidden');
    } else {
        btnS.className = "px-6 py-2.5 text-xs font-black rounded-lg transition-all bg-white text-gray-900 shadow-sm border border-gray-200 flex items-center gap-2";
        btnC.className = "px-6 py-2.5 text-xs font-black rounded-lg transition-all text-gray-500 hover:text-gray-900 border border-transparent flex items-center gap-2";
        secS.classList.remove('hidden');
        secC.classList.add('hidden');
    }
}

/* ─── Fetch active debts ──────────────────────────────── */
function fetchPendingDebts() {
    fetch('{{ route('driver-management.pending-debts') }}', {
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(data => {
        document.getElementById('loading-active').classList.add('hidden');

        if (!data.success || !data.debts || data.debts.length === 0) {
            allDebtsData = [];
            document.getElementById('debts-empty').classList.remove('hidden');
            updateActiveStats();
            return;
        }

        allDebtsData = data.debts;
        updateActiveStats();
        renderCards();
        document.getElementById('driver-cards-grid').classList.remove('hidden');
    })
    .catch(() => {
        document.getElementById('loading-active').innerHTML =
            `<div class="text-center py-10 text-red-500 font-bold text-sm">Failed to load records. Please refresh.</div>`;
    });
}

/* ─── Stat cards ──────────────────────────────────────── */
function updateActiveStats() {
    let total = 0;
    allDebtsData.forEach(d => { total += parseFloat(d.total_remaining); });
    document.getElementById('stat-debtors').textContent = allDebtsData.length;
    document.getElementById('stat-total-pending').textContent =
        '₱' + total.toLocaleString('en-PH', { minimumFractionDigits: 2 });
}

/* ─── Debt type helper ────────────────────────────────── */
function debtMeta(debt) {
    const d = (debt.description || '').toLowerCase();
    if (debt.is_boundary_shortage)
        return { icon:'trending-down', label:'Boundary Shortage', badge:'badge-shortage', color:'#be123c', dotCls:'bg-rose-400' };
    if (d.includes('damage') || d.includes('accident'))
        return { icon:'car-front',     label:'Vehicle Damage',    badge:'badge-damage',   color:'#c2410c', dotCls:'bg-orange-400' };
    if (d.includes('part') || d.includes('missing'))
        return { icon:'wrench',        label:'Missing Parts',     badge:'badge-parts',    color:'#b45309', dotCls:'bg-amber-400' };
    return     { icon:'alert-triangle',label:'General Liability', badge:'badge-general',  color:'#475569', dotCls:'bg-slate-400' };
}

/* ─── Render driver summary CARDS ─────────────────────── */
function renderCards(searchTerm = currentSearchTerm, page = 1) {
    currentSearchTerm = searchTerm;
    currentPage = page;

    const grid = document.getElementById('cards-row');
    const noRes = document.getElementById('debts-no-results');
    const pagination = document.getElementById('pagination-controls');
    let html = '';

    /* 1. Filter data based on search */
    const filteredData = allDebtsData.filter(driver => {
        if (!searchTerm) return true;
        return driver.driver_name.toLowerCase().includes(searchTerm) ||
               (driver.unit_plate && driver.unit_plate.toLowerCase().includes(searchTerm));
    });

    /* 2. Slice for pagination */
    const totalPages = Math.ceil(filteredData.length / itemsPerPage);
    if (currentPage > totalPages && totalPages > 0) currentPage = totalPages;

    const startIndex = (currentPage - 1) * itemsPerPage;
    const paginatedData = filteredData.slice(startIndex, startIndex + itemsPerPage);

    /* 3. Render cards */
    paginatedData.forEach(driver => {
        const initials   = driver.driver_name.trim().split(' ').slice(0,2).map(n=>n[0]).join('').toUpperCase();
        const totalDebt  = parseFloat(driver.total_remaining) +
                           driver.debts.reduce((s,d) => s + parseFloat(d.total_paid), 0);
        const totalPaid  = driver.debts.reduce((s,d) => s + parseFloat(d.total_paid), 0);
        const paidPct    = totalDebt > 0 ? Math.min(100,(totalPaid / totalDebt * 100)) : 0;
        const remaining  = parseFloat(driver.total_remaining);

        const firstMeta  = debtMeta(driver.debts[0] || {});
        const colors = ['#0f172a','#1e3a5f','#7c3aed','#0369a1','#065f46','#92400e','#991b1b'];
        const avatarBg = colors[driver.driver_name.charCodeAt(0) % colors.length];

        html += `
        <div class="driver-card bg-white border-2 border-gray-200 rounded-2xl p-5 select-none"
             id="dcard-${driver.driver_id}"
             onclick="openDriverPanel(${driver.driver_id})">

            {{-- Top row: avatar + name --}}
            <div class="flex items-center gap-3 mb-4">
                <div class="w-12 h-12 rounded-full flex items-center justify-center shrink-0 text-white font-black text-sm shadow-md"
                     style="background:${avatarBg};">
                    ${initials}
                </div>
                <div class="flex-1 min-w-0">
                    <h4 class="text-sm font-black text-gray-900 truncate">${driver.driver_name}</h4>
                    <div class="flex items-center gap-1.5 mt-0.5">
                        <i data-lucide="car" class="w-3 h-3 text-gray-400 shrink-0"></i>
                        <span class="text-[11px] font-bold text-gray-400 truncate">${driver.unit_plate || 'No Unit Assigned'}</span>
                    </div>
                </div>
            </div>

            <div class="border-t border-gray-100 mb-4"></div>

            <div class="space-y-3">
                <div class="flex justify-between items-end">
                    <div>
                        <p class="text-[9px] font-black uppercase tracking-widest text-gray-400 mb-0.5">Outstanding Balance</p>
                        <p class="text-xl font-black text-red-600">₱${remaining.toLocaleString('en-PH',{minimumFractionDigits:2})}</p>
                    </div>
                    <div class="text-right">
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[9px] font-black uppercase bg-slate-100 text-slate-600 border border-slate-200">
                            ${driver.debts.length} item${driver.debts.length > 1 ? 's' : ''}
                        </span>
                    </div>
                </div>

                <div>
                    <div class="flex justify-between items-center mb-1.5">
                        <span class="text-[9px] font-black uppercase tracking-widest text-gray-400">Settlement Progress</span>
                        <span class="text-[10px] font-black text-gray-500">${paidPct.toFixed(0)}%</span>
                    </div>
                    <div class="pbar-track">
                        <div class="pbar-fill" style="width:${paidPct}%"></div>
                    </div>
                </div>

                <div class="flex items-center gap-1.5 pt-1">
                    <i data-lucide="hand-click" class="w-3 h-3 text-slate-400"></i>
                    <span class="text-[10px] font-bold text-slate-400">Click to view &amp; pay liabilities</span>
                </div>
            </div>
        </div>`;
    });

    grid.innerHTML = html;
    lucide.createIcons();

    /* 4. Render Pagination Controls */
    let pagHtml = '';
    if (totalPages > 1) {
        pagHtml += `<button onclick="renderCards(currentSearchTerm, ${currentPage - 1})" ${currentPage === 1 ? 'disabled' : ''} class="w-8 h-8 flex items-center justify-center rounded-lg border border-gray-200 bg-white text-gray-600 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"><i data-lucide="chevron-left" class="w-4 h-4"></i></button>`;

        for (let i = 1; i <= totalPages; i++) {
            if (i === currentPage) {
                pagHtml += `<button class="w-8 h-8 flex items-center justify-center rounded-lg bg-slate-900 text-white font-black text-xs shadow-md">${i}</button>`;
            } else {
                pagHtml += `<button onclick="renderCards(currentSearchTerm, ${i})" class="w-8 h-8 flex items-center justify-center rounded-lg border border-gray-200 bg-white text-gray-600 font-bold text-xs hover:bg-gray-50 transition-colors">${i}</button>`;
            }
        }

        pagHtml += `<button onclick="renderCards(currentSearchTerm, ${currentPage + 1})" ${currentPage === totalPages ? 'disabled' : ''} class="w-8 h-8 flex items-center justify-center rounded-lg border border-gray-200 bg-white text-gray-600 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"><i data-lucide="chevron-right" class="w-4 h-4"></i></button>`;
    }
    pagination.innerHTML = pagHtml;
    lucide.createIcons();

    /* Visibility handling */
    noRes.classList.toggle('hidden', filteredData.length > 0 || !searchTerm);
    if (filteredData.length === 0 && !searchTerm) {
        document.getElementById('driver-cards-grid').classList.add('hidden');
        document.getElementById('debts-empty').classList.remove('hidden');
    } else {
        document.getElementById('driver-cards-grid').classList.remove('hidden');
    }
}

/* ─── Filter cards by search ──────────────────────────── */
function filterCards(term) {
    closeModal();
    renderCards(term, 1);
}

/* ─── Close modal ────────────────────────────────────── */
function closeModal() {
    selectedDriverId = null;
    document.getElementById('driver-modal-overlay').classList.remove('open');
    document.body.style.overflow = '';
    document.querySelectorAll('.driver-card').forEach(c => c.classList.remove('selected'));
    lucide.createIcons();
}

/* ─── Backdrop click ─────────────────────────────────── */
function handleModalBackdropClick(e) {
    if (e.target === document.getElementById('driver-modal-overlay')) closeModal();
}

/* ─── Open driver modal ──────────────────────────────── */
function openDriverPanel(driverId) {
    const driver = allDebtsData.find(d => d.driver_id == driverId);
    if (!driver) return;

    /* Toggle off if same card */
    if (selectedDriverId === driverId) { closeModal(); return; }

    /* Highlight selected card */
    document.querySelectorAll('.driver-card').forEach(c => c.classList.remove('selected'));
    const card = document.getElementById('dcard-' + driverId);
    if (card) card.classList.add('selected');

    selectedDriverId = driverId;
    renderModal(driver);

    /* Open overlay */
    document.getElementById('driver-modal-overlay').classList.add('open');
    document.body.style.overflow = 'hidden';
}

/* ─── ESC key closes modal ───────────────────────────── */
document.addEventListener('keydown', e => { if (e.key === 'Escape') closeModal(); });

/* ─── Render modal content ──────────────────────────── */
function renderModal(driver) {
    const initials = driver.driver_name.trim().split(' ').slice(0,2).map(n=>n[0]).join('').toUpperCase();
    const colors   = ['#0f172a','#1e3a5f','#7c3aed','#0369a1','#065f46','#92400e','#991b1b'];
    const avatarBg = colors[driver.driver_name.charCodeAt(0) % colors.length];
    const totalPaid = driver.debts.reduce((s,d)=>s+parseFloat(d.total_paid),0);
    const totalDebt = parseFloat(driver.total_remaining) + totalPaid;
    const paidPct   = totalDebt > 0 ? Math.min(100,(totalPaid/totalDebt*100)) : 0;

    /* Build debt item rows */
    let rows = '';
    driver.debts.forEach((debt, idx) => {
        const meta    = debtMeta(debt);
        const charge  = parseFloat(debt.total_charge);
        const paid    = parseFloat(debt.total_paid);
        const balance = parseFloat(debt.remaining_balance);
        const pct     = charge > 0 ? Math.min(100,(paid/charge*100)).toFixed(0) : 0;
        const dateStr = new Date(debt.timestamp || debt.date)
            .toLocaleDateString('en-PH', { month:'short', day:'numeric', year:'numeric' });

        rows += `
        <div class="debt-row border-b border-gray-100 last:border-b-0">
            <div class="p-6 flex flex-col lg:flex-row gap-6">

                {{-- Left: type + desc --}}
                <div class="flex-1 min-w-0">
                    <div class="flex flex-wrap items-center gap-2 mb-2">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[9px] font-black uppercase tracking-widest ${meta.badge}">
                            <i data-lucide="${meta.icon}" class="w-3 h-3"></i>
                            ${meta.label}
                        </span>
                        <span class="text-[10px] font-bold text-gray-400">${dateStr}</span>
                        <span class="text-[10px] font-bold text-gray-300">•</span>
                        <span class="text-[10px] font-bold text-gray-400">Item #${idx + 1}</span>
                    </div>
                    <p class="text-sm font-bold text-gray-800 leading-snug mb-4">${debt.description}</p>

                    {{-- Amounts row --}}
                    <div class="grid grid-cols-3 gap-3">
                        <div class="bg-gray-50 rounded-xl p-3 border border-gray-100">
                            <p class="text-[9px] font-black uppercase tracking-widest text-gray-400 mb-0.5">Total Charge</p>
                            <p class="text-sm font-black text-gray-800">₱${charge.toLocaleString('en-PH',{minimumFractionDigits:2})}</p>
                        </div>
                        <div class="bg-emerald-50 rounded-xl p-3 border border-emerald-100">
                            <p class="text-[9px] font-black uppercase tracking-widest text-emerald-600 mb-0.5">Amount Paid</p>
                            <p class="text-sm font-black text-emerald-700">₱${paid.toLocaleString('en-PH',{minimumFractionDigits:2})}</p>
                        </div>
                        <div class="bg-red-50 rounded-xl p-3 border border-red-100">
                            <p class="text-[9px] font-black uppercase tracking-widest text-red-500 mb-0.5">Remaining</p>
                            <p class="text-sm font-black text-red-700">₱${balance.toLocaleString('en-PH',{minimumFractionDigits:2})}</p>
                        </div>
                    </div>

                    {{-- Progress --}}
                    <div class="flex items-center gap-2 mt-3">
                        <div class="pbar-track flex-1">
                            <div class="pbar-fill" style="width:${pct}%"></div>
                        </div>
                        <span class="text-[10px] font-black text-gray-400 shrink-0">${pct}% paid</span>
                    </div>
                </div>

                {{-- Right: payment box --}}
                <div class="lg:w-56 shrink-0">
                    <div class="bg-white border-2 border-gray-200 rounded-2xl p-4 h-full flex flex-col justify-between">
                        <div>
                            <p class="text-[9px] font-black uppercase tracking-widest text-red-500 mb-1">Balance to Pay</p>
                            <p class="text-2xl font-black text-red-600 leading-none mb-4">₱${balance.toLocaleString('en-PH',{minimumFractionDigits:2})}</p>
                        </div>
                        <form onsubmit="return handlePaymentSubmit(event, this, '${driver.driver_name}', '${meta.label}', ${balance}, ${debt.id})"
                              class="space-y-2">
                            <p class="text-[9px] font-black uppercase tracking-widest text-gray-400">Enter Payment Amount</p>
                            <div class="flex gap-2">
                                <div class="relative flex-1">
                                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs font-black">₱</span>
                                    <input type="number" name="payment_amount"
                                        step="0.01" min="0.01" max="${balance}" required
                                        placeholder="0.00"
                                        oninput="if(parseFloat(this.value)>${balance}) this.value=${balance};"
                                        class="pay-input w-full pl-7 pr-2 py-2.5 text-sm font-black border border-gray-200 rounded-xl">
                                </div>
                                <button type="submit"
                                    class="px-4 py-2.5 bg-slate-900 hover:bg-red-600 text-white text-xs font-black rounded-xl transition-colors whitespace-nowrap shadow-sm">
                                    Pay
                                </button>
                            </div>
                            <button type="button"
                                onclick="const i=this.closest('form').querySelector('input[name=payment_amount]');i.value=${balance};"
                                class="w-full text-[10px] font-black text-slate-500 hover:text-red-600 transition-colors text-center py-1">
                                Pay full balance
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>`;
    });

    /* Inject into modal elements */
    document.getElementById('driver-modal-header').innerHTML = `
        <div class="bg-gradient-to-r from-slate-900 to-slate-800 px-6 py-5 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <div class="w-11 h-11 rounded-full flex items-center justify-center text-white font-black text-sm border-2 border-white/20"
                     style="background:${avatarBg}80;">
                    ${initials}
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h3 class="text-base font-black text-white">${driver.driver_name}</h3>
                        <span class="px-2 py-0.5 bg-white/10 text-white text-[9px] font-black uppercase rounded-full border border-white/20">
                            ${driver.debts.length} Liabilit${driver.debts.length > 1 ? 'ies' : 'y'}
                        </span>
                    </div>
                    <div class="flex items-center gap-3 mt-0.5">
                        <span class="text-[10px] font-bold text-gray-400 flex items-center gap-1">
                            <i data-lucide="car" class="w-3 h-3"></i> ${driver.unit_plate || 'No Unit'}
                        </span>
                        <span class="text-gray-600">•</span>
                        <span class="text-[10px] font-bold text-gray-400">${paidPct.toFixed(0)}% settled overall</span>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-4">
                <div class="text-right">
                    <p class="text-[9px] font-black uppercase tracking-widest text-red-300">Total Outstanding</p>
                    <p class="text-2xl font-black text-white">₱${parseFloat(driver.total_remaining).toLocaleString('en-PH',{minimumFractionDigits:2})}</p>
                </div>
                <button onclick="closeModal()"
                    class="w-8 h-8 rounded-full bg-white/10 hover:bg-white/20 flex items-center justify-center transition-colors border border-white/20">
                    <i data-lucide="x" class="w-4 h-4 text-white"></i>
                </button>
            </div>
        </div>`;

    document.getElementById('driver-modal-count').textContent = `${driver.debts.length} pending item${driver.debts.length > 1 ? 's' : ''}`;
    document.getElementById('driver-modal-subheader').classList.remove('hidden');
    document.getElementById('driver-modal-body').innerHTML = rows;

    lucide.createIcons();
}

/* ─── Payment modal + submit ─────────────────────────── */
function handlePaymentSubmit(e, form, driverName, debtType, maxBalance, debtId) {
    e.preventDefault();
    const amount = parseFloat(form.payment_amount.value);
    if (!amount || amount <= 0) return false;

    Swal.fire({
        title: '<span style="font-size:1rem;font-weight:900;color:#0f172a">Confirm Payment</span>',
        html: `
            <div style="text-align:left;margin-top:12px;display:flex;flex-direction:column;gap:8px;">
                <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;padding:12px 16px;">
                    <p style="font-size:9px;font-weight:900;color:#94a3b8;letter-spacing:.12em;text-transform:uppercase;margin:0 0 2px">Driver</p>
                    <p style="font-size:14px;font-weight:800;color:#0f172a;margin:0">${driverName}</p>
                </div>
                <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;padding:12px 16px;">
                    <p style="font-size:9px;font-weight:900;color:#94a3b8;letter-spacing:.12em;text-transform:uppercase;margin:0 0 2px">Payment For</p>
                    <p style="font-size:13px;font-weight:700;color:#334155;margin:0">${debtType}</p>
                </div>
                <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:12px;padding:14px 16px;">
                    <p style="font-size:9px;font-weight:900;color:#16a34a;letter-spacing:.12em;text-transform:uppercase;margin:0 0 4px">Cash Received</p>
                    <p style="font-size:28px;font-weight:900;color:#15803d;margin:0">₱${amount.toLocaleString('en-PH',{minimumFractionDigits:2})}</p>
                </div>
                <p style="font-size:11px;color:#94a3b8;font-weight:600;margin:0">This permanently records the cash entry and reduces the driver's outstanding balance.</p>
            </div>`,
        showCancelButton: true,
        confirmButtonColor: '#0f172a',
        cancelButtonColor:  '#94a3b8',
        confirmButtonText:  'Accept Payment',
        cancelButtonText:   'Cancel',
        customClass: {
            confirmButton: 'rounded-xl font-black text-xs px-6 py-3',
            cancelButton:  'rounded-xl font-black text-xs px-6 py-3',
            popup:         'rounded-2xl'
        }
    }).then(result => {
        if (!result.isConfirmed) return;
        Swal.fire({ title:'Processing…', allowOutsideClick:false, didOpen:()=>Swal.showLoading() });

        const fd = new FormData();
        fd.append('_token', '{{ csrf_token() }}');
        fd.append('debt_id', debtId);
        fd.append('payment_amount', amount);

        fetch('{{ route('driver-management.pay-debt') }}', {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: fd
        })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                Swal.fire({ icon:'success', title:'Payment Recorded!', text:'The liability has been updated.', timer:2000, showConfirmButton:false });
                const prevSelected = selectedDriverId;
                fetchPendingDebts();
                fetchDebtHistory();
                /* Re-open same driver panel after refresh */
                setTimeout(() => {
                    if (prevSelected) openDriverPanel(prevSelected);
                }, 900);
            } else {
                throw new Error(res.message || 'Payment failed.');
            }
        })
        .catch(() => Swal.fire('Error', 'Could not process payment. Please try again.', 'error'));
    });

    return false;
}

/* ─── Fetch history ───────────────────────────────────── */
function getDateCategory(dateStr) {
    const d = new Date(dateStr);
    const today = new Date();
    today.setHours(0,0,0,0);
    const itemDate = new Date(d);
    itemDate.setHours(0,0,0,0);
    const diffTime = today - itemDate;
    const diffDays = Math.floor(diffTime / (1000 * 60 * 60 * 24));

    if (diffDays === 0) return 'Today';
    if (diffDays === 1) return 'Yesterday';
    if (diffDays <= 7) return 'Past 7 Days';
    return 'Older';
}

function renderGroupedList(items, type) {
    if (items.length === 0) {
        return `<div class="py-10 text-center text-sm font-semibold text-gray-400 bg-gray-50 rounded-xl border border-dashed border-gray-200">No records found.</div>`;
    }

    const groups = {};
    items.forEach(item => {
        const cat = getDateCategory(item.date);
        if (!groups[cat]) groups[cat] = [];
        groups[cat].push(item);
    });

    const order = ['Today', 'Yesterday', 'Past 7 Days', 'Older'];
    let html = '';

    order.forEach(cat => {
        if (!groups[cat] || groups[cat].length === 0) return;

        html += `
            <div class="mb-4">
                <div class="flex items-center gap-3 mb-3 pl-2">
                    <span class="text-[10px] font-black uppercase tracking-widest px-2.5 py-1 bg-slate-900 text-white rounded-md shadow-sm">
                        ${cat}
                    </span>
                    <div class="h-px bg-gray-200 flex-1"></div>
                </div>
                <div class="grid grid-cols-1 ${type === 'payment' ? 'sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4' : 'md:grid-cols-2 lg:grid-cols-3'} gap-3">
        `;

        groups[cat].forEach(item => {
            if (type === 'payment') {
                html += `
                    <div class="bg-white border border-gray-100 rounded-xl p-5 hover:shadow-md hover:-translate-y-0.5 hover:border-blue-200 transition-all relative overflow-hidden group">
                        <div class="absolute inset-y-0 left-0 w-1 bg-emerald-400 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                        <div class="flex justify-between items-start mb-3">
                            <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center border border-blue-100">
                                <i data-lucide="banknote" class="w-4 h-4"></i>
                            </div>
                            <div class="text-right">
                                <p class="text-lg font-black text-emerald-600">+₱${parseFloat(item.amount).toLocaleString('en-PH',{minimumFractionDigits:2})}</p>
                                <p class="text-[9px] font-black uppercase text-gray-400">Cash In</p>
                            </div>
                        </div>
                        <div class="flex items-center justify-between mt-2 mb-1">
                            <p class="text-[10px] font-black uppercase tracking-widest text-blue-500">
                                ${new Date(item.date).toLocaleDateString('en-PH',{month:'short',day:'numeric',year:'numeric'})}
                            </p>
                            <span class="text-[9px] font-bold text-gray-400">${new Date(item.date).toLocaleTimeString('en-PH',{hour:'2-digit',minute:'2-digit'})}</span>
                        </div>
                        <p class="text-xs font-bold text-gray-600 leading-snug line-clamp-2">${item.description}</p>
                    </div>`;
            } else {
                html += `
                    <div class="bg-white border border-gray-100 rounded-xl p-5 flex items-center gap-4 hover:border-emerald-200 hover:shadow-md transition-all relative overflow-hidden group">
                        <div class="absolute inset-y-0 left-0 w-1 bg-emerald-400 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                        <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0 border border-emerald-100">
                            <i data-lucide="check-circle" class="w-5 h-5"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h5 class="text-sm font-black text-gray-900 truncate">
                                ${item.driver_name} <span class="text-[10px] font-bold text-gray-400 ml-1">${item.unit_plate||''}</span>
                            </h5>
                            <p class="text-xs text-gray-500 truncate mt-0.5">${item.description}</p>
                            <p class="text-[9px] font-black uppercase tracking-widest text-emerald-600 mt-1.5 flex items-center gap-1">
                                <i data-lucide="clock" class="w-3 h-3"></i> Settled ${new Date(item.date).toLocaleDateString('en-PH')}
                            </p>
                        </div>
                    </div>`;
            }
        });

        html += `</div></div>`;
    });

    return html;
}

function renderHistoryList(searchTerm = '') {
    if (!allHistoryData || !allHistoryData.settled) return;

    let filteredSettled = allHistoryData.settled;
    let filteredPayments = allHistoryData.payments;

    if (searchTerm) {
        filteredSettled = allHistoryData.settled.filter(item => {
            const dateStr = new Date(item.date).toLocaleDateString('en-PH', {month:'short', day:'numeric', year:'numeric'}).toLowerCase();
            return (item.driver_name && item.driver_name.toLowerCase().includes(searchTerm)) ||
                   (item.unit_plate && item.unit_plate.toLowerCase().includes(searchTerm)) ||
                   dateStr.includes(searchTerm) ||
                   item.date.includes(searchTerm);
        });

        filteredPayments = allHistoryData.payments.filter(item => {
            const dateStr = new Date(item.date).toLocaleDateString('en-PH', {month:'short', day:'numeric', year:'numeric'}).toLowerCase();
            return dateStr.includes(searchTerm) || 
                   item.date.includes(searchTerm) ||
                   (item.description && item.description.toLowerCase().includes(searchTerm));
        });
    }

    const settledList = document.getElementById('settled-debts-list');
    settledList.innerHTML = renderGroupedList(filteredSettled, 'settled');

    const payList = document.getElementById('payment-logs-list');
    payList.innerHTML = renderGroupedList(filteredPayments, 'payment');
    
    lucide.createIcons();
}

function fetchDebtHistory() {
    fetch('{{ route('driver-management.debt-history') }}', {
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(data => {
        if (!data.success) throw new Error();
        allHistoryData = data;

        let totalCollections = 0;
        data.payments.forEach(p => { totalCollections += parseFloat(p.amount); });
        document.getElementById('stat-collections').textContent =
            '₱' + totalCollections.toLocaleString('en-PH', { minimumFractionDigits: 2 });

        renderHistoryList(document.getElementById('searchInput').value.toLowerCase());

        document.getElementById('loading-history').classList.add('hidden');
        document.getElementById('history-list').classList.remove('hidden');
        lucide.createIcons();
    })
    .catch(() => {
        document.getElementById('loading-history').innerHTML =
            `<div class="text-center py-10 text-red-500 font-bold text-sm">Failed to load history.</div>`;
    });
}
</script>
@endpush
