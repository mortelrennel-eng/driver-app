@extends('layouts.app')

@section('title', 'Daily Financial Ledger - Euro System')
@section('page-heading', 'Daily History & Ledger')
@section('page-subheading', 'Detailed daily tracking of collections, expenses, maintenance, and net yield')

@section('content')
    {{-- Back to Dashboard --}}
    <a href="{{ route('analytics.index') }}" 
       class="inline-flex items-center gap-2 text-slate-500 hover:text-indigo-600 transition-colors font-black text-xs uppercase tracking-widest mb-6">
        <i data-lucide="arrow-left" class="w-4 h-4"></i> Back to Intelligence Dashboard
    </a>

    {{-- ── Date Filter Command Bar ────────────────────────────────────────── --}}
    <div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-6 mb-8">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
            <div>
                <h3 class="text-base font-black text-slate-800 flex items-center gap-2">
                    <i data-lucide="filter" class="w-5 h-5 text-indigo-500"></i>
                    Ledger Search Filters
                </h3>
                <p class="text-xs text-slate-500 mt-0.5">Filter daily history records by specific date range</p>
            </div>
            
            <form method="GET" action="{{ route('analytics.history') }}" class="flex flex-col sm:flex-row items-end gap-3 flex-1 lg:max-w-2xl">
                <div class="flex-1 w-full">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5 block">
                        Date Range
                    </label>
                    <div class="flex items-center gap-2 w-full">
                        <input type="date" name="date_from" value="{{ $date_from }}"
                            class="block w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all text-sm font-semibold">
                        <span class="text-slate-400 font-bold text-xs shrink-0">→</span>
                        <input type="date" name="date_to" value="{{ $date_to }}"
                            class="block w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all text-sm font-semibold">
                    </div>
                </div>
                <div class="flex gap-2 w-full sm:w-auto">
                    <button type="submit"
                        class="flex-1 sm:flex-none px-6 py-2.5 bg-indigo-600 text-white rounded-xl font-black text-xs hover:bg-indigo-700 active:bg-indigo-800 transition-all shadow-md shadow-indigo-200/50 flex items-center justify-center gap-2 h-[42px]">
                        <i data-lucide="search" class="w-4 h-4"></i> Search Date
                    </button>
                    <a href="{{ route('analytics.history') }}" 
                       class="px-4 py-2.5 bg-slate-100 border border-slate-200 text-slate-600 rounded-xl text-xs font-bold hover:bg-slate-200 transition-all flex items-center justify-center h-[42px]"
                       title="Reset Filters">
                        <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- ── Financial Summary Cards ─────────────────────────────────────────── --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        {{-- Total Collections --}}
        <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm hover:shadow-lg transition-all duration-300 relative overflow-hidden group">
            <div class="absolute -right-6 -top-6 w-24 h-24 bg-emerald-50 rounded-full opacity-60 group-hover:scale-120 transition-transform"></div>
            <div class="relative z-10 flex items-start justify-between mb-3">
                <div class="p-3 bg-emerald-50 rounded-2xl text-emerald-600">
                    <i data-lucide="wallet" class="w-6 h-6"></i>
                </div>
                <span class="text-[9px] font-black text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-md uppercase tracking-wider">Revenue</span>
            </div>
            <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Total Collections</h4>
            <p class="text-2xl font-black text-slate-800">{{ formatCurrency($totals['revenue']) }}</p>
        </div>

        {{-- Total Expenses --}}
        <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm hover:shadow-lg transition-all duration-300 relative overflow-hidden group">
            <div class="absolute -right-6 -top-6 w-24 h-24 bg-rose-50 rounded-full opacity-60 group-hover:scale-120 transition-transform"></div>
            <div class="relative z-10 flex items-start justify-between mb-3">
                <div class="p-3 bg-rose-50 rounded-2xl text-rose-600">
                    <i data-lucide="receipt" class="w-6 h-6"></i>
                </div>
                <span class="text-[9px] font-black text-rose-600 bg-rose-50 px-2 py-0.5 rounded-md uppercase tracking-wider">Expenses</span>
            </div>
            <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Total Expenses</h4>
            <p class="text-2xl font-black text-slate-800 cursor-pointer hover:text-rose-600 transition-colors select-none"
               onclick="openExpensesBreakdown(null, '{{ $date_from }}', '{{ $date_to }}', 'All Expenses ({{ formatDate($date_from) }} to {{ formatDate($date_to) }})')">
                <span class="underline decoration-dashed decoration-slate-300 hover:decoration-rose-500">{{ formatCurrency($totals['expenses']) }}</span>
            </p>
        </div>

        {{-- Total Maintenance --}}
        <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm hover:shadow-lg transition-all duration-300 relative overflow-hidden group">
            <div class="absolute -right-6 -top-6 w-24 h-24 bg-amber-50 rounded-full opacity-60 group-hover:scale-120 transition-transform"></div>
            <div class="relative z-10 flex items-start justify-between mb-3">
                <div class="p-3 bg-amber-50 rounded-2xl text-amber-600">
                    <i data-lucide="wrench" class="w-6 h-6"></i>
                </div>
                <span class="text-[9px] font-black text-amber-600 bg-amber-50 px-2 py-0.5 rounded-md uppercase tracking-wider">Maintenance</span>
            </div>
            <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Total Maintenance</h4>
            <p class="text-2xl font-black text-slate-800">{{ formatCurrency($totals['maintenance']) }}</p>
        </div>

        {{-- Net Income --}}
        <div class="p-6 rounded-3xl border shadow-sm hover:shadow-lg transition-all duration-300 relative overflow-hidden group
            {{ $totals['net_profit'] >= 0 ? 'bg-white border-slate-200' : 'bg-rose-50/30 border-rose-100' }}">
            <div class="absolute -right-6 -top-6 w-24 h-24 rounded-full opacity-60 group-hover:scale-120 transition-transform
                {{ $totals['net_profit'] >= 0 ? 'bg-indigo-50' : 'bg-rose-100/50' }}"></div>
            <div class="relative z-10 flex items-start justify-between mb-3">
                <div class="p-3 rounded-2xl 
                    {{ $totals['net_profit'] >= 0 ? 'bg-indigo-50 text-indigo-600' : 'bg-rose-100 text-rose-600' }}">
                    <i data-lucide="banknote" class="w-6 h-6"></i>
                </div>
                <span class="text-[9px] font-black rounded-md uppercase tracking-wider
                    {{ $totals['net_profit'] >= 0 ? 'text-indigo-600 bg-indigo-50' : 'text-rose-600 bg-rose-100' }}">Net Yield</span>
            </div>
            <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Net Yield</h4>
            <p class="text-2xl font-black 
                {{ $totals['net_profit'] >= 0 ? 'text-indigo-700' : 'text-rose-700' }}">{{ formatCurrency($totals['net_profit']) }}</p>
        </div>
    </div>

    {{-- ── Daily Ledger Table Card ────────────────────────────────────────── --}}
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden mb-8">
        <div class="px-8 py-6 border-b border-slate-100 bg-slate-50/50 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h3 class="text-lg font-black text-slate-800">Daily Ledger Log</h3>
                <p class="text-xs text-slate-500">Breakdown of earnings, operating costs, repairs, and net margins per calendar date</p>
            </div>
            <div class="flex items-center gap-3">
                {{-- Quick Exports using active filters --}}
                <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest hidden sm:block">Export this period:</span>
                <a href="{{ route('analytics.export.csv', ['type' => 'revenue', 'date_from' => $date_from, 'date_to' => $date_to]) }}"
                   class="flex items-center gap-1.5 px-3 py-2 bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs font-bold rounded-xl hover:bg-emerald-100 transition-all">
                    <i data-lucide="download" class="w-3 h-3"></i> Revenue
                </a>
                <a href="{{ route('analytics.export.csv', ['type' => 'maintenance', 'date_from' => $date_from, 'date_to' => $date_to]) }}"
                   class="flex items-center gap-1.5 px-3 py-2 bg-amber-50 border border-amber-200 text-amber-700 text-xs font-bold rounded-xl hover:bg-amber-100 transition-all">
                    <i data-lucide="download" class="w-3 h-3"></i> Repairs
                </a>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-100 bg-slate-50/20">
                        <th class="text-left text-[10px] font-black text-slate-400 uppercase tracking-widest px-8 py-4">Date</th>
                        <th class="text-center text-[10px] font-black text-slate-400 uppercase tracking-widest py-4">Active Units</th>
                        <th class="text-right text-[10px] font-black text-slate-400 uppercase tracking-widest py-4">Daily Collection</th>
                        <th class="text-right text-[10px] font-black text-slate-400 uppercase tracking-widest py-4">Operating Expenses</th>
                        <th class="text-right text-[10px] font-black text-slate-400 uppercase tracking-widest py-4">Maintenance Spend</th>
                        <th class="text-right text-[10px] font-black text-slate-400 uppercase tracking-widest px-8 py-4">Net Yield</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($ledger as $row)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-8 py-4 font-bold text-slate-800">
                                {{ date('F d, Y', strtotime($row['date'])) }}
                            </td>
                            <td class="text-center py-4 font-bold text-slate-500">
                                @if($row['active_units'] > 0)
                                    <span class="px-2.5 py-1 bg-indigo-50 text-indigo-700 text-[10px] font-black rounded-lg border border-indigo-100">
                                        {{ $row['active_units'] }} Units
                                    </span>
                                @else
                                    <span class="text-slate-300 font-medium text-xs">0</span>
                                @endif
                            </td>
                            <td class="text-right py-4 font-bold text-emerald-600">
                                {{ $row['revenue'] > 0 ? '+ ' . formatCurrency($row['revenue']) : '—' }}
                                @if($row['shortage'] > 0)
                                    <p class="text-[9px] text-rose-500 font-bold mt-0.5">Shortage: -{{ formatCurrency($row['shortage']) }}</p>
                                @endif
                            </td>
                            <td class="text-right py-4 font-bold text-rose-500">
                                @if($row['expenses'] > 0)
                                    <span class="cursor-pointer hover:text-rose-700 transition-colors select-none underline decoration-dashed decoration-rose-300 hover:decoration-rose-500"
                                          onclick="openExpensesBreakdown('{{ $row['date'] }}', null, null, 'Expenses Breakdown — {{ date('F d, Y', strtotime($row['date'])) }}')">
                                        - {{ formatCurrency($row['expenses']) }}
                                    </span>
                                @else
                                    —
                                @endif
                            </td>
                            <td class="text-right py-4 font-bold text-amber-500">
                                {{ $row['maintenance'] > 0 ? '- ' . formatCurrency($row['maintenance']) : '—' }}
                            </td>
                            <td class="text-right px-8 py-4 font-black text-base
                                {{ $row['net_profit'] >= 0 ? 'text-indigo-600' : 'text-rose-600 bg-rose-50/20' }}">
                                {{ $row['net_profit'] >= 0 ? '+' : '' }}{{ formatCurrency($row['net_profit']) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-16 text-center">
                                <div class="w-16 h-16 bg-slate-50 text-slate-400 rounded-3xl flex items-center justify-center mx-auto mb-4 border border-slate-100">
                                    <i data-lucide="database" class="w-8 h-8"></i>
                                </div>
                                <h4 class="text-sm font-black text-slate-700 mb-1">No Ledger Data</h4>
                                <p class="text-xs text-slate-400 max-w-xs mx-auto leading-normal">No activity logs or financial records match the selected date filter.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ── Expenses Breakdown Modal ────────────────────────────────────────── --}}
    <div id="expenses-modal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        {{-- Backdrop --}}
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity bg-slate-900/60 backdrop-blur-sm" onclick="closeExpensesModal()"></div>

            {{-- Spacing helper to center modal --}}
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            {{-- Modal Content Card --}}
            <div class="inline-block align-bottom bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full border border-slate-100 animate-in fade-in zoom-in-95 duration-200">
                {{-- Header --}}
                <div class="px-6 py-5 bg-slate-50 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-black text-slate-800 flex items-center gap-2" id="modal-title">
                            <i data-lucide="receipt" class="w-5 h-5 text-rose-500"></i>
                            <span id="expenses-modal-title">Expenses Breakdown</span>
                        </h3>
                        <p class="text-xs text-slate-500 mt-0.5" id="expenses-modal-subtitle">Detailed itemized list of all operational disbursements</p>
                    </div>
                    <button type="button" onclick="closeExpensesModal()" class="p-1.5 bg-slate-100 hover:bg-slate-200 text-slate-400 hover:text-slate-600 rounded-xl transition-all">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                {{-- Body (Table) --}}
                <div class="px-6 py-6">
                    <div id="modal-loader" class="py-12 flex flex-col items-center justify-center gap-3">
                        <div class="w-8 h-8 border-4 border-indigo-500 border-t-transparent rounded-full animate-spin"></div>
                        <p class="text-xs text-slate-500 font-bold">Retrieving expenses records...</p>
                    </div>

                    <div id="modal-content-container" class="hidden">
                        <div class="max-h-[60vh] overflow-y-auto overflow-x-hidden rounded-2xl border border-slate-100 shadow-inner">
                            <table class="w-full text-sm table-fixed">
                                <thead>
                                    <tr class="bg-slate-50/50 border-b border-slate-100 sticky top-0 backdrop-blur-md">
                                        <th class="text-left text-[10px] font-black text-slate-400 uppercase tracking-widest px-4 py-3.5 w-[18%]">Category</th>
                                        <th class="text-left text-[10px] font-black text-slate-400 uppercase tracking-widest px-4 py-3.5 w-[32%]">Description</th>
                                        <th class="text-left text-[10px] font-black text-slate-400 uppercase tracking-widest px-4 py-3.5 w-[16%]">Vendor</th>
                                        <th class="text-center text-[10px] font-black text-slate-400 uppercase tracking-widest px-4 py-3.5 w-[15%]">Unit</th>
                                        <th class="text-center text-[10px] font-black text-slate-400 uppercase tracking-widest px-4 py-3.5 w-[9%]">Payment</th>
                                        <th class="text-right text-[10px] font-black text-slate-400 uppercase tracking-widest px-4 py-3.5 w-[10%]">Amount</th>
                                    </tr>
                                </thead>
                                <tbody id="expenses-breakdown-tbody" class="divide-y divide-slate-100">
                                    {{-- Dynamic contents --}}
                                </tbody>
                            </table>
                        </div>

                        {{-- Footer Summary --}}
                        <div class="mt-4 flex items-center justify-between bg-rose-50/40 border border-rose-100/50 rounded-2xl p-4">
                            <span class="text-xs font-black text-slate-500 uppercase tracking-wider">Total Aggregated Expense</span>
                            <span id="modal-total-amount" class="text-lg font-black text-rose-600">₱0.00</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function openExpensesBreakdown(date, dateFrom, dateTo, title) {
            const modal = document.getElementById('expenses-modal');
            const loader = document.getElementById('modal-loader');
            const container = document.getElementById('modal-content-container');
            const titleSpan = document.getElementById('expenses-modal-title');
            
            titleSpan.textContent = title;
            modal.classList.remove('hidden');
            document.body.classList.add('overflow-hidden');

            loader.classList.remove('hidden');
            container.classList.add('hidden');

            let url = '{{ route("analytics.expenses-breakdown") }}?';
            if (date) {
                url += 'date=' + encodeURIComponent(date);
            } else {
                url += 'date_from=' + encodeURIComponent(dateFrom) + '&date_to=' + encodeURIComponent(dateTo);
            }

            fetch(url)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.expenses.length > 0) {
                        const tbody = document.getElementById('expenses-breakdown-tbody');
                        tbody.innerHTML = '';

                        data.expenses.forEach(exp => {
                            const tr = document.createElement('tr');
                            tr.className = 'hover:bg-slate-50 transition-colors';

                            let dateText = '';
                            if (!date) {
                                const d = new Date(exp.date);
                                const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                                dateText = `<p class="text-[9px] text-slate-400 font-bold mt-0.5">${months[d.getMonth()]} ${d.getDate()}, ${d.getFullYear()}</p>`;
                            }

                            const catCell = document.createElement('td');
                            catCell.className = 'px-4 py-3 font-bold text-slate-800 whitespace-nowrap';
                            catCell.innerHTML = `
                                <span class="px-2 py-0.5 bg-slate-100 text-slate-600 rounded-md text-[10px] uppercase font-black tracking-wide border border-slate-200">
                                    ${exp.category}
                                </span>
                                ${dateText}
                            `;
                            tr.appendChild(catCell);

                            const descCell = document.createElement('td');
                            descCell.className = 'px-4 py-3 text-slate-600 text-xs font-semibold';
                            descCell.innerHTML = `<div class="truncate w-full" title="${exp.description || '—'}">${exp.description || '—'}</div>`;
                            tr.appendChild(descCell);

                            const vendorCell = document.createElement('td');
                            vendorCell.className = 'px-4 py-3 text-slate-600 text-xs font-bold';
                            vendorCell.innerHTML = `<div class="truncate w-full" title="${exp.vendor_name || '—'}">${exp.vendor_name || '—'}</div>`;
                            tr.appendChild(vendorCell);

                            const unitCell = document.createElement('td');
                            unitCell.className = 'px-4 py-3 text-center whitespace-nowrap';
                            unitCell.innerHTML = exp.plate_number 
                                ? `<span class="px-2.5 py-0.5 bg-indigo-50 text-indigo-700 text-[10px] font-black rounded-md border border-indigo-100 whitespace-nowrap">${exp.plate_number}</span>`
                                : `<span class="text-slate-300 font-medium text-xs">—</span>`;
                            tr.appendChild(unitCell);

                            const payCell = document.createElement('td');
                            payCell.className = 'px-4 py-3 text-center text-xs font-bold text-slate-500 whitespace-nowrap';
                            payCell.textContent = exp.payment_method ? exp.payment_method.toUpperCase() : '—';
                            tr.appendChild(payCell);

                            const amtCell = document.createElement('td');
                            amtCell.className = 'px-4 py-3 text-right font-black text-rose-600';
                            amtCell.textContent = '₱' + parseFloat(exp.amount).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                            tr.appendChild(amtCell);

                            tbody.appendChild(tr);
                        });

                        document.getElementById('modal-total-amount').textContent = data.total_formatted;
                        loader.classList.add('hidden');
                        container.classList.remove('hidden');
                    } else {
                        const tbody = document.getElementById('expenses-breakdown-tbody');
                        tbody.innerHTML = `
                            <tr>
                                <td colspan="6" class="py-12 text-center">
                                    <div class="w-12 h-12 bg-slate-50 text-slate-400 rounded-full flex items-center justify-center mx-auto mb-3 border border-slate-100">
                                        <i data-lucide="database" class="w-6 h-6"></i>
                                    </div>
                                    <h4 class="text-xs font-black text-slate-700 mb-1">No Expense Records</h4>
                                    <p class="text-[10px] text-slate-400 max-w-xs mx-auto leading-normal">No detailed expenses found for the requested criteria.</p>
                                </td>
                            </tr>
                        `;
                        document.getElementById('modal-total-amount').textContent = '₱0.00';
                        loader.classList.add('hidden');
                        container.classList.remove('hidden');
                    }
                    
                    if (window.lucide) {
                        lucide.createIcons();
                    }
                })
                .catch(error => {
                    console.error('Error fetching expenses:', error);
                    alert('Failed to load expenses details. Please try again.');
                    closeExpensesModal();
                });
        }

        function closeExpensesModal() {
            document.getElementById('expenses-modal').classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        }
    </script>
@endsection
