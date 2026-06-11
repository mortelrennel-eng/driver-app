const fs = require('fs');
const { Client } = require('ssh2');

const config = {
    host: '195.35.62.133',
    port: 65002,
    username: 'u747826271',
    password: '@Admineuro2026',
    readyTimeout: 30000
};

const conn = new Client();
conn.on('ready', () => {
    conn.exec('cat /home/u747826271/domains/eurotaxisystem.site/public_html/public/js/realtime-tracking.js', (err, stream) => {
        if (err) throw err;
        let data = '';
        stream.on('data', (chunk) => data += chunk.toString());
        stream.on('close', () => {
            fs.writeFileSync('server_realtime-tracking.js', data);
            console.log('Downloaded. Lines:', data.split('\n').length);
            conn.end();
        });
    });
}).connect(config);
