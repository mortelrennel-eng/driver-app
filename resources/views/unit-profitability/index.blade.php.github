@extends('layouts.app')

@section('title', 'Unit Profitability - Euro System')
@section('page-heading', 'Unit Profitability Analysis')
@section('page-subheading', 'Evaluate each unit\'s revenue versus expenses to determine profitability')

@section('content')
<style>
    .card-number-container {
        container-type: inline-size;
    }
    .auto-scale-text {
        font-size: 14px; /* Fallback */
        font-size: clamp(12px, 8cqw, 30px);
    }
</style>

    {{-- Overall Stats Cards (TOP) --}}
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 card-hover overflow-hidden card-number-container">
            <div class="px-2 py-4 sm:p-6 text-center">
                <p class="text-[9px] sm:text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Total Units</p>
                <div class="flex justify-center items-center h-10 sm:h-12">
                    <p class="auto-scale-text font-black text-gray-800 whitespace-nowrap">{{ $overview['total_units'] ?? 0 }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 card-hover overflow-hidden card-number-container">
            <div class="px-2 py-4 sm:p-6 text-center">
                <p class="text-[9px] sm:text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1 text-green-600">Total Boundary</p>
                <div class="flex justify-center items-center h-10 sm:h-12">
                    <p class="auto-scale-text font-black text-green-600 whitespace-nowrap">{{ formatCurrency($overview['total_boundary'] ?? 0) }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 card-hover overflow-hidden card-number-container">
            <div class="px-2 py-4 sm:p-6 text-center">
                <p class="text-[9px] sm:text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1 text-red-600">Total Expenses</p>
                <div class="flex justify-center items-center h-10 sm:h-12">
                    <p class="auto-scale-text font-black text-red-600 whitespace-nowrap">{{ formatCurrency($overview['total_expenses'] ?? 0) }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 card-hover overflow-hidden card-number-container">
            <div class="px-2 py-4 sm:p-6 text-center">
                <p class="text-[9px] sm:text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1 text-blue-600">Net Income</p>
                @php $ni = $overview['net_income'] ?? 0; @endphp
                <div class="flex justify-center items-center h-10 sm:h-12">
                    <p class="auto-scale-text font-black {{ $ni >= 0 ? 'text-blue-600' : 'text-red-600' }} whitespace-nowrap">{{ formatCurrency($ni) }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 card-hover overflow-hidden card-number-container">
            <div class="px-2 py-4 sm:p-6 text-center">
                <p class="text-[9px] sm:text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1 text-violet-600">Avg Profit Margin</p>
                <div class="flex justify-center items-center h-10 sm:h-12">
                    <p class="auto-scale-text font-black text-violet-600 whitespace-nowrap">{{ number_format($overview['avg_margin'] ?? 0, 1) }}%</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-6">
        <form method="GET" action="{{ route('unit-profitability.index') }}" class="flex flex-col md:flex-row gap-4">
            <div class="flex-1">
                <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">From Date</label>
                <div class="relative group">
                    <i data-lucide="calendar" class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 group-focus-within:text-yellow-600 transition-colors"></i>
                    <input type="date" name="date_from" value="{{ $date_from ?? date('Y-m-01') }}"
                        onchange="this.form.submit()"
                        class="block w-full pl-11 pr-4 py-3.5 bg-gray-50 border border-gray-100 rounded-2xl text-xs font-black shadow-sm focus:ring-4 focus:ring-yellow-500/10 focus:border-yellow-400 transition-all outline-none">
                </div>
            </div>
            <div class="flex-1">
                <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-1">To Date</label>
                <div class="relative group">
                    <i data-lucide="calendar" class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 group-focus-within:text-yellow-600 transition-colors"></i>
                    <input type="date" name="date_to" value="{{ $date_to ?? date('Y-m-d') }}"
                        onchange="this.form.submit()"
                        class="block w-full pl-11 pr-4 py-3.5 bg-gray-50 border border-gray-100 rounded-2xl text-xs font-black shadow-sm focus:ring-4 focus:ring-yellow-500/10 focus:border-yellow-400 transition-all outline-none">
                </div>
            </div>
            <div class="flex-1">
                <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-1">Unit Selection</label>
                <div class="relative group">
                    <i data-lucide="car-front" class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 group-focus-within:text-yellow-600 transition-colors"></i>
                    <select name="unit_id" onchange="this.form.submit()"
                        class="block w-full pl-11 pr-4 py-3.5 bg-gray-50 border border-gray-100 rounded-2xl text-xs font-black shadow-sm focus:ring-4 focus:ring-yellow-500/10 focus:border-yellow-400 transition-all outline-none appearance-none cursor-pointer">
                        <option value="">All Units</option>
                        @foreach($units as $unit)
                            <option value="{{ $unit->id }}" {{ ($selected_unit ?? '') == $unit->id ? 'selected' : '' }}>
                                {{ $unit->plate_number }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </form>
    </div>

    {{-- Unit Profitability Details Table --}}
    <div class="bg-white rounded-lg shadow overflow-hidden mb-6">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-800">Unit Profitability Details</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Unit</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Vehicle</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total Boundary</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Maintenance</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Other Exp.</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Net Income</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Margin%</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Performance</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($profitability as $item)
                        @php
                            $margin = $item->profit_margin ?? 0;
                            $perf = $margin > 60 ? 'Excellent' : ($margin > 40 ? 'Good' : ($margin > 20 ? 'Fair' : 'Poor'));
                            $perfColor = $margin > 60 ? 'bg-green-100 text-green-800' : ($margin > 40 ? 'bg-blue-100 text-blue-800' : ($margin > 20 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800'));
                        @endphp
                        <tr class="hover:bg-gray-50 cursor-pointer" onclick="openComputationModal('{{ $item->id }}', '{{ $item->plate_number }}')">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ $item->plate_number }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $item->make ?? '' }} {{ $item->model ?? '' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-green-600">
                                {{ formatCurrency($item->total_boundary ?? 0) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-red-600">
                                {{ formatCurrency($item->maintenance_cost ?? 0) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-red-600">
                                {{ formatCurrency($item->other_expenses ?? 0) }}
                            </td>
                            <td
                                class="px-6 py-4 whitespace-nowrap text-sm font-bold {{ ($item->net_income ?? 0) >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                {{ formatCurrency($item->net_income ?? 0) }}
                            </td>
                            <td
                                class="px-6 py-4 whitespace-nowrap text-sm font-medium {{ $margin >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                {{ number_format($margin, 1) }}%
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-medium rounded-full {{ $perfColor }}">{{ $perf }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center text-gray-500">
                                <i data-lucide="bar-chart-2" class="w-12 h-12 mx-auto mb-4 text-gray-300"></i>
                                <p>No profitability data for the selected period.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($profitability->hasPages())
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 pagination-container">
                {{ $profitability->appends(request()->query())->links() }}
            </div>
        @endif
    </div>

    {{-- Top Performers & Needs Attention --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        {{-- Top Performers --}}
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-green-50">
                <h3 class="text-md font-semibold text-green-800 flex items-center gap-2">
                    <i data-lucide="trending-up" class="w-5 h-5 text-green-600"></i>
                    Top Performers
                </h3>
            </div>
            <div class="divide-y divide-gray-100">
                @php
                    $topPerformers = collect($full_profitability)->filter(fn($u) => ($u->profit_margin ?? 0) >= 40)->sortByDesc('profit_margin')->take(5);
                @endphp
                @forelse($topPerformers as $unit)
                    <div class="px-5 py-3 flex items-center justify-between">
                        <div>
                            <p class="text-sm font-semibold text-gray-800">{{ $unit->plate_number }}</p>
                            <p class="text-[10px] text-gray-500">{{ $unit->make ?? '' }} {{ $unit->model ?? '' }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-bold text-green-600">{{ formatCurrency($unit->net_income ?? 0) }}</p>
                            <p class="text-xs text-gray-500">{{ number_format($unit->profit_margin ?? 0, 1) }}% margin</p>
                        </div>
                    </div>
                @empty
                    <div class="px-5 py-4 text-sm text-gray-400 text-center">No top performers yet.</div>
                @endforelse
            </div>
        </div>

        {{-- Needs Attention --}}
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-red-50">
                <h3 class="text-md font-semibold text-red-800 flex items-center gap-2">
                    <i data-lucide="alert-triangle" class="w-5 h-5 text-red-600"></i>
                    Needs Attention
                </h3>
            </div>
            <div class="divide-y divide-gray-100">
                @php
                    $needsAttention = collect($full_profitability)->filter(fn($u) => ($u->profit_margin ?? 0) < 40)->sortBy('profit_margin')->take(5);
                @endphp
                @forelse($needsAttention as $unit)
                    <div class="px-5 py-3 flex items-center justify-between">
                        <div>
                            <p class="text-sm font-semibold text-gray-800">{{ $unit->plate_number }}</p>
                            <p class="text-[10px] text-gray-500">{{ $unit->make ?? '' }} {{ $unit->model ?? '' }}</p>
                        </div>
                        <div class="text-right">
                            <p
                                class="text-sm font-bold {{ ($unit->net_income ?? 0) >= 0 ? 'text-yellow-600' : 'text-red-600' }}">
                                {{ formatCurrency($unit->net_income ?? 0) }}</p>
                            <p class="text-xs text-gray-500">{{ number_format($unit->profit_margin ?? 0, 1) }}% margin</p>
                        </div>
                    </div>
                @empty
                    <div class="px-5 py-4 text-sm text-gray-400 text-center">All units are performing well!</div>
                @endforelse
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    {{-- Computation Details Modal --}}
    <div id="computationModal" class="fixed inset-0 z-[100] hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-900 bg-opacity-70 transition-opacity backdrop-blur-sm" aria-hidden="true" onclick="closeComputationModal()"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full border border-gray-100">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-8 sm:pb-4">
                    <div class="flex justify-between items-center border-b border-gray-100 pb-6 mb-6">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 bg-yellow-100 rounded-2xl flex items-center justify-center">
                                <i data-lucide="calculator" class="w-6 h-6 text-yellow-600"></i>
                            </div>
                            <div>
                                <h3 class="text-xl font-black text-gray-900 leading-none mb-1" id="modal-title">Profitability Analysis Details</h3>
                                <p class="text-xs font-black text-yellow-600 uppercase tracking-widest" id="modal-unit-plate"></p>
                            </div>
                        </div>
                        <button type="button" onclick="closeComputationModal()" class="w-10 h-10 flex items-center justify-center rounded-xl bg-gray-50 text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition-all">
                            <i data-lucide="x" class="w-5 h-5"></i>
                        </button>
                    </div>

                    <div id="modal-content" class="min-h-[400px]">
                        {{-- Loading State --}}
                        <div id="modal-loading" class="py-20 text-center">
                            <div class="inline-block animate-spin rounded-full h-10 w-10 border-4 border-yellow-500 border-t-transparent shadow-lg shadow-yellow-500/20"></div>
                            <p class="mt-6 text-gray-400 font-black uppercase tracking-widest text-[10px]">Analyzing records data...</p>
                        </div>

                        {{-- Real Content --}}
                        <div id="modal-data" class="hidden pb-8">
                             {{-- Content injected via JS --}}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function openComputationModal(unitId, plateNumber) {
            document.body.style.overflow = 'hidden';
            document.getElementById('computationModal').classList.remove('hidden');
            document.getElementById('modal-unit-plate').innerText = 'Vehicle Identification: ' + plateNumber;
            document.getElementById('modal-loading').classList.remove('hidden');
            document.getElementById('modal-data').classList.add('hidden');

            const dateFrom = document.querySelector('input[name="date_from"]').value;
            const dateTo = document.querySelector('input[name="date_to"]').value;

            fetch(`/unit-profitability/details?unit_id=${unitId}&date_from=${dateFrom}&date_to=${dateTo}`)
                .then(response => response.json())
                .then(data => {
                    renderModalContent(data);
                })
                .catch(err => {
                    console.error('Error fetching details:', err);
                    alert('Error loading unit details.');
                    closeComputationModal();
                });
        }

        function closeComputationModal() {
            document.body.style.overflow = 'auto';
            document.getElementById('computationModal').classList.add('hidden');
        }

        function renderModalContent(data) {
            const container = document.getElementById('modal-data');
            
            const totalRev = data.boundaries.reduce((acc, b) => acc + parseFloat(b.actual_boundary || 0), 0);
            const totalMaint = data.maintenances.reduce((acc, m) => acc + parseFloat(m.cost || 0), 0);
            const totalOther = data.expenses.reduce((acc, e) => acc + parseFloat(e.amount || 0), 0);
            const totalExp = totalMaint + totalOther;
            const netInc = totalRev - totalExp;

            container.innerHTML = `
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
                    <div class="bg-gradient-to-br from-green-50 to-white p-6 rounded-3xl border border-green-100 shadow-sm">
                        <p class="text-[10px] font-black text-green-700 uppercase tracking-widest mb-1">Gross Boundary</p>
                        <p class="text-3xl font-black text-green-600">${formatCurr(totalRev)}</p>
                    </div>
                    <div class="bg-gradient-to-br from-red-50 to-white p-6 rounded-3xl border border-red-100 shadow-sm">
                        <p class="text-[10px] font-black text-red-700 uppercase tracking-widest mb-1">Total Operating Exp.</p>
                        <p class="text-3xl font-black text-red-600">${formatCurr(totalExp)}</p>
                    </div>
                    <div class="bg-gradient-to-br from-blue-50 to-white p-6 rounded-3xl border border-blue-100 shadow-sm relative overflow-hidden">
                        <p class="text-[10px] font-black text-blue-700 uppercase tracking-widest mb-1">Calculated Net</p>
                        <p class="text-3xl font-black ${netInc >= 0 ? 'text-blue-600' : 'text-red-500'}">${formatCurr(netInc)}</p>
                        <i data-lucide="bar-chart" class="absolute -right-4 -bottom-4 w-16 h-16 text-blue-100 opacity-50"></i>
                    </div>
                </div>

                <div class="space-y-12">
                    {{-- Boundaries --}}
                    <div class="bg-gray-50/50 rounded-3xl p-6 border border-gray-100">
                        <div class="flex items-center justify-between mb-6">
                            <h4 class="text-xs font-black text-gray-400 uppercase tracking-widest flex items-center gap-2">
                                <i data-lucide="wallet" class="w-4 h-4 text-green-500"></i>
                                Boundary Income Stream
                            </h4>
                            <span class="px-3 py-1 bg-white rounded-full border border-gray-100 text-[10px] font-black text-gray-500">${data.boundaries.length} Remit Records</span>
                        </div>
                        <div class="max-h-64 overflow-y-auto custom-scrollbar pr-2">
                            <table class="min-w-full text-xs">
                                <thead class="bg-white/80 backdrop-blur sticky top-0 border-b border-gray-100">
                                    <tr>
                                        <th class="px-4 py-3 text-left font-black text-gray-400 uppercase tracking-tight">Remittance Date</th>
                                        <th class="px-4 py-3 text-right font-black text-gray-400 uppercase tracking-tight">Amount Remitted</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    ${data.boundaries.map(b => `
                                        <tr class="hover:bg-white transition-colors">
                                            <td class="px-4 py-4 text-gray-600 font-medium">${new Date(b.date).toLocaleDateString(undefined, {month: 'long', day: 'numeric', year: 'numeric'})}</td>
                                            <td class="px-4 py-4 text-right font-black text-green-600 font-mono tracking-tighter">${formatCurr(b.actual_boundary)}</td>
                                        </tr>
                                    `).join('') || '<tr><td colspan="2" class="py-12 text-center text-gray-400 font-medium italic">No boundary records for this period.</td></tr>'}
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                        {{-- Maintenance --}}
                        <div class="bg-gray-50/50 rounded-3xl p-6 border border-gray-100">
                            <div class="flex items-center justify-between mb-6">
                                <h4 class="text-xs font-black text-gray-400 uppercase tracking-widest flex items-center gap-2">
                                    <i data-lucide="wrench" class="w-4 h-4 text-red-500"></i>
                                    Maintenance Costs
                                </h4>
                            </div>
                            <div class="max-h-64 overflow-y-auto custom-scrollbar pr-2">
                                <table class="min-w-full text-xs">
                                    <thead class="bg-white/80 backdrop-blur sticky top-0 border-b border-gray-100">
                                        <tr>
                                            <th class="px-3 py-3 text-left font-black text-gray-400 uppercase tracking-tight">Date</th>
                                            <th class="px-3 py-3 text-left font-black text-gray-400 uppercase tracking-tight">Type/Desc</th>
                                            <th class="px-3 py-3 text-right font-black text-gray-400 uppercase tracking-tight">Cost</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100">
                                        ${data.maintenances.map(m => `
                                            <tr class="hover:bg-white transition-colors">
                                                <td class="px-3 py-4 text-gray-600 font-medium">${new Date(m.date_started).toLocaleDateString(undefined, {month: 'short', day: 'numeric'})}</td>
                                                <td class="px-3 py-4 text-gray-900 font-semibold truncate max-w-[120px]" title="${m.description}">${m.description}</td>
                                                <td class="px-3 py-4 text-right font-black text-red-600 font-mono tracking-tighter">${formatCurr(m.cost)}</td>
                                            </tr>
                                        `).join('') || '<tr><td colspan="3" class="py-8 text-center text-gray-300 italic">No maintenance logs.</td></tr>'}
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        {{-- Other Expenses --}}
                        <div class="bg-gray-50/50 rounded-3xl p-6 border border-gray-100">
                            <div class="flex items-center justify-between mb-6">
                                <h4 class="text-xs font-black text-gray-400 uppercase tracking-widest flex items-center gap-2">
                                    <i data-lucide="receipt" class="w-4 h-4 text-violet-500"></i>
                                    Other Expenses
                                </h4>
                            </div>
                            <div class="max-h-64 overflow-y-auto custom-scrollbar pr-2">
                                <table class="min-w-full text-xs">
                                    <thead class="bg-white/80 backdrop-blur sticky top-0 border-b border-gray-100">
                                        <tr>
                                            <th class="px-3 py-3 text-left font-black text-gray-400 uppercase tracking-tight">Date</th>
                                            <th class="px-3 py-3 text-left font-black text-gray-400 uppercase tracking-tight">Desc</th>
                                            <th class="px-3 py-3 text-right font-black text-gray-400 uppercase tracking-tight">Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100">
                                        ${data.expenses.map(e => `
                                            <tr class="hover:bg-white transition-colors">
                                                <td class="px-3 py-4 text-gray-600 font-medium">${new Date(e.date).toLocaleDateString(undefined, {month: 'short', day: 'numeric'})}</td>
                                                <td class="px-3 py-4 text-gray-900 font-semibold truncate max-w-[120px]" title="${e.description}">${e.description}</td>
                                                <td class="px-3 py-4 text-right font-black text-red-600 font-mono tracking-tighter">${formatCurr(e.amount)}</td>
                                            </tr>
                                        `).join('') || '<tr><td colspan="3" class="py-8 text-center text-gray-300 italic">No other expenses.</td></tr>'}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            document.getElementById('modal-loading').classList.add('hidden');
            document.getElementById('modal-data').classList.remove('hidden');
            lucide.createIcons();
        }

        function formatCurr(val) {
            return '₱' + parseFloat(val || 0).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
        }
    </script>
@endpush