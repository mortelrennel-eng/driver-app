<?php $__env->startSection('title', 'Driver Management - Euro System'); ?>
<?php $__env->startSection('page-heading', 'Driver Management'); ?>
<?php $__env->startSection('page-subheading', 'Centralized driver records, incentives, and performance analytics'); ?>

<?php $__env->startSection('content'); ?>

<style>
    @keyframes shortage-blink {
        0% { opacity: 1; }
        50% { opacity: 0.7; }
        100% { opacity: 1; }
    }
    @keyframes shortage-text-pulse {
        0% { color: #dc2626; }
        50% { color: #991b1b; }
        100% { color: #dc2626; }
    }
    .shortage-blink {
        animation: shortage-blink 1.5s infinite ease-in-out;
    }
    .shortage-text-blink {
        animation: shortage-blink 1.5s infinite ease-in-out, shortage-text-pulse 1.5s infinite ease-in-out;
        font-weight: 800 !important;
    }
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
    .modern-row.shortage-row {
        background-color: #fef2f2;
    }
    .modern-row.shortage-row:hover td:first-child {
        border-left-color: #ef4444;
    }
</style>

    <!-- Search and Filters -->
    <div class="bg-white px-4 lg:px-6 py-4 border-b border-gray-200">
        <form method="GET" action="<?php echo e(route('driver-management.index')); ?>" class="flex flex-col lg:flex-row gap-2 items-center justify-between w-full">
            <!-- 2-column Grid for mobile, completely bypassed on desktop -->
            <div class="grid grid-cols-2 lg:contents gap-2 w-full">
                
                <!-- Search input: spans both columns on mobile, expands wide on desktop with min-width -->
                <div class="col-span-2 lg:flex-grow lg:min-w-[260px] order-1 lg:order-2">
                    <div class="relative group">
                        <input type="search" name="search" id="tableSearchInput" value="<?php echo e($search ?? ''); ?>"
                            class="block w-full pl-3 pr-10 py-2 lg:h-[38px] border border-gray-300 rounded-lg text-xs focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 focus:outline-none"
                            placeholder="Search by driver name or license..." autocomplete="new-password" spellcheck="false" autocorrect="off" autocapitalize="off" readonly onfocus="this.removeAttribute('readonly');">
                        <button type="submit" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-yellow-600 transition-colors">
                            <i data-lucide="search" class="h-4 w-4"></i>
                        </button>
                    </div>
                </div>

                <!-- Sort A-Z: spans 1 column on mobile, w-full lg:w-44 on desktop -->
                <div class="col-span-1 lg:w-44 order-2 lg:order-1 flex-shrink-0">
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i data-lucide="arrow-up-z-a" class="h-4 w-4 text-gray-400"></i>
                        </div>
                        <select name="sort" onchange="this.form.submit()"
                            class="block w-full pl-9 pr-3 py-2 lg:h-[38px] border border-gray-300 rounded-lg text-xs focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 focus:outline-none appearance-none">
                            <option value="alphabetical" <?php echo e(($sort ?? '') === 'alphabetical' ? 'selected' : ''); ?>>A-Z (Name)</option>
                            <option value="newest" <?php echo e(($sort ?? '') === 'newest' ? 'selected' : ''); ?>>Newest Joined</option>
                            <option value="oldest" <?php echo e(($sort ?? '') === 'oldest' ? 'selected' : ''); ?>>Oldest Joined</option>
                            <option value="status" <?php echo e(($sort ?? '') === 'status' ? 'selected' : ''); ?>>Status (Active first)</option>
                        </select>
                    </div>
                </div>

                <!-- Status Filter: spans 1 column on mobile, w-full lg:w-44 on desktop -->
                <div class="col-span-1 lg:w-44 order-3 flex-shrink-0">
                    <select name="status" onchange="this.form.submit()"
                        class="block w-full px-3 py-2 lg:h-[38px] border border-gray-300 rounded-lg text-xs focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 focus:outline-none">
                        <option value="">All Status</option>
                        <option value="active" <?php echo e(($status_filter ?? '') === 'active' ? 'selected' : ''); ?>>Active Only</option>
                        <option value="inactive" <?php echo e(($status_filter ?? '') === 'inactive' ? 'selected' : ''); ?>>Inactive Only</option>
                        <option value="no_unit" <?php echo e(($status_filter ?? '') === 'no_unit' ? 'selected' : ''); ?>>Available (No Unit)</option>
                    </select>
                </div>

            </div>

            <!-- Action buttons container: uniform heights, single-row non-wrapping container on mobile, auto-width on desktop -->
            <div id="driverActionButtonsBar" class="flex gap-1.5 lg:gap-2 items-center flex-nowrap overflow-x-auto whitespace-nowrap scrollbar-none w-full lg:w-auto lg:flex-shrink-0 max-w-full pb-1 mt-2 lg:mt-0 order-4" style="-ms-overflow-style: none; scrollbar-width: none;">
                <style>
                    /* Hide scrollbar for Chrome, Safari and Opera */
                    #driverActionButtonsBar::-webkit-scrollbar {
                        display: none;
                    }
                </style>
                <button type="button" onclick="openAddDriverModal()" class="px-3 py-1 bg-green-600 text-white rounded-lg hover:bg-green-700 flex items-center justify-center gap-1.5 lg:gap-2 text-xs font-semibold shadow-sm h-[38px] flex-1 min-w-0 lg:flex-initial lg:w-[150px]">
                    <i data-lucide="plus" class="w-3.5 h-3.5"></i> Add Driver
                </button>
            </div>
        </form>
    </div>

    
    <div id="driversTableContainer">
        <?php echo $__env->make('driver-management.partials._drivers_table', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    </div>

    
    <div id="addDriverModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-3xl h-[90vh] flex flex-col overflow-hidden">

            
            <div class="bg-slate-800 p-4 shrink-0">
                <div class="flex justify-between items-center">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-white bg-opacity-20 rounded-lg">
                            <i data-lucide="user" class="w-5 h-5 text-white"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-white leading-tight" id="driverModalTitle">Add Driver</h3>
                            <p class="text-sm text-blue-100 leading-tight" id="driverModalSubtitle">Fill in the driver's information below</p>
                        </div>
                    </div>
                    <button type="button" onclick="closeAddDriverModal()" class="text-white hover:text-gray-200 transition-colors">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>
            </div>

            
            <form id="driverForm" method="POST" class="flex flex-col flex-1 overflow-hidden">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="_method" id="driverFormMethod" value="POST">
                <input type="hidden" name="driver_id" id="editDriverId" value="">

                
                <div class="p-6 flex-1 overflow-y-auto space-y-8">

                    
                    <div>
                        <div class="flex items-center gap-2 mb-4">
                            <div class="p-2 bg-blue-100 rounded-lg">
                                <i data-lucide="user" class="w-5 h-5 text-blue-600"></i>
                            </div>
                            <h4 class="text-lg font-semibold text-gray-900">Personal Information</h4>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div class="space-y-2">
                                <label class="block text-sm font-semibold text-gray-700">First Name <span class="text-red-500">*</span></label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i data-lucide="user" class="w-4 h-4 text-gray-400"></i>
                                    </div>
                                    <input type="text" name="first_name" id="driverFirstName" required
                                        maxlength="15"
                                        class="w-full pl-10 pr-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                        placeholder="e.g., Juan"
                                        pattern="^(?!\s+$)[A-Za-z]+(\s[A-Za-z]+)?$"
                                        title="First name: letters only, max 15 chars, one space allowed."
                                        oninput="let v=this.value.replace(/[^A-Za-z ]/g,''); let parts=v.split(' '); this.value=(parts.length>2?parts[0]+' '+parts.slice(1).join('').replace(/ /g,''):v).slice(0, 15)">
                                </div>
                            </div>
                            <div class="space-y-2">
                                <label class="block text-sm font-semibold text-gray-700">Last Name <span class="text-red-500">*</span></label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i data-lucide="user" class="w-4 h-4 text-gray-400"></i>
                                    </div>
                                    <input type="text" name="last_name" id="driverLastName" required
                                        maxlength="15"
                                        class="w-full pl-10 pr-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                        placeholder="e.g., Dela Cruz"
                                        pattern="^(?!\s+$)[A-Za-z]+(\s[A-Za-z]+)?$"
                                        title="Last name: letters only, max 15 chars, one space allowed."
                                        oninput="let v=this.value.replace(/[^A-Za-z ]/g,''); let parts=v.split(' '); this.value=(parts.length>2?parts[0]+' '+parts.slice(1).join('').replace(/ /g,''):v).slice(0,15)">
                                </div>
                            </div>
                            <div class="space-y-2">
                                <label class="block text-sm font-semibold text-gray-700">Contact Number <span class="text-red-500">*</span></label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i data-lucide="phone" class="w-4 h-4 text-gray-400"></i>
                                    </div>
                                    <input type="tel" name="contact_number" id="driverContact" required
                                        maxlength="11"
                                        class="w-full pl-10 pr-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                        placeholder="e.g., 09XXXXXXXXX"
                                        pattern="^[0-9]{11}$"
                                        title="Contact number must be exactly 11 digits."
                                        oninput="this.value = this.value.replace(/[^0-9]/g,'').slice(0, 11)">
                                </div>
                            </div>
                            <div class="space-y-2">
                                <label class="block text-sm font-semibold text-gray-700">Driver Status</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i data-lucide="activity" class="w-4 h-4 text-gray-400"></i>
                                    </div>
                                    <select name="is_active" id="editIsActive"
                                        class="w-full pl-10 pr-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent appearance-none">
                                        <option value="1">Active</option>
                                        <option value="0">Inactive</option>
                                    </select>
                                </div>
                            </div>
                            <div class="space-y-2 md:col-span-2">
                                <label class="block text-sm font-semibold text-gray-700">Address <span class="text-red-500">*</span></label>
                                <div class="relative">
                                    <div class="absolute top-3 left-0 pl-3 flex items-start pointer-events-none">
                                        <i data-lucide="map-pin" class="w-4 h-4 text-gray-400"></i>
                                    </div>
                                    <textarea name="address" id="driverAddress" required rows="2"
                                        maxlength="250"
                                        class="w-full pl-10 pr-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none"
                                        placeholder="Complete address..."
                                        oninput="let original = this.value; this.value = this.value.replace(/[^a-zA-Z0-9\s.,'-]/g, '').slice(0, 250); if(original !== this.value && this.value.length === 250) { document.getElementById('address-notif').classList.remove('hidden'); } else { document.getElementById('address-notif').classList.add('hidden'); }"></textarea>
                                </div>
                                <p id="address-notif" class="text-xs text-red-500 hidden font-semibold">Address limit reached (250 chars) or invalid character removed.</p>
                                <p class="text-[10px] text-gray-400">Only letters, numbers, spaces, dots, commas, and dashes. Max 250 chars.</p>
                            </div>
                        </div>
                    </div>

                    
                    <div>
                        <div class="flex items-center gap-2 mb-4">
                            <div class="p-2 bg-yellow-100 rounded-lg">
                                <i data-lucide="credit-card" class="w-5 h-5 text-yellow-600"></i>
                            </div>
                            <h4 class="text-lg font-semibold text-gray-900">License & Employment</h4>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div class="space-y-2">
                                <label class="block text-sm font-semibold text-gray-700">License Number <span class="text-red-500">*</span></label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i data-lucide="credit-card" class="w-4 h-4 text-gray-400"></i>
                                    </div>
                                    <input type="text" name="license_number" id="driverLicense" required
                                        maxlength="13"
                                        class="w-full pl-10 pr-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent font-mono uppercase"
                                        placeholder="A00-00-000000"
                                        pattern="^[A-Z][0-9]{2}-[0-9]{2}-[0-9]{6}$"
                                        title="Format must be 1 letter, 10 numbers separated by dashes: X00-00-000000"
                                        oninput="
                                            let v = this.value.toUpperCase().replace(/[^A-Z0-9]/g, ''); 
                                            if (v.length > 0) v = v.replace(/^([^A-Z]+)/, ''); 
                                            if (v.length > 1) v = v[0] + v.substring(1).replace(/[^0-9]/g, ''); 
                                            if (v.length > 3) v = v.slice(0, 3) + '-' + v.slice(3);
                                            if (v.length > 6) v = v.slice(0, 6) + '-' + v.slice(6);
                                            this.value = v.slice(0, 13);
                                        ">
                                </div>
                                <p class="text-[10px] text-gray-400">Format: X00-00-000000 (Auto-formatted)</p>
                            </div>
                            <div class="space-y-2">
                                <label class="block text-sm font-semibold text-gray-700">License Expiry <span class="text-red-500">*</span></label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i data-lucide="calendar" class="w-4 h-4 text-gray-400"></i>
                                    </div>
                                    <input type="date" name="license_expiry" id="driverLicenseExpiry" required
                                        class="w-full pl-10 pr-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                </div>
                            </div>
                            <div class="space-y-2">
                                <label class="block text-sm font-semibold text-gray-700">Hire Date <span class="text-red-500">*</span></label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i data-lucide="briefcase" class="w-4 h-4 text-gray-400"></i>
                                    </div>
                                    <input type="date" name="hire_date" id="driverHireDate" required
                                        class="w-full pl-10 pr-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                        onchange="(function(el){ const n=new Date(); const today=n.getFullYear()+'-'+String(n.getMonth()+1).padStart(2,'0')+'-'+String(n.getDate()).padStart(2,'0'); if(el.value > today){ el.value=today; el.setCustomValidity('Hire date cannot be in the future.'); el.reportValidity(); } else { el.setCustomValidity(''); } })(this)">
                                </div>
                            </div>
                            <div class="space-y-2">
                                <label class="block text-sm font-semibold text-gray-700 flex justify-between items-center">
                                    <span>Daily Boundary Target</span>
                                    <span id="unitDerivedLabel" class="text-[10px] text-gray-500 font-bold hidden"></span>
                                    <span id="codingBoundaryAlert" class="text-[10px] text-red-600 font-bold hidden"></span>
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="text-gray-500 text-sm font-bold">₱</span>
                                    </div>
                                    <input type="number" name="daily_boundary_target" id="driverBoundaryTarget" step="0.01" readonly
                                        class="w-full pl-10 pr-4 py-3 border-2 border-gray-300 rounded-lg bg-gray-50 text-gray-500 cursor-not-allowed focus:outline-none"
                                        placeholder="Auto-synced from Unit Management">
                                </div>
                                <p class="text-xs text-gray-400 italic">Automatically synchronized from Unit Management.</p>
                            </div>
                        </div>
                    </div>

                    
                    <div>
                        <div class="flex items-center gap-2 mb-4">
                            <div class="p-2 bg-red-100 rounded-lg">
                                <i data-lucide="alert-circle" class="w-5 h-5 text-red-600"></i>
                            </div>
                            <h4 class="text-lg font-semibold text-gray-900">Emergency Contact</h4>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div class="space-y-2">
                                <label class="block text-sm font-semibold text-gray-700">Contact Name <span class="text-red-500">*</span></label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i data-lucide="users" class="w-4 h-4 text-gray-400"></i>
                                    </div>
                                    <input type="text" name="emergency_contact" id="driverEmergencyContact" required
                                        maxlength="25"
                                        class="w-full pl-10 pr-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                        placeholder="e.g., Maria Dela Cruz"
                                        pattern="^(?!\s+$)[A-Za-z]+(\s[A-Za-z ]+)*$"
                                        title="Contact name: letters and spaces only, max 25 chars."
                                        oninput="this.value = this.value.replace(/[^A-Za-z ]/g,'').replace(/^ /, '').replace(/ {2,}/, ' ').slice(0, 25)">
                                </div>
                            </div>
                            <div class="space-y-2">
                                <label class="block text-sm font-semibold text-gray-700">Contact Phone <span class="text-red-500">*</span></label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i data-lucide="phone-call" class="w-4 h-4 text-gray-400"></i>
                                    </div>
                                    <input type="tel" name="emergency_phone" id="driverEmergencyPhone" required
                                        maxlength="11"
                                        class="w-full pl-10 pr-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                        placeholder="e.g., 09XXXXXXXXX"
                                        pattern="^[0-9]{11}$"
                                        title="Emergency phone must be exactly 11 digits."
                                        oninput="this.value = this.value.replace(/[^0-9]/g,'').slice(0, 11)">
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                
                <div class="p-4 border-t flex justify-between items-center gap-3 shadow-inner bg-gray-50 shrink-0">
                    <button type="button" id="deleteDriverButton" onclick="confirmDeleteDriver()"
                        class="hidden px-5 py-2 bg-orange-100 text-orange-700 border border-orange-200 rounded-lg hover:bg-orange-200 text-sm font-bold transition-all flex items-center gap-2">
                        <i data-lucide="archive" class="w-4 h-4"></i> Archive Driver
                    </button>
                    <div class="flex gap-3 ml-auto">
                        <button type="button" onclick="closeAddDriverModal()"
                            class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 text-sm font-bold transition-all">
                            Cancel
                        </button>
                        <button type="submit"
                            class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-bold shadow-lg shadow-blue-200/50 transition-all flex items-center gap-2">
                            <i data-lucide="save" class="w-4 h-4"></i> Save Driver
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    
    <div id="suspendBanModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm hidden h-full w-full z-50 flex items-center justify-center p-4 transition-all duration-300">
        <div class="relative bg-white rounded-2xl shadow-2xl max-w-md w-full overflow-hidden flex flex-col scale-95 transition-transform duration-300" id="suspendBanModalContainer">
            
            <div class="bg-slate-800 p-5 shrink-0">
                <div class="flex justify-between items-center">
                    <div class="flex items-center gap-3">
                        <div class="p-2.5 bg-red-500/20 rounded-xl flex items-center justify-center">
                            <i data-lucide="shield-alert" class="w-6 h-6 text-red-500" id="suspendBanModalIcon"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-black text-white tracking-wide uppercase">Administrative Action</h3>
                            <p class="text-[10px] font-bold text-slate-400 mt-0.5 uppercase tracking-widest" id="suspendBanModalSubtitle">Suspend / Ban Driver</p>
                        </div>
                    </div>
                    <button type="button" onclick="closeSuspendBanModal()" class="text-slate-400 hover:text-white bg-slate-700/50 hover:bg-slate-700 p-1.5 rounded-full transition-colors">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>
            </div>

            
            <form id="suspendBanForm" onsubmit="submitSuspendBan(event)" class="p-6 space-y-5">
                <input type="hidden" id="suspendBanDriverId" value="">
                
                
                <div class="space-y-1.5">
                    <label class="block text-xs font-black text-slate-500 uppercase tracking-widest">Select Action <span class="text-red-500">*</span></label>
                    <select id="suspendBanActionType" onchange="toggleDurationField()" required
                        class="w-full px-4 py-3 border-2 border-slate-100 rounded-xl focus:border-red-500/30 focus:ring-4 focus:ring-red-500/5 transition-all outline-none bg-slate-50/50 font-bold text-xs uppercase tracking-wider text-slate-700">
                        <option value="suspend">Temporary Suspension</option>
                        <option value="ban">Permanent Ban</option>
                    </select>
                </div>

                
                <div class="space-y-1.5" id="suspendBanDurationContainer">
                    <label class="block text-xs font-black text-slate-500 uppercase tracking-widest">Suspension Duration (Days) <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <i data-lucide="clock" class="w-4 h-4 text-slate-400"></i>
                        </div>
                        <input type="number" id="suspendBanDuration" min="1" max="365" placeholder="e.g. 7" value="7" required
                            class="w-full pl-11 pr-4 py-3 border-2 border-slate-100 rounded-xl focus:border-red-500/30 focus:ring-4 focus:ring-red-500/5 transition-all outline-none bg-slate-50/50 font-mono text-sm font-black text-slate-800">
                    </div>
                </div>

                
                <div class="space-y-1.5">
                    <div class="flex justify-between items-center">
                        <label class="block text-xs font-black text-slate-500 uppercase tracking-widest">Reason / Description <span class="text-red-500">*</span></label>
                        <span id="suspendBanReasonCount" class="text-[10px] font-bold text-slate-400">0 / 500</span>
                    </div>
                    <textarea id="suspendBanReason" rows="3" placeholder="Provide detailed explanation for this administrative action..." required minlength="3" maxlength="500"
                        oninput="document.getElementById('suspendBanReasonCount').textContent = this.value.length + ' / 500'"
                        class="w-full px-4 py-3 border-2 border-slate-100 rounded-xl focus:border-red-500/30 focus:ring-4 focus:ring-red-500/5 transition-all outline-none bg-slate-50/50 font-medium text-xs text-slate-700 resize-none"></textarea>
                </div>

                
                <div class="bg-red-50 p-4 rounded-xl border border-red-100 flex items-start gap-3">
                    <i data-lucide="info" class="w-5 h-5 text-red-600 shrink-0 mt-0.5 animate-pulse"></i>
                    <p class="text-[10px] text-red-800 font-bold leading-relaxed uppercase tracking-wider">
                        Performing this action will automatically unassign the driver from their active vehicle unit.
                    </p>
                </div>

                
                <div class="pt-4 flex justify-end gap-3">
                    <button type="button" onclick="closeSuspendBanModal()"
                        class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-black rounded-xl transition-all">
                        Cancel
                    </button>
                    <button type="submit"
                        class="px-5 py-2.5 bg-red-600 hover:bg-red-700 text-white text-xs font-black rounded-xl transition-all shadow-md shadow-red-100 hover:shadow-lg active:scale-95 flex items-center gap-2">
                        <i data-lucide="shield-alert" class="w-4 h-4"></i> Apply Lock-out
                    </button>
                </div>
            </form>
        </div>
    </div>

    <?php echo $__env->make('driver-management.partials._driver_details_modal', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>


    <script>
    // ─── Set hire date max to TODAY (client local time) immediately on page load ───
    document.addEventListener('DOMContentLoaded', function () {
        const _n = new Date();
        const _today = _n.getFullYear() + '-' +
            String(_n.getMonth() + 1).padStart(2, '0') + '-' +
            String(_n.getDate()).padStart(2, '0');
        const hireDateEl = document.getElementById('driverHireDate');
        if (hireDateEl) {
            hireDateEl.max = _today;
            hireDateEl.value = _today;
        }
    });

    window.boundaryRules = <?php echo json_encode($boundary_rules ?? [], 15, 512) ?>;
    function openAddDriverModal() {
        document.getElementById('driverModalTitle').textContent = 'Add Driver';
        document.getElementById('driverFormMethod').value = 'POST';
        document.getElementById('driverForm').action = '<?php echo e(route('driver-management.store')); ?>';
        document.getElementById('editDriverId').value = '';
        document.getElementById('driverFirstName').value = '';
        document.getElementById('driverLastName').value = '';

        document.getElementById('driverContact').value = '';
        document.getElementById('driverLicense').value = '';
        document.getElementById('driverLicenseExpiry').value = '';
        // Use local date (not UTC) to correctly enforce today's date in PH timezone
        const _now = new Date();
        const todayStr = _now.getFullYear() + '-' +
            String(_now.getMonth() + 1).padStart(2, '0') + '-' +
            String(_now.getDate()).padStart(2, '0');
        const hireDateEl = document.getElementById('driverHireDate');
        hireDateEl.max = todayStr;
        hireDateEl.value = todayStr;
        document.getElementById('driverAddress').value = '';
        document.getElementById('driverEmergencyContact').value = '';
        document.getElementById('driverEmergencyPhone').value = '';
        const targetInput = document.getElementById('driverBoundaryTarget');
        const codingAlert = document.getElementById('codingBoundaryAlert');
        
        targetInput.value = '';
        targetInput.placeholder = 'Please dispatch to appear boundary';
        if (codingAlert) {
            codingAlert.classList.remove('hidden');
            codingAlert.classList.remove('text-red-600');
            codingAlert.classList.add('text-gray-500');
            codingAlert.textContent = '(Pending Dispatch)';
        }

        document.getElementById('editIsActive').value = '1';
        document.getElementById('deleteDriverButton').classList.add('hidden');
        document.getElementById('addDriverModal').classList.remove('hidden');
        lucide.createIcons();
    }

    function openEditDriverModal(id) {
        fetch('<?php echo e(route('driver-management.index')); ?>/' + id + '?format=json', {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
        .then(r => r.json())
        .then(data => {
            document.getElementById('driverModalTitle').textContent = 'Edit Driver';
            document.getElementById('driverFormMethod').value = 'PUT';
            document.getElementById('driverForm').action = '<?php echo e(url('driver-management')); ?>/' + id;
            document.getElementById('editDriverId').value = id;
            document.getElementById('driverFirstName').value = data.first_name || '';
            document.getElementById('driverLastName').value = data.last_name || '';

            document.getElementById('driverContact').value = data.contact_number || '';
            document.getElementById('driverLicense').value = data.license_number || '';
            document.getElementById('driverLicenseExpiry').value = data.license_expiry || '';
            
            // Set max to today for Edit mode as well
            const _n = new Date();
            const _today = _n.getFullYear() + '-' +
                String(_n.getMonth() + 1).padStart(2, '0') + '-' +
                String(_n.getDate()).padStart(2, '0');
            const hireDateEl = document.getElementById('driverHireDate');
            hireDateEl.max = _today;
            hireDateEl.value = data.hire_date || _today;
            
            document.getElementById('driverAddress').value = data.address || '';
            document.getElementById('driverEmergencyContact').value = data.emergency_contact || '';
            document.getElementById('driverEmergencyPhone').value = data.emergency_phone || '';
            
            const targetInput = document.getElementById('driverBoundaryTarget');
            const codingAlert = document.getElementById('codingBoundaryAlert');
            
            if (data.current_pricing) {
                targetInput.value = data.current_pricing.rate.toFixed(2);
                targetInput.placeholder = '0.00';
                
                if (data.current_pricing.label && data.current_pricing.type !== 'regular') {
                    codingAlert.classList.remove('hidden');
                    codingAlert.textContent = data.current_pricing.label;
                    codingAlert.className = data.current_pricing.type === 'coding' ? 'text-[11px] text-red-600 font-bold' : 'text-[11px] text-blue-600 font-bold';
                } else {
                    codingAlert.classList.add('hidden');
                }
            } else {
                targetInput.value = data.daily_boundary_target || '';
                targetInput.placeholder = 'Enter boundary target...';
                codingAlert.classList.add('hidden');
            }
            document.getElementById('editIsActive').value = data.is_active ? '1' : '0';
            document.getElementById('deleteDriverButton').classList.remove('hidden');
            document.getElementById('addDriverModal').classList.remove('hidden');
            lucide.createIcons();
        });
    }

    function closeAddDriverModal() {
        document.getElementById('addDriverModal').classList.add('hidden');
    }

    function confirmDeleteDriver() {
        const id = document.getElementById('editDriverId').value;
        const firstName = document.getElementById('driverFirstName').value || '';
        const lastName = document.getElementById('driverLastName').value || '';
        const name = (firstName + ' ' + lastName).trim() || 'this driver';
        deleteDriver(id, name);
    }

    function deleteDriver(id, name) {
        if (!id) return;
        if (confirm('Are you sure you want to delete ' + name + '?')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '<?php echo e(url('driver-management')); ?>/' + id;
            form.innerHTML = '<?php echo csrf_field(); ?>' +
                            '<input type="hidden" name="_method" value="DELETE">';
            document.body.appendChild(form);
            form.submit();
        }
    }

    let searchTimer;
    const searchInput = document.getElementById('tableSearchInput');
    const statusFilter = document.querySelector('select[name="status"]');
    const sortFilter = document.querySelector('select[name="sort"]');
    const tableContainer = document.getElementById('driversTableContainer');

    function performSearch(page = 1) {
        const query = searchInput.value;
        const status = statusFilter.value;
        const sort = sortFilter.value;

        tableContainer.style.opacity = '0.5';
        tableContainer.style.pointerEvents = 'none';

        const finalUrl = `<?php echo e(route('driver-management.index')); ?>?search=${encodeURIComponent(query)}&status=${status}&sort=${sort}&page=${page}`;
        
        // Synchronize state with history state engine
        if (typeof window.history.replaceState === 'function') {
            window.history.replaceState({}, '', finalUrl);
        }

        fetch(finalUrl, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(response => response.text())
        .then(html => {
            tableContainer.innerHTML = html;
            tableContainer.style.opacity = '1';
            tableContainer.style.pointerEvents = 'auto';
            if (typeof lucide !== 'undefined') lucide.createIcons();
            if (typeof window.adjustMobileTableColumns === 'function') window.adjustMobileTableColumns();
        });
    }

    if (searchInput) {
        searchInput.addEventListener('input', () => {
            clearTimeout(searchTimer);
            searchTimer = setTimeout(() => performSearch(1), 300);
        });
    }
    if (statusFilter) statusFilter.addEventListener('change', () => performSearch(1));
    if (sortFilter) sortFilter.addEventListener('change', () => performSearch(1));

    window.changePage = function(page) {
        performSearch(page);
        window.scrollTo({ top: 0, behavior: 'smooth' });
    };

    </script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/u747826271/domains/eurotaxisystem.site/public_html/resources/views/driver-management/index.blade.php ENDPATH**/ ?>