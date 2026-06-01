{{--
    Maintenance Health Bar Partial (Visual Timeline)
    Shows for GPS-equipped units only.
    
    Logic:
    - Interval = 5,000 km
    - Color: Green -> Yellow -> Orange -> Red (Overdue)
--}}
@php
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
@endphp

@if($has_gps)
<div class="maintenance-timeline w-full mt-2">
    <div class="flex items-center justify-between mb-1">
        <div class="flex items-center gap-1.5">
            <span class="w-2 h-2 rounded-full {{ str_replace('bg-', 'bg-', $bar_color) }} animate-pulse"></span>
            <span class="text-[10px] font-black uppercase tracking-wider {{ $text_color }}">{{ $label }}</span>
        </div>
        <span class="text-[10px] font-bold text-gray-400 tabular-nums">
            {{ number_format($km_since) }} / {{ number_format($SERVICE_KM) }} KM
        </span>
    </div>
    
    {{-- Timeline Bar --}}
    <div class="relative h-2 w-full bg-gray-100 rounded-full overflow-hidden border border-gray-200/50 shadow-inner">
        {{-- Progress Fill --}}
        <div class="absolute inset-y-0 left-0 {{ $bar_color }} {{ $pulse }} rounded-full transition-all duration-1000 ease-out"
             style="width: {{ $pct }}%">
            {{-- Glossy effect --}}
            <div class="absolute inset-0 bg-gradient-to-b from-white/20 to-transparent"></div>
        </div>
        
        {{-- Milestone Markers (Visual Timeline ticks) --}}
        <div class="absolute inset-0 flex justify-between px-2 pointer-events-none">
            <div class="w-px h-full bg-white/30"></div>
            <div class="w-px h-full bg-white/30"></div>
            <div class="w-px h-full bg-white/30"></div>
            <div class="w-px h-full bg-white/30"></div>
        </div>
    </div>
    
    @if($is_overdue)
        <p class="text-[9px] font-bold text-red-500 mt-1 italic tracking-tight">
            Unit has exceeded the {{ number_format($SERVICE_KM) }}km service interval by {{ number_format($km_since - $SERVICE_KM) }}km.
        </p>
    @endif
</div>
@endif
