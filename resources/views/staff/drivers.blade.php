@extends('layouts.app')

@section('page-heading', 'Mobile App Drivers')
@section('page-subheading', 'Manage driver accounts registered on the mobile application')

@section('content')
<div class="space-y-6">
    <!-- Search Bar (Live) -->
    <div class="bg-white p-4 rounded-xl shadow-sm border">
        <div class="relative max-w-md">
            <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none"></i>
            <input type="text" id="appDriverSearchInput" value="{{ request('search') }}" 
                placeholder="Search mobile app drivers by name or email..." 
                autocomplete="new-password"
                spellcheck="false" autocorrect="off" autocapitalize="off"
                readonly onfocus="this.removeAttribute('readonly');"
                class="w-full pl-10 pr-10 py-2 border rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 outline-none text-sm transition-all">
            <button id="appDriverSearchClear" onclick="clearAppDriverSearch()" class="absolute right-3 top-1/2 -translate-y-1/2 hidden text-gray-400 hover:text-gray-600">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>
    </div>

    <!-- Drivers Table -->
    <div class="space-y-4">
        <div class="flex items-center gap-2">
            <div class="p-2 bg-green-100 rounded-lg">
                <i data-lucide="smartphone" class="w-5 h-5 text-green-600"></i>
            </div>
            <div>
                <h2 class="text-xl font-bold text-gray-900">Mobile App Drivers</h2>
                <p class="text-sm text-gray-500">Drivers with registered mobile accounts</p>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50 border-b">
                            <th class="px-6 py-4 text-sm font-semibold text-gray-600">Name</th>
                            <th class="px-6 py-4 text-sm font-semibold text-gray-600">Email/Phone</th>
                            <th class="px-6 py-4 text-sm font-semibold text-gray-600">IP Address</th>
                            <th class="px-6 py-4 text-sm font-semibold text-gray-600">Phone ID (Device)</th>
                            <th class="px-6 py-4 text-sm font-semibold text-gray-600 text-right">Status</th>
                            <th class="px-6 py-4 text-sm font-semibold text-gray-600 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y" id="appDriverTableBody">
                        @forelse($appDrivers as $driver)
                            @php
                                $latestBrowser = $driver->verifiedBrowsers->sortByDesc('last_active_at')->first();
                                $ip = $latestBrowser ? $latestBrowser->ip_address : '---';
                                $device = $latestBrowser ? ($latestBrowser->device_info ?? $latestBrowser->user_agent ?? 'Unknown Device') : '---';
                            @endphp
                        <tr class="hover:bg-gray-50 transition-colors cursor-pointer app-driver-row"
                            data-search-terms="{{ strtolower(($driver->full_name ?? $driver->name ?? '') . ' ' . ($driver->email ?? '') . ' ' . ($driver->phone ?? $driver->phone_number ?? '') . ' ' . $ip . ' ' . $device) }}"
                            onclick="viewAppDriver({{ json_encode($driver) }}, '{{ addslashes($ip) }}', '{{ addslashes($device) }}', event)">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-green-100 flex items-center justify-center text-green-700 font-bold text-xs uppercase">
                                        {{ substr($driver->full_name ?? $driver->name, 0, 1) }}
                                    </div>
                                    <span class="font-medium text-gray-900">{{ $driver->full_name ?? $driver->name }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600">
                                <div>{{ $driver->email ?? '---' }}</div>
                                <div class="text-xs text-gray-400">{{ $driver->phone ?? $driver->phone_number ?? '---' }}</div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600 font-mono">
                                {{ $ip }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600">
                                <div class="truncate max-w-[200px]" title="{{ $device }}">
                                    {{ $device }}
                                </div>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <span class="px-2.5 py-1 rounded-full text-xs font-medium {{ $driver->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                    {{ $driver->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right relative">
                                <div class="inline-block text-left">
                                    <button type="button" 
                                        onclick="toggleDriversDropdown('app-driver-dropdown-{{ $driver->id }}', event)"
                                        class="p-2 hover:bg-gray-100 rounded-full transition-colors focus:outline-none">
                                        <i data-lucide="more-vertical" class="w-4 h-4 text-gray-500"></i>
                                    </button>
                                    <div id="app-driver-dropdown-{{ $driver->id }}" 
                                        class="driver-action-dropdown absolute right-6 mt-1 w-32 bg-white border border-gray-100 rounded-xl shadow-xl z-50 hidden animate-in fade-in zoom-in-95 duration-200 overflow-hidden">
                                        <div class="p-1.5 space-y-1">
                                            <form action="{{ route('staff.destroyAppDriver', $driver->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to archive this Driver\'s Mobile App Account? They will lose access to the app immediately.')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="w-full flex items-center gap-2 px-3 py-2 text-xs font-bold text-red-600 hover:bg-red-50 rounded-lg transition-all text-left">
                                                    <i data-lucide="archive" class="w-3.5 h-3.5"></i>
                                                    Archive Account
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr id="appDriverEmptyRow">
                            <td colspan="6" class="px-6 py-8 text-center text-gray-500 text-sm italic">No app drivers found.</td>
                        </tr>
                        @endforelse
                        <!-- No search results row -->
                        <tr id="appDriverNoResultsRow" class="hidden">
                            <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                <i data-lucide="search-x" class="w-12 h-12 mx-auto mb-4 text-gray-300"></i>
                                <p class="font-medium">No results found</p>
                                <p class="text-xs mt-1">Try a different name or email.</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Mobile App Driver View Info Modal -->
<div id="viewAppDriverModal" class="hidden fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4 backdrop-blur-sm">
    <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-lg overflow-hidden">
        <!-- Header -->
        <div class="bg-slate-800 p-5">
            <div class="flex justify-between items-center">
                <div class="flex items-center gap-3">
                    <div id="viewAppDriverAvatar" class="w-11 h-11 rounded-full bg-green-500 flex items-center justify-center text-white font-black text-xl uppercase"></div>
                    <div>
                        <h3 id="viewAppDriverName" class="text-lg font-black text-white"></h3>
                        <p id="viewAppDriverSub" class="text-xs text-slate-300 uppercase tracking-widest capitalize">Mobile App Driver</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <span id="viewAppDriverBadge" class="px-3 py-1 rounded-full text-xs font-bold"></span>
                    <button type="button" onclick="closeAppDriverModal()" class="text-slate-400 hover:text-white bg-slate-700/50 hover:bg-slate-700 p-2 rounded-full transition-colors">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>
            </div>
        </div>
        <!-- Body -->
        <div class="p-6 space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <div class="bg-gray-50 rounded-xl p-4 border border-gray-100">
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1 flex items-center gap-1"><i data-lucide="mail" class="w-3 h-3"></i> Email</p>
                    <p id="viewAppDriverEmail" class="text-sm font-bold text-gray-800 break-all"></p>
                </div>
                <div class="bg-gray-50 rounded-xl p-4 border border-gray-100">
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1 flex items-center gap-1"><i data-lucide="phone" class="w-3 h-3"></i> Phone</p>
                    <p id="viewAppDriverPhone" class="text-sm font-bold text-gray-800"></p>
                </div>
            </div>
            <div class="bg-gray-50 rounded-xl p-4 border border-gray-100">
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1 flex items-center gap-1"><i data-lucide="globe" class="w-3 h-3"></i> Last Known IP Address</p>
                <p id="viewAppDriverIP" class="text-sm font-bold font-mono text-gray-800"></p>
            </div>
            <div class="bg-gray-50 rounded-xl p-4 border border-gray-100">
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1 flex items-center gap-1"><i data-lucide="smartphone" class="w-3 h-3"></i> Device Info</p>
                <p id="viewAppDriverDevice" class="text-sm font-bold text-gray-800"></p>
            </div>
        </div>
        <!-- Footer -->
        <div class="px-6 pb-5 flex justify-end gap-2">
            <button type="button" onclick="closeAppDriverModal()" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 text-sm font-bold transition-all">
                Close
            </button>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    // ─── Live Search ───────────────────────────────────────────────
    let appDriverSearchTimer;
    const appDriverSearchInput = document.getElementById('appDriverSearchInput');
    const appDriverSearchClear = document.getElementById('appDriverSearchClear');

    function filterAppDrivers() {
        const query = appDriverSearchInput.value.trim().toLowerCase();
        const rows = document.querySelectorAll('.app-driver-row');
        const noResultsRow = document.getElementById('appDriverNoResultsRow');
        let visibleCount = 0;

        rows.forEach(row => {
            const terms = row.getAttribute('data-search-terms') || '';
            if (!query || terms.includes(query)) {
                row.classList.remove('hidden');
                visibleCount++;
            } else {
                row.classList.add('hidden');
            }
        });

        if (noResultsRow) {
            noResultsRow.classList.toggle('hidden', visibleCount > 0 || rows.length === 0);
        }

        if (appDriverSearchClear) {
            appDriverSearchClear.classList.toggle('hidden', !query);
        }
    }

    function clearAppDriverSearch() {
        appDriverSearchInput.value = '';
        filterAppDrivers();
        appDriverSearchInput.focus();
    }

    if (appDriverSearchInput) {
        appDriverSearchInput.addEventListener('input', () => {
            clearTimeout(appDriverSearchTimer);
            appDriverSearchTimer = setTimeout(filterAppDrivers, 200);
        });
        if (appDriverSearchInput.value) filterAppDrivers();
    }

    // ─── App Driver View Popup ──────────────────────────────────────────
    function viewAppDriver(driver, ip, device, event) {
        // Ignore clicks on action buttons/dropdowns
        if (event && event.target.closest('.driver-action-dropdown, button, form')) return;

        const name = driver.full_name || driver.name || '—';
        const initial = name.charAt(0).toUpperCase();
        
        document.getElementById('viewAppDriverAvatar').textContent = initial;
        document.getElementById('viewAppDriverName').textContent = name;
        
        document.getElementById('viewAppDriverEmail').textContent = driver.email || '—';
        document.getElementById('viewAppDriverPhone').textContent = driver.phone || driver.phone_number || '—';
        document.getElementById('viewAppDriverIP').textContent = ip || '—';
        document.getElementById('viewAppDriverDevice').textContent = device || '—';

        const badge = document.getElementById('viewAppDriverBadge');
        if (driver.is_active) {
            badge.textContent = 'Active';
            badge.className = 'px-3 py-1 rounded-full text-xs font-bold bg-green-500 text-white';
        } else {
            badge.textContent = 'Inactive';
            badge.className = 'px-3 py-1 rounded-full text-xs font-bold bg-red-500 text-white';
        }

        document.getElementById('viewAppDriverModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        if (typeof lucide !== 'undefined') lucide.createIcons();
    }

    function closeAppDriverModal() {
        document.getElementById('viewAppDriverModal').classList.add('hidden');
        document.body.style.overflow = 'auto';
    }

    window.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            closeAppDriverModal();
        }
    });

    window.addEventListener('click', (e) => {
        if (e.target.id === 'viewAppDriverModal') closeAppDriverModal();
    });

    // ─── Dropdown Toggle Logic ─────────────────────────────────────────
    window.toggleDriversDropdown = function(id, event) {
        event.stopPropagation();
        
        document.querySelectorAll('.driver-action-dropdown').forEach(el => {
            if (el.id !== id) {
                el.classList.add('hidden');
            }
        });

        const dropdown = document.getElementById(id);
        if (dropdown) {
            dropdown.classList.toggle('hidden');
        }
    };

    document.addEventListener('click', function(e) {
        if (!e.target.closest('.relative')) {
            document.querySelectorAll('.driver-action-dropdown').forEach(el => {
                el.classList.add('hidden');
            });
        }
    });
</script>
@endpush

