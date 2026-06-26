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
            <?php $__empty_1 = true; $__currentLoopData = $drivers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $driver): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <?php $has_shortage = isset($driver->net_shortage) && $driver->net_shortage > 0; ?>
                <tr class="modern-row cursor-pointer group <?php echo e($has_shortage ? 'shortage-row' : ''); ?>"
                    onclick="openDriverDetails(<?php echo e($driver->id); ?>)">

                    
                    <td class="px-3 md:px-6 py-4 md:py-5">
                        <div class="flex items-center gap-2 md:gap-4">
                            <div
                                class="w-10 h-10 md:w-12 md:h-12 rounded-full <?php echo e($has_shortage ? 'bg-red-100 text-red-600' : 'bg-blue-100 text-blue-600'); ?> flex items-center justify-center flex-shrink-0 shadow-inner">
                                <span
                                    class="text-sm md:text-lg font-black"><?php echo e(substr($driver->first_name ?? $driver->full_name, 0, 1)); ?><?php echo e(substr($driver->last_name ?? '', 0, 1)); ?></span>
                            </div>
                            <div>
                                <div class="flex items-center gap-2 mb-1">
                                    <h4
                                        class="text-xs md:text-sm font-black <?php echo e($has_shortage ? 'text-red-700 shortage-text-blink' : 'text-gray-900'); ?>">
                                        <?php echo e($driver->full_name); ?>

                                    </h4>
                                    <?php if($has_shortage): ?>
                                        <div class="shortage-blink flex items-center gap-1.5 px-2.5 py-1 bg-red-50 text-red-600 border border-red-200 rounded-lg shadow-sm group/shortage hover:bg-red-600 hover:text-white transition-all duration-300"
                                            title="Net unpaid boundary shortage: ₱<?php echo e(number_format($driver->net_shortage, 2)); ?>">
                                            <i data-lucide="alert-triangle" class="w-3 h-3"></i>
                                            <span class="text-[10px] font-black tracking-tight whitespace-nowrap">
                                                ₱<?php echo e(number_format($driver->net_shortage, 0)); ?> <span
                                                    class="text-[8px] opacity-70">SHORT</span>
                                            </span>
                                        </div>
                                    <?php endif; ?>

                                    <?php if(isset($driver->total_pending_debt) && $driver->total_pending_debt > 0): ?>
                                        <div class="flex items-center gap-1.5 px-2.5 py-1 bg-orange-50 text-orange-600 border border-orange-200 rounded-lg shadow-sm group/debt hover:bg-orange-600 hover:text-white transition-all duration-300"
                                            title="Pending Accident/Incident Debt: ₱<?php echo e(number_format($driver->total_pending_debt, 2)); ?>">
                                            <i data-lucide="shield-alert" class="w-3 h-3"></i>
                                            <span class="text-[10px] font-black tracking-tight whitespace-nowrap">
                                                ₱<?php echo e(number_format($driver->total_pending_debt, 0)); ?> <span
                                                    class="text-[8px] opacity-70">DEBT</span>
                                            </span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="text-[10px] font-semibold text-gray-400 flex gap-2">
                                    <span title="Input by <?php echo e($driver->creator_name ?? 'System'); ?>">IN:
                                        <?php echo e(strtoupper($driver->creator_name ?? 'System')); ?></span>
                                    <?php if(isset($driver->editor_name) && $driver->editor_name): ?>
                                        <span class="text-gray-300">|</span>
                                        <span title="Last edit by <?php echo e($driver->editor_name); ?>">ED:
                                            <?php echo e(strtoupper($driver->editor_name)); ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </td>

                    
                    <td class="px-3 md:px-6 py-4 md:py-5 whitespace-nowrap">
                        <?php if(!empty($driver->assigned_unit)): ?>
                            <div
                                class="inline-flex items-center gap-1.5 md:gap-2 bg-slate-800 text-white px-2.5 py-1 md:px-3 md:py-1.5 rounded-lg shadow-sm">
                                <i data-lucide="car" class="w-3.5 h-3.5 md:w-4 md:h-4 text-blue-400"></i>
                                <span class="text-xs md:text-sm font-black tracking-widest"><?php echo e($driver->assigned_unit); ?></span>
                            </div>
                        <?php else: ?>
                            <span
                                class="inline-flex items-center gap-1 px-1.5 md:gap-1.5 text-emerald-700 font-black text-[10px] md:text-[11px] bg-emerald-50 px-2.5 py-1 md:px-3 md:py-1.5 rounded-lg border border-emerald-200 uppercase tracking-widest">
                                <div class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></div>
                                Unassigned
                            </span>
                        <?php endif; ?>
                    </td>

                    
                    <td class="px-6 py-5 whitespace-nowrap license-detail-col hidden lg:table-cell">
                        <div class="text-sm font-bold text-gray-900 font-mono tracking-wider">
                            <?php echo e($driver->license_number ?? 'N/A'); ?>

                        </div>
                        <?php if(isset($driver->license_expiry)): ?>
                            <?php $is_license_expired = \Carbon\Carbon::parse($driver->license_expiry)->isPast(); ?>
                            <div class="flex flex-col gap-1 mt-1">
                                <div
                                    class="text-[10px] font-semibold <?php echo e($is_license_expired ? 'text-red-500' : 'text-gray-500'); ?>">
                                    EXP: <?php echo e(\Carbon\Carbon::parse($driver->license_expiry)->format('M d, Y')); ?>

                                </div>
                                <?php if($is_license_expired): ?>
                                    <span
                                        class="inline-flex items-center gap-1 px-1.5 py-0.5 bg-red-100 text-red-600 text-[9px] font-black rounded uppercase tracking-widest border border-red-200 w-fit animate-pulse">
                                        <i data-lucide="alert-circle" class="w-2.5 h-2.5"></i> Expired
                                    </span>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </td>

                    
                    <td class="px-3 md:px-6 py-4 md:py-5 whitespace-nowrap">
                        <div class="flex items-center gap-2">
                            <?php if($driver->driver_status === 'banned'): ?>
                                <div class="w-2 h-2 md:w-2.5 md:h-2.5 rounded-full bg-red-600 shadow-[0_0_8px_rgba(220,38,38,0.6)]"></div>
                                <span
                                    class="text-[10px] md:text-[11px] font-black uppercase tracking-widest text-red-600 flex items-center gap-1">
                                    <i data-lucide="ban" class="w-3.5 h-3.5 md:w-3 md:h-3"></i> Banned
                                </span>
                            <?php else: ?>
                                <span
                                    class="inline-flex items-center gap-1 px-1.5 py-0.5 md:px-2.5 md:py-1 rounded-full <?php echo e($driver->is_active ? 'bg-green-50 text-green-700 border border-green-200' : 'bg-red-50 text-red-700 border border-red-200'); ?>">
                                    <div
                                        class="w-1.5 h-1.5 rounded-full <?php echo e($driver->is_active ? 'bg-green-500 animate-pulse' : 'bg-red-500'); ?>">
                                    </div>
                                    <span class="text-[9px] md:text-[10px] font-black uppercase tracking-widest">
                                        <?php echo e($driver->is_active ? 'Active' : 'Inactive'); ?>

                                    </span>
                                </span>
                            <?php endif; ?>
                        </div>
                    </td>

                    
                    <td class="px-6 py-5 whitespace-nowrap financial-target-col hidden lg:table-cell">
                        <div class="flex flex-col gap-1.5">
                            <div class="flex items-center gap-2">
                                <?php if(!empty($driver->assigned_unit)): ?>
                                    <span
                                        class="text-lg font-black text-gray-900 tracking-tight">₱<?php echo e(number_format($driver->current_target ?? $driver->daily_boundary_target, 2)); ?></span>
                                    <span
                                        class="text-[9px] bg-blue-50 text-blue-600 border border-blue-200 px-1.5 py-0.5 rounded font-bold uppercase tracking-widest">Unit</span>
                                <?php else: ?>
                                    <span class="text-[11px] font-bold text-gray-400 italic">Pending Unit Assignment</span>
                                <?php endif; ?>
                            </div>
                            <?php if(isset($driver->target_label) && $driver->target_type !== 'regular'): ?>
                                <span class="text-[10px] font-black uppercase tracking-widest px-2 py-0.5 rounded w-fit
                                                    <?php if($driver->target_type === 'coding'): ?> bg-indigo-50 text-indigo-700 border border-indigo-200
                                                    <?php elseif($driver->target_type === 'discount'): ?> bg-amber-50 text-amber-700 border border-amber-200
                                                    <?php else: ?> bg-gray-50 text-gray-600 border border-gray-200 <?php endif; ?>">
                                    <?php echo e($driver->target_label); ?>

                                </span>
                            <?php endif; ?>
                        </div>
                    </td>

                    
                    <td class="px-3 md:px-6 py-4 md:py-5 whitespace-nowrap rating-col hidden lg:table-cell">
                        <?php
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
                        ?>
                        <div class="flex flex-col items-center gap-1">
                            <div class="flex items-center gap-0.5">
                                <?php for($i = 1; $i <= 5; $i++): ?>
                                    <i data-lucide="star" class="w-3 h-3 <?php echo e(($starsCount > 0 && $i <= $starsCount) ? ($cfg['star'] . ' fill-current') : 'text-slate-200'); ?>"></i>
                                <?php endfor; ?>
                            </div>
                            <span class="text-[10px] font-bold uppercase tracking-wider <?php echo e($cfg['color']); ?> <?php echo e($cfg['bg']); ?> px-1.5 py-0.5 rounded-full border border-current/10">
                                <?php echo e($label); ?>

                            </span>
                        </div>
                    </td>

                    
                    <td class="px-3 md:px-6 py-4 md:py-5 whitespace-nowrap text-right">
                        <button type="button"
                            class="driver-action-btn p-1.5 md:p-2 text-gray-400 hover:text-gray-800 hover:bg-gray-200 rounded-full transition-colors focus:outline-none"
                            data-dropdown-id="dropdown-<?php echo e($driver->id); ?>"
                            onclick="toggleDriverDropdown('dropdown-<?php echo e($driver->id); ?>', event)" title="Actions">
                            <i data-lucide="more-vertical" class="w-4 h-4 md:w-5 md:h-5"></i>
                        </button>
                    </td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
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
            <?php endif; ?>
        </tbody>
    </table>
