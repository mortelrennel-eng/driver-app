<div id="expensesTableContainer" class="bg-white rounded-lg shadow overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Category</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Details</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Reference</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($expenses as $expense)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ \Carbon\Carbon::parse($expense->date)->format('M d, Y') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @php
                                $cat = $expense->category;
                                
                                $colors = [
                                    // Utilities
                                    'Electricity (Meralco)' => 'bg-blue-50 text-blue-700 border-blue-200',
                                    'Water (Maynilad)' => 'bg-cyan-50 text-cyan-700 border-cyan-200',
                                    'Internet & WiFi' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
                                    'Communications' => 'bg-sky-50 text-sky-700 border-sky-200',
                                    // Supplies
                                    'Office Supplies' => 'bg-purple-50 text-purple-700 border-purple-200',
                                    'Pantry & Cleaning' => 'bg-pink-50 text-pink-700 border-pink-200',
                                    // Facility
                                    'Building Repairs' => 'bg-orange-50 text-orange-700 border-orange-200',
                                    'Construction Materials' => 'bg-amber-50 text-amber-700 border-amber-200',
                                    'Office Equipment' => 'bg-yellow-50 text-yellow-700 border-yellow-200',
                                    // Fleet / Inventory
                                    'Spare Parts Purchase' => 'bg-amber-100 text-amber-800 border-amber-300',
                                    'Tires & Batteries' => 'bg-red-50 text-red-700 border-red-200',
                                    'Oil & Lubricants' => 'bg-orange-100 text-orange-800 border-orange-300',
                                    // Admin
                                    'Govt Permits & Fees' => 'bg-teal-50 text-teal-700 border-teal-200',
                                    'LTO & Registration' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                    'Insurance' => 'bg-blue-50 text-blue-700 border-blue-200',
                                    'Franchise Renewal' => 'bg-emerald-100 text-emerald-800 border-emerald-300',
                                    'Staff Meals & Incentives' => 'bg-violet-50 text-violet-700 border-violet-200',
                                    'Petty Cash' => 'bg-green-50 text-green-700 border-green-200',
                                    'maintenance' => 'bg-rose-50 text-rose-700 border-rose-200',
                                ];
                                $cls = $colors[$cat] ?? 'bg-gray-50 text-gray-700 border-gray-200';
                            @endphp
                            <span class="px-2 py-1 {{ $cls }} border rounded text-[9px] uppercase tracking-tight font-black">
                                {{ $cat }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900 w-[35%] min-w-[280px]">
                            <div class="font-bold text-gray-800 leading-relaxed whitespace-pre-wrap">{{ $expense->description }}</div>
                            @if($expense->vendor_name)
                                <div class="inline-flex items-center gap-1.5 px-2 py-0.5 bg-blue-50 text-blue-700 rounded-md text-[10px] font-black uppercase mt-2 border border-blue-100 shadow-sm">
                                    <i data-lucide="building-2" class="w-3 h-3"></i>
                                    <span>Vendor: {{ $expense->vendor_name }}</span>
                                </div>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $expense->reference_number ?? '-' }}
                            <div class="flex items-center gap-2 mt-1">
                                <div class="text-[9px] text-gray-400">
                                    <span title="Input by {{ $expense->creator_name ?? 'System' }}">By: {{ $expense->creator_name ?? 'System' }}</span>
                                </div>
                                @if($expense->payment_method)
                                    <span class="px-1.5 py-0.5 bg-gray-100 text-gray-500 rounded text-[8px] font-black uppercase">{{ $expense->payment_method }}</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-red-600">
                            {{ formatCurrency($expense->amount) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex items-center gap-2">
                                <button type="button" onclick="openEditExpenseModal({{ $expense->id }})" class="text-blue-600 hover:text-blue-900">
                                    <i data-lucide="edit" class="w-4 h-4"></i>
                                </button>
                                <form method="POST" action="{{ route('office-expenses.destroy', $expense->id) }}" class="inline"
                                    onsubmit="return confirm('Archive this expense?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-rose-500 hover:text-rose-700 transition-colors">
                                        <i data-lucide="archive" class="w-4 h-4"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                            <i data-lucide="philippine-peso" class="w-12 h-12 mx-auto mb-4 text-gray-300"></i>
                            <p>No expenses recorded yet.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if(isset($expenses) && method_exists($expenses, 'links'))
    <div class="px-6 py-4 border-t office-expenses-pagination">
        {{ $expenses->withQueryString()->links() }}
    </div>
    @endif
</div>
