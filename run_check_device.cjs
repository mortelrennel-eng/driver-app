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
$token = $ts->getAccessToken();
$imei = '352503096887481'; // IMEI for AAA 4591

// Generate timestamp manually since getTimestamp is protected
$timestamp = gmdate('Y-m-d H:i:s');

$params = [
    'method'       => 'jimi.device.detail.get',
    'app_key'      => '8FB345B8693CCD00F4EFB0A7B5CA8D10',
    'access_token' => $token,
    'timestamp'    => $timestamp,
    'format'       => 'json',
    'v'            => '1.0',
    'sign_method'  => 'md5',
    'imeis'        => $imei
];

ksort($params);
$rawString = '';
foreach ($params as $key => $value) {
    if ($key !== 'sign' && !is_null($value) && $value !== '') {
        $rawString .= $key . $value;
    }
}
$params['sign'] = strtoupper(md5('8AE7DA4C7ACF41B4A7822557AAFEAEE3' . $rawString . '8AE7DA4C7ACF41B4A7822557AAFEAEE3'));

$response = Illuminate\\Support\\Facades\\Http::asForm()->post('https://hk-open.tracksolidpro.com/route/rest', $params);
echo "\\nTracksolid Device Info:\\n";
print_r($response->json());
`;

fs.writeFileSync('check_device.php', phpCode);

const conn = new Client();
conn.on('ready', () => {
    conn.sftp((err, sftp) => {
        if (err) throw err;
        sftp.fastPut('check_device.php', '/home/u747826271/domains/eurotaxisystem.site/public_html/check_device.php', (err) => {
            if (err) throw err;
            conn.exec('php /home/u747826271/domains/eurotaxisystem.site/public_html/check_device.php', (err, stream) => {
                if (err) throw err;
                stream.on('data', (data) => console.log(data.toString()))
                      .on('close', () => conn.end());
            });
        });
    });
}).connect(config);
