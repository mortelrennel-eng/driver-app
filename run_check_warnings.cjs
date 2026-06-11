const { Client } = require('ssh2');

const config = {
    host: '195.35.62.133',
    port: 65002,
    username: 'u747826271',
    password: '@Admineuro2026',
    readyTimeout: 30000
};

const phpCode = `<?php
$logPath = '/home/u747826271/domains/eurotaxisystem.site/public_html/storage/logs/laravel.log';
$lines = file($logPath);
$latestLines = array_slice($lines, -50);
foreach ($latestLines as $line) {
    if (strpos($line, 'production.WARNING') !== false || strpos($line, 'production.ERROR') !== false) {
        echo $line;
    }
}
`;

const fs = require('fs');
fs.writeFileSync('check_warnings.cjs', phpCode);

const conn = new Client();
conn.on('ready', () => {
    conn.sftp((err, sftp) => {
        if (err) throw err;
        sftp.fastPut('check_warnings.cjs', '/home/u747826271/domains/eurotaxisystem.site/public_html/check_warnings.php', (err) => {
            if (err) throw err;
            conn.exec('php /home/u747826271/domains/eurotaxisystem.site/public_html/check_warnings.php', (err, stream) => {
                if (err) throw err;
                stream.on('data', (data) => console.log(data.toString()))
                      .on('close', () => conn.end());
            });
        });
    });
}).connect(config);
