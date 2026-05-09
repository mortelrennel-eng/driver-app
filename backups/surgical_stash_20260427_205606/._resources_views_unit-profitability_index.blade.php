@extends('layouts.app')

@section('title', 'Unit Profitability - Euro System')
@section('page-heading', 'Unit Profitability Analysis')
@section('page-subheading', 'Evaluate each unit\'s revenue versus expenses to determine profitability')

@section('content')

    {{-- Filters --}}
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <form method="GET" action="{{ route('unit-profitability.index') }}" class="flex flex-col md:flex-row gap-4">
            <div class="flex-1">
                <label class="text-sm font-medium text-gray-700 block mb-1">From Date</label>
                <input type="date" name="date_from" value="{{ $date_from ?? date('Y-m-01') }}"
                    class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 focus:outline-none">
            </div>
            <div class="flex-1">
                <label class="text-sm font-medium text-gray-700 block mb-1">To Date</label>
                <input type="date" name="date_to" value="{{ $date_to ?? date('Y-m-d') }}"
                    class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 focus:outline-none">
            </div>
            <div class="flex-1">
                <label class="text-sm font-medium text-gray-700 block mb-1">Unit</label>
                <select name="unit_id" class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 focus:outline-none">
                    <option value="">All Units</option>
                    @foreach($units as $unit)
                        <option value="{{ $unit->id }}" {{ ($selected_unit ?? '') == $unit->id ? 'selected' : '' }}>
                            {{ $unit->plate_number }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end">
                <button type="submit"
                    class="px-6 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 flex items-center gap-2">
                    <i data-lucide="bar-chart-2" class="w-4 h-4"></i> Analyze
                </button>
            </div>
        </form>
    </div>

    {{-- Overall Stats Cards --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow card-hover">
            <div class="p-5 text-center">
                <p class="text-xs text-gray-500 mb-1">Total Units</p>
                <p class="text-2xl font-bold text-gray-900">{{ $overview['total_units'] ?? 0 }}</p>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow card-hover">
            <div class="p-5 text-center">
                <p class="text-xs text-gray-500 mb-1">Total Boundary</p>
                <p class="text-2xl font-bold text-green-600">{{ formatCurrency($overview['total_boundary'] ?? 0) }}</p>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow card-hover">
            <div class="p-5 text-center">
                <p class="text-xs text-gray-500 mb-1">Total Expenses</p>
                <p class="text-2xl font-bold text-red-600">{{ formatCurrency($overview['total_expenses'] ?? 0) }}</p>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow card-hover">
            <div class="p-5 text-center">
                <p class="text-xs text-gray-500 mb-1">Net Income</p>
                @php $ni = $overview['net_income'] ?? 0; @endphp
                <p class="text-2xl font-bold {{ $ni >= 0 ? 'text-green-600' : 'text-red-600' }}">{{ formatCurrency($ni) }}</p>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow card-hover">
            <div class="p-5 text-center">
                <p class="text-xs text-gray-500 mb-1">Avg Profit Margin</p>
                <p class="text-2xl font-bold text-blue-600">{{ number_format($overview['avg_margin'] ?? 0, 1) }}%</p>
            </div>
        </div>
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
                        <tr class="hover:bg-gray-50">
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
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-bold {{ ($item->net_income ?? 0) >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                {{ formatCurrency($item->net_income ?? 0) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium {{ $margin >= 0 ? 'text-green-600' : 'text-red-600' }}">
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
                    $topPerformers = collect($profitability)->filter(fn($u) => ($u->profit_margin ?? 0) >= 40)->sortByDesc('profit_margin')->take(5);
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
                    $needsAttention = collect($profitability)->filter(fn($u) => ($u->profit_margin ?? 0) < 40)->sortBy('profit_margin')->take(5);
                @endphp
                @forelse($needsAttention as $unit)
                    <div class="px-5 py-3 flex items-center justify-between">
                        <div>
                            <p class="text-sm font-semibold text-gray-800">{{ $unit->plate_number }}</p>
                            <p class="text-[10px] text-gray-500">{{ $unit->make ?? '' }} {{ $unit->model ?? '' }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-bold {{ ($unit->net_income ?? 0) >= 0 ? 'text-yellow-600' : 'text-red-600' }}">{{ formatCurrency($unit->net_income ?? 0) }}</p>
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