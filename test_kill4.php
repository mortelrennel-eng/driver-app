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

$instParamJson = json_encode([
    "inst_id" => 113, 
    "inst_template" => "RELAY,1#", 
    "params" => [], 
    "is_cover" => true
]);

$params = [
    'method'          => 'jimi.open.instruction.send',
    'app_key'         => '8FB345B8693CCD00F4EFB0A7B5CA8D10', // From TracksolidService
    'access_token'    => $ts->getAccessToken(),
    'timestamp'       => $ts->getTimestamp(),
    'format'          => 'json',
    'v'               => '1.0',
    'sign_method'     => 'md5',
    'imei'            => $imei,
    'inst_param_json' => $instParamJson,
];

ksort($params);
$rawString = '';
foreach ($params as $key => $value) {
    if ($key !== 'sign' && !is_null($value) && $value !== '') {
        $rawString .= $key . $value;
    }
}
$params['sign'] = strtoupper(md5('8AE7DA4C7ACF41B4A7822557AAFEAEE3' . $rawString . '8AE7DA4C7ACF41B4A7822557AAFEAEE3'));

echo "Sending Kill Command to AAA 4591 (IMEI: {$imei})...\n";
$start = time();
$response = Illuminate\Support\Facades\Http::asForm()->timeout(15)->post('https://hk-open.tracksolidpro.com/route/rest', $params);
echo "Time taken: " . (time() - $start) . " seconds\n";
print_r($response->json());
