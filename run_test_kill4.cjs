const fs = require('fs');
const { Client } = require('ssh2');

const config = {
    host: '195.35.62.133',
    port: 65002,
    username: 'u747826271',
    password: '@Admineuro2026',
    readyTimeout: 30000
};

const phpCode = `<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\\Contracts\\Http\\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\\Http\\Request::capture()
);

$ts = app(\\App\\Services\\TracksolidService::class);
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

echo "Sending Kill Command to AAA 4591 (IMEI: {$imei})...\\n";
$start = time();
$response = Illuminate\\Support\\Facades\\Http::asForm()->timeout(15)->post('https://hk-open.tracksolidpro.com/route/rest', $params);
echo "Time taken: " . (time() - $start) . " seconds\\n";
print_r($response->json());
`;

fs.writeFileSync('test_kill4.php', phpCode);

const conn = new Client();
conn.on('ready', () => {
    conn.sftp((err, sftp) => {
        if (err) throw err;
        sftp.fastPut('test_kill4.php', '/home/u747826271/domains/eurotaxisystem.site/public_html/test_kill4.php', (err) => {
            if (err) throw err;
            conn.exec('php /home/u747826271/domains/eurotaxisystem.site/public_html/test_kill4.php', (err, stream) => {
                if (err) throw err;
                stream.on('data', (data) => console.log(data.toString()))
                      .on('close', () => conn.end());
            });
        });
    });
}).connect(config);
