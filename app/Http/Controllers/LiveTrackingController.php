<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\TracksolidService;
use App\Services\AkshGpsService;

class LiveTrackingController extends Controller
{
    protected $tracksolid;
    protected $aksh;

    public function __construct(TracksolidService $tracksolid, AkshGpsService $aksh)
    {
        $this->tracksolid = $tracksolid;
        $this->aksh = $aksh;
    }

    private const GPS_ONLINE_WINDOW_SECONDS = 600;
    private const GPS_SPEED_STALE_SECONDS = 300;

    private function secondsSinceUtc(?string $time): int
    {
        if (!$time) {
            return PHP_INT_MAX;
        }

        $timestamp = strtotime($time . ' UTC');
        if ($timestamp === false) {
            return PHP_INT_MAX;
        }

        return max(0, time() - $timestamp);
    }

    private function formatDuration(int $seconds): string
    {
        $hours = (int) floor($seconds / 3600);
        $minutes = (int) floor(($seconds % 3600) / 60);

        return $hours > 0 ? "{$hours}h {$minutes}m" : "{$minutes}m";
    }

    private function gpsDwellSeconds(?array $gps): ?int
    {
        if (!$gps || !isset($gps['dwellSeconds']) || !is_numeric($gps['dwellSeconds'])) {
            return null;
        }

        return max(0, (int)$gps['dwellSeconds']);
    }

    private function resolveGpsStatus(?array $gps, ?string $lastUpdate, bool $ignition, float $rawSpeed): array
    {
        $heartbeatAge = PHP_INT_MAX;
        $gpsAge = PHP_INT_MAX;

        if ($gps && isset($gps['hbTime']) && isset($gps['gpsTime'])) {
            $heartbeatAge = $this->secondsSinceUtc($gps['hbTime']);
            $gpsAge = $this->secondsSinceUtc($gps['gpsTime']);
        } else {
            $fallbackAge = $this->secondsSinceUtc($lastUpdate);
            $heartbeatAge = $fallbackAge;
            $gpsAge = $fallbackAge;
        }

        $effectiveGpsAge = $gpsAge;
        $dwellSeconds = $this->gpsDwellSeconds($gps);

        if (!$ignition && $dwellSeconds !== null) {
            $effectiveGpsAge = $gpsAge === PHP_INT_MAX
                ? $dwellSeconds
                : max($gpsAge, $dwellSeconds);
        }

        $providerOffline = (bool)($gps['providerOffline'] ?? false);
        $status = 'offline';
        $speed = 0.0;
        $safeIgnition = $ignition;

        if (!$providerOffline && $heartbeatAge < self::GPS_ONLINE_WINDOW_SECONDS) {
            if ($ignition) {
                $speed = $gpsAge > self::GPS_SPEED_STALE_SECONDS ? 0.0 : max(0.0, $rawSpeed);
                $status = $speed > 2 ? 'moving' : 'idle';
            } else {
                $status = $effectiveGpsAge > self::GPS_ONLINE_WINDOW_SECONDS ? 'offline' : 'stopped';
            }
        }

        if ($status === 'offline' || $status === 'stopped') {
            $speed = 0.0;
            $safeIgnition = false;
        }

        $offlineAge = PHP_INT_MAX;
        if ($status === 'offline') {
            $offlineAge = $effectiveGpsAge < PHP_INT_MAX ? $effectiveGpsAge : $heartbeatAge;
        }

        return [
            'status'          => $status,
            'speed'           => $speed,
            'ignition'        => $safeIgnition,
            'heartbeat_age'   => $heartbeatAge,
            'gps_age'         => $gpsAge,
            'effective_age'   => $effectiveGpsAge,
            'offline_age'     => $offlineAge,
            'offline_display' => ($status === 'offline' && $offlineAge < PHP_INT_MAX) ? $this->formatDuration($offlineAge) : '',
        ];
    }
    // ─── Main Page ─────────────────────────────────────────

