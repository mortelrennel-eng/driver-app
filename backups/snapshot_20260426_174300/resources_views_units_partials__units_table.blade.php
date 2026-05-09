{{-- ═══════════════════════════════════════════════════════════════
     UNIT MANAGEMENT — PRECISE TABLE FORMAT
     Matching the user-provided screenshot aesthetic.
     ═══════════════════════════════════════════════════════════════ --}}

<div class="overflow-x-auto bg-white">
    <table class="min-w-full divide-y divide-gray-100">
        <thead class="bg-gray-50/50 border-b border-gray-100">
            <tr>
                <th class="px-6 py-4 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Plate Number Info</th>
                <th class="px-6 py-4 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Vehicle Details</th>
                <th class="px-6 py-4 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Assigned Drivers</th>
                <th class="px-6 py-4 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Status</th>
                <th class="px-6 py-4 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Boundary Rate</th>
                <th class="px-6 py-4 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
            @forelse($units as $unit)
                @php
                    $primary_driver = $unit->primary_driver ?? null;
                    $secondary_driver = $unit->secondary_driver ?? null;
                    
                    $dotClass = match($unit->status) {
                        'active'       => 'bg-green-500',
                        'maintenance'  => 'bg-red-500',
                        'coding'       => 'bg-yellow-500',
                        'surveillance' => 'bg-orange-500',
                        'vacant', 'available' => 'bg-gray-400',
                        default        => 'bg-gray-400',
                    };
                    $statusColor = match($unit->status) {
                        'active'       => 'text-green-600',
                        'maintenance'  => 'text-red-600',
                        'coding'       => 'text-yellow-600',
                        'surveillance' => 'text-orange-600',
                        default        => 'text-gray-500',
                    };
                    
                    // Maintenance check for the sub-row bar
                    $has_maintenance_data = (int)($unit->gps_device_count ?? 0) > 0 || !empty($unit->imei);
                @endphp
                
                {{-- Main Data Row --}}
                <tr class="hover:bg-blue-50/30 transition-colors cursor-pointer group" onclick="viewUnitDetails({{ $unit->id }})">
                    {{-- Plate Number Info --}}
                    <td class="px-6 py-5 whitespace-nowrap">
                        <div class="flex flex-col">
                            <span class="text-sm font-black text-gray-900 tracking-tight">{{ $unit->plate_number }}</span>
                            <div class="mt-1 flex flex-col gap-0.5">
                                <span class="text-[9px] font-bold text-gray-400 uppercase tracking-tighter">M: {{ $unit->motor_no ?? '—' }}</span>
                                <span class="text-[9px] font-bold text-gray-400 uppercase tracking-tighter">C: {{ $unit->chassis_no ?? '—' }}</span>
                            </div>
                        </div>
                    </td>

                    {{-- Vehicle Details --}}
                    <td class="px-6 py-5 whitespace-nowrap">
                        <div class="flex flex-col">
                            <span class="text-sm font-black text-gray-900">{{ $unit->make }} {{ $unit->model }}</span>
                            <span class="text-xs font-bold text-gray-400">{{ $unit->year }}</span>
                            <div class="mt-2">
                                <span class="px-2 py-0.5 bg-blue-50 text-blue-600 text-[9px] font-black uppercase rounded border border-blue-100">New</span>
                            </div>
                        </div>
                    </td>

                    {{-- Assigned Drivers --}}
                    <td class="px-6 py-5 whitespace-nowrap">
                        <div class="flex flex-col gap-1">
                            <div class="flex items-center gap-2">
                                <span class="text-[10px] font-black text-gray-400 uppercase tracking-tighter">D1:</span>
                                <span class="text-[11px] font-bold {{ $unit->driver_id ? 'text-gray-900' : 'text-gray-300 italic' }}">
                                    @if($unit->driver_id && $primary_driver)
                                        @php $d1 = explode('|', $primary_driver); @endphp
                                        {{ $d1[0] }}
                                    @else
                                        No D1
                                    @endif
                                </span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="text-[10px] font-black text-gray-400 uppercase tracking-tighter">D2:</span>
                                <span class="text-[11px] font-bold {{ $unit->secondary_driver_id ? 'text-gray-900' : 'text-gray-300 italic' }}">
                                    @if($unit->secondary_driver_id && $secondary_driver)
                                        @php $d2 = explode('|', $secondary_driver); @endphp
                                        {{ $d2[0] }}
                                    @else
                                        No D2
                                    @endif
                                </span>
                            </div>
                        </div>
                    </td>

                    {{-- Status --}}
                    <td class="px-6 py-5 whitespace-nowrap">
                        <div class="flex items-center gap-2">
                            <div class="w-2 h-2 rounded-full {{ $dotClass }} animate-pulse {{ $unit->status === 'active' ? 'shadow-[0_0_8px_rgba(34,197,94,0.5)]' : '' }}"></div>
                            <span class="text-[11px] font-black uppercase tracking-widest {{ $statusColor }}">
                                {{ ucfirst($unit->status === 'available' ? 'vacant' : $unit->status) }}
                            </span>
                        </div>
                    </td>

                    {{-- Boundary Rate --}}
                    <td class="px-6 py-5 whitespace-nowrap">
                        <div class="flex flex-col">
                            <span class="text-sm font-black text-gray-900">₱{{ number_format($unit->current_rate ?? $unit->boundary_rate, 2) }}</span>
                            <div class="mt-2">
                                <span class="px-2 py-1 bg-blue-600 text-white text-[9px] font-black uppercase rounded shadow-sm">
                                    {{ $unit->rate_label ?? 'Standard Rate' }}
                                </span>
                            </div>
                        </div>
                    </td>

                    {{-- Actions --}}
                    <td class="px-6 py-5 whitespace-nowrap">
                        <div class="flex items-center gap-2">
                            <button onclick="event.stopPropagation(); editUnit({{ $unit->id }})" 
                                class="w-8 h-8 flex items-center justify-center bg-blue-50 text-blue-600 rounded-lg border border-blue-100 hover:bg-blue-600 hover:text-white transition-all shadow-sm">
                                <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                            </button>
                            <form method="POST" action="{{ route('units.destroy', $unit->id) }}"
                                onsubmit="return confirm('Delete unit {{ $unit->plate_number }}?');" class="inline m-0 p-0">
                                @csrf @method('DELETE')
                                <button type="submit" onclick="event.stopPropagation()"
                                    class="w-8 h-8 flex items-center justify-center bg-red-50 text-red-600 rounded-lg border border-red-100 hover:bg-red-600 hover:text-white transition-all shadow-sm">
                                    <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>

                {{-- Maintenance Bar Row (Sub-Row) --}}
                @if($has_maintenance_data)
                    <tr class="hover:bg-blue-50/10 transition-colors" onclick="viewUnitDetails({{ $unit->id }})">
                        <td colspan="6" class="px-6 pb-5 pt-0 border-none">
                            @include('units.partials._maintenance_health_bar', ['unit' => $unit])
                        </td>
                    </tr>
                @endif

            @empty
                <tr>
                    <td colspan="6" class="px-6 py-20 text-center">
                        <i data-lucide="car" class="w-16 h-16 mx-auto mb-4 text-gray-100"></i>
                        <h4 class="text-gray-900 font-black text-xl">No units found</h4>
                        <p class="text-gray-400 italic">Try adjusting your search criteria.</p>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- Modern Pagination --}}
