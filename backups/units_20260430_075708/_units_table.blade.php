{{-- ═══════════════════════════════════════════════════════════════
     UNIT MANAGEMENT — PRECISE TABLE FORMAT
     Matching the user-provided screenshot aesthetic.
     ═══════════════════════════════════════════════════════════════ --}}

<div class="overflow-x-auto bg-gray-50/50 px-4 py-3">
    <table class="min-w-full text-sm modern-table-sep">
        <thead>
            <tr>
                <th class="px-6 py-3 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Plate Number Info</th>
                <th class="px-6 py-3 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Vehicle Details</th>
                <th class="px-6 py-3 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Assigned Drivers</th>
                <th class="px-6 py-3 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Status</th>
                <th class="px-6 py-3 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Boundary Rate</th>
                <th class="px-6 py-3 text-center text-[10px] font-black text-gray-400 uppercase tracking-widest">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($units as $unit)
                @php
                    $primary_driver = $unit->primary_driver ?? null;
                    $secondary_driver = $unit->secondary_driver ?? null;
                    
                    $dotClass = match($unit->status) {
                        'active'       => 'bg-green-500',
                        'maintenance'  => 'bg-red-500',
                        'coding'       => 'bg-yellow-500',
                        'at_risk'      => 'bg-orange-500',
                        'vacant', 'available' => 'bg-gray-400',
                        default        => 'bg-gray-400',
                    };
                    $statusColor = match($unit->status) {
                        'active'       => 'text-green-600',
                        'maintenance'  => 'text-red-600',
                        'coding'       => 'text-yellow-600',
                        'at_risk'      => 'text-orange-600',
                        default        => 'text-gray-500',
                    };
                    
                    // Maintenance check for the sub-row bar
                    $has_maintenance_data = (int)($unit->gps_device_count ?? 0) > 0 || !empty($unit->imei);
                @endphp
                
                {{-- Main Data Row --}}
                <tr class="modern-row cursor-pointer group" onclick="viewUnitDetails({{ $unit->id }})">
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
                                {{ $unit->status === 'at_risk' ? 'At Risk' : ucfirst($unit->status === 'available' ? 'vacant' : $unit->status) }}
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
                    <td class="px-6 py-5 whitespace-nowrap text-center relative">
                        <button type="button"
                            class="p-2 text-gray-400 hover:text-gray-800 hover:bg-gray-200 rounded-full transition-colors focus:outline-none inline-flex items-center justify-center"
                            onclick="toggleUnitDropdown('unit-dropdown-{{ $unit->id }}', event)"
                            title="Actions">
                            <i data-lucide="more-vertical" class="w-5 h-5"></i>
                        </button>

                        <div id="unit-dropdown-{{ $unit->id }}"
                            class="unit-action-dropdown hidden absolute right-4 mt-1 w-40 bg-white rounded-lg shadow-xl border border-gray-100 z-50 overflow-hidden">
                            {{-- Edit --}}
                            <button type="button"
                                class="w-full text-left px-4 py-2.5 text-xs font-bold text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors flex items-center gap-2"
                                onclick="event.stopPropagation(); document.getElementById('unit-dropdown-{{ $unit->id }}').classList.add('hidden'); editUnit({{ $unit->id }})">
                                <i data-lucide="edit-2" class="w-4 h-4"></i> Edit Unit
                            </button>
                            {{-- Archive --}}
                            <form method="POST" action="{{ route('units.destroy', $unit->id) }}"
                                onsubmit="return confirm('Archive unit {{ $unit->plate_number }}? It will be moved to the Archive page.');"
                                class="m-0 p-0">
                                @csrf @method('DELETE')
                                <button type="submit"
                                    onclick="event.stopPropagation()"
                                    class="w-full text-left px-4 py-2.5 text-xs font-bold text-amber-600 hover:bg-amber-50 transition-colors flex items-center gap-2 border-t border-gray-50">
                                    <i data-lucide="archive" class="w-4 h-4"></i> Archive Unit
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>

                {{-- Maintenance Bar Row (Sub-Row) --}}
                @if($has_maintenance_data)
                    <tr class="modern-row" onclick="viewUnitDetails({{ $unit->id }})" style="cursor:pointer">
                        <td colspan="6" class="px-6 pb-4 pt-0" style="border-radius: 0 0 0.75rem 0.75rem">
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
                <button onclick="changePage({{ $i }})" class="w-10 h-10 rounded-xl border text-[11px] font-black transition-all {{ $i === $pagination['page'] ? 'bg-blue-600 border-blue-600 text-white shadow-md' : 'bg-white border-gray-200 text-gray-500 hover:bg-gray-50' }}">
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

<script>
    window.toggleUnitDropdown = function(id, event) {
        event.stopPropagation();

        // Close all other unit dropdowns and reset their row z-index
        document.querySelectorAll('.unit-action-dropdown').forEach(el => {
            if (el.id !== id) el.classList.add('hidden');
            const row = el.closest('tr');
            if (row) {
                row.style.zIndex = '';
                row.style.position = '';
            }
        });

        // Toggle this dropdown
        const dropdown = document.getElementById(id);
        if (dropdown) {
            const isHidden = dropdown.classList.contains('hidden');
            const row = dropdown.closest('tr');
            if (isHidden) {
                dropdown.classList.remove('hidden');
                if (row) {
                    row.style.position = 'relative';
                    row.style.zIndex = '50';
                }
            } else {
                dropdown.classList.add('hidden');
                if (row) {
                    row.style.zIndex = '';
                    row.style.position = '';
                }
            }
        }
    };

    // Attach document-level close listener only once
    if (!window.unitDropdownListenerAdded) {
        document.addEventListener('click', function () {
            document.querySelectorAll('.unit-action-dropdown').forEach(el => {
                el.classList.add('hidden');
                const row = el.closest('tr');
                if (row) {
                    row.style.zIndex = '';
                    row.style.position = '';
                }
            });
        });
        window.unitDropdownListenerAdded = true;
    }
</script>
