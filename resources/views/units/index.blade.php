@extends('layouts.app')

@section('title', 'Unit Management - Euro System')
@section('page-heading', 'Unit Management')
@section('page-subheading', 'Manage your fleet of taxi units')
@section('main-padding', 'p-0')
@section('content')

    <!-- Leaflet CSS for Map -->
    <link rel="stylesheet" href="{{ asset('assets/leaflet/leaflet.css') }}" />
    <style>
        #unitDetailMap { z-index: 1; }

        /* ── Modern Table — Separated Rounded Rows (matching Maintenance page) ── */
        .modern-table-sep {
            border-collapse: separate;
            border-spacing: 0; /* No default vertical spacing so sub-rows merge seamlessly */
        }
        .modern-row {
            background-color: white;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05);
            transition: all 0.2s ease-in-out;
        }
        .modern-row:hover {
            box-shadow: 0 10px 15px -3px rgba(234, 179, 8, 0.18), 0 4px 6px -2px rgba(234, 179, 8, 0.08);
            transform: translateY(-1px);
            background-color: #fefdf6; /* Subtle high-end highlight */
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

        /* ── Unified Cards for Rows with Maintenance Sub-rows ── */
        .modern-row-has-sub {
            background-color: white;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05);
            transition: all 0.2s ease-in-out;
        }
        .modern-row-has-sub td {
            border-bottom: 1px solid rgba(243, 244, 246, 0.8); /* Elegant interior card divider */
        }
        .modern-row-has-sub td:first-child {
            border-top-left-radius: 0.75rem;
            border-left: 4px solid transparent;
        }
        .modern-row-has-sub td:last-child {
            border-top-right-radius: 0.75rem;
        }

        .modern-sub-row {
            background-color: white;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05);
            transition: all 0.2s ease-in-out;
        }
        .modern-sub-row td:first-child {
            border-bottom-left-radius: 0.75rem;
            border-left: 4px solid transparent;
        }
        .modern-sub-row td:last-child {
            border-bottom-right-radius: 0.75rem;
        }

        /* Cohesive synchronized hover for both rows of the single card */
        .modern-card-tbody:hover .modern-row-has-sub,
        .modern-card-tbody:hover .modern-sub-row {
            box-shadow: 0 10px 15px -3px rgba(234, 179, 8, 0.18), 0 4px 6px -2px rgba(234, 179, 8, 0.08);
            transform: translateY(-1px);
            background-color: #fefdf6; /* Matching high-end highlight */
        }
        .modern-card-tbody:hover .modern-row-has-sub td:first-child,
        .modern-card-tbody:hover .modern-sub-row td:first-child {
            border-left-color: #eab308; /* Cohesive continuous yellow border glow */
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

    <!-- Search and Filters -->
    <div class="bg-white px-4 lg:px-6 py-4 border-b border-gray-200">
        <form method="GET" action="{{ route('units.index') }}" class="flex flex-col lg:flex-row gap-2 items-center justify-between w-full">
            <!-- 2-column Grid for mobile, completely bypassed on desktop -->
            <div class="grid grid-cols-2 lg:contents gap-2 w-full">
                
                <!-- Search plate numbers: spans both columns on mobile, expands wide on desktop with min-width -->
                <div class="col-span-2 lg:flex-grow lg:min-w-[260px] order-1 lg:order-2">
                    <div class="relative group">
                        <input type="search" name="search" id="tableSearchInput" value="{{ $search }}"
                            class="block w-full pl-3 pr-10 py-2 lg:h-[38px] border border-gray-300 rounded-lg text-xs focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 focus:outline-none"
                            placeholder="Search plate or driver..." autocomplete="new-password" spellcheck="false" autocorrect="off" autocapitalize="off" readonly onfocus="this.removeAttribute('readonly');">
                        <button type="submit" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-yellow-600 transition-colors">
                            <i data-lucide="search" class="h-4 w-4"></i>
                        </button>
                    </div>
                </div>

                <!-- Sort A-Z: spans 1 column on mobile, w-full lg:w-36 on desktop -->
                <div class="col-span-1 lg:w-36 order-2 lg:order-1 flex-shrink-0">
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i data-lucide="arrow-up-z-a" class="h-4 w-4 text-gray-400"></i>
                        </div>
                        <select name="sort" onchange="this.form.submit()"
                            class="block w-full pl-9 pr-3 py-2 lg:h-[38px] border border-gray-300 rounded-lg text-xs focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 focus:outline-none appearance-none">
                            <option value="alphabetical" {{ ($sort ?? '') === 'alphabetical' ? 'selected' : '' }}>A-Z (Plate #)</option>
                            <option value="newest" {{ ($sort ?? '') === 'newest' ? 'selected' : '' }}>Newest Added</option>
                            <option value="oldest" {{ ($sort ?? '') === 'oldest' ? 'selected' : '' }}>Oldest Added</option>
                            <option value="vacant" {{ ($sort ?? '') === 'vacant' ? 'selected' : '' }}>Vacant Units First</option>
                        </select>
                    </div>
                </div>

                <!-- Status Filter: spans 1 column on mobile, w-full lg:w-36 on desktop -->
                <div class="col-span-1 lg:w-36 order-3 flex-shrink-0">
                    <select name="status" onchange="this.form.submit()"
                        class="block w-full px-3 py-2 lg:h-[38px] border border-gray-300 rounded-lg text-xs focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 focus:outline-none">
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

            </div>

            <!-- Action buttons container: uniform heights, single-row non-wrapping container on mobile, auto-width on desktop -->
            <div id="unitActionButtonsBar" class="flex gap-1.5 lg:gap-2 items-center flex-nowrap overflow-x-auto whitespace-nowrap scrollbar-none w-full lg:w-auto lg:flex-shrink-0 max-w-full pb-1 mt-2 lg:mt-0 order-4" style="-ms-overflow-style: none; scrollbar-width: none;">
                <style>
                    /* Hide scrollbar for Chrome, Safari and Opera */
                    #unitActionButtonsBar::-webkit-scrollbar {
                        display: none;
                    }
                </style>
                {{-- ── View Mode Toggle (Premium Labeled Pill) ─────── --}}
                <div class="flex items-stretch bg-gray-900/5 p-0.5 rounded-xl border border-gray-200/80 gap-0.5 shadow-inner h-[38px] flex-shrink-0">
                    <button type="button" onclick="setViewMode('table')" id="btn-view-table"
                        class="flex items-center justify-center gap-1.5 lg:gap-2 px-3 lg:px-4 rounded-lg text-xs font-black transition-all duration-200 uppercase tracking-wide whitespace-nowrap">
                        <i data-lucide="table-properties" class="w-3.5 h-3.5"></i>
                        <span class="hidden lg:inline">Table</span>
                    </button>
                    <button type="button" onclick="setViewMode('grid')" id="btn-view-grid"
                        class="flex items-center justify-center gap-1.5 lg:gap-2 px-3 lg:px-4 rounded-lg text-xs font-black transition-all duration-200 uppercase tracking-wide whitespace-nowrap">
                        <i data-lucide="layout-grid" class="w-3.5 h-3.5"></i>
                        <span class="hidden lg:inline">Cards</span>
                    </button>
                    <input type="hidden" name="view" id="viewModeInput" value="table">
                </div>

                <button type="button" onclick="printInHiddenIframe('{{ route('units.print') }}')"
                    class="px-3 py-1 bg-blue-600 text-white rounded-lg hover:bg-blue-700 flex items-center justify-center gap-1.5 lg:gap-2 text-xs font-semibold shadow-sm h-[38px] flex-1 min-w-0 lg:flex-initial lg:w-[135px]">
                    <i data-lucide="printer" class="w-3.5 h-3.5"></i> Print to PDF
                </button>
                <button type="button" onclick="document.getElementById('addUnitModal').classList.remove('hidden')"
                    class="px-3 py-1 bg-green-600 text-white rounded-lg hover:bg-green-700 flex items-center justify-center gap-1.5 lg:gap-2 text-xs font-semibold shadow-sm h-[38px] flex-1 min-w-0 lg:flex-initial lg:w-[135px]">
                    <i data-lucide="plus" class="w-3.5 h-3.5"></i> Add Unit
                </button>
            </div>
        </form>
    </div>

    <!-- ══════════════════════════════════════════════════════════════ -->
    <!-- Quick Stats Bar — updates in real-time with filters/search    -->
    <!-- ══════════════════════════════════════════════════════════════ -->
    <div id="quickStatsBar" class="bg-gradient-to-r from-gray-900 via-gray-800 to-gray-900 border-b border-gray-700/50 px-3 md:px-6 py-2.5 md:py-4 flex items-center justify-between gap-4 text-white min-h-[40px]">
        <!-- Stats pills -->
        <div class="flex items-center gap-1 md:gap-1.5 flex-nowrap overflow-x-auto whitespace-nowrap scrollbar-none max-w-full" style="-ms-overflow-style: none; scrollbar-width: none;">
            <style>
                /* Hide scrollbar for Chrome, Safari and Opera */
                #quickStatsBar::-webkit-scrollbar,
                #quickStatsBar .scrollbar-none::-webkit-scrollbar {
                    display: none;
                }
            </style>
            <!-- Filtered badge (hidden by default) -->
            <span id="qs-filter-badge" class="hidden mr-2 px-2 py-0.5 rounded-full text-[10px] font-black uppercase bg-yellow-400 text-gray-900 tracking-wide animate-pulse flex-shrink-0">
                ⚡ Filtered
            </span>

            <!-- Total -->
            <div class="flex items-center gap-2 px-2.5 md:px-3 py-1 rounded-full bg-white/10 hover:bg-white/20 transition-all cursor-default select-none flex-shrink-0">
                <div class="w-2 h-2 rounded-full bg-white animate-pulse"></div>
                <span class="text-[11px] font-bold text-gray-300">Total</span>
                <span id="qs-total" class="text-[13px] font-black text-white tabular-nums">{{ $stats['total'] ?? '—' }}</span>
            </div>

            <span class="text-gray-600 select-none flex-shrink-0">·</span>

            <!-- On Road -->
            <div class="flex items-center gap-2 px-2.5 md:px-3 py-1 rounded-full bg-green-500/20 hover:bg-green-500/30 transition-all cursor-default select-none flex-shrink-0">
                <div class="w-2 h-2 rounded-full bg-green-400 animate-pulse shadow-[0_0_6px_rgba(74,222,128,0.8)]"></div>
                <span class="text-[11px] font-bold text-green-300">Active</span>
                <span id="qs-onroad" class="text-[13px] font-black text-green-400 tabular-nums">{{ $stats['on_road'] ?? '—' }}</span>
            </div>

            <span class="text-gray-600 select-none flex-shrink-0">·</span>

            <!-- Workshop / Maintenance -->
            <div class="flex items-center gap-2 px-2.5 md:px-3 py-1 rounded-full bg-yellow-500/20 hover:bg-yellow-500/30 transition-all cursor-default select-none flex-shrink-0">
                <div class="w-2 h-2 rounded-full bg-yellow-400 animate-pulse shadow-[0_0_6px_rgba(250,204,21,0.8)]"></div>
                <span class="text-[11px] font-bold text-yellow-300">Maintenance</span>
                <span id="qs-workshop" class="text-[13px] font-black text-yellow-400 tabular-nums">{{ $stats['workshop'] ?? '—' }}</span>
            </div>

            <span class="text-gray-600 select-none flex-shrink-0">·</span>

            <!-- Coding -->
            <div class="flex items-center gap-2 px-2.5 md:px-3 py-1 rounded-full bg-red-500/20 hover:bg-red-500/30 transition-all cursor-default select-none flex-shrink-0">
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
    <div id="addUnitModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-3xl h-[90vh] flex flex-col overflow-hidden">

            {{-- Modal Header (Dark Slate/Navy, matching Add Driver Modal) --}}
            <div class="bg-slate-800 p-4 shrink-0">
                <div class="flex justify-between items-center">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-white bg-opacity-20 rounded-lg">
                            <i data-lucide="car" class="w-5 h-5 text-white"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-white leading-tight">Add New Unit</h3>
                            <p class="text-sm text-blue-100 leading-tight">Enter vehicle information and add devices</p>
                        </div>
                    </div>
                    <button type="button" onclick="document.getElementById('addUnitModal').classList.add('hidden'); resetAddUnitModal()" class="text-white hover:text-gray-200 transition-colors">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>
            </div>

            {{-- Form --}}
            <form method="POST" action="{{ route('units.store') }}" id="addUnitForm" class="flex flex-col flex-1 overflow-hidden">
                @csrf
                <div class="p-6 flex-1 overflow-y-auto space-y-8">

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

                {{-- Section 6: GPS Integration --}}
                <div class="mb-8">
                    <div class="flex items-center gap-2 mb-4">
                        <div class="p-2 bg-indigo-100 rounded-lg">
                            <i data-lucide="satellite" class="w-5 h-5 text-indigo-600"></i>
                        </div>
                        <h4 class="text-lg font-semibold text-gray-900">GPS Integration</h4>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">GPS Provider <span class="text-red-500">*</span></label>
                            <select name="gps_provider" id="addGpsProvider" onchange="toggleAddGpsPassword()"
                                class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                                <option value="tracksolid">Tracksolid Pro</option>
                                <option value="aksh">AKSH GPS (Aika168)</option>
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Device IMEI / Serial</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i data-lucide="hash" class="w-5 h-5 text-gray-400"></i>
                                </div>
                                <input type="text" name="imei" id="addImei"
                                    class="w-full pl-10 pr-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent font-mono"
                                    placeholder="e.g. 123456789012345 (10-15 digits)" minlength="10" maxlength="30">
                            </div>
                            <p class="text-xs text-gray-400 mt-1">Accepts 10–15 digit IMEI (AKSH GPS: 11 digits, Standard: 15 digits)</p>
                        </div>
                        <div class="space-y-2 hidden col-span-1 md:col-span-2 animate-in fade-in" id="addGpsPasswordContainer">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">GPS Device Password</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i data-lucide="key" class="w-5 h-5 text-gray-400"></i>
                                </div>
                                <input type="password" name="gps_password" id="addGpsPassword"
                                    class="w-full pl-10 pr-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                    placeholder="Default: 123456">
                            </div>
                            <p class="text-xs text-gray-500">Leave blank to use default password (123456).</p>
                        </div>
                    </div>
                </div>

                </div> {{-- End Scrollable Content --}}

                {{-- Fixed Footer --}}
                <div class="p-4 border-t flex justify-end items-center gap-3 shadow-inner bg-gray-50 shrink-0">
                    <button type="button" onclick="document.getElementById('addUnitModal').classList.add('hidden'); resetAddUnitModal()"
                        class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 text-sm font-bold transition-all">
                        Cancel
                    </button>
                    <button type="submit"
                        class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-bold shadow-lg shadow-blue-200/50 transition-all flex items-center gap-2">
                        <i data-lucide="save" class="w-4 h-4"></i> Save Unit
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Edit Unit Modal --}}
    <div id="editUnitModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-3xl h-[90vh] flex flex-col overflow-hidden">

            {{-- Modal Header (Dark Slate/Navy, matching Edit Driver Modal) --}}
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
                <div class="p-6 flex-1 overflow-y-auto space-y-8">

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

                {{-- Section 6: GPS Integration --}}
                <div class="mb-8">
                    <div class="flex items-center gap-2 mb-4">
                        <div class="p-2 bg-teal-100 rounded-lg">
                            <i data-lucide="satellite" class="w-5 h-5 text-teal-600"></i>
                        </div>
                        <h4 class="text-lg font-semibold text-gray-900">GPS Integration</h4>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">GPS Provider <span class="text-red-500">*</span></label>
                            <select name="gps_provider" id="editGpsProvider" onchange="toggleEditGpsPassword()"
                                class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent">
                                <option value="tracksolid">Tracksolid Pro</option>
                                <option value="aksh">AKSH GPS (Aika168)</option>
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Device IMEI / Serial</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i data-lucide="hash" class="w-5 h-5 text-gray-400"></i>
                                </div>
                                <input type="text" name="imei" id="editImei"
                                    class="w-full pl-10 pr-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent font-mono"
                                    placeholder="e.g. 123456789012345 (10-15 digits)" minlength="10" maxlength="30">
                            </div>
                            <p class="text-xs text-gray-400 mt-1">Accepts 10–15 digit IMEI (AKSH GPS: 11 digits, Standard: 15 digits)</p>
                        </div>
                        <div class="space-y-2 hidden col-span-1 md:col-span-2 animate-in fade-in" id="editGpsPasswordContainer">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">GPS Device Password</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i data-lucide="key" class="w-5 h-5 text-gray-400"></i>
                                </div>
                                <input type="password" name="gps_password" id="editGpsPassword"
                                    class="w-full pl-10 pr-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent"
                                    placeholder="Leave blank to use default/unchanged password">
                            </div>
                            <p class="text-xs text-gray-500">Leave blank if using the default password or not overriding.</p>
                        </div>
                    </div>
                </div>

                </div> {{-- End Scrollable Content --}}

                {{-- Fixed Footer --}}
                <div class="p-4 border-t flex justify-end items-center gap-3 shadow-inner bg-gray-50 shrink-0">
                    <button type="button" onclick="closeEditUnitModal()"
                        class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 text-sm font-bold transition-all">
                        Cancel
                    </button>
                    <button type="submit"
                        class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-bold shadow-lg shadow-blue-200/50 transition-all flex items-center gap-2">
                        <i data-lucide="save" class="w-4 h-4"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>

