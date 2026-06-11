<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class TracksolidService
{
    protected $appKey;
    protected $appSecret;
    protected $username;
    protected $password;
    protected $apiUrl;
    protected $drift;

    public function __construct()
    {
        $this->appKey = config('services.tracksolid.app_key');
        $this->appSecret = config('services.tracksolid.app_secret');
        $this->username = config('services.tracksolid.username');
        $this->password = config('services.tracksolid.password');
        $this->apiUrl = config('services.tracksolid.api_url', 'https://hk-open.tracksolidpro.com/route/rest');
        $this->drift = (int)config('services.tracksolid.drift', 0);
    }

    /**
     * Get synchronized timestamp
     */
    protected function getTimestamp()
    {
        // Official docs: "timestamp must be GMT (UTC) time"
        // Asia/Hong_Kong +8h offset was causing illegal timestamp (Error 1001).
        return gmdate('Y-m-d H:i:s', time() + $this->drift);
    }

    /**
     * Get Access Token from API or Cache
     */
    public function getAccessToken($forceRefresh = false)
    {
        $cacheKey = 'tracksolid_access_token_' . $this->username;
        
        if ($forceRefresh) {
            Cache::forget($cacheKey);
        }
        
        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $params = [
            'method'      => 'jimi.oauth.token.get',
            'app_key'     => $this->appKey,
            'timestamp'   => $this->getTimestamp(),
            'format'      => 'json',
            'v'           => '0.9', // v=0.9 skips sign verification but still requires sign_method
            'sign_method' => 'md5',
            'expires_in'  => 7200,
            'user_id'     => $this->username,
            'user_pwd_md5'=> strlen($this->password) === 32 ? $this->password : md5($this->password),
        ];

        // Ensure sign is null or omitted for v=0.9
        // $params['sign'] = $this->generateSignature($params);

        try {
            $response = Http::asForm()->post($this->apiUrl, $params);
            $body = $response->body();
            $data = $response->json();

            if (isset($data['code']) && $data['code'] == 0 && isset($data['result']['accessToken'])) {
                $token = $data['result']['accessToken'];
                $expiresIn = $data['result']['expiresIn'] ?? 3600;
                
                // Cache token slightly shorter than its actual expiry
                Cache::put($cacheKey, $token, $expiresIn - 60);
                
                return $token;
            }

            Log::error('Tracksolid API Token Error: ' . json_encode($data));
            return null;

        } catch (\Exception $e) {
            Log::error('Tracksolid API Exception (Token): ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Generate Signature as per Jimi IoT Specification
     * md5(appSecret + [sorted params keyvalue] + appSecret)
     */
    protected function generateSignature(array $params)
    {
        // 1. Sort parameters by key alphabetically
        ksort($params);

        // 2. Concatenate keys and values
        $rawString = '';
        foreach ($params as $key => $value) {
            if ($key !== 'sign' && !is_null($value) && $value !== '') {
                $rawString .= $key . $value;
            }
        }

        // 3. Wrap with appSecret and MD5
        $signature = strtoupper(md5($this->appSecret . $rawString . $this->appSecret));

        return $signature;
    }

    /**
     * Get Location for specific IMEIs
     */
    public function getLocations(array $imeis)
    {
        $token = $this->getAccessToken();
        if (!$token) return null;

        $params = [
            'method'       => 'jimi.device.location.get',
            'app_key'      => $this->appKey,
            'access_token' => $token,
            'timestamp'    => $this->getTimestamp(),
            'format'       => 'json',
            'v'            => '1.0',
            'sign_method'  => 'md5',
            'imeis'        => implode(',', $imeis),
        ];

        $params['sign'] = $this->generateSignature($params);

        try {
            $response = Http::asForm()->post($this->apiUrl, $params);
            $data = $response->json();

            if (isset($data['code']) && $data['code'] == 0) {
                return $data['result'];
            }

            Log::error('Tracksolid API Location Error: ' . json_encode($data));
            return null;

        } catch (\Exception $e) {
            Log::error('Tracksolid API Exception (Location): ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get Location for all devices under account
     */
    public function getAllLocations($retry = true)
    {
        $token = $this->getAccessToken();
        if (!$token) return null;

        $params = [
            'method'       => 'jimi.user.device.location.list',
            'app_key'      => $this->appKey,
            'access_token' => $token,
            'timestamp'    => $this->getTimestamp(),
            'format'       => 'json',
            'v'            => '1.0',
            'sign_method'  => 'md5',
            'target'       => $this->username,
        ];

        $params['sign'] = $this->generateSignature($params);

        try {
            $response = Http::asForm()->post($this->apiUrl, $params);
            $data = $response->json();

            if (isset($data['code'])) {
                if ($data['code'] == 0) {
                    return $data['result'];
                }
                
                // Auto-Healing: Handle Invalid Token Errors (1004, 10006, etc)
                if (in_array($data['code'], [1004, 10006, 10011]) && $retry) {
                    Log::warning("Tracksolid API Token Expired [Code {$data['code']}]. Auto-refreshing token.");
                    $this->getAccessToken(true); // Force refresh
                    return $this->getAllLocations(false); // Retry once
                }
            }

            Log::error('Tracksolid API All Locations Error: ' . json_encode($data));
            return null;

        } catch (\Exception $e) {
            Log::error('Tracksolid API Exception (All Locations): ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get Device List (Metadata)
     * returns imei, deviceName, etc.
     */
    public function getDevices($page = 1, $pageSize = 100)
    {
        $token = $this->getAccessToken();
        if (!$token) return null;

        $params = [
            'method'       => 'jimi.user.device.list',
            'app_key'      => $this->appKey,
            'access_token' => $token,
            'timestamp'    => $this->getTimestamp(),
            'format'       => 'json',
            'v'            => '1.0',
            'sign_method'  => 'md5',
            'target'       => $this->username,
            'page'         => $page,
            'pageSize'     => $pageSize,
        ];

        $params['sign'] = $this->generateSignature($params);

        try {
            $response = Http::asForm()->post($this->apiUrl, $params);
            $data = $response->json();

            if (isset($data['code']) && $data['code'] == 0) {
                return $data['result'];
            }

            Log::error('Tracksolid API Device List Error: ' . json_encode($data));
            return null;

        } catch (\Exception $e) {
            Log::error('Tracksolid API Exception (Device List): ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get Mileage for specific devices and time range
     * Format: yyyy-MM-dd HH:mm:ss
     */
    public function getMileage(string $imeis, string $beginTime, string $endTime)
    {
        $token = $this->getAccessToken();
        if (!$token) return null;

        $params = [
            'method'       => 'jimi.device.track.mileage',
            'app_key'      => $this->appKey,
            'access_token' => $token,
            'timestamp'    => $this->getTimestamp(),
            'format'       => 'json',
            'v'            => '1.0',
            'sign_method'  => 'md5',
            'imeis'        => $imeis,
            'begin_time'   => $beginTime,
            'end_time'     => $endTime,
        ];

        $params['sign'] = $this->generateSignature($params);

        try {
            $response = Http::asForm()->post($this->apiUrl, $params);
            $data = $response->json();

            if (isset($data['code']) && $data['code'] == 0) {
                return $data['result'];
            }

            Log::error('Tracksolid API Mileage Error: ' . json_encode($data));
            return null;

        } catch (\Exception $e) {
            Log::error('Tracksolid API Exception (Mileage): ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get Device Details (includes activationTime)
     */
    public function getDeviceDetail(string $imei)
    {
        $token = $this->getAccessToken();
        if (!$token) return null;

        $params = [
            'method'       => 'jimi.track.device.detail',
            'app_key'      => $this->appKey,
            'access_token' => $token,
            'timestamp'    => $this->getTimestamp(),
            'format'       => 'json',
            'v'            => '1.0',
            'sign_method'  => 'md5',
            'imei'         => $imei,
        ];

        $params['sign'] = $this->generateSignature($params);

        try {
            $response = Http::asForm()->post($this->apiUrl, $params);
            $data = $response->json();

            if (isset($data['code']) && $data['code'] == 0) {
                return $data['result'];
            }

            Log::error('Tracksolid API Device Detail Error: ' . json_encode($data));
            return null;

        } catch (\Exception $e) {
            Log::error('Tracksolid API Exception (Device Detail): ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Send Engine Cut-off / Restore Command
     * Uses jimi.device.instruction.send mapping or generic.
     */
    public function sendEngineCommand(string $imei, string $action)
    {
        $token = $this->getAccessToken();
        if (!$token) return ['success' => false, 'error' => 'API Auth Failed'];

        // 'Relay,1#' for cut-off, 'Relay,0#' for restore 
        // Using "Custom" passing raw command
        $paramValue = ($action === 'kill') ? 'Relay,1#' : 'Relay,0#';

        $params = [
            'method'       => 'jimi.device.instruction.send',
            'app_key'      => $this->appKey,
            'access_token' => $token,
            'timestamp'    => $this->getTimestamp(),
            'format'       => 'json',
            'v'            => '1.0',
            'sign_method'  => 'md5',
            'imei'         => $imei,
            'cmd_type'     => 'Custom', 
            'param'        => $paramValue,
        ];

        $params['sign'] = $this->generateSignature($params);

        try {
            $response = Http::asForm()->post($this->apiUrl, $params);
            $data = $response->json();

            if (isset($data['code']) && $data['code'] == 0) {
                return ['success' => true, 'data' => $data];
            }

            Log::warning('Tracksolid Engine Command Rejected: ' . json_encode($data));
            return ['success' => false, 'error' => $data['msg'] ?? 'Tracker rejected the command or is offline.'];

        } catch (\Exception $e) {
            Log::error('Tracksolid API Exception (Engine Command): ' . $e->getMessage());
            return ['success' => false, 'error' => 'Server communication error: ' . $e->getMessage()];
        }
    }
}
