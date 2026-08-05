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
            $response = Http::connectTimeout(3)->timeout(10)->asForm()->post($this->apiUrl, $params);
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
            $response = Http::connectTimeout(3)->timeout(10)->asForm()->post($this->apiUrl, $params);
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
            $response = Http::connectTimeout(3)->timeout(10)->asForm()->post($this->apiUrl, $params);
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
            $response = Http::connectTimeout(3)->timeout(10)->asForm()->post($this->apiUrl, $params);
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
            $response = Http::connectTimeout(3)->timeout(10)->asForm()->post($this->apiUrl, $params);
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
            $response = Http::connectTimeout(3)->timeout(10)->asForm()->post($this->apiUrl, $params);
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
     * Get recent command execution results for a device.
     */
    public function getInstructionResults(string $imei, bool $retryOnExpiredToken = true)
    {
        $token = $this->getAccessToken();
        if (!$token) return null;

        $params = [
            'method'       => 'jimi.open.instruction.result',
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
            $response = Http::connectTimeout(3)->timeout(10)->asForm()->post($this->apiUrl, $params);
            $data = $response->json();

            if (isset($data['code']) && $data['code'] == 0) {
                return $data['result'] ?? [];
            }

            if ($retryOnExpiredToken && isset($data['code']) && in_array($data['code'], [10006, 10011, 1004])) {
                $this->getAccessToken(true);
                return $this->getInstructionResults($imei, false);
            }

            Log::error('Tracksolid API Instruction Result Error: ' . json_encode($data));
            return null;

        } catch (\Exception $e) {
            Log::error('Tracksolid API Exception (Instruction Result): ' . $e->getMessage());
            return null;
        }
    }

    private function latestRelayResult(array $results, string $action): ?array
    {
        $commandId = $action === 'kill' ? '113' : '114';
        $commandText = $action === 'kill' ? 'RELAY,1#' : 'RELAY,0#';

        foreach ($results as $result) {
            if (
                (string)($result['codeId'] ?? '') === $commandId
                || (string)($result['code'] ?? '') === $commandText
            ) {
                return $result;
            }
        }

        return null;
    }

    private function isTracksolidCommandTimeout(array $data): bool
    {
        if ((int)($data['code'] ?? 0) !== 12005) {
            return false;
        }

        $message = (string)($data['message'] ?? '');

        return stripos($message, '225') !== false;
    }

    /**
     * Send Engine Cut-off / Restore Command via Tracksolid Pro API.
     *
     * Open API 7.26 uses jimi.open.instruction.send with inst_param_json:
     *   113 / RELAY,1# = cut relay
     *   114 / RELAY,0# = restore relay
     */
    public function sendEngineCommand(string $imei, string $action, bool $retryOnExpiredToken = true)
    {
        $token = $this->getAccessToken();
        if (!$token) return ['success' => false, 'error' => 'API Auth Failed. Please check TrackSolid credentials.'];

        $command = $action === 'kill'
            ? ['inst_id' => '113', 'inst_template' => 'RELAY,1#']
            : ['inst_id' => '114', 'inst_template' => 'RELAY,0#'];

        $params = [
            'method'          => 'jimi.open.instruction.send',
            'app_key'         => $this->appKey,
            'access_token'    => $token,
            'timestamp'       => $this->getTimestamp(),
            'format'          => 'json',
            'v'               => '1.0',
            'sign_method'     => 'md5',
            'imei'            => $imei,
            'inst_param_json' => json_encode([
                'inst_id'       => $command['inst_id'],
                'inst_template' => $command['inst_template'],
                'params'        => [],
                'is_cover'      => 'true',
            ], JSON_UNESCAPED_SLASHES),
        ];

        $params['sign'] = $this->generateSignature($params);

        try {
            // Command send can wait for Tracksolid/device processing; keep this below the browser timeout.
            // so the server always responds before the browser aborts
            $response = Http::connectTimeout(5)->timeout(45)->asForm()->post($this->apiUrl, $params);
            $data = $response->json();

            Log::info("Tracksolid Engine Command [{$action}] IMEI={$imei}: " . json_encode([
                'request'  => [
                    'method'          => $params['method'],
                    'imei'            => $imei,
                    'inst_param_json' => $params['inst_param_json'],
                ],
                'response' => $data,
            ]));

            // code 0 = success
            if (isset($data['code']) && $data['code'] == 0) {
                return [
                    'success' => true,
                    'message' => $data['message'] ?? ('Engine ' . ($action === 'kill' ? 'cut-off' : 'restore') . ' command sent successfully.'),
                    'data'    => $data,
                ];
            }

            // code 10006 / 10011 = token expired, try once with a fresh token
            if ($retryOnExpiredToken && isset($data['code']) && in_array($data['code'], [10006, 10011, 1004])) {
                Log::warning("Tracksolid token expired during engine command. Refreshing token and retrying...");
                $this->getAccessToken(true); // force refresh
                return $this->sendEngineCommand($imei, $action, false); // retry once
            }

            if ($this->isTracksolidCommandTimeout($data)) {
                $instructionResults = $this->getInstructionResults($imei);
                $latestRelayResult = is_array($instructionResults)
                    ? $this->latestRelayResult($instructionResults, $action)
                    : null;

                Log::warning("Tracksolid Engine Command Submitted With Timeout [{$action}] IMEI={$imei}: " . json_encode([
                    'send_response' => $data,
                    'latest_result' => $latestRelayResult,
                ]));

                if (($latestRelayResult['isExecute'] ?? null) === '1') {
                    return [
                        'success' => true,
                        'message' => 'Tracksolid confirmed the engine command execution.',
                        'data'    => $data,
                        'result'  => $latestRelayResult,
                    ];
                }

                return [
                    'success' => true,
                    'tracksolid_timeout' => true,
                    'message' => 'Engine ' . ($action === 'kill' ? 'cut-off' : 'restore') . ' command sent successfully to Tracksolid Pro.',
                    'data'    => $data,
                    'result'  => $latestRelayResult,
                ];
            }

            $errorMsg  = $data['message'] ?? ($data['msg'] ?? 'Unknown error from Tracksolid API');
            $errorCode = $data['code'] ?? 'N/A';

            if ((int)$errorCode === 12005) {
                $errorMsg .= ' Check whether this device supports RELAY commands and whether the relay wire is installed.';
            }

            Log::error("Tracksolid Engine Command Failed [{$action}] Code:{$errorCode} - " . json_encode($data));
            return ['success' => false, 'error' => "Tracksolid error (Code: {$errorCode}): {$errorMsg}"];

        } catch (\Exception $e) {
            Log::error('Tracksolid Engine Command Exception: ' . $e->getMessage());
            return ['success' => false, 'error' => 'Could not reach Tracksolid server: ' . $e->getMessage()];
        }
    }
}
