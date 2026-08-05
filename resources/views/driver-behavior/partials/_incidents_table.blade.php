    {{-- Incident Table --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-50">
                <thead class="bg-gray-50/80">
                    <tr>
                        <th class="px-5 py-3.5 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Date / Time</th>
                        <th class="px-5 py-3.5 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Driver</th>
                        <th class="px-5 py-3.5 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Unit</th>
                        <th class="px-5 py-3.5 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Incident Description & Charges</th>
                        <th class="px-5 py-3.5 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Incentive Status</th>
                        <th class="px-5 py-3.5 text-right text-[10px] font-black text-gray-400 uppercase tracking-widest">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @php
                        $classificationsMapForUI = collect($classifications)->keyBy('name');
                    @endphp
                    @forelse($incidents as $inc)
                    @php
                        $sevColors = [
                            'critical' => 'bg-red-100 text-red-700 border-red-200',
                            'high'     => 'bg-orange-100 text-orange-700 border-orange-200',
                            'medium'   => 'bg-yellow-100 text-yellow-700 border-yellow-200',
                            'low'      => 'bg-blue-100 text-blue-700 border-blue-200',
                        ];
                        $typeColors = [
                            
                            'Late Boundary'       => 'bg-orange-100 text-orange-700 border-orange-200',
                            'Short Boundary'      => 'bg-yellow-100 text-yellow-700 border-yellow-200',
                            'Vehicle Damage'      => 'bg-purple-100 text-purple-700 border-purple-200',
                            'Accident'            => 'bg-red-100 text-red-700 border-red-200',
                            'Traffic Violation'   => 'bg-orange-100 text-orange-700 border-orange-200',
                            'Absent / No Show'    => 'bg-gray-100 text-gray-600 border-gray-200',
                            'Passenger Complaint' => 'bg-blue-100 text-blue-700 border-blue-200',
                        ];
                        $tc  = $typeColors[$inc->incident_type] ?? 'bg-gray-100 text-gray-600 border-gray-200';
                        $sc  = $sevColors[$inc->severity] ?? 'bg-gray-100 text-gray-600 border-gray-200';
                        $isAccident = in_array($inc->incident_type, ['Accident','Vehicle Damage']);
                    @endphp
                    <tr class="hover:bg-gray-50/50 transition-colors group">
                        <td class="px-5 py-3.5 whitespace-nowrap">
                            <div class="text-xs font-bold text-gray-800">{{ \Carbon\Carbon::parse($inc->incident_date)->timezone('Asia/Manila')->format('M d, Y') }}</div>
                            <div class="text-[10px] text-gray-400 font-medium">{{ \Carbon\Carbon::parse($inc->timestamp)->timezone('Asia/Manila')->format('h:i A') }}</div>
                        </td>
                        <td class="px-5 py-3.5 whitespace-nowrap">
                            <div class="text-xs font-bold text-gray-800">{{ !empty(trim($inc->driver_name)) ? $inc->driver_name : '—' }}</div>
                        </td>
                        <td class="px-5 py-3.5 whitespace-nowrap">
                            <span class="text-xs font-black text-blue-600 uppercase">{{ !empty(trim($inc->plate_number)) ? $inc->plate_number : '—' }}</span>
                        </td>
                        <td class="px-5 py-3.5 max-w-[450px]">
                            {{-- Incident Type & Severity Badges --}}
                            <div class="flex items-center gap-1.5 mb-2">
                                <span class="px-2 py-0.5 {{ $tc }} text-[10px] font-black uppercase tracking-widest rounded-md shadow-sm">
                                    {{ $inc->incident_type }}
                                </span>
                                <span class="px-2 py-0.5 {{ $sc }} text-[9px] font-black uppercase tracking-widest rounded-md shadow-sm">
                                    {{ $inc->severity }}
                                </span>
                            </div>

                            {{-- Unified Tags Row --}}
                            <div class="flex flex-wrap gap-1.5 mb-2">
                                {{-- Driver Fault Status --}}
                                @if($inc->is_driver_fault)
                                    <span class="px-2 py-0.5 bg-red-500 text-white text-[9px] font-black uppercase tracking-widest rounded-full shadow-sm shadow-red-100">Driver at Fault</span>
                                @else
                                    @php
                                        $incClass = $classificationsMapForUI[$inc->incident_type] ?? null;
                                        $showNotAtFault = $incClass ? $incClass->show_not_at_fault : false;
                                    @endphp
                                    @if($showNotAtFault)
                                        <span class="px-2 py-0.5 bg-blue-500 text-white text-[9px] font-black uppercase tracking-widest rounded-full shadow-sm shadow-blue-100">Not at Fault</span>
                                    @endif
                                @endif

                                {{-- Charge Info --}}
                                @if($inc->total_charge_to_driver > 0)
                                    <span class="px-2 py-0.5 bg-purple-600 text-white text-[9px] font-black uppercase tracking-widest rounded-full shadow-sm shadow-purple-100">
                                        Amount: ₱{{ number_format($inc->total_charge_to_driver, 2) }}
                                    </span>
                                @endif
                            </div>

                            {{-- Cause --}}
                            @if($inc->cause_of_incident)
                                <div class="mb-1.5">
                                    <span class="text-[9px] font-black text-orange-600 bg-orange-50 px-1.5 py-0.5 rounded-full border border-orange-100 uppercase tracking-widest">Cause: {{ $inc->cause_of_incident }}</span>
                                </div>
                            @endif

                            <p class="text-xs text-gray-800 font-medium leading-relaxed">{{ $inc->description }}</p>
                        </td>

                        <td class="px-5 py-3.5 whitespace-nowrap">
                            @if(in_array($inc->severity, ['high','critical']) || $inc->is_driver_fault)
                                <div class="text-[10px] font-black text-red-500 uppercase tracking-widest leading-tight">VOID</div>
                                <div class="text-[8px] text-gray-400 font-medium uppercase">Performance Impacted</div>
                            @else
                                <div class="text-[10px] font-black text-green-500 uppercase tracking-widest leading-tight">ELIGIBLE</div>
                                <div class="text-[8px] text-gray-400 font-medium uppercase">Active Cycle</div>
                            @endif
                        <td class="px-5 py-3.5 whitespace-nowrap text-right">
                            <div class="flex justify-end items-center gap-2">
                                {{-- Edit Button --}}
                                <button type="button" 
                                    onclick="IncidentManager.openEdit({{ $inc->id }})"
                                    class="p-2 text-gray-400 hover:text-blue-500 hover:bg-blue-50 rounded-xl transition-all duration-300 group/edit cursor-pointer" 
                                    title="Edit Incident">
                                    <i data-lucide="edit-3" class="w-4 h-4 group-hover/edit:scale-110 transition-transform"></i>
                                </button>
                                {{-- Archive Button --}}
                                <button type="button" 
                                    onclick="IncidentManager.archive({{ $inc->id }})"
                                    class="p-2 text-gray-400 hover:text-orange-500 hover:bg-orange-50 rounded-xl transition-all duration-300 group/delete cursor-pointer" 
                                    title="Archive Record">
                                    <i data-lucide="archive" class="w-4 h-4 group-hover/delete:scale-110 pointer-events-none"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="9" class="px-5 py-16 text-center">
                        <div class="w-16 h-16 bg-green-50 rounded-full flex items-center justify-center mx-auto mb-3 border border-green-100">
                            <i data-lucide="shield-check" class="w-8 h-8 text-green-500"></i>
                        </div>
                        <p class="text-sm font-black text-gray-400 uppercase tracking-widest">No incidents found</p>
                        <p class="text-xs text-gray-300 mt-1">All drivers are performing well</p>
                    </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{-- Pagination --}}
        @if($pagination['total_pages'] > 1)
        <div class="px-5 py-4 border-t border-gray-50 flex items-center justify-between">
            <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between font-bold text-[10px] text-gray-400 uppercase tracking-widest">
                <div>
                    <p>Showing <span class="text-gray-900">{{ min($pagination['total_items'], ($pagination['page'] - 1) * 10 + 1) }}</span> to <span class="text-gray-900">{{ min($pagination['total_items'], $pagination['page'] * 10) }}</span> of <span class="text-gray-900">{{ $pagination['total_items'] }}</span> incidents</p>
                </div>
                <div>
                    <nav class="relative z-0 inline-flex rounded-xl shadow-sm -space-x-px" aria-label="Pagination">
                        @if($pagination['has_prev'])
                            <a href="{{ request()->fullUrlWithQuery(['page' => $pagination['prev_page']]) }}" class="relative inline-flex items-center px-2 py-2 rounded-l-xl border border-gray-200 bg-white text-gray-400 hover:bg-gray-50">
                                <i data-lucide="chevron-left" class="w-4 h-4"></i>
                            </a>
                        @endif

                        @php
                            $start = max(1, $pagination['page'] - 2);
                            $end = min($pagination['total_pages'], $pagination['page'] + 2);
                        @endphp

                        @for($i = $start; $i <= $end; $i++)
                            <a href="{{ request()->fullUrlWithQuery(['page' => $i]) }}" 
                               class="relative inline-flex items-center px-4 py-2 border text-[11px] font-black {{ $i === $pagination['page'] ? 'z-10 bg-yellow-500 border-yellow-500 text-white shadow-lg shadow-yellow-500/20' : 'bg-white border-gray-200 text-gray-500 hover:bg-gray-50' }}">
                                {{ $i }}
                            </a>
                        @endfor

                        @if($pagination['has_next'])
                            <a href="{{ request()->fullUrlWithQuery(['page' => $pagination['next_page']]) }}" class="relative inline-flex items-center px-2 py-2 rounded-r-xl border border-gray-200 bg-white text-gray-400 hover:bg-gray-50">
                                <i data-lucide="chevron-right" class="w-4 h-4"></i>
                            </a>
                        @endif
                    </nav>
                </div>
            </div>
            {{-- Mobile simple pagination --}}
            <div class="flex-1 flex justify-between sm:hidden">
                @if($pagination['has_prev'])
                    <a href="{{ request()->fullUrlWithQuery(['page' => $pagination['prev_page']]) }}" class="relative inline-flex items-center px-4 py-2 border border-gray-200 text-xs font-bold rounded-xl text-gray-700 bg-white hover:bg-gray-50">Previous</a>
                @endif
                @if($pagination['has_next'])
                    <a href="{{ request()->fullUrlWithQuery(['page' => $pagination['next_page']]) }}" class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-200 text-xs font-bold rounded-xl text-gray-700 bg-white hover:bg-gray-50">Next</a>
                @endif
            </div>
        </div>
        @endif
    </div>
</div>
