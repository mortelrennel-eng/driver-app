@extends('layouts.app')

@section('title', 'Salary Management - Euro System')
@section('page-heading', 'Salary Management')
@section('page-subheading', 'Manage employee salaries and company expenses with monthly summaries')

@section('content')

    {{-- Page Header with Action Buttons & Filters --}}
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-6">
        <form id="filterForm" action="{{ route('salary.index') }}" method="GET" class="flex flex-wrap items-center gap-3">
            <div class="relative">
                <i data-lucide="calendar" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"></i>
                <input type="date" name="date_from" value="{{ $date_from }}" onchange="this.form.submit()" 
                    class="pl-10 pr-4 py-2 bg-white border border-gray-200 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:outline-none font-bold text-sm text-gray-700 shadow-sm" title="Date From">
            </div>
            <span class="text-gray-400 font-bold">-</span>
            <div class="relative">
                <i data-lucide="calendar" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"></i>
                <input type="date" name="date_to" value="{{ $date_to }}" onchange="this.form.submit()" 
                    class="pl-10 pr-4 py-2 bg-white border border-gray-200 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:outline-none font-bold text-sm text-gray-700 shadow-sm" title="Date To">
            </div>
            <div class="relative min-w-[200px]">
                <input type="search" name="search" id="employeeSearchInput" value="{{ $search ?? '' }}"
                    class="block w-full pl-9 pr-3 py-2 border border-gray-200 rounded-lg font-bold text-sm text-gray-700 shadow-sm focus:ring-2 focus:ring-yellow-500 focus:outline-none"
                    placeholder="Search employee..." autocomplete="off">
                <button type="submit" class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400 hover:text-yellow-600">
                    <i data-lucide="search" class="w-4 h-4"></i>
                </button>
            </div>
        </form>

        <div class="flex gap-3">
            <button type="button" onclick="openAddSalaryModal()"
                class="px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 shadow-sm flex items-center gap-2 font-bold text-sm transition-all duration-200">
                <i data-lucide="plus" class="w-4 h-4"></i> Add Salary
            </button>
            <button type="button" onclick="openMonthlyReport()"
                class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 shadow-sm flex items-center gap-2 font-bold text-sm transition-all duration-200">
                <i data-lucide="file-text" class="w-4 h-4"></i> Monthly Report
            </button>
        </div>
    </div>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        {{-- Total Employees --}}
        <div class="bg-gradient-to-br from-blue-50 to-indigo-50/70 p-4 rounded-xl shadow-sm border-l-4 border-blue-500 relative overflow-hidden flex items-center">
            <div class="flex items-center gap-4 relative z-10">
                <div class="p-3 bg-blue-100 rounded-lg shadow-sm">
                    <i data-lucide="users" class="w-6 h-6 text-blue-600"></i>
                </div>
                <div class="min-w-0">
                    <div class="text-xl font-black text-gray-900 tracking-tight truncate tabular-nums">{{ $summary['total_employees'] ?? 0 }}</div>
                    <div class="text-[10px] font-black text-blue-400 uppercase tracking-widest truncate">Total Employees</div>
                    <div class="text-[9px] text-blue-300 truncate">On payroll</div>
                </div>
            </div>
            <i data-lucide="users" class="absolute -right-3 -bottom-3 w-24 h-24 text-blue-400 opacity-[0.12] -rotate-12 z-0 pointer-events-none"></i>
        </div>

        {{-- Total Salaries --}}
        <div class="bg-gradient-to-br from-green-50 to-emerald-50/70 p-4 rounded-xl shadow-sm border-l-4 border-green-500 relative overflow-hidden flex items-center">
            <div class="flex items-center gap-4 relative z-10">
                <div class="p-3 bg-green-100 rounded-lg shadow-sm">
                    <i data-lucide="philippine-peso" class="w-6 h-6 text-green-600"></i>
                </div>
                <div class="min-w-0">
                    <div class="text-xl font-black text-gray-900 tracking-tight truncate tabular-nums">{{ formatCurrency($summary['total_salaries'] ?? 0) }}</div>
                    <div class="text-[10px] font-black text-green-400 uppercase tracking-widest truncate">Total Salaries</div>
                    <div class="text-[9px] text-green-300 truncate font-medium">{{ \Carbon\Carbon::parse($date_from)->format('M d') }} - {{ \Carbon\Carbon::parse($date_to)->format('M d, Y') }}</div>
                </div>
            </div>
            <i data-lucide="philippine-peso" class="absolute -right-3 -bottom-3 w-24 h-24 text-green-400 opacity-[0.12] -rotate-12 z-0 pointer-events-none"></i>
        </div>



        {{-- Net Profit --}}
        @php $net = ($summary['net_profit'] ?? 0); @endphp
        <div class="{{ $net >= 0 ? 'bg-gradient-to-br from-emerald-50 to-teal-50/70 border-emerald-500' : 'bg-gradient-to-br from-red-50 to-rose-50/70 border-red-500' }} p-4 rounded-xl shadow-sm border-l-4 relative overflow-hidden flex items-center">
            <div class="flex items-center gap-4 relative z-10">
                <div class="p-3 {{ $net >= 0 ? 'bg-emerald-100' : 'bg-red-100' }} rounded-lg shadow-sm">
                    <i data-lucide="{{ $net >= 0 ? 'trending-up' : 'trending-down' }}" class="w-6 h-6 {{ $net >= 0 ? 'text-emerald-600' : 'text-red-600' }}"></i>
                </div>
                <div class="min-w-0">
                    <div class="text-xl font-black text-gray-900 tracking-tight truncate tabular-nums">
                        {{ formatCurrency($net) }}
                    </div>
                    <div class="text-[10px] font-black {{ $net >= 0 ? 'text-emerald-400' : 'text-red-400' }} uppercase tracking-widest truncate">Net Profit</div>
                    <div class="text-[9px] {{ $net >= 0 ? 'text-emerald-300' : 'text-red-300' }} truncate font-medium">After payroll</div>
                </div>
            </div>
            <i data-lucide="{{ $net >= 0 ? 'trending-up' : 'trending-down' }}" class="absolute -right-3 -bottom-3 w-24 h-24 {{ $net >= 0 ? 'text-emerald-400' : 'text-red-400' }} opacity-[0.12] -rotate-12 z-0 pointer-events-none"></i>
        </div>

        {{-- Average Salary --}}
        <div class="bg-gradient-to-br from-green-50 to-emerald-50/70 p-4 rounded-xl shadow-sm border-l-4 border-green-500 relative overflow-hidden flex items-center">
            <div class="flex items-center gap-4 relative z-10">
                <div class="p-3 bg-green-100 rounded-lg shadow-sm">
                    <i data-lucide="calculator" class="w-6 h-6 text-green-600"></i>
                </div>
                <div class="min-w-0">
                    <div class="text-xl font-black text-gray-900 tracking-tight truncate tabular-nums">{{ formatCurrency($summary['avg_salary'] ?? 0) }}</div>
                    <div class="text-[10px] font-black text-green-400 uppercase tracking-widest truncate">Average Salary/Employee</div>
                </div>
            </div>
            <i data-lucide="bar-chart-2" class="absolute -right-3 -bottom-3 w-24 h-24 text-green-400 opacity-[0.12] -rotate-12 z-0 pointer-events-none"></i>
        </div>
    </div>



    {{-- Recent Salaries Table --}}
    <div class="bg-white rounded-lg shadow overflow-hidden mb-6">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-800">Recent Salaries</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Employee</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Basic Salary</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Overtime</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Pay Date</th>
                    </tr>
                </thead>
                <tbody id="salariesTableBody" class="bg-white divide-y divide-gray-200">
                    @forelse($salaries as $salary)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ $salary->employee_name }}</div>
                                <div class="text-xs text-gray-500">{{ $salary->position ?? '' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ ucfirst($salary->salary_type ?? 'Monthly') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ formatCurrency($salary->basic_salary) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ formatCurrency($salary->overtime_pay ?? 0) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-green-600">
                                {{ formatCurrency($salary->total_pay ?? $salary->basic_salary) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ isset($salary->pay_date) ? \Carbon\Carbon::parse($salary->pay_date)->format('M d, Y') : '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-10 text-center text-gray-500">
                                <i data-lucide="users" class="w-10 h-10 mx-auto mb-3 text-gray-300"></i>
                                <p>No salary records found.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>



    {{-- Add Salary Modal --}}
    <div id="addSalaryModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm hidden h-full w-full z-50 flex items-center justify-center p-4 transition-all duration-300">
        <div class="relative bg-white rounded-2xl shadow-2xl max-w-5xl w-full h-[95vh] overflow-hidden flex flex-col scale-95 transition-transform duration-300" id="salaryModalContainer">
            {{-- Modal Header (Deep Navy) --}}
            <div class="bg-slate-800 p-5 shrink-0">
                <div class="flex justify-between items-center">
                    <div class="flex items-center gap-3">
                        <div class="p-2.5 bg-white/20 rounded-xl flex items-center justify-center">
                            <i data-lucide="philippine-peso" class="w-6 h-6 text-yellow-500"></i>
                        </div>
                        <div>
                            <h3 class="text-xl font-black text-white tracking-wide" id="salaryModalTitle">Add Salary</h3>
                            <p class="text-xs font-medium text-slate-300 mt-0.5 uppercase tracking-widest">Employee Payroll Management</p>
                        </div>
                    </div>
                    <button type="button" onclick="closeAddSalaryModal()" class="text-slate-400 hover:text-white bg-slate-700/50 hover:bg-slate-700 p-2 rounded-full transition-colors">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>
            </div>

            <form id="salaryForm" method="POST" action="{{ route('salaries.store') }}" class="flex flex-col flex-1 overflow-hidden">
                @csrf
                <input type="hidden" name="_method" id="salaryMethod" value="POST">
                
                <div class="p-8 overflow-y-auto flex-1 space-y-8 custom-scrollbar">
                    {{-- Row 1: Employee, Type, Basic --}}
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="space-y-1.5">
                            <label class="text-[11px] font-black text-gray-400 uppercase tracking-widest ml-1">Employee *</label>
                            <div class="relative">
                                <i data-lucide="user" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"></i>
                                <select name="employee_raw" id="salaryEmployee" required
                                    class="w-full pl-10 pr-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 focus:outline-none font-bold text-sm text-gray-700 appearance-none">
                                    <option value="">Select Employee</option>
                                    @foreach($employees as $employee)
                                        <option value="{{ $employee->source }}_{{ $employee->id }}" data-role="{{ ucfirst($employee->role) }}">{{ $employee->name }} ({{ ucfirst($employee->role) }})</option>
                                    @endforeach
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-gray-500">
                                    <i data-lucide="chevron-down" class="w-4 h-4"></i>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-[11px] font-black text-gray-400 uppercase tracking-widest ml-1">Employee Type</label>
                            <div class="relative">
                                <i data-lucide="briefcase" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"></i>
                                <input type="text" name="employee_type" id="salaryType" readonly placeholder="Auto-filled"
                                    class="w-full pl-10 pr-4 py-2.5 bg-gray-100 border border-gray-200 rounded-xl focus:outline-none font-bold text-sm text-gray-500 cursor-not-allowed">
                            </div>
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-[11px] font-black text-gray-400 uppercase tracking-widest ml-1">Basic Salary *</label>
                            <div class="relative">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 flex items-center justify-center pointer-events-none">
                                    <span class="text-sm font-black text-gray-400">₱</span>
                                </span>
                                <input type="number" name="basic_salary" id="salaryBasic" step="1" min="1" max="99999" required placeholder="0"
                                    onkeypress="return (event.charCode >= 48 && event.charCode <= 57)"
                                    oninput="if(this.value.length > 5) this.value = this.value.slice(0, 5); if(parseInt(this.value) > 99999) this.value = 99999;"
                                    class="w-full pl-9 pr-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 focus:outline-none font-black text-sm text-gray-700">
                            </div>
                        </div>
                    </div>

                    {{-- Row 2: Additions / Deductions --}}
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 p-6 bg-gray-50 rounded-2xl border border-gray-100">
                        <div class="space-y-1.5">
                            <label class="text-[11px] font-black text-gray-400 uppercase tracking-widest ml-1">Overtime Pay</label>
                            <div class="relative">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 flex items-center justify-center pointer-events-none">
                                    <span class="text-sm font-black text-gray-400">₱</span>
                                </span>
                                <input type="number" name="overtime_pay" id="salaryOvertime" step="1" min="1" max="99999" placeholder="0"
                                    onkeypress="return (event.charCode >= 48 && event.charCode <= 57)"
                                    oninput="if(this.value.length > 5) this.value = this.value.slice(0, 5); if(parseInt(this.value) > 99999) this.value = 99999;"
                                    class="w-full pl-9 pr-4 py-2.5 bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 focus:outline-none font-bold text-sm text-gray-700">
                            </div>
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-[11px] font-black text-gray-400 uppercase tracking-widest ml-1">Holiday Pay</label>
                            <div class="relative">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 flex items-center justify-center pointer-events-none">
                                    <span class="text-sm font-black text-gray-400">₱</span>
                                </span>
                                <input type="number" name="holiday_pay" id="salaryHoliday" step="1" min="1" max="99999" placeholder="0"
                                    onkeypress="return (event.charCode >= 48 && event.charCode <= 57)"
                                    oninput="if(this.value.length > 5) this.value = this.value.slice(0, 5); if(parseInt(this.value) > 99999) this.value = 99999;"
                                    class="w-full pl-9 pr-4 py-2.5 bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 focus:outline-none font-bold text-sm text-gray-700">
                            </div>
                        </div>
                        
                        <div class="space-y-1.5">
                            <label class="text-[11px] font-black text-gray-400 uppercase tracking-widest ml-1">Night Diff.</label>
                            <div class="relative">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 flex items-center justify-center pointer-events-none">
                                    <span class="text-sm font-black text-gray-400">₱</span>
                                </span>
                                <input type="number" name="night_differential" id="salaryNight" step="1" min="1" max="99999" placeholder="0"
                                    onkeypress="return (event.charCode >= 48 && event.charCode <= 57)"
                                    oninput="if(this.value.length > 5) this.value = this.value.slice(0, 5); if(parseInt(this.value) > 99999) this.value = 99999;"
                                    class="w-full pl-9 pr-4 py-2.5 bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 focus:outline-none font-bold text-sm text-gray-700">
                            </div>
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-[11px] font-black text-gray-400 uppercase tracking-widest ml-1">Allowance</label>
                            <div class="relative">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 flex items-center justify-center pointer-events-none">
                                    <span class="text-sm font-black text-gray-400">₱</span>
                                </span>
                                <input type="number" name="allowance" id="salaryAllowance" step="1" min="1" max="99999" placeholder="0"
                                    onkeypress="return (event.charCode >= 48 && event.charCode <= 57)"
                                    oninput="if(this.value.length > 5) this.value = this.value.slice(0, 5); if(parseInt(this.value) > 99999) this.value = 99999;"
                                    class="w-full pl-9 pr-4 py-2.5 bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 focus:outline-none font-bold text-sm text-gray-700">
                            </div>
                        </div>
                    </div>

                    {{-- Row 3: Month, Year, Date (Month/Year Hidden) --}}
                    <div class="grid grid-cols-1 gap-6">
                        <div class="space-y-1.5">
                            <label class="text-[11px] font-black text-gray-400 uppercase tracking-widest ml-1">Pay Date *</label>
                            <div class="relative">
                                <i data-lucide="calendar-check" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"></i>
                                <input type="date" name="pay_date" id="salaryPayDate" value="{{ date('Y-m-d') }}" required
                                    onchange="updateMonthYear(this.value)"
                                    class="w-full pl-10 pr-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 focus:outline-none font-bold text-sm text-gray-700">
                            </div>
                        </div>
                    </div>

                    {{-- Hidden fields for Month and Year, auto-updated by Pay Date --}}
                    <input type="hidden" name="month" id="salaryMonth" value="{{ date('m') }}">
                    <input type="hidden" name="year" id="salaryYear" value="{{ date('Y') }}">
                </div>

                {{-- Form Footer --}}
                <div class="p-4 border-t flex justify-end gap-3 shadow-inner bg-gray-50 shrink-0">
                    <button type="button" onclick="closeAddSalaryModal()" 
                        class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 text-sm font-bold transition-all">
                        Cancel
                    </button>
                    <button type="submit" 
                        class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 text-sm font-bold shadow-lg shadow-green-200/50 transition-all flex items-center gap-2">
                        <i data-lucide="check" class="w-4 h-4"></i> Save Salary
                    </button>
                </div>
            </form>
        </div>
    </div>



    {{-- Monthly Report Modal --}}
    <div id="monthlyReportModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm hidden h-full w-full z-50 flex items-center justify-center p-4 transition-all duration-300">
        <div class="relative bg-white rounded-2xl shadow-2xl max-w-3xl w-full max-h-[90vh] overflow-hidden flex flex-col scale-95 transition-transform duration-300" id="monthlyReportModalContainer">
            {{-- Modal Header (Deep Navy) --}}
            <div class="bg-slate-800 p-5 shrink-0">
                <div class="flex justify-between items-center">
                    <div class="flex items-center gap-3">
                        <div class="p-2.5 bg-white/20 rounded-xl flex items-center justify-center">
                            <i data-lucide="file-text" class="w-6 h-6 text-green-400"></i>
                        </div>
                        <div>
                            <h3 class="text-xl font-black text-white tracking-wide">Monthly Report</h3>
                            <p class="text-xs font-medium text-slate-300 mt-0.5 uppercase tracking-widest">Financial Summary</p>
                        </div>
                    </div>
                    <button type="button" onclick="closeMonthlyReport()" class="text-slate-400 hover:text-white bg-slate-700/50 hover:bg-slate-700 p-2 rounded-full transition-colors">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>
            </div>

            <div class="p-8 overflow-y-auto flex-1 custom-scrollbar" id="monthlyReportContent">
                <h4 class="text-sm font-black text-gray-400 uppercase tracking-widest mb-6">{{ date('F Y') }} Summary</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div class="p-6 bg-gray-50 border border-gray-100 rounded-2xl flex items-center justify-between">
                        <div>
                            <p class="text-[11px] font-black text-gray-400 uppercase tracking-widest mb-1">Total Employees</p>
                            <p class="text-2xl font-bold text-gray-800">{{ $summary['total_employees'] ?? 0 }}</p>
                        </div>
                        <div class="p-3 bg-white rounded-xl shadow-sm border border-gray-100">
                            <i data-lucide="users" class="h-6 w-6 text-gray-500"></i>
                        </div>
                    </div>
                    
                    <div class="p-6 bg-blue-50/50 border border-blue-100 rounded-2xl flex items-center justify-between">
                        <div>
                            <p class="text-[11px] font-black text-blue-400 uppercase tracking-widest mb-1">Total Salaries</p>
                            <p class="text-2xl font-bold text-blue-700">{{ formatCurrency($summary['total_salaries'] ?? 0) }}</p>
                        </div>
                        <div class="p-3 bg-white rounded-xl shadow-sm border border-blue-100">
                            <i data-lucide="philippine-peso" class="h-6 w-6 text-blue-500"></i>
                        </div>
                    </div>
                    

                    
                    <div class="p-6 {{ ($summary['net_profit'] ?? 0) >= 0 ? 'bg-green-50/50 border-green-100' : 'bg-red-50/50 border-red-100' }} border rounded-2xl flex items-center justify-between">
                        <div>
                            <p class="text-[11px] font-black {{ ($summary['net_profit'] ?? 0) >= 0 ? 'text-green-500' : 'text-red-500' }} uppercase tracking-widest mb-1">Net Profit</p>
                            <p class="text-2xl font-bold {{ ($summary['net_profit'] ?? 0) >= 0 ? 'text-green-700' : 'text-red-700' }}">
                                {{ formatCurrency($summary['net_profit'] ?? 0) }}
                            </p>
                        </div>
                        <div class="p-3 bg-white rounded-xl shadow-sm border {{ ($summary['net_profit'] ?? 0) >= 0 ? 'border-green-100' : 'border-red-100' }}">
                            <i data-lucide="{{ ($summary['net_profit'] ?? 0) >= 0 ? 'trending-up' : 'trending-down' }}" class="h-6 w-6 {{ ($summary['net_profit'] ?? 0) >= 0 ? 'text-green-500' : 'text-red-500' }}"></i>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Form Footer --}}
            <div class="p-4 border-t flex justify-end gap-3 shadow-inner bg-gray-50 shrink-0">
                <button onclick="closeMonthlyReport()" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 text-sm font-bold transition-all">
                    Close
                </button>
                <button onclick="printSalaryReport()" class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 text-sm font-bold shadow-lg shadow-green-200/50 transition-all flex items-center gap-2">
                    <i data-lucide="printer" class="w-4 h-4"></i> Print Report
                </button>
            </div>
        </div>
    </div>

@push('styles')
<style>
    @media print {
        @page {
            size: A4 portrait;
            margin: 0;
        }
        /* Hide EVERYTHING on the page */
        * {
            visibility: hidden !important;
        }
        /* Show only our report and its children */
        #printableReportContainer,
        #printableReportContainer * {
            visibility: visible !important;
        }
        /* Position report to cover full page with manual padding as margins */
        #printableReportContainer {
            position: fixed !important;
            top: 0 !important;
            left: 0 !important;
            width: 100% !important;
            height: auto !important;
            margin: 0 !important;
            padding: 1.5cm 2cm !important;
            background: white !important;
            color: black !important;
            z-index: 99999 !important;
            box-sizing: border-box !important;
            overflow: visible !important;
        }
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }
</style>
@endpush


