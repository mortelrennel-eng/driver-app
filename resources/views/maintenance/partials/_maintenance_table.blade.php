<div class="overflow-x-auto pb-4">
    <table class="min-w-full text-sm modern-table-sep">
        <thead>
            <tr>
                <th class="px-4 py-2 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Unit / Driver</th>
                <th class="px-4 py-2 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Type</th>
                <th class="px-4 py-2 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Mechanic</th>
                <th class="px-4 py-2 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Date Started</th>
                <th class="px-4 py-2 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Date Done</th>
                <th class="px-4 py-2 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Cost</th>
                <th class="px-4 py-2 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Status</th>
                <th class="px-4 py-2 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($records as $r)
            <tr class="modern-row cursor-pointer group"
                onclick="openViewMaint({{ $r->id }})">
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-2">
                           <p class="font-bold text-gray-900 group-hover:text-yellow-700 transition-colors">{{ $r->plate_number }}</p>
                           @if($r->date_started == date('Y-m-d') && $r->status != 'complete')
                               <span class="px-1.5 py-0.5 bg-red-100 text-red-600 text-[9px] font-black uppercase rounded animate-pulse">Today</span>
                           @endif
                           <i data-lucide="external-link" class="w-3 h-3 text-gray-300 opacity-0 group-hover:opacity-100 transition-opacity"></i>
                        </div>
                        @if($r->driver_name)
                            <p class="text-[10px] text-gray-400 font-bold uppercase truncate max-w-[120px] group-hover:text-yellow-600 transition-colors" title="{{ $r->driver_name }}">
                                {{ $r->driver_name }}
                            </p>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        @if($r->maintenance_type === 'emergency')
                            <span class="inline-flex items-center gap-1 px-2 py-1 text-[10px] font-black rounded-lg bg-red-600 text-white uppercase tracking-wider shadow-sm">
                                🚨 Emergency
                            </span>
                        @elseif($r->maintenance_type === 'corrective')
                            <span class="inline-flex items-center gap-1 px-2 py-1 text-[10px] font-black rounded-lg bg-orange-100 text-orange-800 uppercase tracking-wider border border-orange-200">
                                🔧 Corrective
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 px-2 py-1 text-[10px] font-black rounded-lg bg-blue-100 text-blue-800 uppercase tracking-wider border border-blue-200">
                                🛡️ Preventive
                            </span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        @if(!$r->mechanic_name || $r->mechanic_name === '—')
                            <button onclick="openEditMaint(document.querySelector('button[data-id=\'{{ $r->id }}\']')); event.stopPropagation();" data-id="{{ $r->id }}" class="flex items-center gap-1.5 px-2 py-1 border border-dashed border-gray-300 rounded-lg text-[10px] font-bold text-gray-500 hover:text-blue-600 hover:border-blue-400 hover:bg-blue-50 transition w-max">
                                <i data-lucide="user-plus" class="w-3 h-3"></i> Assign Mechanic
                            </button>
                        @else
                            <div class="flex flex-col gap-1.5">
                                @foreach(array_filter(array_map('trim', explode(',', $r->mechanic_name))) as $mech)
                                    @php
                                        $initials = collect(explode(' ', $mech))->map(function($n) { return substr($n, 0, 1); })->take(2)->implode('');
                                    @endphp
                                    <div class="flex items-center gap-2">
                                        <div class="w-6 h-6 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center text-[10px] font-black uppercase shrink-0 shadow-sm border border-blue-200">
                                            {{ $initials }}
                                        </div>
                                        <span class="text-xs font-bold text-gray-700 truncate max-w-[140px]" title="{{ $mech }}">{{ $mech }}</span>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                        <div class="text-[9px] text-gray-400 mt-1.5 font-bold tracking-tight">
                            <span title="Input by {{ $r->creator_name ?? 'System' }}">In: <span class="uppercase text-gray-500">{{ $r->creator_name ?? 'System' }}</span></span>
                            @if(isset($r->editor_name) && $r->editor_name)
                                <span class="ml-1" title="Last edit by {{ $r->editor_name }}">Ed: <span class="uppercase text-gray-500">{{ $r->editor_name }}</span></span>
                            @endif
                        </div>
                    </td>
                    <td class="px-4 py-3 text-gray-600">{{ formatDate($r->date_started) }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ $r->date_completed ? formatDate($r->date_completed) : '—' }}</td>
                    <td class="px-4 py-3 font-semibold text-gray-900">{{ formatCurrency($r->cost) }}</td>
                    <td class="px-4 py-3 min-w-[240px]">
                        @php
                            $s = strtolower($r->status ?? 'pending');
                            $step = $s === 'cancelled' ? 0 : (in_array($s, ['complete', 'completed']) ? 3 : (in_array($s, ['ongoing', 'testing', 'in_progress', 'in_shop']) ? 2 : 1));
                        @endphp
                        @if($step === 0)
                            <span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-800 font-black uppercase tracking-widest">Cancelled</span>
                        @else
                            <div class="flex items-center w-full mt-1.5">
                                <!-- Pending -->
                                <div class="flex flex-col items-center relative flex-1 group cursor-help" title="Pending">
                                    <div class="w-5 h-5 rounded-full {{ $step >= 1 ? 'bg-yellow-500' : 'bg-gray-200' }} z-10 flex items-center justify-center shadow-sm">
                                        @if($step > 1) <i data-lucide="check" class="w-3 h-3 text-white"></i> @endif
                                    </div>
                                    <div class="absolute top-2.5 left-1/2 w-full h-0.5 {{ $step >= 2 ? 'bg-blue-500' : 'bg-gray-200' }} -z-0"></div>
                                    <span class="text-[9px] font-black mt-1.5 {{ $step >= 1 ? 'text-yellow-600' : 'text-gray-400' }} uppercase tracking-wider">Pending</span>
                                </div>
                                
                                <!-- Ongoing -->
                                <div class="flex flex-col items-center relative flex-1 group cursor-help" title="Ongoing">
                                    <div class="absolute top-2.5 right-1/2 w-full h-0.5 {{ $step >= 2 ? 'bg-blue-500' : 'bg-gray-200' }} -z-0"></div>
                                    <div class="w-5 h-5 rounded-full {{ $step >= 2 ? 'bg-blue-500' : 'bg-gray-200' }} z-10 flex items-center justify-center shadow-sm">
                                         @if($step > 2) <i data-lucide="check" class="w-3 h-3 text-white"></i> @endif
                                    </div>
                                    <div class="absolute top-2.5 left-1/2 w-full h-0.5 {{ $step >= 3 ? 'bg-green-500' : 'bg-gray-200' }} -z-0"></div>
                                    <span class="text-[9px] font-black mt-1.5 {{ $step >= 2 ? 'text-blue-600' : 'text-gray-400' }} uppercase tracking-wider text-center leading-none">Ongoing</span>
                                </div>
                                
                                <!-- Complete -->
                                <div class="flex flex-col items-center relative flex-1 group cursor-help" title="Complete">
                                    <div class="absolute top-2.5 right-1/2 w-full h-0.5 {{ $step >= 3 ? 'bg-green-500' : 'bg-gray-200' }} -z-0"></div>
                                    <div class="w-5 h-5 rounded-full {{ $step >= 3 ? 'bg-green-500' : 'bg-gray-200' }} z-10 flex items-center justify-center shadow-sm">
                                         @if($step == 3) <i data-lucide="check" class="w-3 h-3 text-white"></i> @endif
                                    </div>
                                    <span class="text-[9px] font-black mt-1.5 {{ $step >= 3 ? 'text-green-600' : 'text-gray-400' }} uppercase tracking-wider text-center leading-none">Complete</span>
                                </div>
                            </div>
                        @endif
                    </td>
                    <td class="px-4 py-3" onclick="event.stopPropagation()">
                        <div class="flex gap-2">
                            {{-- Toggle Complete --}}
                            <form method="POST" action="{{ route('maintenance.toggle-complete', $r->id) }}">
                                @csrf
                                <button type="submit" title="{{ $r->date_completed ? 'Mark as Incomplete' : 'Mark as Complete' }}" 
                                    class="{{ $r->date_completed ? 'text-orange-600 hover:text-orange-900 hover:bg-orange-50' : 'text-green-600 hover:text-green-900 hover:bg-green-50' }} p-1 rounded transition">
                                    <i data-lucide="{{ $r->date_completed ? 'rotate-ccw' : 'check-circle' }}" class="w-4 h-4"></i>
                                </button>
                            </form>

                            {{-- Advance Maintenance Stage (only for non-completed records) --}}
                            @if(!$r->date_completed && $r->status !== 'cancelled')
                            <form method="POST" action="{{ route('maintenance.toggle-in-progress', $r->id) }}">
                                @csrf
                                <button type="submit"
                                    title="Advance Stage (Pending -> Ongoing -> Complete)"
                                    class="text-blue-500 hover:text-blue-800 hover:bg-blue-50 p-1 rounded transition">
                                    <i data-lucide="fast-forward" class="w-4 h-4"></i>
                                </button>
                            </form>
                            @endif

                            <button onclick="openEditMaint(this)" data-id="{{ $r->id }}" class="text-blue-600 hover:text-blue-900 p-1 rounded hover:bg-blue-50 transition" title="Edit Record"><i data-lucide="edit" class="w-4 h-4"></i></button>
                            <form method="POST" action="{{ route('maintenance.destroy', $r->id) }}" onsubmit="return confirm('Are you sure you want to archive this maintenance record?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-orange-500 hover:text-orange-700 p-1 rounded hover:bg-orange-50 transition" title="Archive Record"><i data-lucide="archive" class="w-4 h-4"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-4 py-12 text-center text-gray-400">
                        <i data-lucide="wrench" class="w-12 h-12 mx-auto mb-3 text-gray-300"></i>
                        <p>No maintenance records found.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($pagination['total_pages'] > 1)
    <div class="px-4 py-3 flex items-center justify-between">
        <div class="flex-1 flex justify-between sm:hidden">
            @if($pagination['has_prev'])
                <a href="?page={{ $pagination['prev_page'] }}&search={{ urlencode($search) }}&status={{ urlencode($status) }}&type={{ urlencode($type) }}" class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-xs font-bold rounded-xl text-gray-700 bg-white hover:bg-gray-50">Previous</a>
            @endif
            @if($pagination['has_next'])
                <a href="?page={{ $pagination['next_page'] }}&search={{ urlencode($search) }}&status={{ urlencode($status) }}&type={{ urlencode($type) }}" class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-xs font-bold rounded-xl text-gray-700 bg-white hover:bg-gray-50">Next</a>
            @endif
        </div>
        <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between font-bold text-[10px] text-gray-500 uppercase tracking-widest">
            <div>
                <p>Showing <span class="text-gray-900">{{ min($pagination['total_items'], ($pagination['page'] - 1) * 10 + 1) }}</span> to <span class="text-gray-900">{{ min($pagination['total_items'], $pagination['page'] * 10) }}</span> of <span class="text-gray-900">{{ $pagination['total_items'] }}</span> records</p>
            </div>
            <div>
                <nav class="relative z-0 inline-flex rounded-xl shadow-sm -space-x-px" aria-label="Pagination">
                    @if($pagination['has_prev'])
                        <a href="?page={{ $pagination['prev_page'] }}&search={{ urlencode($search) }}&status={{ urlencode($status) }}&type={{ urlencode($type) }}" class="relative inline-flex items-center px-2 py-2 rounded-l-xl border border-gray-300 bg-white text-gray-400 hover:bg-gray-50" onclick="event.preventDefault(); performMaintenanceSearch({{ $pagination['prev_page'] }});">
                            <i data-lucide="chevron-left" class="w-4 h-4"></i>
                        </a>
                    @endif

                    @php
                        $start = max(1, $pagination['page'] - 2);
                        $end = min($pagination['total_pages'], $pagination['page'] + 2);
                    @endphp

                    @for($i = $start; $i <= $end; $i++)
                        <a href="?page={{ $i }}&search={{ urlencode($search) }}&status={{ urlencode($status) }}&type={{ urlencode($type) }}" 
                           class="relative inline-flex items-center px-4 py-2 border text-[11px] font-black {{ $i === $pagination['page'] ? 'z-10 bg-yellow-50 border-yellow-500 text-yellow-600' : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50' }}"
                           onclick="event.preventDefault(); performMaintenanceSearch({{ $i }});">
                            {{ $i }}
                        </a>
                    @endfor

                    @if($pagination['has_next'])
                        <a href="?page={{ $pagination['next_page'] }}&search={{ urlencode($search) }}&status={{ urlencode($status) }}&type={{ urlencode($type) }}" class="relative inline-flex items-center px-2 py-2 rounded-r-xl border border-gray-300 bg-white text-gray-400 hover:bg-gray-50" onclick="event.preventDefault(); performMaintenanceSearch({{ $pagination['next_page'] }});">
                            <i data-lucide="chevron-right" class="w-4 h-4"></i>
                        </a>
                    @endif
                </nav>
            </div>
        </div>
    </div>
    @endif
