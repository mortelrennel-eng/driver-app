const { Client } = require('ssh2');
const config = { host: '195.35.62.133', port: 65002, username: 'u747826271', password: '@Admineuro2026' };
const conn = new Client();
conn.on('ready', () => {
    conn.exec('cd /home/u747826271/domains/eurotaxisystem.site/public_html && php artisan optimize:clear', (err, stream) => {
        stream.on('data', d => process.stdout.write(''+d)).on('close', () => conn.end());
    });
}).connect(config);
