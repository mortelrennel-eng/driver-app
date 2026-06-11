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
    console.log('SSH Connected! Running php artisan view:clear...');
    conn.exec('cd /home/u747826271/domains/eurotaxisystem.site/public_html && php artisan view:clear && php artisan cache:clear', (err, stream) => {
        if (err) {
            console.error('Execution Error:', err);
            conn.end();
            return;
        }
        stream.on('close', (code, signal) => {
            console.log(`\n✅ View & Cache cleared successfully on Hostinger!`);
            conn.end();
        }).on('data', (data) => {
            process.stdout.write(data);
        }).stderr.on('data', (data) => {
            process.stderr.write(data);
        });
    });
}).on('error', err => {
    console.error('Connection Error:', err.message);
}).connect(config);