    // ─── Main Page ─────────────────────────────────────────
    public function index()
    {
        try {
            // Get all units with their latest GPS data
            $tracked_units = DB::table('units as u')
                ->leftJoin('drivers as d1', 'u.driver_id', '=', 'd1.id')
                ->leftJoin('drivers as d2', 'u.secondary_driver_id', '=', 'd2.id')
                ->leftJoin('gps_tracking as g', 'u.id', '=', 'g.unit_id')
                ->select(
                    'u.id', 'u.plate_number', 'u.make', 'u.model', 'u.status', 'u.imei', 'u.gps_provider', 'u.gps_password',
                    DB::raw("TRIM(CONCAT(COALESCE(d1.first_name,''), ' ', COALESCE(d1.last_name,''))) as driver_name"),
                    DB::raw("TRIM(CONCAT(COALESCE(d2.first_name,''), ' ', COALESCE(d2.last_name,''))) as secondary_driver"),
                    'd1.contact_number as driver_phone',
                    'g.latitude', 'g.longitude', 'g.speed', 'g.heading', 'g.ignition_status', 'g.timestamp as last_update'
                )
                ->orderBy('u.plate_number')
                ->get();

            // Fetch live data from Tracksolid Pro API
            $liveData = $this->tracksolid->getAllLocations();
            $liveMap = $liveData ? collect($liveData)->keyBy('imei') : collect();
            $apiActive = true; // Active hybrid system

            // Merge API data with local records and determine status in a single safe loop
            foreach ($tracked_units as $unit) {
                $gps = null;

                if ($unit->imei) {
                    if (($unit->gps_provider ?? 'tracksolid') === 'aksh') {
                        $gps = $this->aksh->getGpsData($unit->imei, $unit->gps_password);
                    } else if (isset($liveMap[$unit->imei])) {
                        $gps = $liveMap[$unit->imei];
                    }
                }

                // 1. If we have fresh live data, merge it first
                if ($gps) {
                    $unit->latitude = $gps['lat'] ?? $unit->latitude;
                    $unit->longitude = $gps['lng'] ?? $unit->longitude;
                    $unit->ignition_status = ($gps['accStatus'] ?? 0) == 1;
                    $unit->speed = (float)($gps['speed'] ?? 0);
                    $unit->heading = $gps['direction'] ?? $unit->heading;
                    $unit->last_update = $gps['gpsTime'] ?? $unit->last_update;
                }

                $statusInfo = $this->resolveGpsStatus(
                    $gps,
                    $unit->last_update,
                    (bool)$unit->ignition_status,
                    (float)($unit->speed ?? 0)
                );

                $unit->gps_status = $statusInfo['status'];
                $unit->speed = $statusInfo['speed'];
                $unit->ignition_status = $statusInfo['ignition'];

                // 3. Update local cache table with the CORRECTED/SAFE values (if live data is active)
                if ($unit->imei && isset($liveMap[$unit->imei])) {
                    DB::table('gps_tracking')->updateOrInsert(
                        ['unit_id' => $unit->id],
                        [
                            'latitude' => $unit->latitude,
                            'longitude' => $unit->longitude,
                            'speed' => $unit->speed,
                            'heading' => $unit->heading,
                            'ignition_status' => $unit->ignition_status,
                            'timestamp' => $unit->last_update,
                            'updated_at' => now()
                        ]
                    );
                }
            }

            // Simulated stats logic
            $stats = [
                'total'     => $tracked_units->count(),
                'moving'    => $tracked_units->where('gps_status', 'moving')->count(),
                'idle'      => $tracked_units->where('gps_status', 'idle')->count(),
                'stopped'   => $tracked_units->where('gps_status', 'stopped')->count(),
                'offline'   => $tracked_units->where('gps_status', 'offline')->count(),
                'avg_speed' => $tracked_units->avg('speed') ?? 0
            ];

            // Get system alerts
            $alerts = DB::table('system_alerts')
                ->where('is_resolved', false)
                ->orderByDesc('created_at')
                ->limit(10)
                ->get();

            // Get maintenance alerts
            $maintenanceAlerts = DB::table('maintenance')
                ->where('status', 'pending')
                ->where('date_started', '<=', now())
                ->orderBy('date_started')
                ->limit(5)
                ->get();

            return view('live-tracking.index', compact('tracked_units', 'alerts', 'maintenanceAlerts', 'stats', 'apiActive'));

        } catch (\Exception $e) {
            \Log::error('Live Tracking Error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->with('error', 'Error loading tracking data: ' . $e->getMessage());
        }
    }

    // ─── AJAX: Single Unit Location ────────────────────────
    public function getUnitLocation($id)
    {
        try {
            $unit = DB::table('units')->where('id', $id)->first();
            if (!$unit || !$unit->imei) {
                return response()->json(['success' => false, 'error' => 'Vehicle has no GPS IMEI registered.']);
            }

            $gps = null;
            if (($unit->gps_provider ?? 'tracksolid') === 'aksh') {
                $gps = $this->aksh->getGpsData($unit->imei, $unit->gps_password);
            } else {
                $liveData = $this->tracksolid->getLocations([$unit->imei]);
                if ($liveData && !empty($liveData)) {
                    $gps = $liveData[0];
                }
            }
            
            if (!$gps) {
                return response()->json(['success' => false, 'error' => 'No signal retrieved from GPS provider.']);
            }
            
            $lastUpdate = $gps['gpsTime'] ?? null;
            $ignition = ($gps['accStatus'] ?? 0) == 1;
            $statusInfo = $this->resolveGpsStatus($gps, $lastUpdate, $ignition, (float)($gps['speed'] ?? 0));
            $status = $statusInfo['status'];
            $speed = $statusInfo['speed'];
            $ignition = $statusInfo['ignition'];

            return response()->json([
                'success' => true,
                'data' => [
                    'plate_number'    => $unit->plate_number,
                    'status'          => $status,
                    'latitude'        => (float)$gps['lat'],
                    'longitude'       => (float)$gps['lng'],
                    'speed'           => $speed,
                    'ignition'        => $ignition,
                    'last_update'     => $lastUpdate,
                    'heading'         => $gps['direction'] ?? 0,
                    'coordinates'     => $gps['lat'] . ', ' . $gps['lng']
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    // ─── AJAX: All Units (for auto-refresh) ────────────────
    public function getUnitsLive()
    {
        try {
            $units = DB::table('units as u')
                ->leftJoin('drivers as d1', 'u.driver_id', '=', 'd1.id')
                ->leftJoin('drivers as d2', 'u.secondary_driver_id', '=', 'd2.id')
                ->leftJoin('gps_tracking as g', 'u.id', '=', 'g.unit_id')
                ->select(
                    'u.id', 'u.plate_number', 'u.imei', 'u.status', 'u.driver_id', 'u.gps_provider', 'u.gps_password',
                    'u.engine_status',
                    DB::raw("TRIM(CONCAT(COALESCE(d1.first_name,''), ' ', COALESCE(d1.last_name,''))) as driver_name"),
                    DB::raw("TRIM(CONCAT(COALESCE(d2.first_name,''), ' ', COALESCE(d2.last_name,''))) as secondary_driver"),
                    'd1.contact_number as driver_phone',
                    'g.latitude', 'g.longitude', 'g.speed as cached_speed', 'g.heading as cached_heading', 'g.ignition_status as cached_ignition', 'g.timestamp as last_update', 'g.odo as cached_odo'
                )
                ->orderBy('u.plate_number')
                ->get();

            // Fetch live records from API
            $liveData = $this->tracksolid->getAllLocations();
            $liveMap = $liveData ? collect($liveData)->keyBy('imei') : collect();

            $result = $units->map(function ($unit) use ($liveMap) {
                $gps = null;
                if ($unit->imei) {
                    if (($unit->gps_provider ?? 'tracksolid') === 'aksh') {
                        $gps = $this->aksh->getGpsData($unit->imei, $unit->gps_password);
                    } else if (isset($liveMap[$unit->imei])) {
                        $gps = $liveMap[$unit->imei];
                    }
                }
                
                // Location and raw telemetry
                $lastUpdate = $unit->last_update;
                $lat      = $unit->latitude;
                $lng      = $unit->longitude;
                $ignition = false;
                $rawSpeed = 0.0;

                if ($gps) {
                    $lat        = $gps['lat'] ?? $lat;
                    $lng        = $gps['lng'] ?? $lng;
                    $ignition   = ($gps['accStatus'] ?? 0) == 1;
                    $rawSpeed   = (float)($gps['speed'] ?? 0);
                    $lastUpdate = $gps['gpsTime'] ?? $lastUpdate;
                } else {
                    // Fall back to database cached ignition & speed
                    $ignition   = ($unit->cached_ignition ?? 0) == 1;
                    $rawSpeed   = (float)($unit->cached_speed ?? 0);
                }

                $statusInfo = $this->resolveGpsStatus($gps, $lastUpdate, $ignition, $rawSpeed);
                $status = $statusInfo['status'];
                $speed = $statusInfo['speed'];
                $ignition = $statusInfo['ignition'];
                $offlineDuration = $statusInfo['offline_display'];

                return [
                    'unit_id'         => $unit->id,
                    'driver_id'       => $unit->driver_id,
                    'plate_number'    => $unit->plate_number,
                    'driver_name'     => $unit->driver_name ?? 'None',
                    'secondary_driver'=> $unit->secondary_driver,
                    'gps_status'      => $status,
                    'speed'           => $speed,
                    'ignition_status' => $ignition,
                    'last_update'     => $lastUpdate,
                    'offline_display' => $offlineDuration,
                    'latitude'        => $lat,
                    'longitude'       => $lng,
                    'angle'           => $gps['direction'] ?? $unit->cached_heading ?? 0,
                    'odo'             => $gps['currentMileage'] ?? $unit->cached_odo ?? 0,
                    'u_status'        => $unit->status,
                    'engine_status'   => $unit->engine_status ?? null,
                    'daily_dist'      => 0 // Handled in sync below
                ];
            });

            // 1. Fetch all existing tracking records in one query for optimization
            $trackingData = DB::table('gps_tracking')
                ->whereIn('unit_id', $result->pluck('unit_id'))
                ->get()
                ->keyBy('unit_id');

            $today = now()->timezone('Asia/Manila')->format('Y-m-d');
            $gps_data = $result->toArray();

            foreach ($gps_data as &$unitData) {
                $tracking = $trackingData->get($unitData['unit_id']);
                
                $currentOdo = (float)($unitData['odo'] ?? 0);
                $startMileage = $currentOdo;
                $startDate = $today;

                if ($tracking) {
                    if ($tracking->daily_start_date === $today) {
                        $startMileage = (float)($tracking->daily_start_mileage ?? $currentOdo);
                        $startDate = $tracking->daily_start_date;
                    } else {
                        $startMileage = $currentOdo;
                        $startDate = $today;
                    }
                }

                $realtimeDist = max(0, $currentOdo - $startMileage);
                $unitData['daily_dist'] = round($realtimeDist, 2);


                // Update DB only if coordinates are valid
                if ($unitData['latitude'] !== null && $unitData['longitude'] !== null) {
                    DB::table('gps_tracking')->updateOrInsert(
                        ['unit_id' => $unitData['unit_id']],
                        [
                            'latitude'            => $unitData['latitude'],
                            'longitude'           => $unitData['longitude'],
                            'speed'               => $unitData['speed'],
                            'heading'             => $unitData['angle'],
                            'ignition_status'     => $unitData['ignition_status'],
                            'odo'                 => $currentOdo,
                            'daily_start_mileage' => $startMileage,
                            'daily_start_date'    => $startDate,
                            'updated_at'          => now()
                        ]
                    );

                    // Sync to units table for health tracking
                    DB::table('units')->where('id', $unitData['unit_id'])->update([
                        'current_gps_odo' => $currentOdo,
                        'updated_at' => now()
                    ]);
                }
            }

            return response()->json([
                'success' => true,
                'units' => $gps_data,
                'stats' => [
                    'total'   => count($gps_data),
                    'moving'  => collect($gps_data)->where('gps_status', 'moving')->count(),
                    'idle'    => collect($gps_data)->where('gps_status', 'idle')->count(),
                    'stopped' => collect($gps_data)->where('gps_status', 'stopped')->count(),
                    'offline' => collect($gps_data)->where('gps_status', 'offline')->count()
                ],
                'alerts' => DB::table('system_alerts')
                    ->where('is_resolved', false)
                    ->orderByDesc('created_at')
                    ->limit(10)
                    ->get()
                    ->map(function($alert) {
                        $alert->formatted_time = \Carbon\Carbon::parse($alert->created_at)->diffForHumans();
                        return $alert;
                    })
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // ─── AJAX: Unit Mileage (24h/Daily) ───────────────────
    public function getUnitMileage($id)
    {
        try {
            $unit = DB::table('units')->where('id', $id)->first();
            if (!$unit || !$unit->imei) {
                return response()->json(['success' => false, 'error' => 'IMEI not found']);
            }

            // Calculate start of day (00:00:00 PHT -> UTC)
            // Tracksolid API expects UTC time. Manila 00:00:00 is previous day 16:00:00 UTC.
            $beginTime = now()->timezone('Asia/Manila')->startOfDay()->timezone('UTC')->format('Y-m-d H:i:s');
            $endTime = gmdate('Y-m-d H:i:s');

            $mileageData = $this->tracksolid->getMileage($unit->imei, $beginTime, $endTime);
            
            // The API returns an array of mileage per day or summary
            // For one device and today, we look for the sum or matching record
            $totalDistanceMeters = 0;
            if ($mileageData && is_array($mileageData)) {
                foreach ($mileageData as $record) {
                    // The API returns 'distance' in meters for each segment
                    $totalDistanceMeters += (float)($record['distance'] ?? 0);
                }
            }
            
            // Convert to Kilometers
            $totalDistance = $totalDistanceMeters / 1000;

            // Get device details for activation time
            $detail = $this->tracksolid->getDeviceDetail($unit->imei);
            $ageMonths = null;
            if ($detail && isset($detail['activationTime'])) {
                $activationDate = new \DateTime($detail['activationTime']);
                $now = new \DateTime();
                $diff = $now->diff($activationDate);
                // Calculate total months
                $ageMonths = ($diff->y * 12) + $diff->m + ($diff->d / 30);
                $ageMonths = round($ageMonths, 1);
            }

            // Hybrid Sync: Correct the local baseline using the API data
            $realtimeTracking = DB::table('gps_tracking')->where('unit_id', $unit->id)->first();
            if ($realtimeTracking && $totalDistance > 0) {
                // Corrected Baseline = Current ODO - Distance Traveled Today (from API)
                $currentOdo = (float)($realtimeTracking->odo ?? 0);
                if ($currentOdo > 0) {
                    $correctedBaseline = $currentOdo - $totalDistance;
                    DB::table('gps_tracking')->where('unit_id', $unit->id)->update([
                        'daily_start_mileage' => $correctedBaseline,
                        'daily_start_date'    => now()->timezone('Asia/Manila')->format('Y-m-d')
                    ]);
                }
            }

            return response()->json([
                'success' => true,
                'mileage' => round($totalDistance, 2),
                'age'     => $ageMonths,
                'unit'    => $unit->plate_number
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // ─── AJAX: Engine Control (Kill/Restore) ────────────────────
    public function engineControl(Request $request)
    {
        try {
            $request->validate([
                'unit_id' => 'required|integer',
                'action'  => 'required|in:kill,restore'
            ]);

            $unit = DB::table('units')->where('id', $request->unit_id)->first();
            if (!$unit || !$unit->imei) {
                return response()->json(['success' => false, 'error' => 'Vehicle has no GPS IMEI registered.']);
            }

            // SAFETY CRITICAL CHECK
            if ($request->action === 'kill') {
                $gps = DB::table('gps_tracking')->where('unit_id', $unit->id)->first();
                $speed = $gps ? (float)$gps->speed : 0;
                
                // Block if moving > 20 km/h to prevent accidents
                if ($speed > 20) {
                    return response()->json([
                        'success' => false, 
                        'error' => "Safety Lock Active: Vehicle is traveling too fast ({$speed} km/h). Cannot cut engine above 20km/h."
                    ]);
                }
            }

            // Send command depending on provider
            if (($unit->gps_provider ?? 'tracksolid') === 'aksh') {
                $result = $this->aksh->sendEngineCommand($unit->imei, $unit->gps_password, $request->action);
                $providerName = 'AKSH GPS';
            } else {
                $result = $this->tracksolid->sendEngineCommand($unit->imei, $request->action);
                $providerName = 'Tracksolid';
            }

            if ($result['success']) {
                $engineStatus = ($request->action === 'kill') ? 'killed' : 'restored';

                // Update persistent engine status in units table
                DB::table('units')->where('id', $unit->id)->update([
                    'engine_status' => $engineStatus
                ]);

                // Log the action for auditing
                DB::table('system_alerts')->insert([
                    'title' => "Engine " . strtoupper($request->action) . ": {$unit->plate_number}",
                    'message' => "Remote engine {$request->action} command delivered successfully via {$providerName}.",
                    'type' => $request->action === 'kill' ? 'danger' : 'success',
                    'is_resolved' => false,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

                return response()->json([
                    'success' => true,
                    'pending' => false,
                    'message' => $result['message'] ?? ("Engine " . ($request->action === 'kill' ? 'cut-off' : 'restored') . " command sent to unit."),
                    'engine_status' => $engineStatus,
                ]);
            } else {
                return response()->json(['success' => false, 'error' => $result['error']]);
            }

        } catch (\Exception $e) {
            \Log::error('Engine Control Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => 'Internal server error: ' . $e->getMessage()], 500);
        }
    }
}
