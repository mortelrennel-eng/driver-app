@extends('layouts.app')

@section('title', 'Unit Management - Euro System')
@section('page-heading', 'Unit Management')
@section('page-subheading', 'Manage your fleet of taxi units')
@section('main-padding', 'p-0')

@push('styles')
    <!-- Leaflet CSS for Map -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        #unitDetailMap { z-index: 1; }

        /* ── Modern Table — Separated Rounded Rows (matching Maintenance page) ── */
        .modern-table-sep {
            border-collapse: separate;
            border-spacing: 0 0.55rem;
        }
        .modern-row {
            background-color: white;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05);
            transition: all 0.2s ease-in-out;
        }
        .modern-row:hover {
            box-shadow: 0 10px 15px -3px rgba(234, 179, 8, 0.18), 0 4px 6px -2px rgba(234, 179, 8, 0.08);
            transform: translateY(-1px);
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

        /* ── Live Status Dots ─────────────────────────────────── */
        .status-dot {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.02em;
        }
        .status-dot .dot {
            flex-shrink: 0;
            border-radius: 9999px;
            position: relative;
        }

        /* Green Pulse — Active / On Road */
        .dot-green {
            width: 9px; height: 9px;
            background: #22c55e;
            box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.7);
            animation: pulse-green 1.8s ease-in-out infinite;
        }
        @keyframes pulse-green {
            0%   { box-shadow: 0 0 0 0   rgba(34, 197, 94, 0.7); }
            70%  { box-shadow: 0 0 0 7px rgba(34, 197, 94, 0);   }
            100% { box-shadow: 0 0 0 0   rgba(34, 197, 94, 0);   }
        }

        /* Red Static — Maintenance (no animation, solid danger) */
        .dot-red {
            width: 9px; height: 9px;
            background: #ef4444;
            box-shadow: 0 0 5px rgba(239, 68, 68, 0.6);
        }

        /* Yellow Blink — Coding / At Risk / Pending */
        .dot-yellow {
            width: 9px; height: 9px;
            background: #f59e0b;
            animation: blink-yellow 1.1s step-start infinite;
        }
        @keyframes blink-yellow {
            0%, 100% { opacity: 1; }
            50%       { opacity: 0.15; }
        }

        /* Orange pulse — At Risk */
        .dot-orange {
            width: 9px; height: 9px;
            background: #f97316;
            box-shadow: 0 0 0 0 rgba(249, 115, 22, 0.7);
            animation: pulse-orange 2.2s ease-in-out infinite;
        }
        @keyframes pulse-orange {
            0%   { box-shadow: 0 0 0 0   rgba(249, 115, 22, 0.7); }
            70%  { box-shadow: 0 0 0 6px rgba(249, 115, 22, 0);   }
            100% { box-shadow: 0 0 0 0   rgba(249, 115, 22, 0);   }
        }

        /* Gray Static — Retired / Vacant */
        .dot-gray {
            width: 9px; height: 9px;
            background: #9ca3af;
        }
    </style>
@endpush

