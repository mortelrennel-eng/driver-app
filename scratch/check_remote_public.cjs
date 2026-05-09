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
    console.log('SSH Connection Ready. Listing files in remote public_html directory...');

    conn.exec(`ls -la ${BASE_REMOTE}`, (err, stream) => {
        if (err) {
            console.error('Execution failed:', err);
            conn.end();
            return;
        }
        stream.on('close', (code) => {
            conn.end();
        }).on('data', (data) => {
            process.stdout.write(data);
        }).stderr.on('data', (data) => {
            process.stderr.write(data);
        });
    });
}).on('error', (err) => {
    console.error('Connection Error:', err.message);
}).connect(config);
