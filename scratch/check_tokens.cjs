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
    console.log('SSH Connection Ready. Checking registered FCM tokens in remote database...');

    conn.exec(`cd ${BASE_REMOTE} && php artisan tinker --execute="print_r(App\\Models\\User::whereNotNull('fcm_token')->where('fcm_token', '!=', '')->get(['id', 'username', 'first_name', 'last_name', 'fcm_token'])->toArray())"`, (err, stream) => {
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
