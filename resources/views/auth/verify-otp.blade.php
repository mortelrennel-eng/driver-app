<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Eurotaxisystem - Verify OTP</title>
    <script src="{{ asset('assets/tailwind.min.js') }}"></script>
    <link rel="stylesheet" href="{{ asset('assets/fontawesome/all.min.css') }}">
    <style>
        body {
            margin: 0;
            padding: 0;
            overflow: hidden;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol";
            background-color: #f9fafb;
        }

        .split-layout {
            display: flex;
            height: 100vh;
            width: 100%;
        }

        .frosted-overlay {
            background: rgba(0, 0, 0, 0.12);
            backdrop-filter: blur(3px);
            -webkit-backdrop-filter: blur(3px);
            position: absolute;
            inset: 0;
            display: flex;
            flex-direction: column;
        }

        .text-shadow-enhanced {
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
        }

        .text-shadow-light {
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.3);
        }

        @keyframes logoBounce {
            0% { transform: translateY(-50px); opacity: 0; }
            50% { transform: translateY(10px); opacity: 1; }
            65% { transform: translateY(-5px); }
            80% { transform: translateY(3px); }
            95% { transform: translateY(-1px); }
            100% { transform: translateY(0); }
        }

        .logo-bounce {
            animation: logoBounce 1.5s ease-out;
        }

        @keyframes iconSlideIn1 { 0% { transform: translateX(-100px); opacity: 0; } 100% { transform: translateX(0); opacity: 1; } }
        @keyframes iconSlideIn2 { 0% { transform: translateY(50px); opacity: 0; } 100% { transform: translateY(0); opacity: 1; } }
        @keyframes iconSlideIn3 { 0% { transform: translateX(100px); opacity: 0; } 100% { transform: translateX(0); opacity: 1; } }

        .icon-animate-1 { animation: iconSlideIn1 0.8s ease-out 0.5s both; }
        .icon-animate-2 { animation: iconSlideIn2 0.8s ease-out 0.7s both; }
        .icon-animate-3 { animation: iconSlideIn3 0.8s ease-out 0.9s both; }

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

        /* Right Side Form */
        .right-side {
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f9fafb;
        }

        .form-wrapper {
            width: 100%;
            max-width: 380px;
            padding: 1rem;
            animation: slideUp 0.5s ease-out;
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @media (max-width: 768px) {
            .split-layout {
                flex-direction: column;
            }
            .left-side {
                height: 40vh;
            }
            .right-side {
                height: 60vh;
            }
            .logo-container {
                max-width: 260px;
            }
            .logo-image {
                max-height: 260px;
            }
        }

        .otp-inputs {
            display: flex;
            gap: 0.4rem;
            justify-content: center;
            margin-bottom: 2rem;
            direction: ltr;
        }
        .otp-input {
            width: 45px;
            height: 55px;
            text-align: center;
            font-size: 1.5rem;
            font-weight: 700;
            border: 2px solid #e5e7eb;
            border-radius: 0.5rem;
            background: white;
            transition: all 0.3s ease;
            caret-color: transparent;
        }
        .otp-input:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.15);
            transform: translateY(-2px);
        }
        .otp-input.filled {
            border-color: #10b981;
            background-color: #f0fdf4;
        }
        #hiddenOtp {
            position: absolute;
            left: -9999px;
        }
        .btn-primary {
            width: 100%;
            padding: 1rem;
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            color: white;
            border: none;
            border-radius: 0.5rem;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px -1px rgba(59, 130, 246, 0.5);
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(59, 130, 246, 0.5);
        }
        .btn-primary:active {
            transform: translateY(0);
        }
        .resend-btn {
            background: transparent;
            color: #6b7280;
            border: none;
            font-size: 0.9rem;
            font-weight: 500;
            cursor: pointer;
            transition: color 0.3s ease;
        }
        .resend-btn:disabled {
            cursor: not-allowed;
            opacity: 0.6;
        }
        .resend-btn:not(:disabled):hover {
            color: #3b82f6;
            text-decoration: underline;
        }
        .message-toast {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 1rem 1.5rem;
            border-radius: 0.5rem;
            color: white;
            font-weight: 500;
            z-index: 10000;
            transform: translateX(400px);
            transition: transform 0.3s ease;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }
        .message-toast.show {
            transform: translateX(0);
        }
        .message-toast.success {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        }
        .message-toast.error {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        }
        .message-toast.info {
             background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        }
        .back-link {
            color: #6b7280;
            transition: color 0.3s ease;
            text-decoration: none;
            font-size: 0.9rem;
            display: inline-flex;
            align-items: center;
        }
        .back-link:hover {
            color: #111827;
        }
    </style>
