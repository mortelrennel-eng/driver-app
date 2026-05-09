<?php $__env->startSection('page-heading', 'Staff Records'); ?>
<?php $__env->startSection('page-subheading', 'Manage non-account staff like mechanics and guards'); ?>

<?php $__env->startSection('content'); ?>
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
    <!-- Search Bar -->
    <div class="bg-white p-4 rounded-xl shadow-sm border">
        <form action="<?php echo e(route('staff.index')); ?>" method="GET" class="relative max-w-md">
            <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"></i>
            <input type="text" name="search" value="<?php echo e(request('search')); ?>" 
                placeholder="Search staff by name or role..." 
                class="w-full pl-10 pr-4 py-2 border rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 outline-none text-sm">
        </form>
    </div>

    <!-- Admin Staff Section -->
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
                    <tbody class="divide-y">
                        <?php $__empty_1 = true; $__currentLoopData = $adminStaff; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $admin): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center text-blue-700 font-bold text-xs uppercase">
                                        <?php echo e(substr($admin->full_name, 0, 1)); ?>

                                    </div>
                                    <span class="font-medium text-gray-900"><?php echo e($admin->full_name); ?></span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600 capitalize"><?php echo e($admin->role); ?></td>
                            <td class="px-6 py-4 text-right">
                                <span class="px-2.5 py-1 rounded-full text-xs font-medium <?php echo e($admin->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'); ?>">
                                    <?php echo e($admin->is_active ? 'Active' : 'Inactive'); ?>

                                </span>
                            </td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="3" class="px-6 py-8 text-center text-gray-500 text-sm italic">No admin staff found.</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- General Staff Section -->
    <div class="space-y-4 pt-4">
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
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $generalStaff; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $member): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr class="modern-row group cursor-pointer">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-yellow-100 flex items-center justify-center text-yellow-700 font-bold text-xs uppercase">
                                        <?php echo e(substr($member->name, 0, 1)); ?>

                                    </div>
                                    <span class="font-medium text-gray-900"><?php echo e($member->name); ?></span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600 capitalize"><?php echo e($member->role); ?></td>
                            <td class="px-6 py-4 text-sm text-gray-600"><?php echo e($member->phone ?? '---'); ?></td>
                            <td class="px-6 py-4">
                                <span class="px-2.5 py-1 rounded-full text-xs font-medium <?php echo e($member->status === 'active' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700'); ?>">
                                    <?php echo e(ucfirst($member->status)); ?>

                                </span>
                            </td>
                            <td class="px-6 py-4 text-right relative">
                                <div class="inline-block text-left">
                                    <button type="button" 
                                        onclick="toggleStaffDropdown('staff-dropdown-<?php echo e($member->id); ?>', event)"
                                        class="p-2 hover:bg-gray-100 rounded-full transition-colors focus:outline-none">
                                        <i data-lucide="more-vertical" class="w-4 h-4 text-gray-500"></i>
                                    </button>
                                    <div id="staff-dropdown-<?php echo e($member->id); ?>" 
                                        class="staff-action-dropdown absolute right-6 mt-1 w-32 bg-white border border-gray-100 rounded-xl shadow-xl z-50 hidden animate-in fade-in zoom-in-95 duration-200 overflow-hidden">
                                        <div class="p-1.5 space-y-1">
                                            <button onclick="editStaff(<?php echo e(json_encode($member)); ?>)" class="w-full flex items-center gap-2 px-3 py-2 text-xs font-bold text-gray-600 hover:bg-yellow-50 hover:text-yellow-700 rounded-lg transition-all text-left">
                                                <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                                                Edit Record
                                            </button>
                                            <form action="<?php echo e(route('staff.destroy', $member->id)); ?>" method="POST" onsubmit="return confirm('Archive this staff record?')">
                                                <?php echo csrf_field(); ?>
                                                <?php echo method_field('DELETE'); ?>
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
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                                <i data-lucide="users-2" class="w-12 h-12 mx-auto mb-4 text-gray-300"></i>
                                <p>No general staff records found.</p>
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
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

        <form action="<?php echo e(route('staff.store')); ?>" method="POST" class="flex flex-col flex-1 overflow-hidden">
            <?php echo csrf_field(); ?>
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
                                <input type="text" id="add_custom_role" placeholder="Enter role (letters only)" oninput="validateTextOnly(this)" class="w-full pl-10 pr-4 py-2.5 bg-yellow-50 border border-yellow-200 rounded-xl focus:ring-2 focus:ring-yellow-500 focus:outline-none font-bold text-sm text-yellow-700">
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
            <?php echo csrf_field(); ?>
            <?php echo method_field('PUT'); ?>
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
                                <input type="text" id="edit_custom_role" placeholder="Enter role (letters only)" oninput="validateTextOnly(this)" class="w-full pl-10 pr-4 py-2.5 bg-yellow-50 border border-yellow-200 rounded-xl focus:ring-2 focus:ring-yellow-500 focus:outline-none font-bold text-sm text-yellow-700">
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

                <div class="space-y-1.5">
                    <label class="text-[11px] font-black text-gray-400 uppercase tracking-widest ml-1">Address</label>
                    <div class="relative">
                        <i data-lucide="map-pin" class="absolute left-3 top-3 w-4 h-4 text-gray-400"></i>
                        <textarea name="address" id="edit_address" rows="2" class="w-full pl-10 pr-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 focus:outline-none font-bold text-sm text-gray-700"></textarea>
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