@section('content')
    <!-- Search and Filters -->
    <div class="bg-white px-6 py-4 border-b border-gray-200">
        <form method="GET" action="{{ route('units.index') }}" class="flex flex-col md:flex-row gap-2">
            <div class="md:w-48">
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i data-lucide="arrow-up-z-a" class="h-4 w-4 text-gray-400"></i>
                    </div>
                    <select name="sort" onchange="this.form.submit()"
                        class="block w-full pl-9 pr-3 py-1 border border-gray-300 rounded-lg text-xs focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 focus:outline-none appearance-none">
                        <option value="alphabetical" {{ ($sort ?? '') === 'alphabetical' ? 'selected' : '' }}>A-Z (Plate #)</option>
                        <option value="newest" {{ ($sort ?? '') === 'newest' ? 'selected' : '' }}>Newest Added</option>
                        <option value="oldest" {{ ($sort ?? '') === 'oldest' ? 'selected' : '' }}>Oldest Added</option>
                        <option value="vacant" {{ ($sort ?? '') === 'vacant' ? 'selected' : '' }}>Vacant Units First</option>
                    </select>
                </div>
            </div>
            <div class="flex-1">
                <div class="relative group">
                    <input type="text" name="search" id="tableSearchInput" value="{{ $search }}"
                        class="block w-full pl-3 pr-10 py-1 border border-gray-300 rounded-lg text-xs focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 focus:outline-none"
                        placeholder="Search plate numbers...">
                    <button type="submit" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-yellow-600 transition-colors">
                        <i data-lucide="search" class="h-4 w-4"></i>
                    </button>
                </div>
            </div>
            <div class="md:w-48">
                <select name="status" onchange="this.form.submit()"
                    class="block w-full px-3 py-1 border border-gray-300 rounded-lg text-xs focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 focus:outline-none">
                    <option value="">All Status</option>
                    <option value="active" {{ $status_filter === 'active' ? 'selected' : '' }}>Active Units</option>
                    <option value="available" {{ $status_filter === 'available' ? 'selected' : '' }}>Available (No Driver)</option>
                    <option value="1_2" {{ $status_filter === '1_2' ? 'selected' : '' }}>1/2 Driver (Solo)</option>
                    <option value="2_2" {{ $status_filter === '2_2' ? 'selected' : '' }}>2/2 Driver (Shared)</option>
                    <option value="maintenance" {{ $status_filter === 'maintenance' ? 'selected' : '' }}>In Maintenance</option>
                    <option value="coding" {{ $status_filter === 'coding' ? 'selected' : '' }}>In Coding</option>
                    <option value="retired" {{ $status_filter === 'retired' ? 'selected' : '' }}>Retired</option>
                </select>
            </div>
            <div class="flex gap-2 items-center">
                {{-- ── View Mode Toggle (Premium Labeled Pill) ─────── --}}
                <div class="flex items-center bg-gray-900/5 p-1 rounded-xl border border-gray-200/80 gap-0.5 shadow-inner">
                    <button type="button" onclick="setViewMode('table')" id="btn-view-table"
                        class="flex items-center gap-2 px-4 py-2 rounded-lg text-xs font-black transition-all duration-200 uppercase tracking-wide whitespace-nowrap">
                        <i data-lucide="table-properties" class="w-3.5 h-3.5"></i>
                        <span>Table</span>
                    </button>
                    <button type="button" onclick="setViewMode('grid')" id="btn-view-grid"
                        class="flex items-center gap-2 px-4 py-2 rounded-lg text-xs font-black transition-all duration-200 uppercase tracking-wide whitespace-nowrap">
                        <i data-lucide="layout-grid" class="w-3.5 h-3.5"></i>
                        <span>Cards</span>
                    </button>
                    <input type="hidden" name="view" id="viewModeInput" value="table">
                </div>

                <button type="button" onclick="showFlaggedUnitsModal()" class="px-3 py-1 bg-red-600 text-white rounded-lg hover:bg-red-700 flex items-center gap-2 text-xs font-semibold shadow-sm">
                    <i data-lucide="siren" class="w-3.5 h-3.5"></i> Flagged Units
                </button>
                <a href="{{ route('units.print') }}" target="_blank"
                    class="px-3 py-1 bg-blue-600 text-white rounded-lg hover:bg-blue-700 flex items-center gap-2 text-xs font-semibold">
                    <i data-lucide="printer" class="w-3.5 h-3.5"></i> Print to PDF
                </a>
                <button type="button" onclick="document.getElementById('addUnitModal').classList.remove('hidden')"
                    class="px-3 py-1 bg-green-600 text-white rounded-lg hover:bg-green-700 flex items-center gap-2 text-xs font-semibold">
                    <i data-lucide="plus" class="w-3.5 h-3.5"></i> Add Unit
                </button>
            </div>
        </form>
    </div>

    <!-- ══════════════════════════════════════════════════════════════ -->
    <!-- Quick Stats Bar — updates in real-time with filters/search    -->
    <!-- ══════════════════════════════════════════════════════════════ -->
    <div id="quickStatsBar" class="bg-gradient-to-r from-gray-900 via-gray-800 to-gray-900 border-b border-gray-700/50 px-6 py-4 flex items-center justify-between gap-4 text-white min-h-[40px]">
        <!-- Stats pills -->
        <div class="flex items-center gap-1 flex-wrap">
            <!-- Filtered badge (hidden by default) -->
            <span id="qs-filter-badge" class="hidden mr-2 px-2 py-0.5 rounded-full text-[10px] font-black uppercase bg-yellow-400 text-gray-900 tracking-wide animate-pulse">
                ⚡ Filtered
            </span>

            <!-- Total -->
            <div class="flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 hover:bg-white/20 transition-all cursor-default select-none">
                <div class="w-2 h-2 rounded-full bg-white animate-pulse"></div>
                <span class="text-[11px] font-bold text-gray-300">Total</span>
                <span id="qs-total" class="text-[13px] font-black text-white tabular-nums">{{ $stats['total'] ?? '—' }}</span>
            </div>

            <span class="text-gray-600 select-none">·</span>

            <!-- Active Units -->
            <div class="flex items-center gap-2 px-3 py-1 rounded-full bg-green-500/20 hover:bg-green-500/30 transition-all cursor-default select-none">
                <div class="w-2 h-2 rounded-full bg-green-400 animate-pulse shadow-[0_0_6px_rgba(74,222,128,0.8)]"></div>
                <span class="text-[11px] font-bold text-green-300">Active Units</span>
                <span id="qs-onroad" class="text-[13px] font-black text-green-400 tabular-nums">{{ $stats['on_road'] ?? '—' }}</span>
            </div>

            <span class="text-gray-600 select-none">·</span>

            <!-- At Risk -->
            <div class="flex items-center gap-2 px-3 py-1 rounded-full bg-orange-500/20 hover:bg-orange-500/30 transition-all cursor-default select-none">
                <div class="w-2 h-2 rounded-full bg-orange-400 animate-pulse shadow-[0_0_6px_rgba(251,146,60,0.8)]"></div>
                <span class="text-[11px] font-bold text-orange-300">At Risk</span>
                <span id="qs-garage" class="text-[13px] font-black text-orange-400 tabular-nums">{{ $stats['garage'] ?? '—' }}</span>
            </div>

            <span class="text-gray-600 select-none">·</span>

            <!-- Workshop / Maintenance -->
            <div class="flex items-center gap-2 px-3 py-1 rounded-full bg-yellow-500/20 hover:bg-yellow-500/30 transition-all cursor-default select-none">
                <div class="w-2 h-2 rounded-full bg-yellow-400 animate-pulse shadow-[0_0_6px_rgba(250,204,21,0.8)]"></div>
                <span class="text-[11px] font-bold text-yellow-300">Maintenance</span>
                <span id="qs-workshop" class="text-[13px] font-black text-yellow-400 tabular-nums">{{ $stats['workshop'] ?? '—' }}</span>
            </div>

            <span class="text-gray-600 select-none">·</span>

            <!-- Coding -->
            <div class="flex items-center gap-2 px-3 py-1 rounded-full bg-red-500/20 hover:bg-red-500/30 transition-all cursor-default select-none">
                <div class="w-2 h-2 rounded-full bg-red-400 animate-pulse shadow-[0_0_6px_rgba(248,113,113,0.8)]"></div>
                <span class="text-[11px] font-bold text-red-300">Coding</span>
                <span id="qs-coding" class="text-[13px] font-black text-red-400 tabular-nums">{{ $stats['coding'] ?? '—' }}</span>
            </div>
        </div>

        <!-- Right side: last updated hint -->
        <div class="flex items-center gap-2 flex-shrink-0">
            <div id="qs-loading" class="hidden w-3 h-3 border-2 border-white/30 border-t-white rounded-full animate-spin"></div>
            <span id="qs-context" class="text-[10px] text-gray-500 font-medium italic hidden">showing filtered results</span>
            <div class="w-1.5 h-1.5 rounded-full bg-green-500 shadow-[0_0_6px_rgba(74,222,128,0.8)] animate-pulse" title="Live"></div>
        </div>
    </div>

    <!-- Units Container — renders table or grid based on view_mode -->
    <div id="unitsTableContainer" class="bg-white overflow-hidden">
        @if(($view_mode ?? 'table') === 'grid')
            @include('units.partials._units_grid')
        @else
            @include('units.partials._units_table')
        @endif
    </div>

    {{-- Add Unit Modal --}}
    <div id="addUnitModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden h-full w-full z-50 flex items-center justify-center p-4">
        <div class="relative bg-white rounded-lg shadow-xl max-w-4xl w-full max-h-[90vh] overflow-y-auto">

            {{-- Modal Header --}}
            <div class="bg-gradient-to-r from-yellow-500 to-yellow-600 p-6 rounded-t-lg">
                <div class="flex justify-between items-center">
                    <div class="flex items-center gap-3">
                        <div class="p-3 bg-white bg-opacity-20 rounded-lg">
                            <i data-lucide="car" class="w-6 h-6 text-white"></i>
                        </div>
                        <div>
                            <h3 class="text-2xl font-bold text-white">Add New Unit</h3>
                            <p class="text-yellow-100 text-sm">Enter vehicle information and add devices</p>
                        </div>
                    </div>
                    <button onclick="document.getElementById('addUnitModal').classList.add('hidden'); resetAddUnitModal()"
                        class="text-white hover:bg-white hover:bg-opacity-20 rounded-lg p-2 transition-colors">
                        <i data-lucide="x" class="w-6 h-6"></i>
                    </button>
                </div>
            </div>

            {{-- Form --}}
            <form method="POST" action="{{ route('units.store') }}" id="addUnitForm" class="p-6">
                @csrf

                {{-- Section 1: Basic Information --}}
                <div class="mb-8">
                    <div class="flex items-center gap-2 mb-4">
                        <div class="p-2 bg-blue-100 rounded-lg">
                            <i data-lucide="info" class="w-5 h-5 text-blue-600"></i>
                        </div>
                        <h4 class="text-lg font-semibold text-gray-900">Basic Information</h4>
                    </div>
                    <div class="grid grid-cols-1 gap-6">
                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Plate Number <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i data-lucide="credit-card" class="w-5 h-5 text-gray-400"></i>
                                </div>
                                <input type="text" name="plate_number" id="addPlateNumber" required
                                    class="w-full pl-10 pr-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent"
                                    placeholder="e.g., ABC 123"
                                    oninput="this.value = this.value.toUpperCase(); addUnitUpdateCoding()">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Section 2: Vehicle Details --}}
                <div class="mb-8">
                    <div class="flex items-center gap-2 mb-4">
                        <div class="p-2 bg-green-100 rounded-lg">
                            <i data-lucide="truck" class="w-5 h-5 text-green-600"></i>
                        </div>
                        <h4 class="text-lg font-semibold text-gray-900">Vehicle Details</h4>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Make <span class="text-red-500">*</span></label>
                            <input type="text" name="make" required
                                class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent"
                                placeholder="e.g., Toyota, Honda, Nissan"
                                oninput="this.value = this.value.toUpperCase()">
                        </div>
                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Model <span class="text-red-500">*</span></label>
                            <input type="text" name="model" required
                                class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent"
                                placeholder="e.g., Vios, Civic, Sentra"
                                oninput="this.value = this.value.toUpperCase()">
                        </div>
                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Year <span class="text-red-500">*</span></label>
                            <input type="number" name="year" required min="2000" max="{{ date('Y') }}" value="{{ date('Y') }}"
                                class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent"
                                placeholder="e.g., 2023">
                        </div>
                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Motor No <span class="text-red-500">*</span></label>
                            <input type="text" name="motor_no" required
                                class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent"
                                placeholder="e.g., 2NZ7847183"
                                oninput="this.value = this.value.toUpperCase()">
                        </div>
                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Chassis No <span class="text-red-500">*</span></label>
                            <input type="text" name="chassis_no" required
                                class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent"
                                placeholder="e.g., NCP1512071757"
                                oninput="this.value = this.value.toUpperCase()">
                        </div>

                    </div>
                </div>

                {{-- Section 3: Financial Information --}}
                <div class="mb-8">
                    <div class="flex items-center gap-2 mb-4">
                        <div class="p-2 bg-purple-100 rounded-lg">
                            <i data-lucide="dollar-sign" class="w-5 h-5 text-purple-600"></i>
                        </div>
                        <h4 class="text-lg font-semibold text-gray-900">Financial Information</h4>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Boundary Rate <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 text-sm">₱</span>
                                </div>
                                <input type="text" name="boundary_rate" id="addBoundaryRate" required value="1,100.00"
                                    class="w-full pl-8 pr-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent"
                                    placeholder="0.00"
                                    onfocus="unformatCurrencyInput(this)"
                                    onblur="formatCurrencyInput(this)">
                            </div>
                            <p class="text-xs text-gray-500">Daily boundary collection target</p>
                        </div>
                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Purchase Cost</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 text-sm">₱</span>
                                </div>
                                <input type="text" name="purchase_cost" id="addPurchaseCost" value="0.00"
                                    class="w-full pl-8 pr-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent"
                                    placeholder="0.00"
                                    onfocus="unformatCurrencyInput(this)"
                                    onblur="formatCurrencyInput(this)">
                            </div>
                            <p class="text-xs text-gray-500">Total purchase amount</p>
                        </div>
                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Purchase Date</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i data-lucide="calendar" class="w-4 h-4 text-gray-400"></i>
                                </div>
                                <input type="date" name="purchase_date"
                                    class="w-full pl-10 pr-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
                            </div>
                            <p class="text-xs text-gray-500">When the unit was purchased</p>
                        </div>
                    </div>
                </div>

                {{-- Section 4: Driver Assignment --}}
                <div class="mb-8">
                    <div class="flex items-center gap-2 mb-4">
                        <div class="p-2 bg-blue-100 rounded-lg">
                            <i data-lucide="users" class="w-5 h-5 text-blue-600"></i>
                        </div>
                        <h4 class="text-lg font-semibold text-gray-900">Driver Assignment</h4>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        {{-- Primary Driver --}}
                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Primary Driver</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none z-10">
                                    <i data-lucide="user" class="w-5 h-5 text-gray-400"></i>
                                </div>
                                <input type="text" id="add_driver1_search" autocomplete="off"
                                    placeholder="Start typing to search drivers..."
                                    class="w-full pl-10 pr-10 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent"
                                    onkeyup="addUnitFilterDrivers('add_driver1')"
                                    onfocus="addUnitShowDropdown('add_driver1')"
                                    onblur="setTimeout(()=>addUnitHideDropdown('add_driver1'), 200)"
                                    oninput="addUnitFilterDrivers('add_driver1')">
                                <button type="button" onclick="addUnitClearDriver('add_driver1')"
                                    class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                    <i data-lucide="x" class="w-4 h-4 text-gray-400 hover:text-gray-600"></i>
                                </button>
                                <select id="add_driver1" name="driver_id" class="hidden">
                                    <option value="">Select Primary Driver</option>
                                    @foreach($all_drivers as $driver)
                                        <option value="{{ $driver->id }}" data-name="{{ $driver->full_name }}" data-license="{{ $driver->license_number ?? '' }}" data-assigned-unit="{{ $driver->assigned_unit_id }}">
                                            {{ $driver->full_name }} - {{ $driver->license_number ?? 'No License' }}
                                        </option>
                                    @endforeach
                                </select>
                                <div id="add_driver1_dropdown" class="absolute z-20 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-60 overflow-y-auto hidden"></div>
                            </div>
                            <p class="text-xs text-gray-500">Main driver assigned to this unit</p>
                        </div>

                        {{-- Secondary Driver --}}
                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Secondary Driver (Optional)</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none z-10">
                                    <i data-lucide="user-plus" class="w-5 h-5 text-gray-400"></i>
                                </div>
                                <input type="text" id="add_driver2_search" autocomplete="off"
                                    placeholder="Start typing to search drivers..."
                                    class="w-full pl-10 pr-10 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent"
                                    onkeyup="addUnitFilterDrivers('add_driver2')"
                                    onfocus="addUnitShowDropdown('add_driver2')"
                                    onblur="setTimeout(()=>addUnitHideDropdown('add_driver2'), 200)"
                                    oninput="addUnitFilterDrivers('add_driver2')">
                                <button type="button" onclick="addUnitClearDriver('add_driver2')"
                                    class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                    <i data-lucide="x" class="w-4 h-4 text-gray-400 hover:text-gray-600"></i>
                                </button>
                                <select id="add_driver2" name="secondary_driver_id" class="hidden">
                                    <option value="">Select Secondary Driver</option>
                                    @foreach($all_drivers as $driver)
                                        <option value="{{ $driver->id }}" data-name="{{ $driver->full_name }}" data-license="{{ $driver->license_number ?? '' }}" data-assigned-unit="{{ $driver->assigned_unit_id }}">
                                            {{ $driver->full_name }} - {{ $driver->license_number ?? 'No License' }}
                                        </option>
                                    @endforeach
                                </select>
                                <div id="add_driver2_dropdown" class="absolute z-20 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-60 overflow-y-auto hidden"></div>
                            </div>
                            <p class="text-xs text-gray-500">Backup or relief driver (optional)</p>
                        </div>

                        {{-- Remove All Drivers button --}}
                        <div class="pt-2">
                            <button type="button" onclick="addUnitClearDriver('add_driver1'); addUnitClearDriver('add_driver2')"
                                class="w-full bg-red-50 text-red-600 py-2 px-4 rounded-lg hover:bg-red-100 transition-colors flex items-center justify-center gap-2 border border-red-200">
                                <i data-lucide="user-x" class="w-4 h-4"></i> Remove All Drivers
                            </button>
                            <p class="text-xs text-gray-500 mt-1">Clear both driver assignments for this unit</p>
                        </div>
                    </div>
                </div>

                {{-- Section 5: Coding Information --}}
                <div class="mb-8">
                    <div class="flex items-center gap-2 mb-4">
                        <div class="p-2 bg-indigo-100 rounded-lg">
                            <i data-lucide="calendar" class="w-5 h-5 text-indigo-600"></i>
                        </div>
                        <h4 class="text-lg font-semibold text-gray-900">Coding Information</h4>
                    </div>

                    {{-- MMDA Schedule Reference --}}
                    <div class="mb-4 p-4 bg-blue-50 rounded-lg border border-blue-200">
                        <div class="flex items-center gap-2 mb-3">
                            <i data-lucide="info" class="w-4 h-4 text-blue-600"></i>
                            <h5 class="font-semibold text-blue-900">MMDA Coding Schedule (Metro Manila)</h5>
                        </div>
                        <div class="grid grid-cols-2 md:grid-cols-5 gap-2 text-xs">
                            <div class="flex items-center gap-1"><span class="font-medium">Mon:</span><span class="px-2 py-1 bg-blue-100 text-blue-800 rounded">1, 2</span></div>
                            <div class="flex items-center gap-1"><span class="font-medium">Tue:</span><span class="px-2 py-1 bg-blue-100 text-blue-800 rounded">3, 4</span></div>
                            <div class="flex items-center gap-1"><span class="font-medium">Wed:</span><span class="px-2 py-1 bg-blue-100 text-blue-800 rounded">5, 6</span></div>
                            <div class="flex items-center gap-1"><span class="font-medium">Thu:</span><span class="px-2 py-1 bg-blue-100 text-blue-800 rounded">7, 8</span></div>
                            <div class="flex items-center gap-1"><span class="font-medium">Fri:</span><span class="px-2 py-1 bg-blue-100 text-blue-800 rounded">9, 0</span></div>
                        </div>
                        <p class="text-xs text-blue-600 mt-2">Based on the last digit of your plate number</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Coding Day</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i data-lucide="calendar" class="w-5 h-5 text-gray-400"></i>
                                </div>
                                <input type="text" id="addCodingDay" name="coding_day" readonly
                                    class="w-full pl-10 pr-4 py-3 border-2 border-gray-300 rounded-lg bg-gray-50"
                                    placeholder="Auto-generated">
                            </div>
                            <p class="text-xs text-gray-500">Automatically calculated from plate number</p>
                        </div>
                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Next Coding Date</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i data-lucide="calendar" class="w-5 h-5 text-gray-400"></i>
                                </div>
                                <input type="text" id="addNextCodingDate" readonly
                                    class="w-full pl-10 pr-4 py-3 border-2 border-gray-300 rounded-lg bg-gray-50"
                                    placeholder="Auto-generated">
                            </div>
                            <p class="text-xs text-gray-500">Next scheduled coding date</p>
                        </div>
                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Days Until Next Coding</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i data-lucide="clock" class="w-5 h-5 text-gray-400"></i>
                                </div>
                                <input type="text" id="addDaysUntilCoding" readonly
                                    class="w-full pl-10 pr-4 py-3 border-2 border-gray-300 rounded-lg bg-gray-50"
                                    placeholder="Auto-calculated">
                            </div>
                            <p class="text-xs text-gray-500">Days remaining until next coding</p>
                        </div>
                    </div>
                    <div id="addCodingStatusDisplay" class="mt-4"></div>
                </div>

                {{-- Section 6: Tracksolid Pro GPS Integration --}}
                <div class="mb-8">
                    <div class="flex items-center gap-2 mb-4">
                        <div class="p-2 bg-indigo-100 rounded-lg">
                            <i data-lucide="satellite" class="w-5 h-5 text-indigo-600"></i>
                        </div>
                        <h4 class="text-lg font-semibold text-gray-900">GPS Integration (Tracksolid Pro)</h4>
                    </div>
                    <div class="p-4 bg-indigo-50 rounded-lg border border-indigo-200 mb-4">
                        <p class="text-sm text-indigo-800">
                            <strong>Tracksolid Pro IMEI:</strong> Enter the 15-digit IMEI of the GPS device. This will connect the unit to the real-time tracking system.
                        </p>
                    </div>
                    <div class="grid grid-cols-1 gap-6">
                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Device IMEI (Optional)</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i data-lucide="hash" class="w-5 h-5 text-gray-400"></i>
                                </div>
                                <input type="text" name="imei" id="addImei"
                                    class="w-full pl-10 pr-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent font-mono"
                                    placeholder="Enter 15-digit IMEI">
                            </div>
                            <p class="text-xs text-gray-500">Retrieve the IMEI from the physical device label or the Tracksolid Pro application.</p>
                        </div>
                    </div>
                </div>

                {{-- Form Actions --}}
                <div class="flex gap-3 mt-6 pt-4 border-t">
                    <button type="submit" class="flex-1 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 font-semibold flex items-center justify-center gap-2">
                        <i data-lucide="plus-circle" class="w-5 h-5"></i> Add Unit
                    </button>
                    <button type="button" onclick="document.getElementById('addUnitModal').classList.add('hidden'); resetAddUnitModal()"
                        class="flex-1 py-3 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 font-semibold">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Edit Unit Modal --}}
    <div id="editUnitModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-6xl h-[95vh] flex flex-col overflow-hidden">
            {{-- Modal Header (Deep Navy matching Unit Details) --}}
            <div class="bg-slate-800 p-4 shrink-0">
                <div class="flex justify-between items-center">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-white bg-opacity-20 rounded-lg">
                            <i data-lucide="edit-2" class="w-5 h-5 text-white"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-white leading-tight">Edit Unit</h3>
                            <p class="text-sm text-blue-100 leading-tight">Update vehicle information and settings</p>
                        </div>
                    </div>
                    <button type="button" onclick="closeEditUnitModal()" class="text-white hover:text-gray-200 transition-colors">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>
            </div>

            {{-- Form --}}
            <form method="POST" id="editUnitForm" class="flex flex-col flex-1 overflow-hidden">
                @csrf @method('PUT')
                
                {{-- Scrolling Content Area --}}
                <div class="p-6 flex-1 overflow-y-auto custom-scrollbar">
                    {{-- Section 1: Basic Information --}}
                    <div class="mb-8">
                    <div class="flex items-center gap-2 mb-4">
                        <div class="p-2 bg-blue-100 rounded-lg"><i data-lucide="info" class="w-5 h-5 text-blue-600"></i></div>
                        <h4 class="text-lg font-semibold text-gray-900">Basic Information</h4>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Plate Number <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i data-lucide="credit-card" class="w-5 h-5 text-gray-400"></i>
                                </div>
                                <input type="text" name="plate_number" id="editPlateNumber" required
                                    class="w-full pl-10 pr-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    oninput="this.value = this.value.toUpperCase(); editUnitUpdateCoding()">
                            </div>
                        </div>
                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Make <span class="text-red-500">*</span></label>
                            <input type="text" name="make" id="editMake" required
                                class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                oninput="this.value = this.value.toUpperCase()">
                        </div>
                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Model <span class="text-red-500">*</span></label>
                            <input type="text" name="model" id="editModel" required
                                class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                oninput="this.value = this.value.toUpperCase()">
                        </div>
                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Year <span class="text-red-500">*</span></label>
                            <input type="number" name="year" id="editYear" required
                                class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Motor No <span class="text-red-500">*</span></label>
                            <input type="text" name="motor_no" id="editMotorNo" required
                                class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                oninput="this.value = this.value.toUpperCase()">
                        </div>
                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Chassis No <span class="text-red-500">*</span></label>
                            <input type="text" name="chassis_no" id="editChassisNo" required
                                class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                oninput="this.value = this.value.toUpperCase()">
                        </div>

                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Status</label>
                            <select name="status" id="editStatus"
                                class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option value="active">Active</option>
                                <option value="at_risk">At Risk / Missing</option>
                                <option value="maintenance">Maintenance</option>
                                <option value="coding">Coding</option>
                                <option value="retired">Retired</option>
                                <option value="vacant">Vacant</option>
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Unit Type</label>
                            <select name="unit_type" id="editUnitType"
                                class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option value="new">New</option>
                                <option value="old">Old</option>
                                <option value="rented">Rented</option>
                            </select>
                        </div>
                    </div>
                </div>


                {{-- Section 3: Financial Information --}}
                <div class="mb-8">
                    <div class="flex items-center gap-2 mb-4">
                        <div class="p-2 bg-purple-100 rounded-lg"><i data-lucide="dollar-sign" class="w-5 h-5 text-purple-600"></i></div>
                        <h4 class="text-lg font-semibold text-gray-900">Financial Information</h4>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Boundary Rate <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 text-sm">₱</span>
                                </div>
                                <input type="text" name="boundary_rate" id="editBoundaryRate"
                                    class="w-full pl-8 pr-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    placeholder="0.00"
                                    onfocus="unformatCurrencyInput(this)"
                                    onblur="formatCurrencyInput(this)">
                            </div>
                            <p class="text-xs text-gray-500">Daily boundary collection target</p>
                        </div>
                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Purchase Cost</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 text-sm">₱</span>
                                </div>
                                <input type="text" name="purchase_cost" id="editPurchaseCost"
                                    class="w-full pl-8 pr-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    placeholder="0.00"
                                    onfocus="unformatCurrencyInput(this)"
                                    onblur="formatCurrencyInput(this)">
                            </div>
                        </div>
                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Purchase Date</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i data-lucide="calendar" class="w-4 h-4 text-gray-400"></i>
                                </div>
                                <input type="date" name="purchase_date" id="editPurchaseDate"
                                    class="w-full pl-10 pr-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            </div>
                            <p class="text-xs text-gray-500">When the unit was purchased</p>
                        </div>
                    </div>
                </div>

                {{-- Section 4: Driver Assignment --}}
                <div class="mb-8">
                    <div class="flex items-center gap-2 mb-4">
                        <div class="p-2 bg-blue-100 rounded-lg"><i data-lucide="users" class="w-5 h-5 text-blue-600"></i></div>
                        <h4 class="text-lg font-semibold text-gray-900">Driver Assignment</h4>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        {{-- Primary Driver --}}
                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Primary Driver</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none z-10">
                                    <i data-lucide="user" class="w-5 h-5 text-gray-400"></i>
                                </div>
                                <input type="text" id="edit_driver1_search" autocomplete="off"
                                    placeholder="Start typing to search drivers..."
                                    class="w-full pl-10 pr-10 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    onkeyup="editUnitFilterDrivers('edit_driver1')"
                                    onfocus="editUnitShowDropdown('edit_driver1')"
                                    onblur="setTimeout(()=>editUnitHideDropdown('edit_driver1'), 200)"
                                    oninput="editUnitFilterDrivers('edit_driver1')">
                                <button type="button" onclick="editUnitClearDriver('edit_driver1')"
                                    class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                    <i data-lucide="x" class="w-4 h-4 text-gray-400 hover:text-gray-600"></i>
                                </button>
                                <select id="edit_driver1" name="driver_id" class="hidden">
                                    <option value="">No Driver</option>
                                    @foreach($all_drivers as $d)
                                        <option value="{{ $d->id }}" data-name="{{ $d->full_name }}" data-license="{{ $d->license_number ?? '' }}" data-assigned-unit="{{ $d->assigned_unit_id }}">
                                            {{ $d->full_name }} - {{ $d->license_number ?? 'No License' }}
                                        </option>
                                    @endforeach
                                </select>
                                <div id="edit_driver1_dropdown" class="absolute z-20 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-60 overflow-y-auto hidden"></div>
                            </div>
                            <p class="text-xs text-gray-500">Main driver assigned to this unit</p>
                        </div>

                        {{-- Secondary Driver --}}
                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Secondary Driver (Optional)</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none z-10">
                                    <i data-lucide="user-plus" class="w-5 h-5 text-gray-400"></i>
                                </div>
                                <input type="text" id="edit_driver2_search" autocomplete="off"
                                    placeholder="Start typing to search drivers..."
                                    class="w-full pl-10 pr-10 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    onkeyup="editUnitFilterDrivers('edit_driver2')"
                                    onfocus="editUnitShowDropdown('edit_driver2')"
                                    onblur="setTimeout(()=>editUnitHideDropdown('edit_driver2'), 200)"
                                    oninput="editUnitFilterDrivers('edit_driver2')">
                                <button type="button" onclick="editUnitClearDriver('edit_driver2')"
                                    class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                    <i data-lucide="x" class="w-4 h-4 text-gray-400 hover:text-gray-600"></i>
                                </button>
                                <select id="edit_driver2" name="secondary_driver_id" class="hidden">
                                    <option value="">No Driver</option>
                                    @foreach($all_drivers as $d)
                                        <option value="{{ $d->id }}" data-name="{{ $d->full_name }}" data-license="{{ $d->license_number ?? '' }}" data-assigned-unit="{{ $d->assigned_unit_id }}">
                                            {{ $d->full_name }} - {{ $d->license_number ?? 'No License' }}
                                        </option>
                                    @endforeach
                                </select>
                                <div id="edit_driver2_dropdown" class="absolute z-20 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-60 overflow-y-auto hidden"></div>
                            </div>
                            <p class="text-xs text-gray-500">Backup or relief driver (optional)</p>
                        </div>

                        {{-- Remove All Drivers --}}
                        <div class="pt-2">
                            <button type="button" onclick="editUnitClearDriver('edit_driver1'); editUnitClearDriver('edit_driver2')"
                                class="w-full bg-red-50 text-red-600 py-2 px-4 rounded-lg hover:bg-red-100 transition-colors flex items-center justify-center gap-2 border border-red-200">
                                <i data-lucide="user-x" class="w-4 h-4"></i> Remove All Drivers
                            </button>
                            <p class="text-xs text-gray-500 mt-1">Clear both driver assignments for this unit</p>
                        </div>
                    </div>
                </div>

                {{-- Section 5: Coding Information --}}
                <div class="mb-8">
                    <div class="flex items-center gap-2 mb-4">
                        <div class="p-2 bg-indigo-100 rounded-lg"><i data-lucide="calendar" class="w-5 h-5 text-indigo-600"></i></div>
                        <h4 class="text-lg font-semibold text-gray-900">Coding Information</h4>
                    </div>
                    <div class="mb-4 p-4 bg-blue-50 rounded-lg border border-blue-200">
                        <div class="flex items-center gap-2 mb-3">
                            <i data-lucide="info" class="w-4 h-4 text-blue-600"></i>
                            <h5 class="font-semibold text-blue-900">MMDA Coding Schedule (Metro Manila)</h5>
                        </div>
                        <div class="grid grid-cols-2 md:grid-cols-5 gap-2 text-xs">
                            <div class="flex items-center gap-1"><span class="font-medium">Mon:</span><span class="px-2 py-1 bg-blue-100 text-blue-800 rounded">1, 2</span></div>
                            <div class="flex items-center gap-1"><span class="font-medium">Tue:</span><span class="px-2 py-1 bg-blue-100 text-blue-800 rounded">3, 4</span></div>
                            <div class="flex items-center gap-1"><span class="font-medium">Wed:</span><span class="px-2 py-1 bg-blue-100 text-blue-800 rounded">5, 6</span></div>
                            <div class="flex items-center gap-1"><span class="font-medium">Thu:</span><span class="px-2 py-1 bg-blue-100 text-blue-800 rounded">7, 8</span></div>
                            <div class="flex items-center gap-1"><span class="font-medium">Fri:</span><span class="px-2 py-1 bg-blue-100 text-blue-800 rounded">9, 0</span></div>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Coding Day</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i data-lucide="calendar" class="w-5 h-5 text-gray-400"></i>
                                </div>
                                <input type="text" id="editCodingDay" name="coding_day" readonly
                                    class="w-full pl-10 pr-4 py-3 border-2 border-gray-300 rounded-lg bg-gray-50"
                                    placeholder="Auto-generated">
                            </div>
                            <p class="text-xs text-gray-500">Auto-calculated from plate number</p>
                        </div>
                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Next Coding Date</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i data-lucide="calendar" class="w-5 h-5 text-gray-400"></i>
                                </div>
                                <input type="text" id="editNextCodingDate" readonly
                                    class="w-full pl-10 pr-4 py-3 border-2 border-gray-300 rounded-lg bg-gray-50"
                                    placeholder="Auto-generated">
                            </div>
                        </div>
                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Days Until Next Coding</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i data-lucide="clock" class="w-5 h-5 text-gray-400"></i>
                                </div>
                                <input type="text" id="editDaysUntilCoding" readonly
                                    class="w-full pl-10 pr-4 py-3 border-2 border-gray-300 rounded-lg bg-gray-50"
                                    placeholder="Auto-calculated">
                            </div>
                        </div>
                    </div>
                    <div id="editCodingStatusDisplay" class="mt-4"></div>
                </div>

                {{-- Section 6: Tracksolid Pro GPS Integration --}}
                <div class="mb-8">
                    <div class="flex items-center gap-2 mb-4">
                        <div class="p-2 bg-teal-100 rounded-lg">
                            <i data-lucide="satellite" class="w-5 h-5 text-teal-600"></i>
                        </div>
                        <h4 class="text-lg font-semibold text-gray-900">GPS Integration (Tracksolid Pro)</h4>
                    </div>
                    <div class="grid grid-cols-1 gap-6">
                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Device IMEI (Optional)</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i data-lucide="hash" class="w-5 h-5 text-gray-400"></i>
                                </div>
                                <input type="text" name="imei" id="editImei"
                                    class="w-full pl-10 pr-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent font-mono"
                                    placeholder="Enter 15-digit IMEI">
                            </div>
                            <p class="text-xs text-gray-500">Changing this will update the real-time tracking for this unit.</p>
                        </div>
                    </div>
                </div>
                </div> {{-- End Scrolling Content Area --}}

                {{-- Fixed Footer --}}
                <div class="p-4 border-t flex justify-end gap-3 shadow-inner bg-gray-50 shrink-0">
                    <button type="button" onclick="closeEditUnitModal()"
                        class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 text-sm font-bold transition-all">
                        Cancel
                    </button>
                    <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-bold shadow-lg shadow-blue-200/50 transition-all flex items-center gap-2">
                        <i data-lucide="save" class="w-4 h-4"></i> Update Unit
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Unit Details Modal --}}
    <div id="unitDetailsModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-6xl h-[95vh] flex flex-col overflow-hidden">
            {{-- Modal Header (Deep Navy matching login page) --}}
            <div class="bg-slate-800 p-4 shrink-0">
                <div class="flex justify-between items-center">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-white bg-opacity-20 rounded-lg">
                            <i data-lucide="info" class="w-5 h-5 text-white"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-white leading-tight">Unit Details</h3>
                            <p class="text-sm text-blue-100 leading-tight">Complete unit information and management</p>
                        </div>
                    </div>
                    <button onclick="closeUnitDetailsModal()" class="text-white hover:text-gray-200 transition-colors">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>
            </div>

            {{-- Single dynamic content area --}}
            <div id="unitDetailsContent" class="p-2 overflow-y-auto flex-1">
                {{-- Loading state --}}
                <div class="text-center py-8">
                    <i data-lucide="loader-2" class="w-8 h-8 mx-auto mb-4 text-gray-300 animate-spin"></i>
                    <p class="text-gray-500">Loading unit details...</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Flagged Units Modal --}}
    <div id="flaggedUnitsModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black bg-opacity-50">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-2xl max-h-[90vh] flex flex-col overflow-hidden">
            <div class="bg-gradient-to-r from-red-600 to-red-700 p-4 shrink-0">
                <div class="flex justify-between items-center">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-white bg-opacity-20 rounded-lg">
                            <i data-lucide="siren" class="w-5 h-5 text-white"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-white leading-tight">Flagged Units (Missing/At Risk)</h3>
                            <p class="text-sm text-red-100 leading-tight">Units that are under monitoring and their inactive days</p>
                        </div>
                    </div>
                    <button onclick="document.getElementById('flaggedUnitsModal').classList.add('hidden')" class="text-white hover:text-gray-200 transition-colors">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>
            </div>
            
            <div class="p-4 overflow-y-auto flex-1 bg-gray-50">
                <div id="flaggedUnitsContainer" class="space-y-4">
                    <div class="text-center py-8">
                        <i data-lucide="loader-2" class="w-8 h-8 mx-auto mb-4 text-gray-300 animate-spin"></i>
                        <p class="text-gray-500">Loading flagged units...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