<!-- Hidden Printable Report -->
<div id="printableReportContainer" class="hidden" style="font-family: Arial, sans-serif; font-size: 12px; background: white; color: black;">

    {{-- HEADER --}}
    <div style="text-align:center; border-bottom: 2px solid #000; padding-bottom: 14px; margin-bottom: 14px;">
        <img src="{{ asset('uploads/logo.png') }}" alt="Euro Taxi System" style="height:60px; display:block; margin:0 auto 8px auto; object-fit:contain;" onerror="this.style.display='none'">
        <div style="font-size:15px; font-weight:900; letter-spacing:3px; text-transform:uppercase; color:#000; margin-bottom:3px;">Monthly Salary Report</div>
        <div style="font-size:11px; color:#555;">Period: {{ \Carbon\Carbon::parse($date_from)->format('F d, Y') }} &mdash; {{ \Carbon\Carbon::parse($date_to)->format('F d, Y') }}</div>
    </div>

    {{-- META INFO --}}
    <table style="width:100%; font-size:11px; margin-bottom:14px; border-collapse:collapse;">
        <tr>
            <td style="width:50%; vertical-align:top; padding: 0 0 6px 0;">
                <strong>Generated By:</strong> {{ auth()->user()->full_name ?? 'Admin' }}<br>
                <strong>Date Generated:</strong> {{ date('F d, Y h:i A') }}
            </td>
            <td style="width:50%; vertical-align:top; text-align:right; padding: 0 0 6px 0;">
                <strong>Total Employees:</strong> {{ $summary['total_employees'] ?? 0 }}<br>
                <strong>Net Profit (After Payroll):</strong> {{ formatCurrency($summary['net_profit'] ?? 0) }}
            </td>
        </tr>
    </table>

    {{-- SALARY TABLE --}}
    <table style="width:100%; border-collapse:collapse; font-size:11px; margin-bottom:20px; table-layout:fixed;">
        <colgroup>
            <col style="width:28%;">
            <col style="width:14%;">
            <col style="width:19%;">
            <col style="width:16%;">
            <col style="width:23%;">
        </colgroup>
        <thead>
            <tr style="background:#eeeeee; border-top:2px solid #000; border-bottom:2px solid #000;">
                <th style="padding:7px 5px; text-align:left; font-weight:bold; text-transform:uppercase; font-size:10px; word-wrap:break-word;">Employee</th>
                <th style="padding:7px 5px; text-align:left; font-weight:bold; text-transform:uppercase; font-size:10px;">Type</th>
                <th style="padding:7px 5px; text-align:right; font-weight:bold; text-transform:uppercase; font-size:10px;">Basic Salary</th>
                <th style="padding:7px 5px; text-align:right; font-weight:bold; text-transform:uppercase; font-size:10px;">Overtime</th>
                <th style="padding:7px 5px; text-align:right; font-weight:bold; text-transform:uppercase; font-size:10px;">Net Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($salaries as $i => $salary)
            <tr style="border-bottom:1px solid #ddd; background:{{ $i % 2 === 0 ? '#fff' : '#f9f9f9' }};">
                <td style="padding:5px 5px; word-wrap:break-word; overflow-wrap:break-word;">
                    <span style="font-weight:bold; display:block;">{{ $salary->employee_name }}</span>
                    <span style="font-size:10px; color:#555;">{{ $salary->position ?? '' }}</span>
                </td>
                <td style="padding:5px 5px;">{{ ucfirst($salary->salary_type ?? 'Monthly') }}</td>
                <td style="padding:5px 5px; text-align:right;">{{ formatCurrency($salary->basic_salary) }}</td>
                <td style="padding:5px 5px; text-align:right;">{{ formatCurrency($salary->overtime_pay ?? 0) }}</td>
                <td style="padding:5px 5px; text-align:right; font-weight:bold;">{{ formatCurrency($salary->total_pay ?? $salary->basic_salary) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr style="border-top:3px solid #000; background:#e8e8e8;">
                <td colspan="2" style="padding:8px 5px; text-align:right; font-weight:900; text-transform:uppercase; font-size:11px;">Grand Total</td>
                <td style="padding:8px 5px; text-align:right; font-weight:900;">{{ formatCurrency($salaries->sum('basic_salary')) }}</td>
                <td style="padding:8px 5px; text-align:right; font-weight:900;">{{ formatCurrency($salaries->sum('overtime_pay')) }}</td>
                <td style="padding:8px 5px; text-align:right; font-weight:900; font-size:13px;">{{ formatCurrency($summary['total_salaries'] ?? 0) }}</td>
            </tr>
        </tfoot>
    </table>

    {{-- SIGNATURE SECTION --}}
    <table style="width:100%; margin-top:70px; font-size:11px; text-align:center; border-collapse:collapse;">
        <tr>
            <td style="width:33%; padding: 0 20px;">
                <div style="border-top:1px solid #000; padding-top:6px; font-weight:bold; text-transform:uppercase; letter-spacing:1px;">Prepared By</div>
            </td>
            <td style="width:33%; padding: 0 20px;">
                <div style="border-top:1px solid #000; padding-top:6px; font-weight:bold; text-transform:uppercase; letter-spacing:1px;">Approved By</div>
            </td>
            <td style="width:33%; padding: 0 20px;">
                <div style="border-top:1px solid #000; padding-top:6px; font-weight:bold; text-transform:uppercase; letter-spacing:1px;">Received By</div>
            </td>
        </tr>
    </table>

</div>




<script>
function openAddSalaryModal() {
    document.getElementById('salaryModalTitle').textContent = 'Add Salary';
    document.getElementById('salaryMethod').value = 'POST';
    document.getElementById('salaryForm').action = '{{ route('salaries.store') }}';
    
    const empSelect = document.getElementById('salaryEmployee');
    empSelect.value = '';
    empSelect.style.pointerEvents = 'auto';
    
    document.getElementById('salaryType').value = '';
    document.getElementById('salaryBasic').value = '';
    document.getElementById('salaryOvertime').value = '';
    document.getElementById('salaryHoliday').value = '';
    document.getElementById('salaryNight').value = '';
    document.getElementById('salaryAllowance').value = '';
    document.getElementById('salaryPayDate').value = '{{ date('Y-m-d') }}';
    
    if (document.getElementById('salaryMonth')) {
        document.getElementById('salaryMonth').value = '{{ date('m') }}';
        document.getElementById('salaryYear').value = '{{ date('Y') }}';
    }
    
    const modal = document.getElementById('addSalaryModal');
    modal.classList.remove('hidden');
    setTimeout(() => {
        document.getElementById('salaryModalContainer').classList.remove('scale-95');
    }, 10);
    lucide.createIcons();
}

function updateMonthYear(dateString) {
    if (!dateString) return;
    const date = new Date(dateString);
    if (document.getElementById('salaryMonth')) {
        document.getElementById('salaryMonth').value = date.getMonth() + 1;
        document.getElementById('salaryYear').value = date.getFullYear();
    }
}

function openEditSalaryModal(salary) {
    document.getElementById('salaryModalTitle').textContent = 'Edit Salary Details';
    document.getElementById('salaryMethod').value = 'PUT';
    document.getElementById('salaryForm').action = '{{ url('salaries') }}/' + salary.id;
    
    const empSelect = document.getElementById('salaryEmployee');
    empSelect.value = salary.source + '_' + salary.employee_id;
    empSelect.style.pointerEvents = 'none';

    document.getElementById('salaryType').value = salary.employee_type || '';
    document.getElementById('salaryBasic').value = salary.basic_salary || '';
    document.getElementById('salaryOvertime').value = salary.overtime_pay || '';
    document.getElementById('salaryHoliday').value = salary.holiday_pay || '';
    document.getElementById('salaryNight').value = salary.night_differential || '';
    document.getElementById('salaryAllowance').value = salary.allowance || '';
    
    if (salary.pay_date) {
        document.getElementById('salaryPayDate').value = salary.pay_date;
    }

    if (document.getElementById('salaryMonth')) {
        document.getElementById('salaryMonth').value = salary.month;
        document.getElementById('salaryYear').value = salary.year;
    }
    
    const modal = document.getElementById('addSalaryModal');
    modal.classList.remove('hidden');
    setTimeout(() => {
        document.getElementById('salaryModalContainer').classList.remove('scale-95');
    }, 10);
    lucide.createIcons();
}

function closeAddSalaryModal() {
    document.getElementById('salaryModalContainer').classList.add('scale-95');
    setTimeout(() => {
        document.getElementById('addSalaryModal').classList.add('hidden');
    }, 150);
}



function openMonthlyReport() {
    const modal = document.getElementById('monthlyReportModal');
    modal.classList.remove('hidden');
    setTimeout(() => {
        document.getElementById('monthlyReportModalContainer').classList.remove('scale-95');
    }, 10);
    lucide.createIcons();
}

function closeMonthlyReport() {
    document.getElementById('monthlyReportModalContainer').classList.add('scale-95');
    setTimeout(() => {
        document.getElementById('monthlyReportModal').classList.add('hidden');
    }, 150);
}

document.getElementById('employeeSearchInput').addEventListener('input', function(e) {
    const searchTerm = e.target.value.toLowerCase();
    const rows = document.querySelectorAll('#salariesTableBody tr');
    
    rows.forEach(row => {
        if (row.querySelector('td[colspan]')) return; // Skip empty row message
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(searchTerm) ? '' : 'none';
    });
});

document.getElementById('employeeSearchInput').addEventListener('keydown', function(e) {
    if (e.key === 'Enter') {
        e.preventDefault();
    }
});

// Auto-fill Employee Type based on selected Employee
document.getElementById('salaryEmployee').addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    const role = selectedOption.getAttribute('data-role') || '';
    document.getElementById('salaryType').value = role;
});

function printSalaryReport() {
    const container = document.getElementById('printableReportContainer');

    // Make visible before printing
    container.classList.remove('hidden');
    container.style.display = 'block';

    window.print();

    // Restore after printing dialog closes
    container.style.display = '';
    container.classList.add('hidden');
}
</script>
@endsection
