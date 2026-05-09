<div class="overflow-x-auto pb-4">
    <table class="min-w-full text-sm modern-table-sep">
        <thead class="bg-gray-50/80 border-b border-gray-100">
            <tr>
                <th class="px-3 md:px-6 py-4 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest w-1/4">
                    Driver Profile</th>
                <th class="px-3 md:px-6 py-4 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Assigned
                    Unit</th>
                <th class="px-3 md:px-6 py-4 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest license-detail-col hidden lg:table-cell">License
                    Detail</th>
                <th class="px-3 md:px-6 py-4 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Status
                </th>
                <th class="px-3 md:px-6 py-4 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest financial-target-col hidden lg:table-cell">Financial
                    Target</th>
                <th class="px-3 md:px-6 py-4 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest rating-col hidden lg:table-cell">Rating
                </th>
                <th
                    class="px-3 md:px-6 py-4 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest text-right">
                    Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($drivers as $driver)
                @php $has_shortage = isset($driver->net_shortage) && $driver->net_shortage > 0; @endphp
                <tr class="modern-row cursor-pointer group {{ $has_shortage ? 'shortage-row' : '' }}"
                    onclick="openDriverDetails({{ $driver->id }})">

                    {{-- Driver Profile --}}
                    <td class="px-3 md:px-6 py-4 md:py-5">
                        <div class="flex items-center gap-2 md:gap-4">
                            <div
                                class="w-10 h-10 md:w-12 md:h-12 rounded-full {{ $has_shortage ? 'bg-red-100 text-red-600' : 'bg-blue-100 text-blue-600' }} flex items-center justify-center flex-shrink-0 shadow-inner">
                                <span
                                    class="text-sm md:text-lg font-black">{{ substr($driver->first_name ?? $driver->full_name, 0, 1) }}{{ substr($driver->last_name ?? '', 0, 1) }}</span>
                            </div>
                            <div>
                                <div class="flex items-center gap-2 mb-1">
                                    <h4
                                        class="text-xs md:text-sm font-black {{ $has_shortage ? 'text-red-700 shortage-text-blink' : 'text-gray-900' }}">
                                        {{ $driver->full_name }}
                                    </h4>
                                    @if($has_shortage)
                                        <div class="shortage-blink flex items-center gap-1.5 px-2.5 py-1 bg-red-50 text-red-600 border border-red-200 rounded-lg shadow-sm group/shortage hover:bg-red-600 hover:text-white transition-all duration-300"
                                            title="Net unpaid boundary shortage: ₱{{ number_format($driver->net_shortage, 2) }}">
                                            <i data-lucide="alert-triangle" class="w-3 h-3"></i>
                                            <span class="text-[10px] font-black tracking-tight whitespace-nowrap">
                                                ₱{{ number_format($driver->net_shortage, 0) }} <span
                                                    class="text-[8px] opacity-70">SHORT</span>
                                            </span>
                                        </div>
                                    @endif

                                    @if(isset($driver->total_pending_debt) && $driver->total_pending_debt > 0)
                                        <div class="flex items-center gap-1.5 px-2.5 py-1 bg-orange-50 text-orange-600 border border-orange-200 rounded-lg shadow-sm group/debt hover:bg-orange-600 hover:text-white transition-all duration-300"
                                            title="Pending Accident/Incident Debt: ₱{{ number_format($driver->total_pending_debt, 2) }}">
                                            <i data-lucide="shield-alert" class="w-3 h-3"></i>
                                            <span class="text-[10px] font-black tracking-tight whitespace-nowrap">
                                                ₱{{ number_format($driver->total_pending_debt, 0) }} <span
                                                    class="text-[8px] opacity-70">DEBT</span>
                                            </span>
                                        </div>
                                    @endif
                                </div>
                                <div class="text-[10px] font-semibold text-gray-400 flex gap-2">
                                    <span title="Input by {{ $driver->creator_name ?? 'System' }}">IN:
                                        {{ strtoupper($driver->creator_name ?? 'System') }}</span>
                                    @if(isset($driver->editor_name) && $driver->editor_name)
                                        <span class="text-gray-300">|</span>
                                        <span title="Last edit by {{ $driver->editor_name }}">ED:
                                            {{ strtoupper($driver->editor_name) }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </td>

                    {{-- Assigned Unit --}}
                    <td class="px-3 md:px-6 py-4 md:py-5 whitespace-nowrap">
                        @if(!empty($driver->assigned_unit))
                            <div
                                class="inline-flex items-center gap-1.5 md:gap-2 bg-slate-800 text-white px-2.5 py-1 md:px-3 md:py-1.5 rounded-lg shadow-sm">
                                <i data-lucide="car" class="w-3.5 h-3.5 md:w-4 md:h-4 text-blue-400"></i>
                                <span class="text-xs md:text-sm font-black tracking-widest">{{ $driver->assigned_unit }}</span>
                            </div>
                        @else
                            <span
                                class="inline-flex items-center gap-1 px-1.5 md:gap-1.5 text-emerald-700 font-black text-[10px] md:text-[11px] bg-emerald-50 px-2.5 py-1 md:px-3 md:py-1.5 rounded-lg border border-emerald-200 uppercase tracking-widest">
                                <div class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></div>
                                Unassigned
                            </span>
                        @endif
                    </td>

                    {{-- License Detail --}}
                    <td class="px-6 py-5 whitespace-nowrap license-detail-col hidden lg:table-cell">
                        <div class="text-sm font-bold text-gray-900 font-mono tracking-wider">
                            {{ $driver->license_number ?? 'N/A' }}
                        </div>
                        @if(isset($driver->license_expiry))
                            @php $is_license_expired = \Carbon\Carbon::parse($driver->license_expiry)->isPast(); @endphp
                            <div class="flex flex-col gap-1 mt-1">
                                <div
                                    class="text-[10px] font-semibold {{ $is_license_expired ? 'text-red-500' : 'text-gray-500' }}">
                                    EXP: {{ \Carbon\Carbon::parse($driver->license_expiry)->format('M d, Y') }}
                                </div>
                                @if($is_license_expired)
                                    <span
                                        class="inline-flex items-center gap-1 px-1.5 py-0.5 bg-red-100 text-red-600 text-[9px] font-black rounded uppercase tracking-widest border border-red-200 w-fit animate-pulse">
                                        <i data-lucide="alert-circle" class="w-2.5 h-2.5"></i> Expired
                                    </span>
                                @endif
                            </div>
                        @endif
                    </td>

                    {{-- Status --}}
                    <td class="px-3 md:px-6 py-4 md:py-5 whitespace-nowrap">
                        <div class="flex items-center gap-2">
                            @if($driver->driver_status === 'banned')
                                <div class="w-2 h-2 md:w-2.5 md:h-2.5 rounded-full bg-red-600 shadow-[0_0_8px_rgba(220,38,38,0.6)]"></div>
                                <span
                                    class="text-[10px] md:text-[11px] font-black uppercase tracking-widest text-red-600 flex items-center gap-1">
                                    <i data-lucide="ban" class="w-3.5 h-3.5 md:w-3 md:h-3"></i> Banned
                                </span>
                            @else
                                <span
                                    class="inline-flex items-center gap-1 px-1.5 py-0.5 md:px-2.5 md:py-1 rounded-full {{ $driver->is_active ? 'bg-green-50 text-green-700 border border-green-200' : 'bg-red-50 text-red-700 border border-red-200' }}">
                                    <div
                                        class="w-1.5 h-1.5 rounded-full {{ $driver->is_active ? 'bg-green-500 animate-pulse' : 'bg-red-500' }}">
                                    </div>
                                    <span class="text-[9px] md:text-[10px] font-black uppercase tracking-widest">
                                        {{ $driver->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </span>
                            @endif
                        </div>
                    </td>

                    {{-- Financial Target --}}
                    <td class="px-6 py-5 whitespace-nowrap financial-target-col hidden lg:table-cell">
                        <div class="flex flex-col gap-1.5">
                            <div class="flex items-center gap-2">
                                @if(!empty($driver->assigned_unit))
                                    <span
                                        class="text-lg font-black text-gray-900 tracking-tight">₱{{ number_format($driver->current_target ?? $driver->daily_boundary_target, 2) }}</span>
                                    <span
                                        class="text-[9px] bg-blue-50 text-blue-600 border border-blue-200 px-1.5 py-0.5 rounded font-bold uppercase tracking-widest">Unit</span>
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
                    <td class="px-3 md:px-6 py-4 md:py-5 whitespace-nowrap rating-col hidden lg:table-cell">
                        @php
                            $ratingData = $driver->performance_rating ?? ['label' => 'New Driver', 'stars' => 0];
                            $starsCount = $ratingData['stars'];
                            $label = $ratingData['label'];
                            $cfg = match($label) {
                                'Elite'     => ['color' => 'text-yellow-500', 'bg' => 'bg-yellow-50', 'star' => 'text-yellow-400'],
                                'Excellent' => ['color' => 'text-blue-600',   'bg' => 'bg-blue-50',   'star' => 'text-blue-500'],
                                'Good'      => ['color' => 'text-green-600',  'bg' => 'bg-green-50',  'star' => 'text-green-500'],
                                'Average'   => ['color' => 'text-slate-600',  'bg' => 'bg-slate-100', 'star' => 'text-slate-400'],
                                'Growing'   => ['color' => 'text-slate-500',  'bg' => 'bg-slate-50',  'star' => 'text-slate-400'],
                                'New Driver'=> ['color' => 'text-slate-400',  'bg' => 'bg-slate-50',  'star' => 'text-slate-200'],
                                'At Risk'   => ['color' => 'text-red-600',    'bg' => 'bg-red-50',    'star' => 'text-red-500'],
                                default     => ['color' => 'text-slate-400',  'bg' => 'bg-slate-50',  'star' => 'text-slate-300'],
                            };
                        @endphp
                        <div class="flex flex-col items-center gap-1">
                            <div class="flex items-center gap-0.5">
                                @for ($i = 1; $i <= 5; $i++)
                                    <i data-lucide="star" class="w-3 h-3 {{ ($starsCount > 0 && $i <= $starsCount) ? ($cfg['star'] . ' fill-current') : 'text-slate-200' }}"></i>
                                @endfor
                            </div>
                            <span class="text-[10px] font-bold uppercase tracking-wider {{ $cfg['color'] }} {{ $cfg['bg'] }} px-1.5 py-0.5 rounded-full border border-current/10">
                                {{ $label }}
                            </span>
                        </div>
                    </td>

                    {{-- Actions --}}
                    <td class="px-3 md:px-6 py-4 md:py-5 whitespace-nowrap text-right relative">
                        <button type="button"
                            class="p-1.5 md:p-2 text-gray-400 hover:text-gray-800 hover:bg-gray-200 rounded-full transition-colors focus:outline-none"
                            onclick="toggleDriverDropdown('dropdown-{{ $driver->id }}', event)" title="Actions">
                            <i data-lucide="more-vertical" class="w-4 h-4 md:w-5 md:h-5"></i>
                        </button>

                        <div id="dropdown-{{ $driver->id }}"
                            class="driver-action-dropdown hidden absolute right-8 mt-1 w-40 bg-white rounded-lg shadow-xl border border-gray-100 z-50 overflow-hidden transform transition-all">
                            <button type="button"
                                class="w-full text-left px-4 py-2.5 text-xs font-bold text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors flex items-center gap-2"
                                onclick="event.stopPropagation(); document.getElementById('dropdown-{{ $driver->id }}').classList.add('hidden'); openEditDriverModal({{ $driver->id }})">
                                <i data-lucide="edit-2" class="w-4 h-4"></i> Edit Driver
                            </button>
                            @if($driver->driver_status === 'banned')
                            <button type="button"
                                class="w-full text-left px-4 py-2.5 text-xs font-bold text-green-600 hover:bg-green-50 transition-colors flex items-center gap-2 border-t border-gray-50"
                                onclick="event.stopPropagation(); document.getElementById('dropdown-{{ $driver->id }}').classList.add('hidden'); unbanDriver({{ $driver->id }}, '{{ $driver->full_name }}')">
                                <i data-lucide="shield-check" class="w-4 h-4"></i> Unban Driver
                            </button>
                            @endif
                            <button type="button"
                                class="w-full text-left px-4 py-2.5 text-xs font-bold text-orange-600 hover:bg-orange-50 transition-colors flex items-center gap-2 border-t border-gray-50"
                                onclick="event.stopPropagation(); document.getElementById('dropdown-{{ $driver->id }}').classList.add('hidden'); deleteDriver({{ $driver->id }}, '{{ $driver->full_name }}')">
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
    <div
        class="px-8 py-6 bg-gray-50/50 border-t border-gray-100 flex flex-col md:flex-row items-center justify-between gap-4">
        <div class="text-[11px] font-bold text-gray-400 uppercase tracking-widest">
            Showing <span class="text-gray-900">{{ $pagination['total_items'] }}</span> results / Page <span
                class="text-gray-900">{{ $pagination['page'] }}</span> of <span
                class="text-gray-900">{{ $pagination['total_pages'] }}</span>
        </div>
        <div class="flex items-center gap-1.5">
            @if($pagination['has_prev'])
                <button onclick="changePage({{ $pagination['prev_page'] }})"
                    class="p-2.5 rounded-xl border border-gray-200 bg-white text-gray-600 hover:bg-gray-50 transition-all active:scale-90 shadow-sm">
                    <i data-lucide="chevron-left" class="w-5 h-5"></i>
                </button>
            @endif
            @for($i = max(1, $pagination['page'] - 2); $i <= min($pagination['total_pages'], $pagination['page'] + 2); $i++)
                <button onclick="changePage({{ $i }})"
                    class="w-10 h-10 rounded-xl border text-[11px] font-black transition-all {{ $i === $pagination['page'] ? 'bg-blue-600 border-blue-600 text-white shadow-md' : 'bg-white border-gray-200 text-gray-500 hover:bg-gray-50' }}">
                    {{ $i }}
                </button>
            @endfor
            @if($pagination['has_next'])
                <button onclick="changePage({{ $pagination['next_page'] }})"
                    class="p-2.5 rounded-xl border border-gray-200 bg-white text-gray-600 hover:bg-gray-50 transition-all active:scale-90 shadow-sm">
                    <i data-lucide="chevron-right" class="w-5 h-5"></i>
                </button>
            @endif
        </div>
    </div>
@endif

<script>
    // Define global functions to prevent re-declaration issues with AJAX
    window.toggleDriverDropdown = function (id, event) {
        event.stopPropagation(); // Prevent row click (which opens details)

        // Close all other dropdowns
        document.querySelectorAll('.driver-action-dropdown').forEach(el => {
            if (el.id !== id) {
                el.classList.add('hidden');
            }
        });

        // Toggle the target dropdown
        const dropdown = document.getElementById(id);
        if (dropdown) {
            dropdown.classList.toggle('hidden');
        }
    };

    // Attach document listener only once
    if (!window.driverDropdownListenerAdded) {
        document.addEventListener('click', function () {
            document.querySelectorAll('.driver-action-dropdown').forEach(el => {
                el.classList.add('hidden');
            });
        });
        window.driverDropdownListenerAdded = true;
    }

    window.unbanDriver = function (driverId, driverName) {
        if (!confirm('Are you sure you want to UNBAN ' + driverName + '?\nTheir status will be set back to Available.')) return;

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ||
                          document.querySelector('input[name="_token"]')?.value || '';

        fetch(`/driver-management/${driverId}/unban`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            }
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                if (typeof showNotification === 'function') {
                    showNotification(data.message, 'success');
                } else {
                    alert(data.message);
                }
                if (typeof loadDriversTable === 'function') {
                    loadDriversTable();
                } else {
                    window.location.reload();
                }
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(err => {
            console.error('Unban error:', err);
            alert('Failed to unban driver. Please try again.');
        });
    };

    // Responsive JavaScript layout listener
    window.adjustMobileTableColumns = function() {
        const isMobile = window.innerWidth < 1024;
        const licenseCols = document.querySelectorAll('.license-detail-col');
        const financialCols = document.querySelectorAll('.financial-target-col');
        const ratingCols = document.querySelectorAll('.rating-col');

        licenseCols.forEach(col => {
            if (isMobile) {
                col.classList.add('hidden');
            } else {
                col.classList.remove('hidden');
            }
        });

        financialCols.forEach(col => {
            if (isMobile) {
                col.classList.add('hidden');
            } else {
                col.classList.remove('hidden');
            }
        });

        ratingCols.forEach(col => {
            if (isMobile) {
                col.classList.add('hidden');
            } else {
                col.classList.remove('hidden');
            }
        });
    };

    // Attach resize listeners and run initial check
    window.addEventListener('resize', window.adjustMobileTableColumns);
    window.adjustMobileTableColumns();
    // Fire dynamic layout sync
    setTimeout(window.adjustMobileTableColumns, 150);
</script>