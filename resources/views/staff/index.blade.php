@extends('layouts.app')

@section('page-heading', 'General Staff Records')
@section('page-subheading', 'Manage personnel without system accounts (Mechanics, Guards, etc.)')

@section('content')
<style>
    .modern-table-sep {
        border-collapse: separate;
        border-spacing: 0 0.6rem;
    }
    .modern-row {
        background-color: white;
        box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05);
        transition: all 0.2s ease-in-out;
    }
    .modern-row:hover {
        box-shadow: 0 10px 15px -3px rgba(234, 179, 8, 0.2), 0 4px 6px -2px rgba(234, 179, 8, 0.1);
        transform: translateY(-2px);
    }
    .modern-row td:first-child {
        border-top-left-radius: 0.75rem;
        border-bottom-left-radius: 0.75rem;
        border-left: 4px solid transparent;
    }
    .modern-row:hover td:first-child {
        border-left-color: #eab308;
    }
    .modern-row td:last-child {
        border-top-right-radius: 0.75rem;
        border-bottom-right-radius: 0.75rem;
    }
</style>
<div class="space-y-6">
    <!-- Search Bar (Live) -->
    <div class="bg-white p-4 rounded-xl shadow-sm border">
        <div class="relative max-w-md">
            <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none"></i>
            <input type="text" id="staffSearchInput" value="{{ request('search') }}"
                placeholder="Search general staff by name or role..."
                autocomplete="new-password"
                spellcheck="false" autocorrect="off" autocapitalize="off"
                readonly onfocus="this.removeAttribute('readonly');"
                class="w-full pl-10 pr-10 py-2 border rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 outline-none text-sm transition-all">
            <button id="staffSearchClear" onclick="clearStaffSearch()" class="absolute right-3 top-1/2 -translate-y-1/2 hidden text-gray-400 hover:text-gray-600">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>
    </div>

    <!-- General Staff Section -->
    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
                <div class="p-2 bg-yellow-100 rounded-lg">
                    <i data-lucide="users" class="w-5 h-5 text-yellow-600"></i>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-gray-900">General Staff</h2>
                    <p class="text-sm text-gray-500">Personnel records without system accounts (Mechanics, Guards, etc.)</p>
                </div>
            </div>
            <button onclick="openModal('addStaffModal')" class="flex items-center gap-2 bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2 rounded-lg transition-colors shadow-sm text-sm font-medium">
                <i data-lucide="plus" class="w-4 h-4"></i>
                <span>Add Record</span>
            </button>
        </div>

        <div class="overflow-x-auto bg-gray-50/50 px-4 py-2 rounded-xl border border-gray-100">
            <table class="w-full text-left modern-table-sep">
                <thead>
                    <tr>
                        <th class="px-6 py-2 text-[10px] font-black text-gray-400 uppercase tracking-widest">Name</th>
                        <th class="px-6 py-2 text-[10px] font-black text-gray-400 uppercase tracking-widest">Role</th>
                        <th class="px-6 py-2 text-[10px] font-black text-gray-400 uppercase tracking-widest">Phone</th>
                        <th class="px-6 py-2 text-[10px] font-black text-gray-400 uppercase tracking-widest">Status</th>
                        <th class="px-6 py-2 text-[10px] font-black text-gray-400 uppercase tracking-widest text-right">Actions</th>
                    </tr>
                </thead>
                <tbody id="staffTableBody">
                    @forelse($generalStaff as $member)
                    <tr class="modern-row group cursor-pointer staff-row"
                        data-search-terms="{{ strtolower($member->name . ' ' . $member->role . ' ' . ($member->phone ?? '') . ' ' . ($member->address ?? '')) }}"
                        onclick="viewStaff({{ json_encode($member) }}, event)">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-yellow-100 flex items-center justify-center text-yellow-700 font-bold text-xs uppercase">
                                    {{ substr($member->name, 0, 1) }}
                                </div>
                                <span class="font-medium text-gray-900">{{ $member->name }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600 capitalize">{{ $member->role }}</td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $member->phone ?? '---' }}</td>
                        <td class="px-6 py-4">
                            <span class="px-2.5 py-1 rounded-full text-xs font-medium {{ $member->status === 'active' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700' }}">
                                {{ ucfirst($member->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right relative">
                            <div class="inline-block text-left">
                                <button type="button"
                                    onclick="toggleStaffDropdown('staff-dropdown-{{ $member->id }}', event)"
                                    class="p-2 hover:bg-gray-100 rounded-full transition-colors focus:outline-none">
                                    <i data-lucide="more-vertical" class="w-4 h-4 text-gray-500"></i>
                                </button>
                                <div id="staff-dropdown-{{ $member->id }}"
                                    class="staff-action-dropdown absolute right-6 mt-1 w-32 bg-white border border-gray-100 rounded-xl shadow-xl z-50 hidden animate-in fade-in zoom-in-95 duration-200 overflow-hidden">
                                    <div class="p-1.5 space-y-1">
                                        <button onclick="editStaff({{ json_encode($member) }})" class="w-full flex items-center gap-2 px-3 py-2 text-xs font-bold text-gray-600 hover:bg-yellow-50 hover:text-yellow-700 rounded-lg transition-all text-left">
                                            <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                                            Edit Record
                                        </button>
                                        <form class="staff-destroy-form" action="{{ route('staff.destroy', $member->id) }}" method="POST" onsubmit="return confirm('Archive this staff record?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="w-full flex items-center gap-2 px-3 py-2 text-xs font-bold text-red-600 hover:bg-red-50 rounded-lg transition-all text-left">
                                                <i data-lucide="archive" class="w-3.5 h-3.5"></i>
                                                Archive
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr id="staffEmptyRow">
                        <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                            <i data-lucide="users-2" class="w-12 h-12 mx-auto mb-4 text-gray-300"></i>
                            <p>No general staff records found.</p>
                        </td>
                    </tr>
                    @endforelse
                    <!-- No search results row (hidden by default) -->
                    <tr id="staffNoResultsRow" class="hidden">
                        <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                            <i data-lucide="search-x" class="w-12 h-12 mx-auto mb-4 text-gray-300"></i>
                            <p class="font-medium">No results found</p>
                            <p class="text-xs mt-1">Try a different name or role keyword.</p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Modal -->
<div id="addStaffModal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-black/50 flex items-center justify-center p-4 backdrop-blur-sm transition-all duration-300">
    <div class="relative bg-white rounded-2xl shadow-2xl max-w-3xl w-full max-h-[90vh] overflow-hidden flex flex-col">
        <div class="bg-slate-800 p-5 shrink-0">
            <div class="flex justify-between items-center">
                <div class="flex items-center gap-3">
                    <div class="p-2.5 bg-white/20 rounded-xl flex items-center justify-center">
                        <i data-lucide="user-plus" class="w-6 h-6 text-yellow-500"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-black text-white tracking-wide">Add Staff Record</h3>
                        <p class="text-xs font-medium text-slate-300 mt-0.5 uppercase tracking-widest">Staff Management</p>
                    </div>
                </div>
                <button type="button" onclick="closeModal('addStaffModal')" class="text-slate-400 hover:text-white bg-slate-700/50 hover:bg-slate-700 p-2 rounded-full transition-colors">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
        </div>

        <form action="{{ route('staff.store') }}" method="POST" class="flex flex-col flex-1 overflow-hidden">
            @csrf
            <div class="p-8 overflow-y-auto flex-1 space-y-6 custom-scrollbar">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-1.5">
                        <label class="text-[11px] font-black text-gray-400 uppercase tracking-widest ml-1">Full Name *</label>
                        <div class="relative">
                            <i data-lucide="user" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"></i>
                            <input type="text" name="name" id="add_name" maxlength="20" required class="w-full pl-10 pr-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 focus:outline-none font-bold text-sm text-gray-700">
                        </div>
                    </div>
                    
                    <div class="space-y-1.5">
                        <label class="text-[11px] font-black text-gray-400 uppercase tracking-widest ml-1">Phone Number</label>
                        <div class="relative">
                            <i data-lucide="phone" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"></i>
                            <input type="text" name="phone" id="add_phone" maxlength="11" class="w-full pl-10 pr-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 focus:outline-none font-bold text-sm text-gray-700">
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="space-y-1.5">
                        <label class="text-[11px] font-black text-gray-400 uppercase tracking-widest ml-1">Role *</label>
                        <div class="relative">
                            <i data-lucide="briefcase" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"></i>
                            <select name="role" id="add_role_select" onchange="toggleCustomRole('addStaffModal', this.value)" required class="w-full pl-10 pr-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 focus:outline-none font-bold text-sm text-gray-700 appearance-none">
                                <option value="">Select Role</option>
                                <option value="Mechanic">Mechanic</option>
                                <option value="Guard">Guard</option>
                                <option value="Others">Others</option>
                            </select>
                            <i data-lucide="chevron-down" class="absolute right-4 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none"></i>
                        </div>
                        <div id="add_custom_role_container" class="hidden mt-3 space-y-1.5">
                            <label class="text-[11px] font-black text-yellow-600 uppercase tracking-widest ml-1">Specify Role *</label>
                            <div class="relative">
                                <i data-lucide="edit-3" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-yellow-600"></i>
                                <input type="text" id="add_custom_role" placeholder="Enter role (letters only)" class="w-full pl-10 pr-4 py-2.5 bg-yellow-50 border border-yellow-200 rounded-xl focus:ring-2 focus:ring-yellow-500 focus:outline-none font-bold text-sm text-yellow-700">
                            </div>
                        </div>
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-[11px] font-black text-gray-400 uppercase tracking-widest ml-1">Emergency Contact Name</label>
                        <div class="relative">
                            <i data-lucide="user-check" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"></i>
                            <input type="text" name="contact_person" id="add_contact_person" maxlength="20" class="w-full pl-10 pr-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 focus:outline-none font-bold text-sm text-gray-700" placeholder="Contact person name">
                        </div>
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-[11px] font-black text-gray-400 uppercase tracking-widest ml-1">Emergency Contact Number</label>
                        <div class="relative">
                            <i data-lucide="phone-forwarded" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"></i>
                            <input type="text" name="emergency_phone" id="add_emergency_phone" maxlength="11" class="w-full pl-10 pr-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 focus:outline-none font-bold text-sm text-gray-700" placeholder="Contact person phone">
                        </div>
                    </div>
                </div>

                <div class="space-y-1.5">
                    <label class="text-[11px] font-black text-gray-400 uppercase tracking-widest ml-1">Address</label>
                    <div class="relative">
                        <i data-lucide="map-pin" class="absolute left-3 top-3 w-4 h-4 text-gray-400"></i>
                        <textarea name="address" id="add_address" maxlength="200" rows="2" class="w-full pl-10 pr-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 focus:outline-none font-bold text-sm text-gray-700" placeholder="Full residential address"></textarea>
                    </div>
                </div>

                <div class="space-y-3 flex flex-col items-center py-2">
                    <label class="text-[11px] font-black text-gray-400 uppercase tracking-widest">Status</label>
                    <div class="grid grid-cols-2 gap-3 max-w-md w-full">
                        <label class="relative cursor-pointer group">
                            <input type="radio" name="status" value="active" checked class="peer sr-only">
                            <div class="p-2.5 flex items-center justify-center gap-2 border-2 border-gray-100 rounded-xl peer-checked:border-green-500 peer-checked:bg-green-50 transition-all hover:bg-gray-50">
                                <div class="w-2 h-2 rounded-full bg-gray-300 peer-checked:bg-green-500"></div>
                                <span class="text-sm font-black uppercase text-gray-500 peer-checked:text-green-700 tracking-wider">Active</span>
                            </div>
                        </label>
                        <label class="relative cursor-pointer group">
                            <input type="radio" name="status" value="inactive" class="peer sr-only">
                            <div class="p-2.5 flex items-center justify-center gap-2 border-2 border-gray-100 rounded-xl peer-checked:border-red-500 peer-checked:bg-red-50 transition-all hover:bg-gray-50">
                                <div class="w-2 h-2 rounded-full bg-gray-300 peer-checked:bg-red-500"></div>
                                <span class="text-sm font-black uppercase text-gray-500 peer-checked:text-red-700 tracking-wider">Inactive</span>
                            </div>
                        </label>
                    </div>
                </div>
            </div>

            <div class="p-4 border-t flex justify-end gap-3 shadow-inner bg-gray-50 shrink-0">
                <button type="button" onclick="closeModal('addStaffModal')" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 text-sm font-bold transition-all">
                    Cancel
                </button>
                <button type="submit" class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 text-sm font-bold shadow-lg shadow-green-200/50 transition-all flex items-center gap-2">
                    <i data-lucide="check" class="w-4 h-4"></i> Save Record
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Modal -->
<div id="editStaffModal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-black/50 flex items-center justify-center p-4 backdrop-blur-sm transition-all duration-300">
    <div class="relative bg-white rounded-2xl shadow-2xl max-w-3xl w-full max-h-[90vh] overflow-hidden flex flex-col">
        <div class="bg-slate-800 p-5 shrink-0">
            <div class="flex justify-between items-center">
                <div class="flex items-center gap-3">
                    <div class="p-2.5 bg-white/20 rounded-xl flex items-center justify-center">
                        <i data-lucide="user-cog" class="w-6 h-6 text-yellow-500"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-black text-white tracking-wide">Edit Staff Record</h3>
                        <p class="text-xs font-medium text-slate-300 mt-0.5 uppercase tracking-widest">Staff Management</p>
                    </div>
                </div>
                <button type="button" onclick="closeModal('editStaffModal')" class="text-slate-400 hover:text-white bg-slate-700/50 hover:bg-slate-700 p-2 rounded-full transition-colors">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
        </div>

        <form id="editStaffForm" method="POST" class="flex flex-col flex-1 overflow-hidden">
            @csrf
            @method('PUT')
            <div class="p-8 overflow-y-auto flex-1 space-y-6 custom-scrollbar">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-1.5">
                        <label class="text-[11px] font-black text-gray-400 uppercase tracking-widest ml-1">Full Name *</label>
                        <div class="relative">
                            <i data-lucide="user" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"></i>
                            <input type="text" name="name" id="edit_name" maxlength="20" required class="w-full pl-10 pr-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 focus:outline-none font-bold text-sm text-gray-700">
                        </div>
                    </div>
                    
                    <div class="space-y-1.5">
                        <label class="text-[11px] font-black text-gray-400 uppercase tracking-widest ml-1">Phone Number</label>
                        <div class="relative">
                            <i data-lucide="phone" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"></i>
                            <input type="text" name="phone" id="edit_phone" maxlength="11" class="w-full pl-10 pr-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 focus:outline-none font-bold text-sm text-gray-700">
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="space-y-1.5">
                        <label class="text-[11px] font-black text-gray-400 uppercase tracking-widest ml-1">Role *</label>
                        <div class="relative">
                            <i data-lucide="briefcase" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"></i>
                            <select name="role" id="edit_role_select" onchange="toggleCustomRole('editStaffModal', this.value)" required class="w-full pl-10 pr-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 focus:outline-none font-bold text-sm text-gray-700 appearance-none">
                                <option value="Mechanic">Mechanic</option>
                                <option value="Guard">Guard</option>
                                <option value="Others">Others</option>
                            </select>
                            <i data-lucide="chevron-down" class="absolute right-4 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none"></i>
                        </div>
                        <div id="edit_custom_role_container" class="hidden mt-3 space-y-1.5">
                            <label class="text-[11px] font-black text-yellow-600 uppercase tracking-widest ml-1">Specify Role *</label>
                            <div class="relative">
                                <i data-lucide="edit-3" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-yellow-600"></i>
                                <input type="text" id="edit_custom_role" placeholder="Enter role (letters only)" class="w-full pl-10 pr-4 py-2.5 bg-yellow-50 border border-yellow-200 rounded-xl focus:ring-2 focus:ring-yellow-500 focus:outline-none font-bold text-sm text-yellow-700">
                            </div>
                        </div>
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-[11px] font-black text-gray-400 uppercase tracking-widest ml-1">Emergency Contact Name</label>
                        <div class="relative">
                            <i data-lucide="user-check" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"></i>
                            <input type="text" name="contact_person" id="edit_contact_person" maxlength="20" class="w-full pl-10 pr-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 focus:outline-none font-bold text-sm text-gray-700">
                        </div>
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-[11px] font-black text-gray-400 uppercase tracking-widest ml-1">Emergency Contact Number</label>
                        <div class="relative">
                            <i data-lucide="phone-forwarded" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"></i>
                            <input type="text" name="emergency_phone" id="edit_emergency_phone" maxlength="11" class="w-full pl-10 pr-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 focus:outline-none font-bold text-sm text-gray-700">
                        </div>
                    </div>
                </div>

                <div class="space-y-1.5">
                    <label class="text-[11px] font-black text-gray-400 uppercase tracking-widest ml-1">Address</label>
                    <div class="relative">
                        <i data-lucide="map-pin" class="absolute left-3 top-3 w-4 h-4 text-gray-400"></i>
                        <textarea name="address" id="edit_address" maxlength="200" rows="2" class="w-full pl-10 pr-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 focus:outline-none font-bold text-sm text-gray-700"></textarea>
                    </div>
                </div>

                <div class="space-y-3 flex flex-col items-center py-2">
                    <label class="text-[11px] font-black text-gray-400 uppercase tracking-widest">Status</label>
                    <div class="grid grid-cols-2 gap-3 max-w-md w-full">
                        <label class="relative cursor-pointer group">
                            <input type="radio" name="status" id="edit_status_active" value="active" class="peer sr-only">
                            <div class="p-2.5 flex items-center justify-center gap-2 border-2 border-gray-100 rounded-xl peer-checked:border-green-500 peer-checked:bg-green-50 transition-all hover:bg-gray-50">
                                <div class="w-2 h-2 rounded-full bg-gray-300 peer-checked:bg-green-500"></div>
                                <span class="text-sm font-black uppercase text-gray-500 peer-checked:text-green-700 tracking-wider">Active</span>
                            </div>
                        </label>
                        <label class="relative cursor-pointer group">
                            <input type="radio" name="status" id="edit_status_inactive" value="inactive" class="peer sr-only">
                            <div class="p-2.5 flex items-center justify-center gap-2 border-2 border-gray-100 rounded-xl peer-checked:border-red-500 peer-checked:bg-red-50 transition-all hover:bg-gray-50">
                                <div class="w-2 h-2 rounded-full bg-gray-300 peer-checked:bg-red-500"></div>
                                <span class="text-sm font-black uppercase text-gray-500 peer-checked:text-red-700 tracking-wider">Inactive</span>
                            </div>
                        </label>
                    </div>
                </div>
            </div>

            <div class="p-4 border-t flex justify-end gap-3 shadow-inner bg-gray-50 shrink-0">
                <button type="button" onclick="closeModal('editStaffModal')" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 text-sm font-bold transition-all">
                    Cancel
                </button>
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-bold shadow-lg shadow-blue-200/50 transition-all flex items-center gap-2">
                    <i data-lucide="check" class="w-4 h-4"></i> Update Record
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Staff View Info Modal -->
<div id="viewStaffModal" class="hidden fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4 backdrop-blur-sm">
    <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-lg overflow-hidden">
        <!-- Header -->
        <div class="bg-slate-800 p-5">
            <div class="flex justify-between items-center">
                <div class="flex items-center gap-3">
                    <div id="viewStaffAvatar" class="w-11 h-11 rounded-full bg-yellow-400 flex items-center justify-center text-white font-black text-xl uppercase"></div>
                    <div>
                        <h3 id="viewStaffName" class="text-lg font-black text-white"></h3>
                        <p id="viewStaffRole" class="text-xs text-slate-300 uppercase tracking-widest capitalize"></p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <span id="viewStaffBadge" class="px-3 py-1 rounded-full text-xs font-bold"></span>
                    <button type="button" onclick="closeModal('viewStaffModal')" class="text-slate-400 hover:text-white bg-slate-700/50 hover:bg-slate-700 p-2 rounded-full transition-colors">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>
            </div>
        </div>
        <!-- Body -->
        <div class="p-6 space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <div class="bg-gray-50 rounded-xl p-4">
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Phone Number</p>
                    <p id="viewStaffPhone" class="text-sm font-bold text-gray-800"></p>
                </div>
                <div class="bg-gray-50 rounded-xl p-4">
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Role</p>
                    <p id="viewStaffRoleDetail" class="text-sm font-bold text-gray-800 capitalize"></p>
                </div>
            </div>
            <div class="bg-gray-50 rounded-xl p-4">
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Emergency Contact</p>
                <p id="viewStaffContact" class="text-sm font-bold text-gray-800"></p>
                <p id="viewStaffEmergencyPhone" class="text-xs text-gray-500 mt-0.5"></p>
            </div>
            <div class="bg-gray-50 rounded-xl p-4">
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Address</p>
                <p id="viewStaffAddress" class="text-sm font-bold text-gray-800 leading-relaxed"></p>
            </div>
        </div>
        <!-- Footer -->
        <div class="px-6 pb-5 flex justify-end gap-2">
            <button type="button" onclick="closeModal('viewStaffModal')" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 text-sm font-bold transition-all">
                Close
            </button>
            <button type="button" id="viewStaffEditBtn" class="px-4 py-2 bg-yellow-500 hover:bg-yellow-600 text-white rounded-lg text-sm font-bold transition-all flex items-center gap-2">
                <i data-lucide="edit-3" class="w-4 h-4"></i> Edit Record
            </button>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    // ─── Live Search ───────────────────────────────────────────────
    let staffSearchTimer;
    const staffSearchInput = document.getElementById('staffSearchInput');
    const staffSearchClear = document.getElementById('staffSearchClear');

    function filterStaff() {
        const query = staffSearchInput.value.trim().toLowerCase();
        const rows = document.querySelectorAll('.staff-row');
        const noResultsRow = document.getElementById('staffNoResultsRow');
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
        if (staffSearchClear) {
            staffSearchClear.classList.toggle('hidden', !query);
        }
    }

    function clearStaffSearch() {
        staffSearchInput.value = '';
        filterStaff();
        staffSearchInput.focus();
    }

    if (staffSearchInput) {
        staffSearchInput.addEventListener('input', () => {
            clearTimeout(staffSearchTimer);
            staffSearchTimer = setTimeout(filterStaff, 200);
        });
        // Run on load if there's a pre-filled value (e.g. from query string)
        if (staffSearchInput.value) filterStaff();
    }

    // ─── Staff View Popup ──────────────────────────────────────────
    let _currentViewedStaff = null;

    function viewStaff(member, event) {
        // Ignore clicks on the action buttons area
        if (event && event.target.closest('.staff-action-dropdown, button, form')) return;

        _currentViewedStaff = member;

        const initial = (member.name || '?').charAt(0).toUpperCase();
        document.getElementById('viewStaffAvatar').textContent = initial;
        document.getElementById('viewStaffName').textContent = member.name || '—';
        document.getElementById('viewStaffRole').textContent = member.role || '—';
        document.getElementById('viewStaffRoleDetail').textContent = member.role || '—';
        document.getElementById('viewStaffPhone').textContent = member.phone || '---';

        // Emergency contact
        const contact = member.contact_person || '---';
        const emergencyPhone = member.emergency_phone ? '📞 ' + member.emergency_phone : '';
        document.getElementById('viewStaffContact').textContent = contact;
        document.getElementById('viewStaffEmergencyPhone').textContent = emergencyPhone;

        document.getElementById('viewStaffAddress').textContent = member.address || 'No address on record.';

        // Status badge
        const badge = document.getElementById('viewStaffBadge');
        if (member.status === 'active') {
            badge.textContent = 'Active';
            badge.className = 'px-3 py-1 rounded-full text-xs font-bold bg-green-500 text-white';
        } else {
            badge.textContent = 'Inactive';
            badge.className = 'px-3 py-1 rounded-full text-xs font-bold bg-gray-400 text-white';
        }

        // Wire up Edit button inside view modal
        document.getElementById('viewStaffEditBtn').onclick = () => {
            closeModal('viewStaffModal');
            editStaff(member);
        };

        openModal('viewStaffModal');
        if (typeof lucide !== 'undefined') lucide.createIcons();
    }

    function openModal(id) {
        document.getElementById(id).classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeModal(id) {
        document.getElementById(id).classList.add('hidden');
        document.body.style.overflow = 'auto';
    }

    function toggleCustomRole(modalId, value) {
        const prefix = modalId === 'addStaffModal' ? 'add' : 'edit';
        const container = document.getElementById(`${prefix}_custom_role_container`);
        const input = document.getElementById(`${prefix}_custom_role`);

        if (value === 'Others') {
            container.classList.remove('hidden');
            input.required = true;
        } else {
            container.classList.add('hidden');
            input.required = false;
            input.value = '';
        }
    }

    function validateNameInput(input) {
        let val = input.value.replace(/[^A-Za-z\s]/g, '');
        const spaceCount = (val.match(/ /g) || []).length;
        if (spaceCount > 5) {
            let parts = val.split(' ');
            val = parts.slice(0, 6).join(' ') + parts.slice(6).join('');
        }
        input.value = val;
    }

    function validatePhoneInput(input) {
        input.value = input.value.replace(/\D/g, '');
    }

    document.addEventListener('DOMContentLoaded', function() {
        // Name validation only for actual name/contact fields (NOT custom role)
        const nameInputs = ['add_name', 'edit_name', 'add_contact_person', 'edit_contact_person'];
        const phoneInputs = ['add_phone', 'edit_phone', 'add_emergency_phone', 'edit_emergency_phone'];

        nameInputs.forEach(id => {
            const el = document.getElementById(id);
            if (el) el.addEventListener('input', () => validateNameInput(el));
        });

        phoneInputs.forEach(id => {
            const el = document.getElementById(id);
            if (el) el.addEventListener('input', () => validatePhoneInput(el));
        });
    });

    // Attach submit validation ONLY to the staff create/edit forms, NOT destroy forms
    ['addStaffModal', 'editStaffForm'].forEach(formId => {
        const form = formId === 'addStaffModal'
            ? document.querySelector('#addStaffModal form')
            : document.getElementById('editStaffForm');

        if (!form) return;

        form.addEventListener('submit', (e) => {
            form.querySelectorAll('input[type="text"], textarea').forEach(input => {
                input.value = input.value.trim();
            });

            const prefix = form.id === 'editStaffForm' ? 'edit' : 'add';
            const select = document.getElementById(`${prefix}_role_select`);
            const customInput = document.getElementById(`${prefix}_custom_role`);

            if (select && select.value === 'Others') {
                if (!customInput.value.trim()) {
                    alert('Please enter a role name.');
                    e.preventDefault();
                    return;
                }
                // Replace select value with custom role text before submitting
                const tempOption = document.createElement('option');
                tempOption.value = customInput.value.trim();
                tempOption.text = customInput.value.trim();
                tempOption.selected = true;
                select.add(tempOption);
                select.value = customInput.value.trim();
            }
        });
    });

    function editStaff(member) {
        document.querySelectorAll('.staff-action-dropdown').forEach(el => el.classList.add('hidden'));
        
        document.getElementById('edit_name').value = member.name;
        document.getElementById('edit_phone').value = member.phone || '';
        document.getElementById('edit_contact_person').value = member.contact_person || '';
        document.getElementById('edit_emergency_phone').value = member.emergency_phone || '';
        document.getElementById('edit_address').value = member.address || '';
        
        const roleSelect = document.getElementById('edit_role_select');
        const customContainer = document.getElementById('edit_custom_role_container');
        const customInput = document.getElementById('edit_custom_role');

        const standardRoles = ['Mechanic', 'Guard'];
        if (standardRoles.includes(member.role)) {
            roleSelect.value = member.role;
            customContainer.classList.add('hidden');
            customInput.value = '';
        } else {
            roleSelect.value = 'Others';
            customContainer.classList.remove('hidden');
            customInput.value = member.role;
        }

        if (member.status === 'active') {
            document.getElementById('edit_status_active').checked = true;
        } else {
            document.getElementById('edit_status_inactive').checked = true;
        }

        document.getElementById('editStaffForm').action = `/staff/${member.id}`;
        openModal('editStaffModal');
    }

    window.toggleStaffDropdown = function(id, event) {
        event.stopPropagation();
        
        document.querySelectorAll('.staff-action-dropdown').forEach(el => {
            if (el.id !== id) {
                el.classList.add('hidden');
                const row = el.closest('tr');
                if (row) {
                    row.style.zIndex = '';
                    row.style.position = '';
                }
            }
        });

        const dropdown = document.getElementById(id);
        const row = dropdown ? dropdown.closest('tr') : null;

        if (dropdown) {
            const isHidden = dropdown.classList.contains('hidden');
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

    document.addEventListener('click', function() {
        document.querySelectorAll('.staff-action-dropdown').forEach(el => {
            el.classList.add('hidden');
            const row = el.closest('tr');
            if (row) {
                row.style.zIndex = '';
                row.style.position = '';
            }
        });
    });

    window.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            closeModal('addStaffModal');
            closeModal('editStaffModal');
            closeModal('viewStaffModal');
        }
    });

    window.addEventListener('click', (e) => {
        if (e.target.id === 'addStaffModal') closeModal('addStaffModal');
        if (e.target.id === 'editStaffModal') closeModal('editStaffModal');
        if (e.target.id === 'viewStaffModal') closeModal('viewStaffModal');
    });
</script>
@endpush

