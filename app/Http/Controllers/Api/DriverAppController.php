<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\RescueRequest;
use App\Models\Driver;
use App\Models\Unit;
use App\Models\User;
use App\Models\Staff;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Services\TracksolidService;

class DriverAppController extends Controller
{
    protected $tracksolid;

    public function __construct(TracksolidService $tracksolid)
    {
        $this->tracksolid = $tracksolid;
    }
    /**
     * Step 1: Validate registration data, match driver record, and send SMS OTP.
     * Account is NOT created yet — only after OTP verification.
     */
    public function register(Request $request)
    {
        require_once app_path('Helpers/SemaphoreHelper.php');

        $validator = Validator::make($request->all(), [
            'name'         => ['required', 'string', 'max:255', 'regex:/^[a-zA-ZñÑ\s]+$/', function($attribute, $value, $fail) {
                                if (trim($value) === '') $fail('The name cannot be just spaces.');
                              }],
            'email'        => ['required', 'string', 'email', 'max:255', 'unique:users,email', function($attribute, $value, $fail) {
                                if (str_ends_with($value, '@gmail.com')) {
                                    $prefix = str_before($value, '@gmail.com');
                                    if (strlen($prefix) < 6) $fail('Gmail address must have at least 6 characters before @gmail.com.');
                                }
                              }],
            'phone'        => 'required|string|regex:/^09\d{9}$/',
            'password'     => 'required|string|min:8|confirmed',
            'plate_number' => 'required|string',
            'license_number' => 'nullable|string|max:50',
            'license_expiry' => 'nullable|date',
            'address'      => ['nullable', 'string', 'regex:/^[a-zA-Z0-9].*$/', 'regex:/^(?![0-9]+$).*$/'],
            'emergency_contact' => 'nullable|string|max:100',
            'emergency_phone'   => 'nullable|string|regex:/^09\d{9}$/',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        // Reject duplicate phone numbers
        $cleanPhone = preg_replace('/[^0-9]/', '', $request->phone);
        $existingByPhone = User::where('phone', $request->phone)
            ->orWhere('phone', $cleanPhone)
            ->orWhere('phone_number', $request->phone)
            ->first();
        if ($existingByPhone) {
            return response()->json([
                'success' => false,
                'message' => 'This phone number is already registered. Please log in instead.'
            ], 422);
        }

        // Find Unit
        $unit = Unit::where('plate_number', 'LIKE', '%' . trim($request->plate_number) . '%')->first();
        if (!$unit) {
            return response()->json([
                'success' => false,
                'message' => 'Vehicle plate number not found. Please contact the office.'
            ], 404);
        }

        // Find Driver
        $nameParts = explode(' ', $request->name, 2);
        $firstName = trim($nameParts[0]);
        $lastName = isset($nameParts[1]) ? trim($nameParts[1]) : '';

        $driver = null;
        $driverIds = array_filter([$unit->driver_id, $unit->secondary_driver_id]);
        if (!empty($driverIds)) {
            $driver = Driver::whereIn('id', $driverIds)
                ->where(function($q) use ($firstName, $lastName) {
                    $q->where('first_name', 'LIKE', '%' . $firstName . '%')
                      ->orWhere('last_name', 'LIKE', '%' . $lastName . '%');
                })
                ->whereNull('user_id')
                ->first();
        }
        if (!$driver) {
            $driver = Driver::where('first_name', 'LIKE', '%' . $firstName . '%')
                ->where('last_name', 'LIKE', '%' . $lastName . '%')
                ->whereNull('user_id')
                ->first();
        }
        if (!$driver) {
            return response()->json([
                'success' => false,
                'message' => 'Driver record not found or already registered. Please ensure your name matches the record in our system.'
            ], 404);
        }

        // Generate OTP and store pending registration in cache
        $otp = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
        $pendingKey = 'pending_reg_' . $cleanPhone;
        \Cache::put($pendingKey, [
            'name'              => $request->name,
            'email'             => $request->email,
            'phone'             => $request->phone,
            'password'          => $request->password,
            'plate_number'      => $request->plate_number,
            'license_number'    => $request->license_number,
            'license_expiry'    => $request->license_expiry,
            'address'           => $request->address,
            'emergency_contact' => $request->emergency_contact,
            'emergency_phone'   => $request->emergency_phone,
            'driver_id'         => $driver->id,
            'unit_id'           => $unit->id,
            'otp'               => $otp,
            'otp_expires_at'    => now()->addMinutes(5)->toDateTimeString(),
        ], now()->addMinutes(5));

        // Send SMS OTP
        $smsPhone = $request->phone;
        $smsMessage = "Your EuroTaxi registration code is: {$otp}. Valid for 5 minutes.";
        $smsSent = send_sms_otp($smsPhone, $smsMessage, $otp);

        if (!$smsSent) {
            \Log::warning("SMS OTP failed to send for registration. Phone: {$smsPhone}, OTP: {$otp}");
        }

        return response()->json([
            'success'   => true,
            'otp_sent'  => true,
            'message'   => 'Verification code sent to your phone. Enter the 6-digit code to complete registration.',
            'phone'     => $request->phone,
        ]);
    }

    /**
     * Step 2: Verify SMS OTP and create the driver account.
     */
    public function verifyRegistrationOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string',
            'otp'   => 'required|string|size:6',
        ]);
        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        $cleanPhone = preg_replace('/[^0-9]/', '', $request->phone);
        $pendingKey = 'pending_reg_' . $cleanPhone;
        $pending = \Cache::get($pendingKey);