@push('scripts')
<script>
    let currentViewMode = localStorage.getItem('unitViewMode') || 'table';
    
    function setViewMode(mode) {
        currentViewMode = mode;
        localStorage.setItem('unitViewMode', mode);
        document.getElementById('viewModeInput').value = mode;
        
        // Update UI — premium pill toggle active states
        const btnTable = document.getElementById('btn-view-table');
        const btnGrid  = document.getElementById('btn-view-grid');
        
        if (mode === 'table') {
            // Table ACTIVE
            btnTable.classList.add('bg-white', 'text-yellow-600', 'shadow-md', 'shadow-yellow-100/80');
            btnTable.classList.remove('text-gray-400');
            // Grid INACTIVE
            btnGrid.classList.remove('bg-white', 'text-yellow-600', 'shadow-md', 'shadow-yellow-100/80');
            btnGrid.classList.add('text-gray-400');
        } else {
            // Grid ACTIVE
            btnGrid.classList.add('bg-white', 'text-yellow-600', 'shadow-md', 'shadow-yellow-100/80');
            btnGrid.classList.remove('text-gray-400');
            // Table INACTIVE
            btnTable.classList.remove('bg-white', 'text-yellow-600', 'shadow-md', 'shadow-yellow-100/80');
            btnTable.classList.add('text-gray-400');
        }
        
        performSearch(1); // Re-fetch with new view mode
    }

    let searchTimer;
    const searchInput = document.querySelector('input[name="search"]');
    const statusFilter = document.querySelector('select[name="status"]');
    const sortFilter = document.querySelector('select[name="sort"]');
    const tableContainer = document.getElementById('unitsTableContainer');

    // ── Quick Stats ─────────────────────────────────────────────────
    const QUICK_STATS_URL = '{{ route("units.quick-stats") }}';

    function animateCount(el, target) {
        if (!el) return;
        const current = parseInt(el.textContent.replace(/\D/g,'')) || 0;
        if (current === target) { el.textContent = target; return; }
        const step = target > current ? 1 : -1;
        const diff = Math.abs(target - current);
        const delay = diff > 20 ? 16 : diff > 10 ? 30 : 50;
        let val = current;
        const tick = () => {
            val += step;
            el.textContent = val;
            if (val !== target) setTimeout(tick, delay);
        };
        setTimeout(tick, delay);
    }

    function refreshQuickStats() {
        const search = searchInput ? searchInput.value : '';
        const status = statusFilter ? statusFilter.value : '';
        const loading = document.getElementById('qs-loading');
        if (loading) loading.classList.remove('hidden');

        fetch(`${QUICK_STATS_URL}?search=${encodeURIComponent(search)}&status=${encodeURIComponent(status)}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => r.json())
        .then(data => {
            if (loading) loading.classList.add('hidden');

            const d = data.is_filtered ? data.filtered : data.global;

            animateCount(document.getElementById('qs-total'),    d.total);
            animateCount(document.getElementById('qs-onroad'),   d.on_road);
            animateCount(document.getElementById('qs-garage'),   d.garage);
            animateCount(document.getElementById('qs-workshop'), d.workshop);
            animateCount(document.getElementById('qs-coding'),   d.coding);

            const badge   = document.getElementById('qs-filter-badge');
            const context = document.getElementById('qs-context');
            if (data.is_filtered) {
                badge?.classList.remove('hidden');
                context?.classList.remove('hidden');
            } else {
                badge?.classList.add('hidden');
                context?.classList.add('hidden');
            }
        })
        .catch(() => { if (loading) loading.classList.add('hidden'); });
    }

    // ── performSearch ───────────────────────────────────────────────
    function performSearch(page = 1) {
        const query = searchInput.value;
        const status = statusFilter.value;
        const sort = sortFilter.value;

        // Visual feedback
        tableContainer.style.opacity = '0.5';
        tableContainer.style.pointerEvents = 'none';

        fetch(`{{ route('units.index') }}?search=${encodeURIComponent(query)}&status=${status}&sort=${sort}&page=${page}&view=${currentViewMode}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.text())
        .then(html => {
            tableContainer.innerHTML = html;
            tableContainer.style.opacity = '1';
            tableContainer.style.pointerEvents = 'auto';
            if (typeof lucide !== 'undefined') lucide.createIcons();
        })
        .catch(error => {
            console.error('Search failed:', error);
            tableContainer.style.opacity = '1';
            tableContainer.style.pointerEvents = 'auto';
        });
    }

    searchInput.addEventListener('input', () => {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(() => {
            performSearch(1);
            refreshQuickStats();
        }, 300);
    });

    statusFilter.addEventListener('change', () => {
        performSearch(1);
        refreshQuickStats();
    });
    sortFilter.addEventListener('change', () => performSearch(1));

    window.changePage = function(page) {
        performSearch(page);
        window.scrollTo({ top: 0, behavior: 'smooth' });
    };

    // Load quick stats on initial page load
    refreshQuickStats();

    window.showFlaggedUnitsModal = function() {
        const modal = document.getElementById('flaggedUnitsModal');
        const container = document.getElementById('flaggedUnitsContainer');
        modal.classList.remove('hidden');
        
        container.innerHTML = `
            <div class="text-center py-8">
                <i data-lucide="loader-2" class="w-8 h-8 mx-auto mb-4 text-gray-300 animate-spin"></i>
                <p class="text-gray-500">Loading flagged units...</p>
            </div>
        `;
        if (typeof lucide !== 'undefined') lucide.createIcons();
        
        fetch('{{ route("units.flagged") }}')
            .then(res => res.json())
            .then(data => {
                if(data.length === 0) {
                    container.innerHTML = `
                        <div class="text-center py-12">
                            <i data-lucide="check-circle" class="w-16 h-16 mx-auto mb-4 text-green-500"></i>
                            <h4 class="text-lg font-bold text-gray-900">All Clear!</h4>
                            <p class="text-gray-500">There are no units currently flagged as missing or categorized as at risk.</p>
                        </div>
                    `;
                    if (typeof lucide !== 'undefined') lucide.createIcons();
                    return;
                }
                
                let html = '<div class="flex flex-col gap-2 max-h-[400px] overflow-y-auto pr-2">';
                data.forEach(unit => {
                    const daysMissing = unit.days_inactive !== null && unit.days_inactive !== undefined ? unit.days_inactive : '?';
                    const daysColor = (daysMissing === '?' || daysMissing > 2) ? 'text-red-600 font-bold' : 'text-orange-600 font-bold';
                    const csrfToken = document.querySelector('meta[name="csrf-token"]') ? document.querySelector('meta[name="csrf-token"]').getAttribute('content') : '';
                    const badge = unit.is_at_risk 
                        ? `<span class="text-[9px] px-1.5 py-0.5 bg-red-100 text-red-700 rounded font-bold uppercase tracking-wide">🚨 Manually Flagged</span>`
                        : `<span class="text-[9px] px-1.5 py-0.5 bg-orange-100 text-orange-700 rounded font-bold uppercase tracking-wide">⚠️ Auto-Detected</span>`;
                    const borderColor = unit.is_at_risk ? 'border-red-500' : 'border-orange-400';
                    
                    const suspectDisplay = unit.is_vacant 
                        ? `<span class="bg-gray-200 text-gray-600 px-1.5 py-0.5 rounded font-bold italic tracking-tight">NO ASSIGNED DRIVER</span>`
                        : `<span class="font-black text-gray-900">${unit.suspect_driver || 'Unknown'}</span>`;
                    
                    const contactDisplay = unit.is_vacant 
                        ? `<span class="text-gray-400 italic">--</span>`
                        : (unit.suspect_contact 
                            ? `<a href="tel:${unit.suspect_contact}" class="text-blue-600 font-semibold hover:underline">${unit.suspect_contact}</a>`
                            : `<span class="text-gray-400 italic">No contact</span>`);

                    html += `
                        <div class="bg-white border-l-4 ${borderColor} shadow-sm rounded-lg p-3">
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2 mb-0.5 flex-wrap">
                                        <h5 class="font-bold text-base text-gray-900 leading-none">${unit.plate_number}</h5>
                                        ${badge}
                                    </div>
                                    <p class="text-[10px] text-gray-400">${unit.make || ''} ${unit.model || ''}</p>
                                    
                                    <div class="mt-2 bg-gray-50 rounded p-2 border border-gray-100 space-y-1">
                                        <div class="flex items-center gap-1.5 text-[11px]">
                                            <span class="${unit.is_vacant ? 'text-gray-400' : 'text-red-500'} font-bold w-24 flex-shrink-0 uppercase">SUSPECT:</span>
                                            ${suspectDisplay}
                                        </div>
                                        <div class="flex items-center gap-1.5 text-[11px]">
                                            <span class="text-gray-400 w-24 flex-shrink-0">Contact # :</span>
                                            ${contactDisplay}
                                        </div>
                                        <div class="flex items-center gap-1.5 text-[10px] pt-1 mt-1 border-t border-gray-100">
                                            <span class="text-gray-400 w-24 flex-shrink-0">Last Return By:</span>
                                            <span class="text-gray-600 italic font-medium">${unit.last_known_driver || 'None'}</span>
                                        </div>
                                        <div class="flex items-center gap-1.5 text-[10px]">
                                            <span class="text-gray-400 w-24 flex-shrink-0">Return Date:</span>
                                            <span class="text-gray-600">${unit.last_boundary_date || 'No record'}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex flex-col items-center gap-2 flex-shrink-0">
                                    <div class="text-center">
                                        <div class="text-[10px] uppercase font-bold text-gray-400">Missing For</div>
                                        <div class="text-lg ${daysColor} leading-none mt-0.5 font-bold">${daysMissing}</div>
                                        <div class="text-[10px] text-gray-400">day(s)</div>
                                    </div>
                                    <form method="POST" action="/units/toggle-status" class="m-0" onsubmit="return confirm('Clear MISSING flag on ${unit.plate_number}?');">
                                        <input type="hidden" name="_token" value="${csrfToken}">
                                        <input type="hidden" name="id" value="${unit.id}">
                                        <input type="hidden" name="new_status" value="active">
                                        <button type="submit" class="p-2 bg-green-100 text-green-700 hover:bg-green-200 rounded-lg transition shadow-sm" title="Clear Flag">
                                            <i data-lucide="check-circle" class="w-4 h-4"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    `;
                });
                html += '</div>';
                container.innerHTML = html;
                if (typeof lucide !== 'undefined') lucide.createIcons();
            })
            .catch(err => {
                container.innerHTML = '<div class="text-red-500 p-4 text-center"><i data-lucide="alert-circle" class="w-8 h-8 mx-auto mb-2"></i>Failed to load units.</div>';
                if (typeof lucide !== 'undefined') lucide.createIcons();
            });
    }

    window.closeFlaggedUnitsModalAndEdit = function(id) {
        document.getElementById('flaggedUnitsModal').classList.add('hidden');
        editUnit(id);
    }

    // Auto-open flagged units modal if 'open_flagged' parameter is present
    document.addEventListener('DOMContentLoaded', function() {
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('open_flagged')) {
            showFlaggedUnitsModal();
            // Remove the parameter from URL without refreshing for a cleaner Look
            const newUrl = window.location.pathname;
            window.history.replaceState({}, document.title, newUrl);
        }
    });
