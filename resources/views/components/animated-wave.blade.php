@props(['color' => 'text-blue-200'])

@php
    $waveId = 'gentle-wave-' . uniqid();
@endphp

<style>
    @keyframes move-forever {
        0% { transform: translate3d(-90px, 0, 0); }
        100% { transform: translate3d(85px, 0, 0); }
    }
    @keyframes wave-bounce {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-10px); }
    }
    .parallax > use {
        animation: move-forever 15s cubic-bezier(.55,.5,.45,.5) infinite;
    }
    .parallax > use:nth-child(1) { animation-delay: -2s; animation-duration: 5s; }
    .parallax > use:nth-child(2) { animation-delay: -3s; animation-duration: 8s; }
    .parallax > use:nth-child(3) { animation-delay: -4s; animation-duration: 11s; }
    .parallax > use:nth-child(4) { animation-delay: -5s; animation-duration: 16s; }
    
    .wave-container {
        animation: wave-bounce 6s ease-in-out infinite;
    }
</style>

<div class="absolute bottom-0 left-0 w-full overflow-hidden pointer-events-none z-0 opacity-50 wave-container {{ $color }}" style="height: 60%;">
    <svg class="absolute bottom-0 w-[200%] h-full" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 24 150 28" preserveAspectRatio="none" shape-rendering="auto">
        <defs>
            <path id="{{ $waveId }}" d="M-160 44c30 0 58-18 88-18s 58 18 88 18 58-18 88-18 58 18 88 18 v44h-352z" />
        </defs>
        <g class="parallax">
            <use xlink:href="#{{ $waveId }}" x="48" y="0" fill="currentColor" opacity="0.2" />
            <use xlink:href="#{{ $waveId }}" x="48" y="3" fill="currentColor" opacity="0.4" />
            <use xlink:href="#{{ $waveId }}" x="48" y="5" fill="currentColor" opacity="0.6" />
            <use xlink:href="#{{ $waveId }}" x="48" y="7" fill="currentColor" opacity="0.8" />
        </g>
    </svg>
</div>
