const { Client } = require('ssh2');

const config = {
    host: '195.35.62.133',
    port: 65002,
    username: 'u747826271',
    password: '@Admineuro2026',
    readyTimeout: 30000
};

const BASE = '/home/u747826271/domains/eurotaxisystem.site/public_html';

// Check total number of routes and look for exception on route load
const command = `cd ${BASE} && php artisan route:list 2>&1 | head -20 && echo "---" && php artisan route:list 2>&1 | grep "driver-behavior" | head -10`;

const conn = new Client();
conn.on('ready', () => {
    conn.exec(command, (err, stream) => {
        if (err) throw err;
        stream.on('close', () => conn.end())
              .on('data', data => process.stdout.write(data))
              .stderr.on('data', data => process.stderr.write(data));
    });
}).connect(config);