</div>


<?php $__currentLoopData = $drivers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $driver): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<div id="dropdown-<?php echo e($driver->id); ?>"
    class="driver-action-dropdown hidden fixed w-48 bg-white rounded-xl shadow-2xl border border-gray-100 py-1"
    style="z-index: 99999;">
    <button type="button"
        class="w-full text-left px-4 py-3 text-xs font-bold text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors flex items-center gap-2.5"
        onclick="event.stopPropagation(); closeAllDriverDropdowns(); openEditDriverModal(<?php echo e($driver->id); ?>)">
        <i data-lucide="edit-2" class="w-3.5 h-3.5"></i> Edit Driver
    </button>
    <?php if($driver->driver_status === 'banned' || $driver->driver_status === 'suspended'): ?>
    <button type="button"
        class="w-full text-left px-4 py-3 text-xs font-bold text-green-600 hover:bg-green-50 transition-colors flex items-center gap-2.5 border-t border-gray-50"
        onclick="event.stopPropagation(); closeAllDriverDropdowns(); unbanDriver(<?php echo e($driver->id); ?>, '<?php echo e($driver->full_name); ?>')">
        <i data-lucide="shield-check" class="w-3.5 h-3.5"></i> Restore Driver
    </button>
    <?php else: ?>
    <button type="button"
        class="w-full text-left px-4 py-3 text-xs font-bold text-red-600 hover:bg-red-50 transition-colors flex items-center gap-2.5 border-t border-gray-50"
        onclick="event.stopPropagation(); closeAllDriverDropdowns(); window.location.href = '<?php echo e(route('driver-management.banned')); ?>?suspend_driver_id=<?php echo e($driver->id); ?>'">
        <i data-lucide="shield-alert" class="w-3.5 h-3.5"></i> Suspend / Ban
    </button>
    <?php endif; ?>
    <button type="button"
        class="w-full text-left px-4 py-3 text-xs font-bold text-orange-500 hover:bg-orange-50 transition-colors flex items-center gap-2.5 border-t border-gray-50"
        onclick="event.stopPropagation(); closeAllDriverDropdowns(); deleteDriver(<?php echo e($driver->id); ?>, '<?php echo e($driver->full_name); ?>')">
        <i data-lucide="archive" class="w-3.5 h-3.5"></i> Archive
    </button>
