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
                class="px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 flex items-center gap-2">
                <i data-lucide="plus" class="w-4 h-4"></i> Add Salary
            </button>
            <button type="button" onclick="openAddExpenseModal()"
                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 flex items-center gap-2">
                <i data-lucide="plus" class="w-4 h-4"></i> Add Expense
            </button>
            <button type="button" onclick="openMonthlyReport()"
                class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 flex items-center gap-2">
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

        <div class="bg-white rounded-lg shadow card-hover">
            <div class="p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Total Salaries</p>
                        <p class="text-2xl font-bold text-green-600">{{ formatCurrency($summary['total_salaries'] ?? 0) }}</p>
                        <p class="text-xs text-gray-500 mt-1">This month</p>
                    </div>
                    <div class="p-3 bg-green-100 rounded-full">
                        <i data-lucide="dollar-sign" class="h-8 w-8 text-green-600"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow card-hover">
            <div class="p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Total Expenses</p>
                        <p class="text-2xl font-bold text-red-600">{{ formatCurrency($summary['total_expenses'] ?? 0) }}</p>
                        <p class="text-xs text-gray-500 mt-1">This month</p>
                    </div>
                    <div class="p-3 bg-red-100 rounded-full">
                        <i data-lucide="dollar-sign" class="h-8 w-8 text-red-600"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow card-hover">
            <div class="p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Net Profit</p>
                        @php $net = ($summary['net_profit'] ?? 0); @endphp
                        <p class="text-2xl font-bold {{ $net >= 0 ? 'text-green-600' : 'text-red-600' }}">
                            {{ formatCurrency($net) }}
                        </p>
                        <p class="text-xs text-gray-500 mt-1">After payroll</p>
                    </div>
                    <div class="p-3 {{ $net >= 0 ? 'bg-green-100' : 'bg-red-100' }} rounded-full">
                        <i data-lucide="{{ $net >= 0 ? 'trending-up' : 'trending-down' }}" class="h-8 w-8 {{ $net >= 0 ? 'text-green-600' : 'text-red-600' }}"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Average Stats --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="font-semibold text-gray-700 mb-1">Average Salary/Employee</h3>
            <p class="text-xl text-green-600">{{ formatCurrency($summary['avg_salary'] ?? 0) }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="font-semibold text-gray-700 mb-1">Average Expense/Employee</h3>
            <p class="text-xl text-red-600">{{ formatCurrency($summary['avg_expense'] ?? 0) }}</p>
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
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
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
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <button type="button" onclick="openEditSalaryModal({{ $salary->id }})" class="text-blue-600 hover:text-blue-900 mr-2">
                                    <i data-lucide="edit" class="w-4 h-4"></i>
                                </button>
                                <form method="POST" action="{{ route('salaries.destroy', $salary->id) }}" class="inline"
                                    onsubmit="return confirm('Delete this salary record?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900">
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-10 text-center text-gray-500">
                                <i data-lucide="users" class="w-10 h-10 mx-auto mb-3 text-gray-300"></i>
                                <p>No salary records found.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Recent Expense Records --}}
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-800">Recent Expenses</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Description</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Category</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Receipt</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($expense_records ?? [] as $expense)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $expense->expense_type ?? '-' }}</td>
                            <td class="px-6 py-4 text-sm text-gray-900">{{ Str::limit($expense->description, 40) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $expense->category ?? '-' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-red-600">{{ formatCurrency($expense->amount) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ isset($expense->expense_date) ? \Carbon\Carbon::parse($expense->expense_date)->format('M d, Y') : '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                @if(isset($expense->receipt_path) && $expense->receipt_path)
                                    <a href="{{ asset('storage/' . $expense->receipt_path) }}" target="_blank" class="text-blue-600">View</a>
                                @else
                                    <span class="text-gray-400">None</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <form method="POST" action="{{ route('salaries.destroy', $expense->id) }}" class="inline"
                                    onsubmit="return confirm('Delete this expense?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900">
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-6 text-center text-gray-500 text-sm">No expense records found.</td>
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
                    <select name="employee_id" id="salaryEmployee" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500">
                        <option value="">Select Employee</option>
                        @foreach($employees as $employee)
                            <option value="{{ $employee->id }}">{{ $employee->full_name }} ({{ ucfirst($employee->role) }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Employee Type</label>
                        <select name="employee_type" id="salaryType" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500">
                            <option value="Staff">Staff</option>
                            <option value="Driver">Driver</option>
                            <option value="Admin">Admin</option>
                            <option value="Other">Other</option>
                        </select>
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

    {{-- Add Expense Modal --}}
    <div id="addExpenseModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden h-full w-full z-50 flex items-center justify-center p-4">
        <div class="relative bg-white rounded-lg shadow-xl max-w-lg w-full max-h-[90vh] overflow-y-auto">
            <div class="flex justify-between items-center p-6 border-b">
                <h3 class="text-lg font-bold text-gray-900">Add Expense</h3>
                <button onclick="closeAddExpenseModal()" class="text-gray-400 hover:text-gray-600">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
            <form method="POST" action="{{ route('salaries.store') }}" enctype="multipart/form-data" class="p-6 space-y-4">
                @csrf
                <input type="hidden" name="record_type" value="expense">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Expense Type *</label>
                    <input type="text" name="expense_type" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                    <select name="category" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500">
                        <option value="Operational">Operational</option>
                        <option value="Administrative">Administrative</option>
                        <option value="Utilities">Utilities</option>
                        <option value="Maintenance">Maintenance</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea name="description" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500"></textarea>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Amount *</label>
                        <input type="number" name="amount" step="0.01" min="0" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Date *</label>
                        <input type="date" name="expense_date" value="{{ date('Y-m-d') }}" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Receipt (Image/PDF)</label>
                    <input type="file" name="receipt" accept=".jpg,.jpeg,.png,.pdf"
                        class="block w-full text-sm text-gray-700 border border-gray-300 rounded-lg cursor-pointer">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Approved By</label>
                    <input type="text" name="approved_by"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500">
                </div>
                <div class="flex gap-3 mt-4">
                    <button type="submit" class="flex-1 bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700">Save</button>
                    <button type="button" onclick="closeAddExpenseModal()" class="flex-1 bg-gray-300 text-gray-700 py-2 rounded-lg hover:bg-gray-400">Cancel</button>
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
                <div class="grid grid-cols-2 gap-4 mb-6">
                    <div class="p-4 bg-green-50 rounded-lg">
                        <p class="text-sm text-gray-600">Total Employees</p>
                        <p class="text-xl font-bold text-green-700">{{ $summary['total_employees'] ?? 0 }}</p>
                    </div>
                    <div class="p-4 bg-blue-50 rounded-lg">
                        <p class="text-sm text-gray-600">Total Salaries</p>
                        <p class="text-xl font-bold text-blue-700">{{ formatCurrency($summary['total_salaries'] ?? 0) }}</p>
                    </div>
                    <div class="p-4 bg-red-50 rounded-lg">
                        <p class="text-sm text-gray-600">Total Expenses</p>
                        <p class="text-xl font-bold text-red-700">{{ formatCurrency($summary['total_expenses'] ?? 0) }}</p>
                    </div>
                    <div class="p-4 {{ ($summary['net_profit'] ?? 0) >= 0 ? 'bg-green-50' : 'bg-red-50' }} rounded-lg">
                        <p class="text-sm text-gray-600">Net Profit</p>
                        <p class="text-xl font-bold {{ ($summary['net_profit'] ?? 0) >= 0 ? 'text-green-700' : 'text-red-700' }}">
                            {{ formatCurrency($summary['net_profit'] ?? 0) }}
                        </p>
                    </div>
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

function openAddExpenseModal() {
    document.getElementById('addExpenseModal').classList.remove('hidden');
    lucide.createIcons();
}

function closeAddExpenseModal() {
    document.getElementById('addExpenseModal').classList.add('hidden');
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