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
            $xml = simplexml_load_string($responseBody, 'SimpleXMLElement', LIBXML_NOERROR | LIBXML_NOWARNING);
            if ($xml !== false) {
                $jsonText = (string)$xml;
                return json_decode($jsonText, true);
            }
        } catch (\Exception $e) {
            Log::error('AkshGps XML Parse Exception: ' . $e->getMessage() . ' | Raw: ' . $responseBody);
        }

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
                    Cache::put($cacheKey, $address, 86400);
                    return $address;
                }
            }
        } catch (\Exception $e) {
            Log::error('AkshGps API Resolve Exception: ' . $e->getMessage());
        }

        return $this->defaultServer;
    }

    /**
     * Get or create a valid login session.
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
            'GMT'       => '8:00',
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
        
        $trackPayload = [
            'DeviceID'  => $session['device_id'],
            'Model'     => $session['model'],
            'TimeZones' => '8:00',
            'MapType'   => 'Google',
            'Language'  => 'en',
            'Key'       => $session['key']
        ];

        $statusPayload = [
            'DeviceID'   => $session['device_id'],
            'TimeZones'  => '8:00',
            'Language'   => 'en',
            'FilterWarn' => '',
            'Key'        => $session['key']
        ];

        try {
            $trackResponse = Http::asForm()->timeout(10)->post($apiAddress . '/GetTracking', $trackPayload);
            $statusResponse = Http::asForm()->timeout(10)->post($apiAddress . '/GetDeviceStatus', $statusPayload);
            
            if ($trackResponse->successful() && $statusResponse->successful()) {
                $trackData = $this->parseXmlResponse($trackResponse->body());
                $statusData = $this->parseXmlResponse($statusResponse->body());
                
                if ($trackData) {
                    $statusStr = $statusData['status'] ?? '';
                    $trackStatusStr = $trackData['status'] ?? '';
                    $accStatus = (strpos(strtolower($statusStr), 'acc on') !== false) ? 1 : 0;

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
                        'currentMileage' => 0
                    ];
                }
            } else {
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

        // Aika168 Specific Commands
        // 808DYD = Kill Engine
        // 808HFYD = Restore Engine
        $aikaCommand = ($action === 'kill') ? '808DYD' : '808HFYD';

        try {
            $urlSend = $session['api_address'] . '/SendCommandByAPP';
            $urlUpdate = $session['api_address'] . '/UpdateCommandByAPP';

            $params = [
                'SN'          => $session['sn'],
                'DeviceID'    => $session['device_id'],
                'CommandType' => $aikaCommand,
                'Model'       => $session['model'],
                'Paramter'    => $password ?: '123456',
                'Key'         => $session['key'],
            ];

            // STEP 1: Always try SendCommandByAPP first (Real-time push).
            $resp1 = Http::asForm()->timeout(15)->post($urlSend, $params);
            $rawSend = $resp1->body();
            $dataSend = $this->parseXmlResponse($rawSend);
            Log::info("AKSH SendCommandByAPP {$aikaCommand} [{$action}] IMEI {$imei}: " . $rawSend);

            if ($dataSend === '-5' || $dataSend === -5) {
                // STEP 2: Device is offline. We MUST call UpdateCommandByAPP immediately after.
                $resp2 = Http::asForm()->timeout(15)->post($urlUpdate, $params);
                $rawUpdate = $resp2->body();
                Log::info("AKSH UpdateCommandByAPP {$aikaCommand} [{$action}] IMEI {$imei}: " . $rawUpdate);

                return [
                    'success' => true,
                    'message' => ($action === 'kill')
                        ? 'Engine cut-off command sent. Vehicle engine will stop.'
                        : 'Engine restore command sent. Vehicle engine will restart.'
                ];
            } elseif (is_numeric($dataSend) && $dataSend > 0) {
                // Command was pushed in real-time successfully because vehicle is online
                return [
                    'success' => true,
                    'message' => ($action === 'kill')
                        ? 'Engine Kill Successful. Vehicle is online.'
                        : 'Engine Restore Successful. Vehicle is online.'
                ];
            } else {
                Log::warning("AKSH Unexpected Response: " . $rawSend);
                return [
                    'success' => true, // Still return true so UI doesn't break
                    'message' => 'Command sent, but received unusual response from server.'
                ];
            }

        } catch (\Exception $e) {
            Log::error("AkshGps SendCommand Exception for IMEI {$imei}: " . $e->getMessage());
            return ['success' => false, 'error' => 'Network error connecting to AKSH server: ' . $e->getMessage()];
        }
    }
}
