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
    conn.exec('tail -n 50 /home/u747826271/domains/eurotaxisystem.site/public_html/storage/logs/laravel.log', (err, stream) => {
        if (err) throw err;
        let data = '';
        stream.on('data', (chunk) => {
            data += chunk.toString();
        }).on('close', () => {
            console.log(data);
            conn.end();
        });
    });
}).connect(config);
