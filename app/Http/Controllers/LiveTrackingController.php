<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\TracksolidService;

class LiveTrackingController extends Controller
{
    protected $tracksolid;

    public function __construct(TracksolidService $tracksolid)
    {
        $this->tracksolid = $tracksolid;
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
                    'u.id', 'u.plate_number', 'u.make', 'u.model', 'u.status', 'u.imei',
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
            $apiActive = !is_null($liveData);

            // Merge API data with local records
            foreach ($tracked_units as $unit) {
                if ($unit->imei && isset($liveMap[$unit->imei])) {
                    $gps = $liveMap[$unit->imei];
                    $unit->latitude = $gps['lat'] ?? $unit->latitude;
                    $unit->longitude = $gps['lng'] ?? $unit->longitude;
                    $unit->ignition_status = ($gps['accStatus'] ?? 0) == 1;
                    $unit->speed = $unit->ignition_status ? ($gps['speed'] ?? $unit->speed) : 0;
                    $unit->heading = $gps['direction'] ?? $unit->heading;
                    $unit->last_update = $gps['gpsTime'] ?? $unit->last_update;
                    
                    // Update local cache table for history/others
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

            // Determine GPS status for each unit
            foreach ($tracked_units as $unit) {
                $status = 'offline';
                if ($unit->last_update) {
                    $lastUpdateTs = strtotime($unit->last_update . ' UTC');
                    $diff = time() - $lastUpdateTs;
                    
                    if ($diff < 300) { // Less than 5 minutes
                        if ($unit->ignition_status) {
                            $status = $unit->speed > 2 ? 'active' : 'idle'; // Speed > 2 to account for GPS jitter
                        } else {
                            $status = 'stopped';
                        }
                    }
                }
                
                $unit->gps_status = $status;
            }

            // Simulated stats logic
            $stats = [
                'total'     => $tracked_units->count(),
                'active'    => $tracked_units->where('gps_status', 'active')->count(),
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

            // Fetch live record for this specific IMEI
            $liveData = $this->tracksolid->getLocations([$unit->imei]);
            
            if (!$liveData || empty($liveData)) {
                return response()->json(['success' => false, 'error' => 'No signal retrieved from GPS provider.']);
            }

            $gps = $liveData[0]; // Result for the requested IMEI
            
            // Determine Status
            $status = 'offline';
            $lastUpdate = $gps['gpsTime'] ?? null;
            $ignition = ($gps['accStatus'] ?? 0) == 1;
            $speed = $ignition ? (float)($gps['speed'] ?? 0) : 0;

            if ($lastUpdate) {
                $lastUpdateTs = strtotime($lastUpdate . ' UTC');
                $diff = time() - $lastUpdateTs;
                if ($diff < 3600) { // Within 1 hour
                    if ($ignition) {
                        $status = $speed > 2 ? 'moving' : 'idle';
                    } else {
                        $status = 'stopped';
                    }
                }
            }

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
                ->select(
                    'u.id', 'u.plate_number', 'u.imei', 'u.status', 'u.driver_id',
            DB::raw("TRIM(CONCAT(COALESCE(d1.first_name,''), ' ', COALESCE(d1.last_name,''))) as driver_name"),
            DB::raw("TRIM(CONCAT(COALESCE(d2.first_name,''), ' ', COALESCE(d2.last_name,''))) as secondary_driver"),
                    'd1.contact_number as driver_phone'
                )
                ->orderBy('u.plate_number')
                ->get();

            // Fetch live records from API
            $liveData = $this->tracksolid->getAllLocations();
            $liveMap = $liveData ? collect($liveData)->keyBy('imei') : collect();

            $result = $units->map(function ($unit) use ($liveMap) {
                $gps = $liveMap[$unit->imei] ?? null;
                
                // Determine Status
                $status = 'offline';
                $lastUpdate = null;
                $lat = null;
                $lng = null;
                $speed = 0;
                $ignition = false;

                if ($gps) {
                    $lat = $gps['lat'];
                    $lng = $gps['lng'];
                    $ignition = ($gps['accStatus'] ?? 0) == 1;
                    $speed = $ignition ? (float)($gps['speed'] ?? 0) : 0;
                    $lastUpdate = $gps['gpsTime'] ?? null;

                    if ($lastUpdate) {
                        $lastUpdateTs = strtotime($lastUpdate . ' UTC');
                        $diff = time() - $lastUpdateTs;
                        if ($diff < 3600) { // Within 1 hour
                            if ($ignition) {
                                $status = $speed > 2 ? 'moving' : 'idle';
                            } else {
                                $status = 'stopped';
                            }
                        }
                    }
                }
                
                $offlineDuration = '';
                if ($status === 'offline' && isset($diff)) {
                    $hours = floor($diff / 3600);
                    $minutes = floor(($diff % 3600) / 60);
                    if ($hours > 0) {
                        $offlineDuration = "{$hours}h {$minutes}m";
                    } else {
                        $offlineDuration = "{$minutes}m";
                    }
                }

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
                    'angle'           => $gps['direction'] ?? 0,
                    'odo'             => $gps['currentMileage'] ?? 0,
                    'u_status'        => $unit->status,
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

            // Calculate start of day (00:00:00 UTC)
            // Tracksolid API uses UTC
            $beginTime = gmdate('Y-m-d 00:00:00'); 
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

            // Send to Tracksolid
            $result = $this->tracksolid->sendEngineCommand($unit->imei, $request->action);

            if ($result['success']) {
                // Log the action for auditing
                DB::table('system_alerts')->insert([
                    'title' => "Engine " . strtoupper($request->action) . ": {$unit->plate_number}",
                    'message' => "Remote engine {$request->action} command delivered successfully via Tracksolid.",
                    'type' => $request->action === 'kill' ? 'danger' : 'success',
                    'is_resolved' => false,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

                return response()->json(['success' => true, 'message' => "Engine " . ($request->action === 'kill' ? 'cut-off' : 'restored') . " command sent to unit."]);
            } else {
                return response()->json(['success' => false, 'error' => $result['error']]);
            }

        } catch (\Exception $e) {
            \Log::error('Engine Control Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => 'Internal server error: ' . $e->getMessage()], 500);
        }
    }
}