@include('units.partials._unit_details_shared')


<script>
    var currentViewMode = localStorage.getItem('unitViewMode') || 'table';
    
    function setViewMode(mode, forceFetch = true) {
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
        
        if (forceFetch) {
            performSearch(1); // Re-fetch with new view mode
        }
    }

    let searchTimer;
    const searchInput = document.querySelector('input[name="search"]');
    const statusFilter = document.querySelector('select[name="status"]');
    const sortFilter = document.querySelector('select[name="sort"]');
    const tableContainer = document.getElementById('unitsTableContainer');

    // ── Quick Stats ─────────────────────────────────────────────────
    var QUICK_STATS_URL = '{{ route("units.quick-stats") }}';

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

        const finalUrl = `{{ route('units.index') }}?search=${encodeURIComponent(query)}&status=${status}&sort=${sort}&page=${page}&view=${currentViewMode}`;
        
        // Dynamic state sync with global state engine
        if (typeof window.history.replaceState === 'function') {
            window.history.replaceState({}, '', finalUrl);
        }

        fetch(finalUrl, {
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


    window.toggleUnitDropdown = function(id, event) {
        event.stopPropagation();
        document.querySelectorAll('.unit-action-dropdown').forEach(el => {
            if (el.id !== id) el.classList.add('hidden');
            const row = el.closest('tr');
            if (row) { row.style.zIndex = ''; row.style.position = ''; }
        });
        const dropdown = document.getElementById(id);
        if (dropdown) {
            const isHidden = dropdown.classList.contains('hidden');
            const row = dropdown.closest('tr');
            if (isHidden) {
                dropdown.classList.remove('hidden');
                if (row) { row.style.position = 'relative'; row.style.zIndex = '50'; }
            } else {
                dropdown.classList.add('hidden');
                if (row) { row.style.zIndex = ''; row.style.position = ''; }
            }
        }
    };
    if (!window.unitDropdownListenerAdded) {
        document.addEventListener('click', function () {
            document.querySelectorAll('.unit-action-dropdown').forEach(el => {
                el.classList.add('hidden');
                const row = el.closest('tr');
                if (row) { row.style.zIndex = ''; row.style.position = ''; }
            });
        });
        window.unitDropdownListenerAdded = true;
    }
</script>
    <!-- Leaflet JS for Map -->
    <script src="{{ asset('assets/leaflet/leaflet.js') }}"></script>


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
                
                // GPS Provider and Password Mapping
                if (document.getElementById('editGpsProvider')) {
                    document.getElementById('editGpsProvider').value = unit.gps_provider || 'tracksolid';
                    toggleEditGpsPassword();
                }
                if (document.getElementById('editGpsPassword')) {
                    document.getElementById('editGpsPassword').value = unit.gps_password || '';
                }

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

    </script>

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

// Toggle GPS Password input fields
function toggleAddGpsPassword() {
    const provider = document.getElementById('addGpsProvider')?.value;
    const container = document.getElementById('addGpsPasswordContainer');
    if (container) {
        if (provider === 'aksh') {
            container.classList.remove('hidden');
        } else {
            container.classList.add('hidden');
        }
    }
}
function toggleEditGpsPassword() {
    const provider = document.getElementById('editGpsProvider')?.value;
    const container = document.getElementById('editGpsPasswordContainer');
    if (container) {
        if (provider === 'aksh') {
            container.classList.remove('hidden');
        } else {
            container.classList.add('hidden');
        }
    }
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
    toggleAddGpsPassword();
}

// Real-time table filtering
function initUnits() {
    if (typeof lucide !== 'undefined') lucide.createIcons();
    const serverView = '{{ $view_mode ?? "table" }}';
    if (typeof setViewMode === 'function') {
        setViewMode(currentViewMode, currentViewMode !== serverView);
    }
    
    const searchInput = document.getElementById('tableSearchInput');
    const tableBody = document.querySelector('tbody.bg-white.divide-y.divide-gray-200');
    
    if (searchInput && tableBody) {
        // Clear previous listeners if any (though difficult with anonymous functions, 
        // usually we'd use named functions but here we'll just be careful)
        searchInput.oninput = function(e) {
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
        };
    }
}

document.addEventListener('DOMContentLoaded', () => {
    initUnits();

    // Auto-open unit details if highlight ID is passed via URL
    const urlParams = new URLSearchParams(window.location.search);
    const highlightId = urlParams.get('highlight');
    if (highlightId && typeof viewUnitDetails === 'function') {
        setTimeout(() => {
            viewUnitDetails(highlightId);
            // Clean up the URL so it doesn't reopen on refresh
            const newUrl = window.location.protocol + "//" + window.location.host + window.location.pathname;
            window.history.replaceState({path:newUrl}, '', newUrl);
        }, 200);
    }
});
document.addEventListener('page:loaded', initUnits);
initUnits();
</script>

@endsection

