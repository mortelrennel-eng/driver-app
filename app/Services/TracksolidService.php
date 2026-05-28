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
        $backupPath = storage_path('framework/cache/tracksolid_token_backup.json');
        
        if ($forceRefresh) {
            Cache::forget($cacheKey);
            if (file_exists($backupPath)) {
                @unlink($backupPath);
            }
        }
        
        // 1. Check Laravel cache
        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        // 2. Check local persistent file backup (survives cache clear)
        if (file_exists($backupPath)) {
            try {
                $backupData = json_decode(file_get_contents($backupPath), true);
                if (is_array($backupData) && isset($backupData['token']) && isset($backupData['expires_at'])) {
                    // If not expired, restore cache and return
                    if (time() < $backupData['expires_at']) {
                        $remaining = $backupData['expires_at'] - time();
                        Cache::put($cacheKey, $backupData['token'], $remaining);
                        return $backupData['token'];
                    }
                }
            } catch (\Exception $e) {
                // Ignore backup read errors
            }
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

        try {
            $response = Http::asForm()->post($this->apiUrl, $params);
            $data = $response->json();

            if (isset($data['code']) && $data['code'] == 0 && isset($data['result']['accessToken'])) {
                $token = $data['result']['accessToken'];
                $expiresIn = $data['result']['expiresIn'] ?? 3600;
                
                // Cache token slightly shorter than its actual expiry
                Cache::put($cacheKey, $token, $expiresIn - 60);
                
                // Save to local backup file
                try {
                    $dir = dirname($backupPath);
                    if (!is_dir($dir)) {
                        @mkdir($dir, 0755, true);
                    }
                    file_put_contents($backupPath, json_encode([
                        'token' => $token,
                        'expires_at' => time() + $expiresIn - 60
                    ]));
                } catch (\Exception $e) {
                    // Ignore backup write errors
                }
                
                return $token;
            }

            Log::error('Tracksolid API Token Error: ' . json_encode($data));

            // 3. Fallback: If API returns rate-limiting error, use backup token anyway as best effort
            if (file_exists($backupPath)) {
                try {
                    $backupData = json_decode(file_get_contents($backupPath), true);
                    if (is_array($backupData) && isset($backupData['token'])) {
                        return $backupData['token'];
                    }
                } catch (\Exception $e) {
                    // Ignore
                }
            }
            
            return null;

        } catch (\Exception $e) {
            Log::error('Tracksolid API Exception (Token): ' . $e->getMessage());

            // Fallback: If connection failed, use backup token as best effort
            if (file_exists($backupPath)) {
                try {
                    $backupData = json_decode(file_get_contents($backupPath), true);
                    if (is_array($backupData) && isset($backupData['token'])) {
                        return $backupData['token'];
                    }
                } catch (\Exception $e) {
                    // Ignore
                }
            }
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

        // Tracksolid Pro API requires inst_param_json for jimi.open.instruction.send
        $instParamJson = ($action === 'kill') 
            ? json_encode(["inst_id" => 113, "inst_template" => "RELAY,1#", "params" => [], "is_cover" => true]) 
            : json_encode(["inst_id" => 114, "inst_template" => "RELAY,0#", "params" => [], "is_cover" => true]);

        $params = [
            'method'          => 'jimi.open.instruction.send',
            'app_key'         => $this->appKey,
            'access_token'    => $token,
            'timestamp'       => $this->getTimestamp(),
            'format'          => 'json',
            'v'               => '1.0',
            'sign_method'     => 'md5',
            'imei'            => $imei,
            'inst_param_json' => $instParamJson,
        ];

        $params['sign'] = $this->generateSignature($params);

        try {
            $response = Http::asForm()->post($this->apiUrl, $params);
            $data = $response->json();

            if (isset($data['code']) && $data['code'] == 0) {
                return ['success' => true, 'message' => 'Command executed successfully.', 'data' => $data];
            }
            
            // 1002 means the device is offline, but the command was successfully queued.
            if (isset($data['code']) && $data['code'] == 1002) {
                return ['success' => true, 'message' => 'Command queued. Device is currently offline.', 'data' => $data];
            }
            
            // 12005 / result code 225 means device rejected it (e.g. wire not connected or model doesn't support it)
            if (isset($data['code']) && $data['code'] == 12005) {
                return ['success' => false, 'error' => 'Tracker received the command but hardware rejected it (Result Code 225). Ensure Relay is wired.'];
            }

            Log::warning('Tracksolid Engine Command Rejected: ' . json_encode($data));
            return ['success' => false, 'error' => $data['message'] ?? ($data['msg'] ?? 'Tracker rejected the command or API error.')];

        } catch (\Exception $e) {
            Log::error('Tracksolid API Exception (Engine Command): ' . $e->getMessage());
            return ['success' => false, 'error' => 'Server communication error: ' . $e->getMessage()];
        }
    }
}
