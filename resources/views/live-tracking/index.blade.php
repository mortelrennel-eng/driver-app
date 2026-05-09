@extends('layouts.app')

@section('title', 'Live Tracking - Euro System')
@section('page-heading', 'Live Tracking')
@section('page-subheading', 'Real-time GPS monitoring of all taxi units')
@section('main-padding', 'p-0')

@section('content')
<link rel="stylesheet" href="{{ asset('assets/leaflet/leaflet.css') }}" />
<style>
/* ── Full-height layout fixes ───────────────────────────────────── */
/* Force the content wrapper to be a proper flex column so        */
/* height:100% on children resolves correctly                     */
#appContentArea {
    display: flex !important;
    flex-direction: column !important;
    overflow: hidden !important;   /* no page scroll on tracking    */
}

/* Hide the top page-header bar while on Live Tracking            */
/* (page title + notification bar) to maximise map height         */
#appMainContent > header {
    display: none !important;
}

/* ── Root Container ─────────────────────────────────────────────── */
#liveTrackingRoot {
    display: flex;
    width: 100%;
    flex: 1;               /* fill the flex content area */
    min-height: 0;         /* allow shrink in flex column */
    overflow: hidden;
    position: relative;
    background: #f1f5f9;
}

/* ── App Sidebar Override (tracking mode) ───────────────────────── */
#appSidebar {
    transition: width 0.38s cubic-bezier(0.4,0,0.2,1),
                min-width 0.38s cubic-bezier(0.4,0,0.2,1),
                transform 0.38s cubic-bezier(0.4,0,0.2,1),
                opacity 0.28s ease !important;
}
#appSidebar.tracking-collapsed {
    width: 0 !important;
    min-width: 0 !important;
    transform: translateX(-100%) !important;
    opacity: 0 !important;
    overflow: hidden !important;
    pointer-events: none !important;
}

/* ── Nav Menu Button ──────────────────────────────────────────── */
/* When sidebar is open, button turns into a close (X) icon         */
#navMenuBtn {
    position: absolute;
    top: 14px;
    left: 14px;
    z-index: 900;
    width: 40px;
    height: 40px;
    border-radius: 10px;
    border: none;
    background: linear-gradient(135deg, #ca8a04 0%, #92400e 100%);
    color: #fff;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 4px 18px rgba(0,0,0,0.32);
    transition: all 0.22s cubic-bezier(0.4,0,0.2,1);
    outline: none;
}
#navMenuBtn:hover {
    background: linear-gradient(135deg, #b45309 0%, #78350f 100%);
    box-shadow: 0 6px 24px rgba(0,0,0,0.46);
    transform: scale(1.07);
}
#navMenuBtn svg {
    width: 17px; height: 17px;
    stroke: #fff; fill: none;
    stroke-width: 2; stroke-linecap: round; stroke-linejoin: round;
    transition: opacity 0.2s ease, transform 0.2s ease;
    position: absolute;
}
#navMenuBtn .icon-home  { opacity: 1;  transform: scale(1); }
#navMenuBtn .icon-close { opacity: 0;  transform: scale(0.5); }
#navMenuBtn.nav-open .icon-home  { opacity: 0;  transform: scale(0.5); }
#navMenuBtn.nav-open .icon-close { opacity: 1;  transform: scale(1); }

/* ── Unit Detail Popup (side-opening) ───────────────────────────── */
/* Hide the tip arrow — it points nowhere when popup is to the side */
.pro-popup .leaflet-popup-tip-container { display: none !important; }

