<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\VerifiedBrowser;
use App\Models\Unit;
use App\Models\Driver;
use App\Models\LoginAudit;


class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        $unitsCount = Unit::count();
        $driversCount = Driver::count();

        return view('auth.login', compact('unitsCount', 'driversCount'));
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = User::withTrashed()
            ->where(function($query) use ($request) {
                $query->where('email', $request->email)
                    ->orWhere('username', $request->email);
            })
            ->first();

        if ($user) {
            // Block archived/soft-deleted accounts
            if ($user->trashed()) {
                LoginAudit::log('failed_login', $user, 'Login blocked: account archived/disabled.');
                
                $reason = 'Your account is disabled.';
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'account_disabled' => true,
                        'message' => $reason,
                    ]);
                }
                
                return back()->withErrors(['email' => $reason])->onlyInput('email');
            }

            // Block disabled accounts
            if ($user->is_disabled) {
                $reason = $user->disable_reason ?? 'Your account has been temporarily disabled by the Owner/Super Admin.';
                LoginAudit::log('failed_login', $user, 'Login blocked: account disabled. Reason: ' . $reason);
                
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'account_disabled' => true,
                        'message' => $reason,
                    ]);
                }
                
                return back()->withErrors(['email' => $reason])->onlyInput('email');
            }

            // Block pending or rejected accounts
            if (in_array($user->approval_status ?? 'approved', ['pending', 'rejected'])) {
                LoginAudit::log('failed_login', $user, 'Login blocked: account ' . ($user->approval_status) . '.');
                
                $msg = $user->approval_status === 'pending'
                    ? 'Your account is pending approval by the system owner. Kindly wait or contact Robert Garcia.'
                    : 'Your account registration has been rejected. Please contact the system owner.';
                
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => $msg,
                    ], 403);
                }
                
                return back()->withErrors(['email' => $msg])->onlyInput('email');
            }

            // Block only accounts explicitly set to unverified (0 or false)
            if ($user->is_verified !== null && !$user->is_verified) {
                LoginAudit::log('failed_login', $user, 'Login blocked: email not verified.');
                
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Please verify your email address before logging in.',
                    ], 403);
                }
                
                return back()->withErrors(['email' => 'Please verify your email address before logging in.'])->onlyInput('email');
            }

            // Block inactive accounts
            if (!$user->is_active) {
                LoginAudit::log('failed_login', $user, 'Login blocked: account inactive.');
                
                $msg = 'Your account is inactive. Please contact the system owner.';
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => $msg,
                    ], 403);
                }
                
                return back()->withErrors(['email' => $msg])->onlyInput('email');
            }

            // Support both 'password' and legacy 'password_hash' column names
            $storedHash = $user->password ?? $user->password_hash ?? null;

            if (
                $storedHash && (
                    Hash::check($request->password, $storedHash) ||
                    password_verify($request->password, $storedHash)
                )
            ) {
                // ─── NEW DEVICE CHECK ──────────────────
                $browserCookie = $request->cookie('browser_id');
                $isRecognized  = false;

                if ($browserCookie) {
                    $isRecognized = $user->verifiedBrowsers()
                        ->where('browser_token', hash('sha256', $browserCookie))
                        ->exists();
                }

                if (!$isRecognized) {
                    // Save user ID in session temporarily, but DO NOT log in yet
                    $request->session()->put('mfa_user_id', $user->id);
                    $request->session()->put('mfa_remember', $request->boolean('remember'));

                    return response()->json([
                        'mfa_required' => true,
                        'email'        => $user->email,
                        'phone'        => $user->phone_number ?? $user->phone,
                        'message'      => 'A new device was detected. Please verify your identity.'
                    ]);
                }

                // ─── FORCE PASSWORD CHANGE CHECK ───────────────────────────────
                if ($user->must_change_password) {
                    $request->session()->put('force_pwd_user_id', $user->id);
                    $request->session()->put('force_pwd_remember', $request->boolean('remember'));
                    
                    if ($request->ajax() || $request->wantsJson()) {
                        return response()->json([
                            'force_password_change' => true,
                            'message' => 'Please update your temporary password.'
                        ]);
                    }
                    return redirect()->route('login');
                }

                // Device is recognized and no forced password change, log in normally
                // Force Remember Me (always persistent) for stable mobile/web session experience!
                Auth::login($user, true);

                // Save FCM token if it was stored in session while they were a guest
                $fcmToken = session('fcm_token');
                if ($fcmToken) {
                    try {
                        $user->update(['fcm_token' => $fcmToken]);
                    } catch (\Exception $e) {
                        \Illuminate\Support\Facades\DB::table('users')->where('id', $user->id)->update(['fcm_token' => $fcmToken]);
                    }
                }

                $request->session()->regenerate();

                // Track last login and log audit
                $user->update(['last_login' => now()]);
                LoginAudit::log('login', $user);

                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => true,
                        'redirect' => route('dashboard')
                    ]);
                }

                return redirect()->intended(route('dashboard'))
                    ->with('success', 'Welcome back, ' . ($user->full_name ?? $user->name) . '!');
            }
        }

        // Log failed login attempt
        LoginAudit::log('failed_login', null, 'Failed login for: ' . ($request->email ?? 'unknown'));

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid email or password.'
            ], 401);
        }

        return back()->withErrors([
            'email' => 'Invalid email or password.',
        ])->onlyInput('email');
    }

    // ─── FORCE PASSWORD CHANGE ────────────────────────────────────────────────
    public function showForceChangePassword()
    {
        if (!Auth::check()) return redirect()->route('login');
        if (!Auth::user()->must_change_password) return redirect()->route('dashboard');
        return view('auth.force-change-password');
    }

    public function updateForceChangePassword(Request $request)
    {
        $userId = $request->session()->get('force_pwd_user_id');
        if (!$userId) {
            return response()->json(['success' => false, 'message' => 'Session expired. Please log in again.']);
        }

        $request->validate([
            'current_password'      => 'required|string',
            'new_password'          => [
                'required',
                'string',
                'min:8',
                'confirmed',
                'regex:/^(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9])[A-Za-z\d\D]{8,}$/'
            ],
        ], [
            'new_password.regex' => 'Password must contain at least 1 uppercase letter, 1 number, and 1 special character.'
        ]);

        $user = User::find($userId);
        $storedHash = $user->password ?? $user->password_hash ?? null;

        // Verify temporary password
        if (!$storedHash || !Hash::check($request->current_password, $storedHash)) {
            return response()->json(['success' => false, 'message' => 'The temporary password you entered is incorrect.']);
        }

        // Save new password and clear force-change flag
        $user->update([
            'password'             => Hash::make($request->new_password),
            'password_hash'        => Hash::make($request->new_password),
            'must_change_password' => false,
            'temp_password'        => null,
        ]);

        // Log them in now
        // Force Remember Me (always persistent) for stable mobile/web session experience!
        Auth::login($user, true);
        $request->session()->forget(['force_pwd_user_id', 'force_pwd_remember']);
        $request->session()->regenerate();
        $user->update(['last_login' => now()]);

        LoginAudit::log('password_changed', $user, 'Staff forced password change completed.');
        LoginAudit::log('login', $user);

        return response()->json([
            'success' => true, 
            'message' => 'Password updated successfully! Welcome to Eurotaxi.',
            'redirect' => route('dashboard')
        ]);
    }

    public function sendDeviceOtp(Request $request)
    {
        $request->validate([
            'method' => 'required|in:email,phone'
        ]);

        $userId = $request->session()->get('mfa_user_id');
        if (!$userId) {
            return response()->json(['success' => false, 'message' => 'Session expired. Please log in again.'], 401);
        }

        $user = User::find($userId);
        $otp = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
        
        $user->update([
            'otp_code' => $otp,
            'otp_expires_at' => now()->addMinutes(10)
        ]);

        if ($request->input('method') === 'email') {
            $emailBody = "
                <div style='font-family:Arial,sans-serif;max-width:480px;margin:0 auto;padding:24px;background:#f9fafb;border-radius:12px;'>
                    <h2 style='color:#1d4ed8;margin-bottom:8px;'>New Device Login Detected</h2>
                    <p style='color:#374151;'>Hello <strong>{$user->first_name}</strong>,</p>
                    <p style='color:#374151;'>A login attempt was made from a new device. Use the verification code below to authorize this browser:</p>
                    <div style='background:#1d4ed8;color:#fff;font-size:2rem;font-weight:bold;letter-spacing:0.5rem;text-align:center;padding:18px;border-radius:8px;margin:20px 0;'>{$otp}</div>
                    <p style='color:#6b7280;font-size:0.85rem;'>If this wasn't you, we recommend changing your password immediately.</p>
                </div>
            ";
            if (!send_custom_email($user->email, 'Eurotaxisystem — Device Verification Code', $emailBody)) {
                return response()->json(['success' => false, 'message' => 'Failed to send verification email. Please check your email configuration.'], 500);
            }
        } else {
            // SMS logic
            $phone = $user->phone_number ?? $user->phone;
            if (!$phone) {
                return response()->json(['success' => false, 'message' => 'No phone number found for this account.'], 422);
            }
            send_sms_otp($phone, $otp);
        }

        return response()->json(['success' => true, 'message' => 'Verification code sent!']);
    }

    public function verifyDeviceOtp(Request $request)
    {
        $request->validate([
            'otp' => 'required|string|size:6'
        ]);

        $userId = $request->session()->get('mfa_user_id');
        if (!$userId) {
            return response()->json(['success' => false, 'message' => 'Session expired.'], 401);
        }

        $user = User::find($userId);

        if ($user->otp_code !== $request->otp || now()->gt($user->otp_expires_at)) {
            return response()->json(['success' => false, 'message' => 'Invalid or expired code.'], 422);
        }

        // ─── VERIFICATION SUCCESS ───────────────
        
        // 1. Generate unique device ID
        $deviceId = Str::random(64);
        $deviceToken = hash('sha256', $deviceId);
        
        // 2. Save to database
        // Use updateOrCreate or check existence to be absolutely safe against rare random collisions or double-submission
        $user->verifiedBrowsers()->updateOrCreate(
            ['browser_token' => $deviceToken],
            [
                'ip_address'    => $request->ip(),
                'user_agent'    => $request->userAgent(),
                'verified_at'   => now(),
                'last_active_at'=> now(),
            ]
        );

        // 3. Clear OTP
        $user->update(['otp_code' => null, 'otp_expires_at' => null]);

        // ─── FORCE PASSWORD CHANGE CHECK ───────────────────────────────
        if ($user->must_change_password) {
            $request->session()->put('force_pwd_user_id', $user->id);
            $request->session()->put('force_pwd_remember', $request->session()->get('mfa_remember', false));
            $request->session()->forget(['mfa_user_id', 'mfa_remember']);
            
            return response()->json([
                'force_password_change' => true,
                'message' => 'Please update your temporary password.'
            ])->cookie('browser_id', $deviceId, 60 * 24 * 365);
        }

        // 4. Log in and audit
        // Force Remember Me (always persistent) for stable mobile/web session experience!
        Auth::login($user, true);

        // Save FCM token if it was stored in session while they were a guest
        $fcmToken = session('fcm_token');
        if ($fcmToken) {
            try {
                $user->update(['fcm_token' => $fcmToken]);
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\DB::table('users')->where('id', $user->id)->update(['fcm_token' => $fcmToken]);
            }
        }

        $request->session()->forget(['mfa_user_id', 'mfa_remember']);
        $request->session()->regenerate();
        $user->update(['last_login' => now()]);
        LoginAudit::log('login', $user, 'Login via MFA device verification.');

        // 5. Return success with the cookie (expires in 1 year)
        return response()->json([
            'success' => true,
            'redirect' => route('dashboard')
        ])->cookie('browser_id', $deviceId, 60 * 24 * 365);
    }

    public function register(Request $request)
    {
        $request->validate([
            'first_name'    => ['required', 'string', 'max:25', 'regex:/^[a-zA-ZñÑ]+( [a-zA-ZñÑ]+)?$/'],
            'middle_name'   => ['nullable', 'string', 'max:25', 'regex:/^[a-zA-ZñÑ]+$/'],
            'last_name'     => ['required', 'string', 'max:25', 'regex:/^[a-zA-ZñÑ]+( [a-zA-ZñÑ]+)?$/'],
            'suffix'        => ['nullable', 'in:,N/A,Jr.,Sr.,II,III,IV,V'],
            'phone_number'  => ['required', 'string', 'regex:/^9[0-9]{9}$/'],
            'email'         => ['required', 'email', 'unique:users,email', 'regex:/^(?=[^@]*[a-zA-Z])(?!\.)(?!.*\.{2})[a-zA-Z0-9][a-zA-Z0-9.]{4,28}[a-zA-Z0-9]@gmail\.com$/i'],
            'password'      => ['required', 'string', 'min:6', 'confirmed', 'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9])[A-Za-z\d\D]{6,}$/'],
            'role'          => 'required|in:staff,secretary,manager,dispatcher',
        ], [
            'first_name.regex'      => 'First name must contain letters only. A single space is permitted.',
            'first_name.max'        => 'First name must not exceed 25 characters.',
            'middle_name.regex'     => 'Middle name must contain letters only.',
            'middle_name.max'       => 'Middle name must not exceed 25 characters.',
            'last_name.regex'       => 'Last name must contain letters only. A single space is permitted.',
            'last_name.max'         => 'Last name must not exceed 25 characters.',
            'phone_number.regex'    => 'Phone number must be a valid Philippine number starting with 9 followed by 9 digits.',
            'phone_number.required' => 'Phone number is required.',
            'email.regex'           => 'Only Gmail addresses are accepted (e.g. yourname@gmail.com).',
            'email.unique'          => 'This email already has an existing account.',
            'password.regex'        => 'Password must contain at least one uppercase letter, one lowercase letter, one number, and one symbol.',
            'password.min'          => 'Password must be at least 6 characters long.',
            'password.confirmed'    => 'Passwords do not match.',
        ]);

        // Generate username based on role and first name
        $rolePrefix = $request->role;
        $firstName  = strtolower(str_replace(' ', '', $request->first_name));
        $username   = $rolePrefix . '-' . $firstName;

        // Ensure unique username (check against existing DB users)
        $originalUsername = $username;
        $counter = 1;
        while (User::where('username', $username)->exists()) {
            $username = $originalUsername . '-' . $counter;
            $counter++;
        }

        // Build the full display name
        $middleInitial = $request->middle_name ? ' ' . strtoupper(substr($request->middle_name, 0, 1)) . '.' : '';
        $suffixPart    = $request->suffix ? ' ' . $request->suffix : '';
        $fullName      = $request->first_name . $middleInitial . ' ' . $request->last_name . $suffixPart;

        require_once app_path('Helpers/MailerHelper.php');

        // Generate OTP
        $otp       = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
        $expiresAt = now()->addMinutes(10)->toDateTimeString();

        // ─── Store everything in session — NO DATABASE WRITE YET ───────────
        $request->session()->put('pending_registration', [
            'full_name'    => $fullName,
            'first_name'   => $request->first_name,
            'middle_name'  => $request->middle_name,
            'last_name'    => $request->last_name,
            'suffix'       => $request->suffix,
            'phone_number' => '0' . ltrim($request->phone_number, '0'),
            'email'        => $request->email,
            'username'     => $username,
            'password'     => Hash::make($request->password),
            'role'         => $request->role,
            'otp_code'     => $otp,
            'otp_expires_at' => $expiresAt,
        ]);

        // Send OTP email
        $emailBody = "
            <div style='font-family:Arial,sans-serif;max-width:480px;margin:0 auto;padding:24px;background:#f9fafb;border-radius:12px;'>
                <h2 style='color:#1d4ed8;margin-bottom:8px;'>Eurotaxisystem &mdash; Verify Your Email</h2>
                <p style='color:#374151;'>Hello <strong>{$request->first_name}</strong>,</p>
                <p style='color:#374151;'>Use the code below to verify your email and complete registration:</p>
                <div style='background:#1d4ed8;color:#fff;font-size:2rem;font-weight:bold;letter-spacing:0.5rem;text-align:center;padding:18px;border-radius:8px;margin:20px 0;'>{$otp}</div>
                <p style='color:#6b7280;font-size:0.85rem;'>This code expires in <strong>10 minutes</strong>. Do not share it with anyone.</p>
                <p style='color:#6b7280;font-size:0.85rem;'>If you did not register, you can safely ignore this email.</p>
                <hr style='border:none;border-top:1px solid #e5e7eb;margin:20px 0;'>
                <p style='color:#9ca3af;font-size:0.75rem;text-align:center;'>Eurotaxisystem &copy; 2025</p>
            </div>
        ";

        if (!send_custom_email(
            $request->email,
            'Eurotaxisystem — Email Verification Code',
            $emailBody
        )) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to send verification email. Please check your email address or try again later.',
                ], 500);
            }
            return back()->withErrors(['email' => 'Failed to send verification email. Please check your configuration.'])->withInput();
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Please check your email for the verification code.',
                'email'   => $request->email,
            ]);
        }

        return redirect()->route('login')
            ->with('info', 'Please check your email for the verification code.');
    }

    /**
     * Verify email OTP at registration — creates user only after success
     */
    public function verifyRegistrationOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp'   => 'required|string|size:6',
        ]);

        $pending = $request->session()->get('pending_registration');

        // Validate session data exists and matches
        if (
            !$pending ||
            $pending['email'] !== $request->email ||
            $pending['otp_code'] !== $request->otp
        ) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired code. Please try again.',
            ], 422);
        }

        // Check expiry
        if (now()->gt($pending['otp_expires_at'])) {
            $request->session()->forget('pending_registration');
            return response()->json([
                'success' => false,
                'message' => 'Your verification code has expired. Please register again.',
            ], 422);
        }

        // ─── OTP confirmed — NOW create the user in the database ───────────
        // New accounts are PENDING until approved by Super Admin
        $user = User::create([
            'full_name'       => $pending['full_name'],
            'first_name'      => $pending['first_name'],
            'middle_name'     => $pending['middle_name'],
            'last_name'       => $pending['last_name'],
            'suffix'          => $pending['suffix'],
            'phone_number'    => $pending['phone_number'],
            'email'           => $pending['email'],
            'username'        => $pending['username'],
            'password'        => $pending['password'],
            'password_hash'   => $pending['password'],
            'role'            => $pending['role'],
            'is_active'       => false,           // inactive until approved
            'is_verified'     => true,             // email verified
            'approval_status' => 'pending',        // awaiting Super Admin approval
        ]);

        // Clear the session
        $request->session()->forget('pending_registration');

        return response()->json([
            'success' => true,
            'message' => 'Email verified! Your account is pending approval by the system owner. You will be notified once approved.',
        ]);
    }

    /**
     * Resend registration OTP — updates session only, no DB involved
     */
    public function resendRegistrationOtp(Request $request)
    {
        require_once app_path('Helpers/MailerHelper.php');

        $request->validate(['email' => 'required|email']);

        $pending = $request->session()->get('pending_registration');

        if (!$pending || $pending['email'] !== $request->email) {
            return response()->json([
                'success' => false,
                'message' => 'No pending registration found. Please fill in the form again.',
            ], 404);
        }

        // Generate new OTP and update session
        $otp = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
        $pending['otp_code']       = $otp;
        $pending['otp_expires_at'] = now()->addMinutes(10)->toDateTimeString();
        $request->session()->put('pending_registration', $pending);

        $firstName = $pending['first_name'];
        $emailBody = "
            <div style='font-family:Arial,sans-serif;max-width:480px;margin:0 auto;padding:24px;background:#f9fafb;border-radius:12px;'>
                <h2 style='color:#1d4ed8;margin-bottom:8px;'>Eurotaxisystem &mdash; New Verification Code</h2>
                <p style='color:#374151;'>Hello <strong>{$firstName}</strong>,</p>
                <p style='color:#374151;'>Here is your new verification code:</p>
                <div style='background:#1d4ed8;color:#fff;font-size:2rem;font-weight:bold;letter-spacing:0.5rem;text-align:center;padding:18px;border-radius:8px;margin:20px 0;'>{$otp}</div>
                <p style='color:#6b7280;font-size:0.85rem;'>This code expires in <strong>10 minutes</strong>.</p>
            </div>
        ";

        if (!send_custom_email($pending['email'], 'Eurotaxisystem — New Verification Code', $emailBody)) {
            return response()->json(['success' => false, 'message' => 'Failed to resend verification email. Please check your configuration.'], 500);
        }

        return response()->json(['success' => true, 'message' => 'A new code has been sent to your email.']);
    }

    public function showRegister()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.register');
    }

    public function logout(Request $request)
    {
        $user = Auth::user();
        if ($user) {
            LoginAudit::log('logout', $user);
        }
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }

    /**
     * Send Reset OTP via Email
     */
    public function sendResetOtp(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email'
        ], [
            'email.exists' => 'No account found with this email address.',
            'email.email' => 'Please enter a valid email address.',
            'email.required' => 'Email address is required.'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false, 
                'message' => $validator->errors()->first('email')
            ], 422);
        }
        
        $user = User::where('email', $request->email)->first();
        $otp = sprintf("%06d", mt_rand(1, 999999));
        
        $user->update([
            'otp_code' => $otp,
            'otp_expires_at' => now()->addMinutes(10)
        ]);

        $body = "<h2>Password Reset</h2>
                 <p>Your OTP code for password reset is: <b>{$otp}</b></p>
                 <p>This code will expire in 10 minutes.</p>";
        
        if (send_custom_email($request->email, "Password Reset OTP - Euro Taxi System", $body)) {
            return response()->json(['success' => true, 'message' => 'OTP sent to your email.']);
        }

        return response()->json(['success' => false, 'message' => 'Service unavailable. Please try again later.'], 500);
    }

    /**
     * Send Reset OTP via SMS
     */
    public function sendSmsResetOtp(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'phone' => 'required|string|min:10|exists:users,phone_number'
        ], [
            'phone.exists' => 'This phone number is not registered in our system.',
            'phone.required' => 'Phone number is required.',
            'phone.min' => 'Please enter a valid phone number.'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false, 
                'message' => $validator->errors()->first('phone')
            ], 422);
        }
        
        // Sanitize for DB search (09...)
        $searchPhone = $request->phone;
        if (str_starts_with($searchPhone, '+63')) {
            $searchPhone = '0' . ltrim($searchPhone, '+63');
        } elseif (!str_starts_with($searchPhone, '0')) {
            $searchPhone = '0' . $searchPhone;
        }

        $user = User::where('phone_number', $searchPhone)->first();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'This phone number is not registered in our system.'], 422);
        }

        $otp = sprintf("%06d", mt_rand(1, 999999));
        
        $user->update([
            'otp_code' => $otp,
            'otp_expires_at' => now()->addMinutes(10)
        ]);

        $message = "Your Euro Taxi reset code is: {$otp}. Valid for 10 mins.";
        
        // Sanitize for Semaphore (+63...)
        $smsPhone = $searchPhone;
        if (str_starts_with($smsPhone, '0')) {
            $smsPhone = '+63' . ltrim($smsPhone, '0');
        }
        
        if (send_sms_otp($smsPhone, $message, $otp)) {
            return response()->json(['success' => true, 'message' => 'OTP sent to your phone.']);
        }

        return response()->json(['success' => false, 'message' => 'SMS service temporarily unavailable.'], 500);
    }

    /**
     * Verify Reset OTP (Unified for Email/Phone)
     */
    public function verifyResetOtp(Request $request)
    {
        $request->validate([
            'identifier' => 'required|string',
            'otp' => 'required|string|size:6'
        ]);

        $identifier = $request->identifier;
        if (!filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
            // Sanitize phone for DB lookup
            $identifier = ltrim($identifier, '+63');
            if (!str_starts_with($identifier, '0')) $identifier = '0' . $identifier;
        }

        $user = User::where(function($q) use ($identifier) {
                        $q->where('email', $identifier)
                          ->orWhere('phone_number', $identifier);
                    })
                    ->where('otp_code', $request->otp)
                    ->where('otp_expires_at', '>', now())
                    ->first();

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Invalid or expired OTP.'], 400);
        }

        return response()->json(['success' => true, 'message' => 'OTP verified successfully.']);
    }

    /**
     * Reset Password (Unified for Email/Phone)
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'identifier' => 'required|string',
            'otp' => 'required|string|size:6',
            'password' => 'required|string|min:6|confirmed'
        ]);

        $identifier = $request->identifier;
        if (!filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
            $identifier = ltrim($identifier, '+63');
            if (!str_starts_with($identifier, '0')) $identifier = '0' . $identifier;
        }

        $user = User::where(function($q) use ($identifier) {
                        $q->where('email', $identifier)
                          ->orWhere('phone_number', $identifier);
                    })
                    ->where('otp_code', $request->otp)
                    ->where('otp_expires_at', '>', now())
                    ->first();

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Invalid or expired OTP.'], 400);
        }

        $user->update([
            'password' => Hash::make($request->password),
            'password_hash' => Hash::make($request->password),
            'otp_code' => null,
            'otp_expires_at' => null
        ]);

        return response()->json(['success' => true, 'message' => 'Password reset successfully!']);
    }

    /**
     * Check if email or phone is already registered (AJAX)
     */
    public function checkAvailability(Request $request)
    {
        if ($request->has('email')) {
            $exists = User::where('email', $request->email)->exists();
            return response()->json(['available' => !$exists, 'message' => $exists ? 'This email is already registered.' : '']);
        }

        if ($request->has('phone')) {
            $phone = $request->phone;
            // standard sanitization for lookup
            $phone = ltrim($phone, '+63');
            if (!str_starts_with($phone, '0')) $phone = '0' . $phone;

            $exists = User::where('phone_number', $phone)->exists();
            return response()->json(['available' => !$exists, 'message' => $exists ? 'This phone number is already registered.' : '']);
        }

        if ($request->has('first_name')) {
            $exists = User::where('first_name', $request->first_name)->exists();
            return response()->json(['available' => !$exists, 'message' => $exists ? 'This first name is already taken.' : '']);
        }

        return response()->json(['error' => 'Invalid request'], 400);
    }
}