</head>
<body>
    <div id="messageToast" class="message-toast"></div>

    <!-- Main Split Layout -->
    <div class="split-layout">
        <!-- Left Side - Static Image -->
        <div class="left-side w-full md:w-1/2 h-full relative overflow-hidden">
            <img src="{{ asset('uploads/1000053201.jpg') }}" alt="Eurotaxisystem" class="w-full h-full object-cover">
            
            <!-- Overlay Content -->
            <div class="frosted-overlay flex flex-col">
                <!-- Logo at top -->
                <div class="text-center px-8 pt-16">
                    <div class="logo-container mx-auto logo-bounce">
                        <img src="{{ asset('uploads/logo.png') }}" alt="Eurotaxisystem Logo" class="logo-image">
                    </div>
                </div>

                <!-- Icons -->
                <div class="flex-1 flex items-start justify-center px-8 pt-16">
                    <div class="flex justify-center gap-8">
                        <div class="text-center icon-animate-1">
                            <i class="fas fa-users text-3xl mb-2 text-white text-shadow-light"></i>
                            <p class="text-sm text-white text-shadow-light">200+ Drivers</p>
                        </div>
                        <div class="text-center icon-animate-2">
                            <i class="fas fa-route text-3xl mb-2 text-white text-shadow-light"></i>
                            <p class="text-sm text-white text-shadow-light">10K+ Trips</p>
                        </div>
                        <div class="text-center icon-animate-3">
                            <i class="fas fa-car text-3xl mb-2 text-white text-shadow-light"></i>
                            <p class="text-sm text-white text-shadow-light">94 Units</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Side - OTP Form -->
        <div class="right-side w-full md:w-1/2 h-full">
            <div class="form-wrapper">
                <div class="w-full mb-6">
                    <a href="{{ route('login') }}" class="back-link">
                        <i class="fas fa-arrow-left mr-2"></i> Back to login
                    </a>
                </div>
                
                <div class="w-16 h-16 bg-blue-100 rounded-full flex mx-auto items-center justify-center mb-6 text-blue-600">
                    <i class="fas {{ (session('registration_data')['otp_method'] ?? 'email') === 'sms' ? 'fa-mobile-alt' : 'fa-envelope-open-text' }} text-3xl"></i>
                </div>
                
                <h2 class="text-2xl font-bold text-gray-800 text-center mb-2">Verify Your {{ (session('registration_data')['otp_method'] ?? 'email') === 'sms' ? 'Phone' : 'Email' }}</h2>
                <p class="text-gray-600 text-center mb-8">
                    We've sent a 6-digit verification code to <br>
                    <span class="font-semibold text-gray-800">
                        @if((session('registration_data')['otp_method'] ?? 'email') === 'sms')
                            {{ session('registration_data')['phone'] ?? 'your phone' }}
                        @else
                            {{ session('registration_data')['email'] ?? 'your email' }}
                        @endif
                    </span>.
                </p>

                @if($errors->any())
                    <div class="w-full mb-6 p-3 bg-red-50 border border-red-200 rounded-lg text-red-700 text-sm flex items-start">
                         <i class="fas fa-exclamation-circle mt-0.5 mr-2"></i>
                         <span>{{ $errors->first() }}</span>
                    </div>
                @endif

                <form id="otpForm" method="POST" action="{{ route('verify-otp.submit') }}" class="w-full flex flex-col items-center relative">
                    @csrf
                    
                    <input type="text" id="hiddenOtp" name="otp" maxlength="6" value="" autocomplete="one-time-code" required>
                    
                    <div class="otp-inputs" dir="ltr">
                        @for($i = 0; $i < 6; $i++)
                            <input type="text" class="otp-input" maxlength="1" data-index="{{ $i }}" inputmode="numeric" pattern="[0-9]*" autocomplete="off">
                        @endfor
                    </div>

                    <button type="submit" class="btn-primary" id="verifyBtn">
                        Verify Account <i class="fas fa-check-circle ml-2"></i>
                    </button>
                </form>

                <div class="mt-8 text-center text-sm">
                    <p class="text-gray-600 mb-2">Didn't receive the code?</p>
                    <button type="button" id="resendBtn" class="resend-btn" onclick="resendOTP()">
                        Resend OTP <span id="countdownText" style="display:none;">(<span id="countdown">60</span>s)</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        const hiddenInput = document.getElementById('hiddenOtp');
        const inputs = document.querySelectorAll('.otp-input');
        
        inputs.forEach((input, index) => {
            input.addEventListener('focus', () => {
                let targetIndex = 0;
                for (let i = 0; i < inputs.length; i++) {
                    if (!inputs[i].value) {
                        targetIndex = i;
                        break;
                    }
                    if (i === inputs.length - 1) {
                         targetIndex = i; 
                    }
                }
                if (index !== targetIndex && input.value === '') {
                    inputs[targetIndex].focus();
                }
            });

            input.addEventListener('keydown', (e) => {
                if (e.key === 'Backspace') {
                    if (input.value === '') {
                        if (index > 0) {
                            inputs[index - 1].focus();
                            inputs[index - 1].value = '';
                            inputs[index - 1].classList.remove('filled');
                        }
                    } else {
                        input.value = '';
                        input.classList.remove('filled');
                    }
                    updateHiddenInput();
                    e.preventDefault();
                } else if (e.key === 'ArrowRight') {
                     if (index < inputs.length - 1) inputs[index + 1].focus();
                } else if (e.key === 'ArrowLeft') {
                     if (index > 0) inputs[index - 1].focus();
                }
            });

            input.addEventListener('input', (e) => {
                const val = e.target.value.replace(/[^0-9]/g, '');
                e.target.value = val;
                
                if (val) {
                    input.classList.add('filled');
                    if (index < inputs.length - 1) {
                        inputs[index + 1].focus();
                    }
                }
                updateHiddenInput();
            });
        });

        document.addEventListener('paste', e => {
             const pastedData = e.clipboardData.getData('text').replace(/[^0-9]/g, '').slice(0, 6);
             if (pastedData) {
                  e.preventDefault();
                  for(let i=0; i<inputs.length; i++) {
                       if (pastedData[i]) {
                            inputs[i].value = pastedData[i];
                            inputs[i].classList.add('filled');
                       } else {
                            inputs[i].value = '';
                            inputs[i].classList.remove('filled');
                       }
                  }
                  if (pastedData.length < 6) {
                       inputs[pastedData.length].focus();
                  } else {
                       inputs[5].focus();
                  }
                  updateHiddenInput();
             }
        });

        function updateHiddenInput() {
             let otp = Array.from(inputs).map(i => i.value).join('');
             hiddenInput.value = otp;
        }

        let resendTimer = null;
        
        function resendOTP() {
            const btn = document.getElementById('resendBtn');
            if (btn.disabled) return;
            
            btn.disabled = true;
            
            fetch("{{ route('resend-otp') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content,
                    "Accept": "application/json"
                }
            })
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    showToast(data.message, 'success');
                    startCountdown();
                } else {
                    showToast(data.message || 'Error occurred.', 'error');
                    btn.disabled = false;
                }
            })
            .catch(err => {
                console.error('Resend Error:', err);
                btn.disabled = false;
                showToast('Failed to resend. Check internet connection or server logs.', 'error');
            });
        }

        function startCountdown() {
            let seconds = 120;
            const btn = document.getElementById('resendBtn');
            const countdownText = document.getElementById('countdownText');
            const span = document.getElementById('countdown');
            
            btn.disabled = true;
            countdownText.style.display = 'inline';
            span.textContent = seconds;
            
            resendTimer = setInterval(() => {
                seconds--;
                span.textContent = seconds;
                if (seconds <= 0) {
                    clearInterval(resendTimer);
                    btn.disabled = false;
                    countdownText.style.display = 'none';
                }
            }, 1000);
        }

        function showToast(message, type = 'success') {
            const toast = document.getElementById('messageToast');
            toast.textContent = message;
            if(type === 'success') toast.innerHTML = '<i class="fas fa-check-circle mr-2"></i>' + message;
            else if(type === 'error') toast.innerHTML = '<i class="fas fa-exclamation-circle mr-2"></i>' + message;
            else if(type === 'info') toast.innerHTML = '<i class="fas fa-info-circle mr-2"></i>' + message;

            toast.className = 'message-toast ' + type + ' show';
            setTimeout(() => { toast.classList.remove('show'); }, 4000);
        }

        @if(session('info'))
            showToast("{{ session('info') }}", 'info');
        @endif
        @if(session('success'))
            showToast("{{ session('success') }}", 'success');
        @endif
        @if(session('error'))
            showToast("{{ session('error') }}", 'error');
        @endif

        window.onload = () => {
             inputs[0].focus();
        };
    </script>
</body>
</html>
