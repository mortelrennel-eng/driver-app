const { Client } = require('ssh2');
const conn = new Client();
conn.on('ready', () => {
    conn.exec('grep -C 5 "ERROR" /home/u747826271/domains/eurotaxisystem.site/public_html/storage/logs/laravel.log | tail -n 100', (err, stream) => {
        stream.on('data', data => console.log('ERRORS:\n' + data.toString()))
              .on('close', () => conn.end());
        stream.stderr.on('data', data => console.log('ERR: ' + data));
    });
}).connect({host: '195.35.62.133', port: 65002, username: 'u747826271', password: '@Admineuro2026'});
