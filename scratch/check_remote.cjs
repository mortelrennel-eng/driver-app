const { Client } = require('ssh2');

const config = {
    host: '195.35.62.133',
    port: 65002,
    username: 'u747826271',
    password: '@Admineuro2026',
    readyTimeout: 30000
};

const BASE_REMOTE = '/home/u747826271/domains/eurotaxisystem.site/public_html';

const conn = new Client();

conn.on('ready', () => {
    conn.exec(`cat ${BASE_REMOTE}/resources/views/partials/chat-drawer.blade.php`, (err, stream) => {
        if (err) throw err;
        let dataStr = '';
        stream.on('close', () => {
            conn.end();
            console.log('File size:', dataStr.length);
            console.log(dataStr.substring(0, 500));
        }).on('data', (data) => {
            dataStr += data.toString();
        }).stderr.on('data', (data) => {
            console.error('STDERR: ' + data);
        });
    });
}).connect(config);
