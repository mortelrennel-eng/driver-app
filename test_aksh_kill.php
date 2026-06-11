<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$aksh = app(\App\Services\AkshGpsService::class);
$unit = DB::table('units')->where('gps_provider', 'aksh')->whereNotNull('imei')->first();

if ($unit) {
    $session = $aksh->getSession($unit->imei, $unit->gps_password, true);
    if ($session) {
        $payload = [
            'DeviceID'    => $session['device_id'],
            'CommandType' => 'KY',
            'Paramter'    => '',
            'Key'         => $session['key']
        ];
        $url = $session['api_address'] . '/UpdateCommandByAPP';
        $response = Illuminate\Support\Facades\Http::asForm()->timeout(15)->post($url, $payload);
        echo "Response:\n";
        echo $response->body();
    }
}