<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
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
        const select = document.getElementById(`${prefix}_role_select`);

        if (value === 'Others') {
            container.classList.remove('hidden');
            input.required = true;
            // Transfer name attribute to input on submit logic below
        } else {
            container.classList.add('hidden');
            input.required = false;
            input.value = '';
        }
    }

    function validateNameInput(input) {
        // Remove everything except letters and spaces
        let val = input.value.replace(/[^A-Za-z\s]/g, '');
        
        // Limit to 5 spaces
        const spaceCount = (val.match(/ /g) || []).length;
        if (spaceCount > 5) {
            // If more than 5 spaces, keep only up to the 5th space
            let parts = val.split(' ');
            val = parts.slice(0, 6).join(' ') + parts.slice(6).join('');
        }
        
        input.value = val;
    }

    function validatePhoneInput(input) {
        // Remove everything except digits
        input.value = input.value.replace(/\D/g, '');
    }

    // Attach real-time listeners
    document.addEventListener('DOMContentLoaded', function() {
        const nameInputs = ['add_name', 'edit_name', 'add_contact_person', 'edit_contact_person', 'add_custom_role', 'edit_custom_role'];
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

    // Intercept form submissions to handle custom role and trimming
    document.querySelectorAll('form').forEach(form => {
        if (form.action.includes('/staff')) {
            form.addEventListener('submit', (e) => {
                // Trim all text inputs before submission
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
                    // Temporarily set the value of the select to the custom input value
                    // This is a quick way since the select has name="role"
                    const tempOption = document.createElement('option');
                    tempOption.value = customInput.value;
                    tempOption.text = customInput.value;
                    tempOption.selected = true;
                    select.add(tempOption);
                }
            });
        }
    });

    function editStaff(member) {
        // Close dropdown first
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

    // Dropdown Toggle Logic
    window.toggleStaffDropdown = function(id, event) {
        event.stopPropagation();
        
        // Close all other dropdowns and reset their row z-index
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

    // Close dropdowns on outside click
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

    // Close modals on escape key
    window.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            closeModal('addStaffModal');
            closeModal('editStaffModal');
        }
    });

    // Close modals on click outside
    window.addEventListener('click', (e) => {
        if (e.target.id === 'addStaffModal') closeModal('addStaffModal');
        if (e.target.id === 'editStaffModal') closeModal('editStaffModal');
    });
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\eurotaxisystem-main\resources\views/staff/index.blade.php ENDPATH**/ ?>