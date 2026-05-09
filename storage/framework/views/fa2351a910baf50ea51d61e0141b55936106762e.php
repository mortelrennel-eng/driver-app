
<?php
    $has_gps = ((int)($unit->gps_device_count ?? 0) > 0) || !empty($unit->imei);
    $SERVICE_KM = 5000;
    
    if ($has_gps) {
        $current_odo = (float)($unit->current_gps_odo ?? 0);
        $service_odo = (float)($unit->last_service_odo_gps ?? 0);
        $km_since = max(0, $current_odo - $service_odo);
        $pct = min(100, round(($km_since / $SERVICE_KM) * 100));
        $is_overdue = $km_since >= $SERVICE_KM;
        
        // Colors & Labels
        if ($is_overdue) {
            $bar_color = 'bg-red-600';
            $text_color = 'text-red-600';
            $label = '⚠ SERVICE OVERDUE';
            $pulse = 'animate-pulse';
        } elseif ($pct >= 85) {
            $bar_color = 'bg-orange-500';
            $text_color = 'text-orange-600';
            $label = 'SOON: Maintenance Due';
            $pulse = '';
        } elseif ($pct >= 60) {
            $bar_color = 'bg-yellow-400';
            $text_color = 'text-yellow-600';
            $label = 'Maintenance Progress';
            $pulse = '';
        } else {
            $bar_color = 'bg-green-500';
            $text_color = 'text-green-600';
            $label = 'Optimal Health';
            $pulse = '';
        }
    }
?>

<?php if($has_gps): ?>
<div class="maintenance-timeline w-full mt-2">
    <div class="flex items-center justify-between mb-1">
        <div class="flex items-center gap-1.5">
            <span class="w-2 h-2 rounded-full <?php echo e(str_replace('bg-', 'bg-', $bar_color)); ?> animate-pulse"></span>
            <span class="text-[10px] font-black uppercase tracking-wider <?php echo e($text_color); ?>"><?php echo e($label); ?></span>
        </div>
        <span class="text-[10px] font-bold text-gray-400 tabular-nums">
            <?php echo e(number_format($km_since)); ?> / <?php echo e(number_format($SERVICE_KM)); ?> KM
        </span>
    </div>
    
    
    <div class="relative h-2 w-full bg-gray-100 rounded-full overflow-hidden border border-gray-200/50 shadow-inner">
        
        <div class="absolute inset-y-0 left-0 <?php echo e($bar_color); ?> <?php echo e($pulse); ?> rounded-full transition-all duration-1000 ease-out"
             style="width: <?php echo e($pct); ?>%">
            
            <div class="absolute inset-0 bg-gradient-to-b from-white/20 to-transparent"></div>
        </div>
        
        
        <div class="absolute inset-0 flex justify-between px-2 pointer-events-none">
            <div class="w-px h-full bg-white/30"></div>
            <div class="w-px h-full bg-white/30"></div>
            <div class="w-px h-full bg-white/30"></div>
            <div class="w-px h-full bg-white/30"></div>
        </div>
    </div>
    
    <?php if($is_overdue): ?>
        <p class="text-[9px] font-bold text-red-500 mt-1 italic tracking-tight">
            Unit has exceeded the <?php echo e(number_format($SERVICE_KM)); ?>km service interval by <?php echo e(number_format($km_since - $SERVICE_KM)); ?>km.
        </p>
    <?php endif; ?>
</div>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\eurotaxisystem-main\resources\views/units/partials/_maintenance_health_bar.blade.php ENDPATH**/ ?>