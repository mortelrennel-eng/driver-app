const https = require('https');
const crypto = require('crypto');
const querystring = require('querystring');
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
for ($i=0; $i<3; $i++) {
    $res = $ts->getAllLocations();
    foreach ($res as $r) {
        if ($r['deviceName'] == 'AAA 4591') {
            echo "Attempt " . $i . ": lat=" . $r['lat'] . ", lng=" . $r['lng'] . "\n";
        }
    }
    sleep(11);
}
`;

fs.writeFileSync('test_drift.php', phpCode);

const conn = new Client();
conn.on('ready', () => {
    conn.sftp((err, sftp) => {
        if (err) throw err;
        sftp.fastPut('test_drift.php', '/home/u747826271/domains/eurotaxisystem.site/public_html/test_drift.php', (err) => {
            if (err) throw err;
            conn.exec('php /home/u747826271/domains/eurotaxisystem.site/public_html/test_drift.php', (err, stream) => {
                if (err) throw err;
                stream.on('data', (data) => console.log(data.toString()))
                      .on('close', () => conn.end());
            });
        });
    });
}).connect(config);