</div>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

<?php if($pagination['total_pages'] > 1): ?>
    <div
        class="px-8 py-6 bg-gray-50/50 border-t border-gray-100 flex flex-col md:flex-row items-center justify-between gap-4">
        <div class="text-[11px] font-bold text-gray-400 uppercase tracking-widest">
            Showing <span class="text-gray-900"><?php echo e($pagination['total_items']); ?></span> results / Page <span
                class="text-gray-900"><?php echo e($pagination['page']); ?></span> of <span
                class="text-gray-900"><?php echo e($pagination['total_pages']); ?></span>
        </div>
        <div class="flex items-center gap-1.5">
            <?php if($pagination['has_prev']): ?>
                <button onclick="changePage(<?php echo e($pagination['prev_page']); ?>)"
                    class="p-2.5 rounded-xl border border-gray-200 bg-white text-gray-600 hover:bg-gray-50 transition-all active:scale-90 shadow-sm">
                    <i data-lucide="chevron-left" class="w-5 h-5"></i>
                </button>
            <?php endif; ?>
            <?php for($i = max(1, $pagination['page'] - 2); $i <= min($pagination['total_pages'], $pagination['page'] + 2); $i++): ?>
                <button onclick="changePage(<?php echo e($i); ?>)"
                    class="w-10 h-10 rounded-xl border text-[11px] font-black transition-all <?php echo e($i === $pagination['page'] ? 'bg-blue-600 border-blue-600 text-white shadow-md' : 'bg-white border-gray-200 text-gray-500 hover:bg-gray-50'); ?>">
                    <?php echo e($i); ?>

                </button>
            <?php endfor; ?>
            <?php if($pagination['has_next']): ?>
                <button onclick="changePage(<?php echo e($pagination['next_page']); ?>)"
                    class="p-2.5 rounded-xl border border-gray-200 bg-white text-gray-600 hover:bg-gray-50 transition-all active:scale-90 shadow-sm">
                    <i data-lucide="chevron-right" class="w-5 h-5"></i>
                </button>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>