</script>
    <!-- Leaflet JS for Map -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
@endpush
@endsection

@push('scripts')
    <script>
        function formatCurrencyInput(input) {
            let value = input.value.replace(/[^0-9.]/g, '');
            if (value === '' || isNaN(parseFloat(value))) return;
            let num = parseFloat(value);
            input.value = num.toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }

        function unformatCurrencyInput(input) {
            input.value = input.value.replace(/,/g, '');
        }

        function editUnit(id) {
            window.currentEditingUnitId = id;
            fetch('{{ route("units.details") }}?id=' + id, {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
            })
            .then(r => {
                if (!r.ok) throw new Error('Server returned HTTP ' + r.status);
                return r.json();
            })
            .then(data => {
                // Guard: check for errors
                if (data.error) {
                    alert('Error: ' + data.error);
                    return;
                }
                const unit = data.unit;
                if (!unit) {
                    alert('Unit not found. Please refresh the page and try again.');
                    return;
                }
                // Basic Info
                if (document.getElementById('editPlateNumber')) document.getElementById('editPlateNumber').value = unit.plate_number || '';
                if (document.getElementById('editMake')) document.getElementById('editMake').value = unit.make || '';
                if (document.getElementById('editModel')) document.getElementById('editModel').value = unit.model || '';
                if (document.getElementById('editYear')) document.getElementById('editYear').value = unit.year || '';
                if (document.getElementById('editMotorNo')) document.getElementById('editMotorNo').value = unit.motor_no || '';
                if (document.getElementById('editChassisNo')) document.getElementById('editChassisNo').value = unit.chassis_no || '';
                if (document.getElementById('editStatus')) document.getElementById('editStatus').value = unit.status || 'active';
                if (document.getElementById('editUnitType')) document.getElementById('editUnitType').value = unit.unit_type || 'new';
                if (document.getElementById('editImei')) document.getElementById('editImei').value = unit.imei || '';
                
                // Financial
                const brInput = document.getElementById('editBoundaryRate');
                if (brInput) {
                    brInput.value = unit.boundary_rate || '0.00';
                    formatCurrencyInput(brInput);
                }
                const pcInput = document.getElementById('editPurchaseCost');
                if (pcInput) {
                    pcInput.value = unit.purchase_cost || '0.00';
                    formatCurrencyInput(pcInput);
                }
                if (document.getElementById('editPurchaseDate')) document.getElementById('editPurchaseDate').value = unit.purchase_date || '';

                // Drivers - set hidden selects and populate search inputs
                const d1Val = unit.driver_id || '';
                const d2Val = unit.secondary_driver_id || '';
                document.getElementById('edit_driver1').value = d1Val;
                document.getElementById('edit_driver2').value = d2Val;

                // Populate search inputs from select option text
                if (d1Val) {
                    const opt1 = document.querySelector(`#edit_driver1 option[value="${d1Val}"]`);
                    document.getElementById('edit_driver1_search').value = opt1 ? opt1.getAttribute('data-name') + (opt1.getAttribute('data-license') ? ' - ' + opt1.getAttribute('data-license') : '') : '';
                } else {
                    document.getElementById('edit_driver1_search').value = '';
                }
                if (d2Val) {
                    const opt2 = document.querySelector(`#edit_driver2 option[value="${d2Val}"]`);
                    document.getElementById('edit_driver2_search').value = opt2 ? opt2.getAttribute('data-name') + (opt2.getAttribute('data-license') ? ' - ' + opt2.getAttribute('data-license') : '') : '';
                } else {
                    document.getElementById('edit_driver2_search').value = '';
                }

                // Coding info - compute from plate number using top-level coding_day from API
                if (unit.plate_number) {
                    editUnitUpdateCodingFromPlate(unit.plate_number, data.coding_day || unit.coding_day || '');
                } else {
                    document.getElementById('editCodingDay').value = data.coding_day || unit.coding_day || '';
                    document.getElementById('editNextCodingDate').value = '';
                    document.getElementById('editDaysUntilCoding').value = '';
                }

                // IMEI Mapping
                if (document.getElementById('editImei')) document.getElementById('editImei').value = unit.imei || '';

                // Set form action
                document.getElementById('editUnitForm').action = '/units/' + id;

                // Show modal
                document.getElementById('editUnitModal').classList.remove('hidden');
                lucide.createIcons();
            })
            .catch(err => alert('Failed to load unit: ' + err));
        }

        function closeEditUnitModal() {
            document.getElementById('editUnitModal').classList.add('hidden');
            document.getElementById('editCodingStatusDisplay').innerHTML = '';
        }

        // Edit Unit - Searchable Driver Dropdowns
        function editUnitShowDropdown(driverType) {
            editUnitFilterDrivers(driverType);
            document.getElementById(driverType + '_dropdown').classList.remove('hidden');
        }
        function editUnitHideDropdown(driverType) {
            document.getElementById(driverType + '_dropdown').classList.add('hidden');
        }
        function editUnitFilterDrivers(driverType) {
            const searchInput = document.getElementById(driverType + '_search');
            const select = document.getElementById(driverType);
            const dropdown = document.getElementById(driverType + '_dropdown');
            const query = searchInput ? searchInput.value.toLowerCase() : '';
            const options = Array.from(select.options).slice(1);

            let html = '';
            options.forEach(opt => {
                const assigned = opt.getAttribute('data-assigned-unit') || '';
                if (assigned && String(assigned) !== String(window.currentEditingUnitId)) return;

                const name = opt.getAttribute('data-name') || '';
                const license = opt.getAttribute('data-license') || '';
                if (!query || name.toLowerCase().includes(query) || license.toLowerCase().includes(query)) {
                    html += `<div class="px-4 py-3 hover:bg-blue-50 cursor-pointer border-b border-gray-100 last:border-b-0"
                                 onmousedown="editUnitSelectDriver('${driverType}','${opt.value}','${name.replace(/'/g,"\\'")}','${license.replace(/'/g,"\\'")}')">
                                <div class="font-medium text-gray-900">${name}</div>
                                <div class="text-sm text-gray-500">${license || 'No License'}</div>
                             </div>`;
                }
            });
            dropdown.innerHTML = html || '<p class="px-4 py-3 text-sm text-gray-500">No drivers found</p>';
            dropdown.classList.remove('hidden');
        }
        function editUnitSelectDriver(driverType, value, name, license) {
            document.getElementById(driverType).value = value;
            document.getElementById(driverType + '_search').value = name + (license ? ' - ' + license : '');
            editUnitHideDropdown(driverType);
        }
        function editUnitClearDriver(driverType) {
            document.getElementById(driverType).value = '';
            document.getElementById(driverType + '_search').value = '';
        }

        // Edit Unit - coding helper (shared logic)
        function editUnitGetLastDigit(plateNumber) {
            plateNumber = plateNumber.toUpperCase().trim().replace(/[^A-Z0-9]/g, '');
            if (plateNumber.length > 0) {
                const last = plateNumber.slice(-1);
                if (/[A-Z]/.test(last)) return last.charCodeAt(0) - 64;
                if (/[0-9]/.test(last)) return parseInt(last);
            }
            return null;
        }
        function editUnitUpdateCodingFromPlate(plate, existingCodingDay) {
            const schedule = { Monday:[1,2], Tuesday:[3,4], Wednesday:[5,6], Thursday:[7,8], Friday:[9,0] };
            const lastDigit = editUnitGetLastDigit(plate);
            let codingDay = existingCodingDay || '';
            if (!codingDay) {
                for (const [day, endings] of Object.entries(schedule)) {
                    if (endings.includes(lastDigit)) { codingDay = day; break; }
                }
            }

            const today = new Date();
            const daysOfWeek = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
            const todayName = daysOfWeek[today.getDay()];
            let isCodingToday = (todayName === codingDay);
            let daysUntil = 0;
            let nextDate = new Date(today);

            if (!isCodingToday && codingDay) {
                for (let i = 1; i <= 7; i++) {
                    const test = new Date(today);
                    test.setDate(today.getDate() + i);
                    if (daysOfWeek[test.getDay()] === codingDay) { nextDate = test; daysUntil = i; break; }
                }
            }

            document.getElementById('editCodingDay').value = codingDay || '';
            document.getElementById('editNextCodingDate').value = codingDay ? nextDate.toLocaleDateString('en-US') : '';
            document.getElementById('editDaysUntilCoding').value = codingDay ? (isCodingToday ? 0 : daysUntil) : '';

            const display = document.getElementById('editCodingStatusDisplay');
            if (display) {
                if (!codingDay) {
                    display.innerHTML = '';
                } else if (isCodingToday) {
                    display.innerHTML = `<div class="p-3 rounded-lg border-2 border-red-500 bg-red-50 flex items-center gap-2"><i data-lucide="alert-triangle" class="w-5 h-5 text-red-600"></i><div><p class="text-sm font-semibold text-red-800">CODING TODAY!</p><p class="text-xs text-red-600">This unit is scheduled for coding today (${codingDay})</p></div></div>`;
                } else if (daysUntil === 1) {
                    display.innerHTML = `<div class="p-3 rounded-lg border-2 border-yellow-500 bg-yellow-50 flex items-center gap-2"><i data-lucide="clock" class="w-5 h-5 text-yellow-600"></i><div><p class="text-sm font-semibold text-yellow-800">CODING TOMORROW</p><p class="text-xs text-yellow-600">Next coding: ${codingDay}</p></div></div>`;
                } else {
                    display.innerHTML = `<div class="p-3 rounded-lg border-2 border-blue-400 bg-blue-50 flex items-center gap-2"><i data-lucide="calendar" class="w-5 h-5 text-blue-600"></i><div><p class="text-sm font-semibold text-blue-800">NEXT CODING</p><p class="text-xs text-blue-600">${codingDay} (${daysUntil} days)</p></div></div>`;
                }
                if (typeof lucide !== 'undefined') lucide.createIcons();
            }
        }
        function editUnitUpdateCoding() {
            const plate = document.getElementById('editPlateNumber')?.value || '';
            if (plate) editUnitUpdateCodingFromPlate(plate, '');
        }

        // =============================================
        // VIEW UNIT DETAILS - Matching backup's 8-tab structure
        // =============================================
        let currentViewUnitId = null;

        function viewUnitDetails(id) {
            currentViewUnitId = id;
            document.getElementById('unitDetailsModal').classList.remove('hidden');

            // Show loading state inside content div (same as backup)
            document.getElementById('unitDetailsContent').innerHTML = `
                <div class="text-center py-8">
                    <i data-lucide="loader-2" class="w-8 h-8 mx-auto mb-4 text-gray-300 animate-spin"></i>
                    <p class="text-gray-500">Loading unit details...</p>
                </div>
            `;
            if (typeof lucide !== 'undefined') lucide.createIcons();

            fetch('{{ route("units.details") }}?id=' + id, {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
            })
            .then(r => {
                if (!r.ok) throw new Error('Server returned HTTP ' + r.status);
                return r.json();
            })
            .then(data => {
                if (data.error) {
                    document.getElementById('unitDetailsContent').innerHTML = `<div class="text-center py-8 text-red-500"><i data-lucide="alert-circle" class="w-12 h-12 mx-auto mb-4"></i><p>${data.error}</p></div>`;
                    lucide.createIcons();
                    return;
                }
                const unit = data.unit;
                if (!unit) {
                    document.getElementById('unitDetailsContent').innerHTML = `<div class="text-center py-8 text-red-500"><i data-lucide="alert-circle" class="w-12 h-12 mx-auto mb-4"></i><p>Unit not found or failed to load.</p></div>`;
                    lucide.createIcons();
                    return;
                }

                const assignedDrivers = data.assigned_drivers || [];
                const roi = data.roi_data || {};
                const bHist = data.boundary_history || [];
                const maint = data.maintenance_records || [];
                const locInfo = data.location_info || {};
                const dashcam = data.dashcam_info || {};

                // --- Coding calculations (matching backup logic) ---
                const plate = unit.plate_number || '';
                const lastChar = plate.replace(/[^A-Z0-9]/gi, '').slice(-1).toUpperCase();
                const lastDigit = /[0-9]/.test(lastChar) ? parseInt(lastChar) : (lastChar.charCodeAt(0) - 64);
                const codingSchedule = { Monday:[1,2], Tuesday:[3,4], Wednesday:[5,6], Thursday:[7,8], Friday:[9,0] };
                let codingDay = data.coding_day || 'Not Set';
                let nextCodingDate = '', daysUntilCoding = 0;
                const today = new Date();
                const dayNames = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
                const todayName = dayNames[today.getDay()];
                if (codingDay && codingDay !== 'Not Set') {
                    if (todayName === codingDay) {
                        nextCodingDate = today.toLocaleDateString('en-US', {month:'short', day:'2-digit', year:'numeric'});
                        daysUntilCoding = 0;
                    } else {
                        const cdIdx = dayNames.indexOf(codingDay);
                        let diff = (cdIdx - today.getDay() + 7) % 7;
                        if (diff === 0) diff = 7;
                        const nextDate = new Date(today);
                        nextDate.setDate(today.getDate() + diff);
                        nextCodingDate = nextDate.toLocaleDateString('en-US', {month:'short', day:'2-digit', year:'numeric'});
                        daysUntilCoding = diff;
                    }
                }

                // --- Build the 8-tab HTML matching backup's unit_details_modal.php ---
                const roiPct = parseFloat(roi.roi_percentage || 0);
                const roiColor = roiPct > 0 ? 'green' : 'red';

                let driversOverviewHtml = '';
                if (assignedDrivers.length > 0) {
                    assignedDrivers.forEach(d => {
                        driversOverviewHtml += `<div class="bg-gray-50 p-3 rounded">
                            <div class="font-medium">${d.full_name || ''}</div>
                            <div class="text-sm text-gray-600">${d.license_number || ''}</div>
                            <div class="text-sm text-gray-600">Contact: ${d.contact_number || 'N/A'}</div>
                        </div>`;
                    });
                }

                let driversTabHtml = '';
                if (assignedDrivers.length > 0) {
                    assignedDrivers.forEach(d => {
                        driversTabHtml += `<div class="border border-gray-200 rounded-lg p-4">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h5 class="font-semibold text-gray-900">${d.full_name || ''}</h5>
                                    <p class="text-sm text-gray-600">License: ${d.license_number || ''}</p>
                                    <p class="text-sm text-gray-600">Contact: ${d.contact_number || 'N/A'}</p>
                                </div>
                                <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">Active</span>
                            </div>
                            <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                <div><span class="text-gray-600">License Number:</span><p class="font-medium">${d.license_number || 'N/A'}</p></div>
                                <div><span class="text-gray-600">Contact:</span><p class="font-medium">${d.contact_number || 'N/A'}</p></div>
                                <div><span class="text-gray-600">Daily Target:</span><p class="font-medium">₱${parseFloat(d.daily_boundary_target || 1100).toLocaleString('en-PH', {minimumFractionDigits:2})}</p></div>
                                <div><span class="text-gray-600">Hire Date:</span><p class="font-medium">${d.hire_date || 'Not set'}</p></div>
                                <div><span class="text-gray-600">License Expiry:</span><p class="font-medium">${d.license_expiry || 'Not set'}</p></div>
                            </div>
                        </div>`;
                    });
                } else {
                    driversTabHtml = `<div class="text-center py-8 text-gray-500"><i data-lucide="users" class="w-12 h-12 mx-auto mb-4 text-gray-300"></i><p>No drivers assigned to this unit</p></div>`;
                }

                let boundaryRowsHtml = '';
                if (bHist.length > 0) {
                    bHist.forEach(bh => {
                        boundaryRowsHtml += `<tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${bh.date || ''}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${bh.full_name || 'N/A'}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${bh.remarks || '---'}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-green-600">₱${parseFloat(bh.actual_boundary || 0).toLocaleString('en-PH', {minimumFractionDigits:2})}</td>
                        </tr>`;
                    });
                }

                let maintHtml = '';
                if (maint.length > 0) {
                    maint.forEach(m => {
                        // Build parts details HTML if available
                        let partsDetailsHtml = '';
                        if (m.parts_details && m.parts_details.length > 0) {
                            const parts = m.parts_details.filter(p => p.part_id != null);
                            const others = m.parts_details.filter(p => p.part_id == null);

                            partsDetailsHtml = '<div class="mt-4 space-y-3">';

                            if (parts.length > 0) {
                                partsDetailsHtml += '<div class="bg-blue-50 rounded-xl border border-blue-100 overflow-hidden"><div class="px-3 py-2 bg-blue-100 border-b border-blue-200"><span class="text-[10px] font-black text-blue-800 uppercase tracking-widest">🔧 Parts Replaced</span></div><div class="divide-y divide-blue-100">';
                                parts.forEach(p => {
                                    const supplierBadge = p.supplier_name
                                        ? `<span class="inline-block mt-0.5 px-1.5 py-0.5 bg-indigo-100 text-indigo-700 rounded text-[9px] font-bold truncate max-w-[120px]" title="${p.supplier_name}">${p.supplier_name}</span>`
                                        : '';
                                    partsDetailsHtml += `<div class="px-3 py-2 flex items-start justify-between gap-2">
                                        <div class="flex-1 min-w-0">
                                            <p class="text-xs font-semibold text-gray-900 truncate leading-tight" title="${p.part_name}">${p.part_name}</p>
                                            <div class="flex items-center gap-1.5 flex-wrap mt-0.5">
                                                ${p.quantity > 1 ? `<span class="text-[9px] text-gray-500 font-bold">x${p.quantity}</span>` : ''}
                                                ${supplierBadge}
                                            </div>
                                        </div>
                                        <span class="text-xs font-black text-blue-700 flex-shrink-0 ml-2">₱${parseFloat(p.total || 0).toLocaleString('en-PH', {minimumFractionDigits:2})}</span>
                                    </div>`;
                                });
                                const partsTotal = parts.reduce((sum, p) => sum + parseFloat(p.total || 0), 0);
                                partsDetailsHtml += `</div><div class="flex justify-between items-center px-3 py-2 bg-blue-100 border-t border-blue-200">
                                    <span class="text-[10px] font-black text-blue-800 uppercase">Parts Subtotal</span>
                                    <span class="text-xs font-black text-blue-700">₱${partsTotal.toLocaleString('en-PH', {minimumFractionDigits:2})}</span>
                                </div></div>`;
                            }

                            if (others.length > 0) {
                                partsDetailsHtml += '<div class="bg-orange-50 rounded-xl border border-orange-100 overflow-hidden"><div class="px-3 py-2 bg-orange-100 border-b border-orange-200"><span class="text-[10px] font-black text-orange-800 uppercase tracking-widest">🛠 Other Costs & Services</span></div><div class="divide-y divide-orange-100">';
                                others.forEach(o => {
                                    partsDetailsHtml += `<div class="px-3 py-2 flex items-start justify-between gap-2">
                                        <p class="text-xs font-semibold text-gray-900 flex-1 min-w-0 truncate leading-tight" title="${o.part_name}">${o.part_name}</p>
                                        <span class="text-xs font-black text-orange-700 flex-shrink-0 ml-2">₱${parseFloat(o.total || 0).toLocaleString('en-PH', {minimumFractionDigits:2})}</span>
                                    </div>`;
                                });
                                const othersTotal = others.reduce((sum, o) => sum + parseFloat(o.total || 0), 0);
                                partsDetailsHtml += `</div><div class="flex justify-between items-center px-3 py-2 bg-orange-100 border-t border-orange-200">
                                    <span class="text-[10px] font-black text-orange-800 uppercase">Services Subtotal</span>
                                    <span class="text-xs font-black text-orange-700">₱${othersTotal.toLocaleString('en-PH', {minimumFractionDigits:2})}</span>
                                </div></div>`;
                            }

                            partsDetailsHtml += '</div>';
                        }

                        const statusBadge = m.status === 'completed'
                            ? `<span class="px-2 py-0.5 bg-green-100 text-green-700 rounded-full text-[9px] font-black uppercase tracking-widest">✓ Completed</span>`
                            : m.status === 'cancelled'
                                ? `<span class="px-2 py-0.5 bg-red-100 text-red-700 rounded-full text-[9px] font-black uppercase tracking-widest">✗ Cancelled</span>`
                                : `<span class="px-2 py-0.5 bg-yellow-100 text-yellow-700 rounded-full text-[9px] font-black uppercase tracking-widest">⏳ Pending</span>`;

                        const driverHtml = m.driver_name
                            ? `<div class="flex items-center gap-1.5">
                                <span class="text-[9px] font-black text-gray-400 uppercase w-20 flex-shrink-0">Driver</span>
                                <span class="text-xs font-semibold text-gray-800 truncate" title="${m.driver_name}">${m.driver_name}</span>
                               </div>`
                            : '';

                        maintHtml += `
                        <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden shadow-sm">
                            {{-- Card Header --}}
                            <div class="bg-gradient-to-r from-slate-700 to-slate-800 px-4 py-3 flex items-center justify-between gap-3">
                                <div class="flex-1 min-w-0">
                                    <p class="text-white font-black text-sm truncate leading-tight" title="${m.maintenance_type || m.type || 'Maintenance'}">${m.maintenance_type || m.type || 'Maintenance'}</p>
                                    <p class="text-slate-300 text-[10px] font-medium">${m.date_started || m.date || 'No date'}</p>
                                </div>
                                <div class="flex-shrink-0 text-right">
                                    <p class="text-orange-300 font-black text-base leading-tight">₱${parseFloat(m.total_cost || m.cost || 0).toLocaleString('en-PH', {minimumFractionDigits:2})}</p>
                                    <p class="text-slate-400 text-[9px] uppercase font-bold">Total Cost</p>
                                </div>
                            </div>

                            {{-- Card Body: 2-column info grid --}}
                            <div class="px-4 py-3">
                                <div class="grid grid-cols-2 gap-x-4 gap-y-2 text-xs mb-2">
                                    <div class="min-w-0">
                                        <span class="text-[9px] font-black text-gray-400 uppercase block">Mechanic</span>
                                        <p class="font-semibold text-gray-800 truncate" title="${m.mechanic_name || 'N/A'}">${m.mechanic_name || 'N/A'}</p>
                                    </div>
                                    <div class="min-w-0">
                                        <span class="text-[9px] font-black text-gray-400 uppercase block">Status</span>
                                        <div class="mt-0.5">${statusBadge}</div>
                                    </div>
                                    ${driverHtml ? `<div class="col-span-2 min-w-0">${driverHtml}</div>` : ''}
                                    <div class="col-span-2 min-w-0">
                                        <span class="text-[9px] font-black text-gray-400 uppercase block">Description</span>
                                        <p class="font-medium text-gray-700 text-xs leading-snug line-clamp-3">${m.description || m.notes || 'No description provided.'}</p>
                                    </div>
                                </div>

                                ${partsDetailsHtml}
                            </div>
                        </div>`;
                    });
                } else {
                    maintHtml = `<div class="text-center py-12 text-gray-400"><i data-lucide="wrench" class="w-12 h-12 mx-auto mb-3 text-gray-200"></i><p class="font-semibold text-sm">No maintenance records found</p></div>`;
                }

                const roiPrgW = Math.min(100, Math.max(0, roiPct)).toFixed(1);
                const invPerMonth = parseFloat(roi.total_investment || 0) / 12;
                const mthBnd = parseFloat(roi.monthly_revenue || roi.monthly_boundary || 0);
                const bndPrgW = invPerMonth > 0 ? Math.min(100, (mthBnd / invPerMonth) * 100).toFixed(1) : 0;

                document.getElementById('unitDetailsContent').innerHTML = `
                <div class="space-y-6">
                    <!-- Unit Summary Card (Top) -->
                    <div class="bg-slate-800 p-6 rounded-2xl text-white shadow-lg">
                        <div class="flex justify-between items-center">
                            <div class="flex items-center gap-4">
                                <div class="p-3 bg-white bg-opacity-20 rounded-xl">
                                    <i data-lucide="car" class="w-8 h-8 text-white"></i>
                                </div>
                                <div>
                                    <div class="flex items-center gap-3 mb-1">
                                        <h3 class="text-2xl font-black tracking-tight leading-none">${unit.plate_number || ''}</h3>
                                        ${unit.status !== 'at_risk' ? `<span class="px-2.5 py-1 bg-white bg-opacity-20 rounded-full text-[10px] font-black uppercase tracking-widest border border-white/20">${unit.status || ''}</span>` : ''}
                                        <span class="px-2.5 py-1 bg-white bg-opacity-20 rounded-full text-[10px] font-black uppercase tracking-widest border border-white/20">${unit.unit_type || 'Standard'}</span>
                                        ${unit.status === 'at_risk' ? `<span class="px-2.5 py-1 bg-red-500 text-white rounded-full text-[10px] font-black uppercase tracking-widest animate-pulse border border-red-400">🚨 AT RISK</span>` : ''}
                                    </div>
                                    <p class="text-blue-100 font-medium">${(unit.make || '') + ' ' + (unit.model || '') + ' (' + (unit.year || '') + ')'}</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="text-2xl font-black leading-none mb-1">₱${parseFloat(unit.boundary_rate || 0).toLocaleString('en-PH', {minimumFractionDigits:2})}</div>
                                <p class="text-blue-100 text-xs font-bold uppercase tracking-widest opacity-80">Daily Boundary Rate</p>
                            </div>
                        </div>
                    </div>

                    <!-- Justified Tabs Navigation -->
                    <div class="border-b border-gray-100 bg-gray-50/50 rounded-t-xl p-1">
                        <nav class="flex w-full gap-1">
                            <button onclick="showTab('overview')" class="tab-btn flex-1 py-3 px-1 border-b-2 border-blue-600 font-black text-[10px] uppercase tracking-widest text-blue-600 transition-all duration-200" data-tab="overview">Overview</button>
                            <button onclick="showTab('drivers')" class="tab-btn flex-1 py-3 px-1 border-b-2 border-transparent font-black text-[10px] uppercase tracking-widest text-gray-400 hover:text-gray-600 hover:border-gray-300 transition-all duration-200" data-tab="drivers">Drivers</button>
                            <button onclick="showTab('coding')" class="tab-btn flex-1 py-3 px-1 border-b-2 border-transparent font-black text-[10px] uppercase tracking-widest text-gray-400 hover:text-gray-600 hover:border-gray-300 transition-all duration-200" data-tab="coding">Coding</button>
                            <button onclick="showTab('boundary')" class="tab-btn flex-1 py-3 px-1 border-b-2 border-transparent font-black text-[10px] uppercase tracking-widest text-gray-400 hover:text-gray-600 hover:border-gray-300 transition-all duration-200" data-tab="boundary">Boundary</button>
                            <button onclick="showTab('maintenance')" class="tab-btn flex-1 py-3 px-1 border-b-2 border-transparent font-black text-[10px] uppercase tracking-widest text-gray-400 hover:text-gray-600 hover:border-gray-300 transition-all duration-200" data-tab="maintenance">Maintenance</button>
                            <button onclick="showTab('roi')" class="tab-btn flex-1 py-3 px-1 border-b-2 border-transparent font-black text-[10px] uppercase tracking-widest text-gray-400 hover:text-gray-600 hover:border-gray-300 transition-all duration-200" data-tab="roi">ROI</button>
                            <button onclick="showTab('location')" class="tab-btn flex-1 py-3 px-1 border-b-2 border-transparent font-black text-[10px] uppercase tracking-widest text-gray-400 hover:text-gray-600 hover:border-gray-300 transition-all duration-200" data-tab="location">Location</button>
                            <button onclick="showTab('dashcam')" class="tab-btn flex-1 py-3 px-1 border-b-2 border-transparent font-black text-[10px] uppercase tracking-widest text-gray-400 hover:text-gray-600 hover:border-gray-300 transition-all duration-200" data-tab="dashcam">Dashcam</button>
                        </nav>
                    </div>

                    <!-- Tab Content Area -->
                    <div id="tabContent" class="min-h-[480px]">
                        <!-- Overview Tab -->
                        <div id="overview-tab" class="tab-content animate-in fade-in duration-300">
                            <!-- Quick Stats Grid -->
                            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                                <div class="bg-white border border-gray-100 rounded-2xl p-4 shadow-sm hover:shadow-md transition-all">
                                    <div class="flex items-center gap-4">
                                        <div class="p-2.5 bg-blue-50 rounded-xl"><i data-lucide="users" class="w-5 h-5 text-blue-600"></i></div>
                                        <div><p class="text-[10px] text-gray-400 font-black uppercase tracking-widest">Drivers</p><p class="text-xl font-black text-gray-900">${assignedDrivers.length}/2</p></div>
                                    </div>
                                </div>
                                <div class="bg-white border border-gray-100 rounded-2xl p-4 shadow-sm hover:shadow-md transition-all">
                                    <div class="flex items-center gap-4">
                                        <div class="p-2.5 bg-green-50 rounded-xl"><i data-lucide="calendar" class="w-5 h-5 text-green-600"></i></div>
                                        <div><p class="text-[10px] text-gray-400 font-black uppercase tracking-widest">Next Coding</p><p class="text-xl font-black text-gray-900">${daysUntilCoding === 0 ? 'Today' : daysUntilCoding + 'd'}</p></div>
                                    </div>
                                </div>
                                <div class="bg-white border border-gray-100 rounded-2xl p-4 shadow-sm hover:shadow-md transition-all">
                                    <div class="flex items-center gap-4">
                                        <div class="p-2.5 bg-purple-50 rounded-xl"><i data-lucide="trending-up" class="w-5 h-5 text-purple-600"></i></div>
                                        <div><p class="text-[10px] text-gray-400 font-black uppercase tracking-widest">ROI</p><p class="text-xl font-black text-gray-900">${roiPct.toFixed(1)}%</p></div>
                                    </div>
                                </div>
                                <div class="bg-white border border-gray-100 rounded-2xl p-4 shadow-sm hover:shadow-md transition-all">
                                    <div class="flex items-center gap-4">
                                        <div class="p-2.5 bg-orange-50 rounded-xl"><i data-lucide="wrench" class="w-5 h-5 text-orange-600"></i></div>
                                        <div><p class="text-[10px] text-gray-400 font-black uppercase tracking-widest">Maint Jobs</p><p class="text-xl font-black text-gray-900">${maint.length}</p></div>
                                    </div>
                                </div>
                            </div>

                            <!-- Main Info Grid -->
                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                                <!-- Basic Info Card -->
                                <div class="bg-white border border-gray-100 rounded-2xl p-6 shadow-sm">
                                    <h4 class="text-xs font-black text-gray-900 mb-5 flex items-center gap-2 uppercase tracking-widest border-b border-gray-50 pb-3">
                                        <i data-lucide="info" class="w-4 h-4 text-blue-600"></i> Basic Information
                                    </h4>
                                    <div class="space-y-4 text-sm">
                                        <div class="flex justify-between items-center"><span class="text-gray-400 font-bold uppercase text-[10px] tracking-tight">Plate Number</span><span class="font-black text-gray-900 bg-gray-50 px-2 py-1 rounded">${unit.plate_number || ''}</span></div>
                                        <div class="flex justify-between items-center"><span class="text-gray-400 font-bold uppercase text-[10px] tracking-tight">Vehicle</span><span class="font-black text-gray-700">${(unit.make || '') + ' ' + (unit.model || '')}</span></div>
                                        <div class="flex justify-between items-center"><span class="text-gray-400 font-bold uppercase text-[10px] tracking-tight">Year</span><span class="font-black text-gray-700">${unit.year || ''}</span></div>
                                        <div class="flex justify-between items-center pt-3 border-t border-gray-50"><span class="text-gray-400 font-bold uppercase text-[10px] tracking-tight">Created By</span><span class="font-bold text-gray-600 text-xs">${unit.created_by_name || 'System'}</span></div>
                                        <div class="flex justify-between items-center"><span class="text-gray-400 font-bold uppercase text-[10px] tracking-tight">Last Update</span><span class="font-bold text-gray-600 text-xs">${unit.updated_at_fmt || 'N/A'}</span></div>
                                        <div class="flex justify-between items-center pt-4 border-t border-gray-50 mt-4">
                                            <span class="text-gray-900 font-black uppercase text-[11px] tracking-widest">Active Rate</span>
                                            <span class="text-2xl font-black text-blue-600">₱${parseFloat(unit.boundary_rate || 0).toLocaleString('en-PH', {minimumFractionDigits:2})}</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Assignment Card -->
                                <div class="bg-white border border-gray-100 rounded-2xl p-6 shadow-sm">
                                    <h4 class="text-xs font-black text-gray-900 mb-5 flex items-center gap-2 uppercase tracking-widest border-b border-gray-50 pb-3">
                                        <i data-lucide="users" class="w-4 h-4 text-blue-600"></i> Driver Assignment
                                    </h4>
                                    <div class="space-y-4">
                                        <div class="flex justify-between items-center mb-4">
                                            <span class="text-gray-400 font-bold uppercase text-[10px] tracking-tight">Status</span>
                                            <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase ${assignedDrivers.length >= 2 ? 'bg-red-50 text-red-600 border border-red-100' : 'bg-green-50 text-green-600 border border-green-100'}">${assignedDrivers.length >= 2 ? 'Full' : 'Available'}</span>
                                        </div>
                                        ${driversOverviewHtml ? '<div class="space-y-3">' + driversOverviewHtml.replace(/bg-gray-50/g, 'bg-gray-50 border border-gray-100 rounded-xl p-4') + '</div>' : `
                                            <div class="text-center py-10">
                                                <div class="bg-gray-50 w-12 h-12 rounded-full flex items-center justify-center mx-auto mb-3">
                                                    <i data-lucide="user-x" class="w-6 h-6 text-gray-300"></i>
                                                </div>
                                                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">No Drivers Assigned</p>
                                            </div>
                                        `}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Drivers Tab -->
                        <div id="drivers-tab" class="tab-content hidden animate-in fade-in duration-300">
                            <div class="bg-white border border-gray-100 rounded-2xl p-6 shadow-sm">
                                <h4 class="text-sm font-black text-gray-900 mb-6 flex items-center gap-2 uppercase tracking-widest border-b border-gray-50 pb-4">
                                    <i data-lucide="users" class="w-5 h-5 text-blue-600"></i> Assigned Drivers Details
                                </h4>
                                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                                    ${driversTabHtml.replace(/border border-gray-200/g, 'bg-gray-50 border border-gray-100 shadow-sm').replace(/p-4/g, 'p-6').replace(/rounded-lg/g, 'rounded-2xl')}
                                </div>
                            </div>
                        </div>

                        <!-- Coding Tab -->
                        <div id="coding-tab" class="tab-content hidden animate-in fade-in duration-300">
                            <div class="bg-white border border-gray-100 rounded-2xl p-6 shadow-sm">
                                <h4 class="text-sm font-black text-gray-900 mb-6 flex items-center gap-2 uppercase tracking-widest border-b border-gray-50 pb-4">
                                    <i data-lucide="calendar" class="w-5 h-5 text-blue-600"></i> MMDA Coding Schedule
                                </h4>
                                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                                    <div class="bg-gray-50 rounded-2xl p-6 border border-gray-100">
                                        <h5 class="font-black text-xs text-gray-400 uppercase tracking-widest mb-6 border-b border-gray-200 pb-2">Current Unit Status</h5>
                                        <div class="space-y-4 text-sm">
                                            <div class="flex justify-between items-center"><span class="text-gray-500 font-bold uppercase text-[10px]">Coding Day</span><span class="px-3 py-1 bg-blue-600 text-white rounded-full text-[10px] font-black uppercase tracking-widest">${codingDay}</span></div>
                                            <div class="flex justify-between items-center"><span class="text-gray-500 font-bold uppercase text-[10px]">Plate Ending</span><span class="font-black text-gray-900 text-lg">${lastChar || '-'}</span></div>
                                            <div class="flex justify-between items-center"><span class="text-gray-500 font-bold uppercase text-[10px]">Next Schedule</span><span class="font-black text-gray-900">${nextCodingDate || '-'}</span></div>
                                            <div class="flex justify-between items-center pt-4 border-t border-gray-200"><span class="text-gray-500 font-bold uppercase text-[10px]">Remaining</span><span class="font-black ${daysUntilCoding === 0 ? 'text-red-600' : 'text-green-600'} text-lg">${daysUntilCoding === 0 ? 'TODAY' : daysUntilCoding + ' Days'}</span></div>
                                        </div>
                                    </div>
                                    <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-inner">
                                        <h5 class="font-black text-xs text-gray-400 uppercase tracking-widest mb-4">Standard MMDA Reference</h5>
                                        <div class="space-y-2">
                                            <div class="flex justify-between p-3 bg-gray-50 rounded-xl border border-gray-100">
                                                <span class="font-black text-gray-600 text-[10px] uppercase">Monday</span>
                                                <span class="font-black text-blue-600">1, 2</span>
                                            </div>
                                            <div class="flex justify-between p-3 bg-gray-50 rounded-xl border border-gray-100">
                                                <span class="font-black text-gray-600 text-[10px] uppercase">Tuesday</span>
                                                <span class="font-black text-blue-600">3, 4</span>
                                            </div>
                                            <div class="flex justify-between p-3 bg-gray-50 rounded-xl border border-gray-100">
                                                <span class="font-black text-gray-600 text-[10px] uppercase">Wednesday</span>
                                                <span class="font-black text-blue-600">5, 6</span>
                                            </div>
                                            <div class="flex justify-between p-3 bg-gray-50 rounded-xl border border-gray-100">
                                                <span class="font-black text-gray-600 text-[10px] uppercase">Thursday</span>
                                                <span class="font-black text-blue-600">7, 8</span>
                                            </div>
                                            <div class="flex justify-between p-3 bg-gray-50 rounded-xl border border-gray-100">
                                                <span class="font-black text-gray-600 text-[10px] uppercase">Friday</span>
                                                <span class="font-black text-blue-600">9, 0</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Boundary Tab -->
                        <div id="boundary-tab" class="tab-content hidden animate-in fade-in duration-300">
                            <div class="bg-white border border-gray-100 rounded-2xl p-6 shadow-sm">
                                <h4 class="text-sm font-black text-gray-900 mb-6 flex items-center gap-2 uppercase tracking-widest border-b border-gray-50 pb-4">
                                    <i data-lucide="coins" class="w-5 h-5 text-blue-600"></i> Boundary Collection History
                                </h4>
                                ${boundaryRowsHtml ? `
                                    <div class="overflow-hidden border border-gray-100 rounded-2xl shadow-sm">
                                        <table class="min-w-full divide-y divide-gray-200">
                                            <thead class="bg-gray-50">
                                                <tr>
                                                    <th class="px-6 py-4 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Date</th>
                                                    <th class="px-6 py-4 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Driver</th>
                                                    <th class="px-6 py-4 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Remarks</th>
                                                    <th class="px-6 py-4 text-right text-[10px] font-black text-gray-400 uppercase tracking-widest">Amount</th>
                                                </tr>
                                            </thead>
                                            <tbody class="bg-white divide-y divide-gray-100">
                                                ${boundaryRowsHtml}
                                            </tbody>
                                        </table>
                                    </div>
                                ` : `
                                    <div class="text-center py-20 bg-gray-50 rounded-2xl border border-dashed border-gray-200">
                                        <div class="p-4 bg-white rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4 shadow-sm">
                                            <i data-lucide="banknote" class="w-8 h-8 text-gray-300"></i>
                                        </div>
                                        <p class="text-xs font-black text-gray-400 uppercase tracking-widest">No transaction history found</p>
                                    </div>
                                `}
                            </div>
                        </div>

                        <!-- Maintenance Tab -->
                        <div id="maintenance-tab" class="tab-content hidden animate-in fade-in duration-300">
                            <div class="bg-white border border-gray-100 rounded-2xl p-6 shadow-sm">
                                <h4 class="text-sm font-black text-gray-900 mb-6 flex items-center justify-center gap-2 uppercase tracking-widest border-b border-gray-50 pb-4">
                                    <i data-lucide="wrench" class="w-5 h-5 text-blue-600"></i> Vehicle Maintenance Records
                                    ${parseFloat(roi.total_expenses || 0) > 0 ? `<span class="ml-2 px-2 py-0.5 bg-orange-50 text-orange-600 border border-orange-100 rounded text-[10px] font-black uppercase">Total: ₱${parseFloat(roi.total_expenses || 0).toLocaleString('en-PH', {minimumFractionDigits:2})}</span>` : ''}
                                </h4>
                                <div class="flex flex-col gap-6 max-w-3xl mx-auto">
                                    ${maintHtml.replace(/border border-gray-200 bg-white/g, 'bg-gray-50 border border-gray-100 shadow-sm').replace(/p-4/g, 'p-6').replace(/rounded-lg/g, 'rounded-2xl')}
                                </div>
                            </div>
                        </div>

                        <!-- ROI Tab -->
                        <div id="roi-tab" class="tab-content hidden animate-in fade-in duration-300">
                            <div class="space-y-6">
                                <div class="bg-gradient-to-br from-indigo-600 to-purple-700 p-8 rounded-2xl text-white shadow-xl relative overflow-hidden">
                                    <div class="absolute top-0 right-0 p-8 opacity-10">
                                        <i data-lucide="trending-up" class="w-32 h-32"></i>
                                    </div>
                                    <h4 class="text-xl font-black mb-6 uppercase tracking-widest flex items-center gap-3">
                                        <i data-lucide="bar-chart-3" class="w-6 h-6"></i> ROI Performance Analysis
                                    </h4>
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                                        <div class="bg-white/10 backdrop-blur-md p-6 rounded-2xl border border-white/10">
                                            <p class="text-indigo-100 text-[10px] font-black uppercase tracking-widest mb-1">Total Investment</p>
                                            <p class="text-2xl font-black">₱${parseFloat(roi.total_investment || 0).toLocaleString('en-PH', {minimumFractionDigits:2})}</p>
                                        </div>
                                        <div class="bg-white/10 backdrop-blur-md p-6 rounded-2xl border border-white/10">
                                            <p class="text-indigo-100 text-[10px] font-black uppercase tracking-widest mb-1">Total Net Revenue</p>
                                            <p class="text-2xl font-black text-green-300">₱${parseFloat(roi.total_revenue || 0).toLocaleString('en-PH', {minimumFractionDigits:2})}</p>
                                        </div>
                                        <div class="bg-white/10 backdrop-blur-md p-6 rounded-2xl border border-white/10">
                                            <p class="text-indigo-100 text-[10px] font-black uppercase tracking-widest mb-1">Total Expenses</p>
                                            <p class="text-2xl font-black text-red-300">₱${parseFloat(roi.total_expenses || 0).toLocaleString('en-PH', {minimumFractionDigits:2})}</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                                    <div class="bg-white border border-gray-100 rounded-2xl p-8 shadow-sm">
                                        <h4 class="text-xs font-black text-gray-900 mb-6 uppercase tracking-widest border-b border-gray-50 pb-3">Key Metrics</h4>
                                        <div class="space-y-6">
                                            <div class="flex justify-between items-center">
                                                <span class="text-gray-400 font-bold uppercase text-[10px] tracking-widest">ROI Percentage</span>
                                                <span class="text-2xl font-black text-${roiColor}-600">${roiPct.toFixed(1)}%</span>
                                            </div>
                                            <div class="flex justify-between items-center">
                                                <span class="text-gray-400 font-bold uppercase text-[10px] tracking-widest">Payback Period</span>
                                                <span class="text-2xl font-black text-blue-600">${parseFloat(roi.payback_period || 0).toFixed(1)} <span class="text-sm font-bold text-gray-400 uppercase">mths</span></span>
                                            </div>
                                            <div class="flex justify-between items-center">
                                                <span class="text-gray-400 font-bold uppercase text-[10px] tracking-widest">Avg Monthly Revenue</span>
                                                <span class="text-2xl font-black text-green-600">₱${parseFloat(roi.monthly_revenue || roi.monthly_boundary || 0).toLocaleString('en-PH', {minimumFractionDigits:2})}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="bg-white border border-gray-100 rounded-2xl p-8 shadow-sm">
                                        <h4 class="text-xs font-black text-gray-900 mb-8 uppercase tracking-widest border-b border-gray-50 pb-3">Goal Progress</h4>
                                        <div class="space-y-8">
                                            <div>
                                                <div class="flex justify-between items-center mb-2">
                                                    <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Investment Achievement</span>
                                                    <span class="text-sm font-black text-indigo-600">${roiPct.toFixed(1)}%</span>
                                                </div>
                                                <div class="w-full bg-gray-100 rounded-full h-4 p-1 shadow-inner">
                                                    <div class="bg-gradient-to-r from-indigo-500 to-purple-600 h-2 rounded-full shadow-sm" style="width:${roiPrgW}%"></div>
                                                </div>
                                            </div>
                                            <div>
                                                <div class="flex justify-between items-center mb-2">
                                                    <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Monthly Target Efficiency</span>
                                                    <span class="text-sm font-black text-green-600">₱${invPerMonth.toLocaleString('en-PH', {minimumFractionDigits:0})} Target</span>
                                                </div>
                                                <div class="w-full bg-gray-100 rounded-full h-4 p-1 shadow-inner">
                                                    <div class="bg-gradient-to-r from-green-400 to-emerald-600 h-2 rounded-full shadow-sm" style="width:${bndPrgW}%"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Location Tab -->
                        <div id="location-tab" class="tab-content hidden animate-in fade-in duration-300">
                            <div class="bg-white border border-gray-100 rounded-2xl p-6 shadow-sm">
                                <h4 class="text-sm font-black text-gray-900 mb-6 flex items-center gap-2 uppercase tracking-widest border-b border-gray-50 pb-4">
                                    <i data-lucide="map-pin" class="w-5 h-5 text-blue-600"></i> Real-time GPS Location
                                </h4>
                                <div class="space-y-6">
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                        <div class="bg-gray-50 border border-gray-100 rounded-2xl p-4">
                                            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Current Address</p>
                                            <p class="text-xs font-black text-gray-900">${locInfo.current_location || 'Not Available'}</p>
                                        </div>
                                        <div class="bg-gray-50 border border-gray-100 rounded-2xl p-4">
                                            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Last Update</p>
                                            <p class="text-xs font-black text-gray-900">${locInfo.last_location_update || 'Never'}</p>
                                        </div>
                                        <div class="bg-gray-50 border border-gray-100 rounded-2xl p-4 flex items-center justify-between">
                                            <div>
                                                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">GPS Status</p>
                                                <span class="px-2 py-1 text-[9px] font-black uppercase rounded-full ${locInfo.gps_enabled ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}">${locInfo.gps_enabled ? 'Active' : 'Offline'}</span>
                                            </div>
                                            <i data-lucide="satellite" class="w-6 h-6 ${locInfo.gps_enabled ? 'text-green-500' : 'text-red-400'}"></i>
                                        </div>
                                    </div>
                                    <div id="unitDetailMapContainer" class="relative rounded-2xl overflow-hidden border border-gray-100 bg-gray-50 flex flex-col items-center justify-center p-12 text-center shadow-inner" style="height: 400px;">
                                        <div class="mb-6 p-6 bg-blue-100 rounded-full shadow-sm animate-pulse">
                                            <i data-lucide="navigation" class="w-12 h-12 text-blue-600"></i>
                                        </div>
                                        <h4 class="text-lg font-black text-gray-900 mb-2 uppercase tracking-tight">Tracksolid Pro Enterprise</h4>
                                        <p class="text-sm text-gray-500 mb-8 max-w-md mx-auto">This unit is tracked via real-time satellite identification. Access the full live map for movement history and geofencing.</p>
                                        
                                        <a href="/live-tracking?unit=${unit.id}" class="inline-flex items-center gap-3 px-8 py-4 bg-blue-600 hover:bg-blue-700 text-white text-sm font-black rounded-2xl transition-all shadow-lg hover:shadow-blue-200 hover:-translate-y-1 uppercase tracking-widest">
                                            <i data-lucide="map" class="w-5 h-5"></i>
                                            Open Live Tracking Map
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Dashcam Tab -->
                        <div id="dashcam-tab" class="tab-content hidden animate-in fade-in duration-300">
                            <div class="bg-white border border-gray-100 rounded-2xl p-6 shadow-sm">
                                <h4 class="text-sm font-black text-gray-900 mb-6 flex items-center gap-2 uppercase tracking-widest border-b border-gray-50 pb-4">
                                    <i data-lucide="video" class="w-5 h-5 text-blue-600"></i> Dashcam & Surveillance
                                </h4>
                                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                                    <div class="lg:col-span-1 space-y-4">
                                        <div class="bg-gray-50 border border-gray-100 rounded-2xl p-5">
                                            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-4">Device Status</p>
                                            <div class="space-y-4 text-xs font-bold">
                                                <div class="flex justify-between items-center"><span>System</span><span class="px-2 py-1 rounded-full ${dashcam.dashcam_enabled ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'} text-[10px]">${dashcam.dashcam_enabled ? 'ENABLED' : 'DISABLED'}</span></div>
                                                <div class="flex justify-between items-center"><span>Connection</span><span class="px-2 py-1 rounded-full ${dashcam.dashcam_status === 'Online' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'} text-[10px]">${dashcam.dashcam_status || 'OFFLINE'}</span></div>
                                                <div class="flex justify-between items-center pt-2 border-t border-gray-200"><span>Storage</span><span class="text-indigo-600 font-black">${parseFloat(dashcam.storage_used || 0).toFixed(1)} / ${parseFloat(dashcam.storage_total || 32).toFixed(0)} GB</span></div>
                                            </div>
                                        </div>
                                        <div class="bg-blue-600 rounded-2xl p-5 text-white shadow-lg">
                                            <h5 class="text-xs font-black uppercase tracking-widest mb-2">Remote Access</h5>
                                            <p class="text-[10px] text-blue-100 mb-4 font-medium">Request a live stream or download recorded footage from the cloud.</p>
                                            <button class="w-full py-3 bg-white text-blue-600 rounded-xl text-xs font-black uppercase tracking-widest hover:bg-blue-50 transition-colors">Request Stream</button>
                                        </div>
                                    </div>
                                    <div class="lg:col-span-2 bg-gray-900 rounded-2xl flex flex-col items-center justify-center min-h-[300px] border border-gray-800 shadow-inner relative overflow-hidden">
                                        <div class="absolute top-4 left-4 flex items-center gap-2">
                                            <div class="w-2 h-2 bg-red-500 rounded-full animate-ping"></div>
                                            <span class="text-white/40 text-[9px] font-black uppercase tracking-widest">No Active Stream</span>
                                        </div>
                                        <i data-lucide="video-off" class="w-16 h-16 text-white/10 mb-4"></i>
                                        <p class="text-white/30 text-xs font-black uppercase tracking-widest">Preview Unavailable</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                        <!-- Drivers Tab -->
                        <div id="drivers-tab" class="tab-content hidden">
                            <div class="bg-white border border-gray-200 rounded-lg p-3">
                                <h4 class="text-sm font-semibold text-gray-900 mb-2">Assigned Drivers</h4>
                                <div class="space-y-2">${driversTabHtml.replace(/p-4/g, 'p-2').replace(/p-6/g, 'p-3').replace(/text-lg/g, 'text-base').replace(/text-sm/g, 'text-xs')}</div>
                            </div>
                        </div>

                        <!-- Coding Tab -->
                        <div id="coding-tab" class="tab-content hidden">
                            <div class="bg-white border border-gray-200 rounded-lg p-3">
                                <h4 class="text-sm font-semibold text-gray-900 mb-2">MMDA Coding Schedule</h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                    <div>
                                        <h5 class="font-medium text-xs text-gray-900 mb-1.5">Current Coding Information</h5>
                                        <div class="space-y-1 text-xs">
                                            <div class="flex justify-between"><span class="text-gray-600">Coding Day:</span><span class="px-1.5 py-0.5 bg-blue-100 text-blue-800 rounded-full text-[10px] font-medium">${codingDay}</span></div>
                                            <div class="flex justify-between"><span class="text-gray-600">Last Digit:</span><span class="font-medium">${lastChar || '-'}</span></div>
                                            <div class="flex justify-between"><span class="text-gray-600">Next Coding:</span><span class="font-medium">${nextCodingDate || '-'}</span></div>
                                            <div class="flex justify-between"><span class="text-gray-600">Days Until Coding:</span><span class="font-medium ${daysUntilCoding === 0 ? 'text-red-600' : 'text-green-600'}">${daysUntilCoding === 0 ? 'Today' : daysUntilCoding + ' days'}</span></div>
                                            <div class="flex justify-between"><span class="text-gray-600">Coding Status:</span><span class="px-1.5 py-0.5 text-[10px] rounded-full ${daysUntilCoding === 0 ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800'}">${daysUntilCoding === 0 ? 'Coding Today' : 'No Coding'}</span></div>
                                        </div>
                                    </div>
                                    <div>
                                        <h5 class="font-medium text-xs text-gray-900 mb-1.5">MMDA Coding Schedule</h5>
                                        <div class="space-y-0.5 text-[10px]">
                                            <div class="flex justify-between p-1 bg-blue-50 rounded"><span>Monday</span><span class="font-medium">1, 2</span></div>
                                            <div class="flex justify-between p-1 bg-green-50 rounded"><span>Tuesday</span><span class="font-medium">3, 4</span></div>
                                            <div class="flex justify-between p-1 bg-yellow-50 rounded"><span>Wednesday</span><span class="font-medium">5, 6</span></div>
                                            <div class="flex justify-between p-1 bg-orange-50 rounded"><span>Thursday</span><span class="font-medium">7, 8</span></div>
                                            <div class="flex justify-between p-1 bg-red-50 rounded"><span>Friday</span><span class="font-medium">9, 0</span></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Boundary Tab -->
                        <div id="boundary-tab" class="tab-content hidden">
                            <div class="bg-white border border-gray-200 rounded-lg p-3">
                                <h4 class="text-sm font-semibold text-gray-900 mb-2">Boundary Collection History</h4>
                                ${boundaryRowsHtml ? `<div class="overflow-x-auto"><table class="min-w-full divide-y divide-gray-200"><thead class="bg-gray-50"><tr><th class="px-3 py-2 text-left text-[10px] font-medium text-gray-500 uppercase">Date</th><th class="px-3 py-2 text-left text-[10px] font-medium text-gray-500 uppercase">Driver</th><th class="px-3 py-2 text-left text-[10px] font-medium text-gray-500 uppercase">License</th><th class="px-3 py-2 text-left text-[10px] font-medium text-gray-500 uppercase">Amount</th></tr></thead><tbody class="bg-white divide-y divide-gray-200">${boundaryRowsHtml.replace(/px-6 py-4/g, 'px-3 py-1.5').replace(/text-sm/g, 'text-xs')}</tbody></table></div>` : '<div class="text-center py-6 text-gray-500"><i data-lucide="dollar-sign" class="w-10 h-10 mx-auto mb-2 text-gray-300"></i><p class="text-xs">No boundary history found</p></div>'}
                            </div>
                        </div>

                        <!-- Maintenance Tab -->
                        <div id="maintenance-tab" class="tab-content hidden">
                            <div class="bg-white border border-gray-200 rounded-lg p-3">
                                <h4 class="text-sm font-semibold text-gray-900 mb-2">Maintenance Records</h4>
                                <div class="space-y-2">${maintHtml.replace(/p-4/g, 'p-2').replace(/p-6/g, 'p-3').replace(/text-lg/g, 'text-base').replace(/text-sm/g, 'text-xs')}</div>
                            </div>
                        </div>

                        <!-- ROI Tab - Aggressively Miniaturized -->
                        <div id="roi-tab" class="tab-content hidden">
                            <div class="space-y-3">
                                <div class="bg-gradient-to-r from-purple-500 to-purple-600 p-3 rounded-lg text-white">
                                    <h4 class="text-base font-bold mb-2">ROI Analysis</h4>
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-2">
                                        <div><p class="text-purple-100 text-[10px]">Total Investment</p><p class="text-base font-bold">₱${parseFloat(roi.total_investment || 0).toLocaleString('en-PH', {minimumFractionDigits:2})}</p></div>
                                        <div><p class="text-purple-100 text-[10px]">Total Revenue</p><p class="text-base font-bold">₱${parseFloat(roi.total_revenue || 0).toLocaleString('en-PH', {minimumFractionDigits:2})}</p></div>
                                        <div><p class="text-purple-100 text-[10px]">Total Expenses</p><p class="text-base font-bold">₱${parseFloat(roi.total_expenses || 0).toLocaleString('en-PH', {minimumFractionDigits:2})}</p></div>
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                    <div class="bg-white border border-gray-200 rounded-lg p-3">
                                        <h4 class="text-xs font-semibold text-gray-900 mb-2">ROI Metrics</h4>
                                        <div class="space-y-2 text-xs">
                                            <div class="flex justify-between items-center"><span class="text-gray-600">ROI %</span><span class="font-bold text-${roiColor}-600">${roiPct.toFixed(1)}%</span></div>
                                            <div class="flex justify-between items-center"><span class="text-gray-600">Payback</span><span class="font-bold text-blue-600">${parseFloat(roi.payback_period || 0).toFixed(1)} mths</span></div>
                                            <div class="flex justify-between items-center"><span class="text-gray-600">Mth Rev</span><span class="font-bold text-green-600">₱${parseFloat(roi.monthly_revenue || roi.monthly_boundary || 0).toLocaleString('en-PH', {minimumFractionDigits:2})}</span></div>
                                        </div>
                                    </div>
                                    <div class="bg-white border border-gray-200 rounded-lg p-3">
                                        <h4 class="text-xs font-semibold text-gray-900 mb-2">ROI Progress</h4>
                                        <div class="space-y-3">
                                            <div>
                                                <div class="flex justify-between items-center mb-1"><span class="text-[10px] text-gray-600">Achievement</span><span class="text-[10px] font-medium">${roiPct.toFixed(1)}%</span></div>
                                                <div class="w-full bg-gray-200 rounded-full h-2.5"><div class="bg-gradient-to-r from-purple-500 to-purple-600 h-2.5 rounded-full" style="width:${roiPrgW}%"></div></div>
                                            </div>
                                            <div>
                                                <div class="flex justify-between items-center mb-1"><span class="text-[10px] text-gray-600">Monthly Target</span><span class="text-[10px] font-medium">₱${invPerMonth.toLocaleString('en-PH', {minimumFractionDigits:0})}</span></div>
                                                <div class="w-full bg-gray-200 rounded-full h-2.5"><div class="bg-gradient-to-r from-green-500 to-green-600 h-2.5 rounded-full" style="width:${bndPrgW}%"></div></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Location Tab -->
                        <div id="location-tab" class="tab-content hidden">
                            <div class="bg-white border border-gray-200 rounded-lg p-3">
                                <h4 class="text-sm font-semibold text-gray-900 mb-2">Location Information</h4>
                                <div class="space-y-3">
                                    {{-- Info Row --}}
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-2">
                                        <div class="flex justify-between bg-gray-50 rounded-lg px-2 py-1.5">
                                            <span class="text-gray-500 text-[10px]">Location:</span>
                                            <span class="font-medium text-[10px] text-right">${locInfo.current_location || 'Not Available'}</span>
                                        </div>
                                        <div class="flex justify-between bg-gray-50 rounded-lg px-2 py-1.5">
                                            <span class="text-gray-500 text-[10px]">Update:</span>
                                            <span class="font-medium text-[10px] text-right">${locInfo.last_location_update || 'Never'}</span>
                                        </div>
                                        <div class="flex justify-between bg-gray-50 rounded-lg px-2 py-1.5 items-center">
                                            <span class="text-gray-500 text-[10px]">GPS:</span>
                                            <span class="px-1.5 py-0.5 text-[9px] rounded-full ${locInfo.gps_enabled ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}">
                                                ${locInfo.gps_enabled ? 'Enabled' : 'Disabled'}
                                            </span>
                                        </div>
                                    </div>

                                    {{-- Tracking Status Display --}}
                                    <div id="unitDetailMapContainer" class="relative rounded-lg overflow-hidden border border-gray-200 bg-gray-50 flex flex-col items-center justify-center p-6 text-center" style="height: 320px;">
                                        <div class="mb-4 p-4 bg-indigo-100 rounded-full">
                                            <i data-lucide="satellite" class="w-12 h-12 text-indigo-600"></i>
                                        </div>
                                        <h4 class="text-sm font-bold text-gray-900 mb-1">Tracksolid Pro Enterprise</h4>
                                        <p class="text-xs text-gray-500 mb-4 px-4">This unit is tracked via real-time API using IMEI identification.</p>
                                        
                                        <div class="w-full max-w-xs space-y-2 mb-6">
                                            <div class="flex justify-between items-center bg-white p-2 border border-gray-200 rounded text-xs">
                                                <span class="text-gray-500">Device IMEI:</span>
                                                <span class="font-mono font-bold text-indigo-700">${unit.imei || 'Not Set'}</span>
                                            </div>
                                        </div>

                                        <a href="/live-tracking?unit=${unit.id}" class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-lg transition-colors">
                                            <i data-lucide="map-pin" class="w-4 h-4"></i>
                                            View on Live Tracking Map
                                        </a>
                                    </div>

                                    ${locInfo.coordinates ? `
                                        <div class="flex justify-between bg-gray-50 rounded-lg px-2 py-1.5">
                                            <span class="text-gray-500 text-[10px]">Coordinates:</span>
                                            <span class="font-medium text-[10px]">${locInfo.coordinates}</span>
                                        </div>
                                    ` : ''}
                                </div>
                            </div>
                        </div>

                        <!-- Dashcam Tab -->
                        <div id="dashcam-tab" class="tab-content hidden">
                            <div class="bg-white border border-gray-200 rounded-lg p-3">
                                <h4 class="text-sm font-semibold text-gray-900 mb-2">Dashcam Information</h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                    <div>
                                        <div class="space-y-1.5 text-xs">
                                            <div class="flex justify-between"><span class="text-gray-600">Status:</span><span class="px-1.5 py-0.5 text-[9px] rounded-full ${dashcam.dashcam_enabled ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}">${dashcam.dashcam_enabled ? 'Enabled' : 'Disabled'}</span></div>
                                            <div class="flex justify-between"><span class="text-gray-600">Connect:</span><span class="px-1.5 py-0.5 text-[9px] rounded-full ${dashcam.dashcam_status === 'Online' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'}">${dashcam.dashcam_status || 'Offline'}</span></div>
                                            <div class="flex justify-between"><span class="text-gray-600">Storage:</span><span class="font-medium text-[10px]">${parseFloat(dashcam.storage_used || 0).toFixed(1)} / ${parseFloat(dashcam.storage_total || 32).toFixed(0)} GB</span></div>
                                        </div>
                                    </div>
                                    <div class="bg-gray-100 rounded-lg h-20 flex items-center justify-center"><div class="text-center text-gray-500"><i data-lucide="video" class="w-6 h-6 mx-auto mb-1"></i><p class="text-[10px]">Video placeholder</p></div></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                `;

                // Re-init lucide icons
                if (typeof lucide !== 'undefined') lucide.createIcons();
                // Show overview tab by default
                setTimeout(() => { showTab('overview'); }, 50);
            })
            .catch(err => {
                document.getElementById('unitDetailsContent').innerHTML = `
                    <div class="text-center py-8">
                        <i data-lucide="alert-circle" class="w-12 h-12 mx-auto mb-4 text-red-500"></i>
                        <p class="text-red-500">Failed to load unit details</p>
                    </div>
                `;
                if (typeof lucide !== 'undefined') lucide.createIcons();
            });
        }

        function closeUnitDetailsModal() {
            document.getElementById('unitDetailsModal').classList.add('hidden');
        }

        // showTab() - matches backup's exact tab switching logic
        function showTab(tabName) {
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.add('hidden');
            });
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.classList.remove('border-blue-500', 'text-blue-600');
                btn.classList.add('border-transparent', 'text-gray-500');
            });
            const selectedTab = document.getElementById(tabName + '-tab');
            if (selectedTab) selectedTab.classList.remove('hidden');
            const activeBtn = document.querySelector('[data-tab="' + tabName + '"]');
            if (activeBtn) {
                activeBtn.classList.remove('border-transparent', 'text-gray-500');
                activeBtn.classList.add('border-blue-500', 'text-blue-600');
            }
            setTimeout(() => { if (typeof lucide !== 'undefined') lucide.createIcons(); }, 100);
        }
    </script>
