const { Client } = require('ssh2');
const fs = require('fs');
const path = require('path');

const config = {
    host: '195.35.62.133',
    port: 65002,
    username: 'u747826271',
    password: '@Admineuro2026',
    readyTimeout: 30000
};

const REMOTE_LOG_PATH = '/home/u747826271/domains/eurotaxisystem.site/public_html/storage/logs/capacitor_diagnostics.log';

console.log('--- FETCHING CAPACITOR DIAGNOSTICS FROM HOSTINGER ---');

const conn = new Client();

conn.on('ready', () => {
    conn.sftp((err, sftp) => {
        if (err) {
            console.error('SFTP Error:', err);
            conn.end();
            return;
        }

        sftp.fastGet(REMOTE_LOG_PATH, path.join(__dirname, 'capacitor_diagnostics.log'), (err) => {
            if (err) {
                if (err.message && err.message.includes('No such file')) {
                    console.log('No diagnostic logs have been created yet on the server. The app has not reported any status yet.');
                } else {
                    console.error('Failed to fetch diagnostics file:', err.message);
                }
                conn.end();
                return;
            }

            console.log('✓ Successfully retrieved diagnostics log file from Hostinger.\n');
            const logs = fs.readFileSync(path.join(__dirname, 'capacitor_diagnostics.log'), 'utf8');
            const lines = logs.trim().split('\n');
            console.log(`Showing last 30 log lines (Total: ${lines.length} lines):`);
            console.log('================================================================================');
            lines.slice(-30).forEach(line => console.log(line));
            console.log('================================================================================');
            conn.end();
        });
    });
}).on('error', (err) => {
    console.error('Connection Error:', err.message);
}).connect(config);
