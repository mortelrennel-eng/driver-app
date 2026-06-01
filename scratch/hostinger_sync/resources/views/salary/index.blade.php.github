@extends('layouts.app')

@section('title', 'Salary Management - Euro System')
@section('page-heading', 'Salary Management')
@section('page-subheading', 'Manage employee salaries and company expenses with monthly summaries')

@section('content')

    {{-- Page Header with Action Buttons --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div></div>
        <div class="flex gap-3">
            <button type="button" onclick="openAddSalaryModal()"
                class="px-5 py-3 bg-yellow-600 text-white rounded-2xl hover:bg-yellow-700 flex items-center gap-2 font-black uppercase text-[10px] tracking-widest shadow-lg shadow-yellow-600/20 transition-all active:scale-95">
                <i data-lucide="plus" class="w-4 h-4"></i> Add Salary
            </button>
            <button type="button" onclick="openMonthlyReport()"
                class="px-5 py-3 bg-green-600 text-white rounded-2xl hover:bg-green-700 flex items-center gap-2 font-black uppercase text-[10px] tracking-widest shadow-lg shadow-green-600/20 transition-all active:scale-95">
                <i data-lucide="file-text" class="w-4 h-4"></i> Monthly Report
            </button>
        </div>
    </div>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow card-hover">
            <div class="p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Total Employees</p>
                        <p class="text-2xl font-bold text-blue-600">{{ $summary['total_employees'] ?? 0 }}</p>
                        <p class="text-xs text-gray-500 mt-1">On payroll</p>
                    </div>
                    <div class="p-3 bg-blue-100 rounded-full">
                        <i data-lucide="users" class="h-8 w-8 text-blue-600"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Total Salaries</p>
                        <p class="text-3xl font-black text-green-600">{{ formatCurrency($summary['total_salaries'] ?? 0) }}</p>
                        <p class="text-[10px] text-gray-400 font-bold uppercase mt-1">Released this month</p>
                    </div>
                    <div class="p-4 bg-green-50 rounded-2xl">
                        <i data-lucide="banknote" class="h-8 w-8 text-green-600"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Company Net Balance</p>
                        @php $net = ($summary['net_profit'] ?? 0); @endphp
                        <p class="text-3xl font-black {{ $net >= 0 ? 'text-blue-600' : 'text-red-600' }}">
                            {{ formatCurrency($net) }}
                        </p>
                        <p class="text-[10px] text-gray-400 font-bold uppercase mt-1">Remaining after payroll</p>
                    </div>
                    <div class="p-4 {{ $net >= 0 ? 'bg-blue-50' : 'bg-red-50' }} rounded-2xl">
                        <i data-lucide="trending-up" class="h-8 w-8 {{ $net >= 0 ? 'text-blue-600' : 'text-red-600' }}"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Average Stats --}}
    <div class="grid grid-cols-1 gap-4 mb-6">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 flex items-center justify-between">
            <div>
                <h3 class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Average Salary per Employee</h3>
                <p class="text-2xl font-black text-green-600">{{ formatCurrency($summary['avg_salary'] ?? 0) }}</p>
            </div>
            <div class="hidden md:block">
                <div class="flex gap-2">
                    <span class="px-3 py-1 bg-green-50 text-green-600 rounded-full text-[10px] font-black uppercase tracking-wider">Payroll Stability</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Search and Filter Bar --}}
    <div class="mb-6">
        <form method="GET" action="{{ route('salary.index') }}" class="flex flex-wrap gap-4 items-end bg-gray-50/50 p-4 rounded-3xl border border-gray-100">
            <div class="flex-1 min-w-[200px]">
                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 px-1">Search Employee</label>
                <div class="relative group">
                    <i data-lucide="search" class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 group-focus-within:text-yellow-600 transition-colors"></i>
                    <input type="text" name="search" value="{{ $search }}" placeholder="Search by name or position..."
                        class="w-full pl-11 pr-4 py-3.5 bg-white border border-gray-100 rounded-2xl text-xs font-black shadow-sm focus:ring-4 focus:ring-yellow-500/10 focus:border-yellow-400 transition-all outline-none">
                </div>
            </div>
            <div class="w-full sm:w-auto">
                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 px-1">Filter by Date</label>
                <div class="relative group">
                    <i data-lucide="calendar" class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 group-focus-within:text-yellow-600 transition-colors"></i>
                    <input type="date" name="date" value="{{ request('date') }}"
                        class="w-full pl-11 pr-4 py-3.5 bg-white border border-gray-100 rounded-2xl text-xs font-black shadow-sm focus:ring-4 focus:ring-yellow-500/10 focus:border-yellow-400 transition-all outline-none">
                </div>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="h-[52px] px-6 bg-gray-900 text-white rounded-2xl hover:bg-black transition-all active:scale-95 shadow-xl shadow-black/10 flex items-center justify-center gap-2">
                    <i data-lucide="filter" class="w-4 h-4"></i>
                    <span class="text-[10px] font-black uppercase tracking-widest">Apply</span>
                </button>
                @if(request()->filled('search') || request()->filled('date'))
                    <a href="{{ route('salary.index') }}" class="h-[52px] w-[52px] bg-white border border-gray-100 text-gray-400 rounded-2xl hover:bg-gray-50 flex items-center justify-center transition-all active:scale-95 shadow-sm">
                        <i data-lucide="refresh-cw" class="w-4 h-4"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- Recent Salaries Table --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mb-6">
        <div class="px-6 py-5 border-b border-gray-50 bg-gray-50/50 flex justify-between items-center">
            <h2 class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Recent Salary Records</h2>
            <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-[10px] font-black">ACTIVE PAYROLL</span>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-4 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Employee</th>
                        <th class="px-6 py-4 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Type / Position</th>
                        <th class="px-6 py-4 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Basic Base</th>
                        <th class="px-6 py-4 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Earnings</th>
                        <th class="px-6 py-4 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Total Pay</th>
                        <th class="px-6 py-4 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Pay Date</th>
                        <th class="px-6 py-4 text-right text-[10px] font-black text-gray-400 uppercase tracking-widest">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    @forelse($salaries as $salary)
                        <tr class="hover:bg-gray-50/80 transition-all">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-black text-gray-800">{{ $salary->employee_name }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2.5 py-1 bg-blue-50 text-blue-700 rounded-lg text-[10px] font-black uppercase tracking-wider">
                                    {{ $salary->position ?? 'Staff' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-600">
                                {{ formatCurrency($salary->basic_salary) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @php $extras = ($salary->overtime_pay ?? 0) + ($salary->holiday_pay ?? 0) + ($salary->allowance ?? 0); @endphp
                                <span class="text-[10px] font-bold {{ $extras > 0 ? 'text-green-600' : 'text-gray-300' }}">
                                    +{{ formatCurrency($extras) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-base font-black text-gray-900">{{ formatCurrency($salary->total_pay ?? $salary->basic_salary) }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-[10px] font-black text-gray-500 uppercase tracking-tighter">
                                    {{ isset($salary->pay_date) ? \Carbon\Carbon::parse($salary->pay_date)->format('M d, Y') : '-' }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <button type="button" onclick="openEditSalaryModal({{ $salary->id }})" class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center hover:bg-blue-100 transition-all inline-flex align-middle mr-1">
                                    <i data-lucide="edit" class="w-4 h-4"></i>
                                </button>
                                <form method="POST" action="{{ route('salaries.destroy', $salary->id) }}" class="inline"
                                    onsubmit="return confirm('Delete this salary record?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="w-8 h-8 rounded-lg bg-red-50 text-red-600 flex items-center justify-center hover:bg-red-100 transition-all inline-flex align-middle">
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-10 text-center text-gray-400">
                                <i data-lucide="users" class="w-10 h-10 mx-auto mb-3 text-gray-200"></i>
                                <p class="text-[10px] font-black uppercase tracking-widest">No salary records found for this period.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Add Salary Modal --}}
    <div id="addSalaryModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden h-full w-full z-50 flex items-center justify-center p-4">
        <div class="relative bg-white rounded-lg shadow-xl max-w-lg w-full max-h-[90vh] overflow-y-auto">
            <div class="flex justify-between items-center p-6 border-b">
                <h3 class="text-lg font-bold text-gray-900" id="salaryModalTitle">Add Salary</h3>
                <button onclick="closeAddSalaryModal()" class="text-gray-400 hover:text-gray-600">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
            <form id="salaryForm" method="POST" action="{{ route('salaries.store') }}" class="p-6 space-y-4">
                @csrf
                <input type="hidden" name="_method" id="salaryMethod" value="POST">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Employee *</label>
                    <select name="employee_raw" id="salaryEmployee" required
                        onchange="autoFillEmployeeType(this)"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500">
                        <option value="" data-role="">Select Employee</option>
                        @foreach($employees as $employee)
                            <option value="{{ $employee->source }}_{{ $employee->id }}" data-role="{{ $employee->role }}">{{ $employee->name }} ({{ ucfirst($employee->role) }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Employee Type</label>
                        <input type="text" name="employee_type" id="salaryType" readonly
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 font-bold text-gray-700">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Basic Salary *</label>
                        <input type="number" name="basic_salary" id="salaryBasic" step="0.01" min="0" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Overtime Pay</label>
                        <input type="number" name="overtime_pay" id="salaryOvertime" step="0.01" min="0" value="0"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Holiday Pay</label>
                        <input type="number" name="holiday_pay" id="salaryHoliday" step="0.01" min="0" value="0"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Night Differential</label>
                        <input type="number" name="night_differential" id="salaryNight" step="0.01" min="0" value="0"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Allowance</label>
                        <input type="number" name="allowance" id="salaryAllowance" step="0.01" min="0" value="0"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Month *</label>
                        <select name="month" id="salaryMonth" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500">
                            @for($i = 1; $i <= 12; $i++)
                                <option value="{{ $i }}" {{ $i == date('m') ? 'selected' : '' }}>{{ date('F', mktime(0, 0, 0, $i, 1)) }}</option>
                            @endfor
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Year *</label>
                        <select name="year" id="salaryYear" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500">
                            @for($i = 2024; $i <= 2030; $i++)
                                <option value="{{ $i }}" {{ $i == date('Y') ? 'selected' : '' }}>{{ $i }}</option>
                            @endfor
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Pay Date *</label>
                    <input type="date" name="pay_date" id="salaryPayDate" value="{{ date('Y-m-d') }}" required
                        onchange="updateMonthYear(this.value)"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500">
                </div>
                <div class="flex gap-3 mt-4">
                    <button type="submit" class="flex-1 bg-yellow-600 text-white py-2 rounded-lg hover:bg-yellow-700">Save</button>
                    <button type="button" onclick="closeAddSalaryModal()" class="flex-1 bg-gray-300 text-gray-700 py-2 rounded-lg hover:bg-gray-400">Cancel</button>
                </div>
            </form>
        </div>
    </div>



    {{-- Monthly Report Modal --}}
    <div id="monthlyReportModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden h-full w-full z-50 flex items-center justify-center p-4">
        <div class="relative bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
            <div class="flex justify-between items-center p-6 border-b">
                <h3 class="text-lg font-bold text-gray-900">Monthly Report</h3>
                <button onclick="closeMonthlyReport()" class="text-gray-400 hover:text-gray-600">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
            <div class="p-6" id="monthlyReportContent">
                <h4 class="text-md font-semibold text-gray-700 mb-4">{{ date('F Y') }} Summary</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                    <div class="p-6 bg-green-50 rounded-2xl border border-green-100">
                        <p class="text-[10px] font-black text-green-600 uppercase tracking-widest mb-1">Total Employees</p>
                        <p class="text-2xl font-black text-green-800">{{ $summary['total_employees'] ?? 0 }}</p>
                    </div>
                    <div class="p-6 bg-blue-50 rounded-2xl border border-blue-100">
                        <p class="text-[10px] font-black text-blue-600 uppercase tracking-widest mb-1">Total Salaries</p>
                        <p class="text-2xl font-black text-blue-800">{{ formatCurrency($summary['total_salaries'] ?? 0) }}</p>
                    </div>
                </div>
                <div class="p-6 bg-gray-50 rounded-2xl border border-gray-100 mb-6">
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Company Net Balance (After Payroll)</p>
                    <p class="text-3xl font-black {{ ($summary['net_profit'] ?? 0) >= 0 ? 'text-gray-800' : 'text-red-600' }}">
                        {{ formatCurrency($summary['net_profit'] ?? 0) }}
                    </p>
                </div>
                <div class="flex justify-end gap-3">
                    <button onclick="window.print()" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 flex items-center gap-2">
                        <i data-lucide="printer" class="w-4 h-4"></i> Print
                    </button>
                    <button onclick="closeMonthlyReport()" class="px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700">Close</button>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
<script>
// Auto-fill Employee Type based on selected employee's role from Staff Records
function autoFillEmployeeType(selectEl) {
    const selectedOption = selectEl.options[selectEl.selectedIndex];
    const role = selectedOption.getAttribute('data-role') || '';
    const typeInput = document.getElementById('salaryType');
    
    if (!role) {
        typeInput.value = '';
        return;
    }

    // Capitalize first letters for display
    const formattedRole = role.split(' ')
        .map(word => word.charAt(0).toUpperCase() + word.slice(1).toLowerCase())
        .join(' ');
    
    typeInput.value = formattedRole;
}

function openAddSalaryModal() {
    document.getElementById('salaryModalTitle').textContent = 'Add Salary';
    document.getElementById('salaryMethod').value = 'POST';
    document.getElementById('salaryForm').action = '{{ route('salaries.store') }}';
    document.getElementById('salaryEmployee').value = '';
    document.getElementById('salaryBasic').value = '';
    document.getElementById('salaryOvertime').value = '0';
    document.getElementById('salaryHoliday').value = '0';
    document.getElementById('salaryNight').value = '0';
    document.getElementById('salaryAllowance').value = '0';
    document.getElementById('salaryPayDate').value = '{{ date('Y-m-d') }}';
    document.getElementById('salaryMonth').value = '{{ date('m') }}';
    document.getElementById('salaryYear').value = '{{ date('Y') }}';
    document.getElementById('addSalaryModal').classList.remove('hidden');
    lucide.createIcons();
}

function updateMonthYear(dateString) {
    if (!dateString) return;
    const date = new Date(dateString);
    document.getElementById('salaryMonth').value = date.getMonth() + 1;
    document.getElementById('salaryYear').value = date.getFullYear();
    const months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
    document.getElementById('periodLabel').textContent = `${months[date.getMonth()]} ${date.getFullYear()}`;
}

function openEditSalaryModal(id) {
    document.getElementById('salaryModalTitle').textContent = 'Edit Salary';
    document.getElementById('salaryMethod').value = 'PUT';
    document.getElementById('salaryForm').action = '{{ url('salaries') }}/' + id;
    document.getElementById('addSalaryModal').classList.remove('hidden');
    lucide.createIcons();
}

function closeAddSalaryModal() {
    document.getElementById('addSalaryModal').classList.add('hidden');
}

function openMonthlyReport() {
    document.getElementById('monthlyReportModal').classList.remove('hidden');
    lucide.createIcons();
}

function closeMonthlyReport() {
    document.getElementById('monthlyReportModal').classList.add('hidden');
}
</script>
@endpush