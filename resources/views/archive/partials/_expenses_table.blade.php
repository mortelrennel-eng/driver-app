<div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Expense Date</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date Archived</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @forelse($items as $expense)
            <tr class="hover:bg-gray-50 transition-colors">
                <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">{{ $expense->category }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-red-600">{{ formatCurrency($expense->amount) }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $expense->date->format('M d, Y') }}</td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <span class="inline-flex items-center gap-1 text-xs font-medium text-red-600 bg-red-50 px-2 py-1 rounded-full">
                        <i data-lucide="clock" class="w-3 h-3"></i>
                        {{ $expense->deleted_at->format('M d, Y h:i A') }}
                    </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                    <form action="{{ route('archive.restore', ['type' => 'expense', 'id' => $expense->id]) }}" method="POST" class="inline-block">
                        @csrf
                        <button type="submit" class="inline-flex items-center gap-1 text-xs font-bold text-indigo-600 hover:text-indigo-800 bg-indigo-50 hover:bg-indigo-100 px-3 py-1.5 rounded-lg mr-2 transition-all">
                            <i data-lucide="undo-2" class="w-3 h-3"></i> Restore
                        </button>
                    </form>
                    <button type="button"
                        onclick="archiveForceDelete('{{ route('archive.forceDelete', ['type' => 'expense', 'id' => $expense->id]) }}')"
                        class="inline-flex items-center gap-1 text-xs font-bold text-red-600 hover:text-red-800 bg-red-50 hover:bg-red-100 px-3 py-1.5 rounded-lg transition-all">
                        <i data-lucide="trash-2" class="w-3 h-3"></i> Delete Permanently
                    </button>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="px-6 py-16 text-center">
                    <div class="flex flex-col items-center gap-3 text-gray-400">
                        <i data-lucide="receipt" class="w-12 h-12 opacity-30"></i>
                        <p class="text-sm font-medium">No archived expenses found.</p>
                    </div>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