/* Wrapper styling */
.pro-popup .leaflet-popup-content-wrapper {
    border-radius: 16px;
    box-shadow: 0 8px 40px rgba(0,0,0,0.18), 0 2px 10px rgba(0,0,0,0.08);
    border: 1px solid rgba(0,0,0,0.06);
    padding: 0;
    overflow: hidden;
    /* Always fit within the visible map viewport */
    max-height: calc(100vh - 180px);
    overflow-y: auto;
    /* Thin custom scrollbar */
    scrollbar-width: thin;
    scrollbar-color: #d1d5db transparent;
}
.pro-popup .leaflet-popup-content-wrapper::-webkit-scrollbar { width: 4px; }
.pro-popup .leaflet-popup-content-wrapper::-webkit-scrollbar-thumb { background:#d1d5db; border-radius:4px; }

/* Slide-in from left animation */
.pro-popup .leaflet-popup-content-wrapper {
    animation: popupSlideIn 0.22s cubic-bezier(0.34, 1.56, 0.64, 1) both;
}
@keyframes popupSlideIn {
    from { opacity: 0; transform: translateX(-18px) scale(0.96); }
    to   { opacity: 1; transform: translateX(0)     scale(1);    }
}

/* Close button */
.pro-popup .leaflet-popup-close-button {
    top: 10px !important; right: 10px !important;
    font-size: 18px !important; color: #6b7280 !important;
    z-index: 10;
}
.pro-popup .leaflet-popup-close-button:hover { color: #111 !important; }

/* ── Unit Explorer Panel ────────────────────────────────────────── */
#unitExplorerPanel {
    width: 272px;
    min-width: 272px;
    height: 100%;
    background: #fff;
    border-right: 1px solid #e5e7eb;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    flex-shrink: 0;
    transition: width 0.38s cubic-bezier(0.4,0,0.2,1),
                min-width 0.38s cubic-bezier(0.4,0,0.2,1),
                opacity 0.28s ease,
                transform 0.38s cubic-bezier(0.4,0,0.2,1);
    transform: translateX(0);
    opacity: 1;
    z-index: 20;
}
#unitExplorerPanel.panel-collapsed {
    width: 0 !important;
    min-width: 0 !important;
    opacity: 0 !important;
    transform: translateX(-24px) !important;
    pointer-events: none !important;
    border-right: none !important;
}

/* ── Map Area ───────────────────────────────────────────────────── */
#mapArea {
    flex: 1;
    min-width: 0;
    height: 100%;
    display: flex;
    flex-direction: column;
    position: relative;
    overflow: hidden;
}

/* ── Map Header ─────────────────────────────────────────────────── */
#mapHeaderBar {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 7px 12px;
    background: rgba(255,255,255,0.97);
    border-bottom: 1px solid #e5e7eb;
    flex-shrink: 0;
    flex-wrap: nowrap;
    backdrop-filter: blur(4px);
    min-height: 46px;
    overflow: hidden;
    z-index: 5;
}

/* ── Leaflet Map ────────────────────────────────────────────────── */
#mapViewer {
    width: 100%;
    flex: 1;
    min-height: 0;
    z-index: 1;
}

/* ── Left Floating Controls ─────────────────────────────────────── */
/* Each button is individually absolute-positioned — mirrors right side */
#navMenuBtn {
    position: absolute;
    top: 56px;        /* same vertical start as #mapTypeSwitcher */
    left: 10px;
    z-index: 900;
    width: 34px;
    height: 34px;
    border-radius: 8px;
    border: none;
    background: linear-gradient(135deg, #ca8a04 0%, #92400e 100%);
    color: #fff;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 2px 8px rgba(0,0,0,0.28);
    transition: all 0.22s cubic-bezier(0.4,0,0.2,1);
    outline: none;
}
#navMenuBtn:hover { background: linear-gradient(135deg,#b45309,#78350f); transform:scale(1.08); }

#mapToggleBtn {
    position: absolute;
    top: 95px;        /* 56px + 34px button + 5px gap = 95px — matches right side */
    left: 10px;
    z-index: 900;
    width: 34px;
    height: 34px;
    border-radius: 8px;
    border: none;
    background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
    color: #fff;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 2px 8px rgba(0,0,0,0.28);
    transition: all 0.22s cubic-bezier(0.4,0,0.2,1);
    outline: none;
}
#mapToggleBtn:hover { background: linear-gradient(135deg,#334155,#1e293b); transform:scale(1.08); }

/* Hamburger bars */
.hb { display: flex; flex-direction: column; gap: 3.5px; }
.hb span {
    display: block; width: 15px; height: 2px;
    background: #fff; border-radius: 2px;
    transition: all 0.3s ease; transform-origin: center;
}
#mapToggleBtn.open .hb span:nth-child(1) { transform: rotate(45deg) translate(4px,4px); }
#mapToggleBtn.open .hb span:nth-child(2) { opacity: 0; transform: scaleX(0); }
#mapToggleBtn.open .hb span:nth-child(3) { transform: rotate(-45deg) translate(4px,-4px); }

/* ── Map Type Switcher ─────────────────────────────────────────────
   Placed TOP-RIGHT of the map, below the fullscreen badge.
   This avoids overlapping Leaflet's zoom controls (bottom-right).  */
