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

echo "Latest System Alerts:\\n";
$alerts = DB::table('system_alerts')->orderBy('id', 'desc')->limit(5)->get();
foreach ($alerts as $a) {
    echo "[\$a->created_at] \$a->title : \$a->message\\n";
}

echo "\\nLatest Logs:\\n";
echo shell_exec('tail -n 20 ' . __DIR__ . '/storage/logs/laravel.log');
`;

const fs = require('fs');
fs.writeFileSync('check_alerts.cjs', phpCode);

const conn = new Client();
conn.on('ready', () => {
    conn.sftp((err, sftp) => {
        if (err) throw err;
        sftp.fastPut('check_alerts.cjs', '/home/u747826271/domains/eurotaxisystem.site/public_html/check_alerts.php', (err) => {
            if (err) throw err;
            conn.exec('php /home/u747826271/domains/eurotaxisystem.site/public_html/check_alerts.php', (err, stream) => {
                if (err) throw err;
                stream.on('data', (data) => console.log(data.toString()))
                      .on('close', () => conn.end());
            });
        });
    });
}).connect(config);
