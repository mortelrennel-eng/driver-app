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

echo "Testing ALA 3699 (Working Unit):\\n";
$unit1 = DB::table('units')->where('plate_number', 'ALA 3699')->first();
$start = time();
$result1 = $ts->sendEngineCommand($unit1->imei, 'restore');
echo "Time: " . (time() - $start) . "s\\n";
print_r($result1);

echo "\\nTesting AAA 4591 (Problematic Unit):\\n";
$unit2 = DB::table('units')->where('plate_number', 'AAA 4591')->first();
$start = time();
$result2 = $ts->sendEngineCommand($unit2->imei, 'restore');
echo "Time: " . (time() - $start) . "s\\n";
print_r($result2);
`;

fs.writeFileSync('test_both.php', phpCode);

const conn = new Client();
conn.on('ready', () => {
    conn.sftp((err, sftp) => {
        if (err) throw err;
        sftp.fastPut('test_both.php', '/home/u747826271/domains/eurotaxisystem.site/public_html/test_both.php', (err) => {
            if (err) throw err;
            conn.exec('php /home/u747826271/domains/eurotaxisystem.site/public_html/test_both.php', (err, stream) => {
                if (err) throw err;
                stream.on('data', (data) => console.log(data.toString()))
                      .on('close', () => conn.end());
            });
        });
    });
}).connect(config);