#mapTypeSwitcher {
    position: absolute;
    top: 56px;   /* same level as left controls, below header */
    right: 10px;
    z-index: 900;
    display: flex;
    flex-direction: column;
    gap: 5px;
}
.map-type-btn {
    width: 36px; height: 36px;
    border-radius: 9px; border: 2px solid rgba(255,255,255,0.2);
    background: rgba(15,23,42,0.78);
    backdrop-filter: blur(8px);
    color: #fff; font-size: 16px;
    cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    box-shadow: 0 2px 12px rgba(0,0,0,0.3);
    transition: all 0.22s ease;
    outline: none;
    position: relative;
}
.map-type-btn:hover { transform: scale(1.1); background: rgba(30,41,59,0.92); }
.map-type-btn.active {
    border-color: #eab308;
    background: rgba(202,138,4,0.85);
    box-shadow: 0 0 0 3px rgba(234,179,8,0.3), 0 2px 12px rgba(0,0,0,0.3);
}
.map-type-btn:hover .btn-tip { opacity: 1; }

/* Tooltip: appears to the LEFT of each button (since switcher is on right side) */
.map-type-btn .btn-tip {
    position: absolute;
    right: 42px;       /* left of the button */
    top: 50%;
    transform: translateY(-50%);
    background: rgba(15,23,42,0.9); color: #fff;
    font-size: 9px; font-weight: 800; letter-spacing: 0.06em;
    text-transform: uppercase; white-space: nowrap;
    padding: 3px 7px; border-radius: 5px;
    opacity: 0; pointer-events: none;
    transition: opacity 0.2s ease;
}
.map-type-btn:hover .btn-tip { opacity: 1; }

/* Map tile transition */
#mapViewer .leaflet-tile-pane { transition: opacity 0.4s ease; }

/* ── Fullscreen Badge ───────────────────────────────────────────────
   Shown below the map type switcher when unit panel is hidden.     */
#fsBadge {
    position: absolute;
    /* below: 56px top + (34px btn × 2) + (5px gap × 2) + 10px margin = 145px */
    top: 145px;
    right: 10px;
    z-index: 800;
    display: flex;
    align-items: center;
    gap: 7px;
    padding: 6px 12px 6px 9px;
    background: rgba(15,23,42,0.85);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255,255,255,0.12);
    border-radius: 9px;
    color: #fff;
    font-size: 9.5px;
    font-weight: 800;
    letter-spacing: 0.09em;
    text-transform: uppercase;
    box-shadow: 0 4px 20px rgba(0,0,0,0.28);
    opacity: 0;
    transform: translateY(-10px) scale(0.93);
    transition: opacity 0.32s ease, transform 0.32s cubic-bezier(0.4,0,0.2,1);
    pointer-events: none;
    user-select: none;
}
#fsBadge.show {
    opacity: 1;
    transform: translateY(0) scale(1);
}
.fs-dot {
    width: 7px; height: 7px;
    background: #22c55e; border-radius: 50%;
    position: relative; flex-shrink: 0;
}
.fs-dot::after {
    content: '';
    position: absolute; inset: -3px; border-radius: 50%;
    background: rgba(34,197,94,0.35);
    animation: fsPing 1.6s ease-in-out infinite;
}
@keyframes fsPing {
    0%,100% { transform:scale(1); opacity:.8; }
    50%      { transform:scale(1.7); opacity:0; }
}

