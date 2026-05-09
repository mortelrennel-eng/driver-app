@extends('layouts.app')

@section('title', 'Office Expenses - Euro System')
@section('page-heading', 'Office Expenses')
@section('page-subheading', 'Track and manage all operational and administrative expenses')

@section('content')

    {{-- Statistics Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-3 mb-8">
        {{-- Today --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 card-hover relative overflow-hidden group min-w-0">
            <div class="absolute top-0 right-0 w-16 h-16 bg-orange-50 rounded-full -mr-6 -mt-6 transition-transform group-hover:scale-110"></div>
            <div class="relative">
                <div class="flex items-center justify-between mb-2">
                    <p class="text-[9px] font-black text-gray-400 uppercase tracking-wider truncate">Total Today</p>
                    <i data-lucide="clock" class="h-4 w-4 text-orange-500"></i>
                </div>
                <h3 id="statToday" class="text-base sm:text-lg font-black text-orange-600 truncate tabular-nums">{{ formatCurrency($stats['today'] ?? 0) }}</h3>
                <div class="flex items-center gap-1 mt-1">
                    <span class="w-1 h-1 rounded-full bg-orange-400 animate-pulse"></span>
                    <p class="text-[9px] text-gray-400 font-bold uppercase transition-colors group-hover:text-orange-500">Live</p>
                </div>
            </div>
        </div>

        {{-- This Month --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 card-hover relative overflow-hidden group min-w-0">
            <div class="absolute top-0 right-0 w-16 h-16 bg-red-50 rounded-full -mr-6 -mt-6 transition-transform group-hover:scale-110"></div>
            <div class="relative">
                <div class="flex items-center justify-between mb-2">
                    <p class="text-[9px] font-black text-gray-400 uppercase tracking-wider truncate">This Month</p>
                    <i data-lucide="calendar-days" class="h-4 w-4 text-red-500"></i>
                </div>
                <h3 id="statThisMonth" class="text-base sm:text-lg font-black text-red-600 truncate tabular-nums">{{ formatCurrency($stats['this_month'] ?? 0) }}</h3>
                <p class="text-[9px] text-gray-400 font-bold uppercase mt-1 transition-colors group-hover:text-red-500">M-T-D</p>
            </div>
        </div>

        {{-- Last Month --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 card-hover relative overflow-hidden group min-w-0">
            <div class="absolute top-0 right-0 w-16 h-16 bg-gray-50 rounded-full -mr-6 -mt-6 transition-transform group-hover:scale-110"></div>
            <div class="relative">
                <div class="flex items-center justify-between mb-2">
                    <p class="text-[9px] font-black text-gray-400 uppercase tracking-wider truncate">Last Month</p>
                    <i data-lucide="history" class="h-4 w-4 text-gray-400"></i>
                </div>
                <h3 class="text-base sm:text-lg font-black text-gray-600 truncate tabular-nums">{{ formatCurrency($stats['last_month'] ?? 0) }}</h3>
                <p class="text-[9px] text-gray-400 font-bold uppercase mt-1">Previous</p>
            </div>
        </div>

        {{-- Monthly Change --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 card-hover relative overflow-hidden group min-w-0">
            @php $change = $stats['monthly_change'] ?? 0; @endphp
            <div class="absolute top-0 right-0 w-16 h-16 {{ $change < 0 ? 'bg-green-50' : 'bg-rose-50' }} rounded-full -mr-6 -mt-6 transition-transform group-hover:scale-110"></div>
            <div class="relative">
                <div class="flex items-center justify-between mb-2">
                    <p class="text-[9px] font-black text-gray-400 uppercase tracking-wider truncate">Trend</p>
                    <i data-lucide="{{ $change < 0 ? 'trending-down' : 'trending-up' }}" class="h-4 w-4 {{ $change < 0 ? 'text-green-500' : 'text-rose-500' }}"></i>
                </div>
                <h3 class="text-base sm:text-lg font-black {{ $change < 0 ? 'text-green-600' : 'text-rose-600' }} truncate">
                    {{ $change > 0 ? '+' : '' }}{{ $change }}%
                </h3>
                <p class="text-[9px] text-gray-400 font-bold uppercase mt-1">{{ $change < 0 ? 'Saved' : 'Added' }}</p>
            </div>
        </div>

        {{-- Total Records --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 card-hover relative overflow-hidden group min-w-0">
            <div class="absolute top-0 right-0 w-16 h-16 bg-yellow-50 rounded-full -mr-6 -mt-6 transition-transform group-hover:scale-110"></div>
            <div class="relative">
                <div class="flex items-center justify-between mb-2">
                    <p class="text-[9px] font-black text-gray-400 uppercase tracking-wider truncate">History</p>
                    <i data-lucide="layers" class="h-4 w-4 text-yellow-600"></i>
                </div>
                <h3 id="statTotalRecords" class="text-base sm:text-lg font-black text-yellow-600 truncate tabular-nums">{{ $stats['total_records'] ?? 0 }}</h3>
                <p class="text-[9px] text-gray-400 font-bold uppercase mt-1">Entries</p>
            </div>
        </div>
    </div>

    {{-- Category Breakdown --}}
    @if(isset($category_breakdown) && count($category_breakdown) > 0)
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Expenses by Category (This Month)</h3>
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
            @foreach($category_breakdown as $cat)
                <div class="text-center p-4 border rounded-lg">
                    <div class="text-lg font-bold text-gray-800">{{ formatCurrency($cat->total) }}</div>
                    <div class="text-xs text-gray-500 mt-1">{{ $cat->category }}</div>
                </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Search / Filter + Add Button --}}
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <form method="GET" action="{{ route('office-expenses.index') }}">
            <div class="flex flex-col lg:flex-row gap-4 items-center">
                <div class="flex-1 w-full">
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i data-lucide="search" class="h-5 w-5 text-gray-400"></i>
                        </div>
                        <input type="text" name="search" id="liveSearchInput" value="{{ $search ?? '' }}"
                            oninput="performLiveSearch()"
                            autocomplete="off"
                            class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 focus:outline-none h-[42px]"
                            placeholder="Search by description, category, or reference...">
                    </div>
                </div>

                <div class="flex gap-2 shrink-0">
                    <div class="w-36">
                        <input type="date" name="date_from" id="filterDateFrom" value="{{ $date_from }}" 
                            onchange="performLiveSearch()"
                            title="Start Date"
                            class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 focus:outline-none text-xs font-bold h-[42px]">
                    </div>
                    <div class="w-36">
                        <input type="date" name="date_to" id="filterDateTo" value="{{ $date_to }}" 
                            onchange="performLiveSearch()"
                            title="End Date"
                            class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 focus:outline-none text-xs font-bold h-[42px]">
                    </div>
                </div>

                <div class="lg:w-48">
                    <select name="category" id="filterCategory" onchange="performLiveSearch()" class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 focus:outline-none font-bold text-[10px] uppercase h-[42px]">
                        <option value="">All Categories</option>
                        <optgroup label="Utilities & Bills">
                            <option value="Electricity (Meralco)" {{ ($category ?? '') === 'Electricity (Meralco)' ? 'selected' : '' }}>Electricity (Meralco)</option>
                            <option value="Water (Maynilad)" {{ ($category ?? '') === 'Water (Maynilad)' ? 'selected' : '' }}>Water (Maynilad)</option>
                            <option value="Internet & WiFi" {{ ($category ?? '') === 'Internet & WiFi' ? 'selected' : '' }}>Internet & WiFi</option>
                            <option value="Communications" {{ ($category ?? '') === 'Communications' ? 'selected' : '' }}>Communications</option>
                        </optgroup>
                        <optgroup label="Materials & Supplies">
                            <option value="Office Supplies" {{ ($category ?? '') === 'Office Supplies' ? 'selected' : '' }}>Office Supplies</option>
                            <option value="Pantry & Cleaning" {{ ($category ?? '') === 'Pantry & Cleaning' ? 'selected' : '' }}>Pantry & Cleaning</option>
                        </optgroup>
                        <optgroup label="Facility & Infrastructure">
                            <option value="Building Repairs" {{ ($category ?? '') === 'Building Repairs' ? 'selected' : '' }}>Building Repairs</option>
                            <option value="Construction Materials" {{ ($category ?? '') === 'Construction Materials' ? 'selected' : '' }}>Construction Materials</option>
                            <option value="Office Equipment" {{ ($category ?? '') === 'Office Equipment' ? 'selected' : '' }}>Office Equipment</option>
                        </optgroup>
                        <optgroup label="Fleet Inventory & Parts">
                            <option value="Spare Parts Purchase" {{ ($category ?? '') === 'Spare Parts Purchase' ? 'selected' : '' }}>Spare Parts Purchase</option>
                        </optgroup>
                        <optgroup label="Admin & Fees">
                            <option value="Govt Permits & Fees" {{ ($category ?? '') === 'Govt Permits & Fees' ? 'selected' : '' }}>Govt Permits & Fees</option>
                            <option value="LTO & Registration" {{ ($category ?? '') === 'LTO & Registration' ? 'selected' : '' }}>LTO & Registration</option>
                            <option value="Insurance" {{ ($category ?? '') === 'Insurance' ? 'selected' : '' }}>Insurance</option>
                            <option value="Staff Meals & Incentives" {{ ($category ?? '') === 'Staff Meals & Incentives' ? 'selected' : '' }}>Staff Meals</option>
                        </optgroup>
                        <option value="Petty Cash" {{ ($category ?? '') === 'Petty Cash' ? 'selected' : '' }}>Petty Cash</option>
                        <option value="Other" {{ ($category ?? '') === 'Other' ? 'selected' : '' }}>Other</option>
                    </select>
                </div>

                <div class="flex gap-2">
                    <button type="button" onclick="openAddExpenseModal()" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 flex items-center gap-2">
                        <i data-lucide="plus" class="w-4 h-4"></i> Add Expense
                    </button>
                </div>
            </div>
        </form>
    </div>

    {{-- Expenses Table --}}
    <div id="expensesTableWrapper">
        @include('office-expenses.partials._expenses_table')
    </div>

    {{-- Add/Edit Expense Modal --}}
    <div id="expenseModal" class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm hidden h-full w-full z-50 flex items-center justify-center p-4 transition-all duration-300">
        <div class="relative bg-white rounded-2xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-hidden flex flex-col scale-95 transition-transform duration-300" id="modalContainer">
            <div class="flex justify-between items-center p-5 border-b bg-gray-50/50">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-yellow-500 rounded-xl text-white">
                        <i data-lucide="receipt" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-black text-gray-900 tracking-tight" id="expenseModalTitle">New Expense</h3>
                        <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest">Company Fund Management</p>
                    </div>
                </div>
                <button onclick="closeExpenseModal()" class="p-2 hover:bg-gray-100 rounded-full transition-colors">
                    <i data-lucide="x" class="w-5 h-5 text-gray-400"></i>
                </button>
            </div>

            <form id="expenseForm" method="POST" class="flex flex-col flex-1 min-h-0">
                @csrf
                <input type="hidden" name="_method" id="expenseFormMethod" value="POST">
                
                <div class="p-6 space-y-6 flex-1 overflow-y-auto custom-scrollbar">
                    {{-- Row 1: Date & Amount --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-5 items-end">
                        <div class="space-y-1.5">
                            <label class="text-[11px] font-black text-gray-400 uppercase tracking-widest ml-1">Expense Date *</label>
                            <div class="relative">
                                <i data-lucide="calendar" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"></i>
                                <input type="date" name="date" id="expenseDate" value="{{ date('Y-m-d') }}" required
                                    class="w-full pl-10 pr-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 focus:outline-none font-bold text-sm">
                            </div>
                        </div>

                        {{-- Hidden for Spare Parts Sync to avoid double entries --}}
                        <div class="space-y-1.5" id="topAmountGroup">
                            <label class="text-[11px] font-black text-gray-400 uppercase tracking-widest ml-1">Amount (₱) *</label>
                            <div class="relative">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 font-black text-gray-400 text-sm">₱</span>
                                <input type="number" name="amount" id="expenseAmount" step="0.01" min="0.01" max="10000000" required placeholder="0.00"
                                    onkeypress="if(event.key === 'e' || event.key === 'E' || event.key === '+' || event.key === '-') return false;"
                                    oninput="if(this.value > 10000000) this.value = 10000000; if(this.value.length > 10) this.value = this.value.slice(0, 10);"
                                    class="w-full pl-9 pr-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 focus:outline-none font-black text-sm text-red-600">
                            </div>
                        </div>
                    </div>

                    {{-- Row 2: Category --}}
                    <div class="space-y-1.5 relative">
                        <label class="text-[11px] font-black text-gray-400 uppercase tracking-widest ml-1">Expense Category *</label>
                        
                        {{-- Custom Dropdown Trigger --}}
                        <div class="relative" id="customSelectWrapper">
                            <button type="button" onclick="toggleCustomSelect()" id="customSelectTrigger"
                                class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 focus:outline-none font-bold text-sm flex items-center justify-between group transition-all">
                                <span id="selectedCategoryLabel">-- Choose Specific Category --</span>
                                <i data-lucide="chevron-down" id="customSelectArrow" class="w-4 h-4 text-gray-400 group-hover:text-yellow-600 transition-transform"></i>
                            </button>
                            <input type="hidden" name="category" id="expenseCategory" required>

                            {{-- Custom Dropdown Menu --}}
                            <div id="customSelectMenu" class="hidden absolute top-full left-0 right-0 mt-2 bg-white border border-gray-100 rounded-2xl shadow-2xl z-[100] max-h-[300px] overflow-y-auto animate-in fade-in zoom-in-95 duration-200 py-2">
                                <div class="px-3 pb-2 sticky top-0 bg-white border-b border-gray-50 mb-1 pt-1">
                                    <div class="relative">
                                        <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-3 h-3 text-gray-400"></i>
                                        <input type="text" id="catSearch" placeholder="Search categories..." onkeyup="filterCategories(this.value)"
                                            class="w-full pl-8 pr-3 py-1.5 bg-gray-50 border border-transparent rounded-lg focus:bg-white focus:border-yellow-200 focus:outline-none text-xs font-bold">
                                    </div>
                                </div>
                                <div id="catItemsContainer">
                                    <div class="px-4 py-1.5 text-[9px] font-black text-gray-400 uppercase tracking-widest bg-gray-50/50">Utilities & Bills</div>
                                    <div class="cat-item px-5 py-2.5 hover:bg-yellow-50 cursor-pointer text-sm font-bold text-gray-700 transition-colors flex items-center gap-2" onclick="selectCategory('Electricity (Meralco)')">
                                        <i data-lucide="zap" class="w-3 h-3 text-blue-500"></i> Electricity (Meralco)
                                    </div>
                                    <div class="cat-item px-5 py-2.5 hover:bg-yellow-50 cursor-pointer text-sm font-bold text-gray-700 transition-colors flex items-center gap-2" onclick="selectCategory('Water (Maynilad)')">
                                        <i data-lucide="droplets" class="w-3 h-3 text-cyan-500"></i> Water (Maynilad)
                                    </div>
                                    <div class="cat-item px-5 py-2.5 hover:bg-yellow-50 cursor-pointer text-sm font-bold text-gray-700 transition-colors flex items-center gap-2" onclick="selectCategory('Internet & WiFi')">
                                        <i data-lucide="wifi" class="w-3 h-3 text-indigo-500"></i> Internet & WiFi
                                    </div>
                                    <div class="cat-item px-5 py-2.5 hover:bg-yellow-50 cursor-pointer text-sm font-bold text-gray-700 transition-colors flex items-center gap-2" onclick="selectCategory('Communications')">
                                        <i data-lucide="phone" class="w-3 h-3 text-sky-500"></i> Communications
                                    </div>

                                    <div class="px-4 py-1.5 text-[9px] font-black text-gray-400 uppercase tracking-widest bg-gray-50/50 mt-1">Materials & Supplies</div>
                                    <div class="cat-item px-5 py-2.5 hover:bg-yellow-50 cursor-pointer text-sm font-bold text-gray-700 transition-colors flex items-center gap-2" onclick="selectCategory('Office Supplies')">
                                        <i data-lucide="file-text" class="w-3 h-3 text-purple-500"></i> Office Supplies (Paper, Ink)
                                    </div>
                                    <div class="cat-item px-5 py-2.5 hover:bg-yellow-50 cursor-pointer text-sm font-bold text-gray-700 transition-colors flex items-center gap-2" onclick="selectCategory('Pantry & Cleaning')">
                                        <i data-lucide="coffee" class="w-3 h-3 text-pink-500"></i> Pantry & Cleaning
                                    </div>

                                    <div class="px-4 py-1.5 text-[9px] font-black text-gray-400 uppercase tracking-widest bg-gray-50/50 mt-1">Facility & Infrastructure</div>
                                    <div class="cat-item px-5 py-2.5 hover:bg-yellow-50 cursor-pointer text-sm font-bold text-gray-700 transition-colors flex items-center gap-2" onclick="selectCategory('Building Repairs')">
                                        <i data-lucide="hammer" class="w-3 h-3 text-orange-500"></i> Building Repairs
                                    </div>
                                    <div class="cat-item px-5 py-2.5 hover:bg-yellow-50 cursor-pointer text-sm font-bold text-gray-700 transition-colors flex items-center gap-2" onclick="selectCategory('Construction Materials')">
                                        <i data-lucide="brick-wall" class="w-3 h-3 text-amber-500"></i> Construction Materials
                                    </div>
                                    <div class="cat-item px-5 py-2.5 hover:bg-yellow-50 cursor-pointer text-sm font-bold text-gray-700 transition-colors flex items-center gap-2" onclick="selectCategory('Office Equipment')">
                                        <i data-lucide="monitor" class="w-3 h-3 text-yellow-500"></i> Office Furniture & Eqpt
                                    </div>

                                    <div class="px-4 py-1.5 text-[9px] font-black text-amber-600 uppercase tracking-widest bg-amber-50/50 mt-1">Fleet Inventory & Parts</div>
                                    <div class="cat-item px-5 py-2.5 hover:bg-amber-50 cursor-pointer text-sm font-black text-amber-700 transition-colors flex items-center gap-2" onclick="selectCategory('Spare Parts Purchase')">
                                        <i data-lucide="package-search" class="w-3.5 h-3.5 text-amber-500"></i> Spare Parts Purchase
                                    </div>

                                    <div class="px-4 py-1.5 text-[9px] font-black text-gray-400 uppercase tracking-widest bg-gray-50/50 mt-1">Administrative & Govt</div>
                                    <div class="cat-item px-5 py-2.5 hover:bg-yellow-50 cursor-pointer text-sm font-bold text-gray-700 transition-colors flex items-center gap-2" onclick="selectCategory('Govt Permits & Fees')">
                                        <i data-lucide="landmark" class="w-3 h-3 text-teal-500"></i> Business Permits & Taxes
                                    </div>
                                    <div class="cat-item px-5 py-2.5 hover:bg-yellow-50 cursor-pointer text-sm font-bold text-gray-700 transition-colors flex items-center gap-2" onclick="selectCategory('LTO & Registration')">
                                        <i data-lucide="clipboard-list" class="w-3 h-3 text-emerald-500"></i> LTO Registration
                                    </div>
                                    <div class="cat-item px-5 py-2.5 hover:bg-yellow-50 cursor-pointer text-sm font-bold text-gray-700 transition-colors flex items-center gap-2" onclick="selectCategory('Insurance')">
                                        <i data-lucide="shield-check" class="w-3 h-3 text-blue-500"></i> Insurance (TPL/Comp)
                                    </div>
                                    <div class="cat-item px-5 py-2.5 hover:bg-emerald-50 cursor-pointer text-sm font-black text-emerald-700 transition-colors flex items-center gap-2" onclick="selectCategory('Franchise Renewal')">
                                        <i data-lucide="scroll-text" class="w-3.5 h-3.5 text-emerald-500"></i> Franchise Renewal
                                    </div>
                                    <div class="cat-item px-5 py-2.5 hover:bg-yellow-50 cursor-pointer text-sm font-bold text-gray-700 transition-colors flex items-center gap-2" onclick="selectCategory('Staff Meals & Incentives')">
                                        <i data-lucide="utensils" class="w-3 h-3 text-violet-500"></i> Staff Meals
                                    </div>

                                    <div class="px-4 py-1.5 text-[9px] font-black text-gray-400 uppercase tracking-widest bg-gray-50/50 mt-1">Misc</div>
                                    <div class="cat-item px-5 py-2.5 hover:bg-green-50 cursor-pointer text-sm font-bold text-emerald-700 transition-colors flex items-center gap-2" onclick="selectCategory('Petty Cash')">
                                        <i data-lucide="coins" class="w-3 h-3 text-green-500"></i> Petty Cash
                                    </div>
                                    <div id="otherCatItem" class="cat-item px-5 py-2.5 hover:bg-rose-50 cursor-pointer text-sm font-black text-rose-600 transition-colors flex items-center gap-2 border-t border-rose-50" onclick="selectCategory('Other')">
                                        <i data-lucide="plus-circle" class="w-3 h-3 text-rose-500"></i> -- OTHERS (CUSTOM) --
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Inventory Link Section (Visible only for Spare Parts Purchase) --}}
                        <div id="inventorySyncSection" class="hidden space-y-5 p-5 bg-amber-50/50 border border-amber-100 rounded-2xl animate-in fade-in slide-in-from-top-2 duration-300">
                            <div class="flex items-center gap-2 mb-1">
                                <div class="p-1.5 bg-amber-500 rounded-lg text-white">
                                    <i data-lucide="package" class="w-3.5 h-3.5"></i>
                                </div>
                                <h4 class="text-[10px] font-black text-amber-600 uppercase tracking-widest">Inventory Stock Sync</h4>
                            </div>
                            
                            <div class="space-y-4">
                                {{-- Dropdown vs New Part Toggle --}}
                                <div class="flex items-center justify-between px-1">
                                    <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Select Item or Register New</label>
                                    <button type="button" onclick="toggleNewPartRegistration()" id="regNewPartBtn"
                                        class="text-[9px] font-black text-rose-600 bg-rose-50 px-2 py-1 rounded-lg hover:bg-rose-100 transition-all border border-rose-100">
                                        + REGISTER NEW ITEM
                                    </button>
                                </div>

                                <div id="existingPartGroup" class="space-y-1.5 animate-in fade-in duration-300">
                                    <div class="relative" id="partSelectWrapper">
                                        <button type="button" onclick="togglePartSelect()" 
                                            class="w-full px-4 py-3 bg-white border border-amber-200 rounded-xl focus:ring-2 focus:ring-amber-500 focus:outline-none flex items-center justify-between transition-all group">
                                            <span id="selectedPartLabel" class="text-sm font-bold text-gray-400">-- Select Existing Part to Restock --</span>
                                            <i data-lucide="chevron-down" id="partSelectArrow" class="w-4 h-4 text-gray-400 transition-transform duration-300"></i>
                                        </button>

                                        <div id="partSelectMenu" class="hidden absolute left-0 right-0 top-full mt-2 bg-white border border-amber-100 rounded-2xl shadow-2xl z-[100] animate-in fade-in slide-in-from-top-2 duration-300 overflow-hidden">
                                            <div class="p-3 border-b border-amber-50 bg-amber-50/30">
                                                <div class="relative">
                                                    <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-amber-400"></i>
                                                    <input type="text" id="partSearch" oninput="filterParts(this.value)" placeholder="Search part..." 
                                                        class="w-full pl-9 pr-4 py-2 bg-white border border-amber-100 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-amber-500 font-bold">
                                                </div>
                                            </div>
                                            <div class="max-h-60 overflow-y-auto custom-scrollbar p-2 space-y-1" id="partListContainer">
                                                @foreach($spareParts as $part)
                                                    <button type="button" 
                                                        onclick="selectPart('{{ $part->id }}', '{{ addslashes($part->name) }}', '{{ $part->price }}', '{{ addslashes($part->supplier) }}', '{{ $part->stock_quantity }}')" 
                                                        class="part-item w-full px-4 py-2.5 text-left text-sm font-bold text-gray-600 hover:bg-amber-50 hover:text-amber-600 rounded-xl transition-all flex items-center justify-between">
                                                        <span class="truncate">{{ $part->name }}</span>
                                                        <span class="text-[10px] bg-amber-100 px-1.5 py-0.5 rounded ml-2 whitespace-nowrap">Stock: {{ $part->stock_quantity }}</span>
                                                    </button>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                    <input type="hidden" name="spare_part_id" id="expenseSparePartId">
                                </div>

                                {{-- Register New Part Field (Expanded View) --}}
                                <div id="newPartGroup" class="hidden space-y-4 bg-rose-50/50 p-4 rounded-xl border border-rose-100 animate-in slide-in-from-top-2 duration-300">
                                    <div class="flex items-center justify-between mb-1">
                                        <div class="flex items-center gap-2">
                                            <i data-lucide="sparkles" class="w-4 h-4 text-rose-500"></i>
                                            <span class="text-[10px] font-black text-rose-600 uppercase tracking-widest">New Inventory Registration</span>
                                        </div>
                                        <button type="submit" class="bg-rose-600 text-white px-3 py-1 rounded-lg text-[8px] font-black uppercase tracking-widest hover:bg-rose-700 shadow-md transition-all active:scale-95 flex items-center gap-1">
                                            <i data-lucide="save" class="w-2.5 h-2.5"></i> Save & Sync
                                        </button>
                                    </div>
                                    
                                    <div class="space-y-1.5">
                                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Part Name *</label>
                                        <input type="text" name="new_part_name" id="expenseNewPartName" placeholder="e.g. Clutch Disc (Genuine), Brake Pads, etc."
                                            maxlength="30"
                                            onkeypress="if(event.key === ' ' && this.value.trim() === '') return false;"
                                            oninput="this.value = this.value.trimStart();"
                                            class="w-full px-4 py-2.5 bg-white border border-rose-200 rounded-xl focus:ring-2 focus:ring-rose-500 focus:outline-none font-black text-sm text-rose-700">
                                    </div>
                                    <p class="text-[9px] text-rose-500 font-bold uppercase ml-1">This will be added to your inventory list</p>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div class="space-y-1.5">
                                    <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Quantity *</label>
                                    <input type="number" name="quantity" id="expenseQuantity" min="1" max="9999" placeholder="0"
                                        onkeypress="if(event.key==='e'||event.key==='E'||event.key==='+'||event.key==='-'||event.key==='.') return false;"
                                        oninput="this.value=this.value.replace(/[^0-9]/g,''); if(parseInt(this.value)>9999)this.value=9999; if(this.value.length>4)this.value=this.value.slice(0,4); calcInventoryTotal();"
                                        class="w-full px-4 py-2.5 bg-white border border-amber-200 rounded-xl focus:ring-2 focus:ring-amber-500 focus:outline-none font-black text-sm">
                                </div>
                                <div class="space-y-1.5">
                                    <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1 flex items-center justify-between">
                                        <span>Unit Price *</span>
                                        <span id="priceEditBadge" class="hidden text-[8px] bg-rose-500 text-white px-1.5 py-0.5 rounded animate-pulse">MODIFIED</span>
                                    </label>
                                    <input type="number" name="unit_price" id="expenseUnitPrice" step="0.01" min="0.01" max="500000" placeholder="0.00"
                                        onkeypress="if(event.key==='e'||event.key==='E'||event.key==='+'||event.key==='-') return false;"
                                        oninput="if(parseFloat(this.value)>500000)this.value=500000; if(this.value.length>9)this.value=this.value.slice(0,9); calcInventoryTotal(); markAsModified();"
                                        class="w-full px-4 py-2.5 bg-white border border-amber-200 rounded-xl focus:ring-2 focus:ring-amber-500 focus:outline-none font-black text-sm">
                                </div>
                            </div>

                            {{-- Consolidated Supplier Dropdown --}}
                            <div id="supplierDisplayGroup" class="space-y-1.5 pt-1">
                                <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1 flex items-center justify-between">
                                    <span class="flex items-center gap-1">
                                        <i data-lucide="building-2" class="w-3 h-3 text-amber-500"></i> Supplier / Store Name *
                                    </span>
                                    <div class="flex items-center gap-2">
                                        <span id="editModeBadge" class="hidden text-[8px] bg-amber-500 text-white px-1.5 py-0.5 rounded animate-pulse">MODIFIED</span>
                                        <button type="button" onclick="openSupplierManager()" class="text-[9px] font-black text-blue-600 hover:text-blue-800 uppercase tracking-widest bg-blue-50 px-2 py-0.5 rounded transition-all active:scale-95">
                                            Manage Suppliers
                                        </button>
                                    </div>
                                </label>
                                
                                <div class="relative" id="supplierSelectWrapper">
                                    <button type="button" onclick="toggleSupplierSelect()" 
                                        class="w-full px-4 py-3 bg-white border border-amber-200 rounded-xl focus:ring-2 focus:ring-amber-500 focus:outline-none flex items-center justify-between transition-all group">
                                        <span id="selectedSupplierLabel" class="text-sm font-bold text-gray-400">-- Choose or Type Supplier --</span>
                                        <i data-lucide="chevron-down" id="supplierSelectArrow" class="w-4 h-4 text-gray-400 transition-transform duration-300"></i>
                                    </button>

                                    <div id="supplierSelectMenu" class="hidden absolute left-0 right-0 top-full mt-2 bg-white border border-amber-100 rounded-2xl shadow-2xl z-[100] animate-in fade-in slide-in-from-top-2 duration-300 overflow-hidden">
                                        <div class="p-3 border-b border-amber-50 bg-amber-50/30">
                                            <div class="relative">
                                                <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-amber-400"></i>
                                                <input type="text" id="supSearch" oninput="filterSuppliers(this.value)" placeholder="Search supplier..." 
                                                    class="w-full pl-9 pr-4 py-2 bg-white border border-amber-100 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-amber-500 font-bold">
                                            </div>
                                        </div>
                                        <div class="max-h-48 overflow-y-auto custom-scrollbar p-2 space-y-1" id="supplierListContainer">
                                            @foreach($suppliers as $s)
                                                <button type="button" onclick="selectSupplier('{{ $s->name }}')" 
                                                    class="sup-item w-full px-4 py-2.5 text-left text-sm font-bold text-gray-600 hover:bg-amber-50 hover:text-amber-600 rounded-xl transition-all flex items-center gap-2">
                                                    <i data-lucide="building" class="w-3.5 h-3.5 opacity-40"></i>
                                                    {{ $s->name }}
                                                </button>
                                            @endforeach
                                            <button type="button" onclick="selectSupplier('new')" 
                                                class="sup-item w-full px-4 py-2.5 text-left text-sm font-black text-rose-600 bg-rose-50 hover:bg-rose-100 rounded-xl transition-all flex items-center gap-2">
                                                <i data-lucide="plus-circle" class="w-4 h-4 text-rose-600"></i>
                                                ++ ADD / TYPE NEW SUPPLIER ++
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <div id="newSupplierField" class="hidden space-y-1.5 animate-in slide-in-from-top-2 duration-300 pt-2">
                                    <div class="relative">
                                        <i data-lucide="edit-3" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-rose-500"></i>
                                        <input type="text" id="expenseNewSupplier" placeholder="Type new supplier name here..."
                                            oninput="updateSyncSupplierValue(this.value)"
                                            class="w-full pl-10 pr-4 py-2.5 bg-rose-50 border border-rose-300 rounded-xl focus:ring-2 focus:ring-rose-500 focus:outline-none font-black text-sm text-rose-700">
                                    </div>
                                </div>
                                
                                <input type="hidden" name="vendor_name" id="syncSupplierHidden">
                                <input type="hidden" name="update_master" id="updateMasterHidden" value="0">
                            </div>
                            
                            <p class="text-[9px] text-amber-600 font-bold uppercase ml-1 flex items-center gap-1">
                                <i data-lucide="info" class="w-3 h-3"></i> Adding this expense will automatically increase inventory stock.
                            </p>
                        </div>

                        {{-- Franchise Renewal Sync Section --}}
                        <div id="franchiseSyncSection" class="hidden space-y-5 p-5 bg-emerald-50/50 border border-emerald-100 rounded-2xl animate-in fade-in slide-in-from-top-2 duration-300">
                            <div class="flex items-center gap-2 mb-1">
                                <div class="p-1.5 bg-emerald-500 rounded-lg text-white">
                                    <i data-lucide="scroll-text" class="w-3.5 h-3.5"></i>
                                </div>
                                <h4 class="text-[10px] font-black text-emerald-600 uppercase tracking-widest">Franchise Renewal Sync</h4>
                            </div>
                            
                            <div class="space-y-4">
                                <div class="space-y-1.5">
                                    <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Select Franchise Case *</label>
                                    <div class="relative" id="franchiseSelectWrapper">
                                        <button type="button" onclick="toggleFranchiseSelect()" id="franchiseSelectTrigger"
                                            class="w-full px-4 py-3 bg-white border border-emerald-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:outline-none flex items-center justify-between transition-all group">
                                            <span id="selectedFranchiseLabel" class="text-sm font-bold text-gray-400">-- Choose Franchise Case --</span>
                                            <i data-lucide="chevron-down" id="franchiseSelectArrow" class="w-4 h-4 text-emerald-400 transition-transform duration-300"></i>
                                        </button>
                                        <input type="hidden" name="franchise_case_id" id="expenseFranchiseCaseId">

                                        {{-- Custom Franchise Dropdown --}}
                                        <div id="franchiseSelectMenu" class="hidden absolute left-0 right-0 top-full mt-2 bg-white border border-emerald-100 rounded-2xl shadow-2xl z-[110] animate-in fade-in slide-in-from-top-2 duration-300 overflow-hidden">
                                            <div class="p-3 border-b border-emerald-50 bg-emerald-50/30">
                                                <div class="relative">
                                                    <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-emerald-400"></i>
                                                    <input type="text" id="franSearch" oninput="filterFranchises(this.value)" placeholder="Search case no or name..." 
                                                        class="w-full pl-9 pr-4 py-2 bg-white border border-emerald-100 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-emerald-500 font-bold">
                                                </div>
                                            </div>
                                            <div class="max-h-48 overflow-y-auto custom-scrollbar p-2 space-y-1" id="franListContainer">
                                                @foreach($franchises as $f)
                                                    <button type="button" onclick="selectFranchise('{{ $f->id }}', '{{ $f->case_no }}', '{{ addslashes($f->applicant_name) }}')" 
                                                        class="fran-item w-full px-4 py-2.5 text-left text-sm font-bold text-gray-600 hover:bg-emerald-50 hover:text-emerald-600 rounded-xl transition-all flex items-center justify-between">
                                                        <div class="flex flex-col">
                                                            <span class="font-black">Case #{{ $f->case_no }}</span>
                                                            <span class="text-[10px] opacity-60">{{ $f->applicant_name }}</span>
                                                        </div>
                                                        @if($f->expiry_date)
                                                            <span class="text-[9px] bg-white px-1.5 py-0.5 rounded border border-emerald-100">Expires: {{ $f->expiry_date->format('M Y') }}</span>
                                                        @endif
                                                    </button>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="space-y-1.5">
                                    <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">New Expiry Date *</label>
                                    <div class="relative">
                                        <i data-lucide="calendar" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-emerald-400"></i>
                                        <input type="date" name="new_expiry_date" id="expenseNewExpiryDate"
                                            class="w-full pl-10 pr-4 py-2.5 bg-white border border-emerald-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:outline-none font-bold text-sm">
                                    </div>
                                    <p class="text-[9px] text-emerald-600 font-bold uppercase ml-1 mt-1">This will update the expiry date in the Franchise module.</p>
                                </div>
                            </div>
                        </div>

                    {{-- Custom Category Input (Hidden by default) --}}
                    <div id="customCategoryGroup" class="space-y-1.5 hidden animate-in slide-in-from-top-2 duration-300">
                        <label class="text-[11px] font-black text-rose-600 uppercase tracking-widest ml-1 flex items-center gap-1">
                            <i data-lucide="plus-circle" class="w-3 h-3"></i> Specify Custom Category *
                        </label>
                        <input type="text" name="custom_category" id="expenseCustomCategory" placeholder="e.g. Franchise Fees, Legal Support, etc."
                            class="w-full px-4 py-2.5 bg-rose-50 border border-rose-100 rounded-xl focus:ring-2 focus:ring-rose-500 focus:border-rose-500 focus:outline-none font-bold text-sm">
                        <p class="text-[9px] text-gray-400 font-bold uppercase ml-1">This will be used as the new category name</p>
                    </div>

                    {{-- Row 3: Description --}}
                    <div class="space-y-1.5">
                        <label class="text-[11px] font-black text-gray-400 uppercase tracking-widest ml-1">Description / Details *</label>
                        <textarea name="description" id="expenseDescription" required rows="3" maxlength="250"
                            class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 focus:outline-none font-medium text-sm"
                            placeholder="Example: WiFi Bill for April 2024, or 5 Reams of A4 Bond Paper..."></textarea>
                    </div>

                    {{-- Supplier Selection (Hidden for Spare Parts Sync to avoid double input) --}}
                    <div id="standardVendorOnly" class="space-y-1.5 mb-5">
                        <label class="text-[11px] font-black text-gray-400 uppercase tracking-widest ml-1">Vendor / Store Name</label>
                        <div class="relative">
                            <i data-lucide="building" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"></i>
                            <input type="text" name="vendor_name" id="expenseVendor" placeholder="e.g. Meralco, Pandayan, PLDT" maxlength="30"
                                class="w-full pl-10 pr-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 focus:outline-none font-bold text-sm">
                        </div>
                    </div>

                    {{-- Reference No (Always Visible) --}}
                    <div class="space-y-1.5 mb-5">
                        <label class="text-[11px] font-black text-gray-400 uppercase tracking-widest ml-1 flex items-center gap-1">
                            <i data-lucide="hash" class="w-3.5 h-3.5 text-yellow-500"></i> Reference No. (Invoice)
                        </label>
                        <div class="relative">
                            <i data-lucide="tag" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-300"></i>
                            <input type="text" name="reference_number" id="expenseReference" placeholder="e.g. SINV-12345, OR-9876, etc." maxlength="30"
                                class="w-full pl-10 pr-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 focus:outline-none font-black text-sm uppercase">
                        </div>
                    </div>

                    {{-- Row 5: Payment Method --}}
                    <div class="space-y-1.5">
                        <label class="text-[11px] font-black text-gray-400 uppercase tracking-widest ml-1">Payment Method</label>
                        <div class="grid grid-cols-3 gap-3">
                            <label class="relative cursor-pointer">
                                <input type="radio" name="payment_method" value="Cash" class="peer sr-only" checked id="pmCash">
                                <div class="p-3 text-center border-2 border-gray-100 rounded-xl peer-checked:border-yellow-500 peer-checked:bg-yellow-50 transition-all hover:bg-gray-50">
                                    <i data-lucide="banknote" class="w-4 h-4 mx-auto mb-1 text-gray-400 peer-checked:text-yellow-600"></i>
                                    <span class="text-[10px] font-black uppercase text-gray-500">Cash</span>
                                </div>
                            </label>
                            <label class="relative cursor-pointer">
                                <input type="radio" name="payment_method" value="Check" class="peer sr-only" id="pmCheck">
                                <div class="p-3 text-center border-2 border-gray-100 rounded-xl peer-checked:border-yellow-500 peer-checked:bg-yellow-50 transition-all hover:bg-gray-50">
                                    <i data-lucide="wallet" class="w-4 h-4 mx-auto mb-1 text-gray-400 peer-checked:text-yellow-600"></i>
                                    <span class="text-[10px] font-black uppercase text-gray-500">Check</span>
                                </div>
                            </label>
                            <label class="relative cursor-pointer">
                                <input type="radio" name="payment_method" value="Transfer" class="peer sr-only" id="pmTransfer">
                                <div class="p-3 text-center border-2 border-gray-100 rounded-xl peer-checked:border-yellow-500 peer-checked:bg-yellow-50 transition-all hover:bg-gray-50">
                                    <i data-lucide="cpu" class="w-4 h-4 mx-auto mb-1 text-gray-400 peer-checked:text-yellow-600"></i>
                                    <span class="text-[10px] font-black uppercase text-gray-500">Transfer</span>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>

                {{-- Form Footer --}}
                <div class="p-6 bg-gray-50 border-t flex gap-4 mt-auto shrink-0">
                    <button type="submit" class="flex-1 bg-yellow-600 text-white font-black uppercase tracking-widest text-xs py-4 rounded-2xl hover:bg-yellow-700 shadow-lg shadow-yellow-200 transition-all active:scale-95">Save Expense</button>
                    <button type="button" onclick="closeExpenseModal()" class="flex-1 bg-white border border-gray-200 text-gray-500 font-black uppercase tracking-widest text-xs py-4 rounded-2xl hover:bg-gray-50 transition-all">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Description Modal --}}
    <div id="descModal" class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm hidden h-full w-full z-50 flex items-center justify-center p-4">
        <div class="relative bg-white rounded-2xl shadow-xl max-w-md w-full">
            <div class="flex justify-between items-center p-6 border-b">
                <h3 class="text-lg font-black text-gray-900 uppercase">Expense Details</h3>
                <button onclick="document.getElementById('descModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
            <div class="p-6">
                <p id="fullDescText" class="text-gray-700 font-medium leading-relaxed"></p>
            </div>
        </div>
    </div>

    {{-- Supplier Manager Modal --}}
    <div id="supplierManagerModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm hidden h-full w-full z-[60] flex items-center justify-center p-4">
        <div class="relative bg-white rounded-2xl shadow-2xl max-w-2xl w-full max-h-[85vh] flex flex-col overflow-hidden animate-in fade-in zoom-in-95 duration-300">
            <div class="bg-slate-800 p-4 shrink-0 flex justify-between items-center">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-yellow-500/20 rounded-lg">
                        <i data-lucide="truck" class="w-5 h-5 text-yellow-500"></i>
                    </div>
                    <div>
                        <h4 class="text-lg font-black text-white">Manage Suppliers</h4>
                        <p class="text-[10px] text-slate-400 uppercase font-bold tracking-widest">Master Vendor Catalog</p>
                    </div>
                </div>
                <button onclick="closeSupplierManager()" class="text-slate-400 hover:text-white">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <div class="p-6 overflow-y-auto flex-1 custom-scrollbar">
                {{-- Add/Edit Supplier Form --}}
                <form id="supplierForm" onsubmit="saveSupplier(event)" class="bg-gray-50 p-4 rounded-xl border border-gray-200 mb-6">
                    <input type="hidden" id="supplierId">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <label class="text-[10px] font-black text-gray-400 uppercase ml-1">Supplier Name *</label>
                            <input type="text" id="supName" required 
                                maxlength="20" pattern="^(?=.*[a-zA-Z])[a-zA-Z0-9\s.,&'-]+$" title="Must contain at least one letter. Max 20 characters."
                                oninput="this.value = this.value.replace(/^\s+/, '').replace(/\s{2,}/g, ' ').slice(0, 20)"
                                class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-yellow-500 focus:outline-none font-bold">
                        </div>
                        <div class="space-y-1">
                            <label class="text-[10px] font-black text-gray-400 uppercase ml-1">Contact Person</label>
                            <input type="text" id="supContact" 
                                maxlength="20" pattern="^(?!\s+$)[a-zA-Z\s]*$" title="Letters only. Max 20 characters."
                                oninput="this.value = this.value.replace(/[^a-zA-Z\s]/g, '').replace(/^\s+/, '').replace(/\s{2,}/g, ' ').slice(0, 20)"
                                class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-yellow-500 focus:outline-none">
                        </div>
                        <div class="space-y-1">
                            <label class="text-[10px] font-black text-gray-400 uppercase ml-1">Phone Number</label>
                            <input type="tel" id="supPhone" 
                                maxlength="11" pattern="^(?!0+$)[0-9]+$" title="Up to 11 digits. Cannot be all zeros."
                                oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 11)"
                                class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-yellow-500 focus:outline-none">
                        </div>
                        <div class="space-y-1">
                            <label class="text-[10px] font-black text-gray-400 uppercase ml-1">Address</label>
                            <input type="text" id="supAddress" 
                                maxlength="50" pattern="^(?!\s+$).+$" title="Max 50 characters. Cannot be only spaces."
                                oninput="this.value = this.value.replace(/^\s+/, '').replace(/\s{2,}/g, ' ').slice(0, 50)"
                                class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-yellow-500 focus:outline-none">
                        </div>
                    </div>
                    <div class="mt-4 flex justify-end gap-2">
                        <button type="button" id="cancelSupBtn" onclick="resetSupplierForm()" class="hidden px-4 py-1.5 text-xs font-bold text-gray-500 hover:text-gray-700">Cancel Edit</button>
                        <button type="submit" id="saveSupBtn" class="px-6 py-1.5 bg-yellow-500 text-slate-900 rounded-lg text-xs font-black uppercase hover:bg-yellow-600 transition-colors">Add Supplier</button>
                    </div>
                </form>

                {{-- Supplier List --}}
                <div class="space-y-2" id="supplierListContainerFull">
                    @foreach($suppliers as $s)
                        <div class="flex items-center justify-between p-3 bg-white border border-gray-100 rounded-xl hover:shadow-md transition-shadow group">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center text-slate-400 font-bold">
                                    {{ substr($s->name, 0, 1) }}
                                </div>
                                <div>
                                    <h5 class="text-sm font-black text-slate-700">{{ $s->name }}</h5>
                                    <p class="text-[10px] text-slate-400 font-medium">{{ $s->contact_person ?: 'No contact' }} • {{ $s->phone_number ?: 'No phone' }}</p>
                                </div>
                            </div>
                            <div class="flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                <button onclick="editSupplier({{ json_encode($s) }})" class="p-2 text-blue-500 hover:bg-blue-50 rounded-lg">
                                    <i data-lucide="edit-2" class="w-4 h-4"></i>
                                </button>
                                <button onclick="deleteSupplier({{ $s->id }})" class="p-2 text-rose-500 hover:bg-rose-50 rounded-lg">
                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
const partsCatalog = @json($spareParts);

// Helper to safely set values/classes
function safeSet(id, prop, val, isClass = false) {
    const el = document.getElementById(id);
    if (el) {
        if (isClass) {
            if (val === 'add') el.classList.add(prop);
            else if (val === 'remove') el.classList.remove(prop);
        } else {
            el[prop] = val;
        }
    }
}

function openAddExpenseModal() {
    safeSet('expenseModalTitle', 'textContent', 'New Expense');
    safeSet('expenseFormMethod', 'value', 'POST');
    safeSet('expenseForm', 'action', '{{ route('office-expenses.store') }}');
    safeSet('expenseDate', 'value', '{{ date('Y-m-d') }}');
    safeSet('expenseCategory', 'value', '');
    safeSet('expenseSparePartId', 'value', '');
    safeSet('expenseQuantity', 'value', '');
    safeSet('expenseUnitPrice', 'value', '');
    
    // Reset Sections
    safeSet('inventorySyncSection', 'hidden', 'add', true);
    safeSet('franchiseSyncSection', 'hidden', 'add', true);
    safeSet('topAmountGroup', 'hidden', 'remove', true);
    safeSet('standardVendorOnly', 'hidden', 'remove', true);
    safeSet('customCategoryGroup', 'hidden', 'add', true);
    
    safeSet('editModeBadge', 'hidden', 'add', true);
    safeSet('priceEditBadge', 'hidden', 'add', true);
    safeSet('updateMasterHidden', 'value', '0');
    safeSet('existingPartGroup', 'hidden', 'remove', true);
    safeSet('newPartGroup', 'hidden', 'add', true);
    safeSet('supplierDisplayGroup', 'hidden', 'remove', true);
    
    const regBtn = document.getElementById('regNewPartBtn');
    if (regBtn) {
        regBtn.textContent = '+ REGISTER NEW ITEM';
        regBtn.classList.replace('bg-gray-100', 'bg-rose-50');
        regBtn.classList.replace('text-gray-600', 'text-rose-600');
    }
    
    safeSet('syncSupplierHidden', 'value', '');
    safeSet('selectedSupplierLabel', 'textContent', '-- Choose or Type Supplier --');
    safeSet('selectedSupplierLabel', 'text-gray-900', 'remove', true);
    safeSet('selectedSupplierLabel', 'text-gray-400', 'add', true);
    
    safeSet('selectedPartLabel', 'textContent', '-- Select Existing Part to Restock --');
    safeSet('selectedPartLabel', 'text-gray-900', 'remove', true);
    safeSet('selectedPartLabel', 'text-gray-400', 'add', true);

    safeSet('selectedFranchiseLabel', 'textContent', '-- Choose Franchise Case --');
    safeSet('selectedFranchiseLabel', 'text-gray-900', 'remove', true);
    safeSet('selectedFranchiseLabel', 'text-gray-400', 'add', true);
    safeSet('expenseFranchiseCaseId', 'value', '');
    safeSet('expenseNewExpiryDate', 'value', '');
    
    safeSet('expenseReference', 'value', '');
    safeSet('newSupplierField', 'hidden', 'add', true);
    safeSet('supplierSelectMenu', 'hidden', 'add', true);
    safeSet('supplierSelectArrow', 'rotate-180', 'remove', true);
    
    safeSet('expenseNewPartName', 'value', '');
    safeSet('expenseAmount', 'value', '');
    safeSet('expenseAmount', 'readOnly', false);
    safeSet('expenseAmount', 'bg-gray-100', 'remove', true);
    
    safeSet('selectedCategoryLabel', 'textContent', '-- Choose Specific Category --');
    safeSet('selectedCategoryLabel', 'text-gray-900', 'remove', true);
    safeSet('selectedCategoryLabel', 'text-gray-400', 'add', true);
    
    safeSet('expenseDescription', 'value', '');
    safeSet('expenseVendor', 'value', '');
    
    const pmCash = document.getElementById('pmCash');
    if (pmCash) pmCash.checked = true;
    
    const modal = document.getElementById('expenseModal');
    if (modal) {
        modal.classList.remove('hidden');
        setTimeout(() => {
            const container = document.getElementById('modalContainer');
            if (container) container.classList.remove('scale-95');
        }, 10);
    }
    if (window.lucide) lucide.createIcons();
}
 
function openEditExpenseModal(id) {
    fetch('{{ url('office-expenses') }}/' + id + '?format=json', {
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(data => {
        document.getElementById('expenseModalTitle').textContent = 'Edit Expense Details';
        document.getElementById('expenseFormMethod').value = 'PUT';
        document.getElementById('expenseForm').action = '{{ url('office-expenses') }}/' + id;
        document.getElementById('expenseDate').value = data.date || '';
        document.getElementById('expenseCategory').value = data.category || '';
        document.getElementById('selectedCategoryLabel').textContent = data.category || '-- Choose Specific Category --';
        document.getElementById('selectedCategoryLabel').classList.add('text-gray-900');
        
        // Handle custom category logic
        const predefined = ['Electricity (Meralco)', 'Water (Maynilad)', 'Internet & WiFi', 'Communications', 'Office Supplies', 'Pantry & Cleaning', 'Building Repairs', 'Construction Materials', 'Office Equipment', 'Spare Parts Purchase', 'Tires & Batteries', 'Oil & Lubricants', 'Govt Permits & Fees', 'LTO & Registration', 'Insurance', 'Franchise Renewal', 'Staff Meals & Incentives', 'Petty Cash'];
        
        if (data.category && !predefined.includes(data.category)) {
            document.getElementById('customCategoryGroup').classList.remove('hidden');
            document.getElementById('expenseCustomCategory').value = data.category;
            document.getElementById('selectedCategoryLabel').textContent = 'Custom: ' + data.category;
        } else {
            document.getElementById('customCategoryGroup').classList.add('hidden');
        }

        // Reset all sections first
        document.getElementById('inventorySyncSection').classList.add('hidden');
        document.getElementById('franchiseSyncSection').classList.add('hidden');
        document.getElementById('standardVendorOnly').classList.remove('hidden');
        document.getElementById('topAmountGroup').classList.remove('hidden');

        // Handle Inventory Link for Edit
        if (data.category === 'Spare Parts Purchase') {
            document.getElementById('inventorySyncSection').classList.remove('hidden');
            document.getElementById('standardVendorOnly').classList.add('hidden');
            document.getElementById('expenseSparePartId').value = data.spare_part_id || '';
            document.getElementById('expenseQuantity').value = data.quantity || '';
            document.getElementById('expenseUnitPrice').value = data.unit_price || '';
            document.getElementById('expenseAmount').readOnly = true;
            document.getElementById('expenseAmount').classList.add('bg-gray-100');
            
            // Populate Part Label
            if (data.spare_part_id) {
                const part = partsCatalog.find(p => p.id == data.spare_part_id);
                if (part) {
                    const partLabel = document.getElementById('selectedPartLabel');
                    partLabel.textContent = part.name + ' (Stock: ' + part.stock_quantity + ')';
                    partLabel.classList.add('text-gray-900');
                }
            }
            
            // Populate Supplier Label (shared with standard vendor)
            if (data.vendor_name) {
                const supLabel = document.getElementById('selectedSupplierLabel');
                supLabel.textContent = data.vendor_name;
                supLabel.classList.add('text-gray-900');
                document.getElementById('syncSupplierHidden').value = data.vendor_name;
            }
        } else if (data.category === 'Franchise Renewal') {
            document.getElementById('franchiseSyncSection').classList.remove('hidden');
            document.getElementById('expenseFranchiseCaseId').value = data.franchise_case_id || '';
            // We don't necessarily have the new_expiry_date in the expense table, 
            // but we can at least show the section.
        } else {
            document.getElementById('expenseAmount').readOnly = false;
            document.getElementById('expenseAmount').classList.remove('bg-gray-100');
        }

        document.getElementById('expenseDescription').value = data.description || '';
        document.getElementById('expenseAmount').value = data.amount || '';
        document.getElementById('expenseReference').value = data.reference_number || '';
        document.getElementById('expenseVendor').value = data.vendor_name || '';
        
        // Select correct radio
        const pm = data.payment_method || 'Cash';
        if(pm === 'Cash') document.getElementById('pmCash').checked = true;
        else if(pm === 'Check') document.getElementById('pmCheck').checked = true;
        else if(pm === 'Transfer') document.getElementById('pmTransfer').checked = true;
 
        const modal = document.getElementById('expenseModal');
        modal.classList.remove('hidden');
        setTimeout(() => {
            document.getElementById('modalContainer').classList.remove('scale-95');
        }, 10);
        if (window.lucide) lucide.createIcons();
    });
}
 
function closeExpenseModal() {
    document.getElementById('modalContainer').classList.add('scale-95');
    setTimeout(() => {
        document.getElementById('expenseModal').classList.add('hidden');
    }, 150);
}

function toggleCustomSelect() {
    const menu = document.getElementById('customSelectMenu');
    const arrow = document.getElementById('customSelectArrow');
    const isHidden = menu.classList.contains('hidden');
    
    // Close others
    document.querySelectorAll('#customSelectMenu').forEach(m => m.classList.add('hidden'));
    
    if(isHidden) {
        menu.classList.remove('hidden');
        arrow.classList.add('rotate-180');
        document.getElementById('catSearch').focus();
    } else {
        menu.classList.add('hidden');
        arrow.classList.remove('rotate-180');
    }
}

function selectCategory(val) {
    const label = document.getElementById('selectedCategoryLabel');
    const hiddenInput = document.getElementById('expenseCategory');
    const menu = document.getElementById('customSelectMenu');
    const arrow = document.getElementById('customSelectArrow');
    const inventorySection = document.getElementById('inventorySyncSection');
    const franchiseSection = document.getElementById('franchiseSyncSection');
    const fCaseSelect = document.getElementById('expenseFranchiseCaseId');
    const fExpiryDate = document.getElementById('expenseNewExpiryDate');
    const amountInput = document.getElementById('expenseAmount');
    const descInput = document.getElementById('expenseDescription');
    const customGroup = document.getElementById('customCategoryGroup');
    const customInput = document.getElementById('expenseCustomCategory');
    
    // Set Basic Value
    hiddenInput.value = val;
    label.textContent = val === 'Other' ? '-- Specify Custom Category Below --' : val;
    label.classList.add('text-gray-900');
    
    // Reset Sections
    inventorySection.classList.add('hidden');
    franchiseSection.classList.add('hidden');
    document.getElementById('standardVendorOnly').classList.remove('hidden');
    document.getElementById('topAmountGroup').classList.remove('hidden');
    document.getElementById('expenseVendor').disabled = false;
    amountInput.readOnly = false;
    amountInput.classList.remove('bg-gray-100');
    customGroup.classList.add('hidden');
    customInput.removeAttribute('required');

    fCaseSelect.removeAttribute('required');
    fExpiryDate.removeAttribute('required');
    
    // 1. Handle Custom Other Logic
    if (val === 'Other') {
        customGroup.classList.remove('hidden');
        customInput.setAttribute('required', 'required');
    }

    // 2. Toggle Inventory Section
    if (val === 'Spare Parts Purchase') {
        inventorySection.classList.remove('hidden');
        document.getElementById('standardVendorOnly').classList.add('hidden');
        document.getElementById('topAmountGroup').classList.add('hidden');
        document.getElementById('expenseVendor').disabled = true; 
        amountInput.readOnly = true;
        amountInput.classList.add('bg-gray-100');
        if(!descInput.value || descInput.value.includes('Franchise')) descInput.value = 'Inventory Stock Restock';
    } 
    
    // 3. Toggle Franchise Section
    if (val === 'Franchise Renewal') {
        franchiseSection.classList.remove('hidden');
        fCaseSelect.setAttribute('required', 'required');
        fExpiryDate.setAttribute('required', 'required');
        if(!descInput.value || descInput.value.includes('Restock')) descInput.value = 'Franchise Case Renewal';
    }
    
    menu.classList.add('hidden');
    arrow.classList.remove('rotate-180');
    lucide.createIcons();
}

function filterCategories(query) {
    const items = document.querySelectorAll('.cat-item');
    const q = query.toLowerCase();
    items.forEach(item => {
        const text = item.textContent.toLowerCase();
        item.classList.toggle('hidden', !text.includes(q));
    });
}

function toggleFranchiseSelect() {
    const menu = document.getElementById('franchiseSelectMenu');
    const arrow = document.getElementById('franchiseSelectArrow');
    
    // Close other menus first
    const otherMenus = ['customSelectMenu', 'partSelectMenu', 'supplierSelectMenu'];
    otherMenus.forEach(id => {
        const m = document.getElementById(id);
        if(m && !m.classList.contains('hidden')) m.classList.add('hidden');
    });

    if (menu.classList.contains('hidden')) {
        menu.classList.remove('hidden');
        arrow.classList.add('rotate-180');
        document.getElementById('franSearch').focus();
    } else {
        menu.classList.add('hidden');
        arrow.classList.remove('rotate-180');
    }
}

function filterFranchises(query) {
    const q = query.toLowerCase();
    const items = document.querySelectorAll('.fran-item');
    items.forEach(item => {
        const text = item.textContent.toLowerCase();
        item.classList.toggle('hidden', !text.includes(q));
    });
}

function selectFranchise(id, caseNo, name) {
    const hiddenInput = document.getElementById('expenseFranchiseCaseId');
    const label = document.getElementById('selectedFranchiseLabel');
    const menu = document.getElementById('franchiseSelectMenu');
    const arrow = document.getElementById('franchiseSelectArrow');
    
    hiddenInput.value = id;
    label.textContent = 'Case #' + caseNo + ' - ' + name;
    label.classList.add('text-gray-900');
    
    menu.classList.add('hidden');
    arrow.classList.remove('rotate-180');
}

// Close all custom dropdowns on outside click
document.addEventListener('click', function(e) {
    const wrappers = ['customSelectWrapper', 'partSelectWrapper', 'supplierSelectWrapper', 'franchiseSelectWrapper'];
    wrappers.forEach(id => {
        const wrapper = document.getElementById(id);
        const menuId = id.replace('Wrapper', 'Menu');
        const arrowId = id.replace('Wrapper', 'Arrow');
        const menu = document.getElementById(menuId);
        const arrow = document.getElementById(arrowId);
        
        if (wrapper && !wrapper.contains(e.target) && menu && !menu.classList.contains('hidden')) {
            menu.classList.add('hidden');
            if(arrow) arrow.classList.remove('rotate-180');
        }
    });
});

function toggleSupplierSelect() {
    const menu = document.getElementById('supplierSelectMenu');
    const arrow = document.getElementById('supplierSelectArrow');
    const isHidden = menu.classList.contains('hidden');
    
    if(isHidden) {
        menu.classList.remove('hidden');
        arrow.classList.add('rotate-180');
        document.getElementById('supSearch').focus();
    } else {
        menu.classList.add('hidden');
        arrow.classList.remove('rotate-180');
    }
}

function selectSupplier(val) {
    const label = document.getElementById('selectedSupplierLabel');
    const newSupplierField = document.getElementById('newSupplierField');
    const hiddenSupplier = document.getElementById('syncSupplierHidden');
    const menu = document.getElementById('supplierSelectMenu');
    const arrow = document.getElementById('supplierSelectArrow');
    
    if (val === 'new') {
        label.textContent = '-- Enter New Supplier Below --';
        label.classList.add('text-gray-900');
        newSupplierField.classList.remove('hidden');
        hiddenSupplier.value = '';
        document.getElementById('expenseNewSupplier').focus();
    } else {
        label.textContent = val;
        label.classList.add('text-gray-900');
        newSupplierField.classList.add('hidden');
        hiddenSupplier.value = val;
        document.getElementById('expenseVendor').value = val;
    }
    
    menu.classList.add('hidden');
    arrow.classList.remove('rotate-180');
}

function filterSuppliers(query) {
    const items = document.querySelectorAll('.sup-item');
    const q = query.toLowerCase();
    items.forEach(item => {
        const text = item.textContent.toLowerCase();
        if(text.includes(q)) {
            item.classList.remove('hidden');
        } else {
            item.classList.add('hidden');
        }
    });
}

function togglePartSelect() {
    const menu = document.getElementById('partSelectMenu');
    const arrow = document.getElementById('partSelectArrow');
    const isHidden = menu.classList.contains('hidden');
    
    if(isHidden) {
        menu.classList.remove('hidden');
        arrow.classList.add('rotate-180');
            document.getElementById('partSearch').focus();
    } else {
        menu.classList.add('hidden');
        arrow.classList.remove('rotate-180');
    }
}

function markAsModified() {
    const val = document.getElementById('expenseSparePartId').value;
    if (val && val !== 'new') {
        document.getElementById('editModeBadge').classList.remove('hidden');
        document.getElementById('priceEditBadge').classList.remove('hidden');
        document.getElementById('updateMasterHidden').value = '1';
    }
}

function selectPart(id, name, price, supplier, stock) {
    const label = document.getElementById('selectedPartLabel');
    const hiddenId = document.getElementById('expenseSparePartId');
    const menu = document.getElementById('partSelectMenu');
    const arrow = document.getElementById('partSelectArrow');
    
    label.textContent = name + ' (Stock: ' + stock + ')';
    label.classList.add('text-gray-900');
    hiddenId.value = id;
    
    // Auto-fill price and supplier
    document.getElementById('expenseUnitPrice').value = price;
    document.getElementById('editModeBadge').classList.add('hidden');
    document.getElementById('priceEditBadge').classList.add('hidden');
    document.getElementById('updateMasterHidden').value = '0';
    
    const supName = supplier || 'Unspecified Supplier';
    selectSupplier(supName, false); // Auto-select in the custom dropdown

    document.getElementById('expenseDescription').value = "PURCHASED: " + name;
    calcInventoryTotal();
    
    menu.classList.add('hidden');
    arrow.classList.remove('rotate-180');
}

function selectSupplier(val, userAction = true) {
    const label = document.getElementById('selectedSupplierLabel');
    const newSupplierField = document.getElementById('newSupplierField');
    const hiddenSupplier = document.getElementById('syncSupplierHidden');
    const menu = document.getElementById('supplierSelectMenu');
    const arrow = document.getElementById('supplierSelectArrow');
    
    if (val === 'new') {
        label.textContent = '-- Typing New Supplier --';
        label.classList.add('text-gray-900');
        newSupplierField.classList.remove('hidden');
        hiddenSupplier.value = '';
        setTimeout(() => document.getElementById('expenseNewSupplier').focus(), 100);
        if(userAction) markAsModified();
    } else {
        label.textContent = val;
        label.classList.add('text-gray-900');
        newSupplierField.classList.add('hidden');
        hiddenSupplier.value = val;
        document.getElementById('expenseVendor').value = val;
        if(userAction) markAsModified();
    }
    
    menu.classList.add('hidden');
    arrow.classList.remove('rotate-180');
    lucide.createIcons();
}

// Ensure unit price changes also mark as modified
document.getElementById('expenseUnitPrice').addEventListener('input', function() {
    markAsModified();
});

function filterParts(query) {
    const items = document.querySelectorAll('.part-item');
    const q = query.toLowerCase();
    items.forEach(item => {
        const text = item.textContent.toLowerCase();
        if(text.includes(q)) {
            item.classList.remove('hidden');
        } else {
            item.classList.add('hidden');
        }
    });
}


function markAsModified() {
    const badge = document.getElementById('editModeBadge');
    const priceBadge = document.getElementById('priceEditBadge');
    if (badge) badge.classList.remove('hidden');
    if (priceBadge) priceBadge.classList.remove('hidden');
}

function toggleNewPartRegistration() {
    const isNew = document.getElementById('newPartGroup').classList.contains('hidden');
    const existingGroup = document.getElementById('existingPartGroup');
    const newGroup = document.getElementById('newPartGroup');
    const btn = document.getElementById('regNewPartBtn');
    const supplierHidden = document.getElementById('syncSupplierHidden');
    const partSelectId = document.getElementById('expenseSparePartId');
    const priceInput = document.getElementById('expenseUnitPrice');

    if (isNew) {
        existingGroup.classList.add('hidden');
        newGroup.classList.remove('hidden');
        btn.textContent = '✕ CANCEL REGISTRATION';
        btn.classList.replace('bg-rose-50', 'bg-gray-100');
        btn.classList.replace('text-rose-600', 'text-gray-600');
        
        partSelectId.value = 'new';
        supplierHidden.value = '';
        priceInput.value = '';
        document.getElementById('expenseNewPartName').focus();
    } else {
        existingGroup.classList.remove('hidden');
        newGroup.classList.add('hidden');
        btn.textContent = '+ REGISTER NEW ITEM';
        btn.classList.replace('bg-gray-100', 'bg-rose-50');
        btn.classList.replace('text-gray-600', 'text-rose-600');
        
        partSelectId.value = '';
        updatePartDetails(partSelectId);
    }
    lucide.createIcons();
}

function handleSupplierChange(select) {
    const val = select.value;
    const newSupplierField = document.getElementById('newSupplierField');
    const hiddenSupplier = document.getElementById('syncSupplierHidden');
    
    if (val === 'new') {
        newSupplierField.classList.remove('hidden');
        hiddenSupplier.value = '';
        document.getElementById('expenseNewSupplier').focus();
    } else {
        newSupplierField.classList.add('hidden');
        hiddenSupplier.value = val;
    }
}

function updateSyncSupplierValue(val) {
    document.getElementById('syncSupplierHidden').value = val;
}

function updatePartDetails(select) {
    const val = select.value;
    const option = select.options[select.selectedIndex];
    const unitPriceInput = document.getElementById('expenseUnitPrice');

    if (!val || val === 'new') return;

    if (unitPriceInput) unitPriceInput.value = option.dataset.price;
    
    const supplier = option.dataset.supplier || 'Unspecified Supplier';
    const syncHidden = document.getElementById('syncSupplierHidden');
    const expVendor = document.getElementById('expenseVendor');
    
    if (syncHidden) syncHidden.value = supplier;
    if (expVendor) expVendor.value = supplier;

    const descInput = document.getElementById('expenseDescription');
    if (descInput) descInput.value = "PURCHASED: " + option.text.split(' (Stock:')[0];
    
    calcInventoryTotal();
}

// Update the hidden vendor field when typing in the supplier field (for other modes)
const expNewSup = document.getElementById('expenseNewSupplier');
if (expNewSup) {
    expNewSup.oninput = function() {
        const syncHidden = document.getElementById('syncSupplierHidden');
        const expVendor = document.getElementById('expenseVendor');
        if (syncHidden) syncHidden.value = this.value;
        if (expVendor) expVendor.value = this.value;
    };
}

function calcInventoryTotal() {
    const qtyEl = document.getElementById('expenseQuantity');
    const priceEl = document.getElementById('expenseUnitPrice');
    const amountEl = document.getElementById('expenseAmount');
    
    if (!qtyEl || !priceEl || !amountEl) return;
    
    const qty = qtyEl.value || 0;
    const price = priceEl.value || 0;
    const total = qty * price;
    amountEl.value = total.toFixed(2);
}

function updateSyncSupplierValue(val) {
    document.getElementById('syncSupplierHidden').value = val;
    document.getElementById('expenseVendor').value = val;
}

// Modify form submission to handle custom category
document.getElementById('expenseForm').onsubmit = function(e) {
    const catInput = document.getElementById('expenseCategory');
    const customInput = document.getElementById('expenseCustomCategory');
    
    if (catInput.value === 'Other') {
        // Just replace the value directly since it's a hidden field
        catInput.value = customInput.value;
    }
};

function showDescription(text) {
    document.getElementById('fullDescText').textContent = text;
    document.getElementById('descModal').classList.remove('hidden');
    lucide.createIcons();
}
// --- Supplier Management AJAX Functions ---
function openSupplierManager() {
    document.getElementById('supplierManagerModal').classList.remove('hidden');
    lucide.createIcons();
}

function closeSupplierManager() {
    document.getElementById('supplierManagerModal').classList.add('hidden');
}

function resetSupplierForm() {
    document.getElementById('supplierId').value = '';
    document.getElementById('supName').value = '';
    document.getElementById('supContact').value = '';
    document.getElementById('supPhone').value = '';
    document.getElementById('supAddress').value = '';
    document.getElementById('saveSupBtn').innerText = 'Add Supplier';
    document.getElementById('cancelSupBtn').classList.add('hidden');
}

function editSupplier(data) {
    document.getElementById('supplierId').value = data.id;
    document.getElementById('supName').value = data.name;
    document.getElementById('supContact').value = data.contact_person || '';
    document.getElementById('supPhone').value = data.phone_number || '';
    document.getElementById('supAddress').value = data.address || '';
    document.getElementById('saveSupBtn').innerText = 'Update Supplier';
    document.getElementById('cancelSupBtn').classList.remove('hidden');
    document.getElementById('supName').focus();
}

async function saveSupplier(e) {
    e.preventDefault();
    const id = document.getElementById('supplierId').value;
    const data = {
        id: id || null,
        name: document.getElementById('supName').value,
        contact_person: document.getElementById('supContact').value,
        phone_number: document.getElementById('supPhone').value,
        address: document.getElementById('supAddress').value,
        _token: '{{ csrf_token() }}'
    };

    try {
        const response = await fetch('{{ route('suppliers.store') }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify(data)
        });
        const result = await response.json();
        if (result.success) {
            refreshSupplierLists(result.data);
            resetSupplierForm();
            // Show toast if available
            if(window.showToast) showToast(result.message, 'success');
            else alert(result.message);
        } else {
            alert(result.message || 'Error saving supplier');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('An error occurred while saving the supplier.');
    }
}

async function deleteSupplier(id) {
    if (!confirm('Are you sure you want to archive this supplier?')) return;
    try {
        const response = await fetch(`{{ url('suppliers') }}/${id}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
        });
        const result = await response.json();
        if (result.success) {
            location.reload(); // Refresh to update lists easily
        }
    } catch (error) {
        console.error('Error:', error);
    }
}

function refreshSupplierLists(newData) {
    // 1. Update the Dropdown in Expense Modal
    const dropdownContainer = document.getElementById('supplierListContainer');
    const existingItems = Array.from(dropdownContainer.querySelectorAll('.sup-item'));
    const isNew = !existingItems.some(item => item.textContent.includes(newData.name));

    if (isNew) {
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.onclick = () => selectSupplier(newData.name);
        btn.className = 'sup-item w-full px-4 py-2.5 text-left text-sm font-bold text-gray-600 hover:bg-amber-50 hover:text-amber-600 rounded-xl transition-all flex items-center gap-2';
        btn.innerHTML = `<i data-lucide="building" class="w-3.5 h-3.5 opacity-40"></i> ${newData.name}`;
        dropdownContainer.insertBefore(btn, dropdownContainer.lastElementChild);
    }

    // 2. Update the Manager List
    const managerList = document.getElementById('supplierListContainerFull');
    // For simplicity, we could also reload or manually append. Let's manually append/update.
    location.reload(); 
}
// Real-time Search Logic
let searchTimeout;
function performLiveSearch() {
    clearTimeout(searchTimeout);
    
    // Add a small loading effect to the table
    const tableWrapper = document.getElementById('expensesTableWrapper');
    tableWrapper.style.opacity = '0.5';
    tableWrapper.style.pointerEvents = 'none';

    searchTimeout = setTimeout(async () => {
        const search = document.getElementById('liveSearchInput').value;
        const category = document.getElementById('filterCategory').value;
        const dateFrom = document.getElementById('filterDateFrom').value;
        const dateTo = document.getElementById('filterDateTo').value;

        // Build the URL with current filters
        const params = new URLSearchParams({
            search: search,
            category: category,
            date_from: dateFrom,
            date_to: dateTo,
            format: 'json'
        });

        try {
            const response = await fetch(`{{ route('office-expenses.index') }}?${params.toString()}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            const result = await response.json();
            
            if (result.html) {
                // Update Table
                tableWrapper.innerHTML = result.html;
                lucide.createIcons();
                
                // Update Stats
                if (result.stats) {
                    const formatP = (v) => '₱' + parseFloat(v).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                    
                    const todayEl = document.getElementById('statToday');
                    if (todayEl) todayEl.textContent = formatP(result.stats.today);
                    
                    const thisMonthEl = document.getElementById('statThisMonth');
                    if (thisMonthEl) thisMonthEl.textContent = formatP(result.stats.this_month);
                    
                    const totalRecordsEl = document.getElementById('statTotalRecords');
                    if (totalRecordsEl) totalRecordsEl.textContent = result.stats.total_records;
                }
            }
        } catch (error) {
            console.error('Search error:', error);
        } finally {
            tableWrapper.style.opacity = '1';
            tableWrapper.style.pointerEvents = 'auto';
        }
    }, 300); // 300ms debounce
}

// Handle pagination clicks to remain on AJAX
document.addEventListener('click', function(e) {
    if (e.target.closest('.office-expenses-pagination a')) {
        e.preventDefault();
        const url = e.target.closest('a').href;
        fetchPage(url);
    }
});

async function fetchPage(url) {
    const tableWrapper = document.getElementById('expensesTableWrapper');
    tableWrapper.style.opacity = '0.5';
    
    try {
        const response = await fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        const result = await response.json();
        if (result.html) {
            tableWrapper.innerHTML = result.html;
            lucide.createIcons();
            // Scroll to top of table
            tableWrapper.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    } catch (error) {
        console.error('Pagination error:', error);
    } finally {
        tableWrapper.style.opacity = '1';
    }
}
</script>
@endpush
