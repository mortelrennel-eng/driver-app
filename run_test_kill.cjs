const https = require('https');
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
if (!$unit || !$unit->imei) {
    die("No IMEI found");
}

echo "Sending Kill Command to AAA 4591 (IMEI: {$unit->imei})...\\n";
$start = time();
$result = $ts->sendEngineCommand($unit->imei, 'kill');
$end = time();

echo "Time taken: " . ($end - $start) . " seconds\\n";
print_r($result);
`;

fs.writeFileSync('test_kill.php', phpCode);

const conn = new Client();
conn.on('ready', () => {
    conn.sftp((err, sftp) => {
        if (err) throw err;
        sftp.fastPut('test_kill.php', '/home/u747826271/domains/eurotaxisystem.site/public_html/test_kill.php', (err) => {
            if (err) throw err;
            conn.exec('php /home/u747826271/domains/eurotaxisystem.site/public_html/test_kill.php', (err, stream) => {
                if (err) throw err;
                stream.on('data', (data) => console.log(data.toString()))
                      .on('close', () => conn.end());
            });
        });
    });
}).connect(config);