/* ── Unit Panel Internals ───────────────────────────────────────── */
.unit-item {
    cursor: pointer;
    border-left: 3px solid transparent;
    transition: all 0.2s cubic-bezier(0.4,0,0.2,1);
    overflow: hidden;
}
.unit-item:hover {
    background-color: #fefce8;
    transform: scale(1.01);
    z-index: 10;
    box-shadow: 0 4px 12px rgba(0,0,0,.05);
}
.unit-item.selected {
    background-color: #fef9c3;
    border-left-color: #ca8a04;
}
.driver-reveal {
    max-height: 0; opacity: 0; overflow: hidden;
    transition: all 0.4s cubic-bezier(0.4,0,0.2,1);
}
.unit-item:hover .driver-reveal,
.unit-item.selected .driver-reveal {
    max-height: 100px; opacity: 1;
    margin-top: 10px; margin-bottom: 5px;
}
.unit-scroll {
    flex: 1; overflow-y: auto; overflow-x: hidden;
}
.unit-scroll::-webkit-scrollbar { width: 4px; }
.unit-scroll::-webkit-scrollbar-thumb { background: #d1d5db; border-radius: 4px; }

/* ── Leaflet overrides ──────────────────────────────────────────── */
.custom-div-icon { background: none; border: none; }
.leaflet-popup-content-wrapper { border-radius: 12px; padding: 4px; }
.status-dot { width:8px;height:8px;border-radius:50%;display:inline-block; }

/* ── Mobile ─────────────────────────────────────────────────────── */
@media (max-width: 768px) {
    #unitExplorerPanel {
        position: absolute;
        top: 0; left: 0; bottom: 0;
        box-shadow: 4px 0 28px rgba(0,0,0,.18);
        width: 256px; min-width: 256px;
        z-index: 500;
    }
    #mapHeaderBar { padding-left: 58px; }
}
</style>
<div id="liveTrackingRoot">

    {{-- ═══ Unit Explorer Panel ═══════════════════════════════════ --}}
    <div id="unitExplorerPanel">

        <div class="px-4 py-3 border-b bg-gray-50/70 flex flex-col gap-2.5 flex-shrink-0">
            <div class="flex justify-between items-center">
                <h3 class="font-bold text-gray-800 text-sm flex items-center gap-2">
                    <i data-lucide="layers" class="w-4 h-4 text-yellow-600"></i>
                    Unit Explorer
                </h3>
                <span class="text-[9px] font-black uppercase tracking-widest text-gray-400">
                    {{ count($tracked_units) }} units
                </span>
            </div>

            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i data-lucide="search" class="h-3.5 w-3.5 text-gray-400"></i>
                </div>
                <input type="text" id="unitSearchInput"
                    class="block w-full pl-9 pr-3 py-2 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 transition-all outline-none"
                    placeholder="Search plate...">
            </div>

            <select id="statusFilterSelect"
                class="block w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 transition-all outline-none bg-white">
                <option value="">All Fleet Units</option>
                <option value="active">Active (On/Idle)</option>
                <option value="offline">Offline / Stopped</option>
            </select>
        </div>

        <div class="unit-scroll" id="unitList">
            @forelse($tracked_units as $unit)
                <div class="unit-item p-2.5 border-b border-gray-100 {{ $unit->gps_status === 'offline' ? 'opacity-70' : '' }}"
                    data-unit-id="{{ $unit->id }}"
                    data-plate-number="{{ $unit->plate_number }}"
                    data-driver-name="{{ $unit->driver_name ?? '' }}"
                    data-secondary-driver="{{ $unit->secondary_driver ?? '' }}"
                    data-status="{{ $unit->gps_status }}"
                    onclick="selectUnit(this)">

                    <div class="flex justify-between items-center mb-1.5">
                        <div class="font-black text-[15px] text-gray-900 tracking-tight leading-none uppercase">
                            {{ $unit->plate_number }}
                        </div>
                        <div class="status-badge" id="status-unit-{{ $unit->id }}">
                            @php
                                $sc = ['moving'=>'bg-green-50 text-green-700 border-green-100','idle'=>'bg-yellow-50 text-yellow-700 border-yellow-100','stopped'=>'bg-blue-50 text-blue-700 border-blue-100','offline'=>'bg-gray-50 text-gray-400 border-gray-100'][$unit->gps_status] ?? 'bg-gray-50 text-gray-400 border-gray-100';
                            @endphp
                            <span class="px-1.5 py-0.5 text-[8.5px] font-black uppercase tracking-tighter rounded-sm border {{ $sc }}">
                                {{ ucfirst($unit->gps_status) }}
                            </span>
                        </div>
                    </div>

                    <div class="driver-reveal flex flex-col gap-1.5 border-t border-gray-50 pt-1">
                        <div class="flex items-center gap-1.5 min-w-0">
                            <i data-lucide="user" class="w-3 h-3 text-blue-500 shrink-0"></i>
                            <span class="text-[8.5px] font-black text-gray-400 uppercase shrink-0">D1</span>
                            <span class="driver-primary text-[11px] font-bold text-gray-700 leading-none truncate">{{ $unit->driver_name ?: 'None' }}</span>
                        </div>
                        <div class="secondary-driver-container flex items-center gap-1.5 min-w-0 {{ !$unit->secondary_driver ? 'hidden' : '' }}">
                            <i data-lucide="users" class="w-3 h-3 text-gray-400 shrink-0"></i>
                            <span class="text-[8.5px] font-black text-gray-400 uppercase shrink-0">D2</span>
                            <span class="driver-secondary text-[11px] font-bold text-gray-500 leading-none truncate">{{ $unit->secondary_driver }}</span>
                        </div>
                    </div>

                    <div class="flex justify-between items-center text-[10px] font-bold text-gray-400 uppercase tracking-tighter">
                        <div class="flex items-center gap-1" id="engine-status-container-{{ $unit->id }}">
                            <i data-lucide="zap" class="w-3 h-3 {{ $unit->ignition_status ? 'text-green-500' : 'text-gray-300' }}"></i>
                            <span>{{ $unit->ignition_status ? 'Engine ON' : 'Engine OFF' }}</span>
                        </div>
                        <div class="flex items-baseline gap-0.5">
                            <span class="text-[13px] font-black text-gray-800 unit-speed">{{ number_format($unit->speed ?? 0, 1) }}</span>
                            <span class="text-[8.5px]">km/h</span>
                        </div>
                    </div>
                </div>
            @empty
                <div class="p-10 text-center text-gray-400">
                    <i data-lucide="inbox" class="w-12 h-12 mx-auto mb-3 opacity-20"></i>
                    <p class="text-sm font-medium">No units found</p>
                </div>
            @endforelse
        </div>
    </div>

    {{-- ═══ Map Area ═══════════════════════════════════════════════ --}}
    <div id="mapArea">

        {{-- Header / Stats Bar (title + stats + API only) --}}
        <div id="mapHeaderBar">
            {{-- Left: Map Title --}}
            <h3 class="font-bold text-gray-800 text-sm flex items-center gap-2 flex-shrink-0">
                <i data-lucide="map" class="w-4 h-4 text-blue-600"></i>
                <span class="hidden sm:inline">Live Fleet Map</span>
            </h3>

            {{-- Right: Stats + API --}}
            <div class="flex items-center gap-2 ml-auto flex-shrink-0">
                <div class="flex items-center divide-x divide-gray-200 border border-gray-200 rounded-lg bg-white overflow-hidden shadow-sm text-center">
                    <div class="flex flex-row px-2 py-1 items-center gap-1">
                        <span class="text-[9px] text-gray-400 uppercase font-black tracking-widest">Total</span>
                        <span id="stat-total" class="text-xs font-black text-gray-900">{{ $stats['total'] ?? 0 }}</span>
                    </div>
                    <div class="flex flex-row px-2 py-1 items-center gap-1 bg-green-50/40">
                        <span class="text-[9px] text-green-500 uppercase font-black tracking-widest">Moving</span>
                        <span id="stat-active" class="text-xs font-black text-green-600">{{ $stats['moving'] ?? 0 }}</span>
                    </div>
                    <div class="flex flex-row px-2 py-1 items-center gap-1 bg-yellow-50/40">
                        <span class="text-[9px] text-yellow-500 uppercase font-black tracking-widest">Idle</span>
                        <span id="stat-idle" class="text-xs font-black text-yellow-600">{{ $stats['idle'] ?? 0 }}</span>
                    </div>
                    <div class="flex flex-row px-2 py-1 items-center gap-1 bg-blue-50/40">
                        <span class="text-[9px] text-blue-500 uppercase font-black tracking-widest">Stopped</span>
                        <span id="stat-stopped" class="text-xs font-black text-blue-600">{{ $stats['stopped'] ?? 0 }}</span>
                    </div>
                    <div class="flex flex-row px-2 py-1 items-center gap-1 bg-gray-50">
                        <span class="text-[9px] text-gray-400 uppercase font-black tracking-widest">Offline</span>
                        <span id="stat-offline" class="text-xs font-black text-gray-500">{{ $stats['offline'] ?? 0 }}</span>
                    </div>
                </div>
                <div class="flex items-center gap-1.5 border-l pl-2 border-gray-200">
                    @if($apiActive)
                        <span class="relative flex h-2 w-2">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-green-500"></span>
                        </span>
                        <span class="text-[10px] font-black text-green-600 uppercase hidden lg:inline">API Online</span>
                    @else
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-red-400"></span>
                        <span class="text-[10px] font-black text-red-500 uppercase hidden lg:inline">Setup Required</span>
                    @endif
                </div>
            </div>
        </div>

        {{-- API Warning --}}
        @if(!$apiActive)
        <div class="bg-amber-50 border-b border-amber-200 px-5 py-2 flex items-center gap-3 flex-shrink-0">
            <i data-lucide="alert-triangle" class="w-4 h-4 text-amber-600 flex-shrink-0"></i>
            <p class="text-xs text-amber-800">
                <strong>Tracksolid API inactive.</strong> Live GPS cannot be fetched.
                Use <a href="https://tracksolidpro.com/" target="_blank" class="font-bold underline">Tracksolid Pro</a> as backup.
            </p>
        </div>
        @endif

        {{-- Leaflet Map --}}
        <div id="mapViewer"></div>

        {{-- ══ LEFT FLOATING CONTROLS (top-left, over the map) ══════ --}}

        {{-- 🏠 Nav Toggle --}}
            <button id="navMenuBtn" onclick="toggleNavOverlay()" title="Toggle Navigation">
                <svg class="icon-home" viewBox="0 0 24 24"
                     style="width:15px;height:15px;stroke:#fff;fill:none;stroke-width:2;
                            stroke-linecap:round;stroke-linejoin:round;
                            transition:opacity .2s,transform .2s;position:absolute;">
                    <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                    <polyline points="9 22 9 12 15 12 15 22"/>
                </svg>
                <svg class="icon-close" viewBox="0 0 24 24"
                     style="width:15px;height:15px;stroke:#fff;fill:none;stroke-width:2;
                            stroke-linecap:round;stroke-linejoin:round;
                            transition:opacity .2s,transform .2s;position:absolute;">
                    <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>

            {{-- Unit Panel Toggle: ☰ show / ✕ hide --}}
            <button id="mapToggleBtn" onclick="toggleUnitPanel()" title="Hide Unit Panel" class="open">
                <div class="hb"><span></span><span></span><span></span></div>
        </button>

        {{-- ══ RIGHT FLOATING CONTROLS (top-right, over the map) ══════ --}}
        {{-- Map Type Switcher --}}
        <div id="mapTypeSwitcher">
            <button class="map-type-btn active" id="mapBtnDefault" onclick="setMapType('default')" title="Default Map">
                🗺️<span class="btn-tip">Default Map</span>
            </button>
            <button class="map-type-btn" id="mapBtnSatellite" onclick="setMapType('satellite')" title="Satellite View">
                🛰️<span class="btn-tip">Satellite View</span>
            </button>
        </div>

        {{-- Fullscreen Badge --}}
        <div id="fsBadge">
            <div class="fs-dot"></div>
            <span>Fullscreen Map Mode</span>
        </div>

    </div>{{-- /mapArea --}}

