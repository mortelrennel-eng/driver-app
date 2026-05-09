const { Client } = require('ssh2');
const conn = new Client();
conn.on('ready', () => {
    conn.exec('grep -n "Asia/Manila" /home/u747826271/domains/eurotaxisystem.site/public_html/app/Http/Controllers/DashboardController.php', (err, stream) => {
        stream.on('data', data => console.log('MATCHES:\n' + data.toString()))
              .on('close', () => conn.end());
        stream.stderr.on('data', data => console.log('ERR: ' + data));
    });
}).connect({host: '195.35.62.133', port: 65002, username: 'u747826271', password: '@Admineuro2026'});
