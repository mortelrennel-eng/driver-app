<!DOCTYPE html>
<html lang="en">

<head>
    <!-- CRITICAL DESKTOP SELF-HEALING GUARD:
         If the user is on a desktop/laptop browser, programmatically purge any active Service Workers
         instantly on page load to prevent POST requests (like Test Chime) from being intercepted and failing. -->
    <script>
        (function() {
            const isCapacitor = (typeof window !== 'undefined' && window.Capacitor) || 
                                navigator.userAgent.includes('Capacitor') || 
                                navigator.userAgent.includes('Android');
            if (!isCapacitor && 'serviceWorker' in navigator) {
                navigator.serviceWorker.getRegistrations().then(registrations => {
                    if (registrations.length === 0) return;
                    const promises = registrations.map(reg => {
                        return reg.unregister().then(success => {
                            if (success) {
                                console.log('[Self-Healing] Stale desktop Service Worker successfully unregistered.');
                                return true;
                            }
                            return false;
                        });
                    });
                    Promise.all(promises).then(results => {
                        if (results.some(r => r === true)) {
                            // Reload once to clear browser service worker interception caches instantly!
                            setTimeout(() => window.location.reload(), 300);
                        }
                    });
                }).catch(err => console.error('Service Worker unregister failed:', err));
            }
        })();
    </script>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- Aggressive silence for Tailwind and other dev warnings - MUST BE FIRST -->
    <script>
        (function() {
            window.tailwind = { config: { silent: true } };
            const suppressStrings = ['cdn.tailwindcss.com', 'Tailwind CSS', 'Play CDN', 'production warning'];
            const methods = ['warn', 'log', 'info', 'error', 'debug'];
            methods.forEach(method => {
                const original = console[method];
                console[method] = function(...args) {
                    const msg = args.map(arg => String(arg)).join(' ').toLowerCase();
                    if (msg && suppressStrings.some(s => msg.includes(s.toLowerCase()))) {
                        return;
                    }
                    if (original) original.apply(console, args);
                };
            });
        })();
    </script>

    <!-- SEO Meta Tags -->
    <meta name="description" content="Euro Taxi System - Professional taxi fleet management system in the Philippines. Real-time tracking, driver management, and comprehensive taxi business solutions.">
    <meta name="keywords" content="euro taxi, taxi system, fleet management, taxi business philippines, vehicle tracking, driver management, taxi dispatch, transportation system">
    <meta name="author" content="Euro Taxi System">
    <meta name="robots" content="index, follow">
    <meta property="og:title" content="Euro Taxi System | Professional Taxi Fleet Management">
    <meta property="og:description" content="Complete taxi fleet management system with real-time tracking and driver management in the Philippines">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ config('app.url', 'https://www.eurotaxisystem.site') }}">
    <meta property="og:image" content="{{ asset('image/logo.png') }}">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Euro Taxi System | Taxi Fleet Management">
    <meta name="twitter:description" content="Professional taxi fleet management system in the Philippines">
    
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- Base Asset URL -->
    <meta name="asset-url" content="{{ asset('') }}">

    <!-- Capacitor Native Bridge -->
    <script src="/capacitor.js"></script>
    <script src="/capacitor_plugins.js"></script>

    <title>{{ config('app.name', 'Euro Taxi System') }}</title>

    <!-- Favicon -->
    <link rel="shortcut icon" href="{{ asset('favicon_euro_transparent.png') }}?v=1.6">
    <link rel="icon" type="image/png" href="{{ asset('favicon_euro_transparent.png') }}?v=1.6">
    <link rel="apple-touch-icon" href="{{ asset('favicon_euro_transparent.png') }}?v=1.6">
    <link rel="manifest" href="{{ asset('manifest.json') }}?v=1.7">

    <!-- Critical Assets (Local) -->
    <script src="{{ asset('assets/tailwind.min.js') }}?v=stable_3.4.1"></script>
    <link rel="stylesheet" href="{{ asset('assets/fontawesome/all.min.css') }}?v=stable_6.4.0">
    <link rel="stylesheet" href="{{ asset('assets/inter/inter.css') }}?v=stable_3.19.3">

    <style>
        input::-webkit-outer-spin-button,
        input::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }
        input[type=number] { -moz-appearance: textfield; }
        /* Prevent FOUC: pre-size icon placeholders so sidebar doesn't reflow */
        i[data-lucide] { display: inline-block; width: 1rem; height: 1rem; vertical-align: middle; flex-shrink: 0; }
        .sidebar-item i[data-lucide] { width: 1.25rem; height: 1.25rem; }
        
        /* Smooth page transitions */
        #appMainContent { 
            transition: opacity 0.15s ease-in-out, transform 0.15s ease-in-out; 
        }
        .page-transitioning #appMainContent {
            opacity: 0.7;
            transform: scale(0.995);
        }
        
        /* Prevent sidebar flicker during navigation on desktop only */
        @media (min-width: 768px) {
            #appSidebar {
                transition: none !important;
            }
        }
        
        /* Loading state for navigation */
        .nav-loading {
            opacity: 0.6;
            pointer-events: none;
        }
        .nav-loading::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 16px;
            height: 16px;
            margin: -8px 0 0 -8px;
            border: 2px solid transparent;
            border-top-color: #fbbf24;
            border-radius: 50%;
            animation: spin 0.6s linear infinite;
        }
        @keyframes spin { to { transform: rotate(360deg); } }

        /* Responsive Mobile Drawer Styles (Buttery-Smooth Transitions) */
        @media (max-width: 767px) {
            #appSidebar {
                position: fixed !important;
                top: 0;
                bottom: 0;
                height: 100dvh !important;
                max-height: 100dvh !important;
                left: 0 !important;
                width: 280px !important;
                z-index: 100 !important;
                transform: translateX(-105%) !important;
                transition: transform 0.45s cubic-bezier(0.25, 1, 0.5, 1) !important;
                display: flex !important;
                visibility: hidden;
                pointer-events: none;
                overflow-y: auto !important;
                will-change: transform;
            }
            #appSidebar.show {
                transform: translateX(0) !important;
                visibility: visible !important;
                pointer-events: auto !important;
            }
            #sidebarBackdrop {
                position: fixed;
                inset: 0;
                background-color: rgba(15, 23, 42, 0);
                backdrop-filter: blur(0px);
                z-index: 90;
                visibility: hidden;
                pointer-events: none;
                transition: background-color 0.45s cubic-bezier(0.25, 1, 0.5, 1), backdrop-filter 0.45s cubic-bezier(0.25, 1, 0.5, 1), visibility 0.45s cubic-bezier(0.25, 1, 0.5, 1) !important;
                display: block !important; /* Always active layout-wise, visual state controlled by visibility */
                will-change: background-color, backdrop-filter;
            }
            #sidebarBackdrop.show {
                background-color: rgba(15, 23, 42, 0.5) !important;
                backdrop-filter: blur(4px) !important;
                visibility: visible !important;
                pointer-events: auto !important;
            }
        }
    </style>
    
    <!-- Lucide Icons (Local) -->
    <script src="{{ asset('assets/lucide.min.js') }}"></script>

    <!-- Custom CSS -->
    <link href="{{ asset('assets/app.css') }}?v=1.8" rel="stylesheet">
    @stack('styles')

    <!-- Custom JS -->
    <script src="{{ asset('assets/app.js') }}?v=1.8"></script>

    <!-- Chart.js for Dashboard (Local) -->
    <script src="{{ asset('assets/chart.min.js') }}"></script>
    <script src="{{ asset('assets/chartjs-plugin-datalabels.min.js') }}"></script>

    @auth
        @php
            $user = auth()->user();
            $cacheKey = 'header_notifs_' . $user->id;
            
            $notificationService = app(\App\Services\NotificationService::class);
            $headerNotifications = $notificationService->getGlobalNotifications();


            // ─── SYNC WITH READ STATUS (COOKIE) ───
            $readNotifIds = [];
            if (isset($_COOKIE['read_notifs'])) {
                try {
                    $rawCookie = $_COOKIE['read_notifs'];
                    $decodedVal = stripslashes($rawCookie);
                    $readData = json_decode($decodedVal, true);
                    if (!$readData) {
                        $readData = json_decode($rawCookie, true);
                    }
                    
                    // Handle legacy array format gracefully
                    if (is_array($readData) && array_is_list($readData)) {
                        $readNotifIds = array_map('strval', $readData);
                    } elseif (is_array($readData)) {
                        $nowMs = time() * 1000;
                        foreach ($readData as $id => $timestamp) {
                            if ($nowMs - $timestamp < 1800000) { // 30 minutes in milliseconds
                                $readNotifIds[] = (string)$id;
                            }
                        }
                    }
                } catch (\Exception $e) {}
            }
            
            // Filter out ALL read notifications across all categories
            $headerNotifications = array_filter($headerNotifications, function($n) use ($readNotifIds) {
                $notifId = isset($n['id']) ? (string)$n['id'] : md5(($n['title'] ?? '') . ($n['message'] ?? ''));
                return !in_array($notifId, $readNotifIds);
            });

            $headerNotificationCount = count($headerNotifications);
            
            // Calculate specific counts
            $stockNotifCount = collect($headerNotifications)->where('type', 'low_stock')->count();
            $systemNotifCount = $headerNotificationCount - $stockNotifCount;

            // Sort logic: "Action Required" items first, then others by recency
            // We'll use a custom property 'priority' (0 for standard, 1 for Action Required/High)
            foreach($headerNotifications as &$notif) {
                if (isset($notif['time'])) {
                    $t = strtoupper($notif['time']);
                    $notif['priority'] = ($t === 'ACTION REQUIRED' || $t === 'REORDER NOW' || $t === 'NOW' || $t === 'CRITICAL') ? 1 : 0;
                } else {
                    $notif['priority'] = 0;
                }
            }
            unset($notif);

            usort($headerNotifications, function($a, $b) {
                // Priority descending (1 first)
                if ($a['priority'] !== $b['priority']) {
                    return $b['priority'] - $a['priority'];
                }
                
                // Secondary sort: Recency (Newest first)
                $timeA = isset($a['timestamp']) ? $a['timestamp']->timestamp : 0;
                $timeB = isset($b['timestamp']) ? $b['timestamp']->timestamp : 0;
                
                return $timeB - $timeA;
            });
        @endphp

        <!-- Main Layout -->
        <div class="flex h-screen overflow-hidden" id="appLayout">
            <!-- Sidebar Mobile Backdrop -->
            <div id="sidebarBackdrop" onclick="toggleMobileSidebar()" class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm z-40 hidden md:hidden"></div>
            <aside id="appSidebar" class="hidden md:flex w-16 lg:w-60 bg-white shadow-lg flex-shrink-0 transition-all duration-300 overflow-x-hidden relative h-full">
                <div class="h-full flex flex-col w-full">
                    <!-- Logo & Mobile Close Trigger -->
                    <div class="px-4 py-3 md:p-2 lg:p-4 border-b flex flex-row md:flex-col items-center justify-between md:justify-center flex-shrink-0 w-full relative bg-white">
                        <!-- Logo & Brand info -->
                        <div class="flex flex-col items-start md:items-center min-w-0">
                            <img src="{{ asset('uploads/logo.png') }}" alt="Euro System Logo" class="h-9 md:h-8 lg:h-12 w-auto object-contain">
                            <span class="text-[9px] text-gray-400 font-bold uppercase tracking-widest leading-none mt-1.5 block md:hidden lg:block">Fleet Management</span>
                        </div>
                        
                        <!-- Close Button on Mobile (Aligned & Styled exactly same as Dashboard Header) -->
                        <button type="button" onclick="toggleMobileSidebar()" 
                            class="p-2 -mr-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg md:!hidden flex items-center justify-center shrink-0 transition-colors focus:outline-none">
                            <i data-lucide="menu" class="w-6 h-6"></i>
                        </button>
                    </div>

                    <!-- Navigation -->
                    <nav class="flex-1 p-2 lg:p-4 space-y-1 overflow-y-auto overflow-x-hidden w-full">
                        @if(auth()->user()->role === 'super_admin')
                        <a href="{{ route('super-admin.index') }}"
                            class="sidebar-item flex items-center justify-start md:justify-center lg:justify-start gap-2.5 px-4 md:px-0 lg:px-4 py-1.5 md:py-2 rounded-lg font-semibold {{ request()->routeIs('super-admin.*') ? 'bg-yellow-100 text-yellow-800' : 'text-yellow-700 hover:bg-yellow-50 hover:text-yellow-800' }}">
                            <i data-lucide="crown" class="w-5 md:w-5 lg:w-4 h-5 md:h-5 lg:h-4"></i>
                            <span class="text-sm block md:hidden lg:block">Owner Panel</span>
                        </a>
                        <hr class="my-2 border-gray-100 block md:hidden lg:block">
                        @endif

                        @if(auth()->user()->hasAccessTo('dashboard'))
                        <a href="{{ route('dashboard') }}"
                            class="sidebar-item flex items-center justify-start md:justify-center lg:justify-start gap-2.5 px-4 md:px-0 lg:px-4 py-1.5 md:py-2 rounded-lg text-gray-700 hover:bg-yellow-50 hover:text-yellow-700 {{ request()->routeIs('dashboard') ? 'bg-yellow-50 text-yellow-700 font-semibold' : '' }}">
                            <i data-lucide="layout-dashboard" class="w-5 md:w-5 lg:w-4 h-5 md:h-5 lg:h-4"></i>
                            <span class="text-sm block md:hidden lg:block">Dashboard</span>
                        </a>
                        @endif

                        @if(auth()->user()->hasAccessTo('units.*'))
                        <a href="{{ route('units.index') }}"
                            class="sidebar-item flex items-center justify-start md:justify-center lg:justify-start gap-2.5 px-4 md:px-0 lg:px-4 py-1.5 md:py-2 rounded-lg text-gray-700 hover:bg-yellow-50 hover:text-yellow-700 {{ request()->routeIs('units.*') ? 'bg-yellow-50 text-yellow-700 font-semibold' : '' }}">
                            <i data-lucide="car" class="w-5 md:w-5 lg:w-4 h-5 md:h-5 lg:h-4"></i>
                            <span class="text-sm block md:hidden lg:block">Unit Management</span>
                        </a>
                        @endif

                        @if(auth()->user()->hasAccessTo('driver-management.*'))
                        <a href="{{ route('driver-management.index') }}"
                            class="sidebar-item flex items-center justify-start md:justify-center lg:justify-start gap-2.5 px-4 md:px-0 lg:px-4 py-1.5 md:py-2 rounded-lg text-gray-700 hover:bg-yellow-50 hover:text-yellow-700 {{ request()->routeIs('driver-management.*') ? 'bg-yellow-50 text-yellow-700 font-semibold' : '' }}">
                            <i data-lucide="users" class="w-5 md:w-5 lg:w-4 h-5 md:h-5 lg:h-4"></i>
                            <span class="text-sm block md:hidden lg:block">Driver Management</span>
                        </a>
                        @endif

                        @if(auth()->user()->hasAccessTo('live-tracking.*'))
                        <a href="{{ route('live-tracking.index') }}"
                            class="sidebar-item flex items-center justify-start md:justify-center lg:justify-start gap-2.5 px-4 md:px-0 lg:px-4 py-1.5 md:py-2 rounded-lg text-gray-700 hover:bg-yellow-50 hover:text-yellow-700 {{ request()->routeIs('live-tracking.*') ? 'bg-yellow-50 text-yellow-700 font-semibold' : '' }}">
                            <i data-lucide="map-pin" class="w-5 md:w-5 lg:w-4 h-5 md:h-5 lg:h-4"></i>
                            <span class="text-sm block md:hidden lg:block">Live Tracking</span>
                        </a>
                        @endif

                        @if(auth()->user()->hasAccessTo('decision-management.*'))
                        <a href="{{ route('decision-management.index') }}"
                            class="sidebar-item flex items-center justify-start md:justify-center lg:justify-start gap-2.5 px-4 md:px-0 lg:px-4 py-1.5 md:py-2 rounded-lg text-gray-700 hover:bg-yellow-50 hover:text-yellow-700 {{ request()->routeIs('decision-management.*') ? 'bg-yellow-50 text-yellow-700 font-semibold' : '' }}">
                            <i data-lucide="file-text" class="w-5 md:w-5 lg:w-4 h-5 md:h-5 lg:h-4"></i>
                            <span class="text-sm block md:hidden lg:block">Franchise</span>
                        </a>
                        @endif

                        @if(auth()->user()->hasAccessTo('boundaries.*'))
                        <a href="{{ route('boundaries.index') }}"
                            class="sidebar-item flex items-center justify-start md:justify-center lg:justify-start gap-2.5 px-4 md:px-0 lg:px-4 py-1.5 md:py-2 rounded-lg text-gray-700 hover:bg-yellow-50 hover:text-yellow-700 {{ request()->routeIs('boundaries.*') ? 'bg-yellow-50 text-yellow-700 font-semibold' : '' }}">
                            <i data-lucide="wallet" class="w-5 md:w-5 lg:w-4 h-5 md:h-5 lg:h-4"></i>
                            <span class="text-sm block md:hidden lg:block">Boundaries</span>
                        </a>
                        @endif

                        @if(auth()->user()->hasAccessTo('maintenance.*'))
                        <a href="{{ route('maintenance.index') }}"
                            class="sidebar-item flex items-center justify-start md:justify-center lg:justify-start gap-2.5 px-4 md:px-0 lg:px-4 py-1.5 md:py-2 rounded-lg text-gray-700 hover:bg-yellow-50 hover:text-yellow-700 {{ request()->routeIs('maintenance.*') ? 'bg-yellow-50 text-yellow-700 font-semibold' : '' }}">
                            <i data-lucide="wrench" class="w-5 md:w-5 lg:w-4 h-5 md:h-5 lg:h-4"></i>
                            <span class="text-sm block md:hidden lg:block">Maintenance</span>
                        </a>
                        @endif

                        @if(auth()->user()->hasAccessTo('coding.*'))
                        <a href="{{ route('coding.index') }}"
                            class="sidebar-item flex items-center justify-start md:justify-center lg:justify-start gap-2.5 px-4 md:px-0 lg:px-4 py-1.5 md:py-2 rounded-lg text-gray-700 hover:bg-yellow-50 hover:text-yellow-700 {{ request()->routeIs('coding.*') ? 'bg-yellow-50 text-yellow-700 font-semibold' : '' }}">
                            <i data-lucide="calendar" class="w-5 md:w-5 lg:w-4 h-5 md:h-5 lg:h-4"></i>
                            <span class="text-sm block md:hidden lg:block">Coding Management</span>
                        </a>
                        @endif

                        @if(auth()->user()->hasAccessTo('driver-behavior.*'))
                        <a href="{{ route('driver-behavior.index') }}"
                            class="sidebar-item flex items-center justify-start md:justify-center lg:justify-start gap-2.5 px-4 md:px-0 lg:px-4 py-1.5 md:py-2 rounded-lg text-gray-700 hover:bg-yellow-50 hover:text-yellow-700 {{ request()->routeIs('driver-behavior.*') ? 'bg-yellow-50 text-yellow-700 font-semibold' : '' }}">
                            <i data-lucide="alert-triangle" class="w-5 md:w-5 lg:w-4 h-5 md:h-5 lg:h-4"></i>
                            <span class="text-sm block md:hidden lg:block">Driver Behavior</span>
                        </a>
                        @endif

                        @if(auth()->user()->hasAccessTo('office-expenses.*'))
                        <a href="{{ route('office-expenses.index') }}"
                            class="sidebar-item flex items-center justify-start md:justify-center lg:justify-start gap-2.5 px-4 md:px-0 lg:px-4 py-1.5 md:py-2 rounded-lg text-gray-700 hover:bg-yellow-50 hover:text-yellow-700 {{ request()->routeIs('office-expenses.*') ? 'bg-yellow-50 text-yellow-700 font-semibold' : '' }}">
                            <i data-lucide="philippine-peso" class="w-5 md:w-5 lg:w-4 h-5 md:h-5 lg:h-4"></i>
                            <span class="text-sm block md:hidden lg:block">Office Expenses</span>
                        </a>
                        @endif

                        @if(auth()->user()->hasAccessTo('salary.*'))
                        <a href="{{ route('salary.index') }}"
                            class="sidebar-item flex items-center justify-start md:justify-center lg:justify-start gap-2.5 px-4 md:px-0 lg:px-4 py-1.5 md:py-2 rounded-lg text-gray-700 hover:bg-yellow-50 hover:text-yellow-700 {{ request()->routeIs('salary.*') ? 'bg-yellow-50 text-yellow-700 font-semibold' : '' }}">
                            <i data-lucide="calculator" class="w-5 md:w-5 lg:w-4 h-5 md:h-5 lg:h-4"></i>
                            <span class="text-sm block md:hidden lg:block">Salary Management</span>
                        </a>
                        @endif

                        @if(auth()->user()->hasAccessTo('analytics.*'))
                        <a href="{{ route('analytics.index') }}"
                            class="sidebar-item flex items-center justify-start md:justify-center lg:justify-start gap-2.5 px-4 md:px-0 lg:px-4 py-1.5 md:py-2 rounded-lg text-gray-700 hover:bg-yellow-50 hover:text-yellow-700 {{ request()->routeIs('analytics.*') ? 'bg-yellow-50 text-yellow-700 font-semibold' : '' }}">
                            <i data-lucide="bar-chart" class="w-4 md:w-5 lg:w-4 h-4 md:h-5 lg:h-4"></i>
                            <span class="text-sm block md:hidden lg:block">Analytics</span>
                        </a>
                        @endif

                        @if(auth()->user()->hasAccessTo('activity-logs.*'))
                        <a href="{{ route('activity-logs.index') }}"
                            class="sidebar-item flex items-center justify-start md:justify-center lg:justify-start gap-2.5 px-4 md:px-0 lg:px-4 py-1.5 md:py-2 rounded-lg text-gray-700 hover:bg-yellow-50 hover:text-yellow-700 {{ request()->routeIs('activity-logs.*') ? 'bg-yellow-50 text-yellow-700 font-semibold' : '' }}">
                            <i data-lucide="history" class="w-5 md:w-5 lg:w-4 h-5 md:h-5 lg:h-4"></i>
                            <span class="text-sm block md:hidden lg:block">History Logs</span>
                        </a>
                        @endif

                        @if(auth()->user()->hasAccessTo('unit-profitability.*'))
                        <a href="{{ route('unit-profitability.index') }}"
                            class="sidebar-item flex items-center justify-start md:justify-center lg:justify-start gap-2.5 px-4 md:px-0 lg:px-4 py-1.5 md:py-2 rounded-lg text-gray-700 hover:bg-yellow-50 hover:text-yellow-700 {{ request()->routeIs('unit-profitability.*') ? 'bg-yellow-50 text-yellow-700 font-semibold' : '' }}">
                            <i data-lucide="trending-up" class="w-5 md:w-5 lg:w-4 h-5 md:h-5 lg:h-4"></i>
                            <span class="text-sm block md:hidden lg:block">Unit Profitability</span>
                        </a>
                        @endif

                        @if(auth()->user()->hasAccessTo('staff.*'))
                        <a href="{{ route('staff.index') }}"
                            class="sidebar-item flex items-center justify-start md:justify-center lg:justify-start gap-2.5 px-4 md:px-0 lg:px-4 py-1.5 md:py-2 rounded-lg text-gray-700 hover:bg-yellow-50 hover:text-yellow-700 {{ request()->routeIs('staff.*') ? 'bg-yellow-50 text-yellow-700 font-semibold' : '' }}">
                            <i data-lucide="user-cog" class="w-5 md:w-5 lg:w-4 h-5 md:h-5 lg:h-4"></i>
                            <span class="text-sm block md:hidden lg:block">Staff Records</span>
                        </a>
                        @endif

                        <hr class="my-2 border-gray-100 block md:hidden lg:block">

                        @if(auth()->user()->hasAccessTo('support.*'))
                        <a href="{{ route('support.index') }}"
                            class="sidebar-item flex items-center justify-start md:justify-center lg:justify-start gap-2.5 px-4 md:px-0 lg:px-4 py-1.5 md:py-2 rounded-lg text-gray-700 hover:bg-yellow-50 hover:text-yellow-700 {{ request()->routeIs('support.*') ? 'bg-yellow-50 text-yellow-700 font-semibold' : '' }}">
                            <i data-lucide="message-square" class="w-5 md:w-5 lg:w-4 h-5 md:h-5 lg:h-4"></i>
                            <span class="text-sm block md:hidden lg:block">Support Center</span>
                        </a>
                        @endif

                        @if(auth()->user()->hasAccessTo('archive.*'))
                        <a href="{{ route('archive.index') }}"
                            class="sidebar-item flex items-center justify-start md:justify-center lg:justify-start gap-2.5 px-4 md:px-0 lg:px-4 py-1.5 md:py-2 rounded-lg text-gray-700 hover:bg-red-50 hover:text-red-700 {{ request()->routeIs('archive.*') ? 'bg-red-50 text-red-700 font-semibold' : '' }}">
                            <i data-lucide="archive" class="w-5 md:w-5 lg:w-4 h-5 md:h-5 lg:h-4"></i>
                            <span class="text-sm block md:hidden lg:block">Archive</span>
                        </a>
                        @endif
                    </nav>

                    <!-- User Menu -->
                    <div class="p-2 lg:p-4 border-t bg-white relative z-50 flex-shrink-0 w-full">
                        <a href="{{ route('my-account') }}" 
                           class="flex items-center justify-start md:justify-center lg:justify-start gap-3 mb-3 p-1 lg:p-2 rounded-lg hover:bg-gray-50 transition-colors group w-full">
                            <div
                                class="w-8 h-8 lg:w-10 lg:h-10 bg-yellow-600 rounded-full flex items-center justify-center text-white font-semibold group-hover:bg-yellow-700 transition-colors overflow-hidden flex-shrink-0 border border-gray-100">
                                @if(auth()->user()->profile_image)
                                    @php
                                        $imagePath = str_replace('resources/', '', auth()->user()->profile_image);
                                        $isIcon = str_contains($imagePath, 'image/') && !str_contains($imagePath, 'storage/');
                                    @endphp
                                    @if($isIcon)
                                        <img src="{{ asset($imagePath) }}" alt="Profile" class="w-full h-full object-cover">
                                    @else
                                        <img src="{{ asset('storage/' . auth()->user()->profile_image) }}" alt="Profile" class="w-full h-full object-cover">
                                    @endif
                                @else
                                    {{ strtoupper(substr(auth()->user()->full_name ?? 'U', 0, 1)) }}
                                @endif
                            </div>
                            <div class="block md:hidden lg:block min-w-0 flex-1">
                                <h4 class="text-sm font-semibold text-gray-900 truncate">{{ auth()->user()->first_name }} {{ auth()->user()->last_name }}</h4>
                                <p class="text-xs text-gray-500 truncate">{{ auth()->user()->role === 'super_admin' ? 'Owner' : ucfirst(auth()->user()->role ?? 'user') }}</p>
                            </div>
                            <i data-lucide="chevron-right" class="w-4 h-4 text-gray-400 group-hover:text-yellow-600 transition-colors hidden lg:block"></i>
                        </a>
                        
                        <!-- Logout Form -->
                        <form id="logout-form" action="{{ route('logout') }}" method="GET" class="hidden"></form>
                        
                        <button type="button"
                            onclick="if(confirm('Are you sure you want to logout?')) { document.getElementById('logout-form').submit(); }"
                            class="flex items-center justify-start md:justify-center lg:justify-start gap-2 px-3 md:px-1 lg:px-3 py-2 text-sm text-red-600 hover:bg-red-50 rounded-lg w-full transition-colors">
                            <i data-lucide="log-out" class="w-4 h-4"></i>
                            <span class="block md:hidden lg:block font-semibold">Logout</span>
                        </button>
                    </div>
                </div>
            </aside>

            <!-- Main Content -->
            <main id="appMainContent" class="flex-1 flex flex-col overflow-hidden">
                <!-- Top Bar -->
                <header class="bg-white shadow-sm border-b px-4 md:px-6 py-2">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <!-- Mobile Menu Trigger -->
                            <button onclick="toggleMobileSidebar()" class="p-2 -ml-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg md:!hidden flex items-center justify-center shrink-0">
                                <i data-lucide="menu" class="w-6 h-6"></i>
                            </button>
                            <div>
                                <h2 class="text-lg md:text-2xl font-black text-gray-900 leading-tight">@yield('page-heading', 'Dashboard')</h2>
                                @hasSection('page-subheading')
                                    <p class="text-[11px] md:text-sm text-gray-500 mt-0.5 md:mt-1">@yield('page-subheading')</p>
                                @endif
                            </div>
                        </div>

                        <div class="flex items-center gap-4">
                            {{-- Consolidating all notifications into the Main Bell --}}

                            <!-- Test App Chime Button -->
                            <button onclick="triggerTestNotificationBroadcast()" id="test-chime-broadcast-btn"
                                class="flex items-center gap-1.5 px-3 py-1.5 bg-gradient-to-r from-yellow-500 to-amber-500 hover:from-yellow-600 hover:to-amber-600 text-white font-extrabold text-[10px] uppercase tracking-wider rounded-xl shadow-md hover:shadow-lg transition-all duration-300 transform active:scale-95 flex-shrink-0">
                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-volume-2 animate-bounce"><path d="M11 5 6 9H2v6h4l5 4V5z"/><path d="M15.54 8.46a5 5 0 0 1 0 7.07"/><path d="M19.07 4.93a10 10 0 0 1 0 14.14"/></svg>
                                <span>📢 Test Chime</span>
                            </button>

                            <!-- Main Notification Bell -->
                            <div class="relative">
                                <button id="notificationBell"
                                    class="relative p-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg">
                                    <i data-lucide="bell" class="w-5 h-5"></i>
                                    <span id="main-nav-notif-badge"
                                            class="absolute -top-1 -right-1 min-w-[18px] h-[18px] px-1 bg-red-500 text-white text-[10px] font-black leading-[18px] rounded-full text-center transition-all duration-300 {{ $headerNotificationCount > 0 ? '' : 'hidden' }}">
                                            {{ $headerNotificationCount }}
                                        </span>
                                </button>

                                <div id="notificationDropdown"
                                    class="hidden fixed md:absolute inset-x-4 md:inset-x-auto md:right-0 mt-2 md:w-80 bg-white shadow-2xl md:shadow-xl rounded-2xl border border-gray-100 z-[9999] overflow-hidden">
                                    <div class="px-4 py-3 border-b bg-gray-50/50 flex items-center justify-between">
                                        <div class="flex flex-col">
                                            <span class="text-sm font-black text-gray-900 tracking-tight">Notifications</span>
                                            <span id="notif-dropdown-subtitle" class="text-[9px] font-bold text-gray-400 uppercase tracking-widest">{{ $headerNotificationCount }} item(s)</span>
                                        </div>
                                        @if($headerNotificationCount > 0)
                                            <button onclick="markAllAsRead()" class="text-[10px] font-bold text-yellow-600 hover:text-yellow-700 hover:underline transition-all">
                                                Mark All Read
                                            </button>
                                        @endif
                                    </div>

                                    {{-- Filter Tabs --}}
                                    <div class="flex border-b bg-white">
                                        <button onclick="filterNotifs('system')" id="btn-filter-system" class="flex-1 py-2.5 text-[11px] font-bold uppercase tracking-wider text-yellow-600 border-b-2 border-yellow-500 transition-all">
                                            System
                                            <span id="badge-filter-system" class="bg-red-500 text-white text-[9px] px-1.5 py-0.5 rounded-full ml-1 {{ $systemNotifCount > 0 ? '' : 'hidden' }}">{{ $systemNotifCount }}</span>
                                        </button>
                                        <button onclick="filterNotifs('low_stock')" id="btn-filter-parts" class="flex-1 py-2.5 text-[11px] font-bold uppercase tracking-wider text-gray-400 hover:text-gray-600 transition-all flex items-center justify-center gap-1.5">
                                            Parts Stock
                                            <span id="badge-filter-parts" class="bg-orange-500 text-white text-[9px] px-1.5 py-0.5 rounded-full {{ $stockNotifCount > 0 ? '' : 'hidden' }}">{{ $stockNotifCount }}</span>
                                        </button>
                                    </div>

                                    <div class="max-h-80 overflow-y-auto" id="notificationList">
                                        @if(empty($headerNotifications))
                                            <div class="px-4 py-4 text-sm text-gray-500 text-center">No notifications.</div>
                                        @else
                                            @foreach($headerNotifications as $n)
                                                @php 
                                                    $notifId = $n['id'] ?? md5($n['title'] . ($n['message'] ?? '')); 
                                                    $isHidden = ($n['type'] === 'low_stock');
                                                @endphp
                                                <div class="notification-item px-4 py-3 border-b last:border-b-0 hover:bg-gray-50 flex items-start gap-2 transition-all unread-notif {{ $isHidden ? 'hidden' : '' }}"
                                                     id="notif-{{ $notifId }}"
                                                     data-type="{{ $n['type'] }}" 
                                                     data-notif-id="{{ $notifId }}"
                                                     style="background-color: #f0f9ff;">
                                                    <a href="{{ $n['url'] ?? '#' }}" class="flex-1 flex gap-3 min-w-0" onclick="markAsRead('{{ $notifId }}')">

                                                        <div class="mt-0.5 flex-shrink-0">
                                                            @if($n['type'] === 'case_expiry')
                                                                <i data-lucide="file-warning" class="w-4 h-4 text-yellow-600"></i>
                                                            @elseif($n['type'] === 'coding_today')
                                                                <i data-lucide="car-front" class="w-4 h-4 text-blue-600"></i>
                                                            @elseif($n['type'] === 'violation_alert')
                                                                <i data-lucide="shield-alert" class="w-4 h-4 text-red-600"></i>
                                                            @elseif($n['type'] === 'low_stock')
                                                                <i data-lucide="package-search" class="w-4 h-4 text-orange-500"></i>
                                                            @elseif($n['type'] === 'license_expiry')
                                                                <i data-lucide="id-card" class="w-4 h-4 text-rose-500"></i>
                                                            @elseif($n['type'] === 'odo_maint_due')
                                                                <i data-lucide="settings-2" class="w-4 h-4 text-orange-600"></i>
                                                            @else
                                                                <i data-lucide="alert-circle" class="w-4 h-4 text-red-600"></i>
                                                            @endif
                                                        </div>
                                                        <div class="flex-1 min-w-0">
                                                            <p class="text-xs font-semibold text-gray-800 truncate">
                                                                {{ $n['title'] }}</p>
                                                            <p class="text-xs text-gray-600 mt-0.5 line-clamp-2">{{ $n['message'] }}</p>
                                                            @if(isset($n['time']))
                                                                <p class="text-[10px] text-gray-400 mt-1 font-medium">{{ $n['time'] }}</p>
                                                            @endif
                                                        </div>
                                                    </a>
                                                    <button type="button"
                                                        class="ml-1 text-gray-400 hover:text-gray-600 flex-shrink-0"
                                                        onclick="dismissNotification(this);">
                                                        <span class="sr-only">Dismiss</span>
                                                        <i data-lucide="x" class="w-3 h-3"></i>
                                                    </button>
                                                </div>
                                            @endforeach
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <!-- Date/Time -->
                            <div class="text-right hidden md:block">
                                <p id="header-date" class="text-[13px] font-medium text-gray-900">{{ date('l, F j, Y') }}</p>
                                <p id="header-time" class="text-[11px] text-gray-500 transition-all duration-300">{{ date('h:i A') }}</p>
                            </div>
                        </div>
                    </div>
                </header>

                <!-- Page Content -->
                <div id="appContentArea" class="flex-1 overflow-y-auto @yield('main-padding', 'p-4')">
                    {{-- Flash Messages --}}
                    @foreach(['success', 'error', 'warning', 'info'] as $type)
                        @if(session($type))
                            <div class="alert-slide mb-4 p-4 rounded-lg border
                                    @if($type === 'success') bg-green-50 border-green-200 text-green-800
                                    @elseif($type === 'error') bg-red-50 border-red-200 text-red-800
                                    @elseif($type === 'warning') bg-yellow-50 border-yellow-200 text-yellow-800
                                    @else bg-blue-50 border-blue-200 text-blue-800
                                    @endif">
                                <div class="flex items-center gap-2">
                                    <i data-lucide="@if($type === 'success') check-circle @elseif($type === 'error') x-circle @elseif($type === 'warning') alert-triangle @else info @endif"
                                        class="w-5 h-5"></i>
                                    <span>{{ session($type) }}</span>
                                </div>
                            </div>
                        @endif
                    @endforeach

                    {{-- Validation Errors --}}
                    @if($errors->any())
                        <div class="alert-slide mb-4 p-4 rounded-lg border bg-red-50 border-red-200 text-red-800">
                            <div class="flex items-center gap-2 mb-2">
                                <i data-lucide="x-circle" class="w-5 h-5"></i>
                                <span class="font-semibold">Please fix the following errors:</span>
                            </div>
                            <ul class="list-disc list-inside text-sm">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @yield('content')
                </div>
            </main>
        </div>

        {{-- Global Archive Deletion Security Modal --}}
        <div id="globalArchiveSecurityModal" class="fixed inset-0 z-[9999] hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" onclick="closeGlobalArchiveSecurityModal()"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full p-6 border border-red-100">
                    <div class="text-center">
                        <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-red-50 border-4 border-red-100 mb-4">
                            <i data-lucide="shield-alert" class="h-8 w-8 text-red-600"></i>
                        </div>
                        <h3 class="text-xl font-black text-red-900 mb-2">Security Verification</h3>
                        <p class="text-sm text-gray-500 mb-6">This action is irreversible. To permanently delete this record, please enter the **Archive Deletion Password**.</p>
                        
                        <div class="mb-6">
                            <input type="password" id="global-archive-pwd" class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-red-500 focus:border-red-500 text-center text-lg tracking-widest outline-none transition-all" placeholder="••••••">
                        </div>

                        <div class="bg-amber-50 border border-amber-100 rounded-xl p-4 flex gap-3 text-left mb-6">
                            <i data-lucide="alert-triangle" class="h-5 w-5 text-amber-600 flex-shrink-0 mt-0.5"></i>
                            <p class="text-[11px] text-amber-800 font-medium leading-relaxed">
                                Warning: Permanently deleting this item will remove it and all related data from the database forever. This cannot be undone.
                            </p>
                        </div>

                        <div class="flex gap-3">
                            <button type="button" onclick="closeGlobalArchiveSecurityModal()" class="flex-1 px-4 py-3 bg-white border border-gray-200 text-gray-700 font-bold rounded-xl hover:bg-gray-50 transition-all">Cancel</button>
                            <button type="button" id="global-confirm-archive-delete" class="flex-1 px-4 py-3 bg-red-600 text-white font-bold rounded-xl hover:bg-red-700 shadow-lg shadow-red-200 transition-all">Confirm Delete</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script>
            let pendingDeleteForm = null;
            let pendingArchivePwdResolve = null;

            function closeGlobalArchiveSecurityModal() {
                document.getElementById('globalArchiveSecurityModal').classList.add('hidden');
                document.getElementById('global-archive-pwd').value = '';
                pendingDeleteForm = null;
                pendingArchivePwdResolve = null;
            }

            // Allow JS-driven destructive actions (fetch/AJAX) to reuse this modal.
            // Returns the password string, or null if cancelled.
            window.promptArchiveDeletionPassword = function () {
                return new Promise((resolve) => {
                    pendingArchivePwdResolve = resolve;
                    pendingDeleteForm = null; // ensure we are not in form-submit mode
                    document.getElementById('globalArchiveSecurityModal').classList.remove('hidden');
                    if (window.lucide) window.lucide.createIcons();
                    setTimeout(() => document.getElementById('global-archive-pwd')?.focus(), 100);
                });
            };

            document.addEventListener('submit', function(e) {
                // Intercept forms that look like permanent deletes (force-delete only)
                const form = e.target;
                const action = form.getAttribute('action') || '';
                const method = form.querySelector('input[name="_method"]')?.value || form.getAttribute('method');

                // ONLY intercept permanent force-delete forms — not regular archive forms
                const isArchiveDelete = action.includes('force-delete') && 
                                        (method?.toUpperCase() === 'DELETE' || method?.toUpperCase() === 'POST');

                // Skip if it's already handled or not an archive delete
                if (!isArchiveDelete || form.dataset.verified === 'true') return;

                e.preventDefault();
                pendingDeleteForm = form;
                
                document.getElementById('globalArchiveSecurityModal').classList.remove('hidden');
                if (window.lucide) window.lucide.createIcons();
                setTimeout(() => document.getElementById('global-archive-pwd').focus(), 100);
            });

            document.getElementById('global-confirm-archive-delete').addEventListener('click', function() {
                const password = document.getElementById('global-archive-pwd').value;
                if (!password) { alert('Please enter the password.'); return; }

                if (pendingDeleteForm) {
                    // Add password as a hidden input to the form
                    let pwdInput = pendingDeleteForm.querySelector('input[name="archive_password"]');
                    if (!pwdInput) {
                        pwdInput = document.createElement('input');
                        pwdInput.type = 'hidden';
                        pwdInput.name = 'archive_password';
                        pendingDeleteForm.appendChild(pwdInput);
                    }
                    pwdInput.value = password;
                    pendingDeleteForm.dataset.verified = 'true';
                    pendingDeleteForm.submit();
                }
                // If opened programmatically (fetch/AJAX), resolve instead of submitting a form.
                if (!pendingDeleteForm && typeof pendingArchivePwdResolve === 'function') {
                    const resolve = pendingArchivePwdResolve;
                    closeGlobalArchiveSecurityModal();
                    resolve(password);
                    return;
                }
                closeGlobalArchiveSecurityModal();
            });

            // Toggle Mobile Sidebar
            window.toggleMobileSidebar = function() {
                const sidebar = document.getElementById('appSidebar');
                const backdrop = document.getElementById('sidebarBackdrop');
                if (sidebar && backdrop) {
                    sidebar.classList.toggle('show');
                    backdrop.classList.toggle('show');
                }
            };
        </script>

    @else
        <!-- Login/Signup Layout -->
        <div class="min-h-screen bg-gradient-to-br from-yellow-50 to-orange-50 flex items-center justify-center p-4">
            <div class="w-full max-w-md">
                @yield('content')
            </div>
        </div>
    @endauth

    <!-- Initialize Lucide icons (page content + bfcache restore) -->
    <script>
        lucide.createIcons();
        window.addEventListener('pageshow', function(e) {
            if (e.persisted) { lucide.createIcons(); }
        });
    </script>

    <!-- Common JavaScript -->
    <script>
        // makeRequest — global AJAX helper used across all pages
        async function makeRequest(url, options = {}) {
            try {
                const response = await fetch(url, {
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        ...options.headers
                    },
                    ...options
                });
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return await response.json();
            } catch (error) {
                console.error('Request failed:', error);
                throw error;
            }
        }

        // Header clock — updates every second
        function updateHeaderClock() {
            const now = new Date();
            const dateEl = document.getElementById('header-date');
            const timeEl = document.getElementById('header-time');
            if (dateEl && timeEl) {
                const dateOptions = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
                dateEl.textContent = now.toLocaleDateString('en-US', dateOptions);
                const timeOptions = { hour: '2-digit', minute: '2-digit', hour12: true };
                timeEl.textContent = now.toLocaleTimeString('en-US', timeOptions);
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            // Globally inject Laravel User ID for native android background services
            window.LaravelUserId = "{{ Auth::id() }}";

            // Re-initialize Lucide icons
            if (window.lucide && window.lucide.createIcons) {
                window.lucide.createIcons();
            }
            // Start header clock
            updateHeaderClock();
            setInterval(updateHeaderClock, 1000);

            // Diagnostic reporter for remote mobile debugging
            async function reportDiag(message, data = {}) {
                try {
                    await fetch('/api/diagnose-capacitor', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        },
                        body: JSON.stringify({ message: message, data: data, user_id: "{{ Auth::id() }}" })
                    });
                } catch (e) {
                    console.error('Diag log failed:', e);
                }
            }

            // Capacitor Native Push Notification Bridge for Hybrid App with Retry Logic
            function tryInitCapacitorPush(retries = 0) {
                const hasCapacitor = typeof window.Capacitor !== 'undefined';
                const hasPlugins = hasCapacitor && !!window.Capacitor.Plugins;
                const hasPush = hasPlugins && !!window.Capacitor.Plugins.PushNotifications;
                
                if (retries === 0 || retries === 5 || retries === 10 || retries === 14) {
                    reportDiag("tryInitCapacitorPush status check", { 
                        retries: retries, 
                        hasCapacitor: hasCapacitor, 
                        hasPlugins: hasPlugins, 
                        hasPush: hasPush,
                        href: window.location.href,
                        user_id: "{{ Auth::id() }}"
                    });
                }

                if (hasPush) {
                    console.log('Capacitor Native Platform and PushNotifications plugin detected! Initializing bridge...');
                    reportDiag("Capacitor Push found, initializing bridge", { user_id: "{{ Auth::id() }}" });
                    const PushNotifications = window.Capacitor.Plugins.PushNotifications;
                    const currentUserId = "{{ Auth::id() }}";

                    async function syncTokenWithBackend(token) {
                        try {
                            reportDiag("Syncing token with backend starting...", { token: token });
                            const res = await makeRequest('/web-notifications/save-token', {
                                method: 'POST',
                                body: JSON.stringify({ token: token })
                            });
                            reportDiag("Sync token backend response", { response: res });
                            if (res && res.success) {
                                console.log('FCM Device Token successfully synced with backend!');
                                localStorage.setItem('fcm_token_synced', 'true');
                                if (currentUserId) {
                                    localStorage.setItem('fcm_token_user_id', currentUserId);
                                }
                                window.dispatchEvent(new CustomEvent('fcm_token_synced_event', { detail: { token: token } }));
                            }
                        } catch (e) {
                            console.error('Failed to sync hybrid FCM token with backend:', e);
                            reportDiag("Sync token backend error", { error: e.message });
                        }
                    }
                    
                    async function initNativePush() {
                        try {
                            // Check if we have a cached token in localStorage that needs syncing
                            const savedToken = localStorage.getItem('fcm_token');
                            reportDiag("initNativePush starting", { cached_token: savedToken, currentUserId: currentUserId });
                            
                            if (savedToken && currentUserId) {
                                const lastSyncedUser = localStorage.getItem('fcm_token_user_id');
                                const isSynced = localStorage.getItem('fcm_token_synced') === 'true';
                                reportDiag("Checking cached token sync requirements", { lastSyncedUser: lastSyncedUser, isSynced: isSynced });
                                if (!isSynced || lastSyncedUser !== currentUserId) {
                                    console.log('Cached FCM token found and needs sync. Syncing now...');
                                    await syncTokenWithBackend(savedToken);
                                }
                            }

                            let permStatus = await PushNotifications.checkPermissions();
                            reportDiag("Initial permission status", { permStatus: permStatus });
                            
                            if (permStatus.receive === 'prompt') {
                                reportDiag("Requesting push permissions...");
                                permStatus = await PushNotifications.requestPermissions();
                                reportDiag("After request permission status", { permStatus: permStatus });
                            }
                            if (permStatus.receive === 'granted') {
                                // Check if MainActivity injected the token early via continuous window injection
                                const checkNativeBridge = async () => {
                                    if (window.AndroidNativeError) {
                                        reportDiag("Native Token Error in Bridge", { error: window.AndroidNativeError });
                                        return true; // Stop checking
                                    }
                                    if (window.AndroidNativeToken && window.AndroidNativeToken !== 'null') {
                                        const earlyNativeToken = window.AndroidNativeToken;
                                        console.log('Hybrid FCM Device Token pulled directly from AndroidNativeToken:', earlyNativeToken);
                                        reportDiag("Native Token via AndroidNativeToken", { token: earlyNativeToken });
                                        const lastToken = localStorage.getItem('fcm_token');
                                        if (lastToken !== earlyNativeToken) {
                                            localStorage.setItem('fcm_token', earlyNativeToken);
                                            localStorage.setItem('fcm_token_synced', 'false');
                                        }
                                        await syncTokenWithBackend(earlyNativeToken);
                                        return true;
                                    }
                                    return false;
                                };

                                const bridgeSuccess = await checkNativeBridge();
                                if (!bridgeSuccess) {
                                    // Retry checking the injected variable forever until found
                                    let attempts = 0;
                                    const interval = setInterval(async () => {
                                        attempts++;
                                        const success = await checkNativeBridge();
                                        if (success || attempts > 30) { // Stop after 30 seconds
                                            clearInterval(interval);
                                            if (!success) reportDiag("Bridge timeout", { reason: "Neither token nor error injected" });
                                        }
                                    }, 1000);
                                }

                                // Custom listener for our bypassed Native Token Injector in MainActivity.java!
                                window.addEventListener('native_fcm_token_ready', async (e) => {
                                    const tokenVal = e.detail.token;
                                    console.log('Hybrid FCM Device Token natively injected:', tokenVal);
                                    reportDiag("Native injection event fired", { token: tokenVal });
                                    const lastToken = localStorage.getItem('fcm_token');
                                    if (lastToken !== tokenVal) {
                                        localStorage.setItem('fcm_token', tokenVal);
                                        localStorage.setItem('fcm_token_synced', 'false');
                                    }
                                    await syncTokenWithBackend(tokenVal);
                                });

                                // Capacitor's listeners (may drop events on server.url)
                                await PushNotifications.addListener('registration', async (token) => {
                                    console.log('Capacitor Listener: Hybrid FCM Device Token retrieved:', token.value);
                                    reportDiag("Capacitor registration event fired", { token: token.value });
                                    const lastToken = localStorage.getItem('fcm_token');
                                    if (lastToken !== token.value) {
                                        localStorage.setItem('fcm_token', token.value);
                                        localStorage.setItem('fcm_token_synced', 'false');
                                    }
                                    await syncTokenWithBackend(token.value);
                                });
                                
                                await PushNotifications.addListener('registrationError', (error) => {
                                    console.error('Hybrid FCM Registration Error:', error);
                                    reportDiag("Native registrationError event fired", { error: error });
                                });

                                // Trigger the native registration process after listeners are ready
                                reportDiag("Calling PushNotifications.register()...");
                                await PushNotifications.register();
                                reportDiag("PushNotifications.register() completed!");
                            } else {
                                reportDiag("Push permissions not granted", { final_status: permStatus.receive });
                            }
                        } catch (err) {
                            console.error('Error in hybrid native push initialization:', err);
                            reportDiag("initNativePush fatal error catch", { error: err.message });
                        }
                    }
                    
                    initNativePush();
                } else if (retries < 15) {
                    console.log(`Capacitor plugins not fully loaded yet (Attempt ${retries + 1}/15)... Retrying in 150ms...`);
                    setTimeout(() => tryInitCapacitorPush(retries + 1), 150);
                } else {
                    console.log('Capacitor or PushNotifications plugin not found. Running in browser or native plugins disabled.');
                    reportDiag("tryInitCapacitorPush timed out - Capacitor not detected");
                }
            }

            tryInitCapacitorPush();

            // Restore Read States
            let readNotifs = JSON.parse(localStorage.getItem('read_notifs') || '{}');
            
            // Migrate legacy array to object format
            if (Array.isArray(readNotifs)) {
                readNotifs = {};
                localStorage.setItem('read_notifs', JSON.stringify(readNotifs));
            }

            const nowMs = Date.now();
            let needsCleanup = false;

            Object.keys(readNotifs).forEach(id => {
                if (nowMs - readNotifs[id] < 1800000) { // Still within 30 minutes
                    const el = document.getElementById('notif-' + id);
                    if (el) {
                        el.style.backgroundColor = 'transparent';
                        el.classList.remove('unread-notif');
                    }
                } else {
                    delete readNotifs[id]; // Expired, remove it
                    needsCleanup = true;
                }
            });
            
            // Self-heal and cleanup expired cookies
            if (needsCleanup || Object.keys(readNotifs).length > 0) {
                localStorage.setItem('read_notifs', JSON.stringify(readNotifs));
                document.cookie = "read_notifs=" + encodeURIComponent(JSON.stringify(readNotifs)) + "; path=/; max-age=" + (30 * 24 * 60 * 60);
            }

            // Update badge counts after restoring states
            if (typeof updateNotificationCount === 'function') {
                updateNotificationCount();
            }
        });

        function filterNotifs(type) {
            const items = document.querySelectorAll('.notification-item');
            const btnSystem = document.getElementById('btn-filter-system');
            const btnParts = document.getElementById('btn-filter-parts');

            if (type === 'system') {
                items.forEach(i => {
                    if (i.dataset.type !== 'low_stock') i.classList.remove('hidden');
                    else i.classList.add('hidden');
                });
                btnSystem.classList.add('border-b-2', 'border-yellow-500', 'text-yellow-600');
                btnSystem.classList.remove('text-gray-400');
                btnParts.classList.remove('border-b-2', 'border-yellow-500', 'text-yellow-600');
                btnParts.classList.add('text-gray-400');
            } else {
                items.forEach(i => {
                    if (i.dataset.type === type) i.classList.remove('hidden');
                    else i.classList.add('hidden');
                });
                btnParts.classList.add('border-b-2', 'border-yellow-500', 'text-yellow-600');
                btnParts.classList.remove('text-gray-400');
                btnSystem.classList.remove('border-b-2', 'border-yellow-500', 'text-yellow-600');
                btnSystem.classList.add('text-gray-400');
            }
        }

        function markAsRead(id) {
            id = String(id);
            let readNotifs = JSON.parse(localStorage.getItem('read_notifs') || '{}');
            if (Array.isArray(readNotifs)) readNotifs = {};

            readNotifs[id] = Date.now();
            
            // Cleanup expired entries
            const now = Date.now();
            for (const key in readNotifs) {
                if (now - readNotifs[key] >= 1800000) {
                    delete readNotifs[key];
                }
            }

            localStorage.setItem('read_notifs', JSON.stringify(readNotifs));
            // Set cookie for PHP awareness (30 days)
            document.cookie = "read_notifs=" + encodeURIComponent(JSON.stringify(readNotifs)) + "; path=/; max-age=" + (30 * 24 * 60 * 60);
            
            const el = document.getElementById('notif-' + id);
            if (el) {
                el.style.backgroundColor = 'transparent';
                el.classList.remove('unread-notif');
                // Decrement badge count
                if (typeof updateNotificationCount === 'function') {
                    updateNotificationCount();
                }
            }
        }

        function markAllAsRead() {
            const items = document.querySelectorAll('.notification-item');
            let readNotifs = JSON.parse(localStorage.getItem('read_notifs') || '{}');
            if (Array.isArray(readNotifs)) readNotifs = {};
            
            const now = Date.now();
            
            items.forEach(item => {
                const id = String(item.dataset.notifId);
                if (id) {
                    readNotifs[id] = now;
                }
                item.style.backgroundColor = 'transparent';
                item.classList.remove('unread-notif');
            });

            // Cleanup expired entries
            for (const key in readNotifs) {
                if (now - readNotifs[key] >= 1800000) {
                    delete readNotifs[key];
                }
            }
            
            localStorage.setItem('read_notifs', JSON.stringify(readNotifs));
            // Set cookie for PHP awareness (30 days)
            document.cookie = "read_notifs=" + encodeURIComponent(JSON.stringify(readNotifs)) + "; path=/; max-age=" + (30 * 24 * 60 * 60);
            
            // Zero out badge counts
            if (typeof updateNotificationCount === 'function') {
                updateNotificationCount();
            }
        }

        function updateNotificationCount() {
            const items = document.querySelectorAll('.notification-item');
            let systemCount = 0;
            let partsCount = 0;

            items.forEach(item => {
                // An item is unread if it doesn't have the background removed or is still marked unread
                if (item.classList.contains('unread-notif')) {
                    if (item.dataset.type === 'low_stock') partsCount++;
                    else systemCount++;
                }
            });

            const total = systemCount + partsCount;

            // Update Main Bell Badge
            const mainBadge = document.getElementById('main-nav-notif-badge');
            if (mainBadge) {
                mainBadge.textContent = total;
                mainBadge.classList.toggle('hidden', total === 0);
            }

            // Update Dropdown Subtitle
            const subtitle = document.getElementById('notif-dropdown-subtitle');
            if (subtitle) {
                subtitle.textContent = `${total} item(s)`;
            }

            // Update Filter Tab Badges
            const systemBadge = document.getElementById('badge-filter-system');
            if (systemBadge) {
                systemBadge.textContent = systemCount;
                systemBadge.classList.toggle('hidden', systemCount === 0);
            }

            const partsBadge = document.getElementById('badge-filter-parts');
            if (partsBadge) {
                partsBadge.textContent = partsCount;
                partsBadge.classList.toggle('hidden', partsCount === 0);
            }
        }

        // Premium Web Audio API double chime synthesizer (Ding-Dong!)
        // 100% network-independent, CORS-safe, and offline-compatible.
        function playNotificationChime() {
            try {
                const AudioContext = window.AudioContext || window.webkitAudioContext;
                if (!AudioContext) return;
                const ctx = new AudioContext();
                
                // Note 1 (Ding! - D5)
                let osc1 = ctx.createOscillator();
                let gain1 = ctx.createGain();
                osc1.connect(gain1);
                gain1.connect(ctx.destination);
                osc1.frequency.value = 587.33; 
                gain1.gain.setValueAtTime(0.3, ctx.currentTime);
                gain1.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.4);
                osc1.start(ctx.currentTime);
                osc1.stop(ctx.currentTime + 0.4);
                
                // Note 2 (Dong! - A5)
                setTimeout(() => {
                    let osc2 = ctx.createOscillator();
                    let gain2 = ctx.createGain();
                    osc2.connect(gain2);
                    gain2.connect(ctx.destination);
                    osc2.frequency.value = 880.00; 
                    gain2.gain.setValueAtTime(0.3, ctx.currentTime);
                    gain2.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.6);
                    osc2.start(ctx.currentTime);
                    osc2.stop(ctx.currentTime + 0.6);
                }, 120);
            } catch (e) {
                console.error("Failed to play synthesized chime:", e);
            }
        }

        // Stunning Glassmorphism Slide-Down Notification Banner
        // Renders instantly inside the WebView, feeling 100% native.
        function showInAppNotificationBanner(title, message, url) {
            const existing = document.getElementById('in-app-notif-banner');
            if (existing) existing.remove();
            
            const banner = document.createElement('div');
            banner.id = 'in-app-notif-banner';
            // Styling uses a beautiful, sleek, modern design with animations
            banner.className = 'fixed top-4 left-4 right-4 z-[99999] bg-white/95 backdrop-blur-md rounded-2xl border border-yellow-200 shadow-2xl p-4 flex gap-4 transition-all duration-500 transform -translate-y-40 opacity-0 pointer-events-auto cursor-pointer max-w-md mx-auto';
            banner.innerHTML = `
                <div class="flex-shrink-0 w-10 h-10 rounded-full bg-gradient-to-tr from-yellow-400 to-amber-500 flex items-center justify-center text-white shadow-lg shadow-yellow-100 animate-pulse">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-bell"><path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/></svg>
                </div>
                <div class="flex-1 min-w-0">
                    <h4 class="text-[10px] font-black text-yellow-600 tracking-wider uppercase">System Alert</h4>
                    <p class="text-xs font-bold text-gray-900 mt-0.5 truncate">${title}</p>
                    <p class="text-[11px] text-gray-500 mt-0.5 line-clamp-2 leading-normal">${message}</p>
                </div>
                <button type="button" class="flex-shrink-0 text-gray-400 hover:text-gray-600 self-start" onclick="event.stopPropagation(); this.parentElement.remove();">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-x"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                </button>
            `;
            
            banner.onclick = () => {
                if (url && url !== '#') {
                    window.location.href = url;
                } else {
                    banner.classList.add('-translate-y-40', 'opacity-0');
                    setTimeout(() => banner.remove(), 500);
                }
            };
            
            document.body.appendChild(banner);
            
            setTimeout(() => {
                banner.classList.remove('-translate-y-40', 'opacity-0');
            }, 50);
            
            setTimeout(() => {
                if (banner.parentNode) {
                    banner.classList.add('-translate-y-40', 'opacity-0');
                    setTimeout(() => {
                        if (banner.parentNode) banner.remove();
                    }, 500);
                }
            }, 6000);
        }

        // Real-Time Notification Polling & UI Sync (Lightweight background tasks)
        let pollInterval = null;

        function updateNotificationUI(data) {
            // Track new notification IDs to play chime and show in-app banner
            if (data && data.notifications) {
                if (!window.notifiedIds) {
                    let stored = [];
                    try {
                        stored = JSON.parse(localStorage.getItem('notified_ids')) || [];
                    } catch(e) {}
                    if (!Array.isArray(stored)) stored = [];
                    
                    // Initialize with existing notifications to avoid spam on first load
                    data.notifications.forEach(n => {
                        const idStr = String(n.id);
                        if (n.type === 'test_chime_alert') {
                            // CRITICAL: DO NOT suppress test chime broadcasts on first load!
                            // Trigger sound and banner instantly!
                            if (!stored.includes(idStr)) {
                                stored.push(idStr);
                                playNotificationChime();
                                showInAppNotificationBanner(n.title, n.message, n.url);
                            }
                        } else {
                            if (!stored.includes(idStr)) {
                                stored.push(idStr);
                            }
                        }
                    });
                    window.notifiedIds = stored;
                    localStorage.setItem('notified_ids', JSON.stringify(stored));
                } else {
                    data.notifications.forEach(n => {
                        const idStr = String(n.id);
                        if (!window.notifiedIds.includes(idStr)) {
                            window.notifiedIds.push(idStr);
                            localStorage.setItem('notified_ids', JSON.stringify(window.notifiedIds));
                            
                            // Play custom double-tone and display gorgeous banner!
                            playNotificationChime();
                            showInAppNotificationBanner(n.title, n.message, n.url);
                        }
                    });
                }
            }

            const total = data.total;
            const mainBadge = document.getElementById('main-nav-notif-badge');
            if (mainBadge) {
                mainBadge.textContent = total;
                mainBadge.classList.toggle('hidden', total === 0);
            }

            const subtitle = document.getElementById('notif-dropdown-subtitle');
            if (subtitle) {
                subtitle.textContent = `${total} item(s)`;
            }

            const systemBadge = document.getElementById('badge-filter-system');
            if (systemBadge) {
                systemBadge.textContent = data.system_count;
                systemBadge.classList.toggle('hidden', data.system_count === 0);
            }

            const partsBadge = document.getElementById('badge-filter-parts');
            if (partsBadge) {
                partsBadge.textContent = data.parts_count;
                partsBadge.classList.toggle('hidden', data.parts_count === 0);
            }

            const btnParts = document.getElementById('btn-filter-parts');
            const isPartsSelected = btnParts && btnParts.classList.contains('text-yellow-600');

            const listContainer = document.getElementById('notificationList');
            if (listContainer) {
                if (data.notifications.length === 0) {
                    listContainer.innerHTML = '<div class="px-4 py-4 text-sm text-gray-500 text-center">No notifications.</div>';
                } else {
                    let html = '';
                    data.notifications.forEach(n => {
                        const isHidden = (n.type === 'low_stock') ? !isPartsSelected : isPartsSelected;
                        let icon = 'alert-circle';
                        let iconClass = 'text-red-600';
                        
                        if (n.type === 'case_expiry') {
                            icon = 'file-warning';
                            iconClass = 'text-yellow-600';
                        } else if (n.type === 'coding_today') {
                            icon = 'car-front';
                            iconClass = 'text-blue-600';
                        } else if (n.type === 'violation_alert') {
                            icon = 'shield-alert';
                            iconClass = 'text-red-600';
                        } else if (n.type === 'low_stock') {
                            icon = 'package-search';
                            iconClass = 'text-orange-500';
                        } else if (n.type === 'license_expiry') {
                            icon = 'id-card';
                            iconClass = 'text-rose-500';
                        } else if (n.type === 'odo_maint_due') {
                            icon = 'settings-2';
                            iconClass = 'text-orange-600';
                        }
                        
                        html += `
                            <div class="notification-item px-4 py-3 border-b last:border-b-0 hover:bg-gray-50 flex items-start gap-2 transition-all unread-notif ${isHidden ? 'hidden' : ''}"
                                 id="notif-${n.id}"
                                 data-type="${n.type}" 
                                 data-notif-id="${n.id}"
                                 style="background-color: #f0f9ff;">
                                <a href="${n.url || '#'}" class="flex-1 flex gap-3 min-w-0" onclick="markAsRead('${n.id}')">
                                    <div class="mt-0.5 flex-shrink-0">
                                        <i data-lucide="${icon}" class="w-4 h-4 ${iconClass}"></i>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-xs font-semibold text-gray-800 truncate">${n.title}</p>
                                        <p class="text-xs text-gray-600 mt-0.5 line-clamp-2">${n.message}</p>
                                        ${n.time ? `<p class="text-[10px] text-gray-400 mt-1 font-medium">${n.time}</p>` : ''}
                                    </div>
                                </a>
                                <button type="button"
                                    class="ml-1 text-gray-400 hover:text-gray-600 flex-shrink-0"
                                    onclick="dismissNotification(this);">
                                    <span class="sr-only">Dismiss</span>
                                    <i data-lucide="x" class="w-3 h-3"></i>
                                </button>
                            </div>
                        `;
                    });
                    listContainer.innerHTML = html;
                    if (window.lucide) {
                        window.lucide.createIcons();
                    }
                }
            }
        }

        async function pollNotifications() {
            try {
                const res = await makeRequest('/web-notifications/poll');
                if (res && res.success) {
                    updateNotificationUI(res);
                }
            } catch (e) {
                console.error('Notification poll failed:', e);
            }
        }

        function startNotificationPolling() {
            if (pollInterval) clearInterval(pollInterval);
            pollInterval = setInterval(pollNotifications, 6000); // Snappy real-time polling every 6 seconds
        }

        async function triggerTestNotificationBroadcast() {
            const btn = document.getElementById('test-chime-broadcast-btn');
            if (btn) btn.disabled = true;
            try {
                const res = await makeRequest('/web-notifications/trigger-test-chime', { method: 'POST' });
                if (res && res.success) {
                    alert('📢 Chime Broadcast triggered! Check your Oppo phone screen/sound in the next 6 seconds!');
                } else {
                    alert('Failed to trigger broadcast: ' + (res.error || 'Unknown error'));
                }
            } catch (e) {
                console.error(e);
                alert('Broadcast request failed: ' + (e.message || e) + '\n\nTip: Kung may lumang Service Worker cache ang iyong desktop browser, mangyaring pindutin ang CTRL + F5 sa keyboard (o Cmd + Shift + R sa Mac) upang tuluyang ma-clear ang cache, at subukan muli.');
            } finally {
                if (btn) btn.disabled = false;
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            @auth
                startNotificationPolling();
            @endauth
        });
    </script>

    <!-- Structured Data (JSON-LD) -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "Organization",
        "name": "Euro Taxi System",
        "url": "https://www.eurotaxisystem.site",
        "logo": "https://www.eurotaxisystem.site/{{ asset('image/logo.png') }}",
        "description": "Professional taxi fleet management system in the Philippines with real-time tracking, driver management, and comprehensive business solutions.",
        "address": {
            "@type": "PostalAddress",
            "addressCountry": "PH",
            "addressRegion": "Philippines"
        },
        "contactPoint": {
            "@type": "ContactPoint",
            "telephone": "+63-XXX-XXXX-XXXX",
            "contactType": "customer service",
            "availableLanguage": ["English", "Filipino"]
        },
        "sameAs": [
            "https://www.eurotaxisystem.site"
        ]
    }
    </script>

    <!-- Service Worker disabled to prevent stale data caching on dashboard -->
    <script>
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.getRegistrations().then(function(registrations) {
                for(let registration of registrations) {
                    registration.unregister();
                }
            });
        }
        // Initialize all Lucide icons after the entire DOM is parsed to prevent FOUC
        if(window.lucide) {
            window.lucide.createIcons();
        }
        
        // Client-Side Routing System - No Page Reloads
        document.addEventListener('DOMContentLoaded', function() {
            // Ensure Lucide icons are immediately visible
            if(window.lucide) {
                window.lucide.createIcons();
            }
            
            // Cache for loaded pages
            const pageCache = new Map();
            
            // Hover prefetching disabled to prevent database connection exhaustion on shared hosting
            
            // Fetch page content
            async function fetchPage(url, prefetch = false) {
                if (pageCache.has(url)) {
                    return pageCache.get(url);
                }
                
                try {
                    const response = await fetch(url, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'text/html'
                        }
                    });
                    
                    if (!response.ok) throw new Error('Network response was not ok');
                    
                    const html = await response.text();
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    
                    // Extract main content
                    const mainContent = doc.querySelector('#appMainContent');
                    const pageTitle = doc.querySelector('title')?.textContent || '';
                    
                    const pageData = { mainContent, pageTitle, html };
                    pageCache.set(url, pageData);
                    
                    return pageData;
                } catch (error) {
                    console.error('Error fetching page:', error);
                    if (!prefetch) {
                        window.location.href = url; // Fallback to normal navigation
                    }
                }
            }
            
            // Update page content without reload
            async function navigateToPage(url) {
                // Add loading state
                document.body.classList.add('page-transitioning');
                
                try {
                    const pageData = await fetchPage(url);
                    
                    if (pageData.mainContent) {
                        // Update main content
                        const mainContent = document.querySelector('#appMainContent');
                        mainContent.innerHTML = pageData.mainContent.innerHTML;
                        
                        // Update page title
                        if (pageData.pageTitle) {
                            document.title = pageData.pageTitle;
                        }
                        
                        // Update URL without reload
                        history.pushState({}, '', url);
                        
                        // Re-initialize Lucide icons in new content
                        if(window.lucide) {
                            window.lucide.createIcons();
                        }
                        
                        // Re-run any scripts in the new content
                        const scripts = mainContent.querySelectorAll('script');
                        scripts.forEach(script => {
                            const newScript = document.createElement('script');
                            if (script.src) {
                                newScript.src = script.src;
                            } else {
                                newScript.textContent = script.textContent;
                            }
                            document.head.appendChild(newScript);
                        });

                        // Dispatch custom event for child pages to know they are loaded via AJAX
                        document.dispatchEvent(new CustomEvent('page:loaded', { detail: { url: url } }));
                    }
                } catch (error) {
                    console.error('Navigation error:', error);
                    window.location.href = url; // Fallback
                } finally {
                    // Remove loading state
                    setTimeout(() => {
                        document.body.classList.remove('page-transitioning');
                        document.querySelectorAll('.nav-loading').forEach(el => {
                            el.classList.remove('nav-loading');
                        });
                    }, 100);
                }
            }
            
            // Handle sidebar navigation
            document.querySelectorAll('.sidebar-item').forEach(link => {
                link.addEventListener('click', function(e) {
                    const href = this.getAttribute('href');
                    
                    // Skip external links, anchors, and if modifier keys are pressed
                    if (!href || href.startsWith('#') || href.startsWith('http') || e.ctrlKey || e.metaKey || e.shiftKey) {
                        return;
                    }
                    
                    e.preventDefault();
                    
                    // Add loading state
                    this.classList.add('nav-loading');

                    // Smoothly close mobile sidebar if active
                    const sidebar = document.getElementById('appSidebar');
                    const backdrop = document.getElementById('sidebarBackdrop');
                    if (sidebar) sidebar.classList.remove('show');
                    if (backdrop) backdrop.classList.remove('show');
                    
                    // Navigate without page reload
                    navigateToPage(href);
                });
            });
            
            // Handle browser back/forward
            window.addEventListener('popstate', function(e) {
                if (e.state !== null) {
                    navigateToPage(window.location.href);
                }
            });
            
            // Initialize history state
            history.replaceState({}, '', window.location.href);
        });
    </script>
    @stack('scripts')

    <!-- Beautiful Global Web-Based Pull-To-Refresh Loader for Mobile/Android WebView -->
    <div id="globalPullToRefreshIndicator" class="fixed left-0 right-0 flex items-center justify-center pointer-events-none z-[9999] transition-transform duration-100 ease-out" style="top: -60px; transform: translateY(0px); opacity: 0;">
        <div class="bg-white border border-gray-100 rounded-full p-2.5 shadow-xl flex items-center justify-center">
            <!-- SVG Spinning loader -->
            <div id="globalPullToRefreshIcon" class="transition-transform duration-100">
                <svg id="globalPullArrow" class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 13l-7 7-7-7m14-6l-7 7-7-7"></path>
                </svg>
                <svg id="globalPullSpinner" class="w-5 h-5 text-amber-500 animate-spin hidden" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </div>
        </div>
    </div>

    <script>
        (function() {
            // Only enable pull-to-refresh on mobile devices or Capacitor instances
            const isMobileDevice = window.innerWidth <= 1024 || 
                                   navigator.userAgent.includes('Capacitor') || 
                                   navigator.userAgent.includes('Android');
            if (!isMobileDevice) return;

            let startY = 0;
            let pullDistance = 0;
            let isDragging = false;
            let isRefreshing = false;
            const threshold = 90; // Pull down at least 90px to refresh
            const maxPull = 140; // Max visual translation limit

            const indicator = document.getElementById('globalPullToRefreshIndicator');
            const arrow = document.getElementById('globalPullArrow');
            const spinner = document.getElementById('globalPullSpinner');
            const iconContainer = document.getElementById('globalPullToRefreshIcon');

            if (!indicator || !arrow || !spinner) return;

            window.addEventListener('touchstart', function(e) {
                // Only trigger pull if we are at the very top of scrollable container
                if (window.scrollY === 0 && !isRefreshing) {
                    startY = e.touches[0].pageY;
                    isDragging = true;
                    
                    // Prepare indicator initial state
                    indicator.style.opacity = '0';
                    indicator.style.transform = 'translateY(0px)';
                    arrow.classList.remove('hidden');
                    spinner.classList.add('hidden');
                }
            }, { passive: true });

            window.addEventListener('touchmove', function(e) {
                if (!isDragging || isRefreshing) return;

                const currentY = e.touches[0].pageY;
                const diff = currentY - startY;

                if (diff > 0) {
                    // Apply touch friction damping
                    const resistance = 0.35;
                    pullDistance = Math.min(diff * resistance, maxPull);

                    // Prevent default browser refresh gestures
                    if (e.cancelable && pullDistance > 10) {
                        e.preventDefault();
                    }

                    // Animate indicator sliding down
                    indicator.style.opacity = Math.min(pullDistance / 50, 1).toString();
                    indicator.style.transform = `translateY(${pullDistance}px)`;

                    // Rotate the arrow down as they drag
                    const rotation = Math.min((pullDistance / threshold) * 180, 180);
                    iconContainer.style.transform = `rotate(${rotation}deg)`;
                } else {
                    isDragging = false;
                    pullDistance = 0;
                    indicator.style.opacity = '0';
                    indicator.style.transform = 'translateY(0px)';
                }
            }, { passive: false });

            window.addEventListener('touchend', function() {
                if (!isDragging || isRefreshing) return;
                isDragging = false;

                if (pullDistance >= threshold) {
                    isRefreshing = true;
                    pullDistance = threshold;

                    // Lock indicator position
                    indicator.style.transform = `translateY(${pullDistance}px)`;
                    
                    // Switch icons
                    arrow.classList.add('hidden');
                    spinner.classList.remove('hidden');
                    iconContainer.style.transform = 'rotate(0deg)';

                    // Trigger actual reload after 600ms delay for high fidelity feedback
                    setTimeout(function() {
                        window.location.reload();
                    }, 600);
                } else {
                    // Smoothly reset
                    pullDistance = 0;
                    indicator.style.opacity = '0';
                    indicator.style.transform = 'translateY(0px)';
                }
            });
        })();
    </script>
</body>

</html>