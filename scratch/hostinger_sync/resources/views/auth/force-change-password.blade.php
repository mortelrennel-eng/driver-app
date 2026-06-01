<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password — Eurotaxi Fleet Management</title>
    <link rel="stylesheet" href="{{ asset('assets/inter/inter.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/fontawesome/all.min.css') }}">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            background: linear-gradient(135deg, #0f172a 0%, #1e3a5f 50%, #0f172a 100%);
            display: flex; align-items: center; justify-content: center;
            padding: 1.5rem;
        }
        .card {
            background: white;
            border-radius: 1.5rem;
            box-shadow: 0 40px 80px rgba(0,0,0,0.5);
            width: 100%;
            max-width: 460px;
            overflow: hidden;
        }
        .card-header {
            background: linear-gradient(135deg, #1e3a5f 0%, #2563eb 100%);
            padding: 2rem 2.5rem 1.8rem;
            text-align: center;
        }
        .lock-icon {
            width: 64px; height: 64px;
            background: rgba(255,255,255,0.15);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 1rem;
            border: 2px solid rgba(255,255,255,0.3);
        }
        .card-header h1 { color: white; font-size: 1.4rem; font-weight: 800; margin-bottom: 0.3rem; }
        .card-header p { color: rgba(255,255,255,0.75); font-size: 0.82rem; }
        .card-body { padding: 2rem 2.5rem; }
        .alert-box {
            background: #fef3c7; border: 1px solid #fcd34d;
            border-radius: 0.75rem; padding: 0.9rem 1rem;
            display: flex; align-items: flex-start; gap: 0.7rem;
            margin-bottom: 1.5rem;
        }
        .alert-box i { color: #d97706; margin-top: 1px; flex-shrink: 0; }
        .alert-box p { font-size: 0.8rem; color: #92400e; line-height: 1.5; }
        .input-group { margin-bottom: 1rem; }
        .input-label { display: block; font-size: 0.78rem; font-weight: 700; color: #374151; margin-bottom: 0.4rem; letter-spacing: 0.03em; text-transform: uppercase; }
        .input-wrapper {
            position: relative;
            display: flex; align-items: center;
            border: 2px solid #e5e7eb; border-radius: 0.75rem;
            background: #f9fafb; transition: all 0.2s;
        }
        .input-wrapper:focus-within { border-color: #2563eb; background: white; box-shadow: 0 0 0 4px rgba(37,99,235,0.08); }
        .input-wrapper i.icon { padding: 0 0.75rem; color: #9ca3af; font-size: 0.85rem; }
        .input-wrapper input {
            flex: 1; border: none; background: transparent; padding: 0.7rem 0;
            font-size: 0.88rem; color: #1f2937; outline: none;
        }
        .input-wrapper button.toggle {
            background: none; border: none; padding: 0 0.75rem; cursor: pointer;
            color: #9ca3af; font-size: 0.85rem; transition: color 0.2s;
        }
        .input-wrapper button.toggle:hover { color: #2563eb; }
        .strength-bar { height: 4px; border-radius: 2px; background: #e5e7eb; margin-top: 0.4rem; overflow: hidden; }
        .strength-fill { height: 100%; border-radius: 2px; width: 0%; transition: all 0.3s ease; }
        .strength-text { font-size: 0.7rem; font-weight: 600; margin-top: 0.25rem; text-align: right; }
        .error-msg { font-size: 0.72rem; color: #ef4444; margin-top: 0.25rem; display: none; }
        .btn-submit {
            width: 100%; padding: 0.85rem;
            background: linear-gradient(135deg, #1e3a5f, #2563eb);
            color: white; border: none; border-radius: 0.75rem;
            font-size: 0.95rem; font-weight: 700; cursor: pointer;
            transition: all 0.2s; letter-spacing: 0.04em; margin-top: 0.5rem;
        }
        .btn-submit:hover { transform: translateY(-1px); box-shadow: 0 8px 20px rgba(37,99,235,0.35); }
        .btn-submit:disabled { opacity: 0.6; cursor: not-allowed; transform: none; }
        .error-alert { background: #fef2f2; border: 1px solid #fecaca; border-radius: 0.75rem; padding: 0.8rem 1rem; margin-bottom: 1rem; color: #dc2626; font-size: 0.82rem; }
        .rules { margin: 0.8rem 0; padding: 0.8rem 1rem; background: #f0f9ff; border: 1px solid #bae6fd; border-radius: 0.75rem; }
        .rules p { font-size: 0.75rem; color: #0369a1; font-weight: 600; margin-bottom: 0.4rem; }
        .rules ul { list-style: none; }
        .rules li { font-size: 0.73rem; color: #0c4a6e; margin-bottom: 0.2rem; }
        .rules li i { margin-right: 0.4rem; width: 12px; }
        .rules li.ok { color: #16a34a; }
        .rules li.ok i { color: #16a34a; }
    </style>
</head>
<body>
    <div class="card">
        <div class="card-header">
            <div class="lock-icon">
                <i class="fas fa-lock text-white text-2xl"></i>
            </div>
            <h1>Set Your New Password</h1>
            <p>For your security, please change your temporary password before continuing.</p>
        </div>
        <div class="card-body">
            @if($errors->any())
                <div class="error-alert">
                    <i class="fas fa-exclamation-circle mr-2"></i>{{ $errors->first() }}
                </div>
            @endif

            <div class="alert-box">
                <i class="fas fa-exclamation-triangle"></i>
                <p>Your account was created by an administrator. You must set a new personal password to access the system.</p>
            </div>

            <form method="POST" action="{{ route('auth.force-change-password.update') }}" id="changeForm">
                @csrf

                <div class="input-group">
                    <label class="input-label">Temporary Password (sent to your email)</label>
                    <div class="input-wrapper">
                        <i class="fas fa-key icon"></i>
                        <input type="password" name="current_password" id="currentPw" placeholder="Enter your temporary password" required>
                        <button type="button" class="toggle" onclick="togglePw('currentPw', this)"><i class="fas fa-eye-slash"></i></button>
                    </div>
                </div>

                <div class="rules" id="pwRules">
                    <p><i class="fas fa-info-circle"></i> Password must:</p>
                    <ul>
                        <li id="rule-len"><i class="fas fa-times-circle" style="color:#ef4444"></i> Be at least 8 characters</li>
                        <li id="rule-upper"><i class="fas fa-times-circle" style="color:#ef4444"></i> Contain an uppercase letter</li>
                        <li id="rule-num"><i class="fas fa-times-circle" style="color:#ef4444"></i> Contain a number</li>
                        <li id="rule-sym"><i class="fas fa-times-circle" style="color:#ef4444"></i> Contain a symbol (!@#$...)</li>
                    </ul>
                </div>

                <div class="input-group">
                    <label class="input-label">New Password</label>
                    <div class="input-wrapper">
                        <i class="fas fa-lock icon"></i>
                        <input type="password" name="new_password" id="newPw" placeholder="Create a strong password" required minlength="8" oninput="checkStrength(this.value)">
                        <button type="button" class="toggle" onclick="togglePw('newPw', this)"><i class="fas fa-eye-slash"></i></button>
                    </div>
                    <div class="strength-bar"><div class="strength-fill" id="strengthFill"></div></div>
                    <div class="strength-text" id="strengthText"></div>
                </div>

                <div class="input-group">
                    <label class="input-label">Confirm New Password</label>
                    <div class="input-wrapper">
                        <i class="fas fa-lock icon"></i>
                        <input type="password" name="new_password_confirmation" id="confirmPw" placeholder="Re-enter your new password" required oninput="checkMatch()">
                        <button type="button" class="toggle" onclick="togglePw('confirmPw', this)"><i class="fas fa-eye-slash"></i></button>
                    </div>
                    <div class="error-msg" id="matchError">Passwords do not match.</div>
                </div>

                <button type="submit" class="btn-submit" id="submitBtn">
                    <i class="fas fa-shield-alt mr-2"></i> Save New Password & Continue
                </button>
            </form>
        </div>
    </div>

    <script>
        function togglePw(id, btn) {
            const input = document.getElementById(id);
            const isHidden = input.type === 'password';
            input.type = isHidden ? 'text' : 'password';
            btn.querySelector('i').className = isHidden ? 'fas fa-eye' : 'fas fa-eye-slash';
        }

        function checkRule(id, pass) {
            const el = document.getElementById(id);
            if (pass) {
                el.classList.add('ok');
                el.querySelector('i').className = 'fas fa-check-circle';
                el.querySelector('i').style.color = '#16a34a';
            } else {
                el.classList.remove('ok');
                el.querySelector('i').className = 'fas fa-times-circle';
                el.querySelector('i').style.color = '#ef4444';
            }
        }

        function checkStrength(val) {
            const len   = val.length >= 8;
            const upper = /[A-Z]/.test(val);
            const num   = /[0-9]/.test(val);
            const sym   = /[^A-Za-z0-9]/.test(val);

            checkRule('rule-len',   len);
            checkRule('rule-upper', upper);
            checkRule('rule-num',   num);
            checkRule('rule-sym',   sym);

            const score = [len, upper, num, sym].filter(Boolean).length;
            const fill  = document.getElementById('strengthFill');
            const text  = document.getElementById('strengthText');
            const colors = ['#ef4444','#f97316','#eab308','#22c55e'];
            const labels = ['Weak','Fair','Good','Strong'];

            fill.style.width   = (score * 25) + '%';
            fill.style.background = colors[score - 1] || '#e5e7eb';
            text.textContent   = val.length ? labels[score - 1] || '' : '';
            text.style.color   = colors[score - 1] || '#9ca3af';
        }

        function checkMatch() {
            const newPw  = document.getElementById('newPw').value;
            const confPw = document.getElementById('confirmPw').value;
            const errEl  = document.getElementById('matchError');
            errEl.style.display = (confPw && newPw !== confPw) ? 'block' : 'none';
        }

        document.getElementById('changeForm').addEventListener('submit', function(e) {
            const newPw  = document.getElementById('newPw').value;
            const confPw = document.getElementById('confirmPw').value;
            if (newPw !== confPw) {
                e.preventDefault();
                document.getElementById('matchError').style.display = 'block';
                return;
            }
            const btn = document.getElementById('submitBtn');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Saving...';
        });
    </script>
</body>
</html>