@if($pagination['total_pages'] > 1)
    <div class="px-8 py-6 bg-gray-50/50 border-t border-gray-100 flex flex-col md:flex-row items-center justify-between gap-4">
        <div class="text-[11px] font-bold text-gray-400 uppercase tracking-widest">
            Showing <span class="text-gray-900">{{ count($units) }}</span> of <span class="text-gray-900">{{ number_format($pagination['total_items']) }}</span> Units
        </div>
        <div class="flex items-center gap-1.5">
            @if($pagination['has_prev'])
                <button onclick="changePage({{ $pagination['prev_page'] }})" class="p-2.5 rounded-xl border border-gray-200 bg-white text-gray-600 hover:bg-gray-50 transition-all active:scale-90 shadow-sm">
                    <i data-lucide="chevron-left" class="w-5 h-5"></i>
                </button>
            @endif
            @for($i = max(1, $pagination['page'] - 2); $i <= min($pagination['total_pages'], $pagination['page'] + 2); $i++)
                <button onclick="changePage({{ $i }})" class="w-10 h-10 rounded-xl border text-[11px] font-black transition-all {{ $i === $pagination['page'] ? 'bg-yellow-500 border-yellow-500 text-white shadow-md shadow-yellow-200' : 'bg-white border-gray-200 text-gray-500 hover:bg-gray-50' }}">
                    {{ $i }}
                </button>
            @endfor
            @if($pagination['has_next'])
                <button onclick="changePage({{ $pagination['next_page'] }})" class="p-2.5 rounded-xl border border-gray-200 bg-white text-gray-600 hover:bg-gray-50 transition-all active:scale-90 shadow-sm">
                    <i data-lucide="chevron-right" class="w-5 h-5"></i>
                </button>
            @endif
        </div>
    </div>
@endif
