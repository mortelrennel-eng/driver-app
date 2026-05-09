

<div class="p-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5 gap-6 bg-gray-50/50 min-h-screen">
    <?php $__empty_1 = true; $__currentLoopData = $units; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $unit): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <?php
            $primary_driver = $unit->primary_driver ?? null;
            $status_color = match($unit->status) {
                'active'       => 'text-green-600',
                'maintenance'  => 'text-red-600',
                'coding'       => 'text-yellow-700',
                'at_risk'      => 'text-orange-600',
                'vacant', 'available' => 'text-gray-500',
                default        => 'text-gray-500',
            };
            $dot_bg = match($unit->status) {
                'active'       => 'bg-green-500',
                'maintenance'  => 'bg-red-500',
                'coding'       => 'bg-yellow-500',
                'at_risk'      => 'bg-orange-500',
                'vacant', 'available' => 'bg-gray-400',
                default        => 'bg-gray-400',
            };
            // Card background & border per status
            $card_bg = match($unit->status) {
                'active'            => 'bg-gradient-to-br from-green-50 to-emerald-50/60 border-green-100',
                'maintenance'       => 'bg-gradient-to-br from-red-50 to-rose-50/60 border-red-100',
                'coding'            => 'bg-gradient-to-br from-yellow-50 to-amber-50/60 border-yellow-100',
                'at_risk'           => 'bg-gradient-to-br from-orange-50 to-amber-50/60 border-orange-100',
                'vacant', 'available' => 'bg-gradient-to-br from-slate-50 to-gray-50/60 border-gray-100',
                default             => 'bg-gradient-to-br from-slate-50 to-gray-50/60 border-gray-100',
            };
            // Driver section bg per status
            $driver_bg = match($unit->status) {
                'active'            => 'bg-green-100/50 border-green-100',
                'maintenance'       => 'bg-red-100/50 border-red-100',
                'coding'            => 'bg-yellow-100/50 border-yellow-100',
                'at_risk'           => 'bg-orange-100/50 border-orange-100',
                'vacant', 'available' => 'bg-gray-100/50 border-gray-100',
                default             => 'bg-gray-100/50 border-gray-100',
            };
            // Icon box bg per status
            $icon_bg = match($unit->status) {
                'active'            => 'bg-green-100 border-green-200',
                'maintenance'       => 'bg-red-100 border-red-200',
                'coding'            => 'bg-yellow-100 border-yellow-200',
                'at_risk'           => 'bg-orange-100 border-orange-200',
                'vacant', 'available' => 'bg-slate-100 border-slate-200',
                default             => 'bg-slate-100 border-slate-200',
            };
            // Car icon color per status
            $icon_color = match($unit->status) {
                'active'            => 'text-green-500',
                'maintenance'       => 'text-red-500',
                'coding'            => 'text-yellow-600',
                'at_risk'           => 'text-orange-500',
                'vacant', 'available' => 'text-slate-400',
                default             => 'text-slate-400',
            };
            // Footer divider per status
            $footer_border = match($unit->status) {
                'active'            => 'border-green-100',
                'maintenance'       => 'border-red-100',
                'coding'            => 'border-yellow-100',
                'at_risk'           => 'border-orange-100',
                'vacant', 'available' => 'border-gray-100',
                default             => 'border-gray-100',
            };
            // Maintenance logic for the bar
            $odo_limit = 5000;
            $current_odo = (int)($unit->latest_odo ?? 0);
            $last_service_odo = (int)($unit->last_service_odo ?? 0);
            $kms_since = max(0, $current_odo - $last_service_odo);
            $is_overdue = $kms_since >= $odo_limit;
            $progress_percent = min(100, ($kms_since / $odo_limit) * 100);
        ?>

        <div class="<?php echo e($card_bg); ?> rounded-[2rem] shadow-sm border p-6 flex flex-col cursor-pointer transition-all hover:shadow-xl hover:-translate-y-1" 
             onclick="viewUnitDetails(<?php echo e($unit->id); ?>)">
            
            
            <div class="flex justify-between items-center mb-6">
                <div class="bg-black text-white px-4 py-1.5 rounded-lg text-sm font-black tracking-widest shadow-sm">
                    <?php echo e($unit->plate_number); ?>

                </div>
                <div class="flex items-center gap-2">
                    <div class="w-2.5 h-2.5 rounded-full <?php echo e($dot_bg); ?> animate-pulse <?php echo e($unit->status === 'active' ? 'shadow-[0_0_8px_rgba(34,197,94,0.6)]' : ''); ?>"></div>
                    <span class="text-xs font-bold <?php echo e($status_color); ?>"><?php echo e($unit->status === 'at_risk' ? 'At Risk' : ucfirst($unit->status === 'available' ? 'vacant' : $unit->status)); ?></span>
                </div>
            </div>

            
            <div class="flex items-center gap-5 mb-6">
                
                <div class="w-20 h-20 <?php echo e($icon_bg); ?> rounded-2xl flex items-center justify-center flex-shrink-0 border">
                    <i data-lucide="car" class="w-10 h-10 <?php echo e($icon_color); ?>"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <h4 class="text-xl font-black text-gray-900 leading-tight"><?php echo e($unit->make); ?> <?php echo e($unit->model); ?></h4>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wide mt-0.5"><?php echo e($unit->year); ?> • <?php echo e(strtoupper($unit->unit_type ?? 'NEW')); ?></p>
                    
                    
                    <div class="mt-3 inline-flex items-center gap-1.5 px-3 py-1 bg-green-50 text-green-600 rounded-lg border border-green-100">
                        <i data-lucide="banknote" class="w-3.5 h-3.5"></i>
                        <span class="text-sm font-black">₱<?php echo e(number_format($unit->current_rate ?? $unit->boundary_rate, 2)); ?></span>
                    </div>
                </div>
            </div>

            
            <div class="<?php echo e($driver_bg); ?> rounded-2xl p-4 flex items-center gap-4 mb-4 border">
                <div class="w-10 h-10 bg-white rounded-full flex items-center justify-center border border-gray-200 flex-shrink-0 shadow-sm">
                    <i data-lucide="user" class="w-5 h-5 text-gray-300"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest leading-none mb-1">Primary Driver</p>
                    <p class="text-sm font-bold text-gray-600 truncate">
                        <?php if($unit->driver_id && $primary_driver): ?>
                            <?php $d1 = explode('|', $primary_driver); ?>
                            <?php echo e($d1[0]); ?>

                        <?php else: ?>
                            Unassigned
                        <?php endif; ?>
                    </p>
                </div>
                <?php if($unit->driver_id): ?>
                    <div class="w-2 h-2 rounded-full bg-green-500 shadow-[0_0_6px_rgba(34,197,94,0.4)]"></div>
                <?php endif; ?>
            </div>

            
            <?php $has_maintenance_data = (int)($unit->gps_device_count ?? 0) > 0 || !empty($unit->imei); ?>
            <?php if($has_maintenance_data): ?>
                <div class="mb-4">
                    <?php echo $__env->make('units.partials._maintenance_health_bar', ['unit' => $unit], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                </div>
            <?php endif; ?>

            
            <div class="mt-auto flex items-center justify-between pt-2 border-t <?php echo e($footer_border); ?>">
                <div class="flex flex-col">
                    <span class="text-[9px] font-black text-gray-400 uppercase tracking-tighter leading-none mb-1">Serial Info</span>
                    <span class="text-xs font-bold text-gray-800"><?php echo e($unit->motor_no ? substr($unit->motor_no, -8) : 'N/A'); ?></span>
                </div>

                
                <div class="relative">
                    <button type="button"
                        class="p-2 text-gray-400 hover:text-gray-800 hover:bg-gray-100 rounded-full transition-colors focus:outline-none"
                        onclick="toggleUnitDropdown('grid-unit-dropdown-<?php echo e($unit->id); ?>', event)"
                        title="Actions">
                        <i data-lucide="more-vertical" class="w-5 h-5"></i>
                    </button>

                    <div id="grid-unit-dropdown-<?php echo e($unit->id); ?>"
                        class="unit-action-dropdown hidden absolute right-0 bottom-10 w-40 bg-white rounded-lg shadow-xl border border-gray-100 z-50 overflow-hidden">
                        
                        <button type="button"
                            class="w-full text-left px-4 py-2.5 text-xs font-bold text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors flex items-center gap-2"
                            onclick="event.stopPropagation(); document.getElementById('grid-unit-dropdown-<?php echo e($unit->id); ?>').classList.add('hidden'); editUnit(<?php echo e($unit->id); ?>)">
                            <i data-lucide="edit-2" class="w-4 h-4"></i> Edit Unit
                        </button>
                        
                        <form method="POST" action="<?php echo e(route('units.destroy', $unit->id)); ?>"
                            onsubmit="return confirm('Archive unit <?php echo e($unit->plate_number); ?>? It will be moved to the Archive page.');"
                            class="m-0 p-0">
                            <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                            <button type="submit"
                                onclick="event.stopPropagation()"
                                class="w-full text-left px-4 py-2.5 text-xs font-bold text-amber-600 hover:bg-amber-50 transition-colors flex items-center gap-2 border-t border-gray-50">
                                <i data-lucide="archive" class="w-4 h-4"></i> Archive Unit
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <div class="col-span-full py-20 text-center">
            <i data-lucide="car" class="w-16 h-16 mx-auto mb-4 text-gray-200"></i>
            <h4 class="text-gray-900 font-black text-xl">No units found</h4>
            <p class="text-gray-500 mt-1 italic">Try adjusting your filters.</p>
        </div>
    <?php endif; ?>
</div>

<?php if($pagination['total_pages'] > 1): ?>
    <div class="px-8 py-6 bg-white border-t border-gray-100 flex flex-col sm:flex-row items-center justify-between gap-4">
        <div class="text-[11px] font-bold text-gray-400 uppercase tracking-widest">
            Page <span class="text-gray-900"><?php echo e($pagination['page']); ?></span> of <span class="text-gray-900"><?php echo e($pagination['total_pages']); ?></span>
        </div>
        <div class="flex items-center gap-1.5">
            <?php if($pagination['has_prev']): ?>
                <button onclick="changePage(<?php echo e($pagination['prev_page']); ?>)" class="p-2.5 rounded-xl border border-gray-200 bg-white text-gray-600 hover:bg-gray-50 transition-all">
                    <i data-lucide="chevron-left" class="w-5 h-5"></i>
                </button>
            <?php endif; ?>
            <?php for($i = max(1, $pagination['page'] - 2); $i <= min($pagination['total_pages'], $pagination['page'] + 2); $i++): ?>
                <button onclick="changePage(<?php echo e($i); ?>)" class="w-10 h-10 rounded-xl border text-sm font-black transition-all <?php echo e($i === $pagination['page'] ? 'bg-blue-600 border-blue-600 text-white shadow-md' : 'bg-white border-gray-200 text-gray-500 hover:bg-gray-50'); ?>">
                    <?php echo e($i); ?>

                </button>
            <?php endfor; ?>
            <?php if($pagination['has_next']): ?>
                <button onclick="changePage(<?php echo e($pagination['next_page']); ?>)" class="p-2.5 rounded-xl border border-gray-200 bg-white text-gray-600 hover:bg-gray-50 transition-all">
                    <i data-lucide="chevron-right" class="w-5 h-5"></i>
                </button>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\eurotaxisystem-main\resources\views/units/partials/_units_grid.blade.php ENDPATH**/ ?>