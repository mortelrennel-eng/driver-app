<div class="overflow-x-auto pb-4">
    <table class="min-w-full text-sm modern-table-sep">
        <thead>
            <tr>
                <th class="px-6 py-4 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest w-1/4">Driver Profile</th>
                <th class="px-6 py-4 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Assigned Unit</th>
                <th class="px-6 py-4 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">License Detail</th>
                <th class="px-6 py-4 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Status</th>
                <th class="px-6 py-4 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Financial Target</th>
                <th class="px-6 py-4 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Rating</th>
                <th class="px-6 py-4 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest text-right">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($drivers as $driver)
                @php $has_shortage = isset($driver->net_shortage) && $driver->net_shortage > 0; @endphp
                <tr class="modern-row cursor-pointer group {{ $has_shortage ? 'shortage-row' : '' }}" onclick="openDriverDetails({{ $driver->id }})">
                    
                    {{-- Driver Profile --}}
                    <td class="px-6 py-5">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 rounded-full {{ $has_shortage ? 'bg-red-100 text-red-600' : 'bg-blue-100 text-blue-600' }} flex items-center justify-center flex-shrink-0 shadow-inner">
                                <span class="text-lg font-black">{{ substr($driver->first_name ?? $driver->full_name, 0, 1) }}{{ substr($driver->last_name ?? '', 0, 1) }}</span>
                            </div>
                            <div>
                                <div class="flex items-center gap-2 mb-1">
                                    <h4 class="text-sm font-black {{ $has_shortage ? 'text-red-700 shortage-text-blink' : 'text-gray-900' }}">{{ $driver->full_name }}</h4>
                                    @if($has_shortage)
                                        <span class="shortage-blink inline-flex items-center gap-1 px-2 py-0.5 bg-red-600 text-white text-[9px] font-black rounded uppercase tracking-widest shadow-sm"
                                              title="Net unpaid shortage: ₱{{ number_format($driver->net_shortage, 2) }}">
                                            <i data-lucide="alert-triangle" class="w-3 h-3"></i> ₱{{ number_format($driver->net_shortage, 2) }}
                                        </span>
                                    @endif
                                </div>
                                <div class="text-[10px] font-semibold text-gray-400 flex gap-2">
                                    <span title="Input by {{ $driver->creator_name ?? 'System' }}">IN: {{ strtoupper($driver->creator_name ?? 'System') }}</span>
                                    @if(isset($driver->editor_name) && $driver->editor_name)
                                        <span class="text-gray-300">|</span>
                                        <span title="Last edit by {{ $driver->editor_name }}">ED: {{ strtoupper($driver->editor_name) }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </td>

                    {{-- Assigned Unit --}}
                    <td class="px-6 py-5 whitespace-nowrap">
                        @if(!empty($driver->assigned_unit))
                            <div class="inline-flex items-center gap-2 bg-slate-800 text-white px-3 py-1.5 rounded-lg shadow-sm">
                                <i data-lucide="car" class="w-4 h-4 text-blue-400"></i>
                                <span class="text-sm font-black tracking-widest">{{ $driver->assigned_unit }}</span>
                            </div>
                        @else
                            <span class="inline-flex items-center gap-1.5 text-emerald-700 font-black text-[11px] bg-emerald-50 px-3 py-1.5 rounded-lg border border-emerald-200 uppercase tracking-widest">
                                <div class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></div>
                                Unassigned
                            </span>
                        @endif
                    </td>

                    {{-- License Detail --}}
                    <td class="px-6 py-5 whitespace-nowrap">
                        <div class="text-sm font-bold text-gray-900 font-mono tracking-wider">{{ $driver->license_number ?? 'N/A' }}</div>
                        @if(isset($driver->license_expiry))
                            <div class="text-[10px] font-semibold mt-1 {{ \Carbon\Carbon::parse($driver->license_expiry)->isPast() ? 'text-red-500' : 'text-gray-500' }}">
                                EXP: {{ \Carbon\Carbon::parse($driver->license_expiry)->format('M d, Y') }}
                            </div>
                        @endif
                    </td>

                    {{-- Status --}}
                    <td class="px-6 py-5 whitespace-nowrap">
                        <div class="flex items-center gap-2">
                            <div class="w-2.5 h-2.5 rounded-full {{ $driver->is_active ? 'bg-green-500 animate-pulse shadow-[0_0_8px_rgba(34,197,94,0.6)]' : 'bg-red-500' }}"></div>
                            <span class="text-[11px] font-black uppercase tracking-widest {{ $driver->is_active ? 'text-green-700' : 'text-red-700' }}">
                                {{ $driver->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </div>
                    </td>

                    {{-- Financial Target --}}
                    <td class="px-6 py-5 whitespace-nowrap">
                        <div class="flex flex-col gap-1.5">
                            <div class="flex items-center gap-2">
                                @if(!empty($driver->assigned_unit))
                                    <span class="text-lg font-black text-gray-900 tracking-tight">₱{{ number_format($driver->current_target ?? $driver->daily_boundary_target, 2) }}</span>
                                    <span class="text-[9px] bg-blue-50 text-blue-600 border border-blue-200 px-1.5 py-0.5 rounded font-bold uppercase tracking-widest">Unit</span>
                                @else
                                    <span class="text-[11px] font-bold text-gray-400 italic">Pending Unit Assignment</span>
                                @endif
                            </div>
                            @if(isset($driver->target_label) && $driver->target_type !== 'regular')
                                <span class="text-[10px] font-black uppercase tracking-widest px-2 py-0.5 rounded w-fit
                                    @if($driver->target_type === 'coding') bg-indigo-50 text-indigo-700 border border-indigo-200
                                    @elseif($driver->target_type === 'discount') bg-amber-50 text-amber-700 border border-amber-200
                                    @else bg-gray-50 text-gray-600 border border-gray-200 @endif">
                                    {{ $driver->target_label }}
                                </span>
                            @endif
                        </div>
                    </td>

                    {{-- Rating --}}
                    <td class="px-6 py-5 whitespace-nowrap">
                        <div class="inline-flex items-center gap-1.5 bg-yellow-50 px-2 py-1 rounded-md border border-yellow-200">
                            <i data-lucide="star" class="w-4 h-4 text-yellow-500 fill-yellow-500"></i>
                            <span class="text-xs font-black text-yellow-700 uppercase tracking-widest">{{ $driver->performance_rating ?? 'Good' }}</span>
                        </div>
                    </td>

                    {{-- Actions --}}
                    <td class="px-6 py-5 whitespace-nowrap text-right relative">
                        <button type="button" class="p-2 text-gray-400 hover:text-gray-800 hover:bg-gray-200 rounded-full transition-colors focus:outline-none" onclick="toggleDriverDropdown('dropdown-{{ $driver->id }}', event)" title="Actions">
                            <i data-lucide="more-vertical" class="w-5 h-5"></i>
                        </button>
                        
                        <div id="dropdown-{{ $driver->id }}" class="driver-action-dropdown hidden absolute right-8 mt-1 w-36 bg-white rounded-lg shadow-xl border border-gray-100 z-50 overflow-hidden transform transition-all">
                            <button type="button" class="w-full text-left px-4 py-2.5 text-xs font-bold text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors flex items-center gap-2" onclick="event.stopPropagation(); document.getElementById('dropdown-{{ $driver->id }}').classList.add('hidden'); openEditDriverModal({{ $driver->id }})">
                                <i data-lucide="edit-2" class="w-4 h-4"></i> Edit Driver
                            </button>
                            <button type="button" class="w-full text-left px-4 py-2.5 text-xs font-bold text-orange-600 hover:bg-orange-50 transition-colors flex items-center gap-2 border-t border-gray-50" onclick="event.stopPropagation(); document.getElementById('dropdown-{{ $driver->id }}').classList.add('hidden'); archiveDriver({{ $driver->id }}, '{{ $driver->full_name }}')">
                                <i data-lucide="archive" class="w-4 h-4"></i> Archive
                            </button>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="px-6 py-16 text-center">
                        <div class="flex flex-col items-center justify-center">
                            <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mb-4">
                                <i data-lucide="users" class="w-8 h-8 text-gray-400"></i>
                            </div>
                            <h3 class="text-sm font-bold text-gray-900 mb-1">No Drivers Found</h3>
                            <p class="text-xs text-gray-500">There are currently no drivers matching your criteria.</p>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($pagination['total_pages'] > 1)
    <div class="px-8 py-6 bg-gray-50/50 border-t border-gray-100 flex flex-col md:flex-row items-center justify-between gap-4">
        <div class="text-[11px] font-bold text-gray-400 uppercase tracking-widest">
            Showing <span class="text-gray-900">{{ $pagination['total_items'] }}</span> results / Page <span class="text-gray-900">{{ $pagination['page'] }}</span> of <span class="text-gray-900">{{ $pagination['total_pages'] }}</span>
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
    // Define global functions to prevent re-declaration issues with AJAX
    window.toggleDriverDropdown = function(id, event) {
        event.stopPropagation(); // Prevent row click (which opens details)
        
        // Close all other dropdowns and reset their row z-index
        document.querySelectorAll('.driver-action-dropdown').forEach(el => {
            if (el.id !== id) {
                el.classList.add('hidden');
            }
            const row = el.closest('tr');
            if (row) {
                row.style.zIndex = '';
                row.style.position = '';
            }
        });
        
        // Toggle the target dropdown
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

    // Attach document listener only once
    if (!window.driverDropdownListenerAdded) {
        document.addEventListener('click', function() {
            document.querySelectorAll('.driver-action-dropdown').forEach(el => {
                el.classList.add('hidden');
                const row = el.closest('tr');
                if (row) {
                    row.style.zIndex = '';
                    row.style.position = '';
                }
            });
        });
        window.driverDropdownListenerAdded = true;
    }
</script>