<script>
    window.closeAllDriverDropdowns = function() {
        document.querySelectorAll('.driver-action-dropdown').forEach(el => el.classList.add('hidden'));
    };

    window.toggleDriverDropdown = function (id, event) {
        event.stopPropagation();

        const dropdown = document.getElementById(id);
        if (!dropdown) return;

        const isHidden = dropdown.classList.contains('hidden');

        // Close all dropdowns first
        window.closeAllDriverDropdowns();

        if (isHidden) {
            // Calculate position from the button's screen coordinates
            const btn = event.currentTarget;
            const rect = btn.getBoundingClientRect();
            const dropW = 192; // w-48 = 12rem = 192px
            const spaceBelow = window.innerHeight - rect.bottom;

            // Position: prefer below-left of button
            let top = rect.bottom + 6;
            let left = rect.right - dropW;

            // Flip up if not enough space below
            if (spaceBelow < 160) {
                top = rect.top - dropdown.offsetHeight - 6;
            }
            // Keep within viewport horizontally
            if (left < 8) left = 8;
            if (left + dropW > window.innerWidth - 8) left = window.innerWidth - dropW - 8;

            dropdown.style.top  = top + 'px';
            dropdown.style.left = left + 'px';
            dropdown.classList.remove('hidden');

            if (typeof lucide !== 'undefined') lucide.createIcons();
        }
    };

    // Close on scroll or click outside
    if (!window.driverDropdownListenerAdded) {
        document.addEventListener('click', window.closeAllDriverDropdowns);
        document.addEventListener('scroll', window.closeAllDriverDropdowns, true);
        window.driverDropdownListenerAdded = true;
    }

    window.unbanDriver = function (driverId, driverName) {
        if (!confirm('Are you sure you want to RESTORE/UNBAN ' + driverName + '?\nTheir status will be set back to Available.')) return;

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
</script><?php /**PATH /home/u747826271/domains/eurotaxisystem.site/public_html/resources/views/driver-management/partials/_drivers_table.blade.php ENDPATH**/ ?>