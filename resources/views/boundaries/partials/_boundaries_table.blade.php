<div id="boundariesTableContainer" class="bg-white rounded-lg shadow-lg overflow-hidden border border-gray-100">
    <div class="overflow-x-auto overflow-y-visible">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50/50">
                <tr>
                    <th class="px-4 py-3 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Date</th>
                    <th class="px-4 py-3 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Unit</th>
                    <th class="px-4 py-3 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Driver</th>
                    <th class="px-4 py-3 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Target</th>
                    <th class="px-4 py-3 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Actual</th>
                    <th class="px-4 py-3 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Status</th>
                    <th class="px-4 py-3 text-right text-[10px] font-black text-gray-400 uppercase tracking-widest">Action</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-100">
                @if (empty($boundaries))
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center text-gray-400 font-bold uppercase tracking-widest italic">No boundary records found.</td>
                    </tr>
                @else
                    @foreach ($boundaries as $boundary)
                        <tr class="hover:bg-yellow-50/50 transition-colors cursor-pointer group" onclick="openViewBoundary({{ $boundary['id'] }})">
                            <td class="px-4 py-3 whitespace-nowrap">
                                <div class="text-[12px] font-black text-gray-900">{{ \Carbon\Carbon::parse($boundary['date'])->format('M d, Y') }}</div>
                                <div class="text-[9px] text-gray-400 font-bold uppercase tracking-tighter">{{ \Carbon\Carbon::parse($boundary['created_at'])->format('h:i A') }}</div>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <div class="flex items-center gap-1">
                                    <span class="text-[12px] font-black text-gray-900 group-hover:text-yellow-700 transition-colors uppercase">{{ $boundary['plate_number'] }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <div class="text-[12px] font-bold text-gray-900 leading-tight">{{ $boundary['driver_name'] ?? 'Unassigned' }}
                                    @if(!empty($boundary['is_extra_driver']))
                                        <span class="ml-1 px-1 py-0.5 bg-orange-100 text-orange-700 text-[8px] font-black rounded border border-orange-200 uppercase tracking-tighter">Extra</span>
                                    @endif
                                </div>
                                <div class="text-[9px] text-gray-400 mt-0.5 font-bold uppercase tracking-tighter flex gap-2">
                                    <span title="Input by {{ $boundary['creator_name'] ?? 'System' }}">In: {{ explode(' ', $boundary['creator_name'] ?? 'System')[0] }}</span>
                                    @if(isset($boundary['editor_name']) && $boundary['editor_name'])
                                        <span class="text-gray-300">|</span>
                                        <span title="Last edit by {{ $boundary['editor_name'] }}">Ed: {{ explode(' ', $boundary['editor_name'])[0] }}</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <div class="flex flex-col">
                                    <span class="text-[12px] text-gray-900 font-black">{{ formatCurrency($boundary['boundary_amount']) }}</span>
                                    @if(isset($boundary['rate_label']) && ($boundary['rate_type'] ?? 'regular') !== 'regular')
                                        <span class="text-[8px] font-black uppercase tracking-tighter px-1 rounded-[2px] mt-0.5 w-fit
                                            @if($boundary['rate_type'] === 'coding') bg-red-100 text-red-600 border border-red-200
                                            @elseif($boundary['rate_type'] === 'discount') bg-blue-100 text-blue-600 border border-blue-200
                                            @else bg-gray-100 text-gray-500 @endif">
                                            {{ $boundary['rate_label'] }}
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-[12px] text-gray-900 font-black">
                                {{ formatCurrency($boundary['actual_boundary'] ?? 0) }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                @php
                                    $statusClass = 'bg-yellow-100 text-yellow-800 border-yellow-200';
                                    if ($boundary['status'] === 'paid') $statusClass = 'bg-green-100 text-green-800 border-green-200';
                                    if ($boundary['status'] === 'shortage') $statusClass = 'bg-red-100 text-red-800 border-red-200';
                                    if ($boundary['status'] === 'excess') $statusClass = 'bg-blue-100 text-blue-800 border-blue-200';
                                @endphp
                                <div class="flex flex-col gap-0.5">
                                    <span class="px-1.5 py-0.5 inline-flex text-[9px] leading-none font-black rounded border w-fit uppercase tracking-tighter {{ $statusClass }}">
                                        {{ $boundary['status'] }}
                                    </span>
                                    @if (isset($boundary['has_incentive']))
                                        @if ($boundary['has_incentive'])
                                            <span class="px-1.5 py-0.5 bg-green-50 text-green-600 text-[8px] font-black rounded border border-green-100 uppercase tracking-tighter w-fit">Incentive ✅</span>
                                        @else
                                            @php
                                                $notes_lc = strtolower($boundary['notes'] ?? '');
                                                $is_damage_case = str_contains($notes_lc, 'vehicle damaged') || str_contains($notes_lc, 'maintenance') || str_contains($notes_lc, 'breakdown');
                                            @endphp
                                            <span class="px-1.5 py-0.5 bg-red-50 text-red-600 text-[8px] font-black rounded border border-red-100 uppercase tracking-tighter w-fit">
                                                {{ $is_damage_case ? 'Damaged/B-Down' : 'Late Turn ⏰' }}
                                            </span>
                                        @endif
                                    @endif
                                    @if ($boundary['shortage'] > 0)
                                        <div class="text-[9px] font-black text-red-600 tracking-tighter">-{{ formatCurrency($boundary['shortage']) }}</div>
                                    @elseif ($boundary['excess'] > 0)
                                        <div class="text-[9px] font-black text-blue-600 tracking-tighter">+{{ formatCurrency($boundary['excess']) }}</div>
                                    @endif
                                </div>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-right" onclick="event.stopPropagation()">
                                <button
                                    type="button"
                                    onclick="editBoundary({{ $boundary['id'] }})"
                                    class="p-1.5 text-gray-400 hover:text-yellow-600 hover:bg-yellow-50 rounded-lg transition"
                                    title="Edit Boundary"
                                >
                                    <i data-lucide="edit-3" class="w-4 h-4"></i>
                                </button>
                            </td>
                        </tr>
                    @endforeach
                @endif
            </tbody>
        </table>
    </div>
    
    <!-- Pagination -->
    @if ($pagination['total_pages'] > 1)
        <div class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6 boundaries-pagination">
            <div class="flex-1 flex justify-between sm:hidden">
                @if ($pagination['has_prev'])
                    <a href="?page={{ $pagination['prev_page'] }}&search={{ urlencode($search) }}&date={{ urlencode($date_filter) }}&status={{ urlencode($status_filter) }}" class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">Previous</a>
                @endif
                @if ($pagination['has_next'])
                    <a href="?page={{ $pagination['next_page'] }}&search={{ urlencode($search) }}&date={{ urlencode($date_filter) }}&status={{ urlencode($status_filter) }}" class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">Next</a>
                @endif
            </div>
            <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm text-gray-700">
                        Showing <span class="font-medium">{{ $pagination['offset'] + 1 }}</span> to 
                        <span class="font-medium">{{ min($pagination['offset'] + $pagination['items_per_page'], $pagination['total_items']) }}</span> of 
                        <span class="font-medium">{{ $pagination['total_items'] }}</span> results
                    </p>
                </div>
                <div>
                    <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                        @if ($pagination['has_prev'])
                            <a href="?page={{ $pagination['prev_page'] }}&search={{ urlencode($search) }}&date={{ urlencode($date_filter) }}&status={{ urlencode($status_filter) }}" class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                <i data-lucide="chevron-left" class="w-4 h-4"></i>
                            </a>
                        @endif
                        
                        @php
                        $start_page = max(1, $pagination['page'] - 2);
                        $end_page = min($pagination['total_pages'], $pagination['page'] + 2);
                        @endphp
                        
                        @for ($i = $start_page; $i <= $end_page; $i++)
                            <a href="?page={{ $i }}&search={{ urlencode($search) }}&date={{ urlencode($date_filter) }}&status={{ urlencode($status_filter) }}" class="relative inline-flex items-center px-4 py-2 border text-sm font-medium {{ $i === $pagination['page'] ? 'z-10 bg-yellow-50 border-yellow-500 text-yellow-600' : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50' }}">
                                {{ $i }}
                            </a>
                        @endfor
                        
                        @if ($pagination['has_next'])
                            <a href="?page={{ $pagination['next_page'] }}&search={{ urlencode($search) }}&date={{ urlencode($date_filter) }}&status={{ urlencode($status_filter) }}" class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                <i data-lucide="chevron-right" class="w-4 h-4"></i>
                            </a>
                        @endif
                    </nav>
                </div>
            </div>
        </div>
    @endif
</div>