        if (!$pending) {
            return response()->json(['success' => false, 'message' => 'Registration session expired. Please start over.'], 410);
        }
        if ($pending['otp'] !== $request->otp) {
            return response()->json(['success' => false, 'message' => 'Invalid verification code. Please try again.'], 422);
        }
        if (now()->gt($pending['otp_expires_at'])) {
            \Cache::forget($pendingKey);
            return response()->json(['success' => false, 'message' => 'Verification code has expired. Please resend.'], 410);
        }

        DB::beginTransaction();
        try {
            $driver = Driver::find($pending['driver_id']);
            $unit   = Unit::find($pending['unit_id']);
            if (!$driver || $driver->user_id) {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => 'Driver record is no longer available.'], 409);
            }

            $nameParts = explode(' ', $pending['name'], 2);
            $firstName = trim($nameParts[0]);
            $lastName  = isset($nameParts[1]) ? trim($nameParts[1]) : '';

            $user = User::create([
                'name'          => $pending['name'],
                'full_name'     => $pending['name'],
                'first_name'    => $firstName,
                'last_name'     => $lastName,
                'username'      => strtolower(str_replace(' ', '.', $pending['name'])) . rand(10, 99),
                'email'         => $pending['email'],
                'phone'         => $pending['phone'],
                'password'      => Hash::make($pending['password']),
                'password_hash' => Hash::make($pending['password']),
                'role'          => 'driver',
                'is_active'     => true,
                'approval_status' => 'approved',
                'is_verified'   => true,
            ]);

            $driver->user_id        = $user->id;
            $driver->contact_number = $pending['phone'];
            if (!empty($pending['license_expiry'])) $driver->license_expiry = $pending['license_expiry'];
            if (!empty($pending['address']))         $driver->address        = $pending['address'];
            $driver->save();

            if ($unit) {
                if ($unit->driver_id == $driver->id || $unit->secondary_driver_id == $driver->id) {
                    // already linked
                } elseif (!$unit->driver_id) {
                    $unit->driver_id = $driver->id;
                    $unit->status    = 'active';
                } elseif (!$unit->secondary_driver_id) {
                    $unit->secondary_driver_id = $driver->id;
                    $unit->status              = 'active';
                }
                $unit->save();
            }

            DB::commit();
            \Cache::forget($pendingKey);

            // AUTO-LOGIN: Generate token after successful verification
            $deviceName = $request->device_name ?? 'Mobile Device';
            $token = $user->createToken($deviceName)->plainTextToken;

            // Register this device as verified automatically since they just did OTP
            $deviceToken = hash('sha256', $user->id . '|' . $deviceName);
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

            return response()->json([
                'success' => true,
                'message' => 'Account created and verified successfully!',
                'token'   => $token,
                'user'    => [
                    'id'    => $user->id,
                    'name'  => $user->full_name ?? $user->name,
                    'email' => $user->email,
                    'role'  => $user->role,
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Registration failed: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Resend registration OTP.
     */
    public function resendRegistrationOtp(Request $request)
    {
        require_once app_path('Helpers/SemaphoreHelper.php');

        $validator = Validator::make($request->all(), ['phone' => 'required|string']);
        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        $cleanPhone = preg_replace('/[^0-9]/', '', $request->phone);
        $pendingKey = 'pending_reg_' . $cleanPhone;
        $pending = \Cache::get($pendingKey);

        if (!$pending) {
            return response()->json(['success' => false, 'message' => 'Registration session not found. Please start over.'], 410);
        }

        // Generate new OTP
        $otp = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
        $pending['otp'] = $otp;
        $pending['otp_expires_at'] = now()->addMinutes(10)->toDateTimeString();
        \Cache::put($pendingKey, $pending, now()->addMinutes(10));

        $smsMessage = "Your EuroTaxi registration code is: {$otp}. Valid for 10 minutes.";
        send_sms_otp($request->phone, $smsMessage, $otp);

        return response()->json(['success' => true, 'message' => 'A new verification code has been sent to your phone.']);
    }


    public function performance(Request $request)
    {
        $user = $request->user();
        \Log::info("Performance lookup started", ['user_id' => $user->id, 'user_name' => $user->name]);
        $driver = $this->resolveDriver($user);

        if (!$driver) {
            \Log::warning("Driver not found in performance lookup", ['user_id' => $user ? $user->id : 'null']);
            return response()->json([
                'success' => false, 
                'message' => 'Driver record not linked. User ID: ' . ($user ? $user->id : 'null') . '. Please contact the EuroTaxi office.'
            ], 404);
        }

        $today = now()->timezone('Asia/Manila')->toDateString();

        // Get assigned unit
        $unit = Unit::where('driver_id', $driver->id)
            ->orWhere('secondary_driver_id', $driver->id)
            ->whereNull('deleted_at')
            ->first();

        // Get boundary sum for today (matching system logic)
        $boundaryData = DB::table('boundaries')
            ->where('driver_id', $driver->id)
            ->whereDate('date', $today)
            ->whereNull('deleted_at')
            ->select([
                DB::raw('SUM(actual_boundary) as total_actual'),
                DB::raw('SUM(boundary_amount) as total_target'),
                DB::raw('SUM(shortage) as total_shortage'),
                DB::raw('SUM(excess) as total_excess'),
                DB::raw('MAX(status) as last_status')
            ])
            ->first();

        // Determine Target based on Rules (Sunday, Coding, etc.)
        $target_label = 'Regular Target';
        if ($boundaryData && $boundaryData->total_target > 0) {
            $target = $boundaryData->total_target;
            $target_label = 'Recorded Target';
        } else if ($unit) {
            $unitYear = (int) $unit->year;
            $rule = DB::table('boundary_rules')
                ->where('start_year', '<=', $unitYear)
                ->where('end_year', '>=', $unitYear)
                ->whereNull('deleted_at')
                ->first();

            if ($rule) {
                $baseRate = (float) $rule->regular_rate;
                $dayOfWeek = now()->timezone('Asia/Manila')->dayOfWeek; // 0=Sun, 6=Sat

                // 1. Check Coding First (Highest Priority)
                $lastDigit = (int) substr($unit->plate_number, -1);
                $codingDay = $this->getCodingDayForDigit($lastDigit);

                if ($dayOfWeek === $codingDay) {
                    $target = (float) $rule->coding_rate;
                    $target_label = 'Coding Target';
                } else if ($dayOfWeek === 0) { // Sunday
                    $target = $baseRate - (float) $rule->sun_discount;
                    $target_label = 'Sunday Target';
                } else if ($dayOfWeek === 6) { // Saturday
                    $target = $baseRate - (float) $rule->sat_discount;
                    $target_label = 'Saturday Target';
                } else {
                    $target = $baseRate;
                }
            } else {
                $target = $unit->boundary_rate ?: ($driver->daily_boundary_target ?: 0);
            }
        } else {
            $target = $driver->daily_boundary_target ?: 0;
        }

        $actual = $boundaryData ? (float) $boundaryData->total_actual : 0;
        $status = ($boundaryData && $boundaryData->last_status) ? $boundaryData->last_status : 'pending';
        $shortage = $boundaryData ? (float) $boundaryData->total_shortage : 0;
        $excess = $boundaryData ? (float) $boundaryData->total_excess : 0;

        $progress = $target > 0 ? ($actual / $target) * 100 : 0;

        $is_coding = false;
        $coding_message = 'No Coding Today';
        $next_coding_date = 'N/A';
        $coding_day_name = 'N/A';

        if ($unit) {
            $lastDigit = (int) substr($unit->plate_number, -1);
            $codingDay = $this->getCodingDayForDigit($lastDigit);
            $dayOfWeek = Carbon::now('Asia/Manila')->dayOfWeek;

            $daysOfWeekNames = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
            $coding_day_name = $daysOfWeekNames[$codingDay] ?? 'N/A';

            if ($dayOfWeek === $codingDay) {
                $is_coding = true;
                $coding_message = 'ALERTO: Coding po kayo ngayon!';
                $next_coding_date = Carbon::now('Asia/Manila')->format('Y-m-d');
            } else {
                $daysToAdd = ($codingDay - $dayOfWeek + 7) % 7;
                $next_coding_date = Carbon::now('Asia/Manila')->addDays($daysToAdd)->format('Y-m-d');
            }
        }

        $message = "Keep up the good work!";
        if ($progress < 50) {
            $message = "You're a bit behind your target. Keep going!";
        } elseif ($progress >= 100) {
            $message = "Target achieved! Great job!";
        }

        // Performance Metrics (Last 30 Days)
        $history = DB::table('boundaries')
            ->where('driver_id', $driver->id)
            ->where('date', '>=', Carbon::now()->subDays(30))
            ->whereNull('deleted_at')
            ->get();

        $daysCount = $history->count();
        $paidDays = $history->where('status', 'paid')->count();
        $totalTarget = $history->sum('boundary_amount');
        $totalActual = $history->sum('actual_boundary');

        $attendance_rate = $daysCount > 0 ? round(($paidDays / 30) * 100, 1) : 0;
        $efficiency_rate = $totalTarget > 0 ? round(($totalActual / $totalTarget) * 100, 1) : 0;

        // GPS Data
        $gps_status = 'Offline';
        $location = 'Unknown';
        $latitude = 0;
        $longitude = 0;
        $speed = 0;
        $ignition = false;
        $heading = 0;
        $last_update = null;
        $total_odo = 0;
        $daily_dist = 0;
        $age = 'N/A';

        if ($unit) {
            // SYNC CHECK: If local GPS data is older than 30 seconds, fetch live from Tracksolid
            $lastLocalGps = DB::table('gps_tracking')
                ->where('unit_id', $unit->id)
                ->first();

            $needsSync = true;
            if ($lastLocalGps && $lastLocalGps->updated_at) {
                $lastSync = Carbon::parse($lastLocalGps->updated_at);
                if ($lastSync->diffInSeconds(now()) < 30) {
                    $needsSync = false;
                }
            }

            if ($needsSync && $unit->imei) {
                \Log::info("Auto-syncing GPS for unit {$unit->plate_number} (Driver App Trigger)");
                $liveData = $this->tracksolid->getLocations([$unit->imei]);
                if ($liveData && isset($liveData[0])) {
                    $gps = $liveData[0];
                    $ignition = ($gps['accStatus'] ?? 0) == 1;
                    $speed = $ignition ? (float)($gps['speed'] ?? 0) : 0;
                    
                    DB::table('gps_tracking')->updateOrInsert(
                        ['unit_id' => $unit->id],
                        [
                            'latitude' => $gps['lat'],
                            'longitude' => $gps['lng'],
                            'speed' => $speed,
                            'heading' => $gps['direction'] ?? 0,
                            'ignition_status' => $ignition,
                            'timestamp' => $gps['gpsTime'] ?? now(),
                            'updated_at' => now()
                        ]
                    );
                }
            }

            $gps = DB::table('gps_tracking')
                ->where('unit_id', $unit->id)
                ->orderBy('timestamp', 'desc')
                ->first();

            if ($gps) {
                $latitude = (float) $gps->latitude;
                $longitude = (float) $gps->longitude;
                $speed = (float) $gps->speed;
                $ignition = (bool) $gps->ignition_status;
                $heading = (float) $gps->heading;
                $last_update = $gps->timestamp;

                // Determine Status (Matching LiveTrackingController logic)
                if ($last_update) {
                    $lastUpdateTs = strtotime($last_update . ' UTC');
                    $diff = time() - $lastUpdateTs;

                    if ($diff < 600) { // Within 10 minutes
                        if ($ignition) {
                            $gps_status = $speed > 2 ? 'Active' : 'Idle';
                        } else {
                            $gps_status = 'Stopped';
                        }
                    } else {
                        $gps_status = 'Offline';
                    }
                }

                $location = $gps->address ?? ($gps_status !== 'Offline' ? 'Active' : 'Signal Lost');
                $total_odo = (float) ($gps->odo ?? 0);

                // Calculate Daily Distance (Today's Distance)
                $todayStr = Carbon::now('Asia/Manila')->format('Y-m-d');
                if (($gps->daily_start_date ?? '') === $todayStr) {
                    $daily_dist = max(0, $total_odo - (float) ($gps->daily_start_mileage ?? $total_odo));
                }

                // Vehicle Age
                if ($unit->created_at) {
                    $months = Carbon::parse($unit->created_at)->diffInMonths(Carbon::now());
                    $age = round($months, 1) . ' months old';
                }
            }
        }

        // Profile Incomplete Check
        $profile_incomplete = false;
        if (!$driver->address || !$driver->emergency_contact || !$driver->emergency_phone || !$driver->license_number || !$driver->license_expiry) {
            $profile_incomplete = true;
        }

        return response()->json([
            'success' => true,
            'data' => [
                'driver_name' => $driver->first_name . ' ' . $driver->last_name,
                'unit' => $unit ? $unit->plate_number : 'NO UNIT',
                'license_number' => $driver->license_number,
                'phone' => $driver->contact_number,
                'address' => $driver->address,
                'emergency_contact' => $driver->emergency_contact,
                'emergency_phone' => $driver->emergency_phone,

                'location' => $location,
                'latitude' => $latitude,
                'longitude' => $longitude,
                'speed' => $speed,
                'ignition' => $ignition,
                'heading' => $heading,
                'gps_status' => $gps_status,
                'last_update' => $last_update ? Carbon::parse($last_update)->diffForHumans() : 'N/A',
                'boundary_target' => (float)$target,
                'boundary_target_label' => $target_label,
                'boundary_actual' => (float)$actual,
                'boundary_shortage' => (float)$shortage,
                'boundary_excess' => (float)$excess,
                'progress' => round($progress, 1),
                'boundary_status' => strtoupper($status),
                'message' => $message,
                'attendance_rate' => $attendance_rate,
                'efficiency_rate' => $efficiency_rate,
                'is_coding' => $is_coding,
                'coding_message' => $coding_message,
                'next_coding_date' => $next_coding_date,
                'coding_day_name' => $coding_day_name,
                'today_dist' => round($daily_dist, 2),
                'total_odo' => round($total_odo, 1),
                'age' => $age,
                'profile_incomplete' => $profile_incomplete,
            ]
        ]);
    }

    protected function getCodingDayForDigit($digit)
    {
        if ($digit == 1 || $digit == 2)
            return 1;
        if ($digit == 3 || $digit == 4)
            return 2;
        if ($digit == 5 || $digit == 6)
            return 3;
        if ($digit == 7 || $digit == 8)
            return 4;
        if ($digit == 9 || $digit == 0)
            return 5;
        return null;
    }

    public function requestRescue(Request $request)
    {
        $user = $request->user();
        $driver = $this->resolveDriver($user);

        if (!$driver) {
            return response()->json(['success' => false, 'message' => 'Driver record not found.'], 404);
        }

        $request->validate([
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'notes' => 'nullable|string',
        ]);

        $unit = Unit::where('driver_id', $driver->id)
            ->orWhere('secondary_driver_id', $driver->id)
            ->whereNull('deleted_at')
            ->first();

        $rescue = RescueRequest::create([
            'driver_id' => $driver->id,
            'unit_id' => $unit ? $unit->id : null,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'notes' => $request->notes,
            'status' => 'pending',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Rescue request sent successfully',
            'data' => $rescue
        ]);
    }

    public function updateLocation(Request $request)
    {
        $user = $request->user();
        $driver = $this->resolveDriver($user);

        if (!$driver) {
            return response()->json(['success' => false, 'message' => 'Driver record not found.'], 404);
        }

        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'speed' => 'nullable|numeric',
            'heading' => 'nullable|string',
        ]);

        $unit = Unit::where('driver_id', $driver->id)
            ->orWhere('secondary_driver_id', $driver->id)
            ->whereNull('deleted_at')
            ->first();

        if (!$unit) {
            return response()->json(['success' => false, 'message' => 'No assigned unit found for tracking'], 404);
        }

        DB::table('gps_tracking')->updateOrInsert(
            ['unit_id' => $unit->id],
            [
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'speed' => $request->speed ?? 0,
                'heading' => $request->heading ?? 0,
                'ip_address' => $request->ip(),
                'device_id' => $request->device_id,
                'timestamp' => now(),
                'updated_at' => now(),
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Location updated'
        ]);
    }
    public function earnings(Request $request)
    {
        $user = $request->user();
        $driver = $this->resolveDriver($user);

        if (!$driver) {
            return response()->json(['success' => false, 'message' => 'Driver record not found.'], 404);
        }

        $earnings = DB::table('boundaries')
            ->where('driver_id', $driver->id)
            ->whereNull('deleted_at')
            ->select([
                'id',
                'date',
                'boundary_amount',
                'actual_boundary',
                'status',
                'shortage',
                'excess',
                'notes'
            ])
            ->orderBy('date', 'desc')
            ->limit(30)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $earnings
        ]);
    }

    public function vehicleDetails(Request $request)
    {
        $user = $request->user();
        $driver = $this->resolveDriver($user);

        if (!$driver) {
            return response()->json(['success' => false, 'message' => 'Driver record not found.'], 404);
        }

        $unit = Unit::where('driver_id', $driver->id)
            ->orWhere('secondary_driver_id', $driver->id)
            ->whereNull('deleted_at')
            ->first();

        if (!$unit) {
            return response()->json(['success' => false, 'message' => 'No unit assigned'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'plate_number' => $unit->plate_number,
                'model' => $unit->model,
                'brand' => $unit->brand,
                'odo' => $unit->current_gps_odo ?: $unit->current_odo,
                'maintenance_status' => $unit->status,
                'registration_date' => $unit->registration_date
            ]
        ]);
    }

    /**
     * Delete (Archive) the driver's own account from the mobile app.
     * Unlinks the driver record from the user account so driver data is preserved.
     */
    public function deleteAccount(Request $request)
    {
        $user = $request->user();
        
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 401);
        }

        try {
            // 1. Unlink the Driver record if it exists
            \App\Models\Driver::where('user_id', $user->id)->update(['user_id' => null]);

            // 2. Archive the User account (Soft Delete)
            $user->delete();

            return response()->json([
                'success' => true,
                'message' => 'Account deleted successfully. Your driver records have been preserved for management.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting account: ' . $e->getMessage()
            ], 500);
        }
    }

    public function boundaryHistory(Request $request)
    {
        $user = $request->user();
        $driver = $this->resolveDriver($user);
        if (!$driver)
            return response()->json(['success' => false, 'message' => 'Driver not found'], 404);
        

        $boundaries = DB::table('boundaries')
            ->leftJoin('units', 'boundaries.unit_id', '=', 'units.id')
            ->where('boundaries.driver_id', $driver->id)
            ->whereNull('boundaries.deleted_at')
            ->orderByDesc('boundaries.date')
            ->select(
                'boundaries.*',
                'units.plate_number',
                DB::raw("IF(boundaries.expected_driver_id != boundaries.driver_id AND boundaries.expected_driver_id IS NOT NULL, 1, boundaries.is_extra_driver) as is_extra")
            )
            ->get();

        return response()->json(['success' => true, 'data' => $boundaries]);
    }

    public function incidents(Request $request)
    {
        $user = $request->user();
        $driver = $this->resolveDriver($user);
        if (!$driver) return response()->json(['success' => false, 'message' => 'Driver not found'], 404);

        $incidents = DB::table('driver_behavior')
            ->leftJoin('units', 'driver_behavior.unit_id', '=', 'units.id')
            ->where('driver_behavior.driver_id', $driver->id)
            ->whereNull('driver_behavior.deleted_at')
            ->select([
                'driver_behavior.*',
                'units.plate_number'
            ])
            ->orderByDesc('driver_behavior.incident_date')
            ->orderByDesc('driver_behavior.timestamp')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $incidents
        ]);
    }

    public function chargesIncentives(Request $request)
    {
        $user = $request->user();
        $driver = $this->resolveDriver($user);
        if (!$driver)
            return response()->json(['success' => false, 'message' => 'Driver not found'], 404);

        $charges = DB::table('driver_behavior')
            ->where('driver_id', $driver->id)
            ->where('charge_status', 'pending')
            ->where('remaining_balance', '>', 0)
            ->whereNull('deleted_at')
            ->orderByDesc('incident_date')
            ->get();

        $incentives = DB::table('boundaries')
            ->where('driver_id', $driver->id)
            ->where('has_incentive', 1)
            ->whereNull('deleted_at')
            ->orderByDesc('date')
            ->get();

        return response()->json([
            'success' => true,
            'charges' => $charges,
            'incentives' => $incentives,
        ]);
    }

    public function nearby(Request $request)
    {
        $user = $request->user();
        $driver = $this->resolveDriver($user);
        
        if (!$driver) return response()->json(['success' => false, 'message' => 'Driver not found'], 404);

        $lat = $request->input('lat');
        $lng = $request->input('lng');

        if (!$lat || !$lng) {
             return response()->json(['success' => true, 'nearby' => []]);
        }

        // Global Sync Check: If last global sync was > 2 minutes ago, sync all units
        $lastGlobalSync = \Cache::get('last_global_gps_sync');
        if (!$lastGlobalSync || now()->diffInMinutes($lastGlobalSync) >= 2) {
            \Log::info("Triggering global GPS sync (Driver Nearby Trigger)");
            $liveData = $this->tracksolid->getAllLocations();
            if ($liveData) {
                foreach ($liveData as $gps) {
                    $imei = $gps['imei'] ?? null;
                    if (!$imei) continue;
                    
                    $unit = Unit::where('imei', $imei)->first();
                    if ($unit) {
                        $ignition = ($gps['accStatus'] ?? 0) == 1;
                        $speed = $ignition ? (float)($gps['speed'] ?? 0) : 0;
                        
                        DB::table('gps_tracking')->updateOrInsert(
                            ['unit_id' => $unit->id],
                            [
                                'latitude' => $gps['lat'],
                                'longitude' => $gps['lng'],
                                'speed' => $speed,
                                'heading' => $gps['direction'] ?? 0,
                                'ignition_status' => $ignition,
                                'timestamp' => $gps['gpsTime'] ?? now(),
                                'updated_at' => now()
                            ]
                        );
                    }
                }
                \Cache::put('last_global_gps_sync', now(), 120); // Store for 2 mins
            }
        }

        $nearby = DB::table('units')
            ->join('gps_tracking', 'units.id', '=', 'gps_tracking.unit_id')
            ->leftJoin('drivers', 'units.driver_id', '=', 'drivers.id')
            ->whereNotNull('units.driver_id')
            ->where('units.driver_id', '!=', $driver->id)
            ->where('gps_tracking.timestamp', '>=', now('UTC')->subHours(12))
            ->select([
                'units.plate_number',
                'gps_tracking.latitude',
                'gps_tracking.longitude',
                'gps_tracking.speed',
                'gps_tracking.ignition_status',
                'gps_tracking.timestamp',
                'drivers.first_name',
                'drivers.last_name',
                DB::raw("ROUND(ST_Distance_Sphere(point(gps_tracking.longitude, gps_tracking.latitude), point($lng, $lat)) / 1000, 2) as distance")
            ])
            ->having('distance', '<=', 30) // 30 kilometers
            ->orderBy('distance')
            ->get();

        $nearby->transform(function ($item) {
            $gps_status = 'Offline';
            if ($item->timestamp) {
                $lastUpdateTs = strtotime($item->timestamp . ' UTC');
                $diff = time() - $lastUpdateTs;
                if ($diff < 600) { // Within 10 minutes
                    if ($item->ignition_status) {
                        $gps_status = $item->speed > 2 ? 'Moving' : 'Idle';
                    } else {
                        $gps_status = 'Stopped';
                    }
                }
            }
            $item->gps_status = $gps_status;
            return $item;
        });

        return response()->json([
            'success' => true,
            'nearby' => $nearby
        ]);
    }

    public function changePassword(Request $request)
    {
        $user = $request->user();
        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json(['success' => false, 'message' => 'Current password is incorrect'], 400);
        }

        if (strlen($request->new_password) < 8) {
            return response()->json(['success' => false, 'message' => 'New password must be at least 8 characters'], 400);
        }

        if ($request->new_password !== $request->new_password_confirmation) {
            return response()->json(['success' => false, 'message' => 'New password confirmation does not match'], 400);
        }

        $user->password = Hash::make($request->new_password);
        $user->password_hash = Hash::make($request->new_password);
        $user->save();

        return response()->json(['success' => true, 'message' => 'Password updated successfully']);
    }

    public function getProfile(Request $request)
    {
        $user = $request->user();
        $driver = $this->resolveDriver($user);

        return response()->json([
            'success' => true,
            'data' => [
                'name' => $driver ? ($driver->first_name . ' ' . $driver->last_name) : $user->full_name,
                'email' => $user->email,
                'phone' => $driver ? $driver->contact_number : $user->phone,
                'address' => $driver ? $driver->address : '',
                'license_number' => $driver ? $driver->license_number : '',
                'license_expiry' => $driver ? $driver->license_expiry : '',
                'emergency_contact' => $driver ? $driver->emergency_contact : '',
                'emergency_phone' => $driver ? $driver->emergency_phone : '',
            ]
        ]);
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();
        $driver = $this->resolveDriver($user);

        $request->validate([
            'name' => ['required', 'string', 'max:255', 'regex:/^[a-zA-ZñÑ\s]+$/', function($attribute, $value, $fail) {
                        if (trim($value) === '') $fail('The name cannot be just spaces.');
                      }],
            'phone' => 'nullable|string|regex:/^09\d{9}$/',
            'license_number' => 'nullable|string|max:50',
            'license_expiry' => 'nullable|date',
            'address' => ['nullable', 'string', 'regex:/^[a-zA-Z0-9].*$/', 'regex:/^(?![0-9]+$).*$/'],
            'emergency_contact' => 'nullable|string|max:100',
            'emergency_phone' => 'nullable|string|regex:/^09\d{9}$/',
        ]);

        // Update User
        $user->full_name = $request->name;
        $user->phone = $request->phone;
        $user->save();

        // Update Driver record
        if ($driver) {
            $nameParts = explode(' ', $request->name, 2);
            $driver->first_name = trim($nameParts[0]);
            $driver->last_name = isset($nameParts[1]) ? trim($nameParts[1]) : '';
            $driver->contact_number = $request->phone;
            $driver->license_number = $request->license_number;
            $driver->license_expiry = $request->license_expiry;
            $driver->address = $request->address;
            $driver->emergency_contact = $request->emergency_contact;
            $driver->emergency_phone = $request->emergency_phone;
            $driver->save();
        }

        return response()->json(['success' => true, 'message' => 'Profile updated successfully']);
    }

    /**
     * Get historical performance data for charts.
     */
    public function getPerformanceHistory()
    {
        $user = Auth::user();
        $driver = $user->driver;

        if (!$driver) {
            return response()->json(['success' => false, 'message' => 'Driver record not found.'], 404);
        }

        // Fetch last 7 days of boundaries
        $history = \App\Models\Boundary::where('driver_id', $driver->id)
            ->where('date', '>=', now()->subDays(7)->toDateString())
            ->whereNull('deleted_at')
            ->orderBy('date', 'asc')
            ->get(['date', 'actual_boundary', 'boundary_amount as target_boundary']);

        return response()->json([
            'success' => true,
            'history' => $history
        ]);
    }

    /**
     * Upload driver documents (License, Clearances, Profile Photo).
     */
    public function uploadDocuments(Request $request)
    {
        $request->validate([
            'document_type' => 'required|string|in:license,nbi,pnp,profile',
            'file' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $user = Auth::user();
        $driver = $user->driver;

        if (!$driver) {
            return response()->json(['success' => false, 'message' => 'Driver record not found.'], 404);
        }

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $type = $request->document_type;

            // Define filename
            $filename = $type . '_' . $driver->id . '_' . time() . '.' . $file->getClientOriginalExtension();

            // Move to storage
            $destinationPath = public_path('uploads/driver_docs');
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0755, true);
            }

            $file->move($destinationPath, $filename);
            $path = 'uploads/driver_docs/' . $filename;

            // Update database column based on type
            switch ($type) {
                case 'license':
                    $driver->license_photo = $path;
                    break;
                case 'nbi':
                    $driver->nbi_clearance_photo = $path;
                    break;
                case 'pnp':
                    $driver->pnp_clearance_photo = $path;
                    break;
                case 'profile':
                    $driver->profile_photo = $path;
                    break;
            }

            $driver->save();

            return response()->json([
                'success' => true,
                'message' => ucfirst($type) . ' photo uploaded successfully.',
                'path' => asset($path)
            ]);
        }

        return response()->json(['success' => false, 'message' => 'No file uploaded.'], 400);
    }

    public function saveNotificationToken(Request $request)
    {
        $user = $request->user();
        if ($request->token) {
            // Save token logic (e.g. to users table or device_tokens)
            DB::table('users')->where('id', $user->id)->update(['fcm_token' => $request->token]);
            return response()->json(['success' => true]);
        }
        return response()->json(['success' => false], 400);
    }

    /**
     * Robustly resolves a Driver record for a given User.
     * Tries relationship, direct ID match, phone match, and name match.
     */
    private function resolveDriver($user)
    {
        if (!$user) return null;

        // 1. Direct ID match (Highest Priority)
        $driver = Driver::where('user_id', $user->id)->whereNull('deleted_at')->first();
        if ($driver) return $driver;

        // 2. Exact Phone Match
        $phone = $user->phone ?: $user->phone_number;
        if ($phone) {
            $driver = Driver::where('contact_number', $phone)
                        ->whereNull('deleted_at')
                        ->orderByRaw('user_id IS NULL DESC')
                        ->first();
            
            if ($driver) {
                if (!$driver->user_id) {
                    $driver->user_id = $user->id;
                    $driver->save();
                }
                return $driver;
            }

            // 2b. Cleaned Phone Match (Last 10 digits)
            $cleanPhone = substr(preg_replace('/[^0-9]/', '', $phone), -10);
            if (strlen($cleanPhone) >= 10) {
                $driver = Driver::where('contact_number', 'LIKE', '%' . $cleanPhone)
                            ->whereNull('deleted_at')
                            ->orderByRaw('user_id IS NULL DESC')
                            ->first();
                
                if ($driver) {
                    if (!$driver->user_id) {
                        $driver->user_id = $user->id;
                        $driver->save();
                    }
                    return $driver;
                }
            }
        }

        // 3. Name match (Safe CONCAT for NULLs)
        $firstName = trim($user->first_name);
        $lastName = trim($user->last_name);
        
        if ($firstName && $lastName) {
            $driver = Driver::where('first_name', 'LIKE', '%' . $firstName . '%')
                        ->where('last_name', 'LIKE', '%' . $lastName . '%')
                        ->whereNull('deleted_at')
                        ->orderByRaw('user_id IS NULL DESC')
                        ->first();
            
            if ($driver) {
                if (!$driver->user_id) {
                    $driver->user_id = $user->id;
                    $driver->save();
                }
                return $driver;
            }
        }

        // 4. Full Name fallback (Case-insensitive)
        $searchName = trim($user->full_name ?: $user->name);
        if ($searchName) {
            $driver = Driver::where(DB::raw("CONCAT_WS(' ', first_name, last_name)"), 'LIKE', '%' . $searchName . '%')
                        ->whereNull('deleted_at')
                        ->orderByRaw('user_id IS NULL DESC')
                        ->first();

            if ($driver) {
                if (!$driver->user_id) {
                    $driver->user_id = $user->id;
                    $driver->save();
                }
                return $driver;
            }
        }

        return null;
    }

}
