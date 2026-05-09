<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    
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

    <!-- Cache control headers to prevent back button caching -->
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    
    <!-- Favicon -->
    <link rel="shortcut icon" href="<?php echo e(asset('favicon_euro_transparent.png')); ?>?v=1.6">
    <link rel="icon" type="image/png" href="<?php echo e(asset('favicon_euro_transparent.png')); ?>?v=1.6">
    <link rel="apple-touch-icon" href="<?php echo e(asset('favicon_euro_transparent.png')); ?>?v=1.6">
    
    <!-- Critical Assets (Local) -->
    <script src="<?php echo e(asset('assets/tailwind.min.js')); ?>?v=stable_3.4.1"></script>
    <script src="<?php echo e(asset('assets/lottie-player.js')); ?>?v=stable_2.0.4"></script>
    <link rel="stylesheet" href="<?php echo e(asset('assets/fontawesome/all.min.css')); ?>?v=stable_6.4.0">
    <link rel="stylesheet" href="<?php echo e(asset('assets/inter/inter.css')); ?>?v=stable_3.19.3">

    
    <script>
        // Prevent back button caching
        (function() {
            window.onpageshow = function(event) {
                if (event.persisted) {
                    window.location.reload();
                }
            };
            
            // Clear history when coming from authenticated pages
            if (performance.navigation.type === 2) {
                window.location.replace(window.location.href);
            }
        })();
    </script>
    <style>
        * { box-sizing: border-box; }

        html, body {
            margin: 0;
            padding: 0;
            height: 100%;
            overflow: hidden;
            background-color: #0a0f1e;
            font-family: 'Inter', sans-serif;
        }

        .flip-container {
            perspective: 1500px;
            width: 100%;
            max-width: 420px;
        }

        .flipper {
            display: grid;
            align-items: center;
            position: relative;
            width: 100%;
            transform-style: preserve-3d;
            transition: transform 0.8s cubic-bezier(0.4, 0.0, 0.2, 1);
        }

        .flipper.state-login {
            transform: rotateY(0deg);
        }

        .flipper.state-register {
            transform: rotateY(180deg);
        }

        .flipper.state-forgot {
            transform: rotateY(360deg);
        }

        .flip-face {
            position: absolute;
            width: 100%;
            height: 100%;
            backface-visibility: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow-y: auto;
            padding: 1rem 0;
        }

        .front-face {
            z-index: 2;
        }

        .back-face {
            transform: rotateY(180deg);
        }

        .forgot-face {
            transform: rotateY(180deg);
        }

        .form-wrapper {
            width: 100%;
            max-width: 380px;
            padding: 2rem 2.2rem;
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            background: rgba(255,255,255,0.97);
            border-radius: 1.5rem;
            box-shadow: 0 8px 32px rgba(0,0,0,0.18), 0 1.5px 8px rgba(59,130,246,0.07);
            border: 1px solid rgba(255,255,255,0.7);
            margin: auto;
        }

        .form-panel {
            width: 100%;
            display: none;
        }

        .login-panel {
            display: block;
        }

        .flipper.state-login .front-face .login-panel,
        .flipper.state-register .back-face .register-panel,
        .flipper.state-forgot .front-face .forgot-panel {
            display: block !important;
            visibility: visible !important;
            opacity: 1 !important;
        }

        .flipper.state-login .forgot-panel,
        .flipper.state-register .front-face,
        .flipper.state-forgot .login-panel {
            display: none;
        }

        .grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.4rem;
            width: 100%;
        }

        .mb-4 button {
            display: flex !important;
            align-items: center;
            padding: 0.5rem 1rem;
            background: transparent;
            border: none;
            cursor: pointer;
            font-size: 0.9rem;
            color: #6b7280;
            transition: color 0.3s ease;
            margin-bottom: 1rem;
            z-index: 10;
            position: relative;
        }

        .mb-4 button:hover {
            color: #2563eb !important;
        }

        .form-panel .mb-4:not(.flex) {
            display: block !important;
            visibility: visible !important;
        }

        .flex.mb-4 button {
            padding: 0 !important;
            margin: 0 !important;
            display: inline-block !important;
            height: auto !important;
            line-height: inherit !important;
        }

        .form-panel button {
            opacity: 1 !important;
            visibility: visible !important;
        }

        .input-group {
            position: relative;
            margin-bottom: 0.4rem;
            width: 100%;
        }

        .input-icon-wrapper {
            position: relative;
            width: 100%;
        }

        .input-group input,
        .input-group select {
            width: 100%;
            padding: 0.6rem 0.75rem 0.6rem 2.25rem;
            border: 1.5px solid #e2e8f0;
            border-radius: 0.65rem;
            font-size: 0.875rem;
            font-family: 'Inter', sans-serif;
            transition: all 0.2s ease;
            background: #f8fafc;
            color: #1e293b;
        }

        .input-group input:focus,
        .input-group select:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.12);
            background: #fff;
        }

        .input-icon-wrapper i {
            position: absolute;
            left: 0.7rem;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
            font-size: 0.875rem;
            pointer-events: none;
            z-index: 10;
        }

        .pw-group,
        .ph-phone-wrapper {
            position: relative;
            display: flex;
            align-items: center;
            border: 1.5px solid #e2e8f0;
            border-radius: 0.65rem;
            background: #f8fafc;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
            margin-bottom: 0.4rem;
            width: 100%;
        }

        .pw-group:focus-within,
        .ph-phone-wrapper:focus-within {
            border-color: #3b82f6 !important;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.12) !important;
            background: #fff !important;
        }

        .pw-group .pw-icon {
            flex-shrink: 0;
            padding: 0 0.35rem 0 0.75rem;
            color: #94a3b8;
            font-size: 0.875rem;
            pointer-events: none;
        }

        .pw-group input {
            flex: 1;
            min-width: 0;
            border: none !important;
            outline: none !important;
            box-shadow: none !important;
            background: transparent !important;
            padding: 0.6rem 2rem 0.6rem 0.25rem !important;
            font-size: 0.875rem;
            font-family: 'Inter', sans-serif;
            color: #1e293b;
        }

        .toggle-password {
            position: absolute;
            right: 0;
            top: 0;
            bottom: 0;
            flex-shrink: 0;
            background: none;
            border: none;
            cursor: pointer;
            color: #9ca3af;
            font-size: 0.875rem;
            padding: 0 0.65rem;
            display: none;
            align-items: center;
            justify-content: center;
            transition: color 0.2s ease;
            z-index: 5;
        }

        .toggle-password:hover {
            color: #3b82f6;
        }

        .pw-group input:not(:placeholder-shown) ~ .toggle-password {
            display: flex;
        }

        .btn-primary {
            width: 100%;
            padding: 0.75rem;
            background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%);
            color: white;
            border: none;
            border-radius: 0.75rem;
            font-size: 0.9375rem;
            font-weight: 700;
            font-family: 'Inter', sans-serif;
            letter-spacing: 0.01em;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-bottom: 0.75rem;
            box-shadow: 0 4px 15px rgba(37,99,235,0.3);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 28px rgba(37, 99, 235, 0.45);
            background: linear-gradient(135deg, #1d4ed8 0%, #1e3a8a 100%);
        }

        .btn-secondary {
            width: 100%;
            padding: 0.7rem;
            background: linear-gradient(135deg, #7c3aed 0%, #5b21b6 100%);
            color: white;
            border: none;
            border-radius: 0.75rem;
            font-size: 0.875rem;
            font-weight: 700;
            font-family: 'Inter', sans-serif;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-bottom: 0.5rem;
            box-shadow: 0 4px 15px rgba(124,58,237,0.3);
        }

        .btn-secondary:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 28px rgba(124, 58, 237, 0.45);
        }

        .forgot-options {
            display: flex;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .forgot-option {
            flex: 1;
            padding: 1rem;
            border: 2px solid #e5e7eb;
            border-radius: 0.5rem;
            background: white;
            cursor: pointer;
            transition: all 0.3s ease;
            text-align: center;
        }

        .forgot-option:hover {
            border-color: #3b82f6;
            background: #f8fafc;
        }

        .forgot-option.selected {
            border-color: #3b82f6;
            background: #eff6ff;
        }

        .otp-inputs {
            display: flex;
            gap: 0.5rem;
            justify-content: center;
            margin-bottom: 1.5rem;
        }

        .otp-input {
            width: 42px;
            height: 42px;
            text-align: center;
            font-size: 1.25rem;
            font-weight: bold;
            border: 2px solid #e5e7eb;
            border-radius: 0.5rem;
            transition: all 0.3s ease;
        }

        .otp-input:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .resend-btn {
            background: #6b7280;
            color: white;
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 0.25rem;
            font-size: 0.875rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .resend-btn:disabled {
            background: #d1d5db;
            cursor: not-allowed;
        }

        .resend-btn:not(:disabled):hover {
            background: #4b5563;
        }

        .message-toast {
            position: fixed;
            top: 16px;
            right: 16px;
            max-width: 320px;
            width: auto;
            padding: 0.85rem 1.2rem;
            border-radius: 0.6rem;
            color: white;
            font-weight: 500;
            font-size: 0.875rem;
            line-height: 1.4;
            word-wrap: break-word;
            z-index: 99999;
            box-shadow: 0 4px 16px rgba(0,0,0,0.18);
            transform: translateY(-120px);
            opacity: 0;
            transition: transform 0.35s cubic-bezier(0.34, 1.56, 0.64, 1), opacity 0.3s ease;
            pointer-events: none;
        }

        .message-toast.show {
            transform: translateY(0);
            opacity: 1;
            pointer-events: auto;
        }

        .message-toast.success {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        }

        .message-toast.error {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        }

        .frosted-overlay {
            background: rgba(0, 0, 0, 0.12);
            backdrop-filter: blur(3px);
            -webkit-backdrop-filter: blur(3px);
        }

        .text-shadow-enhanced {
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
        }

        .text-shadow-light {
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.3);
        }

        @keyframes logoBounce {
            0% {
                transform: translateY(-50px);
                opacity: 0;
            }

            50% {
                transform: translateY(10px);
                opacity: 1;
            }

            65% {
                transform: translateY(-5px);
            }

            80% {
                transform: translateY(3px);
            }

            95% {
                transform: translateY(-1px);
            }

            100% {
                transform: translateY(0);
            }
        }

        .logo-bounce {
            animation: logoBounce 1.5s ease-out;
        }

        @keyframes pulseGlow {
            0% {
                filter: drop-shadow(0 0 20px rgba(59, 130, 246, 0.4));
            }
            50% {
                filter: drop-shadow(0 0 60px rgba(59, 130, 246, 0.95));
            }
            100% {
                filter: drop-shadow(0 0 20px rgba(59, 130, 246, 0.4));
            }
        }

        .pulse-shadow {
            animation: pulseGlow 1.2s infinite ease-in-out;
        }

        @keyframes iconSlideIn1 {
            0% {
                transform: translateX(-100px);
                opacity: 0;
            }

            100% {
                transform: translateX(0);
                opacity: 1;
            }
        }

        @keyframes iconSlideIn2 {
            0% {
                transform: translateY(50px);
                opacity: 0;
            }

            100% {
                transform: translateY(0);
                opacity: 1;
            }
        }

        @keyframes iconSlideIn3 {
            0% {
                transform: translateX(100px);
                opacity: 0;
            }

            100% {
                transform: translateX(0);
                opacity: 1;
            }
        }

        .icon-animate-1 {
            animation: iconSlideIn1 0.8s ease-out 0.5s both;
        }

        .icon-animate-2 {
            animation: iconSlideIn2 0.8s ease-out 0.7s both;
        }

        .icon-animate-3 {
            animation: iconSlideIn3 0.8s ease-out 0.9s both;
        }

        .logo-container {
            max-width: 400px;
            width: 100%;
        }

        .logo-image {
            width: 100%;
            height: auto;
            max-height: 320px;
            object-fit: contain;
            filter: drop-shadow(0 4px 6px rgba(0, 0, 0, 0.3));
        }

        @media (max-width: 1024px) {
            .split-container {
                flex-direction: column !important;
                overflow-y: auto !important;
            }
            .branding-side, .auth-side {
                width: 100% !important;
                height: auto !important;
                min-height: auto !important;
            }
            .branding-side {
                padding: 4rem 2rem 2rem !important;
            }
            .auth-side {
                padding: 1rem 1rem 4rem !important;
            }
            .logo-image {
                max-width: 160px;
            }
            h1 {
                font-size: 2rem !important;
            }
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        .loading-spinner {
            width: 50px;
            height: 50px;
            border: 4px solid #f3f4f6;
            border-top: 4px solid #3b82f6;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        /* MFA Modal Styles */
        .mfa-modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(8px);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 100000;
            opacity: 0;
            transition: opacity 0.3s ease;
            padding: 1rem;
        }
        .mfa-modal-overlay.show {
            display: flex;
            opacity: 1;
        }
        .mfa-modal-content {
            background: white;
            width: 100%;
            max-width: 400px;
            max-height: calc(100vh - 2rem);
            overflow-y: auto;
            border-radius: 1.5rem;
            padding: 2rem;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            transform: scale(0.9);
            transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
        }
        .mfa-modal-overlay.show .mfa-modal-content {
            transform: scale(1);
        }
        .mfa-method-card {
            border: 2px solid #f3f4f6;
            transition: all 0.2s ease;
            cursor: pointer;
        }
        .mfa-method-card:hover {
            border-color: #3b82f6;
            background: #eff6ff;
        }
        .mfa-otp-input {
            width: 45px;
            height: 45px;
            text-align: center;
            font-size: 1.5rem;
            font-weight: bold;
            border: 2px solid #e5e7eb;
            border-radius: 0.75rem;
        }
        .mfa-otp-input:focus {
            border-color: #3b82f6;
            outline: none;
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
        }
    </style>
</head>

<body>
    <!-- Message Toast -->
    <div id="messageToast" class="message-toast"></div>

    <!-- Full Screen Layout -->
    <div class="split-container" style="position:relative;height:100vh;width:100vw;display:flex;overflow:hidden;">

        <!-- Full Screen Background Image -->
        <img src="<?php echo e(asset('uploads/1000053201.jpg')); ?>" alt="Eurotaxisystem" style="position:absolute;inset:0;width:100%;height:100%;object-fit:cover;z-index:0;">

        <!-- Overlay: lighter on right, darker on left -->
        <div style="position:absolute;inset:0;z-index:1;background:linear-gradient(105deg,rgba(8,12,35,0.88) 0%,rgba(12,20,55,0.85) 45%,rgba(20,40,100,0.65) 70%,rgba(30,58,138,0.45) 100%);"></div>

        <!-- LEFT HALF: Large Branding -->
        <div class="branding-side" style="position:relative;z-index:10;width:50%;height:100%;display:flex;flex-direction:column;align-items:center;justify-content:center;padding:3rem;">
            <div style="transform: translateY(-5vh); display: flex; flex-direction: column; align-items: center;">
                <!-- Logo -->
                <div class="logo-bounce" style="margin-bottom:1rem;">
                    <img src="<?php echo e(asset('uploads/logo.png')); ?>" alt="Eurotaxi Inc." class="pulse-shadow" style="width:380px;max-width:100%;object-fit:contain;">
                </div>
                <p style="color:#bfdbfe;font-size:0.95rem;font-weight:500;letter-spacing:0.18em;text-transform:uppercase;margin:0 0 1.5rem;font-family:'Inter',sans-serif;">Fleet Management System</p>
                <!-- Animation Stats Container -->
                <div style="display: flex; gap: 3rem; margin-top: 1rem; animation: logoBounce 2s ease-out;">
                    <!-- Drivers Lottie Animation -->
                    <div style="display: flex; flex-direction: column; align-items: center;">
                        <lottie-player 
                            src="<?php echo e(asset('animations/drivers.json')); ?>" 
                            background="transparent" 
                            speed="1" 
                            style="width: 170px; height: 170px; filter: drop-shadow(0 4px 15px rgba(59,130,246,0.3));"
                            loop 
                            autoplay>
                        </lottie-player>
                        <div style="text-align: center; margin-top: -15px;">
                            <p style="color:#fff;font-weight:800;font-size:2.2rem;margin:0;line-height:1;text-shadow:0 2px 10px rgba(0,0,0,0.5);"><?php echo e($driversCount ?? '0'); ?></p>
                            <p style="color:#93c5fd;font-size:0.85rem;font-weight:700;text-transform:uppercase;letter-spacing:0.15em;margin:0; margin-top: 5px;">Partner Drivers</p>
                        </div>
                    </div>

                    <!-- Units Lottie Animation -->
                    <div style="display: flex; flex-direction: column; align-items: center;">
                        <lottie-player 
                            src="<?php echo e(asset('animations/taxi_units.json')); ?>" 
                            background="transparent" 
                            speed="1" 
                            style="width: 170px; height: 170px; filter: drop-shadow(0 4px 15px rgba(52,211,153,0.3));"
                            loop 
                            autoplay>
                        </lottie-player>
                        <div style="text-align: center; margin-top: -15px;">
                            <p style="color:#fff;font-weight:800;font-size:2.2rem;margin:0;line-height:1;text-shadow:0 2px 10px rgba(0,0,0,0.5);"><?php echo e($unitsCount ?? '0'); ?></p>
                            <p style="color:#93c5fd;font-size:0.85rem;font-weight:700;text-transform:uppercase;letter-spacing:0.15em;margin:0; margin-top: 5px;">Taxi Units</p>
                        </div>
                    </div>
                </div>
            </div>


            <!-- Bottom copyright -->
            <p style="position:absolute;bottom:1.5rem;color:rgba(147,197,253,0.5);font-size:0.7rem;font-family:'Inter',sans-serif;">&copy; <?php echo e(date('Y')); ?> Eurotaxi Inc. All rights reserved.</p>
        </div>

        <!-- RIGHT HALF: Login Card -->
        <div class="auth-side" style="position:relative;z-index:10;width:50%;height:100%;display:flex;align-items:center;justify-content:center;padding:2rem;">
            <div class="flip-container" style="width:100%;max-width:430px;perspective:1500px;">
                <div class="flipper state-login" id="flipper" style="position:relative;width:100%;transform-style:preserve-3d;transition:transform 0.8s cubic-bezier(0.4,0,0.2,1);">

                    <!-- Front Face - Login & Forgot -->
                    <div class="front-face" style="grid-area:1/1;width:100%;backface-visibility:hidden;">
                        <div class="form-wrapper" style="max-width:100%;border-radius:1.25rem;box-shadow:0 30px 70px rgba(0,0,0,0.45),0 0 0 1px rgba(0,0,0,0.06);background:#fff;padding:2.2rem 2.4rem;">

                            <!-- Login Panel -->
                            <div class="form-panel login-panel">
                                <!-- Card Header -->
                                <div style="margin-bottom:1.5rem;">
                                    <h2 style="font-size:1.6rem;font-weight:800;color:#1e293b;margin:0 0 4px;font-family:'Inter',sans-serif;">Welcome to <span style="color:#2563eb;">Eurotaxi</span></h2>
                                    <p style="color:#64748b;font-size:0.85rem;margin:0 0 1.2rem;font-family:'Inter',sans-serif;">Fleet Management System</p>
                                    <!-- Divider -->
                                    <hr style="border:none;border-top:1.5px solid #e2e8f0;margin-bottom:1rem;">
                                    <!-- Role Badge -->
                                    <p style="color:#2563eb;font-size:0.78rem;font-weight:700;letter-spacing:0.15em;text-transform:uppercase;font-family:'Inter',sans-serif;margin:0;">FLEET ADMINISTRATOR</p>
                                </div>

                                <?php if($errors->any()): ?>
                                    <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-red-700 text-sm">
                                        <?php echo e($errors->first()); ?>

                                    </div>
                                <?php endif; ?>

                                <?php if(session('info')): ?>
                                    <div class="mb-4 p-3 bg-blue-50 border border-blue-200 rounded-lg text-blue-700 text-sm animate-pulse">
                                        <i class="fas fa-info-circle mr-2"></i>
                                        <?php echo e(session('info')); ?>

                                    </div>
                                <?php endif; ?>

                                <?php if(session('success')): ?>
                                    <div class="mb-4 p-3 bg-green-50 border border-green-200 rounded-lg text-green-700 text-sm">
                                        <i class="fas fa-check-circle mr-2"></i>
                                        <?php echo e(session('success')); ?>

                                    </div>
                                <?php endif; ?>

                                <form id="loginForm" method="POST" action="<?php echo e(route('login.submit')); ?>">
                                    <?php echo csrf_field(); ?>
                                    <div class="input-group">
                                        <div class="input-icon-wrapper">
                                            <i class="fas fa-envelope"></i>
                                            <input type="text" name="email" id="loginEmail" placeholder="Email or Username"
                                                value="<?php echo e(old('email')); ?>" required>
                                        </div>
                                    </div>

                                    <div class="pw-group">
                                        <i class="fas fa-lock pw-icon"></i>
                                        <input type="password" name="password" id="loginPassword" placeholder="Password" required>
                                        <button type="button" class="toggle-password" onclick="togglePassword('loginPassword', this)" tabindex="-1">
                                            <i class="fas fa-eye-slash"></i>
                                        </button>
                                    </div>

                                    <div class="flex items-center justify-between mb-4">
                                        <label class="flex items-center">
                                            <input type="checkbox" name="remember" id="remember" class="mr-2">
                                            <span class="text-gray-600 text-sm">Remember me</span>
                                        </label>
                                        <button type="button" onclick="setState('forgot')"
                                            class="text-blue-600 hover:underline text-sm">Forgot password?</button>
                                    </div>

                                    <button type="submit" class="btn-primary" style="letter-spacing:0.08em;font-size:0.95rem;">
                                        SIGN IN
                                    </button>
                                </form>


                            </div>

                            <!-- Forgot Password Panel -->
                            <div class="form-panel forgot-panel">
                                <div class="mb-4" id="forgotBackButton" style="display: none !important;">
                                    <button type="button" onclick="backToRecoveryOptions()"
                                        class="flex items-center text-gray-600 hover:text-blue-600 transition-colors">
                                        <i class="fas fa-arrow-left mr-2"></i> Back
                                    </button>
                                </div>

                                <div class="text-center mb-5">
                                    <h2 class="text-2xl font-bold text-gray-800 mb-1">Reset Password</h2>
                                    <p class="text-gray-600">Choose your recovery method</p>
                                </div>

                                <div id="recoveryOptions" class="forgot-options">
                                    <div class="forgot-option" onclick="selectRecoveryMethod('email')">
                                        <i class="fas fa-envelope text-2xl text-blue-600 mb-2"></i>
                                        <p class="text-sm font-semibold">Email Verification</p>
                                    </div>
                                    <div class="forgot-option" onclick="selectRecoveryMethod('phone')">
                                        <i class="fas fa-mobile-alt text-2xl text-green-600 mb-2"></i>
                                        <p class="text-sm font-semibold">Phone OTP</p>
                                    </div>
                                </div>

                                <form id="emailResetForm" style="display:none;">
                                    <?php echo csrf_field(); ?>
                                    <div class="input-group">
                                        <div class="input-icon-wrapper">
                                            <i class="fas fa-envelope"></i>
                                            <input type="email" id="resetEmail" placeholder="Enter your email address"
                                                required>
                                        </div>
                                    </div>
                                    <button type="submit" class="btn-primary">
                                        <i class="fas fa-paper-plane mr-2"></i> Send Verification Link
                                    </button>
                                </form>

                                <form id="phoneResetForm" style="display:none;">
                                    <?php echo csrf_field(); ?>
                                    <div class="input-group">
                                        <div class="input-icon-wrapper">
                                            <i class="fas fa-phone"></i>
                                            <input type="tel" id="resetPhone" placeholder="Enter your phone number (09XXXXXXXXX)"
                                                maxlength="11" required pattern="[0-9]*" inputmode="numeric">
                                        </div>
                                        <div id="resetPhoneError" class="text-red-600 text-[10px] leading-tight font-medium hidden mt-1"></div>
                                    </div>
                                    <button type="submit" class="btn-primary">
                                        <i class="fas fa-paper-plane mr-2"></i> Send OTP
                                    </button>
                                </form>

                                <div id="otpSection" style="display:none;">
                                    <h4 class="text-center font-semibold mb-2">Verification Required</h4>
                                    <p class="text-center text-gray-600 mb-4">Enter 6-digit code sent to your phone</p>
                                    <div class="otp-inputs">
                                        <?php for($i = 0; $i < 6; $i++): ?>
                                            <input type="text" class="otp-input" maxlength="1" data-index="<?php echo e($i); ?>">
                                        <?php endfor; ?>
                                    </div>
                                    <button type="button" onclick="verifyOTP()" class="btn-primary mb-4">
                                        <i class="fas fa-check mr-2"></i> Verify OTP
                                    </button>
                                    <div class="text-center">
                                        <button type="button" id="resendBtn" class="resend-btn" onclick="resendOTP()"
                                            disabled>
                                            Resend OTP (<span id="countdown">120</span>s)
                                        </button>
                                    </div>
                                </div>

                                <form id="newPasswordForm" style="display:none;">
                                    <?php echo csrf_field(); ?>
                                    <p class="text-center text-green-600 mb-4 text-sm">✓ Verification successful! Set your new password.</p>
                                    
                                    <div class="input-group" style="margin-bottom: 0.3rem;">
                                        <div class="pw-group" style="margin-bottom: 0.1rem;">
                                            <i class="fas fa-lock pw-icon"></i>
                                            <input type="password" id="newPassword" placeholder="New password" required minlength="6">
                                            <button type="button" class="toggle-password" onclick="togglePassword('newPassword', this)" tabindex="-1">
                                                <i class="fas fa-eye-slash"></i>
                                            </button>
                                        </div>
                                        <div class="password-strength-container hidden" id="resetPwStrengthContainer">
                                            <div style="width: 100%; height: 3px; background: #e5e7eb; border-radius: 2px; overflow: hidden; display: flex;">
                                                <div id="resetPwStrengthBar" style="width: 0%; height: 100%; transition: all 0.3s ease; border-radius: 2px;"></div>
                                            </div>
                                            <div style="display: flex; justify-content: space-between; margin-top: 1px;">
                                                <div id="resetPwError" class="text-red-600 text-[10px] leading-tight font-medium hidden mt-0.5" style="max-width: 80%;"></div>
                                                <div id="resetPwStrengthText" class="text-[10px] font-medium leading-tight mt-0.5 text-right w-full"></div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="input-group">
                                        <div class="pw-group" style="margin-bottom: 0.1rem;">
                                            <i class="fas fa-lock pw-icon"></i>
                                            <input type="password" id="confirmNewPassword" placeholder="Confirm password" required>
                                            <button type="button" class="toggle-password" onclick="togglePassword('confirmNewPassword', this)" tabindex="-1">
                                                <i class="fas fa-eye-slash"></i>
                                            </button>
                                        </div>
                                        <div id="resetConfirmError" class="text-red-600 text-[10px] leading-tight font-medium hidden mt-0.5"></div>
                                    </div>

                                    <button type="submit" class="btn-primary mt-2">
                                        <i class="fas fa-save mr-2"></i> Update Password
                                    </button>
                                </form>

                                <div class="text-center mt-4">
                                    <p class="text-gray-600 text-sm">
                                        Remember your password?
                                        <button type="button" onclick="setState('login')"
                                            class="text-blue-600 font-semibold hover:underline">
                                            Back to Login
                                        </button>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Back Face - Register -->
                    <div class="back-face" style="grid-area:1/1;width:100%;backface-visibility:hidden;transform:rotateY(180deg);">
                        <div class="form-wrapper" style="max-width:100%;box-shadow:0 25px 60px rgba(0,0,0,0.5),0 0 0 1px rgba(255,255,255,0.08);background:rgba(255,255,255,0.97);">
                            <div class="form-panel register-panel">
                                <div class="text-center mb-3">
                                    <h2 class="text-xl font-bold text-gray-800 mb-0.5">Create Account</h2>
                                    <p class="text-gray-500 text-sm">Join Eurotaxisystem today</p>
                                </div>

                                <form id="registerForm" method="POST" action="<?php echo e(route('register')); ?>">
                                    <?php echo csrf_field(); ?>
                                    <!-- Row 1: First Name | Last Name -->
                                    <div class="grid mb-1" style="grid-template-columns:1fr 1fr; gap:0.5rem;">
                                        <div class="input-group" style="margin-bottom:0;">
                                            <div class="input-icon-wrapper">
                                                <i class="fas fa-user"></i>
                                                <input type="text" name="first_name" id="firstName" placeholder="First name"
                                                    maxlength="25" required>
                                            </div>
                                            <div id="firstNameError" class="text-red-600 text-[10px] leading-tight font-medium hidden"></div>
                                        </div>
                                        <div class="input-group" style="margin-bottom:0;">
                                            <div class="input-icon-wrapper">
                                                <i class="fas fa-user"></i>
                                                <input type="text" name="last_name" id="lastName" placeholder="Last name"
                                                    maxlength="25" required>
                                            </div>
                                            <div id="lastNameError" class="text-red-600 text-[10px] leading-tight font-medium hidden"></div>
                                        </div>
                                    </div>

                                    <!-- Row 2: Middle Name | Suffix -->
                                    <div class="grid mb-1" style="grid-template-columns:1fr auto; gap:0.4rem; align-items:start;">
                                        <div class="input-group" style="margin-bottom:0;">
                                            <div class="input-icon-wrapper">
                                                <i class="fas fa-user"></i>
                                                <input type="text" name="middle_name" id="middleName" placeholder="Middle name (optional)"
                                                    maxlength="25">
                                            </div>
                                            <div id="middleNameError" class="text-red-600 text-[10px] leading-tight font-medium hidden"></div>
                                        </div>
                                        <div class="input-group" style="margin-bottom:0; width:90px;">
                                            <select name="suffix" id="regSuffix"
                                                onchange="this.style.color = this.value ? '#1f2937' : '#9ca3af';"
                                                style="text-align: center; padding-left: 0.5rem; width: 100%; border: 2px solid #e5e7eb; border-radius: 0.5rem; font-size: 0.8rem; background: #ffffff; color: #9ca3af; padding-top: 0.45rem; padding-bottom: 0.45rem;">
                                                <option value="" disabled hidden selected style="color: #9ca3af;">Suffix</option>
                                                <option value="N/A" style="color: #1f2937;">N/A</option>
                                                <option value="Jr." style="color: #1f2937;">Jr.</option>
                                                <option value="Sr." style="color: #1f2937;">Sr.</option>
                                                <option value="II" style="color: #1f2937;">II</option>
                                                <option value="III" style="color: #1f2937;">III</option>
                                                <option value="IV" style="color: #1f2937;">IV</option>
                                                <option value="V" style="color: #1f2937;">V</option>
                                            </select>
                                        </div>
                                    </div>

                                    <!-- Role -->
                                    <div class="input-group">
                                        <div class="input-icon-wrapper">
                                            <i class="fas fa-user-tag"></i>
                                            <select name="role" id="regRole" required>
                                                <option value="">Select Role</option>
                                                <option value="secretary">Secretary</option>
                                                <option value="manager">Manager</option>
                                                <option value="dispatcher">Dispatcher</option>
                                            </select>
                                        </div>
                                    </div>

                                    <!-- PH Phone Number -->
                                    <div class="input-group">
                                        <div class="ph-phone-wrapper" style="padding: 0.45rem 0.5rem;">
                                            <span class="ph-prefix" style="color: #6b7280; margin-right: 0.4rem; font-size: 0.8rem;"><i class="fas fa-phone-alt"></i> (+63)</span>
                                            <input type="tel" name="phone_number" id="phoneNumber"
                                                placeholder="9XXXXXXXXX" maxlength="10" required
                                                pattern="9[0-9]{9}" inputmode="numeric"
                                                title="Must start with 9 and contain exactly 10 digits"
                                                style="border: none; outline: none; flex: 1; padding: 0; background: transparent; font-size: 0.8rem; color: #1f2937;">
                                        </div>
                                        <div id="phoneError" class="text-red-600 text-[10px] leading-tight font-medium hidden"></div>
                                    </div>


                                    <!-- Email -->
                                    <div class="input-group">
                                        <div class="input-icon-wrapper">
                                            <i class="fas fa-envelope"></i>
                                            <input type="email" name="email" id="regEmail" placeholder="Gmail (e.g. you@gmail.com)"
                                                required>
                                        </div>
                                        <div id="regEmailError" class="text-red-600 text-[10px] leading-tight font-medium hidden"></div>
                                    </div>

                                    <div class="input-group" style="margin-bottom: 0.3rem;">
                                        <div class="pw-group" style="margin-bottom: 0.1rem;">
                                            <i class="fas fa-lock pw-icon"></i>
                                            <input type="password" name="password" id="regPassword" placeholder="Password" required minlength="6">
                                            <button type="button" class="toggle-password" onclick="togglePassword('regPassword', this)" tabindex="-1">
                                                <i class="fas fa-eye-slash"></i>
                                            </button>
                                        </div>
                                        <div class="password-strength-container hidden" id="pwStrengthContainer">
                                            <div style="width: 100%; height: 3px; background: #e5e7eb; border-radius: 2px; overflow: hidden; display: flex;">
                                                <div id="pwStrengthBar" style="width: 0%; height: 100%; transition: all 0.3s ease; border-radius: 2px;"></div>
                                            </div>
                                            <div style="display: flex; justify-content: space-between; margin-top: 1px;">
                                                <div id="regPasswordError" class="text-red-600 text-[10px] leading-tight font-medium hidden mt-0.5" style="max-width: 80%;"></div>
                                                <div id="pwStrengthText" class="text-[10px] font-medium leading-tight mt-0.5 text-right w-full"></div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="input-group">
                                        <div class="pw-group" style="margin-bottom: 0.1rem;">
                                            <i class="fas fa-lock pw-icon"></i>
                                            <input type="password" name="password_confirmation" id="regPasswordConfirm" placeholder="Confirm password" required>
                                            <button type="button" class="toggle-password" onclick="togglePassword('regPasswordConfirm', this)" tabindex="-1">
                                                <i class="fas fa-eye-slash"></i>
                                            </button>
                                        </div>
                                        <div id="regPasswordConfirmError" class="text-red-600 text-[10px] leading-tight font-medium hidden mt-0.5"></div>
                                    </div>

                                    <button type="submit" id="createAccountBtn" class="btn-secondary">
                                        <i class="fas fa-user-plus mr-2"></i> Create Account
                                    </button>
                                </form>

                                
                                <div id="regOtpSection" style="display:none;">
                                    <div class="text-center mb-4">
                                        <div style="width:56px;height:56px;background:linear-gradient(135deg,#3b82f6,#1d4ed8);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 12px;">
                                            <i class="fas fa-envelope-open-text text-white text-xl"></i>
                                        </div>
                                        <h3 class="font-bold text-gray-800 text-lg mb-1">Verify Your Email</h3>
                                        <p class="text-gray-500 text-sm">We sent a 6-digit code to</p>
                                        <p class="text-blue-600 font-semibold text-sm" id="regOtpEmailDisplay"></p>
                                    </div>

                                    <div class="otp-inputs">
                                        <?php for($i = 0; $i < 6; $i++): ?>
                                            <input type="text" class="otp-input reg-otp-input" maxlength="1" data-index="<?php echo e($i); ?>">
                                        <?php endfor; ?>
                                    </div>

                                    <button type="button" onclick="verifyRegOTP()" id="regVerifyBtn" class="btn-primary mb-3">
                                        <i class="fas fa-check mr-2"></i> Verify & Activate
                                    </button>

                                    <div class="text-center">
                                        <button type="button" id="regResendBtn" class="resend-btn" onclick="resendRegOTP()" disabled>
                                            Resend Code (<span id="regCountdown">120</span>s)
                                        </button>
                                    </div>

                                    <p class="text-center text-gray-500 text-xs mt-3">
                                        Wrong email?
                                        <button type="button" onclick="cancelRegOtp()" class="text-blue-600 font-semibold hover:underline">Go back</button>
                                    </p>
                                </div>
                                
                                <div class="text-center mt-3">
                                    <p class="text-gray-600 text-sm">
                                        Already have an account?
                                        <button type="button" onclick="setState('login')"
                                            class="text-blue-600 font-semibold hover:underline">
                                            Sign In
                                        </button>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <!-- MFA Verification Modal -->
    <div id="mfaModal" class="mfa-modal-overlay">
        <div class="mfa-modal-content">
            <!-- Step 1: Selection -->
            <div id="mfaStepSelect">
                <div class="text-center mb-6">
                    <div class="w-16 h-16 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-shield-alt text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900">Verify Your Identity</h3>
                    <p class="text-gray-500 mt-2">A new device was detected. Choose where to receive your verification code.</p>
                </div>

                <div class="space-y-3 mb-6">
                    <div onclick="sendMfaOtp('email')" class="mfa-method-card p-4 rounded-xl flex items-center gap-4">
                        <div class="w-10 h-10 bg-blue-50 text-blue-500 rounded-lg flex items-center justify-center">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <div class="flex-1">
                            <p class="font-semibold text-gray-800">Email Address</p>
                            <p class="text-xs text-gray-500" id="mfaEmailMask">••••••••@gmail.com</p>
                        </div>
                        <i class="fas fa-chevron-right text-gray-400"></i>
                    </div>

                    <div onclick="sendMfaOtp('phone')" class="mfa-method-card p-4 rounded-xl flex items-center gap-4">
                        <div class="w-10 h-10 bg-green-50 text-green-500 rounded-lg flex items-center justify-center">
                            <i class="fas fa-phone-alt"></i>
                        </div>
                        <div class="flex-1">
                            <p class="font-semibold text-gray-800">Phone Number</p>
                            <p class="text-xs text-gray-500" id="mfaPhoneMask">+63 •••• ••• ••00</p>
                        </div>
                        <i class="fas fa-chevron-right text-gray-400"></i>
                    </div>
                </div>

                <button onclick="closeMfaModal()" class="w-full text-gray-500 text-sm hover:underline">Cancel login</button>
            </div>

            <!-- Step 2: OTP Entry -->
            <div id="mfaStepOtp" style="display: none;">
                <div class="text-center mb-6">
                    <button onclick="showMfaStep('select')" class="absolute top-6 left-6 text-gray-400 hover:text-gray-600">
                        <i class="fas fa-arrow-left"></i>
                    </button>
                    <div class="w-16 h-16 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-key text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900">Enter Code</h3>
                    <p class="text-gray-500 mt-2">Check your <span id="mfaTargetDesc">email</span> for the 6-digit code.</p>
                </div>

                <div class="flex gap-2 justify-center mb-8">
                    <?php for($i = 0; $i < 6; $i++): ?>
                        <input type="text" class="mfa-otp-input" maxlength="1" data-mfa-index="<?php echo e($i); ?>">
                    <?php endfor; ?>
                </div>

                <button onclick="verifyMfaOtp()" id="mfaVerifyBtn" class="btn-primary w-full py-3">
                    Verify & Sign In
                </button>

                <div class="text-center mt-6">
                    <p id="mfaTimer" class="text-xs text-gray-400">Code expires in 10:00</p>
                    <button onclick="sendMfaOtp(mfaMethod)" id="mfaResendBtn" class="mt-2 text-sm text-blue-600 font-semibold hover:underline" style="display: none;">
                        Resend Code
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Force Password Change Modal -->
    <div id="forcePasswordModal" class="mfa-modal-overlay">
        <div class="mfa-modal-content relative" style="max-width: 400px; padding: 0; overflow: hidden;">
            <button onclick="closeForcePasswordModal()" type="button" class="absolute top-3 right-3 text-white/80 hover:text-white transition-colors p-2 z-10" aria-label="Close">
                <i class="fas fa-times text-lg"></i>
            </button>
            <div class="bg-amber-500 p-4 sm:p-5 text-center relative">
                <div class="w-12 h-12 bg-white rounded-full flex items-center justify-center mx-auto mb-2 shadow-inner">
                    <i class="fas fa-shield-alt text-2xl text-amber-500"></i>
                </div>
                <h3 class="text-lg sm:text-xl font-bold text-white leading-tight">Action Required</h3>
                <p class="text-amber-50 mt-1 text-[11px] sm:text-xs" id="forcePasswordMessage">Please update your temporary password to continue.</p>
            </div>
            
            <div class="p-4 sm:p-5">
                <form id="forcePasswordForm" onsubmit="submitForcePassword(event)">
                    <div class="space-y-3">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Temporary Password</label>
                            <div class="input-group" style="margin-bottom:0;">
                                <div class="pw-group" style="margin-bottom:0;">
                                    <i class="fas fa-key pw-icon"></i>
                                    <input type="password" id="force-current-pwd" required placeholder="Enter current password">
                                    <button type="button" class="toggle-password" onclick="togglePassword('force-current-pwd', this)" tabindex="-1">
                                        <i class="fas fa-eye-slash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">New Password</label>
                            <div class="input-group" style="margin-bottom:0;">
                                <div class="pw-group" style="margin-bottom:0;">
                                    <i class="fas fa-lock pw-icon"></i>
                                    <input type="password" id="force-new-pwd" required minlength="8" placeholder="Enter new password">
                                    <button type="button" class="toggle-password" onclick="togglePassword('force-new-pwd', this)" tabindex="-1">
                                        <i class="fas fa-eye-slash"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="password-strength-container hidden mt-1" id="forcePwStrengthContainer">
                                <div style="width: 100%; height: 3px; background: #e5e7eb; border-radius: 2px; overflow: hidden; display: flex;">
                                    <div id="forcePwStrengthBar" style="width: 0%; height: 100%; transition: all 0.3s ease; border-radius: 2px;"></div>
                                </div>
                                <div style="display: flex; justify-content: space-between; margin-top: 2px;">
                                    <div id="forcePwStrengthText" class="text-[10px] font-medium leading-tight w-full text-right"></div>
                                </div>
                            </div>
                            <ul id="forcePwCriteria" class="text-[10px] text-gray-500 mt-1.5 space-y-0.5">
                                <li id="req-length" class="flex items-center gap-1.5 transition-colors duration-200"><i class="fas fa-circle text-[6px]"></i> <span>Minimum 8 characters</span></li>
                                <li id="req-upper" class="flex items-center gap-1.5 transition-colors duration-200"><i class="fas fa-circle text-[6px]"></i> <span>At least 1 uppercase</span></li>
                                <li id="req-number" class="flex items-center gap-1.5 transition-colors duration-200"><i class="fas fa-circle text-[6px]"></i> <span>At least 1 number</span></li>
                                <li id="req-special" class="flex items-center gap-1.5 transition-colors duration-200"><i class="fas fa-circle text-[6px]"></i> <span>At least 1 special character</span></li>
                            </ul>
                        </div>
                        
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Confirm New Password</label>
                            <div class="input-group" style="margin-bottom:0;">
                                <div class="pw-group" style="margin-bottom:0;">
                                    <i class="fas fa-lock pw-icon"></i>
                                    <input type="password" id="force-confirm-pwd" required minlength="8" placeholder="Confirm new password">
                                    <button type="button" class="toggle-password" onclick="togglePassword('force-confirm-pwd', this)" tabindex="-1">
                                        <i class="fas fa-eye-slash"></i>
                                    </button>
                                </div>
                            </div>
                            <div id="forceConfirmError" class="text-red-600 text-[10px] leading-tight font-medium hidden mt-1">Passwords do not match.</div>
                        </div>
                    </div>
                    
                    <div id="force-pwd-error" class="hidden mt-4 p-3 bg-red-50 border border-red-200 text-red-700 text-xs rounded-lg text-center font-medium"></div>
                    
                    <div class="mt-6">
                        <button type="submit" id="btn-force-pwd" class="btn-primary w-full py-2.5 flex justify-center items-center gap-2" style="background-color: #f59e0b; border-color: #f59e0b;">
                            <i class="fas fa-check"></i> Update Password & Sign In
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Account Disabled Modal -->
    <div id="disabledModal" class="mfa-modal-overlay">
        <div class="mfa-modal-content" style="max-width: 440px; text-align: center; border: 1px solid #fee2e2;">
            <div style="margin-bottom: 1.5rem; display: flex; justify-content: center;">
                <div style="background: #fef2f2; width: 80px; height: 80px; border-radius: 50%; display: flex; align-items: center; justify-content: center; border: 4px solid #fee2e2;">
                    <i class="fas fa-user-slash" style="font-size: 2.2rem; color: #dc2626;"></i>
                </div>
            </div>
            
            <h2 style="font-size: 1.5rem; font-weight: 800; color: #991b1b; margin-bottom: 0.5rem;">Account Suspended</h2>
            <p style="color: #4b5563; font-size: 0.95rem; line-height: 1.6; margin-bottom: 1.5rem;">
                Your access to the system has been temporarily disabled.
            </p>

            <div style="background: #f8fafc; border: 1.5px dashed #e2e8f0; border-radius: 1rem; padding: 1.25rem; margin-bottom: 1.5rem; text-align: left;">
                <p style="font-size: 0.75rem; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.5rem;">Note from Administrator:</p>
                <p id="disabledReasonText" style="color: #1e293b; font-size: 0.9rem; font-weight: 500; font-style: italic;">
                    Your account has been temporarily disabled by the Owner/Super Admin.
                </p>
            </div>

            <button type="button" onclick="closeDisabledModal()" class="btn-primary" style="background: #1e293b; border: none; margin-bottom: 0; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
                GOT IT
            </button>
            
            <p style="margin-top: 1.25rem; color: #94a3b8; font-size: 0.75rem;">
                If you believe this is a mistake, please contact <strong>Robert Garcia</strong>.
            </p>
        </div>
    </div>

    <script>
        let currentState = 'login';
        let mfaMethod = '';
        let mfaCountdownInterval;

        function startMfaCountdown() {
            if (mfaCountdownInterval) clearInterval(mfaCountdownInterval);
            let seconds = 600; // 10 minutes
            const timerDisplay = document.getElementById('mfaTimer');
            const resendBtn = document.getElementById('mfaResendBtn');
            
            resendBtn.style.display = 'none';
            timerDisplay.style.display = 'block';

            mfaCountdownInterval = setInterval(() => {
                seconds--;
                const mins = Math.floor(seconds / 60);
                const secs = seconds % 60;
                timerDisplay.textContent = `Code expires in ${mins}:${secs.toString().padStart(2, '0')}`;

                if (seconds <= 0) {
                    clearInterval(mfaCountdownInterval);
                    timerDisplay.textContent = 'Code has expired.';
                    resendBtn.style.display = 'inline-block';
                }
            }, 1000);
        }

        function showDisabledModal(reason) {
            const modal = document.getElementById('disabledModal');
            if (reason) {
                document.getElementById('disabledReasonText').textContent = reason;
            }
            modal.style.display = 'flex';
            setTimeout(() => modal.classList.add('show'), 10);
        }

        function closeDisabledModal() {
            const modal = document.getElementById('disabledModal');
            modal.classList.remove('show');
            setTimeout(() => {
                modal.style.display = 'none';
            }, 300);
        }

        // Force Password Modal Logic
        function showForcePasswordModal(message) {
            const modal = document.getElementById('forcePasswordModal');
            if (message) {
                document.getElementById('forcePasswordMessage').textContent = message;
            }
            modal.style.display = 'flex';
            setTimeout(() => modal.classList.add('show'), 10);
        }

        function closeForcePasswordModal() {
            const modal = document.getElementById('forcePasswordModal');
            modal.classList.remove('show');
            setTimeout(() => {
                modal.style.display = 'none';
                document.getElementById('forcePasswordForm').reset();
            }, 300);
        }

        async function submitForcePassword(e) {
            e.preventDefault();
            
            const currentPwd = document.getElementById('force-current-pwd').value;
            const newPwd = document.getElementById('force-new-pwd').value;
            const confirmPwd = document.getElementById('force-confirm-pwd').value;
            const errBox = document.getElementById('force-pwd-error');
            const btn = document.getElementById('btn-force-pwd');
            
            errBox.classList.add('hidden');
            
            if (newPwd !== confirmPwd) {
                errBox.textContent = 'New passwords do not match.';
                errBox.classList.remove('hidden');
                return;
            }
            
            if (!/(?=.*[A-Z])(?=.*[0-9])/.test(newPwd)) {
                errBox.textContent = 'Password must contain at least 1 uppercase letter and 1 number.';
                errBox.classList.remove('hidden');
                return;
            }

            const originalText = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Updating...';
            btn.disabled = true;

            try {
                const response = await fetch('<?php echo e(route("auth.force-change-password.update")); ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        current_password: currentPwd,
                        new_password: newPwd,
                        new_password_confirmation: confirmPwd
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showToast('Password updated successfully! Welcome to Eurotaxi.', 'success');
                    setTimeout(() => {
                        window.location.href = data.redirect;
                    }, 1000);
                } else {
                    errBox.textContent = data.message || 'Validation failed.';
                    if(data.errors) {
                        errBox.textContent = Object.values(data.errors)[0][0];
                    }
                    errBox.classList.remove('hidden');
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                }
            } catch (err) {
                errBox.textContent = 'A network error occurred. Please try again.';
                errBox.classList.remove('hidden');
                btn.innerHTML = originalText;
                btn.disabled = false;
            }
        }

        // Added MFA Functions
        function showMfaModal(data) {
            const modal = document.getElementById('mfaModal');
            document.getElementById('mfaEmailMask').textContent = maskEmail(data.email);
            document.getElementById('mfaPhoneMask').textContent = maskPhone(data.phone || '');
            
            showMfaStep('select');
            modal.style.display = 'flex';
            setTimeout(() => modal.classList.add('show'), 10);
        }

        function closeMfaModal() {
            const modal = document.getElementById('mfaModal');
            modal.classList.remove('show');
            setTimeout(() => modal.style.display = 'none', 300);
        }

        function showMfaStep(step) {
            document.getElementById('mfaStepSelect').style.display = step === 'select' ? 'block' : 'none';
            document.getElementById('mfaStepOtp').style.display = step === 'otp' ? 'block' : 'none';
            if (step === 'otp') {
                document.querySelectorAll('.mfa-otp-input').forEach(i => i.value = '');
                setTimeout(() => document.querySelector('.mfa-otp-input[data-mfa-index="0"]').focus(), 100);
            }
        }

        function maskEmail(email) {
            if (!email) return 'No email available';
            const parts = email.split('@');
            const name = parts[0];
            return name.substring(0, 2) + "••••••••@" + parts[1];
        }

        function maskPhone(phone) {
            if (!phone) return 'No phone available';
            return "+63 •••• ••• ••" + phone.slice(-2);
        }

        function sendMfaOtp(method) {
            mfaMethod = method;
            document.getElementById('mfaTargetDesc').textContent = method;
            
            const btn = typeof event !== 'undefined' && event ? event.currentTarget : null;
            let originalHTML = '';
            if (btn) {
                originalHTML = btn.innerHTML;
                btn.style.pointerEvents = 'none';
                btn.innerHTML += ' <i class="fas fa-spinner fa-spin ml-auto"></i>';
            }

            fetch('<?php echo e(route("login.mfa.send")); ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ method: method })
            })
            .then(async res => {
                if (res.status === 419) {
                    throw new Error('Session expired. Page will refresh.');
                }
                return res.json();
            })
            .then(data => {
                if (data.success) {
                    showToast(data.message, 'success');
                    showMfaStep('otp');
                    startMfaCountdown();
                } else {
                    showToast(data.message, 'error');
                }
            })
            .catch(error => {
                showToast(error.message || 'Failed to send code.', 'error');
                if (error.message.includes('Session expired')) {
                    setTimeout(() => window.location.reload(), 2000);
                }
            })
            .finally(() => {
                if (btn) {
                    btn.style.pointerEvents = 'auto';
                    btn.innerHTML = originalHTML;
                }
            });
        }

        function verifyMfaOtp() {
            const inputs = document.querySelectorAll('.mfa-otp-input');
            let otp = '';
            inputs.forEach(i => otp += i.value);

            if (otp.length !== 6) {
                showToast('Please enter the 6-digit code.', 'error');
                return;
            }

            const btn = document.getElementById('mfaVerifyBtn');
            const originalText = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Verifying...';

            fetch('<?php echo e(route("login.mfa.verify")); ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ otp: otp })
            })
            .then(async res => {
                if (res.status === 419) {
                    throw new Error('Session expired. Page will refresh.');
                }
                return res.json();
            })
            .then(data => {
                if (data.force_password_change) {
                    closeMfaModal();
                    showForcePasswordModal(data.message);
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                } else if (data.success) {
                    showToast('Identity verified! Signing in...', 'success');
                    setTimeout(() => {
                        window.location.href = data.redirect;
                    }, 1000);
                } else {
                    showToast(data.message, 'error');
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                }
            })
            .catch(error => {
                showToast(error.message || 'Verification failed.', 'error');
                btn.disabled = false;
                btn.innerHTML = originalText;
                if (error.message.includes('Session expired')) {
                    setTimeout(() => window.location.reload(), 2000);
                }
            });
        }

        // Auto-advance OTP inputs
        document.querySelectorAll('.mfa-otp-input').forEach((input, index) => {
            input.addEventListener('input', function() {
                this.value = this.value.replace(/[^0-9]/g, '');
                if (this.value.length === 1) {
                    const next = document.querySelector(`.mfa-otp-input[data-mfa-index="${index + 1}"]`);
                    if (next) next.focus();
                }
            });
            input.addEventListener('keydown', function(e) {
                if (e.key === 'Backspace' && this.value === '') {
                    const prev = document.querySelector(`.mfa-otp-input[data-mfa-index="${index - 1}"]`);
                    if (prev) prev.focus();
                }
            });
        });

        // Remember Me functionality
        function saveRememberMe() {
            const rememberCheckbox = document.getElementById('remember');
            const emailInput = document.getElementById('loginEmail');
            const passwordInput = document.getElementById('loginPassword');
            
            if (rememberCheckbox.checked && emailInput.value) {
                localStorage.setItem('rememberedEmail', emailInput.value);
                localStorage.setItem('rememberedPassword', passwordInput.value);
                localStorage.setItem('rememberMeChecked', 'true');
            } else {
                localStorage.removeItem('rememberedEmail');
                localStorage.removeItem('rememberedPassword');
                localStorage.removeItem('rememberMeChecked');
            }
        }

        function loadRememberMe() {
            const rememberedEmail = localStorage.getItem('rememberedEmail');
            const rememberedPassword = localStorage.getItem('rememberedPassword');
            const rememberMeChecked = localStorage.getItem('rememberMeChecked');
            const emailInput = document.getElementById('loginEmail');
            const passwordInput = document.getElementById('loginPassword');
            const rememberCheckbox = document.getElementById('remember');
            
            if (rememberedEmail && rememberMeChecked === 'true') {
                emailInput.value = rememberedEmail;
                if (rememberedPassword) {
                    passwordInput.value = rememberedPassword;
                }
                rememberCheckbox.checked = true;
            }
        }

        function togglePassword(inputId, btn) {
            const input = document.getElementById(inputId);
            const icon = btn.querySelector('i');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            }
        }

        function setState(state) {
            currentState = state;
            const flipper = document.getElementById('flipper');
            flipper.className = 'flipper state-' + state;
            
            if (state === 'login' || state === 'forgot') {
                backToRecoveryOptions();
            }
            
            lucide && lucide.createIcons && lucide.createIcons();
        }

        function goBack() {
            setState('login');
        }

        function selectRecoveryMethod(method) {
            document.getElementById('recoveryOptions').style.display = 'none';
            document.getElementById('forgotBackButton').style.setProperty('display', 'block', 'important');
            // Clear inputs
            document.querySelectorAll('.otp-input').forEach(i => i.value = '');
            if (method === 'email') {
                document.getElementById('emailResetForm').style.display = 'block';
                document.getElementById('phoneResetForm').style.display = 'none';
            } else {
                document.getElementById('phoneResetForm').style.display = 'block';
                document.getElementById('emailResetForm').style.display = 'none';
            }
        }

        function backToRecoveryOptions() {
            document.getElementById('recoveryOptions').style.display = 'flex';
            document.getElementById('forgotBackButton').style.setProperty('display', 'none', 'important');
            document.getElementById('emailResetForm').style.display = 'none';
            document.getElementById('phoneResetForm').style.display = 'none';
            document.getElementById('otpSection').style.display = 'none';
            document.getElementById('newPasswordForm').style.display = 'none';
            // Clear inputs and reset button
            document.querySelectorAll('.otp-input').forEach(i => i.value = '');
            const verifyBtn = document.querySelector('#otpSection .btn-primary');
            if(verifyBtn) verifyBtn.innerHTML = '<i class="fas fa-check mr-2"></i> Verify OTP';
            if(verifyBtn) verifyBtn.disabled = false;
        }


        let countdownInterval;
        function startCountdown() {
            if (countdownInterval) clearInterval(countdownInterval);
            let seconds = 120;
            const btn = document.getElementById('resendBtn');
            const span = document.getElementById('countdown');
            btn.disabled = true;
            btn.classList.add('opacity-50', 'cursor-not-allowed');
            
            countdownInterval = setInterval(() => {
                seconds--;
                span.textContent = seconds;
                btn.innerHTML = `Resend OTP (${seconds}s)`;
                
                if (seconds <= 0) {
                    clearInterval(countdownInterval);
                    btn.disabled = false;
                    btn.classList.remove('opacity-50', 'cursor-not-allowed');
                    btn.innerHTML = 'Resend OTP';
                }
            }, 1000);
        }

        function resendOTP() {
            showToast('OTP resent!', 'success');
            startCountdown();
        }

        function showToast(message, type = 'success') {
            const toast = document.getElementById('messageToast');
            toast.textContent = message;
            toast.className = 'message-toast ' + type + ' show';
            setTimeout(() => { toast.classList.remove('show'); }, 3000);
        }

        // Update username preview based on role and first name
        function updateUsernamePreview() {
            const role = document.getElementById('regRole').value;
            const firstName = document.getElementById('firstName').value;
            const preview = document.getElementById('regUsername'); // Wait, ID might be different
            
            // Check if there's a preview element (some versions might have it)
            const usernamePreviewInput = document.getElementById('usernamePreview');
            
            if (role && firstName) {
                const cleanFirstName = firstName.toLowerCase().replace(/[^a-z]/g, '');
                if (usernamePreviewInput) {
                    usernamePreviewInput.value = role + '-' + cleanFirstName;
                }
            } else {
                if (usernamePreviewInput) {
                    usernamePreviewInput.value = '';
                }
            }
        }

        // OTP input auto-advance
        document.querySelectorAll('.otp-input').forEach((input, index) => {
            input.addEventListener('input', function () {
                if (this.value.length === 1) {
                    const next = document.querySelector(`.otp-input[data-index="${index + 1}"]`);
                    if (next) next.focus();
                }
            });
            input.addEventListener('keydown', function(e) {
                if(e.key === 'Backspace' && this.value === '') {
                    const prev = document.querySelector(`.otp-input[data-index="${index - 1}"]`);
                    if (prev) prev.focus();
                }
            });
        });

        // ─── Forgot Password AJAX Handlers ──────────────────
        let currentIdentifier = '';
        let currentMethod = '';

        // Email Reset Form
        const emailResetForm = document.getElementById('emailResetForm');
        if (emailResetForm) {
            emailResetForm.addEventListener('submit', function(e) {
                e.preventDefault();
                const email = document.getElementById('resetEmail').value;
                currentIdentifier = email;
                currentMethod = 'email';
                
                const btn = this.querySelector('button[type="submit"]');
                const originalText = btn.innerHTML;
                btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Sending...';

                fetch('<?php echo e(route("forgot-password.send-otp")); ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ email: email })
                })
                .then(async res => {
                    const data = await res.json();
                    if (res.status === 419) throw new Error('Session expired. Page will refresh.');
                    if (!res.ok) throw new Error(data.message || 'An error occurred. Please try again.');
                    return data;
                })
                .then(data => {
                    if (data.success) {
                        showToast(data.message, 'success');
                        emailResetForm.style.display = 'none';
                        document.getElementById('otpSection').style.display = 'block';
                        startCountdown();
                    } else {
                        showToast(data.message, 'error');
                        btn.disabled = false;
                        btn.innerHTML = originalText;
                    }
                })
                .catch(err => {
                    showToast(err.message, 'error');
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                    if (err.message.includes('Session expired')) setTimeout(() => window.location.reload(), 2000);
                });
            });
        }

        // Phone Reset Form
        const phoneResetForm = document.getElementById('phoneResetForm');
        if (phoneResetForm) {
            phoneResetForm.addEventListener('submit', function(e) {
                e.preventDefault();
                const phone = document.getElementById('resetPhone').value;
                currentIdentifier = phone;
                currentMethod = 'phone';

                const btn = this.querySelector('button[type="submit"]');
                const originalText = btn.innerHTML;
                btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Sending...';

                fetch('<?php echo e(route("forgot-password.send-sms-otp")); ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ phone: phone })
                })
                .then(async res => {
                    const data = await res.json();
                    if (res.status === 419) throw new Error('Session expired. Page will refresh.');
                    if (!res.ok) throw new Error(data.message || 'An error occurred. Please try again.');
                    return data;
                })
                .then(data => {
                    if (data.success) {
                        showToast(data.message, 'success');
                        phoneResetForm.style.display = 'none';
                        document.getElementById('otpSection').style.display = 'block';
                        startCountdown();
                    } else {
                        showToast(data.message, 'error');
                        btn.disabled = false;
                        btn.innerHTML = originalText;
                    }
                })
                .catch(err => {
                    showToast(err.message, 'error');
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                    if (err.message.includes('Session expired')) setTimeout(() => window.location.reload(), 2000);
                });
            });
        }

        // Verify OTP Function (Updated)
        function verifyOTP() {
            const inputs = document.querySelectorAll('.otp-input');
            let otp = '';
            inputs.forEach(i => otp += i.value);
            
            if (otp.length === 6) {
                const btn = document.querySelector('#otpSection .btn-primary');
                const originalText = btn.innerHTML;
                btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Verifying...';

                fetch('<?php echo e(route("forgot-password.verify-otp")); ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ 
                        identifier: currentIdentifier,
                        otp: otp 
                    })
                })
                .then(async res => {
                    const data = await res.json();
                    if (res.status === 419) throw new Error('Session expired. Page will refresh.');
                    return data;
                })
                .then(data => {
                    if (data.success) {
                        showToast(data.message, 'success');
                        document.getElementById('otpSection').style.display = 'none';
                        document.getElementById('newPasswordForm').style.display = 'block';
                    } else {
                        showToast(data.message, 'error');
                        btn.disabled = false;
                        btn.innerHTML = originalText;
                    }
                })
                .catch(err => {
                    showToast(err.message || 'Verification failed. Try again.', 'error');
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                    if (err.message && err.message.includes('Session expired')) setTimeout(() => window.location.reload(), 2000);
                });
            } else {
                showToast('Please enter the 6-digit OTP', 'error');
            }
        }

        // Resend OTP Function (Updated)
        function resendOTP() {
            const btn = document.getElementById('resendBtn');
            const originalText = btn.innerHTML;
            btn.disabled = true;

            const url = currentMethod === 'email' 
                ? '<?php echo e(route("forgot-password.send-otp")); ?>' 
                : '<?php echo e(route("forgot-password.send-sms-otp")); ?>';
            
            const body = currentMethod === 'email' 
                ? { email: currentIdentifier } 
                : { phone: currentIdentifier };

            fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify(body)
            })
            .then(async res => {
                const data = await res.json();
                if (res.status === 419) throw new Error('Session expired. Page will refresh.');
                return data;
            })
            .then(data => {
                if (data.success) {
                    showToast(data.message, 'success');
                    startCountdown();
                } else {
                    showToast(data.message, 'error');
                    btn.disabled = false;
                }
            })
            .catch(err => {
                showToast(err.message || 'Failed to resend. Please try again.', 'error');
                btn.disabled = false;
                if (err.message && err.message.includes('Session expired')) setTimeout(() => window.location.reload(), 2000);
            });
        }

        // New Password Form
        const newPasswordForm = document.getElementById('newPasswordForm');
        if (newPasswordForm) {
            newPasswordForm.addEventListener('submit', function(e) {
                e.preventDefault();
                const password = document.getElementById('newPassword').value;
                const password_confirmation = document.getElementById('confirmNewPassword').value;
                
                const inputs = document.querySelectorAll('.otp-input');
                let otp = '';
                inputs.forEach(i => otp += i.value);

                const btn = this.querySelector('button[type="submit"]');
                const originalText = btn.innerHTML;
                btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Resetting...';

                fetch('<?php echo e(route("forgot-password.reset")); ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ 
                        identifier: currentIdentifier,
                        otp: otp,
                        password: password,
                        password_confirmation: password_confirmation
                    })
                })
                .then(async res => {
                    const data = await res.json();
                    if (res.status === 419) throw new Error('Session expired. Page will refresh.');
                    return data;
                })
                .then(data => {
                    if (data.success) {
                        showToast(data.message, 'success');
                        setTimeout(() => {
                            window.location.reload();
                        }, 2000);
                    } else {
                        showToast(data.message, 'error');
                        btn.disabled = false;
                        btn.innerHTML = originalText;
                    }
                })
                .catch(err => {
                    showToast(err.message || 'Reset failed. Try again.', 'error');
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                    if (err.message && err.message.includes('Session expired')) setTimeout(() => window.location.reload(), 2000);
                });
            });
        }

        // Remember Me event listeners
        document.addEventListener('DOMContentLoaded', function() {
            // Load remembered email on page load
            loadRememberMe();
            
            // Add event listeners for remember me functionality
            const rememberCheckbox = document.getElementById('remember');
            const emailInput = document.getElementById('loginEmail');
            const loginForm = document.getElementById('loginForm');
            
            const firstNameInput = document.getElementById('firstName');
            if (firstNameInput) {
                firstNameInput.addEventListener('keydown', function(e) {
                    // Allow: backspace, delete, tab, escape, enter, arrows
                    if ([8, 46, 9, 27, 13, 37, 38, 39, 40].indexOf(e.keyCode) !== -1) return;
                    // Allow one space (32)
                    if (e.keyCode === 32) {
                        if (this.value.includes(' ') || this.value.length === 0) {
                            e.preventDefault();
                        }
                        return;
                    }
                    // Block anything not a letter (allowing ñ and Ñ)
                    if (e.keyCode < 65 || e.keyCode > 90) {
                        if (e.key === 'ñ' || e.key === 'Ñ') return;
                        e.preventDefault();
                    }
                });

                firstNameInput.addEventListener('paste', function(e) {
                    e.preventDefault();
                    const pasteData = (e.clipboardData || window.clipboardData).getData('text');
                    let cleaned = pasteData.replace(/[^a-zA-ZñÑ ]/g, '');
                    // Force only one space if present
                    if (cleaned.includes(' ')) {
                        const parts = cleaned.split(' ');
                        cleaned = parts[0] + ' ' + parts.slice(1).join('').replace(/ /g, '');
                    }
                    document.execCommand('insertText', false, cleaned);
                });

                firstNameInput.addEventListener('input', function() {
                    let val = this.value.replace(/[^a-zA-ZñÑ ]/g, '');
                    // Force only one space
                    if (val.includes(' ')) {
                        const parts = val.split(' ');
                        val = parts[0] + ' ' + parts.slice(1).join('').replace(/ /g, '');
                    }
                    if (this.value !== val) this.value = val;

                    const errorDiv = document.getElementById('firstNameError');
                    if (val.length > 25) {
                        errorDiv.textContent = 'Maximum of 25 characters only.';
                        errorDiv.classList.remove('hidden');
                        this.classList.add('border-red-500');
                    } else {
                        errorDiv.classList.add('hidden');
                        this.classList.remove('border-red-500');
                    }
                    updateUsernamePreview();
                });

                firstNameInput.addEventListener('blur', function() {
                    const val = this.value.trim();
                    const errorDiv = document.getElementById('firstNameError');
                    if (val.length === 0) return;

                    fetch('<?php echo e(route("check-availability")); ?>', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({ first_name: val })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (!data.available) {
                            errorDiv.textContent = data.message;
                            errorDiv.classList.remove('hidden');
                            this.classList.add('border-red-500');
                        } else {
                            // Don't hide if validateEmailLive already found a format error
                            if (!this.classList.contains('border-red-500') || errorDiv.textContent === 'This first name is already taken.') {
                                errorDiv.classList.add('hidden');
                                this.classList.remove('border-red-500');
                            }
                        }
                    });
                });
            }

            const lastNameInput = document.getElementById('lastName');
            if (lastNameInput) {
                lastNameInput.addEventListener('keydown', function(e) {
                    if ([8, 46, 9, 27, 13, 37, 38, 39, 40].indexOf(e.keyCode) !== -1) return;
                    // Allow one space (32)
                    if (e.keyCode === 32) {
                        if (this.value.includes(' ') || this.value.length === 0) {
                            e.preventDefault();
                        }
                        return;
                    }
                    // Block numbers and special chars (allowing ñ and Ñ)
                    if (e.keyCode < 65 || e.keyCode > 90) {
                        if (e.key === 'ñ' || e.key === 'Ñ') return;
                        e.preventDefault();
                    }
                });

                lastNameInput.addEventListener('paste', function(e) {
                    e.preventDefault();
                    const pasteData = (e.clipboardData || window.clipboardData).getData('text');
                    let cleaned = pasteData.replace(/[^a-zA-ZñÑ ]/g, '');
                    // Force only one space if present
                    if (cleaned.includes(' ')) {
                        const parts = cleaned.split(' ');
                        cleaned = parts[0] + ' ' + parts.slice(1).join('').replace(/ /g, '');
                    }
                    document.execCommand('insertText', false, cleaned);
                });

                lastNameInput.addEventListener('input', function() {
                    let val = this.value.replace(/[^a-zA-ZñÑ ]/g, '');
                    // Force only one space
                    if (val.includes(' ')) {
                        const parts = val.split(' ');
                        val = parts[0] + ' ' + parts.slice(1).join('').replace(/ /g, '');
                    }
                    if (this.value !== val) this.value = val;

                    const errorDiv = document.getElementById('lastNameError');
                    if (val.length > 25) {
                        errorDiv.textContent = 'Maximum of 25 characters only.';
                        errorDiv.classList.remove('hidden');
                        this.classList.add('border-red-500');
                    } else {
                        errorDiv.classList.add('hidden');
                        this.classList.remove('border-red-500');
                    }
                });
            }

            const middleNameInput = document.getElementById('middleName');
            if (middleNameInput) {
                middleNameInput.addEventListener('keydown', function(e) {
                    if ([8, 46, 9, 27, 13, 37, 38, 39, 40].indexOf(e.keyCode) !== -1) return;
                    // Allow one space (32)
                    if (e.keyCode === 32) {
                        if (this.value.includes(' ') || this.value.length === 0) {
                            e.preventDefault();
                        }
                        return;
                    }
                    // Block numbers and special chars (allowing ñ and Ñ)
                    if (e.keyCode < 65 || e.keyCode > 90) {
                        if (e.key === 'ñ' || e.key === 'Ñ') return;
                        e.preventDefault();
                    }
                });

                middleNameInput.addEventListener('paste', function(e) {
                    e.preventDefault();
                    const pasteData = (e.clipboardData || window.clipboardData).getData('text');
                    let cleaned = pasteData.replace(/[^a-zA-ZñÑ ]/g, '');
                    // Force only one space if present
                    if (cleaned.includes(' ')) {
                        const parts = cleaned.split(' ');
                        cleaned = parts[0] + ' ' + parts.slice(1).join('').replace(/ /g, '');
                    }
                    document.execCommand('insertText', false, cleaned);
                });

                middleNameInput.addEventListener('input', function() {
                    let val = this.value.replace(/[^a-zA-ZñÑ ]/g, '');
                    // Force only one space
                    if (val.includes(' ')) {
                        const parts = val.split(' ');
                        val = parts[0] + ' ' + parts.slice(1).join('').replace(/ /g, '');
                    }
                    if (this.value !== val) this.value = val;

                    const errorDiv = document.getElementById('middleNameError');
                    if (val.length > 25) {
                        errorDiv.textContent = 'Maximum of 25 characters only.';
                        errorDiv.classList.remove('hidden');
                        this.classList.add('border-red-500');
                    } else {
                        errorDiv.classList.add('hidden');
                        this.classList.remove('border-red-500');
                    }
                });
            }

            const phoneInput = document.getElementById('phoneNumber');
            if (phoneInput) {
                phoneInput.addEventListener('keydown', function(e) {
                    // Allow: backspace, delete, tab, escape, enter, arrow keys
                    if ([46, 8, 9, 27, 13, 110, 190, 37, 38, 39, 40].indexOf(e.keyCode) !== -1) {
                        return;
                    }
                    // Allow: Ctrl+A, Ctrl+C, Ctrl+V, Command+A
                    if ((e.ctrlKey || e.metaKey) && (e.keyCode === 65 || e.keyCode === 67 || e.keyCode === 86)) {
                        return;
                    }
                    // Ensure that it is a number and stop the keypress
                    if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
                        e.preventDefault();
                    }
                });

                phoneInput.addEventListener('paste', function(e) {
                    e.preventDefault();
                    const pasteData = (e.clipboardData || window.clipboardData).getData('text');
                    const cleaned = pasteData.replace(/[^0-9]/g, '');
                    document.execCommand('insertText', false, cleaned);
                });

                phoneInput.addEventListener('input', function() {
                    let val = this.value.replace(/[^0-9]/g, '');
                    this.value = val;
                    
                    const errorDiv = document.getElementById('phoneError');
                    if (val.length === 0) {
                        errorDiv.classList.add('hidden');
                        this.parentElement.style.borderColor = '#e5e7eb';
                    } else if (val[0] !== '9') {
                        errorDiv.textContent = 'Phone number must start with 9';
                        errorDiv.classList.remove('hidden');
                        this.parentElement.style.borderColor = '#ef4444';
                    } else if (val.length < 10) {
                        errorDiv.textContent = 'Phone number must be exactly 10 digits';
                        errorDiv.classList.remove('hidden');
                        this.parentElement.style.borderColor = '#ef4444';
                    } else {
                        errorDiv.classList.add('hidden');
                        this.parentElement.style.borderColor = '#e5e7eb';
                    }
                });

                phoneInput.addEventListener('blur', function() {
                    const val = this.value.trim();
                    const errorDiv = document.getElementById('phoneError');
                    if (val.length < 10) return;

                    fetch('<?php echo e(route("check-availability")); ?>', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({ phone: val })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (!data.available) {
                            errorDiv.textContent = data.message;
                            errorDiv.classList.remove('hidden');
                            this.parentElement.style.borderColor = '#ef4444';
                        } else {
                            errorDiv.classList.add('hidden');
                            this.parentElement.style.borderColor = '#e5e7eb';
                        }
                    });
                });
            }

            const resetPhoneInput = document.getElementById('resetPhone');
            if (resetPhoneInput) {
                resetPhoneInput.addEventListener('keydown', function(e) {
                    if ([46, 8, 9, 27, 13, 110, 190, 37, 38, 39, 40].indexOf(e.keyCode) !== -1) return;
                    if ((e.ctrlKey || e.metaKey) && (e.keyCode === 65 || e.keyCode === 67 || e.keyCode === 86)) return;
                    if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
                        e.preventDefault();
                    }
                });
                resetPhoneInput.addEventListener('input', function() {
                    let val = this.value.replace(/[^0-9]/g, '');
                    this.value = val;

                    const errorDiv = document.getElementById('resetPhoneError');
                    if (val.length === 0) {
                        errorDiv.classList.add('hidden');
                        this.parentElement.style.borderColor = '#e5e7eb';
                    } else if (!val.startsWith('09')) {
                        errorDiv.textContent = 'Phone number must start with 09';
                        errorDiv.classList.remove('hidden');
                        this.parentElement.style.borderColor = '#ef4444';
                    } else if (val.length < 11) {
                        errorDiv.textContent = 'Phone number must be exactly 11 digits';
                        errorDiv.classList.remove('hidden');
                        this.parentElement.style.borderColor = '#ef4444';
                    } else {
                        errorDiv.classList.add('hidden');
                        this.parentElement.style.borderColor = '#e5e7eb';
                    }
                });
            }

            const regEmailInput = document.getElementById('regEmail');
            if (regEmailInput) {
                const gmailRegex = /^(?=[^@]*[a-zA-Z])(?!\.)(?!.*\.{2})[a-zA-Z0-9][a-zA-Z0-9.]{4,28}[a-zA-Z0-9]@gmail\.com$/i;
                const errorDiv = document.getElementById('regEmailError');

                function validateEmailLive(val) {
                    if (val === '') {
                        errorDiv.classList.add('hidden');
                        regEmailInput.classList.remove('border-red-500');
                        return;
                    }
                    if (/^[0-9]+$/.test(val.split('@')[0])) {
                        errorDiv.textContent = 'Gmail username must contain at least one letter.';
                        errorDiv.classList.remove('hidden');
                        regEmailInput.classList.add('border-red-500');
                        return;
                    }
                    if (val.includes('@')) {
                        if (!gmailRegex.test(val)) {
                            // Specific error messages
                            const uname = val.split('@')[0];
                            if (uname.length < 6) {
                                errorDiv.textContent = 'Gmail username must be at least 6 characters.';
                            } else if (uname.length > 30) {
                                errorDiv.textContent = 'Gmail username must not exceed 30 characters.';
                            } else if (/\.{2}/.test(uname)) {
                                errorDiv.textContent = 'Gmail username cannot have consecutive dots.';
                            } else if (/^\.|\.$/.test(uname)) {
                                errorDiv.textContent = 'Gmail username cannot start or end with a dot.';
                            } else if (!val.endsWith('@gmail.com')) {
                                errorDiv.textContent = 'Only Gmail addresses are accepted (e.g. you@gmail.com).';
                            } else {
                                errorDiv.textContent = 'Please enter a valid Gmail address.';
                            }
                            errorDiv.classList.remove('hidden');
                            regEmailInput.classList.add('border-red-500');
                        } else {
                            errorDiv.classList.add('hidden');
                            regEmailInput.classList.remove('border-red-500');
                        }
                        return;
                    }
                    // Still typing username part (no @ yet)
                    errorDiv.classList.add('hidden');
                    regEmailInput.classList.remove('border-red-500');
                }

                // Only allow: letters, digits, dot, and @. Block everything else.
                regEmailInput.addEventListener('keydown', function(e) {
                    if ([8, 46, 9, 27, 13, 37, 38, 39, 40].indexOf(e.keyCode) !== -1) return;
                    if (e.keyCode === 32) { e.preventDefault(); return; } // block space
                    if (e.ctrlKey || e.metaKey) return; // allow ctrl+c/v etc.
                    // Only allow a-z, A-Z, 0-9, dot, @
                    if (!/^[a-zA-Z0-9.@]$/.test(e.key)) {
                        e.preventDefault();
                        return;
                    }
                });

                // Strip any disallowed chars on paste / autocomplete, then validate
                regEmailInput.addEventListener('input', function() {
                    // Only allow letters, numbers, dot, @ — strip everything else
                    const cleaned = this.value.replace(/[^a-zA-Z0-9.@]/g, '');
                    if (this.value !== cleaned) this.value = cleaned;
                    validateEmailLive(this.value.trim());
                });

                regEmailInput.addEventListener('blur', function() {
                    const val = this.value.trim();
                    if (val === '' || !val.includes('@')) return;

                    // First check format
                    if (!gmailRegex.test(val)) {
                        errorDiv.textContent = 'Only Gmail addresses are accepted (e.g. you@gmail.com).';
                        errorDiv.classList.remove('hidden');
                        this.classList.add('border-red-500');
                        return;
                    }

                    // Then check if already registered
                    fetch('<?php echo e(route("check-availability")); ?>', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({ email: val })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (!data.available) {
                            errorDiv.textContent = data.message;
                            errorDiv.classList.remove('hidden');
                            this.classList.add('border-red-500');
                        } else {
                            if (!regEmailInput.classList.contains('border-red-500') ||
                                errorDiv.textContent === 'This email is already registered.') {
                                errorDiv.classList.add('hidden');
                                this.classList.remove('border-red-500');
                            }
                        }
                    });
                });
            }

            const regPasswordInput = document.getElementById('regPassword');
            const regPasswordConfirm = document.getElementById('regPasswordConfirm');

            const pwRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9])[A-Za-z\d\D]{6,}$/;

            function validatePasswordConfirm() {
                if (!regPasswordConfirm) return;
                const errDiv = document.getElementById('regPasswordConfirmError');
                if (regPasswordConfirm.value.length === 0) {
                    errDiv.classList.add('hidden');
                    return;
                }
                if (regPasswordInput.value !== regPasswordConfirm.value) {
                    errDiv.textContent = 'Passwords do not match.';
                    errDiv.classList.remove('hidden');
                } else {
                    errDiv.classList.add('hidden');
                }
            }

            if (regPasswordInput) {
                // Initialize button as disabled
                setTimeout(() => {
                    const btn = document.querySelector('#registerForm button[type="submit"]');
                    if(btn) {
                        btn.disabled = true;
                        btn.style.opacity = '0.5';
                        btn.style.cursor = 'not-allowed';
                    }
                }, 100);

                regPasswordInput.addEventListener('input', function() {
                    const val = this.value;
                    const errDiv = document.getElementById('regPasswordError');
                    const strengthContainer = document.getElementById('pwStrengthContainer');
                    const strengthBar = document.getElementById('pwStrengthBar');
                    const strengthText = document.getElementById('pwStrengthText');
                    
                    if (val.length === 0) {
                        strengthContainer.classList.add('hidden');
                        errDiv.classList.add('hidden');
                        return;
                    }
                    strengthContainer.classList.remove('hidden');

                    let strength = 0;
                    
                    if (val.length >= 6) strength++;
                    if (val.match(/[A-Z]/)) strength++;
                    if (val.match(/[a-z]/)) strength++;
                    if (val.match(/[0-9]/)) strength++;
                    if (val.match(/[^A-Za-z0-9]/)) strength++;

                    let percent = 0;
                    let color = '';
                    let text = '';

                    if (strength <= 2) {
                        percent = 33;
                        color = '#ef4444'; // red
                        text = 'Weak';
                        strengthText.style.color = color;
                        errDiv.textContent = 'Must have at least 6 chars, uppercase, lowercase, number, and symbol.';
                        errDiv.classList.remove('hidden');
                    } else if (strength === 3 || strength === 4) {
                        percent = 66;
                        color = '#eab308'; // yellow
                        text = 'Medium';
                        strengthText.style.color = color;
                        errDiv.textContent = 'Must have at least 6 chars, uppercase, lowercase, number, and symbol.';
                        errDiv.classList.remove('hidden');
                    } else if (strength === 5) {
                        percent = 100;
                        color = '#22c55e'; // green
                        text = 'Strong';
                        strengthText.style.color = color;
                        errDiv.classList.add('hidden');
                        document.querySelector('#registerForm button[type="submit"]').disabled = false;
                        document.querySelector('#registerForm button[type="submit"]').style.opacity = '1';
                        document.querySelector('#registerForm button[type="submit"]').style.cursor = 'pointer';
                    } else {
                        document.querySelector('#registerForm button[type="submit"]').disabled = true;
                        document.querySelector('#registerForm button[type="submit"]').style.opacity = '0.5';
                        document.querySelector('#registerForm button[type="submit"]').style.cursor = 'not-allowed';
                    }

                    strengthBar.style.width = percent + '%';
                    strengthBar.style.backgroundColor = color;
                    strengthText.textContent = text;
                    validatePasswordConfirm();
                });
            }

            if (regPasswordConfirm) {
                regPasswordConfirm.addEventListener('input', validatePasswordConfirm);
            }

            // ─── Reset Password Form Strength & Confirm ─────────────────
            const resetPwInput = document.getElementById('newPassword');
            const resetPwConfirmInput = document.getElementById('confirmNewPassword');

            function validateResetConfirm() {
                if (!resetPwConfirmInput) return;
                const errDiv = document.getElementById('resetConfirmError');
                if (resetPwConfirmInput.value.length === 0) {
                    errDiv.classList.add('hidden');
                    return;
                }
                if (resetPwInput.value !== resetPwConfirmInput.value) {
                    errDiv.textContent = 'Passwords do not match.';
                    errDiv.classList.remove('hidden');
                } else {
                    errDiv.classList.add('hidden');
                }
            }

            if (resetPwInput) {
                resetPwInput.addEventListener('input', function() {
                    const val = this.value;
                    const errDiv = document.getElementById('resetPwError');
                    const strengthContainer = document.getElementById('resetPwStrengthContainer');
                    const strengthBar = document.getElementById('resetPwStrengthBar');
                    const strengthText = document.getElementById('resetPwStrengthText');
                    
                    if (val.length === 0) {
                        strengthContainer.classList.add('hidden');
                        errDiv.classList.add('hidden');
                        return;
                    }
                    strengthContainer.classList.remove('hidden');

                    let strength = 0;
                    if (val.length >= 6) strength++;
                    if (val.match(/[A-Z]/)) strength++;
                    if (val.match(/[a-z]/)) strength++;
                    if (val.match(/[0-9]/)) strength++;
                    if (val.match(/[^A-Za-z0-9]/)) strength++;

                    let percent = 0;
                    let color = '';
                    let text = '';

                    if (strength <= 2) {
                        percent = 33; color = '#ef4444'; text = 'Weak';
                        errDiv.textContent = 'Must have at least 6 chars, uppercase, lowercase, number, and symbol.';
                        errDiv.classList.remove('hidden');
                    } else if (strength === 3 || strength === 4) {
                        percent = 66; color = '#eab308'; text = 'Medium';
                        errDiv.textContent = 'Must have at least 6 chars, uppercase, lowercase, number, and symbol.';
                        errDiv.classList.remove('hidden');
                    } else if (strength === 5) {
                        percent = 100; color = '#22c55e'; text = 'Strong';
                        errDiv.classList.add('hidden');
                    }

                    strengthBar.style.width = percent + '%';
                    strengthBar.style.backgroundColor = color;
                    strengthText.textContent = text;
                    strengthText.style.color = color;
                    validateResetConfirm();
                });
            }

            if (resetPwConfirmInput) { resetPwConfirmInput.addEventListener('input', validateResetConfirm); }

            // ─── Registration AJAX + OTP Verification ────────────────────
            let regUserEmail = '';
            let regCountdownInterval;

            function startRegCountdown() {
                if (regCountdownInterval) clearInterval(regCountdownInterval);
                let seconds = 120;
                const btn = document.getElementById('regResendBtn');
                const span = document.getElementById('regCountdown');
                btn.disabled = true;
                btn.innerHTML = `Resend Code (${seconds}s)`;

                regCountdownInterval = setInterval(() => {
                    seconds--;
                    btn.innerHTML = `Resend Code (${seconds}s)`;
                    if (seconds <= 0) {
                        clearInterval(regCountdownInterval);
                        btn.disabled = false;
                        btn.innerHTML = 'Resend Code';
                    }
                }, 1000);
            }

            // Expose to global scope so onclick="..." HTML attributes can call them
            window.cancelRegOtp = function() {
                document.getElementById('regOtpSection').style.display = 'none';
                document.getElementById('registerForm').style.display = 'block';
                // ─── Do NOT clear the form inputs so user keeps their data ───
                document.querySelectorAll('.reg-otp-input').forEach(i => i.value = '');
                const verifyBtn = document.getElementById('regVerifyBtn');
                if (verifyBtn) {
                    verifyBtn.innerHTML = '<i class="fas fa-check mr-2"></i> Verify & Activate';
                    verifyBtn.disabled = false;
                }
                if (regCountdownInterval) clearInterval(regCountdownInterval);
                // Re-enable create account button in case it was disabled
                const createBtn = document.getElementById('createAccountBtn');
                if (createBtn) { createBtn.disabled = false; createBtn.innerHTML = '<i class="fas fa-user-plus mr-2"></i> Create Account'; }
            };

            window.verifyRegOTP = function() {
                const inputs = document.querySelectorAll('.reg-otp-input');
                let otp = '';
                inputs.forEach(i => otp += i.value);

                if (otp.length !== 6) {
                    showToast('Please enter the 6-digit code.', 'error');
                    return;
                }

                const btn = document.getElementById('regVerifyBtn');
                btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Verifying...';

                fetch('<?php echo e(route("register.verify-otp")); ?>', {
                    method: 'POST',
                    headers: { 
                        'Content-Type': 'application/json', 
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') 
                    },
                    body: JSON.stringify({ email: regUserEmail, otp: otp })
                })
                .then(async res => {
                    const data = await res.json();
                    if (!res.ok) throw new Error(data.message || 'Verification failed.');
                    return data;
                })
                .then(data => {
                    if (data.success) {
                        showToast(data.message, 'success');
                        if (regCountdownInterval) clearInterval(regCountdownInterval);
                        setTimeout(() => { window.location.href = '<?php echo e(route("login")); ?>'; }, 2500);
                    } else {
                        showToast(data.message, 'error');
                        btn.disabled = false;
                        btn.innerHTML = '<i class="fas fa-check mr-2"></i> Verify & Activate';
                        document.querySelectorAll('.reg-otp-input').forEach(i => i.value = '');
                        document.querySelector('.reg-otp-input')?.focus();
                    }
                })
                .catch(err => {
                    showToast(err.message, 'error');
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-check mr-2"></i> Verify & Activate';
                });
            };

            window.resendRegOTP = function() {
                fetch('<?php echo e(route("register.resend-otp")); ?>', {
                    method: 'POST',
                    headers: { 
                        'Content-Type': 'application/json', 
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') 
                    },
                    body: JSON.stringify({ email: regUserEmail })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        showToast(data.message, 'success');
                        startRegCountdown();
                        document.querySelectorAll('.reg-otp-input').forEach(i => i.value = '');
                        document.querySelector('.reg-otp-input')?.focus();
                    } else {
                        showToast(data.message, 'error');
                    }
                });
            };

            // OTP input auto-advance for registration OTP boxes
            document.querySelectorAll('.reg-otp-input').forEach((input, index) => {
                input.addEventListener('input', function() {
                    this.value = this.value.replace(/[^0-9]/g, '');
                    if (this.value.length === 1) {
                        const next = document.querySelector(`.reg-otp-input[data-index="${index + 1}"]`);
                        if (next) next.focus();
                    }
                });
                input.addEventListener('keydown', function(e) {
                    if (e.key === 'Backspace' && this.value === '') {
                        const prev = document.querySelector(`.reg-otp-input[data-index="${index - 1}"]`);
                        if (prev) prev.focus();
                    }
                });
            });

            const registerForm = document.getElementById('registerForm');
            if (registerForm) {
                registerForm.addEventListener('submit', function(e) {
                    e.preventDefault();

                    const fname = document.getElementById('firstName').value;
                    const lname = document.getElementById('lastName').value;
                    const emailVal = document.getElementById('regEmail').value.trim();
                    const fRegex = /^[a-zA-ZñÑ]+( [a-zA-ZñÑ]+)?$/;
                    const lRegex = /^[a-zA-ZñÑ]+( [a-zA-ZñÑ]+)?$/;
                    const gmailRegex = /^(?=[^@]*[a-zA-Z])(?!\.)(?!.*\.{2})[a-zA-Z0-9][a-zA-Z0-9.]{4,28}[a-zA-Z0-9]@gmail\.com$/i;

                    let hasError = false;

                    // ── Required field: First Name ──────────────────
                    if (fname.trim() === '') {
                        hasError = true;
                        const errEl = document.getElementById('firstNameError');
                        errEl.textContent = 'First name is required.';
                        errEl.classList.remove('hidden');
                        document.getElementById('firstName').classList.add('border-red-500');
                    } else if (!fRegex.test(fname) || fname.length > 25) {
                        hasError = true;
                        document.getElementById('firstNameError').classList.remove('hidden');
                        document.getElementById('firstName').classList.add('border-red-500');
                    }

                    // ── Required field: Last Name ───────────────────
                    if (lname.trim() === '') {
                        hasError = true;
                        const lErrEl = document.getElementById('lastNameError');
                        lErrEl.textContent = 'Last name is required.';
                        lErrEl.classList.remove('hidden');
                        document.getElementById('lastName').classList.add('border-red-500');
                    } else if (!lRegex.test(lname) || lname.length > 25) {
                        hasError = true;
                        document.getElementById('lastNameError').classList.remove('hidden');
                        document.getElementById('lastName').classList.add('border-red-500');
                    }

                    // ── Required field: Role ────────────────────────
                    const roleVal = document.getElementById('regRole').value;
                    if (!roleVal) {
                        hasError = true;
                        showToast('Please select a role.', 'error');
                    }

                    // ── Required field: Phone ───────────────────────
                    const phoneVal = document.getElementById('phoneNumber').value.trim();
                    const phoneErrEl = document.getElementById('phoneError');
                    if (phoneVal === '') {
                        hasError = true;
                        phoneErrEl.textContent = 'Phone number is required.';
                        phoneErrEl.classList.remove('hidden');
                        document.querySelector('.ph-phone-wrapper').style.borderColor = '#ef4444';
                    }

                    // ── Required field: Email ───────────────────────
                    if (emailVal === '') {
                        hasError = true;
                        const eErrDiv = document.getElementById('regEmailError');
                        eErrDiv.textContent = 'Email address is required.';
                        eErrDiv.classList.remove('hidden');
                        document.getElementById('regEmail').classList.add('border-red-500');
                    } else if (!gmailRegex.test(emailVal)) {
                        hasError = true;
                        const errDiv = document.getElementById('regEmailError');
                        errDiv.textContent = 'Only Gmail addresses are accepted (e.g. you@gmail.com).';
                        errDiv.classList.remove('hidden');
                        document.getElementById('regEmail').classList.add('border-red-500');
                    } else {
                        document.getElementById('regEmailError').classList.add('hidden');
                        document.getElementById('regEmail').classList.remove('border-red-500');
                    }

                    const passwordVal = document.getElementById('regPassword').value;
                    const confirmVal = document.getElementById('regPasswordConfirm').value;

                    // ── Required field: Password ────────────────────
                    if (passwordVal === '') {
                        hasError = true;
                        const err = document.getElementById('regPasswordError');
                        err.textContent = 'Password is required.';
                        err.classList.remove('hidden');
                    } else if (!pwRegex.test(passwordVal)) {
                        hasError = true;
                        const err = document.getElementById('regPasswordError');
                        err.textContent = 'Password does not meet the strong criteria.';
                        err.classList.remove('hidden');
                    }

                    // ── Required field: Confirm Password ────────────
                    if (confirmVal === '') {
                        hasError = true;
                        const errC = document.getElementById('regPasswordConfirmError');
                        errC.textContent = 'Please confirm your password.';
                        errC.classList.remove('hidden');
                    } else if (passwordVal !== confirmVal) {
                        hasError = true;
                        const errC = document.getElementById('regPasswordConfirmError');
                        errC.textContent = 'Passwords do not match.';
                        errC.classList.remove('hidden');
                    }

                    if (hasError) {
                        showToast('Please correct the errors in the form.', 'error');
                        return;
                    }

                    // AJAX submit
                    const btn = document.getElementById('createAccountBtn');
                    const originalText = btn.innerHTML;
                    btn.disabled = true;
                    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Creating Account...';

                    const formData = new FormData(this);

                    fetch('<?php echo e(route("register")); ?>', {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: formData
                    })
                    .then(async res => {
                        const data = await res.json();
                        if (res.status === 419) throw new Error('Session expired. Page will refresh.');
                        if (!res.ok) {
                            // Laravel validation errors
                            const messages = data.errors
                                ? Object.values(data.errors).flat().join(' ')
                                : (data.message || 'Registration failed.');
                            throw new Error(messages);
                        }
                        return data;
                    })
                    .then(data => {
                        if (data.success) {
                            regUserEmail = data.email;
                            document.getElementById('regOtpEmailDisplay').textContent = data.email;
                            registerForm.style.display = 'none';
                            document.getElementById('regOtpSection').style.display = 'block';
                            document.querySelector('.reg-otp-input')?.focus();
                            startRegCountdown();
                            showToast(data.message, 'success');
                        } else {
                            showToast(data.message || 'Registration failed.', 'error');
                            btn.disabled = false;
                            btn.innerHTML = originalText;
                        }
                    })
                    .catch(err => {
                        showToast(err.message, 'error');
                        btn.disabled = false;
                        btn.innerHTML = originalText;
                    });
                });
            }

            if (rememberCheckbox) {
                rememberCheckbox.addEventListener('change', saveRememberMe);
            }
            
            if (emailInput) {
                emailInput.addEventListener('input', function() {
                    if (rememberCheckbox && rememberCheckbox.checked) {
                        saveRememberMe();
                    }
                });
            }
            
            const passwordInput = document.getElementById('loginPassword');
            if (passwordInput) {
                passwordInput.addEventListener('input', function() {
                    if (rememberCheckbox && rememberCheckbox.checked) {
                        saveRememberMe();
                    }
                });
            }

            // ─── Force Password Change Modal Strength & Match ─────────────────
            const forcePwInput = document.getElementById('force-new-pwd');
            const forcePwConfirm = document.getElementById('force-confirm-pwd');
            const forceBtn = document.getElementById('btn-force-pwd');

            if (forceBtn) {
                forceBtn.disabled = true;
                forceBtn.style.opacity = '0.5';
                forceBtn.style.cursor = 'not-allowed';
            }
            
            function validateForcePwConfirm() {
                if (!forcePwConfirm || !forcePwInput || !forceBtn) return;
                const errDiv = document.getElementById('forceConfirmError');
                let isValidMatch = true;
                
                if (forcePwConfirm.value.length === 0) {
                    errDiv.classList.add('hidden');
                    isValidMatch = false;
                } else if (forcePwInput.value !== forcePwConfirm.value) {
                    errDiv.textContent = 'Passwords do not match.';
                    errDiv.classList.remove('hidden');
                    isValidMatch = false;
                } else {
                    errDiv.classList.add('hidden');
                }

                const val = forcePwInput.value;
                const meetsCriteria = val.length >= 8 && val.match(/[A-Z]/) && val.match(/[0-9]/) && val.match(/[^A-Za-z0-9]/);

                if (isValidMatch && meetsCriteria) {
                    forceBtn.disabled = false;
                    forceBtn.style.opacity = '1';
                    forceBtn.style.cursor = 'pointer';
                } else {
                    forceBtn.disabled = true;
                    forceBtn.style.opacity = '0.5';
                    forceBtn.style.cursor = 'not-allowed';
                }
            }

            if (forcePwInput) {
                forcePwInput.addEventListener('input', function() {
                    const val = this.value;
                    const criteriaText = document.getElementById('forcePwCriteria');
                    const strengthContainer = document.getElementById('forcePwStrengthContainer');
                    const strengthBar = document.getElementById('forcePwStrengthBar');
                    const strengthText = document.getElementById('forcePwStrengthText');
                    
                    const reqLength = document.getElementById('req-length');
                    const reqUpper = document.getElementById('req-upper');
                    const reqNumber = document.getElementById('req-number');
                    const reqSpecial = document.getElementById('req-special');

                    function resetReq(el, text) {
                        el.className = 'flex items-center gap-1.5 text-gray-500 transition-colors duration-200';
                        el.innerHTML = '<i class="fas fa-circle text-[6px]"></i> <span>' + text + '</span>';
                    }

                    if (val.length === 0) {
                        strengthContainer.classList.add('hidden');
                        criteriaText.classList.remove('hidden');
                        resetReq(reqLength, 'Minimum 8 characters');
                        resetReq(reqUpper, 'At least 1 uppercase');
                        resetReq(reqNumber, 'At least 1 number');
                        resetReq(reqSpecial, 'At least 1 special character');
                        validateForcePwConfirm();
                        return;
                    }
                    strengthContainer.classList.remove('hidden');
                    
                    let strength = 0;
                    if (val.length >= 8) strength++;
                    if (val.match(/[A-Z]/)) strength++;
                    if (val.match(/[a-z]/)) strength++;
                    if (val.match(/[0-9]/)) strength++;
                    if (val.match(/[^A-Za-z0-9]/)) strength++;

                    let percent = 0;
                    let color = '';
                    let text = '';

                    if (strength <= 2) {
                        percent = 33; color = '#ef4444'; text = 'Weak';
                    } else if (strength === 3 || strength === 4) {
                        percent = 66; color = '#eab308'; text = 'Medium';
                    } else if (strength === 5) {
                        percent = 100; color = '#22c55e'; text = 'Strong';
                    }

                    function updateReq(el, isMet, text) {
                        if (isMet) {
                            el.className = 'flex items-center gap-1.5 text-green-600 transition-colors duration-200';
                            el.innerHTML = '<i class="fas fa-check text-[10px]"></i> <span>' + text + '</span>';
                        } else {
                            el.className = 'flex items-center gap-1.5 text-red-500 transition-colors duration-200';
                            el.innerHTML = '<i class="fas fa-times text-[10px]"></i> <span>' + text + '</span>';
                        }
                    }

                    const hasLength = val.length >= 8;
                    const hasUpper = /[A-Z]/.test(val);
                    const hasNumber = /[0-9]/.test(val);
                    const hasSpecial = /[^A-Za-z0-9]/.test(val);

                    updateReq(reqLength, hasLength, 'Minimum 8 characters');
                    updateReq(reqUpper, hasUpper, 'At least 1 uppercase');
                    updateReq(reqNumber, hasNumber, 'At least 1 number');
                    updateReq(reqSpecial, hasSpecial, 'At least 1 special character');

                    // Strict check for the minimum requirements
                    if (hasLength && hasUpper && hasNumber && hasSpecial) {
                        criteriaText.classList.add('hidden');
                    } else {
                        criteriaText.classList.remove('hidden');
                    }

                    strengthBar.style.width = percent + '%';
                    strengthBar.style.backgroundColor = color;
                    strengthText.textContent = text;
                    strengthText.style.color = color;
                    
                    validateForcePwConfirm();
                });
            }

            if (forcePwConfirm) {
                forcePwConfirm.addEventListener('input', validateForcePwConfirm);
            }
            
            if (loginForm) {
                loginForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    saveRememberMe();

                    const email = document.getElementById('loginEmail').value;
                    const password = document.getElementById('loginPassword').value;
                    const remember = document.getElementById('remember').checked;

                    const btn = this.querySelector('button[type="submit"]');
                    const originalText = btn.innerHTML;
                    btn.disabled = true;
                    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Signing In...';

                    fetch(this.action, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ email, password, remember })
                    })
                    .then(async res => {
                        const data = await res.json();
                        if (res.status === 419) {
                            throw new Error('Session expired. Page will refresh to update your security token.');
                        }
                        if (res.status === 403 || res.status === 401) {
                            throw new Error(data.message);
                        }
                        return data;
                    })
                    .then(data => {
                        if (data.account_disabled) {
                            showDisabledModal(data.message);
                            btn.disabled = false;
                            btn.innerHTML = originalText;
                        } else if (data.mfa_required) {
                            showMfaModal(data);
                            btn.disabled = false;
                            btn.innerHTML = originalText;
                        } else if (data.force_password_change) {
                            showForcePasswordModal(data.message);
                            btn.disabled = false;
                            btn.innerHTML = originalText;
                        } else if (data.success) {
                            showToast('Login successful!', 'success');
                            window.location.href = data.redirect;
                        } else {
                            showToast(data.message || 'Login failed.', 'error');
                            btn.disabled = false;
                            btn.innerHTML = originalText;
                        }
                    })
                    .catch(error => {
                        showToast(error.message || 'An error occurred.', 'error');
                        btn.disabled = false;
                        btn.innerHTML = originalText;
                        
                        if (error.message.includes('Session expired')) {
                            setTimeout(() => window.location.reload(), 2000);
                        }
                    });
                });
            }
        });

        // Show error if any from Laravel session
        <?php if(session('error')): ?>
            showToast("<?php echo e(session('error')); ?>", 'error');
        <?php endif; ?>
        <?php if(session('success')): ?>
            showToast("<?php echo e(session('success')); ?>", 'success');
        <?php endif; ?>
    </script>
</body>

</html><?php /**PATH C:\xampp\htdocs\eurotaxisystem-main\resources\views/auth/login.blade.php ENDPATH**/ ?>