@endpush

@push('scripts')
<script>
// =============================================
// ADD UNIT MODAL - Driver Searchable Dropdown
// =============================================
function addUnitShowDropdown(driverType) {
    addUnitFilterDrivers(driverType);
    document.getElementById(driverType + '_dropdown').classList.remove('hidden');
}
function addUnitHideDropdown(driverType) {
    document.getElementById(driverType + '_dropdown').classList.add('hidden');
}
function addUnitFilterDrivers(driverType) {
    const searchInput = document.getElementById(driverType + '_search');
    const select = document.getElementById(driverType);
    const dropdown = document.getElementById(driverType + '_dropdown');
    const query = searchInput ? searchInput.value.toLowerCase() : '';
    const options = Array.from(select.options).slice(1);

    let html = '';
    options.forEach(opt => {
        const assigned = opt.getAttribute('data-assigned-unit') || '';
        if (assigned) return;

        const name = opt.getAttribute('data-name') || '';
        const license = opt.getAttribute('data-license') || '';
        const display = name + ' - ' + license;
        if (!query || name.toLowerCase().includes(query) || license.toLowerCase().includes(query)) {
            html += `<div class="px-4 py-3 hover:bg-yellow-50 cursor-pointer border-b border-gray-100 last:border-b-0"
                         onmousedown="addUnitSelectDriver('${driverType}','${opt.value}','${name.replace(/'/g,"\\'")}','${license.replace(/'/g,"\\'")}')">
                        <div class="font-medium text-gray-900">${name}</div>
                        <div class="text-sm text-gray-500">${license || 'No License'}</div>
                     </div>`;
        }
    });
    dropdown.innerHTML = html || '<p class="px-4 py-3 text-sm text-gray-500">No drivers found</p>';
    dropdown.classList.remove('hidden');
}
function addUnitSelectDriver(driverType, value, name, license) {
    document.getElementById(driverType).value = value;
    document.getElementById(driverType + '_search').value = name + (license ? ' - ' + license : '');
    addUnitHideDropdown(driverType);
}
function addUnitClearDriver(driverType) {
    document.getElementById(driverType).value = '';
    document.getElementById(driverType + '_search').value = '';
}

