@extends('layouts.app')

@section('page-heading', 'Boundary Pricing Rules')
@section('page-subheading', 'Manage automated boundary targets based on vehicle year models.')

@section('content')
<style>
    .modern-table-sep {
        border-collapse: separate;
        border-spacing: 0 0.75rem;
    }
    .modern-row {
        background-color: white;
        box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05);
        transition: all 0.2s ease-in-out;
    }
    .modern-row:hover {
        box-shadow: 0 10px 15px -3px rgba(234, 179, 8, 0.2), 0 4px 6px -2px rgba(234, 179, 8, 0.1);
        transform: translateY(-2px);
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
</style>
<div class="space-y-6">
    <!-- Action Bar -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
            <div>
                <h3 class="text-lg font-semibold text-gray-900">Pricing Brackets</h3>
                <p class="text-sm text-gray-500">Configure default rates for different vehicle year ranges.</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('boundaries.index') }}" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-300 flex items-center gap-2 border border-gray-200 transition-all font-semibold shadow-sm">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i>
                    <span>Go to Boundary</span>
                </a>
                <button onclick="openAddRuleModal()" class="px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-yellow-500 flex items-center gap-2 shadow-sm font-bold">
                    <i data-lucide="plus-circle" class="w-4 h-4"></i>
                    <span>Add New Bracket</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Rules Table -->
    <div class="overflow-x-auto">
        <table class="min-w-full modern-table-sep">
            <thead>
                <tr>
                    <th class="px-6 py-1 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Bracket Name</th>
                    <th class="px-6 py-1 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Year Range</th>
                    <th class="px-6 py-1 text-center text-[10px] font-black text-gray-400 uppercase tracking-widest">Regular Rate</th>
                    <th class="px-6 py-1 text-center text-[10px] font-black text-gray-400 uppercase tracking-widest">Sat Disc</th>
                    <th class="px-6 py-1 text-center text-[10px] font-black text-gray-400 uppercase tracking-widest">Sun Disc</th>
                    <th class="px-6 py-1 text-center text-[10px] font-black text-gray-400 uppercase tracking-widest">Coding Rate</th>
                    <th class="px-6 py-1 text-center text-[10px] font-black text-gray-400 uppercase tracking-widest">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rules as $rule)
                <tr class="modern-row group transition-all duration-300">
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        <div class="flex items-center gap-3">
                            <div class="p-1.5 bg-yellow-50 rounded text-yellow-600">
                                <i data-lucide="shield-check" class="w-4 h-4"></i>
                            </div>
                            <span class="font-medium group-hover:text-yellow-700 transition-colors">{{ $rule->name }}</span>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                            {{ $rule->start_year }} - {{ $rule->end_year }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium text-gray-900">₱{{ number_format($rule->regular_rate, 2) }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-red-600">-₱{{ number_format($rule->sat_discount, 2) }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-red-600">-₱{{ number_format($rule->sun_discount, 2) }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-center">
                        <div class="flex flex-col items-center">
                            <span class="text-sm font-medium text-gray-900">₱{{ number_format($rule->coding_rate, 2) }}</span>
                            <span class="text-[10px] uppercase font-bold {{ $rule->coding_is_fixed ? 'text-purple-600' : 'text-gray-400' }}">
                                {{ $rule->coding_is_fixed ? 'Fixed' : '50% Applied' }}
                            </span>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                        <div class="flex items-center justify-center gap-2">
                            <button type="button"
                                class="px-3 py-2 bg-blue-50 text-blue-600 rounded-lg hover:bg-blue-600 hover:text-white transition-all duration-200 flex items-center gap-1.5 font-bold shadow-sm group"
                                onclick="openEditRuleModal({{ json_encode($rule) }})">
                                <i data-lucide="edit-2" class="w-3.5 h-3.5 group-hover:scale-110 transition-transform"></i>
                                <span>Edit</span>
                            </button>
                            
                            <form action="{{ route('boundary-rules.destroy', $rule->id) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to archive this pricing bracket?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="px-3 py-2 bg-red-50 text-red-600 rounded-lg hover:bg-red-600 hover:text-white transition-all duration-200 flex items-center gap-1.5 font-bold shadow-sm group">
                                    <i data-lucide="archive" class="w-3.5 h-3.5 group-hover:scale-110 transition-transform"></i>
                                    <span>Archive</span>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                        <div class="flex flex-col items-center gap-2">
                            <i data-lucide="alert-circle" class="w-12 h-12 text-gray-300"></i>
                            <p class="font-medium text-lg">No Pricing Rules Found</p>
                            <p class="text-sm">Add your first year bracket to start automating boundaries.</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Add/Edit Modal -->
<div id="ruleModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm hidden z-[100] flex items-center justify-center p-4 transition-all">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[95vh] flex flex-col overflow-hidden">
        <!-- Header (Deep Navy) -->
        <div class="bg-slate-800 p-5 shrink-0">
            <div class="flex justify-between items-start">
                <div class="flex items-center gap-3">
                    <div class="p-2.5 bg-white/10 rounded-xl">
                        <i data-lucide="settings-2" class="w-6 h-6 text-yellow-500"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-black text-white tracking-wide" id="ruleModalTitle">Add Pricing Bracket</h3>
                        <p class="text-xs font-medium text-slate-300 mt-0.5">Configure default rates for different vehicle year ranges.</p>
                    </div>
                </div>
                <button onclick="closeRuleModal()" type="button" class="text-slate-400 hover:text-white bg-slate-700/50 hover:bg-slate-700 p-2 rounded-full transition-colors">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
        </div>

        <!-- Form -->
        <form id="ruleForm" method="POST" class="flex flex-col flex-1 min-h-0">
            <div class="p-6 overflow-y-auto flex-1 space-y-5">
                @csrf
                <input type="hidden" name="_method" id="ruleFormMethod" value="POST">
                <input type="hidden" id="editRuleId">

                <!-- Real-time Error Messages -->
                <div id="ruleErrorContainer" class="hidden px-4 py-3 bg-red-50 border border-red-200 rounded-xl mb-4">
                    <p class="text-xs font-black text-red-600 uppercase tracking-widest mb-1">Please fix the following:</p>
                    <ul id="ruleErrorList" class="text-xs text-red-700 font-bold list-disc list-inside space-y-0.5">
                    </ul>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-1.5">Bracket Description <span class="text-red-500">*</span></label>
                    <input type="text" name="name" id="ruleName" required maxlength="30" placeholder="e.g., Standard Models" class="w-full px-3 py-2.5 border border-gray-300 rounded-xl bg-white focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 text-sm font-bold shadow-sm">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-1.5">Start Year <span class="text-red-500">*</span></label>
                        <input type="text" name="start_year" id="ruleStartYear" required 
                               oninput="this.value = this.value.replace(/[^0-9]/g, '').replace(/^0+/, '').slice(0, 4)"
                               placeholder="YYYY"
                               class="w-full px-3 py-2.5 border border-gray-300 rounded-xl bg-white focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 text-sm font-bold shadow-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-1.5">End Year <span class="text-red-500">*</span></label>
                        <input type="text" name="end_year" id="ruleEndYear" required 
                               oninput="this.value = this.value.replace(/[^0-9]/g, '').replace(/^0+/, '').slice(0, 4)"
                               placeholder="YYYY"
                               class="w-full px-3 py-2.5 border border-gray-300 rounded-xl bg-white focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 text-sm font-bold shadow-sm">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-1.5">Regular Daily Rate <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                            <span class="text-gray-500 font-black">₱</span>
                        </div>
                        <input type="text" name="regular_rate" id="ruleRegularRate" required 
                               oninput="this.value = this.value.replace(/[^0-9]/g, '').replace(/^0+/, '').slice(0, 4)"
                               placeholder="0000"
                               class="w-full pl-8 px-3 py-2.5 border border-gray-300 rounded-xl bg-white focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 text-base font-black text-gray-900 shadow-sm">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-xs font-bold text-red-600 uppercase tracking-widest mb-1.5">Sat Discount <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                                <span class="text-red-500 font-black">₱</span>
                            </div>
                            <input type="text" name="sat_discount" id="ruleSatDiscount" required 
                                   oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 4)"
                                   placeholder="0000"
                                   class="w-full pl-8 px-3 py-2.5 border border-red-200 rounded-xl bg-white focus:ring-2 focus:ring-red-500 focus:border-red-500 text-base font-black text-red-700 shadow-sm">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-red-600 uppercase tracking-widest mb-1.5">Sun Discount <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                                <span class="text-red-500 font-black">₱</span>
                            </div>
                            <input type="text" name="sun_discount" id="ruleSunDiscount" required 
                                   oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 4)"
                                   placeholder="0000"
                                   class="w-full pl-8 px-3 py-2.5 border border-red-200 rounded-xl bg-white focus:ring-2 focus:ring-red-500 focus:border-red-500 text-base font-black text-red-700 shadow-sm">
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div id="codingRateGroup">
                        <label class="block text-xs font-bold text-purple-600 uppercase tracking-widest mb-1.5">Coding Rate <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                                <span class="text-purple-500 font-black">₱</span>
                            </div>
                            <input type="text" name="coding_rate" id="ruleCodingRate" required 
                                   oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 4)"
                                   placeholder="0000"
                                   class="w-full pl-8 px-3 py-2.5 border border-purple-200 rounded-xl bg-white focus:ring-2 focus:ring-purple-500 focus:border-purple-500 text-base font-black text-purple-700 shadow-sm">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-purple-600 uppercase tracking-widest mb-1.5">Coding Logic <span class="text-red-500">*</span></label>
                        <select name="coding_is_fixed" id="ruleCodingIsFixed" class="w-full px-3 py-2.5 border border-purple-200 rounded-xl bg-white focus:ring-2 focus:ring-purple-500 focus:border-purple-500 text-sm font-bold text-purple-900 shadow-sm">
                            <option value="0">50% Calculation</option>
                            <option value="1">Fixed Amount</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="bg-gray-50 px-6 py-4 border-t border-gray-200 flex justify-end gap-3 shrink-0">
                <button type="button" onclick="closeRuleModal()" class="px-5 py-2.5 text-sm font-bold text-gray-600 bg-white border border-gray-300 rounded-xl hover:bg-gray-50 focus:ring-4 focus:ring-gray-100 transition-all shadow-sm">
                    Cancel
                </button>
                <button type="submit" id="saveRuleBtn" class="px-6 py-2.5 text-sm font-black text-white bg-yellow-500 rounded-xl hover:bg-yellow-400 focus:ring-4 focus:ring-yellow-100 transition-all shadow-sm disabled:opacity-50 disabled:cursor-not-allowed">
                    Save Settings
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function openAddRuleModal() {
        document.getElementById('ruleModalTitle').textContent = 'Add Pricing Bracket';
        document.getElementById('ruleFormMethod').value = 'POST';
        document.getElementById('ruleForm').action = '{{ route('boundary-rules.store') }}';
        document.getElementById('ruleForm').reset();
        document.getElementById('ruleModal').classList.remove('hidden');
        toggleCodingRateField();
        validateInputs(); // Initial check
    }

    function openEditRuleModal(rule) {
        document.getElementById('ruleModalTitle').textContent = 'Edit Pricing Bracket';
        document.getElementById('ruleFormMethod').value = 'PUT';
        document.getElementById('ruleForm').action = '/boundary-rules/' + rule.id;
        
        document.getElementById('ruleName').value = rule.name;
        document.getElementById('ruleStartYear').value = rule.start_year;
        document.getElementById('ruleEndYear').value = rule.end_year;
        document.getElementById('ruleRegularRate').value = rule.regular_rate;
        document.getElementById('ruleSatDiscount').value = rule.sat_discount;
        document.getElementById('ruleSunDiscount').value = rule.sun_discount;
        document.getElementById('ruleCodingRate').value = rule.coding_rate;
        document.getElementById('ruleCodingIsFixed').value = rule.coding_is_fixed ? '1' : '0';
        
        document.getElementById('ruleModal').classList.remove('hidden');
        toggleCodingRateField();
        validateInputs(); // Initial check
    }

    function closeRuleModal() {
        document.getElementById('ruleModal').classList.add('hidden');
    }

    function validateInputs() {
        const name = document.getElementById('ruleName').value.trim();
        const startYearStr = document.getElementById('ruleStartYear').value;
        const endYearStr = document.getElementById('ruleEndYear').value;
        const regularRateStr = document.getElementById('ruleRegularRate').value;
        const codingRateStr = document.getElementById('ruleCodingRate').value;
        const codingIsFixed = document.getElementById('ruleCodingIsFixed').value === '1';
        const saveBtn = document.getElementById('saveRuleBtn');
        const errorContainer = document.getElementById('ruleErrorContainer');
        const errorList = document.getElementById('ruleErrorList');

        let isValid = true;
        let errors = [];

        // Reset borders
        document.getElementById('ruleStartYear').classList.remove('border-red-500', 'ring-1', 'ring-red-500');
        document.getElementById('ruleEndYear').classList.remove('border-red-500', 'ring-1', 'ring-red-500');
        document.getElementById('ruleRegularRate').classList.remove('border-red-500', 'ring-1', 'ring-red-500');
        document.getElementById('ruleCodingRate').classList.remove('border-red-500', 'ring-1', 'ring-red-500');

        // Check Description
        if (name.length === 0) {
            isValid = false;
        }

        // Check Years
        const sy = parseInt(startYearStr) || 0;
        const ey = parseInt(endYearStr) || 0;

        if (startYearStr.length > 0) {
            if (sy < 2000) {
                errors.push('The start year must be at least 2000.');
                document.getElementById('ruleStartYear').classList.add('border-red-500', 'ring-1', 'ring-red-500');
                isValid = false;
            }
        } else {
            isValid = false;
        }

        if (endYearStr.length > 0) {
            if (ey < 2000) {
                errors.push('The end year must be at least 2000.');
                document.getElementById('ruleEndYear').classList.add('border-red-500', 'ring-1', 'ring-red-500');
                isValid = false;
            } else if (ey < sy) {
                errors.push('The end year must be greater than or equal to ' + sy + '.');
                document.getElementById('ruleEndYear').classList.add('border-red-500', 'ring-1', 'ring-red-500');
                isValid = false;
            }
        } else {
            isValid = false;
        }

        // Check Regular Rate
        const rr = parseFloat(regularRateStr) || 0;
        if (regularRateStr.length > 0) {
            if (rr <= 0) {
                errors.push('Regular Daily Rate must be greater than 0.');
                document.getElementById('ruleRegularRate').classList.add('border-red-500', 'ring-1', 'ring-red-500');
                isValid = false;
            }
        } else {
            isValid = false;
        }

        // Check Coding Rate only if Fixed Amount is selected
        if (codingIsFixed) {
            const cr = parseFloat(codingRateStr) || 0;
            if (codingRateStr.length > 0) {
                if (cr <= 0) {
                    errors.push('Coding Rate must be greater than 0 if Fixed Amount is selected.');
                    document.getElementById('ruleCodingRate').classList.add('border-red-500', 'ring-1', 'ring-red-500');
                    isValid = false;
                }
            } else {
                isValid = false;
            }
        }

        // Display Errors
        if (errors.length > 0) {
            errorList.innerHTML = errors.map(err => `<li>${err}</li>`).join('');
            errorContainer.classList.remove('hidden');
        } else {
            errorContainer.classList.add('hidden');
            errorList.innerHTML = '';
        }

        saveBtn.disabled = !isValid;
    }

    function toggleCodingRateField() {
        const codingIsFixed = document.getElementById('ruleCodingIsFixed').value === '1';
        const codingRateGroup = document.getElementById('codingRateGroup');
        const codingRateInput = document.getElementById('ruleCodingRate');
        
        if (codingIsFixed) {
            codingRateGroup.classList.remove('hidden');
            codingRateInput.setAttribute('required', 'required');
        } else {
            codingRateGroup.classList.add('hidden');
            codingRateInput.removeAttribute('required');
            codingRateInput.value = '0'; // Set to 0 if not used
        }
        validateInputs();
    }

    // Attach listeners for real-time validation
    ['ruleName', 'ruleStartYear', 'ruleEndYear', 'ruleRegularRate', 'ruleCodingRate'].forEach(id => {
        document.getElementById(id).addEventListener('input', validateInputs);
    });

    document.getElementById('ruleCodingIsFixed').addEventListener('change', toggleCodingRateField);


    // Add JS validation for whitespace and extra checks on submit
    document.getElementById('ruleForm').addEventListener('submit', function(e) {
        if (document.getElementById('saveRuleBtn').disabled) {
            e.preventDefault();
            return false;
        }
        return true;
    });
</script>
@endpush
@endsection
