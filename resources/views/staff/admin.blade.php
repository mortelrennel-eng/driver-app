@extends('layouts.app')

@section('page-heading', 'Admin Staff Records')
@section('page-subheading', 'Personnel with web system accounts')

@section('content')
<div class="space-y-6">
    <!-- Search Bar (Live) -->
    <div class="bg-white p-4 rounded-xl shadow-sm border">
        <div class="relative max-w-md">
            <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none"></i>
            <input type="text" id="adminSearchInput" value="{{ request('search') }}" 
                placeholder="Search admin staff by name, role, or email..." 
                autocomplete="new-password"
                spellcheck="false" autocorrect="off" autocapitalize="off"
                readonly onfocus="this.removeAttribute('readonly');"
                class="w-full pl-10 pr-10 py-2 border rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 outline-none text-sm transition-all">
            <button id="adminSearchClear" onclick="clearAdminSearch()" class="absolute right-3 top-1/2 -translate-y-1/2 hidden text-gray-400 hover:text-gray-600">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>
    </div>

    <!-- Admin Staff Table -->
    <div class="space-y-4">
        <div class="flex items-center gap-2">
            <div class="p-2 bg-blue-100 rounded-lg">
                <i data-lucide="shield-check" class="w-5 h-5 text-blue-600"></i>
            </div>
            <div>
                <h2 class="text-xl font-bold text-gray-900">Admin Staff</h2>
                <p class="text-sm text-gray-500">Personnel with web system accounts</p>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50 border-b">
                            <th class="px-6 py-4 text-sm font-semibold text-gray-600">Name</th>
                            <th class="px-6 py-4 text-sm font-semibold text-gray-600">Role</th>
                            <th class="px-6 py-4 text-sm font-semibold text-gray-600 text-right">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y" id="adminTableBody">
                        @forelse($adminStaff as $admin)
                        <tr class="hover:bg-gray-50 transition-colors cursor-pointer admin-row"
                            data-search-terms="{{ strtolower(($admin->full_name ?? $admin->name ?? '') . ' ' . $admin->role . ' ' . ($admin->email ?? '') . ' ' . ($admin->username ?? '') . ' ' . ($admin->phone ?? $admin->phone_number ?? '')) }}"
                            onclick="viewAdmin({{ json_encode($admin) }}, event)">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center text-blue-700 font-bold text-xs uppercase">
                                        {{ substr($admin->full_name ?? $admin->name ?? '?', 0, 1) }}
                                    </div>
                                    <span class="font-medium text-gray-900">{{ $admin->full_name ?? $admin->name ?? '—' }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600 capitalize">{{ str_replace('_', ' ', $admin->role) }}</td>
                            <td class="px-6 py-4 text-right">
                                <span class="px-2.5 py-1 rounded-full text-xs font-medium {{ $admin->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                    {{ $admin->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr id="adminEmptyRow">
                            <td colspan="3" class="px-6 py-8 text-center text-gray-500 text-sm italic">No admin staff found.</td>
                        </tr>
                        @endforelse
                        <!-- No search results row -->
                        <tr id="adminNoResultsRow" class="hidden">
                            <td colspan="3" class="px-6 py-12 text-center text-gray-500">
                                <i data-lucide="search-x" class="w-12 h-12 mx-auto mb-4 text-gray-300"></i>
                                <p class="font-medium">No results found</p>
                                <p class="text-xs mt-1">Try a different name, role, or email.</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Admin Staff View Info Modal -->
<div id="viewAdminModal" class="hidden fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4 backdrop-blur-sm">
    <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-lg overflow-hidden">
        <!-- Header -->
        <div class="bg-slate-800 p-5">
            <div class="flex justify-between items-center">
                <div class="flex items-center gap-3">
                    <div id="viewAdminAvatar" class="w-11 h-11 rounded-full bg-blue-500 flex items-center justify-center text-white font-black text-xl uppercase"></div>
                    <div>
                        <h3 id="viewAdminName" class="text-lg font-black text-white"></h3>
                        <p id="viewAdminRole" class="text-xs text-slate-300 uppercase tracking-widest capitalize"></p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <span id="viewAdminBadge" class="px-3 py-1 rounded-full text-xs font-bold"></span>
                    <button type="button" onclick="closeAdminModal()" class="text-slate-400 hover:text-white bg-slate-700/50 hover:bg-slate-700 p-2 rounded-full transition-colors">
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
                    <p id="viewAdminEmail" class="text-sm font-bold text-gray-800 break-all"></p>
                </div>
                <div class="bg-gray-50 rounded-xl p-4 border border-gray-100">
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1 flex items-center gap-1"><i data-lucide="phone" class="w-3 h-3"></i> Phone</p>
                    <p id="viewAdminPhone" class="text-sm font-bold text-gray-800"></p>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div class="bg-gray-50 rounded-xl p-4 border border-gray-100">
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1 flex items-center gap-1"><i data-lucide="user" class="w-3 h-3"></i> Username</p>
                    <p id="viewAdminUsername" class="text-sm font-bold text-gray-800"></p>
                </div>
                <div class="bg-gray-50 rounded-xl p-4 border border-gray-100">
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1 flex items-center gap-1"><i data-lucide="briefcase" class="w-3 h-3"></i> Role</p>
                    <p id="viewAdminRoleDetail" class="text-sm font-bold text-gray-800 capitalize"></p>
                </div>
            </div>
        </div>
        <!-- Footer -->
        <div class="px-6 pb-5 flex justify-end gap-2">
            <button type="button" onclick="closeAdminModal()" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 text-sm font-bold transition-all">
                Close
            </button>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    // ─── Live Search ───────────────────────────────────────────────
    let adminSearchTimer;
    const adminSearchInput = document.getElementById('adminSearchInput');
    const adminSearchClear = document.getElementById('adminSearchClear');

    function filterAdmin() {
        const query = adminSearchInput.value.trim().toLowerCase();
        const rows = document.querySelectorAll('.admin-row');
        const noResultsRow = document.getElementById('adminNoResultsRow');
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

        // Toggle no-results message
        if (noResultsRow) {
            noResultsRow.classList.toggle('hidden', visibleCount > 0 || rows.length === 0);
        }

        // Toggle clear button
        if (adminSearchClear) {
            adminSearchClear.classList.toggle('hidden', !query);
        }
    }

    function clearAdminSearch() {
        adminSearchInput.value = '';
        filterAdmin();
        adminSearchInput.focus();
    }

    if (adminSearchInput) {
        adminSearchInput.addEventListener('input', () => {
            clearTimeout(adminSearchTimer);
            adminSearchTimer = setTimeout(filterAdmin, 200);
        });
        if (adminSearchInput.value) filterAdmin();
    }

    // ─── Admin View Popup ──────────────────────────────────────────
    function viewAdmin(admin, event) {
        const name = admin.full_name || admin.name || '—';
        const initial = name.charAt(0).toUpperCase();
        
        document.getElementById('viewAdminAvatar').textContent = initial;
        document.getElementById('viewAdminName').textContent = name;
        
        let role = (admin.role || '—').replace(/_/g, ' ');
        document.getElementById('viewAdminRole').textContent = role;
        document.getElementById('viewAdminRoleDetail').textContent = role;
        
        document.getElementById('viewAdminEmail').textContent = admin.email || '—';
        document.getElementById('viewAdminPhone').textContent = admin.phone || admin.phone_number || '—';
        document.getElementById('viewAdminUsername').textContent = admin.username || '—';

        // Status badge
        const badge = document.getElementById('viewAdminBadge');
        if (admin.is_active) {
            badge.textContent = 'Active';
            badge.className = 'px-3 py-1 rounded-full text-xs font-bold bg-green-500 text-white';
        } else {
            badge.textContent = 'Inactive';
            badge.className = 'px-3 py-1 rounded-full text-xs font-bold bg-red-500 text-white';
        }

        document.getElementById('viewAdminModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        if (typeof lucide !== 'undefined') lucide.createIcons();
    }

    function closeAdminModal() {
        document.getElementById('viewAdminModal').classList.add('hidden');
        document.body.style.overflow = 'auto';
    }

    window.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            closeAdminModal();
        }
    });

    window.addEventListener('click', (e) => {
        if (e.target.id === 'viewAdminModal') closeAdminModal();
    });
</script>
@endpush