window.boundaryRules = @json($boundary_rules ?? []);

function getRateByYear(year, plate = '') {
    if (!year) return 1100;
    const rules = window.boundaryRules;
    const matches = rules.filter(r => year >= r.start_year && year <= r.end_year);
    const rule = matches.length > 0 ? matches[0] : null;
    
    // Base rate
    const base = rule ? rule.regular_rate : 1100;
    
    // Check coding if plate provided
    if (plate) {
        const codingDay = deriveCodingDay(plate);
        const today = new Date().toLocaleDateString('en-US', { weekday: 'long' });
        if (codingDay && today === codingDay) {
            return (rule && rule.coding_rate > 0) ? rule.coding_rate : (base / 2);
        }
    }
    
    return base;
}

function deriveCodingDay(plate) {
    if (!plate) return null;
    const cleanPlate = plate.toString().trim();
    let lastChar = cleanPlate.slice(-1);
    
    if (isNaN(parseInt(lastChar))) {
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

document.addEventListener('DOMContentLoaded', function() {
    const addYearInput = document.querySelector('input[name="year"]');
    if (addYearInput) {
        addYearInput.addEventListener('input', function() {
            const rate = getRateByYear(this.value);
            const rateInput = document.getElementById('addBoundaryRate');
            if (rateInput) rateInput.value = parseFloat(rate).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        });
    }

    const editYearInput = document.getElementById('editYear');
    if (editYearInput) {
        editYearInput.addEventListener('input', function() {
            const rate = getRateByYear(this.value);
            const rateInput = document.getElementById('editBoundaryRate');
            if (rateInput) rateInput.value = parseFloat(rate).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        });
    }
});

// =============================================
// ADD UNIT MODAL - Auto Coding Calculation
// =============================================
function addUnitGetLastDigit(plateNumber) {
    plateNumber = plateNumber.toUpperCase().trim().replace(/[^A-Z0-9]/g, '');
    if (plateNumber.length > 0) {
        const last = plateNumber.slice(-1);
        if (/[A-Z]/.test(last)) return last.charCodeAt(0) - 64;
        if (/[0-9]/.test(last)) return parseInt(last);
    }
    return null;
}
function addUnitUpdateCoding() {
    const plate = document.getElementById('addPlateNumber')?.value || '';
    if (!plate) return;

    const schedule = { Monday:[1,2], Tuesday:[3,4], Wednesday:[5,6], Thursday:[7,8], Friday:[9,0] };
    const lastDigit = addUnitGetLastDigit(plate);
    let codingDay = '';
    for (const [day, endings] of Object.entries(schedule)) {
        if (endings.includes(lastDigit)) { codingDay = day; break; }
    }

    const today = new Date();
    const daysOfWeek = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
    const todayName = daysOfWeek[today.getDay()];
    let isCodingToday = (todayName === codingDay);
    let daysUntil = 0;
    let nextDate = new Date(today);

    if (!isCodingToday && codingDay) {
        for (let i = 1; i <= 7; i++) {
            const test = new Date(today);
            test.setDate(today.getDate() + i);
            if (daysOfWeek[test.getDay()] === codingDay) { nextDate = test; daysUntil = i; break; }
        }
    }



    document.getElementById('addCodingDay').value = codingDay || '';
    document.getElementById('addNextCodingDate').value = codingDay ? nextDate.toLocaleDateString('en-US') : '';
    document.getElementById('addDaysUntilCoding').value = codingDay ? (isCodingToday ? 0 : daysUntil) : '';

    // Auto-set status to coding if today is coding day
    if (isCodingToday) {
        document.getElementById('addUnitStatus').value = 'coding';
    }

    // Update coding status display
    const display = document.getElementById('addCodingStatusDisplay');
    if (!codingDay) {
        display.innerHTML = '<div class="p-3 rounded-lg border-2 border-gray-300 bg-gray-50 flex items-center gap-2"><i data-lucide="info" class="w-5 h-5 text-gray-500"></i><div><p class="text-sm font-semibold text-gray-800">NO CODING SCHEDULE</p><p class="text-xs text-gray-500">Plate number does not match MMDA schedule</p></div></div>';
    } else if (isCodingToday) {
        display.innerHTML = `<div class="p-3 rounded-lg border-2 border-red-500 bg-red-50 flex items-center gap-2"><i data-lucide="alert-triangle" class="w-5 h-5 text-red-600"></i><div><p class="text-sm font-semibold text-red-800">CODING TODAY!</p><p class="text-xs text-red-600">This unit is scheduled for coding today (${codingDay})</p></div></div>`;
    } else if (daysUntil === 1) {
        display.innerHTML = `<div class="p-3 rounded-lg border-2 border-yellow-500 bg-yellow-50 flex items-center gap-2"><i data-lucide="clock" class="w-5 h-5 text-yellow-600"></i><div><p class="text-sm font-semibold text-yellow-800">CODING TOMORROW</p><p class="text-xs text-yellow-600">Next coding: ${codingDay}</p></div></div>`;
    } else {
        display.innerHTML = `<div class="p-3 rounded-lg border-2 border-blue-400 bg-blue-50 flex items-center gap-2"><i data-lucide="calendar" class="w-5 h-5 text-blue-600"></i><div><p class="text-sm font-semibold text-blue-800">NEXT CODING</p><p class="text-xs text-blue-600">${codingDay} (${daysUntil} days)</p></div></div>`;
    }
    if (typeof lucide !== 'undefined') lucide.createIcons();
}

// =============================================
// ADD UNIT MODAL - GPS/Dashcam Devices
// =============================================
let addUnitGPS = [], addUnitDashcam = [];

function addUnitAddGPS() {
    const id = prompt('Enter GPS Device ID:');
    if (id && id.trim()) {
        addUnitGPS.push({ id: id.trim() });
        addUnitRenderGPS();
    }
}
function addUnitAddDashcam() {
    const id = prompt('Enter Dashcam Device ID:');
    if (id && id.trim()) {
        addUnitDashcam.push({ id: id.trim() });
        addUnitRenderDashcam();
    }
}
function addUnitRemoveGPS(index) { addUnitGPS.splice(index, 1); addUnitRenderGPS(); }
function addUnitRemoveDashcam(index) { addUnitDashcam.splice(index, 1); addUnitRenderDashcam(); }
function addUnitRenderGPS() {
    const list = document.getElementById('addGPSDevicesList');
    if (!addUnitGPS.length) { list.innerHTML = '<p class="text-sm text-gray-500 text-center py-2">No GPS devices added</p>'; return; }
    list.innerHTML = addUnitGPS.map((d, i) => `
        <div class="flex items-center justify-between p-2 bg-indigo-50 rounded-lg">
            <div class="flex items-center gap-2"><i data-lucide="map-pin" class="w-4 h-4 text-indigo-600"></i>
                <span class="text-sm font-medium">${d.id}</span>
                <span class="px-2 py-0.5 bg-green-100 text-green-800 text-xs rounded-full">Active</span>
            </div>
            <button type="button" onclick="addUnitRemoveGPS(${i})" class="text-red-500 hover:text-red-700"><i data-lucide="x" class="w-4 h-4"></i></button>
        </div>
        <input type="hidden" name="gps_devices[]" value="${d.id}">
    `).join('');
    if (typeof lucide !== 'undefined') lucide.createIcons();
}
function addUnitRenderDashcam() {
    const list = document.getElementById('addDashcamDevicesList');
    if (!addUnitDashcam.length) { list.innerHTML = '<p class="text-sm text-gray-500 text-center py-2">No dashcam devices added</p>'; return; }
    list.innerHTML = addUnitDashcam.map((d, i) => `
        <div class="flex items-center justify-between p-2 bg-purple-50 rounded-lg">
            <div class="flex items-center gap-2"><i data-lucide="camera" class="w-4 h-4 text-purple-600"></i>
                <span class="text-sm font-medium">${d.id}</span>
                <span class="px-2 py-0.5 bg-green-100 text-green-800 text-xs rounded-full">Active</span>
            </div>
            <button type="button" onclick="addUnitRemoveDashcam(${i})" class="text-red-500 hover:text-red-700"><i data-lucide="x" class="w-4 h-4"></i></button>
        </div>
        <input type="hidden" name="dashcam_devices[]" value="${d.id}">
    `).join('');
    if (typeof lucide !== 'undefined') lucide.createIcons();
}

// Reset the Add Unit modal
function resetAddUnitModal() {
    document.getElementById('addUnitForm')?.reset();
    addUnitClearDriver('add_driver1');
    addUnitClearDriver('add_driver2');
    document.getElementById('addCodingDay').value = '';
    document.getElementById('addNextCodingDate').value = '';
    document.getElementById('addDaysUntilCoding').value = '';
    document.getElementById('addCodingStatusDisplay').innerHTML = '';
    addUnitGPS = []; addUnitDashcam = [];
    addUnitRenderGPS(); addUnitRenderDashcam();
}

// Real-time table filtering
document.addEventListener('DOMContentLoaded', function() {
    setViewMode(currentViewMode);
    const searchInput = document.getElementById('tableSearchInput');
    const tableBody = document.querySelector('tbody.bg-white.divide-y.divide-gray-200');
    
    if (searchInput && tableBody) {
        searchInput.addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const rows = tableBody.querySelectorAll('tr.cursor-pointer');
            let visibleCount = 0;

            rows.forEach(row => {
                const text = row.innerText.toLowerCase();
                if (text.includes(searchTerm)) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });

            // Handle "No units found" message
            let emptyMsgRow = document.getElementById('clientEmptySearchRow');
            if (visibleCount === 0 && rows.length > 0) {
                if (!emptyMsgRow) {
                    emptyMsgRow = document.createElement('tr');
                    emptyMsgRow.id = 'clientEmptySearchRow';
                    emptyMsgRow.innerHTML = `
                        <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                            <i data-lucide="search" class="w-12 h-12 mx-auto mb-4 text-gray-300"></i>
                            <p>No units match your search.</p>
                        </td>
                    `;
                    tableBody.appendChild(emptyMsgRow);
                    if (typeof lucide !== 'undefined') lucide.createIcons();
                } else {
                    emptyMsgRow.style.display = '';
                }
            } else if (emptyMsgRow) {
                emptyMsgRow.style.display = 'none';
            }
        });
    }
});
</script>
@endpush