</div>{{-- /liveTrackingRoot --}}

<script src="{{ asset('assets/leaflet/leaflet.js') }}"></script>
<script src="{{ asset('js/realtime-tracking.js') }}?v={{ time() }}"></script>
<script>
/* ================================================================
   LIVE TRACKING — Auto-collapse Nav + Unit Panel Toggle
================================================================ */
(function () {
    'use strict';

    const PANEL_KEY = 'lt_panel';          // sessionStorage key
    const ANIM_MS   = 400;                 // match CSS transition

    /* DOM refs */
    const appSidebar = document.getElementById('appSidebar');
    const unitPanel  = document.getElementById('unitExplorerPanel');
    const toggleBtn  = document.getElementById('mapToggleBtn');
    const fsBadge    = document.getElementById('fsBadge');

    /* ── State ─────────────────────────────────────────────────── */
    // Restore from sessionStorage; default = panel VISIBLE
    let panelVisible = sessionStorage.getItem(PANEL_KEY) !== '0';

    /* ── Helpers ────────────────────────────────────────────────── */
    function invalidateMap() {
        // Call multiple times to catch transition frames
        [50, 200, ANIM_MS + 20, ANIM_MS + 100].forEach(delay => {
            setTimeout(() => {
                if (window.liveMap && typeof window.liveMap.invalidateSize === 'function') {
                    window.liveMap.invalidateSize({ animate: false });
                }
            }, delay);
        });
    }

    // ResizeObserver: auto-invalidate whenever map container resizes
    if (window.ResizeObserver) {
        const mapEl = document.getElementById('mapViewer');
        if (mapEl) {
            new ResizeObserver(() => {
                if (window.liveMap) window.liveMap.invalidateSize({ animate: false });
            }).observe(mapEl);
        }
    }

    function applyPanelState() {
        if (!unitPanel || !toggleBtn || !fsBadge) return;

        if (panelVisible) {
            unitPanel.classList.remove('panel-collapsed');
            toggleBtn.classList.add('open');
            fsBadge.classList.remove('show');
            toggleBtn.setAttribute('title', 'Hide Unit Panel');
        } else {
            unitPanel.classList.add('panel-collapsed');
            toggleBtn.classList.remove('open');
            fsBadge.classList.add('show');
            toggleBtn.setAttribute('title', 'Show Unit Panel');
        }

        invalidateMap();
    }

    /* ── Public toggle (called by button onclick) ───────────────── */
    window.toggleUnitPanel = function () {
        panelVisible = !panelVisible;
        sessionStorage.setItem(PANEL_KEY, panelVisible ? '1' : '0');
        applyPanelState();
    };

    /* ── Collapse the app's left-nav when on Live Tracking ─────── */
    function collapseAppNav() {
        if (appSidebar) appSidebar.classList.add('tracking-collapsed');
    }

    function restoreAppNav() {
        if (appSidebar) {
            appSidebar.classList.remove('tracking-collapsed');
            appSidebar.classList.remove('nav-overlay-open');
        }
    }

    /* ── Nav Push Toggle (sidebar slides in, pushes content right) ── */
    const navMenuBtn = document.getElementById('navMenuBtn');
    let navOpen = false;

    window.toggleNavOverlay = function () {
        navOpen ? closeNavOverlay() : openNavOverlay();
    };

    function openNavOverlay() {
        if (!appSidebar) return;
        navOpen = true;
        // Simply un-collapse the sidebar — flex layout pushes content right
        appSidebar.classList.remove('tracking-collapsed');
        if (navMenuBtn) navMenuBtn.classList.add('nav-open');
    }

    window.closeNavOverlay = function () {
        if (!appSidebar) return;
        navOpen = false;
        appSidebar.classList.add('tracking-collapsed');
        if (navMenuBtn) navMenuBtn.classList.remove('nav-open');
    };

    /* ── Restore nav when user navigates away ───────────────────── */
    // Sidebar links — restore everything before navigating
    document.querySelectorAll('#appSidebar a[href]').forEach(function (link) {
        link.addEventListener('click', function () {
            restoreAppNav();
            sessionStorage.removeItem(PANEL_KEY);
        });
    });

    // Browser back/forward / page unload
    window.addEventListener('pagehide', function () {
        restoreAppNav();
    });

    /* ── Init (safe for both before & after DOMContentLoaded) ───── */
    function init() {
        collapseAppNav();
        applyPanelState();

        // Reinitialise Lucide icons (panel was hidden during first init)
        if (window.lucide && window.lucide.createIcons) {
            window.lucide.createIcons();
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

})();

/* ================================================================
   MAP TYPE SWITCHER
================================================================ */
(function () {
    const MAPTYPE_KEY = 'lt_maptype';
    let currentType = localStorage.getItem(MAPTYPE_KEY) || 'default';
    let satelliteLayer = null;

    function getSatelliteLayer() {
        if (!satelliteLayer) {
            satelliteLayer = L.tileLayer(
                'https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}',
                { attribution: 'Tiles &copy; Esri &mdash; Source: Esri, USDA, USGS, GIS User Community', maxZoom: 19 }
            );
        }
        return satelliteLayer;
    }

    window.setMapType = function (type) {
        const lm = window.liveMap;
        if (!lm) { setTimeout(() => window.setMapType(type), 300); return; }

        if (type === 'satellite') {
            if (window.defaultTileLayer) lm.removeLayer(window.defaultTileLayer);
            getSatelliteLayer().addTo(lm);
        } else {
            lm.removeLayer(getSatelliteLayer());
            if (window.defaultTileLayer) window.defaultTileLayer.addTo(lm);
        }

        currentType = type;
        localStorage.setItem(MAPTYPE_KEY, type);

        document.getElementById('mapBtnDefault')?.classList.toggle('active', type === 'default');
        document.getElementById('mapBtnSatellite')?.classList.toggle('active', type === 'satellite');
    };

    // Apply persisted type on load
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => window.setMapType(currentType));
    } else {
        // Wait for liveMap to be initialized by realtime-tracking.js
        setTimeout(() => window.setMapType(currentType), 600);
    }
})();
</script>
@endsection