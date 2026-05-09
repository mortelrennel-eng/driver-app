@extends('layouts.app')

@section('page-heading', 'Boundary Pricing Rules')
@section('page-subheading', 'Manage automated boundary targets based on vehicle year models.')

@section('content')
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
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bracket Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Year Range</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider text-center">Regular Rate</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider text-center">Sat Disc</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider text-center">Sun Disc</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider text-center">Coding Rate</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($rules as $rule)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        <div class="flex items-center gap-3">
                            <div class="p-1.5 bg-yellow-50 rounded text-yellow-600">
                                <i data-lucide="shield-check" class="w-4 h-4"></i>
                            </div>
                            <span class="font-medium">{{ $rule->name }}</span>
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
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <div class="flex justify-end gap-3 text-yellow-600">
                            <button onclick="openEditRuleModal({{ json_encode($rule) }})" class="hover:text-yellow-900" title="Edit Rule">
                                <i data-lucide="edit" class="w-4 h-4"></i>
                            </button>
                            <form action="{{ route('boundary-rules.destroy', $rule->id) }}" method="POST" onsubmit="return confirm('Archive this pricing rule?');" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-500 hover:text-red-700" title="Delete Rule">
                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
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
<div id="ruleModal" class="fixed inset-0 z-[100] hidden overflow-y-auto">
    <div class="fixed inset-0 bg-black/50 backdrop-blur-sm transition-opacity"></div>
    <div class="flex min-h-screen items-center justify-center p-4">
        <div class="relative w-full max-w-lg transform rounded-2xl bg-white shadow-2xl transition-all">
            <!-- Header -->
            <div class="flex items-center justify-between border-b px-6 py-4">
                <h3 id="ruleModalTitle" class="text-xl font-bold text-gray-900">Add Pricing Bracket</h3>
                <button onclick="closeRuleModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <i data-lucide="x" class="w-6 h-6"></i>
                </button>
            </div>

            <!-- Form -->
            <form id="ruleForm" method="POST" class="p-6 space-y-4">
                @csrf
                <input type="hidden" name="_method" id="ruleFormMethod" value="POST">
                <input type="hidden" id="editRuleId">

                <div class="space-y-1">
                    <label class="block text-sm font-bold text-gray-700 uppercase tracking-wider">Bracket Description</label>
                    <input type="text" name="name" id="ruleName" required placeholder="e.g., Standard Models" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-1">
                        <label class="block text-sm font-bold text-gray-700 uppercase tracking-wider">Start Year</label>
                        <input type="number" name="start_year" id="ruleStartYear" required min="2000" max="2099" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500">
                    </div>
                    <div class="space-y-1">
                        <label class="block text-sm font-bold text-gray-700 uppercase tracking-wider">End Year</label>
                        <input type="number" name="end_year" id="ruleEndYear" required min="2000" max="2099" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500">
                    </div>
                </div>

                <div class="space-y-1">
                    <label class="block text-sm font-bold text-gray-700 uppercase tracking-wider">Regular Daily Rate (₱)</label>
                    <input type="number" name="regular_rate" id="ruleRegularRate" required step="0.01" min="0" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 font-bold">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-1">
                        <label class="block text-sm font-bold text-gray-700 uppercase tracking-wider">Sat Discount (₱)</label>
                        <input type="number" name="sat_discount" id="ruleSatDiscount" required step="0.01" min="0" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500">
                    </div>
                    <div class="space-y-1">
                        <label class="block text-sm font-bold text-gray-700 uppercase tracking-wider">Sun Discount (₱)</label>
                        <input type="number" name="sun_discount" id="ruleSunDiscount" required step="0.01" min="0" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4 pt-2">
                    <div class="space-y-1">
                        <label class="block text-sm font-bold text-gray-700 uppercase tracking-wider">Coding Rate (₱)</label>
                        <input type="number" name="coding_rate" id="ruleCodingRate" required step="0.01" min="0" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                    </div>
                    <div class="space-y-1">
                        <label class="block text-sm font-bold text-gray-700 uppercase tracking-wider">Coding Logic</label>
                        <select name="coding_is_fixed" id="ruleCodingIsFixed" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                            <option value="0">50% Calculation</option>
                            <option value="1">Fixed Amount</option>
                        </select>
                    </div>
                </div>

                <!-- Footer -->
                <div class="flex gap-3 justify-end pt-4 border-t">
                    <button type="button" onclick="closeRuleModal()" class="px-6 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg font-semibold transition-colors">Cancel</button>
                    <button type="submit" class="px-8 py-2 bg-yellow-600 hover:bg-yellow-700 text-white rounded-lg font-bold shadow-md transform transition-all hover:scale-[1.02]">Save Settings</button>
                </div>
            </form>
        </div>
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
    }

    function closeRuleModal() {
        document.getElementById('ruleModal').classList.add('hidden');
    }
</script>
@endpush
@endsection
