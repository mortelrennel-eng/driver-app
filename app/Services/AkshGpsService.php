<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class AkshGpsService
{
    protected $defaultPassword;
    protected $defaultServer;

    public function __construct()
    {
        $this->defaultPassword = env('AKSH_DEFAULT_PASSWORD', '123456');
        $this->defaultServer = 'http://www.aika168.com';
    }

    /**
     * Parse the XML-wrapped JSON response from Aika168.
     */
    protected function parseXmlResponse($responseBody)
    {
        try {
            // Aika168 returns JSON wrapped inside <string xmlns="http://tempuri.org/">{...}</string>
            // We disable entity loader for security and parse it cleanly
            $xml = simplexml_load_string($responseBody, 'SimpleXMLElement', LIBXML_NOERROR | LIBXML_NOWARNING);
            if ($xml !== false) {
                $jsonText = (string)$xml;
                return json_decode($jsonText, true);
            }
        } catch (\Exception $e) {
            Log::error('AkshGps XML Parse Exception: ' . $e->getMessage() . ' | Raw: ' . $responseBody);
        }

        // Fallback: simple regex search if simplexml fails
        if (preg_match('/<string[^>]*>(.*)<\/string>/is', $responseBody, $matches)) {
            return json_decode(html_entity_decode($matches[1]), true);
        }

        return null;
    }

    /**
     * Retrieve the API address by querying getapp.aspx.
     */
    protected function resolveApiAddress()
    {
        $cacheKey = 'aksh_resolved_api_address';
        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        try {
            $url = $this->defaultServer . '/getapp.aspx';
            $response = Http::timeout(10)->get($url);
            
            if ($response->successful()) {
                $address = trim($response->body());
                if (!empty($address) && strpos($address, 'http') === 0) {
                    Cache::put($cacheKey, $address, 86400); // Cache resolved domain for 24 hours
                    return $address;
                }
            }
        } catch (\Exception $e) {
            Log::error('AkshGps API Resolve Exception: ' . $e->getMessage());
        }

        return $this->defaultServer; // Fallback to main server
    }

    /**
     * Get or create a valid login session (caching device details and session key).
     */
    public function getSession($imei, $password = null, $forceRefresh = false)
    {
        $password = $password ?: $this->defaultPassword;
        $cacheKey = 'aksh_session_' . $imei;

        if (!$forceRefresh && Cache::has($cacheKey)) {
            $session = Cache::get($cacheKey);
            if (is_array($session) && isset($session['key']) && isset($session['api_address'])) {
                return $session;
            }
        }

        $apiAddress = $this->resolveApiAddress();
        
        $payload = [
            'Name'      => $imei,
            'Pass'      => $password,
            'LoginType' => 1,
            'LoginAPP'  => 'AKSH',
            'GMT'       => '8:00', // Philippine Standard Time (UTC+8)
            'Key'       => '7DU2DJFDR8321'
        ];

        try {
            $url = $apiAddress . '/Login';
            $response = Http::asForm()->timeout(15)->post($url, $payload);
            
            if ($response->successful()) {
                $data = $this->parseXmlResponse($response->body());
                
                if ($data && isset($data['deviceInfo']) && isset($data['deviceInfo']['key2018'])) {
                    $devInfo = $data['deviceInfo'];
                    
                    $sessionData = [
                        'device_id'   => $devInfo['deviceID'] ?? null,
                        'model'       => $devInfo['model'] ?? null,
                        'key'         => $devInfo['key2018'],
                        'api_address' => $apiAddress,
                        'sn'          => $devInfo['sn'] ?? $imei
                    ];
                    
                    // Cache session for 55 minutes (expiring slightly before the 1h key duration)
                    Cache::put($cacheKey, $sessionData, 3300);
                    return $sessionData;
                } else {
                    Log::error("AkshGps Login Failed for IMEI {$imei}: " . json_encode($data));
                }
            }
        } catch (\Exception $e) {
            Log::error("AkshGps Login Exception for IMEI {$imei}: " . $e->getMessage());
        }

        return null;
    }

    /**
     * Fetch tracking position and status details for a unit.
     */
    public function getGpsData($imei, $password = null)
    {
        $session = $this->getSession($imei, $password);
        if (!$session) {
            return null;
        }

        $apiAddress = $session['api_address'];
        
        // 1. Get Location tracking data
        $trackPayload = [
            'DeviceID'  => $session['device_id'],
            'Model'     => $session['model'],
            'TimeZones' => '8:00', // Philippine Standard Time (UTC+8)
            'MapType'   => 'Google',
            'Language'  => 'en',
            'Key'       => $session['key']
        ];

        // 2. Get Device Status data (for ignition status)
        $statusPayload = [
            'DeviceID'   => $session['device_id'],
            'TimeZones'  => '8:00', // Philippine Standard Time (UTC+8)
            'Language'   => 'en',
            'FilterWarn' => '',
            'Key'        => $session['key']
        ];

        try {
            // Parallel/sequential fetch
            $trackResponse = Http::asForm()->timeout(10)->post($apiAddress . '/GetTracking', $trackPayload);
            $statusResponse = Http::asForm()->timeout(10)->post($apiAddress . '/GetDeviceStatus', $statusPayload);
            
            if ($trackResponse->successful() && $statusResponse->successful()) {
                $trackData = $this->parseXmlResponse($trackResponse->body());
                $statusData = $this->parseXmlResponse($statusResponse->body());
                
                if ($trackData) {
                    $statusStr = $statusData['status'] ?? '';
                    $trackStatusStr = $trackData['status'] ?? '';
                    $accStatus = (strpos(strtolower($statusStr), 'acc on') !== false) ? 1 : 0;

                    // Aika keeps positionTime fresh while the device is stationary.
                    // `stm` is the dwell/stationary duration in minutes shown in Aika's own popup.
                    $isStopped = (string)($trackData['isStop'] ?? '') === '1';
                    $dwellMinutes = null;
                    if (isset($trackData['stm']) && is_numeric($trackData['stm'])) {
                        $dwellMinutes = max(0, (int)$trackData['stm']);
                    }
                    $providerOffline = (string)($trackData['ofl'] ?? '') === '1'
                        || stripos($trackStatusStr, 'offline') !== false
                        || stripos($statusStr, 'offline') !== false;
                    
                    $gpsTimePht = $trackData['positionTime'] ?? null;
                    if ($gpsTimePht) {
                        // Aika168 API returns positionTime already in UTC.
                        // DO NOT subtract 8 hours — the offline calculation in LiveTrackingController
                        // appends ' UTC' and compares against time() (UTC), so this must stay UTC.
                        $gpsTime = $gpsTimePht;
                    } else {
                        $gpsTime = now()->utc()->toDateTimeString();
                    }
                    
                    return [
                        'lat'            => (float)($trackData['lat'] ?? 0.0),
                        'lng'            => (float)($trackData['lng'] ?? 0.0),
                        'accStatus'      => $accStatus,
                        'speed'          => (float)($trackData['speed'] ?? 0.0),
                        'direction'      => (int)($trackData['course'] ?? 0),
                        'gpsTime'        => $gpsTime,
                        'hbTime'         => $gpsTime,
                        'isStopped'      => $isStopped,
                        'dwellSeconds'   => ($isStopped && $dwellMinutes !== null) ? $dwellMinutes * 60 : null,
                        'providerOffline'=> $providerOffline,
                        'currentMileage' => 0 // Aika168 does not expose odometer mileage in this endpoint
                    ];
                }
            } else {
                // Check if session has expired, force reload next time
                if ($trackResponse->status() == 401 || $statusResponse->status() == 401) {
                    Cache::forget('aksh_session_' . $imei);
                }
            }
        } catch (\Exception $e) {
            Log::error("AkshGps Data Retrieval Exception for IMEI {$imei}: " . $e->getMessage());
        }

        return null;
    }

    /**
     * Send Engine Command (cut-off/restore) to a unit.
     */
    public function sendEngineCommand($imei, $password = null, $action = 'kill')
    {
        $session = $this->getSession($imei, $password);
        if (!$session) {
            return ['success' => false, 'error' => 'API Authentication failed. Please check tracker login.'];
        }

        // JT808 Protocol Section 7.10 Terminal Control [0x8105]:
        // Command Word 0x64 (decimal 100) = Cut off fuel/electric loop (KILL ENGINE)
        // Command Word 0x65 (decimal 101) = Connect fuel/electric loop (RESTORE ENGINE)
        $commandType = ($action === 'kill') ? '0x64' : '0x65';
        $commandDecimal = ($action === 'kill') ? '100' : '101';
        
        $payload = [
            'DeviceID'    => $session['device_id'],
            'CommandType' => $commandType,  // JT808 standard hex command
            'Paramter'    => '',
            'Key'         => $session['key']
        ];

        try {
            $url = $session['api_address'] . '/UpdateCommandByAPP';
            $response = Http::asForm()->timeout(15)->post($url, $payload);
            
            // If 0x64/0x65 doesn't work, try decimal equivalent
            $rawBody = $response->body();
            $data = $this->parseXmlResponse($rawBody);
            Log::info("AkshGps SendCommand [0x64/0x65 hex] for IMEI {$imei} [{$action}]: " . $rawBody);

            // Aika might use decimal instead of hex — try decimal if hex returned non-success
            $isError = is_string($data) && !is_numeric($data) && $data !== '';
            if ($isError) {
                $payload['CommandType'] = $commandDecimal;
                $response = Http::asForm()->timeout(15)->post($url, $payload);
                $rawBody = $response->body();
                $data = $this->parseXmlResponse($rawBody);
                Log::info("AkshGps SendCommand [decimal fallback] for IMEI {$imei} [{$action}]: " . $rawBody);
            }

            // Aika168 UpdateCommandByAPP returns:
            //   "0"  or 0   → success (most firmware)
            //   "1"  or 1   → success (some firmware versions)
            //   null        → success (empty XML body = command queued)
            //   positive integers → typically success/queued
            //   negative integers → error codes
            //   "error message" string → failure
            if ($response->successful()) {
                if ($data === null || $data === "" || $data === "0" || $data === 0 || $data === "1" || $data === 1) {
                    return ['success' => true, 'message' => 'Command queued to device.'];
                }
                
                if (is_numeric($data) && (int)$data >= 0) {
                    return ['success' => true, 'message' => 'Command accepted (code: ' . $data . ').'];
                }
                
                if (is_array($data)) {
                    if ((isset($data['success']) && $data['success'] == true) || (isset($data['status']) && (int)$data['status'] >= 0)) {
                        return ['success' => true];
                    }
                    $errorMsg = $data['message'] ?? ($data['msg'] ?? json_encode($data));
                    return ['success' => false, 'error' => 'AKSH Error: ' . $errorMsg];
                }
                
                // Non-empty string that's not a number → likely an error message
                $errorMsg = is_string($data) ? "AKSH Response: " . $data : 'Command was rejected by the AKSH GPS network.';
                return ['success' => false, 'error' => $errorMsg];
            }
        } catch (\Exception $e) {
            Log::error("AkshGps SendCommand Exception for IMEI {$imei}: " . $e->getMessage());
            return ['success' => false, 'error' => 'Network error connecting to AKSH server: ' . $e->getMessage()];
        }

        return ['success' => false, 'error' => 'Server communication failure.'];
    }
}
