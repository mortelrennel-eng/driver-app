<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

$ts = app(\App\Services\TracksolidService::class);
$unit = DB::table('units')->where('plate_number', 'AAA 4591')->first();
$imei = $unit->imei;

// REWRITE sendEngineCommand with stdClass for params
$instParamJson = json_encode([
    "inst_id" => "113", 
    "inst_template" => "RELAY,1#", 
    "params" => new \stdClass(), 
    "is_cover" => true
]);

$params = [
    'method'          => 'jimi.open.instruction.send',
    'app_key'         => $ts->appKey,
    'access_token'    => $ts->getAccessToken(),
    'timestamp'       => $ts->getTimestamp(),
    'format'          => 'json',
    'v'               => '1.0',
    'sign_method'     => 'md5',
    'imei'            => $imei,
    'inst_param_json' => $instParamJson,
];

// Re-implement signature generation here since it's protected
ksort($params);
$rawString = '';
foreach ($params as $key => $value) {
    if ($key !== 'sign' && !is_null($value) && $value !== '') {
        $rawString .= $key . $value;
    }
}
$params['sign'] = strtoupper(md5($ts->appSecret . $rawString . $ts->appSecret));

echo "Sending modified Kill Command to AAA 4591 (IMEI: {$imei})...\n";
$start = time();
try {
    $response = Illuminate\Support\Facades\Http::asForm()->timeout(15)->post($ts->apiUrl, $params);
    echo "Time taken: " . (time() - $start) . " seconds\n";
    print_r($response->json());
} catch (\Exception $e) {
    echo "Time taken: " . (time() - $start) . " seconds\n";
    echo "Error: " . $e->getMessage() . "\n";
}
