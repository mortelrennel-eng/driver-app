<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use App\Models\User;

class AuthController extends Controller
{
    /**
     * Handle an authentication attempt via email OR phone number.
     */
    public function login(Request $request)
    {
        // Debug logging
        Log::info('Raw Login Request:', [
            'all' => $request->all(),
            'input' => $request->input(),
            'json' => $request->json()->all(),
            'content_type' => $request->header('Content-Type')
        ]);

        // Fallback for login field
        $loginValue = $request->login ?? $request->email ?? $request->username;
        
        // Inject back into request for validation if needed, or just validate manually
        if ($loginValue) {
            $request->merge(['login' => $loginValue]);
        }

        $validator = Validator::make($request->all(), [
            'login'       => 'required|string',
            'password'    => 'required|string',
            'device_name' => 'sometimes|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error: ' . $validator->errors()->first(),
                'debug_received' => $request->all()
            ], 422);
        }

        $user = User::withTrashed()
            ->where(function ($query) use ($loginValue) {
                $query->where('email', $loginValue)
                      ->orWhere('phone', $loginValue)
                      ->orWhere('phone_number', $loginValue);
            })
            ->first();

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials.',
            ], 401);
        }

        // Block archived/soft-deleted accounts
        if ($user->trashed()) {
            return response()->json([
                'success' => false,
                'message' => 'Your account is disabled.',
            ], 403);
        }

        // Block disabled accounts
        if ($user->is_disabled) {
            return response()->json([
                'success' => false,
                'message' => $user->disable_reason ?? 'Your account has been temporarily disabled by the Owner/Super Admin.',
            ], 403);
        }

        // Block pending or rejected accounts
        if (in_array($user->approval_status ?? 'approved', ['pending', 'rejected'])) {
            $msg = $user->approval_status === 'pending'
                ? 'Your account is pending approval by the system owner. Kindly wait or contact Robert Garcia.'
                : 'Your account registration has been rejected. Please contact the system owner.';
            
            return response()->json([
                'success' => false,
                'message' => $msg,
            ], 403);
        }

        // Block inactive accounts
        if (! $user->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Your account is inactive.',
            ], 403);
        }

        // Get the user with raw DB to bypass Eloquent hidden fields
        $rawUser = \DB::table('users')->where('id', $user->id)->first();
        
        // Try password_hash first (used by web), then password column
        $storedHash = $rawUser->password_hash ?? $rawUser->password ?? null;

        Log::info('Login attempt', [
            'user_id'     => $user->id,
            'has_hash'    => !empty($storedHash),
            'hash_type'   => $storedHash ? (str_starts_with($storedHash, '$2y$') ? 'bcrypt' : 'other') : 'null',
        ]);

        if (! $storedHash ||
            ! (Hash::check($request->password, $storedHash) ||
               password_verify($request->password, $storedHash))
        ) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials.',
            ], 401);
        }

        // ─── MFA / DEVICE VERIFICATION CHECK ───
        $deviceName = $request->device_name ?? 'Unknown Mobile Device';
        
        // Use a more unique token that includes the user ID to avoid collisions between users
        $deviceToken = hash('sha256', $user->id . '|' . $deviceName);
        
        // We check if this device name (acting as browser token) is recognized
        $isRecognized = $user->verifiedBrowsers()
            ->where('browser_token', $deviceToken)
            ->exists();

        // If not recognized, trigger MFA
        if (!$isRecognized) {
            return response()->json([
                'success'      => true,
                'mfa_required' => true,
                'user_id'      => encrypt($user->id), // Send encrypted ID for security
                'email'        => $user->email,
                'phone'        => $user->phone_number ?? $user->phone,
                'message'      => 'A new device was detected. Please verify your identity.'
            ]);
        }

        // If recognized, login normally
        $token = $user->createToken($deviceName)->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'token'   => $token,
            'user'    => [
                'id'    => $user->id,
                'name'  => $user->full_name ?? $user->name,
                'email' => $user->email,
                'role'  => $user->role,
            ],
        ]);
    }

    /**
     * Send Device Verification OTP
     */
    public function sendDeviceOtp(Request $request)
    {
        $request->validate([
            'user_token' => 'required|string',
            'method'     => 'required|in:email,phone'
        ]);

        try {
            $userId = decrypt($request->user_token);
            $user = User::findOrFail($userId);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Invalid session token.'], 401);
        }

        $otp = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
        
        $user->update([
            'otp_code' => $otp,
            'otp_expires_at' => now()->addMinutes(5)
        ]);

        if ($request->input('method') === 'email') {
            require_once app_path('Helpers/MailerHelper.php');
            $emailBody = "
                <div style='font-family:Arial,sans-serif;max-width:480px;margin:0 auto;padding:24px;background:#f9fafb;border-radius:12px;'>
                    <h2 style='color:#1d4ed8;margin-bottom:8px;'>New Device Login Detected</h2>
                    <p style='color:#374151;'>A login attempt was made from a new device on the mobile app. Use the verification code below to authorize this device:</p>
                    <div style='background:#1d4ed8;color:#fff;font-size:2rem;font-weight:bold;letter-spacing:0.5rem;text-align:center;padding:18px;border-radius:8px;margin:20px 0;'>{$otp}</div>
                </div>
            ";
            if (!send_custom_email($user->email, 'Eurotaxisystem Mobile — Device Verification Code', $emailBody)) {
                return response()->json(['success' => false, 'message' => 'Failed to send verification email.'], 500);
            }
        } else {
            $phone = $user->phone_number ?? $user->phone;
            if (!$phone) {
                return response()->json(['success' => false, 'message' => 'No phone number found.'], 422);
            }
            $message = "Your EuroTaxi device verification code is: {$otp}. Valid for 5 minutes.";
            send_sms_otp($phone, $message, $otp);
        }

        return response()->json(['success' => true, 'message' => 'Verification code sent!']);
    }

    /**
     * Verify Device OTP and generate login token
     */
    public function verifyDeviceOtp(Request $request)
    {
        $request->validate([
            'user_token'  => 'required|string',
            'otp'         => 'required|string|size:6',
            'device_name' => 'required|string'
        ]);

        try {
            $userId = decrypt($request->user_token);
            $user = User::findOrFail($userId);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Invalid session token.'], 401);
        }

        if ($user->otp_code !== $request->otp || now()->gt($user->otp_expires_at)) {
            return response()->json(['success' => false, 'message' => 'Invalid or expired code.'], 422);
        }

        // Verify device
        $deviceName = $request->device_name;
        $deviceToken = hash('sha256', $user->id . '|' . $deviceName);
        
        // Check if already exists to avoid duplicate entry error
        $existing = $user->verifiedBrowsers()->where('browser_token', $deviceToken)->first();
        
        if ($existing) {
            $existing->update([
                'ip_address'    => $request->ip(),
                'user_agent'    => $request->userAgent() ?? 'Eurotaxi Mobile App',
                'last_active_at'=> now(),
            ]);
        } else {
            $user->verifiedBrowsers()->create([
                'browser_token' => $deviceToken,
                'ip_address'    => $request->ip(),
                'user_agent'    => $request->userAgent() ?? 'Eurotaxi Mobile App',
                'verified_at'   => now(),
                'last_active_at'=> now(),
            ]);
        }

        $user->update(['otp_code' => null, 'otp_expires_at' => null]);

        $token = $user->createToken($deviceName)->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Device verified and login successful',
            'token'   => $token,
            'user'    => [
                'id'    => $user->id,
                'name'  => $user->full_name ?? $user->name,
                'email' => $user->email,
                'role'  => $user->role,
            ],
        ]);
    }

    /**
     * Send Reset OTP via Email or SMS
     */
    public function sendResetOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'identifier' => 'required|string',
            'method'     => 'required|in:email,phone'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        $identifier = $request->identifier;
        $method = $request->input('method');
        
        // Clean identifier if it looks like a phone number
        $cleanIdentifier = preg_replace('/[^0-9]/', '', $identifier);
        // If it's a 10 or 11 digit number, try matching last 10 digits too
        $phoneSuffix = strlen($cleanIdentifier) >= 10 ? substr($cleanIdentifier, -10) : null;

        $user = User::where(function($q) use ($identifier, $cleanIdentifier, $phoneSuffix) {
            $q->where('email', $identifier)
              ->orWhere('phone', $identifier)
              ->orWhere('phone', $cleanIdentifier)
              ->orWhere('phone_number', $identifier)
              ->orWhere('phone_number', $cleanIdentifier);
            
            if ($phoneSuffix) {
                $q->orWhere('phone', 'LIKE', '%' . $phoneSuffix)
                  ->orWhere('phone_number', 'LIKE', '%' . $phoneSuffix);
            }
        })->first();

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'No account found with this information.'], 404);
        }

        $otp = sprintf("%06d", mt_rand(1, 999999));
        $user->update([
            'otp_code' => $otp,
            'otp_expires_at' => now()->addMinutes(10)
        ]);

        if ($method === 'email') {
            require_once app_path('Helpers/MailerHelper.php');
            $body = "<h2>Password Reset</h2><p>Your OTP code is: <b>{$otp}</b></p>";
            if (!send_custom_email($user->email, "Eurotaxi - Password Reset OTP", $body)) {
                return response()->json(['success' => false, 'message' => 'Failed to send email.'], 500);
            }
        } else {
            $phone = $user->phone_number ?? $user->phone;
            $message = "Your Euro Taxi reset code is: {$otp}. Valid for 10 mins.";
            if (!send_sms_otp($phone, $message, $otp)) {
                return response()->json(['success' => false, 'message' => 'Failed to send SMS.'], 500);
            }
        }

        return response()->json(['success' => true, 'message' => 'OTP sent successfully!']);
    }

    /**
     * Verify OTP
     */
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'identifier' => 'required|string',
            'otp'        => 'required|string|size:6'
        ]);

        $user = User::where(function($q) use ($request) {
            $q->where('email', $request->identifier)
              ->orWhere('phone', $request->identifier)
              ->orWhere('phone_number', $request->identifier);
        })
        ->where('otp_code', $request->otp)
        ->where('otp_expires_at', '>', now())
        ->first();

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Invalid or expired OTP.'], 400);
        }

        return response()->json(['success' => true, 'message' => 'OTP verified.']);
    }

    /**
     * Reset Password
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'identifier' => 'required|string',
            'otp'        => 'required|string|size:6',
            'password'   => 'required|string|min:6|confirmed'
        ]);

        $user = User::where(function($q) use ($request) {
            $q->where('email', $request->identifier)
              ->orWhere('phone', $request->identifier)
              ->orWhere('phone_number', $request->identifier);
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
     * Log the user out (revoke token).
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully',
        ]);
    }
}
