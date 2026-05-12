const { Client } = require('ssh2');
const fs = require('fs');

const config = {
    host: '195.35.62.133',
    port: 65002,
    username: 'u747826271',
    password: '@Admineuro2026',
    readyTimeout: 30000
};

const BASE_REMOTE = '/home/u747826271/domains/eurotaxisystem.site/public_html';

const command = process.argv[2] || 'ls -la';

console.log('--- EXECUTING REMOTE COMMAND ---');
const conn = new Client();

conn.on('ready', () => {
    console.log('SSH Connected.');
    conn.exec(command, (err, stream) => {
        if (err) { console.error('Exec error:', err); conn.end(); return; }
        stream.on('close', (code) => {
            console.log(`\nDone (code: ${code})`);
            conn.end();
        }).on('data', (data) => {
            process.stdout.write(data.toString());
        }).stderr.on('data', (data) => {
            process.stderr.write('STDERR: ' + data.toString());
        });
    });
}).on('error', (err) => {
    console.error('Connection error:', err.message);
}).connect(config);
