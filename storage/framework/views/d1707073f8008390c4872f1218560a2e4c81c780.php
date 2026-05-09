<!DOCTYPE html>
<html lang="en">

<head>
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
    <meta property="og:url" content="<?php echo e(config('app.url', 'https://www.eurotaxisystem.site')); ?>">
    <meta property="og:image" content="<?php echo e(asset('image/logo.png')); ?>">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Euro Taxi System | Taxi Fleet Management">
    <meta name="twitter:description" content="Professional taxi fleet management system in the Philippines">
    
    <!-- CSRF Token -->
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    
    <!-- Base Asset URL -->
    <meta name="asset-url" content="<?php echo e(asset('')); ?>">

    <!-- Capacitor Native Bridge -->
    <script src="/capacitor.js"></script>
    <script src="/capacitor_plugins.js"></script>

    <title><?php echo e(config('app.name', 'Euro Taxi System')); ?></title>

    <!-- Favicon -->
    <link rel="shortcut icon" href="<?php echo e(asset('favicon_euro_transparent.png')); ?>?v=1.6">
    <link rel="icon" type="image/png" href="<?php echo e(asset('favicon_euro_transparent.png')); ?>?v=1.6">
    <link rel="apple-touch-icon" href="<?php echo e(asset('favicon_euro_transparent.png')); ?>?v=1.6">
    <link rel="manifest" href="<?php echo e(asset('manifest.json')); ?>?v=1.7">

    <!-- Critical Assets (Local) -->
    <script src="<?php echo e(asset('assets/tailwind.min.js')); ?>?v=stable_3.4.1"></script>
    <link rel="stylesheet" href="<?php echo e(asset('assets/fontawesome/all.min.css')); ?>?v=stable_6.4.0">
    <link rel="stylesheet" href="<?php echo e(asset('assets/inter/inter.css')); ?>?v=stable_3.19.3">

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
        
        /* Prevent sidebar flicker during navigation */
        #appSidebar {
            transition: none;
            will-change: transform;
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

        /* Responsive Mobile Drawer Styles */
        @media (max-width: 767px) {
            #appSidebar {
                position: fixed !important;
                top: 0;
                bottom: 0;
                height: 100dvh !important;
                max-height: 100dvh !important;
                left: -260px !important;
                width: 260px !important;
                z-index: 100 !important;
                transition: left 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
                display: none;
                overflow-y: auto !important;
            }
            #appSidebar.show {
                left: 0 !important;
                display: flex !important;
            }
            #sidebarBackdrop {
                position: fixed;
                inset: 0;
                background-color: rgba(15, 23, 42, 0.5);
                backdrop-filter: blur(4px);
                z-index: 90;
                display: none;
            }
            #sidebarBackdrop.show {
                display: block !important;
            }
        }
    </style>
    
    <!-- Lucide Icons (Local) -->
    <script src="<?php echo e(asset('assets/lucide.min.js')); ?>"></script>

    <!-- Custom CSS -->
    <link href="<?php echo e(asset('assets/app.css')); ?>?v=1.2" rel="stylesheet">
    <?php echo $__env->yieldPushContent('styles'); ?>

    <!-- Custom JS -->
    <script src="<?php echo e(asset('assets/app.js')); ?>?v=1.2"></script>

    <!-- Chart.js for Dashboard (Local) -->
    <script src="<?php echo e(asset('assets/chart.min.js')); ?>"></script>
    <script src="<?php echo e(asset('assets/chartjs-plugin-datalabels.min.js')); ?>"></script>

    <?php if(auth()->guard()->check()): ?>
        <?php
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
        ?>

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
                            <img src="<?php echo e(asset('uploads/logo.png')); ?>" alt="Euro System Logo" class="h-9 md:h-8 lg:h-12 w-auto object-contain">
                            <span class="text-[9px] text-gray-400 font-bold uppercase tracking-widest leading-none mt-1.5 block md:hidden lg:block">Fleet Management</span>
                        </div>
                        
                        <!-- Close Button on Mobile -->
                        <button type="button" onclick="toggleMobileSidebar()" 
                            class="p-1.5 text-gray-400 hover:text-gray-700 hover:bg-gray-100 rounded-full md:hidden flex items-center justify-center shrink-0 transition-colors focus:outline-none focus:ring-2 focus:ring-yellow-500">
                            <i data-lucide="x" class="w-5 h-5"></i>
                        </button>
                    </div>

                    <!-- Navigation -->
                    <nav class="flex-1 p-2 lg:p-4 space-y-1 overflow-y-auto overflow-x-hidden w-full">
                        <?php if(auth()->user()->role === 'super_admin'): ?>
                        <a href="<?php echo e(route('super-admin.index')); ?>"
                            class="sidebar-item flex items-center justify-start md:justify-center lg:justify-start gap-2.5 px-4 md:px-0 lg:px-4 py-1.5 md:py-2 rounded-lg font-semibold <?php echo e(request()->routeIs('super-admin.*') ? 'bg-yellow-100 text-yellow-800' : 'text-yellow-700 hover:bg-yellow-50 hover:text-yellow-800'); ?>">
                            <i data-lucide="crown" class="w-5 md:w-5 lg:w-4 h-5 md:h-5 lg:h-4"></i>
                            <span class="text-sm block md:hidden lg:block">Owner Panel</span>
                        </a>
                        <hr class="my-2 border-gray-100 block md:hidden lg:block">
                        <?php endif; ?>

                        <?php if(auth()->user()->hasAccessTo('dashboard')): ?>
                        <a href="<?php echo e(route('dashboard')); ?>"
                            class="sidebar-item flex items-center justify-start md:justify-center lg:justify-start gap-2.5 px-4 md:px-0 lg:px-4 py-1.5 md:py-2 rounded-lg text-gray-700 hover:bg-yellow-50 hover:text-yellow-700 <?php echo e(request()->routeIs('dashboard') ? 'bg-yellow-50 text-yellow-700 font-semibold' : ''); ?>">
                            <i data-lucide="layout-dashboard" class="w-5 md:w-5 lg:w-4 h-5 md:h-5 lg:h-4"></i>
                            <span class="text-sm block md:hidden lg:block">Dashboard</span>
                        </a>
                        <?php endif; ?>

                        <?php if(auth()->user()->hasAccessTo('units.*')): ?>
                        <a href="<?php echo e(route('units.index')); ?>"
                            class="sidebar-item flex items-center justify-start md:justify-center lg:justify-start gap-2.5 px-4 md:px-0 lg:px-4 py-1.5 md:py-2 rounded-lg text-gray-700 hover:bg-yellow-50 hover:text-yellow-700 <?php echo e(request()->routeIs('units.*') ? 'bg-yellow-50 text-yellow-700 font-semibold' : ''); ?>">
                            <i data-lucide="car" class="w-5 md:w-5 lg:w-4 h-5 md:h-5 lg:h-4"></i>
                            <span class="text-sm block md:hidden lg:block">Unit Management</span>
                        </a>
                        <?php endif; ?>

                        <?php if(auth()->user()->hasAccessTo('driver-management.*')): ?>
                        <a href="<?php echo e(route('driver-management.index')); ?>"
                            class="sidebar-item flex items-center justify-start md:justify-center lg:justify-start gap-2.5 px-4 md:px-0 lg:px-4 py-1.5 md:py-2 rounded-lg text-gray-700 hover:bg-yellow-50 hover:text-yellow-700 <?php echo e(request()->routeIs('driver-management.*') ? 'bg-yellow-50 text-yellow-700 font-semibold' : ''); ?>">
                            <i data-lucide="users" class="w-5 md:w-5 lg:w-4 h-5 md:h-5 lg:h-4"></i>
                            <span class="text-sm block md:hidden lg:block">Driver Management</span>
                        </a>
                        <?php endif; ?>

                        <?php if(auth()->user()->hasAccessTo('live-tracking.*')): ?>
                        <a href="<?php echo e(route('live-tracking.index')); ?>"
                            class="sidebar-item flex items-center justify-start md:justify-center lg:justify-start gap-2.5 px-4 md:px-0 lg:px-4 py-1.5 md:py-2 rounded-lg text-gray-700 hover:bg-yellow-50 hover:text-yellow-700 <?php echo e(request()->routeIs('live-tracking.*') ? 'bg-yellow-50 text-yellow-700 font-semibold' : ''); ?>">
                            <i data-lucide="map-pin" class="w-5 md:w-5 lg:w-4 h-5 md:h-5 lg:h-4"></i>
                            <span class="text-sm block md:hidden lg:block">Live Tracking</span>
                        </a>
                        <?php endif; ?>

                        <?php if(auth()->user()->hasAccessTo('decision-management.*')): ?>
                        <a href="<?php echo e(route('decision-management.index')); ?>"
                            class="sidebar-item flex items-center justify-start md:justify-center lg:justify-start gap-2.5 px-4 md:px-0 lg:px-4 py-1.5 md:py-2 rounded-lg text-gray-700 hover:bg-yellow-50 hover:text-yellow-700 <?php echo e(request()->routeIs('decision-management.*') ? 'bg-yellow-50 text-yellow-700 font-semibold' : ''); ?>">
                            <i data-lucide="file-text" class="w-5 md:w-5 lg:w-4 h-5 md:h-5 lg:h-4"></i>
                            <span class="text-sm block md:hidden lg:block">Franchise</span>
                        </a>
                        <?php endif; ?>

                        <?php if(auth()->user()->hasAccessTo('boundaries.*')): ?>
                        <a href="<?php echo e(route('boundaries.index')); ?>"
                            class="sidebar-item flex items-center justify-start md:justify-center lg:justify-start gap-2.5 px-4 md:px-0 lg:px-4 py-1.5 md:py-2 rounded-lg text-gray-700 hover:bg-yellow-50 hover:text-yellow-700 <?php echo e(request()->routeIs('boundaries.*') ? 'bg-yellow-50 text-yellow-700 font-semibold' : ''); ?>">
                            <i data-lucide="wallet" class="w-5 md:w-5 lg:w-4 h-5 md:h-5 lg:h-4"></i>
                            <span class="text-sm block md:hidden lg:block">Boundaries</span>
                        </a>
                        <?php endif; ?>

                        <?php if(auth()->user()->hasAccessTo('maintenance.*')): ?>
                        <a href="<?php echo e(route('maintenance.index')); ?>"
                            class="sidebar-item flex items-center justify-start md:justify-center lg:justify-start gap-2.5 px-4 md:px-0 lg:px-4 py-1.5 md:py-2 rounded-lg text-gray-700 hover:bg-yellow-50 hover:text-yellow-700 <?php echo e(request()->routeIs('maintenance.*') ? 'bg-yellow-50 text-yellow-700 font-semibold' : ''); ?>">
                            <i data-lucide="wrench" class="w-5 md:w-5 lg:w-4 h-5 md:h-5 lg:h-4"></i>
                            <span class="text-sm block md:hidden lg:block">Maintenance</span>
                        </a>
                        <?php endif; ?>

                        <?php if(auth()->user()->hasAccessTo('coding.*')): ?>
                        <a href="<?php echo e(route('coding.index')); ?>"
                            class="sidebar-item flex items-center justify-start md:justify-center lg:justify-start gap-2.5 px-4 md:px-0 lg:px-4 py-1.5 md:py-2 rounded-lg text-gray-700 hover:bg-yellow-50 hover:text-yellow-700 <?php echo e(request()->routeIs('coding.*') ? 'bg-yellow-50 text-yellow-700 font-semibold' : ''); ?>">
                            <i data-lucide="calendar" class="w-5 md:w-5 lg:w-4 h-5 md:h-5 lg:h-4"></i>
                            <span class="text-sm block md:hidden lg:block">Coding Management</span>
                        </a>
                        <?php endif; ?>

                        <?php if(auth()->user()->hasAccessTo('driver-behavior.*')): ?>
                        <a href="<?php echo e(route('driver-behavior.index')); ?>"
                            class="sidebar-item flex items-center justify-start md:justify-center lg:justify-start gap-2.5 px-4 md:px-0 lg:px-4 py-1.5 md:py-2 rounded-lg text-gray-700 hover:bg-yellow-50 hover:text-yellow-700 <?php echo e(request()->routeIs('driver-behavior.*') ? 'bg-yellow-50 text-yellow-700 font-semibold' : ''); ?>">
                            <i data-lucide="alert-triangle" class="w-5 md:w-5 lg:w-4 h-5 md:h-5 lg:h-4"></i>
                            <span class="text-sm block md:hidden lg:block">Driver Behavior</span>
                        </a>
                        <?php endif; ?>

                        <?php if(auth()->user()->hasAccessTo('office-expenses.*')): ?>
                        <a href="<?php echo e(route('office-expenses.index')); ?>"
                            class="sidebar-item flex items-center justify-start md:justify-center lg:justify-start gap-2.5 px-4 md:px-0 lg:px-4 py-1.5 md:py-2 rounded-lg text-gray-700 hover:bg-yellow-50 hover:text-yellow-700 <?php echo e(request()->routeIs('office-expenses.*') ? 'bg-yellow-50 text-yellow-700 font-semibold' : ''); ?>">
                            <i data-lucide="philippine-peso" class="w-5 md:w-5 lg:w-4 h-5 md:h-5 lg:h-4"></i>
                            <span class="text-sm block md:hidden lg:block">Office Expenses</span>
                        </a>
                        <?php endif; ?>

                        <?php if(auth()->user()->hasAccessTo('salary.*')): ?>
                        <a href="<?php echo e(route('salary.index')); ?>"
                            class="sidebar-item flex items-center justify-start md:justify-center lg:justify-start gap-2.5 px-4 md:px-0 lg:px-4 py-1.5 md:py-2 rounded-lg text-gray-700 hover:bg-yellow-50 hover:text-yellow-700 <?php echo e(request()->routeIs('salary.*') ? 'bg-yellow-50 text-yellow-700 font-semibold' : ''); ?>">
                            <i data-lucide="calculator" class="w-5 md:w-5 lg:w-4 h-5 md:h-5 lg:h-4"></i>
                            <span class="text-sm block md:hidden lg:block">Salary Management</span>
                        </a>
                        <?php endif; ?>

                        <?php if(auth()->user()->hasAccessTo('analytics.*')): ?>
                        <a href="<?php echo e(route('analytics.index')); ?>"
                            class="sidebar-item flex items-center justify-start md:justify-center lg:justify-start gap-2.5 px-4 md:px-0 lg:px-4 py-1.5 md:py-2 rounded-lg text-gray-700 hover:bg-yellow-50 hover:text-yellow-700 <?php echo e(request()->routeIs('analytics.*') ? 'bg-yellow-50 text-yellow-700 font-semibold' : ''); ?>">
                            <i data-lucide="bar-chart" class="w-4 md:w-5 lg:w-4 h-4 md:h-5 lg:h-4"></i>
                            <span class="text-sm block md:hidden lg:block">Analytics</span>
                        </a>
                        <?php endif; ?>

                        <?php if(auth()->user()->hasAccessTo('activity-logs.*')): ?>
                        <a href="<?php echo e(route('activity-logs.index')); ?>"
                            class="sidebar-item flex items-center justify-start md:justify-center lg:justify-start gap-2.5 px-4 md:px-0 lg:px-4 py-1.5 md:py-2 rounded-lg text-gray-700 hover:bg-yellow-50 hover:text-yellow-700 <?php echo e(request()->routeIs('activity-logs.*') ? 'bg-yellow-50 text-yellow-700 font-semibold' : ''); ?>">
                            <i data-lucide="history" class="w-5 md:w-5 lg:w-4 h-5 md:h-5 lg:h-4"></i>
                            <span class="text-sm block md:hidden lg:block">History Logs</span>
                        </a>
                        <?php endif; ?>

                        <?php if(auth()->user()->hasAccessTo('unit-profitability.*')): ?>
                        <a href="<?php echo e(route('unit-profitability.index')); ?>"
                            class="sidebar-item flex items-center justify-start md:justify-center lg:justify-start gap-2.5 px-4 md:px-0 lg:px-4 py-1.5 md:py-2 rounded-lg text-gray-700 hover:bg-yellow-50 hover:text-yellow-700 <?php echo e(request()->routeIs('unit-profitability.*') ? 'bg-yellow-50 text-yellow-700 font-semibold' : ''); ?>">
                            <i data-lucide="trending-up" class="w-5 md:w-5 lg:w-4 h-5 md:h-5 lg:h-4"></i>
                            <span class="text-sm block md:hidden lg:block">Unit Profitability</span>
                        </a>
                        <?php endif; ?>

                        <?php if(auth()->user()->hasAccessTo('staff.*')): ?>
                        <a href="<?php echo e(route('staff.index')); ?>"
                            class="sidebar-item flex items-center justify-start md:justify-center lg:justify-start gap-2.5 px-4 md:px-0 lg:px-4 py-1.5 md:py-2 rounded-lg text-gray-700 hover:bg-yellow-50 hover:text-yellow-700 <?php echo e(request()->routeIs('staff.*') ? 'bg-yellow-50 text-yellow-700 font-semibold' : ''); ?>">
                            <i data-lucide="user-cog" class="w-5 md:w-5 lg:w-4 h-5 md:h-5 lg:h-4"></i>
                            <span class="text-sm block md:hidden lg:block">Staff Records</span>
                        </a>
                        <?php endif; ?>

                        <hr class="my-2 border-gray-100 block md:hidden lg:block">

                        <?php if(auth()->user()->hasAccessTo('archive.*')): ?>
                        <a href="<?php echo e(route('archive.index')); ?>"
                            class="sidebar-item flex items-center justify-start md:justify-center lg:justify-start gap-2.5 px-4 md:px-0 lg:px-4 py-1.5 md:py-2 rounded-lg text-gray-700 hover:bg-red-50 hover:text-red-700 <?php echo e(request()->routeIs('archive.*') ? 'bg-red-50 text-red-700 font-semibold' : ''); ?>">
                            <i data-lucide="archive" class="w-5 md:w-5 lg:w-4 h-5 md:h-5 lg:h-4"></i>
                            <span class="text-sm block md:hidden lg:block">Archive</span>
                        </a>
                        <?php endif; ?>
                    </nav>

                    <!-- User Menu -->
                    <div class="p-2 lg:p-4 border-t bg-white relative z-50 flex-shrink-0 w-full">
                        <a href="<?php echo e(route('my-account')); ?>" 
                           class="flex items-center justify-start md:justify-center lg:justify-start gap-3 mb-3 p-1 lg:p-2 rounded-lg hover:bg-gray-50 transition-colors group w-full">
                            <div
                                class="w-8 h-8 lg:w-10 lg:h-10 bg-yellow-600 rounded-full flex items-center justify-center text-white font-semibold group-hover:bg-yellow-700 transition-colors overflow-hidden flex-shrink-0 border border-gray-100">
                                <?php if(auth()->user()->profile_image): ?>
                                    <?php
                                        $imagePath = str_replace('resources/', '', auth()->user()->profile_image);
                                        $isIcon = str_contains($imagePath, 'image/') && !str_contains($imagePath, 'storage/');
                                    ?>
                                    <?php if($isIcon): ?>
                                        <img src="<?php echo e(asset($imagePath)); ?>" alt="Profile" class="w-full h-full object-cover">
                                    <?php else: ?>
                                        <img src="<?php echo e(asset('storage/' . auth()->user()->profile_image)); ?>" alt="Profile" class="w-full h-full object-cover">
                                    <?php endif; ?>
                                <?php else: ?>
                                    <?php echo e(strtoupper(substr(auth()->user()->full_name ?? 'U', 0, 1))); ?>

                                <?php endif; ?>
                            </div>
                            <div class="block md:hidden lg:block min-w-0 flex-1">
                                <h4 class="text-sm font-semibold text-gray-900 truncate"><?php echo e(auth()->user()->first_name); ?> <?php echo e(auth()->user()->last_name); ?></h4>
                                <p class="text-xs text-gray-500 truncate"><?php echo e(auth()->user()->role === 'super_admin' ? 'Owner' : ucfirst(auth()->user()->role ?? 'user')); ?></p>
                            </div>
                            <i data-lucide="chevron-right" class="w-4 h-4 text-gray-400 group-hover:text-yellow-600 transition-colors hidden lg:block"></i>
                        </a>
                        
                        <!-- Logout Form -->
                        <form id="logout-form" action="<?php echo e(route('logout')); ?>" method="GET" class="hidden"></form>
                        
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
                            <button onclick="toggleMobileSidebar()" class="p-2 -ml-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg md:hidden flex items-center justify-center shrink-0">
                                <i data-lucide="menu" class="w-6 h-6"></i>
                            </button>
                            <div>
                                <h2 class="text-lg md:text-2xl font-black text-gray-900 leading-tight"><?php echo $__env->yieldContent('page-heading', 'Dashboard'); ?></h2>
                                <?php if (! empty(trim($__env->yieldContent('page-subheading')))): ?>
                                    <p class="text-[11px] md:text-sm text-gray-500 mt-0.5 md:mt-1"><?php echo $__env->yieldContent('page-subheading'); ?></p>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="flex items-center gap-4">
                            


                            <!-- Main Notification Bell -->
                            <div class="relative">
                                <button id="notificationBell"
                                    class="relative p-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg">
                                    <i data-lucide="bell" class="w-5 h-5"></i>
                                    <span id="main-nav-notif-badge"
                                            class="absolute -top-1 -right-1 min-w-[18px] h-[18px] px-1 bg-red-500 text-white text-[10px] font-black leading-[18px] rounded-full text-center transition-all duration-300 <?php echo e($headerNotificationCount > 0 ? '' : 'hidden'); ?>">
                                            <?php echo e($headerNotificationCount); ?>

                                        </span>
                                </button>

                                <div id="notificationDropdown"
                                    class="hidden fixed md:absolute inset-x-4 md:inset-x-auto md:right-0 mt-2 md:w-80 bg-white shadow-2xl md:shadow-xl rounded-2xl border border-gray-100 z-[9999] overflow-hidden">
                                    <div class="px-4 py-3 border-b bg-gray-50/50 flex items-center justify-between">
                                        <div class="flex flex-col">
                                            <span class="text-sm font-black text-gray-900 tracking-tight">Notifications</span>
                                            <span id="notif-dropdown-subtitle" class="text-[9px] font-bold text-gray-400 uppercase tracking-widest"><?php echo e($headerNotificationCount); ?> item(s)</span>
                                        </div>
                                        <?php if($headerNotificationCount > 0): ?>
                                            <button onclick="markAllAsRead()" class="text-[10px] font-bold text-yellow-600 hover:text-yellow-700 hover:underline transition-all">
                                                Mark All Read
                                            </button>
                                        <?php endif; ?>
                                    </div>

                                    
                                    <div class="flex border-b bg-white">
                                        <button onclick="filterNotifs('system')" id="btn-filter-system" class="flex-1 py-2.5 text-[11px] font-bold uppercase tracking-wider text-yellow-600 border-b-2 border-yellow-500 transition-all">
                                            System
                                            <span id="badge-filter-system" class="bg-red-500 text-white text-[9px] px-1.5 py-0.5 rounded-full ml-1 <?php echo e($systemNotifCount > 0 ? '' : 'hidden'); ?>"><?php echo e($systemNotifCount); ?></span>
                                        </button>
                                        <button onclick="filterNotifs('low_stock')" id="btn-filter-parts" class="flex-1 py-2.5 text-[11px] font-bold uppercase tracking-wider text-gray-400 hover:text-gray-600 transition-all flex items-center justify-center gap-1.5">
                                            Parts Stock
                                            <span id="badge-filter-parts" class="bg-orange-500 text-white text-[9px] px-1.5 py-0.5 rounded-full <?php echo e($stockNotifCount > 0 ? '' : 'hidden'); ?>"><?php echo e($stockNotifCount); ?></span>
                                        </button>
                                    </div>

                                    <div class="max-h-80 overflow-y-auto" id="notificationList">
                                        <?php if(empty($headerNotifications)): ?>
                                            <div class="px-4 py-4 text-sm text-gray-500 text-center">No notifications.</div>
                                        <?php else: ?>
                                            <?php $__currentLoopData = $headerNotifications; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $n): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <?php 
                                                    $notifId = $n['id'] ?? md5($n['title'] . ($n['message'] ?? '')); 
                                                    $isHidden = ($n['type'] === 'low_stock');
                                                ?>
                                                <div class="notification-item px-4 py-3 border-b last:border-b-0 hover:bg-gray-50 flex items-start gap-2 transition-all unread-notif <?php echo e($isHidden ? 'hidden' : ''); ?>"
                                                     id="notif-<?php echo e($notifId); ?>"
                                                     data-type="<?php echo e($n['type']); ?>" 
                                                     data-notif-id="<?php echo e($notifId); ?>"
                                                     style="background-color: #f0f9ff;">
                                                    <a href="<?php echo e($n['url'] ?? '#'); ?>" class="flex-1 flex gap-3 min-w-0" onclick="markAsRead('<?php echo e($notifId); ?>')">

                                                        <div class="mt-0.5 flex-shrink-0">
                                                            <?php if($n['type'] === 'case_expiry'): ?>
                                                                <i data-lucide="file-warning" class="w-4 h-4 text-yellow-600"></i>
                                                            <?php elseif($n['type'] === 'coding_today'): ?>
                                                                <i data-lucide="car-front" class="w-4 h-4 text-blue-600"></i>
                                                            <?php elseif($n['type'] === 'violation_alert'): ?>
                                                                <i data-lucide="shield-alert" class="w-4 h-4 text-red-600"></i>
                                                            <?php elseif($n['type'] === 'low_stock'): ?>
                                                                <i data-lucide="package-search" class="w-4 h-4 text-orange-500"></i>
                                                            <?php elseif($n['type'] === 'license_expiry'): ?>
                                                                <i data-lucide="id-card" class="w-4 h-4 text-rose-500"></i>
                                                            <?php elseif($n['type'] === 'odo_maint_due'): ?>
                                                                <i data-lucide="settings-2" class="w-4 h-4 text-orange-600"></i>
                                                            <?php else: ?>
                                                                <i data-lucide="alert-circle" class="w-4 h-4 text-red-600"></i>
                                                            <?php endif; ?>
                                                        </div>
                                                        <div class="flex-1 min-w-0">
                                                            <p class="text-xs font-semibold text-gray-800 truncate">
                                                                <?php echo e($n['title']); ?></p>
                                                            <p class="text-xs text-gray-600 mt-0.5 line-clamp-2"><?php echo e($n['message']); ?></p>
                                                            <?php if(isset($n['time'])): ?>
                                                                <p class="text-[10px] text-gray-400 mt-1 font-medium"><?php echo e($n['time']); ?></p>
                                                            <?php endif; ?>
                                                        </div>
                                                    </a>
                                                    <button type="button"
                                                        class="ml-1 text-gray-400 hover:text-gray-600 flex-shrink-0"
                                                        onclick="dismissNotification(this);">
                                                        <span class="sr-only">Dismiss</span>
                                                        <i data-lucide="x" class="w-3 h-3"></i>
                                                    </button>
                                                </div>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <!-- Date/Time -->
                            <div class="text-right hidden md:block">
                                <p id="header-date" class="text-[13px] font-medium text-gray-900"><?php echo e(date('l, F j, Y')); ?></p>
                                <p id="header-time" class="text-[11px] text-gray-500 transition-all duration-300"><?php echo e(date('h:i A')); ?></p>
                            </div>
                        </div>
                    </div>
                </header>

                <!-- Page Content -->
                <div id="appContentArea" class="flex-1 overflow-y-auto <?php echo $__env->yieldContent('main-padding', 'p-4'); ?>">
                    
                    <?php $__currentLoopData = ['success', 'error', 'warning', 'info']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php if(session($type)): ?>
                            <div class="alert-slide mb-4 p-4 rounded-lg border
                                    <?php if($type === 'success'): ?> bg-green-50 border-green-200 text-green-800
                                    <?php elseif($type === 'error'): ?> bg-red-50 border-red-200 text-red-800
                                    <?php elseif($type === 'warning'): ?> bg-yellow-50 border-yellow-200 text-yellow-800
                                    <?php else: ?> bg-blue-50 border-blue-200 text-blue-800
                                    <?php endif; ?>">
                                <div class="flex items-center gap-2">
                                    <i data-lucide="<?php if($type === 'success'): ?> check-circle <?php elseif($type === 'error'): ?> x-circle <?php elseif($type === 'warning'): ?> alert-triangle <?php else: ?> info <?php endif; ?>"
                                        class="w-5 h-5"></i>
                                    <span><?php echo e(session($type)); ?></span>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                    
                    <?php if($errors->any()): ?>
                        <div class="alert-slide mb-4 p-4 rounded-lg border bg-red-50 border-red-200 text-red-800">
                            <div class="flex items-center gap-2 mb-2">
                                <i data-lucide="x-circle" class="w-5 h-5"></i>
                                <span class="font-semibold">Please fix the following errors:</span>
                            </div>
                            <ul class="list-disc list-inside text-sm">
                                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <li><?php echo e($error); ?></li>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <?php echo $__env->yieldContent('content'); ?>
                </div>
            </main>
        </div>

        
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

    <?php else: ?>
        <!-- Login/Signup Layout -->
        <div class="min-h-screen bg-gradient-to-br from-yellow-50 to-orange-50 flex items-center justify-center p-4">
            <div class="w-full max-w-md">
                <?php echo $__env->yieldContent('content'); ?>
            </div>
        </div>
    <?php endif; ?>

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
                        body: JSON.stringify({ message: message, data: data, user_id: "<?php echo e(Auth::id()); ?>" })
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
                        user_id: "<?php echo e(Auth::id()); ?>"
                    });
                }

                if (hasPush) {
                    console.log('Capacitor Native Platform and PushNotifications plugin detected! Initializing bridge...');
                    reportDiag("Capacitor Push found, initializing bridge", { user_id: "<?php echo e(Auth::id()); ?>" });
                    const PushNotifications = window.Capacitor.Plugins.PushNotifications;
                    const currentUserId = "<?php echo e(Auth::id()); ?>";

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

        // Real-Time Notification Polling & UI Sync (Lightweight background tasks)
        let pollInterval = null;

        function updateNotificationUI(data) {
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
            pollInterval = setInterval(pollNotifications, 12000); // Poll every 12 seconds
        }

        document.addEventListener('DOMContentLoaded', () => {
            <?php if(auth()->guard()->check()): ?>
                startNotificationPolling();
            <?php endif; ?>
        });
    </script>

    <!-- Structured Data (JSON-LD) -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "Organization",
        "name": "Euro Taxi System",
        "url": "https://www.eurotaxisystem.site",
        "logo": "https://www.eurotaxisystem.site/<?php echo e(asset('image/logo.png')); ?>",
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
    <?php echo $__env->yieldPushContent('scripts'); ?>
</body>

</html><?php /**PATH C:\xampp\htdocs\eurotaxisystem-main\resources\views/layouts/app.blade.php ENDPATH**/ ?>